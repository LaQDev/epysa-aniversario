<?php
/**
 * Sistema de Exportación de Datos (Historias y Votantes)
 * Ubicación: inc/exportador.php
 */

// Seguridad: Evitar acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Crear el submenú dentro del Custom Post Type "Historias"
add_action('admin_menu', 'epysa_exportador_menu');
function epysa_exportador_menu() {
    add_submenu_page(
        'edit.php?post_type=historia', // Cuelga del menú de Historias
        'Exportar Datos',              // Título de la página
        'Exportar Datos',              // Título del menú
        'manage_options',              // Capacidad requerida (Administrador)
        'epysa-exportador',            // Slug
        'epysa_exportador_html'        // Función que renderiza la vista
    );
}

// 2. Renderizar la vista del menú en el administrador
function epysa_exportador_html() {
    ?>
    <div class="wrap">
        <h1>Exportar Datos del Concurso</h1>
        <p>Selecciona el tipo de reporte que deseas descargar. Los archivos se generarán en formato CSV (puedes abrirlos directamente con Excel).</p>
        
        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <a href="<?php echo admin_url('admin.php?page=epysa-exportador&action=export_historias'); ?>" class="button button-primary button-large" style="background: #E30613; border-color: #E30613;">
                Descargar Base de Historias
            </a>
            <a href="<?php echo admin_url('admin.php?page=epysa-exportador&action=export_votantes'); ?>" class="button button-primary button-large">
                Descargar Base de Votantes
            </a>
        </div>
    </div>
    <?php
}

// 3. Lógica para interceptar la petición y descargar los archivos
add_action('admin_init', 'epysa_procesar_exportaciones');
function epysa_procesar_exportaciones() {
    // Validar que estamos en la página de exportación y hay una acción solicitada
    if (!isset($_GET['page']) || $_GET['page'] !== 'epysa-exportador' || !isset($_GET['action'])) {
        return;
    }

    // Seguridad: Solo administradores
    if (!current_user_can('manage_options')) {
        wp_die('No tienes permisos para realizar esta acción.');
    }

    $action = $_GET['action'];

    if ($action === 'export_historias') {
        epysa_generar_csv_historias();
    } elseif ($action === 'export_votantes') {
        epysa_generar_csv_votantes();
    }
}

// 4. Función para generar Excel de HISTORIAS
function epysa_generar_csv_historias() {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Base_Historias_Epysa_' . date('Y-m-d') . '.csv"');
    
    // Imprimir BOM para que Excel lea los tildes (UTF-8) correctamente
    echo "\xEF\xBB\xBF";
    $output = fopen('php://output', 'w');
    
    // Cabeceras del Excel
    fputcsv($output, array('ID', 'Titulo de la historia', 'Nombre y apellido del participante', 'Valor seleccionado', 'Fecha de publicacion', 'Votos recibidos'));

    // Paso A: Mapear todos los votos para contarlos eficientemente
    $todos_los_usuarios = get_users();
    $conteo_votos = array();
    
    foreach ($todos_los_usuarios as $user) {
        $votos_usuario = get_user_meta($user->ID, 'epysa_votos_usuario', true);
        if (is_array($votos_usuario)) {
            foreach ($votos_usuario as $id_historia_votada) {
                if (!isset($conteo_votos[$id_historia_votada])) {
                    $conteo_votos[$id_historia_votada] = 0;
                }
                $conteo_votos[$id_historia_votada]++;
            }
        }
    }

    // Paso B: Obtener todas las historias publicadas
    $args = array(
        'post_type'      => 'historia',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Traer todas
    );
    $historias = get_posts($args);

    // Paso C: Llenar el Excel
    foreach ($historias as $historia) {
        $id = $historia->ID;
        $titulo = $historia->post_title;
        
        $nombre = get_field('nombre', $id);
        $apellido = get_field('apellido', $id);
        $participante = $nombre . ' ' . $apellido;
        
        $valor_crudo = get_field('valor_epysa', $id);
        $valores_map = [
            'pasion'     => 'Pasión',
            'innovacion' => 'Innovación',
            'entereza'   => 'Entereza',
            'seguridad'  => 'Seguridad',
            'amistad'    => 'Amistad'
        ];
        $valor = isset($valores_map[strtolower($valor_crudo)]) ? $valores_map[strtolower($valor_crudo)] : ucfirst(strtolower($valor_crudo));
        
        $fecha = get_the_date('d/m/Y H:i', $id);
        
        // Cantidad de votos (del mapeo que hicimos en el Paso A)
        $votos_recibidos = isset($conteo_votos[$id]) ? $conteo_votos[$id] : 0;
        
        fputcsv($output, array($id, $titulo, $participante, $valor, $fecha, $votos_recibidos));
    }
    
    fclose($output);
    exit;
}

// 5. Función para generar Excel de VOTANTES
function epysa_generar_csv_votantes() {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Base_Votantes_Epysa_' . date('Y-m-d') . '.csv"');
    
    echo "\xEF\xBB\xBF"; // BOM para UTF-8
    $output = fopen('php://output', 'w');
    
    // Cabeceras del Excel
    fputcsv($output, array('ID', 'Nombre y apellido del votante', 'Correo electronico', 'Cantidad de votos emitidos'));

    $usuarios = get_users();
    
    foreach ($usuarios as $user) {
        $votos_usuario = get_user_meta($user->ID, 'epysa_votos_usuario', true);
        $cantidad_votos = is_array($votos_usuario) ? count($votos_usuario) : 0;
        
        // Solo incluimos en el Excel a los usuarios que hayan emitido al menos 1 voto
        if ($cantidad_votos > 0) {
            $nombre_completo = $user->first_name . ' ' . $user->last_name;
            
            // Si el usuario no llenó nombre/apellido al registrarse, usamos su display_name
            if (trim($nombre_completo) == '') {
                $nombre_completo = $user->display_name;
            }
            
            fputcsv($output, array($user->ID, trim($nombre_completo), $user->user_email, $cantidad_votos));
        }
    }
    
    fclose($output);
    exit;
}