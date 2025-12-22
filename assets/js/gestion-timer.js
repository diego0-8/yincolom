/**
 * Archivo: assets/js/gestion-timer.js
 * Cronómetro para medir la duración de cada gestión de cliente
 * Se activa al cargar la vista de gestionar cliente
 * Se detiene y guarda cuando se guarda la gestión
 */

// Variables globales para el cronómetro de gestión
let gestionStartTime = null;
let gestionTimerInterval = null;
let gestionDuracionSegundos = 0;

/**
 * Iniciar el cronómetro de gestión cuando se carga la vista
 */
function iniciarCronometroGestion() {
    // Limpiar intervalo anterior si existe
    if (gestionTimerInterval) {
        clearInterval(gestionTimerInterval);
    }
    
    // Guardar tiempo de inicio (timestamp en segundos)
    gestionStartTime = Math.floor(Date.now() / 1000);
    gestionDuracionSegundos = 0;
    
    console.log('⏱️ [Cronómetro Gestión] Iniciado');
    
    // Actualizar cada segundo
    gestionTimerInterval = setInterval(function() {
        if (gestionStartTime) {
            const ahora = Math.floor(Date.now() / 1000);
            gestionDuracionSegundos = ahora - gestionStartTime;
            actualizarDisplayCronometroGestion();
        }
    }, 1000);
    
    // Actualizar inmediatamente
    actualizarDisplayCronometroGestion();
}

/**
 * Detener el cronómetro de gestión
 */
function detenerCronometroGestion() {
    if (gestionTimerInterval) {
        clearInterval(gestionTimerInterval);
        gestionTimerInterval = null;
    }
    
    // Calcular duración final
    if (gestionStartTime) {
        const ahora = Math.floor(Date.now() / 1000);
        gestionDuracionSegundos = ahora - gestionStartTime;
    }
    
    console.log('⏱️ [Cronómetro Gestión] Detenido. Duración:', gestionDuracionSegundos, 'segundos');
    
    return gestionDuracionSegundos;
}

/**
 * Obtener la duración actual en segundos
 */
function obtenerDuracionGestion() {
    if (gestionStartTime) {
        const ahora = Math.floor(Date.now() / 1000);
        return ahora - gestionStartTime;
    }
    return gestionDuracionSegundos;
}

/**
 * Actualizar el display del cronómetro (si existe un elemento para mostrarlo)
 */
function actualizarDisplayCronometroGestion() {
    const display = document.getElementById('gestionTimerDisplay');
    if (display) {
        const horas = Math.floor(gestionDuracionSegundos / 3600);
        const minutos = Math.floor((gestionDuracionSegundos % 3600) / 60);
        const segundos = gestionDuracionSegundos % 60;
        
        const tiempoFormateado =
            String(horas).padStart(2, '0') + ':' +
            String(minutos).padStart(2, '0') + ':' +
            String(segundos).padStart(2, '0');
        
        display.textContent = tiempoFormateado;
    }
}

/**
 * Resetear el cronómetro (cuando se cambia de cliente sin guardar)
 */
function resetearCronometroGestion() {
    detenerCronometroGestion();
    gestionStartTime = null;
    gestionDuracionSegundos = 0;
    actualizarDisplayCronometroGestion();
    console.log('⏱️ [Cronómetro Gestión] Resetado');
}

// Hacer funciones disponibles globalmente
window.iniciarCronometroGestion = iniciarCronometroGestion;
window.detenerCronometroGestion = detenerCronometroGestion;
window.obtenerDuracionGestion = obtenerDuracionGestion;
window.resetearCronometroGestion = resetearCronometroGestion;

// Iniciar automáticamente cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un momento para asegurar que todo esté cargado
    setTimeout(function() {
        iniciarCronometroGestion();
    }, 500);
});

