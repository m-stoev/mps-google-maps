<?php
/**
 * Plugin Name: Miroslav PS Google Maps Plugin
 * Plugin URI: https://github.com/m-stoev/mps-google-maps
 * Description: Plugin for Google maps and POIs.
 * Version: 0.1
 * Author: Miroslav Stoev
*/

defined('ABSPATH') || die('die');

add_action('plugins_loaded',	'mps_gm_init', 0);
add_action('admin_menu',		'mps_add_plugin_menu');
add_action('admin_init',		'mps_settings_init');

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
	
	$settings_group	= 'mps_gm';
	$settings_name	= 'mps_gm_settings';
	
	register_setting( $settings_group, $settings_name );
	
	add_shortcode('mps-google-map', 'mps_generate_shortcode');
}

function mps_add_plugin_menu() {
	add_menu_page(
		'MPS ' . mps_tr('Google Maps'), // title
		'MPS ' . mps_tr('Google Maps'), // title
		'manage_options', // capability of the user to see this page
		'mps_gm_settings', // the slug
		'mps_render_page', // the render function
		'dashicons-admin-site-alt' // menu icon
	);
}

function mps_settings_init() {
	
}

function mps_tr($string) {
	global $mp_lang_strings;
	
	if (!empty($mp_lang_strings[$string])) {
		return $mp_lang_strings[$string];
	}
	
	return $string;
}

function mps_render_page() {
	global $mp_ds;
	
	ob_start();
	//the_ID();
	
	require plugin_dir_path(__FILE__) . 'templates' . $mp_ds . 'options.php';
	echo ob_get_clean();
}

function mps_generate_shortcode() {
	global $mp_ds;
	
	ob_start();
	//the_ID();
	
	require plugin_dir_path(__FILE__) . 'templates' . $mp_ds . 'shortcode.php';
	return ob_get_clean();
	
}