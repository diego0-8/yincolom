<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .header { margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .back-link { margin-bottom: 20px; }
        .back-link a { color: #0066cc; text-decoration: none; }
        
        /* Mensajes de éxito y error */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .assignment-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 30px; 
            margin-top: 20px; 
        }
        .assignment-section { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 8px; 
            border: 1px solid #dee2e6; 
        }
        .assignment-section h3 { 
            color: #333; 
            margin-top: 0; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #0066cc; 
        }
        .user-list { 
            max-height: 400px; 
            overflow-y: auto; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            background: white; 
        }
        .user-item { 
            padding: 12px; 
            border-bottom: 1px solid #eee; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .user-item:last-child { border-bottom: none; }
        .user-info { flex: 1; }
        .user-name { font-weight: bold; color: #333; }
        .user-details { color: #666; font-size: 0.9em; }
        .btn { 
            display: inline-block; 
            padding: 8px 16px; 
            text-decoration: none; 
            border-radius: 4px; 
            font-weight: bold; 
            border: none; 
            cursor: pointer; 
            font-size: 14px; 
        }
        .btn-primary { background-color: #0066cc; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn:hover { opacity: 0.8; }
        .assignment-form { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            margin-top: 20px; 
            border: 1px solid #dee2e6; 
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-group select { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            font-size: 14px; 
        }
        .form-group button { 
            background-color: #0066cc; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 14px; 
        }
        .form-group button:hover { background-color: #0052a3; }
        .info-box { 
            background: #e7f3ff; 
            border: 1px solid #b3d9ff; 
            color: #0066cc; 
            padding: 15px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
        }
        .empty-state { 
            text-align: center; 
            padding: 40px; 
            color: #666; 
        }
        
        /* Mejoras visuales */
        .user-item:hover {
            background-color: #f8f9fa;
        }
        
        .btn-primary:hover {
            background-color: #0052a3;
            transform: translateY(-1px);
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .assignment-form h3 {
            color: #333;
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #0066cc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-link">
            <a href="index.php?action=dashboard">← Volver al Dashboard</a>
        </div>
        
        <div class="header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
        </div>

        <!-- Mensajes de éxito y error -->
        <?php if (!empty($success)): ?>
            <?php
            $successMessage = '';
            switch ($success) {
                case 'asignacion_exitosa':
                    $successMessage = 'Asesor asignado exitosamente al coordinador.';
                    break;
                case 'liberacion_exitosa':
                    $successMessage = 'Asesor liberado exitosamente del coordinador.';
                    break;
                default:
                    $successMessage = 'Operación realizada con éxito.';
            }
            ?>
            <div class="alert alert-success">
                <strong>✅ Éxito:</strong> <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <?php
            $errorMessage = '';
            switch ($error) {
                case 'datos_incompletos':
                    $errorMessage = 'Por favor, complete todos los campos requeridos.';
                    break;
                case 'coordinador_invalido':
                    $errorMessage = 'El coordinador seleccionado no es válido.';
                    break;
                case 'asesor_invalido':
                    $errorMessage = 'El asesor seleccionado no es válido.';
                    break;
                case 'usuarios_inactivos':
                    $errorMessage = 'No se pueden asignar usuarios inactivos.';
                    break;
                case 'error_asignacion':
                    $errorMessage = 'Error al realizar la asignación. Intente nuevamente.';
                    break;
                case 'error_liberacion':
                    $errorMessage = 'Error al liberar el asesor. Intente nuevamente.';
                    break;
                case 'error_sistema':
                    $errorMessage = 'Error del sistema. Contacte al administrador.';
                    break;
                default:
                    $errorMessage = 'Ha ocurrido un error. Intente nuevamente.';
            }
            ?>
            <div class="alert alert-error">
                <strong>❌ Error:</strong> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <strong>Información:</strong> En esta sección puedes asignar asesores a coordinadores para optimizar la distribución de trabajo y responsabilidades en el sistema.
        </div>

        <div class="assignment-grid">
            <!-- Sección de Coordinadores -->
            <div class="assignment-section">
                <h3>👥 Coordinadores Disponibles</h3>
                <?php if (!empty($coordinadores)): ?>
                    <div class="user-list">
                        <?php foreach ($coordinadores as $coordinador): ?>
                            <div class="user-item">
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($coordinador['nombre_completo'] ?? ''); ?></div>
                                    <div class="user-details">
                                        Usuario: <?php echo htmlspecialchars($coordinador['usuario'] ?? ''); ?> | 
                                        Cédula: <?php echo htmlspecialchars($coordinador['cedula'] ?? ''); ?> |
                                        Estado: <span style="color: <?php echo $coordinador['estado'] === 'Activo' ? '#28a745' : '#dc3545'; ?>; font-weight: 600;">
                                            <?php echo htmlspecialchars($coordinador['estado'] ?? ''); ?>
                                        </span>
                                    </div>
                                </div>
                                <a href="index.php?action=ver_gestion_coordinador&id=<?php echo $coordinador['id']; ?>" class="btn btn-primary">Ver Gestión</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        No hay coordinadores disponibles en el sistema.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sección de Asesores -->
            <div class="assignment-section">
                <h3>👤 Asesores Disponibles para Asignación</h3>
                <p style="color: #6b7280; margin-bottom: 15px; font-size: 0.9rem;">
                    <strong>Nota:</strong> Solo se muestran los asesores que NO están asignados a ningún coordinador.
                </p>
                <?php if (!empty($asesores)): ?>
                    <div class="user-list">
                        <?php foreach ($asesores as $asesor): ?>
                            <div class="user-item">
                                <div class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?></div>
                                    <div class="user-details">
                                        Usuario: <?php echo htmlspecialchars($asesor['usuario'] ?? ''); ?> | 
                                        Cédula: <?php echo htmlspecialchars($asesor['cedula'] ?? ''); ?> |
                                        Estado: <span style="color: <?php echo $asesor['estado'] === 'Activo' ? '#28a745' : '#dc3545'; ?>; font-weight: 600;">
                                            <?php echo htmlspecialchars($asesor['estado'] ?? ''); ?>
                                        </span>
                                    </div>
                                </div>
                                <a href="index.php?action=ver_gestion_asesor&id=<?php echo $asesor['id']; ?>" class="btn btn-primary">Ver Gestión</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div style="text-align: center; padding: 40px; color: #6b7280; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px;">
                            <div style="font-size: 3rem; margin-bottom: 20px;">✅</div>
                            <h4 style="color: #059669; margin-bottom: 15px;">Todos los asesores están asignados</h4>
                            <p style="margin-bottom: 20px; font-size: 1.1rem;">
                                <strong>No hay asesores disponibles para asignar en este momento.</strong>
                            </p>
                            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #d1fae5;">
                                <p style="margin: 0; color: #065f46;">
                                    <strong>Para asignar un nuevo asesor:</strong><br>
                                    1. Libera un asesor existente de su coordinador actual<br>
                                    2. O crea un nuevo asesor en el sistema<br>
                                    3. El asesor aparecerá automáticamente aquí
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección de Asesores Asignados -->
        <div class="assignment-section" style="margin-top: 30px;">
            <h3>🔗 Asesores Actualmente Asignados</h3>
            <p style="color: #6b7280; margin-bottom: 15px; font-size: 0.9rem;">
                <strong>Nota:</strong> Estos asesores ya están asignados a coordinadores y no aparecen en la lista de disponibles.
            </p>
            
            <?php if (!empty($asesoresAsignados)): ?>
                <div class="user-list">
                    <?php foreach ($asesoresAsignados as $asesor): ?>
                        <div class="user-item" style="background: #f0f9ff; border: 1px solid #bae6fd;">
                            <div class="user-info">
                                <div class="user-name"><?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?></div>
                                <div class="user-details">
                                    Usuario: <?php echo htmlspecialchars($asesor['usuario'] ?? ''); ?> | 
                                    <strong>Asignado a:</strong> <?php echo htmlspecialchars($asesor['coordinador_nombre'] ?? ''); ?> |
                                    Estado: <span style="color: #28a745; font-weight: 600;">Asignado</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <a href="index.php?action=ver_gestion_asesor&id=<?php echo $asesor['id']; ?>" class="btn btn-primary">Ver Gestión</a>
                                <a href="index.php?action=liberar_asesor&asesor_id=<?php echo $asesor['id']; ?>&coordinador_id=<?php echo $asesor['coordinador_id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('¿Estás seguro de que quieres liberar este asesor del coordinador?')">
                                    🔓 Liberar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div style="text-align: center; padding: 20px; color: #6b7280; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
                        No hay asesores asignados actualmente.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Formulario de Asignación -->
        <div class="assignment-form" style="margin-top: 30px;">
            <h3>🔗 Asignar Asesor a Coordinador</h3>
            <p style="color: #6b7280; margin-bottom: 20px; font-size: 0.9rem;">
                <strong>Instrucciones:</strong> Selecciona un coordinador y un asesor disponible para crear la asignación.
            </p>
            
            <?php if (!empty($asesores) && !empty(array_filter($coordinadores, function($c) { return $c['estado'] === 'Activo'; }))): ?>
                <form method="POST" action="index.php?action=asignar_asesor">
                    <div class="form-group">
                        <label for="coordinador_id">Seleccionar Coordinador:</label>
                        <select id="coordinador_id" name="coordinador_id" required>
                            <option value="">Selecciona un coordinador</option>
                            <?php foreach ($coordinadores as $coordinador): ?>
                                <?php if ($coordinador['estado'] === 'Activo'): ?>
                                    <option value="<?php echo $coordinador['id']; ?>">
                                        <?php echo htmlspecialchars($coordinador['nombre_completo'] ?? ''); ?> 
                                        (<?php echo htmlspecialchars($coordinador['usuario'] ?? ''); ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="asesor_id">Seleccionar Asesor:</label>
                        <select id="asesor_id" name="asesor_id" required>
                            <option value="">Selecciona un asesor disponible</option>
                            <?php foreach ($asesores as $asesor): ?>
                                <?php if ($asesor['estado'] === 'Activo'): ?>
                                    <option value="<?php echo $asesor['id']; ?>">
                                        <?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?> 
                                        (<?php echo htmlspecialchars($asesor['usuario'] ?? ''); ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success" style="background: #059669; border-color: #059669;">
                            🔗 Asignar Asesor
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-info" style="text-align: center; padding: 20px; color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0; border-radius: 8px;">
                    <strong>⚠️ No se puede realizar asignaciones en este momento</strong><br>
                    <?php if (empty($asesores)): ?>
                        No hay asesores disponibles para asignar.
                    <?php endif; ?>
                    <?php if (empty(array_filter($coordinadores, function($c) { return $c['estado'] === 'Activo'; }))): ?>
                        No hay coordinadores activos disponibles.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Información adicional -->
        <div class="info-box" style="margin-top: 20px;">
            <strong>Nota:</strong> La asignación de asesores a coordinadores permite una mejor organización del trabajo y supervisión de las actividades de ventas. Los coordinadores podrán ver y gestionar los clientes asignados a sus asesores.
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="info-box" style="margin-top: 20px; background: #f0f9ff; border-color: #7dd3fc;">
            <strong>📊 Estadísticas Rápidas:</strong><br>
            • Coordinadores activos: <strong><?php echo count(array_filter($coordinadores, function($c) { return $c['estado'] === 'Activo'; })); ?></strong><br>
            • Asesores activos: <strong><?php echo count(array_filter($asesores, function($a) { return $a['estado'] === 'Activo'; })); ?></strong><br>
            • Total de usuarios: <strong><?php echo count($coordinadores) + count($asesores); ?></strong>
        </div>
        
        <!-- Botón para forzar recarga -->
        <div style="text-align: center; margin-top: 20px;">
            <button onclick="forzarRecarga()" class="btn btn-primary" style="background: #0066cc;">
                🔄 Actualizar Datos
            </button>
        </div>
    </div>
    
    <script>
        // Función para forzar recarga de la página
        function forzarRecarga() {
            // Limpiar cache del navegador para esta página
            if (window.caches) {
                caches.keys().then(function(names) {
                    for (let name of names) {
                        caches.delete(name);
                    }
                });
            }
            
            // Recargar la página con timestamp para evitar cache
            window.location.href = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 't=' + Date.now();
        }
        
        // Verificar si hay mensajes de éxito o error para mostrar
        <?php if (!empty($success) || !empty($error)): ?>
        // Si hay mensajes, hacer scroll hacia arriba para mostrarlos
        window.scrollTo(0, 0);
        <?php endif; ?>
        
        // Prevenir cache del navegador
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html> 