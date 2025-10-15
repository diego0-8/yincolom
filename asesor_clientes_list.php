<?php
// Archivo: views/asesor_clientes_list.php
// Vista rediseñada para que el asesor vea la lista de sus clientes asignados
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php require_once 'shared_styles.php'; ?>
    <style>
        .clientes-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f8fafc;
            min-height: 100vh;
        }
        
        .clientes-header {
            text-align: center;
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .clientes-header h2 {
            color: #1f2937;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .clientes-header p {
            color: #6b7280;
            font-size: 1.1rem;
            margin: 0;
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
        
        /* Header con posición relativa para la campana */
        .clientes-header {
            text-align: center;
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            position: relative;
        }
        
        .search-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
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
        
        .filtros-rapidos {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .tab-button {
            padding: 12px 24px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #6b7280;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
            min-width: 160px;
        }
        
        .tab-button:hover {
            border-color: #3b82f6;
            color: #3b82f6;
            transform: translateY(-1px);
        }
        
        .tab-button.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .tab-content {
            display: none;
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .tab-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tab-count {
            background: #3b82f6;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .clientes-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .cliente-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .cliente-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-color: #3b82f6;
        }
        
        .cliente-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .cliente-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: bold;
        }
        
        .cliente-info h3 {
            margin: 0 0 5px 0;
            color: #1f2937;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .cliente-meta {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .cliente-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            color: #1f2937;
            font-weight: 500;
        }
        
        .cliente-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
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
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .pagination a, .pagination span {
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #374151;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            min-width: 40px;
            text-align: center;
        }
        
        .pagination a:hover {
            background: #f1f5f9;
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .pagination .current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .pagination .disabled {
            color: #9ca3af;
            cursor: not-allowed;
            background: #f9fafb;
        }
        
        .no-clientes {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        .no-clientes i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            color: #d1d5db;
        }
        
        .estado-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .estado-badge.nuevo {
            background: #dcfce7;
            color: #166534;
        }
        
        .estado-badge.gestionado {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .estado-badge.venta {
            background: #fef3c7;
            color: #92400e;
        }
        
        .estado-badge.seguimiento {
            background: #fee2e2;
            color: #dc2626;
        }
        
        /* ESTILOS PARA FILTROS DE CLIENTES GESTIONADOS */
        .filtros-gestionados {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .filtros-header h4 {
            margin: 0 0 8px 0;
            color: #1e293b;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .filtros-header p {
            margin: 0 0 20px 0;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .filtros-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .filtro-btn {
            display: inline-block;
            padding: 8px 16px;
            background: white;
            color: #475569;
            text-decoration: none;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .filtro-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #334155;
            transform: translateY(-1px);
        }
        
        .filtro-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        
        .filtro-activo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .filtro-label {
            color: #1e40af;
            font-weight: 500;
        }
        
        .filtro-valor {
            background: #3b82f6;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .limpiar-filtro {
            margin-left: auto;
            padding: 4px 12px;
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        
        .limpiar-filtro:hover {
            background: #dc2626;
        }
        
        /* ESTILOS PARA RESULTADOS DE GESTIÓN */
        .resultado-gestion {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }
        
        .resultado-gestion.volver-llamar {
            background: #fef3c7;
            color: #92400e;
        }
        
        .resultado-gestion.interesado {
            background: #dcfce7;
            color: #166534;
        }
        
        .resultado-gestion.venta {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .resultado-gestion.rechazo {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .resultado-gestion.contacto-no-efectivo {
            background: #f3f4f6;
            color: #374151;
        }
        
        .resultado-gestion.otro {
            background: #f3e8ff;
            color: #7c3aed;
        }
        
        /* Responsive para filtros */
        @media (max-width: 768px) {
            .filtros-buttons {
                justify-content: center;
            }
            
            .filtro-btn {
                font-size: 0.8rem;
                padding: 6px 12px;
            }
            
            .filtro-activo {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .limpiar-filtro {
                margin-left: 0;
                align-self: flex-end;
            }
        }
        
        .search-results-info {
            background: #eff6ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .search-results-info i {
            color: #0369a1;
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .clientes-container {
                padding: 15px;
            }
            
            .filtros-rapidos {
                flex-direction: column;
                align-items: center;
            }
            
            .tab-button {
                width: 100%;
                max-width: 300px;
            }
            
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-input-group {
                min-width: auto;
            }
            
            .cliente-details {
                grid-template-columns: 1fr;
            }
            
            .cliente-actions {
                flex-direction: column;
            }
            
            .pagination {
                gap: 5px;
            }
            
            .pagination a, .pagination span {
                padding: 8px 12px;
                min-width: 35px;
            }
        }
        
        /* Estilos para el Modal de Historial */
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
            max-width: 800px;
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
        
        /* Estilos para el Historial */
        .historial-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .historial-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .historial-item:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .historial-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .historial-fecha {
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }
        
        .historial-tipo {
            background: #3b82f6;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .historial-resultado {
            margin-bottom: 15px;
            font-weight: 600;
            color: #059669;
            font-size: 1rem;
        }
        
        .historial-comentarios {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 10px;
        }
        
        .historial-venta {
            background: #dcfce7;
            color: #166534;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
            margin-right: 10px;
        }
        
        .historial-duracion {
            background: #fef3c7;
            color: #92400e;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .historial-proxima-fecha {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px 15px;
            margin-top: 10px;
            border-left: 4px solid #ef4444;
        }
        
        .historial-proxima-fecha strong {
            color: #dc2626;
            font-size: 0.9rem;
        }
        
        .proxima-fecha-texto {
            color: #dc2626;
            font-weight: 600;
            font-size: 1rem;
            display: block;
            margin-top: 5px;
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
        
        .error {
            text-align: center;
            padding: 40px;
            color: #dc2626;
            font-size: 1.1rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-size: 1.1rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        
        /* Estilos para las Llamadas Pendientes */
        .llamadas-pendientes-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .llamada-pendiente-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .llamada-pendiente-item:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .llamada-header {
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
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
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
            
            .historial-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .llamada-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .fecha-programada {
                align-self: flex-start;
            }
        }
        
        /* Botón de Teléfono Flotante */
        .telefono-fab {
            position: fixed;
            bottom: 30px;
            left: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .telefono-fab:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }
        
        .telefono-fab:active {
            transform: scale(0.95);
        }
        
        .no-tareas-message {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            margin: 20px 0;
            border: 2px dashed #dee2e6;
        }
        
        .no-tareas-message i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .no-tareas-message h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .no-tareas-message p {
            color: #6c757d;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <?php 
    require_once 'shared_navbar.php';
    echo getNavbar('Mis Clientes', $_SESSION['user_role'] ?? '');
    ?>
    
    <!-- Sistema Click to Call solo para números de teléfono -->
    <style>
        .numero-telefono {
            color: #667eea;
            cursor: pointer;
            text-decoration: underline;
            transition: all 0.3s ease;
            padding: 2px 4px;
            border-radius: 4px;
            display: inline-block;
        }
        .numero-telefono:hover {
            background-color: #667eea;
            color: white;
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
    </style>
    
    <div class="clientes-container">
        <!-- Header Principal -->
        <div class="clientes-header">
            <h2>👥 Mis Clientes Asignados</h2>
            <p>Gestiona tu portafolio de clientes de manera organizada</p>
            
            <?php if (empty($clientesAsignados)): ?>
                <div class="no-tareas-message">
                    <i class="fas fa-clipboard-check"></i>
                    <h3>No tienes tareas pendientes</h3>
                    <p>No se te han asignado tareas específicas en este momento</p>
                    <a href="index.php?action=gestionar_clientes" class="btn btn-primary">
                        <i class="fas fa-search"></i> Gestionar Clientes
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Campana de Notificaciones -->
            <div class="notification-bell <?php echo ($datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0) > 0 ? '' : 'no-notifications'; ?>" 
                 onclick="mostrarLlamadasPendientes()" 
                 title="<?php echo ($datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0) > 0 ? 'Tienes llamadas pendientes para hoy' : 'No hay llamadas pendientes'; ?>">
                🔔
                <?php if (($datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0) > 0): ?>
                    <div class="notification-badge"><?php echo $datos_dashboard['total_llamadas_pendientes_hoy'] ?? 0; ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sección de Búsqueda -->
        <div class="search-section">
            <form method="GET" action="index.php" class="search-form">
                <input type="hidden" name="action" value="mis_clientes">
                <div class="search-input-group">
                    <input type="text" 
                           name="buscar" 
                           placeholder="Buscar cliente por cédula..." 
                           value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>"
                           class="search-input">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <button type="submit" class="btn btn-search">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <?php if (!empty($_GET['buscar'])): ?>
                    <a href="index.php?action=mis_clientes" class="btn btn-clear">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                <?php endif; ?>
            </form>
            
            <!-- Filtros Rápidos (Pestañas) -->
            <div class="filtros-rapidos">
                <button class="tab-button <?php echo (!isset($_GET['filter']) || $_GET['filter'] === 'todos') ? 'active' : ''; ?>" 
                        onclick="cambiarTab('todos')">
                    📋 Todos
                    <span class="tab-count"><?php echo count($clientesAsignados); ?></span>
                </button>
                <button class="tab-button <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'pendientes') ? 'active' : ''; ?>" 
                        onclick="cambiarTab('pendientes')">
                    ⏰ Pendientes
                    <span class="tab-count"><?php echo $clientesPendientes; ?></span>
                </button>
                <button class="tab-button <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'gestionados') ? 'active' : ''; ?>" 
                        onclick="cambiarTab('gestionados')">
                    📞 Gestionados
                    <span class="tab-count"><?php echo $clientesConGestiones; ?></span>
                </button>
                <button class="tab-button <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'ventas') ? 'active' : ''; ?>" 
                        onclick="cambiarTab('ventas')">
                    💰 Con Ventas
                    <span class="tab-count"><?php echo $clientesGestionados; ?></span>
                </button>
                <button class="tab-button <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'seguimiento') ? 'active' : ''; ?>" 
                        onclick="cambiarTab('seguimiento')">
                    📅 Seguimiento
                    <span class="tab-count"><?php echo count($datos_dashboard['llamadas_pendientes'] ?? []); ?></span>
                </button>
            </div>
        </div>
        
        <!-- Mensaje de búsqueda -->
        <?php if (!empty($_GET['buscar'])): ?>
            <div class="search-results-info">
                <i class="fas fa-search"></i>
                <strong>Búsqueda activa:</strong> Mostrando clientes con cédula que contenga "<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>"
                <span class="search-results-count">(<?php echo count($clientesAsignados); ?> resultado<?php echo count($clientesAsignados) !== 1 ? 's' : ''; ?>)</span>
            </div>
        <?php endif; ?>
        
        <!-- Pestaña: Todos los Clientes -->
        <div id="tab-todos" class="tab-content <?php echo (!isset($_GET['filter']) || $_GET['filter'] === 'todos') ? 'active' : ''; ?>">
            <div class="tab-header">
                <div class="tab-title">
                    📋 Todos Mis Clientes
                    <span class="tab-count"><?php echo count($clientesAsignados); ?></span>
                </div>
                <p style="color: #6b7280; margin: 0;">Vista completa de tu portafolio de clientes</p>
            </div>
            
            <?php if (!empty($clientesAsignados)): ?>
                <div class="clientes-grid">
                    <?php foreach ($clientesAsignados as $cliente): ?>
                        <div class="cliente-card">
                            <span class="estado-badge <?php echo ($cliente['total_gestiones'] > 0) ? 'gestionado' : 'nuevo'; ?>">
                                <?php echo ($cliente['total_gestiones'] > 0) ? 'Gestionado' : 'Nuevo'; ?>
                            </span>
                            
                            <div class="cliente-header">
                                <div class="cliente-avatar">
                                    <?php echo strtoupper(substr($cliente['nombre'] ?? 'C', 0, 1)); ?>
                                </div>
                                <div class="cliente-info">
                                    <h3><?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?></h3>
                                    <div class="cliente-meta">
                                        Cédula: <?php echo htmlspecialchars($cliente['cedula'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="cliente-details">
                                <div class="detail-item">
                                    <span class="detail-label">Teléfono</span> <?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>
                                    <span class="detail-value">
                                        
                                </div>
                                <?php if (!empty($cliente['celular2'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Celular</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($cliente['celular2'] ?? ''); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($cliente['ciudad'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Ciudad</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($cliente['ciudad'] ?? ''); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="detail-item">
                                    <span class="detail-label">Estado</span>
                                    <span class="detail-value">
                                        <?php if ($cliente['total_gestiones'] > 0): ?>
                                            <?php echo $cliente['total_gestiones']; ?> gestión(es)
                                        <?php else: ?>
                                            Sin gestionar
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="cliente-actions">
                                <a href="index.php?action=gestionar_cliente&id=<?php echo $cliente['id']; ?>" 
                                   class="btn btn-primary">
                                    📞 Gestionar
                                </a>
                                <button type="button" class="btn btn-secondary" 
                                        onclick="mostrarHistorialCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nombre'] ?? '', ENT_QUOTES); ?>')">
                                    📋 Historial
                                </button>
                                <a href="index.php?action=gestionar_productos&cliente_id=<?php echo $cliente['id']; ?>" 
                                   class="btn btn-success">
                                    📦 Productos
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación para la pestaña "Todos" -->
                <?php if ($total_paginas > 1): ?>
                    <div class="pagination">
                        <?php if ($pagina_actual > 1): ?>
                            <a href="?action=mis_clientes&filter=todos&pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                ← Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <?php if ($i == $pagina_actual): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?action=mis_clientes&filter=todos&pagina=<?php echo $i; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $total_paginas): ?>
                            <a href="?action=mis_clientes&filter=todos&pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                Siguiente →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-clientes">
                    <i class="fas fa-users"></i>
                    <p>📞 No tienes clientes asignados aún.</p>
                    <p>Contacta a tu coordinador para que te asigne clientes.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pestaña: Clientes Pendientes -->
        <div id="tab-pendientes" class="tab-content <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'pendientes') ? 'active' : ''; ?>">
            <div class="tab-header">
                <div class="tab-title">
                    ⏰ Clientes Pendientes
                    <span class="tab-count"><?php echo $clientesPendientes; ?></span>
                </div>
                <p style="color: #6b7280; margin: 0;">Clientes que requieren tu primera gestión</p>
            </div>
            
            <?php 
            $clientesPendientesList = array_filter($clientesAsignados, function($cliente) {
                return $cliente['total_gestiones'] == 0;
            });
            ?>
            
            <?php if (!empty($clientesPendientesList)): ?>
                <div class="clientes-grid">
                    <?php foreach ($clientesPendientesList as $cliente): ?>
                        <div class="cliente-card">
                            <span class="estado-badge nuevo">Nuevo</span>
                            
                            <div class="cliente-header">
                                <div class="cliente-avatar">
                                    <?php echo strtoupper(substr($cliente['nombre'] ?? 'C', 0, 1)); ?>
                                </div>
                                <div class="cliente-info">
                                    <h3><?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?></h3>
                                    <div class="cliente-meta">
                                        Cédula: <?php echo htmlspecialchars($cliente['cedula'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="cliente-details">
                                <div class="detail-item">
                                    <span class="detail-label">Teléfono</span>
                                    <span class="detail-value">
                                        <span class="numero-telefono" onclick="llamarDesdeVentanaAnclada('<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>')" 
                                              style="color: #667eea; cursor: pointer; text-decoration: underline;">
                                            📞 <?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>
                                        </span>
                                    </span>
                                </div>
                                <?php if (!empty($cliente['celular2'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Celular</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($cliente['celular2'] ?? ''); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($cliente['ciudad'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Ciudad</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($cliente['ciudad'] ?? ''); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cliente-actions">
                                <a href="index.php?action=gestionar_cliente&id=<?php echo $cliente['id']; ?>" 
                                   class="btn btn-primary">
                                    📞 Gestionar Cliente
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación para la pestaña "Pendientes" -->
                <?php 
                $total_pendientes = count($clientes_pendientes);
                $paginas_pendientes = ceil($total_pendientes / 10);
                if ($paginas_pendientes > 1): 
                ?>
                    <div class="pagination">
                        <?php if ($pagina_actual > 1): ?>
                            <a href="?action=mis_clientes&filter=pendientes&pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                ← Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $paginas_pendientes; $i++): ?>
                            <?php if ($i == $pagina_actual): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?action=mis_clientes&filter=pendientes&pagina=<?php echo $i; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $paginas_pendientes): ?>
                            <a href="?action=mis_clientes&filter=pendientes&pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                Siguiente →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-clientes">
                    <i class="fas fa-check-circle"></i>
                    <p>🎉 ¡Excelente trabajo! No tienes clientes pendientes.</p>
                    <p>Todos tus clientes han sido contactados.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pestaña: Clientes Gestionados -->
        <div id="tab-gestionados" class="tab-content <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'gestionados') ? 'active' : ''; ?>">
            <div class="tab-header">
                <div class="tab-title">
                    📞 Clientes Gestionados
                    <span class="tab-count"><?php echo $clientesConGestiones; ?></span>
                </div>
                <p style="color: #6b7280; margin: 0;">Clientes que ya han sido contactados</p>
            </div>
            
            <!-- BARRA DE FILTROS PARA CLIENTES GESTIONADOS -->
            <div class="filtros-gestionados">
                <div class="filtros-header">
                    <h4>🔍 Filtrar por Resultado de Gestión</h4>
                    <p>Selecciona el tipo de resultado para filtrar los clientes</p>
                </div>
                
                <div class="filtros-buttons">
                    <a href="?action=mis_clientes&filter=gestionados&filtro_resultado=todos<?php echo !empty($_GET['pagina']) ? '&pagina=' . $_GET['pagina'] : ''; ?>" 
                       class="filtro-btn <?php echo (!isset($_GET['filtro_resultado']) || $_GET['filtro_resultado'] === 'todos') ? 'active' : ''; ?>">
                        📊 Todos
                    </a>
                    
                    <a href="?action=mis_clientes&filter=gestionados&filtro_resultado=volver_llamar<?php echo !empty($_GET['pagina']) ? '&pagina=' . $_GET['pagina'] : ''; ?>" 
                       class="filtro-btn <?php echo (isset($_GET['filtro_resultado']) && $_GET['filtro_resultado'] === 'volver_llamar') ? 'active' : ''; ?>">
                        🔔 Volver a Llamar
                    </a>
                    
                    <a href="?action=mis_clientes&filter=gestionados&filtro_resultado=interesados<?php echo !empty($_GET['pagina']) ? '&pagina=' . $_GET['pagina'] : ''; ?>" 
                       class="filtro-btn <?php echo (isset($_GET['filtro_resultado']) && $_GET['filtro_resultado'] === 'interesados') ? 'active' : ''; ?>">
                        💡 Interesados
                    </a>
                    
                    <a href="?action=mis_clientes&filter=gestionados&filtro_resultado=ventas_positivas<?php echo !empty($_GET['pagina']) ? '&pagina=' . $_GET['pagina'] : ''; ?>" 
                       class="filtro-btn <?php echo (isset($_GET['filtro_resultado']) && $_GET['filtro_resultado'] === 'ventas_positivas') ? 'active' : ''; ?>">
                        💰 Ventas Positivas
                    </a>
                    
                    <a href="?action=mis_clientes&filter=gestionados&filtro_resultado=rechazos<?php echo !empty($_GET['pagina']) ? '&pagina=' . $_GET['pagina'] : ''; ?>" 
                       class="filtro-btn <?php echo (isset($_GET['filtro_resultado']) && $_GET['filtro_resultado'] === 'rechazos') ? 'active' : ''; ?>">
                        ❌ Rechazos
                    </a>
                    
                    <a href="?action=mis_clientes&filter=gestionados&filtro_resultado=contactos_no_efectivos<?php echo !empty($_GET['pagina']) ? '&pagina=' . $_GET['pagina'] : ''; ?>" 
                       class="filtro-btn <?php echo (isset($_GET['filtro_resultado']) && $_GET['filtro_resultado'] === 'contactos_no_efectivos') ? 'active' : ''; ?>">
                        📱 Contactos No Efectivos
                    </a>
                    
                    <a href="?action=mis_clientes&filter=gestionados&filtro_resultado=otros<?php echo !empty($_GET['pagina']) ? '&pagina=' . $_GET['pagina'] : ''; ?>" 
                       class="filtro-btn <?php echo (isset($_GET['filtro_resultado']) && $_GET['filtro_resultado'] === 'otros') ? 'active' : ''; ?>">
                        🔧 Otros
                    </a>
                </div>
                
                <?php if (isset($_GET['filtro_resultado']) && $_GET['filtro_resultado'] !== 'todos'): ?>
                    <div class="filtro-activo">
                        <span class="filtro-label">Filtro activo:</span>
                        <span class="filtro-valor">
                            <?php 
                            $filtroNombres = [
                                'volver_llamar' => 'Volver a Llamar',
                                'interesados' => 'Interesados',
                                'ventas_positivas' => 'Ventas Positivas',
                                'rechazos' => 'Rechazos',
                                'contactos_no_efectivos' => 'Contactos No Efectivos',
                                'otros' => 'Otros'
                            ];
                            echo $filtroNombres[$_GET['filtro_resultado']] ?? $_GET['filtro_resultado'];
                            ?>
                        </span>
                        <a href="?action=mis_clientes&filter=gestionados<?php echo !empty($_GET['pagina']) ? '&pagina=' . $_GET['pagina'] : ''; ?>" class="limpiar-filtro">
                            ✕ Limpiar Filtro
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php 
            $clientesGestionadosList = array_filter($clientesAsignados, function($cliente) {
                return $cliente['total_gestiones'] > 0;
            });
            ?>
            
            <?php if (!empty($clientesGestionadosList)): ?>
                <div class="clientes-grid">
                    <?php foreach ($clientesGestionadosList as $cliente): ?>
                        <div class="cliente-card">
                            <span class="estado-badge gestionado">Gestionado</span>
                            
                            <div class="cliente-header">
                                <div class="cliente-avatar">
                                    <?php echo strtoupper(substr($cliente['nombre'] ?? 'C', 0, 1)); ?>
                                </div>
                                <div class="cliente-info">
                                    <h3><?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?></h3>
                                    <div class="cliente-meta">
                                        Cédula: <?php echo htmlspecialchars($cliente['cedula'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="cliente-details">
                                <div class="detail-item">
                                    <span class="detail-label">Teléfono</span>
                                    <span class="detail-value">
                                        <span class="numero-telefono" onclick="llamarDesdeVentanaAnclada('<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>')" 
                                              style="color: #667eea; cursor: pointer; text-decoration: underline;">
                                            📞 <?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>
                                        </span>
                                    </span>
                                </div>
                                <?php if (!empty($cliente['celular2'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Celular</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($cliente['celular2'] ?? ''); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="detail-item">
                                    <span class="detail-label">Gestiones</span>
                                    <span class="detail-value"><?php echo $cliente['total_gestiones']; ?> contacto(s)</span>
                                </div>
                                <?php if (!empty($cliente['ultimo_resultado'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Último Resultado</span>
                                    <span class="detail-value resultado-gestion <?php echo $this->getClaseResultado($cliente['ultimo_resultado']); ?>">
                                        <?php echo htmlspecialchars($cliente['ultimo_resultado'] ?? ''); ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($cliente['ultima_gestion'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Última Gestión</span>
                                    <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($cliente['ultima_gestion'])); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cliente-actions">
                                <a href="index.php?action=gestionar_cliente&id=<?php echo $cliente['id']; ?>" 
                                   class="btn btn-primary">
                                    📞 Gestionar Nuevamente
                                </a>
                                <button type="button" class="btn btn-secondary" 
                                        onclick="mostrarHistorialCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nombre'] ?? '', ENT_QUOTES); ?>')">
                                    📋 Ver Historial
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación para la pestaña "Gestionados" -->
                <?php 
                $total_gestionados = count($clientesAsignados);
                $paginas_gestionados = ceil($total_gestionados / 10);
                if ($paginas_gestionados > 1): 
                ?>
                    <div class="pagination">
                        <?php if ($pagina_actual > 1): ?>
                            <a href="?action=mis_clientes&filter=gestionados&pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($_GET['filtro_resultado']) ? '&filtro_resultado=' . $_GET['filtro_resultado'] : ''; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                ← Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $paginas_gestionados; $i++): ?>
                            <?php if ($i == $pagina_actual): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?action=mis_clientes&filter=gestionados&pagina=<?php echo $i; ?><?php echo !empty($_GET['filtro_resultado']) ? '&filtro_resultado=' . $_GET['filtro_resultado'] : ''; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $paginas_gestionados): ?>
                            <a href="?action=mis_clientes&filter=gestionados&pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($_GET['filtro_resultado']) ? '&filtro_resultado=' . $_GET['filtro_resultado'] : ''; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                Siguiente →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-clientes">
                    <i class="fas fa-phone"></i>
                    <p>📞 No hay clientes gestionados aún.</p>
                    <p>Comienza contactando a tus clientes pendientes.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pestaña: Clientes con Ventas -->
        <div id="tab-ventas" class="tab-content <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'ventas') ? 'active' : ''; ?>">
            <div class="tab-header">
                <div class="tab-title">
                    💰 Clientes con Ventas
                    <span class="tab-count"><?php echo $clientesGestionados; ?></span>
                </div>
                <p style="color: #6b7280; margin: 0;">Clientes que han generado ventas</p>
            </div>
            
            <?php 
            $clientesConVentasList = array_filter($clientesAsignados, function($cliente) {
                return !empty($cliente['ultimo_resultado']) && 
                       in_array($cliente['ultimo_resultado'], ['Venta Exitosa', 'Venta en Frío', 'Venta con Seguimiento', 'Venta Cruzada']);
            });
            ?>
            
            <?php if (!empty($clientesConVentasList)): ?>
                <div class="clientes-grid">
                    <?php foreach ($clientesConVentasList as $cliente): ?>
                        <div class="cliente-card">
                            <span class="estado-badge venta">Venta</span>
                            
                            <div class="cliente-header">
                                <div class="cliente-avatar">
                                    <?php echo strtoupper(substr($cliente['nombre'] ?? 'C', 0, 1)); ?>
                                </div>
                                <div class="cliente-info">
                                    <h3><?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?></h3>
                                    <div class="cliente-meta">
                                        Cédula: <?php echo htmlspecialchars($cliente['cedula'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="cliente-details">
                                <div class="detail-item">
                                    <span class="detail-label">Teléfono</span>
                                    <span class="detail-value">
                                        <span class="numero-telefono" onclick="llamarDesdeVentanaAnclada('<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>')" 
                                              style="color: #667eea; cursor: pointer; text-decoration: underline;">
                                            📞 <?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Resultado</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($cliente['ultimo_resultado'] ?? ''); ?></span>
                                </div>
                                <?php if (!empty($cliente['monto_venta'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Monto</span>
                                    <span class="detail-value">$<?php echo number_format($cliente['monto_venta'], 0, ',', '.'); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($cliente['fecha_venta'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Fecha Venta</span>
                                    <span class="detail-value"><?php echo date('d/m/Y', strtotime($cliente['fecha_venta'])); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cliente-actions">
                                                                 <button type="button" class="btn btn-success" 
                                         onclick="mostrarHistorialCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nombre'] ?? '', ENT_QUOTES); ?>')">
                                     📋 Ver Detalles
                                 </button>
                                <a href="index.php?action=gestionar_cliente&id=<?php echo $cliente['id']; ?>" 
                                   class="btn btn-primary">
                                    📞 Seguir Gestionando
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación para la pestaña "Con Ventas" -->
                <?php 
                $total_ventas = count($clientes_con_ventas);
                $paginas_ventas = ceil($total_ventas / 10);
                if ($paginas_ventas > 1): 
                ?>
                    <div class="pagination">
                        <?php if ($pagina_actual > 1): ?>
                            <a href="?action=mis_clientes&filter=ventas&pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                ← Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $paginas_ventas; $i++): ?>
                            <?php if ($i == $pagina_actual): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?action=mis_clientes&filter=ventas&pagina=<?php echo $i; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $paginas_ventas): ?>
                            <a href="?action=mis_clientes&filter=ventas&pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                Siguiente →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-clientes">
                    <i class="fas fa-chart-line"></i>
                    <p>💰 No hay clientes con ventas aún.</p>
                    <p>Continúa gestionando para lograr tus primeras ventas.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pestaña: Clientes con Seguimiento -->
        <div id="tab-seguimiento" class="tab-content <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'seguimiento') ? 'active' : ''; ?>">
            <div class="tab-header">
                <div class="tab-title">
                    📅 Clientes con Seguimiento
                    <span class="tab-count"><?php echo count($datos_dashboard['llamadas_pendientes'] ?? []); ?></span>
                </div>
                <p style="color: #6b7280; margin: 0;">Clientes que requieren seguimiento y llamadas pendientes</p>
            </div>
            
            <?php if (!empty($clientesAsignados)): ?>
                <div class="clientes-grid">
                    <?php foreach ($clientesAsignados as $cliente): ?>
                        <div class="cliente-card">
                            <span class="estado-badge seguimiento">Seguimiento</span>
                            
                            <div class="cliente-header">
                                <div class="cliente-avatar">
                                    <?php echo strtoupper(substr($cliente['nombre'] ?? 'C', 0, 1)); ?>
                                </div>
                                <div class="cliente-info">
                                    <h3><?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?></h3>
                                    <div class="cliente-meta">
                                        Cédula: <?php echo htmlspecialchars($cliente['cedula'] ?? ''); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="cliente-details">
                                <div class="detail-item">
                                    <span class="detail-label">Teléfono</span>
                                    <span class="detail-value">
                                        <span class="numero-telefono" onclick="llamarDesdeVentanaAnclada('<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>')" 
                                              style="color: #667eea; cursor: pointer; text-decoration: underline;">
                                            📞 <?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>
                                        </span>
                                    </span>
                                </div>
                                <?php if (!empty($cliente['proxima_fecha'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Próxima Llamada</span>
                                    <span class="detail-value" style="color: #ef4444; font-weight: bold;">
                                        <?php echo date('d/m/Y H:i', strtotime($cliente['proxima_fecha'])); ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($cliente['comentarios_seguimiento'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Comentarios</span>
                                    <span class="detail-value"><?php echo htmlspecialchars(substr($cliente['comentarios_seguimiento'], 0, 100)) . (strlen($cliente['comentarios_seguimiento']) > 100 ? '...' : ''); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cliente-actions">
                                <a href="index.php?action=gestionar_cliente&id=<?php echo $cliente['id']; ?>" 
                                   class="btn btn-primary">
                                    📞 Gestionar
                                </a>
                                <button type="button" class="btn btn-success" 
                                        onclick="mostrarHistorialCliente(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nombre'] ?? '', ENT_QUOTES); ?>')">
                                    📋 Ver Historial
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación para la pestaña "Seguimiento" -->
                <?php 
                $total_seguimiento = count($datos_dashboard['llamadas_pendientes'] ?? []);
                $paginas_seguimiento = ceil($total_seguimiento / 10);
                if ($paginas_seguimiento > 1): 
                ?>
                    <div class="pagination">
                        <?php if ($pagina_actual > 1): ?>
                            <a href="?action=mis_clientes&filter=seguimiento&pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                ← Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $paginas_seguimiento; $i++): ?>
                            <?php if ($i == $pagina_actual): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?action=mis_clientes&filter=seguimiento&pagina=<?php echo $i; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($pagina_actual < $paginas_seguimiento): ?>
                            <a href="?action=mis_clientes&filter=seguimiento&pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>">
                                Siguiente →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-clientes">
                    <i class="fas fa-calendar-check"></i>
                    <p>📅 No hay clientes con seguimiento pendiente.</p>
                    <p>¡Excelente! Has gestionado todos tus clientes.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal de Historial del Cliente -->
    <div id="modalHistorial" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📋 Historial del Cliente: <span id="nombreClienteHistorial"></span></h3>
                <button type="button" class="modal-close" onclick="cerrarModalHistorial()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="contenidoHistorial">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        Cargando historial...
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Llamadas Pendientes -->
    <div id="modalLlamadasPendientes" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔔 Llamadas Pendientes para Hoy</h3>
                <button type="button" class="modal-close" onclick="cerrarModalLlamadasPendientes()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="contenidoLlamadasPendientes">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        Cargando llamadas pendientes...
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function cambiarTab(tab) {
            // Ocultar todas las pestañas
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remover clase active de todos los botones
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Mostrar la pestaña seleccionada
            document.getElementById('tab-' + tab).classList.add('active');
            
            // Activar el botón correspondiente
            event.target.classList.add('active');
            
            // Redirigir a la pestaña seleccionada
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('filter', tab);
            urlParams.set('action', 'mis_clientes');
            window.location.href = 'index.php?' + urlParams.toString();
        }
        
        // Marcar la pestaña activa en la URL actual
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const filter = urlParams.get('filter') || 'todos';
            
            // Asegurar que la pestaña correcta esté activa
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById('tab-' + filter).classList.add('active');
            
            // Asegurar que el botón correcto esté activo
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
                // Encontrar el botón correcto por el texto
                const buttonText = button.textContent.toLowerCase();
                if (buttonText.includes(filter.toLowerCase())) {
                    button.classList.add('active');
                }
            });
        });
        
        // Función para mostrar llamadas pendientes
        function mostrarLlamadasPendientes() {
            const llamadasPendientes = <?php echo json_encode($datos_dashboard['llamadas_pendientes'] ?? []); ?>;
            
            if (llamadasPendientes.length === 0) {
                alert('No tienes llamadas pendientes para hoy.');
                return;
            }
            
            // Mostrar el modal
            document.getElementById('modalLlamadasPendientes').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Cargar contenido del modal
            mostrarLlamadasPendientesEnModal(llamadasPendientes);
        }
        
        function cerrarModalLlamadasPendientes() {
            document.getElementById('modalLlamadasPendientes').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function mostrarLlamadasPendientesEnModal(llamadasPendientes) {
            const contenidoModal = document.getElementById('contenidoLlamadasPendientes');
            
            let html = '<div class="llamadas-pendientes-container">';
            
            llamadasPendientes.forEach((llamada, index) => {
                const fecha = new Date(llamada.proxima_fecha || new Date()).toLocaleString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                html += `
                    <div class="llamada-pendiente-item">
                        <div class="llamada-header">
                            <div class="cliente-info">
                                <h4>👤 ${llamada.cliente_nombre || 'Cliente'}</h4>
                                <div class="cliente-meta">
                                    📱 ${llamada.telefono || llamada.celular2 || 'Sin teléfono'}
                                </div>
                            </div>
                            <div class="fecha-programada">
                                ⏰ ${fecha}
                            </div>
                        </div>
                        <div class="tipificacion-actual">
                            🏷️ <strong>Tipificación:</strong> ${llamada.resultado || 'N/A'}
                        </div>
                        <div class="comentarios">
                            💬 <strong>Comentarios:</strong><br>
                            ${llamada.comentarios || 'Sin comentarios específicos'}
                        </div>
                        <div class="acciones">
                            <a href="index.php?action=gestionar_cliente&id=${llamada.cliente_id}" 
                               class="btn btn-primary btn-sm">
                                📞 Gestionar Cliente
                            </a>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            contenidoModal.innerHTML = html;
        }
        
        // Cerrar modal al hacer clic fuera de él
        document.getElementById('modalLlamadasPendientes').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalLlamadasPendientes();
            }
        });
        
        // Funciones para el modal de historial
        function mostrarHistorialCliente(clienteId, nombreCliente) {
            document.getElementById('nombreClienteHistorial').textContent = nombreCliente;
            document.getElementById('modalHistorial').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Cargar historial del cliente
            cargarHistorialCliente(clienteId);
        }
        
        function cerrarModalHistorial() {
            document.getElementById('modalHistorial').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function cargarHistorialCliente(clienteId) {
            const contenidoHistorial = document.getElementById('contenidoHistorial');
            
            // Mostrar loading
            contenidoHistorial.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando historial...</div>';
            
            // Hacer petición AJAX para obtener el historial
            fetch(`index.php?action=obtener_historial_cliente&id=${clienteId}`, {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarHistorialEnModal(data.historial);
                } else {
                    contenidoHistorial.innerHTML = '<div class="error">❌ Error al cargar el historial: ' + (data.message || 'Error desconocido') + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                contenidoHistorial.innerHTML = '<div class="error">❌ Error al cargar el historial</div>';
            });
        }
        
        function mostrarHistorialEnModal(historial) {
            const contenidoHistorial = document.getElementById('contenidoHistorial');
            
            if (!historial || historial.length === 0) {
                contenidoHistorial.innerHTML = '<div class="no-data">📝 No hay historial disponible para este cliente.</div>';
                return;
            }
            
            let html = '<div class="historial-container">';
            
            historial.forEach((gestion, index) => {
                // Verificar si es "VOLVER A LLAMAR" y tiene próxima fecha
                const esVolverLlamar = gestion.resultado && 
                    (gestion.resultado.includes('VOLVER A LLAMAR') || 
                     gestion.resultado.includes('Agenda Llamada de Seguimiento'));
                
                const tieneProximaFecha = gestion.proxima_fecha && gestion.proxima_fecha !== 'null';
                
                html += `
                    <div class="historial-item">
                        <div class="historial-header">
                            <div class="historial-fecha">
                                📅 ${new Date(gestion.fecha_gestion).toLocaleDateString('es-ES', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}
                            </div>
                            <div class="historial-tipo">
                                📞 ${gestion.tipo_gestion || 'N/A'}
                            </div>
                        </div>
                        <div class="historial-resultado">
                            🏷️ ${gestion.resultado || 'N/A'}
                        </div>
                        <div class="historial-comentarios">
                            💬 <strong>Observaciones:</strong><br>
                            ${gestion.comentarios || 'Sin observaciones'}
                        </div>
                        ${gestion.monto_venta ? `<div class="historial-venta">💰 Venta: $${parseInt(gestion.monto_venta).toLocaleString('es-CO')}</div>` : ''}
                        ${gestion.duracion_llamada ? `<div class="historial-duracion">⏱️ Duración: ${gestion.duracion_llamada} min</div>` : ''}
                        ${esVolverLlamar && tieneProximaFecha ? `
                            <div class="historial-proxima-fecha">
                                🔔 <strong>Próxima Llamada:</strong><br>
                                <span class="proxima-fecha-texto">
                                    ${new Date(gestion.proxima_fecha).toLocaleDateString('es-ES', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })}
                                </span>
                            </div>
                        ` : ''}
                    </div>
                `;
            });
            
            html += '</div>';
            contenidoHistorial.innerHTML = html;
        }
        
        // Cerrar modal al hacer clic fuera de él
        document.getElementById('modalHistorial').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalHistorial();
            }
        });
        
        // Función para obtener la clase CSS del resultado de gestión
        function getClaseResultado(resultado) {
            if (!resultado) return 'otro';
            
            const resultadoLower = resultado.toLowerCase();
            
            if (resultadoLower.includes('volver a llamar') || resultadoLower.includes('volver_llamar')) {
                return 'volver-llamar';
            } else if (resultadoLower.includes('interesado') || resultadoLower.includes('interes')) {
                return 'interesado';
            } else if (resultadoLower.includes('venta') && (resultadoLower.includes('exitosa') || resultadoLower.includes('frio') || resultadoLower.includes('seguimiento') || resultadoLower.includes('cruzada'))) {
                return 'venta';
            } else if (resultadoLower.includes('rechazo') || resultadoLower.includes('no interesa')) {
                return 'rechazo';
            } else if (resultadoLower.includes('no contesta') || resultadoLower.includes('no efectivo') || resultadoLower.includes('equivocado')) {
                return 'contacto-no-efectivo';
            } else {
                return 'otro';
            }
        }
    </script>
    
    <script>
        // Función para Click to Call (sistema original)
        function llamarDesdeVentanaAnclada(numero) {
            if (confirm(`¿Desea llamar al número ${numero}?`)) {
                // Obtener datos del usuario
                fetch('index.php?action=get_telefono_data', {
                    method: 'GET',
                    credentials: 'same-origin'
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.tiene_telefono) {
                            const urlLlamada = `https://estaqueue.udpsa.com/phone/phone.php?PBXCLOUD=onix.udpsa.com&extension=${data.extension}&claveWEBRTC=${data.clave}&autoanswer=1&numero=${numero}`;
                            window.open(urlLlamada, 'telefono', 'width=400,height=600,scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no');
                        } else {
                            alert('No tiene configurada la extensión telefónica. Contacte al administrador.');
                        }
                    })
                    .catch(error => {
                        console.error('Error obteniendo datos de teléfono:', error);
                        alert('Error al obtener configuración de teléfono');
                    });
            }
        }
        
    </script>
</body>
</html>
