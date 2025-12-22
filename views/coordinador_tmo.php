<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <?php require_once 'shared_styles.php'; ?>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin: 20px auto;
            padding: 30px;
            max-width: 700px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .page-header h1 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 1.8rem;
        }
        
        .page-header p {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 0;
        }
        
        .export-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        
        .export-icon {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .export-icon i {
            font-size: 2.5rem;
            color: #10b981;
            background: white;
            width: 60px;
            height: 60px;
            line-height: 60px;
            border-radius: 50%;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3);
        }
        
        .export-title {
            text-align: center;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .export-description {
            text-align: center;
            color: #6c757d;
            margin-bottom: 20px;
            line-height: 1.5;
            font-size: 0.9rem;
        }
        
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        
        .form-section h5 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
            text-align: center;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 12px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .form-control:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25);
        }
        
        .btn-export {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
            width: 100%;
        }
        
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5);
            color: white;
        }
        
        .quick-actions {
            text-align: center;
            margin-top: 20px;
        }
        
        .quick-actions h6 {
            color: #6c757d;
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .btn-quick {
            background: #6c757d;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.8rem;
            color: white;
            margin: 0 3px;
            transition: all 0.3s ease;
        }
        
        .btn-quick:hover {
            background: #5a6268;
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-quick.active {
            background: #10b981;
        }
        
        .info-box {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .info-box i {
            color: #059669;
            font-size: 1.2rem;
            margin-bottom: 8px;
        }
        
        .info-box p {
            color: #065f46;
            margin-bottom: 0;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .stats-row {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .stat-item {
            flex: 1;
            padding: 12px;
            margin: 0 8px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .main-container {
                margin: 15px;
                padding: 20px;
            }
            
            .stats-row {
                flex-direction: column;
            }
            
            .stat-item {
                margin: 3px 0;
            }
            
            .btn-quick {
                margin: 2px;
                padding: 5px 10px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <?php 
    require_once 'shared_navbar.php';
    echo getNavbar('Reporte TMO', $_SESSION['user_role'] ?? '');
    ?>

    <!-- Main Content -->
    <div class="container">
        <div class="main-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-clock"></i> Reporte TMO</h1>
                <p>Tiempo Medio de Operación - Breaks y Tiempo de Sesión</p>
            </div>
            
            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($asesores); ?></div>
                    <div class="stat-label">Asesores</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo date('M Y'); ?></div>
                    <div class="stat-label">Período</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">TMO</div>
                    <div class="stat-label">Formato</div>
                </div>
            </div>
            
            <!-- Info Box -->
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <p><strong>Información:</strong> Exporta el reporte de breaks y tiempo de sesión de todos los asesores en formato CSV.</p>
            </div>
            
            <!-- Export Section -->
            <div class="export-section">
                <div class="export-icon">
                    <i class="fas fa-stopwatch"></i>
                </div>
                
                <h3 class="export-title">Exportar Reporte TMO</h3>
                
                <p class="export-description">
                    Genera un reporte completo en CSV con todos los breaks y tiempo de sesión de tu equipo.
                </p>
                
                <!-- Export Form -->
                <div class="form-section">
                    <h5><i class="fas fa-cog"></i> Configuración de Exportación</h5>
                    
                    <form action="index.php" method="GET" id="exportForm">
                        <input type="hidden" name="action" value="exportar_tmo">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_inicio" class="form-label">
                                        <i class="fas fa-calendar"></i> Fecha de Inicio
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fecha_inicio" 
                                           name="fecha_inicio" 
                                           value="<?php echo date('Y-m-01'); ?>"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_fin" class="form-label">
                                        <i class="fas fa-calendar"></i> Fecha de Fin
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fecha_fin" 
                                           name="fecha_fin" 
                                           value="<?php echo date('Y-m-t'); ?>"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-export">
                            <i class="fas fa-download"></i> Exportar CSV TMO
                        </button>
                    </form>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h6><i class="fas fa-bolt"></i> Períodos Rápidos</h6>
                    <button class="btn btn-quick" onclick="setPeriod('hoy', this)">
                        <i class="fas fa-calendar-check"></i> Hoy
                    </button>
                    <button class="btn btn-quick" onclick="setPeriod('semana', this)">
                        <i class="fas fa-calendar-day"></i> Semana
                    </button>
                    <button class="btn btn-quick" onclick="setPeriod('mes', this)">
                        <i class="fas fa-calendar-week"></i> Mes
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para establecer períodos rápidos
        function setPeriod(period, buttonElement) {
            const today = new Date();
            let startDate, endDate;
            
            // Remover clase active de todos los botones
            document.querySelectorAll('.btn-quick').forEach(btn => {
                btn.classList.remove('active');
            });
            
            switch(period) {
                case 'hoy':
                    startDate = today.toISOString().split('T')[0];
                    endDate = today.toISOString().split('T')[0];
                    if (buttonElement) buttonElement.classList.add('active');
                    break;
                case 'semana':
                    const startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() - today.getDay());
                    startDate = startOfWeek.toISOString().split('T')[0];
                    endDate = today.toISOString().split('T')[0];
                    if (buttonElement) buttonElement.classList.add('active');
                    break;
                case 'mes':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
                    if (buttonElement) buttonElement.classList.add('active');
                    break;
            }
            
            document.getElementById('fecha_inicio').value = startDate;
            document.getElementById('fecha_fin').value = endDate;
        }
        
        // Validación del formulario
        document.getElementById('exportForm').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('fecha_inicio').value);
            const endDate = new Date(document.getElementById('fecha_fin').value);
            
            if (startDate > endDate) {
                e.preventDefault();
                alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
                return false;
            }
        });
        
        // Animación de carga al exportar
        document.getElementById('exportForm').addEventListener('submit', function() {
            const button = this.querySelector('.btn-export');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando CSV...';
            button.disabled = true;
        });
        
        // Establecer período por defecto (mes actual)
        document.addEventListener('DOMContentLoaded', function() {
            // Encontrar el botón de "mes" y activarlo
            const mesButton = document.querySelector('button[onclick*="mes"]');
            if (mesButton) {
                mesButton.classList.add('active');
            }
            
            // Establecer las fechas del mes actual
            const today = new Date();
            const startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            const endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            
            document.getElementById('fecha_inicio').value = startDate;
            document.getElementById('fecha_fin').value = endDate;
        });
    </script>
</body>
</html>

