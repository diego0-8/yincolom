<?php
$page_title = "Actividades del Sistema";
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
                <h2 class="mb-4">📊 Actividades del Sistema</h2>
                
                <!-- Estadísticas generales -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Usuarios</h5>
                                <h3><?php echo $stats['total_usuarios']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Coordinadores</h5>
                                <h3><?php echo $stats['coordinadores']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Asesores</h5>
                                <h3><?php echo $stats['asesores']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Usuarios Activos</h5>
                                <h3><?php echo $stats['usuarios_activos']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información del sistema -->
                <div class="card">
                    <div class="card-header">
                        <h5>Información del Sistema</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Versión:</strong> 2.2</p>
                        <p><strong>Última actualización:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                        <p><strong>Estado:</strong> <span class="badge bg-success">Activo</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'views/shared_footer.php'; ?>
</body>
</html>

