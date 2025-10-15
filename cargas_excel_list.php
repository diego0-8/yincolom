<?php
// Archivo: views/cargas_excel_list.php
// Vista para listar las cargas de Excel del coordinador.
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
    echo getNavbar('Gestión', $_SESSION['user_role'] ?? ''); 
    ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Gestión de Bases de Datos</h1>
            <p class="page-description">Administra tus bases de datos de clientes independientes y la base consolidada</p>
        </div>

        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['info_message'])): ?>
            <div class="alert alert-info">
                <?php echo $_SESSION['info_message']; ?>
            </div>
            <?php unset($_SESSION['info_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['warning_message'])): ?>
            <div class="alert alert-warning">
                <?php echo $_SESSION['warning_message']; ?>
            </div>
            <?php unset($_SESSION['warning_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error_message'] ?? ''); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="actions-bar">
            <a href="index.php?action=gestion_cargas" class="btn btn-primary">
                <i class="fas fa-upload"></i> Gestionar Cargas de Archivos
            </a>
            <a href="index.php?action=gestionar_tareas" class="btn btn-outline-primary">
                <i class="fas fa-tasks"></i> Gestionar Tareas Específicas
            </a>
        </div>

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
                <i class="fas fa-database"></i>
                <h3>No hay bases de datos</h3>
                <p>Comienza subiendo tu primer archivo CSV para crear una base de datos</p>
                <a href="index.php?action=gestion_cargas" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Gestionar Cargas de Archivos
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
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo date('H:i', strtotime($carga['fecha_cargue'])); ?>
                                    </span>
                                    <?php if (isset($carga['tipo_base_datos'])): ?>
                                    <span class="badge badge-<?php echo $carga['tipo_base_datos'] === 'independiente' ? 'success' : 'primary'; ?>">
                                        <i class="fas fa-<?php echo $carga['tipo_base_datos'] === 'independiente' ? 'database' : 'layer-group'; ?>"></i>
                                        <?php echo $carga['tipo_base_datos'] === 'independiente' ? 'Independiente' : 'Consolidada'; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="carga-stats">
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $carga['total_clientes']; ?></span>
                                    <span class="stat-label">Total</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $carga['clientes_asignados']; ?></span>
                                    <span class="stat-label">Asignados</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $carga['clientes_pendientes']; ?></span>
                                    <span class="stat-label">Pendientes</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="carga-body">
                            <div class="carga-info">
                                <div class="info-row">
                                    <span class="info-label">Coordinador:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($carga['coordinador_nombre'] ?? ''); ?></span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Estado:</span>
                                    <span class="info-value">
                                        <span class="badge badge-<?php echo $carga['estado'] === 'completada' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($carga['estado']); ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="asesores-section">
                                <div class="asesores-header">
                                    <h4><i class="fas fa-user-tie"></i> Asesores Asignados</h4>
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                            onclick="verAsesores(<?php echo $carga['id']; ?>)">
                                        <i class="fas fa-eye"></i> Ver Todos
                                    </button>
                                </div>
                                
                                <div class="asesores-dropdown">
                                    <button type="button" class="dropdown-toggle" onclick="toggleAsesores(<?php echo $carga['id']; ?>)">
                                        <span id="asesores-count-<?php echo $carga['id']; ?>">0 asesores</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <div class="dropdown-content" id="asesores-dropdown-<?php echo $carga['id']; ?>" style="display: none;">
                                        <div class="asesores-list">
                                            <div class="loading-asesores">
                                                <i class="fas fa-spinner fa-spin"></i> Cargando asesores...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="carga-actions">
                                <div class="action-group">
                                    <a href="index.php?action=ver_clientes&carga_id=<?php echo $carga['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Ver Clientes
                                    </a>
                                    <a href="index.php?action=ver_actividades&carga_id=<?php echo $carga['id']; ?>" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-chart-line"></i> Actividades
                                    </a>
                                </div>
                                
                                <div class="action-group">
                                    <button type="button" class="btn btn-success btn-sm" 
                                            onclick="asignarAsesor(<?php echo $carga['id']; ?>)">
                                        <i class="fas fa-user-plus"></i> Asignar Asesor
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" 
                                            onclick="asignarBaseCompleta(<?php echo $carga['id']; ?>)">
                                        <i class="fas fa-database"></i> Asignar Base Completa
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" 
                                            onclick="exportarCarga(<?php echo $carga['id']; ?>)">
                                        <i class="fas fa-download"></i> Exportar
                                    </button>
                                </div>
                                
                                <?php if (isset($carga['tipo_base_datos']) && $carga['tipo_base_datos'] === 'independiente'): ?>
                                <div class="action-group">
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            onclick="eliminarBaseDatos(<?php echo $carga['id']; ?>)">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
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
                            <a href="?action=list_cargas&pagina=1" class="pagination-btn">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="?action=list_cargas&pagina=<?php echo $pagina_actual - 1; ?>" class="pagination-btn">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $inicio_pagina = max(1, $pagina_actual - 2);
                        $fin_pagina = min($total_paginas, $pagina_actual + 2);
                        
                        for ($i = $inicio_pagina; $i <= $fin_pagina; $i++):
                        ?>
                            <a href="?action=list_cargas&pagina=<?php echo $i; ?>" 
                               class="pagination-btn <?php echo $i === $pagina_actual ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $total_paginas): ?>
                            <a href="?action=list_cargas&pagina=<?php echo $pagina_actual + 1; ?>" class="pagination-btn">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="?action=list_cargas&pagina=<?php echo $total_paginas; ?>" class="pagination-btn">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="cargas-summary">
                <div class="summary-card">
                    <div class="summary-item">
                        <i class="fas fa-file-excel"></i>
                        <div class="summary-content">
                            <span class="summary-number"><?php echo count($cargas); ?></span>
                            <span class="summary-label">Total Cargas</span>
                        </div>
                    </div>
                    
                    <div class="summary-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div class="summary-content">
                            <span class="summary-number">
                                <?php 
                                $ultimaCarga = reset($cargas);
                                echo $ultimaCarga ? date('d/m', strtotime($ultimaCarga['fecha_cargue'])) : 'N/A';
                                ?>
                            </span>
                            <span class="summary-label">Última Carga</span>
                        </div>
                    </div>
                    
                    <div class="summary-item">
                        <i class="fas fa-clock"></i>
                        <div class="summary-content">
                            <span class="summary-number">
                                <?php 
                                $cargasHoy = 0;
                                $hoy = date('Y-m-d');
                                foreach ($cargas as $carga) {
                                    if (date('Y-m-d', strtotime($carga['fecha_cargue'])) === $hoy) {
                                        $cargasHoy++;
                                    }
                                }
                                echo $cargasHoy;
                                ?>
                            </span>
                            <span class="summary-label">Cargas Hoy</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para asignar asesores -->
    <div class="modal" id="modalAsignarAsesor" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Asignar Asesor</h3>
                <button type="button" class="close" onclick="cerrarModalAsignar()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formAsignarAsesor">
                    <input type="hidden" id="carga_id_asignar" name="carga_id">
                    
                    <div class="form-group">
                        <label for="asesor_id">Seleccionar Asesor <span class="required">*</span></label>
                        <select id="asesor_id" name="asesor_id" required class="form-control">
                            <option value="">Selecciona un asesor...</option>
                        </select>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="cerrarModalAsignar()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Asignar Asesor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para ver asesores -->
    <div class="modal" id="modalVerAsesores" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-users"></i> Asesores Asignados</h3>
                <button type="button" class="close" onclick="cerrarModalVerAsesores()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="lista-asesores">
                    <!-- Se cargará dinámicamente -->
                </div>
            </div>
        </div>
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

        function exportarCarga(cargaId) {
            if (confirm('¿Deseas exportar los datos de esta carga?')) {
                // Aquí implementarías la lógica de exportación
                alert('Función de exportación en desarrollo');
            }
        }

        // Función para cargar estadísticas en tiempo real
        function cargarEstadisticas() {
            // Aquí podrías hacer llamadas AJAX para obtener estadísticas en tiempo real
            console.log('Cargando estadísticas...');
        }

        // Cargar estadísticas al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarEstadisticas();
            cargarConteoAsesores();
        });
        
        // Función para cargar el conteo de asesores para cada carga
        function cargarConteoAsesores() {
            const cargaItems = document.querySelectorAll('.carga-item');
            cargaItems.forEach(item => {
                const cargaId = item.querySelector('[onclick*="verAsesores"]').getAttribute('onclick').match(/\d+/)[0];
                cargarConteoAsesoresCarga(cargaId);
            });
        }
        
        // Función para cargar el conteo de asesores de una carga específica
        function cargarConteoAsesoresCarga(cargaId) {
            fetch(`index.php?action=get_asesores_asignados&carga_id=${cargaId}`)
                .then(response => response.json())
                .then(data => {
                    const countElement = document.getElementById(`asesores-count-${cargaId}`);
                    if (countElement) {
                        countElement.textContent = `${data.length} asesor${data.length !== 1 ? 'es' : ''}`;
                    }
                })
                .catch(error => {
                    console.error('Error al cargar conteo de asesores:', error);
                });
        }
        
        // Función para toggle del desplegable de asesores
        function toggleAsesores(cargaId) {
            const dropdown = document.getElementById(`asesores-dropdown-${cargaId}`);
            const toggle = dropdown.previousElementSibling;
            
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                // Mostrar dropdown
                dropdown.style.display = 'block';
                toggle.classList.add('active');
                
                // Cargar asesores si no se han cargado
                const asesoresList = dropdown.querySelector('.asesores-list');
                if (asesoresList.innerHTML.includes('Cargando asesores')) {
                    cargarAsesoresDropdown(cargaId);
                }
            } else {
                // Ocultar dropdown
                dropdown.style.display = 'none';
                toggle.classList.remove('active');
            }
        }
        
        // Función para cargar asesores en el dropdown
        function cargarAsesoresDropdown(cargaId) {
            fetch(`index.php?action=get_asesores_asignados&carga_id=${cargaId}`)
                .then(response => response.json())
                .then(data => {
                    const asesoresList = document.querySelector(`#asesores-dropdown-${cargaId} .asesores-list`);
                    
                    if (data.length === 0) {
                        asesoresList.innerHTML = '<p style="text-align: center; color: #6c757d; padding: 15px;">No hay asesores asignados</p>';
                    } else {
                        asesoresList.innerHTML = data.map(asesor => `
                            <div class="asesor-item">
                                <div class="asesor-info">
                                    <i class="fas fa-user"></i>
                                    <span>${asesor.nombre_completo}</span>
                                </div>
                                <button class="btn btn-outline-danger btn-sm" onclick="liberarAsesor(${cargaId}, ${asesor.id})">
                                    <i class="fas fa-user-minus"></i> Liberar
                                </button>
                            </div>
                        `).join('');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const asesoresList = document.querySelector(`#asesores-dropdown-${cargaId} .asesores-list`);
                    asesoresList.innerHTML = '<p style="text-align: center; color: #dc3545; padding: 15px;">Error al cargar asesores</p>';
                });
        }

        // Funciones para gestión de asesores
        function asignarAsesor(cargaId) {
            document.getElementById('carga_id_asignar').value = cargaId;
            
            // Cargar asesores disponibles
            fetch(`index.php?action=get_asesores_disponibles&carga_id=${cargaId}`)
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('asesor_id');
                    select.innerHTML = '<option value="">Selecciona un asesor...</option>';
                    
                    data.forEach(asesor => {
                        const option = document.createElement('option');
                        option.value = asesor.id;
                        option.textContent = asesor.nombre_completo;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los asesores disponibles');
                });
            
            document.getElementById('modalAsignarAsesor').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function asignarBaseCompleta(cargaId) {
            if (confirm('¿Estás seguro de que quieres asignar esta base completa a un asesor? El asesor tendrá acceso a TODOS los clientes de esta base.')) {
                // Crear modal para seleccionar asesor
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.style.display = 'flex';
                modal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3><i class="fas fa-database"></i> Asignar Base Completa</h3>
                            <button type="button" class="close" onclick="this.closest('.modal').remove(); document.body.style.overflow = 'auto';">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="formAsignarBaseCompleta">
                                <input type="hidden" name="carga_id" value="${cargaId}">
                                <div class="form-group">
                                    <label for="asesor_base_id">Seleccionar Asesor <span class="required">*</span></label>
                                    <select id="asesor_base_id" name="asesor_id" required class="form-control">
                                        <option value="">Selecciona un asesor...</option>
                                    </select>
                                </div>
                                <div class="modal-actions">
                                    <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove(); document.body.style.overflow = 'auto';">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-database"></i> Asignar Base Completa
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                document.body.style.overflow = 'hidden';
                
                // Cargar asesores disponibles
                fetch(`index.php?action=get_asesores_disponibles_carga&carga_id=${cargaId}`)
                    .then(response => response.json())
                    .then(data => {
                        const select = document.getElementById('asesor_base_id');
                        select.innerHTML = '<option value="">Selecciona un asesor...</option>';
                        
                        data.asesores.forEach(asesor => {
                            const option = document.createElement('option');
                            option.value = asesor.id;
                            option.textContent = asesor.nombre_completo;
                            select.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al cargar los asesores disponibles');
                    });
                
                // Manejar envío del formulario
                document.getElementById('formAsignarBaseCompleta').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    fetch('index.php?action=asignar_base_completa', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(text => {
                        // Recargar la página para mostrar los cambios
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al asignar la base completa');
                    });
                });
            }
        }

        function cerrarModalAsignar() {
            document.getElementById('modalAsignarAsesor').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('formAsignarAsesor').reset();
        }

        function verAsesores(cargaId) {
            // Cargar asesores asignados
            fetch(`index.php?action=get_asesores_asignados&carga_id=${cargaId}`)
                .then(response => response.json())
                .then(data => {
                    const lista = document.getElementById('lista-asesores');
                    
                    if (data.length === 0) {
                        lista.innerHTML = '<p>No hay asesores asignados a esta base de datos.</p>';
                    } else {
                        lista.innerHTML = data.map(asesor => `
                            <div class="asesor-item">
                                <div class="asesor-info">
                                    <i class="fas fa-user"></i>
                                    <span>${asesor.nombre_completo}</span>
                                </div>
                                <button class="btn btn-outline-danger btn-sm" onclick="liberarAsesor(${cargaId}, ${asesor.id})">
                                    <i class="fas fa-user-minus"></i> Liberar
                                </button>
                            </div>
                        `).join('');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los asesores asignados');
                });
            
            document.getElementById('modalVerAsesores').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalVerAsesores() {
            document.getElementById('modalVerAsesores').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function liberarAsesor(cargaId, asesorId) {
            if (confirm('¿Estás seguro de que deseas liberar a este asesor de esta base de datos?')) {
                fetch(`index.php?action=liberar_asesor_base`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `carga_id=${cargaId}&asesor_id=${asesorId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Asesor liberado exitosamente. Se liberaron ${data.asignaciones_eliminadas} asignaciones.`);
                        cerrarModalVerAsesores();
                        location.reload(); // Recargar para actualizar las estadísticas
                    } else {
                        alert('Error al liberar el asesor: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al liberar el asesor');
                });
            }
        }

        function eliminarBaseDatos(cargaId) {
            if (confirm('¿Estás seguro de que deseas eliminar esta base de datos? Esta acción no se puede deshacer.')) {
                fetch(`index.php?action=eliminar_base_datos`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `carga_id=${cargaId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Base de datos eliminada exitosamente');
                        location.reload();
                    } else {
                        alert('Error al eliminar la base de datos: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la base de datos');
                });
            }
        }

        // Manejar envío del formulario de asignación
        document.getElementById('formAsignarAsesor').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('index.php?action=asignar_asesor_base', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Asesor asignado exitosamente. Se asignaron ${data.asignaciones_creadas} clientes.`);
                    cerrarModalAsignar();
                    location.reload(); // Recargar para actualizar las estadísticas
                } else {
                    alert('Error al asignar el asesor: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al asignar el asesor');
            });
        });

        // Cerrar modales al hacer clic fuera de ellos
        document.getElementById('modalAsignarAsesor').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalAsignar();
            }
        });

        document.getElementById('modalVerAsesores').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalVerAsesores();
            }
        });
    </script>

    <style>
        /* Centrar todo el contenido */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #495057;
        }
        
        .page-description {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .actions-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
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
        
        .cargas-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .carga-item {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            background: white;
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
        }
        
        .carga-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .carga-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #e9ecef;
        }
        
        .carga-title h3 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 20px;
        }
        
        .carga-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .carga-stats {
            display: flex;
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            min-width: 60px;
        }
        
        .stat-number {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #495057;
            line-height: 1;
        }
        
        .stat-label {
            display: block;
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        .carga-body {
            padding: 20px;
        }
        
        .carga-info {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c757d;
        }
        
        .info-value {
            color: #495057;
        }
        
        .asesores-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .asesores-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .asesores-header h4 {
            margin: 0;
            color: #495057;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .asesores-dropdown {
            position: relative;
        }
        
        .dropdown-toggle {
            width: 100%;
            padding: 10px 15px;
            background: white;
            border: 1px solid #ced4da;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }
        
        .dropdown-toggle:hover {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .dropdown-toggle i {
            transition: transform 0.2s;
        }
        
        .dropdown-toggle.active i {
            transform: rotate(180deg);
        }
        
        .dropdown-content {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ced4da;
            border-top: none;
            border-radius: 0 0 6px 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .loading-asesores {
            padding: 15px;
            text-align: center;
            color: #6c757d;
        }
        
        .asesores-list {
            padding: 10px;
        }
        
        .asesor-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            margin: 5px 0;
            background-color: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        
        .asesor-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .asesor-info i {
            color: #6c757d;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
        }
        
        .header-content h3 {
            margin: 0;
            font-size: 18px;
            color: #495057;
            flex: 1;
        }
        
        .carga-meta {
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: flex-end;
        }
        
        .carga-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }
        
        .info-icon {
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
        
        .info-content {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 14px;
            color: #495057;
            font-weight: 600;
        }
        
        .loading {
            color: #6c757d;
            font-style: italic;
        }
        
        .carga-actions {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .action-group {
            display: flex;
            gap: 8px;
        }
        
        .cargas-summary {
            margin-top: 30px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .summary-card {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
        }
        
        .summary-item {
            display: flex;
            align-items: center;
            gap: 15px;
            text-align: center;
        }
        
        .summary-item i {
            font-size: 32px;
            opacity: 0.8;
        }
        
        .summary-content {
            display: flex;
            flex-direction: column;
        }
        
        .summary-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            margin-bottom: 15px;
            color: #495057;
            font-size: 1.8rem;
        }
        
        .empty-state p {
            margin-bottom: 25px;
            font-size: 16px;
        }
        
        /* Centrar alertas */
        .alert {
            max-width: 800px;
            margin: 0 auto 20px auto;
            text-align: center;
        }

        /* Estilos para asesores asignados */
        .asesores-asignados {
            margin: 15px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .asesores-asignados h4 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .asesores-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .asesor-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            background-color: #e3f2fd;
            color: #1976d2;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .asesor-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background-color: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .asesor-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .asesor-info i {
            color: #6c757d;
        }

        /* Estilos para modales */
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
            max-width: 500px;
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
            font-size: 18px;
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
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .page-description {
                font-size: 1rem;
            }
            
            .actions-bar {
                flex-direction: column;
                align-items: center;
            }
            
            .cargas-list {
                max-width: 100%;
            }
            
            .carga-header {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }
            
            .carga-title {
                text-align: center;
            }
            
            .carga-meta {
                justify-content: center;
            }
            
            .carga-stats {
                justify-content: center;
            }
            
            .carga-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-group {
                justify-content: center;
            }
            
            .asesores-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .summary-card {
                grid-template-columns: 1fr;
            }
            
            .summary-item {
                justify-content: center;
            }
            
            .alert {
                margin: 0 10px 20px 10px;
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
