<?php

namespace GFPDF\Tests;


use GFPDF\Helper\Helper_Url_Signer;
use WP_UnitTestCase;

/**
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.2
 */

/**
 * @since 5.2
 * @group url-signer
 */
class Test_Url_Signer extends WP_UnitTestCase {

	/**
	 * @throws \Spatie\UrlSigner\Exceptions\InvalidSignatureKey
	 *
	 * @since 5.2
	 */
	public function test_sign_and_verify() {
		$signer = new Helper_Url_Signer();
		$url    = 'https://test.com';

		$this->assertNotEquals( $url, $signer->sign( $url, '+ 1 day' ) );
		$this->assertTrue( $signer->verify( $signer->sign( $url, '+ 1 day' ) ) );
		$this->assertFalse( $signer->verify( $url ) );
	}
}
