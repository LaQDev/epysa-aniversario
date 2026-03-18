<?php
// inc/cpt-historias.php

if (!defined('ABSPATH')) exit;

/**
 * Registrar CPT "Historias"
 */
function registrar_cpt_historias() {
    $labels = array(
        'name'               => 'Historias',
        'singular_name'      => 'Historia',
        'menu_name'          => 'Historias',
        'add_new'            => 'Añadir Nueva',
        'add_new_item'       => 'Añadir Nueva Historia',
        'edit_item'          => 'Editar Historia',
        'new_item'          => 'Nueva Historia',
        'view_item'          => 'Ver Historia',
        'search_items'       => 'Buscar Historias',
        'not_found'          => 'No se encontraron historias',
        'not_found_in_trash' => 'No hay historias en la papelera'
    );

    $args = array(
        'labels'          => $labels,
        'public'          => true,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'menu_position'   => 5,
        'menu_icon'       => 'dashicons-book-alt',
        'capability_type' => 'post',
        'hierarchical'    => false,
        'supports'        => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'has_archive'     => true,
        'rewrite'         => array('slug' => 'historias'),
    );
    
    register_post_type('historia', $args);
}
add_action('init', 'registrar_cpt_historias');