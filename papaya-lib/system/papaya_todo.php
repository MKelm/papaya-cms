<?php
/**
* Object to display user todo list
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
* @subpackage Administration
*/


/**
* Basic class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');
/**
* Object to display user todo list
*
* @package Papaya
* @subpackage Administration
*/
class papaya_todo extends base_db {
  /**
  * Papaya database table auth user
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;
  /**
  * Papaya database table auth groups
  * @var string $tableAuthGroups
  */
  var $tableAuthGroups = PAPAYA_DB_TBL_AUTHGROUPS;
  /**
  * Papaya database table todos
  * @var string $tableTodos
  */
  var $tableTodos = PAPAYA_DB_TBL_TODOS;

  /**
  * Todo data
  * @var array $todo
  */
  var $todo;
  /**
  * Todo list
  * @var array $todoList
  */
  var $todoList;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'todo';

  /**
  * Initialization, to papaya_todo::initializeParams
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
  }

  /**
  * Execute - base function for handlig parameters
  *
  * @access public
  */
  function execute() {
    if ($this->authUser->hasPerm(PapayaAdministrationPermissions::MESSAGES)) {
      if (isset($this->params['cmd'])) {
        switch ($this->params['cmd']) {
        case 'todo_delete':
          if (isset($this->params['todo_id']) && $this->params['todo_id'] > 0 &&
              isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete']) {
            if ($this->deleteTodo((int)$this->params['todo_id'])) {
              unset($this->params['cmd']);
              $this->addMsg(MSG_INFO, $this->_gt('Entry deleted.'));
            } else {
              $this->addMsg(MSG_WARNING, $this->_gt('Database error'));
            }
          }
          break;
        case 'todo_create':
          $this->initializeTodoDialog();
          if ($this->dialogTodo->checkDialogInput() &&
              $this->checkTodoInput()) {
            if ($this->createTodo()) {
              unset($this->dialogTodo);
              unset($this->todo);
              unset($this->params['todo_id']);
              $this->addMsg(MSG_INFO, $this->_gt('New entry saved.'));
            } else {
              $this->addMsg(
                MSG_WARNING,
                $this->_gt('Database error! New entry not saved.')
              );
            }
          }
          break;
        case 'todo_edit':
          if (isset($this->params['todo_id']) && $this->params['todo_id'] > 0 &&
              $this->loadTodo($this->params['todo_id'])) {
            $this->initializeTodoDialog();
            if ($this->dialogTodo->checkDialogInput() &&
              $this->checkTodoInput()) {
              if ($this->saveTodo()) {
                unset($this->dialogTodo);
                unset($this->todo);
                $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
              } else {
                $this->addMsg(
                  MSG_WARNING,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            }
          }
          break;
        case 'todo_forward':
          if (isset($this->params['todo_id']) && $this->params['todo_id'] > 0 &&
              isset($this->params['confirm_forward']) &&
              $this->params['confirm_forward'] ) {
            $this->initializeForwardDialog();
            if ($this->forwardTodo()) {
              unset($this->params['cmd']);
              unset($this->dialogForwardTodo);
              unset($this->params['todo_id']);
              $this->addMsg(MSG_INFO, $this->_gt('Task forwarded.'));
            } else {
              $this->addMsg(
                MSG_WARNING,
                $this->_gt('Database error! Changes not saved.')
              );
            }
          }
          break;
        }
      }
      if (isset($this->params['todo_id']) && $this->params['todo_id'] > 0) {
        $this->loadTodo($this->params['todo_id']);
      }
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('No permission active.'));
    }
  }

  /**
  * Get xml
  *
  * @access public
  */
  function getXML() {
    $this->layout->setParam('COLUMNWIDTH_LEFT', '100px');
    $this->layout->setParam('COLUMNWIDTH_CENTER', '50%');
    $this->layout->setParam('COLUMNWIDTH_RIGHT', '50%');
    if ($this->authUser->hasPerm(PapayaAdministrationPermissions::MESSAGES)) {
      $this->loadTodoList();
      if ($str = $this->getTodoList()) {
        $this->layout->add($str);
      } else {
        $this->layout->setParam('COLUMNWIDTH_RIGHT', '100%');
      }
      if (!isset($this->params['cmd'])) {
        $this->params['cmd'] = '';
      }
      switch($this->params['cmd']) {
      case 'todo_forward':
        $this->layout->addRight($this->getForwardDialog());
        break;
      case 'todo_delete':
        $this->layout->addRight($this->getDeleteForm());
        break;
      default:
        $this->layout->addRight($this->getTodoDialog());
        break;
      }
      $this->getXMLButtons();
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('No permission active.'));
    }
  }

  /**
  * Get user list user database table
  *
  * @param mixed $perms optional, default value NULL
  * @access public
  * @return array $users
  */
  function getUserList($perms = NULL) {
    $sql = "SELECT user_id, givenname, surname
              FROM %s
             ORDER BY givenname, surname ASC";
    $params = array($this->tableAuthUser);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $users[$row['user_id']] = $row['surname'].", ".$row['givenname'];
      }
    }
    return $users;
  }


  /**
  * Load users Todo's
  *
  * @param integer $baseId
  * @access public
  */
  function loadTodoList() {
    unset($this->todoList);
    $sql = "SELECT td.todo_id, td.title, td.priority, td.date_from, td.date_to,
                   td.status, td.comment, td.user_id_from, td.topic_id,
                   au.givenname, au.surname
              FROM %s td
              LEFT OUTER JOIN %s au ON ( au.user_id = td.user_id_from )
             WHERE td.user_id_to = '".$this->authUser->user['user_id']."'
             ORDER BY td.date_to ASC\r\n";
    $params = array($this->tableTodos, $this->tableAuthUser);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->todoList[$row['todo_id']] = $row;
      }
    }
  }

  /**
  * Try to load a todo entry
  *
  * @param integer $todoId
  * @access public
  * @return boolean
  */
  function loadTodo($todoId) {
    unset($this->todo);
    $sql = "SELECT todo_id, title, priority, date_from, date_to,
                   status, topic_id, comment, user_id_from, user_id_to
              FROM %s
             WHERE todo_id = %d";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableTodos, $todoId))) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->todo = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get todo list
  *
  * @access public
  * @return string $result
  */
  function getTodoList() {
    $result = '';
    if (isset($this->todoList) &&
        is_array($this->todoList) && count($this->todoList) > 0) {
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Task list'))
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Title'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Priority'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Date To'))
      );
      $result .= '<col/>'.LF;
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      $result .= $this->getTodoElements();
      $result .= '</items></listview>'.LF;
    } else {
      $this->addMsg(MSG_INFO, $this->_gt('No tasks defined.'));
    }
    return $result;
  }

  /**
  * Get todo elements
  *
  * @access public
  * @return string $result
  */
  function getTodoElements() {
    $result = '';
    if (isset($this->todoList) && is_array($this->todoList)) {
      foreach ($this->todoList as $id => $values) {
        if (isset($values) && is_array($values)) {
          if ($values['date_to'] > 0 ) {
            $dateToStr = date("Y-m-d H:i", $values['date_to']);
          } else {
            $dateToStr = $this->_gt("undefined");
          }
          if (isset($this->params['todo_id']) && $values['todo_id'] == $this->params['todo_id']) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $result .= sprintf(
            '<listitem title="%s" href="%s" image="%s" subtitle="%s" %s>',
            papaya_strings::escapeHTMLChars($values['title']),
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('todo_id' => $values['todo_id']))
            ),
            papaya_strings::escapeHTMLChars(
              $this->images[$this->getStatusGlyphIndex($values['status'])]
            ),
            papaya_strings::escapeHTMLChars($values['comment']),
            $selected
          );
          if ($iconIndex = $this->getPriorityGlyphIndex($values['priority'])) {
            $result .= sprintf(
              '<subitem align="center"><glyph src="%s"/></subitem>'.LF,
              papaya_strings::escapeHTMLChars(
                $this->images[$iconIndex]
              )
            );
          } else {
            $result .= '<subitem/>'.LF;
          }
          $result .= sprintf('<subitem align="center">%s</subitem>'.LF, $dateToStr);
          if ($values['topic_id'] > 0) {
            $result .= sprintf(
              '<subitem align="center"><a href="%s"><glyph src="%s"/></a></subitem>'.LF,
              papaya_strings::escapeHTMLChars(
                $this->getLink(array('page_id'=>$values['topic_id']), 'tt', 'topic.php')
              ),
              papaya_strings::escapeHTMLChars($this->images['items-page'])
            );
          } else {
            $result .= '<subitem/>'.LF;
          }
          $result .= '</listitem>'.LF;
        }
      }
    }
    return $result;
  }

  /**
  * Check input
  *
  * @access public
  * @return boolean $result
  */
  function checkTodoInput() {
    $result = TRUE;
    if (isset($this->params['topic_id']) && $this->params['topic_id'] > 0) {
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_topic.php');
      $topic = new papaya_topic;
      if (!(
            isset($this->params['topic_id']) &&
            $topic->topicExists((int)$this->params['topic_id'])
          )) {
        $this->addMsg(MSG_ERROR, $this->_gt("Specified page doesn't exist."));
        $result = FALSE;
      }
    } else {
      $result = TRUE;
    }
    return $result;
  }

  /**
  * Create a new todo entry
  *
  * @access public
  * @return boolean
  */
  function createTodo() {
    $patternDateIso = '~^\s*(\d{2,4})-(\d{1,2})-(\d{1,2})\s*(\d{1,2}):(\d{1,2})~';
    if ((!empty($this->params['date_to_str'])) &&
        preg_match($patternDateIso, $this->params['date_to_str'], $dateTo)) {
      $date = mktime(
        empty($dateTo[4]) ? 0 : $dateTo[4],
        empty($dateTo[5]) ? 0 : $dateTo[5],
        0,
        empty($dateTo[2]) ? 0 : $dateTo[2],
        empty($dateTo[3]) ? 0 : $dateTo[3],
        empty($dateTo[1]) ? 0 : $dateTo[1]
      );
    } else {
      $date = time();
    }
    if (isset($this->authUser->user['user_id']) &&
        $this->authUser->user['user_id'] != "") {
      $idFrom = $this->authUser->user['user_id'];
    } else {
      $idFrom = $this->params['user_id_from'];
    }
    $values = array(
      'topic_id' => (int)$this->params['topic_id'],
      'title' => $this->params['title'],
      'priority' => $this->params['priority'],
      'date_from' => time(),
      'date_to' => $date,
      'status' => $this->params['status'],
      'comment' => $this->params['comment'],
      'user_id_from' => $idFrom,
      'user_id_to' => $this->params['user_id_to']
    );
    $inserted = $this->databaseInsertRecord($this->tableTodos, 'todo_id', $values);
    if (FALSE !== $inserted) {
      $this->notifyOfTodo($values);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Save todo
  *
  * @access public
  * @return boolean
  */
  function saveTodo() {
    $patternDateIso = '~^\s*(\d{2,4})-(\d{1,2})-(\d{1,2})\s*(\d{1,2}):(\d{1,2})~';
    if (preg_match($patternDateIso, $this->params['date_to_str'], $dateTo)) {
      $date = mktime(
        empty($dateTo[4]) ? 0 : $dateTo[4],
        empty($dateTo[5]) ? 0 : $dateTo[5],
        0,
        empty($dateTo[2]) ? 0 : $dateTo[2],
        empty($dateTo[3]) ? 0 : $dateTo[3],
        empty($dateTo[1]) ? 0 : $dateTo[1]
      );
    } else {
      $date = time();
    }
    $values = array(
      'topic_id' => $this->params['topic_id'],
      'title' => $this->params['title'],
      'priority' => $this->params['priority'],
      'date_to' => $date,
      'status' => $this->params['status'],
      'comment' => $this->params['comment'],
      'user_id_to' => $this->params['user_id_to']
    );
    $this->databaseUpdateRecord(
      $this->tableTodos, $values, 'todo_id', (int)$this->params['todo_id']
    );
    if (FALSE !== $updated) {
      $this->notifyOfTodo($values);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Delete Todo entry
  *
  * @param integer $id
  * @access public
  * @return mixed
  */
  function deleteTodo($todoId) {
    return FALSE !== $this->databaseDeleteRecord(
      $this->tableTodos, 'todo_id', $todoId
    );
  }

  /**
  * send email of todo as reminder
  *
  * @param array $values values of inserted/updated todo
  * @return boolean
  */
  function notifyOfTodo($values) {
    if (defined('PAPAYA_OVERVIEW_TASK_NOTIFY') && PAPAYA_OVERVIEW_TASK_NOTIFY &&
        isset($values) && is_array($values) && count($values) > 0) {
      $additional = (isset($values['user_id_from'])) ?
        sprintf(" OR user_id = '%s' ", $values['user_id_from']) : '';
      $sql = "SELECT email
                FROM %s
               WHERE user_id = '%s'
               $additional";
      $params = array($this->tableAuthUser, $values['user_id_to']);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
          $emailObj = new email;
          include_once(PAPAYA_INCLUDE_PATH.'system/base_statictables.php');
          $states = base_statictables::getTodoStates();
          $priorities = base_statictables::getTodoPriorities();
          $bodyTemplate =
            $this->_gt('Task'). ': {%TASK%}'.LF.
            $this->_gt('Page Id'). ': {%TOPIC_ID%}'.LF.
            $this->_gt('Date'). ': {%DATE%}'.LF.
            $this->_gt('Status'). ': {%STATUS%}'.LF.
            $this->_gt('Priority'). ': {%PRIORITY%}'.LF.
            $this->_gt('Comment'). ': {%COMMENT%}'.LF;
          $data = array(
            'TASK' => $values['title'],
            'TOPIC_ID' => $values['topic_id'],
            'DATE' => date('Y-m-d H:i', $values['date_to']),
            'PRIORITY' => $priorities[$values['priority']],
            'STATUS' => $states[$values['status']],
            'COMMENT' => $values['comment'],
          );
          $emailObj->setTemplate('body', $bodyTemplate, $data);
          $subject = sprintf($this->_gt('Task reminder: %s'), $values['title']);
          if ($row['email'] != '') {
            if ($emailObj->send($row['email'], $subject)) {
              $this->addMsg(
                MSG_INFO,
                sprintf('Notification was sent to %s', $row['email'])
              );
              return TRUE;
            }
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Forward todo
  *
  * @access public
  * @return boolean
  */
  function forwardTodo() {
    $values = array(
      'user_id_to' => $this->params['user_id_to']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableTodos, $values, 'todo_id', (int)$this->params['todo_id']
    );
  }

  /**
  * Get buttons
  *
  * @access public
  * @return mixed
  */
  function getXMLButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;

    $toolbar->addButton(
      'Add task',
      $this->getLink(array('cmd' => 'new', 'todo_id' => 0)),
      'actions-task-add',
      'Create a new task'
    );
    $toolbar->addButton(
      'Compose message',
      $this->getLink(array('cmd' => 'new'), 'msg', 'msgbox.php'),
      'actions-mail-add',
      'Compose a new message'
    );
    $toolbar->addSeperator();
    if (isset($this->todo)) {
      $toolbar->addButton(
        'Forward',
        $this->getLink(array('cmd' => 'todo_forward', 'todo_id' => $this->todo['todo_id'])),
        'actions-task-forward',
        'Forward task to another user'
      );
      $toolbar->addButton(
        'Delete task',
        $this->getLink(array('cmd' => 'todo_delete', 'todo_id' => $this->todo['todo_id'])),
        'actions-task-delete',
        'Delete selected task'
      );
    }

    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(sprintf('<menu>%s</menu>'.LF, $str));
    }
  }

  /**
  * Initialize the create / modify Dialog
  *
  * @access public
  */
  function initializeTodoDialog() {
    if (!(isset($this->dialogTodo) && is_object($this->dialogTodo))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      if (isset($this->todo)) {
        $dialogTitle = $this->_gt('Edit task');
        $data = $this->todo;
        if (!empty($data['date_to'])) {
          $data['date_to_str'] = date('Y-m-d H:i', $data['date_to']);
        } else {
          $data['date_to_str'] = date('Y-m-d H:i');
        }
        $selectedUser = empty($this->params['user_id_to']) ? '' : $this->params['user_id_to'];
        $hidden = array(
          'cmd' => 'todo_edit',
          'todo_id' => (int)$this->todo['todo_id']
        );
        $btnCaption = 'Edit';
      } else {
        $dialogTitle = $this->_gt('Add task');
        $selectedUser = $this->authUser->user['user_id'];
        $data = array();
        $data['date_to_str'] = date('Y-m-d H:i');
        $hidden = array(
          'cmd' => 'todo_create'
        );
        $btnCaption = 'Add';
      }
      include_once(PAPAYA_INCLUDE_PATH.'system/base_statictables.php');
      $fields = array(
        'title' => array('Title', 'isSometext', TRUE, 'input', 255, '', ''),
        'comment' => array('Comment', 'isSometext', FALSE, 'input', 255, '', ''),
        'topic_id' => array('Page id', 'isNum', FALSE, 'pageid', 5, '', ''),
        'user_id_to' => array('Create for', 'isSometext', TRUE, 'combo',
          $this->getUserList(), '', $selectedUser),
        'date_to_str' => array('Deadline', 'isISODateTime', FALSE,
          'input', 20, 'ISO Date YYYY-MM-DD HH:MM', ''),
        'priority'=>array('Priority', 'isNum', TRUE, 'combo',
          base_statictables::getTodoPriorities()),
        'status'=>array('Status', 'isNum', TRUE, 'combo',
          base_statictables::getTodoStates())
      );
      $this->dialogTodo = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogTodo->msgs = &$this->msgs;
      $this->dialogTodo->loadParams();
      $this->dialogTodo->dialogTitle = $dialogTitle;
      $this->dialogTodo->buttonTitle = $btnCaption;
      $this->dialogTodo->inputFieldSize = 'small';
      $this->dialogTodo->dialogDoubleButtons = FALSE;
    }
  }

  /**
  * Build xml for the create / modify Dialog
  *
  * @see base_dialog::getDialogXML
  * @access public
  * @return string xml
  */
  function getTodoDialog() {
    $this->initializeTodoDialog();
    return $this->dialogTodo->getDialogXML();
  }

  /**
  * Get delete form
  *
  * @access public
  * @return string
  */
  function getDeleteForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'todo_delete',
      'todo_id' => $this->todo['todo_id'],
      'confirm_delete'=>1,
    );
    $msg = sprintf(
      $this->_gt('Do you really want to delete task "%s"?'),
      $this->todo['todo_id']
    );
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Get forward form
  *
  * @access public
  * @return string
  */
  function initializeForwardDialog() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'cmd' => 'todo_forward',
      'todo_id' => $this->todo['todo_id'],
      'confirm_forward'=>1,
    );
    $fields = array(
      'user_id_to' =>
        array('User', 'isSometext', TRUE, 'combo', $this->getUserList(), '', '')
    );
    $this->dialogForwardTodo = new base_dialog(
      $this, $this->paramName, $fields, $data, $hidden
    );
    $this->dialogForwardTodo->msgs = &$this->msgs;
    $this->dialogForwardTodo->loadParams();
    $this->dialogForwardTodo->dialogTitle = $this->_gt('Forward to');
    $this->dialogForwardTodo->buttonTitle = 'Forward';
    $this->dialogForwardTodo->dialogDoubleButtons = FALSE;
  }

  /**
  * Build xml for the forward Dialog
  *
  * @access public
  * @return string
  */
  function getForwardDialog() {
    $this->initializeForwardDialog();
    return $this->dialogForwardTodo->getDialogXML();
  }

  /**
  * Get priority glyph index
  *
  * @param integer $priority
  * @access public
  * @return integer
  */
  function getPriorityGlyphIndex($priority) {
    switch($priority) {
    case 2:
      return 'status-priority-highest';
    case 1:
      return 'status-priority-high';
    default:
      return FALSE;
    }
  }

  /**
  * Get status glyph index
  *
  * @param integer $status
  * @access public
  * @return integer
  */
  function getStatusGlyphIndex($status) {
    switch($status) {
    case 2:
      return 'status-sign-problem';
    case 1:
      return 'status-sign-warning';
    default:
      return 'status-sign-info';
    }
  }
}
?>