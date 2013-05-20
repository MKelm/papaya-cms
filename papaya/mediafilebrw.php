<?php
/**
* Mediafile browser
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
* @version $Id: mediafilebrw.php 37742 2012-11-29 11:44:56Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");


if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::FILE_BROWSE)) {
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_mediadb_browser.php');

  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Mediafile browser'));

  $mediaDB = new papaya_mediadb_browser;
  $mediaDB->dataDirectory = PAPAYA_PATH_MEDIAFILES;
  $mediaDB->thumbnailDirectory = PAPAYA_PATH_THUMBFILES;

  $mediaDB->msgs = &$PAPAYA_MSG;
  $mediaDB->authUser = &$PAPAYA_USER;
  $mediaDB->images = &$PAPAYA_IMAGES;
  $mediaDB->layout = &$PAPAYA_LAYOUT;

  $mediaDB->initialize();
  $mediaDB->getXML();

} else {
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', 'Mediafile browser');
}
require('inc.footer.php');
?>