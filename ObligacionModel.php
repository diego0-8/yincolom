<?php
/**
 * Modelo para manejar obligaciones de clientes
 * Permite múltiples obligaciones por cliente con cédulas duplicadas
 */

class ObligacionModel {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Crear una nueva obligación
     */
    public function crearObligacion($datos) {
        $sql = "INSERT INTO obligaciones (cliente_id, obligacion, saldo_k_obligacion, capital_cliente, pago_total_obligacion, mora_actual, propiedad, producto, medicion, estado, fecha_creacion, fecha_actualizacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([
            $datos['cliente_id'],
            $datos['obligacion'],
            $datos['saldo_k_obligacion'] ?? null,
            $datos['capital_cliente'] ?? null,
            $datos['pago_total_obligacion'] ?? null,
            $datos['mora_actual'] ?? null,
            $datos['propiedad'] ?? null,
            $datos['producto'] ?? null,
            $datos['medicion'] ?? null,
            $datos['estado'] ?? 'activa'
        ])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }
    
    /**
     * Obtener todas las obligaciones de un cliente por cédula
     */
    public function getObligacionesByCedula($cedula) {
        $sql = "SELECT c.*, o.*, ce.nombre_cargue
                FROM clientes c
                LEFT JOIN obligaciones o ON c.id = o.cliente_id
                LEFT JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                WHERE c.cedula = ?
                ORDER BY o.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cedula]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener todas las obligaciones de un cliente por ID
     */
    public function getObligacionesByClienteId($cliente_id) {
        $sql = "SELECT o.*, c.nombre, c.cedula, c.telefono, c.email, ce.nombre_cargue
                FROM obligaciones o
                LEFT JOIN clientes c ON o.cliente_id = c.id
                LEFT JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                WHERE o.cliente_id = ?
                ORDER BY o.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cliente_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener una obligación por su ID
     */
    public function getObligacionById($id) {
        $sql = "SELECT * FROM obligaciones WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener una obligación específica por número de obligación
     */
    public function getObligacionByNumero($obligacion) {
        $sql = "SELECT o.*, c.nombre, c.cedula, c.telefono, c.email, ce.nombre_cargue
                FROM obligaciones o
                LEFT JOIN clientes c ON o.cliente_id = c.id
                LEFT JOIN cargas_excel ce ON c.carga_excel_id = ce.id
                WHERE o.obligacion = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$obligacion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si una obligación ya existe
     */
    public function obligacionExiste($obligacion) {
        $sql = "SELECT COUNT(*) FROM obligaciones WHERE obligacion = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$obligacion]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Actualizar una obligación
     */
    public function actualizarObligacion($id, $datos) {
        $sql = "UPDATE obligaciones SET 
                saldo_k_obligacion = ?, 
                capital_cliente = ?, 
                pago_total_obligacion = ?, 
                mora_actual = ?, 
                propiedad = ?, 
                producto = ?, 
                medicion = ?, 
                estado = ?,
                fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $datos['saldo_k_obligacion'] ?? null,
            $datos['capital_cliente'] ?? null,
            $datos['pago_total_obligacion'] ?? null,
            $datos['mora_actual'] ?? null,
            $datos['propiedad'] ?? null,
            $datos['producto'] ?? null,
            $datos['medicion'] ?? null,
            $datos['estado'] ?? 'activa',
            $id
        ]);
    }
    
    /**
     * Eliminar una obligación
     */
    public function eliminarObligacion($id) {
        $sql = "DELETE FROM obligaciones WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Obtener estadísticas de obligaciones por cliente
     */
    public function getEstadisticasObligaciones($cedula) {
        $sql = "SELECT 
                    COUNT(*) as total_obligaciones,
                    SUM(saldo_k_obligacion) as saldo_total,
                    SUM(capital_cliente) as capital_total,
                    AVG(mora_actual) as mora_promedio,
                    COUNT(CASE WHEN estado = 'activa' THEN 1 END) as obligaciones_activas,
                    COUNT(CASE WHEN estado = 'pagada' THEN 1 END) as obligaciones_pagadas,
                    COUNT(CASE WHEN mora_actual > 30 THEN 1 END) as obligaciones_mora_alta
                FROM obligaciones o
                LEFT JOIN clientes c ON o.cliente_id = c.id
                WHERE c.cedula = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar clientes con múltiples obligaciones
     */
    public function getClientesConMultiplesObligaciones() {
        $sql = "SELECT c.cedula, c.nombre, COUNT(o.id) as total_obligaciones
                FROM clientes c
                INNER JOIN obligaciones o ON c.id = o.cliente_id
                GROUP BY c.cedula, c.nombre
                HAVING COUNT(o.id) > 1
                ORDER BY total_obligaciones DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener obligaciones por estado
     */
    public function getObligacionesByEstado($estado) {
        $sql = "SELECT o.*, c.nombre, c.cedula, c.telefono, c.email
                FROM obligaciones o
                LEFT JOIN clientes c ON o.cliente_id = c.id
                WHERE o.estado = ?
                ORDER BY o.fecha_creacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener obligaciones con mora alta
     */
    public function getObligacionesMoraAlta($dias_mora = 30) {
        $sql = "SELECT o.*, c.nombre, c.cedula, c.telefono, c.email
                FROM obligaciones o
                LEFT JOIN clientes c ON o.cliente_id = c.id
                WHERE o.mora_actual > ? AND o.estado = 'activa'
                ORDER BY o.mora_actual DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dias_mora]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener resumen de obligaciones para dashboard
     */
    public function getResumenObligaciones() {
        $sql = "SELECT 
                    COUNT(*) as total_obligaciones,
                    COUNT(CASE WHEN estado = 'activa' THEN 1 END) as obligaciones_activas,
                    COUNT(CASE WHEN estado = 'pagada' THEN 1 END) as obligaciones_pagadas,
                    COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) as obligaciones_canceladas,
                    COUNT(CASE WHEN mora_actual > 30 THEN 1 END) as obligaciones_mora_alta,
                    SUM(saldo_k_obligacion) as saldo_total,
                    AVG(mora_actual) as mora_promedio
                FROM obligaciones";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
