<?php
/**
* Action box - next dates in calendar
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
* @version $Id: actbox_nextdates_tag.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');
/**
* Action box - next dates in calendar with tag filter.
*
* @package Papaya-Modules
* @subpackage Free-Calendar
*/
class actionbox_nextdates_tag extends base_actionbox {

  /**
  * Preview possible
  * @var boolean $preview
  */
  var $preview = TRUE;
  /**
  * Modification status
  * @var boolean $modified
  */
  var $modified = FALSE;
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
  * edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'filter_tag' => array('Show only entries having this tag', 'isNum', TRUE,
      'combo', array('0' => 'no', '1' => 'yes')),
    'tag_id' => array('Tag', 'isNum', TRUE, 'disabled_function',
      'callbackSelectedTag', 'Go to "Tag Selection" to modify.'),
    'file' => array ('URL', 'isNoHTML', TRUE, 'pageid', 100,
      'Please input a relative or an absolute URL.'),
    'count' => array ('Date count', 'isNum', TRUE, 'input', 4, '', 1),
    'add_days' => array ('Additional days', 'isNum', TRUE, 'input', 2, '', '0')
  );

  /**
  * Callback method for selected tag field
  *
  * @param string $name name of tag
  * @param $element
  * @param $data
  * @access public
  * @return string $result as xml
  */
  function callbackSelectedTag($name, $element, $data) {
    $result = '';
    $tag = $this->tagSelector->getTag($data, $this->lngSelect->currentLanguageId);
    $result .= sprintf(
      '<input type="text" name="%s[%s]" class="dialogInput dialogScale"'.
      ' value="%s" disabled="disabled"></input>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name),
      papaya_strings::escapeHTMLChars(
        empty($tag['tag_title']) ? '' : $tag['tag_title']
      )
    );
    return $result;
  }

  /**
  * Initializes backend dialog
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
  * Gets backend form
  *
  * @access public
  * @return string $result as xml
  */
  function getForm() {
    $result = $this->getFormToolbar();
    switch ($this->params['contentmode']) {
    case 1:
      $result .= $this->tagSelectorForm;
      break;
    default:
      $result .= parent::getForm();
      break;
    }
    return $result;
  }

  /**
  * Gets toolbar for backend form
  *
  * @access public
  * @return string as xml
  */
  function getFormToolbar() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$GLOBALS['PAPAYA_IMAGES'];

    $toolbar->addButton(
      'General',
      $this->getLink(
        array('contentmode' => 0)
      ),
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
  * Modified status
  *
  * @access public
  * @return boolean
  */
  function modified() {
    if (@$this->params['contentmode'] > 0) {
      return $this->modified;
    } else {
      return parent::modified();
    }
  }

  /**
  * Returns headstring for dates, one date/time frame and sets from/to time
  *
  * @param object $calendar base_calendar
  * @param integer $minTime time minimum
  * @access public
  * @return string head string
  */
  function setTimeData(&$calendar, $minTime) {
    $fromTime = $minTime;
    $toTime = 0;
    // gets from / to time span
    if (isset($calendar->dates) && is_array($calendar->dates)) {
      foreach ($calendar->dates as $date) {
        if (($date['date_start'] > $fromTime) &&
            ($fromTime == $minTime)) {
          $fromTime = $date['date_start'];
        }
        if ($toTime < $date['date_end']) {
          $toTime = $date['date_end'];
        }
      }
    }
    // time strings and date from / date to for later xml output in base_calendar
    $startTimeString = date($this->_gt($calendar->tplDateStr), $fromTime);
    $endTimeString = date($this->_gt($calendar->tplDateStr), $toTime);
    $calendar->dateFrom = $calendar->parseTimeToArray($fromTime);
    $calendar->dateTo = $calendar->parseTimeToArray($toTime);
    // returns head string, depends on time strings
    if ($startTimeString != $endTimeString) {
      return $startTimeString .' - '.$endTimeString;
    } else {
      return $startTimeString;
    }
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result as xml
  */
  function getParsedData() {
    $result = '';
    include_once(dirname(__FILE__)."/base_calendar.php");
    $calendar = new base_calendar;
    $calendar->currentLanguageId = $this->parentObj->getContentLanguageId();
    $calendar->mode = 'next_dates';
    $calendar->initialize();
    $calendar->baseLink = @$this->getAbsoluteURL($this->data['file']);
    $minTime = time() + (@(int)$this->data['add_days'] * 60 * 60 * 24);
    // load next dates specified by time and count, tag filter optional
    if (isset($this->data['count']) &&
        (int)$this->data['count'] > 0) {
      $count = (int)$this->data['count'];
    } else {
      $counter = 1;
    }
    $calendar->loadNextDates(
      $minTime,
      $count,
      @(int)$this->data['tag_id'] * @(int)$this->data['filter_tag']
    );
    // sets date from / date to and returns headstring
    $headString = $this->setTimeData($calendar, $minTime);
    if (isset($calendar->dates) &&
        is_array($calendar->dates) &&
        count($calendar->dates > 0)) {
      $result = $calendar->showDates($headString, TRUE);
    }
    return $result;
  }
}
?>