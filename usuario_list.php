<?php
// Archivo: views/usuario_list.php
// Vista para listar usuarios del sistema.
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
    echo getNavbar('Gestión', $_SESSION['user_role'] ?? ''); 
    ?>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="page-description">Gestiona todos los usuarios del sistema</p>
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

        <div class="card">
            <div class="card-header">
                <div class="header-content">
                    <h2>Lista de Usuarios</h2>
                    <a href="index.php?action=crear_usuario" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($usuarios)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No hay usuarios registrados</h3>
                        <p>Comienza creando el primer usuario del sistema</p>
                        <a href="index.php?action=crear_usuario" class="btn btn-primary">
                            Crear Usuario
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Cédula</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>ID</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-secondary">#<?php echo htmlspecialchars($usuario['id'] ?? ''); ?></span>
                                        </td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="user-details">
                                                    <strong><?php echo htmlspecialchars($usuario['nombre_completo'] ?? ''); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($usuario['cedula'] ?? ''); ?></td>
                                        <td>
                                            <code class="username"><?php echo htmlspecialchars($usuario['usuario'] ?? ''); ?></code>
                                        </td>
                                        <td>
                                            <?php
                                            $rolClass = '';
                                            $rolText = '';
                                            switch ($usuario['rol']) {
                                                case 'administrador':
                                                    $rolClass = 'badge-admin';
                                                    $rolText = 'Administrador';
                                                    break;
                                                case 'coordinador':
                                                    $rolClass = 'badge-coordinator';
                                                    $rolText = 'Coordinador';
                                                    break;
                                                case 'asesor':
                                                    $rolClass = 'badge-advisor';
                                                    $rolText = 'Asesor';
                                                    break;
                                                default:
                                                    $rolClass = 'badge-secondary';
                                                    $rolText = ucfirst($usuario['rol']);
                                            }
                                            ?>
                                            <span class="badge <?php echo $rolClass; ?>"><?php echo $rolText; ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $estadoClass = $usuario['estado'] === 'Activo' ? 'badge-success' : 'badge-danger';
                                            ?>
                                            <span class="badge <?php echo $estadoClass; ?>">
                                                <?php echo htmlspecialchars($usuario['estado'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                ID: <?php echo htmlspecialchars($usuario['id'] ?? ''); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="index.php?action=editar_usuario&id=<?php echo $usuario['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Editar Usuario">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <?php if ($usuario['estado'] === 'Activo'): ?>
                                                    <a href="index.php?action=toggle_estado&id=<?php echo $usuario['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" title="Desactivar Usuario"
                                                       onclick="return confirm('¿Estás seguro de que quieres desactivar este usuario?')">
                                                        <i class="fas fa-user-times"></i> Desactivar
                                                    </a>
                                                <?php else: ?>
                                                    <a href="index.php?action=toggle_estado&id=<?php echo $usuario['id']; ?>" 
                                                       class="btn btn-sm btn-outline-success" title="Activar Usuario"
                                                       onclick="return confirm('¿Estás seguro de que quieres activar este usuario?')">
                                                        <i class="fas fa-user-check"></i> Activar
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <div class="table-info">
                            <span class="text-muted">
                                Total: <strong><?php echo count($usuarios); ?></strong> usuarios
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }
        
        .username {
            background-color: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        
        .badge-admin {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
        }
        
        .badge-coordinator {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
        }
        
        .badge-advisor {
            background: linear-gradient(135deg, #45b7d1, #96c93d);
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
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
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #495057;
        }
        
        .empty-state p {
            margin-bottom: 20px;
        }
    </style>
</body>
</html>