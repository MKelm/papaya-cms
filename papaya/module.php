<?php
/**
* Wrapper - embeds edit modules in admin interface
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
* @package Papaya
* @subpackage Administration
* @version $Id: module.php 38330 2013-03-26 20:38:52Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");


if ($PAPAYA_SHOW_ADMIN_PAGE) {

  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_editmodules.php');
  
  $moduleName = '';
  $pattern = '(/module_(?P<module>[^/.?#]+)\.php(?:[?#]|$))';
  if (isset($_SERVER['REQUEST_URI']) &&
  		preg_match($pattern, $_SERVER['REQUEST_URI'], $matches)) {
    $moduleName = PapayaUtilArray::get($matches, 'module');
  }

  $module = new papaya_editmodules($moduleName);
  $module->images = &$PAPAYA_IMAGES;
  $module->layout = &$PAPAYA_LAYOUT;
  $module->msgs = &$PAPAYA_MSG;
  $module->authUser = &$PAPAYA_USER;

  $module->initialize();
  $module->execute();
  initNavigation();
}
require('inc.footer.php');
?>