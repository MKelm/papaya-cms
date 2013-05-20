<?php
/**
* Database filter for task item lists
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
* @package Papaya-Modules
* @subpackage Tasks
* @version $Id: Filter.php 35920 2011-07-15 08:42:03Z hallerbach $
*/

/**
* Database filter conditions for task item lists
*
* @package Papaya-Modules
* @subpackage Tasks
*/
class PapayaModuleTasksFilter extends PapayaDatabaseObject {

  protected $_filterParams = array();

  /**
  * The array with query conditions
  *
  * @var array
  */
  protected $_conditions = array();

  /**
  * The database object
  *
  * @var PapayaDatabaseObject
  */
  protected $_databaseAccessObject = NULL;

  /**
  * The contructor
  *
  * @param array $filterParams
  */
  public function __construct($filterParams = array()) {
    $this->_filterParams = $filterParams;
  }

  /**
  * Set the filter params array
  *
  * @param array $filterParams
  */
  public function setFilterParams($filterParams = array()) {
    $this->_filterParams = $filterParams;
  }

  /**
  * Returns the query condition
  *
  * @return string
  */
  public function getFilterConditions() {
    if (isset($this->_filterParams['id-starts-with'])) {
      $this->_conditions[] = $this->_getTaskItemIdCondition();
    }
    if (isset($this->_filterParams['status']) && $this->_filterParams['status'] != 0) {
      $this->_conditions[] = $this->_getTaskItemStatusCondition();
    }
    if (isset($this->_filterParams['data-contains'])) {
      $this->_conditions[] = $this->_getTaskItemDataCondition();
    }
    if (isset($this->_filterParams['time-from']) || isset($this->_filterParams['time-to'])) {
      $this->_conditions[] = $this->_getTimeframeCondition();
    }
    if (isset($this->_filterParams['task-guid']) && ($this->_filterParams['task-guid'] != '0')) {
      $this->_conditions[] = $this->_getTaskItemGuidCondition();
    }
    if (count($this->_conditions) > 0) {
      return " WHERE ".implode(" AND ", $this->_conditions);
    }
    return '';
  }

  /**
  * Return the condition for the search by id
  *
  * @return string
  */
  protected function _getTaskItemIdCondition() {
    return sprintf(
      "tasks_item_id LIKE '%s%%'",
      $this->databaseEscapeString($this->_filterParams['id-starts-with'])
    );
  }

  /**
  * Return the condition for the search by status
  *
  * @return string
  */
  protected function _getTaskItemStatusCondition() {
    return sprintf(
      "tasks_item_status = '%d'",
      $this->_filterParams['status']
    );
  }

  /**
  * Return the condition for the search in the data field
  *
  * @return string
  */
  protected function _getTaskItemDataCondition() {
    return sprintf(
      "(tasks_item_data LIKE '%%%1\$s%%'
      OR tasks_item_title LIKE '%%%1\$s%%'
      OR tasks_item_description LIKE '%%%1\$s%%')",
      $this->databaseEscapeString($this->_filterParams['data-contains'])
    );
  }

  /**
  * Return the condition for the search with module guid
  *
  * @return string
  */
  protected function _getTaskItemGuidCondition() {
    return sprintf(
      "tasks_item_guid = '%s'",
      $this->databaseEscapeString($this->_filterParams['task-guid'])
    );
  }


  /**
  * Return the condition for the search in a given timeframe
  *
  * @return string
  */
  protected function _getTimeframeCondition() {
    if (preg_match(
      '(^([0-9]{4})-([0-9]{2})-([0-9]{2})$)', $this->_filterParams['time-from'], $matches)) {
      $timeFrom = gmmktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
    } else {
      $timeFrom = 0;
    }

    if (preg_match(
      '(^([0-9]{4})-([0-9]{2})-([0-9]{2})$)', $this->_filterParams['time-to'], $matches)) {
      $timeTo = gmmktime(0, 0, 0, $matches[2], $matches[3] + 1, $matches[1]);
    } else {
      $timeTo = $this->_getCurrentTime();
    }
    return sprintf(
      "tasks_item_created between %d AND %d",
      $timeFrom,
      $timeTo
    );
  }

  /**
  * Loads all the different module guids to generate a filter
  *
  * @return array
  */
  public function getModuleGuids() {
    $result = array();
    $sql = "SELECT DISTINCT module_guid, module_title
              FROM %s
             WHERE module_guid IN (
               SELECT DISTINCT (tasks_item_guid)
                 FROM %s
               )
          ORDER BY module_title ASC";
    $parameters = array(
      $this->databaseGetTableName('modules'),
      $this->databaseGetTableName('tasks_items')
    );
    if (FALSE != ($res = $this->databaseQueryFmt($sql, $parameters))) {
      while ($row = $res->fetchRow(PapayaDatabaseResult::FETCH_ASSOC)) {
        $result[$row['module_guid']] = $row['module_title'];
      }
    }
    return $result;
  }

  /**
  * Returns the current timestamp
  */
  protected function _getCurrentTime() {
    return time();
  }

}