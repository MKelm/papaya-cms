<?php
/**
* Dynamic images managment
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
* @version $Id: imggen.php 37757 2012-11-30 12:11:14Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

/**
* moduels manager
*/
require_once(PAPAYA_INCLUDE_PATH.'system/papaya_imagegenerator.php');

if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::IMAGE_GENERATOR)) {
  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Administration').' - '._gt('Dynamic Images'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['items-graphic']);

  $imgGenerator = new papaya_imagegenerator();
  $imgGenerator->layout = &$PAPAYA_LAYOUT;
  $imgGenerator->images = &$PAPAYA_IMAGES;
  $imgGenerator->msgs = &$PAPAYA_MSG;
  $imgGenerator->authUser = &$PAPAYA_USER;

  $imgGenerator->initialize();
  $imgGenerator->execute();
  $imgGenerator->getXML();
}
require('inc.footer.php');
?>