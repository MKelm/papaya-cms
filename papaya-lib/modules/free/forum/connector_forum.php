<?php
/**
* Forum Connector
*
* Guid: d7af5f9eee4babcc8271aa2bf606bdcf
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
* @version $Id: connector_forum.php 35622 2011-04-05 14:53:54Z kelm $
*/

/**
* Load necessary libraries
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_connector.php');

/**
* Papaya Forum - Connector Module
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/
class connector_forum extends base_connector {

  /**
  * Instance of the base_forum class
  * @var base_forum
  */
  private $_baseForum = NULL;

  /**
  * Instance of the admin_forum class
  * @var admin_forum
  */
  private $_administration = NULL;

  /**
  * Local caching of multiple used surfer information
  */
  protected $_surfer = NULL;

  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
  * Category
  **********************/

  /**
  * Find the forum id by given mode
  *
  * @param array $mode
  * @return integer Identifier of the corresponding category
  */
  public function getCategoryIdByMode($mode) {
    if ($mode['mode'] == 'categ') {
      return $mode['id'];
    } else {
      $board = $this->loadBoard($mode['id']);
      return $board['forumcat_id'];
    }
  }


  /**
  * Forum
  **********************/

  /**
  * Create a forum in the forum database.
  *
  * The parent refers to a forum category.
  * Sypnosis:
  *
  *  $uri:
  *    * pageId (default)
  *    * page:pageId
  *    * module:GUID/articleId
  *
  * @param integer $categoryId  Identifier of the category the forum shall be related to
  * @param string $title
  * @param string $description
  * @param integer $uri
  * @return integer|boolean id of new record, else FALSE
  *
  * @see base_forum::addForum
  */
  public function addForum($categoryId, $title = '', $description = '', $uri = 0) {
    $forum = $this->getBaseForumObject();
    $page = $this->decodeURI($uri);
    return $forum->addForum(
      $categoryId, $title, $description, $page['page_id'], $page['page_prefix']);
  }

  /**
  * Get the identfier of a forum
  *
  * Sypnosis:
  *
  *  $uri:
  *    * pageId (default)
  *    * page:pageId
  *    * module:GUID/articleId
  *
  * @param integer $categoryId
  * @param string $uri
  * @return integer|boolean Identifier of the forum, else FALSE
  *
  * @see base_forum::getForumByPageId()
  */
  public function getForumByURI($categoryId, $uri) {
    $forum = $this->getBaseForumObject();
    $page = $this->decodeURI($uri);
    return $forum->getForumByPageId($categoryId, $page['page_id'], $page['page_prefix']);
  }

  /**
  * Load forums of category
  *
  * @param integer $forumId Identifier of a forum
  * @return boolean
  */
  public function loadBoard($forumId) {
    $forum = $this->getBaseForumObject();
    return $forum->loadBoard($forumId);
  }


  /**
  * Entry
  **********************/

  /**
  * Add a post
  *
  * Structure of $data:
  * <code>
  *  array (
  *    entry_text => string
  *    entry_subject => string
  *    entry_path => string
  *    entry_pid => string  // thread
  *    [entry_ip => string]
  *  )
  * </code>
  *
  * @param array $entryData List of information to be stored in entry
  * @param boolean $richtextEnabled
  * @return integer|boolean Id of the new entry, else FALSE
  *
  * @see base_forum::getCurrentSurfer()
  * @see base_forum::addEntry()
  */
  public function addEntry($data, $richtextEnabled) {
    $forum = $this->getBaseForumObject();
    $forum->getCurrentSurfer();
    return $forum->addEntry($data, $richtextEnabled);
  }

  /**
  * Get thread identifier
  *
  * Sypnosis:
  *
  *  $uri:
  *    * pageId (default)
  *    * page:pageId
  *    * module:GUID/articleId
  *
  * @param integer $forumId
  * @param string $uri
  * @return integer|boolean Identifier of the thread, else FALSE
  */
  public function getThreadByURI($forumId, $uri) {
    $forum = $this->getBaseForumObject();
    return $forum->getThreadByPageId($forumId, $uri);
  }

  /**
  * Get a list of entries identified by its category and URI
  *
  * Sypnosis:
  *
  *  $uri:
  *    * pageId (default)
  *    * page:pageId
  *    * module:GUID/articleId
  *
  * @param integer $categoryId Indentifier of the category the forum is attached to
  * @param string $uri Identifier of the forum
  * @param integer $limit Amount of entries to be returned
  * @param integer $offset Amount of entries to be skipped from the result
  * @param boolean $absCount if TRUE, this absolute amount of found records
  *                          will be attached to result array
  * @param string $order optional, 'ASC' or 'DESC', default 'DESC'
  * @return array Range of a entry set
  */
  public function getEntries($categoryId, $uri, $limit = 0, $offset = 0, $absCount = FALSE,
      $order = 'DESC') {
    $forum = $this->getBaseForumObject();
    return $forum->loadLastEntriesByForum(
      $this->getForumByURI($categoryId, $uri),
      $limit,
      $offset,
      $absCount,
      $order
    );
  }

  /**
  * Mark an entry as blocked
  *
  * @param integer $entryId
  * @return boolean TRUE on success, else FALSE
  */
  public function blockEntry($entryId) {
    $forum = $this->getBaseForumObject();
    return $forum->blockEntry($entryId);
  }

  /**
  * Remove blocked mark of an entry
  *
  * @param integer $entryId
  * @return boolean TRUE on success, else FALSE
  */
  public function unblockEntry($entryId) {
    $forum = $this->getBaseForumObject();
    return $forum->unblockEntry($entryId);
  }


  /**
  * Surfer Methods
  **********************/

  /**
  * Get information about the current logged in surfer
  * @return unknown_type
  */
  public function getCurrentSurfer() {
    if (is_null($this->_surfer)) {
      $forum = $this->getBaseForumObject();
      $forum->getCurrentSurfer();
      $this->_surfer = $forum->surferObj;
    }
    return $this->_surfer;
  }

  /**
  * Count the number of entries in a given forum
  *
  * @param integer $forumId
  * @param boolean $publicOnly leave out blocked entries? optional, default TRUE
  * @return integer number of entries
  */
  public function countForumEntries($forumId, $publicOnly = TRUE) {
    $forum = $this->getBaseForumObject();
    return $forum->countForumEntries($forumId, $publicOnly);
  }

  /**
  * Administration
  **********************/

  /**
  * Get forum combo
  *
  * @param string $paramName Identifier of the related parameters in query string
  * @param string $field Fieldname of the parameter
  * @param string $data List of information to be set in the combobox
  * @return string HTML representing a dropdown menu
  */
  public function getForumCategoryCombo($paramName, $field, $data) {
    $forumAdminObject = $this->getAdminObject();
    return $forumAdminObject->getForumCombo($paramName, $field, $data);
  }


  /***************************************************************************/
  /** Helper / Instances                                                     */
  /***************************************************************************/

  /**
  * Rate given text to be or not to be Spam
  *
  * @param string $text String to be rated
  * @return boolean TRUE, if the text is not rated a spam, else FALSE
  */
  public function checkSpam($text = '') {
    $forum = $this->getBaseForumObject();
    return $forum->checkSpam($text);
  }

  /**
  * Decode given URI to an identifier and a type
  *
  * @param string $uri
  * @return array
  */
  protected function decodeURI($uri) {
    $result = array(
      'page_id' => 0,
      'page_prefix' => ''
    );

    if (is_numeric($uri)) {
      $result['page_id'] = $uri;
    } elseif (!empty($uri)) {
      list($prefix, $id) = split(':', $uri, 2);
      switch($prefix) {
      case 'module':
        // module:guid/id
        list($guid, $id) = split('/', $id, 2);
        $result = array(
          'page_id' => $id,
          'page_prefix' => $prefix.':'.$guid
        );
        break;
      case 'page':
        // page:id
        $result = array(
          'page_id' => $id,
          'page_prefix' => $prefix.':'
        );
        break;
      }
    }
    return $result;
  }

  /**
  * Check whether there was a 'rejected attack string' error
  *
  * @return boolean TRUE if the error occurred, FALSE otherwise
  */
  public function getRejectedAttackStringError() {
    $forum = $this->getBaseForumObject();
    return $forum->rejectedAttackStrings;
  }

  /**
  * Check whether there was an 'entry too long' error
  *
  * @return boolean TRUE if the error occurred, FALSE otherwise
  */
  public function getEntryTooLongError() {
    $forum = $this->getBaseForumObject();
    return $forum->entryTooLong;
  }

  /**
  * Instantiate the base_forum object
  *
  * @return base_forum
  */
  public function getBaseForumObject() {
    if (!(isset($this->_baseForum) && $this->_baseForum instanceof base_forum)) {
      include_once(dirname(__FILE__).'/base_forum.php');
      $this->_baseForum = new base_forum();
      $this->_baseForum->setOwnerObject($this->parentObj);
    }
    return $this->_baseForum;
  }

  /**
  * Override or preset the used instance of the base_forum class
  * @param $baseForumObject
  * @return unknown_type
  */
  public function setBaseForumObject(base_forum $baseForumObject) {
    $this->_baseForum = $baseForumObject;
  }

  /**
  * Instantiate a admin_forum object
  *
  * @return admin_forum
  */
  public function getAdminObject() {
    if (!(isset($this->_administration) && $this->_administration instanceof admin_forum)) {
      include_once(dirname(__FILE__).'/admin_forum.php');
      $this->_administration = new admin_forum();
    }
    return $this->_administration;
  }

  /**
  * Set / override the used instance of admin_forum object
  *
  * @param $adminObject
  * @return admin_forum
  */
  public function setAdminObject(admin_forum $adminObject) {
    $this->_administration = $adminObject;
  }

  /**
  * Set the forum owner module object, e.g. to determine a language id on spam check
  *
  * @param object $owner
  */
  public function setOwnerObject($owner) {
    $this->getBaseForumObject()->setOwnerObject($owner);
  }
}