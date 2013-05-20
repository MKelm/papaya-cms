<?php
/**
* Module Feedback
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
* @subpackage Free-Mail
* @version $Id: base_feedback_store.php 32984 2009-11-10 16:39:35Z weinert $
*/

/**
* Basic class for database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Module Feedback
*
* @package Papaya-Modules
* @subpackage Free-Mail
*/
class base_feedback_store extends base_db {

  /**
  * Absolute count of entries
  * @var integer $entriesAbsCount
  */
  var $entriesAbsCount = 0;
  /**
  * array for feedback form detail
  * @var array $feedbackFormDetail
  */
  var $feedbackFormDetail = NULL;

  /**
  * array that holds the titles and ids of all forms
  * @var array $feedbackFormTitles
  */
  var $feedbackFormTitles = NULL;

  /**
  * Steps for multi paging bar
  * @var integer $steps
  */
  var $steps = 10;
  /**
  * PHP5 constructor
  *
  * @param string $paramName optional, default value 'fbs'
  * @access public
  */
  function __construct($paramName = 'fbs') {
    $this->paramName = $paramName;
    $this->tableFeedback = PAPAYA_DB_TABLEPREFIX.'_feedback';
    $this->tableFeedbackForms = PAPAYA_DB_TABLEPREFIX.'_feedback_forms';
  }

  /**
  * PHP4 constructor pipe to __construct()
  *
  * @param string $paramName optional, default value 'fbs'
  * @access public
  */
  function admin_feedback_store($paramName = 'fbs') {
    $this->__construct($paramName);
  }

  /**
  * delete feedback entry
  *
  * @param int $feedbackId
  * @access public
  */
  function delEntry($feedbackId) {
    $condition = array('feedback_id' => $feedbackId);
    return FALSE !== ($this->databaseDeleteRecord($this->tableFeedback, $condition));

  }
  /**
  * delete all feedback entries of $feedbackForm
  *
  * @param int $feedbackForm leave empty to delete all entries of all forms
  * @access public
  */
  function delAllEntries($feedbackForm = 0) {
    if ($feedbackForm == 0) {
      return FALSE !== ($this->databaseDeleteRecord($this->tableFeedback, NULL));
    } else {
      $condition = array('feedback_form' => $feedbackForm);
      return FALSE !== ($this->databaseDeleteRecord($this->tableFeedback, $condition));

    }
  }

  /**
  * load feedback data
  *
  * @param $int feedbackFormId
  * @param integer $limit optional, default value 0
  * @param integer $offset optional, default value 0
  * @access public
  */
  function loadFeedbackData($feedbackFormId, $limit = NULL, $offset = 0) {
    if ($feedbackFormId == 0) {
      $sql = "SELECT feedback_id, feedback_time, feedback_name,
                     feedback_email, feedback_new, feedback_subject,
                     feedback_message, feedback_xmlmessage, feedback_form
               FROM %s
           ORDER BY feedback_time DESC";
      $params = array($this->tableFeedback);
    } elseif ($feedbackFormId == -1) {
      $sql = "SELECT feedback_id, feedback_time, feedback_name,
                     feedback_email, feedback_new, feedback_subject,
                     feedback_message, feedback_xmlmessage
                FROM %s
               WHERE feedback_form < 1
            ORDER BY feedback_time DESC";
      $params = array($this->tableFeedback);
    } else {
      $sql = "SELECT feedback_id, feedback_time, feedback_name,
                     feedback_email, feedback_new, feedback_subject,
                     feedback_message, feedback_xmlmessage
                FROM %s
               WHERE feedback_form = '%d'
            ORDER BY feedback_time DESC";
      $params = array($this->tableFeedback, $feedbackFormId);
    }

    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->feedbackData[] = $row;
      }

      $this->entriesAbsCount = $res->absCount();
    }
  }
  /**
  * load feedback detail
  *
  * @param integer $limit optional, default value 0
  * @param integer $offset optional, default value 0
  * @access public
  */
  function loadFeedbackDetail($feedbackId) {
    $sql = "SELECT feedback_id, feedback_time, feedback_name, feedback_email,
                   feedback_subject, feedback_message, feedback_xmlmessage
              FROM %s
             WHERE feedback_id = '%d'";
    $params = array($this->tableFeedback, $feedbackId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->feedbackDetail = $row;
      }
    }
  }

  /**
  * Loads all form titles and form ids and saves them in an internal array.
  */
  function loadFeedbackFormTitles() {
    $sql = "SELECT feedback_form_id, feedback_form_title FROM %s";
    $params = array($this->tableFeedbackForms);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $this->feedbackFormTitles = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->feedbackFormTitles[$row['feedback_form_id'].''] = $row['feedback_form_title'];
      }
    }
    $this->feedbackFormTitles['0'] = $this->_gt('Standard form');
  }
  /**
  * load feedback forms data
  *
  * @param integer $limit optional, default value 0
  * @param integer $offset optional, default value 0
  * @access public
  */
  function loadFeedbackFormsData() {
    $sql = "SELECT form.feedback_form_id, form.feedback_form_title,
                   COUNT(feed.feedback_form) AS feedback_count
              FROM %s AS form
   LEFT OUTER JOIN %s AS feed ON (form.feedback_form_id = feed.feedback_form)
          GROUP BY feed.feedback_form, form.feedback_form_id, form.feedback_form_title
          ORDER BY form.feedback_form_title ASC";
    $params = array($this->tableFeedbackForms, $this->tableFeedback);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->feedbackFormsData[] = $row;
      }
    }
  }

  /**
  * load feedback form structure data
  *
  * @param long $feedbackId
  * @access public
  */
  function loadFeedbackFormStructureData($feedbackId) {
    $sql = "SELECT feedback_form_id, feedback_form_title, feedback_form_structure
              FROM %s
             WHERE feedback_form_id = '%d'
          ORDER BY feedback_form_title ASC";
    $params = array($this->tableFeedbackForms, $feedbackId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $this->feedbackFormDetail = $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
  }

  /**
  * Returns number of feedback forms
  *
  * @param int $tableFeedback id of feedback table
  * @return (integer number of feedback forms
  */
  function getFeedbackFormCount($tableFeedback) {
    $count = 0;
    $sql = "SELECT COUNT(feedback_form) AS count
              FROM %s
             WHERE feedback_form = 0
          GROUP BY feedback_form";
    if ($res = $this->databaseQueryFmt($sql, $tableFeedback)) {
      $count = $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
    return $count;
  }

  /**
  * Add feedback form
  *
  * @access public
  */
  function addFeedbackForm() {
    $data = array(
      'feedback_form_title' => trim($this->params['feedback_form_title'])
    );
    return (FALSE !== $this->databaseInsertRecord($this->tableFeedbackForms, NULL, $data));

  }
  /**
  * Edit feedback form
  *
  * Saves the form title.
  *
  * @access public
  */
  function editFeedbackForm() {
    $filter = array(
      'feedback_form_id' => (int)$this->feedbackFormDetail['feedback_form_id']
    );
    $dataTrans = array(
      'feedback_form_title' => trim($this->params['feedback_form_title'])
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableFeedbackForms, $dataTrans, $filter
    );
  }

  /**
  * Delete feedback form
  *
  * @access public
  */
  function deleteFeedbackForm() {
    return FALSE !== $this->databaseDeleteRecord(
      $this->tableFeedbackForms, 'feedback_form_id', $this->params['ffid']
    );
  }

  /**
  *
  *
  * @see base_db::databaseUpdateRecord
  * @param string $formXML
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function saveFormStructure($formXML) {
    $data = array(
      'feedback_form_structure' => $formXML
    );
    return $this->databaseUpdateRecord(
      $this->tableFeedbackForms, $data, 'feedback_form_id', $this->params['ffid']
    );
  }

  /**
  * mark feedback as read
  *
  * @param int $feedbackId
  * @access public
  */
  function markFeedbackRead($feedbackId) {
    $dataTrans = array(
      'feedback_new' => 1);
    $filter = array(
      'feedback_id' => $feedbackId
    );
    $this->databaseUpdateRecord($this->tableFeedback, $dataTrans, $filter);
  }
}
?>
