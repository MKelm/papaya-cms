<?php
/**
* Mapper object to convert a database fields into object properties and back
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
* @version $Id: Mapping.php 38281 2013-03-18 20:27:09Z weinert $
*/

/**
* Mapper object to convert a database fields into object properties and back
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Mapping.php 38281 2013-03-18 20:27:09Z weinert $
*/
class PapayaDatabaseRecordMapping implements PapayaDatabaseInterfaceMapping {

  const PROPERTY_TO_FIELD = 1;
  const FIELD_TO_PROPERTY = 2;

  /**
  * Properties to fields mapping
  *
  * @var array(string=>string|NULL)
  */
  private $_properties = array();
  /**
  * Field to properties mapping
  *
  * @var array(string=>string|NULL)
  */
  private $_fields = array();
  /**
  * Field to properties mapping excluding table aliases, this is only used if the original
  * field name contains an . - indicating the use of an table alias.
  *
  * @var array(string=>string|NULL)
  */
  private $_fieldsWithoutAlias = array();

  /**
  * Callbacks to modify the mapping behaviour
  *
  * @var unknown_type
  */
  private $_callbacks = NULL;

  /**
  * Create object and define mapping
  *
  * @param array(string=>string|NULL) $definition
  */
  public function __construct(array $definition) {
    $this->setDefinition($definition);
  }

  /**
  * Define mapping
  *
  * @param array(string=>string|NULL) $definition
  */
  private function setDefinition($definition) {
    $this->_properties = array();
    $this->_fields = array();
    foreach ($definition as $property => $field) {
      $this->_properties[$property] = $field;
      if (!empty($field)) {
        if (isset($this->_fields[$field])) {
          throw new LogicException(
            sprintf(
              'Duplicate database field "%s" in mapping definition.',
              $field
            )
          );
        }
        $this->_fields[$field] = $property;
        if (FALSE !== ($position = strpos($field, '.'))) {
          $this->_fieldsWithoutAlias[substr($field, $position + 1)] = $property;
        }
      }
    }
  }

  /**
  * Map the database fields of an record to the object properties
  *
  * @param array $record
  * @return array
  */
  public function mapFieldsToProperties(array $record) {
    $values = array();
    if (isset($this->callbacks()->onBeforeMappingFieldsToProperties)) {
      $values = $this->callbacks()->onBeforeMappingFieldsToProperties(
        $values, $record
      );
    }
    if (isset($this->callbacks()->onBeforeMapping)) {
      $values = $this->callbacks()->onBeforeMapping(
        self::FIELD_TO_PROPERTY, $values, $record
      );
    }
    foreach ($record as $field => $value) {
      if ($property = $this->getProperty($field)) {
        if (isset($this->callbacks()->onMapValueFromFieldToProperty)) {
          $value = $this->callbacks()->onMapValueFromFieldToProperty(
            $property, $field, $value
          );
        }
        if (isset($this->callbacks()->onMapValue)) {
          $value = $this->callbacks()->onMapValue(
            self::FIELD_TO_PROPERTY, $property, $field, $value
          );
        }
        $values[$property] = $value;
      }
    }
    if (isset($this->callbacks()->onAfterMappingFieldsToProperties)) {
      $values = $this->callbacks()->onAfterMappingFieldsToProperties(
        $values, $record
      );
    }
    if (isset($this->callbacks()->onAfterMapping)) {
      $values = $this->callbacks()->onAfterMapping(
        self::FIELD_TO_PROPERTY, $values, $record
      );
    }
    return $values;
  }

  /**
  * Map the object properties to database fields
  *
  * @param array $values
  * @return array
  */
  public function mapPropertiesToFields(array $values) {
    $record = array();
    if (isset($this->callbacks()->onBeforeMappingPropertiesToFields)) {
      $record = $this->callbacks()->onBeforeMappingPropertiesToFields(
        $values, $record
      );
    }
    if (isset($this->callbacks()->onBeforeMapping)) {
      $record = $this->callbacks()->onBeforeMapping(
        self::PROPERTY_TO_FIELD, $values, $record
      );
    }
    foreach ($values as $property => $value) {
      if ($field = $this->getField($property)) {
        if (isset($this->callbacks()->onMapValueFromPropertyToField)) {
          $value = $this->callbacks()->onMapValueFromPropertyToField(
            $property, $field, $value
          );
        }
        if (isset($this->callbacks()->onMapValue)) {
          $value = $this->callbacks()->onMapValue(
            self::PROPERTY_TO_FIELD, $property, $field, $value
          );
        }
        $record[$field] = $value;
      }
    }
    if (isset($this->callbacks()->onAfterMappingPropertiesToFields)) {
      $record = $this->callbacks()->onAfterMappingPropertiesToFields(
        $values, $record
      );
    }
    if (isset($this->callbacks()->onAfterMapping)) {
      $record = $this->callbacks()->onAfterMapping(
        self::PROPERTY_TO_FIELD, $values, $record
      );
    }
    return $record;
  }

  /**
  * Get a list of the used properties
  *
  * @return array
  */
  public function getProperties() {
    return array_keys($this->_properties);
  }

  /**
  * Get a list of the used database fields
  *
  * @return array
  */
  public function getFields() {
    return array_keys($this->_fields);
  }

  /**
  * Get the database field name for a property
  *
  * @param string $property
  * @return string|NULL
  */
  public function getField($property) {
    $result = NULL;
    if (isset($this->callbacks()->onGetFieldForProperty)) {
      $result = $this->callbacks()->onGetFieldForProperty($property);
    }
    if (empty($result) && isset($this->_properties[$property])) {
      return $this->_properties[$property];
    }
    return $result;
  }

  /**
  * Get the property name for a database fields
  *
  * @param string $field
  * @return string|NULL
  */
  public function getProperty($field) {
    $result = NULL;
    if (isset($this->callbacks()->onGetPropertyForField)) {
      $result = $this->callbacks()->onGetPropertyForField($field);
    }
    if (empty($result)) {
      if (isset($this->_fields[$field])) {
        return $this->_fields[$field];
      } elseif (isset($this->_fieldsWithoutAlias[$field])) {
        return $this->_fieldsWithoutAlias[$field];
      }
    }
    return $result;
  }

  /**
  * Getter/Setter for the possible callbacks, to modify the behaviour of the mapping
  *
  * @param PapayaDatabaseRecordMappingCallbacks $callbacks
  * @return PapayaDatabaseRecordMappingCallbacks
  */
  public function callbacks(PapayaDatabaseRecordMappingCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    } elseif (is_null($this->_callbacks)) {
      $this->_callbacks = new PapayaDatabaseRecordMappingCallbacks();
    }
    return $this->_callbacks;
  }
}