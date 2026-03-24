<?php
/**
 * Componente Modular: Hero
 * Ubicación: template-parts/hero.php
 */

// 1. Recuperar valores ACF principales
$bg_type = get_field('hero_bg_type'); // 'image' o 'video'
$video_id = get_field('hero_video_id'); // Solo el ID (texto)

// 2. Llamamos al GRUPO completo para las imágenes responsivas
$grupo_fondo = get_field('hero_image');

$bg_desktop = '';
$bg_tablet  = '';
$bg_mobile  = '';

if (is_array($grupo_fondo)) {
    $bg_desktop = isset($grupo_fondo['hero_image_desktop']) ? $grupo_fondo['hero_image_desktop'] : '';
    $bg_tablet  = isset($grupo_fondo['hero_image_tablet']) ? $grupo_fondo['hero_image_tablet'] : '';
    $bg_mobile  = isset($grupo_fondo['hero_image_mobile']) ? $grupo_fondo['hero_image_mobile'] : '';
} elseif (is_string($grupo_fondo) && !empty($grupo_fondo)) {
    // Fallback por si la base de datos aún tiene la imagen guardada a la manera antigua
    $bg_desktop = $grupo_fondo;
}

// 3. Fallbacks de seguridad (si no suben la de tablet/móvil, heredan la de desktop)
if (!$bg_tablet) $bg_tablet = $bg_desktop;
if (!$bg_mobile) $bg_mobile = $bg_tablet;

// Badge
$show_badge = get_field('hero_show_badge');
$pretitle = get_field('hero_pretitle');

// Contenidos
$title = get_field('hero_title');
$subtitle = get_field('hero_subtitle');

// Botón
$show_btn = get_field('hero_show_btn');
$btn_text = get_field('hero_btn_text');
$btn_link = get_field('hero_btn_link');
?>

<section id="hero" class="hero-section">

    <div class="hero-bg">
        <?php if ($bg_type === 'video' && $video_id): ?>
            <div class="video-container">
                <iframe
                    src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>?controls=0&autoplay=1&mute=1&loop=1&playlist=<?php echo esc_attr($video_id); ?>&showinfo=0&rel=0&iv_load_policy=3&modestbranding=1"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
            <div class="bg-overlay"></div>

        <?php elseif ($bg_desktop): ?>
            <div class="bg-media responsive-bg" style="--bg-desktop: url('<?php echo esc_url($bg_desktop); ?>'); --bg-tablet: url('<?php echo esc_url($bg_tablet); ?>'); --bg-mobile: url('<?php echo esc_url($bg_mobile); ?>');"></div>
            <div class="bg-overlay"></div>

        <?php else: ?>
            <div class="bg-media" style="background-color: #E30613;"></div>
        <?php endif; ?>
    </div>

    <div class="container h-100">
        <div class="row h-100 align-items-center">

            <div class="hero-content">

                <?php if ($show_badge && $pretitle): ?>
                    <span class="hero-badge">
                        <?php echo esc_html($pretitle); ?>
                    </span>
                <?php endif; ?>

                <?php if ($title): ?>
                    <h1 class="hero-title">
                        <?php echo nl2br(esc_html($title)); ?>
                    </h1>
                <?php endif; ?>

                <?php if ($subtitle): ?>
                    <div class="hero-subtitle">
                        <?php echo $subtitle; ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_btn && $btn_text && $btn_link): ?>
                    <div class="hero-actions">
                        <a href="<?php echo esc_url($btn_link); ?>" class="btn btn-outline-white">
                            <?php echo esc_html($btn_text); ?>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

</section>