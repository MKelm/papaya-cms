<?php
/**
* Query log base class
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
* @subpackage Free-QueryLog
* @version $Id: base_querylog.php 34151 2010-04-30 14:06:19Z elbrecht $
*/

/**
* Basic class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Query log base class
*
* @package Papaya-Modules
* @subpackage Free-QueryLog
*/
class base_querylog extends base_db {

  /**
  * Papaya database table guestbooks
  * @var string $tableQueryLog
  */
  var $tableQueryLog = PAPAYA_DB_TBL_LOG_QUERIES;

  /**
  * Absolute count of latest database query
  * @var int $absCount
  */
  var $absCount = 0;

  /**
  * Constructor
  */
  function __construct(&$msgs, $paramName = "qlog") {
    parent::__construct($paramName);
    // Set param name
    $this->paramName = $paramName;
  }

  /**
  * PHP4 constructor
  */
  function base_querylog(&$msgs, $paramName = "qlog") {
    $this->__construct($msgs, $paramName);
  }

  /**
  * Get query details by id
  *
  * @access public
  * @param int $queryId
  * @return array
  */
  function getQueryDetailsById($queryId) {
    $sql = "SELECT query_id, query_class, query_conn,
                   query_time, query_records, query_limit,
                   query_offset, query_content, query_explain,
                   query_backtrace, query_uri, query_timestamp
              FROM %s
             WHERE query_id = '%d'";
    $sqlParams = array($this->tableQueryLog, $queryId);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row;
      }
    }
    return $result;
  }

  /**
  * Get query details by content hash
  *
  * @access public
  * @param string $queryHash
  * @return array
  */
  function getQueryDetailsByHash($queryHash) {
    $sql = "SELECT query_id, query_class, query_conn,
                   query_time, query_records, query_limit,
                   query_offset, query_content, query_hash,
                   query_explain, query_backtrace, query_uri,
                   query_timestamp
              FROM %s
             WHERE query_hash = '%s'";
    $sqlParams = array($this->tableQueryLog, $queryHash);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, 1)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result = $row;
      }
    }
    return $result;
  }

  /**
  * Get slowest queries
  *
  * @access public
  * @param int $limit optional, default 25
  * @param int $offset optional, default 0
  * @return array
  */
  function getSlowestQueries($limit = 25, $offset = 0) {
    // Prepare database query and result array
    $sql = "SELECT query_id, query_content, query_uri, query_class, query_time
              FROM %s
             WHERE query_content NOT LIKE '%%%1\$s%%'
          ORDER BY query_time DESC";
    $sqlParams = array($this->tableQueryLog);
    $result = array();
    // Perform the query
    $this->databaseEnableAbsoluteCount();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
      $this->absCount = $res->absCount();
    }
    // Return result
    return $result;
  }

  /**
  * Get most frequently used queries (per page request)
  *
  * @access public
  * @param int $limit optional, default 25
  * @param int $offset optional, default 0
  * @return array
  */
  function getMostFrequentQueries($limit = 25, $offset = 0) {
    // Prepare database query and result array
    $sql = "SELECT DISTINCT COUNT(query_id) as num_queries, query_content, query_hash,
                   query_request, MIN(query_uri) AS query_uri,
                   MIN(query_class) AS query_class
              FROM %s
          GROUP BY query_hash, query_request, query_content
         HAVING query_content NOT LIKE '%%%1\$s%%'
          ORDER BY num_queries DESC";
    $sqlParams = array($this->tableQueryLog);
    $result = array();
    // Perform the query
    $this->databaseEnableAbsoluteCount();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
      $this->absCount = $res->absCount();
    }
    // Return result
    return $result;
  }

  /**
  * Get complicated queries (using filesort and/or temporary tables)
  *
  * @access public
  * @param int $limit optional, default 25
  * @param int $offset optional, default 0
  * @return array
  */
  function getComplicatedQueries($limit = 25, $offset = 0) {
    // Prepare database query and result array
    $sql = "SELECT query_id, query_content, query_time, query_uri, query_class
              FROM %s
             WHERE (query_explain LIKE '%%temporary%%'
                OR query_explain LIKE '%%filesort%%')
               AND query_content NOT LIKE '%%%1\$s%%'
          ORDER BY query_time DESC";
    $sqlParams = array($this->tableQueryLog);
    $result = array();
    // Perform the query
    $this->databaseEnableAbsoluteCount();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
      $this->absCount = $res->absCount();
    }
    // Return result
    return $result;
  }

  /**
  * Search queries
  *
  * By default, the search will be performed in the query text;
  * optionally, you can also search in all free text fields which is slower
  *
  * @access public
  * @param string $search
  * @param string $searchBy ('sql'|'all'), optional, default 'sql'
  * @param int $limit optional, default 25
  * @param int $offset optinal, default 0
  */
  function searchQueries($search, $searchBy = 'sql', $limit = 25, $offset = 0) {
    // Convert search string to SQL pattern
    $search = addslashes($search);
    $search = str_replace('*', '%', $search);
    // Prepare database query and result array
    $condition = " WHERE query_content LIKE '".$search."'";
    if ($searchBy == 'all') {
      $condition .= " OR query_class LIKE '".$search."'";
      $condition .= " OR query_class LIKE '".$search."'";
      $condition .= " OR query_explain LIKE '".$search."'";
      $condition .= " OR query_backtrace LIKE '".$search."'";
      $condition .= " OR query_uri LIKE '".$search."'";
    }
    $sql = "SELECT query_id, query_content, query_time, query_uri, query_class
              FROM %s ".str_replace('%', '%%', $condition)." ORDER BY query_time DESC";
    $sqlParams = array($this->tableQueryLog);
    $result = array();
    // Perform the query
    $this->databaseEnableAbsoluteCount();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row;
      }
      $this->absCount = $res->absCount();
    }
    // Return result
    return $result;
  }
}
