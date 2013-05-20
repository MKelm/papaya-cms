/**
* Embed controls - javascript
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @module Papaya
* @submodule Administration
* @version $Id: controls.js 37575 2012-10-19 15:03:05Z weinert $
*/

/**
 * Initialization for the jQuery based extended dialog fields
 */
jQuery(document).ready(
  function() {
    jQuery.papayaDialogManager({}).setUp();
    jQuery('a.hintSwitch').papayaDialogHints({});
    jQuery('div.dialogCheckboxes').papayaDialogCheckboxes({});
    jQuery('input.dialogInputColor').papayaDialogFieldColor({});
    jQuery('input.dialogInputCounted,input.dialogInputCounter').papayaDialogFieldCounted({});
    jQuery('input.dialogInputDate').datepicker(
        {
          dateFormat: 'yy-mm-dd',
          changeYear: true,
          changeMonth: true
        }
      );
      jQuery('input.dialogInputDateTime').datetimepicker(
        {
          dateFormat: 'yy-mm-dd',
          timeFormat: 'hh:mm',
          changeYear: true,
          changeMonth: true,
          duration: false,
          showTime: true,
          constrainInput: false,
          stepMinutes: 1,
          stepHours: 1,
          altTimeField: '',
          time24h: true
        }
      );
    jQuery('input.dialogInputGeoPosition,input.dialogGeoPos').papayaDialogFieldGeoPosition({});
    jQuery('input.dialogInputPage,input.dialogPageId').papayaDialogFieldPage({});
    jQuery('input.dialogInputMediaFile,input.dialogMediaFile').papayaDialogFieldMediaFile({});
    jQuery('input.dialogInputMediaImage,input.dialogFixedImage').papayaDialogFieldImage({});
    jQuery('input.dialogInputMediaImageResized,input.dialogImage').papayaDialogFieldImageResized({});
    jQuery('select.dialogSelect')
      .filter(
        function() {
          return (
           ($(this).find('option').length > 10) &&
           !($(this).parents('form').is('.dialogXSmall'))
          );
        }
      )
      .papayaDialogFieldSelect({});
  }
);
