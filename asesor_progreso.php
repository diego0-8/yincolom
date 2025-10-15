<?php
$page_title = "Progreso del Asesor";
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
    <?php include 'views/shared_navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">📈 Progreso del Asesor</h2>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Información del Asesor</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($asesor['nombre_completo'] ?? 'N/A'); ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-<?php echo ($asesor['estado'] ?? '') === 'Activo' ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars($asesor['estado'] ?? 'N/A'); ?>
                            </span>
                        </p>
                        <p><strong>Fecha de registro:</strong> <?php echo htmlspecialchars($asesor['fecha_registro'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Métricas de Progreso</h5>
                    </div>
                    <div class="card-body">
                        <p>Las métricas de progreso se mostrarán aquí.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'views/shared_footer.php'; ?>
</body>
</html>

