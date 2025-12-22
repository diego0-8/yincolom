<?php
/**
 * Controlador Base
 * Contiene funcionalidades comunes para todos los controladores
 */

// Incluir todos los modelos necesarios
require_once 'models/UsuarioModel.php';
require_once 'models/ClienteModel.php';
require_once 'models/GestionModel.php';
require_once 'models/TareaModel.php';
require_once 'models/CargaExcelModel.php';
require_once 'models/ObligacionModel.php';

class BaseController {
    protected $pdo;
    protected $usuarioModel;
    protected $clienteModel;
    protected $gestionModel;
    protected $tareaModel;
    protected $cargaExcelModel;
    protected $obligacionModel;
    protected $sessionManager;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->clienteModel = new ClienteModel($pdo);
        $this->gestionModel = new GestionModel($pdo);
        $this->tareaModel = new TareaModel($pdo);
        $this->cargaExcelModel = new CargaExcelModel($pdo);
        $this->obligacionModel = new ObligacionModel($pdo);
        $this->sessionManager = getSessionManager();
    }

    /**
     * Verifica si el usuario está autenticado
     */
    protected function verificarAutenticacion() {
        if (!$this->sessionManager->isLoggedIn()) {
            header('Location: index.php?action=login');
            exit;
        }
    }

    /**
     * Verifica si el usuario tiene el rol requerido
     */
    protected function verificarRol($rolRequerido) {
        $this->verificarAutenticacion();
        if ($this->sessionManager->getUserRole() !== $rolRequerido) {
            header('Location: index.php?action=dashboard');
            exit;
        }
    }

    /**
     * Renderiza una vista con datos
     */
    protected function renderizarVista($vista, $datos = []) {
        extract($datos);
        include "views/{$vista}.php";
    }

    /**
     * Redirige con mensaje de éxito
     */
    protected function redirigirConExito($url, $mensaje) {
        $this->sessionManager->set('success_message', $mensaje);
        header("Location: $url");
        exit;
    }

    /**
     * Redirige con mensaje de error
     */
    protected function redirigirConError($url, $mensaje) {
        $this->sessionManager->set('error_message', $mensaje);
        header("Location: $url");
        exit;
    }

    /**
     * Obtiene mensajes de sesión y los limpia
     */
    protected function obtenerMensajes() {
        $mensajes = [
            'success' => $this->sessionManager->get('success_message'),
            'error' => $this->sessionManager->get('error_message')
        ];
        
        $this->sessionManager->remove('success_message');
        $this->sessionManager->remove('error_message');
        return $mensajes;
    }

    /**
     * Valida datos requeridos
     */
    protected function validarDatosRequeridos($datos, $camposRequeridos) {
        $errores = [];
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                $errores[] = "El campo {$campo} es requerido";
            }
        }
        return $errores;
    }

    /**
     * Sanitiza datos de entrada
     */
    protected function sanitizarDatos($datos) {
        $sanitizados = [];
        foreach ($datos as $clave => $valor) {
            if (is_string($valor)) {
                $sanitizados[$clave] = htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitizados[$clave] = $valor;
            }
        }
        return $sanitizados;
    }

    /**
     * Obtiene y sanitiza datos de $_GET
     */
    protected function getGet($key, $default = null) {
        if (!isset($_GET[$key])) {
            return $default;
        }
        return htmlspecialchars(trim($_GET[$key]), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Obtiene y sanitiza datos de $_POST
     */
    protected function getPost($key, $default = null) {
        if (!isset($_POST[$key])) {
            return $default;
        }
        return htmlspecialchars(trim($_POST[$key]), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida que un ID sea numérico y positivo
     */
    protected function validarId($id, $nombreCampo = 'ID') {
        if (!is_numeric($id) || $id <= 0 || !is_int($id + 0)) {
            throw new Exception("El {$nombreCampo} debe ser un número entero positivo válido");
        }
        return (int)$id;
    }

    /**
     * Valida que una fecha tenga el formato correcto
     */
    protected function validarFecha($fecha, $nombreCampo = 'fecha') {
        if (empty($fecha)) {
            return null;
        }
        
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
            throw new Exception("El formato de {$nombreCampo} debe ser YYYY-MM-DD");
        }
        
        return $fecha;
    }

    /**
     * Limpia todos los output buffers
     */
    protected function limpiarOutputBuffers() {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * Configura headers para respuesta JSON
     */
    protected function configurarHeadersJSON() {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
            header('X-Content-Type-Options: nosniff');
        }
    }

    /**
     * Envía una respuesta JSON de éxito
     */
    protected function enviarJSONExito($datos = [], $mensaje = 'Operación exitosa') {
        $this->limpiarOutputBuffers();
        $this->configurarHeadersJSON();
        
        $respuesta = [
            'success' => true,
            'message' => $mensaje
        ];
        
        if (!empty($datos)) {
            $respuesta = array_merge($respuesta, $datos);
        }
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Envía una respuesta JSON de error
     */
    protected function enviarJSONError($mensaje = 'Error en la operación', $codigoError = 'GENERAL_ERROR', $httpCode = 400) {
        $this->limpiarOutputBuffers();
        
        if (!headers_sent()) {
            http_response_code($httpCode);
            $this->configurarHeadersJSON();
        }
        
        $respuesta = [
            'success' => false,
            'message' => $mensaje,
            'error_code' => $codigoError
        ];
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
?>
