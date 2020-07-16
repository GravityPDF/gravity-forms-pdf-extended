import $ from 'jquery'
import { wpDialog } from '../../../helper/wpDialog'
import { resizeDialogIfNeeded } from '../../../helper/resizeDialogIfNeeded'

/**
 * Handles the Uninstall Dialog Box
 * @return void
 * @since 4.0
 */
export function setupToolsUninstallDialog () {
  const $uninstall = $('#gfpdf_settings\\[uninstaller\\]')
  const $uninstallDialog = $('#uninstall-confirm')

  /* Set up uninstall dialog */
  const uninstallButtons = [{
    text: GFPDF.uninstall,
    click: function () {
      /* submit form */
      $uninstall.parents('form').submit()
    }
  }, {
    text: GFPDF.cancel,
    click: function () {
      /* cancel */
      $uninstallDialog.wpdialog('close')
    }
  }]

  wpDialog($uninstallDialog, uninstallButtons, 500, 175)

  $uninstall.click(function () {
    /* Allow responsiveness */
    resizeDialogIfNeeded($uninstallDialog, 500, 175)

    $uninstallDialog.wpdialog('open')
    return false
  })
}
