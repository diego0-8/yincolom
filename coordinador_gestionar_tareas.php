<?php
// Archivo: views/coordinador_gestionar_tareas.php
// Vista para que el coordinador gestione tareas específicas para asesores
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php include 'views/shared_styles.php'; ?>
</head>
<body>
    <?php 
    include 'views/shared_navbar.php';
    echo getNavbar('Gestión de Tareas', $_SESSION['user_role'] ?? ''); 
    ?>
    
    <div class="container">
        <div class="page-header text-center">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="page-description">Asigna clientes específicos a asesores mediante tareas personalizadas</p>
            <div class="header-actions">
                <button type="button" class="btn btn-primary" onclick="abrirModalCrearTarea()">
                    <i class="fas fa-plus"></i> Crear Nueva Tarea
                </button>
                <a href="index.php?action=list_cargas" class="btn btn-outline-primary">
                    <i class="fas fa-database"></i> Gestionar Bases
                </a>
            </div>
        </div>

        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message'] ?? ''); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error_message'] ?? ''); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Estadísticas de tareas -->
        <div class="stats-grid" style="margin-bottom: 30px;">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $estadisticas['total_tareas'] ?? 0; ?></div>
                    <div class="stat-label">Total Tareas</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $estadisticas['pendientes'] ?? 0; ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔄</div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $estadisticas['en_proceso'] ?? 0; ?></div>
                    <div class="stat-label">En Proceso</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $estadisticas['completadas'] ?? 0; ?></div>
                    <div class="stat-label">Completadas</div>
                </div>
            </div>
        </div>

        <!-- Lista de tareas existentes -->
        <div class="tareas-section">
            <h3><i class="fas fa-tasks"></i> Tareas Existentes</h3>
            
            <?php if (empty($tareas)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No hay tareas creadas</h3>
                    <p>Crea tu primera tarea para asignar clientes específicos a un asesor</p>
                    <button type="button" class="btn btn-primary" onclick="abrirModalCrearTarea()">
                        <i class="fas fa-plus"></i> Crear Primera Tarea
                    </button>
                </div>
            <?php else: ?>
                <div class="tareas-list">
                    <?php foreach ($tareas as $tarea): ?>
                        <div class="tarea-item">
                            <div class="tarea-header">
                                <div class="tarea-info">
                                    <h4>Tarea de <?php echo htmlspecialchars($tarea['asesor_nombre'] ?? ''); ?></h4>
                                    <div class="tarea-meta">
                                        <span class="badge badge-<?php echo $tarea['estado'] === 'completada' ? 'success' : ($tarea['estado'] === 'en_proceso' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($tarea['estado']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="tarea-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetallesTarea(<?php echo $tarea['id']; ?>)">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </button>
                                    <?php if ($tarea['estado'] !== 'completada'): ?>
                                        <button class="btn btn-sm btn-success" onclick="marcarCompletada(<?php echo $tarea['id']; ?>)">
                                            <i class="fas fa-check"></i> Completar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="tarea-body">
                                <div class="tarea-details">
                                    <div class="detail-item">
                                        <strong>Base:</strong> <?php echo htmlspecialchars($tarea['nombre_cargue'] ?? ''); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Clientes:</strong> <?php echo $tarea['total_clientes'] ?? 0; ?> asignados
                                    </div>
                                    <div class="detail-item">
                                        <strong>Creada:</strong> <?php echo date('d/m/Y H:i', strtotime($tarea['fecha_creacion'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para crear nueva tarea -->
    <div class="modal" id="modalCrearTarea" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> Crear Nueva Tarea</h3>
                <button type="button" class="close" onclick="cerrarModalCrearTarea()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCrearTarea">
                    <div class="form-group">
                        <label for="carga_id">Base de Datos <span class="required">*</span></label>
                        <select id="carga_id" name="carga_id" required class="form-control" onchange="cargarAsesoresYClientes()">
                            <option value="">Selecciona una base...</option>
                            <?php foreach ($cargas as $carga): ?>
                                <option value="<?php echo $carga['id']; ?>">
                                    <?php echo htmlspecialchars($carga['nombre_cargue']); ?> 
                                    (<?php echo $carga['total_clientes'] ?? 0; ?> clientes)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="asesor_id">Asesor <span class="required">*</span></label>
                        <select id="asesor_id" name="asesor_id" required class="form-control">
                            <option value="">Primero selecciona una base de datos</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Asignar Clientes <span class="required">*</span></label>
                        <div id="clientes-info" class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <span id="clientes-disponibles">Selecciona una base de datos para ver los clientes disponibles</span>
                        </div>
                        <div class="form-group">
                            <label for="cantidad_clientes">Cantidad de Clientes a Asignar</label>
                            <input type="number" id="cantidad_clientes" name="cantidad_clientes" 
                                   class="form-control" min="1" max="1000" 
                                   placeholder="Ingresa la cantidad de clientes">
                            <small class="form-text text-muted">
                                Se asignarán clientes no gestionados de forma aleatoria
                            </small>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="cerrarModalCrearTarea()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Crear Tarea
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles de tarea -->
    <div class="modal" id="modalDetallesTarea" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-eye"></i> Detalles de la Tarea</h3>
                <button type="button" class="close" onclick="cerrarModalDetalles()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="detalles-tarea-content">
                    <!-- Se cargará dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirModalCrearTarea() {
            document.getElementById('modalCrearTarea').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalCrearTarea() {
            document.getElementById('modalCrearTarea').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('formCrearTarea').reset();
            // Limpiar selectores
            document.getElementById('asesor_id').innerHTML = '<option value="">Primero selecciona una base de datos</option>';
            document.getElementById('clientes-disponibles').textContent = 'Selecciona una base de datos para ver los clientes disponibles';
        }

        function cargarAsesoresYClientes() {
            const cargaId = document.getElementById('carga_id').value;
            const asesorSelect = document.getElementById('asesor_id');
            const clientesInfo = document.getElementById('clientes-disponibles');
            
            if (!cargaId) {
                asesorSelect.innerHTML = '<option value="">Primero selecciona una base de datos</option>';
                clientesInfo.textContent = 'Selecciona una base de datos para ver los clientes disponibles';
                return;
            }
            
            // Mostrar loading
            asesorSelect.innerHTML = '<option value="">Cargando asesores...</option>';
            clientesInfo.textContent = 'Cargando información de clientes...';
            
            // Cargar asesores asignados a esta base
            fetch(`index.php?action=get_asesores_base&carga_id=${cargaId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        asesorSelect.innerHTML = '<option value="">Selecciona un asesor...</option>';
                        data.asesores.forEach(asesor => {
                            const option = document.createElement('option');
                            option.value = asesor.id;
                            option.textContent = asesor.nombre_completo;
                            asesorSelect.appendChild(option);
                        });
                    } else {
                        asesorSelect.innerHTML = '<option value="">Error al cargar asesores</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    asesorSelect.innerHTML = '<option value="">Error al cargar asesores</option>';
                });
            
            // Cargar información de clientes no gestionados
            fetch(`index.php?action=get_clientes_no_gestionados&carga_id=${cargaId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        clientesInfo.textContent = `Clientes no gestionados disponibles: ${data.total_no_gestionados} de ${data.total_clientes} clientes`;
                        document.getElementById('cantidad_clientes').max = data.total_no_gestionados;
                    } else {
                        clientesInfo.textContent = 'Error al cargar información de clientes';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    clientesInfo.textContent = 'Error al cargar información de clientes';
                });
        }

        // Manejar envío del formulario
        document.getElementById('formCrearTarea').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const cantidadClientes = document.getElementById('cantidad_clientes').value;
            const asesorId = document.getElementById('asesor_id').value;
            const cargaId = document.getElementById('carga_id').value;
            
            if (!cantidadClientes || !asesorId || !cargaId) {
                alert('Por favor completa todos los campos requeridos');
                return;
            }
            
            if (parseInt(cantidadClientes) < 1) {
                alert('La cantidad de clientes debe ser mayor a 0');
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('index.php?action=crear_tarea', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                // Recargar la página para mostrar la nueva tarea
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al crear la tarea');
            });
        });

        function verDetallesTarea(tareaId) {
            // Aquí podrías cargar los detalles de la tarea via AJAX
            document.getElementById('modalDetallesTarea').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalDetalles() {
            document.getElementById('modalDetallesTarea').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function marcarCompletada(tareaId) {
            if (confirm('¿Estás seguro de que quieres marcar esta tarea como completada?')) {
                fetch('index.php?action=actualizar_estado_tarea', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `tarea_id=${tareaId}&estado=completada`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar la tarea');
                });
            }
        }

        // Cerrar modales al hacer clic fuera de ellos
        document.getElementById('modalCrearTarea').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalCrearTarea();
            }
        });

        document.getElementById('modalDetallesTarea').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalDetalles();
            }
        });
    </script>

    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            font-size: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .stat-content {
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #495057;
            margin: 0;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        .tareas-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }

        .tareas-section h3 {
            margin-bottom: 20px;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tarea-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 15px;
            background: #f8f9fa;
            transition: all 0.2s;
        }

        .tarea-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }

        .tarea-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: white;
            border-bottom: 1px solid #e9ecef;
            border-radius: 8px 8px 0 0;
        }

        .tarea-info h4 {
            margin: 0 0 8px 0;
            color: #495057;
            font-size: 1.1rem;
        }

        .tarea-meta {
            display: flex;
            gap: 8px;
        }

        .tarea-actions {
            display: flex;
            gap: 8px;
        }

        .tarea-body {
            padding: 15px 20px;
        }

        .tarea-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .detail-item {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .detail-item strong {
            color: #495057;
        }

        .cliente-item {
            margin-bottom: 10px;
        }

        .cliente-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .cliente-checkbox:hover {
            background-color: #f8f9fa;
        }

        .cliente-checkbox input[type="checkbox"] {
            margin: 0;
        }

        .cliente-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .cliente-info strong {
            color: #495057;
            font-size: 0.9rem;
        }

        .cliente-info small {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            margin-bottom: 15px;
            color: #495057;
        }

        .empty-state p {
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .close:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 25px;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .required {
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .tarea-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }

            .tarea-actions {
                justify-content: center;
            }

            .tarea-details {
                grid-template-columns: 1fr;
            }

            .form-row {
                flex-direction: column;
            }

            .modal-content {
                width: 95%;
                margin: 10px;
            }
        }
    </style>
</body>
</html>
