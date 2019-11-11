<?php
/**
 * Plugin Name: Miroslav PS Google Maps Plugin
 * Plugin URI: https://github.com/m-stoev/mps-google-maps
 * Description: Plugin for Google maps and POIs.
 * Version: 1.1
 * Author: Miroslav Stoev
*/

defined('ABSPATH') || die('die');

add_action('plugins_loaded',	'mps_gm_init', 0);
add_action('admin_menu',		'mps_add_plugin_menu');

$mp_lang_strings	= [];
$mp_ds				= DIRECTORY_SEPARATOR;
$mps_locale;

function mps_gm_init() {
	global $mp_lang_strings, $mps_locale, $mp_ds;
	
	$mps_locale = get_locale();
	
	if ('bg_BG' == $mps_locale) {
		$mp_lang_strings = json_decode(
			file_get_contents(plugin_dir_path(__FILE__) . 'langs' . $mp_ds . $mps_locale . '.json'),
			true
		);
	}
	
	add_shortcode('mps-google-map', 'mps_generate_shortcode');
	add_action( 'template_redirect', 'mps_inspect_page_id' );
}

function mps_inspect_page_id() {
	if(is_admin()) {
		return;
	}
	
	global $mp_lang_strings;
	
	$current_page = add_query_arg( array() ); // the url
	
	if(strpos(urldecode($current_page), mb_strtolower($mp_lang_strings['Gallery']) === false)) {
		return;
	}
	
	// get fields names
    $lat_field_name		= get_option('lat_met_field', false);
    $lng_field_name		= get_option('lng_met_field', false);
	
	if(!$lat_field_name or !$lng_field_name) {
		return;
	}
	
	$page_id	= get_queried_object_id();
	$lat		= current(get_post_meta($page_id, $lat_field_name, []));
	$lng		= current(get_post_meta($page_id, $lng_field_name, []));
	
	if(empty($lat) or empty($lng)) {
		return;
	}
	
	var_dump($lng);
}

function mps_add_plugin_menu() {
	add_menu_page(
		'MPS ' . mps_tr('Google Map'), // title
		'MPS ' . mps_tr('Google Map'), // title
		'manage_options', // capability of the user to see this page
		'mps_gm_settings', // the slug
		'mps_render_page', // the render function
		'dashicons-admin-site-alt' // menu icon
	);
	
	add_action('admin_init', 'mps_settings_init');
}

function mps_settings_init() {
	global $mp_lang_strings, $mp_ds;
	
	$fields = [
		[
			'field' => 'google_api_key',
			'label'	=> mps_tr('Google Api Key'),
			'type'	=> 'text',
		],
		[
			'field' => 'lat_met_field',
			'label'	=> mps_tr('Latitude Meta field name'),
			'type'	=> 'text',
		],
		[
			'field' => 'lng_met_field',
			'label'	=> mps_tr('Longtitude Meta field name'),
			'type'	=> 'text',
		],
		[
			'field' => 'icon_met_field',
			'label'	=> mps_tr('Icon Meta field name'),
			'type'	=> 'text',
		],
		[
			'field'	=> 'title_link_met_field',
			'label'	=> mps_tr('Map link in the Title'),
			'type'	=> 'text',
			'style'	=> 'width: 400px;'
		],
	];
	
	add_settings_section(
		'mps-settings-section',
		'', // title to be displayed
		'', // callback function to be called when opening section, currently empty
		'mps_gm_settings' // page
	);
	
	foreach($fields as $field) {
		// register google_api_key option
		register_setting('mps_gm_settings', $field['field']);

		// add google_api_key option
		add_settings_field(
			$field['field'], // field name
			$field['label'], // lable text
			'mps_settings_cb', // callback
			'mps_gm_settings', // page
			'mps-settings-section', // section
			$field
		);
	}
}

function mps_settings_cb($args) {
	$field_name		= esc_attr($args['field']);
	$field_val		= get_option($field_name, '');
	$field_type		= esc_attr($args['type']);
	$field_style	= esc_attr(@$args['style']);
	
	echo
		'<div id="titlediv">
			<input id="mps_' . $field_name . '" type="'. $field_type .'" name="' . $field_name . '" value="' . $field_val . '" style="'. ($field_style ? $field_style : '') .'" />
		</div>';
}

function mps_tr($string) {
	global $mp_lang_strings;
	
	if (!empty($mp_lang_strings[$string])) {
		return $mp_lang_strings[$string];
	}
	
	return $string;
}

// render options page
function mps_render_page() {
	if (!current_user_can('manage_options')) {
        return;
    }
	
	global $mp_ds;
	
	// get available icons names
	$names = [];
	
	$icons_dir = dirname(__FILE__) . $mp_ds . 'icons' . $mp_ds . 'map' . $mp_ds;
	$files = scandir($icons_dir, SCANDIR_SORT_ASCENDING);
	
	foreach($files as $file) {
		if(!in_array($file, ['.', '..', '.htaccess'])) {
			$names[] = basename($icons_dir . $file, '.png');
		}
	}
	
	$list = implode(', ', $names);
	// get available icons names END
	
	ob_start();
	
	require plugin_dir_path(__FILE__) . 'templates' . $mp_ds . 'options.php';
	echo ob_get_clean();
}

function mps_generate_shortcode() {
	global $mp_ds, $mps_locale;
    
    $plugin_url = plugin_dir_url( __FILE__ );
    
    // get fields names
    $google_api_key = get_option('google_api_key', '');
    $lat            = get_option('lat_met_field', false);
    $lng            = get_option('lng_met_field', false);
    $icon           = get_option('icon_met_field', false);
    
    if($lat and $lng and $icon) {
        // get coordinates and icons
        $pois       = [];
        $pois_json  = '';

        $args = [
            'post_type'		=> 'post',
			'meta_key'		=> 'icon',
			'meta_value'	=> array(''),
			'meta_compare'	=> 'NOT IN',
			'lang'			=> substr($mps_locale, 0, 2),
			'fields'		=> 'ids',
        ];

        $posts = new WP_Query( $args );

        if($posts) {
            foreach($posts->posts as $id) {
                $pois[$id] = [
                    'lat' => current(get_post_meta($id, $lat)),
                    'lng' => current(get_post_meta($id, $lng)),
                    'icon' => current(get_post_meta($id, $icon)),
                    'link' => urldecode(current(get_post_meta($id, 'custom_permalink'))),
                    'title' => get_the_title($id)
                ];
            }

            $pois_json = json_encode($pois);
        }
        // get coordinates and icons END
    }
	
	ob_start();
	
	require plugin_dir_path(__FILE__) . 'templates' . $mp_ds . 'shortcode.php';
	return ob_get_clean();
}