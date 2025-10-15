<?php
// Archivo: views/gestion_cargas_integrada.php
// Vista integrada para gestionar cargas de archivos CSV
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php include 'views/shared_styles.php'; ?>
    <style>
        .upload-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .upload-option {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .upload-option:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
        }
        
        .upload-option.selected {
            border-color: #007bff;
            background: #f8f9ff;
        }
        
        .upload-option h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .upload-option .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .upload-option.consolidada .icon {
            color: #28a745;
        }
        
        .upload-option.nueva .icon {
            color: #007bff;
        }
        
        .upload-option p {
            color: #666;
            margin: 0;
        }
        
        .upload-form {
            display: none;
            margin-top: 30px;
        }
        
        .upload-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        
        .file-upload-area:hover {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
        
        .file-upload-area.dragover {
            border-color: #007bff;
            background-color: #e3f2fd;
            transform: scale(1.02);
        }
        
        .file-info {
            display: none;
            background-color: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .existing-bases {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .existing-bases h4 {
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .base-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .base-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .base-item:hover {
            border-color: #007bff;
            background: #f8f9ff;
        }
        
        .base-item.selected {
            border-color: #007bff;
            background: #e3f2fd;
        }
        
        /* Estilos para el instructivo */
        .instructivo {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #007bff;
        }
        
        .instructivo h3 {
            color: #007bff;
            margin: 0 0 15px 0;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .instructivo-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-top: 20px;
        }
        
        .instructivo-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #e9ecef;
        }
        
        .instructivo-section h4 {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .campo-obligatorio {
            color: #dc3545;
            font-weight: bold;
        }
        
        .campo-opcional {
            color: #6c757d;
        }
        
        .campo-financiero {
            color: #28a745;
            font-weight: 600;
        }
        
        .orden-campos {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .orden-campos h5 {
            color: #1976d2;
            margin: 0 0 10px 0;
            font-size: 1rem;
        }
        
        .orden-campos ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .orden-campos li {
            margin-bottom: 5px;
            color: #1976d2;
        }
        
        .ejemplo-csv {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin-top: 10px;
            overflow-x: auto;
        }
        
        .ejemplo-csv .header {
            font-weight: bold;
            color: #007bff;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        
        .ejemplo-csv .row {
            margin-bottom: 5px;
        }
        
        .notas-importantes {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .notas-importantes h5 {
            color: #856404;
            margin: 0 0 10px 0;
            font-size: 1rem;
        }
        
        .notas-importantes ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .notas-importantes li {
            color: #856404;
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .instructivo-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php 
    include 'views/shared_navbar.php';
    echo getNavbar('Gestión de Cargas', $_SESSION['user_role'] ?? ''); 
    ?>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="page-description">Gestiona tus bases de datos de clientes subiendo archivos CSV</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success <?php echo isset($_SESSION['success_auto_hide']) ? 'auto-hide' : ''; ?>" 
                 <?php echo isset($_SESSION['success_auto_hide']) ? 'data-auto-hide="true"' : ''; ?>>
                <strong>Éxito:</strong> <?php echo $success_message; ?>
            </div>
            <?php unset($_SESSION['success_auto_hide']); ?>
        <?php endif; ?>

        <?php if (!empty($info_message)): ?>
            <div class="alert alert-info <?php echo isset($_SESSION['success_auto_hide']) ? 'auto-hide' : ''; ?>" 
                 <?php echo isset($_SESSION['success_auto_hide']) ? 'data-auto-hide="true"' : ''; ?>>
                <strong>Información:</strong> <?php echo $info_message; ?>
            </div>
            <?php unset($_SESSION['success_auto_hide']); ?>
        <?php endif; ?>

        <?php if (!empty($warning_message)): ?>
            <div class="alert alert-warning <?php echo isset($_SESSION['success_auto_hide']) ? 'auto-hide' : ''; ?>" 
                 <?php echo isset($_SESSION['success_auto_hide']) ? 'data-auto-hide="true"' : ''; ?>>
                <strong>Advertencia:</strong> <?php echo $warning_message; ?>
            </div>
            <?php unset($_SESSION['success_auto_hide']); ?>
        <?php endif; ?>

        <!-- Instructivo de estructura del archivo CSV -->
        <div class="instructivo">
            <h3>
                <i class="fas fa-info-circle"></i>
                📋 Instructivo: Estructura del Archivo CSV
            </h3>
            <p>Antes de subir tu archivo CSV, asegúrate de que tenga la estructura correcta. El sistema requiere ciertos campos obligatorios y acepta otros opcionales con información financiera.</p>
            
            
            
            <div class="instructivo-content">
                <div class="instructivo-section">
                    <h4>
                        <i class="fas fa-exclamation-triangle"></i>
                        Campos Obligatorios
                    </h4>
                    <ol>
                        <li><span class="campo-obligatorio">cedula</span> - Número de cédula (sin puntos ni guiones)</li>
                        <li><span class="campo-obligatorio">nombre</span> - <strong>Nombre completo del cliente</strong> (consolidado en un solo campo)</li>
                        <li><span class="campo-obligatorio">telefono</span> - Número de teléfono principal</li>
                        <li><span class="campo-obligatorio">obligacion</span> - Número de obligación financiera</li>
                    </ol>
                    
                    <h4 style="margin-top: 20px;">
                        <i class="fas fa-plus-circle"></i>
                        Campos Opcionales (Información Básica)
                    </h4>
                    <ul>
                        <li><span class="campo-opcional">celular2</span> - Número de celular secundario</li>
                        <li><span class="campo-opcional">email</span> - Correo electrónico</li>
                        <li><span class="campo-opcional">ciudad</span> - Ciudad de residencia</li>
                        <li><span class="campo-opcional">direccion</span> - Dirección completa</li>
                    </ul>
                    
                    <h4 style="margin-top: 20px;">
                        <i class="fas fa-dollar-sign"></i>
                        Campos Opcionales (Información Financiera)
                    </h4>
                    <ul>
                        <li><span class="campo-financiero">saldo_k_obligacion</span> - Saldo de la obligación en miles</li>
                        <li><span class="campo-financiero">capital_cliente</span> - Capital del cliente</li>
                        <li><span class="campo-financiero">pago_total_obligacion</span> - Pago total de la obligación</li>
                        <li><span class="campo-financiero">mora_actual</span> - Días de mora actual</li>
                        <li><span class="campo-financiero">propiedad</span> - Propiedad (FINANDINA, INCOMERCIO, etc.)</li>
                        <li><span class="campo-financiero">producto</span> - Tipo de producto (VEHICULO, LIBRE INVERSION, etc.)</li>
                        <li><span class="campo-financiero">medicion</span> - Medición/segmentación del cliente</li>
                    </ul>
                </div>
                
                <div class="instructivo-section">
                    <h4>
                        <i class="fas fa-table"></i>
                        Ejemplo de Estructura Completa
                    </h4>
                    <div class="ejemplo-csv">
                        <div class="header">OBLIGACION;CEDULA;NOMBRE;Saldo K Obl;Capital Cliente;Pago Total Obl;Mora Actual;Propiedad;Producto;MEDICION;telefono;celular2;email;ciudad</div>
                        <div class="row">1234567890;1014213070;ANA MILENA HINCAPIE GOMEZ;1500000.50;2000000.00;500000.00;30;FINANDINA;VEHICULO;10 SALDO <5MM;3124281457;3001234567;ana@email.com;Bogotá</div>
                        <div class="row">9876543210;87654321;CARLOS LOPEZ RODRIGUEZ;2500000.75;3000000.00;750000.00;15;INCOMERCIO;LIBRE INVERSION;15 SALDO 5-10MM;3009876543;3007654321;carlos@email.com;Medellín</div>
                    </div>
                    
                    <h4 style="margin-top: 20px;">
                        <i class="fas fa-file-csv"></i>
                        Formato del Archivo
                    </h4>
                    <ul>
                        <li>Formato: <strong>CSV (Comma Separated Values)</strong></li>
                        <li>Codificación: <strong>UTF-8</strong></li>
                        <li>Separador: <strong>Punto y coma (;)</strong> o <strong>Coma (,)</strong></li>
                        <li>Primera fila: <strong>Encabezados de columnas</strong></li>
                        <li>Números: <strong>Usar punto (.) como separador decimal</strong></li>
                    </ul>
                </div>
            </div>
            
            <div class="notas-importantes">
                <h5><i class="fas fa-exclamation-circle"></i> Notas Importantes</h5>
                <ul>
                    <li><strong>Campo "nombre":</strong> Debe contener el nombre completo del cliente. Si en tu base de datos tienes campos separados como "nombre" y "apellido", consolídalos en un solo campo "nombre".</li>
                    <li><strong>Campos financieros:</strong> Si están presentes en el CSV, se mostrarán automáticamente en la vista del asesor para gestionar el cliente.</li>
                    <li><strong>Orden de campos:</strong> Aunque el orden puede variar, se recomienda seguir la secuencia: obligación, cédula, nombre, teléfono, y luego los campos opcionales.</li>
                    <li><strong>Validación automática:</strong> El sistema detectará automáticamente los encabezados y mapeará los campos correctamente.</li>
                </ul>
            </div>
        </div>

        <!-- Opciones de carga -->
        <div class="upload-options">
            <div class="upload-option existente" onclick="selectUploadOption('existente')">
                <div class="icon">📁</div>
                <h3>Agregar a Base Existente</h3>
                <p>Selecciona una base de datos específica para agregar clientes</p>
            </div>
            
            <div class="upload-option nueva" onclick="selectUploadOption('nueva')">
                <div class="icon">🗄️</div>
                <h3>Crear Nueva Base de Datos</h3>
                <p>Crea una nueva base de datos independiente con nombre personalizado</p>
            </div>
        </div>

        <!-- Formulario para nueva base de datos -->
        <div class="upload-form" id="formNueva">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-database"></i> Crear Nueva Base de Datos</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?action=crear_nueva_base" enctype="multipart/form-data" id="formNuevaForm">
                        <input type="hidden" name="action" value="nueva_base">
                        
                        <div class="form-group">
                            <label for="nombre_base_datos">Nombre de la Base de Datos *</label>
                            <input type="text" id="nombre_base_datos" name="nombre_base_datos" 
                                   class="form-control" 
                                   placeholder="Ej: Clientes Enero 2024, Campaña Navidad, etc."
                                   maxlength="100" required>
                            <small class="form-help">Asigna un nombre descriptivo para identificar esta base de datos</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Archivo CSV *</label>
                            <div class="file-upload-area" id="fileUploadAreaNueva" onclick="document.getElementById('archivo_excel_nueva').click()">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h5>Arrastra y suelta tu archivo CSV aquí</h5>
                                <p class="text-muted">o haz clic para seleccionar archivo</p>
                                <small class="text-muted">Formatos soportados: .csv</small>
                            </div>
                            <input type="file" id="archivo_excel_nueva" name="archivo_excel_nueva" 
                                   accept=".csv" style="display: none;" required>
                            <div class="file-info" id="fileInfoNueva">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span id="fileNameNueva"></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFileNueva()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="index.php?action=list_cargas" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-database"></i> Crear Nueva Base de Datos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Formulario para base existente -->
        <div class="upload-form" id="formExistente">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-database"></i> Agregar a Base Existente</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php?action=agregar_a_base_existente" enctype="multipart/form-data" id="formExistenteForm">
                        <input type="hidden" name="action" value="existente">
                        <input type="hidden" name="carga_id" id="carga_id_seleccionada">
                        
                        <div class="form-group">
                            <label for="base_seleccionada">Base de Datos Seleccionada *</label>
                            <div class="selected-base-display" id="selectedBaseDisplay">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Selecciona una base de datos de la lista de abajo
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Archivo CSV *</label>
                            <div class="file-upload-area" id="fileUploadAreaExistente" onclick="document.getElementById('archivo_excel_existente').click()">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h5>Arrastra y suelta tu archivo CSV aquí</h5>
                                <p class="text-muted">o haz clic para seleccionar archivo</p>
                                <small class="text-muted">Formatos soportados: .csv</small>
                            </div>
                            <input type="file" id="archivo_excel_existente" name="archivo_excel_existente" 
                                   accept=".csv" style="display: none;" required>
                            <div class="file-info" id="fileInfoExistente">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span id="fileNameExistente"></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFileExistente()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="index.php?action=list_cargas" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success" id="btnAgregarExistente" disabled>
                                <i class="fas fa-plus"></i> Agregar Clientes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mostrar bases existentes si hay -->
        <?php if (!empty($cargas)): ?>
        <div class="existing-bases">
            <h4><i class="fas fa-database"></i> Bases de Datos Existentes</h4>
            <div class="base-list">
                <?php foreach ($cargas as $carga): ?>
                <div class="base-item" onclick="selectExistingBase(<?php echo $carga['id']; ?>, '<?php echo htmlspecialchars($carga['nombre_cargue']); ?>')">
                    <strong><?php echo htmlspecialchars($carga['nombre_cargue']); ?></strong>
                    <br>
                    <small><?php echo date('d/m/Y H:i', strtotime($carga['fecha_cargue'])); ?></small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Botón de gestión de estado de bases -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            <i class="fas fa-cogs text-primary"></i>
                            Gestión Avanzada de Bases de Datos
                        </h5>
                        <p class="card-text">
                            Controla qué bases de datos están habilitadas o deshabilitadas para su uso en el sistema.
                        </p>
                        <a href="index.php?action=gestionar_estado_bases" class="btn btn-warning" style="background-color: #ffc107; color: #212529; font-weight: bold; padding: 12px 20px; border: 2px solid #ffc107; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <i class="fas fa-cogs"></i>
                            Habilitar/Deshabilitar Bases de Datos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedOption = null;
        let selectedBaseId = null;

        function selectUploadOption(option) {
            // Remover selección anterior
            document.querySelectorAll('.upload-option').forEach(el => el.classList.remove('selected'));
            document.querySelectorAll('.upload-form').forEach(el => el.classList.remove('active'));
            
            // Seleccionar nueva opción
            document.querySelector(`.upload-option.${option}`).classList.add('selected');
            document.getElementById(`form${option.charAt(0).toUpperCase() + option.slice(1)}`).classList.add('active');
            
            selectedOption = option;
        }

        function selectExistingBase(baseId, baseName) {
            selectedBaseId = baseId;
            document.querySelectorAll('.base-item').forEach(el => el.classList.remove('selected'));
            event.target.closest('.base-item').classList.add('selected');
            
            // Actualizar el formulario de base existente
            if (selectedOption === 'existente') {
                document.getElementById('carga_id_seleccionada').value = baseId;
                document.getElementById('selectedBaseDisplay').innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Base seleccionada:</strong> ${baseName}
                    </div>
                `;
                document.getElementById('btnAgregarExistente').disabled = false;
            }
        }


        // Drag and drop para nueva base
        const fileUploadAreaNueva = document.getElementById('fileUploadAreaNueva');
        const fileInputNueva = document.getElementById('archivo_excel_nueva');
        const fileInfoNueva = document.getElementById('fileInfoNueva');
        const fileNameNueva = document.getElementById('fileNameNueva');

        fileUploadAreaNueva.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        fileUploadAreaNueva.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        fileUploadAreaNueva.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const files = e.dataTransfer.files;
            handleFilesNueva(files);
        });

        fileInputNueva.addEventListener('change', function(e) {
            handleFilesNueva(e.target.files);
        });

        function handleFilesNueva(files) {
            if (files.length > 0) {
                const file = files[0];
                displayFileInfoNueva(file);
            }
        }

        function displayFileInfoNueva(file) {
            fileNameNueva.textContent = file.name;
            fileInfoNueva.style.display = 'block';
            fileUploadAreaNueva.style.display = 'none';
        }

        function removeFileNueva() {
            fileInputNueva.value = '';
            fileInfoNueva.style.display = 'none';
            fileUploadAreaNueva.style.display = 'block';
        }

        // Drag and drop para base existente
        const fileUploadAreaExistente = document.getElementById('fileUploadAreaExistente');
        const fileInputExistente = document.getElementById('archivo_excel_existente');
        const fileInfoExistente = document.getElementById('fileInfoExistente');
        const fileNameExistente = document.getElementById('fileNameExistente');

        fileUploadAreaExistente.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        fileUploadAreaExistente.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        fileUploadAreaExistente.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const files = e.dataTransfer.files;
            handleFilesExistente(files);
        });

        fileInputExistente.addEventListener('change', function(e) {
            handleFilesExistente(e.target.files);
        });

        function handleFilesExistente(files) {
            if (files.length > 0) {
                const file = files[0];
                displayFileInfoExistente(file);
            }
        }

        function displayFileInfoExistente(file) {
            fileNameExistente.textContent = file.name;
            fileInfoExistente.style.display = 'block';
            fileUploadAreaExistente.style.display = 'none';
        }

        function removeFileExistente() {
            fileInputExistente.value = '';
            fileInfoExistente.style.display = 'none';
            fileUploadAreaExistente.style.display = 'block';
        }

        // Validación de formularios
        document.getElementById('formNuevaForm').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre_base_datos').value.trim();
            if (!nombre) {
                e.preventDefault();
                alert('Por favor ingresa un nombre para la base de datos');
                return;
            }
            if (!fileInputNueva.files.length) {
                e.preventDefault();
                alert('Por favor selecciona un archivo CSV');
                return;
            }
        });

        document.getElementById('formExistenteForm').addEventListener('submit', function(e) {
            const cargaId = document.getElementById('carga_id_seleccionada').value;
            if (!cargaId) {
                e.preventDefault();
                alert('Por favor selecciona una base de datos de la lista');
                return;
            }
            if (!fileInputExistente.files.length) {
                e.preventDefault();
                alert('Por favor selecciona un archivo CSV');
                return;
            }
        });

        // Auto-ocultar mensajes de éxito después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const successMessages = document.querySelectorAll('.alert-success, .alert-info, .alert-warning');
            successMessages.forEach(function(message) {
                // Verificar si el mensaje debe auto-ocultarse
                if (message.dataset.autoHide === 'true' || message.classList.contains('auto-hide')) {
                    setTimeout(function() {
                        message.style.transition = 'opacity 0.5s ease-out';
                        message.style.opacity = '0';
                        setTimeout(function() {
                            message.remove();
                        }, 500);
                    }, 5000); // 5 segundos
                }
            });
        });
    </script>
</body>
</html>
