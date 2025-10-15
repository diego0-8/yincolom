<?php
// Archivo: views/asesor_gestionar_clientes.php
// Vista para que el asesor gestione clientes mediante búsqueda por cédula
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php require_once 'shared_styles.php'; ?>
</head>
<body>
    <?php 
    require_once 'shared_navbar.php';
    echo getNavbar('Gestión de Clientes', $_SESSION['user_role'] ?? '');
    ?>
    
    <div class="clientes-container">
        <!-- Header Principal -->
        <div class="clientes-header">
            <h2>🔍 Gestión de Clientes</h2>
            <p>Busca clientes por cédula en las bases asignadas para gestionarlos</p>
            
            <?php if ($tieneTareasPendientes): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Tienes tareas pendientes. Ve a <a href="index.php?action=mis_clientes">Mis Clientes</a> para gestionarlas.
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Información de bases asignadas -->
        <div class="bases-info">
            <h3><i class="fas fa-database"></i> Bases Asignadas</h3>
            <?php if (empty($basesAsignadas)): ?>
                <div class="empty-state">
                    <i class="fas fa-database"></i>
                    <h4>No tienes bases asignadas</h4>
                    <p>Contacta a tu coordinador para que te asigne bases de clientes</p>
                </div>
            <?php else: ?>
                <div class="bases-list">
                    <?php foreach ($basesAsignadas as $base): ?>
                        <div class="base-item">
                            <div class="base-info">
                                <h4><?php echo htmlspecialchars($base['nombre_cargue']); ?></h4>
                                <p>Cargada el <?php echo date('d/m/Y', strtotime($base['fecha_cargue'])); ?></p>
                            </div>
                            <div class="base-status">
                                <span class="badge badge-success">Acceso Completo</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Información sobre permisos -->
                <div class="alert alert-info" style="margin-top: 20px;">
                    <i class="fas fa-info-circle"></i>
                    <strong>Información importante:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>Solo puedes buscar y gestionar clientes de las bases que aparecen arriba</li>
                        <li>Si tu coordinador retira el acceso a una base, ya no podrás ver los clientes de esa base</li>
                        <li>Si no encuentras un cliente, puede ser que el acceso a su base haya sido retirado</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Búsqueda de clientes -->
        <div class="search-section">
            <div class="search-container">
                <h3><i class="fas fa-search"></i> Buscar Cliente por Cédula</h3>
                <form id="searchForm" onsubmit="buscarCliente(event)">
                    <div class="search-input-group">
                        <input type="text" 
                               id="cedulaInput" 
                               name="cedula" 
                               placeholder="Ingresa el número de cédula del cliente..." 
                               class="search-input"
                               required>
                        <button type="submit" class="btn btn-primary search-btn">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                    <small class="form-text text-muted">
                        Busca en todas las bases de datos asignadas a ti
                    </small>
                </form>
            </div>
        </div>
        
        <!-- Resultados de búsqueda -->
        <div id="resultados-container" style="display: none;">
            <div class="resultados-header">
                <h3><i class="fas fa-list"></i> Resultados de la Búsqueda</h3>
                <div id="resultados-info" class="resultados-info"></div>
            </div>
            <div id="clientes-resultados" class="clientes-list">
                <!-- Se cargará dinámicamente -->
            </div>
        </div>
        
    </div>
    
    <script>
        function buscarCliente(event) {
            event.preventDefault();
            
            const cedula = document.getElementById('cedulaInput').value.trim();
            if (!cedula) {
                alert('Por favor ingresa un número de cédula');
                return;
            }
            
            // Mostrar loading
            const resultadosContainer = document.getElementById('resultados-container');
            const clientesResultados = document.getElementById('clientes-resultados');
            const resultadosInfo = document.getElementById('resultados-info');
            
            resultadosContainer.style.display = 'block';
            clientesResultados.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Buscando cliente...</div>';
            resultadosInfo.innerHTML = '';
            
            fetch(`index.php?action=buscar_cliente_por_cedula&cedula=${encodeURIComponent(cedula)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarResultados(data.clientes, cedula);
                    } else {
                        mostrarError(data.message || 'Error al buscar cliente');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarError('Error de conexión al buscar cliente');
                });
        }
        
        function mostrarResultados(clientes, cedula) {
            const clientesResultados = document.getElementById('clientes-resultados');
            const resultadosInfo = document.getElementById('resultados-info');
            
            resultadosInfo.innerHTML = `Encontrados ${clientes.length} cliente${clientes.length !== 1 ? 's' : ''} con cédula "${cedula}"`;
            
            if (clientes.length === 0) {
                clientesResultados.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h4>No se encontraron clientes</h4>
                        <p>No hay clientes con la cédula "${cedula}" en las bases asignadas</p>
                        <div class="alert alert-info" style="margin-top: 15px; text-align: left;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Posibles razones:</strong>
                            <ul style="margin: 10px 0 0 20px;">
                                <li>El cliente no existe en ninguna base asignada</li>
                                <li>El coordinador retiró el acceso a la base que contenía este cliente</li>
                                <li>La cédula ingresada no coincide exactamente</li>
                            </ul>
                            <p style="margin: 10px 0 0 0; font-size: 0.9em;">
                                <strong>Nota:</strong> Solo puedes ver clientes de las bases que tu coordinador te ha asignado.
                            </p>
                        </div>
                    </div>
                `;
            } else {
                let html = '';
                clientes.forEach(cliente => {
                    html += `
                        <div class="cliente-item">
                            <div class="cliente-info">
                                <h4>${cliente.nombre || 'Sin nombre'}</h4>
                                <div class="cliente-details">
                                    <span><strong>Cédula:</strong> ${cliente.cedula}</span>
                                    ${cliente.telefono ? `<span><strong>Teléfono:</strong> ${cliente.telefono}</span>` : ''}
                                    ${cliente.celular2 ? `<span><strong>Celular:</strong> ${cliente.celular2}</span>` : ''}
                                    ${cliente.email ? `<span><strong>Email:</strong> ${cliente.email}</span>` : ''}
                                    <span><strong>Base:</strong> ${cliente.nombre_cargue}</span>
                                </div>
                            </div>
                            <div class="cliente-actions">
                                <button class="btn btn-primary" onclick="gestionarCliente(${cliente.id})">
                                    <i class="fas fa-edit"></i> Gestionar
                                </button>
                            </div>
                        </div>
                    `;
                });
                clientesResultados.innerHTML = html;
            }
        }
        
        function mostrarError(mensaje) {
            const clientesResultados = document.getElementById('clientes-resultados');
            const resultadosInfo = document.getElementById('resultados-info');
            
            resultadosInfo.innerHTML = '';
            clientesResultados.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    ${mensaje}
                </div>
            `;
        }
        
        function gestionarCliente(clienteId) {
            // Redirigir directamente a la vista de gestión de cliente
            window.location.href = `index.php?action=gestionar_cliente&id=${clienteId}`;
        }
        
        
        // Buscar al presionar Enter
        document.getElementById('cedulaInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarCliente(e);
            }
        });
    </script>
    
    <style>
        .clientes-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f8fafc;
            min-height: 100vh;
        }
        
        .clientes-header {
            text-align: center;
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .clientes-header h2 {
            color: #1f2937;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .clientes-header p {
            color: #6b7280;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .bases-info {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .bases-info h3 {
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .bases-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .base-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        
        .base-info h4 {
            margin: 0 0 5px 0;
            color: #495057;
            font-size: 1rem;
        }
        
        .base-info p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .search-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .search-container h3 {
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .search-btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .search-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .resultados-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .resultados-header h3 {
            color: #1f2937;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .resultados-info {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .clientes-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .cliente-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        
        .cliente-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        
        .cliente-info h4 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 1.1rem;
        }
        
        .cliente-details {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .cliente-details span {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .cliente-details strong {
            color: #495057;
        }
        
        .cliente-actions {
            display: flex;
            gap: 10px;
        }
        
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
        
        .loading i {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .empty-state h4 {
            margin-bottom: 10px;
            color: #495057;
        }
        
        .empty-state p {
            margin: 0;
            font-size: 1rem;
        }
        
        
        @media (max-width: 768px) {
            .search-input-group {
                flex-direction: column;
            }
            
            .cliente-item {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .cliente-actions {
                justify-content: center;
            }
            
            .cliente-datos {
                grid-template-columns: 1fr;
            }
            
            .historial-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .historial-fecha {
                min-width: auto;
            }
        }
    </style>
</body>
</html>
