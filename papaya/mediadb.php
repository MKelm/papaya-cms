<?php
/**
* Media database
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
* @version $Id: mediadb.php 37742 2012-11-29 11:44:56Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");
if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::FILE_MANAGE)) {
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_mediadb.php');
  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Content').' - '._gt('Files'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['items-folder']);

  $mediadb = new papaya_mediadb;
  $mediadb->authUser = &$PAPAYA_USER;
  $mediadb->msgs = &$PAPAYA_MSG;
  $mediadb->layout = &$PAPAYA_LAYOUT;
  $mediadb->images = &$PAPAYA_IMAGES;

  $mediadb->initialize();
  $mediadb->execute();
  $mediadb->getXML();
}
require('inc.footer.php');
?>