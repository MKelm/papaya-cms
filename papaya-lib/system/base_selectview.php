<?php
/**
* Select view class
*
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
* @version $Id: base_selectview.php 37828 2012-12-14 10:45:52Z weinert $
*/


/**
* Basic class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');
/**
* Select view class
*
* Show a listview with views
*
* @package Papaya
* @subpackage Administration
*/
class base_selectview extends base_db {

  /**
  * Papaya database table views
  * @var string $tableViews
  */
  var $tableViews = PAPAYA_DB_TBL_VIEWS;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * Papaya database table module groups
  * @var string $tableModuleGroups
  */
  var $tableModuleGroups = PAPAYA_DB_TBL_MODULEGROUPS;

  /**
  * Views
  * @var array $views
  */
  var $views;
  /**
  * Module groups
  * @var array $moduleGroups
  */
  var $moduleGroups;
  /**
  * Module group views
  * @var array $moduleGroupViews
  */
  var $moduleGroupViews;

  /**
  * Module type
  * @var string $moduleType
  */
  var $moduleType = '';
  /**
  * Current view
  * @var array $currentView
  */
  var $currentView = NULL;

  /**
  * Action link
  * @var string $actionLink
  */
  var $actionLink = '';

  var $paramName = 'views';
  /**
  * Load module and view
  *
  * @param integer $viewId
  * @param string $moduleType
  * @access public
  */
  function load($viewId, $moduleType, $pageIds = NULL) {
    $this->moduleType = $moduleType;
    $this->loadModuleGroups();
    $this->loadViews($pageIds);
    $this->loadView($viewId);
  }

  /**
  * Load view details
  *
  * @param integer $viewId
  * @access public
  * @return boolean
  */
  function loadView($viewId) {
    unset($this->currentView);
    $sql = "SELECT v.view_id, v.view_title, v.view_is_deprecated,
                    m.module_guid, m.module_title, m.module_file,
                    m.modulegroup_id
              FROM %s v, %s m
             WHERE m.module_guid = v.module_guid
               AND m.module_active = 1
               AND v.view_id = '%d'";
    $params = array($this->tableViews, $this->tableModules, $viewId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->currentView = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load view and module information
  *
  * @access public
  */
  function loadViews($pageIds = NULL) {
    unset($this->views);
    $sql = "SELECT v.view_id, v.view_title, v.view_limits, v.view_is_deprecated,
                    m.module_guid, m.module_title, m.module_description,
                    m.modulegroup_id
              FROM %s v, %s m
             WHERE m.module_guid = v.module_guid
               AND m.module_type = '%s'
               AND m.module_active = 1
             ORDER BY v.view_title, m.module_title";
    $params = array($this->tableViews, $this->tableModules, $this->moduleType);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (isset($pageIds) && is_array($pageIds) && count($pageIds) > 0 &&
            isset($row['view_limits']) && !empty($row['view_limits']) &&
            preg_match_all('~\d+~', $row['view_limits'], $matches)) {
          if (isset($matches[0]) && is_array($matches[0]) && count($matches[0]) > 0 &&
               !in_array(0, $matches[0])) {
            if (!count(array_intersect($pageIds, $matches[0])) > 0) {
              continue;
            }
          }
        }
        $this->views[$row['view_id']] = $row;
        $this->moduleGroupViews[$row['modulegroup_id']][$row['view_id']] =
          &$this->views[$row['view_id']];
      }
    }
  }

  /**
  * Load all module groups
  *
  * @access public
  */
  function loadModuleGroups() {
    unset($this->moduleGroups);
    $sql = "SELECT modulegroup_id, modulegroup_title
              FROM %s
             ORDER BY modulegroup_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableModuleGroups)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->moduleGroups[$row['modulegroup_id']] = $row;
      }
    }
  }

  /**
  * Get xml view list
  *
  * @access public
  * @return string $result xml
  */
  function getXMLViewList() {
    if (isset($this->views) && is_array($this->views) &&
        isset($this->moduleGroups) && is_array($this->moduleGroups)) {

      include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
      $this->lngSelect = &base_language_select::getInstance();

      $this->sessionParamName = get_class($this).'_views';
      $this->initializeParams();
      $this->sessionParams = $this->getSessionValue($this->sessionParamName);
      if (isset($this->params['cmd'])) {
        switch ($this->params['cmd']) {
        case 'open' :
          if (isset($this->params['group_id']) && $this->params['group_id'] > 0) {
            $this->sessionParams['opengroups'][(int)$this->params['group_id']] = TRUE;
          }
          break;
        case 'close' :
          if (isset($this->params['group_id']) && $this->params['group_id'] > 0 &&
              isset($this->sessionParams['opengroups']) &&
              isset($this->sessionParams['opengroups'][(int)$this->params['group_id']])) {
            unset($this->sessionParams['opengroups'][(int)$this->params['group_id']]);
          }
          break;
        }
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
      }

      $result = sprintf(
        '<listview title="%s%s" icon="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->lngSelect->getCurrentLanguageTitle()),
        papaya_strings::escapeHTMLChars($this->_gt('Select view and module')),
        papaya_strings::escapeHTMLChars($this->lngSelect->getCurrentLanguageIcon())
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('View'))
      );
      if ($this->authUser->hasPerm(PapayaAdministrationPermissions::VIEW_MANAGE)) {
        $result .= '<col/>';
      }
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Module'))
      );
      $result .= '<col/>';
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      foreach ($this->moduleGroups as $groupId => $group) {
        if (isset($this->moduleGroupViews[$groupId]) &&
            is_array($this->moduleGroupViews[$groupId])) {
          if ((
               isset($this->sessionParams['opengroups']) &&
               isset($this->sessionParams['opengroups'][$groupId]) &&
               $this->sessionParams['opengroups'][$groupId]
              ) ||
              (
               isset($this->moduleGroupViews[$groupId]) &&
               isset($this->currentView['view_id']) &&
               isset($this->moduleGroupViews[$groupId][$this->currentView['view_id']])
              )) {
            $nodeStatus = 'open';
            $nodeHref = $this->getLink(array('cmd' => 'close', 'group_id' => (int)$groupId));
            $imageIdx = 'status-folder-open';
          } else {
            $nodeStatus = 'close';
            $nodeHref = $this->getLink(array('cmd' => 'open', 'group_id' => (int)$groupId));
            $imageIdx = 'items-folder';
          }
          $result .= sprintf(
            '<listitem title="%s" image="%s" nhref="%s" node="%s">'.LF,
            papaya_strings::escapeHTMLChars($group['modulegroup_title']),
            papaya_strings::escapeHTMLChars($this->images[$imageIdx]),
            papaya_strings::escapeHTMLChars($nodeHref),
            papaya_strings::escapeHTMLChars($nodeStatus)
          );
          $result .= '<subitem span="3"/>'.LF;
          $result .= '</listitem>'.LF;
          if ($nodeStatus == 'open') {
            foreach ($this->moduleGroupViews[$groupId] as $viewId => $view) {
              if (isset($this->currentView['view_id'])
                  && $viewId == $this->currentView['view_id']) {
                $selected = ' selected="selected"';
                $selectable = FALSE;
              } else {
                $selected = '';
                $selectable = TRUE;
              }
              if ($view['view_is_deprecated'] && $selectable) {
                continue;
              }

              $href = $this->actionLink.(int)$viewId;
              $result .= sprintf(
                '<listitem title="%s" href="%s" indent="2" hint="%s" image="%s"%s>'.LF,
                papaya_strings::escapeHTMLChars($view['view_title']),
                papaya_strings::escapeHTMLChars($href),
                papaya_strings::escapeHTMLChars($view['module_description']),
                papaya_strings::escapeHTMLChars($this->images['items-view']),
                $selected
              );
              if ($this->authUser->hasPerm(PapayaAdministrationPermissions::VIEW_MANAGE)) {
                $result .= sprintf(
                  '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a>'.
                  '</subitem>'.LF,
                  $this->getLink(
                    array('cmd' => 'view_select', 'view_id' => (int)$viewId),
                    'vl',
                    'views.php'
                  ),
                  papaya_strings::escapeHTMLChars($this->images['actions-edit']),
                  papaya_strings::escapeHTMLChars('Edit view')
                );
              }
              $result .= sprintf(
                '<subitem>%s</subitem>'.LF,
                papaya_strings::escapeHTMLChars($view['module_title'])
              );
              if ($selectable) {
                $result .= sprintf(
                  '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a>'.
                  '</subitem>'.LF,
                  papaya_strings::escapeHTMLChars($href),
                  papaya_strings::escapeHTMLChars($this->images['actions-list-add']),
                  papaya_strings::escapeHTMLChars('Edit view')
                );
              } else {
                $result .= '<subitem/>'.LF;
              }
              $result .= '</listitem>'.LF;
            }
          }
        }
      }
      $result .= '</items>'.LF;
      if ($this->authUser->hasPerm(PapayaAdministrationPermissions::VIEW_MANAGE)) {
        $result .= '<menu>'.LF;
        $result .= sprintf(
          '<button href="views.php" title="%s" glyph="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Add view')),
          papaya_strings::escapeHTMLChars($this->images['actions-view-add'])
        );
        $result .= '</menu>'.LF;
      }
      $result .= '</listview>'.LF;
      return $result;
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('No views defined.'));
      return '';
    }
  }

  /**
  * generate list of selected views (similar to active boxes)
  *
  * @todo determine whether this is the correct place for this function
  *       or whether it should go to actbox_page_tatistic or elsewhere
  * @see actbox_page_statistic::getModulesList()
  * @param array $displayViews list of view_ids to select
  * @param boolean $displaySelected if TRUE, shows selected views,
  *          else it shows all views NOT selected
  * @param string $result XML string of listview
  */
  function getXMLMultipleViewsList($displayViews, $displaySelected) {
    if (isset($this->views) && is_array($this->views) &&
        isset($this->moduleGroups) && is_array($this->moduleGroups)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
         papaya_strings::escapeHTMLChars($this->_gt('Views'))
      );
      $result .= '<items>'.LF;
      foreach ($this->moduleGroups as $groupId => $group) {
        $resultGroup = '';
        if (isset($this->moduleGroupViews[$groupId]) &&
            is_array($this->moduleGroupViews[$groupId])) {
          $resultGroup .= sprintf(
            '<listitem title="%s" image="%s">'.LF,
            papaya_strings::escapeHTMLChars($group['modulegroup_title']),
            papaya_strings::escapeHTMLChars($this->images['status-folder-open'])
          );
          $resultGroup .= '<subitem/><subitem/>'.LF;
          $resultGroup .= '</listitem>'.LF;
          foreach ($this->moduleGroupViews[$groupId] as $viewId => $view) {
            if ((
                 $displaySelected &&
                 is_array($displayViews) &&
                 in_array($viewId, $displayViews)
                ) ||
                (
                 !$displaySelected &&
                 (
                  !is_array($displayViews) || !in_array($viewId, $displayViews)
                 )
                )) {
              $displayGroup = TRUE;
              $href = $this->actionLink.(int)$viewId;
              $resultGroup .= sprintf(
                '<listitem title="%s" href="%s" indent="1" hint="%s" image="%s">'.LF,
                papaya_strings::escapeHTMLChars($view['view_title']),
                papaya_strings::escapeHTMLChars($href),
                papaya_strings::escapeHTMLChars($view['module_description']),
                papaya_strings::escapeHTMLChars($this->images['items-view'])
              );
              $resultGroup .= sprintf(
                '<subitem>%s</subitem>'.LF,
                papaya_strings::escapeHTMLChars($view['module_title'])
              );
              if ($displaySelected) {
                $deleteText = papaya_strings::escapeHTMLChars($this->_gt('Delete'));
                $resultGroup .= sprintf(
                  '<subitem><a href="%s"><glyph src="%s" hint="%s" alt="%s"/></a></subitem>'.LF,
                  papaya_strings::escapeHTMLChars($href),
                  papaya_strings::escapeHTMLChars($this->images['actions-list-remove']),
                  $deleteText,
                  $deleteText
                );
              } else {
                $selectText = papaya_strings::escapeHTMLChars($this->_gt('Select'));
                $resultGroup .= sprintf(
                  '<subitem><a href="%s"><glyph src="%s" hint="%s" alt="%s"/></a></subitem>'.LF,
                  papaya_strings::escapeHTMLChars($href),
                  papaya_strings::escapeHTMLChars($this->images['actions-list-add']),
                  $selectText,
                  $selectText
                );
              }
              $resultGroup .= '</listitem>'.LF;
            }
          }
        }
        if (isset($displayGroup) && $displayGroup) {
          $result .= $resultGroup;
          $displayGroup = FALSE;
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      return $result;
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('No views defined.'));
      return '';
    }
  }

}
?>
