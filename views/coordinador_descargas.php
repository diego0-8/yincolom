<?php
// Archivo: views/coordinador_descargas.php
// Vista para descargas CSV y reportes del coordinador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php require_once 'shared_styles.php'; ?>
    <style>
        /* Contenedor principal centralizado */
        .descargas-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background: #f8fafc;
            min-height: 100vh;
        }
        
        /* Header principal */
        .descargas-header {
            text-align: center;
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .descargas-header h1 {
            color: #1f2937;
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .descargas-header p {
            color: #6b7280;
            font-size: 1.1rem;
            margin: 0;
        }
        
        /* Botón de volver */
        .btn-volver {
            position: relative;
            top: auto;
            left: auto;
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            align-self: flex-start;
        }
        
        .btn-volver:hover {
            background: #4b5563;
            transform: translateY(-1px);
            color: white;
            text-decoration: none;
        }
        
        /* Grid de opciones de descarga */
        .descargas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .descarga-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .descarga-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .descarga-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        
        .card-icon.export { background: linear-gradient(135deg, #10b981, #059669); }
        .card-icon.report { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .card-icon.analytics { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .card-icon.team { background: linear-gradient(135deg, #f59e0b, #d97706); }
        
        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        
        .card-description {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        
        /* Formularios de filtros */
        .filtros-form {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .filtros-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }
        
        .form-input {
            padding: 10px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-select {
            padding: 10px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
            cursor: pointer;
        }
        
        /* Botones de acción */
        .card-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-1px);
        }
        
        .btn-info {
            background: #06b6d4;
            color: white;
        }
        
        .btn-info:hover {
            background: #0891b2;
            transform: translateY(-1px);
        }
        
        /* Sección de estadísticas */
        .stats-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .stats-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .descargas-container {
                padding: 15px;
            }
            
            .descargas-grid {
                grid-template-columns: 1fr;
            }
            
            .filtros-row {
                grid-template-columns: 1fr;
            }
            
            .card-actions {
                flex-direction: column;
            }
            
            .btn-volver {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                align-self: flex-start;
            }
        }
        
        /* Animaciones */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php 
    require_once 'shared_navbar.php';
    echo getNavbar('Descargas y Reportes', $_SESSION['user_role'] ?? '');
    ?>
    
    <div class="descargas-container">
        <!-- Header Principal -->
        <div class="descargas-header fade-in">
            <h1>📊 Descargas y Reportes</h1>
            <p>Exporta datos y genera reportes personalizados de tu equipo</p>
        </div>
        
        <!-- Botón de volver -->
        <div style="display: flex; justify-content: flex-start; margin-bottom: 20px;">
            <a href="index.php?action=dashboard" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="stats-section fade-in">
            <div class="stats-title">📈 Resumen de Actividad</div>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_asesores ?? 0; ?></div>
                    <div class="stat-label">Total Asesores</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_clientes ?? 0; ?></div>
                    <div class="stat-label">Total Clientes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_gestiones ?? 0; ?></div>
                    <div class="stat-label">Total Gestiones</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_ventas ?? 0; ?></div>
                    <div class="stat-label">Total Ventas</div>
                </div>
            </div>
        </div>
        
        <!-- Grid de opciones de descarga -->
        <div class="descargas-grid">
            <!-- Exportar Gestión de Asesor Específico -->
            <div class="descarga-card fade-in">
                <div class="card-header">
                    <div class="card-icon export">
                        <i class="fas fa-user-chart"></i>
                    </div>
                    <h3 class="card-title">Gestión por Asesor</h3>
                </div>
                <div class="card-description">
                    Exporta el historial completo de gestión de un asesor específico, incluyendo todas sus interacciones con clientes.
                </div>
                
                <form class="filtros-form" method="GET" action="index.php">
                    <input type="hidden" name="action" value="exportar_gestion_asesor">
                    <div class="filtros-row">
                        <div class="form-group">
                            <label class="form-label">Asesor</label>
                            <select name="asesor_id" class="form-select" required>
                                <option value="">Seleccionar asesor...</option>
                                <?php if (!empty($asesores)): ?>
                                    <?php foreach ($asesores as $asesor): ?>
                                        <option value="<?php echo $asesor['id']; ?>">
                                            <?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-input">
                        </div>
                    </div>
                    <div class="card-actions">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download"></i> Exportar CSV
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Exportar Gestión de Todos los Asesores -->
            <div class="descarga-card fade-in">
                <div class="card-header">
                    <div class="card-icon report">
                        <i class="fas fa-users-chart"></i>
                    </div>
                    <h3 class="card-title">Gestión de Todo el Equipo</h3>
                </div>
                <div class="card-description">
                    Exporta el historial completo de gestión de todos los asesores en un solo archivo CSV.
                </div>
                
                <form class="filtros-form" method="GET" action="index.php">
                    <input type="hidden" name="action" value="exportar_gestion_todos_asesores">
                    <div class="filtros-row">
                        <div class="form-group">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-input">
                        </div>
                    </div>
                    <div class="card-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download"></i> Exportar CSV
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Reporte Personalizado -->
            <div class="descarga-card fade-in">
                <div class="card-header">
                    <div class="card-icon analytics">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="card-title">Reporte Personalizado</h3>
                </div>
                <div class="card-description">
                    Genera reportes personalizados con filtros avanzados por asesor, resultado, tipo de gestión y fechas.
                </div>
                
                <form class="filtros-form" method="GET" action="index.php">
                    <input type="hidden" name="action" value="exportar_reporte_personalizado">
                    <div class="filtros-row">
                        <div class="form-group">
                            <label class="form-label">Asesor</label>
                            <select name="asesor_id" class="form-select">
                                <option value="">Todos los asesores</option>
                                <?php if (!empty($asesores)): ?>
                                    <?php foreach ($asesores as $asesor): ?>
                                        <option value="<?php echo $asesor['id']; ?>">
                                            <?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Resultado</label>
                            <select name="resultado" class="form-select">
                                <option value="">Todos los resultados</option>
                                <option value="INTERESADO">Interesado</option>
                                <option value="VENTA INGRESADA">Venta Ingresada</option>
                                <option value="VOLVER A LLAMAR">Volver a Llamar</option>
                                <option value="RECLAMO">Reclamo</option>
                                <option value="NO ES EL TITULAR">No es el Titular</option>
                                <option value="NO LE INTERESA">No le Interesa</option>
                                <option value="NO TIENE EPS">No tiene EPS</option>
                                <option value="SUBSIDIADO">Subsidiado</option>
                                <option value="SIN COBERTURA">Sin Cobertura</option>
                                <option value="BUZON DE VOZ">Buzón de Voz</option>
                                <option value="FALLECIDO">Fallecido</option>
                                <option value="NUMERO INCORRECTO">Número Incorrecto</option>
                                <option value="NUMERO FUERA DE SERVICIO">Número Fuera de Servicio</option>
                            </select>
                        </div>
                    </div>
                    <div class="filtros-row">
                        <div class="form-group">
                            <label class="form-label">Tipo de Gestión</label>
                            <select name="tipo_gestion" class="form-select">
                                <option value="">Todos los tipos</option>
                                <option value="Llamada de Venta">Llamada de Venta</option>
                                <option value="Cliente Interesado">Cliente Interesado</option>
                                <option value="Venta Ingresada">Venta Ingresada</option>
                                <option value="Llamada de Seguimiento">Llamada de Seguimiento</option>
                                <option value="Llamada de Gestión">Llamada de Gestión</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-input">
                        </div>
                    </div>
                    <div class="card-actions">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-chart-bar"></i> Generar Reporte
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Reportes de Exportación -->
            <div class="descarga-card fade-in">
                <div class="card-header">
                    <div class="card-icon team">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <h3 class="card-title">Reportes de Exportación</h3>
                </div>
                <div class="card-description">
                    Accede a reportes especializados de exportación y análisis de datos del equipo.
                </div>
                
                <div class="card-actions">
                    <a href="index.php?action=reportes_exportacion" class="btn btn-info">
                        <i class="fas fa-chart-pie"></i> Ver Reportes
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Información adicional -->
        <div class="stats-section fade-in">
            <div class="stats-title">ℹ️ Información sobre las Exportaciones</div>
            <div style="color: #6b7280; line-height: 1.6; text-align: center;">
                <p><strong>Formato CSV:</strong> Todos los reportes se exportan en formato CSV compatible con Excel, Google Sheets y otras herramientas de análisis.</p>
                <p><strong>Filtros:</strong> Utiliza los filtros disponibles para obtener datos más específicos y relevantes para tu análisis.</p>
                <p><strong>Fechas:</strong> Si no especificas fechas, se exportarán todos los datos disponibles del período seleccionado.</p>
            </div>
        </div>
    </div>
</body>
</html>
