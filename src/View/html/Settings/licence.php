<?php

/**
 * License Settings View
 *
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.2
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="pdfextended-settings">
	<form method="post" class="gform_settings_form" action="options.php">
		<?php settings_fields( 'gfpdf_settings' ); ?>

		<?= $content ?>

		<?php if ( $args['edit_cap'] ): ?>
			<div id="submit-and-promo-container">
				<input type="submit" name="submit" id="submit" value="<?= __( 'Save Settings  →', 'gravityforms' ) ?>" class="button primary large">
			</div>
		<?php endif; ?>
	</form>

	<?php
	/* @TODO */
	do_action( 'gfpdf_post_license_settings_page' );
	?>
</div>
