<?php
// Archivo: views/gestionar_cliente.php
// Sistema de tipificaciones inteligente para asesores
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php require_once 'shared_styles.php'; ?>
    <style>
        .gestion-container {
            background: #f8fafc;
            min-height: 100vh;
            padding: 20px;
        }
        
        .cliente-info-card {
    background: white;
    border-radius: 12px;
    padding: 0px 33px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
}
        
        .cliente-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .cliente-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        
        .cliente-details h2 {
            margin: 0;
            color: #1f2937;
            font-size: 1.5rem;
        }
        
        .cliente-meta {
            color: #6b7280;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
        }
        
        .cliente-meta-item {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }
        
        /* Estilos para el desplegable de teléfonos */
        .telefono-dropdown select {
            border: none;
            background: transparent;
            color: #6b7280;
            font-size: 0.9rem;
            margin-left: 5px;
            cursor: pointer;
            outline: none;
            font-weight: normal;
            padding: 2px 5px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .telefono-dropdown select:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
        
        .telefono-dropdown select:focus {
            background: rgba(102, 126, 234, 0.15);
            color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        .tipificacion-card {
            width: 100% !important;
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .tipificacion-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tipificacion-principal {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        /* Nuevos estilos para el diseño de dos columnas */
        .tipificacion-columnas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .columna-tipificaciones {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e2e8f0;
        }
        
        .columna-observaciones {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e2e8f0;
        }
        
        /* Estilos para campos específicos de acuerdo de pago */
        .campos-especificos {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .campos-especificos .form-group {
            margin-bottom: 15px;
        }
        
        .campos-especificos .form-label {
            font-weight: 600;
            color: #856404;
            margin-bottom: 8px;
            display: block;
        }
        
        .campos-especificos .form-control {
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .campos-especificos .form-control:focus {
            border-color: #ff8c00;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
            outline: none;
        }
        
        .campos-especificos h4 {
            color: #856404;
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tipificaciones-especificas {
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .tipificacion-option {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .tipificacion-option:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.15);
        }
        
        .tipificacion-option.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .tipificacion-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .tipificacion-icon.contactado {
            background: #dcfce7;
            color: #166534;
        }
        
        .tipificacion-icon.no-contactado {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .tipificacion-text {
            flex: 1;
        }
        
        .tipificacion-text h3 {
            margin: 0 0 5px 0;
            color: #1f2937;
            font-size: 1.1rem;
        }
        
        .tipificacion-text p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .sub-tipificaciones {
            display: none;
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .sub-option input[type="radio"]:checked + label {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .sub-option input[type="radio"]:checked + label::before {
            content: "✓ ";
            font-weight: bold;
        }
        
        .sub-tipificaciones.show {
            display: block;
        }
        
        .sub-tipificaciones h4 {
            color: #1f2937;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .sub-options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .sub-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .sub-option:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .sub-option input[type="radio"] {
            margin: 0;
        }
        
        .sub-option label {
            cursor: pointer;
            margin: 0;
            flex: 1;
            color: #374151;
            font-weight: 500;
        }
        
        .acciones-especificas {
            display: none;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .acciones-especificas.show {
            display: block;
        }
        
        .acciones-especificas h4 {
            color: #0369a1;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .form-section {
            width: 100% !important;
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .form-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }
        
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
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-prefix {
            position: absolute;
            left: 12px;
            color: #6b7280;
            font-weight: 500;
            z-index: 10;
        }
        
        .input-group .form-input {
            padding-left: 30px;
        }
        
        .form-help {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }
        
        .form-select, .form-input, .form-textarea {
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .form-select:focus, .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-container {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        /* Estilos para Canales Autorizados */
        .canales-autorizados-section {
            margin-top: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .canales-title {
            color: #495057;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .canales-checkboxes {
            display: flex;
            justify-content: center;
        }
        
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            max-width: 800px;
            width: 100%;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .checkbox-label:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,123,255,0.15);
        }
        
        .canal-checkbox {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: #007bff;
            cursor: pointer;
        }
        
        .checkbox-text {
            color: #495057;
            user-select: none;
        }
        
        .checkbox-label:has(.canal-checkbox:checked) {
            border-color: #007bff;
            background-color: #e7f3ff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.2);
        }
        
        .checkbox-label:has(.canal-checkbox:checked) .checkbox-text {
            color: #0056b3;
            font-weight: 600;
        }
        
        /* Estilos para el modal de detalles */
        .modal-detalles {
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .detalles-gestion {
            display: grid;
            gap: 20px;
        }
        
        .detalle-seccion {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        
        .detalle-seccion h4 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detalle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        
        .detalle-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .detalle-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .detalle-valor {
            color: #495057;
            font-size: 1rem;
        }
        
        .canales-seleccionados {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .canal-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 4px rgba(40,167,69,0.2);
        }
        
        .comentarios-detalle {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            white-space: pre-wrap;
            line-height: 1.6;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
        
        .btn-info {
            background: #06b6d4;
            color: white;
        }
        
        .btn-info:hover {
            background: #0891b2;
            transform: translateY(-1px);
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-group .btn {
            flex: 1;
            min-width: 200px;
        }
        
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-1px);
        }
        
        .btn-lg {
            padding: 15px 30px;
            font-size: 1.1rem;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .alert-info {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            color: #1e40af;
        }
        
        /* Estilos específicos para el mensaje del aplicativo de agentes */
        .alert-info .btn-outline-info {
            border-color: #17a2b8;
            color: #17a2b8;
            transition: all 0.3s ease;
        }
        
        .alert-info .btn-outline-info:hover {
            background-color: #17a2b8;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
        }
        
        .alert-info .btn-outline-info:focus {
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        }
        
        .info-adicional {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .info-adicional h4 {
            color: #92400e;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .info-adicional.show {
            display: block;
        }
        
        .info-adicional.hide {
            display: none;
        }
        
        
        /* Estilos para el historial de gestiones - Diseño empresarial */
        .historial-section {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        
        .historial-title {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 1.4em;
            font-weight: 600;
            border-bottom: 3px solid #3498db;
            padding-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .historial-item {
            background: white;
            padding: 0;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #e1e8ed;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .historial-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transform: translateY(-1px);
        }
        
        .historial-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .historial-fecha {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95em;
        }
        
        .historial-actions {
            display: flex;
            gap: 8px;
        }
        
        .historial-content {
            padding: 20px;
        }
        
        .historial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .historial-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .historial-field-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .historial-field-value {
            color: #2c3e50;
            font-size: 0.95em;
            font-weight: 500;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 3px solid #3498db;
            min-height: 20px;
        }
        
        .historial-field-value.canal-contacto {
            border-left-color: #e74c3c;
        }
        
        .historial-field-value.tipificacion {
            border-left-color: #f39c12;
        }
        
        .historial-field-value.asesor {
            border-left-color: #9b59b6;
        }
        
        .historial-field-value.obligacion {
            border-left-color: #e67e22;
        }
        
        .historial-field-value.canales-autorizados {
            border-left-color: #27ae60;
        }
        
        .historial-field-value.observaciones {
            border-left-color: #34495e;
        }
        
        .historial-observaciones {
            grid-column: 1 / -1;
        }
        
        .historial-observaciones .historial-field-value {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px;
            line-height: 1.5;
            white-space: pre-wrap;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .historial-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .historial-field-label {
                font-size: 0.8em;
            }
            
            .historial-field-value {
                font-size: 0.9em;
                padding: 6px 10px;
            }
            
            .historial-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .historial-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
        
        @media (max-width: 480px) {
            .historial-section {
                padding: 15px;
            }
            
            .historial-content {
                padding: 15px;
            }
            
            .historial-title {
                font-size: 1.2em;
            }
        }
        
        .btn-detalles {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 4px rgba(0,123,255,0.2);
        }
        
        .btn-detalles:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
        }
        
        .btn-detalles:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0,123,255,0.2);
        }
        
        .historial-fecha {
            font-weight: bold;
            color: #6c757d;
        }
        
        .historial-tipo {
            background: #007bff;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        
        .historial-resultado {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: inline-block;
            font-weight: bold;
        }
        
        /* Estilos para detalles de tipificación */
        .historial-detalles {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #e9ecef;
        }
        
        .historial-detalles h5 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        
        .detalles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .detalle-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .detalle-label {
            font-weight: bold;
            color: #6c757d;
        }
        
        .detalle-valor {
            color: #495057;
            font-weight: 500;
        }
        
        /* Estilos para próxima acción */
        .historial-proxima {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #ffeaa7;
        }
        
        .historial-proxima h5 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        
        .proxima-accion, .proxima-fecha {
            margin-bottom: 8px;
            color: #856404;
        }
        
         
         /* Estilos para información del cliente */
         .cliente-info-display {
             background: #f8fafc;
             border-radius: 8px;
             padding: 20px;
             border: 1px solid #e2e8f0;
         }
         
         .info-value {
             background: white;
             padding: 8px 12px;
             border-radius: 6px;
             border: 1px solid #e2e8f0;
             color: #374151;
             font-weight: 500;
         }
         
         /* Estilos para la información del cliente CSV */
         .info-cliente-csv {
             background: #f8fafc;
             border-radius: 12px;
             padding: 25px;
             border: 1px solid #e2e8f0;
         }
         
         .info-seccion {
             background: white;
             border-radius: 8px;
             padding: 20px;
             margin-bottom: 20px;
             border: 1px solid #e2e8f0;
             box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
         }
         
         .info-seccion:last-child {
             margin-bottom: 0;
         }
         
         .info-seccion h4 {
             color: #1f2937;
             margin-bottom: 15px;
             font-size: 1.1rem;
             font-weight: 600;
             display: flex;
             align-items: center;
             gap: 8px;
             border-bottom: 2px solid #e2e8f0;
             padding-bottom: 10px;
         }
         
         .info-grid {
             display: grid;
             grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
             gap: 15px;
         }
         
         .info-item {
             display: flex;
             flex-direction: column;
             gap: 5px;
             padding: 12px;
             background: #f8fafc;
             border-radius: 6px;
             border: 1px solid #e2e8f0;
         }
         
         .info-label {
             font-weight: 600;
             color: #6b7280;
             font-size: 0.9rem;
             text-transform: uppercase;
             letter-spacing: 0.5px;
         }
         
         .info-value {
             color: #1f2937;
             font-size: 1rem;
             font-weight: 500;
             word-break: break-word;
         }
         
         /* Estilos para indicadores de mora */
         .mora-baja {
             color: #059669;
             background: #d1fae5;
             padding: 4px 8px;
             border-radius: 4px;
             font-weight: 600;
         }
         
         .mora-media {
             color: #d97706;
             background: #fef3c7;
             padding: 4px 8px;
             border-radius: 4px;
             font-weight: 600;
         }
         
         .mora-alta {
             color: #dc2626;
             background: #fee2e2;
             padding: 4px 8px;
             border-radius: 4px;
             font-weight: 600;
         }
         
        /* Responsive para la información del cliente */
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .info-item {
                padding: 10px;
            }
        }
        
        /* Estilos para el layout de Bootstrap */
        .row {
            margin: 0;
            display: flex;
            flex-wrap: wrap;
        }
        
        .col-lg-8, .col-lg-4, .col-md-12 {
            padding: 0 15px;
            box-sizing: border-box;
        }
        
        .col-lg-8 {
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }
        
        .col-lg-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
        
        @media (max-width: 991.98px) {
            .col-lg-8, .col-lg-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        
        /* Ajustar el grid de información del cliente para la columna de 4 */
        .col-lg-4 .info-grid {
            grid-template-columns: 1fr;
            gap: 5px;
        }
        
        .col-lg-4 .info-item {
            padding: 5px 8px;
            margin-bottom: 3px;
            font-size: 0.9rem;
        }
        
        .col-lg-4 .info-seccion {
            margin-bottom: 10px;
        }
        
        .col-lg-4 .info-seccion h4 {
            font-size: 0.9rem;
            margin-bottom: 8px;
            padding: 5px 8px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .col-lg-4 .info-label {
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .col-lg-4 .info-value {
            font-size: 0.85rem;
        }
        
        /* Estilos para la lista compacta de datos CSV */
        .datos-csv-lista {
            padding: 10px 0;
        }
        
        .dato-item {
            padding: 6px 8px;
            margin-bottom: 4px;
            background: #f8f9fa;
            border-left: 3px solid #007bff;
            border-radius: 3px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #333;
        }
        
        .dato-item:hover {
            background: #e9ecef;
            border-left-color: #0056b3;
        }
        
        .dato-item.mora-baja {
            border-left-color: #28a745;
            background: #d4edda;
        }
        
        .dato-item.mora-media {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        
        .dato-item.mora-alta {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        
        /* Asegurar que Sistema de Tipificaciones y Observaciones tengan el mismo ancho */
        
        /* Asegurar que ambas secciones tengan el mismo estilo visual */
        .tipificacion-card .tipificacion-title,
        .form-section .form-title {
            color: #2d3748;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        /* Ajustar canales autorizados para que se vean en columna */
        .canales-checkboxes .checkbox-group {
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
        }
        
        .canales-checkboxes .checkbox-label {
            width: 100% !important;
            margin-bottom: 5px !important;
        }
         
         /* Estilos para el modal */
         .modal-overlay {
             display: none;
             position: fixed;
             top: 0;
             left: 0;
             width: 100%;
             height: 100%;
             background: rgba(0, 0, 0, 0.5);
             z-index: 1000;
             backdrop-filter: blur(4px);
         }
         
         .modal-content {
             position: absolute;
             top: 50%;
             left: 50%;
             transform: translate(-50%, -50%);
             background: white;
             border-radius: 12px;
             width: 90%;
             max-width: 600px;
             max-height: 90vh;
             overflow-y: auto;
             box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
         }
         
         .modal-header {
             display: flex;
             justify-content: space-between;
             align-items: center;
             padding: 20px 25px;
             border-bottom: 1px solid #e2e8f0;
             background: #f8fafc;
             border-radius: 12px 12px 0 0;
         }
         
         .modal-header h3 {
             margin: 0;
             color: #1f2937;
             font-size: 1.2rem;
         }
         
         .modal-close {
             background: none;
             border: none;
             font-size: 24px;
             color: #dc3545;
             cursor: pointer;
             padding: 0;
             width: 30px;
             height: 30px;
             display: flex;
             align-items: center;
             justify-content: center;
             border-radius: 50%;
             transition: all 0.3s ease;
             font-weight: bold;
         }
         
         .modal-close:hover {
             background: #f8d7da;
             color: #721c24;
             transform: scale(1.1);
         }
         
         .modal-body {
             padding: 25px;
         }
         
         .modal-body p {
             color: #6b7280;
             margin-bottom: 20px;
         }
         
         .modal-actions {
             display: flex;
             gap: 15px;
             justify-content: flex-end;
             margin-top: 25px;
             padding-top: 20px;
             border-top: 1px solid #e2e8f0;
         }
         
         .d-flex {
             display: flex;
         }
         
         .justify-content-between {
             justify-content: space-between;
         }
         
         .align-items-center {
             align-items: center;
         }
         
        @media (max-width: 768px) {
            .tipificacion-columnas {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .columna-tipificaciones,
            .columna-observaciones {
                padding: 20px;
            }
             
             .sub-options-grid {
                 grid-template-columns: 1fr;
             }
             
             .form-row {
                 grid-template-columns: 1fr;
             }
             
             .btn-container {
                 flex-direction: column;
                 align-items: center;
             }
             
             .modal-content {
                 width: 95%;
                 margin: 20px;
             }
             
             .modal-actions {
                 flex-direction: column;
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
     
     /* Estilos para números clickeables */
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
     
     
     
     
     /* Estilos para el modal de búsqueda */
     .resultados-busqueda {
         border-top: 1px solid #e0e0e0;
         padding-top: 15px;
     }
     
     .lista-resultados {
         max-height: none;
         overflow-y: visible;
         padding-right: 0;
     }
     
     .resultado-cliente {
         border: 1px solid #e0e0e0;
         border-radius: 8px;
         padding: 15px;
         margin-bottom: 12px;
         background: #f8f9fa;
         transition: all 0.2s ease;
         cursor: pointer;
         min-height: 80px;
     }
     
     .resultado-cliente:hover {
         background: #e9ecef;
         border-color: #007bff;
         transform: translateY(-2px);
         box-shadow: 0 4px 8px rgba(0,0,0,0.1);
     }
     
     .resultado-cliente h5 {
         margin: 0 0 8px 0;
         color: #333;
         font-size: 16px;
     }
     
     .resultado-cliente .cliente-info {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 10px;
         font-size: 14px;
         color: #666;
     }
     
     .resultado-cliente .cliente-info span {
         display: flex;
         align-items: center;
         gap: 5px;
     }
     
     .resultado-cliente .cliente-acciones {
         margin-top: 10px;
         display: flex;
         gap: 10px;
     }
     
     .btn-seleccionar-cliente {
         background: #28a745;
         color: white;
         border: none;
         padding: 8px 16px;
         border-radius: 4px;
         cursor: pointer;
         font-size: 14px;
         transition: background 0.2s ease;
     }
     
     .btn-seleccionar-cliente:hover {
         background: #218838;
     }
     
     .sin-resultados {
         text-align: center;
         padding: 20px;
         color: #666;
         font-style: italic;
     }
     
     .info-resultados {
         background: #e3f2fd;
         border: 1px solid #bbdefb;
         border-radius: 6px;
         padding: 10px 15px;
         margin-bottom: 15px;
         color: #1976d2;
         font-size: 14px;
         display: flex;
         align-items: center;
         gap: 8px;
     }
     
     .resultado-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 10px;
     }
     
     .resultado-numero {
         background: #007bff;
         color: white;
         padding: 4px 8px;
         border-radius: 12px;
         font-size: 12px;
         font-weight: bold;
     }
     
     /* Estilos específicos para el modal de búsqueda */
     .modal-busqueda {
         overflow: hidden !important;
     }
     
     .modal-body-scrollable {
         flex: 1;
         overflow-y: auto !important;
         padding: 20px;
         max-height: calc(85vh - 120px);
         min-height: 200px;
     }
     
     .modal-body-scrollable::-webkit-scrollbar {
         width: 10px;
     }
     
     .modal-body-scrollable::-webkit-scrollbar-track {
         background: #f1f1f1;
         border-radius: 5px;
     }
     
     .modal-body-scrollable::-webkit-scrollbar-thumb {
         background: #888;
         border-radius: 5px;
     }
     
     .modal-body-scrollable::-webkit-scrollbar-thumb:hover {
         background: #555;
     }
     
     /* Estilos para loading de cliente */
     .loading-cliente {
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         padding: 40px;
         text-align: center;
     }
     
     .loading-cliente .spinner-border {
         width: 3rem;
         height: 3rem;
         border-width: 0.3em;
     }
     
     .loading-cliente p {
         margin-top: 15px;
         color: #666;
         font-size: 16px;
     }
     
     /* Estilos para la gestión de productos */
     .products-management-container {
         margin-top: 20px;
     }
     
     .client-info-header {
         border-left: 4px solid #28a745;
     }
     
     .productos-lista {
         max-height: 400px;
         overflow-y: auto;
     }
     
     .producto-item {
         background: white;
         border: 1px solid #e2e8f0;
         border-radius: 8px;
         padding: 15px;
         margin-bottom: 10px;
         cursor: pointer;
         transition: all 0.3s ease;
     }
     
     .producto-item:hover {
         border-color: #3498db;
         box-shadow: 0 2px 8px rgba(52, 152, 219, 0.1);
     }
     
     .producto-item.selected {
         border-color: #3498db;
         background: #e3f2fd;
     }
     
     .producto-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 8px;
     }
     
     .producto-nombre {
         font-weight: 600;
         color: #2c3e50;
         margin: 0;
     }
     
     .producto-estado {
         padding: 4px 8px;
         border-radius: 12px;
         font-size: 12px;
         font-weight: 500;
     }
     
     .estado-activa {
         background: #d1ecf1;
         color: #0c5460;
     }
     
     .estado-pagada {
         background: #d4edda;
         color: #155724;
     }
     
     .estado-cancelada {
         background: #f8d7da;
         color: #721c24;
     }
     
     .estado-refinanciada {
         background: #fff3cd;
         color: #856404;
     }
     
     .producto-monto {
         color: #6c757d;
         font-size: 14px;
         margin: 0;
     }
     
     .producto-fecha {
         color: #adb5bd;
         font-size: 12px;
         margin: 5px 0 0 0;
     }
     
     .canales-checkboxes {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 10px;
         margin-top: 10px;
     }
     
     .checkbox-label {
         display: flex;
         align-items: center;
         cursor: pointer;
         padding: 8px;
         border: 1px solid #e2e8f0;
         border-radius: 6px;
         transition: all 0.3s ease;
     }
     
     .checkbox-label:hover {
         background: #f8f9fa;
     }
     
     .checkbox-label input[type="checkbox"] {
         margin-right: 8px;
     }
     
     .form-actions {
         display: flex;
         gap: 10px;
         margin-top: 20px;
     }
     
     .product-actions {
         display: flex;
         gap: 10px;
         flex-wrap: wrap;
     }
     
     .selected-product-info {
         border-left: 4px solid #3498db;
     }
     
     @media (max-width: 768px) {
         .canales-checkboxes {
             grid-template-columns: 1fr;
         }
         
         .form-actions {
             flex-direction: column;
         }
         
         .product-actions {
             flex-direction: column;
         }
     }
     
    </style>
</head>
<body>
    <?php 
    require_once 'shared_navbar.php';
    echo getNavbar('Gestión de Cliente', $_SESSION['user_role'] ?? '');
    ?>
    
    <!-- Botón de búsqueda integrado en el navbar (solo para asesores) -->
    <?php if (($_SESSION['user_role'] ?? '') === 'asesor'): ?>
    <style>
        /* Agregar botón de búsqueda al menú del navbar */
        .nav-menu .search-nav-item {
            margin-left: auto;
            display: flex;
            align-items: center;
        }
        
        .nav-menu .search-nav-item .search-nav-button {
            background: #3b82f6;
            color: white !important;
            border: none;
            width: 42px;
            height: 42px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
            margin-right: 15px;
        }
        
        .nav-menu .search-nav-item .search-nav-button i {
            color: white !important;
            font-size: 17px !important;
            text-shadow: none !important;
        }
        
        .nav-menu .search-nav-item .search-nav-button:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.5);
        }
        
        .nav-menu .search-nav-item .search-nav-button:hover i {
            color: white !important;
        }
        
        .nav-menu .search-nav-item .search-nav-button.active {
            background: #10b981;
        }
        
        .nav-menu .search-nav-item .search-nav-button.active i {
            color: white !important;
        }
    </style>
    
    <style>
        /* Modal de búsqueda en navbar */
        .search-overlay {
            display: none;
            position: fixed;
            top: 55px;
            right: 15px;
            width: 450px;
            max-width: 90vw;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            z-index: 1002;
            padding: 20px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .search-overlay.active {
            display: block;
        }
        
        .search-overlay-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .search-overlay-header h3 {
            margin: 0;
            color: #1f2937;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-overlay-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .search-overlay-close:hover {
            background: #f3f4f6;
            color: #dc2626;
        }
        
        .search-input-group-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .search-input-nav {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .search-input-nav:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .search-btn-nav {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 500;
            white-space: nowrap;
            font-size: 14px;
        }
        
        .search-results-nav {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 15px;
        }
        
        .search-result-item {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #f8fafc;
        }
        
        .search-result-item:hover {
            background: #e7f3ff;
            border-color: #3b82f6;
            transform: translateY(-1px);
        }
        
        .search-result-item h5 {
            margin: 0 0 8px 0;
            color: #1f2937;
            font-size: 0.95rem;
        }
        
        .search-result-item p {
            margin: 4px 0;
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .search-no-results {
            text-align: center;
            padding: 20px;
            color: #6b7280;
        }
        
        .search-loading {
            text-align: center;
            padding: 20px;
            color: #6b7280;
        }
        
        @media (max-width: 1024px) {
            .search-overlay {
                width: 90vw;
                max-width: 400px;
                right: 5vw;
            }
        }
        
        @media (max-width: 768px) {
            .search-overlay {
                width: 95vw;
                right: 2.5vw;
            }
            
            .nav-menu .search-nav-item .search-nav-button {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
        }
    </style>
    
    <script>
        // Agregar botón de búsqueda al navbar dinámicamente
        document.addEventListener('DOMContentLoaded', function() {
            const navMenu = document.querySelector('.nav-menu');
            if (navMenu) {
                const searchButton = document.createElement('li');
                searchButton.className = 'search-nav-item';
                searchButton.innerHTML = `
                    <button class="search-nav-button" onclick="toggleNavSearch()" title="Buscar Cliente">
                        <i class="fas fa-search"></i>
                    </button>
                `;
                navMenu.appendChild(searchButton);
            }
        });
    </script>
    
    <!-- Modal de búsqueda en navbar -->
    <div id="navSearchOverlay" class="search-overlay">
        <div class="search-overlay-header">
            <h3>
                <i class="fas fa-search"></i> Buscar Cliente
            </h3>
            <button class="search-overlay-close" onclick="toggleNavSearch()">&times;</button>
        </div>
        
        <form id="navSearchForm" onsubmit="navBuscarCliente(event)">
            <div class="search-input-group-nav">
                <input type="text" 
                       id="navCedulaInput" 
                       name="cedula" 
                       placeholder="Ingresa la cédula..." 
                       class="search-input-nav"
                       required>
                <button type="submit" class="btn btn-primary search-btn-nav">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>
        
        <div id="navSearchResults" class="search-results-nav" style="display: none;">
            <!-- Resultados se cargarán aquí -->
        </div>
    </div>
    
    <?php endif; ?>
    
    <div class="gestion-container">

        <!-- Layout principal con Bootstrap -->
        <div class="row">
            <!-- Información del Cliente CSV (4 columnas) - IZQUIERDA -->
            <div class="col-lg-4 col-md-12">
                <?php if (isset($cliente) && $cliente): ?>
                <!-- Información Básica del Cliente -->
                <div class="cliente-info-card" style="margin-bottom: 20px;">
                    <div class="cliente-header">
                        <div class="cliente-avatar">
                            <?php echo strtoupper(substr($cliente['nombre'] ?? 'C', 0, 1)); ?>
                        </div>
                        <div class="cliente-details">
                            <h2><?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?></h2>
                            <div class="cliente-meta">
                                <span class="cliente-meta-item">
                                    <strong>Cédula:</strong> <?php echo htmlspecialchars($cliente['cedula'] ?? ''); ?>
                                </span>
                                <span class="cliente-meta-item">|</span>
                                
                                <!-- Desplegable para números de teléfono -->
                                <span class="cliente-meta-item">
                                    <div class="telefono-dropdown" style="display: inline-block; position: relative;">
                                        <strong>Teléfono:</strong> 
                                        <select id="telefonoSelect" onchange="actualizarTelefonoSeleccionado()">
                                            <?php if (!empty($cliente['telefono'])): ?>
                                                <option value="<?php echo htmlspecialchars($cliente['telefono']); ?>" data-tipo="Teléfono"><?php echo htmlspecialchars($cliente['telefono']); ?> (Teléfono)</option>
                                            <?php endif; ?>
                                            <?php if (!empty($cliente['celular2'])): ?>
                                                <option value="<?php echo htmlspecialchars($cliente['celular2']); ?>" data-tipo="Celular" <?php echo empty($cliente['telefono']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cliente['celular2']); ?> (Celular)</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </span>
                                
                                <?php if (!empty($cliente['email'])): ?>
                                    <span class="cliente-meta-item">|</span>
                                    <span class="cliente-meta-item">
                                        <strong>Correo:</strong> <?php echo htmlspecialchars($cliente['email'] ?? ''); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información del Cliente CSV -->
                <div class="cliente-info-card">
                    <h3 class="tipificacion-title">
                        <i class="fas fa-database"></i> Información del Cliente
                    </h3>
                    
                    <div class="info-cliente-csv" style="max-height: 400px; overflow-y: auto;">
                        <?php if (!empty($obligaciones) && count($obligaciones) > 0): ?>
                            <!-- Mostrar todas las obligaciones del cliente -->
                            <div class="obligaciones-container">
                                <h4 style="margin-bottom: 15px; color: #2c3e50; font-size: 14px;">
                                    <i class="fas fa-list-alt"></i> Obligaciones (<?php echo count($obligaciones); ?>)
                                </h4>
                                
                                <?php foreach ($obligaciones as $index => $obligacion): ?>
                                    <div class="obligacion-item" style="background: white; border: 1px solid #e1e8ed; border-radius: 8px; padding: 12px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                            <div style="flex: 1;">
                                                <h6 style="margin: 0 0 5px 0; color: #2c3e50; font-size: 13px; font-weight: 600;">
                                                    <i class="fas fa-file-invoice"></i> Obligación #<?php echo htmlspecialchars($obligacion['obligacion'] ?? 'N/A'); ?>
                                                </h6>
                                                <div style="font-size: 11px; color: #7f8c8d;">
                                                    <strong>Producto:</strong> <?php echo htmlspecialchars($obligacion['producto'] ?? 'N/A'); ?> | 
                                                    <strong>Propiedad:</strong> <?php echo htmlspecialchars($obligacion['propiedad'] ?? 'N/A'); ?>
                                                </div>
                                            </div>
                                            <div style="text-align: right; font-size: 11px; color: #27ae60; font-weight: 600;">
                                                <?php if (!empty($obligacion['saldo_k_obligacion'])): ?>
                                                    $<?php echo number_format($obligacion['saldo_k_obligacion'], 0, ',', '.'); ?>
                                                <?php else: ?>
                                                    Sin saldo
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 10px; color: #555;">
                                            <?php if (!empty($obligacion['capital_cliente'])): ?>
                                            <div>
                                                <strong>Capital:</strong> $<?php echo number_format($obligacion['capital_cliente'], 0, ',', '.'); ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($obligacion['pago_total_obligacion'])): ?>
                                            <div>
                                                <strong>Pago Total:</strong> $<?php echo number_format($obligacion['pago_total_obligacion'], 0, ',', '.'); ?>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($obligacion['mora_actual'])): ?>
                                            <div>
                                                <strong>Mora:</strong> <?php echo $obligacion['mora_actual']; ?> días
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($obligacion['medicion'])): ?>
                                            <div>
                                                <strong>Medición:</strong> <?php echo htmlspecialchars($obligacion['medicion']); ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($obligacion['estado'])): ?>
                                        <div style="margin-top: 8px; text-align: right;">
                                            <span style="background: <?php echo $obligacion['estado'] === 'activa' ? '#e8f5e8' : '#fef3c7'; ?>; 
                                                         color: <?php echo $obligacion['estado'] === 'activa' ? '#27ae60' : '#f59e0b'; ?>; 
                                                         padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 500;">
                                                <?php echo ucfirst($obligacion['estado']); ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Estadísticas de obligaciones -->
                                <?php if (!empty($estadisticasObligaciones)): ?>
                                <div class="estadisticas-obligaciones" style="margin-top: 15px; padding: 10px; background: #e8f4f8; border-radius: 6px; border-left: 4px solid #3498db;">
                                    <h6 style="margin: 0 0 8px 0; color: #2c3e50; font-size: 12px;">
                                        <i class="fas fa-chart-bar"></i> Resumen
                                    </h6>
                                    <div style="font-size: 11px; color: #555;">
                                        <div>Total: <?php echo $estadisticasObligaciones['total_obligaciones']; ?> obligaciones</div>
                                        <?php if (!empty($estadisticasObligaciones['saldo_total'])): ?>
                                        <div>Saldo Total: $<?php echo number_format($estadisticasObligaciones['saldo_total'], 0, ',', '.'); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($estadisticasObligaciones['mora_promedio'])): ?>
                                        <div>Mora Promedio: <?php echo round($estadisticasObligaciones['mora_promedio']); ?> días</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <!-- Mensaje cuando no hay obligaciones -->
                            <div style="text-align: center; padding: 20px; color: #7f8c8d;">
                                <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                                <p style="margin: 0; font-size: 12px;">No se encontraron obligaciones para este cliente.</p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Historial de Gestiones -->
                        <?php if (!empty($historial) && count($historial) > 0): ?>
                            <div style="margin-top: 20px; border-top: 1px solid #e1e8ed; padding-top: 15px;">
                                <h4 style="margin-bottom: 15px; color: #2c3e50; font-size: 14px;">
                                    <i class="fas fa-history"></i> Historial de Gestiones (<?php echo count($historial); ?>)
                                </h4>
                                
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach (array_slice($historial, 0, 5) as $gestion): ?>
                                        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 10px; margin-bottom: 8px;">
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                                                <div style="font-size: 11px; color: #6c757d;">
                                                    <i class="fas fa-calendar-alt"></i> 
                                                    <?php echo date('d/m/Y H:i', strtotime($gestion['fecha_gestion'])); ?>
                                                </div>
                                                <div style="font-size: 10px; color: #28a745; font-weight: 600;">
                                                    <?php echo htmlspecialchars($gestion['resultado'] ?? 'Sin resultado'); ?>
                                                </div>
                                            </div>
                                            
                                            <div style="font-size: 10px; color: #495057;">
                                                <strong>Tipo:</strong> <?php echo htmlspecialchars($gestion['tipo_gestion'] ?? 'N/A'); ?>
                                                <?php if (!empty($gestion['duracion_llamada'])): ?>
                                                    | <strong>Duración:</strong> <?php echo round($gestion['duracion_llamada'], 1); ?> min
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($gestion['comentarios'])): ?>
                                                <div style="font-size: 10px; color: #6c757d; margin-top: 5px; font-style: italic;">
                                                    "<?php echo htmlspecialchars(substr($gestion['comentarios'], 0, 100)); ?><?php echo strlen($gestion['comentarios']) > 100 ? '...' : ''; ?>"
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($historial) > 5): ?>
                                        <div style="text-align: center; margin-top: 10px; font-size: 10px; color: #6c757d;">
                                            <i class="fas fa-ellipsis-h"></i> 
                                            Y <?php echo count($historial) - 5; ?> gestiones más...
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="margin-top: 20px; border-top: 1px solid #e1e8ed; padding-top: 15px;">
                                <h4 style="margin-bottom: 15px; color: #2c3e50; font-size: 14px;">
                                    <i class="fas fa-history"></i> Historial de Gestiones
                                </h4>
                                <div style="text-align: center; padding: 15px; color: #7f8c8d; background: #f8f9fa; border-radius: 6px;">
                                    <i class="fas fa-info-circle" style="font-size: 16px; margin-bottom: 5px;"></i>
                                    <p style="margin: 0; font-size: 11px;">No hay gestiones registradas para este cliente.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Mensaje de alerta para el aplicativo de agentes -->
                    <div class="alert alert-info d-flex align-items-center" style="margin: 15px 0; border-left: 4px solid #17a2b8;">
                        <i class="fas fa-phone-alt me-3" style="font-size: 1.2rem; color: #17a2b8;"></i>
                        <div class="flex-grow-1">
                            <strong>Ingresa aquí al aplicativo agentes para las llamadas</strong>
                            <br>
                            <small class="text-muted">Accede al sistema de marcado automático para realizar llamadas</small>
                        </div>
                        <div class="ms-3">
                            <a href="javascript:void(0)" 
                               class="btn btn-outline-info btn-sm"
                               onclick="abrirAplicativoAgentes()">
                                <i class="fas fa-external-link-alt"></i> Dale aquí
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sistema de Tipificaciones (8 columnas) - DERECHA -->
            <div class="col-lg-8 col-md-12">
                <div class="tipificacion-card">
                    <h3 class="tipificacion-title">
                        📞 Sistema de Tipificaciones de Llamadas
                    </h3>
                    
                    <form method="POST" id="tipificacionForm" action="index.php?action=guardar_tipificacion">
                        <input type="hidden" name="cliente_id" value="<?php echo $cliente['id']; ?>">
                        <input type="hidden" name="tipificacion" id="tipificacion_principal" value="">
                        <input type="hidden" name="sub_tipificacion" id="sub_tipificacion_hidden" value="">
                        
                        <!-- Diseño en dos columnas dentro del sistema de tipificaciones -->
                        <div class="tipificacion-columnas">
                            <!-- Columna izquierda: Tipificaciones -->
                            <div class="columna-tipificaciones">
                                <h4>🎯 Tipificación de la Llamada</h4>
                                
                                <!-- Forma de contacto -->
                        <div class="form-group">
                            <label for="forma_contacto" class="form-label">Forma de Contacto:</label>
                            <select name="forma_contacto" id="forma_contacto" class="form-select" required>
                                <option value="">Selecciona una opción</option>
                                <option value="llamada">Llamada</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="email">Email/Correo Electrónico</option>
                            </select>
                        </div>
                        
                        <!-- Selección de Obligación/Producto -->
                        <div class="form-group">
                            <label for="obligacion_seleccionada" class="form-label">
                                Obligación/Producto a Gestionar:
                                <span id="obligacion_required_indicator" style="display: none; color: #dc3545; font-weight: bold;">*</span>
                            </label>
                            <select name="obligacion_seleccionada" id="obligacion_seleccionada" class="form-select" onchange="manejarSeleccionObligacion()">
                                <option value="ninguna">Ninguna</option>
                                <?php if (isset($obligaciones) && !empty($obligaciones)): ?>
                                    <?php foreach ($obligaciones as $obligacion): ?>
                                        <?php if (!empty($obligacion['producto'])): ?>
                                            <option value="<?php echo $obligacion['id']; ?>" 
                                                    data-producto="<?php echo htmlspecialchars($obligacion['producto']); ?>"
                                                    data-monto="<?php echo $obligacion['saldo_k_obligacion'] ?? 0; ?>"
                                                    data-obligacion="<?php echo htmlspecialchars($obligacion['obligacion']); ?>">
                                                <?php echo htmlspecialchars($obligacion['producto']); ?> - $<?php echo number_format($obligacion['saldo_k_obligacion'] ?? 0, 0, ',', '.'); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- Primer dropdown: Tipo de gestión -->
                        <div class="form-group">
                            <label for="tipo_gestion" class="form-label">Tipo de Gestión:</label>
                            <select name="tipo_gestion" id="tipo_gestion" class="form-select" onchange="mostrarTipificacionesEspecificas(this.value)" required>
                                <option value="">Selecciona una opción</option>
                                <option value="hacer_llamada">HACER LLAMADA</option>
                                <option value="recibir_llamada">RECIBIR LLAMADA</option>
                            </select>
                        </div>
                        
                        <!-- Segundo dropdown: Tipo de contacto (para HACER LLAMADA) -->
                        <div id="subcategoria_hacer_llamada" class="form-group" style="display: none;">
                            <label for="subcategoria_hacer" class="form-label">Tipo de Contacto:</label>
                            <select name="subcategoria_hacer" id="subcategoria_hacer" class="form-select" onchange="mostrarOpcionesEspecificasHacer(this.value)" required>
                                <option value="">Selecciona el tipo de contacto</option>
                                <option value="contacto_directo">CONTACTO DIRECTO</option>
                                <option value="contacto_tercero">CONTACTO TERCERO</option>
                                <option value="no_contacto">NO CONTACTO</option>
                            </select>
                        </div>
                        
                        <!-- Segundo dropdown: Tipo de contacto (para RECIBIR LLAMADA) -->
                        <div id="subcategoria_recibir_llamada" class="form-group" style="display: none;">
                            <label for="subcategoria_recibir" class="form-label">Tipo de Contacto:</label>
                            <select name="subcategoria_recibir" id="subcategoria_recibir" class="form-select" onchange="mostrarOpcionesEspecificasRecibir(this.value)" required>
                                <option value="">Selecciona el tipo de contacto</option>
                                <option value="contacto_directo">CONTACTO DIRECTO</option>
                                <option value="contacto_tercero">CONTACTO TERCERO</option>
                            </select>
                        </div>
                        
                        <!-- Tercer nivel: Opciones específicas para HACER LLAMADA -->
                        <div id="opciones_especificas_hacer" class="tipificaciones-especificas" style="display: none;">
                            <div class="form-group">
                                <label for="opcion_especifica_hacer" class="form-label">Tipificación Específica:</label>
                                <select name="opcion_especifica_hacer" id="opcion_especifica_hacer" class="form-select" onchange="seleccionarOpcionEspecificaHacer(this.value)" required>
                                    <option value="">Selecciona una tipificación específica</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Tercer nivel: Opciones específicas para RECIBIR LLAMADA -->
                        <div id="opciones_especificas_recibir" class="tipificaciones-especificas" style="display: none;">
                            <div class="form-group">
                                <label for="opcion_especifica_recibir" class="form-label">Tipificación Específica:</label>
                                <select name="opcion_especifica_recibir" id="opcion_especifica_recibir" class="form-select" onchange="seleccionarOpcionEspecificaRecibir(this.value)" required>
                                    <option value="">Selecciona una tipificación específica</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Sección de Campos Específicos para Acuerdo de Pago -->
                        <div id="campos_acuerdo_pago" class="campos-especificos" style="display: none;">
                            <h4>💰 Información del Acuerdo de Pago</h4>
                            <div class="form-group">
                                <label for="valor_acuerdo" class="form-label">Valor del Acuerdo:</label>
                                <input type="text" name="valor_acuerdo_display" id="valor_acuerdo" class="form-control" placeholder="Ej: $1.500.000" oninput="formatearPesosAcuerdo(this)" required>
                                <input type="hidden" name="valor_acuerdo" id="valor_acuerdo_hidden">
                                <small class="form-help">Ingrese el valor total del acuerdo de pago</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="no_cuotas" class="form-label">Número de Cuotas Totales:</label>
                                <input type="number" name="no_cuotas" id="no_cuotas" class="form-control" min="1" max="60" placeholder="Ej: 12">
                            </div>
                            
                            <div class="form-group">
                                <label for="fecha_pago" class="form-label">Fecha de Pago:</label>
                                <input type="date" name="fecha_pago" id="fecha_pago" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="valor_cuota" class="form-label">Valor de la Cuota:</label>
                                <input type="text" name="valor_cuota_display" id="valor_cuota" class="form-control" placeholder="Ej: $150.000" oninput="formatearPesos(this)">
                                <input type="hidden" name="valor_cuota" id="valor_cuota_hidden">
                            </div>
                            
                            <div class="form-group">
                                <label for="numero_cuota" class="form-label">Número de la Cuota:</label>
                                <input type="number" name="numero_cuota" id="numero_cuota" class="form-control" min="1" max="60" placeholder="Ej: 3">
                            </div>
                        </div>
                        
                            </div>
                            
                            <!-- Columna derecha: Observaciones y Comentarios -->
                            <div class="columna-observaciones">
                                <h4>📝 Observaciones y Comentarios</h4>
                                <p><em>Documente las interacciones y seguimientos pertinentes</em></p>
                                
                                <!-- Campo de observaciones -->
                                <div class="form-group">
                                    <label for="comentarios" class="form-label">Observaciones Detalladas:</label>
                                    <textarea name="comentarios" id="comentarios" class="form-textarea" 
                                              placeholder="Describe detalladamente el resultado de la gestión, acuerdos, próximos pasos, objeciones del cliente, etc." 
                                              required></textarea>
                                </div>
                                
                                <!-- Canales de Comunicación Autorizados -->
                                <div class="canales-autorizados-section" style="margin-top: 20px;">
                                    <h5 class="canales-title">
                                        <i class="fas fa-broadcast-tower"></i> Canales de Comunicación Autorizados
                                    </h5>
                                    <p><em>Seleccione los canales autorizados por la empresa para futuras comunicaciones</em></p>
                                    
                                    <div class="canales-checkboxes">
                                        <div class="checkbox-group" style="display: flex; flex-direction: column; gap: 10px;">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="canales_autorizados[]" value="llamada" class="canal-checkbox">
                                                <span class="checkbox-text">📞 Llamada Telefónica</span>
                                            </label>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="canales_autorizados[]" value="whatsapp" class="canal-checkbox">
                                                <span class="checkbox-text">📱 WhatsApp</span>
                                            </label>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="canales_autorizados[]" value="correo_electronico" class="canal-checkbox">
                                                <span class="checkbox-text">📧 Correo Electrónico</span>
                                            </label>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="canales_autorizados[]" value="sms" class="canal-checkbox">
                                                <span class="checkbox-text">💬 SMS</span>
                                            </label>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="canales_autorizados[]" value="correo_fisico" class="canal-checkbox">
                                                <span class="checkbox-text">📮 Correo Físico</span>
                                            </label>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="canales_autorizados[]" value="mensajeria_aplicaciones" class="canal-checkbox">
                                                <span class="checkbox-text">📱 Mensajería por Aplicaciones</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    
                    <!-- Botones de acción dinámicos -->
                    <div class="btn-container">
                        <!-- Botón principal que cambia según el estado -->
                        <button type="submit" id="btnGuardarPrincipal" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Gestión
                        </button>
                        
                        
                        <!-- Botones de navegación (se muestran después de completar todas las obligaciones) -->
                        <div id="btnNavegacion" style="display: none;">
                            <button type="button" id="btnSiguienteCliente" class="btn btn-warning" onclick="irAlSiguienteCliente()">
                                <i class="fas fa-arrow-right"></i> Siguiente Cliente
                            </button>
                            <button type="button" id="btnBuscarCliente" class="btn btn-info" onclick="mostrarModalBusqueda()">
                                <i class="fas fa-search"></i> Buscar Cliente
                            </button>
                        </div>
                        
                        <!-- Botones de navegación estándar -->
                        <a href="index.php?action=mis_tareas" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Tareas
                        </a>
                        <a href="index.php?action=dashboard" class="btn btn-success">
                            <i class="fas fa-home"></i> Ir al Dashboard
                        </a>
                    </div>
                </div>
                            </div>
                         
                    </form>
                </div>
            </div>
        </div>



        <!-- Historial de Gestiones -->
         <?php if (isset($historial) && !empty($historial)): ?>
         <div class="historial-section">
             <h4 class="historial-title">
                 <i class="fas fa-history"></i> 
                 Historial de Interacciones (<?php echo count($historial); ?> registros)
             </h4>
             <?php foreach ($historial as $gestion): ?>
             <div class="historial-item">
                 <div class="historial-header">
                     <div class="historial-fecha">
                         <i class="fas fa-calendar-alt"></i>
                         <?php echo date('d/m/Y H:i', strtotime($gestion['fecha_gestion'])); ?>
                     </div>
                     <div class="historial-actions">
                         <button class="btn-detalles" onclick="mostrarDetallesGestion(<?php echo $gestion['id']; ?>)">
                             <i class="fas fa-expand-arrows-alt"></i> Expandir
                         </button>
                     </div>
                 </div>
                 
                 <div class="historial-content">
                     <div class="historial-grid">
                         <!-- Canal de Contacto -->
                         <div class="historial-field">
                             <div class="historial-field-label">
                                 <i class="fas fa-phone"></i> Canal de Contacto
                             </div>
                             <div class="historial-field-value canal-contacto">
                                 <?php 
                                 $canalContacto = $gestion['forma_contacto'] ?? 'llamada';
                                 $canalMap = [
                                     'llamada' => '📞 Llamada Telefónica',
                                     'whatsapp' => '📱 WhatsApp',
                                     'email' => '📧 Correo Electrónico',
                                     'correo_electronico' => '📧 Correo Electrónico',
                                     'chat' => '💬 Chat en Línea'
                                 ];
                                 echo $canalMap[$canalContacto] ?? ucfirst($canalContacto);
                                 ?>
                             </div>
                         </div>
                         
                         <!-- Tipificación -->
                         <div class="historial-field">
                             <div class="historial-field-label">
                                 <i class="fas fa-tags"></i> Tipificación
                             </div>
                             <div class="historial-field-value tipificacion">
                                 <?php echo htmlspecialchars($gestion['tipificacion_completa'] ?? $gestion['tipo_gestion'] ?? 'No especificada'); ?>
                             </div>
                         </div>
                         
                         <!-- Asesor que lo tipificó -->
                         <div class="historial-field">
                             <div class="historial-field-label">
                                 <i class="fas fa-user"></i> Asesor Responsable
                             </div>
                             <div class="historial-field-value asesor">
                                 <?php echo htmlspecialchars($gestion['asesor_nombre'] ?? 'No asignado'); ?>
                             </div>
                         </div>
                         
                         <!-- Obligación a Gestionar -->
                         <?php if (!empty($gestion['producto_gestionado'])): ?>
                         <div class="historial-field">
                             <div class="historial-field-label">
                                 <i class="fas fa-box"></i> Obligación a Gestionar
                             </div>
                             <div class="historial-field-value obligacion">
                                 <?php echo htmlspecialchars($gestion['producto_gestionado']); ?>
                                 <?php if (!empty($gestion['numero_obligacion'])): ?>
                                     <span style="color: #6c757d; font-size: 0.9em;">(<?php echo htmlspecialchars($gestion['numero_obligacion']); ?>)</span>
                                 <?php endif; ?>
                             </div>
                         </div>
                         <?php endif; ?>
                         
                         <!-- Canales Autorizados -->
                         <div class="historial-field">
                             <div class="historial-field-label">
                                 <i class="fas fa-broadcast-tower"></i> Canales Autorizados
                             </div>
                             <div class="historial-field-value canales-autorizados">
                                 <?php if (!empty($gestion['canales_autorizados'])): ?>
                                     <?php 
                                    $canalesMap = [
                                        'llamada' => '📞 Llamada Telefónica',
                                        'whatsapp' => '📱 WhatsApp',
                                        'correo_electronico' => '📧 Correo Electrónico',
                                        'sms' => '💬 SMS',
                                        'correo_fisico' => '📮 Correo Físico',
                                        'mensajeria_aplicaciones' => '📱 Mensajería por Aplicaciones'
                                    ];
                                    // Normalizar origen (array o string CSV) y evitar duplicados
                                    $canalesOrigen = $gestion['canales_autorizados'];
                                    if (!is_array($canalesOrigen)) {
                                        $canalesOrigen = array_filter(array_map('trim', explode(',', (string)$canalesOrigen)));
                                    }
                                    // Normalizar a minúscula y sin espacios, y evitar duplicados
                                    $canalesOrigen = array_map(function($c){ return strtolower(trim($c)); }, $canalesOrigen);
                                    $canalesOrigen = array_values(array_unique($canalesOrigen));
                                    $canalesTexto = array_map(function($canal) use ($canalesMap) {
                                        return $canalesMap[$canal] ?? $canal;
                                    }, $canalesOrigen);
                                    echo implode(', ', $canalesTexto);
                                     ?>
                                 <?php else: ?>
                                     <span style="color: #6c757d; font-style: italic;">No especificados</span>
                                 <?php endif; ?>
                             </div>
                         </div>
                         
                         <!-- Observaciones -->
                         <div class="historial-field historial-observaciones">
                             <div class="historial-field-label">
                                 <i class="fas fa-comments"></i> Observaciones
                             </div>
                             <div class="historial-field-value observaciones">
                                 <?php echo htmlspecialchars($gestion['comentarios'] ?? 'Sin observaciones'); ?>
                             </div>
                         </div>
                     </div>
                 </div>
                 
                 <!-- Mostrar campos específicos de tipificación si existen -->
                 <?php 
                 // Verificar si hay información básica de gestión (edad, personas, cotización, whatsapp)
                 $tieneInfoBasica = !empty($gestion['edad_cliente']) || !empty($gestion['num_personas']) || !empty($gestion['valor_cotizacion']) || !empty($gestion['whatsapp_enviado']);
                 
                 // Verificar si hay información de acuerdo de pago (ignorar fechas inválidas y valores en cero)
                 $tieneInfoAcuerdoPago = (!empty($gestion['no_cuotas']) && $gestion['no_cuotas'] > 0) || 
                                        (!empty($gestion['fecha_pago']) && $gestion['fecha_pago'] !== '0000-00-00' && $gestion['fecha_pago'] !== '') || 
                                        (!empty($gestion['valor_cuota']) && $gestion['valor_cuota'] > 0) || 
                                        (!empty($gestion['numero_cuota']) && $gestion['numero_cuota'] > 0) ||
                                        (!empty($gestion['valor_acuerdo']) && $gestion['valor_acuerdo'] > 0);
                 
                 if ($tieneInfoBasica || $tieneInfoAcuerdoPago): 
                 ?>
                 <div class="historial-detalles">
                     <h5>📊 Detalles de la Gestión:</h5>
                     <div class="detalles-grid">
                         <!-- Información básica de gestión -->
                         <?php if (!empty($gestion['edad_cliente'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">👤 Edad:</span>
                             <span class="detalle-valor"><?php echo htmlspecialchars($gestion['edad_cliente'] ?? ''); ?> años</span>
                         </div>
                         <?php endif; ?>
                         
                         <?php if (!empty($gestion['num_personas'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">👥 Personas a cubrir:</span>
                             <span class="detalle-valor"><?php echo htmlspecialchars($gestion['num_personas'] ?? ''); ?></span>
                         </div>
                         <?php endif; ?>
                         
                         <?php if (!empty($gestion['valor_cotizacion'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">💰 Valor:</span>
                             <span class="detalle-valor">$<?php echo number_format($gestion['valor_cotizacion'], 0, ',', '.'); ?></span>
                         </div>
                         <?php endif; ?>
                         
                         <?php if (!empty($gestion['whatsapp_enviado'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">📱 WhatsApp:</span>
                             <span class="detalle-valor"><?php echo htmlspecialchars($gestion['whatsapp_enviado'] ?? ''); ?></span>
                         </div>
                         <?php endif; ?>
                         
                         <!-- Campos específicos de acuerdo de pago - solo mostrar si hay información de cuotas -->
                         <?php if ($tieneInfoAcuerdoPago): ?>
                         <div class="detalle-item" style="grid-column: 1 / -1; margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                             <span class="detalle-label" style="font-weight: 600; color: #059669;">💳 Información de Acuerdo de Pago:</span>
                         </div>
                         
                         <?php if (!empty($gestion['no_cuotas'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">📊 Total Cuotas:</span>
                             <span class="detalle-valor"><?php echo htmlspecialchars($gestion['no_cuotas']); ?></span>
                         </div>
                         <?php endif; ?>
                         
                         <?php if (!empty($gestion['fecha_pago'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">📅 Fecha Pago:</span>
                             <span class="detalle-valor"><?php echo date('d/m/Y', strtotime($gestion['fecha_pago'])); ?></span>
                         </div>
                         <?php endif; ?>
                         
                         <?php if (!empty($gestion['valor_cuota'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">💰 Valor Cuota:</span>
                             <span class="detalle-valor">$<?php echo number_format($gestion['valor_cuota'], 0, ',', '.'); ?></span>
                         </div>
                         <?php endif; ?>
                         
                         <?php if (!empty($gestion['numero_cuota'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">🔢 Número Cuota:</span>
                             <span class="detalle-valor"><?php echo htmlspecialchars($gestion['numero_cuota']); ?></span>
                         </div>
                         <?php endif; ?>
                         
                         <?php if (!empty($gestion['valor_acuerdo'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">💰 Valor del Acuerdo:</span>
                             <span class="detalle-valor">$<?php echo number_format($gestion['valor_acuerdo'], 0, ',', '.'); ?></span>
                         </div>
                         <?php endif; ?>
                         <?php endif; ?>
                         
                         <?php if (!empty($gestion['monto_venta'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">💵 Venta:</span>
                             <span class="detalle-valor">$<?php echo number_format($gestion['monto_venta'], 0, ',', '.'); ?></span>
                         </div>
                         <?php endif; ?>
                         
                         <?php if (!empty($gestion['duracion_llamada'])): ?>
                         <div class="detalle-item">
                             <span class="detalle-label">⏱️ Duración:</span>
                             <span class="detalle-valor"><?php echo htmlspecialchars($gestion['duracion_llamada'] ?? ''); ?> min</span>
                         </div>
                         <?php endif; ?>
                     </div>
                 </div>
                 <?php endif; ?>
                 
                 <?php if (!empty($gestion['proxima_accion']) || !empty($gestion['proxima_fecha'])): ?>
                 <div class="historial-proxima">
                     <h5>📅 Próxima Acción:</h5>
                     <?php if (!empty($gestion['proxima_accion'])): ?>
                     <div class="proxima-accion">
                         <strong>Acción:</strong> <?php echo htmlspecialchars($gestion['proxima_accion'] ?? ''); ?>
                     </div>
                     <?php endif; ?>
                     <?php if (!empty($gestion['proxima_fecha'])): ?>
                     <div class="proxima-fecha">
                         <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($gestion['proxima_fecha'])); ?>
                     </div>
                     <?php endif; ?>
                 </div>
                 <?php endif; ?>
                 
             </div>
             <?php endforeach; ?>
         </div>
         <?php else: ?>
         <div class="historial-section">
             <h4 class="historial-title">📋 Historial de Gestiones</h4>
             <div class="alert alert-info">
                 <i class="fas fa-info-circle"></i>
                 <strong>Sin historial:</strong> Este cliente no tiene gestiones registradas aún.
             </div>
         </div>
         <?php endif; ?>
     </div>


    <script>
        let tipificacionSeleccionada = null;
        let subTipificacionSeleccionada = null;
        
        // Mapeo centralizado de canales para evitar duplicación
        const CANALES_MAP = {
            'llamada': '📞 Llamada Telefónica',
            'whatsapp': '📱 WhatsApp',
            'email': '📧 Correo Electrónico',
            'correo_electronico': '📧 Correo Electrónico',
            'sms': '💬 SMS',
            'correo_fisico': '📮 Correo Físico',
            'mensajeria_aplicaciones': '📱 Mensajería por Aplicaciones',
            'chat': '💬 Chat en Línea'
        };
        
        // Función para obtener el texto del canal
        function getCanalTexto(canal) {
            return CANALES_MAP[canal] || canal;
        }

        // Función para mostrar tipificaciones específicas según el tipo de gestión
        function mostrarTipificacionesEspecificas(tipo) {
            // Ocultar todas las secciones (con verificación de existencia)
            const subcategoriaHacer = document.getElementById('subcategoria_hacer_llamada');
            const subcategoriaRecibir = document.getElementById('subcategoria_recibir_llamada');
            const opcionesHacer = document.getElementById('opciones_especificas_hacer');
            const opcionesRecibir = document.getElementById('opciones_especificas_recibir');
            const camposAcuerdoPago = document.getElementById('campos_acuerdo_pago');
            // Referencia a acciones específicas removida
            
            // Obtener los selects para manejar el atributo required
            const subcategoriaHacerSelect = document.getElementById('subcategoria_hacer');
            const subcategoriaRecibirSelect = document.getElementById('subcategoria_recibir');
            const opcionEspecificaHacer = document.getElementById('opcion_especifica_hacer');
            const opcionEspecificaRecibir = document.getElementById('opcion_especifica_recibir');
            
            // Ocultar secciones y remover required de selects ocultos
            if (subcategoriaHacer) {
                subcategoriaHacer.style.display = 'none';
                if (subcategoriaHacerSelect) subcategoriaHacerSelect.removeAttribute('required');
            }
            if (subcategoriaRecibir) {
                subcategoriaRecibir.style.display = 'none';
                if (subcategoriaRecibirSelect) subcategoriaRecibirSelect.removeAttribute('required');
            }
            if (opcionesHacer) {
                opcionesHacer.style.display = 'none';
                if (opcionEspecificaHacer) opcionEspecificaHacer.removeAttribute('required');
            }
            if (opcionesRecibir) {
                opcionesRecibir.style.display = 'none';
                if (opcionEspecificaRecibir) opcionEspecificaRecibir.removeAttribute('required');
            }
            if (camposAcuerdoPago) {
                camposAcuerdoPago.style.display = 'none';
                // Limpiar y remover required de campos de acuerdo de pago
                const valorAcuerdo = document.getElementById('valor_acuerdo');
                if (valorAcuerdo) {
                    valorAcuerdo.removeAttribute('required');
                    valorAcuerdo.value = '';
                }
                
                const camposObligatorios = ['no_cuotas', 'fecha_pago', 'valor_cuota', 'numero_cuota'];
                camposObligatorios.forEach(campoId => {
                    const campo = document.getElementById(campoId);
                    if (campo) {
                        campo.removeAttribute('required');
                        campo.value = '';
                    }
                });
                
                // Limpiar también los campos ocultos
                const valorCuotaHidden = document.getElementById('valor_cuota_hidden');
                if (valorCuotaHidden) {
                    valorCuotaHidden.value = '';
                }
                const valorAcuerdoHidden = document.getElementById('valor_acuerdo_hidden');
                if (valorAcuerdoHidden) {
                    valorAcuerdoHidden.value = '';
                }
            }
            // Acciones específicas removidas
            
            // Mostrar la sección correspondiente y agregar required
            if (tipo === 'hacer_llamada' && subcategoriaHacer) {
                subcategoriaHacer.style.display = 'block';
                if (subcategoriaHacerSelect) subcategoriaHacerSelect.setAttribute('required', 'required');
            } else if (tipo === 'recibir_llamada' && subcategoriaRecibir) {
                subcategoriaRecibir.style.display = 'block';
                if (subcategoriaRecibirSelect) subcategoriaRecibirSelect.setAttribute('required', 'required');
            }
            
            // Limpiar selecciones anteriores
            const subTipificacionHidden = document.getElementById('sub_tipificacion_hidden');
            
            if (subcategoriaHacerSelect) subcategoriaHacerSelect.value = '';
            if (subcategoriaRecibirSelect) subcategoriaRecibirSelect.value = '';
            if (opcionEspecificaHacer) opcionEspecificaHacer.value = '';
            if (opcionEspecificaRecibir) opcionEspecificaRecibir.value = '';
            if (subTipificacionHidden) subTipificacionHidden.value = '';
            
            // Actualizar la tipificación principal
            tipificacionSeleccionada = tipo;
            document.getElementById('tipificacion_principal').value = tipo;
            
            // Limpiar campos de acciones específicas si existen
            const camposAccion = document.querySelectorAll('#accionContenido input, #accionContenido select, #accionContenido textarea');
            camposAccion.forEach(campo => {
                if (campo && campo.type) {
                    if (campo.type === 'text' || campo.type === 'email' || campo.type === 'tel' || campo.type === 'number') {
                        campo.value = '';
                    } else if (campo.type === 'select-one') {
                        campo.selectedIndex = 0;
                    }
                }
            });
        }

        // Función para mostrar opciones específicas de HACER LLAMADA
        function mostrarOpcionesEspecificasHacer(subcategoria) {
            const opcionesSelect = document.getElementById('opcion_especifica_hacer');
            const opcionesDiv = document.getElementById('opciones_especificas_hacer');
            
            if (!opcionesSelect || !opcionesDiv) {
                console.error('Elementos no encontrados para mostrar opciones específicas de HACER LLAMADA');
                return;
            }
            
            // Limpiar opciones anteriores
            opcionesSelect.innerHTML = '<option value="">Selecciona una tipificación específica</option>';
            
            // Mostrar el tercer nivel y agregar required
            opcionesDiv.style.display = 'block';
            opcionesSelect.setAttribute('required', 'required');
            
            // Definir opciones según la subcategoría
            let opciones = [];
            
            if (subcategoria === 'contacto_directo') {
                opciones = [
                    { value: '01', text: 'CANCELADA' },
                    { value: '02', text: 'MEMORANDO CNC' },
                    { value: '03', text: 'ACUERDO DE PAGO' },
                    { value: '04', text: 'PAGO TOTAL' },
                    { value: '05', text: 'YA PAGO' },
                    { value: '06', text: 'PROMESA' },
                    { value: '06.1', text: 'BANNER' },
                    { value: '06.2', text: 'REFINANCIACION' },
                    { value: '06.3', text: 'UNIFICACION' },
                    { value: '06.4', text: 'NIVELACION O NORMALIZACIO' },
                    { value: '07', text: 'REPORTE DE PAGO' },
                    { value: '08', text: 'ABONOS' },
                    { value: '09', text: 'NEGOCIACION EN TRAMITE' },
                    { value: '10', text: 'SEGUIM GESTION' },
                    { value: '11', text: 'SEGUIMIENTO' },
                    { value: '12', text: 'RENUENTE' },
                    { value: '13', text: 'VOLUNTAD DE PAGO' },
                    { value: '14', text: 'VOLVER A LLAMAR' },
                    { value: '14.1', text: 'VOLVER A LLAMAR HOY' },
                    { value: '15', text: 'LOCALIZADO' }
                ];
            } else if (subcategoria === 'contacto_tercero') {
                opciones = [
                    { value: '16', text: 'CONTACTO CON TERCERO' },
                    { value: '17', text: 'FALLECIDO' },
                    { value: '18', text: 'QUEJA / RECLAMO' }
                ];
            } else if (subcategoria === 'no_contacto') {
                opciones = [
                    { value: '19', text: 'NO CONTESTAN' },
                    { value: '19.1', text: 'TELEFONO APAGADO' },
                    { value: '20', text: 'ACTUALIZACION DATOS' },
                    { value: '21', text: 'MENSAJE' },
                    { value: '22', text: 'CORREO-E' },
                    { value: '23', text: 'LEY DE INSOLVENCIA' },
                    { value: '24', text: 'NO LOCALIZADO' },
                    { value: '25', text: 'NUMERO EQUIVOCADO' },
                    { value: '26', text: 'WHATSAPP' },
                    { value: '27', text: 'ABANDONO CHAT' }
                ];
            }
            
            // Agregar opciones al select
            opciones.forEach(opcion => {
                const option = document.createElement('option');
                option.value = opcion.value;
                option.textContent = opcion.text;
                opcionesSelect.appendChild(option);
            });
            
            // Limpiar selección anterior
            document.getElementById('sub_tipificacion_hidden').value = '';
            // Acciones específicas removidas
        }

        // Función para mostrar opciones específicas de RECIBIR LLAMADA
        function mostrarOpcionesEspecificasRecibir(subcategoria) {
            const opcionesSelect = document.getElementById('opcion_especifica_recibir');
            const opcionesDiv = document.getElementById('opciones_especificas_recibir');
            
            if (!opcionesSelect || !opcionesDiv) {
                console.error('Elementos no encontrados para mostrar opciones específicas de RECIBIR LLAMADA');
                return;
            }
            
            // Limpiar opciones anteriores
            opcionesSelect.innerHTML = '<option value="">Selecciona una tipificación específica</option>';
            
            // Mostrar el tercer nivel y agregar required
            opcionesDiv.style.display = 'block';
            opcionesSelect.setAttribute('required', 'required');
            
            // Definir opciones según la subcategoría
            let opciones = [];
            
            if (subcategoria === 'contacto_directo') {
                opciones = [
                    { value: '01', text: 'CANCELADA' },
                    { value: '02', text: 'MEMORANDO CNC' },
                    { value: '03', text: 'ACUERDO DE PAGO' },
                    { value: '04', text: 'PAGO TOTAL' },
                    { value: '05', text: 'YA PAGO' },
                    { value: '06', text: 'PROMESA' },
                    { value: '06.1', text: 'BANNER' },
                    { value: '06.2', text: 'REFINANCIACION' },
                    { value: '06.3', text: 'UNIFICACION' },
                    { value: '06.4', text: 'NIVELACION O NORMALIZACIO' },
                    { value: '07', text: 'REPORTE DE PAGO' },
                    { value: '08', text: 'ABONOS' },
                    { value: '09', text: 'NEGOCIACION EN TRAMITE' },
                    { value: '10', text: 'SEGUIM GESTION' },
                    { value: '11', text: 'SEGUIMIENTO' },
                    { value: '12', text: 'RENUENTE' },
                    { value: '13', text: 'VOLUNTAD DE PAGO' },
                    { value: '14', text: 'VOLVER A LLAMAR' },
                    { value: '14.1', text: 'VOLVER A LLAMAR HOY' },
                    { value: '15', text: 'LOCALIZADO' }
                ];
            } else if (subcategoria === 'contacto_tercero') {
                opciones = [
                    { value: '16', text: 'CONTACTO CON TERCERO' },
                    { value: '17', text: 'FALLECIDO' },
                    { value: '18', text: 'QUEJA / RECLAMO' }
                ];
            }
            
            // Agregar opciones al select
            opciones.forEach(opcion => {
                const option = document.createElement('option');
                option.value = opcion.value;
                option.textContent = opcion.text;
                opcionesSelect.appendChild(option);
            });
            
            // Limpiar selección anterior
            document.getElementById('sub_tipificacion_hidden').value = '';
            // Acciones específicas removidas
        }

        // Función para seleccionar opción específica de HACER LLAMADA
        function seleccionarOpcionEspecificaHacer(valor) {
            subTipificacionSeleccionada = valor;
            
            const subTipificacionHidden = document.getElementById('sub_tipificacion_hidden');
            if (subTipificacionHidden) {
                subTipificacionHidden.value = valor;
            }
            
            // Mostrar campos específicos para acuerdo de pago
            mostrarCamposEspecificos(valor);
        }

        // Función para seleccionar opción específica de RECIBIR LLAMADA
        function seleccionarOpcionEspecificaRecibir(valor) {
            subTipificacionSeleccionada = valor;
            
            const subTipificacionHidden = document.getElementById('sub_tipificacion_hidden');
            if (subTipificacionHidden) {
                subTipificacionHidden.value = valor;
            }
            
            // Mostrar campos específicos para acuerdo de pago
            mostrarCamposEspecificos(valor);
        }

        // Función para mostrar campos específicos según la tipificación seleccionada
        function mostrarCamposEspecificos(valor) {
            const camposAcuerdoPago = document.getElementById('campos_acuerdo_pago');
            const obligacionSeleccionada = document.getElementById('obligacion_seleccionada');
            
            // Ocultar todos los campos específicos primero
            if (camposAcuerdoPago) {
                camposAcuerdoPago.style.display = 'none';
            }
            
            // Mostrar campos específicos para acuerdo de pago (valor '03')
            if (valor === '03' && camposAcuerdoPago) {
                camposAcuerdoPago.style.display = 'block';
                
                // Hacer obligatorio el campo de obligación/producto
                if (obligacionSeleccionada) {
                    obligacionSeleccionada.setAttribute('required', 'required');
                    // Agregar validación visual
                    obligacionSeleccionada.style.borderColor = '#dc3545';
                    // Mostrar indicador de obligatorio en el label
                    const obligacionIndicator = document.getElementById('obligacion_required_indicator');
                    if (obligacionIndicator) {
                        obligacionIndicator.style.display = 'inline';
                    }
                }
                
                // Hacer obligatorio el campo de valor del acuerdo
                const valorAcuerdo = document.getElementById('valor_acuerdo');
                if (valorAcuerdo) {
                    valorAcuerdo.setAttribute('required', 'required');
                }
                
                // Hacer obligatorios los campos de acuerdo de pago
                const camposObligatorios = ['no_cuotas', 'fecha_pago', 'valor_cuota', 'numero_cuota'];
                camposObligatorios.forEach(campoId => {
                    const campo = document.getElementById(campoId);
                    if (campo) {
                        campo.setAttribute('required', 'required');
                    }
                });
            } else {
                // Remover atributo required del campo de obligación/producto
                if (obligacionSeleccionada) {
                    obligacionSeleccionada.removeAttribute('required');
                    obligacionSeleccionada.style.borderColor = '';
                    // Ocultar indicador de obligatorio en el label
                    const obligacionIndicator = document.getElementById('obligacion_required_indicator');
                    if (obligacionIndicator) {
                        obligacionIndicator.style.display = 'none';
                    }
                }
                
                // Remover atributo required del campo de valor del acuerdo
                const valorAcuerdo = document.getElementById('valor_acuerdo');
                if (valorAcuerdo) {
                    valorAcuerdo.removeAttribute('required');
                    valorAcuerdo.value = '';
                }
                
                // Remover atributo required de los campos de acuerdo de pago si no se selecciona
                const camposObligatorios = ['no_cuotas', 'fecha_pago', 'valor_cuota', 'numero_cuota'];
                camposObligatorios.forEach(campoId => {
                    const campo = document.getElementById(campoId);
                    if (campo) {
                        campo.removeAttribute('required');
                        campo.value = ''; // Limpiar valores
                    }
                });
                
                // Limpiar también los campos ocultos
                const valorCuotaHidden = document.getElementById('valor_cuota_hidden');
                if (valorCuotaHidden) {
                    valorCuotaHidden.value = '';
                }
                const valorAcuerdoHidden = document.getElementById('valor_acuerdo_hidden');
                if (valorAcuerdoHidden) {
                    valorAcuerdoHidden.value = '';
                }
            }
        }

        // Validación del formulario
        const tipificacionForm = document.getElementById('tipificacionForm');
        if (tipificacionForm) {
            tipificacionForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevenir envío normal del formulario
                
                // Remover el atributo required de todos los selects ocultos para evitar el error de validación
                const allSelects = document.querySelectorAll('select[required]');
                allSelects.forEach(select => {
                    if (select.style.display === 'none' || select.offsetParent === null) {
                        select.removeAttribute('required');
                    }
                });
                
                const formaContactoElement = document.getElementById('forma_contacto');
                const tipoGestionElement = document.getElementById('tipo_gestion');
                const subcategoriaHacerElement = document.getElementById('subcategoria_hacer');
                const subcategoriaRecibirElement = document.getElementById('subcategoria_recibir');
                const opcionEspecificaHacerElement = document.getElementById('opcion_especifica_hacer');
                const opcionEspecificaRecibirElement = document.getElementById('opcion_especifica_recibir');
                const subTipificacionHiddenElement = document.getElementById('sub_tipificacion_hidden');
                
                if (!tipoGestionElement) {
                    console.error('Elemento tipo_gestion no encontrado');
                    return;
                }
                
                const formaContacto = formaContactoElement ? formaContactoElement.value : '';
                const tipoGestion = tipoGestionElement.value;
                const subcategoriaHacer = subcategoriaHacerElement ? subcategoriaHacerElement.value : '';
                const subcategoriaRecibir = subcategoriaRecibirElement ? subcategoriaRecibirElement.value : '';
                const opcionEspecificaHacer = opcionEspecificaHacerElement ? opcionEspecificaHacerElement.value : '';
                const opcionEspecificaRecibir = opcionEspecificaRecibirElement ? opcionEspecificaRecibirElement.value : '';
                const subTipificacionHidden = subTipificacionHiddenElement ? subTipificacionHiddenElement.value : '';
                
                if (!formaContacto) {
                    alert('Por favor selecciona la forma de contacto (Llamada, WhatsApp o Email).');
                    return;
                }
                
                if (!tipoGestion) {
                    alert('Por favor selecciona el tipo de gestión (HACER LLAMADA o RECIBIR LLAMADA).');
                    return;
                }
                
                let subTipificacionSeleccionada = '';
                if (tipoGestion === 'hacer_llamada') {
                    // Para HACER LLAMADA, usar la opción específica
                    subTipificacionSeleccionada = opcionEspecificaHacer;
                } else if (tipoGestion === 'recibir_llamada') {
                    // Para RECIBIR LLAMADA, usar la opción específica
                    subTipificacionSeleccionada = opcionEspecificaRecibir;
                }
                
                // Si no hay sub-tipificación seleccionada, usar la del campo hidden
                if (!subTipificacionSeleccionada && subTipificacionHidden) {
                    subTipificacionSeleccionada = subTipificacionHidden;
                }
                
                if (!subTipificacionSeleccionada) {
                    alert('Por favor selecciona una tipificación específica.');
                    return;
                }
                
                const comentariosElement = document.getElementById('comentarios');
                const comentarios = comentariosElement ? comentariosElement.value.trim() : '';
                if (comentarios.length < 10) {
                    alert('Las observaciones deben tener al menos 10 caracteres.');
                    return;
                }
                
                // Validaciones específicas según la acción
                if (subTipificacionSeleccionada === 'INTERESADO') {
                    const edad = document.getElementById('edad')?.value;
                    const numPersonas = document.getElementById('num_personas')?.value;
                    const valorCotizacion = document.getElementById('valor_cotizacion')?.value;
                    const whatsapp = document.getElementById('whatsapp_enviado')?.value;
                    
                    if (!edad || !numPersonas || !valorCotizacion || !whatsapp) {
                        alert('Para clientes interesados, todos los campos son obligatorios: edad, número de personas, valor cotización y WhatsApp.');
                        return;
                    }
                }
                
                if (subTipificacionSeleccionada === 'VENTA INGRESADA') {
                    const edad = document.getElementById('edad')?.value;
                    const numPersonas = document.getElementById('num_personas')?.value;
                    const montoVenta = document.getElementById('monto_venta')?.value;
                    const whatsapp = document.getElementById('whatsapp_enviado')?.value;
                    
                    if (!edad || !numPersonas || !montoVenta || !whatsapp) {
                        alert('Para ventas ingresadas, todos los campos son obligatorios: edad, número de personas, valor venta y WhatsApp.');
                        return;
                    }
                }
                
                if (subTipificacionSeleccionada === 'VOLVER A LLAMAR') {
                    const fecha = document.getElementById('fecha_nueva_llamada')?.value;
                    const motivo = document.getElementById('motivo_nueva_llamada')?.value;
                    
                    if (!fecha || !motivo) {
                        alert('Para agendar nueva llamada, fecha y motivo son obligatorios.');
                        return;
                    }
                }
                
                // Validación para Acuerdo de Pago (valor '03')
                if (subTipificacionSeleccionada === '03') {
                    // Validar que se haya seleccionado una obligación/producto
                    const obligacionSeleccionada = document.getElementById('obligacion_seleccionada')?.value;
                    if (!obligacionSeleccionada || obligacionSeleccionada === 'ninguna') {
                        alert('Para acuerdos de pago, debe seleccionar una obligación/producto. No puede quedar como "Ninguna".');
                        const obligacionSelect = document.getElementById('obligacion_seleccionada');
                        if (obligacionSelect) {
                            obligacionSelect.focus();
                            obligacionSelect.style.borderColor = '#dc3545';
                        }
                        return;
                    }
                    
                    const valorAcuerdo = document.getElementById('valor_acuerdo_hidden')?.value;
                    const noCuotas = document.getElementById('no_cuotas')?.value;
                    const fechaPago = document.getElementById('fecha_pago')?.value;
                    const valorCuota = document.getElementById('valor_cuota_hidden')?.value;
                    const numeroCuota = document.getElementById('numero_cuota')?.value;
                    
                    if (!valorAcuerdo) {
                        alert('Para acuerdos de pago, el valor del acuerdo es obligatorio.');
                        return;
                    }
                    
                    if (!noCuotas || !fechaPago || !valorCuota || !numeroCuota) {
                        alert('Para acuerdos de pago, todos los campos son obligatorios: número de cuotas, fecha de pago, valor de cuota y número de cuota.');
                        return;
                    }
                }
                
                // Si pasa todas las validaciones, llamar a la función de guardar
                guardarTipificacion();
            });
        }

         
         
         // Mostrar información del cliente por defecto
         document.addEventListener('DOMContentLoaded', function() {
             // La información del cliente se muestra por defecto
             
             // Inicializar gestión de obligaciones
             inicializarGestionObligaciones();
             
             
             // Verificar si se acaba de guardar una gestión
             const urlParams = new URLSearchParams(window.location.search);
             if (urlParams.get('gestion_guardada') === '1') {
                 // Mostrar botones de navegación si es necesario
                 mostrarBotonesNavegacion();
             }
             
             // Limpiar formulario al cargar la página
             document.getElementById('tipificacionForm').reset();
             
            // Ocultar tipificaciones específicas al inicio y remover required
            const subcategoriaHacer = document.getElementById('subcategoria_hacer_llamada');
            const subcategoriaRecibir = document.getElementById('subcategoria_recibir_llamada');
            const opcionesHacer = document.getElementById('opciones_especificas_hacer');
            const opcionesRecibir = document.getElementById('opciones_especificas_recibir');
            
            const subcategoriaHacerSelect = document.getElementById('subcategoria_hacer');
            const subcategoriaRecibirSelect = document.getElementById('subcategoria_recibir');
            const opcionEspecificaHacer = document.getElementById('opcion_especifica_hacer');
            const opcionEspecificaRecibir = document.getElementById('opcion_especifica_recibir');
            
            if (subcategoriaHacer) {
                subcategoriaHacer.style.display = 'none';
                if (subcategoriaHacerSelect) subcategoriaHacerSelect.removeAttribute('required');
            }
            if (subcategoriaRecibir) {
                subcategoriaRecibir.style.display = 'none';
                if (subcategoriaRecibirSelect) subcategoriaRecibirSelect.removeAttribute('required');
            }
            if (opcionesHacer) {
                opcionesHacer.style.display = 'none';
                if (opcionEspecificaHacer) opcionEspecificaHacer.removeAttribute('required');
            }
            if (opcionesRecibir) {
                opcionesRecibir.style.display = 'none';
                if (opcionEspecificaRecibir) opcionEspecificaRecibir.removeAttribute('required');
            }
         });
         
         
         
        // Función para ir al siguiente cliente
        function irAlSiguienteCliente() {
            console.log('Obteniendo siguiente cliente...');
            
            // Mostrar loading en el botón
            const btnSiguiente = document.getElementById('btnSiguienteCliente');
            const textoOriginal = btnSiguiente.innerHTML;
            btnSiguiente.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
            btnSiguiente.disabled = true;
            
            // Obtener el siguiente cliente de la lista
            fetch('index.php?action=obtener_siguiente_cliente', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta del servidor:', data);
                
                if (data.success && data.siguiente_cliente) {
                    // Recargar la página con el siguiente cliente
                    console.log('Redirigiendo al siguiente cliente:', data.siguiente_cliente.nombre);
                    window.location.href = `index.php?action=gestionar_cliente&id=${data.siguiente_cliente.id}`;
                } else {
                    alert('No hay más clientes en tu lista. ¡Excelente trabajo!');
                    window.location.href = 'index.php?action=mis_tareas';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al obtener el siguiente cliente: ' + error.message);
                
                // Restaurar botón
                btnSiguiente.innerHTML = textoOriginal;
                btnSiguiente.disabled = false;
            });
        }
         
         
        
         

         
         // Función para formatear pesos colombianos
         function formatearPesos(input) {
             // Remover todos los caracteres no numéricos
             let valor = input.value.replace(/\D/g, '');
             
             // Si no hay valor, limpiar el campo
             if (!valor) {
                 input.value = '';
                 // Limpiar también el campo oculto si existe
                 const hiddenField = document.getElementById('valor_cuota_hidden');
                 if (hiddenField) {
                     hiddenField.value = '';
                 }
                 return;
             }
             
             // Convertir a número y formatear
             let numero = parseInt(valor);
             if (isNaN(numero)) {
                 input.value = '';
                 // Limpiar también el campo oculto si existe
                 const hiddenField = document.getElementById('valor_cuota_hidden');
                 if (hiddenField) {
                     hiddenField.value = '';
                 }
                 return;
             }
             
             // Formatear con separadores de miles
             let formateado = numero.toLocaleString('es-CO');
             input.value = formateado;
             
             // Actualizar el campo oculto con el valor numérico
             const hiddenField = document.getElementById('valor_cuota_hidden');
             if (hiddenField) {
                 hiddenField.value = numero;
             }
         }
         
         // Función para formatear pesos colombianos del valor del acuerdo
         function formatearPesosAcuerdo(input) {
             // Remover todos los caracteres no numéricos
             let valor = input.value.replace(/\D/g, '');
             
             // Si no hay valor, limpiar el campo
             if (!valor) {
                 input.value = '';
                 // Limpiar también el campo oculto si existe
                 const hiddenField = document.getElementById('valor_acuerdo_hidden');
                 if (hiddenField) {
                     hiddenField.value = '';
                 }
                 return;
             }
             
             // Convertir a número y formatear
             let numero = parseInt(valor);
             if (isNaN(numero)) {
                 input.value = '';
                 // Limpiar también el campo oculto si existe
                 const hiddenField = document.getElementById('valor_acuerdo_hidden');
                 if (hiddenField) {
                     hiddenField.value = '';
                 }
                 return;
             }
             
             // Formatear con separadores de miles
             let formateado = numero.toLocaleString('es-CO');
             input.value = formateado;
             
             // Actualizar el campo oculto con el valor numérico
             const hiddenField = document.getElementById('valor_acuerdo_hidden');
             if (hiddenField) {
                 hiddenField.value = numero;
             }
         }
         
         // Función para guardar tipificación

        function guardarTipificacion() {
            const formData = new FormData(document.getElementById('tipificacionForm'));
             
            // Obtener valores de los nuevos dropdowns
            const formaContactoElement = document.getElementById('forma_contacto');
            const tipoGestionElement = document.getElementById('tipo_gestion');
            
            // Agregar información de la obligación seleccionada si existe
            if (obligacionActual) {
                formData.append('obligacion_id', obligacionActual.id);
                formData.append('producto_gestionado', obligacionActual.producto);
                formData.append('monto_obligacion', obligacionActual.monto);
                formData.append('numero_obligacion', obligacionActual.obligacion);
            }
            const subcategoriaHacerElement = document.getElementById('subcategoria_hacer');
            const subcategoriaRecibirElement = document.getElementById('subcategoria_recibir');
            const opcionEspecificaHacerElement = document.getElementById('opcion_especifica_hacer');
            const opcionEspecificaRecibirElement = document.getElementById('opcion_especifica_recibir');
            const subTipificacionHiddenElement = document.getElementById('sub_tipificacion_hidden');
            // Obtener canales autorizados seleccionados
            const canalesCheckboxes = document.querySelectorAll('input[name="canales_autorizados[]"]:checked');
            const canalesAutorizados = Array.from(canalesCheckboxes).map(checkbox => checkbox.value);
            
            const formaContacto = formaContactoElement ? formaContactoElement.value : '';
            const tipoGestion = tipoGestionElement ? tipoGestionElement.value : '';
            const subcategoriaHacer = subcategoriaHacerElement ? subcategoriaHacerElement.value : '';
            const subcategoriaRecibir = subcategoriaRecibirElement ? subcategoriaRecibirElement.value : '';
            const opcionEspecificaHacer = opcionEspecificaHacerElement ? opcionEspecificaHacerElement.value : '';
            const opcionEspecificaRecibir = opcionEspecificaRecibirElement ? opcionEspecificaRecibirElement.value : '';
            const subTipificacionHidden = subTipificacionHiddenElement ? subTipificacionHiddenElement.value : '';
            const comentarios = formData.get('comentarios');
             
             let subTipificacionSeleccionada = '';
             if (tipoGestion === 'hacer_llamada') {
                 // Para HACER LLAMADA, usar la opción específica
                 subTipificacionSeleccionada = opcionEspecificaHacer;
             } else if (tipoGestion === 'recibir_llamada') {
                 // Para RECIBIR LLAMADA, usar la opción específica
                 subTipificacionSeleccionada = opcionEspecificaRecibir;
             }
             
             // Si no hay sub-tipificación seleccionada, usar la del campo hidden
             if (!subTipificacionSeleccionada && subTipificacionHidden) {
                 subTipificacionSeleccionada = subTipificacionHidden;
             }
             
             
             // Validar campos obligatorios
             if (!tipoGestion) {
                 alert('Por favor selecciona el tipo de gestión (HACER LLAMADA o RECIBIR LLAMADA).');
                 return;
             }
             
             if (!subTipificacionSeleccionada) {
                 alert('Por favor selecciona una tipificación específica.');
                 return;
             }
             
             if (!comentarios || comentarios.trim() === '') {
                 alert('Por favor agrega comentarios sobre la gestión.');
                 return;
             }
             
             // Validación adicional para Acuerdo de Pago: debe seleccionar una obligación
             if (subTipificacionSeleccionada === '03') {
                 const obligacionSeleccionada = document.getElementById('obligacion_seleccionada')?.value;
                 if (!obligacionSeleccionada || obligacionSeleccionada === 'ninguna') {
                     alert('Para acuerdos de pago, debe seleccionar una obligación/producto. No puede quedar como "Ninguna".');
                     const obligacionSelect = document.getElementById('obligacion_seleccionada');
                     if (obligacionSelect) {
                         obligacionSelect.focus();
                         obligacionSelect.style.borderColor = '#dc3545';
                         // Restaurar el color después de 3 segundos
                         setTimeout(() => {
                             if (obligacionSelect.style.borderColor === '#dc3545') {
                                 obligacionSelect.style.borderColor = '';
                             }
                         }, 3000);
                     }
                     return;
                 }
             }
             
            // Agregar valores al FormData
            formData.set('forma_contacto', formaContacto);
            formData.set('tipificacion', tipoGestion);
            formData.set('sub_tipificacion', subTipificacionSeleccionada);
            
            // Agregar canales autorizados correctamente como array
            // Eliminar cualquier entrada previa de canales
            formData.delete('canales_autorizados');
            
            // Agregar cada canal individualmente (FormData manejará el array)
            canalesAutorizados.forEach(canal => {
                formData.append('canales_autorizados[]', canal);
            });
             
             // Agregar campos opcionales de información de pago si existen
             const fechaPagoEsperada = document.getElementById('fecha_pago_esperada');
             const montoPendiente = document.getElementById('monto_pendiente');
             const detallesPago = document.getElementById('detalles_pago');
             
             if (fechaPagoEsperada && fechaPagoEsperada.value) {
                 formData.set('fecha_pago_esperada', fechaPagoEsperada.value);
             }
             
             if (montoPendiente && montoPendiente.value) {
                 formData.set('monto_pendiente', montoPendiente.value);
             }
             
             if (detallesPago && detallesPago.value) {
                 formData.set('detalles_pago', detallesPago.value);
             }
             
             // Agregar campos específicos de acuerdo de pago si existen
             const noCuotas = document.getElementById('no_cuotas');
             const fechaPago = document.getElementById('fecha_pago');
             const valorCuotaHidden = document.getElementById('valor_cuota_hidden');
             const numeroCuota = document.getElementById('numero_cuota');
             const valorAcuerdo = document.getElementById('valor_acuerdo_hidden');
             
             if (noCuotas && noCuotas.value) {
                 formData.set('no_cuotas', noCuotas.value);
             }
             
             if (fechaPago && fechaPago.value) {
                 formData.set('fecha_pago', fechaPago.value);
             }
             
             if (valorCuotaHidden && valorCuotaHidden.value) {
                 formData.set('valor_cuota', valorCuotaHidden.value);
             }
             
             if (numeroCuota && numeroCuota.value) {
                 formData.set('numero_cuota', numeroCuota.value);
             }
             
             if (valorAcuerdo && valorAcuerdo.value) {
                 formData.set('valor_acuerdo', valorAcuerdo.value);
             }
             
             // Agregar campos opcionales de programar llamada si existen
             const fechaNuevaLlamada = document.getElementById('fecha_nueva_llamada');
             const motivoNuevaLlamada = document.getElementById('motivo_nueva_llamada');
             
             if (fechaNuevaLlamada && fechaNuevaLlamada.value) {
                 formData.set('fecha_nueva_llamada', fechaNuevaLlamada.value);
             }
             
             if (motivoNuevaLlamada && motivoNuevaLlamada.value) {
                 formData.set('motivo_nueva_llamada', motivoNuevaLlamada.value);
             }
             
            // Enviar formulario
            fetch('index.php?action=guardar_tipificacion', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                // Verificar si la respuesta es realmente JSON
                const contentType = response.headers.get('content-type');
                
                if (!contentType || !contentType.includes('application/json')) {
                    // Si no es JSON, intentar leer como texto para diagnosticar
                    return response.text().then(text => {
                        console.error('❌ Servidor devolvió contenido no-JSON:');
                        console.error('Content-Type:', contentType);
                        console.error('Primeros 500 caracteres:', text.substring(0, 500));
                        throw new Error('El servidor devolvió HTML en lugar de JSON. Revisa la consola del navegador.');
                    });
                }
                
                // Si es JSON, parsear
                return response.json().then(data => ({response, data}));
            })
            .then(result => {
                const data = result.data;
                console.log('✅ Respuesta del servidor:', data);
                
                if (data.success) {
                    alert('✅ Tipificación guardada exitosamente');
                    
                    // Mostrar botones de navegación después del guardado
                    console.log('Mostrando botones de navegación...');
                    debugEstadoSistema();
                    mostrarBotonesNavegacion();
                    
                    // Limpiar formulario
                    document.getElementById('tipificacionForm').reset();
                    
                    // Limpiar selecciones de tipificación
                    tipificacionSeleccionada = null;
                    subTipificacionSeleccionada = null;
                } else {
                    alert('❌ Error: ' + (data.message || 'No se pudo guardar la tipificación'));
                }
            })
            .catch(error => {
                console.error('❌ Error en fetch:', error);
                alert('❌ Error al guardar la tipificación: ' + error.message);
            });
         }
         
         // Función para resetear el formulario para un nuevo cliente
        function resetearFormularioParaNuevoCliente() {

             // Resetear el formulario de tipificación
             const tipificacionForm = document.getElementById('tipificacionForm');
             if (tipificacionForm) {
                 tipificacionForm.reset();
             }

             // Limpiar selecciones de tipificación
             tipificacionSeleccionada = null;
             subTipificacionSeleccionada = null;

            // Ocultar tipificaciones específicas y remover required
            const subcategoriaHacer = document.getElementById('subcategoria_hacer_llamada');
            const subcategoriaRecibir = document.getElementById('subcategoria_recibir_llamada');
            const opcionesHacer = document.getElementById('opciones_especificas_hacer');
            const opcionesRecibir = document.getElementById('opciones_especificas_recibir');
            
            const subcategoriaHacerSelect = document.getElementById('subcategoria_hacer');
            const subcategoriaRecibirSelect = document.getElementById('subcategoria_recibir');
            const opcionEspecificaHacer = document.getElementById('opcion_especifica_hacer');
            const opcionEspecificaRecibir = document.getElementById('opcion_especifica_recibir');
            
            if (subcategoriaHacer) {
                subcategoriaHacer.style.display = 'none';
                if (subcategoriaHacerSelect) subcategoriaHacerSelect.removeAttribute('required');
            }
            if (subcategoriaRecibir) {
                subcategoriaRecibir.style.display = 'none';
                if (subcategoriaRecibirSelect) subcategoriaRecibirSelect.removeAttribute('required');
            }
            if (opcionesHacer) {
                opcionesHacer.style.display = 'none';
                if (opcionEspecificaHacer) opcionEspecificaHacer.removeAttribute('required');
            }
            if (opcionesRecibir) {
                opcionesRecibir.style.display = 'none';
                if (opcionEspecificaRecibir) opcionEspecificaRecibir.removeAttribute('required');
            }

             // Acciones específicas removidas

             // Acciones específicas removidas

            // Mostrar botones principales (guardar, etc.)
            const botonesPrincipales = document.querySelector('.btn-container');
            if (botonesPrincipales) {
                botonesPrincipales.style.display = 'flex';
            }

            // Ocultar botones de siguiente cliente y búsqueda
            const botonSiguiente = document.getElementById('btnSiguienteCliente');
            if (botonSiguiente) {
                botonSiguiente.style.display = 'none';
            }

             // Limpiar campos de acciones específicas si existen
             const camposAccion = document.querySelectorAll('#accionContenido input, #accionContenido select, #accionContenido textarea');
             camposAccion.forEach(campo => {
                 if (campo && campo.type) {
                     if (campo.type === 'text' || campo.type === 'email' || campo.type === 'tel' || campo.type === 'number') {
                         campo.value = '';
                     } else if (campo.type === 'select-one') {
                         campo.selectedIndex = 0;
                     }
                 }
             });

         }

        
        // Funciones para el modal de detalles de gestión
        function mostrarDetallesGestion(gestionId) {
            
            // Mostrar loading
            const modal = document.getElementById('modalDetallesGestion');
            const contenido = document.getElementById('contenidoDetalles');
            contenido.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Cargando detalles...</div>';
            modal.style.display = 'flex';
            
            // Obtener detalles de la gestión
            fetch(`index.php?action=obtener_detalles_gestion&id=${gestionId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarContenidoDetalles(data.gestion);
                } else {
                    contenido.innerHTML = '<div class="alert alert-danger">Error al cargar los detalles: ' + (data.message || 'Error desconocido') + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                contenido.innerHTML = '<div class="alert alert-danger">Error al cargar los detalles: ' + error.message + '</div>';
            });
        }
        
        function mostrarContenidoDetalles(gestion) {
            const contenido = document.getElementById('contenidoDetalles');
            
            // Crear badges para canales autorizados usando el mapeo centralizado
            let canalesHTML = '';
            if (gestion.canales_autorizados && gestion.canales_autorizados.length > 0) {
                canalesHTML = '<div class="canales-seleccionados">';
                gestion.canales_autorizados.forEach(canal => {
                    canalesHTML += `<span class="canal-badge">${getCanalTexto(canal)}</span>`;
                });
                canalesHTML += '</div>';
            } else {
                canalesHTML = '<span style="color: #6c757d; font-style: italic;">No se seleccionaron canales autorizados</span>';
            }
            
            // Construir el árbol de tipificación completo
            let arbolTipificacion = '';
            if (gestion.tipo_gestion && gestion.resultado) {
                arbolTipificacion = `
                    <div class="arbol-tipificacion" style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff; margin: 10px 0;">
                        <h5 style="color: #495057; margin-bottom: 10px; font-size: 1rem;">
                            <i class="fas fa-sitemap"></i> Árbol de Tipificación
                        </h5>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="background: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 500;">
                                    1. ${gestion.tipo_gestion || 'Tipo de Gestión'}
                                </span>
                                <i class="fas fa-arrow-right" style="color: #6c757d;"></i>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-left: 20px;">
                                <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 500;">
                                    2. ${gestion.subcategoria || 'Tipo de Contacto'}
                                </span>
                                <i class="fas fa-arrow-right" style="color: #6c757d;"></i>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-left: 40px;">
                                <span style="background: #ffc107; color: #212529; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 500;">
                                    3. ${gestion.resultado || 'Resultado Específico'}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            contenido.innerHTML = `
                <div class="detalles-gestion">
                    <!-- Información General -->
                    <div class="detalle-seccion">
                        <h4><i class="fas fa-info-circle"></i> Información General</h4>
                        <div class="detalle-grid">
                            <div class="detalle-item">
                                <span class="detalle-label">📅 Fecha de Gestión</span>
                                <span class="detalle-valor">${gestion.fecha_gestion}</span>
                            </div>
                            <div class="detalle-item">
                                <span class="detalle-label">📞 Canal de Contacto</span>
                                <span class="detalle-valor">${getCanalTexto(gestion.forma_contacto) || 'No especificado'}</span>
                            </div>
                            <div class="detalle-item">
                                <span class="detalle-label">👤 Asesor Responsable</span>
                                <span class="detalle-valor">${gestion.asesor_nombre || 'No especificado'}</span>
                            </div>
                            ${gestion.producto_gestionado ? `
                            <div class="detalle-item">
                                <span class="detalle-label">📦 Obligación a Gestionar</span>
                                <span class="detalle-valor">${gestion.producto_gestionado} (${gestion.numero_obligacion || 'N/A'})</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <!-- Árbol de Tipificación -->
                    ${arbolTipificacion}
                    
                    <!-- Canales Autorizados -->
                    <div class="detalle-seccion">
                        <h4><i class="fas fa-broadcast-tower"></i> Canales Autorizados</h4>
                        ${gestion.canales_autorizados && gestion.canales_autorizados.length > 0 ? 
                            '<div class="canales-seleccionados">' + 
                            gestion.canales_autorizados.map(canal => 
                                '<span class="canal-badge">' + getCanalTexto(canal) + '</span>'
                            ).join('') + 
                            '</div>' : 
                            '<span style="color: #6c757d; font-style: italic;">No se seleccionaron canales autorizados</span>'
                        }
                    </div>
                    
                    <!-- Detalles de la Gestión -->
                    ${(() => {
                        // Verificar si hay información básica de gestión
                        const tieneInfoBasica = gestion.edad_cliente || gestion.num_personas || gestion.valor_cotizacion || gestion.whatsapp_enviado || gestion.monto_venta || gestion.duracion_llamada;
                        
                        // Verificar si hay información de acuerdo de pago (ignorar fechas inválidas y valores en cero)
                        const tieneInfoAcuerdoPago = (gestion.no_cuotas && gestion.no_cuotas > 0) || 
                                                   (gestion.fecha_pago && gestion.fecha_pago !== '0000-00-00' && gestion.fecha_pago !== '') || 
                                                   (gestion.valor_cuota && gestion.valor_cuota > 0) || 
                                                   (gestion.numero_cuota && gestion.numero_cuota > 0) ||
                                                   (gestion.valor_acuerdo && gestion.valor_acuerdo > 0);
                        
                        if (!tieneInfoBasica && !tieneInfoAcuerdoPago) {
                            return '';
                        }
                        
                        let html = `
                        <div class="detalle-seccion">
                            <h4><i class="fas fa-chart-bar"></i> Detalles de la Gestión</h4>
                            <div class="detalle-grid">
                        `;
                        
                        // Información básica de gestión
                        if (gestion.edad_cliente) {
                            html += `
                            <div class="detalle-item">
                                <span class="detalle-label">👤 Edad del Cliente</span>
                                <span class="detalle-valor">${gestion.edad_cliente} años</span>
                            </div>
                            `;
                        }
                        
                        if (gestion.num_personas) {
                            html += `
                            <div class="detalle-item">
                                <span class="detalle-label">👥 Personas a Cubrir</span>
                                <span class="detalle-valor">${gestion.num_personas}</span>
                            </div>
                            `;
                        }
                        
                        if (gestion.valor_cotizacion) {
                            html += `
                            <div class="detalle-item">
                                <span class="detalle-label">💰 Valor de Cotización</span>
                                <span class="detalle-valor">$${parseInt(gestion.valor_cotizacion).toLocaleString()}</span>
                            </div>
                            `;
                        }
                        
                        if (gestion.whatsapp_enviado) {
                            html += `
                            <div class="detalle-item">
                                <span class="detalle-label">📱 WhatsApp</span>
                                <span class="detalle-valor">${gestion.whatsapp_enviado}</span>
                            </div>
                            `;
                        }
                        
                        if (gestion.monto_venta) {
                            html += `
                            <div class="detalle-item">
                                <span class="detalle-label">💵 Monto de Venta</span>
                                <span class="detalle-valor">$${parseInt(gestion.monto_venta).toLocaleString()}</span>
                            </div>
                            `;
                        }
                        
                        if (gestion.duracion_llamada) {
                            html += `
                            <div class="detalle-item">
                                <span class="detalle-label">⏱️ Duración</span>
                                <span class="detalle-valor">${gestion.duracion_llamada} min</span>
                            </div>
                            `;
                        }
                        
                        // Campos específicos de acuerdo de pago - solo mostrar si hay información de cuotas
                        if (tieneInfoAcuerdoPago) {
                            html += `
                            <div class="detalle-item" style="grid-column: 1 / -1; margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                                <span class="detalle-label" style="font-weight: 600; color: #059669;">💳 Información de Acuerdo de Pago:</span>
                            </div>
                            `;
                            
                            if (gestion.no_cuotas) {
                                html += `
                                <div class="detalle-item">
                                    <span class="detalle-label">📊 Total Cuotas</span>
                                    <span class="detalle-valor">${gestion.no_cuotas}</span>
                                </div>
                                `;
                            }
                            
                            if (gestion.fecha_pago) {
                                html += `
                                <div class="detalle-item">
                                    <span class="detalle-label">📅 Fecha Pago</span>
                                    <span class="detalle-valor">${new Date(gestion.fecha_pago).toLocaleDateString('es-ES')}</span>
                                </div>
                                `;
                            }
                            
                            if (gestion.valor_cuota) {
                                html += `
                                <div class="detalle-item">
                                    <span class="detalle-label">💰 Valor Cuota</span>
                                    <span class="detalle-valor">$${parseFloat(gestion.valor_cuota).toLocaleString('es-CO')}</span>
                                </div>
                                `;
                            }
                            
                            if (gestion.numero_cuota) {
                                html += `
                                <div class="detalle-item">
                                    <span class="detalle-label">🔢 Número Cuota</span>
                                    <span class="detalle-valor">${gestion.numero_cuota}</span>
                                </div>
                                `;
                            }
                            
                            if (gestion.valor_acuerdo) {
                                html += `
                                <div class="detalle-item">
                                    <span class="detalle-label">💰 Valor del Acuerdo</span>
                                    <span class="detalle-valor">$${parseFloat(gestion.valor_acuerdo).toLocaleString('es-CO')}</span>
                                </div>
                                `;
                            }
                        }
                        
                        html += `
                            </div>
                        </div>
                        `;
                        
                        return html;
                    })()}
                    
                    <!-- Próxima Acción -->
                    ${gestion.proxima_accion || gestion.proxima_fecha ? `
                    <div class="detalle-seccion">
                        <h4><i class="fas fa-calendar-alt"></i> Próxima Acción</h4>
                        <div class="detalle-grid">
                            ${gestion.proxima_accion ? `
                            <div class="detalle-item">
                                <span class="detalle-label">📋 Acción Programada</span>
                                <span class="detalle-valor">${gestion.proxima_accion}</span>
                            </div>
                            ` : ''}
                            ${gestion.proxima_fecha ? `
                            <div class="detalle-item">
                                <span class="detalle-label">📅 Fecha Programada</span>
                                <span class="detalle-valor">${gestion.proxima_fecha}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    ` : ''}
                    
                    <!-- Observaciones -->
                    <div class="detalle-seccion">
                        <h4><i class="fas fa-comments"></i> Observaciones</h4>
                        <div class="comentarios-detalle">${gestion.comentarios || 'No hay observaciones registradas'}</div>
                    </div>
                </div>
            `;
        }
        
        function cerrarModalDetalles() {
            const modal = document.getElementById('modalDetallesGestion');
            modal.style.display = 'none';
        }
        
        // Función para abrir el aplicativo de agentes
        function abrirAplicativoAgentes(event) {
            // Prevenir el comportamiento por defecto del enlace
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            try {
                // Abrir en nueva pestaña de forma segura
                const url = 'https://estaqueue.udpsa.com/loginMarcador.html';
                const nuevaVentana = window.open(url, '_blank', 'noopener,noreferrer');
                
                // Verificar si la ventana se abrió correctamente
                if (nuevaVentana) {
                    nuevaVentana.focus();
                } else {
                    // Si el popup fue bloqueado, mostrar mensaje
                    alert('Por favor, permite ventanas emergentes para este sitio y vuelve a intentar.');
                }
            } catch (error) {
                console.error('❌ Error al abrir el aplicativo de agentes:', error);
                alert('Error al abrir el aplicativo de agentes. Por favor, intenta nuevamente.');
            }
            
            // Retornar false para prevenir cualquier comportamiento adicional
            return false;
        }
         
        // Función para Click to Call (usando el nuevo sistema)
        // La función global llamarCliente se define en click_to_call.js
        
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
        
        // Reemplazar la función original
        window.llamarCliente = llamarDesdeVentanaAnclada;
        
        // Funciones para búsqueda de clientes
        function mostrarModalBusqueda() {
            const modal = document.getElementById('modalBusquedaCliente');
            const form = document.getElementById('formBusquedaCliente');
            const resultados = document.getElementById('resultadosBusqueda');
            
            if (modal) {
                modal.style.display = 'flex';
            }
            
            // Limpiar formulario
            if (form) {
                form.reset();
            }
            
            if (resultados) {
                resultados.style.display = 'none';
            }
        }
        
        function cerrarModalBusqueda() {
            const modal = document.getElementById('modalBusquedaCliente');
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
        function buscarCliente(event) {
            event.preventDefault();
            
            const tipoBusqueda = document.getElementById('tipo_busqueda').value;
            const terminoBusqueda = document.getElementById('termino_busqueda').value.trim();
            
            if (!tipoBusqueda || !terminoBusqueda) {
                alert('Por favor completa todos los campos');
                return;
            }
            
            // Mostrar loading
            const resultadosDiv = document.getElementById('resultadosBusqueda');
            const listaResultados = document.getElementById('listaResultados');
            
            if (resultadosDiv) {
                resultadosDiv.style.display = 'block';
            }
            
            if (listaResultados) {
                listaResultados.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
            }
            
            // Realizar búsqueda
            fetch('index.php?action=buscar_cliente', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    tipo: tipoBusqueda,
                    termino: terminoBusqueda
                }),
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarResultadosBusqueda(data.clientes);
                } else {
                    mostrarErrorBusqueda(data.message || 'Error en la búsqueda');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarErrorBusqueda('Error de conexión');
            });
        }
        
        function mostrarResultadosBusqueda(clientes) {
            const listaResultados = document.getElementById('listaResultados');
            
            if (clientes.length === 0) {
                listaResultados.innerHTML = '<div class="sin-resultados">No se encontraron clientes con esos criterios</div>';
                return;
            }
            
            let html = '';
            
            // Agregar indicador de cantidad de resultados
            if (clientes.length >= 20) {
                html += `<div class="info-resultados">
                    <i class="fas fa-info-circle"></i> 
                    Se encontraron ${clientes.length} clientes. Mostrando los primeros 20 resultados.
                    <br><small><i class="fas fa-mouse"></i> Usa la rueda del mouse o la barra de desplazamiento para ver todos los resultados</small>
                </div>`;
            } else {
                html += `<div class="info-resultados">
                    <i class="fas fa-check-circle"></i> 
                    Se encontraron ${clientes.length} cliente${clientes.length > 1 ? 's' : ''}.
                </div>`;
            }
            
            clientes.forEach((cliente, index) => {
                html += `
                    <div class="resultado-cliente" onclick="seleccionarCliente(${cliente.id})">
                        <div class="resultado-header">
                            <h5>${cliente.nombre || 'Sin nombre'}</h5>
                            <span class="resultado-numero">#${index + 1}</span>
                        </div>
                        <div class="cliente-info">
                            <span><i class="fas fa-phone"></i> ${cliente.telefono || 'Sin teléfono'}</span>
                            <span><i class="fas fa-id-card"></i> ${cliente.cedula || 'Sin cédula'}</span>
                            <span><i class="fas fa-envelope"></i> ${cliente.email || 'Sin email'}</span>
                            <span><i class="fas fa-map-marker-alt"></i> ${cliente.direccion || 'Sin dirección'}</span>
                        </div>
                        <div class="cliente-acciones">
                            <button class="btn-seleccionar-cliente" onclick="event.stopPropagation(); seleccionarCliente(${cliente.id})">
                                <i class="fas fa-check"></i> Seleccionar
                            </button>
                        </div>
                    </div>
                `;
            });
            
            listaResultados.innerHTML = html;
            
            // Forzar el scroll después de mostrar los resultados
            setTimeout(() => {
                const modalBody = document.querySelector('.modal-body-scrollable');
                if (modalBody) {
                    // Verificar si hay scroll disponible
                    if (modalBody.scrollHeight > modalBody.clientHeight) {
                        // Agregar indicador visual de scroll
                        if (modalBody) {
                            modalBody.style.borderRight = '3px solid #007bff';
                        }
                    }
                }
            }, 100);
        }
        
        function mostrarErrorBusqueda(mensaje) {
            const listaResultados = document.getElementById('listaResultados');
            listaResultados.innerHTML = `<div class="sin-resultados">❌ ${mensaje}</div>`;
        }
        
        function seleccionarCliente(clienteId) {
            // Cerrar el modal de búsqueda
            cerrarModalBusqueda();
            
            // Recargar la página con el cliente seleccionado
            window.location.href = `index.php?action=gestionar_cliente&id=${clienteId}`;
        }

        // ===== FUNCIONES PARA GESTIÓN DE OBLIGACIONES/PRODUCTOS =====
        
        let obligacionesDisponibles = [];
        let obligacionActual = null;
        let obligacionesGestionadas = [];
        // Bloquea reconfiguración de botones una vez que se muestran los de navegación
        let uiBloqueoNavegacion = false;

        // Utilidad de depuración para rastrear visibilidad de botones claves
        function debugVisibilidad(id, visible) {
            try {
                const ts = new Date().toLocaleTimeString();
                console.log(`[UI][${ts}] ${id} => ${visible ? 'show' : 'hide'}`);
            } catch (_) {}
        }
        
        // Función de depuración para verificar el estado del sistema
        function debugEstadoSistema() {
            console.log('=== ESTADO DEL SISTEMA ===');
            console.log('tieneTareasPendientes:', tieneTareasPendientes);
            console.log('uiBloqueoNavegacion:', uiBloqueoNavegacion);
            console.log('obligacionActual:', obligacionActual);
            console.log('obligacionesGestionadas:', obligacionesGestionadas);
            
            const btnNavegacion = document.getElementById('btnNavegacion');
            const btnSiguienteCliente = document.getElementById('btnSiguienteCliente');
            const btnBuscarCliente = document.getElementById('btnBuscarCliente');
            const btnGuardarPrincipal = document.getElementById('btnGuardarPrincipal');
            
            console.log('=== ESTADO DE BOTONES ===');
            console.log('btnNavegacion display:', btnNavegacion ? btnNavegacion.style.display : 'NO ENCONTRADO');
            console.log('btnSiguienteCliente display:', btnSiguienteCliente ? btnSiguienteCliente.style.display : 'NO ENCONTRADO');
            console.log('btnBuscarCliente display:', btnBuscarCliente ? btnBuscarCliente.style.display : 'NO ENCONTRADO');
            console.log('btnGuardarPrincipal display:', btnGuardarPrincipal ? btnGuardarPrincipal.style.display : 'NO ENCONTRADO');
            console.log('========================');
        }
        let tieneTareasPendientes = <?php echo json_encode($tieneTareasPendientes ?? false); ?>;
        
        
        // Inicializar gestión de obligaciones
        function inicializarGestionObligaciones() {
            // Obtener obligaciones disponibles del dropdown
            const selectObligaciones = document.getElementById('obligacion_seleccionada');
            obligacionesDisponibles = [];
            
            for (let i = 1; i < selectObligaciones.options.length; i++) {
                const option = selectObligaciones.options[i];
                obligacionesDisponibles.push({
                    id: option.value,
                    producto: option.dataset.producto,
                    monto: parseFloat(option.dataset.monto),
                    obligacion: option.dataset.obligacion
                });
            }
            
            // Configurar botones
            configurarBotonesSegunObligaciones();
        }
        
        // Manejar selección de obligación
        function manejarSeleccionObligacion() {
            const selectObligaciones = document.getElementById('obligacion_seleccionada');
            const valorSeleccionado = selectObligaciones.value;
            
            if (valorSeleccionado === 'ninguna') {
                obligacionActual = null;
                configurarBotonesSegunObligaciones();
                
                // Si el campo es requerido (acuerdo de pago), mantener el borde rojo
                if (selectObligaciones.hasAttribute('required')) {
                    selectObligaciones.style.borderColor = '#dc3545';
                } else {
                    selectObligaciones.style.borderColor = '';
                }
            } else {
                // Encontrar la obligación seleccionada
                obligacionActual = obligacionesDisponibles.find(obl => obl.id === valorSeleccionado);
                configurarBotonesSegunObligaciones();
                
                // Quitar el borde rojo si se seleccionó una obligación válida
                selectObligaciones.style.borderColor = '';
            }
        }
        
        // Configurar botones según el estado de las obligaciones
        function configurarBotonesSegunObligaciones() {
            // Si ya mostramos los botones de navegación, no volver a ocultarlos ni reconfigurar
            if (uiBloqueoNavegacion) {
                debugVisibilidad('configurarBotonesSegunObligaciones:bloqueado', true);
                return;
            }
            const btnGuardarPrincipal = document.getElementById('btnGuardarPrincipal');
            const btnNavegacion = document.getElementById('btnNavegacion');
            
            // Ocultar botones de navegación inicialmente
            btnNavegacion.style.display = 'none'; debugVisibilidad('btnNavegacion', false);
            
            // Mostrar botón principal
            btnGuardarPrincipal.style.display = 'inline-block'; debugVisibilidad('btnGuardarPrincipal', true);
            btnGuardarPrincipal.innerHTML = '<i class="fas fa-save"></i> Guardar Gestión';
        }
        
        
        
        // Mostrar botones de navegación después del guardado
        function mostrarBotonesNavegacion() {
            const btnNavegacion = document.getElementById('btnNavegacion');
            const btnSiguienteCliente = document.getElementById('btnSiguienteCliente');
            const btnBuscarCliente = document.getElementById('btnBuscarCliente');
            const btnGuardarPrincipal = document.getElementById('btnGuardarPrincipal');
            
            if (!btnNavegacion || !btnSiguienteCliente || !btnBuscarCliente) {
                console.error('Elementos de navegación no encontrados');
                return;
            }
            
            // Marcar bloqueo de navegación para evitar reconfiguraciones posteriores
            uiBloqueoNavegacion = true;
            
            // Mostrar botones de navegación
            btnNavegacion.style.display = 'block'; 
            debugVisibilidad('btnNavegacion', true);
            
            // Ocultar el botón principal para que en el mismo lugar queden los de navegación
            if (btnGuardarPrincipal) {
                btnGuardarPrincipal.style.display = 'none'; 
                debugVisibilidad('btnGuardarPrincipal', false);
            }
            
            // Mostrar botones según lógica: Next Client + Search si hay tareas, solo Search si no
            if (tieneTareasPendientes) {
                // Hay tareas pendientes - mostrar ambos botones inicialmente
                btnSiguienteCliente.style.display = 'inline-block'; 
                debugVisibilidad('btnSiguienteCliente', true);
                btnBuscarCliente.style.display = 'inline-block'; 
                debugVisibilidad('btnBuscarCliente', true);
                
                // Verificar dinámicamente si realmente hay un siguiente cliente disponible
                actualizarDisponibilidadSiguienteCliente(btnSiguienteCliente, btnBuscarCliente);
            } else {
                // No hay tareas pendientes - solo mostrar Search
                btnSiguienteCliente.style.display = 'none'; 
                debugVisibilidad('btnSiguienteCliente', false);
                btnBuscarCliente.style.display = 'inline-block'; 
                debugVisibilidad('btnBuscarCliente', true);
            }
        }

        // Verifica contra el backend si existe un siguiente cliente y ajusta visibilidad de botones
        function actualizarDisponibilidadSiguienteCliente(btnSiguienteCliente, btnBuscarCliente, intento = 0) {
            try {
                console.log('Verificando disponibilidad del siguiente cliente...');
                
                fetch('index.php?action=obtener_siguiente_cliente', { 
                    method: 'GET', 
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Respuesta del servidor:', data);
                    
                    if (data && data.success && data.siguiente_cliente) {
                        // Hay siguiente cliente asignado
                        btnSiguienteCliente.style.display = 'inline-block'; 
                        debugVisibilidad('btnSiguienteCliente', true);
                        btnBuscarCliente.style.display = 'inline-block'; 
                        debugVisibilidad('btnBuscarCliente', true);
                        console.log('Siguiente cliente disponible:', data.siguiente_cliente.nombre);
                    } else {
                        // No hay siguiente; dejar solo buscar
                        btnSiguienteCliente.style.display = 'none'; 
                        debugVisibilidad('btnSiguienteCliente', false);
                        btnBuscarCliente.style.display = 'inline-block'; 
                        debugVisibilidad('btnBuscarCliente', true);
                        console.log('No hay siguiente cliente disponible');
                    }
                })
                .catch(error => {
                    console.error('Error verificando siguiente cliente:', error);
                    
                    // Reintento simple por posible condición de carrera de persistencia (hasta 2 intentos)
                    if (intento < 2) {
                        console.log(`Reintentando en 700ms... (intento ${intento + 1})`);
                        setTimeout(() => actualizarDisponibilidadSiguienteCliente(btnSiguienteCliente, btnBuscarCliente, intento + 1), 700);
                        return;
                    }
                    
                    // En caso de error persistente, mantener al menos el botón de buscar
                    btnSiguienteCliente.style.display = 'none'; 
                    debugVisibilidad('btnSiguienteCliente', false);
                    btnBuscarCliente.style.display = 'inline-block'; 
                    debugVisibilidad('btnBuscarCliente', true);
                    console.log('Error persistente, mostrando solo botón de búsqueda');
                });
            } catch (error) {
                console.error('Error en actualizarDisponibilidadSiguienteCliente:', error);
                btnSiguienteCliente.style.display = 'none'; 
                debugVisibilidad('btnSiguienteCliente', false);
                btnBuscarCliente.style.display = 'inline-block'; 
                debugVisibilidad('btnBuscarCliente', true);
            }
        }
        
        // Cerrar modal de declinar todos
        function cerrarModalDeclinarTodos() {
            document.getElementById('modalDeclinarTodos').style.display = 'none';
            document.getElementById('comentarios-declinacion').value = '';
        }
        
        // Confirmar declinación de todos los productos
        function confirmarDeclinarTodos() {
            const comentarios = document.getElementById('comentarios-declinacion').value.trim();
            
            if (!comentarios) {
                mostrarError('Los comentarios son obligatorios');
                return;
            }
            
            const clienteId = <?php echo $cliente['id']; ?>;
            
            fetch('index.php?action=declinar_todos_productos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cliente_id: clienteId,
                    comentarios: comentarios
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarExito('Todos los productos han sido declinados');
                    cerrarModalDeclinarTodos();
                    cargarProductos(); // Recargar lista
                } else {
                    mostrarError('Error al declinar productos: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError('Error al declinar productos');
            });
        }
        
        // Funciones de utilidad para mostrar mensajes
        function mostrarExito(mensaje) {
            // Crear o actualizar mensaje de éxito
            let alertDiv = document.getElementById('alert-exito');
            if (!alertDiv) {
                alertDiv = document.createElement('div');
                alertDiv.id = 'alert-exito';
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.style.position = 'fixed';
                alertDiv.style.top = '20px';
                alertDiv.style.right = '20px';
                alertDiv.style.zIndex = '9999';
                document.body.appendChild(alertDiv);
            }
            
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle"></i> ${mensaje}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        function mostrarError(mensaje) {
            // Crear o actualizar mensaje de error
            let alertDiv = document.getElementById('alert-error');
            if (!alertDiv) {
                alertDiv = document.createElement('div');
                alertDiv.id = 'alert-error';
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.style.position = 'fixed';
                alertDiv.style.top = '20px';
                alertDiv.style.right = '20px';
                alertDiv.style.zIndex = '9999';
                document.body.appendChild(alertDiv);
            }
            
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle"></i> ${mensaje}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        // Función para actualizar el teléfono seleccionado
        function actualizarTelefonoSeleccionado() {
            const select = document.getElementById('telefonoSelect');
            const numeroSeleccionado = select.value;
            const tipoSeleccionado = select.options[select.selectedIndex].getAttribute('data-tipo');
            
            console.log(`Teléfono seleccionado: ${numeroSeleccionado} (${tipoSeleccionado})`);
            
            // Aquí puedes agregar lógica adicional si necesitas hacer algo con el número seleccionado
            // Por ejemplo, actualizar algún campo oculto o hacer una llamada
        }
        
        // Función para hacer clic en el número de teléfono seleccionado
        function llamarNumeroSeleccionado() {
            const select = document.getElementById('telefonoSelect');
            const numeroSeleccionado = select.value;
            
            if (numeroSeleccionado) {
                llamarDesdeVentanaAnclada(numeroSeleccionado);
            }
        }
        
        // ===== FUNCIONES PARA EL BUSCADOR DEL NAVBAR =====
        
        // Función para toggle del buscador en navbar
        function toggleNavSearch() {
            const overlay = document.getElementById('navSearchOverlay');
            const button = document.querySelector('.search-nav-button');
            
            if (overlay && button) {
                if (overlay.classList.contains('active')) {
                    overlay.classList.remove('active');
                    button.classList.remove('active');
                } else {
                    overlay.classList.add('active');
                    button.classList.add('active');
                    document.getElementById('navCedulaInput').focus();
                }
            }
        }
        
        // Función para buscar cliente desde el navbar
        function navBuscarCliente(event) {
            event.preventDefault();
            
            const cedula = document.getElementById('navCedulaInput').value.trim();
            if (!cedula) {
                mostrarExito('Por favor ingresa una cédula');
                return;
            }
            
            const resultsDiv = document.getElementById('navSearchResults');
            
            // Mostrar loading
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = '<div class="search-loading"><i class="fas fa-spinner fa-spin"></i> Buscando cliente...</div>';
            
            fetch(`index.php?action=buscar_cliente_por_cedula&cedula=${encodeURIComponent(cedula)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarNavResultados(data.clientes, cedula);
                    } else {
                        mostrarNavError(data.message || 'Error al buscar cliente');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarNavError('Error de conexión al buscar cliente');
                });
        }
        
        // Función para mostrar resultados en el navbar
        function mostrarNavResultados(clientes, cedula) {
            const resultsDiv = document.getElementById('navSearchResults');
            
            if (clientes.length === 0) {
                resultsDiv.innerHTML = `
                    <div class="search-no-results">
                        <i class="fas fa-search"></i>
                        <p style="margin-top: 10px;">No se encontraron clientes con la cédula "${cedula}"</p>
                        <small style="display: block; margin-top: 10px; color: #6b7280;">
                            Asegúrate de tener acceso a la base de datos correspondiente.
                        </small>
                    </div>
                `;
                return;
            }
            
            let html = '';
            clientes.forEach(cliente => {
                html += `
                    <div class="search-result-item" onclick="navSeleccionarCliente(${cliente.id})">
                        <h5><i class="fas fa-user"></i> ${cliente.nombre || 'Sin nombre'}</h5>
                        <p><strong>Cédula:</strong> ${cliente.cedula}</p>
                        ${cliente.telefono ? `<p><strong>Teléfono:</strong> ${cliente.telefono}</p>` : ''}
                        ${cliente.email ? `<p><strong>Email:</strong> ${cliente.email}</p>` : ''}
                        <p style="color: #3b82f6; font-size: 0.75rem;"><i class="fas fa-database"></i> ${cliente.nombre_cargue || 'Base asignada'}</p>
                    </div>
                `;
            });
            
            resultsDiv.innerHTML = html;
        }
        
        // Función para mostrar errores en el navbar
        function mostrarNavError(mensaje) {
            const resultsDiv = document.getElementById('navSearchResults');
            resultsDiv.innerHTML = `
                <div class="search-no-results">
                    <i class="fas fa-exclamation-triangle" style="color: #dc2626;"></i>
                    <p style="margin-top: 10px; color: #dc2626;">${mensaje}</p>
                </div>
            `;
        }
        
        // Función para seleccionar cliente desde el navbar
        function navSeleccionarCliente(clienteId) {
            // Redirigir a la vista de gestión de cliente
            window.location.href = `index.php?action=gestionar_cliente&id=${clienteId}`;
        }
        
        // Cerrar el modal de búsqueda al hacer clic fuera
        document.addEventListener('DOMContentLoaded', function() {
            // Cerrar modal al hacer clic fuera
            document.addEventListener('click', function(event) {
                const overlay = document.getElementById('navSearchOverlay');
                const button = document.querySelector('.search-nav-button');
                
                if (overlay && button && !overlay.contains(event.target) && !button.contains(event.target)) {
                    if (overlay.classList.contains('active')) {
                        overlay.classList.remove('active');
                        button.classList.remove('active');
                    }
                }
            });
            
            // Permitir buscar con Enter
            const navCedulaInput = document.getElementById('navCedulaInput');
            if (navCedulaInput) {
                navCedulaInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        document.getElementById('navSearchForm').dispatchEvent(new Event('submit'));
                    }
                });
            }
        });
         
    </script>
     
     
     <!-- Modal para Búsqueda de Clientes -->
     <div id="modalBusquedaCliente" class="modal-overlay">
         <div class="modal-content modal-busqueda" style="max-width: 700px; max-height: 85vh; display: flex; flex-direction: column;">
             <div class="modal-header">
                 <h3>🔍 Buscar Cliente</h3>
                 <button type="button" class="modal-close" onclick="cerrarModalBusqueda()">&times;</button>
             </div>
             <div class="modal-body modal-body-scrollable">
                 <form id="formBusquedaCliente" onsubmit="buscarCliente(event)">
                     <div class="form-group">
                         <label for="tipo_busqueda" class="form-label">Tipo de búsqueda:</label>
                         <select name="tipo_busqueda" id="tipo_busqueda" class="form-select" required>
                             <option value="">Seleccionar tipo...</option>
                             <option value="telefono">Número de Teléfono</option>
                             <option value="cedula">Número de Cédula</option>
                         </select>
                     </div>
                     
                     <div class="form-group">
                         <label for="termino_busqueda" class="form-label">Término de búsqueda:</label>
                         <input type="text" name="termino_busqueda" id="termino_busqueda" class="form-input" 
                                placeholder="Ingresa el número o cédula..." required>
                     </div>
                     
                     <div class="btn-container">
                         <button type="submit" class="btn btn-primary">
                             <i class="fas fa-search"></i> Buscar
                         </button>
                         <button type="button" class="btn btn-secondary" onclick="cerrarModalBusqueda()">
                             <i class="fas fa-times"></i> Cancelar
                         </button>
                     </div>
                 </form>
                 
                 <!-- Resultados de búsqueda -->
                 <div id="resultadosBusqueda" class="resultados-busqueda" style="display: none; margin-top: 20px;">
                     <h4>Resultados de búsqueda:</h4>
                     <div id="listaResultados" class="lista-resultados">
                         <!-- Los resultados se cargarán aquí -->
                     </div>
                 </div>
             </div>
         </div>
     </div>

     <!-- Modal para Crear Producto -->
     <div id="modalCrearProducto" class="modal-overlay">
         <div class="modal-content">
             <div class="modal-header">
                 <h3><i class="fas fa-plus"></i> Agregar Nuevo Producto</h3>
                 <button type="button" class="modal-close" onclick="cerrarModalCrearProducto()">&times;</button>
             </div>
             <div class="modal-body">
                 <form id="formCrearProducto">
                     <input type="hidden" name="cliente_id" value="<?php echo $cliente['id']; ?>">
                     
                     <div class="form-group">
                         <label for="nombre-producto" class="form-label">Nombre del Producto:</label>
                         <input type="text" name="nombre_producto" id="nombre-producto" class="form-input" 
                                placeholder="Ej: Cuota mensual, Servicio adicional..." required>
                     </div>
                     
                     <div class="form-group">
                         <label for="monto-producto" class="form-label">Monto (opcional):</label>
                         <input type="number" name="monto" id="monto-producto" class="form-input" 
                                step="0.01" min="0" placeholder="0.00">
                     </div>
                     
                     <div class="form-group">
                         <label for="estado-producto" class="form-label">Estado Inicial:</label>
                         <select name="estado" id="estado-producto" class="form-select">
                             <option value="pendiente">Pendiente</option>
                             <option value="en_proceso">En Proceso</option>
                             <option value="pagado">Pagado</option>
                             <option value="rechazado">Rechazado</option>
                         </select>
                     </div>
                     
                     <div class="btn-container">
                         <button type="submit" class="btn btn-primary">
                             <i class="fas fa-save"></i> Crear Producto
                         </button>
                         <button type="button" class="btn btn-secondary" onclick="cerrarModalCrearProducto()">
                             <i class="fas fa-times"></i> Cancelar
                         </button>
                     </div>
                 </form>
             </div>
         </div>
     </div>

     <!-- Modal para Confirmar Declinación de Todos los Productos -->
     <div id="modalDeclinarTodos" class="modal-overlay">
         <div class="modal-content">
             <div class="modal-header">
                 <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Declinación</h3>
                 <button type="button" class="modal-close" onclick="cerrarModalDeclinarTodos()">&times;</button>
             </div>
             <div class="modal-body">
                 <p>¿Estás seguro de que deseas declinar todos los productos pendientes de este cliente?</p>
                 <p class="text-muted">Esta acción marcará todos los productos como "Rechazado" y no se puede deshacer.</p>
                 
                 <div class="form-group">
                     <label for="comentarios-declinacion" class="form-label">Comentarios (obligatorio):</label>
                     <textarea name="comentarios" id="comentarios-declinacion" class="form-input" 
                               rows="3" placeholder="Motivo de la declinación..." required></textarea>
                 </div>
                 
                 <div class="btn-container">
                     <button type="button" class="btn btn-danger" onclick="confirmarDeclinarTodos()">
                         <i class="fas fa-times-circle"></i> Sí, Declinar Todos
                     </button>
                     <button type="button" class="btn btn-secondary" onclick="cerrarModalDeclinarTodos()">
                         <i class="fas fa-times"></i> Cancelar
                     </button>
                 </div>
             </div>
         </div>
     </div>
     
     <!-- Modal para Detalles de Gestión -->
     <div id="modalDetallesGestion" class="modal-overlay">
         <div class="modal-content modal-detalles">
             <div class="modal-header">
                 <h3><i class="fas fa-info-circle"></i> Detalles Completos de la Gestión</h3>
                 <button type="button" class="modal-close" onclick="cerrarModalDetalles()">&times;</button>
             </div>
             <div class="modal-body" id="contenidoDetalles">
                 <!-- El contenido se cargará dinámicamente -->
             </div>
         </div>
     </div>
</body>
</html>


