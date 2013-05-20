<?php
/**
* Edit tags
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
* @version $Id: tags.php 37744 2012-11-29 15:39:51Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::TAG_MANAGE)) {
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_tags.php');
  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Content').' - '._gt('Tags'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['items-tag']);

  $tags = new papaya_tags;
  $tags->authUser = &$PAPAYA_USER;
  $tags->msgs = &$PAPAYA_MSG;
  $tags->layout = &$PAPAYA_LAYOUT;
  $tags->images = &$PAPAYA_IMAGES;

  $tags->initialize();
  $tags->execute();
  $tags->getXML();
}
require('inc.footer.php');
?>