<?php
/**
* Create base navigagtion for papaya admin area
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
* @version $Id: papaya_navigation.php 38360 2013-04-04 10:47:15Z weinert $
*/

/**
* Basic class base_nav
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_object.php');
/**
* Create base navigagtion for papaya admin area
*
* @package Papaya
* @subpackage Administration
*/
class papaya_navigation extends base_object {

  /**
  * Menu
  * @var array $menu
  *   array values
  *   0) title
  *   1) tooltip
  *   2) icon
  *   3) permission
  *   4) url
  *   5) target
  *   6) force button down
  *   7) access key
  *   8) do not translate button title and tooltip
  */
  var $menu = array(
    'general' => array(
      array(
        'Overview',
        'Last messages, todos and page changes',
        'places-home',
        0,
        'index.php',
        '_self',
        FALSE
      ),
      array(
        'Messages',
        'Messages / ToDo',
        'status-mail-open',
        PapayaAdministrationPermissions::MESSAGES,
        'msgbox.php',
        '_self',
        FALSE
      ),
    ),
    'pages' => array (
      array(
        'Sitemap',
        'All pages in a tree',
        'categories-sitemap',
        PapayaAdministrationPermissions::PAGE_MANAGE,
        'tree.php',
        '_self',
        FALSE,
        'T'
      ),
      array(
        'Search',
        'Search pages',
        'actions-search',
        PapayaAdministrationPermissions::PAGE_MANAGE,
        'search.php',
        '_self',
        FALSE
      ),
      array(
        'Edit',
        'Edit pages',
        'items-page',
        PapayaAdministrationPermissions::PAGE_MANAGE,
        'topic.php',
        '_self',
        FALSE,
        'E'
      )
    ),
    'additional' => array(
      array(
        'Boxes',
        'Edit boxes',
        'items-box',
        PapayaAdministrationPermissions::BOX_MANAGE,
        'boxes.php'
      ),
      array(
        'Files',
        'Media database',
        'items-folder',
        PapayaAdministrationPermissions::FILE_MANAGE,
        'mediadb.php',
        '_self',
        FALSE,
        'M'
      ),
      array(
        'Aliases',
        'Aliases for pages',
        'items-alias',
        PapayaAdministrationPermissions::ALIAS_MANAGE,
        'alias.php'
      ),
      array(
        'Tags',
        'Manage Tags',
        'items-tag',
        PapayaAdministrationPermissions::TAG_MANAGE,
        'tags.php'
      ),
    ),
    'modules' => array(
    ),
    'administration' => array(
      array(
        'Users',
        'User management',
        'items-user-group',
        PapayaAdministrationPermissions::USER_MANAGE,
        'auth.php'
      ),
      array(
        'Views',
        'Configure Views',
        'items-view',
        PapayaAdministrationPermissions::VIEW_MANAGE,
        'views.php'
      ),
      array(
        'Modules',
        'Modules management',
        'items-plugin',
        PapayaAdministrationPermissions::MODULE_MANAGE,
        'modules.php'
      ),
      array(
        'Themes',
        'Configure Dynamic Themes',
        'items-theme',
        PapayaAdministrationPermissions::SYSTEM_THEMESET_MANAGE,
        'themes.php'
      ),
      array(
        'Images',
        'Configure Dynamic Images',
        'items-graphic',
        PapayaAdministrationPermissions::IMAGE_GENERATOR,
        'imggen.php'
      ),
      array(
        'Settings',
        'System configuration',
        'items-option',
        PapayaAdministrationPermissions::SYSTEM_SETTINGS,
        'options.php'
      ),
      array(
        'Protocol',
        'Event protocol',
        'categories-protocol',
        PapayaAdministrationPermissions::SYSTEM_PROTOCOL,
        'log.php'
      ),
      array(
        'Translations',
        'Interface Translations',
        'items-translation',
        PapayaAdministrationPermissions::SYSTEM_TRANSLATE,
        'phrases.php'
      )
    ),
  );

  var $menuGroups = array(
    'general' => 'General',
    'pages' => 'Pages',
    'additional' => 'Additional',
    'modules' => 'Applications',
    'administration' => 'Administration'
  );

  /**
  * Initialization
  *
  * @access public
  */
  function initialize($fileName = NULL) {
    $this->getEditModules();
    $menuStr = $this->getMenuBar($fileName);
    $this->layout->addMenu($menuStr);
    $this->getNewMessageCount();
  }

  /**
  * Get count of new message for the current user
  */
  function getNewMessageCount() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_messages.php');
    $messages = new base_messages();
    $messages->authUser = $this->authUser;
    $counts = $messages->loadMessageCounts(array(0), TRUE);
    $this->layout->setParam(
      'PAPAYA_MESSAGES_INBOX_NEW',
      empty($counts[0]) ? 0 : (int)$counts[0]
    );
  }

  /**
  * Get edit modules
  *
  * @access public
  */
  function getEditModules() {
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_editmodules.php');
    $obj = new papaya_editmodules(empty($_GET['p_module']) ? '' : $_GET['p_module']);
    $obj->authUser = &$this->authUser;
    $obj->images = &$this->images;
    $obj->loadModulesList();
    $modules = $obj->getButtonArray();
    if (isset($modules) && is_array($modules)) {
      $this->menu['modules'] = array_merge($this->menu['modules'], $modules);
    }
  }

  /**
  * Get main manubar xml
  * @param string $fileName
  * @return string
  */
  function getMenuBar($fileName = '') {
    $menu = new PapayaUiMenu();
    $menu->identifier = 'main';
    $currentUrl = $this->papaya()->request->getUrl()->getPathUrl();
    foreach ($this->menuGroups as $groupId => $groupTitle) {
      if (isset($this->menu[$groupId])) {
        $group = new PapayaUiToolbarGroup($groupTitle);
        foreach ($this->menu[$groupId] as  $buttonId => $buttonData) {
          if (empty($buttonData[3]) || ($this->authUser->hasPerm($buttonData[3]))) {
            $button = new PapayaUiToolbarButton();
            $button->image = $buttonData[2];
            if (isset($buttonData[8]) && $buttonData[8]) {
              $button->caption = empty($buttonData[0]) ? '' : $buttonData[0];
              $button->hint = empty($buttonData[1]) ? '' : $buttonData[1];
            } else {
              $button->caption = new PapayaUiStringTranslated(
                empty($buttonData[0]) ? '' : $buttonData[0]
              );
              $button->hint = new PapayaUiStringTranslated(
                empty($buttonData[1]) ? '' : $buttonData[1]
              );
            }
            if (!empty($buttonData[7])) {
              $button->accessKey = $buttonData[7];
            }
            $button->target = empty($buttonData[5]) ? '_self' : $buttonData[5];
            $button->reference->setRelative(
              empty($buttonData[4]) ? '' : $buttonData[4]
            );
            if ($button->reference->url()->getPathUrl() == $currentUrl) {
              $button->selected = TRUE;
            }
            $group->elements[] = $button;
          }
        }
        $menu->elements[] = $group;
      }
    }
    return $menu->getXml();
  }
}