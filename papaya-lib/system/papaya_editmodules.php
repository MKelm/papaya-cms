<?php
/**
* Administration class for change modules
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
* @version $Id: papaya_editmodules.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
*  Basic class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Administration class for change modules
* @package Papaya
* @subpackage Administration
*/
class papaya_editmodules extends base_db {
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * Papaya database table module groups
  * @var string $tableModulegroups
  */
  var $tableModulegroups = PAPAYA_DB_TBL_MODULEGROUPS;

  /**
  * Maximum favorite links in main menu bar
  * @var unknown_type
  */
  var $favoriteMax = 5;

  /**
  * Constructor
  *
  * @param string $moduleClass optional, default value ''
  * @access public
  */
  function __construct($moduleClass = '') {
    $this->moduleClass = $moduleClass;
  }

  /**
  * Load modules list
  *
  * @access public
  */
  function loadModulesList() {
    unset($this->modules);
    $sql = "SELECT module_guid, module_title, module_class,
                   module_file, module_path, module_glyph
              FROM %s
             WHERE module_type = 'admin' AND module_active = 1
             ORDER BY module_title";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableModules))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->modules[$row["module_guid"]] = $row;
      }
    }
  }

  /**
  * Get button array
  *
  * @access public
  * @return mixed
  */
  function getButtonArray() {
    $result = NULL;
    if (isset($this->modules) && is_array($this->modules)) {
      foreach ($this->modules as $id => $module) {
        if (isset($this->authUser->userModules) &&
            is_array($this->authUser->userModules) &&
            in_array($id, $this->authUser->userModules)) {
          if ($this->authUser->isAdmin() || $this->authUser->hasModulePerm(1, $id)) {
            if (trim($module['module_glyph']) != '') {
              $glyph = 'modglyph.php?module='.urlencode($module['module_guid']);
            } else {
              $glyph = '';
            }
            $result[] = array($module['module_title'], $module['module_title'],
              $glyph, 0,
              'module_'.$module['module_class'].'.php', '_self',
              $this->moduleClass == $module['module_class'], NULL, TRUE);
          }
        }

      }
      $result[] = array($this->_gt('Applications'), $this->_gt('Applications list'),
        $this->images['categories-applications'], 0, 'module.php', '_self', FALSE, NULL, TRUE);
    }
    return $result;
  }

  /**
  * Load module
  *
  * @access public
  * @return boolean
  */
  function loadModule() {
    unset($this->module);
    $sql = "SELECT m.module_guid, m.module_title, m.module_class, m.module_file,
                   m.module_path, m.module_glyph, m.modulegroup_id,
                   mg.modulegroup_tables
              FROM %s m
              LEFT OUTER JOIN %s mg ON mg.modulegroup_id = m.modulegroup_id
             WHERE module_class = '%s' AND module_active = 1";
    $params = array($this->tableModules,
      $this->tableModulegroups, $this->moduleClass);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->module = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Initialize parameters
  *
  * @access public
  * @return boolean
  */
  function initialize() {
    if ($this->loadModule()) {
      if ($this->checkTables($this->module['modulegroup_tables'])) {
        $parent = NULL;
        include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
        $this->moduleObject = &base_pluginloader::getPluginInstance(
          $this->module['module_guid'],
          $parent,
          NULL,
          $this->module['module_class'],
          $this->module['module_path'].$this->module['module_file']
        );
        if (isset($this->moduleObject) && is_object($this->moduleObject)) {
          $this->moduleObject->guid = $this->module['module_guid'];
          $this->moduleObject->images = &$this->images;
          $this->moduleObject->layout = &$this->layout;
          $this->moduleObject->msgs = &$this->msgs;
          $this->moduleObject->authUser = &$this->authUser;
          $this->layout->setParam(
            'PAGE_TITLE',
            papaya_strings::escapeHTMLChars($this->_gt('Applications')).' - '.
            papaya_strings::escapeHTMLChars($this->module['module_title'])
          );
          $this->layout->setParam(
            'PAGE_ICON',
            './modglyph.php?size=22&module='.urlencode($this->module['module_guid'])
          );
          return TRUE;
        }
      }
    } else {
      $this->loadModulesList();
      if (isset($this->modules) &&
          is_array($this->modules) && count($this->modules) > 0) {
        $this->initializeParams();
        $this->layout->setParam(
          'PAGE_TITLE', papaya_strings::escapeHTMLChars($this->_gt('Applications'))
        );
        $this->layout->setParam('PAGE_ICON', $this->images['categories-applications']);
      } else {
        $this->addMsg(MSG_INFO, 'No modules installed');
      }
    }
    return FALSE;
  }

  /**
  * Execute
  *
  * @access public
  */
  function execute() {
    if (isset($this->moduleObject) && is_object($this->moduleObject)) {
      $this->moduleObject->execModule();
    } elseif (isset($this->modules) &&
       is_array($this->modules) && count($this->modules) > 0) {
      $currentUserModules = array_intersect(
        $this->authUser->userModules, array_keys($this->modules)
      );
      $this->authUser->userModules = $currentUserModules;
      if (isset($this->params['cmd'])) {
        switch ($this->params['cmd']) {
        case 'switch_favorite' :
          if (isset($this->params['module_id']) &&
              isset($this->modules[$this->params['module_id']])) {
            if (in_array($this->params['module_id'], $this->authUser->userModules)) {
              $moduleIndex = array_search($this->params['module_id'], $this->authUser->userModules);
              unset($this->authUser->userModules[$moduleIndex]);
              if ($this->authUser->saveUserOption(
                    'PAPAYA_USER_MODULES', implode(',', $this->authUser->userModules))) {
                $this->addMsg(MSG_INFO, $this->_gt('Favorite removed.'));
              } else {
                $this->authUser->userModules = $currentUserModules;
                $this->addMsg(MSG_INFO, $this->_gt('Database Error.'));
              }
            } else {
              if ($this->favoriteMax > count($this->authUser->userModules)) {
                $this->authUser->userModules[] = $this->params['module_id'];
                if ($this->authUser->saveUserOption(
                      'PAPAYA_USER_MODULES', implode(',', $this->authUser->userModules))) {
                  $this->addMsg(MSG_INFO, $this->_gt('Favorite added.'));
                } else {
                  $this->authUser->userModules = $currentUserModules;
                  $this->addMsg(MSG_INFO, $this->_gt('Database Error.'));
                }
              } else {
                $this->addMsg(MSG_INFO, $this->_gt('Favorite limit reached.'));
              }
            }
          }
          break;
        }
      }
      $this->getModulesListView();
    }
  }

  /**
  * Check database tables
  *
  * @param string $tableString
  * @access public
  * @return boolean
  */
  function checkTables($tableString) {
    $result = TRUE;
    if (trim($tableString) != '') {
      $tables = explode(',', $tableString);
      if (isset($tables) && is_array($tables) && count($tables) > 0 &&
          is_array($dbTables = $this->databaseQueryTableNames())) {
        $dbTables = array_flip($dbTables);
        foreach ($tables as $tableName) {
          if (!isset($dbTables[PAPAYA_DB_TABLEPREFIX.'_'.$tableName])) {
            include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
            $hidden = array(
              'pkg_id' => (int)$this->module['modulegroup_id'],
              'module_id' => 0,
              'table' => '',
            );
            $msg = $this->_gt('Missing tables - Go to module managment?');
            $dialog = new base_msgdialog($this, 'mods', $hidden, $msg, 'warning');
            $dialog->msgs = &$this->msgs;
            $dialog->buttonTitle = 'Goto';
            $dialog->baseLink = 'modules.php';
            if ($str = $dialog->getMsgDialog()) {
              $this->layout->add($str);
            }
            return FALSE;
          }
        }
        $data = array('modulegroup_tables'=>'');
        $this->databaseUpdateRecord(
          $this->tableModulegroups,
          $data,
          'modulegroup_id',
          (int)$this->module['modulegroup_id']
        );
      }
    }
    return $result;
  }

  /**
  * Get modules listview xml
  * @return void
  */
  function getModulesListView() {
    $result = '';
    if (isset($this->modules) &&
      is_array($this->modules) && count($this->modules) > 0) {
      $modules = array();
      foreach ($this->modules as $id => $module) {
        if ($this->authUser->isAdmin() || $this->authUser->hasModulePerm(1, $id)) {
          $modules[] = $id;
        }
      }
      if (count($modules) > 0) {
        $result = sprintf(
          '<listview title="%s" mode="tile">',
          papaya_strings::escapeHTMLChars($this->_gt('Applications'))
        );
        $result .= '<items>';
        foreach ($modules as $id) {
          $module = &$this->modules[$id];
          if (trim($module['module_glyph']) != '') {
            $glyph = './modglyph.php?module='.urlencode($module['module_guid']);
          } else {
            $glyph = '';
          }
          list($basePath) = explode('/', $module['module_path'], 2);
          $result .= sprintf(
            '<listitem image="%s" href="%s" title="%s" subtitle="%s">',
            papaya_strings::escapeHTMLChars($glyph),
            papaya_strings::escapeHTMLChars('module_'.$module['module_class'].'.php'),
            papaya_strings::escapeHTMLChars($module['module_title']),
            papaya_strings::escapeHTMLChars($basePath.'/...')
          );
          if (in_array($id, $this->authUser->userModules)) {
            $result .= sprintf(
              '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(array('cmd' => 'switch_favorite', 'module_id' =>$id))
              ),
              papaya_strings::escapeHTMLChars($this->images['items-favorite']),
              papaya_strings::escapeHTMLChars($this->_gt('Remove from menu'))
            );
          } elseif (count($this->authUser->userModules) < $this->favoriteMax) {
            $result .= sprintf(
              '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(array('cmd' => 'switch_favorite', 'module_id' => $id))
              ),
              papaya_strings::escapeHTMLChars($this->images['status-favorite-disabled']),
              papaya_strings::escapeHTMLChars($this->_gt('Add to menu'))
            );
          }
          $result .= '</listitem>';

        }
        $result .= '</items>';
        $result .= '</listview>';
      }
      $this->layout->add($result);
    }
  }
}
?>
