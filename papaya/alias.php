<?php
/**
* Manage path aliases
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
* @version $Id: alias.php 37745 2012-11-29 15:45:30Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::ALIAS_MANAGE)) {

  /**
  * Edit pages
  */
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_topic.php');
  /**
  * Edit aliases
  */
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_alias_tree.php');

  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Content').' - '._gt('Aliases'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['items-alias']);

  $aliasTree = new papaya_alias_tree;
  $aliasTree->images = &$PAPAYA_IMAGES;
  $aliasTree->layout = &$PAPAYA_LAYOUT;
  $aliasTree->authUser = &$PAPAYA_USER;
  $aliasTree->msgs = &$PAPAYA_MSG;
  $aliasTree->initialize();
  $aliasTree->execute();
  $aliasTree->getXML();
}
require('inc.footer.php');
?>