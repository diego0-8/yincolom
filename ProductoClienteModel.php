<?php
/**
 * Modelo para gestión de productos de clientes
 * Maneja la relación entre clientes y sus productos con clasificaciones específicas
 */

class ProductoClienteModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los productos de un cliente desde la tabla obligaciones
     */
    public function getProductosByCliente($clienteId) {
        try {
            $sql = "SELECT 
                        o.id,
                        o.cliente_id,
                        o.producto as nombre_producto,
                        o.saldo_k_obligacion as valor_producto,
                        o.estado as estado_producto,
                        o.fecha_creacion,
                        o.fecha_actualizacion as ultima_gestion,
                        c.asesor_id,
                        u.nombre_completo as asesor_nombre,
                        o.obligacion,
                        o.capital_cliente,
                        o.pago_total_obligacion,
                        o.mora_actual,
                        o.propiedad,
                        o.medicion
                    FROM obligaciones o
                    JOIN clientes c ON o.cliente_id = c.id
                    LEFT JOIN usuarios u ON c.asesor_id = u.id
                    WHERE o.cliente_id = ? AND o.producto IS NOT NULL
                    ORDER BY o.fecha_creacion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$clienteId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo productos del cliente: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un producto específico por ID
     */
    public function getProductoById($productoId) {
        try {
            $sql = "SELECT 
                        pc.*,
                        c.nombre as cliente_nombre,
                        c.cedula,
                        c.telefono,
                        u.nombre_completo as asesor_nombre
                    FROM productos_clientes pc
                    JOIN clientes c ON pc.cliente_id = c.id
                    LEFT JOIN usuarios u ON pc.asesor_id = u.id
                    WHERE pc.id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$productoId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo producto por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo producto para un cliente
     */
    public function crearProducto($datos) {
        try {
            $sql = "INSERT INTO productos_clientes (
                        cliente_id, nombre_producto, valor_producto, 
                        estado_producto, asesor_id, comentarios,
                        tipo_clasificacion, resultado_clasificacion,
                        monto_pagado, fecha_pago, proxima_gestion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                $datos['cliente_id'],
                $datos['nombre_producto'],
                $datos['valor_producto'],
                $datos['estado_producto'] ?? 'pendiente',
                $datos['asesor_id'],
                $datos['comentarios'] ?? '',
                $datos['tipo_clasificacion'] ?? '',
                $datos['resultado_clasificacion'] ?? '',
                $datos['monto_pagado'] ?? 0,
                $datos['fecha_pago'] ?? null,
                $datos['proxima_gestion'] ?? null
            ]);

            if ($success) {
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error creando producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un producto existente
     */
    public function actualizarProducto($productoId, $datos) {
        try {
            $sql = "UPDATE productos_clientes SET 
                        nombre_producto = ?, valor_producto = ?, 
                        estado_producto = ?, comentarios = ?,
                        tipo_clasificacion = ?, resultado_clasificacion = ?,
                        monto_pagado = ?, fecha_pago = ?, 
                        proxima_gestion = ?, ultima_gestion = NOW()
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $datos['nombre_producto'],
                $datos['valor_producto'],
                $datos['estado_producto'],
                $datos['comentarios'],
                $datos['tipo_clasificacion'],
                $datos['resultado_clasificacion'],
                $datos['monto_pagado'],
                $datos['fecha_pago'],
                $datos['proxima_gestion'],
                $productoId
            ]);
        } catch (Exception $e) {
            error_log("Error actualizando producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra una gestión específica de un producto
     */
    public function registrarGestionProducto($productoId, $datosGestion) {
        try {
            $sql = "INSERT INTO gestiones_productos (
                        producto_id, asesor_id, tipo_gestion,
                        resultado_gestion, comentarios, monto_gestion,
                        fecha_gestion, proxima_gestion, canales_autorizados
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                $productoId,
                $datosGestion['asesor_id'],
                $datosGestion['tipo_gestion'],
                $datosGestion['resultado_gestion'],
                $datosGestion['comentarios'],
                $datosGestion['monto_gestion'] ?? 0,
                $datosGestion['proxima_gestion'] ?? null,
                $datosGestion['canales_autorizados'] ?? null
            ]);

            if ($success) {
                // Actualizar el producto con la última gestión
                $this->actualizarProducto($productoId, [
                    'nombre_producto' => $datosGestion['nombre_producto'] ?? '',
                    'valor_producto' => $datosGestion['valor_producto'] ?? 0,
                    'estado_producto' => $datosGestion['estado_producto'] ?? 'pendiente',
                    'comentarios' => $datosGestion['comentarios'] ?? '',
                    'tipo_clasificacion' => $datosGestion['tipo_gestion'],
                    'resultado_clasificacion' => $datosGestion['resultado_gestion'],
                    'monto_pagado' => $datosGestion['monto_gestion'] ?? 0,
                    'fecha_pago' => $datosGestion['fecha_pago'] ?? null,
                    'proxima_gestion' => $datosGestion['proxima_gestion'] ?? null
                ]);
                
                return $this->pdo->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error registrando gestión de producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el historial de gestiones de un producto
     */
    public function getHistorialGestionesProducto($productoId) {
        try {
            $sql = "SELECT 
                        gp.*,
                        u.nombre_completo as asesor_nombre
                    FROM gestiones_productos gp
                    LEFT JOIN usuarios u ON gp.asesor_id = u.id
                    WHERE gp.producto_id = ?
                    ORDER BY gp.fecha_gestion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$productoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo historial de gestiones del producto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene productos pendientes de gestión por asesor
     */
    public function getProductosPendientesByAsesor($asesorId) {
        try {
            $sql = "SELECT 
                        pc.*,
                        c.nombre as cliente_nombre,
                        c.cedula,
                        c.telefono,
                        c.celular2
                    FROM productos_clientes pc
                    JOIN clientes c ON pc.cliente_id = c.id
                    WHERE pc.asesor_id = ? 
                    AND (pc.estado_producto = 'pendiente' OR pc.proxima_gestion <= NOW())
                    ORDER BY pc.proxima_gestion ASC, pc.fecha_creacion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asesorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo productos pendientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas de productos por asesor
     */
    public function getEstadisticasProductosByAsesor($asesorId, $fechaInicio = null, $fechaFin = null) {
        try {
            $whereClause = "WHERE pc.asesor_id = ?";
            $params = [$asesorId];
            
            if ($fechaInicio && $fechaFin) {
                $whereClause .= " AND DATE(pc.ultima_gestion) BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            }
            
            $sql = "SELECT 
                        COUNT(*) as total_productos,
                        COUNT(CASE WHEN pc.estado_producto = 'pagado' THEN 1 END) as productos_pagados,
                        COUNT(CASE WHEN pc.estado_producto = 'pendiente' THEN 1 END) as productos_pendientes,
                        COUNT(CASE WHEN pc.estado_producto = 'rechazado' THEN 1 END) as productos_rechazados,
                        SUM(pc.monto_pagado) as total_recaudado,
                        AVG(pc.monto_pagado) as promedio_pago
                    FROM productos_clientes pc
                    $whereClause";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de productos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene productos para reporte del coordinador
     */
    public function getProductosParaReporte($coordinadorId, $fechaInicio = null, $fechaFin = null, $asesorId = null) {
        try {
            $sql = "SELECT 
                        pc.*,
                        c.nombre as cliente_nombre,
                        c.cedula,
                        c.telefono,
                        u.nombre_completo as asesor_nombre,
                        ce.nombre_cargue as base_datos_nombre
                    FROM productos_clientes pc
                    JOIN clientes c ON pc.cliente_id = c.id
                    LEFT JOIN usuarios u ON pc.asesor_id = u.id
                    LEFT JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                    WHERE ce.usuario_coordinador_id = ?";
            
            $params = [$coordinadorId];
            
            if ($fechaInicio && $fechaFin) {
                $sql .= " AND DATE(pc.ultima_gestion) BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            }
            
            if ($asesorId) {
                $sql .= " AND pc.asesor_id = ?";
                $params[] = $asesorId;
            }
            
            $sql .= " ORDER BY pc.ultima_gestion DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo productos para reporte: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Elimina un producto (soft delete)
     */
    public function eliminarProducto($productoId) {
        try {
            $sql = "UPDATE productos_clientes SET estado_producto = 'eliminado' WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$productoId]);
        } catch (Exception $e) {
            error_log("Error eliminando producto: " . $e->getMessage());
            return false;
        }
    }
}
?>
