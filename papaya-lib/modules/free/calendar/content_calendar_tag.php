<?php
/**
* Page module - language dependent calendar with category-tag-filter.
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
* @version $Id: content_calendar_tag.php 36224 2011-09-20 08:00:57Z weinert $
*/


/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - calendar
*
* Display dates
*
* @package Papaya-Modules
* @subpackage Free:Calendar
*/
class content_calendar_tag extends base_content {

  /**
  * Edit fields
  *
  * @var array $editFields
  */
  var $editFields = array(
    'show_calendar_navigation' => array('Show calendar Navigation', 'isNum', TRUE,
      'translatedcombo', array('0' => 'no', '1' => 'yes'),
      '"yes" to show detail entries, "no" to show month table.'
    ),
    'Tag configuration',
    'filter_tag' => array('Tagged entries only', 'isNum', TRUE,
      'translatedcombo', array('0' => 'no', '1' => 'yes'), 'Show only entries having this tag?'),
    'tag_id' => array('Tag', 'isNum', TRUE, 'disabled_function',
      'callbackSelectedTag', 'Go to "Tag Selection" to modify.'),
    'Image',
    'image' => array('Image', 'isSomeText', FALSE, 'image', 400),
    'imgalign' => array('Image align', 'isAlpha', TRUE, 'combo',
      array('left' => 'left', 'right' => 'right', 'center' => 'center')),
    'breakstyle' => array('Text float', 'isAlpha', TRUE, 'combo',
      array('none' => 'None', 'side' => 'Side', 'center' => 'Center')),
    'Texts',
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 10),
    'text' => array ('Text', 'isSomeText', FALSE, 'richtext', 10)
  );

  /**
  * Tag selector
  * @var object $tagSelector papaya_tagselector
  */
  var $tagSelector = FALSE;

  /**
  * Modification status
  * @var boolean $modified
  */
  var $modified = FALSE;

  /**
  * Initialization
  *
  * @access public
  */
  function initialize() {
    parent::initialize();

    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();

    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('contentmode');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    $this->initializeSessionParam('cat_id');

  }

  /**
  * Loads tag selector and execute
  *
  * @access public
  */
  function execute() {
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_tagselector.php');
    $this->tagSelector = papaya_tagselector::getInstance($this->parentObj);
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
  }

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
    $tag = $this->tagSelector->getTag(
      $data, $this->parentObj->getContentLanguageId()
    );
    return sprintf(
      '<input type="text" name="%s[%s]" class="dialogInput dialogScale"'.
      ' value="%s" disabled="disabled"></input>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name),
      papaya_strings::escapeHTMLChars(
        empty($tag['tag_title']) ? '' : $tag['tag_title']
      )
    );
  }

  /**
  * Gets backend form
  *
  * @access public
  * @return string $result as xml
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
      break;
    }
    return $result;
  }

  /**
  * Gets content toolbar for backend form.
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
  * Get parsed data. Returns the main xml for this calendar page module. If the
  * filter_tag switch is set to no, this module will display all calendar entries.
  * Otherwise, only those calendar entries are displayed that are tagged with the
  * selected tag shown in the field 'tag_id'.
  *
  * @access public
  * @return string output the content elements of /page/content/topic/
  */
  function getParsedData() {
    include_once(dirname(__FILE__).'/base_calendar.php');
    $result = sprintf(
      '<teaser>%s</teaser>'.LF,
      $this->getXHTMLString(
        $this->applyFilterData(@$this->data['teaser']),
        !((bool)@$this->data['nl2br'])
      )
    );
    $result = sprintf(
      '<text>%s</text>',
      $this->getXHTMLString(
        $this->applyFilterData(@$this->data['text']),
        !((bool)@$this->data['nl2br'])
      )
    );
    $result .= sprintf(
      '<image align="%s" break="%s">%s</image>'.LF,
      papaya_strings::escapeHTMLChars(@$this->data['imgalign']),
      papaya_strings::escapeHTMLChars(@$this->data['breakstyle']),
      $this->getPapayaImageTag(@$this->data['image'])
    );
    $calendar = new base_calendar;
    $calendar->currentLanguageId = $this->parentObj->getContentLanguageId();
    $calendar->initialize();
    $calendar->execute();
    $calendar->mode = empty($calendar->params['cmd']) ? 'month' : $calendar->params['cmd'];

    if (!isset($calendar->params['time'])) {
      $calendar->selectedDate = $calendar->parseTimeToArray(time());
    } else {
      if (isset($calendar->params['newtime'])) {
        $calendar->selectedDate =
          $calendar->parseTimeToArray($calendar->params['newtime']);
      } else {
        $calendar->selectedDate = $calendar->parseTimeToArray($calendar->params['time']);
      }
    }
    if (isset($calendar->params['month']) &&
        $calendar->params['month'] != $calendar->selectedDate['month']) {
      $calendar->selectedDate['month'] = $calendar->params['month'];
    }

    $result .= '<calendar>'.LF;
    if (isset($this->data['tag_id']) &&
        isset($this->data['filter_tag']) &&
        $this->data['filter_tag']) {
      if (isset($this->data['show_calendar_navigation']) &&
          $this->data['show_calendar_navigation']) {
        $result .= $calendar->showMonthTable((int)$this->data['tag_id']);
      } else {
        $result .= $calendar->showDetail((int)$this->data['tag_id']);
      }
    } else {
      if (isset($this->data['show_calendar_navigation']) &&
          $this->data['show_calendar_navigation']) {
        $result .= $calendar->showMonthTable();
        $result .= $calendar->showDetail();
      } else {
        $result .= $calendar->showDetail();
      }
    }
    $result .= '</calendar>'.LF;
    return $result;
  }

  /**
  * Returns the teaser and image of this article as xml.
  *
  * @access public
  * @return string the teaser and image content as xml
  */
  function getParsedTeaser() {
    $result = sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString(@$this->data['teaser'], !((bool)@$this->data['nl2br']))
    );
    $result .= sprintf(
      '<image align="%s" break="%s">%s</image>'.LF,
      papaya_strings::escapeHTMLChars(@$this->data['imgalign']),
      papaya_strings::escapeHTMLChars(@$this->data['breakstyle']),
      $this->getPapayaImageTag(@$this->data['image'])
    );
    return $result;
  }
}

?>
