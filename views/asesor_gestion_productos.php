<?php
// Archivo: views/asesor_gestion_productos.php
// Interfaz de gestión de productos para asesores

// Nota: Esta vista espera que el controlador le pase `$productos` y `$cliente`.
// Evitamos usar `$pdo` o acceder a modelos directamente desde la vista.
$productos = $productos ?? [];
$cliente = $cliente ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - <?php echo $cliente['nombre'] ?? 'Cliente'; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .main-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Panel principal */
        .main-panel {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        /* Panel lateral de productos */
        .products-panel {
            width: 400px;
            background: white;
            border-left: 1px solid #e0e6ed;
            padding: 20px;
            overflow-y: auto;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e6ed;
        }

        .panel-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .btn-add-product {
            background: #27ae60;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-add-product:hover {
            background: #229954;
            transform: translateY(-1px);
        }

        /* Información del cliente */
        .client-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }

        .client-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .client-details {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Lista de productos */
        .products-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .product-item {
            background: white;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .product-item:hover {
            border-color: #3498db;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.1);
        }

        .product-item.selected {
            border-color: #3498db;
            background: #f0f8ff;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.2);
        }

        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .product-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }

        .product-value {
            font-weight: 600;
            color: #27ae60;
            font-size: 1.1rem;
        }

        .product-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pendiente {
            background: #fff3cd;
            color: #856404;
        }

        .status-pagado {
            background: #d4edda;
            color: #155724;
        }

        .status-rechazado {
            background: #f8d7da;
            color: #721c24;
        }

        .product-details {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-top: 8px;
        }

        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-classify {
            background: #3498db;
            color: white;
        }

        .btn-classify:hover {
            background: #2980b9;
        }

        .btn-view-history {
            background: #95a5a6;
            color: white;
        }

        .btn-view-history:hover {
            background: #7f8c8d;
        }

        /* Modal de clasificación */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e6ed;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-textarea {
            height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-save {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            margin-right: 10px;
        }

        .btn-save:hover {
            background: #229954;
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
        }

        /* Botones de acción flexibles */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-decline-all {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-decline-all:hover {
            background: #c0392b;
        }

        .btn-continue {
            background: #f39c12;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-continue:hover {
            background: #e67e22;
        }

        .btn-finish {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-finish:hover {
            background: #229954;
        }

        /* Historial de gestiones */
        .history-item {
            background: #f8f9fa;
            border: 1px solid #e0e6ed;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .history-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .history-result {
            font-weight: 600;
            color: #2c3e50;
        }

        .history-comments {
            color: #555;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            
            .products-panel {
                width: 100%;
                height: 50vh;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Panel principal -->
        <div class="main-panel">
            <div class="panel-header">
                <h1 class="panel-title">Gestión de Productos</h1>
                <button class="btn-add-product" onclick="abrirModalAgregarProducto()">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
            </div>

            <?php if ($cliente): ?>
            <div class="client-info">
                <div class="client-name"><?php echo htmlspecialchars($cliente['nombre']); ?></div>
                <div class="client-details">
                    Cédula: <?php echo htmlspecialchars($cliente['cedula']); ?> | 
                    Teléfono: <?php echo htmlspecialchars($cliente['telefono']); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Botones de acción flexibles -->
            <div class="action-buttons">
                <button class="btn-decline-all" onclick="declinarTodosProductos()">
                    <i class="fas fa-times"></i> Cliente Rechaza Todos los Productos
                </button>
                <button class="btn-continue" onclick="continuarGestion()" style="display: none;">
                    <i class="fas fa-arrow-right"></i> Continuar con Otro Producto
                </button>
                <button class="btn-finish" onclick="finalizarGestion()" style="display: none;">
                    <i class="fas fa-check"></i> Finalizar Gestión
                </button>
            </div>

            <!-- Área de clasificación actual -->
            <div id="classification-area" style="display: none;">
                <h3>Clasificación del Producto Seleccionado</h3>
                <div id="current-product-info"></div>
                <div id="classification-form"></div>
            </div>
        </div>

        <!-- Panel lateral de productos -->
        <div class="products-panel">
            <h3 class="panel-title">Productos del Cliente</h3>
            
            <div class="products-list">
                <?php if (empty($productos)): ?>
                    <div style="text-align: center; color: #7f8c8d; padding: 20px;">
                        <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 10px;"></i>
                        <p>No hay productos registrados para este cliente</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($productos as $producto): ?>
                    <div class="product-item" data-product-id="<?php echo $producto['id']; ?>">
                        <div class="product-header">
                            <div class="product-name"><?php echo htmlspecialchars($producto['nombre_producto']); ?></div>
                            <div class="product-value">$<?php echo number_format($producto['valor_producto'], 0, ',', '.'); ?></div>
                        </div>
                        
                        <div class="product-status status-<?php echo $producto['estado_producto']; ?>">
                            <?php echo ucfirst($producto['estado_producto']); ?>
                        </div>
                        
                        <div class="product-details">
                            <?php if (!empty($producto['comentarios'])): ?>
                                <p><strong>Comentarios:</strong> <?php echo htmlspecialchars($producto['comentarios'] ?? ''); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($producto['ultima_gestion'])): ?>
                                <p><strong>Última gestión:</strong> <?php echo date('d/m/Y H:i', strtotime($producto['ultima_gestion'])); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn-action btn-classify" onclick="seleccionarProducto(<?php echo $producto['id']; ?>)">
                                <i class="fas fa-edit"></i> Clasificar
                            </button>
                            <button class="btn-action btn-view-history" onclick="verHistorial(<?php echo $producto['id']; ?>)">
                                <i class="fas fa-history"></i> Historial
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de clasificación -->
    <div id="classificationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Clasificar Producto</h2>
                <span class="close" onclick="cerrarModal()">&times;</span>
            </div>
            
            <form id="classificationForm">
                <input type="hidden" id="productId" name="product_id">
                
                <div class="form-group">
                    <label class="form-label">Tipo de Gestión</label>
                    <select class="form-select" id="tipoGestion" name="tipo_gestion" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="hacer_llamada">Hacer Llamada</option>
                        <option value="recibir_llamada">Recibir Llamada</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Resultado de la Gestión</label>
                    <select class="form-select" id="resultadoGestion" name="resultado_gestion" required>
                        <option value="">Seleccionar resultado</option>
                        <option value="01">01. CANCELADA</option>
                        <option value="02">02. MEMORANDO CNC</option>
                        <option value="03">03. ACUERDO DE PAGO</option>
                        <option value="04">04. PAGO TOTAL</option>
                        <option value="05">05. YA PAGO</option>
                        <option value="06">06. PROMESA</option>
                        <option value="06.1">06.1 BANNER</option>
                        <option value="06.2">06.2 REFINANCIACION</option>
                        <option value="06.3">06.3 UNIFICACION</option>
                        <option value="06.4">06.4 NIVELACION O NORMALIZACION</option>
                        <option value="07">07. REPORTE DE PAGO</option>
                        <option value="08">08. ABONOS</option>
                        <option value="09">09. NEGOCIACION EN TRAMITE</option>
                        <option value="10">10. SEGUIM GESTION</option>
                        <option value="11">11. SEGUIMIENTO</option>
                        <option value="12">12. RENUENTE</option>
                        <option value="13">13. VOLUNTAD DE PAGO</option>
                        <option value="14">14. VOLVER A LLAMAR</option>
                        <option value="14.1">14.1 VOLVER A LLAMAR HOY</option>
                        <option value="15">15. LOCALIZADO</option>
                        <option value="16">16. CONTACTO CON TERCERO</option>
                        <option value="17">17. FALLECIDO</option>
                        <option value="18">18. QUEJA / RECLAMO</option>
                        <option value="19">19. NO CONTESTAN</option>
                        <option value="20">20. ACTUALIZACION DATOS</option>
                        <option value="21">21. MENSAJE</option>
                        <option value="22">22. CORREO-E</option>
                        <option value="23">23. LEY DE INSOLVENCIA</option>
                        <option value="24">24. NO LOCALIZADO</option>
                        <option value="25">25. NUMERO EQUIVOCADO</option>
                        <option value="26">26. WHATSAPP</option>
                        <option value="27">27. ABANDONO CHAT</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Monto Gestionado</label>
                        <input type="number" class="form-input" id="montoGestion" name="monto_gestion" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Pago</label>
                        <input type="date" class="form-input" id="fechaPago" name="fecha_pago">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Comentarios</label>
                    <textarea class="form-textarea" id="comentarios" name="comentarios" placeholder="Detalles de la gestión..."></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Próxima Gestión</label>
                    <input type="datetime-local" class="form-input" id="proximaGestion" name="proxima_gestion">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Canales Autorizados</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <label><input type="checkbox" name="canales[]" value="llamada"> Llamada</label>
                        <label><input type="checkbox" name="canales[]" value="correo_electronico"> Correo Electrónico</label>
                        <label><input type="checkbox" name="canales[]" value="sms"> SMS</label>
                        <label><input type="checkbox" name="canales[]" value="correo_fisico"> Correo Físico</label>
                        <label><input type="checkbox" name="canales[]" value="mensajeria_aplicaciones"> Mensajería por Aplicaciones</label>
                    </div>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-save">Guardar Clasificación</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de historial -->
    <div id="historyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Historial de Gestiones</h2>
                <span class="close" onclick="cerrarHistorialModal()">&times;</span>
            </div>
            <div id="historyContent"></div>
        </div>
    </div>

    <script>
        let productoSeleccionado = null;
        let productosGestionados = [];

        function seleccionarProducto(productoId) {
            // Remover selección anterior
            document.querySelectorAll('.product-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            // Seleccionar producto actual
            const productoItem = document.querySelector(`[data-product-id="${productoId}"]`);
            productoItem.classList.add('selected');
            
            productoSeleccionado = productoId;
            
            // Mostrar área de clasificación
            document.getElementById('classification-area').style.display = 'block';
            
            // Cargar información del producto
            cargarInformacionProducto(productoId);
        }

        function cargarInformacionProducto(productoId) {
            // Aquí se cargaría la información del producto via AJAX
            // Por ahora mostramos un placeholder
            document.getElementById('current-product-info').innerHTML = `
                <div class="product-item">
                    <div class="product-header">
                        <div class="product-name">Producto ID: ${productoId}</div>
                    </div>
                </div>
            `;
        }

        // TODO: Implementar modal para agregar nuevo producto
        function abrirModalAgregarProducto() {
            console.warn('Función abrirModalAgregarProducto() pendiente de implementación');
        }

        function verHistorial(productoId) {
            // Implementar modal de historial
            document.getElementById('historyModal').style.display = 'block';
            document.getElementById('historyContent').innerHTML = `
                <div class="history-item">
                    <div class="history-header">
                        <span class="history-date">15/01/2024 10:30</span>
                        <span class="history-result">PROMESA</span>
                    </div>
                    <div class="history-comments">
                        Cliente prometió pagar en 15 días. Se acordó seguimiento.
                    </div>
                </div>
            `;
        }

        function cerrarHistorialModal() {
            document.getElementById('historyModal').style.display = 'none';
        }

        function cerrarModal() {
            document.getElementById('classificationModal').style.display = 'none';
        }

        function declinarTodosProductos() {
            if (confirm('¿Está seguro de que el cliente rechaza todos los productos?')) {
                // Implementar lógica para declinar todos los productos
                alert('Todos los productos han sido marcados como rechazados');
                productosGestionados = [];
                mostrarOpcionesContinuacion();
            }
        }

        function continuarGestion() {
            // Limpiar selección y permitir seleccionar otro producto
            productoSeleccionado = null;
            document.querySelectorAll('.product-item').forEach(item => {
                item.classList.remove('selected');
            });
            document.getElementById('classification-area').style.display = 'none';
        }

        function finalizarGestion() {
            if (confirm('¿Finalizar la gestión de productos para este cliente?')) {
                // Implementar lógica para finalizar gestión
                alert('Gestión finalizada exitosamente');
                window.location.href = 'index.php?action=mis_clientes';
            }
        }

        function mostrarOpcionesContinuacion() {
            document.querySelector('.btn-continue').style.display = 'inline-block';
            document.querySelector('.btn-finish').style.display = 'inline-block';
        }

        // Manejar envío del formulario de clasificación
        document.getElementById('classificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const productoId = formData.get('product_id');
            
            // Aquí se enviaría la data via AJAX
            console.log('Guardando clasificación para producto:', productoId);
            
            // Simular guardado exitoso
            alert('Clasificación guardada exitosamente');
            cerrarModal();
            
            // Marcar producto como gestionado
            productosGestionados.push(productoId);
            mostrarOpcionesContinuacion();
        });

        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            const classificationModal = document.getElementById('classificationModal');
            const historyModal = document.getElementById('historyModal');
            
            if (event.target === classificationModal) {
                cerrarModal();
            }
            if (event.target === historyModal) {
                cerrarHistorialModal();
            }
        }
    </script>
</body>
</html>
