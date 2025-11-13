<?php
// Archivo: views/coordinador_dashboard.php
// Vista del dashboard principal del coordinador con tarjetas compactas y modales
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
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background: #f8fafc;
            min-height: 100vh;
        }
        
        /* Header principal */
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            position: relative;
        }

        /* Campana de notificaciones */
        .notification-bell {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .notification-bell:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #1f2937;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            border: 2px solid white;
        }

        .notification-bell.no-notifications {
            background: #6b7280;
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
        }

        .notification-bell.no-notifications:hover {
            box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
        }
        
        .dashboard-header h1 {
            color: #1f2937;
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .dashboard-header p {
            color: #6b7280;
            font-size: 1.1rem;
            margin: 0;
        }
        
        /* Grid de estadísticas principales */
        .stats-grid-main {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card-main {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .stat-card-main:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-number-main {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .stat-label-main {
            color: #6b7280;
            font-size: 1rem;
            font-weight: 500;
        }
        
        /* Sección de búsqueda y filtros */
        .search-filters-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-input-group {
            flex: 1;
            min-width: 300px;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }
        
        .btn-search {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .btn-search:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .btn-clear {
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .btn-clear:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }
        
        /* Filtros rápidos */
        .filtros-rapidos {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #6b7280;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .filter-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            transform: translateY(-1px);
        }
        
        .filter-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        /* Filtros de fechas */
        .filtros-fechas {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .filtros-fechas h3 {
            color: #1f2937;
            font-size: 1.3rem;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        
        .filtros-fechas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }
        
        .filtro-fecha-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filtro-fecha-label {
            font-weight: 500;
            color: #374151;
            font-size: 0.9rem;
        }
        
        .filtro-fecha-input {
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .filtro-fecha-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-aplicar-fechas {
            background: #10b981;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-aplicar-fechas:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        
        .btn-limpiar-fechas {
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-limpiar-fechas:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }
        
        /* Estilos para filtros activos */
        .filtros-activos {
            margin-top: 20px;
            padding: 15px;
            background: #eff6ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
        }
        
        .filtro-activo-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .filtro-activo-badge i {
            font-size: 0.8rem;
        }
        
        .btn-limpiar-filtro-activo {
            background: #ef4444;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.7rem;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-limpiar-filtro-activo:hover {
            background: #dc2626;
            transform: scale(1.05);
        }
        
        /* Paginación */
        .paginacion {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding: 20px 0;
        }
        
        .paginacion-info {
            color: #6b7280;
            font-size: 0.9rem;
            margin-right: 20px;
        }
        
        .btn-pagina {
            padding: 8px 12px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #6b7280;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
            min-width: 40px;
        }
        
        .btn-pagina:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            transform: translateY(-1px);
        }
        
        .btn-pagina.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .btn-pagina:disabled {
            background: #f3f4f6;
            color: #9ca3af;
            border-color: #e5e7eb;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Filtros de fechas del modal */
        .filtros-fechas-modal {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
        }
        
        .filtros-fechas-modal h4 {
            color: #1f2937;
            font-size: 1.1rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .filtros-fechas-grid-modal {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .filtro-fecha-group-modal {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .filtro-fecha-label-modal {
            font-weight: 500;
            color: #374151;
            font-size: 0.85rem;
        }
        
        .filtro-fecha-input-modal {
            padding: 8px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .filtro-fecha-input-modal:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        
        .btn-limpiar-fechas-modal {
            background: #6b7280;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }
        
        .btn-limpiar-fechas-modal:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }
        
        .search-results-info {
            background: #eff6ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            color: #0369a1;
        }
        
        .search-results-info i {
            margin-right: 8px;
        }
        
        /* Grid de asesores */
        .asesores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        /* Estilos para la tabla de asesores */
        .asesores-table-container {
            overflow-x: auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .asesores-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        
        .asesores-table th {
            background: #f8fafc;
            color: #374151;
            font-weight: 600;
            padding: 15px 12px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .asesores-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        
        .asesores-table tbody tr:hover {
            background: #f8fafc;
        }
        
        .asesor-row:nth-child(even) {
            background: #fafbfc;
        }
        
        .asesor-info {
            display: flex;
            flex-direction: column;
        }
        
        .asesor-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-block;
            text-align: center;
            min-width: 80px;
        }
        
        .status-activo {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-inactivo {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .text-center {
            text-align: center;
        }
        
        .btn-detalles-table {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .btn-detalles-table:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        /* Acciones rápidas */
        .actions-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .action-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-2px);
            background: #f1f5f9;
        }
        
        .action-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
        
        .action-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .action-description {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .btn-primary { background: #3b82f6; color: white; }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-info { background: #06b6d4; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 16px;
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background: #3b82f6;
            color: white;
            padding: 20px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
        }
        
        .close {
            color: red;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
        }
        
        .close:hover {
            opacity: 0.7;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .filtros-section {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .filtro-group {
            display: flex;
            flex-direction: column;
        }
        
        .filtro-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }
        
        .filtro-select {
            padding: 10px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: 0.9rem;
        }
        
        .clientes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .clientes-table th,
        .clientes-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .clientes-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        .clientes-table tr:hover {
            background: #f9fafb;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-gestionado { background: #dcfce7; color: #166534; }
        .badge-no-gestionado { background: #fef2f2; color: #dc2626; }
        .badge-contactado { background: #dbeafe; color: #1e40af; }
        .badge-no-contactado { background: #fef3c7; color: #92400e; }
        .badge-interesado { background: #dcfce7; color: #166534; }
        .badge-no-interesado { background: #fef2f2; color: #dc2626; }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 15px;
            }
            
            .stats-grid-main {
                grid-template-columns: 1fr;
            }
            
            .asesores-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-input-group {
                min-width: auto;
            }
            
            .filtros-rapidos {
                justify-content: center;
            }
        }
        
        /* Animaciones */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        /* Estilos para el Modal de Recordatorios Pendientes */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            width: 90%;
            max-height: 80vh;
            overflow: hidden;
        }

        .modal-header {
            background: #f8fafc;
            padding: 20px 25px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: #1f2937;
            font-size: 1.3rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6b7280;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .modal-body {
            padding: 25px;
            max-height: 60vh;
            overflow-y: auto;
        }

        /* Estilos para los Recordatorios Pendientes */
        .recordatorios-pendientes-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .recordatorio-pendiente-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .recordatorio-pendiente-item:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .recordatorio-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .cliente-info h4 {
            margin: 0 0 8px 0;
            color: #1f2937;
            font-size: 1.1rem;
        }

        .cliente-meta {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .fecha-programada {
            background: #3b82f6;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .tipificacion-actual {
            background: #fef3c7;
            color: #92400e;
            padding: 12px 15px;
            border-radius: 6px;
            border-left: 4px solid #f59e0b;
            margin-bottom: 15px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .comentarios {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 15px;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .acciones {
            text-align: right;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-size: 1.1rem;
        }

        .loading i {
            font-size: 2rem;
            margin-bottom: 15px;
            display: block;
            color: #3b82f6;
        }

        /* Responsive para el modal */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                max-height: 90vh;
            }

            .modal-header {
                padding: 15px 20px;
            }

            .modal-body {
                padding: 20px;
            }

            .recordatorio-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .fecha-programada {
                align-self: flex-start;
            }

            .acciones {
                flex-direction: column;
                gap: 8px;
            }
        }

        /* Estilos para el árbol de tipificación */
        .info-filtro {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #0369a1;
            font-size: 0.9rem;
        }
        
        .info-filtro i {
            color: #0284c7;
            font-size: 1rem;
        }
        
        .filtro-subtipificacion {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .filtro-subtipificacion h5 {
            color: #92400e;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Estilos para acciones masivas */
        .acciones-masivas {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border-radius: 12px;
            border: 2px solid #feb2b2;
            box-shadow: 0 4px 6px rgba(220, 53, 69, 0.1);
        }
        
        .acciones-masivas h5 {
            margin: 0 0 15px 0;
            color: #c53030;
            font-size: 1.1em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .acciones-masivas .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c53030 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 0.95em;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
        }
        
        .acciones-masivas .btn-danger:hover {
            background: linear-gradient(135deg, #c53030 0%, #a71e2a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.4);
        }
        
        .acciones-masivas .info-text {
            color: #744210;
            font-size: 0.9em;
            line-height: 1.4;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <?php 
    require_once 'shared_navbar.php';
    echo getNavbar('Dashboard Coordinador', $_SESSION['user_role'] ?? '');
    ?>
    
    <div class="dashboard-container">
        <!-- Header Principal -->
        <div class="dashboard-header fade-in">
            <h1>🎯 Dashboard del Coordinador</h1>
            <p>Gestiona cargas de CSV, asigna clientes a asesores y supervisa el rendimiento del equipo</p>

            <!-- Campana de Notificaciones -->
            <div class="notification-bell <?php echo ($datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0) > 0 ? '' : 'no-notifications'; ?>"
                 onclick="mostrarModalLlamadasPendientes()"
                 title="<?php echo ($datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0) > 0 ? 'Tienes recordatorios pendientes del equipo' : 'No hay recordatorios pendientes'; ?>">
                🔔
                <?php if (($datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0) > 0): ?>
                    <div class="notification-badge"><?php echo $datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0; ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Estadísticas Principales -->
        <div class="stats-grid-main fade-in">
            <div class="stat-card-main">
                <div class="stat-number-main"><?php echo $total_asesores ?? 0; ?></div>
                <div class="stat-label-main">Total Asesores</div>
            </div>
            
            <div class="stat-card-main">
                <div class="stat-number-main"><?php echo $total_clientes ?? 0; ?></div>
                <div class="stat-label-main">Total Clientes</div>
            </div>
            
            <div class="stat-card-main">
                <div class="stat-number-main"><?php echo $total_llamadas ?? 0; ?></div>
                <div class="stat-label-main">Total Llamadas</div>
            </div>
            
            <div class="stat-card-main">
                <div class="stat-number-main"><?php echo $total_ventas ?? 0; ?></div>
                <div class="stat-label-main">Total Ventas</div>
            </div>
        </div>
        
        <!-- Barra de búsqueda y filtros -->
        <div class="search-filters-section fade-in">
            <h3 style="margin-bottom: 20px; color: #1f2937; text-align: center;">🔍 Búsqueda y Filtros de Asesores</h3>
            
            <form method="GET" action="index.php" class="search-form">
                <input type="hidden" name="action" value="dashboard">
                <div class="search-input-group">
                    <input type="text" 
                           name="buscar" 
                           placeholder="Buscar asesor por nombre o usuario..." 
                           value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>"
                           class="search-input">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <button type="submit" class="btn btn-search">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <?php if (!empty($_GET['buscar'])): ?>
                    <a href="index.php?action=dashboard" class="btn btn-clear">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                <?php endif; ?>
            </form>
            
            <!-- Filtros rápidos -->
            <div class="filtros-rapidos">
                <button class="filter-btn active" onclick="filtrarAsesores('todos')">
                    👥 Todos (<?php echo $total_asesores ?? 0; ?>)
                </button>
                <button class="filter-btn" onclick="filtrarAsesores('activos')">
                    ✅ Activos
                </button>
                <button class="filter-btn" onclick="filtrarAsesores('con_clientes')">
                    📊 Con Clientes
                </button>
                <button class="filter-btn" onclick="filtrarAsesores('con_ventas')">
                    💰 Con Ventas
                </button>
            </div>
            
            <!-- Mensaje de búsqueda -->
            <?php if (!empty($_GET['buscar'])): ?>
                <div class="search-results-info">
                    <i class="fas fa-search"></i>
                    <strong>Búsqueda activa:</strong> Mostrando asesores que contengan "<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>"
                    <span class="search-results-count">(<?php echo count($asesores ?? []) !== 1 ? 's' : ''; ?>)</span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Filtros de fechas -->
        <div class="filtros-fechas fade-in">
            <h3>📅 Filtros por Fechas</h3>
            <div class="filtros-fechas-grid">
                <div class="filtro-fecha-group">
                    <label class="filtro-fecha-label">Fecha de inicio:</label>
                    <input type="date" id="fechaInicio" class="filtro-fecha-input" name="fecha_inicio" 
                           value="<?php echo $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-7 days')); ?>">
                </div>
                <div class="filtro-fecha-group">
                    <label class="filtro-fecha-label">Fecha de fin:</label>
                    <input type="date" id="fechaFin" class="filtro-fecha-input" name="fecha_fin" 
                           value="<?php echo $_GET['fecha_fin'] ?? date('Y-m-d'); ?>">
                </div>
                <div class="filtro-fecha-group">
                    <label class="filtro-fecha-label">Período:</label>
                    <select id="periodoRapido" class="filtro-fecha-input" onchange="aplicarPeriodoRapido()">
                        <option value="">Seleccionar período</option>
                        <option value="hoy">Hoy</option>
                        <option value="ayer">Ayer</option>
                        <option value="semana">Esta semana</option>
                        <option value="mes">Este mes</option>
                        <option value="trimestre">Este trimestre</option>
                        <option value="año">Este año</option>
                    </select>
                </div>
                <div class="filtro-fecha-group">
                    <button type="button" class="btn-aplicar-fechas" onclick="aplicarFiltrosFechas()">
                        <i class="fas fa-filter"></i> Aplicar Filtros
                    </button>
                </div>
                <div class="filtro-fecha-group">
                    <button type="button" class="btn-limpiar-fechas" onclick="limpiarFiltrosFechas()">
                        <i class="fas fa-times"></i> Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Indicador de filtros activos -->
        <?php if (isset($_GET['fecha_inicio']) || isset($_GET['fecha_fin'])): ?>
            <div class="filtros-activos fade-in">
                <div class="filtro-activo-badge">
                    <i class="fas fa-filter"></i>
                    <strong>Filtros aplicados:</strong> 
                    <?php if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])): ?>
                        <?php echo date('d/m/Y', strtotime($_GET['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($_GET['fecha_fin'])); ?>
                    <?php elseif (isset($_GET['fecha_inicio'])): ?>
                        Desde: <?php echo date('d/m/Y', strtotime($_GET['fecha_inicio'])); ?>
                    <?php elseif (isset($_GET['fecha_fin'])): ?>
                        Hasta: <?php echo date('d/m/Y', strtotime($_GET['fecha_fin'])); ?>
                    <?php endif; ?>
                    <button type="button" class="btn-limpiar-filtro-activo" onclick="limpiarFiltrosFechas()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Lista de Asesores -->
        <div class="search-filters-section fade-in">
            <h3 style="margin-bottom: 20px; color: #1f2937; text-align: center;">👥 Gestión de Asesores</h3>
            
            <?php if (!empty($asesores)): ?>
                <div class="asesores-table-container">
                    <table class="asesores-table">
                        <thead>
                            <tr>
                                <th>Asesor</th>
                                <th>Estado</th>
                                <th>Total Clientes</th>
                                <th>Gestiones</th>
                                <th>Contactos Efectivos</th>
                                <th>Tareas/Actividad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($asesores as $asesor): ?>
                                <tr class="asesor-row">
                                    <td>
                                        <div class="asesor-info">
                                            <strong><?php echo htmlspecialchars($asesor['nombre_completo'] ?? ''); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="asesor-status <?php echo $asesor['estado'] === 'Activo' ? 'status-activo' : 'status-inactivo'; ?>">
                                            <?php echo htmlspecialchars($asesor['estado'] ?? ''); ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?php echo $asesor['total_clientes'] ?? 0; ?></td>
                                    <td class="text-center"><?php echo $asesor['llamadas_realizadas'] ?? 0; ?></td>
                                    <td class="text-center"><?php echo $asesor['metricas']['contactos_efectivos'] ?? 0; ?></td>
                                    <td class="text-center">
                                        <?php if ($asesor['tareas_pendientes'] > 0): ?>
                                            <div style="background: #fef3c7; padding: 8px; border-radius: 6px; border-left: 4px solid #f59e0b;">
                                                <div style="font-weight: 600; color: #92400e; font-size: 0.9rem;">
                                                    📋 <?php echo $asesor['tareas_pendientes']; ?> Tarea<?php echo $asesor['tareas_pendientes'] > 1 ? 's' : ''; ?>
                                                </div>
                                                <div style="color: #744210; font-size: 0.8rem;">
                                                    <?php echo $asesor['clientes_pendientes_tareas']; ?> cliente<?php echo $asesor['clientes_pendientes_tareas'] > 1 ? 's' : ''; ?> pendiente<?php echo $asesor['clientes_pendientes_tareas'] > 1 ? 's' : ''; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div style="background: #d1fae5; padding: 8px; border-radius: 6px; border-left: 4px solid #10b981;">
                                                <div style="font-weight: 600; color: #065f46; font-size: 0.9rem;">
                                                    ✅ Sin tareas
                                                </div>
                                                <div style="color: #047857; font-size: 0.8rem;">
                                                    📞 <?php echo $asesor['gestiones_hoy']; ?> gestiones hoy
                                                </div>
                                                <div style="color: #047857; font-size: 0.8rem;">
                                                    🎯 <?php echo $asesor['contactos_efectivos_hoy']; ?> contactos efectivos
                                                </div>
                                                <div style="color: #047857; font-size: 0.8rem;">
                                                    💰 <?php echo $asesor['acuerdos_hoy']; ?> acuerdos
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-detalles-table" onclick="mostrarDetallesAsesor(<?php echo $asesor['id']; ?>)">
                                            📊 Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info" style="text-align: center; padding: 40px; color: #6b7280; background: #eff6ff; border: 1px solid #bae6fd; border-radius: 12px;">
                    <div style="font-size: 3rem; margin-bottom: 20px;">👥</div>
                    <h4 style="color: #1e40af; margin-bottom: 15px;">No hay asesores asignados</h4>
                    <p style="margin-bottom: 20px; font-size: 1.1rem;">
                        <strong>Los asesores aparecerán aquí una vez que sean asignados formalmente a tu coordinación.</strong>
                    </p>
                    <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #dbeafe;">
                        <p style="margin: 0; color: #374151;">
                            <strong>Para asignar asesores:</strong><br>
                            1. Contacta al administrador del sistema<br>
                            2. Solicita la asignación de asesores a tu coordinación<br>
                            3. Los asesores aparecerán automáticamente aquí
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="actions-section fade-in">
            <h3 style="margin-bottom: 20px; color: #1f2937; text-align: center;">🚀 Acciones Rápidas</h3>
            
            <div class="actions-grid">
                <div class="action-card">
                    <div class="action-icon">📁</div>
                    <div class="action-title">Subir Archivo CSV</div>
                    <div class="action-description">Carga nuevos clientes desde un archivo CSV</div>
                    <a href="index.php?action=subir_excel" class="btn btn-primary">
                        Subir CSV
                    </a>
                </div>
                
                <div class="action-card">
                    <div class="action-icon">📋</div>
                    <div class="action-title">Ver Cargas de CSV</div>
                    <div class="action-description">Revisa todas las cargas realizadas</div>
                    <a href="index.php?action=list_cargas" class="btn btn-secondary">
                        Ver Cargas
                    </a>
                </div>
                
                <div class="action-card">
                    <div class="action-icon">⚡</div>
                    <div class="action-title">Gestionar Tareas</div>
                    <div class="action-description">Supervisa y gestiona las tareas del equipo</div>
                    <a href="index.php?action=tareas_coordinador" class="btn btn-success">
                        Gestionar
                    </a>
                </div>
                
                <div class="action-card">
                    <div class="action-icon">📊</div>
                    <div class="action-title">Descargas y Reportes</div>
                    <div class="action-description">Exporta datos y genera reportes CSV</div>
                    <a href="index.php?action=descargas" class="btn btn-warning">
                        Ver Reportes
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Información del sistema -->
        <div class="search-filters-section fade-in">
            <h3 style="margin-bottom: 20px; color: #1f2937; text-align: center;">ℹ️ Información del Sistema</h3>
            
            <div style="text-align: center; color: #6b7280;">
                <div style="margin-bottom: 15px;">
                    <strong>Coordinador:</strong> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'No identificado'); ?>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Total de asesores:</strong> <?php echo count($asesores ?? []); ?>
                </div>
                <div>
                    <strong>Última actualización:</strong> <?php echo date('d/m/Y H:i'); ?>
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
                                <i class="fas fa-unlock-alt"></i> Liberar
                            </button>
                        </div>
                    </div>
                    
                    <div class="filtros-section">
                        <h4>🔍 Filtros de Búsqueda por Tipificación</h4>
                        <div class="filtros-grid">
                            <div class="filtro-group">
                                <label class="filtro-label">Estado de Gestión:</label>
                                <select id="filtroGestion" class="filtro-select" onchange="aplicarFiltros()">
                                    <option value="gestionado" selected>Gestionado</option>
                                    <option value="todos">Todos</option>
                                    <option value="no_gestionado">No Gestionado</option>
                                </select>
                            </div>
                            
                            <!-- Árbol de Tipificación Principal -->
                            <div class="filtro-group">
                                <label class="filtro-label">Estado de Contacto:</label>
                                <select id="filtroContacto" class="filtro-select" onchange="cambiarFiltroTipificacion()">
                                    <option value="todos">Todos</option>
                                    <option value="contactado">✅ CONTACTADO</option>
                                    <option value="no_contactado">❌ NO CONTACTADO</option>
                                </select>
                            </div>
                            
                            <!-- Sub-tipificaciones dinámicas -->
                            <div class="filtro-group" id="filtroSubTipificacionGroup" style="display: none;">
                                <label class="filtro-label">Resultado del Contacto:</label>
                                <select id="filtroSubTipificacion" class="filtro-select" onchange="aplicarFiltros()">
                                    <option value="todos">Todos los resultados</option>
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
            const modal = document.getElementById('modalDetalles');
            if (modal) {
                modal.style.display = 'none';
            }
            asesorActualId = null;
            datosAsesor = null;

            // Limpiar filtros de tipificación
            const filtroContacto = document.getElementById('filtroContacto');
            if (filtroContacto) filtroContacto.value = 'todos';

            const filtroSubTipificacionGroup = document.getElementById('filtroSubTipificacionGroup');
            if (filtroSubTipificacionGroup) filtroSubTipificacionGroup.style.display = 'none';

            const infoFiltroActivo = document.getElementById('infoFiltroActivo');
            if (infoFiltroActivo) infoFiltroActivo.style.display = 'none';
        }

        function cargarDetallesAsesor(asesorId) {
            const url = `index.php?action=get_detalles_asesor&asesor_id=${asesorId}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    return response.text(); // Get as text first to debug
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        
                        if (data.error) {
                            alert('Error: ' + data.error);
                            return;
                        }
                        
                        datosAsesor = data;
                        mostrarContenidoModal(data);
                    } catch (parseError) {
                        console.error('JSON Parse Error:', parseError);
                        alert('Error: La respuesta del servidor no es válida.');
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('Error al cargar los datos del asesor: ' + error.message);
                });
        }

        function mostrarContenidoModal(data) {
            document.getElementById('modalLoading').style.display = 'none';
            document.getElementById('modalContent').style.display = 'block';
            
            // Actualizar título del modal
            document.querySelector('.modal-title').textContent = `📊 ${data.asesor.nombre_completo}`;
            
            // Limpiar filtros de fechas del modal
            document.getElementById('fechaInicioModal').value = '';
            document.getElementById('fechaFinModal').value = '';
            document.getElementById('periodoRapidoModal').value = '';
            
            // Limpiar filtros de tipificación
            document.getElementById('filtroGestion').value = 'gestionado';
            document.getElementById('filtroContacto').value = 'todos';
            document.getElementById('filtroSubTipificacionGroup').style.display = 'none';
            document.getElementById('infoFiltroActivo').style.display = 'none';
            
            // Limpiar mensaje de filtros
            const mensajeFiltros = document.getElementById('mensajeFiltros');
            if (mensajeFiltros) {
                mensajeFiltros.innerHTML = '';
            }
            
            // Mostrar tabla de clientes
            mostrarTablaClientes(data.clientes);
        }

        function mostrarTablaClientes(clientes) {
            // Inicializar variables de paginación
            clientesOriginales = [...clientes];
            clientesFiltrados = [...clientes];
            paginaActual = 1;
            
            mostrarTablaClientesPaginada();
        }
        
        function mostrarTablaClientesPaginada() {
            const tablaContainer = document.getElementById('tablaClientes');
            const paginacionContainer = document.getElementById('paginacionClientes');
            
            if (!clientesFiltrados || clientesFiltrados.length === 0) {
                tablaContainer.innerHTML = '<div class="no-data">No hay clientes para mostrar con los filtros actuales.</div>';
                paginacionContainer.style.display = 'none';
                return;
            }
            
            // Calcular paginación
            const totalClientes = clientesFiltrados.length;
            const totalPaginas = Math.ceil(totalClientes / clientesPorPagina);
            const inicio = (paginaActual - 1) * clientesPorPagina;
            const fin = Math.min(inicio + clientesPorPagina, totalClientes);
            
            // Obtener clientes de la página actual
            const clientesPagina = clientesFiltrados.slice(inicio, fin);
            
            // Generar tabla
            let tablaHTML = `
                <table class="clientes-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Teléfono</th>
                            <th>Estado Gestión</th>
                            <th>Tipificación</th>
                            <th>Fecha Gestión</th>
                            <th>Monto Venta</th>
                            <th>Duración</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            clientesPagina.forEach(cliente => {
                // Determinar si está gestionado basado en si tiene resultado
                const gestionado = (cliente.resultado && cliente.resultado !== '') ? 'Gestionado' : 'No Gestionado';
                const tipificacion = cliente.resultado || 'Sin tipificar';
                const fechaGestion = cliente.fecha_gestion ? new Date(cliente.fecha_gestion).toLocaleDateString('es-ES') : 'N/A';
                const montoVenta = cliente.monto_venta ? `$${parseInt(cliente.monto_venta).toLocaleString('es-CO')}` : 'N/A';
                const duracion = cliente.duracion_llamada ? `${cliente.duracion_llamada} min` : 'N/A';
                
                // Preparar información de observaciones y próxima llamada
                const observaciones = cliente.observaciones || cliente.comentarios || 'Sin observaciones';
                const proximaFecha = cliente.proxima_fecha || '';
                const proximaHora = cliente.proxima_hora || '';
                
                let proximaLlamadaInfo = '';
                if (proximaFecha && proximaHora) {
                    const fechaFormateada = new Date(proximaFecha).toLocaleDateString('es-ES');
                    proximaLlamadaInfo = `<br><small style="color: #3b82f6;">📅 Próxima: ${fechaFormateada} ${proximaHora}</small>`;
                }
                
                tablaHTML += `
                    <tr>
                        <td>${cliente.cliente_nombre || 'N/A'}</td>
                        <td>${cliente.cedula || 'N/A'}</td>
                        <td>${cliente.telefono || 'N/A'}</td>
                        <td><span class="badge ${gestionado === 'Gestionado' ? 'badge-gestionado' : 'badge-no-gestionado'}">${gestionado}</span></td>
                        <td><span class="badge badge-interesado">${tipificacion}</span></td>
                        <td>${fechaGestion}</td>
                        <td>${montoVenta}</td>
                        <td>${duracion}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="mostrarObservaciones('${cliente.cliente_nombre || 'Cliente'}', '${observaciones.replace(/'/g, "\\'")}', '${proximaFecha}', '${proximaHora}')" title="Ver observaciones">
                                👁️
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            tablaHTML += '</tbody></table>';
            tablaContainer.innerHTML = tablaHTML;
            
            // Mostrar paginación
            mostrarPaginacion(totalPaginas, totalClientes, inicio, fin);
        }
        
        function mostrarPaginacion(totalPaginas, totalClientes, inicio, fin) {
            const paginacionContainer = document.getElementById('paginacionClientes');
            const infoPaginacion = document.getElementById('infoPaginacion');
            const numerosPagina = document.getElementById('numerosPagina');
            
            // Actualizar información de paginación
            infoPaginacion.textContent = `Mostrando ${inicio + 1}-${fin} de ${totalClientes} clientes`;
            
            // Generar números de página
            let numerosHTML = '';
            const maxNumeros = 5; // Máximo 5 números de página visibles
            
            let inicioNumero = Math.max(1, paginaActual - Math.floor(maxNumeros / 2));
            let finNumero = Math.min(totalPaginas, inicioNumero + maxNumeros - 1);
            
            // Ajustar si estamos cerca del final
            if (finNumero - inicioNumero < maxNumeros - 1) {
                inicioNumero = Math.max(1, finNumero - maxNumeros + 1);
            }
            
            for (let i = inicioNumero; i <= finNumero; i++) {
                numerosHTML += `<button class="btn-pagina ${i === paginaActual ? 'active' : ''}" onclick="cambiarPagina(${i})">${i}</button>`;
            }
            
            numerosPagina.innerHTML = numerosHTML;
            
            // Actualizar estado de botones
            document.getElementById('btnPrimera').disabled = paginaActual === 1;
            document.getElementById('btnAnterior').disabled = paginaActual === 1;
            document.getElementById('btnSiguiente').disabled = paginaActual === totalPaginas;
            document.getElementById('btnUltima').disabled = paginaActual === totalPaginas;
            
            paginacionContainer.style.display = 'block';
        }
        
        function cambiarPagina(nuevaPagina) {
            if (nuevaPagina === 'anterior') {
                nuevaPagina = paginaActual - 1;
            } else if (nuevaPagina === 'siguiente') {
                nuevaPagina = paginaActual + 1;
            } else if (nuevaPagina === 'ultima') {
                nuevaPagina = Math.ceil(clientesFiltrados.length / clientesPorPagina);
            }
            
            if (nuevaPagina >= 1 && nuevaPagina <= Math.ceil(clientesFiltrados.length / clientesPorPagina)) {
                paginaActual = nuevaPagina;
                mostrarTablaClientesPaginada();
            }
        }

        // Función para manejar el árbol de tipificación
        function cambiarFiltroTipificacion() {
            const filtroContacto = document.getElementById('filtroContacto').value;
            const filtroSubTipificacionGroup = document.getElementById('filtroSubTipificacionGroup');
            const filtroSubTipificacion = document.getElementById('filtroSubTipificacion');
            const infoFiltroActivo = document.getElementById('infoFiltroActivo');
            const textoFiltroActivo = document.getElementById('textoFiltroActivo');
            
            // Limpiar opciones anteriores
            filtroSubTipificacion.innerHTML = '<option value="todos">Todos los resultados</option>';
            
            if (filtroContacto === 'contactado') {
                // Mostrar sub-tipificaciones para CONTACTADO
                filtroSubTipificacionGroup.style.display = 'block';
                infoFiltroActivo.style.display = 'block';
                textoFiltroActivo.textContent = 'Cliente respondió la llamada - Selecciona el resultado del contacto';
                
                // Agregar opciones para contactado
                const opcionesContactado = [
                    'INTERESADO',
                    'VENTA INGRESADA', 
                    'VOLVER A LLAMAR',
                    'RECLAMO',
                    'NO ES EL TITULAR',
                    'NO LE INTERESA',
                    'NO TIENE EPS',
                    'SUBSIDIADO',
                    'SIN COBERTURA'
                ];
                
                opcionesContactado.forEach(opcion => {
                    const option = document.createElement('option');
                    option.value = opcion;
                    option.textContent = opcion;
                    filtroSubTipificacion.appendChild(option);
                });
                
            } else if (filtroContacto === 'no_contactado') {
                // Mostrar sub-tipificaciones para NO CONTACTADO
                filtroSubTipificacionGroup.style.display = 'block';
                infoFiltroActivo.style.display = 'block';
                textoFiltroActivo.textContent = 'No se pudo establecer comunicación - Selecciona la razón del no contacto';
                
                // Agregar opciones para no contactado
                const opcionesNoContactado = [
                    'BUZÓN DE VOZ',
                    'FALLECIDO',
                    'NÚMERO INCORRECTO',
                    'NÚMERO FUERA DE SERVICIO'
                ];
                
                opcionesNoContactado.forEach(opcion => {
                    const option = document.createElement('option');
                    option.value = opcion;
                    option.textContent = opcion;
                    filtroSubTipificacion.appendChild(option);
                });
                
            } else {
                // Ocultar sub-tipificaciones si no hay filtro específico
                filtroSubTipificacionGroup.style.display = 'none';
                infoFiltroActivo.style.display = 'none';
            }
            
            // Aplicar filtros después del cambio
            aplicarFiltros();
        }

        function aplicarFiltros() {
            if (!asesorActualId) return;
            
            // Mostrar loading
            document.getElementById('modalLoading').style.display = 'block';
            document.getElementById('modalContent').style.display = 'none';
            
            const filtroGestion = document.getElementById('filtroGestion').value;
            const filtroContacto = document.getElementById('filtroContacto').value;
            const filtroSubTipificacion = document.getElementById('filtroSubTipificacion').value;
            const fechaInicio = document.getElementById('fechaInicioModal').value;
            const fechaFin = document.getElementById('fechaFinModal').value;
            
            // Construir URL con parámetros de filtro
            let url = `index.php?action=get_detalles_asesor&asesor_id=${asesorActualId}`;
            
            if (filtroGestion !== 'todos') {
                url += `&gestion=${filtroGestion}`;
            }
            if (filtroContacto !== 'todos') {
                url += `&contacto=${filtroContacto}`;
            }
            if (filtroSubTipificacion !== 'todos') {
                url += `&tipificacion=${encodeURIComponent(filtroSubTipificacion)}`;
            }
            if (fechaInicio) {
                url += `&fecha_inicio=${fechaInicio}`;
            }
            if (fechaFin) {
                url += `&fecha_fin=${fechaFin}`;
            }
            
            // Hacer consulta a la base de datos con filtros
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    
                    // Actualizar datos del asesor con los resultados filtrados
                    datosAsesor = data;
                    
                    // Ocultar loading y mostrar contenido
                    document.getElementById('modalLoading').style.display = 'none';
                    document.getElementById('modalContent').style.display = 'block';
                    
                    // Actualizar tabla con nuevos datos
                    mostrarTablaClientes(data.clientes);
                    
                    // Mostrar mensaje de resultados
                    mostrarMensajeFiltros(filtroGestion, filtroContacto, filtroSubTipificacion, fechaInicio, fechaFin, data.clientes.length);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al aplicar los filtros');
                    
                    // Ocultar loading y mostrar contenido anterior
                    document.getElementById('modalLoading').style.display = 'none';
                    document.getElementById('modalContent').style.display = 'block';
                });
        }
        
        function mostrarMensajeFiltros(filtroGestion, filtroContacto, filtroSubTipificacion, fechaInicio, fechaFin, totalResultados) {
            const mensajeResultados = document.getElementById('mensajeFiltros');
            if (mensajeResultados) {
                let mensajeFiltros = `Gestión: ${filtroGestion}, Contacto: ${filtroContacto}`;
                
                if (filtroSubTipificacion !== 'todos') {
                    mensajeFiltros += `, Resultado: ${filtroSubTipificacion}`;
                }
                
                if (fechaInicio || fechaFin) {
                    const fechaInicioFormateada = fechaInicio ? new Date(fechaInicio).toLocaleDateString('es-ES') : 'Sin límite';
                    const fechaFinFormateada = fechaFin ? new Date(fechaFin).toLocaleDateString('es-ES') : 'Sin límite';
                    mensajeFiltros += `, Fechas: ${fechaInicioFormateada} - ${fechaFinFormateada}`;
                }
                
                mensajeResultados.innerHTML = `
                    <div class="alert alert-info" style="margin-top: 15px; padding: 10px; background: #eff6ff; border: 1px solid #bae6fd; border-radius: 8px; color: #0369a1;">
                        <i class="fas fa-filter"></i> 
                        <strong>Filtros aplicados:</strong> 
                        ${mensajeFiltros}
                        <br>
                        <strong>Resultados:</strong> ${totalResultados} cliente${totalResultados !== 1 ? 's' : ''} encontrado${totalResultados !== 1 ? 's' : ''}
                    </div>
                `;
            }
        }
        
        function buscarCliente() {
            if (!asesorActualId) return;
            
            const searchTerm = document.getElementById('searchCliente').value.trim();
            if (!searchTerm) {
                alert('Por favor ingresa un término de búsqueda');
                return;
            }
            
            // Aplicar búsqueda en los datos existentes
            if (datosAsesor && datosAsesor.clientes) {
                clientesFiltrados = datosAsesor.clientes.filter(cliente => 
                    cliente.nombre?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    cliente.cedula?.includes(searchTerm) ||
                    cliente.telefono?.includes(searchTerm)
                );
                
                if (clientesFiltrados.length === 0) {
                    document.getElementById('tablaClientes').innerHTML = '<div class="no-data">No se encontraron clientes con ese término de búsqueda.</div>';
                    document.getElementById('paginacionClientes').style.display = 'none';
                } else {
                    // Resetear a la primera página
                    paginaActual = 1;
                    mostrarTablaClientesPaginada();
                }
            }
        }
        
        function limpiarBusqueda() {
            document.getElementById('searchCliente').value = '';
            
            // Limpiar filtros de fechas del modal
            document.getElementById('fechaInicioModal').value = '';
            document.getElementById('fechaFinModal').value = '';
            document.getElementById('periodoRapidoModal').value = '';
            
            // Limpiar filtros de tipificación
            document.getElementById('filtroGestion').value = 'gestionado';
            document.getElementById('filtroContacto').value = 'todos';
            document.getElementById('filtroSubTipificacionGroup').style.display = 'none';
            document.getElementById('infoFiltroActivo').style.display = 'none';
            
            // Recargar datos sin filtros
            if (asesorActualId) {
                aplicarFiltros(); // Esto recargará todos los datos
            }
        }

        // Función para mostrar observaciones en un modal
        function mostrarObservaciones(nombreCliente, observaciones, proximaFecha, proximaHora) {
            const modalHtml = `
                <div class="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;">
                    <div style="background: white; padding: 30px; border-radius: 12px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h3 style="margin: 0; color: #1f2937;">👁️ Observaciones del Cliente</h3>
                            <button onclick="this.closest('.modal-overlay').remove()" style="background: none; border: none; font-size: 1.5rem; color: #6b7280; cursor: pointer;">&times;</button>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <h4 style="color: #374151; margin-bottom: 10px;">👤 ${nombreCliente}</h4>
                        </div>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6; margin-bottom: 20px;">
                            <h5 style="color: #1f2937; margin-bottom: 10px;">📝 Observaciones:</h5>
                            <p style="color: #4b5563; line-height: 1.6; margin: 0;">${observaciones}</p>
                        </div>
                        ${proximaFecha && proximaHora ? `
                            <div style="background: #fef3c7; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                                <h5 style="color: #92400e; margin-bottom: 10px;">📅 Próxima Llamada Programada:</h5>
                                <p style="color: #744210; margin: 0; font-weight: 500;">
                                    ${new Date(proximaFecha).toLocaleDateString('es-ES', { 
                                        weekday: 'long', 
                                        year: 'numeric', 
                                        month: 'long', 
                                        day: 'numeric' 
                                    })} a las ${proximaHora}
                                </p>
                            </div>
                        ` : `
                            <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; border-left: 4px solid #6b7280;">
                                <p style="color: #6b7280; margin: 0; font-style: italic;">No hay próxima llamada programada</p>
                            </div>
                        `}
                        <div style="text-align: right; margin-top: 20px;">
                            <button onclick="this.closest('.modal-overlay').remove()" style="background: #6b7280; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">Cerrar</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Agregar funcionalidad para cerrar al hacer clic fuera del modal
            const observacionesModal = document.querySelector('.modal-overlay:last-child');
            if (observacionesModal) {
                observacionesModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.remove();
                    }
                });
            }
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modalDetalles');
            if (event.target === modal) {
                cerrarModal();
            }
        }
        
        // Variables globales para paginación
        let paginaActual = 1;
        let clientesPorPagina = 10;
        let clientesFiltrados = [];
        let clientesOriginales = [];
        
        // Función para filtrar asesores
        function filtrarAsesores(tipo) {
            // Remover clase active de todos los botones
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Agregar clase active al botón clickeado
            event.target.classList.add('active');
            
            // Aquí puedes implementar la lógica de filtrado
            // Por ahora solo cambiamos la clase visual
            console.log('Filtro aplicado:', tipo);
        }
        
        // Función para aplicar filtros de fechas
        function aplicarFiltrosFechas() {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            
            if (!fechaInicio && !fechaFin) {
                alert('Por favor selecciona al menos una fecha');
                return;
            }
            
            if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
                alert('La fecha de inicio no puede ser mayor que la fecha de fin');
                return;
            }
            
            // Recargar el dashboard con los filtros de fechas
            recargarDashboardConFiltros(fechaInicio, fechaFin);
        }
        
        // Función para recargar el dashboard con filtros
        function recargarDashboardConFiltros(fechaInicio = null, fechaFin = null) {
            let url = 'index.php?action=coordinador&subaction=dashboard';
            
            if (fechaInicio) {
                url += '&fecha_inicio=' + encodeURIComponent(fechaInicio);
            }
            if (fechaFin) {
                url += '&fecha_fin=' + encodeURIComponent(fechaFin);
            }
            
            // Mostrar indicador de carga
            mostrarIndicadorCarga();
            
            // Recargar la página con los filtros
            window.location.href = url;
        }
        
        // Función para mostrar indicador de carga
        function mostrarIndicadorCarga() {
            const overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            `;
            
            overlay.innerHTML = `
                <div style="background: white; padding: 30px; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 15px;">⏳</div>
                    <div>Cargando dashboard con filtros...</div>
                </div>
            `;
            
            document.body.appendChild(overlay);
        }
        
        // Función para aplicar período rápido
        function aplicarPeriodoRapido() {
            const periodo = document.getElementById('periodoRapido').value;
            if (!periodo) return;
            
            const hoy = new Date();
            let fechaInicio = new Date();
            let fechaFin = new Date();
            
            switch (periodo) {
                case 'hoy':
                    fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
                    fechaFin = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
                    break;
                case 'ayer':
                    fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() - 1);
                    fechaFin = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() - 1);
                    break;
                case 'semana':
                    const inicioSemana = hoy.getDate() - hoy.getDay();
                    fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), inicioSemana);
                    fechaFin = new Date(hoy.getFullYear(), hoy.getMonth(), inicioSemana + 6);
                    break;
                case 'mes':
                    fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                    fechaFin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
                    break;
                case 'trimestre':
                    const trimestre = Math.floor(hoy.getMonth() / 3);
                    fechaInicio = new Date(hoy.getFullYear(), trimestre * 3, 1);
                    fechaFin = new Date(hoy.getFullYear(), (trimestre + 1) * 3, 0);
                    break;
                case 'año':
                    fechaInicio = new Date(hoy.getFullYear(), 0, 1);
                    fechaFin = new Date(hoy.getFullYear(), 11, 31);
                    break;
            }
            
            // Actualizar los campos de fecha
            document.getElementById('fechaInicio').value = fechaInicio.toISOString().split('T')[0];
            document.getElementById('fechaFin').value = fechaFin.toISOString().split('T')[0];
            
            // Limpiar el select de período
            document.getElementById('periodoRapido').value = '';
            
            // Aplicar filtros automáticamente
            recargarDashboardConFiltros(fechaInicio.toISOString().split('T')[0], fechaFin.toISOString().split('T')[0]);
        }
        
        // Función para verificar si hay filtros activos y actualizar la interfaz
        function verificarFiltrosActivos() {
            const urlParams = new URLSearchParams(window.location.search);
            const fechaInicio = urlParams.get('fecha_inicio');
            const fechaFin = urlParams.get('fecha_fin');
            
            if (fechaInicio || fechaFin) {
                // Si hay filtros activos, actualizar los campos de fecha
                if (fechaInicio) {
                    document.getElementById('fechaInicio').value = fechaInicio;
                }
                if (fechaFin) {
                    document.getElementById('fechaFin').value = fechaFin;
                }
                
                // Limpiar el select de período
                document.getElementById('periodoRapido').value = '';
            }
        }
        
        // Ejecutar verificación de filtros activos al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            verificarFiltrosActivos();
        });
        
        // Función para limpiar filtros de fechas
        function limpiarFiltrosFechas() {
            // Restaurar fechas por defecto (últimos 7 días)
            const hoy = new Date();
            const hace7Dias = new Date(hoy.getTime() - (7 * 24 * 60 * 60 * 1000));
            
            // Actualizar los campos de fecha
            document.getElementById('fechaInicio').value = hace7Dias.toISOString().split('T')[0];
            document.getElementById('fechaFin').value = hoy.toISOString().split('T')[0];
            document.getElementById('periodoRapido').value = '';
            
            // Recargar dashboard con fechas por defecto
            recargarDashboardConFiltros(hace7Dias.toISOString().split('T')[0], hoy.toISOString().split('T')[0]);
        }
        
        // Función para aplicar período rápido en el modal
        function aplicarPeriodoRapidoModal() {
            const periodo = document.getElementById('periodoRapidoModal').value;
            if (!periodo) return;
            
            const hoy = new Date();
            let fechaInicio = new Date();
            let fechaFin = new Date();
            
            switch (periodo) {
                case 'hoy':
                    fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
                    fechaFin = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
                    break;
                case 'ayer':
                    fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() - 1);
                    fechaFin = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() - 1);
                    break;
                case 'semana':
                    const inicioSemana = hoy.getDate() - hoy.getDay();
                    fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), inicioSemana);
                    fechaFin = new Date(hoy.getFullYear(), hoy.getMonth(), inicioSemana + 6);
                    break;
                case 'mes':
                    fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                    fechaFin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
                    break;
                case 'trimestre':
                    const trimestre = Math.floor(hoy.getMonth() / 3);
                    fechaInicio = new Date(hoy.getFullYear(), trimestre * 3, 1);
                    fechaFin = new Date(hoy.getFullYear(), (trimestre + 1) * 3, 0);
                    break;
                case 'año':
                    fechaInicio = new Date(hoy.getFullYear(), 0, 1);
                    fechaFin = new Date(hoy.getFullYear(), 11, 31);
                    break;
            }
            
            document.getElementById('fechaInicioModal').value = fechaInicio.toISOString().split('T')[0];
            document.getElementById('fechaFinModal').value = fechaFin.toISOString().split('T')[0];
            
            // Aplicar filtros automáticamente
            aplicarFiltros();
        }
        
        // Función para limpiar filtros de fechas del modal
        function limpiarFiltrosFechasModal() {
            document.getElementById('fechaInicioModal').value = '';
            document.getElementById('fechaFinModal').value = '';
            document.getElementById('periodoRapidoModal').value = '';
            
            // Aplicar filtros automáticamente
            aplicarFiltros();
        }
        
        // Función para aplicar filtros con fechas
        function aplicarFiltrosConFechas(fechaInicio, fechaFin) {
            if (!datosAsesor || !datosAsesor.clientes) return;
            
            // Filtrar clientes por fechas
            clientesFiltrados = datosAsesor.clientes.filter(cliente => {
                if (!cliente.fecha_creacion) return true; // Si no hay fecha, incluir
                
                const fechaCliente = new Date(cliente.fecha_creacion);
                let cumpleFiltros = true;
                
                if (fechaInicio) {
                    const fechaInicioObj = new Date(fechaInicio);
                    if (fechaCliente < fechaInicioObj) cumpleFiltros = false;
                }
                
                if (fechaFin) {
                    const fechaFinObj = new Date(fechaFin);
                    if (fechaCliente > fechaFinObj) cumpleFiltros = false;
                }
                
                return cumpleFiltros;
            });
            
            console.log('Filtrado por fechas:', { 
                fechaInicio, 
                fechaFin, 
                totalOriginal: datosAsesor.clientes.length,
                totalFiltrado: clientesFiltrados.length 
            });
            
            paginaActual = 1;
            mostrarTablaClientesPaginada();
            
            // Mostrar mensaje de filtros aplicados
            const mensajeFiltros = document.getElementById('mensajeFiltros');
            if (mensajeFiltros) {
                const fechaInicioFormateada = fechaInicio ? new Date(fechaInicio).toLocaleDateString('es-ES') : 'Sin límite';
                const fechaFinFormateada = fechaFin ? new Date(fechaFin).toLocaleDateString('es-ES') : 'Sin límite';
                
                mensajeFiltros.innerHTML = `
                    <div class="alert alert-info" style="margin-top: 15px; padding: 10px; background: #eff6ff; border: 1px solid #bae6fd; border-radius: 8px; color: #0369a1;">
                        <i class="fas fa-calendar"></i> 
                        <strong>Filtros de fechas aplicados:</strong> 
                        Desde: ${fechaInicioFormateada} - Hasta: ${fechaFinFormateada}
                        <br>
                        <strong>Resultados:</strong> ${clientesFiltrados.length} cliente${clientesFiltrados.length !== 1 ? 's' : ''} encontrado${clientesFiltrados.length !== 1 ? 's' : ''}
                    </div>
                `;
            }
        }

        // Función para confirmar la liberación de todos los clientes
        function confirmarLiberarTodos() {
            console.log('confirmarLiberarTodos() ejecutada');
            console.log('asesorActualId:', asesorActualId);
            console.log('datosAsesor:', datosAsesor);
            
            if (!asesorActualId) {
                alert('No se ha seleccionado un asesor.');
                return;
            }

            if (!datosAsesor || !datosAsesor.asesor) {
                alert('Error: No se pudieron obtener los datos del asesor.');
                return;
            }

            const confirmacion = confirm(`¿Estás seguro de que quieres liberar TODOS los clientes del asesor "${datosAsesor.asesor.nombre_completo}"? Esta acción es irreversible.`);
            console.log('Confirmación del usuario:', confirmacion);
            
            if (confirmacion) {
                liberarTodosClientes();
            }
        }

        // Función para liberar todos los clientes de un asesor
        function liberarTodosClientes() {
            console.log('liberarTodosClientes() ejecutada');
            console.log('asesorActualId:', asesorActualId);
            
            const url = `index.php?action=liberar_clientes`;
            console.log('URL de la petición:', url);
            
            const formData = new FormData();
            formData.append('asesor_id', asesorActualId);
            
            console.log('Datos a enviar:', { asesor_id: asesorActualId });
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Respuesta del servidor:', response);
                console.log('Status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Respuesta en texto:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Datos parseados:', data);
                    
                    if (data.success) {
                        alert('Todos los clientes del asesor han sido liberados exitosamente.');
                        // Recargar la página para reflejar el cambio en el dashboard
                        window.location.reload();
                    } else {
                        alert('Error al liberar los clientes: ' + (data.error || 'Desconocido'));
                    }
                } catch (e) {
                    console.error('Error al parsear JSON:', e);
                    console.error('Texto recibido:', text);
                    alert('Error: Respuesta inesperada del servidor. Revisa la consola para más detalles.');
                }
            })
            .catch(error => {
                console.error('Error al liberar clientes:', error);
                alert('Error de red al liberar los clientes. Revisa la consola para más detalles.');
            });
        }

    </script>

    <!-- Modal de Recordatorios Pendientes -->
    <div id="modalLlamadasPendientes" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔔 Recordatorios Pendientes del Equipo</h3>
                <button type="button" class="modal-close" onclick="cerrarModalLlamadasPendientes()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="contenidoLlamadasPendientes">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        Cargando recordatorios pendientes...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para mostrar modal de recordatorios pendientes
        function mostrarModalLlamadasPendientes() {
            const llamadasPendientes = <?php echo json_encode($datos_dashboard['llamadas_pendientes'] ?? []); ?>;

            if (llamadasPendientes.length === 0) {
                alert('No hay recordatorios pendientes para hoy.');
                return;
            }

            // Mostrar el modal
            document.getElementById('modalLlamadasPendientes').style.display = 'block';
            document.body.style.overflow = 'hidden';

            // Cargar contenido del modal
            mostrarRecordatoriosEnModal(llamadasPendientes);
        }

        function cerrarModalLlamadasPendientes() {
            const modal = document.getElementById('modalLlamadasPendientes');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function mostrarRecordatoriosEnModal(recordatorios) {
            const contenidoModal = document.getElementById('contenidoLlamadasPendientes');

            let html = '<div class="recordatorios-pendientes-container">';

            recordatorios.forEach((recordatorio, index) => {
                const fecha = new Date(recordatorio.proxima_fecha || recordatorio.fecha_gestion).toLocaleString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                html += `
                    <div class="recordatorio-pendiente-item">
                        <div class="recordatorio-header">
                            <div class="cliente-info">
                                <h4>👤 ${recordatorio.cliente_nombre || 'Cliente'}</h4>
                                <div class="cliente-meta">
                                    📱 ${recordatorio.telefono || recordatorio.celular2 || 'Sin teléfono'}
                                    <br>
                                    👨‍💼 <strong>Asesor:</strong> ${recordatorio.asesor_nombre || 'N/A'}
                                </div>
                            </div>
                            <div class="fecha-programada">
                                ⏰ ${fecha}
                            </div>
                        </div>
                        <div class="tipificacion-actual">
                            🏷️ <strong>Tipificación:</strong> ${recordatorio.resultado || 'N/A'}
                        </div>
                        <div class="comentarios">
                            💬 <strong>Comentarios:</strong><br>
                            ${recordatorio.comentarios || 'Sin comentarios específicos'}
                        </div>
                        <div class="acciones">
                            <button class="btn btn-primary btn-sm" onclick="transferirRecordatorio(${recordatorio.cliente_id}, ${recordatorio.asesor_id}, '${recordatorio.asesor_nombre}')">
                                Transferir
                            </button>
                            <a href="index.php?action=gestionar_cliente&id=${recordatorio.asignacion_id}" class="btn btn-secondary btn-sm">
                                Gestionar
                            </a>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            contenidoModal.innerHTML = html;
        }

        // Función para transferir recordatorio
        function transferirRecordatorio(clienteId, asesorOrigenId, asesorNombre) {
            const asesoresDisponibles = <?php echo json_encode($datos_dashboard['asesores_disponibles'] ?? []); ?>;

            if (asesoresDisponibles.length <= 1) {
                alert('No hay otros asesores disponibles para transferir el recordatorio.');
                return;
            }

            // Crear opciones para el select
            let opciones = '<option value="">Seleccionar asesor...</option>';
            asesoresDisponibles.forEach(asesor => {
                if (asesor.id != asesorOrigenId) {
                    opciones += `<option value="${asesor.id}">${asesor.nombre_completo}</option>`;
                }
            });

            // Mostrar modal de confirmación con select
            const modalHtml = `
                <div class="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;">
                    <div style="background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%;">
                        <h3 style="margin-top: 0; color: #1f2937;">Transferir Recordatorio</h3>
                        <p>¿Deseas transferir este recordatorio del asesor <strong>${asesorNombre}</strong> a otro asesor?</p>
                        <div style="margin: 20px 0;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Seleccionar asesor destino:</label>
                            <select id="asesorDestino" style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem;">
                                ${opciones}
                            </select>
                        </div>
                        <div style="text-align: right; margin-top: 20px;">
                            <button onclick="const modal = this.closest('.modal-overlay'); if (modal) modal.remove();" style="background: #6b7280; color: white; border: none; padding: 10px 20px; border-radius: 8px; margin-right: 10px; cursor: pointer;">Cancelar</button>
                            <button onclick="confirmarTransferencia(${clienteId}, ${asesorOrigenId})" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">Transferir</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Agregar funcionalidad para cerrar al hacer clic fuera del modal
            const transferModal = document.querySelector('.modal-overlay:last-child');
            if (transferModal) {
                transferModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.remove();
                    }
                });
            }
        }

        // Función para confirmar la transferencia
        function confirmarTransferencia(clienteId, asesorOrigenId) {
            const asesorDestinoId = document.getElementById('asesorDestino').value;

            if (!asesorDestinoId) {
                alert('Por favor selecciona un asesor destino.');
                return;
            }

            // Enviar petición AJAX
            const formData = new FormData();
            formData.append('cliente_id', clienteId);
            formData.append('asesor_origen_id', asesorOrigenId);
            formData.append('asesor_destino_id', asesorDestinoId);

            fetch('index.php?action=transferir_recordatorio', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Recordatorio transferido exitosamente.');
                    // Cerrar modal de transferencia
                    const transferModal = document.querySelector('.modal-overlay');
                    if (transferModal) transferModal.remove();
                    // Cerrar modal de recordatorios y recargar
                    cerrarModalLlamadasPendientes();
                    location.reload();
                } else {
                    alert('Error al transferir el recordatorio: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de red al transferir el recordatorio.');
            });
        }

        // Cerrar modal al hacer clic fuera de él
        const modalLlamadasPendientes = document.getElementById('modalLlamadasPendientes');
        console.log('coordinador_dashboard: modalLlamadasPendientes element:', modalLlamadasPendientes);
        if (modalLlamadasPendientes) {
            modalLlamadasPendientes.addEventListener('click', function(e) {
                if (e.target === this) {
                    cerrarModalLlamadasPendientes();
                }
            });
        } else {
            console.error('coordinador_dashboard: modalLlamadasPendientes element not found');
        }
    </script>
</body>
</html>
