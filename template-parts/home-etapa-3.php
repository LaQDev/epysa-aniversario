<?php
/**
 * Inicio - Etapa 3 (Ganadores)
 * Ubicación: template-parts/home-etapa-3.php
 */

// 1. HERO MODULAR
get_template_part('template-parts/hero');

// --- RECOLECTOR DE IDs PARA MODALES ---
// Aquí guardaremos los IDs de todas las historias mostradas en la página para generar sus modales al final.
$modal_post_ids = [];

// 2. RECUPERAR HISTORIAS GANADORAS DESDE ACF
$post_1 = get_field('historia_ganadora_1');
$post_2 = get_field('historia_ganadora_2');
$post_3 = get_field('historia_ganadora_3');

$ganadores = [
    ['post' => $post_1, 'place_class' => 'place-1', 'badge_text' => 'Historia Más Votada', 'col_class' => 'col-12'],
    ['post' => $post_2, 'place_class' => 'place-2', 'badge_text' => 'Segundo Lugar', 'col_class' => 'col-12 col-lg-6'],
    ['post' => $post_3, 'place_class' => 'place-3', 'badge_text' => 'Tercer Lugar', 'col_class' => 'col-12 col-lg-6'],
];
?>

<section id="historias-ganadoras" class="section-ganadores">
    <div class="container">

        <h2 class="section-title text-center">Historias Ganadoras del Viaje a Chiloé</h2>

        <div class="row g-4">
            <?php
            foreach ($ganadores as $ganador):
                if (!$ganador['post'])
                    continue;

                $p_id = is_object($ganador['post']) ? $ganador['post']->ID : $ganador['post'];
                $modal_post_ids[] = $p_id; // Guardamos el ID para el modal
            
                $nombre = get_field('nombre', $p_id);
                $apellido = get_field('apellido', $p_id);
                $anos = get_field('anos_epysa', $p_id);
                $valor = get_field('valor_epysa', $p_id);
                $excerpt = wp_trim_words(get_post_field('post_content', $p_id), 15, '...');
                $thumb = get_the_post_thumbnail_url($p_id, 'large');

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
                ?>
                <div class="<?php echo $ganador['col_class']; ?>">

                    <div class="winner-card <?php echo $ganador['place_class']; ?>">

                        <div class="pattern-bg"></div>

                        <div class="winner-left">
                            <?php if ($thumb): ?>
                                <img src="<?php echo esc_url($thumb); ?>" alt="Ganador">
                            <?php else: ?>
                                <div class="placeholder-img"></div>
                            <?php endif; ?>
                        </div>

                        <div class="winner-right">
                            <div class="winner-right-top">
                                <div class="pill-valor val-<?php echo $slug_valor; ?>">
                                    <img src="<?php echo $icon_path; ?>" alt="" class="pill-icon">
                                    <?php echo esc_html($valor_display); ?>
                                </div>
                                <div class="winner-badge">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/icon-reward.svg"
                                        alt="Premio">
                                    <span><?php echo $ganador['badge_text']; ?></span>
                                </div>
                            </div>

                            <div class="winner-right-mid">
                                <h3 class="story-author"><?php echo esc_html($nombre . ' ' . $apellido); ?></h3>
                                <p class="story-tenure">Trabaja hace <?php echo esc_html($anos); ?> años en Epysa</p>
                                <div class="story-excerpt">
                                    <?php echo esc_html($excerpt); ?>
                                </div>
                            </div>

                            <div class="winner-right-bottom">
                                <button class="btn-link-read btn-story-trigger"
                                    data-modal-id="modal-winner-<?php echo $p_id; ?>">
                                    Leer historia completa
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<?php
$sorteo_titulo = get_field('sorteo_titulo') ?: 'Ganadores del Sorteo';
$sorteo_bajada = get_field('sorteo_bajada') ?: 'Felicitamos a los ganadores de una Cena Doble simplemente por votar sus historias favoritas.';
?>
<section id="ganadores-sorteo" class="section-sorteo">
    <div class="container">

        <div class="sorteo-header">
            <h2 class="section-title text-center"><?php echo esc_html($sorteo_titulo); ?></h2>
            <div class="sorteo-subtitle">
                <?php echo wp_kses_post($sorteo_bajada); ?>
            </div>
        </div>

        <?php if (have_rows('sorteo_ganadores')): ?>
            <div class="sorteo-grid">
                <?php while (have_rows('sorteo_ganadores')):
                    the_row();
                    $icono = get_sub_field('icono');
                    $nombre = get_sub_field('nombre');
                    $area = get_sub_field('area');
                    $premio = get_sub_field('premio');
                    ?>
                    <div class="sorteo-card">
                        <?php if ($icono): ?>
                            <div class="sorteo-icon">
                                <img src="<?php echo esc_url(is_array($icono) ? $icono['url'] : $icono); ?>" alt="Premio">
                            </div>
                        <?php endif; ?>
                        <div class="sorteo-info">
                            <h3 class="sorteo-name"><?php echo esc_html($nombre); ?></h3>
                            <p class="sorteo-area"><?php echo esc_html($area); ?></p>
                        </div>
                        <div class="sorteo-prize">
                            <?php echo esc_html($premio); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php
$destacadas_ids = get_field('historias_destacadas_posts');

// Agregamos los IDs de las destacadas a nuestro recolector
if ($destacadas_ids) {
    foreach ($destacadas_ids as $d_id) {
        $modal_post_ids[] = is_object($d_id) ? $d_id->ID : $d_id;
    }
}
?>
<section id="historias-destacadas" class="section-historias section-destacadas">
    <div class="container">

        <div class="row">
            <div class="col-12 text-start">
                <h2 class="section-title">Historias Destacadas</h2>
            </div>
        </div>

        <div class="swiper historias-swiper">
            <div class="swiper-wrapper">
                <?php
                if ($destacadas_ids):
                    foreach ($destacadas_ids as $p_id):
                        $p_id = is_object($p_id) ? $p_id->ID : $p_id;

                        $nombre = get_field('nombre', $p_id);
                        $apellido = get_field('apellido', $p_id);
                        $anos = get_field('anos_epysa', $p_id);
                        $valor = get_field('valor_epysa', $p_id);
                        $excerpt = wp_trim_words(get_post_field('post_content', $p_id), 12, '...');
                        $thumb = get_the_post_thumbnail_url($p_id, 'medium');

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
                        ?>
                        <div class="swiper-slide">
                            <div class="story-card">
                                <div class="story-img-col">
                                    <div class="story-img">
                                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($nombre); ?>">
                                    </div>
                                    <div class="pill-valor val-<?php echo $slug_valor; ?> mt-3">
                                        <img src="<?php echo $icon_path; ?>" alt="" class="pill-icon">
                                        <?php echo esc_html($valor_display); ?>
                                    </div>
                                </div>

                                <div class="story-content-col">
                                    <div class="story-meta">
                                        <h3 class="story-name"><?php echo esc_html($nombre . ' ' . $apellido); ?></h3>
                                        <div class="story-bio">Trabaja hace <?php echo esc_html($anos); ?> años en Epysa</div>
                                    </div>
                                    <div class="story-body">
                                        <div class="story-excerpt"><?php echo esc_html($excerpt); ?></div>
                                        <button class="btn-story-trigger" data-modal-id="modal-winner-<?php echo $p_id; ?>">
                                            Leer historia completa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
            </div>

            <div class="historias-controls">
                <div class="swiper-pagination"></div>
                <div class="historias-nav-buttons">
                    <button
                        class="swiper-button-custom story-prev"><?php include get_template_directory() . '/assets/icons/nav-red-prev.svg'; ?></button>
                    <button
                        class="swiper-button-custom story-next"><?php include get_template_directory() . '/assets/icons/nav-red-next.svg'; ?></button>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo home_url('/galeria-de-historias'); ?>" class="btn btn-primary btn-lg">
                Explorar todas las historias
            </a>
        </div>

    </div>
</section>

<?php
// Limpiamos IDs duplicados por si una historia es ganadora y destacada a la vez
$modal_post_ids = array_unique($modal_post_ids);

foreach ($modal_post_ids as $p_id):
    if (!$p_id)
        continue;

    $nombre = get_field('nombre', $p_id);
    $apellido = get_field('apellido', $p_id);
    $anos = get_field('anos_epysa', $p_id);
    $valor = get_field('valor_epysa', $p_id);
    $titulo = get_the_title($p_id);
    $full = get_post_field('post_content', $p_id);

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

    $repeater_rows = get_field('archivos_historia', $p_id);
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
        $thumb = get_the_post_thumbnail_url($p_id, 'large');
        if ($thumb)
            $clean_files[] = array('url' => $thumb, 'mime' => 'image/jpeg');
    }
    ?>
    <div id="modal-winner-<?php echo $p_id; ?>" class="epysa-modal gallery-modal-theme" aria-hidden="true">
        <div class="epysa-modal-overlay" data-close-modal></div>
        <div class="epysa-modal-container">
            <div class="gallery-modal-layout">

                <button class="modal-close-icon" data-close-modal>
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
                                                <source src="<?php echo esc_url($url); ?>"
                                                    type="<?php echo esc_attr($file['mime']); ?>">
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

                    <div class="modal-body-text">
                        <?php echo nl2br(esc_html($full)); ?>
                    </div>

                    <div class="modal-actions" style="justify-content: center; width: 100%;">
                        <button class="btn-link-back" data-close-modal>Volver</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const triggers = document.querySelectorAll('.btn-story-trigger');
        triggers.forEach(trigger => {
            trigger.addEventListener('click', function () {
                const modalId = this.getAttribute('data-modal-id');
                const modal = document.getElementById(modalId);
                if (modal) {
                    const swiperEl = modal.querySelector('.modal-swiper.is-slider');
                    if (swiperEl && !swiperEl.swiper) {
                        new Swiper(swiperEl, {
                            loop: false,
                            slidesPerView: 1,
                            navigation: {
                                nextEl: '.swiper-button-next',
                                prevEl: '.swiper-button-prev',
                            },
                            pagination: {
                                el: '.swiper-pagination',
                                clickable: true,
                            },
                            on: {
                                slideChange: function () {
                                    const videos = swiperEl.querySelectorAll('video');
                                    videos.forEach(v => v.pause());
                                }
                            }
                        });
                    }
                }
            });
        });
    });
</script>

<?php get_template_part('template-parts/banner', 'cierre'); ?>