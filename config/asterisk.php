<?php
/**
 * Configuración de Asterisk/Issabel para WebRTC Softphone
 * 
 * Copiado desde APEX6/config/asterisk.php para reutilizar la misma
 * configuración de servidor SIP en el proyecto incomercio2.
 */

// =====================================================================
// CONFIGURACIÓN DEL SERVIDOR ASTERISK/ISSABEL
// =====================================================================

// URL del servidor WebSocket de Asterisk
// Usa 'ws://' si NO tienes SSL configurado
// Usa 'wss://' si tienes SSL/certificado configurado
// IP LOCAL (comentada para referencia): 192.168.65.190
// define('ASTERISK_WSS_SERVER', 'wss://192.168.65.190:8089/ws');
// IP PÚBLICA (activa): 190.60.96.10
// 5160 puerto alternativo para evitar bloqueos ISP
define('ASTERISK_WSS_SERVER', 'wss://190.60.96.10:8089/ws');

// Dominio SIP (debe coincidir con el 'realm' en pjsip.conf)
// Dominio del servidor PBX
// IP LOCAL (comentada para referencia): 192.168.65.190
// define('ASTERISK_SIP_DOMAIN', '192.168.65.190');
// IP PÚBLICA (activa): 190.60.96.10
define('ASTERISK_SIP_DOMAIN', '190.60.96.10');

// Puerto WSS de Asterisk (por defecto 8089)
define('ASTERISK_WSS_PORT', 8089);

// Servidor STUN para NAT Traversal
// Ayuda a clientes detrás de NAT a descubrir su IP pública
// Puedes usar servidores públicos de Google o configurar el tuyo
define('ASTERISK_STUN_SERVER', 'stun:stun.l.google.com:19302');

// =====================================================================
// CONFIGURACIÓN DE SERVIDORES TURN
// =====================================================================

// ¿Usar servidor TURN? (true/false)
// IMPORTANTE: Un servidor TURN es ALTAMENTE RECOMENDADO para garantizar audio bidireccional
// cuando hay firewalls estrictos o NATs simétricos
// PARA SERVIDOR LOCAL (sin NAT): Desactivar TURN y usar solo STUN
define('ASTERISK_USE_TURN', false);

// Configuración del servidor TURN
// OPCIÓN 1: Servidor TURN propio (RECOMENDADO para producción)
// Si tienes un servidor TURN propio, configura aquí:
define('ASTERISK_TURN_SERVER', ''); // Ejemplo: 'turn:192.168.65.190:3478' o 'turn:tu-dominio.com:3478'
define('ASTERISK_TURN_USERNAME', ''); // Usuario para autenticación TURN
define('ASTERISK_TURN_CREDENTIAL', ''); // Contraseña para autenticación TURN

// OPCIÓN 2: Servidor TURN público temporal (solo para pruebas rápidas)
// Metered.ca ofrece un servidor TURN público gratuito para pruebas
// IMPORTANTE: Este servidor es solo para pruebas, NO para producción
// Puede tener limitaciones de ancho de banda y latencia
// Para entorno local NO usamos TURN. Mantener en false.
define('ASTERISK_USE_PUBLIC_TURN', false);
// Si ASTERISK_USE_PUBLIC_TURN = true, se usaría el servidor de Metered.ca automáticamente

// Puerto RTP esperado de Asterisk dentro del rango 10000-20000
// Usado para diagnósticos y advertencias en el cliente WebRTC
define('ASTERISK_PREFERRED_RTP_PORT', 10000);

// Servidores ICE (STUN/TURN) para WebRTC
// Estos servidores son esenciales para que el audio se transporte correctamente
// cuando los clientes están detrás de NAT/firewalls
// Formato: array de arrays con 'urls', opcionalmente 'username' y 'credential' para TURN
// IMPORTANTE: Debe ser un array PHP válido, NO una cadena vacía
//
// NOTA: define() no puede definir arrays como constantes en PHP (en ninguna versión),
// por lo que usamos una función para obtener los servidores ICE
function getAsteriskIceServers() {
    // CRÍTICO: Configurar STUN servers para resolver problemas RTP
    // Sin STUN/TURN, WebRTC no puede establecer conexiones RTP
    return [
        ['urls' => 'stun:stun.l.google.com:19302'],
        ['urls' => 'stun:stun1.l.google.com:19302'],
        ['urls' => 'stun:stun2.l.google.com:19302'],
        ['urls' => 'stun:stun3.l.google.com:19302'],
        ['urls' => 'stun:stun4.l.google.com:19302']
    ];
}

// NOTA: No podemos usar define() para arrays en PHP
// La función getAsteriskIceServers() debe usarse directamente

// Modo de depuración (true = muestra logs en consola del navegador)
define('ASTERISK_DEBUG_MODE', true);

// =====================================================================
// FUNCIÓN PARA OBTENER LA CONFIGURACIÓN
// =====================================================================

/**
 * Retorna la configuración de WebRTC como array
 * @return array Configuración del softphone
 */
function getWebRTCConfig() {
    // Obtener servidores ICE (compatible con todas las versiones de PHP)
    $iceServers = getAsteriskIceServers();
    
    // Validar que sea un array y no esté vacío
    /* COMENTADO PARA RED LOCAL: No queremos fallback a Google
    if (!is_array($iceServers) || empty($iceServers)) {
        // Fallback: usar servidores STUN públicos por defecto
        $iceServers = [
            ['urls' => 'stun:stun.l.google.com:19302'],
            ['urls' => 'stun:stun1.l.google.com:19302']
        ];
    }
    */
    
    return [
        'wss_server'        => ASTERISK_WSS_SERVER,
        'sip_domain'        => ASTERISK_SIP_DOMAIN,
        'wss_port'          => ASTERISK_WSS_PORT,
        // 'stun_server'    => ASTERISK_STUN_SERVER,
        'iceServers'        => $iceServers, // Configuración de servidores ICE (array válido)
        'preferred_rtp_port'=> defined('ASTERISK_PREFERRED_RTP_PORT') ? ASTERISK_PREFERRED_RTP_PORT : 2000,
        'debug_mode'        => ASTERISK_DEBUG_MODE,
    ];
}


