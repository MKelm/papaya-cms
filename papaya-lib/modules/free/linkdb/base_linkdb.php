<?php
/**
* LinkDb administration
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
* @subpackage Free-LinkDatabase
* @version $Id: base_linkdb.php 32741 2009-10-29 11:55:33Z kelm $
*/

/**
* Basicclass for database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* LinkDb administration
*
* @package Papaya-Modules
* @subpackage Free-LinkDatabase
*/
class base_linkdb extends base_db {

  /**
  * Links table
  * @var string $tableLinkdb
  */
  var $tableLinkdb = "";

  /**
  * Categories table
  * @var string $tableLinkdbCateg
  */
  var $tableLinkdbCateg = "";

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
  * Links
  * @var array $links
  */
  var $links = NULL;

  /**
  * link count
  * @var integer $linkCount
  */
  var $linkCount = 0;

  /**
  * Link
  * @var array $link
  */
  var $link = NULL;

  /**
  * contain ids from categories
  * @var array $categsById
  */
  var $categsById = NULL;

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
  * Links from Clipboard
  * @var array $cuttedLink
  */
  var $cuttedLink = NULL;

  /**
  * Categories from Clipboard
  * @var array $cuttedCategs
  */
  var $cuttedCategs = NULL;

  /**
  * Tree of categories
  * @var array $categTree
  */
  var $categTree = NULL;

  /**
  * number of links per page
  * @var integer $linksPerPage
  */
  var $linksPerPage = 20;

  /**
  * Full text search ?
  * @var array $fullTextSearch
  */
  var $fullTextSearch = PAPAYA_SEARCH_BOOLEAN;

  /**
  * Cache search results
  * @var boolean $cacheSearchResults
  */
  var $cacheSearchResults = FALSE;


  /**
  * Constructor initialisize class variables
  *
  * @param string $paramName optional, default value 'ldb'
  * @access public
  */
  function __construct($paramName = 'ldb') {
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_'.$paramName;
    $this->tableLinkdb = PAPAYA_DB_TABLEPREFIX.'_linkdb';
    $this->tableLinkdbCateg = PAPAYA_DB_TABLEPREFIX.'_linkdb_categ';
    $this->tableLinkdbClicks = PAPAYA_DB_TABLEPREFIX.'_linkdb_clicks';
  }

  /**
  * Initialize parameters
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('categ_id', array('link_id'));
    $this->initializeSessionParam('link_id', array('cmd'));
    $this->initializeSessionParam('stat_year', array('stat_month'));
    $this->initializeSessionParam('stat_month');
    $imagePath = 'module:'.$this->module->guid;
    $this->localImages = array(
      'counter-delete' => $imagePath."/counter-delete.png"
    );
  }

  /**
  * Calculate preview path
  *
  * @param integer $categId
  * @access public
  * @return string
  */
  function calcPrevPath($categId) {
    if ($categId > 0 && isset($this->categs[$categId])) {
      $categ = $this->categs[$categId];
      if (!isset($categ['newpath'])) {
        if ($categ['linkcateg_parent_id'] > 0) {
          if (isset($this->categs[$categ['linkcateg_parent_id']])) {
            $newPath =
              $this->calcPrevPath($categ['linkcateg_parent_id']).
                $categ['linkcateg_parent_id'].';';
          } else {
            $newPath = ";0;";
          }
        } else {
          $newPath = ";0;";
        }
        $this->categs[$categId]['newpath'] = $newPath;
        return $newPath;
      } else {
        return $categ['newpath'];
      }
    }
    return '';
  }


  /**
  * Load Categies per id or all categories
  *
  * @access public
  * @return boolean
  */
  function loadCategs($id = NULL, $repair = NULL) {
    unset($this->categs);
    unset($this->categTree);
    if (isset($id)) {
      $sql = "SELECT linkcateg_id,
                     linkcateg_parent_id,
                     linkcateg_title,
                     linkcateg_description
                FROM %s
               WHERE linkcateg_parent_id = %d
               ORDER BY linkcateg_title";

      $params = array($this->tableLinkdbCateg, $id);
    } elseif (isset($repair) && $repair == TRUE ) {
      $sql = "SELECT linkcateg_id,
                     linkcateg_parent_id,
                     linkcateg_title,
                     linkcateg_path
                FROM %s
               WHERE linkcateg_parent_id > -1
               ORDER BY linkcateg_title";
      $params = array($this->tableLinkdbCateg);
    } else {
      $sql = "SELECT linkcateg_id,
                     linkcateg_parent_id,
                     linkcateg_title,
                     linkcateg_path
                FROM %s
            ORDER BY linkcateg_title";
      $params = array($this->tableLinkdbCateg);
    }

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->categs[(int)$row['linkcateg_id']] = $row;
        $this->categTree[(int)$row['linkcateg_parent_id']][] = $row['linkcateg_id'];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load one Category per id
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadCateg($id) {
    unset($this->categ);
    $sql = "SELECT linkcateg_id,
                   linkcateg_title,
                   linkcateg_parent_id,
                   linkcateg_description,
                   linkcateg_path
              FROM %s
             WHERE linkcateg_id = %d
          ORDER BY linkcateg_title";
    $params = array($this->tableLinkdbCateg, $id);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->categ = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Check for existing category per id
  *
  * @param integer $id
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function categExists($id) {
    if ($id == 0) {
      return TRUE;
    }
    $sql = "SELECT count(*)
              FROM %s
             WHERE linkcateg_id = '%d'";
    $params = array($this->tableLinkdbCateg, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Is category empty ?
  *
  * @param integer $id category id
  * @access public
  * @return boolean
  */
  function categIsEmpty($id) {
    $sql = "SELECT count(*)
              FROM %s
             WHERE linkcateg_parent_id = '%d'";
    $params = array($this->tableLinkdbCateg, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        if ($row[0] == 0) {
          $sql = "SELECT count(*)
                    FROM %s
                   WHERE linkcateg_id = '%d'";
          $params = array($this->tableLinkdb, $id);
          if ($res = $this->databaseQueryFmt($sql, $params)) {
            if ($row = $res->fetchRow()) {
              return ((bool)$row[0] == 0);
            }
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Check for existing Link by id
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function linkExists($id) {
    $sql = "SELECT count(*)
              FROM %s
             WHERE link_id = '%d'";
    $params = array($this->tableLinkdb, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Load Links by Categid
  *
  * @param integer $id
  * @param mixed $full optional, default value NULL
  * @access public
  * @return boolean
  */
  function loadLinks($id, $full = FALSE) {
    unset($this->links);
    if ($full) {
      $sql = "SELECT link_id,
                     linkcateg_id,
                     link_title,
                     link_url,
                     link_description,
                     link_status
                FROM %s AS links
               WHERE linkcateg_id = %d
                 AND link_status = 1
               ORDER BY link_title";
      $params = array($this->tableLinkdb, (int)$id);
    } else {
      $sql = "SELECT link_id,
                     linkcateg_id,
                     link_title,
                     link_url,
                     link_description,
                     link_status
                FROM %s AS links
               WHERE linkcateg_id = %d
               ORDER BY link_title";
      $params = array($this->tableLinkdb, (int)$id);
    }
    $ids = array();
    if ($res = $this->databaseQueryFmt(
          $sql, $params, $this->linksPerPage, @(int)$this->params['offset'])
        ) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['counted'] = 0;
        $this->links[(int)$row['link_id']] = $row;
        $ids[] = (int)$row['link_id'];
      }
      $this->linkCount = $res->absCount();
      if (count($ids) > 0 && (!$full)) {
        if (count($ids) > 1) {
          $filter = ' IN ('.implode(',', $ids).')';
        } else {
          $filter = ' = '.(int)$ids[0];
        }
        $sql = "SELECT link_id, SUM(clicks_count) AS counted
                  FROM %s
                 WHERE link_id $filter
                 GROUP BY link_id";
        if ($res = $this->databaseQueryFmt($sql, $this->tableLinkdbClicks)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $this->links[(int)$row['link_id']]['counted'] = $row['counted'];
          }
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load one link per id
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadLink($id) {
    unset($this->link);
    $sql = "SELECT link_id,
                   linkcateg_id,
                   link_title,
                   link_description,
                   link_url,
                   link_created,
                   link_modified,
                   link_status
              FROM %s
             WHERE link_id = %d
             ORDER BY link_title";
    $params = array($this->tableLinkdb, (int)$id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $this->link = $res->fetchRow(DB_FETCHMODE_ASSOC);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Build link
  *
  * @param mixed $params optional, default value NULL
  * @param mixed $file optional, default value NULL
  * @param string $seperator optional, default value '&amp;'
  * @access public
  * @return string
  */
  function buildLink($params = NULL, $file = NULL, $seperator = '&amp;') {
    $link = (isset($file)) ? $file : $this->baseLink;
    $sql = '';
    if (isset($params) && is_array($params)) {
      foreach ($params as $key=>$val) {
        $sql .= $seperator.$this->paramName.'['.urlencode($key).']='.urlencode($val);
      }
    }
    if ($sql != '') {
      return $link.'?'.substr($sql, strlen($seperator));
    } else {
      return $link;
    }
  }
}
?>
