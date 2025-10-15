<?php
// Archivo: views/clientes_list.php
// Vista para listar clientes de una carga específica.
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
    echo getNavbar('Resultados', $_SESSION['user_role'] ?? ''); 
    ?>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="page-description">Gestiona los clientes de esta carga y asígnalos a los asesores</p>
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

        <div class="actions-bar">
            <a href="index.php?action=list_cargas" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Cargas
            </a>
            <a href="index.php?action=tareas_coordinador" class="btn btn-primary">
                <i class="fas fa-tasks"></i> Gestionar Tareas
            </a>
        </div>

        <?php if (empty($clientes)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No hay clientes en esta carga</h3>
                <p>Parece que no se encontraron clientes para mostrar</p>
                <a href="index.php?action=list_cargas" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver a Cargas
                </a>
            </div>
        <?php else: ?>
            <div class="clients-section">
                <div class="card">
                    <div class="card-header">
                        <div class="header-content">
                            <h2>Lista de Clientes</h2>
                            <div class="header-actions">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleFilters()">
                                    <i class="fas fa-filter"></i> Filtros
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportarClientes()">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="filters-panel" id="filtersPanel" style="display: none;">
                            <div class="filters-grid">
                                <div class="filter-group">
                                    <label for="searchName">Buscar por nombre:</label>
                                    <input type="text" id="searchName" class="form-control form-control-sm" placeholder="Nombre del cliente">
                                </div>
                                <div class="filter-group">
                                    <label for="filterAsesor">Filtrar por asesor:</label>
                                    <select id="filterAsesor" class="form-control form-control-sm">
                                        <option value="">Todos los asesores</option>
                                        <?php foreach ($asesores as $asesor): ?>
                                            <option value="<?php echo $asesor['id']; ?>">
                                                <?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="filterCiudad">Filtrar por ciudad:</label>
                                    <input type="text" id="filterCiudad" class="form-control form-control-sm" placeholder="Ciudad">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table" id="clientesTable">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                        </th>
                                        <th>Cliente</th>
                                        <th>Contacto</th>
                                        <th>Ciudad</th>
                                        <th>Asesor</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <tr class="cliente-row" data-nombre="<?php echo strtolower($cliente['nombre']); ?>" 
                                            data-asesor="<?php echo $cliente['asesor_id'] ?? ''; ?>"
                                            data-ciudad="<?php echo strtolower($cliente['ciudad']); ?>">
                                            <td>
                                                <input type="checkbox" name="clientes[]" value="<?php echo $cliente['id']; ?>" 
                                                       class="cliente-checkbox">
                                            </td>
                                            <td>
                                                <div class="cliente-info">
                                                    <div class="cliente-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="cliente-details">
                                                        <strong><?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?></strong>
                                                        <small class="text-muted">Cédula: <?php echo htmlspecialchars($cliente['cedula'] ?? ''); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div class="contact-item">
                                                        <i class="fas fa-phone"></i>
                                                        <span><?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?></span>
                                                    </div>
                                                    <?php if (!empty($cliente['celular2'])): ?>
                                                        <div class="contact-item">
                                                            <i class="fas fa-mobile-alt"></i>
                                                            <span><?php echo htmlspecialchars($cliente['celular2'] ?? ''); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-outline"><?php echo htmlspecialchars($cliente['ciudad'] ?? ''); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($cliente['asesor_id']): ?>
                                                    <?php
                                                    $asesor = array_filter($asesores, function($a) use ($cliente) {
                                                        return $a['id'] == $cliente['asesor_id'];
                                                    });
                                                    $asesor = reset($asesor);
                                                    ?>
                                                    <div class="asesor-info">
                                                        <div class="asesor-avatar">
                                                            <i class="fas fa-user-tie"></i>
                                                        </div>
                                                        <span><?php echo htmlspecialchars($asesor['nombre_completo'] ?? 'N/A'); ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Sin asignar</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($cliente['asesor_id']): ?>
                                                    <span class="badge badge-success">Asignado</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="verCliente(<?php echo $cliente['id']; ?>)" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if (!$cliente['asesor_id']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="asignarCliente(<?php echo $cliente['id']; ?>)" title="Asignar asesor">
                                                            <i class="fas fa-user-plus"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($totalPaginas > 1): ?>
                            <div class="pagination-container">
                                <div class="pagination-info">
                                    Mostrando página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?>
                                    (Total: <?php echo $totalClientes; ?> clientes)
                                </div>
                                <div class="pagination">
                                    <?php if ($paginaActual > 1): ?>
                                        <a href="?action=ver_clientes&carga_id=<?php echo $carga_id; ?>&pagina=<?php echo $paginaActual - 1; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-chevron-left"></i> Anterior
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $paginaActual - 2); $i <= min($totalPaginas, $paginaActual + 2); $i++): ?>
                                        <a href="?action=ver_clientes&carga_id=<?php echo $carga_id; ?>&pagina=<?php echo $i; ?>" 
                                           class="btn btn-sm <?php echo $i == $paginaActual ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($paginaActual < $totalPaginas): ?>
                                        <a href="?action=ver_clientes&carga_id=<?php echo $carga_id; ?>&pagina=<?php echo $paginaActual + 1; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            Siguiente <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleFilters() {
            const panel = document.getElementById('filtersPanel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.cliente-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        function exportarClientes() {
            if (confirm('¿Deseas exportar la lista de clientes?')) {
                // Implementar lógica de exportación
                alert('Función de exportación en desarrollo');
            }
        }

        function verCliente(clienteId) {
            // Implementar vista de detalles del cliente
            alert('Vista de detalles en desarrollo');
        }

        function asignarCliente(clienteId) {
            // Implementar asignación individual de cliente
            alert('Asignación individual en desarrollo');
        }

        // Filtros en tiempo real
        document.getElementById('searchName').addEventListener('input', filterClientes);
        document.getElementById('filterAsesor').addEventListener('change', filterClientes);
        document.getElementById('filterCiudad').addEventListener('input', filterClientes);

        function filterClientes() {
            const searchName = document.getElementById('searchName').value.toLowerCase();
            const filterAsesor = document.getElementById('filterAsesor').value;
            const filterCiudad = document.getElementById('filterCiudad').value.toLowerCase();
            
            const rows = document.querySelectorAll('.cliente-row');
            
            rows.forEach(row => {
                const nombre = row.dataset.nombre;
                const asesor = row.dataset.asesor;
                const ciudad = row.dataset.ciudad;
                
                const matchName = nombre.includes(searchName);
                const matchAsesor = !filterAsesor || asesor === filterAsesor;
                const matchCiudad = ciudad.includes(filterCiudad);
                
                if (matchName && matchAsesor && matchCiudad) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>

    <style>
        .actions-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .clients-section {
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .filters-panel {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-size: 14px;
            font-weight: 500;
            color: #495057;
        }
        
        .cliente-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .cliente-avatar {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .cliente-details small {
            font-size: 12px;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .contact-item i {
            color: #6c757d;
            width: 16px;
        }
        
        .asesor-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .asesor-avatar {
            width: 25px;
            height: 25px;
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .pagination-info {
            color: #6c757d;
            font-size: 14px;
        }
        
        .pagination {
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
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: stretch;
            }
            
            .header-actions {
                justify-content: center;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .pagination-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</body>
</html>
