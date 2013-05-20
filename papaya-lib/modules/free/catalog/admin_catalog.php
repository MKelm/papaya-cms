<?php
/**
* Catalog administration
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
* @package Papaya-Modules
* @subpackage Free-Catalog
* @version $Id: admin_catalog.php 37226 2012-07-13 09:52:38Z weinert $
*/

/**
* Basicclass for database access
*/
require_once(dirname(__FILE__)."/base_catalog.php");

/**
* Catalog administration
*
* @package Papaya-Modules
* @subpackage Free-Catalog
*/
class admin_catalog extends base_catalog {

  /**
  * topic parameter name
  * @var string $topicParamName
  */
  var $topicParamName = 'tt';

  /**
  * catalog catagories types list
  * @var array $catalogTypes
  */
  var $catalogTypes = NULL;

  /**
  * List of displayed Link Synonyms
  * @var array $catalogSynonyms
  */
  var $catalogSynonyms = NULL;

  /**
  * clipboard array
  * @var array $clipboard
  */
  var $clipboard = NULL;

  /**
  * @var string
  */
  var $tableSubscriptionsMeta;

  /**
  * @var string
  */
  var $tableSubscriptions;

  /**
  * Dialog to edit a topic's links
  * @var base_dialog
  */
  var $_topicEditForm = NULL;

  /**
  * Initial funktion for module
  *
  * @access public
  */
  function initialize() {
    $this->tableSubscriptionsMeta = PAPAYA_DB_TABLEPREFIX . '_catalog_subscriptions_meta';
    $this->tableSubscriptions = PAPAYA_DB_TABLEPREFIX . '_catalog_subscriptions';
    $this->initializeParams();
    $this->sessionPageParams =
      $this->getSessionValue('PAPAYA_SESS_'.$this->topicParamName);
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('mode', array('contentmode'));
    $this->initializeSessionParam('catalog_id', array('contentmode'));
    $this->initializeSessionParam('offset', array('contentmode'));
    $this->initializeSessionParam('patt', array('contentmode'));
    $this->initializeSessionParam('catalog_subscription_email', array('contentmode'));
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Execute - basic function for handling parameters
  *
  * @access public
  */
  function execute() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $this->lngSelect = &base_language_select::getInstance();
    switch (@$this->params['mode']) {
    case 1 :
      if ($this->module->hasPerm(2)) {
        $this->loadCatalogTypes();
        switch (@$this->params['cmd']) {
        case 'add_type' :
          if (isset($this->params['save']) && $this->params['save']) {
            $this->initializeCatalogTypeAddForm();
            if ($this->catalogTypeDialog->checkDialogInput()) {
              if ($newId = $this->addCatalogType()) {
                $this->addMsg(MSG_INFO, $this->_gt('Added.'));
                $this->params['type_id'] = $newId;
                unset($this->catalogTypeDialog);
                $this->loadCatalogTypes();
              }
            }
          }
          break;
        case 'edit_type':
          if (isset($this->params['type_id']) && $this->params['type_id'] > 0
              && isset($this->params['save']) && $this->params['save']) {
            $this->initializeCatalogTypeEditForm();
            if ($this->catalogTypeDialog->checkDialogInput()) {
              if ($this->saveCatalogType()) {
                $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
                unset($this->catalogTypeDialog);
                $this->loadCatalogTypes();
              }
            }
          }
          break;
        case 'del_type':
          if (isset($this->params['type_id']) && $this->params['type_id'] > 0 &&
              isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete']) {
            if ($this->deleteCatalogType()) {
              $this->addMsg(MSG_INFO, $this->_gt('Deleted.'));
              unset($this->params['cmd']);
              $this->loadCatalogTypes();
            }
          }
          break;
        }
      }
      break;
    case 2 :
      if ($this->module->hasPerm(2)) {
        $this->loadCatalogTypes();
        switch (@$this->params['cmd']) {
        case 'delete_subscriptions':
          if (isset($this->params['catalog_subscription_email'])
              && $this->params['catalog_subscription_email'] !== '') {
            if (isset($this->params['confirm_delete'])
                && $this->params['confirm_delete']) {
              if (TRUE && $this->deleteCatalogSubscriptions()) {
                unset($this->params['cmd']);
              }
            }
          }
          break;
        default:
        case 'list_subscriptions':
          /* nothing to execute for listing */
          break;
        }
      }
      break;
    case 0 :
    default :
      if (isset($this->sessionParams['catalogopen']) &&
          is_array($this->sessionParams['catalogopen'])) {
        $this->catalogsOpen = $this->sessionParams['catalogopen'];
      } else {
        $this->catalogsOpen = array();
      }
      switch (@$this->params['cmd']) {
      case 'regenerate':
        $this->repairCatalogPath();
        break;
      case 'topiclinks_edit':
        $this->executeLinkChanges();
        break;
      case 'move_up':
      case 'move_down':
        if (isset($this->params['cataloglink_id']) &&
            $this->params['cataloglink_id'] > 0) {
          $this->moveLink((int)$this->params['cataloglink_id']);
        }
        break;
      case 'open':
        $this->catalogsOpen[(int)$this->params['catalog_id']] = TRUE;
        break;
      case 'close':
        unset($this->catalogsOpen[(int)$this->params['catalog_id']]);
        break;
      case 'repair':
        $this->repairPaths();
        break;
      case 'add_catalog':
        if ($newId = $this->addCatalog(@(int)$this->params['catalog_id'])) {
          $this->addMsg(MSG_INFO, $this->_gt('Category added.'));
          $this->catalogsOpen[$this->params['catalog_id']] = TRUE;
          $this->params['catalog_id'] = $newId;
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
        }
        break;
      case 'create_catalog_detail' :
        if (isset($this->params['catalog_id']) &&
            $this->params['catalog_id'] > 0 &&
            $this->loadCatalog($this->params['catalog_id'])) {
          $this->initializeCatalogEditForm();
          if ($this->dialogCatalog->modified()) {
            if ($this->dialogCatalog->checkDialogInput()) {
              if ($this->createDialogDetail($this->dialogCatalog->data)) {
                unset($this->dialogCatalog);
                $this->loadCatalogDetail(
                  $this->params['catalog_id'], $this->lngSelect->currentLanguageId
                );
                $this->addMsg(
                  MSG_INFO,
                  sprintf($this->_gt('%s created.'), $this->_gt('Category'))
                );
              }
            }
          } else {
            $this->addMsg(MSG_INFO, sprintf($this->_gt('No changes.')));
          }
        }
        break;
      case 'edit_catalog_detail':
        if (isset($this->params['catalog_id']) &&
            $this->params['catalog_id'] > 0 &&
            $this->loadCatalog($this->params['catalog_id']) &&
            $this->loadCatalogDetail(
              $this->params['catalog_id'], $this->lngSelect->currentLanguageId
            )
           ) {
          $this->initializeCatalogEditForm();
          if ($this->dialogCatalog->modified()) {
            if ($this->dialogCatalog->checkDialogInput()) {
              if ($this->saveCatalogDetail($this->dialogCatalog->data)) {
                unset($this->dialogCatalog);
                $this->loadCatalogDetail(
                  $this->params['catalog_id'],
                  $this->lngSelect->currentLanguageId
                );
                $this->addMsg(
                  MSG_INFO,
                  sprintf($this->_gt('%s changed.'), $this->_gt('Category'))
                );
              }
            }
          } else {
            $this->addMsg(MSG_INFO, sprintf($this->_gt('No changes.')));
          }
        }
        break;
      case 'del_catalog':
        $this->loadCatalog($this->params['catalog_id']);
        if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
          if ($this->catalogExist($this->params['catalog_id'])) {
            if ($this->catalogHasNoSubCategories($this->params['catalog_id'])) {
              if ($this->deleteCatalog($this->params['catalog_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Category deleted.'));
                if ($this->catalogExist($this->catalog['catalog_parent'])) {
                  $this->params['catalog_id'] = $this->catalog['catalog_parent'];
                  $this->loadCatalog($this->params['catalog_id']);
                  $this->loadCatalogDetail(
                    $this->params['catalog_id'],
                    $this->lngSelect->currentLanguageId
                  );
                } else {
                  $this->params['catalog_id'] = 0;
                  unset($this->catalog);
                  unset($this->catalogDetail);
                }
                $this->params['cmd'] = NULL;
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            } else {
              $this->addMsg(MSG_WARNING, $this->_gt('Category is not empty.'));
              $this->params['cmd'] = '';
            }
          } else {
            $this->addMsg(MSG_WARNING, $this->_gt('Category not found.'));
          }
        }
        break;
      case 'cut_catalog' :
        if ($this->catalogExist($this->params['catalog_id'])) {
          $this->cutToClipboard($this->params['catalog_id']);
        }
        break;
      case 'add_topic':
        $this->addTopic();
        break;
      case 'del_topic' :
        $this->getXMLInfobar();
        if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
          if ($this->deleteTopic($this->params['catalog_id'], $this->params['topic_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Link deleted.'));
            $this->params['topic_id'] = NULL;
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
        break;
      case 'paste':
        if (isset($this->params['catalog_id']) && $this->params['catalog_id'] >= 0) {
          if ($this->catalogExist($this->params['catalog_id'])) {
            if (isset($this->params['paste_id']) &&
                $this->params['catalog_id'] != $this->params['paste_id']) {
                $this->pasteCatalog();
            } else {
              $this->addMsg(
                MSG_ERROR,
                $this->_gt('You must select a catalog first')
              );
            }
          }
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('You must select a catalog first')
          );
        }
        break;
      case 'add_synonym':
        if (isset($this->params['catalog_id']) && $this->params['catalog_id'] != '') {
          if (trim($this->params['catalogsynonym_title']) != '') {
            $data = array(
              'catalogsynonym_title' => $this->params['catalogsynonym_title'],
              'lng_id' => $this->lngSelect->currentLanguageId,
              'catalog_id' => $this->params['catalog_id']);
            if ($id = $this->databaseInsertRecord(
                  $this->tableCatalogSynonyms, 'catalogsynonym_id', $data)) {
              $this->addMsg(
                MSG_INFO,
                sprintf(
                  $this->_gt('Synonym "%s" (%d) added.'),
                  $this->params['catalogsynonym_title'],
                  $id
                )
              );
            } else {
              $this->addMsg(
                MSG_ERROR,
                sprintf(
                  $this->_gt('Synonym "%s" could not be added.'),
                  $this->params['catalogsynonym_title']
                )
              );
            }
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Synonym title may not be empty.'));
          }
        }
        break;
      case 'del_synonym':
        if (isset($this->params['catalog_id']) && $this->params['catalog_id'] != '') {
          if (isset($this->params['catalogsynonym_id']) &&
              trim(@$this->params['catalogsynonym_id']) != '') {
            if (@$this->params['confirmdelete']) {
              $data = array(
                'lng_id' => $this->lngSelect->currentLanguageId,
                'catalog_id' => $this->params['catalog_id'],
                'catalogsynonym_id' => $this->params['catalogsynonym_id']);
              if ($this->databaseDeleteRecord($this->tableCatalogSynonyms, $data)) {
                $this->addMsg(
                  MSG_INFO,
                  sprintf(
                    $this->_gt('Synonym (%d) deleted.'),
                    $this->params['catalogsynonym_id']
                  )
                );
              } else {
                $this->addMsg(
                  MSG_INFO,
                  $this->_gt('Synonym could not be deleted.')
                );
              }
            } else {
              $this->getXMLDelSynonymDialog();
            }
          }
        }
        break;
      case 'save_synonym':
        if (isset($this->params['catalog_id']) && $this->params['catalog_id'] != '') {
          if (isset($this->params['catalogsynonym_id']) &&
              trim($this->params['catalogsynonym_id']) != '' &&
              trim(@$this->params['catalogsynonym_title']) != '') {
            $data = array(
              'lng_id' => $this->lngSelect->currentLanguageId,
              'catalog_id' => $this->params['catalog_id'],
              'catalogsynonym_title' => $this->params['catalogsynonym_title'],
            );
            $condition = array('catalogsynonym_id' => $this->params['catalogsynonym_id']);
            if ($this->databaseUpdateRecord(
                  $this->tableCatalogSynonyms, $data, $condition)) {
              $this->addMsg(
                MSG_INFO,
                sprintf(
                  $this->_gt('Synonym "%s" (%d) saved.'),
                  $this->params['catalogsynonym_title'],
                  $this->params['catalogsynonym_id']
                )
              );
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Synonym could not be updated.'));
            }
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Synonym title may not be empty.'));
          }
        }
        break;
      case 'edit_synonym':
        break;
      }
      $this->sessionParams['catalogopen'] = $this->catalogsOpen;
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
      $this->loadCatalogs();
      $this->loadClipboard();
      if (isset($this->params) && isset($this->params['catalog_id'])) {
        $this->loadCatalog($this->params['catalog_id']);
        $this->loadCatalogDetail(
          $this->params['catalog_id'],
          $this->lngSelect->currentLanguageId
        );
      }
    }
  }

  /**
  * Execute - function for handling link changes
  *
  * @access public
  */
  function executeLinkChanges() {
    $catalogLinkTitles = array();
    $catalogLinkIds[] = array();
    foreach ($this->params as $field => $data) {
      if (strpos($field, 'cataloglink_titles_') === 0) {
        $index = substr($field, 19);
        if (strpos($index, 'new_') === 0) {
          if (!isset($catalogLinkTitles['new'])) {
            $catalogLinkTitles['new'] = array();
          }
          $catalogLinkTitles['new'][substr($index, 4)] = $data;
        } else {
          $catalogLinkIds[] = $index;
          $catalogLinkTitles[$index] = $data;
        }
      }
    }
    foreach ($catalogLinkTitles as $id => $linkData) {
      if ($id == 'new' && isset($linkData) && is_array($linkData)) {
        $sql = "SELECT MAX(cataloglink_sort)
                  FROM %s
                 WHERE catalog_id = %d
                   AND lng_id = %d";
        $params = array($this->tableCatalogLinks,
                        (int)$this->params['catalog_id'],
                        $this->lngSelect->currentLanguageId);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          $maxSort = (int)$res->fetchField();
        } else {
          $maxSort = 0;
        }
        foreach ($linkData as $lngId => $title) {
          if (isset($title) && $title != '') {
            $data = array(
              'catalog_id' => (int)$this->params['catalog_id'],
              'lng_id' => $lngId,
              'topic_id' => (int)$this->params['topic_id'],
              'cataloglink_title' => $title,
              'cataloglink_sort' => ++$maxSort
            );
            $newLinkId = $this->databaseInsertRecord(
              $this->tableCatalogLinks,
              'cataloglink_id',
              $data
            );
            if (FALSE !== $newLinkId) {
              $catalogLinkIds[] = $newLinkId;
            }
          }
        }
      } else {
        if ($linkData == '') {
          $this->databaseDeleteRecord($this->tableCatalogLinks, 'cataloglink_id', $id);
        } else {
          $data = array(
            'cataloglink_title' => $linkData
          );
          $this->saveTopicDetail($id, $data);
        }
      }
    }
    $this->loadTopicDetail($this->params['catalog_id'], $this->params['topic_id']);
    if ($this->params['topic_id'] != $this->params['new_topic_id']) {
      $this->params['topic_id'] = $this->params['new_topic_id'];
      $values = array('topic_id' => $this->params['new_topic_id']);
      foreach ($catalogLinkIds as $id) {
        if ((int)$id > 0) {
          if (!isset($condition)) {
            $condition = array('cataloglink_id' => array());
          }
          $condition['cataloglink_id'][] = (int)$id;
        }
      }
      if (isset($condition)) {
        $this->loadTopicDetail($this->params['catalog_id'], $this->params['topic_id']);
        $this->databaseUpdateRecord($this->tableCatalogLinks, $values, $condition);
      }
    }
  }

  /**
  * Function to handle link movements
  *
  * @param integer $linkId
  * @param boolean $recursion optional, default value FALSE
  * @access public
  */
  function moveLink($linkId, $recursion = FALSE) {
    $sql = "SELECT cataloglink_id, catalog_id, lng_id, cataloglink_sort
              FROM %s
             WHERE cataloglink_id = %d";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableCatalogLinks, $linkId))) {
      if ($link = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $sql = "SELECT COUNT(*)
                  FROM %s
                 WHERE catalog_id = %d
                   AND lng_id = %d
                   AND cataloglink_sort = %d";
        $params = array($this->tableCatalogLinks,
                        $link['catalog_id'],
                        $link['lng_id'],
                        $link['cataloglink_sort']);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          if ($res->fetchField() > 1) {
            //double sort - regenerate sort
            $this->loadCatalogLinkList(
              $link['catalog_id'], $link['lng_id'], 0, FALSE
            );
            if (isset($this->linkList[$link['catalog_id']]) &&
                is_array($this->linkList[$link['catalog_id']])) {
              $counter = 0;
              foreach ($this->linkList[$link['catalog_id']] as $aLink) {
                $data = array('cataloglink_sort' => ++$counter);
                if ($counter != @(int)$aLink['cataloglink_sort']) {
                  $filter = array('cataloglink_id' => $aLink['cataloglink_id']);
                  $this->databaseUpdateRecord(
                    $this->tableCatalogLinks, $data, $filter
                  );
                }
              }
            }
            if (!$recursion) {
              $this->moveLink($linkId, TRUE);
            }
            return 0;
          }
        }
        if ($this->params['cmd'] == 'move_up') {
          $operator = '<';
          $sort = 'DESC';
        } else {
          $operator = '>';
          $sort = 'ASC';
        }
        $sql = "SELECT cataloglink_id, cataloglink_sort
                  FROM %s
                 WHERE catalog_id = %d
                   AND lng_id = %d
                   AND cataloglink_sort $operator %d
                 ORDER BY cataloglink_sort $sort, cataloglink_title $sort";
        if ($res = $this->databaseQueryFmt($sql, $params, 1)) {
          if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            if ($this->databaseUpdateRecord(
                  $this->tableCatalogLinks,
                  array('cataloglink_sort' => $row['cataloglink_sort']),
                  array('cataloglink_id' => $link['cataloglink_id'])
                ) &&
                $this->databaseUpdateRecord(
                  $this->tableCatalogLinks,
                  array('cataloglink_sort' => $link['cataloglink_sort']),
                  array('cataloglink_id' => $row['cataloglink_id'])
                )
               ) {
              $this->addMsg(MSG_INFO, $this->_gt('Link moved'));
            }
          }
        }
      }
    }
  }

  /**
  * get catalog toolbar in xml
  *
  * @access public
  */
  function getXMLCatalogToolbar() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$GLOBALS['PAPAYA_IMAGES'];
    $toolbar->addButton(
      'Properties',
      $this->getLink(
        array(
          'contentmode' => 0,
          'catalog_id' => @(int)$this->params['catalog_id']
        )
      ),
      'categories-properties',
      '',
      @$this->params['contentmode'] == 0
    );
    $toolbar->addButton(
      'Synonyms',
      $this->getLink(
        array(
          'contentmode' => 1,
          'catalog_id' => @(int)$this->params['catalog_id']
        )
      ),
      'items-alias',
      '',
      @$this->params['contentmode'] == 1
    );
    if ($str = $toolbar->getXML()) {
      $this->layout->add('<toolbar>'.$str.'</toolbar>');
    }
  }

  /**
  * Get XML - XML for admin page
  *
  * @access public
  */
  function getXML() {
    if (is_object($this->layout)) {
      $this->getXMLButtons();
      switch (@$this->params['mode']) {
      case 1:
        if ($this->module->hasPerm(2)) {
          $this->getXMLCatalogTypes();
          if (isset($this->catalogTypes) &&
              isset($this->params['type_id']) &&
              isset($this->catalogTypes[$this->params['type_id']])) {
            if (isset($this->params['cmd']) && $this->params['cmd'] == 'del_type') {
              $this->getXMLDelCatalogTypeForm();
            } else {
              $this->getXMLCatalogTypeEditForm();
            }
          } else {
            $this->getXMLCatalogTypeAddForm();
          }
        }
        break;
      case 2:
        if ($this->module->hasPerm(2)) {
          $this->displaySearch();
          $this->getXMLSubscriptions();
          $this->getXMLSubscriptionEdit();
        }
        break;
      case 0:
      default:
        $this->getXMLCatalogTree();
        $this->getXMLClipboard();
        switch (@$this->params['cmd']) {
        case 'del_catalog':
          $this->getXMLDelCatalogForm();
          $this->getXMLInfobar();
          break;
        case 'del_topic':
          $this->getXMLDelTopicForm();
          $this->getXMLSynonymList();
          break;
        case 'add_topic':
          $this->getXMLTopicAddForm();
          $this->getXMLSynonymList();
          $this->getXMLInfobar();
          break;
        default:
          if (isset($this->params['topic_id'])) {
            $this->getXMLTopicForm();
            $this->getXMLSynonymList();
            $this->getXMLInfobar();
          } elseif (!isset($this->params['catalog_id']) ||
                     $this->params['catalog_id'] == 0) {
            $this->layout->addRight(
              '<sheet width="350" align="center"><text>'.
              '<div style="padding: 0px 5px 0px 5px; ">'.
              _gtfile('linkdb_info.txt').'</div></text></sheet>'
            );
          } else {
            $this->getXMLCatalogToolbar();
            switch (@$this->params['contentmode']) {
            case 0:
              $this->getXMLCatalogEditForm();
              break;
            case 1:
              $this->getXMLCatalogSynonymForm();
              break;
            }
            $this->getXMLSynonymList();
            $this->getXMLInfobar();
          }
        }
      }
    }
  }

  /**
  *
  * Get XML for category tree
  *
  * @access public
  *
  */
  function getXMLCatalogTree() {
    $result = sprintf(
      '<listview title="%s" >'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Categories'))
    );
    $result .= '<items>'.LF;
    if (isset($this->catalogs) && is_array($this->catalogs)) {
      if (isset($this->params) && isset($this->params['catalog_id'])) {
        $selected = ($this->params['catalog_id'] == 0) ? ' selected="selected"' : '';
      } else {
        $selected = '';
      }
      $result .= sprintf(
        '<listitem href="%s" title="%s" image="%s" %s/>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('catalog_id' => 0))
        ),
        papaya_strings::escapeHTMLChars($this->_gt('Base')),
        papaya_strings::escapeHTMLChars($this->images['status-folder-open']),
        $selected
      );
      $result .= $this->getXMLCatalogSubTree(0, 0);
    } else {
      $result .= sprintf(
        '<listitem href="%s" title="%s" image="%s" %s/>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('catalog_id'=>0))
        ),
        papaya_strings::escapeHTMLChars($this->_gt('Base')),
        papaya_strings::escapeHTMLChars($this->images['items-folder']),
        ' selected="selected"'
      );
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    $this->layout->addLeft($result);
  }

  /**
  *
  * Get XML for category tree by clipboard
  *
  * @access public
  *
  */
  function getXMLClipboard() {
    if (isset($this->clipboard) && is_array($this->clipboard)) {
      $result = sprintf(
        '<listview title="%s" >'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Clipboard'))
      );
      $result .= '<items>'.LF;
      foreach ($this->clipboard as $id => $value) {
        $categTitle = ($value['catalog_title']) ?
          $value['catalog_title'] :
          $this->_gt('No title');
        $result .= sprintf(
          '<listitem title="%s" image="%s">'.LF,
          papaya_strings::escapeHTMLChars($categTitle),
          papaya_strings::escapeHTMLChars($this->images['items-folder'])
        );
        $result .= sprintf(
          '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s" /></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd' => 'paste',
                'catalog_id' => $this->params['catalog_id'],
                'paste_id' => $value['catalog_id']
              )
            )
          ),
          papaya_strings::escapeHTMLChars($this->images['actions-edit-paste']),
          papaya_strings::escapeHTMLChars($this->_gt('Paste Category'))
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  *
  * Get XML for Infobar
  *
  * @access public
  *
  */
  function getXMLInfobar() {
    $this->loadCatalogInformation($this->params['catalog_id']);
    $result = sprintf(
      '<listview title="%s" >'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Catalog information'))
    );
    $result .= '<items>'.LF;
    if (isset($this->lngSelect->languages) && is_array($this->lngSelect->languages)) {
      foreach ($this->lngSelect->languages as $lngId=>$lng) {
        if ($lng['lng_glyph'] != '' &&
            file_exists($this->getBasePath(TRUE).'pics/language/'.$lng['lng_glyph'])) {
          $image = ' image="./pics/language/'.
            papaya_strings::escapeHtmlChars($lng['lng_glyph']).'"';
        } else {
          $image = '';
        }
        $result .= sprintf(
          '<listitem title="%s"%s ><subitem >%s</subitem></listitem>'.LF,
          papaya_strings::escapeHtmlChars($lng['lng_title']),
          $image,
          papaya_strings::escapeHtmlChars($lng['lng_short'])
        );
        if (isset($this->catalogDetails) &&
            isset($this->catalogDetails[$lngId]) &&
            is_array($this->catalogDetails[$lngId]) ) {
          if (isset($this->catalogDetails[$lngId]['catalog_title'])) {
            $result .= sprintf(
              '<listitem title="%s" indent="2"><subitem>%s</subitem></listitem>'.LF,
              papaya_strings::escapeHtmlChars($this->_gt('Title')),
              papaya_strings::escapeHtmlChars(
                $this->catalogDetails[$lngId]['catalog_title']
              )
            );
          } else {
            $result .= sprintf(
              '<listitem title="%s" indent="2" span="2"></listitem>'.LF,
              papaya_strings::escapeHtmlChars($this->_gt('Title'))
            );
          }
        } else {
          $result .= sprintf(
            '<listitem title="%s" indent="2"><subitem>%s</subitem></listitem>'.LF,
            papaya_strings::escapeHtmlChars($this->_gt('Status')),
            papaya_strings::escapeHtmlChars($this->_gt('No content'))
          );
        }
      }
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    $this->layout->addRight($result);

  }

  /**
  * XML for sub tree of catalogs
  '
  * @param integer $parent Parent-ID
  * @param integer $indent shifting
  * @return string $result XML
  */
  function getXMLCatalogSubTree($parent, $indent) {
    $result = '';
    if (isset($this->catalogTree[$parent]) &&
        is_array($this->catalogTree[$parent]) &&
        (isset($this->catalogsOpen[$parent]) || ($parent == 0))) {
      foreach ($this->catalogTree[$parent] as $id) {
        $result .= $this->getXMLCatalogEntry($id, $indent);
      }
    }
    return $result;
  }


  /**
  * Get XML for one catalog entry
  *
  * @param integer $id ID
  * @param integer $indent shifting
  * @return string $result XML
  */
  function getXMLCatalogEntry($id, $indent, $mode = TRUE) {
    $result = '';
    if (isset($this->catalogs[$id]) && is_array($this->catalogs[$id])) {
      $opened = (isset($this->catalogsOpen[$id]) && @$this->catalogs[$id]['CATEG_COUNT'] > 0);
      if (@$this->catalogs[$id]['CATEG_COUNT'] < 1) {
        $nodeHref = FALSE;
        $node = ' node="empty"';
        $imageIndex = 'items-folder';
      } elseif ($opened) {
        $nodeHref = $this->getLink(array('cmd'=>'close', 'catalog_id' => (int)$id));
        $node = sprintf(
          ' node="open" nhref="%s"',
          papaya_strings::escapeHTMLChars($nodeHref)
        );
        $imageIndex = 'status-folder-open';
      } else {
        $nodeHref = $this->getLink(array('cmd'=>'open', 'catalog_id' => (int)$id));
        $node = sprintf(
          ' node="close" nhref="%s"',
          papaya_strings::escapeHTMLChars($nodeHref)
        );
        $imageIndex = 'items-folder';
      }
      if (!isset($this->catalogs[$id]) || !isset($this->catalogs[$id]['catalog_title']) ||
          $this->catalogs[$id]['catalog_title'] == "") {
        $title = $this->_gt('No title');
      } else {
        $title = $this->catalogs[$id]['catalog_title'];
      }
      if ($opened) {
        $modus = 'close';
      } else {
        $modus = 'open';
      }
      if (isset($this->params) && isset($this->params['catalog_id'])) {
        $selected = ($this->params['catalog_id'] == $id) ? ' selected="selected"' : '';
      } else {
        $selected = '';
      }
      $result .= sprintf(
        '<listitem href="%s" title="%s" indent="%d" image="%s" %s %s/>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('catalog_id' => (int)$id))
        ),
        papaya_strings::escapeHTMLChars($title),
        (int)$indent,
        papaya_strings::escapeHTMLChars($this->images[$imageIndex]),
        $node,
        $selected
      );
      $result .= $this->getXMLCatalogSubTree($id, $indent + 1);
    }
    return $result;
  }

  /**
  * Display search dialog
  *
  * @access public
  */
  function displaySearch() {
    $result = '';
    $result .= sprintf(
      '<dialog title="%s" action="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Search')),
      papaya_strings::escapeHTMLChars($this->baseLink)
    );
    $result .= '<lines>'.LF;
    $result .= '<line align="center">';
    $result .= sprintf(
      '<input type="text" class="smallinput" name="%s[patt]" value="%s" /></line>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($this->params['patt'])
        ? '' : papaya_strings::escapeHTMLChars($this->params['patt'])
    );
    $result .= '</lines>'.LF;
    $result .= sprintf(
      '<dlgbutton value="%s" />'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Search'))
    );
    $result .= '</dialog>'.LF;
    $this->layout->addLeft($result);
  }

  /**
   * Get a list of subscribed emails and add it to layout. In case a search
   * was done, pick that up to reduce the dataset with a LIKE clause.
   */
  function getXMLSubscriptions() {
    $resultSet = array();
    $absCount = 1;
    $limit = 20;
    $offset = isset($this->params['offset']) ? (int)$this->params['offset'] : 0;

    if (isset($this->params['patt']) && strlen($this->params['patt']) > 0) {
      $whereClause = " WHERE meta.catalog_subscription_email LIKE '%%"
        .$this->params['patt']."%%'";
    } else {
      $whereClause = '';
    }

    /*
     * Get number of email adresses subscribed:
     */
    $sql = "SELECT count(DISTINCT meta.catalog_subscription_email) AS count
              FROM %s AS meta
        INNER JOIN %s AS s USING(catalog_subscription_id)" . $whereClause;
    $params = array($this->tableSubscriptionsMeta, $this->tableSubscriptions);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $absCount = $row['count'];
      }
    }

    $sql = "SELECT DISTINCT meta.catalog_subscription_email
              FROM %s AS meta
        INNER JOIN %s AS s USING(catalog_subscription_id)" . $whereClause;
    $params = array($this->tableSubscriptionsMeta, $this->tableSubscriptions);
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (checkit::isEmail($row['catalog_subscription_email'], TRUE)) {
          $resultSet[] = $row;
        }
      }
    }
    $result = '';
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHtmlChars($this->_gt('Confirmed Subscribers'))
    );
    $result .= $this->getPagingBar($absCount, $limit, $offset);
    $result .= '<items>'.LF;
    foreach ($resultSet as $resultEntry) {
      if (isset($this->params['catalog_subscription_email'])
          && $resultEntry['catalog_subscription_email'] ===
             $this->params['catalog_subscription_email']) {
        $selected = ' selected="selected" ';
      } else {
        $selected = '';
      }
      $result .= sprintf(
        '<listitem href="%s" title="%s" %s></listitem>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'catalog_subscription_email' => $resultEntry['catalog_subscription_email'],
              'cmd' => 'edit_subscriptions'
            )
          )
        ),
        papaya_strings::escapeHtmlChars($resultEntry['catalog_subscription_email']),
        $selected);
    }
    $result .= '</items></listview>'.LF;
    $this->layout->addLeft($result);
  }

  /**
   * Get paging bar for list.
   *
   * @param integer $absCount Total number of files.
   * @param integer $step Entries per page.
   * @param integer $offset Entry at which selected page starts.
   * @return string $result Buttons xml.
   */
  function getPagingBar($absCount, $step, $offset) {
    $result = '';
    $result = '<buttons>'.LF;
    if ($absCount > $step) {
      $result .= papaya_paging_buttons::getPagingButtons(
        $this, array(), $offset, $step, $absCount, 9, 'offset', 'left'
      );
    }
    $result .= '</buttons>'.LF;
    return $result;
  }

  /**
   * Get XML for subscriptions for editing. That is an overview of subscribed
   * catalogs per selected email address. Editing currently is deletion in
   * total.
   */
  function getXMLSubscriptionEdit() {
    if (isset($this->params['catalog_subscription_email'])
        && $this->params['catalog_subscription_email'] !== '') {
      $result = '';
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHtmlChars(
          $this->_gt('Subscriber details for ')
          .$this->params['catalog_subscription_email']
        )
      );
      $result .= '<cols>'.LF;
      $result .= '<col>Title</col>'.LF;
      $result .= '<col>Catalog id</col>'.LF;
      $result .= '<col>Subscription id</col>'.LF;
      $result .= '</cols>'.LF;

      $result .= '<items>'.LF;
      $email = $this->params['catalog_subscription_email'];
      $condition = str_replace(
        "%",
        "%%",
        $this->databaseGetSQLCondition('meta.catalog_subscription_email', $email)
      );
      if ($condition != '') {
        $condition = ' WHERE ' . $condition;
      }
      $sql = "SELECT DISTINCT meta.catalog_subscription_email,
                              s.catalog_id, s.catalog_subscription_id,
                              trans.catalog_title
                FROM %s AS meta
          INNER JOIN %s AS s USING(catalog_subscription_id)
          INNER JOIN %s AS trans USING(catalog_id, lng_id)
                     $condition";
      $params = array(
        $this->tableSubscriptionsMeta,
        $this->tableSubscriptions,
        $this->tableCatalogTrans
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $subitems = '';
          $subitems .= sprintf(
            '<subitem>%s</subitem>'.LF,
            papaya_strings::escapeHtmlChars($row['catalog_id'])
          );
          $subitems .= sprintf(
            '<subitem>%s</subitem>'.LF,
            papaya_strings::escapeHtmlChars($row['catalog_subscription_id'])
          );
          $link = $this->getLink(
            array(
              'cmd' => 'delete_subscriptions',
              'catalog_subscription_email' => $row['catalog_subscription_id']
            ),
            $this->paramName
          );

          $result .= sprintf(
            '<listitem title="%s">%s</listitem>'.LF,
            papaya_strings::escapeHtmlChars($row['catalog_title']),
            $subitems
          );
        }
      }
      $result .= '</items></listview>'.LF;
      $result .= $this->getDeleteSubscriptionsForm();
      $this->layout->add($result);
    }
  }

  /**
  * Get and add delete subscriptions dialog.
  *
  */
  function getDeleteSubscriptionsForm() {
    if (!isset($this->params['catalog_subscription_email']) ||
         $this->params['cmd'] != 'delete_subscriptions' ||
         isset($this->params['confirm_delete'])) {
      return;
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'delete_subscriptions',
      'confirm_delete' => 1,
      'catalog_subscription_email' => $this->params['catalog_subscription_email']
    );
    $msg = sprintf(
      $this->_gt('Really delete all subscriptions of "%s"?'),
      $this->params['catalog_subscription_email']
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    if (is_object($dialog)) {
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
   * Delete all catalog subscriptions of a (via backend) selected email
   * address.
   * @return boolean Success Status.
   */
  function deleteCatalogSubscriptions() {
    $email = $this->params['catalog_subscription_email'];
    $condition = str_replace(
      "%",
      "%%",
      $this->databaseGetSQLCondition('catalog_subscription_email', $email)
    );
    if ($condition != '') {
      $condition = ' WHERE ' . $condition;
    }
    $sql = "DELETE FROM %s
                  WHERE catalog_subscription_id IN
                        (SELECT catalog_subscription_id
                           FROM %s
                           $condition)";
    $params = array($this->tableSubscriptions, $this->tableSubscriptionsMeta);
    if (FALSE !== ($resCount = $this->databaseQueryFmtWrite($sql, $params))) {
      if ($resCount !== TRUE) {
        $this->addMsg(
          MSG_INFO,
          sprintf($this->_gt('Removed %s subscriptions.'), $resCount)
        );
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  *
  * XML for topic id list on Left side
  *
  * @access public
  */
  function getXMLSynonymList() {
    $catalogId = $this->params['catalog_id'];
    $this->loadCatalogLinkList(
      $catalogId, $this->lngSelect->currentLanguageId, 0, FALSE
    );
    if (isset($this->linkList[$catalogId]) && is_array($this->linkList[$catalogId])) {
      $result = sprintf(
        '<listview title="%s [%s]" >'.LF,
        papaya_strings::escapeHtmlChars($this->_gt('Catalog links')),
        papaya_strings::escapeHtmlChars($this->lngSelect->currentLanguage['lng_title'])
      );
      $result .= '<items>'.LF;
      $counter = 0;
      $max = count($this->linkList[$catalogId]) - 1;
      foreach ($this->linkList[$catalogId] as $data) {
        if (isset($data['topic_id'])) {
          if (isset($this->params) && isset($this->params['topic_id'])) {
            $selected = ((int)$this->params['topic_id'] == $data['topic_id'])
              ? ' selected="selected"' : '';
          } else {
            $selected = '';
          }
          if (!isset($data['cataloglink_title']) || $data['cataloglink_title'] == '') {
            $title = $this->_gt('No title');
          } else {
            $title = $data['cataloglink_title'];
          }
          $imgIndex = ($data['topic_exists']) ? 'items-page' : 'status-page-warning';
          $result .= sprintf(
            '<listitem href="%s" title="%s" image="%s" %s>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('topic_id' => (int)$data['topic_id']))
            ),
            papaya_strings::escapeHtmlChars($title),
            papaya_strings::escapeHTMLChars($this->images[$imgIndex]),
            $selected
          );
          $result .= '<subitem>#'.(int)$data['topic_id'].'</subitem>';
          if ($counter > 0) {
            $result .= sprintf(
              '<subitem><a href="%s"><glyph src="%s" hint="%s" /></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'cmd' => 'move_up',
                    'cataloglink_id' => (int)$data['cataloglink_id']
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($this->images['actions-go-up']),
              papaya_strings::escapeHtmlChars($this->_gt('Up'))
            );
          } else {
            $result .= '<subitem/>';
          }
          if ($counter < $max) {
            $result .= sprintf(
              '<subitem><a href="%s"><glyph src="%s" hint="%s" /></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'cmd' => 'move_down',
                    'cataloglink_id' => (int)$data['cataloglink_id']
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($this->images['actions-go-down']),
              papaya_strings::escapeHtmlChars($this->_gt('Down'))
            );
          } else {
            $result .= '<subitem/>';
          }
          $result .= '</listitem>';
          $counter++;
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addRight($result);
    }
  }

  /**
  *
  * Get XML for buttons
  *
  * @access public
  */
  function getXMLButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;
    switch (@$this->params['mode']) {
    case 1:
      $toolbar->addButton(
        'Catalog',
        $this->getLink(array('mode' => '0')),
        'items-folder',
        'Categories and Entries'
      );
      if ($this->module->hasPerm(2)) {
        $toolbar->addButton(
          'Subscriptions',
          $this->getLink(array('mode' => '2')),
          'items-option',
          'Manage Subscriptions'
        );
        $toolbar->addButton(
          'Options',
          $this->getLink(array('mode' => '1')),
          'items-option',
          'Category Types',
          TRUE
        );
        $toolbar->addSeparator();
        $toolbar->addButton(
          'Add type',
          $this->getLink(
            array('cmd' => 'add_type', 'type_id' => 0)
          ),
          'actions-option-add'
        );
        if (isset($this->catalogTypes) &&
            isset($this->params['type_id']) &&
            isset($this->catalogTypes[$this->params['type_id']])) {
          $toolbar->addButton(
            'Delete type',
            $this->getLink(
              array(
                'cmd' => 'del_type',
                'type_id' => @(int)$this->params['type_id']
              )
            ),
            'actions-option-delete'
          );
        }
      }
      break;
    case 2:
      if ($this->module->hasPerm(2)) {
        $toolbar->addButton(
          'Catalog',
          $this->getLink(array('mode' => '0')),
          'items-folder',
          'Categories and Entries'
        );
        $toolbar->addButton(
          'Subscriptions',
          $this->getLink(array('mode' => '2', 'patt' => '')),
          'items-option',
          'Manage Subscriptions',
          TRUE
        );
        $toolbar->addButton(
          'Options',
          $this->getLink(array('mode' => '1')),
          'items-option',
          'Category Types'
        );
        if (isset($this->params['cmd'])
            && isset($this->params['catalog_subscription_email'])) {
          $toolbar->addSeparator();
          if ($this->params['cmd'] === 'edit_subscriptions') {
            $toolbar->addButton(
              'Delete',
              $this->getLink(
                array(
                  'mode' => '2',
                  'cmd' => 'delete_subscriptions'
                )
              ),
              'actions-generic-delete',
              'Delete subscriptions of this user'
            );
          } elseif ($this->params['cmd'] === 'delete_subscriptions') {
            if (! isset($this->params['confirm_delete'])
                || ! (bool)$this->params['confirm_delete']) {
              $toolbar->addButton(
                'Cancel',
                $this->getLink(
                  array(
                    'mode' => '2',
                    'cmd' => 'list_subscriptions'
                  )
                ),
                'actions-go-previous',
                'Go back.'
              );
            }
          }
        }
      }
      break;
    case 0:
    default :
      if ($this->module->hasPerm(2)) {
        $toolbar->addButton(
          'Catalog',
          $this->getLink(array('mode' => '0')),
          'items-folder',
          'Categories and Entries',
          TRUE
        );
        $toolbar->addButton(
          'Subscriptions',
          $this->getLink(array('mode' => '2')),
          'items-option',
          'Manage Subscriptions'
        );
        $toolbar->addButton(
          'Options',
          $this->getLink(array('mode' => '1')),
          'items-option',
          'Category Types'
        );
      }
      $toolbar->addSeparator();
      $toolbar->addButton(
        'Check index',
        $this->getLink(array('cmd' => 'regenerate')),
        'actions-tree-scan'
      );
      $toolbar->addSeperator();
      if (isset($this->params) && isset($this->params['catalog_id'])) {
        $toolbar->addButton(
          'Add category',
          $this->getLink(
            array(
              'cmd' => 'add_catalog',
              'catalog_id' => @(int)$this->catalog['catalog_parent']
            )
          ),
          'actions-folder-add'
        );
      }
      if (isset($this->params) && isset($this->params['catalog_id']) &&
          $this->params['catalog_id'] != 0 ) {
        $toolbar->addSeparator();
        $toolbar->addButton(
          'Delete category',
          $this->getLink(
            array(
              'cmd' => 'del_catalog',
              'catalog_id' => @(int)$this->params['catalog_id']
            )
          ),
          'actions-folder-delete'
        );
        $toolbar->addButton(
          'Cut category',
          $this->getLink(
            array(
              'cmd' => 'cut_catalog',
              'catalog_id' => @(int)$this->params['catalog_id']
            )
          ),
          'actions-edit-cut'
        );
        $toolbar->addButton(
          'Add subcategory',
          $this->getLink(
            array(
              'cmd' => 'add_catalog',
              'catalog_id' => @(int)$this->params['catalog_id']
            )
          ),
          'actions-folder-child-add'
        );
        if (isset($this->catalogDetail)) {
          $toolbar->addSeparator();
          $toolbar->addButton(
            'Add Link',
            $this->getLink(
              array(
                'cmd' => 'add_topic',
                'catalog_id' => @(int)$this->params['catalog_id']
              )
            ),
            'actions-link-add'
          );
        }
      }
      if (isset($this->params) &&
          isset($this->params['topic_id'])) {
        $toolbar->addButton(
          'Delete Link',
          $this->getLink(
            array(
              'cmd' => 'del_topic',
              'topic_id' => @(int)$this->params['topic_id'],
              'catalog_id' => @(int)$this->params['catalog_id']
            )
          ),
          'actions-link-delete'
        );
      }
      break;
    }
    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $str));
    }
  }

  /**
  * Get XML for topic edit form
  *
  * @access public
  */
  function getXMLTopicForm() {
    if (isset($this->params['topic_id']) && isset($this->params['catalog_id'])) {
      $this->loadTopicDetail($this->params['catalog_id'], $this->params['topic_id']);
      $this->initializeTopicEditForm();
      if (is_object($this->_topicEditForm)) {
        $this->layout->add($this->_topicEditForm->getDialogXML());
      }
    }
  }

  /**
  * Initial funtion for topic edit form
  *
  * @todo use base_dialog to implement this function and
  *       use type pageid for topic selection
  * @access public
  */
  function initializeTopicEditForm() {
    if (!is_object($this->_topicEditForm)) {
      $fields = array(
        'new_topic_id' => array(
          'Topic id',
          'isNum',
          FALSE,
          'pageid',
          10,
          '',
          $this->params['topic_id']
        ),

      );
      $hidden = array(
        'cmd' => 'topiclinks_edit',
        'topic_id' => $this->params['topic_id'],
        'catalog_id' => $this->params['catalog_id']
      );
      $data = array();
      foreach ($this->lngSelect->languages as $lngId => $lng) {
        $fields[] = $lng['lng_title'];
        if (isset($this->topicDetailLngGroups[$lngId])) {
          foreach ($this->topicDetailLngGroups[$lngId] as $catalogLinkId => $title) {
            $fields['cataloglink_titles_'.$catalogLinkId] = array(
              'Link',
              'isNoHTML',
              FALSE,
              'input',
              255,
              'Delete text to remove link'
            );
            $data['cataloglink_titles_'.$catalogLinkId] = $title['cataloglink_title'];
          }
        }
        $fields['cataloglink_titles_new_'.$lngId] = array(
          'Add',
          'isNoHTML',
          FALSE,
          'input',
          255
        );
      }
      $this->_topicEditForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      if (is_object($this->_topicEditForm)) {
        $this->_topicEditForm->dialogTitle = $this->_gt('Edit topic links');
        $this->_topicEditForm->buttonTitle = $this->_gt('Save');
        $this->_topicEditForm->dialogMethod = 'post';
      }
    }
  }

  /**
  * Get XML for topic add form
  *
  * @access public
  */
  function getXMLTopicAddForm() {
    if (isset($this->params['catalog_id'])) {
      $this->initializeTopicAddForm();
      $this->layout->add($this->catalogDialog->getDialogXML());
    }
  }

  /**
  * Initialize add topic to catalog form
  *
  * @access public
  */
  function initializeTopicAddForm() {
    if (!(isset($this->catalogDialog) && is_object($this->catalogDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = array('topic_id' => (int)$this->sessionPageParams['page_id']);
      $hidden = array(
        'cmd' => 'add_topic', 'save' => 1,
        'catalog_id' => $this->params['catalog_id'],
        'lng_id' => $this->lngSelect->currentLanguageId,
        'submited' => TRUE
      );
      $fields = array(
        'topic_id' => array('Topic id', 'isNum', TRUE, 'pageid', 250)
      );
      $this->catalogDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->catalogDialog->dialogTitle = $this->_gt('Add');
      $this->catalogDialog->msgs = &$this->msgs;
      $this->catalogDialog->loadParams();
    }
  }

  /**
  * Initialize catalog edit form
  *
  * @access public
  */
  function initializeCatalogEditForm() {
    if (!(isset($this->dialogCatalog) && is_object($this->dialogCatalog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      if (isset($this->catalogDetail) &&
          is_array($this->catalogDetail)) {
        $data = $this->catalogDetail;
        $hidden = array(
          'cmd' => 'edit_catalog_detail',
          'save' => 1,
          'catalog_id' => $this->catalog['catalog_id']
        );
        $btnCaption = 'Edit';
      } else {
        $data = array();
        $hidden = array(
           'cmd' => 'create_catalog_detail',
           'save' => 1,
           'catalog_id' => $this->catalog['catalog_id']
        );
        $btnCaption = 'Save';
      }
      $catalogTypes = array();
      if (isset($this->catalogTypes) &&
          is_array($this->catalogTypes) && count($this->catalogTypes)) {
        foreach ($this->catalogTypes as $catalogType) {
          $catalogTypes[$catalogType['catalogtype_id']] =
            $catalogType['catalogtype_title'].' ['.$catalogType['catalogtype_name'].']';
        }
      }
      $fields = array(
        'catalog_title' => array('Title', 'isNoHTML', TRUE, 'input', 250),
        'catalog_glyph' => array('Glyph', 'isSomeText', FALSE, 'input', 200),
        'catalog_image' => array('Image', 'isSomeText', FALSE, 'imagefixed', 200),
        'catalog_text' => array('Text', 'isSomeText', FALSE, 'simplerichtext', 12),
        'catalogtype_id' => array('Type', 'isNum', FALSE, 'combo', $catalogTypes)
      );
      $this->dialogCatalog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogCatalog->msgs = &$this->msgs;
      $this->dialogCatalog->loadParams();
      $this->dialogCatalog->baseLink = $this->baseLink;
      $this->dialogCatalog->dialogTitle = $this->_gt('Properties');
      $this->dialogCatalog->buttonTitle = $btnCaption;
      $this->dialogCatalog->dialogDoubleButtons = FALSE;
    }
  }

  /**
  * Get XML for catalog edit form
  *
  * @access public
  */
  function getXMLCatalogEditForm() {
    if (isset($this->catalog)) {
      $this->loadCatalogTypes();
      $this->initializeCatalogEditForm();
      $this->layout->add($this->dialogCatalog->getDialogXML());
    }
  }

  /**
  * get XML for catalog synonym form
  *
  * @access public
  */
  function getXMLCatalogSynonymForm() {
    if (isset($this->catalog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $this->loadCatalogSynonyms($this->params['catalog_id']);
        $hidden = array(
          'catalog_id' => $this->params['catalog_id'],
          'contentmode' => $this->params['contentmode'],
        );
      if (isset($this->params['cmd']) &&
          $this->params['cmd'] == 'edit_synonym' &&
          $this->params['catalogsynonym_id'] != '') {
        $data['catalogsynonym_title'] =
          $this->catalogSynonyms[$this->params['catalogsynonym_id']];
        $actionTitle = 'save';
        $hidden['cmd'] = 'save_synonym';
        $hidden['confirmedit'] = 1;
        $hidden['catalogsynonym_id'] = $this->params['catalogsynonym_id'];
      } else {
        $actionTitle = 'add';
        $hidden['cmd'] = 'add_synonym';
      }
      $fields = array(
        'catalogsynonym_title' => array('Synonym', 'isNoHTML', TRUE, 'input', 250),
      );

      $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $dialog->msgs = &$this->msgs;
      $dialog->loadParams();
      unset($dialog->params['catalogsynonym_title']);
      $dialog->baseLink = $this->baseLink;
      $dialog->dialogTitle = papaya_strings::escapeHtmlChars($this->_gt($actionTitle));
      $dialog->buttonTitle = $actionTitle;
      $this->layout->add($this->getXMLCatalogSynonymsList().$dialog->getDialogXML());
    }
  }

  /**
  * get XML for delete synonym dialog
  *
  * @access public
  */
  function getXMLDelSynonymDialog() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'del_synonym',
      'contentmode' => $this->params['contentmode'],
      'catalogsynonym_id' => $this->params['catalogsynonym_id'],
      'confirmdelete'=>1,
    );
    $msg = sprintf(
      $this->_gt('Delete this synonym (%d)?'), $this->params['catalogsynonym_id']
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->buttonTitle = 'Delete';
    $this->layout->add($dialog->getMsgDialog());
  }

  /**
  * get XML for catalog synonyms list
  *
  * @access public
  */
  function getXMLCatalogSynonymsList() {
    $result = '';
    if (isset($this->catalogSynonyms)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Synonyms'))
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Title'))
      );
      $result .= '<col></col><col></col>'.LF;
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      foreach ($this->catalogSynonyms as $id => $title) {
        $result .= sprintf(
          '<listitem title="%s">'.LF,
          papaya_strings::escapeHTMLChars($title)
        );
        $editLink = $this->getLink(
          array(
            'cmd' => 'edit_synonym',
            'catalogsynonym_id' => $id,
            'contentmode' => $this->params['contentmode'])
          );
        $result .= sprintf(
          '<subitem><a href="%s"><glyph src="%s" hint="%s" /></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($editLink),
          papaya_strings::escapeHTMLChars($this->images['actions-edit']),
          papaya_strings::escapeHTMLChars($this->_gt('edit'))
        );
        $delLink = $this->getLink(
          array(
            'cmd' => 'del_synonym',
            'catalogsynonym_id' => $id,
            'contentmode' => $this->params['contentmode']
          )
        );
        $result .= sprintf(
          '<subitem><a href="%s"><glyph src="%s" hint="%s" /></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($delLink),
          papaya_strings::escapeHTMLChars($this->images['places-trash']),
          papaya_strings::escapeHTMLChars($this->_gt('delete'))
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }


  /**
  * Delete catalog form
  *
  * @access public
  */
  function getXMLDelCatalogForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'del_catalog',
      'catalog_id' => $this->params['catalog_id'],
      'confirm_delete' => 1
    );

    $catalogId = $this->params['catalog_id'];
    $msg = sprintf(
      $this->_gt('Delete category "%s" (%d)?'),
      $this->catalogs[(int)$catalogId]['catalog_title'],
      (int)$catalogId
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->baseLink = $this->baseLink;
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    $this->layout->add($dialog->getMsgDialog());
  }

  /**
  * Delete topic form
  *
  * @access public
  */
  function getXMLDelTopicForm() {
    $this->loadTopicList($this->params['catalog_id']);
    if (isset($this->topicList) && is_array($this->topicList) &&
        isset($this->topicList[$this->params['topic_id']])) {

      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_topic',
        'catalog_id' => $this->params['catalog_id'],
        'topic_id' => $this->params['topic_id'],
        'confirm_delete' => 1
      );

      $msg = sprintf(
        $this->_gt('Do you really want to delete the link to page id "%d"?'),
        (int)$this->params['topic_id']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Initialize catalog detail edit form
  *
  * @access public
  */
  function initializeCatalogsEditForm() {
    if (!(isset($this->catalogDialog) && is_object($this->catalogDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->catalog;
      $hidden = array(
        'cmd' => 'edit_catalog',
        'save' => 1,
        'catalog_id' => $this->params['catalog_id']
      );
      $fields = array(
      );
      $this->catalogDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->catalogDialog->msgs = &$this->msgs;
      $this->catalogDialog->loadParams();
    }
  }

  /**
  * Add topic
  *
  * @access public
  */
  function addTopic() {
    $newIds = NULL;
    $this->initializeTopicAddForm();
    if (isset($this->params['submited']) &&
        $this->params['submited'] == TRUE) {
      if ($this->catalogDialog->checkDialogInput()) {
        if (!$this->topicIsLinked(
            @(int)$this->params['topic_id'], @(int)$this->params['catalog_id'])) {
          $newIds = $this->addTopicTitle();
          if (is_array($newIds)) {
            $this->addMsg(MSG_INFO, $this->_gt('Link added.'));
            unset($this->params['cmd']);
          } elseif (isset($newIds) && $newIds == FALSE ) {
            $this->addMsg(MSG_ERROR, $this->_gt('Page content not found.'));
            $this->params['topic_id'] = NULL;
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Page not found.'));
            $this->params['topic_id'] = NULL;
          }
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('This page is already linked with the current category.')
          );
        }
      }
    }
  }

  /**
  * get XML for catalog types list
  *
  * @access public
  */
  function getXMLCatalogTypes() {
    if (isset($this->catalogTypes) && is_array($this->catalogTypes) &&
        count($this->catalogTypes) > 0) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Category types'))
      );
      $result .= '<items>';
      foreach ($this->catalogTypes as $typeId => $type) {
        $selected = (@$this->params['type_id'] == $typeId) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<listitem href="%s" title="%s" image="%s"%s/>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cmd' => 'sel_type', 'type_id' => $typeId))
          ),
          papaya_strings::escapeHTMLChars($type['catalogtype_title']),
          papaya_strings::escapeHTMLChars($this->images['items-option']),
          $selected
        );
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addLeft($result);
    }
  }

  /**
  * Initialize add category type form
  *
  * @access public
  */
  function initializeCatalogTypeAddForm() {
    if (!(isset($this->catalogTypeDialog) && is_object($this->catalogTypeDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = array();
      $hidden = array(
        'cmd' => 'add_type',
        'save' => 1,
        'type_id' => 0,
        'lng_id' => $this->lngSelect->currentLanguageId
      );
      $fields = array(
        'catalogtype_title' =>
          array('Title', 'isSomeText', TRUE, 'input', 250),
        'catalogtype_name' =>
          array('Name', 'isSomeText', TRUE, 'input', 50),
        'Always Load',
        'catalogtype_loadlinks' =>
          array('Links', 'isNum', TRUE, 'yesno', '', '', FALSE),
        'catalogtype_loadteaser' =>
          array('Teaser', 'isNum', TRUE, 'yesno', '', '', FALSE),
      );
      $this->catalogTypeDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->catalogTypeDialog->dialogTitle = $this->_gt('Add');
      $this->catalogTypeDialog->msgs = &$this->msgs;
      $this->catalogTypeDialog->loadParams();
    }
  }

  /**
  * Get XML for catalog edit form
  *
  * @access public
  */
  function getXMLCatalogTypeAddForm() {
    $this->initializeCatalogTypeAddForm();
    $this->layout->add($this->catalogTypeDialog->getDialogXML());
  }

  /**
  * Initialize the catalog entry type edit dialog
  * @return void
  */
  function initializeCatalogTypeEditForm() {
    if (!(isset($this->catalogTypeDialog) && is_object($this->catalogTypeDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->catalogTypes[$this->params['type_id']];
      $hidden = array(
        'cmd' => 'edit_type',
        'save' => 1,
        'type_id' => (int)$this->params['type_id'],
        'lng_id' => $this->lngSelect->currentLanguageId
      );
      $fields = array(
        'catalogtype_title' => array('Title', 'isSomeText', TRUE, 'input', 250),
        'catalogtype_name' => array('Name', 'isSomeText', TRUE, 'input', 50),
        'Always Load',
        'catalogtype_loadlinks' => array('Links', 'isNum', TRUE, 'yesno'),
        'catalogtype_loadteaser' => array('Teaser', 'isNum', TRUE, 'yesno'),
      );
      $this->catalogTypeDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->catalogTypeDialog->dialogTitle = $this->_gt('Edit');
      $this->catalogTypeDialog->msgs = &$this->msgs;
      $this->catalogTypeDialog->loadParams();
    }
  }

  /**
  * Get XML for catalog edit form
  *
  * @access public
  */
  function getXMLCatalogTypeEditForm() {
    if (isset($this->catalogTypes) &&
        isset($this->catalogTypes[$this->params['type_id']])) {
      $this->initializeCatalogTypeEditForm();
      $this->layout->add($this->catalogTypeDialog->getDialogXML());
    }
  }


  /**
  * Delete catalog type form
  *
  * @access public
  */
  function getXMLDelCatalogTypeForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'del_type',
      'type_id' => $this->params['type_id'],
      'confirm_delete' => 1
    );

    $msg = sprintf(
      $this->_gt('Delete category type "%s" (%d)?'),
      $this->catalogTypes[$this->params['type_id']]['catalogtype_title'],
      (int)$this->params['type_id']
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->baseLink = $this->baseLink;
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    $this->layout->add($dialog->getMsgDialog());
  }

  /**
  * add catalog type record to database
  * @return boolean
  */
  function addCatalogType() {
    $data = array(
      'catalogtype_title' => (string)$this->params['catalogtype_title'],
      'catalogtype_name' => (string)$this->params['catalogtype_name'],
      'catalogtype_loadlinks' => (int)$this->params['catalogtype_loadlinks'],
      'catalogtype_loadteaser' => (int)$this->params['catalogtype_loadteaser'],
    );
    return $this->databaseInsertRecord(
      $this->tableCatalogTypes, 'catalogtype_id', $data
    );
  }

  /**
  * save catalog type record to database
  * @return boolean
  */
  function saveCatalogType() {
    $data = array(
      'catalogtype_title' => (string)$this->params['catalogtype_title'],
      'catalogtype_name' => (string)$this->params['catalogtype_name'],
      'catalogtype_loadlinks' => (int)$this->params['catalogtype_loadlinks'],
      'catalogtype_loadteaser' => (int)$this->params['catalogtype_loadteaser']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableCatalogTypes, $data, 'catalogtype_id', $this->params['type_id']
    );
  }

  /**
  * delete catalog type record from database
  * @return boolean
  */
  function deleteCatalogType() {
    return FALSE !== $this->databaseDeleteRecord(
      $this->tableCatalogTypes, 'catalogtype_id', $this->params['type_id']
    );
  }

  /**
  * Add catalog
  *
  * @param integer $parent
  * @access public
  * @return boolean
  */
  function addCatalog($parent) {
    $this->loadCatalog($this->params['catalog_id']);
    if ($this->catalogExist($parent) || ($parent == 0)) {
      if (isset($this->catalog) && is_array($this->catalog)) {
        $path = $this->catalog['catalog_parent_path'].$this->catalog['catalog_id'].';';
      } else {
        $path = ';0;';
      }
      $data = array(
        'catalog_parent' => @(int)$parent,
        'catalog_parent_path' => $path
      );
      unset($this->catalog);
      return $this->databaseInsertRecord($this->tableCatalog, 'catalog_id', $data);
    }
    return FALSE;
  }

  /**
  * Create dialog detail with empty data
  *
  * @access public
  * @return boolean
  */
  function createDialogDetail($values) {
    $data = array(
      'catalog_id' => $this->catalog['catalog_id'],
      'lng_id' => $this->lngSelect->currentLanguageId,
      'catalog_title' => (string)$values['catalog_title'],
      'catalog_glyph' => (string)$values['catalog_glyph'],
      'catalog_image' => (string)$values['catalog_image'],
      'catalog_text' => empty($values['catalog_text']) ? '' : $values['catalog_text']
    );
    return (FALSE !== $this->databaseInsertRecord($this->tableCatalogTrans, NULL, $data));
  }

  /**
  * Save category details
  *
  * @access public
  * @return integer|FALSE
  */
  function saveCatalogDetail($values) {
    $dataTrans = array(
      'catalog_title' => (string)$values['catalog_title'],
      'catalog_glyph' => (string)$values['catalog_glyph'],
      'catalog_image' => (string)$values['catalog_image'],
      'catalog_text' => empty($values['catalog_text']) ? '' : $values['catalog_text']
    );
    $filter = array(
      'catalog_id' => (int)$this->catalog['catalog_id'],
      'lng_id' => $this->lngSelect->currentLanguageId
    );
    if (FALSE !== $this->databaseUpdateRecord(
          $this->tableCatalogTrans, $dataTrans, $filter)) {
      $data = array(
        'catalogtype_id' => isset($values['catalogtype_id'])
           ? $this->params['catalogtype_id'] : 0
      );
      return FALSE !== $this->databaseUpdateRecord(
        $this->tableCatalog, $data, 'catalog_id', (int)$this->catalog['catalog_id']
      );
    }
    return FALSE;
  }

  /**
  * function to paste (update) catalog
  *
  * @access public
  */
  function pasteCatalog() {
    $this->catalogsOpen[(int)$this->params['catalog_id']] = TRUE;
    $catalog = NULL;
    $sql = "SELECT catalog_parent_path, catalog_id, catalog_parent
              FROM %s
             WHERE catalog_id = %d
                OR catalog_id = %d";
    $params = array(
      $this->tableCatalog, $this->params['catalog_id'], $this->params['paste_id']);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $catalog[$row['catalog_id']] = $row;
      }
    }
    if (isset($catalog) && is_array($catalog)) {
      $oldPath = $catalog[$this->params['paste_id']]['catalog_parent_path'];
      $strLength = strlen($oldPath) + 1;
      $newPath = ';0;'.$catalog[$this->params['catalog_id']]['catalog_id'].";";
      $newPath = str_replace(';;', ';', $newPath);
      $sqlReplace = $this->databaseGetSQLSource(
        'CONCAT',
        $newPath,
        TRUE,
        $this->databaseGetSQLSource(
          'SUBSTRING',
          'catalog_parent_path',
          FALSE,
          $strLength,
          TRUE
        ),
        FALSE
      );
      $sql = "UPDATE %s
                 SET catalog_parent_path = ".$sqlReplace."
               WHERE catalog_parent = '%d'
                  OR catalog_parent_path LIKE '%s%%'";
      $params = array(
        $this->tableCatalog, $this->params['paste_id'], $oldPath
      );
      if (!(FALSE !== $this->databaseQueryFmtWrite($sql, $params))) {
        $this->addMsg(MSG_WARNING, $this->_gt('Could not move subpages!'));
      }
      $path = NULL;
      if ($this->params['catalog_id'] != 0) {
        $path = $catalog[$this->params['catalog_id']]['catalog_parent_path']
          .$catalog[$this->params['catalog_id']]['catalog_id'].';';
      } else {
        $path = ';0;';
      }
      $data = array(
        'catalog_parent' => $catalog[$this->params['catalog_id']]['catalog_id'],
        'catalog_parent_path' => $path,
      );
      $condition = array('catalog_id' => $this->params['paste_id']);
      if (FALSE === $this->databaseUpdateRecord($this->tableCatalog, $data, $condition)) {
        $this->addMsg(MSG_WARNING, $this->_gt('Database inconsistency!'));
      }
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Catalog does not exist!'));
    }
  }

  /**
  * cut catalog to clipboard
  *
  * @param integer $catalogId
  * @access public
  */
  function cutToClipboard($catalogId) {
    $catalog = NULL;
    if ($catalogId > 0) {
      $sql = "SELECT catalog_parent_path, catalog_id, catalog_parent
                FROM %s
               WHERE catalog_id = %d";
      $params = array( $this->tableCatalog, $catalogId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $catalog = $row;
        }
      }
      $oldPath = $catalog['catalog_parent_path'].$catalog['catalog_id'].";";
      $strLength = strlen($oldPath) + 1;
      $newPath = ';-1;'.$catalog['catalog_id'].";";
      $newPath = str_replace(';;', ';', $newPath);
      $sqlReplace = $this->databaseGetSQLSource(
        'CONCAT',
        $newPath,
        TRUE,
        $this->databaseGetSQLSource(
          'SUBSTRING',
          'catalog_parent_path',
          FALSE,
          $strLength,
          TRUE
        ),
        FALSE
      );
      $sql = "UPDATE %s
                 SET catalog_parent_path = ".$sqlReplace."
               WHERE catalog_parent = '%d'
                  OR catalog_parent_path LIKE '%s%%'";
      $params = array(
        $this->tableCatalog, $catalogId, $oldPath
      );
      if (!(FALSE !== $this->databaseQueryFmtWrite($sql, $params))) {
        $this->addMsg(MSG_WARNING, $this->_gt('Could not move subpages!'));
      }
      $data = array(
        'catalog_parent' => -1,
        'catalog_parent_path' => ';-1;',
      );
      $condition = array('catalog_id' => $catalog['catalog_id']);
      if (FALSE === $this->databaseUpdateRecord($this->tableCatalog, $data, $condition)) {
        $this->addMsg(MSG_WARNING, $this->_gt('Database inconsistency!'));
      }
    } else {
      $this->addMsg(MSG_WARNING, $this->_gt('You cannot cut out this item.'));
      $this->params['cmd'] = '';
    }
  }

  /**
  * Load topic list
  *
  * @param integer $catalogId
  * @access public
  */
  function loadTopicList($catalogId) {
    unset($this->topicList);
    $filter = '';
    $filter = NULL;
    $sql = "SELECT DISTINCT cl.topic_id, t.topic_id AS topictable_id, tt.topic_title
              FROM %s cl
              LEFT OUTER JOIN %s t ON (t.topic_id = cl.topic_id)
              LEFT OUTER JOIN %s tt ON (tt.topic_id = cl.topic_id AND tt.lng_id = %d)
             WHERE cl.catalog_id = %d
             ORDER BY tt.topic_title, t.topic_id";
    $params = array($this->tableCatalogLinks, $this->tableTopics,
      $this->tableTopicsTrans, $this->lngSelect->currentLanguageId, $catalogId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['exists'] = (isset($row['topictable_id'])) ? TRUE : FALSE;
        $this->topicList[$row['topic_id']] = $row;
      }
    }
  }

  /**
  * adds the catalog title to topic list
  *
  * @access public
  * @return boolean
  */
  function addTopicTitle() {
    $titles = NULL;
    $ret = NULL;
    $sql = "SELECT topic_title, lng_id
              FROM %s
             WHERE topic_id = %d";
    $params = array( $this->tableTopicsTrans, $this->params['topic_id'] );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['topic_title'] == '') {
          $tmp = $this->_gt('No title');
        } else {
          $tmp = $row['topic_title'];
        }
        $titles[$row['lng_id']] = $tmp;
      }
    }

    if (is_array($titles)) {
      foreach ($titles as $id => $value) {
        $data = array(
          'topic_id' => $this->params['topic_id'],
          'catalog_id' => $this->params['catalog_id'],
          'lng_id' => $id,
          'cataloglink_title' => $value
        );
        $ret[$id] = $this->databaseInsertRecord(
          $this->tableCatalogLinks, 'cataloglink_id', $data);
      }
    } else {
      $sql = "SELECT topic_id
                FROM %s
               WHERE topic_id = %d";
      $params = array( $this->tableTopics, $this->params['topic_id'] );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $ret = FALSE;
        }
      }
    }
    return $ret;
  }

  /**
  * Load details of topic
  *
  * @param integer $catalogId
  * @param integer $topicId
  * @access public
  */
  function loadTopicDetail($catalogId, $topicId) {
    unset($this->topicDetail);
    unset($this->topicDetailLngGroups);

    $filter = $this->databaseGetSQLCondition(
      'lng_id', @array_keys($this->lngSelect->languages)
    );
    $sql = "SELECT cataloglink_id, catalog_id, topic_id, lng_id,
                    cataloglink_title, cataloglink_sort
              FROM %s
             WHERE topic_id = %d
               AND $filter
               AND catalog_id = %d";
    $params = array( $this->tableCatalogLinks, $topicId, $catalogId);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->topicDetail[$row['cataloglink_id']] = array(
          'cataloglink_title' => $row['cataloglink_title'],
          'cataloglink_sort' => $row['cataloglink_sort']
        );
        $this->topicDetailLngGroups[$row['lng_id']][$row['cataloglink_id']] =
          &$this->topicDetail[$row['cataloglink_id']];
      }
    }
  }


  /**
  * Save topic via update function
  *
  * @param $id
  * @param $data
  * @access public
  * @return boolean
  */
  function saveTopicDetail($id, $data) {
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableCatalogLinks, $data, array('cataloglink_id' => $id)
    );
  }

  /**
  * Delete topic
  *
  * @param integer $catalog
  * @param integer $topic
  * @access public
  * @return boolean
  */
  function deleteTopic($catalog, $topic) {
    return FALSE !== $this->databaseDeleteRecord(
      $this->tableCatalogLinks,
      array (
        'catalog_id' => $catalog,
        'topic_id' => $topic
      )
    );
  }

  /**
  * Delete catalog
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function deleteCatalog($id) {
    return (
      FALSE !== $this->databaseDeleteRecord(
        $this->tableCatalogLinks, 'catalog_id', $id) &&
      FALSE !== $this->databaseDeleteRecord(
        $this->tableCatalogTrans, 'catalog_id', $id) &&
      FALSE !== $this->databaseDeleteRecord(
        $this->tableCatalog, 'catalog_id', $id)
    );
  }

  /**
  * Load catalogs for catalog tree navigation
  *
  * @access public
  * @return boolean
  */
  function loadCatalogs() {
    unset($this->catalogs);
    unset($this->catalogTree);
    $ids = array(0);
    if (isset($this->catalogsOpen) && is_array($this->catalogsOpen)) {
      foreach ($this->catalogsOpen as $catalogId => $opened) {
        if ($opened) {
          $ids[] = (int)$catalogId;
        }
      }
    }
    if (count($ids) > 1) {
      $filter = " IN ('".implode("', '", $ids)."') ";
    } else {
      $filter = " = '0' ";
    }
    $sql = "SELECT c.catalog_id, c.catalog_parent, ct.lng_id, ct.catalog_title
              FROM %s AS c
              LEFT OUTER JOIN %s AS ct ON (ct.catalog_id=c.catalog_id AND ct.lng_id = '%d')
             WHERE c.catalog_parent $filter
             ORDER BY ct.catalog_title, c.catalog_id DESC";
    $params = array($this->tableCatalog,
                    $this->tableCatalogTrans,
                    $this->lngSelect->currentLanguageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->catalogs[(int)$row['catalog_id']] = $row;
        $this->catalogTree[(int)$row['catalog_parent']][] = $row['catalog_id'];
      }
      $this->loadCatalogCounts();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load catalogs for catalog tree navigation
  *
  * @access public
  * @return boolean
  */
  function loadClipboard() {
    unset($this->clipboard);
    $sql = "SELECT c.catalog_id, c.catalog_parent, ct.lng_id, ct.catalog_title
              FROM %s AS c
              LEFT OUTER JOIN %s AS ct ON (ct.catalog_id=c.catalog_id AND ct.lng_id = '%d')
             WHERE c.catalog_parent = -1
             ORDER BY ct.catalog_title, c.catalog_id DESC";
    $params = array($this->tableCatalog,
                    $this->tableCatalogTrans,
                    $this->lngSelect->currentLanguageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->clipboard[(int)$row['catalog_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * load counts of catalog
  *
  * @param boolean $mode optional, default value TRUE
  * @access public
  * @return boolean
  */
  function loadCatalogCounts($mode = TRUE) {
    $ids = array();
    if (isset($this->catalogsOpen) && is_array($this->catalogsOpen)) {
      if (isset($this->catalogs) && is_array($this->catalogs)) {
        $ids = array_keys($this->catalogs);
      } else {
        $ids = array();
      }
      if (count($ids) > 1) {
        $filter = " IN ('".implode("', '", $ids)."') ";
      } else {
        $filter = " = '".@(int)$ids[0]."' ";
      }
    }
    $sql = "SELECT COUNT(*) AS subcategs, catalog_parent
              FROM %s
             WHERE catalog_parent $filter
             GROUP BY catalog_parent";
    if ($res = $this->databaseQueryFmt($sql, $this->tableCatalog)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->catalogs[(int)$row['catalog_parent']]['CATEG_COUNT'] = $row['subcategs'];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load Catalog types list
  *
  * @access public
  */
  function loadCatalogTypes() {
    unset($this->catalogTypes);
    $sql = "SELECT catalogtype_id, catalogtype_title, catalogtype_name,
                   catalogtype_loadlinks, catalogtype_loadteaser
              FROM %s
             ORDER BY catalogtype_title, catalogtype_name";
    if ($res = $this->databaseQueryFmt($sql, $this->tableCatalogTypes)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->catalogTypes[$row['catalogtype_id']] = $row;
      }
    }
  }

  /**
  * load catalog synonyms
  *
  * @param integer $catalogId
  * @access public
  * @return boolean
  */
  function loadCatalogSynonyms($catalogId) {
    unset($this->catalogSynonyms);
    $sql = "SELECT catalogsynonym_id, catalog_id, catalogsynonym_title
              FROM %s
             WHERE catalog_id = '%d' AND lng_id = '%d'
             ORDER BY catalogsynonym_title ASC";
    $params = array(
      $this->tableCatalogSynonyms, $catalogId, $this->lngSelect->currentLanguageId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->catalogSynonyms[$row['catalogsynonym_id']] = $row['catalogsynonym_title'];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * load catalog link list
  *
  * @param integer  $catalogIds
  * @param integer $lngId
  * @access public
  */
  function loadCatalogLinkList($catalogIds, $lngId) {
    if (is_array($catalogIds)) {
      if (count($catalogIds) > 1) {
        $filter = " IN ('".implode("', '", $catalogIds)."')";
      } else {
        $filter = " = '".@(int)$catalogIds[0]."'";
      }
      foreach ($catalogIds as $catalogId) {
        unset($this->linkList[$catalogId]);
      }
    } else {
      $filter = " = '".(int)$catalogIds."'";
      unset($this->linkList[$catalogIds]);
    }
    $sql = "SELECT l.topic_id, l.cataloglink_id, l.cataloglink_title,
                   l.catalog_id, t.topic_id AS topic_exists
              FROM %s AS l
              LEFT OUTER JOIN %s AS t ON (t.topic_id = l.topic_id)
             WHERE l.catalog_id $filter
             ORDER BY l.cataloglink_sort, l.cataloglink_title";
    $params = array($this->tableCatalogLinks, $this->tableTopics);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->linkList[$row['catalog_id']][] = $row;
      }
    }
  }

  /**
  * Topic is linked to category ?
  *
  * @param integer $topicId topic_id
  * @param integer $catalogId catalog_id
  * @access public
  * @return boolean
  */
  function topicIsLinked($topicId, $catalogId) {
    if ($catalogId == 0) {
      return FALSE;
    }
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE catalog_id = '%d'
               AND topic_id = '%d'";
    $params = array($this->tableCatalogLinks, (int)$catalogId, (int)$topicId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() >= 1);
    }
    return FALSE;
  }

  /**
  * Check for existing catalog per id
  *
  * @param integer $id
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function catalogExist($id) {
    if ($id == 0) {
      return TRUE;
    }
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE catalog_id = '%d'";
    $params = array($this->tableCatalog, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() >= 1);
    }
    return FALSE;
  }

  /**
  * Check if catalog have no more links
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function catalogIsEmpty($id) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE catalog_id = '%d'";
    $params = array($this->tableCatalogLinks, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return ($res->fetchField() == 0);
    }
    return TRUE;
  }

  /**
  * Has sub categories?
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function catalogHasNoSubCategories($id) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE catalog_parent = '%d'";
    $params = array($this->tableCatalog, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return (0 == $res->fetchField());
    }
    return TRUE;
  }

  /**
  * Load catalog information per id
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadCatalogInformation($id) {
    unset($this->catalogDetails);
    $sql = "SELECT lng_id, catalog_id, catalog_title
              FROM %s
             WHERE catalog_id = '%d' ";
    $params = array($this->tableCatalogTrans, $id);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->catalogDetails[$row['lng_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Recreate Catalog Path
  *
  * @access public
  */
  function repairCatalogPath() {
    $categories = array();
    $sql = "SELECT catalog_id, catalog_parent, catalog_parent_path
              FROM %s";
    if ($res = $this->databaseQueryFmt($sql, $this->tableCatalog)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $categories[$row['catalog_id']] = $row;
      }
    }
    $counter = 0;
    foreach ($categories as $catId=>$category) {
      $newPath = $this->calcPrevPath($categories, $category);
      $categories[$catId]['NEW_PATH'] = $newPath;
      if ($newPath != $category['catalog_parent_path']) {
        $data = array(
          'catalog_parent' => $categories[$catId]['catalog_parent'],
          'catalog_parent_path' => $newPath
        );
        $updated = $this->databaseUpdateRecord(
          $this->tableCatalog, $data, 'catalog_id', $category['catalog_id']
        );
        if (FALSE !== $updated) {
          $counter++;
        }
      }
    }
    if ($counter > 0) {
      $this->addMsg(MSG_INFO, sprintf($this->_gt('%s paths repaired.'), $counter));
    } else {
      $this->addMsg(MSG_INFO, $this->_gt('Path index checked.'));
    }
  }

  /**
  * calculates previous path
  *
  * @param array &$categories
  * @param array &$category
  * @access public
  * @return string
  */
  function calcPrevPath(&$categories, &$category) {
    if (isset($category) && is_array($category)) {
      if (isset($category['NEW_PATH'])) {
        return $category['NEW_PATH'];
      } elseif (isset($categories[$category['catalog_parent']]) &&
                is_array($categories[$category['catalog_parent']])) {
        return $this->calcPrevPath(
          $categories,
          $categories[$category['catalog_parent']]
        ).$category['catalog_parent'].';';
      } else {
        $categories[$category['catalog_id']]['catalog_parent'] = 0;
        return ';0;';
      }
    }
  }
}
?>
