<?php 
class CargaExcelModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllCargas() {
        $stmt = $this->pdo->query("SELECT ce.*, u.nombre_completo as coordinador_nombre FROM cargas_excel ce JOIN usuarios u ON ce.usuario_coordinador_id = u.id ORDER BY ce.fecha_cargue DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene solo las cargas del coordinador específico
     */
    public function getCargasByCoordinador($coordinadorId, $soloHabilitadas = true) {
        $sql = "SELECT ce.*, u.nombre_completo as coordinador_nombre 
                FROM cargas_excel ce 
                JOIN usuarios u ON ce.usuario_coordinador_id = u.id 
                WHERE ce.usuario_coordinador_id = ?";
        
        if ($soloHabilitadas) {
            $sql .= " AND ce.estado_habilitado = 'habilitado'";
        }
        
        $sql .= " ORDER BY ce.fecha_cargue DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$coordinadorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una carga específica verificando que pertenezca al coordinador
     */
    public function getCargaByIdAndCoordinador($cargaId, $coordinadorId) {
        $stmt = $this->pdo->prepare("SELECT ce.*, u.nombre_completo as coordinador_nombre 
                                    FROM cargas_excel ce 
                                    JOIN usuarios u ON ce.usuario_coordinador_id = u.id 
                                    WHERE ce.id = ? AND ce.usuario_coordinador_id = ?");
        $stmt->execute([$cargaId, $coordinadorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una carga por nombre y coordinador
     */
    public function getCargaByNombre($nombreCargue, $coordinadorId) {
        $sql = "SELECT * FROM cargas_excel WHERE nombre_cargue = ? AND usuario_coordinador_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nombreCargue, $coordinadorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la carga consolidada del coordinador (solo una)
     */
    public function getCargaConsolidada($coordinadorId) {
        $sql = "SELECT * FROM cargas_excel WHERE usuario_coordinador_id = ? AND nombre_cargue = 'BASE_DATOS_CONSOLIDADA' LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$coordinadorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea la carga consolidada del coordinador
     */
    public function crearCargaConsolidada($coordinadorId) {
        $sql = "INSERT INTO cargas_excel (nombre_cargue, usuario_coordinador_id, fecha_cargue, estado) 
                VALUES ('BASE_DATOS_CONSOLIDADA', ?, NOW(), 'Activa')";
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([$coordinadorId])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Crea una nueva carga
     */
    public function crearCarga($nombreCargue, $coordinadorId) {
        // Verificar si la columna tipo_base_datos existe
        $checkColumn = $this->pdo->query("SHOW COLUMNS FROM cargas_excel LIKE 'tipo_base_datos'");
        $columnExists = $checkColumn->rowCount() > 0;
        
        if ($columnExists) {
            $sql = "INSERT INTO cargas_excel (nombre_cargue, usuario_coordinador_id, fecha_cargue, estado, tipo_base_datos) 
                    VALUES (?, ?, NOW(), 'Activa', 'independiente')";
        } else {
            $sql = "INSERT INTO cargas_excel (nombre_cargue, usuario_coordinador_id, fecha_cargue, estado) 
                    VALUES (?, ?, NOW(), 'Activa')";
        }
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([$nombreCargue, $coordinadorId])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Crea una nueva base de datos independiente
     */
    public function crearBaseDatosIndependiente($nombreBaseDatos, $coordinadorId) {
        // Verificar si la columna tipo_base_datos existe
        $checkColumn = $this->pdo->query("SHOW COLUMNS FROM cargas_excel LIKE 'tipo_base_datos'");
        $columnExists = $checkColumn->rowCount() > 0;
        
        if ($columnExists) {
            $sql = "INSERT INTO cargas_excel (nombre_cargue, usuario_coordinador_id, fecha_cargue, estado, tipo_base_datos) 
                    VALUES (?, ?, NOW(), 'Activa', 'independiente')";
        } else {
            $sql = "INSERT INTO cargas_excel (nombre_cargue, usuario_coordinador_id, fecha_cargue, estado) 
                    VALUES (?, ?, NOW(), 'Activa')";
        }
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([$nombreBaseDatos, $coordinadorId])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Obtiene estadísticas de una base de datos
     */
    public function getEstadisticasBaseDatos($cargaId) {
        // Total de clientes en la base de datos
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_clientes FROM clientes WHERE carga_excel_id = ?");
        $stmt->execute([$cargaId]);
        $totalClientes = $stmt->fetch(PDO::FETCH_ASSOC)['total_clientes'];

        // Clientes asignados (solo los que tienen asesor_id y asignación activa)
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT c.id) as clientes_asignados 
            FROM clientes c 
            INNER JOIN asignaciones_clientes ac ON c.id = ac.cliente_id 
            WHERE c.carga_excel_id = ? 
            AND c.asesor_id IS NOT NULL 
            AND ac.estado = 'asignado'
        ");
        $stmt->execute([$cargaId]);
        $clientesAsignados = $stmt->fetch(PDO::FETCH_ASSOC)['clientes_asignados'];

        // Clientes por asignar
        $clientesPorAsignar = $totalClientes - $clientesAsignados;

        // Asesores asignados a esta base de datos (acceso completo a la base)
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT u.id, u.nombre_completo, u.usuario
            FROM usuarios u
            INNER JOIN asignaciones_base_asesor aba ON u.id = aba.asesor_id
            WHERE aba.carga_id = ? 
            AND u.rol = 'asesor' 
            AND aba.estado = 'activa'
        ");
        $stmt->execute([$cargaId]);
        $asesoresAsignados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_clientes' => $totalClientes,
            'clientes_asignados' => $clientesAsignados,
            'clientes_por_asignar' => $clientesPorAsignar,
            'asesores_asignados' => $asesoresAsignados
        ];
    }

    /**
     * Asigna un asesor a una base de datos
     */
    public function asignarAsesorABaseDatos($cargaId, $asesorId) {
        $this->pdo->beginTransaction();
        try {
            // 1. Crear o actualizar asignación de base completa
            $stmt = $this->pdo->prepare("
                INSERT INTO asignaciones_base_asesor (carga_id, asesor_id, coordinador_id, fecha_asignacion, estado, acceso_completo) 
                VALUES (?, ?, (SELECT usuario_coordinador_id FROM cargas_excel WHERE id = ?), NOW(), 'activa', 1)
                ON DUPLICATE KEY UPDATE 
                estado = 'activa', 
                acceso_completo = 1, 
                fecha_asignacion = NOW()
            ");
            $stmt->execute([$cargaId, $asesorId, $cargaId]);

            // 2. Obtener todos los clientes no asignados de esta base de datos
            $stmt = $this->pdo->prepare("
                SELECT c.id as cliente_id 
                FROM clientes c 
                LEFT JOIN asignaciones_clientes ac ON c.id = ac.cliente_id AND ac.estado != 'no_interesado'
                WHERE c.carga_excel_id = ? AND ac.cliente_id IS NULL
            ");
            $stmt->execute([$cargaId]);
            $clientesNoAsignados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Crear asignaciones individuales de clientes
            $asignacionesCreadas = 0;
            foreach ($clientesNoAsignados as $cliente) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO asignaciones_clientes (cliente_id, asesor_id, fecha_asignacion, estado) 
                    VALUES (?, ?, NOW(), 'asignado')
                ");
                if ($stmt->execute([$cliente['cliente_id'], $asesorId])) {
                    $asignacionesCreadas++;
                }
            }

            $this->pdo->commit();
            return $asignacionesCreadas;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al asignar asesor a base de datos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Libera un asesor de una base de datos
     */
    public function liberarAsesorDeBaseDatos($cargaId, $asesorId) {
        $this->pdo->beginTransaction();
        try {
            $asignacionesActualizadas = 0;

            if ($asesorId === null) {
                // Liberar todos los asesores de esta base de datos

                // 1. Desactivar asignaciones de base completa
                $stmt = $this->pdo->prepare("
                    UPDATE asignaciones_base_asesor
                    SET estado = 'inactiva'
                    WHERE carga_id = ? AND estado = 'activa'
                ");
                $stmt->execute([$cargaId]);

                // 2. Cambiar estado de asignaciones individuales de clientes a 'liberado'
                $stmt = $this->pdo->prepare("
                    UPDATE asignaciones_clientes ac
                    INNER JOIN clientes c ON ac.cliente_id = c.id
                    SET ac.estado = 'liberado'
                    WHERE c.carga_excel_id = ? AND ac.estado = 'asignado'
                ");
                if ($stmt->execute([$cargaId])) {
                    $asignacionesActualizadas = $stmt->rowCount();
                }
            } else {
                // Liberar solo el asesor específico

                // 1. Desactivar asignación de base completa
                $stmt = $this->pdo->prepare("
                    UPDATE asignaciones_base_asesor
                    SET estado = 'inactiva'
                    WHERE carga_id = ? AND asesor_id = ? AND estado = 'activa'
                ");
                $stmt->execute([$cargaId, $asesorId]);

                // 2. Cambiar estado de asignaciones individuales de clientes a 'liberado'
                $stmt = $this->pdo->prepare("
                    UPDATE asignaciones_clientes ac
                    INNER JOIN clientes c ON ac.cliente_id = c.id
                    SET ac.estado = 'liberado'
                    WHERE c.carga_excel_id = ? AND ac.asesor_id = ? AND ac.estado = 'asignado'
                ");
                if ($stmt->execute([$cargaId, $asesorId])) {
                    $asignacionesActualizadas = $stmt->rowCount();
                }
            }

            $this->pdo->commit();
            return $asignacionesActualizadas;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al liberar asesor de base de datos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene asesores disponibles para asignar
     */
    public function getAsesoresDisponibles($coordinadorId) {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.nombre_completo, u.usuario
            FROM usuarios u
            INNER JOIN asignaciones_asesor_coordinador aac ON u.id = aac.asesor_id
            WHERE aac.coordinador_id = ? AND aac.estado = 'Activa' AND u.rol = 'asesor'
            ORDER BY u.nombre_completo
        ");
        $stmt->execute([$coordinadorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
