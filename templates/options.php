<?php defined('ABSPATH') || die('die'); ?>

<div class="wrap">
	<?php settings_errors(); ?>
	
	<h1><?= mps_tr('MPS Map Settings'); ?></h1>
	
	<form method="post" action="options.php">
		<?php settings_fields('mps_gm_settings'); ?>
		<?php do_settings_sections('mps_gm_settings'); ?>
		
		<?php submit_button(); ?>
	</form>
	
	<p><?= mps_tr('Your page short code is'); ?>: [mps-google-map]</p>
</div>