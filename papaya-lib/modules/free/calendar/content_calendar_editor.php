<?php
/**
* Page module - Front-end editor for surfers
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
* @package Papaya-Modules
* @subpackage Free-Calendar
* @version $Id: content_calendar_editor.php
*/


/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - Front-end editor for surfers
*
* @package Papaya-Modules
* @subpackage Free-Calendar
*/
class content_calendar_editor extends base_content {

  var $paramName = "cae";

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'caption_page_title' =>
      array('Title of Page', 'isNoHTML', TRUE, 'input', 200, '', 'Title'),
    'default_module' =>
      array('Default module', 'isAlphaNum', TRUE, 'function', 'callbackModuleCombo'),
    'default_state' =>
      array('Default state', 'isNum', TRUE, 'combo',
        array(0 => 'none', 1 => 'created', 2 => 'published'), '', 1),
    'modify_published' =>
      array('Modify published dates', 'isNum', TRUE, 'combo',
        array(0 => 'no', 1 => 'yes'), '', 0),
    'text' =>
      array('Text', 'isSomeText', FALSE, 'textarea', 10),
    'time_mode' =>
      array('Time', 'isNum', TRUE, 'combo',
        array(0 => 'One free field', 1 => 'Two checked fields'),
        'Define if timefield is one field without inputcheck or two fields
         that have to contain the correct time.', 0),

    'Captions',
    'caption_add_date' =>
      array('Add date', 'isNoHTML', TRUE, 'input', 200, '', 'Add date'),
    'caption_edit_date' =>
      array('Edit date', 'isNoHTML', TRUE, 'input', 200, '', 'Edit date'),
    'caption_delete_date' =>
      array('Delete date', 'isNoHTML', TRUE, 'input', 200, '', 'Delete date'),
    'caption_delete_trans' =>
      array('Delete translation', 'isNoHTML', TRUE, 'input', 200, '',
        'Delete translation'),
    'caption_input_title' =>
      array('Title', 'isNoHTML', TRUE, 'input', 200, '', 'Title'),
    'caption_input_datetext' =>
      array('Date text', 'isNoHTML', TRUE, 'input', 200, '', 'Date text'),
    'caption_input_from' =>
      array('From', 'isNoHTML', TRUE, 'input', 200, '', 'From (ISO YYYY-MM-DD)'),
    'caption_input_to' =>
      array('To', 'isNoHTML', TRUE, 'input', 200, '', 'To (ISO YYYY-MM-DD)'),
    'caption_input_state' =>
      array('State', 'isNoHTML', TRUE, 'input', 200, '', 'State'),
    'caption_input_state_created' =>
      array('Created', 'isNoHTML', TRUE, 'input', 200, '', 'Created'),
    'caption_input_state_published' =>
      array('Published', 'isNoHTML', TRUE, 'input', 200, '', 'Published'),
    'caption_input_date_field_hour' =>
      array('Hour', 'isNoHTML', TRUE, 'input', 200, '', 'Hour'),
    'caption_input_date_field_minute' =>
      array('Minute', 'isNoHTML', TRUE, 'input', 200, '', 'Minute'),
    'caption_save_button' =>
      array('Save button', 'isNoHTML', TRUE, 'input', 200, '', 'Save'),

    'Messages',
    'message_save_datecontent' =>
      array('Content changed', 'isNoHTML', TRUE, 'input', 200, '', 'Content changed.'),
    'message_save_date' =>
      array('Date changed', 'isNoHTML', TRUE, 'input', 200, '', 'Date changed.'),
    'message_new_date' =>
      array('Date added', 'isNoHTML', TRUE, 'input', 200, '', 'Date added.'),
    'message_delete_date_question' =>
      array('Delete date question', 'isNoHTML', TRUE, 'input', 200, '',
        'Do you want to delete the date?'),
    'message_delete_date' =>
      array('Date deleted', 'isNoHTML', TRUE, 'input', 200, '', 'Date deleted.'),
    'message_delete_datetrans_question' =>
      array('Delete date translation question', 'isNoHTML', TRUE, 'input', 200, '',
        'Do you want to delete the date?'),
    'message_delete_datetrans' =>
      array('Translation deleted', 'isNoHTML', TRUE, 'input', 200, '',
        'Translation deleted.'),

    'Errors',
    'error_save_datecontent' =>
      array('Content not changed', 'isNoHTML', TRUE, 'input', 200, '',
        'Content not changed.'),
    'error_save_date' =>
      array('Date not changed', 'isNoHTML', TRUE, 'input', 200, '',
        'Date not changed.'),
    'error_new_date' =>
      array('Date not added', 'isNoHTML', TRUE, 'input', 200, '',
        'Date not added.'),
    'error_delete_date' =>
      array('Date not deleted', 'isNoHTML', TRUE, 'input', 200, '', 'Date not deleted.'),
    'error_delete_datetrans' =>
      array('Translation not deleted', 'isNoHTML', TRUE, 'input', 200, '',
        'Translation not deleted.'),
    'error_check_inputs' =>
      array('Input error', 'isNoHTML', TRUE, 'input', 200, '',
        'Please check your inputs.'),
    'error_fromto_fields' =>
      array('From-to error', 'isNoHTML', TRUE, 'input', 200, '',
        'Error(s) in from-to fields.'),

    'Mandatory fields',
    'mf_title' =>
      array('Title', 'isNum', TRUE, 'combo', array(0 => 'no', 1 => 'yes'), '', 1),
    'mf_startf' =>
      array('Start', 'isNum', TRUE, 'combo', array(0 => 'no', 1 => 'yes'), '', 1),
    'mf_endf' =>
      array('End', 'isNum', TRUE, 'combo', array(0 => 'no', 1 => 'yes'), '', 1),
    'mf_text' => array(
      'Date text / Time fields', 'isNum', TRUE, 'combo',
      array(0 => 'no', 1 => 'yes'),
      'Defines if the "date text" field or the fields "hour" and "minute" are mandatory fields.',
      0
    )
  );

  /**
  * Callback function to generate combo for existing calendar modules
  *
  * @return string $result xml
  */
  function callbackModuleCombo($name, $element, $data) {
    // needs module list from base_calendar through output_calendar
    include_once(dirname(__FILE__).'/output_calendar.php');
    $output = new output_calendar;
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    // loads and list modules
    if (isset($output) && is_object($output) && is_a($output, 'output_calendar')) {
      $output->loadModulesList();
      if (isset($output->contentModules) && is_array($output->contentModules)) {
        $result .= sprintf(
          '<option value="0">[%s]</option>',
          papaya_strings::escapeHTMLChars($this->_gt('Please select'))
        );
        foreach ($output->contentModules as $moduleGuid => $module) {
          $selected = ($moduleGuid == $data) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%s" %s>%s</option>',
            papaya_strings::escapeHTMLChars($moduleGuid),
            $selected,
            papaya_strings::escapeHTMLChars($module['module_title'])
          );
        }
      }
    }
    $result .= '</select>';
    return $result;
  }

  /**
  * Gets parsed data with text and calendar output by output_calendar
  *
  * @access public
  * @return string output
  */
  function getParsedData() {
    $result = '';
    // calendar output object
    include_once(dirname(__FILE__).'/output_calendar.php');
    $output = new output_calendar();
    $output->currentLanguageId = $this->parentObj->getContentLanguageId();
    $output->initialize($this);
    $output->execute();
    // text xml output
    $captionPageTitle = (isset($this->data['caption_page_title']))?
      $this->data['caption_page_title'] : '';
    $result .= sprintf(
      '<title>%s</title>',
      papaya_strings::escapeHTMLChars($captionPageTitle)
    );
    // text xml output
    $text = (isset($this->data['text']))? $this->data['text'] : '';
    $result .= sprintf(
      '<text>%s</text>',
      $this->getXHTMLString($text, TRUE)
    );
    // calendar xml output
    $result .= $output->getXML();
    return $result;
  }

}
?>