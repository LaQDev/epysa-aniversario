<?php
/* Template Name: Página Perfil Usuario */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/?auth_error=login_required'));
    exit;
}

get_header();

$current_user = wp_get_current_user();
$first_name = !empty($current_user->first_name) ? $current_user->first_name : 'Usuario';

$votos_ids = get_user_meta($current_user->ID, 'epysa_votos_usuario', true);
if (!is_array($votos_ids))
    $votos_ids = array();

$args = array(
    'post_type' => 'historia',
    'post_status' => 'publish',
    'post__in' => !empty($votos_ids) ? $votos_ids : array(0),
    'posts_per_page' => -1,
    'orderby' => 'post__in'
);
$query = new WP_Query($args);
?>

<div class="page-perfil-wrapper">
    <div class="container">

        <div class="profile-header text-start">
            <h1 class="profile-greeting">Hola, <?php echo esc_html($first_name); ?>.</h1>

            <div class="profile-actions-row">
                <p class="profile-subtitle">¿Este no es tu nombre? No te preocupes, acá lo puedes corregir.</p>
                <button class="btn-change-name" onclick="abrirModalCambiarNombre()">
                    Cambiar nombre
                </button>
            </div>
        </div>

        <div class="section-title-wrapper text-start mb-4">
            <h2 class="section-title-profile">Acá puedes revisar tus votos.</h2>
        </div>

        <div class="galeria-grid-section pt-0 pb-5">
            <?php if ($query->have_posts()): ?>
                <div class="row g-4" id="grid-votos-usuario">
                    <?php while ($query->have_posts()):
                        $query->the_post();
                        $nombre = get_field('nombre');
                        $apellido = get_field('apellido');
                        $anos = get_field('anos_epysa');
                        $valor = get_field('valor_epysa');
                        $excerpt = wp_trim_words(get_the_content(), 12, '...');
                        $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');

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
                        <div class="col-12 col-lg-6 story-item" id="story-<?php echo get_the_ID(); ?>">

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
                                    <button class="btn-link-read" onclick="cargarModalHistoria(<?php echo get_the_ID(); ?>)">
                                        Leer historia completa
                                    </button>
                                </div>

                                <div class="card-col-media">
                                    <div class="profile-frame">
                                        <?php if ($thumb): ?>
                                            <img src="<?php echo esc_url($thumb); ?>" alt="">
                                        <?php else: ?>
                                            <div class="placeholder-img"></div>
                                        <?php endif; ?>
                                    </div>

                                    <button class="btn btn-quitar-voto" data-id="<?php echo get_the_ID(); ?>"
                                        onclick="gestionarVoto(this, <?php echo get_the_ID(); ?>)">
                                        Quitar voto
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state text-center py-5">
                    <p class="text-muted mb-4">Aún no has votado por ninguna historia.</p>
                    <a href="<?php echo home_url('/galeria-de-votacion'); ?>" class="btn btn-primary">Ir a votar</a>
                </div>
            <?php endif;
            wp_reset_postdata(); ?>
        </div>

    </div>
</div>

<?php get_footer(); ?>