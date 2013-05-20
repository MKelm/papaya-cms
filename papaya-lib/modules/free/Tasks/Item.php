<?php
/**
* Database wrapper for a single task item
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
* @version $Id: Item.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Database wrapper for a single task item
*
* @package Papaya-Modules
* @subpackage Tasks
*/
class PapayaModuleTasksItem extends PapayaDatabaseObjectRecord {

  /**
  * New tasks get a created status
  * @var integer
  */
  const TASK_CREATED = 1;
  /**
  * After the task has been confirmed
  * @var integer
  */
  const TASK_CONFIRMED = 2;
  /**
  * If a task has been declined
  * @var integer
  */
  const TASK_DECLINED = 3;

  /**
  * Field definition (properties and array keys the data is mapped to)
  *
  * @var array
  */
  protected $_fields = array(
    'id' => 'tasks_item_id',
    'status' => 'tasks_item_status',
    'created' => 'tasks_item_created',
    'modified' => 'tasks_item_modified',
    'modified_by' => 'tasks_item_modified_by',
    'title' => 'tasks_item_title',
    'description' => 'tasks_item_description',
    'guid' => 'tasks_item_guid',
    'data' => ''
  );

  /**
  * Sequence object
  * @var PapayaDatabaseSequence
  */
  protected $_sequence = NULL;

  /**
  * Load database record into object
  *
  * @param string $id
  */
  public function load($id) {
    $sql = "SELECT tasks_item_id, tasks_item_status,
                   tasks_item_created, tasks_item_modified, tasks_item_modified_by,
                   tasks_item_title, tasks_item_description,
                   tasks_item_guid, tasks_item_data
              FROM %s
             WHERE tasks_item_id = '%s'";
    $parameters = array(
      $this->databaseGetTableName('tasks_items'),
      $id
    );
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      if ($row = $res->fetchRow(PapayaDatabaseResult::FETCH_ASSOC)) {
        $this->_values = $this->convertRecordToValues($row);
        $this->_values['data'] = PapayaUtilStringXml::unserializeArray($row['tasks_item_data']);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Save object to database
  *
  * return string|boolean
  */
  public function save() {
    if (empty($this['id'])) {
      return $this->_insert();
    } else {
      return $this->_update();
    }
  }

  /**
  * Sequence object setter
  *
  * @param PapayaDatabaseSequence $sequence
  */
  public function setSequence(PapayaDatabaseSequence $sequence) {
    $this->_sequence = $sequence;
  }

  /**
  * Sequence object getter with implizit create
  *
  * @return PapayaDatabaseSequence
  */
  public function getSequence() {
    if (is_null($this->_sequence)) {
      $this->_sequence = new PapayaDatabaseSequenceHuman(
        $this->databaseGetTableName('tasks_items'), 'tasks_item_id', 10
      );
      $this->_sequence->setDatabaseAccess($this->getDatabaseAccess());
    }
    return $this->_sequence;
  }

  /**
  * Insert object data into database
  *
  * @return string|FALSE
  */
  private function _insert() {
    $now = time();
    $data = array(
      'tasks_item_id' => $this->getSequence()->next(),
      'tasks_item_status' => (int)$this['status'] = self::TASK_CREATED,
      'tasks_item_created' => $this['created'] = $now,
      'tasks_item_modified' => $this['modified'] = $now,
      'tasks_item_modified_by' => $this->_getCurrentUser(),
      'tasks_item_title' => (string)$this['title'],
      'tasks_item_description' => (string)$this['description'],
      'tasks_item_guid' => (string)$this['guid'],
      'tasks_item_data' => PapayaUtilStringXml::serializeArray($this['data']),
    );
    $tableName = $this->databaseGetTableName('tasks_items');
    if (FALSE !== $this->databaseInsertRecord($tableName, NULL, $data)) {
      return $this['id'] = $data['tasks_item_id'];
    }
    return FALSE;
  }

  /**
  * Update object data in database
  *
  * @return boolean
  */
  private function _update() {
    $data = array(
      'tasks_item_status' => (int)$this['status'],
      'tasks_item_modified' => $this['modified'] = time(),
      'tasks_item_modified_by' => $this->_getCurrentUser(),
      'tasks_item_title' => (string)$this['title'],
      'tasks_item_description' => (string)$this['description'],
      'tasks_item_guid' => (string)$this['guid'],
      'tasks_item_data' => PapayaUtilStringXml::serializeArray($this['data']),
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->databaseGetTableName('tasks_items'), $data, 'tasks_item_id', $this['id']
    );
  }

  /**
  * Get the id of the current administration user if here is any.
  *
  * @return string
  */
  private function _getCurrentUser() {
    $application = $this->papaya();
    if (isset($application->administrationUser)) {
      return $application->administrationUser->getUserId();
    }
    return '';
  }
}