<?php
/**
* Surfer contact utilities
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
* @subpackage _Base-Community
* @version $Id: base_contacts.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Define some useful constants
* Contact types
*/
if (!defined('SURFERCONTACT_STATUS_BOTH')) {
  define('SURFERCONTACT_STATUS_BOTH', 0);
}
if (!defined('SURFERCONTACT_STATUS_PENDING')) {
  define('SURFERCONTACT_STATUS_PENDING', 1);
}
if (!defined('SURFERCONTACT_STATUS_ACCEPTED')) {
  define('SURFERCONTACT_STATUS_ACCEPTED', 2);
}

/**
* Define some more useful constants
* Result types for findContact()
* as bit values that can be ORed together
*/
if (!defined('SURFERCONTACT_NONE')) {
  define('SURFERCONTACT_NONE', 0);
}
if (!defined('SURFERCONTACT_DIRECT')) {
  define('SURFERCONTACT_DIRECT', 1);
}
if (!defined('SURFERCONTACT_PENDING')) {
  define('SURFERCONTACT_PENDING', 2);
}
if (!defined('SURFERCONTACT_INDIRECT')) {
  define('SURFERCONTACT_INDIRECT', 4);
}
if (!defined('SURFERCONTACT_OWNREQUEST')) {
  define('SURFERCONTACT_OWNREQUEST', 8);
}

/**
* Basic class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Surfer contact utitlities
*
* A set of methods to retrieve and manage contact data
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class contact_manager extends base_db {
  /**
  * Guid of the base_surfers module
  * @var string $surfersModuleGuid
  */
  var $surfersModuleGuid = '88236ef1454768e23787103f46d711c2';

  /**
  * Papaya database table surfer
  * @var string $tableSurfer
  */
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  /**
  * Papaya database table surfer contacts
  * @var string $tableContacts
  */
  var $tableContacts = PAPAYA_DB_TBL_SURFERCONTACTS;

  /**
  * Papaya database table surfer contact cache
  * @var string $tableContactCache
  */
  var $tableContactCache = PAPAYA_DB_TBL_SURFERCONTACTCACHE;

  /**
  * Current surfer id
  * @var string $surfer
  */
  var $surfer = '';

  /**
  * Contact path list
  * @var array $pathList
  */
  var $pathList = array();

  /**
  * List of all surfers in contact paths
  * @var array $surfersList
  */
  var $surfersList = array();

  /**
  * Maximum path length
  * @var int $maxPathLength
  */
  var $maxPathLength = 0;

  /**
  * Contact "cache" array
  * @var array $contactCache
  */
  var $contactCache = array();

  /**
  * Number of contacts "cache" array
  * @var array $contactNumCache
  */
  var $contactNumCache = array();

  /**
  * Number of contact requests "cache" array
  * @var array $contactRequestNumCache
  */
  var $contactRequestNumCache = array();

  /**
  * Contact status "cache" array
  * @var array $isContactCache
  */
  var $isContactCache = array();

  /**
  * The abs count (last query)
  * @param integer
  */
  var $absCount = 0;

  /** Constructor
  * Takes an optional surfer id and an optional
  * max path length as arguments
  * @param string $surferId
  * @param int $max maximum path length
  */
  function __construct($surferId = '', $max = 3) {
    $this->surfer = $surferId;
    $this->maxPathLength = $max;
  }

  /**
  * PHP 4 constructor
  */
  function contact_manager($surferId = '', $max = 3) {
    $this->__construct($surferId, $max);
  }

  /**
  * get Singleton instance of the contact manager
  */
  function getInstance($surferId = '', $max = 3) {
    static $contactManager;
    if (!(isset($contactManager) && is_object($contactManager))) {
      $contactManager = new contact_manager($surferId, $max);
    } else {
      if ($contactManager->surfer != $surferId) {
        $contactManager->surfer = $surferId;
      }
      if ($contactManager->maxPathLength != $max) {
        $contactManager->maxPathLength = $max;
      }
    }
    return $contactManager;
  }

  /**
  * Internal helper method to get all accepted contacts
  *
  * As accepted contacts are now stored bidirectionally,
  * they can no longer be fetched together with the
  * contact requests that are only stored for their
  * direction.
  * So the getContacts( ) method needs to call
  * this helper method, the helper to get contact requests,
  * or both.
  * @param string $surferId (optional)
  * @return array
  */
  function _getAcceptedContacts($surferId) {
    if ($surferId == '') {
      $surferId = $this->surfer;
    }
    $sql = "SELECT surfercontact_requestor,
                   surfercontact_requested,
                   surfercontact_status
              FROM %s
             WHERE surfercontact_requestor = '%s'
               AND surfercontact_status = %d";
    $sqlParams = array($this->tableContacts, $surferId,
      SURFERCONTACT_STATUS_ACCEPTED
    );
    $contacts = array();
    $dbg = '';
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $contacts[] = $row['surfercontact_requested'];
        $dbg .= '.';
      }
    }
    return $contacts;
  }

  /**
  * Internal helper method to get all contact requests from or to a surfer
  *
  * As accepted contacts are now stored bidirectionally,
  * they can no longer be fetched together with the
  * contact requests that are only stored for their
  * direction.
  * So the getContacts( ) method needs to call
  * this helper method, the helper to get the accepted contacts,
  * or both.
  * @param string $surferId
  * @return array
  */
  function _getContactRequests($surferId) {
    if ($surferId == '') {
      $surferId = $this->surfer;
    }
    $sql = "SELECT surfercontact_requestor,
                   surfercontact_requested,
                   surfercontact_status
              FROM %s
             WHERE (surfercontact_requestor = '%s'
                OR  surfercontact_requested = '%s')
               AND surfercontact_status = %d";
    $sqlParams = array($this->tableContacts, $surferId, $surferId,
      SURFERCONTACT_STATUS_PENDING
    );
    $contactRequests = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['surfercontact_requestor'] == $surferId) {
          $contactRequests[] = $row['surfercontact_requested'];
        } else {
          $contactRequests[] = $row['surfercontact_requestor'];
        }
      }
    }
    return $contactRequests;
  }

  /**
  * Get contacts
  *
  * Get all contacts for a surfer
  * If no surfer id (third argument) is provided,
  * it will return the contacts of the current surfer
  * The first two (optional) arguments can be used to
  * limit the list using an offset value and a maximum length
  *
  * The third (optional) argument, the contact type, can be
  *   SURFERCONTACT_STATUS_BOTH (0) for both kinds
  *   SURFERCONTACT_STATUS_PENDING (1) for pending contacts only
  *   SURFERCONTACT_STATUS_ACCEPTED (2, default) for accepted contacts only
  *
  * The last (optional) argument is a surfer id that can be used
  * if you want to explore the contacts of another surfer
  * than the one from the attribute
  *
  * If type is SURFERCONTACT_STATUS_BOTH, the returned array will be associative and
  * contain id=>status pairs;
  * otherwise, it will be a plain vanilla array
  * because we know the elements' status anyway
  *
  * @access public
  * @param mixed NULL|int $number (optional) -- maximum number of records
  * @param mixed NULL|int $offset (optional) -- start record number
  * @param int $type (optional)
  * @param string $surferId (optional)
  * @param string $sort optional 'ASC' OR 'DESC' (default 'ASC')
  * @return array
  */
  function getContacts($number = NULL, $offset = NULL,
      $type = SURFERCONTACT_STATUS_ACCEPTED,
      $surferId = '', $sort = 'ASC') {
    if ($surferId == '') {
      $surferId = $this->surfer;
    }
    if ($sort != 'DESC') {
      $sort = 'ASC';
    }
    // Get the contacts and/or contact requests
    $contacts = array();
    $contactRequests = array();
    if ($type == SURFERCONTACT_STATUS_BOTH || $type == SURFERCONTACT_STATUS_ACCEPTED) {
      $contacts = $this->_getAcceptedContacts($surferId);
    }
    if ($type == SURFERCONTACT_STATUS_BOTH || $type == SURFERCONTACT_STATUS_PENDING) {
      $contactRequests = $this->_getContactRequests($surferId);
    }
    $surfers = array_merge($contacts, $contactRequests);
    $this->absCount = sizeof($contacts);
    // Get the surfer handles for sorting
    include_once(dirname(__FILE__).'/base_surfers.php');
    $surfersObj = surfer_admin::getInstance($this->msgs);
    $handles = $surfersObj->getHandleById($surfers, TRUE, $sort);
    if ($number !== NULL && $offset === NULL) {
      $offset = 0;
    }
    $counter = 0;
    $result = array();
    foreach ($handles as $id => $handle) {
      if ($number == NULL || ($counter >= $offset && $counter < $offset + $number)) {
        if ($type == SURFERCONTACT_STATUS_BOTH) {
          if (in_array($id, $contacts)) {
            $result[$id] = SURFERCONTACT_STATUS_ACCEPTED;
          } else {
            $result[$id] = SURFERCONTACT_STATUS_PENDING;
          }
        } else {
          $result[] = $id;
        }
      }
      $counter++;
      if ($number !== NULL && $counter >= $offset + $number) {
        break;
      }
    }
    return $result;
  }

  /**
   * return the last abscount
   *
   * @return integer
   */
  function getAbsCount() {
    return $this->absCount;
  }


  /**
  * Get contacts with timestamp
  *
  * Returns an array of surferId => timestamp arrays
  * with all direct contacts of the current surfer
  *
  * @access public
  * @param mixed NULL|int $number (optional) -- maximum number of records
  * @param mixed NULL|int $offset (optional) -- start record number
  * @param string $surferId (optional)
  * @param string $sort optional 'ASC' OR 'DESC' (default 'ASC')
  * @return array
  */
  function getContactsWithTimestamp($number = NULL, $offset = NULL,
                                    $surferId = '', $sort = 'ASC') {
    if ($surferId == '') {
      $surferId = $this->surfer;
    }
    if ($sort != 'DESC') {
      $sort = 'ASC';
    }
    // Create an empty array for contacts
    $contacts = array();
    // Select all records in which our surfer is requestor
    $sql = "SELECT c.surfercontact_requestor,
                   c.surfercontact_requested,
                   c.surfercontact_status,
                   c.surfercontact_timestamp,
                   s.surfer_id, s.surfer_handle
              FROM %s c, %s s
             WHERE (c.surfercontact_requestor='%s'
                     AND c.surfercontact_requested = s.surfer_id)
               AND c.surfercontact_status=2
          ORDER BY s.surfer_handle ".$sort;
    $sqlParams = array($this->tableContacts, $this->tableSurfer, $surferId, $surferId);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $number, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $contacts[$row['surfercontact_requested']] = $row['surfercontact_timestamp'];
      }
    }
    return $contacts;
  }

  /**
  * Is contact?
  *
  * Returns contact status between two surfers
  * Return values:
  * SURFERCONTACT_NONE (0) => no contact at all, or the sum of
  * SURFERCONTACT_DIRECT (1) => direct contact
  * SURFERCONTACT_PENDING (2) => direct contact pending, initiated by either of them
  * SURFERCONTACT_INDIRECT (4) => indirect contact(s) within the scope of
  *                               the maximum path length
  * SURFERCONTACT_OWNREQUEST (8) => the request (2) was initiated by the current surfer,
  *                                 not by the contact surfer (only if $requestDirection is TRUE)
  *
  * If you provide an array of contact surfers, you will receive
  * an associative array of surfer id => contact status pairs
  *
  * In many cases you only want to know whether surfers have got direct contact,
  * so the method only searches for indirect contacts if you set the third,
  * optional parameter to TRUE
  *
  * @access public
  * @param mixed string|array $contactIds
  * @param boolean $searchIndirect optional, default FALSE
  * @param boolean $requestDirection optional, default FALSE
  * @return mixed int|array
  */
  function isContact($contactIds, $searchIndirect = FALSE, $requestDirection = FALSE) {
    // Check whether we've got one or many contact ids
    $isMultiples = TRUE;
    if (!(is_array($contactIds))) {
      $contactIds = array($contactIds);
      $isMultiples = FALSE;
    }
    // Determine the key for the correct memory cache branch
    $cacheKey = 'direct';
    if ($searchIndirect) {
      $cacheKey = 'indirect';
    }
    // Initialize result and query arrays
    $contactValues = array();
    $queryContacts = array();
    if (isset($this->isContactCache[$cacheKey]) && is_array($this->isContactCache[$cacheKey])) {
      foreach ($contactIds as $contactId) {
        if (isset($this->isContactCache[$cacheKey][$contactId])) {
          $contactValues[$contactId] = $this->isContactCache[$cacheKey][$contactId];
        } else {
          $queryContacts[] = $contactId;
        }
      }
    } else {
      $queryContacts = &$contactIds;
    }
    // Now for the queries, if necessary
    if (!(empty($queryContacts))) {
      if ($searchIndirect) {
        // Get detailed contacts (including idirect) by calling findContact()
        foreach ($queryContacts as $contactId) {
          $status = $this->findContact($contactId, FALSE, !$searchIndirect, $requestDirection);
          $contactValues[$contactId] = $status;
          if (!(isset($this->isContactCache[$cacheKey]))) {
            $this->isContactCache[$cacheKey] = array();
          }
          $this->isContactCache[$cacheKey][$contactId] = $status;
        }
      } else {
        // If we only need direct contacts, use a single query
        $requestedCond = $this->databaseGetSQLCondition(
          array('surfercontact_requested' => $queryContacts)
        );
        $requestorCond = $this->databaseGetSQLCondition(
          array('surfercontact_requestor' => $queryContacts)
        );
        $sql = "SELECT surfercontact_requestor, surfercontact_requested, surfercontact_status
                  FROM %1\$s
                 WHERE (surfercontact_requestor = '%2\$s' AND ".$requestedCond.")
                    OR (surfercontact_requested = '%2\$s' AND ".$requestorCond.")";
        $sqlParams = array($this->tableContacts, $this->surfer);
        if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            if ($row['surfercontact_status'] == 1) {
              $status = SURFERCONTACT_PENDING;
            } elseif ($row['surfercontact_status'] == 2) {
              $status = SURFERCONTACT_DIRECT;
            } else {
              $status = SURFERCONTACT_NONE;
            }
            if ($row['surfercontact_requestor'] == $this->surfer) {
              if (!isset($contactValues[$row['surfercontact_requested']])) {
                if ($requestDirection && $status == SURFERCONTACT_PENDING) {
                  $status += SURFERCONTACT_OWNREQUEST;
                }
                $contactValues[$row['surfercontact_requested']] = $status;
                if (!(isset($this->isContactCache[$cacheKey]))) {
                  $this->isContactCache[$cacheKey] = array();
                }
                $this->isContactCache[$cacheKey][$row['surfercontact_requested']] = $status;
              }
            } else {
              if (!isset($contactValues[$row['surfercontact_requestor']])) {
                $contactValues[$row['surfercontact_requestor']] = $status;
                if (!(isset($this->isContactCache[$cacheKey]))) {
                  $this->isContactCache[$cacheKey] = array();
                }
                $this->isContactCache[$cacheKey][$row['surfercontact_requestor']] = $status;
              }
            }
          }
        }
        // Set SURFERCONTACT_NONE for the rest of the surfers
        $diff = array_diff($queryContacts, array_keys($contactValues));
        foreach ($diff as $contact) {
          $contactValues[$contact] = SURFERCONTACT_NONE;
          $this->isContactCache[$cacheKey][$contact] = SURFERCONTACT_NONE;
        }
      }
    }
    // Return result according to type of $contactIds -- int or array
    if ($isMultiples) {
      return $contactValues;
    }
    if (isset($contactValues[$contactIds[0]])) {
      return $contactValues[$contactIds[0]];
    }
    return FALSE;
  }

  /**
  * Helper method to get the number of (bidirectional) contact requests
  *
  * @access private
  * @param string $surferId (optional)
  */
  function _getContactRequestsNumber($surferId = '') {
    if ($surferId == '') {
      $surferId = $this->surfer;
    }
    if (isset($this->contactRequestNumCache[$surferId])) {
      $number = $this->contactRequestNumCache[$surferId];
    } else {
      $sql = "SELECT COUNT(*)
                FROM %s
              WHERE (surfercontact_requestor='%s'
                  OR surfercontact_requested='%s')
                AND surfercontact_status=1";
      $sqlParams = array($this->tableContacts, $surferId, $surferId);
      $number = 0;
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($n = $res->fetchField()) {
          $number = $n;
          $this->contactRequestNumCache[$surferId] = $n;
        }
      }
    }
    return $number;
  }

  /**
  * Helper method to get the number of direct contacts
  *
  * @access private
  * @param string $surferId (optional)
  */
  function _getAcceptedContactsNumber($surferId = '') {
    if ($surferId == '') {
      $surferId = $this->surfer;
    }
    if (isset($this->contactNumCache[$surferId])) {
      $number = $this->contactNumCache[$surferId];
    } else {
      $sql = "SELECT COUNT(*)
                FROM %s
              WHERE surfercontact_requestor='%s'
                AND surfercontact_status=2";
      $sqlParams = array($this->tableContacts, $surferId);
      $number = 0;
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($n = $res->fetchField()) {
          $number = $n;
          $this->contactNumCache[$surferId] = $n;
        }
      }
    }
    return $number;
  }

  /**
  * Get number of contacts
  *
  * A practical helper method to determine
  * whether another page is needed
  *
  * The first (optional) argument, the contact type, can be
  *   SURFERCONTACT_STATUS_BOTH (0) for both kinds
  *   SURFERCONTACT_STATUS_PENDING (1) for pending contacts only
  *   SURFERCONTACT_STATUS_ACCEPTED (2, default) for accepted contacts only
  *
  * @access public
  * @param int $type (optional)
  * @param string $surferId (optional)
  * @return int
  */
  function getContactNumber($type = SURFERCONTACT_STATUS_ACCEPTED, $surferId = '') {
    $number = 0;
    if ($type == SURFERCONTACT_STATUS_BOTH || $type == SURFERCONTACT_STATUS_PENDING) {
      $number += $this->_getContactRequestsNumber($surferId);
    }
    if ($type == SURFERCONTACT_STATUS_BOTH || $type == SURFERCONTACT_STATUS_ACCEPTED) {
      $number += $this->_getAcceptedContactsNumber($surferId);
    }
    return $number;
  }

  /**
  * Get contact requests sent
  *
  * Returns an array of surfer IDs
  * to which the current surfer has sent contact requests
  *
  * @access public
  * @param string $surferId (optional)
  * @param boolean $withTimestamp (optional)
  * @param mixed NULL|int $number (optional) -- max. number of records
  * @param mixed NULL|int $offset (optional) -- start record
  * @param string $sort optional 'ASC' OR 'DESC' (default 'ASC')
  * @return array
  */
  function getContactRequestsSent($surferId = '', $withTimestamp = FALSE,
                                  $number = NULL, $offset = NULL, $sort = 'ASC') {
    if ($surferId == '') {
      $surferId = $this->surfer;
    }
    if ($sort != 'DESC') {
      $sort = 'ASC';
    }
    $addTimestamp = ($withTimestamp == TRUE) ? ', c.surfercontact_timestamp' : '';
    $orderBy = ($withTimestamp == TRUE) ? ' c.surfercontact_timestamp ' : ' s.surfer_handle ';
    if ($withTimestamp == TRUE) {
      $sort = 'DESC';
    }
    $sql = "SELECT c.surfercontact_requestor,
                   c.surfercontact_requested,
                   c.surfercontact_status %s
              FROM %s c, %s s
             WHERE c.surfercontact_requested = s.surfer_id
               AND c.surfercontact_requestor = '%s'
               AND c.surfercontact_status=1
          ORDER BY ".$orderBy.$sort;
    $sqlParams = array($addTimestamp, $this->tableContacts,
      $this->tableSurfer, $surferId);
    $contactRequests = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $number, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($withTimestamp) {
          $contactRequests[$row['surfercontact_requested']] =
            $row['surfercontact_timestamp'];
        } else {
          $contactRequests[] = $row['surfercontact_requested'];
        }
      }
      $this->absCount = $res->absCount();
    }
    return $contactRequests;
  }

  /**
  * Get contact requests received
  *
  * Returns an array of surfer IDs
  * who have sent contact requests to the current surfer
  *
  * @access public
  * @param string $surferId (optional)
  * @param boolean $withTimestamp (optional)
  * @param mixed NULL|int $number (optional) -- max. number of records
  * @param mixed NULL|int $offset (optional) -- start record
  * @param string $sort optional 'ASC' OR 'DESC' (default 'ASC')
  * @return array
  */
  function getContactRequestsReceived($surferId = '', $withTimestamp = FALSE,
                                      $number = NULL, $offset = NULL, $sort = 'ASC') {
    if ($surferId == '') {
      $surferId = $this->surfer;
    }
    if ($sort != 'DESC') {
      $sort = 'ASC';
    }
    $addTimestamp = ($withTimestamp == TRUE) ? ', c.surfercontact_timestamp' : '';
    $orderBy = ($withTimestamp == TRUE) ? ' c.surfercontact_timestamp ' : ' s.surfer_handle ';
    if ($withTimestamp == TRUE) {
      $sort = 'DESC';
    }
    $sql = "SELECT c.surfercontact_requestor,
                   c.surfercontact_requested,
                   c.surfercontact_status %s
              FROM %s c, %s s
             WHERE c.surfercontact_requestor = s.surfer_id
               AND c.surfercontact_requested = '%s'
               AND c.surfercontact_status = 1
          ORDER BY ".$orderBy.$sort;
    $sqlParams = array($addTimestamp, $this->tableContacts, $this->tableSurfer,
      $surferId);
    $contactRequests = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams, $number, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($withTimestamp) {
          $contactRequests[$row['surfercontact_requestor']] =
            $row['surfercontact_timestamp'];
        } else {
          $contactRequests[] = $row['surfercontact_requestor'];
        }
      }
      $this->absCount = $res->absCount();
    }
    return $contactRequests;
  }

  /**
  * Get number of contact requests sent
  *
  * Simply returns the number of contact requests
  * that the current surfer has sent to other surfers
  *
  * @access public
  * @param string $surferId (optional)
  * @return array
  */
  function getContactRequestsSentNumber($surferId = '') {
    if ($surferId == '') {
      $surferId = $this->surfer;
    }
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE surfercontact_requestor='%s'
               AND surfercontact_status=1";
    $sqlParams = array($this->tableContacts, $surferId);
    $contactNumber = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $contactNumber = $num;
      }
    }
    return $contactNumber;
  }

  /**
  * Get number of contact requests received
  *
  * Simply returns the number of contact requests
  * that other surfers have sent to the current surfer
  *
  * @access public
  * @param string $surferId (optional)
  * @return array
  */
  function getContactRequestsReceivedNumber($surferId = '') {
    if ($surferId == '') {
      $surferId = $this->surfer;
    }
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE surfercontact_requested='%s'
               AND surfercontact_status=1";
    $sqlParams = array($this->tableContacts, $surferId);
    $contactNumber = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $contactNumber = $num;
      }
    }
    return $contactNumber;
  }

  /**
  * trace contact
  *
  * Recursively checks contact from start surfer up to target surfer
  * to get all contact paths
  *
  * @access public
  * @param string $targetSurfer id of the surfer to whom contact is to be checked
  * @param string $currentSurfer (optional) id of the currently examined surfer
  * @param array $path (optional) path that lead from $this->surfer to $currentSurfer
  * @return boolean
  */
  function traceContact($targetSurfer, $currentSurfer = NULL,
                        $path = NULL, $defaultContact = NULL) {
    // Presume that this is not the first run
    $firstRun = FALSE;
    // Set current surfer to surfer attribute if not selected yet
    if ($currentSurfer === NULL) {
      $currentSurfer = $this->surfer;
      // There can't be a path yet, so create it as an empty array
      $path = array();
      // Keep in mind that this was the original call to prevent
      // exit on direct contact before recursion
      $firstRun = TRUE;
    }
    if ($firstRun) {
      // If we've got a default contact, he/she needs to be generally excluded
      // unless he/she is the target surfer we're originally looking for
      include_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');
      $opt = base_module_options::readOption(
        $this->surfersModuleGuid, 'DEFAULT_CONTACT', ''
      );
      if ($opt != '' && $defaultContact != $targetSurfer) {
        $defaultContact = $opt;
      }
    }
    // Add current person to path
    $path[] = $currentSurfer;
    // If path is longer than max path length, return without result
    if (sizeof($path) > $this->maxPathLength) {
      return FALSE;
    }
    // If we're not in first run,
    // check out whether the target surfer is within the current surfers's direct contacts
    if (!$firstRun) {
      $key = $currentSurfer.'-'.$targetSurfer;
      if (isset($this->contactNumCache[$key])) {
        $direct = $this->contactNumCache[$key];
      } else {
        $sql = "SELECT COUNT(*)
                  FROM %s
                WHERE surfercontact_requestor='%s'
                  AND surfercontact_requested='%s'
                  AND (surfercontact_status=2)";
        $queryData = array($this->tableContacts, $currentSurfer, $targetSurfer,
                          $targetSurfer, $currentSurfer);
        $query = $this->databaseQueryFmt($sql, $queryData);
        $direct = 0;
        if ($num = $query->fetchField()) {
          $direct = $num;
        }
        $this->contactNumCache[$key] = $direct;
      }
      if ($direct > 0) {
        // We found the contact, so update its path,
        // push it into contact path list,
        // and return TRUE
        $path[] = $targetSurfer;
        $this->pathList[] = $path;
        return TRUE;
      }
    }
    // Create an SQL condition for the exclude list
    $excludePath = $path;
    // Additionally exclude target surfer in first run
    if ($firstRun) {
      $excludePath[] = $targetSurfer;
    }
    // Check out whether the current surfer's contacts are already cached
    if (isset($this->contactCache[$currentSurfer]) &&
        is_array($this->contactCache[$currentSurfer])) {
      $contacts = $this->contactCache[$currentSurfer];
    } else {
      // Get all contacts of current surfer
      // except for any person on the path that lead us here <-- this is very important!
      $contacts = array();
      $sql = "SELECT surfercontact_requested
                FROM %s
               WHERE surfercontact_requestor='%s'
                 AND surfercontact_status=2";
      $queryData = array($this->tableContacts, $currentSurfer);
      if ($defaultContact != '') {
        $sql .= " AND surfercontact_requested != '%s'";
        $queryData[] = $defaultContact;
      }
      if ($res = $this->databaseQueryFmt($sql, $queryData)) {
        while ($srf = $res->fetchField()) {
          $contacts[] = $srf;
          $this->surfersList[] = $srf;
        }
      }
      // Store contacts in cache array
      $this->contactCache[$currentSurfer] = $contacts;
    }
    // Assume that we don't find anything here
    $success = FALSE;
    // Now, recursively call this function for each contact (except for the exclude path)
    foreach ($contacts as $contact) {
      if (!(in_array($contact, $excludePath))) {
        if ($this->traceContact($targetSurfer, $contact, $path, $defaultContact)) {
          $success = TRUE;
        }
      }
    }
    // Return whether we found any paths in this turn
    return $success;
  }

  /**
  * Find contact
  *
  * Look for contacts between current surfer and another surfer
  * Returns:
  * SURFERCONTACT_NONE (0) => no contact at all, or the sum of
  * SURFERCONTACT_DIRECT (1) => direct contact
  * SURFERCONTACT_PENDING (2) => direct contact pending, initiated by either of them
  * SURFERCONTACT_INDIRECT (4) => indirect contact(s) within the scope
  *                               of the maximum path length
  * SURFERCONTACT_OWNREQUEST (8) => the request (2) was initiated by the current surfer,
  *                                 not by the contact surfer (only if $requestDirection is TRUE)
  *
  * In case of direct contacts, the function won't look for indirect contacts by default,
  * but you can force this functionality by setting the second, optional argument to TRUE.
  * If you set the third, optional argument to TRUE, the method will ONLY search for
  * direct contact and stop if it doesn't find it.
  *
  * @access public
  * @param string $contactSurfer
  * @param boolean $forceCheck (optional)
  * @param boolean $directOnly (optional)
  * @param boolean $requestDirection (optional)
  * @return int
  */
  function findContact($contactSurfer, $forceCheck = FALSE, $directOnly = FALSE,
                       $requestDirection = FALSE) {
    // Get out of here if we asked for the current surfer
    if ($contactSurfer == $this->surfer) {
      return SURFERCONTACT_NONE;
    }
    // First, check whether both people have contacts at all --
    // if not, there can't be a contact between them, either
    // Group the answer by contact status because we do not need
    // to check for indirect contacts if one of the surfers
    // does not have confirmed contacts
    $sql = "SELECT COUNT(*) AS num_contacts, surfercontact_status FROM %s
             WHERE surfercontact_requestor='%s' OR surfercontact_requested='%s'
          GROUP BY surfercontact_status
            HAVING surfercontact_status IN (1, 2)";
    $params = array($this->tableContacts, $this->surfer, $this->surfer);
    if ($query = $this->databaseQueryFmt($sql, $params)) {
      $surferContacts = array();
      while ($row = $query->fetchRow(DB_FETCHMODE_ASSOC)) {
        $surferContacts[$row['surfercontact_status']] = $row['num_contacts'];
      }
      // Check for number of direct and pending contacts
      $surferHasDirectContacts = FALSE;
      $surferHasPendingContacts = FALSE;
      if (isset($surferContacts[2]) && $surferContacts[2] > 0) {
        $surferHasDirectContacts = TRUE;
      }
      if (isset($surferContacts[1]) && $surferContacts[1] > 0) {
        $surferHasPendingContacts = TRUE;
      }
      // If there is no direct contact, store this in cache; if there is no contact at all, return
      if ($surferHasDirectContacts === FALSE) {
        $this->cachePathList($this->surfer, $contactSurfer, TRUE);
        if ($surferHasPendingContacts === FALSE) {
          return SURFERCONTACT_NONE;
        }
      }
    }
    $sql = "SELECT COUNT(*) AS num_contacts, surfercontact_status FROM %s
             WHERE surfercontact_requestor='%s' OR surfercontact_requested='%s'
          GROUP BY surfercontact_status
            HAVING surfercontact_status IN (1, 2)";
    $params = array($this->tableContacts, $contactSurfer, $contactSurfer);
    if ($query = $this->databaseQueryFmt($sql, $params)) {
      $contactContacts = array();
      while ($row = $query->fetchRow(DB_FETCHMODE_ASSOC)) {
        $contactContacts[$row['surfercontact_status']] = $row['num_contacts'];
      }
      // Check for number of direct and pending contacts
      $contactHasDirectContacts = FALSE;
      $contactHasPendingContacts = FALSE;
      if (isset($contactContacts[2]) && $contactContacts[2] > 0) {
        $contactHasDirectContacts = TRUE;
      }
      if (isset($contactContacts[1]) && $contactContacts[1] > 0) {
        $contactHasPendingContacts = TRUE;
      }
      // If there is no direct contact, store this in cache; if there is no contact at all, return
      if ($contactHasDirectContacts === FALSE) {
        $this->cachePathList($this->surfer, $contactSurfer, TRUE);
        if ($contactHasPendingContacts === FALSE) {
          return SURFERCONTACT_NONE;
        }
      }
    }
    $result = SURFERCONTACT_NONE;
    // Now check whether they do have DIRECT contact (confirmed or pending)
    $sql = "SELECT surfercontact_status, surfercontact_requestor, surfercontact_requested FROM %s
             WHERE (surfercontact_requestor='%s' AND surfercontact_requested='%s')
                OR (surfercontact_requestor='%s' AND surfercontact_requested='%s')";
    $sqlParams = array(
      $this->tableContacts,
      $this->surfer,
      $contactSurfer,
      $contactSurfer,
      $this->surfer
    );
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $status = $row['surfercontact_status'];
        if ($status == 2) {
          $result = SURFERCONTACT_DIRECT;
          // Return if $forceCheck is set to FALSE
          if (!$forceCheck) {
            return $result;
          }
        } elseif ($status == 1) {
          $result = SURFERCONTACT_PENDING;
          if ($requestDirection && $row['surfercontact_requestor'] == $this->surfer) {
            $result += SURFERCONTACT_OWNREQUEST;
          }
        }
      }
    }
    // If we only want to search for direct contacts, that's it
    if ($directOnly) {
      return $result;
    }
    // If one of the surfers has only got pending contacts, we can
    // just return the current result without checking for contact paths
    if ($surferHasDirectContacts === FALSE || $contactHasDirectContacts === FALSE) {
      return $result;
    }
    // Check whether we can get the path list from cache
    $isCached = $this->checkContactCache($this->surfer, $contactSurfer);
    if ($isCached) {
      // If we have received an empty path list, return current result
      if (empty($this->pathList)) {
        return $result;
      }
      // Otherwise add indirect contact to results and get out of here
      $result += SURFERCONTACT_INDIRECT;
      return $result;
    }
    // No or only pending direct contact => trace indirect contacts
    // Start from person 1 because his/her contacts to person 2
    // are to be displayed
    if ($this->traceContact($contactSurfer)) {
      // Sort path list by length of the nested arrays
      usort($this->pathList, array('contact_manager', '_lenCmp'));
      // Make surfers list unique
      $this->surfersList = array_unique($this->surfersList);
      // Store the sorted path list in cache
      $this->cachePathList($this->surfer, $contactSurfer);
      // Add indirect contact to results
      $result += SURFERCONTACT_INDIRECT;
    } else {
      // Store in cache that there is no contact
      $this->cachePathList($this->surfer, $contactSurfer, TRUE);
    }
    return $result;
  }

  /**
  * Check whether all surfer ids in an array exist
  * (internal helper method)
  *
  * @access private
  * @param array $surferIds
  * @return boolean
  */
  function _existSurferIds($surferIds) {
    // For how many surfers do we ask?
    $numRequestedSurfers = count($surferIds);
    // Get the really existing numbers
    $numFoundSurfers = 0;
    $sql = "SELECT COUNT(*) FROM %s WHERE ".$this->databaseGetSQLCondition('surfer_id', $surferIds);
    $sqlParams = array($this->tableSurfer);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $numFoundSurfers = $num;
      }
    }
    // Return the result of the comparison operation
    return ($numFoundSurfers >= $numRequestedSurfers);
  }

  /**
  * Load contact paths between two surfers from cache
  * If only a reverse record is available, it will be turned around
  * If there is a cache record, store data in the path instance variable
  * Return boolean success/failure
  *
  * @access public
  * @param string $startSurfer
  * @param string $targetSurfer
  * @return boolean
  */
  function checkContactCache($startSurfer, $targetSurfer) {
    // Get cache time
    include_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');
    $cacheTime = base_module_options::readOption(
      $this->surfersModuleGuid, 'PATH_CACHE_TIME', 1440
    );
    // Check whether we've got a cache record at all
    $sql = "SELECT contactcache_id,
                   contactcache_start,
                   contactcache_target,
                   contactcache_paths,
                   contactcache_surfers,
                   contactcache_timestamp
              FROM %1\$s
             WHERE ((contactcache_start = '%2\$s'
                     AND contactcache_target = '%3\$s')
                OR (contactcache_start = '%3\$s'
                     AND contactcache_target = '%2\$s'))
               AND contactcache_timestamp > %4\$d";
    $sqlParams = array(
      $this->tableContactCache, $startSurfer, $targetSurfer, time() - $cacheTime * 60
    );
    $cacheData = array();
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $cacheData = &$row;
      }
    }
    // Return FALSE if we do not have a valid cache record
    if (empty($cacheData)) {
      return FALSE;
    }
    // Return TRUE if the result is that there are no contacts
    if ($cacheData['contactcache_paths'] == 'NONE') {
      $this->pathList = array();
      return TRUE;
    }
    // Has there been a deletion of a surfer account
    // since the last update of this cache record?
    $del = base_module_options::readOption(
      $this->surfersModuleGuid, 'LAST_SURFER_DELETION', 0
    );
    // If there was, check whether all involved surfers are still alive
    if ($del > $cacheData['contactcache_timestamp']) {
      $surfersList = unserialize($row['contactcache_surfers']);
      $allExist = $this->_existSurferIds($surfersList);
    } else {
      $allExist = TRUE;
    }
    if ($allExist) {
      // Restore surfers list and path list, and return TRUE
      $this->surfersList = &$surfersList;
      $tempPathList = unserialize($row['contactcache_paths']);
      // Do we have to reverse the order?
      if ($cacheData['contactcache_target'] == $startSurfer) {
        $reversePathList = array();
        foreach ($tempPathList as $path) {
          $reversePathList[] = array_reverse($path);
        }
        $this->pathList = &$reversePathList;
      } else {
        $this->pathList = &$tempPathList;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Store the current path list in cache
  *
  * @access public
  * @param string $startSurfer
  * @param string $targetSurfer
  * @param boolean $noContact optional, default FALSE
  */
  function cachePathList($startSurfer, $targetSurfer, $noContact = FALSE) {
    if ($noContact === TRUE) {
      // No contact at all:
      // Set path list to a fixed string of 'NONE'
      // and surfers list to an empty string
      $pathListString = 'NONE';
      $surfersListString = '';
    } else {
      // Serialize path list and surfers list
      $pathListString = serialize($this->pathList);
      $surfersListString = serialize($this->surfersList);
    }
    // Check whether we've already got a cache record
    $sql = "SELECT contactcache_id,
                   contactcache_start,
                   contactcache_target
              FROM %s
             WHERE contactcache_start = '%s'
               AND contactcache_target = '%s'";
    $sqlParams = array($this->tableContactCache, $startSurfer, $targetSurfer);
    $id = NULL;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $id = $row['contactcache_id'];
      }
    }
    $data = array(
      'contactcache_paths' => &$pathListString,
      'contactcache_surfers' => &$surfersListString,
      'contactcache_timestamp' => time()
    );
    $data['contactcache_start'] = $startSurfer;
    $data['contactcache_target'] = $targetSurfer;
    if ($id) {
      $this->databaseUpdateRecord(
        $this->tableContactCache, $data, 'contactcache_id', $id
      );
    } else {
      $this->databaseInsertRecord(
        $this->tableContactCache, 'contactcache_id', $data
      );
    }
  }

  /**
  * Clear contact cache
  *
  * Remove all records that are older than the official cache time
  *
  * @access public
  */
  function clearContactCache() {
    // Get cache time
    include_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');
    $cacheTime = base_module_options::readOption(
      $this->surfersModuleGuid, 'PATH_CACHE_TIME', 1440
    );
    $sql = "DELETE FROM %s
             WHERE contactcache_timestamp <= %d";
    $sqlParams = array($this->tableContactCache, time() - $cacheTime * 60);
    $this->databaseQueryFmtWrite($sql, $sqlParams);
  }

  /**
  * search by string for contacts
  *
  * will return an array of results, each result looks like
  * [surfer_id => [surfer_handle, surfer_givenname, surfer_surname, surfercontact_status]]
  *
  * @param string $pattern searchstring
  * @param boolean $handleOnly optional will search only the handle and not names
  * @param integer $type optional default only real connections
  * @param integer $number optional
  * @param integer $offset optional
  * @param string $sort optional ASC/DESC
  * @return array result
  */
  function searchContacts($pattern, $handleOnly = FALSE,
    $type = SURFERCONTACT_STATUS_ACCEPTED, $number = NULL, $offset = NULL, $sort = 'ASC') {

    $surferId = $this->surfer;

    // Default Sorting
    $sort = ($sort != 'DESC') ? 'ASC' : 'DESC';

    // Create an empty array for contacts
    $contacts = array();

    // Create contact type condition depending on $type argument
    $typeCondition = 'c.surfercontact_status = 2';
    if ($type == SURFERCONTACT_STATUS_PENDING) {
      $typeCondition = 'c.surfercontact_status = 1';
    } elseif ($type == SURFERCONTACT_STATUS_BOTH) {
      $typeCondition = 'c.surfercontact_status IN (1, 2)';
    }

    //include_once(PAPAYA_INCLUDE_PATH.'system/base_searchstringparser.php');
    //$parser = new searchStringParser();
    //$parser->tokenMinLength = 2;
    if ($handleOnly) {
      $searchFields = array('s.surfer_handle');
    } else {
      $searchFields = array('s.surfer_handle', 's.surfer_givenname', 's.surfer_surname');
    }

    // cant use papaya search string parser, e.g. because we want allow to search for "And"
    // as a part of a name
    $filter = array();
    foreach ($searchFields as $searchField) {
      if (count($filter) > 0) {
        $filter[] = 'OR';
      }

      $filter[] = sprintf("%s LIKE '%%%s%%'", $searchField, $this->escapeStr($pattern));
    }

    //if ($filter = $parser->getSQL($pattern, $searchFields, PAPAYA_SEARCH_BOOLEAN)) {
    if (count($filter) > 0) {
      // Select all records in which our surfer is either requestor or requested
      $sql = "SELECT DISTINCT
                     s.surfer_id,
                     s.surfer_handle,
                     s.surfer_givenname,
                     s.surfer_surname,
                     c.surfercontact_status
                FROM %s c, %s s
               WHERE (" . str_replace('%', '%%', implode(' ', $filter)) . ")
                 AND ((c.surfercontact_requestor='%s'
                      AND c.surfercontact_requested = s.surfer_id
                      AND s.surfer_valid != 4)
                  OR (c.surfercontact_requested='%s'
                      AND c.surfercontact_requestor = s.surfer_id
                      AND s.surfer_valid != 4))
                 AND %s
            ORDER BY s.surfer_handle " . $sort;
      $sqlParams = array($this->tableContacts, $this->tableSurfer, $surferId,
        $surferId, $typeCondition);
      if ($res = $this->databaseQueryFmt($sql, $sqlParams, $number, $offset)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $contacts[$row['surfer_id']] = $row;
        }
        $this->absCount = $res->absCount();
      }
    }

    return $contacts;
  }

  /**
  * Internal helper function lenCmp
  *
  * Compares length of two arrays $a and $b
  * Returns
  *   -1 if $a is shorter than $b,
  *   +1 if $a is longer than $b,
  *    0 if their length is equal
  *
  * @param array $a
  * @param array $b
  * @return int
  */
  function _lenCmp($a, $b) {
    $aLen = sizeof($a);
    $bLen = sizeof($b);
    if ($aLen == $bLen) {
      return 0;
    }
    return ($aLen < $bLen) ? -1 : 1;
  }
}

// End of class definition
?>
