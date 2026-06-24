<?php
/**
 * Carga de archivos por fragmentos (chunks) vía AJAX
 * Permite pre-subir archivos al seleccionarlos, antes de enviar el formulario.
 */

if (!defined('ABSPATH')) exit;

define('EPYSA_MAX_UPLOAD_SIZE', 120 * 1024 * 1024); // 120 MB

add_action('wp_ajax_epysa_upload_chunk',        'epysa_ajax_upload_chunk');
add_action('wp_ajax_nopriv_epysa_upload_chunk', 'epysa_ajax_upload_chunk');

function epysa_ajax_upload_chunk() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'epysa_upload_nonce')) {
        wp_send_json_error(['message' => 'Error de seguridad.'], 403);
    }

    $file_uid     = isset($_POST['file_uid'])     ? preg_replace('/[^a-z0-9]/', '', strtolower($_POST['file_uid'])) : '';
    $chunk_index  = isset($_POST['chunk_index'])  ? intval($_POST['chunk_index'])                                    : -1;
    $total_chunks = isset($_POST['total_chunks']) ? intval($_POST['total_chunks'])                                   : 0;
    $file_name    = isset($_POST['file_name'])    ? sanitize_file_name($_POST['file_name'])                         : '';
    $file_type    = isset($_POST['file_type'])    ? sanitize_text_field($_POST['file_type'])                        : '';
    $total_size   = isset($_POST['total_size'])   ? intval($_POST['total_size'])                                    : 0;

    if (!$file_uid || $chunk_index < 0 || $total_chunks < 1 || !$file_name) {
        wp_send_json_error(['message' => 'Parámetros inválidos.'], 400);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];
    if (!in_array($file_type, $allowed_types, true)) {
        wp_send_json_error(['message' => 'Tipo de archivo no permitido.'], 400);
    }

    if ($total_size > EPYSA_MAX_UPLOAD_SIZE) {
        wp_send_json_error(['message' => 'El archivo supera el límite de 40 MB.'], 400);
    }

    if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error(['message' => 'Error al recibir el fragmento.'], 400);
    }

    $upload_dir  = wp_upload_dir();
    $chunks_base = $upload_dir['basedir'] . '/epysa-chunks/';
    $chunks_dir  = $chunks_base . $file_uid;

    if (!wp_mkdir_p($chunks_dir)) {
        wp_send_json_error(['message' => 'Error al crear directorio temporal.'], 500);
    }

    // Proteger el directorio base contra listado y acceso directo
    $htaccess = $chunks_base . '.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Options -Indexes\nDeny from all\n");
    }

    $chunk_path = $chunks_dir . '/chunk_' . str_pad($chunk_index, 5, '0', STR_PAD_LEFT);
    if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path)) {
        wp_send_json_error(['message' => 'Error al guardar el fragmento.'], 500);
    }

    $received = count(glob($chunks_dir . '/chunk_*'));

    if ($received < $total_chunks) {
        wp_send_json_success([
            'status'   => 'chunk_received',
            'received' => $received,
            'total'    => $total_chunks,
        ]);
        return;
    }

    // Todos los fragmentos recibidos — ensamblar el archivo
    $assembled_path = $chunks_dir . '/' . $file_name;
    $out = fopen($assembled_path, 'wb');
    if (!$out) {
        epysa_remove_chunks_dir($chunks_dir);
        wp_send_json_error(['message' => 'Error al ensamblar el archivo.'], 500);
    }

    for ($i = 0; $i < $total_chunks; $i++) {
        $chunk = $chunks_dir . '/chunk_' . str_pad($i, 5, '0', STR_PAD_LEFT);
        if (!file_exists($chunk)) {
            fclose($out);
            epysa_remove_chunks_dir($chunks_dir);
            wp_send_json_error(['message' => "Fragmento $i no encontrado. Intenta nuevamente."], 500);
        }
        $in = fopen($chunk, 'rb');
        stream_copy_to_stream($in, $out);
        fclose($in);
    }
    fclose($out);

    // Importar a la biblioteca de medios de WordPress
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $file_array = [
        'name'     => $file_name,
        'tmp_name' => $assembled_path,
        'type'     => $file_type,
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($assembled_path),
    ];

    $sideload = wp_handle_sideload($file_array, ['test_form' => false]);

    if (isset($sideload['error'])) {
        epysa_remove_chunks_dir($chunks_dir);
        wp_send_json_error(['message' => $sideload['error']], 500);
    }

    $attach_id = wp_insert_attachment([
        'post_mime_type' => $sideload['type'],
        'post_title'     => sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME)),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ], $sideload['file']);

    if (is_wp_error($attach_id)) {
        epysa_remove_chunks_dir($chunks_dir);
        wp_send_json_error(['message' => 'Error al registrar el archivo en la biblioteca.'], 500);
    }

    $attach_data = wp_generate_attachment_metadata($attach_id, $sideload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    epysa_remove_chunks_dir($chunks_dir);

    wp_send_json_success([
        'status'        => 'complete',
        'attachment_id' => $attach_id,
    ]);
}

function epysa_remove_chunks_dir($dir) {
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*') as $file) {
        if (is_file($file)) @unlink($file);
    }
    @rmdir($dir);
}

// Limpieza de chunks huérfanos (más de 2 horas sin modificar)
add_action('wp_scheduled_delete', 'epysa_cleanup_orphaned_chunks');
function epysa_cleanup_orphaned_chunks() {
    $upload_dir = wp_upload_dir();
    $base = $upload_dir['basedir'] . '/epysa-chunks/';
    if (!is_dir($base)) return;
    foreach (glob($base . '*', GLOB_ONLYDIR) as $dir) {
        if ((time() - filemtime($dir)) > 7200) {
            epysa_remove_chunks_dir($dir);
        }
    }
}
