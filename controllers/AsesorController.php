<?php
// Archivo: AsesorController.php
// Lógica para el asesor

require_once 'BaseController.php';
require_once 'models/ProductoClienteModel.php';

class AsesorController extends BaseController {
    public function __construct($pdo) {
        parent::__construct($pdo);
    }
    
    public function dashboard() {
        $page_title = "Dashboard Profesional del Asesor";
        $asesor_id = $_SESSION['user_id'];
        
        // Obtener período seleccionado (día, semana, mes)
        $periodo = $_GET['periodo'] ?? 'dia';
        
        // OPTIMIZACIÓN: Obtener todas las métricas en una sola consulta optimizada
        $metricasCompletas = $this->gestionModel->getMetricasDashboardOptimizadas($asesor_id, $periodo);
        
        // Obtener estadísticas de tareas pendientes (optimizado)
        $tareasPendientes = $this->tareaModel->getTareasPendientesByAsesor($asesor_id);
        $totalTareasPendientes = count($tareasPendientes);
        
        // Calcular total de clientes en tareas (optimizado)
        $totalClientesEnTareas = 0;
        if (!empty($tareasPendientes)) {
            $totalClientesEnTareas = array_sum(array_map(function($tarea) {
                return count($tarea['cliente_ids'] ?? []);
            }, $tareasPendientes));
        }
        
        // Obtener métricas de acuerdos de pago (NUEVA MÉTRICA)
        $acuerdosPago = $this->gestionModel->getTotalAcuerdosPago($asesor_id);
        $totalRecaudadoAcuerdos = $this->gestionModel->getTotalRecaudadoAcuerdos($asesor_id);
        
        // Obtener datos de seguimiento (optimizado - solo los necesarios)
        $llamadasPendientes = $this->gestionModel->getLlamadasPendientesHoy($asesor_id);
        $totalLlamadasPendientesHoy = count($llamadasPendientes);
        
        // Determinar el total de clientes real
        $total_clientes = $totalClientesEnTareas > 0 ? $totalClientesEnTareas : $metricasCompletas['total_clientes_gestionados'];
        
        // Datos optimizados para el dashboard
        $datos_dashboard = [
            'total_clientes' => $total_clientes,
            'clientes_gestionados' => $metricasCompletas['total_clientes_gestionados'],
            'total_recaudado' => $metricasCompletas['total_recaudado'],
            'total_acuerdos_pago' => $acuerdosPago,
            'total_recaudado_acuerdos' => $totalRecaudadoAcuerdos,
            'periodo' => $periodo,
            'llamadas_pendientes' => $llamadasPendientes,
            'total_llamadas_pendientes_hoy' => $totalLlamadasPendientesHoy,
            'total_tareas_pendientes' => $totalTareasPendientes,
            'clientes_pendientes_tareas' => $totalClientesEnTareas,
            'metricas_detalladas' => $metricasCompletas
        ];

        require 'views/asesor_dashboard.php';
    }

    public function misClientes() {
        $page_title = "Mis Clientes";
        $asesorId = $_SESSION['user_id'];
        
        // OPTIMIZACIÓN: Verificar tareas de forma más eficiente
        $tieneTareasPendientes = $this->tareaModel->tieneTareasPendientes($asesorId);
        
        if ($tieneTareasPendientes) {
            // OPTIMIZACIÓN: Obtener todos los datos en una sola consulta
            $todosClientes = $this->tareaModel->getClientesTareasConMetricas($asesorId);
        } else {
            // Si no tiene tareas pendientes, mostrar mensaje de "No tienes tareas pendientes"
            $todosClientes = [];
        }
        
        // Parámetros de paginación
        $por_pagina = 10; // 10 clientes por página
        $pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
        $offset = ($pagina_actual - 1) * $por_pagina;
        
        // Filtrar por cédula si se proporciona un término de búsqueda
        $terminoBusqueda = $_GET['buscar'] ?? '';
        if (!empty($terminoBusqueda)) {
            $todosClientes = array_filter($todosClientes, function($cliente) use ($terminoBusqueda) {
                return stripos($cliente['cedula'], $terminoBusqueda) !== false;
            });
        }
        
        // Separar clientes por estado para las pestañas
        $clientes_pendientes = array_filter($todosClientes, function($cliente) {
            return $cliente['total_gestiones'] == 0;
        });
        
        $clientes_gestionados = array_filter($todosClientes, function($cliente) {
            return $cliente['total_gestiones'] > 0;
        });
        
        $clientes_con_ventas = array_filter($todosClientes, function($cliente) {
            return !empty($cliente['ultimo_resultado']) && 
                   in_array($cliente['ultimo_resultado'], ['Venta Exitosa', 'Venta en Frío', 'Venta con Seguimiento', 'Venta Cruzada']);
        });
        
        // Calcular estadísticas
        $clientesGestionados = count($clientes_con_ventas);
        $clientesPendientes = count($clientes_pendientes);
        $clientesConGestiones = count($clientes_gestionados);
        $totalVentas = 0;
        
        foreach ($todosClientes as $cliente) {
            if (!empty($cliente['monto_venta'])) {
                $totalVentas += $cliente['monto_venta'];
            }
        }
        
        // Obtener datos de llamadas pendientes para las notificaciones
        $llamadasPendientes = $this->gestionModel->getLlamadasPendientesHoy($asesorId);
        $totalLlamadasPendientesHoy = $this->gestionModel->getTotalLlamadasPendientesHoy($asesorId);
        
        // Crear array de datos del dashboard para las notificaciones
        $datos_dashboard = [
            'llamadas_pendientes' => $llamadasPendientes,
            'total_llamadas_pendientes_hoy' => $totalLlamadasPendientesHoy
        ];
        
        // Determinar qué pestaña está activa
        $pestaña_activa = isset($_GET['filter']) ? $_GET['filter'] : 'todos';
        
        // Calcular paginación para la pestaña activa
        switch ($pestaña_activa) {
            case 'pendientes':
                $total_clientes = count($clientes_pendientes);
                $clientesAsignados = array_slice($clientes_pendientes, $offset, $por_pagina);
                $total_paginas = ceil($total_clientes / $por_pagina);
                break;
            case 'gestionados':
                // Aplicar filtros adicionales para clientes gestionados
                $filtro_resultado = $_GET['filtro_resultado'] ?? 'todos';
                $clientes_gestionados_filtrados = $this->filtrarClientesGestionados($clientes_gestionados, $filtro_resultado);
                
                $total_clientes = count($clientes_gestionados_filtrados);
                $clientesAsignados = array_slice($clientes_gestionados_filtrados, $offset, $por_pagina);
                $total_paginas = ceil($total_clientes / $por_pagina);
                break;
            case 'ventas':
                $total_clientes = count($clientes_con_ventas);
                $clientesAsignados = array_slice($clientes_con_ventas, $offset, $por_pagina);
                $total_paginas = ceil($total_clientes / $por_pagina);
                break;
            case 'seguimiento':
                // Obtener clientes que necesitan seguimiento (con llamadas pendientes)
                $clientesSeguimiento = [];
                foreach ($llamadasPendientes as $llamada) {
                    // Buscar el cliente correspondiente
                    foreach ($todosClientes as $cliente) {
                        if ($cliente['id'] == $llamada['cliente_id']) {
                            $cliente['proxima_fecha'] = $llamada['proxima_fecha'];
                            $cliente['comentarios_seguimiento'] = $llamada['comentarios'];
                            $clientesSeguimiento[] = $cliente;
                            break;
                        }
                    }
                }
                $total_clientes = count($clientesSeguimiento);
                $clientesAsignados = array_slice($clientesSeguimiento, $offset, $por_pagina);
                $total_paginas = ceil($total_clientes / $por_pagina);
                break;
            default: // 'todos'
                $total_clientes = count($todosClientes);
                $clientesAsignados = array_slice($todosClientes, $offset, $por_pagina);
                $total_paginas = ceil($total_clientes / $por_pagina);
                break;
        }
        
        require 'views/asesor_clientes_list.php';
    }
    
    /**
     * Filtra los clientes gestionados según el resultado de la gestión
     */
    private function filtrarClientesGestionados($clientesGestionados, $filtroResultado) {
        if ($filtroResultado === 'todos') {
            return $clientesGestionados;
        }
        
        $clientesFiltrados = [];
        
        foreach ($clientesGestionados as $cliente) {
            $ultimoResultado = $cliente['ultimo_resultado'] ?? '';
            
            switch ($filtroResultado) {
                case 'volver_llamar':
                    if (in_array($ultimoResultado, ['VOLVER A LLAMAR', 'Agenda Llamada de Seguimiento'])) {
                        $clientesFiltrados[] = $cliente;
                    }
                    break;
                    
                case 'interesados':
                    if (in_array($ultimoResultado, ['INTERESADO', 'Cliente Interesado', 'Necesita Pensarlo'])) {
                        $clientesFiltrados[] = $cliente;
                    }
                    break;
                    
                case 'ventas_positivas':
                    if (in_array($ultimoResultado, ['VENTA INGRESADA', 'Venta Exitosa', 'Venta en Frío', 'Venta con Seguimiento', 'Venta Cruzada'])) {
                        $clientesFiltrados[] = $cliente;
                    }
                    break;
                    
                case 'rechazos':
                    if (in_array($ultimoResultado, ['Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica'])) {
                        $clientesFiltrados[] = $cliente;
                    }
                    break;
                    
                case 'contactos_no_efectivos':
                    if (in_array($ultimoResultado, ['No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado'])) {
                        $clientesFiltrados[] = $cliente;
                    }
                    break;
                    
                case 'otros':
                    if (!in_array($ultimoResultado, [
                        'VOLVER A LLAMAR', 'Agenda Llamada de Seguimiento',
                        'INTERESADO', 'Cliente Interesado', 'Necesita Pensarlo',
                        'VENTA INGRESADA', 'Venta Exitosa', 'Venta en Frío', 'Venta con Seguimiento', 'Venta Cruzada',
                        'Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica',
                        'No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado'
                    ]) && !empty($ultimoResultado)) {
                        $clientesFiltrados[] = $cliente;
                    }
                    break;
            }
        }
        
        return $clientesFiltrados;
    }
    
    /**
     * Determina la clase CSS para el resultado de la gestión
     */
    private function getClaseResultado($resultado) {
        if (empty($resultado)) return '';
        
        if (in_array($resultado, ['VOLVER A LLAMAR', 'Agenda Llamada de Seguimiento'])) {
            return 'volver-llamar';
        } elseif (in_array($resultado, ['INTERESADO', 'Cliente Interesado', 'Necesita Pensarlo'])) {
            return 'interesado';
        } elseif (in_array($resultado, ['VENTA INGRESADA', 'Venta Exitosa', 'Venta en Frío', 'Venta con Seguimiento', 'Venta Cruzada'])) {
            return 'venta';
        } elseif (in_array($resultado, ['Rechazo por Precio', 'Rechazo por Competencia', 'No Interesado', 'No Califica'])) {
            return 'rechazo';
        } elseif (in_array($resultado, ['No Contesta', 'Número Equivocado', 'Buzón de Voz', 'Número Fuera de Servicio', 'Cliente Ocupado'])) {
            return 'contacto-no-efectivo';
        } else {
            return 'otro';
        }
    }


    public function gestionarCliente($clienteId) {
        $page_title = "Gestionar Cliente";
        $asesorId = $_SESSION['user_id'];
        
        // Verificar que el cliente pertenece a una base asignada al asesor
        $basesAsignadas = $this->tareaModel->getBasesAsignadasByAsesor($asesorId);
        $cargaIds = array_column($basesAsignadas, 'carga_id');
        
        if (empty($cargaIds)) {
            $_SESSION['error_message'] = "No tienes bases asignadas para gestionar clientes.";
            header('Location: index.php?action=gestionar_clientes');
            exit;
        }
        
        // Verificar que el cliente pertenece a una de las bases asignadas
        $sql = "SELECT c.*, ce.nombre_cargue 
                FROM clientes c 
                JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                WHERE c.id = ? AND c.carga_excel_id IN (" . implode(',', array_fill(0, count($cargaIds), '?')) . ")";
        
        $params = array_merge([$clienteId], $cargaIds);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            $_SESSION['error_message'] = "No tienes permisos para gestionar este cliente o el cliente no existe.";
            header('Location: index.php?action=gestionar_clientes');
            exit;
        }
        
        // Obtener el ID de la asignación (si existe) o crear uno temporal
        $asignacionId = $this->clienteModel->getAsignacionId($asesorId, $clienteId);
        if (!$asignacionId) {
            // Crear una asignación temporal para el cliente
            $asignacionId = $this->clienteModel->createTemporaryAsignacion($asesorId, $clienteId);
        }
        
        // Verificar si el asesor tiene tareas pendientes
        $tieneTareasPendientes = $this->tareaModel->tieneTareasPendientes($asesorId);
        
        // Obtener estadísticas básicas del cliente
        $total_gestiones = 0; // Se puede implementar después
        $ultima_gestion = 'N/A'; // Se puede implementar después
        $estado_actual = 'Nuevo'; // Se puede implementar después
        
        // Procesar formulario si se envía
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarGestionCliente($asesorId, $clienteId, $asignacionId);
        }
        
        // Obtener todas las obligaciones del cliente
        $obligaciones = $this->obligacionModel->getObligacionesByClienteId($clienteId);
        
        // Obtener estadísticas de obligaciones
        $estadisticasObligaciones = $this->obligacionModel->getEstadisticasObligaciones($cliente['cedula']);
        
        // Obtener historial de gestiones (usar método existente)
        $historial = $this->gestionModel->getGestionByAsesorAndCliente($asesorId, $clienteId);
        
        // Obtener productos del cliente
        $productoModel = new ProductoClienteModel($this->pdo);
        $productos = $productoModel->getProductosByCliente($clienteId);
        
        require 'views/gestionar_cliente.php';
    }
    
    private function procesarGestionCliente($asesorId, $clienteId, $asignacionId) {
        try {
            // Validar datos requeridos
            if (empty($_POST['sub_tipificacion']) || empty($_POST['comentarios'])) {
                throw new Exception("Todos los campos obligatorios deben ser completados.");
            }
            
            $sub_tipificacion = $_POST['sub_tipificacion'];
            $comentarios = $_POST['comentarios'];
            
            // Determinar el tipo de gestión basado en la tipificación
            $tipo_gestion = 'Llamada de Venta'; // Por defecto
            
            // Campos específicos según la tipificación
            $monto_venta = null;
            $producto_vendido = null;
            $fecha_agendamiento = null;
            $fecha_nueva_llamada = null;
            $motivo_nueva_llamada = null;
            
            // Procesar según la tipificación seleccionada
            if ($sub_tipificacion === 'INTERESADO') {
                // Cliente interesado - información completa
                $tipo_gestion = 'Cliente Interesado';
                $edad = $_POST['edad'] ?? null;
                $num_personas = $_POST['num_personas'] ?? null;
                $valor_cotizacion = $_POST['valor_cotizacion'] ?? null;
                $whatsapp_enviado = $_POST['whatsapp_enviado'] ?? null;
                
                if (empty($edad) || empty($num_personas) || empty($valor_cotizacion) || empty($whatsapp_enviado)) {
                    throw new Exception("Para clientes interesados, todos los campos son obligatorios.");
                }
                
                // Agregar información adicional a los comentarios
                $comentarios .= "\n\n📊 INFORMACIÓN DEL CLIENTE INTERESADO:\n";
                $comentarios .= "Edad: " . $edad . " años\n";
                $comentarios .= "Personas a cubrir: " . $num_personas . "\n";
                $comentarios .= "Valor cotización: $" . number_format($valor_cotizacion, 0, ',', '.') . "\n";
                $comentarios .= "WhatsApp: " . $whatsapp_enviado;
                
            } elseif ($sub_tipificacion === 'VENTA INGRESADA') {
                // Venta ingresada - información completa
                $tipo_gestion = 'Venta Ingresada';
                $edad = $_POST['edad'] ?? null;
                $num_personas = $_POST['num_personas'] ?? null;
                $monto_venta = $_POST['monto_venta'] ?? null;
                $whatsapp_enviado = $_POST['whatsapp_enviado'] ?? null;
                
                if (empty($edad) || empty($num_personas) || empty($monto_venta) || empty($whatsapp_enviado)) {
                    throw new Exception("Para ventas ingresadas, todos los campos son obligatorios.");
                }
                
                // Agregar información adicional a los comentarios
                $comentarios .= "\n\n💰 INFORMACIÓN DE LA VENTA INGRESADA:\n";
                $comentarios .= "Edad: " . $edad . " años\n";
                $comentarios .= "Personas a cubrir: " . $num_personas . "\n";
                $comentarios .= "Valor venta: $" . number_format($monto_venta, 0, ',', '.') . "\n";
                $comentarios .= "WhatsApp: " . $whatsapp_enviado;
                
            } elseif ($sub_tipificacion === 'VOLVER A LLAMAR') {
                // Agendar nueva llamada
                $fecha_nueva_llamada = $_POST['fecha_nueva_llamada'] ?? null;
                $motivo_nueva_llamada = $_POST['motivo_nueva_llamada'] ?? null;
                
                if (empty($fecha_nueva_llamada) || empty($motivo_nueva_llamada)) {
                    throw new Exception("Para agendar nueva llamada, fecha y motivo son obligatorios.");
                }
                
                $tipo_gestion = 'Llamada de Seguimiento';
                $comentarios .= "\n\n📅 NUEVA LLAMADA AGENDADA:\nFecha: " . date('d/m/Y H:i', strtotime($fecha_nueva_llamada)) . "\nMotivo: " . $motivo_nueva_llamada;
                
            } else {
                // Otras tipificaciones - solo observaciones
                $tipo_gestion = 'Llamada de Gestión';
            }
            
            // Procesar información adicional del cliente si se proporciona
            $this->procesarInformacionAdicional($clienteId, $_POST);
            
            // Crear la gestión
            $gestionData = [
                'asignacion_id' => $asignacionId,
                'tipo_gestion' => $tipo_gestion,
                'resultado' => $sub_tipificacion,
                'comentarios' => $comentarios,
                // Campos específicos de tipificación que sí existen en la tabla
                'edad' => $_POST['edad'] ?? null,
                'num_personas' => $_POST['num_personas'] ?? null,
                'valor_cotizacion' => $_POST['valor_cotizacion'] ?? null,
                'whatsapp_enviado' => $_POST['whatsapp_enviado'] ?? null,
                'monto_venta' => ($sub_tipificacion === 'VENTA INGRESADA') ? ($_POST['monto_venta'] ?? null) : null,
                'proxima_fecha' => $fecha_nueva_llamada,
                'duracion_llamada' => $_POST['duracion_llamada'] ?? null,
                'forma_contacto' => $_POST['forma_contacto'] ?? 'llamada'
            ];
            
            // Obtener campos de cuotas si están disponibles
            $noCuotas = $_POST['no_cuotas'] ?? null;
            $fechaPago = $_POST['fecha_pago'] ?? null;
            $valorCuota = $_POST['valor_cuota'] ?? null;
            $numeroCuota = $_POST['numero_cuota'] ?? null;
            
            // Procesar valor de cuota si está presente
            if ($valorCuota) {
                $valorCuota = (float) str_replace(['.', ','], '', $valorCuota);
            }
            
            $gestionId = $this->gestionModel->createGestion(
                $gestionData['asignacion_id'],
                $gestionData['tipo_gestion'],
                $gestionData['comentarios'],
                $gestionData['resultado'],
                $gestionData['monto_venta'],
                $gestionData['duracion_llamada'],
                $gestionData['edad'],
                $gestionData['num_personas'],
                $gestionData['valor_cotizacion'],
                $gestionData['whatsapp_enviado'],
                $gestionData['forma_contacto'],
                null, // obligacion_id
                null, // producto_gestionado
                null, // monto_obligacion
                null, // numero_obligacion
                $noCuotas,
                $fechaPago,
                $valorCuota,
                $numeroCuota
            );
            
            if ($gestionId) {
                // Actualizar estado del cliente según la tipificación
                $nuevoEstado = $this->determinarNuevoEstadoCliente($sub_tipificacion);
                $this->clienteModel->actualizarCliente($clienteId, ['estado_cliente' => $nuevoEstado]);
                
                $_SESSION['success_message'] = "Gestión guardada exitosamente. El cliente ha sido marcado como: " . $nuevoEstado;
                
                // Redirigir de vuelta a la gestión del cliente para mostrar el botón de siguiente
                header('Location: index.php?action=gestionar_cliente&id=' . $asignacionId . '&gestion_guardada=1');
                exit;
            } else {
                throw new Exception("Error al guardar la gestión.");
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: index.php?action=gestionar_cliente&id=' . $asignacionId);
            exit;
        }
    }
    
    /**
     * Guarda un cliente nuevo durante la llamada
     */
    public function guardarClienteNuevo() {
        try {
            // Verificar que sea un asesor
            if ($_SESSION['user_role'] !== 'asesor') {
                throw new Exception("Acceso denegado.");
            }
            
            $asesorId = $_SESSION['user_id'];
            
            // Validar campos obligatorios
            if (empty($_POST['nuevo_cedula']) || empty($_POST['nuevo_telefono'])) {
                throw new Exception("Los campos Cédula y Teléfono son obligatorios.");
            }
            
            // Preparar datos del cliente
            $clienteData = [
                'nombre' => trim($_POST['nuevo_nombre']),
                'cedula' => trim($_POST['nuevo_cedula']),
                'telefono' => trim($_POST['nuevo_telefono']),
                'celular2' => trim($_POST['nuevo_celular'] ?? ''),
                'email' => trim($_POST['nuevo_email'] ?? ''),
                'ciudad' => trim($_POST['nuevo_ciudad'] ?? ''),
                'estado_cliente' => 'Nuevo',
                'asesor_id' => $asesorId,
                'coordinador_id' => $_SESSION['user_coordinador_id'] ?? null,
                'carga_excel_id' => null // Cliente nuevo no viene de carga
            ];
            
            // Crear el cliente
            $clienteId = $this->clienteModel->crearCliente($clienteData);
            
            if ($clienteId) {
                // Crear gestión inicial para el cliente nuevo
                $gestionData = [
                    'cliente_id' => $clienteId,
                    'asesor_id' => $asesorId,
                    'coordinador_id' => $_SESSION['user_coordinador_id'] ?? null,
                    'tipo_gestion' => 'Llamada',
                    'resultado' => 'INTERESADO',
                    'comentarios' => "Cliente nuevo captado durante llamada. " . ($_POST['nuevo_observaciones'] ?? ''),
                    'estado' => 'Completada'
                ];
                
                // Crear asignación temporal para el cliente nuevo
                $asignacionId = $this->clienteModel->createTemporaryAsignacion($gestionData['asesor_id'], $gestionData['cliente_id']);
                
                $this->gestionModel->createGestion(
                    $asignacionId,
                    $gestionData['tipo_gestion'],
                    $gestionData['comentarios'],
                    $gestionData['resultado']
                );
                
                // Respuesta exitosa
                echo json_encode([
                    'success' => true,
                    'message' => 'Cliente nuevo guardado exitosamente',
                    'cliente_id' => $clienteId
                ]);
            } else {
                throw new Exception("Error al crear el cliente.");
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene los datos de un cliente específico para carga AJAX
     */
    public function obtenerDatosCliente() {
        try {
            // Verificar que sea un asesor
            if ($_SESSION['user_role'] !== 'asesor') {
                throw new Exception("Acceso denegado.");
            }

            $asesorId = $_SESSION['user_id'];
            $clienteId = $_GET['id'] ?? null;

            if (!$clienteId) {
                throw new Exception("ID de cliente no proporcionado.");
            }

            // Verificar que el cliente esté asignado al asesor
            $asignacionId = $this->clienteModel->getAsignacionId($asesorId, $clienteId);
            if (!$asignacionId) {
                throw new Exception("No tienes permisos para acceder a este cliente.");
            }

            // Obtener información del cliente
            $cliente = $this->clienteModel->getClienteById($clienteId);

            if (!$cliente) {
                throw new Exception("Cliente no encontrado.");
            }

            // Obtener historial de gestiones
            $historial = $this->gestionModel->getGestionByAsesorAndCliente($asesorId, $clienteId);

            // Preparar respuesta
            $clienteData = [
                'id' => $cliente['id'],
                'nombre' => $cliente['nombre'],
                'cedula' => $cliente['cedula'],
                'telefono' => $cliente['telefono'],
                'celular2' => $cliente['celular2'],
                'email' => $cliente['email'],
                'direccion' => $cliente['direccion'],
                'ciudad' => $cliente['ciudad'],
                'estado_cliente' => $cliente['estado_cliente']
            ];

            echo json_encode([
                'success' => true,
                'cliente' => $clienteData,
                'historial' => $historial ?: []
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene el siguiente cliente en la lista del asesor
     */
    public function obtenerSiguienteCliente() {
        try {
            // Verificar que sea un asesor
            if ($_SESSION['user_role'] !== 'asesor') {
                throw new Exception("Acceso denegado.");
            }

            $asesorId = $_SESSION['user_id'];

            // Obtener el siguiente cliente no gestionado
            $siguienteCliente = $this->clienteModel->getSiguienteClienteAsesor($asesorId);

            if ($siguienteCliente) {
                echo json_encode([
                    'success' => true,
                    'siguiente_cliente' => $siguienteCliente
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No hay más clientes en tu lista'
                ]);
            }

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Determina el nuevo estado del cliente según la tipificación
     */
    private function determinarNuevoEstadoCliente($tipificacion) {
        // Tipificaciones de CON INTENCION DE PAGO
        if (strpos($tipificacion, '1.1.') === 0) {
            return 'En Proceso';
        }
        
        // Tipificaciones de SIN INTENCION DE PAGO
        if (strpos($tipificacion, '1.2.') === 0) {
            return 'No Interesado';
        }
        
        // Tipificaciones de NO COLABORA
        if (strpos($tipificacion, '1.3.') === 0) {
            return 'No Colabora';
        }
        
        // Tipificaciones de YA PAGO
        if (strpos($tipificacion, '1.4.') === 0) {
            return 'Pagado';
        }
        
        // Tipificaciones de NO CONTACTADO
        if (strpos($tipificacion, '2.') === 0) {
            return 'No Contactado';
        }
        
        // Fallback para tipificaciones antiguas
        switch ($tipificacion) {
            case 'VENDIDO':
                return 'Vendido';
            case 'INTERESADO':
                return 'En Proceso';
            case 'NO LE INTERESA':
                return 'No Interesado';
            case 'VOLVER A LLAMAR':
                return 'En Proceso';
            default:
                return 'Contactado';
        }
    }
    
    private function procesarInformacionAdicional($clienteId, $postData) {
        try {
            $nuevo_telefono = $postData['nuevo_telefono'] ?? null;
            $nuevo_celular = $postData['nuevo_celular'] ?? null;
            $nuevo_email = $postData['nuevo_email'] ?? null;
            $nueva_direccion = $postData['nueva_direccion'] ?? null;
            $nueva_ciudad = $postData['nueva_ciudad'] ?? null;
            
            // Campos opcionales de información de pago
            $fechaPagoEsperada = $postData['fecha_pago_esperada'] ?? null;
            $montoPendiente = $postData['monto_pendiente'] ?? null;
            $detallesPago = $postData['detalles_pago'] ?? null;
            $motivoNuevaLlamada = $postData['motivo_nueva_llamada'] ?? null;
            
            // Solo procesar si hay información nueva
            if ($nuevo_telefono || $nuevo_celular || $nuevo_email || $nueva_direccion || $nueva_ciudad || 
                $fechaPagoEsperada || $montoPendiente || $detallesPago || $motivoNuevaLlamada) {
                // Preparar datos para actualizar
                $datosActualizar = [];
                
                if ($nuevo_telefono) {
                    $datosActualizar['telefono'] = $nuevo_telefono;
                }
                
                if ($nuevo_celular) {
                    $datosActualizar['celular2'] = $nuevo_celular;
                }
                
                if ($nuevo_email) {
                    $datosActualizar['email'] = $nuevo_email;
                }
                
                if ($nueva_direccion) {
                    $datosActualizar['direccion'] = $nueva_direccion;
                }
                
                if ($nueva_ciudad) {
                    $datosActualizar['ciudad'] = $nueva_ciudad;
                }
                
                // Actualizar cliente si hay datos nuevos
                if (!empty($datosActualizar)) {
                    $this->clienteModel->actualizarCliente($clienteId, $datosActualizar);
                    
                    error_log("Información adicional actualizada para cliente $clienteId: " . json_encode($datosActualizar));
                }
                
                // Procesar campos opcionales de información de pago
                $camposOpcionales = [];
                
                if ($fechaPagoEsperada) {
                    $camposOpcionales['fecha_pago_esperada'] = $fechaPagoEsperada;
                }
                
                if ($montoPendiente) {
                    // Procesar monto pendiente (remover formato de pesos)
                    $montoPendiente = (int) str_replace(['.', ','], '', $montoPendiente);
                    $camposOpcionales['monto_pendiente'] = $montoPendiente;
                }
                
                if ($detallesPago) {
                    $camposOpcionales['detalles_pago'] = $detallesPago;
                }
                
                if ($motivoNuevaLlamada) {
                    $camposOpcionales['motivo_nueva_llamada'] = $motivoNuevaLlamada;
                }
                
                // Guardar campos opcionales en la base de datos si existen
                if (!empty($camposOpcionales)) {
                    // Aquí podrías guardar en una tabla específica para información adicional
                    // Por ahora, los agregamos a los comentarios o los guardamos en el historial
                    error_log("Campos opcionales procesados para cliente $clienteId: " . json_encode($camposOpcionales));
                }
            }
        } catch (Exception $e) {
            error_log("Error procesando información adicional: " . $e->getMessage());
        }
    }
    
    /**
     * Guarda la tipificación de un cliente
     * IMPORTANTE: Este método debe devolver JSON puro, sin headers ni output adicional
     */
    public function guardarTipificacion() {
        // MEJORADO: Manejo robusto para evitar error 520
        // NUEVO: Configurar límites de tiempo y memoria
        set_time_limit(30); // Máximo 30 segundos por operación
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit < 256) {
            ini_set('memory_limit', '256M');
        }
        
        // Limpiar TODOS los buffers existentes
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Asegurar que no haya output previo
        if (ob_get_level() == 0) {
            ob_start();
        }
        
        // Desactivar display_errors y warnings ANTES de cualquier operación
        $displayErrors = ini_get('display_errors');
        $errorReporting = error_reporting();
        ini_set('display_errors', 0);
        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
        
        // Establecer headers para JSON ANTES de cualquier output
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
            header('X-Content-Type-Options: nosniff');
        }
        
        // NUEVO: Verificar conexión a la base de datos antes de continuar
        try {
            $this->pdo->query("SELECT 1");
        } catch (PDOException $e) {
            // Si la conexión falla, intentar reconectar
            error_log("Conexión PDO perdida, intentando reconectar...");
            try {
                $this->pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_PERSISTENT => false,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_TIMEOUT => 5
                    ]
                );
                // Recrear modelos con nueva conexión
                $this->gestionModel = new GestionModel($this->pdo);
                $this->clienteModel = new ClienteModel($this->pdo);
                $this->obligacionModel = new ObligacionModel($this->pdo);
            } catch (PDOException $e2) {
                $response = [
                    'success' => false,
                    'message' => 'Error de conexión a la base de datos. Por favor, intenta nuevamente.',
                    'error_code' => 'DB_CONNECTION_ERROR'
                ];
                echo json_encode($response);
                exit;
            }
        }
        
        try {
            // Log inicial para debugging
            error_log("=== INICIO guardarTipificacion ===");
            error_log("POST data keys: " . implode(', ', array_keys($_POST)));
            
            // Verificar que sea un asesor
            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'asesor') {
                error_log("Error: Sesión inválida o rol incorrecto");
                throw new Exception("Acceso denegado. Por favor, inicia sesión nuevamente.");
            }
            
            $asesorId = $_SESSION['user_id'];
            $clienteId = $_POST['cliente_id'] ?? null;
            
            error_log("Asesor ID: {$asesorId}, Cliente ID: {$clienteId}");
            
            if (!$clienteId) {
                throw new Exception("ID de cliente no proporcionado.");
            }
            
            // Obtener datos del formulario
            $formaContacto = $_POST['forma_contacto'] ?? 'llamada';
            
            // Procesar canales autorizados (pueden venir como array indexado o no indexado)
            $canalesAutorizados = [];
            if (isset($_POST['canales_autorizados'])) {
                if (is_array($_POST['canales_autorizados'])) {
                    // Array normal (de checkboxes)
                    $canalesAutorizados = $_POST['canales_autorizados'];
                } else {
                    // Single value
                    $canalesAutorizados = [$_POST['canales_autorizados']];
                }
            }
            
            // También verificar formato indexado: canales_autorizados[0], canales_autorizados[1], etc.
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'canales_autorizados[') === 0) {
                    $canalesAutorizados[] = $value;
                }
            }
            
            // Eliminar duplicados
            $canalesAutorizados = array_unique($canalesAutorizados);
            
            // Log de canales autorizados para debugging
            error_log("Canales autorizados procesados: " . json_encode($canalesAutorizados));
            
            $tipificacion = $_POST['tipificacion'] ?? '';
            $subTipificacion = $_POST['sub_tipificacion'] ?? '';
            $comentarios = trim($_POST['comentarios'] ?? '');
            
            // Validar que los comentarios tengan contenido
            if (empty($comentarios)) {
                throw new Exception("Los comentarios son obligatorios.");
            }
            
            // Asegurar codificación UTF-8 correcta
            $comentarios = mb_convert_encoding($comentarios, 'UTF-8', 'auto');
            $edadCliente = $_POST['edad'] ?? null;
            $numPersonas = $_POST['num_personas'] ?? null;
            $valorCotizacion = $_POST['valor_cotizacion'] ?? null;
            $whatsappEnviado = $_POST['whatsapp_enviado'] ?? null;
            $montoVenta = $_POST['monto_venta'] ?? null;
            $duracionLlamada = $_POST['duracion_llamada'] ?? null;
            $fechaProximaLlamada = $_POST['fecha_nueva_llamada'] ?? null;
            $horaProximaLlamada = null;
            
            // Campos opcionales de información de pago
            $fechaPagoEsperada = $_POST['fecha_pago_esperada'] ?? null;
            $montoPendiente = $_POST['monto_pendiente'] ?? null;
            $detallesPago = $_POST['detalles_pago'] ?? null;
            $motivoNuevaLlamada = $_POST['motivo_nueva_llamada'] ?? null;
            
            // Campos específicos de acuerdo de pago
            $noCuotas = $_POST['no_cuotas'] ?? null;
            $fechaPago = $_POST['fecha_pago'] ?? null;
            $valorCuota = $_POST['valor_cuota'] ?? null;
            $numeroCuota = $_POST['numero_cuota'] ?? null;
            $valorAcuerdo = $_POST['valor_acuerdo'] ?? null;
            
            // Procesar campos de valor (remover formato de pesos)
            if ($valorCotizacion) {
                $valorCotizacion = (int) str_replace(['.', ','], '', $valorCotizacion);
            }
            if ($montoVenta) {
                $montoVenta = (int) str_replace(['.', ','], '', $montoVenta);
            }
            if ($valorCuota) {
                // Remover formato de pesos colombianos (puntos y comas)
                $valorCuota = (float) str_replace(['.', ','], '', $valorCuota);
            }
            if ($valorAcuerdo) {
                // Remover formato de pesos colombianos (puntos y comas) del valor del acuerdo
                $valorAcuerdo = (float) str_replace(['.', ','], '', $valorAcuerdo);
            }
            
            // Validar campos obligatorios
            if (empty($tipificacion) || empty($comentarios)) {
                throw new Exception("La tipificación y los comentarios son obligatorios.");
            }
            
            // Obtener el ID de asignación
            $asignacionId = $this->clienteModel->getAsignacionId($asesorId, $clienteId);
            
            if (!$asignacionId) {
                throw new Exception("No se encontró la asignación del cliente para este asesor.");
            }
            
            // Obtener información de la obligación si está seleccionada
            $obligacionId = $_POST['obligacion_id'] ?? null;
            $productoGestionado = $_POST['producto_gestionado'] ?? null;
            $montoObligacion = $_POST['monto_obligacion'] ?? null;
            $numeroObligacion = $_POST['numero_obligacion'] ?? null;
            
            // Obtener valor_total del producto desde la base de datos
            $valorTotal = null;
            if ($obligacionId) {
                $obligacion = $this->obligacionModel->getObligacionById($obligacionId);
                if ($obligacion) {
                    $valorTotal = $obligacion['saldo_k_obligacion'];
                }
            }
            
            // Crear registro en historial_gestion
            $gestionData = [
                'asignacion_id' => $asignacionId,
                'tipo_gestion' => $tipificacion,
                'resultado' => $subTipificacion ?: $tipificacion,
                'comentarios' => $comentarios,
                'monto_venta' => $montoVenta,
                'duracion_llamada' => $duracionLlamada,
                'edad' => $edadCliente,
                'num_personas' => $numPersonas,
                'valor_cotizacion' => $valorCotizacion,
                'whatsapp_enviado' => $whatsappEnviado,
                'proxima_fecha' => $fechaProximaLlamada,
                'forma_contacto' => $formaContacto,
                'obligacion_id' => $obligacionId,
                'producto_gestionado' => $productoGestionado,
                'monto_obligacion' => $montoObligacion,
                'numero_obligacion' => $numeroObligacion,
                'no_cuotas' => $noCuotas,
                'fecha_pago' => $fechaPago,
                'valor_cuota' => $valorCuota,
                'numero_cuota' => $numeroCuota,
                'valor_total' => $valorTotal,
                'valor_acuerdo' => $valorAcuerdo
            ];
            
            // Validar datos críticos antes de guardar
            if (empty($gestionData['asignacion_id'])) {
                throw new Exception("Error: Asignación ID no válido.");
            }
            
            if (empty($gestionData['tipo_gestion'])) {
                throw new Exception("Error: Tipo de gestión no válido.");
            }
            
            error_log("Guardando gestión con asignacion_id: {$gestionData['asignacion_id']}, tipo_gestion: {$gestionData['tipo_gestion']}");
            
            // Guardar en historial_gestion
            $gestionId = $this->gestionModel->createGestion(
                $gestionData['asignacion_id'],
                $gestionData['tipo_gestion'],
                $gestionData['comentarios'],
                $gestionData['resultado'],
                $gestionData['monto_venta'],
                $gestionData['duracion_llamada'],
                $gestionData['edad'],
                $gestionData['num_personas'],
                $gestionData['valor_cotizacion'],
                $gestionData['whatsapp_enviado'],
                $gestionData['forma_contacto'],
                $gestionData['obligacion_id'],
                $gestionData['producto_gestionado'],
                $gestionData['monto_obligacion'],
                $gestionData['numero_obligacion'],
                $gestionData['no_cuotas'],
                $gestionData['fecha_pago'],
                $gestionData['valor_cuota'],
                $gestionData['numero_cuota'],
                $gestionData['valor_acuerdo']
            );
            
            error_log("Gestion creada con ID: {$gestionId}");
            
            if (!$gestionId) {
                throw new Exception("Error al guardar la gestión en la base de datos.");
            }
            
            // Guardar canales autorizados múltiples
            if (!empty($canalesAutorizados)) {
                $this->gestionModel->guardarCanalesAutorizados($gestionId, $canalesAutorizados);
                
                // Registrar actividad de canales autorizados
                $this->registrarActividadCanales($gestionId, $clienteId, $asesorId, $canalesAutorizados);
            }
            
            // Actualizar estado del cliente
            $nuevoEstado = $this->determinarNuevoEstadoCliente($subTipificacion ?: $tipificacion);
            $this->clienteModel->actualizarCliente($clienteId, ['estado_cliente' => $nuevoEstado]);
            
            // Procesar información adicional si existe
            $this->procesarInformacionAdicional($clienteId, $_POST);
            
            // Preparar respuesta exitosa
            $response = [
                'success' => true,
                'message' => 'Tipificación guardada exitosamente',
                'gestion_id' => $gestionId,
                'redirect_url' => 'index.php?action=gestionar_cliente&id=' . $clienteId . '&gestion_guardada=1'
            ];
            
        } catch (PDOException $e) {
            // Log del error PDO para debugging
            error_log("Error PDO en guardarTipificacion: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            $response = [
                'success' => false,
                'message' => 'Error de base de datos. Por favor, intenta nuevamente.',
                'error_code' => 'DB_ERROR'
            ];
        } catch (Exception $e) {
            // Log del error para debugging
            error_log("Error en guardarTipificacion: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'GENERAL_ERROR'
            ];
        } catch (Throwable $e) {
            // Capturar cualquier error fatal o throwable
            error_log("Error fatal en guardarTipificacion: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            $response = [
                'success' => false,
                'message' => 'Error inesperado. Por favor, contacta al administrador.',
                'error_code' => 'FATAL_ERROR'
            ];
        }
        
        // Limpiar cualquier output capturado
        $obContent = ob_get_clean();
        
        // Si hay contenido en el buffer que no debería estar ahí, loggearlo
        if (!empty($obContent) && trim($obContent) !== '') {
            error_log("Advertencia: Output no esperado antes del JSON: " . substr($obContent, 0, 500));
            // Limpiar el contenido para asegurar JSON limpio
            $obContent = '';
        }
        
        // Restaurar configuración de errores
        if (isset($displayErrors)) {
            ini_set('display_errors', $displayErrors);
        }
        if (isset($errorReporting)) {
            error_reporting($errorReporting);
        }
        
        // NUEVO: Limpiar recursos antes de enviar respuesta
        // Cerrar statements abiertos
        if (isset($stmt) && $stmt instanceof PDOStatement) {
            $stmt->closeCursor();
        }
        
        // Asegurar que no haya output previo
        while (ob_get_level() > 0) {
            $obContent = ob_get_clean();
            if (!empty($obContent) && trim($obContent) !== '') {
                error_log("Output no esperado detectado y limpiado: " . substr($obContent, 0, 200));
            }
        }
        
        // Verificar que los headers no se hayan enviado ya
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        }
        
        // Log de respuesta para debugging (solo si no es muy grande)
        $responseJson = json_encode($response);
        if (strlen($responseJson) < 1000) {
            error_log("Respuesta JSON: " . $responseJson);
        } else {
            error_log("Respuesta JSON generada (tamaño: " . strlen($responseJson) . " bytes)");
        }
        error_log("=== FIN guardarTipificacion ===");
        
        // Enviar respuesta JSON limpia con manejo de errores
        try {
            $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if ($jsonResponse === false) {
                // Si json_encode falla, verificar el error
                $jsonError = json_last_error_msg();
                error_log("Error en json_encode: " . $jsonError);
                
                // Intentar con datos simplificados
                $responseSimplificada = [
                    'success' => $response['success'] ?? false,
                    'message' => substr($response['message'] ?? 'Error desconocido', 0, 200),
                    'error_code' => $response['error_code'] ?? 'JSON_ENCODE_ERROR'
                ];
                $jsonResponse = json_encode($responseSimplificada);
                
                if ($jsonResponse === false) {
                    // Último recurso: respuesta mínima
                    $jsonResponse = '{"success":false,"message":"Error al procesar la respuesta","error_code":"JSON_ENCODE_ERROR"}';
                }
            }
            
            // Asegurar que no haya espacios antes del JSON
            echo trim($jsonResponse);
            
        } catch (Exception $e) {
            error_log("Excepción al enviar JSON: " . $e->getMessage());
            // Último recurso: respuesta de error simple
            echo '{"success":false,"message":"Error crítico al procesar la respuesta","error_code":"CRITICAL_ERROR"}';
        } catch (Throwable $e) {
            error_log("Error fatal al enviar JSON: " . $e->getMessage());
            echo '{"success":false,"message":"Error fatal","error_code":"FATAL_ERROR"}';
        }
        
        // Asegurar que no se envíe nada más
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        }
        
        // Cerrar conexión PDO si es necesario (pero no siempre, puede estar en uso)
        // $this->pdo = null; // Comentado para no romper otras operaciones
        
        exit;
    }
    
    /**
     * Obtiene el historial de gestiones de un cliente específico
     */
    public function obtenerHistorialCliente() {
        try {
            // Verificar que sea un asesor
            if ($_SESSION['user_role'] !== 'asesor') {
                throw new Exception("Acceso denegado.");
            }
            
            $asesorId = $_SESSION['user_id'];
            $clienteId = $_GET['id'] ?? null;
            
            if (!$clienteId) {
                throw new Exception("ID de cliente no proporcionado.");
            }
            
            // Obtener el historial del cliente
            $historial = $this->gestionModel->getGestionByAsesorAndCliente($asesorId, $clienteId);
            
            if ($historial === false) {
                throw new Exception("Error al obtener el historial del cliente.");
            }
            
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'historial' => $historial
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ===== NUEVOS MÉTODOS PARA EL SISTEMA DE TAREAS =====

    /**
     * Vista de gestión de clientes (búsqueda por cédula)
     */
    public function gestionarClientes() {
        $page_title = "Gestión de Clientes";
        $asesorId = $_SESSION['user_id'];
        
        // Obtener bases asignadas al asesor
        $basesAsignadas = $this->tareaModel->getBasesAsignadasByAsesor($asesorId);
        
        // Verificar si tiene tareas pendientes
        $tieneTareasPendientes = $this->tareaModel->tieneTareasPendientes($asesorId);
        
        require 'views/asesor_gestionar_clientes.php';
    }

    /**
     * Buscar cliente por cédula en las bases asignadas
     */
    public function buscarClientePorCedula() {
        $asesorId = $_SESSION['user_id'];
        $cedula = $_GET['cedula'] ?? '';
        
        if (empty($cedula)) {
            echo json_encode([
                'success' => false,
                'message' => 'Cédula requerida'
            ]);
            exit;
        }
        
        $clientes = $this->tareaModel->buscarClienteEnBasesAsignadas($asesorId, $cedula);
        
        echo json_encode([
            'success' => true,
            'clientes' => $clientes,
            'total' => count($clientes)
        ]);
        exit;
    }

    /**
     * Obtener información de un cliente específico para gestión
     */
    public function getClienteParaGestion() {
        $asesorId = $_SESSION['user_id'];
        $clienteId = $_GET['cliente_id'] ?? null;
        
        if (!$clienteId) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de cliente requerido'
            ]);
            exit;
        }
        
        // Verificar que el cliente pertenece a una base asignada al asesor
        $basesAsignadas = $this->tareaModel->getBasesAsignadasByAsesor($asesorId);
        $cargaIds = array_column($basesAsignadas, 'carga_id');
        
        $sql = "SELECT c.*, ce.nombre_cargue 
                FROM clientes c 
                JOIN cargas_excel ce ON c.carga_excel_id = ce.id 
                WHERE c.id = ? AND c.carga_excel_id IN (" . implode(',', array_fill(0, count($cargaIds), '?')) . ")";
        
        $params = array_merge([$clienteId], $cargaIds);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            echo json_encode([
                'success' => false,
                'message' => 'Cliente no encontrado o no tienes acceso a él'
            ]);
            exit;
        }
        
        // Obtener historial de gestiones del cliente
        $historial = $this->gestionModel->getGestionByAsesorAndCliente($asesorId, $clienteId);
        
        // Obtener obligaciones del cliente
        $obligaciones = $this->obligacionModel->getObligacionesByClienteId($clienteId);
        
        // Obtener estadísticas de obligaciones
        $estadisticasObligaciones = $this->obligacionModel->getEstadisticasObligaciones($cliente['cedula']);
        
        echo json_encode([
            'success' => true,
            'cliente' => $cliente,
            'historial' => $historial,
            'obligaciones' => $obligaciones,
            'estadisticasObligaciones' => $estadisticasObligaciones
        ]);
        exit;
    }

    /**
     * Obtener tareas pendientes del asesor
     */
    public function getTareasPendientes() {
        $asesorId = $_SESSION['user_id'];
        $tareas = $this->tareaModel->getTareasPendientesByAsesor($asesorId);
        
        echo json_encode([
            'success' => true,
            'tareas' => $tareas
        ]);
        exit;
    }

    /**
     * Marcar tarea como completada
     */
    public function completarTarea() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        $asesorId = $_SESSION['user_id'];
        $tareaId = $_POST['tarea_id'] ?? null;
        
        if (!$tareaId) {
            echo json_encode(['error' => 'ID de tarea requerido']);
            exit;
        }
        
        // Verificar que la tarea pertenece al asesor
        $tareas = $this->tareaModel->getTareasByAsesor($asesorId);
        $tareaExiste = false;
        foreach ($tareas as $tarea) {
            if ($tarea['id'] == $tareaId) {
                $tareaExiste = true;
                break;
            }
        }
        
        if (!$tareaExiste) {
            echo json_encode(['error' => 'No tienes permisos para modificar esta tarea']);
            exit;
        }
        
        $resultado = $this->tareaModel->actualizarEstadoTarea($tareaId, 'completada', $asesorId);
        
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Tarea completada correctamente']);
        } else {
            echo json_encode(['error' => 'Error al completar la tarea']);
        }
        exit;
    }
    
    /**
     * Muestra la interfaz de gestión de productos para un cliente específico
     */
    public function gestionarProductosCliente() {
        $this->verificarRol('asesor');
        
        $clienteId = $this->getGet('cliente_id');
        $clienteId = $this->validarId($clienteId, 'cliente');
        
        // Verificar que el cliente esté asignado al asesor
        $cliente = $this->clienteModel->getClienteById($clienteId);
        if (!$cliente || $cliente['asesor_id'] != $_SESSION['user_id']) {
            $this->redirigirConError('index.php?action=mis_clientes', 'Cliente no encontrado o no asignado');
            return;
        }
        
        // Redirigir a la interfaz de gestión de productos
        header('Location: index.php?action=gestionar_productos&cliente_id=' . $clienteId);
        exit;
    }
    
    /**
     * Obtiene los detalles completos de una gestión específica
     */
    public function obtenerDetallesGestion() {
        // Limpiar cualquier output previo
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Iniciar buffer de salida
        ob_start();
        
        try {
            // Verificar que sea un asesor
            if ($_SESSION['user_role'] !== 'asesor') {
                throw new Exception("Acceso denegado.");
            }
            
            $gestionId = $_GET['id'] ?? null;
            
            if (!$gestionId) {
                throw new Exception("ID de gestión no proporcionado.");
            }
            
            $asesorId = $_SESSION['user_id'];
            
            // Obtener la gestión con todos sus detalles
            $gestion = $this->gestionModel->getGestionById($gestionId);
            
            if (!$gestion) {
                throw new Exception("Gestión no encontrada.");
            }
            
            // Verificar que la gestión pertenece al asesor
            $asignacion = $this->clienteModel->getAsignacionById($gestion['asignacion_id']);
            if (!$asignacion || $asignacion['asesor_id'] != $asesorId) {
                throw new Exception("No tienes acceso a esta gestión.");
            }
            
            // Obtener canales autorizados para esta gestión
            $canalesAutorizados = $this->gestionModel->getCanalesAutorizados($gestionId);
            
            // Obtener información del asesor
            $asesor = $this->usuarioModel->getUsuarioById($asignacion['asesor_id']);
            
            // Agregar canales autorizados y asesor a la gestión
            $gestion['canales_autorizados'] = $canalesAutorizados;
            $gestion['asesor_nombre'] = $asesor ? $asesor['nombre_completo'] : 'No especificado';
            
            // Formatear fecha
            $gestion['fecha_gestion'] = date('d/m/Y H:i', strtotime($gestion['fecha_gestion']));
            
            // Formatear próxima fecha si existe
            if ($gestion['proxima_fecha']) {
                $gestion['proxima_fecha'] = date('d/m/Y H:i', strtotime($gestion['proxima_fecha']));
            }
            
            // Limpiar el buffer y enviar solo JSON
            ob_clean();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'gestion' => $gestion
            ]);
            
            // Asegurar que no se envíe nada más
            exit;
            
        } catch (Exception $e) {
            // Limpiar el buffer y enviar solo JSON
            ob_clean();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            
            // Asegurar que no se envíe nada más
            exit;
        }
    }
    
    /**
     * Obtiene los productos pendientes de un cliente específico
     */
    public function obtenerProductosPendientes() {
        try {
            $this->verificarRol('asesor');
            
            $clienteId = $this->getGet('cliente_id');
            $clienteId = $this->validarId($clienteId, 'cliente');
            
            // Verificar que el cliente esté asignado al asesor
            $cliente = $this->clienteModel->getClienteById($clienteId);
            if (!$cliente || $cliente['asesor_id'] != $_SESSION['user_id']) {
                throw new Exception('Cliente no encontrado o no asignado');
            }
            
            // Obtener productos del cliente
            $productoModel = new ProductoClienteModel($this->pdo);
            $productos = $productoModel->getProductosByCliente($clienteId);
            
            echo json_encode([
                'success' => true,
                'productos' => $productos
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Registra automáticamente la actividad de canales autorizados
     */
    private function registrarActividadCanales($gestionId, $clienteId, $asesorId, $canales) {
        try {
            require_once 'models/ActividadProductoModel.php';
            $actividadModel = new ActividadProductoModel($this->pdo);
            
            $actividadModel->registrarCanalesAutorizados($gestionId, $clienteId, $asesorId, $canales);
            
        } catch (Exception $e) {
            error_log("Error en registrarActividadCanales: " . $e->getMessage());
        }
    }
}

