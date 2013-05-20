<?php
/**
* ActionBox for HTTP-Include
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
* @subpackage Free-Include
* @version $Id: actbox_url.php 32587 2009-10-14 15:09:03Z weinert $
*/

/**
* Basisklasse Aktionboxen
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action Box for HTTP-Include
*
* @package Papaya-Modules
* @subpackage Free-Include
*/
class actionbox_url extends base_actionbox {

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'file' => array('URL', 'isHTTPX', TRUE, 'input', 50, '')
  );

  /**
  * Get Parsed Data - Basic function for page output
  *
  * @access public
  * @return string XML
  */
  function getParsedData() {
    if (!empty($this->data['file'])) {
      return file_get_contents($this->data['file']);
    }
    return '';
  }
}
?>