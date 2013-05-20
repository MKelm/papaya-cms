<?php
/**
* Sitemap
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
* @version $Id: tree.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE) {
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_topic_tree.php');
  include_once(PAPAYA_INCLUDE_PATH.'system/papaya_topic.php');

  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Content').' - '._gt('Sitemap'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['categories-sitemap']);

  $topicTree = new papaya_topic_tree;
  $topicTree->msgs = &$PAPAYA_MSG;
  $topicTree->images = &$PAPAYA_IMAGES;
  $topicTree->authUser = &$PAPAYA_USER;
  $topicTree->layout = &$PAPAYA_LAYOUT;
  $topicTree->initialize(
    empty($_REQUEST['p_id']) ? 0 : (int)$_REQUEST['p_id']
  );
  $topicTree->execute();
  $topicTree->get();
  $topicTree->getButtonsXML();
}
require('inc.footer.php');
?>