<?php
/**
* Messaging system for editors
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @version $Id: msgbox.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE) {
  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('General').' - '._gt('Messages'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['status-mail-open']);

  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_messages.php');
  $messageBox = new papaya_messages();
  $messageBox->layout = &$PAPAYA_LAYOUT;
  $messageBox->images = &$PAPAYA_IMAGES;
  $messageBox->msgs = &$PAPAYA_MSG;
  $messageBox->authUser = &$PAPAYA_USER;

  $messageBox->initialize();

  $messageBox->execute();
  $messageBox->getXML();
}
require('inc.footer.php');
?>