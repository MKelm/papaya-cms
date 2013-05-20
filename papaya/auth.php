<?php
/**
* Userverwaltung
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
* @version $Id: auth.php 37753 2012-11-30 11:37:43Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::USER_MANAGE)) {
  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Administration').' - '._gt('Users'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['items-user-group']);

  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_user.php');
  $auth = new papaya_user();
  $auth->layout = &$PAPAYA_LAYOUT;
  $auth->images = &$PAPAYA_IMAGES;
  $auth->msgs = &$PAPAYA_MSG;
  $auth->authUser = &$PAPAYA_USER;

  $auth->initialize('usredit');

  $auth->execute();
  $auth->getXML();
}
require('inc.footer.php');
?>