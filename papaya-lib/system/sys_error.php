<?php
/**
* basic class for handling errors
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
* @package Papaya
* @subpackage Core
* @version $Id: sys_error.php 36443 2011-11-22 14:11:42Z weinert $
*/

/**
* base object
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_object.php');

/**
* Error Object, here you can output all errors on demand
* @package Papaya
* @subpackage Core
*/
class base_errors extends base_object {
  /**
  * Error list
  * @var array $errorList
  */
  var $errorList;

  /**
  * Error types
  * @var array $errorList
  */
  var $errorTypes;

  /**
  * Add error
  *
  * @param integer $id error type
  * @param string $msg error message
  * @access public
  * @return boolean Added? (ignoring duplicates)
  */
  function add($id, $msg) {
    if (isset($this->errorList) && is_array($this->errorList)) {
      foreach ($this->errorList as $error) {
        if ($error[0] == $id && $error[1] == $msg) {
          return FALSE;
        }
      }
    }
    $this->errorList[] = array($id, $msg);
    return TRUE;
  }

  /**
  * Delete error list
  *
  * @access public
  */
  function clear() {
    unset($this->errorList);
  }

  /**
  * Set error type
  *
  * @param integer $id identification number
  * @param string $start ?
  * @param string $end ?
  * @access public
  */
  function setType($id, $start = "", $end = "") {
    $this->errorTypes[$id] = array($start, $end);
  }
}
?>