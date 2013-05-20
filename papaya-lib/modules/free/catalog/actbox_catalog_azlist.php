<?php
/**
* Catalog letter box
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
* @version $Id: actbox_catalog_azlist.php 32288 2009-09-30 17:40:22Z weinert $
*/

/**
* Basic class aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Catalog  letter box  - outputs an a-z list linked to an a-z list page
*
* @package Papaya-Modules
* @subpackage Free-Catalog
*/
class actionbox_catalog_azlist extends base_actionbox {

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'page_id' => array('Page Id', 'isNum', TRUE, 'input', 5, ''),
    'caption' => array('Caption', 'isNoHTML', TRUE, 'input', 50, ''),
    'description' => array('Description', 'isNoHTML', TRUE, 'input', 200, ''),
  );

  /**
  * Return page XML
  *
  * @access public
  * @return string $str
  */
  function getParsedData() {
    $pageId = empty($this->data['page_id']) ? 0 : (int)$this->data['page_id'];
    $str = sprintf(
      '<letters caption="%s" description="%s">'.LF,
      empty($this->data['caption'])
        ? '' : papaya_strings::escapeHTMLChars($this->data['caption']),
      empty($this->data['description'])
        ? '' : papaya_strings::escapeHTMLChars($this->data['description'])
    );
    for ($i = ord('A'); $i <= ord('Z'); $i++) {
      $str .= sprintf(
        '<letter href="%s">%s</letter>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getWebLink($pageId).'#'.chr($i)
        ),
        papaya_strings::escapeHTMLChars(chr($i))
      );
    }
    $str .= '</letters>'.LF;
    return $str;
  }
}
?>