import React from 'react'
import PropTypes from 'prop-types'
import FontVariant from './FontVariant'
import AddFontFooter from './AddFontFooter'
import { sprintf } from 'sprintf-js'

export const AddFont = (
  {
    label,
    onHandleInputChange,
    onHandleUpload,
    onHandleDeleteFontStyle,
    onHandleSubmit,
    fontStyles,
    validateLabel,
    validateRegular,
    msg,
    loading,
    tabIndexFontName,
    tabIndexFontFiles,
    tabIndexFooterButtons
  }
) => {
  const fontNameLabel = sprintf(GFPDF.fontManagerFontNameLabel, "<span class='required'>", '</span>')

  return (
    <div className='add-font'>
      <form onSubmit={onHandleSubmit}>
        <h2>{GFPDF.fontManagerAddTitle}</h2>

        <p>{GFPDF.fontManagerAddDesc}</p>

        <label htmlFor='gfpdf-font-name-input' dangerouslySetInnerHTML={{ __html: fontNameLabel }} />

        <p id='gfpdf-font-name-desc'>{GFPDF.fontManagerFontNameDesc}</p>

        <input
          type='text'
          id='gfpdf-font-name-input'
          className={!validateLabel ? 'input-label-validation-error' : ''}
          aria-describedby='gfpdf-font-name-desc'
          name='label'
          value={label}
          maxLength='60'
          onChange={e => onHandleInputChange(e, 'addFont')}
          tabIndex={tabIndexFontName}
        />

        {!validateLabel && (
          <span className='required'>
            <em>{GFPDF.fontManagerFontNameValidationError}</em>
          </span>
        )}

        <label id='gfpdf-font-files-label'>{GFPDF.fontManagerFontFilesLabel}</label>

        <p id='gfpdf-font-files-description'>{GFPDF.fontManagerFontFilesDesc}</p>

        <FontVariant
          state='addFont'
          fontStyles={fontStyles}
          validateRegular={validateRegular}
          onHandleUpload={onHandleUpload}
          onHandleDeleteFontStyle={onHandleDeleteFontStyle}
          msg={msg}
          tabIndex={tabIndexFontFiles}
        />

        <AddFontFooter
          state='addFont'
          msg={msg}
          loading={loading}
          tabIndex={tabIndexFooterButtons}
        />
      </form>
    </div>
  )
}

AddFont.propTypes = {
  label: PropTypes.string.isRequired,
  onHandleInputChange: PropTypes.func.isRequired,
  onHandleUpload: PropTypes.func.isRequired,
  onHandleDeleteFontStyle: PropTypes.func.isRequired,
  onHandleSubmit: PropTypes.func.isRequired,
  validateLabel: PropTypes.bool.isRequired,
  validateRegular: PropTypes.bool.isRequired,
  fontStyles: PropTypes.object.isRequired,
  msg: PropTypes.object.isRequired,
  loading: PropTypes.bool.isRequired,
  tabIndexFontName: PropTypes.string.isRequired,
  tabIndexFontFiles: PropTypes.string.isRequired,
  tabIndexFooterButtons: PropTypes.string.isRequired
}

export default AddFont
