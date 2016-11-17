<?php

namespace GFPDF\Tests;

use GPDFAPI;

use WP_UnitTestCase;

/**
 * Test Gravity PDF Hlper Misc Functionality
 *
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2016, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/*
    This file is part of Gravity PDF.

    Gravity PDF – Copyright (C) 2016, Blue Liquid Designs

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Test the GPDFAPI class
 *
 * @since 4.0
 * @group api
 */
class Test_API extends WP_UnitTestCase {

	/**
	 * Check the correct class is returned
	 *
	 * @since        4.0
	 *
	 * @dataProvider provider_classes
	 */
	public function test_get_class( $expected, $method ) {
		$this->assertEquals( $expected, get_class( GPDFAPI::$method() ) );
	}

	/**
	 * The data provider passing in our class getter methods and expected values
	 *
	 * @since 4.0
	 */
	public function provider_classes() {
		return array(
			array( 'Monolog\Logger', 'get_log_class' ),
			array( 'GFPDF\Helper\Helper_Notices', 'get_notice_class' ),
			array( 'GFPDF\Helper\Helper_Data', 'get_data_class' ),
			array( 'GFPDF\Helper\Helper_Options_Fields', 'get_options_class' ),
			array( 'GFPDF\Helper\Helper_Misc', 'get_misc_class' ),
			array( 'GFPDF\Helper\Helper_Form', 'get_form_class' ),
			array( 'GFPDF\Helper\Helper_Templates', 'get_templates_class' ),
		);
	}

	/**
	 * Check we can get a form's PDF settings
	 *
	 * @since 4.0
	 */
	public function test_get_form_pdfs() {
		$this->assertTrue( is_wp_error( GPDFAPI::get_form_pdfs( null ) ) );
	}

	/**
	 * Check we are returning the correct class when called
	 *
	 * @since 4.0
	 */
	public function test_get_pdf_class() {
		/* Check View Class */
		$class = GPDFAPI::get_pdf_class();
		$this->assertEquals( 'GFPDF\View\View_PDF', get_class( $class ) );

		/* Check Model Class */
		$class = GPDFAPI::get_pdf_class( 'model' );
		$this->assertEquals( 'GFPDF\Model\Model_PDF', get_class( $class ) );
	}

	/**
	 * Verify our API returns the correct classes
	 *
	 * @since 4.0
	 */
	public function test_get_mvc_class() {
		$class = GPDFAPI::get_mvc_class( 'Model_Install' );

		$this->assertEquals( 'GFPDF\Model\Model_Install', get_class( $class ) );

		$this->assertFalse( GPDFAPI::get_mvc_class( 'Fake_Class' ) );
	}

	/**
	 * Check we can add a new PDF
	 *
	 * @since 4.0
	 */
	public function test_add_update_delete() {

		/* Check we can add a new PDF */
		$id = GPDFAPI::add_pdf( $GLOBALS['GFPDF_Test']->form['form-settings']['id'], array( 'working' => 'yes' ) );
		$this->assertNotFalse( $id );

		/* Check we can get the PDF details */
		$pdf = GPDFAPI::get_pdf( $GLOBALS['GFPDF_Test']->form['form-settings']['id'], $id );
		$this->assertEquals( 'yes', $pdf['working'] );

		/* Check we can update the PDF details correctly */
		GPDFAPI::update_pdf( $GLOBALS['GFPDF_Test']->form['form-settings']['id'], $id, array( 'working' => 'no' ) );
		$pdf = GPDFAPI::get_pdf( $GLOBALS['GFPDF_Test']->form['form-settings']['id'], $id );
		$this->assertEquals( 'no', $pdf['working'] );

		/* Check we can delete the PDF correctly */
		GPDFAPI::delete_pdf( $GLOBALS['GFPDF_Test']->form['form-settings']['id'], $id );
		$pdf = GPDFAPI::get_pdf( $GLOBALS['GFPDF_Test']->form['form-settings']['id'], $id );
		$this->assertTrue( is_wp_error( $pdf ) );
	}

	/**
	 * Check we can get the global Gravity PDF settings
	 *
	 * @since 4.0
	 */
	public function test_get_plugin_settings() {

		/* Add some settings */
		GPDFAPI::update_plugin_option( 'item1', 'yes' );
		GPDFAPI::update_plugin_option( 'item2', 'no' );

		/* Select the settings and verify the results */
		$settings = GPDFAPI::get_plugin_settings();

		$this->assertEquals( 'yes', $settings['item1'] );
		$this->assertEquals( 'no', $settings['item2'] );

		/* Add another option but cause an error */
		$this->assertTrue( is_wp_error( GPDFAPI::add_plugin_option( 'item1', 'yes' ) ) );
		$this->assertTrue( GPDFAPI::add_plugin_option( 'item3', 'maybe' ) );

		/* Check our getter works correctly */
		$this->assertEquals( 'maybe', GPDFAPI::get_plugin_option( 'item3' ) );

		/* Check our delete function works correctly */
		GPDFAPI::delete_plugin_option( 'item3' );
		$this->assertEquals( '', GPDFAPI::get_plugin_option( 'item3' ) );

		/* Cleanup */
		GPDFAPI::delete_plugin_option( 'item2' );
		GPDFAPI::delete_plugin_option( 'item1' );

		/* Verify cleanup */
		$this->assertSame( 0, sizeof( GPDFAPI::get_plugin_settings() ) );
	}

	/**
	 * Ensure we generate the product table correctly
	 *
	 * @since 4.0
	 */
	public function test_product_table() {

		$entry = $GLOBALS['GFPDF_Test']->entries['all-form-fields'][0];

		$table = GPDFAPI::product_table( $entry, true );
		$this->assertNotFalse( strpos( $table, '<table class="entry-products' ) );

		ob_start();
		GPDFAPI::product_table( $entry );
		$table = ob_get_clean();
		$this->assertNotFalse( strpos( $table, '<table class="entry-products' ) );
	}

	/**
	 * Ensure we generate the likert table correctly
	 *
	 * @since 4.0
	 */
	public function test_likert_table() {
		$entry = $GLOBALS['GFPDF_Test']->entries['all-form-fields'][0];

		$table = GPDFAPI::likert_table( $entry, 26, true );
		$this->assertNotFalse( strpos( $table, "class='gsurvey-likert-choice-label'" ) );

		ob_start();
		GPDFAPI::likert_table( $entry, 26 );
		$table = ob_get_clean();
		$this->assertNotFalse( strpos( $table, "class='gsurvey-likert-choice-label'" ) );
	}
}
