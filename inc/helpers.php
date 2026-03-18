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