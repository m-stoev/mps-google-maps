<?php defined('ABSPATH') || die('die'); ?>

<div class="wrap">
	<h1><?= mps_tr('MPS Map Settings'); ?></h1>
	
	<form method="post" action="options.php">
		you page short code is: <i>[mps-google-map]</i>
		
		<?php settings_fields($option_group); ?>
		<?php do_settings_sections($option_group); ?>
		
		<?php submit_button(); ?>
	</form>
</div>