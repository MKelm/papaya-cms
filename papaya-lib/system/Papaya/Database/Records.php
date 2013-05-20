<?php
/**
* Papaya Database List, represents a list of records fetched from the database.
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Records.php 37722 2012-11-26 18:42:23Z weinert $
*/

/**
* Papaya Database List, represents a list of records fetched from the database.
*
* @package Papaya-Library
* @subpackage Database
*/
abstract class PapayaDatabaseRecords
  extends PapayaDatabaseRecordsUnbuffered
  implements ArrayAccess {

  /**
  * internal storage for the record da after mapping.
  *
  * @var array
  */
  protected $_records = array();

  /**
  * An array of properties, used to compile the identifer
  *
  * @var array(string)
  */
  protected $_identifierProperties = array();

  /**
  * The parts of an identifer a joined using the given separator string
  *
  * @var string
  */
  protected $_identifierSeparator = '|';

  /**
  * Load records from the defined table. This method can be overloaded to define an own sql.
  *
  * @param array(string=>scalar)|scalar $filter If it is an scalar the value will be used
  *   for the id property.
  * @param integer|NULL $limit
  * @param integer|NULL $offset
  */
  public function load($filter = array(), $limit = NULL, $offset = NULL) {
    $fields = implode(', ', $this->mapping()->getFields());
    $sql = "SELECT $fields FROM %s";
    $sql .= PapayaUtilString::escapeForPrintf(
      $this->_compileCondition($filter).$this->_compileOrderBy()
    );
    $parameters = array(
      $this->getDatabaseAccess()->getTableName($this->_tableName, $this->_useTablePrefix)
    );
    return $this->_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
  }

  /**
  * A protected method that does the actual loading. The separation allows to overload load, to
  * create and own logic that defines the sql and parameters.
  *
  * @param string $sql
  * @param array $parameters
  * @param integer|NULL $limit
  * @param integer|NULL $offset
  * @param array $idProperties if set the defined fields are used to create the keys for the
  *    records array. If it is an empty array the records array will be a list.
  */
  protected function _loadRecords($sql, $parameters, $limit, $offset, $idProperties = array()) {
    $this->reset();
    if ($this->_loadSql($sql, $parameters, $limit, $offset)) {
      foreach ($this->getResultIterator() as $values) {
        $identifier = $this->getIdentifier($values, $idProperties);
        if (isset($identifier)) {
          $this->_records[$identifier] = $values;
        } else {
          $this->_records[] = $values;
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Reset the object to "unloaded" status
  */
  public function reset() {
    $this->_records = array();
  }

  /**
  * Return the current count of records in the internal buffer
  *
  * @return integer
  */
  public function count() {
    return count($this->_records);
  }

  /**
  * Return loaded records as array
  *
  * @return array
  */
  public function toArray() {
    return $this->_records;
  }

  /**
  * Get an iterator for the loaded records.
  *
  * @return ArrayIterator
  */
  public function getIterator() {
    return empty($this->_records) ? new EmptyIterator() : new ArrayIterator($this->_records);
  }

  /**
  * return true if an record with the given offset/identifier exists
  *
  * @param array|scalar $offset
  * @return boolean
  */
  public function offsetExists($offset) {
    return isset($this->_records[$this->getIdentifier($offset)]);
  }

  /**
  * return the record data of on result row.
  *
  * @param array|scalar $offset
  * @return array
  */
  public function offsetGet($offset) {
    return $this->_records[$this->getIdentifier($offset)];
  }

  /**
  * This is an encapsulation of the database result, you can not change it.
  *
  * @param array|scalar $offset
  * @param mixed $value
  */
  public function offsetSet($offset, $value) {
    PapayaUtilConstraints::assertArray($value);
    $identifier = $this->getIdentifier($offset);
    foreach ($this->mapping()->getProperties() as $property) {
      if (isset($value[$property])) {
        $record[$property] = $value[$property];
      } else {
        $record[$property] = NULL;
      }
    }
    if (isset($identifier)) {
      $this->_records[$identifier] = $record;
    } else {
      $this->_records[] = $record;
    }
  }

  /**
  * This is an encapsulation of the database result, you can not change it.
  *
  * @param array|scalar $offset
  */
  public function offsetUnset($offset) {
    $identifier = $this->getIdentifier($offset);
    if (isset($this->_records[$this->getIdentifier($offset)])) {
      unset($this->_records[$this->getIdentifier($offset)]);
    }
  }

  /**
  * Compiles different kind of values into an string identifier. If the filter is given
  * only the properties defined in the filter (corresponding to keys in the values array) are
  * used. If the $filter argument is an empty array the method returns NULL.
  *
  * If the $filter argument is NULL, all values in the $valeus argument are used.
  *
  * @param NULL|scalar|array $values
  * @param NULL|scalar|array $filter
  */
  protected function getIdentifier($values, $filter = NULL) {
    if (isset($filter)) {
      if (!is_array($filter)) {
        $filter = array($filter);
      }
      if (empty($filter)) {
        return NULL;
      } else {
        $identifier = array();
        foreach ($filter as $property) {
          if (isset($values[$property])) {
            $identifier[] = $values[$property];
          } else {
            throw new UnexpectedValueException(
              sprintf(
                'The property "%s" was not found, but is needed to create the identifier.',
                $property
              )
            );
          }
        }
        return implode($this->_identifierSeparator, $identifier);
      }
    } elseif (is_array($values)) {
      return implode($this->_identifierSeparator, $values);
    } else {
      return $values;
    }
  }
}