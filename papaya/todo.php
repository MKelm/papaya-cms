<?php
/**
* Manage User ToDo List
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
* @version $Id: todo.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE) {
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('General').' - '._gt('Messages').' - '._gt('Tasks'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['items-task']);

  initNavigation('msgbox.php');

  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_todo.php');
  $todoList = new papaya_todo;
  $todoList->images = &$PAPAYA_IMAGES;
  $todoList->layout = &$PAPAYA_LAYOUT;
  $todoList->authUser = &$PAPAYA_USER;
  $todoList->msgs = &$PAPAYA_MSG;
  $todoList->initialize();
  $todoList->execute();
  $todoList->getXML();

  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_messages.php');
  $messageBox = new papaya_messages();
  $messageBox->layout = &$PAPAYA_LAYOUT;
  $messageBox->images = &$PAPAYA_IMAGES;
  $messageBox->msgs = &$PAPAYA_MSG;
  $messageBox->authUser = &$PAPAYA_USER;
  $messageBox->getFolderPanel();
}
require('inc.footer.php');
?>