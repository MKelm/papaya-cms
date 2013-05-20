<?php
/**
* Help page
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
* @version $Id: help.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE) {
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_help.php');
  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Help'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['categories-help']);


  $help = new papaya_help;
  $help->authUser = &$PAPAYA_USER;
  $help->msgs = &$PAPAYA_MSG;
  $help->layout = &$PAPAYA_LAYOUT;
  $help->images = &$PAPAYA_IMAGES;

  $help->initialize();
  $help->execute();
  $help->getXML();
}
require('inc.footer.php');

?>