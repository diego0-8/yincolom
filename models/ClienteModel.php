<?php
class ClienteModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getClientsByCargaId($cargaId, $limit = null, $offset = null) {
        $sql = "SELECT * FROM clientes WHERE carga_excel_id = ?";
        $params = [$cargaId];
        
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el número total de clientes para una carga específica.
     * @param int $cargaId El ID de la carga de Excel.
     * @return int El número total de clientes.
     */
    public function getTotalClientsByCargaId($cargaId) {
        $sql = "SELECT COUNT(*) AS total FROM clientes WHERE carga_excel_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cargaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Obtiene clientes por carga ID filtrando solo los del coordinador específico
     */
    public function getClientsByCargaIdAndCoordinador($cargaId, $coordinadorId, $limit = null, $offset = null) {
        $sql = "SELECT c.* FROM clientes c 
                JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                WHERE c.carga_excel_id = ? AND ce.usuario_coordinador_id = ?";
        $params = [$cargaId, $coordinadorId];
        
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el total de clientes por carga filtrando solo los del coordinador específico
     */
    public function getTotalClientsByCargaIdAndCoordinador($cargaId, $coordinadorId) {
        $sql = "SELECT COUNT(*) AS total FROM clientes c 
                JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                WHERE c.carga_excel_id = ? AND ce.usuario_coordinador_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cargaId, $coordinadorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene el total de clientes asignados por carga filtrando solo los del coordinador específico
     */
    public function getTotalClientsAsignadosByCargaIdAndCoordinador($cargaId, $coordinadorId) {
        $sql = "SELECT COUNT(*) AS total FROM clientes c 
                JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                WHERE c.carga_excel_id = ? AND ce.usuario_coordinador_id = ? AND c.asesor_id IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cargaId, $coordinadorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Obtiene clientes no asignados por carga filtrando solo los del coordinador específico
     * EXCLUYE clientes que ya fueron gestionados previamente (tienen historial de gestiones)
     */
    public function getUnassignedClientsByCargaAndCoordinador($cargaId, $coordinadorId) {
        $stmt = $this->pdo->prepare("SELECT c.* FROM clientes c 
                                    JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                                    WHERE c.carga_excel_id = ? 
                                    AND ce.usuario_coordinador_id = ? 
                                    AND c.asesor_id IS NULL
                                    AND NOT EXISTS (
                                        SELECT 1 FROM asignaciones_clientes ac
                                        JOIN historial_gestion hg ON ac.id = hg.asignacion_id
                                        WHERE ac.cliente_id = c.id
                                    )");
        $stmt->execute([$cargaId, $coordinadorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene clientes liberados que pueden ser reasignados (sin historial de gestiones)
     */
    public function getLiberatedClientsAvailableForReassignment($cargaId, $coordinadorId) {
        $stmt = $this->pdo->prepare("SELECT c.*, ac.asesor_id as asesor_original, ac.fecha_asignacion as fecha_liberacion
                                    FROM clientes c 
                                    JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                                    JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                                    WHERE c.carga_excel_id = ? 
                                    AND ce.usuario_coordinador_id = ? 
                                    AND c.asesor_id IS NULL
                                    AND ac.estado = 'liberado'
                                    AND NOT EXISTS (
                                        SELECT 1 FROM historial_gestion hg 
                                        WHERE hg.asignacion_id = ac.id
                                    )
                                    ORDER BY ac.fecha_asignacion DESC");
        $stmt->execute([$cargaId, $coordinadorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene el total de clientes asignados por asesor y carga específica
     */
    public function getTotalClientsByAsesorAndCarga($asesorId, $cargaId, $coordinadorId) {
        $sql = "SELECT COUNT(*) AS total FROM clientes c 
                JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                WHERE c.asesor_id = ? AND c.carga_excel_id = ? AND ce.usuario_coordinador_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $cargaId, $coordinadorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Busca clientes por término de búsqueda en una carga específica
     * Optimizado para millones de registros
     */
    public function buscarClientesPorTermino($cargaId, $searchTerm, $coordinadorId) {
        // Limpiar término de búsqueda
        $searchTerm = trim($searchTerm);
        if (empty($searchTerm)) {
            return [];
        }
        
        // Validar parámetros
        if (!is_numeric($cargaId) || !is_numeric($coordinadorId)) {
            return [];
        }
        
        // Si es un número (cédula o teléfono), buscar exacto primero
        if (is_numeric($searchTerm)) {
            $sql = "SELECT c.* FROM clientes c 
                    JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                    WHERE c.carga_excel_id = ? 
                    AND ce.usuario_coordinador_id = ?
                    AND (c.cedula = ? OR c.telefono = ? OR c.celular2 = ?)
                    ORDER BY 
                        CASE 
                            WHEN c.cedula = ? THEN 1
                            WHEN c.telefono = ? THEN 2
                            WHEN c.celular2 = ? THEN 3
                            ELSE 4
                        END,
                        c.nombre ASC
                    LIMIT 100";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $cargaId, 
                $coordinadorId, 
                $searchTerm, 
                $searchTerm, 
                $searchTerm,
                $searchTerm,
                $searchTerm,
                $searchTerm
            ]);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si encontramos resultados exactos, devolverlos
            if (!empty($results)) {
                return $results;
            }
        }
        
        // Búsqueda con LIKE para texto parcial
        $searchTermLike = '%' . $searchTerm . '%';
        
        // Usar FULLTEXT search si el término tiene más de 2 caracteres
        if (strlen($searchTerm) > 2) {
            $sql = "SELECT c.*, 
                           MATCH(c.nombre, c.cedula, c.telefono, c.celular2, c.email) 
                           AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                    FROM clientes c 
                    JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                    WHERE c.carga_excel_id = ? 
                    AND ce.usuario_coordinador_id = ?
                    AND MATCH(c.nombre, c.cedula, c.telefono, c.celular2, c.email) 
                        AGAINST(? IN NATURAL LANGUAGE MODE)
                    ORDER BY relevance DESC, c.nombre ASC
                    LIMIT 1000";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$searchTerm, $cargaId, $coordinadorId, $searchTerm]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si encontramos resultados con FULLTEXT, devolverlos
            if (!empty($results)) {
                return $results;
            }
        }
        
        // Fallback a búsqueda LIKE tradicional
        $sql = "SELECT c.* FROM clientes c 
                JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                WHERE c.carga_excel_id = ? 
                AND ce.usuario_coordinador_id = ?
                AND (c.nombre LIKE ? 
                     OR c.cedula LIKE ? 
                     OR c.telefono LIKE ? 
                     OR c.celular2 LIKE ? 
                     OR c.email LIKE ?)
                ORDER BY 
                    CASE 
                        WHEN c.cedula LIKE ? THEN 1
                        WHEN c.nombre LIKE ? THEN 2
                        WHEN c.telefono LIKE ? THEN 3
                        WHEN c.celular2 LIKE ? THEN 4
                        ELSE 5
                    END,
                    c.nombre ASC
                LIMIT 1000";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $cargaId, 
            $coordinadorId, 
            $searchTermLike, 
            $searchTermLike, 
            $searchTermLike, 
            $searchTermLike, 
            $searchTermLike,
            $searchTermLike,
            $searchTermLike,
            $searchTermLike,
            $searchTermLike
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Guarda los clientes en la base de datos a partir de un archivo Excel.
     * Se asume que los datos del array $clientes tienen la misma estructura que las columnas de la tabla.
     * @param int $cargaId El ID de la carga de Excel.
     * @param array $clientes Un array de arrays con los datos de los clientes.
     * @return bool Retorna true si el guardado fue exitoso, false en caso de error.
     */
    public function saveClientsFromExcel($cargaId, $clientes) {
        $this->pdo->beginTransaction();
        try {
            $sql = "INSERT INTO clientes (nombre, cedula, telefono, celular2, ciudad, carga_excel_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($clientes as $cliente) {
                $stmt->execute([
                    $cliente['nombre'], 
                    $cliente['cedula'], 
                    $cliente['telefono'], 
                    $cliente['celular2'], 
                    $cliente['ciudad'], 
                    $cargaId
                ]);
            }
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error al guardar clientes: " . $e->getMessage()); 
            return false;
        }
    }

    public function getUnassignedClientsByCarga($cargaId) {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE carga_excel_id = ? AND asesor_id IS NULL");
        $stmt->execute([$cargaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignClientsToAsesor($clienteIds, $asesorId) {
        if (empty($clienteIds)) return;
        
        $this->pdo->beginTransaction();
        try {
            // Actualizar la tabla clientes
            $ids = implode(',', array_map('intval', $clienteIds));
            $sql = "UPDATE clientes SET asesor_id = ? WHERE id IN ($ids)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asesorId]);
            
            // Crear registros en asignaciones_clientes
            $sql = "INSERT INTO asignaciones_clientes (asesor_id, cliente_id, estado, fecha_asignacion) VALUES (?, ?, 'asignado', NOW())";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($clienteIds as $clienteId) {
                // Verificar si ya existe la asignación
                $existing = $this->getAsignacionId($asesorId, $clienteId);
                if (!$existing) {
                    $stmt->execute([$asesorId, $clienteId]);
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error al asignar clientes: " . $e->getMessage());
            throw $e;
        }
    }

    public function getClienteById($clienteId) {
        $sql = "SELECT * FROM clientes WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clienteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAssignedClientsForAsesor($asesorId) {
        // Primero intentar obtener de asignaciones_clientes (método nuevo)
        $sql = "SELECT 
                    c.*, 
                    a.id as asignacion_id, 
                    a.estado as estado_gestion,
                    a.fecha_asignacion,
                    (SELECT COUNT(*) FROM historial_gestion hg WHERE hg.asignacion_id = a.id) as total_gestiones,
                    (SELECT MAX(hg.fecha_gestion) FROM historial_gestion hg WHERE hg.asignacion_id = a.id) as ultima_gestion,
                    (SELECT hg.resultado FROM historial_gestion hg WHERE hg.asignacion_id = a.id ORDER BY hg.fecha_gestion DESC LIMIT 1) as ultimo_resultado,
                    (SELECT hg.monto_venta FROM historial_gestion hg WHERE hg.asignacion_id = a.id ORDER BY hg.fecha_gestion DESC LIMIT 1) as monto_venta
                FROM asignaciones_clientes a
                JOIN clientes c ON a.cliente_id = c.id
                WHERE a.asesor_id = ? AND a.estado = 'asignado'
                ORDER BY a.fecha_asignacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        $clientesAsignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si no hay clientes en asignaciones_clientes, buscar directamente en clientes
        if (empty($clientesAsignaciones)) {
            $sql = "SELECT 
                        c.*, 
                        NULL as asignacion_id, 
                        c.estado_cliente as estado_gestion,
                        c.fecha_creacion as fecha_asignacion,
                        (SELECT COUNT(*) FROM historial_gestion hg WHERE hg.cliente_id = c.id) as total_gestiones,
                        (SELECT MAX(hg.fecha_gestion) FROM historial_gestion hg WHERE hg.cliente_id = c.id) as ultima_gestion,
                        (SELECT hg.resultado FROM historial_gestion hg WHERE hg.cliente_id = c.id ORDER BY hg.fecha_gestion DESC LIMIT 1) as ultimo_resultado,
                        (SELECT hg.monto_venta FROM historial_gestion hg WHERE hg.cliente_id = c.id ORDER BY hg.fecha_gestion DESC LIMIT 1) as monto_venta
                    FROM clientes c
                    WHERE c.asesor_id = ?
                    ORDER BY c.fecha_creacion DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asesorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $clientesAsignaciones;
    }

    /**
     * Obtiene el ID de la asignación de un cliente a un asesor.
     * @param int $asesorId El ID del asesor.
     * @param int $clienteId El ID del cliente.
     * @return int|false El ID de la asignación o false si no se encuentra.
     */
    public function getAsignacionId($asesorId, $clienteId) {
        $sql = "SELECT id FROM asignaciones_clientes WHERE asesor_id = ? AND cliente_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $clienteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : false;
    }
    
    public function getTotalClientesByAsesor($asesorId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM clientes WHERE asesor_id = ?");
        $stmt->execute([$asesorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene clientes asignados a un asesor específico
     */
    public function getClientesByAsesor($asesorId) {
        $stmt = $this->pdo->prepare("SELECT c.*, ac.id as asignacion_id, ac.estado as estado_gestion 
                                    FROM clientes c 
                                    LEFT JOIN asignaciones_clientes ac ON c.id = ac.cliente_id AND ac.asesor_id = c.asesor_id
                                    WHERE c.asesor_id = ? 
                                    ORDER BY c.id DESC");
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Traspasa un cliente de un asesor a otro
     */
    public function traspasarCliente($clienteId, $nuevoAsesorId, $asesorOrigenId) {
        $this->pdo->beginTransaction();
        try {
            // Verificar que el cliente pertenezca al asesor origen
            $cliente = $this->getClienteById($clienteId);
            if (!$cliente || $cliente['asesor_id'] != $asesorOrigenId) {
                throw new Exception("El cliente no pertenece al asesor origen");
            }
            
            // Actualizar la tabla clientes
            $sql = "UPDATE clientes SET asesor_id = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nuevoAsesorId, $clienteId]);
            
            // Actualizar o crear registro en asignaciones_clientes
            $sql = "UPDATE asignaciones_clientes SET asesor_id = ?, estado = 'asignado', fecha_asignacion = NOW() 
                    WHERE cliente_id = ? AND asesor_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nuevoAsesorId, $clienteId, $asesorOrigenId]);
            
            // Si no se actualizó ninguna fila, crear nueva asignación
            if ($stmt->rowCount() == 0) {
                $sql = "INSERT INTO asignaciones_clientes (asesor_id, cliente_id, estado, fecha_asignacion) 
                        VALUES (?, ?, 'asignado', NOW())";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nuevoAsesorId, $clienteId]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al traspasar cliente: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Libera un cliente de un asesor (lo deja sin asignar)
     */
    public function liberarCliente($clienteId, $asesorId) {
        $this->pdo->beginTransaction();
        try {
            // Verificar que el cliente pertenezca al asesor
            $cliente = $this->getClienteById($clienteId);
            if (!$cliente || $cliente['asesor_id'] != $asesorId) {
                throw new Exception("El cliente no pertenece al asesor especificado");
            }
            
            // Liberar el cliente
            $sql = "UPDATE clientes SET asesor_id = NULL WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$clienteId]);
            
            // Actualizar estado en asignaciones_clientes
            $sql = "UPDATE asignaciones_clientes SET estado = 'liberado', fecha_asignacion = NOW() 
                    WHERE cliente_id = ? AND asesor_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$clienteId, $asesorId]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al liberar cliente: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtiene un cliente específico verificando que pertenezca a una carga del coordinador
     */
    public function getClienteByIdAndCoordinador($clienteId, $coordinadorId) {
        $sql = "SELECT c.*, ce.usuario_coordinador_id as coordinador_id
                FROM clientes c
                JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                WHERE c.id = ? AND ce.usuario_coordinador_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clienteId, $coordinadorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un cliente por su cédula
     */
    public function getClienteByCedula($cedula) {
        $sql = "SELECT * FROM clientes WHERE cedula = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un cliente ya está en una carga específica
     */
    public function clienteYaEnCarga($clienteId, $cargaId) {
        $sql = "SELECT id FROM clientes WHERE id = ? AND carga_excel_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clienteId, $cargaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Agrega un cliente existente a una carga
     */
    public function agregarClienteACarga($cargaId, $clienteId) {
        $sql = "UPDATE clientes SET carga_excel_id = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$cargaId, $clienteId]);
    }

    /**
     * Agrega un cliente existente a una carga y actualiza su información si es más completa
     */
    public function agregarClienteACargaConActualizacion($cargaId, $clienteId, $nuevosDatos, $useTransaction = true) {
        // OPTIMIZACIÓN: Si useTransaction es false, no crear transacción individual (para procesos batch)
        if ($useTransaction) {
            $this->pdo->beginTransaction();
        }
        
        try {
            // Obtener datos actuales del cliente
            $sql = "SELECT * FROM clientes WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$clienteId]);
            $clienteActual = $stmt->fetch();
            
            if (!$clienteActual) {
                if ($useTransaction) {
                    $this->pdo->rollBack();
                }
                return false;
            }
            
            // Preparar datos para actualización
            $camposActualizar = [];
            $valores = [];
            
            // Verificar cada campo y actualizar si el nuevo dato es mejor
            $campos = ['nombre', 'telefono', 'celular2', 'email', 'direccion', 'ciudad'];
            
            foreach ($campos as $campo) {
                $valorActual = $clienteActual[$campo] ?? '';
                $valorNuevo = $nuevosDatos[$campo] ?? '';
                
                // Actualizar si el campo actual está vacío y el nuevo no
                if (empty($valorActual) && !empty($valorNuevo)) {
                    $camposActualizar[] = "$campo = ?";
                    $valores[] = $valorNuevo;
                }
                // O si el nuevo valor es más largo (más completo)
                elseif (!empty($valorNuevo) && strlen($valorNuevo) > strlen($valorActual)) {
                    $camposActualizar[] = "$campo = ?";
                    $valores[] = $valorNuevo;
                }
            }
            
            // Agregar carga_excel_id
            $camposActualizar[] = "carga_excel_id = ?";
            $valores[] = $cargaId;
            $valores[] = $clienteId;
            
            // Ejecutar actualización si hay campos para actualizar
            if (count($camposActualizar) > 1) { // Más de 1 porque siempre incluimos carga_excel_id
                $sql = "UPDATE clientes SET " . implode(', ', $camposActualizar) . " WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($valores);
            } else {
                // Solo actualizar carga_excel_id
                $sql = "UPDATE clientes SET carga_excel_id = ? WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$cargaId, $clienteId]);
            }
            
            if ($useTransaction) {
                $this->pdo->commit();
            }
            return true;
            
        } catch (Exception $e) {
            if ($useTransaction) {
                $this->pdo->rollBack();
            }
            error_log("Error updating client: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo cliente
     */
    public function crearCliente($datos) {
        $sql = "INSERT INTO clientes (nombre, cedula, telefono, celular2, email, direccion, ciudad, carga_excel_id, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([
            $datos['nombre'],
            $datos['cedula'],
            $datos['telefono'],
            $datos['celular2'] ?? null,
            $datos['email'] ?? null,
            $datos['direccion'] ?? null,
            $datos['ciudad'] ?? null,
            $datos['carga_excel_id'] ?? null
        ])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Obtiene el total de clientes únicos en toda la base de datos
     */
    public function getTotalClientesUnicos() {
        $sql = "SELECT COUNT(DISTINCT cedula) as total FROM clientes";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene clientes por asesor con filtros de fechas
     */
    public function getClientesByAsesorWithFilters($asesorId, $fechaInicio = null, $fechaFin = null) {
        $sql = "SELECT c.*, 
                       CASE WHEN hg.id IS NOT NULL THEN true ELSE false END as gestionado,
                       CASE WHEN hg.tipo_gestion IS NOT NULL THEN true ELSE false END as contactado,
                       COALESCE(hg.resultado, 'Sin tipificar') as tipificacion,
                       COALESCE(hg.comentarios, 'Sin observaciones') as observaciones,
                       hg.fecha_gestion,
                       hg.tipo_gestion,
                       hg.monto_venta,
                       hg.duracion_llamada,
                       hg.edad,
                       hg.num_personas,
                       hg.valor_cotizacion,
                       hg.whatsapp_enviado
                FROM clientes c
                JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                LEFT JOIN historial_gestion hg ON ac.id = hg.asignacion_id
                WHERE ac.asesor_id = ?";
        
        $params = [$asesorId];
        
        // Agregar filtros de fechas si están especificados
        if ($fechaInicio) {
            $sql .= " AND DATE(c.fecha_creacion) >= ?";
            $params[] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $sql .= " AND DATE(c.fecha_creacion) <= ?";
            $params[] = $fechaFin;
        }
        
        $sql .= " ORDER BY c.fecha_creacion DESC, hg.fecha_gestion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene clientes del coordinador con filtros
     */
    public function getClientesByCoordinadorWithFilters($coordinadorId, $fechaInicio = null, $fechaFin = null, $estadoCliente = null) {
        $sql = "SELECT c.* 
                FROM clientes c 
                JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                WHERE ce.usuario_coordinador_id = ?";
        
        $params = [$coordinadorId];
        
        // Agregar filtros de fechas si están especificados
        if ($fechaInicio) {
            $sql .= " AND DATE(c.fecha_creacion) >= ?";
            $params[] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $sql .= " AND DATE(c.fecha_creacion) <= ?";
            $params[] = $fechaFin;
        }
        
        // Agregar filtro por estado del cliente
        if ($estadoCliente) {
            $sql .= " AND c.estado_cliente = ?";
            $params[] = $estadoCliente;
        }
        
        $sql .= " ORDER BY c.fecha_creacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene el siguiente cliente no gestionado del asesor
     */
    public function getSiguienteClienteAsesor($asesorId) {
        $sql = "SELECT c.*, ac.id as asignacion_id
                FROM clientes c 
                JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                WHERE ac.asesor_id = ? 
                AND ac.estado = 'asignado'
                AND NOT EXISTS (
                    SELECT 1 FROM historial_gestion hg 
                    WHERE hg.asignacion_id = ac.id
                )
                ORDER BY ac.fecha_asignacion ASC
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualiza el estado de un cliente
     */
    public function actualizarEstadoCliente($clienteId, $nuevoEstado) {
        $sql = "UPDATE clientes SET estado_cliente = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nuevoEstado, $clienteId]);
    }
    
    /**
     * Actualiza múltiples campos de un cliente
     */
    public function actualizarCliente($clienteId, $datos) {
        if (empty($datos)) {
            return false;
        }
        
        $campos = [];
        $valores = [];
        
        foreach ($datos as $campo => $valor) {
            $campos[] = "$campo = ?";
            $valores[] = $valor;
        }
        
        $valores[] = $clienteId;
        
        $sql = "UPDATE clientes SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($valores);
    }
    
    /**
     * Obtiene las asignaciones de un asesor
     */
    public function getAsignacionesByAsesor($asesorId) {
        $sql = "SELECT ac.*, c.nombre, c.cedula, c.telefono, c.celular2
                FROM asignaciones_clientes ac
                JOIN clientes c ON ac.cliente_id = c.id
                WHERE ac.asesor_id = ?
                ORDER BY ac.fecha_asignacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene clientes asignados sin gestionar
     */
    public function getClientesAsignadosSinGestionar($asesorId) {
        $sql = "SELECT c.*, ac.id as asignacion_id, ac.fecha_asignacion, ac.estado,
                       false as gestionado,
                       false as contactado,
                       'Sin tipificar' as tipificacion,
                       'Pendiente de gestionar' as observaciones
                FROM clientes c
                JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                WHERE ac.asesor_id = ?
                AND NOT EXISTS (
                    SELECT 1 FROM historial_gestion hg 
                    WHERE hg.asignacion_id = ac.id
                )
                ORDER BY ac.fecha_asignacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene clientes del asesor con filtros avanzados
     */
    public function getClientesByAsesorWithAdvancedFilters($asesorId, $fechaInicio = null, $fechaFin = null, $filtroGestion = null, $filtroContacto = null, $filtroTipificacion = null) {
        $sql = "SELECT c.*, 
                       CASE WHEN hg.id IS NOT NULL THEN true ELSE false END as gestionado,
                       CASE WHEN hg.tipo_gestion IS NOT NULL THEN true ELSE false END as contactado,
                       COALESCE(hg.resultado, 'Sin tipificar') as tipificacion,
                       COALESCE(hg.comentarios, 'Sin observaciones') as observaciones,
                       hg.fecha_gestion,
                       hg.tipo_gestion,
                       hg.monto_venta,
                       hg.duracion_llamada,
                       hg.edad,
                       hg.num_personas,
                       hg.valor_cotizacion,
                       hg.whatsapp_enviado
                FROM clientes c
                JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                LEFT JOIN historial_gestion hg ON ac.id = hg.asignacion_id
                WHERE ac.asesor_id = ?";
        
        $params = [$asesorId];
        
        // Filtro por gestión
        if ($filtroGestion === 'gestionado') {
            $sql .= " AND hg.id IS NOT NULL";
        } elseif ($filtroGestion === 'no_gestionado') {
            $sql .= " AND hg.id IS NULL";
        }
        
        // Filtro por contacto
        if ($filtroContacto === 'contactado') {
            $sql .= " AND hg.tipo_gestion IS NOT NULL";
        } elseif ($filtroContacto === 'no_contactado') {
            $sql .= " AND (hg.tipo_gestion IS NULL OR hg.tipo_gestion = 'no-contactado')";
        }
        
        // Filtro por tipificación
        if ($filtroTipificacion && $filtroTipificacion !== 'todos') {
            $sql .= " AND hg.resultado = ?";
            $params[] = $filtroTipificacion;
        }
        
        // Filtros de fechas
        if ($fechaInicio) {
            $sql .= " AND DATE(c.fecha_creacion) >= ?";
            $params[] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $sql .= " AND DATE(c.fecha_creacion) <= ?";
            $params[] = $fechaFin;
        }
        
        $sql .= " ORDER BY c.fecha_creacion DESC, hg.fecha_gestion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un cliente por cédula en una carga específica
     */
    public function getClienteByCedulaAndCarga($cedula, $cargaId) {
        $sql = "SELECT * FROM clientes WHERE cedula = ? AND carga_excel_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cedula, $cargaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las cargas del coordinador con filtro por estado habilitado
     */
    public function getCargasByCoordinador($coordinadorId, $soloHabilitadas = true) {
        $sql = "SELECT * FROM cargas_excel WHERE usuario_coordinador_id = ?";
        $params = [$coordinadorId];
        
        if ($soloHabilitadas) {
            $sql .= " AND estado_habilitado = 'habilitado'";
        }
        
        $sql .= " ORDER BY fecha_cargue DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cambia el estado de una carga (habilitar/deshabilitar)
     */
    public function cambiarEstadoCarga($cargaId, $nuevoEstado) {
        $sql = "UPDATE cargas_excel SET estado_habilitado = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nuevoEstado, $cargaId]);
    }

    /**
     * Busca cargas por nombre con filtro por estado
     */
    public function buscarCargasPorNombre($coordinadorId, $terminoBusqueda, $soloHabilitadas = true) {
        $sql = "SELECT * FROM cargas_excel WHERE usuario_coordinador_id = ? AND nombre_cargue LIKE ?";
        $params = [$coordinadorId, '%' . $terminoBusqueda . '%'];
        
        if ($soloHabilitadas) {
            $sql .= " AND estado_habilitado = 'habilitado'";
        }
        
        $sql .= " ORDER BY fecha_cargue DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca clientes por número de teléfono
     * @param string $telefono El número de teléfono a buscar
     * @return array Lista de clientes encontrados
     */
    public function buscarPorTelefono($telefono) {
        $sql = "SELECT id, nombre, telefono, cedula, email, direccion 
                FROM clientes 
                WHERE telefono LIKE ? 
                ORDER BY nombre ASC 
                LIMIT 20";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['%' . $telefono . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca clientes por cédula de identidad
     * @param string $cedula La cédula a buscar
     * @return array Lista de clientes encontrados
     */
    public function buscarPorCedula($cedula) {
        $sql = "SELECT id, nombre, telefono, cedula, email, direccion 
                FROM clientes 
                WHERE cedula LIKE ? 
                ORDER BY nombre ASC 
                LIMIT 20";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['%' . $cedula . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una asignación temporal para un cliente cuando se gestiona desde bases asignadas
     * @param int $asesorId ID del asesor
     * @param int $clienteId ID del cliente
     * @return int ID de la asignación creada
     */
    public function createTemporaryAsignacion($asesorId, $clienteId) {
        $this->pdo->beginTransaction();
        try {
            // Crear registro en asignaciones_clientes
            $sql = "INSERT INTO asignaciones_clientes (asesor_id, cliente_id, estado, fecha_asignacion) 
                    VALUES (?, ?, 'asignado', NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asesorId, $clienteId]);
            
            $asignacionId = $this->pdo->lastInsertId();
            
            // Actualizar la tabla clientes para asignar el asesor
            $sql = "UPDATE clientes SET asesor_id = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asesorId, $clienteId]);
            
            $this->pdo->commit();
            return $asignacionId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al crear asignación temporal: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene una asignación por su ID
     */
    public function getAsignacionById($asignacionId) {
        try {
            $sql = "SELECT * FROM asignaciones_clientes WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asignacionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo asignación por ID: " . $e->getMessage());
            return false;
        }
    }
}
?>