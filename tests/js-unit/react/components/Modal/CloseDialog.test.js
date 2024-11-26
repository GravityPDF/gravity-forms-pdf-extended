import React from 'react';
import { shallow } from 'enzyme';
import { findByTestAttr } from '../../testUtils';
import { CloseDialog } from '../../../../../src/assets/js/react/components/Modal/CloseDialog';
import * as utilitiesA from '../../../../../src/assets/js/react/utilities/FontManager/associatedFontManagerSelectBox';
import * as utilitiesB from '../../../../../src/assets/js/react/utilities/FontManager/toggleUpdateFont';

describe('CloseDialog - CloseDialog.js', () => {
	// Mock component props data
	const props = {
		getCustomFontList: jest.fn(),
		clearAddFontMsg: jest.fn(),
		templateList: [{}],
		fontList: [{}],
		selectedFont: '',
		msg: { success: {}, error: {} },
		navigate: jest.fn(),
		pathname: '/fontmanager/',
	};

	describe('RUN LIFECYCLE METHODS', () => {
		test('componentDidMount() - Assign keydown listener to document on mount', () => {
			const map = {};

			document.addEventListener = jest.fn((event, cb) => {
				map[event] = cb;
			});

			const wrapper = shallow(<CloseDialog {...props} />);
			const instance = wrapper.instance();

			const handleKeyPress = jest.spyOn(
				wrapper.instance(),
				'handleKeyPress'
			);

			instance.componentDidMount();
			// simulate event
			map.keydown({
				keyCode: 27,
				target: { className: '', value: '' },
			});

			expect(handleKeyPress).toHaveBeenCalledTimes(1);
		});

		test('componentDidUpdate() - Trigger a request of updated font manager select box', () => {
			const prevProps = { templateList: [{}, {}] };

			const wrapper = shallow(<CloseDialog {...props} />);
			const instance = wrapper.instance();

			instance.componentDidUpdate(prevProps);

			expect(props.getCustomFontList).toHaveBeenCalledTimes(1);
		});

		test('componentWillUnmount() - Remove keydown listener to document on mount', () => {
			// Mock font manager select box DOM
			document.body.innerHTML =
				'<div class="gfpdf-font-manager">' +
				' <select class="gfpdf_settings_default_font " name="gfpdf_settings[default_font]">' +
				'   <optgroup label="User-Defined Fonts">' +
				'     <option value="z" />' +
				'     <option value="c" />' +
				'   </optgroup>' +
				' </select>' +
				'</div>';

			const map = {};

			document.removeEventListener = jest.fn((event, cb) => {
				map[event] = cb;
			});

			const wrapper = shallow(<CloseDialog {...props} />);
			const instance = wrapper.instance();

			const handleKeyPress = jest.spyOn(
				wrapper.instance(),
				'handleKeyPress'
			);
			const associatedFontManagerSelectBox = jest.spyOn(
				utilitiesA,
				'associatedFontManagerSelectBox'
			);

			instance.componentWillUnmount();
			// simulate event
			map.keydown({
				keyCode: 27,
				target: { className: '', value: '' },
			});

			expect(handleKeyPress).toHaveBeenCalledTimes(1);
			expect(associatedFontManagerSelectBox).toHaveBeenCalledTimes(1);
		});
	});

	describe('RUN COMPONENT METHODS', () => {
		test("handleKeyPress() - Close font manager 'Update Font' panel first", () => {
			// Mock update font panel DOM
			document.body.innerHTML =
				'<div class="update-font show">' + '</div>';

			const msg = { success: { addFont: {} }, error: {} };
			const wrapper = shallow(
				<CloseDialog {...props} id="yes" msg={msg} />
			);
			const instance = wrapper.instance();
			const toggleUpdateFont = jest.spyOn(utilitiesB, 'toggleUpdateFont');
			const e = { keyCode: 27 };

			instance.handleKeyPress(e);

			expect(props.clearAddFontMsg).toHaveBeenCalledTimes(1);
			expect(toggleUpdateFont).toHaveBeenCalledTimes(1);
		});

		test('handleKeyPress() - Close modal', () => {
			const wrapper = shallow(<CloseDialog {...props} />);
			const instance = wrapper.instance();

			const handleCloseDialog = jest.spyOn(
				wrapper.instance(),
				'handleCloseDialog'
			);
			const e = { keyCode: 27, target: { className: '', value: '' } };
			instance.handleKeyPress(e);

			expect(handleCloseDialog).toHaveBeenCalledTimes(1);
			expect(props.navigate.mock.calls.length).toBe(1);
		});

		test('handleCloseDialog() - trigger router', () => {
			const wrapper = shallow(<CloseDialog {...props} />);
			const instance = wrapper.instance();

			instance.handleCloseDialog();

			expect(props.navigate.mock.calls.length).toBe(1);
		});
	});

	describe('RENDERS COMPONENT', () => {
		test('render <CloseDialog /> component', () => {
			const wrapper = shallow(<CloseDialog {...props} />);

			const component = findByTestAttr(wrapper, 'component-CloseDialog');

			expect(component.length).toBe(1);
		});

		test('render button screen reader text', () => {
			const wrapper = shallow(<CloseDialog {...props} />);
			expect(wrapper.find('span').text()).toBe('Close dialog');
		});

		test('check button click', () => {
			const wrapper = shallow(<CloseDialog {...props} />);
			wrapper.simulate('click');

			expect(props.navigate.mock.calls.length).toBe(1);
		});
	});
});
