<?php
/**
 * Controlador para gestión de productos de clientes
 * Maneja todas las operaciones relacionadas con productos y sus clasificaciones
 */

require_once 'BaseController.php';
require_once 'models/ProductoClienteModel.php';

class ProductoClienteController extends BaseController {
    private $productoModel;
    
    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->productoModel = new ProductoClienteModel($pdo);
    }

    /**
     * Muestra la interfaz de gestión de productos para un cliente
     */
    public function gestionarProductos() {
        $this->verificarRol('asesor');
        
        $page_title = "Gestión de Productos";
        $clienteId = $this->getGet('cliente_id');
        
        if (!$clienteId) {
            $this->redirigirConError('index.php?action=mis_clientes', 'ID de cliente no proporcionado');
            return;
        }
        
        $clienteId = $this->validarId($clienteId, 'cliente');
        
        // Obtener productos del cliente
        $productos = $this->productoModel->getProductosByCliente($clienteId);
        $cliente = $this->clienteModel->getClienteById($clienteId);
        
        if (!$cliente) {
            $this->redirigirConError('index.php?action=mis_clientes', 'Cliente no encontrado');
            return;
        }
        
        $this->renderizarVista('asesor_gestion_productos', [
            'productos' => $productos,
            'cliente' => $cliente,
            'page_title' => $page_title
        ]);
    }

    /**
     * Crea un nuevo producto para un cliente
     */
    public function crearProducto() {
        $this->verificarRol('asesor');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigirConError('index.php?action=mis_clientes', 'Método no permitido');
            return;
        }
        
        try {
            $datos = $this->sanitizarDatos($_POST);
            
            // Validar datos requeridos
            $errores = $this->validarDatosRequeridos($datos, [
                'cliente_id', 'nombre_producto', 'valor_producto'
            ]);
            
            if (!empty($errores)) {
                $this->redirigirConError('index.php?action=mis_clientes', implode(', ', $errores));
                return;
            }
            
            $datos['asesor_id'] = $_SESSION['user_id'];
            $datos['estado_producto'] = 'pendiente';
            
            $productoId = $this->productoModel->crearProducto($datos);
            
            if ($productoId) {
                $this->redirigirConExito(
                    'index.php?action=gestionar_productos&cliente_id=' . $datos['cliente_id'],
                    'Producto creado exitosamente'
                );
            } else {
                $this->redirigirConError(
                    'index.php?action=mis_clientes',
                    'Error al crear el producto'
                );
            }
        } catch (Exception $e) {
            error_log("Error creando producto: " . $e->getMessage());
            $this->redirigirConError('index.php?action=mis_clientes', 'Error interno del servidor');
        }
    }

    /**
     * Registra una gestión/clasificación de un producto
     */
    public function registrarGestionProducto() {
        $this->verificarRol('asesor');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigirConError('index.php?action=mis_clientes', 'Método no permitido');
            return;
        }
        
        try {
            $datos = $this->sanitizarDatos($_POST);
            
            // Validar datos requeridos
            $errores = $this->validarDatosRequeridos($datos, [
                'producto_id', 'tipo_gestion', 'resultado_gestion'
            ]);
            
            if (!empty($errores)) {
                $this->redirigirConError('index.php?action=mis_clientes', implode(', ', $errores));
                return;
            }
            
            $datos['asesor_id'] = $_SESSION['user_id'];
            
            // Procesar canales autorizados
            if (isset($datos['canales']) && is_array($datos['canales'])) {
                $datos['canales_autorizados'] = implode(',', $datos['canales']);
            }
            
            $gestionId = $this->productoModel->registrarGestionProducto($datos['producto_id'], $datos);
            
            if ($gestionId) {
                $this->redirigirConExito(
                    'index.php?action=gestionar_productos&cliente_id=' . $datos['cliente_id'],
                    'Gestión registrada exitosamente'
                );
            } else {
                $this->redirigirConError(
                    'index.php?action=mis_clientes',
                    'Error al registrar la gestión'
                );
            }
        } catch (Exception $e) {
            error_log("Error registrando gestión de producto: " . $e->getMessage());
            $this->redirigirConError('index.php?action=mis_clientes', 'Error interno del servidor');
        }
    }

    /**
     * Obtiene el historial de gestiones de un producto (AJAX)
     */
    public function obtenerHistorialProducto() {
        $this->verificarRol('asesor');
        
        // Limpiar output previo
        ob_clean();
        
        try {
            $productoId = $this->getGet('producto_id');
            $productoId = $this->validarId($productoId, 'producto');
            
            $historial = $this->productoModel->getHistorialGestionesProducto($productoId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'historial' => $historial
            ]);
        } catch (Exception $e) {
            error_log("Error obteniendo historial de producto: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error obteniendo historial'
            ]);
        }
    }

    /**
     * Obtiene productos pendientes de gestión (AJAX)
     */
    public function obtenerProductosPendientes() {
        $this->verificarRol('asesor');
        
        // Limpiar output previo
        ob_clean();
        
        try {
            $asesorId = $_SESSION['user_id'];
            $productos = $this->productoModel->getProductosPendientesByAsesor($asesorId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'productos' => $productos
            ]);
        } catch (Exception $e) {
            error_log("Error obteniendo productos pendientes: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error obteniendo productos pendientes'
            ]);
        }
    }

    /**
     * Declina todos los productos de un cliente
     */
    public function declinarTodosProductos() {
        $this->verificarRol('asesor');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigirConError('index.php?action=mis_clientes', 'Método no permitido');
            return;
        }
        
        try {
            $clienteId = $this->getPost('cliente_id');
            $clienteId = $this->validarId($clienteId, 'cliente');
            
            // Obtener todos los productos del cliente
            $productos = $this->productoModel->getProductosByCliente($clienteId);
            
            $productosDeclinados = 0;
            foreach ($productos as $producto) {
                if ($producto['estado_producto'] === 'pendiente') {
                    $datosGestion = [
                        'asesor_id' => $_SESSION['user_id'],
                        'tipo_gestion' => 'hacer_llamada',
                        'resultado_gestion' => '12', // RENUENTE
                        'comentarios' => 'Cliente rechaza todos los productos',
                        'monto_gestion' => 0,
                        'estado_producto' => 'rechazado'
                    ];
                    
                    if ($this->productoModel->registrarGestionProducto($producto['id'], $datosGestion)) {
                        $productosDeclinados++;
                    }
                }
            }
            
            $this->redirigirConExito(
                'index.php?action=gestionar_productos&cliente_id=' . $clienteId,
                "Se declinaron {$productosDeclinados} productos exitosamente"
            );
        } catch (Exception $e) {
            error_log("Error declinando productos: " . $e->getMessage());
            $this->redirigirConError('index.php?action=mis_clientes', 'Error interno del servidor');
        }
    }

    /**
     * Obtiene estadísticas de productos para el dashboard del asesor
     */
    public function obtenerEstadisticasProductos() {
        $this->verificarRol('asesor');
        
        // Limpiar output previo
        ob_clean();
        
        try {
            $asesorId = $_SESSION['user_id'];
            $fechaInicio = $this->getGet('fecha_inicio');
            $fechaFin = $this->getGet('fecha_fin');
            
            $estadisticas = $this->productoModel->getEstadisticasProductosByAsesor(
                $asesorId, 
                $fechaInicio, 
                $fechaFin
            );
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de productos: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error obteniendo estadísticas'
            ]);
        }
    }

    /**
     * Exporta productos para reporte del coordinador
     */
    public function exportarProductos() {
        $this->verificarRol('coordinador');
        
        try {
            $coordinadorId = $_SESSION['user_id'];
            $fechaInicio = $this->getGet('fecha_inicio');
            $fechaFin = $this->getGet('fecha_fin');
            $asesorId = $this->getGet('asesor_id');
            
            $productos = $this->productoModel->getProductosParaReporte(
                $coordinadorId, 
                $fechaInicio, 
                $fechaFin, 
                $asesorId
            );
            
            // Generar CSV
            $filename = 'productos_gestion_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($output, [
                'ID Producto',
                'Cliente',
                'Cédula',
                'Teléfono',
                'Producto',
                'Valor',
                'Estado',
                'Asesor',
                'Última Gestión',
                'Tipo Clasificación',
                'Resultado',
                'Monto Pagado',
                'Fecha Pago',
                'Comentarios',
                'Base de Datos'
            ]);
            
            // Datos
            foreach ($productos as $producto) {
                fputcsv($output, [
                    $producto['id'],
                    $producto['cliente_nombre'],
                    $producto['cedula'],
                    $producto['telefono'],
                    $producto['nombre_producto'],
                    $producto['valor_producto'],
                    $producto['estado_producto'],
                    $producto['asesor_nombre'],
                    $producto['ultima_gestion'],
                    $producto['tipo_clasificacion'],
                    $producto['resultado_clasificacion'],
                    $producto['monto_pagado'],
                    $producto['fecha_pago'],
                    $producto['comentarios'],
                    $producto['base_datos_nombre']
                ]);
            }
            
            fclose($output);
            exit;
        } catch (Exception $e) {
            error_log("Error exportando productos: " . $e->getMessage());
            $this->redirigirConError('index.php?action=reportes_exportacion', 'Error al exportar productos');
        }
    }
}
?>
