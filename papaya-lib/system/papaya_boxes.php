<?php
/**
* Manage action boxes
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
* @version $Id: papaya_boxes.php 38384 2013-04-10 14:50:30Z weinert $
*/

/**
* base class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Include input check class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Manage action boxes
*
* @package Papaya
* @subpackage Administration
*/
class papaya_boxes extends base_db {
  /**
  * Papaya database table box
  * @var string $tableBox
  */
  var $tableBox = PAPAYA_DB_TBL_BOX;
  /**
  * Papaya database table box translation
  * @var string $tableBoxTrans
  */
  var $tableBoxTrans = PAPAYA_DB_TBL_BOX_TRANS;
  /**
  * Papaya database table box versions
  * @var string $tableBoxVersions
  */
  var $tableBoxVersions = PAPAYA_DB_TBL_BOX_VERSIONS;
  /**
  * Papaya database table box versions of translation
  * @var string $tableBoxVersionsTrans
  */
  var $tableBoxVersionsTrans = PAPAYA_DB_TBL_BOX_VERSIONS_TRANS;
  /**
  * Papaya database table box public
  * @var string $tableBoxPublic
  */
  var $tableBoxPublic = PAPAYA_DB_TBL_BOX_PUBLIC;
  /**
  * Papaya database table box public translation
  * @var string $tableBoxPublicTrans
  */
  var $tableBoxPublicTrans = PAPAYA_DB_TBL_BOX_PUBLIC_TRANS;
  /**
  * Papaya database table boxgroup
  * @var string $tableBoxgroup
  */
  var $tableBoxgroup = PAPAYA_DB_TBL_BOXGROUP;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;
  /**
  * Papaya database table views
  * @var string $tableViews
  */
  var $tableViews = PAPAYA_DB_TBL_VIEWS;
  /**
  * Papaya database table boxlinks
  * @var string $tableLink
  */
  var $tableLink = PAPAYA_DB_TBL_BOXLINKS;
  /**
  * Papaya database table authentification user
  * @var string $tableAuthUser
  */
  var $tableAuthUser = PAPAYA_DB_TBL_AUTHUSER;
  /**
  * Papaya database table topics trans
  * @var string $tableTopicTrans
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;
  /**
  * Papaya database table topics trans
  * @var string $tableTopicTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;

  /**
  * Papaya database table maximum count
  * @var string $maxVersions
  */
  var $maxVersions = -1;

  /**
  * list of boxes
  * @var array $boxesList
  */
  var $boxesList;
  /**
  * box $box
  * @var array
  */
  var $box;
  /**
  * modules of box
  * @var array $boxModules
  */
  var $boxModules;
  /**
  * new box $boxNew
  * @var mixed
  */
  var $boxNew;
  /**
  * Object reference for error messages
  * @var object base_errors $msgs
  */
  var $msgs;

  /**
  * parameter name
  * @var string $paramName
  */
  var $paramName;
  /**
  * parameter
  * @var array $params
  */
  var $params;

  /**
  * Constructor
  *
  * @param string $paramName optional, default value 'bb'
  * @access public
  */
  function __construct($paramName = 'bb') {
    $this->paramName = $paramName;
    $this->initializeParams();
  }

  /**
  * Initialisation of class variable
  *
  * @access public
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_boxes'.$this->paramName;
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    //$this->initializeSessionParam('cmd');
    $this->initializeSessionParam('mode');
    $this->initializeSessionParam('gid');
    $this->initializeSessionParam('bid');
    $this->initializeSessionParam('viewmode');
    $this->initializeNodes();
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Initialisation of nodes
  *
  * @access public
  */
  function initializeNodes() {
    if (isset($this->sessionParams['opened'])) {
      $this->opened = $this->sessionParams['opened'];
    } else {
      $this->opened = array();
    }
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'open' :
        if ($this->params['gid'] > 0) {
          $this->opened[$this->params['gid']] = TRUE;
        }
        break;
      case 'close' :
        if (isset($this->opened[$this->params['gid']])) {
          unset($this->opened[$this->params['gid']]);
        }
        break;
      }
      $this->sessionParams['opened'] = $this->opened;
    }
  }

  /**
  * Access function
  *
  * @access public
  */
  function execute() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $this->lngSelect = &base_language_select::getInstance();

    $this->loadList();
    $this->loadTemplateGroupsList();

    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    switch ($this->params['cmd']) {
    case 'add_translation' :
      if (isset($this->params['bid']) &&
          isset($this->params['lng_id']) &&
          $this->params['lng_id'] == $this->lngSelect->currentLanguageId &&
          $this->load($this->params['bid'], $this->lngSelect->currentLanguageId)) {
        $this->loadTranslationsInfo();
        $this->initializeAddTranslationDialog();
        if ($this->dialogAddTranslation->checkDialogInput()) {
          $this->loadTranslation($this->box['box_id'], $this->params['copy_lng_id']);
          if ($this->createTranslation($this->lngSelect->currentLanguageId)) {
            $this->loadTranslation($this->box['box_id'], $this->lngSelect->currentLanguageId);
          } elseif (isset($this->box['TRANSLATION'])) {
            unset($this->box['TRANSLATION']);
          }
        }
      }
      break;
    case 'translation_delete' :
      if (isset($this->params['confirm_delete']) &&
          $this->params['confirm_delete'] &&
          isset($this->params['bid']) &&
          isset($this->params['lng_id']) &&
          $this->params['lng_id'] == $this->lngSelect->currentLanguageId &&
          $this->load($this->params['bid'], $this->lngSelect->currentLanguageId)) {
        if ($this->deleteTranslation($this->params['bid'], $this->params['lng_id'])) {
          $this->addMsg(
            MSG_INFO,
            $this->_gt('Translation deleted.')
          );
          unset($this->box['TRANSLATION']);
          $this->params['cmd'] = '';
        }
      }
      break;
    case "box_add":
      $newId = $this->create(
        (int)$this->params['gid'],
        $this->lngSelect->currentLanguageId,
        isset($this->params['bid']) ? (int)$this->params['bid'] : 0
      );
      if ($newId) {
        $this->addMsg(MSG_INFO, $this->_gt('New box created.'));
        $this->params['bid'] = $newId;
        $this->sessionParams['opened'][$this->params['gid']] = TRUE;
        $this->initializeSessionParam('bid');
        $this->loadList();
      }
      break;
    case "box_edit":
      if ($this->load($this->params['bid'], $this->lngSelect->currentLanguageId)) {
        $this->initializePropertiesDialog();
        if ($this->dialogProperties->checkDialogInput()) {
          if ($this->save()) {
            unset($this->box);
            unset($this->dialogProperties);
            $this->loadList();
          }
        }
      }
      break;
    case "box_delete":
      if ($str = $this->deleteBox($this->params['bid'])) {
        $this->layout->add($str);
      }
      break;
    case "chg_view":
      if (isset($this->params['view_id']) && $this->params['view_id'] > 0 &&
          $this->load($this->params['bid'], $this->lngSelect->currentLanguageId)) {
        if ($this->saveView()) {
          $this->addMsg(
            MSG_INFO, $this->_gt('View modified.')
          );
          unset($this->box);
        }
      }
      break;
    case "group_add":
      if (isset($this->params['confirm_add']) && $this->params['confirm_add']) {
        $this->initializeGroupDialog();
        if ($this->dialogGroup->checkDialogInput()) {
          if ($newId = $this->createGroup($this->dialogGroup->data)) {
            $this->params['gid'] = $newId;
            unset($this->dialogGroup);
            $this->initializeSessionParam('gid', array('bid'));
            $this->loadGroupList();
            $this->addMsg(MSG_INFO, $this->_gt('New group created.'));
          } else {
            $this->addMsg(
              MSG_ERROR, $this->_gt('Could not add group.')
            );
          }
        }
      }
      break;
    case "group_edit":
      if (isset($this->params['confirm_edit']) &&
          $this->params['confirm_edit'] &&
          $this->loadGroup($this->params['gid'])) {
        $this->initializeGroupDialog();
        if ($this->dialogGroup->checkDialogInput()) {
          if ($this->saveGroup($this->params['gid'], $this->dialogGroup->data)) {
            $this->addMsg(MSG_INFO, $this->_gt('Group modified.'));
            $this->loadGroupList();
          }
        }
      }
      break;
    case "group_delete":
      if ($str = $this->deleteGroup($this->params['gid'])) {
        $this->layout->add($str);
      }
      break;
    case 'open_panel' :
      if (isset($this->params['panel'])) {
        $this->sessionParams['panel_state'][$this->params['panel']] = 'open';
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
      }
      break;
    case 'close_panel' :
      if (isset($this->params['panel'])) {
        $this->sessionParams['panel_state'][$this->params['panel']] = 'closed';
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
      }
      break;
    case 'search' :
      if (!empty($this->params['search_string'])) {
        if (isset($this->params['search_offset']) &&
            $this->params['search_offset'] > 0 &&
            isset($this->params['search_next'])) {
          $this->searchResult = $this->searchBox(
            $this->lngSelect->currentLanguageId,
            $this->params['search_string'],
            $this->params['search_offset']
          );
        } else {
          $this->searchResult = $this->searchBox(
            $this->lngSelect->currentLanguageId,
            $this->params['search_string']
          );
        }
        if ($this->searchResult) {
          $this->params['gid'] = $this->searchResult['boxgroup_id'];
          $this->params['bid'] = $this->searchResult['box_id'];
          $this->initializeSessionParam('bid');
          $this->initializeSessionParam('gid');
          $this->opened[$this->searchResult['boxgroup_id']] = TRUE;
          $this->sessionParams['opened'] = $this->opened;
        }
      }
      break;
    }

    if (isset($this->params['gid']) && $this->params['gid'] > 0) {
      $this->loadGroup($this->params['gid']);
    }

    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Build site
  *
  * @access public
  */
  function getXML() {
    if (isset($this->params['bid']) &&
        $this->params['bid'] > 0 &&
        $this->load($this->params['bid'], $this->lngSelect->currentLanguageId)) {
      if (isset($this->box) && isset($this->box['TRANSLATION']) &&
          is_array($this->box['TRANSLATION'])) {
        if (empty($this->params['cmd']) ||
            !in_array($this->params['cmd'], array("box_delete", 'translation_delete'))) {
          if (!isset($this->params['mode'])) {
            $this->params['mode'] = 0;
          }
          switch ($this->params['mode']) {
          case 1:
            $this->layout->add($this->editContent());
            break;
          case 2:
            if ($this->authUser->hasPerm(PapayaAdministrationPermissions::PAGE_PUBLISH)) {
              $this->loadTranslationsInfo();
              $this->layout->add($this->publishExecute());

              $this->load($this->params['bid'], $this->lngSelect->currentLanguageId);
              $this->loadList();
              $this->loadTranslationsInfo();
              $this->layout->add($this->getPublicData());
            } else {
              $this->loadList();
            }

            if ($this->loadOldVersion()) {
              $this->layout->add($this->versionExecute());
              $this->getVersionInfos();
            }

            $this->loadVersionsList($this->params['bid']);
            $this->layout->add($this->getVersionsList());
            break;
          case 3:
            //preview frame
            include_once(PAPAYA_INCLUDE_PATH.'system/papaya_output.php');
            $outputObj = new papaya_output();
            $views = $outputObj->loadViewsList($this->box['TRANSLATION']['view_id']);

            $currentView = 'xml';
            $viewLinks = array();
            if (count($views) > 0) {
              $viewMode = NULL;
              $viewLinks['xml'] = 'xml';
              if (isset($this->params['viewmode'])) {
                $currentView = $this->params['viewmode'];
              } elseif (defined(PAPAYA_URL_EXTENSION)) {
                $currentView = PAPAYA_URL_EXTENSION;
              } else {
                $currentView = 'html';
              }
              foreach ($views as $view) {
                $viewLinks[$view['viewmode_ext']] = $view['viewmode_ext'];
                if ($view['viewmode_ext'] == $currentView) {
                  $viewMode = $currentView.'.preview';
                }
              }
              if (!isset($viewMode)) {
                $viewMode = 'xml.preview';
                if ($currentView != 'xml') {
                  $this->addMsg(
                    MSG_WARNING,
                    $this->_gt('This page has currently no default output - showing XML.')
                  );
                }
              }
            } else {
              $viewMode = 'xml.preview';
              $this->addMsg(
                MSG_WARNING,
                $this->_gt('This page has currently no formatted output - showing XML.')
              );
            }
            $this->layout->add(
              $this->getPreviewFrame('Preview', $viewMode, $viewLinks, $currentView)
            );
            break;
          case 5:
            $this->layout->addRight($this->getBoxInfos());
            $this->layout->add($this->getEditView());
            break;
          default:
            $this->layout->addRight($this->getBoxInfos());
            $this->layout->add($this->getPropertiesDialog());
          }
        } elseif ($this->params['cmd'] == 'translation_delete') {
          $this->layout->add($this->getDeleteTranslationDialog());
        }
      } else {
        $this->layout->addRight($this->getBoxInfos());
        $this->layout->add($this->addTranslationDialog());
        $this->layout->add($this->getPropertiesDialog());
      }
    } elseif (isset($this->params['cmd']) &&
              in_array($this->params['cmd'], array('group_add', 'group_edit'))) {
      $this->layout->add($this->getGroupDialogXML());
    }

    $this->layout->addLeft($this->getXMLSearchBox());
    $this->layout->addLeft(
      $this->getXMLList(
        isset($this->params['bid']) ? $this->params['bid'] : 0,
        isset($this->params['gid']) ? $this->params['gid'] : 0
      )
    );
    $this->layout->add($this->getButtonsXML());
  }

  /**
  * buttons in menu bar
  *
  * @access public
  */
  function getButtonsXML() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $menubar = new base_btnbuilder;
    $menubar->images = &$this->images;
    if ($this->authUser->hasPerm(PapayaAdministrationPermissions::BOX_MANAGE)) {
      $menubar->addButton(
        'Add group',
        $this->getLink(array('cmd' => 'group_add', 'gid' => '0', 'bid' => '0')),
        'actions-folder-add',
        '',
        FALSE
      );
      if (isset($this->boxGroups) &&
          is_array($this->boxGroups)) {
        $menubar->addButton(
          'Delete group',
          $this->getLink(array('cmd'=>'group_delete', 'gid'=>(int)$this->params['gid'])),
          'actions-folder-delete',
          '',
          FALSE
        );
      }
      $menubar->addSeperator();

      if ((isset($this->boxGroups) && is_array($this->boxGroups)) ||
          (isset($this->box) && is_array($this->box))) {
        $menubar->addButton(
          'Add box',
          $this->getLink(
            array(
              'cmd' => 'box_add',
              'gid' => $this->params['gid'],
              'bid' => 0
           )
          ),
          'actions-box-add',
          '',
          FALSE
        );
        if (isset($this->box)) {
          $menubar->addButton(
            'Copy box',
            $this->getLink(
              array(
                'cmd' => 'box_add',
                'gid' => $this->box['boxgroup_id'],
                'bid' => $this->box['box_id'],
              )
            ),
            'actions-edit-copy',
            '',
            FALSE
          );
        }
      }
      if (isset($this->box) && is_array($this->box)) {
        $menubar->addButton(
          'Delete translation',
          $this->getLink(array('cmd'=>'translation_delete', 'bid'=>$this->box['box_id'])),
          'actions-phrase-delete',
          '',
          FALSE
        );
        $menubar->addButton(
          'Delete box',
          $this->getLink(array('cmd'=>'box_delete', 'bid'=>$this->box['box_id'])),
          'actions-box-delete',
          '',
          FALSE
        );
        $menubar->addSeparator();
        $menubar->addButton(
          'Publish box',
          $this->getLink(array('cmd'=>'publish', 'mode'=> 2, 'bid' => $this->box['box_id'])),
          'items-publication',
          '',
          FALSE
        );

        if (isset($this->box) &&
            isset($this->box['TRANSLATION']) &&
            is_array($this->box['TRANSLATION'])) {
          include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
          $toolbar = new base_btnbuilder;
          $toolbar->images = &$this->images;
          $toolbar->addButton(
            'Properties',
            $this->getLink(array('cmd' => 'chg_mode', 'mode' => 0, 'bid' => $this->box['box_id'])),
            'categories-properties',
            '',
            ($this->params['mode'] == 0)
          );
          $toolbar->addButton(
            'View',
            $this->getLink(array('cmd' => 'chg_mode', 'mode' => 5, 'bid' => $this->box['box_id'])),
            'items-view',
            'Select module and view',
            ($this->params['mode'] == 5)
          );
          $toolbar->addButton(
            'Content',
            $this->getLink(array('cmd' => 'chg_mode', 'mode' => 1, 'bid' => $this->box['box_id'])),
            'categories-content',
            '',
            ($this->params['mode'] == 1)
          );
          $toolbar->addButton(
            'Preview',
            $this->getLink(array('cmd' => 'chg_mode', 'mode' => 3, 'bid' => $this->box['box_id'])),
            'categories-preview',
            '',
            ($this->params['mode'] == 3)
          );
          $toolbar->addSeparator();
          $toolbar->addButton(
            'Versions',
            $this->getLink(array('cmd' => 'chg_mode', 'mode' => 2, 'bid' => $this->box['box_id'])),
            'items-time',
            'Version management',
            ($this->params['mode'] == 2)
          );
          if ($result = $toolbar->getXML()) {
            $this->layout->add('<toolbar>'.$result.'</toolbar>', 'toolbars');
          }
        }
      }
    }
    if ($result = $menubar->getXML()) {
      $this->layout->add('<menu>'.$result.'</menu>', 'menus');
    }
  }

  /**
  * Load list
  *
  * @access public
  * @return mixed boolean
  */
  function loadList($groupId = NULL) {
    if (!empty($groupId)) {
      $filter = " WHERE b.boxgroup_id = '".(int)$groupId."'";
      $this->boxGroupLinks[$groupId] = array();
    } else {
      $this->boxesList = array();
      $this->boxGroupLinks = array();
      $filter = '';
    }
    $sql = "SELECT b.box_id, b.box_name, b.boxgroup_id,
                    b.box_created, b.box_modified,
                    b.box_unpublished_languages,
                    bp.box_modified AS box_published,
                    bp.box_public_from, bp.box_public_to
              FROM %s b
              LEFT OUTER JOIN %s bp ON bp.box_id = b.box_id
              $filter
             ORDER BY b.box_name, b.box_id";
    $params = array($this->tableBox, $this->tableBoxPublic);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->boxesList[(int)$row['box_id']] = $row;
        $this->boxGroupLinks[$row['boxgroup_id']][] = (int)$row['box_id'];
      }
      return $this->loadGroupList();
    }
    return FALSE;
  }

  /**
  * Load group list
  *
  * @access public
  * @return boolean
  */
  function loadGroupList() {
    unset($this->boxGroupsList);
    $sql = "SELECT boxgroup_id, boxgroup_title, boxgroup_name
              FROM %s
             ORDER BY boxgroup_title ASC";
    $params = array($this->tableBoxgroup);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->boxGroupsList[$row['boxgroup_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load Box by id
  *
  * @param integer $boxId
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function load($boxId, $lngId = 0) {
    unset($this->box);
    if ($boxId > 0) {
      $sql = "SELECT b.box_id, b.box_name, b.boxgroup_id,
                     b.box_created, b.box_modified,
                     b.box_cachemode, b.box_cachetime,
                     b.box_unpublished_languages,
                     bp.box_modified AS box_published,
                     bp.box_public_from,
                     bp.box_public_to
                FROM %s b
                LEFT OUTER JOIN %s bp ON bp.box_id = b.box_id
               WHERE b.box_id = '%d'";
      $params = array($this->tableBox, $this->tableBoxPublic, $boxId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->box = $row;
          if (isset($lngId) && $lngId > 0) {
            $this->loadTranslation($this->box['box_id'], $lngId);
          }
          return TRUE;
        }
      }
    }
    return NULL;
  }

  /**
  * Search box record in database
  *
  * @param integer $lngId
  * @param string $searchFor
  * @param integer $searchOffset
  * @return array
  */
  function searchBox($lngId, $searchFor, $searchOffset = 0) {
    if (preg_match('(^\d+$)', $searchFor)) {
      $filter = $this->databaseGetSQLCondition('b.box_id', $searchFor);
    } else {
      $fields = array('b.box_name', 'bt.box_title');
      include_once(PAPAYA_INCLUDE_PATH.'system/base_searchstringparser.php');
      $parser = new searchStringParser();
      $filter = $parser->getSQL($searchFor, $fields, 0);
    }
    if ($filter) {
      $filter = str_replace('%', '%%', $filter);
      $sql = "SELECT b.box_id, b.boxgroup_id, b.box_name, bt.box_title
                FROM %s b
                LEFT OUTER JOIN %s bt ON (bt.box_id = b.box_id AND bt.lng_id = '%d')
               WHERE $filter
               ORDER BY b.box_name, bt.box_title";
      $params = array(
        $this->tableBox,
        $this->tableBoxTrans,
        (int)$lngId
      );
      if ($res = $this->databaseQueryFmt($sql, $params, 1, $searchOffset)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          return array(
            'count' => $res->absCount(),
            'offset' => $searchOffset + 1,
            'box_id' => $row['box_id'],
            'boxgroup_id' => $row['boxgroup_id']
          );
        } elseif ($res->absCount() > 0) {
          if ($res = $this->databaseQueryFmt($sql, $params, 1)) {
            if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
              return array(
                'count' => $res->absCount(),
                'offset' => 1,
                'box_id' => $row['box_id'],
                'boxgroup_id' => $row['boxgroup_id']
              );
            }
          }
        }
      }
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('Invalid search string.'));
    }
    return FALSE;
  }

  /**
  * Load translated data for a box
  *
  * @param integer $boxId
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function loadTranslation($boxId, $lngId) {
    unset($this->box['TRANSLATION']);
    $sql = "SELECT b.box_id, b.lng_id, b.view_id, b.box_title, b.box_data,
                   b.box_trans_created, b.box_trans_modified, b.view_id,
                   m.module_guid, m.module_path, m.module_file,
                   m.module_class, m.module_useoutputfilter
              FROM %s b
              LEFT OUTER JOIN %s v ON (v.view_id = b.view_id)
              LEFT OUTER JOIN %s m ON (m.module_guid = v.module_guid
                                      AND m.module_active = 1 AND m.module_type = 'box')
             WHERE b.box_id = '%d' AND lng_id = '%d'";
    $params = array($this->tableBoxTrans,
                    $this->tableViews,
                    $this->tableModules,
                    $boxId,
                    $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->box['TRANSLATION'] = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load informations about all translations of the selected box .
  * @return void
  */
  function loadTranslationsInfo() {
    unset($this->box['TRANSLATIONINFOS']);
    $sql = "SELECT bt.box_id, bt.lng_id, bt.box_trans_modified,
                   bt.box_title, bpt.box_trans_modified as box_trans_published,
                   v.view_title, v.view_is_cacheable
              FROM %s bt
              LEFT OUTER JOIN %s bpt ON (bpt.box_id = bt.box_id AND bpt.lng_id = bt.lng_id)
              LEFT OUTER JOIN %s v ON (v.view_id = bt.view_id)
             WHERE bt.box_id = %d";
    $params = array($this->tableBoxTrans,
                    $this->tableBoxPublicTrans,
                    $this->tableViews,
                    (int)$this->box['box_id']);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $this->box['UNPUBLISHED'] = 0;
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->box['TRANSLATIONINFOS'][$row['lng_id']] = $row;
        if ((!isset($row['box_trans_published'])) ||
            $row['box_trans_published'] < $row['box_trans_modified']) {
          $this->box['UNPUBLISHED']++;
        }
      }
      $this->saveUnpublishedLanguages($this->box['UNPUBLISHED']);
    }
  }

  /**
  * Save the current count of unpublished language version to the box record
  *
  * @param integer $unpublished
  * @access public
  * @return boolean
  */
  function saveUnpublishedLanguages($unpublished) {
    if ($this->box['box_unpublished_languages'] != $unpublished) {
      $data = array(
        'box_unpublished_languages' => $unpublished
      );
      $filter = array(
        'box_id' => (int)$this->box['box_id']
      );
      if (FALSE !== $this->databaseUpdateRecord($this->tableBox, $data, $filter)) {
        $this->box['box_unpublished_languages'] = $unpublished;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load group by id
  *
  * @param integer $id
  * @access public
  * @return mixed
  */
  function loadGroup($id) {
    unset($this->boxGroups);
    $this->boxGroups = NULL;
    if (isset($this->boxGroupsList) && is_array($this->boxGroupsList) &&
        isset($this->boxGroupsList[$id])) {
      $this->boxGroups = $this->boxGroupsList[$id];
    } elseif ($id > 0) {
      $sql = "SELECT boxgroup_id, boxgroup_title, boxgroup_name
                FROM %s
               WHERE boxgroup_id = '%s'";
      $params = array($this->tableBoxgroup, $id);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->boxGroups = $row;
        }
      }
    }
    return $this->boxGroups;
  }

  /**
  * Load versions list
  *
  * @param integer $id
  * @access public
  */
  function loadVersionsList($id) {
    unset($this->versions);
    $sql = "SELECT b.version_id, b.version_time, b.version_author_id, b.version_message,
                   b.box_name, u.user_id, u.givenname, u.surname
              FROM %s b
   LEFT OUTER JOIN %s u ON b.version_author_id = u.user_id
             WHERE b.box_id = '%s'
             ORDER BY b.version_time DESC, b.version_id DESC";
    $params = array($this->tableBoxVersions, $this->tableAuthUser, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['fullname'] = $row['givenname'].' '.$row['surname'];
        $this->versions[$row["version_id"]] = $row;
      }
    }
  }

  /**
  * Add a box
  *
  * @param integer $groupId
  * @param integer $lngId
  * @param integer $copyBoxId Copy data from current box
  * @access public
  * @return mixed
  */
  function create($groupId, $lngId, $copyBoxId = 0) {
    if ($this->loadGroup($groupId)) {
      $currentTime = time();
      if ($copyBoxId > 0 && $this->load($copyBoxId)) {
        $data = array(
          'box_name' => $this->_gt('Copy of ').' '.$this->box['box_name'],
          'boxgroup_id' => $groupId,
          'box_modified' => $currentTime,
          'box_created' => $currentTime,
          'box_cachemode' => $this->box['box_cachemode'],
          'box_cachetime' => $this->box['box_cachetime']
        );
      } else {
        $data = array(
          'box_name' => $this->_gt('New box'),
          'boxgroup_id' => $groupId,
          'box_modified' => $currentTime,
          'box_created' => $currentTime,
          'box_cachemode' => 1,
          'box_cachetime' => 0
        );
      }
      if ($newId = $this->databaseInsertRecord($this->tableBox, 'box_id', $data)) {
        if ($copyBoxId > 0) {
          $sql = "INSERT INTO %s
                         (box_id, lng_id,
                          box_trans_created, box_trans_modified,
                          box_title, box_data, view_id)
                  SELECT '%d', lng_id,
                         '%d', '%d',
                         box_title, box_data, view_id
                    FROM %s
                   WHERE box_id = '%d'";
          $params = array(
            $this->tableBoxTrans,
            $newId,
            $currentTime,
            $currentTime,
            $this->tableBoxTrans,
            $copyBoxId
          );
          $this->databaseQueryFmtWrite($sql, $params);
        } else {
          $data = array(
            'box_id' => (int)$newId,
            'lng_id' => (int)$lngId,
            'box_trans_created' => $currentTime,
            'box_trans_modified' => $currentTime,
            'box_data' => ''
          );
          $this->databaseInsertRecord($this->tableBoxTrans, NULL, $data);
        }
        return $newId;
      }
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('Please select a group!'));
    }
    return FALSE;
  }

  /**
  * Create translation of a box
  *
  * @param intern $lngId
  * @access public
  * @return boolean
  */
  function createTranslation($lngId) {
    $currentTime = time();
    if (isset($this->box) && isset($this->box['TRANSLATION'])) {
      $data = array(
        'box_id' => (int)$this->box['box_id'],
        'lng_id' => (int)$lngId,
        'box_trans_created' => time(),
        'box_trans_modified' => time(),
        'box_title' => (string)$this->box['TRANSLATION']['box_title'],
        'box_data' => (string)$this->box['TRANSLATION']['box_data'],
        'view_id' => (string)$this->box['TRANSLATION']['view_id']
      );
    } else {
      $data = array(
        'box_id' => (int)$this->box['box_id'],
        'lng_id' => (int)$lngId,
        'box_trans_created' => time(),
        'box_trans_modified' => time(),
        'box_data' => ''
      );
    }
    return (FALSE !== $this->databaseInsertRecord($this->tableBoxTrans, NULL, $data));
  }

  /**
  * Delete selected box translation
  * @access public
  * @return boolean
  */
  function deleteTranslation($boxId, $lngId) {
    return FALSE !== $this->databaseDeleteRecord(
      $this->tableBoxTrans,
      array(
        'box_id' => (int)$boxId,
        'lng_id' => (int)$lngId
      )
    );
  }

  /**
  * Add a group
  *
  * @access public
  * @return mixed
  */
  function createGroup($values) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE boxgroup_name = '%s'";
    $params = array(
      $this->tableBoxgroup,
      $values['boxgroup_name']
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($res->fetchField() == 0) {
        $data = array(
          'boxgroup_title' => $values['boxgroup_title'],
          'boxgroup_name' => $values['boxgroup_name']
        );
        return $this->databaseInsertRecord($this->tableBoxgroup, 'boxgroup_id', $data);
      }
    }
    return FALSE;
  }

  /**
  * Save changes of a box
  *
  * @access public
  * @return mixed
  */
  function save() {
    if (isset($this->box)) {
      $translationModified = FALSE;
      $result = TRUE;
      if (isset($this->box['TRANSLATION'])) {
        $dataTrans = array(
          'box_title' => $this->params['box_title']
        );
        if ($this->checkDataModified($dataTrans, $this->box['TRANSLATION'])) {
          $translationModified = TRUE;
          $dataTrans['box_trans_modified'] = time();
          $filter = array('box_id'=>(int)$this->box['box_id'],
            'lng_id'=>$this->box['TRANSLATION']['lng_id']);
          $result = (
            FALSE !== $this->databaseUpdateRecord($this->tableBoxTrans, $dataTrans, $filter)
          );
        }
      }

      if ($result) {
        $data = array(
          'box_name' => $this->params['box_name'],
          'boxgroup_id' => (int)$this->params['boxgroup_id'],
          'box_cachemode' => (int)$this->params['box_cachemode'],
          'box_cachetime' => (int)$this->params['box_cachetime'],
        );
        if ($translationModified || $this->checkDataModified($data, $this->box)) {
          $data['box_modified'] = time();
          $result = (
            FALSE !== $this->databaseUpdateRecord(
              $this->tableBox, $data, 'box_id', (int)$this->box['box_id']
            )
          );
          if ($result) {
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_PAGES,
              sprintf(
                'Properties of box "%s" #%d changed.',
                papaya_strings::escapeHTMLChars($this->params['box_name']),
                (int)$this->box['box_id']
              )
            );
          }
        }
        return $result;
      }
    }
    return FALSE;
  }

  /**
  * Create version of box
  *
  * @access public
  * @return boolean
  */
  function createVersion() {
    $currentTime = time();
    $data = array(
      'version_time' => $currentTime,
      'version_author_id' => $this->authUser->userId,
      'version_message' => $this->params['commit_message'],
      'box_id' => $this->box['box_id'],
      'box_name' => $this->box['box_name'],
      'boxgroup_id' => $this->box['boxgroup_id'],
      'box_modified' => $this->box['box_modified'],
    );
    if ($newVersionId = $this->databaseInsertRecord($this->tableBoxVersions, 'version_id', $data)) {
      $sql = "INSERT INTO %s (version_id, lng_id, box_id, box_title, box_data, view_id)
              SELECT '%d', tt.lng_id, tt.box_id, tt.box_title, tt.box_data, tt.view_id
                FROM %s tt
               WHERE tt.box_id = %d";
      $params = array(
        $this->tableBoxVersionsTrans,
        $newVersionId,
        $this->tableBoxTrans,
        $this->box['box_id']
      );
      if (FALSE !== $this->databaseQueryFmtWrite($sql, $params)) {
        $this->removeOldVersions();
        return TRUE;
      }
    } else {
      return FALSE;
    }
  }

  /**
  * Load old version
  *
  * @param mixed $lngId optional, default value NULL else integer value
  * @access public
  * @return boolean
  */
  function loadOldVersion($lngId = NULL) {
    unset($this->oldVersion);
    $lngParams = ($lngId !== NULL) ? "AND t.lng_id = ".(int)$lngId : $lngParams = '';
    $sql = "SELECT v.version_id, v.version_time,
                   v.version_message, v.version_author_id,
                   v.box_id, v.box_name, v.boxgroup_id,
                   u.user_id, u.givenname, u.surname,
                   t.box_title, t.box_data, t.view_id
              FROM %s v
              LEFT OUTER JOIN %s t ON t.version_id = v.version_id
              LEFT OUTER JOIN %s u ON v.version_author_id = u.user_id
             WHERE v.version_id = '%d' $lngParams";
    $params = array(
      $this->tableBoxVersions,
      $this->tableBoxVersionsTrans,
      $this->tableAuthUser,
      empty($this->params['version_id']) ? 0 : (int)$this->params['version_id']
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['fullname'] = $row['givenname'].' '.$row['surname'];
        $this->oldVersion = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Save group
  *
  * @access public
  * @param $groupId
  * @param array $values array('boxgroup_title' => , 'boxgroup_name' => )
  * @return boolean success
  */
  function saveGroup($groupId, $values) {
    if (!isset($values['boxgroup_title']) || !isset($values['boxgroup_name'])) {
      return FALSE;
    }
    $data = array(
      'boxgroup_title' => $values['boxgroup_title'],
      'boxgroup_name' => $values['boxgroup_name']
    );
    if (FALSE !== $this->databaseUpdateRecord(
          $this->tableBoxgroup, $data, 'boxgroup_id', $groupId)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Save data
  *
  * @access public
  * @return boolean
  */
  function saveData($dataString) {
    if (isset($this->box['TRANSLATION']) && is_array($this->box['TRANSLATION'])) {
      $dataTrans = array('box_data' => $dataString);
      if ($this->checkDataModified($dataTrans, $this->topic['TRANSLATION'])) {
        $dataTrans['box_trans_modified'] = time();
        $filter = array(
          'box_id' => (int)$this->box['box_id'],
          'lng_id' => $this->box['TRANSLATION']['lng_id']
        );
        if (FALSE !== $this->databaseUpdateRecord($this->tableBoxTrans, $dataTrans, $filter)) {
          $data = array('box_modified' => $dataTrans['box_trans_modified']);
          $updated = $this->databaseUpdateRecord(
            $this->tableBox, $data, 'box_id', (int)$this->box['box_id']
          );
          if (FALSE !== $updated) {
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_PAGES,
              sprintf(
                'Content of box "%s (%s)" changed.',
                papaya_strings::escapeHTMLChars($this->box['box_name']),
                $this->box['box_id']
              )
            );
            return TRUE;
          }
        }
      }
      return FALSE;
    } else {
      return FALSE;
    }
  }

  /**
  * Boxes publish function
  *
  * @access public
  * @return boolean
  */
  function publishBox() {
    if (isset($this->box) && is_array($this->box)) {
      if ($this->createVersion()) {
        $publicFrom = PapayaUtilDate::stringToTimestamp($this->params['box_public_from']);
        if ($publicFrom <= time()) {
          $publicFrom = 0;
        }
        $publicTo = PapayaUtilDate::stringToTimestamp($this->params['box_public_to']);
        if ($publicTo <= $publicFrom) {
          $publicTo = 0;
        }
        $data = array(
          'box_name' => $this->box['box_name'],
          'boxgroup_id' => $this->box['boxgroup_id'],
          'box_modified' => time(),
          'box_cachemode' => $this->box['box_cachemode'],
          'box_cachetime' => $this->box['box_cachetime'],
          'box_public_from' => $publicFrom,
          'box_public_to' => $publicTo,
        );
        $sql = "SELECT COUNT(*) FROM %s WHERE box_id = %d";
        $params = array($this->tableBoxPublic, $this->box['box_id']);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          if ($res->fetchField() > 0) {
            $result = (
              FALSE !== $this->databaseUpdateRecord(
                $this->tableBoxPublic, $data, 'box_id', $this->box['box_id']
              )
            );
          } else {
            $data['box_id'] = $this->box['box_id'];
            $result = (
              FALSE !== $this->databaseInsertRecord($this->tableBoxPublic, NULL, $data)
            );
          }
          if ($result) {
            $languages = array();
            if (isset($this->params['public_languages']) &&
                is_array($this->params['public_languages']) &&
                count($this->params['public_languages']) > 0) {
              foreach ($this->params['public_languages'] as $lng) {
                $languages[] = (int)$lng;
              }
              $params = array(
                'box_id' => $this->box['box_id'],
                'lng_id' => $languages
              );
              if (FALSE !== $this->databaseDeleteRecord($this->tableBoxPublicTrans, $params)) {
                $filter = $this->databaseGetSQLCondition('bt.lng_id', $languages);
                if (!empty($filter)) {
                  $filter = ' AND '.$filter;
                }
                $sql = "INSERT INTO %s (box_id, lng_id, box_title,
                               box_data, view_id,
                               box_trans_created, box_trans_modified)
                        SELECT bt.box_id, bt.lng_id, bt.box_title, bt.box_data,
                               bt.view_id, bt.box_trans_created, '%d'
                          FROM %s bt
                         WHERE bt.box_id = %d $filter";
                $params = array(
                  $this->tableBoxPublicTrans,
                  time(),
                  $this->tableBoxTrans,
                  $this->box['box_id']
                );
                if (FALSE !== $this->databaseQueryFmtWrite($sql, $params)) {
                  $this->deleteCache($this->box['box_id']);
                  return TRUE;
                }
              }
              return TRUE;
            }
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Delete box output cache
  *
  * @param $boxId
  * @access public
  */
  function deleteCache($boxId) {
    $cache = PapayaCache::getService($this->papaya()->options);
    return $cache->delete('boxes', $boxId);
  }

  /**
  * Get publish date
  *
  * @access public
  * @return mixed boolean od modified
  */
  function getPublicDate() {
    $sql = "SELECT box_modified
              FROM %s
             WHERE box_id = '%d'";
    $params = array(
      $this->tableBoxPublic,
      $this->box['box_id']
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return $row[0];
      }
    }
    return FALSE;
  }

  /**
  * Delete public box
  *
  * @access public
  * @return boolean
  */
  function deletePublicBox() {
    return (
      FALSE !== $this->databaseDeleteRecord(
        $this->tableBoxPublicTrans, 'box_id', $this->box['box_id']
      ) &&
      FALSE !== $this->databaseDeleteRecord(
        $this->tableBoxPublic, 'box_id', $this->box['box_id']
      )
    );
  }

  /**
  * Delete public trans box
  *
  * @access public
  * @return boolean
  */
  function deletePublicBoxTrans() {
    $condition = array('box_id' => $this->box['box_id'], 'lng_id' => $this->params['lng_id']);
    return ($this->databaseDeleteRecord($this->tableBoxPublicTrans, $condition) !== FALSE);
  }

  /**
  * Group input check function
  *
  * @access public
  * @return mixed boolean or integer
  */
  function checkGroup() {
    if ($this->params['boxgroup_name'] != $this->boxGroups['boxgroup_name']) {
      $sql = "SELECT boxgroup_id
                FROM %s
               WHERE boxgroup_name = '%s'";
      $params = array($this->tableBoxgroup, $this->params['boxgroup_name']);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if (($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) &&
            $row['boxgroup_id'] != $this->params['gid']) {
          $this->addMsg(MSG_ERROR, $this->_gt('A group with this name does already exists.'));
          $result = FALSE;
        }
      }
    }
    return TRUE;
  }

  /**
  * Delete box via given id
  *
  * @param integer $id
  * @access public
  * @return mixed form or ''
  */
  function deleteBox($id) {
    $result = '';
    if ($id > 0) {
      if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
        $break = FALSE;
        if (FALSE !== $this->databaseDeleteRecord($this->tableBoxVersionsTrans, 'box_id', $id) &&
            FALSE !== $this->databaseDeleteRecord($this->tableBoxVersions, 'box_id', $id)) {
          $this->addMsg(MSG_INFO, $this->_gt('Versions deleted.'));
        } else {
          $break = TRUE;
        }
        if (!$break) {
          if (FALSE !== $this->databaseDeleteRecord($this->tableBoxPublicTrans, 'box_id', $id) &&
              FALSE !== $this->databaseDeleteRecord($this->tableBoxPublic, 'box_id', $id)) {
            $this->addMsg(MSG_INFO, $this->_gt('Published box deleted.'));
          } else {
            $break = TRUE;
          }
        }
        if (!$break) {
          if (FALSE !== $this->databaseDeleteRecord($this->tableLink, 'box_id', $id)) {
            if (FALSE !== $this->databaseDeleteRecord($this->tableBoxTrans, 'box_id', $id) &&
                FALSE !== $this->databaseDeleteRecord($this->tableBox, 'box_id', $id)) {
              $this->addMsg(MSG_INFO, $this->_gt('Box and links deleted.'));
            } else {
              $this->addMsg(
                MSG_WARNING,
                $this->_gt('Database error!').' '.
                  $this->_gt('Could not delete box. - Links deleted only.')
              );
            }
          } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
        }
      } else {
        if ($this->load($id, $this->lngSelect->currentLanguageId)) {
          $result = $this->deleteBoxForm();
        }
      }
      $this->loadList('');
    }
    return $result;
  }

  /**
  * Delete group via given id
  *
  * @param integer $id
  * @param string $cmd optional, default value 'group_delete'
  * @access public
  * @return string form or ''
  */
  function deleteGroup($id) {
    if ($id > 0) {
      $sql = "SELECT COUNT(box_id) FROM %s WHERE boxgroup_id = '%d'";
      $params = array( $this->tableBox, $id);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          if ($row[0] == 0) {
            if (isset($this->params['confirm_delete']) &&
                $this->params['confirm_delete']) {
              if (FALSE !== $this->databaseDeleteRecord($this->tableBoxgroup, 'boxgroup_id', $id)) {
                $this->addMsg(MSG_INFO, $this->_gt('Group deleted.'));
                unset($this->boxGroups);
                $this->params['gid'] = 0;
                $this->initializeSessionParam('gid', array('bid'));
                $this->loadGroupList('');
              }
            } else {
              $this->loadGroup($this->params['gid']);
              return $this->deleteGroupForm();
            }
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Group is not empty. Please delete all boxes in this group first.')
            );
          }
        }
      }
    }
    return '';
  }

  /**
  * Initialize dialog with properties for box
  *
  * @access public
  */
  function initializePropertiesDialog() {
    if (!(isset($this->dialogProperties) && is_object($this->dialogProperties))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');

      if (isset($this->box) && is_array($this->box)) {
        $data = array(
          'box_name' => $this->box['box_name'],
          'boxgroup_id' => $this->box['boxgroup_id'],
          'box_cachemode' => $this->box['box_cachemode'],
          'box_cachetime' => $this->box['box_cachetime']
        );
        if (isset($this->box['TRANSLATION']) && is_array($this->box['TRANSLATION'])) {
          $data['box_title'] = $this->box['TRANSLATION']['box_title'];
          $data['view_id'] = $this->box['TRANSLATION']['view_id'];
        }
      } else {
        $data = array();
      }
      $hidden = array(
        'cmd' => 'box_edit',
        'bid' => (int)$this->box['box_id']
      );

      $boxGroups = array();
      if (isset($this->boxGroupsList) && is_array($this->boxGroupsList)) {
        foreach ($this->boxGroupsList as $key=>$val) {
          $boxGroups[$key] = $val['boxgroup_title'];
        }
      }

      $fields = array();
      if (isset($this->box['TRANSLATION']) && is_array($this->box['TRANSLATION'])) {
        $fields['box_title'] = array('Title', 'isNoHTML', FALSE, 'input', 100);
      }
      $fields[] = 'Language independent';
      $fields['box_name'] = array('Name', 'isNoHTML', TRUE, 'input', 100);
      $fields['boxgroup_id'] = array('Group', 'isNum', TRUE, 'combo', $boxGroups);

      $cacheModes = array(
        0 => $this->_gt('No Cache'),
        1 => $this->_gt('System cache time'),
        2 => $this->_gt('Own cache time'),
      );
      $fields[] = 'Caching';
      $fields['box_cachemode'] = array('Mode', 'isNum', TRUE, 'combo', $cacheModes);
      $fields['box_cachetime'] = array('Time (seconds)', 'isNum', TRUE, 'input', 10, '', 0);

      $this->dialogProperties = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogProperties->msgs = &$this->msgs;
      $this->dialogProperties->loadParams();
      $this->dialogProperties->dialogTitle =
        $this->lngSelect->getCurrentLanguageTitle().$this->_gt('Properties');
      $this->dialogProperties->dialogIcon = $this->lngSelect->getCurrentLanguageIcon();
      $this->dialogProperties->dialogDoubleButtons = FALSE;
      $this->dialogProperties->textYes = 'Yes';
      $this->dialogProperties->textNo = 'No';
    }
  }

  /**
  * Edit - copy box parameters in $this->params
  *
  * @access public
  * @return string edit form
  */
  function getPropertiesDialog() {
    $this->initializePropertiesDialog();
    $result = $this->dialogProperties->getDialogXML();
    $result .= $this->getTopicsListForBox($this->box['box_id']);
    return $result;
  }

  /**
  * Get pages the selected box is linked to
  * @param integer $boxId
  * @return string
  */
  function getTopicsListForBox($boxId) {
    $result = '';
    $condition = $this->databaseGetSQLCondition('box_id', $boxId);

    $sql = "SELECT bl.topic_id, tt.topic_title
              FROM %s bl
              LEFT OUTER JOIN %s tt ON (tt.topic_id = bl.topic_id)
              WHERE $condition
                AND lng_id = '%d'
            ";
    $params = array($this->tableLink, $this->tableTopicsTrans,
      $this->lngSelect->currentLanguageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $topicIds[$row['topic_id']] = $row['topic_title'];
      }
    }
    if (isset($topicIds) && is_array($topicIds) && count($topicIds) > 0) {
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('This box is used by the following topics'))
      );
      $result .= '<items>'.LF;
      foreach ($topicIds as $topicId => $title) {
        $result .= sprintf(
          '<listitem href="%s" image="%s" title="%s (#%d)"/>'.LF,
          $this->getLink(NULL, NULL, 'topic.php', $topicId),
          papaya_strings::escapeHTMLChars($this->images['items-page']),
          papaya_strings::escapeHTMLChars($title),
          (int)$topicId
        );
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * Initialize add translation dialog
  * @return void
  */
  function initializeAddTranslationDialog() {
    if (!(isset($this->dialogAddTranslation) && is_object($this->dialogAddTranslation))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $this->loadTranslationsInfo();
      $hidden = array(
        'cmd' => 'add_translation',
        'bid' => $this->box['box_id'],
        'lng_id' => $this->lngSelect->currentLanguageId
      );
      $data = array();
      if (isset($this->box['TRANSLATIONINFOS']) && is_array($this->box['TRANSLATIONINFOS'])) {
        foreach ($this->box['TRANSLATIONINFOS'] as $translation) {
          if ($translation['lng_id'] != $this->lngSelect->currentLanguageId) {
            $data['copy_lng_id'] = $translation['lng_id'];
            break;
          }
        }
      }
      $translations = array(
        0 => $this->_gt('None')
      );
      if (isset($this->box['TRANSLATIONINFOS']) && is_array($this->box['TRANSLATIONINFOS'])) {
        foreach ($this->box['TRANSLATIONINFOS'] as $translation) {
          if (!empty($translation['box_title'])) {
            $translations[$translation['lng_id']] =
              $this->lngSelect->languages[$translation['lng_id']]['lng_title'].' - '.
              $translation['box_title'];
          } else {
            $translations[$translation['lng_id']] =
              $this->lngSelect->languages[$translation['lng_id']]['lng_title'];
          }
        }
      }
      $fields = array(
        'copy_lng_id' => array('Copy translation', 'isNum', FALSE, 'combo', $translations)
      );

      $this->dialogAddTranslation = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogAddTranslation->dialogTitle = sprintf(
        $this->_gt('Add content for language "%s" (%s) to box "%s"?'),
        papaya_strings::escapeHTMLChars($this->lngSelect->currentLanguage['lng_title']),
        papaya_strings::escapeHTMLChars($this->lngSelect->currentLanguage['lng_short']),
        (int)$this->box['box_id']
      );
      $this->dialogAddTranslation->buttonTitle = 'Add';
      $this->dialogAddTranslation->msgs = &$this->msgs;
      $this->dialogAddTranslation->loadParams();
    }
  }

  /**
  * Get xml for add translation dialog
  * @return string
  */
  function addTranslationDialog() {
    $this->initializeAddTranslationDialog();
    return $this->dialogAddTranslation->getDialogXML();
  }

  /**
  * Get xml for delete translation confirmation dialog.
  * @return string
  */
  function getDeleteTranslationDialog() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'translation_delete',
      'bid' => $this->box['box_id'],
      'lng_id' => $this->lngSelect->currentLanguageId,
      'confirm_delete' => 1
    );
    $msg = sprintf(
      $this->_gt('Delete translation?')
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Initialize group add/edit dialog
  * @return void
  */
  function initializeGroupDialog() {
    if (!(isset($this->dialogGroup) && is_object($this->dialogGroup))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      if (isset($this->boxGroups)) {
        $data = $this->boxGroups;
        $hidden = array(
          'cmd' => 'group_edit',
          'gid' => $data['boxgroup_id'],
          'confirm_edit' => 1
        );
        $buttonCaption = 'Save';
      } else {
        $data = array();
        $hidden = array(
          'cmd' => 'group_add',
          'gid' => 0,
          'confirm_add' => 1
        );
        $buttonCaption = 'Add';
      }
      $fields = array(
        'boxgroup_title' => array('Title', 'isNoHTML', TRUE, 'input', 100, ''),
        'boxgroup_name' => array('Name', 'isAlphaChar', TRUE, 'input', 100, '')
      );
      $this->dialogGroup = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->dialogGroup->msgs = &$this->msgs;
      $this->dialogGroup->loadParams();
      $this->dialogGroup->dialogTitle = $this->_gt('Group');
      $this->dialogGroup->buttonTitle = $buttonCaption;
      $this->dialogGroup->dialogDoubleButtons = FALSE;
    }
  }

  /**
  * Get xml for edit/add group dialog
  *
  * @access public
  * @return string edit form
  */
  function getGroupDialogXML() {
    $this->initializeGroupDialog();
    return $this->dialogGroup->getDialogXML();
  }

  /**
  * box form for delete confirmation
  *
  * @access public
  * @return mixed dialog
  */
  function deleteBoxForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'box_delete',
      'bid' => $this->box['box_id'],
      'confirm_delete' => 1
    );
    $msg = sprintf(
      $this->_gt('Delete box "%s" (%s)?'),
      $this->box['box_name'],
      $this->box['box_id']
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * group form for delete confirmation
  *
  * @access public
  * @return mixed dialog
  */
  function deleteGroupForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'group_delete',
      'gid' => $this->boxGroups['boxgroup_id'],
      'confirm_delete' => 1
    );
    $msg = sprintf(
      $this->_gt('Delete group "%s" (%s)?'),
      $this->boxGroups['boxgroup_title'],
      $this->boxGroups['boxgroup_id']
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Edit Content
  *
  * @param integer $userId
  * @access public
  * @return mixed form
  */
  function editContent() {
    $result = '';
    if (isset($this->box['TRANSLATION']) && $this->box['TRANSLATION']['module_class'] != '') {
      $moduleData = $this->box['TRANSLATION'];
      $plugin = $this->papaya()->plugins->get($moduleData['module_guid'], $this);
      if ($plugin instanceOf PapayaPluginEditable) {
        $plugin->content()->setXml($moduleData['box_data']);
        if ($plugin->content()->editor()) {
          $plugin->content()->editor()->context()->merge(
            array(
              $this->paramName => array(
                'mode' => 1,
                'gid' => $this->params['gid'],
                'bid' => $this->params['bid']
              )
            )
          );
          $result = $plugin->content()->editor()->getXml();
          if ($plugin->content()->modified()) {
            if ($this->saveData($plugin->content()->getXml())) {
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
              $this->loadList();
            }
          }
        }
      } elseif ($plugin instanceOf base_actionbox) {
        $plugin->setData($moduleData['box_data']);
        $plugin->msgs = &$this->msgs;
        $plugin->images = &$this->images;
        $plugin->authUser = &$this->authUser;
        $plugin->initialize();
        $plugin->execute();
        $plugin->initializeDialog();
        if ($plugin->modified()) {
          if ($plugin->checkData()) {
            if ($this->saveData($plugin->getData())) {
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
              $this->loadList();
            }
          }
        }
        $result = $plugin->getForm(
          $this->lngSelect->getCurrentLanguageTitle(),
          $this->lngSelect->getCurrentLanguageIcon()
        );
      }
    }
    return $result;
  }

  /**
  * Get xml for box search dialog
  *
  * @return string
  */
  function getXMLSearchBox() {
    $result = '';
    if (isset($this->sessionParams['panel_state']['search'])
        && $this->sessionParams['panel_state']['search'] == 'open') {
      $resize = sprintf(
        ' minimize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'close_panel',
              'panel' => 'search',
            )
          )
        )
      );
      $result .= sprintf(
        '<dialog action="%s" method="post" title="%s" %s>'.LF,
        papaya_strings::escapeHTMLChars($this->getBaseLink()),
        papaya_strings::escapeHTMLChars($this->_gt('Search')),
        $resize
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="search" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      if (isset($this->searchResult['count']) &&
          $this->searchResult['count'] > 0 &&
          isset($this->searchResult['offset']) &&
          $this->searchResult['offset'] > 0) {
        $result .= sprintf(
          '<input type="hidden" name="%s[search_offset]" value="%d" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          (int)$this->searchResult['offset']
        );
      }
      $result .= '<lines class="dialogXSmall">'.LF;
      if (!empty($this->params['search_string'])) {
        $searchString = $this->params['search_string'];
      } else {
        $searchString = '';
      }
      $result .= '<line>'.LF;
      $result .= sprintf(
        '<input type="text" name="%s[search_string]" value="%s" class="dialogInput dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($searchString)
      );
      $result .= '</line>'.LF;
      $result .= '</lines>'.LF;
      if (isset($this->searchResult['count']) &&
          $this->searchResult['count'] > 0 &&
          isset($this->searchResult['offset']) &&
          $this->searchResult['offset'] > 0) {
        $result .= sprintf(
          '<dlgbutton name="%s[search_next]" value="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($this->_gt('Next'))
        );
        $result .= sprintf(
          '<dlgbutton value="%s" align="left"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Search new'))
        );
      } else {
        $result .= sprintf(
          '<dlgbutton value="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Search'))
        );
      }
      $result .= '</dialog>'.LF;
    } else {
      $resize = sprintf(
        ' maximize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'open_panel',
              'panel' => 'search',
            )
          )
        )
      );
      $result .= sprintf(
        '<listview title="%s" %s />'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Search')),
        $resize
      );
    }
    return $result;
  }

  /**
  * load possible groups from template information file
  *
  * @access public
  * @return void
  */
  function loadTemplateGroupsList() {
    $this->templateGroups = NULL;
    $templateHandler = new PapayaTemplateXsltHandler();
    $fileName = $templateHandler->getLocalPath().'/info.xml';
    if (file_exists($fileName) && is_readable($fileName)) {
      $this->templateGroups = array();
      include_once(PAPAYA_INCLUDE_PATH.'system/sys_simple_xmltree.php');
      $xmlTree = simple_xmltree::createFromXML(file_get_contents($fileName), $this);
      for ($idx1 = 0; $idx1 < $xmlTree->documentElement->childNodes->length; $idx1++) {
        $node = $xmlTree->documentElement->childNodes->item($idx1);
        if ($node->nodeType == XML_ELEMENT_NODE &&
          $node->nodeName == 'boxes') {
          for ($idx2 = 0; $idx2 < $node->childNodes->length; $idx2++) {
            $groupsNode = $node->childNodes->item($idx2);
            if ($groupsNode->nodeType == XML_ELEMENT_NODE &&
              $groupsNode->nodeName == 'groups') {
              for ($idx3 = 0; $idx3 < $groupsNode->childNodes->length; $idx3++) {
                $groupNode = $groupsNode->childNodes->item($idx3);
                if ($groupNode->nodeType == XML_ELEMENT_NODE &&
                    $groupNode->nodeName == 'group') {
                   $name = '';
                  if ($groupNode->hasAttribute('name')) {
                    $name = trim($groupNode->getAttribute('name'));
                  }
                  if (!empty($name)) {
                    $this->templateGroups[$name] = TRUE;
                  }
                }
              }
              break;
            }
          }
          break;
        }
      }
      ksort($this->templateGroups);
    }
  }

  /**
  * Get list XML-source
  *
  * @param $aBoxId
  * @param $aGroupId
  * @access public
  * @return string XML-source
  */
  function getXMLList($aBoxId, $aGroupId) {
    if ( !(isset($this->boxGroupsList) && is_array($this->boxGroupsList)) &&
         !(isset($this->templateGroups) && is_array($this->templateGroups)) ) {
      return '';
    }
    $usedGroups = array();
    $result = sprintf(
      '<listview title="%s"><items>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Boxes'))
    );
    if ( (isset($this->boxGroupsList) && is_array($this->boxGroupsList)) ) {
      foreach ($this->boxGroupsList as $groupId => $group) {
        if (isset($this->boxGroupLinks[$groupId]) &&
              is_array($this->boxGroupLinks[$groupId])) {
          $boxIds = $this->boxGroupLinks[$groupId];
          $indent = 0;
          if (isset($this->opened[$groupId]) && $this->opened[$groupId]) {
            $nodeHref = $this->getLink(
              array(
                'cmd' => 'close',
                'gid' => $groupId,
                'bid' => 0
              )
            );
            $node = sprintf(
              ' node="open" nhref="%s"',
              papaya_strings::escapeHTMLChars($nodeHref)
            );
            $imgIdx = 'status-folder-open';
          } else {
            $nodeHref = $this->getLink(
              array(
                'cmd' => 'open',
                'gid' => $groupId,
                'bid' => 0
              )
            );
            $node = sprintf(
              ' node="close" nhref="%s"',
              papaya_strings::escapeHTMLChars($nodeHref)
            );
            $imgIdx = 'items-folder';
          }
        } else {
          $boxIds = NULL;
          $indent = 0;
          $node = ' node="empty"';
          $imgIdx = 'items-folder';
        }
        if ($groupId == $aGroupId) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem href="%s" title="%s" image="%s" indent="%s"  %s%s>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('gid' => $groupId, 'bid' => 0, 'cmd' => 'group_edit'))
          ),
          papaya_strings::escapeHTMLChars($group["boxgroup_title"]),
          papaya_strings::escapeHTMLChars($this->images[$imgIdx]),
          (int)$indent,
          $selected,
          $node
        );
        if (isset($this->templateGroups)) {
          if (!isset($this->templateGroups[$group["boxgroup_name"]])) {
            $result .= sprintf(
              '<subitem><glyph src="%s" hint="%s"/></subitem>',
              papaya_strings::escapeHTMLChars($this->images['status-sign-warning']),
              papaya_strings::escapeHTMLChars($this->_gt('Group is not defined in template set.'))
            );
          } else {
            $usedGroups[] = $group["boxgroup_name"];
            $result .= '<subitem/>';
          }
        } else {
          $result .= '<subitem/>';
        }
        $result .= '</listitem>';
        if (isset($boxIds) && is_array($boxIds) &&
            isset($this->opened[$groupId]) && ($this->opened[$groupId])) {
          foreach ($boxIds as $boxId) {
            $box = $this->boxesList[$boxId];
            if (isset($box) && is_array($box)) {
              if ($boxId == $aBoxId) {
                $selected = ' selected="selected"';
              } else {
                $selected = '';
              }
              if ($pubDate = $box['box_published']) {
                $pubDateStr = date('Y-m-d H:i:s', $pubDate);
                $now = time();
                if ($pubDate >= $box['box_modified']) {
                  if ($box['box_public_from'] < $now &&
                      (
                       $box['box_public_to'] == 0 ||
                       $box['box_public_to'] == $box['box_public_from'] ||
                       $box['box_public_to'] > $now
                      )
                     ) {
                    if ($box['box_unpublished_languages'] > 0) {
                      $imageIndex = 'status-box-published-partial';
                    } else {
                      $imageIndex = 'status-box-published';
                    }
                  } else {
                    $imageIndex = 'status-box-published-hidden';
                  }
                } elseif ($box['box_public_from'] < $now &&
                          (
                           $box['box_public_to'] == 0 ||
                           $box['box_public_to'] == $box['box_public_from'] ||
                           $box['box_public_to'] > $now
                          )) {
                  $imageIndex = 'status-box-modified';
                } else {
                  $imageIndex = 'status-box-modified-hidden';
                }
              } else {
                $pubDateStr = '';
                $imageIndex = 'status-box-created';
              }
              $result .= sprintf(
                '<listitem href="%s" title="%s" image="%s" indent="2" span="2" %s></listitem>',
                $this->getLink(array('gid' => $groupId, 'bid' => $boxId, 'cmd' => 'chg_show')),
                papaya_strings::escapeHTMLChars($box['box_name']),
                papaya_strings::escapeHTMLChars($this->images[$imageIndex]),
                $selected
              );
            }
          }
        }
      }
    }
    if (isset($this->templateGroups) && is_array($this->templateGroups)) {
      foreach ($this->templateGroups as $groupName => $groupData) {
        if (!in_array($groupName, $usedGroups)) {
          $result .= sprintf(
            '<listitem title="%s" image="%s" href="%s" node="empty" span="2" />',
            papaya_strings::escapeHTMLChars($groupName),
            papaya_strings::escapeHTMLChars($this->images['actions-folder-add']),
            $this->getLink(
              array(
                'cmd' => 'group_add',
                'gid' => '0',
                'bid' => '0',
                'boxgroup_name' => $groupName,
                'boxgroup_title' => $groupName
              )
            )
          );
        }
      }
    }
    $result .= "</items></listview>".LF;
    return $result;
  }

  /**
  * Get versions list XML-source
  *
  * @access public
  * @return string XML-source
  */
  function getVersionsList() {
    $result = '';
    if (isset($this->versions) && is_array($this->versions)) {

      $listview = new PapayaUiListview();
      $listview->caption = new PapayaUiStringTranslated('Versions');
      $listview->parameterGroup($this->paramName);

      $listview->columns[] = new PapayaUiListviewColumn(
        new PapayaUiStringTranslated('Version time')
      );
      $listview->columns[] = new PapayaUiListviewColumn(
        new PapayaUiStringTranslated('User')
      );
      $listview->columns[] = new PapayaUiListviewColumn(
        '', PapayaUiOptionAlign::CENTER
      );
      foreach ($this->versions as $id => $version) {
        $listitem = new PapayaUiListviewItem(
          'items-page',
          new PapayaUiStringDate($version['version_time']),
          array(
            'bid' => $this->params['bid'],
            'version_id' => $id
          ),
          (isset($this->params['version_id']) && $id == $this->params['version_id'])
        );
        $listitem->text = PapayaUtilString::truncate(
          $version['version_message'], 100, FALSE, "\xE2\x80\xA6"
        );
        $listitem->subitems[] = new PapayaUiListviewSubitemText($version['fullname']);
        $listitem->subitems[] = new PapayaUiListviewSubitemImage(
          'actions-recycle',
          new PapayaUiStringTranslated('Recycle'),
          array(
            'cmd' => 'restore_version',
            'bid' => $this->params['bid'],
            'version_id' => $id
          )
        );
        $listview->items[] = $listitem;
      }
      return $listview->getXml();
    }
    return $result;
  }

  /**
  * Get version Information XML-source
  *
  * @access public
  * @return string XML-source
  */
  function getVersionInfos() {
    if (isset($this->oldVersion) && is_array($this->oldVersion)) {
      $result = sprintf(
        '<listview title="Infos [%s]">',
        date('Y-m-d H:i:s', $this->oldVersion['version_time'])
      );
      $result .= '<items>';
      $result .= sprintf(
        '<listitem title="%s" indent="%s"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Name')),
        0,
        papaya_strings::escapeHTMLChars($this->oldVersion['box_name'])
      );
      $result .= sprintf(
        '<listitem title="%s" indent="%s"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Group')),
        0,
        papaya_strings::escapeHTMLChars(
          $this->boxGroupsList[$this->oldVersion['boxgroup_id']]['boxgroup_title']
        )
      );
      $result .= sprintf(
        '<listitem title="%s" indent="%s"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('User')),
        0,
        papaya_strings::escapeHTMLChars($this->oldVersion['fullname'])
      );
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addRight($result);
      $result = sprintf(
        '<panel title="%s"><sheet width="100%%" align="center"><text>'.
          '<div style="padding: 0px 5px 0px 5px; ">%s</div></text></sheet></panel>',
        papaya_strings::escapeHTMLChars($this->_gt('Message')),
        papaya_strings::escapeHTMLChars($this->oldVersion['version_message'])
      );
      $this->layout->add($result);
    }
  }

  /**
  * Execute version
  *
  * @access public
  * @return mixed
  */
  function versionExecute() {
    $result = '';
    if (isset($this->oldVersion) && is_array($this->oldVersion) && isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'version_delete':
        if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
          if ($this->deleteVersion()) {
            $this->addMsg(MSG_INFO, $this->_gt('Version deleted.'));
            unset($this->oldVersion);
          }
        } else {
          $result = $this->getVersionDeleteForm();
        }
        break;
      case 'restore_version':
        if ($this->loadOldVersion()) {
          if (isset($this->params['confirm_restore']) &&
              $this->params['confirm_restore']) {
            if (isset($this->params['restore_languages']) &&
                is_array($this->params['restore_languages']) &&
                count($this->params['restore_languages'] > 0)) {
              foreach ($this->params['restore_languages'] as $lngId) {
                $this->restoreVersionTrans($lngId);
              }
            }
            if (isset($this->params['meta_info']) &&
                $this->params['meta_info'] == 'Yes') {
              $this->restoreVersion();
            }
            $this->addMsg(MSG_INFO, $this->_gt('Version restored.'));
          } else {
            $result = $this->getVersionRestoreForm();
          }
        }
        break;
      }
    }
    return $result;
  }

  /**
  * Get version delete fromular
  *
  * @access public
  * @return string fromular
  */
  function getVersionDeleteForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'version_delete',
      'bid' => $this->box['box_id'],
      'version_id' => (int)$this->params['version_id'],
      'confirm_delete'=>1,
    );
    $msg = sprintf(
      $this->_gt('Delete version from "%s"?'),
      date('Y-m-d H:i:s', $this->oldVersion['version_time'])
    );
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Get version restore form
  *
  * @access public
  * @return string dialog
  */
  function getVersionRestoreForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'cmd' => 'restore_version',
      'bid' => $this->box['box_id'],
      'version_id' => (int)$this->params['version_id'],
      'confirm_restore'=>1,
    );

    $data = array();
    $msg = sprintf(
      $this->_gt('Restore version for "%s" from %s?'),
      papaya_strings::escapeHTMLChars($this->box['box_name']),
      date('Y-m-d H:i:s', $this->oldVersion['version_time'])
    );
    $fields = array(
      'meta_info' => array('', 'isSomeText', FALSE, 'function',
        'callbackLanguageIndependent', '', '', 'left'),
      'Languages',
      'restore_languages' => array('', 'isSomeText', FALSE, 'function',
        'callbackRestoreLanguages', '', '', 'left')
    );
    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $dialog->dialogTitle = $msg;
    $dialog->buttonTitle = 'Restore';
    $dialog->inputFieldSize = 'x-large';
    return $dialog->getDialogXML();
  }

  /**
  * Get checkbox control xml for language independent option
  *
  * @param string $name
  * @param array $element
  * @param string $data
  * @return string
  */
  function callbackLanguageIndependent($name, $element, $data) {
    $result = sprintf(
      '%s<input type="checkbox" name="%s[%s]" value="Yes"/>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Language independent information')),
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    return $result;
  }

  /**
  * Callback to restore languages
  *
  * @param string $name
  * @param mixed $element
  * @param array $data
  * @access public
  * @return string $result
  */
  function callbackRestoreLanguages($name, $element, $data) {
    $result = '';
    if (isset($this->lngSelect->languages) && is_array($this->lngSelect->languages)) {
      $sql = 'SELECT lng_id
                FROM %s
               WHERE box_id=%d
                 AND version_id=%d';
      $params = array(
        $this->tableBoxVersionsTrans,
        (int)$this->box['box_id'],
        $this->params['version_id']
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow()) {
          $lngId = $row[0];
          $languages[$lngId] = $this->lngSelect->languages[$lngId];
        }
      }
      foreach ($languages as $lngId=>$lng) {
        $result .= sprintf(
          '%s (%s)<input type="checkbox" name="%s[%s][]" value="%d" />'.LF,
          papaya_strings::escapeHTMLChars($languages[$lngId]['lng_title']),
          papaya_strings::escapeHTMLChars($this->lngSelect->languages[$lngId]['lng_short']),
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($lngId)
        );
      }
    }
    return $result;
  }

  /**
  * Delete version
  *
  * @access public
  * @return boolean
  */
  function deleteVersion() {
    return (
      FALSE !== $this->databaseDeleteRecord(
        $this->tableBoxVersions, 'version_id', $this->params['version_id']
      )
    );
  }

  /**
  * Restore last version of box
  *
  * @access public
  * @return boolean
  */
  function restoreVersion() {
    $data = array(
      'box_name' => $this->oldVersion['box_name'],
      'boxgroup_id' => $this->oldVersion['boxgroup_id'],
      'box_modified' => time()
    );
    $updated = $this->databaseUpdateRecord(
      $this->tableBox, $data, 'box_id', $this->box['box_id']
    );
    if ($updated !== FALSE) {
      return TRUE;
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
    }
    return FALSE;
  }

  /**
  * Restore last version of box translation
  *
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function restoreVersionTrans($lngId) {
    $this->loadOldVersion($lngId);
    $data = array(
      'view_id' => $this->oldVersion['view_id'],
      'box_title' => $this->oldVersion['box_title'],
      'box_data' => $this->oldVersion['box_data'],
      'box_trans_modified' => time()
    );
    $updated = $this->databaseUpdateRecord(
      $this->tableBoxTrans, $data, 'box_id', $this->box['box_id']
    );
    if ($updated !== FALSE) {
      return TRUE;
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
    }
    return FALSE;
  }

  /**
  * Remove old versions
  *
  * @access public
  * @return mixed
  */
  function removeOldVersions() {
    $sql = "SELECT version_time
              FROM %s
             WHERE box_id = %d
             ORDER BY version_time DESC";
    $counter = 0;
    $params = array($this->tableBoxVersions, $this->box['box_id']);
    $maxVersions = $this->papaya()->options->get(
      'PAPAYA_VERSIONS_MAXCOUNT', $this->maxVersions
    );
    if ($maxVersions > 0) {
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while (($row = $res->fetchRow()) && ($counter < $maxVersions)) {
          $border = $row[0];
          ++$counter;
        }
        if ($counter >= $maxVersions) {
          $sql = "DELETE
                    FROM %s
                   WHERE box_id = %d
                     AND version_time < %d";
          $params = array($this->tableBoxVersions, $this->box['box_id'], $border);
          return $this->databaseQueryFmtWrite($sql, $params);
        }
      }
      return FALSE;
    }
    return TRUE;
  }

  /**
  * publication execute
  *
  * @access public
  * @return string form or ''
  */
  function publishExecute() {
    $result = '';
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'publish' :
        if (isset($this->params['confirm_publish']) && $this->params['confirm_publish']) {
          if (isset($this->params['commit_message']) &&
              checkit::isSomeText($this->params['commit_message'], TRUE)) {
            if ($this->publishBox()) {
              if (!empty($this->params['commit_message'])) {
                $this->sessionParams['last_publish_message'] = $this->params['commit_message'];
              }
              $this->addMsg(MSG_INFO, $this->_gt('Box published.'));
              $this->logMsg(
                MSG_INFO,
                PAPAYA_LOGTYPE_PAGES,
                sprintf(
                  'Box "%s (%s)" published.',
                  $this->box['box_name'],
                  $this->box['box_id']
                )
              );
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Couldn\'t publish this box.'));
            }
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Please input a commit message.'));
            $result = $this->getPublishForm();
          }
        } else {
          $result = $this->getPublishForm();
        }
        break;
      case 'deletepublic' :
        if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
          if ($this->deletePublicBox()) {
            $this->addMsg(MSG_INFO, $this->_gt('Published box deleted.'));
          }
        } else {
          $result = $this->getDeletePublicForm();
        }
        break;
      case 'deletepublic_trans' :
        if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
          if ($this->deletePublicBoxTrans()) {
            $this->addMsg(MSG_INFO, $this->_gt('Published box translation deleted.'));
          }
        } else {
          $result = $this->getDeletePublicTransForm();
        }
        break;
      }
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    }
    return $result;
  }

  /**
  * Get public data
  *
  * @param boolean $sel
  * @access public
  * @return string XML
  */
  function getPublicData() {
    $result = sprintf(
      '<listview title="%s" width="100%%">',
      papaya_strings::escapeHTMLChars($this->_gt('Published'))
    );
    $result .= '<cols>';
    $result .= '<col/>';
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Modified'))
    );
    $result .= '<col/>';
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Published'))
    );
    $result .= '<col/>';
    $result .= '</cols>';
    $result .= '<items>';
    if ($pubDate = $this->getPublicDate()) {
      $pubDateStr = date('Y-m-d H:i:s', $pubDate);
    } else {
      $pubDateStr = '';
    }
    if (isset($this->box['box_published']) &&
        $this->box['box_published'] < $this->box['box_modified']) {
      $imageIndex = 'status-box-modified'; //published and modified
      $imageHint = 'Modified';
    } elseif (isset($this->box['box_published']) &&
              $this->box['box_published'] >= $this->box['box_modified']) {
      $imageIndex = 'status-box-published'; //published and up to date
      $imageHint = 'Published';
    } else {
      $imageIndex = 'status-box-created'; //created
      $imageHint = 'Created';
    }
    $result .= sprintf(
      '<listitem title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('General'))
    );
    $result .= sprintf(
      '<subitem align="center">%s</subitem>',
      date('Y-m-d H:i:s', $this->box['box_modified'])
    );
    $result .= sprintf(
      '<subitem align="center"><glyph src="%s" hint="%s"/></subitem>',
      papaya_strings::escapeHTMLChars($this->images[$imageIndex]),
      papaya_strings::escapeHTMLChars($this->_gt($imageHint))
    );
    $result .= sprintf(
      '<subitem align="center">%s</subitem>',
      papaya_strings::escapeHTMLChars($pubDateStr)
    );
    if ($pubDate) {
      $result .= sprintf(
        '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
        $this->getLink(array('cmd' => 'deletepublic')),
        papaya_strings::escapeHTMLChars($this->images['actions-publication-delete']),
        papaya_strings::escapeHTMLChars($this->_gt('Remove publication'))
      );
    } else {
      $result .= '<subitem/>';
    }
    $result .= '</listitem>';
    if (isset($this->lngSelect->languages) && is_array($this->lngSelect->languages)) {
      foreach ($this->lngSelect->languages as $lngId=>$lng) {
        if ($lng['lng_glyph'] != '' &&
            file_exists($this->getBasePath(TRUE).'pics/language/'.$lng['lng_glyph'])) {
          $image = sprintf(
            ' image="./pics/language/%s"',
            papaya_strings::escapeHTMLChars($lng['lng_glyph'])
          );
        } else {
          $image = '';
        }
        $result .= sprintf(
          '<listitem title="%s" indent="1"%s>'.LF,
          papaya_strings::escapeHTMLChars($lng['lng_title']),
          $image
        );
        if (isset($this->box['TRANSLATIONINFOS']) &&
            isset($this->box['TRANSLATIONINFOS'][$lngId])) {
          $result .= sprintf(
            '<subitem align="center">%s</subitem>'.LF,
            date('Y-m-d H:i:s', $this->box['TRANSLATIONINFOS'][$lngId]['box_trans_modified'])
          );
          if (isset($this->box['TRANSLATIONINFOS'][$lngId]['box_trans_published'])) {
            if ($this->box['TRANSLATIONINFOS'][$lngId]['box_trans_published'] >=
                $this->box['TRANSLATIONINFOS'][$lngId]['box_trans_modified']) {
              $result .= sprintf(
                '<subitem align="center"><glyph src="%s" hint="%s"/></subitem>',
                papaya_strings::escapeHTMLChars($this->images['status-box-published']),
                papaya_strings::escapeHTMLChars($this->_gt('Published'))
              );
            } else {
              $result .= sprintf(
                '<subitem align="center"><glyph src="%s" hint="%s"/></subitem>',
                papaya_strings::escapeHTMLChars($this->images['status-box-modified']),
                papaya_strings::escapeHTMLChars($this->_gt('Modified'))
              );
            }
            $result .= sprintf(
              '<subitem align="center">%s</subitem>'.LF,
              date(
                'Y-m-d H:i:s',
                $this->box['TRANSLATIONINFOS'][$lngId]['box_trans_published']
              )
            );
            $result .= sprintf(
              '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              $this->getLink(array('cmd' => 'deletepublic_trans', 'lng_id' => $lngId)),
              papaya_strings::escapeHTMLChars($this->images['actions-publication-delete']),
              papaya_strings::escapeHTMLChars($this->_gt('Remove publication'))
            );
          } else {
            $result .= sprintf(
              '<subitem align="center"><glyph src="%s" hint="%s"/></subitem>',
              papaya_strings::escapeHTMLChars($this->images['status-box-created']),
              papaya_strings::escapeHTMLChars($this->_gt('Created'))
            );
            $result .= '<subitem/>';
            $result .= '<subitem/>';
          }
        } else {
          $result .= '<subitem/><subitem/><subitem/><subitem/>'.LF;
        }
        $result .= '</listitem>'.LF;
      }
    }
    $result .= '</items>';
    $result .= '</listview>';
    return $result;
  }

  /**
  * Get information about box
  *
  * @access public
  * @return string XML
  */
  function getBoxInfos() {
    $result = '';
    if (isset($this->box) && is_array($this->box)) {
      $this->loadTranslationsInfo();
      $listview = new PapayaUiListview();
      $listview->caption = new PapayaUiStringTranslated('Information');

      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('General')
      );
      $item->columnSpan = 2;
      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('Name')
      );
      $item->indentation = 1;
      $item->subitems[] = new PapayaUiListviewSubitemText($this->box['box_name']);
      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('Group')
      );
      $item->indentation = 1;
      $item->subitems[] = new PapayaUiListviewSubitemText(
        $this->boxGroupsList[$this->box['boxgroup_id']]['boxgroup_title']
      );
      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('Created')
      );
      $item->indentation = 1;
      $item->subitems[] = new PapayaUiListviewSubitemDate(
        (int)$this->box['box_created']
      );
      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('Modified')
      );
      $item->indentation = 1;
      $item->subitems[] = new PapayaUiListviewSubitemDate(
        (int)$this->box['box_modified']
      );
      if (!empty($this->box['box_published'])) {
        $now = time();
        $isPublic = (
          (empty($this->box['box_public_from']) || $this->box['box_public_from'] <= $now) &&
          (empty($this->box['box_public_to']) || $this->box['box_public_to'] >= $now)
        );
        $listview->items[] = $item = new PapayaUiListviewItem(
          '', new PapayaUiStringTranslated('Currently public')
        );
        $item->indentation = 1;
        $item->subitems[] = new PapayaUiListviewSubitemImage(
          $isPublic ? 'status-sign-ok' : 'status-sign-warning',
          new PapayaUiStringTranslated($isPublic ? 'Yes' : 'No')
        );
        if (!empty($this->box['box_public_from'])) {
          $listview->items[] = $item = new PapayaUiListviewItem(
            '', new PapayaUiStringTranslated('From')
          );
          $item->indentation = 2;
          $item->subitems[] = new PapayaUiListviewSubitemDate(
            (int)$this->box['box_public_from']
          );
        }
        if (!empty($this->box['box_public_to'])) {
          $listview->items[] = $item = new PapayaUiListviewItem(
            '', new PapayaUiStringTranslated('To')
          );
          $item->indentation = 2;
          $item->subitems[] = new PapayaUiListviewSubitemDate(
            (int)$this->box['box_public_to']
          );
        }
      }
      foreach ($this->papaya()->languages as $languageId => $language) {
        if ($language['is_content'] || isset($this->box['TRANSLATIONINFOS'][$languageId])) {
          $listview->items[] = $item = new PapayaUiListviewItem(
            './pics/language/'.$language['image'],
            $language['title'].' ('.$language['code'].')'
          );
          $item->columnSpan = 2;
          if (isset($this->box['TRANSLATIONINFOS'][$languageId])) {
            $translation = $this->box['TRANSLATIONINFOS'][$languageId];
            $listview->items[] = $item = new PapayaUiListviewItem(
              '', new PapayaUiStringTranslated('Title')
            );
            $item->indentation = 1;
            $item->subitems[] = new PapayaUiListviewSubitemText(
              $translation['box_title']
            );
            $listview->items[] = $item = new PapayaUiListviewItem(
              '', new PapayaUiStringTranslated('View')
            );
            $item->indentation = 1;
            $item->subitems[] = new PapayaUiListviewSubitemText(
              $translation['view_title']
            );
            if (isset($translation['box_trans_published'])) {
              if ($translation['box_trans_published'] <
                  $translation['box_trans_modified']) {
                $listview->items[] = $item = new PapayaUiListviewItem(
                  '', new PapayaUiStringTranslated('Status')
                );
                $item->indentation = 1;
                $item->subitems[] = new PapayaUiListviewSubitemText(
                  new PapayaUiStringTranslated('modified')
                );
              } else {
                $listview->items[] = $item = new PapayaUiListviewItem(
                  '', new PapayaUiStringTranslated('Status')
                );
                $item->indentation = 1;
                $item->subitems[] = new PapayaUiListviewSubitemText(
                  new PapayaUiStringTranslated('published')
                );
              }
              $listview->items[] = $item = new PapayaUiListviewItem(
                '', new PapayaUiStringTranslated('Published')
              );
              $item->indentation = 1;
              $item->subitems[] = new PapayaUiListviewSubitemDate(
                (int)$translation['box_trans_published']
              );
            } else {
              $listview->items[] = $item = new PapayaUiListviewItem(
                '', new PapayaUiStringTranslated('Status')
              );
              $item->indentation = 1;
              $item->subitems[] = new PapayaUiListviewSubitemText(
                new PapayaUiStringTranslated('created')
              );
            }
            $listview->items[] = $item = new PapayaUiListviewItem(
              '', new PapayaUiStringTranslated('Modified')
            );
            $item->indentation = 1;
            $item->subitems[] = new PapayaUiListviewSubitemDate(
              (int)$translation['box_trans_modified']
            );
          } else {
            $listview->items[] = $item = new PapayaUiListviewItem(
              '', new PapayaUiStringTranslated('Status')
            );
            $item->indentation = 1;
            $item->subitems[] = new PapayaUiListviewSubitemText(
              new PapayaUiStringTranslated('no content')
            );
          }
        }
      }
      $listview->items[] = $item = new PapayaUiListviewItem(
        '', new PapayaUiStringTranslated('Marked As Cacheable')
      );
      $item->columnSpan = 2;
      if (isset($this->box['TRANSLATIONINFOS']) && is_array($this->box['TRANSLATIONINFOS'])) {
        foreach ($this->box['TRANSLATIONINFOS'] as $languageId => $translation) {
          if (isset($this->papaya()->languages[$languageId])) {
            $language = $this->papaya()->languages[$languageId];
            $listview->items[] = $item = $aggregation = new PapayaUiListviewItem(
              './pics/language/'.$language['image'],
              $language['title'].' ('.$language['code'].')'
            );
            $item->indentation = 0;
            $cacheable = TRUE;
            if (!$translation['view_is_cacheable']) {
              $cacheable = FALSE;
              $listview->items[] = $item = new PapayaUiListviewItem(
                'categories-content',
                new PapayaUiStringTranslated('Content')
              );
              $item->indentation = 1;
              $item->subitems[] = new PapayaUiListviewSubitemText(
                $translation['view_title']
              );
            }
            $aggregation->subitems[] = new PapayaUiListviewSubitemImage(
              $cacheable ? 'status-sign-ok' : 'status-sign-problem',
              new PapayaUiStringTranslated($cacheable ? 'Yes' : 'No')
            );
          }
        }
      }
      $result .= $listview->getXml();
    }
    return $result;
  }

  /**
  * Get publish form
  *
  * @access public
  * @return mixed dialog
  */
  function getPublishForm() {
    if (isset($this->box['TRANSLATION'])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array(
        'cmd' => 'publish',
        'bid' => $this->box['box_id'],
        'confirm_publish'=>1,
      );
      $data = array(
        'box_public_from' => date(
          'Y-m-d H:i:s',
           empty($this->box['box_public_from']) ? time() : $this->box['box_public_from']
         ),
        'box_public_to' => empty($this->box['box_public_to'])
          ? '' : date('Y-m-d H:i:s', $this->box['box_public_to']),
      );
      if (isset($this->sessionParams['last_publish_message'])
        && trim($this->sessionParams['last_publish_message']) != '') {
          $publishVersionMessage = $this->sessionParams['last_publish_message'];
      } else {
        $publishVersionMessage = '';
      }
      $fields = array(
        'commit_message' => array(
          'Message', 'isSomeText', TRUE, 'input', 200, '', $publishVersionMessage
        ),
        'public_languages' => array(
          'Languages', 'isSomeText', TRUE, 'function', 'callbackPublishLanguages', '', '', 'left'
        ),
        'Publication period',
        'box_public_from' => array(
          'Published from', 'isIsoDateTime', FALSE, 'datetime', 20, '', ''
        ),
        'box_public_to' => array(
          'Published to', 'isIsoDateTime', FALSE, 'datetime', 20, '', ''
        )
      );
      $msg = sprintf(
        $this->_gt('Publish box "%s" (%s)?'),
        empty($this->box['box_name']) ? '' : $this->box['box_name'],
        (int)$this->box['box_id']
      );
      $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $dialog->msgs = &$this->msgs;
      $dialog->dialogTitle = $this->_gt('Publish');
      $dialog->buttonTitle = 'Publish';
      $dialog->inputFieldSize = 'x-large';

      return $dialog->getDialogXML();
    }
    return '';
  }

  /**
  * Callback for publish languages
  *
  * @param string $name
  * @param mixed $element
  * @param array $data
  * @access public
  * @return string $result XML
  */
  function callbackPublishLanguages($name, $element, $data) {
    $result = '';
    if (isset($this->lngSelect->languages) && is_array($this->lngSelect->languages)) {
      foreach ($this->lngSelect->languages as $lngId=>$lng) {
        if (isset($this->box['TRANSLATIONINFOS']) &&
            isset($this->box['TRANSLATIONINFOS'][$lngId])) {
          $selected = (is_array($data) && in_array($lngId, $data)) ? ' checked="checked"' : '';
          if ((!is_array($data)) && $this->lngSelect->currentLanguageId == $lngId) {
            $selected = ' checked="checked"';
          }
          if (isset($this->box['TRANSLATIONINFOS'][$lngId]['box_trans_published']) &&
                $this->box['TRANSLATIONINFOS'][$lngId]['box_trans_published'] >=
                $this->box['TRANSLATIONINFOS'][$lngId]['box_trans_modified']) {
            $disabled = ' disabled="disabled"';
          } else {
            $disabled = '';
          }
          $result .= sprintf(
            '<input type="checkbox" name="%s[%s][]" value="%d" %s%s/>'.LF,
            papaya_strings::escapeHTMLChars($this->paramName),
            papaya_strings::escapeHTMLChars($name),
            (int)$lngId,
            $selected,
            $disabled
          );
          $result .=
            papaya_strings::escapeHTMLChars($this->lngSelect->languages[$lngId]['lng_title']).' ('.
            papaya_strings::escapeHTMLChars($this->lngSelect->languages[$lngId]['lng_short']).')';
        }
      }
    }
    return $result;
  }

  /**
  * Get public delete form
  *
  * @access public
  * @return mixed dialog
  */
  function getDeletePublicForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'deletepublic',
      'bid' => $this->box['box_id'],
      'confirm_delete'=>1,
    );
    $msg = $this->_gt('Delete published box?');
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Get public delete translation form
  *
  * @access public
  * @return mixed dialog
  */
  function getDeletePublicTransForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'deletepublic_trans',
      'bid' => $this->box['box_id'],
      'lng_id' => $this->params['lng_id'],
      'confirm_delete'=>1,
    );
    $msg = sprintf(
      $this->_gt('Delete published box for language %s (%s)?'),
      $this->lngSelect->languages[$this->params['lng_id']]['lng_title'],
      $this->lngSelect->languages[$this->params['lng_id']]['lng_short']
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    return $dialog->getMsgDialog();
  }

  /**
  * Get preview frame
  *
  * @param string $caption
  * @param integer $sid
  * @access public
  * @return string $result
  */
  function getPreviewFrame($caption, $viewMode, $views = NULL, $currentView = NULL) {
    $result = $this->getSelectPageDialog();
    $result .= sprintf(
      '<panel width="100%%" title="%s [%s]">',
      papaya_strings::escapeHTMLChars($this->_gt($caption)),
      papaya_strings::escapeHTMLChars($this->box['box_name'])
    );
    $lngIdent = $this->lngSelect->currentLanguage['lng_ident'];
    if (isset($views) && is_array($views) && count($views) > 0) {
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_paging_buttons.php');
      $result .= papaya_paging_buttons::getButtons(
        $this, array(), $views, $currentView, 'viewmode'
      );
    }
    $link = $this->getWebLink(
      $this->pageId,
      $lngIdent,
      $viewMode,
      array('papaya_box_preview' => $this->box['box_id']),
      NULL,
      'index'
    );
    $result .= sprintf(
      '<iframe width="100%%" scrolling="auto" height="550" src="%s" class="inset" id="preview" />',
      papaya_strings::escapeHTMLChars($link)
    );
    $result .= '</panel>';
    return $result;
  }

  /**
  * Get select page id dialog xml (used to define an page id for preview)
  *
  * @return string
  */
  function getSelectPageDialog() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $sessionParams = $this->getSessionValue('PAPAYA_SESS_tt');
    $this->pageId = 0;
    if (isset($sessionParams['page_id']) && $sessionParams['page_id'] > 0) {
      $data = array('page_id' => (int)$sessionParams['page_id']);
      $this->pageId = (int)$sessionParams['page_id'];
    } else {
      $data = array();
    }
    $hidden = array(
      'cmd' => 'select_page'
    );
    $fields = array(
      'page_id' => array('Page id', 'isNum', TRUE, 'pageid', 100, '', 0),
    );
    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $dialog->tokenKeySuffix = 'select_preview_page';
    $dialog->dialogTitle = $this->_gt('Select preview page');
    $dialog->buttonTitle = 'Select';
    $dialog->inputFieldSize = 'large';
    $dialog->loadParams();
    if ($dialog->checkDialogInput()) {
      $sessionParams['page_id'] = (int)$dialog->data['page_id'];
      $this->pageId = (int)$dialog->data['page_id'];
      $this->setSessionValue('PAPAYA_SESS_tt', $sessionParams);
    }
    return $dialog->getDialogXML();
  }

  /**
  * Parsed Box
  *
  * @access public
  * @return mixed preview
  */
  function parsedBox($topicObj, $parseParams = array()) {
    $output = '';
    if (isset($this->box) && is_array($this->box) && isset($this->box['TRANSLATION'])) {
      if (isset($this->box['TRANSLATION']['module_guid']) &&
          $this->box['TRANSLATION']['module_guid'] != '') {
        $plugin = $this->papaya()->plugins->get(
          $this->box['TRANSLATION']['module_guid'], $topicObj
        );
        if ($plugin instanceOf PapayaPluginAppendable) {
          $plugin->content()->setXml($this->box['TRANSLATION']['box_data']);
          $dom = new PapayaXmlDocument();
          $boxNode = $dom->appendElement('box');
          $boxNode->append($plugin);
          $output = $boxNode->saveFragment();
        } elseif (isset($plugin) && is_object($plugin)) {
          $plugin->boxId = $this->box['box_id'];
          $plugin->languageId = $this->box['TRANSLATION']['lng_id'];
          $plugin->setData($this->box['TRANSLATION']['box_data']);
          $output = $plugin->getParsedData();
        }
        if (!empty($output)) {
          include_once(PAPAYA_INCLUDE_PATH.'system/papaya_parser.php');
          $parser = new papaya_parser;
          $parser->tableTopics = $this->tableTopics;
          $parser->tableTopicsTrans = $this->tableTopicsTrans;
          if (isset($parseParams['link_outputmode'])) {
            $parser->setLinkOutputMode($parseParams['link_outputmode']);
          }
          $output = $parser->parse($output, $this->box['TRANSLATION']['lng_id']);
        }
      } else {
        $this->logMsg(
          MSG_ERROR,
          PAPAYA_LOGTYPE_SYSTEM,
          'No module for box defined.'
        );
      }
    }
    return $output;
  }

  /**
  * get current box view id
  *
  * @return integer
  */
  function getViewId() {
    if (isset($this->box) && $this->box['TRANSLATION']) {
      return (int)$this->box['TRANSLATION']['view_id'];
    } else {
      return 0;
    }
  }

  /**
  * List of modules and views
  *
  * @return string
  */
  function getEditView() {
    if (isset($this->box['TRANSLATION'])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_selectview.php');
      $selectView = new base_selectview();
      $selectView->authUser = &$this->authUser;
      $selectView->msgs = &$this->msgs;
      $selectView->images = &$this->images;
      $selectView->load((int)$this->box['TRANSLATION']['view_id'], 'box');
      $selectView->actionLink = $this->getLink(
        array('cmd'=>'chg_view', 'bid'=>$this->box['box_id']),
        $this->paramName
      ).'&'.$this->paramName.'[view_id]=';
      return $selectView->getXMLViewList();
    } else {
      return '';
    }
  }

  /**
  * save language specific view
  *
  * @access public
  * @return boolean
  */
  function saveView() {
    if (isset($this->box['TRANSLATION']) && is_array($this->box['TRANSLATION'])) {
      $dataTrans = array(
        'view_id' => (int)$this->params['view_id']
      );
      if ($this->checkDataModified($dataTrans, $this->box['TRANSLATION'])) {
        $dataTrans['box_trans_modified'] = time();
        $filter = array(
          'box_id' => (int)$this->box['box_id'],
          'lng_id' => $this->box['TRANSLATION']['lng_id']
        );
        if (FALSE !== $this->databaseUpdateRecord(
              $this->tableBoxTrans, $dataTrans, $filter)) {
          $data = array('box_modified' => $dataTrans['box_trans_modified']);
          $updated = $this->databaseUpdateRecord(
            $this->tableBox, $data, 'box_id', (int)$this->box['box_id']
          );
          if (FALSE !== $updated) {
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_PAGES,
              sprintf(
                'View of box "%s (%d)" changed.',
                $this->box['TRANSLATION']['box_title'],
                $this->box['box_id']
              )
            );
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }
}
?>
