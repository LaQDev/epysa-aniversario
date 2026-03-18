<?php
/**
 * Motor AJAX para Galería y Modales
 * Ubicación: inc/ajax-galeria.php
 */

if (!defined('ABSPATH'))
    exit;

/* =========================================
   1. FILTRADO DE HISTORIAS (GRILLA)
   ========================================= */
add_action('wp_ajax_filtrar_historias', 'epysa_filtrar_historias');
add_action('wp_ajax_nopriv_filtrar_historias', 'epysa_filtrar_historias');

function epysa_filtrar_historias()
{
    $busqueda = isset($_POST['busqueda']) ? sanitize_text_field($_POST['busqueda']) : '';
    $valor = isset($_POST['valor']) ? sanitize_text_field($_POST['valor']) : '';
    $orden = isset($_POST['orden']) ? sanitize_text_field($_POST['orden']) : 'desc';
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;

    // VALIDACIÓN DE ETAPA PARA OCULTAR BOTONES
    $etapa_actual = get_field('etapa_actual', 'option') ?: 1;
    $etapa_actual = intval($etapa_actual);

    // Obtener votos del usuario actual
    $votos_usuario = array();
    if (is_user_logged_in()) {
        $votos_usuario = get_user_meta(get_current_user_id(), 'epysa_votos_usuario', true);
        if (!is_array($votos_usuario))
            $votos_usuario = array();
    }

    $args = array(
        'post_type' => 'historia',
        'post_status' => 'publish',
        'posts_per_page' => 8,
        'paged' => $paged,
        'order' => $orden,
        'orderby' => 'date',
        'meta_query' => array('relation' => 'AND')
    );

    if (!empty($busqueda)) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array('key' => 'nombre', 'value' => $busqueda, 'compare' => 'LIKE'),
            array('key' => 'apellido', 'value' => $busqueda, 'compare' => 'LIKE')
        );
    }

    if (!empty($valor)) {
        $args['meta_query'][] = array('key' => 'valor_epysa', 'value' => $valor, 'compare' => 'LIKE');
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $nombre = get_field('nombre');
            $apellido = get_field('apellido');
            $anos = get_field('anos_epysa');
            $valor = get_field('valor_epysa');
            $excerpt = wp_trim_words(get_the_content(), 12, '...');
            $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');

            $slug_valor = sanitize_title($valor);
            $valores_map = [
                'pasion' => 'Pasión',
                'innovacion' => 'Innovación',
                'entereza' => 'Entereza',
                'seguridad' => 'Seguridad',
                'amistad' => 'Amistad'
            ];
            $valor_display = isset($valores_map[$slug_valor]) ? $valores_map[$slug_valor] : ucfirst(strtolower($valor));
            $icon_path = get_template_directory_uri() . '/assets/icons/icon-' . $slug_valor . '.svg';

            $ya_votado = in_array(get_the_ID(), $votos_usuario);
            $clase_btn = $ya_votado ? 'btn-quitar-voto' : 'btn-votar';
            $texto_btn = $ya_votado ? 'Quitar voto' : 'Votar';
            ?>
            <div class="col-12 col-lg-6">
                <div class="story-card-gallery">
                    <div class="card-col-text">
                        <div class="card-badge">
                            <div class="pill-valor val-<?php echo $slug_valor; ?>">
                                <img src="<?php echo $icon_path; ?>" alt="" class="pill-icon">
                                <?php echo esc_html($valor_display); ?>
                            </div>
                        </div>
                        <h3 class="story-author"><?php echo esc_html($nombre . ' ' . $apellido); ?></h3>
                        <div class="story-tenure">Trabaja hace <?php echo esc_html($anos); ?> años en Epysa</div>
                        <div class="story-excerpt"><?php echo esc_html($excerpt); ?></div>
                        <button class="btn-link-read" data-id="<?php echo get_the_ID(); ?>">Leer historia completa</button>
                    </div>
                    <div class="card-col-media">
                        <div class="profile-frame">
                            <?php if ($thumb_url): ?>
                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($nombre); ?>">
                            <?php else: ?>
                                <div class="placeholder-img"></div>
                            <?php endif; ?>
                        </div>

                        <?php if ($etapa_actual !== 3): ?>
                            <button class="btn <?php echo $clase_btn; ?>" data-id="<?php echo get_the_ID(); ?>"
                                onclick="gestionarVoto(this, <?php echo get_the_ID(); ?>)">
                                <?php echo $texto_btn; ?>
                            </button>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php
        }
        if ($paged < $query->max_num_pages) {
            echo '<div id="next-page-trigger" data-next="' . ($paged + 1) . '"></div>';
        }
    } else {
        echo '<div class="col-12 text-center py-5"><p class="text-muted">No encontramos historias.</p></div>';
    }
    wp_reset_postdata();
    die();
}

/* =========================================
   2. CARGA DE MODAL DETALLE HISTORIA
   ========================================= */
add_action('wp_ajax_cargar_historia_modal', 'epysa_cargar_historia_modal');
add_action('wp_ajax_nopriv_cargar_historia_modal', 'epysa_cargar_historia_modal');

function epysa_cargar_historia_modal()
{
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id)
        wp_send_json_error('ID inválido');

    // VALIDACIÓN DE ETAPA PARA OCULTAR BOTONES
    $etapa_actual = get_field('etapa_actual', 'option') ?: 1;
    $etapa_actual = intval($etapa_actual);

    $nombre = get_field('nombre', $post_id);
    $apellido = get_field('apellido', $post_id);
    $anos = get_field('anos_epysa', $post_id);
    $valor = get_field('valor_epysa', $post_id);
    $titulo = get_the_title($post_id);
    $post_obj = get_post($post_id);
    $relato = $post_obj->post_content;

    $votos_usuario = array();
    if (is_user_logged_in()) {
        $votos_usuario = get_user_meta(get_current_user_id(), 'epysa_votos_usuario', true);
        if (!is_array($votos_usuario))
            $votos_usuario = array();
    }
    $ya_votado = in_array($post_id, $votos_usuario);
    $clase_btn = $ya_votado ? 'btn-quitar-voto' : 'btn-votar-modal';
    $texto_btn = $ya_votado ? 'Quitar voto' : 'Votar por esta historia';

    $repeater_rows = get_field('archivos_historia', $post_id);
    $clean_files = array();

    if ($repeater_rows && is_array($repeater_rows)) {
        foreach ($repeater_rows as $row) {
            if (isset($row['archivo_subido'])) {
                $file_data = $row['archivo_subido'];
                if (is_array($file_data) && isset($file_data['url'])) {
                    $clean_files[] = array('url' => $file_data['url'], 'mime' => $file_data['mime_type'] ?? '');
                } elseif (is_string($file_data) && !empty($file_data)) {
                    $clean_files[] = array('url' => $file_data, 'mime' => (strpos($file_data, '.mp4') !== false) ? 'video/mp4' : 'image/jpeg');
                }
            }
        }
    }
    if (empty($clean_files)) {
        $thumb = get_the_post_thumbnail_url($post_id, 'large');
        if ($thumb)
            $clean_files[] = array('url' => $thumb, 'mime' => 'image/jpeg');
    }

    $slug_valor = sanitize_title($valor);
    $valores_map = [
        'pasion' => 'Pasión',
        'innovacion' => 'Innovación',
        'entereza' => 'Entereza',
        'seguridad' => 'Seguridad',
        'amistad' => 'Amistad'
    ];
    $valor_display = isset($valores_map[$slug_valor]) ? $valores_map[$slug_valor] : ucfirst(strtolower($valor));
    $icon_path = get_template_directory_uri() . '/assets/icons/icon-' . $slug_valor . '.svg';

    ob_start();
    ?>
    <div class="gallery-modal-layout">
        <button class="modal-close-icon" onclick="cerrarModalHistoria()">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/x-close.svg" alt="Cerrar">
        </button>

        <div class="modal-col-media">
            <?php
            $count = count($clean_files);
            $has_slider = $count > 1;
            ?>
            <div class="swiper modal-swiper <?php echo $has_slider ? 'is-slider' : 'is-single'; ?>">
                <div class="swiper-wrapper">
                    <?php foreach ($clean_files as $file):
                        $url = $file['url'];
                        $is_video = (strpos($file['mime'], 'video') !== false);
                        ?>
                        <div class="swiper-slide">
                            <div class="media-wrapper">
                                <?php if ($is_video): ?>
                                    <video controls playsinline>
                                        <source src="<?php echo esc_url($url); ?>" type="<?php echo esc_attr($file['mime']); ?>">
                                    </video>
                                <?php else: ?>
                                    <img src="<?php echo esc_url($url); ?>" alt="Historia">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($has_slider): ?>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="modal-col-content">
            <div class="modal-badge">
                <div class="pill-valor val-<?php echo $slug_valor; ?>">
                    <img src="<?php echo $icon_path; ?>" alt="" class="pill-icon">
                    <?php echo esc_html($valor_display); ?>
                </div>
            </div>
            <h2 class="modal-title"><?php echo esc_html($titulo); ?></h2>
            <div class="modal-author-block">
                <h3 class="author-name"><?php echo esc_html($nombre . ' ' . $apellido); ?></h3>
                <p class="author-tenure">Trabaja hace <?php echo esc_html($anos); ?> años en Epysa</p>
            </div>
            <div class="modal-body-text"><?php echo nl2br(esc_html($relato)); ?></div>

            <div class="modal-actions" <?php if ($etapa_actual === 3)
                echo 'style="justify-content: center; width: 100%;"'; ?>>
                <button class="btn-link-back" onclick="cerrarModalHistoria()">Volver</button>

                <?php if ($etapa_actual !== 3): ?>
                    <button class="btn <?php echo $clase_btn; ?>" data-id="<?php echo $post_id; ?>"
                        onclick="gestionarVoto(this, <?php echo $post_id; ?>)">
                        <?php echo $texto_btn; ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json_success($html);
}