<?php
/**
* Papaya Media Database Item Record
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Media-Database
* @version $Id: Record.php 36028 2011-08-04 10:10:14Z weinert $
*/

/**
* Papaya Media Database Item Record
* @package Papaya-Library
* @subpackage Media-Database
*/
class PapayaMediaDatabaseItemRecord extends PapayaDatabaseObjectRecord {

  /**
  * Fields (accessible using dynamic properties)
  * @var array
  */
  protected $_fields = array(
    'media_id', 'folder_id', 'surfer_id',
    'file_name', 'file_date', 'file_size',
    'media_width', 'media_height'
  );

  /**
  * Load item from database
  * @param string $mediaId
  * @return boolean
  */
  public function load($mediaId) {
    $sql = "SELECT f.file_id, f.folder_id, f.surfer_id, f.file_name, f.file_date,
                   f.file_size, f.width, f.height, f.metadata, f.file_sort,
                   f.current_version_id,
                   m.mimetype_id, m.mimetype, m.mimetype_icon, m.mimetype_ext, m.mimegroup_id,
                   m.range_support, m.shaping, m.shaping_limit, m.shaping_offset
              FROM %s f
              LEFT OUTER JOIN %s m ON (f.mimetype_id = m.mimetype_id)
             WHERE f.file_id = '%s'";
    $params = array(
      $this->databaseGetTableName(PapayaContentTables::MEDIA_FILES),
      $this->databaseGetTableName(PapayaContentTables::MEDIA_MIMETYPES),
      $mediaId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->_values = $row;
        $this->_values['media_id'] = $row['file_id'];
        return TRUE;
      }
    }
    return FALSE;
  }

  public function save() {
    return FALSE;
  }
}

?>