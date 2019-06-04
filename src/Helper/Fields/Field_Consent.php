<?php

namespace GFPDF\Helper\Fields;

use GFPDF\Helper\Helper_Abstract_Form;
use GFPDF\Helper\Helper_Misc;
use GFPDF\Helper\Helper_Abstract_Fields;

use GF_Field_Consent;

use Exception;

/**
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2019, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controls the display and output of a Gravity Form field
 *
 * @since 5.1
 */
class Field_Consent extends Helper_Abstract_Fields {

	/**
	 * Check the appropriate variables are parsed in send to the parent construct
	 *
	 * @param object                             $field The GF_Field_* Object
	 * @param array                              $entry The Gravity Forms Entry
	 *
	 * @param \GFPDF\Helper\Helper_Abstract_Form $gform
	 * @param \GFPDF\Helper\Helper_Misc          $misc
	 *
	 * @throws Exception
	 *
	 * @since 5.1
	 */
	public function __construct( $field, $entry, Helper_Abstract_Form $gform, Helper_Misc $misc ) {

		if ( ! is_object( $field ) || ! $field instanceof GF_Field_Consent ) {
			throw new Exception( '$field needs to be in instance of GF_Field_Consent' );
		}

		/* call our parent method */
		parent::__construct( $field, $entry, $gform, $misc );
	}

	/**
	 * Display the HTML version of this field
	 *
	 * @param string $value
	 * @param bool   $label
	 *
	 * @return string
	 *
	 * @since 5.1
	 */
	public function html( $value = '', $label = true ) {
		$value = $this->value();

		if ( empty( $value['value'] ) ) {
			$html = sprintf(
				'<span class="consent-tick consent-not-accepted" style="font-family:dejavusans;">&#10006;</span> <span class="consent-label consent-not-accepted-label">%s</span>',
				__( 'Consent not given.', 'gravity-forms-pdf-extended' )
			);
		} else {
			$html = sprintf(
				'<span class="consent-tick consent-accepted" style="font-family:dejavusans;">&#10004;</span> <span class="consent-label consent-accepted-label">%s</span>',
				$value['label']
			);
		}

		if ( strlen( $value['description'] ) > 0 ) {
			$html .= sprintf( '<div class="consent-text">%s</div>', $value['description'] );
		}

		return parent::html( $html );
	}

	/**
	 * Get the standard GF value of this field
	 *
	 * @return string|array
	 *
	 * @since 5.1
	 */
	public function value() {
		if ( $this->has_cache() ) {
			return $this->cache();
		}

		$value = array_values( $this->get_value() );

		$consent = [
			'value'       => $value[0],
			'label'       => esc_html( $value[1] ),
			'description' => wp_kses_post(
				wpautop(
					$this->gform->process_tags( $this->field->get_field_description_from_revision( $value[2] ), $this->form, $this->entry )
				)
			),
		];

		$this->cache( $consent );

		return $this->cache();
	}
}
