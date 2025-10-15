<?php 
// Archivo: CoordinadorController.php
// Lógica para el coordinador

require_once 'BaseController.php';

class CoordinadorController extends BaseController {
    public function __construct($pdo) {
        parent::__construct($pdo);
    }

    public function dashboard() {
        $page_title = "Dashboard Coordinador";
        $coordinador_id = $_SESSION['user_id'];
        
        // Obtener filtros de fechas o período
        $fecha_inicio = $this->getGet('fecha_inicio');
        $fecha_fin = $this->getGet('fecha_fin');
        $periodo = $this->getGet('periodo', 'total'); // Usar 'total' por defecto para mostrar todas las gestiones
        
        // Si hay fechas específicas, usar esas; si no, usar el período
        if ($fecha_inicio && $fecha_fin) {
            // Usar fechas específicas para las métricas
            $metricas_equipo = $this->gestionModel->getMetricasEquipoConFechas($coordinador_id, $fecha_inicio, $fecha_fin);
        } else {
            // Usar período predefinido
            $metricas_equipo = $this->gestionModel->getMetricasEquipo($coordinador_id, $periodo);
        }
        
        // Obtener asesores asignados al coordinador
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        // Filtrar por término de búsqueda si se proporciona
        $terminoBusqueda = $this->getGet('buscar');
        if (!empty($terminoBusqueda)) {
            $asesores = array_filter($asesores, function($asesor) use ($terminoBusqueda) {
                return stripos($asesor['nombre_completo'], $terminoBusqueda) !== false ||
                       stripos($asesor['username'], $terminoBusqueda) !== false;
            });
        }
        
        // Calcular métricas para cada asesor usando el nuevo método
        foreach ($asesores as $key => $asesor) {
            try {
                // Si hay fechas específicas, usar métricas con fechas; si no, usar período
                if ($fecha_inicio && $fecha_fin) {
                    $asesores[$key]['metricas'] = $this->gestionModel->getMetricasAsesor($asesor['id'], $periodo, $fecha_inicio, $fecha_fin);
                } else {
                    $asesores[$key]['metricas'] = $this->gestionModel->getMetricasAsesor($asesor['id'], $periodo);
                }
                
                // Obtener información de tareas pendientes
                $tareasPendientes = $this->tareaModel->getTareasPendientesByAsesor($asesor['id']);
                $asesores[$key]['tareas_pendientes'] = count($tareasPendientes);
                
                // Calcular clientes pendientes de tareas
                $clientesPendientesTareas = 0;
                foreach ($tareasPendientes as $tarea) {
                    $clientesPendientesTareas += count($tarea['cliente_ids']);
                }
                $asesores[$key]['clientes_pendientes_tareas'] = $clientesPendientesTareas;
                
                // Obtener métricas del día si no tiene tareas
                if ($asesores[$key]['tareas_pendientes'] == 0) {
                    $gestionesHoy = $this->gestionModel->getGestionesPorDia($asesor['id'], 'dia');
                    $asesores[$key]['gestiones_hoy'] = $gestionesHoy[0]['total_gestiones'] ?? 0;
                    $asesores[$key]['contactos_efectivos_hoy'] = $gestionesHoy[0]['contactos_efectivos'] ?? 0;
                    $asesores[$key]['acuerdos_hoy'] = $gestionesHoy[0]['ventas'] ?? 0;
                } else {
                    $asesores[$key]['gestiones_hoy'] = 0;
                    $asesores[$key]['contactos_efectivos_hoy'] = 0;
                    $asesores[$key]['acuerdos_hoy'] = 0;
                }
                
                // Verificar que las métricas se obtuvieron correctamente
                if ($asesores[$key]['metricas'] && is_array($asesores[$key]['metricas'])) {
                    // Mantener compatibilidad con el código existente
                    $asesores[$key]['total_clientes'] = $asesores[$key]['metricas']['total_clientes'] ?? 0;
                    $asesores[$key]['llamadas_realizadas'] = $asesores[$key]['metricas']['total_gestiones'] ?? 0;
                    $asesores[$key]['ventas_realizadas'] = $asesores[$key]['metricas']['ventas_exitosas'] ?? 0;
                    
                    // Calcular porcentaje de llamadas
                    if ($asesores[$key]['total_clientes'] > 0) {
                        $asesores[$key]['porcentaje_llamadas'] = round(($asesores[$key]['llamadas_realizadas'] / $asesores[$key]['total_clientes']) * 100, 1);
                    } else {
                        $asesores[$key]['porcentaje_llamadas'] = 0;
                    }
                } else {
                    // Si no se pudieron obtener métricas, establecer valores por defecto
                    $asesores[$key]['metricas'] = [
                        'total_clientes' => 0,
                        'total_gestiones' => 0,
                        'ventas_exitosas' => 0,
                        'tasa_conversion' => 0,
                        'tasa_contacto_efectivo' => 0,
                        'tiempo_promedio_conversacion' => 0,
                        'total_ventas_monto' => 0,
                        'promedio_venta' => 0
                    ];
                    
                    $asesores[$key]['total_clientes'] = 0;
                    $asesores[$key]['llamadas_realizadas'] = 0;
                    $asesores[$key]['ventas_realizadas'] = 0;
                    $asesores[$key]['porcentaje_llamadas'] = 0;
                    
                    // Log del error para debugging
                    error_log("No se pudieron obtener métricas para el asesor ID: " . $asesor['id'] . " - Nombre: " . $asesor['nombre_completo']);
                }
            } catch (Exception $e) {
                // En caso de error, establecer valores por defecto y log del error
                error_log("Error al obtener métricas del asesor ID: " . $asesor['id'] . " - Error: " . $e->getMessage());
                
                $asesores[$key]['metricas'] = [
                    'total_clientes' => 0,
                    'total_gestiones' => 0,
                    'ventas_exitosas' => 0,
                    'tasa_conversion' => 0,
                    'tasa_contacto_efectivo' => 0,
                    'tiempo_promedio_conversacion' => 0,
                    'total_ventas_monto' => 0,
                    'promedio_venta' => 0
                ];
                
                $asesores[$key]['total_clientes'] = 0;
                $asesores[$key]['llamadas_realizadas'] = 0;
                $asesores[$key]['ventas_realizadas'] = 0;
                $asesores[$key]['porcentaje_llamadas'] = 0;
            }
        }
        
        // Usar métricas del equipo para estadísticas generales
        $total_asesores = $metricas_equipo['total_asesores'];
        $total_clientes = $metricas_equipo['total_clientes'];
        $total_llamadas = $metricas_equipo['total_gestiones'];
        $total_ventas = $metricas_equipo['ventas_exitosas'];
        
        // Obtener recordatorios pendientes del equipo
        $llamadasPendientes = $this->gestionModel->getLlamadasPendientesCoordinador($coordinador_id);
        $totalLlamadasPendientesHoy = count($llamadasPendientes);

        // Obtener asesores disponibles para transferencias
        $asesoresDisponibles = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);

        // Datos adicionales para el dashboard
        $datos_dashboard = [
            'total_asesores' => $total_asesores,
            'total_clientes' => $total_clientes,
            'total_llamadas' => $total_llamadas,
            'total_ventas' => $total_ventas,
            'tasa_conversion' => $metricas_equipo['tasa_conversion'],
            'tasa_contacto_efectivo' => $metricas_equipo['tasa_contacto_efectivo'],
            'tiempo_promedio_conversacion' => $metricas_equipo['tiempo_promedio_conversacion'],
            'total_ventas_monto' => $metricas_equipo['total_ventas_monto'],
            'promedio_venta' => $metricas_equipo['promedio_venta'],
            'periodo' => $periodo,
            'llamadas_pendientes' => $llamadasPendientes,
            'total_llamadas_pendientes_hoy' => $totalLlamadasPendientesHoy,
            'asesores_disponibles' => $asesoresDisponibles
        ];
        
        require 'views/coordinador_dashboard.php';
    }
    
    public function listCargas() {
        $page_title = "Gestión de Bases de Datos";
        $coordinador_id = $_SESSION['user_id'];
        $cargas = $this->clienteModel->getCargasByCoordinador($coordinador_id, true); // Solo bases habilitadas
        
        // Calcular estadísticas para cada carga
        $cargas_con_stats = [];
        foreach ($cargas as $carga) {
            $carga['total_clientes'] = $this->clienteModel->getTotalClientsByCargaIdAndCoordinador($carga['id'], $coordinador_id);
            $carga['clientes_asignados'] = $this->clienteModel->getTotalClientsAsignadosByCargaIdAndCoordinador($carga['id'], $coordinador_id);
            $carga['clientes_pendientes'] = $carga['total_clientes'] - $carga['clientes_asignados'];
            $cargas_con_stats[] = $carga;
        }
        $cargas = $cargas_con_stats;
        
        require 'views/cargas_excel_list.php';
    }

    public function gestionCargas() {
        $page_title = "Gestión de Cargas de Archivos";
        $coordinador_id = $_SESSION['user_id'];
        
        // Obtener cargas existentes para mostrar en la interfaz
        $cargas = $this->cargaExcelModel->getCargasByCoordinador($coordinador_id);
        
        require 'views/gestion_cargas_integrada.php';
    }

    public function uploadExcel() {
        $page_title = "Subir Nuevo Archivo CSV";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_excel'])) {
            $action = $this->getPost('action', 'consolidada');
            $usuarioCoordinadorId = $_SESSION['user_id'];
            
            // Determinar el nombre según la acción
            if ($action === 'consolidada') {
                $nombreCargue = 'BASE_DATOS_CONSOLIDADA';
            } else {
                $nombreCargue = $this->getPost('nombre_cargue', 'BASE_DATOS_CONSOLIDADA');
            }
            
            // Verificar el tamaño del archivo
            $fileSize = $_FILES['archivo_excel']['size'];
            $maxFileSize = 500 * 1024 * 1024; // 500MB para archivos CSV grandes
            
            if ($fileSize > $maxFileSize) {
                $_SESSION['error_message'] = "❌ Error en la carga: El archivo es demasiado grande. El tamaño máximo permitido es 500MB.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Verificar tipo de archivo
            $fileType = strtolower(pathinfo($_FILES['archivo_excel']['name'], PATHINFO_EXTENSION));
            if ($fileType !== 'csv') {
                $_SESSION['error_message'] = "❌ Error en la carga: Solo se permiten archivos CSV.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Procesar el archivo CSV
            $handle = fopen($_FILES['archivo_excel']['tmp_name'], 'r');
            if (!$handle) {
                $_SESSION['error_message'] = "❌ Error en la carga: No se pudo abrir el archivo CSV.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Detectar delimitador automáticamente
            $first_line = fgets($handle);
            rewind($handle);
            
            $delimiters = [',', ';', "\t"];
            $delimiter = ',';
            $max_count = 0;
            
            foreach ($delimiters as $d) {
                $count = substr_count($first_line, $d);
                if ($count > $max_count) {
                    $max_count = $count;
                    $delimiter = $d;
                }
            }
            
            // Leer encabezados con el delimitador detectado
            $headers = fgetcsv($handle, 0, $delimiter);
            if (!$headers) {
                $error_message = "❌ Error en la carga: El archivo CSV está vacío o no tiene encabezados válidos.";
                fclose($handle);
                $page_title = "Subir Nuevo Archivo CSV";
                $coordinador_id = $_SESSION['user_id'];
                $cargaExistente = $this->cargaExcelModel->getCargaConsolidada($coordinador_id);
                header('Location: index.php?action=gestion_cargas');
                exit;
                return;
            }
            
            // Log para debug
            error_log("Delimitador detectado: " . $delimiter);
            error_log("Encabezados encontrados: " . implode(', ', $headers));
            
            // Mapear columnas por nombre (insensible a mayúsculas/minúsculas)
            $columnMap = [];
            foreach ($headers as $index => $header) {
                if (empty(trim($header))) {
                    continue; // Saltar encabezados vacíos
                }
                
                $headerLower = strtolower(trim($header));
                
                // Mapeo más flexible para nombre
                if (strpos($headerLower, 'nombre') !== false || 
                    strpos($headerLower, 'name') !== false ||
                    strpos($headerLower, 'nombres') !== false) {
                    $columnMap['nombre'] = $index;
                }
                // Mapeo más flexible para cédula
                elseif (strpos($headerLower, 'cedula') !== false || 
                        strpos($headerLower, 'dni') !== false ||
                        strpos($headerLower, 'identificacion') !== false ||
                        strpos($headerLower, 'id') !== false ||
                        strpos($headerLower, 'documento') !== false) {
                    $columnMap['cedula'] = $index;
                }
                // Mapeo más flexible para teléfono
                elseif (strpos($headerLower, 'telefono') !== false || 
                        strpos($headerLower, 'tel') !== false ||
                        strpos($headerLower, 'phone') !== false ||
                        strpos($headerLower, 'fono') !== false) {
                    $columnMap['telefono'] = $index;
                }
                // Mapeo más flexible para celular
                elseif (strpos($headerLower, 'celular') !== false || 
                        strpos($headerLower, 'cel') !== false ||
                        strpos($headerLower, 'mobile') !== false ||
                        strpos($headerLower, 'movil') !== false) {
                    $columnMap['celular'] = $index;
                }
                // Mapeo más flexible para email
                elseif (strpos($headerLower, 'email') !== false || 
                        strpos($headerLower, 'correo') !== false ||
                        strpos($headerLower, 'e-mail') !== false ||
                        strpos($headerLower, 'mail') !== false) {
                    $columnMap['email'] = $index;
                }
                // Mapeo más flexible para dirección
                elseif (strpos($headerLower, 'direccion') !== false || 
                        strpos($headerLower, 'dir') !== false ||
                        strpos($headerLower, 'address') !== false ||
                        strpos($headerLower, 'domicilio') !== false) {
                    $columnMap['direccion'] = $index;
                }
                // Mapeo más flexible para ciudad
                elseif (strpos($headerLower, 'ciudad') !== false || 
                        strpos($headerLower, 'city') !== false ||
                        strpos($headerLower, 'municipio') !== false ||
                        strpos($headerLower, 'localidad') !== false) {
                    $columnMap['ciudad'] = $index;
                }
            }
            
            // Log para debug del mapeo
            error_log("Mapeo de columnas: " . json_encode($columnMap));
            
            // Verificar columnas obligatorias
            if (!isset($columnMap['nombre']) || !isset($columnMap['cedula']) || !isset($columnMap['telefono'])) {
                $error_message = "❌ Error en la carga: El archivo CSV debe contener al menos: Nombre, Cédula y Teléfono.<br><br>
                <strong>Columnas encontradas:</strong><br>";
                
                if (!empty($headers)) {
                    $error_message .= "• " . implode("<br>• ", array_map('htmlspecialchars', $headers));
                } else {
                    $error_message .= "No se encontraron encabezados en el archivo.";
                }
                
                $error_message .= "<br><br><strong>Columnas requeridas:</strong><br>• Nombre (o similar)<br>• Cédula/DNI (o similar)<br>• Teléfono (o similar)";
                
                fclose($handle);
                $page_title = "Subir Nuevo Archivo CSV";
                $coordinador_id = $_SESSION['user_id'];
                $cargaExistente = $this->cargaExcelModel->getCargaConsolidada($coordinador_id);
                header('Location: index.php?action=gestion_cargas');
                exit;
                return;
            }
            
            // Verificar solo las columnas obligatorias
            $columnasObligatorias = ['nombre', 'cedula', 'telefono'];
            $columnasFaltantes = [];
            
            foreach ($columnasObligatorias as $columna) {
                if (!isset($columnMap[$columna])) {
                    $columnasFaltantes[] = $columna;
                }
            }
            
            if (!empty($columnasFaltantes)) {
                $error_message = "❌ Error en la carga: El archivo CSV debe contener las columnas obligatorias: Nombre, Cédula y Teléfono.<br><br>
                <strong>Columnas faltantes:</strong><br>• " . implode("<br>• ", $columnasFaltantes) . "<br><br>
                <strong>Columnas encontradas:</strong><br>";
                
                if (!empty($headers)) {
                    $error_message .= "• " . implode("<br>• ", array_map('htmlspecialchars', $headers));
                }
                
                $error_message .= "<br><br><strong>Columnas obligatorias:</strong><br>• Nombre (o similar)<br>• Cédula/DNI (o similar)<br>• Teléfono (o similar)<br><br>
                <strong>Columnas opcionales (recomendadas):</strong><br>• Celular<br>• Email<br>• Dirección<br>• Ciudad";
                
                fclose($handle);
                $page_title = "Subir Nuevo Archivo CSV";
                $coordinador_id = $_SESSION['user_id'];
                $cargaExistente = $this->cargaExcelModel->getCargaConsolidada($coordinador_id);
                header('Location: index.php?action=gestion_cargas');
                exit;
                return;
            }
            
            // Siempre usar la misma carga consolidada para el coordinador
            $cargaExistente = $this->cargaExcelModel->getCargaConsolidada($usuarioCoordinadorId);
            
                        if ($cargaExistente) {
                // Usar carga existente
                $cargaId = $cargaExistente['id'];
                $esNuevaBase = false;
            } else {
                // Crear única carga consolidada
                $cargaId = $this->cargaExcelModel->crearCargaConsolidada($usuarioCoordinadorId);
                if (!$cargaId) {
                    $_SESSION['error_message'] = "❌ Error en la carga: No se pudo crear la base de datos consolidada.";
                    fclose($handle);
                    header('Location: index.php?action=gestion_cargas');
                    exit;
                }
                $esNuevaBase = true;
            }
            
            // Contadores para el resumen
            $clientesNuevos = 0;
            $clientesDuplicados = 0;
            $clientesAgregados = 0;
            $errores = 0;
            
            // Procesar cada fila del CSV con el delimitador detectado
            $rowNumber = 1; // Comenzar en 1 porque ya leímos los encabezados
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;
                
                try {
                    // Verificar que las columnas obligatorias existan en el mapeo
                    if (!isset($columnMap['nombre']) || !isset($columnMap['cedula']) || !isset($columnMap['telefono'])) {
                        $errores++;
                        error_log("Error en fila {$rowNumber}: Columnas obligatorias no encontradas en el mapeo. Mapeo actual: " . json_encode($columnMap));
                        continue;
                    }
                    
                    // Extraer datos según el mapeo con validación
                    // Columnas obligatorias - deben tener contenido
                    $nombre = isset($data[$columnMap['nombre']]) ? trim($data[$columnMap['nombre']]) : '';
                    $cedula = isset($data[$columnMap['cedula']]) ? trim($data[$columnMap['cedula']]) : '';
                    $telefono = isset($data[$columnMap['telefono']]) ? trim($data[$columnMap['telefono']]) : '';
                    
                    // Columnas opcionales - pueden estar vacías
                    $celular = (isset($columnMap['celular']) && isset($data[$columnMap['celular']])) ? trim($data[$columnMap['celular']]) : '';
                    $email = (isset($columnMap['email']) && isset($data[$columnMap['email']])) ? trim($data[$columnMap['email']]) : '';
                    $direccion = (isset($columnMap['direccion']) && isset($data[$columnMap['direccion']])) ? trim($data[$columnMap['direccion']]) : '';
                    $ciudad = (isset($columnMap['ciudad']) && isset($data[$columnMap['ciudad']])) ? trim($data[$columnMap['ciudad']]) : '';
                    
                    // Validar datos obligatorios
                    if (empty($nombre) || empty($cedula) || empty($telefono)) {
                        $errores++;
                        error_log("Error en fila {$rowNumber}: Datos obligatorios vacíos - Nombre: '{$nombre}', Cédula: '{$cedula}', Teléfono: '{$telefono}'");
                        continue;
                    }
                    
                    // Verificar si el cliente ya existe
                    $clienteExistente = $this->clienteModel->getClienteByCedula($cedula);
                    
                    if ($clienteExistente) {
                        // Cliente existe, verificar si ya está en esta carga
                        if (!$this->clienteModel->clienteYaEnCarga($clienteExistente['id'], $cargaId)) {
                            // Agregar cliente existente a la carga
                            $this->clienteModel->agregarClienteACarga($cargaId, $clienteExistente['id']);
                            $clientesAgregados++;
                        }
                        $clientesDuplicados++;
                    } else {
                        // Cliente nuevo, crearlo y agregarlo a la carga
                        $nuevoClienteId = $this->clienteModel->crearCliente([
                            'nombre' => $nombre,
                            'cedula' => $cedula,
                            'telefono' => $telefono,
                            'celular2' => $celular,
                            'email' => $email,
                            'ciudad' => $ciudad,
                            'carga_excel_id' => $cargaId
                        ]);
                        
                        if ($nuevoClienteId) {
                            $clientesNuevos++;
                            $clientesAgregados++;
                        } else {
                            $errores++;
                        }
                    }
                    
                } catch (Exception $e) {
                    $errores++;
                    error_log("Error procesando fila {$rowNumber}: " . $e->getMessage());
                }
            }
            
            fclose($handle);
            
            // Verificar si hubo errores críticos
            if ($errores > 0 && $clientesAgregados == 0) {
                $error_message = "❌ <strong>Error en la carga: No se pudo procesar el archivo correctamente.</strong><br><br>
                <strong>Detalles del error:</strong><br>
                • <strong>Total de filas procesadas:</strong> " . ($rowNumber - 1) . "<br>
                • <strong>Errores encontrados:</strong> {$errores}<br>
                • <strong>Clientes agregados:</strong> {$clientesAgregados}<br><br>
                <strong>Posibles causas:</strong><br>
                • Columnas obligatorias vacías (Nombre, Cédula, Teléfono)<br>
                • Formato incorrecto del CSV<br>
                • Datos malformados en las filas<br>
                • Problemas de permisos en el servidor<br><br>
                <strong>Recomendaciones:</strong><br>
                • Verifique que las columnas obligatorias tengan datos<br>
                • Revise el formato del archivo CSV<br>
                • Asegúrese de que no haya filas completamente vacías<br>
                • Contacte al administrador si el problema persiste";
            } else {
                // Mostrar mensaje según el resultado
                if ($clientesNuevos > 0) {
                    // Hay contactos nuevos - mostrar éxito
                    $mensajeBase = $esNuevaBase ? "🏗️ ¡Base de datos creada exitosamente! Se agregaron nuevos contactos." : "✅ ¡Carga exitosa! Se agregaron nuevos contactos a la base de datos.";
                    $success_message = "
                        <strong>{$mensajeBase}</strong><br><br>
                        <strong>Resumen del archivo:</strong><br>
                        • <strong>Total de filas procesadas:</strong> " . ($rowNumber - 1) . "<br>
                        • <strong>Clientes nuevos agregados:</strong> {$clientesNuevos}<br>
                        • <strong>Clientes duplicados encontrados:</strong> {$clientesDuplicados}<br>
                        • <strong>Total clientes agregados:</strong> {$clientesAgregados}<br>
                        • <strong>Errores encontrados:</strong> {$errores}<br><br>
                        <strong>📊 Base de Datos Consolidada:</strong><br>
                        • <strong>Total de clientes en la base:</strong> " . $this->clienteModel->getTotalClientsByCargaId($cargaId) . "<br>
                        • <strong>Clientes únicos totales:</strong> " . $this->clienteModel->getTotalClientesUnicos() . "<br><br>
                        <em>💡 " . ($esNuevaBase ? "Se creó una nueva base de datos consolidada." : "Todos los archivos CSV se consolidan en una sola base de datos para facilitar la gestión.") . "</em>
                    ";
                } elseif ($clientesAgregados > 0) {
                    // Solo se agregaron contactos existentes - mostrar información
                    $info_message = "
                        <strong>ℹ️ Archivo procesado correctamente</strong><br><br>
                        <strong>Resumen del archivo:</strong><br>
                        • <strong>Total de filas procesadas:</strong> " . ($rowNumber - 1) . "<br>
                        • <strong>Clientes nuevos agregados:</strong> {$clientesNuevos}<br>
                        • <strong>Clientes duplicados encontrados:</strong> {$clientesDuplicados}<br>
                        • <strong>Total clientes agregados:</strong> {$clientesAgregados}<br>
                        • <strong>Errores encontrados:</strong> {$errores}<br><br>
                        <strong>📊 Base de Datos Consolidada:</strong><br>
                        • <strong>Total de clientes en la base:</strong> " . $this->clienteModel->getTotalClientsByCargaId($cargaId) . "<br>
                        • <strong>Clientes únicos totales:</strong> " . $this->clienteModel->getTotalClientesUnicos() . "<br><br>
                        <em>💡 No se agregaron contactos nuevos porque todos ya estaban en la base de datos.</em>
                    ";
                } else {
                    // No se agregó ningún contacto - mostrar advertencia
                    $warning_message = "
                        <strong>⚠️ Archivo procesado pero no se agregaron contactos</strong><br><br>
                        <strong>Resumen del archivo:</strong><br>
                        • <strong>Total de filas procesadas:</strong> " . ($rowNumber - 1) . "<br>
                        • <strong>Clientes nuevos agregados:</strong> {$clientesNuevos}<br>
                        • <strong>Clientes duplicados encontrados:</strong> {$clientesDuplicados}<br>
                        • <strong>Total clientes agregados:</strong> {$clientesAgregados}<br>
                        • <strong>Errores encontrados:</strong> {$errores}<br><br>
                        <em>💡 Todos los contactos del archivo ya estaban en la base de datos.</em>
                    ";
                }
            }
            
            // Establecer mensajes de sesión según el resultado
            if (isset($success_message)) {
                $_SESSION['success_message'] = $success_message;
                $_SESSION['success_auto_hide'] = true; // Flag para auto-ocultar mensaje
            } elseif (isset($info_message)) {
                $_SESSION['info_message'] = $info_message;
                $_SESSION['success_auto_hide'] = true;
            } elseif (isset($warning_message)) {
                $_SESSION['warning_message'] = $warning_message;
                $_SESSION['success_auto_hide'] = true;
            } elseif (isset($error_message)) {
                $_SESSION['error_message'] = $error_message;
            }
            
            // Redirigir a la gestión de cargas
            header('Location: index.php?action=gestion_cargas');
            exit;
            
        } else {
            // Si no es POST, redirigir a la gestión integrada
            header('Location: index.php?action=gestion_cargas');
            exit;
        }
    }

    /**
     * Lee un archivo CSV y retorna un array de clientes
     */
    private function leerArchivoCSV($archivo_path) {
        $clientes = [];
        
        if (($handle = fopen($archivo_path, "r")) !== FALSE) {
            // Leer la primera línea (encabezados)
            // Detectar el delimitador automáticamente
            $primera_linea = fgets($handle);
            $delimitador = $this->detectarDelimitador($primera_linea);
            
            // Volver al inicio del archivo
            rewind($handle);
            
            // Leer la primera línea (encabezados)
            $encabezados = fgetcsv($handle, 1000, $delimitador);
            
            // Mapear encabezados a índices
            $indices = $this->mapearEncabezadosCSV($encabezados);
            
            // Leer cada línea de datos
            $linea = 2; // Empezar en línea 2 (después de encabezados)
            while (($data = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
                if (count($data) >= 3) { // Mínimo nombre, cedula y teléfono
                    $cliente = [
                        'obligacion' => trim($data[$indices['obligacion']] ?? ''),
                        'cedula' => trim($data[$indices['cedula']] ?? ''),
                        'nombre' => trim($data[$indices['nombre']] ?? ''),
                        'saldo_k_obligacion' => $this->limpiarNumero($data[$indices['saldo_k_obligacion']] ?? ''),
                        'capital_cliente' => $this->limpiarNumero($data[$indices['capital_cliente']] ?? ''),
                        'pago_total_obligacion' => $this->limpiarNumero($data[$indices['pago_total_obligacion']] ?? ''),
                        'mora_actual' => (int)($data[$indices['mora_actual']] ?? 0),
                        'propiedad' => trim($data[$indices['propiedad']] ?? ''),
                        'producto' => trim($data[$indices['producto']] ?? ''),
                        'medicion' => trim($data[$indices['medicion']] ?? ''),
                        'telefono' => trim($data[$indices['telefono']] ?? ''),
                        'celular2' => trim($data[$indices['celular2']] ?? ''),
                        'email' => trim($data[$indices['email']] ?? ''),
                        'direccion' => trim($data[$indices['direccion']] ?? ''),
                        'ciudad' => trim($data[$indices['ciudad']] ?? ''),
                        'linea' => $linea
                    ];
                    
                    // Solo agregar si tiene datos mínimos
                    if (!empty($cliente['nombre']) && !empty($cliente['cedula'])) {
                        $clientes[] = $cliente;
                    }
                }
                $linea++;
            }
            fclose($handle);
        }
        
        return $clientes;
    }

    /**
     * Mapea los encabezados del CSV a índices
     */
    private function mapearEncabezadosCSV($encabezados) {
        $indices = [
            'obligacion' => -1,
            'cedula' => -1,
            'nombre' => -1,
            'saldo_k_obligacion' => -1,
            'capital_cliente' => -1,
            'pago_total_obligacion' => -1,
            'mora_actual' => -1,
            'propiedad' => -1,
            'producto' => -1,
            'medicion' => -1,
            'telefono' => -1,
            'celular2' => -1,
            'email' => -1,
            'direccion' => -1,
            'ciudad' => -1
        ];
        
        // Buscar encabezados específicos
        foreach ($encabezados as $index => $encabezado) {
            // Limpiar el encabezado: quitar espacios extra y convertir a minúsculas
            $encabezado_limpio = strtolower(preg_replace('/\s+/', ' ', trim($encabezado)));
            
            // Campos financieros
            if (strpos($encabezado_limpio, 'obligacion') !== false) {
                $indices['obligacion'] = $index;
            } elseif (strpos($encabezado_limpio, 'saldo k obl') !== false || strpos($encabezado_limpio, 'saldo_k_obl') !== false) {
                $indices['saldo_k_obligacion'] = $index;
            } elseif (strpos($encabezado_limpio, 'capital cliente') !== false || strpos($encabezado_limpio, 'capital_cliente') !== false) {
                $indices['capital_cliente'] = $index;
            } elseif (strpos($encabezado_limpio, 'pago total obl') !== false || strpos($encabezado_limpio, 'pago_total_obl') !== false) {
                $indices['pago_total_obligacion'] = $index;
            } elseif (strpos($encabezado_limpio, 'mora actual') !== false || strpos($encabezado_limpio, 'mora_actual') !== false) {
                $indices['mora_actual'] = $index;
            } elseif (strpos($encabezado_limpio, 'propiedad') !== false) {
                $indices['propiedad'] = $index;
            } elseif (strpos($encabezado_limpio, 'producto') !== false) {
                $indices['producto'] = $index;
            } elseif (strpos($encabezado_limpio, 'medicion') !== false) {
                $indices['medicion'] = $index;
            }
            // Campos existentes
            elseif (strpos($encabezado_limpio, 'nombre') !== false) {
                $indices['nombre'] = $index;
            } elseif (strpos($encabezado_limpio, 'cedula') !== false || strpos($encabezado_limpio, 'dni') !== false) {
                $indices['cedula'] = $index;
            } elseif (strpos($encabezado_limpio, 'telefono') !== false || strpos($encabezado_limpio, 'tel') !== false) {
                $indices['telefono'] = $index;
            } elseif (strpos($encabezado_limpio, 'celular') !== false || strpos($encabezado_limpio, 'movil') !== false) {
                $indices['celular2'] = $index;
            } elseif (strpos($encabezado_limpio, 'email') !== false || strpos($encabezado_limpio, 'correo') !== false) {
                $indices['email'] = $index;
            } elseif (strpos($encabezado_limpio, 'direccion') !== false || strpos($encabezado_limpio, 'dir') !== false) {
                $indices['direccion'] = $index;
            } elseif (strpos($encabezado_limpio, 'ciudad') !== false || strpos($encabezado_limpio, 'municipio') !== false) {
                $indices['ciudad'] = $index;
            }
        }
        
        return $indices;
    }

    /**
     * Detecta el delimitador del CSV automáticamente
     */
    private function detectarDelimitador($primera_linea) {
        $delimitadores = [',', ';', '\t', '|'];
        $max_campos = 0;
        $mejor_delimitador = ',';
        
        foreach ($delimitadores as $delimitador) {
            $campos = str_getcsv($primera_linea, $delimitador);
            if (count($campos) > $max_campos) {
                $max_campos = count($campos);
                $mejor_delimitador = $delimitador;
            }
        }
        
        return $mejor_delimitador;
    }

    /**
     * Limpia y convierte un valor a número decimal
     */
    private function limpiarNumero($valor) {
        if (empty($valor)) return null;
        
        // Remover caracteres no numéricos excepto punto y coma
        $valor = preg_replace('/[^0-9.,]/', '', $valor);
        
        if (empty($valor)) return null;
        
        // Convertir coma a punto para decimales
        $valor = str_replace(',', '.', $valor);
        
        return (float) $valor;
    }

    /**
     * Procesa los clientes del CSV y detecta duplicados
     */
    private function procesarClientesCSV($clientes, $cargaId, $coordinadorId) {
        $nuevos = 0;
        $duplicados = 0;
        $obligacionesDuplicadas = 0;
        $obligacionesCreadas = 0;
        $total = count($clientes);
        
        // MEJORA: Agrupar datos por cédula antes de procesar
        $datosAgrupados = [];
        foreach ($clientes as $cliente) {
            $cedula = $cliente['cedula'];
            if (!isset($datosAgrupados[$cedula])) {
                $datosAgrupados[$cedula] = [
                    'info_cliente' => [
                        'cedula' => $cliente['cedula'],
                        'nombre' => $cliente['nombre'],
                        'telefono' => $cliente['telefono'],
                        'celular2' => $cliente['celular2'] ?? null,
                        'email' => $cliente['email'] ?? null,
                        'direccion' => $cliente['direccion'] ?? null,
                        'ciudad' => $cliente['ciudad'] ?? null
                    ],
                    'obligaciones' => []
                ];
            }
            
            // Agregar obligación al grupo
            $datosAgrupados[$cedula]['obligaciones'][] = [
                'obligacion' => $cliente['obligacion'],
                'saldo_k_obligacion' => $cliente['saldo_k_obligacion'],
                'capital_cliente' => $cliente['capital_cliente'],
                'pago_total_obligacion' => $cliente['pago_total_obligacion'],
                'mora_actual' => $cliente['mora_actual'],
                'propiedad' => $cliente['propiedad'],
                'producto' => $cliente['producto'],
                'medicion' => $cliente['medicion']
            ];
        }
        
        // Procesar cada grupo (un cliente por cédula)
        foreach ($datosAgrupados as $cedula => $grupo) {
            try {
                // Verificar si el cliente ya existe (por cédula)
                $clienteExistente = $this->clienteModel->getClienteByCedula($cedula);
                
                if ($clienteExistente) {
                    // Cliente duplicado - agregar obligaciones a este cliente
                    $duplicados++;
                    
                    // Verificar si ya está en esta carga
                    $yaEnCarga = $this->clienteModel->clienteYaEnCarga($clienteExistente['id'], $cargaId);
                    
                    if (!$yaEnCarga) {
                        $this->clienteModel->agregarClienteACarga($cargaId, $clienteExistente['id']);
                    }
                    
                    $clienteId = $clienteExistente['id'];
                } else {
                    // Cliente nuevo - crear cliente
                    $clienteId = $this->clienteModel->crearCliente(array_merge($grupo['info_cliente'], [
                        'carga_excel_id' => $cargaId
                    ]));
                    
                    if ($clienteId) {
                        $this->clienteModel->agregarClienteACarga($cargaId, $clienteId);
                        $nuevos++;
                    } else {
                        error_log("Error al crear cliente con cédula: $cedula");
                        continue;
                    }
                }
                
                // Procesar todas las obligaciones de este cliente
                foreach ($grupo['obligaciones'] as $obligacion) {
                    // Verificar si la obligación ya existe (única por obligación)
                    if ($this->obligacionModel->obligacionExiste($obligacion['obligacion'])) {
                        $obligacionesDuplicadas++;
                        continue; // Saltar esta obligación duplicada
                    }
                    
                    // Crear obligación
                    if ($this->obligacionModel->crearObligacion(array_merge($obligacion, [
                        'cliente_id' => $clienteId,
                        'estado' => 'activa'
                    ]))) {
                        $obligacionesCreadas++;
                    } else {
                        error_log("Error al crear obligación: {$obligacion['obligacion']}");
                    }
                }
            } catch (Exception $e) {
                error_log("Error procesando grupo de cliente con cédula $cedula: " . $e->getMessage());
                // Continuar con el siguiente grupo
            }
        }
        
        return [
            'success' => true,
            'nuevos' => $nuevos,
            'duplicados' => $duplicados,
            'obligaciones_duplicadas' => $obligacionesDuplicadas,
            'obligaciones_creadas' => $obligacionesCreadas,
            'total' => $total
        ];
    }

    public function listClientsByCarga($cargaId) {
        $page_title = "Clientes de la Carga";
        $coordinador_id = $_SESSION['user_id'];
        
        // Verificar que la carga pertenezca al coordinador
        $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($cargaId, $coordinador_id);
        if (!$carga) {
            $_SESSION['error_message'] = "No tienes acceso a esta carga o no existe.";
            header('Location: index.php?action=list_cargas');
            exit;
        }
        
        // Se implementa la paginación.
        $clientesPorPagina = 25;
        $paginaActual = $this->getGet('pagina', 1);
        $paginaActual = $this->validarId($paginaActual, 'página');
        
        // Se usa la función del modelo para obtener el total de clientes por carga.
        $totalClientes = $this->clienteModel->getTotalClientsByCargaIdAndCoordinador($cargaId, $coordinador_id);
        $totalPaginas = ceil($totalClientes / $clientesPorPagina);

        $offset = ($paginaActual - 1) * $clientesPorPagina;
        
        // Se llama a la función corregida en el modelo para obtener los clientes paginados.
        $clientes = $this->clienteModel->getClientsByCargaIdAndCoordinador($cargaId, $coordinador_id, $clientesPorPagina, $offset);
        $asesores = $this->usuarioModel->getUsuariosByRol('asesor');
        $carga_id = $cargaId;
        
        require 'views/clientes_list.php';
    }
    
    public function assignClients($cargaId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clientes']) && isset($_POST['asesor_id'])) {
            $clienteIds = $_POST['clientes']; // Array de IDs, validar cada uno
            $asesorId = $this->getPost('asesor_id');
            $asesorId = $this->validarId($asesorId, 'asesor');
            $this->clienteModel->assignClientsToAsesor($clienteIds, $asesorId);
            header('Location: index.php?action=ver_clientes&carga_id=' . $cargaId);
            exit;
        }
    }

    public function viewAsesorProgress($asesorId) {
        $page_title = "Progreso del Asesor";
        $asesor = $this->usuarioModel->getUsuarioById($asesorId);
        $gestiones = $this->gestionModel->getGestionByAsesor($asesorId);
        $clientes = $this->clienteModel->getAssignedClientsForAsesor($asesorId);

        // Contar el número de gestiones por cliente
        $gestiones_por_cliente = [];
        foreach ($gestiones as $gestion) {
            $clienteId = $gestion['cliente_id'];
            if (!isset($gestiones_por_cliente[$clienteId])) {
                $gestiones_por_cliente[$clienteId] = 0;
            }
            $gestiones_por_cliente[$clienteId]++;
        }
        
        require 'views/asesor_progreso.php';
    }

    public function tareas() {
        $page_title = "Tareas del Coordinador";
        $coordinador_id = $_SESSION['user_id'];
        $cargas = $this->clienteModel->getCargasByCoordinador($coordinador_id, true); // Solo bases habilitadas
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        // Calcular estadísticas para cada carga
        $cargas_con_stats = [];
        foreach ($cargas as $carga) {
            $carga['total_clientes'] = $this->clienteModel->getTotalClientsByCargaIdAndCoordinador($carga['id'], $coordinador_id);
            $carga['clientes_asignados'] = $this->clienteModel->getTotalClientsAsignadosByCargaIdAndCoordinador($carga['id'], $coordinador_id);
            $carga['clientes_pendientes'] = $carga['total_clientes'] - $carga['clientes_asignados'];
            $cargas_con_stats[] = $carga;
        }
        $cargas = $cargas_con_stats;
        
        // Calcular clientes asignados por asesor para cada carga
        $asesores_con_clientes = [];
        foreach ($asesores as $asesor) {
            $asesor['clientes_por_carga'] = [];
            foreach ($cargas as $carga) {
                $asesor['clientes_por_carga'][$carga['id']] = $this->clienteModel->getTotalClientsByAsesorAndCarga($asesor['id'], $carga['id'], $coordinador_id);
            }
            $asesores_con_clientes[] = $asesor;
        }
        $asesores = $asesores_con_clientes;
        
        require 'views/tareas_coordinador.php';
    }

    public function asignarClientes() {
        $cargaId = $this->getPost('carga_id');
        $cargaId = $this->validarId($cargaId, 'carga');
        $coordinador_id = $_SESSION['user_id'];
        
        // Verificar que la carga pertenezca al coordinador
        $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($cargaId, $coordinador_id);
        if (!$carga) {
            $_SESSION['error_message'] = "No tienes acceso a esta carga o no existe.";
            header("Location: index.php?action=tareas_coordinador");
            exit;
        }
        
        $asignaciones = $_POST['asignaciones']; // array: asesor_id => cantidad - validar cada clave y valor
        $clientes = $this->clienteModel->getUnassignedClientsByCargaAndCoordinador($cargaId, $coordinador_id);
        
        if (empty($clientes)) {
            $_SESSION['error_message'] = "No hay clientes disponibles para asignar.";
            header("Location: index.php?action=tareas_coordinador");
            exit;
        }

        $totalAsignados = 0;
        $clientesDisponibles = $clientes;
        
        foreach ($asignaciones as $asesorId => $cantidad) {
            if ($cantidad > 0 && !empty($clientesDisponibles)) {
                // Tomar solo la cantidad especificada de clientes disponibles
                $aAsignar = array_slice($clientesDisponibles, 0, $cantidad);
                $clienteIds = array_column($aAsignar, 'id');
                
                if (!empty($clienteIds)) {
                    $this->clienteModel->assignClientsToAsesor($clienteIds, $asesorId);
                    $totalAsignados += count($clienteIds);
                    
                    // Remover los clientes asignados de la lista disponible
                    $clientesDisponibles = array_slice($clientesDisponibles, $cantidad);
                }
            }
        }
        
        if ($totalAsignados > 0) {
            $_SESSION['success_message'] = "Se asignaron $totalAsignados clientes correctamente.";
        } else {
            $_SESSION['error_message'] = "No se pudo asignar ningún cliente.";
        }
        
        header("Location: index.php?action=tareas_coordinador");
        exit;
    }

    public function asignarAutomatico() {
        $cargaId = $this->getPost('carga_id');
        $cargaId = $this->validarId($cargaId, 'carga');
        $coordinador_id = $_SESSION['user_id'];
        
        // Verificar que la carga pertenezca al coordinador
        $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($cargaId, $coordinador_id);
        if (!$carga) {
            $_SESSION['error_message'] = "No tienes acceso a esta carga o no existe.";
            header("Location: index.php?action=tareas_coordinador");
            exit;
        }
        
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        if (empty($asesores)) {
            $_SESSION['error_message'] = "No tienes asesores asignados. Contacta al administrador.";
            header("Location: index.php?action=tareas_coordinador");
            exit;
        }
        
        $clientes = $this->clienteModel->getUnassignedClientsByCargaAndCoordinador($cargaId, $coordinador_id);

        $totalAsesores = count($asesores);
        $index = 0;
        foreach ($clientes as $cliente) {
            $asesorId = $asesores[$index % $totalAsesores]['id'];
            $this->clienteModel->assignClientsToAsesor([$cliente['id']], $asesorId);
            $index++;
        }
        $_SESSION['success_message'] = "Clientes asignados automáticamente.";
        header("Location: index.php?action=tareas_coordinador");
        exit;
    }
    
    /**
     * Gestiona la asignación de asesores al coordinador
     */
    public function gestionarAsesores() {
        $page_title = "Gestión de Asesores";
        $coordinador_id = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'asignar':
                        if (isset($_POST['asesor_id'])) {
                            $asesorId = $this->validarId($_POST['asesor_id'], 'asesor');
                            $this->usuarioModel->asignarAsesorACoordinador($asesorId, $coordinador_id);
                            $_SESSION['success_message'] = "Asesor asignado correctamente.";
                        }
                        break;
                        
                    case 'liberar':
                        if (isset($_POST['asesor_id'])) {
                            $asesorId = $this->validarId($_POST['asesor_id'], 'asesor');
                            $this->usuarioModel->liberarAsesorDeCoordinador($asesorId, $coordinador_id);
                            $_SESSION['success_message'] = "Asesor liberado correctamente.";
                        }
                        break;
                }
                header("Location: index.php?action=gestionar_asesores");
                exit;
            }
        }
        
        $asesoresAsignados = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        $asesoresDisponibles = $this->usuarioModel->getAsesoresDisponibles();
        
        require 'views/coordinador_gestionar_asesores.php';
    }
    
    /**
     * Gestiona el traspaso de clientes entre asesores
     */
    public function gestionarTraspasos() {
        $page_title = "Gestión de Traspasos de Clientes";
        $coordinador_id = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                try {
                    switch ($_POST['action']) {
                        case 'traspasar':
                            if (isset($_POST['cliente_id']) && isset($_POST['nuevo_asesor_id']) && isset($_POST['asesor_origen_id'])) {
                                $clienteId = $this->validarId($_POST['cliente_id'], 'cliente');
                                $nuevoAsesorId = $this->validarId($_POST['nuevo_asesor_id'], 'nuevo asesor');
                                $asesorOrigenId = $this->validarId($_POST['asesor_origen_id'], 'asesor origen');
                                
                                $this->clienteModel->traspasarCliente($clienteId, $nuevoAsesorId, $asesorOrigenId);
                                $_SESSION['success_message'] = "Cliente traspasado correctamente.";
                            }
                            break;
                            
                        case 'liberar':
                            if (isset($_POST['cliente_id']) && isset($_POST['asesor_id'])) {
                                $clienteId = $this->validarId($_POST['cliente_id'], 'cliente');
                                $asesorId = $this->validarId($_POST['asesor_id'], 'asesor');
                                
                                $this->clienteModel->liberarCliente($clienteId, $asesorId);
                                $_SESSION['success_message'] = "Cliente liberado correctamente.";
                            }
                            break;
                    }
                } catch (Exception $e) {
                    $_SESSION['error_message'] = "Error: " . $e->getMessage();
                }
                header("Location: index.php?action=gestionar_traspasos");
                exit;
            }
        }
        
        // Obtener asesores del coordinador
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        // Obtener clientes de cada asesor
        $clientesPorAsesor = [];
        foreach ($asesores as $asesor) {
            $clientesPorAsesor[$asesor['id']] = [
                'asesor' => $asesor,
                'clientes' => $this->clienteModel->getClientesByAsesor($asesor['id'])
            ];
        }
        
        require 'views/coordinador_gestionar_traspasos.php';
    }
    
    /**
     * Muestra los detalles de un cliente específico para el coordinador
     */
    public function verDetalleCliente($clienteId) {
        $page_title = "Detalle del Cliente";
        $coordinador_id = $_SESSION['user_id'];
        
        // Verificar que el cliente pertenezca a una carga del coordinador
        $cliente = $this->clienteModel->getClienteByIdAndCoordinador($clienteId, $coordinador_id);
        
        if (!$cliente) {
            $_SESSION['error_message'] = "No tienes acceso a este cliente o no existe.";
            header("Location: index.php?action=tareas_coordinador");
            exit;
        }
        
        // Obtener el historial de gestiones del cliente
        $gestiones = $this->gestionModel->getGestionByAsesorAndCliente($cliente['asesor_id'] ?? null, $clienteId);
        
        // Obtener información del asesor asignado
        $asesor = null;
        if ($cliente['asesor_id']) {
            $asesor = $this->usuarioModel->getUsuarioById($cliente['asesor_id']);
        }
        
        require 'views/coordinador_detalle_cliente.php';
    }

    /**
     * Muestra los detalles de gestión de un cliente específico de un asesor para el coordinador
     */
    public function verDetalleGestionAsesor($clienteId, $asesorId) {
        $page_title = "Detalle de Gestión del Asesor";
        $coordinador_id = $_SESSION['user_id'];
        
        // Verificar que el cliente pertenezca a una carga del coordinador
        $cliente = $this->clienteModel->getClienteByIdAndCoordinador($clienteId, $coordinador_id);
        
        if (!$cliente) {
            $_SESSION['error_message'] = "No tienes acceso a este cliente o no existe.";
            header("Location: index.php?action=tareas_coordinador");
            exit;
        }
        
        // Verificar que el asesor esté asignado al coordinador
        $asesor = $this->usuarioModel->getUsuarioById($asesorId);
        if (!$asesor || $asesor['rol'] !== 'asesor') {
            $_SESSION['error_message'] = "El asesor especificado no existe o no es válido.";
            header("Location: index.php?action=tareas_coordinador");
            exit;
        }
        
        // Obtener el historial de gestiones del cliente por ese asesor específico
        $gestiones = $this->gestionModel->getGestionByAsesorAndCliente($asesorId, $clienteId);
        
        require 'views/coordinador_detalle_gestion_asesor.php';
    }

    /**
     * Obtiene la clase CSS para la fila de gestión basada en el resultado
     */
    private function getGestionRowClass($resultado) {
        if (empty($resultado)) return '';
        
        if (in_array($resultado, ['Venta Exitosa', 'Venta en Frío', 'Venta con Seguimiento', 'Venta Cruzada'])) {
            return 'venta';
        } elseif (in_array($resultado, ['Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica', 'Necesita Pensarlo'])) {
            return 'rechazado';
        } elseif (in_array($resultado, ['No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado'])) {
            return 'sin-contacto';
        } elseif (in_array($resultado, ['Agenda Llamada de Seguimiento'])) {
            return 'seguimiento';
        }
        
        return '';
    }

    /**
     * Obtiene la clase CSS para el badge de resultado
     */
    private function getResultadoClass($resultado) {
        if (empty($resultado)) return 'sin-resultado';
        
        if (in_array($resultado, ['Venta Exitosa', 'Venta en Frío', 'Venta con Seguimiento', 'Venta Cruzada'])) {
            return 'venta';
        } elseif (in_array($resultado, ['Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica', 'Necesita Pensarlo'])) {
            return 'rechazo';
        } elseif (in_array($resultado, ['No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado'])) {
            return 'sin-contacto';
        } elseif (in_array($resultado, ['Agenda Llamada de Seguimiento'])) {
            return 'seguimiento';
        }
        
        return 'sin-resultado';
    }

    /**
     * Exporta la gestión de un asesor específico a CSV
     */
    public function exportarGestionAsesor($asesorId, $fechaInicio = null, $fechaFin = null, $filtros = []) {
        // Limpiar cualquier output previo
        ob_clean();
        
        try {
            // Desactivar la salida de errores para evitar que se mezclen con el CSV
            error_reporting(0);
            ini_set('display_errors', 0);
            
            $coordinador_id = $_SESSION['user_id'];
        
        // Verificar que el asesor esté asignado al coordinador
        $asesor = $this->usuarioModel->getUsuarioById($asesorId);
        if (!$asesor || !$this->usuarioModel->isAsesorAsignadoACoordinador($asesorId, $coordinador_id)) {
            $_SESSION['error_message'] = "No tienes acceso a este asesor.";
            header('Location: index.php?action=tareas_coordinador');
            exit;
        }
        
        // Si no se especifican fechas, usar el mes actual
        if (!$fechaInicio) { $fechaInicio = date('Y-m-01'); }
        if (!$fechaFin) { $fechaFin = date('Y-m-t'); }
        
        // Aplicar filtros del modal y obtener historial COMPLETO con todos los campos requeridos
        // Usar el nuevo método que incluye tipificaciones de 2 y 3 nivel, canales autorizados y base de datos
        $gestiones = $this->gestionModel->getHistorialCompletoParaExportacion(
            $asesorId, 
            $fechaInicio, 
            $fechaFin
        );
        
        // Si se aplican filtros adicionales, filtrar los resultados
        if (!empty($filtros)) {
            $gestiones = $this->filtrarGestiones($gestiones, $filtros);
        }
        
        if (empty($gestiones)) {
            // En lugar de redirigir, crear un CSV vacío con mensaje
            $nombreAsesor = str_replace(' ', '_', $asesor['nombre_completo']);
            $filename = "Gestion_Asesor_{$nombreAsesor}_{$fechaInicio}_a_{$fechaFin}_SIN_DATOS.csv";
            
            // Configurar headers para descarga CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Crear archivo CSV
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados del CSV con todos los campos requeridos
            $headers = [
                'Fecha de Gestión',
                'Asesor',
                'Cédula del Cliente',
                'Nombre del Cliente',
                'Celular del Cliente',
                'Nombre de la Base de Clientes',
                'Tipo de Contacto',
                'Tipificación 2 Nivel',
                'Tipificación 3 Nivel',
                'Observaciones',
                'Canales Autorizados',
                'Valor Total (Producto)',
                'Total Cuotas (Acuerdo de Pago)',
                'Fecha de Pago (Acuerdo de Pago)',
                'Valor de Cuota (Acuerdo de Pago)',
                'Cuota (Número de Cuota)'
            ];
            fputcsv($output, $headers);
            
            // Agregar mensaje de que no hay datos
            $row = [
                'No hay datos para exportar en el período seleccionado',
                'Período: ' . $fechaInicio . ' a ' . $fechaFin,
                'Asesor: ' . $asesor['nombre_completo'],
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ];
            fputcsv($output, $row);
            
            fclose($output);
            exit;
        }
        
        // Generar nombre del archivo
        $nombreAsesor = str_replace(' ', '_', $asesor['nombre_completo']);
        $filename = "Gestion_Asesor_{$nombreAsesor}_{$fechaInicio}_a_{$fechaFin}.csv";
        
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Configurar headers para descarga CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados del CSV con todos los campos requeridos
        $headers = [
            'Fecha de Gestión',
            'Asesor',
            'Cédula del Cliente',
            'Nombre del Cliente',
            'Celular del Cliente',
            'Nombre de la Base de Clientes',
            'Tipo de Contacto',
            'Tipificación 2 Nivel',
            'Tipificación 3 Nivel',
            'Obligación/Producto a Gestionar:',
            'Observaciones',
            'Canales Autorizados',
            'Valor Total (Producto)',
            'Total Cuotas (Acuerdo de Pago)',
            'Fecha de Pago (Acuerdo de Pago)',
            'Valor de Cuota (Acuerdo de Pago)',
            'Cuota (Número de Cuota)'
        ];
        fputcsv($output, $headers);
        
        // Datos de las gestiones con todos los campos requeridos
        foreach ($gestiones as $gestion) {
            // Verificar si es acuerdo de pago (tipificación 3 nivel = 'ACUERDO DE PAGO')
            $esAcuerdoPago = ($gestion['tipificacion_3_nivel'] ?? '') === 'ACUERDO DE PAGO';
            
            $row = [
                $this->limpiarDatoCSV($gestion['fecha_gestion']),
                $this->limpiarDatoCSV($gestion['asesor_nombre'] ?? 'No asignado'),
                $this->limpiarDatoCSV($gestion['cedula']),
                $this->limpiarDatoCSV($gestion['cliente_nombre']),
                $this->limpiarDatoCSV($gestion['celular_cliente']),
                $this->limpiarDatoCSV($gestion['base_datos_nombre'] ?? 'No especificada'),
                $this->limpiarDatoCSV($gestion['forma_contacto'] ?? 'llamada'),
                $this->limpiarDatoCSV($gestion['tipificacion_2_nivel']),
                $this->limpiarDatoCSV($gestion['tipificacion_3_nivel']),
                $this->limpiarDatoCSV($gestion['obligacion_texto']),
                $this->limpiarDatoCSV($gestion['comentarios']),
                $this->limpiarDatoCSV($gestion['canales_autorizados_texto']),
                // Campos de acuerdo de pago - solo mostrar si es acuerdo de pago
                $esAcuerdoPago ? $this->limpiarDatoCSV($gestion['valor_total'] ?? '') : '',
                $esAcuerdoPago ? $this->limpiarDatoCSV($gestion['no_cuotas'] ?? '') : '',
                $esAcuerdoPago ? $this->limpiarDatoCSV($gestion['fecha_pago'] ?? '') : '',
                $esAcuerdoPago ? $this->limpiarDatoCSV($gestion['valor_cuota'] ?? '') : '',
                $esAcuerdoPago ? $this->limpiarDatoCSV($gestion['numero_cuota'] ?? '') : ''
            ];
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
        
        } catch (Exception $e) {
            // Log del error
            error_log("Error en exportarGestionAsesor: " . $e->getMessage());
            
            // Limpiar cualquier output previo
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Configurar headers para descarga CSV de error
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="error_exportacion_asesor.csv"');
            header('Cache-Control: max-age=0');
            
            // Crear archivo CSV con mensaje de error
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            $headers = ['Error', 'Mensaje', 'Fecha'];
            fputcsv($output, $headers);
            
            $row = [
                'Error de Exportación del Asesor',
                'Ocurrió un error al exportar los datos del asesor: ' . $e->getMessage(),
                date('Y-m-d H:i:s')
            ];
            fputcsv($output, $row);
            
            fclose($output);
            exit;
        }
    }

    /**
     * Exporta la gestión de todos los asesores a CSV con información de base de datos
     */
    public function exportarGestionTodosAsesores($fechaInicio = null, $fechaFin = null) {
        // Limpiar cualquier output previo
        ob_clean();
        
        try {
            // Desactivar la salida de errores para evitar que se mezclen con el CSV
            error_reporting(0);
            ini_set('display_errors', 0);
            
            $coordinador_id = $_SESSION['user_id'];
        
        // Si no se especifican fechas, usar el mes actual
        if (!$fechaInicio) { $fechaInicio = date('Y-m-01'); }
        if (!$fechaFin) { $fechaFin = date('Y-m-t'); }
        
        // Obtener asesores asignados al coordinador
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        if (empty($asesores)) {
            // En lugar de redirigir, crear un CSV vacío con mensaje
            $filename = "Gestion_Equipo_Completo_{$fechaInicio}_a_{$fechaFin}_SIN_ASESORES.csv";
            
            // Configurar headers para descarga CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Crear archivo CSV
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados del CSV con orden correcto
            $headers = [
                'Fecha de Gestión',
                'Asesor',
                'Cédula del Cliente',
                'Nombre del Cliente',
                'Celular del Cliente',
                'Nombre de la Base de Clientes',
                'Tipo de Contacto',
                'Tipificación 2 Nivel',
                'Tipificación 3 Nivel',
                'Observaciones',
                'Canales Autorizados',
                'Valor Total (Producto)',
                'Total Cuotas (Acuerdo de Pago)',
                'Fecha de Pago (Acuerdo de Pago)',
                'Valor de Cuota (Acuerdo de Pago)',
                'Cuota (Número de Cuota)'
            ];
            fputcsv($output, $headers);
            
            // Agregar mensaje de que no hay asesores
            $row = [
                'No hay asesores asignados para exportar',
                'Período: ' . $fechaInicio . ' a ' . $fechaFin,
                'Estado: Sin asesores asignados',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ];
            fputcsv($output, $row);
            
            fclose($output);
            exit;
        }
        
        // Generar nombre del archivo
        $filename = "Gestion_Equipo_Completo_{$fechaInicio}_a_{$fechaFin}.csv";
        
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Configurar headers para descarga CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados del CSV con orden correcto
        $headers = [
            'Fecha de Gestión',
            'Asesor',
            'Cédula del Cliente',
            'Nombre del Cliente',
            'Celular del Cliente',
            'Nombre de la Base de Clientes',
            'Tipo de Contacto',
            'Tipificación 2 Nivel',
            'Tipificación 3 Nivel',
            'Obligación/Producto a Gestionar:',
            'Observaciones',
            'Canales Autorizados',
            'Valor Total (Producto)',
            'Total Cuotas (Acuerdo de Pago)',
            'Fecha de Pago (Acuerdo de Pago)',
            'Valor de Cuota (Acuerdo de Pago)',
            'Cuota (Número de Cuota)'
        ];
        fputcsv($output, $headers);
        
        // Datos de todas las gestiones con información de base de datos
        foreach ($asesores as $asesor) {
            // Usar el método correcto que incluye tipificaciones de 2 y 3 nivel
            $gestiones = $this->gestionModel->getHistorialCompletoParaExportacion($asesor['id'], $fechaInicio, $fechaFin);

            foreach ($gestiones as $gestion) {
                $row = [
                    $this->limpiarDatoCSV($gestion['fecha_gestion']),
                    $this->limpiarDatoCSV($gestion['asesor_nombre'] ?? 'No asignado'),
                    $this->limpiarDatoCSV($gestion['cedula']),
                    $this->limpiarDatoCSV($gestion['cliente_nombre']),
                    $this->limpiarDatoCSV($gestion['celular_cliente']),
                    $this->limpiarDatoCSV($gestion['base_datos_nombre'] ?? 'No especificada'),
                    $this->limpiarDatoCSV($gestion['forma_contacto'] ?? 'llamada'),
                    $this->limpiarDatoCSV($gestion['tipificacion_2_nivel']),
                    $this->limpiarDatoCSV($gestion['tipificacion_3_nivel']),
                    $this->limpiarDatoCSV($gestion['obligacion_texto']),
                    $this->limpiarDatoCSV($gestion['comentarios']),
                    $this->limpiarDatoCSV($gestion['canales_autorizados_texto']),
                    $this->limpiarDatoCSV($gestion['valor_total'] ?? ''),
                    $this->limpiarDatoCSV($gestion['no_cuotas'] ?? ''),
                    $this->limpiarDatoCSV($gestion['fecha_pago'] ?? ''),
                    $this->limpiarDatoCSV($gestion['valor_cuota'] ?? ''),
                    $this->limpiarDatoCSV($gestion['numero_cuota'] ?? '')
                ];
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
        
        } catch (Exception $e) {
            // Log del error
            error_log("Error en exportarGestionTodosAsesores: " . $e->getMessage());
            
            // Limpiar cualquier output previo
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Configurar headers para descarga CSV de error
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="error_exportacion_equipo.csv"');
            header('Cache-Control: max-age=0');
            
            // Crear archivo CSV con mensaje de error
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            $headers = ['Error', 'Mensaje', 'Fecha'];
            fputcsv($output, $headers);
            
            $row = [
                'Error de Exportación del Equipo',
                'Ocurrió un error al exportar los datos del equipo: ' . $e->getMessage(),
                date('Y-m-d H:i:s')
            ];
            fputcsv($output, $row);
            
            fclose($output);
            exit;
        }
    }

    /**
     * Exporta un reporte personalizado a CSV
     */
    public function exportarReportePersonalizado($filtros = []) {
        // Desactivar la salida de errores para evitar que se mezclen con el CSV
        error_reporting(0);
        ini_set('display_errors', 0);
        
        $coordinador_id = $_SESSION['user_id'];
        
        // Aplicar filtros por defecto si no se especifican
        if (empty($filtros['fecha_inicio'])) { $filtros['fecha_inicio'] = date('Y-m-01'); }
        if (empty($filtros['fecha_fin'])) { $filtros['fecha_fin'] = date('Y-m-t'); }
        
        $gestiones = $this->gestionModel->getGestionFiltrada(
            $coordinador_id,
            $filtros['fecha_inicio'],
            $filtros['fecha_fin'],
            $filtros['asesor_id'],
            $filtros['resultado'],
            $filtros['tipo_gestion']
        );
        
        if (empty($gestiones)) {
            $_SESSION['error_message'] = "No hay datos que coincidan con los filtros seleccionados.";
            header('Location: index.php?action=reportes_exportacion');
            exit;
        }
        
        // Generar nombre del archivo
        $filename = "Reporte_Personalizado_{$filtros['fecha_inicio']}_a_{$filtros['fecha_fin']}.csv";
        
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Configurar headers para descarga CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados del CSV con orden correcto
        $headers = [
            'Fecha de Gestión',
            'Asesor',
            'Cédula del Cliente',
            'Nombre del Cliente',
            'Celular del Cliente',
            'Nombre de la Base de Clientes',
            'Tipo de Contacto',
            'Tipificación 2 Nivel',
            'Tipificación 3 Nivel',
            'Obligación/Producto a Gestionar:',
            'Observaciones',
            'Canales Autorizados'
        ];
        fputcsv($output, $headers);
        
        // Datos filtrados con orden correcto
        foreach ($gestiones as $gestion) {
            $row = [
                $this->limpiarDatoCSV($gestion['fecha_gestion']),
                $this->limpiarDatoCSV($gestion['asesor_nombre'] ?? 'No asignado'),
                $this->limpiarDatoCSV($gestion['cedula']),
                $this->limpiarDatoCSV($gestion['cliente_nombre']),
                $this->limpiarDatoCSV($gestion['celular_cliente']),
                $this->limpiarDatoCSV($gestion['base_datos_nombre'] ?? 'No especificada'),
                $this->limpiarDatoCSV($gestion['forma_contacto'] ?? 'llamada'),
                $this->limpiarDatoCSV($gestion['tipificacion_2_nivel']),
                $this->limpiarDatoCSV($gestion['tipificacion_3_nivel']),
                $this->limpiarDatoCSV($gestion['obligacion_texto']),
                $this->limpiarDatoCSV($gestion['comentarios']),
                $this->limpiarDatoCSV($gestion['canales_autorizados_texto'])
            ];
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }

    // Métodos de estilo y descarga eliminados - Ahora usamos CSV

    /**
     * Muestra la vista de reportes y exportación CSV simplificada
     */
    public function reportesExportacion() {
        $page_title = "Exportación CSV - Gestión del Equipo";
        $coordinador_id = $_SESSION['user_id'];
        
        // Obtener asesores asignados al coordinador
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        require 'views/coordinador_reportes_exportacion_simplificado.php';
    }
    
    /**
     * Exporta la lista de clientes a CSV
     */
    public function exportarClientes($fechaInicio = null, $fechaFin = null, $estadoCliente = null) {
        // Desactivar la salida de errores para evitar que se mezclen con el CSV
        error_reporting(0);
        ini_set('display_errors', 0);
        
        $coordinador_id = $_SESSION['user_id'];
        
        // Si no se especifican fechas, usar el mes actual
        if (!$fechaInicio) { $fechaInicio = date('Y-m-01'); }
        if (!$fechaFin) { $fechaFin = date('Y-m-t'); }
        
        // Obtener clientes del coordinador con filtros
        $clientes = $this->clienteModel->getClientesByCoordinadorWithFilters(
            $coordinador_id, 
            $fechaInicio, 
            $fechaFin, 
            $estadoCliente
        );
        
        if (empty($clientes)) {
            // En lugar de redirigir, crear un CSV vacío con mensaje
            $filename = "Clientes_Coordinador_{$fechaInicio}_a_{$fechaFin}_SIN_DATOS.csv";
            
            // Limpiar cualquier salida previa
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Configurar headers para descarga CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Crear archivo CSV
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados del CSV
            $headers = [
                'ID Cliente',
                'Nombre',
                'Cédula',
                'Teléfono',
                'Celular',
                'Email',
                'Ciudad',
                'Estado Cliente',
                'Asesor Asignado',
                'Fecha Creación',
                'Carga Excel'
            ];
            fputcsv($output, $headers);
            
            // Agregar mensaje de que no hay datos
            $row = [
                'No hay clientes para exportar con los filtros seleccionados',
                'Período: ' . $fechaInicio . ' a ' . $fechaFin,
                'Estado: ' . ($estadoCliente ?: 'Todos los estados'),
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ];
            fputcsv($output, $row);
            
            fclose($output);
            exit;
        }
        
        // Generar nombre del archivo
        $filename = "Clientes_Coordinador_{$fechaInicio}_a_{$fechaFin}.csv";
        
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Configurar headers para descarga CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados del CSV
        $headers = [
            'ID Cliente',
            'Nombre',
            'Cédula',
            'Teléfono',
            'Celular',
            'Email',
            'Ciudad',
            'Estado Cliente',
            'Asesor Asignado',
            'Fecha Creación',
            'Carga Excel'
        ];
        fputcsv($output, $headers);
        
        // Datos de los clientes
        foreach ($clientes as $cliente) {
            $asesor = $this->usuarioModel->getUsuarioById($cliente['asesor_id']);
            $asesorNombre = $asesor ? $asesor['nombre_completo'] : 'Sin asignar';
            
            $row = [
                $this->limpiarDatoCSV($cliente['id']),
                $this->limpiarDatoCSV($cliente['nombre']),
                $this->limpiarDatoCSV($cliente['cedula']),
                $this->limpiarDatoCSV($cliente['telefono']),
                $this->limpiarDatoCSV($cliente['celular2'] ?? ''),
                $this->limpiarDatoCSV($cliente['email'] ?? ''),
                $this->limpiarDatoCSV($cliente['ciudad'] ?? ''),
                $this->limpiarDatoCSV($cliente['estado_cliente']),
                $this->limpiarDatoCSV($asesorNombre),
                $this->limpiarDatoCSV($cliente['fecha_creacion']),
                $this->limpiarDatoCSV($cliente['carga_excel_id'])
            ];
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exporta la información de cargas de Excel a CSV
     */
    public function exportarCargas($estadoCarga = null) {
        $coordinador_id = $_SESSION['user_id'];
        
        // Obtener cargas del coordinador
        $cargas = $this->cargaExcelModel->getCargasByCoordinador($coordinador_id);
        
        if (empty($cargas)) {
            $_SESSION['error_message'] = "No hay cargas para exportar.";
            header('Location: index.php?action=reportes_exportacion');
            exit;
        }
        
        // Filtrar por estado si se especifica
        if ($estadoCarga) {
            $cargas = array_filter($cargas, function($carga) use ($estadoCarga) {
                return $carga['estado'] === $estadoCarga;
            });
        }
        
        if (empty($cargas)) {
            $_SESSION['error_message'] = "No hay cargas con el estado seleccionado.";
            header('Location: index.php?action=reportes_exportacion');
            exit;
        }
        
        // Generar nombre del archivo
        $filename = "Cargas_Excel_Coordinador_" . date('Y-m-d') . ".csv";
        
        // Configurar headers para descarga CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados del CSV
        $headers = [
            'ID Carga',
            'Nombre Cargue',
            'Fecha Cargue',
            'Estado',
            'Total Clientes',
            'Clientes Asignados',
            'Clientes Pendientes',
            'Coordinador'
        ];
        fputcsv($output, $headers);
        
        // Datos de las cargas
        foreach ($cargas as $carga) {
            // Calcular estadísticas para cada carga
            $totalClientes = $this->clienteModel->getTotalClientsByCargaIdAndCoordinador($carga['id'], $coordinador_id);
            $clientesAsignados = $this->clienteModel->getTotalClientsAsignadosByCargaIdAndCoordinador($carga['id'], $coordinador_id);
            $clientesPendientes = $totalClientes - $clientesAsignados;
            
            $row = [
                $carga['id'],
                $carga['nombre_cargue'],
                $carga['fecha_cargue'],
                $carga['estado'],
                $totalClientes,
                $clientesAsignados,
                $clientesPendientes,
                'Coordinador ID: ' . $carga['usuario_coordinador_id']
            ];
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }

    public function resultadosEquipo() {
        $page_title = "Resultados del Equipo";
        $coordinador_id = $_SESSION['user_id'];
        
        // Obtener asesores asignados al coordinador
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        // Calcular métricas detalladas para cada asesor
        foreach ($asesores as $key => $asesor) {
            $asesores[$key]['total_clientes'] = $this->clienteModel->getTotalClientesByAsesor($asesor['id']);
            $asesores[$key]['llamadas_realizadas'] = $this->gestionModel->getTotalLlamadasByAsesor($asesor['id']);
            $asesores[$key]['ventas_realizadas'] = $this->gestionModel->getTotalVentasByAsesor($asesor['id']);
            
            // Calcular porcentaje de llamadas
            if ($asesores[$key]['total_clientes'] > 0) {
                $asesores[$key]['porcentaje_llamadas'] = round(($asesores[$key]['llamadas_realizadas'] / $asesores[$key]['total_clientes']) * 100, 1);
            } else {
                $asesores[$key]['porcentaje_llamadas'] = 0;
            }
            
            // Obtener estadísticas por tipo de resultado
            $asesores[$key]['tipificaciones'] = $this->gestionModel->getTipificacionesPorResultado($asesor['id'], 'mes');
            $asesores[$key]['estadisticas_ventas'] = $this->gestionModel->getEstadisticasPorTipoVenta($asesor['id'], 'mes');
            $asesores[$key]['estadisticas_rechazos'] = $this->gestionModel->getEstadisticasPorRechazo($asesor['id'], 'mes');
        }
        
        // Calcular estadísticas generales del equipo
        $total_asesores = count($asesores);
        $total_clientes = array_sum(array_column($asesores, 'total_clientes'));
        $total_llamadas = array_sum(array_column($asesores, 'llamadas_realizadas'));
        $total_ventas = array_sum(array_column($asesores, 'ventas_realizadas'));
        
        // Calcular promedio de cumplimiento del equipo
        $porcentajes_llamadas = array_column($asesores, 'porcentaje_llamadas');
        $promedio_cumplimiento = count($porcentajes_llamadas) > 0 ? round(array_sum($porcentajes_llamadas) / count($porcentajes_llamadas), 1) : 0;
        
        require 'views/coordinador_resultados_equipo.php';
    }


    /**
     * Obtiene los detalles completos de un asesor para mostrar en modal
     * CORREGIDO para usar los nuevos métodos de métricas
     */
    public function getDetallesAsesor() {
        try {
        // Limpiar cualquier output previo
            if (ob_get_level()) {
        ob_clean();
            }
        
        if (!isset($_GET['asesor_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de asesor no proporcionado']);
            return;
        }

        $asesor_id = $this->validarId($_GET['asesor_id'], 'asesor');
        $coordinador_id = $_SESSION['user_id'];

        // Obtener información básica del asesor
        $asesor = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        $asesor = array_filter($asesor, function($a) use ($asesor_id) {
            return $a['id'] == $asesor_id;
        });
        $asesor = reset($asesor);

        if (!$asesor) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permisos para ver este asesor']);
            return;
        }

        // Obtener todos los parámetros de filtro
        $fecha_inicio = $this->getGet('fecha_inicio');
        $fecha_fin = $this->getGet('fecha_fin');
        $filtro_gestion = $this->getGet('gestion');
        $filtro_contacto = $this->getGet('contacto');
        $filtro_tipificacion = $this->getGet('tipificacion');
        $filtro_tipificacion_especifica = $this->getGet('tipificacion_especifica');
            
            // Por defecto, solo mostrar clientes gestionados (con historial de gestiones)
            if (empty($filtro_gestion)) {
                $filtro_gestion = 'gestionado';
            }
        
        // Preparar filtros para el modelo de gestión
        $filtros = [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'gestion' => $filtro_gestion,
            'contacto' => $filtro_contacto,
            'tipificacion' => $filtro_tipificacion,
            'tipificacion_especifica' => $filtro_tipificacion_especifica
        ];
        
        // Obtener gestiones del asesor con filtros usando el nuevo método
        $gestiones = $this->gestionModel->getGestionByAsesorAndFechasConFiltros(
            $asesor_id, $fecha_inicio, $fecha_fin, $filtros
        );
        
            // Ensure gestiones is an array
            if (!is_array($gestiones)) {
                $gestiones = [];
            }
            
            
            // Agregar información de observaciones y fechas de llamada para cada gestión
            foreach ($gestiones as $key => $gestion) {
                if ($gestion['id']) {
                    // Obtener observaciones y fecha de próxima llamada
                    $observaciones = $this->gestionModel->getObservacionesGestion($gestion['id']);
                    $gestiones[$key]['observaciones'] = $observaciones['comentarios'] ?? '';
                    $gestiones[$key]['proxima_fecha'] = $observaciones['proxima_fecha'] ?? '';
                    $gestiones[$key]['proxima_hora'] = $observaciones['proxima_hora'] ?? '';
            } else {
                    $gestiones[$key]['observaciones'] = '';
                    $gestiones[$key]['proxima_fecha'] = '';
                    $gestiones[$key]['proxima_hora'] = '';
                }
            }
        
            // Solo mostrar clientes que han sido gestionados (tienen historial de gestiones)
            // No mostrar clientes sin gestionar

        // Obtener estadísticas del asesor usando el nuevo método
        $estadisticas = $this->gestionModel->getMetricasAsesor($asesor_id, 'total'); // Usar total para obtener todas las gestiones
            
            // Ensure estadisticas is an array
            if (!is_array($estadisticas)) {
                $estadisticas = [
                    'total_clientes' => 0,
                    'total_gestiones' => 0,
                    'ventas_exitosas' => 0,
                    'tasa_conversion' => 0,
                    'contactos_efectivos' => 0,
                    'tiempo_promedio_conversacion' => 0,
                    'total_ventas_monto' => 0,
                    'promedio_venta' => 0
                ];
            }
        
        // Calcular porcentaje de llamadas
        if ($estadisticas['total_clientes'] > 0) {
            $estadisticas['porcentaje_llamadas'] = round(
                ($estadisticas['total_gestiones'] / $estadisticas['total_clientes']) * 100, 1
            );
        } else {
            $estadisticas['porcentaje_llamadas'] = 0;
        }

        // Preparar respuesta
        $response = [
            'asesor' => $asesor,
            'clientes' => $gestiones,
            'estadisticas' => $estadisticas,
            'metricas' => [
                'clientes_filtrados' => count($gestiones),
                'total_gestionados' => $estadisticas['total_gestiones'] ?? 0,
                'total_asignados' => $estadisticas['total_clientes'] ?? 0,
                'porcentaje' => $estadisticas['total_clientes'] > 0 ? 
                    round((count($gestiones) / $estadisticas['total_clientes']) * 100, 1) : 0
            ],
            'filtros' => [
                'gestion' => $filtro_gestion ?? 'todos',
                'tipificacion_especifica' => $filtro_tipificacion_especifica ?? 'todos'
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
            
        } catch (Exception $e) {
            // Log the error
            error_log("Error in getDetallesAsesor: " . $e->getMessage());
            
            // Return error response
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Muestra la vista de descargas y reportes del coordinador
     */
    public function descargas() {
        $page_title = "Descargas y Reportes";
        $coordinador_id = $_SESSION['user_id'];
        
        // Obtener asesores para los filtros
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        // Calcular estadísticas generales
        $total_asesores = count($asesores);
        $total_clientes = 0;
        $total_gestiones = 0;
        $total_ventas = 0;
        
        foreach ($asesores as $asesor) {
            $total_clientes += $this->clienteModel->getTotalClientesByAsesor($asesor['id']);
            $total_gestiones += $this->gestionModel->getTotalLlamadasByAsesor($asesor['id']);
            $total_ventas += $this->gestionModel->getTotalVentasByAsesor($asesor['id']);
        }
        
        require 'views/coordinador_descargas.php';
    }

    /**
     * Muestra la lista de clientes de una carga específica
     */
    public function verClientes() {
        $page_title = "Clientes de la Carga";
        $coordinador_id = $_SESSION['user_id'];
        $carga_id = $this->getGet('carga_id');
        
        if (!$carga_id) {
            header('Location: index.php?action=list_cargas');
            exit;
        }
        
        // Verificar que la carga pertenezca al coordinador
        $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($carga_id, $coordinador_id);
        if (!$carga) {
            header('Location: index.php?action=list_cargas');
            exit;
        }
        
        // Obtener todos los clientes de la carga
        $clientes = $this->clienteModel->getUnassignedClientsByCargaAndCoordinador($carga_id, $coordinador_id);
        $total_clientes = count($clientes);
        
        // Para la vista inicial, mostrar los primeros 200 clientes
        $clientes_vista = array_slice($clientes, 0, 200);
        
        // Obtener asesores para asignación
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        require 'views/coordinador_ver_clientes.php';
    }

    /**
     * Busca clientes por término de búsqueda (AJAX)
     */
    public function buscarClientes() {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Establecer headers para JSON solo si no se han enviado headers
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        try {
            $coordinador_id = $_SESSION['user_id'] ?? null;
            $carga_id = $this->getGet('carga_id');
            $search_term = $this->getGet('search');
            
            // Log para debugging
            error_log("buscarClientes - Coordinador ID: " . $coordinador_id);
            error_log("buscarClientes - Carga ID: " . $carga_id);
            error_log("buscarClientes - Search term: " . $search_term);
            
            if (!$coordinador_id) {
                echo json_encode(['success' => false, 'error' => 'No hay sesión activa']);
                return;
            }
            
            if (!$carga_id) {
                echo json_encode(['success' => false, 'error' => 'Carga no especificada']);
                return;
            }
            
            // Verificar que la carga pertenezca al coordinador
            $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($carga_id, $coordinador_id);
            if (!$carga) {
                echo json_encode(['success' => false, 'error' => 'No tienes acceso a esta carga']);
                return;
            }
            
            // Buscar clientes
            $clientes = $this->clienteModel->buscarClientesPorTermino($carga_id, $coordinador_id, $search_term);
            
            // Preparar datos para la respuesta
            $resultados = [];
            foreach ($clientes as $cliente) {
                $resultados[] = [
                    'id' => $cliente['id'],
                    'nombre' => $cliente['nombre'] ?? 'N/A',
                    'cedula' => $cliente['cedula'] ?? 'N/A',
                    'telefono' => $cliente['telefono'] ?? 'N/A',
                    'celular' => $cliente['celular2'] ?? 'N/A',
                    'email' => $cliente['email'] ?? 'N/A',
                    'estado' => isset($cliente['asesor_id']) && $cliente['asesor_id'] ? 'Asignado' : 'Pendiente'
                ];
            }
            
            $response = [
                'success' => true,
                'clientes' => $resultados,
                'total' => count($resultados),
                'termino' => $search_term
            ];
            
            error_log("buscarClientes - Respuesta: " . json_encode($response));
            echo json_encode($response);
            
        } catch (Exception $e) {
            error_log("Error en buscarClientes: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Muestra la vista para asignar clientes de una carga específica
     */
    public function asignarClientesVista() {
        $page_title = "Asignar Clientes";
        $coordinador_id = $_SESSION['user_id'];
        $carga_id = $this->getGet('carga_id');
        
        if (!$carga_id) {
            header('Location: index.php?action=list_cargas');
            exit;
        }
        
        // Verificar que la carga pertenezca al coordinador
        $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($carga_id, $coordinador_id);
        if (!$carga) {
            header('Location: index.php?action=list_cargas');
            exit;
        }
        
        // Obtener clientes pendientes de asignación
        $clientes_pendientes = $this->clienteModel->getUnassignedClientsByCargaAndCoordinador($carga_id, $coordinador_id);
        
        // Obtener asesores disponibles
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        require 'views/coordinador_asignar_clientes.php';
    }

    /**
     * Muestra la gestión de un asesor específico
     */
    public function verGestionAsesor() {
        $page_title = "Gestión del Asesor";
        $coordinador_id = $_SESSION['user_id'];
        $asesor_id = $this->getGet('asesor_id');
        
        if (!$asesor_id) {
            header('Location: index.php?action=list_cargas');
            exit;
        }
        
        // Verificar que el asesor esté asignado al coordinador
        $asesor = $this->usuarioModel->getUsuarioById($asesor_id);
        if (!$asesor || !$this->usuarioModel->isAsesorAsignadoACoordinador($asesor_id, $coordinador_id)) {
            header('Location: index.php?action=list_cargas');
            exit;
        }
        
        // Obtener métricas del asesor
        $metricas = $this->gestionModel->getMetricasAsesor($asesor_id, 'mes');
        
        // Obtener clientes asignados al asesor
        $clientes = $this->clienteModel->getAssignedClientsForAsesor($asesor_id);
        
        // Obtener gestiones del asesor
        $gestiones = $this->gestionModel->getUltimasGestiones($asesor_id, 50);
        
        require 'views/coordinador_ver_gestion_asesor.php';
    }

    /**
     * Libera todos los clientes de un asesor específico
     */
    public function liberarTodosClientes() {
        try {
            // Verificar que sea un coordinador
            if ($_SESSION['user_role'] !== 'coordinador') {
                throw new Exception("Acceso denegado.");
            }
            
            $coordinador_id = $_SESSION['user_id'];
            $asesor_id = $this->getPost('asesor_id');
            
            if (!$asesor_id) {
                throw new Exception("ID de asesor no proporcionado.");
            }
            
            // Verificar que el asesor esté asignado al coordinador
            $asesor = $this->usuarioModel->getUsuarioById($asesor_id);
            if (!$asesor || $asesor['rol'] !== 'asesor') {
                throw new Exception("El asesor especificado no existe o no es válido.");
            }
            
            // Verificar que el asesor esté asignado al coordinador
            if (!$this->usuarioModel->isAsesorAsignadoACoordinador($asesor_id, $coordinador_id)) {
                throw new Exception("El asesor no está asignado a tu coordinación.");
            }
            
            // Obtener todos los clientes del asesor
            $clientes = $this->clienteModel->getClientesByAsesor($asesor_id);
            
            if (empty($clientes)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'El asesor no tiene clientes asignados para liberar.'
                ]);
                return;
            }
            
            // Contador de clientes liberados
            $clientesLiberados = 0;
            $errores = [];
            
            // Liberar cada cliente
            foreach ($clientes as $cliente) {
                try {
                    $resultado = $this->clienteModel->liberarCliente($cliente['id'], $asesor_id);
                    if ($resultado) {
                        $clientesLiberados++;
                    } else {
                        $errores[] = "Error al liberar cliente ID: " . $cliente['id'];
                    }
                } catch (Exception $e) {
                    $errores[] = "Error al liberar cliente ID: " . $cliente['id'] . ": " . $e->getMessage();
                }
            }
            
            // Log de la acción
            error_log("Liberación masiva de clientes - Coordinador ID: {$coordinador_id}, Asesor ID: {$asesor_id}, Clientes liberados: {$clientesLiberados}, Total clientes: " . count($clientes));
            
            if (empty($errores)) {
                echo json_encode([
                    'success' => true,
                    'message' => "Se liberaron exitosamente {$clientesLiberados} clientes del asesor.",
                    'clientes_liberados' => $clientesLiberados,
                    'total_clientes' => count($clientes)
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => "Se liberaron {$clientesLiberados} clientes, pero hubo algunos errores.",
                    'clientes_liberados' => $clientesLiberados,
                    'total_clientes' => count($clientes),
                    'errores' => $errores
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Error en liberación masiva de clientes: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Filtra las gestiones según los criterios especificados
     */
    private function filtrarGestiones($gestiones, $filtros) {
        $gestionesFiltradas = $gestiones;
        
        // Filtro de gestión (gestionado, no gestionado)
        if (!empty($filtros['gestion'])) {
            if ($filtros['gestion'] === 'gestionado') {
                $gestionesFiltradas = array_filter($gestionesFiltradas, function($g) {
                    return !empty($g['resultado']);
                });
            } elseif ($filtros['gestion'] === 'no_gestionado') {
                $gestionesFiltradas = array_filter($gestionesFiltradas, function($g) {
                    return empty($g['resultado']);
                });
            }
        }
        
        // Filtro de contacto (contactado, no contactado)
        if (!empty($filtros['contacto'])) {
            if ($filtros['contacto'] === 'contactado') {
                $gestionesFiltradas = array_filter($gestionesFiltradas, function($g) {
                    return !empty($g['resultado']);
                });
            } elseif ($filtros['contacto'] === 'no_contactado') {
                $gestionesFiltradas = array_filter($gestionesFiltradas, function($g) {
                    return empty($g['resultado']);
                });
            }
        }
        
        // Filtro de tipificación específica
        if (!empty($filtros['tipificacion']) && $filtros['tipificacion'] !== 'todos') {
            $gestionesFiltradas = array_filter($gestionesFiltradas, function($g) use ($filtros) {
                return ($g['resultado'] ?? '') === $filtros['tipificacion'];
            });
        }
        
        // Filtro de fechas de creación del cliente
        if (!empty($filtros['fecha_creacion_inicio'])) {
            $gestionesFiltradas = array_filter($gestionesFiltradas, function($g) use ($filtros) {
                return !empty($g['fecha_creacion']) && $g['fecha_creacion'] >= $filtros['fecha_creacion_inicio'];
            });
        }
        
        if (!empty($filtros['fecha_creacion_fin'])) {
            $gestionesFiltradas = array_filter($gestionesFiltradas, function($g) use ($filtros) {
                return !empty($g['fecha_creacion']) && $g['fecha_creacion'] <= $filtros['fecha_creacion_fin'];
            });
        }
        
        return array_values($gestionesFiltradas);
    }
    
    /**
     * Limpia los datos para exportación CSV - Elimina espacios extra y caracteres problemáticos
     */
    private function limpiarDatoCSV($dato) {
        if ($dato === null || $dato === '') {
            return '';
        }
        
        // Convertir a string si no lo es
        $dato = (string) $dato;
        
        // Eliminar espacios al inicio y final
        $dato = trim($dato);
        
        // Eliminar espacios múltiples
        $dato = preg_replace('/\s+/', ' ', $dato);
        
        // Eliminar caracteres problemáticos para Excel
        $dato = str_replace(["\r", "\n", "\t"], ' ', $dato);
        
        // Eliminar espacios extra que puedan quedar
        $dato = preg_replace('/\s+/', ' ', $dato);
        
        return $dato;
    }

    public function crearNuevaBase() {
        $page_title = "Crear Nueva Base de Datos";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_excel_nueva'])) {
            $nombreBaseDatos = $this->getPost('nombre_base_datos');
            $usuarioCoordinadorId = $_SESSION['user_id'];
            
            // Verificar el tamaño del archivo
            $fileSize = $_FILES['archivo_excel_nueva']['size'];
            $maxFileSize = 500 * 1024 * 1024; // 500MB para archivos CSV grandes
            
            if ($fileSize > $maxFileSize) {
                $_SESSION['error_message'] = "❌ Error en la carga: El archivo es demasiado grande. El tamaño máximo permitido es 500MB.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Verificar tipo de archivo
            $fileType = strtolower(pathinfo($_FILES['archivo_excel_nueva']['name'], PATHINFO_EXTENSION));
            if ($fileType !== 'csv') {
                $_SESSION['error_message'] = "❌ Error en la carga: Solo se permiten archivos CSV.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Verificar que el nombre no esté en uso
            $cargaExistente = $this->cargaExcelModel->getCargaByNombre($nombreBaseDatos, $usuarioCoordinadorId);
            if ($cargaExistente) {
                $_SESSION['error_message'] = "❌ Error: Ya existe una base de datos con el nombre '$nombreBaseDatos'.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Procesar el archivo CSV
            $handle = fopen($_FILES['archivo_excel_nueva']['tmp_name'], 'r');
            if (!$handle) {
                $_SESSION['error_message'] = "❌ Error en la carga: No se pudo abrir el archivo CSV.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Detectar delimitador automáticamente
            $first_line = fgets($handle);
            rewind($handle);
            
            $delimiters = [',', ';', "\t"];
            $delimiter = ',';
            $max_count = 0;
            
            foreach ($delimiters as $d) {
                $count = substr_count($first_line, $d);
                if ($count > $max_count) {
                    $max_count = $count;
                    $delimiter = $d;
                }
            }
            
            // Leer encabezados con el delimitador detectado
            $headers = fgetcsv($handle, 0, $delimiter);
            if (!$headers) {
                $_SESSION['error_message'] = "❌ Error en la carga: El archivo CSV está vacío o no tiene encabezados válidos.";
                fclose($handle);
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Mapear columnas por nombre (insensible a mayúsculas/minúsculas)
            $columnMap = [];
            foreach ($headers as $index => $header) {
                $headerClean = trim(strtolower($header));
                $columnMap[$headerClean] = $index;
            }
            
            // Verificar columnas obligatorias
            $columnasObligatorias = ['nombre', 'cedula', 'telefono'];
            $columnasFaltantes = [];
            
            foreach ($columnasObligatorias as $columna) {
                $encontrada = false;
                foreach ($columnMap as $header => $index) {
                    if (strpos($header, $columna) !== false) {
                        $encontrada = true;
                        break;
                    }
                }
                if (!$encontrada) {
                    $columnasFaltantes[] = $columna;
                }
            }
            
            if (!empty($columnasFaltantes)) {
                $_SESSION['error_message'] = "❌ Error en la carga: El archivo CSV debe contener las columnas obligatorias: Nombre, Cédula y Teléfono.";
                fclose($handle);
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Crear nueva base de datos independiente
            $cargaId = $this->cargaExcelModel->crearBaseDatosIndependiente($nombreBaseDatos, $usuarioCoordinadorId);
            if (!$cargaId) {
                $_SESSION['error_message'] = "❌ Error en la carga: No se pudo crear la nueva base de datos.";
                fclose($handle);
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Contadores para el resumen
            $clientesNuevos = 0;
            $clientesDuplicados = 0;
            $clientesAgregados = 0;
            $errores = 0;
            
            // Usar el método existente para procesar clientes con obligaciones
            $clientes = $this->leerArchivoCSV($_FILES['archivo_excel_nueva']['tmp_name']);
            
            if (empty($clientes)) {
                $_SESSION['error_message'] = "❌ Error en la carga: No se encontraron clientes válidos en el archivo CSV.";
                fclose($handle);
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Procesar clientes usando el método que maneja obligaciones
            $resultado = $this->procesarClientesCSV($clientes, $cargaId, $usuarioCoordinadorId);
            
            $clientesNuevos = $resultado['nuevos'];
            $clientesDuplicados = $resultado['duplicados'];
            $obligacionesCreadas = $resultado['obligaciones_creadas'];
            $obligacionesDuplicadas = $resultado['obligaciones_duplicadas'];
            $clientesAgregados = $clientesNuevos + $clientesDuplicados;
            
            fclose($handle);
            
            // Mensaje de éxito
            $mensaje = "✅ Base de datos '$nombreBaseDatos' creada exitosamente!<br>";
            $mensaje .= "📊 <strong>Resumen:</strong><br>";
            $mensaje .= "• Clientes nuevos: $clientesNuevos<br>";
            $mensaje .= "• Clientes duplicados: $clientesDuplicados<br>";
            $mensaje .= "• Total procesados: $clientesAgregados<br>";
            $mensaje .= "• Obligaciones creadas: $obligacionesCreadas<br>";
            if ($obligacionesDuplicadas > 0) {
                $mensaje .= "• Obligaciones duplicadas: $obligacionesDuplicadas<br>";
            }
            
            $_SESSION['success_message'] = $mensaje;
            header('Location: index.php?action=list_cargas');
            exit;
        }
        
        // Si no es POST, redirigir a la gestión integrada
        header('Location: index.php?action=gestion_cargas');
        exit;
    }

    public function getAsesoresDisponibles() {
        $cargaId = $this->getGet('carga_id', 0);
        $coordinadorId = $_SESSION['user_id'];
        
        $asesores = $this->cargaExcelModel->getAsesoresDisponibles($coordinadorId);
        
        header('Content-Type: application/json');
        echo json_encode($asesores);
        exit;
    }

    public function getAsesoresAsignados() {
        // Limpiar cualquier output previo
        ob_clean();
        
        $cargaId = $this->getGet('carga_id', 0);
        $coordinadorId = $_SESSION['user_id'];
        
        // Verificar que la carga pertenece al coordinador
        $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($cargaId, $coordinadorId);
        if (!$carga) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
        
        $estadisticas = $this->cargaExcelModel->getEstadisticasBaseDatos($cargaId);
        
        header('Content-Type: application/json');
        echo json_encode($estadisticas['asesores_asignados']);
        exit;
    }

    public function asignarAsesorBase() {
        // Limpiar cualquier output previo
        ob_clean();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cargaId = $this->getPost('carga_id', 0);
            $asesorId = $this->getPost('asesor_id', 0);
            $coordinadorId = $_SESSION['user_id'];
            
            // Verificar que la carga pertenece al coordinador
            $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($cargaId, $coordinadorId);
            if (!$carga) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Base de datos no encontrada']);
                exit;
            }
            
            // Verificar que el asesor está asignado al coordinador
            $asesores = $this->cargaExcelModel->getAsesoresDisponibles($coordinadorId);
            $asesorValido = false;
            foreach ($asesores as $asesor) {
                if ($asesor['id'] == $asesorId) {
                    $asesorValido = true;
                    break;
                }
            }
            
            if (!$asesorValido) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Asesor no válido']);
                exit;
            }
            
            $asignacionesCreadas = $this->cargaExcelModel->asignarAsesorABaseDatos($cargaId, $asesorId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'asignaciones_creadas' => $asignacionesCreadas,
                'message' => "Se asignaron $asignacionesCreadas clientes al asesor"
            ]);
            exit;
        }
    }

    public function liberarAsesorBase() {
        // Limpiar cualquier output previo
        ob_clean();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $cargaId = $this->getPost('carga_id', 0);
                $asesorId = $this->getPost('asesor_id', 0);
                $coordinadorId = $_SESSION['user_id'];

                // Verificar que la carga pertenece al coordinador
                $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($cargaId, $coordinadorId);
                if (!$carga) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Base de datos no encontrada']);
                    exit;
                }

                $asignacionesActualizadas = $this->cargaExcelModel->liberarAsesorDeBaseDatos($cargaId, $asesorId);
    
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'asignaciones_actualizadas' => $asignacionesActualizadas,
                    'message' => "Se liberaron $asignacionesActualizadas asignaciones del asesor"
                ]);
                exit;
            } catch (Exception $e) {
                error_log("Error al liberar asesor de base de datos: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Error interno del servidor al liberar el asesor'
                ]);
                exit;
            }
        }
    }

    public function eliminarBaseDatos() {
        // Limpiar cualquier output previo
        ob_clean();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cargaId = $_POST['carga_id'] ?? 0;
            $coordinadorId = $_SESSION['user_id'];
            
            // Verificar que la carga pertenece al coordinador
            $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($cargaId, $coordinadorId);
            if (!$carga) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Base de datos no encontrada']);
                exit;
            }
            
            // Solo permitir eliminar bases de datos independientes
            if (isset($carga['tipo_base_datos']) && $carga['tipo_base_datos'] !== 'independiente') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar la base consolidada']);
                exit;
            }
            
            try {
                // Iniciar transacción
                $this->pdo->beginTransaction();

                // Paso 1: Eliminar historial de gestiones primero
                $stmt = $this->pdo->prepare("
                    DELETE hg FROM historial_gestion hg
                    INNER JOIN asignaciones_clientes ac ON hg.asignacion_id = ac.id
                    INNER JOIN clientes c ON ac.cliente_id = c.id
                    WHERE c.carga_excel_id = ?
                ");
                $stmt->execute([$cargaId]);

                // Paso 2: Eliminar gestiones de los clientes
                $stmt = $this->pdo->prepare("DELETE FROM gestiones WHERE cliente_id IN (SELECT id FROM clientes WHERE carga_excel_id = ?)");
                $stmt->execute([$cargaId]);

                // Paso 3: Eliminar asignaciones
                $stmt = $this->pdo->prepare("
                    DELETE ac FROM asignaciones_clientes ac
                    INNER JOIN clientes c ON ac.cliente_id = c.id
                    WHERE c.carga_excel_id = ?
                ");
                $stmt->execute([$cargaId]);

                // Paso 4: Eliminar clientes de la carga
                $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE carga_excel_id = ?");
                $stmt->execute([$cargaId]);

                // Paso 5: Eliminar la carga
                $stmt = $this->pdo->prepare("DELETE FROM cargas_excel WHERE id = ?");
                $stmt->execute([$cargaId]);

                // Confirmar transacción
                $this->pdo->commit();

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Base de datos eliminada exitosamente']);
                exit;

            } catch (Exception $e) {
                // Revertir transacción en caso de error
                $this->pdo->rollBack();
                error_log("Error eliminando base de datos: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al eliminar la base de datos: ' . $e->getMessage()]);
                exit;
            }
        }
    }

    /**
     * Agrega clientes a una base de datos existente
     */
    public function agregarABaseExistente() {
        $page_title = "Agregar Clientes a Base Existente";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_excel_existente'])) {
            $cargaId = $_POST['carga_id'];
            $usuarioCoordinadorId = $_SESSION['user_id'];
            
            // Verificar que la carga pertenezca al coordinador
            $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($cargaId, $usuarioCoordinadorId);
            if (!$carga) {
                $_SESSION['error_message'] = "❌ Error: No tienes acceso a esta base de datos o no existe.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Verificar el tamaño del archivo
            $fileSize = $_FILES['archivo_excel_existente']['size'];
            $maxFileSize = 100 * 1024 * 1024; // 100MB
            
            if ($fileSize > $maxFileSize) {
                $_SESSION['error_message'] = "❌ Error en la carga: El archivo es demasiado grande. El tamaño máximo permitido es 500MB.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Verificar tipo de archivo
            $fileType = strtolower(pathinfo($_FILES['archivo_excel_existente']['name'], PATHINFO_EXTENSION));
            if ($fileType !== 'csv') {
                $_SESSION['error_message'] = "❌ Error en la carga: Solo se permiten archivos CSV.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Procesar el archivo CSV
            $handle = fopen($_FILES['archivo_excel_existente']['tmp_name'], 'r');
            if (!$handle) {
                $_SESSION['error_message'] = "❌ Error en la carga: No se pudo abrir el archivo CSV.";
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Detectar delimitador automáticamente
            $first_line = fgets($handle);
            rewind($handle);
            
            $delimiters = [',', ';', "\t"];
            $delimiter = ',';
            $max_count = 0;
            
            foreach ($delimiters as $d) {
                $count = substr_count($first_line, $d);
                if ($count > $max_count) {
                    $max_count = $count;
                    $delimiter = $d;
                }
            }
            
            // Leer encabezados con el delimitador detectado
            $headers = fgetcsv($handle, 0, $delimiter);
            if (!$headers) {
                $_SESSION['error_message'] = "❌ Error en la carga: El archivo CSV está vacío o no tiene encabezados válidos.";
                fclose($handle);
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Mapear columnas por nombre (insensible a mayúsculas/minúsculas)
            $columnMap = [];
            foreach ($headers as $index => $header) {
                $headerClean = trim(strtolower($header));
                $columnMap[$headerClean] = $index;
            }
            
            // Verificar columnas obligatorias
            $columnasObligatorias = ['nombre', 'cedula', 'telefono'];
            $columnasFaltantes = [];
            
            foreach ($columnasObligatorias as $columna) {
                $encontrada = false;
                foreach ($columnMap as $header => $index) {
                    if (strpos($header, $columna) !== false) {
                        $encontrada = true;
                        break;
                    }
                }
                if (!$encontrada) {
                    $columnasFaltantes[] = $columna;
                }
            }
            
            if (!empty($columnasFaltantes)) {
                $_SESSION['error_message'] = "❌ Error en la carga: El archivo CSV debe contener las columnas obligatorias: Nombre, Cédula y Teléfono.";
                fclose($handle);
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Usar el método existente para procesar clientes con obligaciones
            $clientes = $this->leerArchivoCSV($_FILES['archivo_excel_existente']['tmp_name']);
            
            if (empty($clientes)) {
                $_SESSION['error_message'] = "❌ Error en la carga: No se encontraron clientes válidos en el archivo CSV.";
                fclose($handle);
                header('Location: index.php?action=gestion_cargas');
                exit;
            }
            
            // Procesar clientes usando el método que maneja obligaciones
            $resultado = $this->procesarClientesCSV($clientes, $cargaId, $usuarioCoordinadorId);
            
            $clientesNuevos = $resultado['nuevos'];
            $clientesDuplicados = $resultado['duplicados'];
            $obligacionesCreadas = $resultado['obligaciones_creadas'];
            $obligacionesDuplicadas = $resultado['obligaciones_duplicadas'];
            $clientesAgregados = $clientesNuevos + $clientesDuplicados;
            
            fclose($handle);
            
            // Mensaje de éxito con resumen
            $mensaje = "✅ <strong>Clientes agregados exitosamente a '{$carga['nombre_cargue']}'</strong><br><br>";
            $mensaje .= "📊 <strong>Resumen de la carga:</strong><br>";
            $mensaje .= "• <strong>Clientes nuevos:</strong> $clientesNuevos<br>";
            $mensaje .= "• <strong>Clientes duplicados (ya existían):</strong> $clientesDuplicados<br>";
            $mensaje .= "• <strong>Total procesados:</strong> $clientesAgregados<br>";
            $mensaje .= "• <strong>Obligaciones creadas:</strong> $obligacionesCreadas<br>";
            if ($obligacionesDuplicadas > 0) {
                $mensaje .= "• <strong>Obligaciones duplicadas:</strong> $obligacionesDuplicadas<br>";
            }
            
            $_SESSION['success_message'] = $mensaje;
            header('Location: index.php?action=list_cargas');
            exit;
        }
        
        // Si no es POST, redirigir a la gestión de cargas
        header('Location: index.php?action=gestion_cargas');
        exit;
    }

    /**
     * Gestiona el estado de las bases de datos (habilitar/deshabilitar)
     */
    public function gestionarEstadoBases() {
        $page_title = "Gestionar Estado de Bases de Datos";
        $coordinador_id = $_SESSION['user_id'];
        
        // Obtener todas las bases de datos del coordinador (habilitadas y deshabilitadas)
        $bases_datos = $this->clienteModel->getCargasByCoordinador($coordinador_id, false);
        
        include 'views/gestionar_estado_bases.php';
    }

    /**
     * Cambia el estado de una base de datos
     */
    public function cambiarEstadoBase() {
        // Limpiar cualquier output previo
        ob_clean();
        
        try {
            $coordinador_id = $_SESSION['user_id'];
            $carga_id = (int)$_POST['carga_id'];
            $nuevo_estado = $_POST['nuevo_estado'];
            
            // Verificar que la carga pertenezca al coordinador
            $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($carga_id, $coordinador_id);
            if (!$carga) {
                throw new Exception("La base de datos no es válida.");
            }
            
            // Cambiar el estado
            if ($this->clienteModel->cambiarEstadoCarga($carga_id, $nuevo_estado)) {
                $_SESSION['success_message'] = "Estado de la base de datos actualizado correctamente.";
            } else {
                throw new Exception("Error al actualizar el estado de la base de datos.");
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
        }
        
        header("Location: index.php?action=gestionar_estado_bases");
        exit;
    }

    /**
     * Busca bases de datos por nombre
     */
    public function buscarBasesDatos() {
        // Limpiar cualquier output previo
        ob_clean();
        
        $coordinador_id = $_SESSION['user_id'];
        $termino_busqueda = $_POST['termino_busqueda'] ?? '';
        $solo_habilitadas = $_POST['solo_habilitadas'] ?? 'true';
        
        $bases_datos = $this->clienteModel->buscarCargasPorNombre(
            $coordinador_id, 
            $termino_busqueda, 
            $solo_habilitadas === 'true'
        );
        
        header('Content-Type: application/json');
        echo json_encode($bases_datos);
        exit;
    }

    /**
     * Transfiere un recordatorio de un asesor a otro
     */
    public function transferirRecordatorio() {
        try {
            // Verificar que sea un coordinador
            if ($_SESSION['user_role'] !== 'coordinador') {
                throw new Exception("Acceso denegado.");
            }

            $coordinador_id = $_SESSION['user_id'];
            $cliente_id = $_POST['cliente_id'] ?? null;
            $asesor_origen_id = $_POST['asesor_origen_id'] ?? null;
            $asesor_destino_id = $_POST['asesor_destino_id'] ?? null;

            if (!$cliente_id || !$asesor_origen_id || !$asesor_destino_id) {
                throw new Exception("Datos incompletos para la transferencia.");
            }

            // Verificar que el asesor origen esté asignado al coordinador
            if (!$this->usuarioModel->isAsesorAsignadoACoordinador($asesor_origen_id, $coordinador_id)) {
                throw new Exception("El asesor origen no está asignado a tu coordinación.");
            }

            // Verificar que el asesor destino esté asignado al coordinador
            if (!$this->usuarioModel->isAsesorAsignadoACoordinador($asesor_destino_id, $coordinador_id)) {
                throw new Exception("El asesor destino no está asignado a tu coordinación.");
            }

            // Verificar que el cliente pertenezca a una carga del coordinador
            $cliente = $this->clienteModel->getClienteByIdAndCoordinador($cliente_id, $coordinador_id);
            if (!$cliente) {
                throw new Exception("El cliente no pertenece a tus cargas.");
            }

            // Realizar la transferencia
            $resultado = $this->clienteModel->traspasarCliente($cliente_id, $asesor_destino_id, $asesor_origen_id);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Recordatorio transferido exitosamente.'
                ]);
            } else {
                throw new Exception("Error al transferir el recordatorio.");
            }

        } catch (Exception $e) {
            error_log("Error en transferirRecordatorio: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ===== NUEVOS MÉTODOS PARA EL SISTEMA DE TAREAS =====

    /**
     * Vista para gestionar tareas específicas
     */
    public function gestionarTareas() {
        $page_title = "Gestión de Tareas Específicas";
        $coordinador_id = $_SESSION['user_id'];
        
        // Obtener cargas del coordinador
        $cargas = $this->cargaExcelModel->getCargasByCoordinador($coordinador_id);
        
        // Calcular estadísticas para cada carga
        foreach ($cargas as &$carga) {
            $carga['total_clientes'] = $this->clienteModel->getTotalClientsByCargaId($carga['id']);
        }
        
        // Obtener asesores asignados
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        // Obtener tareas existentes
        $tareas = $this->tareaModel->getTareasByCoordinador($coordinador_id);
        
        // Obtener estadísticas
        $estadisticas = $this->tareaModel->getEstadisticasTareas($coordinador_id);
        
        require 'views/coordinador_gestionar_tareas.php';
    }

    /**
     * Crear nueva tarea específica
     */
    public function crearTarea() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=gestionar_tareas');
            exit;
        }

        $coordinador_id = $_SESSION['user_id'];
        
        // Validar datos
        $asesor_id = $_POST['asesor_id'] ?? null;
        $carga_id = $_POST['carga_id'] ?? null;
        $cantidad_clientes = intval($_POST['cantidad_clientes'] ?? 0);

        if (!$asesor_id || !$carga_id || $cantidad_clientes <= 0) {
            $_SESSION['error_message'] = 'Faltan datos requeridos para crear la tarea';
            header('Location: index.php?action=gestionar_tareas');
            exit;
        }

        // Verificar que el asesor está asignado a esta base
        $asesoresBase = $this->tareaModel->getAsesoresByBase($carga_id);
        $asesorValido = false;
        foreach ($asesoresBase as $asesor) {
            if ($asesor['id'] == $asesor_id) {
                $asesorValido = true;
                break;
            }
        }

        if (!$asesorValido) {
            $_SESSION['error_message'] = 'El asesor seleccionado no está asignado a esta base';
            header('Location: index.php?action=gestionar_tareas');
            exit;
        }

        // Obtener clientes no gestionados de la base
        $clientesNoGestionados = $this->tareaModel->getClientesNoGestionadosBase($carga_id, $cantidad_clientes);

        if (empty($clientesNoGestionados)) {
            $_SESSION['error_message'] = 'No hay clientes no gestionados disponibles en esta base';
            header('Location: index.php?action=gestionar_tareas');
            exit;
        }

        $datos = [
            'asesor_id' => $asesor_id,
            'carga_id' => $carga_id,
            'cliente_ids' => array_column($clientesNoGestionados, 'id'),
            'descripcion' => "Tarea de {$cantidad_clientes} clientes de la base",
            'prioridad' => 'media',
            'fecha_vencimiento' => null,
            'coordinador_id' => $coordinador_id
        ];

        $tarea_id = $this->tareaModel->crearTarea($datos);

        if ($tarea_id) {
            $_SESSION['success_message'] = "Tarea creada exitosamente. Se asignaron {$cantidad_clientes} clientes no gestionados al asesor.";
        } else {
            $_SESSION['error_message'] = 'Error al crear la tarea';
        }

        header('Location: index.php?action=gestionar_tareas');
        exit;
    }

    /**
     * Asignar base completa a asesor
     */
    public function asignarBaseCompleta() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=list_cargas');
            exit;
        }

        $coordinador_id = $_SESSION['user_id'];
        $carga_id = $_POST['carga_id'] ?? null;
        $asesor_id = $_POST['asesor_id'] ?? null;

        if (!$carga_id || !$asesor_id) {
            $_SESSION['error_message'] = 'Faltan datos requeridos';
            header('Location: index.php?action=list_cargas');
            exit;
        }

        $resultado = $this->cargaExcelModel->asignarAsesorABaseDatos($carga_id, $asesor_id);

        if ($resultado) {
            $_SESSION['success_message'] = 'Base asignada exitosamente. El asesor tendrá acceso completo a todos los clientes de esta base.';
        } else {
            $_SESSION['error_message'] = 'Error al asignar la base';
        }

        header('Location: index.php?action=list_cargas');
        exit;
    }

    /**
     * Liberar base de asesor
     */
    public function liberarBase() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=list_cargas');
            exit;
        }

        $carga_id = $_POST['carga_id'] ?? null;
        $asesor_id = $_POST['asesor_id'] ?? null;

        if (!$carga_id || !$asesor_id) {
            $_SESSION['error_message'] = 'Faltan datos requeridos';
            header('Location: index.php?action=list_cargas');
            exit;
        }

        $resultado = $this->cargaExcelModel->liberarAsesorDeBaseDatos($carga_id, $asesor_id);

        if ($resultado) {
            $_SESSION['success_message'] = 'Base liberada exitosamente';
        } else {
            $_SESSION['error_message'] = 'Error al liberar la base';
        }

        header('Location: index.php?action=list_cargas');
        exit;
    }

    /**
     * Obtener clientes de una carga para selección en tareas
     */
    public function getClientesCarga() {
        $carga_id = $_GET['carga_id'] ?? null;
        $coordinador_id = $_SESSION['user_id'];

        if (!$carga_id) {
            echo json_encode(['error' => 'ID de carga requerido']);
            exit;
        }

        // Verificar que la carga pertenece al coordinador
        $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($carga_id, $coordinador_id);
        if (!$carga) {
            echo json_encode(['error' => 'No tienes permisos para acceder a esta carga']);
            exit;
        }

        $clientes = $this->clienteModel->getClientsByCargaId($carga_id);
        
        echo json_encode([
            'success' => true,
            'clientes' => $clientes,
            'total' => count($clientes)
        ]);
        exit;
    }

    /**
     * Obtener asesores disponibles para una carga
     */
    public function getAsesoresDisponiblesCarga() {
        ob_clean();
        
        $carga_id = $_GET['carga_id'] ?? null;
        $coordinador_id = $_SESSION['user_id'];

        if (!$carga_id) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID de carga requerido']);
            exit;
        }

        // Obtener asesores del coordinador
        $asesores = $this->usuarioModel->getAsesoresByCoordinador($coordinador_id);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'asesores' => $asesores
        ]);
        exit;
    }

    /**
     * Obtener asesores asignados a una base específica
     */
    public function getAsesoresBase() {
        // Limpiar cualquier output previo
        ob_clean();
        
        // Configurar headers para JSON
        header('Content-Type: application/json');
        
        try {
            $carga_id = $_GET['carga_id'] ?? null;
            $coordinador_id = $_SESSION['user_id'];

            if (!$carga_id) {
                echo json_encode(['error' => 'ID de carga requerido']);
                exit;
            }

            // Verificar que la carga pertenece al coordinador
            $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($carga_id, $coordinador_id);
            if (!$carga) {
                echo json_encode(['error' => 'No tienes permisos para acceder a esta carga']);
                exit;
            }

            // Obtener asesores asignados a esta base específica
            $asesores = $this->tareaModel->getAsesoresByBase($carga_id);
            
            echo json_encode([
                'success' => true,
                'asesores' => $asesores
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Obtener información de clientes no gestionados en una base
     */
    public function getClientesNoGestionados() {
        // Limpiar cualquier output previo
        ob_clean();
        
        // Configurar headers para JSON
        header('Content-Type: application/json');
        
        try {
            $carga_id = $_GET['carga_id'] ?? null;
            $coordinador_id = $_SESSION['user_id'];

            if (!$carga_id) {
                echo json_encode(['error' => 'ID de carga requerido']);
                exit;
            }

            // Verificar que la carga pertenece al coordinador
            $carga = $this->cargaExcelModel->getCargaByIdAndCoordinador($carga_id, $coordinador_id);
            if (!$carga) {
                echo json_encode(['error' => 'No tienes permisos para acceder a esta carga']);
                exit;
            }

            // Obtener estadísticas de clientes
            $estadisticas = $this->tareaModel->getEstadisticasClientesBase($carga_id);
            
            echo json_encode([
                'success' => true,
                'total_clientes' => $estadisticas['total_clientes'],
                'total_no_gestionados' => $estadisticas['total_no_gestionados']
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Obtener bases asignadas a un asesor
     */
    public function getBasesAsignadasAsesor() {
        $asesor_id = $_GET['asesor_id'] ?? null;
        $coordinador_id = $_SESSION['user_id'];

        if (!$asesor_id) {
            echo json_encode(['error' => 'ID de asesor requerido']);
            exit;
        }

        // Verificar que el asesor pertenece al coordinador
        $asesor = $this->usuarioModel->getUsuarioById($asesor_id);
        if (!$asesor || $asesor['coordinador_id'] != $coordinador_id) {
            echo json_encode(['error' => 'No tienes permisos para acceder a este asesor']);
            exit;
        }

        $bases = $this->tareaModel->getBasesAsignadasByAsesor($asesor_id);
        
        echo json_encode([
            'success' => true,
            'bases' => $bases
        ]);
        exit;
    }

    /**
     * Actualizar estado de tarea
     */
    public function actualizarEstadoTarea() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $tarea_id = $_POST['tarea_id'] ?? null;
        $nuevo_estado = $_POST['estado'] ?? null;
        $coordinador_id = $_SESSION['user_id'];

        if (!$tarea_id || !$nuevo_estado) {
            echo json_encode(['error' => 'Faltan datos requeridos']);
            exit;
        }

        // Verificar que la tarea pertenece al coordinador
        $tareas = $this->tareaModel->getTareasByCoordinador($coordinador_id);
        $tarea_existe = false;
        foreach ($tareas as $tarea) {
            if ($tarea['id'] == $tarea_id) {
                $tarea_existe = true;
                break;
            }
        }

        if (!$tarea_existe) {
            echo json_encode(['error' => 'No tienes permisos para modificar esta tarea']);
            exit;
        }

        $resultado = $this->tareaModel->actualizarEstadoTarea($tarea_id, $nuevo_estado, $coordinador_id);

        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } else {
            echo json_encode(['error' => 'Error al actualizar el estado']);
        }
        exit;
    }
}
?>