<?php
/**
* papaya action dispatcher, base class
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
* @package Papaya-Modules
* @subpackage Free-Actions
* @version $Id: base_actions.php 37760 2012-11-30 16:34:25Z weinert $
*/

/**
* Base class base_db
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* papaya action dispatcher, base class
*
* @package Papaya-Modules
* @subpackage Free-Actions
*/
class base_actions extends base_db {
  /**
  * papaya database table action groups
  * @var string $tableGroups
  */
  var $tableGroups = '';

  /**
  * papaya database table actions
  * @var string $tableActions
  */
  var $tableActions = '';

  /**
  * papaya database table action observers
  * @var string $tableObservers
  */
  var $tableObservers = '';

  /**
  * papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;

  /**
  * Constructor
  *
  * @param &string $msgs
  * @param string $paramName optional, default 'act'
  */
  function __construct(&$msgs, $paramName = 'act') {
    $this->paramName = $paramName;
    // Action dispatcher database tables
    $this->tableGroups = PAPAYA_DB_TABLEPREFIX.'_action_groups';
    $this->tableActions = PAPAYA_DB_TABLEPREFIX.'_actions';
    $this->tableObservers = PAPAYA_DB_TABLEPREFIX.'_action_observers';
  }

  /**
  * PHP4 constructor
  *
  * @param &string $msgs
  * @param string $paramName optional, default 'act'
  */
  function base_actions(&$msgs, $paramName = 'act') {
    $this->__construct($msgs, $paramName);
  }

  /**
  * Get action groups
  *
  * Returns an array of id => group name elements;
  * optionally limited
  *
  * @param mixed int|NULL $limit optional, default NULL
  * @param mixed int|NULL $offset optional, default NULL
  * @return array
  */
  function getActionGroups($limit = NULL, $offset = NULL) {
    $sql = "SELECT actiongroup_id, actiongroup_name
              FROM %s
             ORDER BY actiongroup_name ASC";
    $sqlParams = array($this->tableGroups);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['actiongroup_id']] = $row['actiongroup_name'];
      }
    }
    return $result;
  }

  /**
  * Get group name by id
  *
  * @param int $groupId
  * @return mixed string|NULL
  */
  function getGroupNameById($groupId) {
    $sql = "SELECT actiongroup_id, actiongroup_name
              FROM %s
             WHERE actiongroup_id = %d";
    $sqlParams = array($this->tableGroups, $groupId);
    $result = NULL;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row['actiongroup_name'];
      }
    }
    return $result;
  }

  /**
  * Get group id by name
  *
  * @param int $groupName
  * @return mixed int|NULL
  */
  function getGroupIdByName($groupName) {
    $sql = "SELECT actiongroup_name, actiongroup_id
              FROM %s
             WHERE actiongroup_name = '%s'";
    $sqlParams = array($this->tableGroups, $groupName);
    $result = NULL;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row['actiongroup_id'];
      }
    }
    return $result;
  }

  /**
  * Get actions for a group
  *
  * Returns an array of id => action name elements;
  * optionally limited
  *
  * @param int $groupId
  * @param mixed int|NULL $limit optional, default NULL
  * @param mixed int|NULL $offset optional, default NULL
  * @return array
  */
  function getActionsByGroupId($groupId, $limit = NULL, $offset = NULL) {
    $sql = "SELECT action_id, action_name
              FROM %s
             WHERE action_group = %d
             ORDER BY action_name ASC";
    $sqlParams = array($this->tableActions, $groupId);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['action_id']] = $row['action_name'];
      }
    }
    return $result;
  }

  /**
  * Get action name by id
  *
  * @param int $actionId
  * @return mixed string|NULL
  */
  function getActionNameById($actionId) {
    $sql = "SELECT action_id, action_name
              FROM %s
             WHERE action_id = %d";
    $sqlParams = array($this->tableActions, $actionId);
    $result = NULL;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row['action_name'];
      }
    }
    return $result;
  }

  /**
  * Get observers for an action
  *
  * Returns an array of observer guids for an action.
  * Optionally, the class names of the observer classes can be retrieved
  *
  * @param int $actionId
  * @param boolen $getClassNames optional, default FALSE
  * @return array
  */
  function getObserversByActionId($actionId, $getClassNames = FALSE) {
    $sql = "SELECT observer_guid
              FROM %s
             WHERE action_id = %d";
    $sqlParams = array($this->tableObservers, $actionId);
    $observerGuids = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $observerGuids[] = $row['observer_guid'];
      }
    }
    if ($getClassNames && !empty($observerGuids)) {
      $cond = $this->databaseGetSQLCondition('module_guid', $observerGuids);
      $sql = "SELECT module_class, module_guid
                FROM %s
               WHERE ".str_replace('%', '%%', $cond).
              "ORDER BY module_class ASC";
      $sqlParams = array($this->tableModules);
      $result = array();
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['module_guid']] = $row['module_class'];
        }
      }
      return $result;
    } else {
      return $observerGuids;
    }
  }

  /**
  * Get an action id by group and action names
  *
  * @param string $group
  * @param string $action
  * @return mixed int|NULL
  */
  function getActionIdByGroupAndAction($group, $action) {
    $sql = "SELECT a.action_id, g.actiongroup_id
              FROM %s a
             INNER JOIN %s g
                ON a.action_group = g.actiongroup_id
             WHERE a.action_name = '%s'
               AND g.actiongroup_name = '%s'";
    $sqlParams = array($this->tableActions, $this->tableGroups, $action, $group);
    $result = NULL;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row['action_id'];
      }
    }
    return $result;
  }

  /**
  * Check whether an observer exists by action ID and observer GUID
  *
  * @param int $actionId
  * @param string $observerGuid
  * @return boolean
  */
  function checkObserverByActionAndGUID($actionId, $observerGuid) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE action_id = %d
               AND observer_guid = '%s'";
    $sqlParams = array($this->tableObservers, $actionId, $observerGuid);
    $result = FALSE;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($count = $res->fetchField()) {
        $result = ($count > 0) ? TRUE : FALSE;
      }
    }
    return $result;
  }

  /**
  * Get a connector modules's class name by GUID
  *
  * @param string $guid
  * @return mixed string|NULL
  */
  function getClassByGuid($guid) {
    $sql = "SELECT module_guid, module_class, module_type
              FROM %s
             WHERE module_type = 'connector'
               AND module_guid = '%s'
             ORDER BY module_class ASC";
    $sqlParams = array($this->tableModules, $guid);
    $result = NULL;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row['module_class'];
      }
    }
    return $result;
  }

  /**
  * Get all available connector modules
  *
  * (except the action dispatcher connector)
  *
  * @return array
  */
  function getConnectorModules() {
    $sql = "SELECT module_guid, module_class, module_type
              FROM %s
             WHERE module_type = 'connector'
               AND module_guid != '79f18e7c40824a0f975363346716ff62'
             ORDER BY module_class ASC";
    $sqlParams = array($this->tableModules);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['module_guid']] = $row['module_class'];
      }
    }
    return $result;
  }

  /**
  * Delete all existing configuration data
  *
  * @return boolean successfully deleted?
  */
  function deleteAllData() {
    $successObservers = $this->databaseDeleteRecord($this->tableObservers, NULL);
    $successActions = $this->databaseDeleteRecord($this->tableActions, NULL);
    $successGroups = $this->databaseDeleteRecord($this->tableGroups, NULL);
    if ($successObservers !== FALSE && $successActions !== FALSE && $successGroups !== FALSE) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Register action
  *
  * Automatically register an action on call
  * (will be called if the appropriate module option is set)
  *
  * @param string $group
  * @param string $action
  * @return mixed int|NULL
  */
  function registerAction($group, $action) {
    if (!(checkit::isNoHTML($group, TRUE) && checkit::isNoHTML($action, TRUE))) {
      return NULL;
    }
    $group = trim($group);
    $action = trim($action);
    $groupId = $this->getGroupIdByName($group);
    if ($groupId === NULL) {
      $groupId = $this->databaseInsertRecord(
        $this->tableGroups,
        'actiongroup_id',
        array('actiongroup_name' => $group)
      );
      if ($groupId === FALSE) {
        return NULL;
      }
    }
    $actionId = $this->databaseInsertRecord(
      $this->tableActions,
      'action_id',
      array('action_group' => $groupId, 'action_name' => $action)
    );
    if ($actionId !== FALSE) {
      $this->logMsg(
        MSG_INFO,
        PAPAYA_LOGTYPE_SYSTEM,
        'Action dispatcher registered new action',
        sprintf(
          'Automatically registered action %s in group %s',
          $action,
          $group
        )
      );
      return $actionId;
    }
    return NULL;
  }

  /**
  * Call action method on all suitable observers
  *
  * Returns the number of successfully callable observer methods
  *
  * @param string $group
  * @param string $action
  * @param mixed $params optional, default NULL
  * @return int
  */
  function call($group, $action, $params = NULL) {
    // Determine the action id and auto-register or exit if it does not exist
    $actionId = $this->getActionIdByGroupAndAction($group, $action);
    if ($actionId === NULL) {
      // Is auto-registration activated?
      include_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');
      $guid = '73d66a9ce59741d38d02bf8080392669';
      $autoRegister = base_module_options::readOption($guid, 'AUTO_REGISTER_ACTIONS', 0);
      if ($autoRegister) {
        $actionId = $this->registerAction($group, $action);
      }
      if ($actionId === NULL) {
        return 0;
      }
    }
    // Get the matching observers and exit if there aren't any
    $observers = $this->getObserversByActionId($actionId);
    if (empty($observers)) {
      return 0;
    }
    // Load the observers (connector modules)
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $pluginloaderObj = &base_pluginloader::getInstance();
    $pluginloaderObj->loadData($observers);
    $connectors = array();
    foreach ($observers as $observer) {
      $connectors[] = &$pluginloaderObj->createObject($observer, $this);
    }
    // Now check each connector, and if it has got a matching method, call it
    $counter = 0;
    foreach ($connectors as $connector) {
      if (is_object($connector)) {
        if (method_exists($connector, $action)) {
          // Check for recursion
          $backtrace = debug_backtrace();
          $recursion = FALSE;
          foreach ($backtrace as $backtraceItem) {
            if ($backtraceItem['class'] == get_class($connector) &&
                $backtraceItem['function'] == $action) {
              $recursion = TRUE;
              break;
            }
          }
          if ($recursion) {
            $this->logMsg(
              MSG_ERROR,
              PAPAYA_LOGTYPE_SYSTEM,
              'Recursion detected in action dispatcher',
              sprintf(
                'Action dispatcher detected a recursion in its call for %s::%s()',
                get_class($connector),
                $action
              )
            );
            return $counter;
          }
          // Is this one of the new-style connectors with a setConfiguration() method?
          if (method_exists($connector, 'setConfiguration')) {
            if (!isset($this->baseOptions)) {
              $this->baseOptions = $this->papaya()->options;
            }
            $connector->setConfiguration($this->baseOptions);
          }
          $counter++;
          $connector->$action($params);
        }
      } else {
        $this->logMsg(
          MSG_ERROR,
          PAPAYA_LOGTYPE_SYSTEM,
          'Class not found in action dispatcher',
          sprintf('Action dispatcher tried to call method %s on missing connector', $action)
        );
      }
    }
    return $counter;
  }
}
?>
