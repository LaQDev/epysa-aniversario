<?php
/**
 * Lógica del Sistema de Votación - Epysa 50 Años
 * Ubicación: /inc/votacion.php
 */

if (!defined('ABSPATH'))
    exit;

/* =========================================
   1. PROCESAMIENTO AJAX DE VOTOS
   ========================================= */
add_action('wp_ajax_epysa_procesar_voto', 'epysa_procesar_voto');

function epysa_procesar_voto()
{
    // 1. Verificar Nonce y Login
    check_ajax_referer('epysa_auth_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['code' => 'not_logged_in', 'message' => 'Debes iniciar sesión.']);
    }

    $user_id = get_current_user_id();
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $accion = isset($_POST['tipo_accion']) ? sanitize_text_field($_POST['tipo_accion']) : '';

    if (!$post_id)
        wp_send_json_error(['message' => 'ID de historia inválido']);

    // 2. Obtener votos actuales (Array de IDs en UserMeta)
    $votos_usuario = get_user_meta($user_id, 'epysa_votos_usuario', true);
    if (!is_array($votos_usuario))
        $votos_usuario = array();

    // Obtener contador actual de la historia
    $total_votos_historia = (int) get_post_meta($post_id, 'epysa_total_votos', true);

    // --- ACCIÓN: VOTAR ---
    if ($accion === 'votar') {
        // Validación A: Límite de 3 votos
        if (count($votos_usuario) >= 3) {
            wp_send_json_error(['code' => 'limit_reached', 'message' => 'Límite de votos alcanzado.']);
        }

        // Validación B: Ya votó por esta historia
        if (in_array($post_id, $votos_usuario)) {
            wp_send_json_error(['message' => 'Ya votaste por esta historia.']);
        }

        // 1. Guardar en Usuario
        $votos_usuario[] = $post_id;
        update_user_meta($user_id, 'epysa_votos_usuario', $votos_usuario);

        // 2. Actualizar Contador en la Historia (Incrementar)
        $nuevo_total = $total_votos_historia + 1;
        update_post_meta($post_id, 'epysa_total_votos', $nuevo_total);

        wp_send_json_success(['action' => 'voted', 'count' => count($votos_usuario)]);
    }

    // --- ACCIÓN: QUITAR VOTO ---
    elseif ($accion === 'quitar') {
        // Buscar y eliminar del array
        if (($key = array_search($post_id, $votos_usuario)) !== false) {
            unset($votos_usuario[$key]);
            $votos_usuario = array_values($votos_usuario); // Reindexar
            update_user_meta($user_id, 'epysa_votos_usuario', $votos_usuario);

            // 2. Actualizar Contador en la Historia (Decrementar)
            $nuevo_total = max(0, $total_votos_historia - 1); // Evitar negativos
            update_post_meta($post_id, 'epysa_total_votos', $nuevo_total);

            wp_send_json_success(['action' => 'removed', 'count' => count($votos_usuario)]);
        } else {
            wp_send_json_error(['message' => 'No habías votado por esta historia.']);
        }
    }
}

/* =========================================
   2. COLUMNAS ADMIN PARA "HISTORIAS" (Ranking)
   ========================================= */

// Agregar la columna "Votos"
add_filter('manage_historia_posts_columns', 'epysa_agregar_columna_votos');
function epysa_agregar_columna_votos($columns)
{
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['votos_totales'] = '<span class="dashicons dashicons-chart-bar"></span> Votos';
        }
    }
    return $new_columns;
}

// Mostrar el número
add_action('manage_historia_posts_custom_column', 'epysa_mostrar_votos_columna', 10, 2);
function epysa_mostrar_votos_columna($column, $post_id)
{
    if ($column === 'votos_totales') {
        $votos = (int) get_post_meta($post_id, 'epysa_total_votos', true);
        if ($votos > 0) {
            echo '<strong style="color:#D32F2F; font-size:16px;">' . number_format_i18n($votos) . '</strong>';
        } else {
            echo '<span style="color:#999;">0</span>';
        }
    }
}

// Hacer ordenable
add_filter('manage_edit_historia_sortable_columns', 'epysa_hacer_votos_ordenables');
function epysa_hacer_votos_ordenables($columns)
{
    $columns['votos_totales'] = 'epysa_total_votos';
    return $columns;
}

// Lógica de ordenamiento
add_action('pre_get_posts', 'epysa_ordenar_historias_por_votos');
function epysa_ordenar_historias_por_votos($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    if ($query->get('orderby') === 'epysa_total_votos') {
        $query->set('meta_key', 'epysa_total_votos');
        $query->set('orderby', 'meta_value_num');
    }
}