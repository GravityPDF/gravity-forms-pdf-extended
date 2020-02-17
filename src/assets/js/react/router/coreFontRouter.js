import React from 'react'
import PropTypes from 'prop-types'
import { HashRouter as Router, Route, Switch } from 'react-router-dom'
import CoreFontContainer from '../components/CoreFonts/CoreFontContainer'

/**
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
 */

/**
 * Contains the React Router Routes for our Core Font downloader.
 * We are using hashHistory instead of browserHistory so as not to affect the backend
 *
 * Routes include:
 *
 * /downloadCoreFonts
 * /retryDownloadCoreFonts
 *
 * @param button DOM Node containing the original static <button> markup (gets replaced by React)
 *
 * @since 5.0
 */
const Routes = ({ button }) => (
  <Router>
    <Switch>
      <Route render={(props) => <CoreFont history={props.history} button={button} />} />

      <Route
        path='/downloadCoreFonts'
        exact
        render={(props) => <CoreFont history={props.history} button={button} />} />

      <Route
        path='/retryDownloadCoreFonts'
        exact
        render={(props) => <CoreFont history={props.history} button={button} />} />
    </Switch>
  </Router>
)

/**
 * @since 5.0
 */
Routes.propTypes = {
  history: PropTypes.object,
  button: PropTypes.object
}

/**
 * Because we used the same component multiple times above, the real component was abstracted
 *
 * @param history HashHistory object
 * @param button DOM Node
 *
 * @since 5.0
 */
const CoreFont = ({ history, button }) => (
  <CoreFontContainer
    history={history}
    location={history.location}
    buttonClassName={button.className}
    buttonText={button.innerText}
    success={GFPDF.coreFontSuccess}
    error={GFPDF.coreFontError}
    itemPending={GFPDF.coreFontItemPendingMessage}
    itemSuccess={GFPDF.coreFontItemSuccessMessage}
    itemError={GFPDF.coreFontItemErrorMessage}
    counterText={GFPDF.coreFontCounter}
    retryText={GFPDF.coreFontRetry}
  />
)

/**
 * @since 5.0
 */
CoreFont.propTypes = {
  history: PropTypes.object,
  button: PropTypes.object
}

export default Routes
