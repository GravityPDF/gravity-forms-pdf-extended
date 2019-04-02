import React from 'react'
import { mount } from 'enzyme'
import configureStore from 'redux-mock-store'
import { Provider } from 'react-redux'
import { HashRouter as Router } from 'react-router-dom'

const mockStore = configureStore()

import TemplateFooterActions from '../../../../../src/assets/js/react/components/Template/TemplateFooterActions'

describe('<TemplateFooterActions />', () => {

  it('should render a button', () => {
    const comp = mount(<Router>
      <Provider store={mockStore()}>
        <TemplateFooterActions template={{path: '/my/test/path', compatible: true}}/>
      </Provider>
    </Router>)

    expect(comp.find('div.theme-actions')).to.have.length(1)
    expect(comp.find('a.button')).to.have.length(1)
  })

})
