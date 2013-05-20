<?php
/**
* Interface for mapper objects to convert a database fields into object properties and back
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
* Interface for mapper objects to convert a database fields into object properties and back
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Mapping.php 38281 2013-03-18 20:27:09Z weinert $
*/

interface PapayaDatabaseInterfaceMapping {

  /**
  * Map the database fields of an record to the object properties
  *
  * @param array $record
  * @return array
  */
  function mapFieldsToProperties(array $record);

  /**
  * Map the object properties to database fields
  *
  * @param array $values
  * @return array
  */
  function mapPropertiesToFields(array $values);

  /**
  * Get a list of the used database fields
  *
  * @return array
  */
  function getProperties();

  /**
  * Get a list of the used database fields
  *
  * @return array
  */
  function getFields();

  /**
  * Get the property name for a field
  *
  * @return string|FALSE
  */
  function getProperty($field);


  /**
  * Get the field name for a property
  *
  * @return string|FALSE
  */
  function getField($property);
}