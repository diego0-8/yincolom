<?php 
require_once 'BaseController.php';

class AdminController extends BaseController {
    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    public function login() {
        $page_title = "Login";
        $error = '';
        $success = '';
        
        // Obtener el gestor de sesiones
        $session_manager = getSessionManager();
        
        // Si ya está logueado, redirigir al dashboard
        if ($session_manager->isLoggedIn()) {
            header('Location: index.php?action=dashboard');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim($_POST['usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');
            
            // Validar que los campos no estén vacíos
            if (empty($usuario) || empty($contrasena)) {
                $error = "Por favor, completa todos los campos.";
            } else {
                // Intentar autenticar al usuario
                $user = $this->usuarioModel->authenticateUser($usuario, $contrasena);
                
                if ($user) {
                    // Usuario autenticado correctamente
                    $session_manager->login($user['id'], $user['rol'], $user['nombre_completo']);

                    // Sincronizar datos de softphone y nombre extendido en la sesión global,
                    // que es lo que usa la vista gestionar_cliente.php
                    $_SESSION['usuario_nombre']       = $user['nombre_completo'] ?? $usuario;
                    $_SESSION['usuario_extension']    = $user['extension_telefono'] ?? '';
                    $_SESSION['usuario_sip_password'] = $user['clave_webrtc'] ?? '';
                    
                    // Log de acceso exitoso
                    error_log("Login exitoso - Usuario: {$usuario}, Rol: {$user['rol']}, ID: {$user['id']}, Sesión: " . $session_manager->getSessionName());
                    
                    // Redirigir al dashboard correspondiente
                    header('Location: index.php?action=dashboard');
                    exit;
                } else {
                    // Verificar si el usuario existe pero la contraseña es incorrecta
                    $userExists = $this->usuarioModel->checkUserExists($usuario);
                    
                    if ($userExists) {
                        if ($userExists['estado'] === 'Inactivo') {
                            $error = "Tu cuenta está inactiva. Contacta al administrador.";
                        } else {
                            $error = "Contraseña incorrecta. Verifica tu contraseña.";
                        }
                    } else {
                        $error = "Usuario no encontrado. Verifica tu nombre de usuario.";
                    }
                    
                    // Log de intento fallido
                    error_log("Login fallido - Usuario: {$usuario}, IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida'));
                }
            }
        }
        
        require 'views/login_form.php';
    }

    public function logout() {
        $session_manager = getSessionManager();
        $session_manager->logout();
        header('Location: index.php?action=login');
        exit;
    }

    public function dashboard() {
        $page_title = "Dashboard Administrador";
        require 'views/admin_dashboard.php';
    }

    public function listUsuarios() {
        $page_title = "Lista de Usuarios";
        
        // Obtener filtros
        $search = $_GET['search'] ?? '';
        $rol_filter = $_GET['rol_filter'] ?? '';
        $estado_filter = $_GET['estado_filter'] ?? '';
        
        // Obtener usuarios con filtros
        $usuarios = $this->usuarioModel->getUsuariosWithFilters($search, $rol_filter, $estado_filter);
        
        require 'views/usuario_list.php';
    }

    public function createUsuario() {
        $page_title = "Crear Nuevo Usuario";
        $usuario = null;
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar datos
                if (empty($_POST['nombre']) || empty($_POST['cedula']) || empty($_POST['usuario']) || empty($_POST['contrasena']) || empty($_POST['rol'])) {
                    $error = "Todos los campos obligatorios deben estar completos.";
                } else {
                    $result = $this->usuarioModel->createUsuario($_POST);
                    if ($result) {
                        $success = "Usuario creado exitosamente.";
                        // Limpiar el formulario
                        $_POST = [];
                    } else {
                        $error = "Error al crear el usuario. Verifica que el usuario no exista ya.";
                    }
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
        
        require 'views/usuario_form.php';
    }

    public function editUsuario($id) {
        $page_title = "Editar Usuario";
        $usuario = $this->usuarioModel->getUsuarioById($id);
        $error = '';
        $success = '';
        
        if (!$usuario) {
            header('Location: index.php?action=list_usuarios');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar datos obligatorios
                if (empty($_POST['nombre']) || empty($_POST['cedula']) || empty($_POST['usuario']) || empty($_POST['rol'])) {
                    $error = "Todos los campos obligatorios deben estar completos.";
                } else {
                    // Validar contraseña si se proporciona
                    if (!empty($_POST['contrasena'])) {
                        if (empty($_POST['confirmar_contrasena'])) {
                            $error = "Debe confirmar la nueva contraseña.";
                        } elseif ($_POST['contrasena'] !== $_POST['confirmar_contrasena']) {
                            $error = "Las contraseñas no coinciden.";
                        } elseif (strlen($_POST['contrasena']) < 6) {
                            $error = "La contraseña debe tener al menos 6 caracteres.";
                        }
                    }
                    
                    // Si no hay errores, proceder con la actualización
                    if (empty($error)) {
                        $result = $this->usuarioModel->updateUsuario($id, $_POST);
                        if ($result) {
                            $success = "Usuario actualizado exitosamente.";
                            // Actualizar datos del usuario en la variable
                            $usuario = array_merge($usuario, $_POST);
                        } else {
                            $error = "Error al actualizar el usuario.";
                        }
                    }
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
        
        require 'views/usuario_form.php';
    }

    public function toggleEstadoUsuario($id) {
        $this->usuarioModel->toggleEstadoUsuario($id);
        header('Location: index.php?action=list_usuarios');
        exit;
    }
    
    public function verActividades() {
        $page_title = "Actividades del Sistema";
        
        // Obtener estadísticas de actividades
        $stats = [
            'total_usuarios' => count($this->usuarioModel->getAllUsuarios()),
            'coordinadores' => count($this->usuarioModel->getUsuariosByRol('coordinador')),
            'asesores' => count($this->usuarioModel->getUsuariosByRol('asesor')),
            'usuarios_activos' => count($this->usuarioModel->getUsuariosByRol('administrador')) + count($this->usuarioModel->getUsuariosByRol('coordinador')) + count($this->usuarioModel->getUsuariosByRol('asesor'))
        ];
        
        require 'views/admin_actividades.php';
    }
    
    public function asignarPersonal() {
        $page_title = "Asignación de Personal";
        
        // Prevenir cache del navegador para esta página
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        // Obtener coordinadores y asesores DISPONIBLES (no asignados)
        $coordinadores = $this->usuarioModel->getUsuariosByRol('coordinador');
        $asesores = $this->usuarioModel->getAsesoresDisponibles(); // Solo asesores NO asignados
        
        // Obtener asesores asignados para mostrar en la sección correspondiente
        $asesoresAsignados = [];
        foreach ($coordinadores as $coordinador) {
            if ($coordinador['estado'] === 'Activo') {
                $asesoresDelCoordinador = $this->usuarioModel->getAsesoresByCoordinador($coordinador['id']);
                foreach ($asesoresDelCoordinador as $asesor) {
                    $asesor['coordinador_nombre'] = $coordinador['nombre_completo'];
                    $asesor['coordinador_id'] = $coordinador['id'];
                    $asesoresAsignados[] = $asesor;
                }
            }
        }
        
        // Obtener mensajes de éxito o error
        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';
        
        require 'views/admin_asignar_personal.php';
    }

    /**
     * Ver la gestión y métricas de un coordinador específico
     */
    public function verGestionCoordinador($coordinadorId) {
        $page_title = "Gestión del Coordinador";
        
        // Verificar que el usuario sea administrador
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener datos del coordinador
        $coordinador = $this->usuarioModel->getUsuarioById($coordinadorId);
        
        if (!$coordinador || $coordinador['rol'] !== 'coordinador') {
            header('Location: index.php?action=asignar_personal&error=coordinador_no_encontrado');
            exit;
        }
        
        // Obtener asesores asignados al coordinador
        $asesoresAsignados = $this->usuarioModel->getAsesoresByCoordinador($coordinadorId);
        
        // Obtener métricas básicas
        $metricas = [
            'total_asesores_asignados' => count($asesoresAsignados),
            'asesores_activos' => count(array_filter($asesoresAsignados, function($asesor) {
                return $asesor['estado'] === 'Activo';
            })),
            'coordinador_estado' => $coordinador['estado']
        ];
        
        require 'views/admin_gestion_coordinador.php';
    }

    /**
     * Ver la gestión y métricas de un asesor específico
     */
    public function verGestionAsesor($asesorId) {
        $page_title = "Gestión del Asesor";
        
        // Verificar que el usuario sea administrador
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener datos del asesor
        $asesor = $this->usuarioModel->getUsuarioById($asesorId);
        
        if (!$asesor || $asesor['rol'] !== 'asesor') {
            header('Location: index.php?action=asignar_personal&error=asesor_no_encontrado');
            exit;
        }
        
        // Obtener coordinador asignado al asesor
        $coordinadorAsignado = null;
        $coordinadores = $this->usuarioModel->getUsuariosByRol('coordinador');
        
        foreach ($coordinadores as $coordinador) {
            if ($this->usuarioModel->isAsesorAsignadoACoordinador($asesorId, $coordinador['id'])) {
                $coordinadorAsignado = $coordinador;
                break;
            }
        }
        
        // Obtener métricas básicas
        $metricas = [
            'asesor_estado' => $asesor['estado'],
            'coordinador_asignado' => $coordinadorAsignado ? $coordinadorAsignado['nombre_completo'] : 'Sin asignar',
            'fecha_registro' => $asesor['fecha_registro'] ?? 'No disponible'
        ];
        
        require 'views/admin_gestion_asesor.php';
    }

    /**
     * Procesar la asignación de un asesor a un coordinador
     */
    public function asignarAsesor() {
        // Verificar que el usuario sea administrador
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $coordinadorId = $_POST['coordinador_id'] ?? null;
            $asesorId = $_POST['asesor_id'] ?? null;
            
            // Validar datos
            if (empty($coordinadorId) || empty($asesorId)) {
                header('Location: index.php?action=asignar_personal&error=datos_incompletos');
                exit;
            }
            
            // Verificar que existan los usuarios
            $coordinador = $this->usuarioModel->getUsuarioById($coordinadorId);
            $asesor = $this->usuarioModel->getUsuarioById($asesorId);
            
            if (!$coordinador || $coordinador['rol'] !== 'coordinador') {
                header('Location: index.php?action=asignar_personal&error=coordinador_invalido');
                exit;
            }
            
            if (!$asesor || $asesor['rol'] !== 'asesor') {
                header('Location: index.php?action=asignar_personal&error=asesor_invalido');
                exit;
            }
            
            // Verificar que ambos usuarios estén activos
            if ($coordinador['estado'] !== 'Activo' || $asesor['estado'] !== 'Activo') {
                header('Location: index.php?action=asignar_personal&error=usuarios_inactivos');
                exit;
            }
            
            try {
                // Realizar la asignación
                $result = $this->usuarioModel->asignarAsesorACoordinador($asesorId, $coordinadorId);
                
                if ($result) {
                    // Log de la asignación
                    error_log("Asignación exitosa - Asesor ID: {$asesorId} asignado a Coordinador ID: {$coordinadorId} por Admin ID: {$_SESSION['user_id']}");
                    
                    // Limpiar cualquier cache de sesión
                    if (isset($_SESSION['asesores_cache'])) {
                        unset($_SESSION['asesores_cache']);
                    }
                    
                    header('Location: index.php?action=asignar_personal&success=asignacion_exitosa&t=' . time());
                } else {
                    header('Location: index.php?action=asignar_personal&error=error_asignacion&t=' . time());
                }
            } catch (Exception $e) {
                error_log("Error en asignación de asesor: " . $e->getMessage());
                header('Location: index.php?action=asignar_personal&error=error_sistema');
            }
        } else {
            // Si no es POST, redirigir
            header('Location: index.php?action=asignar_personal');
        }
        exit;
    }

    /**
     * Liberar un asesor de un coordinador
     */
    public function liberarAsesor($asesorId, $coordinadorId) {
        // Verificar que el usuario sea administrador
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        try {
            $result = $this->usuarioModel->liberarAsesorDeCoordinador($asesorId, $coordinadorId);
            
            if ($result) {
                // Log de la liberación
                error_log("Liberación exitosa - Asesor ID: {$asesorId} liberado del Coordinador ID: {$coordinadorId} por Admin ID: {$_SESSION['user_id']}");
                
                // Limpiar cualquier cache de sesión
                if (isset($_SESSION['asesores_cache'])) {
                    unset($_SESSION['asesores_cache']);
                }
                
                header('Location: index.php?action=asignar_personal&success=liberacion_exitosa&t=' . time());
            } else {
                header('Location: index.php?action=asignar_personal&error=error_liberacion&t=' . time());
            }
        } catch (Exception $e) {
            error_log("Error en liberación de asesor: " . $e->getMessage());
            header('Location: index.php?action=asignar_personal&error=error_sistema');
        }
        exit;
    }

    public function cargarHash() {
        $this->verificarAdministrador();

        $page_title = "Cargar Hash de Gestión";
        $error = '';
        $success = '';
        $cargas = $this->cargaExcelModel->getAllCargas();
        $previewData = $this->sessionManager->get('cargar_hash_preview');
        $importResult = $this->sessionManager->get('cargar_hash_import_result');
        // Diagnóstico real (misma conexión PDO) para evitar falsos negativos por BD equivocada o error de permisos.
        $diagnosticoHistorialGestion = $this->diagnosticarHistorialGestion();
        $fechaOrigenDisponible = (bool)($diagnosticoHistorialGestion['columna_fecha_gestion_origen_existe'] ?? false);
        $selectedCargaId = (int)($_POST['carga_id'] ?? ($previewData['carga_id'] ?? 0));

        if ($importResult) {
            $this->sessionManager->remove('cargar_hash_import_result');
            $success = $importResult['mensaje'] ?? '';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion_cargar_hash'] ?? '';

            try {
                if ($accion === 'limpiar_preview') {
                    $this->sessionManager->remove('cargar_hash_preview');
                    header('Location: index.php?action=cargar_hash');
                    exit;
                }

                if ($accion === 'previsualizar') {
                    $selectedCargaId = $this->validarId($_POST['carga_id'] ?? null, 'base de clientes');
                    $previewData = $this->generarPreviewCargaHash($selectedCargaId, $_FILES['archivo_csv'] ?? null);
                    $this->sessionManager->set('cargar_hash_preview', $previewData);
                    header('Location: index.php?action=cargar_hash&preview=1');
                    exit;
                }

                if ($accion === 'importar') {
                    $selectedCargaId = $this->validarId($_POST['carga_id'] ?? null, 'base de clientes');
                    $importResult = $this->importarCargaHashDesdePreview($selectedCargaId);
                    $this->sessionManager->remove('cargar_hash_preview');
                    $this->sessionManager->set('cargar_hash_import_result', $importResult);
                    header('Location: index.php?action=cargar_hash&importado=1');
                    exit;
                }

                throw new Exception("Acción de carga no válida.");
            } catch (Exception $e) {
                $error = $e->getMessage();
                $previewData = $this->sessionManager->get('cargar_hash_preview');
            }
        }

        require 'views/admin_cargar_hash.php';
    }

    private function diagnosticarHistorialGestion() {
        $out = [
            'database' => null,
            'tabla_historial_gestion_existe' => null,
            'columna_fecha_gestion_origen_existe' => null,
            'error' => null,
        ];

        try {
            $out['database'] = $this->pdo->query("SELECT DATABASE()")->fetchColumn();

            // Evitar SHOW TABLES/SHOW COLUMNS con placeholders: en algunos MariaDB/PDO da error cerca de '?'
            $sqlTabla = "SELECT 1
                         FROM INFORMATION_SCHEMA.TABLES
                         WHERE TABLE_SCHEMA = DATABASE()
                           AND TABLE_NAME = ?
                         LIMIT 1";
            $stmtTabla = $this->pdo->prepare($sqlTabla);
            $stmtTabla->execute(['historial_gestion']);
            $out['tabla_historial_gestion_existe'] = $stmtTabla->fetchColumn() !== false;

            if ($out['tabla_historial_gestion_existe']) {
                $sqlCol = "SELECT 1
                           FROM INFORMATION_SCHEMA.COLUMNS
                           WHERE TABLE_SCHEMA = DATABASE()
                             AND TABLE_NAME = 'historial_gestion'
                             AND COLUMN_NAME = ?
                           LIMIT 1";
                $stmtCol = $this->pdo->prepare($sqlCol);
                $stmtCol->execute(['fecha_gestion_origen']);
                $out['columna_fecha_gestion_origen_existe'] = $stmtCol->fetchColumn() !== false;
            } else {
                $out['columna_fecha_gestion_origen_existe'] = false;
            }
        } catch (Exception $e) {
            $out['error'] = $e->getMessage();
            $out['tabla_historial_gestion_existe'] = false;
            $out['columna_fecha_gestion_origen_existe'] = false;
        }

        return $out;
    }

    private function verificarAdministrador() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header('Location: index.php?action=login');
            exit;
        }
    }

    private function generarPreviewCargaHash($cargaId, $archivo) {
        $carga = $this->cargaExcelModel->getCargaById($cargaId);
        if (!$carga) {
            throw new Exception("La base de clientes seleccionada no existe.");
        }

        if (!$archivo || !isset($archivo['tmp_name']) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new Exception("Debes seleccionar un archivo CSV válido para continuar.");
        }

        $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            throw new Exception("El archivo debe tener extensión .csv.");
        }

        $delimitador = $this->detectarDelimitadorCSV($archivo['tmp_name']);
        $handle = fopen($archivo['tmp_name'], 'r');
        if ($handle === false) {
            throw new Exception("No fue posible leer el archivo cargado.");
        }

        $encabezadosOriginales = fgetcsv($handle, 0, $delimitador);
        if ($encabezadosOriginales === false) {
            fclose($handle);
            throw new Exception("El archivo CSV no contiene encabezados válidos.");
        }

        $mapaColumnas = $this->crearMapaEncabezadosCSV($encabezadosOriginales);
        $this->validarEncabezadosCSV($mapaColumnas);

        $filasValidas = [];
        $filasInvalidas = [];
        $firmasDetectadas = [];
        $totalFilas = 0;
        $totalAdvertencias = 0;
        $numeroLinea = 1;

        while (($fila = fgetcsv($handle, 0, $delimitador)) !== false) {
            $numeroLinea++;
            if ($this->filaCSVVacia($fila)) {
                continue;
            }

            $totalFilas++;
            $filaAsociativa = $this->combinarFilaCSV($mapaColumnas, $fila);
            $analisis = $this->analizarFilaCargaHash($filaAsociativa, $carga, $numeroLinea);

            if ($analisis['valida']) {
                $firma = $this->generarFirmaFilaImportacion($analisis['gestion_data']);
                if (isset($firmasDetectadas[$firma])) {
                    $analisis['valida'] = false;
                    $analisis['errores'][] = 'Fila duplicada dentro del mismo archivo.';
                } else {
                    $firmasDetectadas[$firma] = true;
                }
            }

            $totalAdvertencias += count($analisis['advertencias']);

            if ($analisis['valida']) {
                $filasValidas[] = $analisis;
            } else {
                $filasInvalidas[] = $analisis;
            }
        }

        fclose($handle);

        if ($totalFilas === 0) {
            throw new Exception("El archivo no contiene filas de datos para procesar.");
        }

        return [
            'carga_id' => (int)$carga['id'],
            'carga_nombre' => $carga['nombre_cargue'],
            'archivo_nombre' => $archivo['name'],
            'delimitador' => $delimitador,
            'encabezados_detectados' => $encabezadosOriginales,
            'generado_en' => date('Y-m-d H:i:s'),
            'filas_validas' => $filasValidas,
            'filas_invalidas' => $filasInvalidas,
            'resumen' => [
                'total_filas' => $totalFilas,
                'validas' => count($filasValidas),
                'invalidas' => count($filasInvalidas),
                'advertencias' => $totalAdvertencias
            ]
        ];
    }

    private function importarCargaHashDesdePreview($cargaId) {
        $previewData = $this->sessionManager->get('cargar_hash_preview');
        if (!$previewData) {
            throw new Exception("No hay una previsualización activa para importar.");
        }

        if ((int)($previewData['carga_id'] ?? 0) !== (int)$cargaId) {
            throw new Exception("La base seleccionada no coincide con la previsualización generada.");
        }

        $filasValidas = $previewData['filas_validas'] ?? [];
        if (empty($filasValidas)) {
            throw new Exception("No hay filas válidas para importar.");
        }

        $this->pdo->beginTransaction();
        try {
            $gestionIds = [];
            foreach ($filasValidas as $fila) {
                $asignacionId = $this->clienteModel->ensureAsignacionClienteAsesor(
                    (int)$fila['asesor_id'],
                    (int)$fila['cliente_id']
                );

                if (!$asignacionId) {
                    throw new Exception("No fue posible crear o recuperar la asignación para la línea " . ($fila['linea'] ?? 'N/A') . ".");
                }

                $gestionData = $fila['gestion_data'];
                $gestionData['asignacion_id'] = $asignacionId;
                $gestionId = $this->gestionModel->crearGestion($gestionData);

                if (!empty($fila['canales_autorizados'])) {
                    $this->gestionModel->guardarCanalesAutorizados($gestionId, $fila['canales_autorizados']);
                }

                $gestionIds[] = $gestionId;
            }

            $this->pdo->commit();

            return [
                'mensaje' => "Se importaron " . count($gestionIds) . " gestiones en la base seleccionada.",
                'importadas' => count($gestionIds),
                'gestion_ids' => $gestionIds,
                'archivo_nombre' => $previewData['archivo_nombre'] ?? '',
                'carga_nombre' => $previewData['carga_nombre'] ?? '',
                'resumen_preview' => $previewData['resumen'] ?? []
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception("La importación fue cancelada: " . $e->getMessage());
        }
    }

    private function analizarFilaCargaHash($fila, $carga, $numeroLinea) {
        $errores = [];
        $advertencias = [];

        $cedula = preg_replace('/\D+/', '', $fila['cedula_cliente'] ?? '');
        $asesorNombre = trim((string)($fila['asesor'] ?? ''));
        $observaciones = trim((string)($fila['observaciones'] ?? ''));

        if ($cedula === '') {
            $errores[] = 'La cédula del cliente es obligatoria.';
        }

        if ($asesorNombre === '') {
            $errores[] = 'El nombre del asesor es obligatorio.';
        }

        if ($observaciones === '') {
            $errores[] = 'La observación/comentario no puede quedar vacía.';
        }

        $fechaGestionOrigen = $this->parsearFechaHoraCSV($fila['fecha_gestion'] ?? '');
        if ($fechaGestionOrigen === null) {
            $errores[] = 'La fecha de gestión no tiene un formato válido.';
        }

        $tipoGestion = $this->mapearTipificacion2Nivel($fila['tipificacion_2'] ?? '');
        if ($tipoGestion === null) {
            $errores[] = 'La tipificación de 2 nivel no es válida.';
        }

        $resultado = $this->mapearTipificacion3Nivel($fila['tipificacion_3'] ?? '');
        if ($resultado === null) {
            $errores[] = 'La tipificación de 3 nivel no es válida.';
        }

        $cliente = $cedula !== '' ? $this->clienteModel->getClienteByCedulaAndCarga($cedula, $carga['id']) : false;
        if (!$cliente) {
            $errores[] = 'La cédula no pertenece a la base seleccionada.';
        }

        $asesor = $asesorNombre !== '' ? $this->usuarioModel->findAsesorByNombre($asesorNombre) : false;
        if (!$asesor) {
            $errores[] = 'No se encontró un asesor activo que coincida con el nombre del archivo.';
        }

        $numeroObligacion = $this->limpiarValorTextoCSV($fila['obligacion_producto'] ?? '');
        if ($numeroObligacion === '' || strcasecmp($numeroObligacion, 'ninguna') === 0) {
            $numeroObligacion = null;
        }

        $obligacion = null;
        if ($numeroObligacion !== null && $cliente) {
            $obligacion = $this->obligacionModel->getObligacionByNumeroAndClienteId($numeroObligacion, (int)$cliente['id']);
            if (!$obligacion) {
                $advertencias[] = 'La obligación indicada no existe para el cliente en el sistema.';
            }
        }

        if ($resultado === '03' && !$obligacion) {
            $errores[] = 'La fila es un acuerdo de pago y requiere una obligación válida del cliente.';
        }

        $canalesAutorizados = $this->mapearCanalesAutorizados($fila['canales_autorizados'] ?? '');
        $duracionMinutos = $this->parsearDuracionMinutos($fila['duracion_gestion'] ?? '');
        $formaContacto = $this->mapearFormaContacto($fila['tipo_contacto'] ?? '');
        $valorTotal = $this->parsearNumeroCSV($fila['valor_total'] ?? '');

        if ($valorTotal === null && $obligacion && isset($obligacion['saldo_k_obligacion'])) {
            $valorTotal = (float)$obligacion['saldo_k_obligacion'];
        }

        $gestionData = [
            'cliente_id' => (int)($cliente['id'] ?? 0),
            'asesor_id' => (int)($asesor['id'] ?? 0),
            'tipo_gestion' => $tipoGestion,
            'resultado' => $resultado,
            'comentarios' => $observaciones,
            'duracion_llamada' => $duracionMinutos,
            'forma_contacto' => $formaContacto,
            'obligacion_id' => $obligacion['id'] ?? null,
            'producto_gestionado' => null,
            'monto_obligacion' => $valorTotal,
            'numero_obligacion' => $numeroObligacion,
            'no_cuotas' => $this->parsearEnteroCSV($fila['total_cuotas'] ?? ''),
            'fecha_pago' => $this->parsearFechaSoloCSV($fila['fecha_pago'] ?? ''),
            'valor_cuota' => $this->parsearNumeroCSV($fila['valor_cuota'] ?? ''),
            'numero_cuota' => $this->parsearEnteroCSV($fila['cuota_numero'] ?? ''),
            'valor_total' => $valorTotal,
            'valor_acuerdo' => $this->parsearNumeroCSV($fila['valor_acuerdo'] ?? ''),
            'fecha_gestion_origen' => $fechaGestionOrigen
        ];

        return [
            'valida' => empty($errores),
            'linea' => $numeroLinea,
            'cedula' => $cedula,
            'cliente_id' => (int)($cliente['id'] ?? 0),
            'cliente_nombre' => $cliente['nombre'] ?? '',
            'asesor_id' => (int)($asesor['id'] ?? 0),
            'asesor_nombre' => $asesor['nombre_completo'] ?? $asesorNombre,
            'errores' => $errores,
            'advertencias' => $advertencias,
            'canales_autorizados' => $canalesAutorizados,
            'fila_original' => $fila,
            'gestion_data' => $gestionData
        ];
    }

    private function detectarDelimitadorCSV($rutaArchivo) {
        $primeraLinea = '';
        $handle = fopen($rutaArchivo, 'r');
        if ($handle !== false) {
            $primeraLinea = fgets($handle) ?: '';
            fclose($handle);
        }

        $cantidadPuntoComa = substr_count($primeraLinea, ';');
        $cantidadComas = substr_count($primeraLinea, ',');

        return $cantidadPuntoComa >= $cantidadComas ? ';' : ',';
    }

    private function crearMapaEncabezadosCSV($encabezadosOriginales) {
        $aliases = [
            'fecha de gestion' => 'fecha_gestion',
            'asesor' => 'asesor',
            'cedula del cliente' => 'cedula_cliente',
            'tipo de contacto' => 'tipo_contacto',
            'tipificacion 2 nivel' => 'tipificacion_2',
            'tipificacion 3 nivel' => 'tipificacion_3',
            'obligacion producto a gestionar' => 'obligacion_producto',
            'observaciones' => 'observaciones',
            'canales autorizados' => 'canales_autorizados',
            'duracion de gestion' => 'duracion_gestion',
            'valor total producto' => 'valor_total',
            'valor del acuerdo acuerdo de pago' => 'valor_acuerdo',
            'total cuotas acuerdo de pago' => 'total_cuotas',
            'fecha de pago acuerdo de pago' => 'fecha_pago',
            'valor de cuota acuerdo de pago' => 'valor_cuota',
            'cuota numero de cuota' => 'cuota_numero'
        ];

        $mapa = [];
        foreach ($encabezadosOriginales as $indice => $encabezado) {
            $normalizado = $this->normalizarTextoPlano($this->limpiarBOM((string)$encabezado));
            if (isset($aliases[$normalizado])) {
                $mapa[$aliases[$normalizado]] = $indice;
            }
        }

        return $mapa;
    }

    private function validarEncabezadosCSV($mapaColumnas) {
        $requeridos = [
            'fecha_gestion',
            'asesor',
            'cedula_cliente',
            'tipo_contacto',
            'tipificacion_2',
            'tipificacion_3',
            'observaciones'
        ];

        $faltantes = [];
        foreach ($requeridos as $requerido) {
            if (!array_key_exists($requerido, $mapaColumnas)) {
                $faltantes[] = $requerido;
            }
        }

        if (!empty($faltantes)) {
            throw new Exception('Faltan columnas obligatorias en el CSV: ' . implode(', ', $faltantes) . '.');
        }
    }

    private function combinarFilaCSV($mapaColumnas, $fila) {
        $resultado = [];
        foreach ($mapaColumnas as $campo => $indice) {
            $resultado[$campo] = isset($fila[$indice]) ? trim((string)$fila[$indice]) : '';
        }
        return $resultado;
    }

    private function filaCSVVacia($fila) {
        foreach ($fila as $valor) {
            if (trim((string)$valor) !== '') {
                return false;
            }
        }
        return true;
    }

    private function generarFirmaFilaImportacion($gestionData) {
        $componentes = [
            $gestionData['cliente_id'] ?? '',
            $gestionData['asesor_id'] ?? '',
            $gestionData['fecha_gestion_origen'] ?? '',
            $gestionData['tipo_gestion'] ?? '',
            $gestionData['resultado'] ?? '',
            $gestionData['comentarios'] ?? '',
            $gestionData['numero_obligacion'] ?? ''
        ];

        return md5(implode('|', $componentes));
    }

    private function mapearTipificacion2Nivel($valor) {
        $valor = $this->normalizarTextoPlano($valor);
        $mapa = [
            'hacer llamada' => 'hacer_llamada',
            'recibir llamada' => 'recibir_llamada'
        ];

        return $mapa[$valor] ?? null;
    }

    private function mapearTipificacion3Nivel($valor) {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return null;
        }

        if (preg_match('/^([0-9]{2}(?:\.[0-9])?)/', $valor, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function mapearFormaContacto($valor) {
        $valor = $this->normalizarTextoPlano($valor);
        $mapa = [
            'llamada' => 'llamada',
            'whatsapp' => 'whatsapp',
            'sms' => 'sms',
            'correo' => 'correo',
            'correo electronico' => 'correo',
            'visita' => 'visita'
        ];

        return $mapa[$valor] ?? 'llamada';
    }

    private function mapearCanalesAutorizados($valor) {
        $valor = trim((string)$valor);
        if ($valor === '' || strcasecmp($valor, 'No especificados') === 0) {
            return [];
        }

        $mapa = [
            'llamada' => 'llamada',
            'correo electronico' => 'correo_electronico',
            'correo e' => 'correo_electronico',
            'sms' => 'sms',
            'correo fisico' => 'correo_fisico',
            'mensajeria por aplicaciones' => 'mensajeria_aplicaciones',
            'whatsapp' => 'mensajeria_aplicaciones'
        ];

        $resultado = [];
        foreach (preg_split('/[,|]+/', $valor) as $canal) {
            $normalizado = $this->normalizarTextoPlano($canal);
            if ($normalizado === '') {
                continue;
            }

            $resultado[] = $mapa[$normalizado] ?? str_replace(' ', '_', $normalizado);
        }

        return array_values(array_unique($resultado));
    }

    private function parsearFechaHoraCSV($valor) {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return null;
        }

        $formatos = ['d/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y G:i:s', 'd/m/Y G:i', 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d G:i:s', 'Y-m-d G:i'];
        foreach ($formatos as $formato) {
            $fecha = DateTime::createFromFormat($formato, $valor);
            if ($fecha instanceof DateTime) {
                return $fecha->format('Y-m-d H:i:s');
            }
        }

        return null;
    }

    private function parsearFechaSoloCSV($valor) {
        $valor = trim((string)$valor);
        if ($valor === '' || $valor === '0000-00-00') {
            return null;
        }

        $formatos = ['d/m/Y', 'Y-m-d'];
        foreach ($formatos as $formato) {
            $fecha = DateTime::createFromFormat($formato, $valor);
            if ($fecha instanceof DateTime) {
                return $fecha->format('Y-m-d');
            }
        }

        return null;
    }

    private function parsearDuracionMinutos($valor) {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $valor, $matches)) {
            $segundosTotales = ((int)$matches[1] * 3600) + ((int)$matches[2] * 60) + (int)$matches[3];
            return round($segundosTotales / 60, 2);
        }

        return $this->parsearNumeroCSV($valor);
    }

    private function parsearNumeroCSV($valor) {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return null;
        }

        $valor = str_replace(["\xc2\xa0", ' '], '', $valor);
        $valor = preg_replace('/[^0-9,.\-]/', '', $valor);

        if ($valor === '' || $valor === '-' || $valor === null) {
            return null;
        }

        if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
            $valor = str_replace(',', '', $valor);
        } elseif (strpos($valor, ',') !== false) {
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float)$valor : null;
    }

    private function parsearEnteroCSV($valor) {
        $numero = $this->parsearNumeroCSV($valor);
        if ($numero === null) {
            return null;
        }

        return (int)round($numero);
    }

    private function limpiarValorTextoCSV($valor) {
        return trim((string)$valor);
    }

    private function limpiarBOM($texto) {
        return preg_replace('/^\xEF\xBB\xBF/', '', $texto);
    }

    private function normalizarTextoPlano($texto) {
        $texto = trim((string)$texto);
        if ($texto === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            $texto = mb_strtolower($texto, 'UTF-8');
        } else {
            $texto = strtolower($texto);
        }

        if (function_exists('iconv')) {
            $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
            if ($convertido !== false) {
                $texto = $convertido;
            }
        }

        $texto = preg_replace('/[^a-z0-9]+/', ' ', $texto);
        return trim(preg_replace('/\s+/', ' ', $texto));
    }
}
?>
