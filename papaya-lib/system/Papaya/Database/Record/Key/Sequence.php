<?php
/**
* An single field key, provided by a sequence object, the sequence is created on the
* client side and the sequence object validates the existance in the database.
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
* @subpackage Database
* @version $Id: Sequence.php 36660 2012-01-20 16:35:41Z weinert $
*/

/**
* An single field key, provided by a sequence object, the sequence is created on the
* client side and the sequence object validates the existance in the database.
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Sequence.php 36660 2012-01-20 16:35:41Z weinert $
*/
class PapayaDatabaseRecordKeySequence implements PapayaDatabaseInterfaceKey {

  /**
  * Sequence object to create new identifiers
  *
  * @var PapayaDatabaseSequence
  */
  private $_sequence = NULL;

  /**
  * the property name of the identifier field
  *
  * @var string
  */
  private $_property = 'id';

  /**
  * the current field value
  *
  * @var NULL|string|integer
  */
  private $_value = NULL;

  /**
  * Create objecd and store sequence and property.
  *
  * @param PapayaDatabaseSequence $sequence
  * @param string $property
  */
  public function __construct(PapayaDatabaseSequence $sequence, $property = 'id') {
    $this->_sequence = $sequence;
    $this->_property = $property;
  }

  /**
  * Provide information about the key
  *
  * @var integer
  */
  public function getQualities() {
    return PapayaDatabaseInterfaceKey::CLIENT_GENERATED;
  }

  /**
  * Assign data to the key. This is an array because others keys can consist of multiple fields
  *
  * @param array $data
  */
  public function assign(array $data) {
    foreach ($data as $name => $value) {
      if ($name === $this->_property) {
        $this->_value = $value;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Validate if the record exists. In this case if the key value is not null it will
  * be considered as TRUE without asking the database.
  *
  * The key is provided by the key sequence object so it should always exists if it is set.
  *
  * @return boolean
  */
  public function exists() {
    return !empty($this->_value);
  }

  /**
  * Convert the key values into an string, that can be used in array keys.
  *
  * @return string
  */
  public function __toString() {
    return (string)$this->_value;
  }

  /**
  * Get the property names of the key. This will always be on property for an sequence key.
  *
  * @return array(string)
  */
  public function getProperties() {
    return array($this->_property);
  }

  /**
  * Get the a property=>value array to use it. A mapping is used to convert it into actual database
  * fields.
  *
  * If the filter for a create action (insert) is requested, a new id is created using the sequence
  * object.
  *
  * @param integer $for the action the filter ist fetched for
  * @return array(string)
  */
  public function getFilter($for = self::ACTION_FILTER) {
    if ($for == self::ACTION_CREATE) {
      return array($this->_property => $this->_sequence->next());
    }
    return array($this->_property => $this->_value);
  }
}