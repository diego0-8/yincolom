<?php
// Archivo: views/coordinador_resultados_equipo.php
// Vista para mostrar resultados y estadísticas del equipo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php require_once 'shared_styles.php'; ?>
    <style>
        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 2% auto;
            padding: 0;
            border: none;
            width: 95%;
            max-width: 1200px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            background: linear-gradient(196deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .filtros-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filtro-group {
            display: flex;
            flex-direction: column;
        }

        .filtro-label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #374151;
        }

        .filtro-select {
            padding: 8px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .filtros-fechas-modal {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .filtros-fechas-grid-modal {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filtro-fecha-group-modal {
            display: flex;
            flex-direction: column;
        }

        .filtro-fecha-label-modal {
            font-weight: 600;
            margin-bottom: 5px;
            color: #1976d2;
        }

        .filtro-fecha-input-modal {
            padding: 8px 12px;
            border: 2px solid #bbdefb;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .btn-limpiar-fechas-modal {
            padding: 8px 16px;
            background: #f44336;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .acciones-masivas {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }

        .info-text {
            color: #856404;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .paginacion {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .btn-pagina {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            background: white;
            color: #495057;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-pagina:hover {
            background: #e9ecef;
        }

        .paginacion-info {
            margin-right: 20px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.05);
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }

        .badge-success {
            color: #fff;
            background-color: #28a745;
        }

        .badge-secondary {
            color: #fff;
            background-color: #6c757d;
        }

        .badge-warning {
            color: #212529;
            background-color: #ffc107;
        }

        .badge-danger {
            color: #fff;
            background-color: #dc3545;
        }

        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        /* Estilos para métricas */
        .metricas-filtro {
            background: linear-gradient(196deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 8px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .metricas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .metrica-item {
            text-align: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .metrica-numero {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #fff;
        }

        .metrica-porcentaje {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #4ade80;
        }

        .metrica-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <?php 
    require_once 'shared_navbar.php';
    echo getNavbar('Resultados', $_SESSION['user_role'] ?? '');
    ?>
    
    <div class="main-container">
        <!-- Encabezado -->
        <div class="card">
            <div class="card-header">
                📊 Resultados del Equipo
            </div>
            <div class="card-body">
                <h2>Estadísticas Generales del Equipo</h2>
                <p>Resumen del rendimiento de todos los asesores bajo tu coordinación.</p>
            </div>
        </div>
        
        <!-- Estadísticas generales del equipo -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_asesores ?? 0; ?></div>
                <div class="stat-label">Total Asesores</div>
                <p class="mt-20">Asesores activos en el equipo</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_clientes ?? 0; ?></div>
                <div class="stat-label">Total Clientes</div>
                <p class="mt-20">Clientes asignados al equipo</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_llamadas ?? 0; ?></div>
                <div class="stat-label">Total Llamadas</div>
                <p class="mt-20">Llamadas realizadas por el equipo</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_ventas ?? 0; ?></div>
                <div class="stat-label">Total Ventas</div>
                <p class="mt-20">Ventas concretadas por el equipo</p>
            </div>
        </div>
        
        <!-- Métricas de cumplimiento -->
        <div class="card">
            <div class="card-header">
                🎯 Métricas de Cumplimiento
            </div>
            <div class="card-body">
                <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <div class="stat-number" style="color: white;"><?php echo $promedio_cumplimiento ?? 0; ?>%</div>
                        <div class="stat-label" style="color: white;">Promedio Cumplimiento</div>
                        <p class="mt-20" style="color: #d1fae5;">Promedio del equipo en llamadas</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                        <div class="stat-number" style="color: white;"><?php echo $total_asesores > 0 ? round(($total_ventas / $total_asesores), 1) : 0; ?></div>
                        <div class="stat-label" style="color: white;">Promedio Ventas/Asesor</div>
                        <p class="mt-20" style="color: #dbeafe;">Ventas promedio por asesor</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <div class="stat-number" style="color: white;"><?php echo $total_clientes > 0 ? round(($total_llamadas / $total_clientes), 1) : 0; ?></div>
                        <div class="stat-label" style="color: white;">Promedio Llamadas/Cliente</div>
                        <p class="mt-20" style="color: #fef3c7;">Llamadas promedio por cliente</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resultados detallados por asesor -->
        <div class="card">
            <div class="card-header">
                👥 Resultados por Asesor
            </div>
            <div class="card-body">
                <?php if (!empty($asesores)): ?>
                    <?php foreach ($asesores as $asesor): ?>
                        <div class="stat-card mb-20" style="text-align: left; padding: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3 style="margin: 0; color: #1f2937;"><?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?></h3>
                                <span class="asesor-status <?php echo $asesor['estado'] === 'Activo' ? 'status-activo' : 'status-inactivo'; ?>">
                                    <?php echo htmlspecialchars($asesor['estado'] ?? ''); ?>
                                </span>
                            </div>
                            
                            <!-- Métricas principales -->
                            <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
                                <div style="text-align: center; padding: 15px; background: #f8fafc; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #3b82f6;"><?php echo $asesor['total_clientes'] ?? 0; ?></div>
                                    <div style="font-size: 0.8rem; color: #6b7280;">Clientes</div>
                                </div>
                                
                                <div style="text-align: center; padding: 15px; background: #f8fafc; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #10b981;"><?php echo $asesor['llamadas_realizadas'] ?? 0; ?></div>
                                    <div style="font-size: 0.8rem; color: #6b7280;">Llamadas</div>
                                </div>
                                
                                <div style="text-align: center; padding: 15px; background: #f8fafc; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #f59e0b;"><?php echo $asesor['ventas_realizadas'] ?? 0; ?></div>
                                    <div style="font-size: 0.8rem; color: #6b7280;">Ventas</div>
                                </div>
                                
                                <div style="text-align: center; padding: 15px; background: #f8fafc; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #8b5cf6;"><?php echo $asesor['porcentaje_llamadas'] ?? 0; ?>%</div>
                                    <div style="font-size: 0.8rem; color: #6b7280;">Cumplimiento</div>
                                </div>
                            </div>
                            
                            <!-- Estadísticas detalladas -->
                            <?php if (!empty($asesor['tipificaciones'])): ?>
                                <div style="margin-bottom: 15px;">
                                    <h4 style="color: #374151; margin-bottom: 10px;">📈 Tipificaciones del Mes</h4>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                                        <?php foreach ($asesor['tipificaciones'] as $tipificacion): ?>
                                            <div style="padding: 10px; background: #f3f4f6; border-radius: 6px; text-align: center;">
                                                <div style="font-weight: bold; color: #1f2937;"><?php echo htmlspecialchars($tipificacion['resultado'] ?? ''); ?></div>
                                                <div style="color: #6b7280; font-size: 0.9rem;"><?php echo $tipificacion['cantidad']; ?> casos</div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Botones de acción -->
                            <div style="text-align: center; margin-top: 20px;">
                                <button class="btn btn-primary" onclick="mostrarDetallesAsesor(<?php echo $asesor['id']; ?>)">
                                    📊 Ver Progreso Detallado
                                </button>
                                <a href="index.php?action=exportar_gestion_asesor&asesor_id=<?php echo $asesor['id']; ?>" class="btn btn-success">
                                    📥 Exportar Reporte
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <strong>No hay asesores asignados.</strong> Los resultados aparecerán aquí una vez que tengas asesores en tu equipo.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Acciones rápidas -->
        <div class="card">
            <div class="card-header">
                🚀 Acciones Rápidas
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <a href="index.php?action=exportar_gestion_todos_asesores" class="btn btn-success" style="width: 100%; padding: 15px;">
                            📊 Exportar Reporte General del Equipo
                        </a>
                    </div>
                    <div class="form-group">
                        <a href="index.php?action=reportes_exportacion" class="btn btn-primary" style="width: 100%; padding: 15px;">
                            📈 Ver Todos los Reportes
                        </a>
                    </div>
                    <div class="form-group">
                        <a href="index.php?action=dashboard" class="btn btn-secondary" style="width: 100%; padding: 15px;">
                            🏠 Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalles del Asesor -->
    <div id="modalDetalles" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📊 Detalles del Asesor</h2>
                <span class="close" onclick="cerrarModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="modalLoading" class="loading">
                    <p>Cargando información del asesor...</p>
                </div>
                
                <div id="modalContent" style="display: none;">
                    <!-- Barra de búsqueda de clientes -->
                    <div class="search-section" style="margin-bottom: 20px;">
                        <h4>🔍 Buscar Cliente Específico</h4>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 250px;">
                                <input type="text" 
                                       id="searchCliente" 
                                       placeholder="Buscar cliente por nombre, cédula o teléfono..." 
                                       style="width: 100%; padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem;">
                            </div>
                            <button onclick="buscarCliente()" style="padding: 10px 16px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem;">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button onclick="limpiarBusqueda()" style="padding: 10px 16px; background: #6b7280; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem;">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                            <button id="btnLiberarTodos" 
                                    onclick="confirmarLiberarTodos()" 
                                    style="padding: 10px 12px; background: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem;"
                                    title="Liberar Todos los Clientes">
                                <i class="fas fa-unlock-alt"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="filtros-section">
                        <h4>🔍 Filtros de Búsqueda por Tipificación</h4>
                        <div class="filtros-grid">
                            <div class="filtro-group">
                                <label class="filtro-label">Estado de Gestión:</label>
                                <select id="filtroGestion" class="filtro-select" onchange="aplicarFiltros()">
                                    <option value="todos">Todos</option>
                                    <option value="gestionado">Gestionado</option>
                                    <option value="no_gestionado">No Gestionado</option>
                                </select>
                            </div>
                            
                            
                            <!-- Filtros específicos de tipificación -->
                            <div class="filtro-group">
                                <label class="filtro-label">Tipificación Específica:</label>
                                <select id="filtroTipificacionEspecifica" class="filtro-select" onchange="aplicarFiltros()">
                                    <option value="todos">Todas las tipificaciones</option>
                                    <option value="BUZÓN DE VOZ">📞 Buzón de Voz</option>
                                    <option value="SUBSIDIADO">🏥 Subsidiado</option>
                                    <option value="NO LE INTERESA">😞 No Le Interesa</option>
                                    <option value="NO TIENE EPS">❌ No Tiene EPS</option>
                                    <option value="NO ES EL TITULAR">👤 No Es El Titular</option>
                                    <option value="NÚMERO FUERA DE SERVICIO">📵 Número Fuera de Servicio</option>
                                    <option value="VOLVER A LLAMAR">🔄 Volver a Llamar</option>
                                    <option value="NÚMERO INCORRECTO">📞 Número Incorrecto</option>
                                    <option value="INTERESADO">✅ Interesado</option>
                                    <option value="Agendado">📅 Agendado</option>
                                    <option value="No Contesta">❌ No Contesta</option>
                                    <option value="Información Enviada">📧 Información Enviada</option>
                                    <option value="No Interesado">😞 No Interesado</option>
                                    <option value="VENTA INGRESADA">💰 Venta Ingresada</option>
                                    <option value="SIN COBERTURA">📡 Sin Cobertura</option>
                                    <option value="FALLECIDO">🕊️ Fallecido</option>
                                    <option value="Venta Exitosa">💰 Venta Exitosa</option>
                                    <option value="sin_gestion">⭕ Sin Gestión</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Información del filtro activo -->
                        <div id="infoFiltroActivo" class="info-filtro" style="display: none;">
                            <i class="fas fa-info-circle"></i>
                            <span id="textoFiltroActivo"></span>
                        </div>
                        
                    </div>
                    
                    <div class="filtros-fechas-modal">
                        <h4>📅 Filtros por Fechas</h4>
                        <div class="filtros-fechas-grid-modal">
                            <div class="filtro-fecha-group-modal">
                                <label class="filtro-fecha-label-modal">Fecha de inicio:</label>
                                <input type="date" id="fechaInicioModal" class="filtro-fecha-input-modal" onchange="aplicarFiltros()">
                            </div>
                            <div class="filtro-fecha-group-modal">
                                <label class="filtro-fecha-label-modal">Fecha de fin:</label>
                                <input type="date" id="fechaFinModal" class="filtro-fecha-input-modal" onchange="aplicarFiltros()">
                            </div>
                            <div class="filtro-fecha-group-modal">
                                <label class="filtro-fecha-label-modal">Período:</label>
                                <select id="periodoRapidoModal" class="filtro-fecha-input-modal" onchange="aplicarPeriodoRapidoModal()">
                                    <option value="">Seleccionar período</option>
                                    <option value="hoy">Hoy</option>
                                    <option value="ayer">Ayer</option>
                                    <option value="semana">Esta semana</option>
                                    <option value="mes">Este mes</option>
                                    <option value="trimestre">Este trimestre</option>
                                    <option value="año">Este año</option>
                                </select>
                            </div>
                            <div class="filtro-fecha-group-modal">
                                <button type="button" class="btn-limpiar-fechas-modal" onclick="limpiarFiltrosFechasModal()">
                                    <i class="fas fa-times"></i> Limpiar Fechas
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="mensajeFiltros"></div>
                    
                    <!-- Métricas del filtro aplicado -->
                    <div id="metricasFiltro" class="metricas-filtro" style="display: none;">
                        <div class="metricas-grid">
                            <div class="metrica-item">
                                <div class="metrica-numero" id="metricaFiltrados">0</div>
                                <div class="metrica-label">Clientes Filtrados</div>
                            </div>
                            <div class="metrica-item">
                                <div class="metrica-numero" id="metricaGestionados">0</div>
                                <div class="metrica-label">Total Gestionados</div>
                            </div>
                            <div class="metrica-item">
                                <div class="metrica-numero" id="metricaAsignados">0</div>
                                <div class="metrica-label">Total Asignados</div>
                            </div>
                            <div class="metrica-item">
                                <div class="metrica-porcentaje" id="metricaPorcentaje">0%</div>
                                <div class="metrica-label">Porcentaje</div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="tablaClientes"></div>
                    
                    <!-- Paginación -->
                    <div id="paginacionClientes" class="paginacion" style="display: none;">
                        <div class="paginacion-info">
                            <span id="infoPaginacion">Mostrando 1-10 de 0 clientes</span>
                        </div>
                        <button id="btnPrimera" class="btn-pagina" onclick="cambiarPagina(1)">«</button>
                        <button id="btnAnterior" class="btn-pagina" onclick="cambiarPagina('anterior')">‹</button>
                        <div id="numerosPagina"></div>
                        <button id="btnSiguiente" class="btn-pagina" onclick="cambiarPagina('siguiente')">›</button>
                        <button id="btnUltima" class="btn-pagina" onclick="cambiarPagina('ultima')">»</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let asesorActualId = null;
        let datosAsesor = null;

        function mostrarDetallesAsesor(asesorId) {
            asesorActualId = asesorId;
            document.getElementById('modalDetalles').style.display = 'block';
            document.getElementById('modalLoading').style.display = 'block';
            document.getElementById('modalContent').style.display = 'none';
            
            cargarDetallesAsesor(asesorId);
        }

        function cerrarModal() {
            document.getElementById('modalDetalles').style.display = 'none';
            asesorActualId = null;
            datosAsesor = null;
            
            // Limpiar filtros de tipificación
            document.getElementById('filtroTipificacionEspecifica').value = 'todos';
            
            // Limpiar filtros de fechas
            document.getElementById('fechaInicioModal').value = '';
            document.getElementById('fechaFinModal').value = '';
            document.getElementById('periodoRapidoModal').value = '';
            
            // Limpiar búsqueda
            document.getElementById('searchCliente').value = '';
            
            // Limpiar contenido
            document.getElementById('tablaClientes').innerHTML = '';
            document.getElementById('mensajeFiltros').innerHTML = '';
            document.getElementById('paginacionClientes').style.display = 'none';
            document.getElementById('metricasFiltro').style.display = 'none';
            document.getElementById('infoFiltroActivo').style.display = 'none';
        }

        function cargarDetallesAsesor(asesorId) {
            // Obtener información básica del asesor
            fetch(`index.php?action=get_detalles_asesor&asesor_id=${asesorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        cerrarModal();
                        return;
                    }
                    
                    datosAsesor = data;
                    document.getElementById('modalLoading').style.display = 'none';
                    document.getElementById('modalContent').style.display = 'block';
                    
                    // Actualizar título del modal
                    document.querySelector('.modal-title').textContent = `📊 ${data.asesor.nombre_completo}`;
                    
                    // Cargar clientes iniciales
                    cargarClientesAsesor();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los detalles del asesor');
                    cerrarModal();
                });
        }

        function cargarClientesAsesor() {
            if (!asesorActualId || !datosAsesor) return;
            
            const filtros = {
                asesor_id: asesorActualId,
                gestion: document.getElementById('filtroGestion').value,
                tipificacion_especifica: document.getElementById('filtroTipificacionEspecifica').value,
                fecha_inicio: document.getElementById('fechaInicioModal').value,
                fecha_fin: document.getElementById('fechaFinModal').value,
                busqueda: document.getElementById('searchCliente').value
            };
            
            const params = new URLSearchParams(filtros);
            
            fetch(`index.php?action=get_detalles_asesor&${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('mensajeFiltros').innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                        return;
                    }
                    
                    mostrarClientes(data.clientes || []);
                    actualizarPaginacion(data.paginacion || {});
                    actualizarMetricas(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('mensajeFiltros').innerHTML = '<div class="alert alert-danger">Error al cargar los clientes</div>';
                });
        }

        function mostrarClientes(clientes) {
            const tabla = document.getElementById('tablaClientes');
            
            if (clientes.length === 0) {
                tabla.innerHTML = '<div class="alert alert-info">No se encontraron clientes con los filtros aplicados</div>';
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                <th>Última Gestión</th>
                                <th>Resultado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            clientes.forEach(cliente => {
                // Determinar el color del badge según el resultado
                let badgeClass = 'badge-secondary';
                let resultadoTexto = cliente.resultado || 'Sin gestión';
                
                if (cliente.resultado) {
                    if (cliente.resultado.includes('Venta') || cliente.resultado.includes('venta')) {
                        badgeClass = 'badge-success';
                    } else if (cliente.resultado.includes('Volver') || cliente.resultado.includes('volver')) {
                        badgeClass = 'badge-warning';
                    } else if (cliente.resultado.includes('No contesta') || cliente.resultado.includes('No interesado')) {
                        badgeClass = 'badge-danger';
                    }
                }
                
                html += `
                    <tr>
                        <td>
                            <strong>${cliente.cliente_nombre || cliente.nombre || 'N/A'}</strong><br>
                            <small>${cliente.cedula || 'N/A'}</small>
                        </td>
                        <td>
                            ${cliente.telefono || 'N/A'}<br>
                            <small>${cliente.celular2 || ''}</small>
                        </td>
                        <td>
                            ${cliente.fecha_gestion ? new Date(cliente.fecha_gestion).toLocaleDateString() : 'N/A'}
                        </td>
                        <td>
                            <span class="badge ${badgeClass}">
                                ${resultadoTexto}
                            </span>
                        </td>
                        <td>
                            <a href="index.php?action=gestionar_cliente&id=${cliente.asignacion_id || cliente.id}" class="btn btn-sm btn-primary">
                                Gestionar
                            </a>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            tabla.innerHTML = html;
        }

        function actualizarPaginacion(paginacion) {
            const paginacionDiv = document.getElementById('paginacionClientes');
            
            if (!paginacion || paginacion.total_paginas <= 1) {
                paginacionDiv.style.display = 'none';
                return;
            }
            
            paginacionDiv.style.display = 'block';
            document.getElementById('infoPaginacion').textContent = 
                `Mostrando ${paginacion.inicio}-${paginacion.fin} de ${paginacion.total} clientes`;
        }

        function actualizarMetricas(data) {
            const metricasDiv = document.getElementById('metricasFiltro');
            const metricas = data.metricas || {};
            
            // Actualizar los números
            document.getElementById('metricaFiltrados').textContent = metricas.clientes_filtrados || 0;
            document.getElementById('metricaGestionados').textContent = metricas.total_gestionados || 0;
            document.getElementById('metricaAsignados').textContent = metricas.total_asignados || 0;
            document.getElementById('metricaPorcentaje').textContent = (metricas.porcentaje || 0) + '%';
            
            // Mostrar las métricas
            metricasDiv.style.display = 'block';
            
            // Obtener el filtro activo para mostrar información contextual
            const filtroActivo = document.getElementById('filtroTipificacionEspecifica').value;
            const filtroGestion = document.getElementById('filtroGestion').value;
            const fechaInicio = document.getElementById('fechaInicioModal').value;
            const fechaFin = document.getElementById('fechaFinModal').value;
            
            let infoFiltro = '';
            if (filtroActivo !== 'todos') {
                const opcionSeleccionada = document.getElementById('filtroTipificacionEspecifica').selectedOptions[0].text;
                infoFiltro += `Filtro: ${opcionSeleccionada}`;
            }
            if (filtroGestion !== 'todos') {
                infoFiltro += infoFiltro ? ` | Gestión: ${filtroGestion}` : `Gestión: ${filtroGestion}`;
            }
            if (fechaInicio || fechaFin) {
                const fechaTexto = fechaInicio && fechaFin ? `${fechaInicio} - ${fechaFin}` : (fechaInicio || fechaFin);
                infoFiltro += infoFiltro ? ` | Fecha: ${fechaTexto}` : `Fecha: ${fechaTexto}`;
            }
            
            if (infoFiltro) {
                document.getElementById('textoFiltroActivo').textContent = infoFiltro;
                document.getElementById('infoFiltroActivo').style.display = 'block';
            } else {
                document.getElementById('infoFiltroActivo').style.display = 'none';
            }
        }

        function aplicarFiltros() {
            cargarClientesAsesor();
        }

        function cambiarFiltroTipificacion() {
            const contacto = document.getElementById('filtroContacto').value;
            const subTipificacionGroup = document.getElementById('filtroSubTipificacionGroup');
            const subTipificacion = document.getElementById('filtroSubTipificacion');
            
            if (contacto === 'todos') {
                subTipificacionGroup.style.display = 'none';
                subTipificacion.innerHTML = '<option value="todos">Todos los resultados</option>';
            } else {
                subTipificacionGroup.style.display = 'block';
                // Aquí se cargarían las sub-tipificaciones según el contacto
                subTipificacion.innerHTML = '<option value="todos">Todos los resultados</option>';
            }
            
            aplicarFiltros();
        }

        function buscarCliente() {
            aplicarFiltros();
        }

        function limpiarBusqueda() {
            document.getElementById('searchCliente').value = '';
            aplicarFiltros();
        }

        function limpiarFiltrosFechasModal() {
            document.getElementById('fechaInicioModal').value = '';
            document.getElementById('fechaFinModal').value = '';
            document.getElementById('periodoRapidoModal').value = '';
            aplicarFiltros();
        }

        function aplicarPeriodoRapidoModal() {
            const periodo = document.getElementById('periodoRapidoModal').value;
            const hoy = new Date();
            let fechaInicio, fechaFin;
            
            switch (periodo) {
                case 'hoy':
                    fechaInicio = fechaFin = hoy.toISOString().split('T')[0];
                    break;
                case 'ayer':
                    const ayer = new Date(hoy);
                    ayer.setDate(hoy.getDate() - 1);
                    fechaInicio = fechaFin = ayer.toISOString().split('T')[0];
                    break;
                case 'semana':
                    const inicioSemana = new Date(hoy);
                    inicioSemana.setDate(hoy.getDate() - hoy.getDay());
                    fechaInicio = inicioSemana.toISOString().split('T')[0];
                    fechaFin = hoy.toISOString().split('T')[0];
                    break;
                case 'mes':
                    fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
                    fechaFin = hoy.toISOString().split('T')[0];
                    break;
                case 'trimestre':
                    const trimestre = Math.floor(hoy.getMonth() / 3);
                    fechaInicio = new Date(hoy.getFullYear(), trimestre * 3, 1).toISOString().split('T')[0];
                    fechaFin = hoy.toISOString().split('T')[0];
                    break;
                case 'año':
                    fechaInicio = new Date(hoy.getFullYear(), 0, 1).toISOString().split('T')[0];
                    fechaFin = hoy.toISOString().split('T')[0];
                    break;
            }
            
            if (fechaInicio && fechaFin) {
                document.getElementById('fechaInicioModal').value = fechaInicio;
                document.getElementById('fechaFinModal').value = fechaFin;
                aplicarFiltros();
            }
        }

        function confirmarLiberarTodos() {
            if (!asesorActualId) return;
            
            if (confirm('¿Estás seguro de que quieres liberar TODOS los clientes de este asesor? Esta acción no se puede deshacer.')) {
                // Aquí se implementaría la lógica para liberar todos los clientes
                alert('Funcionalidad de liberación masiva en desarrollo');
            }
        }

        function cambiarPagina(pagina) {
            // Aquí se implementaría la lógica de paginación
            console.log('Cambiar a página:', pagina);
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modalDetalles');
            if (event.target === modal) {
                cerrarModal();
            }
        }
    </script>
    
    <?php require_once 'shared_footer.php'; ?>
</body>
</html>
