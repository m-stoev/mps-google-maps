<?php
/**
 * Plugin Name: Miroslav PS Google Maps Plugin
 * Plugin URI: https://github.com/m-stoev/mps-google-maps
 * Description: Plugin for Google maps and POIs.
 * Version: 1.5
 * Author: Miroslav Stoev
 * Text Domain: mps-google-maps
 * Domain Path: /langs
*/

defined('ABSPATH') || die('die');

add_action('plugins_loaded',		'mps_gm_init', 0);
add_action('admin_menu',			'mps_gm_add_plugin_menu');
add_action('wp_enqueue_scripts',	'mps_gm_load_scripts');

function mps_gm_init() {
	load_plugin_textdomain( 'mps-google-maps', FALSE, basename( dirname( __FILE__ ) )
		. DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR );
	add_shortcode('mps-google-map', 'mps_gm_generate_shortcode');
	add_action( 'the_post', 'mps_gm_open_post' );
}

function mps_gm_load_scripts() {
	if(is_page()) {
		global $post;
		
		if(strpos($post->post_content, '[mps-google-map]') !== false) {
			wp_enqueue_script('google_maps_api', '//maps.googleapis.com/maps/api/js?key='
				. get_option('google_api_key', '') .'&sensor=false&libraries=geometry&language='
				. substr( get_bloginfo ( 'language' ), 0, 2 ));

			wp_enqueue_script('mps-maplabel', plugin_dir_url(__FILE__) . 'js/google-maps/maplabel-compiled.min.js');
			wp_enqueue_script('mps-makercluster',  plugin_dir_url(__FILE__) . 'js/google-maps/markerclusterer.min.js');
		}
	}
}

function mps_gm_open_post($post) {
	if(is_admin() or is_home()) {
		return;
	}
	
	$current_page = urldecode(add_query_arg( array() )); // the url
	
	if(strpos($current_page, __('Gallery', 'mps-google-maps')) !== false) {
		// get fields names
		$lat_field_name		= get_option('lat_met_field', false);
		$lng_field_name		= get_option('lng_met_field', false);
		$title_link			= get_option('title_link_met_field', false);

		if(!$lat_field_name or !$lng_field_name or !$title_link) {
			return;
		}

		$lat		= current(get_post_meta($post->ID, $lat_field_name, []));
		$lng		= current(get_post_meta($post->ID, $lng_field_name, []));

		if(empty($lat) or empty($lng)) {
			return;
		}

		$g_map_url = 'https://www.google.com/maps/place/';

		$post->post_title = '<a href="' . esc_url($g_map_url . $lat . ',' . $lng)
			. '" rel="nofollow" target="_blank">' . $post->post_title . '</a>';
	}
}

function mps_gm_add_plugin_menu() {
	add_menu_page(
		__('Google Map', 'mps-google-maps'), // title
		__('Google Map', 'mps-google-maps'), // title
		'manage_options', // capability of the user to see this page
		'mps_gm_settings', // the slug
		'mps_gm_render_page', // the render function
		'dashicons-admin-site-alt' // menu icon
	);
	
	add_action('admin_init', 'mps_gm_settings_init');
}

function mps_gm_settings_init() {
	$fields = [
		[
			'field' => 'google_api_key',
			'label'	=> __('Google Api Key', 'mps-google-maps'),
			'type'	=> 'text',
			'style'	=> 'width: 400px;',
		],
		[
			'field' => 'lat_met_field',
			'label'	=> __('Latitude Meta field name', 'mps-google-maps'),
			'type'	=> 'text',
		],
		[
			'field' => 'lng_met_field',
			'label'	=> __('Longtitude Meta field name', 'mps-google-maps'),
			'type'	=> 'text',
		],
		[
			'field' => 'icon_met_field',
			'label'	=> __('Icon Meta field name', 'mps-google-maps'),
			'type'	=> 'text',
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
			'mps_gm_settings_cb', // callback
			'mps_gm_settings', // page
			'mps-settings-section', // section
			$field
		);
	}
}

function mps_gm_settings_cb($args) {
	$field_name		= esc_attr($args['field']);
	$field_val		= get_option($field_name, '');
	$field_type		= esc_attr($args['type']);
	$field_style	= esc_attr(@$args['style']);
	
	echo
		'<div id="titlediv">
			<input id="mps_' . $field_name . '" type="'. $field_type .'" name="' . $field_name . '" value="' . $field_val . '" style="'. ($field_style ? $field_style : '') .'" />
		</div>';
}

// render options page
function mps_gm_render_page() {
	if (!current_user_can('manage_options')) {
        return;
    }
	
	// get available icons names
	$names = [];
	
	$icons_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . 'map' . DIRECTORY_SEPARATOR;
	$files = scandir($icons_dir, SCANDIR_SORT_ASCENDING);
	
	foreach($files as $file) {
		if(!in_array($file, ['.', '..', '.htaccess'])) {
			$names[] = basename($icons_dir . $file, '.png');
		}
	}
	
	$list = implode(', ', $names);
	// get available icons names END
	
	ob_start();
	
	require plugin_dir_path(__FILE__) . 'templates' . DIRECTORY_SEPARATOR . 'options.php';
	echo ob_get_clean();
}

function mps_gm_generate_shortcode() {
    $plugin_url = plugin_dir_url( __FILE__ );
    
    // get fields names
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
			'lang'			=> substr(get_locale(), 0, 2),
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
	
	require plugin_dir_path(__FILE__) . 'templates' . DIRECTORY_SEPARATOR . 'shortcode.php';
	return ob_get_clean();
}
