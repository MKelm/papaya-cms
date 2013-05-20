<?php
/**
* Mnogo html actionbox
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
* @subpackage Free-Mnogo
* @version $Id: actbox_searchbox.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Base actionbox class
**/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Mnogo html actionbox
*
* @package Papaya-Modules
* @subpackage Free-Mnogo
*/
class actionbox_searchbox extends base_actionbox {

  /**
  * more detailed cache dependencies
  *
  * querystring = depends on current $_SERVER['QUERY_STRING'],
  * page = depends on current page/topic id
  * surfer = depends on current surfer id (guest for invalid surfers)
  *
  * @var array $cacheDependency
  */
  var $cacheDependency = array(
    'querystring' => FALSE,
    'page' => FALSE,
    'surfer' => FALSE
  );

  /**
  * Preview allowed?
  * @var boolean $preview
  */
  var $preview = TRUE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'page_id' => array ('Page Id', 'isNum', TRUE, 'input', 5, '', 0),
    'buttontitle' => array ('Button Title', 'isSomeText', TRUE, 'input', 50, '', 'Search')
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string $xml XML
  */
  function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $xml = sprintf(
      '<searchdialog action="%s" button="%s">'.LF,
      papaya_strings::escapeHTMLChars(
        $this->getWebLink($this->data['page_id'])
      ),
      papaya_strings::escapeHTMLChars($this->data['buttontitle'])
    );
    $xml .= sprintf(
      '<input type="text" name="%s[searchfor]" value="%s" class="text" />'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($this->params['searchfor'])
        ? '' : papaya_strings::escapeHTMLChars($this->params['searchfor'])
    );
    $xml .= '</searchdialog>'.LF;
    return $xml;
  }

  /**
  * Get cache id
  *
  * @access public
  * @return boolean defualt value FALSE
  */
  function getCacheId($additionalCacheString = '') {
    $this->initializeParams();
    return parent::getCacheId(empty($this->params['searchfor']) ? '' : $this->params['searchfor']);
  }
}
?>