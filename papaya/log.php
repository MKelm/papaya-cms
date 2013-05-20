<?php
/**
* Log message viewer
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
* @version $Id: log.php 37754 2012-11-30 12:01:23Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::SYSTEM_PROTOCOL)) {
  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Administration').' - '._gt('Protocol'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['categories-protocol']);

  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_log.php');

  $log = new papaya_log();
  $log->initialize();
  $log->authUser = &$PAPAYA_USER;
  $log->images = &$PAPAYA_IMAGES;
  $log->msgs = &$PAPAYA_MSG;
  $log->layout = &$PAPAYA_LAYOUT;
  $log->loadList();

  $log->getXML();
}
require('inc.footer.php');
?>