<?php
/**
 * Inicio - Etapa 1 (Convocatoria)
 * Ubicación: template-parts/home-etapa-1.php
 */

// 1. HERO MODULAR
get_template_part('template-parts/hero');
?>

<section id="historias" class="section-historias">
    <div class="container">
        <div class="row">
            <div class="col-12 text-start">
                <h2 class="section-title">Mira quiénes ya se sumaron</h2>
            </div>
        </div>
        <div class="swiper historias-swiper">
            <div class="swiper-wrapper">
                <?php if (have_rows('listado_historias')):
                    $i = 0;
                    while (have_rows('listado_historias')):
                        the_row();
                        $i++;
                        $foto = get_sub_field('foto'); ?>
                        <div class="swiper-slide">
                            <div class="story-card">
                                <div class="story-img-col">
                                    <div class="story-img"><img src="<?php echo esc_url($foto['url'] ?? $foto); ?>" alt="">
                                    </div>
                                </div>
                                <div class="story-content-col">
                                    <div class="story-meta">
                                        <h3 class="story-name"><?php echo esc_html(get_sub_field('nombre')); ?></h3>
                                        <div class="story-bio"><?php echo esc_html(get_sub_field('bajada')); ?></div>
                                    </div>
                                    <div class="story-body">
                                        <div class="story-excerpt"><?php echo esc_html(get_sub_field('extracto')); ?>...</div>
                                        <button class="btn-story-trigger" data-modal-id="modal-story-<?php echo $i; ?>">Ver
                                            historia completa</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; endif; ?>
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
    </div>
</section>
<?php if (have_rows('listado_historias')):
    $j = 0;
    while (have_rows('listado_historias')):
        the_row();
        $j++;
        $video = get_sub_field('video_file'); ?>
        <div id="modal-story-<?php echo $j; ?>" class="epysa-modal" aria-hidden="true">
            <div class="epysa-modal-overlay" data-close-modal></div>
            <div class="epysa-modal-container">
                <button class="modal-close-icon"
                    data-close-modal><?php include get_template_directory() . '/assets/icons/x-close.svg'; ?></button>
                <div class="modal-content-scroll">
                    <?php if ($video): ?>
                        <div class="modal-video-wrapper"><video controls class="story-video">
                                <source src="<?php echo esc_url($video); ?>" type="video/mp4">
                            </video></div><?php endif; ?>
                    <div class="modal-info">
                        <h3 class="modal-name"><?php echo esc_html(get_sub_field('nombre')); ?></h3>
                        <div class="modal-bio"><?php echo esc_html(get_sub_field('bajada')); ?></div>
                    </div>
                    <div class="modal-text-body"><?php echo get_sub_field('historia_completa'); ?></div>
                    <div class="modal-footer"><button class="link-back" data-close-modal>Volver</button></div>
                </div>
            </div>
        </div>
    <?php endwhile; endif; ?>

<?php
$p_title = get_field('premios_title');
$p_subtitle = get_field('premios_subtitle');
$cta_text = get_field('premios_cta_text');
$cta_link = get_field('premios_cta_link');
$legal_text = get_field('premios_legal_text');
$legal_link = get_field('premios_legal_link');
?>
<section id="premios" class="section-premios">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-12 text-center">
                <?php if ($p_title): ?>
                    <h2 class="section-title w-100"><?php echo esc_html($p_title); ?></h2><?php endif; ?>
                <?php if ($p_subtitle): ?>
                    <div class="section-subtitle w-100"><?php echo nl2br(esc_html($p_subtitle)); ?></div><?php endif; ?>
            </div>
        </div>
        <?php if (have_rows('listado_premios')): ?>
            <div class="prize-stack">
                <?php $i = 0;
                while (have_rows('listado_premios')):
                    the_row();
                    $i++;
                    $layout_class = ($i % 2 != 0) ? 'card-text-left' : 'card-text-right'; ?>
                    <div class="prize-card <?php echo $layout_class; ?>">
                        <div class="prize-content">
                            <?php if ($icon = get_sub_field('icono')): ?>
                                <div class="prize-icon-wrapper"><img src="<?php echo esc_url($icon); ?>" class="prize-icon"
                                        alt="Icono"></div><?php endif; ?>
                            <div class="prize-text-group">
                                <h3 class="prize-title"><?php echo esc_html(get_sub_field('titulo')); ?></h3>
                                <div class="prize-description"><?php echo get_sub_field('descripcion'); ?></div>
                            </div>
                        </div>
                        <?php if ($img = get_sub_field('imagen')): ?>
                            <div class="prize-img-wrapper"><img src="<?php echo esc_url($img); ?>" alt=""></div><?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
        <div class="section-footer">
            <?php if ($legal_text): ?>
                <div class="legal-wrapper text-start"><a href="<?php echo esc_url($legal_link ?: '#'); ?>"
                        class="legal-link"><?php echo esc_html($legal_text); ?></a></div><?php endif; ?>
            <?php if ($cta_text && $cta_link): ?>
                <div class="cta-wrapper text-center"><a href="<?php echo esc_url($cta_link); ?>"
                        class="btn btn-primary"><?php echo esc_html($cta_text); ?></a></div><?php endif; ?>
        </div>
    </div>
</section>

<?php $steps_title = get_field('pasos_section_title'); ?>
<section id="pasos" class="section-pasos bg-outline-alt">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-start">
                <h2 class="section-title">
                    <?php echo $steps_title ? esc_html($steps_title) : '¿Cómo agregar tus kilómetros a la historia?'; ?>
                </h2>
            </div>
        </div>
        <?php if (have_rows('listado_pasos')): ?>
            <div class="row">
                <?php while (have_rows('listado_pasos')):
                    the_row(); ?>
                    <div class="col-12 col-md-4 mb-4">
                        <div class="step-card h-100">
                            <?php if ($icon = get_sub_field('icono')): ?>
                                <div class="step-icon"><img src="<?php echo esc_url($icon); ?>" alt="Icono"></div><?php endif; ?>
                            <div class="step-content">
                                <?php if ($pre = get_sub_field('pre_titulo')): ?><span
                                        class="step-pretitle"><?php echo esc_html($pre); ?></span><?php endif; ?>
                                <h3 class="step-title"><?php echo esc_html(get_sub_field('titulo')); ?></h3>
                                <div class="step-desc"><?php echo get_sub_field('descripcion'); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$v_title = get_field('valores_title');
$v_subtitle = get_field('valores_subtitle');
?>
<section id="valores" class="section-valores">
    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="col-12 col-lg-4 mb-5 mb-lg-0">
                <div class="valores-intro">
                    <h2 class="section-title"><?php echo $v_title ? esc_html($v_title) : 'Valores Epysa'; ?></h2>
                    <div class="section-subtitle"><?php echo $v_subtitle ? $v_subtitle : '<p>Cada kilómetro...</p>'; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-8">
                <div class="swiper valores-swiper">
                    <div class="swiper-wrapper">
                        <?php if (have_rows('listado_valores')):
                            while (have_rows('listado_valores')):
                                the_row();
                                $name = get_sub_field('nombre');
                                $icon = get_sub_field('icono');
                                $c_start = get_sub_field('color_inicio') ?: '#E30613';
                                $c_end = get_sub_field('color_fin') ?: '#FAD1D3';
                                ?>
                                <div class="swiper-slide">
                                    <div class="flip-card" onclick="this.classList.toggle('flipped')"
                                        style="--card-color-start: <?php echo esc_attr($c_start); ?>; --card-color-end: <?php echo esc_attr($c_end); ?>;">
                                        <div class="flip-card-inner">
                                            <div class="flip-card-front">
                                                <div class="icon-wrapper">
                                                    <div class="icon-mask"
                                                        style="-webkit-mask-image: url(<?php echo esc_url($icon); ?>); mask-image: url(<?php echo esc_url($icon); ?>);">
                                                    </div>
                                                </div>
                                                <h3 class="valor-name"><?php echo esc_html($name); ?></h3>
                                                <?php if ($desc_short = get_sub_field('descripcion_corta')): ?>
                                                    <div class="valor-short-text"><?php echo nl2br(esc_html($desc_short)); ?></div>
                                                <?php endif; ?>
                                                <div class="flip-indicator"
                                                    style="color: var(--card-color-start); font-size:24px; font-weight:bold; margin-top:auto;">
                                                    +</div>
                                            </div>
                                            <div class="flip-card-back">
                                                <h3 class="valor-name-back"><?php echo esc_html($name); ?></h3>
                                                <div class="valor-desc"><?php echo get_sub_field('descripcion_larga'); ?></div>
                                                <div class="bg-icon"
                                                    style="-webkit-mask-image: url(<?php echo esc_url($icon); ?>); mask-image: url(<?php echo esc_url($icon); ?>);">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; endif; ?>
                    </div>
                </div>
                <div class="valores-nav-container mt-4 text-end">
                    <button
                        class="swiper-button-custom swiper-prev"><?php include get_template_directory() . '/assets/icons/nav-red-prev.svg'; ?></button>
                    <button
                        class="swiper-button-custom swiper-next"><?php include get_template_directory() . '/assets/icons/nav-red-next.svg'; ?></button>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new Swiper('.valores-swiper', { slidesPerView: 'auto', spaceBetween: 10, breakpoints: { 768: { spaceBetween: 20 } }, navigation: { nextEl: '.swiper-next', prevEl: '.swiper-prev', } });
    });
</script>