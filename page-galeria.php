<?php
/* Template Name: Página Galería */

get_header();

// --- 1. RECUPERAR DATOS DEL BANNER ---
// (Los datos del Hero ahora se manejan dentro de template-parts/hero.php)
$show_banner = get_field('banner_mostrar');
$banner_icon = get_field('banner_icono');
$banner_text = get_field('banner_texto');
?>

<div class="page-galeria">

    <?php get_template_part('template-parts/hero'); ?>

    <?php if ($show_banner): ?>
        <section class="banner-info-section">
            <div class="container">
                <div class="banner-info-box">
                    <?php if ($banner_icon): ?>
                        <div class="banner-icon">
                            <img src="<?php echo esc_url($banner_icon['url']); ?>" alt="Icono">
                        </div>
                    <?php endif; ?>

                    <?php if ($banner_text): ?>
                        <div class="banner-text">
                            <?php echo $banner_text; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="filtros-section" id="filtros-anchor">
        <div class="container">
            <form id="filtros-form" class="filtros-wrapper">
                <div class="filtro-item search-box">
                    <label>Busca por colaborador</label>
                    <div class="input-wrapper">
                        <input type="text" name="busqueda" id="search-input" placeholder="Por nombre de colaborador">
                        <span class="search-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#E30613"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="filtro-item">
                    <label>Filtrar por valor</label>
                    <select name="valor" id="filtro-valor" class="form-select">
                        <option value="">Todos los Valores</option>
                        <option value="Entereza">Entereza</option>
                        <option value="Pasion">Pasión</option>
                        <option value="Innovacion">Innovación</option>
                        <option value="Seguridad">Seguridad</option>
                        <option value="Amistad">Amistad</option>
                    </select>
                </div>

                <div class="filtro-item">
                    <label>Ordenar por</label>
                    <select name="orden" id="filtro-orden" class="form-select">
                        <option value="desc">Más reciente</option>
                        <option value="asc">Más antigua</option>
                    </select>
                </div>
            </form>
        </div>
    </section>

    <section class="galeria-grid-section">
        <div class="container">
            <div class="row g-4" id="resultados-galeria">
                <div class="col-12 text-center py-5 loading-state">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="mt-2">Cargando historias...</p>
                </div>
            </div>

            <div class="text-center mt-5" id="load-more-container" style="display:none;">
                <button id="btn-cargar-mas" class="btn btn-outline-red">Cargar más</button>
            </div>
        </div>
    </section>

</div>

<?php get_footer(); ?>