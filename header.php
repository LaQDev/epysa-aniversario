<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?> | <?php is_front_page() ? bloginfo('description') : wp_title(''); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <?php
    // OBTENER LA ETAPA ACTUAL
    $etapa_actual = get_field('etapa_actual', 'option');
    if (!$etapa_actual)
        $etapa_actual = 1;
    $etapa_actual = intval($etapa_actual);
    ?>

    <header id="masthead" class="site-header">
        <div class="container">
            <div class="header-inner">

                <div class="site-branding">
                    <?php
                    if (has_custom_logo()) {
                        the_custom_logo();
                    } else {
                        echo '<h1 class="site-title"><a href="' . home_url('/') . '">' . get_bloginfo('name') . '</a></h1>';
                    }
                    ?>
                </div>

                <nav id="site-navigation" class="main-navigation">
                    <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                    </button>

                    <div class="menu-wrapper">

                        <?php if ($etapa_actual === 2): ?>
                            <?php
                            wp_nav_menu(
                                array(
                                    'theme_location' => 'menu-principal',
                                    'menu_id' => 'primary-menu',
                                    'container' => false,
                                )
                            );
                            ?>
                        <?php endif; ?>

                        <div class="header-cta">

                            <?php if ($etapa_actual === 1): ?>
                                <?php elseif ($etapa_actual === 2): ?>

                                <?php if (is_user_logged_in()): ?>
                                    <?php
                                    $current_user = wp_get_current_user();
                                    $display_name = !empty($current_user->first_name) ? $current_user->first_name . ' ' . $current_user->last_name : 'Usuario';
                                    ?>
                                    <div class="user-dropdown-wrapper">
                                        <button class="btn btn-inverse btn-user-logged" id="userDropdownTrigger">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/icon-user-black.svg"
                                                alt="" class="icon-btn">
                                            Hola, <?php echo esc_html($display_name); ?>
                                        </button>

                                        <div class="user-dropdown-menu">
                                            <a href="<?php echo home_url('/mi-perfil'); ?>" class="link-profile">
                                                Ver mis votos
                                            </a>
                                            <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn-logout">
                                                <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/icon-logout-white.svg"
                                                    alt="">
                                                Cerrar sesión
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <button class="btn btn-inverse btn-login-trigger" onclick="abrirModalLogin()">
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/icon-login-black.svg"
                                            alt="" class="icon-btn">
                                        Ingresar
                                    </button>
                                <?php endif; ?>

                            <?php elseif ($etapa_actual === 3): ?>
                                <a href="<?php echo home_url('/galeria-de-historias'); ?>" class="btn btn-inverse">Ver las
                                    historias</a>
                            <?php endif; ?>

                        </div>
                    </div>
                </nav>

            </div>
        </div>
    </header>

    