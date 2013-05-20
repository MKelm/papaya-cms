<?php
/**
* Implementes a generic task/todo list for confirmable actions
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
* @version $Id: Manager.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Implementes a generic task/todo list for confirmable actions
*
* First an module adds a new task and gets an id. Then the taks is confirmed/declined from the
* administration interface.
*
* @package Papaya-Modules
* @subpackage Tasks
*/
class PapayaModuleTasksManager extends PapayaObject {

  /**
  * Create a new task object
  *
  * @return PapayaModuleTasksItem
  */
  public function createTaskObject() {
    return new PapayaModuleTasksItem();
  }


  /**
  * Create a new task object
  *
  * @return PapayaModuleTasksItem
  */
  public function createTaskList() {
    return new PapayaModuleTasksList();
  }

  /**
  * Adds a new task for all administration users/groups.
  *
  * Title and description are used to provide informations about the task. The guid identifies a
  * connector module which has the methods acceptTaks() and declineTask().
  *
  * The $data array is serialized, stored and used as a parameter for the specified connector.
  *
  * @param string $title
  * @param string $description
  * @param string $guid
  * @param array $data
  */
  public function add($title, $description, $guid, array $data) {
    $item = $this->createTaskObject();
    $item['title'] = $title;
    $item['description'] = $description;
    $item['guid'] = $guid;
    $item['data'] = $data;
    return $item->save();
  }

  /**
  * Loads and returns the all informatiosn about a task
  *
  * @param string $id
  */
  public function get($id) {
    $item = $this->createTaskObject();
    if ($item->load($id)) {
      return $item;
    } else {
      return NULL;
    }
  }

  /**
  * Confirm a task
  *
  * Loads the task, calls the specified connector and sets it to confirmed if the confirmTask method
  * of the connector returned TRUE.
  *
  * @param string $id
  */
  public function confirm($id) {
    $result = FALSE;
    if ($item = $this->get($id)) {
      if (empty($item['guid'])) {
        $result = TRUE;
      } elseif ($taskHandler = $this->getTaskPlugin($item['guid'])) {
        $result = $taskHandler->confirmTask($item['data']);
      }
      if ($result) {
        $item['status'] = PapayaModuleTasksItem::TASK_CONFIRMED;
        return FALSE !== $item->save();
      }
    }
    return FALSE;
  }

  /**
  * Decline a task
  *
  * Loads the task, calls the specified connector and sets it to declined if the declineTask method
  * of the connector returned TRUE.
  *
  * @param string $id
  */
  public function decline($id) {
    $result = FALSE;
    if ($item = $this->get($id)) {
      if (empty($item['guid'])) {
        $result = TRUE;
      } elseif ($taskHandler = $this->getTaskPlugin($item['guid'])) {
        $result = $taskHandler->declineTask($item['data']);
      }
      if ($result) {
        $item['status'] = PapayaModuleTasksItem::TASK_DECLINED;
        return FALSE !== $item->save();
      }
    }
    return FALSE;
  }

  /**
  * Deletes task from database table, the status is not important
  *
  * @param string $id
  */
  public function delete($id) {
    return $this->createTaskList()->delete($id);
  }

  /**
  * Get task list database access object with loaded records
  *
  * @param integer|NULL $limit
  * @param integer|NULL $offset
  * @param array|NULL $filter
  * @param string $sort
  * @return PapayaModuleTasksList|NULL
  */
  public function getList($limit = NULL, $offset = NULL, $filter = NULL, $orderBy = 'desc') {
    $list = $this->createTaskList();
    if ($list->load($limit, $offset, $filter, $orderBy)) {
      return $list;
    }
    return NULL;
  }

  /**
  * Get the task handler plugin using the plugin loader
  *
  * @param string $guid
  * @return Object|NULL
  */
  public function getTaskPlugin($guid) {
    return $this->papaya()->plugins->getPluginInstance($guid, $this);
  }
}