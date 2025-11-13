/**
 * Sistema Click to Call Mejorado
 * Conexión automática al iniciar sesión
 * Desconexión automática al cerrar sesión
 * Manejo robusto de CORS
 */

// Cargar configuración
const config = window.ClickToCallConfig || {
    CONNECTION_CHECK_INTERVAL: 3000,
    NOTIFICATION_DURATION: 3000,
    DEBUG: true,
    methods: {
        isWindowClosed: (window) => {
            try { return window.closed; } catch (error) { return true; }
        },
        sendMessage: (window, message) => {
            try { window.postMessage(message, '*'); return true; } catch (error) { return false; }
        },
        isWindowAccessible: (window) => {
            try { window.document; return true; } catch (error) { return false; }
        },
        log: (message, type = 'info') => {
            if (config.DEBUG) {
                const timestamp = new Date().toLocaleTimeString();
                const prefix = type === 'error' ? '❌' : type === 'warning' ? '⚠️' : '✅';
                console.log(`[${timestamp}] ${prefix} ${message}`);
            }
        }
    }
};

class ClickToCallManager {
    constructor() {
        this.isConnected = false;
        this.extension = '';
        this.clave = '';
        this.phoneWindow = null;
        this.connectionCheckInterval = null;
        this.init();
    }

    async init() {
        config.methods.log('Inicializando Click to Call Manager...');
        
        // Verificar si es un asesor
        if (!this.isAsesor()) {
            config.methods.log('Usuario no es asesor, deshabilitando Click to Call', 'warning');
            return;
        }

        // Obtener datos de teléfono
        await this.loadPhoneData();
        
        // NO conectar automáticamente - solo cuando se haga clic en números
        if (this.extension && this.clave) {
            this.updateButtonStatus('ready', 'Listo para Llamar');
        } else {
            this.updateButtonStatus('disconnected', 'Sin Configuración');
        }

        // Configurar eventos
        this.setupEventListeners();
        
        // NO iniciar verificación periódica - solo cuando esté conectado
    }

    isAsesor() {
        // Verificar si estamos en la vista de gestión de cliente
        return document.querySelector('.click-to-call') !== null;
    }

    async loadPhoneData() {
        try {
            const response = await fetch('index.php?action=get_telefono_data');
            const data = await response.json();
            
            if (data.success) {
                this.extension = data.extension;
                this.clave = data.clave;
                console.log('📞 Datos de teléfono cargados:', { extension: this.extension, clave: this.clave });
            } else {
                console.error('❌ Error cargando datos de teléfono:', data.error);
            }
        } catch (error) {
            console.error('❌ Error en la petición de datos de teléfono:', error);
        }
    }

    async connect() {
        if (!this.extension || !this.clave) {
            console.error('❌ No hay datos de teléfono para conectar');
            this.updateButtonStatus('disconnected', 'Sin Datos');
            return false;
        }

        try {
            config.methods.log('Conectando teléfono...');
            
            // Crear URL de llamada
            const callUrl = `https://estaqueue.udpsa.com/phone/phone.php?PBXCLOUD=onix.udpsa.com&extension=${this.extension}&claveWEBRTC=${this.clave}&autoanswer=1`;
            
            // Crear ventana de teléfono
            this.phoneWindow = window.open(
                callUrl,
                'telefono',
                config.WINDOW_FEATURES || 'width=400,height=600,scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no'
            );

            if (this.phoneWindow) {
                this.isConnected = true;
                this.updateButtonStatus('connected', 'Conectado');
                console.log('✅ Teléfono conectado exitosamente');
                
                // Configurar evento de cierre de ventana de forma segura
                try {
                    this.phoneWindow.addEventListener('beforeunload', () => {
                        this.disconnect();
                    });
                } catch (error) {
                    console.log('⚠️ No se pudo configurar evento de cierre (CORS), usando verificación periódica');
                }
                
                return true;
            } else {
                throw new Error('No se pudo abrir la ventana del teléfono');
            }
        } catch (error) {
            console.error('❌ Error conectando teléfono:', error);
            this.updateButtonStatus('disconnected', 'Error');
            return false;
        }
    }

    disconnect() {
        console.log('🔌 Desconectando teléfono...');
        
        if (this.phoneWindow && !this.phoneWindow.closed) {
            this.phoneWindow.close();
        }
        
        this.phoneWindow = null;
        this.isConnected = false;
        this.updateButtonStatus('disconnected', 'Desconectado');
        
        console.log('✅ Teléfono desconectado');
    }

    async toggleConnection() {
        if (this.isConnected) {
            this.disconnect();
        } else {
            await this.connect();
        }
    }

    async makeCall(phoneNumber) {
        // Conectar automáticamente cuando se haga clic en un número
        if (!this.isConnected) {
            config.methods.log('Conectando teléfono para hacer llamada...');
            const connected = await this.connect();
            if (!connected) {
                alert('No se pudo conectar el teléfono. Verifique su configuración.');
                return;
            }
        }

        if (this.phoneWindow && !this.phoneWindow.closed) {
            try {
                // Enfocar la ventana del teléfono
                this.phoneWindow.focus();
                
                console.log(`📞 Marcando número: ${phoneNumber}`);
                
                // Intentar enviar el número a la ventana del teléfono
                const messageSent = config.methods.sendMessage(this.phoneWindow, {
                    action: 'dial',
                    number: phoneNumber
                });
                
                if (messageSent) {
                    config.methods.log('Mensaje enviado a la ventana del teléfono');
                } else {
                    config.methods.log('No se pudo enviar mensaje (CORS), el número debe marcarse manualmente', 'warning');
                    config.methods.log(`Número para marcar: ${phoneNumber}`);
                    
                    // Mostrar notificación al usuario
                    this.showNotification(`Número para marcar: ${phoneNumber}`, 'info');
                }
            } catch (error) {
                console.error('❌ Error al hacer la llamada:', error);
                this.showNotification('Error al hacer la llamada', 'error');
            }
        } else {
            // Si la ventana se cerró, reconectar y marcar
            console.log('🔄 Ventana cerrada, reconectando...');
            await this.connect();
            setTimeout(() => {
                this.makeCall(phoneNumber);
            }, 1000);
        }
    }

    updateButtonStatus(status, text) {
        const button = document.getElementById('telefono-nav-btn');
        const statusSpan = document.getElementById('telefono-status');
        
        if (button && statusSpan) {
            button.className = `telefono-nav-btn ${status}`;
            statusSpan.textContent = text;
        }
    }

    setupEventListeners() {
        // Evento de cierre de sesión
        const logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                this.disconnect();
            });
        }

        // Evento de cierre de página
        window.addEventListener('beforeunload', () => {
            this.disconnect();
        });

        // Evento de visibilidad de página
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && this.isConnected) {
                console.log('📱 Página oculta, manteniendo conexión...');
            }
        });
    }

    startConnectionCheck() {
        this.connectionCheckInterval = setInterval(() => {
            if (this.isConnected) {
                try {
                    // Verificar si la ventana está cerrada usando método seguro
                    if (this.phoneWindow && config.methods.isWindowClosed(this.phoneWindow)) {
                        config.methods.log('Ventana de teléfono cerrada, desconectando...', 'warning');
                        this.disconnect();
                    } else if (this.phoneWindow) {
                        // Verificar si la ventana es accesible (para detectar CORS)
                        if (!config.methods.isWindowAccessible(this.phoneWindow)) {
                            // Si no es accesible, solo verificamos si está cerrada
                            if (config.methods.isWindowClosed(this.phoneWindow)) {
                                config.methods.log('Ventana de teléfono cerrada, desconectando...', 'warning');
                                this.disconnect();
                            }
                        }
                    }
                } catch (error) {
                    // Error al verificar la ventana, asumir que está cerrada
                    config.methods.log('Error verificando ventana de teléfono, desconectando...', 'error');
                    this.disconnect();
                }
            }
        }, config.CONNECTION_CHECK_INTERVAL || 3000);
    }

    stopConnectionCheck() {
        if (this.connectionCheckInterval) {
            clearInterval(this.connectionCheckInterval);
            this.connectionCheckInterval = null;
        }
    }

    showNotification(message, type = 'info') {
        // Crear notificación temporal
        const notification = document.createElement('div');
        notification.className = `telefono-notification ${type}`;
        notification.textContent = message;
        
        // Agregar al body
        document.body.appendChild(notification);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
}

// Variables globales para compatibilidad
let clickToCallManager = null;
let extensionUsuario = '';
let claveWebRTC = '';

// Función global para toggle de conexión
function toggleTelefonoConnection() {
    if (window.clickToCallManager) {
        window.clickToCallManager.toggleConnection();
    } else {
        console.error('ClickToCallManager no está inicializado');
    }
}

function llamarCliente(numero) {
    if (window.clickToCallManager) {
        window.clickToCallManager.makeCall(numero);
    } else {
        console.error('ClickToCallManager no está inicializado');
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    clickToCallManager = new ClickToCallManager();
});

// Exportar para uso global
window.ClickToCallManager = ClickToCallManager;
window.toggleTelefonoConnection = toggleTelefonoConnection;
window.llamarCliente = llamarCliente;
