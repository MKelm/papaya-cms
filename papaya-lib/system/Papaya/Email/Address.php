<?php
/**
* A single Email adress, inclusing properties for the parts and string casting.
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Email
* @version $Id: Address.php 35516 2011-03-07 12:30:35Z weinert $
*/

/**
* A single Email adress, inclusing properties for the parts and string casting.
*
* @package Papaya-Library
* @subpackage Email
*/
class PapayaEmailAddress {

  /**
  * Recipient name
  *
  * @var string
  */
  private $_name = '';

  /**
  * Recipient email
  *
  * @var unknown_type
  */
  private $_email = '';

  /**
  * Initialize object wiht address if provided.
  *
  * @param string $address
  */
  public function __construct($address = NULL) {
    if (isset($address)) {
      $this->setAddress($address);
    }
  }

  /**
  * Cast object to string. Returns "email" or "name <email>".
  *
  * @return string
  */
  public function __toString() {
    if (empty($this->_name)) {
      return $this->_email;
    } else {
      return $this->_name.' <'.$this->_email.'>';
    }
  }

  /**
  * Set address from string (can include a name)
  *
  * @return string
  */
  protected function setAddress($address) {
    if (preg_match('~^\s*(.*?)\s*<([^>]+)>~', $address, $matches)) {
      $this->_name = $matches[1];
      $this->_email = $matches[2];
    } else {
      $this->_email = $address;
    }
  }

  /**
  * Set recipient name
  *
  * @param string $name
  */
  protected function setName($name) {
    $this->_name = $name;
  }

  /**
  * Dynamic property setter
  *
  * @param string $name
  * @param string $value
  */
  public function __set($name, $value) {
    switch ($name) {
    case 'name' :
      $this->setName($value);
      return;
    case 'email' :
    case 'address' :
      $this->setAddress($value);
      return;
    }
    throw new InvalidArgumentException(
      sprintf('InvalidArgumentException: Unknown property "%s".', $name)
    );
  }

  /**
  * Dynamic property getter
  *
  * @param string $name
  */
  public function __get($name) {
    switch ($name) {
    case 'name' :
      return $this->_name;
    case 'email' :
      return $this->_email;
    case 'address' :
      return $this->__toString();
    }
    throw new InvalidArgumentException(
      sprintf('InvalidArgumentException: Unknown property "%s".', $name)
    );
  }
}