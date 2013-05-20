<?php
/**
* This object loads the defined output modes for a papaya installation.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Mode.php 37790 2012-12-07 16:02:39Z weinert $
*/

/**
* This object loads the defined output modes for a papaya installation.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentViewMode extends PapayaDatabaseRecord {

  /**
  * Map field names to more convinient property names
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    'extension' => 'viewmode_ext',
    'type' => 'viewmode_type',
    'charset' => 'viewmode_charset',
    'content_type' => 'viewmode_contenttype',
    'path' => 'viewmode_path',
    'module_guid' => 'module_guid',
    'session_mode' => 'viewmode_sessionmode',
    'session_redirect' => 'viewmode_sessionredirect',
    'session_cache' => 'viewmode_sessioncache'
  );

  /**
  * Table containing domain informations
  *
  * @var string
  */
  protected $_tableName = PapayaContentTables::VIEW_MODES;

  /**
   * @see PapayaDatabaseRecord::_createKey()
   */
  protected function _createKey() {
    return new PapayaDatabaseRecordKeyFields(
      $this,
      $this->_tableName,
      array('extension')
    );
  }
}