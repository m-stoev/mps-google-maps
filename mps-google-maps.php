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
}

function mps_add_plugin_menu() {
	add_menu_page(
		'MPS ' . mps_tr('Google Maps'),
		'MPS ' . mps_tr('Google Maps'),
		'manage_options',
		'mps-google-maps', // the slug
		'mps_render_page',
		'dashicons-admin-site-alt'
	);
}

function mps_tr($string) {
	global $mp_lang_strings;
	
	if (!empty($mp_lang_strings[$string])) {
		return $mp_lang_strings[$string];
	}
	
	return $string;
}

function mps_render_page() {
	
}