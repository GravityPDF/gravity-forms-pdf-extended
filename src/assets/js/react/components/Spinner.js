import React from 'react'

/**
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
 */

/**
 * Display a loading spinner
 *
 * @since 5.0
 */
const Spinner = () => (
  <img alt={GFPDF.spinnerAlt} src={GFPDF.spinnerUrl} className='gfpdf-spinner' />
)

export default Spinner
