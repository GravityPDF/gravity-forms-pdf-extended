import React from 'react'
import { mount } from 'enzyme'
import configureStore from 'redux-mock-store'
import { Provider } from 'react-redux'

const mockStore = configureStore()
import { HashRouter as Router } from 'react-router-dom'

import TemplateHeaderNavigation from '../../../../../src/assets/js/react/components/Template/TemplateHeaderNavigation'

describe('<TemplateHeaderNavigation />', () => {

  it('should render two buttons with correct classes and text', () => {
    const comp = mount(<Router>
      <Provider store={mockStore()}>
        <TemplateHeaderNavigation
          templates={[{}, {}]}
          template={{}}
          templateIndex={0}
          showPreviousTemplateText="Show previous template"
          showNextTemplateText="Show next template"
        />
      </Provider>
    </Router>)

    expect(comp.find('button.left')).to.have.length(1)
    expect(comp.find('button.right')).to.have.length(1)

    expect(comp.find('button.left').hasClass('dashicons-no')).to.be.true
    expect(comp.find('button.right').hasClass('dashicons-no')).to.be.true

    expect(comp.find('button.left span').text()).to.equal('Show previous template')
    expect(comp.find('button.right span').text()).to.equal('Show next template')
  })

  it('should disable left button', () => {
    const comp = mount(<Router>
      <Provider store={mockStore()}>
        <TemplateHeaderNavigation
          templates={[{id: 'first-id'}, {id: 'middle-id'}, {id: 'last-id'}]}
          template={{id: 'first-id'}}
          templateIndex={0}
        />
      </Provider>
    </Router>)

    expect(comp.find('button.left.disabled')).to.have.length(1)
    expect(comp.find('button.right.disabled')).to.have.length(0)

    expect(comp.render().find('button.left').attr('disabled')).to.equal('disabled')
    expect(comp.render().find('button.right').attr('disabled')).to.not.equal('disabled')
  })

  it('should disable right button', () => {
    const comp = mount(<Router>
      <Provider store={mockStore()}>
        <TemplateHeaderNavigation
          templates={[{id: 'first-id'}, {id: 'middle-id'}, {id: 'last-id'}]}
          template={{id: 'last-id'}}
          templateIndex={2}
        />
      </Provider>
    </Router>)

    expect(comp.find('button.left.disabled')).to.have.length(0)
    expect(comp.find('button.right.disabled')).to.have.length(1)

    expect(comp.render().find('button.left').attr('disabled')).to.not.equal('disabled')
    expect(comp.render().find('button.right').attr('disabled')).to.equal('disabled')
  })

  it('both buttons should NOT be disabled', () => {
    const comp = mount(<Router>
      <Provider store={mockStore()}>
        <TemplateHeaderNavigation
          templates={[{id: 'first-id'}, {id: 'middle-id'}, {id: 'last-id'}]}
          template={{id: 'middle-id'}}
          templateIndex={1}
        />
      </Provider>
    </Router>)

    expect(comp.find('button.left.disabled')).to.have.length(0)
    expect(comp.find('button.right.disabled')).to.have.length(0)

    expect(comp.render().find('button.left').attr('disabled')).to.not.equal('disabled')
    expect(comp.render().find('button.right').attr('disabled')).to.not.equal('disabled')
  })

  it('when left or right arrows pressed the route gets updated', () => {
    const comp = mount(<Router>
      <Provider store={mockStore()}>
        <TemplateHeaderNavigation
          templates={[{id: 'first-id'}, {id: 'middle-id'}, {id: 'last-id'}]}
          template={{id: 'middle-id'}}
          templateIndex={1}/>
      </Provider>
    </Router>)

    comp.find('button.left').simulate('keydown', {key: 'ArrowLeft', keyCode: 37})
    expect(window.location.hash).to.equal('#/template/first-id')

    comp.find('button.right').simulate('keydown', {key: 'ArrowRight', keyCode: 39})
    expect(window.location.hash).to.equal('#/template/last-id')

  })

})
