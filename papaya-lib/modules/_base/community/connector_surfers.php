<?php
/**
* Community user connector class
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
* @todo Move internal method logics (i.e. getProfileDataClassTitles) to surfers_admin
* @package Papaya-Modules
* @subpackage _Base-Community
* @version $Id: connector_surfers.php 38453 2013-04-29 10:55:34Z kersken $
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
* Basic class surfer administration
*/
require_once(dirname(__FILE__).'/base_surfers.php');

/**
* Basic class connector
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_connector.php');

/**
* Community user connector class
*
* Usage:
* include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
* $surfersObj = base_pluginloader::getPluginInstance('06648c9c955e1a0e06a7bd381748c4e4', $this);
*
* For now, this connector pipes through a lot of functionality to base_surfers
* Verbose documentation for each of these methods can be found there
*
* $bool = $surfersObj->existID($id, $includeBlocked = FALSE)
* $bool = $surfersObj->existEmail($email, $includeBlocked = FALSE)
* $bool = $surfersObj->existHandle($handle, $includeBlocked = FALSE)
* $bool = $surfersObj->checkHandle($handle)
* $bool = $surfersObj->checkEmailAgainsBlacklist($email)
* $string = $surfersObj->getIdByHandle($handle, $includeBlocked = FALSE)
* $string = $surfersObj->getIdByMail($mail, $includeBlocked = FALSE)
* $string = $surfersObj->getMailById($id, $includeBlocked = FALSE)
* $string = $surfersObj->getHandleById($id, $includeBlocked = FALSE)
* $array = $surfersObj->getNameById($id, $includeBlocked = FALSE)
* $string = $surfersObj->getMailByToken($token)
* $string = $surfersObj->getHandleByMail($mail, $includeBlocked = FALSE)
* $array = $surfersObj->getIdsByValidHandles($handles, $includeBlocked = FALSE)
* $array = $surfersObj->loadSurfer($id)
* $array = $surfersObj->loadSurfers(
*   $ids, $sort = 'ASC', $number = NULL, $offset = NULL, $includeBlocked = FALSE
* )
* $array = $surfersObj->loadAllSurfers(
*   $limit, $offset, $orderBy, $sort, $withAbsCount, $includeBlocked = FALSE
* )
* $array = $surfersObj->loadSurferNames(
*   $ids, $sort = 'ASC', $number = NULL, $offset = NULL, $includeBlocked = FALSE
* )
* $bool = $surferObj->setValid($surferId, $valid = 1)
* $bool|$string = $surfersObj->createSurfer($data)
* $bool = $surfersObj->saveSurfer($params = NULL)
* $bool = $surfersObj->saveSurferData($surferId, $params)
* $array = $surfersObj->loadGroups()
* $array = $surfersObj->searchSurfers($pattern, $handleOnly = FALSE,
*   $includeBlocked = FALSE, $orderBy = 'surfer_handle'
* )
* $array = $surfersObj->searchHandlesSimple($pattern);
* $surfersObj->blockSurfer($surferId, $contactId, $block);
* $surfersObj->bookmarkSurfer($surferId, $contactId, $bookmark);
* $array = $surfersObj->getBlocks($surferId);
* $array = $surfersObj->getBookmarks($surferId);
* $bool = $surfersObj->isBlocked($surferId, $contactId);
* $bool = $surfersObj->isBookmarked($surferId, $contactId);
* $int|$array = $surfersObj->getOnlineStatus($surferIds);
* $string|$array = $surfersObj->getDynamicData($surferId, $fieldName(s))
* $string|$array = $surfersObj->getDynamicDataCount($surferIds, $fields)
* $surfersObj->deleteDynamicData($surferId, $fieldName(s))
* $bool|$int = $surfersObj->setDynamicData($surferId, $fieldName(s), $value(s))
* $bool|$array = $surfersObj->checkDynamicData($fields, $value = NULL)
* $array = $surfersObj->findSurfersByDynamicData($criteria, $mode = 'AND')
* $surfersObj->getSearchDynamicForm(&$layout, $paramName = '', $hidden = NULL, $title = '')
* $surfersObj->showSurferResults($layout, FALSE, $params, $returnResults)
* $bool = $surfersObj->addContactRequest($surferId, $contactId)
* $bool = $surfersObj->forceContact($surferId, $contactId)
* $bool = $surfersObj->removeContactRequest($surferId, $contactId, $bidirectional = FALSE)
* $bool = $surfersObj->acceptContact($surferId, $contactId, $bidirectional = FALSE)
* $bool = $surfersObj->removeContact($surferId, $contactId)
* $string|NULL = $surfersObj->getDefaultContact()
* $string|NULL = $surfersObj->getBackendSurferLink($surferId)
* $string = $surfersObj->getPasswordHash($password)
* $string = $surfersObj->getPermCombo($name, $field, $data, $paramName = NULL)
*
* ============== Native methods ==============
*
* $array = $surfersObj->getProfileDataClasses()
* $array = $surfersObj->getProfileDataClassTitles($class)
* $array = $surfersObj->getProfileFieldNames($class = 0)
* $array = $surfersObj->getProfileFieldTitle($field)
* $array = $surfersObj->getProfileData($profileSurferId, $fields = NULL)
* $array = $surfersObj->getContacts(
*   $surferId, $withTimestamp = FALSE, $number = NULL, $offset = NULL
* )
* $int = $surfersObj->getContactNumber($surferId)
* $int = $surfersObj->isContact(
*   $surferId, $contactId, $searchIndirect = FALSE, $requestDirection = FALSE
* )
* $array = $surfersObj->getContactRequestsSent(
*   $surferId, $withTimestamp = FALSE, $number = NULL, $offset = NULL
* )
* $array = $surfersObj->getContactRequestsReceived(
*   $surferId, $withTimestamp = FALSE, $number = NULL, $offset = NULL
* )
* $int = $surfersObj->getContactRequestsSentNumber($surferId)
* $int = $surfersObj->getContactRequestsReceivedNumber($surferId)
*
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class connector_surfers extends base_connector {
  /**
   * Abs count of contacts operations
   * @var integer
   */
  var $contactsAbsCount = 0;

  /**
   * Surfer_admin
   * @var surfer_admin
   */
  var $surferAdmin = NULL;

  /**
  * Internal helper function to create a surfer admin instance
  *
  * @access private
  */
  function _initSurferAdmin() {
    if (!(isset($this->surferAdmin) && is_object($this->surferAdmin))) {
      $this->surferAdmin = surfer_admin::getInstance($this->msgs);
    }
  }

  /**
   * Check if a surfer id exists already.
   *
   * @see surfer_admin::existID Detailed description
   * @access public
   * @param string $id A unique surfer id
   * @param boolean $includeBlocked Check blocked surfers too
   * @return boolean Status (id exists)
   */
  function existID($id, $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->existID($id, $includeBlocked);
  }

  /**
   * Check if a surfer email handle exists already.
   *
   * @see surfer_admin::existEmail Detailed description
   * @see surfer_admin::existID() for behavior on blocked surfers
   * @access public
   * @param string $email unique surfer email
   * @param boolean $includeBlocked Check blocked surfers too
   * @return boolean Status (email exists)
   */
  function existEmail($email, $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->existEmail($email, $includeBlocked);
  }

  /**
   * Check if a surfer handle exists already.
   *
   * @see surfer_admin::existHandle Detailed description
   * @see surfer_admin::existID() for behavior on blocked surfers
   * @access public
   * @param string $handle Unique surfer handle
   * @param boolean $includeBlocked Check blocked surfers too
   * @return boolean Status (handle exists)
   */
  function existHandle($handle, $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->existHandle($handle, $includeBlocked);
  }

  /**
   * Get black list rules of a certain type.
   *
   * @see surfer_admin::getBlacklistRules Detailed description
   * @see surfer_admin::existID() for behavior on blocked surfers
   * @access public
   * @param array|string $type Type to get black list, i.e. by surfer "handle"
   * @param boolean $ordered Get an ordered list
   * @return array $rules A list with blacklist rules
   */
  function getBlacklistRules($type = 'handle', $ordered = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getBlacklistRules($type, $ordered);
  }

  /**
   * Check a (potential) surfer handle against the black list.
   *
   * @see surfer_admin::checkHandle Detailed description
   * @access public
   * @param string $handle surfer handle
   * @return boolean Status (surfer has not been blocked yet)
   */
  function checkHandle($handle) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->checkHandle($handle);
  }

  /**
   * Check if a surfer email has been added to blacklist yet.
   *
   * @see surfer_admin::checkEmailAgainstBlacklist Detailed description
   * @see surfer_admin::existID() for behavior on blocked surfers
   * @access public
   * @param string $email Surfer email
   * @return boolean Status (surfer email has not been blocked yet)
   */
  function checkEmailAgainstBlacklist($email) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->checkEmailAgainstBlacklist($email);
  }

  /**
  * Check a potential password according to the current password policy.
  *
  * Details on return values:
  * 0 => Password complies to the policy
  * -1 => too short (shorter than PASSWORD_MIN_LENGTH)
  * -2 => equal to surfer handle
  * -4 => matches a black list entry
  * (the return value is 0 or the sum of all error values
  *  in order to provide verbose error messages)
  *
  * @param string $password the password to check
  * @param string $handle (optional, default '') handle to compare the password to if
  *                       PASSWORD_NOT_EQUALS_HANDLE is set
  * @return integer 0 if password complies to the policy, a negative value otherwise
  */
  function checkPasswordForPolicy($password, $handle = '') {
    $this->_initSurferAdmin();
    return $this->surferAdmin->checkPasswordForPolicy($password, $handle);
  }

  /**
  * Get surfer id by handle.
  *
  * @see surfer_admin::getIdByHandle Detailed description
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param array|string $handle A surfer handle
  * @param boolean $includeBlocked Check blocked surfers too
  * @return mixed array|string Surfer id(s) or empty
  */
  function getIdByHandle($handle, $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getIdByHandle($handle, $includeBlocked);
  }

  /**
  * Get surfer id by email.
  *
  * @see surfer_admin::getIdByMail Detailed description
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param string $mail Unique surfer email address
  * @param boolean $includeBlocked Check blocked surfers too.
  * @return string Surfer id or empty
  */
  function getIdByMail($mail, $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getIdByMail($mail, $includeBlocked);
  }

  /**
  * Get surfer email by id.
  *
  * Looks up and returns the email address for a surfer ID
  * you provide as an argument. Returns an empty string
  * if the ID doesn't exist
  *
  * @see surfer_admin::getMailById Detailed description
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param string $id Unique surfer id.
  * @param boolean $includeBlocked Check blocked surfers too.
  * @return string Surfer email.
  */
  function getMailById($id, $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getMailById($id, $includeBlocked);
  }

  /**
  * Get surfer handle by id.
  *
  * @see surfer_admin::getHandleById Detailed description
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param string $id Unique surfer id(s)
  * @param boolean $order Flag to order multiple results by surfer handle
  * @param string $sort Sort muliple surfer handle results ('ASC'|'DESC')
  * @param boolean $includeBlocked Check blocked surfers too
  * @return string Surfer handle(s) or empty
   */
  function getHandleById($id, $order = FALSE, $sort = 'ASC', $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getHandleById($id, $includeBlocked);
  }

  /**
  * Get surfer name data by id(s)
  *
  * @see surfer_admin::getNameById Detailed description
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @access public
  * @param mixed array|string $id Surfer id(s)
  * @param boolean $includeBlocked Check blocked surfers too
  * @return mixed array|string Surfer name(s) or empty
  */
  function getNameById($id, $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getNameById($id, $includeBlocked);
  }

  /**
  * Get surfer email address by request token.
  *
  * @see surfer_admin::getMailByToken Detailed description
  * @access public
  * @param string $token Unique token to identify a surfer change request
  * @return string Surfer email address or empty
  */
  function getMailByToken($token) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getMailByToken($token);
  }

  /**
  * Get surfer handle by email
  *
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @see surfer_admin::getHandleByMail Detailed description
  * @access public
  * @param string $mail Surfer email
  * @param boolean $includeBlocked Check blocked surfers too
  * @return string Surfer handle
  */
  function getHandleByMail($mail, $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getHandleByMail($mail, $includeBlocked);
  }

  /**
  * Get surfer ids by valid handles.
  *
  * @see surfer_admin::existID() for behavior on blocked surfers
  * @see surfer_admin::getIdsByValidHandles Detailed description
  * @access public
  * @param mixed array|string $handles Surfer handle(s)
  * @param boolean $includeBlocked Check blocked surfers too
  * @return string $ids Surfer id(s)
  */
  function getIdsByValidHandles($handles, $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getIdsByValidHandles($handles, $includeBlocked);
  }

  /**
  * Load surfer data by id.
  *
  * @see surfer_admin::loadSurfer Detailed description
  * @access public
  * @param string $id Surfer id
  * @param boolean $frontend Get surfer data for frontend
  * @return mixed NULL|array Surfer data or empty
  */
  function loadSurfer($id) {
    $this->_initSurferAdmin();
    // Call the surfer_admin class' loadSurfer( ) method for frontend use
    // by setting the second, optional argument to TRUE
    return $this->surferAdmin->loadSurfer($id, TRUE);
  }

  /**
  * Load surfer names by id.
  *
  * @see existID() for behavior on blocked surfers
  * @see surfer_admin::loadSurferNames Detailed description
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
    $this->_initSurferAdmin();
    return $this->surferAdmin->loadSurferNames(
      $ids, $sort, $limit, $offset, $includeBlocked
    );
  }

  /**
  * Get favorite surfers.
  *
  * @see surfer_admin::getFavoriteSurfers Detailed description
  * @access public
  * @param boolean $withHandles Flag to get surfers with handles
  * @return array $surfers Surfers result
  */
  function getFavoriteSurfers($withHandles = TRUE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getFavoriteSurfers($withHandles);
  }

  /**
  * Add surfer to favorites.
  *
  * @see surfer_admin::addSurferToFavorites Detailed description
  * @access public
  * @param string $surferId Unique 32-char surfer id
  */
  function addSurferToFavorites($surferId) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->addSurferToFavorites($surferId);
  }

  /**
  * Get all valid surfers.
  *
  * @see surfer_admin::getAllValidSurfers Detailed description
  * @access public
  * @param boolean $withHandles Flag to get handles data too
  * @param mixed integer|NULL $groupId Filter by group id
  * @param mixed integer|NULL $limit Results limit
  * @param mixed integer|NULL $offset Results offset
  * @return array $surfers Surfers data
  */
  function getAllValidSurfers($withHandles = FALSE,
                              $groupId = NULL, $limit = NULL, $offset = NULL) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getAllValidSurfers(
      $withHandles, $groupId, $limit, $offset
    );
  }

  /**
  * Get the amount of valid surfers.
  *
  * @see surfer_admin::getAllValidSurfersNum Detailed description
  * @access public
  * @param integer $groupId Filter by group id
  * @return integer Surfers' amount
  */
  function getAllValidSurfersNum($groupId = NULL) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getAllValidSurfersNum($groupId);
  }

  /**
  * Get a list of all surfers who have ever logged in.
  *
  * @see surfer_admin::getSurfersLoginStatus Detailed description
  * @access public
  * @param string|array $surferAttr Value of the surfer's attribute to search for
  * @param string $attrType Tye of the surfer's attribute to search for
  * @param boolean $loggedinOnly Get surfers which have been logged in only
  * @return mixed NULL|boolean|int|array $result Surfer(s)' status
  */
  function getSurfersLoginStatus($surferAttr, $attrType = 'id', $loggedinOnly = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getSurfersLoginStatus($surferAttr, $attrType, $loggedinOnly);
  }

  /**
  * Load surfers data by id.
  *
  * @see surfer_admin::loadSurfers Detailed description
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
  function loadSurfers($ids, $sort = 'ASC',
                       $limit = NULL, $offset = NULL, $includeBlocked = FALSE) {
    $this->_initSurferAdmin();
    // Call the surferAdmin::loadSurfers() in frontend mode using an additional argument set to TRUE
    return $this->surferAdmin->loadSurfers(
      $ids, $sort, $limit, $offset, $includeBlocked, TRUE
    );
  }

  /**
  * Load all surfers.
  *
  * @see surfer_admin::loadAllSurfers Detailed description
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
    $this->_initSurferAdmin();
    return $this->surferAdmin->loadAllSurfers(
      $limit, $offset, $orderBy, $sort, $withAbsCount, $includeBlocked
    );
  }

  /**
   * Get the absolute count of last contact operation.
   *
   * @return integer Absolute count
   */
  function getSurfersAbsCount() {
    $this->_initSurferAdmin();
    return $this->surferAdmin->surfersAbsCount;
  }

  /**
  * Set surfer status to valid.
  *
  * @see surfer_admin::setValid Detailed description
  * @param string $surferId Unique 32-char guid
  * @param integer $valid Valid status value, 0 or 1
  * @return boolean Status
  */
  function setValid($surferId, $valid = 1) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->setValid($surferId, $valid);
  }

  /**
  * Creates a new surfer record.
  *
  * @see surfer_admin::createSurfer Detailed description
  * @param array $data Surfer's data
  * @param boolean $ignoreIllegal optional, default FALSE
  * @return mixed string|FALSE Status
  */
  function createSurfer($data, $ignoreIllegal = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->createSurfer($data, $ignoreIllegal);
  }

  /**
  * Save surfer formular data.
  *
  * Stores a surfer's basic data that was changed in the surfer form
  * permanently in the surfer database table.
  * Returns true if this works, otherwise false.
  *
  * @see surfer_admin::saveSurfer Detailed description
  * @access public
  * @param mixed $params Optional surfer data params, fallback $this->params
  * @return boolean Status
  */
  function saveSurfer($params = NULL) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->saveSurfer($params);
  }

  /**
  * Save surfer basic data.
  *
  * @see surfer_admin::saveSurferData Detailed description
  * @access public
  * @param string $surferId Unique 32-char surfer idx
  * @param array $params Additional data
  * @return boolean Status
  */
  function saveSurferData($surferId, $params) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->saveSurferData($surferId, $params);
  }

  /**
  * Load surfer groups.
  *
  * @see surfer_admin::loadGroups Detailed description
  * @access public
  * @return array $result Group list
  */
  function loadGroups() {
    $this->_initSurferAdmin();
    $result = array();
    if ($this->surferAdmin->loadGroups()) {
      $result = $this->surferAdmin->groupList;
    }
    return $result;
  }

  /**
  * Search surfers.
  *
  * @see existID() for behavior on blocked surfers
  * @see surfer_admin::searchSurfers Detailed description
  * @access public
  * @param string $pattern Search pattern
  * @param boolean $handleOnlyset to TRUE if you only want to search in surfer handles
  * @param boolean $includeBlocked Seach in blocked surfers too
  * @param string $orderBy Order search results by a given (NULL = disabled)
  * @return array $result Surfers data (id, handle, email, givenname, surfname)
  */
  function searchSurfers($pattern, $handleOnly = FALSE,
                         $includeBlocked = FALSE, $orderBy = 'surfer_handle') {
    $this->_initSurferAdmin();
    return $this->surferAdmin->searchSurfers(
      $pattern, $handleOnly, $includeBlocked, $orderBy
    );
  }

  /**
  * Simple surfer handle search.
  *
  * Useful if you only need a plain list of handles that match an SQL search pattern.
  *
  * @see surfer_admin::searchSurfers Detailed description
  * @access public
  * @param string $pattern Surfer handle (part)
  * @return array Matching surfer handles
  */
  function searchHandlesSimple($pattern) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->searchHandlesSimple($pattern);
  }

  /**
  * Block a surfer contact.
  *
  * @see surfer_admin::blockSurfer Detailed description
  * @access public
  * @param string $surferId The blocking surfer by id
  * @param string $contactId The surfer to be (un-)blocked by id
  * @param boolean $block Add or remove block
  */
  function blockSurfer($surferId, $contactId, $block = TRUE) {
    $this->_initSurferAdmin();
    $this->surferAdmin->blockSurfer($surferId, $contactId, $block);
  }

  /**
  * Bookmark a surfer contact.
  *
  * @see surfer_admin::bookmarkSurfer Detailed description
  * @access public
  * @param string $surferId The bookmarking surfer by id
  * @param string $contactId The surfer to be (un-)bookmarked by id
  * @param boolean $bookmark Add or remove bookmark
  */
  function bookmarkSurfer($surferId, $contactId, $bookmark = TRUE) {
    $this->_initSurferAdmin();
    $this->surferAdmin->bookmarkSurfer($surferId, $contactId, $bookmark);
  }

  /**
  * Get list of blocked surfer contacts by id.
  *
  * @see surfer_admin::getBlocks Detailed description
  * @access public
  * @param string $surferId Unqiue 32-char surfer id
  * @return array $blocks Blocked surfer contact ids
  */
  function getBlocks($surferId) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getBlocks($surferId);
  }

  /**
  * Get list of bookmarked surfer contacts.
  *
  * @see surfer_admin::getBookmarks Detailed description
  * @access public
  * @param string $surferId Unqiue 32-char surfer id
  * @return array $bookmarks Bookmarked surfer contact ids
  */
  function getBookmarks($surferId) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getBookmarks($surferId);
  }

  /**
  * Check whether a surfer has blocked a specific surfer contact.
  *
  * @see surfer_admin::isBlocked Detailed description
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $contactId Unique 32-char surfer id (contact)
  * @return boolean Status (blocked)
  */
  function isBlocked($surferId, $contactId) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->isBlocked($surferId, $contactId);
  }

  /**
  * Check whether a surfer has bookmarked a specific surfer contact.
  *
  * @see surfer_admin::isBookmarked Detailed description
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $contactId Unique 32-char surfer id (contact)
  * @return boolean Status (bookmarked)
  */
  function isBookmarked($surferId, $contactId) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->isBookmarked($surferId, $contactId);
  }

  /**
  * Get surfer's online status.
  *
  * @see surfer_admin::getOnlineStatus Detailed description
  * @access public
  * @param mixed string|array $surferIds Unique 32-char surfer id(s)
  * @return mixed integer|array Surfer(s)' status
  */
  function getOnlineStatus($surferIds) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getOnlineStatus($surferIds);
  }

  /**
  * Get dynamic profile data.
  *
  * @see surfer_admin::getDynamicData Detailed description
  * @access public
  * @param mixed array|string $surferIds Unique 32-char surfer id(s)
  * @param mixed array|string|int $fields Dynamic data fields
  * @return mixed array|string|NULL Dynamic data
  */
  function getDynamicData($surferIds, $fields) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getDynamicData($surferIds, $fields);
  }

  /**
  * Get amount of dynamic data fields with contents.
  *
  * @see surfer_admin::getDynamicDataCount Detailed description
  * @access public
  * @param mixed array|string $surferIds Unique 32-char surfer id
  * @param mixed array|string $fields Dynamic data field names
  * @return mixed array|integer|NULL Data count(s) or empty
  */
  function getDynamicDataCount($surferIds, $fields) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getDynamicDataCount($surferIds, $fields);
  }

  /**
  * Delete dynamic profile data field(s).
  *
  * @see surfer_admin::deleteDynamicData Detailed description
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param mixed string|array $fields Dynamic data field name(s)
  */
  function deleteDynamicData($surferId, $fields) {
    $this->_initSurferAdmin();
    $this->surferAdmin->deleteDynamicData($surferId, $fields);
  }

  /**
  * Set dynamic profile data
  *
  * @see surfer_admin::setDynamicData Detailed description
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param mixed string|array $fields Dynamic data field name(s)
  * @param mixed string|NULL $value Dynamic data field value(s)
  * @return integer Count of added entries
  */
  function setDynamicData($surferId, $fields, $value = NULL) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->setDynamicData($surferId, $fields, $value);
  }

  /**
  * Check dynamic data.
  *
  * @see surfer_admin::checkDynamicData Detailed description
  * @access public
  * @param mixed string|array $fields Dynamic data field name(s)
  * @param mixed string|NULL $value Dynamic data field value
  * @return mixed boolean|array Checked status result(s)
  */
  function checkDynamicData($fields, $value = NULL) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->checkDynamicData($fields, $value);
  }

  /**
  * Get dynamic data edit fields.
  *
  * @see surfer_admin::getDynamicEditFields Detailed description
  * @access public
  * @param mixed NULL|int|string|array $fields Field names
  * @param string $prefix Prefix for field names
  * @param integer $lng Language id
  * @return array $dynamicEditFields Fields configuration
  */
  function getDynamicEditFields($fields, $prefix = '', $lng = 0) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getDynamicEditFields($fields, $prefix, $lng);
  }

  /**
  * Get data field names.
  *
  * @see surfer_admin::getDataFieldNames Detailed description
  * @access public
  * @param integer|array $class Surfer data class filter value
  * @return array $fieldNames Data field names
  */
  function getDataFieldNames($class = 0) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getDataFieldNames($class);
  }

  /**
  * Find surfers by dynamic data.
  *
  * @see surfer_admin::findSurfersByDynamicData Detailed description
  * @access public
  * @param array $criteria Filter criteria for dynamic data
  * @param string $mode Mode to combine results (AND|OR)
  * @param array $totalResult Combined result
  */
  function findSurfersByDynamicData($criteria, $mode = 'AND') {
    $this->_initSurferAdmin();
    return $this->surferAdmin->findSurfersByDynamicData($criteria, $mode);
  }

  /**
  * Get search form for surfers by basic and dynamic profile data.
  *
  * @see surfer_admin::getSearchDynamicForm Detailed description
  * @access public
  * @param object xsl_layout &$layout Papaya layout object
  * @param string $paramName Parameter group name
  * @param mixed NULL|array $hidden Hidden formular values
  * @param string $title Formular title
  */
  function getSearchDynamicForm(&$layout, $paramName = '', $hidden = NULL, $title = '') {
    $this->_initSurferAdmin();
    $this->surferAdmin->getSearchDynamicForm($layout, $paramName, $hidden, $title);
  }

  /**
  * Show surfer search results.
  *
  * @see surfer_admin::showSurferResults Detailed description
  * @access public
  * @param object xsl_layout &$layout Papaya layout object
  * @param boolean $links Show links
  * @param mixed NULL|array $params Additional parameters
  * @param boolean $returnResults Return values or not
  * @param array $result Surfer results or empty
  */
  function showSurferResults(&$layout, $params = NULL, $returnResults = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->showSurferResults($layout, FALSE, $params, $returnResults);
  }

  /**
  * Add a contact (request) from one surfer to another.
  *
  * @see surfer_admin::addContactRequest Detailed description
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $contactId Unique 32-char surfer id (contact)
  * @param int $status Contact status, i.e. pending (see status constants)
  * @return boolean Status (added)
  */
  function addContactRequest($surferId, $contactId) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->addContactRequest($surferId, $contactId);
  }

  /**
  * Add a forced surfer contact.
  *
  * @see surfer_admin::forceContact Detailed description
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $contactId Unique 32-char surfer id (contact)
  * @return boolean Status (added)
  */
  function forceContact($surferId, $contactId) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->forceContact($surferId, $contactId);
  }

  /**
  * Remove a contact request from one surfer to another.
  *
  * @see surfer_admin::removeContactRequest Detailed description
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param string $contactId Unique 32-char surfer id (contact)
  * @param boolean $bidirectional Flag to remove contact in both directions
  * @return boolean Status (deleted)
  */
  function removeContactRequest($surferId, $contactId, $bidirectional = FALSE) {
    $this->_initSurferAdmin();
    return
      $this->surferAdmin->removeContactRequest($surferId, $contactId, $bidirectional);
  }

  /**
  * Accept a contact request from one surfer to another.
  *
  * @see surfer_admin::acceptContact Detailed description
  * @access public
  * @param string $surferId 32-char surfer id
  * @param string $contactId 32-char surfer id (contact)
  * @param boolean $bidirectional Flag to accept contact in both directions
  * @return boolean Status (accepted)
  */
  function acceptContact($surferId, $contactId, $bidirectional = FALSE) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->acceptContact($surferId, $contactId, $bidirectional);
  }

  /**
  * Remove a surfer contact.
  *
  * @see surfer_admin::removeContact Detailed description
  * @access public
  * @param string $surferId 32-char surfer id
  * @param string $contactId 32-char surfer id (contact)
  * @return boolean Status (removed)
  */
  function removeContact($surferId, $contactId) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->removeContact($surferId, $contactId);
  }

  /**
  * Get default contact for surfers.
  *
  * @see surfer_admin::getDefaultContact Detailed description
  * @access public
  * @return mixed string|NULL Default surfer contact value
  */
  function getDefaultContact() {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getDefaultContact();
  }

  /**
   * Get a link to manage a certain surfer in backend.
   *
   * @see surfer_admin::getBackendSurferLink Detailed description
   * @access public
   * @param string $surferId Unique 32-char surfer id
   * @return string $link Link to surfer
   */
  function getBackendSurferLink($surferId) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getBackendSurferLink($surferId);
  }

  /**
  * Get password hash
  *
  * @see surfer_admin::getPasswordHash Detailed description
  * @access public
  * @param string $password Password input
  * @return string Password hash
  */
  function getPasswordHash($password) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getPasswordHash($password);
  }

  /**
  * Get profile field categories.
  *
  * Retrieve a list of all valid dynamic profile category ids.
  *
  * @access public
  * @return mixed array $classes Category classes or empty
  */
  function getProfileDataClasses() {
    $classes = NULL;
    $this->_initSurferAdmin();
    $sql = "SELECT surferdataclass_id
              FROM %s
          ORDER BY surferdataclass_id ASC";
    $sqlData = array($this->surferAdmin->tableDataClasses);
    if ($res = $this->surferAdmin->databaseQueryFmt($sql, $sqlData)) {
      while ($class = $res->fetchField()) {
        if (!$classes) {
          $classes = array();
        }
        array_push($classes, $class);
      }
    }
    return $classes;
  }

  /**
  * Get profile data class titles.
  *
  * Retrieves a list of titles for a certain profile category id
  * as a frontend language id => title array.
  *
  * @access public
  * @param integer $class Class name
  * @return array $classTitles Titles list or empty
  */
  function getProfileDataClassTitles($class) {
    $classTitles = NULL;
    $this->_initSurferAdmin();
    $filter = str_replace(
      '%',
      '%%',
      $this->surferAdmin->databaseGetSQLCondition('surferdataclasstitle_classid', $class)
    );
    $sql = "SELECT surferdataclasstitle_classid,
                   surferdataclasstitle_lang,
                   surferdataclasstitle_name
              FROM %s
             WHERE ".$filter;
    $sqlData = array($this->surferAdmin->tableDataClassTitles);
    if ($res = $this->surferAdmin->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!$classTitles) {
          $classTitles = array();
        }
        $classTitles[$row['surferdataclasstitle_lang']] = $row['surferdataclasstitle_name'];
      }
    }
    return $classTitles;
  }

  /**
  * Get profile field names.
  *
  * Retrieve a list of all available dynamic profile field names.
  *
  * @access public
  * @param int $class Id of a profile category; empty = get all field names
  * @return array $fieldNames Field names or empty
  */
  function getProfileFieldNames($class = 0) {
    $fieldNames = NULL;
    $this->_initSurferAdmin();
    $sql = "SELECT surferdata_name, surferdata_class
              FROM %s";
    if ($class) {
      $sql .= " WHERE ".str_replace(
        '%',
        '%%',
        $this->surferAdmin->databaseGetSQLCondition('surferdata_class', $class)
      );
    }
    $sql .= " ORDER BY surferdata_name ASC";
    $sqlData = array($this->surferAdmin->tableData);
    if ($res = $this->surferAdmin->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!$fieldNames) {
          $fieldNames = array();
        }
        array_push($fieldNames, $row['surferdata_name']);
      }
    }
    return $fieldNames;
  }

  /**
  * Get profile data field titles.
  *
  * Retrieve a list of profile data field titles
  * as a language id => field title list.
  *
  * @todo Check meaning of $field parameter
  * @access public
  * @param string $field Surferdata name
  * @return array $fieldTitles Field titles or empty
  */
  function getProfileFieldTitles($field) {
    $fieldTitles = NULL;
    $this->_initSurferAdmin();
    $filter = str_replace(
      '%',
      '%%',
      $this->surferAdmin->databaseGetSQLCondition('s.surferdata_name', $field)
    );
    $sql = "SELECT s.surferdata_id, s.surferdata_name,
                   st.surferdatatitle_field,
                   st.surferdatatitle_lang,
                   st.surferdatatitle_title
              FROM %s AS s, %s AS st
             WHERE $filter
               AND s.surferdata_id=st.surferdatatitle_field";
    $sqlData = array($this->surferAdmin->tableData,
                     $this->surferAdmin->tableDataTitles);
    if ($res = $this->surferAdmin->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!$fieldTitles) {
          $fieldTitles = array();
        }
        $fieldTitles[$row['surferdatatitle_lang']] = $row['surferdatatitle_title'];
      }
    }
    return $fieldTitles;
  }

  /**
  * Get data from profile data field(s) for a surfer.
  *
  * Data will only be retrieved if it's either public by preset
  * or if the currently logged-in surfer is allowed to see it.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param mixed string, array, or NULL $fields One or more profile data field names
  * @return array $fieldValues Field values or empty
  * @deprecated ?
  */
  function getProfileData($profileSurferId, $fields = NULL) {
    $this->_initSurferAdmin();
    // First check out whether we've got a valid surfer
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $surferObj = &base_surfer::getInstance();
    if ($surferObj->isValid) {
      $surferId = $surferObj->surfer['surfer_id'];
    } else {
      $surferId = NULL;
    }

    // build filters
    $filter = array('sc.surfercontactdata_surferid' => $profileSurferId);

    if (!empty($fields)) {
      $filter['s.surferdata_name'] = $fields;
    }
    // Retrieve the public field(s) first
    $filterStr = str_replace(
      '%',
      '%%',
      $this->surferAdmin->databaseGetSQLCondition($filter)
    );
    $sql = "SELECT s.surferdata_id, s.surferdata_name,
                   s.surferdata_needsapproval,
                   sc.surfercontactdata_property,
                   sc.surfercontactdata_surferid,
                   sc.surfercontactdata_value
              FROM %s AS s, %s AS sc
             WHERE s.surferdata_available = 1
               AND s.surferdata_id = sc.surfercontactdata_property
               AND ".$filterStr;
    $sqlData = array($this->surferAdmin->tableData,
                     $this->surferAdmin->tableContactData);

    $fieldValues = NULL;
    if ($res = $this->surferAdmin->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!$fieldValues) {
          $fieldValues = array();
        }
        $fieldValues[$row['surferdata_name']] = $row['surfercontactdata_value'];
      }
    }
    // Return this as the end result if there's no valid surfer
    if (!$surferId) {
      return $fieldValues;
    }
    // If we're still here, retrieve the more complex fields with individual approval

    // add filters (more)
    $filter2 = array(
      'sp.surfercontactpublic_surferid' => $profileSurferId,
      'sp.surfercontactpublic_partner' => $surferId
    );
    $filterStr2 = str_replace(
      '%',
      '%%',
      $this->surferAdmin->databaseGetSQLCondition($filter2)
    );

    $sql = "SELECT s.surferdata_id, s.surferdata_name,
                   s.surferdata_needsapproval,
                   sc.surfercontactdata_property,
                   sc.surfercontactdata_surferid,
                   sc.surfercontactdata_value,
                   sp.surfercontactpublic_surferid,
                   sp.surfercontactpublic_partner,
                   sp.surfercontactpublic_data,
                   sp.surfercontactpublic_public
              FROM %s AS s, %s AS sc, %s AS sp
             WHERE $filterStr
               AND s.surferdata_needsapproval=1
               AND s.surferdata_id=sc.surfercontactdata_property
               AND $filterStr2
               AND sp.surfercontactpublic_data = s.surferdata_id
               AND sp.surfercontactpublic_public = 1";
    $sqlData = array($this->surferAdmin->tableData,
                     $this->surferAdmin->tableContactData,
                     $this->surferAdmin->tableContactPublic);
    if ($res = $this->surferAdmin->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!$fieldValues) {
          $fieldValues = array();
        }
        $fieldValues[$row['surferdata_name']] = $row['surfercontactdata_value'];
      }
    }
    return $fieldValues;
  }

  /**
  * Is valid surfer URI?
  *
  * Checks whether a 'user:id' URI or simple surfer id refers to
  * an existing and valid surfer.
  *
  * @access public
  * @param string $surferURI Surfer URI 'user:id'
  * @return boolean Status (is valid)
  */
  function isValidSurfer($surferURI) {
    $this->_initSurferAdmin();
    // Reject invalid URIs by returning FALSE
    if (preg_match("/^([a-z]+):(.*)$/", $surferURI, $matchData)) {
      if ($matchData[1] != 'user') {
        return FALSE;
      }
      // We're still here, so we can safely use the part behind the : as surfer id
      $surferId = $matchData[2];
    } else {
      // There is no :-pattern, so we can assume the whole argument is a surfer id
      $surferId = $surferURI;
    }
    // Try to load the corresponding surfer
    $surfer = $this->surferAdmin->loadSurfer($surferId);
    if ($surfer && $surfer['surfer_valid']) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
  * Get contacts.
  *
  * Returns a list of contact ids (or id => timestamp arrays) for a surfer's contacts.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param boolean $withTimestamp Get time stamp
  * @param mixed NULL|int $limit Set limit for database results
  * @param mixed NULL|int $offset Set offset for database results
  * @param string $sort Sort direction ('ASC' OR 'DESC')
  * @return array $results Contacts data
  */
  function getContacts($surferId, $withTimestamp = FALSE, $limit = NULL,
                       $offset = NULL, $sort = 'ASC') {
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    if ($withTimestamp) {
      $results = $contactManager->getContactsWithTimestamp($limit, $offset, '', $sort);
    } else {
      $results = $contactManager->getContacts($limit, $offset, 2, '', $sort);
    }
    $this->contactsAbsCount = $contactManager->getAbsCount();
    return $results;
  }

  /**
  * Search by string for contacts.
  *
  * Will return an array of results, each result looks like.
  * [surfer_id => [surfer_handle, surfer_givenname, surfer_surname, surfercontact_status]]
  *
  * @param string $surferId Search the contacts of this surfer id
  * @param string $pattern Search string
  * @param boolean $handleOnly Flag to search in handles only
  * @param integer $type Search for a specific connection status type
  * @param integer $limit Set limit for database results
  * @param integer $offset Set offset for database results
  * @param string $sort Sort direction ('ASC' OR 'DESC')
  * @return array $results Contacts data
  */
  function searchContacts($surferId, $pattern, $handleOnly = FALSE,
                          $type = SURFERCONTACT_STATUS_ACCEPTED,
                          $limit = NULL, $offset = NULL, $sort = 'ASC') {
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    $results = $contactManager->searchContacts(
      $pattern, $handleOnly, $type, $limit, $offset, $sort
    );
    $this->contactsAbsCount = $contactManager->getAbsCount();
    return $results;
  }

  /**
  * Get amount of contacts.
  *
  * Returns the amount of the provided surfer's contacts.
  *
  * @todo Check / optimize method name meaning ("number" <> "amount")
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @return integer Contacts amount
  */
  function getContactNumber($surferId, $type = 2) {
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    return $contactManager->getContactNumber($type);
  }

  /**
   * Get the absolute amount of last contact operation.
   *
   * @return integer Contacts amount
   */
  function getContactsAbsCount() {
    return $this->contactsAbsCount;
  }

  /**
  * Is contact?
  *
  * Returns contact status between two surfers.
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
  * an associative array of surfer id => contact status pairs.
  *
  * In many cases you only want to know whether surfers have got direct contact,
  * so the method only searches for indirect contacts if you set the third,
  * optional parameter to TRUE.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param mixed string|array $contactId Unique 32-char surfer id(s)
  * @param boolean $searchIndirect Search indirect
  * @param boolean $requestDirection Contact request direction (surfer <> surfer)
  * @return mixed integer|array Is (multible) contact
  */
  function isContact($surferId, $contactId, $searchIndirect = FALSE, $requestDirection = FALSE) {
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    return $contactManager->isContact($contactId, $searchIndirect, $requestDirection);
  }

  /**
  * Get contact requests sent.
  *
  * Returns an array of surfer IDs
  * (or an array of surfer_id => timestamp arrays)
  * for surfers to which the given surfer has sent contact requests.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param boolean $withTimestamp With time stamp
  * @param mixed NULL|int $limit Set limit for database records
  * @param mixed NULL|int $offset Set offset for database records
  * @param string $sort Sort direction ('ASC' OR 'DESC')
  * @return array $results Contact requests data
  */
  function getContactRequestsSent($surferId, $withTimestamp = FALSE, $limit = NULL,
                                  $offset = NULL, $sort = 'ASC') {
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    $results = $contactManager->getContactRequestsSent(
      $surferId, $withTimestamp, $limit, $offset, $sort);
    $this->contactsAbsCount = $contactManager->getAbsCount();
    return $results;
  }

  /**
  * Get contact requests received.
  *
  * Returns an array of surfer IDs
  * (or an array of surfer_id => timestamp arrays)
  * for surfers who have sent contact requests to the current surfer.
  *
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @param boolean $withTimestamp With timestamp
  * @param mixed NULL|int $limit Set limit for database records
  * @param mixed NULL|int $offset Set offset for database records
  * @param string $sort Sort direction ('ASC' OR 'DESC')
  * @return array $results Contact requests data
  */
  function getContactRequestsReceived($surferId, $withTimestamp = FALSE, $limit = NULL,
                                      $offset = NULL, $sort = 'ASC') {
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    $results = $contactManager->getContactRequestsReceived(
      $surferId, $withTimestamp, $limit, $offset, $sort);
    $this->contactsAbsCount = $contactManager->getAbsCount();
    return $results;
  }

  /**
  * Get number of contact requests sent
  *
  * Simply returns the number of contact requests
  * that the current surfer has sent to other surfers
  *
  * @todo Check / optimize method name meaning ("number" <> "amount")
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @return array Amounts of contact requests sent
  */
  function getContactRequestsSentNumber($surferId = '') {
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    return $contactManager->getContactRequestsSentNumber();
  }

  /**
  * Get number of contact requests received.
  *
  * Simply returns the number of contact requests
  * that other surfers have sent to the current surfer.
  *
  * @todo Check / optimize method name meaning ("number" <> "amount")
  * @access public
  * @param string $surferId Unique 32-char surfer id
  * @return array Amounts of contact requests received
  */
  function getContactRequestsReceivedNumber($surferId = '') {
    include_once(dirname(__FILE__).'/base_contacts.php');
    $contactManager = contact_manager::getInstance($surferId);
    return $contactManager->getContactRequestsReceivedNumber();
  }

  /**
  * Get avatar image / thumnail.
  *
  * The return value is an empty string if there no avatar can be retrieved
  *
  * @see surfer_admin::getAvatar Detailed description
  * @access public
  * @param string $surferId
  * @param int $size optional
  * @param boolean $useDefault optional default TRUE
  * @param string $mode optional default 'max'
  * @return string Avatar thumbnail location
  */
  function getAvatar($surferId, $size = 0, $useDefault = TRUE, $mode = 'max') {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getAvatar($surferId, $size, $useDefault, $mode);
  }

  /**
   * Get avatar media id.
   * Returns the media id of the specified surfer id(s).
   *
   * @see surfer_admin::getAvatarId Detailed description
   * @param string|array $surferIds Single or multiple surfer id(s).
   * @return string|array Avatar media id(s)
   */
  function getAvatarId($surferIds) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getAvatarId($surferIds);
  }

  /**
   * Initiate a new contact manager.
   *
   * @param string $surferId Unique 32-char surfer id
   * @param integer $max Maximum path length
   * @return object $obj contact_manager object
   */
  function getNewContactManager($surferId = '', $max = 3) {
    include_once(dirname(__FILE__).'/base_contacts.php');
    $obj = contact_manager::getInstance($surferId, $max);
    return $obj;
  }

  /**
   * Returns the amount of valid surfers currently active.
   *
   * @see surfer_admin::getLastActiveSurfers Detailed description
   * @access public
   * @param integer $decay Time frame in seconds
   * @param integer $limit Limit for database results
   * @param integer $offset Offset for database results
   * @return array Active surfers data and absolute surfers count
   */
  function getLastActiveSurfers($decay, $limit, $offset = 0) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getLastActiveSurfers($decay, $limit, $offset);
  }

  /**
   * Load surfers with a more complex statement see table papaya_surfers for handling.
   *
   * @see surfer_admin::loadSurfersComplex Detailed description
   * @access public
   * @param array $filter Filter array for sql conditions
   * @param string $order Table field key identifier to sort by
   * @param string $by Sort direction ('ASC' or 'DESC')
   * @param integer limit Set limit for database results
   * @param integer $offset Set offset for database results
   * @return array $results List with complex surfers data
   */
  function loadSurfersComplex($filter, $order = NULL, $by = NULL, $limit = NULL,
    $offset = NULL) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->loadSurfersComplex($filter, $order, $by, $limit, $offset);
  }

  /**
   * Load the lastest registered surfers.
   *
   * @see surfer_admin::getLatestRegisteredSurfers Detailed description
   * @access public
   * @param integer $timeOffset Time offset
   * @param integer $limit Set limit for database results
   * @param integer $offset Set offset for database results
   * @return $results List with latest registered surfers data
   */
  function getLatestRegisteredSurfers($timeOffset = NULL, $number = NULL,
    $offset = NULL) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getLatestRegisteredSurfers($timeOffset, $number, $offset);
  }

  /**
   * Returns the latest online surfers.
   *
   * @see surfer_admin::getOnlineSurfers Detailed description
   * @access public
   * @param string $sortBy optional use: handle, surname, email, lastaction
   * @param string $sortDirection Set sort direction (asc, desc)
   * @param integer $limit Set limit for database results set
   * @param integer $offset Set offset for database results set
   * @return array Amount of found results and data of online surfers
   */
  function getOnlineSurfers($sortBy = NULL, $sortDirection = NULL, $limit = NULL, $offset = NULL) {
    $this->_initSurferAdmin();
    return $this->surferAdmin->getOnlineSurfers($sortBy, $sortDirection, $limit, $offset);
  }

  /**
  * Get currently logged in surfer
  *
  * @return base_surfer
  */
  function getCurrentSurfer() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $currentSurfer = &base_surfer::getInstance();
    return $currentSurfer;
  }

  /**
  * Get the current list of surfer groups
  *
  * @return array
  */
  function getGroupsList() {
    $this->_initSurferAdmin();
    $groups = array();
    if ($this->surferAdmin->loadGroups()) {
      $groups = $this->surferAdmin->groupList;
    }
    return $groups;
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
    $this->_initSurferAdmin();
    return $this->surferAdmin->getPermCombo($name, $field, $data, $paramName);
  }
}
?>