<?php
// Archivo: views/coordinador_detalle_cliente.php
// Vista para que el coordinador vea los detalles de un cliente específico
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
    echo getNavbar('Detalle del Cliente', $_SESSION['user_role'] ?? ''); 
    ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📋 Detalle del Cliente</h1>
            <p class="page-description">Información completa y historial de gestiones</p>
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

        <!-- Información del Cliente -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user"></i> Información del Cliente</h3>
            </div>
            <div class="card-body">
                <div class="cliente-info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Nombre</span>
                            <span class="info-value"><?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Cédula</span>
                            <span class="info-value"><?php echo htmlspecialchars($cliente['cedula'] ?? ''); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Teléfono</span>
                            <span class="info-value"><?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Celular</span>
                            <span class="info-value"><?php echo htmlspecialchars($cliente['celular2'] ?? ''); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Ciudad</span>
                            <span class="info-value"><?php echo htmlspecialchars($cliente['ciudad'] ?? ''); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Fecha de Carga</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($cliente['fecha_carga'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Estado de Asignación -->
                <div class="asignacion-status">
                    <h4><i class="fas fa-user-check"></i> Estado de Asignación</h4>
                    <?php if ($asesor): ?>
                        <div class="asesor-info">
                            <div class="asesor-avatar">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="asesor-details">
                                <strong>Asesor Asignado:</strong> <?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?>
                                <br>
                                <small>Email: <?php echo htmlspecialchars($asesor['email'] ?? ''); ?></small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-asesor">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Este cliente no tiene asesor asignado</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Historial de Gestiones -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Historial de Gestiones</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($gestiones)): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Tipo de Gestión</th>
                                    <th>Resultado</th>
                                    <th>Comentarios</th>
                                    <th>Duración</th>
                                    <th>Monto Venta</th>
                                    <th>Asesor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gestiones as $gestion): ?>
                                    <tr>
                                        <td>
                                            <div class="fecha-gestion">
                                                <div class="fecha"><?php echo date('d/m/Y', strtotime($gestion['fecha_gestion'])); ?></div>
                                                <div class="hora"><?php echo date('H:i', strtotime($gestion['fecha_gestion'])); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo htmlspecialchars($gestion['tipo_gestion'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($gestion['resultado'])): ?>
                                                <?php
                                                $resultadoClass = 'badge-secondary';
                                                if (in_array($gestion['resultado'], ['Venta Exitosa', 'Venta en Frío', 'Venta con Seguimiento', 'Venta Cruzada'])) {
                                                    $resultadoClass = 'badge-success';
                                                } elseif (in_array($gestion['resultado'], ['Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica', 'Necesita Pensarlo'])) {
                                                    $resultadoClass = 'badge-warning';
                                                } elseif (in_array($gestion['resultado'], ['No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado'])) {
                                                    $resultadoClass = 'badge-info';
                                                }
                                                ?>
                                                <span class="badge <?php echo $resultadoClass; ?>">
                                                    <?php echo htmlspecialchars($gestion['resultado'] ?? ''); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Sin resultado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="comentarios">
                                                <?php if (!empty($gestion['comentarios'])): ?>
                                                    <?php echo htmlspecialchars(substr($gestion['comentarios'], 0, 80)); ?>
                                                    <?php if (strlen($gestion['comentarios']) > 80): ?>
                                                        <span class="comentarios-more">...</span>
                                                        <div class="comentarios-full" style="display: none;">
                                                            <?php echo nl2br(htmlspecialchars($gestion['comentarios'] ?? '')); ?>
                                                        </div>
                                                        <button type="button" class="btn-link" onclick="toggleComentarios(this)">
                                                            Ver más
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($gestion['duracion_llamada'])): ?>
                                                <span class="duracion">
                                                    <?php echo round($gestion['duracion_llamada'], 1); ?> min
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($gestion['monto_venta'])): ?>
                                                <span class="monto-venta">
                                                    $<?php echo number_format($gestion['monto_venta'], 0, ',', '.'); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($gestion['asesor_nombre'])): ?>
                                                <span class="asesor-nombre">
                                                    <?php echo htmlspecialchars($gestion['asesor_nombre'] ?? ''); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-phone-slash"></i>
                        <h4>Sin gestiones registradas</h4>
                        <p>Este cliente aún no ha sido contactado por ningún asesor.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumen de Actividad -->
        <?php if (!empty($gestiones)): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Resumen de Actividad</h3>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="stat-content">
                                <span class="stat-number"><?php echo count($gestiones); ?></span>
                                <span class="stat-label">Total Gestiones</span>
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-content">
                                <span class="stat-number"><?php echo date('d/m/Y', strtotime($gestiones[0]['fecha_gestion'])); ?></span>
                                <span class="stat-label">Última Gestión</span>
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-content">
                                <?php
                                $totalVentas = 0;
                                foreach ($gestiones as $gestion) {
                                    if (!empty($gestion['monto_venta'])) {
                                        $totalVentas += $gestion['monto_venta'];
                                    }
                                }
                                ?>
                                <span class="stat-number">$<?php echo number_format($totalVentas, 0, ',', '.'); ?></span>
                                <span class="stat-label">Total Ventas</span>
                            </div>
                        </div>
                        
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <?php
                                $totalDuracion = 0;
                                $gestionesConDuracion = 0;
                                foreach ($gestiones as $gestion) {
                                    if (!empty($gestion['duracion_llamada'])) {
                                        $totalDuracion += $gestion['duracion_llamada'];
                                        $gestionesConDuracion++;
                                    }
                                }
                                $promedioDuracion = $gestionesConDuracion > 0 ? $totalDuracion / $gestionesConDuracion : 0;
                                ?>
                                <span class="stat-number"><?php echo round($promedioDuracion, 1); ?> min</span>
                                <span class="stat-label">Promedio Llamada</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Botones de Acción -->
        <div class="card">
            <div class="card-body text-center">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="index.php?action=tareas_coordinador" class="btn btn-primary">
                    <i class="fas fa-tasks"></i> Gestionar Tareas
                </a>
                <a href="index.php?action=dashboard" class="btn btn-outline-primary">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    <style>
        .cliente-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-label {
            display: block;
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .info-value {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #2d3748;
        }
        
        .asignacion-status {
            margin-top: 20px;
            padding: 20px;
            background: #e3f2fd;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }
        
        .asignacion-status h4 {
            margin: 0 0 15px 0;
            color: #1565c0;
        }
        
        .asesor-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .asesor-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .asesor-details {
            color: #1565c0;
        }
        
        .no-asesor {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #dc3545;
            font-weight: 500;
        }
        
        .fecha-gestion {
            text-align: center;
        }
        
        .fecha {
            font-weight: 600;
            color: #2d3748;
        }
        
        .hora {
            font-size: 12px;
            color: #6c757d;
        }
        
        .comentarios {
            max-width: 200px;
        }
        
        .comentarios-full {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .btn-link {
            background: none;
            border: none;
            color: #007bff;
            text-decoration: underline;
            cursor: pointer;
            font-size: 12px;
            padding: 0;
        }
        
        .duracion, .monto-venta {
            font-weight: 600;
            color: #28a745;
        }
        
        .asesor-nombre {
            font-weight: 500;
            color: #6c757d;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-icon {
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
        
        .stat-content {
            flex: 1;
        }
        
        .stat-number {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .stat-label {
            display: block;
            font-size: 14px;
            color: #718096;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h4 {
            margin-bottom: 10px;
            color: #495057;
        }
        
        @media (max-width: 768px) {
            .cliente-info-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    <script>
        function toggleComentarios(button) {
            const comentariosFull = button.previousElementSibling;
            const isHidden = comentariosFull.style.display === 'none';
            
            comentariosFull.style.display = isHidden ? 'block' : 'none';
            button.textContent = isHidden ? 'Ver menos' : 'Ver más';
        }
    </script>
</body>
</html>
