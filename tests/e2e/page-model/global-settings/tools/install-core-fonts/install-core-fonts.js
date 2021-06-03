import { Selector, t } from 'testcafe'
import { button } from '../../../helpers/field'
import { admin, baseURL } from '../../../../auth'

class InsallCoreFonts {
  constructor () {
    this.sectionTable = Selector('#pdf-tools')
    this.downloadFailed = Selector('.gfpdf-core-font-status-error')
    this.retryDownload = Selector('a').withText('Retry Failed Downloads?')
    this.downloadButton = button('Download Core Fonts')
    this.pendingResult = Selector('.gfpdf-core-font-status-pending')
    this.downloadSuccess = Selector('.gfpdf-core-font-status-success')
    this.allSuccessfullyIntalled = Selector('.gfpdf-core-font-status-success').withText('ALL CORE FONTS SUCCESSFULLY INSTALLED')
  }

  async navigateSettingsTab (text) {
    await t
      .setNativeDialogHandler(() => true)
      .useRole(admin)
      .navigateTo(`${baseURL}/wp-admin/admin.php?page=${text}`)
      .click(this.sectionTable)
      .click(this.downloadButton, { speed: 1 })
  }
}

export default InsallCoreFonts
