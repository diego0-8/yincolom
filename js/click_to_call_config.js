/**
 * Configuración para Click to Call
 * Maneja problemas de CORS y configuración
 */

const ClickToCallConfig = {
    // URL base del sistema de teléfono
    PHONE_BASE_URL: 'https://estaqueue.udpsa.com/phone/phone.php',
    
    // Parámetros del sistema
    PBXCLOUD: 'onix.udpsa.com',
    
    // Configuración de la ventana
    WINDOW_FEATURES: 'width=400,height=600,scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no',
    
    // Intervalos de verificación
    CONNECTION_CHECK_INTERVAL: 3000, // 3 segundos
    
    // Timeouts
    CONNECTION_TIMEOUT: 10000, // 10 segundos
    
    // Configuración de notificaciones
    NOTIFICATION_DURATION: 3000, // 3 segundos
    
    // Manejo de CORS
    HANDLE_CORS: true,
    
    // Debug mode
    DEBUG: true,
    
    // Métodos para manejar CORS
    methods: {
        // Verificar si una ventana está cerrada de forma segura
        isWindowClosed: function(window) {
            try {
                return window.closed;
            } catch (error) {
                return true; // Asumir que está cerrada si hay error
            }
        },
        
        // Enviar mensaje de forma segura
        sendMessage: function(window, message) {
            try {
                window.postMessage(message, '*');
                return true;
            } catch (error) {
                console.log('⚠️ No se pudo enviar mensaje (CORS):', error.message);
                return false;
            }
        },
        
        // Verificar si una ventana es accesible
        isWindowAccessible: function(window) {
            try {
                window.document;
                return true;
            } catch (error) {
                return false;
            }
        },
        
        // Crear URL de llamada
        createCallUrl: function(extension, clave, numero = '') {
            const params = new URLSearchParams({
                PBXCLOUD: ClickToCallConfig.PBXCLOUD,
                extension: extension,
                claveWEBRTC: clave,
                autoanswer: '1'
            });
            
            if (numero) {
                params.append('numero', numero);
            }
            
            return `${ClickToCallConfig.PHONE_BASE_URL}?${params.toString()}`;
        },
        
        // Log con timestamp
        log: function(message, type = 'info') {
            if (ClickToCallConfig.DEBUG) {
                const timestamp = new Date().toLocaleTimeString();
                const prefix = type === 'error' ? '❌' : type === 'warning' ? '⚠️' : '✅';
                console.log(`[${timestamp}] ${prefix} ${message}`);
            }
        }
    }
};

// Exportar para uso global
window.ClickToCallConfig = ClickToCallConfig;

