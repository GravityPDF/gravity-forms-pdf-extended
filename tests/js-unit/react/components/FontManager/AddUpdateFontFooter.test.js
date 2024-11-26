import React from 'react';
import { shallow } from 'enzyme';
import { findByTestAttr } from '../../testUtils';
import { AddUpdateFontFooter } from '../../../../../src/assets/js/react/components/FontManager/AddUpdateFontFooter';

describe('FontManager - AddFontFooter.js', () => {
	// Mock component props
	const props = {
		selectedFont: 'roboto',
		selectFont: jest.fn(),
		deleteFont: jest.fn(),
		msg: {
			success: { addFont: 'success' },
			error: { addFont: 'error' },
		},
		loading: true,
		tabIndex: '148',
	};

	describe('RUN COMPONENT METHODS', () => {
		test('handleSelectFont() - Handle the functionality to select and set default font type to be used in PDFs', () => {
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			const instance = wrapper.instance();
			instance.handleSelectFont();

			expect(props.selectFont).toHaveBeenCalledTimes(1);
		});

		test('handleSelectFontKeypress() - Handle the functionality to select and set default font type to be used in PDFs (Keyboard press (true))', () => {
			const e = { key: 'Enter' };
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			const instance = wrapper.instance();
			instance.handleSelectFontKeypress(e, 'roboto', 'roboto');

			expect(props.selectFont).toHaveBeenCalledTimes(1);
		});

		test('handleSelectFontKeypress() - Handle the functionality to select and set default font type to be used in PDFs (Keyboard press (false))', () => {
			const e = { keyCode: 12 };
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			const instance = wrapper.instance();
			instance.handleSelectFontKeypress(e, 'roboto', 'roboto');

			expect(props.selectFont).toHaveBeenCalledTimes(0);
		});

		test('handleDeleteFont() - Handle request of font deletion (true)', () => {
			global.confirm = () => true;
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			const instance = wrapper.instance();
			instance.handleDeleteFont('arial');

			expect(props.deleteFont).toHaveBeenCalledTimes(1);
		});

		test('handleDeleteFont() - Handle request of font deletion (false)', () => {
			global.confirm = () => false;
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			const instance = wrapper.instance();
			instance.handleDeleteFont('arial');

			expect(props.deleteFont).toHaveBeenCalledTimes(0);
		});

		test('handleDeleteFontKeypress() - Handle request of font deletion (Keyboard press (true))', () => {
			global.confirm = () => true;
			const e = { key: 'Enter' };
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			const instance = wrapper.instance();
			instance.handleDeleteFontKeypress(e, 'arial');

			expect(props.deleteFont).toHaveBeenCalledTimes(1);
		});

		test('handleDeleteFontKeypress() - Handle request of font deletion (Keyboard press (false))', () => {
			global.confirm = () => false;
			const e = { keyCode: 12 };
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			const instance = wrapper.instance();
			instance.handleDeleteFontKeypress(e, 'arial');

			expect(props.deleteFont).toHaveBeenCalledTimes(0);
		});
	});

	describe('RENDERS COMPONENT', () => {
		test('render <AddFontFooter /> component', () => {
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			const component = findByTestAttr(
				wrapper,
				'component-AddFontFooter'
			);

			expect(component.length).toBe(1);
		});

		test('render cancel button', () => {
			const wrapper = shallow(
				<AddUpdateFontFooter {...props} id="active" />
			);

			expect(wrapper.find('button.cancel').length).toBe(1);
		});

		test('render add font button', () => {
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			expect(wrapper.find('button').at(0).text()).toBe('Add Font →');
		});

		test('render update font button', () => {
			const wrapper = shallow(
				<AddUpdateFontFooter {...props} id="active" />
			);

			expect(wrapper.find('button').at(1).text()).toBe('Update Font →');
		});

		test('render update panel select font checkbox', () => {
			const wrapper = shallow(
				<AddUpdateFontFooter {...props} id="roboto" />
			);

			expect(wrapper.find('button.dashicons-yes').length).toBe(1);
		});

		test('render update panel delete icon', () => {
			const wrapper = shallow(
				<AddUpdateFontFooter {...props} id="roboto" />
			);

			expect(wrapper.find('button.dashicons-trash').length).toBe(1);
		});

		test('render loading spinner', () => {
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			expect(wrapper.find('Spinner').length).toBe(1);
		});

		test('render success message', () => {
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			expect(wrapper.find('span.success').length).toBe(1);
		});

		test('render error message', () => {
			const wrapper = shallow(<AddUpdateFontFooter {...props} />);
			expect(wrapper.find('span.error').length).toBe(1);
		});
	});
});
