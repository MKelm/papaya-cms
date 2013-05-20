<?php
/**
* Action box for PHP-Include
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
* @version $Id: actbox_include.php 32587 2009-10-14 15:09:03Z weinert $
*/

/**
* Basic class aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');
/**
* Action box for Include-File
*
* Attention! Error in include can inhibit page display
*
* @package Papaya-Modules
* @subpackage Free-Include
*/
class actionbox_include extends base_actionbox {

  /**
  * edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'file' => array('Einzubindende Datei', 'isFile', TRUE, 'input', 50, '')
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    if (isset($this->data['file']) && trim($this->data['file']) != '') {
      ob_start();
      $result = @include($this->data['file']);
      if (isset($result) && is_string($result) && $result != '') {
        ob_end_clean();
        return $result;
      }
      return ob_get_clean();
    } else {
      return '';
    }
  }
}
?>
