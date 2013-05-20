<?php
/**
* Database access for task item lists
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
* @version $Id: List.php 35920 2011-07-15 08:42:03Z hallerbach $
*/

require_once(dirname(__FILE__).'/Filter.php');

/**
* Database access for task item lists
*
* @package Papaya-Modules
* @subpackage Tasks
*/
class PapayaModuleTasksList
  extends PapayaDatabaseObjectList {

  /**
  * Map field names to value identfiers
  *
  * @var array
  */
  protected $_fieldMapping = array(
    'tasks_item_id' => 'id',
    'tasks_item_status' => 'status',
    'tasks_item_created' => 'created',
    'tasks_item_modified' => 'modified',
    'tasks_item_title' => 'title'
  );

  /**
  * the filter object generates the conditions for database queries
  *
  * @var PapayaModuleTasksFilter
  */
  protected $_filterObject = NULL;

  /**
  * Load task item records from database
  *
  * @param integer|NULL $limit
  * @param integer|NULL $offset
  * @param array|NULL $filter
  * @param string $orderBy
  * @return boolean
  */
  public function load($limit = NULL, $offset = NULL, $filter = NULL, $orderBy = 'DESC') {
    $this->_records = array();
    $this->_recordCount = 0;
    $filterObject = $this->_getFilterObject($filter);
    $filterCondition = $filterObject->getFilterConditions();
    $sql = sprintf(
      "SELECT tasks_item_id, tasks_item_status,
              tasks_item_created, tasks_item_modified,
              tasks_item_title
         FROM %s
              %s
        ORDER BY tasks_item_created %s, tasks_item_title ASC",
      $this->databaseGetTableName('tasks_items'),
      $filterCondition,
      ($orderBy == 'asc') ? 'ASC' : 'DESC'
    );
    if ($res = $this->databaseQuery($sql, $limit, $offset)) {
      $this->_fetchRecords($res, 'tasks_item_id');
      $this->_recordCount = $res->absCount();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Delete task item record from database
  *
  * @param string $id
  * @return boolean
  */
  public function delete($id) {
    return FALSE !== $this->databaseDeleteRecord(
      $this->databaseGetTableName('tasks_items'), 'tasks_item_id', $id
    );
  }

  /**
  * Get the filter class object
  *
  * @return PapayaModuleTasksFilter
  */
  protected function _getFilterObject($filterParams = array()) {
    if (NULL === $this->_filterObject) {
      $this->_filterObject = new PapayaModuleTasksFilter($filterParams);
    }
    return $this->_filterObject;
  }

  /**
  * Set the filter class object
  *
  * @param PapayaModuleTasksFilter
  */
  protected function _setFilterObject(PapayaModuleTasksFilter $filterObject) {
    $this->_filterObject = $filterObject;
  }
}