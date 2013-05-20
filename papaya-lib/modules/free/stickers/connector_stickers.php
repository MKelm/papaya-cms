<?php
/**
* Stickers connector API
*
* @package Papaya-Modules
* @subpackage Free-Stickers
* @version $Id: connector_stickers.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* basic plugin class
*/
require_once(PAPAYA_INCLUDE_PATH.'base_plugin.php');

/**
* Stickers connector API
*
* @package Papaya-Modules
* @subpackage Free-Stickers
*/
class connector_stickers extends base_plugin {

  var $stickersObj = NULL;
  var $_stickersCount = array();

  /**
  * constructor, initializes stickersObj
  */
  function __construct() {
    parent::__construct();
    include_once(dirname(__FILE__).'/base_stickers.php');
    $this->stickersObj = new base_stickers;
  }

  /**
  * PHP4 legacy constructor
  */
  function connector_stickers() {
    connector_stickers::__construct();
  }

  /**
  * This method retrieves a list of available collections.
  */
  function getCollections() {
    return $this->stickersObj->getCollections();
  }

  /**
  * This method retrieves stickers from a given collection
  *
  * @param integer $collectionId ID of collection
  * @param integer $limit number of stickers to fetch
  * @param integer $offset offset for fetching slices of stickers
  * @return array $result list of stickers
  */
  function getStickersByCollection($collectionId, $limit, $offset) {
    if ($stickers = $this->stickersObj->getStickersByCollection($collectionId, $limit, $offset)) {
      $this->_stickersCount[$collectionId] = $this->stickersObj->absCount;
      return $stickers;
    } else {
      $this->_stickersCount[$collectionId] = 0;
    }
  }

  /**
  * This method identifies the number of stickers in a collection
  *
  * @param integer $collectionId ID of collection
  * @return integer $result number of collections
  */
  function getNumberOfStickersInCollection($collectionId) {
    if (!isset($this->_stickersCount[$collectionId])) {
      $this->_stickersCount[$collectionId] = $this->getStickersCount($colletionId);
    }
    if (isset($this->_stickersCount[$collectionId])) {
      return $this->_stickersCount[$collectionId];
    }
    return FALSE;
  }

  /**
  * This method retrieves a number of random stickers from a collection.
  *
  * @param integer $collectionId ID of collection
  * @param integer $limit number of stickers to fetch
  */
  function getRandomStickers($collectionId, $limit) {
    return $this->stickersObj->getRandomSticker($collectionId, $limit);
  }
}
?>