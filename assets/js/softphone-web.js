/**
 * Softphone WebRTC para Sistema APEX / INCOMERCIO
 * Implementación basada en recomendaciones de arquitectura
 * Versión: SIP.js 0.16.1 + Custom SDH Factory
 */

/**
 * Factory personalizada para permitir el uso de 'mediaStreamFactory' en SIP.js 0.16.1
 * Esto soluciona el problema de "NotFoundError" al evitar llamadas duplicadas a getUserMedia
 */
function createCustomSessionDescriptionHandlerFactory(peerConnectionConfig) {
    return function customFactory(session, options) {
        const logger = session.userAgent.getLogger('sip.SessionDescriptionHandler', session.id);

        // Copiar opciones y asegurar configuración RTC
        const sdhOptions = options || {};
        if (!sdhOptions.peerConnectionConfiguration) {
            sdhOptions.peerConnectionConfiguration = peerConnectionConfig;
        }

        // Crear el SDH por defecto
        const sdh = new SIP.Web.SessionDescriptionHandler(logger, sdhOptions);

        // Guardar la función acquire original
        const originalAcquire = sdh.acquire.bind(sdh);

        // Reemplazar la función acquire para soportar mediaStreamFactory
        sdh.acquire = function (constraints) {
            console.log('🔧 [SDH] acquire() invocado');

            // Si se pasó una factory personalizada, usarla
            if (this.options.mediaStreamFactory) {
                console.log('✅ [SDH] Usando mediaStreamFactory configurada');

                return this.options.mediaStreamFactory()
                    .then(stream => {
                        console.log('✅ [SDH] Stream obtenido de factory. Tracks:', stream.getTracks().length);

                        // Agregar tracks al PeerConnection
                        if (this._peerConnection) {
                            stream.getTracks().forEach(track => {
                                try {
                                    this._peerConnection.addTrack(track, stream);
                                    console.log(`📎 [SDH] Track ${track.kind} agregado`);
                                } catch (e) {
                                    console.warn('⚠️ [SDH] Error agregando track:', e);
                                }
                            });
                        }
                        return stream;
                    });
            }

            // Fallback original
            console.warn('⚠️ [SDH] No hay mediaStreamFactory, usando originalAcquire');
            return originalAcquire(constraints);
        };

        return sdh;
    };
}

class WebRTCSoftphone {
    constructor(config) {
        this.config = config;
        this.userAgent = null;
        this.registerer = null;
        this.currentCall = null;
        this.status = 'disconnected';
        this.lastMediaStream = null;

        this.incomingSound = new Audio('assets/audio/ringtone.mp3');
        this.incomingSound.loop = true;
        this.remoteAudioElement = new Audio();
        this.remoteAudioElement.autoplay = true;

        if (typeof SIP === 'undefined') {
            return console.error('CRITICAL: SIP.js not loaded');
        }

        this.initUI();
        this.setupAudioUnlock();
        this.connect();
    }

    // --- FACTORY DE MEDIA (RECOMENDACIÓN) ---

    async _mediaStreamFactory() {
        // 1. Liberar stream anterior para evitar "NotFoundError"
        this._releaseLastMediaStream();

        // 2. Definir constraints (Audio Only)
        const constraints = {
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true
            },
            video: false
        };

        try {
            console.log('🎤 [_mediaStreamFactory] Solicitando getUserMedia...');

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('El navegador no soporta acceso al micrófono o el sitio no es seguro (HTTPS requerido).');
            }

            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            this.lastMediaStream = stream;
            console.log('✅ [_mediaStreamFactory] Nuevo stream obtenido:', stream.id);
            return stream;
        } catch (err) {
            console.error('❌ [_mediaStreamFactory] Error:', err);

            if (err && err.name === 'NotFoundError') {
                alert('No se detectó micrófono. Verifica que esté conectado.');
            } else if (err && err.name === 'NotAllowedError') {
                alert('Permiso de micrófono denegado. Por favor permite el acceso.');
            } else {
                alert('Error al acceder al micrófono: ' + (err ? err.message : 'Desconocido'));
            }
            throw err;
        }
    }

    _releaseLastMediaStream() {
        if (this.lastMediaStream) {
            console.log('🔇 [_releaseLastMediaStream] Cerrando tracks...');
            this.lastMediaStream.getTracks().forEach(track => track.stop());
            this.lastMediaStream = null;
        }
    }

    _getIceServers() {
        // Configuración STUN estándar
        return [{ urls: 'stun:stun.l.google.com:19302' }];
    }

    // --- CONEXIÓN SIP ---

    async connect() {
        if (!this.config.extension) return;

        const uri = SIP.UserAgent.makeURI(`sip:${this.config.extension}@${this.config.sip_domain}`);
        const rtcConfig = {
            rtcpMuxPolicy: 'negotiate',
            iceServers: this._getIceServers()
        };

        console.log('🚀 [WebRTC Softphone] Version: 5.1 - Safe Media Access');
        console.log('🔧 [WebRTC Softphone] RTC Config:', rtcConfig);

        const options = {
            uri: uri,
            transportOptions: {
                server: this.config.wss_server,
                keepAliveInterval: 30
            },
            hackViaTcp: true,
            authorizationUsername: this.config.extension,
            authorizationPassword: this.config.password,

            // Usar nuestra factory personalizada
            sessionDescriptionHandlerFactory: createCustomSessionDescriptionHandlerFactory(rtcConfig),

            sessionDescriptionHandlerFactoryOptions: {
                peerConnectionConfiguration: rtcConfig,
                mediaStreamFactory: this._mediaStreamFactory.bind(this),
                // Timeouts para evitar hang en ICE
                iceGatheringTimeout: 2000
            },

            delegate: {
                onInvite: (invitation) => this.handleIncomingCall(invitation)
            }
        };

        try {
            this.userAgent = new SIP.UserAgent(options);
            await this.userAgent.start();
            this.updateStatus('connected', 'Conectado');
            this.setupRegisterer();
        } catch (err) {
            console.error('SIP Connect Error:', err);
            this.updateStatus('disconnected', 'Error Conexión');
        }
    }

    setupRegisterer() {
        this.registerer = new SIP.Registerer(this.userAgent);
        this.registerer.stateChange.addListener((state) => {
            if (state === SIP.RegistererState.Registered) {
                this.updateStatus('registered', 'En línea');
            } else {
                this.updateStatus('connected', 'Sin registro');
            }
        });
        this.registerer.register();
    }

    // --- LLAMADAS ---

    async makeCall() {
        const target = document.getElementById('number-display').innerText;
        if (!target || target === 'Ingrese número') return;
        if (this.currentCall) return;

        const targetURI = SIP.UserAgent.makeURI(`sip:${target}@${this.config.sip_domain}`);
        if (!targetURI) return alert('Número inválido');

        // UI
        this.showCallUI(true);

        const inviter = new SIP.Inviter(this.userAgent, targetURI);
        this.currentCall = inviter;
        this.setupSessionListeners(this.currentCall);

        console.log('📞 [makeCall] Iniciando invitación...');

        const options = {
            sessionDescriptionHandlerOptions: {
                // RECOMENDACIÓN: NO pasar constraints explícitas
                // Pasar mediaStreamFactory para que nuestro código maneje el stream
                mediaStreamFactory: () => this._mediaStreamFactory(),
                iceGatheringTimeout: 2000, // Timeout seguridad ICE
                peerConnectionConfiguration: {
                    rtcpMuxPolicy: 'negotiate',
                    iceServers: this._getIceServers()
                }
            }
        };

        try {
            // Nota: Con el custom factory, SIP.js usará _mediaStreamFactory
            // y no llamará a getUserMedia por su cuenta.
            await this.currentCall.invite(options);
            console.log('✅ [makeCall] INVITE enviado');
        } catch (err) {
            console.error('❌ [makeCall] Error:', err);
            alert('Error al llamar: ' + err.message);
            this.resetCall();
        }
    }

    handleIncomingCall(invitation) {
        this.incomingSound.play().catch(() => { });

        if (confirm(`Llamada entrante de ${invitation.remoteIdentity.uri.user}. ¿Contestar?`)) {
            this.incomingSound.pause();

            const options = {
                sessionDescriptionHandlerOptions: {
                    // RECOMENDACIÓN: Mismo patrón para llamadas entrantes
                    mediaStreamFactory: () => this._mediaStreamFactory(),
                    iceGatheringTimeout: 2000,
                    peerConnectionConfiguration: {
                        rtcpMuxPolicy: 'negotiate',
                        iceServers: this._getIceServers()
                    }
                }
            };

            invitation.accept(options)
                .then(() => {
                    this.currentCall = invitation;
                    this.showCallUI(true);
                    this.setupSessionListeners(invitation);
                })
                .catch(err => {
                    alert('Error al contestar: ' + err.message);
                });
        } else {
            this.incomingSound.pause();
            invitation.reject();
        }
    }

    // --- UTILIDADES ---

    setupSessionListeners(session) {
        session.stateChange.addListener((state) => {
            console.log('📊 [Call State]', state);
            if (state === SIP.SessionState.Established) {
                this.setupRemoteAudio(session);
                this.startTimer();
                this.updateCallStatus('En llamada');
            } else if (state === SIP.SessionState.Terminated) {
                this.resetCall();
            }
        });
    }

    setupRemoteAudio(session) {
        const remoteStream = new MediaStream();
        const pc = session.sessionDescriptionHandler.peerConnection;
        pc.getReceivers().forEach((receiver) => {
            if (receiver.track) remoteStream.addTrack(receiver.track);
        });
        this.remoteAudioElement.srcObject = remoteStream;
        this.remoteAudioElement.play().catch(console.error);
    }

    hangup() {
        if (this.currentCall) {
            // Prevenir errores de transición de estado
            if (this.currentCall.state === SIP.SessionState.Terminated ||
                this.currentCall.state === SIP.SessionState.Terminating) {
                this.resetCall();
                return;
            }

            switch (this.currentCall.state) {
                case SIP.SessionState.Initial:
                case SIP.SessionState.Establishing:
                    if (this.currentCall instanceof SIP.Inviter) {
                        this.currentCall.cancel();
                    } else {
                        this.currentCall.reject();
                    }
                    break;
                case SIP.SessionState.Established:
                    this.currentCall.bye();
                    break;
            }
        } else {
            this.resetCall();
        }
    }

    resetCall() {
        this.currentCall = null;
        this.stopTimer();
        this.showCallUI(false);
        this.incomingSound.pause();
        this.incomingSound.currentTime = 0;

        // RECOMENDACIÓN: Liberar siempre al final
        this._releaseLastMediaStream();
    }

    // --- UI Methods ---

    initUI() {
        const container = document.getElementById('webrtc-softphone');
        if (!container) return;
        container.innerHTML = `
            <div class="softphone-header"><h3><i class="fas fa-phone"></i> Softphone</h3></div>
            <div class="softphone-body">
                <div class="softphone-status">
                    <div class="status-indicator">
                        <span class="status-dot disconnected" id="status-dot"></span>
                        <span id="status-text">Desconectado</span>
                    </div>
                </div>
                <div class="number-input-container">
                    <div class="number-display" id="number-display">Ingrese número</div>
                </div>
                <div class="dialpad" id="dialpad">
                    ${[1, 2, 3, 4, 5, 6, 7, 8, 9, '*', 0, '#'].map(n => `<button class="dialpad-btn" data-val="${n}">${n}</button>`).join('')}
                </div>
                <div class="action-buttons">
                     <button class="action-btn delete-btn" onclick="window.webrtcSoftphone.backspace()"><i class="fas fa-backspace"></i></button>
                    <button class="action-btn call-btn" onclick="window.webrtcSoftphone.makeCall()"><i class="fas fa-phone"></i></button>
                    <button class="action-btn hangup-btn" onclick="window.webrtcSoftphone.hangup()" style="display:none;"><i class="fas fa-phone-slash"></i></button>
                </div>
                <div id="call-info" style="display:none; text-align:center; margin-top:10px;">
                    <div id="call-timer">00:00</div>
                    <div id="call-status">Conectando...</div>
                </div>
            </div>`;
        container.querySelectorAll('.dialpad-btn').forEach(btn =>
            btn.addEventListener('click', () => this.addDigit(btn.dataset.val)));
    }

    setupAudioUnlock() {
        document.addEventListener('click', () => {
            this.incomingSound.play().then(() => {
                this.incomingSound.pause();
                this.incomingSound.currentTime = 0;
            }).catch(() => { });
        }, { once: true });
    }

    addDigit(digit) {
        const d = document.getElementById('number-display');
        d.innerText = d.innerText === 'Ingrese número' ? digit : d.innerText + digit;
    }

    backspace() {
        const d = document.getElementById('number-display');
        d.innerText = d.innerText.length > 1 ? d.innerText.slice(0, -1) : 'Ingrese número';
    }

    updateStatus(s, t) {
        this.status = s;
        document.getElementById('status-dot').className = 'status-dot ' + s;
        document.getElementById('status-text').textContent = t;
    }

    updateCallStatus(t) {
        document.getElementById('call-status').textContent = t;
    }

    showCallUI(show) {
        document.querySelector('.call-btn').style.display = show ? 'none' : 'inline-flex';
        document.querySelector('.hangup-btn').style.display = show ? 'inline-flex' : 'none';
        document.getElementById('call-info').style.display = show ? 'block' : 'none';

        // Si ocultamos UI, asegurar que el estado visual se resetee
        if (!show) {
            document.getElementById('call-timer').textContent = '00:00';
            document.getElementById('call-status').textContent = 'Conectando...';
        }
    }

    startTimer() {
        this.callStartTime = Date.now();
        this.callTimer = setInterval(() => {
            const e = Math.floor((Date.now() - this.callStartTime) / 1000);
            document.getElementById('call-timer').textContent =
                `${String(Math.floor(e / 60)).padStart(2, '0')}:${String(e % 60).padStart(2, '0')}`;
        }, 1000);
    }

    stopTimer() { clearInterval(this.callTimer); }

    setNumber(n) { document.getElementById('number-display').innerText = n; }
}

window.WebRTCSoftphone = WebRTCSoftphone;
