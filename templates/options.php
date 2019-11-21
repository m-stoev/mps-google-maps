<?php defined('ABSPATH') || die('die'); ?>

<div class="wrap">
	<?php settings_errors(); ?>
	
	<h1><?= __('Map Settings', 'mps-google-maps'); ?></h1>
	
	<form method="post" action="options.php">
		<?php settings_fields('mps_gm_settings'); ?>
		<?php do_settings_sections('mps_gm_settings'); ?>
		
		<?php submit_button(); ?>
	</form>
	
	<p><b><?= __('List of icons names', 'mps-google-maps') . ':</b> ' . $list; ?></p>
	<p><b><?= __('Your page short code is', 'mps-google-maps'); ?>:</b> [mps-google-map]</p>
</div>