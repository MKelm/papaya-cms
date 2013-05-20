<?php
/**
* Page module Catalog
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
* @subpackage Free-Catalog
* @version $Id: content_catalog.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* base catalog module
*/
require_once(dirname(__FILE__).'/base_catalog.php');

/**
* Page module Catalog
*
* @package Papaya-Modules
* @subpackage Free-Catalog
*/
class content_catalog extends base_content {

  /**
  * Page content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Papaya will apply your linebreaks to the output page.',
      0),
    'link_mode' => array(
      'Link page name mode',
      'isNoHTML',
      TRUE,
      'combo',
      array('catalog' => 'Catalog titles', 'topic' => 'Topic titles'),
      '',
      'catalog'
    ),
    'title' =>
      array ('Title', 'isNoHTML', TRUE, 'input', 200, '', ''),
    'teaser' =>
      array ('Teaser', 'isSomeText', FALSE, 'simplerichtext', 6, '', ''),
    'Start view categories',
    'categ' =>
      array ('Category', 'isNum', TRUE, 'input', 200, '', 0),
    'catalog_offset' =>
      array ('Offset', 'isNum', TRUE, 'input', 3, '', 1),
    'catalog_levels' =>
      array ('Levels', 'isNum', TRUE, 'input', 3, '', 1),
    'Category teaser images',
    'cat_img_width' =>
      array ('Width', 'isNum', FALSE, 'input', 10, '', ''),
    'cat_img_height' =>
      array ('Height', 'isNum', FALSE, 'input', 10, '', ''),
    'cat_img_resize' =>
      array ('Resize', 'isAlpha', TRUE, 'combo',
        array(
          'max' => 'Maximal',
          'min' => 'Minimum',
          'mincrop' => 'Minimum crop',
          'abs' => 'Absolute'
        ), '', 'max'),
    'Link teaser images',
    'img_width' =>
      array ('Width', 'isNum', FALSE, 'input', 10, '', ''),
    'img_height' =>
      array ('Height', 'isNum', FALSE, 'input', 10, '', ''),
    'img_resize' =>
      array ('Resize', 'isAlpha', TRUE, 'combo',
        array(
          'max' => 'Maximal',
          'min' => 'Minimum',
          'mincrop' => 'Minimum crop',
          'abs' => 'Absolute'
        ), '', 'max')
  );

  /**
  * Base catalog object
  * @var object base_catalog $catalog
  */
  var $catalog = NULL;

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    $this->initCatalogObject();
    $mode = 'catalog';
    if (isset($this->data['link_mode']) && $this->data['link_mode'] == 'topic') {
      $mode = 'topic';
    }
    $result .= $this->catalog->getOutput(
      $this->parentObj->getContentLanguageId(),
      NULL,
      $mode
    );
    return $result;
  }

  /**
  * check url filename for url fixation
  *
  * @param string $currentFileName
  * @param string $outputMode
  * @access public
  * @return string | FALSE  url for redirect (empty for default redirect) or FALSE
  */
  function checkURLFileName($currentFileName, $outputMode) {
    $this->initCatalogObject();
    return $this->catalog->checkURLFileName(
      $currentFileName,
      $outputMode,
      $this->parentObj->getContentLanguageId()
    );
  }

  /**
  * init catalog object only once in request
  *
  * @access public
  * @return void
  */
  function initCatalogObject() {
    if (!(isset($this->catalog) && is_object($this->catalog))) {
      $this->catalog = new base_catalog();
      $this->catalog->module = &$this;
      $this->catalog->tableTopics = $this->parentObj->tableTopics;
      $this->catalog->tableTopicsTrans = $this->parentObj->tableTopicsTrans;
    }
  }
}

?>