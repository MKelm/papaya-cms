<?php
/**
* The PluginLoaderList allows to to load module/plugin data using a list of guids.
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
* @package Papaya-Library
* @subpackage Plugins
* @version $Id: List.php 35415 2011-02-10 15:13:53Z weinert $
*/

/**
* The PluginLoaderList allows to to load module/plugin data using a list of guids.
*
* It stores the loaded plugin data in an internal variable and loads additional data for missing
* guids only. It does not reset the list with each load() call, but appends the new data.
*
* @package Papaya-Library
* @subpackage Plugins
*/
class PapayaPluginList extends PapayaDatabaseObjectList {

  /**
  * Map fields to application names
  *
  * @var array($fieldName => $name)
  */
  protected $_fieldMapping = array(
    'module_guid' => 'guid',
    'module_class' => 'class',
    'module_path' => 'path',
    'module_file' => 'file',
    'modulegroup_prefix' => 'prefix'
  );

  /**
  * Database table name containing plugins/modules
  *
  * @var string
  */
  protected $_tablePlugins = 'modules';
  /**
  * Database table name containing plugin/module groups
  *
  * @var string
  */
  protected $_tablePluginGroups = 'modulegroups';

  /**
  * Load plugin data for the provided
  *
  *
  * @param array $pluginGuids
  */
  public function load($pluginGuids) {
    if (!is_array($pluginGuids)) {
      $pluginGuids = array($pluginGuids);
    }
    $missingGuids = array_diff($pluginGuids, array_keys($this->_records));
    if (!empty($missingGuids)) {
      $filter = $this->databaseGetSQLCondition('m.module_guid', $missingGuids);
      $sql = "SELECT m.module_class, m.module_path,
                     m.module_file, m.module_guid,
                     mg.modulegroup_prefix
                FROM %s AS m, %s AS mg
               WHERE mg.modulegroup_id = m.modulegroup_id
                 AND $filter";
      $parameters = array(
        $this->databaseGetTableName($this->_tablePlugins),
        $this->databaseGetTableName($this->_tablePluginGroups)
      );
      if ($databaseResult = $this->databaseQueryFmt($sql, $parameters)) {
        $this->_fetchRecords($databaseResult, 'module_guid');
        $this->_recordCount = count($this->_records);
        return TRUE;
      } else {
        return FALSE;
      }
    } else {
      return TRUE;
    }
  }
}