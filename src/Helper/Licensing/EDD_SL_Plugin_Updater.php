<?php

namespace GFPDF\Helper\Licensing;

/**
 * @package     Gravity PDF
 * @author      Easy Digital Downloads
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       6.13
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* The autoloader may not be included yet. Ensure base class is loaded */
if ( ! class_exists( '\GFPDF\Helper\Licensing\EDD_SL_Plugin_Updater_Base' ) ) {
	require_once __DIR__ . '/EDD_SL_Plugin_Updater_Base.php';
}

/**
 * Allows plugins to use their own update API.
 */
class EDD_SL_Plugin_Updater extends EDD_SL_Plugin_Updater_Base {

	public function __construct( $_api_url, $_plugin_file, $_api_data = null ) {
		parent::__construct( $_api_url, $_plugin_file, $_api_data );

		//delete_site_transient( 'update_plugins' );
	}

	/**
	 * Standardize the cached response
	 *
	 * @param $cache_key
	 *
	 * @return false|object
	 */
	public function get_cached_version_info( $cache_key = '' ) {
		$value = parent::get_cached_version_info( $cache_key );

		if ( isset( $value->sections ) && ! is_array( $value->sections ) ) {
			$value->sections = $this->convert_object_to_array( $value->sections );
		}

		if ( isset( $value->banners ) && ! is_array( $value->banners ) ) {
			$value->banners = $this->convert_object_to_array( $value->banners );
		}

		if ( isset( $value->icons ) && ! is_array( $value->icons ) ) {
			$value->icons = $this->convert_object_to_array( $value->icons );
		}

		return $value;
	}

	/**
	 * Always return the full data array, and not a subset of the data
	 *
	 * This is required so subsites in a Network can correctly display the changelog info
	 *
	 * @return \stdClass|false
	 */
	public function get_update_transient_data() {
		return $this->get_repo_api_data();
	}

	/**
	 * When the plugin version info is being saved, also save it to the network `update_plugins` transient
	 *
	 * @param $value
	 * @param $cache_key
	 *
	 * @return void
	 */
	public function set_version_info_cache( $value = '', $cache_key = '' ) {
		parent::set_version_info_cache( $value, $cache_key );

		/* If multisite, store new info in the site transient */
		if ( is_multisite() && ! is_network_admin() ) {
			$current = get_site_transient( 'update_plugins' );
			if ( $current === false ) {
				/* force the entire network update */
				/* @TODO - move to background process and/or daily schedule */
				/* @TODO - ensure WP marks the plugins as "checked" so it doesn't refresh it on the network tab */
				switch_to_blog( 1 );
				wp_update_plugins();
				restore_current_blog();
				$current = get_site_transient( 'update_plugins' );
			}

			set_site_transient( 'update_plugins', $current );
		}
	}
}
