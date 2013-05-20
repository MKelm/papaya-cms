<?php
/**
* Provide an array like access to session values. Allow to use complex identifiers. Handle
* sessions that are not startet yet.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Session
* @version $Id: Values.php 37975 2013-01-17 11:41:41Z weinert $
*/

/**
* Provide an array like access to session values. Allow to use complex identifiers. Handle
* sessions that are not startet yet.
*
* @package Papaya-Library
* @subpackage Session
*/
class PapayaSessionValues implements ArrayAccess {

  /**
  * Linked session object
  * @var PapayaSession
  */
  private $_session = NULL;

  /**
  * Initialize object and link session object
  *
  * @param PapayaSession $session
  */
  public function __construct(PapayaSession $session) {
    $this->_session = $session;
  }

  /**
  * Check if the session variable exists
  *
  * @param mixed $identifier
  * @return boolean
  */
  public function offsetExists($identifier) {
    if (isset($_SESSION) && is_array($_SESSION)) {
      return array_key_exists($this->_compileKey($identifier), $_SESSION);
    }
    return FALSE;
  }

  /**
  * Get a session value if the session is active and the value exists. Return NULL otherwise.
  *
  * @param mixed $identifier
  * @return mixed
  */
  public function offsetGet($identifier) {
    $key = $this->_compileKey($identifier);
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }
    return NULL;
  }

  /**
  * Alias for {@see PapayaSessionValues::offsetGet()}.
  *
  * @param mixed $identifier
  * @param mixed $value
  */
  public function get($identifier) {
    return $this->offsetGet($identifier);
  }


  /**
  * Set a session value, if the session is inactive the value will not be stored.
  *
  * @param mixed $identifier
  * @param mixed $value
  */
  public function offsetSet($identifier, $value) {
    if ($this->_session->isActive()) {
      $_SESSION[$this->_compileKey($identifier)] = $value;
    }
  }

  /**
  * Alias for {@see PapayaSessionValues::offsetSet()}.
  *
  * @param mixed $identifier
  * @param mixed $value
  */
  public function set($identifier, $value) {
    $this->offsetSet($identifier, $value);
  }

  /**
  * Remove an existing session value if the session is active.
  *
  * @param mixed $identifier
  */
  public function offsetUnset($identifier) {
    if ($this->_session->isActive() &&
        is_array($_SESSION)) {
      $key = $this->_compileKey($identifier);
      if (array_key_exists($key, $_SESSION)) {
        unset($_SESSION[$this->_compileKey($identifier)]);
      }
    }
  }

  /**
   * Provide access to the used identifer string
   *
   * @param mixed $identifier
   * @return string
   */
  public function getKey($identifier) {
    return $this->_compileKey($identifier);
  }

  /**
  * Compile session variable identifier data into an string.
  *
  * If the identifier data is an object the class of this object is used.
  *
  * If the identifier data is an array the elements are joined using underscores. For object
  * elements their classname will be used. Array elements will be serialized and hased with md5.
  * All other elements are casted to strings.
  *
  * All other identifier data is casted to a string.
  *
  * @param mixed $identifier
  * @return string
  */
  private function _compileKey($identifier) {
    if (is_array($identifier)) {
      $result = '';
      foreach ($identifier as $part) {
        if (is_object($part)) {
          $result .= '_'.get_class($part);
        } elseif (is_array($part)) {
          $result .= '_'.md5(serialize($part));
        } else {
          $result .= '_'.((string)$part);
        }
      }
      return substr($result, 1);
    } elseif (is_object($identifier)) {
      return get_class($identifier);
    } else {
      return (string)$identifier;
    }
  }
}