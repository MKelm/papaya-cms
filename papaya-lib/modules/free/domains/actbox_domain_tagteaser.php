<?php
/**
* Action box for Category teaser with thumbnail
*
* Shows teaser text with thumbnail of item image, teasers are selected by tag and
* the tag can be domain specific.
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
* @subpackage Free-Domains
* @version $Id: actbox_domain_tagteaser.php 38114 2013-02-12 16:37:37Z weinert $
*/

/**
* Basic class Action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box for Category teaser with thumbnail
*
* Shows teaser text with thumbnail of item image, teasers are selected by tag and
* the tag can be domain specific.
*
* @package Papaya-Modules
* @subpackage Free-Domains
*/
class actionbox_domain_tagteaser extends base_actionbox {

  /**
  * More detailed cache dependencies
  *
  * @var array $cacheDependency
  */
  var $cacheDependency = array(
    'querystring' => FALSE,
    'page' => TRUE,
    'surfer' => TRUE
  );

  /**
  * GUID of domain connector module
  * @var string
  */
  var $domainConnectorGuid = '8ec0c5995d97c9c3cc9c237ad0dc6c0b';

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'domain_tag_ident' =>
      array('Tag identifier', 'isAlphaNum',
        FALSE,  'function', 'getIdentifierCombo',
        'Select a domain field which contains an URI used to identify a tag.',
        ''),
    'tag_id' => array('Default tag', 'isNum', TRUE, 'disabled_function',
      'callbackSelectedTag', 'Go to "Tag Selection" to modify.', 0),
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
    ),
    'Thumbnails',
    'show_thumbnails' => array('Show', 'isNum', TRUE, 'yesno', NULL, '', 0),
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
  * Tag selector form xml
  * @var string
  */
  var $tagSelectorForm = '';

  /**
  * edit data modified
  * @var boolean
  */
  var $modified = FALSE;

  /**
  * get select box of domain fields
  *
  * @param string $name
  * @param array $field
  * @param string $data
  * @access public
  * @return string
  */
  function getIdentifierCombo($name, $field, $data) {
    $domainConnector = base_pluginloader::getPluginInstance(
      $this->domainConnectorGuid,
      $this
    );

    if (isset($domainConnector) && is_object($domainConnector)) {
      return $domainConnector->getIdentifierCombo(
        $this->paramName.'['.$name.']',
        $data,
        FALSE
      );
    }
    return '';
  }

  /**
  * get information field for selected tag
  *
  * @param string $name
  * @param array $element
  * @param string $data
  * @access public
  * @return string
  */
  function callbackSelectedTag($name, $element, $data) {
    $result = '';
    $tag = $this->tagSelector->getTag($data, $this->lngSelect->currentLanguageId);
    $result .= sprintf(
      '<input type="text" name="%s[%s]" class="dialogInput dialogScale"'.
      ' value="%s" disabled="disabled"></input>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name),
      empty($tag['tag_title']) ? '' : papaya_strings::escapeHTMLChars($tag['tag_title'])
    );
    return $result;
  }

  /**
  * initialize backend dialog
  *
  * @access public
  * @return void
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
        $this->tagSelectorForm = $this->tagSelector->getTagSelector(
           empty($this->data['tag_id']) ? array() : array($this->data['tag_id']),
          'single'
        );
        $selectedTags = $this->tagSelector->getSelectedTags();
        $newTag = current($selectedTags);
        if ((empty($this->data['tag_id']) && !empty($newTag)) || $newTag != $this->data['tag_id']) {
          $this->data['tag_id'] = $newTag;
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
  * get backend dialog xml
  *
  * @access public
  * @return string
  */
  function getForm() {
    $result = '';
    $result .= $this->getContentToolbar();

    if (!isset($this->data['tag_id']) && !isset($this->data['tag_title'])) {
      $this->addMsg(MSG_INFO, $this->_gt('No tag selected!'));
    }

    if (!isset($this->params['contentmode'])) {
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
  * get content toolbar (switch between edit dialog and tag selector)
  *
  * @access public
  * @return string
  */
  function getContentToolbar() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$GLOBALS['PAPAYA_IMAGES'];

    $toolbar->addButton(
      'General',
      $this->getLink(array('contentmode' => 0)),
      $toolbar->images['categories-content'],
      '',
      $this->params['contentmode'] == 0
    );
    $toolbar->addButton(
      'Tag Selection',
      $this->getLink(array('contentmode' => 1)),
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
  * Check input data in backend
  *
  * @access public
  * @return boolean
  */
  function checkData() {
    if (!isset($this->params['contentmode'])) {
      $this->params['contentmode'] = 0;
    }
    switch ($this->params['contentmode']) {
    case 1:
      return TRUE;
    default:
      return parent::checkData();
    }
    return FALSE;
  }

  /**
  * back end data modified?
  *
  * @access public
  * @return boolean
  */
  function modified() {
    if (!isset($this->params['contentmode'])) {
      $this->params['contentmode'] = 0;
    }
    switch ($this->params['contentmode']) {
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
    $result = '';
    $this->setDefaultData();
    $tagId = $this->data['tag_id'];
    if (!empty($this->data['domain_tag_ident'])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $domainConnector = base_pluginloader::getPluginInstance(
        $this->domainConnectorGuid,
        $this
      );
      if (is_object($domainConnector)) {
        $data = $domainConnector->loadValues($this->data['domain_tag_ident']);
        if (!empty($data[$this->data['domain_tag_ident']])) {
          $tagURI = $data[$this->data['domain_tag_ident']];
          include_once(PAPAYA_INCLUDE_PATH.'system/base_tags.php');
          $tags = new base_tags();
          $data = $tags->getTagIdsByURI($tagURI);
          if (!empty($data[$tagURI])) {
            $tagId = $data[$tagURI];
          }
        }
      }
    }
    if ($tagId > 0) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_topiclist.php');
      $topicList = new base_topiclist;
      $topicList->tableTopics = $this->parentObj->tableTopics;
      $topicList->tableTopicsTrans = $this->parentObj->tableTopicsTrans;
      $topicClass = get_class($this->parentObj);
      $topicList->loadListByTag(
        $tagId,
        (int)$this->parentObj->getContentLanguageId(),
        is_a($this->parentObj, 'papaya_publictopic'),
        (int)$this->data['sort']
      );
      $subTopicString = papaya_strings::entityToXML(
        $topicList->getList($topicClass, (int)$this->data['count'])
      );
      $result .= $subTopicString;
      //get all subtopic images and generate/append papaya tags for thumbnails
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
        '<categteaserthumb topic="%d">%s</categteaserthumb>',
        (int)$this->parentObj->topicId,
        $result
      );
    }
  }
}
?>