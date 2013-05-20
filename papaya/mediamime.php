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
* @version $Id: mediamime.php 37754 2012-11-30 12:01:23Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");
if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::SYSTEM_MIMETYPES_MANAGE)) {
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_mediadb_mime.php');
  initNavigation('options.php');
  $PAPAYA_LAYOUT->setParam(
    'PAGE_TITLE', _gt('Administration').' - '._gt('Settings').' - '._gt('Mime Types')
  );
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['items-option']);

  $mediadb = new papaya_mediadb_mime;
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