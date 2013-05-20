<?php
/**
* Cronjob-module sends reminders of todos
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
* @package Papaya-Modules
* @subpackage _Base-Cronjobs
* @version $Id: cronjob_todos_reminder.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class Cronjobs
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_cronjob.php');
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Cronjob-module sends reminders of todos
*
* @package Papaya-Modules
* @subpackage _Base-Cronjobs
*/
class cronjob_todos_reminder extends base_cronjob {

  /**
  * Modified?
  * @var boolean $modified
  */
  var $modified = FALSE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'subject' => array('Subject', 'isNoHTML', TRUE, 'input', 100, '',
      'You\'ve got something to do.'),
    'todo' => array('TodoList', 'isNoHTML', TRUE,
      'textarea', 10,
      'Available placeholders: {%id%}, {%todo%}, {%from%}, {%to%},
        {%topic%}, {%comment%}, {%status%}, {%priority%}',
      "CronjobID: {%id%}
      Todo:       {%todo%}
      Date:       {%from%} - {%to%}
      Topic:      {%topic%}
      Comment:    {%comment%}
      Status:     {%status%}
      Priority:   {%priority%}
      ------------------------------"),
    'body' => array('Email Body', 'isNoHTML', TRUE, 'textarea', 10,
      'You have got something to do with the following: {%content%}',
      'You have got something to do with the following:
{%content%}'),
    );

  /**
  * Papaya database table topics
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;
  /**
  * Papaya database table topics public
  * @var string $tableTopicsPublic
  */
  var $tableTopicsPublic = PAPAYA_DB_TBL_TOPICS_PUBLIC;
  /**
  * Papaya database table auth user
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;

  /**
  * PHP5 Constructor
  *
  * @param object $owner
  * @param string $paramName Parametername, default 'tr'
  * @access public
  */
  function __construct(&$owner, $paramName = 'tr') {
    parent::__construct($owner, $paramName);
    $this->tableTodos = PAPAYA_DB_TABLEPREFIX."_todos";
    $this->tableTopicsTrans = PAPAYA_DB_TABLEPREFIX."_topic_trans";
    $this->tableTopicsPublicTrans = PAPAYA_DB_TABLEPREFIX."_topic_public_trans";
    $this->db = new base_db;
  }

  /**
  * Get Reminder
  *
  * @access public
  * @return array
  */
  function getReminder() {
    $result = array();
    $sql = "SELECT todo.todo_id, todo.title, todo.priority, todo.date_from,
                   todo.date_to, todo.comment, todo.status,
                   user.email,
                   tt.topic_title
              FROM %s AS todo
              LEFT OUTER JOIN %s AS tt ON (tt.topic_id = todo.topic_id)
              LEFT OUTER JOIN %s AS user ON (user.user_id = todo.user_id_to)
             ORDER BY date_to";
    $params = array($this->tableTodos, $this->tableTopicsPublicTrans,
      $this->tableAuthUser);
    if ($res = $this->db->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['email']][] = $row;
      }
    }
    return $result;
  }

  /**
  * Load modified topics
  *
  * @access public
  */
  function loadModifiedTopics() {
    $sql = "SELECT topic_title, t.topic_id, t.topic_created, email
              FROM %s AS t
              LEFT JOIN %s AS tt ON ( t.topic_id = tt.topic_id )
              LEFT JOIN %s AS tp ON ( t.topic_id = tp.topic_id )
              LEFT JOIN papaya_auth_user AS u ON ( t.author_id = u.user_id )
             WHERE tp.topic_id IS NULL
             ORDER BY t.topic_id";
    $params = array($this->tableTopics, $this->tableTopicsTrans,
      $this->tableTopicsPublic);
    if ($res = $this->db->databaseQueryFmt($sql, $params, $max)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->modifiedTopics[$row["email"]][] = $row;
      }
    }
  }

  /**
  * Basic execution
  *
  * @access public
  * @return integer 0
  */
  function execute() {
    if ($reminder = $this->getReminder()) {
      $this->loadModifiedTopics();
      // send emails
      include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
      $emailObj = new email;
      include_once(PAPAYA_INCLUDE_PATH.'system/base_statictables.php');
      $states = base_statictables::getTodoStates();
      $priorities = base_statictables::getTodoPriorities();
      $mailsCount = 0;
      foreach ($reminder as $email => $todos) {
        $content = '';
        $bodyData = array();
        if (trim($email) != '') {
          foreach ($todos as $todo) {
            $content = array(
              '{%id%}' => $todo['todo_id'],
              '{%todo%}' => $todo['title'],
              '{%from%}' => date('d.m.Y H:i', (int)$todo['date_from']),
              '{%to%}' => date('d.m.Y H:i', (int)$todo['date_to']),
              '{%topic%}' => $todo['topic_title'],
              '{%comment%}' => $todo['comment'],
              '{%status%}' => $states[$todo['status']],
              '{%priority%}' => $priorities[$todo['priority']],
            );
            $bodyData['content'] .= strtr($this->data['todo'], $content).LF;
          }
          if (isset($this->modifiedTopics[$email]) &&
              is_array($this->modifiedTopics[$email]) &&
              count($this->modifiedTopics[$email]) > 0) {
            foreach ($this->modifiedTopics[$email] as $email => $topic) {
              $bodyData['topics'] .= sprintf(
                "topic %s (%d)\ncreated: %s".LF,
                $topic['topic_title'],
                $topic['topic_id'],
                date('d.m.Y H:i', $topic['topic_created'])
              );
            }
          }
          if (isset($bodyData)) {
            $emailObj->setTemplate('body', $this->data['body'], $bodyData);
            if ($emailObj->send($email, $this->data['subject'])) {
              $mailsCount++;
            }
          }
        }
      }
      printf('%d emails sent.'.LF, $mailsCount);
      return 0;
    }
  }

  /**
  * Check execution parameters
  *
  * @access public
  * @return boolean Execution possible?
  */
  function checkExecParams() {
    if ($this->data['subject'] != '' && $this->data['todo'] != '' &&
        $this->data['body'] != '') {
      return TRUE;
    }
    return FALSE;
  }

}

?>