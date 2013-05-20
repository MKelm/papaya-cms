<?php
/**
* Community user management, base class
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
* @todo Output escaping
* @package Papaya-Modules
* @subpackage _Base-Community
* @version $Id: base_surfers.php 38470 2013-04-30 14:56:54Z kersken $
*/

/**
* Basic class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Basic class check conditions
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Constant: Avatar default size (unless otherwise configured)
*/
define('AVATAR_DEFAULT_SIZE', 96);

/**
* Constant: Default limit for online list
*/
define('LIMIT_ONLINE_LIST', 50);

/**
* Define some useful constants for contact status types.
* @ignore
*/
if (!defined('SURFERCONTACT_STATUS_BOTH')) {
  define('SURFERCONTACT_STATUS_BOTH', 0);
}
/**
 * @ignore
 */
if (!defined('SURFERCONTACT_STATUS_PENDING')) {
  define('SURFERCONTACT_STATUS_PENDING', 1);
}
/**
 * @ignore
 */
if (!defined('SURFERCONTACT_STATUS_ACCEPTED')) {
  define('SURFERCONTACT_STATUS_ACCEPTED', 2);
}

/**
* Define some more useful constants for contact types.
* Result types for findContact() as bit values that can be ordered together
* @ignore
*/
if (!defined('SURFERCONTACT_NONE')) {
  define('SURFERCONTACT_NONE', 0);
}
/**
 * @ignore
 */
if (!defined('SURFERCONTACT_DIRECT')) {
  define('SURFERCONTACT_DIRECT', 1);
}
/**
 * @ignore
 */
if (!defined('SURFERCONTACT_PENDING')) {
  define('SURFERCONTACT_PENDING', 2);
}
/**
 * @ignore
 */
if (!defined('SURFERCONTACT_INDIRECT')) {
  define('SURFERCONTACT_INDIRECT', 4);
}

/**
* Community user management base class
*
* Instantiate this if you need to access community features
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class surfer_admin extends base_db {
  /**
  * Module guid
  * @var string $moduleGuid
  */
  var $moduleGuid = '88236ef1454768e23787103f46d711c2';

  /**
  * Language Selector
  * @var object $lngSelect
  */
  var $lngSelect = NULL;

  /**
   * Media db
   * @var object $mediaDB
   */
  var $mediaDB = NULL;

  /**
   * Array "cache" for ids by handle
   * @var array $idByHandleCache
   */
  var $idByHandleCache = array();

  /**
   * Array "cache" for handles by id
   * @var array $handleByIdCache
   */
  var $handleByIdCache = array();

  /**
   * Array "cache" for full surfer names
   * @var array $nameByIdCache
   */
  var $nameByIdCache = array();

  /**
   * Array "cache" for avatars
   * @var array $avatarCache
   */
  var $avatarCache = array();

  /**
   * Array "cache" for avatar ids
   * @var array $avatarCache
   */
  var $avatarIdCache = array();

  /**
  * Papaya database table surfer
  * @var string $tableSurfer
  */
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  /**
  * Papaya database table topics
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;

  /**
  * Papaya database table surferpermlink
  * @var string $tableLink
  */
  var $tableLink = PAPAYA_DB_TBL_SURFERPERMLINK;

  /**
  * Papaya database table surferperm
  * @var string $tablePerm
  */
  var $tablePerm = PAPAYA_DB_TBL_SURFERPERM;

  /**
  * Papaya database table surfergroupoups
  * @var string $tableGroups
  */
  var $tableGroups = PAPAYA_DB_TBL_SURFERGROUPS;

  /**
  * Papaya database table surferchangerequests
  * @var string $tableChangeRequests
  */
  var $tableChangeRequests = PAPAYA_DB_TBL_SURFERCHANGEREQUESTS;

  /**
  * Papaya database table surfer data
  * @var string $tableData
  */
  var $tableData = PAPAYA_DB_TBL_SURFERDATA;

  /**
  * Papaya database table surfer data titles
  * @var string $tableDataTitles
  */
  var $tableDataTitles = PAPAYA_DB_TBL_SURFERDATATITLES;

  /**
  * Papaya database table surfer data classes
  * @var string $tableDataClasses
  */
  var $tableDataClasses = PAPAYA_DB_TBL_SURFERDATACLASSES;

  /**
  * Papaya database table surfer data class titles
  * @var string $tableDataClassTitles
  */
  var $tableDataClassTitles = PAPAYA_DB_TBL_SURFERDATACLASSTITLES;

  /**
  * Papaya database table surfer contact data
  * @var string $tableContactData
  */
  var $tableContactData = PAPAYA_DB_TBL_SURFERCONTACTDATA;

  /**
  * Papaya database table surfer contacts
  * @var string $tableContacts
  */
  var $tableContacts = PAPAYA_DB_TBL_SURFERCONTACTS;

  /**
  * Papaya database table surfer contact public
  * @var string $tableContactPublic
  */
  var $tableContactPublic = PAPAYA_DB_TBL_SURFERCONTACTPUBLIC;

  /**
  * Papaya database table surfer lists
  * @var string $tableSurferLists
  */
  var $tableSurferLists = PAPAYA_DB_TBL_SURFERLISTS;

  /**
  * Papaya database table surfer blacklist
  * @var string $tableBlacklist
  */
  var $tableBlacklist = PAPAYA_DB_TBL_SURFERBLACKLIST;

  /**
  * Papaya database table surfer favorites
  * @var string $tableFavorites
  */
  var $tableFavorites = PAPAYA_DB_TBL_SURFERFAVORITES;

  /**
  * Papaya database table topics
  * @var string $tableTopic
  */
  var $tableTopic = PAPAYA_DB_TBL_TOPICS;

  /**
  * Papaya database table authuser
  * @var string $tableAuthuser
  */
  var $tableAuthuser = PAPAYA_DB_TBL_AUTHUSER;

  /**
  * Papaya database table languages
  * @var string $tableLng
  */
  var $tableLng = PAPAYA_DB_TBL_LNG;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName;

  /**
  * Parameters
  * @var array $params
  */
  var $params;

  /**
  * Base link
  * @var string $baseLink
  */
  var $baseLink;

  /**
  * Object reference for error messages
  * @var object base_errors $msgs
  */
  var $msgs;

  /**
  * User
  * @var array $user
  */
  var $user;

  /**
  * Surfers
  * @var array $surfers
  */
  var $surfers;

  /**
  * Permissionlist
  * @var array $permissionsList
  */
  var $permissionsList;

  /**
  * Linklist
  * @var array $linkList
  */
  var $linkList;

  /**
  * Edit surfer data
  * @var array $editSurfer
  */
  var $editSurfer;

  /**
  * List of surfers categorized by their group membership
  * @var array
  */
  var $groupList = NULL;

  /**
  * Edit permission
  * @var array $editPerm
  */
  var $editPerm;

  /**
  * Display threshold
  * @var integer $displayThreshold
  */
  var $displayThreshold;

  /**
  * List length
  * @var integer $listLength
  */
  var $listLength = 10;

  /**
  * Delete flag
  * @var boolean $delFlag
  */
  var $delFlag = FALSE;
  /**
   * Status list
   * @var array $status
   */
  var $status = array();
  /**
   * Status images list
   * @var array $statusImages
   */
  var $statusImages = array (
      0 => 'status-user-new',
      2 => 'items-user',
      1 => 'status-user-published',
      3 => 'status-user-locked',
      4 => 'status-user-locked',
    );

  /**
   * List of surfers who where active within the lastest time period
   * @var array $lastActiveSurfers
   */
  var $lastActiveSurfers;

  /**
   * Abs count of surfers operations
   * @var integer $surfersAbsCount
   */
  var $surfersAbsCount = 0;

  /**
  * Constuctor with parameter initalisation
  *
  * @param object base_errors $msgs
  * @param string $paramName optional, default value "sadm"
  * @access public
  */
  function __construct(&$msgs, $paramName = "sadm") {
    $this->paramName = $paramName;
    $this->msgs = $msgs;
    $this->status = array(
      '0' => $this->_gt('Created'),
      '2' => $this->_gt('Confirmed'),
      '1' => $this->_gt('Valid'),
      '3' => $this->_gt('Locked'),
      '4' => $this->_gt('Blocked')
    );
  }

  /**
  * PHP 4 Constuctor
  *
  * @access public
  * @param base_errors $msgs Error messages object
  * @param string $paramName Parameter groupd name
  */
  function surfer_admin(&$msgs, $paramName = "sadm") {
    $this->__construct($msgs, $paramName);
  }

  /**
   * Get singleton object.
   *
   * @param base_errors $msgs Error messages object
   * @param string $paramName Parameter groupd name
   * @return sufers_admin $instance
   */
  function getInstance($msgs = NULL, $paramName = "sadm") {
    static $instance;
    if (!(isset($instance) && is_object($instance))) {
      $instance = new surfer_admin($msgs, $paramName);
    } else {
      $instance->msgs = $msgs;
    }
    if ($instance->paramName != $paramName) {
      $instance->paramName = $paramName;
    }
    return $instance;
  }

  /**
   * Check if a surfer id exists already.
   *
   * By default, blocked surfers are not taken into account
   * because they should not be displayed in frontend.
   * For backend operations, though, you can call this method
   * with an optional second parameter set to TRUE.
   *
   * @access public
   * @param string $id A unique surfer id
   * @param boolean $includeBlocked Check blocked surfers too.
   * @return boolean Status (id exists)
   */
  function existID($id, $includeBlocked = FALSE) {
    if (checkit::isGuid($id, TRUE)) {
      $sql = "SELECT COUNT(*)
               FROM %s
              WHERE surfer_id = '%s' %s";
      $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
      $params = array($this->tableSurfer, $id, $blockedClause);
      if ($res = $this->databaseQueryFmtWrite($sql, $params)) {
        if ($row = $res->fetchRow()) {
          return ((bool)$row[0] > 0);
        }
      }
    }
    return FALSE;
  }

  /**
   * Check if a surfer email handle exists already.
   *
   * Looks up the email address you provide as an argument in the surfer table.
   * Returns TRUE if it exists, FALSE otherwise.
   *
   * @see surfer_admin::existID() for behavior on blocked surfers
   * @access public
   * @param string $email unique surfer email
   * @param boolean $includeBlocked Check blocked surfers too
   * @return boolean Status (email exists)
   */
  function existEmail($email, $includeBlocked = FALSE) {
    if (trim($email) != '') {
      $sql = "SELECT count(*)
                FROM %s
               WHERE surfer_email = '%s'";
      $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
      $params = array($this->tableSurfer, $email, $blockedClause);
      if ($res = $this->databaseQueryFmtWrite($sql, $params)) {
        if ($row = $res->fetchRow()) {
          return ((bool)$row[0] > 0);
        }
      }
    }
    return FALSE;
  }

  /**
   * Check if a surfer handle exists already.
   *
   * Looks up the user handle you provide as an argument in the surfer table.
   * Returns true if it exists, false otherwise.
   *
   * @see surfer_admin::existID() for behavior on blocked surfers
   * @access public
   * @param string $handle Unique surfer handle
   * @param boolean $includeBlocked Check blocked surfers too
   * @return boolean Status (handle exists)
   */
  function existHandle($handle, $includeBlocked = FALSE) {
    if (trim($handle) != '') {
      $sql = "SELECT count(*)
                FROM %s
               WHERE surfer_handle = '%s' %s";
      $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
      $params = array($this->tableSurfer, $handle, $blockedClause);
      if ($res = $this->databaseQueryFmtWrite($sql, $params)) {
        if ($row = $res->fetchRow()) {
          return ($row[0] > 0);
        }
      }
    }
    return FALSE;
  }

  /**
  * Get surfer id by handle.
  *
  * Looks up and returns the surfer id for a user handle you provide as an argument.
  * Returns an empty string if the user handle doesn't exist.
  *
  * Once looked up, each handle:id pair is stored in an associative array
  * because the same id might be requested several times in contact lists and the like.
  *
  * Since 2008-01-21, it supports multiple handles.
  *
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param array|string $handle Surfer handle(s)
  * @param boolean $includeBlocked Check blocked surfers too
  * @return mixed array|string Surfer id(s) or empty
  */
  function getIdByHandle($handle, $includeBlocked = FALSE) {
    $result = array();
    if (is_array($handle)) {
      $isMultiples = TRUE;
    } else {
      $isMultiples = FALSE;
      $handle = array($handle);
    }
    $loadHandles = array();
    foreach ($handle as $value) {
      if (trim($value) != '') {
        if (isset($this->idByHandleCache[$value]) &&
            $this->idByHandleCache[$value] != '') {
          $result[$value] = $this->idByHandleCache[$value];
        } else {
          $loadHandles[] = $value;
        }
      }
    }
    if (count($loadHandles) > 0) {
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('surfer_handle', $loadHandles)
      );
      $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
      // Otherwise, look up this handle in database
      $sql = "SELECT surfer_id, surfer_handle
                FROM %s
               WHERE $filter $blockedClause";
      $params = array($this->tableSurfer);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          // Store id in cache for future use and return it
          $this->idByHandleCache[$row['surfer_handle']] = $row['surfer_id'];
          $this->handleByIdCache[$row['surfer_id']] = $row['surfer_handle'];
          $result[$row['surfer_handle']] = $row['surfer_id'];
        }
      }
    }
    if ($isMultiples) {
      // case 1: multiples handles
      return $result;
    } else if (count($result) > 0) {
      // case 2: one handle lookup
      return reset($result);
    }
    return '';
  }

  /**
  * Get surfer id by email.
  *
  * Looks up and returns the surfer id for an email address you provide as an argument.
  * Returns an empty string if the email doesn't exist.
  *
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param string $mail Unique surfer email address
  * @param boolean $includeBlocked Check blocked surfers too
  * @return string Surfer id or empty
  */
  function getIdByMail($mail, $includeBlocked = FALSE) {
    if (trim($mail) != '') {
      $sql = "SELECT surfer_id, surfer_email
                FROM %s
               WHERE surfer_email = '%s' %s";
      $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
      $params = array($this->tableSurfer, $mail, $blockedClause);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          return $row['surfer_id'];
        }
      }
    }
    return '';
  }

  /**
  * Get surfer email by id.
  *
  * Looks up and returns the email address for a surfer ID you provide as an argument.
  * Returns an empty string if the ID doesn't exist
  *
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param string $id Unique surfer id
  * @param boolean $includeBlocked Check blocked surfers too
  * @return string Surfer email or empty
  */
  function getMailById($id, $includeBlocked = FALSE) {
    if (trim($id) != '') {
      $sql = "SELECT surfer_email, surfer_id
                FROM %s
               WHERE surfer_id = '%s' %s";
      $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
      $params = array($this->tableSurfer, $id, $blockedClause);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          return $row['surfer_email'];
        }
      }
    }
    return '';
  }

  /**
  * Get surfer handle by id.
  *
  * Looks up and returns the surfer handle for a surfer id you provide as an argument.
  * Returns an empty string if the handle doesn't exist.
  *
  * Once looked up, each id:handle pair is stored in an associative array
  * because the same handle might be requested several times in contact lists and the like.
  *
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param string $id Unique surfer id(s)
  * @param boolean $order Flag to order multiple results by surfer handle
  * @param string $sort Sort muliple surfer handle results ('ASC'|'DESC')
  * @param boolean $includeBlocked Check blocked surfers too
  * @return string Surfer handle(s) or empty
  */
  function getHandleById($id, $order = FALSE, $sort = 'ASC', $includeBlocked = FALSE) {
    $result = array();
    if (is_array($id)) {
      $isMultiples = TRUE;
    } else {
      $isMultiples = FALSE;
      $id = array($id);
    }
    $loadIds = array();
    foreach ($id as $value) {
      if (checkit::isGUID($value, TRUE)) {
        if (isset($this->handleByIdCache[$value]) &&
            $this->handleByIdCache[$value] != '') {
          $result[$value] = $this->handleByIdCache[$value];
        } else {
          $loadIds[] = $value;
        }
      }
    }
    if (count($loadIds) > 0) {
      // Otherwise, look up this handle in database
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('surfer_id', $loadIds)
      );
      $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
      $sql = "SELECT surfer_handle, surfer_id
                FROM %s
              WHERE $filter $blockedClause";
      $params = array($this->tableSurfer);
      if ($order) {
        $sql .= " ORDER BY surfer_handle %s";
        $params[] = ($sort == 'ASC') ? $sort : 'DESC';
      }
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          // Store handle in cache for future use and return it
          $this->handleByIdCache[$row['surfer_id']] = $row['surfer_handle'];
          $this->idByHandleCache[$row['surfer_handle']] = $row['surfer_id'];
          $result[$row['surfer_id']] = $row['surfer_handle'];
        }
      }
    }
    if ($isMultiples) {
      // case 1: multiples ids
      return $result;
    } else if (count($result) > 0) {
      // case 2: one id lookup
      return reset($result);
    }
    return '';
  }

  /**
  * Get any surfer handle by id.
  *
  * Use this method when you definitely need any handle, even for blocked surfers,
  * and usually do not need sorting of any kind. Supports multiple ids.
  *
  * @param string $id Surfer id(s)
  * @return string|array Surfer handle(s) or empty
  */
  function getAnyHandleById($id) {
    return $this->getHandleById($id, FALSE, 'ASC', TRUE);
  }

  /**
  * Get surfer name data by id(s)
  *
  * Looks up and returns an array containing handle, surname and given name
  * for surfer id(s) you provide as an argument. Returns NULL if no data has been found.
  *
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param mixed array|string $id Surfer id(s)
  * @param boolean $includeBlocked Check blocked surfers too
  * @return mixed array|string Surfer name(s) or empty
  */
  function getNameById($id, $includeBlocked = FALSE) {
    $result = array();
    if (is_array($id)) {
      $isMultiples = TRUE;
    } else {
      $isMultiples = FALSE;
      $id = array($id);
    }
    $loadIds = array();
    foreach ($id as $value) {
      if (checkit::isGUID($value, TRUE)) {
        if (isset($this->nameByIdCache[$value]) &&
            $this->nameByIdCache[$value] != '') {
          $result[$value] = $this->nameByIdCache[$value];
        } else {
          $loadIds[] = $value;
        }
      }
    }
    if (count($loadIds) > 0) {
      // Otherwise, look up these names in database
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('surfer_id', $loadIds)
      );
      $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
      $sql = "SELECT surfer_id,
                     surfer_handle,
                     surfer_surname,
                     surfer_givenname
                FROM %s
              WHERE $filter $blockedClause";
      $params = array($this->tableSurfer);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          // Store handle in cache for future use and return it
          $this->nameByIdCache[$row['surfer_id']] = array(
            'surfer_handle' => $row['surfer_handle'],
            'surfer_surname' => $row['surfer_surname'],
            'surfer_givenname' => $row['surfer_givenname']
          );
          $this->handleByIdCache[$row['surfer_id']] = $row['surfer_handle'];
          $this->idByHandleCache[$row['surfer_handle']] = $row['surfer_id'];
          $result[$row['surfer_id']] = array(
            'surfer_handle' => $row['surfer_handle'],
            'surfer_surname' => $row['surfer_surname'],
            'surfer_givenname' => $row['surfer_givenname']
          );
        }
      }
    }
    if ($isMultiples) {
      // case 1: multiples ids
      return $result;
    } elseif (count($result) > 0) {
      // case 2: one id lookup
      return reset($result);
    } else {
      return '';
    }
  }

  /**
  * Get surfer basic data by id(s)
  *
  * Looks up and returns an array containing handle, surname, given name, email, and gender
  * for surfer id(s) you provide as an argument. Returns NULL if no data has been found.
  *
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param mixed array|string $id Surfer id(s)
  * @param boolean $includeBlocked Check blocked surfers too
  * @return mixed array|string Surfer name(s) or empty
  */
  function getBasicDataById($id, $includeBlocked = FALSE) {
    $result = array();
    if (is_array($id)) {
      $isMultiples = TRUE;
    } else {
      $isMultiples = FALSE;
      $id = array($id);
    }
    $filter = str_replace(
      '%',
      '%%',
      $this->databaseGetSQLCondition('surfer_id', $id)
    );
    $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
    $sql = "SELECT surfer_id,
                   surfer_handle,
                   surfer_surname,
                   surfer_givenname,
                   surfer_email,
                   surfer_gender
              FROM %s
            WHERE $filter $blockedClause";
    $params = array($this->tableSurfer);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        // Store handle and name in cache for future use and return it
        $this->nameByIdCache[$row['surfer_id']] = array(
          'surfer_handle' => $row['surfer_handle'],
          'surfer_surname' => $row['surfer_surname'],
          'surfer_givenname' => $row['surfer_givenname']
        );
        $this->handleByIdCache[$row['surfer_id']] = $row['surfer_handle'];
        $this->idByHandleCache[$row['surfer_handle']] = $row['surfer_id'];
        $result[$row['surfer_id']] = array(
          'surfer_handle' => $row['surfer_handle'],
          'surfer_surname' => $row['surfer_surname'],
          'surfer_givenname' => $row['surfer_givenname'],
          'surfer_email' => $row['surfer_email'],
          'surfer_gender' => $row['surfer_gender']
        );
      }
    }

    if ($isMultiples) {
      // case 1: multiples ids
      return $result;
    } else if (count($result) > 0) {
      // case 2: one id lookup
      return reset($result);
    }
    return '';
  }

  /**
  * Get surfer email address by request token.
  *
  * Looks up and returns the email address for a request token you provide as an argument.
  * Returns an empty string if the email doesn't exist
  *
  * @access public
  * @param string $token Unique token to identify a surfer change request
  * @return string Surfer email address or empty
  */
  function getMailByToken($token) {
    $sql = "SELECT s.surfer_email, s.surfer_id,
                   sr.surferchangerequest_surferid,
                   sr.surferchangerequest_token
            FROM %s s, %s sr
            WHERE sr.surferchangerequest_token = '%s'
            AND s.surfer_id = sr.surferchangerequest_surferid;";
    $params = array($this->tableSurfer, $this->tableChangeRequests, $token);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        return $row['surfer_email'];
      }
    }
    return '';
  }

  /**
  * Returns the surfer id of a surfer who has set a token within the change requests table.
  *
  * @access public
  * @param string $token Unique token to identify a surfer change request
  * @return string Surfer id
  */
  function getIdByToken($token) {
    $sql = "SELECT surferchangerequest_surferid FROM %s WHERE surferchangerequest_token='%s'";
    $params = array($this->tableChangeRequests, $token);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return FALSE;
  }

  /**
  * Get surfer handle by email
  *
  * Looks up and returns the surfer handle for an email address
  * you provide as an argument. Returns an empty string
  * if the email doesn't exist
  *
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param string $mail Surfer email
  * @param boolean $includeBlocked Check blocked surfers too
  * @return string Surfer handle
  */
  function getHandleByMail($mail, $includeBlocked = FALSE) {
    $sql = "SELECT surfer_handle
              FROM %s
             WHERE surfer_email = '%s' %s";
    $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
    $params = array($this->tableSurfer, $mail, $blockedClause);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return $row[0];
      }
    }
    return '';
  }

  /**
  * Get surfer ids by valid handles.
  *
  * Takes an array of surfer handles and returns an array of corresponding the
  * surfer ids which exist.
  *
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param mixed array|string $handles Surfer handle(s)
  * @param boolean $includeBlocked Check blocked surfers too
  * @return string $ids Surfer id(s)
  */
  function getIdsByValidHandles($handles, $includeBlocked = FALSE) {
    $handleCondition = $this->databaseGetSQLCondition('surfer_handle', $handles);
    $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
    // Build query
    $sql = "SELECT surfer_id, surfer_handle
              FROM %s
             WHERE $handleCondition $blockedClause";
    $sqlData = array($this->tableSurfer);
    // Create result array
    $ids = array();
    // Execute query
    if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
      // Get results and store them in array
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $ids[] = $row['surfer_id'];
      }
    }
    return $ids;
  }

  /**
  * Get amount of surfers.
  *
  * Returns the amount of surfers in the community.
  * The $mode parameter can be set to one of the following values:
  * - 'total' (default): total amount of surfers
  * - 'valid': valid surfers only (i.e. those who can actually log in)
  * - 'online': amount of surfers currently online
  *             (i.e. status online and activities since 30 minutes ago)
  *
  * @todo Check / optimize method name meaning ("number" <> "amount")
  * @access public
  * @param string $mode Set mode to get surfers, see description
  * @return integer $surfersNum Surfers' amount
  */
  function getSurfersNum($mode = 'total') {
    if (!in_array($mode, array('total', 'valid', 'online'))) {
      $mode = 'total';
    }
    $condition = '';
    if ($mode == 'valid') {
      $condition = " WHERE surfer_valid = 1";
    } elseif ($mode == 'online') {
      $condition = sprintf(
        " WHERE surfer_status = 1 AND surfer_lastaction > %d", time() - 1800
      );
    }
    $sql = "SELECT COUNT(*)
              FROM %s".$condition;
    $sqlParams = array($this->tableSurfer);
    $surfersNum = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $surfersNum = $num;
      }
    }
    return $surfersNum;
  }

  /**
  * Get all valid surfers.
  *
  * Returns an array containing the ids of all valid surfers.
  * If the optional $withHandles parameter is set to TRUE, you will get an id=>handle array.
  * Optionally, you may specify also specify group id, maximum amount of results, and offset.
  *
  * @access public
  * @param boolean $withHandles Flag to get handles data too
  * @param mixed integer|NULL $groupId Filter by group id
  * @param mixed integer|NULL $limit Results limit
  * @return array $surfers Surfers data
  */
  function getAllValidSurfers($withHandles = FALSE,
                              $groupId = NULL, $limit = NULL, $offset = NULL) {
    $addHandles = ($withHandles == TRUE) ? ', surfer_handle' : '';
    $groupClause = ($groupId != NULL) ? ' AND surfergroup_id = '.$groupId : '';
    $orderClause = ($withHandles == TRUE) ? ' ORDER BY surfer_handle ASC' : '';
    $sql = "SELECT surfer_id %s
              FROM %s
             WHERE surfer_valid = 1 %s %s";
    $sqlParams = array($addHandles, $this->tableSurfer, $groupClause, $orderClause);
    $surfers = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($withHandles) {
          $surfers[$row['surfer_id']] = $row['surfer_handle'];
        } else {
          $surfers[] = $row['surfer_id'];
        }
      }
      return $surfers;
    }
  }

  /**
  * Get the amount of valid surfers.
  *
  * Returns the total amount of valid surfers.
  * Optionally, you may specify a group.
  *
  * @todo Check / optimize method name meaning ("number" <> "amount")
  * @access public
  * @param integer $groupId Filter by group id
  * @return integer $number Surfers' amount
  */
  function getAllValidSurfersNum($groupId = NULL) {
    $groupClause = ($groupId != NULL) ? ' AND surfergroup_id = '.$groupId : '';
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE surfer_valid = 1 %s";
    $sqlParams = array($this->tableSurfer, $groupClause);
    $amount = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($count = $res->fetchField()) {
        $amount = $count;
      }
    }
    return $amount;
  }

  /**
  * Get a list of all surfers who have ever logged in.
  *
  * The first parameter determines the surfer(s)
  * for which you need this information.
  * The second, optional parameter is the kind of attribute
  * you provide ('id' as default, 'email', or 'handle').
  * The return value is FALSE for surfers who never logged in
  * or the timestamp of the last login for those who did;
  * if $surferAttr is an array, the return value will be an associative
  * array of attribute=>FALSE_or_timestamp pairs.
  * If you provide an illegal attribute type or if none of the
  * surfers you asked for exist, the return value is NULL.
  *
  * @access public
  * @param string|array $surferAttr Value of the surfer's attribute to search for
  * @param string $attrType Tye of the surfer's attribute to search for
  * @param boolean $loggedinOnly Get surfers which have been logged in only
  * @return mixed NULL|boolean|int|array $result Surfer(s)' status
  */
  function getSurfersLoginStatus($surferAttr, $attrType = 'id', $loggedinOnly = FALSE) {
    if (!(in_array($attrType, array('id', 'email', 'handle')))) {
      return NULL;
    }
    $attrType = 'surfer_'.$attrType;
    if (is_array($surferAttr)) {
      $isMultiples = TRUE;
    } else {
      $surferAttr = array($surferAttr);
      $isMultiples = FALSE;
    }
    $cond = $this->databaseGetSQLCondition($attrType, $surferAttr);
    $sql = "SELECT ".$attrType.", surfer_lastlogin
              FROM %s
             WHERE ".str_replace('%', '%%', $cond);
    if ($loggedinOnly) {
      $sql .= "  AND surfer_lastlogin > 0";
    }
    $sqlParams = array($this->tableSurfer);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $lastLogin = $row['surfer_lastlogin'];
        if ($lastLogin == 0) {
          $lastLogin = FALSE;
        }
        $result[$row[$attrType]] = $lastLogin;
      }
    }
    if (empty($result)) {
      return NULL;
    }
    if ($isMultiples) {
      return $result;
    }
    return reset($result);
  }

  /**
  * Get black list rules of a certain type.
  *
  * Since 19.01.2009 the parameter $type allows multiple values (array).
  *
  * @access public
  * @param array|string $type Type to get black list, i.e. by surfer "handle"
  * @param boolean $ordered Get an ordered list
  * @param boolean $detailed Full list including delay state (email only)
  * @return array $rules A list with blacklist rules
  */
  function getBlacklistRules($type = 'handle', $ordered = FALSE, $detailed = FALSE) {
    if ($detailed && $type != 'email') {
      $detailed = FALSE;
    }
    if (!is_array($type)) {
      $type = array($type);
    }
    $types = array();
    foreach ($type as $testType) {
      if (in_array($testType, array('handle', 'email', 'password'))) {
        $types[] = $testType;
      }
    }
    $rules = array();
    if (empty($types)) {
      return $rules;
    }
    $condition = $this->databaseGetSQLCondition('blacklist_type', $types);
    $sql = "SELECT blacklist_id, blacklist_match, blacklist_type, blacklist_delay
              FROM %s
             WHERE " . str_replace('%', '%%', $condition);
    if ($ordered == TRUE) {
      $sql .= " ORDER BY blacklist_match ASC";
    }
    $sqlParams = array($this->tableBlacklist);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($detailed) {
          $rules[$row['blacklist_id']] = $row;
        } else {
          $rules[$row['blacklist_id']] = $row['blacklist_match'];
        }
      }
    }
    return $rules;
  }

  /**
  * Check a string against one of the blacklists
  *
  * Will be called by checkHandle(), checkEmailAgainstHandle(),
  * and checkPasswordAgainstBlacklist(), respectively.
  *
  * @param string $type Blacklist type ('handle'|'email'|'password')
  * @param string $value the string to check
  * @return boolean TRUE if string is valid against blacklist, FALSE otherwise
  */
  function checkAgainstBlacklist($type, $value) {
    // Get the black list rules
    $detailed = FALSE;
    if ($type == 'email') {
      $detailed = TRUE;
    }
    $rules = $this->getBlacklistRules($type, FALSE, $detailed);
    // If there are no rules yet, we can safely return TRUE
    if (empty($rules)) {
      return TRUE;
    }
    // Otherwise we need to check the handle against each rule
    foreach ($rules as $id => $likeRule) {
      if ($detailed) {
        if ($likeRule['blacklist_delay'] == 1) {
          continue;
        }
        $likeRule = $likeRule['blacklist_match'];
      }
      $rule = $this->convertFromSqlLikeValue($likeRule);
      // Strip whitespace
      $rule = trim($rule);
      // Convert current rule into valid regexp
      if ($rule[0] == '*') {
        $rule = ltrim($rule, '*');
      } else {
        $rule = '^'.$rule;
      }
      if ($rule[strlen($rule) - 1] == '*') {
        $rule = rtrim($rule, '*');
      } else {
        $rule = $rule.'$';
      }
      // Now do the check
      if (preg_match('~'.$rule.'~i', $value)) {
        // Return FALSE on first match
        return FALSE;
      }
    }
    // We didn't find any match, so the handle is valid
    return TRUE;
  }

  /**
  * Check a (potential) surfer handle against the black list.
  *
  * @access public
  * @param string $handle Surfer handle
  * @return boolean TRUE if handle is valid against blacklist, FALSE otherwise
  */
  function checkHandle($handle) {
    return $this->checkAgainstBlacklist('handle', $handle);
  }

  /**
  * Check a (potential) email address against the black list
  *
  * @access public
  * @param string $email Surfer email
  * @return boolean TRUE if email is valid against blacklist, FALSE otherwise
  */
  function checkEmailAgainstBlacklist($email) {
    return $this->checkAgainstBlacklist('email', $email);
  }

  /**
  * Get delay time for an email address
  *
  * @param string $email email address
  * @return integer delay time in seconds
  */
  function getEmailDelayTime($email) {
    // Get the general delay time
    $delayTime = $this->getProperty('FREEMAIL_DELAY', 0);
    // If it's 0, we can safely return this value regardless of the email address
    if ($delayTime == 0) {
      return 0;
    }
    // Now check whether the email address matches a blacklist rule in delay mode
    $match = FALSE;
    $emailBlacklist = $this->getBlacklistRules('email', FALSE, TRUE);
    foreach ($emailBlacklist as $id => $blacklistRule) {
      if ($blacklistRule['blacklist_delay'] == 0) {
        continue;
      }
      $rule = $this->convertFromSqlLikeValue($blacklistRule['blacklist_match']);
      // Strip whitespace
      $rule = trim($rule);
      // Convert current rule into valid regexp
      if ($rule[0] == '*') {
        $rule = ltrim($rule, '*');
      } else {
        $rule = '^'.$rule;
      }
      if ($rule[strlen($rule) - 1] == '*') {
        $rule = rtrim($rule, '*');
      } else {
        $rule = $rule.'$';
      }
      // Now do the check
      if (preg_match('~'.$rule.'~i', $email)) {
        $match = TRUE;
        break;
      }
    }
    if ($match) {
      return $delayTime * 60;
    }
    return 0;
  }

  /**
  * Check a (potential) password against the black list
  *
  * @access public
  * @param string $password the password to check
  * @return boolean TRUE if email is valid against blacklist, FALSE otherwise
  */
  function checkPasswordAgainstBlacklist($password) {
     return $this->checkAgainstBlacklist('password', $password);
  }

  /**
  * Try to add a rule to the black list
  *
  * @access public
  * @param string $type Blacklist type ('handle'|'email'|'password')
  * @param string $rule Blacklist rule
  * @return boolean Status (rule has been added)
  */
  function addRuleToBlacklist($type, $rule) {
    if (!in_array($type, array('handle', 'email', 'password'))) {
      return FALSE;
    }

    $rule = $this->convertToSqlLikeValue($rule);

    // Now check whether this rule already exists
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE blacklist_type = '%s'
               AND blacklist_match = '%s'";
    $sqlParams = array($this->tableBlacklist, $type, $rule);
    $amount = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($count = $res->fetchField()) {
        $amount = $count;
      }
    }
    if ($amount > 0) {
      return FALSE;
    }
    // Checks passed, so add the rule
    $data = array(
      'blacklist_type' => $type,
      'blacklist_match' => $rule
    );
    $success = $this->databaseInsertRecord(
      $this->tableBlacklist, 'blacklist_id', $data
    );
    return $success;
  }

  /**
  * Try to add a handle rule to the black list.
  *
  * @access public
  * @param string $rule Handle blacklist rule
  * @return boolean Status
  */
  function addHandleRuleToBlacklist($rule) {
    // Check if the rule is valid
    if (!preg_match('~^\*?[a-z0-9-]+\*?$~i', $rule)) {
      return FALSE;
    }
    return $this->addRuleToBlacklist('handle', $rule);
  }

  /**
  * Try to add an email rule to the black list.
  *
  * @access public
  * @param string $rule Email blacklist rule
  * @return boolean Status
  */
  function addEmailRuleToBlacklist($rule) {
    // Check if the rule is valid
    if (!preg_match('~^\*?([^@]+@?[^@]*|[^@]*@?[^@]+)\*?$~i', $rule)) {
      return FALSE;
    }
    return $this->addRuleToBlacklist('email', $rule);
  }

  /**
  * Try to add a password rule to the black list.
  *
  * @access public
  * @param string $rule Password blacklist rule
  * @return boolean Status
  */
  function addPasswordRuleToBlacklist($rule) {
    // Check if the rule is valid
    if (!preg_match('~^\*?.+\*?$~i', $rule)) {
      return FALSE;
    }
    return $this->addRuleToBlacklist('password', $rule);
  }

  /**
  * Check a potential password according to the current password policy.
  *
  * Details on return values:
  * 0 => Password complies to the policy
  * -1 => too short (shorter than PASSWORD_MIN_LENGTH)
  * -2 => equal to surfer handle
  * -4 => matches a black list entry
  * (the return value is 0 or the sum of all error values in order
  *   to provide verbose error messages)
  *
  * @param string $password the password to check
  * @param string $handle (optional, default '') handle to compare the password
  *   to if PASSWORD_NOT_EQUALS_HANDLE is set
  * @return integer 0 if password complies to the policy, a negative value otherwise
  */
  function checkPasswordForPolicy($password, $handle = '') {
    $minLength = $this->getProperty('PASSWORD_MIN_LENGTH', 0);
    $compareToHandle = $this->getProperty('PASSWORD_NOT_EQUALS_HANDLE', 0);
    $checkAgainstBlacklist = $this->getProperty('PASSWORD_BLACKLIST_CHECK', 0);
    $result = 0;
    if ($minLength > 0 && strlen($password) < $minLength) {
      $result -= 1;
    }
    if ($compareToHandle && $handle != '' && strtolower($password) == strtolower($handle)) {
      $result -= 2;
    }
    if ($checkAgainstBlacklist && $this->checkPasswordAgainstBlacklist($password) === FALSE) {
      $result -= 4;
    }
    return $result;
  }

  /**
  * Service function to create an email change request within the community database.
  * When successful, the confirmation string to be sent to the surfer's new email address
  * is returned.
  *
  * @param string $surferId Unique surfer id
  * @param string $newEmail New email value
  * @param mixed int|NULL $mailExpiry Expiry of change request's confirmation email
  * @return mixed string|boolean Status (change request has been added)
  */
  function emailChangeRequest($surferId, $newEmail, $mailExpiry = NULL) {
    srand((double)microtime() * 1000000);
    $emailConfirmString = uniqid(rand());

    // For backwards compatibility.
    if ($mailExpiry == NULL) {
      $mailExpiry = $this->data['Mail_Expiry'];
    }

    $t = time();

    // Upadate this data in table
    if ($this->databaseInsertRecord(
      $this->tableChangeRequests,
      'surferchangerequest_id',
      array(
        'surferchangerequest_surferid' => $surferId,
        'surferchangerequest_type' => 'email',
        'surferchangerequest_data' => $newEmail,
        'surferchangerequest_token' => $emailConfirmString,
        'surferchangerequest_time' => $t,
        'surferchangerequest_expiry' => $t + $mailExpiry * 3600
      )
    ) !== FALSE) {
      return $emailConfirmString;
    }

    return FALSE;
  }

  /**
  * Returns all change requests for a specific surfer, whose id is given.
  * For each change request type available only the last one is used.
  *
  * @param string $surferId Unique 32-char surfer id
  * @return mixed array|boolean Type => value results or FALSE
  */
  function getChangeRequests($surferId) {
    $sql = "SELECT surferchangerequest_data, surferchangerequest_type
              FROM %s
             WHERE surferchangerequest_surferid='%s'
          ORDER BY surferchangerequest_time DESC";
    $params = array($this->tableChangeRequests, $surferId);
    $result = array();
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!isset($result[ $row['surferchangerequest_type' ]])) {
          $result[ $row['surferchangerequest_type'] ] = $row['surferchangerequest_data'];
        }
      }
      return $result;
    }
    return FALSE;
  }

  /**
  * Returns the value of the change request, which token is provided.
  *
  * When the token is invalid FALSE is returned.
  *
  * @param string $token Unique 32-char token to identify change requests
  * @return mixed array|boolean Type => value results or FALSE
  */
  function getChangeRequest($token) {
    $sql = "SELECT surferchangerequest_data FROM %s WHERE surferchangerequest_token='%s'";
    $params = array($this->tableChangeRequests, $token);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return FALSE;
  }

  /**
  * Deletes a change request thats token has been given.
  *
  * @param $token Unique 32-char token to identify change requests
  * @return boolean Status
  */
  function deleteChangeRequest($token) {
    return $this->databaseDeleteRecord(
      $this->tableChangeRequests, 'surferchangerequest_token', $token
    );
  }

  /**
  * Load surfer data by id.
  *
  * Looks up a surfer's basic data in the surfer table
  * for the surfer id you provide as an argument.
  * Stores the surfer's data in the $this->editSurfer attribute
  * and returns it as well.
  * If you set the optional second argument $frontend to TRUE,
  * the auth_user table will not be joined because it is
  * of no use in frontend, and the data will not be stored
  * in the $this->editSurfer attribute.
  * Returns NULL if the surfer id doesn't exist.
  *
  * @access public
  * @param string $id Surfer id
  * @param boolean $frontend Get surfer data for frontend
  * @return mixed NULL|array Surfer data or empty
  */
  function loadSurfer($id, $frontend = FALSE) {
    // Do not try to look for empty ids
    if (!checkit::isGUID($id, TRUE)) {
      return NULL;
    }
    $filter = str_replace('%', '%%', $this->databaseGetSQLCondition('s.surfer_id', $id));
    $sql = "SELECT s.surfer_id, s.surfer_handle,
                   s.surfer_givenname, s.surfer_surname,
                   s.surfer_email, s.surfer_valid,
                   s.surfer_password, s.surfer_gender,
                   s.surfer_avatar, s.surfergroup_id,
                   s.surfer_lastlogin, s.surfer_lastaction,
                   s.surfer_registration,
                   s.surfer_status,
                   sg.surfergroup_title";
    if ($frontend === FALSE) {
      $sql .= ", s.auth_user_id, u.user_id";
    }
    $sql .= " FROM %s AS s
              LEFT OUTER JOIN %s AS sg ON sg.surfergroup_id = s.surfergroup_id";
    if ($frontend === FALSE) {
      $sql .= " LEFT OUTER JOIN %s AS u ON u.user_id = s.auth_user_id";
    }
    $sql .= " WHERE ".$filter;
    $params = array($this->tableSurfer,
                    $this->tableGroups);
    if ($frontend === FALSE) {
      $params[] = $this->tableAuthuser;
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($frontend === FALSE) {
          $this->editSurfer = $row;
        }
        // Feed the caches
        $this->handleByIdCache[$row['surfer_id']] = $row['surfer_handle'];
        $this->nameByIdCache[$row['surfer_id']] = array(
          'surfer_handle' => $row['surfer_handle'],
          'surfer_surname' => $row['surfer_surname'],
          'surfer_givenname' => $row['surfer_givenname']
        );
        $this->idByHandleCache[$row['surfer_handle']] = $row['surfer_id'];
        return $row;
      }
    }
    return NULL;
  }

  /**
  * Load surfers data by id.
  *
  * Looks up a surfer's basic data in the surfer table
  * for the surfer id you provide as an argument.
  * Stores the surfer's data in the $this->editSurfer attribute
  * and returns it as well.
  * Returns NULL if the surfer id doesn't exist.
  *
  * @see existID() for behavior on blocked surfers
  * @access public
  * @param array $id Surfer id
  * @param mixed NULL|string $sort Sort results ('ASC' | 'DESC')
  * @param integer $limit Limit database results set
  * @param integer $offset Offset in database results set
  * @param boolean $includeBlocked Load blocked surfers too
  * @param boolean $frontend Get surfer data for frontend
  * @return array Surfers' data
  */
  function loadSurfers($ids, $sort = 'ASC', $limit = NULL, $offset = NULL,
                       $includeBlocked = FALSE, $frontend = FALSE, $useHandles = FALSE) {
    if ($sort != 'ASC' && $sort != 'DESC') {
      $sort = NULL;
    }
    $orderBy = ($sort !== NULL) ? " ORDER BY s.surfer_handle ".$sort : '';

    $filter = str_replace(
      '%',
      '%%',
      $this->databaseGetSQLCondition(
        ($useHandles) ? 's.surfer_handle' : 's.surfer_id',
        $ids
      )
    );

    $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
    $sql = "SELECT s.surfer_id, s.surfer_handle,
                   s.surfer_givenname, s.surfer_surname,
                   s.surfer_email, s.surfer_valid,
                   s.surfer_password, s.surfer_gender,
                   s.surfer_avatar, s.surfergroup_id,
                   s.surfer_lastlogin, s.surfer_lastaction,
                   s.surfer_registration,
                   s.surfer_status,
                   sg.surfergroup_title";
    if ($frontend === FALSE) {
      $sql .= ", s.auth_user_id, u.user_id";
    }
    $sql .= " FROM %s AS s
              LEFT OUTER JOIN %s AS sg ON sg.surfergroup_id = s.surfergroup_id";
    if ($frontend === FALSE) {
      $sql .= " LEFT OUTER JOIN %s AS u ON u.user_id = s.auth_user_id";
    }
    $sql .= " WHERE $filter $blockedClause $orderBy";
    $params = array($this->tableSurfer,
                    $this->tableGroups);
    if ($frontend === FALSE) {
      $params[] = $this->tableAuthuser;
    }
    $results = array();
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $results[$row['surfer_id']] = $row;
        // Feed the caches
        $this->handleByIdCache[$row['surfer_id']] = $row['surfer_handle'];
        $this->nameByIdCache[$row['surfer_id']] = array(
          'surfer_handle' => $row['surfer_handle'],
          'surfer_surname' => $row['surfer_surname'],
          'surfer_givenname' => $row['surfer_givenname']
        );
        $this->idByHandleCache[$row['surfer_handle']] = $row['surfer_id'];
      }
      $this->surfersAbsCount = $res->absCount();
    }
    return $results;
  }

  /**
  * Load all surfers.
  *
  * Call this method if you need a list of all surfers and their basic data.
  * You can provide limit and offset arguments for paging,
  * which you are strongly encouraged to do unless you want a lot of database traffic.
  *
  * @see existID() for behavior on blocked surfers
  * @access public
  * @param mixed int|NULL $limit Limit database results set
  * @param mixed int|NULL $offset Offset of database results set
  * @param mixed string|array $orderBy Order by fieldname or array (field => direction)
  * @param string $sort Sort direction (ASC|DESC)
  * @param boolean $withAbsCount Get absolute count of database results
  * @param boolean $includeBlocked Get blocked surfers too
  * @return array $basicData Nested, associative data with $surferId=>$dataArray pairs
  */
  function loadAllSurfers($limit = NULL, $offset = NULL, $orderBy = 'surfer_lastmodified',
                          $sort = 'DESC', $withAbsCount = TRUE, $includeBlocked = FALSE) {
    // Desired fields as array and as string
    $fields = array('surfer_id', 'surfer_handle', 'surfer_givenname', 'surfer_surname',
                    'surfer_email', 'surfer_valid', 'surfer_registration',
                    'surfer_lastlogin', 'surfer_lastaction', 'surfer_gender',
                    'surfer_avatar', 'surfer_status', 'surfer_lastmodified');
    $fieldString = implode(', ', $fields);
    // Normalize parameters
    $sort = '';
    if (is_array($orderBy)) {
      $orderString = '';
      $directions = array('ASC', 'DESC');
      foreach ($orderBy as $orderField => $direction) {
        if (in_array($orderField, $fields) &&
            in_array(strtoupper($direction), $directions)) {
          $orderString .= ', '.$orderField.' '.$direction;
        }
      }
      $orderString = substr($orderString, 1);
    } elseif (in_array($orderBy, $fields)) {
      $orderString = $orderBy;
      if ($sort != 'DESC') {
        $sort = 'ASC';
      }
    } else {
      $orderString = 'surfer_lastmodified';
      $sort = 'DESC';
    }
    $sql = "SELECT %s
              FROM %s
                   %s
          ORDER BY %s %s";
    $blockedClause = ($includeBlocked === FALSE) ? ' WHERE surfer_valid != 4' : '';
    $sqlParams = array($fieldString, $this->tableSurfer, $blockedClause, $orderString, $sort);
    $basicData = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $basicData[$row['surfer_id']] = $row;
      }
      $surferCount = $res->absCount();
      if ($withAbsCount) {
        return array('surfers' => $basicData, 'abscount' => $surferCount);
      }
    }
    return $basicData;
  }

  /**
  * Load surfer names by id.
  *
  * Looks up a surfer's real name, handle,
  * and email address in the surfer table
  * for the surfer id(s) you provide as an argument.
  * Stores the surfer's data in the $this->editSurfer attribute
  * and returns it as well.
  * Returns NULL if the surfer id doesn't exist
  *
  * @see existID() for behavior on blocked surfers
  * @access public
  * @param array $id Unique 32-char surfer id
  * @param string $sort Sort results ('ASC'|'DESC')
  * @param integer $limit Limit database results set
  * @param integer $offset Offset of database results set
  * @param boolean $includeBlocked Load blocked surfers too
  * @return mixed NULL|array $results
  */
  function loadSurferNames($ids, $sort = 'ASC',
                           $limit = NULL, $offset = NULL, $includeBlocked = FALSE) {
    if ($sort != 'DESC') {
      $sort = 'ASC';
    }
    $filter = str_replace('%', '%%', $this->databaseGetSQLCondition('surfer_id', $ids));
    $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
    $sql = "SELECT surfer_id, surfer_handle,
                   surfer_givenname, surfer_surname,
                   surfer_email, surfer_gender
              FROM %s
             WHERE $filter $blockedClause
          ORDER BY surfer_handle ".$sort;

    $params = array($this->tableSurfer);
    $results = array();
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $results[$row['surfer_id']] = $row;
      }
      $this->surfersAbsCount = $res->absCount();
    }
    return $results;
  }

  /**
  * Set surfer status to valid.
  *
  * Modifies the valid status of a surfer with a given id.
  *
  * @param string $surferId Unique 32-char guid
  * @param integer $valid Valid status value, 0 or 1
  * @return boolean Status
  */
  function setValid($surferId, $valid = 1) {
    if (is_numeric($valid) && (int)$valid >= 1 && (int)$valid <= 4) {
      $data = array('surfer_valid' => (int)$valid);
      $success = $this->databaseUpdateRecord(
        $this->tableSurfer, $data, 'surfer_id', $surferId
      );
      // Call the action dispatcher method for other modules
      // who need to do surfer cleanup stuff
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $actionsObj = base_pluginloader::getPluginInstance(
        '79f18e7c40824a0f975363346716ff62', $this
      );
      $num = $actionsObj->call(
        'community',
        'onSetSurferValid',
        array('surfer_id' => $surferId, 'valid' => $valid)
      );
      return (bool)$success;
    }
    return FALSE;
  }

  /**
  * Check whether a surfer can already be validated based on registration delay
  *
  * @param string $confirmString the confirmation id
  * @return integer 0 if the surfer can be validated, remaining time to wait (in seconds) otherwise
  */
  function checkValidationTime($confirmString) {
    $sql = "SELECT surferchangerequest_id, surferchangerequest_time,
                   surferchangerequest_data
              FROM %s
             WHERE surferchangerequest_token = '%s'";
    $params = array($this->tableChangeRequests, $confirmString);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!isset($row['surferchangerequest_data']) || $row['surferchangerequest_data'] == 0) {
          return 0;
        }
        $now = time();
        if ($now < $row['surferchangerequest_time'] + $row['surferchangerequest_data']) {
          return $row['surferchangerequest_time'] + $row['surferchangerequest_data'] - $now;
        }
      }
    }
    return 0;
  }

  /**
  * Make a surfer valid using their email confirmation id
  *
  * @param string $confirmString the confirmation id
  * @param integer $newStatus status for confirmed surfers (optional, default 1)
  * @return mixed surfer id on success, FALSE otherwise
  */
  function makeValid($confirmString, $newStatus = 1) {
    $surferId = '';
    $sql = "SELECT surferchangerequest_id, surferchangerequest_surferid,
                   surferchangerequest_expiry
              FROM %s
             WHERE surferchangerequest_token = '%s'";
    $params = array($this->tableChangeRequests, $confirmString);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $changeId = $row['surferchangerequest_id'];
        $surferId = $row['surferchangerequest_surferid'];
        $expiry = $row['surferchangerequest_expiry'];
      }
    }
    if ($surferId != '' && $expiry >= time()) {
      $data = array(
        'surfer_valid' => $newStatus,
        'surfer_registration' => time()
      );
      $success = $this->databaseUpdateRecord(
        $this->tableSurfer,
        $data,
        'surfer_id',
        $surferId
      );
      $this->databaseDeleteRecord(
        $this->tableChangeRequests,
        'surferchangerequest_id',
        $changeId
      );
      if (FALSE !== $success) {
        return $surferId;
      }
    }
    return FALSE;     // Error: Invalid/expired token or error updating record
  }

  /**
  * Creates a new surfer record.
  *
  * The minimum of required parameters are email address and handle.
  * The method checks whether any of them already exists and returns FALSE in this case.
  * If the surfer record can be created successfully, its surfer id is returned.
  *
  * @param array $data Surfer's data
  * @param boolean $ignoreIllegal optional, default FALSE
  * @return mixed string|FALSE Status
  */
  function createSurfer($data, $ignoreIllegal = FALSE) {
    $mailCheck = isset($data['surfer_email']) && isset($data['surfer_handle']) &&
      trim($data['surfer_email']) != '' && trim($data['surfer_handle']) != '';
    if (!$mailCheck) {
      return FALSE;
    }
    if (!checkit::isEMail($data['surfer_email'])) {
      return FALSE;
    }
    if (!$ignoreIllegal &&
      !preg_match(
        '{^[A-Za-z0-9-]{4,'.PAPAYA_COMMUNITY_HANDLE_MAX_LENGTH.'}$}',
        $data['surfer_handle'])
      ) {
      return FALSE;
    }
    if ($ignoreIllegal) {
      $surferExists = $this->existEmail($data['surfer_email'], TRUE) &&
        $this->existHandle($data['surfer_handle'], TRUE);
    } else {
      $surferExists = $this->existEmail($data['surfer_email'], TRUE) ||
        $this->existHandle($data['surfer_handle'], TRUE);
    }
    if ($surferExists) {
      return FALSE;
    }
    $dataFields = array(
      'surfer_handle', 'surfergroup_id', 'surfer_password',
      'surfer_givenname', 'surfer_surname', 'surfer_email',
      'surfer_valid', 'surfer_gender', 'surfer_avatar',
      'surfer_registration'
    );
    $saveData = array(
      'surfer_id' => $this->createSurferId()
    );
    foreach ($dataFields as $field) {
      if (isset($data[$field])) {
        $saveData[$field] = $data[$field];
      }
    }
    if (isset($saveData['surfer_password'])) {
      $saveData['surfer_password'] = $this->getPasswordHash($saveData['surfer_password']);
    }
    // Try to insert the record
    if ($this->databaseInsertRecord($this->tableSurfer, NULL, $saveData)) {
      return $saveData['surfer_id'];
    }
    return FALSE;
  }

  /**
  * Save surfer formular data.
  *
  * Stores a surfer's basic data that was changed in the surfer form
  * permanently in the surfer database table.
  * Returns true if this works, otherwise false.
  *
  * @access public
  * @param mixed $params Optional surfer data params, fallback $this->params
  * @param boolean $ignoreIllegal ignore illegal values optional, default FALSE
  * @return boolean Status
  */
  function saveSurfer($params = NULL, $ignoreIllegal = FALSE) {
    // If parameters have been provided as an argument,
    // use them as surfer data, otherwise
    // take the data from surfer form
    // This behavior is useful because it can save
    // both a new surfer with automatically created fixed data
    // and a surfer whose data was changed manually
    if (isset($params) && $params !== NULL) {
      // use method argument
      $data = &$params;
      // If there is no current edit surfer but a surfer id in data,
      // load this surfer as the current edit surfer
      if (!isset($this->editSurfer) || $this->editSurfer == NULL) {
        if (isset($data['surfer_id']) && $data['surfer_id'] != '') {
          $this->editSurfer = $this->loadSurfer($data['surfer_id']);
        }
      }
    } else {
      // use surfer form data
      $data = array(
        'surfer_handle' => isset($this->params['surfer_handle']) ?
          (string)$this->params['surfer_handle'] : NULL,
        'surfergroup_id' => isset($this->params['surfergroup_id']) ?
          (int)$this->params['surfergroup_id'] : NULL,
        'surfer_givenname' => isset($this->params['surfer_givenname']) ?
          (string)$this->params['surfer_givenname'] : NULL,
        'surfer_surname' => isset($this->params['surfer_surname']) ?
          (string)$this->params['surfer_surname'] : NULL,
        'surfer_email' => isset($this->params['surfer_email']) ?
          (string)$this->params['surfer_email'] : NULL,
        'surfer_valid' => @(int)$this->params['surfer_valid'] ?
          (string)$this->params['surfer_valid'] : 0,
        'surfer_gender' => isset($this->params['surfer_gender']) ?
          (string)$this->params['surfer_gender'] : NULL,
        'surfer_avatar' => isset($this->params['surfer_avatar']) ?
          (string)$this->params['surfer_avatar'] : NULL
      );
      // If the password was changed,
      // create its hash and add it to data
      if (isset($this->params['surfer_password']) &&
          trim($this->params['surfer_password']) != '') {
        $data['surfer_password'] = $this->getPasswordHash($this->params['surfer_password']);
      }
    }
    // stop if no valid surfer id is provided
    if (empty($this->editSurfer['surfer_id'])) {
      return FALSE;
    }
    // In either case, add modification date/time
    $data['surfer_lastmodified'] = time();
    // If the new valid status is anything else than 'valid',
    // kill a potential relogin cookie field
    if (isset($data['surfer_valid']) && $data['surfer_valid'] != 1) {
      $data['surfer_relogin'] = '';
    }
    // If we've got a surfer handle and do not ignore illegal values,
    // check it against the black list
    if (!$ignoreIllegal && isset($data['surfer_handle'])) {
      if (!$this->checkHandle($data['surfer_handle'])) {
        return FALSE;
      }
    }
    // Try to update the database record
    $update = $this->databaseUpdateRecord(
      $this->tableSurfer, $data, 'surfer_id', $this->editSurfer['surfer_id']
    );
    if ($update !== FALSE) {
      // Call the action dispatcher method for other modules
      // that need to act on surfer data modification
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $actionsObj = base_pluginloader::getPluginInstance(
        '79f18e7c40824a0f975363346716ff62', $this
      );
      $actionData = array(
        'surfer_id' => $this->editSurfer['surfer_id'],
        'data_before' => $this->extractActionData($this->editSurfer),
        'data_after' => $this->extractActionData($data)
      );
      $num = $actionsObj->call('community', 'onModifySurfer', $actionData);
      // If this succeeds, load the changed surfer data
      $this->editSurfer = $this->loadSurfer($this->editSurfer['surfer_id']);
      // Set current time as registration date if the surfer was made valid
      if ($this->editSurfer['surfer_valid'] == 1 &&
          $this->editSurfer['surfer_registration'] == 0) {
        $data = array('surfer_registration' => time());
        $this->databaseUpdateRecord(
          $this->tableSurfer, $data, 'surfer_id', $this->editSurfer['surfer_id']
        );
      }
      if ($this->editSurfer['auth_user_id'] != '') {
        // If this surfer is an authorized user, store his or her data
        // in the auth user table as well
        $data = array(
          'surname' => $this->editSurfer['surfer_surname'],
          'givenname' => $this->editSurfer['surfer_givenname'],
          'email' => $this->editSurfer['surfer_email'],
          'user_password' => $this->editSurfer['surfer_password'],
          'username' => $this->editSurfer['surfer_handle']
        );
        $this->databaseUpdateRecord(
          $this->tableAuthuser, $data, 'user_id', $this->editSurfer['auth_user_id']
        );
        return ($update !== FALSE);
      } else {
        return TRUE;
      }
    } else {
      return FALSE;
    }
  }

  /**
  * Extract data to be used by the action dispatcher
  *
  * @param array $data
  * @return array
  */
  function extractActionData($data) {
    $fields = array(
      'surfer_handle',
      'surfer_givenname',
      'surfer_surname',
      'surfer_email',
      'surfer_valid',
      'surfergroup_id',
      'surfer_gender',
      'surfer_avatar'
    );
    $result = array();
    foreach ($fields as $field) {
      if (isset($data[$field])) {
        $result[$field] = $data[$field];
      }
    }
    return $result;
  }

  /**
  * Save surfer basic data.
  *
  * Stores a surfer's (non-critical) basic data.
  * Returns true if this works, otherwise false.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer idx
  * @param array $params Additional data
  * @return boolean Status
  */
  function saveSurferData($surferId, $params) {
    // If the surfer id doesn't exist, we can return FALSE right away
    if (!($this->existID($surferId, TRUE))) {
      return FALSE;
    }
    // Create data array from params
    $dataFields = array(
      'surfer_handle',
      'surfer_givenname',
      'surfer_surname',
      'surfer_email',
      'surfer_gender',
      'surfer_avatar'
    );
    $data = array();
    foreach ($dataFields as $field) {
      if (isset($params[$field]) && trim($params[$field]) != '') {
        $data[$field] = $params[$field];
      }
    }
    // If we don't have data at all, return FALSE
    if (empty($data)) {
      return FALSE;
    }
    // Add modification date/time
    $data['surfer_lastmodified'] = time();
    // Load surfer record before the modification
    $arrSurfer = $this->loadSurfers($surferId);
    $surferDataBefore = $arrSurfer[$surferId];
    // Update the database record
    $update = $this->databaseUpdateRecord(
      $this->tableSurfer, $data, 'surfer_id', $surferId
    );
    // If this does not succeed, return FALSE
    if ($update === FALSE) {
      return FALSE;
    }
    // Load surfer record after the modification
    $arrSurfer = $this->loadSurfers($surferId);
    $surferData = $arrSurfer[$surferId];
    // Call the action dispatcher method for other modules
    // that need to act on surfer data modification
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $actionsObj = base_pluginloader::getPluginInstance(
      '79f18e7c40824a0f975363346716ff62', $this
    );
    $actionData = array(
      'surfer_id' => $surferId,
      'data_before' => $this->extractActionData($surferDataBefore),
      'data_after' => $this->extractActionData($surferData)
    );
    $num = $actionsObj->call('community', 'onModifySurfer', $actionData);
    // If this surfer is an authorized user, store his or her data
    // in the auth user table as well
    if ($surferData['auth_user_id'] != '') {
      $authDataFields = array(
        'surfer_handle' => 'username',
        'surfer_givenname' => 'givenname',
        'surfer_surname' => 'surname',
        'surfer_email' => 'email'
      );
      $userData = array();
      foreach ($authDataFields as $surferKey => $userKey) {
        if (isset($data[$surferKey])) {
          $userData[$userKey] = $data[$surferKey];
        }
      }
      if (!empty($userData)) {
        return (
          $this->databaseUpdateRecord(
            $this->tableAuthuser, $userData, 'user_id', $surferData['auth_user_id']
          ) !== FALSE
        );
      }
    }
    return TRUE;
  }

  /**
  * Delete surfer.
  *
  * Deletes a surfer, specified by his/her id, from surfer db table
  * as well as the corresponding profile, contact, publishing, and change request data.
  * Returns true on success, otherwise false
  *
  * @access public
  * @param integer $surferId Unique 32-char surfer id
  * @return boolean Status
  */
  function deleteSurfer($surferId) {
    // Call the action dispatcher method for other modules
    //that need to do surfer cleanup stuff
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $actionsObj = base_pluginloader::getPluginInstance(
      '79f18e7c40824a0f975363346716ff62', $this
    );
    $num = $actionsObj->call('community', 'onDeleteSurfer', $surferId);
    // Success/failure depends only on surfer table; any other data is optional
    $deletion = $this->databaseDeleteRecord(
      $this->tableSurfer, 'surfer_id', $surferId
    );
    // Get out of here if there was no such surfer
    if ($deletion === FALSE) {
      return FALSE;
    }
    // Attempt to delete profile data first
    $this->databaseDeleteRecord(
      $this->tableContactData, 'surfercontactdata_surferid', $surferId
    );
    // Now proceed to contact data
    // Note: surfer id can be both 'requestor' and/or 'requested'
    $this->databaseDeleteRecord(
      $this->tableContacts, 'surfercontact_requestor', $surferId
    );
    $this->databaseDeleteRecord(
      $this->tableContacts, 'surfercontact_requested', $surferId
    );
    // Delete contact publishing data
    // Similar to contact data, surfer id can be either 'surferid'
    // (i.e. the surfer who decides on publishing his/her contact data
    // to someone else) or 'partner' (i.e. the surfer to whom that
    // data is published/not published)
    $this->databaseDeleteRecord(
      $this->tableContactPublic, 'surfercontactpublic_surferid', $surferId
    );
    $this->databaseDeleteRecord(
      $this->tableContactPublic, 'surfercontactpublic_partner', $surferId
    );
    // Delete change requests
    $this->databaseDeleteRecord(
      $this->tableChangeRequests, 'surferchangerequest_surferid', $surferId
    );
    // Take a note about the latest deletion for methods
    // that rely on surfers being still present
    $this->setProperty('LAST_SURFER_DELETION', time());
    // A surfer was deleted, so report success
    return TRUE;
  }

  /**
  * Search surfers.
  *
  * Returns an array of surfers (id => handle, email, givenname, surname)
  * that match an SQL "LIKE" pattern you provide as a parameter.
  * Returns an empty array if no surfers match the pattern.
  *
  * @see existID() for behavior on blocked surfers
  * @access public
  * @param string $pattern Search pattern
  * @param boolean $handleOnlyset to TRUE if you only want to search in surfer handles
  * @param boolean $includeBlocked Seach in blocked surfers too
  * @param string $orderBy Order search results by a given (NULL = disabled)
  * @return array $result Surfers data (id, handle, email, givenname, surfname)
  */
  function searchSurfers($pattern, $handleOnly = FALSE,
                         $includeBlocked = FALSE, $orderBy = 'surfer_handle') {
    $result = array();

    include_once(PAPAYA_INCLUDE_PATH.'system/base_searchstringparser.php');
    $parser = new searchStringParser();
    if ($handleOnly) {
      $searchFields = array('surfer_handle');
    } else {
      $searchFields = array('surfer_handle', 'surfer_givenname', 'surfer_surname');
    }
    if ($filter = $parser->getSQL($pattern, $searchFields, PAPAYA_SEARCH_BOOLEAN)) {
      $sql = "SELECT surfer_id,
                     surfer_handle,
                     surfer_email,
                     surfer_givenname,
                     surfer_surname
              FROM %s
              WHERE ".str_replace('%', '%%', $filter)." %s";
      if (in_array($orderBy, array('surfer_handle', 'surfer_email', 'surfer_surname'))) {
        $sql .= " ORDER BY ".$orderBy." ASC";
      }
      $blockedClause = ($includeBlocked === FALSE) ? ' AND surfer_valid != 4' : '';
      $sqlParams = array($this->tableSurfer, $blockedClause);
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['surfer_id']] = $row;
        }
      }
    }
    return $result;
  }

  /**
  * Simple surfer handle search.
  *
  * Useful if you only need a plain list of handles that match an SQL search pattern.
  *
  * @access public
  * @param string $pattern Surfer handle (part)
  * @return array Matching surfer handles
  */
  function searchHandlesSimple($pattern) {
    $sql = "SELECT surfer_handle
              FROM %s
             WHERE surfer_handle LIKE '%s'";
    $sqlParams = array($this->tableSurfer, str_replace('%', '%%', $pattern));
    $handles = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($handle = $res->fetchField()) {
        $handles[] = $handle;
      }
    }
    return $handles;
  }

  /**
  * Load surfer groups.
  *
  * Loads the whole group list from the surfergroups db table
  * and stores them in the groupList array attribute.
  * Returns true if any group data is available or false if not.
  *
  * @access public
  * @param mixed $adminGroups optional, array of admin groups or NULL
  * @return boolean Status (loaded)
  */
  function loadGroups($adminGroups = NULL) {
    $this->groupList = array();
    $sql = "SELECT surfergroup_id,
                   surfergroup_title,
                   surfergroup_profile_page,
                   surfergroup_redirect_page,
                   surfergroup_admin_group,
                   surfergroup_identifier
              FROM %s";
    if ($adminGroups !== NULL) {
      if (!in_array(0, $adminGroups)) {
        $adminGroups[] = 0;
      }
      $sql .= " WHERE ".str_replace(
        '%',
        '%%',
        $this->databaseGetSqlCondition(array('surfergroup_admin_group' => $adminGroups))
      );
    }
    $sql .= " ORDER BY surfergroup_title";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableGroups))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->groupList[$row['surfergroup_id']] = $row;
      }
      $res->free();
      if (!empty($this->groupList)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Create, verify and return a new surfer id.
  *
  * Creates an md5-hashed, unique surfer id.
  *
  * @access public
  * @return string $id Unique 32-char surfer id
  */
  function createSurferID() {
    srand((double)microtime() * 10000000);
    // Paranoia mode: Repeat until an unused id is found
    // (which a correct implementation of uniq!!id() should
    //  grant anyway ...)
    do {
      $id = (string)md5(uniqid(rand()));
    } while ($this->existID($id, TRUE));
    return $id;
  }

  /**
  * Get password hash
  *
  * @see base_auth_secure::getPasswordHash
  * @access public
  * @param string $password Password input
  * @return string Password hash
  */
  function getPasswordHash($password) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_auth_secure.php');
    return base_auth_secure::getPasswordHash($password);
  }

  /**
  * Get language title by id.
  *
  * @access public
  * @param int $lngId Language id
  * @return string Unique language identifier / title
  */
  function getLanguageTitle($lngId = NULL) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    if ($this->lngSelect == NULL) {
      $this->lngSelect = &base_language_select::getInstance();
    }
    if ($lngId == NULL) {
      $lngId = $this->lngSelect->currentLanguageId;
    }
    $sql = "SELECT lng_short FROM %s
             WHERE lng_id = %d";
    $res = $this->databaseQueryFmt($sql, array($this->tableLng, $lngId));
    if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      return $row['lng_short'];
    } else {
      return 'en-US';
    }
  }

  /**
  * Block a surfer contact.
  *
  * Method to add another surfer to a surfer's block list (or remove it, optionally).
  *
  * @access public
  * @param string $surferId The blocking surfer by id
  * @param string $contactId The surfer to be (un-)blocked by id
  * @param boolean $block Add or remove block
  * @return mixed result of database operation or FALSE
  */
  function blockSurfer($surferId, $contactId, $block = TRUE) {
    // Make block value numeric for database
    if ($block) {
      $blockValue = 1;
    } else {
      $blockValue = 0;
    }
    // Try to get the surferlists record for this surfer
    $sql = "SELECT surferlist_id, surferlist_surferid, surferlist_contact
              FROM %s
             WHERE surferlist_surferid='%s'
               AND surferlist_contact='%s'";
    $sqlParams = array($this->tableSurferLists,
                       $surferId,
                       $contactId
                      );
    $fieldId = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fieldId = $row['surferlist_id'];
      }
    }
    // Check whether there is a record
    $success = FALSE;
    if ($fieldId) {
      // Update existing record
      $data = array('surferlist_blocked' => $blockValue);
      $success = $this->databaseUpdateRecord(
        $this->tableSurferLists,
        $data,
        'surferlist_id',
        $fieldId
      );
    } else {
      // Create a new record
      $data = array('surferlist_surferid' => $surferId,
                    'surferlist_contact' => $contactId,
                    'surferlist_blocked' => $blockValue
                   );
      $success = $this->databaseInsertRecord(
        $this->tableSurferLists,
        'surferlist_id',
        $data
      );
    }
    return $success;
  }

  /**
  * Bookmark a surfer contact.
  *
  * Method to add another surfer to a surfer's bookmark list (or remove it, optionally).
  *
  * @access public
  * @param string $surferId The bookmarking surfer by id
  * @param string $contactId The surfer to be (un-)bookmarked by id
  * @param boolean $bookmark Add or remove bookmark
  * @return mixed result of database operation or FALSE
  */
  function bookmarkSurfer($surferId, $contactId, $bookmark = TRUE) {
    // Make bookmark value numeric for database
    if ($bookmark) {
      $bookmarkValue = 1;
    } else {
      $bookmarkValue = 0;
    }
    // Try to get the surferlists record for this surfer
    $sql = "SELECT surferlist_id, surferlist_surferid, surferlist_contact
              FROM %s
             WHERE surferlist_surferid='%s'
               AND surferlist_contact='%s'";
    $sqlParams = array($this->tableSurferLists,
                       $surferId,
                       $contactId
                      );
    $fieldId = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fieldId = $row['surferlist_id'];
      }
    }
    $success = FALSE;
    // Check whether there is a record
    if ($fieldId) {
      // Update existing record
      $data = array('surferlist_bookmark' => $bookmarkValue);
      $success = $this->databaseUpdateRecord(
        $this->tableSurferLists,
        $data,
        'surferlist_id',
        $fieldId
      );
    } else {
      // Create a new record
      $data = array('surferlist_surferid' => $surferId,
                    'surferlist_contact' => $contactId,
                    'surferlist_bookmark' => $bookmarkValue
                   );
      $success = $this->databaseInsertRecord(
        $this->tableSurferLists,
        'surferlist_id',
        $data
      );
    }
    return $success;
  }

  /**
  * Get list of blocked surfer contacts by id.
  *
  * @access public
  * @param string $surferId Unqiue 32-char surfer id
  * @return array $blocks Blocked surfer contact ids
  */
  function getBlocks($surferId) {
    $sql = "SELECT surferlist_surferid, surferlist_contact, surferlist_blocked
              FROM %s
             WHERE surferlist_surferid = '%s'
               AND surferlist_blocked = 1";
    $sqlParams = array($this->tableSurferLists, $surferId);
    $blocks = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $blocks[] = $row['surferlist_contact'];
      }
    }
    return $blocks;
  }

  /**
  * Get list of bookmarked surfer contacts.
  *
  * @access public
  * @param string $surferId Unqiue 32-char surfer id
  * @return array $bookmarks Bookmarked surfer contact ids
  */
  function getBookmarks($surferId) {
    $sql = "SELECT surferlist_surferid, surferlist_contact, surferlist_bookmark
              FROM %s
             WHERE surferlist_surferid = '%s'
               AND surferlist_bookmark = 1";
    $sqlParams = array($this->tableSurferLists, $surferId);
    $bookmarks = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $bookmarks[] = $row['surferlist_contact'];
      }
    }
    return $bookmarks;
  }

  /**
  * Check whether a surfer has blocked a specific surfer contact.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $contactId Unique 32-char surfer id (contact)
  * @return boolean Status (blocked)
  */
  function isBlocked($surferId, $contactId) {
    $sql = "SELECT surferlist_surferid, surferlist_contact, surferlist_blocked
              FROM %s
             WHERE surferlist_surferid = '%s'
               AND surferlist_contact = '%s'";
    $sqlParams = array($this->tableSurferLists, $surferId, $contactId);
    $blocked = FALSE;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $blocked = $row['surferlist_blocked'];
      }
    }
    return $blocked;
  }

  /**
  * Check whether a surfer has bookmarked a specific surfer contact.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $contactId Unique 32-char surfer id (contact)
  * @return boolean Status (bookmarked)
  */
  function isBookmarked($surferId, $contactId) {
    $sql = "SELECT surferlist_surferid, surferlist_contact, surferlist_bookmark
              FROM %s
             WHERE surferlist_surferid = '%s'
               AND surferlist_contact = '%s'";
    $sqlParams = array($this->tableSurferLists, $surferId, $contactId);
    $bookmarked = FALSE;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $bookmarked = $row['surferlist_bookmark'];
      }
    }
    return $bookmarked;
  }

  /**
  * Add a contact (request) from one surfer to another.
  *
  * First checks whether there already is a contact
  * or a contact request and returns FALSE in that case
  * Otherwise, the contact (request) is added,
  * and the method returns TRUE
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $contactId Unique 32-char surfer id (contact)
  * @param int $status Contact status, i.e. pending (see status constants)
  * @return boolean Status (added)
  */
  function addContactRequest($surferId, $contactId, $status = SURFERCONTACT_STATUS_PENDING) {
    // security fix: block invalid GUIDs
    if (!(checkit::isGUID($surferId, TRUE) && checkit::isGUID($contactId, TRUE))) {
      return FALSE;
    }

    // Never allow contact requests for the current surfer him-/herself
    if ($surferId == $contactId) {
      return FALSE;
    }
    // Import and instantiate contact manager
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    $contact = $contactManager->findContact($contactId);
    // If there already is a contact (request), return FALSE
    if (($contact & SURFERCONTACT_DIRECT) || ($contact & SURFERCONTACT_PENDING)) {
      return FALSE;
    }
    // There's no contact (request) yet, so add a request
    $time = time();
    $data = array('surfercontact_requestor' => $surferId,
                  'surfercontact_requested' => $contactId,
                  'surfercontact_status' => $status,
                  'surfercontact_timestamp' => $time
                 );
    if ($this->databaseInsertRecord($this->tableContacts, 'surfercontact_id', $data)) {
      // If we're setting the SURFERCONTACT_STATUS_ACCEPTED status, i.e. forcing a contact,
      // we also need to set a contant record for the opposite direction
      if ($status == SURFERCONTACT_STATUS_ACCEPTED) {
        $data = array('surfercontact_requestor' => $contactId,
                      'surfercontact_requested' => $surferId,
                      'surfercontact_status' => $status,
                      'surfercontact_timestamp' => $time
                     );
        if ($this->databaseInsertRecord($this->tableContacts, 'surfercontact_id', $data)) {
          return TRUE;
        }
        return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Add a forced surfer contact.
  *
  * Calls addContactRequest() with a status of SURFERCONTACT_STATUS_ACCEPTED
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $contactId Unique 32-char surfer id (contact)
  * @return boolean Status (added)
  */
  function forceContact($surferId, $contactId) {
    return $this->addContactRequest($surferId, $contactId, SURFERCONTACT_STATUS_ACCEPTED);
  }

  /**
  * Remove a contact request from one surfer to another.
  *
  * Can be called if the contact surfer declines the request.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $contactId Unique 32-char surfer id (contact)
  * @param boolean $bidirectional Flag to remove contact in both directions
  * @return boolean Status (deleted)
  */
  function removeContactRequest($surferId, $contactId, $bidirectional = FALSE) {
    // security fix: block invalid GUIDs
    if (!(checkit::isGUID($surferId, TRUE) && checkit::isGUID($contactId, TRUE))) {
      return FALSE;
    }

    // Import and instantiate contact manager
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    $contact = $contactManager->findContact($contactId);
    // Check whether there is a contact request
    if ($contact & SURFERCONTACT_PENDING) {
      // Create condition array
      $condition = array('surfercontact_requestor' => $surferId,
                         'surfercontact_requested' => $contactId
                        );
      // Remove it
      $deleted = $this->databaseDeleteRecord($this->tableContacts, $condition);
      // Simply return the value if we don't go bidirectional
      if (!$bidirectional) {
        return $deleted;
      }
      // Bidirectional mode
      // If the record has been deleted yet, return the value
      if ($deleted) {
        return $deleted;
      }
      // Try the other direction
      // Create condition array
      $condition = array('surfercontact_requestor' => $contactId,
                         'surfercontact_requested' => $surferId
                        );
      // Remove it
      $deleted = $this->databaseDeleteRecord($this->tableContacts, $condition);
      return $deleted;
    } else {
      return FALSE;
    }
  }

  /**
  * Accept a contact request from one surfer to another.
  *
  * Can be called as an action if a surfer accepts a contact request.
  *
  * @access public
  * @param string $surferId 32-char surfer id
  * @param string $contactId 32-char surfer id (contact)
  * @param boolean $bidirectional Flag to accept contact in both directions
  * @return boolean Status (accepted)
  */
  function acceptContact($surferId, $contactId, $bidirectional = FALSE) {
    // security fix: block invalid GUIDs
    if (!(checkit::isGUID($surferId, TRUE) && checkit::isGUID($contactId, TRUE))) {
      return FALSE;
    }

    // Import and instantiate contact manager
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    $contact = $contactManager->findContact($contactId);
    // Check whether there is a contact request
    if ($contact & SURFERCONTACT_PENDING) {
      // Accept the contact by setting its status to SURFERCONTACT_STATUS_ACCEPTED
      // Build data and condition arrays
      $time = time();
      $data = array('surfercontact_status' => SURFERCONTACT_STATUS_ACCEPTED,
                    'surfercontact_timestamp' => $time
                   );
      $condition = array('surfercontact_requestor' => $surferId,
                         'surfercontact_requested' => $contactId
                        );
      $update = $this->databaseUpdateRecord($this->tableContacts, $data, $condition);
      // If the update worked, add a record for the other direction
      if ($update) {
        $data['surfercontact_requestor'] = $contactId;
        $data['surfercontact_requested'] = $surferId;
        $insert = $this->databaseInsertRecord($this->tableContacts, 'surfercontact_id', $data);
      }
      // Simply return the values if we don't go bidirectional
      if (!$bidirectional) {
        return $update && $insert;
      }
      // Bidirectional mode
      // If the record has been updated yet, return the value
      if ($update && $insert) {
        return TRUE;
      }
      // Try the other direction
      // Create condition array
      $data = array('surfercontact_status' => SURFERCONTACT_STATUS_ACCEPTED,
                    'surfercontact_timestamp' => $time
                   );
      $condition = array('surfercontact_requestor' => $contactId,
                         'surfercontact_requested' => $surferId
                        );
      // Update it
      $update = $this->databaseUpdateRecord($this->tableContacts, $data, $condition);
      // If the update worked, add a record for the other direction
      if ($update) {
        $data['surfercontact_requestor'] = $surferId;
        $data['surfercontact_requested'] = $contactId;
        $insert = $this->databaseInsertRecord($this->tableContacts, 'surfercontact_id', $data);
      }
      return $update && $insert;
    } else {
      return FALSE;
    }
  }

  /**
  * Remove a surfer contact.
  *
  * This one will always work in bidirectional mode
  * because any of the two surfers can cancel the contact.
  *
  * @access public
  * @param string $surferId 32-char surfer id
  * @param string $contactId 32-char surfer id (contact)
  * @return boolean Status (removed)
  */
  function removeContact($surferId, $contactId) {
    // security fix: block invalid GUIDs
    if (!(checkit::isGUID($surferId, TRUE) && checkit::isGUID($contactId, TRUE))) {
      return FALSE;
    }

    // Delete one direction first
    // Create condition array
    $condition = array('surfercontact_requestor' => $surferId,
                       'surfercontact_requested' => $contactId
                      );
    $deleted1 = $this->databaseDeleteRecord($this->tableContacts, $condition);
    // Now delete the other direction
    $condition = array('surfercontact_requestor' => $contactId,
                       'surfercontact_requested' => $surferId
                      );
    $deleted2 = $this->databaseDeleteRecord($this->tableContacts, $condition);
    return $deleted1 && $deleted2;
  }

  /**
  * Get surfer's online status.
  *
  * Returns the online status of the desired surfer(s).
  *
  * @access public
  * @param mixed string|array $surferIds Unique 32-char surfer id(s)
  * @return mixed integer|array Surfer(s)' status
  */
  function getOnlineStatus($surferIds) {
    // Idle timeout (in seconds)
    $idleTimeout = $this->getProperty('IDLE_TIMEOUT', 30) * 60;
    $condition = str_replace(
       '%',
       '%%',
       $this->databaseGetSQLCondition(array('surfer_id' => $surferIds))
    );
    $sql = "SELECT surfer_id, surfer_status, surfer_lastaction, surfer_valid
              FROM %s
             WHERE ".$condition;
    $sqlParams = array($this->tableSurfer);
    $status = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        // Basic online status
        $stat = $row['surfer_status'];
        if ($row['surfer_valid'] != 1) {
          // Surfer invalid?
          $stat = 0;
        } elseif ($row['surfer_lastaction'] + $idleTimeout < time()) {
          // Idle timeout?
          $stat = 0;
        }
        $status[$row['surfer_id']] = $stat;
      }
    }
    if (empty($status)) {
      return NULL;
    }
    if (is_array($surferIds)) {
      return $status;
    }
    return $status[$surferIds];
  }

  /**
   * Returns the latest online surfers.
   *
   * @access public
   * @param string $sortBy optional use: handle, surname, email, lastaction
   * @param string $sortDirection Set sort direction (asc, desc)
   * @param integer $limit Set limit for database results set
   * @param integer $offset Set offset for database results set
   * @return array Amount of found results and data of online surfers
   */
  function getOnlineSurfers($sortBy = NULL, $sortDirection = NULL,
                            $limit = NULL, $offset = NULL) {
    // Idle timeout (in seconds)
    $idleTimeout = $this->getProperty('IDLE_TIMEOUT', 30) * 60;
    // check requested sort field
    $arrSortBy = array(
      'surfer_handle',
      'surfer_surname',
      'surfer_email',
      'surfer_lastaction'
    );
    // if valid, use it, else use default
    if (in_array('surfer_'.$sortBy, $arrSortBy)) {
      $sqlSortBy = 'surfer_'.$sortBy;
    } else {
      // default sort by
      $sqlSortBy = 'surfer_lastaction';
    }
    // check requested sort direction
    $arrSortDirection = array('asc', 'desc');
    // if valid, use it, else use default
    if (in_array($sortDirection, $arrSortDirection)) {
      $sqlSortDirection = $sortDirection;
    } else {
      // default sort direction
      $sqlSortDirection = 'desc';
    }
    // set default limit & offset if nothing requested
    if (!is_null($limit) && is_int($limit) && (int)$limit > 0) {
      $limit = (int)$limit;
    } else {
      $limit = LIMIT_ONLINE_LIST;
    }
    if (!is_null($offset) && is_int($offset) && (int)$offset >= 0) {
      $offset = (int)$offset;
    } else {
      $offset = 0;
    }
    // get sql statement
    $sql = "SELECT surfer_id, surfer_handle
              FROM %s
             WHERE surfer_status = 1
              AND surfer_lastaction + %d >= %d
              AND surfer_valid = 1
              ORDER BY %s %s";
    $sqlParams = array(
      $this->tableSurfer,
      $idleTimeout,
      time(),
      $sqlSortBy,
      $sqlSortDirection
    );
    $onlineList = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $onlineList[$row['surfer_id']] = $row['surfer_handle'];
      }
    }
    if (empty($onlineList)) {
      return array('count' => 0, 'list' => array());
    }
    return array('count' => $res->absCount(), 'list' => $onlineList);
  }

  /**
  * Get dynamic profile data field.
  *
  * Returns the value of a specific dynamic profile field,
  * referred to by its unique internal name.
  *
  * @deprecated 2008-01-21 you have to use getDynamicData instead of this method!
  * @param mixed array|string $surferIds Unique 32-char surfer id(s)
  * @param string $fieldName Dynamic data file name
  * @return mixed array|string|NULL Dynamic data result
  */
  function getDynamicDataField($surferIds, $fieldName) {
    return $this->getDynamicData($surferIds, $fieldName);
  }

  /**
  * Get dynamic profile data.
  *
  * Returns the value(s) of specific dynamic profile field(s),
  * referred to by unique internal name(s) or by a single id.
  *
  * @access public
  * @param mixed array|string $surferIds Unique 32-char surfer id(s)
  * @param mixed array|string|int $fields Dynamic data fields
  * @return mixed array|string|NULL Dynamic data
  */
  function getDynamicData($surferIds, $fields) {
    $results = NULL;
    $isMultiples = is_array($surferIds);
    $isMultipleFields = is_array($fields);
    $surferIds = is_array($surferIds) ? $surferIds : array($surferIds);
    $numericMode = FALSE;
    if (is_numeric($fields)) {
      $numericMode = TRUE;
    }
    $fields = is_array($fields) ? $fields : array($fields);

    // get the values for all surfers and fields
    // if field or surfer do not exist, the result will be empty
    if ($numericMode) {
      $condition = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition(array('surfercontactdata_surferid' => $surferIds))
      );
      $sql = "SELECT sc.surfercontactdata_value,
                     sc.surfercontactdata_property,
                     sc.surfercontactdata_surferid,
                     d.surferdata_id,
                     d.surferdata_type
                FROM %s d INNER JOIN %s sc
                  ON d.surferdata_id = sc.surfercontactdata_property
               WHERE surfercontactdata_property = %d
                 AND ".$condition;
    } else {
      $condition = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition(
          array(
            'd.surferdata_name' => $fields,
            'cd.surfercontactdata_surferid' => $surferIds
          )
        )
      );
      $sql = "SELECT cd.surfercontactdata_value,
                     cd.surfercontactdata_surferid,
                     cd.surfercontactdata_property,
                     d.surferdata_name,
                     d.surferdata_id,
                     d.surferdata_type
                FROM %s d INNER JOIN %s cd
                  ON d.surferdata_id = cd.surfercontactdata_property
               WHERE ".$condition;
    }
    $sqlParams = array($this->tableData, $this->tableContactData);
    if ($numericMode) {
      $sqlParams[] = $fields[0];
    }
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      // fallback (compability): return only an array, if we are at this logical step
      $results = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($numericMode) {
          if ($row['surferdata_type'] == 'checkgroup') {
            $results[$row['surfercontactdata_surferid']] = unserialize(
              $row['surfercontactdata_value']
            );
          } else {
            $results[$row['surfercontactdata_surferid']] = $row['surfercontactdata_value'];
          }
        } else {
          if (!isset($results[$row['surfercontactdata_surferid']])) {
            $results[$row['surfercontactdata_surferid']] = array();
          }
          if ($row['surferdata_type'] == 'checkgroup') {
            $results[$row['surfercontactdata_surferid']][$row['surferdata_name']] =
              unserialize($row['surfercontactdata_value']);
          } else {
            $results[$row['surfercontactdata_surferid']][$row['surferdata_name']] =
              $row['surfercontactdata_value'];
          }
        }
      }
    }
    // behaviour for fallback (compability):
    // if surferIds not an array, return only the fields
    // if fields not an array, return only the value
    if (!$isMultiples && isset($results[$surferIds[0]])) {
      if ($isMultipleFields || $numericMode) {
        return $results[$surferIds[0]];
      } elseif (isset($results[$surferIds[0]][$fields[0]])) {
        return $results[$surferIds[0]][$fields[0]];
      }
    } elseif ($isMultiples) {
      return $results;
    }
    return NULL;
  }

  /**
  * Get amount of dynamic data fields with contents.
  *
  * @access public
  * @param mixed array|string $surferIds Unique 32-char surfer id
  * @param mixed array|string $fields Dynamic data field names
  * @return mixed array|integer|NULL Data count(s) or empty
  */
  function getDynamicDataCount($surferIds, $fields) {
    $isMultipleSurfers = is_array($surferIds);
    // $surferIds = is_array($surferIds) ? $surferIds : array($surferIds);
    $condition = str_replace(
      '%',
      '%%',
      $this->databaseGetSQLCondition(
        array(
          'cd.surfercontactdata_surferid' => $surferIds,
          'd.surferdata_name' => $fields
        )
      )
    );
    $sql = "SELECT cd.surfercontactdata_surferid, COUNT(*) AS num
              FROM %s AS cd, %s AS d
             WHERE cd.surfercontactdata_property = d.surferdata_id
                   AND cd.surfercontactdata_value != ''
               AND $condition
             GROUP BY cd.surfercontactdata_surferid";
    $sqlParams = array($this->tableContactData, $this->tableData);
    $amounts = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $amounts[$row['surfercontactdata_surferid']] = $row['num'];
      }
    }
    if (empty($amounts)) {
      return NULL;
    }
    if ($isMultipleSurfers) {
      return $amounts;
    }
    return $amounts[$surferIds];
  }

  /**
  * Delete dynamic profile data field.
  *
  * Deletes the value of a dynamic profile data field for a specific surfer.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $fieldName Dynamic data field name
  * @return mixed result of database operation or FALSE
  */
  function deleteDynamicDataField($surferId, $fieldName) {
    // Get field id
    $sql = "SELECT surferdata_id, surferdata_name
              FROM %s
             WHERE surferdata_name = '%s'";
    $sqlParams = array($this->tableData, $fieldName);
    $fieldId = NULL;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fieldId = $row['surferdata_id'];
      }
    }
    if (!$fieldId) {
      return FALSE;
    }
    // We've got a field id, so delete the record
    // Create condition array first
    $conditions = array(
      'surfercontactdata_property' => $fieldId,
      'surfercontactdata_surferid' => $surferId
    );
    $success = $this->databaseDeleteRecord($this->tableContactData, $conditions);
    return $success;
  }

  /**
  * Delete dynamic profile data field(s).
  *
  * Deletes the value(s) of one or more dynamic profile data fields for a specific surfer.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param mixed string|array $fields Dynamic data field name(s)
  * @return boolean TRUE on success, FALSE otherwise
  */
  function deleteDynamicData($surferId, $fields) {
    // If $fields is a string, we can simply delegate the
    // job to a single deleteDynamicDataField() call
    if (!is_array($fields)) {
      $success = $this->deleteDynamicDataField($surferId, $fields);
    } else {
      // Get each field, send its value to deleteDynamicDataField()
      $success = TRUE;
      foreach ($fields as $fieldName) {
        $partialSuccess = $this->deleteDynamicDataField($surferId, $fieldName);
        if (FALSE === $partialSuccess) {
          $success = FALSE;
        }
      }
    }
    return $success;
  }

  /**
  * Set dynamic profile data field.
  *
  * Sets the value of a specific dynamic profile field,
  * referred to by its unique internal name.
  *
  * @deprecated 21.01.2008 you have to use setDynamicData instead of this method!
  * @param string $surferId
  * @param string $fieldName
  * @param string $value Dynamic data value
  * @return boolean Status
  */
  function setDynamicDataField($surferId, $fieldName, $value) {
    return (1 == $this->setDynamicData($surferId, $fieldName, $value));
  }

  /**
  * Set dynamic profile data
  *
  * Sets values of specific dynamic profile field(s),
  * referred to by unique internal name(s)
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param mixed string|array $fields Dynamic data field name(s)
  * @param mixed string|NULL $value Dynamic data field value(s)
  * @return integer Count of added entries
  */
  function setDynamicData($surferId, $fields, $value = NULL) {
    $counted = 0;
    $fieldIds = array();
    $surferHasFields = array();
    $fields = is_array($fields) ? $fields : array($fields => $value);

    // get the data-ids for all affected data
    // if surfer not exist, the result will be NULL
    // if field not exist, it will be empty
    $condition = str_replace(
      '%',
      '%%',
      $this->databaseGetSQLCondition(
        array(
          'd.surferdata_name' => array_keys($fields),
          'cd.surfercontactdata_surferid' => $surferId
        )
      )
    );
    $sql = "SELECT d.surferdata_id,
                   d.surferdata_name,
                   cd.surfercontactdata_id,
                   cd.surfercontactdata_surferid
              FROM %s d
         LEFT JOIN %s cd ON d.surferdata_id = cd.surfercontactdata_property
               AND ".$condition;
    $sqlParams = array($this->tableData, $this->tableContactData);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fieldIds[$row['surferdata_name']] =
          array('id' => $row['surferdata_id'], 'relId' => $row['surfercontactdata_id']);
      }
    }

    // foreach required field, look up the selected ones
    // when it is valid => update, otherwise => insert
    foreach ($fields as $field => $value) {
      // Empty values must be empty strings, not NULL
      if (NULL === $value) {
        $value = '';
      }
      if (isset($fieldIds[$field]) && $fieldIds[$field]['relId'] != NULL) {
        // It exists, so it needs to be replaced
        $this->databaseUpdateRecord(
          $this->tableContactData,
          array('surfercontactdata_value' => $value),
          array('surfercontactdata_id' => $fieldIds[$field]['relId'])
        );
        $counted++;
      } elseif (isset($fieldIds[$field])) {
        // It doesn't exist yet, so insert it
        $data = array('surfercontactdata_value' => $value,
                      'surfercontactdata_property' => $fieldIds[$field]['id'],
                      'surfercontactdata_surferid' => $surferId);
        $this->databaseInsertRecord(
          $this->tableContactData, 'surfercontactdata_id', $data
        );
        $counted++;
      }
    }
    // Set surfer's last modified date
    if ($counted > 0) {
      $data = array('surfer_lastmodified' => time());
      $this->databaseUpdateRecord($this->tableSurfer, $data, 'surfer_id', $surferId);
    }
    return $counted;
  }

  /**
  * Check dynamic data.
  *
  * @access public
  * @param mixed string|array $fields Dynamic data field name(s)
  * @param mixed string|NULL $value Dynamic data field value
  * @return mixed boolean|array Checked status result(s)
  */
  function checkDynamicData($fields, $value = NULL) {
    // Import and instantiate sys_checkit
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');
    $checkit = new checkit();
    // Get an array of lowerstring check function names
    $checkFunctions = get_class_methods('checkit');
    foreach ($checkFunctions as $idx=>$functionName) {
      $checkFunctions[$idx] = strtolower($functionName);
    }
    // Create the SQL condition to check the field name(s)
    if (is_array($fields)) {
      $list = '';
      foreach ($fields as $fieldName => $irrelevant) {
        if ($list != '') {
          $list .= ', ';
        }
        $list .= sprintf("'%s'", $fieldName);
      }
      $condition = sprintf(" surferdata_name IN (%s)", $list);
    } else {
      $condition = sprintf(" surferdata_name = '%s'", $fields);
    }
    $sql = "SELECT surferdata_name,
                   surferdata_mandatory,
                   surferdata_check
              FROM %s
             WHERE ".$condition."
               AND surferdata_available = 1";
    $sqlParams = array($this->tableData);
    $fieldChecks = array();
    $fieldResults = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fieldChecks[$row['surferdata_name']] = array(
          'mandatory' => $row['surferdata_mandatory'],
          'check' => strtolower($row['surferdata_check'])
        );
        // If the user asked for a single field, set $value as value,
        // otherwise use the appropriate values of $fields
        if (is_array($fields)) {
          $fieldChecks[$row['surferdata_name']]['value'] =
            $fields[$row['surferdata_name']];
        } else {
          $fieldChecks[$row['surferdata_name']]['value'] = $value;
        }
        $fieldResults[$row['surferdata_name']] = NULL;
      }
    }
    // If there are no valid fields, return FALSE
    // TO DO: When PHP 4 compatibility is no longer necessary,
    // we should throw an exception instead
    if (empty($fieldChecks)) {
      return FALSE;
    }
    // Assume that all input is correct
    // Now check all fields against the appropriate checkit function
    foreach ($fieldResults as $fieldName => $irrelevant) {
      // Get the value
      $val = $fieldChecks[$fieldName]['value'];
      // First of all, check whether there is a value for this field
      if ($val === NULL || trim($val) === '') {
        // Check whether the field is mandatory
        if ($fieldChecks[$fieldName]['mandatory'] != 0) {
          // The field is mandatory and it is unset, so the result is FALSE
          $fieldResults[$fieldName] = FALSE;
        } else {
          // An unmandatory and empty field cannot be against any further rule => TRUE
          $fieldResults[$fieldName] = TRUE;
        }
      } else {
        // Get the check function to call for that field
        $checkFunction = $fieldChecks[$fieldName]['check'];
        // Check whether this is an official check function
        if (in_array($checkFunction, $checkFunctions)) {
          $fResult = $checkit->$checkFunction($val);
          // If field is invalid, set value for this field to FALSE
          // and add error message
          if ($fResult) {
            $fieldResults[$fieldName] = TRUE;
          } else {
            $fieldResults[$fieldName] = FALSE;
          }
        } else {
          // This is not a function but a regexp,
          // so check it manually
          if (!preg_match($checkFunction, $val)) {
            $fieldResults[$fieldName] = TRUE;
          } else {
            $fieldResults[$fieldName] = FALSE;
          }
        }
      }
    }
    // If the user asked for multiple fields, return the whole array
    if (is_array($fields)) {
      return $fieldResults;
    }
    // The user only asked for a single field, so return its result
    return $fieldResults[$fields];
  }

  /**
  * Build XML for multivalued dynamic data edit fields.
  *
  * The only argument is an array with value => captions arrays.
  * The result is a well-formed XML tree to be stored in the database.
  *
  * @access public
  * @param array $valueData Data array to build from
  * @return mixed FALSE|string XML
  */
  function buildFormValueXML($valueData) {
    // Check the array
    if (empty($valueData)) {
      return FALSE;
    }
    foreach ($valueData as $val => $captions) {
      if (!is_array($captions)) {
        return FALSE;
      }
    }
    // Array seems to be okay, build the XML tree
    $xml = '<options>';
    foreach ($valueData as $val => $captions) {
      $xml .= '<value>';
      $xml .= sprintf('<content>%s</content>', $val);
      if (!empty($captions)) {
        $xml .= '<captions>';
        foreach ($captions as $lang => $caption) {
          $xml .= sprintf('<%1$s>%2$s</%1$s>', $lang, $caption);
        }
        $xml .= '</captions>';
      }
      $xml .= '</value>';
    }
    $xml .= '</options>';
    return $xml;
  }

  /**
  * Parse XML for multivalued dynamic data edit fields.
  *
  * The first argument is an XML string,
  * the second one (optional) is a content language id;
  * if you set the third one (optional as well) to TRUE,
  * you will get an associative array of associative arrays with
  * language => caption pairs for each field, suitable
  * for backend operations.
  *
  * The return value is an associative array of
  * field value => caption pairs, suitable for
  * base_dialog's radio, checkgroup, and combo fields.
  *
  * If no content language is provided or if
  * no captions are available in the desired content language,
  * English will be used; if this is not available either,
  * the values themselves will be used as fallback captions.
  *
  * @access public
  * @param string $xml XML input
  * @param integer $lng Language id
  * @param boolean $complete Get node names as keys?
  * @return array $values Result values or nodes and values
  */
  function parseFormValueXML($xmlString, $lng = 0, $complete = FALSE) {
    $values = array();
    // Get the appropriate language name
    if ($lng > 0) {
      $lngName = $this->getLanguageTitle($lng);
    } else {
      $lngName = 'en-US';
    }
    // Try to create an XML tree and check whether it's valid
    $xml = simple_xmltree::createFromXML($xmlString, $this);
    if (!($xml && isset($xml->documentElement))) {
      return $values;
    }
    // Formally, everything is okay, so start parsing the XML tree
    $doc = $xml->documentElement;
    if (!$doc->hasChildNodes()) {
      return $values;
    }
    for ($i = 0; $i < $doc->childNodes->length; $i++) {
      unset($name, $caption);
      $val = $doc->childNodes->item($i);
      if ($val->nodeType == XML_ELEMENT_NODE &&
          $val->nodeName == 'value' && $val->hasChildNodes()) {
        for ($j = 0; $j < $val->childNodes->length; $j++) {
          $node = $val->childNodes->item($j);
          if ($node->nodeType == XML_ELEMENT_NODE) {
            $childrenFound = FALSE;
            if ($node->nodeName == 'content' && $node->hasChildNodes()) {
              $contentNode = &$node->childNodes->item(0);
              if ($contentNode->nodeType == XML_TEXT_NODE) {
                $childrenFound = TRUE;
                $nameNode = $node->childNodes->item(0);
                $name = $nameNode->valueOf();
              }
            }
            if (!$childrenFound &&
                $node->nodeName == 'captions' && $node->hasChildNodes()) {
              for ($k = 0; $k < $node->childNodes->length; $k++) {
                $subNode = $node->childNodes->item($k);
                if ($subNode->nodeType == XML_ELEMENT_NODE && $subNode->hasChildNodes()) {
                  $captionNode = $subNode->childNodes->item(0);
                  if ($captionNode->nodeType == XML_TEXT_NODE) {
                    if ($complete) {
                      if (!isset($caption)) {
                        $caption = array();
                      }
                      $caption[$subNode->nodeName] = $captionNode->valueOf();
                    } elseif ($subNode->nodeName == $lngName) {
                      $caption = $captionNode->valueOf();
                    }
                  }
                }
              }
            }
          }
        }
      }
      if (isset($name)) {
        if (!isset($caption)) {
          $caption = $name;
        }
        $values[$name] = $caption;
      }
    }
    return $values;
  }

  /**
  * Get dynamic data edit fields.
  *
  * Returns a base_dialog-style array of the desired dynamic data fields.
  *
  * The first argument is either a category id, a single field name,
  * an array of ids, an array of field names, or 0/NULL to get all fields.
  * The second (optional) argument is a prefix which is prepended to the field names
  * (separated by an underscore) to tell them apart from other fields in your form.
  * The third (optional) argument is a content language id for the captions --
  * if you do not provide one, or if titles are not availabe in the desired language,
  * the pure field names will be used as captions.
  *
  * @access public
  * @param mixed NULL|int|string|array $fields Field names
  * @param string $prefix Prefix for field names
  * @param integer $lng Language id
  * @return array $dynamicEditFields Fields configuration
  */
  function getDynamicEditFields($fields, $prefix = '', $lng = 0, $getClasses = FALSE) {
    // Create field condition
    $fieldCondition = '';
    if (is_numeric($fields) && $fields > 0) {
      $fieldCondition = sprintf(" d.surferdata_class = %d", $fields);
    } elseif (is_string($fields) && $fields != '0') {
      $fieldCondition = sprintf(" d.surferdata_name = '%s'", $fields);
    } elseif (is_array($fields)) {
      // Check whether we have got an array of categories (numeric) or of names
      $numeric = TRUE;
      foreach ($fields as $field) {
        if (!is_numeric($field)) {
          $numeric = FALSE;
        }
      }
      if ($numeric) {
        $fieldCondition = $this->databaseGetSQLCondition('d.surferdata_class', $fields);
      } else {
        $fieldCondition = $this->databaseGetSQLCondition('d.surferdata_name', $fields);
      }
    }
    if ($fieldCondition != '') {
      $fieldCondition = " AND ".$fieldCondition;
    }

    if ($getClasses = TRUE) {
      $fieldSurferDataClassTitle = ", ct.surferdataclasstitle_name";
      $joinSurferDataClassTitles = sprintf(
        "JOIN %s AS ct ON (ct.surferdataclasstitle_classid = d.surferdata_class ".
        "AND ct.surferdataclasstitle_lang = %d)",
        $this->tableDataClassTitles,
        $lng
      );
      $orderBySurferDataClassTitle = "ct.surferdataclasstitle_name, ";
    } else {
      $fieldSurferDataClassTitle = '';
      $joinSurferDataClassTitles = '';
      $orderBySurferDataClassTitle = '';
    }

    $sql = "SELECT d.surferdata_id, d.surferdata_name, d.surferdata_type,
                   d.surferdata_values, d.surferdata_check, d.surferdata_class,
                   d.surferdata_available, d.surferdata_mandatory, d.surferdata_order%s
              FROM %s AS d
              %s
             WHERE d.surferdata_available = 1".
                   $fieldCondition."
          ORDER BY %sd.surferdata_order, d.surferdata_name";
    $sqlParams = array(
      $fieldSurferDataClassTitle, $this->tableData, $joinSurferDataClassTitles, $orderBySurferDataClassTitle
    );
    $fields = array();

    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fields[] = $row;
      }
    }
    if (empty($fields)) {
      return $fields;
    }
    // Now try to get the localized field names, if desired
    if ($lng > 0) {
      $sql = "SELECT dt.surferdatatitle_title, dt.surferdatatitle_lang,
                     dt.surferdatatitle_field, d.surferdata_id
                FROM %s AS d LEFT OUTER JOIN %s AS dt
                  ON dt.surferdatatitle_field = d.surferdata_id
               WHERE dt.surferdatatitle_lang = '%d'";
      $sqlParams = array($this->tableData, $this->tableDataTitles, $lng);
      $titles = array();
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $titles[$row['surferdata_id']] = $row['surferdatatitle_title'];
        }
      }
    }

    // Create the edit fields
    $dynamicEditFields = array();
    $lastClassTitle = NULL;
    foreach ($fields as $field) {
      if ($getClasses == TRUE && $field['surferdataclasstitle_name'] != $lastClassTitle) {
        $dynamicEditFields[] = $field['surferdataclasstitle_name'];
        $lastClassTitle = $field['surferdataclasstitle_name'];
      }
      $fieldName = $field['surferdata_name'];
      if ($prefix != '') {
        $fieldName = $prefix.'_'.$fieldName;
      }
      if (isset($titles) && isset($titles[$field['surferdata_id']])) {
        $title = $titles[$field['surferdata_id']];
      } else {
        $title = $field['surferdata_name'];
      }
      // Get values according to field type
      if (in_array($field['surferdata_type'], array('combo', 'radio', 'checkgroup'))) {
        $values = $this->parseFormValueXML($field['surferdata_values'], $lng);
      } else {
        $values = $field['surferdata_values'];
      }
      $dynamicEditFields[$fieldName] = array(
        $title,
        $field['surferdata_check'],
        $field['surferdata_mandatory'],
        $field['surferdata_type'],
        $values
      );
    }
    return $dynamicEditFields;
  }

  /**
  * Get data field names.
  *
  * Returns all data field names (or those for a certain category, if provided)
  * in order to call getDynamicData() and the like for all fields
  * or for a certain category.
  *
  * @access public
  * @param integer|array $class Surfer data class filter value
  * @return array $fieldNames Data field names
  */
  function getDataFieldNames($class = 0) {
    $sql = "SELECT surferdata_name, surferdata_class
              FROM %s";
    if (is_array($class) || $class > 0) {
      $sql .= " WHERE ".$this->databaseGetSQLCondition('surferdata_class', $class);
    }
    $sqlParams = array($this->tableData, $class);
    $fieldNames = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fieldNames[] = $row['surferdata_name'];
      }
    }
    return $fieldNames;
  }

  /**
  * Find surfers by dynamic data.
  *
  * Returns an array of surfer ids that match your search criteria.
  *
  * The first argument, $criteria, is an array in field => pattern format.
  * The fields can be either numeric or field ids;
  * the patterns may contain LIKE-style wildcards
  *
  * The second argument, $mode, is either AND (default)
  * or OR and determines whether all or just any of the criteria should match.
  *
  * @access public
  * @param array $criteria Filter criteria for dynamic data
  * @param string $mode Mode to combine results (AND|OR)
  * @param array $totalResult Combined result
  */
  function findSurfersByDynamicData($criteria, $mode = 'AND') {
    // Check mode (set to 'AND' if it's not 'OR')
    if ($mode != 'OR') {
      $mode = 'AND';
    }
    // Check whether field names or ids were used
    $fieldKeys = array_keys($criteria);
    $ids = TRUE;
    foreach ($fieldKeys as $key) {
      if (!is_int($key)) {
        $ids = FALSE;
      }
    }
    if ($ids) {
      $fields = $criteria;
    } else {
      // For field names, we need to get the dynamic data field ids
      $nameCond = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('surferdata_name', array_keys($criteria))
      );
      $sql = "SELECT surferdata_name, surferdata_id
              FROM %s
              WHERE ".$nameCond;
      $sqlParams = array($this->tableData);
      $fields = array();
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $fields[$row['surferdata_id']] = $criteria[$row['surferdata_name']];
        }
      }
    }
    // Now get the surfers that match each of the criteria
    $totalResult = array();
    foreach ($fields as $id => $value) {
      $sql = "SELECT surfercontactdata_surferid,
                     surfercontactdata_property,
                     surfercontactdata_value
                FROM %s
               WHERE surfercontactdata_property = %d
                 AND surfercontactdata_value LIKE '%s'";
      $sqlParams = array($this->tableContactData, $id, $value);
      $result = array();
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[] = $row['surfercontactdata_surferid'];
        }
      }
      // Check whether there is a current result
      if (!empty($result)) {
        if (empty($totalResult)) {
          // If there is no combined result yet, create it
          $totalResult = $result;
        } else {
          // Otherwise combine the results according to mode
          if ($mode == 'AND') {
            $totalResult = array_intersect($totalResult, $result);
          } else {
            $totalResult = array_unique(array_merge($totalResult, $result));
          }
        }
      } else {
        // If the current result is empty,
        // we can safely return an empty array in AND mode
        if ($mode == 'AND') {
          return array();
        }
      }
    }
    return $totalResult;
  }

  /**
  * Get search form for surfers by basic and dynamic profile data.
  *
  * @access public
  * @param object xsl_layout &$layout Papaya layout object
  * @param string $paramName Parameter group name
  * @param mixed NULL|array $hidden Hidden formular values
  * @param string $title form title
  */
  function getSearchDynamicForm(&$layout, $paramName = '', $hidden = NULL, $title = '') {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    // If no param name was provided, use the current module's
    if (trim($paramName) == '') {
      $paramName = $this->paramName;
    }
    // If no hidden fields were provided, define the defaults
    // that are only useful for community management
    if ($hidden == NULL) {
      $hidden = array(
        'mode' => 0,
        'cmd' => 'find_surfers'
      );
    }
    $fields = array(
      'searchmode' => array(
        'Search mode',
        'isNoHTML',
        TRUE,
        'radio',
        array(
          'AND' => $this->_gt('AND (match all)'),
          'OR' => $this->_gt('OR (match any)')
        ),
        '',
        'AND'
      ),
      'Basic data',
      'static_surfer_handle' => array('Username', 'isNoHTML', FALSE, 'input', 50),
      'static_surfer_givenname' => array('Givenname', 'isNoHTML', FALSE, 'input', 50),
      'static_surfer_surname' => array('Surname', 'isNoHTML', FALSE, 'input', 50),
      'static_surfer_email' => array('Email', 'isNoHTML', FALSE, 'input', 50),
      'static_surfer_gender' => array(
        'Gender',
        'isNoHTML',
        FALSE,
        'combo',
        array(
          '' => $this->_gt('[any]'),
          'f' => $this->_gt('female'),
          'm' => $this->_gt('male'),
        )
      ),
      'static_surfergroup_id' => array(
        'Group',
        'isNum',
        FALSE,
        'function',
        'groupCallback'
      ),
      'static_surfer_valid' => array(
        'Status',
        'isNum',
        FALSE,
        'combo',
        array(
          -1 => $this->_gt('[any]'),
          0 => $this->_gt('Created'),
          2 => $this->_gt('Confirmed'),
          1 => $this->_gt('Valid'),
          3 => $this->_gt('Blocked')
        ),
        '',
        -1
      )
    );
    // Add text input fields for each available dynamic data field
    $sql = "SELECT surferdata_id, surferdata_name
              FROM %s
             WHERE surferdata_available = 1";
    $sqlParams = $this->tableData;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      $fields[] = 'Profile data';
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fields['search_'.$row['surferdata_id']] = array(
          $row['surferdata_name'],
          'isNoHTML',
          FALSE,
          'input',
          50,
          ''
        );
      }
    }
    $data = array();
    $searchDialog = new base_dialog(
      $this, $paramName, $fields, $data, $hidden
    );
    if (trim($title) == '') {
      $title = 'Search surfers';
    }
    $title = $this->_gt($title);
    $searchDialog->dialogTitle = $title;
    $searchDialog->baseLink = $this->baseLink;
    $searchDialog->msgs = &$this->msgs;
    $searchDialog->buttonTitle = $this->_gt('Search');
    $searchDialog->loadParams();
    $layout->add($searchDialog->getDialogXML());
  }

  /**
  * Show surfer search results.
  *
  * If the $links argument is set to TRUE, each of the surfer
  * names will be a link that will only be useful within the
  * community management; additionally, an email link for each
  * surfer will be displayed.
  * Set $links to FALSE if you just want a plain vanilla list
  * (the surfers_connector's caller for this method
  *  leaves out this argument, setting it to a hard-coded FALSE).
  *
  * If you call this method using the surfers_connector
  * to use it within another module, you need to pass in
  * a reference to its $this->params property as the next argument.
  *
  * If you set the last parameter, $returnResults, to TRUE,
  * the method will return the result array of surfer ids.
  *
  * @access public
  * @param object xsl_layout &$layout Papaya layout object
  * @param boolean $links Show links
  * @param mixed NULL|array $params Additional parameters
  * @param boolean $returnResults Return values or not
  * @param array $result Surfer results or empty
  */
  function showSurferResults(&$layout, $links = TRUE, $params = NULL, $returnResults = FALSE) {
    // If no params are set, use the current ones
    if ($params == NULL) {
      $params = $this->params;
    }
    // Search mode, default 'AND'
    $searchMode = 'AND';
    if (isset($params['searchmode']) && $params['searchmode'] == 'OR') {
      $searchMode = 'OR';
    }
    // Search criteria ($condition => static; $criteria => dynamic)
    $fieldList = 'surfer_id';
    $condition = '';
    $criteria = array();
    if (isset($params)) {
      // Valid static fields
      $staticFields = array('static_surfer_handle',
                            'static_surfer_givenname',
                            'static_surfer_surname',
                            'static_surfer_email',
                            'static_surfer_gender',
                            'static_surfergroup_id',
                            'static_surfer_valid'
                           );
      // Wildcards to replace
      $sourceWildcards = array('*', '?');
      $destWildcards = array('%', '_');
      foreach ($params as $field => $value) {
        if (preg_match('~^search_(\d+)$~', $field, $matches) && trim($value) != '') {
          $fieldNum = $matches[1];
          $value = addslashes($value);
          $value = str_replace($sourceWildcards, $destWildcards, $value);
          $criteria[$fieldNum] = $value;
        } elseif (in_array($field, $staticFields)) {
          if (($field == 'static_surfer_valid' && $value != -1) ||
              ($field != 'static_surfer_valid' && $value != '')) {
            $col = preg_replace('~^static_~', '', $field);
            $fieldList .= ', '.$col;
            if ($condition != '') {
              $condition .= ' '.$searchMode.' ';
            }
            $value = addslashes($value);
            $value = str_replace($sourceWildcards, $destWildcards, $value);
            $condition .= sprintf("%s LIKE '%s'", $col, $value);
          }
        }
      }
    }
    if (trim($condition == '') && empty($criteria)) {
      // Just output a message if there are no criteria
      $this->addMsg(MSG_WARNING, $this->_gt('Please enter search criteria'));
      return NULL;
    } else {
      // Otherwise get and output the result
      // Static data first
      $searchStatic = FALSE;
      $staticResult = array();
      if (trim($condition) != '') {
        $searchStatic = TRUE;
        $sql = "SELECT %s
                  FROM %s
                 WHERE ".str_replace('%', '%%', $condition);
        $sqlParams = array($fieldList, $this->tableSurfer);
        if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $staticResult[] = $row['surfer_id'];
          }
        }
      }
      // In AND mode, we already know that there is no match if this query is empty
      if ($searchMode == 'AND' && $searchStatic == TRUE && empty($staticResult)) {
        $this->addMsg(MSG_INFO, $this->_gt('No surfers match your criteria.'));
        return NULL;
      }
      // Now go for the dynamic data
      $searchDynamic = FALSE;
      $dynamicResult = array();
      if (!empty($criteria)) {
        $searchDynamic = TRUE;
        $dynamicResult = $this->findSurfersByDynamicData($criteria, $searchMode);
      }
      // Combine the results according to search mode
      if ($searchMode == 'AND') {
        if ($searchStatic == TRUE) {
          if ($searchDynamic == TRUE) {
            $result = array_intersect($staticResult, $dynamicResult);
          } else {
            $result = $staticResult;
          }
        } else {
          $result = $dynamicResult;
        }
      } else {
        $result = array_unique(array_merge($staticResult, $dynamicResult));
      }
      if (empty($result)) {
        // If no surfers match, output a message
        $this->addMsg(MSG_INFO, $this->_gt('No surfers match your criteria.'));
        return NULL;
      }
      // Otherwise show the result
      $names = $this->loadSurferNames($result);
      $output = sprintf('<listview title="%s">', $this->_gt('Search results'));
      $output .= '<cols>';
      $output .= sprintf('<col>%s</col>', $this->_gt('Surfer'));
      if ($links) {
        $output .= '<col/>';
      }
      $output .= '</cols>';
      $output .= '<items>';
      foreach ($names as $id => $data) {
        $realNameStr = ($data['surfer_givenname'] != '' || $data['surfer_surname'] != '') ?
          sprintf('(%s %s)', $data['surfer_givenname'], $data['surfer_surname']) : '';
        $href = $this->getLink(
          array('mode' => 0, 'id' => $id)
        );
        $link = ($links == TRUE) ? sprintf(' href="%s"', $href) : '';
        $output .= sprintf(
          '<listitem title="%s %s" %s>',
          $data['surfer_handle'],
          $realNameStr,
          $link
        );
        if ($links) {
          $output .= '<subitem align="right">';
          if ($data['surfer_email'] != '') {
            $output .= sprintf(
              '<a href="%s"><glyph src="%s" alt="%s" /></a>'.LF,
              'mailto:'.$data['surfer_email'],
              $this->images['items-mail'],
              $this->_gt('Send email to user using your email client.')
            );
          }
          $output .= '</subitem>';
        }
        $output .= '</listitem>';
      }
      $output .= '</items>';
      $output .= '</listview>';
      $layout->add($output);
      // If we were asked for results, return them right here
      if ($returnResults) {
        return $result;
      }
    }
    return NULL;
  }

  /**
  * Get avatar image / thumbnail.
  *
  * Returns the thumbnail location of the specified surfer's avatar
  * If a surfer has no avatar, but the $useDefault parameter
  * is set to TRUE (default), the default avatar (by gender)
  * will be returned.
  *
  * The return value is an empty string if no avatar can be retrieved.
  *
  * @access public
  * @param array|string $surferIds Surfer ids to get avatar from
  * @param int $size Avatar size in pixels
  * @param boolean $useDefault Flag to use default avatar
  * @param string $mode Thumbnail resize mode
  * @return string Thumbnail location or empty
  */
  function getAvatar($surferIds, $size = 0, $useDefault = TRUE, $mode = 'max') {
    $results = array();
    $isMultiples = is_array($surferIds);
    $surferIds = is_array($surferIds) ? $surferIds : array($surferIds);
    $mode = in_array($mode, array('max', 'mincrop', 'abs')) ? $mode : 'max';

    // Use default size if size is unset
    if (!$size) {
      $size = $this->getProperty('AVATAR_DEFAULT_SIZE', AVATAR_DEFAULT_SIZE);
    }

    // first: for each surfer, look up cached avatars
    $haveToLoadAvatars = array();
    foreach ($surferIds as $surferId) {
      $key = $surferId.'-'.$size.'-'.$mode;
      // If the avatar for this id is in cache, use it
      if (isset($this->avatarCache[$key]) && $this->avatarCache[$key] != '') {
        $results[$surferId] = ($this->avatarCache[$key] == '[NONE]')
          ? '' : $this->avatarCache[$key];
      } else {
        // cached version doesn't exist, so mark
        $haveToLoadAvatars[] = $surferId;
      }
    }

    // second: for all non-cached avatars, build them
    $loadedAvatars = array();
    if (count($haveToLoadAvatars) > 0) {
      $avatarFemale = $this->getProperty('AVATAR_FEMALE');
      $avatarMale = $this->getProperty('AVATAR_MALE');
      $avatarGeneral = $this->getProperty('AVATAR_GENERAL');

      // so we've got to look up the avatar in db
      $condition = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('surfer_id', $haveToLoadAvatars)
      );
      $sql = "SELECT surfer_avatar, surfer_gender, surfer_id
                FROM %s
               WHERE surfer_valid != 4
                 AND " .$condition;
      $sqlData = array($this->tableSurfer);

      $loadedAvatars = array();
      if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          // If there is no avatar and we are to use the default,
          // retrieve it by gender
          if (!$row['surfer_avatar'] && $useDefault) {
            if ($row['surfer_gender'] == 'f') {
              $row['surfer_avatar'] = $avatarFemale;
            } elseif ($row['surfer_gender'] == 'm') {
              $row['surfer_avatar'] = $avatarMale;
            } else {
              $row['surfer_avatar'] = $avatarGeneral;
            }
          }
          $loadedAvatars[$row['surfer_id']] = $row['surfer_avatar'];
        }
      }
    }

    // third: after db record loop, creating thumbnails of new avatars
    // Create an image thumbnail for the avatar in the correct size
    include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
    $thumbnail = new base_thumbnail;
    // Generate an avatar for each surfer
    // If avatar is missing or not createable, generate a standard one
    // Each avatar will stored in internal cache
    $generalThumb = NULL;
    $maleThumb = NULL;
    $femaleThumb = NULL;

    foreach ($loadedAvatars as $surferId => $mediaId) {

      if ($mediaId == $avatarFemale) {
        if (is_null($femaleThumb)) {
          $femaleThumb = $thumbnail->getThumbnail($avatarFemale, NULL, $size, $size, $mode);
        }
        $thumb = $femaleThumb;
      } elseif ($mediaId == $avatarMale) {
        if (is_null($maleThumb)) {
          $maleThumb = $thumbnail->getThumbnail($avatarMale, NULL, $size, $size, $mode);
        }
        $thumb = $maleThumb;
      } elseif ($mediaId == $avatarGeneral) {
        if (is_null($generalThumb)) {
          $generalThumb = $thumbnail->getThumbnail($avatarGeneral, NULL, $size, $size, $mode);
        }
        $thumb = $generalThumb;
      } else {
        $thumb = $thumbnail->getThumbnail($mediaId, NULL, $size, $size, $mode);
        if (empty($thumb)) {
          if (is_null($generalThumb)) {
            $generalThumb = $thumbnail->getThumbnail($avatarGeneral, NULL, $size, $size, $mode);
          }
          $thumb = $generalThumb;
        }
      }

      if (!empty($thumb)) {
        $thumb = 'media.thumb.'.$thumb;
      }
      $this->avatarCache[$surferId.'-'.$size.'-'.$mode] = ($thumb == '') ? '[NONE]' : $thumb;
      $results[$surferId] = $thumb;
    }

    // behavior for fallback (compability):
    // if surferIds is not an array, return only the avatar
    // otherwise NULL
    if ($isMultiples) {
      return $results;
    } else {
      if (isset($results[$surferIds[0]])) {
        return $results[$surferIds[0]];
      }
    }
    return NULL;
  }

  /**
   * Get avatar media id.
   * Returns the media id of the specified surfer id(s).
   *
   * @see surfer_admin::getAvatar to get avatar image / thumbnail.
   * @param string|array $surferIds Single or multiple surfer id(s).
   * @return string|array Avatar media id(s)
   */
  function getAvatarId(&$surferIds) {
    $isMultiples = is_array($surferIds);
    $surferIds = is_array($surferIds) ? $surferIds : array($surferIds);

    // first: for each surfer, look up cached avatars
    $loadedAvatarIds = array();
    $haveToLoadAvatarIds = array();
    foreach ($surferIds as $surferId) {
      // $key = $surferId.'-'.$size.'-'.$mode;
      // If the avatar for this id is in cache, use it
      if (isset($this->avatarIdCache[$surferId]) && $this->avatarIdCache[$surferId] != '') {
        $loadedAvatarIds[$surferId] = ($this->avatarIdCache[$surferId] == NULL)
          ? '' : $this->avatarIdCache[$key];
      } else {
        // cached version doesn't exist, so mark
        $haveToLoadAvatarIds[] = $surferId;
      }
    }

    // second: for all non-cached avatars, build them
    if (count($haveToLoadAvatarIds) > 0) {

      // so we've got to look up the avatar id in db
      $condition = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('surfer_id', $haveToLoadAvatarIds)
      );
      $sql = "SELECT surfer_id, surfer_avatar
                FROM %s
               WHERE surfer_valid != 4
                 AND " .$condition;
      $sqlData = array($this->tableSurfer);

      if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $loadedAvatarIds[$row['surfer_id']] = $row['surfer_avatar'];
        }
      }
    }

    // behaviour for fallback (compability):
    // if surferIds not an array, return only the avatar otherwise NULL
    if ($isMultiples) {
      return $loadedAvatarIds;
    } else {
      if (isset($loadedAvatarIds[$surferIds[0]])) {
        return $loadedAvatarIds[$surferIds[0]];
      }
    }
    return NULL;
  }

  /**
  * Check whether a specific surfer has a linked editor (backend user) account
  *
  * @param string $surferId
  * @return boolean TRUE if editor user, FALSE otherwise
  */
  public function isEditor($surferId) {
    $result = FALSE;
    $sql = "SELECT auth_user_id, surfer_id
              FROM %s
             WHERE surfer_id = '%s'";
    $params = array($this->tableSurfer, $surferId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!empty($row['auth_user_id'])) {
          $result = TRUE;
        }
      }
    }
    return $result;
  }

  /**
  * Get global property from community management.
  *
  * Switched from a dedicated table to base_module_options.
  *
  * @access public
  * @param string $name Property name
  * @param mixed $default Default value
  * @return string $optionValue Property value
  */
  function getProperty($name, $default = '') {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');
    $optionValue = base_module_options::readOption($this->moduleGuid, $name, $default);
    return $optionValue;
  }

  /**
  * Set global property for community management.
  *
  * Switched from a dedicated table to base_module_options.
  *
  * @access public
  * @param string $name Property name
  * @param string $value Property value
  */
  function setProperty($name, $value) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');
    base_module_options::writeOption($this->moduleGuid, $name, $value);
  }

  /**
  * Is favorite surfer?
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @return boolean Status (is favourite)
  */
  function isFavoriteSurfer($surferId) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE favorite_surferid = '%s'";
    $sqlParams = array($this->tableFavorites, $surferId);
    $amount = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($count = $res->fetchField()) {
        $amount = $count;
      }
    }
    return ($amount > 0);
  }

  /**
  * Get favorite surfers.
  *
  * @access public
  * @param boolean $withHandles Flag to get surfers with handles
  * @return array $surfers Surfers result
  */
  function getFavoriteSurfers($withHandles = TRUE) {
    $sql = "SELECT favorite_surferid
              FROM %s";
    $sqlParams = array($this->tableFavorites);
    $surferIds = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($id = $res->fetchField()) {
        $surferIds[] = $id;
      }
    }
    // Return an empty array if there is no list,
    // or a plain array if we were not asked for handles
    if (empty($surferIds) || $withHandles == FALSE) {
      return $surferIds;
    }
    // Otherwise add the surfer handles
    $surfers = $this->getAnyHandleById($surferIds);
    return $surfers;
  }

  /**
  * Get default contact for surfers.
  *
  * @access public
  * @return mixed string|NULL Default surfer contact value
  */
  function getDefaultContact() {
    $defaultContact = NULL;
    $opt = $this->getProperty('DEFAULT_CONTACT', '');
    if ($opt != '') {
      $defaultContact = $opt;
    }
    return $defaultContact;
  }

  /**
  * Add surfer to favorites.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  */
  function addSurferToFavorites($surferId) {
    // If this surfer id already is a favorite, return
    if ($this->isFavoriteSurfer($surferId)) {
      return;
    }
    // Make up the data array
    $data = array(
      'favorite_surferid' => $surferId,
      'favorite_timestamp' => time()
    );
    // Get the amount of existing favorite surfers
    $sql = "SELECT COUNT(*)
              FROM %s";
    $sqlParams = array($this->tableFavorites);
    $amount = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($count = $res->fetchField()) {
        $amount = $count;
      }
    }
    // If we have reached the maximum amount, replace the oldest one
    $maxFavoriteSurfers = $this->getProperty('MAX_FAVORITE_SURFERS', 25);
    if ($amount >= $maxFavoriteSurfers) {
      $sql = "SELECT favorite_surferid, favorite_timestamp
                FROM %s
            ORDER BY favorite_timestamp";
      $sqlParams = array($this->tableFavorites);
      $replacedId = '';
      if ($res = $this->databaseQueryFmt($sql, $sqlParams, 1)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $replacedId = $row['favorite_surferid'];
        }
      }
      if ($replacedId != '') {
        $s = $this->databaseUpdateRecord(
          $this->tableFavorites, $data, 'favorite_surferid', $replacedId
        );
        return;
      }
    }
    // Otherwise, insert a new record
    $s = $this->databaseInsertRecord($this->tableFavorites, NULL, $data);
  }

  /**
  * Remove surfer from favorites.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  */
  function removeSurferFromFavorites($surferId = NULL) {
    $condition = NULL;
    if ($surferId != NULL) {
      $condition = array('favorite_surferid' => $surferId);
    }
    $this->databaseDeleteRecord($this->tableFavorites, $condition);
  }

  /**
   * Returns the amount of valid surfers currently active.
   *
   * @access public
   * @param integer $decay Time frame in seconds
   * @param integer $limit Limit for database results
   * @param integer $offset Offset for database results
   * @return array Active surfers data and absolute surfers count
   */
  function getLastActiveSurfers($decay, $limit, $offset = 0) {
    $this->lastActiveSurfers = array();
    $sql = "SELECT surfer_id, surfer_handle, surfer_givenname, surfer_surname,
                       surfer_email, surfer_lastlogin, surfer_lastaction,
                       surfer_gender, surfer_status FROM %s
                WHERE surfer_lastaction >= '%d'";
    $currTime = time();
    $params = array($this->tableSurfer, $currTime - $decay);

    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      // get datarecord
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
         // store to array
         $this->lastActiveSurfers[$row['surfer_id']] = $row;
      }
      return array($this->lastActiveSurfers, $res->absCount());
    }
    return array(NULL, 0);
  }

  /**
  * Get group callback xml.
  *
  * Callback function to get a surfer group formular with a combo selection field
  *
  * @param string $name Field name
  * @param array $element Field element configuration
  * @param mixed $data Current field data
  * @return string $result XML
  */
  function groupCallback($name, $element, $data) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      $this->paramName,
      $name
    );
    $selected = (!$data) ? ' selected="selected"' : '';
    $result .= sprintf(
      '<option value=""%s>%s</option>', $selected, $this->_gt('[any]')
    );
    $sql = "SELECT surfergroup_id, surfergroup_title
              FROM %s
          ORDER BY surfergroup_title ASC";
    $sqlData = array($this->tableGroups);
    if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $selected = ($data == $row['surfergroup_id']) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s</option>',
          $row['surfergroup_id'],
          $selected,
          $row['surfergroup_title']
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  /**
  * Get a surfer group id by its external identifier
  *
  * @param string $identifier
  * @return integer
  */
  function getGroupIdByIdentifier($identifier) {
    $result = 0;
    if ($identifier != '') {
      $sql = "SELECT surfergroup_id, surfergroup_identifier
                FROM %s
               WHERE surfergroup_identifier = '%s'";
      $sqlData = array($this->tableGroups, $identifier);
      if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result = $row['surfergroup_id'];
        }
      }
    }
    return $result;
  }

  /**
   * Load surfers with a more complex statement see table papaya_surfer for handling.
   *
   * @access public
   * @param array $filter Filter array for sql conditions
   * @param string $order Table field key identifier to sort by
   * @param string $by Sort direction ('ASC' or 'DESC')
   * @param integer limit Set limit for database results
   * @param integer $offset Set offset for database results
   * @return array $results List with complex surfers data
   */
  function loadSurfersComplex($filter, $order = NULL, $by = NULL,
                              $limit = NULL, $offset = NULL) {
    $results = array();
    $sql = 'SELECT  surfer_id,
                    surfer_handle,
                    surfer_givenname,
                    surfer_surname,
                    surfer_registration
              FROM  %s %s %s';

    $condition = str_replace('%', '%%', $this->databaseGetSQLCondition($filter));
    if ($condition) {
      $condition = 'WHERE ' . $condition;
    } else {
      $condition = '';
    }
    if ($order != NULL) {
      $by = ($by == NULL || $by != 'DESC') ? 'ASC' : 'DESC';
      $order = sprintf(' ORDER BY %s %s', $order, $by);
    } else {
      $order = '';
    }

    $params = array($this->tableSurfer, $condition, $order);
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $results[$row['surfer_id']] = $row;
      }

      $this->surfersAbsCount = $res->absCount();
    }

    return $results;
  }

  /**
   * Load the lastest registered surfers.
   *
   * @access public
   * @param integer $timeOffset Time offset
   * @param integer $limit Set limit for database results
   * @param integer $offset Set offset for database results
   * @return $results List with latest registered surfers data
   */
  function getLatestRegisteredSurfers($timeOffset = NULL, $limit = NULL, $offset = NULL) {
    $results = array();
    $sql = 'SELECT   surfer_id,
                    surfer_handle,
                    surfer_givenname,
                    surfer_surname,
                    surfer_registration
              FROM  %s
             WHERE   surfer_valid = 1 %s
             ORDER   BY surfer_registration DESC';

    $condition = ($timeOffset != NULL)
      ? ('AND surfer_registration >= '. (int)$timeOffset): '';

    $params = array($this->tableSurfer, $condition);
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $results[$row['surfer_id']] = $row;
      }
      $this->surfersAbsCount = $res->absCount();
    }
    return $results;
  }

  /**
  * Automatically create a surfer handle from a string.
  *
  * @param string $identifier Input handle identifier
  * @return string $currentHandle New handle
  */
  function generateHandle($identifier) {
    // Step 1: If $identifier is an email address, only use the part before the '@'
    if (strstr($identifier, '@') !== FALSE) {
      $identifier = substr($identifier, 0, strpos($identifier, '@'));
    }
    // Step 2: Replace one or more special chars by '-'
    $identifier = preg_replace('([^a-z\d._-]+)i', '-', $identifier);
    // Step 3: Replace more than one '-' by a single '-'
    $identifier = preg_replace('(\-{2,})', '-', $identifier);
    // Step 4: Shorten to 14 characters max.
    $identifier = substr($identifier, 0, 14);
    // Step 5: Add suffix if not unique
    $currentHandle = $identifier;
    $suffix = 0;
    while ($this->existHandle($currentHandle, TRUE)) {
      $suffix++;
      $currentHandle = $identifier.$suffix;
    }
    return $currentHandle;
  }

  /**
   * Get a link to manage a certain surfer in backend.
   *
   * Return NULL if the requested surfer id does not exist,
   * a valid link to the backend surfer management otherwise.
   *
   * @access public
   * @param string $surferId Unique 32-char surfer id
   * @return string $link Link to surfer
   */
  function getBackendSurferLink($surferId) {
    if (!$this->existID($surferId, TRUE)) {
      return NULL;
    }
    $link = sprintf(
      PAPAYA_PATHWEB_ADMIN.'module_edmodule_community.php?%1$s[mode]=0&%1$s[id]=%2$s',
      $this->paramName,
      $surferId
    );
    return $link;
  }

  /**
  * Load permission list
  *
  * Loads the full list of available permissions
  * Stores them in the permissionList attribute
  * Returns true if this is possible
  * or false if not
  *
  * @access public
  * @return boolean
  */
  function loadPermList() {
    $this->permissionsList = array();
    $sql = "SELECT surferperm_id, surferperm_title, surferperm_active
              FROM %s
             ORDER BY surferperm_title ASC, surferperm_id DESC";
    if ($res = $this->databaseQueryFmt($sql, array($this->tablePerm))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->permissionsList[$row['surferperm_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Get Permission combo
  *
  * Callback function that makes all active permissions
  * available as a select field
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @param string $paramName optional, default NULL
  * @access public
  * @return string XML
  */
  function getPermCombo($name, $field, $data, $paramName = NULL) {
    if ($paramName === NULL) {
      $paramName = $this->paramName;
    }
    // Reload permission list if not set
    if (!(isset($this->permissionsList) && is_array($this->permissionsList))) {
      $this->loadPermList();
    }
    // Create select field
    $result = sprintf(
      '<select id="field_type" name="%s[%s]" class="dialogSelect dialogScale" size="1">'.LF,
      papaya_strings::escapeHTMLChars($paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $result .= sprintf(
      '<option value="0">[%s]</option>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('globally allowed'))
    );
    // Iterate over all permissions
    foreach ($this->permissionsList as $perm) {
      // Use only active permissions
      if ($perm['surferperm_active']) {
        // Mark current permission as selected if it's the field value
        $selected = ($perm['surferperm_id'] == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($perm['surferperm_id']),
          $selected,
          papaya_strings::escapeHTMLChars($perm['surferperm_title'])
        );
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
   * Clear old registrations and change requests.
   *
   * Deletes any records from the surfer table
   * and the change request table whose expiration date
   * has passed longer than a given amount of seconds.
   *
   * @access public
   * @param integer $seconds Time frame
   * @return array amount of deleted registrations, amount of deleted change requests
   */
  function clearOldRegistrations($seconds) {
    // Create the timestamp to check for
    $timestamp = time() - $seconds;
    // Get the records to delete
    $sql = "SELECT s.surfer_id, max( r.surferchangerequest_expiry ) exp
              FROM %s s
        INNER JOIN %s r
           ON s.surfer_id = r.surferchangerequest_surferid
     GROUP BY r.surferchangerequest_surferid
       HAVING exp < %d";
    $sqlParams = array($this->tableSurfer, $this->tableChangeRequests, $timestamp);
    $surferIds = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $surferIds[] = $row['surfer_id'];
      }
    }
    if (!(empty($surferIds))) {
      $condition = str_replace(
        "%", "%%", $this->databaseGetSQLCondition('surfer_id', $surferIds)
      );
      $sql = "DELETE FROM %s
               WHERE ".$condition;
      $sqlParams = array($this->tableSurfer);
      $this->databaseQueryFmtWrite($sql, $sqlParams);
      $condition = str_replace(
        "%",
        "%%",
        $this->databaseGetSQLCondition('surferchangerequest_surferid', $surferIds)
      );
      $sql = "DELETE FROM %s
               WHERE ".$condition;
      $sqlParams = array($this->tableChangeRequests);
      $this->databaseQueryFmtWrite($sql, $sqlParams);
    }
    // Record amount of deleted surfer ids
    $numSurferIds = count($surferIds);
    // Now go for any old change requests
    $sql = "DELETE FROM %s
                  WHERE surferchangerequest_expiry < %d";
    $sqlParams = array($this->tableChangeRequests, $timestamp);
    $numChangeRequests = 0;
    if ($num = $this->databaseQueryFmtWrite($sql, $sqlParams)) {
      if ($num === TRUE || $num === FALSE) {
        $numChangeRequests = 0;
      } else {
        $numChangeRequests = $num;
      }
    }
    return array($numSurferIds, $numChangeRequests);
  }

  /**
   * Convert an like value from an sql compatible format to a generic format.
   * @param string $input Value input
   * @return string $result Value output
   */
  function convertFromSqlLikeValue($input) {
    $result = $input;

    $pattern1a = '/(?<!\\\)(%)/i';
    $result = preg_replace($pattern1a, '*', $result);
    $pattern1b = '/(?<!\\\)(\_)/i';
    $result = preg_replace($pattern1b, '?', $result);

    $pattern2a = '/(\\\%)/i';
    $result = preg_replace($pattern2a, '%', $result);
    $pattern2b = '/(\\\\_)/i';
    $result = preg_replace($pattern2b, '_', $result);

    return $result;
  }

  /**
   * Convert an like value to a sql compatible format to use it in in sql statements.
   * @param string $input Value input
   * @return string $result Value output
   */
  function convertToSqlLikeValue($input) {
    $result = $input;

    // escape special chars
    $replace_Step1 = array(
      '%' => '\%',
      '_' => '\_',
    );
    // replace chars given by surfer
    $replace_Step2 = array(
      '?' => '_',
      '*' => '%',
    );

    $result = strtr($result, $replace_Step1);
    $result = strtr($result, $replace_Step2);

    return $result;
  }
}
?>
