<?php
// inc/setup.php

if (!defined('ABSPATH')) exit;

/**
 * Configuración inicial del tema
 */
function epysa_setup() {
    // Permite que WP gestione la etiqueta <title>
    add_theme_support('title-tag');

    // Habilita imágenes destacadas
    add_theme_support('post-thumbnails');

    // Habilita soporte para logotipo personalizado
    add_theme_support('custom-logo');
}
add_action('after_setup_theme', 'epysa_setup');

/**
 * Registrar ubicaciones de menús
 */
function epysa_register_menus() {
    register_nav_menus(array(
        'menu-principal' => __('Menú Principal (Header)', 'epysa-aniversario'),
        'menu-footer'    => __('Menú del Pie de Página', 'epysa-aniversario')
    ));
}
add_action('init', 'epysa_register_menus');

/**
 * Gestionar la barra de administración
 */
// Forzar mostrar durante desarrollo (opcional, puedes comentarlo en producción)
show_admin_bar(true);

function epysa_disable_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'epysa_disable_admin_bar');