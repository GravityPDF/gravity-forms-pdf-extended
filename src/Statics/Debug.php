<?php

namespace GFPDF\Statics;

/**
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2024, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 7.0.0
 */
class Debug {

	/**
	 * Checks if the WP site is in debug mode
	 *
	 * Currently, debug mode is considered on if the PDF Debug Mode setting is explicitly enabled
	 * or the WP environment is not set to production
	 *
	 * @return bool
	 *
	 * @since 7.0
	 */
	public static function is_enabled(): bool {
		$options = \GPDFAPI::get_options_class();

		$pdf_debug_mode            = $options->get_option( 'debug_mode', 'No' ) === 'Yes';
		$wp_production_environment = ! function_exists( 'wp_get_environment_type' ) || wp_get_environment_type() === 'production';

		return $pdf_debug_mode || ! $wp_production_environment;
	}

	/**
	 * Check the logged-in user has a specific capability which can view the log info
	 *
	 * @return bool
	 *
	 * @since 7.0
	 */
	public static function can_view(): bool {
		$gform = \GPDFAPI::get_form_class();

		return $gform->has_capability( 'gravityforms_logging' );

	}

	/**
	 * Verify debug mode is enabled and the user can view the log info
	 *
	 * @return bool
	 *
	 * @since 7.0
	 */
	public static function is_enabled_and_can_view(): bool {
		return static::is_enabled() && static::can_view();
	}
}
