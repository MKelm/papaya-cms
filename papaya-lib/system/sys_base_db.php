<?php
/**
* Implementation of the basic class for database access
*
* useful:
*   databaseDebugNextQuery($n) : shows explain for the next $n queries, call before query of course
* often used (no need for writing more SQL than you have to):
*   databaseInsertRecord       : inserts a record in the database
*     (may return the auto_incremented key if $key is not NULL)
*   databaseInsertRecords      : inserts multiple records in the database
*   databaseDeleteRecord       : deletes records from the database
*   databaseUpdateRecord       : updates an existing record in the database
*   databaseQueryFmtWrite      : use if default insert/updateRecord isn't enough
*   databaseQueryFmt           : use for select statements, the parameters will get
*     escaped automatically rarely used (for good reasons, you should know why, if you use these):
*   databaseQueryWrite         : inserts/updates; if you use this, make sure the input is sanitized!
*   databaseQuery              : selects; if you use this, make sure the input is sanitized!
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Database
* @version $Id: sys_base_db.php 38361 2013-04-04 12:09:41Z hapke $
*/

/**
* Aviable the basic class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_object.php');
/**
* Aviable the mistake catcher class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_error.php');

/**
* Object class for database access
*
* all other classes which have database access must be inherited from this one
*
* @package Papaya
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
* @method mixed databaseEmptyTable() databaseEmptyTable(string $table)
* @method string databaseEscapeString() databaseEscapeString(mixed $value)
* @method string databaseGetProtocol() databaseGetProtocol()
* @method string databaseGetSqlSource() databaseGetSqlSource(string $function, array $params)
* @method string databaseGetSqlCondition() databaseGetSqlCondition(array $filter, $value = NULL)
* @method integer|NULL databaseInsertRecord() databaseInsertRecord(string $table, string|NULL $idField, array $values = NULL)
* @method boolean databaseInsertRecords() databaseInsertRecords(string $table, array $values)
* @method boolean databaseQuery() databaseQuery(string $sql, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
* @method boolean databaseQueryFmt() databaseQueryFmt(string $sql, array $values, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
* @method boolean databaseQueryFmtWrite() databaseQueryFmtWrite(string $sql, array $values)
* @method boolean databaseQueryWrite() databaseQueryWrite(string $sql)
* @method integer databaseUpdateRecord() databaseUpdateRecord(string $table, array $values, array|string $filter, [mixed $value = NULL])
* @method array databaseQueryTableNames() databaseQueryTableNames()
* @method array databaseQueryTableStructure() databaseQueryTableStructure(string $tableName)
*/
class base_db extends base_object {

  /**
  * Database access object
  * @var PapayaDatabaseAccess $_databaseAccessObject
  */
  var $_databaseAccessObject = NULL;

  /**
  * Database URI, default value ist the option PAPAYA_DB_URI
  * @var string $databaseURI
  */
  var $databaseURI = NULL;
  /**
  * Database URI for insert/update/..., default value ist the option PAPAYA_DB_URI_WRITE
  * @var string $databaseURIWrite
  */
  var $databaseURIWrite = NULL;

  /**
  * Set database access object
  * @return PapayaDatabaseAccess
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

  /**
  * Old function name for backwards compatibility
  *
  * @param mixed $value Value to escape
  * @access public
  * @return string escaped value.
  */
  function escapeStr($value) {
    return $this->databaseEscapeString($value);
  }

  /**
  * Compare new values with current values (from db)
  *
  * @param array &$newValues
  * @param array &$dbValues
  * @access public
  * @return boolean
  */
  function checkDataModified(&$newValues, &$dbValues) {
    if (isset($newValues) && is_array($newValues)) {
      foreach ($newValues as $key => $val) {
        if (isset($dbValues[$key]) && $dbValues[$key] != $newValues[$key]) {
          return TRUE;
        } elseif (!isset($dbValues[$key])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}
?>