<?php

namespace GFPDF\Tests;

use GFPDF\Helper\Helper_Options_Fields;

use GFAPI;
use GFForms;

use WP_UnitTestCase;

/**
 * Test Gravity PDF Options API Class
 *
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2019, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.0
 */

/**
 * Test the WordPress Options API Implimentation
 *
 * @since 4.0
 * @group options-api
 */
class Test_Options_API extends WP_UnitTestCase {

	/**
	 * Our Gravity PDF Options API Object
	 *
	 * @var \GFPDF\Helper\Helper_Options_Fields
	 *
	 * @since 4.0
	 */
	public $options;

	/**
	 * The Gravity Form ID we are working with
	 *
	 * @var integer
	 *
	 * @since  4.0
	 */
	public $form_id;

	/**
	 * The WP Unit Test Set up function
	 *
	 * @since 4.0
	 */
	public function setUp() {
		global $gfpdf;

		/* run parent method */
		parent::setUp();

		/* setup our object */
		$this->options = new Helper_Options_Fields( $gfpdf->log, $gfpdf->gform, $gfpdf->data, $gfpdf->misc, $gfpdf->notices, $gfpdf->templates );

		/* load settings in database  */
		update_option( 'gfpdf_settings', json_decode( file_get_contents( dirname( __FILE__ ) . '/json/options-settings.json' ), true ) );

		/* Load a form / form PDF settings into database */
		$this->form_id                                = $GLOBALS['GFPDF_Test']->form['form-settings']['id'];
		$gfpdf->data->form_settings[ $this->form_id ] = $GLOBALS['GFPDF_Test']->form['form-settings']['gfpdf_form_settings'];

		/* Set up our global settings */
		$this->options->set_plugin_settings();
	}

	/**
	 * Check if settings getter function works correctly
	 *
	 * @since 4.0
	 */
	public function test_get_settings() {

		/**
		 * Check our default action works correctly
		 */
		$settings = $this->options->get_settings();

		$this->assertEquals( 'custom', $settings['default_pdf_size'] );
		$this->assertEquals( 'Awesomeness', $settings['default_template'] );
		$this->assertEquals( 'dejavusans', $settings['default_font_type'] );
		$this->assertEquals( 'No', $settings['default_rtl'] );
		$this->assertEquals( 'View', $settings['default_action'] );
		$this->assertEquals( 'No', $settings['default_restrict_owner'] );
		$this->assertEquals( '20', $settings['logged_out_timeout'] );

		$this->assertTrue( is_array( $settings['default_custom_pdf_size'] ) );
		$this->assertTrue( is_array( $settings['admin_capabilities'] ) );

		$this->assertEquals( 30, $settings['default_custom_pdf_size'][0] );
		$this->assertEquals( 50, $settings['default_custom_pdf_size'][1] );
		$this->assertEquals( 'millimeters', $settings['default_custom_pdf_size'][2] );

		$this->assertEquals( 'gravityforms_create_form', $settings['admin_capabilities'][0] );

		/**
		 * Check our transient user data is loaded
		 * Used in settings_sanitize() when there are errors the user has to fix
		 */
		set_transient( 'gfpdf_settings_user_data', [ 'testing' ], 30 );

		set_current_screen( 'dashboard' );
		$_GET['page'] = 'gfpdf-';

		$this->assertEquals( [ 'testing' ], $this->options->get_settings() );
	}

	/**
	 * @since 4.2
	 */
	public function test_update_settings() {
		$oldsettings = $this->options->get_settings();
		$settings    = $oldsettings;

		$this->assertEquals( 'custom', $settings['default_pdf_size'] );
		$settings['default_pdf_size'] = 'Letter';
		$this->options->update_settings( $settings );

		$settings = $this->options->get_settings();
		$this->assertEquals( 'Letter', $settings['default_pdf_size'] );
		$this->options->update_settings( $oldsettings );
	}

	/**
	 * Check if Gravity Forms PDF settings getter function works correctly
	 *
	 * @since 4.0
	 */
	public function test_get_form_settings() {
		/* test false values */
		$this->assertEmpty( $this->options->get_form_settings() );

		$_GET['id'] = $this->form_id + 50; /* a form ID that won't exist */

		/* check empty array is returned */
		$this->assertEmpty( $this->options->get_form_settings() );

		/* Set up and return real values */
		$form = GFAPI::get_form( $this->form_id );

		reset( $form['gfpdf_form_settings'] );
		$pid = key( $form['gfpdf_form_settings'] );

		/* set up our $_GET variables */
		$_GET['id']  = $this->form_id;
		$_GET['pid'] = $pid;

		/* get legitimate results */
		$results = $this->options->get_form_settings();

		/* check they contain values */
		$this->assertNotEmpty( $results );

		/* check for specific values */
		$this->assertEquals( 'My First PDF Template', $results['name'] );
		$this->assertEquals( 'Gravity Forms Style', $results['template'] );
		$this->assertTrue( in_array( 'Admin Notification', $results['notification'], true ) );
	}

	/**
	 * Check that any settings passed in through get_registered_settings() gets registered correctly
	 *
	 * @since 4.0
	 */
	public function test_register_settings() {
		global $wp_settings_fields, $new_whitelist_options;

		$fields = [
			'general' => [
				'my_test_item' => [
					'id'   => 'my_test_item',
					'name' => 'Test Item',
					'type' => 'text',
				],
			],
		];

		/* call our function */
		$this->options->register_settings( $fields );

		/* Test setting was added correctly */
		$this->assertTrue( isset( $wp_settings_fields['gfpdf_settings_general']['gfpdf_settings_general']['gfpdf_settings[my_test_item]'] ) );
		$this->assertEquals( 'Test Item', $wp_settings_fields['gfpdf_settings_general']['gfpdf_settings_general']['gfpdf_settings[my_test_item]']['title'] );

		/* Test our registered settings were added */
		$this->assertTrue( isset( $new_whitelist_options['gfpdf_settings'] ) );

		/* clean up filter */
		remove_all_filters( 'gfpdf_registered_fields' );
	}

	/**
	 * Check that we can successfully update a registered field item
	 *
	 * @since 4.0
	 */
	public function test_update_registered_field() {
		global $wp_settings_fields;

		$this->options->register_settings( $this->options->get_registered_fields() );

		$group     = 'gfpdf_settings_form_settings';
		$setting   = 'gfpdf_settings[notification]';
		$option_id = 'options';

		/* Run false test */
		$this->assertSame( 0, sizeof( $wp_settings_fields[ $group ][ $group ][ $setting ]['args'][ $option_id ] ) );

		/* Run valid test */
		$this->options->update_registered_field( 'form_settings', 'notification', 'options', 'working' );

		$this->assertEquals( 'working', $wp_settings_fields[ $group ][ $group ][ $setting ]['args'][ $option_id ] );
	}

	/**
	 * Check the options list is returned correctly
	 *
	 * @since 4.0
	 */
	public function test_get_registered_settings() {
		$items = $this->options->get_registered_fields();

		/* Check the array */
		$this->assertTrue( isset( $items['general'] ) );
		$this->assertTrue( isset( $items['general_security'] ) );
		$this->assertTrue( isset( $items['extensions'] ) );
		$this->assertTrue( isset( $items['licenses'] ) );
		$this->assertTrue( isset( $items['tools'] ) );
		$this->assertTrue( isset( $items['form_settings'] ) );
		$this->assertTrue( isset( $items['form_settings_appearance'] ) );
		$this->assertTrue( isset( $items['form_settings_advanced'] ) );

		/* Check filters work correctly */
		add_filter(
			'gfpdf_settings_general',
			function( $array ) {
				return 'General Settings';
			}
		);

		add_filter(
			'gfpdf_settings_general_security',
			function( $array ) {
				return 'General Security Settings';
			}
		);

		add_filter(
			'gfpdf_settings_extensions',
			function( $array ) {
				return 'Extension Settings';
			}
		);

		add_filter(
			'gfpdf_settings_licenses',
			function( $array ) {
				return 'License Settings';
			}
		);

		add_filter(
			'gfpdf_settings_tools',
			function( $array ) {
				return 'Tools Settings';
			}
		);

		add_filter(
			'gfpdf_form_settings',
			function( $array ) {
				return 'PDF Form Settings';
			}
		);

		add_filter(
			'gfpdf_form_settings_appearance',
			function( $array ) {
				return 'PDF Form Settings Appearance';
			}
		);

		add_filter(
			'gfpdf_form_settings_advanced',
			function( $array ) {
				return 'PDF Form Settings Advanced';
			}
		);

		/* reset items */
		$items = $this->options->get_registered_fields();

		$this->assertEquals( 'General Settings', $items['general'] );
		$this->assertEquals( 'General Security Settings', $items['general_security'] );
		$this->assertEquals( 'Extension Settings', $items['extensions'] );
		$this->assertEquals( 'License Settings', $items['licenses'] );
		$this->assertEquals( 'Tools Settings', $items['tools'] );
		$this->assertEquals( 'PDF Form Settings', $items['form_settings'] );
		$this->assertEquals( 'PDF Form Settings Appearance', $items['form_settings_appearance'] );
		$this->assertEquals( 'PDF Form Settings Advanced', $items['form_settings_advanced'] );

		/* Cleanup */
		remove_all_filters( 'gfpdf_settings_general' );
		remove_all_filters( 'gfpdf_settings_general_security' );
		remove_all_filters( 'gfpdf_settings_extensions' );
		remove_all_filters( 'gfpdf_settings_licenses' );
		remove_all_filters( 'gfpdf_settings_tools' );
		remove_all_filters( 'gfpdf_form_settings' );
		remove_all_filters( 'gfpdf_form_settings_appearance' );
		remove_all_filters( 'gfpdf_form_settings_advanced' );
	}

	/**
	 * Test we can get the form's PDF settings
	 *
	 * @since 4.0
	 */
	public function test_get_form_pdfs() {
		/* get our form settings */
		$settings = $this->options->get_form_pdfs( $this->form_id );

		/* check basic expected results */
		$this->assertTrue( is_array( $settings ) );
		$this->assertEquals( 3, sizeof( $settings ) );

		/* check values are avaliable */
		$pdf = $settings['555ad84787d7e'];

		$this->assertEquals( 'My First PDF Template', $pdf['name'] );
		$this->assertEquals( 'Gravity Forms Style', $pdf['template'] );
		$this->assertTrue( is_array( $pdf['notification'] ) );
		$this->assertEquals( 2, sizeof( $pdf['notification'] ) );
		$this->assertEquals( 'test', $pdf['filename'] );
		$this->assertTrue( is_array( $pdf['conditionalLogic'] ) );
		$this->assertEquals( 3, sizeof( $pdf['conditionalLogic'] ) );
		$this->assertEquals( 'custom', $pdf['pdf_size'] );
		$this->assertEquals( '150', $pdf['custom_pdf_size'][0] );
		$this->assertEquals( '300', $pdf['custom_pdf_size'][1] );
		$this->assertEquals( 'millimeters', $pdf['custom_pdf_size'][2] );
		$this->assertEquals( 'landscape', $pdf['orientation'] );
		$this->assertEquals( 'dejavusans', $pdf['font'] );
		$this->assertEquals( 'No', $pdf['rtl'] );
		$this->assertEquals( 'Standard', $pdf['format'] );
		$this->assertEquals( 'Yes', $pdf['security'] );
		$this->assertEquals( 'my password', $pdf['password'] );
		$this->assertTrue( is_array( $pdf['privileges'] ) );
		$this->assertEquals( 8, sizeof( $pdf['privileges'] ) );
		$this->assertEquals( '300', $pdf['image_dpi'] );
		$this->assertEquals( 'No', $pdf['save'] );
		$this->assertEquals( '555ad84787d7e', $pdf['id'] );
		$this->assertSame( true, $pdf['active'] );
	}

	/**
	 * Test we can get individual PDF settings
	 *
	 * @since 4.0
	 */
	public function test_get_pdf() {
		$pdf = $this->options->get_pdf( $this->form_id, '555ad84787d7e' );
		$this->assertEquals( 'My First PDF Template', $pdf['name'] );

		$pdf = $this->options->get_pdf( $this->form_id, '556690c67856b' );
		$this->assertEquals( 'My First PDF Template (copy)', $pdf['name'] );

		$pdf = $this->options->get_pdf( $this->form_id, '556690c8d7f82' );
		$this->assertEquals( 'Disable PDF Template', $pdf['name'] );
		$this->assertSame( false, $pdf['active'] );
	}

	/**
	 * Test we can successfully add a new PDF setting
	 *
	 * @since 4.0
	 */
	public function test_add_pdf() {
		global $gfpdf;

		$pdf = [
			'name'     => 'Added PDF',
			'template' => 'default-template',
		];

		$id = $this->options->add_pdf( $this->form_id, $pdf );

		/* check it was successful */
		$this->assertNotFalse( $id );

		/* remove local cache and retest */
		$gfpdf->data->form_settings = [];

		/* verify it was added */
		$pdf = $this->options->get_pdf( $this->form_id, $id );

		$this->assertEquals( 'Added PDF', $pdf['name'] );
		$this->assertEquals( 'default-template', $pdf['template'] );
	}

	/**
	 * Test we can make changes to individual PDF settings
	 *
	 * @since 4.0
	 */
	public function test_update_pdf() {
		global $gfpdf;

		/* get the configuration node */
		$pid = '555ad84787d7e';
		$pdf = $this->options->get_pdf( $this->form_id, $pid );

		/* assign new values */
		$pdf['name']   = 'My New Name';
		$pdf['active'] = false;

		/* update database */
		$this->options->update_pdf( $this->form_id, $pid, $pdf );

		/* check the update was successful */
		$new_pdf = $this->options->get_pdf( $this->form_id, $pid );

		/* ensure everything worked correctly */
		$this->assertEquals( 'My New Name', $new_pdf['name'] );
		$this->assertSame( false, $new_pdf['active'] );

		/* remove local cache and retest */
		$gfpdf->data->form_settings = [];

		/* retest */
		$new_pdf = $this->options->get_pdf( $this->form_id, $pid );

		/* ensure everything worked correctly */
		$this->assertEquals( 'My New Name', $new_pdf['name'] );
		$this->assertSame( false, $new_pdf['active'] );

		/* check the auto delete functionality works correctly */
		$this->options->update_pdf( $this->form_id, $pid );

		/* test that the PDF was deleted in the last call */
		$has_pdf_deleted = $this->options->get_pdf( $this->form_id, $pid );

		/* check it was deleted */
		$this->assertTrue( is_wp_error( $has_pdf_deleted ) );
	}

	/**
	 * Test we can make delete individual PDF settings
	 *
	 * @since 4.0
	 */
	public function test_delete_pdf() {

		/* check the pdf exists */
		$pid = '555ad84787d7e';
		$pdf = $this->options->get_pdf( $this->form_id, $pid );
		$this->assertEquals( 'My First PDF Template', $pdf['name'] );

		/* test delete functionality */
		$this->assertSame( true, $this->options->delete_pdf( $this->form_id, $pid ) );
		$this->assertTrue( is_wp_error( $this->options->get_pdf( $this->form_id, $pid ) ) );

	}

	/**
	 * Check user's can correctly tap into the appropriate filters triggered
	 * during a get_pdf() call
	 *
	 * @since 4.0
	 */
	public function test_get_pdf_filter() {
		add_filter(
			'gfpdf_pdf_config',
			function() {
				return 'main filter fired';
			}
		);

		/* check filter was triggered */
		$this->assertEquals( 'main filter fired', $this->options->get_pdf( $this->form_id, '555ad84787d7e' ) );

		/* cleanup filters */
		remove_all_filters( 'gfpdf_pdf_config' );

		/* run individual form ID filter */
		add_filter(
			'gfpdf_pdf_config_' . $this->form_id,
			function() {
				return 'ID filter fired';
			}
		);

		/* check filter was triggered */
		$this->assertEquals( 'ID filter fired', $this->options->get_pdf( $this->form_id, '555ad84787d7e' ) );
	}

	/**
	 * Check user's can correctly tap into the appropriate filters triggered
	 * during an add_pdf() call
	 *
	 * @since 4.0
	 */
	public function test_add_pdf_filter() {
		add_filter(
			'gfpdf_form_add_pdf',
			function() {
				return [
					'id'   => 'id',
					'name' => 'Add Filter Fired',
				];
			}
		);

		/* run our method */
		$id = $this->options->add_pdf( $this->form_id, [ 'name' => 'test' ] );

		/* verify the results */
		$pdf = $this->options->get_pdf( $this->form_id, $id );
		$this->assertEquals( 'Add Filter Fired', $pdf['name'] );

		/* cleanup filters */
		remove_all_filters( 'gfpdf_pdf_config' );

		add_filter(
			'gfpdf_form_add_pdf_' . $this->form_id,
			function() {
				return [
					'id'   => 'id',
					'name' => 'ID Add Filter Fired',
				];
			}
		);

		/* run our method */
		$id = $this->options->add_pdf( $this->form_id, [ 'name' => 'test' ] );

		/* verify the results */
		$pdf = $this->options->get_pdf( $this->form_id, $id );
		$this->assertEquals( 'ID Add Filter Fired', $pdf['name'] );
	}

	/**
	 * Check user's can correctly tap into the appropriate filters triggered
	 * during a update_pdf() call
	 *
	 * @since 4.0
	 */
	public function test_update_pdf_filter() {
		add_filter(
			'gfpdf_form_update_pdf',
			function() {
				return [ 'name' => 'Update Filter Fired' ];
			}
		);

		/* run our method */
		$this->options->update_pdf( $this->form_id, '555ad84787d7e', [ 'name' => 'test' ] );

		/* verify the results */
		$pdf = $this->options->get_pdf( $this->form_id, '555ad84787d7e' );
		$this->assertEquals( 'Update Filter Fired', $pdf['name'] );

		/* cleanup filters */
		remove_all_filters( 'gfpdf_pdf_config' );

		add_filter(
			'gfpdf_form_update_pdf_' . $this->form_id,
			function() {
				return [ 'name' => 'ID Update Filter Fired' ];
			}
		);

		/* run our method */
		$this->options->update_pdf( $this->form_id, '555ad84787d7e', [ 'name' => 'test' ] );

		/* verify the results */
		$pdf = $this->options->get_pdf( $this->form_id, '555ad84787d7e' );
		$this->assertEquals( 'ID Update Filter Fired', $pdf['name'] );
	}


	/**
	 * Test we can get a single global PDF option
	 *
	 * @since 4.0
	 */
	public function test_get_option() {
		/* test for real values */
		$this->assertEquals( 'custom', $this->options->get_option( 'default_pdf_size' ) );
		$this->assertEquals( 'Awesomeness', $this->options->get_option( 'default_template' ) );
		$this->assertEquals( 'No', $this->options->get_option( 'default_restrict_owner' ) );
		$this->assertTrue( is_array( $this->options->get_option( 'admin_capabilities' ) ) );

		/* test for non-existant option */
		$this->assertFalse( $this->options->get_option( 'non-existant' ) );

		/* test default when non-existant option */
		$this->assertTrue( $this->options->get_option( 'non-existant', true ) );

		/* check filters */
		add_filter(
			'gfpdf_get_option',
			function( $value ) {
				return 'New Value';
			}
		);

		$this->assertEquals( 'New Value', $this->options->get_option( 'default_pdf_size' ) );

		/* clean up */
		remove_all_filters( 'gfpdf_get_option' );

		add_filter(
			'gfpdf_get_option_default_rtl',
			function( $value ) {
				return 'RTL';
			}
		);

		$this->assertEquals( 'RTL', $this->options->get_option( 'default_rtl' ) );

		/* cleanup */
		remove_all_filters( 'gfpdf_get_option_default_rtl' );
	}

	/**
	 * Test we can update a single global PDF option
	 *
	 * @since 4.0
	 */
	public function test_update_option() {
		/* test failures */
		$this->assertFalse( $this->options->update_option() );
		$this->assertFalse( $this->options->update_option( '' ) );

		/* test update functionality */
		$this->assertTrue( $this->options->update_option( 'default_pdf_size', 'new pdf size' ) );
		$this->assertEquals( 'new pdf size', $this->options->get_option( 'default_pdf_size' ) );

		/* Check filters */
		add_filter(
			'gfpdf_update_option',
			function( $value ) {
				return 'filtered option';
			}
		);

		$this->assertTrue( $this->options->update_option( 'default_pdf_size', 'new pdf size' ) );
		$this->assertEquals( 'filtered option', $this->options->get_option( 'default_pdf_size' ) );

		remove_all_filters( 'gfpdf_update_option' );

		add_filter(
			'gfpdf_update_option_default_restrict_owner',
			function( $value ) {
				return 'filtered admin option';
			}
		);

		$this->assertTrue( $this->options->update_option( 'default_pdf_size', 'new pdf size' ) );
		$this->assertEquals( 'new pdf size', $this->options->get_option( 'default_pdf_size' ) );

		$this->assertTrue( $this->options->update_option( 'default_restrict_owner', 'admin' ) );
		$this->assertEquals( 'filtered admin option', $this->options->get_option( 'default_restrict_owner' ) );

		remove_all_filters( 'gfpdf_update_option_default_restrict_owner' );
	}

	/**
	 * Test we can delete a single global PDF option
	 *
	 * @since 4.0
	 */
	public function test_delete_option() {
		/* test failure */
		$this->assertFalse( $this->options->delete_option() );
		$this->assertFalse( $this->options->delete_option( '' ) );

		/* test delete functionality */
		$this->assertEquals( 'custom', $this->options->get_option( 'default_pdf_size' ) );
		$this->assertTrue( $this->options->delete_option( 'default_pdf_size' ) );
		$this->assertFalse( $this->options->get_option( 'default_pdf_size' ) );
	}

	/**
	 * Test the returned capabilities list
	 *
	 * @since 4.0
	 */
	public function test_get_capabilities() {
		$capabilities = $this->options->get_capabilities();

		$this->assertTrue( isset( $capabilities['Gravity Forms Capabilities'] ) );
		$this->assertTrue( isset( $capabilities['Active WordPress Capabilities'] ) );

		$this->assertNotSame( 0, sizeof( $capabilities['Gravity Forms Capabilities'] ) );
		$this->assertNotSame( 0, sizeof( $capabilities['Active WordPress Capabilities'] ) );
	}

	/**
	 * Test the returned paper size list
	 *
	 * @since 4.0
	 */
	public function test_get_paper_size() {
		$paper_size = $this->options->get_paper_size();

		$this->assertTrue( isset( $paper_size['Common Sizes'] ) );
		$this->assertTrue( isset( $paper_size['&quot;A&quot; Sizes'] ) );
		$this->assertTrue( isset( $paper_size['&quot;B&quot; Sizes'] ) );
		$this->assertTrue( isset( $paper_size['&quot;C&quot; Sizes'] ) );
		$this->assertTrue( isset( $paper_size['&quot;RA&quot; and &quot;SRA&quot; Sizes'] ) );
	}

	/**
	 * Test the installed fonts getter functionality
	 *
	 * @since 4.0
	 */
	public function test_get_installed_fonts() {

		$fonts = $this->options->get_installed_fonts();

		$this->assertArrayHasKey( 'Unicode', $fonts );
		$this->assertArrayHasKey( 'Indic', $fonts );
		$this->assertArrayHasKey( 'Arabic', $fonts );
		$this->assertArrayHasKey( 'Other', $fonts );

		$this->assertTrue( isset( $fonts['Unicode']['dejavusans'] ) );
	}

	/**
	 * Add a custom font to our array
	 *
	 * @since 4.0
	 */
	public function test_add_custom_fonts() {

		$fonts = [
			[ 'font_name' => 'Helvetica' ],
			[ 'font_name' => 'Calibri Bold' ],
		];

		$this->options->update_option( 'custom_fonts', $fonts );

		$existing_fonts = [
			'Unicode' => [
				'dejavusans' => 'Dejavu Sans',
				'courier'    => 'Courier',
			],
		];

		$get_fonts = $this->options->add_custom_fonts( $existing_fonts );

		$this->assertTrue( isset( $get_fonts['Unicode'] ) );
		$this->assertTrue( isset( $get_fonts['User-Defined Fonts'] ) );

		$this->assertSame( 2, sizeof( $get_fonts['Unicode'] ) );
		$this->assertSame( 2, sizeof( $get_fonts['User-Defined Fonts'] ) );
	}

	/**
	 * Test the custom font getter
	 *
	 * @since 4.0
	 */
	public function test_get_custom_fonts() {

		$fonts = [
			[ 'font_name' => 'Helvetica' ],
			[ 'font_name' => 'Calibri Bold' ],
		];

		$this->options->update_option( 'custom_fonts', $fonts );

		$get_fonts = $this->options->get_custom_fonts();

		$this->assertEquals( 'helvetica', $get_fonts[0]['shortname'] );
		$this->assertEquals( 'calibribold', $get_fonts[1]['shortname'] );
	}

	/**
	 * Test the font display name getter
	 *
	 * @since 4.0
	 */
	public function test_get_font_display_name() {
		$this->assertEquals( 'Dejavu Sans', $this->options->get_font_display_name( 'dejavusans' ) );
	}

	/**
	 * Test the privilages getter
	 *
	 * @since 4.0
	 */
	public function test_get_privilages() {
		$this->assertTrue( is_array( $this->options->get_privilages() ) );
	}

	/**
	 * Test we are correctly sanitizing our settings
	 *
	 * @since 4.0
	 */
	public function test_settings_sanitize() {

		/* Test failed referer / option name */
		$this->assertEquals( 'test', $this->options->settings_sanitize( 'test' ) );

		$_POST['_wp_http_referer'] = '?tab=general';
		$_POST['option_page']      = 'gfpdf_settings';

		$input = [
			'default_pdf_size'  => 'A5',
			'default_font_size' => '15',
			'other_type'        => 'wont validate',
		];

		/* Test our current settings */
		$initial_settings = $this->options->get_settings();

		$this->assertEquals( 'custom', $initial_settings['default_pdf_size'] );
		$this->assertArrayNotHasKey( 'default_font_size', $initial_settings );
		$this->assertArrayNotHasKey( 'other_type', $initial_settings );

		/* Run our settings santize function and check the results are accurate */
		$this->options->settings_sanitize( $input );

		set_current_screen( 'dashboard' );
		$_GET['page'] = 'gfpdf-';

		$updated_settings = $this->options->get_settings();

		$this->assertEquals( 'A5', $updated_settings['default_pdf_size'] );
		$this->assertEquals( '15', $updated_settings['default_font_size'] );
		$this->assertArrayNotHasKey( 'other_type', $updated_settings );
	}

	/**
	 * Test the trim sanitisation function
	 *
	 * @since        4.0
	 *
	 * @dataProvider dataprovider_sanitize_trim
	 */
	public function test_sanitize_trim_field( $expected, $input ) {
		$this->assertEquals( $expected, $this->options->sanitize_trim_field( $input ) );
	}

	/**
	 * Test data provider for our trim functionality (test_sanitize_trim_field)
	 *
	 * @return array The data to test
	 *
	 * @since  4.0
	 */
	public function dataprovider_sanitize_trim() {
		return [
			[ 'My First PDF', '    My First PDF ' ],
			[ 'My First   PDF', 'My First   PDF   ' ],
			[
				'123_Advanced_{My Funny\\\'s PDF Name:213}',
				'              123_Advanced_{My Funny\\\'s PDF Name:213}',
			],
			[ '驚いた彼は道を走っていった', '   驚いた彼は道を走っていった  ' ],
			[ 'élève forêt', 'élève forêt                ' ],
			[ 'English', 'English' ],
			[ 'मानक हिन्दी', '            मानक हिन्दी ' ],
		];
	}

	/**
	 * Test the number sanitisation function
	 *
	 * @since        4.0
	 *
	 * @dataProvider dataprovider_sanitize_number
	 */
	public function test_sanitize_number_field( $expected, $input ) {
		$this->assertSame( $expected, $this->options->sanitize_number_field( $input ) );
	}

	/**
	 * Test data provider for our number functionality (test_sanitize_number_field)
	 *
	 * @return array The data to test
	 *
	 * @since  4.0
	 */
	public function dataprovider_sanitize_number() {
		return [
			[ 122, '122.34343The' ],
			[ 0, 'The122.34343' ],
			[ 20, '20' ],
			[ 2000, '2000' ],
			[ 20, '20.50' ],
			[ 50, '50,20' ],
		];
	}

	/**
	 * Test the paper size sanitisation function
	 *
	 * @since        4.0
	 *
	 * @dataProvider dataprovider_sanitize_paper_size
	 */
	public function test_sanitize_paper_size( $expected, $input ) {
		$this->assertSame( $expected, $this->options->sanitize_paper_size( $input ) );
	}

	/**
	 * Test data provider for our number functionality (test_sanitize_number_field)
	 *
	 * @return array The data to test
	 *
	 * @since  4.0
	 */
	public function dataprovider_sanitize_paper_size() {
		return [
			[ [ 200, 100, 'mm' ], [ 200, 100, 'mm' ] ],
			[ [ 200, 100, 'mm' ], [ -200, -100, 'mm' ] ],
			[ [ 72, 210, 'inches' ], [ 72, 210, 'inches' ] ],
			[ [ 72, 210, 'inches' ], [ -72, -210, 'inches' ] ],
			[ [ -20, -50 ], [ -20, -50 ] ],
			[ '50', '50' ],
		];
	}

	/**
	 * Test our global sanitisation function
	 *
	 * @since        4.0
	 *
	 * @dataProvider provider_sanitize_all_fields
	 */
	public function test_sanitize_all_fields( $type, $value, $expected ) {
		$this->assertEquals( $expected, $this->options->sanitize_all_fields( $value, '', '', [ 'type' => $type ] ) );
	}

	/**
	 * Test our sanitize_all_fields functions correctly
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function provider_sanitize_all_fields() {
		return [
			[
				'rich_editor',
				'<strong>Test</strong> <script>console.log("test");</script>',
				'<strong>Test</strong> <script>console.log("test");</script>',
			],
			[
				'textarea',
				'<em>Test</em> <script>console.log("test");</script>',
				'<em>Test</em> console.log("test");',
			],
			[ 'text', '<b><em>Test</em></b>', 'Test' ],
			[ 'checkbox', [ '<b>Item 1</b>', '<em>Item 2</em>' ], [ 'Item 1', 'Item 2' ] ],
		];
	}

	/**
	 * Test our required sanitized field errors trigger
	 *
	 * @since        4.0
	 *
	 * @dataProvider provider_sanitize_required_field
	 */
	public function test_sanitize_required_field( $type, $value, $expected ) {
		global $wp_settings_errors;

		/* Reset the WP errors */
		$wp_settings_errors = [];

		/* Setup data needed for our test */
		$input = [ 'default_pdf_size' => 'CUSTOM' ];

		$settings = [
			'required' => true,
			'type'     => $type,
		];

		/* Execute test */
		$this->options->sanitize_required_field( $value, $type, $input, $settings );

		/* Check the results */
		$this->assertEquals( $expected, sizeof( $wp_settings_errors ) );
	}

	/**
	 * Test our sanitize_required_field functions correctly
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function provider_sanitize_required_field() {
		return [
			[ 'select', [], true ],
			[ 'multicheck', [], true ],
			[ 'paper_size', [], true ],
			[ 'text', '', true ],

			[ 'select', [ 'item' ], false ],
			[ 'multicheck', [ 'item' ], false ],
			[ 'paper_size', [ '10', '20', 'cm' ], false ],
			[ 'text', 'Working', false ],
		];
	}

	/**
	 * Test we can correctly get the field details
	 *
	 * @since        4.0
	 *
	 * @dataProvider provider_get_form_value
	 *
	 * @todo         checkbox, multicheck and conditionalLogic
	 */
	public function test_get_form_value( $input, $expected ) {
		$_GET['id']  = $this->form_id;
		$_GET['pid'] = '555ad84787d7e';
		$this->options->update_option( 'default_font_size', 13 );

		$this->assertEquals( $expected, $this->options->get_form_value( $input ) );
	}

	/**
	 * The data provider for test_get_form_value()
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function provider_get_form_value() {
		return [

			/* Test Settings Radio */
			[
				[
					'id'      => 'default_action',
					'type'    => 'radio',
					'options' => [
						'View'     => 'View',
						'Download' => 'Download',
					],
				],
				'View',
			],

			/* Test Form Settings Radio */
			[
				[
					'id'      => 'rtl',
					'type'    => 'radio',
					'options' => [
						'Yes' => 'Yes',
						'No'  => 'No',
					],
				],
				'No',
			],

			/* Test Fallback Radio */
			[
				[
					'id'      => 'no_field',
					'type'    => 'radio',
					'options' => [
						'Yes' => 'Yes',
						'No'  => 'No',
					],
					'std'     => 'Yes',
				],
				'Yes',
			],

			/* Test Blank Radio */
			[
				[
					'id'      => 'no_field',
					'type'    => 'radio',
					'options' => [
						'Yes' => 'Yes',
						'No'  => 'No',
					],
				],
				'',
			],

			/* Test Settings Select */
			[
				[
					'id'      => 'admin_capabilities',
					'type'    => 'select',
					'options' => [
						'Gravity Forms Capabilities'    => [
							'gform_view_settings',
						],

						'Active WordPress Capabilities' => [
							'read',
						],
					],
				],
				[ 'gravityforms_create_form' ],
			],

			/* Test Form Settings Select */
			[
				[
					'id'      => 'template',
					'type'    => 'select',
					'options' => [],
				],
				'Gravity Forms Style',
			],

			/* Test Fallback Select */
			[
				[
					'id'      => 'no_field',
					'type'    => 'select',
					'options' => [
						'Yes' => 'Yes',
						'No'  => 'No',
					],
					'std'     => 'Yes',
				],
				'Yes',
			],

			/* Test Blank Select */
			[
				[
					'id'      => 'no_field',
					'type'    => 'select',
					'options' => [
						'Yes' => 'Yes',
						'No'  => 'No',
					],
				],
				'',
			],

			/* Test Settings Text */
			[
				[
					'id'   => 'default_font_size',
					'type' => 'number',
				],
				'13',
			],

			/* Test Form Settings Text */
			[
				[
					'id'   => 'name',
					'type' => 'text',
				],
				'My First PDF Template',
			],

			/* Test Fallback Text */
			[
				[
					'id'   => 'no_field',
					'type' => 'text',
					'std'  => 'Working',
				],
				'Working',
			],

			/* Test Blank Text */
			[
				[
					'id'   => 'no_field',
					'type' => 'text',
				],
				'',
			],
		];
	}

	/**
	 * @since 4.2
	 */
	public function test_get_form_value_licensing() {
		$results = $this->options->get_form_value(
			[
				'id'   => 'test',
				'type' => 'license',
			]
		);

		$this->assertArrayHasKey( 'key', $results );
		$this->assertArrayHasKey( 'msg', $results );
		$this->assertArrayHasKey( 'status', $results );
	}
}
