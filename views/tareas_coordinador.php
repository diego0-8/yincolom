<?php
// Archivo: views/tareas_coordinador.php
// Vista para que el coordinador gestione las tareas y asignaciones.
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
    echo getNavbar('Tareas', $_SESSION['user_role'] ?? ''); 
    ?>
    
    <div class="container">
        <div class="page-header text-center">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="page-description">Gestiona la asignación de clientes a los asesores</p>
            <div class="header-actions">
                <a href="index.php?action=gestionar_asesores" class="btn btn-outline-primary">
                    <i class="fas fa-users-cog"></i> Gestionar Asesores
                </a>
                <a href="index.php?action=gestionar_traspasos" class="btn btn-outline-info">
                    <i class="fas fa-exchange-alt"></i> Gestionar Traspasos
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

        <!-- Buscador de bases de datos -->
        <div class="search-section">
            <div class="search-container">
                <div class="search-input-group">
                    <input type="text" 
                           id="searchBasesDatos" 
                           placeholder="🔍 Buscar base de datos por nombre..." 
                           class="search-input">
                    <button onclick="buscarBasesDatos()" class="btn btn-primary search-btn">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <button onclick="limpiarBusqueda()" class="btn btn-secondary search-btn">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>

        <?php 
        // Configuración de paginación
        $elementos_por_pagina = 3;
        $total_cargas = count($cargas);
        $total_paginas = ceil($total_cargas / $elementos_por_pagina);
        $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $pagina_actual = max(1, min($pagina_actual, $total_paginas));
        
        // Obtener cargas para la página actual
        $inicio = ($pagina_actual - 1) * $elementos_por_pagina;
        $cargas_pagina = array_slice($cargas, $inicio, $elementos_por_pagina);
        ?>

        <?php if (empty($cargas)): ?>
            <div class="empty-state">
                <i class="fas fa-file-excel"></i>
                <h3>No hay cargas de Excel disponibles</h3>
                <p>Primero debes subir un archivo Excel con clientes para poder asignarlos</p>
                <a href="index.php?action=subir_excel" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Subir Excel
                </a>
            </div>
        <?php else: ?>
            <div class="cargas-list">
                <?php foreach ($cargas_pagina as $carga): ?>
                    <div class="carga-item">
                        <div class="carga-header">
                            <div class="carga-title">
                                <h3><?php echo htmlspecialchars($carga['nombre_cargue'] ?? ''); ?></h3>
                                <div class="carga-meta">
                                    <span class="badge badge-info">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('d/m/Y', strtotime($carga['fecha_cargue'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="carga-stats">
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $carga['total_clientes'] ?? 'N/A'; ?></span>
                                    <span class="stat-label">Total</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $carga['clientes_asignados'] ?? 'N/A'; ?></span>
                                    <span class="stat-label">Asignados</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $carga['clientes_pendientes'] ?? 'N/A'; ?></span>
                                    <span class="stat-label">Pendientes</span>
                                </div>
                            </div>
                        </div>
                        <div class="carga-content">
                            <div class="asesores-section">
                                <h4>Asignar a Asesores</h4>
                                <form method="POST" action="index.php?action=asignarClientes" class="asignacion-form">
                                    <input type="hidden" name="carga_id" value="<?php echo $carga['id']; ?>">
                                    
                                    <div class="asesores-list">
                                        <?php foreach ($asesores as $asesor): ?>
                                            <div class="asesor-list-item">
                                                <div class="asesor-info">
                                                    <div class="asesor-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="asesor-details">
                                                        <strong><?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?></strong>
                                                        <small class="text-muted"><?php echo ucfirst($asesor['rol']); ?></small>
                                                    </div>
                                                    <div class="asesor-stats">
                                                        <span class="current-assigned">
                                                            <i class="fas fa-users"></i>
                                                            <?php echo $asesor['clientes_por_carga'][$carga['id']] ?? 0; ?> asignados
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="asignacion-input">
                                                    <label for="asesor_<?php echo $asesor['id']; ?>">Nueva asignación:</label>
                                                    <input type="number" 
                                                           id="asesor_<?php echo $asesor['id']; ?>" 
                                                           name="asignaciones[<?php echo $asesor['id']; ?>]" 
                                                           min="0" 
                                                           max="1000" 
                                                           class="form-control form-control-sm"
                                                           placeholder="0">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Asignar Clientes
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="asignarAutomatico(<?php echo $carga['id']; ?>)">
                                            <i class="fas fa-magic"></i> Asignación Automática
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="carga-actions">
                            <a href="index.php?action=ver_clientes&carga_id=<?php echo $carga['id']; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-users"></i> Ver Clientes
                            </a>
                            <a href="index.php?action=ver_actividades&carga_id=<?php echo $carga['id']; ?>" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-chart-line"></i> Ver Actividades
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span>Mostrando <?php echo $inicio + 1; ?>-<?php echo min($inicio + $elementos_por_pagina, $total_cargas); ?> de <?php echo $total_cargas; ?> bases de datos</span>
                    </div>
                    <div class="pagination">
                        <?php if ($pagina_actual > 1): ?>
                            <a href="?action=tareas_coordinador&pagina=1" class="pagination-btn">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="?action=tareas_coordinador&pagina=<?php echo $pagina_actual - 1; ?>" class="pagination-btn">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $inicio_pagina = max(1, $pagina_actual - 2);
                        $fin_pagina = min($total_paginas, $pagina_actual + 2);
                        
                        for ($i = $inicio_pagina; $i <= $fin_pagina; $i++):
                        ?>
                            <a href="?action=tareas_coordinador&pagina=<?php echo $i; ?>" 
                               class="pagination-btn <?php echo $i === $pagina_actual ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $total_paginas): ?>
                            <a href="?action=tareas_coordinador&pagina=<?php echo $pagina_actual + 1; ?>" class="pagination-btn">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="?action=tareas_coordinador&pagina=<?php echo $total_paginas; ?>" class="pagination-btn">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // Funciones de búsqueda
        function buscarBasesDatos() {
            const termino = document.getElementById('searchBasesDatos').value.toLowerCase().trim();
            const cargaItems = document.querySelectorAll('.carga-item');
            let resultados = 0;
            
            cargaItems.forEach(item => {
                const nombreCarga = item.querySelector('h3').textContent.toLowerCase();
                const coincide = nombreCarga.includes(termino);
                
                if (coincide) {
                    item.style.display = 'block';
                    resultados++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Mostrar mensaje si no hay resultados
            mostrarMensajeBusqueda(resultados, termino);
        }
        
        function limpiarBusqueda() {
            document.getElementById('searchBasesDatos').value = '';
            const cargaItems = document.querySelectorAll('.carga-item');
            
            cargaItems.forEach(item => {
                item.style.display = 'block';
            });
            
            // Ocultar mensaje de búsqueda
            const mensajeBusqueda = document.getElementById('mensaje-busqueda');
            if (mensajeBusqueda) {
                mensajeBusqueda.remove();
            }
        }
        
        function mostrarMensajeBusqueda(resultados, termino) {
            // Remover mensaje anterior si existe
            const mensajeAnterior = document.getElementById('mensaje-busqueda');
            if (mensajeAnterior) {
                mensajeAnterior.remove();
            }
            
            if (termino && resultados === 0) {
                const mensaje = document.createElement('div');
                mensaje.id = 'mensaje-busqueda';
                mensaje.className = 'alert alert-info';
                mensaje.innerHTML = `
                    <i class="fas fa-search"></i>
                    No se encontraron bases de datos que coincidan con "${termino}"
                `;
                
                const cargasList = document.querySelector('.cargas-list');
                cargasList.parentNode.insertBefore(mensaje, cargasList);
            }
        }
        
        // Búsqueda en tiempo real
        document.getElementById('searchBasesDatos').addEventListener('input', function() {
            const termino = this.value.toLowerCase().trim();
            if (termino.length >= 2 || termino.length === 0) {
                buscarBasesDatos();
            }
        });
        
        // Búsqueda al presionar Enter
        document.getElementById('searchBasesDatos').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarBasesDatos();
            }
        });

        function asignarAutomatico(cargaId) {
            if (confirm('¿Estás seguro de que quieres asignar automáticamente todos los clientes de esta carga?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'index.php?action=asignar_automatico';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'carga_id';
                input.value = cargaId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Validación del formulario
        document.querySelectorAll('.asignacion-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const inputs = this.querySelectorAll('input[type="number"]');
                let total = 0;
                let hasValue = false;
                
                inputs.forEach(input => {
                    const value = parseInt(input.value) || 0;
                    total += value;
                    if (value > 0) hasValue = true;
                });
                
                if (!hasValue) {
                    e.preventDefault();
                    alert('Debes asignar al menos un cliente a algún asesor.');
                    return false;
                }
                
                if (total > 1000) {
                    e.preventDefault();
                    alert('El total de clientes asignados no puede exceder 1000.');
                    return false;
                }
            });
        });
    </script>

    <style>
        .cargas-list {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .carga-item {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: box-shadow 0.2s;
        }
        
        .carga-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .carga-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
        }
        
        .carga-title h3 {
            margin: 0 0 8px 0;
            color: #495057;
            font-size: 1.4rem;
        }
        
        .carga-meta {
            display: flex;
            gap: 10px;
        }
        
        .carga-stats {
            display: flex;
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .carga-content {
            padding: 20px;
        }
        
        .carga-actions {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .tarea-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .carga-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }
        
        .info-item i {
            color: #6c757d;
            width: 14px;
            font-size: 12px;
        }
        
        .asesores-section h4 {
            margin-bottom: 12px;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 6px;
            font-size: 16px;
        }
        
        .asesores-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .asesor-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        
        .asesor-list-item:hover {
            background: #e9ecef;
        }
        
        .asesor-stats {
            margin-left: auto;
            margin-right: 20px;
        }
        
        .current-assigned {
            display: flex;
            align-items: center;
            gap: 5px;
            background: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .current-assigned i {
            font-size: 0.8rem;
        }
        
        .asesor-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        
        .asesor-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .asesor-avatar {
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
        
        .asesor-details {
            display: flex;
            flex-direction: column;
        }
        
        .asesor-details small {
            font-size: 11px;
        }
        
        .asignacion-input {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .asignacion-input label {
            font-size: 13px;
            font-weight: 500;
            margin: 0;
        }
        
        .form-actions {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .card-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            border-top: 1px solid #e9ecef;
            padding-top: 12px;
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
        
        .header-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .page-description {
            font-size: 1.1rem;
            margin-bottom: 20px;
            color: #6c757d;
        }
        
        /* Estilos para el buscador */
        .search-section {
            margin-bottom: 30px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .search-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .search-input-group {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 300px;
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
        
        /* Estilos para la paginación */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 30px 0;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .pagination-info {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }
        
        .pagination {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .pagination-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #6c757d;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .pagination-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #f8fafc;
            transform: translateY(-1px);
        }
        
        .pagination-btn.active {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }
        
        .pagination-btn.active:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
        }
        
        .pagination-btn i {
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .carga-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .carga-stats {
                justify-content: center;
            }
            
            .asesor-list-item {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .asesor-stats {
                margin: 0;
                align-self: center;
            }
            
            .asignacion-input {
                justify-content: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .carga-actions {
                flex-direction: column;
            }
            
            .header-actions {
                flex-direction: column;
            }
            
            /* Responsive para buscador */
            .search-input-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-input {
                min-width: auto;
                width: 100%;
            }
            
            .search-btn {
                width: 100%;
                justify-content: center;
            }
            
            /* Responsive para paginación */
            .pagination-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .pagination {
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .pagination-btn {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
        }
    </style>
</body>
</html>