<?php
// Archivo: models/ActividadProductoModel.php
// Modelo para el registro automático de actividades sobre productos

class ActividadProductoModel {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Registra una actividad automáticamente
     */
    public function registrarActividad($datos) {
        try {
            $sql = "INSERT INTO actividades_productos (
                        historial_gestion_id, cliente_id, asesor_id, producto_id, 
                        numero_obligacion, tipo_actividad, accion_realizada, 
                        detalles_especificos, estado_anterior, estado_nuevo,
                        ip_address, user_agent, metadata
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                $datos['historial_gestion_id'] ?? null,
                $datos['cliente_id'],
                $datos['asesor_id'],
                $datos['producto_id'] ?? null,
                $datos['numero_obligacion'] ?? null,
                $datos['tipo_actividad'],
                $datos['accion_realizada'],
                $datos['detalles_especificos'] ?? null,
                $datos['estado_anterior'] ?? null,
                $datos['estado_nuevo'] ?? null,
                $datos['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
                $datos['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
                $datos['metadata'] ? json_encode($datos['metadata']) : null
            ];
            
            $success = $stmt->execute($params);
            
            if ($success) {
                return $this->pdo->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error en registrarActividad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registra un log del sistema en tiempo real
     */
    public function registrarLogSistema($datos) {
        try {
            $sql = "INSERT INTO logs_sistema_tiempo_real (
                        tipo_evento, entidad_afectada, entidad_id, usuario_id,
                        accion, descripcion, datos_anteriores, datos_nuevos,
                        ip_address, session_id
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                $datos['tipo_evento'],
                $datos['entidad_afectada'],
                $datos['entidad_id'],
                $datos['usuario_id'],
                $datos['accion'],
                $datos['descripcion'] ?? null,
                isset($datos['datos_anteriores']) && $datos['datos_anteriores'] ? json_encode($datos['datos_anteriores']) : null,
                isset($datos['datos_nuevos']) && $datos['datos_nuevos'] ? json_encode($datos['datos_nuevos']) : null,
                $datos['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
                $datos['session_id'] ?? session_id()
            ];
            
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            error_log("Error en registrarLogSistema: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el historial de actividades de un producto específico
     */
    public function getActividadesProducto($productoId, $clienteId = null) {
        try {
            $sql = "SELECT 
                        ap.*, 
                        u.nombre_completo as asesor_nombre,
                        c.nombre as cliente_nombre,
                        hg.tipo_gestion,
                        hg.resultado,
                        hg.fecha_gestion
                    FROM actividades_productos ap
                    JOIN usuarios u ON ap.asesor_id = u.id
                    JOIN clientes c ON ap.cliente_id = c.id
                    LEFT JOIN historial_gestion hg ON ap.historial_gestion_id = hg.id
                    WHERE ap.producto_id = ?";
            
            $params = [$productoId];
            
            if ($clienteId) {
                $sql .= " AND ap.cliente_id = ?";
                $params[] = $clienteId;
            }
            
            $sql .= " ORDER BY ap.timestamp_actividad DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en getActividadesProducto: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene el historial completo de actividades de un cliente
     */
    public function getActividadesCliente($clienteId, $limit = 50) {
        try {
            $sql = "SELECT 
                        ap.*, 
                        u.nombre_completo as asesor_nombre,
                        c.nombre as cliente_nombre,
                        hg.tipo_gestion,
                        hg.resultado,
                        hg.fecha_gestion
                    FROM actividades_productos ap
                    JOIN usuarios u ON ap.asesor_id = u.id
                    JOIN clientes c ON ap.cliente_id = c.id
                    LEFT JOIN historial_gestion hg ON ap.historial_gestion_id = hg.id
                    WHERE ap.cliente_id = ?
                    ORDER BY ap.timestamp_actividad DESC
                    LIMIT ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$clienteId, $limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en getActividadesCliente: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene actividades en tiempo real (últimas 24 horas)
     */
    public function getActividadesTiempoReal($asesorId = null, $limit = 100) {
        try {
            // Asegurar que limit sea un entero
            $limit = (int)$limit;
            $asesorId = $asesorId ? (int)$asesorId : null;
            
            $sql = "SELECT 
                        ap.*, 
                        u.nombre_completo as asesor_nombre,
                        c.nombre as cliente_nombre,
                        hg.tipo_gestion,
                        hg.resultado,
                        hg.fecha_gestion
                    FROM actividades_productos ap
                    JOIN usuarios u ON ap.asesor_id = u.id
                    JOIN clientes c ON ap.cliente_id = c.id
                    LEFT JOIN historial_gestion hg ON ap.historial_gestion_id = hg.id
                    WHERE ap.timestamp_actividad >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            
            $params = [];
            
            if ($asesorId) {
                $sql .= " AND ap.asesor_id = ?";
                $params[] = $asesorId;
            }
            
            $sql .= " ORDER BY ap.timestamp_actividad DESC LIMIT " . $limit;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en getActividadesTiempoReal: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
            return [];
        }
    }
    
    /**
     * Obtiene estadísticas de actividades por tipo
     */
    public function getEstadisticasActividades($asesorId, $periodo = 'dia') {
        try {
            $fechaInicio = $this->getFechaInicio($periodo);
            
            $sql = "SELECT 
                        tipo_actividad,
                        COUNT(*) as total_actividades,
                        COUNT(DISTINCT cliente_id) as clientes_afectados,
                        COUNT(DISTINCT producto_id) as productos_afectados
                    FROM actividades_productos
                    WHERE asesor_id = ? AND timestamp_actividad >= ?
                    GROUP BY tipo_actividad
                    ORDER BY total_actividades DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asesorId, $fechaInicio]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en getEstadisticasActividades: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene la fecha de inicio según el período
     */
    private function getFechaInicio($periodo) {
        switch ($periodo) {
            case 'hora':
                return date('Y-m-d H:00:00');
            case 'dia':
                return date('Y-m-d 00:00:00');
            case 'semana':
                return date('Y-m-d 00:00:00', strtotime('-7 days'));
            case 'mes':
                return date('Y-m-01 00:00:00');
            default:
                return date('Y-m-d 00:00:00');
        }
    }
    
    /**
     * Registra automáticamente una gestión de producto
     */
    public function registrarGestionProducto($historialGestionId, $clienteId, $asesorId, $datosGestion) {
        $actividad = [
            'historial_gestion_id' => $historialGestionId,
            'cliente_id' => $clienteId,
            'asesor_id' => $asesorId,
            'producto_id' => $datosGestion['obligacion_id'] ?? null,
            'numero_obligacion' => $datosGestion['numero_obligacion'] ?? null,
            'tipo_actividad' => 'gestion',
            'accion_realizada' => 'Gestión de producto realizada',
            'detalles_especificos' => $datosGestion['comentarios'] ?? null,
            'estado_anterior' => null,
            'estado_nuevo' => $datosGestion['resultado'] ?? null,
            'metadata' => [
                'tipo_gestion' => $datosGestion['tipo_gestion'] ?? null,
                'forma_contacto' => $datosGestion['forma_contacto'] ?? null,
                'producto_gestionado' => $datosGestion['producto_gestionado'] ?? null,
                'monto_obligacion' => $datosGestion['monto_obligacion'] ?? null
            ]
        ];
        
        return $this->registrarActividad($actividad);
    }
    
    /**
     * Registra automáticamente la autorización de canales
     */
    public function registrarCanalesAutorizados($historialGestionId, $clienteId, $asesorId, $canales) {
        $actividad = [
            'historial_gestion_id' => $historialGestionId,
            'cliente_id' => $clienteId,
            'asesor_id' => $asesorId,
            'tipo_actividad' => 'canal_autorizado',
            'accion_realizada' => 'Canales de comunicación autorizados',
            'detalles_especificos' => implode(', ', $canales),
            'metadata' => [
                'canales_autorizados' => $canales,
                'total_canales' => count($canales)
            ]
        ];
        
        return $this->registrarActividad($actividad);
    }
}
?>
