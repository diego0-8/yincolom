<?php
// Archivo: index.php
// Este es el router principal de la aplicación.
// Incluye los archivos necesarios y maneja las peticiones.

require_once 'config.php';

// El gestor de sesiones ya está configurado en config.php
// No necesitamos llamar session_start() manualmente
require_once 'models/UsuarioModel.php';
require_once 'models/CargaExcelModel.php';
require_once 'models/ClienteModel.php';
require_once 'models/GestionModel.php';
require_once 'models/TareaModel.php';
require_once 'controllers/adminController.php';
require_once 'controllers/CoordinadorController.php';
require_once 'controllers/AsesorController.php';
require_once 'controllers/ProductoClienteController.php';
require_once 'controllers/ActividadController.php';

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Conexión a la base de datos exitosa");
} catch (PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("Error de conexión: " . $e->getMessage());
}

$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$session_manager = getSessionManager();
$user_role = $session_manager->getUserRole();

// Lógica de ruteo y control de sesión
// SOLUCIÓN AL BUCLE: Solo redirigir si no hay sesión Y la acción no es login
if (!$session_manager->isLoggedIn() && $action !== 'login') {
    // En lugar de cambiar $action, redirigir directamente
    header('Location: index.php?action=login');
    exit;
}

// Validar que el rol del usuario esté definido
if ($session_manager->isLoggedIn() && empty($user_role)) {
    // Si hay usuario pero no rol, cerrar sesión y redirigir
    $session_manager->logout();
    header('Location: index.php?action=login');
    exit;
}

// Este switch maneja todas las rutas, llamando a la lógica del controlador apropiado.
// La instanciación del controlador se mueve dentro de cada case para asegurar que
// siempre se tenga el controlador correcto.
switch ($action) {
    case 'login':
        $controller = new AdminController($pdo);
        $controller->login();
        break;
        
    case 'logout':
        $controller = new AdminController($pdo);
        $controller->logout();
        break;
        
    case 'dashboard':
        // Se instancia el controlador aquí, basándose en el rol del usuario
        if ($user_role === 'administrador') {
            $controller = new AdminController($pdo);
            $controller->dashboard();
        } elseif ($user_role === 'coordinador') {
            $controller = new CoordinadorController($pdo);
            $controller->dashboard();
        } elseif ($user_role === 'asesor') {
            $controller = new AsesorController($pdo);
            $controller->dashboard();
        } else {
            // Si no hay rol, redirigir al login
            header('Location: index.php?action=login');
            exit;
        }
        break;
        
    // Acciones para el rol de Admin
    case 'list_usuarios':
    case 'crear_usuario':
    case 'editar_usuario':
    case 'toggle_estado':
    case 'ver_actividades':
    case 'asignar_personal':
    case 'ver_gestion_coordinador':
    case 'ver_gestion_asesor':
    case 'asignar_asesor':
    case 'liberar_asesor':
        if ($user_role === 'administrador') {
            $controller = new AdminController($pdo);
            if ($action === 'list_usuarios') $controller->listUsuarios();
            if ($action === 'crear_usuario') $controller->createUsuario();
            if ($action === 'editar_usuario' && isset($_GET['id'])) $controller->editUsuario($_GET['id']);
            if ($action === 'toggle_estado' && isset($_GET['id'])) $controller->toggleEstadoUsuario($_GET['id']);
            if ($action === 'ver_actividades') $controller->verActividades();
            if ($action === 'asignar_personal') $controller->asignarPersonal();
            if ($action === 'ver_gestion_coordinador' && isset($_GET['id'])) $controller->verGestionCoordinador($_GET['id']);
            if ($action === 'ver_gestion_asesor' && isset($_GET['id'])) $controller->verGestionAsesor($_GET['id']);
            if ($action === 'asignar_asesor') $controller->asignarAsesor();
            if ($action === 'liberar_asesor' && isset($_GET['asesor_id']) && isset($_GET['coordinador_id'])) $controller->liberarAsesor($_GET['asesor_id'], $_GET['coordinador_id']);
        } else {
            header('Location: index.php?action=login');
            exit;
        }
        break;
        
    // Acciones para el rol de Coordinador
    case 'tareas_coordinador':
    case 'gestionar_tareas':
        $controller = new CoordinadorController($pdo);
        $controller->gestionarTareas();
        break;
    case 'crear_tarea':
        $controller = new CoordinadorController($pdo);
        $controller->crearTarea();
        break;
    case 'asignar_base_completa':
        $controller = new CoordinadorController($pdo);
        $controller->asignarBaseCompleta();
        break;
    case 'liberar_base':
        $controller = new CoordinadorController($pdo);
        $controller->liberarBase();
        break;
    case 'get_clientes_carga':
        $controller = new CoordinadorController($pdo);
        $controller->getClientesCarga();
        break;
    case 'get_asesores_disponibles_carga':
        $controller = new CoordinadorController($pdo);
        $controller->getAsesoresDisponiblesCarga();
        break;
    case 'get_bases_asignadas_asesor':
        $controller = new CoordinadorController($pdo);
        $controller->getBasesAsignadasAsesor();
        break;
    case 'actualizar_estado_tarea':
        $controller = new CoordinadorController($pdo);
        $controller->actualizarEstadoTarea();
        break;
    case 'get_asesores_base':
        $controller = new CoordinadorController($pdo);
        $controller->getAsesoresBase();
        break;
    case 'get_clientes_no_gestionados':
        $controller = new CoordinadorController($pdo);
        $controller->getClientesNoGestionados();
        break;
    case 'gestionar_traspasos':
    case 'subir_excel':
    case 'crear_nueva_base':
    case 'gestion_cargas':
    case 'list_cargas':
    case 'descargas':
    case 'get_detalles_asesor':
    case 'ver_detalle_cliente':
    case 'ver_detalle_gestion_asesor':
    case 'agregar_a_base_existente':
    case 'liberar_clientes':
    case 'asignarClientes':
    case 'asignar_automatico':
    case 'resultados_equipo':
    case 'reportes_exportacion':
    case 'ver_clientes':
    case 'buscar_clientes':
    case 'asignar_clientes':
    case 'ver_gestion_asesor':
    case 'get_asesores_disponibles':
    case 'get_asesores_asignados':
    case 'asignar_asesor_base':
    case 'liberar_asesor_base':
    case 'eliminar_base_datos':
    case 'gestionar_estado_bases':
    case 'cambiar_estado_base':
    case 'buscar_bases_datos':
    case 'transferir_recordatorio':
        if ($user_role === 'coordinador') {
            $controller = new CoordinadorController($pdo);
            if ($action === 'dashboard') $controller->dashboard();
            if ($action === 'tareas_coordinador') $controller->tareas();
            if ($action === 'gestionar_traspasos') $controller->gestionarTraspasos();
            if ($action === 'subir_excel') $controller->uploadExcel();
            if ($action === 'crear_nueva_base') $controller->crearNuevaBase();
            if ($action === 'gestion_cargas') $controller->gestionCargas();
            if ($action === 'list_cargas') $controller->listCargas();
            if ($action === 'descargas') $controller->descargas();
            if ($action === 'get_detalles_asesor') $controller->getDetallesAsesor();
            if ($action === 'ver_detalle_cliente' && isset($_GET['id'])) $controller->verDetalleCliente($_GET['id']);
            if ($action === 'ver_detalle_gestion_asesor' && isset($_GET['cliente_id']) && isset($_GET['asesor_id'])) $controller->verDetalleGestionAsesor($_GET['cliente_id'], $_GET['asesor_id']);
            if ($action === 'agregar_a_base_existente') $controller->agregarABaseExistente();
            if ($action === 'liberar_clientes') $controller->liberarTodosClientes();
            if ($action === 'asignarClientes') $controller->asignarClientes();
            if ($action === 'asignar_automatico') $controller->asignarAutomatico();
            if ($action === 'resultados_equipo') $controller->resultadosEquipo();
            if ($action === 'reportes_exportacion') $controller->reportesExportacion();
            if ($action === 'ver_clientes') $controller->verClientes();
            if ($action === 'buscar_clientes') $controller->buscarClientes();
            if ($action === 'asignar_clientes') $controller->asignarClientesVista();
            if ($action === 'ver_gestion_asesor') $controller->verGestionAsesor();
            if ($action === 'get_asesores_disponibles') $controller->getAsesoresDisponibles();
            if ($action === 'get_asesores_asignados') $controller->getAsesoresAsignados();
            if ($action === 'asignar_asesor_base') $controller->asignarAsesorBase();
            if ($action === 'liberar_asesor_base') $controller->liberarAsesorBase();
            if ($action === 'eliminar_base_datos') $controller->eliminarBaseDatos();
            if ($action === 'gestionar_estado_bases') $controller->gestionarEstadoBases();
            if ($action === 'cambiar_estado_base') $controller->cambiarEstadoBase();
            if ($action === 'buscar_bases_datos') $controller->buscarBasesDatos();
            if ($action === 'transferir_recordatorio') $controller->transferirRecordatorio();
        } else {
            header('Location: index.php?action=login');
            exit;
        }
        break;
        
    // Acciones para actividades en tiempo real
    case 'obtener_actividades_tiempo_real':
    case 'obtener_actividades_cliente':
    case 'obtener_actividades_producto':
    case 'obtener_estadisticas_actividades':
    case 'obtener_historial_completo':
        // Permitir acceso sin autenticación para pruebas
        $controller = new ActividadController($pdo);
        if ($action === 'obtener_actividades_tiempo_real') $controller->obtenerActividadesTiempoReal();
        if ($action === 'obtener_actividades_cliente') $controller->obtenerActividadesCliente();
        if ($action === 'obtener_actividades_producto') $controller->obtenerActividadesProducto();
        if ($action === 'obtener_estadisticas_actividades') $controller->obtenerEstadisticasActividades();
        if ($action === 'obtener_historial_completo') $controller->obtenerHistorialCompleto();
        break;
        
    // Acciones de exportación para coordinadores
    case 'exportar_gestion_asesor':
    case 'exportar_gestion_todos_asesores':
    case 'exportar_reporte_personalizado':
    case 'exportar_clientes':
    case 'exportar_cargas':
    case 'exportar_productos':
        if ($user_role === 'coordinador') {
            $controller = new CoordinadorController($pdo);
            if ($action === 'exportar_gestion_asesor') {
                // Recopilar todos los filtros del modal
                $filtros = [
                    'gestion' => $_GET['gestion'] ?? null,
                    'contacto' => $_GET['contacto'] ?? null,
                    'tipificacion' => $_GET['tipificacion'] ?? null,
                    'fecha_creacion_inicio' => $_GET['fecha_creacion_inicio'] ?? null,
                    'fecha_creacion_fin' => $_GET['fecha_creacion_fin'] ?? null
                ];
                $controller->exportarGestionAsesor(
                    $_GET['asesor_id'] ?? null, 
                    $_GET['fecha_inicio'] ?? null, 
                    $_GET['fecha_fin'] ?? null,
                    $filtros
                );
            }
            if ($action === 'exportar_gestion_todos_asesores') $controller->exportarGestionTodosAsesores($_GET['fecha_inicio'] ?? null, $_GET['fecha_fin'] ?? null);
            if ($action === 'exportar_reporte_personalizado') $controller->exportarReportePersonalizado($_GET);
            if ($action === 'exportar_clientes') $controller->exportarClientes($_GET['fecha_inicio'] ?? null, $_GET['fecha_fin'] ?? null, $_GET['estado_cliente'] ?? null);
            if ($action === 'exportar_cargas') $controller->exportarCargas($_GET['estado_carga'] ?? null);
            if ($action === 'exportar_productos') {
                $controller = new ProductoClienteController($pdo);
                $controller->exportarProductos();
            }
        } else {
            header('Location: index.php?action=login');
            exit;
        }
        break;
        
    // Acciones para el rol de Asesor
    case 'mis_clientes':
    case 'gestionar_cliente':
    case 'guardar_tipificacion':
    case 'guardar_cliente_nuevo':
    case 'obtener_siguiente_cliente':
    case 'obtener_historial_cliente':
    case 'obtener_datos_cliente':
    case 'gestionar_clientes':
    case 'buscar_cliente_por_cedula':
    case 'get_cliente_para_gestion':
    case 'get_tareas_pendientes':
    case 'completar_tarea':
    case 'gestionar_productos_cliente':
        if ($user_role === 'asesor') {
            $controller = new AsesorController($pdo);
            if ($action === 'mis_clientes') $controller->misClientes();
            if ($action === 'gestionar_cliente' && isset($_GET['id'])) $controller->gestionarCliente($_GET['id']);
            if ($action === 'guardar_tipificacion') $controller->guardarTipificacion();
            if ($action === 'guardar_cliente_nuevo') $controller->guardarClienteNuevo();
            if ($action === 'obtener_siguiente_cliente') $controller->obtenerSiguienteCliente();
            if ($action === 'obtener_historial_cliente' && isset($_GET['id'])) $controller->obtenerHistorialCliente();
            if ($action === 'obtener_datos_cliente') $controller->obtenerDatosCliente();
            if ($action === 'gestionar_clientes') $controller->gestionarClientes();
            if ($action === 'buscar_cliente_por_cedula') $controller->buscarClientePorCedula();
            if ($action === 'get_cliente_para_gestion') $controller->getClienteParaGestion();
            if ($action === 'get_tareas_pendientes') $controller->getTareasPendientes();
            if ($action === 'completar_tarea') $controller->completarTarea();
            if ($action === 'obtener_detalles_gestion') $controller->obtenerDetallesGestion();
            if ($action === 'gestionar_productos_cliente') $controller->gestionarProductosCliente();
        } else {
            header('Location: index.php?action=login');
            exit;
        }
        break;
        
    // Acciones para gestión de productos
    case 'gestionar_productos':
    case 'crear_producto':
    case 'registrar_gestion_producto':
    case 'obtener_historial_producto':
    case 'obtener_productos_pendientes':
    case 'declinar_todos_productos':
    case 'obtener_estadisticas_productos':
        if ($user_role === 'asesor') {
            $controller = new ProductoClienteController($pdo);
            if ($action === 'gestionar_productos') $controller->gestionarProductos();
            if ($action === 'crear_producto') $controller->crearProducto();
            if ($action === 'registrar_gestion_producto') $controller->registrarGestionProducto();
            if ($action === 'obtener_historial_producto') $controller->obtenerHistorialProducto();
            if ($action === 'obtener_productos_pendientes') $controller->obtenerProductosPendientes();
            if ($action === 'declinar_todos_productos') $controller->declinarTodosProductos();
            if ($action === 'obtener_estadisticas_productos') $controller->obtenerEstadisticasProductos();
        } else {
            header('Location: index.php?action=login');
            exit;
        }
        break;
        
    // Acción para obtener datos de teléfono (disponible para todos los roles)
    case 'get_telefono_data':
        // Limpiar cualquier output previo
        ob_clean();
        
        try {
            $session_manager = getSessionManager();
            $usuarioModel = new UsuarioModel($pdo);
            $datosTelefono = $usuarioModel->getDatosTelefono($session_manager->getUserId());
            $tieneTelefono = $usuarioModel->tieneTelefonoConfigurado($session_manager->getUserId());
            
            header('Content-Type: application/json');
            header('Cache-Control: no-cache, must-revalidate');
            echo json_encode([
                'success' => true,
                'extension' => $datosTelefono['extension_telefono'] ?? '',
                'clave' => $datosTelefono['clave_webrtc'] ?? '',
                'tiene_telefono' => $tieneTelefono
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error obteniendo datos de teléfono: ' . $e->getMessage()
            ]);
        }
        exit;
        break;
        
    // Acción para buscar clientes (disponible para asesores)
    case 'buscar_cliente':
        // Limpiar cualquier output previo
        ob_clean();
        
        try {
            // Verificar que el usuario esté logueado y sea asesor
            $session_manager = getSessionManager();
            if (!$session_manager->isLoggedIn() || $session_manager->getUserRole() !== 'asesor') {
                throw new Exception('Acceso no autorizado');
            }
            
            // Obtener datos del POST
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['tipo']) || !isset($input['termino'])) {
                throw new Exception('Datos de búsqueda incompletos');
            }
            
            $tipo = $input['tipo'];
            $termino = trim($input['termino']);
            
            if (empty($termino)) {
                throw new Exception('El término de búsqueda no puede estar vacío');
            }
            
            // Incluir el modelo de clientes
            require_once 'models/ClienteModel.php';
            $clienteModel = new ClienteModel($pdo);
            
            // Realizar búsqueda según el tipo
            $clientes = [];
            if ($tipo === 'telefono') {
                $clientes = $clienteModel->buscarPorTelefono($termino);
            } elseif ($tipo === 'cedula') {
                $clientes = $clienteModel->buscarPorCedula($termino);
            } else {
                throw new Exception('Tipo de búsqueda no válido');
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'clientes' => $clientes
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
        break;
        
        
    default:
        // Si la acción no coincide, redirigir al dashboard (o login si no está logueado)
        $session_manager = getSessionManager();
        if ($session_manager->isLoggedIn()) {
            header('Location: index.php?action=dashboard');
        } else {
            header('Location: index.php?action=login');
        }
        exit;
}
?>
