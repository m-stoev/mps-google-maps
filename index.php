<?php
/*
Plugin Name: Miroslav PS Google Maps Plugin
Plugin URI: https://github.com/m-stoev/mps-google-maps
Description: Plugin for Google maps and POIs.
Version: 0.1
Author: Miroslav Stoev
*/

defined('ABSPATH') || die('die');

add_action('plugins_loaded',	'mps_gm_init', 0);
add_action('admin_menu',		'mps_add_plugin_menu');

function mps_gm_init() {
	
}

function mps_add_plugin_menu() {
	add_menu_page(
		'MPS Google Maps',
		'MPS Google Maps',
		'manage_options',
		__FILE__,
		'mps_render_page',
		'dashicons-admin-site-alt'
	);
}

function mps_render_page() {
	
}