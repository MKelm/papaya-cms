<?php
/**
* Action box for Tag teaser
*
* displays teaser of topics with specified tag
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
* @subpackage _Base
* @version $Id: actbox_tagteaser.php 38114 2013-02-12 16:37:37Z weinert $
*/

/**
* Basic class Action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_topiclist.php');
/**
* Action box for Tag teaser
*
* displays teaser of topics with specified tag
*
* @package Papaya-Modules
* @subpackage _Base
*/
class actionbox_tagteaser extends base_actionbox {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'tag_id' => array('Tag', 'isNum', TRUE, 'disabled_function',
      'callbackSelectedTag', 'Go to "Tag Selection" to modify.', ''),
    'count' => array('Count', 'isNum', TRUE, 'input', 5, '', 1),
    'sort' => array(
      'Sort',
      'isNum',
      TRUE,
      'translatedcombo',
      array(
        base_topiclist::SORT_CREATED_ASCENDING => 'Created Ascending',
        base_topiclist::SORT_CREATED_DESCENDING => 'Created Descending',
        base_topiclist::SORT_PUBLISHED_ASCENDING => 'Modified/Published Ascending',
        base_topiclist::SORT_PUBLISHED_DESCENDING => 'Modified/Published Descending',
        base_topiclist::SORT_RANDOM => 'Random'
      ),
      '',
      0
    )
  );

  var $tagSelectorForm = '';

  var $modified = FALSE;

  /**
  * callback function for tags
  */
  function callbackSelectedTag($name, $element, $data) {
    $result = '';
    $tag = $this->tagSelector->getTag($data, $this->lngSelect->currentLanguageId);
    $result .= sprintf(
      '<input type="text" name="%s[%s]"'.
      ' class="dialogInput dialogScale" value="%s" disabled="disabled"></input>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name),
      empty($tag['tag_title'])
        ? papaya_strings::escapeHTMLChars($name)
        :papaya_strings::escapeHTMLChars($tag['tag_title'])
    );
    return $result;
  }

  /**
  * initialize edit dialog, extended by tag selection
  */
  function initializeDialog() {
    $result = '';
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
        $this->tagSelectorForm =
          $this->tagSelector->getTagSelector(array($this->data['tag_id']), 'single');
        $selectedTags = $this->tagSelector->getSelectedTags();
        if (current($selectedTags) != $this->data['tag_id']) {
          $this->data['tag_id'] = current($selectedTags);
          $this->modified = TRUE;
        }
      }
      break;
    default:
      $result .= parent::initializeDialog();
      break;
    }
  }

  /**
  * generate edit form
  */
  function getForm() {
    $result = '';
    $result .= $this->getContentToolbar();

    if (!isset($this->data['tag_id']) && !isset($this->data['tag_title'])) {
      $this->addMsg(MSG_INFO, $this->_gt('No tag selected!'));
    }

    if (empty($this->params['contentmode'])) {
      $this->params['contentmode'] = 0;
    }
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
  * generate content toolbar
  */
  function getContentToolbar() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$GLOBALS['PAPAYA_IMAGES'];

    $toolbar->addButton(
      'General',
      $this->getLink(array('contentmode'=>0)),
      $toolbar->images['categories-content'],
      '',
      $this->params['contentmode'] == 0
    );
    $toolbar->addButton(
      'Tag Selection',
      $this->getLink(array('contentmode'=>1)),
      $toolbar->images['actions-tag-add'],
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
  * check input data
  */
  function checkData() {
    if (empty($this->params['contentmode'])) {
      $this->params['contentmode'] = 0;
    }
    switch($this->params['contentmode']) {
    case 1:
      return TRUE;
    default:
      return parent::checkData();
    }
    return FALSE;
  }

  /**
  * return modified state of dialog
  */
  function modified() {
    if (empty($this->params['contentmode'])) {
      $this->params['contentmode'] = 0;
    }
    switch($this->params['contentmode']) {
    case 1:
      return $this->modified;
    default:
      return parent::modified();
    }
    return FALSE;
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = '';
    $topicList = new base_topiclist;
    $topicList->databaseURI = $this->parentObj->databaseURI;
    $topicList->databaseURIWrite = $this->parentObj->databaseURIWrite;
    $topicList->tableTopics = $this->parentObj->tableTopics;
    $topicList->tableTopicsTrans = $this->parentObj->tableTopicsTrans;
    $topicClass = get_class($this->parentObj);
    $topicList->loadListByTag(
      $this->data['tag_id'],
      (int)$this->parentObj->topic['TRANSLATION']['lng_id'],
      is_a($this->parentObj, 'papaya_publictopic'),
      (int)$this->data['sort']
    );
    $result = $topicList->getList($topicClass, (int)$this->data['count']);
    return $result;
  }
}
?>