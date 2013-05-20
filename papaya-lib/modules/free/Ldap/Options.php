<?php
/**
* LDAP Options Class
*
* This File contains the class <var>Options.php</var>
*
* @copyright by papaya Software GmbH, Cologne, Germany - All rights reserved.
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Ldap
* @version $Id: Options.php 38419 2013-04-18 21:51:53Z kersken $

/**
* LDAP Options Class
*
* This class wraps a set of options to be set on an LDAP connection using ldap_set_option()
*
* @package Papaya-Modules
* @subpackage Free-Ldap
*/
class PapayaModuleLdapOptions implements ArrayAccess, Iterator, Countable {
  /**
  * Mapping of string option names to option values
  * @var array
  */
  private $optionMapping = array();

  /**
  * The actual set of options currently set
  * @var array
  */
  protected $options = array(
    LDAP_OPT_PROTOCOL_VERSION => 3
  );

  /**
  * The cursor, used by the iterator
  * @var integer
  */
  private $cursor = 0;

  /**
  * Constructor
  *
  * @param array $options optional, default empty array
  */
  public function __construct($options = array()) {
    $availableOptions = array(
      'LDAP_OPT_DEREF',
      'LDAP_OPT_SIZELIMIT',
      'LDAP_OPT_TIMELIMIT',
      'LDAP_OPT_NETWORK_TIMEOUT',
      'LDAP_OPT_PROTOCOL_VERSION',
      'LDAP_OPT_ERROR_NUMBER',
      'LDAP_OPT_REFERRALS',
      'LDAP_OPT_RESTART',
      'LDAP_OPT_HOST_NAME',
      'LDAP_OPT_ERROR_STRING',
      'LDAP_OPT_MATCHED_DN',
      'LDAP_OPT_SERVER_CONTROLS',
      'LDAP_OPT_CLIENT_CONTROLS',
      'LDAP_OPT_DEBUG_LEVEL'
    );
    foreach ($availableOptions as $option) {
      if (defined($option)) {
        $this->optionMapping[$option] = constant($option);
      }
    }
    foreach ($options as $key => $value) {
      $this[$key] = $value;
    }
  }

  /**
  * Check whether an option is set
  *
  * @param integer $offset
  * @return boolean
  */
  public function offsetExists($offset) {
    if (in_array($offset, array_values($this->optionMapping))) {
      return isset($this->options[$offset]);
    }
    return FALSE;
  }

  /**
  * Get an option value by key
  *
  * @param mixed $offset (string or integer)
  * @throws InvalidArgumentException
  * @return mixed
  */
  public function offsetGet($offset) {
    $offsetValue = $this->getConstantValue($offset);
    if (!in_array($offsetValue, array_values($this->optionMapping))) {
      throw new InvalidArgumentException(sprintf('%s is not a valid LDAP option.', $offset));
    }
    $result = NULL;
    if ($this->offsetExists($offsetValue)) {
      $result = $this->options[$offsetValue];
    }
    return $result;
  }

  /**
  * Set an option
  *
  * @param mixed $offset (string or integer)
  * @param mixed $value
  * @throws InvalidArgumentException
  */
  public function offsetSet($offset, $value) {
    $offsetValue = $this->getConstantValue($offset);
    if (!in_array($offsetValue, array_values($this->optionMapping))) {
      throw new InvalidArgumentException(sprintf('%s is not a valid LDAP option.', $offset));
    }
    $this->options[$offsetValue] = $value;
  }

  /**
  * Remove an option
  *
  * @param mixed $offset (string or integer)
  * @throws InvalidArgumentException
  */
  public function offsetUnset($offset) {
    $offsetValue = $this->getConstantValue($offset);
    if (!in_array($offsetValue, array_values($this->optionMapping))) {
      throw new InvalidArgumentException(sprintf('%s is not a valid LDAP option.', $offset));
    }
    unset($this->options[$offsetValue]);
  }

  /**
  * Rewind the cursor on the internal iterator
  *
  */
  public function rewind() {
    $this->cursor = 0;
  }

  /**
  * Get the current value in iteration
  *
  */
  public function current() {
    $keys = array_keys($this->options);
    return $this->options[$keys[$this->cursor]];
  }

  /**
  * Get the current key in iteration
  *
  */
  public function key() {
    $keys = array_keys($this->options);
    return $keys[$this->cursor];
  }

  /**
  * Advance the cursor by one step
  *
  */
  public function next() {
    $this->cursor++;
  }

  /**
  * Check whether an element at current cursor position exists
  *
  */
  public function valid() {
    $keys = array_keys($this->options);
    return isset($keys[$this->cursor]);
  }

  /**
  * Return the current number of set options
  *
  */
  public function count() {
    return count($this->options);
  }
  /**
  * Check whether a value is a permitted option
  *
  * @param mixed $offset (string or integer)
  * @return boolean
  */
  public function isPermittedOption($offset) {
    $offsetValue = $this->getConstantValue($offset);
    return in_array($offsetValue, array_values($this->optionMapping));
  }

  /**
  * If an offset is a string, get the corresponding constant value
  *
  * @param mixed $offset (string or integer)
  * @return integer
  */
  protected function getConstantValue($offset) {
    $result = $offset;
    if (is_string($offset)) {
      $result = 0;
      if (isset($this->optionMapping[$offset])) {
        $result = $this->optionMapping[$offset];
      }
    }
    return $result;
  }
}
