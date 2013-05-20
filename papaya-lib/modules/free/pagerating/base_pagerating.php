<?php
/**
* Page rating
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
* @subpackage Free-PageRating
* @version $Id: base_pagerating.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Base database class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Base class page rating
* @package Papaya-Modules
* @subpackage Free-PageRating
*/
class base_pagerating extends base_db {

  /**
  * Table Links
  * @var string $tableLink
  */
  var $tableLink = '';
  /**
  * Table results
  * @var string $tableResults
  */
  var $tableResults = '';
  /**
  * Table abstract
  * @var string $tableAbstract
  */
  var $tableAbstract = '';
  /**
  * Table sites
  * @var string $tableSites
  */
  var $tableSites = '';
  /**
  * Table topic
  * @var string $tableTopics
  */
  var $tableTopics = '';
  /**
  * Table topic translation
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = '';
  /**
  * Table surfer
  * @var string $tableSurfer
  */
  var $tableSurfer = '';

  /**
  * poll data
  * @var array $pollData
  */
  var $pollData = NULL;

  /**
  * PHP5 constructor
  * @param string $paramName Name des Parameterarrays
  */
  function __construct($paramName = 'rate') {
    $this->tableLink = PAPAYA_DB_TBL_SURFERPERMLINK;
    $this->tableSurfer = PAPAYA_DB_TBL_SURFER;
    $this->tableResults = PAPAYA_DB_TABLEPREFIX.'_pagerating';
    $this->tableAbstract = PAPAYA_DB_TABLEPREFIX.'_pagerating_abstract';
    $this->tableSites = PAPAYA_DB_TABLEPREFIX.'_pagerating_sites';
    $this->tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_'.$paramName;
  }

  /**
  * Initialize
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
  }

  /**
  * Check if user can vote again
  *
  * @param integer $topicId
  * @access public
  * @return mixed boolean or data
  */
  function userCanVote($topicId) {
    $userId = 0;

    include_once(PAPAYA_INCLUDE_PATH.'system/base_robots.php');
    if (base_robots::checkRobot()) {
      return FALSE;
    }

    //check the surfer
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();
    if (isset($this->surferObj) && is_object($this->surferObj) &&
        $this->surferObj->isValid) {
      $userId = $this->surferObj->surferId;
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE topic_id = %d
                 AND user_id = %d";
      $params = array($this->tableResults, (int)$topicId, (int)$userId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        list($count) = $res->fetchRow();
        if ($count > 0) {
          return FALSE;
        }
      }
    }

    //check the cookie
    if (isset($_COOKIE['poll']) && isset($this->pollData['poll_id']) &&
        isset($_COOKIE['poll'][$this->pollData['poll_id']])) {
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE topic_id = %d
                 AND surfer_cookie = '%s'";
      $params = array($this->tableResults,
        (int)$topicId, @$_COOKIE['poll'][$topicId]);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        list($count) = $res->fetchRow();
        if ($count > 0) {
          return FALSE;
        }
      }
    }

    //check the ip
    $time = time() - 1800;
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE topic_id = '%d'
               AND surfer_ip = '%s'
               AND surfer_time >= '%d'";
    $params = array($this->tableResults, (int)$topicId, $_SERVER['REMOTE_ADDR'], $time);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      list($count) = $res->fetchRow();
      if ($count > 0) {
        return FALSE;
      }
    }
    return array('userId' => $userId, 'ip' => $_SERVER['REMOTE_ADDR']);
  }

  /**
  * Add result to database
  *
  * @param integer $topicId
  * @param integer $lngId
  * @param integer $vote
  * @access public
  * @return boolean
  */
  function addResult($topicId, $lngId, $vote) {
    srand((double)microtime() * 1000000);
    $cookie = md5(uniqid(rand()));
    $now = time();
    if ($ret = $this->userCanVote($topicId) ) {
      $data = array(
        'topic_id' => $topicId,
        'answer_id' => $vote,
        'surfer_time' => (int)$now,
        'surfer_ip' => $ret['ip'],
        'user_id' => (int)$ret['userId'],
        'surfer_cookie' => $cookie,
        'lng_id' => $lngId,
      );
      if ($this->databaseInsertRecord($this->tableResults, 'result_id', $data, $lngId)) {
        setcookie('topic['.(int)$topicId.']', $cookie);
        $this->loadStatisticData($lngId, $topicId);
        return TRUE;
      } else {
        return FALSE;
      }
    } else {
      return FALSE;
    }
  }

  /**
  * Load statistic data and fill statistic tables
  *
  * @param mixed $lngId optional, default value NULL
  * @access public
  * @return boolean
  */
  function loadStatisticData($lngId = NULL, $topicId = NULL) {
    if ($lngId === NULL) {
      $sqlLng = "SELECT lng_id
                   FROM %s
                  GROUP BY lng_id";
      if ($res = $this->databaseQueryFmt($sqlLng, array($this->tableAbstract))) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->loadStatisticData($row['lng_id'], $topicId);
        }
      }
      return TRUE;
    } else {
      if (isset($topicId) && $topicId > 0) {
        $filter = " AND topic_id = '".(int)$topicId."'";
        $this->databaseDeleteRecord($this->tableAbstract, 'topic_id', $topicId);
      } else {
        $filter = "";
        $this->databaseEmptyTable($this->tableAbstract);
      }
      $sql = "INSERT INTO %s(topic_id, answer_id, count, lng_id)
              SELECT topic_id, answer_id, COUNT(*), lng_id
                FROM %s
               WHERE lng_id = '%d' $filter
               GROUP BY topic_id, answer_id, lng_id";
      $params = array($this->tableAbstract, $this->tableResults, $lngId);
      if ($res = $this->databaseQueryFmtWrite($sql, $params)) {
        $sql = "INSERT INTO %s(topic_id, count, lng_id)
                SELECT topic_id, COUNT(*), lng_id
                  FROM %s
                WHERE lng_id = '%d' $filter
                GROUP BY topic_id, lng_id";
        $params = array($this->tableAbstract, $this->tableResults, $lngId);
        if ($res = $this->databaseQueryFmtWrite($sql, $params)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Calculate Rating
  *
  * @access public
  */
  function calculateRating() {
    if (isset($this->pageList) &&
        is_array($this->pageList) &&
        count($this->pageList) > 0) {
      foreach ($this->pageList as $pid => $page) {
        if (isset($page['votes']) && isset($page['answer'])) {
          $rating = $this->getRating($page['votes'], $page['answer']);
          $this->pageList[$pid]['rating'] = $rating;
        }
      }
      $this->sortPageList();
    }
  }

  /**
   * Checks if the 'sort' parameter is set and calls the array sorting function
   * uasort. The parameter value specifies which field of the array to evaluate as
   * sorting key.
   */
  function sortPageList() {
    if (isset($this->params['sort'])) {
      switch($this->params['sort']) {
      case 'rating':
        uasort($this->pageList, array(&$this, 'compareRatings'));
        break;
      case 'votes':
        uasort($this->pageList, array(&$this, 'compareVotes'));
        break;
      case 'topic_title':
        uasort($this->pageList, array(&$this, 'compareTitles'));
        break;
      case 'id':
        uasort($this->pageList, array(&$this, 'compareIds'));
        break;
      default:
        break;
      }
    }
  }

  /**
   * Compares array element $a to array element $b. This function uses the
   * topic_id field as sort key.
   *
   * @param array $a the first array element of the comparison
   * @param array $b the second array element of the comparison
   * @return 0 iff $a equals $b, -1 iff $a is less than $b, 1 otherwise.
   */
  function compareIds($a, $b) {
    $_a = $a['detail']['topic_id'];
    $_b = $b['detail']['topic_id'];
    if ($_a == $_b) {
      return 0;
    }
    return ($_a < $_b)? -1 : 1;
  }

  /**
   * Compares array element $a to array element $b. This function uses the topic
   * title field as sort key.
   *
   * @param array $a the first array element of the comparison
   * @param array $b the second array element of the comparison
   * @return 0 iff $a equals $b, -1 iff $a is less than $b, 1 otherwise.
   */
  function compareTitles($a, $b) {
    $_a = $a['detail']['topic_title'];
    $_b = $b['detail']['topic_title'];
    if ($_a == $_b) {
      return 0;
    }
    return ($_a < $_b)? -1 : 1;
  }

  /**
   * Compares array element $a to array element $b. This function uses the rating
   * value field as sort key.
   *
   * @param array $a the first array element of the comparison
   * @param array $b the second array element of the comparison
   * @return 0 iff $a equals $b, -1 iff $a is greater than $b, 1 otherwise.
   */
  function compareRatings($a, $b) {
    if ($a['rating'] == $b['rating']) {
      return 0;
    }
    return ($a['rating'] > $b['rating']) ? -1:1;
  }

  /**
   * Compares array element $a to array element $b. This function uses the votes
   * value field as sort key.
   *
   * @param array $a the first array element of the comparison
   * @param array $b the second array element of the comparison
   * @return 0 iff $a equals $b, -1 iff $a is greater than $b, 1 otherwise.
   */
  function compareVotes($a, $b) {
    if ($a['votes'] == $b['votes']) {
        return 0;
    }
    return ($a['votes'] > $b['votes']) ? -1 : 1;
  }

  /**
  * Loads the list of all pages that have been rated and saves the data in
  * $this->pageList.
  *
  * @param integer $lngId
  * @param mixed $topicId optional, default value NULL
  * @param integer $offset optional, default value 0
  * @param integer $rowCount optional, default value 100
  * @access public
  * @return boolean $watch
  */
  function loadPageList($lngId, $topicId = NULL, $offset = 0, $rowCount = 100) {
    $watch = FALSE;
    $keys = array();
    $filter = ($topicId !== NULL) ? sprintf("AND topic_id = '%d'", (int)$topicId) : '';
    unset($this->pageList);
    $sql = "SELECT DISTINCT topic_id
              FROM %s
             WHERE lng_id = '%d' $filter";
    $params = array($this->tableAbstract, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params, $rowCount, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $keys[] = $row['topic_id'];
        $watch = TRUE;
      }
    }
    if (isset($keys) && is_array($keys) && count($keys) > 0) {
      $filter = $this->databaseGetSQLCondition('topic_id', $keys);
      $sql = "SELECT topic_id, answer_id, count
                FROM %s
               WHERE lng_id = '%d'
                 AND $filter";
      $params = array($this->tableAbstract, $lngId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->pageOrder[] = $row['topic_id'];
          if ($row['answer_id'] != 0) {
            $this->pageList[$row['topic_id']]['answer'][$row['answer_id']] =
              $row['count'];
          } else {
            $this->pageList[$row['topic_id']]['votes'] = $row['count'];
          }
        }
      }
    }
    return $watch;
  }

  /**
  * Loads the topic title for all topics that have been rated and stores them into
  * $this->pageList.
  *
  * @param integer $lngId
  * @access public
  */
  function loadTopicTitle($lngId) {
    if (isset($this->pageList) &&
        is_array($this->pageList) &&
        count($this->pageList) > 0) {
      $filter = $this->databaseGetSQLCondition('topic_id', array_keys($this->pageList));
      $sql = "SELECT topic_id, topic_title
                FROM %s
               WHERE lng_id = '%d'
                 AND $filter";
      $params = array($this->tableTopicsTrans, $lngId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->pageList[$row['topic_id']]['detail'] = $row;
        }
      }
    }
  }

  /**
  * Get rating
  *
  * <p>in words:
  *  - multiply each rating-value with its votes
  *  - sum up these products
  *  - divide this value by total sum of votes
  *  - divide this value by maximum absolute rating-value
  *  - multiply by 100 to get the rating in percent
  * </p>
  * <p>formula:
  *
  *  /  i=max(x)     \     / i=max(x) \
  * |      __         |   |    __      |
  * |     \           |   |   \        |
  * |      >   x*n    | / |    >   n   | / max(|x|) * 100
  * |     /__     x   |   |   /__   x  |
  * |                 |   |            |
  * |   i=min(x)      |   |  i=min(x)  |
  *  \               /     \          /
  *
  * </p>
  *
  * @param integer $count total number of votings, e.g. 10
  * @param array $answers e.g. array(-1 => 3, 1 => 4, 2 => 1)
  * @return integer $result {-100, 100} rating in percent, e.g. 15
  */
  function getRating($count, $answers) {
    $result = 0;
    if (isset($answers) && is_array($answers) && count($answers) > 0) {
      $sumVotes = 0;
      $highest = 0;
      foreach ($answers as $vote => $voteCount) {
        $sumVotes += $vote * $voteCount;
        if (abs($vote) > $highest) {
          $highest = abs($vote);
        }
      }
      if ($highest > 0 && $count > 0) {
        $result = round($sumVotes / $count / $highest * 100);
      }
      return $result;
    }
    return 0;
  }

}