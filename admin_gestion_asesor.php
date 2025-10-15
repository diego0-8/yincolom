<?php
// Archivo: views/admin_gestion_asesor.php
// Vista para que el administrador vea la gestión de un asesor específico
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
        
        .asesor-header {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .asesor-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        
        .asesor-name {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .asesor-role {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .asesor-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            background: <?php echo $metricas['asesor_estado'] === 'Activo' ? '#dcfce7' : '#fef2f2'; ?>;
            color: <?php echo $metricas['asesor_estado'] === 'Activo' ? '#166534' : '#dc2626'; ?>;
            border: 1px solid <?php echo $metricas['asesor_estado'] === 'Activo' ? '#bbf7d0' : '#fecaca'; ?>;
        }
        
        .coordinador-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .coordinador-info h3 {
            color: #0369a1;
            margin: 0 0 10px 0;
            font-size: 1.2rem;
        }
        
        .coordinador-info p {
            color: #0c4a6e;
            margin: 0;
            font-weight: 500;
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
        
        .info-section {
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
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .info-item h4 {
            margin: 0 0 10px 0;
            color: #1f2937;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-item p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
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
            display: inline-block;
            margin: 5px;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
        
        <!-- Header del Asesor -->
        <div class="asesor-header">
            <div class="asesor-avatar">
                <?php echo strtoupper(substr($asesor['nombre_completo'], 0, 1)); ?>
            </div>
            <h1 class="asesor-name"><?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?></h1>
            <div class="asesor-role">Asesor del Sistema</div>
            <div class="asesor-status">
                <?php echo htmlspecialchars($metricas['asesor_estado'] ?? ''); ?>
            </div>
        </div>
        
        <!-- Información del Coordinador Asignado -->
        <?php if ($metricas['coordinador_asignado'] !== 'Sin asignar'): ?>
            <div class="coordinador-info">
                <h3>👥 Coordinador Asignado</h3>
                <p><strong><?php echo htmlspecialchars($metricas['coordinador_asignado'] ?? ''); ?></strong></p>
                <p>Este asesor está bajo la supervisión del coordinador asignado</p>
            </div>
        <?php else: ?>
            <div class="coordinador-info" style="background: #fef3c7; border-color: #fbbf24;">
                <h3>⚠️ Sin Coordinador Asignado</h3>
                <p>Este asesor no tiene coordinador asignado actualmente</p>
                <a href="index.php?action=asignar_personal" class="btn btn-primary">
                    Asignar Coordinador
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Estadísticas del Asesor -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">👤</div>
                <div class="stat-label">Estado del Asesor</div>
                <div style="margin-top: 10px; font-size: 1.2rem; font-weight: 600; color: <?php echo $metricas['asesor_estado'] === 'Activo' ? '#059669' : '#dc2626'; ?>;">
                    <?php echo htmlspecialchars($metricas['asesor_estado'] ?? ''); ?>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">📅</div>
                <div class="stat-label">Fecha de Registro</div>
                <div style="margin-top: 10px; font-size: 1.1rem; color: #6b7280;">
                    <?php echo htmlspecialchars($metricas['fecha_registro'] ?? ''); ?>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">🎯</div>
                <div class="stat-label">Rol del Sistema</div>
                <div style="margin-top: 10px; font-size: 1.1rem; color: #3b82f6; font-weight: 600;">
                    Asesor
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">📊</div>
                <div class="stat-label">Gestión de Clientes</div>
                <div style="margin-top: 10px; font-size: 1.1rem; color: #6b7280;">
                    Activo en el sistema
                </div>
            </div>
        </div>
        
        <!-- Información Detallada del Asesor -->
        <div class="info-section">
            <h2 class="section-title">📋 Información Detallada</h2>
            <div class="info-grid">
                <div class="info-item">
                    <h4>👤 Datos Personales</h4>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?></p>
                    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($asesor['usuario'] ?? ''); ?></p>
                    <p><strong>Cédula:</strong> <?php echo htmlspecialchars($asesor['cedula'] ?? ''); ?></p>
                </div>
                
                <div class="info-item">
                    <h4>🔐 Acceso al Sistema</h4>
                    <p><strong>Rol:</strong> Asesor</p>
                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($asesor['estado'] ?? ''); ?></p>
                    <p><strong>ID del Sistema:</strong> <?php echo htmlspecialchars($asesor['id'] ?? ''); ?></p>
                </div>
                
                <div class="info-item">
                    <h4>👥 Asignación</h4>
                    <p><strong>Coordinador:</strong> <?php echo htmlspecialchars($metricas['coordinador_asignado'] ?? ''); ?></p>
                    <p><strong>Estado Asignación:</strong> 
                        <span style="color: <?php echo $metricas['coordinador_asignado'] !== 'Sin asignar' ? '#059669' : '#dc2626'; ?>; font-weight: 600;">
                            <?php echo $metricas['coordinador_asignado'] !== 'Sin asignar' ? 'Asignado' : 'Sin asignar'; ?>
                        </span>
                    </p>
                </div>
                
                <div class="info-item">
                    <h4>📅 Información del Sistema</h4>
                    <p><strong>Fecha de Registro:</strong> <?php echo htmlspecialchars($metricas['fecha_registro'] ?? ''); ?></p>
                    <p><strong>Última Sesión:</strong> No disponible</p>
                    <p><strong>Estado de Cuenta:</strong> <?php echo htmlspecialchars($asesor['estado'] ?? ''); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Acciones Disponibles -->
        <div class="info-section">
            <h2 class="section-title">⚙️ Acciones Disponibles</h2>
            <div style="text-align: center;">
                <a href="index.php?action=editar_usuario&id=<?php echo $asesor['id']; ?>" class="btn btn-primary">
                    ✏️ Editar Usuario
                </a>
                
                <a href="index.php?action=toggle_estado&id=<?php echo $asesor['id']; ?>" 
                   class="btn btn-secondary"
                   onclick="return confirm('¿Estás seguro de que quieres cambiar el estado de este usuario?')">
                    <?php echo $asesor['estado'] === 'Activo' ? '🚫 Desactivar' : '✅ Activar'; ?>
                </a>
                
                <?php if ($metricas['coordinador_asignado'] !== 'Sin asignar'): ?>
                    <a href="index.php?action=liberar_asesor&asesor_id=<?php echo $asesor['id']; ?>&coordinador_id=<?php 
                        // Obtener el ID del coordinador asignado
                        $coordinadores = $this->model->getUsuariosByRol('coordinador');
                        $coordinadorId = null;
                        foreach ($coordinadores as $coordinador) {
                            if ($this->model->isAsesorAsignadoACoordinador($asesor['id'], $coordinador['id'])) {
                                $coordinadorId = $coordinador['id'];
                                break;
                            }
                        }
                        echo $coordinadorId;
                    ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('¿Estás seguro de que quieres liberar este asesor del coordinador?')">
                        🚫 Liberar del Coordinador
                    </a>
                <?php endif; ?>
                
                <a href="index.php?action=asignar_personal" class="btn btn-secondary">
                    🔄 Volver a Asignación
                </a>
            </div>
        </div>
        
        <!-- Información del Sistema -->
        <div class="info-section">
            <h2 class="section-title">ℹ️ Información del Sistema</h2>
            <div style="color: #6b7280; line-height: 1.6;">
                <p><strong>Asesor ID:</strong> <?php echo htmlspecialchars($asesor['id'] ?? ''); ?></p>
                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($asesor['usuario'] ?? ''); ?></p>
                <p><strong>Cédula:</strong> <?php echo htmlspecialchars($asesor['cedula'] ?? ''); ?></p>
                <p><strong>Estado:</strong> <?php echo htmlspecialchars($asesor['estado'] ?? ''); ?></p>
                <p><strong>Fecha de Registro:</strong> <?php echo htmlspecialchars($metricas['fecha_registro'] ?? ''); ?></p>
                <p><strong>Coordinador Asignado:</strong> <?php echo htmlspecialchars($metricas['coordinador_asignado'] ?? ''); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
