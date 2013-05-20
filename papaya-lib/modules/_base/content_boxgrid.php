<?php
/**
* Page module - boxgrid
*
* This module displays a set of boxes (a box grid) named by a boxgroup.
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
* @version $Id: content_boxgrid.php 32604 2009-10-14 15:38:08Z weinert $
*/

/**
* Basic class Page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Page module - boxgrid
*
* @package Papaya-Modules
* @subpackage _Base
*/
class content_boxgrid extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 400, '', ''),
    'cols' => array('Columns per row', 'isNum', TRUE, 'input', 2, '', 2),
    'boxgroup' => array('Box group', 'isNoHTML', TRUE, 'input', 100, '', ''),
  );

  /**
  * Get parsed data
  *
  * @access public
  * @param array | NULL $parseParams Parameters from output filter
  * @return string
  */
  function getParsedData($parseParams = NULL) {
    $this->setDefaultData();
    $result = sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<cols>%d</cols>'.LF,
      (int)$this->data['cols']);
    $result .= sprintf(
      '<boxgroup>%s</boxgroup>'.LF,
      papaya_strings::escapeHTMLChars($this->data['boxgroup'])
    );
    return $result;
  }
}

?>