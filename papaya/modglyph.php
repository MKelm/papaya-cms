<?php
/**
* Glyph for edit modules
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
* @version $Id: modglyph.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Not an admin page - just an image
*/
define('PAPAYA_ADMIN_PAGE_STATIC', TRUE);

/**
* Authentication
*/
require_once("./inc.auth.php");


/**
* modules manager
*/
require_once(PAPAYA_INCLUDE_PATH.'system/papaya_modulemanager.php');

if ($PAPAYA_SHOW_ADMIN_PAGE) {
  $modules = new papaya_modulemanager();
  $modules->msgs = &$PAPAYA_MSG;
  $modules->authUser = &$PAPAYA_USER;
  $modules->getGlyph();
}

?>