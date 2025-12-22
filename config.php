<?php
/**
 * Configuración del Sistema de Gestión de Ventas - INCOMERCIO
 * Configuración completa para desarrollo y producción
 */

// Incluir el gestor de sesiones
require_once 'includes/session_manager.php';

// Configurar sesión única para este CRM
// Definir constantes de sesión primero
define('CRM_NAME', 'INCOMERCIO1');
define('SESSION_NAME', 'INCOMERCIO1_SID');

// Configurar sesión única para este CRM
// NOTA: Para el primer proyecto usa: getSessionManager('INCOMERCIO', 'INCOMERCIO_SID')
// Para el segundo proyecto usa: getSessionManager('INCOMERCIO1', 'INCOMERCIO1_SID')
$session_manager = getSessionManager(CRM_NAME, SESSION_NAME);

// Configuración de zona horaria
date_default_timezone_set('America/Bogota');

// Constantes del proyecto
define('SITE_NAME', 'Sistema de Ventas');
define('SITE_VERSION', '2.2');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['xlsx', 'xls', 'csv']);

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'yeimy');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/error.log');
?>
