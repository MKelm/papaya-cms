<?php
/**
* Error object collect errors and return them on request
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
* @package Papaya
* @subpackage Administration
* @version $Id: papaya_errors.php 32574 2009-10-14 14:00:46Z weinert $
*/


/**
* Basic class Error
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_error.php');
/**
* Error object collect errors and return them on request
* @package Papaya
* @subpackage Administration
*/
class papaya_errors extends base_errors {

  /**
  * use own error handler
  * @var boolean $useErrorHandler
  */
  var $useErrorHandler = FALSE;

  /**
  * Constructor
  *
  * @access public
  */
  function __construct() {
    $this->setType(MSG_INFO, "info");
    $this->setType(MSG_WARNING, "warning");
    $this->setType(MSG_ERROR, "error");
  }

  /**
  * Output a box error
  *
  * @param string $width optional, default value '100%'
  * @access public
  * @return string
  */
  function get($width = '100%') {
    $result = "";
    if (isset($this->errorList) && is_array($this->errorList)) {
      foreach ($this->errorList as $error) {
        $msg = papaya_strings::escapeHTMLChars($error[1]);
        $result .= sprintf(
          '<msg type="%s" width="%s">%s</msg>',
          papaya_strings::escapeHTMLChars($this->errorTypes[$error[0]][0]),
          papaya_strings::escapeHTMLChars($width),
          papaya_strings::escapeHTMLChars($msg)
        );
      }
    }
    return $result;
  }

  /**
  * Translate errors and attach them
  *
  * @param integer $id error typ
  * @param string $msg error text
  * @access public
  */
  function addMsgTranslated($id, $msg) {
    $this->add($id, $this->_gt($msg));
  }

  /**
  * Save errors to session
  * @return string|FALSE
  */
  function saveToSession() {
    if (isset($this->errorList) && is_array($this->errorList)
        && count($this->errorList) > 0) {
      $identifier = md5(uniqid(rand() * 10000));
      $this->setSessionValue(get_class($this).$identifier, $this->errorList);
      return $identifier;
    }
    return FALSE;
  }

  /**
  * Restore errors from session
  * @param string $identifier
  * @return void
  */
  function restoreFromSession($identifier) {
    if (checkit::isGUID($identifier)) {
      $errors = $this->getSessionValue(get_class($this).$identifier);
      if (isset($errors) && is_array($errors) && count($errors) > 0) {
        if (isset($this->errorList) && is_array($this->errorList)) {
          $this->errorList = array_merge($this->errorList, $errors);
        } else {
          $this->errorList = $errors;
        }
        $this->setSessionValue(get_class($this).$identifier, NULL);
      }
    }
  }
}

?>