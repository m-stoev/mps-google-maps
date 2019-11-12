<?php defined('ABSPATH') || die('die'); ?>

<div class="wrap">
	<?php settings_errors(); ?>
	
	<h1><?= mps_gm_tr('Map Settings'); ?></h1>
	
	<form method="post" action="options.php">
		<?php settings_fields('mps_gm_settings'); ?>
		<?php do_settings_sections('mps_gm_settings'); ?>
		
		<?php submit_button(); ?>
	</form>
	
	<p><b><?= mps_gm_tr('List of icons names') . ':</b> ' . $list; ?></p>
	<p><b><?= mps_gm_tr('Your page short code is'); ?>:</b> [mps-google-map]</p>
</div>