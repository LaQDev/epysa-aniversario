<?php
// inc/helpers.php

if (!defined('ABSPATH')) exit;

/**
 * Permitir subida de archivos SVG
 */
function epysa_add_file_types_to_uploads($file_types) {
    $new_filetypes = array();
    $new_filetypes['svg'] = 'image/svg+xml';
    $file_types = array_merge($file_types, $new_filetypes);
    return $file_types;
}
add_filter('upload_mimes', 'epysa_add_file_types_to_uploads');

/**
 * Imprime el contenido de un SVG del theme de forma segura.
 *
 * Usar en lugar de `include` para SVG: file_get_contents no evalúa PHP,
 * por lo que un SVG con código inyectado no se ejecutaría.
 */
function epysa_svg_inline($relative_path) {
    $path = get_template_directory() . '/' . ltrim($relative_path, '/');
    if (file_exists($path)) {
        echo file_get_contents($path);
    }
}