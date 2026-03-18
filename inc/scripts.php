<?php
// inc/scripts.php

if (!defined('ABSPATH'))
    exit;

/**
 * Encolar estilos y scripts
 */
function epysa_scripts()
{
    // 1. Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap', array(), null);

    // 2. Estilo Principal
    wp_enqueue_style('epysa-main-style', get_template_directory_uri() . '/css/main.css');

    // 3. Scripts Generales (Librerías y Nav)
    wp_enqueue_script('epysa-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '1.1', true);
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true);

    // 4. Main JS
    wp_enqueue_script('epysa-main-js', get_template_directory_uri() . '/js/main.js', array('swiper-js'), '1.0', true);

    // 5. Script Específico: Página Participa
    if (is_page_template('page-participa.php')) {
        wp_enqueue_script('participa-js', get_template_directory_uri() . '/js/participa.js', array(), '1.1', true);
        wp_localize_script('participa-js', 'epysa_vars', array('theme_url' => get_template_directory_uri()));
    }

    // --- SISTEMA DE AUTENTICACIÓN (Global) ---
    // Cargamos auth.js siempre porque el modal de login está en el footer
    wp_enqueue_script('auth-js', get_template_directory_uri() . '/js/auth.js', array('jquery'), '1.0', true);

    // DEFINICIÓN DE LA VARIABLE AJAX (Global para todos los scripts dependientes)
    wp_localize_script('auth-js', 'epysa_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('epysa_auth_nonce')
    ));

    // Script de Votación (depende de auth-js)
    wp_enqueue_script('votacion-js', get_template_directory_uri() . '/js/votacion.js', array('auth-js'), '1.0', true);

    // --- PAGINAS ESPECÍFICAS (Galería de Votación, Perfil y Galería Etapa 3) ---
    // AQUÍ ESTÁ EL CAMBIO: Agregamos is_page_template('page-galeria-historias.php')
    if (is_page_template('page-galeria.php') || is_page_template('page-perfil.php') || is_page_template('page-galeria-historias.php')) {

        // JS de la Galería (Modales, Swiper, Carga AJAX)
        wp_enqueue_script('galeria-js', get_template_directory_uri() . '/js/galeria.js', array('swiper-js', 'auth-js'), '1.0', true);

        // JS del Perfil (Solo si estamos en perfil)
        if (is_page_template('page-perfil.php')) {
            wp_enqueue_script('perfil-js', get_template_directory_uri() . '/js/perfil.js', array('votacion-js'), '1.0', true);
        }
    }
}
add_action('wp_enqueue_scripts', 'epysa_scripts');