<?php
/**
* Provide a superclass data encapsulation for the content box itself. HEre a two children
* of this class {@see PapayaContent'BoxWork} for the working copy and
* {@see PapayaContentBoxPublication} for the published version.
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
* @version $Id: Box.php 36028 2011-08-04 10:10:14Z weinert $
*/

/**
* Provide a superclass data encapsulation for the content box itself. HEre a two children
* of this class {@see PapayaContent'BoxWork} for the working copy and
* {@see PapayaContentBoxPublication} for the published version.
*
* @package Papaya-Library
* @subpackage Content
*/
abstract class PapayaContentBox extends PapayaDatabaseObjectRecord {

  /**
  * Map properties to database fields
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    // box id
    'id' => 'box_id',
    // box group id
    'group_id' => 'boxgroup_id',
    // name for administration interface
    'name' => 'box_name',
    // creation / modification timestamps
    'created' => 'box_created',
    'modified' => 'box_modified',
    // server side content caching
    'cache_mode' => 'box_cachemode',
    'cache_time' => 'box_cachetime',
    // unpublished translations counter
    'unpublished_translations' => 'box_unpublished_languages'
  );

  protected $_tableName = PapayaContentTables::BOXES;

  /**
  * Box translations list object
  * @var PapayaContentBoxTranslations
  */
  protected $_translations = NULL;

  public function load($id) {
    if (parent::load($id)) {
      $this->translations()->load($id);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Access to the translation list informations
  *
  * Allows to get/set the list object. Can create a list object if needed.
  *
  * @param PapayaContentBoxTranslations $translations
  * @return PapayaContentBoxTranslations
  */
  public function translations(PapayaContentBoxTranslations $translations = NULL) {
    if (isset($translations)) {
      $this->_translations = $translations;
    }
    if (is_null($this->_translations)) {
      $this->_translations = new PapayaContentBoxTranslations();
      $this->_translations->setDatabaseAccess($this->getDatabaseAccess());
    }
    return $this->_translations;
  }
}