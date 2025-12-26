<?php
/**
 * Vista: Gestionar Cliente (Asesor)
 * Archivo: views/asesor_gestionar_cliente.php
 * 
 * Vista rediseñada con layout de 3 columnas:
 * - Columna 1: Información del cliente + Obligaciones
 * - Columna 2: Formulario de registro de gestión
 * - Columna 3: Softphone WebRTC
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Gestionar Cliente'); ?></title>
    <?php require_once 'shared_styles.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/asesor-gestionar-cliente.css">
    <link rel="stylesheet" href="assets/css/softphone-web.css">
</head>

<body>
    <?php
    require_once 'shared_navbar.php';
    $navbar = getNavbar('Gestionar Cliente', $_SESSION['user_role'] ?? '');
    // Agregar icono de búsqueda al navbar del asesor
    if (($_SESSION['user_role'] ?? '') === 'asesor') {
        // Insertar icono de búsqueda antes del cierre de user-section
        $navbar = str_replace(
            '<div class="user-section">',
            '<div class="user-section">
                <button id="btnNavbarBuscar" class="navbar-search-btn" onclick="abrirModalBuscarCliente()" title="Buscar Cliente">
                    <i class="fas fa-search"></i>
                </button>',
            $navbar
        );
    }
    echo $navbar;
    ?>

    <div class="gestion-container">
        <!-- Layout de 3 Columnas -->
        <div class="main-content">
            <!-- Columna 1: Información del Cliente + Obligaciones -->
            <div class="columna-cliente">
                <!-- Panel de Información del Cliente -->
                <div class="panel-cliente">
                    <h2 id="clienteNombre"><?php echo htmlspecialchars($cliente['nombre'] ?? 'Cliente'); ?></h2>

                    <div class="cliente-info-item">
                        <i class="fas fa-id-card"></i>
                        <strong>Cédula</strong>
                        <span id="clienteCedula"><?php echo htmlspecialchars($cliente['cedula'] ?? ''); ?></span>
                    </div>

                    <?php if (!empty($cliente['email'])): ?>
                        <div class="cliente-info-item">
                            <i class="fas fa-envelope"></i>
                            <strong>Correo</strong>
                            <span><?php echo htmlspecialchars($cliente['email']); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Selector de Teléfono -->
                    <div class="telefono-selector">
                        <select id="telefonoSelect">
                            <?php if (!empty($cliente['telefono'])): ?>
                                <option value="<?php echo htmlspecialchars($cliente['telefono']); ?>" data-tipo="Teléfono">
                                    <?php echo htmlspecialchars($cliente['telefono']); ?> (Teléfono)
                                </option>
                            <?php endif; ?>
                            <?php if (!empty($cliente['celular2'])): ?>
                                <option value="<?php echo htmlspecialchars($cliente['celular2']); ?>" data-tipo="Celular">
                                    <?php echo htmlspecialchars($cliente['celular2']); ?> (Celular)
                                </option>
                            <?php endif; ?>
                        </select>
                        <div class="telefono-display">
                            <input type="text" id="telefonoSeleccionadoDisplay"
                                value="<?php echo htmlspecialchars($cliente['telefono'] ?? $cliente['celular2'] ?? ''); ?>"
                                readonly>
                            <button type="button" onclick="iniciarLlamadaDesdeTelefonoSeleccionado()">
                                <i class="fas fa-phone"></i> Llamar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Panel de Obligaciones -->
                <div class="panel" id="panelObligacionesHistorial">
                    <h3><i class="fas fa-file-invoice"></i> Obligaciones</h3>
                    <div id="obligacionesListaPanel">
                        <?php if (!empty($obligaciones)): ?>
                            <?php foreach ($obligaciones as $obligacion): ?>
                                <div class="obligacion-item">
                                    <h6>
                                        <i class="fas fa-file-invoice"></i>
                                        Obligación #<?php echo htmlspecialchars($obligacion['obligacion'] ?? 'N/A'); ?>
                                    </h6>
                                    <div style="font-size: 11px; color: #6b7280;">
                                        <strong>Producto:</strong>
                                        <?php echo htmlspecialchars($obligacion['producto'] ?? 'N/A'); ?> |
                                        <strong>Propiedad:</strong>
                                        <?php echo htmlspecialchars($obligacion['propiedad'] ?? 'N/A'); ?>
                                    </div>
                                    <div style="text-align: right; margin-top: 5px; color: #10b981; font-weight: 600;">
                                        $<?php echo number_format($obligacion['saldo_k_obligacion'] ?? 0, 0, ',', '.'); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 14px; color: #7f8c8d;">
                                <i class="fas fa-info-circle" style="font-size: 18px; margin-bottom: 6px;"></i>
                                <div style="font-size: 12px;">No se encontraron obligaciones para este cliente.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Columna 2: Formulario de Registro de Gestión -->
            <div class="columna-formulario">
                <div class="form-container">
                    <h2><i class="fas fa-clipboard-list"></i> Registrar Gestión</h2>

                    <form id="tipificacionForm" method="POST" action="javascript:void(0);" onsubmit="return false;">
                        <input type="hidden" name="cliente_id" id="inputClienteId"
                            value="<?php echo htmlspecialchars($cliente['id'] ?? ''); ?>">
                        <input type="hidden" name="tipificacion" id="tipificacion_principal">
                        <input type="hidden" name="sub_tipificacion" id="sub_tipificacion_hidden">
                        <input type="hidden" name="duracion_llamada" id="duracion_llamada_hidden" value="0">

                        <!-- Forma de Contacto -->
                        <div class="form-group">
                            <label for="forma_contacto">
                                Forma de Contacto <span class="required-indicator">*</span>
                            </label>
                            <select name="forma_contacto" id="forma_contacto" required>
                                <option value="llamada">Llamada</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="email">Email</option>
                            </select>
                        </div>

                        <!-- Obligación Seleccionada (PRIMERO) -->
                        <div class="form-group">
                            <label for="obligacion_seleccionada">
                                Obligación <span id="obligacion_required_indicator" class="required-indicator"
                                    style="display: none;">*</span>
                            </label>
                            <select name="obligacion_id" id="obligacion_seleccionada"
                                onchange="manejarSeleccionObligacion()">
                                <option value="ninguna">Ninguna</option>
                                <?php if (!empty($obligaciones)): ?>
                                    <?php foreach ($obligaciones as $obligacion): ?>
                                        <option value="<?php echo htmlspecialchars($obligacion['id'] ?? ''); ?>"
                                            data-producto="<?php echo htmlspecialchars($obligacion['producto'] ?? ''); ?>"
                                            data-monto="<?php echo htmlspecialchars($obligacion['saldo_k_obligacion'] ?? 0); ?>"
                                            data-obligacion="<?php echo htmlspecialchars($obligacion['obligacion'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($obligacion['producto'] ?? 'Producto'); ?> -
                                            $<?php echo number_format($obligacion['saldo_k_obligacion'] ?? 0, 0, ',', '.'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <input type="hidden" name="producto_gestionado" id="producto_gestionado">
                            <input type="hidden" name="monto_obligacion" id="monto_obligacion">
                            <input type="hidden" name="numero_obligacion" id="numero_obligacion">
                        </div>

                        <!-- Árbol de Tipificación (DESPUÉS de Obligación) -->
                        <!-- Tipo de Gestión -->
                        <div class="form-group">
                            <label for="tipo_gestion">
                                Tipo de Gestión <span class="required-indicator">*</span>
                            </label>
                            <select name="tipo_gestion" id="tipo_gestion"
                                onchange="mostrarTipificacionesEspecificas(this.value)" required>
                                <option value="">Seleccione...</option>
                                <option value="hacer_llamada">Hacer Llamada</option>
                                <option value="recibir_llamada">Recibir Llamada</option>
                            </select>
                        </div>

                        <!-- Subcategoría Hacer Llamada -->
                        <div class="form-group" id="subcategoria_hacer_llamada" style="display: none;">
                            <label for="subcategoria_hacer">
                                Subcategoría <span class="required-indicator">*</span>
                            </label>
                            <select name="subcategoria_hacer" id="subcategoria_hacer"
                                onchange="mostrarOpcionesEspecificasHacer(this.value)">
                                <option value="">Seleccione...</option>
                                <option value="1.1">CON INTENCIÓN DE PAGO</option>
                                <option value="1.2">SIN INTENCIÓN DE PAGO</option>
                                <option value="1.3">NO COLABORA</option>
                                <option value="1.4">YA PAGO</option>
                                <option value="2">NO CONTACTADO</option>
                            </select>
                        </div>

                        <!-- Opciones Específicas Hacer -->
                        <div class="form-group" id="opciones_especificas_hacer" style="display: none;">
                            <label for="opcion_especifica_hacer">
                                Opción Específica <span class="required-indicator">*</span>
                            </label>
                            <select name="opcion_especifica_hacer" id="opcion_especifica_hacer"
                                onchange="seleccionarOpcionEspecificaHacer(this.value)">
                                <option value="">Seleccione...</option>
                            </select>
                        </div>

                        <!-- Subcategoría Recibir Llamada -->
                        <div class="form-group" id="subcategoria_recibir_llamada" style="display: none;">
                            <label for="subcategoria_recibir">
                                Subcategoría <span class="required-indicator">*</span>
                            </label>
                            <select name="subcategoria_recibir" id="subcategoria_recibir"
                                onchange="mostrarOpcionesEspecificasRecibir(this.value)">
                                <option value="">Seleccione...</option>
                                <option value="1.1">CON INTENCIÓN DE PAGO</option>
                                <option value="1.2">SIN INTENCIÓN DE PAGO</option>
                                <option value="1.3">NO COLABORA</option>
                                <option value="1.4">YA PAGO</option>
                                <option value="2">NO CONTACTADO</option>
                            </select>
                        </div>

                        <!-- Opciones Específicas Recibir -->
                        <div class="form-group" id="opciones_especificas_recibir" style="display: none;">
                            <label for="opcion_especifica_recibir">
                                Opción Específica <span class="required-indicator">*</span>
                            </label>
                            <select name="opcion_especifica_recibir" id="opcion_especifica_recibir"
                                onchange="seleccionarOpcionEspecificaRecibir(this.value)">
                                <option value="">Seleccione...</option>
                            </select>
                        </div>

                        <!-- Campos de Acuerdo de Pago (se muestran dinámicamente) -->
                        <div class="campos-especificos" id="campos_acuerdo_pago">
                            <h4 style="margin: 0 0 15px 0; font-size: 16px;">Acuerdo de Pago</h4>

                            <div class="form-group">
                                <label for="valor_acuerdo">Valor del Acuerdo</label>
                                <input type="text" id="valor_acuerdo"
                                    oninput="formatearPesos(this, 'valor_acuerdo_hidden')" placeholder="0">
                                <input type="hidden" name="valor_acuerdo" id="valor_acuerdo_hidden">
                            </div>

                            <div class="form-group">
                                <label for="no_cuotas">Número de Cuotas</label>
                                <input type="number" name="no_cuotas" id="no_cuotas" min="1" placeholder="1">
                            </div>

                            <div class="form-group">
                                <label for="fecha_pago">Fecha de Pago</label>
                                <input type="date" name="fecha_pago" id="fecha_pago">
                            </div>

                            <div class="form-group">
                                <label for="valor_cuota">Valor de la Cuota</label>
                                <input type="text" id="valor_cuota" oninput="formatearPesos(this, 'valor_cuota_hidden')"
                                    placeholder="0">
                                <input type="hidden" name="valor_cuota" id="valor_cuota_hidden">
                            </div>

                            <div class="form-group">
                                <label for="numero_cuota">Número de Cuota</label>
                                <input type="number" name="numero_cuota" id="numero_cuota" min="1" placeholder="1">
                            </div>
                        </div>

                        <!-- Comentarios -->
                        <div class="form-group">
                            <label for="comentarios">
                                Comentarios / Observaciones <span class="required-indicator">*</span>
                            </label>
                            <textarea name="comentarios" id="comentarios" required
                                placeholder="Ingrese los comentarios de la gestión..."></textarea>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="btn-actions">
                            <button type="submit" id="btnGuardarPrincipal" class="btn-primary">
                                <i class="fas fa-save"></i> Guardar Gestión
                            </button>
                            <button type="button" class="btn-secondary" onclick="abrirModalAgregarInformacion()">
                                <i class="fas fa-plus-circle"></i> Agregar Información
                            </button>
                        </div>
                    </form>

                    <!-- Botones de Navegación -->
                    <div class="nav-buttons" id="btnNavegacion">
                        <button type="button" id="btnSiguienteCliente" onclick="irAlSiguienteCliente()">
                            <i class="fas fa-arrow-right"></i> Siguiente Cliente
                        </button>
                        <button type="button" id="btnBuscarCliente" onclick="abrirModalBuscarCliente()">
                            <i class="fas fa-search"></i> Buscar Cliente
                        </button>
                    </div>
                </div>
            </div>

            <!-- Columna 3: Softphone WebRTC -->
            <div class="columna-softphone">
                <div class="softphone-container">
                    <div id="webrtc-softphone"></div>
                </div>
            </div>
        </div>

        <!-- Historial de Llamadas (Ancho completo debajo del softphone) -->
        <div class="historial-llamadas-container">
            <div class="historial-llamadas-header">
                <h3><i class="fas fa-history"></i> Historial de Llamadas</h3>
            </div>
            <div class="historial-llamadas-body">
                <div id="historialLlamadasLista">
                    <?php if (!empty($historial)): ?>
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
                                <?php foreach ($historial as $gestion): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-calendar-alt"></i>
                                            <?php
                                            $fecha = $gestion['fecha_gestion'] ?? '';
                                            if ($fecha) {
                                                $fechaObj = new DateTime($fecha);
                                                echo htmlspecialchars($fechaObj->format('d/m/Y H:i'));
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-user"></i>
                                            <?php echo htmlspecialchars($gestion['asesor_nombre'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <span class="tipificacion-badge">
                                                <?php echo htmlspecialchars($gestion['tipificacion_completa'] ?? $gestion['resultado'] ?? 'N/A'); ?>
                                            </span>
                                            <?php
                                            // Mostrar detalles del acuerdo de pago si la tipificación es '03' (ACUERDO DE PAGO)
                                            if (($gestion['resultado'] ?? '') === '03' || strpos($gestion['tipificacion_completa'] ?? '', 'ACUERDO DE PAGO') !== false):
                                                $numeroObligacion = $gestion['numero_obligacion'] ?? '';
                                                $valorAcuerdo = $gestion['valor_acuerdo'] ?? null;
                                                $valorCuota = $gestion['valor_cuota'] ?? null;
                                                $numeroCuota = $gestion['numero_cuota'] ?? null;
                                                ?>
                                                <div
                                                    style="margin-top: 8px; padding: 8px; background: #f0f9ff; border-left: 3px solid #3b82f6; border-radius: 4px; font-size: 12px;">
                                                    <div style="font-weight: 600; color: #1e40af; margin-bottom: 4px;">
                                                        <i class="fas fa-file-invoice-dollar"></i> Detalles del Acuerdo:
                                                    </div>
                                                    <?php if ($numeroObligacion): ?>
                                                        <div style="margin-bottom: 3px;">
                                                            <strong>Obligación:</strong>
                                                            #<?php echo htmlspecialchars($numeroObligacion); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($valorAcuerdo): ?>
                                                        <div style="margin-bottom: 3px;">
                                                            <strong>Valor Acuerdo:</strong>
                                                            $<?php echo number_format($valorAcuerdo, 0, ',', '.'); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($valorCuota): ?>
                                                        <div style="margin-bottom: 3px;">
                                                            <strong>Valor Cuota:</strong>
                                                            $<?php echo number_format($valorCuota, 0, ',', '.'); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($numeroCuota): ?>
                                                        <div>
                                                            <strong>Número Cuota:</strong> <?php echo htmlspecialchars($numeroCuota); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn-ver-observaciones"
                                                onclick="mostrarObservacionesGestion(<?php echo htmlspecialchars($gestion['id'] ?? 0); ?>)"
                                                title="Ver observaciones">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="historial-vacio">
                            <i class="fas fa-info-circle"></i>
                            <p>No hay historial de llamadas registrado para este cliente.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Observaciones de Gestión -->
    <div class="modal-observaciones" id="modalObservaciones"
        onclick="if(event.target === this) cerrarModalObservaciones()">
        <div class="modal-observaciones-content" onclick="event.stopPropagation()">
            <div class="modal-observaciones-header">
                <h3><i class="fas fa-eye"></i> Observaciones de la Gestión</h3>
                <button class="modal-observaciones-close" onclick="cerrarModalObservaciones()" title="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-observaciones-body">
                <div id="observacionesContenido">
                    <div class="loading-observaciones">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Cargando observaciones...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Búsqueda de Cliente (Navbar) -->
    <div class="modal-buscar-cliente" id="modalBuscarCliente"
        onclick="if(event.target === this) cerrarModalBuscarCliente()">
        <div class="modal-buscar-content" onclick="event.stopPropagation()">
            <div class="modal-buscar-header">
                <h3><i class="fas fa-search"></i> Buscar Cliente</h3>
                <button class="modal-buscar-close" onclick="cerrarModalBuscarCliente()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="formBuscarClienteNavbar" onsubmit="buscarClienteNavbar(event)">
                <div class="modal-buscar-form">
                    <div class="form-group">
                        <label for="inputBuscarCliente">
                            <i class="fas fa-id-card"></i> Cédula o Teléfono
                        </label>
                        <input type="text" id="inputBuscarCliente" placeholder="Ingrese cédula o teléfono..."
                            autocomplete="off" minlength="2" required>
                    </div>
                    <div class="modal-buscar-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <button type="button" class="btn-secondary" onclick="cerrarModalBuscarCliente()">
                            Cancelar
                        </button>
                    </div>
                </div>
            </form>
            <div id="resultadosBuscarCliente" class="modal-buscar-results"></div>
        </div>
    </div>

    <!-- Modal de Agregar Información -->
    <div class="modal-agregar-informacion" id="modalAgregarInformacion"
        onclick="if(event.target === this) cerrarModalAgregarInformacion()">
        <div class="modal-agregar-content" onclick="event.stopPropagation()">
            <div class="modal-agregar-header">
                <h3><i class="fas fa-plus-circle"></i> Agregar Información del Cliente</h3>
                <button class="modal-agregar-close" onclick="cerrarModalAgregarInformacion()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="formAgregarInformacion" onsubmit="guardarInformacionCliente(event)">
                <div class="modal-agregar-body">
                    <!-- Campo de Correo -->
                    <div class="form-group">
                        <label for="nuevoEmail">
                            <i class="fas fa-envelope"></i> Correo Electrónico
                        </label>
                        <input type="email" id="nuevoEmail" name="email" placeholder="ejemplo@correo.com"
                            autocomplete="off">
                        <small class="form-text">Si ingresas un correo, reemplazará el existente.</small>
                    </div>

                    <!-- Teléfonos -->
                    <div class="form-group">
                        <label>
                            <i class="fas fa-phone"></i> Teléfonos
                        </label>
                        <div id="telefonosContainer">
                            <div class="telefono-item">
                                <input type="tel" class="telefono-input" placeholder="Número de teléfono"
                                    pattern="[0-9+\-\s()]+" autocomplete="off">
                                <button type="button" class="btn-remove-telefono" onclick="eliminarTelefono(this)"
                                    style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn-add-telefono" onclick="agregarCampoTelefono()">
                            <i class="fas fa-plus"></i> Agregar Otro Teléfono
                        </button>
                        <small class="form-text">Los teléfonos se agregarán sin eliminar los existentes.</small>
                    </div>
                </div>
                <div class="modal-agregar-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar Información
                    </button>
                    <button type="button" class="btn-secondary" onclick="cerrarModalAgregarInformacion()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>


    <?php
    // Variables ya están disponibles desde el controlador:
    // $webrtcConfig, $datosTelefono, $tieneTelefono, $basePath
    ?>

    <script>
        // Configuración del softphone
        const webrtcConfig = {
            wss_server: '<?php echo $webrtcConfig['wss_server']; ?>',
            sip_domain: '<?php echo $webrtcConfig['sip_domain']; ?>',
            extension: '<?php echo htmlspecialchars($datosTelefono['extension_telefono'] ?? ''); ?>',
            password: '<?php echo htmlspecialchars($datosTelefono['clave_webrtc'] ?? ''); ?>',
            display_name: '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Asesor'); ?>',
            iceServers: <?php echo json_encode($webrtcConfig['iceServers']); ?>,
            debug_mode: <?php echo $webrtcConfig['debug_mode'] ? 'true' : 'false'; ?>,
            base_path: '<?php echo $basePath; ?>'
        };

        // Inicializar softphone solo si tiene teléfono configurado
        <?php if ($tieneTelefono && !empty($datosTelefono['extension_telefono'])): ?>

            // Función para inicializar el softphone
            function inicializarSoftphoneIncomercio() {
                // Verificar que SIP.js esté cargado
                if (typeof SIP === 'undefined') {
                    console.warn('⚠️ [Softphone] SIP.js aún no está cargado, reintentando en 100ms...');
                    setTimeout(inicializarSoftphoneIncomercio, 100);
                    return;
                }

                if (typeof WebRTCSoftphone === 'undefined') {
                    console.error('❌ [Softphone] WebRTCSoftphone no está cargado');
                    return;
                }

                if (window.webrtcSoftphone) {
                    console.warn('⚠️ [Softphone] Ya existe una instancia del softphone');
                    return;
                }

                try {
                    window.webrtcSoftphone = new WebRTCSoftphone(webrtcConfig);
                    console.log('✅ [Softphone] Inicializado correctamente');
                } catch (error) {
                    console.error('❌ [Softphone] Error al inicializar:', error);
                }
            }

            // Llamar desde WebRTC
            async function llamarDesdeWebRTC(numero) {
                if (!window.webrtcSoftphone) {
                    console.error('❌ [Softphone] No está inicializado');
                    alert('El softphone no está disponible. Por favor, espera a que se conecte.');
                    return;
                }

                // Validar que el número no esté vacío
                if (!numero || numero.trim() === '') {
                    alert('Por favor, selecciona un número de teléfono.');
                    return;
                }

                // Limpiar el número (solo dígitos)
                const numeroLimpio = numero.toString().replace(/\D/g, '');

                if (numeroLimpio === '') {
                    alert('El número de teléfono no es válido.');
                    return;
                }

                console.log('📞 [Llamar] Iniciando llamada al número:', numeroLimpio);

                try {
                    // Usar callNumber() que establece el número y luego llama automáticamente
                    if (typeof window.webrtcSoftphone.callNumber === 'function') {
                        await window.webrtcSoftphone.callNumber(numeroLimpio);
                        console.log('✅ [Llamar] Llamada iniciada correctamente');
                    } else if (typeof window.webrtcSoftphone.setNumber === 'function' && typeof window.webrtcSoftphone.makeCall === 'function') {
                        // Fallback: establecer número y luego llamar
                        window.webrtcSoftphone.setNumber(numeroLimpio);
                        await window.webrtcSoftphone.makeCall();
                        console.log('✅ [Llamar] Llamada iniciada usando setNumber + makeCall');
                    } else {
                        // Último fallback: establecer currentNumber directamente
                        window.webrtcSoftphone.currentNumber = numeroLimpio;
                        if (typeof window.webrtcSoftphone._updateNumberDisplay === 'function') {
                            window.webrtcSoftphone._updateNumberDisplay();
                        }
                        await window.webrtcSoftphone.makeCall();
                        console.log('✅ [Llamar] Llamada iniciada usando currentNumber directo');
                    }
                } catch (error) {
                    console.error('❌ [Llamar] Error al iniciar llamada:', error);
                    alert('Error al iniciar la llamada: ' + (error.message || 'Error desconocido'));
                }
            }

            // Esperar a que todos los scripts estén cargados
            let intentosEspera = 0;
            const maxIntentos = 50; // 5 segundos máximo (50 * 100ms)

            function esperarScriptsYInicializar() {
                intentosEspera++;

                // Verificar que SIP.js esté disponible
                if (typeof SIP === 'undefined') {
                    if (intentosEspera < maxIntentos) {
                        if (intentosEspera % 10 === 0) {
                            console.log(`⏳ [Softphone] Esperando a que SIP.js se cargue... (intento ${intentosEspera}/${maxIntentos})`);
                        }
                        setTimeout(esperarScriptsYInicializar, 100);
                    } else {
                        console.error('❌ [Softphone] Timeout: SIP.js no se cargó después de 5 segundos');
                    }
                    return;
                }

                // Verificar que WebRTCSoftphone esté disponible
                if (typeof WebRTCSoftphone === 'undefined') {
                    if (intentosEspera < maxIntentos) {
                        if (intentosEspera % 10 === 0) {
                            console.log(`⏳ [Softphone] Esperando a que softphone-web.js se cargue... (intento ${intentosEspera}/${maxIntentos})`);
                        }
                        setTimeout(esperarScriptsYInicializar, 100);
                    } else {
                        console.error('❌ [Softphone] Timeout: softphone-web.js no se cargó después de 5 segundos');
                    }
                    return;
                }

                // Verificar que el contenedor exista
                const container = document.getElementById('webrtc-softphone');
                if (!container) {
                    if (intentosEspera < maxIntentos) {
                        setTimeout(esperarScriptsYInicializar, 100);
                    } else {
                        console.error('❌ [Softphone] No se encontró el contenedor #webrtc-softphone');
                    }
                    return;
                }

                // Todo está listo, inicializar
                console.log('✅ [Softphone] Todos los scripts están cargados, inicializando...');
                inicializarSoftphoneIncomercio();
            }

            // Función para inicializar cuando los scripts estén listos (definida antes de cargar scripts)
            window.inicializarSoftphoneCuandoListo = function () {
                // Resetear contador de intentos
                intentosEspera = 0;
                // Esperar un momento adicional para asegurar que todo esté completamente cargado
                setTimeout(function () {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', esperarScriptsYInicializar);
                    } else {
                        esperarScriptsYInicializar();
                    }
                }, 100);
            };

        <?php else: ?>
            console.warn('⚠️ [Softphone] Usuario sin teléfono configurado');
        <?php endif; ?>
    </script>

    <!-- Script del cronómetro de gestión -->
    <script src="assets/js/gestion-timer.js"></script>

    <!-- Scripts - CARGAR PRIMERO asesor-gestionar.js para que esté disponible -->
    <script>
            // CRÍTICO: Cargar asesor-gestionar.js PRIMERO para que cambiarClienteSinRecargar esté disponible
            (function () {
                console.log('📦 [Carga Scripts] Iniciando carga de scripts...');

                // Paso 1: Cargar asesor-gestionar.js PRIMERO (sin dependencias)
                const asesorScript = document.createElement('script');
                asesorScript.src = 'assets/js/asesor-gestionar.js';
                asesorScript.onload = function () {
                    console.log('✅ [Carga Scripts] asesor-gestionar.js cargado - cambiarClienteSinRecargar disponible');

                    // Paso 2: Cargar SIP.js
                    const sipScript = document.createElement('script');
                    sipScript.src = 'assets/js/sip.min.js';
                    sipScript.onload = function () {
                        console.log('✅ [Carga Scripts] SIP.js cargado');

                        // Paso 3: Cargar softphone-web.js
                        const softphoneScript = document.createElement('script');
                        softphoneScript.src = 'assets/js/softphone-web.js?v=' + new Date().getTime();
                        softphoneScript.onload = function () {
                            console.log('✅ [Carga Scripts] softphone-web.js cargado');

                            // Paso 4: Inicializar softphone si está configurado
                            if (typeof window.inicializarSoftphoneCuandoListo === 'function') {
                                window.inicializarSoftphoneCuandoListo();
                            }
                        };
                        softphoneScript.onerror = function () {
                            console.error('❌ [Carga Scripts] Error al cargar softphone-web.js');
                        };
                        document.head.appendChild(softphoneScript);
                    };
                    sipScript.onerror = function () {
                        console.error('❌ [Carga Scripts] Error al cargar SIP.js, intentando CDN...');
                        const sipScriptCDN = document.createElement('script');
                        sipScriptCDN.src = 'https://cdn.jsdelivr.net/npm/sip.js@0.20.0/dist/sip.min.js';
                        sipScriptCDN.onload = function () {
                            console.log('✅ [Carga Scripts] SIP.js cargado desde CDN');
                            const softphoneScript = document.createElement('script');
                            softphoneScript.src = 'assets/js/softphone-web.js?v=' + new Date().getTime();
                            softphoneScript.onload = function () {
                                console.log('✅ [Carga Scripts] softphone-web.js cargado');
                                if (typeof window.inicializarSoftphoneCuandoListo === 'function') {
                                    window.inicializarSoftphoneCuandoListo();
                                }
                            };
                            document.head.appendChild(softphoneScript);
                        };
                        sipScriptCDN.onerror = function () {
                            console.error('❌ [Carga Scripts] Error: No se pudo cargar SIP.js');
                        };
                        document.head.appendChild(sipScriptCDN);
                    };
                    document.head.appendChild(sipScript);
                };
                asesorScript.onerror = function () {
                    console.error('❌ [Carga Scripts] ERROR CRÍTICO: No se pudo cargar asesor-gestionar.js');
                    alert('Error crítico: No se pudo cargar el script necesario. Por favor, recarga la página.');
                };
                document.head.appendChild(asesorScript);
            })();
    </script>

    <script>
        // Funciones de tipificación
        const opcionesEspecificas = {
            '1.1': [
                { value: '03', text: 'ACUERDO DE PAGO' },
                { value: '04', text: 'PAGO TOTAL' },
                { value: '06', text: 'PROMESA' },
                { value: '06.1', text: 'BANNER' },
                { value: '06.2', text: 'REFINANCIACION' },
                { value: '06.3', text: 'UNIFICACION' },
                { value: '06.4', text: 'NIVELACION O NORMALIZACION' },
                { value: '13', text: 'VOLUNTAD DE PAGO' }
            ],
            '1.2': [
                { value: '12', text: 'RENUENTE' }
            ],
            '1.3': [
                { value: '09', text: 'NEGOCIACION EN TRAMITE' },
                { value: '10', text: 'SEGUIM GESTION' },
                { value: '11', text: 'SEGUIMIENTO' }
            ],
            '1.4': [
                { value: '05', text: 'YA PAGO' },
                { value: '07', text: 'REPORTE DE PAGO' },
                { value: '08', text: 'ABONOS' }
            ],
            '2': [
                { value: '14', text: 'VOLVER A LLAMAR' },
                { value: '14.1', text: 'VOLVER A LLAMAR HOY' },
                { value: '15', text: 'LOCALIZADO' },
                { value: '19', text: 'NO CONTESTAN' },
                { value: '24', text: 'NO LOCALIZADO' },
                { value: '25', text: 'NUMERO EQUIVOCADO' }
            ]
        };

        function mostrarTipificacionesEspecificas(tipo) {
            const hacerDiv = document.getElementById('subcategoria_hacer_llamada');
            const recibirDiv = document.getElementById('subcategoria_recibir_llamada');
            const opcionesHacer = document.getElementById('opciones_especificas_hacer');
            const opcionesRecibir = document.getElementById('opciones_especificas_recibir');

            if (tipo === 'hacer_llamada') {
                hacerDiv.style.display = 'block';
                recibirDiv.style.display = 'none';
                opcionesRecibir.style.display = 'none';
            } else if (tipo === 'recibir_llamada') {
                recibirDiv.style.display = 'block';
                hacerDiv.style.display = 'none';
                opcionesHacer.style.display = 'none';
            } else {
                hacerDiv.style.display = 'none';
                recibirDiv.style.display = 'none';
                opcionesHacer.style.display = 'none';
                opcionesRecibir.style.display = 'none';
            }
        }

        function mostrarOpcionesEspecificasHacer(subcategoria) {
            const opcionesDiv = document.getElementById('opciones_especificas_hacer');
            const select = document.getElementById('opcion_especifica_hacer');

            if (!opcionesEspecificas[subcategoria]) {
                opcionesDiv.style.display = 'none';
                return;
            }

            select.innerHTML = '<option value="">Seleccione...</option>';
            opcionesEspecificas[subcategoria].forEach(op => {
                const option = document.createElement('option');
                option.value = op.value;
                option.textContent = op.text;
                select.appendChild(option);
            });

            opcionesDiv.style.display = 'block';
        }

        function mostrarOpcionesEspecificasRecibir(subcategoria) {
            const opcionesDiv = document.getElementById('opciones_especificas_recibir');
            const select = document.getElementById('opcion_especifica_recibir');

            if (!opcionesEspecificas[subcategoria]) {
                opcionesDiv.style.display = 'none';
                return;
            }

            select.innerHTML = '<option value="">Seleccione...</option>';
            opcionesEspecificas[subcategoria].forEach(op => {
                const option = document.createElement('option');
                option.value = op.value;
                option.textContent = op.text;
                select.appendChild(option);
            });

            opcionesDiv.style.display = 'block';
        }

        function seleccionarOpcionEspecificaHacer(valor) {
            document.getElementById('sub_tipificacion_hidden').value = valor;
            document.getElementById('tipificacion_principal').value = 'hacer_llamada';

            const obligacionSelect = document.getElementById('obligacion_seleccionada');
            const obligacionRequiredIndicator = document.getElementById('obligacion_required_indicator');

            // Mostrar campos de acuerdo de pago si es '03'
            if (valor === '03') {
                document.getElementById('campos_acuerdo_pago').classList.add('active');
                // Hacer obligatorio el campo de obligación
                if (obligacionSelect) {
                    obligacionSelect.required = true;
                    obligacionSelect.setAttribute('data-required-for-acuerdo', 'true');

                    // Deshabilitar la opción "Ninguna" cuando es acuerdo de pago
                    const opcionNinguna = obligacionSelect.querySelector('option[value="ninguna"]');
                    if (opcionNinguna) {
                        opcionNinguna.disabled = true;
                        opcionNinguna.style.display = 'none'; // Ocultar visualmente
                    }

                    // Si está seleccionada "Ninguna", cambiarla a la primera obligación disponible
                    if (obligacionSelect.value === 'ninguna') {
                        const primeraObligacion = obligacionSelect.querySelector('option:not([value="ninguna"]):not([disabled])');
                        if (primeraObligacion) {
                            obligacionSelect.value = primeraObligacion.value;
                            manejarSeleccionObligacion(); // Actualizar campos relacionados
                        } else {
                            // Si no hay obligaciones disponibles, mostrar alerta
                            alert('Para registrar un acuerdo de pago, debe haber al menos una obligación disponible.');
                        }
                    }
                }
                if (obligacionRequiredIndicator) {
                    obligacionRequiredIndicator.style.display = 'inline';
                }
            } else {
                document.getElementById('campos_acuerdo_pago').classList.remove('active');
                // Quitar obligatoriedad si no es acuerdo de pago
                if (obligacionSelect && obligacionSelect.getAttribute('data-required-for-acuerdo') === 'true') {
                    obligacionSelect.required = false;
                    obligacionSelect.removeAttribute('data-required-for-acuerdo');

                    // Restaurar la opción "Ninguna" cuando no es acuerdo de pago
                    const opcionNinguna = obligacionSelect.querySelector('option[value="ninguna"]');
                    if (opcionNinguna) {
                        opcionNinguna.disabled = false;
                        opcionNinguna.style.display = ''; // Mostrar visualmente
                    }
                }
                if (obligacionRequiredIndicator) {
                    obligacionRequiredIndicator.style.display = 'none';
                }
            }
        }

        function seleccionarOpcionEspecificaRecibir(valor) {
            document.getElementById('sub_tipificacion_hidden').value = valor;
            document.getElementById('tipificacion_principal').value = 'recibir_llamada';

            const obligacionSelect = document.getElementById('obligacion_seleccionada');
            const obligacionRequiredIndicator = document.getElementById('obligacion_required_indicator');

            // Mostrar campos de acuerdo de pago si es '03'
            if (valor === '03') {
                document.getElementById('campos_acuerdo_pago').classList.add('active');
                // Hacer obligatorio el campo de obligación
                if (obligacionSelect) {
                    obligacionSelect.required = true;
                    obligacionSelect.setAttribute('data-required-for-acuerdo', 'true');

                    // Deshabilitar la opción "Ninguna" cuando es acuerdo de pago
                    const opcionNinguna = obligacionSelect.querySelector('option[value="ninguna"]');
                    if (opcionNinguna) {
                        opcionNinguna.disabled = true;
                        opcionNinguna.style.display = 'none'; // Ocultar visualmente
                    }

                    // Si está seleccionada "Ninguna", cambiarla a la primera obligación disponible
                    if (obligacionSelect.value === 'ninguna') {
                        const primeraObligacion = obligacionSelect.querySelector('option:not([value="ninguna"]):not([disabled])');
                        if (primeraObligacion) {
                            obligacionSelect.value = primeraObligacion.value;
                            manejarSeleccionObligacion(); // Actualizar campos relacionados
                        } else {
                            // Si no hay obligaciones disponibles, mostrar alerta
                            alert('Para registrar un acuerdo de pago, debe haber al menos una obligación disponible.');
                        }
                    }
                }
                if (obligacionRequiredIndicator) {
                    obligacionRequiredIndicator.style.display = 'inline';
                }
            } else {
                document.getElementById('campos_acuerdo_pago').classList.remove('active');
                // Quitar obligatoriedad si no es acuerdo de pago
                if (obligacionSelect && obligacionSelect.getAttribute('data-required-for-acuerdo') === 'true') {
                    obligacionSelect.required = false;
                    obligacionSelect.removeAttribute('data-required-for-acuerdo');

                    // Restaurar la opción "Ninguna" cuando no es acuerdo de pago
                    const opcionNinguna = obligacionSelect.querySelector('option[value="ninguna"]');
                    if (opcionNinguna) {
                        opcionNinguna.disabled = false;
                        opcionNinguna.style.display = ''; // Mostrar visualmente
                    }
                }
                if (obligacionRequiredIndicator) {
                    obligacionRequiredIndicator.style.display = 'none';
                }
            }
        }

        function formatearPesos(input, hiddenId) {
            let value = input.value.replace(/[^\d]/g, '');
            if (value === '') {
                input.value = '';
                document.getElementById(hiddenId).value = '';
                return;
            }
            const formatted = Number(value).toLocaleString('es-CO');
            input.value = formatted;
            document.getElementById(hiddenId).value = value;
        }

        function manejarSeleccionObligacion() {
            const select = document.getElementById('obligacion_seleccionada');
            const option = select.options[select.selectedIndex];

            document.getElementById('producto_gestionado').value = option.dataset.producto || '';
            document.getElementById('monto_obligacion').value = option.dataset.monto || '';
            document.getElementById('numero_obligacion').value = option.dataset.obligacion || '';
        }

        function resetearFormularioParaNuevoCliente() {
            document.getElementById('tipificacionForm').reset();
            document.getElementById('campos_acuerdo_pago').classList.remove('active');
            document.getElementById('subcategoria_hacer_llamada').style.display = 'none';
            document.getElementById('subcategoria_recibir_llamada').style.display = 'none';
            document.getElementById('opciones_especificas_hacer').style.display = 'none';
            document.getElementById('opciones_especificas_recibir').style.display = 'none';

            // Limpiar estado de obligatorio del campo de obligación
            const obligacionSelect = document.getElementById('obligacion_seleccionada');
            const obligacionRequiredIndicator = document.getElementById('obligacion_required_indicator');
            if (obligacionSelect) {
                obligacionSelect.required = false;
                obligacionSelect.removeAttribute('data-required-for-acuerdo');

                // Restaurar la opción "Ninguna" cuando se resetea el formulario
                const opcionNinguna = obligacionSelect.querySelector('option[value="ninguna"]');
                if (opcionNinguna) {
                    opcionNinguna.disabled = false;
                    opcionNinguna.style.display = ''; // Mostrar visualmente
                }
            }
            if (obligacionRequiredIndicator) {
                obligacionRequiredIndicator.style.display = 'none';
            }
        }

        /**
         * Verifica si el softphone está registrado y listo para hacer llamadas
         * @returns {boolean} true si está registrado, false si no
         */
        function verificarSoftphoneRegistrado() {
            if (!window.webrtcSoftphone) {
                return false;
            }

            // Verificar estado del registerer (método correcto)
            if (window.webrtcSoftphone.registerer) {
                const regState = window.webrtcSoftphone.registerer.state;
                // Verificar si está registrado usando el estado del registerer
                // SIP.RegistererState.Registered = 2
                if (regState === 2 || regState === 'Registered' || regState === SIP?.RegistererState?.Registered) {
                    return true;
                }
            }

            // Verificar también el status del softphone como fallback
            if (window.webrtcSoftphone.status === 'connected') {
                return true;
            }

            return false;
        }

        /**
         * Espera a que el softphone esté registrado antes de hacer la llamada
         * @param {string} numero - Número a llamar
         * @param {number} intentos - Número de intentos realizados
         * @param {number} maxIntentos - Máximo número de intentos
         */
        async function esperarRegistroYLLamar(numero, intentos = 0, maxIntentos = 20) {
            if (verificarSoftphoneRegistrado()) {
                console.log('✅ [Llamar] Softphone registrado, iniciando llamada...');
                llamarDesdeWebRTC(numero);
                return;
            }

            if (intentos >= maxIntentos) {
                alert('El softphone no se ha conectado al servidor después de varios intentos. Por favor, verifica tu conexión y recarga la página.');
                return;
            }

            // Esperar 250ms antes de intentar de nuevo
            await new Promise(resolve => setTimeout(resolve, 250));
            console.log(`⏳ [Llamar] Esperando registro del softphone... (intento ${intentos + 1}/${maxIntentos})`);
            esperarRegistroYLLamar(numero, intentos + 1, maxIntentos);
        }

        function iniciarLlamadaDesdeTelefonoSeleccionado() {
            const select = document.getElementById('telefonoSelect');
            if (!select) {
                alert('Error: No se encontró el selector de teléfono.');
                return;
            }

            const numero = select.value;
            if (!numero || numero.trim() === '') {
                alert('Por favor, selecciona un número de teléfono del desplegable.');
                return;
            }

            console.log('📞 [Llamar] Número seleccionado:', numero);

            // Verificar que el softphone esté disponible
            if (!window.webrtcSoftphone) {
                alert('El softphone no está disponible. Por favor, espera a que se conecte al servidor.');
                return;
            }

            // Verificar que no haya una llamada activa
            if (window.webrtcSoftphone.currentCall) {
                const call = window.webrtcSoftphone.currentCall;
                const state = call.state;
                const stateStr = String(state);
                const hayLlamada = stateStr === 'Established' || stateStr === '4' || state === 'Established';

                if (hayLlamada) {
                    if (!confirm('Ya hay una llamada en curso. ¿Deseas colgar la llamada actual y llamar a este número?')) {
                        return;
                    }
                    // Colgar la llamada actual
                    window.webrtcSoftphone.hangup();
                    // Esperar un momento antes de iniciar la nueva llamada
                    setTimeout(() => {
                        esperarRegistroYLLamar(numero);
                    }, 500);
                    return;
                }
            }

            // Verificar registro y esperar si es necesario
            if (verificarSoftphoneRegistrado()) {
                // Ya está registrado, hacer la llamada directamente
                llamarDesdeWebRTC(numero);
            } else {
                // No está registrado aún, esperar a que se registre
                console.log('⏳ [Llamar] Softphone no registrado aún, esperando registro...');
                esperarRegistroYLLamar(numero);
            }
        }

        function actualizarTelefonoSeleccionado() {
            const select = document.getElementById('telefonoSelect');
            const display = document.getElementById('telefonoSeleccionadoDisplay');
            if (select && display) {
                display.value = select.value;
            }
        }

        document.getElementById('telefonoSelect')?.addEventListener('change', actualizarTelefonoSeleccionado);

        // Navegación - NUNCA recargar la página
        async function irAlSiguienteCliente() {
            console.log('➡️ [Navegación] Obteniendo siguiente cliente...');

            // Esperar a que cambiarClienteSinRecargar esté disponible
            let intentos = 0;
            const maxIntentos = 50;
            while (typeof window.cambiarClienteSinRecargar !== 'function' && intentos < maxIntentos) {
                await new Promise(resolve => setTimeout(resolve, 100));
                intentos++;
            }

            if (typeof window.cambiarClienteSinRecargar !== 'function') {
                console.error('❌ [Navegación] cambiarClienteSinRecargar no disponible después de esperar');
                alert('Error: El script necesario no se cargó. Por favor, recarga la página.');
                return;
            }

            // Verificar si hay llamada activa (solo para logging, no bloquear)
            if (window.webrtcSoftphone && window.webrtcSoftphone.currentCall) {
                const call = window.webrtcSoftphone.currentCall;
                const state = call.state;
                const stateStr = String(state);
                const hayLlamada = stateStr === 'Established' || stateStr === '4' || state === 'Established';

                if (hayLlamada) {
                    console.log('📞 [Navegación] Hay llamada activa detectada - Cambiando cliente sin interrumpir la llamada');
                }
            }

            try {
                const response = await fetch('index.php?action=obtener_siguiente_cliente');
                const data = await response.json();

                if (data.success && data.siguiente_cliente) {
                    const clienteId = data.siguiente_cliente.id;
                    console.log('✅ [Navegación] Siguiente cliente encontrado:', clienteId);
                    console.log('✅ [Navegación] Usando cambiarClienteSinRecargar para mantener la llamada activa');

                    // SIEMPRE usar cambiarClienteSinRecargar - NUNCA recargar
                    window.cambiarClienteSinRecargar(clienteId);
                } else {
                    alert(data.message || 'No hay más clientes disponibles');
                }
            } catch (error) {
                console.error('❌ [Navegación] Error al obtener siguiente cliente:', error);
                alert('Error al obtener el siguiente cliente');
            }
        }

        /**
         * Función única para abrir el modal de búsqueda de cliente
         * NO requiere confirmación - solo abre el modal (la confirmación se hace al seleccionar)
         */
        function abrirModalBuscarCliente() {
            console.log('🔍 [Búsqueda] Abriendo modal de búsqueda de cliente');

            // Verificar si hay llamada activa para logging (pero no bloquear la búsqueda)
            if (window.webrtcSoftphone && window.webrtcSoftphone.currentCall) {
                const call = window.webrtcSoftphone.currentCall;
                const state = call.state;
                const stateStr = String(state);
                const hayLlamada = stateStr === 'Established' || stateStr === '4' || state === 'Established';

                if (hayLlamada) {
                    console.log('📞 [Búsqueda] Hay llamada activa - El usuario puede buscar sin interrumpir la llamada');
                }
            }

            const modal = document.getElementById('modalBuscarCliente');
            if (modal) {
                modal.classList.add('active');
                // Limpiar resultados anteriores
                document.getElementById('resultadosBuscarCliente').innerHTML = '';
                // Enfocar input
                setTimeout(() => {
                    document.getElementById('inputBuscarCliente').focus();
                }, 100);
            } else {
                console.error('❌ [Búsqueda] No se encontró el modal #modalBuscarCliente');
            }
        }

        /**
         * Cerrar el modal de búsqueda
         */
        function cerrarModalBuscarCliente() {
            const modal = document.getElementById('modalBuscarCliente');
            if (modal) {
                modal.classList.remove('active');
                // Limpiar formulario
                document.getElementById('formBuscarClienteNavbar').reset();
                document.getElementById('resultadosBuscarCliente').innerHTML = '';
            }
        }

        /**
         * Buscar cliente usando AJAX (sin recargar página)
         * @param {Event} event - Evento del formulario
         */
        async function buscarClienteNavbar(event) {
            event.preventDefault();

            const termino = document.getElementById('inputBuscarCliente').value.trim();
            const resultadosDiv = document.getElementById('resultadosBuscarCliente');

            if (termino.length < 2) {
                resultadosDiv.innerHTML = '<div class="modal-buscar-empty"><i class="fas fa-info-circle"></i><div>Ingrese al menos 2 caracteres</div></div>';
                return;
            }

            resultadosDiv.innerHTML = '<div class="modal-buscar-loading"><i class="fas fa-spinner fa-spin"></i><div>Buscando...</div></div>';

            try {
                const response = await fetch('index.php?action=buscar_cliente_asesor', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ termino, criterio: 'auto' })
                });

                const data = await response.json();

                if (data.success && data.clientes && data.clientes.length > 0) {
                    mostrarResultadosBuscarCliente(data.clientes);
                } else {
                    resultadosDiv.innerHTML = '<div class="modal-buscar-empty"><i class="fas fa-search"></i><div>No se encontraron clientes</div></div>';
                }
            } catch (error) {
                console.error('❌ [Búsqueda] Error al buscar clientes:', error);
                resultadosDiv.innerHTML = '<div class="modal-buscar-empty" style="color: #ef4444;"><i class="fas fa-exclamation-triangle"></i><div>Error al buscar clientes</div></div>';
            }
        }

        /**
         * Mostrar resultados de búsqueda en el modal
         * @param {Array} clientes - Array de clientes encontrados
         */
        function mostrarResultadosBuscarCliente(clientes) {
            const resultadosDiv = document.getElementById('resultadosBuscarCliente');

            if (!clientes || clientes.length === 0) {
                resultadosDiv.innerHTML = '<div class="modal-buscar-empty"><i class="fas fa-search"></i><div>No se encontraron clientes</div></div>';
                return;
            }

            resultadosDiv.innerHTML = clientes.map(function (cliente) {
                // Asegurar que el ID existe y es válido
                const clienteId = cliente.id || cliente.ID_CLIENTE || cliente.id_cliente;

                if (!clienteId) {
                    console.error('❌ [Búsqueda] Cliente sin ID válido:', cliente);
                    return ''; // Saltar este cliente
                }

                const nombre = cliente.nombre || 'Sin nombre';
                const cedula = cliente.cedula || 'N/A';
                const telefono = cliente.telefono || cliente.celular2 || 'N/A';

                // Usar data-attribute en lugar de onclick directo para mayor seguridad
                return '<div class="modal-buscar-result-item" data-cliente-id="' + clienteId + '" onclick="seleccionarClienteDesdeBusqueda(' + clienteId + ')">' +
                    '<strong><i class="fas fa-user"></i> ' + nombre + '</strong>' +
                    '<small><i class="fas fa-id-card"></i> Cédula: ' + cedula + ' | <i class="fas fa-phone"></i> Tel: ' + telefono + '</small>' +
                    '</div>';
            }).filter(html => html !== '').join(''); // Filtrar elementos vacíos
        }

        /**
         * Seleccionar cliente desde los resultados de búsqueda
         * Usa AJAX para cambiar de cliente SIN recargar la página
         * @param {number|string} clienteId - ID del cliente seleccionado
         */
        async function seleccionarClienteDesdeBusqueda(clienteId) {
            console.log('🔍 [Búsqueda] Seleccionando cliente desde búsqueda:', clienteId, '(tipo:', typeof clienteId + ')');

            // Validar que el ID existe y es válido
            if (!clienteId || clienteId === 'undefined' || clienteId === 'null' || clienteId === '') {
                console.error('❌ [Búsqueda] ID de cliente inválido:', clienteId);
                alert('Error: ID de cliente inválido. Por favor, intenta nuevamente.');
                return;
            }

            // Convertir a número si es string
            const idNumerico = Number(clienteId);
            if (isNaN(idNumerico) || idNumerico <= 0) {
                console.error('❌ [Búsqueda] ID de cliente no es un número válido:', clienteId);
                alert('Error: ID de cliente no válido. Por favor, intenta nuevamente.');
                return;
            }

            console.log('✅ [Búsqueda] ID validado:', idNumerico);

            // Esperar a que cambiarClienteSinRecargar esté disponible
            let intentos = 0;
            const maxIntentos = 50;
            while (typeof window.cambiarClienteSinRecargar !== 'function' && intentos < maxIntentos) {
                await new Promise(resolve => setTimeout(resolve, 100));
                intentos++;
            }

            if (typeof window.cambiarClienteSinRecargar !== 'function') {
                console.error('❌ [Búsqueda] cambiarClienteSinRecargar no disponible después de esperar');
                alert('Error: El script necesario no se cargó. Por favor, recarga la página.');
                return;
            }

            // Verificar si hay llamada activa (solo para logging, no bloquear)
            if (window.webrtcSoftphone && window.webrtcSoftphone.currentCall) {
                const call = window.webrtcSoftphone.currentCall;
                const state = call.state;
                const stateStr = String(state);
                const hayLlamada = stateStr === 'Established' || stateStr === '4' || state === 'Established';

                if (hayLlamada) {
                    console.log('📞 [Búsqueda] Hay llamada activa detectada - Cambiando cliente sin interrumpir la llamada');
                }
            }

            cerrarModalBuscarCliente();

            // SIEMPRE usar cambiarClienteSinRecargar - NUNCA recargar
            console.log('✅ [Búsqueda] Usando cambiarClienteSinRecargar para mantener la llamada activa');
            console.log('✅ [Búsqueda] Llamando cambiarClienteSinRecargar con ID:', idNumerico);
            window.cambiarClienteSinRecargar(idNumerico);
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                cerrarModalBuscarCliente();
                cerrarModalObservaciones();
                cerrarModalAgregarInformacion();
            }
        });

        /**
         * Funciones para el modal de agregar información
         */
        function abrirModalAgregarInformacion() {
            const modal = document.getElementById('modalAgregarInformacion');
            if (modal) {
                modal.classList.add('active');
                // Limpiar formulario
                document.getElementById('formAgregarInformacion').reset();
                // Resetear a un solo campo de teléfono
                const container = document.getElementById('telefonosContainer');
                container.innerHTML = `
                    <div class="telefono-item">
                        <input type="tel" 
                               class="telefono-input" 
                               placeholder="Número de teléfono"
                               pattern="[0-9+\-\s()]+"
                               autocomplete="off">
                        <button type="button" class="btn-remove-telefono" onclick="eliminarTelefono(this)" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            }
        }

        function cerrarModalAgregarInformacion() {
            const modal = document.getElementById('modalAgregarInformacion');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        function agregarCampoTelefono() {
            const container = document.getElementById('telefonosContainer');
            const nuevoItem = document.createElement('div');
            nuevoItem.className = 'telefono-item';
            nuevoItem.innerHTML = `
                <input type="tel" 
                       class="telefono-input" 
                       placeholder="Número de teléfono"
                       pattern="[0-9+\-\s()]+"
                       autocomplete="off">
                <button type="button" class="btn-remove-telefono" onclick="eliminarTelefono(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(nuevoItem);

            // Mostrar botones de eliminar si hay más de un campo
            actualizarVisibilidadBotonesEliminar();
        }

        function eliminarTelefono(button) {
            const item = button.closest('.telefono-item');
            if (item) {
                item.remove();
                actualizarVisibilidadBotonesEliminar();
            }
        }

        function actualizarVisibilidadBotonesEliminar() {
            const container = document.getElementById('telefonosContainer');
            const items = container.querySelectorAll('.telefono-item');
            items.forEach((item, index) => {
                const btn = item.querySelector('.btn-remove-telefono');
                if (btn) {
                    // Mostrar botón solo si hay más de un campo
                    btn.style.display = items.length > 1 ? 'flex' : 'none';
                }
            });
        }

        async function guardarInformacionCliente(event) {
            event.preventDefault();

            const clienteId = document.getElementById('inputClienteId')?.value ||
                document.querySelector('input[name="cliente_id"]')?.value;

            if (!clienteId) {
                alert('Error: No se encontró el ID del cliente.');
                return;
            }

            // Obtener correo
            const emailInput = document.getElementById('nuevoEmail');
            const email = emailInput ? emailInput.value.trim() : '';

            // Obtener teléfonos
            const telefonosInputs = document.querySelectorAll('#telefonosContainer .telefono-input');
            const telefonos = Array.from(telefonosInputs)
                .map(input => input.value.trim())
                .filter(tel => tel !== '');

            // Validar que haya al menos correo o teléfono
            if (!email && telefonos.length === 0) {
                alert('Por favor, ingresa al menos un correo o un teléfono.');
                return;
            }

            // Validar formato de email si se proporciona
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert('Por favor, ingresa un correo electrónico válido.');
                return;
            }

            // Preparar datos para enviar
            const datosEnviar = {
                cliente_id: clienteId,
                telefonos: telefonos
            };

            // SIEMPRE incluir email si tiene valor (para reemplazar el existente)
            // Si el email está vacío, no incluirlo en el objeto para que no se actualice
            if (email && email.length > 0) {
                datosEnviar.email = email;
                console.log('📧 [Agregar Info] Email incluido en datos a enviar:', email);
            } else {
                console.log('📧 [Agregar Info] Email vacío, no se incluirá en la actualización');
            }

            console.log('💾 [Agregar Info] Guardando información:', datosEnviar);
            console.log('📧 [Agregar Info] Email capturado:', email, '(longitud:', email.length + ')');

            try {
                const response = await fetch('index.php?action=agregar_informacion_cliente', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(datosEnviar)
                });

                console.log('📥 [Agregar Info] Respuesta recibida. Status:', response.status);

                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('❌ [Agregar Info] Respuesta no es JSON:', text.substring(0, 200));
                    throw new Error('La respuesta del servidor no es válida.');
                }

                const data = await response.json();
                console.log('📊 [Agregar Info] Datos recibidos:', data);

                if (data.success) {
                    alert('Información guardada exitosamente.');
                    cerrarModalAgregarInformacion();

                    // Actualizar solo correo y teléfonos sin recargar toda la información
                    await actualizarCorreoYTelefonos(email, telefonos);
                } else {
                    console.error('❌ [Agregar Info] Error:', data.message);
                    alert(data.message || 'Error al guardar la información.');
                }
            } catch (error) {
                console.error('❌ [Agregar Info] Error al guardar información:', error);
                alert('Error al guardar la información: ' + error.message);
            }
        }

        /**
         * Actualizar solo el correo y teléfonos en la vista sin recargar la página
         * @param {string} nuevoEmail - Nuevo correo electrónico (puede ser vacío)
         * @param {Array} nuevosTelefonos - Array de nuevos teléfonos
         */
        async function actualizarCorreoYTelefonos(nuevoEmail, nuevosTelefonos) {
            console.log('🔄 [Actualizar Info] Actualizando correo y teléfonos en la vista...');

            const panelCliente = document.querySelector('.panel-cliente');
            if (!panelCliente) {
                console.warn('⚠️ [Actualizar Info] Panel cliente no encontrado');
                return;
            }

            // Actualizar correo
            const infoItems = panelCliente.querySelectorAll('.cliente-info-item');
            let emailItem = null;

            // Buscar el item que contiene "Correo"
            infoItems.forEach(item => {
                const strong = item.querySelector('strong');
                if (strong && strong.textContent.includes('Correo')) {
                    emailItem = item;
                }
            });

            if (nuevoEmail && nuevoEmail.trim() !== '') {
                // Si hay email nuevo, actualizar o crear el elemento
                if (emailItem) {
                    // Actualizar el email existente
                    const emailSpan = emailItem.querySelector('span');
                    if (emailSpan) {
                        emailSpan.textContent = nuevoEmail;
                        console.log('✅ [Actualizar Info] Email actualizado:', nuevoEmail);
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
                            <span>${escapeHtml(nuevoEmail)}</span>
                        `;
                        cedulaItem.parentNode.insertBefore(emailItem, cedulaItem.nextSibling);
                        console.log('✅ [Actualizar Info] Email creado:', nuevoEmail);
                    }
                }
            } else {
                // Si el email está vacío, ocultar el elemento (no eliminar)
                if (emailItem) {
                    emailItem.style.display = 'none';
                    console.log('⚠️ [Actualizar Info] Email vacío, elemento ocultado');
                }
            }

            // Actualizar teléfonos
            if (nuevosTelefonos && nuevosTelefonos.length > 0) {
                // Obtener datos actuales del cliente para combinar con los nuevos teléfonos
                const clienteId = document.getElementById('inputClienteId')?.value ||
                    document.querySelector('input[name="cliente_id"]')?.value;

                if (clienteId) {
                    try {
                        // Obtener datos actualizados del cliente
                        const response = await fetch(`index.php?action=obtener_datos_cliente&id=${encodeURIComponent(clienteId)}`);
                        const contentType = response.headers.get('content-type') || '';

                        if (contentType.includes('application/json')) {
                            const data = await response.json();

                            if (data.success && data.cliente) {
                                // Actualizar teléfonos usando la función renderTelefonos
                                if (typeof window.renderTelefonos === 'function') {
                                    window.renderTelefonos(data.cliente);
                                    console.log('✅ [Actualizar Info] Teléfonos actualizados');
                                } else {
                                    console.warn('⚠️ [Actualizar Info] renderTelefonos no disponible');
                                }
                            }
                        }
                    } catch (error) {
                        console.error('❌ [Actualizar Info] Error al obtener datos del cliente:', error);
                    }
                }
            }

            console.log('✅ [Actualizar Info] Actualización completada');
        }

        // Exponer funciones globalmente
        window.abrirModalAgregarInformacion = abrirModalAgregarInformacion;
        window.cerrarModalAgregarInformacion = cerrarModalAgregarInformacion;
        window.agregarCampoTelefono = agregarCampoTelefono;
        window.eliminarTelefono = eliminarTelefono;
        window.guardarInformacionCliente = guardarInformacionCliente;
        window.actualizarCorreoYTelefonos = actualizarCorreoYTelefonos;

        /**
         * Mostrar observaciones de una gestión en un modal
         * @param {number} gestionId - ID de la gestión
         */
        async function mostrarObservacionesGestion(gestionId) {
            console.log('👁️ [Observaciones] mostrarObservacionesGestion llamado con ID:', gestionId, '(tipo:', typeof gestionId + ')');

            // Validar ID
            if (!gestionId || gestionId === 0 || gestionId === '0' || gestionId === 'undefined' || gestionId === 'null') {
                console.error('❌ [Observaciones] ID de gestión inválido:', gestionId);
                alert('Error: ID de gestión inválido. Por favor, intenta nuevamente.');
                return;
            }

            const modal = document.getElementById('modalObservaciones');
            const contenido = document.getElementById('observacionesContenido');

            if (!modal) {
                console.error('❌ [Observaciones] ERROR: Modal #modalObservaciones no encontrado en el DOM');
                alert('Error: No se encontró el modal de observaciones. Por favor, recarga la página.');
                return;
            }

            if (!contenido) {
                console.error('❌ [Observaciones] ERROR: Contenedor #observacionesContenido no encontrado en el DOM');
                alert('Error: No se encontró el contenedor de observaciones. Por favor, recarga la página.');
                return;
            }

            console.log('✅ [Observaciones] Modal y contenedor encontrados');

            // Mostrar modal con loading
            modal.classList.add('active');
            console.log('✅ [Observaciones] Modal activado (clase "active" agregada)');

            // Verificar que el modal se muestra
            const modalStyles = window.getComputedStyle(modal);
            console.log('📋 [Observaciones] Estilos del modal - display:', modalStyles.display, 'z-index:', modalStyles.zIndex);

            contenido.innerHTML = `
                <div class="loading-observaciones">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando observaciones...</p>
                </div>
            `;

            try {
                console.log(`📡 [Observaciones] Haciendo fetch a: index.php?action=obtener_detalles_gestion&id=${gestionId}`);

                const response = await fetch(`index.php?action=obtener_detalles_gestion&id=${encodeURIComponent(gestionId)}`);

                console.log(`📥 [Observaciones] Respuesta recibida. Status: ${response.status} ${response.statusText}`);

                // Verificar Content-Type
                const contentType = response.headers.get('content-type') || '';
                console.log(`📋 [Observaciones] Content-Type: ${contentType}`);

                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('❌ [Observaciones] ERROR: Respuesta no es JSON. Preview:', text.substring(0, 300));
                    console.error('❌ [Observaciones] Respuesta completa (primeros 500 caracteres):', text.substring(0, 500));

                    contenido.innerHTML = `
                        <div class="error-observaciones">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Error al cargar las observaciones. El servidor no devolvió JSON.</p>
                            <p style="font-size: 12px; margin-top: 10px; color: #6b7280; text-align: left; max-width: 100%; overflow: auto;">
                                <strong>Detalles:</strong><br>
                                Status: ${response.status} ${response.statusText}<br>
                                Content-Type: ${contentType || 'No especificado'}<br>
                                <strong>Respuesta:</strong><br>
                                <code style="font-size: 11px; word-break: break-all;">${escapeHtml(text.substring(0, 300))}</code>
                            </p>
                        </div>
                    `;
                    return;
                }

                const data = await response.json();
                console.log('✅ [Observaciones] JSON parseado correctamente');
                console.log('📊 [Observaciones] Datos recibidos:', data);

                if (data.success && data.gestion) {
                    const gestion = data.gestion;
                    const fecha = gestion.fecha_gestion || 'N/A';
                    const asesor = gestion.asesor_nombre || 'N/A';
                    const tipificacion = gestion.tipificacion_completa || gestion.resultado || gestion.tipo_gestion || 'N/A';

                    // Obtener comentarios - verificar múltiples campos posibles
                    let comentarios = gestion.comentarios || gestion.observaciones || '';

                    console.log('📝 [Observaciones] Comentarios encontrados:', comentarios ? 'Sí' : 'No', comentarios.length, 'caracteres');
                    console.log('📝 [Observaciones] Preview comentarios:', comentarios.substring(0, 100));

                    // Si está vacío o es null, mostrar mensaje
                    if (!comentarios || comentarios.trim() === '') {
                        comentarios = 'No hay observaciones registradas para esta gestión.';
                        console.log('⚠️ [Observaciones] Comentarios vacíos, usando mensaje por defecto');
                    }

                    contenido.innerHTML = `
                        <div class="observaciones-detalle">
                            <div class="observaciones-info">
                                <div class="info-item">
                                    <strong><i class="fas fa-calendar-alt"></i> Fecha y Hora:</strong>
                                    <span>${escapeHtml(fecha)}</span>
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-user"></i> Asesor:</strong>
                                    <span>${escapeHtml(asesor)}</span>
                                </div>
                                <div class="info-item">
                                    <strong><i class="fas fa-tag"></i> Tipificación:</strong>
                                    <span class="tipificacion-badge">${escapeHtml(tipificacion)}</span>
                                </div>
                            </div>
                            <div class="observaciones-texto">
                                <strong><i class="fas fa-comment-dots"></i> Observaciones:</strong>
                                <div class="comentarios-contenido">${escapeHtml(comentarios).replace(/\n/g, '<br>')}</div>
                            </div>
                        </div>
                    `;

                    console.log('✅ [Observaciones] Modal actualizado con datos de la gestión');
                } else {
                    console.error('❌ [Observaciones] ERROR: Datos no válidos. Message:', data.message);
                    contenido.innerHTML = `
                        <div class="error-observaciones">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>${data.message || 'No se pudieron cargar las observaciones.'}</p>
                            <p style="font-size: 12px; margin-top: 10px; color: #6b7280;">
                                <strong>Detalles del error:</strong><br>
                                ${escapeHtml(JSON.stringify(data, null, 2))}
                            </p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('❌ [Observaciones] ERROR al cargar observaciones:', error);
                console.error('❌ [Observaciones] Stack trace:', error.stack);
                contenido.innerHTML = `
                    <div class="error-observaciones">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error al cargar las observaciones. Por favor, intenta nuevamente.</p>
                        <p style="font-size: 12px; margin-top: 10px; color: #6b7280;">
                            <strong>Error:</strong> ${escapeHtml(error.message)}<br>
                            <strong>Tipo:</strong> ${error.name || 'Error desconocido'}
                        </p>
                    </div>
                `;
            }
        }

        /**
         * Cerrar modal de observaciones
         */
        function cerrarModalObservaciones() {
            const modal = document.getElementById('modalObservaciones');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // Función helper para escapar HTML (global)
        window.escapeHtml = function (text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        /**
         * Actualizar el historial del cliente actual sin recargar la página
         * Usa el mismo endpoint que obtenerDatosCliente para mantener consistencia
         */
        async function actualizarHistorialCliente() {
            try {
                // Obtener el ID del cliente actual
                const clienteId = document.getElementById('inputClienteId')?.value ||
                    document.querySelector('input[name="cliente_id"]')?.value;

                if (!clienteId) {
                    console.warn('⚠️ [Historial] No se encontró ID de cliente para actualizar historial');
                    return;
                }

                console.log('🔄 [Historial] Actualizando historial del cliente:', clienteId);

                // Obtener historial usando el endpoint del controlador
                const response = await fetch(`index.php?action=obtener_historial_cliente&id=${encodeURIComponent(clienteId)}`);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status} ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('❌ [Historial] Respuesta no es JSON:', text.substring(0, 200));
                    return;
                }

                const data = await response.json();

                if (data.success && data.historial) {
                    // Usar la función renderHistorialLlamadas de asesor-gestionar.js si está disponible
                    if (typeof window.renderHistorialLlamadas === 'function') {
                        window.renderHistorialLlamadas(data.historial);
                        console.log('✅ [Historial] Historial actualizado usando renderHistorialLlamadas');
                    } else {
                        // Fallback: actualizar manualmente si la función no está disponible
                        console.warn('⚠️ [Historial] renderHistorialLlamadas no disponible, usando actualización manual');
                        actualizarHistorialManual(data.historial);
                    }
                } else {
                    console.warn('⚠️ [Historial] No se pudo obtener el historial:', data.message || 'Error desconocido');
                }
            } catch (error) {
                console.error('❌ [Historial] Error al actualizar historial:', error);
                // No mostrar alerta para no interrumpir el flujo del usuario
            }
        }

        /**
         * Actualizar el historial manualmente (fallback si renderHistorialLlamadas no está disponible)
         */
        function actualizarHistorialManual(historial) {
            const container = document.getElementById('historialLlamadasLista');
            if (!container) {
                console.error('❌ [Historial] No se encontró el contenedor #historialLlamadasLista');
                return;
            }

            if (!Array.isArray(historial) || historial.length === 0) {
                container.innerHTML = `
                    <div class="historial-vacio">
                        <i class="fas fa-info-circle"></i>
                        <p>No hay historial de llamadas registrado para este cliente.</p>
                    </div>
                `;
                return;
            }

            const formatearPesos = (valor) => {
                if (!valor || valor === 0) return 'N/A';
                return '$' + Number(valor).toLocaleString('es-CO');
            };

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

                // Verificar si es acuerdo de pago
                const esAcuerdoPago = (g.resultado === '03') || (tipificacion.includes('ACUERDO DE PAGO'));
                const numeroObligacion = g.numero_obligacion || '';
                const valorAcuerdo = g.valor_acuerdo || null;
                const valorCuota = g.valor_cuota || null;
                const numeroCuota = g.numero_cuota || null;

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

        /**
         * Cerrar sesión del PBX antes de navegar o cerrar la página
         * @param {string} targetUrl - URL de destino (opcional)
         */
        async function cerrarSesionPBX(targetUrl = null) {
            console.log('📴 [PBX] Cerrando sesión del PBX...');

            if (window.webrtcSoftphone && typeof window.webrtcSoftphone.disconnect === 'function') {
                try {
                    // Cerrar llamada activa si existe
                    if (window.webrtcSoftphone.currentCall) {
                        console.log('📞 [PBX] Colgando llamada activa antes de desconectar...');
                        window.webrtcSoftphone.hangup();
                        // Esperar un momento para que la llamada se cierre
                        await new Promise(resolve => setTimeout(resolve, 500));
                    }

                    // Desconectar del PBX
                    window.webrtcSoftphone.disconnect();
                    console.log('✅ [PBX] Sesión del PBX cerrada correctamente');

                    // Esperar un momento para que el unregister se complete
                    await new Promise(resolve => setTimeout(resolve, 300));
                } catch (error) {
                    console.error('❌ [PBX] Error al cerrar sesión del PBX:', error);
                }
            } else {
                console.log('ℹ️ [PBX] No hay sesión del PBX activa para cerrar');
            }

            // Si hay URL de destino, navegar después de cerrar la sesión
            if (targetUrl) {
                window.location.href = targetUrl;
            }
        }

        /**
         * Interceptar navegación para cerrar sesión del PBX
         */
        function setupPBXLogout() {
            // Interceptar clics en enlaces del navbar
            document.addEventListener('click', function (event) {
                const link = event.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href) return;

                // Ignorar enlaces que no son del navbar o que son la misma página
                if (!link.closest('.top-navbar')) return;

                // Ignorar enlaces que no cambian de página (hash, javascript, etc.)
                if (href.startsWith('#') || href.startsWith('javascript:') || href === '') return;

                // Ignorar si es la página actual (gestionar_cliente)
                const currentUrl = window.location.href;
                if (href.includes('gestionar_cliente') && currentUrl.includes('gestionar_cliente')) {
                    return;
                }

                // Interceptar el clic
                event.preventDefault();
                console.log('🔗 [PBX] Navegación detectada a:', href);

                // Cerrar sesión del PBX antes de navegar
                cerrarSesionPBX(href);
            }, true); // Usar capture phase para interceptar antes que otros handlers

            // Interceptar clic en botón de logout
            document.addEventListener('click', function (event) {
                const link = event.target.closest('a.logout-btn');
                if (!link) return;

                const href = link.getAttribute('href');
                if (href && href.includes('logout')) {
                    event.preventDefault();
                    console.log('🚪 [PBX] Logout detectado, cerrando sesión del PBX...');

                    // Cerrar sesión del PBX antes de hacer logout
                    cerrarSesionPBX(href);
                }
            }, true);

            // Interceptar cierre de pestaña/ventana
            window.addEventListener('beforeunload', function (event) {
                console.log('📴 [PBX] Página cerrando, cerrando sesión del PBX...');

                // Cerrar sesión del PBX de forma síncrona (no podemos usar async aquí)
                if (window.webrtcSoftphone && typeof window.webrtcSoftphone.disconnect === 'function') {
                    try {
                        // Colgar llamada activa si existe
                        if (window.webrtcSoftphone.currentCall) {
                            window.webrtcSoftphone.hangup();
                        }

                        // Desconectar
                        window.webrtcSoftphone.disconnect();
                        console.log('✅ [PBX] Sesión del PBX cerrada antes de cerrar la página');
                    } catch (error) {
                        console.error('❌ [PBX] Error al cerrar sesión del PBX:', error);
                    }
                }
            });

            console.log('✅ [PBX] Listeners de cierre de sesión configurados');
        }

        // Configurar cierre de sesión cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupPBXLogout);
        } else {
            setupPBXLogout();
        }

        // Hacer funciones globales para que estén disponibles desde onclick
        window.mostrarObservacionesGestion = mostrarObservacionesGestion;
        window.cerrarModalObservaciones = cerrarModalObservaciones;
        window.actualizarHistorialCliente = actualizarHistorialCliente;
        window.cerrarSesionPBX = cerrarSesionPBX;

        // Guardar tipificación SIN RECARGAR LA PÁGINA (para mantener la llamada activa)
        document.getElementById('tipificacionForm')?.addEventListener('submit', async function (e) {
            e.preventDefault();
            e.stopPropagation();

            console.log('💾 [Formulario] Guardando tipificación sin recargar página...');

            // Detener cronómetro y obtener duración
            if (typeof detenerCronometroGestion === 'function') {
                const duracionSegundos = detenerCronometroGestion();
                const duracionMinutos = duracionSegundos > 0 ? (duracionSegundos / 60) : 0;

                // Guardar duración en el campo hidden
                const duracionInput = document.getElementById('duracion_llamada_hidden');
                if (duracionInput) {
                    duracionInput.value = duracionMinutos.toFixed(2);
                    console.log('⏱️ [Formulario] Duración de gestión:', duracionMinutos.toFixed(2), 'minutos (', duracionSegundos, 'segundos)');
                }
            }

            // Verificar si hay llamada activa
            let hayLlamada = false;
            if (window.webrtcSoftphone && window.webrtcSoftphone.currentCall) {
                const call = window.webrtcSoftphone.currentCall;
                const state = call.state;
                const stateStr = String(state);
                hayLlamada = stateStr === 'Established' || stateStr === '4' || state === 'Established';

                if (hayLlamada) {
                    console.log('📞 [Formulario] Hay llamada activa - Guardando sin recargar para mantener la llamada');
                }
            }

            // Validación adicional: Si es acuerdo de pago, verificar que la obligación no sea "ninguna"
            const subTipificacion = document.getElementById('sub_tipificacion_hidden').value;
            const obligacionId = document.getElementById('obligacion_seleccionada').value;

            if (subTipificacion === '03') {
                if (!obligacionId || obligacionId === 'ninguna' || obligacionId === '') {
                    alert('Para registrar un acuerdo de pago, debe seleccionar una obligación.');
                    return;
                }
            }

            const formData = new FormData(this);
            const btnGuardar = document.getElementById('btnGuardarPrincipal');

            // Deshabilitar botón mientras se guarda
            if (btnGuardar) {
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            }

            try {
                const response = await fetch('index.php?action=guardar_tipificacion', {
                    method: 'POST',
                    body: formData
                });

                // Verificar Content-Type
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('❌ [Formulario] Respuesta no es JSON:', text.substring(0, 200));
                    throw new Error('La respuesta del servidor no es válida. Por favor, recarga la página.');
                }

                const data = await response.json();

                if (data.success) {
                    console.log('✅ [Formulario] Gestión guardada exitosamente');

                    // Mostrar mensaje de éxito
                    alert('Gestión guardada exitosamente');

                    // Actualizar el historial dinámicamente después de guardar
                    await actualizarHistorialCliente();

                    // NUNCA recargar la página si hay llamada activa
                    if (hayLlamada) {
                        console.log('📞 [Formulario] Llamada activa detectada - NO recargando página');
                        resetearFormularioParaNuevoCliente();
                    } else if (data.redirect_url) {
                        // Solo redirigir si NO hay llamada activa y hay redirect_url
                        console.log('🔄 [Formulario] Redirigiendo a:', data.redirect_url);
                        window.location.href = data.redirect_url;
                        return;
                    } else {
                        resetearFormularioParaNuevoCliente();
                    }
                } else {
                    console.error('❌ [Formulario] Error al guardar:', data.message);
                    alert(data.message || 'Error al guardar la gestión');
                    // Reiniciar cronómetro si hay error
                    if (typeof iniciarCronometroGestion === 'function') {
                        iniciarCronometroGestion();
                    }
                }
            } catch (error) {
                console.error('❌ [Formulario] Error al guardar tipificación:', error);
                alert('Error al guardar la gestión: ' + error.message);
                // Reiniciar cronómetro si hay error
                if (typeof iniciarCronometroGestion === 'function') {
                    iniciarCronometroGestion();
                }
            } finally {
                // Rehabilitar botón
                if (btnGuardar) {
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<i class="fas fa-save"></i> Guardar Gestión';
                }
            }
        });
    </script>
</body>

</html>