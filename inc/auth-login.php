<?php
/**
 * Lógica de Autenticación Magic Link y Registro
 * Ubicación: inc/auth-login.php
 */

if (!defined('ABSPATH'))
    exit;

/* --------------------------------------------------------------
   1. AJAX: SOLICITAR MAGIC LINK
-------------------------------------------------------------- */
add_action('wp_ajax_nopriv_epysa_solicitar_login', 'epysa_solicitar_login');
add_action('wp_ajax_epysa_solicitar_login', 'epysa_solicitar_login');

function epysa_solicitar_login()
{
    // Verificar seguridad básica
    check_ajax_referer('epysa_auth_nonce', 'nonce');

    $email = sanitize_email($_POST['email']);

    // 1. Validar Email
    if (!is_email($email)) {
        wp_send_json_error(['message' => 'El correo electrónico no es válido.']);
    }

    // 2. Validar Dominio (@epysa.cl o @laq.cl)
    $parts = explode('@', $email);
    $domain = array_pop($parts);
    $allowed_domains = ['epysa.cl', 'laq.cl'];

    if (!in_array($domain, $allowed_domains)) {
        wp_send_json_error(['message' => 'Debes usar un correo institucional (@epysa.cl).']);
    }

    // 3. Buscar o Crear Usuario
    $user = get_user_by('email', $email);

    if (!$user) {
        $password = wp_generate_password(20, false);
        $user_id = wp_create_user($email, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'Error al crear usuario. Intenta más tarde.']);
        }

        $user = get_user_by('id', $user_id);
        $user->set_role('subscriber');
    }

    // 4. Generar Token Seguro
    $token = bin2hex(random_bytes(32));
    $expiry = time() + (15 * 60); // 15 minutos

    update_user_meta($user->ID, 'epysa_login_token', $token);
    update_user_meta($user->ID, 'epysa_token_expiry', $expiry);

    // 5. Enviar Correo HTML
    $login_link = home_url('/?epysa_auth_token=' . $token . '&uid=' . $user->ID);

    $subject = '¡Estás a un paso de elegir a los ganadores! - Epysa 50 Años';

    // --- INICIO PLANTILLA HTML ---
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso Epysa</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            /* Reset y Fuentes */
            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

            body {
                margin: 0;
                padding: 0;
                background-color: #E5E0D2;
                font-family: 'Poppins', Verdana, sans-serif;
            }

            table {
                border-spacing: 0;
            }

            td {
                padding: 0;
            }

            img {
                border: 0;
            }

            /* Colores */
            .wrapper {
                width: 100%;
                table-layout: fixed;
                background-color: #E5E0D2;
                padding-bottom: 40px;
            }

            .main {
                background-color: #ffffff;
                margin: 0 auto;
                width: 100%;
                max-width: 600px;
                border-spacing: 0;
                font-family: 'Poppins', Verdana, sans-serif;
                color: #171717;
            }

            /* Botón */
            .btn-primary {
                background-color: #E30613;
                color: #ffffff;
                text-decoration: none;
                padding: 15px 40px;
                border-radius: 50px;
                font-weight: bold;
                display: inline-block;
                mso-padding-alt: 0;
                text-align: center;
                font-size: 16px;
            }
        </style>
    </head>

    <body style="background-color: #E5E0D2; font-family: 'Poppins', Verdana, sans-serif;">

        <center class="wrapper" style="background-color: #E5E0D2;">
            <table class="main" width="100%"
                style="background-color: #ffffff; font-family: 'Poppins', Verdana, sans-serif;">

                <tr>
                    <td style="background-color: #E30613; padding: 25px; text-align: center;">
                        <img src="https://epysabuses.cl/wp-content/uploads/2026/02/logo-50anos-email.png"
                            alt="Epysa 50 Años" width="160" style="display: block; margin: 0 auto;">
                    </td>
                </tr>

                <tr>
                    <td style="padding: 40px 30px; text-align: left;">

                        <h1
                            style="color: #111111; font-family: 'Poppins', Verdana, sans-serif; font-size: 24px; font-weight: 700; margin: 0 0 20px 0; line-height: 1.2; text-align: left;">
                            ¡Estás a un paso de elegir a los ganadores!
                        </h1>

                        <p
                            style="font-family: 'Poppins', Verdana, sans-serif; font-size: 16px; line-height: 1.6; color: #333333; margin: 0 0 30px 0; text-align: left;">
                            Recibimos tu solicitud de acceso. Para garantizar que eres parte del equipo Epysa, confirma tu
                            correo haciendo clic en el botón de abajo.
                        </p>

                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td align="center" style="padding-bottom: 30px;">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="center" style="border-radius: 50px;" bgcolor="#E30613">
                                                <a href="<?php echo esc_url($login_link); ?>" target="_blank"
                                                    style="font-size: 16px; font-family: 'Poppins', Verdana, sans-serif; color: #ffffff; text-decoration: none; padding: 14px 40px; border-radius: 50px; border: 1px solid #E30613; display: inline-block; font-weight: 700;">
                                                    Ingresar y votar
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p
                            style="font-family: 'Poppins', Verdana, sans-serif; font-size: 14px; color: #666666; margin: 0 0 10px 0; text-align: left;">
                            ¿El botón no funciona? Copia y pega este enlace en tu navegador:
                        </p>
                        <p
                            style="font-family: 'Poppins', Verdana, sans-serif; font-size: 12px; color: #999999; margin: 0; word-break: break-all; text-align: left;">
                            <a href="<?php echo esc_url($login_link); ?>"
                                style="color: #999999; text-decoration: underline;">
                                <?php echo esc_url($login_link); ?>
                            </a>
                        </p>

                    </td>
                </tr>

                <tr>
                    <td style="background-color: #525252; padding: 40px 30px; text-align: left;">

                        <img src="https://epysabuses.cl/wp-content/uploads/2026/02/logo-blanco-email.png" alt="Epysa"
                            width="120" style="display: block; margin: 0 0 25px 0;">

                        <p
                            style="font-family: 'Poppins', Verdana, sans-serif; font-size: 12px; color: #999999; margin: 0; text-align: left;">
                            <a href="<?php echo esc_url(home_url('/terminos-y-condiciones')); ?>" target="_blank"
                                style="color: #ffffff; text-decoration: none;">Términos y Condiciones</a>
                        </p>

                    </td>
                </tr>

            </table>
        </center>
    </body>

    </html>
    <?php
    $message = ob_get_clean();
    // --- FIN PLANTILLA HTML ---

    // Configurar headers para HTML
    $headers = array('Content-Type: text/html; charset=UTF-8');

    $sent = wp_mail($email, $subject, $message, $headers);

    if ($sent) {
        wp_send_json_success(['message' => 'Enlace enviado']);
    } else {
        wp_send_json_error(['message' => 'Error al enviar el correo. Revisa tu servidor.']);
    }
}

/* --------------------------------------------------------------
   2. HOOK: PROCESAR EL LINK AL ENTRAR AL SITIO
-------------------------------------------------------------- */
add_action('init', 'epysa_procesar_magic_link');

function epysa_procesar_magic_link()
{
    if (!isset($_GET['epysa_auth_token']) || !isset($_GET['uid']))
        return;

    $token = sanitize_text_field($_GET['epysa_auth_token']);
    $user_id = intval($_GET['uid']);

    $saved_token = get_user_meta($user_id, 'epysa_login_token', true);
    $expiry = get_user_meta($user_id, 'epysa_token_expiry', true);

    if (!$saved_token || $saved_token !== $token) {
        wp_redirect(home_url('/?auth_error=invalid_token'));
        exit;
    }

    if (time() > $expiry) {
        wp_redirect(home_url('/?auth_error=expired'));
        exit;
    }

    // Login Exitoso
    wp_set_auth_cookie($user_id);

    // Eliminar token (un solo uso)
    delete_user_meta($user_id, 'epysa_login_token');
    delete_user_meta($user_id, 'epysa_token_expiry');

    // Redirección Inteligente
    $user = get_user_by('id', $user_id);
    if (empty($user->first_name)) {
        wp_redirect(home_url('/?auth_action=complete_profile'));
    } else {
        wp_redirect(home_url('/?auth_action=login_success'));
    }
    exit;
}

/* --------------------------------------------------------------
   3. AJAX: GUARDAR PERFIL (NOMBRE)
-------------------------------------------------------------- */
add_action('wp_ajax_epysa_guardar_nombre', 'epysa_guardar_nombre');

function epysa_guardar_nombre()
{
    check_ajax_referer('epysa_auth_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Sesión no iniciada.']);
    }

    $nombre = sanitize_text_field($_POST['nombre']);
    $apellido = sanitize_text_field($_POST['apellido']);

    if (empty($nombre) || empty($apellido)) {
        wp_send_json_error(['message' => 'Nombre y apellido son obligatorios.']);
    }

    $user_id = get_current_user_id();

    $updated = wp_update_user([
        'ID' => $user_id,
        'first_name' => $nombre,
        'last_name' => $apellido,
        'display_name' => $nombre . ' ' . $apellido
    ]);

    if (is_wp_error($updated)) {
        wp_send_json_error(['message' => 'Error al guardar.']);
    } else {
        wp_send_json_success(['message' => 'Perfil actualizado']);
    }
}