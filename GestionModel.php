<?php 
class GestionModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getGestionByAsesorAndCliente($asesorId, $clienteId) {
        // Obtener historial completo del cliente visible para todos los asesores con acceso a la base
        $sql = "SELECT hg.id, hg.fecha_gestion, hg.tipo_gestion, hg.resultado, hg.comentarios, 
                       hg.monto_venta, hg.duracion_llamada, hg.edad, hg.num_personas, 
                       hg.valor_cotizacion, hg.whatsapp_enviado, hg.proxima_fecha, 
                       hg.forma_contacto, hg.obligacion_id, hg.producto_gestionado, 
                       hg.monto_obligacion, hg.numero_obligacion, hg.no_cuotas, 
                       hg.fecha_pago, hg.valor_cuota, hg.numero_cuota, hg.valor_acuerdo,
                       u.nombre_completo as asesor_nombre, u.id as asesor_id
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                JOIN usuarios u ON ac.asesor_id = u.id
                JOIN clientes c ON ac.cliente_id = c.id
                WHERE ac.cliente_id = ? 
                AND EXISTS (
                    SELECT 1 FROM asignaciones_base_asesor aba 
                    WHERE aba.asesor_id = ? AND aba.carga_id = c.carga_excel_id AND aba.estado = 'activa'
                )
                ORDER BY hg.fecha_gestion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clienteId, $asesorId]);
        $gestiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($gestiones)) {
            return $gestiones;
        }
        
        // Obtener todos los IDs de gestiones para optimizar consultas
        $gestionIds = array_column($gestiones, 'id');
        $placeholders = str_repeat('?,', count($gestionIds) - 1) . '?';
        
        // Obtener todos los canales autorizados en una sola consulta
        $canalesSql = "SELECT historial_gestion_id, canal_autorizado 
                       FROM canales_autorizados_gestion 
                       WHERE historial_gestion_id IN ($placeholders)";
        $canalesStmt = $this->pdo->prepare($canalesSql);
        $canalesStmt->execute($gestionIds);
        $canalesData = $canalesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agrupar canales por gestión
        $canalesPorGestion = [];
        foreach ($canalesData as $canal) {
            $canalesPorGestion[$canal['historial_gestion_id']][] = $canal['canal_autorizado'];
        }
        
        // Agregar canales autorizados y tipificación completa a cada gestión
        foreach ($gestiones as &$gestion) {
            // Obtener canales autorizados del array agrupado
            $gestion['canales_autorizados'] = $canalesPorGestion[$gestion['id']] ?? [];
            
            // Convertir código de tipificación a texto completo
            $gestion['tipificacion_completa'] = $this->getTipificacionCompleta($gestion['tipo_gestion'], $gestion['resultado']);
        }
        
        return $gestiones;
    }
    

    public function getGestionByAsesor($asesorId) {
        $sql = "SELECT hg.*, c.nombre as cliente_nombre, c.id as cliente_id FROM historial_gestion hg JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id JOIN clientes c ON ac.cliente_id = c.id WHERE ac.asesor_id = ? ORDER BY hg.fecha_gestion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createGestion($asignacionId, $tipo, $comentarios, $resultado = null, $monto = null, $duracion = null, $edad = null, $numPersonas = null, $valorCotizacion = null, $whatsappEnviado = null, $formaContacto = 'llamada', $obligacionId = null, $productoGestionado = null, $montoObligacion = null, $numeroObligacion = null, $noCuotas = null, $fechaPago = null, $valorCuota = null, $numeroCuota = null, $valorAcuerdo = null) {
        $sql = "INSERT INTO historial_gestion (asignacion_id, fecha_gestion, tipo_gestion, comentarios, resultado, monto_venta, duracion_llamada, edad, num_personas, valor_cotizacion, whatsapp_enviado, forma_contacto, obligacion_id, producto_gestionado, monto_obligacion, numero_obligacion, no_cuotas, fecha_pago, valor_cuota, numero_cuota, valor_acuerdo) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([$asignacionId, $tipo, $comentarios, $resultado, $monto, $duracion, $edad, $numPersonas, $valorCotizacion, $whatsappEnviado, $formaContacto, $obligacionId, $productoGestionado, $montoObligacion, $numeroObligacion, $noCuotas, $fechaPago, $valorCuota, $numeroCuota, $valorAcuerdo]);
        
        if ($result) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }
    
    /**
     * Guarda múltiples canales autorizados para una gestión
     */
    public function guardarCanalesAutorizados($historialGestionId, $canales) {
        try {
            // Eliminar canales existentes para esta gestión
            $sql = "DELETE FROM canales_autorizados_gestion WHERE historial_gestion_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$historialGestionId]);
            
            // Insertar nuevos canales
            if (!empty($canales)) {
                $sql = "INSERT INTO canales_autorizados_gestion (historial_gestion_id, canal_autorizado) VALUES (?, ?)";
                $stmt = $this->pdo->prepare($sql);
                
                foreach ($canales as $canal) {
                    $stmt->execute([$historialGestionId, $canal]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error guardando canales autorizados: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene los canales autorizados para una gestión
     */
    public function getCanalesAutorizados($historialGestionId) {
        try {
            $sql = "SELECT canal_autorizado FROM canales_autorizados_gestion WHERE historial_gestion_id = ? ORDER BY canal_autorizado";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$historialGestionId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Error obteniendo canales autorizados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Convierte códigos de tipificación a texto completo
     */
    public function getTipificacionCompleta($tipoGestion, $resultado) {
        $tipificaciones = [
            'hacer_llamada' => 'HACER LLAMADA',
            'recibir_llamada' => 'RECIBIR LLAMADA'
        ];
        
        $resultados = [
            '01' => '01. CANCELADA',
            '02' => '02. MEMORANDO CNC',
            '03' => '03. ACUERDO DE PAGO',
            '04' => '04. PAGO TOTAL',
            '05' => '05. YA PAGO',
            '06' => '06. PROMESA',
            '06.1' => '06.1 BANNER',
            '06.2' => '06.2 REFINANCIACION',
            '06.3' => '06.3 UNIFICACION',
            '06.4' => '06.4 NIVELACION O NORMALIZACION',
            '07' => '07. REPORTE DE PAGO',
            '08' => '08. ABONOS',
            '09' => '09. NEGOCIACION EN TRAMITE',
            '10' => '10. SEGUIM GESTION',
            '11' => '11. SEGUIMIENTO',
            '12' => '12. RENUENTE',
            '13' => '13. VOLUNTAD DE PAGO',
            '14' => '14. VOLVER A LLAMAR',
            '14.1' => '14.1 VOLVER A LLAMAR HOY',
            '15' => '15. LOCALIZADO',
            '16' => '16. CONTACTO CON TERCERO',
            '17' => '17. FALLECIDO',
            '18' => '18. QUEJA / RECLAMO',
            '19' => '19. NO CONTESTAN',
            '20' => '20. ACTUALIZACION DATOS',
            '21' => '21. MENSAJE',
            '22' => '22. CORREO-E',
            '23' => '23. LEY DE INSOLVENCIA',
            '24' => '24. NO LOCALIZADO',
            '25' => '25. NUMERO EQUIVOCADO',
            '26' => '26. WHATSAPP',
            '27' => '27. ABANDONO CHAT'
        ];
        
        $tipoTexto = $tipificaciones[$tipoGestion] ?? $tipoGestion;
        $resultadoTexto = $resultados[$resultado] ?? $resultado;
        
        return $tipoTexto . ' - ' . $resultadoTexto;
    }
    
    /**
     * Obtiene una gestión por su ID
     */
    public function getGestionById($gestionId) {
        try {
            $sql = "SELECT * FROM historial_gestion WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$gestionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo gestión por ID: " . $e->getMessage());
            return false;
        }
    }

    public function updateAsignacionStatus($asignacionId, $estado) {
        $sql = "UPDATE asignaciones_clientes SET estado = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$estado, $asignacionId]);
    }

    // MÉTODOS COMPLETOS PARA EL DASHBOARD DEL ASESOR
    
    public function getMetricasDashboard($asesorId, $periodo = 'dia') {
        $fechaInicio = $this->getFechaInicio($periodo);
        
        $sql = "SELECT 
                    COUNT(*) as total_llamadas,
                    COUNT(CASE WHEN hg.resultado IS NOT NULL THEN 1 END) as contactos_efectivos,
                    COUNT(CASE WHEN hg.resultado IN ('Venta Exitosa', 'VENTA INGRESADA', 'Agendado', 'Interesado') THEN 1 END) as ventas_exitosas,
                    COUNT(CASE WHEN hg.resultado IN ('Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica', 'Necesita Pensarlo') THEN 1 END) as ventas_fallidas,
                    COUNT(CASE WHEN hg.resultado IN ('No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado') THEN 1 END) as contactos_no_efectivos,
                    COUNT(CASE WHEN hg.resultado = 'Agenda Llamada de Seguimiento' THEN 1 END) as seguimientos_agendados,
                    COUNT(CASE WHEN hg.resultado IN ('Problema Técnico', 'Error en la Base de Datos', 'Cliente Molesto/Insultos') THEN 1 END) as errores_gestion,
                    AVG(hg.duracion_llamada) as tiempo_promedio_conversacion,
                    SUM(hg.monto_venta) as total_ventas_monto,
                    AVG(CASE WHEN hg.monto_venta > 0 THEN hg.monto_venta END) as promedio_venta,
                    COUNT(CASE WHEN hg.duracion_llamada > 0 THEN 1 END) as llamadas_con_duracion
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? AND hg.fecha_gestion >= ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $fechaInicio]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calcular métricas adicionales
        $resultado['tasa_conversion'] = $resultado['total_llamadas'] > 0 ? 
            round(($resultado['ventas_exitosas'] / $resultado['total_llamadas']) * 100, 2) : 0;
        
        $resultado['tasa_contacto_efectivo'] = $resultado['total_llamadas'] > 0 ? 
            round(($resultado['contactos_efectivos'] / $resultado['total_llamadas']) * 100, 2) : 0;
        
        $resultado['tiempo_promedio_conversacion'] = $resultado['tiempo_promedio_conversacion'] !== null ? 
            round($resultado['tiempo_promedio_conversacion'], 2) : 0;
        $resultado['promedio_venta'] = $resultado['promedio_venta'] !== null ? 
            round($resultado['promedio_venta'], 2) : 0;
        
        return $resultado;
    }

    public function getTipificacionesPorResultado($asesorId, $periodo = 'dia') {
        $fechaInicio = $this->getFechaInicio($periodo);
        
        $sql = "SELECT 
                    hg.resultado,
                    COUNT(*) as cantidad,
                    AVG(hg.duracion_llamada) as tiempo_promedio,
                    SUM(hg.monto_venta) as total_monto
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? AND hg.fecha_gestion >= ? AND hg.resultado IS NOT NULL
                GROUP BY hg.resultado
                ORDER BY cantidad DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $fechaInicio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLlamadasPorDia($asesorId, $periodo = 'semana') {
        $fechaInicio = $this->getFechaInicio($periodo);
        
        $sql = "SELECT 
                    DATE(hg.fecha_gestion) as fecha,
                    COUNT(*) as total_llamadas,
                    COUNT(CASE WHEN hg.resultado IS NOT NULL THEN 1 END) as contactos_efectivos,
                    COUNT(CASE WHEN hg.resultado IN ('Venta Exitosa', 'VENTA INGRESADA', 'Agendado', 'Interesado') THEN 1 END) as ventas_exitosas,
                    AVG(hg.duracion_llamada) as tiempo_promedio
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? AND hg.fecha_gestion >= ?
                GROUP BY DATE(hg.fecha_gestion)
                ORDER BY fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $fechaInicio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClientesConSeguimiento($asesorId) {
        $sql = "SELECT 
                    c.id, c.nombre, c.cedula, c.telefono, c.celular2,
                    hg.proxima_fecha, hg.resultado, hg.fecha_gestion, hg.comentarios,
                    ac.id as asignacion_id
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                JOIN clientes c ON ac.cliente_id = c.id 
                WHERE ac.asesor_id = ? AND hg.proxima_fecha IS NOT NULL 
                AND hg.proxima_fecha > NOW()
                ORDER BY hg.proxima_fecha ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene clientes con tipificación "volver a llamar" en su ÚLTIMA gestión
     * Solo incluye clientes que actualmente tienen esta tipificación
     */
    public function getClientesVolverALlamar($asesorId) {
        $sql = "SELECT 
                    c.id as cliente_id, 
                    c.nombre as cliente_nombre, 
                    c.cedula, 
                    c.telefono, 
                    c.celular2,
                    hg.proxima_fecha, 
                    hg.resultado, 
                    hg.fecha_gestion, 
                    hg.comentarios,
                    ac.id as asignacion_id
                FROM clientes c
                JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                JOIN (
                    SELECT 
                        ac2.cliente_id,
                        MAX(hg2.fecha_gestion) as ultima_fecha
                    FROM historial_gestion hg2
                    JOIN asignaciones_clientes ac2 ON hg2.asignacion_id = ac2.id
                    WHERE ac2.asesor_id = ?
                    GROUP BY ac2.cliente_id
                ) ultima ON c.id = ultima.cliente_id
                JOIN historial_gestion hg ON hg.asignacion_id = ac.id 
                    AND hg.fecha_gestion = ultima.ultima_fecha
                WHERE ac.asesor_id = ? 
                    AND (hg.resultado LIKE '%volver a llamar%' OR hg.resultado LIKE '%volver_llamar%')
                    AND hg.proxima_fecha IS NOT NULL
                    AND DATE(hg.proxima_fecha) = CURDATE()
                ORDER BY hg.proxima_fecha ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
        /**
     * Obtiene el total de clientes con tipificación "volver a llamar"
     */
    public function getTotalClientesVolverALlamar($asesorId) {
        $sql = "SELECT COUNT(DISTINCT c.id) as total
                FROM clientes c
                JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                JOIN (
                    SELECT 
                        ac2.cliente_id,
                        MAX(hg2.fecha_gestion) as ultima_fecha
                    FROM historial_gestion hg2
                    JOIN asignaciones_clientes ac2 ON hg2.asignacion_id = ac2.id
                    WHERE ac2.asesor_id = ?
                    GROUP BY ac2.cliente_id
                    ) ultima ON c.id = ultima.cliente_id
                JOIN historial_gestion hg ON hg.asignacion_id = ac.id 
                    AND hg.fecha_gestion = ultima.ultima_fecha
                WHERE ac.asesor_id = ? 
                    AND (hg.resultado LIKE '%volver a llamar%' OR hg.resultado LIKE '%volver_llamar%')
                    AND hg.proxima_fecha IS NOT NULL
                    AND DATE(hg.proxima_fecha) = CURDATE()";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $asesorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] ?? 0;
    }
    
    /**
     * Obtiene llamadas pendientes para el día actual (método alternativo más preciso)
     */
    public function getLlamadasPendientesHoy($asesorId) {
        $sql = "SELECT 
                    c.id as cliente_id, 
                    c.nombre as cliente_nombre, 
                    c.cedula, 
                    c.telefono, 
                    c.celular2,
                    hg.proxima_fecha, 
                    hg.resultado, 
                    hg.fecha_gestion, 
                    hg.comentarios,
                    ac.id as asignacion_id
                FROM clientes c
                JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                JOIN historial_gestion hg ON hg.asignacion_id = ac.id
                WHERE ac.asesor_id = ? 
                    AND hg.resultado = 'VOLVER A LLAMAR'
                    AND hg.fecha_gestion = (
                        SELECT MAX(hg2.fecha_gestion)
                        FROM historial_gestion hg2
                        JOIN asignaciones_clientes ac2 ON hg2.asignacion_id = ac2.id
                        WHERE ac2.cliente_id = c.id AND ac2.asesor_id = ?
                    )
                    AND (
                        (hg.proxima_fecha IS NOT NULL AND DATE(hg.proxima_fecha) = CURDATE())
                        OR
                        (hg.proxima_fecha IS NULL AND DATE(hg.fecha_gestion) = CURDATE())
                    )
                ORDER BY hg.fecha_gestion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene el total de llamadas pendientes para el día actual
     */
    public function getTotalLlamadasPendientesHoy($asesorId) {
        $sql = "SELECT COUNT(DISTINCT c.id) as total
                FROM clientes c
                JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                JOIN historial_gestion hg ON hg.asignacion_id = ac.id
                WHERE ac.asesor_id = ? 
                    AND hg.resultado = 'VOLVER A LLAMAR'
                    AND hg.fecha_gestion = (
                        SELECT MAX(hg2.fecha_gestion)
                        FROM historial_gestion hg2
                        JOIN asignaciones_clientes ac2 ON hg2.asignacion_id = ac2.id
                        WHERE ac2.cliente_id = c.id AND ac2.asesor_id = ?
                    )
                    AND (
                        (hg.proxima_fecha IS NOT NULL AND DATE(hg.proxima_fecha) = CURDATE())
                        OR
                        (hg.proxima_fecha IS NULL AND DATE(hg.fecha_gestion) = CURDATE())
                    )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $asesorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] ?? 0;
    }

    public function getUltimasGestiones($asesorId, $limite = 5) {
        $sql = "SELECT 
                    hg.id, hg.tipo_gestion, hg.resultado, hg.fecha_gestion, hg.duracion_llamada,
                    c.nombre as cliente_nombre, c.cedula, c.telefono, c.celular2
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                JOIN clientes c ON ac.cliente_id = c.id 
                WHERE ac.asesor_id = ? 
                ORDER BY hg.fecha_gestion DESC
                LIMIT " . $limite;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el número real de clientes gestionados por un asesor
     * (clientes que han sido contactados al menos una vez)
     */
    public function getClientesGestionados($asesorId) {
        $sql = "SELECT 
                    COUNT(DISTINCT ac.cliente_id) as total_clientes_gestionados
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? AND hg.fecha_gestion IS NOT NULL";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total_clientes_gestionados'] ?? 0;
    }

    /**
     * Obtiene el total recaudado real por un asesor
     * (suma de todas las ventas exitosas)
     */
    public function getTotalRecaudado($asesorId) {
        $sql = "SELECT 
                    COALESCE(SUM(hg.monto_venta), 0) as total_recaudado
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? AND hg.monto_venta > 0";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total_recaudado'] ?? 0;
    }

    /**
     * Obtiene el total de acuerdos de pago del día actual
     */
    public function getAcuerdosDelDia($asesorId) {
        $sql = "SELECT COUNT(*) as acuerdos_dia
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? 
                AND hg.resultado = 'ACUERDO DE PAGO'
                AND DATE(hg.fecha_gestion) = CURDATE()";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['acuerdos_dia'] ?? 0;
    }

    /**
     * MÉTODO OPTIMIZADO: Obtiene todas las métricas del dashboard en una sola consulta
     * Optimizado para manejar más de 10,000 clientes
     */
    public function getMetricasDashboardOptimizadas($asesorId, $periodo = 'dia') {
        $fechaInicio = $this->getFechaInicio($periodo);
        
        // Consulta optimizada con índices y agregaciones eficientes
        $sql = "SELECT 
                    -- Clientes gestionados (únicos)
                    COUNT(DISTINCT ac.cliente_id) as total_clientes_gestionados,
                    
                    -- Total recaudado
                    COALESCE(SUM(hg.monto_venta), 0) as total_recaudado,
                    
                    -- Métricas por período
                    COUNT(CASE WHEN hg.fecha_gestion >= ? THEN 1 END) as gestiones_periodo,
                    COUNT(CASE WHEN hg.fecha_gestion >= ? AND hg.resultado IS NOT NULL THEN 1 END) as contactos_efectivos_periodo,
                    
                    -- Ventas por período
                    COUNT(CASE WHEN hg.fecha_gestion >= ? AND hg.resultado IN ('Venta Exitosa', 'VENTA INGRESADA', 'Agendado', 'Interesado') THEN 1 END) as ventas_exitosas_periodo,
                    COALESCE(SUM(CASE WHEN hg.fecha_gestion >= ? AND hg.monto_venta > 0 THEN hg.monto_venta END), 0) as total_ventas_periodo,
                    
                    -- Tipificaciones por período
                    COUNT(CASE WHEN hg.fecha_gestion >= ? AND hg.resultado = 'VOLVER A LLAMAR' THEN 1 END) as seguimientos_agendados_periodo,
                    COUNT(CASE WHEN hg.fecha_gestion >= ? AND hg.resultado IN ('No Contesta', 'Número Equivocado', 'Buzón de Voz') THEN 1 END) as contactos_no_efectivos_periodo,
                    
                    -- Tiempo promedio
                    AVG(CASE WHEN hg.fecha_gestion >= ? AND hg.duracion_llamada > 0 THEN hg.duracion_llamada END) as tiempo_promedio_periodo
                    
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $fechaInicio, $fechaInicio, $fechaInicio, $fechaInicio, 
            $fechaInicio, $fechaInicio, $fechaInicio, $asesorId
        ]);
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Agregar métricas calculadas
        $resultado['tasa_contacto_efectivo'] = $resultado['gestiones_periodo'] > 0 
            ? round(($resultado['contactos_efectivos_periodo'] / $resultado['gestiones_periodo']) * 100, 2) 
            : 0;
            
        $resultado['tasa_ventas'] = $resultado['contactos_efectivos_periodo'] > 0 
            ? round(($resultado['ventas_exitosas_periodo'] / $resultado['contactos_efectivos_periodo']) * 100, 2) 
            : 0;
        
        return $resultado;
    }

    /**
     * Obtiene el total de acuerdos de pago realizados por un asesor
     */
    public function getTotalAcuerdosPago($asesorId) {
        $sql = "SELECT COUNT(*) as total_acuerdos
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? 
                AND hg.resultado = 'ACUERDO DE PAGO'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total_acuerdos'] ?? 0;
    }

    /**
     * Obtiene el total recaudado por acuerdos de pago (valor de cuotas)
     */
    public function getTotalRecaudadoAcuerdos($asesorId) {
        $sql = "SELECT COALESCE(SUM(hg.valor_cuota), 0) as total_recaudado_acuerdos
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? 
                AND hg.resultado = 'ACUERDO DE PAGO'
                AND hg.valor_cuota > 0";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total_recaudado_acuerdos'] ?? 0;
    }

    public function getEstadisticasPorTipoVenta($asesorId, $periodo = 'dia') {
        $fechaInicio = $this->getFechaInicio($periodo);
        
        $sql = "SELECT 
                    hg.resultado,
                    COUNT(*) as cantidad,
                    AVG(hg.monto_venta) as monto_promedio,
                    SUM(hg.monto_venta) as monto_total,
                    AVG(hg.duracion_llamada) as tiempo_promedio
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? AND hg.fecha_gestion >= ? 
                AND hg.resultado IN ('Venta Exitosa', 'VENTA INGRESADA', 'Agendado', 'Interesado')
                GROUP BY hg.resultado
                ORDER BY cantidad DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $fechaInicio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticasPorRechazo($asesorId, $periodo = 'dia') {
        $fechaInicio = $this->getFechaInicio($periodo);
        
        $sql = "SELECT 
                    hg.resultado,
                    COUNT(*) as cantidad,
                    AVG(hg.duracion_llamada) as tiempo_promedio
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? AND hg.fecha_gestion >= ? 
                AND hg.resultado IN ('Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica', 'Necesita Pensarlo')
                GROUP BY hg.resultado
                ORDER BY cantidad DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $fechaInicio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticasContactosNoEfectivos($asesorId, $periodo = 'dia') {
        $fechaInicio = $this->getFechaInicio($periodo);
        
        $sql = "SELECT 
                    hg.resultado,
                    COUNT(*) as cantidad,
                    AVG(hg.duracion_llamada) as tiempo_promedio
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? AND hg.fecha_gestion >= ? 
                AND hg.resultado IN ('No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado')
                GROUP BY hg.resultado
                ORDER BY cantidad DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $fechaInicio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductividadPorHora($asesorId, $periodo = 'dia') {
        $fechaInicio = $this->getFechaInicio($periodo);
        
        $sql = "SELECT 
                    HOUR(hg.fecha_gestion) as hora,
                    COUNT(*) as total_llamadas,
                    COUNT(CASE WHEN hg.resultado IS NOT NULL THEN 1 END) as contactos_efectivos,
                    AVG(hg.duracion_llamada) as tiempo_promedio
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? AND hg.fecha_gestion >= ?
                GROUP BY HOUR(hg.fecha_gestion)
                ORDER BY hora ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $fechaInicio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Obtiene gestiones de un asesor en un rango de fechas específico
     * CORREGIDO para usar la estructura real de la tabla historial_gestion
     */
    public function getGestionByAsesorAndFechas($asesorId, $fechaInicio, $fechaFin) {
        try {
            // Usar la estructura correcta con asignaciones_clientes
            $sql = "SELECT hg.*, c.nombre as cliente_nombre, c.cedula, c.telefono, c.celular2,
                           ac.asesor_id, ac.estado as estado_asignacion
                    FROM historial_gestion hg
                    JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                    JOIN clientes c ON ac.cliente_id = c.id
                    WHERE ac.asesor_id = ? 
                    AND DATE(hg.fecha_gestion) BETWEEN ? AND ?
                    ORDER BY hg.fecha_gestion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asesorId, $fechaInicio, $fechaFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en getGestionByAsesorAndFechas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene gestiones de un asesor en un rango de fechas específico con información de base de datos
     */
    public function getGestionByAsesorAndFechasConBaseDatos($asesorId, $fechaInicio, $fechaFin) {
        try {
            // Usar la estructura correcta con asignaciones_clientes e incluir información de base de datos
            $sql = "SELECT hg.*, c.nombre as cliente_nombre, c.cedula, c.telefono, c.celular2,
                           ac.asesor_id, ac.estado as estado_asignacion,
                           ce.nombre_cargue as base_datos_nombre, ce.fecha_cargue as base_datos_fecha
                    FROM historial_gestion hg
                    JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                    JOIN clientes c ON ac.cliente_id = c.id
                    LEFT JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                    WHERE ac.asesor_id = ? 
                    AND DATE(hg.fecha_gestion) BETWEEN ? AND ?
                    ORDER BY hg.fecha_gestion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asesorId, $fechaInicio, $fechaFin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en getGestionByAsesorAndFechasConBaseDatos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene gestiones de un asesor con filtros avanzados del modal
     * CORREGIDO para usar la estructura real de la tabla historial_gestion
     */
    public function getGestionByAsesorAndFechasConFiltros($asesorId, $fechaInicio, $fechaFin, $filtros = []) {
        try {
            // Si el filtro es "gestionado", solo mostrar clientes con gestiones reales
            if (!empty($filtros['gestion']) && $filtros['gestion'] === 'gestionado') {
                $sql = "SELECT hg.*, c.nombre as cliente_nombre, c.cedula, c.telefono, c.celular2,
                               c.fecha_creacion, ac.estado as estado_asignacion
                        FROM historial_gestion hg
                        JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                        JOIN clientes c ON ac.cliente_id = c.id
                        WHERE ac.asesor_id = ?";
                $params = [$asesorId];
                
                // Filtro de fechas de gestión
                if ($fechaInicio && $fechaFin) {
                    $sql .= " AND DATE(hg.fecha_gestion) BETWEEN ? AND ?";
                    $params[] = $fechaInicio;
                    $params[] = $fechaFin;
                }
            } 
            // Si el filtro es "no_gestionado", mostrar clientes asignados sin gestiones
            elseif (!empty($filtros['gestion']) && $filtros['gestion'] === 'no_gestionado') {
                $sql = "SELECT NULL as id, c.nombre as cliente_nombre, c.cedula, c.telefono, c.celular2,
                               c.fecha_creacion, ac.estado as estado_asignacion,
                               NULL as fecha_gestion, NULL as tipo_gestion, NULL as resultado, 
                               NULL as comentarios, NULL as monto_venta, NULL as duracion_llamada
                        FROM asignaciones_clientes ac
                        JOIN clientes c ON ac.cliente_id = c.id
                        WHERE ac.asesor_id = ? AND ac.estado = 'asignado'
                        AND NOT EXISTS (
                            SELECT 1 FROM historial_gestion hg2 
                            WHERE hg2.asignacion_id = ac.id
                        )";
                $params = [$asesorId];
            }
            // Si no hay filtro de gestión, mostrar todos los clientes asignados
            else {
                $sql = "SELECT hg.*, c.nombre as cliente_nombre, c.cedula, c.telefono, c.celular2,
                               c.fecha_creacion, ac.estado as estado_asignacion
                        FROM asignaciones_clientes ac
                        JOIN clientes c ON ac.cliente_id = c.id
                        LEFT JOIN historial_gestion hg ON hg.asignacion_id = ac.id
                        WHERE ac.asesor_id = ? AND ac.estado = 'asignado'";
                $params = [$asesorId];
                
                // Filtro de fechas de gestión
                if ($fechaInicio && $fechaFin) {
                    $sql .= " AND (hg.fecha_gestion IS NULL OR DATE(hg.fecha_gestion) BETWEEN ? AND ?)";
                    $params[] = $fechaInicio;
                    $params[] = $fechaFin;
                }
            }
            
            // Aplicar filtros adicionales solo si no es "no_gestionado"
            if (empty($filtros['gestion']) || $filtros['gestion'] !== 'no_gestionado') {
                // Filtro de contacto (contactado, no contactado)
                if (!empty($filtros['contacto'])) {
                    if ($filtros['contacto'] === 'contactado') {
                        $sql .= " AND hg.resultado IS NOT NULL AND hg.resultado != ''";
                    } elseif ($filtros['contacto'] === 'no_contactado') {
                        $sql .= " AND (hg.resultado IS NULL OR hg.resultado = '')";
                    }
                }
                
                // Filtro de tipificación específica
                if (!empty($filtros['tipificacion']) && $filtros['tipificacion'] !== 'todos') {
                    $sql .= " AND hg.resultado = ?";
                    $params[] = $filtros['tipificacion'];
                }
                
                // Filtro de tipificación específica (usando valores exactos de la base de datos)
                if (!empty($filtros['tipificacion_especifica']) && $filtros['tipificacion_especifica'] !== 'todos') {
                    if ($filtros['tipificacion_especifica'] === 'sin_gestion') {
                        $sql .= " AND (hg.resultado IS NULL OR hg.resultado = '' OR hg.resultado = 'Sin gestión')";
                    } else {
                        // Usar el valor exacto de la base de datos
                        $sql .= " AND hg.resultado = ?";
                        $params[] = $filtros['tipificacion_especifica'];
                    }
                }
            }
            
            // Filtro de fechas de creación del cliente (aplicable a todos los casos)
            if (!empty($filtros['fecha_creacion_inicio'])) {
                $sql .= " AND DATE(c.fecha_creacion) >= ?";
                $params[] = $filtros['fecha_creacion_inicio'];
            }
            
            if (!empty($filtros['fecha_creacion_fin'])) {
                $sql .= " AND DATE(c.fecha_creacion) <= ?";
                $params[] = $filtros['fecha_creacion_fin'];
            }
            
            // Ordenar según el tipo de consulta
            if (!empty($filtros['gestion']) && $filtros['gestion'] === 'no_gestionado') {
                $sql .= " ORDER BY c.fecha_creacion DESC";
            } else {
                $sql .= " ORDER BY hg.fecha_gestion DESC";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en getGestionByAsesorAndFechasConFiltros: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene gestiones filtradas para el coordinador con múltiples criterios
     * CORREGIDO para usar la estructura real de la tabla historial_gestion
     */
    public function getGestionFiltrada($coordinadorId, $fechaInicio, $fechaFin, $asesorId = null, $resultado = null, $tipoGestion = null) {
        $sql = "SELECT hg.*, c.nombre as cliente_nombre, c.cedula, c.telefono, c.celular2,
                       u.nombre_completo as asesor_nombre, ac.estado as estado_asignacion
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                JOIN clientes c ON ac.cliente_id = c.id 
                JOIN usuarios u ON ac.asesor_id = u.id 
                JOIN asignaciones_asesor_coordinador aac ON ac.asesor_id = aac.asesor_id
                WHERE aac.coordinador_id = ? 
                AND DATE(hg.fecha_gestion) BETWEEN ? AND ?";
        
        $params = [$coordinadorId, $fechaInicio, $fechaFin];
        
        if ($asesorId) {
            $sql .= " AND ac.asesor_id = ?";
            $params[] = $asesorId;
        }
        
        if ($resultado) {
            $sql .= " AND hg.resultado = ?";
            $params[] = $resultado;
        }
        
        if ($tipoGestion) {
            $sql .= " AND hg.tipo_gestion = ?";
            $params[] = $tipoGestion;
        }
        
        $sql .= " ORDER BY hg.fecha_gestion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crea una nueva gestión en la tabla historial_gestion
     * CORREGIDO para usar solo los campos que realmente existen en la tabla
     */
    public function crearGestion($gestionData) {
        try {
            // Validar que los campos obligatorios estén presentes
            if (empty($gestionData['asignacion_id']) || empty($gestionData['tipo_gestion']) || empty($gestionData['comentarios'])) {
                throw new Exception("Campos obligatorios faltantes para crear la gestión");
            }
            
            // Iniciar transacción para asegurar consistencia
            $this->pdo->beginTransaction();
            
            // Usar solo los campos que realmente existen en la tabla historial_gestion
            $sql = "INSERT INTO historial_gestion (
                        asignacion_id, fecha_gestion, tipo_gestion, comentarios, resultado,
                        monto_venta, duracion_llamada, edad, num_personas, 
                        valor_cotizacion, whatsapp_enviado, proxima_fecha, forma_contacto,
                        obligacion_id, producto_gestionado, monto_obligacion, numero_obligacion,
                        no_cuotas, fecha_pago, valor_cuota, numero_cuota, valor_total, valor_acuerdo
                    ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            
            // Preparar los parámetros en el orden correcto según la estructura real de la tabla
            $params = [
                $gestionData['asignacion_id'],
                $gestionData['tipo_gestion'],
                $gestionData['comentarios'],
                $gestionData['resultado'] ?? null,
                $gestionData['monto_venta'] ?? null,
                $gestionData['duracion_llamada'] ?? null,
                $gestionData['edad'] ?? $gestionData['edad_cliente'] ?? null,
                $gestionData['num_personas'] ?? null,
                $gestionData['valor_cotizacion'] ?? null,
                $gestionData['whatsapp_enviado'] ?? null,
                $gestionData['proxima_fecha'] ?? null,
                $gestionData['forma_contacto'] ?? 'llamada',
                $gestionData['obligacion_id'] ?? null,
                $gestionData['producto_gestionado'] ?? null,
                $gestionData['monto_obligacion'] ?? null,
                $gestionData['numero_obligacion'] ?? null,
                $gestionData['no_cuotas'] ?? null,
                $gestionData['fecha_pago'] ?? null,
                $gestionData['valor_cuota'] ?? null,
                $gestionData['numero_cuota'] ?? null,
                $gestionData['valor_total'] ?? null,
                $gestionData['valor_acuerdo'] ?? null
            ];
            
            // Ejecutar la consulta
            $success = $stmt->execute($params);
            
            if (!$success) {
                $this->pdo->rollBack();
                throw new Exception("Error al ejecutar la consulta SQL");
            }
            
            $historialGestionId = $this->pdo->lastInsertId();
            
            // Registrar actividad automáticamente
            $this->registrarActividadAutomatica($historialGestionId, $gestionData);
            
            // Confirmar transacción
            $this->pdo->commit();
            
            return $historialGestionId;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error en crearGestion: " . $e->getMessage());
            error_log("Datos recibidos: " . json_encode($gestionData));
            throw $e;
        }
    }

    /**
     * Método de prueba simplificado para crear gestiones
     * Usa solo las columnas que realmente existen en la tabla
     */
    public function crearGestionSimple($asignacionId, $tipoGestion, $comentarios, $resultado = null) {
        try {
            $sql = "INSERT INTO historial_gestion (asignacion_id, fecha_gestion, tipo_gestion, comentarios, resultado) VALUES (?, NOW(), ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            
            $success = $stmt->execute([$asignacionId, $tipoGestion, $comentarios, $resultado]);
            
            if (!$success) {
                throw new Exception("Error al ejecutar la consulta SQL");
            }
            
            return $this->pdo->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Error en crearGestionSimple: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene el total de llamadas realizadas por un asesor
     */
    public function getTotalLlamadasByAsesor($asesorId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM historial_gestion hg
                                    JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                                    WHERE ac.asesor_id = ?");
        $stmt->execute([$asesorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Obtiene el total de ventas realizadas por un asesor
     */
    public function getTotalVentasByAsesor($asesorId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM historial_gestion hg
                                    JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                                    WHERE ac.asesor_id = ? AND hg.resultado IN ('Venta Exitosa', 'VENTA INGRESADA', 'Agendado', 'Interesado')");
        $stmt->execute([$asesorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Obtiene el total de gestiones por resultado específico
     */
    public function getTotalGestionesByResultado($asesorId, $resultado) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM historial_gestion hg
                                    JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                                    WHERE ac.asesor_id = ? AND hg.resultado = ?");
        $stmt->execute([$asesorId, $resultado]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Obtiene la fecha de inicio según el período seleccionado
     */
    private function getFechaInicio($periodo) {
        switch ($periodo) {
            case 'total':
                // Para mostrar todas las gestiones históricas, retornar una fecha muy antigua
                return '1900-01-01 00:00:00';
            case 'dia':
                return date('Y-m-d 00:00:00');
            case 'semana':
                return date('Y-m-d 00:00:00', strtotime('-7 days'));
            case 'mes':
                return date('Y-m-d 00:00:00', strtotime('-30 days'));
            default:
                return date('Y-m-d 00:00:00');
        }
    }
    
    /**
     * Obtiene la fecha de inicio basada en fechas específicas
     */
    private function getFechaInicioFromFechas($fechaInicio, $fechaFin) {
        if ($fechaInicio && $fechaFin) {
            return $fechaInicio . ' 00:00:00';
        }
        return date('Y-m-d 00:00:00');
    }
    
    /**
     * Obtiene la fecha de fin basada en fechas específicas
     */
    private function getFechaFinFromFechas($fechaInicio, $fechaFin) {
        if ($fechaInicio && $fechaFin) {
            return $fechaFin . ' 23:59:59';
        }
        return date('Y-m-d 23:59:59');
    }

    /**
     * Obtiene métricas del equipo para el coordinador
     */
    public function getMetricasEquipo($coordinadorId, $periodo = 'dia') {
        $fechaInicio = $this->getFechaInicio($periodo);
        
        // Primero obtener el total de asesores asignados al coordinador
        $sqlAsesores = "SELECT COUNT(DISTINCT aac.asesor_id) as total_asesores
                        FROM asignaciones_asesor_coordinador aac 
                        WHERE aac.coordinador_id = ? AND aac.estado = 'Activa'";
        $stmtAsesores = $this->pdo->prepare($sqlAsesores);
        $stmtAsesores->execute([$coordinadorId]);
        $totalAsesores = $stmtAsesores->fetch(PDO::FETCH_ASSOC)['total_asesores'];
        
        // Obtener el total de clientes de TODAS las cargas del coordinador
        $sqlTotalClientes = "SELECT COUNT(*) as total_clientes
                            FROM clientes c
                            JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                            WHERE ce.usuario_coordinador_id = ?";
        $stmtTotalClientes = $this->pdo->prepare($sqlTotalClientes);
        $stmtTotalClientes->execute([$coordinadorId]);
        $totalClientes = $stmtTotalClientes->fetch(PDO::FETCH_ASSOC)['total_clientes'];
        
        // Luego obtener las métricas del equipo (solo gestiones)
        $sql = "SELECT 
                    COUNT(hg.id) as total_gestiones,
                    COUNT(CASE WHEN hg.resultado IS NOT NULL THEN 1 END) as contactos_efectivos,
                    COUNT(CASE WHEN hg.resultado IN ('Venta Exitosa', 'VENTA INGRESADA', 'Agendado', 'Interesado') THEN 1 END) as ventas_exitosas,
                    COUNT(CASE WHEN hg.resultado IN ('Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica', 'Necesita Pensarlo') THEN 1 END) as ventas_fallidas,
                    COUNT(CASE WHEN hg.resultado IN ('No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado') THEN 1 END) as contactos_no_efectivos,
                    AVG(hg.duracion_llamada) as tiempo_promedio_conversacion,
                    SUM(hg.monto_venta) as total_ventas_monto,
                    AVG(CASE WHEN hg.monto_venta > 0 THEN hg.monto_venta END) as promedio_venta
                FROM asignaciones_clientes ac
                JOIN asignaciones_asesor_coordinador aac ON ac.asesor_id = aac.asesor_id
                LEFT JOIN historial_gestion hg ON ac.id = hg.asignacion_id AND hg.fecha_gestion >= ?
                WHERE aac.coordinador_id = ? AND aac.estado = 'Activa'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fechaInicio, $coordinadorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Agregar el total de asesores y clientes calculados por separado
        $resultado['total_asesores'] = $totalAsesores;
        $resultado['total_clientes'] = $totalClientes;
        
        // Calcular métricas adicionales
        $resultado['tasa_conversion'] = $resultado['total_gestiones'] > 0 ? 
            round(($resultado['ventas_exitosas'] / $resultado['total_gestiones']) * 100, 2) : 0;
        
        $resultado['tasa_contacto_efectivo'] = $resultado['total_gestiones'] > 0 ? 
            round(($resultado['contactos_efectivos'] / $resultado['total_gestiones']) * 100, 2) : 0;
        
        $resultado['tiempo_promedio_conversacion'] = $resultado['tiempo_promedio_conversacion'] !== null ? 
            round($resultado['tiempo_promedio_conversacion'], 2) : 0;
        $resultado['promedio_venta'] = $resultado['promedio_venta'] !== null ? 
            round($resultado['promedio_venta'], 2) : 0;
        
        return $resultado;
    }
    
    /**
     * Obtiene métricas del equipo para el coordinador con fechas específicas
     */
    public function getMetricasEquipoConFechas($coordinadorId, $fechaInicio, $fechaFin) {
        $fechaInicioFormateada = $this->getFechaInicioFromFechas($fechaInicio, $fechaFin);
        $fechaFinFormateada = $this->getFechaFinFromFechas($fechaInicio, $fechaFin);
        
        // Primero obtener el total de asesores asignados al coordinador
        $sqlAsesores = "SELECT COUNT(DISTINCT aac.asesor_id) as total_asesores
                        FROM asignaciones_asesor_coordinador aac 
                        WHERE aac.coordinador_id = ? AND aac.estado = 'Activa'";
        $stmtAsesores = $this->pdo->prepare($sqlAsesores);
        $stmtAsesores->execute([$coordinadorId]);
        $totalAsesores = $stmtAsesores->fetch(PDO::FETCH_ASSOC)['total_asesores'];
        
        // Obtener el total de clientes de TODAS las cargas del coordinador
        $sqlTotalClientes = "SELECT COUNT(*) as total_clientes
                            FROM clientes c
                            JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                            WHERE ce.usuario_coordinador_id = ?";
        $stmtTotalClientes = $this->pdo->prepare($sqlTotalClientes);
        $stmtTotalClientes->execute([$coordinadorId]);
        $totalClientes = $stmtTotalClientes->fetch(PDO::FETCH_ASSOC)['total_clientes'];
        
        // Luego obtener las métricas del equipo con fechas específicas (solo gestiones)
        $sql = "SELECT 
                    COUNT(hg.id) as total_gestiones,
                    COUNT(CASE WHEN hg.resultado IS NOT NULL THEN 1 END) as contactos_efectivos,
                    COUNT(CASE WHEN hg.resultado IN ('Venta Exitosa', 'VENTA INGRESADA', 'Agendado', 'Interesado') THEN 1 END) as ventas_exitosas,
                    COUNT(CASE WHEN hg.resultado IN ('Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica', 'Necesita Pensarlo') THEN 1 END) as ventas_fallidas,
                    COUNT(CASE WHEN hg.resultado IN ('No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado') THEN 1 END) as contactos_no_efectivos,
                    AVG(hg.duracion_llamada) as tiempo_promedio_conversacion,
                    SUM(hg.monto_venta) as total_ventas_monto,
                    AVG(CASE WHEN hg.monto_venta > 0 THEN hg.monto_venta END) as promedio_venta
                FROM asignaciones_clientes ac
                JOIN asignaciones_asesor_coordinador aac ON ac.asesor_id = aac.asesor_id
                LEFT JOIN historial_gestion hg ON ac.id = hg.asignacion_id 
                    AND hg.fecha_gestion >= ? AND hg.fecha_gestion <= ?
                WHERE aac.coordinador_id = ? AND aac.estado = 'Activa'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fechaInicioFormateada, $fechaFinFormateada, $coordinadorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Agregar el total de asesores y clientes calculados por separado
        $resultado['total_asesores'] = $totalAsesores;
        $resultado['total_clientes'] = $totalClientes;
        
        // Calcular métricas adicionales
        $resultado['tasa_conversion'] = $resultado['total_gestiones'] > 0 ? 
            round(($resultado['ventas_exitosas'] / $resultado['total_gestiones']) * 100, 2) : 0;
        
        $resultado['tasa_contacto_efectivo'] = $resultado['total_gestiones'] > 0 ? 
            round(($resultado['contactos_efectivos'] / $resultado['total_gestiones']) * 100, 2) : 0;
        
        $resultado['tiempo_promedio_conversacion'] = $resultado['tiempo_promedio_conversacion'] !== null ? 
            round($resultado['tiempo_promedio_conversacion'], 2) : 0;
        $resultado['promedio_venta'] = $resultado['promedio_venta'] !== null ? 
            round($resultado['promedio_venta'], 2) : 0;
        
        return $resultado;
    }

    /**
     * Obtiene métricas de un asesor específico para el coordinador
     * MEJORADO para manejar casos donde no hay gestiones y fechas específicas
     */
    public function getMetricasAsesor($asesorId, $periodo = 'dia', $fechaInicio = null, $fechaFin = null) {
        try {
            // Determinar fechas según parámetros
            if ($fechaInicio && $fechaFin) {
                $fechaInicioFormateada = $this->getFechaInicioFromFechas($fechaInicio, $fechaFin);
                $fechaFinFormateada = $this->getFechaFinFromFechas($fechaInicio, $fechaFin);
            } else {
                $fechaInicioFormateada = $this->getFechaInicio($periodo);
                $fechaFinFormateada = date('Y-m-d 23:59:59');
            }
            
            // CONSULTA CORREGIDA: Contar clientes asignados en asignaciones_clientes
            $sql = "SELECT 
                        (
                            -- Contar clientes asignados en asignaciones_clientes
                            SELECT COUNT(DISTINCT ac.cliente_id) 
                            FROM asignaciones_clientes ac 
                            WHERE ac.asesor_id = ? AND ac.estado = 'asignado'
                        ) as total_clientes,
                        (
                            -- Gestiones por asignacion_id únicamente
                            SELECT COUNT(hg1.id) 
                            FROM historial_gestion hg1 
                            JOIN asignaciones_clientes ac1 ON hg1.asignacion_id = ac1.id 
                            WHERE ac1.asesor_id = ? AND hg1.fecha_gestion >= ? AND hg1.fecha_gestion <= ?
                        ) as total_gestiones,
                        (
                            -- Contactos efectivos por asignacion_id únicamente
                            SELECT COUNT(hg1.id) 
                            FROM historial_gestion hg1 
                            JOIN asignaciones_clientes ac1 ON hg1.asignacion_id = ac1.id 
                            WHERE ac1.asesor_id = ? AND hg1.fecha_gestion >= ? AND hg1.fecha_gestion <= ? AND hg1.resultado IS NOT NULL
                        ) as contactos_efectivos,
                        (
                            -- Ventas exitosas por asignacion_id únicamente
                            SELECT COUNT(hg1.id) 
                            FROM historial_gestion hg1 
                            JOIN asignaciones_clientes ac1 ON hg1.asignacion_id = ac1.id 
                            WHERE ac1.asesor_id = ? AND hg1.fecha_gestion >= ? AND hg1.fecha_gestion <= ? AND hg1.resultado IN ('Venta Exitosa', 'VENTA INGRESADA', 'Agendado', 'Interesado')
                        ) as ventas_exitosas,
                        (
                            -- Ventas fallidas por asignacion_id únicamente
                            SELECT COUNT(hg1.id) 
                            FROM historial_gestion hg1 
                            JOIN asignaciones_clientes ac1 ON hg1.asignacion_id = ac1.id 
                            WHERE ac1.asesor_id = ? AND hg1.fecha_gestion >= ? AND hg1.fecha_gestion <= ? AND hg1.resultado IN ('Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica', 'Necesita Pensarlo')
                        ) as ventas_fallidas,
                        (
                            -- Tiempo promedio por asignacion_id únicamente
                            SELECT AVG(hg1.duracion_llamada) 
                            FROM historial_gestion hg1 
                            JOIN asignaciones_clientes ac1 ON hg1.asignacion_id = ac1.id 
                            WHERE ac1.asesor_id = ? AND hg1.fecha_gestion >= ? AND hg1.fecha_gestion <= ? AND hg1.duracion_llamada > 0
                        ) as tiempo_promedio_conversacion,
                        (
                            -- Total ventas por asignacion_id únicamente
                            SELECT COALESCE(SUM(hg1.monto_venta), 0) 
                            FROM historial_gestion hg1 
                            JOIN asignaciones_clientes ac1 ON hg1.asignacion_id = ac1.id 
                            WHERE ac1.asesor_id = ? AND hg1.fecha_gestion >= ? AND hg1.fecha_gestion <= ? AND hg1.monto_venta > 0
                        ) as total_ventas_monto
                    FROM asignaciones_clientes ac
                    WHERE ac.asesor_id = ? AND ac.estado = 'asignado'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $asesorId,  // total_clientes
                $asesorId, $fechaInicioFormateada, $fechaFinFormateada,  // total_gestiones
                $asesorId, $fechaInicioFormateada, $fechaFinFormateada,  // contactos_efectivos
                $asesorId, $fechaInicioFormateada, $fechaFinFormateada,  // ventas_exitosas
                $asesorId, $fechaInicioFormateada, $fechaFinFormateada,  // ventas_fallidas
                $asesorId, $fechaInicioFormateada, $fechaFinFormateada,  // tiempo_promedio_conversacion
                $asesorId, $fechaInicioFormateada, $fechaFinFormateada,  // total_ventas_monto
                $asesorId  // WHERE final
            ]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si la consulta devolvió resultados
            if ($resultado === false) {
                $resultado = [
                    'total_clientes' => 0,
                    'total_gestiones' => 0,
                    'contactos_efectivos' => 0,
                    'ventas_exitosas' => 0,
                    'ventas_fallidas' => 0,
                    'tiempo_promedio_conversacion' => 0,
                    'total_ventas_monto' => 0
                ];
            } else {
                // Asegurar que todos los campos tengan valores por defecto si son null
                $resultado = array_map(function($value) {
                    return $value !== null ? $value : 0;
                }, $resultado);
            }
            
            // Calcular métricas adicionales con manejo seguro de división por cero
            $resultado['tasa_conversion'] = $resultado['total_gestiones'] > 0 ? 
                round(($resultado['ventas_exitosas'] / $resultado['total_gestiones']) * 100, 2) : 0;
            
            $resultado['tasa_contacto_efectivo'] = $resultado['total_gestiones'] > 0 ? 
                round(($resultado['contactos_efectivos'] / $resultado['total_gestiones']) * 100, 2) : 0;
            
            $resultado['tiempo_promedio_conversacion'] = $resultado['tiempo_promedio_conversacion'] !== null ? 
                round($resultado['tiempo_promedio_conversacion'], 2) : 0;
            
            // Calcular promedio de venta correctamente
            $total_ventas_count = $resultado['ventas_exitosas'] ?? 0;
            if ($total_ventas_count > 0 && $resultado['total_ventas_monto'] > 0) {
                $resultado['promedio_venta'] = round($resultado['total_ventas_monto'] / $total_ventas_count, 2);
            } else {
                $resultado['promedio_venta'] = 0;
            }
            
            // Log para debugging
            error_log("Métricas obtenidas para asesor ID: " . $asesorId . " - Clientes: " . $resultado['total_clientes'] . ", Gestiones: " . $resultado['total_gestiones']);
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error en getMetricasAsesor para asesor ID: " . $asesorId . " - Error: " . $e->getMessage());
            
            // Retornar métricas por defecto en caso de error
            return [
                'total_clientes' => 0,
                'total_gestiones' => 0,
                'contactos_efectivos' => 0,
                'ventas_exitosas' => 0,
                'ventas_fallidas' => 0,
                'tiempo_promedio_conversacion' => 0,
                'total_ventas_monto' => 0,
                'promedio_venta' => 0,
                'tasa_conversion' => 0,
                'tasa_contacto_efectivo' => 0
            ];
        }
    }

    /**
     * Obtiene las llamadas pendientes de un asesor (clientes que debe volver a llamar)
     * SOLUCIÓN HÍBRIDA: Busca tanto en proxima_fecha como en gestiones existentes
     */
    public function getLlamadasPendientes($asesorId) {
        // CONSULTA HÍBRIDA: Combinar gestiones nuevas (con proxima_fecha) y existentes (con resultado VOLVER A LLAMAR)
        $sql = "SELECT
                    hg.*,
                    c.nombre as cliente_nombre,
                    c.telefono,
                    c.celular2,
                    ac.cliente_id,
                    CASE
                        WHEN hg.proxima_fecha IS NOT NULL THEN hg.proxima_fecha
                        WHEN hg.resultado = 'VOLVER A LLAMAR' THEN DATE_ADD(hg.fecha_gestion, INTERVAL 1 DAY)
                        ELSE NULL
                    END as fecha_llamada_pendiente
                FROM historial_gestion hg
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                JOIN clientes c ON ac.cliente_id = c.id
                WHERE ac.asesor_id = ?
                AND (
                    -- Gestiones nuevas con proxima_fecha
                    (hg.proxima_fecha IS NOT NULL
                     AND hg.proxima_fecha >= CURDATE()
                     AND hg.proxima_fecha <= DATE_ADD(CURDATE(), INTERVAL 7 DAY))
                    OR
                    -- Gestiones existentes de VOLVER A LLAMAR (sin proxima_fecha)
                    (hg.resultado = 'VOLVER A LLAMAR'
                     AND hg.proxima_fecha IS NULL
                     AND hg.fecha_gestion >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))
                )
                ORDER BY fecha_llamada_pendiente ASC, hg.fecha_gestion DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    

    
    /**
     * Obtiene todas las gestiones de seguimiento de un asesor (nuevas y existentes)
     * SOLUCIÓN HÍBRIDA para compatibilidad con gestiones existentes
     */
    public function getGestionesSeguimiento($asesorId) {
        $sql = "SELECT 
                    hg.*, 
                    c.nombre as cliente_nombre, 
                    c.telefono, 
                    c.celular2, 
                    ac.cliente_id,
                    CASE 
                        WHEN hg.proxima_fecha IS NOT NULL THEN hg.proxima_fecha
                        WHEN hg.resultado = 'VOLVER A LLAMAR' THEN DATE_ADD(hg.fecha_gestion, INTERVAL 1 DAY)
                        ELSE NULL
                    END as fecha_seguimiento,
                    CASE 
                        WHEN hg.proxima_fecha IS NOT NULL THEN 'Nueva Gestión'
                        WHEN hg.resultado = 'VOLVER A LLAMAR' THEN 'Gestión Existente'
                        ELSE 'Sin Seguimiento'
                    END as tipo_seguimiento
                FROM historial_gestion hg
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                JOIN clientes c ON ac.cliente_id = c.id
                WHERE ac.asesor_id = ? 
                AND (
                    -- Gestiones nuevas con proxima_fecha
                    hg.proxima_fecha IS NOT NULL 
                    OR
                    -- Gestiones existentes de VOLVER A LLAMAR
                    hg.resultado = 'VOLVER A LLAMAR'
                )
                ORDER BY fecha_seguimiento ASC, hg.fecha_gestion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el historial COMPLETO de gestiones de un asesor
     * INCLUYE múltiples gestiones por cliente para el CSV
     */
    public function getHistorialCompletoAsesor($asesorId, $fechaInicio = null, $fechaFin = null) {
        try {
            $sql = "SELECT 
                        hg.id,
                        hg.fecha_gestion,
                        hg.tipo_gestion,
                        hg.resultado,
                        hg.comentarios,
                        hg.monto_venta,
                        hg.duracion_llamada,
                        hg.edad,
                        hg.num_personas,
                        hg.valor_cotizacion,
                        hg.whatsapp_enviado,
                        c.nombre as cliente_nombre,
                        c.cedula,
                        c.telefono,
                        c.celular2,
                        c.fecha_creacion,
                        ac.estado as estado_asignacion,
                        ac.cliente_id
                    FROM historial_gestion hg
                    LEFT JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                    LEFT JOIN clientes c ON ac.cliente_id = c.id
                    WHERE (ac.asesor_id = ? OR hg.asesor_id = ?)";
            
            $params = [$asesorId, $asesorId];
            
            // Filtro de fechas si se especifican
            if ($fechaInicio && $fechaFin) {
                $sql .= " AND DATE(hg.fecha_gestion) BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            }
            
            // Ordenar por cliente y luego por fecha de gestión
            $sql .= " ORDER BY c.nombre ASC, hg.fecha_gestion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en getHistorialCompletoAsesor: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene historial completo para exportación con todos los campos requeridos
     * Incluye tipificaciones de 2 y 3 nivel, canales autorizados y nombre de base de datos
     */
    public function getHistorialCompletoParaExportacion($asesorId, $fechaInicio = null, $fechaFin = null) {
        try {
            $sql = "SELECT 
                        hg.id,
                        hg.fecha_gestion,
                        hg.tipo_gestion,
                        hg.resultado,
                        hg.comentarios,
                        hg.forma_contacto,
                        hg.proxima_fecha,
                        hg.obligacion_id,
                        hg.numero_obligacion,
                        hg.monto_obligacion,
                        hg.no_cuotas,
                        hg.fecha_pago,
                        hg.valor_cuota,
                        hg.numero_cuota,
                        hg.valor_acuerdo,
                        COALESCE(hg.valor_total, o.saldo_k_obligacion) as valor_total,
                        o.obligacion as numero_obligacion_tabla,
                        o.producto as producto_obligacion,
                        c.nombre as cliente_nombre,
                        c.cedula,
                        c.telefono as celular_cliente,
                        c.celular2,
                        u.nombre_completo as asesor_nombre,
                        ce.nombre_cargue as base_datos_nombre,
                        ac.estado as estado_asignacion
                    FROM historial_gestion hg
                    LEFT JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                    LEFT JOIN clientes c ON ac.cliente_id = c.id
                    LEFT JOIN usuarios u ON ac.asesor_id = u.id
                    LEFT JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                    LEFT JOIN obligaciones o ON hg.obligacion_id = o.id
                    WHERE ac.asesor_id = ?";
            
            $params = [$asesorId];
            
            // Filtro de fechas si se especifican
            if ($fechaInicio && $fechaFin) {
                $sql .= " AND DATE(hg.fecha_gestion) BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            }
            
            // Ordenar por fecha de gestión
            $sql .= " ORDER BY hg.fecha_gestion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $gestiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug: Log para verificar qué está pasando
            error_log("getHistorialCompletoParaExportacion - SQL: " . $sql);
            error_log("getHistorialCompletoParaExportacion - Params: " . json_encode($params));
            error_log("getHistorialCompletoParaExportacion - Resultados: " . count($gestiones));
            
            // Agregar información adicional a cada gestión
            foreach ($gestiones as &$gestion) {
                // Obtener canales autorizados
                $gestion['canales_autorizados'] = $this->getCanalesAutorizados($gestion['id']);
                
                // Obtener tipificaciones de 2 y 3 nivel
                $gestion['tipificacion_2_nivel'] = $this->getTipificacion2Nivel($gestion['tipo_gestion']);
                $gestion['tipificacion_3_nivel'] = $this->getTipificacion3Nivel($gestion['resultado']);
                
                // Formatear obligación para CSV
                $gestion['obligacion_texto'] = $this->formatearObligacionParaExportacion($gestion);
                
                // Formatear canales autorizados para CSV
                $gestion['canales_autorizados_texto'] = $this->formatearCanalesAutorizados($gestion['canales_autorizados']);
            }
            
            return $gestiones;
            
        } catch (Exception $e) {
            error_log("Error en getHistorialCompletoParaExportacion: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Formatea la obligación para mostrar en CSV
     * Prioriza el número de obligación de la tabla obligaciones
     */
    private function formatearObligacionParaExportacion($gestion) {
        // Prioridad 1: Número de obligación de la tabla obligaciones
        if (!empty($gestion['numero_obligacion_tabla'])) {
            return $gestion['numero_obligacion_tabla'];
        }
        
        // Prioridad 2: Número de obligación del historial_gestion
        if (!empty($gestion['numero_obligacion'])) {
            return $gestion['numero_obligacion'];
        }
        
        // Si no hay obligación, mostrar "Ninguna"
        return 'Ninguna';
    }
    
    /**
     * Obtiene tipificación de 2 nivel basada en el tipo de gestión
     */
    private function getTipificacion2Nivel($tipoGestion) {
        $tipificaciones2Nivel = [
            'hacer_llamada' => 'HACER LLAMADA',
            'recibir_llamada' => 'RECIBIR LLAMADA'
        ];
        
        return $tipificaciones2Nivel[$tipoGestion] ?? $tipoGestion;
    }
    
    /**
     * Obtiene tipificación de 3 nivel basada en el resultado
     */
    private function getTipificacion3Nivel($resultado) {
        $tipificaciones3Nivel = [
            '01' => '01. CANCELADA',
            '02' => '02. MEMORANDO CNC',
            '03' => '03. ACUERDO DE PAGO',
            '04' => '04. PAGO TOTAL',
            '05' => '05. YA PAGO',
            '06' => '06. PROMESA',
            '06.1' => '06.1 BANNER',
            '06.2' => '06.2 REFINANCIACION',
            '06.3' => '06.3 UNIFICACION',
            '06.4' => '06.4 NIVELACION O NORMALIZACION',
            '07' => '07. REPORTE DE PAGO',
            '08' => '08. ABONOS',
            '09' => '09. NEGOCIACION EN TRAMITE',
            '10' => '10. SEGUIM GESTION',
            '11' => '11. SEGUIMIENTO',
            '12' => '12. RENUENTE',
            '13' => '13. VOLUNTAD DE PAGO',
            '14' => '14. VOLVER A LLAMAR',
            '14.1' => '14.1 VOLVER A LLAMAR HOY',
            '15' => '15. LOCALIZADO',
            '16' => '16. CONTACTO CON TERCERO',
            '17' => '17. FALLECIDO',
            '18' => '18. QUEJA / RECLAMO',
            '19' => '19. NO CONTESTAN',
            '20' => '20. ACTUALIZACION DATOS',
            '21' => '21. MENSAJE',
            '22' => '22. CORREO-E',
            '23' => '23. LEY DE INSOLVENCIA',
            '24' => '24. NO LOCALIZADO',
            '25' => '25. NUMERO EQUIVOCADO',
            '26' => '26. WHATSAPP',
            '27' => '27. ABANDONO CHAT'
        ];
        
        return $tipificaciones3Nivel[$resultado] ?? $resultado;
    }
    
    /**
     * Formatea los canales autorizados para mostrar en CSV
     */
    private function formatearCanalesAutorizados($canales) {
        if (empty($canales)) {
            return 'No especificados';
        }
        
        $canalesMap = [
            'llamada' => 'Llamada',
            'correo_electronico' => 'Correo Electrónico',
            'sms' => 'SMS',
            'correo_fisico' => 'Correo Físico',
            'mensajeria_aplicaciones' => 'Mensajería por Aplicaciones'
        ];
        
        $canalesFormateados = [];
        foreach ($canales as $canal) {
            $canalesFormateados[] = $canalesMap[$canal] ?? $canal;
        }
        
        return implode(', ', $canalesFormateados);
    }
    

    /**
     * Obtiene llamadas pendientes consolidadas de todos los asesores de un coordinador
     */
    public function getLlamadasPendientesCoordinador($coordinadorId) {
        $sql = "SELECT
                    c.id as cliente_id,
                    c.nombre as cliente_nombre,
                    c.cedula,
                    c.telefono,
                    c.celular2,
                    hg.proxima_fecha,
                    hg.resultado,
                    hg.fecha_gestion,
                    hg.comentarios,
                    ac.id as asignacion_id,
                    u.nombre_completo as asesor_nombre,
                    u.id as asesor_id
                FROM clientes c
                JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                JOIN historial_gestion hg ON hg.asignacion_id = ac.id
                JOIN usuarios u ON ac.asesor_id = u.id
                JOIN asignaciones_asesor_coordinador aac ON ac.asesor_id = aac.asesor_id
                WHERE aac.coordinador_id = ?
                    AND aac.estado = 'Activa'
                    AND hg.resultado = 'VOLVER A LLAMAR'
                    AND hg.fecha_gestion = (
                        SELECT MAX(hg2.fecha_gestion)
                        FROM historial_gestion hg2
                        JOIN asignaciones_clientes ac2 ON hg2.asignacion_id = ac2.id
                        WHERE ac2.cliente_id = c.id AND ac2.asesor_id = ac.asesor_id
                    )
                    AND (
                        (hg.proxima_fecha IS NOT NULL AND DATE(hg.proxima_fecha) = CURDATE())
                        OR
                        (hg.proxima_fecha IS NULL AND DATE(hg.fecha_gestion) = CURDATE())
                    )
                ORDER BY hg.fecha_gestion DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$coordinadorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el total de llamadas pendientes consolidadas de todos los asesores de un coordinador
     */
    public function getTotalLlamadasPendientesCoordinador($coordinadorId) {
        $sql = "SELECT COUNT(DISTINCT c.id) as total
                FROM clientes c
                JOIN asignaciones_clientes ac ON c.id = ac.cliente_id
                JOIN historial_gestion hg ON hg.asignacion_id = ac.id
                JOIN asignaciones_asesor_coordinador aac ON ac.asesor_id = aac.asesor_id
                WHERE aac.coordinador_id = ?
                    AND aac.estado = 'Activa'
                    AND hg.resultado = 'VOLVER A LLAMAR'
                    AND hg.fecha_gestion = (
                        SELECT MAX(hg2.fecha_gestion)
                        FROM historial_gestion hg2
                        JOIN asignaciones_clientes ac2 ON hg2.asignacion_id = ac2.id
                        WHERE ac2.cliente_id = c.id AND ac2.asesor_id = ac.asesor_id
                    )
                    AND (
                        (hg.proxima_fecha IS NOT NULL AND DATE(hg.proxima_fecha) = CURDATE())
                        OR
                        (hg.proxima_fecha IS NULL AND DATE(hg.fecha_gestion) = CURDATE())
                    )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$coordinadorId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] ?? 0;
    }

    /**
     * Transfiere un recordatorio de un asesor a otro
     */
    public function transferirRecordatorio($clienteId, $asesorOrigenId, $asesorDestinoId, $coordinadorId) {
        try {
            // Verificar que ambos asesores estén asignados al coordinador
            $sqlVerificar = "SELECT COUNT(*) as total FROM asignaciones_asesor_coordinador
                            WHERE coordinador_id = ? AND asesor_id IN (?, ?) AND estado = 'Activa'";
            $stmtVerificar = $this->pdo->prepare($sqlVerificar);
            $stmtVerificar->execute([$coordinadorId, $asesorOrigenId, $asesorDestinoId]);
            $verificacion = $stmtVerificar->fetch(PDO::FETCH_ASSOC);

            if ($verificacion['total'] != 2) {
                throw new Exception("Uno o ambos asesores no están asignados a este coordinador");
            }

            // Obtener la asignación actual del cliente
            $sqlAsignacion = "SELECT ac.id as asignacion_id, ac.asesor_id
                            FROM asignaciones_clientes ac
                            WHERE ac.cliente_id = ? AND ac.asesor_id = ?";
            $stmtAsignacion = $this->pdo->prepare($sqlAsignacion);
            $stmtAsignacion->execute([$clienteId, $asesorOrigenId]);
            $asignacion = $stmtAsignacion->fetch(PDO::FETCH_ASSOC);

            if (!$asignacion) {
                throw new Exception("No se encontró la asignación del cliente al asesor origen");
            }

            // Transferir la asignación al nuevo asesor
            $sqlTransferir = "UPDATE asignaciones_clientes SET asesor_id = ? WHERE id = ?";
            $stmtTransferir = $this->pdo->prepare($sqlTransferir);
            $resultado = $stmtTransferir->execute([$asesorDestinoId, $asignacion['asignacion_id']]);

            if ($resultado) {
                // Log de la transferencia
                error_log("Recordatorio transferido - Cliente ID: {$clienteId}, De asesor ID: {$asesorOrigenId} a asesor ID: {$asesorDestinoId}, Coordinador ID: {$coordinadorId}");
                return true;
            } else {
                throw new Exception("Error al transferir la asignación");
            }

        } catch (Exception $e) {
            error_log("Error en transferirRecordatorio: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene la lista de asesores disponibles para transferir recordatorios (excluyendo el asesor actual)
     */
    public function getAsesoresParaTransferencia($coordinadorId, $asesorActualId = null) {
        $sql = "SELECT u.id, u.nombre_completo
                FROM usuarios u
                JOIN asignaciones_asesor_coordinador aac ON u.id = aac.asesor_id
                WHERE aac.coordinador_id = ? AND aac.estado = 'Activa' AND u.estado = 'Activo'";

        $params = [$coordinadorId];

        if ($asesorActualId) {
            $sql .= " AND u.id != ?";
            $params[] = $asesorActualId;
        }

        $sql .= " ORDER BY u.nombre_completo";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener total de gestiones por asesor y cliente
     */
    public function getTotalGestionesByAsesorAndCliente($asesorId, $clienteId) {
        $sql = "SELECT COUNT(*) as total
                FROM historial_gestion hg
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                WHERE ac.asesor_id = ? AND ac.cliente_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $clienteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Registra automáticamente una actividad cuando se crea una gestión
     */
    private function registrarActividadAutomatica($historialGestionId, $gestionData) {
        try {
            error_log("Iniciando registrarActividadAutomatica para gestión ID: $historialGestionId");
            
            // Obtener información del cliente y asesor
            $sql = "SELECT ac.cliente_id, ac.asesor_id 
                    FROM asignaciones_clientes ac 
                    WHERE ac.id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$gestionData['asignacion_id']]);
            $asignacion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$asignacion) {
                error_log("No se encontró asignación para ID: " . $gestionData['asignacion_id']);
                return false;
            }
            
            error_log("Asignación encontrada - Cliente: {$asignacion['cliente_id']}, Asesor: {$asignacion['asesor_id']}");
            
            // Crear instancia del modelo de actividades
            require_once 'models/ActividadProductoModel.php';
            $actividadModel = new ActividadProductoModel($this->pdo);
            
            error_log("Modelo de actividades creado");
            
            // Registrar la gestión del producto
            $actividadId = $actividadModel->registrarGestionProducto(
                $historialGestionId,
                $asignacion['cliente_id'],
                $asignacion['asesor_id'],
                $gestionData
            );
            
            if ($actividadId) {
                error_log("Actividad registrada con ID: $actividadId");
            } else {
                error_log("Error al registrar actividad");
            }
            
            // Registrar log del sistema
            $logResult = $actividadModel->registrarLogSistema([
                'tipo_evento' => 'gestion_producto',
                'entidad_afectada' => 'historial_gestion',
                'entidad_id' => $historialGestionId,
                'usuario_id' => $asignacion['asesor_id'],
                'accion' => 'crear_gestion',
                'descripcion' => 'Nueva gestión de producto creada',
                'datos_nuevos' => $gestionData
            ]);
            
            error_log("Log del sistema registrado: " . ($logResult ? 'true' : 'false'));
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error en registrarActividadAutomatica: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Obtiene gestiones por día para el dashboard del asesor
     */
    public function getGestionesPorDia($asesorId, $periodo = 'dia') {
        $fechaInicio = $this->getFechaInicio($periodo);
        
        $sql = "SELECT 
                    DATE(hg.fecha_gestion) as fecha,
                    COUNT(*) as total_gestiones,
                    COUNT(CASE WHEN hg.resultado IN ('Venta Exitosa', 'VENTA INGRESADA', 'Agendado', 'Interesado') THEN 1 END) as ventas,
                    COUNT(CASE WHEN hg.resultado IS NOT NULL THEN 1 END) as contactos_efectivos,
                    COUNT(CASE WHEN hg.resultado IN ('No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado') THEN 1 END) as contactos_no_efectivos
                FROM historial_gestion hg 
                JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id 
                WHERE ac.asesor_id = ? AND hg.fecha_gestion >= ?
                GROUP BY DATE(hg.fecha_gestion)
                ORDER BY fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $fechaInicio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene las observaciones y fecha de próxima llamada de una gestión
     */
    public function getObservacionesGestion($gestionId) {
        $sql = "SELECT comentarios, proxima_fecha 
                FROM historial_gestion 
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$gestionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        
        // Extraer hora de proxima_fecha si existe
        if (isset($result['proxima_fecha']) && $result['proxima_fecha']) {
            $fecha = new DateTime($result['proxima_fecha']);
            $result['proxima_hora'] = $fecha->format('H:i:s');
        } else {
            $result['proxima_hora'] = '';
        }
        
        return $result;
    }
}
?>
