<?php
class TareaModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Crear una nueva tarea para un asesor
     */
    public function crearTarea($datos) {
        $this->pdo->beginTransaction();
        try {
            $sql = "INSERT INTO tareas_asesor (asesor_id, carga_id, cliente_ids, descripcion, fecha_vencimiento, coordinador_id, prioridad) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            
            $resultado = $stmt->execute([
                $datos['asesor_id'],
                $datos['carga_id'],
                json_encode($datos['cliente_ids']),
                $datos['descripcion'] ?? null,
                $datos['fecha_vencimiento'] ?? null,
                $datos['coordinador_id'],
                $datos['prioridad'] ?? 'media'
            ]);
            
            if ($resultado) {
                $tareaId = $this->pdo->lastInsertId();
                
                // Registrar en historial
                $this->registrarHistorial($tareaId, 'creada', 'Tarea creada', $datos['coordinador_id']);
                
                $this->pdo->commit();
                return $tareaId;
            }
            
            $this->pdo->rollBack();
            return false;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al crear tarea: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener tareas de un asesor
     */
    public function getTareasByAsesor($asesorId, $estado = null) {
        $sql = "SELECT t.*, c.nombre_cargue, u.nombre_completo as coordinador_nombre
                FROM tareas_asesor t
                JOIN cargas_excel c ON t.carga_id = c.id
                JOIN usuarios u ON t.coordinador_id = u.id
                WHERE t.asesor_id = ?";
        
        $params = [$asesorId];
        
        if ($estado) {
            $sql .= " AND t.estado = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY t.fecha_creacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decodificar los IDs de clientes
        foreach ($tareas as &$tarea) {
            $tarea['cliente_ids'] = json_decode($tarea['cliente_ids'], true);
            $tarea['total_clientes'] = count($tarea['cliente_ids']);
        }
        
        return $tareas;
    }

    /**
     * Obtener tareas pendientes de un asesor
     */
    public function getTareasPendientesByAsesor($asesorId) {
        return $this->getTareasByAsesor($asesorId, 'pendiente');
    }

    /**
     * Obtener clientes de una tarea específica
     */
    public function getClientesByTarea($tareaId) {
        $sql = "SELECT t.cliente_ids, c.nombre_cargue
                FROM tareas_asesor t
                JOIN cargas_excel c ON t.carga_id = c.id
                WHERE t.id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tareaId]);
        $tarea = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tarea) {
            return [];
        }
        
        $clienteIds = json_decode($tarea['cliente_ids'], true);
        
        if (empty($clienteIds)) {
            return [];
        }
        
        // Obtener información de los clientes
        $placeholders = str_repeat('?,', count($clienteIds) - 1) . '?';
        $sql = "SELECT * FROM clientes WHERE id IN ($placeholders) ORDER BY nombre ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($clienteIds);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar estado de una tarea
     */
    public function actualizarEstadoTarea($tareaId, $nuevoEstado, $usuarioId) {
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE tareas_asesor SET estado = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$nuevoEstado, $tareaId]);
            
            if ($resultado) {
                $this->registrarHistorial($tareaId, 'estado_cambiado', "Estado cambiado a: $nuevoEstado", $usuarioId);
                $this->pdo->commit();
                return true;
            }
            
            $this->pdo->rollBack();
            return false;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al actualizar estado de tarea: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un asesor tiene tareas pendientes
     */
    public function tieneTareasPendientes($asesorId) {
        $sql = "SELECT COUNT(*) as total FROM tareas_asesor WHERE asesor_id = ? AND estado = 'pendiente'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total'] > 0;
    }

    /**
     * Obtener bases asignadas a un asesor (acceso completo)
     */
    public function getBasesAsignadasByAsesor($asesorId) {
        $sql = "SELECT aba.*, c.nombre_cargue, c.fecha_cargue, u.nombre_completo as coordinador_nombre
                FROM asignaciones_base_asesor aba
                JOIN cargas_excel c ON aba.carga_id = c.id
                JOIN usuarios u ON aba.coordinador_id = u.id
                WHERE aba.asesor_id = ? AND aba.estado = 'activa' AND aba.acceso_completo = 1
                ORDER BY aba.fecha_asignacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Asignar base completa a un asesor
     */
    public function asignarBaseCompleta($cargaId, $asesorId, $coordinadorId) {
        $this->pdo->beginTransaction();
        try {
            // Verificar si ya existe la asignación
            $sql = "SELECT id FROM asignaciones_base_asesor WHERE carga_id = ? AND asesor_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$cargaId, $asesorId]);
            
            if ($stmt->fetch()) {
                // Actualizar asignación existente
                $sql = "UPDATE asignaciones_base_asesor 
                        SET estado = 'activa', acceso_completo = 1, tipo_asignacion = 'base_completa', fecha_asignacion = NOW()
                        WHERE carga_id = ? AND asesor_id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$cargaId, $asesorId]);
            } else {
                // Crear nueva asignación
                $sql = "INSERT INTO asignaciones_base_asesor (carga_id, asesor_id, coordinador_id, acceso_completo, tipo_asignacion) 
                        VALUES (?, ?, ?, 1, 'base_completa')";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$cargaId, $asesorId, $coordinadorId]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al asignar base completa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Liberar base de un asesor
     */
    public function liberarBase($cargaId, $asesorId) {
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE asignaciones_base_asesor SET estado = 'inactiva' WHERE carga_id = ? AND asesor_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([$cargaId, $asesorId]);
            
            $this->pdo->commit();
            return $resultado;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al liberar base: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar acción en historial
     */
    private function registrarHistorial($tareaId, $accion, $descripcion, $usuarioId) {
        $sql = "INSERT INTO historial_tareas (tarea_id, accion, descripcion, usuario_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tareaId, $accion, $descripcion, $usuarioId]);
    }

    /**
     * Obtener estadísticas de tareas para coordinador
     */
    public function getEstadisticasTareas($coordinadorId) {
        $sql = "SELECT 
                    COUNT(*) as total_tareas,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN estado = 'completadas' THEN 1 ELSE 0 END) as completadas,
                    SUM(CASE WHEN estado = 'canceladas' THEN 1 ELSE 0 END) as canceladas
                FROM tareas_asesor 
                WHERE coordinador_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$coordinadorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las tareas de un coordinador
     */
    public function getTareasByCoordinador($coordinadorId, $estado = null) {
        $sql = "SELECT t.*, c.nombre_cargue, u.nombre_completo as asesor_nombre
                FROM tareas_asesor t
                JOIN cargas_excel c ON t.carga_id = c.id
                JOIN usuarios u ON t.asesor_id = u.id
                WHERE t.coordinador_id = ?";
        
        $params = [$coordinadorId];
        
        if ($estado) {
            $sql .= " AND t.estado = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY t.fecha_creacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decodificar los IDs de clientes
        foreach ($tareas as &$tarea) {
            $tarea['cliente_ids'] = json_decode($tarea['cliente_ids'], true);
            $tarea['total_clientes'] = count($tarea['cliente_ids']);
        }
        
        return $tareas;
    }

    /**
     * Buscar cliente por cédula en las bases asignadas al asesor
     */
    public function buscarClienteEnBasesAsignadas($asesorId, $cedula) {
        // Obtener bases asignadas al asesor
        $bases = $this->getBasesAsignadasByAsesor($asesorId);
        
        if (empty($bases)) {
            return [];
        }
        
        $cargaIds = array_column($bases, 'carga_id');
        $placeholders = str_repeat('?,', count($cargaIds) - 1) . '?';
        
        $sql = "SELECT c.*, ce.nombre_cargue
                FROM clientes c
                JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                WHERE c.cedula = ? AND c.carga_excel_id IN ($placeholders)
                ORDER BY c.nombre ASC";
        
        $params = array_merge([$cedula], $cargaIds);
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener asesores asignados a una base específica
     */
    public function getAsesoresByBase($cargaId) {
        $sql = "SELECT DISTINCT u.id, u.nombre_completo, u.usuario
                FROM usuarios u
                JOIN asignaciones_base_asesor ab ON u.id = ab.asesor_id
                WHERE ab.carga_id = ? AND u.rol = 'asesor' AND ab.estado = 'activa'
                ORDER BY u.nombre_completo ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cargaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estadísticas de clientes en una base
     */
    public function getEstadisticasClientesBase($cargaId) {
        $sql = "SELECT 
                    COUNT(*) as total_clientes,
                    COUNT(CASE WHEN c.asesor_id IS NULL THEN 1 END) as total_no_gestionados
                FROM clientes c
                WHERE c.carga_excel_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cargaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener clientes no gestionados de una base (para asignar a tareas)
     */
    public function getClientesNoGestionadosBase($cargaId, $cantidad) {
        $sql = "SELECT c.id, c.nombre, c.cedula, c.telefono
                FROM clientes c
                WHERE c.carga_excel_id = ? 
                AND c.asesor_id IS NULL
                ORDER BY RAND()
                LIMIT " . intval($cantidad);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cargaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
