<?php
// Archivo: views/admin_dashboard.php
// Vista del dashboard principal del administrador con diseño moderno
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
    echo getNavbar('Inicio', $_SESSION['user_role'] ?? '');
    ?>
    
    <div class="main-container">
        <!-- Tarjeta de bienvenida -->
        <div class="card">
            <div class="card-header">
                🏠 Panel de Control del Administrador
            </div>
            <div class="card-body">
                <h2>Bienvenido al Sistema de Gestión de Ventas</h2>
                <p>Desde aquí puedes gestionar usuarios, coordinar actividades y supervisar el rendimiento del equipo.</p>
            </div>
        </div>
        
        <!-- Grid de estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">👥</div>
                <div class="stat-label">Gestión de Usuarios</div>
                <p class="mt-20">Crear, editar y gestionar usuarios del sistema</p>
                <a href="index.php?action=list_usuarios" class="btn btn-primary mt-20">Gestionar Usuarios</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">📊</div>
                <div class="stat-label">Reportes y Estadísticas</div>
                <p class="mt-20">Ver métricas y reportes del equipo</p>
                <a href="index.php?action=ver_actividades" class="btn btn-primary mt-20">Ver Reportes</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">⚙️</div>
                <div class="stat-label">Configuración del Sistema</div>
                <p class="mt-20">Asignar personal y configurar roles</p>
                <a href="index.php?action=asignar_personal" class="btn btn-primary mt-20">Configurar</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">➕</div>
                <div class="stat-label">Nuevo Usuario</div>
                <p class="mt-20">Crear un nuevo usuario en el sistema</p>
                <a href="index.php?action=crear_usuario" class="btn btn-success mt-20">Crear Usuario</a>
            </div>
        </div>
        
        <!-- Tarjeta de acciones rápidas -->
        <div class="card">
            <div class="card-header">
                🚀 Acciones Rápidas
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <a href="index.php?action=list_usuarios" class="btn btn-primary" style="width: 100%; padding: 15px;">
                            👥 Ver Todos los Usuarios
                        </a>
                    </div>
                    <div class="form-group">
                        <a href="index.php?action=crear_usuario" class="btn btn-success" style="width: 100%; padding: 15px;">
                            ➕ Crear Nuevo Usuario
                        </a>
                    </div>
                    <div class="form-group">
                        <a href="index.php?action=ver_actividades" class="btn btn-secondary" style="width: 100%; padding: 15px;">
                            📊 Ver Actividades
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Nueva tarjeta de gestión de personal -->
        <div class="card">
            <div class="card-header">
                👥 Gestión de Personal
            </div>
            <div class="card-body">
                <p>Gestiona la asignación de asesores a coordinadores y supervisa el rendimiento del equipo.</p>
                <div class="form-row">
                    <div class="form-group">
                        <a href="index.php?action=asignar_personal" class="btn btn-primary" style="width: 100%; padding: 15px;">
                            🔗 Asignar Personal
                        </a>
                    </div>
                    <div class="form-group">
                        <a href="index.php?action=ver_actividades" class="btn btn-info" style="width: 100%; padding: 15px;">
                            📈 Ver Métricas del Equipo
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información del sistema -->
        <div class="card">
            <div class="card-header">
                ℹ️ Información del Sistema
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Estado:</strong> Sistema funcionando correctamente
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <strong>Usuario actual:</strong> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'No identificado'); ?>
                    </div>
                    <div class="form-group">
                        <strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['user_role'] ?? 'No definido'); ?>
                    </div>
                    <div class="form-group">
                        <strong>ID de sesión:</strong> <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'No disponible'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
