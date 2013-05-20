<?php
/**
* Base quiz
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
* @subpackage Free-Quiz
* @version $Id: base_quiz.php 34150 2010-04-30 13:51:32Z elbrecht $
*/


/**
* Basicclass for database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Base quiz
*
* @package Papaya-Modules
* @subpackage Free-Quiz
*/
class base_quiz extends base_db {

  /**
  * Answer
  * @var array $answer
  */
  var $answer = NULL;

  /**
  * Answers array
  * @var array $answers
  */
  var $answers = NULL;

  /**
  * Question
  * @var array $question
  */
  var $question = NULL;

  /**
  * Question array
  * @var array $questions
  */
  var $questions = NULL;

  /**
  * Group
  * @var array $group
  */
  var $group = NULL;

  /**
  * Groups array
  * @var array $groups
  */
  var $groups = NULL;

  /**
  * Group array tree
  * @var array $groupTree
  */
  var $groupTree = NULL;

  /**
  * Group detail
  * @var array $groupDetail
  */
  var $groupDetail = NULL;

  /**
  * Database table answer
  * @var string $tableAnswer
  */
  var $tableAnswer = '';

  /**
  * Database table group
  * @var string $tableGroup
  */
  var $tableGroup = '';

  /**
  * Database table group trans
  * @var string $tableGroupTrans
  */
  var $tableGroupTrans = '';

  /**
  * Database table question
  * @var string $tableQuestion
  */
  var $tableQuestion = '';

  /**
  * Constructor initialisize class variables
  *
  * @param string $paramName optional, default value 'quiz'
  * @access public
  */
  function __construct($paramName = 'quiz') {
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_'.$paramName;
    $this->tableAnswer = PAPAYA_DB_TABLEPREFIX.'_quiz_answer';
    $this->tableGroup = PAPAYA_DB_TABLEPREFIX.'_quiz_group';
    $this->tableGroupTrans = PAPAYA_DB_TABLEPREFIX.'_quiz_group_trans';
    $this->tableQuestion = PAPAYA_DB_TABLEPREFIX.'_quiz_question';
  }

  /**
  * Initialisize parameters
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
  }

  /**
  * Load group
  *
  * @param integer $id
  * @param integer $baseId optional, default value 0
  * @access public
  * @return boolean
  */
  function loadGroup($id, $baseId = 0) {
    unset($this->group);
    $sql = "SELECT group_id, group_parent_path, group_parent
              FROM %s
             WHERE group_id = '%d'";
    $params = array($this->tableGroup, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($baseId == 0 || $id == $baseId ||
            (strpos($row['catalog_parent_path'], ';'.$baseId.';') !== FALSE)) {
          $this->group = $row;
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Load groups
  *
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function loadGroups($lngId) {
    unset($this->groups);
    unset($this->groupTree);
    $ids = array(0);
    if (isset($this->groupsOpen) && is_array($this->groupsOpen)) {
      foreach ($this->groupsOpen AS $groupId=>$opened) {
        if ($opened) {
          $ids[] = (int)$groupId;
        }
      }
    }
    if (count($ids) > 1) {
      $filter = " IN ('".implode("', '", $ids)."') ";
    } else {
      $filter = " = '0' ";
    }
    $sql = "SELECT c.group_id, c.group_parent, ct.lng_id,
                   ct.groupdetail_title, ct.groupdetail_text
              FROM %s AS c
              LEFT OUTER JOIN %s AS ct
                ON (ct.group_id = c.group_id AND ct.lng_id = '%d')
             WHERE c.group_parent $filter
             ORDER BY ct.groupdetail_title, c.group_id DESC";
    $params = array($this->tableGroup, $this->tableGroupTrans, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->groups[(int)$row['group_id']] = $row;
        $this->groupTree[(int)$row['group_parent']][] = $row['group_id'];
      }
      $this->loadGroupCounts();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load group tree
  *
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function loadGroupTree($lngId) {
    unset($this->groups);
    unset($this->groupTree);

    $ids = array(0);
    if (isset($this->groupsOpen) && is_array($this->groupsOpen)) {
      foreach ($this->groupsOpen AS $groupId => $opened) {
        if ($opened) {
          $ids[] = (int)$groupId;
        }
      }
    }
    if (count($ids) > 1) {
      $filter = " IN ('".implode("', '", $ids)."') ";
    } else {
      $filter = " = '0' ";
    }
    $sql = "SELECT c.group_id, c.group_parent, c.group_parent_path, ct.lng_id,
                   ct.groupdetail_title, ct.groupdetail_text
              FROM %s AS c
              LEFT OUTER JOIN %s AS ct
                ON (ct.group_id=c.group_id AND ct.lng_id = '%d')
             ORDER BY ct.groupdetail_title, c.group_id DESC";
    $params = array($this->tableGroup, $this->tableGroupTrans, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->groups[(int)$row['group_id']] = $row;
        $this->groupTree[(int)$row['group_parent']][] = $row['group_id'];
      }
      $this->loadGroupCounts();
      return TRUE;
    }
    return FALSE;
  }


  /**
  * Load group counts
  *
  * @param boolean $mode optional, default value TRUE
  * @access public
  * @return boolean
  */
  function loadGroupCounts($mode = TRUE) {
    $ids = array();
    $filter = '';
    if (isset($this->groupsOpen) && is_array($this->groupsOpen)) {
      if (isset($this->groups) && is_array($this->groups)) {
        $ids = array_keys($this->groups);
      } else {
        $ids = array();
      }
      if (count($ids) > 1) {
        $filter = " WHERE group_parent IN ('".implode("', '", $ids)."') ";
      } else {
        $filter = " WHERE group_parent = '".@(int)$ids[0]."' ";
      }
    }
    $sql = "SELECT COUNT(*) AS subcategs, group_parent
              FROM %s
              $filter
             GROUP BY group_parent";
    if ($res = $this->databaseQueryFmt($sql, $this->tableGroup)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->groups[(int)$row['group_parent']]['CATEG_COUNT'] = $row['subcategs'];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load group details
  *
  * @param integer $id
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function loadGroupDetail($id, $lngId) {
    if (isset($this->group)) {
      $sql = "SELECT groupdetail_title, groupdetail_text, lng_id
                FROM %s
               WHERE group_id = '%d'
                 AND lng_id = '%d'";
      $params = array($this->tableGroupTrans, $id, $lngId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->groupDetail = $row;
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Load question
  *
  * @param integer $id
  * @access public
  * @return booelan
  */
  function loadQuestion($id) {
    $sql = "SELECT question_id, question_title, question_text, question_link,
                   question_number, lng_id
              FROM %s
             WHERE question_id = '%d'";
    $params = array($this->tableQuestion, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->question = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load answer
  *
  * @param integer $id
  * @access public
  * @return booelan
  */
  function loadAnswer($id) {
    $sql = "SELECT answer_id, question_id, lng_id, answer_text, answer_explanation,
                   answer_right, answer_number, answer_response
              FROM %s
             WHERE answer_id = '%d'";
    $params = array($this->tableAnswer, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->answer = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get next smaller question sort-id
  *
  * @param integer $value
  * @access public
  * @return integer next smaller question sort-id
  */
  function getNextSmallerQ($value) {
    $ret = 0;
    $sql = "SELECT MAX(question_number) AS max
              FROM %s
             WHERE question_number < '%d'";
    $params = array($this->tableQuestion, $value);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $ret = $row['max'];
      }
    }
    return $ret;
  }

  /**
  * Get next bigger question sort-id
  *
  * @param integer $value
  * @access public
  * @return integer next bigger question sort-id
  */
  function getNextBiggerQ($value) {
    $ret = 0;
    $sql = "SELECT MIN(question_number) AS min
              FROM %s
             WHERE question_number > '%d'";
    $params = array($this->tableQuestion, $value);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $ret = $row['min'];
      }
    }
    return $ret;
  }

  /**
  * Get next smaller answer sort-id
  *
  * @param integer $value
  * @access public
  * @return integer next smaller question sort-id
  */
  function getNextSmallerA($value) {
    $ret = 0;
    $sql = "SELECT MAX(answer_number) AS max
              FROM %s
             WHERE answer_number < '%d'";
    $params = array($this->tableAnswer, $value);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $ret = $row['max'];
      }
    }
    return $ret;
  }

  /**
  * Get next bigger answer sort-id
  *
  * @param integer $value
  * @access public
  * @return integer next bigger answer sort-id
  */
  function getNextBiggerA($value) {
    $ret = 0;
    $sql = "SELECT MIN(answer_number) AS min
              FROM %s
             WHERE answer_number > '%d'";
    $params = array($this->tableAnswer, $value);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $ret = $row['min'];
      }
    }
    return $ret;
  }

  /**
  * Load question list
  *
  * @param integer $groupId
  * @param integer $lngId
  * @access public
  */
  function loadQuestionList($groupId, $lngId) {
    unset($this->questions);
    $sql = "SELECT question_id, group_id, lng_id, question_title,
                   question_link, question_text, question_number
              FROM %s
             WHERE group_id = '%d'
               AND lng_id = '%d'
             ORDER BY question_number";
    $params = array($this->tableQuestion, $groupId, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->questions[$row['question_id']] = $row;
      }
    }
  }

  /**
  * Load answer list
  *
  * @param integer $questionId
  * @param integer $lngId
  * @access public
  */
  function loadAnswerList($questionId, $lngId) {
    unset($this->answers);
    $sql = "SELECT answer_id, question_id, lng_id, answer_text, answer_explanation,
                   answer_right, answer_number, answer_response
              FROM %s
             WHERE question_id = '%d'
               AND lng_id = '%d'
             ORDER BY answer_number";
    $params = array($this->tableAnswer, $questionId, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->answers[$row['answer_id']] = $row;
      }
    }
  }

  /**
  * Load answer list
  *
  * @param integer $questionId
  * @param integer $lngId
  * @access public
  */
  function loadAnswerByIds($answerdIds, $lngId) {
    unset($this->answers);
    $filter = $this->databaseGetSQLCondition('answer_id', $answerdIds);
    $sql = "SELECT answer_id, question_id, lng_id, answer_text, answer_explanation,
                   answer_right, answer_number, answer_response
              FROM %s
             WHERE lng_id = '%d' AND $filter
             ORDER BY answer_number";
    $params = array($this->tableAnswer, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->answers[$row['answer_id']] = $row;
      }
    }
  }

  /**
  * Does group exist ?
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function groupExist($id) {
    if ($id == 0) {
      return TRUE;
    }
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE group_id = '%d'";
    $params = array($this->tableGroup, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() >= 1);
    }
    return FALSE;
  }

  /**
  * Group is empty ?
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function groupIsEmpty($id) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE group_parent = '%d'";
    $params = array($this->tableGroup, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() == 0);
    }
    return TRUE;
  }

  /**
  * Get box output xml
  *
  * @param array $box
  * @param array $data
  * @access public
  * @return string box xml
  */
  function getBoxOutput($box, $data) {
    $single = FALSE;
    $answer = NULL;
    $ret = '';
    $ret .= sprintf(
      '<quiz action="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->getWebLink())
    );
    if (!isset($this->params['answer_id'])) {
      $this->loadQuestionList($data['quiz'], $box->parentObj->getContentLanguageId());
      $questSelect = array_keys($this->questions);
      $max = count($questSelect);
      srand((double)microtime() * 1000000);
      $randomValue = rand(0, ($max - 1));
      $questionId = $questSelect[$randomValue];
      $question = $this->questions[$questionId];
      $this->loadAnswerList($questionId, $box->parentObj->getContentLanguageId());
      $answer = $this->answers;
    } elseif (isset($this->params['answer_id'])) {
      // questions answered, output correct answer
      $this->loadAnswer($this->params['answer_id']);
      $answer = $this->answer;
      $this->loadQuestion($answer['question_id']);
      $questionId = $answer['question_id'];
      $question = $this->question;
      $single = TRUE;
    }

    $ret .= sprintf(
      '<question id="%d" title="%s" fieldname="%s[question_id]">%s</question>'.LF,
      (int)$question['question_id'],
      $box->getXHTMLString($question['question_title']),
      $this->paramName,
      $box->getXHTMLString($question['question_text'])
    );
    if ($answer != NULL && !$single) {
      foreach ($answer as $id => $value) {
        $ret .= sprintf(
          '<answer href="%s" id="%d" fieldname="%s[answer_id]">%s'.
          '<explanation>%s</explanation></answer>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink(
              NULL,
              NULL,
              NULL,
              array('answer_id' => (int)$value['answer_id']),
              $this->paramName
            )
          ),
          (int)$value['answer_id'],
          papaya_strings::escapeHTMLChars($this->paramName),
          $box->getXHTMLString($value['answer_text']),
          $box->getXHTMLString($value['answer_explanation'])
        );
      }
    } else {
      $ret .= sprintf(
        '<reply right="%d">%s</reply>'.LF,
        (int)$answer['answer_right'],
         $box->getXHTMLString($answer['answer_response'])
      );
      $this->params['question_id'] = NULL;
      $this->params['answer_id'] = NULL;
    }
    $ret .= '</quiz>'.LF;
    return $ret;
  }

  // --------------------------------------  CONTENT ---------------------------------------------

  /**
  * Get content output xml
  *
  * @param array $contentObj calling content object
  * @param array $data data from content object
  * @return string $result content xml
  */
  function getContentOutput($contentObj, $data) {
    $result = '';
    $this->loadQuestionList(
      $data['quiz'],
      $contentObj->parentObj->getContentLanguageId()
    );
    if (isset($this->questions) && is_array($this->questions) && count($this->questions > 0)) {
      $questionIds = array_keys($this->questions);

      // store answers in session
      if (isset($this->params['question_id']) && isset($this->params['answer_id'])) {
        $answerIds = $this->getSessionValue($this->sessionParamName.'_quiz_answers');
        if (!is_array($answerIds)) {
          $answerIds = array();
        }
        $answerIds[$this->params['question_id']] = $this->params['answer_id'];
        $this->setSessionValue($this->sessionParamName.'_quiz_answers', $answerIds);
      }

      $quid = $questionIds[0];
      $qNr = 1;
      $lastId = -1;
      if (isset($this->params['question_id'])) {
        $add = 0;
        foreach ($questionIds as $i => $id) {
          if ($id == $this->params['question_id']) {
            if (isset($this->params['answer_id'])) {
              $add = 1;
            }
            $quid = @$questionIds[$i + $add];
            $qNr = $i + 1 + $add;
            break;
          }
        }
        $sort = $questionIds;
        sort($sort);
        $lastId = array_pop($sort);
      }

      $result .= sprintf(
        '<quiz action="%s">'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getWebLink(
            NULL,
            NULL,
            NULL,
            array('question_id' => $quid),
            $this->paramName
          )
        )
      );
      if (isset($this->questions[$quid])) {
        $question = $this->questions[$quid];
        $result .= sprintf(
          '<question number="%d" id="%d" last="%d" title="%s"'.
          ' fieldname="%s[question_id]">%s</question>'.LF,
          $qNr,
          $question['question_id'],
          ($quid == $lastId),
          papaya_strings::escapeHTMLChars($question['question_title']),
          $this->paramName,
          $contentObj->getXHTMLString($question['question_text'])
        );
        $this->params['question_id'] = NULL;
        $this->params['answer_id'] = NULL;
        $this->loadAnswerList($quid, $contentObj->parentObj->getContentLanguageId());
        if (isset($this->answers) && is_array($this->answers)) {
          foreach ($this->answers as $id => $value) {
            $result .= sprintf(
              '<answer id="%d" fieldname="%s[answer_id]">'.
              '<text>%s</text><explanation>%s</explanation></answer>'.LF,
              (int)$value['answer_id'],
              $this->paramName,
              $contentObj->getXHTMLString($value['answer_text']),
              $contentObj->getXHTMLString($value['answer_explanation'])
            );
          }
        }
      } elseif (!isset($this->questions[$quid])) {
        $answerIds = $this->getSessionValue($this->sessionParamName.'_quiz_answers');
        if (isset($answerIds) && is_array($answerIds) && count($answerIds) > 0) {
          ksort($answerIds);
          $this->loadAnswerByIds($answerIds, $contentObj->parentObj->getContentLanguageId());
          $result .= '<summary>'.LF;
          $j = 1;
          foreach ($answerIds as $questionId => $answerId) {
            if (isset($this->answers[$answerId]) && isset($this->questions[$questionId])) {
              $answer = &$this->answers[$answerId];
              $question = &$this->questions[$questionId];
              $result .= sprintf(
                '<question number="%d"><title>%s</title>'.
                '<link>%s</link><text>%s</text>'.LF,
                $j++,
                $contentObj->getXHTMLString($question['question_title']),
                $contentObj->getXHTMLString($question['question_link']),
                $contentObj->getXHTMLString($question['question_text'])
              );
              $result .= sprintf(
                '<given_answer>%s</given_answer>',
                $contentObj->getXHTMLString($answer['answer_text'])
              );
              $result .= sprintf(
                '<explanation>%s</explanation>',
                $contentObj->getXHTMLString($answer['answer_explanation'])
              );
              $result .= sprintf(
                '<reply correct="%d">%s</reply>'.LF,
                (int)$answer['answer_right'],
                $contentObj->getXHTMLString($answer['answer_response'])
              );
              $result .= '</question>'.LF;
            }
          }
          $result .= '</summary>'.LF;
        }
      }
      $result .= '</quiz>'.LF;
      return $result;
    }
  }
}
?>
