<?php
// Archivo: views/shared_navbar.php
// Barra de navegación compartida para todas las vistas

// El gestor de sesiones ya está configurado globalmente

// Función para obtener la barra de navegación
function getNavbar($currentPage = '', $userRole = '') {
    // Obtener el rol del usuario de la sesión si no se proporciona
    if (empty($userRole)) {
        $session_manager = getSessionManager();
        $userRole = $session_manager->getUserRole();
    }
    
    $menuItems = [];
    
    // Menú según el rol del usuario
    switch ($userRole) {
        case 'administrador':
            $menuItems = [
                'Inicio' => 'index.php?action=dashboard',
                'Gestión' => 'index.php?action=ver_actividades',
                'Resultados' => 'index.php?action=asignar_personal',
                'Tareas' => 'index.php?action=ver_actividades',
                'Localización' => 'index.php?action=ver_actividades',
                'Registrar usuario' => 'index.php?action=crear_usuario',
                'Sitio Web' => '#'
            ];
            break;
            
        case 'coordinador':
            $menuItems = [
                'Inicio' => 'index.php?action=dashboard',
                'Gestión' => 'index.php?action=list_cargas',
                'Resultados' => 'index.php?action=resultados_equipo',
                'Tareas' => 'index.php?action=tareas_coordinador',
                'Reportes CSV' => 'index.php?action=reportes_exportacion'
            ];
            break;
            
        case 'asesor':
            $menuItems = [
                'Inicio' => 'index.php?action=dashboard',
                'Mis Clientes' => 'index.php?action=mis_clientes',
                
            ];
            break;
            
        default:
            $menuItems = [
                'Inicio' => 'index.php?action=dashboard'
            ];
    }
    
    $navbar = '
    <nav class="top-navbar">
        <div class="nav-container">
            <ul class="nav-menu">';
    
    foreach ($menuItems as $label => $url) {
        $activeClass = ($currentPage === $label) ? 'active' : '';
        $navbar .= '<li><a href="' . $url . '" class="' . $activeClass . '">' . $label . '</a></li>';
    }
    
    $navbar .= '
            </ul>
            <div class="user-section">
                <div class="user-greeting">
                    Bienvenido/a: <span class="user-name">' . htmlspecialchars(getSessionManager()->getUserName() ?? 'Usuario') . '</span>
                </div>
                <a href="index.php?action=logout" class="logout-btn">Cerrar</a>
            </div>
        </div>
    </nav>';


    return $navbar;
}

// Función alternativa para incluir la barra de navegación directamente
function includeNavbar($currentPage = '', $userRole = '') {
    echo getNavbar($currentPage, $userRole);
}
?>
