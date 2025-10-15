<?php
// Vista para gestionar el estado de las bases de datos
include 'shared_styles.php';
include 'shared_navbar.php';

// Conexión a la base de datos
require_once 'config.php';
$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
?>

<style>
/* Estilos específicos para los botones de acción */
.btn-habilitar {
    background-color: #28a745 !important;
    color: white !important;
    font-weight: bold !important;
    padding: 8px 12px !important;
    border: 2px solid #28a745 !important;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3) !important;
}

.btn-habilitar:hover {
    background-color: #218838 !important;
    border-color: #1e7e34 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4) !important;
}

.btn-deshabilitar {
    background-color: #dc3545 !important;
    color: white !important;
    font-weight: bold !important;
    padding: 8px 12px !important;
    border: 2px solid #dc3545 !important;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3) !important;
}

.btn-deshabilitar:hover {
    background-color: #c82333 !important;
    border-color: #bd2130 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.4) !important;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-database text-primary"></i>
                        Gestionar Estado de Bases de Datos
                    </h4>
                    <a href="index.php?action=dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <!-- Barra de búsqueda -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="buscarBases" class="form-control" placeholder="Buscar base de datos por nombre...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="button" id="btnBuscar">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="soloHabilitadas" checked>
                                <label class="form-check-label" for="soloHabilitadas">
                                    Solo mostrar bases habilitadas
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de bases de datos -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre de la Base</th>
                                    <th>Tipo</th>
                                    <th>Fecha de Carga</th>
                                    <th>Total Clientes</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaBases">
                                <?php foreach ($bases_datos as $base): ?>
                                <tr data-base-id="<?= $base['id'] ?>">
                                    <td><?= $base['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($base['nombre_cargue']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= htmlspecialchars($base['tipo_base_datos']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($base['fecha_cargue'])) ?></td>
                                    <td>
                                        <?php
                                        // Contar clientes directamente desde la base de datos
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM clientes WHERE carga_excel_id = ?");
                                        $stmt->execute([$base['id']]);
                                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo $result['total'];
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($base['estado_habilitado'] === 'habilitado'): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i> Habilitado
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times-circle"></i> Deshabilitado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($base['estado_habilitado'] === 'habilitado'): ?>
                                                <button class="btn btn-sm btn-deshabilitar" onclick="cambiarEstado(<?= $base['id'] ?>, 'deshabilitado')" 
                                                        title="Deshabilitar Base de Datos">
                                                    <i class="fas fa-ban"></i> Deshabilitar
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-habilitar" onclick="cambiarEstado(<?= $base['id'] ?>, 'habilitado')" 
                                                        title="Habilitar Base de Datos">
                                                    <i class="fas fa-check"></i> Habilitar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mensaje cuando no hay resultados -->
                    <div id="sinResultados" class="text-center py-4" style="display: none;">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron bases de datos</h5>
                        <p class="text-muted">Intenta con otros términos de búsqueda</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Cambio de Estado</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="mensajeConfirmacion"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarCambio">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
let baseIdActual = null;
let nuevoEstadoActual = null;

// Función para buscar bases de datos
function buscarBases() {
    const termino = document.getElementById('buscarBases').value;
    const soloHabilitadas = document.getElementById('soloHabilitadas').checked;
    
    fetch('index.php?action=buscar_bases_datos', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `termino_busqueda=${encodeURIComponent(termino)}&solo_habilitadas=${soloHabilitadas}`
    })
    .then(response => response.json())
    .then(data => {
        mostrarResultados(data);
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error al buscar bases de datos', 'error');
    });
}

// Función para mostrar resultados de búsqueda
function mostrarResultados(bases) {
    const tabla = document.getElementById('tablaBases');
    const sinResultados = document.getElementById('sinResultados');
    
    if (bases.length === 0) {
        tabla.innerHTML = '';
        sinResultados.style.display = 'block';
        return;
    }
    
    sinResultados.style.display = 'none';
    
    let html = '';
    bases.forEach(base => {
        const estadoBadge = base.estado_habilitado === 'habilitado' 
            ? '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Habilitado</span>'
            : '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Deshabilitado</span>';
        
        const botonAccion = base.estado_habilitado === 'habilitado'
            ? `<button class="btn btn-sm btn-deshabilitar" onclick="cambiarEstado(${base.id}, 'deshabilitado')" title="Deshabilitar Base de Datos"><i class="fas fa-ban"></i> Deshabilitar</button>`
            : `<button class="btn btn-sm btn-habilitar" onclick="cambiarEstado(${base.id}, 'habilitado')" title="Habilitar Base de Datos"><i class="fas fa-check"></i> Habilitar</button>`;
        
        html += `
            <tr data-base-id="${base.id}">
                <td>${base.id}</td>
                <td><strong>${base.nombre_cargue}</strong></td>
                <td><span class="badge badge-info">${base.tipo_base_datos}</span></td>
                <td>${new Date(base.fecha_carga).toLocaleDateString('es-ES')} ${new Date(base.fecha_carga).toLocaleTimeString('es-ES')}</td>
                <td>${base.total_clientes || 0}</td>
                <td>${estadoBadge}</td>
                <td>
                    <div class="btn-group" role="group">
                        ${botonAccion}
                    </div>
                </td>
            </tr>
        `;
    });
    
    tabla.innerHTML = html;
}

// Función para cambiar estado de una base
function cambiarEstado(baseId, nuevoEstado) {
    baseIdActual = baseId;
    nuevoEstadoActual = nuevoEstado;
    
    const accion = nuevoEstado === 'habilitado' ? 'habilitar' : 'deshabilitar';
    const mensaje = `¿Estás seguro de que quieres ${accion} esta base de datos?`;
    
    document.getElementById('mensajeConfirmacion').textContent = mensaje;
    $('#modalConfirmacion').modal('show');
}

// Función para confirmar el cambio
function confirmarCambio() {
    if (!baseIdActual || !nuevoEstadoActual) return;
    
    const formData = new FormData();
    formData.append('carga_id', baseIdActual);
    formData.append('nuevo_estado', nuevoEstadoActual);
    
    fetch('index.php?action=cambiar_estado_base', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        $('#modalConfirmacion').modal('hide');
        mostrarMensaje('Estado actualizado correctamente', 'success');
        buscarBases(); // Refrescar la búsqueda
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error al actualizar el estado', 'error');
    });
}

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo) {
    const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${mensaje}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    // Insertar al inicio del card-body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertAdjacentHTML('afterbegin', alert);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        const alertElement = cardBody.querySelector('.alert');
        if (alertElement) {
            alertElement.remove();
        }
    }, 5000);
}

// Event listeners
document.getElementById('btnBuscar').addEventListener('click', buscarBases);
document.getElementById('buscarBases').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        buscarBases();
    }
});
document.getElementById('soloHabilitadas').addEventListener('change', buscarBases);
document.getElementById('confirmarCambio').addEventListener('click', confirmarCambio);

// Búsqueda inicial
document.addEventListener('DOMContentLoaded', function() {
    buscarBases();
});
</script>

<?php include 'shared_footer.php'; ?>

