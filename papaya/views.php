<?php
/**
* Manage views (content module + xsl files)
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
* @version $Id: views.php 37752 2012-11-30 11:33:05Z weinert $
*/

/**
* Check user and init admin page
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::VIEW_MANAGE)) {
  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Administration').' - '._gt('Views'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['items-view']);

  include_once(PAPAYA_INCLUDE_PATH.'system/base_viewlist.php');

  $views = new base_viewlist;
  $views->msgs = &$PAPAYA_MSG;
  $views->images = &$PAPAYA_IMAGES;
  $views->layout = &$PAPAYA_LAYOUT;
  $views->authUser = &$PAPAYA_USER;

  $views->initialize();
  $views->execute();
  $views->getXML();

}
require('inc.footer.php');
?>