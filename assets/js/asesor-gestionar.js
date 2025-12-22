/**
 * `assets/js/asesor-gestionar.js`
 *
 * Versión alineada con `views/asesor_gestionar_cliente.php` (incomercio2).
 *
 * Objetivo:
 * - Exponer `window.cambiarClienteSinRecargar(clienteId)` para cambiar de cliente sin recargar la página.
 * - Mantener la sesión WebRTC/softphone viva (sin destruir la instancia por reload).
 * - Actualizar el DOM REAL de `asesor_gestionar_cliente.php` mediante IDs estables.
 *
 * NOTA:
 * Este archivo reemplaza una versión legacy que apuntaba a IDs inexistentes y causaba
 * `Cannot set properties of null` y errores de JSON (por HTML inesperado).
 */

(function () {
  'use strict';

  const logPrefix = '[asesor-gestionar]';

  function getUrlParams() {
    return new URLSearchParams(window.location.search);
  }

  function getCurrentClienteId() {
    const p = getUrlParams();
    return p.get('id') || p.get('cliente_id');
  }

  function escapeHtml(s) {
    return String(s ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  async function fetchJson(url, opts = {}) {
    const res = await fetch(url, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json', ...(opts.headers || {}) },
      ...opts,
    });

    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const text = await res.text();

    if (!res.ok) {
      throw new Error(`HTTP ${res.status} ${res.statusText} en ${url}`);
    }

    // Intentar parsear JSON aunque el Content-Type no sea application/json
    // (algunos servidores envían text/html pero con contenido JSON válido)
    let jsonData = null;
    try {
      jsonData = JSON.parse(text);
      // Si el parseo fue exitoso, retornar los datos aunque el Content-Type no sea correcto
      return jsonData;
    } catch (parseError) {
      // Si falla el parseo, verificar si el Content-Type es correcto
      if (!ct.includes('application/json')) {
        // Verificar si parece ser HTML (página de error/login)
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
          const preview = text.slice(0, 200).replace(/\s+/g, ' ').trim();
          throw new Error(`Respuesta HTML en ${url}. Content-Type=${ct || 'N/A'}. Preview="${preview}"`);
        }
        // Si no es HTML pero tampoco es JSON válido, lanzar error
        const preview = text.slice(0, 200).replace(/\s+/g, ' ').trim();
        throw new Error(`Respuesta no-JSON en ${url}. Content-Type=${ct || 'N/A'}. Preview="${preview}"`);
      }
      // Si el Content-Type es JSON pero el parseo falló, lanzar error de JSON inválido
      const preview = text.slice(0, 200).replace(/\s+/g, ' ').trim();
      throw new Error(`JSON inválido en ${url}. Preview="${preview}"`);
    }
  }

  function setText(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = value ?? '';
  }

  function setValue(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    el.value = value ?? '';
  }

  function renderTelefonos(cliente) {
    const select = document.getElementById('telefonoSelect');
    const display = document.getElementById('telefonoSeleccionadoDisplay');
    if (!select || !display) return;

    const telefonos = [];
    if (cliente.telefono) telefonos.push({ num: cliente.telefono, tipo: 'Teléfono' });
    if (cliente.celular2) telefonos.push({ num: cliente.celular2, tipo: 'Celular' });

    select.innerHTML = '';
    telefonos.forEach((t, idx) => {
      const opt = document.createElement('option');
      opt.value = t.num;
      opt.dataset.tipo = t.tipo;
      opt.textContent = `${t.num} (${t.tipo})`;
      if (idx === 0) opt.selected = true;
      select.appendChild(opt);
    });

    display.value = telefonos[0]?.num || '';
  }

  function renderObligaciones(obligaciones) {
    const panel = document.getElementById('obligacionesListaPanel'); // panel mini izquierdo (HTML)
    const select = document.getElementById('obligacion_seleccionada'); // selector del formulario
    if (!Array.isArray(obligaciones)) obligaciones = [];

    // Selector del formulario
    if (select) {
      const prev = select.value;
      select.innerHTML = '<option value="ninguna">Ninguna</option>';

      obligaciones.forEach((o) => {
        if (!o) return;
        const opt = document.createElement('option');
        opt.value = o.id ?? '';
        opt.dataset.producto = o.producto ?? '';
        opt.dataset.monto = o.saldo_k_obligacion ?? 0;
        opt.dataset.obligacion = o.obligacion ?? '';
        opt.textContent = `${o.producto ?? 'Producto'} - $${Number(o.saldo_k_obligacion ?? 0).toLocaleString('es-CO')}`;
        select.appendChild(opt);
      });

      if (prev && [...select.options].some((op) => op.value === prev)) {
        select.value = prev;
      } else {
        select.value = 'ninguna';
      }
    }

    if (!panel) return;

    if (obligaciones.length === 0) {
      panel.innerHTML = `
        <div style="text-align:center; padding: 14px; color:#7f8c8d;">
          <i class="fas fa-info-circle" style="font-size:18px; margin-bottom:6px;"></i>
          <div style="font-size:12px;">No se encontraron obligaciones para este cliente.</div>
        </div>
      `;
      return;
    }

    panel.innerHTML = obligaciones
      .map((o) => {
        const oblig = o.obligacion ?? 'N/A';
        const prod = o.producto ?? 'N/A';
        const prop = o.propiedad ?? 'N/A';
        const saldo = o.saldo_k_obligacion ?? null;
        return `
          <div class="obligacion-item" style="background:white; border: 1px solid #e1e8ed; border-radius: 8px; padding: 12px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
              <div style="flex:1;">
                <h6 style="margin: 0 0 5px 0; color:#2c3e50; font-size: 13px; font-weight: 600;">
                  <i class="fas fa-file-invoice"></i> Obligación #${escapeHtml(oblig)}
                </h6>
                <div style="font-size: 11px; color:#7f8c8d;">
                  <strong>Producto:</strong> ${escapeHtml(prod)} | <strong>Propiedad:</strong> ${escapeHtml(prop)}
                </div>
              </div>
              <div style="text-align:right; font-size: 11px; color:#27ae60; font-weight: 600;">
                ${saldo ? `$${Number(saldo).toLocaleString('es-CO')}` : 'Sin saldo'}
              </div>
            </div>
          </div>
        `;
      })
      .join('');
  }

  function renderHistorialMini(historial) {
    const panel = document.getElementById('historialMiniPanel');
    if (!panel) return;
    if (!Array.isArray(historial)) historial = [];

    if (historial.length === 0) {
      panel.innerHTML = `
        <div style="text-align:center; padding: 12px; color:#7f8c8d; background:#f8f9fa; border-radius: 6px;">
          <i class="fas fa-info-circle" style="font-size:14px; margin-bottom:5px;"></i>
          <div style="font-size:11px;">No hay gestiones registradas para este cliente.</div>
        </div>
      `;
      return;
    }

    panel.innerHTML = historial
      .slice(0, 5)
      .map((g) => {
        const fecha = g.fecha_gestion || '';
        const res = g.resultado || 'Sin resultado';
        const tipo = g.tipo_gestion || 'N/A';
        return `
          <div style="background:#f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 10px; margin-bottom: 8px;">
            <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start; margin-bottom:5px;">
              <div style="font-size: 11px; color:#6c757d;">
                <i class="fas fa-calendar-alt"></i> ${escapeHtml(fecha)}
              </div>
              <div style="font-size: 10px; color:#28a745; font-weight: 600;">
                ${escapeHtml(res)}
              </div>
            </div>
            <div style="font-size: 10px; color:#495057;">
              <strong>Tipo:</strong> ${escapeHtml(tipo)}
            </div>
          </div>
        `;
      })
      .join('');
  }

  function renderHistorialLlamadas(historial) {
    const container = document.getElementById('historialLlamadasLista');
    if (!container) return;
    if (!Array.isArray(historial)) historial = [];

    if (historial.length === 0) {
      container.innerHTML = `
        <div class="historial-vacio">
          <i class="fas fa-info-circle"></i>
          <p>No hay historial de llamadas registrado para este cliente.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <table class="historial-table">
        <thead>
          <tr>
            <th>Fecha y Hora</th>
            <th>Asesor</th>
            <th>Tipificación</th>
            <th>Observaciones</th>
          </tr>
        </thead>
        <tbody>
          ${historial.map((g) => {
            const fecha = g.fecha_gestion || '';
            let fechaFormateada = 'N/A';
            if (fecha) {
              try {
                const fechaObj = new Date(fecha);
                if (!isNaN(fechaObj.getTime())) {
                  fechaFormateada = fechaObj.toLocaleDateString('es-CO', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                  });
                }
              } catch (e) {
                fechaFormateada = fecha;
              }
            }
            const asesor = g.asesor_nombre || 'N/A';
            const tipificacion = g.tipificacion_completa || g.resultado || g.tipo_gestion || 'N/A';
            const gestionId = g.id || 0;
            
            // Verificar si es acuerdo de pago (resultado '03' o contiene 'ACUERDO DE PAGO')
            const esAcuerdoPago = (g.resultado === '03') || (tipificacion.includes('ACUERDO DE PAGO'));
            const numeroObligacion = g.numero_obligacion || '';
            const valorAcuerdo = g.valor_acuerdo || null;
            const valorCuota = g.valor_cuota || null;
            const numeroCuota = g.numero_cuota || null;
            
            // Formatear valores monetarios
            const formatearPesos = (valor) => {
              if (!valor || valor === 0) return 'N/A';
              return '$' + Number(valor).toLocaleString('es-CO');
            };
            
            return `
              <tr>
                <td>
                  <i class="fas fa-calendar-alt"></i>
                  ${escapeHtml(fechaFormateada)}
                </td>
                <td>
                  <i class="fas fa-user"></i>
                  ${escapeHtml(asesor)}
                </td>
                <td>
                  <span class="tipificacion-badge">
                    ${escapeHtml(tipificacion)}
                  </span>
                  ${esAcuerdoPago ? `
                    <div style="margin-top: 8px; padding: 8px; background: #f0f9ff; border-left: 3px solid #3b82f6; border-radius: 4px; font-size: 12px;">
                      <div style="font-weight: 600; color: #1e40af; margin-bottom: 4px;">
                        <i class="fas fa-file-invoice-dollar"></i> Detalles del Acuerdo:
                      </div>
                      ${numeroObligacion ? `
                        <div style="margin-bottom: 3px;">
                          <strong>Obligación:</strong> #${escapeHtml(numeroObligacion)}
                        </div>
                      ` : ''}
                      ${valorAcuerdo ? `
                        <div style="margin-bottom: 3px;">
                          <strong>Valor Acuerdo:</strong> ${formatearPesos(valorAcuerdo)}
                        </div>
                      ` : ''}
                      ${valorCuota ? `
                        <div style="margin-bottom: 3px;">
                          <strong>Valor Cuota:</strong> ${formatearPesos(valorCuota)}
                        </div>
                      ` : ''}
                      ${numeroCuota ? `
                        <div>
                          <strong>Número Cuota:</strong> ${escapeHtml(numeroCuota)}
                        </div>
                      ` : ''}
                    </div>
                  ` : ''}
                </td>
                <td>
                  <button class="btn-ver-observaciones" 
                          onclick="mostrarObservacionesGestion(${gestionId})"
                          title="Ver observaciones">
                    <i class="fas fa-eye"></i>
                  </button>
                </td>
              </tr>
            `;
          }).join('')}
        </tbody>
      </table>
    `;
  }

  function updateUrl(clienteId) {
    const url = new URL(window.location.href);
    url.searchParams.set('action', 'gestionar_cliente');
    url.searchParams.set('id', String(clienteId));
    url.searchParams.delete('cliente_id');
    url.searchParams.delete('gestion_guardada');
    history.pushState({ clienteId }, '', url.toString());
  }

  /**
   * Verificar si hay una llamada activa en el softphone
   * Verifica tanto la existencia de currentCall como su estado
   */
  function hayLlamadaActiva() {
    if (typeof window.webrtcSoftphone === 'undefined' || !window.webrtcSoftphone) {
      return false;
    }
    
    const call = window.webrtcSoftphone.currentCall;
    if (!call) {
      return false;
    }
    
    // Verificar el estado de la llamada (Established = 4 = llamada activa)
    const state = call.state;
    const stateStr = String(state);
    
    // La llamada está activa si el estado es 'Established' o '4'
    return stateStr === 'Established' || stateStr === '4' || state === 'Established';
  }

  async function cambiarClienteSinRecargar(nuevoClienteId, actualizarUrl = true) {
    console.log(logPrefix, 'cambiarClienteSinRecargar llamado con:', nuevoClienteId, '(tipo:', typeof nuevoClienteId + ')');
    
    if (!nuevoClienteId) {
      console.error(logPrefix, 'ERROR: nuevoClienteId es null/undefined');
      return;
    }
    
    // Convertir a número si es string
    const idNumerico = Number(nuevoClienteId);
    if (isNaN(idNumerico) || idNumerico <= 0) {
      console.error(logPrefix, 'ERROR: nuevoClienteId no es un número válido:', nuevoClienteId);
      return;
    }

    const current = getCurrentClienteId();
    if (String(current) === String(idNumerico)) {
      console.log(logPrefix, 'Ya se está mostrando este cliente');
      return;
    }

    // Verificar si hay una llamada activa (solo para logging, no bloquear)
    if (hayLlamadaActiva()) {
      console.log(logPrefix, '📞 Llamada activa detectada - Cambiando cliente sin interrumpir la llamada');
    }

    console.log(logPrefix, 'cambiarClienteSinRecargar =>', idNumerico);

    if (actualizarUrl) updateUrl(idNumerico);

    try {
      console.log(logPrefix, 'Obteniendo datos del cliente:', idNumerico);
      const [datos, contratos] = await Promise.all([
        fetchJson(`index.php?action=obtener_datos_cliente&id=${encodeURIComponent(idNumerico)}`),
        fetchJson(`index.php?action=obtener_contratos_cliente&id=${encodeURIComponent(idNumerico)}`),
      ]);
      
      console.log(logPrefix, 'Datos recibidos:', datos);
      console.log(logPrefix, 'Contratos recibidos:', contratos);

      if (!datos?.success) throw new Error(datos?.message || 'No se pudieron obtener datos del cliente');
      if (!contratos?.success) throw new Error(contratos?.message || 'No se pudieron obtener obligaciones');

      const cliente = datos.cliente || {};
      const historial = datos.historial || [];
      const obligaciones = contratos.obligaciones || [];

      // Reiniciar cronómetro de gestión al cambiar de cliente
      if (typeof resetearCronometroGestion === 'function') {
        resetearCronometroGestion();
      }
      if (typeof iniciarCronometroGestion === 'function') {
        iniciarCronometroGestion();
      }
      
      setText('clienteNombre', cliente.nombre || '');
      setText('clienteCedula', cliente.cedula || '');
      setValue('inputClienteId', cliente.id || idNumerico);

      // Compat: input hidden del formulario
      const hidden = document.querySelector('input[name="cliente_id"]');
      if (hidden) hidden.value = cliente.id || idNumerico;

      // Actualizar email (mostrar/ocultar según si existe)
      // Buscar el contenedor de email por su estructura HTML
      const panelCliente = document.querySelector('.panel-cliente');
      if (panelCliente) {
        // Buscar todos los items de información del cliente
        const infoItems = panelCliente.querySelectorAll('.cliente-info-item');
        let emailItem = null;
        
        // Buscar el item que contiene "Correo"
        infoItems.forEach(item => {
          const strong = item.querySelector('strong');
          if (strong && strong.textContent.includes('Correo')) {
            emailItem = item;
          }
        });
        
        if (cliente.email && cliente.email.trim() !== '') {
          // Si hay email, actualizar o crear el elemento
          if (emailItem) {
            // Actualizar el email existente
            const emailSpan = emailItem.querySelector('span');
            if (emailSpan) {
              emailSpan.textContent = cliente.email;
            }
            emailItem.style.display = '';
          } else {
            // Crear el elemento de email si no existe
            const cedulaItem = Array.from(infoItems).find(item => {
              const strong = item.querySelector('strong');
              return strong && strong.textContent.includes('Cédula');
            });
            
            if (cedulaItem) {
              emailItem = document.createElement('div');
              emailItem.className = 'cliente-info-item';
              emailItem.innerHTML = `
                <i class="fas fa-envelope"></i>
                <strong>Correo</strong>
                <span>${escapeHtml(cliente.email)}</span>
              `;
              cedulaItem.parentNode.insertBefore(emailItem, cedulaItem.nextSibling);
            }
          }
        } else {
          // Ocultar el email si no existe
          if (emailItem) {
            emailItem.style.display = 'none';
          }
        }
      }

      renderTelefonos(cliente);
      renderObligaciones(obligaciones);
      renderHistorialMini(historial);
      renderHistorialLlamadas(historial);
      
      // Log para debugging
      if (hayLlamadaActiva()) {
        console.log(logPrefix, '✅ Cliente cambiado exitosamente - Llamada activa mantenida');
      } else {
        console.log(logPrefix, '✅ Cliente cambiado exitosamente');
      }

      // Reset básico del formulario de tipificación para evitar estados cruzados
      const form = document.getElementById('tipificacionForm');
      if (form) form.reset();

      // Rehidratar lógica de obligaciones si existe en la vista
      if (typeof window.inicializarGestionObligaciones === 'function') {
        try {
          window.inicializarGestionObligaciones();
        } catch (_) {}
      }
    } catch (e) {
      console.error(logPrefix, 'Fallo en cambiarClienteSinRecargar:', e);
      console.error(logPrefix, 'Detalles del error:', {
        message: e.message,
        stack: e.stack,
        clienteId: idNumerico
      });
      
      // Si hay llamada activa, NUNCA recargar la página
      if (hayLlamadaActiva()) {
        console.error(logPrefix, 'ERROR: No se puede recargar porque hay una llamada activa');
        
        // Intentar obtener al menos los datos básicos del cliente (sin obligaciones)
        console.log(logPrefix, 'Intentando obtener solo datos básicos del cliente...');
        try {
          const datosSolo = await fetchJson(`index.php?action=obtener_datos_cliente&id=${encodeURIComponent(idNumerico)}`);
          if (datosSolo?.success && datosSolo?.cliente) {
            const cliente = datosSolo.cliente;
            setText('clienteNombre', cliente.nombre || '');
            setText('clienteCedula', cliente.cedula || '');
            setValue('inputClienteId', cliente.id || idNumerico);
            
            const hidden = document.querySelector('input[name="cliente_id"]');
            if (hidden) hidden.value = cliente.id || idNumerico;
            
            renderTelefonos(cliente);
            
            // Mostrar mensaje informativo pero no bloquear
            console.warn(logPrefix, '⚠️ Cliente actualizado parcialmente (sin obligaciones) debido a error en contratos');
            alert('Cliente actualizado. Hubo un problema al cargar las obligaciones, pero puedes continuar trabajando. La llamada se mantiene activa.');
            return;
          }
        } catch (error2) {
          console.error(logPrefix, 'Error al obtener datos básicos:', error2);
        }
        
        alert('Error al cargar los datos del cliente. La llamada se mantiene activa. Por favor, intenta nuevamente o contacta al administrador.\n\nError: ' + (e.message || 'Error desconocido'));
        return;
      }
      
      // Solo como último recurso si NO hay llamada activa
      console.warn(logPrefix, 'Fallback: Recargando página (NO hay llamada activa)');
      window.location.href = `index.php?action=gestionar_cliente&id=${encodeURIComponent(idNumerico)}`;
    }
  }

  // Exponer funciones globalmente para uso desde otras partes del código
  window.cambiarClienteSinRecargar = cambiarClienteSinRecargar;
  window.renderHistorialLlamadas = renderHistorialLlamadas;
  window.renderObligaciones = renderObligaciones;
  window.renderTelefonos = renderTelefonos;
  
  // Asegurar que mostrarObservacionesGestion esté disponible si existe en la vista
  // (se define en asesor_gestionar_cliente.php)
  if (typeof window.mostrarObservacionesGestion === 'undefined') {
    console.warn(logPrefix, '⚠️ mostrarObservacionesGestion no está disponible globalmente');
  } else {
    console.log(logPrefix, '✅ mostrarObservacionesGestion está disponible globalmente');
  }

  window.addEventListener('popstate', (ev) => {
    const id = ev.state?.clienteId || getCurrentClienteId();
    if (id) cambiarClienteSinRecargar(id, false);
  });

  document.addEventListener('DOMContentLoaded', () => {
    const id = getCurrentClienteId();
    if (id) {
      try {
        history.replaceState({ clienteId: id }, '', window.location.href);
      } catch (_) {}
    }
  });
})();


