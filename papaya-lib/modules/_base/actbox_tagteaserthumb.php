<?php
/**
* Action box for Category teaser with thumbnail
*
* Shows teaser text with thumbnail of item image
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
* @subpackage _Base
* @version $Id: actbox_tagteaserthumb.php 38248 2013-03-02 12:04:04Z weinert $
*/

/**
* Basic class Action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_topiclist.php');

/**
* Action box for Category teaser with thumbnail
*
* Shows teaser text with thumbnail of item image
*
* @package Papaya-Modules
* @subpackage _Base
*/
class actionbox_tagteaserthumb extends base_actionbox {

  /**
  * more detailed cache dependencies
  * @var array
  */
  var $cacheDependency = array(
    'querystring' => FALSE,
    'page' => TRUE,
    'surfer' => TRUE
  );

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'tag_id' => array('Tag', 'isNum', TRUE, 'disabled_function',
      'callbackSelectedTag', 'Go to "Tag Selection" to modify.', 0),
    'count' => array('Count', 'isNum', TRUE, 'input', 5, '', 1),
    'columns' => array('Column count', 'isNum', TRUE, 'input', 1, '', 2),
    'balance_mode' => array(
      'Balance',
      'isAlpha',
      TRUE,
      'translatedcombo',
      array(
        'top' => 'top',
        'bottom' => 'bottom'
      ),
      '',
      'bottom'
    ),
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
    ),
    'Thumbnails',
    'thumbwidth' => array('Width', 'isNum', TRUE, 'input', 5, '', 100),
    'thumbheight' => array('Height', 'isNum', TRUE, 'input', 5, '', 100),
    'resizemode' => array('Resize mode', 'isAlpha', TRUE, 'combo',
      array(
        'max' => 'Maximal',
        'min' => 'Minimum',
        'mincrop' => 'Minimum crop',
        'abs' => 'Absolute'
      ), '', 'max')
  );

  /**
  * tag selector form
  * @var base_dialog
  */
  var $tagSelectorForm = '';

  /**
  * modified status
  * @var boolean
  */
  var $modified = FALSE;

  /**
  * generate disabled input to show selected tag
  * @param string $name
  * @param array $element
  * @param string $data
  * @return string
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
  * initialize administration dialog (properties or tag selection)
  */
  function initializeDialog() {
    $result = '';
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $this->lngSelect = &base_language_select::getInstance();

    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_tagselector.php');
    $this->tagSelector = &papaya_tagselector::getInstance($this);

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
          array($this->data['tag_id']), 'single'
        );
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
  * return administration dialog
  * @param string $dialogTitlePrefix
  * @param string $dialogIcon
  */
  function getForm($dialogTitlePrefix, $dialogIcon) {
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
  * generate administration content mode toolbar
  * @return string
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
  * @return string
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
      (int)$this->parentObj->getContentLanguageId(),
      is_a($this->parentObj, 'papaya_publictopic'),
      ((int)$this->data['sort'])
    );
    $subTopicString = papaya_strings::entityToXML(
      $topicList->getList(
      $topicClass,
      (int)$this->data['count'])
    );
    $result .= $subTopicString;
    if (trim($subTopicString) != '') {
      $dom = new PapayaXmlDocument();
      $dom->loadXml($subTopicString);
      $thumbnails = new PapayaUiContentTeaserImages(
        $dom->documentElement,
        (int)$this->data['thumbwidth'],
        (int)$this->data['thumbheight'],
        $this->data['resizemode']
      );
      $result .= $thumbnails->getXml();
    }
    return sprintf(
      '<categteaserthumb topic="%d" columns="%d" balance-mode="%s">%s</categteaserthumb>',
      (int)$this->parentObj->topicId,
      (int)$this->data['columns'],
      PapayaUtilstringXml::escapeAttribute($this->data['balance_mode']),
      $result
    );
  }
}
?>