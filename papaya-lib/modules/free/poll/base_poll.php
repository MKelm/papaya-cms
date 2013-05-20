<?php
/**
* Function class for polls
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
* @subpackage Free-Poll
* @version $Id: base_poll.php 38268 2013-03-13 12:34:47Z yurtsever $
*/

/**
* Base database class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Function class for polls
* @package Papaya-Modules
* @subpackage Free-Poll
*/
class base_poll extends base_db {

  /**
  * Table for links
  * @var string $tableLink
  */
  var $tableLink = "";
  /**
  * Table for surfers
  * @var string $tableSurfer
  */
  var $tableSurfer = "";
  /**
  * Table for results
  * @var string $tableResults
  */
  var $tableResults = "";
  /**
  * Table for answers
  * @var string $tableAnswers
  */
  var $tableAnswers = "";
  /**
  * Table fpr polls
  * @var string $tablePolls
  */
  var $tablePolls = "";
  /**
  * Table for categories
  * @var string $tableCategs
  */
  var $tableCategs = "";

  /**
  * Set input field size
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'x-large';

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = '';
  /**
  * Session parameter name
  * @var string $sessionParamName
  */
  var $sessionParamName = '';
  /**
  * Parameters
  * @var array $params
  */
  var $params = NULL;

  /**
  * Categories
  * @var array $categs
  */
  var $categs = NULL;
  /**
  * Polls
  * @var array $polls
  */
  var $polls = NULL;
  /**
  * Answers
  * @var array $answers
  */
  var $answers = NULL;
  /**
  * Results
  * @var array $results
  */
  var $results = NULL;

  /**
  * Cache search results
  * @var array $cacheSearchResults
  */
  var $cacheSearchResults = FALSE;

  /**
  * Poll dialog object base_dialog
  * @var object $pollDialog
  */
  var $pollDialog = NULL;

  /**
  * Poll record data
  * @var array $pollData
  */
  var $pollData = NULL;

  /**
  * PHP5 Constructor
  *
  * @param string $paramName Name des Parameterarrays
  * @access public
  */
  function __construct($paramName = 'pl') {
    $this->tableLink = PAPAYA_DB_TBL_SURFERPERMLINK;
    $this->tableSurfer = PAPAYA_DB_TBL_SURFER;
    $this->tableResults = PAPAYA_DB_TABLEPREFIX.'_poll_result';
    $this->tableAnswers = PAPAYA_DB_TABLEPREFIX.'_poll_answer';
    $this->tablePolls = PAPAYA_DB_TABLEPREFIX.'_poll';
    $this->tableCategs = PAPAYA_DB_TABLEPREFIX.'_poll_categ';
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_'.$paramName;
  }

  /**
  * Initialization
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam(
      'categ_id',
      array('cmd', 'poll_id', 'answer_id', 'result_id')
    );
    $this->initializeSessionParam(
      'poll_id',
      array('cmd', 'answer_id', 'result_id')
    );
    $this->initializeSessionParam(
      'answer_id',
      array('cmd', 'result_id')
    );
    $this->initializeSessionParam(
      'result_id',
      array('cmd')
    );
  }

  /**
  * Executions
  *
  * @access public
  */
  function execute() {
    switch (@$this->params['cmd']) {
    case 'add_categ':
      if ($newId = $this->addCateg()) {
        $this->addMsg(MSG_INFO, $this->_gt('Category added.'));
        $this->params['categ_id'] = $newId;
        $this->params['poll_id'] = 0;
        $this->initializeSessionParam('categ_id', array('cmd'));
        $this->initializeSessionParam('poll_id', array('cmd'));

      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('Database error! Changes not saved.')
        );
      }
      break;
    case 'del_categ':
      if (isset($this->params['confirm_delete']) &&
          $this->params['confirm_delete']) {
        if ($this->categExists($this->params['categ_id'])) {
          if ($this->categIsEmpty($this->params['categ_id'])) {
            if ($this->deleteCateg($this->params['categ_id'])) {
              $this->addMsg(MSG_INFO, $this->_gt('Category deleted.'));
              $this->params['categ_id'] = 0;
              $this->initializeSessionParam('categ_id', array('cmd'));
            } else {
              $this->addMsg(
                MSG_ERROR,
                $this->_gt('Database error! Changes not saved.')
              );
            }
          } else {
            $this->addMsg(
              MSG_WARNING,
              $this->_gt('Category is not empty.')
            );
          }
        } else {
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('Category not found.')
          );
        }
      }
      break;
    case 'edit_categ':
      $this->loadCategs($this->params['categ_id']);
      $this->initializeCategEditform();
      if ($this->pollDialog->modified()) {
        if ($this->pollDialog->checkDialogInput()) {
          if ($this->saveCateg()) {
            $this->addMsg(MSG_INFO, $this->_gt('Category modified.'));
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
      }
      break;
    case 'add_poll':
      if ($newId = $this->addPoll((int)$this->params['categ_id'])) {
        $this->addMsg(MSG_INFO, $this->_gt('Poll added.'));
        $this->params['poll_id'] = $newId;
        $this->initializeSessionParam('poll_id', array('cmd'));
      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('Database error! Changes not saved.')
        );
      }
      break;
    case 'del_poll':
      if (isset($this->params['confirm_delete']) &&
          $this->params['confirm_delete']) {
        if ($this->pollExists($this->params['poll_id'])) {
          if ($this->deletePoll($this->params['poll_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Poll deleted.'));
            $this->params['poll_id'] = 0;
            $this->initializeSessionParam('poll_id', array('cmd'));
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        } else {
          $this->addMsg(MSG_WARNING, $this->_gt('Poll not found.'));
        }
      }
      break;
    case 'edit_poll':
      $this->loadCategs();
      $this->loadPolls($this->params['categ_id']);
      $this->initializePollEditForm();
      if ($this->pollDialog->modified()) {
        if ($this->pollDialog->checkDialogInput()) {
          if ($this->savePoll()) {
            $this->addMsg(MSG_INFO, $this->_gt('Poll modified.'));
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
      }
      break;
    case 'add_answer':
      if ($newId = $this->addAnswer((int)$this->params['poll_id'])) {
        $this->addMsg(MSG_INFO, $this->_gt('Answer added.'));
        $this->params['answer_id'] = $newId;
        $this->initializeSessionParam('answer_id', array('cmd'));
      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('Database error! Changes not saved.')
        );
      }
      break;
    case 'del_answer':
      if (isset($this->params['confirm_delete']) &&
          $this->params['confirm_delete']) {
        if ($this->answerExists($this->params['answer_id'])) {
          if ($this->deleteAnswer($this->params['answer_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Answer deleted.'));
            $this->params['answer_id'] = 0;
            $this->initializeSessionParam('answer_id', array('cmd'));
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        } else {
          $this->addMsg(MSG_WARNING, $this->_gt('Poll not found.'));
        }
      }
      break;
    case 'edit_answer':
      $this->loadCategs();
      $this->loadPolls($this->params['categ_id']);
      $this->loadAnswers($this->params['poll_id']);
      $this->initializeAnswerEditForm();
      if ($this->pollDialog->modified()) {
        if ($this->pollDialog->checkDialogInput()) {
          if ($this->saveAnswer()) {
            $this->addMsg(MSG_INFO, $this->_gt('Answer modified.'));
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
      }
      break;
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    $this->loadCategs();
    $this->loadPolls($this->params['categ_id']);
    $this->loadAnswers($this->params['poll_id']);
  }


  /**
  * Get xml
  *
  * @access public
  */
  function getXML() {
    if (is_object($this->layout)) {
      $this->getXMLButtons();
      $this->getXMLTreeList();
      switch (@$this->params['cmd']) {
      case 'edit_categ':
        $this->getXMLCategForm();
        break;
      case 'edit_poll':
        $this->getXMLPollForm();
        break;
      case 'edit_answer':
        $this->getXMLAnswerForm();
        break;
      case 'del_categ':
        $this->getXMLDelCategForm();
        break;
      case 'del_poll':
        $this->getXMLDelPollForm();
        break;
      case 'del_answer':
        $this->getXMLDelAnswerForm();
        break;
      default :
        if (isset($this->answers[$this->params['answer_id']]) &&
            is_array($this->answers[$this->params['answer_id']])) {
          $this->getXMLAnswerForm();
        } elseif (isset($this->polls[$this->params['poll_id']]) &&
                  is_array($this->polls[$this->params['poll_id']])) {
          $this->getXMLPollForm();
        } elseif (isset($this->categs[$this->params['categ_id']]) &&
                  is_array($this->categs[$this->params['categ_id']])) {
          $this->getXMLCategForm();
        }
      }
      $this->getXMLAnswerList();
    }
  }

  /**
  * Add category
  *
  * @see base_db::databaseInsertRecord
  *
  * @access public
  * @return mixed FALSE or Id of new record
  */
  function addCateg() {
    return $this->databaseInsertRecord(
      $this->tableCategs, 'categ_id', array('categ_title' => $this->_gt('New category'))
    );
  }

  /**
  * Check if category exists
  *
  * @param integer $id category id
  * @access public
  * @return boolean
  */
  function categExists($id) {
    $sql = "SELECT count(*)
              FROM %s
             WHERE categ_id = '%d'";
    $params = array($this->tableCategs, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Check if category is empty
  *
  * @param integer $id category id
  * @access public
  * @return boolean
  */
  function categIsEmpty($id) {
    return TRUE;
  }

  /**
  * Delete category
  *
  * @see base_db::databaseDeleteRecord
  *
  * @param integer $id category id
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function deleteCateg($id) {
    $this->loadPolls($id);
    if (isset($this->polls) && is_array($this->polls)) {
      foreach ($this->polls as $pollId => $poll) {
        $this->deletePolls($pollId);
      }
    }
    unset ($this->categs);
    return $this->databaseDeleteRecord($this->tableCategs, 'categ_id', $id);
  }

  /**
  * Load polls from database for one category or for
  * all categories including the category title
  *
  * @param integer $id category id or NULL
  * @access public
  * @return boolean
  */
  function loadPolls($id = NULL) {
    unset($this->polls);
    if (isset($id) && $id > 0) {
      $sql = "SELECT poll_id, categ_id, question, start_time, end_time
                FROM %s
               WHERE categ_id = %d
               ORDER BY poll_id ASC";
      $params = array($this->tablePolls, (int)$id);
    } else {
      $sql = "SELECT p.poll_id, p.categ_id, p.question,
                      p.start_time, p.end_time, c.categ_title
                FROM %s AS p, %s AS c
               WHERE p.categ_id = c.categ_id
               ORDER BY categ_title, question ASC";
      $params = array($this->tablePolls, $this->tableCategs);
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->polls[(int)$row['poll_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load poll data
  *
  * @param integer $id poll id
  * @access public
  * @return boolean
  */
  function loadPollData($id) {
    unset($this->pollData);
    if (isset($id) && $id > 0) {
      $sql = "SELECT poll_id, question, start_time, end_time
                FROM %s
               WHERE poll_id = %d";
      $params = array($this->tablePolls, (int)$id);
    } else {
      return FALSE;
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->pollData = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load poll data from category
  *
  * @param integer $id optional category id, default value NULL
  * @param integer $max optional, default value NULL
  * @access public
  * @return boolean
  */
  function loadPollDataFromCateg($id = NULL, $max = NULL) {
    unset($this->polls);
    if (isset($id) && $id > 0) {
      $sql = "SELECT poll_id, question, start_time, end_time
                FROM %s
               WHERE categ_id = %d
                 AND start_time < %d
               ORDER BY start_time DESC, end_time DESC, question ASC";
      $params = array($this->tablePolls, (int)$id, time());
    } else {
      return FALSE;
    }
    if ($res = $this->databaseQueryFmt($sql, $params, $max)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->polls[$row['poll_id']] = $row;
      }
      if (isset($this->polls) && is_array($this->polls)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load answer data
  *
  * @param integer $id poll id
  * @access public
  * @return boolean
  */
  function loadAnswerData($id) {
    unset($this->pollData['ANSWERS']);
    if (isset($id) && $id > 0) {
      $sql = "SELECT a.answer_id, a.answer, a.answer_sort, COUNT(b.result_id) AS counted
                FROM %s AS a
     LEFT OUTER JOIN %s AS b ON b.answer_id = a.answer_id
               WHERE a.poll_id = %d
            GROUP BY a.answer_id, a.answer
            ORDER BY a.answer_sort ASC";
      $params = array($this->tableAnswers, $this->tableResults, (int)$id);
    } else {
      return FALSE;
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->pollData['ANSWERS'][$row['answer_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * check if user can vote
  *
  * @access public
  * @return mixed boolean FALSE or array user data
  */
  function userCanVote() {
    $userId = '';
    //check the surfer
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();
    if (isset($this->surferObj) && is_object($this->surferObj) &&
        $this->surferObj->isValid) {
      $userId = $this->surferObj->surferId;
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE poll_id = %d AND user_id = '%s'";
      $params = array(
        $this->tableResults,
        (int)$this->pollData['poll_id'],
        $userId
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        list($count) = $res->fetchRow();
        if ($count > 0) {
          return FALSE;
        }
      }
    }
    // check the cookie
    if (isset($_COOKIE['poll']) &&
        isset($_COOKIE['poll'][$this->pollData['poll_id']])) {
      $sql = "SELECT COUNT(*)
                FROM %s
                WHERE poll_id = %d
                  AND surfer_cookie = '%s'";
      $params = array(
        $this->tableResults,
        (int)$this->pollData['poll_id'],
        @$_COOKIE['poll'][$this->pollData['poll_id']]
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        list($count) = $res->fetchRow();
        if ($count > 0) {
          return FALSE;
        }
      }
    }
    //check the ip
    $time = time() - 43200;
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE poll_id = %d
               AND surfer_ip = '%s'
               AND surfer_time >= %d";
    $params = array(
      $this->tableResults,
      (int)$this->pollData['poll_id'],
      $_SERVER['REMOTE_ADDR'], $time
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      list($count) = $res->fetchRow();
      if ($count > 0) {
        return FALSE;
      }
    }
    return array('userId' => $userId, 'ip' => $_SERVER['REMOTE_ADDR']);
  }

  /**
  * Delete poll and its answers
  *
  * @see base_db::databaseDeleteRecord
  *
  * @param integer $id poll id
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function deletePolls($id) {
    $this->loadAnswers($id);
    if (isset($this->answers) && is_array($this->answers)) {
      foreach ($this->answers as $anid => $answer) {
        $this->loadResults($anid);
        if (isset($this->results) && is_array($this->results)) {
          foreach ($this->results as $resId => $result) {
            if ($result['answer_id'] == $anid) {
              $this->databaseDeleteRecord(
                $this->tableResults, 'answer_id', $anid
               );
            }
          }
        }
        if ($answer['poll_id'] == $id) {
          $this->databaseDeleteRecord($this->tableAnswers, 'poll_id', $id);
        }
      }
    }
    unset($this->results);
    unset($this->answers);
    unset($this->polls);
    return $this->databaseDeleteRecord($this->tablePolls, 'poll_id', $id);
  }


  /**
  * Load answers
  *
  * @param integer $id poll id
  * @access public
  * @return boolean
  */
  function loadAnswers($id) {
    unset($this->answers);
    if ($id) {
      $sql = "SELECT answer_id, poll_id, answer, answer_sort
                FROM %s
               WHERE poll_id = %d
            ORDER BY answer_sort ASC, answer_id ASC";
    } else {
      return FALSE;
    }
    $params = array($this->tableAnswers, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->answers[(int)$row['answer_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * load results
  *
  * @param integer $id poll id
  * @access public
  * @return boolean
  */
  function loadResults($id) {
    unset($this->results);
    if ($id) {
      $sql = "SELECT answer_id, COUNT(result_id) AS counted
                FROM %s
               WHERE poll_id = %d
               GROUP BY answer_id";
    } else {
      return FALSE;
    }
    $params = array($this->tableResults, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->results[(int)$row['result_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * load categories
  *
  * @param mixed $id optional, default value NULL
  * @access public
  * @return boolean
  */
  function loadCategs($id = NULL) {
    unset($this->categs);
    if (isset($id)) {
      $sql = "SELECT categ_id, categ_title
                FROM %s
               WHERE categ_id = '%d'";
    } else {
      $sql = "SELECT categ_id, categ_title
                FROM %s
            ORDER BY categ_title ASC";
    }
    $params = array($this->tableCategs, (int)$id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['pollcount'] = 0;
        $this->categs[(int)$row['categ_id']] = $row;
      }
      return $this->checkPolls();
    }
    return FALSE;
  }

  /**
  * Check polls
  *
  * @access public
  * @return boolean
  */
  function checkPolls() {
    $sql = "SELECT count(poll_id), categ_id
              FROM %s
             GROUP BY categ_id";
    $params = array($this->tablePolls);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow()) {
        $this->categs[(int)$row[1]]['pollcount'] = (int)$row[0];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Initialize categ
  *
  * @access public
  */
  function initializeCategEditForm() {
    if (!is_object($this->pollDialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->categs[$this->params['categ_id']];
      $hidden = array(
        'cmd'      => 'edit_categ',
        'save'     => 1,
        'categ_id' => $data['categ_id']
      );
      $fields = array(
        'categ_title' => array('Title', 'isNoHTML', TRUE, 'input', 200)
      );
      $this->pollDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->pollDialog->msgs = &$this->msgs;
      $this->pollDialog->loadParams();
    }
  }

  /**
  * Save categories
  *
  * @see base_db::databaseUpdateRecord
  *
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function saveCateg() {
    $data = array(
      'categ_title' => $this->params['categ_title']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableCategs, $data, 'categ_id', (int)$this->params['categ_id']
    );
  }

  /**
  * Add poll
  *
  * @see base_db::databaseInsertRecord
  *
  * @param string $parent
  * @access public
  * @return mixed FALSE or Id of new record
  */
  function addPoll($parent) {
    if ($this->categExists($parent)) {
      return $this->databaseInsertRecord(
        $this->tablePolls,
        'poll_id',
        array('categ_id' => $parent, 'question' => $this->_gt('New poll'))
      );
    }
    return FALSE;
  }

  /**
  * Check if poll exists
  *
  * @param integer $id poll id
  * @access public
  * @return boolean
  */
  function pollExists($id) {
    $sql = "SELECT count(*)
              FROM %s
             WHERE poll_id = '%d'";
    $params = array($this->tablePolls, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Delete poll
  *
  * @see base_db::databaseDeleteRecord
  *
  * @param integer $i
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function deletePoll($i) {
    $this->loadAnswers($i);
    if (isset($this->answers) && is_array($this->answers)) {
      foreach ($this->answers as $anid=>$answer) {
        $this->loadResults($anid);
        if (isset($this->results) && is_array($this->results)) {
          foreach ($this->results as $resId => $result) {
            if ($result['answer_id'] == $anid) {
              $this->databaseDeleteRecord($this->tableResults, 'entry_id', $enid);
            }
          }
        }
        if ($answer['poll_id'] == $i) {
          $this->databaseDeleteRecord($this->tableAnswers, 'poll_id', $i);
        }
      }
    }
    unset ($this->results);
    unset ($this->answers);
    unset ($this->polls);
    return $this->databaseDeleteRecord($this->tablePolls, 'poll_id', $i);
  }

  /**
  * Save poll
  *
  * @see base_db::databaseUpdateRecord
  *
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function savePoll() {
    if (preg_match_all('#\d+#', $this->params['start_time'], $regs, PREG_PATTERN_ORDER)) {
      $start = $regs[0];
      $start = mktime($start[3], $start[4], 0, $start[1], $start[2], $start[0]);
    } else {
      $start = time();
    }
    if (preg_match_all('#\d+#', $this->params['end_time'], $regs, PREG_PATTERN_ORDER)) {
      $end = $regs[0];
      $end = mktime($end[3], $end[4], 0, $end[1], $end[2], $end[0]);
    } else {
      $end = time();
    }
    $data = array(
      'question'   => $this->params['question'],
      'start_time' => $start,
      'end_time'   => $end
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tablePolls, $data, 'poll_id', (int)$this->params['poll_id']
    );
  }

  /**
  * Initialize poll edit form
  *
  * @access public
  */
  function initializePollEditForm() {
    if (!is_object($this->pollDialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->polls[$this->params['poll_id']];
      $data['start_time'] = !empty($data['start_time']) ?
        date('Y-m-d H:i', $data['start_time']) : '';
      $data['end_time'] = !empty($data['end_time']) ?
        date('Y-m-d H:i', $data['end_time']) : '';
      $hidden = array(
        'cmd' => 'edit_poll',
        'save' => 1,
        'poll_id' => $data['poll_id']
      );
      $fields = array(
        'question'   => array('Question', 'isNoHTML', TRUE, 'input', 200),
        'start_time' => array('Start time', 'isISODateTime', TRUE, 'datetime', 200),
        'end_time'   => array('End time', 'isISODateTime', TRUE, 'datetime', 200)
      );
      $this->pollDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->pollDialog->msgs = &$this->msgs;
      $this->pollDialog->loadParams();
    }
  }

  /**
  * Add answer
  *
  * @param string $parent
  * @access public
  * @return boolean
  */
  function addAnswer($parent) {
    if ($this->pollExists($parent)) {
      $this->loadAnswers($parent);
      $data = array('poll_id' => $parent, 'answer' => $this->_gt('New answer'));
      $maxSort = -1;
      if (!empty($this->answers)) {
        foreach ($this->answers as $id => $answer) {
          if ((int)$answer['answer_sort'] > $maxSort) {
            $maxSort = $answer['answer_sort'];
          }
        }
      }
      $data['answer_sort'] = $maxSort + 1;
      return $this->databaseInsertRecord($this->tableAnswers, 'answer_id', $data);
    }
    return FALSE;
  }

  /**
  * Check if answer exists
  *
  * @param integer $id answer id
  * @access public
  * @return boolean exists?
  */
  function answerExists($id) {
    $sql = "SELECT count(*)
              FROM %s
             WHERE answer_id = '%d'";
    $params = array($this->tableAnswers, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Delete answer
  *
  * @see base_db::databseDeleteRecord
  *
  * @param integer $i answer id
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function deleteAnswer($i) {
    $temp = $this->results;
    $this->loadResults($i);
    if (isset($this->results) && is_array($this->results)) {
      foreach ($this->results as $id => $result) {
        if ($result['answer_id'] == $i) {
          $this->databaseDeleteRecord($this->tableResults, 'answer_id', $i);
        }
      }
    }
    unset($this->results);
    unset($this->answers);
    return $this->databaseDeleteRecord($this->tableAnswers, 'answer_id', $i);
    $this->results = $temp;
  }

  /**
  * Save answer
  *
  * @see base_db::databaseInsertRecord
  *
  * @access public
  * @return mixed FALSE or Id of new record
  */
  function saveAnswer() {
    $data = array(
      'answer' => $this->params['answer'],
      'answer_sort' => $this->params['answer_sort']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableAnswers, $data, 'answer_id', (int)$this->params['answer_id']
    );
  }

  /**
  * Initialize answer edit form
  *
  * @access public
  */
  function initializeAnswerEditForm() {
    if (!is_object($this->pollDialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->answers[$this->params['answer_id']];
      $hidden = array(
        'cmd' => 'edit_answer',
        'save' => 1,
        'answer_id' => $data['answer_id']
      );
      $fields = array(
        'answer' => array('Answer', 'isNoHTML', TRUE, 'input', 200),
        'answer_sort' => array('Answer Sort', 'isNum', TRUE, 'input', 8)
      );
      $this->pollDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->pollDialog->msgs = &$this->msgs;
      $this->pollDialog->loadParams();
    }
  }


  /**
  * Add result
  *
  * @param integer $pollId poll id
  * @param integer $answerId answer id
  * @param string $userId user id
  * @param string $surferIp surfer ip
  * @access public
  * @return boolean
  */
  function addResult($pollId, $answerId, $userId, $surferIp) {
    srand((double)microtime() * 1000000);
    $cookie = md5(uniqid(rand()));
    $now = time();
    $data = array(
      'poll_id' => $pollId, 'answer_id' => $answerId, 'surfer_time' => $now,
      'surfer_ip' => $surferIp, 'user_id' => $userId, 'surfer_cookie' => $cookie
    );
    if ( $this->userCanVote() ) {
      if ($this->databaseInsertRecord($this->tableResults, 'result_id', $data)) {
        setcookie(
          'poll['.(int)$pollId.']', $cookie, $this->pollData['end_time'] + 1209600
        );
        return TRUE;
      } else {
        return FALSE;
      }
    } else {
      return FALSE;
    }
  }


  /**
  * get category edit form xml
  *
  * @access public
  */
  function getXMLCategForm() {
    if (isset($this->categs[$this->params['categ_id']]) &&
        is_array($this->categs[$this->params['categ_id']])) {
      $this->initializeCategEditForm();
      $this->pollDialog->inputFieldSize = $this->inputFieldSize;
      $this->pollDialog->baseLink = $this->baseLink;
      $this->pollDialog->dialogTitle = htmlspecialchars($this->_gt('Properties'));
      $this->pollDialog->dialogDoubleButtons = FALSE;
      $this->layout->add($this->pollDialog->getDialogXML());
    }
  }

  /**
  * get poll form xml
  *
  * @access public
  */
  function getXMLPollForm() {
    if (isset($this->polls[$this->params['poll_id']]) &&
        is_array($this->polls[$this->params['poll_id']])) {
      $this->initializePollEditForm();
      $this->pollDialog->inputFieldSize = $this->inputFieldSize;
      $this->pollDialog->baseLink = $this->baseLink;
      $this->pollDialog->dialogTitle = htmlspecialchars($this->_gt('Properties'));
      $this->pollDialog->dialogDoubleButtons = FALSE;
      $this->layout->add($this->pollDialog->getDialogXML());
    }
  }

  /**
  * get answer form xml
  *
  * @access public
  */
  function getXMLAnswerForm() {
    if (isset($this->answers[$this->params['answer_id']]) &&
        is_array($this->answers[$this->params['answer_id']])) {
      $this->initializeAnswerEditForm();
      $this->pollDialog->inputFieldSize = $this->inputFieldSize;
      $this->pollDialog->baseLink = $this->baseLink;
      $this->pollDialog->dialogTitle = htmlspecialchars($this->_gt('Properties'));
      $this->pollDialog->dialogDoubleButtons = FALSE;
      $this->layout->add($this->pollDialog->getDialogXML());
    }
  }

  /**
  * get delete category form xml
  *
  * @access public
  */
  function getXMLDelCategForm() {
    if (isset($this->categs[$this->params['categ_id']]) &&
        is_array($this->categs[$this->params['categ_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_categ',
        'categ_id' => $this->params['categ_id'],
        'confirm_delete' => 1
      );
      $msg = sprintf(
        $this->_gt('Delete Category "%s" (%s)?'),
        $this->categs[$this->params['categ_id']]['categ_title'],
        (int)$this->params['categ_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * get delete poll form xml
  *
  * @access public
  */
  function getXMLDelPollForm() {
    if (isset($this->polls[$this->params['poll_id']]) &&
        is_array($this->polls[$this->params['poll_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_poll',
        'poll_id' => $this->params['poll_id'],
        'confirm_delete' =>1,
      );
      $msg = sprintf(
        $this->_gt('Delete poll "%s" (%s)?'),
        $this->polls[$this->params['poll_id']]['question'],
        (int)$this->params['poll_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * get delete answer form xml
  *
  * @access public
  */
  function getXMLDelAnswerForm() {
    if (isset($this->answers[$this->params['answer_id']]) &&
        is_array($this->answers[$this->params['answer_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_answer',
        'answer_id' => $this->params['answer_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete answer "%s" (%s)?'),
        $this->answers[$this->params['answer_id']]['answer'],
        (int)$this->params['answer_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * get buttons xml
  *
  * @access public
  */
  function getXMLButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;
    $toolbar->addButton(
      'Add category',
      $this->getLink(
        array('cmd' => 'add_categ', 'categ_id' => (int)$this->params['categ_id'])
      ),
      'actions-folder-add',
      '',
      FALSE
    );
    if (isset($this->categs[$this->params['categ_id']]) &&
        is_array($this->categs[$this->params['categ_id']])) {
      $toolbar->addButton(
        'Delete category',
        $this->getLink(
          array(
            'cmd' => 'del_categ',
            'categ_id' => (int)$this->params['categ_id']
          )
        ),
        'actions-folder-delete',
        '',
        FALSE
      );
      $toolbar->addSeparator();
      $toolbar->addButton(
        'Add poll',
        $this->getLink(
          array('cmd'=>'add_poll', 'poll_id' => (int)$this->params['poll_id'])
        ),
        'actions-page-add',
        '',
        FALSE
      );

      if (isset($this->polls[$this->params['poll_id']]) &&
          is_array($this->polls[$this->params['poll_id']])) {
        $toolbar->addButton(
          'Delete poll',
          $this->getLink(
            array(
              'cmd' => 'del_poll', 'poll_id' => (int)$this->params['poll_id']
            )
          ),
          'places-trash',
          '',
          FALSE
        );
        $toolbar->addSeparator();
        $toolbar->addButton(
          'Add answer',
          $this->getLink(
            array(
              'cmd' => 'add_answer', 'poll_id' => (int)$this->params['poll_id']
            )
          ),
          'actions-page-add',
          '',
          FALSE
        );
        if (isset($this->answers[$this->params['answer_id']]) &&
            is_array($this->answers[$this->params['answer_id']])) {
          $toolbar->addButton(
            'Delete answer',
            $this->getLink(
              array(
                'cmd'=>'del_answer', 'answer_id'=>(int)$this->params['answer_id']
              )
            ),
            'places-trash',
            '',
            FALSE
          );
          $toolbar->addSeperator();
        }
      }
    }

    if ($str = $toolbar->getXML()) {
        $this->layout->addMenu(sprintf('<menu ident="edit">%s</menu>'.LF, $str));
    }
  }

  /**
  * get tree list xml
  *
  * @access public
  */
  function getXMLTreeList() {
    if (isset($this->categs) && is_array($this->categs)) {
      $result = sprintf(
        '<listview title="%s" width="200">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Poll categories'))
      );
      $result .= '<items>'.LF;
      foreach ($this->categs as $id => $categ) {
        if (isset($categ) && is_array($categ)) {
          if ($this->categs[$id]['pollcount'] > 0) {
            $selected = ($this->params['categ_id'] == $id) ?
              'node="open" selected="selected"' : 'node="close"';
          } else {
            $selected = ($this->params['categ_id'] == $id) ?
              'node="empty" selected="selected"' : 'node="empty"';
          }
          $result .= sprintf(
            '<listitem href="%s" title="%s" nhref="%s" %s />'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array('categ_id' => (int)$id, 'cmd' => 'edit_categ')
              )
            ),
            papaya_strings::escapeHTMLChars($categ['categ_title']),
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('categ_id' => (int)$id))
            ),
            $selected
          );
          if ((isset($this->polls) && is_array($this->polls)) &&
              ($this->params['categ_id'] == $id)) {
            $result .= $this->getXMLPollTreeList($this->params['categ_id']);
          }
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * get poll tree list xml
  *
  * @param integer $i category id
  * @access public
  * @return string $result xml
  */
  function getXMLPollTreeList($i) {
    $result = "";
    if (isset($this->polls) && is_array($this->polls)) {
      foreach ($this->polls as $id=>$poll) {
        if (isset($poll) && is_array($poll)) {
          $selected = ($this->params['poll_id'] == $id) ?
            'selected="selected"' : '';
          if ($poll['categ_id'] == $i) {
            $result .= sprintf(
              '<listitem indent="1" href="%s" title="%s" image="%s" %s/>'.LF,
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array('poll_id' => (int)$id, 'cmd' => 'edit_poll')
                )
              ),
              papaya_strings::escapeHTMLChars($poll['question']),
              papaya_strings::escapeHTMLChars(
                $this->images['items-page']
              ),
              $selected
            );
          }
        }
      }
      return $result;
    }
  }

  /**
  * get answer list xml
  *
  * @access public
  */
  function getXMLAnswerList() {
    if (isset($this->answers) && is_array($this->answers)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Answers'))
      );
      $result .= '<items>'.LF;
      foreach ($this->answers as $id => $answer) {
        if (isset($answer) && is_array($answer)) {
          $result .= sprintf(
            '<listitem href="%s" title="%s" />'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('answer_id' => (int)$id, 'cmd' => 'edit_answer'))
            ),
            papaya_strings::escapeHTMLChars($answer['answer'])
          );
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->add($result);
    }
  }

  /**
  * get output poll
  *
  * @param boolean $dialog optional, default value FALSE
  * @param string $link optional, default value ''
  * @param string $linkText optional, default value ''
  * @access public
  * @return string $result xml
  */
  function getOutputPoll($dialog = FALSE, $link = '', $linkText = '') {
    $dialogStr = ($dialog) ? 'yes' : 'no';
    $result = sprintf(
      '<poll id="%d" title="%s" action="%s" fieldname="%s[poll_id]"'.
      ' showdialog="%s" start_time="%s" end_time="%s">',
      (int)$this->pollData['poll_id'],
      papaya_strings::escapeHTMLChars($this->pollData['question']),
      papaya_strings::escapeHTMLChars($this->getWebLink()),
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($dialogStr),
      date("Y-m-d", $this->pollData['start_time']),
      date("Y-m-d", $this->pollData['end_time'])
    );
    $resultCounter = 0;
    if (isset($this->pollData['ANSWERS']) &&
        is_array($this->pollData['ANSWERS'])) {
      foreach ($this->pollData['ANSWERS'] as $answer) {
        $result .= sprintf(
          '<answer id="%d" result="%d" sort="%d" fieldname="%s[id]">%s</answer>',
          (int)$answer['answer_id'],
          (int)$answer['counted'],
          (int)$answer['answer_sort'],
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($answer['answer'])
        );
        $resultCounter += $answer['counted'];
      }
    }
    $result .= '<voted>'.$resultCounter.'</voted>';
    if (trim($link) != '') {
      $result .= sprintf(
        '<link href="%s">%s</link>',
        $this->getAbsoluteURL($link, $linkText),
        papaya_strings::escapeHTMLChars($linkText)
      );
    }
    $result .= '</poll>';
    return $result;
  }

  /**
  * get output
  *
  * @param object &$conf see base_poll::getOutputPoll()
  * @param boolean $details optional, default value TRUE
  * @access public
  * @return string $result xml
  */
  function getOutput(&$conf, $details = TRUE) {
    $this->initializeParams();
    @list($mode, $dataId) = explode(';', @$conf['poll']);
    $result = '';
    if ($details && $mode == 'categ' && $dataId > 0 &&
        $this->loadCategs($dataId) ) {
      if (isset($this->categs)) {
        $this->loadPollDataFromCateg($dataId);
        if (isset($this->polls) && is_array($this->polls)) {
          $result .= sprintf('<categ>');
          foreach ($this->polls as $id => $poll) {
            $now = time();
            if ($now <= $poll['end_time']) {
              $result .= sprintf(
                '<poll id="%d" title="%s" status="active" href="%s"/>',
                (int)$poll['poll_id'],
                papaya_strings::escapeHTMLChars($poll['question']),
                papaya_strings::escapeHTMLChars(
                  $this->getWebLink(
                    NULL,
                    NULL,
                    NULL,
                    array('poll_id' => $id),
                    $this->paramName
                  )
                )
              );
            } else {
              $result .= sprintf(
                '<poll id="%d" title="%s" status="closed" href="%s"/>',
                (int)$poll['poll_id'],
                papaya_strings::escapeHTMLChars($poll['question']),
                papaya_strings::escapeHTMLChars(
                  $this->getWebLink(
                    NULL,
                    NULL,
                    NULL,
                    array('poll_id' => $id),
                    $this->paramName
                  )
                )
              );
            }
          }
          $result .= sprintf('</categ>');
        }
      }
    }

    if ($mode == 'poll' && $dataId > 0) {
      $pollId = $dataId;
    } elseif ($mode == 'categ' &&
        isset($this->params['poll_id']) && $this->params['poll_id'] > 0 &&
        isset($this->polls[$this->params['poll_id']])) {
      $pollId = $this->params['poll_id'];
    } elseif ($mode == 'categ') {
      if (!isset($this->polls)) {
        $this->loadPollDataFromCateg($dataId);
      }
      $values = @reset($this->polls);
      $pollId = $values["poll_id"];
    } else {
      $pollId = 0;
    }

    // Load Polldata with answers or Errormessage
    if (isset($pollId) && $pollId > 0 && $this->loadPollData($pollId)) {
      $this->loadAnswerData($pollId);
      $now = time();
      $showVoteForm = TRUE;
      if ($now >= $this->pollData['start_time'] && $now <= $this->pollData['end_time']) {
        if ($data = $this->userCanVote()) {
          if (isset($this->params['id']) && isset($this->params['poll_id']) &&
              $pollId == $this->params['poll_id']) {
            if ($this->addResult(
                  $this->params['poll_id'], $this->params['id'], $data['userId'], $data['ip']
                )
               ) {
              $showVoteForm = FALSE;
              $this->loadAnswerData($pollId);
            }
          }
        } else {
          $showVoteForm = FALSE;
        }
      } else {
        $showVoteForm = FALSE;
      }
      if ($details) {
        $result .= $this->getOutputPoll($showVoteForm);
      } else {
        $result .= $this->getOutputPoll(
          $showVoteForm,
          empty($conf['poll_page_id']) ? '' : trim($conf['poll_page_id']),
          empty($conf['linktext']) ? '' : trim($conf['linktext'])
        );
      }
    } else {
      $result .= sprintf(
        '<error>%s</error>',
        papaya_strings::escapeHTMLChars(
          empty($conf['error_nopoll']) ? '' : $conf['error_nopoll']
        )
      );
    }
    return $result;
  }
}