<?php
/**
* Action box - monthly calendar with tag filter.
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: actbox_calendar_tag.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box - monthly calendar
*
* @package Papaya-Modules
* @subpackage Free-Calendar
*/
class actionbox_calendar_tag extends base_actionbox {

  /**
  * Preview possible
  * @var boolean $preview
  */
  var $preview = TRUE;
  /**
  * Modification status
  * @var boolean $modified
  */
  var $modified = NULL;
  /**
  * Tag selector
  * @var object $tagSelector papaya_tagselector
  */
  var $tagSelector = FALSE;
  /**
  * lngSelect
  * @var array $lngSelect
  */
  var $lngSelect = NULL;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'filter_tag' => array('Show tagged ones only', 'isNum', TRUE, 'combo',
      array('1' => 'yes', '0' => 'no')),
    'tag_id' => array('Tag', 'isNum', TRUE, 'disabled_function',
      'callbackSelectedTag', 'Go to "Tag Selection" to modify.'),
    'file' => array ('URL', 'isNoHTML', TRUE, 'pageid', 800,
      'Please input a relative or an absolute URL.'),
    'add_months' => array ('Months to add', 'isNum', FALSE, 'input', 3, '', '0'),
    'add_days' => array ('Days to add', 'isNum', FALSE, 'input', 3, '', '0'),
  );

  /**
  * Callback method for edit field, shows selected tag
  *
  * @param string $name
  * @param $element
  * @param array $data
  * @access public
  * @return string as xml
  */
  function callbackSelectedTag($name, $element, $data) {
    $tag = $this->tagSelector->getTag($data, $this->lngSelect->currentLanguageId);
    return sprintf(
      '<input type="text" name="%s[%s]" '.
      'class="dialogInput dialogScale" value="%s" disabled="disabled"></input>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name),
      papaya_strings::escapeHTMLChars(@$tag['tag_title'])
    );
  }

  /**
  * Initializes dialog
  *
  * @access public
  */
  function initializeDialog() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $this->lngSelect = &base_language_select::getInstance();

    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_tagselector.php');
    $this->tagSelector = papaya_tagselector::getInstance($this);

    $this->sessionParamName = 'PAPAYA_SESS_'.get_class($this).'_'.$this->paramName;
    $this->initializeParams();

    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('contentmode');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);

    switch ($this->params['contentmode']) {
    case 1:
      if (isset($this->tagSelector) && is_object($this->tagSelector)
          && get_class($this->tagSelector) == 'papaya_tagselector') {
        $this->tagSelectorForm = $this->tagSelector->getTagSelector(
          array($this->data['tag_id']), 'single');
        $selectedTags = $this->tagSelector->getSelectedTags();
        if (current($selectedTags) != $this->data['tag_id']) {
          $this->data['tag_id'] = current($selectedTags);
          $this->modified = TRUE;
        }
      }
      break;
    default:
      parent::initializeDialog();
    }
  }

  /**
  * Get administration form xml depending on current content mode
  */
  function getForm() {
    $result = '';
    $result .= $this->getContentToolbar();
    switch ($this->params['contentmode']) {
    case 1:
      $result .= $this->tagSelectorForm;
      break;
    default:
      $result .= parent::getForm();
    }
    return $result;
  }

  /**
  * Get content toolbar xml
  *
  * @access public
  * @return string as xml
  */
  function getContentToolbar() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$GLOBALS['PAPAYA_IMAGES'];

    $toolbar->addButton(
      'General',
      $this->getLink(array('contentmode' => 0)),
      'items-option',
      '',
      $this->params['contentmode'] == 0
    );
    $toolbar->addButton(
      'Tag Selection',
      $this->getLink(array('contentmode' => 1)),
      'items-tag',
      '',
      $this->params['contentmode'] == 1
    );
    $toolbar->addSeperator();

    if ($str = $toolbar->getXML()) {
      return '<toolbar>'.$str.'</toolbar>';
    }
    return '';
  }

  /**
  * Checks data
  *
  * @access public
  * @return boolean
  */
  function checkData() {
    switch(@$this->params['contentmode']) {
    case 1:
      return TRUE;
    default:
      return parent::checkData();
    }
    return FALSE;
  }

  /**
  * Returns modification status
  *
  * @access public
  * @return boolean modified
  */
  function modified() {
    if (@$this->params['contentmode'] > 0) {
      return $this->modified;
    } else {
      return parent::modified();
    }
  }

  /**
  * Gets time and add days / months
  *
  * @access public
  * @return integer time stamp
  */
  function getTime() {
    $time = time() + @(int)$this->data['add_days'] * 60 * 60 * 24;
    $date = getdate($time);
    $date['mon'] = $date['mon'] + @(int)$this->data['add_months'];
    if ($date['mon'] > 12) {
      $addMonths = (int)$this->data['add_months'] % 12;
      $date['mon'] = $date['mon'] - $addMonths;
      $addYears = (int)(@(int)$this->data['add_months'] / 12);
      $date['year'] = $date['year'] + $addYears;
    }
    return mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
  }

  /**
  * Get parsed data - page xml
  *
  * @access public
  * @return string $result
  */
  function getParsedData() {
    $result = '';
    include_once(dirname(__FILE__)."/base_calendar.php");
    $calendar = new base_calendar;
    $calendar->currentLanguageId = $this->parentObj->getContentLanguageId();
    $calendar->initialize();
    $calendar->execute();
    $calendar->baseLink = $this->getAbsoluteURL(@$this->data['file']);
    if (!isset($calendar->params['time'])) {
      $calendar->selectedDate = $calendar->parseTimeToArray($this->getTime());
    }

    if (isset($this->data['tag_id']) && $this->data['tag_id'] > 0) {
      $result .= $calendar->showMonthTable(
        @(int)$this->data['tag_id'] *
        @(int)$this->data['filter_tag']
      );
    } else {
      $result .= $calendar->showMonthTable();
    }
    return $result;
  }
}
?>