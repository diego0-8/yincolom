<?php
// Archivo: index.php
// Este es el router principal de la aplicación.
// Incluye los archivos necesarios y maneja las peticiones.

// MEJORA: Iniciar output buffer temprano para capturar cualquier output inesperado
ob_start();

// MEJORA: Configurar manejo de errores desde el inicio
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en output
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// MEJORA: Función helper para logging estructurado
function logError($message, $context = [], $level = 'ERROR')
{
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    error_log("[{$timestamp}] [{$level}] {$message}{$contextStr}");
}

// MEJORA: Función helper para enviar respuesta JSON de error
function sendJsonError($message, $errorCode = 'GENERAL_ERROR', $httpCode = 500)
{
    // Limpiar cualquier output previo
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Establecer headers
    if (!headers_sent()) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('X-Content-Type-Options: nosniff');
    }

    // Enviar respuesta JSON
    echo json_encode([
        'success' => false,
        'message' => $message,
        'error_code' => $errorCode
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// MEJORA: Lista de acciones que son APIs (deben devolver JSON)
$apiActions = [
    'guardar_tipificacion',
    'get_productos_cliente',
    'obtener_siguiente_cliente',
    'obtener_historial_cliente',
    'obtener_datos_cliente',
    'buscar_cliente_por_cedula',
    'get_cliente_para_gestion',
    'get_tareas_pendientes',
    'completar_tarea',
    'obtener_detalles_gestion',
    'agregar_informacion_cliente',
    'get_telefono_data',
    'buscar_cliente',
    'get_clientes_carga',
    'get_asesores_disponibles_carga',
    'get_bases_asignadas_asesor',
    'actualizar_estado_tarea',
    'get_asesores_base',
    'get_clientes_no_gestionados',
    'get_detalles_asesor',
    'get_asesores_disponibles',
    'get_asesores_asignados',
    'buscar_clientes',
    'buscar_bases_datos',
    'obtener_actividades_tiempo_real',
    'obtener_actividades_cliente',
    'obtener_actividades_producto',
    'obtener_estadisticas_actividades',
    'obtener_historial_completo',
    'crear_producto',
    'registrar_gestion_producto',
    'obtener_historial_producto',
    'obtener_productos_pendientes',
    'declinar_todos_productos',
    'obtener_estadisticas_productos',
    'registrar_break',
    'verificar_contrasena_desbloqueo',
    'obtener_break_activo'
];

try {
    require_once 'config.php';
} catch (Exception $e) {
    logError("Error al cargar config.php", ['exception' => $e->getMessage()]);
    sendJsonError('Error de configuración del sistema', 'CONFIG_ERROR', 500);
} catch (Throwable $e) {
    logError("Error fatal al cargar config.php", ['exception' => $e->getMessage()]);
    sendJsonError('Error crítico de configuración', 'CONFIG_FATAL_ERROR', 500);
}

// El gestor de sesiones ya está configurado en config.php
// No necesitamos llamar session_start() manualmente
try {
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
} catch (Exception $e) {
    logError("Error al cargar modelos/controladores", ['exception' => $e->getMessage()]);
    sendJsonError('Error al cargar componentes del sistema', 'LOAD_ERROR', 500);
} catch (Throwable $e) {
    logError("Error fatal al cargar modelos/controladores", ['exception' => $e->getMessage()]);
    sendJsonError('Error crítico al cargar componentes', 'LOAD_FATAL_ERROR', 500);
}

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_PERSISTENT, false);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    logError("Conexión a la base de datos exitosa", [], 'INFO');
} catch (PDOException $e) {
    logError("Error de conexión a la base de datos", ['exception' => $e->getMessage(), 'code' => $e->getCode()]);
    sendJsonError('Error de conexión a la base de datos', 'DB_CONNECTION_ERROR', 500);
} catch (Exception $e) {
    logError("Error inesperado al conectar a la base de datos", ['exception' => $e->getMessage()]);
    sendJsonError('Error al conectar con la base de datos', 'DB_ERROR', 500);
}

// MEJORA: Validar y sanitizar action
$action = isset($_GET['action']) ? trim($_GET['action']) : 'login';
if (empty($action)) {
    $action = 'login';
}

// MEJORA: Detectar si es una acción API
$isApiAction = in_array($action, $apiActions);

// MEJORA: Para acciones API, configurar límites y headers temprano
if ($isApiAction) {
    set_time_limit(30); // Máximo 30 segundos
    ini_set('memory_limit', '256M');

    // Limpiar output buffer y establecer headers JSON
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    ob_start();

    // Establecer headers JSON ANTES de cualquier operación
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('X-Content-Type-Options: nosniff');
        // Headers CORS si es necesario
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }
}

try {
    $session_manager = getSessionManager();
    $user_role = $session_manager->getUserRole();
} catch (Exception $e) {
    logError("Error al obtener sesión", ['exception' => $e->getMessage()]);
    if ($isApiAction) {
        sendJsonError('Error de sesión', 'SESSION_ERROR', 401);
    } else {
        header('Location: index.php?action=login');
        exit;
    }
}

// MEJORA: Validaciones de entrada básicas
if ($isApiAction) {
    // Para APIs, validar método HTTP si es necesario
    $requiredMethod = in_array($action, ['guardar_tipificacion', 'completar_tarea', 'crear_producto', 'registrar_gestion_producto']) ? 'POST' : 'GET';
    if ($_SERVER['REQUEST_METHOD'] !== $requiredMethod && $requiredMethod === 'POST') {
        sendJsonError('Método HTTP no permitido. Se requiere POST.', 'METHOD_NOT_ALLOWED', 405);
    }
}

// Lógica de ruteo y control de sesión
// SOLUCIÓN AL BUCLE: Solo redirigir si no hay sesión Y la acción no es login
if (!$session_manager->isLoggedIn() && $action !== 'login') {
    if ($isApiAction) {
        sendJsonError('Sesión no válida. Por favor, inicia sesión nuevamente.', 'UNAUTHORIZED', 401);
    } else {
        // En lugar de cambiar $action, redirigir directamente
        header('Location: index.php?action=login');
        exit;
    }
}

// Validar que el rol del usuario esté definido
if ($session_manager->isLoggedIn() && empty($user_role)) {
    // Si hay usuario pero no rol, cerrar sesión y redirigir
    $session_manager->logout();
    if ($isApiAction) {
        sendJsonError('Rol de usuario no válido', 'INVALID_ROLE', 403);
    } else {
        header('Location: index.php?action=login');
        exit;
    }
}

// MEJORA: Try-catch global alrededor del switch para capturar errores inesperados
try {
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
        case 'asignar_asesor':
        case 'liberar_asesor':
            if ($user_role === 'administrador') {
                $controller = new AdminController($pdo);
                if ($action === 'list_usuarios')
                    $controller->listUsuarios();
                if ($action === 'crear_usuario')
                    $controller->createUsuario();
                if ($action === 'editar_usuario' && isset($_GET['id']))
                    $controller->editUsuario($_GET['id']);
                if ($action === 'toggle_estado' && isset($_GET['id']))
                    $controller->toggleEstadoUsuario($_GET['id']);
                if ($action === 'ver_actividades')
                    $controller->verActividades();
                if ($action === 'asignar_personal')
                    $controller->asignarPersonal();
                if ($action === 'ver_gestion_coordinador' && isset($_GET['id']))
                    $controller->verGestionCoordinador($_GET['id']);
                if ($action === 'asignar_asesor')
                    $controller->asignarAsesor();
                if ($action === 'liberar_asesor' && isset($_GET['asesor_id']) && isset($_GET['coordinador_id']))
                    $controller->liberarAsesor($_GET['asesor_id'], $_GET['coordinador_id']);
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
        case 'tmo':
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
                if ($action === 'gestionar_traspasos')
                    $controller->gestionarTraspasos();
                if ($action === 'subir_excel')
                    $controller->uploadExcel();
                if ($action === 'crear_nueva_base')
                    $controller->crearNuevaBase();
                if ($action === 'gestion_cargas')
                    $controller->gestionCargas();
                if ($action === 'list_cargas')
                    $controller->listCargas();
                if ($action === 'descargas')
                    $controller->descargas();
                if ($action === 'get_detalles_asesor')
                    $controller->getDetallesAsesor();
                if ($action === 'ver_detalle_cliente' && isset($_GET['id']))
                    $controller->verDetalleCliente($_GET['id']);
                if ($action === 'ver_detalle_gestion_asesor' && isset($_GET['cliente_id']) && isset($_GET['asesor_id']))
                    $controller->verDetalleGestionAsesor($_GET['cliente_id'], $_GET['asesor_id']);
                if ($action === 'agregar_a_base_existente')
                    $controller->agregarABaseExistente();
                if ($action === 'liberar_clientes')
                    $controller->liberarTodosClientes();
                if ($action === 'asignarClientes')
                    $controller->asignarClientes();
                if ($action === 'asignar_automatico')
                    $controller->asignarAutomatico();
                if ($action === 'resultados_equipo')
                    $controller->resultadosEquipo();
                if ($action === 'reportes_exportacion')
                    $controller->reportesExportacion();
                if ($action === 'tmo')
                    $controller->tmo();
                if ($action === 'ver_clientes')
                    $controller->verClientes();
                if ($action === 'buscar_clientes')
                    $controller->buscarClientes();
                if ($action === 'asignar_clientes')
                    $controller->asignarClientesVista();
                if ($action === 'ver_gestion_asesor')
                    $controller->verGestionAsesor();
                if ($action === 'get_asesores_disponibles')
                    $controller->getAsesoresDisponibles();
                if ($action === 'get_asesores_asignados')
                    $controller->getAsesoresAsignados();
                if ($action === 'asignar_asesor_base')
                    $controller->asignarAsesorBase();
                if ($action === 'liberar_asesor_base')
                    $controller->liberarAsesorBase();
                if ($action === 'eliminar_base_datos')
                    $controller->eliminarBaseDatos();
                if ($action === 'gestionar_estado_bases')
                    $controller->gestionarEstadoBases();
                if ($action === 'cambiar_estado_base')
                    $controller->cambiarEstadoBase();
                if ($action === 'buscar_bases_datos')
                    $controller->buscarBasesDatos();
                if ($action === 'transferir_recordatorio')
                    $controller->transferirRecordatorio();
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
            if ($action === 'obtener_actividades_tiempo_real')
                $controller->obtenerActividadesTiempoReal();
            if ($action === 'obtener_actividades_cliente')
                $controller->obtenerActividadesCliente();
            if ($action === 'obtener_actividades_producto')
                $controller->obtenerActividadesProducto();
            if ($action === 'obtener_estadisticas_actividades')
                $controller->obtenerEstadisticasActividades();
            if ($action === 'obtener_historial_completo')
                $controller->obtenerHistorialCompleto();
            break;

        // Acciones de exportación para coordinadores
        case 'exportar_gestion_asesor':
        case 'exportar_gestion_todos_asesores':
        case 'exportar_tmo':
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
                if ($action === 'exportar_gestion_todos_asesores')
                    $controller->exportarGestionTodosAsesores($_GET['fecha_inicio'] ?? null, $_GET['fecha_fin'] ?? null);
                if ($action === 'exportar_tmo')
                    $controller->exportarTMO($_GET['fecha_inicio'] ?? null, $_GET['fecha_fin'] ?? null);
                if ($action === 'exportar_reporte_personalizado')
                    $controller->exportarReportePersonalizado($_GET);
                if ($action === 'exportar_clientes')
                    $controller->exportarClientes($_GET['fecha_inicio'] ?? null, $_GET['fecha_fin'] ?? null, $_GET['estado_cliente'] ?? null);
                if ($action === 'exportar_cargas')
                    $controller->exportarCargas($_GET['estado_carga'] ?? null);
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
        case 'obtener_contratos_cliente':
        case 'gestionar_clientes':
        case 'buscar_cliente_por_cedula':
        case 'buscar_cliente_asesor':
        case 'get_cliente_para_gestion':
        case 'get_tareas_pendientes':
        case 'completar_tarea':
        case 'obtener_detalles_gestion':
        case 'agregar_informacion_cliente':
        case 'gestionar_productos_cliente':
        case 'registrar_break':
        case 'check_break_status':
        case 'verificar_contrasena_desbloqueo':
            if ($user_role === 'asesor') {
                $controller = new AsesorController($pdo);
                if ($action === 'mis_clientes')
                    $controller->misClientes();
                if ($action === 'gestionar_cliente' && isset($_GET['id']))
                    $controller->gestionarCliente($_GET['id']);
                if ($action === 'guardar_tipificacion')
                    $controller->guardarTipificacion();
                if ($action === 'guardar_cliente_nuevo')
                    $controller->guardarClienteNuevo();
                if ($action === 'obtener_siguiente_cliente')
                    $controller->obtenerSiguienteCliente();
                if ($action === 'obtener_historial_cliente' && isset($_GET['id']))
                    $controller->obtenerHistorialCliente();
                if ($action === 'obtener_datos_cliente')
                    $controller->obtenerDatosCliente();
                if ($action === 'obtener_contratos_cliente' && isset($_GET['id']))
                    $controller->obtenerContratosCliente();
                if ($action === 'gestionar_clientes')
                    $controller->gestionarClientes();
                if ($action === 'buscar_cliente_por_cedula')
                    $controller->buscarClientePorCedula();
                if ($action === 'buscar_cliente_asesor')
                    $controller->buscarClienteAsesor();
                if ($action === 'get_cliente_para_gestion')
                    $controller->getClienteParaGestion();
                if ($action === 'get_tareas_pendientes')
                    $controller->getTareasPendientes();
                if ($action === 'completar_tarea')
                    $controller->completarTarea();
                if ($action === 'obtener_detalles_gestion')
                    $controller->obtenerDetallesGestion();
                if ($action === 'agregar_informacion_cliente')
                    $controller->agregarInformacionCliente();
                if ($action === 'gestionar_productos_cliente')
                    $controller->gestionarProductosCliente();
                if ($action === 'registrar_break')
                    $controller->registrarBreak();
                if ($action === 'check_break_status')
                    $controller->checkBreakStatus();
                if ($action === 'verificar_contrasena_desbloqueo')
                    $controller->verificarContrasenaDesbloqueo();
            } else {
                header('Location: index.php?action=login');
                exit;
            }
            break;
        case 'obtener_break_activo':
            if ($user_role === 'asesor') {
                $controller = new AsesorController($pdo);
                $controller->obtenerBreakActivo();
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
                if ($action === 'gestionar_productos')
                    $controller->gestionarProductos();
                if ($action === 'crear_producto')
                    $controller->crearProducto();
                if ($action === 'registrar_gestion_producto')
                    $controller->registrarGestionProducto();
                if ($action === 'obtener_historial_producto')
                    $controller->obtenerHistorialProducto();
                if ($action === 'obtener_productos_pendientes')
                    $controller->obtenerProductosPendientes();
                if ($action === 'declinar_todos_productos')
                    $controller->declinarTodosProductos();
                if ($action === 'obtener_estadisticas_productos')
                    $controller->obtenerEstadisticasProductos();
            } else {
                header('Location: index.php?action=login');
                exit;
            }
            break;

        // Acción para obtener datos de teléfono (disponible para todos los roles)
        case 'get_telefono_data':
            try {
                // UsuarioModel ya está cargado
                $usuarioModel = new UsuarioModel($pdo);
                $datosTelefono = $usuarioModel->getDatosTelefono($session_manager->getUserId());
                $tieneTelefono = $usuarioModel->tieneTelefonoConfigurado($session_manager->getUserId());

                echo json_encode([
                    'success' => true,
                    'extension' => $datosTelefono['extension_telefono'] ?? '',
                    'clave' => $datosTelefono['clave_webrtc'] ?? '',
                    'tiene_telefono' => $tieneTelefono
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } catch (Exception $e) {
                logError("Error en get_telefono_data", ['exception' => $e->getMessage()]);
                echo json_encode([
                    'success' => false,
                    'error' => 'Error obteniendo datos de teléfono: ' . $e->getMessage()
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            exit;

        // Acción para buscar clientes (disponible para asesores)
        case 'buscar_cliente':
            try {
                if (!$session_manager->isLoggedIn() || $user_role !== 'asesor') {
                    throw new Exception('Acceso no autorizado');
                }

                $input = json_decode(file_get_contents('php://input'), true);

                if (!$input || !isset($input['tipo']) || !isset($input['termino'])) {
                    throw new Exception('Datos de búsqueda incompletos');
                }

                $tipo = $input['tipo'];
                $termino = trim($input['termino']);

                if (empty($termino)) {
                    throw new Exception('El término de búsqueda no puede estar vacío');
                }

                // ClienteModel ya está cargado
                $clienteModel = new ClienteModel($pdo);

                $clientes = [];
                if ($tipo === 'telefono') {
                    $clientes = $clienteModel->buscarPorTelefono($termino);
                } elseif ($tipo === 'cedula') {
                    $clientes = $clienteModel->buscarPorCedula($termino);
                } else {
                    throw new Exception('Tipo de búsqueda no válido');
                }

                echo json_encode([
                    'success' => true,
                    'clientes' => $clientes
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            } catch (Exception $e) {
                logError("Error en buscar_cliente", ['exception' => $e->getMessage()]);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            exit;


        default:
            // Si la acción no coincide, redirigir al dashboard (o login si no está logueado)
            if ($isApiAction) {
                sendJsonError('Acción no válida: ' . htmlspecialchars($action), 'INVALID_ACTION', 404);
            } else {
                // $session_manager ya está definido arriba
                if ($session_manager->isLoggedIn()) {
                    header('Location: index.php?action=dashboard');
                } else {
                    header('Location: index.php?action=login');
                }
                exit;
            }
    }

} catch (PDOException $e) {
    // Error de base de datos
    logError("Error PDO en index.php", [
        'action' => $action,
        'exception' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString()
    ]);

    if ($isApiAction) {
        sendJsonError('Error de base de datos. Por favor, intenta nuevamente.', 'DB_ERROR', 500);
    } else {
        // Para acciones no-API, mostrar error genérico
        error_log("Error PDO en acción no-API: " . $e->getMessage());
        header('Location: index.php?action=dashboard&error=db_error');
        exit;
    }

} catch (InvalidArgumentException $e) {
    // Error de validación
    logError("Error de validación en index.php", [
        'action' => $action,
        'exception' => $e->getMessage()
    ]);

    if ($isApiAction) {
        sendJsonError($e->getMessage(), 'VALIDATION_ERROR', 400);
    } else {
        header('Location: index.php?action=dashboard&error=validation_error');
        exit;
    }

} catch (Exception $e) {
    // Error general
    logError("Error general en index.php", [
        'action' => $action,
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    if ($isApiAction) {
        sendJsonError('Error inesperado. Por favor, contacta al administrador.', 'GENERAL_ERROR', 500);
    } else {
        // Para acciones no-API, redirigir con error
        error_log("Error en acción no-API: " . $e->getMessage());
        header('Location: index.php?action=dashboard&error=general_error');
        exit;
    }

} catch (Throwable $e) {
    // Error fatal (incluye errores de PHP 7+)
    logError("Error fatal en index.php", [
        'action' => $action,
        'exception' => $e->getMessage(),
        'type' => get_class($e),
        'trace' => $e->getTraceAsString()
    ]);

    if ($isApiAction) {
        sendJsonError('Error crítico del sistema. Por favor, contacta al administrador.', 'FATAL_ERROR', 500);
    } else {
        // Para acciones no-API, mostrar error fatal
        error_log("Error fatal en acción no-API: " . $e->getMessage());
        die("Error crítico del sistema. Por favor, contacta al administrador.");
    }
}

// MEJORA: Limpiar output buffer al final para acciones no-API
if (!$isApiAction && ob_get_level() > 0) {
    ob_end_flush();
}
?>