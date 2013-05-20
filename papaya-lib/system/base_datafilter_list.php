<?php
/**
* Data filter base class
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
* @package Papaya
* @subpackage Core
* @version $Id: base_datafilter_list.php 35739 2011-05-10 13:48:39Z zerebecki $
*/

/**
* Basic class plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');
/**
* Data filter basse class
*
* @package Papaya
* @subpackage Core
*/
class base_datafilter_list extends base_db {
  /**
  * Papaya database table data filter
  * @var string $tableDataFilter
  */
  var $tableDataFilter = PAPAYA_DB_TBL_DATAFILTER;
  /**
  * Papaya database table data filter links
  * @var string $tableDataFilterLinks
  */
  var $tableDataFilterLinks = PAPAYA_DB_TBL_DATAFILTER_LINKS;
  /**
  * Papaya database data modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;

  /**
  * Filter configurations
  * @var array $filterGuids
  */
  var $filterConfs = array();

  /**
  * Filter objects
  * @var array $filterLinks
  */
  var $filterObjects = array();

  /**
  * Object of content
  * @var object $contentObj
  */
  var $contentObj = NULL;

  /**
  * Initialize contentObj, load filter guids and objects
  *
  * @access public
  */
  function initialize(&$contentObj) {
    $this->contentObj = $contentObj;
    if (isset($contentObj) && is_object($contentObj) &&
        $this->loadFilterConfigurations($contentObj->parentObj->getViewId())) {
      $this->loadFilterObjects();
    }
  }

  /**
  * Prepares content data in getData()
  *
  * @param array $keys keys of content data array to prepare
  * @access public
  */
  function prepareFilterData($keys) {
    foreach ($this->filterConfs as $filterId => $conf) {
      if (isset($this->filterObjects[$filterId]) &&
           is_object($this->filterObjects[$filterId])) {
        $this->filterObjects[$filterId]->prepareFilterData(
          $this->contentObj->data, $keys);
      }
    }
  }

  /**
  * Load filtered data in parseData
  *
  * @param array $keys keys of content data array to prepare
  * @access public
  */
  function loadFilterData() {
    foreach ($this->filterConfs as $filterId => $conf) {
      if (isset($this->filterObjects[$filterId]) &&
           is_object($this->filterObjects[$filterId])) {
        $this->filterObjects[$filterId]->loadFilterData(
          $this->contentObj->data);
      }
    }
  }

  /**
  * Applies filter to given string and return result
  *
  * @param string $string input string to filter
  * @access public
  * @return string $string filtered string
  */
  function applyFilterData($string) {
    foreach ($this->filterConfs as $filterId => $conf) {
      if (isset($this->filterObjects[$filterId]) &&
           is_object($this->filterObjects[$filterId])) {
        $string = $this->filterObjects[$filterId]->applyFilterData($string);
      }
    }
    return $string;
  }

  /**
  * Get xml data from filter(s)
  *
  * @access public
  * @param array $parseParams parsing params
  * @return string as xml
  */
  function getFilterData($parseParams = NULL) {
    $result = '';
    if (isset($this->filterConfs) && is_array($this->filterConfs) &&
        count($this->filterConfs) > 0) {
      foreach ($this->filterConfs as $filterId => $conf) {
        if (isset($this->filterObjects[$filterId]) &&
            is_object($this->filterObjects[$filterId])) {
          $result .= $this->filterObjects[$filterId]->getFilterData($parseParams);
        }
      }
    }
    return $result;
  }

  /**
  * Loads all filter objects by guid in $this->filterGuids
  *
  * @access public
  * @return boolean status response
  */
  function loadFilterObjects() {
    foreach ($this->filterConfs as $filterId => $conf) {
      $this->filterObjects[$filterId] =
        &$this->createFilterObject($conf['guid'], $contentObj);
      if (isset($this->filterObjects[$filterId]) &&
          is_object($this->filterObjects[$filterId])) {
        $this->filterObjects[$filterId]->initialize($this->contentObj);
        $this->filterObjects[$filterId]->setData($conf['data']);
      }
    }
    if (count($this->filterObjects) > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Loads configurations for filters (includes guid and filter specific data)
  *
  * @param integer $viewId
  * @access public
  * @return boolean loaded or not
  */
  function loadFilterConfigurations($viewId) {
    $this->filterConfs = array();
    $sql = "SELECT fl.datafilter_id, fl.datafilter_data, df.module_guid
              FROM %s fl
              LEFT OUTER JOIN %s df ON (df.datafilter_id = fl.datafilter_id)
             WHERE fl.view_id = %d
             ORDER BY df.datafilter_id ASC";
    $params = array($this->tableDataFilterLinks, $this->tableDataFilter,
      $viewId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->filterConfs[$row['datafilter_id']]['guid'] = $row['module_guid'];
        $this->filterConfs[$row['datafilter_id']]['data'] = $row['datafilter_data'];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Creates filter object with base_pluginloader
  *
  * @param string $guid unique identifier of the object class
  * @param object &$parent parent object
  * @param mixed $data optional, default value NULL
  * @access public
  * @return object filter
  */
  function createFilterObject($guid, &$parent, $data = NULL) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $obj = &base_pluginloader::getInstance();
    return $obj->createObject($guid, $parent, $data);
  }

}
?>
