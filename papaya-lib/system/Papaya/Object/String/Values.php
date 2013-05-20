<?php
/**
* A list of strings that is castable to a string using a default offset
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
* @subpackage Objects
* @version $Id: Values.php 38394 2013-04-12 15:54:39Z weinert $
*/

/**
* A list of strings that is castable to a string using a default offset
*
* If you cast is to a string the default element will be casted to string and returned.
*
* But you can treat it like an array, too.
*
* @package Papaya-Library
* @subpackage Objects
*/
class PapayaObjectStringValues implements ArrayAccess, Countable, IteratorAggregate {

  private $_values = NULL;
  private $_defaultOffset = 0;

  /**
   * Create object store values and default offset
   *
   * @param scalar|array|Traversable $values
   * @param scalar $defaultOffset
   */
  public function __construct($values = array(), $defaultOffset = 0) {
    $this->_defaultOffset = $defaultOffset;
    $this->assign($values);
  }

  /**
   * Assign values to the internal list, if it is a single value it will be added as only value
   * to the list using the default offset.
   *
   * @param scalar|array|Traversable $values
   */
  public function assign($values) {
    $this->_values = new \ArrayObject;
    if (is_array($values) || $values instanceOf Traversable) {
      foreach ($values as $key => $value) {
        $this->_values[$key] = (string)$value;
      }
    } else {
      $this->_values[$this->_defaultOffset] = (string)$values;
    }
  }

  /**
   * Get the value with the provided offset, if the value does not exists the provided default
   * value ist returned.
   *
   * @param unknown $offset
   * @param string $defaultValue
   * @return Ambigous <string, mixed>
   */
  public function get($offset, $defaultValue = NULL) {
    return $this->offsetExists($offset) ? $this->offsetGet($offset) : $defaultValue;
  }

  /**
   * Fetches the element specified by the default offset and returns it as string
   *
   * @return string
   */
  public function __toString() {
    return (string)$this->get($this->_defaultOffset, '');
  }

  /**
   * @see ArrayAccess::offsetExists()
   */
  public function offsetExists($offset) {
    return $this->_values->offsetExists($offset);
  }

  /**
   * @see ArrayAccess::offsetGet()
   */
  public function offsetGet($offset) {
    return $this->_values->offsetGet($offset);
  }

  /**
   * @see ArrayAccess::offsetSet()
   */
  public function offsetSet($offset, $value) {
    $this->_values->offsetSet($offset, $value);
  }

  /**
   * @see ArrayAccess::offsetUnset()
   */
  public function offsetUnset($offset) {
    $this->_values->offsetUnset($offset);
  }

  /**
   * @see Countable::count()
   */
  public function count() {
    return count($this->_values);
  }

  /**
   * @see IteratorAggregate::getIterator()
   */
  public function getIterator() {
    return $this->_values->getIterator();
  }
}
