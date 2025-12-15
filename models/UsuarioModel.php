<?php  
class UsuarioModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllUsuarios() {
        $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY rol, nombre_completo");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUsuariosWithFilters($search = '', $rol_filter = '', $estado_filter = '') {
        $sql = "SELECT * FROM usuarios WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (nombre_completo LIKE ? OR usuario LIKE ? OR cedula LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($rol_filter)) {
            $sql .= " AND rol = ?";
            $params[] = $rol_filter;
        }
        
        if (!empty($estado_filter)) {
            $sql .= " AND estado = ?";
            $params[] = $estado_filter;
        }
        
        $sql .= " ORDER BY rol, nombre_completo";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUsuariosByRol($rol) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE rol = ? AND estado = 'Activo' ORDER BY nombre_completo");
        $stmt->execute([$rol]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsuarioById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene los datos de teléfono de un usuario
     */
    public function getDatosTelefono($userId) {
        $stmt = $this->pdo->prepare("SELECT extension_telefono, clave_webrtc, telefono_activo FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verifica si un usuario tiene teléfono configurado
     */
    public function tieneTelefonoConfigurado($userId) {
        $datos = $this->getDatosTelefono($userId);
        return $datos && $datos['telefono_activo'] === 'Si' && 
               !empty($datos['extension_telefono']) && 
               !empty($datos['clave_webrtc']);
    }

    public function authenticateUser($usuario, $contrasena) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND estado = 'Activo' LIMIT 1");
            $stmt->execute([$usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verificar si la contraseña está hasheada (empieza con $2y$)
                if (strpos($user['contrasena'], '$2y$') === 0) {
                    // Contraseña hasheada, usar password_verify
                    if (password_verify($contrasena, $user['contrasena'])) {
                        return $user;
                    }
                } else {
                    // Contraseña plana, comparación directa
                    if ($user['contrasena'] === $contrasena) {
                        return $user;
                    }
                }
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un usuario existe (sin verificar contraseña)
     */
    public function checkUserExists($usuario) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, usuario, estado FROM usuarios WHERE usuario = ? LIMIT 1");
            $stmt->execute([$usuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error verificando usuario: " . $e->getMessage());
            return false;
        }
    }

    public function createUsuario($data) {
        try {
            $sql = "INSERT INTO usuarios (nombre_completo, cedula, usuario, contrasena, rol, extension_telefono, clave_webrtc, telefono_activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            
            // Validar datos requeridos
            if (empty($data['nombre']) || empty($data['cedula']) || empty($data['usuario']) || empty($data['contrasena']) || empty($data['rol'])) {
                throw new Exception("Datos requeridos faltantes");
            }
            
            // Solo asesores pueden tener teléfono activo
            $esAsesor = isset($data['rol']) && $data['rol'] === 'asesor';

            // Si es asesor y se define extensión y clave, asumimos que el teléfono debe estar activo
            $telefonoActivo = ($esAsesor && !empty($data['extension_telefono']) && !empty($data['clave_webrtc']))
                ? 'Si'
                : 'No';

            return $stmt->execute([
                $data['nombre'], 
                $data['cedula'], 
                $data['usuario'], 
                $data['contrasena'], 
                $data['rol'],
                $esAsesor ? ($data['extension_telefono'] ?? null) : null,
                $esAsesor ? ($data['clave_webrtc'] ?? null) : null,
                $telefonoActivo
            ]);
        } catch (Exception $e) {
            error_log("Error en createUsuario: " . $e->getMessage());
            return false;
        }
    }

    public function updateUsuario($id, $data) {
        // Si se proporciona una nueva contraseña, incluirla en la actualización
        if (!empty($data['contrasena'])) {
            $sql = "UPDATE usuarios SET nombre_completo = ?, cedula = ?, usuario = ?, rol = ?, contrasena = ?, extension_telefono = ?, clave_webrtc = ?, telefono_activo = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            
            // Detectar automáticamente el tipo de contraseña en la base de datos
            $usePlainPassword = $this->shouldUsePlainPasswords();
            
            $password = $usePlainPassword 
                ? $data['contrasena'] 
                : password_hash($data['contrasena'], PASSWORD_DEFAULT);

            // Solo asesores pueden tener teléfono activo
            $esAsesor = isset($data['rol']) && $data['rol'] === 'asesor';

            // Recalcular teléfono_activo en base a extensión/clave solo para asesores
            $telefonoActivo = ($esAsesor && !empty($data['extension_telefono']) && !empty($data['clave_webrtc']))
                ? 'Si'
                : 'No';
                
            return $stmt->execute([
                $data['nombre'], 
                $data['cedula'], 
                $data['usuario'], 
                $data['rol'], 
                $password, 
                $esAsesor ? ($data['extension_telefono'] ?? null) : null,
                $esAsesor ? ($data['clave_webrtc'] ?? null) : null,
                $telefonoActivo,
                $id
            ]);
        } else {
            // Si no se proporciona contraseña, no actualizar el campo de contraseña
            $sql = "UPDATE usuarios SET nombre_completo = ?, cedula = ?, usuario = ?, rol = ?, extension_telefono = ?, clave_webrtc = ?, telefono_activo = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);

            // Solo asesores pueden tener teléfono activo
            $esAsesor = isset($data['rol']) && $data['rol'] === 'asesor';

            // Recalcular teléfono_activo en base a extensión/clave solo para asesores
            $telefonoActivo = ($esAsesor && !empty($data['extension_telefono']) && !empty($data['clave_webrtc']))
                ? 'Si'
                : 'No';

            return $stmt->execute([
                $data['nombre'], 
                $data['cedula'], 
                $data['usuario'], 
                $data['rol'], 
                $esAsesor ? ($data['extension_telefono'] ?? null) : null,
                $esAsesor ? ($data['clave_webrtc'] ?? null) : null,
                $telefonoActivo,
                $id
            ]);
        }
    }
    
    /**
     * Detecta si la base de datos usa contraseñas planas o hasheadas
     * Analiza las contraseñas existentes para determinar el patrón
     */
    private function shouldUsePlainPasswords() {
        try {
            $stmt = $this->pdo->prepare("SELECT contrasena FROM usuarios WHERE contrasena IS NOT NULL AND contrasena != '' LIMIT 5");
            $stmt->execute();
            $passwords = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $hashedCount = 0;
            $plainCount = 0;
            
            foreach ($passwords as $password) {
                if (strpos($password, '$2y$') === 0) {
                    $hashedCount++;
                } else {
                    $plainCount++;
                }
            }
            
            // Si hay más contraseñas planas que hasheadas, usar planas
            return $plainCount >= $hashedCount;
        } catch (PDOException $e) {
            error_log("Error detectando tipo de contraseñas: " . $e->getMessage());
            // Por defecto, usar contraseñas hasheadas para mayor seguridad
            return false;
        }
    }

    public function toggleEstadoUsuario($id) {
        $usuario = $this->getUsuarioById($id);
        if ($usuario) {
            $nuevo_estado = ($usuario['estado'] == 'Activo') ? 'Inactivo' : 'Activo';
            $sql = "UPDATE usuarios SET estado = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$nuevo_estado, $id]);
        }
        return false;
    }

    public function getAsesores() {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE rol = 'asesor' AND estado = 'Activo' ORDER BY nombre_completo");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene los asesores asignados a un coordinador específico
     */
    public function getAsesoresByCoordinador($coordinador_id) {
        $sql = "SELECT u.*, aac.fecha_asignacion, aac.estado as estado_asignacion
                FROM usuarios u
                JOIN asignaciones_asesor_coordinador aac ON u.id = aac.asesor_id
                WHERE aac.coordinador_id = ? AND aac.estado = 'Activa' AND u.estado = 'Activo'
                ORDER BY aac.fecha_asignacion DESC, u.nombre_completo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$coordinador_id]);
        $asesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // NO devolver todos los asesores si no hay asignaciones
        // Solo devolver los que estén formalmente asignados
        return $asesores;
    }
    
    /**
     * Obtiene solo los asesores que NO están asignados a ningún coordinador
     */
    public function getAsesoresDisponibles() {
        $sql = "SELECT u.* FROM usuarios u 
                WHERE u.rol = 'asesor' AND u.estado = 'Activo' 
                AND u.id NOT IN (
                    SELECT DISTINCT aac.asesor_id 
                    FROM asignaciones_asesor_coordinador aac 
                    WHERE aac.estado = 'Activa'
                )
                ORDER BY u.nombre_completo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Asigna un asesor a un coordinador
     * IMPORTANTE: Desactiva automáticamente cualquier asignación previa del asesor
     */
    public function asignarAsesorACoordinador($asesorId, $coordinadorId) {
        try {
            // 1. DESACTIVAR TODAS LAS ASIGNACIONES PREVIAS DEL ASESOR (sin importar el coordinador)
            $sql = "UPDATE asignaciones_asesor_coordinador SET estado = 'Inactiva' WHERE asesor_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asesorId]);
            
            // 2. Verificar si ya existe la asignación al coordinador actual
            $sql = "SELECT id FROM asignaciones_asesor_coordinador WHERE asesor_id = ? AND coordinador_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$asesorId, $coordinadorId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // 3a. Reactivar asignación existente
                $sql = "UPDATE asignaciones_asesor_coordinador SET estado = 'Activa', fecha_asignacion = NOW() WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([$existing['id']]);
            } else {
                // 3b. Crear nueva asignación
                $sql = "INSERT INTO asignaciones_asesor_coordinador (asesor_id, coordinador_id, estado, fecha_asignacion) VALUES (?, ?, 'Activa', NOW())";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([$asesorId, $coordinadorId]);
            }
        } catch (Exception $e) {
            error_log("Error en asignarAsesorACoordinador: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Libera un asesor de un coordinador
     */
    public function liberarAsesorDeCoordinador($asesorId, $coordinadorId) {
        $sql = "UPDATE asignaciones_asesor_coordinador SET estado = 'Inactiva' WHERE asesor_id = ? AND coordinador_id = ? AND estado = 'Activa'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$asesorId, $coordinadorId]);
    }
    
    public function getCoordinadores() {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE rol = 'coordinador' AND estado = 'Activo' ORDER BY nombre_completo");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un asesor está asignado a un coordinador específico
     */
    public function isAsesorAsignadoACoordinador($asesorId, $coordinadorId) {
        $sql = "SELECT id FROM asignaciones_asesor_coordinador 
                WHERE asesor_id = ? AND coordinador_id = ? AND estado = 'Activa'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$asesorId, $coordinadorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false;
    }
    
    /**
     * Verifica si un coordinador tiene asesores asignados
     */
    public function tieneCoordinadorAsesoresAsignados($coordinadorId) {
        $sql = "SELECT COUNT(*) as total FROM asignaciones_asesor_coordinador 
                WHERE coordinador_id = ? AND estado = 'Activa'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$coordinadorId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }
}
?>
