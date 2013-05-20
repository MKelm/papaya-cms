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
* @version $Id: actbox_categteaserthumb.php 38457 2013-04-29 12:15:14Z kersken $
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
class actionbox_categteaserthumb extends base_actionbox {

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
    'page' => array('Page', 'isNum', TRUE, 'input', 5, '', 0),
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
        base_topiclist::SORT_WEIGHT_ASCENDING => 'Position Ascending',
        base_topiclist::SORT_WEIGHT_DESCENDING => 'Position Descending',
        base_topiclist::SORT_CREATED_ASCENDING => 'Created Ascending',
        base_topiclist::SORT_CREATED_DESCENDING => 'Created Descending',
        base_topiclist::SORT_PUBLISHED_ASCENDING => 'Modified/Published Ascending',
        base_topiclist::SORT_PUBLISHED_DESCENDING => 'Modified/Published Descending',
        base_topiclist::SORT_RANDOM => 'Random'
      ),
      '',
      0
    ),
    'perm' => array(
      'Surfer permission',
      'isNum',
      TRUE,
      'function',
      'callbackSurferPerm',
      'Permission to view this or "none" if generally allowed',
      -1
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
  * Get parsed data, generate a list of topic teasers including thumbnails selected by a tag.
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = '';
    $permission = TRUE;
    if ($this->data['perm'] > 0) {
      $surfer = $this->papaya()->surfer;
      if (!$surfer->hasPerm($this->data['perm'])) {
        $permission = FALSE;
      }
    }
    if ($permission) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_topiclist.php');
      $topicList = new base_topiclist;
      $topicList->databaseURI = $this->parentObj->databaseURI;
      $topicList->databaseURIWrite = $this->parentObj->databaseURIWrite;
      $topicList->tableTopics = $this->parentObj->tableTopics;
      $topicList->tableTopicsTrans = $this->parentObj->tableTopicsTrans;
      $topicClass = get_class($this->parentObj);
      $topicList->loadList(
        (int)$this->data['page'],
        (int)$this->parentObj->getContentLanguageId(),
        is_a($this->parentObj, 'papaya_publictopic'),
        (int)$this->data['sort'],
        (int)$this->data['count']
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
      $result = sprintf(
        '<categteaserthumb topic="%d" columns="%d" balance-mode="%s">%s</categteaserthumb>',
        (int)$this->parentObj->topicId,
        (int)$this->data['columns'],
        PapayaUtilstringXml::escapeAttribute($this->data['balance_mode']),
        $result
      );
    }
    return $result;
  }

  /**
  * Get a selector for surfer permissions
  *
  * @param string $name
  * @param array $field
  * @param integer $data
  * @return string form XML
  */
  function callbackSurferPerm($name, $field, $data) {
    $surfersObj = base_pluginloader::getPluginInstance('06648c9c955e1a0e06a7bd381748c4e4', $this);
    return $surfersObj->getPermCombo($name, $field, $data, $this->paramName);
  }
}
?>