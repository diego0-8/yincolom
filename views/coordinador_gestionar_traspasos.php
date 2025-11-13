<?php
// Archivo: views/coordinador_gestionar_traspasos.php
// Vista para que el coordinador gestione traspasos de clientes entre asesores
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
    echo getNavbar('Gestión de Traspasos', $_SESSION['user_role'] ?? ''); 
    ?>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="page-description">Gestiona el traspaso de clientes entre asesores de tu equipo</p>
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

        <?php if (empty($clientesPorAsesor)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No hay asesores con clientes asignados</h3>
                <p>Primero debes asignar clientes a los asesores para poder gestionar traspasos</p>
                <a href="index.php?action=tareas_coordinador" class="btn btn-primary">
                    <i class="fas fa-tasks"></i> Ir a Tareas
                </a>
            </div>
        <?php else: ?>
            <div class="traspasos-grid">
                <?php foreach ($clientesPorAsesor as $asesorId => $data): ?>
                    <?php if (!empty($data['clientes'])): ?>
                        <div class="card asesor-card">
                            <div class="card-header">
                                <div class="asesor-header">
                                    <div class="asesor-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="asesor-info">
                                        <h3><?php echo htmlspecialchars(($data['asesor'] ?? [])['nombre_completo'] ?? ''); ?></h3>
                                        <small><?php echo htmlspecialchars(($data['asesor'] ?? [])['usuario'] ?? ''); ?></small>
                                    </div>
                                    <div class="asesor-stats">
                                        <span class="badge badge-primary"><?php echo count($data['clientes']); ?> clientes</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="clientes-list">
                                    <?php foreach ($data['clientes'] as $cliente): ?>
                                        <div class="cliente-item">
                                            <div class="cliente-info">
                                                <div class="cliente-avatar">
                                                    <i class="fas fa-user-tie"></i>
                                                </div>
                                                <div class="cliente-details">
                                                    <strong><?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?></strong>
                                                    <small><?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?> | <?php echo htmlspecialchars($cliente['ciudad'] ?? ''); ?></small>
                                                </div>
                                            </div>
                                            <div class="cliente-actions">
                                                <!-- Botón para traspasar -->
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="mostrarModalTraspaso(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?>', <?php echo $asesorId; ?>)">
                                                    <i class="fas fa-exchange-alt"></i> Traspasar
                                                </button>
                                                
                                                <!-- Botón para liberar -->
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="liberar">
                                                    <input type="hidden" name="cliente_id" value="<?php echo $cliente['id']; ?>">
                                                    <input type="hidden" name="asesor_id" value="<?php echo $asesorId; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                            onclick="return confirm('¿Estás seguro de liberar este cliente?')">
                                                        <i class="fas fa-user-times"></i> Liberar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para traspasar cliente -->
    <div id="modalTraspaso" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Traspasar Cliente</h3>
                <span class="close" onclick="cerrarModalTraspaso()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Traspasar cliente: <strong id="clienteNombre"></strong></p>
                <form method="POST" id="formTraspaso">
                    <input type="hidden" name="action" value="traspasar">
                    <input type="hidden" name="cliente_id" id="clienteId">
                    <input type="hidden" name="asesor_origen_id" id="asesorOrigenId">
                    
                    <div class="form-group">
                        <label for="nuevo_asesor_id">Nuevo Asesor:</label>
                        <select name="nuevo_asesor_id" id="nuevoAsesorId" class="form-control" required>
                            <option value="">Seleccionar asesor...</option>
                            <?php foreach ($clientesPorAsesor as $asesorId => $data): ?>
                                <option value="<?php echo $asesorId; ?>" data-origin="<?php echo $asesorId; ?>">
                                    <?php echo htmlspecialchars(($data['asesor'] ?? [])['nombre_completo'] ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-exchange-alt"></i> Confirmar Traspaso
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cerrarModalTraspaso()">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function mostrarModalTraspaso(clienteId, clienteNombre, asesorOrigenId) {
            document.getElementById('clienteId').value = clienteId;
            document.getElementById('clienteNombre').textContent = clienteNombre;
            document.getElementById('asesorOrigenId').value = asesorOrigenId;
            document.getElementById('nuevoAsesorId').value = '';
            
            // Filtrar opciones del select para excluir al asesor origen
            const select = document.getElementById('nuevoAsesorId');
            const options = select.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block'; // Siempre mostrar la opción vacía
                } else if (option.value == asesorOrigenId) {
                    option.style.display = 'none'; // Ocultar el asesor origen
                } else {
                    option.style.display = 'block'; // Mostrar otros asesores
                }
            });
            
            document.getElementById('modalTraspaso').style.display = 'block';
        }
        
        function cerrarModalTraspaso() {
            document.getElementById('modalTraspaso').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modalTraspaso');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>

    <style>
        .traspasos-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        }
        
        .asesor-card {
            border: 1px solid #e9ecef;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .asesor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .asesor-header {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .asesor-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .asesor-info h3 {
            margin: 0;
            font-size: 18px;
            color: #495057;
        }
        
        .asesor-info small {
            color: #6c757d;
            font-size: 12px;
        }
        
        .asesor-stats {
            margin-left: auto;
        }
        
        .clientes-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .cliente-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        
        .cliente-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .cliente-avatar {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #45b7d1, #96c93d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }
        
        .cliente-details {
            display: flex;
            flex-direction: column;
        }
        
        .cliente-details strong {
            color: #495057;
            font-size: 14px;
        }
        
        .cliente-details small {
            color: #6c757d;
            font-size: 12px;
        }
        
        .cliente-actions {
            display: flex;
            gap: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            margin-bottom: 15px;
            color: #495057;
        }
        
        .empty-state p {
            margin-bottom: 25px;
            font-size: 16px;
        }
        
        /* Modal styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #495057;
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
        
        .modal-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        @media (max-width: 768px) {
            .traspasos-grid {
                grid-template-columns: 1fr;
            }
            
            .cliente-item {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .cliente-actions {
                justify-content: center;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
