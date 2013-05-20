<?php
/**
* Forum Database Access Class
***********************************************************************************
* This class provides basic access to forum related database tables. Forums are
* organized into a tree like category structure, where each forum is represented
* having a tree like thread structure. Output generation for each forum can be
* switched between BBS, Threaded and Threaded-BBS where the tree like structure
* is used in any of these cases. A BBS is nothing more or less than just having
* one parent node (the initial question/topic) having N childs on just one tree
* level, where the answers go. Additionally a subscription table is used to keep
* track of registered users thread subscriptions. This feature is available to
* registered users only while they are logged in properly.
*
* To get meta information about entrys authors the community module is used as
* far as the specific user is a registered one. The methods loading entries into
* memory create entries in a class array called users, which at first only contains
* ids of the registered users who wrote the entries loaded. This array is then
* substituted in a second step using one single query within the community module
* to get data about those users, when available.
*
* Thread notifications for administrators are stored within the entries-table
* for each entry and effect all administrators globally. It is not possible for
* one administrator to be notified about thread1 while another administrator
* is notified in thread2 only. Administrator notifications are sent to the
* email address stored using the content module/boxes.
*
* _forumcateg --{ _forum --{ _forumentries --{ _forumsubscriptions
*                            |                      |
*                            |                      |
*                            |                   [registered]
*                           / \                     |
*                          /   \                    |
*               [registered]  [!registered]         |
*                    |                              |
*                    +------------------------------+
*                    |
*                _surfers
*
* (fig1): [ --{ 1 to n ]
* Each forum is contained within one forum category which may be a subcategory
* of another forum category or the root forum category. Each forum entry belongs
* into a specific forum and is either created by an registered or an unregistered
* user. A forum entry having childs is called a thread. Threads can be subscribed
* by registered users.
**********************************************************************************
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Free-Forum
* @version $Id: base_forum.php 37747 2012-11-29 16:58:01Z smekal $
*/

/**
* Required classes
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');
require_once(PAPAYA_INCLUDE_PATH.'system/papaya_strings.php');

/**
* email class, needed for notifications/subscriptions
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');

/**
* Forum Database Access Class
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/
class base_forum extends base_db {
  
  const SHOW_FORUM = 0;
  const SHOW_COMMENTS = 1;
  const SHOW_LATEST = 2;
  
  const MODE_THREADED = 0;
  const MODE_BBS = 1;
  const MODE_THREADED_BBS = 2;

  /**
  * Instance of the page/box module
  * @var object
  */
  var $_owner = NULL;

  /**
  * Categories table
  * @var string $tableCategs
  */
  var $tableCategs = '';

  /**
  * Forum / boards table
  * @var string $tableBoards
  */
  var $tableBoards = '';

  /**
  * Entry table
  * @var string $tableEntries
  */
  var $tableEntries = '';

  /**
  * Surfer table
  * @var string $tableSurfers
  */
  var $tableSurfers = '';

  /**
  * Css input class
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'x-large';

  /**
  * name for page links
  * @var string $pageLinkTitle
  */
  var $pageLinkName = '';

  /**
  * Parameter prefix name
  * @var string $paramName
  */
  var $paramName = '';

  /**
  * Session parameter prefix name
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
  * Category
  * @var array $categ
  */
  var $categ = NULL;

  /**
  * Category tree
  * @var array $categTree
  */
  var $categTree = NULL;

  /**
  * Boards
  * @var array $boards
  */
  var $boards = NULL;

  /**
  * Board
  * @var array $board
  */
  var $board = NULL;

  /**
  * Entries
  * @var array $entries
  */
  var $entries = NULL;

  /**
  * Entry
  * @var array $entry
  */
  var $entry = NULL;

  /**
  * Entry tree
  * @var array $entryTree
  */
  var $entryTree = NULL;

  /**
  * Threads
  * @var array $threads
  */
  var $threads = NULL;

  /**
  * Thread id list
  * @var array $entryIdList
  */
  var $entryIdList = NULL;

  /**
  * Full text search ?
  * @var array $fullTextSearch
  */
  var $fullTextSearch = FALSE;

  /**
  * Cache search results
  * @var boolean $cacheSearchResults
  */
  var $cacheSearchResults = FALSE;

  /**
  * cut categories
  * @var array $cutCategs
  */
  var $cutCategs = NULL;

  /**
  * cut list
  * @var array $cutList
  */
  var $cutList = NULL;

  /**
  * cut threads
  * @var array $cutThreads
  */
  var $cutThreads = NULL;

  /**
  * Categories by id
  * @var array $categsById
  */
  var $categsById = NULL;

  /**
  * Topics currently loaded.
  * @var array $searchResults
  */
  var $searchResults = NULL;

  /**
  * Did we have to reject a potential attack string?
  * @var boolean $rejectedAttackStrings
  */
  var $rejectedAttackStrings = FALSE;

  /**
  * Entry too long?
  * @var boolean $entryTooLong
  */
  var $entryTooLong = FALSE;

  /**
  * Associative array with user information coming
  * from the community module. Will be initialized
  * when loading forum entries with empty arrays
  * which fields have to be substituted by the
  * information coming from the community.
  * We allways start with an empty array.
  * @var array $users
  */
  var $users = array();

  /**
  * Array to contain strings of all commands, that
  * are understood by this application.
  *
  * @var array $allowedCommands
  */
  var $allowedCommands = array(
    'add_entry',
    'edit_entry',
    'cite_entry',
    'subscribe_thread',
    'unsubscribe_thread'
  );

  /**
  * To prevent never ending recursion, depth of tree is
  * limited to a numeric value.
  *
  * @var int $maxIndent (> 0)
  */
  var $maxIndent = 10;

  /**
  * Allowed Tags for richtext editor.
  * @var array $allowedTags
  */
  var $allowedTags = array('b', 'i', 'tt', 'quote');

  /**
  * Count of total entries returned by the entry search
  * @var integer $_totalEntriesCount
  */
  var $_totalEntriesCount = 0;

  /**
  * searchstring parser object
  * @var searchStringParser $_searchStringParserObject
  */
  var $_searchStringParserObject = NULL;

  /**
  * total surfers count
  * @var integer $_totalSurfersCount
  */
  var $_totalSurfersCount;

  /**
   * Guid of the administration module holding the options.
   * @var string
   */
  var $_edModuleGuid = '62ddb02f4d397f55f90bd113f7f2d4cb';

  /**
  * Constructor
  * @param string $paramName Name des Parameterarrays
  */
  function __construct($paramName = 'ff') {
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_'.$paramName;
    $this->tableCategs = PAPAYA_DB_TABLEPREFIX.'_forumcategs';
    $this->tableBoards = PAPAYA_DB_TABLEPREFIX.'_forum';
    $this->tableEntries = PAPAYA_DB_TABLEPREFIX.'_forumentries';
    $this->tableSurfers = PAPAYA_DB_TABLEPREFIX.'_surfer';
    $this->tableSubscriptions = PAPAYA_DB_TABLEPREFIX.'_forumsubscriptions';
    if (!empty($GLOBALS['PAPAYA_PAGE']->requestData['filename'])) {
      $this->pageLinkName = (string)$GLOBALS['PAPAYA_PAGE']->requestData['filename'];
    } else {
      $this->pageLinkName = 'forum';
    }

    $this->fullTextSearch = PAPAYA_SEARCH_BOOLEAN;
  }

  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
  * Category
  **********************/

  /**
  * Does category exist?
  *
  * @param integer $id category id
  * @access public
  * @return boolean
  */
  function categExists($id) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE forumcat_id = %d";
    $params = array($this->tableCategs, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Is category empty?
  *
  * This method checks whether the category specified with the category
  * id contains a subcategory or a forum. If this is the case, this method
  * returns TRUE, otherwise it returns FALSE.
  *
  * @param integer $id category id
  * @access public
  * @return boolean TRUE when category is empty, otherwise FALSE
  */
  function categIsEmpty($id) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE forumcat_prev = '%d'";
    $params = array($this->tableCategs, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $result = $res->fetchField();
      if ($result[0] == 0) {
        $sql = "SELECT COUNT(*)
                  FROM %s
                 WHERE forumcat_id = '%d'";
        $params = array($this->tableBoards, $id);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          $result = $res->fetchRow();
          return ($result[0] == 0);
        }
      }
    }
    return FALSE;
  }

  /**
  * Returns the id of the category where a forum, with the
  * given forum id is located within the category tree.
  *
  * @param $forumId
  * @return integer $categoryId|boolean FALSE.
  */
  function getCategoryIdByForumId($forumId) {
    $sql = "SELECT forumcat_id
              FROM %s
             WHERE forum_id = '%d'";
    $params = array($this->tableBoards, $forumId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return (int)$res->fetchField();
    }
    return FALSE;
  }

  /**
  * Load categories
  *
  * @param integer $id optional, default value NULL
  * @param boolean $repair optional, default value NULL
  * @access public
  * @return boolean
  */
  function loadCategs($id = NULL, $repair = NULL) {
    unset($this->category);
    unset($this->categs);
    unset($this->categTree);
    if (isset($id)) {
      $sql = "SELECT forumcat_id, forumcat_prev, forumcat_title,
                     forumcat_desc, forumcat_path
                FROM %s
               WHERE forumcat_prev = '%d' OR forumcat_id = '%d'
               ORDER BY forumcat_title ASC, forumcat_id ASC";
    } elseif (isset($repair) && $repair) {
      $sql = "SELECT forumcat_id, forumcat_prev, forumcat_title, forumcat_path
               FROM %s
              WHERE forumcat_prev > -1
              ORDER BY forumcat_title ASC, forumcat_id ASC";
    } else {
      $sql = "SELECT forumcat_id, forumcat_prev, forumcat_title, forumcat_path, forumcat_desc
                FROM %s
               ORDER BY forumcat_title ASC, forumcat_id ASC";
    }
    if ($res = $this->databaseQueryFmt($sql, array($this->tableCategs, (int)$id, (int)$id))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['forumcat_id'] == $id) {
          $this->category = $row;
        } else {
          $this->categs[(int)$row['forumcat_id']] = $row;
          $this->categTree[(int)$row['forumcat_prev']][] = (int)$row['forumcat_id'];
        }
      }
      $this->categCount = $res->absCount();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load category
  *
  * @param integer $categId
  * @access public
  * @return boolean
  */
  function loadCateg($categId) {
    $sql = "SELECT forumcat_id, forumcat_desc, forumcat_path, forumcat_prev, forumcat_title
              FROM %s
             WHERE forumcat_id = '%d'";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableCategs, $categId))) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (isset($this->categs[(int)$row['forumcat_id']])) {
          $this->categs[(int)$row['forumcat_id']] = array_merge(
            $this->categs[(int)$row['forumcat_id']],
            $row
          );
        } else {
          $this->categs[(int)$row['forumcat_id']] = $row;
        }
        return $row;
      }
    }
    return FALSE;
  }

  /**
  * Load Categs by Ids from Array
  *
  * @param array $categIds
  * @access public
  * @return boolean
  */
  function loadCategsByIds($categIds) {
    $filter = str_replace('%', '%%', $this->databaseGetSQLCondition('forumcat_id', $categIds));
    $sql = "SELECT forumcat_id, forumcat_title
              FROM %s
             WHERE $filter
             ORDER BY forumcat_path";
    $params = array($this->tableCategs);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->categsById[(int)$row['forumcat_id']] = $row;
      }
      $this->categCount = $res->count();
      return TRUE;
    }
    return FALSE;
  }


  /**
  * Forum
  **********************/

  /**
  * Returns the id of a thread the provided entry is in.
  * First the entry_path attribute is checked if it is empty.
  * In this case entry_pid (parent id) is checked if it is empty.
  * If this one is empty as well, the entry does not have a parent
  * and is a root node within the tree. Therefore its entry id
  * is returned being a valid thread id. When entry_pid is defined
  * it is the thread id where this entry is in. If none of these
  * are not empty but the attribute entry_path is defined, the
  * thread id is extracted from there by returning the (n - 2)th
  * element in this path.
  *
  * @param array $entry (reference)
  * @return integer threadId
  */
  function getThreadId(&$entry) {
    if (!preg_match('/.+\;\d*\;.+/', $entry['entry_path'])) {
      if (isset($entry['entry_pid']) && (int)$entry['entry_pid'] == 0) {
        return (int)$entry['entry_id'];
      } else {
        return (int)$entry['entry_pid'];
      }
    } else {
      $ids = explode(';', $entry['entry_path']);
      return $ids[count($ids) - 2];
    }
  }

  /**
  * Deletes a surfers thread subscription when it exists.
  * If it does not exist, nothing is to be done.
  *
  * @param $surferHandle
  * @param $threadId
  */
  function clearSurferThreadSubscription($surferHandle, $threadId) {
    if ($this->checkSurferSubscribedThread($surferHandle, $threadId)) {
      $this->databaseDeleteRecord(
        $this->tableSubscriptions,
        array(
          'surfer_handle' => $surferHandle,
          'entry_tid' => (int)$threadId
        )
      );
    }
  }

  /**
  * Load a list of all forums within a category. When
  * no parameter for the category Id is specified this
  * function returns a list of all available forums.
  * Additionally to forum information, the most recent
  * entry of each forum is loaded as well. Additionally
  * the amount of threads and the sum of all entries are
  * calculated for each of those forums considered.
  *
  * This method resets the global class variable $boards.
  *
  * @param $categId (int)
  */
  function loadForumsInCategory($categId = NULL) {
    unset($this->boards);
    if ($categId !== NULL && (int)$categId > 0) {
      $categoryFilter = 'AND '.sprintf("f.forumcat_id = '%d'", $categId);
    } else {
      $categoryFilter = '';
    }
    $sql = "SELECT f.forum_id, f.forumcat_id, f.forum_title, f.forum_desc,
                   c.forumcat_title
              FROM %s f, %s c
             WHERE f.forumcat_id = c.forumcat_id
                   $categoryFilter";
    $params = array($this->tableBoards, $this->tableCategs);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $this->boards = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['thread_count'] = 0;
        $row['entry_count'] = 0;
        $this->boards[$row['forum_id']] = $row;
      }
      $this->forumCount = $res->count();
    }
    if (count($this->boards) > 0) {
      $sql = '';
      foreach (array_keys($this->boards) as $boardId) {
        if (!empty($sql)) {
          $sql .= 'UNION ';
        }
        $sql .= sprintf(
          "(SELECT forum_id, entry_subject, entry_username, entry_modified,
                   entry_id, entry_created, entry_userhandle, entry_notify,
                   entry_thread_modified, entry_path
              FROM %s
             WHERE forum_id = '%d'
             ORDER BY entry_modified DESC LIMIT 1)\n",
          $this->escapeStr($this->tableEntries),
          (int)$boardId
        );
      }
      if ($res = $this->databaseQuery($sql)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $forumId = $row['forum_id'];
          $this->boards[$forumId]['entry_id'] = $row['entry_id'];
          $this->boards[$forumId]['entry_subject'] = $row['entry_subject'];
          $this->boards[$forumId]['entry_username'] = $row['entry_username'];
          $this->boards[$forumId]['entry_created'] = $row['entry_created'];
          $this->boards[$forumId]['entry_modified'] = $row['entry_modified'];
          $this->boards[$forumId]['entry_userhandle'] = $row['entry_userhandle'];
          $this->boards[$forumId]['entry_thread_modified'] = $row['entry_thread_modified'];
          $this->boards[$forumId]['entry_path'] = $row['entry_path'];
        }
      }

      $sql = "SELECT f.forum_id, COUNT(*) AS cnt
                FROM %s f, %s e
               WHERE f.forum_id = e.forum_id
                 AND e.entry_pid = 0
                     $categoryFilter
            GROUP BY f.forum_id";
      $params = array($this->tableBoards, $this->tableEntries);

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->boards[$row['forum_id']]['thread_count'] = $row['cnt'];
        }
      }

      $sql = "SELECT f.forum_id, COUNT(*) AS cnt
                FROM %s f, %s e
               WHERE f.forum_id = e.forum_id
                     $categoryFilter
            GROUP BY f.forum_id";
      $params = array($this->tableBoards, $this->tableEntries);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->boards[$row['forum_id']]['entry_count'] = $row['cnt'];
        }
      }
    }
    return FALSE;
  }

  /**
  * Load forums
  *
  * @access public
  * @return boolean
  */
  function loadBoards($categoryId = 0, $limit = NULL, $offset = NULL) {
    $this->boards = array();
    $this->boardCount = 0;
    if ($categoryId > 0) {
      $sql = "SELECT forum_id, forumcat_id, forum_title, forum_desc
                FROM %s
               WHERE forumcat_id = %d
               ORDER BY forum_title ASC, forum_id ASC";
      $params = array($this->tableBoards, (int)$categoryId);
    } else {
      $sql = "SELECT forum_id, forumcat_id, forum_title, forum_desc
                FROM %s
               ORDER BY forum_title ASC, forum_id ASC";
      $params = $this->tableBoards;
    }
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->boards[(int)$row['forum_id']] = $row;
        if (isset($this->categs) && is_array($this->categs)) {
          $this->categs[(int)$row['forumcat_id']]['boards'][] = (int)$row['forum_id'];
        }
      }
      $this->boardCount = $res->absCount();
      $res->free();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load forums of category
  *
  * @param integer $forumId forum id
  * @access public
  * @return boolean
  */
  function loadBoard($forumId) {
    unset($this->board);
    $sql = "SELECT forum_id, forumcat_id, forum_title, forum_desc
              FROM %s
             WHERE forum_id = %d
             ORDER BY forum_title ASC, forum_id ASC";
    $params = array($this->tableBoards, (int)$forumId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->board = $row;
        $res->free();
        return TRUE;
      }
      $res->free();
    }
    return FALSE;
  }

  /**
  * Does forum exist?
  *
  * @param integer $id forum id
  * @access public
  * @return boolean
  */
  function forumExists($id) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE forum_id = '%d'";
    $params = array($this->tableBoards, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() > 0);
    }
    return FALSE;
  }

  /**
  * Count the number of entries in a given forum
  *
  * @param integer $forumId
  * @param boolean $publicOnly leave out blocked entries? optional, default TRUE
  * @return integer number of entries
  */
  function countForumEntries($forumId, $publicOnly = TRUE) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE forum_id = %d ";
    if ($publicOnly) {
      $sql .= " AND entry_blocked = 0";
    }
    $sqlParams = array($this->tableEntries, $forumId);
    $count = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $count = $num;
      }
    }
    return $count;
  }

  /**
  * Get forum by page id
  *
  * @param integer $categId category id
  * @param integer $pageId page id
  * @param integer $prefix Identified the source of the comment
  * @access public
  * @return boolean
  */
  function getForumByPageId($categId, $pageId, $prefix = '') {
    if (!empty($prefix)) {
      $prefixCondition = ' AND '. $this->databaseGetSQLCondition('page_prefix', $prefix);
    } else {
      $prefixCondition = '';
    }

    $sql = "SELECT forum_id
              FROM %s
             WHERE forumcat_id = '%d'
               AND page_id = '%d'
               $prefixCondition
               ";
    $params = array($this->tableBoards, $categId, $pageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return (int)$row[0];
      }
    }
    return FALSE;
  }

  /**
  * Add forum. Creates a new forum in the forum database.
  * The parent refers to a forum category. A page id can
  * be provided when a comments box is to be created.
  *
  * @see base_db::databaseInsertRecord
  * @see actbox_forum.php
  * @param integer $parent parent id
  * @param string $title optional, default value ''
  * @param string $description optional, default value ''
  * @param integer $pageId optional, default value 0
  * @param string $pageId optional, default value ''
  * @access public
  * @return mixed integer id of new record or boolean FALSE (error)
  */
  function addForum($parent, $title = '', $description = '', $pageId = 0, $prefix = '') {
    if ($this->categExists($parent)) {
      $data = array(
        'forumcat_id' => $parent,
        'forum_title' => ((trim($title) != '') ? $title : $this->_gt('New forum')),
        'forum_desc' => $description,
        'page_id' => $pageId,
        'page_prefix' => $prefix
      );
      return $this->databaseInsertRecord($this->tableBoards, 'forum_id', $data);
    }
    return FALSE;
  }

  /**
  * Get thread by page id
  *
  * @param integer $forumId
  * @param integer $pageId
  * @access public
  * @return boolean
  */
  function getThreadByPageId($forumId, $pageId) {
    $sql = "SELECT entry_id
              FROM %s
             WHERE forum_id = '%d'
               AND page_id = '%d'";
    $params = array($this->tableEntries, $forumId, $pageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return (int)$row[0];
      }
    }
    return FALSE;
  }



  /**
  * Entry
  **********************/

  /**
  * Returns an associative array of surfer handles having
  * email adresses of all those community surfers who are
  * valid, registered and do have subscribed the given thread.
  *
  * @param $threadId
  * @return array( userhandle => useremail, ...)
  */
  function getSubscribedSurfers($threadId) {
    $sql = "SELECT s.surfer_handle,
                   s.surfer_email,
                   s.surfer_surname,
                   s.surfer_givenname
              FROM %s s, %s t
             WHERE s.surfer_handle = t.surfer_handle
               AND s.surfer_valid = 1
               AND t.entry_tid = '%d'";
    $params = array($this->tableSurfers, $this->tableSubscriptions, $threadId);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['surfer_handle']] = $row;
      }
      return $result;
    }
    return FALSE;
  }

  /**
  * Get a full path to the selected thread.
  *
  * Set additional parameters to get a thread path with sub entry paths.
  *
  * @param integer $threadId
  * @param boolean $withSubEntryPaths
  * @param boolean $removeIntermediatePaths
  * @return string|array a single thread path or array with further sub entry paths
  */
  function getThreadPath($threadId, $withSubEntryPaths = FALSE, $removeIntermediatePaths = TRUE) {
    $sql = "SELECT entry_id, entry_path
              FROM %s
             WHERE entry_id = %d";
    if ($withSubEntryPaths === TRUE) {
      $sql .= " OR entry_path LIKE '%%;%d;%%' ORDER BY entry_path, entry_id ASC";
    }
    $params = array($this->tableEntries, $threadId, $threadId);

    $threadPath = NULL;
    $subEntryPaths = array();
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {

        if ($row['entry_id'] == $threadId) {
          $threadPath = $row['entry_path'].$threadId.';';
          if (empty($row['entry_path'])) {
            $threadPath = ';'.$threadPath;
          }

        } elseif ($withSubEntryPaths === TRUE) {
          $subEntryPaths[$row['entry_id']] = $row['entry_path'].$row['entry_id'].';';

          if ($removeIntermediatePaths === TRUE &&
              preg_match_all('(\d+)', $row['entry_path'], $matches, PREG_PATTERN_ORDER) &&
              !empty($matches[0]) && is_array($matches[0])) {
            // remove intermediate paths to keep full paths only, e.g. to determine last changes
            foreach ($matches[0] as $id) {
              if (!empty($subEntryPaths[$id])) {
                unset($subEntryPaths[$id]);
              }
            }
          }
        }
      }
    }

    if ($withSubEntryPaths) {
      return array($threadPath, $subEntryPaths);
    }
    return $threadPath;
  }

  /**
  * Return the amount of entries having the provided thread
  * as direct or indirect parent. This method will count the
  * entries of threads which are childs of the provided thread
  * as well. You can provide the tread path to avoid an additional
  * sql query.
  *
  * @param integer $threadId thread id
  * @param string $threadPath the thread path
  * @return integer Amount or FALSE.
  */
  function countThreadEntries($threadId, $threadPath = NULL) {
    if (empty($threadPath)) {
      $threadPath = $this->getThreadPath($threadId, FALSE);
    }
    if (!empty($threadPath)) {
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE entry_path LIKE '%s%%'";
      $params = array($this->tableEntries, $threadPath);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        return $res->fetchField();
      }
    }
    return FALSE;
  }

  /**
  * Updates the nodes within the thread tree, being on the
  * provided path. A path allways has to start and end with
  * a colon. The only change made, is setting the modification
  * time of the entries in our way to be set to NOW.
  *
  * @param string $path (;-separated)
  * @param integer $modifiedTime
  * @return TRUE or FALSE.
  */
  function updatePath($path, $modifiedTime = NULL) {
    if (preg_match_all('(\d+)', $path, $matches, PREG_PATTERN_ORDER)) {
      if ($modifiedTime === NULL) {
        $modifiedTime = time();
      }
      return FALSE !== $this->databaseUpdateRecord(
        $this->tableEntries,
        array('entry_thread_modified' => $modifiedTime),
        'entry_id',
        $matches[0]
      );
    }
    return TRUE;
  }

  /**
  * Update thread
  *
  * @see base_db::databaseUpdateRecord
  *
  * @param integer $threadId
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function updateThread($threadId) {
    $threadUpdateTimeMode = base_module_options::readOption(
      $this->_edModuleGuid, 'THREAD_UPDATE_TIME_MODE', 0
    );
    list($threadPath, $subEntryPaths) = $this->getThreadPath($threadId, TRUE);

    $threadModified = NULL;
    if ($threadUpdateTimeMode > 0) {
      $subEntryPaths[$threadId] = $threadPath;

      if (!empty($subEntryPaths)) {
        foreach ($entryPaths as $entryPath) {
          if (preg_match_all('(\d+)', $entryPath, $matches, PREG_PATTERN_ORDER) &&
              !empty($matches[0])) {

            $entryIds = array_reverse($matches[0]);
            $passedEntryIds = array();
            foreach ($entryIds as $entryId) {
              $sql = "SELECT entry_id, entry_modified
                        FROM %s
                       WHERE entry_id = '%d'";
              $params = array($this->tableEntries, $entryId);

              if ($res = $this->databaseQueryFmt($sql, $params)) {
                while ($row = $res->fetchRow()) {
                  if (!array_key_exists($row[0], $passedEntryIds)) {
                    if ($row[1] > $pathLastModified) {
                      $threadModified = $row[1];
                    }
                    $passedEntryIds[$row[0]] = TRUE;
                  }
                }
              }
            }
          }
        }
      }
    }

    if (!empty($threadPath)) {
      $this->updatePath($threadPath, !empty($threadModified) ? $threadModified : time());
    }
    $count = $this->countThreadEntries($threadId);
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableEntries,
      array('entry_thread_count' => $this->countThreadEntries($threadId)),
      'entry_id',
      (int)$threadId
    );
    return FALSE;
  }

  /**
  * Search through specified category/forum/thread using full text search.
  * When provided the search can be limited by offset and perPage.
  * When successfull the current search result is stored within the session.
  *
  * @param integer $categoryId
  * @param integer $forumId
  * @param array $searchFor
  * @param integer $offset
  * @param integer $perPage
  * @access private
  * @return boolean
  */
  function searchThreads($categoryId, $forumId, $searchFor, $offset, $perPage) {
    $searchResult = array();
    $limits = (int)$offset.','.(int)$perPage;

    $filter = '';
    if (!empty($categoryId)) {
      $filter .= sprintf(" AND f.forumcat_id = '%d'", (int)$categoryId);
    }
    if (!empty($forumId)) {
      $filter .= sprintf(" AND e.forum_id = '%d'", (int)$forumId);
    }
    $params = array(
      $this->tableEntries,
      $this->tableBoards
    );
    /*
    * When search results are available within cache, the thread ids
    * are loaded from the session directly. This is only done if the
    * parameters for searching the threads did not change.
    */
    if ($this->cacheSearchResults) {
      $searchResult = $this->getSessionValue($this->sessionParamName.'_searchresult');
      if (isset($searchResult['searchfor']) &&
          $searchResult['searchfor'] == $searchFor) {
        if ((int)$forumId == (int)$searchResult['forum_id'] &&
            (int)$categoryId == (int)$searchResult['category_id'] &&
            $searchResult['searchfor'] == $searchFor &&
            isset($searchResult['thread_ids'][$limits])) {
          $sql = "SELECT MAX(e.entry_modified)
                    FROM %s e, %s f
                   WHERE e.forum_id = f.forum_id
                         $filter";
          if ($res = $this->databaseQueryFmt($sql, $params)) {
            if (isset($searchResult['last_modified']) &&
                $res->fetchField() < $searchResult['last_modified']) {
              $this->searchResultsCount = $searchResult['count'];
              return $searchResult['thread_ids'][$limits];
            }
          }
        }
      }
    }

    /*
    * There are no valid search result stored in the cache.
    * A new search result cache must be built by executing a query
    * finding entry ids (thread ids) matching the conditions given
    * by the papaya fulltext parser.
    */
    include_once(PAPAYA_INCLUDE_PATH.'system/base_searchstringparser.php');
    $threadIds = array();
    $parser = new searchstringparser;
    $filter = str_replace(
      '%',
      '%%',
      $parser->getSQL(
        $searchFor,
        array('e.entry_subject', 'e.entry_strip', 'e.entry_userhandle', 'e.entry_username'),
        $this->fullTextSearch
      )
    );
    if (!empty($filter)) {
      $sql = "SELECT e.entry_id
                FROM %s e, %s f
               WHERE e.forum_id = f.forum_id
                 AND $filter";

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $threadIds[] = $row['entry_id'];
        }
        /*
         * Store our current search settings into session to check
         * if those parameters did change in the future.
         */
        $searchResult['category_id'] = (int)$categoryId;
        $searchResult['forum_id'] = (int)$forumId;
        $searchResult['searchfor'] = $searchFor;
        $searchResult['last_modified'] = time();
        if (empty($searchResult['count'])) {
          $searchResult['count'] = $res->absCount();
        }
        $searchResult['thread_ids'][$limits] = $threadIds;
        if ($this->cacheSearchResults) {
          $this->setSessionValue($this->sessionParamName.'_searchresult', $searchResult);
        }
      }
      $this->searchResultsCount = $searchResult['count'];
      return $searchResult['thread_ids'][$limits];
    }
    return FALSE;
  }

  /**
  * Search thread ids and load data for it.
  *
  * @param integer $categoryId
  * @param integer $forumId
  * @param integer $threadId
  * @param array $searchFor
  * @param integer $offset
  * @param integer $perPage
  * @access public
  * @return boolean
  */
  function search($categoryId, $forumId, $searchFor, $offset, $perPage) {
    $this->searchResultsCount = 0;
    $this->searchResults = array();
    $threadIds = $this->searchThreads($categoryId, $forumId, $searchFor, $offset, $perPage);
    if (is_array($threadIds) && count($threadIds) > 0) {
      $condition = str_replace(
        '%',
        '%%',
        $this->databaseGetSqlCondition('entry_id', $threadIds)
      );
      $sql = "SELECT e.entry_id, e.entry_pid, e.entry_created, e.entry_modified,
                     e.entry_thread_modified, e.entry_thread_count,
                     e.entry_subject, e.entry_text,
                     e.entry_username, e.entry_userhandle, e.entry_userregistered,
                     f.forum_title, c.forumcat_title, e.forum_id, entry_notify
                FROM %s e, %s f, %s c
               WHERE f.forumcat_id = c.forumcat_id
                 AND e.forum_id = f.forum_id
                 AND $condition";
      $params = array($this->tableEntries, $this->tableBoards, $this->tableCategs);
      if ($res = $this->databaseQueryFmt($sql, $params, $perPage, $offset)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->searchResults[] = $row;
        }
      }
    }
  }

  /**
  * Does entry exist ?
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function entryExists($entryId) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE entry_id = '%d'";
    $params = array($this->tableEntries, (int)$entryId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() > 0);
    }
    return FALSE;
  }

  /**
  * Load entry
  *
  * @param integer $id
  * @param boolean $reload even if it has the same id as in $entryId
  * @access public
  * @return boolean
  */
  function loadEntry($entryId, $reload = TRUE) {
    if (!$reload) {
      if (!empty($this->entry) && (int)$this->entry['entry_id'] == (int)$entryId) {
        return TRUE;
      } elseif ($entryId === NULL) {
        return FALSE;
      }
    }
    unset($this->entry);
    $sql = "SELECT entry_id, entry_pid, forum_id,
                   entry_created, entry_modified, entry_thread_modified,
                   entry_thread_count, entry_subject, entry_text, entry_username,
                   entry_userhandle, entry_useremail, entry_userregistered, entry_userguid,
                   entry_ip, entry_notify, entry_path, entry_blocked, 0 as entry_depth
              FROM %s
             WHERE entry_id = '%d'";
    $params = array($this->tableEntries, $entryId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->entry = $row;
        if (1 == $row['entry_userregistered']
            && !empty($row['entry_userhandle'])
            && !isset($this->users[$row['entry_userhandle']])) {
          $this->users[$row['entry_userhandle']] = array();
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Returns the record of the most recently modified or created
  * entry of a thread that id is provided.
  *
  * @param $threadId
  * @return array Entry.
  * @deprecated
  */
  function loadMostRecentEntryInThread($threadId) {
    $sql = "SELECT entry_id, entry_pid, forum_id,
                   entry_created, entry_modified, entry_thread_modified,
                   entry_thread_count, entry_subject, entry_text, entry_username,
                   entry_useremail, entry_userregistered, entry_userguid,
                   entry_ip, entry_userhandle, entry_blocked
              FROM %s
             WHERE entry_thread_count = 0
               AND entry_pid = %d
          ORDER BY entry_modified DESC";
    $params = array($this->tableEntries, $threadId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!isset($this->users[$row['entry_userhandle']])) {
          $this->users[$row['entry_userhandle']] = array();
        }
        if (1 == $row['entry_userregistered']
            && !empty($row['entry_userhandle'])
            && !isset($this->users[$row['entry_userhandle']])) {
          $this->users[$row['entry_userhandle']] = array();
        }
        return $row;
      }
    }
    return FALSE;
  }

  /**
  * Get child ids
  *
  * @param array $ids
  * @access public
  * @return array
  */
  function getChildIds($ids) {
    $result = array();
    if (isset($ids) && is_array($ids)) {
      foreach ($ids as $id) {
        if (isset($this->entryTree[$id]) && is_array($this->entryTree[$id])) {
          $result = array_merge($result, $this->entryTree[$id]);
          $result = array_merge($result, $this->getChildIds($this->entryTree[$id]));
        }
      }
    }
    return $result;
  }

  /**
  * Load all entries and subentries of a thread.
  * This method can be used to load threads in either BBS or
  * threaded mode. For each entry a variable entry_depth
  * is calculated from the path indicating the level where
  * the entry is found within the entry tree in this thread.
  *
  * All entry data is stored in an indexed array called
  * $this->entries, the tree structure is stored in the
  * array $this->entryTree and all community users, as far
  * as they have been registered are stored in $this->users.
  *
  * $this->users has to be substituted with user data after
  * calling this method.
  *
  * @param integer $threadId
  * @access public
  * @return boolean
  */
  function loadThread($threadId, $orderBy = NULL) {
    unset($this->entries);
    unset($this->entryTree);

    $this->entries = array();
    $this->entryTree = array();

    $entryPath = '';
    $levelOffset = 0;
    if (isset($this->entry) &&
        isset($this->entry['entry_id']) &&
        $this->entry['entry_id'] == $threadId) {
      $entryPath = $this->entry['entry_path'].$threadId.';';
      $parents = $this->decodePath($this->entry['entry_path']);
      $levelOffset = count($parents);
      $row['entry_depth'] = 0;
      $this->entries[$this->entry['entry_id']] = $this->entry;
    } else {
      $sql = "SELECT forum_id, entry_id, entry_pid, entry_created, entry_modified,
                     entry_subject, entry_username, entry_userhandle, entry_notify,
                     entry_userregistered, entry_path, entry_thread_modified,
                     entry_text, entry_thread_count, entry_blocked
                FROM %s
               WHERE entry_id = '%d'";
      $params = array($this->tableEntries, $threadId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $parents = $this->decodePath($row['entry_path']);
          $levelOffset = count($parents);
          $row['entry_depth'] = 0;
          $this->entries[$row['entry_id']] = $row;
          $entryPath = $row['entry_path'].$threadId.';';
          if (1 == $row['entry_userregistered'] &&
              !empty($row['entry_userhandle']) && !isset($this->users[$row['entry_userhandle']])) {
            $this->users[$row['entry_userhandle']] = array();
          }
        }
      }
    }

    if (!empty($entryPath)) {
      if (substr($entryPath, 0, 1) != ';') {
        $entryPath = ';'.$entryPath;
      }
      $sql = "SELECT forum_id, entry_id, entry_pid, entry_created, entry_modified,
                     entry_subject, entry_username, entry_userhandle, entry_notify,
                     entry_userregistered, entry_path, entry_thread_modified,
                     entry_text, entry_thread_count, entry_blocked
                FROM %s
               WHERE entry_path LIKE '%s%%' ";
      switch ($orderBy) {
      case 'created' :
        $sql .= ' ORDER BY entry_created ASC ';
        break;
      case 'path' :
        $sql .= ' ORDER BY entry_path ASC ';
        break;
      }
      $params = array($this->tableEntries, $entryPath);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $parents = $this->decodePath($row['entry_path']);
          $row['entry_depth'] = count($parents) - $levelOffset;
          $this->entries[$row['entry_id']] = $row;
          $this->entryTree[$row['entry_pid']][] = $row['entry_id'];
          if (1 == $row['entry_userregistered'] &&
              !empty($row['entry_userhandle']) && !isset($this->users[$row['entry_userhandle']])) {
            $this->users[$row['entry_userhandle']] = array();
          }
        }
        $this->entryCount = $res->count();
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Loads all threads on top of a forum. These threads
  * do not have a parent. The results are stored within
  * the array $this->topics.
  *
  * @param integer $forumId
  * @param integer $offset, default NULL
  * @param integer $max, default NULL
  * @return TRUE or FALSE
  */
  function loadTopics($forumId, $offset = NULL, $max = NULL, $sort = 'DESC') {
    $this->topics = array();
    $this->topicCount = 0;
    $sql = "SELECT entry_id, entry_created, entry_modified, entry_notify,
                   entry_subject, entry_username, entry_userhandle,
                   entry_userregistered, entry_thread_modified,
                   entry_thread_count, entry_text, entry_blocked, forum_id
              FROM %s
             WHERE forum_id = %d AND entry_pid = 0
          ORDER BY entry_thread_modified %s";
    $params = array($this->tableEntries, $forumId, $sort);
    if ($res = $this->databaseQueryFmt($sql, $params, $max, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['entry_depth'] = 0;
        $this->topics[] = $row;
        if (1 == $row['entry_userregistered'] && !empty($row['entry_userhandle']) &&
            !isset($this->users[$row['entry_userhandle']])) {
          $this->users[$row['entry_userhandle']] = array();
        }
      }
      $this->topicCount = $res->absCount();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load last entries
  *
  * @param integer $categId optional
  * @param integer $forumId optional
  * @param integer $threadId optional
  * @param integer $maxCount optional, default 30
  * @param string $order optional, 'ASC' or 'DESC', default 'DESC'
  * @access public
  */
  function loadLastEntries($categId = NULL, $forumId = NULL, $threadId = NULL, $maxCount = 30,
      $order = 'DESC') {
    if ($order != 'ASC') {
      $order = 'DESC';
    }
    unset($this->entries);

    $filter = '';
    if (!empty($forumId)) {
      $filter = sprintf("AND e.forum_id = '%d'", $forumId);
    }
    if (!empty($threadId)) {
      $filter = sprintf("AND e.entry_pid = '%d'", $threadId);
    }
    if (!empty($categId)) {
      $filter = sprintf("AND f.forumcat_id = %d", $categId);
    }
    $sql = "SELECT e.entry_id, e.entry_pid, e.forum_id, e.entry_notify,
                   e.entry_created, e.entry_modified, e.entry_subject, entry_userguid,
                   e.entry_username, e.entry_userhandle, e.entry_userregistered,
                   e.entry_thread_modified, e.entry_blocked,
                   f.forum_id, f.forum_title,
                   c.forumcat_id, c.forumcat_title
              FROM %s e, %s f, %s c
             WHERE f.forum_id = e.forum_id
               AND f.forumcat_id = c.forumcat_id
                   $filter
          ORDER BY entry_modified $order, entry_id $order";
    $params = array($this->tableEntries, $this->tableBoards, $this->tableCategs);

    if ($res = $this->databaseQueryFmt($sql, $params, $maxCount)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->entries[(int)$row['entry_id']] = $row;
        if (1 == $row['entry_userregistered']
            && !empty($row['entry_userhandle'])
            && !isset($this->users[$row['entry_userhandle']])) {
          $this->users[$row['entry_userhandle']] = array();
        }
      }
      $this->lastEntryCount = $res->absCount();
    }
  }

  /**
  * Load last entries in category
  *
  * @param integer $categId
  * @param integer $maxCount
  * @param mixed integer|NULL $offset (optional, default NULL)
  * @param integer $mode (optional, default 0)
  * @access public
  * @return boolean
  */
  function loadLastEntriesInCateg($categId, $maxCount, $offset = NULL, $mode = 0) {
    if ($this->categ = $this->loadCateg($categId)) {
      $prevPath = $this->categ['forumcat_path'];
    } elseif ($categId == 0) {
      $prevPath = '';
    } else {
      $prevPath = NULL;
    }
    if (isset($prevPath)) {
      $sql = "SELECT f.forum_id
                FROM %s AS c, %s AS f
               WHERE c.forumcat_path LIKE '%s%%'
                 AND f.forumcat_id = c.forumcat_id";
      $params = array(
        $this->tableCategs,
        $this->tableBoards,
        $prevPath
      );
      $forumIds = array();
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow()) {
          $forumIds[] = (int)$row[0];
        }
        if (count($forumIds) > 0) {
          $filter = $this->databaseGetSQLCondition('', $forumIds);
          $sql = "SELECT entry_id, entry_pid, forum_id, entry_notify,
                         entry_created, entry_modified, entry_thread_modified,
                         entry_subject, entry_username, entry_userregistered,
                         entry_userhandle, entry_blocked, entry_userguid
                    FROM %s
                   WHERE $filter
                   ORDER BY entry_created DESC, entry_id DESC";
          $params = array($this->tableEntries);
          if ($res = $this->databaseQueryFmt($sql, $params, $maxCount, $offset)) {
            while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
              $this->entries[(int)$row['entry_id']] = $row;
              if (1 == $row['entry_userregistered']
                  && !empty($row['entry_userhandle'])
                  && !isset($this->users[$row['entry_userhandle']])) {
                $this->users[$row['entry_userhandle']] = array();
              }
            }
            $this->entryCount = $res->absCount();
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Load last entries attached to a forum
  *
  * @param integer $forumId Identifier of the forum
  * @param integer $limit Amount of entries to return
  * @param integer $offset Number of entries to skip from start of the result set
  * @param boolean $absCount
  * @param string $order optional 'ASC' or 'DESC', default 'DESC'
  * @return array List of entries attached to the given forum
  */
  function loadLastEntriesByForum($forumId, $limit = 0, $offset = 0, $absCount = FALSE,
      $order = 'DESC') {
    if ($order != 'ASC') {
      $order = 'DESC';
    }
    $entries = array();
    $records = array();
    $sql = "SELECT entry_id, entry_pid, entry_notify, entry_text,
                   entry_created, entry_modified, entry_thread_modified,
                   entry_subject, entry_username, entry_userregistered,
                   entry_userhandle, entry_blocked, entry_userguid
              FROM %s
             WHERE forum_id = %d
             ORDER BY entry_created $order, entry_id $order";
    $params = array($this->tableEntries, $forumId);

    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->entries[(int)$row['entry_id']] = $row;
        $records[(int)$row['entry_id']] = $row;
        if (1 == $row['entry_userregistered']
            && !empty($row['entry_userhandle'])
            && !isset($this->users[$row['entry_userhandle']])) {
          $this->users[$row['entry_userhandle']] = array();
        }
      }
      $this->entryCount = $res->absCount();
    }

    // gather data
    $entries['entries'] = $records;
    if ($absCount) {
      $entries['abs_count'] = $this->entryCount;
    }

    return $entries;
  }

  /**
  * Load entries by id
  *
  * @param integer $ids
  * @access public
  */
  function loadEntriesById($ids) {
    unset($this->entries);
    unset($this->entryTree);
    $filter = $this->databaseGetSQLCondition('entry_id', $ids);
    $keys = array_flip($ids);

    $sql = "SELECT entry_id, entry_pid, forum_id,
                   entry_created, entry_modified, entry_notify,
                   entry_subject, entry_username, entry_userhandle,
                   entry_userregistered, entry_text, entry_blocked, entry_userguid
              FROM %s
             WHERE $filter";
    $params = array($this->tableEntries);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['index'] = $keys[(int)$row['entry_id']];
        $this->entries[(int)$row['entry_id']] = $row;
        if (1 == $row['entry_userregistered']
            && !empty($row['entry_userhandle'])
            && !isset($this->users[$row['entry_userhandle']])) {
          $this->users[$row['entry_userhandle']] = array();
        }
      }
      $this->entriesCount = $res->absCount();

      if (isset($this->entries) && is_array($this->entries)) {
        uasort($this->entries, array($this, 'cmpEntriesByIndex'));
      }
    }
  }

  /**
  * Get entries by the id of its poster
  *
  * @param guid $id
  * @access public
  */
  function getEntriesBySurferId($id) {
    $filter = $this->databaseGetSQLCondition('entry_userguid', $id);
    $entries = array();
    $sql = "SELECT e.entry_id, e.entry_pid, e.forum_id, e.entry_notify,
                   e.entry_created, e.entry_modified, e.entry_subject, entry_userguid,
                   e.entry_username, e.entry_userhandle, e.entry_userregistered,
                   e.entry_thread_modified, e.entry_blocked,
                   f.forum_id, f.forum_title,
                   c.forumcat_id, c.forumcat_title
              FROM %s e, %s f, %s c
             WHERE f.forum_id = e.forum_id
               AND $filter";
    $params = array($this->tableEntries, $this->tableBoards, $this->tableCategs);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $entries[(int)$row['entry_id']] = $row;
      }
    }
    return $entries;
  }

  /**
  * Count the number of entries,the users have written.
  * Only those users are taken into consideration whose
  * handles have been provided as keys in the array reference
  * users. This should be the same array, that has been prepared
  * by a prior call to loadThread. The amount of entries each
  * user has written is stored as an additional field within
  * the provided users array. The field is called entry_count.
  *
  * @param array $users Reference to users array.
  * @return array
  */
  function countUserEntries(&$users) {
    $filter = str_replace(
      '%',
      '%%',
      $this->databaseGetSqlCondition('entry_userhandle', array_keys($users))
    );
    $sql = "SELECT e.entry_userhandle, COUNT(*) AS entry_count
              FROM %s e
             WHERE $filter
          GROUP BY e.entry_userhandle";
    $params = array($this->tableEntries);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->users[$row['entry_userhandle']]['entry_count'] = $row['entry_count'];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Save an allready existing entry with new data.
  * When a parentId is provided, the thread will be updated to
  * generate a thread-modification timestamp. The thread count
  * of the thread shall be unaffected. The provided array entryData
  * consists of key-value pairs, with optional fields to store.
  *
  * The field entry_text, when provided will be checked for
  * html tags and a tag-less copy of it will be set into
  * entry_strip to enable full text search on formatted text.
  *
  * The fields entry_modified and entry_ip will be set allways.
  *
  * @param integer $entryId
  * @param array $entryData array
  * @return boolean TRUE or FALSE
  */
  function saveEntry($entryId, $entryData) {
    if (isset($entryData['entry_text'])) {
      $entryData['entry_text'] = $this->verifyHTMLInput($entryData['entry_text']);
      $entryData['entry_strip'] = strip_tags($entryData['entry_text']);
    }

    $entryData['entry_modified'] = time();
    $entryData['entry_ip'] = (string)$_SERVER['REMOTE_ADDR'];

    if ($this->databaseUpdateRecord($this->tableEntries, $entryData, 'entry_id', $entryId)) {
      $this->updateThread($entryId);
      if (isset($entryData['entry_path'])) {
        $this->updatePath($entryData['entry_path']);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Add a new entry.
  *
  * When successfull the newly created entryId will be returned,
  * otherwise FALSE will be returned.
  *
  * When a parentId is provided, the thread will be
  * updated to generate a thread-modififcation timestamp. The thread
  * count of the thread will be increased by one. The provided
  * array entryData consists of key-value paris, with optional fields to store.
  *
  * The field entry_text, when provided will be checked for
  * html tags and a tag-less copy of it will be set into
  * entry_strip to enable full text search on formatted text.
  *
  * When this method is called in the output context, a class variable
  * set of user data is interpreted to store the entry editor as well.
  * When the surfer is not logged in, it is important that the fields
  * entry_username, entry_useremail, entry_userhandle are set within
  * the provided data array. Otherwise the entry will remain without
  * a user reference.
  *
  * The fields entry_created, entry_modified, entry_ip, entry_path and
  * entry_userregistered will always be set by this function.
  *
  * Before the entry is added to the database, this method will check
  * if there allready is a previous post with the same contents, which
  * came from the same user. This indicates a double post wich is
  * prevented.
  *
  * @param array $entryData
  * @param boolean $richtextEnabled optional, default FALSE
  * @access public
  * @return int|boolean $entryId or FALSE
  */
  function addEntry($entryData, $richtextEnabled = FALSE) {
    // make sure whitespace do not count as content
    $entryData['entry_text'] = trim($entryData['entry_text']);
    $entryData['entry_subject'] = trim($entryData['entry_subject']);

    if (empty($entryData['entry_text']) || empty($entryData['entry_subject'])) {
      //no content - no insert
      return FALSE;
    }
    if (isset($entryData['entry_text'])) {
      // Reject potential attack strings?
      include_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');
      $rejectAttackStrings = base_module_options::readOption(
        $this->_edModuleGuid, 'REJECT_ATTACK_STRINGS', ''
      );
      if (trim($rejectAttackStrings) != '') {
        $rejectAttackChars = preg_split('(\s+)', trim($rejectAttackStrings));
        foreach ($rejectAttackChars as $chars) {
          if (strpos($entryData['entry_text'], $chars) !== FALSE) {
            $this->rejectedAttackStrings = TRUE;
            return FALSE;
          }
          if (strpos($entryData['entry_subject'], $chars) !== FALSE) {
            $this->rejectedAttackStrings = TRUE;
            return FALSE;
          }
        }
      }
      $entryData['entry_strip'] = PapayaUtilStringHtml::escapeStripped($entryData['entry_text']);
      $entryMaxLength = base_module_options::readOption(
        $this->_edModuleGuid, 'ENTRY_MAX_LENGTH', 0
      );
      if ($entryMaxLength > 0) {
        if (strlen($entryData['entry_strip']) > $entryMaxLength) {
          $this->entryTooLong = TRUE;
          return FALSE;
        }
      }
      $entryData['entry_text'] = ((bool)$richtextEnabled == FALSE)
        ? $entryData['entry_strip'] : $this->verifyHTMLInput($entryData['entry_text']);
    }
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE entry_subject = '%s'
               AND entry_strip = '%s'";
    $params = array(
      $this->tableEntries,
      $entryData['entry_subject'],
      $entryData['entry_strip']
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      //check for duplicates
      if ($res->fetchField() == 0) {
        $res->free();

        if (isset($this->entry)) {
          if (empty($this->entry['entry_path'])) {
            $prefix = ';';
          } else {
            $prefix = $this->entry['entry_path'];
          }
          $entryData['entry_path'] = $prefix.$this->entry['entry_id'].';';
        } else {
          $entryData['entry_path'] = '';
        }
        $now = time();
        $entryData['entry_modified'] = $now;
        $entryData['entry_thread_modified'] = $now;
        $entryData['entry_created'] = $now;
        $entryData['entry_ip'] = empty($_SERVER['REMOTE_ADDR'])
          ? '' : (string)$_SERVER['REMOTE_ADDR'];
        if (!empty($this->surferValid)) {
          $entryData['entry_userhandle'] = $this->surferHandle;
          $entryData['entry_userregistered'] = 1;
        } else {
          $entryData['entry_userregistered'] = 0;
        }
        $entryData['entry_username'] = $this->surferName;
        $entryData['entry_useremail'] = $this->surferEmail;
        $entryData['entry_userguid'] =
          (!empty($this->surfer['surfer_id'])) ? $this->surfer['surfer_id'] : '';

        if ($entryId = $this->databaseInsertRecord($this->tableEntries, 'entry_id', $entryData)) {
          $this->entryTree[$entryData['entry_pid']][] = $entryId;
          $this->updateThread($entryData['entry_pid']);
          $this->updatePath($entryData['entry_path']);
          return $entryId;
        }
      }
    }
    return FALSE;
  }

  /**
  * Mark an entry as blocked
  *
  * @param integer $entryId
  * @return boolean TRUE on success, else FALSE
  */
  function blockEntry($entryId) {
    if (!$this->entryExists($entryId)) {
      return FALSE;
    }
    return $this->databaseUpdateRecord(
      $this->tableEntries,
      array('entry_blocked' => time()),
      'entry_id',
      $entryId
    );
  }

  /**
  * Remove blocked mark of an entry
  *
  * @param integer $entryId
  * @return boolean TRUE on success, else FALSE
  */
  function unBlockEntry($entryId) {
    if (!$this->entryExists($entryId)) {
      return FALSE;
    }
    return $this->databaseUpdateRecord(
      $this->tableEntries,
      array('entry_blocked' => 0),
      'entry_id',
      $entryId
    );
  }

  /**
  * Get all user ever written a post
  *
  * @param integer $limit
  * @param integer $offset
  * @return array
  */
  function loadAllEntryUsers($limit = 0, $offset = 0) {
    $surfer = array();
    $sql = "SELECT DISTINCT entry_username, entry_userhandle,
                   entry_userregistered, entry_blocked, entry_userguid
              FROM %s";
    $params = array($this->tableEntries);
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!isset($surfer[$row['entry_userguid']])) {
          $surfer[$row['entry_userguid']] = $row;
        }
      }
      $resCount = $this->databaseQueryFmt($sql, $params);
      $this->_totalSurfersCount = $resCount->count();
    }
    return $surfer;
  }


  /***************************************************************************/
  /** Helper / instances                                                     */
  /***************************************************************************/

  /**
  * Required to enable rhichtexteditor capabilities
  */
  function verifyHTMLInput($text) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_htmlpurifier.php');
    $htmlPurifier = new base_htmlpurifier();
    $htmlPurifier->setUp(
      array(
        'HTML:Doctype' => 'XHTML 1.0 Transitional',
        'HTML:Allowed' =>
          'h1[style],h2[style],h3[style],ol,li,ul,a[title|href|name|target],em,strong,'.
          'p[align|style|class],b,i,br,'.
          'cdb[id|height|width|align|resize|lspace|tspace|rspace|bspace|thumb|link|subtitle|alt],'.
          'span[style|class],div[style|class|align],'.
          'img[width|height|src|style|align|class],'.
          'blockquote,pre',
        'HTML:DefinitionID' => 'Richtext_tinyMCE',
        'HTML:DefinitionRev' => 7,
        'Core:EscapeInvalidTags' => 'false'
      )
    );

    $htmlPurifier->addAttribute('div', 'align', 'Text');
    $htmlPurifier->addAttribute('p', 'align', 'Text');
    $htmlPurifier->addAttribute('span', 'align', 'Text');
    $htmlPurifier->addAttribute('a', 'name', 'Text');
    $attrDefInteger = $htmlPurifier->getAttributeDefinition('Integer');

    $htmlPurifier->addElement(
      'video',
      'Inline',
      'Inline',
      'Custom',
      array(
        'width' => $attrDefInteger,
        'height' => $attrDefInteger,
        'dataurl' => 'URI',
        'flashvars' => 'Text',
      )
    );

    $htmlPurifier->addElement(
      'cdb',
      'Inline',
      'Empty',
      'Custom',
      array(
        'id*' => $htmlPurifier->getAttributeDefinition('GUID'), // required
        'height' => $attrDefInteger,
        'width' => $attrDefInteger,
        'align' => 'Enum#left,right,middle,inline,justify,center',
        'lspace' => $attrDefInteger,
        'tspace' => $attrDefInteger,
        'rspace' => $attrDefInteger,
        'bspace' => $attrDefInteger,
        'resize' => 'Enum#abs,max,mincrop,crop',
        'thumb' => 'Enum#0,1',
        'link' => 'URI',
        'subtitle' => 'Text',
        'alt' => 'Text'
      )
    );
    $str = $htmlPurifier->purifyInput($text);
    return $str;
  }

  /**
  * Required to enable rhichtexteditor capabilities
  */
  function verifySimpleHTMLInput($text) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_htmlpurifier.php');
    $htmlPurifier = new base_htmlpurifier();
    $htmlPurifier->setUp(
      array(
        'HTML:Allowed' =>
          'h1,h2,h3,ol,li,ul,em,strong,p,'.
          'span[style|class]',
          'HTML:DefinitionID' => 'Simplerichtext_tinyMCE',
        'HTML:DefinitionRev' => 1,
        'Core:EscapeInvalidTags' => 'false'
     )
    );
    return $htmlPurifier->purifyInput($text);
  }

  /**
  * Check if an administrator wants to be notified about changes in this
  * thread.
  *
  * @param integer $threadId
  * @return TRUE or FALSE.
  */
  function checkNotify($threadId) {
    $sql = "SELECT entry_notify FROM %s WHERE entry_id = '%d'";
    $params = array($this->tableEntries, $threadId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() > 0);
    }
    return FALSE;
  }

  /**
  * Spam check routine.
  *
  * The spam is either passed as parameter or taken from $this->params['entry_text'].
  *
  * @param string $text String to be rated
  * @return boolean TRUE : Its not spam. FALSE : It is spam.
  */
  function checkSpam($text = '') {

    if (empty($text) && empty($this->params['entry_text'])) {
      return TRUE;
    } elseif (empty($text) && !empty($this->params['entry_text'])) {
      $text = $this->params['entry_text'];
    }

    $module = $this->getOwnerObject();

    include_once(PAPAYA_INCLUDE_PATH.'system/base_spamfilter.php');
    $spamFilter = &base_spamfilter::getInstance();
    $spamProbability = $spamFilter->check(
      $text,
      $module->parentObj->getContentLanguageId()
    );
    $spamFilter->log(
      $text,
      $module->parentObj->getContentLanguageId(),
      'Forum Entry Text'
    );
    if ($spamProbability['spam'] && defined('PAPAYA_SPAM_BLOCK') && PAPAYA_SPAM_BLOCK) {
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
  * This method creates an object of the community module
  * to substitute the allready loaded list of users
  * with their data coming from the community module.
  * Additionally to the default user data, dynamic data fields
  * are loaded containing the field called 'papaya-employee'.
  * This field should be set up within the backend to distinguish
  * those users working for the papaya company from those who don't.
  *
  * Additionally to the user data from the apropriate papaya
  * community surfer tables, the amount of entries posted by each
  * surfer will be determined as well.
  *
  * This method may only be called once right after the forum's
  * entries have been loaded because the loadEntry method prepares
  * the users array to substitute.
  *
  */
  function substituteCommunityUsers() {
    if (isset($this->users) && is_array($this->users) && count($this->users)) {
      $surfersObj = base_pluginloader::getPluginInstance('06648c9c955e1a0e06a7bd381748c4e4', $this);
      $surferIds = $surfersObj->getIdsByValidHandles(array_keys($this->users));
      $surfers = $surfersObj->loadSurfers($surferIds);
      foreach ($surfers as $surferId => $surferData) {
        $this->users[$surferData['surfer_handle']] = $surferData;
      }
      $this->countUserEntries($this->users);
    }
  }

  /**
  * Compare entries by index
  *
  * @param array $a
  * @param array $b
  * @access public
  * @return integer -1;0;1
  */
  function cmpEntriesByIndex($a, $b) {
    if ($a['index'] != $b['index']) {
      return ($a['index'] > $b['index']) ? 1 : -1;
    } else {
      return 0;
    }
  }

  /**
  * Prepares an object reference to a surfer object.
  * If the currently surfing surfer is registered and
  * logged in, $this->surferValid will be set. If the
  * current surfer is not registered and logged in,
  * a given username and email is assumed to be entered
  * using the post form.
  *
  */
  function getCurrentSurfer() {
    if (!isset($this->surferObj) || !is_object($this->surferObj)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
      $this->surferObj = &base_surfer::getInstance();
      if ($this->surferObj->isValid) {
        $this->surferValid = TRUE;
        $this->surfer = $this->surferObj->surfer;
        $this->surferHandle = $this->surfer['surfer_handle'];
        $this->surferEmail = $this->surfer['surfer_email'];
        $this->surferName =
          $this->surfer['surfer_givenname'].' '.
          $this->surfer['surfer_surname'];
      } else {
        $this->surferValid = FALSE;
        $this->surferHandle = NULL;
        $this->surferName = NULL;
        $this->surferEmail = NULL;
        if (isset($this->params['entry_username'])) {
          $this->surferName = $this->params['entry_username'];
        }
        if (isset($this->params['entry_useremail'])) {
          $this->surferEmail = $this->params['entry_useremail'];
        }
      }
    }
  }

  /**
  * Checks if a surfer has subscribed the specified thread in the specified
  * forum. In this case TRUE is returned, FALSE is returned otherwise.
  *
  * @param $surferHandle
  * @param $threadId
  * @return TRUE or FALSE
  */
  function checkSurferSubscribedThread($surferHandle, $threadId) {
    $sql = "SELECT COUNT(*) FROM %s WHERE surfer_handle='%s' AND entry_tid=%d";
    $params = array($this->tableSubscriptions, $surferHandle, $threadId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() == 0) ? FALSE : TRUE;
    }
    return FALSE;
  }

  /**
  * Sets a subscription for a registered user for a specific thread, if it not
  * yet exists. If it allready exists nothing will be done at all.
  *
  * @param $surferHandle
  * @param $threadId
  */
  function setSurferThreadSubscription($surferHandle, $threadId) {
    if (!$this->checkSurferSubscribedThread($surferHandle, $threadId)) {
      $this->databaseInsertRecord(
        $this->tableSubscriptions,
        NULL,
        array(
          'surfer_handle' => $surferHandle,
          'entry_tid' => (int)$threadId
        )
      );
    }
  }

  /**
  * decode a path string to an array (a list of all numbers in the string)
  *
  * @param string $pathString
  * @access public
  * @return array
  */
  function decodePath($pathString) {
    if (preg_match_all('(\d+)', $pathString, $matches, PREG_PATTERN_ORDER)) {
      return $matches[0];
    }
    return array();
  }

  /**
  * Get instance of a module/page class
  *
  * @return object
  */
  function getOwnerObject() {
    if (!(isset($this->_owner) && is_object($this->_owner))) {
      if (isset($this->module) && is_object($this->module)) {
        $this->_owner = $this->module;
      }
    }
    return $this->_owner;
  }

  /**
  * Set owner object to use instead of the original one
  *
  * @param object $owner
  * @return object
  */
  function setOwnerObject($owner) {
    $this->_owner = $owner;
  }

  /**
  * get entries for a specified filter criterias and return
  * the results
  * The search can be modified by an offset and a limit
  *
  * @param array filter for the entry search
  * @param integer optional
  * @param integer optional offset
  * @return array with results
  */
  function getEntriesByFilter($filter, $limit = NULL, $offset = NULL) {
    $result = array();
    $conditions = $this->getQueryConditions($filter);
    $sql = "SELECT entry_id, entry_pid, forum_id,
                   entry_created, entry_modified, entry_thread_modified,
                   entry_thread_count, entry_subject, entry_text, entry_username,
                   entry_userhandle, entry_useremail, entry_userregistered, entry_userguid,
                   entry_ip, entry_notify, entry_path, entry_blocked, 0 as entry_depth
              FROM %s
             WHERE $conditions
             ORDER BY entry_modified DESC";
    $params = array($this->tableEntries);
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      $this->_totalEntriesCount = $res->absCount();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['entry_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * Get the total entries count of a previous database
  * @return integer total entry count
  */
  function getEntriesTotalCount() {
    return $this->_totalEntriesCount;
  }

  /**
  * Returns the sql string as condition.
  * possible filters:
  *   ['search_string' => string]
  *
  * @param array $filter
  * @return string sql condition
  */
  function getQueryConditions($filter) {
    $conditions = '';
    if (isset($filter['search_string'])) {
      $fields = array('entry_subject', 'entry_strip');
      $parser = $this->getSearchStringParserObject();
      $sqlSearchString = $parser->getSQL($filter['search_string'], $fields, 0);
      if (!$sqlSearchString) {
        throw new InvalidArgumentException('Invalid SQL search string!');
      }
      $conditions .= str_replace('%', '%%', $sqlSearchString);
    }
    return $conditions;
  }

  /**
  * Initialize the papaya search string parser
  *
  * @return searchStringParser
  */
  function getSearchStringParserObject() {
    if (!(isset($this->_searchStringParserObject) && is_object($this->_searchStringParserObject))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_searchstringparser.php');
      $this->_searchStringParserObject = new searchStringParser();
    }
    return $this->_searchStringParserObject;
  }

  /**
  * Set the papaya search string parser object to be used instead of the real one.
  *
  * @param object searchStringParser
  */
  function setSearchStringParserObject($searchStringParserObject) {
    $this->_searchStringParserObject = $searchStringParserObject;
  }

  /**
  * Load boards to get the position of a defined board
  *
  * @access public
  * @param integer $categoryId
  * @param integer $boardId
  * @return integer 0 or $position
  */
  function getBoardPosition($categoryId, $boardId) {
    $sql = "SELECT forum_id, forumcat_id, forum_title
              FROM %s
             WHERE forumcat_id = %d
             ORDER BY forum_title ASC, forum_id ASC";
    $params = array($this->tableBoards, (int)$categoryId);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $position = 0;
      $count = 0;
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ((int)$row['forum_id'] == $boardId) {
          $position = $count;
        }
        $count++;
      }
      $res->free();
      return $position;
    }
    return 0;
  }

  /**
  * Loads all threads on top of a forum. to get the position of
  * a defined thread
  *
  * @param integer $forumId
  * @param integer $threadId
  * @return integer
  */
  function getTopicPosition($forumId, $threadId) {
    $sql = "SELECT DISTINCT entry_id
              FROM %s
             WHERE forum_id = %d AND entry_pid = 0
          ORDER BY entry_thread_modified DESC";
    $params = array($this->tableEntries, $forumId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $position = 0;
      $count = 0;
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ((int)$row['entry_id'] == $threadId) {
          $position = $count;
        }
        $count++;
      }
      return $position;
    }
    return 0;
  }

}
?>
