<?php
/**
* Papaya Database Object, superclass for classes with database access
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
* @version $Id: Object.php 38361 2013-04-04 12:09:41Z hapke $
*/

/**
* Papaya Database Object, superclass for classes with database access
* @package Papaya-Library
* @subpackage Database
*
* @method boolean databaseAddField() databaseAddField(string $table, array $fieldData)
* @method boolean databaseAddIndex() databaseAddIndex(string $table, array $index)
* @method boolean databaseChangeField() databaseChangeField(string $table, array $fieldData)
* @method boolean databaseChangeIndex() databaseChangeIndex(string $table, array $index)
* @method void databaseClose() databaseClose()
* @method true databaseCompareFieldStructure() databaseCompareFieldStructure(array $xmlField, array $databaseField)
* @method boolean databaseCompareKeyStructure() databaseCompareKeyStructure()
* @method boolean databaseCreateTable() databaseCreateTable(string $tableData, string $tablePrefix)
* @method void databaseDebugNextQuery() databaseDebugNextQuery(integer $count = 1)
* @method integer databaseDeleteRecord() databaseDeleteRecord(string $table, string|array $filter, mixed $value = NULL)
* @method boolean databaseDropField() databaseDropField(string $table, string $field)
* @method boolean databaseDropIndex() databaseDropIndex(string $table, string $name)
* @method void databaseEnableAbsoluteCount() databaseEnableAbsoluteCount()
* @method void databaseEmptyTable() databaseEmptyTable(string $table)
* @method string databaseEscapeString() databaseEscapeString(mixed $value)
* @method string databaseQuoteString() databaseQuoteString(mixed $value)
* @method string databaseGetProtocol() databaseGetProtocol()
* @method string databaseGetSqlSource() databaseGetSqlSource(string $function, array $params)
* @method string databaseGetSqlCondition() databaseGetSqlCondition(array $filter, $value = NULL, $operator = '=')
* @method integer|NULL databaseInsertRecord() databaseInsertRecord(string $table, string|NULL $idField, array $values = NULL)
* @method boolean databaseInsertRecords() databaseInsertRecords(string $table, array $values)
* @method boolean databaseQuery() databaseQuery(string $sql, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
* @method boolean databaseQueryFmt() databaseQueryFmt(string $sql, array $values, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
* @method boolean databaseQueryFmtWrite() databaseQueryFmtWrite(string $sql, array $values)
* @method boolean databaseQueryWrite() databaseQueryWrite(string $sql)
* @method integer databaseUpdateRecord() databaseUpdateRecord(string $table, array $values, array|string $filter, [mixed $value = NULL])
* @method array databaseQueryTableNames() databaseQueryTableNames()
* @method array databaseQueryTableStructure() databaseQueryTableStructure(string $tableName)
* @method array databaseGetTableName() databaseGetTableName($tableIdentifier, $usePrefix = TRUE)
* @method array databaseGetTimestamp() databaseGetTimestamp()
*/
class PapayaDatabaseObject
  extends PapayaObject
  implements PapayaDatabaseInterfaceAccess {

  /**
  * Database read uri
  * @var string|NULL
  */
  protected $databaseURI = NULL;

  /**
  * database write uri
  * @var string|NULL
  */
  protected $databaseURIWrite = NULL;

  /**
  * Stored database access object
  * @var PapayaDatabaseAccess
  */
  protected $_databaseAccessObject = NULL;

  /**
  * Set database access object
  * @param PapayaDatabaseAccess $databaseAccessObject
  */
  public function setDatabaseAccess(PapayaDatabaseAccess $databaseAccessObject) {
    $this->_databaseAccessObject = $databaseAccessObject;
  }

  /**
  * Get database access object
  * @return PapayaDatabaseAccess
  */
  public function getDatabaseAccess() {
    if (!isset($this->_databaseAccessObject)) {
      $this->_databaseAccessObject = new PapayaDatabaseAccess(
        $this, $this->databaseURI, $this->databaseURIWrite
      );
      $this->_databaseAccessObject->papaya($this->papaya());
    }
    return $this->_databaseAccessObject;
  }

  /**
  * Delegate calls to "database*" methods to the database access object
  *
  * @param string $functionName
  * @param array $arguments
  * @return mixed
  */
  public function __call($functionName, $arguments) {
    if (substr($functionName, 0, 8) == 'database') {
      $delegateFunction = strtolower($functionName[8]).substr($functionName, 9);
      $access = $this->getDatabaseAccess();
      return call_user_func_array(array($access, $delegateFunction), $arguments);
    } else {
      throw new BadMethodCallException(
        sprintf(
          'Invalid function call. Method %s::%s does not exist.',
          get_class($this),
          $functionName
        )
      );
    }
  }
}