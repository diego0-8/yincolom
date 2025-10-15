<?php
// Archivo: views/asesor_dashboard.php
// Dashboard rediseñado del asesor - Limpio, centralizado y con barras desplegables
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php require_once 'shared_styles.php'; ?>
    <style>
        /* Estilos específicos para el dashboard del asesor */
        .dashboard-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Header del dashboard */
        .dashboard-header {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            padding: 25px;
            position: relative;
        }
        
        .dashboard-header h1 {
            color: #1f2937;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .dashboard-header p {
            color: #6b7280;
            font-size: 1rem;
            margin: 0;
        }
        
        /* Campana de notificaciones */
        .notification-bell {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .notification-bell:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #1f2937;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            border: 2px solid white;
        }
        
        .notification-bell.no-notifications {
            background: #6b7280;
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
        }
        
        .notification-bell.no-notifications:hover {
            box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
        }
        
        /* Selector de período */
        .periodo-selector {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .periodo-btn {
            padding: 8px 16px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #6b7280;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .periodo-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .periodo-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Grid de tarjetas principales */
        .stats-grid-main {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card-main {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            border-left: 4px solid #3b82f6;
            transition: transform 0.3s ease;
        }
        
        .stat-card-main:hover {
            transform: translateY(-5px);
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .stat-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .stat-icon {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        .stat-icon.primary { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .stat-icon.success { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-icon.warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-icon.info { background: linear-gradient(135deg, #06b6d4, #0891b2); }
        .stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3b82f6;
            margin: 10px 0;
        }
        
        .stat-description {
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        /* Secciones colapsables */
        .collapsible-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .section-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 20px 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }
        
        .section-header:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section-toggle {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        
        .section-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        .section-toggle.collapsed {
            transform: rotate(180deg);
        }
        
        .section-content {
            padding: 25px;
            display: block;
        }
        
        .section-content.collapsed {
            display: none;
        }
        
        /* Grid de tarjetas secundarias */
        .stats-grid-secondary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card-secondary {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card-secondary:hover {
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .stat-card-secondary .stat-number {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .stat-card-secondary .stat-description {
            font-size: 0.8rem;
        }
        
        /* Gráficos */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 20px;
        }
        
        .chart-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }
        
        .chart-title {
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
        }
        
        /* Botones de acción */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0 15px;
                margin: 20px auto;
            }
            
            .stats-grid-main {
                grid-template-columns: 1fr;
            }
            
            .stats-grid-secondary {
                grid-template-columns: 1fr;
            }
            
            .charts-container {
                grid-template-columns: 1fr;
            }
            
            .periodo-selector {
                flex-direction: column;
                align-items: center;
            }
            
            .periodo-btn {
                width: 100%;
                max-width: 200px;
            }
        }
        
        /* Estilos para el Modal de Llamadas Pendientes */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 90%;
            max-height: 80vh;
            overflow: hidden;
        }
        
        .modal-header {
            background: #f8fafc;
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #1f2937;
            font-size: 1.3rem;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6b7280;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: #e5e7eb;
            color: #374151;
        }
        
        .modal-body {
            padding: 25px;
            max-height: 60vh;
            overflow-y: auto;
        }
        
        /* Estilos para las Llamadas Pendientes */
        .llamadas-pendientes-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .llamada-pendiente-item {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .llamada-pendiente-item:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .llamada-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .cliente-info h4 {
            margin: 0 0 8px 0;
            color: #1f2937;
            font-size: 1.1rem;
        }
        
        .cliente-meta {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .fecha-programada {
            background: #3b82f6;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .tipificacion-actual {
            background: #fef3c7;
            color: #92400e;
            padding: 12px 15px;
            border-radius: 6px;
            border-left: 4px solid #f59e0b;
            margin-bottom: 15px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .comentarios {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 15px;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .acciones {
            text-align: right;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        .loading i {
            font-size: 2rem;
            margin-bottom: 15px;
            display: block;
            color: #3b82f6;
        }
        
        /* Responsive para el modal */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                max-height: 90vh;
            }
            
            .modal-header {
                padding: 15px 20px;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .llamada-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .fecha-programada {
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php 
    require_once 'shared_navbar.php';
    echo getNavbar('Dashboard', $_SESSION['user_role'] ?? '');
    ?>
    
    
    <div class="dashboard-container">
        <!-- Header Principal -->
        <div class="dashboard-header fade-in">
            <h1>📊 Dashboard del Asesor</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Asesor'); ?> - Gestiona tu rendimiento en tiempo real</p>
            
            <!-- Campana de Notificaciones -->
            <div class="notification-bell <?php echo ($datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0) > 0 ? '' : 'no-notifications'; ?>" 
                 onclick="mostrarModalLlamadasPendientes()" 
                 title="<?php echo ($datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0) > 0 ? 'Tienes llamadas pendientes para hoy' : 'No hay llamadas pendientes'; ?>">
                🔔
                                    <?php if (($datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0) > 0): ?>
                        <div class="notification-badge"><?php echo $datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="periodo-selector">
                <button class="periodo-btn <?php echo $datos_dashboard['periodo'] === 'dia' ? 'active' : ''; ?>" onclick="cambiarPeriodo('dia')">
                    📅 Hoy
                </button>
                <button class="periodo-btn <?php echo $datos_dashboard['periodo'] === 'semana' ? 'active' : ''; ?>" onclick="cambiarPeriodo('semana')">
                    📊 Esta Semana
                </button>
                <button class="periodo-btn <?php echo $datos_dashboard['periodo'] === 'mes' ? 'active' : ''; ?>" onclick="cambiarPeriodo('mes')">
                    📈 Este Mes
                </button>
                <button class="periodo-btn <?php echo $datos_dashboard['periodo'] === 'total' ? 'active' : ''; ?>" onclick="cambiarPeriodo('total')">
                    🏆 Histórico Total
                </button>
            </div>
        </div>
        
        <!-- Tarjetas Principales - Métricas Clave -->
        <div class="stats-grid-main fade-in">
            <div class="stat-card-main">
                <div class="stat-header">
                    <span class="stat-title">Total Clientes</span>
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $datos_dashboard['total_clientes'] ?? 0; ?></div>
                <div class="stat-description">Clientes asignados a tu portafolio</div>
            </div>
            
            <div class="stat-card-main">
                <div class="stat-header">
                    <span class="stat-title">Clientes Gestionados</span>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $datos_dashboard['clientes_gestionados'] ?? 0; ?></div>
                <div class="stat-description">Clientes que ya han sido contactados</div>
            </div>
            
            <div class="stat-card-main">
                <div class="stat-header">
                    <span class="stat-title">Tareas Pendientes</span>
                    <div class="stat-icon warning">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $datos_dashboard['total_tareas_pendientes'] ?? 0; ?></div>
                <div class="stat-description">Tareas asignadas por tu coordinador</div>
            </div>
            
            <div class="stat-card-main">
                <div class="stat-header">
                    <span class="stat-title">Clientes en Tareas</span>
                    <div class="stat-icon info">
                        <i class="fas fa-list-check"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo $datos_dashboard['clientes_pendientes_tareas'] ?? 0; ?></div>
                <div class="stat-description">Clientes pendientes de gestionar en tareas</div>
            </div>
            
            <div class="stat-card-main">
                <div class="stat-header">
                    <span class="stat-title">Total Recaudado</span>
                    <div class="stat-icon purple">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="stat-number">$<?php echo number_format($datos_dashboard['total_recaudado'] ?? 0, 0, ',', '.'); ?></div>
                <div class="stat-description">Monto total de ventas realizadas</div>
            </div>
        </div>
        
        <!-- Sección: Métricas de Rendimiento del Período -->
        <div class="collapsible-section fade-in">
            <div class="section-header" onclick="toggleSection('rendimiento')">
                <div class="section-title">
                    📈 Métricas de Rendimiento
                    <span style="font-size: 0.9rem; color: #6b7280; font-weight: 400;">(<?php echo ucfirst($datos_dashboard['periodo']); ?>)</span>
                </div>
                <button class="section-toggle" id="toggle-rendimiento">▼</button>
            </div>
            <div class="section-content" id="content-rendimiento">
                <div class="stats-grid-secondary">
                    <div class="stat-card-secondary">
                        <div class="stat-number"><?php echo $metricas['ventas_exitosas'] ?? 0; ?></div>
                        <div class="stat-description">Ventas Exitosas</div>
                    </div>
                    <div class="stat-card-secondary">
                        <div class="stat-number"><?php echo $metricas['total_llamadas'] ?? 0; ?></div>
                        <div class="stat-description">Total Llamadas</div>
                    </div>
                    <div class="stat-card-secondary">
                        <div class="stat-number"><?php echo $metricas['tasa_conversion'] ?? 0; ?>%</div>
                        <div class="stat-description">Tasa de Conversión</div>
                    </div>
                    <div class="stat-card-secondary">
                        <div class="stat-number"><?php echo $metricas['tasa_contacto_efectivo'] ?? 0; ?>%</div>
                        <div class="stat-description">Tasa de Contacto</div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <!-- Sección: Análisis Visual -->
        <div class="collapsible-section fade-in">
            <div class="section-header" onclick="toggleSection('graficos')">
                <div class="section-title">
                    📊 Análisis Visual
                    <span style="font-size: 0.9rem; color: #6b7280; font-weight: 400;">(<?php echo ucfirst($datos_dashboard['periodo']); ?>)</span>
                </div>
                <button class="section-toggle" id="toggle-graficos">▼</button>
            </div>
            <div class="section-content" id="content-graficos">
                <div class="charts-container">
                    <div class="chart-card">
                        <div class="chart-title">Tipificaciones por Resultado (Período)</div>
                        <canvas id="tipificacionesChart"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title">Gestiones por Día</div>
                        <canvas id="gestionesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        
        
        <!-- Sección: Clientes con Seguimiento -->
        <?php if (!empty($clientesSeguimiento)): ?>
        <div class="collapsible-section fade-in">
            <div class="section-header" onclick="toggleSection('seguimiento')">
                <div class="section-title">
                    📅 Clientes con Seguimiento Agendado
                    <span style="font-size: 0.9rem; color: #6b7280; font-weight: 400;">(<?php echo count($clientesSeguimiento); ?>)</span>
                </div>
                <button class="section-toggle" id="toggle-seguimiento">▼</button>
            </div>
            <div class="section-content" id="content-seguimiento">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                <th>Última Gestión</th>
                                <th>Comentarios</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientesSeguimiento as $cliente): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cliente['nombre'] ?? 'N/A'); ?></strong><br><small><?php echo htmlspecialchars($cliente['cedula'] ?? 'N/A'); ?></small></td>
                                <td><?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?><br><small><?php echo htmlspecialchars($cliente['celular2'] ?? 'N/A'); ?></small></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($cliente['fecha_gestion'])); ?></td>
                                <td><?php echo htmlspecialchars(substr($cliente['comentarios'] ?? '', 0, 50)) . (strlen($cliente['comentarios'] ?? '') > 50 ? '...' : ''); ?></td>
                                <td>
                                    <a href="index.php?action=gestionar_cliente&id=<?php echo $cliente['asignacion_id']; ?>" class="btn btn-primary">Gestionar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Sección: Últimas Gestiones -->
        <div class="collapsible-section fade-in">
            <div class="section-header" onclick="toggleSection('gestiones')">
                <div class="section-title">
                    🔄 Últimas Gestiones
                    <span style="font-size: 0.9rem; color: #6b7280; font-weight: 400;">(Últimas 5)</span>
                </div>
                <button class="section-toggle" id="toggle-gestiones">▼</button>
            </div>
            <div class="section-content" id="content-gestiones">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Resultado</th>
                                <th>Fecha</th>
                                <th>Duración</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ultimasGestiones)): ?>
                                <?php foreach ($ultimasGestiones as $gestion): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($gestion['cliente_nombre'] ?? 'Cliente'); ?></strong><br><small><?php echo htmlspecialchars($gestion['cedula'] ?? 'N/A'); ?></small></td>
                                    <td><?php echo htmlspecialchars($gestion['tipo_gestion'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if (!empty($gestion['resultado'])): ?>
                                            <span style="color: #28a745;"><?php echo htmlspecialchars($gestion['resultado'] ?? ''); ?></span>
                                        <?php else: ?>
                                            <span style="color: #666;">Sin resultado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo !empty($gestion['fecha_gestion']) ? date('d/m/Y H:i', strtotime($gestion['fecha_gestion'])) : 'N/A'; ?></td>
                                    <td><?php echo !empty($gestion['duracion_llamada']) ? round($gestion['duracion_llamada'], 1) . ' min' : '-'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center; color: #666;">No hay gestiones registradas</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Botones de Acción -->
        <div class="action-buttons fade-in">
            <a href="index.php?action=mis_tareas" class="btn btn-primary">
                <i class="fas fa-tasks"></i> Ver Mis Tareas
            </a>
            <a href="index.php?action=mis_clientes" class="btn btn-secondary">
                <i class="fas fa-list"></i> Ver Mis Clientes
            </a>
            <a href="index.php?action=dashboard&periodo=<?php echo $datos_dashboard['periodo']; ?>" class="btn btn-success">
                <i class="fas fa-sync"></i> Actualizar Dashboard
            </a>
        </div>
    </div>
    
    <script>
        // Función para cambiar período
        function cambiarPeriodo(periodo) {
            window.location.href = 'index.php?action=dashboard&periodo=' + periodo;
        }
        
        // Función para alternar secciones
        function toggleSection(sectionName) {
            const content = document.getElementById('content-' + sectionName);
            const toggle = document.getElementById('toggle-' + sectionName);
            
            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                toggle.classList.remove('collapsed');
                toggle.textContent = '▼';
            } else {
                content.classList.add('collapsed');
                toggle.classList.add('collapsed');
                toggle.textContent = '▶';
            }
        }
        
        // Gráfico de tipificaciones
        const tipificacionesCtx = document.getElementById('tipificacionesChart').getContext('2d');
        new Chart(tipificacionesCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($tipificaciones, 'resultado')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($tipificaciones, 'cantidad')); ?>,
                    backgroundColor: [
                        '#28a745', '#20c997', '#17a2b8', '#007bff',
                        '#6f42c1', '#e83e8c', '#dc3545', '#fd7e14',
                        '#ffc107', '#6c757d', '#343a40'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
        
        // Gráfico de tipificaciones históricas
        const tipificacionesHistoricasCtx = document.getElementById('tipificacionesHistoricasChart').getContext('2d');
        new Chart(tipificacionesHistoricasCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($datos_dashboard['tipificaciones_totales'] ?? [], 'resultado')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($datos_dashboard['tipificaciones_totales'] ?? [], 'cantidad')); ?>,
                    backgroundColor: [
                        '#28a745', '#20c997', '#17a2b8', '#007bff',
                        '#6f42c1', '#e83e8c', '#dc3545', '#fd7e14',
                        '#ffc107', '#6c757d', '#343a40'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
        
        // Gráfico de ventas históricas
        const ventasHistoricasCtx = document.getElementById('ventasHistoricasChart').getContext('2d');
        new Chart(ventasHistoricasCtx, {
            type: 'bar',
            data: {
                labels: ['Ventas Exitosas', 'Total Llamadas', 'Tasa Conversión'],
                datasets: [{
                    label: 'Métricas Históricas',
                    data: [
                        <?php echo $datos_dashboard['metricas_totales']['ventas_exitosas'] ?? 0; ?>,
                        <?php echo $datos_dashboard['metricas_totales']['total_llamadas'] ?? 0; ?>,
                        <?php echo $datos_dashboard['metricas_totales']['tasa_conversion'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(0, 123, 255, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(0, 123, 255, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
        
        // Gráfico de gestiones por día
        const gestionesCtx = document.getElementById('gestionesChart').getContext('2d');
        new Chart(gestionesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($gestionesPorDia, 'fecha')); ?>,
                datasets: [{
                    label: 'Total Gestiones',
                    data: <?php echo json_encode(array_column($gestionesPorDia, 'total_gestiones')); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Contactos Efectivos',
                    data: <?php echo json_encode(array_column($gestionesPorDia, 'contactos_efectivos')); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Ventas',
                    data: <?php echo json_encode(array_column($gestionesPorDia, 'ventas')); ?>,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
        
        // Inicializar todas las secciones como expandidas y configurar event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Por defecto, mantener solo las métricas principales visibles
            // Las demás secciones se pueden colapsar

            // Configurar event listener para cerrar modal al hacer clic fuera
            const modalLlamadasPendientes = document.getElementById('modalLlamadasPendientesAsesor');
            if (modalLlamadasPendientes) {
                modalLlamadasPendientes.addEventListener('click', function(e) {
                    if (e.target === this) {
                        cerrarModalLlamadasPendientes();
                    }
                });
            } else {
                console.error('asesor_dashboard: modalLlamadasPendientesAsesor element not found after DOMContentLoaded');
            }
            
            // Cargar estadísticas de productos
            cargarEstadisticasProductos();
        });
        
        // Función para cargar estadísticas de productos
        function cargarEstadisticasProductos() {
            fetch('index.php?action=obtener_estadisticas_productos')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('total-productos').textContent = data.estadisticas.total_productos || 0;
                        document.getElementById('total-recaudado-productos').textContent = 
                            '$' + (data.estadisticas.total_recaudado || 0).toLocaleString('es-CO');
                    }
                })
                .catch(error => {
                    console.error('Error cargando estadísticas de productos:', error);
                });
        }

        // Función para mostrar llamadas pendientes
        function mostrarLlamadasPendientes() {
            const llamadasPendientes = <?php echo json_encode($datos_dashboard['llamadas_pendientes'] ?? []); ?>;

            if (llamadasPendientes.length === 0) {
                alert('No tienes llamadas pendientes para hoy.');
                return;
            }

            let mensaje = '📞 Llamadas Pendientes para Hoy:\n\n';
            llamadasPendientes.forEach((llamada, index) => {
                const fecha = new Date(llamada.proxima_fecha || new Date()).toLocaleString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                mensaje += `${index + 1}. ${llamada.cliente_nombre || 'Cliente'}\n`;
                mensaje += `   📱 ${llamada.telefono || llamada.celular2 || 'Sin teléfono'}\n`;
                mensaje += `   ⏰ ${fecha}\n`;
                mensaje += `   📝 ${llamada.comentarios || 'Sin comentarios'}\n\n`;
            });

            mensaje += '💡 Organiza tu tiempo para realizar estas llamadas en los horarios acordados.';
            alert(mensaje);
        }

        // Función para mostrar modal de llamadas pendientes
        function mostrarModalLlamadasPendientes() {
            const llamadasPendientes = <?php echo json_encode($datos_dashboard['llamadas_pendientes'] ?? []); ?>;

            if (llamadasPendientes.length === 0) {
                alert('No tienes llamadas pendientes para hoy.');
                return;
            }

            // Mostrar el modal
            const modal = document.getElementById('modalLlamadasPendientesAsesor');
            if (modal) {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';

                // Cargar contenido del modal
                mostrarLlamadasPendientesEnModal(llamadasPendientes);
            } else {
                console.error('asesor_dashboard: Cannot show modal - element not found');
            }
        }

        function cerrarModalLlamadasPendientes() {
            const modal = document.getElementById('modalLlamadasPendientesAsesor');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function mostrarLlamadasPendientesEnModal(llamadasPendientes) {
            const contenidoModal = document.getElementById('contenidoLlamadasPendientes');

            if (!contenidoModal) {
                console.error('asesor_dashboard: contenidoLlamadasPendientes element not found');
                return;
            }

            let html = '<div class="llamadas-pendientes-container">';

                            llamadasPendientes.forEach((llamada, index) => {
                    const fecha = new Date(llamada.proxima_fecha || new Date()).toLocaleString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    html += `
                        <div class="llamada-pendiente-item">
                            <div class="llamada-header">
                                <div class="cliente-info">
                                    <h4>👤 ${llamada.cliente_nombre || 'Cliente'}</h4>
                                    <div class="cliente-meta">
                                        📱 ${llamada.telefono || llamada.celular2 || 'Sin teléfono'}
                                    </div>
                                </div>
                                <div class="fecha-programada">
                                    ⏰ ${fecha}
                                </div>
                            </div>
                            <div class="tipificacion-actual">
                                🏷️ <strong>Tipificación:</strong> ${llamada.resultado || 'N/A'}
                            </div>
                            <div class="comentarios">
                                💬 <strong>Comentarios:</strong><br>
                                ${llamada.comentarios || 'Sin comentarios específicos'}
                            </div>
                            <div class="acciones">
                                <a href="index.php?action=gestionar_cliente&id=${llamada.cliente_id}"
                                   class="btn btn-primary btn-sm">
                                    📞 Gestionar Cliente
                                </a>
                            </div>
                        </div>
                    `;
                });

            html += '</div>';
            contenidoModal.innerHTML = html;
        }
    </script>
    
    <!-- Modal de Llamadas Pendientes -->
    <div id="modalLlamadasPendientesAsesor" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔔 Llamadas Pendientes para Hoy</h3>
                <button type="button" class="modal-close" onclick="cerrarModalLlamadasPendientes()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="contenidoLlamadasPendientes">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        Cargando llamadas pendientes...
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
</html>