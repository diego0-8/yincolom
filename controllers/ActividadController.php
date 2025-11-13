<?php
// Archivo: controllers/ActividadController.php
// Controlador para el manejo de actividades en tiempo real

require_once 'BaseController.php';
require_once 'models/ActividadProductoModel.php';

class ActividadController extends BaseController {
    private $actividadModel;
    
    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->actividadModel = new ActividadProductoModel($pdo);
    }
    
    /**
     * Obtiene actividades en tiempo real
     */
    public function obtenerActividadesTiempoReal() {
        try {
            // Establecer headers para JSON
            header('Content-Type: application/json');
            
            $asesorId = $_SESSION['user_id'] ?? null;
            $limit = $this->getGet('limit', 50);
            
            // Si no hay sesión, obtener actividades de todos los asesores (modo de prueba)
            if (!$asesorId) {
                $asesorId = null;
            }
            
            $actividades = $this->actividadModel->getActividadesTiempoReal($asesorId, $limit);
            
            echo json_encode([
                'success' => true,
                'actividades' => $actividades,
                'total' => count($actividades)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene actividades de un cliente específico
     */
    public function obtenerActividadesCliente() {
        try {
            $clienteId = $this->getGet('cliente_id');
            $limit = $this->getGet('limit', 100);
            
            if (!$clienteId) {
                throw new Exception("ID de cliente no proporcionado");
            }
            
            $actividades = $this->actividadModel->getActividadesCliente($clienteId, $limit);
            
            echo json_encode([
                'success' => true,
                'actividades' => $actividades,
                'total' => count($actividades)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene actividades de un producto específico
     */
    public function obtenerActividadesProducto() {
        try {
            $productoId = $this->getGet('producto_id');
            $clienteId = $this->getGet('cliente_id');
            
            if (!$productoId) {
                throw new Exception("ID de producto no proporcionado");
            }
            
            $actividades = $this->actividadModel->getActividadesProducto($productoId, $clienteId);
            
            echo json_encode([
                'success' => true,
                'actividades' => $actividades,
                'total' => count($actividades)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene estadísticas de actividades
     */
    public function obtenerEstadisticasActividades() {
        try {
            $asesorId = $_SESSION['user_id'] ?? null;
            $periodo = $this->getGet('periodo', 'dia');
            
            if (!$asesorId) {
                throw new Exception("Usuario no autenticado");
            }
            
            $estadisticas = $this->actividadModel->getEstadisticasActividades($asesorId, $periodo);
            
            echo json_encode([
                'success' => true,
                'estadisticas' => $estadisticas,
                'periodo' => $periodo
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene el historial completo de actividades con filtros
     */
    public function obtenerHistorialCompleto() {
        try {
            $asesorId = $_SESSION['user_id'] ?? null;
            $clienteId = $this->getGet('cliente_id');
            $tipoActividad = $this->getGet('tipo_actividad');
            $fechaInicio = $this->getGet('fecha_inicio');
            $fechaFin = $this->getGet('fecha_fin');
            $limit = $this->getGet('limit', 100);
            
            if (!$asesorId) {
                throw new Exception("Usuario no autenticado");
            }
            
            // Construir consulta con filtros
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
                    WHERE ap.asesor_id = ?";
            
            $params = [$asesorId];
            
            if ($clienteId) {
                $sql .= " AND ap.cliente_id = ?";
                $params[] = $clienteId;
            }
            
            if ($tipoActividad) {
                $sql .= " AND ap.tipo_actividad = ?";
                $params[] = $tipoActividad;
            }
            
            if ($fechaInicio) {
                $sql .= " AND ap.timestamp_actividad >= ?";
                $params[] = $fechaInicio;
            }
            
            if ($fechaFin) {
                $sql .= " AND ap.timestamp_actividad <= ?";
                $params[] = $fechaFin;
            }
            
            $sql .= " ORDER BY ap.timestamp_actividad DESC LIMIT ?";
            $params[] = $limit;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'actividades' => $actividades,
                'total' => count($actividades),
                'filtros' => [
                    'cliente_id' => $clienteId,
                    'tipo_actividad' => $tipoActividad,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ]
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
?>
