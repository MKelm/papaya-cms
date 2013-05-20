<?php
/**
* Translation managment (Admin UI)
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
* @version $Id: phrases.php 37754 2012-11-30 12:01:23Z weinert $
*/

/**
* Authentication
*/
require_once("./inc.auth.php");

if ($PAPAYA_SHOW_ADMIN_PAGE &&
    $PAPAYA_USER->hasPerm(PapayaAdministrationPermissions::SYSTEM_TRANSLATE)) {
  initNavigation();
  $PAPAYA_LAYOUT->setParam('PAGE_TITLE', _gt('Administration').' - '._gt('Translations'));
  $PAPAYA_LAYOUT->setParam('PAGE_ICON', $PAPAYA_IMAGES['items-translation']);

  include_once(PAPAYA_INCLUDE_PATH.'system/base_languages.php');

  $languageEditor = new base_languages();
  $languageEditor->layout = &$PAPAYA_LAYOUT;
  $languageEditor->images = &$PAPAYA_IMAGES;
  $languageEditor->msgs = &$PAPAYA_MSG;
  $languageEditor->authUser = &$PAPAYA_USER;

  $languageEditor->initialize();
  $languageEditor->execute();

  $languageEditor->getXML();
}
require('inc.footer.php');
?>