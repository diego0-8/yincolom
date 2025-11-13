<?php
// Archivo: views/coordinador_detalle_gestion_asesor.php
// Vista para que el coordinador vea los detalles de gestión de un cliente específico de un asesor
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
    echo getNavbar('Tareas', $_SESSION['user_role'] ?? '');
    ?>
    
    <div class="main-container">
        <!-- Encabezado con botón de regreso -->
        <div class="header-section">
            <div class="header-content">
                <h1><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($page_title); ?></h1>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

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
                    
                    <?php if (!empty($cliente['celular2'])): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Celular 2</span>
                            <span class="info-value"><?php echo htmlspecialchars($cliente['celular2'] ?? ''); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Ciudad</span>
                            <span class="info-value"><?php echo htmlspecialchars($cliente['ciudad'] ?? 'No especificada'); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Fecha de Carga</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($cliente['fecha_carga'] ?? 'now')); ?></span>
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

        <!-- Historial de Gestiones del Asesor -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Historial de Gestiones del Asesor</h3>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gestiones as $gestion): ?>
                                    <tr class="gestion-row <?php echo $this->getGestionRowClass($gestion['resultado']); ?>">
                                        <td>
                                            <strong><?php echo date('d/m/Y', strtotime($gestion['fecha_gestion'])); ?></strong>
                                            <br>
                                            <small><?php echo date('H:i', strtotime($gestion['fecha_gestion'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="tipo-gestion">
                                                <?php echo htmlspecialchars($gestion['tipo_gestion'] ?? 'Llamada'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($gestion['resultado'])): ?>
                                                <span class="resultado-badge resultado-<?php echo $this->getResultadoClass($gestion['resultado']); ?>">
                                                    <?php echo htmlspecialchars($gestion['resultado'] ?? ''); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="resultado-badge resultado-sin-resultado">
                                                    Sin resultado
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($gestion['comentarios'])): ?>
                                                <div class="comentarios-container">
                                                    <div class="comentarios-preview">
                                                        <?php echo htmlspecialchars(substr($gestion['comentarios'], 0, 100)); ?>
                                                        <?php if (strlen($gestion['comentarios']) > 100): ?>
                                                            <span class="comentarios-more">...</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (strlen($gestion['comentarios']) > 100): ?>
                                                        <div class="comentarios-full" style="display: none;">
                                                            <?php echo nl2br(htmlspecialchars($gestion['comentarios'] ?? '')); ?>
                                                        </div>
                                                        <button class="btn btn-sm btn-link toggle-comentarios" onclick="toggleComentarios(this)">
                                                            Ver más
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Sin comentarios</span>
                                            <?php endif; ?>
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
                                                    $<?php echo number_format($gestion['monto_venta'], 2, ',', '.'); ?>
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
                    
                    <!-- Resumen de Gestiones -->
                    <div class="resumen-gestiones">
                        <h4><i class="fas fa-chart-bar"></i> Resumen de Gestiones</h4>
                        <div class="resumen-grid">
                            <div class="resumen-item">
                                <div class="resumen-number"><?php echo count($gestiones); ?></div>
                                <div class="resumen-label">Total de Gestiones</div>
                            </div>
                            
                            <?php
                            $ventas = array_filter($gestiones, function($g) {
                                return in_array($g['resultado'], ['Venta Exitosa', 'Venta en Frío', 'Venta con Seguimiento', 'Venta Cruzada']);
                            });
                            ?>
                            <div class="resumen-item">
                                <div class="resumen-number"><?php echo count($ventas); ?></div>
                                <div class="resumen-label">Ventas Realizadas</div>
                            </div>
                            
                            <?php
                            $contactos_efectivos = array_filter($gestiones, function($g) {
                                return !in_array($g['resultado'], ['No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado']);
                            });
                            ?>
                            <div class="resumen-item">
                                <div class="resumen-number"><?php echo count($contactos_efectivos); ?></div>
                                <div class="resumen-label">Contactos Efectivos</div>
                            </div>
                            
                            <?php
                            $total_ventas = array_sum(array_column($gestiones, 'monto_venta'));
                            ?>
                            <div class="resumen-item">
                                <div class="resumen-number">$<?php echo number_format($total_ventas, 2, ',', '.'); ?></div>
                                <div class="resumen-label">Total Ventas</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-gestiones">
                        <i class="fas fa-info-circle"></i>
                        <p>Este asesor aún no ha realizado gestiones con este cliente.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-content h1 {
            margin: 0;
            font-size: 1.8em;
        }
        
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
            border-left: 4px solid #667eea;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
        }
        
        .info-content {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.9em;
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
        }
        
        .asignacion-status {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }
        
        .asignacion-status h4 {
            margin: 0 0 15px 0;
            color: #1976d2;
        }
        
        .asesor-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .asesor-avatar {
            width: 50px;
            height: 50px;
            background: #2196f3;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
        }
        
        .asesor-details {
            color: #1976d2;
        }
        
        .no-asesor {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #f57c00;
        }
        
        .table-container {
            overflow-x: auto;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .gestion-row.venta {
            background: #f1f8e9;
        }
        
        .gestion-row.rechazado {
            background: #fff3e0;
        }
        
        .gestion-row.sin-contacto {
            background: #fce4ec;
        }
        
        .resultado-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
        }
        
        .resultado-venta {
            background: #c8e6c9;
            color: #2e7d32;
        }
        
        .resultado-rechazo {
            background: #ffccbc;
            color: #d84315;
        }
        
        .resultado-sin-contacto {
            background: #f8bbd9;
            color: #c2185b;
        }
        
        .resultado-seguimiento {
            background: #b3e5fc;
            color: #0277bd;
        }
        
        .resultado-sin-resultado {
            background: #f5f5f5;
            color: #666;
        }
        
        .comentarios-container {
            max-width: 300px;
        }
        
        .comentarios-preview {
            margin-bottom: 5px;
        }
        
        .comentarios-more {
            color: #667eea;
            font-weight: bold;
        }
        
        .comentarios-full {
            margin-bottom: 10px;
            white-space: pre-line;
        }
        
        .toggle-comentarios {
            padding: 0;
            font-size: 0.8em;
            color: #667eea;
            text-decoration: none;
        }
        
        .duracion, .monto-venta {
            font-weight: 600;
            color: #2e7d32;
        }
        
        .resumen-gestiones {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .resumen-gestiones h4 {
            margin: 0 0 20px 0;
            color: #333;
        }
        
        .resumen-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .resumen-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .resumen-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .resumen-label {
            color: #666;
            font-size: 0.9em;
        }
        
        .no-gestiones {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-gestiones i {
            font-size: 3em;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .no-gestiones p {
            margin: 0;
            font-size: 1.1em;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            color: white;
        }
    </style>

    <script>
        function toggleComentarios(button) {
            const row = button.closest('tr');
            const preview = row.querySelector('.comentarios-preview');
            const full = row.querySelector('.comentarios-full');
            
            if (full.style.display === 'none') {
                preview.style.display = 'none';
                full.style.display = 'block';
                button.textContent = 'Ver menos';
            } else {
                preview.style.display = 'block';
                full.style.display = 'none';
                button.textContent = 'Ver más';
            }
        }
    </script>
</body>
</html>
