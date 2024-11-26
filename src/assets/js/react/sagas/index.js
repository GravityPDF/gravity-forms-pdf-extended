/* Dependencies */
import { all } from 'redux-saga/effects';
/* Sagas */
import {
	watchUpdateSelectBox,
	watchTemplateProcessing,
	watchpostTemplateUploadProcessing,
} from './templates';
import { watchGetFilesFromGitHub, watchDownloadFonts } from './coreFonts';
import {
	watchGetCustomFontList,
	watchAddFont,
	watchEditFont,
	watchDeleteFont,
} from './fontManager';

/**
 * @package			Gravity PDF
 * @copyright   Copyright (c) 2024, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.2
 */

/**
 * Generator function that watch all the watcher sagas and run them in parallel
 *
 * @since 5.2
 */
export default function* rootSaga() {
	yield all([
		watchUpdateSelectBox(),
		watchTemplateProcessing(),
		watchpostTemplateUploadProcessing(),
		watchGetFilesFromGitHub(),
		watchDownloadFonts(),
		watchGetCustomFontList(),
		watchAddFont(),
		watchEditFont(),
		watchDeleteFont(),
	]);
}
