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
}
?>
