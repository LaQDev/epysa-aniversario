<?php
/**
 * Epysa 50 Años - Theme Functions
 * * Toda la lógica ha sido modularizada en la carpeta /inc/
 */

if (!defined('ABSPATH')) exit;

// Definir constante para la ruta del tema (opcional pero útil)
define('EPYSA_THEME_DIR', get_template_directory());
define('EPYSA_THEME_URI', get_template_directory_uri());

/**
 * Carga de módulos
 */

// 1. Configuración del Tema (Setup, Menús, Admin Bar)
require_once EPYSA_THEME_DIR . '/inc/setup.php';

// 2. Scripts y Estilos
require_once EPYSA_THEME_DIR . '/inc/scripts.php';

// 3. Funciones de Ayuda (SVG, etc)
require_once EPYSA_THEME_DIR . '/inc/helpers.php';

// 4. Custom Post Types
require_once EPYSA_THEME_DIR . '/inc/cpt-historias.php';

// 5. Sistema de Votación (Etapa 2)
require_once EPYSA_THEME_DIR . '/inc/votacion.php';

// 6. Sistema de Votación (AJAX)
require_once EPYSA_THEME_DIR . '/inc/ajax-galeria.php';

// 7. Login y Registro de usuario
require_once EPYSA_THEME_DIR . '/inc/auth-login.php';

// 8. Sistema de Exportación de Datos
require_once get_template_directory() . '/inc/exportador.php';

if (function_exists('acf_add_options_page')) {
    
    acf_add_options_page(array(
        'page_title'    => 'Configuración General del Sitio',
        'menu_title'    => 'Configuración Epysa',
        'menu_slug'     => 'configuracion-epysa',
        'capability'    => 'edit_posts',
        'redirect'      => false,
        'icon_url'      => 'dashicons-admin-settings', // Icono de tuerca
        'position'      => 2
    ));
    
}