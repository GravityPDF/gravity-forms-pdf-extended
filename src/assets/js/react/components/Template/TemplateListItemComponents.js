import React from 'react'
import PropTypes from 'prop-types'

/**
 * Contains stateless React components for our Template List Items
 *
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.1
 */

/**
 * React Stateless Component
 *
 * Display the Template Details hover
 *
 * @param label (string)
 * @returns {*}
 *
 * @since 4.1
 */
export const TemplateDetails = ({ label }) => (
  <span className='more-details'>{label}</span>
)

TemplateDetails.propTypes = {
  label: PropTypes.string
}

/**
 * React Stateless Component
 *
 * Display the template group
 *
 * @param group (string)
 * @returns {*}
 *
 * @since 4.1
 */
export const Group = ({ group }) => (
  <p className='theme-author'>{group}</p>
)

Group.propTypes = {
  group: PropTypes.string
}
