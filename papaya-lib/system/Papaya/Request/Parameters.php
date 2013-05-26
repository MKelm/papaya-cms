<?php
/**
* Papaya Request Parameters Handling
*
* @copyright 2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Request
* @version $Id: Parameters.php 38186 2013-02-26 16:33:15Z faber $
*/

/**
* Papaya Request Parameters Handling
*
* @package Papaya-Library
* @subpackage Request
*/
class PapayaRequestParameters extends PapayaObjectParameters {

  /**
  * Get a subgroup of parameters
  * @param string $groupName
  * @return PapayaRequestParameters
  */
  public function getGroup($groupName) {
    $result = new self();
    if (isset($this[$groupName])) {
      $value = $this[$groupName];
      if (is_array($value) || $value instanceOf Traversable) {
        $result->merge($this[$groupName]);
      }
    }
    return $result;
  }

  /**
   * Get the value, filter it, convert it to the type of the default value and
   * return the default value if no value is found.
   *
   * @see PapayaObjectParameters::get()
   * @return mixed
   */
  public function get($offset, $defaultValue = NULL, $filter = NULL) {
    return parent::get($this->_parseParameterName($offset), $defaultValue, $filter);
  }

  /**
   * Set a value. If $offsets is an array or Traversalbe each element in the array/Traversalbe
   * is set.
   *
   * @param string|array|Traversalbe $offsets
   * @param mixed $value
   */
  public function set($offsets, $value = NULL) {
    if (is_array($offsets) || $offsets instanceOf Traversable) {
      foreach ($offsets as $offset => $value) {
        $this[$offset] = $value;
      }
    } else {
      $this[$offsets] = $value;
    }
  }

  /**
   * Remove ohne or more keys. If $offsets is an array, each element is used as an separate
   * parameter name.
   *
   * @param string|array(string) $offsets
   */
  public function remove($offsets) {
    if (!(is_array($offsets) || $offsets instanceOf Traversable)) {
      $offsets = array($offsets);
    }
    foreach ($offsets as $offset) {
      unset($this[$offset]);
    }
  }

  /**
   * Return the values as an array
   *
   * @return array
   */
  public function toArray() {
    return (array)$this;
  }

  /**
  * Parse request parameter name into parts
  *
  * @param string $name
  * @param string $groupSeparator
  * @return array|string
  */
  private function _parseParameterName($name, $groupSeparator = '') {
    $parts = new PapayaRequestParametersName(str_replace('.', '_', $name), $groupSeparator);
    return $parts->getArray();
  }

  /**
  * Prepare parameters, make sure it is utf8 and strip slashes if needed
  *
  * @param string|array $parameter
  * @param boolean $stripSlashes
  * @param integer $recursion
  * @return array|string
  */
  public function prepareParameter($parameter, $stripSlashes = FALSE, $recursion = 42) {
    if (is_array($parameter) && $recursion > 0) {
      foreach ($parameter as $name => $value) {
        $parameter[$name] = $this->prepareParameter($value, $stripSlashes, $recursion - 1);
      }
      return $parameter;
    } elseif (is_bool($parameter)) {
      return $parameter;
    } else {
      if ($stripSlashes) {
        $parameter = stripslashes($parameter);
      }
      return PapayaUtilStringUtf8::ensure($parameter);
    }
  }

  /**
  * Get encoded query string
  *
  * @return string
  */
  public function getQueryString($groupSeparator) {
    $query = new PapayaRequestParametersQuery($groupSeparator);
    $query->values($this);
    return $query->getString();
  }

  /**
  * Return the parameters as a flat array (name => value)
  *
  * @return array
  */
  public function getList($groupSeparator = '[]') {
    return $this->flattenArray((array)$this, $groupSeparator);
  }

  /**
  * Flatten the internal recursive array into a simple name => value list.
  *
  * @param array $parameters
  * @param string $groupSeparator
  * @param string $prefix
  * @param integer $maxRecursions
  * @return array
  */
  private function flattenArray($parameters, $groupSeparator, $prefix = '', $maxRecursions = 42) {
    $result = array();
    foreach ($parameters as $name => $value) {
      if (empty($prefix)) {
        $fullName = $name;
      } elseif ($groupSeparator == '[]' || empty($groupSeparator)) {
        $fullName = $prefix.'['.$name.']';
      } else {
        $fullName = $prefix.$groupSeparator.$name;
      }
      if (is_array($value)) {
        $result = PapayaUtilArray::merge(
          $result, $this->flattenArray($value, $groupSeparator, $fullName, $maxRecursions - 1)
        );
      } else {
        $result[$fullName] = (string)$value;
      }
    }
    return $result;
  }

  /**
  * ArrayAccess Interface: set value
  *
  * @param string|integer $offset
  * @param mixed $value
  */
  public function offsetSet($offset, $value) {
    return parent::offsetSet($this->_parseParameterName($offset), $value);
  }

  /**
  * ArrayAccess Interface: check if index exists
  *
  * @param string|integer $offset
  */
  public function offsetExists($offset) {
    return parent::offsetExists($this->_parseParameterName($offset));
  }

  /**
  * ArrayAccess Interface: remove value
  *
  * @param string|integer $offset
  */
  public function offsetUnset($offset) {
    parent::offsetUnset($this->_parseParameterName($offset));
  }

  /**
  * ArrayAccess Interface: get value
  *
  * If the value is an array, it will return a new instance of itself containing the array.
  *
  * @param string|integer $offset
  * @param mixed $value
  */
  public function offsetGet($offset) {
    $result = parent::offsetGet($this->_parseParameterName($offset));
    if (is_array($result)) {
      return new self($result);
    } else {
      return $result;
    }
  }

  /**
  * Check if the object contains data at all.
  *
  * @return boolean
  */
  public function isEmpty() {
    return count($this) < 1;
  }
}