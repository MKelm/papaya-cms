<?php
/**
* Provide data encapsulation for the content box publication.
*
* Allows to load/save the boxes.
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
* @version $Id: Publication.php 36471 2011-11-30 11:16:08Z weinert $
*/

/**
* Provide data encapsulation for the content box publication.
*
* @package Papaya-Library
* @subpackage Content
*
* @property integer $id box id
* @property integer $groupId box group id
* @property string $name administration interface box name
* @property integer $created box creation timestamp
* @property integer $modified last modification timestamp
* @property integer $cacheMode box content cache mode (system, none, own)
* @property integer $cacheTime box content cache time, if mode == own
*/
class PapayaContentBoxPublication extends PapayaContentBox {

  /**
  * Map properties to database fields
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    // page id
    'id' => 'box_id',
    // parent id
    'group_id' => 'boxgroup_id',
    // name for administration interface
    'name' => 'box_name',
    // creation / modification timestamps
    'created' => 'box_created',
    'modified' => 'box_modified',
    // server side content caching
    'cache_mode' => 'box_cachemode',
    'cache_time' => 'box_cachetime',
    //publication period
    'published_from' => 'box_public_from',
    'published_to' => 'box_public_to'
  );

  protected $_tableName = PapayaContentTables::BOX_PUBLICATIONS;

  public function save() {
    if ($this->id > 0) {
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE box_id = %d";
      $parameters = array(
        $this->databaseGetTableName($this->_tableName),
        $this->id
      );
      if ($res = $this->databaseQueryFmt($sql, $parameters)) {
        $this->modified = time();
        if ($res->fetchField() > 0) {
          return $this->_updateRecord(
            $this->databaseGetTableName($this->_tableName),
            array('box_id' => $this->id)
          );
        } else {
          $this->created = $this->modified;
          return $this->_insertRecord(
            $this->databaseGetTableName($this->_tableName)
          );
        }
      }
    }
    return FALSE;
  }
}