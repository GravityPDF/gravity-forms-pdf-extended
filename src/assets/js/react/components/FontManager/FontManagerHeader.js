/* Dependencies */
import React from 'react';
import PropTypes from 'prop-types';
/* Components */
import CloseDialog from '../Modal/CloseDialog';
/* Helpers */
import withRouterHooks from '../../utilities/withRouterHooks';

/**
 * @package			Gravity PDF
 * @copyright   Copyright (c) 2024, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       6.0
 */

const CloseDialogWithRouter = withRouterHooks(CloseDialog);

/**
 * Display the header of font manager  UI
 *
 * @param { Object } props
 * @param { string } props.id
 *
 * @since 6.0
 */
const FontManagerHeader = ({ id }) => (
	<div data-test="component-FontManagerHeader" className="theme-header">
		<h1>Font Manager</h1>

		<CloseDialogWithRouter id={id} />
	</div>
);

/**
 * PropTypes
 *
 * @since 6.0
 */
FontManagerHeader.propTypes = {
	id: PropTypes.string,
};

export default FontManagerHeader;
