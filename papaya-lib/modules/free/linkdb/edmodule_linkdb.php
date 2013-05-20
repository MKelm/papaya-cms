<?php
/**
* Module link db
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
* @version $Id: edmodule_linkdb.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Base class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Module link db
*
* @package Papaya-Modules
* @subpackage Free-LinkDatabase
*/
class edmodule_linkdb extends base_module {

  /**
  * permissions
  * @var array $permissions
  */
  var $permissions = array(
    1 => 'Manage',
    2 => 'View statistic',
    3 => 'Reset counter'
  );


  /**
  * Function for execute module
  *
  * @access public
  */
  function execModule() {
    if ($this->hasPerm(1, TRUE)) {
      $path = dirname(__FILE__);
      include_once($path.'/admin_linkdb.php');
      $linkDatabase = new admin_linkdb;
      $linkDatabase->module = &$this;
      $linkDatabase->images = &$this->images;
      $linkDatabase->msgs = &$this->msgs;
      $linkDatabase->layout = &$this->layout;
      $linkDatabase->authUser = &$this->authUser;

      $linkDatabase->initialize();
      $linkDatabase->execute();
      $linkDatabase->getXML();
    }
  }
}
?>