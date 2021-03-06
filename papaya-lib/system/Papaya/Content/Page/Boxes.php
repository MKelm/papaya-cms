<?php
/**
* Provide data encapsulation for the content page translations list.
*
* The list does not contain all detail data, it is for list outputs etc. To get the full data
* use {@see PapayaContentPageTranslation}.
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
* @version $Id: Boxes.php 36028 2011-08-04 10:10:14Z weinert $
*/

/**
* Provide data encapsulation for the content page translations list.
*
* The list does not contain all detail data, it is for list outputs etc. To get the full data
* use {@see PapayaContentPageTranslation}.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentPageBoxes extends PapayaDatabaseObjectList {

  /**
  * Map field names to value identfiers
  *
  * @var array
  */
  protected $_fieldMapping = array(
    'topic_id' => 'page_id',
    'box_id' => 'box_id',
    'box_sort' => 'position'
  );

  /**
  * Load boxes links into records list
  *
  * @param integer $pageId
  */
  public function load($pageId) {
    $sql = "SELECT box_id, topic_id, box_sort
              FROM %s
             WHERE topic_id = '%d'
             ORDER BY box_sort, box_id";
    $parameters = array(
      $this->databaseGetTableName(PapayaContentTables::PAGE_BOXES),
      $pageId
    );
    return $this->_loadRecords($sql, $parameters);
  }

  /**
  * Delete box links on the given page ids
  *
  * @param array|integer $pageIds
  * @return boolean
  */
  public function delete($pageIds) {
    return FALSE !== $this->databaseDeleteRecord(
      $this->databaseGetTableName(PapayaContentTables::PAGE_BOXES),
      'topic_id',
      PapayaUtilArray::ensure($pageIds)
    );
  }

  /**
  * Copy currently loaded box links to the given page ids
  *
  * @param array|integer $pageIds
  * @return boolean
  */
  public function copyTo($pageIds) {
    $pageIds = PapayaUtilArray::ensure($pageIds);
    if (empty($this->_records) || empty($pageIds)) {
      return TRUE;
    }
    if ($this->delete($pageIds)) {
      $records = array();
      foreach ($pageIds as $pageId) {
        foreach ($this->_records as $record) {
          $records[] = array(
            'box_id' => $record['box_id'],
            'topic_id' => $pageId,
            'box_sort' => $record['position']
          );
        }
      }
      return FALSE !== $this->databaseInsertRecords(
        $this->databaseGetTableName(PapayaContentTables::PAGE_BOXES),
        $records
      );
    }
    return FALSE;
  }
}
