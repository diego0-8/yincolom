<?php
// Archivo: views/coordinador_gestionar_asesores.php
// Vista para que el coordinador gestione sus asesores asignados
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
    echo getNavbar('Gestión de Asesores', $_SESSION['user_role'] ?? ''); 
    ?>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="page-description">Gestiona los asesores asignados a tu equipo</p>
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

        <div class="row">
            <!-- Asesores Asignados -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-users"></i> Asesores Asignados</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($asesoresAsignados)): ?>
                            <div class="empty-state">
                                <i class="fas fa-user-slash"></i>
                                <p>No tienes asesores asignados</p>
                            </div>
                        <?php else: ?>
                            <div class="asesores-list">
                                <?php foreach ($asesoresAsignados as $asesor): ?>
                                    <div class="asesor-item">
                                        <div class="asesor-info">
                                            <div class="asesor-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="asesor-details">
                                                <strong><?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?></strong>
                                                <small><?php echo htmlspecialchars($asesor['usuario'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                        <div class="asesor-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="liberar">
                                                <input type="hidden" name="asesor_id" value="<?php echo $asesor['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('¿Estás seguro de liberar este asesor?')">
                                                    <i class="fas fa-user-times"></i> Liberar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Asesores Disponibles -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-plus"></i> Asesores Disponibles</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($asesoresDisponibles)): ?>
                            <div class="empty-state">
                                <i class="fas fa-user-check"></i>
                                <p>No hay asesores disponibles</p>
                            </div>
                        <?php else: ?>
                            <div class="asesores-list">
                                <?php foreach ($asesoresDisponibles as $asesor): ?>
                                    <div class="asesor-item">
                                        <div class="asesor-info">
                                            <div class="asesor-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="asesor-details">
                                                <strong><?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?></strong>
                                                <small><?php echo htmlspecialchars($asesor['usuario'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                        <div class="asesor-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="asignar">
                                                <input type="hidden" name="asesor_id" value="<?php echo $asesor['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-user-plus"></i> Asignar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Información</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <strong>Total Asesores Asignados:</strong>
                                <span class="badge badge-primary"><?php echo count($asesoresAsignados); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Asesores Disponibles:</strong>
                                <span class="badge badge-success"><?php echo count($asesoresDisponibles); ?></span>
                            </div>
                        </div>
                        <div class="info-text">
                            <p><strong>Nota:</strong> Solo los asesores asignados a tu equipo pueden recibir clientes de tus cargas de Excel.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .asesores-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .asesor-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        
        .asesor-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .asesor-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        .asesor-details {
            display: flex;
            flex-direction: column;
        }
        
        .asesor-details strong {
            color: #495057;
            font-size: 14px;
        }
        
        .asesor-details small {
            color: #6c757d;
            font-size: 12px;
        }
        
        .asesor-actions {
            display: flex;
            gap: 5px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .info-text {
            padding: 15px;
            background: #e3f2fd;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }
        
        .info-text p {
            margin: 0;
            color: #1976d2;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .empty-state p {
            margin: 0;
            font-size: 14px;
        }
    </style>
</body>
</html>
