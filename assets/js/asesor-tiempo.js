/**
 * Archivo: assets/js/asesor-tiempo.js
 * Gestión del modal de tiempo de sesión y breaks para asesores
 */

// Variables globales para tiempo de sesión
let sessionStartTime = null;
let sessionTimerInterval = null;
let breakActive = false;
let breakTipoActual = null; // Guardar el tipo de break actual
let breakNombreActual = null; // Guardar el nombre del break actual
let breakStartTime = null; // Tiempo de inicio del break (timestamp)
let breakTimerInterval = null; // Intervalo para el cronómetro del break

// Asegurar que las funciones estén disponibles inmediatamente
// Declarar las funciones primero (hoisting)
function mostrarModalTiempoSesion() {
    console.log('mostrarModalTiempoSesion llamada');
    const modal = document.getElementById('modalTiempoSesion');
    console.log('Modal encontrado:', modal);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        iniciarContadorTiempo();
        console.log('Modal mostrado correctamente');
    } else {
        console.error('Modal de tiempo de sesión no encontrado');
        alert('Error: No se pudo encontrar el modal de tiempo de sesión');
    }
}

function cerrarModalTiempoSesion() {
    const modal = document.getElementById('modalTiempoSesion');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        detenerContadorTiempo();
    }
}

// Las funciones ya están declaradas arriba (líneas 13-35)

/**
 * Función para iniciar contador de tiempo
 */
function iniciarContadorTiempo() {
    if (sessionTimerInterval) {
        clearInterval(sessionTimerInterval);
    }

    sessionTimerInterval = setInterval(function () {
        if (!breakActive) {
            actualizarTiempoSesion();
        }
    }, 1000);

    actualizarTiempoSesion();
}

/**
 * Función para detener contador de tiempo
 */
function detenerContadorTiempo() {
    if (sessionTimerInterval) {
        clearInterval(sessionTimerInterval);
        sessionTimerInterval = null;
    }
}

/**
 * Función para actualizar tiempo de sesión
 */
function actualizarTiempoSesion() {
    if (!sessionStartTime) return;

    const ahora = Math.floor(Date.now() / 1000);
    const tiempoTranscurrido = ahora - sessionStartTime;

    const horas = Math.floor(tiempoTranscurrido / 3600);
    const minutos = Math.floor((tiempoTranscurrido % 3600) / 60);
    const segundos = tiempoTranscurrido % 60;

    const tiempoFormateado =
        String(horas).padStart(2, '0') + ':' +
        String(minutos).padStart(2, '0') + ':' +
        String(segundos).padStart(2, '0');

    const display = document.getElementById('tiempoSesionDisplay');
    if (display) {
        display.textContent = tiempoFormateado;
    }

    // Actualizar hora actual
    const horaActualDisplay = document.getElementById('horaActualDisplay');
    if (horaActualDisplay) {
        const ahoraDate = new Date();
        const horas = ahoraDate.getHours();
        const minutos = ahoraDate.getMinutes();
        const ampm = horas >= 12 ? 'PM' : 'AM';
        const horas12 = horas % 12 || 12;
        const minutosStr = String(minutos).padStart(2, '0');
        horaActualDisplay.textContent = `${horas12}:${minutosStr} ${ampm}`;
    }
}

// Variable para almacenar el tipo de break pendiente de confirmación
let breakPendienteConfirmacion = null;

/**
 * Función para mostrar modal de confirmación de break
 */
function mostrarModalConfirmarBreak(tipo, nombreBreak) {
    const modal = document.getElementById('modalConfirmarBreak');
    const mensaje = document.getElementById('confirmarBreakMensaje');

    if (modal && mensaje) {
        mensaje.textContent = `¿Deseas iniciar un descanso de tipo "${nombreBreak}"?`;
        breakPendienteConfirmacion = tipo;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Configurar botones
        const btnConfirmar = document.getElementById('btnConfirmarBreak');
        const btnCancelar = document.getElementById('btnCancelarBreak');

        if (btnConfirmar) {
            btnConfirmar.onclick = function () {
                confirmarIniciarBreak();
            };
        }

        if (btnCancelar) {
            btnCancelar.onclick = function () {
                cancelarIniciarBreak();
            };
        }

        // Cerrar al hacer clic fuera del modal
        modal.onclick = function (e) {
            if (e.target === modal) {
                cancelarIniciarBreak();
            }
        };
    }
}

/**
 * Función para cerrar modal de confirmación
 */
function cerrarModalConfirmarBreak() {
    const modal = document.getElementById('modalConfirmarBreak');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        breakPendienteConfirmacion = null;
    }
}

/**
 * Función para confirmar inicio de break
 */
function confirmarIniciarBreak() {
    if (breakPendienteConfirmacion) {
        const tipoBreak = breakPendienteConfirmacion; // Capturar el valor antes de cerrar (que lo limpia)
        cerrarModalConfirmarBreak();
        iniciarBreakConfirmado(tipoBreak);
    }
}

/**
 * Función para cancelar inicio de break
 */
function cancelarIniciarBreak() {
    cerrarModalConfirmarBreak();
}

/**
 * Función para registrar break
 */
function registrarBreak(tipo) {
    if (breakActive) {
        alert('Ya tienes un descanso activo. Por favor, finaliza el descanso actual antes de iniciar uno nuevo.');
        return;
    }

    const tiposBreak = {
        'baño': 'Baño',
        'almuerzo': 'Almuerzo',
        'break': 'Break',
        'mantenimiento': 'Mantenimiento',
        'actividad_extra': 'Actividad Extra',
        'pausa_activa': 'Pausa Activa'
    };

    const nombreBreak = tiposBreak[tipo] || tipo;

    // Mostrar modal de confirmación en lugar de confirm()
    mostrarModalConfirmarBreak(tipo, nombreBreak);
}

/**
 * Función para iniciar break después de confirmación
 */
function iniciarBreakConfirmado(tipo) {
    const tiposBreak = {
        'baño': 'Baño',
        'almuerzo': 'Almuerzo',
        'break': 'Break',
        'mantenimiento': 'Mantenimiento',
        'actividad_extra': 'Actividad Extra',
        'pausa_activa': 'Pausa Activa'
    };

    const nombreBreak = tiposBreak[tipo] || tipo;

    // Enviar petición al servidor
    fetch('index.php?action=registrar_break', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            tipo: tipo,
            accion: 'iniciar'
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                breakActive = true;
                breakTipoActual = tipo; // Guardar el tipo actual
                breakNombreActual = nombreBreak; // Guardar el nombre actual
                mostrarEstadoBreak(`Descanso "${nombreBreak}" iniciado correctamente`);

                // Bloquear la pantalla
                bloquearPantalla(nombreBreak);

                // Deshabilitar botones de break
                document.querySelectorAll('.break-btn').forEach(btn => {
                    btn.disabled = true;
                    btn.style.opacity = '0.5';
                    btn.style.cursor = 'not-allowed';
                });

                // Crear botón para finalizar break
                crearBotonFinalizarBreak(tipo, nombreBreak);
            } else {
                alert('Error al registrar el descanso: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al registrar el descanso. Por favor, intenta nuevamente.');
        });
}

/**
 * Función para mostrar estado del break
 */
function mostrarEstadoBreak(mensaje) {
    const statusDiv = document.getElementById('breakStatus');
    const statusText = document.getElementById('breakStatusText');

    if (statusDiv && statusText) {
        statusText.textContent = mensaje;
        statusDiv.style.display = 'block';
    }
}

/**
 * Función para crear botón de finalizar break
 */
function crearBotonFinalizarBreak(tipo, nombreBreak) {
    const breakSection = document.querySelector('.break-section');
    if (!breakSection) return;

    // Eliminar botón anterior si existe
    const botonAnterior = document.getElementById('btnFinalizarBreak');
    if (botonAnterior) {
        botonAnterior.remove();
    }

    const botonFinalizar = document.createElement('button');
    botonFinalizar.id = 'btnFinalizarBreak';
    botonFinalizar.className = 'btn btn-success';
    botonFinalizar.style.width = '100%';
    botonFinalizar.style.marginTop = '15px';
    botonFinalizar.style.padding = '15px';
    botonFinalizar.style.fontSize = '1rem';
    botonFinalizar.innerHTML = '<i class="fas fa-stop"></i> Finalizar Descanso: ' + nombreBreak;
    botonFinalizar.onclick = function () {
        finalizarBreak(tipo, nombreBreak);
    };

    breakSection.appendChild(botonFinalizar);
}

/**
 * Función para finalizar break (con confirmación)
 */
function finalizarBreak(tipo, nombreBreak) {
    if (!confirm(`¿Deseas finalizar el descanso "${nombreBreak}"?`)) {
        return;
    }

    finalizarBreakAutomatico(tipo, nombreBreak);
}

/**
 * Función para finalizar break automáticamente (sin confirmación)
 * Se usa cuando se desbloquea con contraseña
 */
function finalizarBreakAutomatico(tipo, nombreBreak) {
    fetch('index.php?action=registrar_break', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            tipo: tipo,
            accion: 'finalizar'
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                breakActive = false;
                breakTipoActual = null; // Limpiar tipo actual
                breakNombreActual = null; // Limpiar nombre actual
                detenerCronometroBreak(); // Detener cronómetro
                mostrarEstadoBreak(`Descanso "${nombreBreak}" finalizado correctamente`);

                // Desbloquear la pantalla (por si acaso no se desbloqueó antes)
                desbloquearPantalla();

                // Habilitar botones de break para poder elegir otro
                document.querySelectorAll('.break-btn').forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                });

                // Eliminar botón de finalizar
                const botonFinalizar = document.getElementById('btnFinalizarBreak');
                if (botonFinalizar) {
                    botonFinalizar.remove();
                }

                // Ocultar estado después de 3 segundos
                setTimeout(() => {
                    const statusDiv = document.getElementById('breakStatus');
                    if (statusDiv) {
                        statusDiv.style.display = 'none';
                    }
                }, 3000);
            } else {
                console.error('Error al finalizar el descanso:', data.message || 'Error desconocido');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

/**
 * Función para bloquear la pantalla durante un break
 */
function bloquearPantalla(tipoBreak) {
    // Guardar el tiempo de inicio del break
    breakStartTime = Math.floor(Date.now() / 1000);

    // Crear overlay de bloqueo
    let lockOverlay = document.getElementById('screenLockOverlay');
    if (!lockOverlay) {
        lockOverlay = document.createElement('div');
        lockOverlay.id = 'screenLockOverlay';
        lockOverlay.className = 'screen-lock-overlay';
        document.body.appendChild(lockOverlay);
    }

    // Contenido del bloqueo
    lockOverlay.innerHTML = `
        <div class="screen-lock-content">
            <div class="screen-lock-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2>Pantalla Bloqueada</h2>
            <p class="break-type">Descanso: <strong>${tipoBreak}</strong></p>
            <div class="break-timer-container" style="margin: 20px 0; padding: 15px; background: #f0f9ff; border-radius: 10px; border: 2px solid #3b82f6;">
                <div style="font-size: 0.9rem; color: #6b7280; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Tiempo de Descanso</div>
                <div id="breakTimerDisplay" style="font-size: 2.5rem; font-weight: bold; color: #3b82f6; font-family: 'Courier New', monospace; text-align: center;">00:00:00</div>
            </div>
            <p class="lock-message">Ingresa tu contraseña para continuar trabajando</p>
            <div class="lock-form">
                <input type="password" id="lockPassword" class="lock-password-input" placeholder="Contraseña" autocomplete="off">
                <div id="lockError" class="lock-error" style="display: none;"></div>
                <div class="lock-buttons">
                    <button onclick="verificarContrasenaDesbloqueo()" class="btn btn-primary">
                        <i class="fas fa-unlock"></i> Desbloquear
                    </button>
                    <button onclick="cancelarDesbloqueo()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    `;

    lockOverlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Iniciar cronómetro del break
    iniciarCronometroBreak();

    // Enfocar el campo de contraseña
    setTimeout(() => {
        const passwordInput = document.getElementById('lockPassword');
        if (passwordInput) {
            passwordInput.focus();
        }
    }, 100);

    // Permitir desbloqueo con Enter
    const passwordInput = document.getElementById('lockPassword');
    if (passwordInput) {
        passwordInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                verificarContrasenaDesbloqueo();
            }
        });
    }
}

/**
 * Función para iniciar el cronómetro del break
 */
function iniciarCronometroBreak() {
    // Limpiar intervalo anterior si existe
    if (breakTimerInterval) {
        clearInterval(breakTimerInterval);
    }

    // Actualizar inmediatamente
    actualizarCronometroBreak();

    // Actualizar cada segundo
    breakTimerInterval = setInterval(function () {
        actualizarCronometroBreak();
    }, 1000);
}

/**
 * Función para actualizar el cronómetro del break
 */
function actualizarCronometroBreak() {
    if (!breakStartTime) return;

    const ahora = Math.floor(Date.now() / 1000);
    const tiempoTranscurrido = ahora - breakStartTime;

    const horas = Math.floor(tiempoTranscurrido / 3600);
    const minutos = Math.floor((tiempoTranscurrido % 3600) / 60);
    const segundos = tiempoTranscurrido % 60;

    const tiempoFormateado =
        String(horas).padStart(2, '0') + ':' +
        String(minutos).padStart(2, '0') + ':' +
        String(segundos).padStart(2, '0');

    const timerDisplay = document.getElementById('breakTimerDisplay');
    if (timerDisplay) {
        timerDisplay.textContent = tiempoFormateado;
    }
}

/**
 * Función para detener el cronómetro del break
 */
function detenerCronometroBreak() {
    if (breakTimerInterval) {
        clearInterval(breakTimerInterval);
        breakTimerInterval = null;
    }
    breakStartTime = null;
}

/**
 * Función para desbloquear la pantalla
 */
function desbloquearPantalla() {
    // Detener el cronómetro del break
    detenerCronometroBreak();

    const lockOverlay = document.getElementById('screenLockOverlay');
    if (lockOverlay) {
        lockOverlay.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

/**
 * Función para verificar contraseña y desbloquear
 */
function verificarContrasenaDesbloqueo() {
    const passwordInput = document.getElementById('lockPassword');
    const errorDiv = document.getElementById('lockError');
    const password = passwordInput ? passwordInput.value : '';

    if (!password) {
        if (errorDiv) {
            errorDiv.textContent = 'Por favor, ingresa tu contraseña';
            errorDiv.style.display = 'block';
        }
        return;
    }

    // Limpiar error anterior
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }

    // Deshabilitar botón mientras se verifica
    const unlockBtn = document.querySelector('.screen-lock-content .btn-primary');
    if (unlockBtn) {
        unlockBtn.disabled = true;
        unlockBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
    }

    // Verificar contraseña con el servidor
    fetch('index.php?action=verificar_contrasena_desbloqueo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            contrasena: password
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Contraseña correcta: desbloquear pantalla Y finalizar break automáticamente
                desbloquearPantalla();
                if (passwordInput) {
                    passwordInput.value = '';
                }

                // Finalizar el break actual automáticamente sin confirmación
                if (breakActive && breakTipoActual && breakNombreActual) {
                    finalizarBreakAutomatico(breakTipoActual, breakNombreActual);
                }
            } else {
                // Contraseña incorrecta
                if (errorDiv) {
                    errorDiv.textContent = data.message || 'Contraseña incorrecta';
                    errorDiv.style.display = 'block';
                }
                if (passwordInput) {
                    passwordInput.value = '';
                    passwordInput.focus();
                }
            }

            // Restaurar botón
            if (unlockBtn) {
                unlockBtn.disabled = false;
                unlockBtn.innerHTML = '<i class="fas fa-unlock"></i> Desbloquear';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (errorDiv) {
                errorDiv.textContent = 'Error al verificar la contraseña. Intenta nuevamente.';
                errorDiv.style.display = 'block';
            }
            if (unlockBtn) {
                unlockBtn.disabled = false;
                unlockBtn.innerHTML = '<i class="fas fa-unlock"></i> Desbloquear';
            }
        });
}

/**
 * Función para cancelar desbloqueo (no hace nada, mantiene bloqueado)
 */
function cancelarDesbloqueo() {
    const passwordInput = document.getElementById('lockPassword');
    const errorDiv = document.getElementById('lockError');

    if (passwordInput) {
        passwordInput.value = '';
        passwordInput.focus();
    }
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

// Hacer las funciones disponibles globalmente inmediatamente
window.mostrarModalTiempoSesion = mostrarModalTiempoSesion;
window.cerrarModalTiempoSesion = cerrarModalTiempoSesion;
window.registrarBreak = registrarBreak;
window.mostrarModalConfirmarBreak = mostrarModalConfirmarBreak;
window.cerrarModalConfirmarBreak = cerrarModalConfirmarBreak;
window.confirmarIniciarBreak = confirmarIniciarBreak;
window.cancelarIniciarBreak = cancelarIniciarBreak;

// Configurar event listener para el botón de tiempo de sesión INMEDIATAMENTE
// Esto asegura que funcione incluso si el script se carga después del DOM
document.addEventListener('click', function (e) {
    // Verificar si el clic fue en el botón o en el icono dentro del botón
    const btn = e.target.closest('.session-time-btn');
    const icon = e.target.closest('.session-time-btn i');
    const btnVista = e.target.closest('#btnTiempoSesionVista');
    const btnVistaIcon = e.target.closest('#btnTiempoSesionVista i');

    if (btn || icon || btnVista || btnVistaIcon) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Clic detectado en botón de tiempo de sesión');
        if (typeof mostrarModalTiempoSesion === 'function') {
            mostrarModalTiempoSesion();
        } else {
            console.error('mostrarModalTiempoSesion no está definida');
            alert('Error: La función mostrarModalTiempoSesion no está disponible');
        }
    }
});

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    // Obtener el tiempo de inicio de sesión desde el atributo data
    const sessionTimeElement = document.getElementById('sessionStartTime');
    if (sessionTimeElement) {
        sessionStartTime = parseInt(sessionTimeElement.getAttribute('data-start-time')) || Math.floor(Date.now() / 1000);
    } else {
        // Fallback: usar tiempo actual si no se encuentra el elemento
        sessionStartTime = Math.floor(Date.now() / 1000);
    }

    // Configurar event listener para cerrar modal al hacer clic fuera
    const modalTiempoSesion = document.getElementById('modalTiempoSesion');
    if (modalTiempoSesion) {
        modalTiempoSesion.addEventListener('click', function (e) {
            if (e.target === this) {
                cerrarModalTiempoSesion();
            }
        });
    }

    // Alias para compatibilidad con pruebas directas
    if (typeof window.abrirModalTiempoPrueba === 'undefined') {
        window.abrirModalTiempoPrueba = mostrarModalTiempoSesion;
    }
    if (typeof window.cerrarModalTiempoPrueba === 'undefined') {
        window.cerrarModalTiempoPrueba = cerrarModalTiempoSesion;
    }

    // VERIFICAR SI HAY UN BREAK ACTIVO AL CARGAR LA PÁGINA
    verificarBreakActivoAlCargar();
});

/**
 * Verifica si hay un break activo al cargar la página y restaura el estado
 */
function verificarBreakActivoAlCargar() {
    fetch('index.php?action=obtener_break_activo')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.break_activo) {
                console.log('Break activo detectado al cargar:', data);

                // Restaurar estado del break
                breakActive = true;
                breakTipoActual = data.tipo; // Guardar tipo actual
                breakNombreActual = data.tipo_nombre; // Guardar nombre actual

                // Calcular tiempo de inicio del break desde la fecha_inicio
                if (data.fecha_inicio) {
                    const fechaInicio = new Date(data.fecha_inicio);
                    breakStartTime = Math.floor(fechaInicio.getTime() / 1000);
                } else {
                    breakStartTime = Math.floor(Date.now() / 1000);
                }

                // Restaurar bloqueo de pantalla
                bloquearPantalla(data.tipo_nombre);

                // Deshabilitar botones de break
                document.querySelectorAll('.break-btn').forEach(btn => {
                    btn.disabled = true;
                    btn.style.opacity = '0.5';
                    btn.style.cursor = 'not-allowed';
                });

                // Crear botón para finalizar break
                crearBotonFinalizarBreak(data.tipo, data.tipo_nombre);

                // Mostrar estado del break
                mostrarEstadoBreak(`Descanso "${data.tipo_nombre}" activo desde ${new Date(data.fecha_inicio).toLocaleString('es-ES')}`);
            } else {
                console.log('No hay break activo');
                breakActive = false;
            }
        })
        .catch(error => {
            console.error('Error al verificar break activo:', error);
            // En caso de error, asumir que no hay break activo
            breakActive = false;
        });
}
