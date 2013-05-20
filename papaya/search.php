<?php
/**
* Administration interface pages search
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
* @version $Id: search.php 38360 2013-04-04 10:47:15Z weinert $
*/

/**
* include overview "search" page
*/

require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE) {
  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Content').' - '._gt('Page Search'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['actions-search']);

  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_overview.php');
  $overview = new papaya_overview();
  $overview->layout = &$PAPAYA_LAYOUT;
  $overview->images = &$PAPAYA_IMAGES;
  $overview->msgs = &$PAPAYA_MSG;
  $overview->authUser = &$PAPAYA_USER;

  $overview->initialize('search');
  $overview->execute();
  $overview->getXML();

}
require('inc.footer.php');