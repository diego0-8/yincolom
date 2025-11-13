<?php
// Archivo: views/coordinador_ver_gestion_asesor.php
// Vista para mostrar la gestión de un asesor específico
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
    echo getNavbar('Gestión del Asesor', $_SESSION['user_role'] ?? '');
    ?>
    
    <div class="main-container">
        <!-- Encabezado -->
        <div class="card">
            <div class="card-header">
                📊 Gestión del Asesor: <?php echo htmlspecialchars($asesor['nombre_completo'] ?? 'N/A'); ?>
            </div>
            <div class="card-body">
                <h2>Detalles de la Gestión</h2>
                <p>Información detallada sobre el rendimiento y gestión del asesor.</p>
                
                <div style="margin-bottom: 20px;">
                    <a href="index.php?action=localizacion_equipo" class="btn btn-secondary">
                        ← Volver a Localización
                    </a>
                    <button class="btn btn-primary" onclick="mostrarDetallesAsesor(<?php echo $asesor['id']; ?>)">
                        📊 Ver Modal Detallado
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Métricas del Asesor -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $metricas['total_clientes'] ?? 0; ?></div>
                <div class="stat-label">Total Clientes</div>
                <p class="mt-20">Asignados al asesor</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $metricas['total_gestiones'] ?? 0; ?></div>
                <div class="stat-label">Gestiones Realizadas</div>
                <p class="mt-20">Llamadas y contactos</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $metricas['contactos_efectivos'] ?? 0; ?></div>
                <div class="stat-label">Contactos Efectivos</div>
                <p class="mt-20">Clientes contactados</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $metricas['ventas_exitosas'] ?? 0; ?></div>
                <div class="stat-label">Ventas Exitosas</div>
                <p class="mt-20">Ventas concretadas</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $metricas['tasa_conversion'] ?? 0; ?>%</div>
                <div class="stat-label">Tasa de Conversión</div>
                <p class="mt-20">Efectividad en ventas</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">$<?php echo number_format($metricas['total_ventas_monto'] ?? 0, 0, ',', '.'); ?></div>
                <div class="stat-label">Total Ventas</div>
                <p class="mt-20">Monto total generado</p>
            </div>
        </div>
        
        <!-- Información del Asesor -->
        <div class="card">
            <div class="card-header">
                👤 Información del Asesor
            </div>
            <div class="card-body">
                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <h4>Datos Personales</h4>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($asesor['nombre_completo'] ?? 'N/A'); ?></p>
                        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($asesor['username'] ?? 'N/A'); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($asesor['email'] ?? 'N/A'); ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="badge <?php echo ($asesor['estado'] ?? '') === 'Activo' ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo htmlspecialchars($asesor['estado'] ?? 'N/A'); ?>
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <h4>Rendimiento</h4>
                        <p><strong>Clientes Asignados:</strong> <?php echo count($clientes); ?></p>
                        <p><strong>Gestiones Realizadas:</strong> <?php echo count($gestiones); ?></p>
                        <p><strong>Última Gestión:</strong> 
                            <?php echo !empty($gestiones) ? date('d/m/Y H:i', strtotime($gestiones[0]['fecha_gestion'] ?? 'now')) : 'N/A'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Últimas Gestiones -->
        <div class="card">
            <div class="card-header">
                📋 Últimas Gestiones
            </div>
            <div class="card-body">
                <?php if (!empty($gestiones)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Resultado</th>
                                    <th>Comentarios</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($gestiones, 0, 10) as $gestion): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($gestion['cliente_nombre'] ?? 'N/A'); ?></td>
                                        <td><?php echo $gestion['fecha_gestion'] ? date('d/m/Y H:i', strtotime($gestion['fecha_gestion'])) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($gestion['tipo_gestion'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($gestion['resultado'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($gestion['comentarios'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($gestiones) > 10): ?>
                        <div style="text-align: center; margin-top: 20px;">
                            <p class="text-muted">Mostrando las últimas 10 gestiones de <?php echo count($gestiones); ?> totales</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <strong>No hay gestiones registradas.</strong> Este asesor aún no ha realizado gestiones.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Clientes Asignados -->
        <div class="card">
            <div class="card-header">
                👥 Clientes Asignados
            </div>
            <div class="card-body">
                <?php if (!empty($clientes)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cédula</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($clientes, 0, 15) as $cliente): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cliente['nombre'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['cedula'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if (isset($cliente['gestionado']) && $cliente['gestionado']): ?>
                                                <span class="badge badge-success">Gestionado</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($clientes) > 15): ?>
                        <div style="text-align: center; margin-top: 20px;">
                            <p class="text-muted">Mostrando los primeros 15 clientes de <?php echo count($clientes); ?> totales</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <strong>No hay clientes asignados.</strong> Este asesor no tiene clientes asignados actualmente.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal de Detalles del Asesor (incluido desde el dashboard) -->
    <div id="modalDetalles" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📊 Detalles del Asesor</h2>
                <span class="close" onclick="cerrarModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="modalLoading" class="loading">
                    <p>Cargando información del asesor...</p>
                </div>
                <div id="modalContent" style="display: none;">
                    <!-- El contenido del modal se cargará dinámicamente -->
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Funciones del modal (simplificadas para esta vista)
        function mostrarDetallesAsesor(asesorId) {
            document.getElementById('modalDetalles').style.display = 'block';
            document.getElementById('modalLoading').style.display = 'block';
            document.getElementById('modalContent').style.display = 'none';
            
            // Cargar detalles del asesor
            const url = `index.php?action=get_detalles_asesor&asesor_id=${asesorId}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    
                    mostrarContenidoModal(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del asesor');
                });
        }
        
        function mostrarContenidoModal(data) {
            document.getElementById('modalLoading').style.display = 'none';
            document.getElementById('modalContent').style.display = 'block';
            
            // Actualizar título del modal
            document.querySelector('.modal-title').textContent = `📊 ${data.asesor.nombre_completo}`;
            
            // Mostrar contenido del modal (simplificado)
            document.getElementById('modalContent').innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <h3>Modal de Detalles del Asesor</h3>
                    <p>Este modal muestra información detallada del asesor con filtros y gestión completa.</p>
                    <p>Para ver la funcionalidad completa, ve al dashboard del coordinador.</p>
                    <button onclick="cerrarModal()" class="btn btn-primary">Cerrar</button>
                </div>
            `;
        }
        
        function cerrarModal() {
            document.getElementById('modalDetalles').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modalDetalles');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
    
    <?php require_once 'shared_footer.php'; ?>
</body>
</html>

