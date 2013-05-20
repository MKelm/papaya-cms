<?php
/**
* Basic object for dr Stickers providing database read and write access
*
* @package Papaya-Modules
* @subpackage Free-Stickers
* @version $Id: base_stickers.php 36882 2012-03-26 09:21:48Z yurtsever $
*/

/**
* Basic class object
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Basic object for dr Stickers providing database read and write access
*
* @package Papaya-Modules
* @subpackage Free-Stickers
*/
class base_stickers extends base_db {

  /**
  * Constructor, initalizes table name attributes
  */
  function __construct() {
    parent::__construct();
    $this->tableSticker = PAPAYA_DB_TABLEPREFIX.'_sticker';
    $this->tableCollections = PAPAYA_DB_TABLEPREFIX.'_sticker_collections';
  }

  /**
  * PHP4 constructor
  */
  function base_stickers() {
    base_stickers::__construct();
  }

  /**
  * Adds a sticker to the database
  *
  * @param integer $collectionId ID of collection to add the sticker to
  * @param array $data sticker data
  * @return stickerId on success, otherwise FALSE
  */
  function addSticker($collectionId, &$data) {
    $data = array(
      'collection_id' => $collectionId,
      'sticker_text' => $data['sticker_text'],
      'sticker_image' => empty($data['sticker_image']) ? '' : $data['sticker_image'],
      'sticker_author' => empty($data['sticker_author']) ? '' : $data['sticker_author'] ,
    );
    if ($stickerId = $this->databaseInsertRecord($this->tableSticker, 'sticker_id', $data)) {
      return $stickerId;
    }
    return FALSE;
  }

  /**
  * This method adds a collection to the database.
  *
  * @param string $title title of collection
  * @param string $description description for collection
  * @return collectionId on success, otherwise FALSE
  */
  function addCollection($title, $description) {
    $data = array(
      'collection_title' => (string)$title,
      'collection_description' => (string)$description,
    );
    if ($collectionId =
          $this->databaseInsertRecord($this->tableCollections, 'collection_id', $data)) {
      return $collectionId;
    }
    return FALSE;
  }

  /**
  * Updates data for an existing sticker.
  *
  * @param integer $collectionId ID of collection
  * @param integer $stickerId sticker ID
  * @param array $data sticker data
  */
  function updateSticker($collectionId, $stickerId, &$data) {
    if ($collectionId > 0 && $stickerId > 0) {
      $data = array(
        'collection_id' => $collectionId,
        'sticker_text' => $data['sticker_text'],
        'sticker_image' => $data['sticker_image'],
        'sticker_author' => $data['sticker_author'],
      );
      $condition = array(
        'sticker_id' => $stickerId,
      );
      return (FALSE !== $this->databaseUpdateRecord($this->tableSticker, $data, $condition));
    }
  }

  /**
  * This method updates an existing collection
  *
  * @param integer $collectionId id of collection to update
  * @param string $title new title for collection
  * @param string $description new description for collection
  * @return boolean TRUE on success, otherwise FALSE
  */
  function updateCollection($collectionId, $title, $description) {
    if ($collectionId > 0 && $title != '') {
      $data = array(
        'collection_title' => (string)$title,
        'collection_description' => (string)$description,
      );
      $condition = array(
        'collection_id' => $this->params['col_id'],
      );
      return (FALSE !== $this->databaseUpdateRecord($this->tableCollections, $data, $condition));
    }
  }

  /**
  * Removes a sticker from the database
  *
  * @param integer $stickerId ID of sticker to remove
  * @return boolean TRUE on success, otherwise FALSE
  */
  function deleteSticker($stickerId) {
    if ($stickerId > 0) {
      $condition = array('sticker_id' => $stickerId);
      return (FALSE !== $this->databaseDeleteRecord($this->tableSticker, $condition));
    }
  }

  /**
  * This method deletes a collection and its stickers
  *
  * @param integer $collectionId Id of collection to delete
  * @return boolean TRUE on success, otherwise FALSE
  */
  function deleteCollection($collectionId) {
    if ($collectionId > 0) {
      $condition = array('collection_id' => $collectionId);
      // first we must delete all stickers in that collection
      if (FALSE !== $this->databaseDeleteRecord($this->tableSticker, $condition)) {
        // then we can delete the collection itself
        return (FALSE !== $this->databaseDeleteRecord($this->tableCollections, $condition));
      }
    }
  }

  /**
  * Fetches a list of all available collections.
  *
  * @return array $result list of collections
  */
  function getCollections() {
    $result = FALSE;
    $sql = "SELECT collection_id, collection_title, collection_description
              FROM %s
           ";
    $params = array($this->tableCollections);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $result = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['collection_id']] = $row;
      }
    }
    return $result;
  }

  /**
  * This method retrieves the number of stickers of a collection
  *
  * Use getNumbersOfStickersInCollection to make use of internal caching
  *
  * @access private
  *
  * @param integer $collectionId Id of collection
  * @return integer $result number of stickers in collection
  */
  function getStickersCount($collectionId) {
    $sql = "SELECT COUNT(*) AS count
              FROM %s
             WHERE collection_id = %d
           ";
    $params = array($this->tableSticker, $collectionId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField();
    }
    return FALSE;
  }


  /**
  * This method retrieves the number of stickers of a collection
  *
  * @param integer $collectionId Id of collection
  * @return integer $result number of stickers in collection
  */
  function getNumberOfStickersInCollection($collectionId) {
    if (!isset($this->_stickersCount[$collectionId])) {
      $this->_stickersCount[$collectionId] = $this->getStickersCount($collectionId);
    }
    if (isset($this->_stickersCount[$collectionId])) {
      return $this->_stickersCount[$collectionId];
    }
    return FALSE;
  }

  /**
  * Retrieves a number of stickers from a collection.
  *
  * @param integer $collectionId ID of desired collection
  * @param integer $limit how many stickers to fetch at max
  * @param integer $offset start fetching from a given position (e.g. for paging)
  * @return array $result list of stickers
  */
  function getStickersByCollection($collectionId, $limit, $offset) {
    $result = FALSE;
    if (isset($collectionId) && $collectionId > 0) {
      $sql = "SELECT sticker_id, collection_id, sticker_text, sticker_image,
                     sticker_author
                FROM %s
               WHERE collection_id = %d
               ORDER BY sticker_id ASC
            ";
      $params = array($this->tableSticker, $collectionId);
      if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
        $result = array();
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result[$row['sticker_id']] = $row;
        }
        $this->absCount = $res->absCount();
      }
    }
    return $result;
  }

  /**
  *  Retrieve complete data for a single sticker.
  *
  * @param integer $stickerId the sticker ID
  */
  function getSticker($stickerId) {
    $result = FALSE;
    if ($stickerId > 0) {
      $sql = "SELECT sticker_id, collection_id, sticker_text, sticker_image,
                     sticker_author
                FROM %s
              WHERE sticker_id = %d
              ORDER BY sticker_id ASC
            ";
      $params = array($this->tableSticker, $stickerId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        return $res->fetchRow(DB_FETCHMODE_ASSOC);
      }
    }
    return $result;
  }

  /**
  * Retrieves a single random sticker.
  *
  * @param integer $collectionId ID of collection
  * @return array $result random sticker record
  */
  function getRandomSticker($collectionId, $limit = 1) {
    $result = FALSE;

    $randomOrder = $this->databaseGetSQLSource('RANDOM');

    $sql = "SELECT s.sticker_id, s.collection_id, s.sticker_text,
                   s.sticker_image, s.sticker_author
              FROM %s AS s
             WHERE s.collection_id = %d
             ORDER BY $randomOrder
           ";

    $params = array($this->tableSticker, $collectionId);
    if ($res = $this->databaseQueryFmt($sql, $params, (int)$limit)) {
      return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
    return $result;
  }

}
?>
