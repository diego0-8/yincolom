<?php
// Archivo: views/usuario_form.php
// Vista para crear o editar un usuario.
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
    echo getNavbar('Registrar usuario', $_SESSION['user_role'] ?? ''); 
    ?>
    
    <div class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="page-description">Complete el formulario para crear o editar un usuario del sistema</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <strong>Éxito:</strong> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Información del Usuario</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo <span class="required">*</span></label>
                            <input type="text" id="nombre" name="nombre" 
                                   value="<?php echo htmlspecialchars($usuario['nombre_completo'] ?? ''); ?>" 
                                   required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="cedula">Cédula <span class="required">*</span></label>
                            <input type="text" id="cedula" name="cedula" 
                                   value="<?php echo htmlspecialchars($usuario['cedula'] ?? ''); ?>" 
                                   required class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario">Usuario <span class="required">*</span></label>
                            <input type="text" id="usuario" name="usuario" 
                                   value="<?php echo htmlspecialchars($usuario['usuario'] ?? ''); ?>" 
                                   required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="contrasena">
                                <?php echo isset($usuario['id']) ? 'Nueva Contraseña' : 'Contraseña'; ?> 
                                <?php echo isset($usuario['id']) ? '' : '<span class="required">*</span>'; ?>
                            </label>
                            <input type="password" id="contrasena" name="contrasena" 
                                   <?php echo isset($usuario['id']) ? '' : 'required'; ?> 
                                   class="form-control">
                            <?php if (isset($usuario['id'])): ?>
                                <small class="form-help">Deja en blanco para mantener la contraseña actual</small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="confirmar_contrasena">
                                Confirmar <?php echo isset($usuario['id']) ? 'Nueva ' : ''; ?>Contraseña
                                <?php echo isset($usuario['id']) ? '' : '<span class="required">*</span>'; ?>
                            </label>
                            <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" 
                                   <?php echo isset($usuario['id']) ? '' : 'required'; ?> 
                                   class="form-control">
                            <?php if (isset($usuario['id'])): ?>
                                <small class="form-help">Confirma la nueva contraseña</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="rol">Rol <span class="required">*</span></label>
                            <select id="rol" name="rol" required class="form-control">
                                <option value="">Selecciona un rol</option>
                                <option value="administrador" <?php echo ($usuario['rol'] ?? '') === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                <option value="coordinador" <?php echo ($usuario['rol'] ?? '') === 'coordinador' ? 'selected' : ''; ?>>Coordinador</option>
                                <option value="asesor" <?php echo ($usuario['rol'] ?? '') === 'asesor' ? 'selected' : ''; ?>>Asesor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado <span class="required">*</span></label>
                            <select id="estado" name="estado" required class="form-control">
                                <option value="">Selecciona un estado</option>
                                <option value="Activo" <?php echo ($usuario['estado'] ?? '') === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                                <option value="Inactivo" <?php echo ($usuario['estado'] ?? '') === 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campos de Teléfono -->
                    <div class="form-section">
                        <h3>📞 Configuración de Teléfono (Opcional)</h3>
                        <p class="form-help">Configure estos campos para habilitar la funcionalidad Click to Call</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="extension_telefono">Extensión Telefónica</label>
                            <input type="text" id="extension_telefono" name="extension_telefono" 
                                   value="<?php echo htmlspecialchars($usuario['extension_telefono'] ?? ''); ?>" 
                                   placeholder="Ej: 1001" class="form-control">
                            <small class="form-help">Número de extensión del sistema telefónico</small>
                        </div>
                        <div class="form-group">
                            <label for="clave_webrtc">Clave WebRTC</label>
                            <input type="password" id="clave_webrtc" name="clave_webrtc" 
                                   value="<?php echo htmlspecialchars($usuario['clave_webrtc'] ?? ''); ?>" 
                                   placeholder="Contraseña para llamadas" class="form-control">
                            <small class="form-help">Contraseña para autenticación en el sistema telefónico</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefono_activo">Activar Teléfono</label>
                            <select id="telefono_activo" name="telefono_activo" class="form-control">
                                <option value="No" <?php echo ($usuario['telefono_activo'] ?? 'No') === 'No' ? 'selected' : ''; ?>>No</option>
                                <option value="Si" <?php echo ($usuario['telefono_activo'] ?? 'No') === 'Si' ? 'selected' : ''; ?>>Sí</option>
                            </select>
                            <small class="form-help">Habilita la funcionalidad Click to Call para este usuario</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="index.php?action=list_usuarios" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?php echo isset($usuario['id']) ? 'Actualizar Usuario' : 'Crear Usuario'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const cedula = document.getElementById('cedula').value.trim();
            const usuario = document.getElementById('usuario').value.trim();
            const rol = document.getElementById('rol').value;
            const estado = document.getElementById('estado').value;
            
            if (!nombre || !cedula || !usuario || !rol || !estado) {
                e.preventDefault();
                alert('Por favor, complete todos los campos obligatorios.');
                return false;
            }
            
            // Validar cédula (solo números)
            if (!/^\d+$/.test(cedula)) {
                e.preventDefault();
                alert('La cédula debe contener solo números.');
                return false;
            }
            
            // Validar usuario (solo letras, números y guiones bajos)
            if (!/^[a-zA-Z0-9_]+$/.test(usuario)) {
                e.preventDefault();
                alert('El usuario solo puede contener letras, números y guiones bajos.');
                return false;
            }
            
            // Validar contraseñas si se proporcionan
            const contrasena = document.getElementById('contrasena').value;
            const confirmarContrasena = document.getElementById('confirmar_contrasena').value;
            
            if (contrasena || confirmarContrasena) {
                if (contrasena && contrasena.length < 6) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 6 caracteres.');
                    return false;
                }
                
                if (contrasena !== confirmarContrasena) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden.');
                    return false;
                }
            }
        });
        
        // Validación de contraseñas en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const contrasena = document.getElementById('contrasena');
            const confirmarContrasena = document.getElementById('confirmar_contrasena');
            
            function validarContrasenas() {
                if (contrasena.value && confirmarContrasena.value) {
                    if (contrasena.value !== confirmarContrasena.value) {
                        confirmarContrasena.setCustomValidity('Las contraseñas no coinciden');
                        confirmarContrasena.style.borderColor = '#ef4444';
                    } else {
                        confirmarContrasena.setCustomValidity('');
                        confirmarContrasena.style.borderColor = '#10b981';
                    }
                } else {
                    confirmarContrasena.setCustomValidity('');
                    confirmarContrasena.style.borderColor = '';
                }
            }
            
            contrasena.addEventListener('input', validarContrasenas);
            confirmarContrasena.addEventListener('input', validarContrasenas);
            
            // Validar longitud mínima de contraseña
            contrasena.addEventListener('input', function() {
                if (this.value && this.value.length < 6) {
                    this.setCustomValidity('La contraseña debe tener al menos 6 caracteres');
                    this.style.borderColor = '#ef4444';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '';
                }
            });
        });
    </script>
</body>
</html>
