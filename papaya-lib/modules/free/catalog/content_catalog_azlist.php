<?php
/**
* Page module catelog a-z list
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
* @version $Id: content_catalog_azlist.php 36224 2011-09-20 08:00:57Z weinert $
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
* Page module catelog a-z list
*
* @package Papaya-Modules
* @subpackage Free-Catalog
*/
class content_catalog_azlist extends base_content {

  /**
  * Page content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0
    ),
    'categ_base_id' => array('Base category Id', 'isNum', TRUE, 'input', 200, '', 0),
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 5, '', ''),
  );

  /**
  * Base catalog objeckt
  * @var object base_dialog $catalog
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
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    $this->initCatalogObject();
    $result .= $this->catalog->getAzListOutput(
      $this->data['categ_base_id'],
      $this->parentObj->getContentLanguageId()
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