<?php
/* Template Name: Página Participar */

$mensaje = '';
$tipo_mensaje = '';

// --- LÓGICA DE PROCESAMIENTO (Backend Actualizado) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'enviar_historia') {
    // ... (El bloque PHP de procesamiento se mantiene idéntico al que enviaste) ...
    if (!isset($_POST['historia_nonce']) || !wp_verify_nonce($_POST['historia_nonce'], 'guardar_historia_action')) {
        $mensaje = 'Error de seguridad. Por favor intenta nuevamente.';
        $tipo_mensaje = 'danger';
    } else {
        $nombre = sanitize_text_field($_POST['nombre']);
        $apellido = sanitize_text_field($_POST['apellido']);
        $empresa = sanitize_text_field($_POST['empresa']);
        $region = sanitize_text_field($_POST['region']);
        $anos = sanitize_text_field($_POST['anos']);
        $valor = sanitize_text_field($_POST['valor']);
        $titulo = sanitize_text_field($_POST['titulo']);
        $relato = sanitize_textarea_field($_POST['relato']);

        $new_post = array(
            'post_title' => $titulo,
            'post_content' => $relato,
            'post_status' => 'draft',
            'post_type' => 'historia'
        );

        $pid = wp_insert_post($new_post);

        if ($pid) {
            update_field('nombre', $nombre, $pid);
            update_field('apellido', $apellido, $pid);
            update_field('empresa', $empresa, $pid);
            update_field('region', $region, $pid);
            update_field('anos_epysa', $anos, $pid);
            update_field('valor_epysa', $valor, $pid);

            if (!empty($_FILES['materiales']['name'][0])) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                $files = $_FILES['materiales'];
                $count = count($files['name']);
                $acf_repeater_data = array();

                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] !== UPLOAD_ERR_OK)
                        continue;

                    $_FILES['single_file'] = array(
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    );

                    $attach_id = media_handle_upload('single_file', $pid);

                    if (!is_wp_error($attach_id)) {
                        $acf_repeater_data[] = array('archivo_subido' => $attach_id);
                        if ($i === 0)
                            set_post_thumbnail($pid, $attach_id);
                    }
                }

                if (!empty($acf_repeater_data)) {
                    update_field('archivos_historia', $acf_repeater_data, $pid);
                }
            }
            wp_redirect(home_url('/confirmacion'));
            exit;
        } else {
            $mensaje = 'Hubo un error al guardar tu historia.';
            $tipo_mensaje = 'danger';
        }
    }
}

// Datos ACF Premios
$premio_icon = get_field('premio_icono');
$premio_title = get_field('premio_titulo');
$premio_desc = get_field('premio_bajada');
$premio_img = get_field('premio_imagen');

get_header();
?>

<div class="page-participa">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="form-container">

                    <div class="form-header text-start mb-5">
                        <h1 class="page-title">¡Agrega tus kilómetros a la historia!</h1>
                        <p class="form-instruction">Completa los datos. Los campos con asterisco (<span
                                class="asterisk">*</span>) son obligatorios.</p>
                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje; ?> mb-4"><?php echo $mensaje; ?></div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="epysa-form" id="participa-form" novalidate>
                        <input type="hidden" name="action" value="enviar_historia">
                        <?php wp_nonce_field('guardar_historia_action', 'historia_nonce'); ?>

                        <div class="form-grid">
                            <div class="form-group half-width">
                                <label class="form-label">Nombre <span class="asterisk">*</span></label>
                                <input type="text" name="nombre" class="form-control" placeholder="Juan" required>
                                <div class="form-hint">Debes ingresar tu nombre.</div>
                            </div>
                            <div class="form-group half-width">
                                <label class="form-label">Apellido <span class="asterisk">*</span></label>
                                <input type="text" name="apellido" class="form-control" placeholder="Pérez" required>
                                <div class="form-hint">Debes ingresar tu apellido.</div>
                            </div>

                            <div class="form-group half-width">
                                <label class="form-label">Empresa <span class="asterisk">*</span></label>
                                <select name="empresa" class="form-select" required>
                                    <option value="" disabled selected>Selecciona tu empresa</option>
                                    <option value="Epysa Buses">Epysa Buses</option>
                                    <option value="Epysa Equipos">Epysa Equipos</option>
                                    <option value="Mundo Buses">Mundo Buses</option>
                                    <option value="Fitrans">Fitrans</option>
                                    <option value="Alianza Inmobiliaria">Alianza Inmobiliaria</option>
                                </select>
                                <div class="form-hint">Selecciona tu empresa.</div>
                            </div>
                            <div class="form-group half-width">
                                <label class="form-label">Región <span class="asterisk">*</span></label>
                                <select name="region" class="form-select" required>
                                    <option value="" disabled selected>Selecciona una región</option>
                                    <option value="Arica y Parinacota">Arica y Parinacota</option>
                                    <option value="Tarapacá">Tarapacá</option>
                                    <option value="Antofagasta">Antofagasta</option>
                                    <option value="Atacama">Atacama</option>
                                    <option value="Coquimbo">Coquimbo</option>
                                    <option value="Valparaíso">Valparaíso</option>
                                    <option value="Metropolitana de Santiago">Metropolitana de Santiago</option>
                                    <option value="Libertador General Bernardo O'Higgins">Libertador General Bernardo
                                        O'Higgins</option>
                                    <option value="Maule">Maule</option>
                                    <option value="Ñuble">Ñuble</option>
                                    <option value="Biobío">Biobío</option>
                                    <option value="La Araucanía">La Araucanía</option>
                                    <option value="Los Ríos">Los Ríos</option>
                                    <option value="Los Lagos">Los Lagos</option>
                                    <option value="Aysén del General Carlos Ibáñez del Campo">Aysén del General Carlos
                                        Ibáñez del Campo</option>
                                    <option value="Magallanes y de la Antártica Chilena">Magallanes y de la Antártica
                                        Chilena</option>
                                </select>
                                <div class="form-hint">Selecciona tu región.</div>
                            </div>

                            <div class="form-group full-width">
                                <label class="form-label">Años en Epysa <span class="asterisk">*</span></label>
                                <input type="number" name="anos" class="form-control" placeholder="Ej: 15" required>
                                <div class="form-hint">Ingresa tus años de antigüedad.</div>
                            </div>

                            <div class="form-group full-width" id="group-valor">
                                <label class="form-label">Selecciona el Valor de tu historia <span
                                        class="asterisk">*</span></label>
                                <div class="valores-pills">
                                    <?php
                                    $valores = ['Entereza', 'Pasión', 'Innovación', 'Seguridad', 'Amistad'];
                                    $icon_map = [
                                        'pasion' => '/assets/icons/icon-pasion.svg',
                                        'amistad' => '/assets/icons/icon-amistad.svg',
                                        'entereza' => '/assets/icons/icon-entereza.svg',
                                        'innovacion' => '/assets/icons/icon-innovacion.svg',
                                        'seguridad' => '/assets/icons/icon-seguridad.svg',
                                    ];
                                    foreach ($valores as $val):
                                        $slug = sanitize_title($val);
                                        $icon_path = get_template_directory() . ($icon_map[$slug] ?? '/assets/icons/icon-valor-default.svg');
                                        ?>
                                        <input type="radio" class="btn-check" name="valor" id="val-<?php echo $slug; ?>"
                                            value="<?php echo $val; ?>" required>
                                        <label class="btn btn-pill-valor val-<?php echo $slug; ?>"
                                            for="val-<?php echo $slug; ?>">
                                            <span class="pill-icon">
                                                <?php if (file_exists($icon_path))
                                                    echo file_get_contents($icon_path); ?>
                                            </span>
                                            <?php echo $val; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-hint">Debes elegir un valor.</div>
                            </div>

                            <div class="form-group full-width">
                                <label class="form-label">Título de tu historia <span class="asterisk">*</span></label>
                                <input type="text" name="titulo" class="form-control" required>
                                <div class="form-hint">Ponle un título a tu historia.</div>
                            </div>

                            <div class="form-group full-width mb-0">
                                <label class="form-label">Tu Relato <span class="asterisk">*</span></label>
                                <textarea name="relato" id="relato-text" class="form-control" rows="6" maxlength="1500"
                                    placeholder="Cuéntanos ese momento que te marcó..." required></textarea>
                                <div class="form-hint">Debes escribir tu historia.</div>
                            </div>
                            <div class="form-group full-width mt-0">
                                <div class="char-count text-end"><span id="current-chars">0</span>/1500 caracteres
                                </div>
                            </div>

                            <div class="form-group full-width" id="group-materiales">
                                <label class="form-label">Sube tu material audiovisual <span
                                        class="asterisk">*</span></label>
                                <div class="file-upload-box multiple-upload" id="drop-zone">
                                    <input type="file" name="materiales[]" id="materiales-input"
                                        accept="image/*,video/*" multiple required>
                                    <div class="preview-gallery" id="preview-gallery"></div>
                                    <div class="upload-ui-content" id="upload-ui-content">
                                        <p class="mb-3 d-none d-md-block"><strong>Arrastra y suelta tus archivos
                                                aquí</strong></p>
                                        <span class="btn-fake-black">Buscar en mi dispositivo</span>
                                        <div class="file-meta mt-2">Formatos aceptados: JPG, PNG o MP4. (Máx. 3
                                            archivos)</div>
                                    </div>
                                </div>
                                <div class="form-hint">Debes subir al menos un archivo.</div>
                            </div>

                            <div class="form-group full-width">
                                <div class="custom-check-wrapper">
                                    <input class="custom-check-input" type="checkbox" id="legal-check"
                                        name="legal_check" required>
                                    <label class="custom-check-label" for="legal-check">
                                        <strong><a href="<?php echo home_url('/terminos-y-condiciones'); ?>"
                                                target="_blank">Acepto los Términos y Condiciones.</a></strong> Declaro
                                        conocer las bases y autorizo a Epysa a publicar mi nombre, historia y
                                        material
                                        audiovisual en el sitio web de la campaña y sus canales de difusión
                                    </label>
                                </div>
                                <div class="form-hint">Debes aceptar los términos.</div>
                            </div>

                            <div class="form-group full-width">
                                <div class="participa-prize-card">
                                    <div class="prize-info">
                                        <?php if ($premio_icon): ?>
                                            <div class="prize-icon"><img
                                                    src="<?php echo esc_url($premio_icon['url'] ?? $premio_icon); ?>"
                                                    alt=""></div><?php endif; ?>
                                        <h3 class="prize-title">
                                            <?php echo $premio_title ? esc_html($premio_title) : 'Premios'; ?>
                                        </h3>
                                        <div class="prize-desc"><?php echo $premio_desc; ?></div>
                                    </div>
                                    <?php if ($premio_img): ?>
                                        <div class="prize-image-wrapper"><img
                                                src="<?php echo esc_url($premio_img['url'] ?? $premio_img); ?>" alt="">
                                        </div><?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group full-width mt-4 mb-5">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Enviar mi historia</button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>