<?php
// Archivo: views/shared_styles.php
// Estilos compartidos para todas las vistas del sistema
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Reset y configuración base */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        background: #f0f2f5; 
        color: #1f2937;
        line-height: 1.6;
    }
    
    /* Barra de navegación superior */
    .top-navbar {
        background: #1f2937;
        color: white;
        padding: 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    .nav-container {
        max-width: 1400px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
    }
    
    .nav-menu {
        display: flex;
        list-style: none;
        gap: 0;
    }
    
    .nav-menu li {
        margin: 0;
    }
    
    .nav-menu a {
        color: white;
        text-decoration: none;
        padding: 20px 25px;
        display: block;
        transition: all 0.3s ease;
        font-weight: 500;
        border-bottom: 3px solid transparent;
    }
    
    .nav-menu a:hover {
        background: rgba(255,255,255,0.1);
        border-bottom-color: #3b82f6;
    }
    
    .nav-menu a.active {
        background: rgba(255,255,255,0.1);
        border-bottom-color: #3b82f6;
    }
    
    .user-section {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 15px 0;
    }
    
    .user-greeting {
        color: #e5e7eb;
        font-size: 0.9rem;
    }
    
    .user-name {
        color: white;
        font-weight: 600;
    }
    
    .logout-btn {
        background: #dc2626;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border: 1px solid #dc2626;
    }
    
    .logout-btn:hover {
        background: #b91c1c;
        border-color: #b91c1c;
        transform: translateY(-1px);
    }
    
    /* Botón de Tiempo de Sesión */
    .session-time-btn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .session-time-btn:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .session-time-btn i {
        font-size: 1.1rem;
    }
    
    /* Botón de Teléfono en Navbar */
    .telefono-nav-btn {
        background: #10b981;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        margin-right: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
        border: 1px solid #10b981;
    }
    
    .telefono-nav-btn:hover {
        background: #059669;
        border-color: #059669;
        transform: translateY(-1px);
    }
    
    .telefono-nav-btn.disconnected {
        background: #6b7280;
        border-color: #6b7280;
    }
    
    .telefono-nav-btn.disconnected:hover {
        background: #4b5563;
        border-color: #4b5563;
    }
    
    .telefono-nav-btn.connected {
        background: #10b981;
        border-color: #10b981;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    
    /* Contenedor principal */
    .main-container {
        max-width: 1400px;
        margin: 30px auto;
        padding: 0 20px;
    }
    
    /* Tarjetas principales */
    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin-bottom: 30px;
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        padding: 20px 25px;
        font-size: 1.3rem;
        font-weight: 600;
    }
    
    .card-body {
        padding: 25px;
    }
    
    /* Formularios */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
    }
    
    .form-input, .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f9fafb;
    }
    
    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background: white;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    /* Botones */
    .btn {
        display: inline-block;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    
    .btn-primary:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-success:hover {
        background: #059669;
        transform: translateY(-2px);
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: #6b7280;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #4b5563;
        transform: translateY(-2px);
    }
    
    /* Tablas */
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }
    
    th, td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    
    th {
        background: #f8fafc;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
    }
    
    tr:hover {
        background: #f9fafb;
    }
    
    /* Grid de estadísticas */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        text-align: center;
        border-left: 4px solid #3b82f6;
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #3b82f6;
        margin: 10px 0;
    }
    
    .stat-label {
        color: #6b7280;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    /* Alertas */
    .alert {
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid;
    }
    
    .alert-info {
        background: #dbeafe;
        border-color: #3b82f6;
        color: #1e40af;
    }
    
    .alert-success {
        background: #d1fae5;
        border-color: #10b981;
        color: #065f46;
    }
    
    .alert-warning {
        background: #fef3c7;
        border-color: #f59e0b;
        color: #92400e;
    }
    
    .alert-danger {
        background: #fee2e2;
        border-color: #ef4444;
        color: #991b1b;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .nav-menu {
            display: none;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .main-container {
            padding: 0 15px;
            margin: 20px auto;
        }
    }
    
    /* Utilidades */
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .mb-20 { margin-bottom: 20px; }
    .mb-30 { margin-bottom: 30px; }
    .mt-20 { margin-top: 20px; }
    .mt-30 { margin-top: 30px; }
    .p-20 { padding: 20px; }
    .p-25 { padding: 25px; }

    /* Botones de Exportación */
    .export-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .export-buttons .btn {
        white-space: nowrap;
        font-size: 0.85rem;
        padding: 8px 16px;
    }

    /* Estilos para la vista de reportes */
    .card.shadow {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
    }

    .card-header.bg-primary,
    .card-header.bg-info,
    .card-header.bg-warning,
    .card-header.bg-secondary {
        border: none;
        font-weight: 600;
    }

    .form-label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 0.35rem;
        border: 1px solid #d1d3e2;
        padding: 0.375rem 0.75rem;
        font-size: 0.9rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: #bac8f3;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .btn-block {
        display: block;
        width: 100%;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1.1rem;
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.25rem;
    }
    
    .btn-outline-primary {
        color: white;
        border: 1px solid #3b82f6;
        background-color: #3b82f6;
    }
    
    .btn-outline-primary:hover {
        color: white;
        background-color: #2563eb;
        border-color: #2563eb;
    }
    
    .btn-outline-warning {
        color: white;
        border: 1px solid #f59e0b;
        background-color: #f59e0b;
    }
    
    .btn-outline-warning:hover {
        color: white;
        background-color: #d97706;
        border-color: #d97706;
    }
    
    .btn-outline-success {
        color: white;
        border: 1px solid #10b981;
        background-color: #10b981;
    }
    
    .btn-outline-success:hover {
        color: white;
        background-color: #059669;
        border-color: #059669;
    }
    
    .btn-outline-danger {
        color: white;
        border: 1px solid #ef4444;
        background-color: #ef4444;
    }
    
    .btn-outline-danger:hover {
        color: white;
        background-color: #dc2626;
        border-color: #dc2626;
    }
    
    .btn-outline-secondary {
        color: white;
        border: 1px solid #6b7280;
        background-color: #6b7280;
    }
    
    .btn-outline-secondary:hover {
        color: white;
        background-color: #4b5563;
        border-color: #4b5563;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }

    .alert-info ul {
        margin-bottom: 0;
        padding-left: 1.2rem;
    }

    .alert-info li {
        margin-bottom: 0.25rem;
    }

    /* Responsive para reportes */
    @media (max-width: 768px) {
        .export-buttons {
            flex-direction: column;
            width: 100%;
            margin-top: 15px;
        }
        
        .export-buttons .btn {
            width: 100%;
        }
        
        .card-body .row {
            margin: 0;
        }
        
        .col-md-3, .col-md-6, .col-md-9 {
            padding: 0 15px;
            margin-bottom: 15px;
        }
    }

    /* Estilos para barras de progreso */
    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        transition: width 0.3s ease;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        transition: width 0.3s ease;
    }

    /* Estilos para tarjetas de estadísticas con gradientes */
    .stat-card.gradient-green {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .stat-card.gradient-blue {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }

    .stat-card.gradient-orange {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .stat-card.gradient-purple {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
    }

    /* Estilos para alertas informativas */
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 8px;
    }

    .alert-info {
        color: #1e40af;
        background-color: #dbeafe;
        border-color: #93c5fd;
    }

    .alert-success {
        color: #065f46;
        background-color: #d1fae5;
        border-color: #6ee7b7;
    }

    /* Estilos para botones de acción */
    .btn {
        display: inline-block;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background-color: #2563eb;
        transform: translateY(-1px);
    }

    .btn-success {
        background-color: #10b981;
        color: white;
    }

    .btn-success:hover {
        background-color: #059669;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background-color: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #4b5563;
        transform: translateY(-1px);
    }

    /* Estilos para formularios */
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    /* Responsive adjustments adicionales */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .export-buttons {
            flex-direction: column;
        }
        
        .export-buttons .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
