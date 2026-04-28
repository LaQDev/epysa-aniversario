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

/**
 * Validación defensiva de SVG en upload.
 *
 * Para sanitización completa lo recomendable es instalar el plugin
 * Safe SVG (10up). Este filtro rechaza SVGs con patrones que claramente
 * indican ejecución de código y sirve como defensa en profundidad.
 */
add_filter('wp_handle_upload_prefilter', 'epysa_reject_unsafe_svg');
function epysa_reject_unsafe_svg($file) {
    $type = isset($file['type']) ? $file['type'] : '';
    $name = isset($file['name']) ? $file['name'] : '';

    $is_svg = ($type === 'image/svg+xml')
        || (strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'svg');

    if (!$is_svg) {
        return $file;
    }

    $content = @file_get_contents($file['tmp_name']);
    if ($content === false) {
        return $file;
    }

    $dangerous_patterns = array(
        '/<\?/',                                  // <?php
        '/<%/',                                   // <% ASP
        '/<script\b/i',                           // <script>
        '/\bon[a-z]+\s*=/i',                      // onclick=, onload=, etc.
        '/javascript\s*:/i',                      // javascript: URI
        '/<foreignObject\b/i',                    // permite HTML embebido
        '/<use\b[^>]*\bhref\s*=\s*["\']https?:/i', // <use href="http..."
        '/<iframe\b/i',
        '/<embed\b/i',
        '/<object\b/i',
    );

    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $file['error'] = 'El SVG contiene contenido potencialmente peligroso y fue rechazado.';
            return $file;
        }
    }

    return $file;
}