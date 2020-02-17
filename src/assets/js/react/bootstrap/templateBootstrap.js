import React, { lazy, Suspense } from 'react'
import { render } from 'react-dom'
import { HashRouter as Router, Route } from 'react-router-dom'
import watch from 'redux-watch'
import { getStore } from '../store'
import { selectTemplate, updateSelectBox } from '../actions/templates'
import templateRouter from '../router/templateRouter'

const TemplateButton = lazy(() => import('../components/Template/TemplateButton'))

/**
 * Advanced Template Selector Bootstrap
 *
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.1
 */

/**
 * Handles the loading of our Fancy Template Selector
 *
 * @param {Object} $templateField The jQuery select box we should attach the fancy template selector to
 *
 * @since 4.1
 */
export default function templateBootstrap ($templateField) {

  const store = getStore()

  /* Create our button container and render our component in it */
  createTemplateMarkup($templateField)

  /* Render our React Component in the DOM */
  render(
    <Suspense fallback={<div>Loading...</div>}>
      <Router>
        <Route render={(props) => <TemplateButton {...props} store={store} buttonText={GFPDF.advanced} />} />
      </Router>
    </Suspense>,
    document.getElementById('gpdf-advance-template-selector')
  )

  /* Mount our router */
  templateRouter(store)

  /*
   * Listen for Redux store updates and do DOM updates
   */
  activeTemplateStoreListener(store, $templateField)
  templateChangeStoreListener(store, $templateField)
}

/**
 * Dynamically add the required markup to attach our React components to.
 *
 * @param {Object} $templateField The jQuery select box we should attach the fancy template selector to
 *
 * @since 4.1
 */
export function createTemplateMarkup ($templateField) {
  $templateField
    .next()
    .after('<span id="gpdf-advance-template-selector">')
    .next()
    .after('<div id="gfpdf-overlay" class="theme-overlay">')
}

/**
 * Listen for updates to the template.activeTemplate data in our Redux store
 * and update the select box value based on this change. Also, listen for changes
 * to our select box and update the store when needed.
 *
 * @param {Object} store The Redux store returned from createStore()
 * @param {Object} $templateField The jQuery select box we should attach the fancy template selector to
 *
 * @since 4.1
 */
export function activeTemplateStoreListener (store, $templateField) {

  /* Watch our store for changes */
  let w = watch(store.getState, 'template.activeTemplate')
  store.subscribe(w((template) => {

    /* Check store and DOM are different to prevent any update recursions */
    if ($templateField.val() !== template) {
      $templateField
        .val(template)
        .trigger('chosen:updated')
        .trigger('change')
    }
  }))

  /* Watch our DOM for changes */
  $templateField.change(function () {
    /* Check store and DOM are different to prevent any update recursions */
    if (this.value !== store.getState().template.activeTemplate) {
      store.dispatch(selectTemplate(this.value))
    }
  })
}

/**
 * PHP builds the Select box DOM for the templates and when we add or delete a template we need to rebuild this.
 * Instead of duplicating the code on both server and client side we do an AJAX call to get the new Selex box HTML when
 * the template.list length changes and update the DOM accordingly.
 *
 * @param {Object} store The Redux store returned from createStore()
 * @param {Object} $templateField The jQuery select box we should attach the fancy template selector to
 *
 * @since 4.1
 */
export function templateChangeStoreListener (store, $templateField) {

  /* Track the initial list size */
  let listCount = store.getState().template.list.length

  /* Watch our store for changes */
  let w = watch(store.getState, 'template.list')
  store.subscribe(w((list) => {

    /* Only update if the list size differs from what we expect */
    if (listCount !== list.length) {
      /* update the list size so we don't run it twice */
      listCount = list.length
      let currentActive = $templateField.val()

      /* Dispatch Redux Action for an AJAX call to get the new Select Box DOM */
      store.dispatch(updateSelectBox())

      /* Watch our store for changes */
      let watchSelectBoxText = watch(store.getState, 'template.updateSelectBoxText')
      store.subscribe(watchSelectBoxText((updateSelectBoxText) => {

        /* Update $templateField */
        $templateField
          .html(updateSelectBoxText)
          .val(currentActive)
          .trigger('chosen:updated')
      }))
    }
  }))
}
