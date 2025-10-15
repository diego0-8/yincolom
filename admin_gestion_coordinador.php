<?php
// Archivo: views/admin_gestion_coordinador.php
// Vista para que el administrador vea la gestión de un coordinador específico
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php require_once 'shared_styles.php'; ?>
    <style>
        .gestion-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background: #f8fafc;
            min-height: 100vh;
        }
        
        .coordinador-header {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .coordinador-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        
        .coordinador-name {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .coordinador-role {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .coordinador-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            background: <?php echo $coordinador['estado'] === 'Activo' ? '#dcfce7' : '#fef2f2'; ?>;
            color: <?php echo $coordinador['estado'] === 'Activo' ? '#166534' : '#dc2626'; ?>;
            border: 1px solid <?php echo $coordinador['estado'] === 'Activo' ? '#bbf7d0' : '#fecaca'; ?>;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .asesores-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .asesores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .asesor-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .asesor-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.1);
        }
        
        .asesor-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .asesor-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: bold;
        }
        
        .asesor-info h4 {
            margin: 0;
            color: #1f2937;
            font-size: 1.1rem;
        }
        
        .asesor-details {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .asesor-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-danger {
            background: #dc2626;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .back-link {
            margin-bottom: 20px;
        }
        
        .back-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php 
    require_once 'shared_navbar.php';
    echo getNavbar('Administración', $_SESSION['user_role'] ?? '');
    ?>
    
    <div class="gestion-container">
        <div class="back-link">
            <a href="index.php?action=asignar_personal">
                ← Volver a Asignación de Personal
            </a>
        </div>
        
        <!-- Header del Coordinador -->
        <div class="coordinador-header">
            <div class="coordinador-avatar">
                <?php echo strtoupper(substr($coordinador['nombre_completo'], 0, 1)); ?>
            </div>
            <h1 class="coordinador-name"><?php echo htmlspecialchars($coordinador['nombre_completo'] ?? ''); ?></h1>
            <div class="coordinador-role">Coordinador del Sistema</div>
            <div class="coordinador-status">
                <?php echo htmlspecialchars($coordinador['estado'] ?? ''); ?>
            </div>
        </div>
        
        <!-- Estadísticas del Coordinador -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $metricas['total_asesores_asignados']; ?></div>
                <div class="stat-label">Total de Asesores Asignados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $metricas['asesores_activos']; ?></div>
                <div class="stat-label">Asesores Activos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $metricas['total_asesores_asignados'] > 0 ? round(($metricas['asesores_activos'] / $metricas['total_asesores_asignados']) * 100) : 0; ?>%</div>
                <div class="stat-label">Tasa de Asesores Activos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">👥</div>
                <div class="stat-label">Equipo de Trabajo</div>
            </div>
        </div>
        
        <!-- Lista de Asesores Asignados -->
        <div class="asesores-section">
            <h2 class="section-title">
                👥 Asesores Asignados
                <span style="font-size: 1rem; color: #6b7280; font-weight: 400;">
                    (<?php echo $metricas['total_asesores_asignados']; ?> asesores)
                </span>
            </h2>
            
            <?php if (!empty($asesoresAsignados)): ?>
                <div class="asesores-grid">
                    <?php foreach ($asesoresAsignados as $asesor): ?>
                        <div class="asesor-card">
                            <div class="asesor-header">
                                <div class="asesor-avatar">
                                    <?php echo strtoupper(substr($asesor['nombre_completo'], 0, 1)); ?>
                                </div>
                                <div class="asesor-info">
                                    <h4><?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?></h4>
                                    <div class="asesor-details">
                                        Usuario: <?php echo htmlspecialchars($asesor['usuario'] ?? ''); ?><br>
                                        Cédula: <?php echo htmlspecialchars($asesor['cedula'] ?? ''); ?><br>
                                        Estado: <span style="color: <?php echo $asesor['estado'] === 'Activo' ? '#059669' : '#dc2626'; ?>; font-weight: 600;">
                                            <?php echo htmlspecialchars($asesor['estado'] ?? ''); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="asesor-actions">
                                <a href="index.php?action=ver_gestion_asesor&id=<?php echo $asesor['id']; ?>" class="btn btn-primary">
                                    Ver Gestión
                                </a>
                                <a href="index.php?action=liberar_asesor&asesor_id=<?php echo $asesor['id']; ?>&coordinador_id=<?php echo $coordinador['id']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('¿Estás seguro de que quieres liberar este asesor del coordinador?')">
                                    Liberar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i>👥</i>
                    <h3>No hay asesores asignados</h3>
                    <p>Este coordinador no tiene asesores asignados actualmente.</p>
                    <a href="index.php?action=asignar_personal" class="btn btn-primary">
                        Asignar Asesores
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Información del Sistema -->
        <div class="asesores-section">
            <h2 class="section-title">ℹ️ Información del Sistema</h2>
            <div style="color: #6b7280; line-height: 1.6;">
                <p><strong>Coordinador ID:</strong> <?php echo htmlspecialchars($coordinador['id'] ?? ''); ?></p>
                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($coordinador['usuario'] ?? ''); ?></p>
                <p><strong>Cédula:</strong> <?php echo htmlspecialchars($coordinador['cedula'] ?? ''); ?></p>
                <p><strong>Estado:</strong> <?php echo htmlspecialchars($coordinador['estado'] ?? ''); ?></p>
                <p><strong>Fecha de Registro:</strong> <?php echo isset($coordinador['fecha_registro']) ? htmlspecialchars($coordinador['fecha_registro'] ?? '') : 'No disponible'; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
