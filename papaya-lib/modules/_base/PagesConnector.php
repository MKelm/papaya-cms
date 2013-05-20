<?php
/**
* A connector with helper methods to handle data by pages.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage _Base
* @version $Id: PagesConnector.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Basic class connector
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_connector.php');

/**
* A connector with helper methods to handle data by pages.
*
* Usage:
* include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
* $pagesConnector = base_pluginloader::getPluginInstance('69db080d0bb7ce20b52b04e7192a60bf', $this);
*
* $array = $pagesConnector->getTitles($pageId(s), $lngId);
* $array = $pagesConnector->getContents($pageId(s), $lngId);
*/
class PapayaBasePagesConnector extends base_connector {

  /**
  * Database access object.
  * @var object
  */
  private $_databaseAccess = NULL;

  /**
  * Memory cache for page titles.
  * array(
  *   (lngId) => array(
  *     (pageId) => 'title'[,...]
  *   )[,...]
  * )
  * @var array
  */
  private $_pageTitles = array();

  /**
  * Memory cache for page contents.
  * array(
  *   (lngId) => array(
  *     (pageId) => array(
  *       'topic_id' => (int),
  *       'topic_title' => 'title',
  *       'topic_content' => 'content'
  *     )[,...]
  *   )[,...]
  * )
  */
  private $_pageContents = array();

  /**
  * Set database access object to load pages data.
  *
  * @param object $databaseAccess
  * @return boolean
  */
  public function setDatabaseAccessObject($databaseAccess) {
    if (!empty($databaseAccess) && is_object($databaseAccess)) {
      $this->_databaseAccess = $databaseAccess;
    }
  }

  /**
  * Get database access object to load pages data.
  *
  * @return object
  */
  protected function getDatabaseAccessObject() {
    if (!isset($this->_databaseAccess)) {
      $this->_databaseAccess = new PapayaDatabaseAccess($this);
      $this->_databaseAccess->papaya($this->papaya());
    }
    return $this->_databaseAccess;
  }

  /**
  * Get page(s) titles by id(s) and language id.
  *
  * @param array|integer $pageIds
  * @param integer $lngId
  * @return array
  */
  public function getTitles($pageIds, $lngId) {
    $result = array();
    if (!empty($pageIds)) {
      if (!is_array($pageIds)) {
        $pageIds = array($pageIds);
      }
      if (!isset($this->_pageTitles[$lngId])) {
        $this->_pageTitles[$lngId] = array();
      }
      $pageIdsToLoad = array();
      foreach ($pageIds as $key => $pageId) {
        if ($pageId > 0) {
          if (isset($this->_pageTitles[$lngId][$pageId])) {
            $result[(int)$pageId] = $this->_pageTitles[$lngId][$pageId];
          } else {
            $pageIdsToLoad[] = $pageId;
          }
        }
      }
      if (!empty($pageIdsToLoad)) {
        $databaseAccess = $this->getDatabaseAccessObject();
        $filter = $databaseAccess->getSQLCondition('tt.topic_id', $pageIdsToLoad);
        $sql = "SELECT tt.topic_id, tt.topic_title
                  FROM %s tt
                 WHERE $filter
                   AND tt.lng_id = %d";
        $params = array(PAPAYA_DB_TBL_TOPICS_TRANS, $lngId);
        if ($res = $databaseAccess->queryFmt($sql, $params)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $result[(int)$row['topic_id']] = $row['topic_title'];
            $this->_pageTitles[$lngId][(int)$row['topic_id']] = $row['topic_title'];
          }
        }
      }
      ksort($result);
    }
    return $result;
  }

  /**
  * Get page(s) titles and contents by id(s) and language id.
  *
  * @param array|integer $pageIds
  * @param integer $lngId
  * @return array
  */
  public function getContents($pageIds, $lngId) {
    $result = array();
    if (!empty($pageIds)) {
      if (!is_array($pageIds)) {
        $pageIds = array($pageIds);
      }
      if (!isset($this->_pageContents[$lngId])) {
        $this->_pageContents[$lngId] = array();
      }
      $pageIdsToLoad = array();
      foreach ($pageIds as $key => $pageId) {
        if ($pageId > 0) {
          if (isset($this->_pageContents[$lngId][$pageId])) {
            $result[(int)$pageId] = $this->_pageContents[$lngId][$pageId];
          } else {
            $pageIdsToLoad[] = $pageId;
          }
        }
      }
      if (!empty($pageIdsToLoad)) {
        $databaseAccess = $this->getDatabaseAccessObject();
        $filter = $databaseAccess->getSQLCondition('tt.topic_id', $pageIdsToLoad);
        $sql = "SELECT tt.topic_id, tt.topic_title, tt.topic_content
                  FROM %s tt
                 WHERE $filter
                   AND tt.lng_id = %d";
        $params = array(PAPAYA_DB_TBL_TOPICS_TRANS, $lngId);
        if ($res = $databaseAccess->queryFmt($sql, $params)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $result[(int)$row['topic_id']] = $row;
            $this->_pageContents[$lngId][(int)$row['topic_id']] = $row;
            $this->_pageTitles[$lngId][(int)$row['topic_id']] = $row['topic_title'];
          }
        }
      }
    }
    return $result;
  }
}
?>