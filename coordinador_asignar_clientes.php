<?php
// Archivo: views/coordinador_asignar_clientes.php
// Vista para asignar clientes a asesores
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
    echo getNavbar('Asignar Clientes', $_SESSION['user_role'] ?? '');
    ?>
    
    <div class="main-container">
        <!-- Encabezado -->
        <div class="card">
            <div class="card-header">
                📋 Asignar Clientes - <?php echo htmlspecialchars($carga['nombre_cargue'] ?? 'N/A'); ?>
            </div>
            <div class="card-body">
                <h2>Asignación de Clientes</h2>
                <p>Distribuye los clientes pendientes entre los asesores disponibles.</p>
                
                <div style="margin-bottom: 20px;">
                    <a href="index.php?action=localizacion_equipo" class="btn btn-secondary">
                        ← Volver a Localización
                    </a>
                    <a href="index.php?action=ver_clientes&carga_id=<?php echo $carga['id']; ?>" class="btn btn-primary">
                        👥 Ver Clientes
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($clientes_pendientes); ?></div>
                <div class="stat-label">Clientes Pendientes</div>
                <p class="mt-20">Por asignar</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo count($asesores); ?></div>
                <div class="stat-label">Asesores Disponibles</div>
                <p class="mt-20">Para asignar clientes</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo count($clientes_pendientes) > 0 && count($asesores) > 0 ? round(count($clientes_pendientes) / count($asesores)) : 0; ?></div>
                <div class="stat-label">Promedio por Asesor</div>
                <p class="mt-20">Distribución equitativa</p>
            </div>
        </div>
        
        <!-- Formulario de Asignación -->
        <div class="card">
            <div class="card-header">
                ⚡ Asignación de Clientes
            </div>
            <div class="card-body">
                <?php if (!empty($clientes_pendientes) && !empty($asesores)): ?>
                    <form method="POST" action="index.php?action=asignarClientes">
                        <input type="hidden" name="carga_id" value="<?php echo $carga['id']; ?>">
                        
                        <div class="form-group">
                            <label><strong>Distribución de Clientes por Asesor:</strong></label>
                            <p class="text-muted">Especifica cuántos clientes asignar a cada asesor:</p>
                        </div>
                        
                        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
                            <?php foreach ($asesores as $asesor): ?>
                                <div class="stat-card" style="text-align: center; padding: 20px;">
                                    <h4 style="margin: 0 0 15px 0; color: #1f2937;">
                                        <?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?>
                                    </h4>
                                    <p style="margin: 0 0 15px 0; color: #6b7280; font-size: 0.9rem;">
                                        Usuario: <?php echo htmlspecialchars($asesor['username'] ?? ''); ?>
                                    </p>
                                    
                                    <div style="margin-bottom: 15px;">
                                        <label for="asesor_<?php echo $asesor['id']; ?>" style="font-weight: bold; color: #374151;">
                                            Cantidad de Clientes:
                                        </label>
                                        <input type="number" 
                                               id="asesor_<?php echo $asesor['id']; ?>" 
                                               name="asignaciones[<?php echo $asesor['id']; ?>]" 
                                               value="0" 
                                               min="0" 
                                               max="<?php echo count($clientes_pendientes); ?>"
                                               class="form-control" 
                                               style="width: 100px; margin: 0 auto; text-align: center;">
                                    </div>
                                    
                                    <div style="font-size: 0.9rem; color: #6b7280;">
                                        <strong>Clientes actuales:</strong> <?php echo $asesor['total_clientes'] ?? 0; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="form-group" style="text-align: center; margin: 30px 0;">
                            <button type="submit" class="btn btn-success btn-lg">
                                🚀 Confirmar Asignación
                            </button>
                        </div>
                    </form>
                    
                    <!-- Asignación Automática -->
                    <div class="card" style="margin-top: 30px; background: #f8f9fa;">
                        <div class="card-header">
                            ⚡ Asignación Automática
                        </div>
                        <div class="card-body">
                            <p>¿Prefieres que el sistema distribuya los clientes automáticamente de manera equitativa?</p>
                            <form method="POST" action="index.php?action=asignarAutomatico" style="display: inline;">
                                <input type="hidden" name="carga_id" value="<?php echo $carga['id']; ?>">
                                <button type="submit" class="btn btn-primary">
                                    🔄 Asignación Automática
                                </button>
                            </form>
                        </div>
                    </div>
                    
                <?php elseif (empty($clientes_pendientes)): ?>
                    <div class="alert alert-success">
                        <strong>¡Excelente!</strong> Todos los clientes de esta carga ya han sido asignados.
                    </div>
                <?php elseif (empty($asesores)): ?>
                    <div class="alert alert-warning">
                        <strong>Atención:</strong> No tienes asesores asignados a tu coordinación. 
                        Contacta al administrador para que te asigne asesores.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php require_once 'shared_footer.php'; ?>
</body>
</html>

