<?php
/**
* Provide data encapsulation for the  surfer groups records.
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
* @subpackage Content
* @version $Id: Groups.php 36690 2012-01-31 16:37:06Z weinert $
*/

/**
* Provide data encapsulation for the  surfer groups records.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentCommunityGroups extends PapayaDatabaseRecords {

  protected $_fields = array(
    'id' => 'surfergroup_id',
    'title' => 'surfergroup_title'
  );

  protected $_orderByFields = array(
    'surfergroup_title' => PapayaDatabaseInterfaceOrder::ASCENDING,
    'surfergroup_id' => PapayaDatabaseInterfaceOrder::ASCENDING
  );

  protected $_identifierProperties = 'id';

  protected $_tableName = PapayaContentTables::COMMUNITY_GROUPS;

  /**
  * This method can be used to load the group records by a given permission id
  * it will load all groups that have the given permission assigned.
  *
  * @param integer $permission
  * @param NULL|integer $limit
  * @param NULL|integer $offset
  * @return boolean;
  */
  public function loadByPermission($permission, $limit = NULL, $offset = NULL) {
    $databaseAccess = $this->getDatabaseAccess();
    $fields = implode(', ', $this->mapping()->getFields());
    $sql = "SELECT $fields
              FROM %s
             WHERE surfergroup_id IN (
               SELECT surfergroup_id FROM %s WHERE surfer_permid = '%d'
             ) ".$this->_compileOrderBy();
    $parameters = array(
      $databaseAccess->getTableName($this->_tableName),
      $databaseAccess->getTableName(PapayaContentTables::COMMUNITY_GROUP_PERMISSIONS),
      (int)$permission
    );
    return $this->_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
  }

}