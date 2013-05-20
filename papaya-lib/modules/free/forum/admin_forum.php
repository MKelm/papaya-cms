<?php
/**
* Forum management main class.
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Free-Forum
* @version $Id: admin_forum.php 38013 2013-01-24 13:12:16Z smekal $
*/

/**
* Basic functionalities.
*/
require_once(dirname(__FILE__).'/base_forum.php');

/**
* Forum management main class.
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/
class admin_forum extends base_forum {

  /**
  * Instance of the community connector
  * @var connector_surfers
  */
  var $_communityObject = NULL;

  /**
  * Categories table
  * @var string $tableCategs
  */
  var $tableCategs = '';

  /**
  * Forum / boards table
  * @var string $tableBoards
  */
  var $tableBoards = '';

  /**
  * Entry table
  * @var string $tableEntries
  */
  var $tableEntries = '';

  /**
  * Surfer table
  * @var string $tableSurfers
  */
  var $tableSurfers = '';

  /**
  * Css input class
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'x-large';

  /**
  * Parameter prefix name
  * @var string $paramName
  */
  var $paramName = '';

  /**
  * Session parameter prefix name
  * @var string $sessionParamName
  */
  var $sessionParamName = '';

  /**
  * Parameters
  * @var array $params
  */
  var $params = NULL;

  /**
  * Categories
  * @var array $categs
  */
  var $categs = NULL;

  /**
  * Category
  * @var array $categ
  */
  var $categ = NULL;

  /**
  * Category tree
  * @var array $categTree
  */
  var $categTree = NULL;

  /**
  * Boards
  * @var array $boards
  */
  var $boards = NULL;

  /**
  * Board
  * @var array $board
  */
  var $board = NULL;

  /**
  * Entries
  * @var array $entries
  */
  var $entries = NULL;

  /**
  * Entry
  * @var array $entry
  */
  var $entry = NULL;

  /**
  * Entry tree
  * @var array $entryTree
  */
  var $entryTree = NULL;

  /**
  * Threads
  * @var array $threads
  */
  var $threads = NULL;

  /**
  * Thread id list
  * @var array $entryIdList
  */
  var $entryIdList = NULL;

  /**
  * Full text search ?
  * @var array $fullTextSearch
  */
  var $fullTextSearch = PAPAYA_SEARCH_BOOLEAN;

  /**
  * Cache search results
  * @var boolean $cacheSearchResults
  */
  var $cacheSearchResults = FALSE;

  /**
  * Cut categories
  * @var array $cutCategs
  */
  var $cutCategs = NULL;

  /**
  * cut list
  * @var array $cutList
  */
  var $cutList = NULL;

  /**
  * cut threads
  * @var array $cutThreads
  */
  var $cutThreads = NULL;

  /**
  * Categories by id
  * @var array $categsById
  */
  var $categsById = NULL;

  /**
  * Allowed Tags
  * @var array $allowedTags
  */
  var $allowedTags = array('b', 'i', 'tt');

  /**
  * Maximum depth for subthreads to be shown.
  * @var int $maxIndent
  */
  var $maxIndent = 7;

  /**
  * Currently showing last entries?
  * @var boolean $showLastEntries
  */
  var $showLastEntries = FALSE;

  /**
  * Count of all audios.
  * @var integer
  */
  var $_boardsTotalCount = 0;

  /**
  * Offset of board list paging.
  * @var integer
  */
  var $_boardPagingOffset = 0;

  /**
  * Count of items per board list page.
  * @var integer
  */
  var $_boardPagingLimit = 10;

  /**
  * Count of items per thread list page.
  * @var integer
  */
  var $_threadPagingLimit = 10;

  /**
  * Count of items per surfer list page.
  * @var integer
  */
  var $_surferPagingLimit = 10;

  /**
  * array with paging params
  * @var array paging params
  */
  var $_pagingParams = array();

  /**
  * Initialize - Load parameters and session variable
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
    $this->initializePagingLimits();

    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    if (!isset($this->params['offset'])) {
      $this->params['offset'] = 0;
    }

    if (!isset($this->params['surfer_limit'])) {
      $this->params['surfer_limit'] = $this->_surferPagingLimit;
    }
    if (!isset($this->params['surfer_offset'])) {
      $this->params['surfer_offset'] = 0;
    }
    if (!isset($this->params['surfer_id'])) {
      $this->params['surfer_id'] = '';
    }

    if (!isset($this->params['thread_offset'])) {
      $this->params['thread_offset'] = 0;
    }

    $this->_boardPagingOffset = isset($this->params['board_offset'])
      ? (int)$this->params['board_offset'] : 0;

    $this->localImages = array(
      'forum' => $this->module->getIconUri('module-forum.png'),
      'forum-add' => $this->module->getIconUri('module-forum-add.png'),
      'forum-delete' => $this->module->getIconUri('module-forum-delete.png')
    );
    if (isset($this->params['last_entries']) && $this->params['last_entries'] == 1) {
      $this->showLastEntries = TRUE;
    }
  }

  /**
  * Execute - basic function for handling parameters
  *
  * @access public
  */
  function execute() {
    $this->initializeForum();
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
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
      case 'search':
        try {
          if (!empty($this->params['search_string'])) {
            if (isset($this->params['search_offset']) &&
                $this->params['search_offset'] > 0 &&
                isset($this->params['search_next'])) {
              $this->_searchResult = $this->searchForumEntries(
                $this->params['search_string'],
                $this->params['search_offset']
              );
            } else {
              $this->_searchResult = $this->searchForumEntries($this->params['search_string']);
            }
            $this->initializeForum();
          }
        } catch (InvalidArgumentException $e) {
          $this->addMsg(MSG_ERROR, $this->_gt($e->getMessage()));
        }
        break;
      case 'open':
        $this->categsOpen[(int)$this->params['categ_id']] = TRUE;
        break;
      case 'close':
        unset($this->categsOpen[(int)$this->params['categ_id']]);
        break;
      case 'repair':
        $this->repairPaths();
        break;
      case 'fix_search':
        if ($this->fixSearch() > 0) {
          $this->addMsg(MSG_INFO, $this->_gt('Entries have been fixed.'));
        } else {
          $this->addMsg(MSG_INFO, $this->_gt('Entries do not need to be fixed.'));
        }
        break;
      case 'add_categ':
        if ($this->module->hasPerm(2)) {
          if ($newId = $this->addCateg((int)$this->params['categ_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Category added.'));
            $this->params['categ_id'] = $newId;
            $this->initializeSessionParam(
              'categ_id', array('cmd', 'forum_id', 'thread_id', 'entry_id')
            );
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
          }
        }
        break;
      case 'del_categ':
        if ($this->module->hasPerm(2)) {
          if (isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete']) {
            if ($this->categExists($this->params['categ_id'])) {
              if ($this->categIsEmpty($this->params['categ_id'])) {
                if ($this->deleteCateg($this->params['categ_id'])) {
                  $this->addMsg(MSG_INFO, $this->_gt('Category deleted.'));
                  if ($this->categExists($this->params['categ_prev'])) {
                    $this->params['categ_id'] = $this->params['categ_prev'];
                  } else {
                    $this->params['categ_id'] = 0;
                  }
                  $this->initializeSessionParam(
                    'categ_id', array('cmd', 'forum_id', 'thread_id', 'entry_id')
                  );
                } else {
                  $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
                }
              } else {
                $this->addMsg(MSG_WARNING, $this->_gt('Category is not empty.'));
                $this->params['cmd'] = '';
              }
            } else {
              $this->addMsg(MSG_WARNING, $this->_gt('Category not found.'));
            }
          }
        }//End of hasPerm-test
        break;
      case 'edit_categ':
        if ($this->module->hasPerm(2) && isset($this->params['categ_id'])) {
          $this->loadCategs();
          $this->loadCateg($this->params['categ_id']);
          $this->initializeCategEditform();
          if ($this->categDialog->modified()) {
            if ($this->categDialog->checkDialogInput()) {
              if ($this->saveCateg()) {
                $this->addMsg(MSG_INFO, $this->_gt('Category modified.'));
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            }
          }
        }
        break;
      case 'cut_categ':
        if ($this->module->hasPerm(2)) {
          if ($this->categExists($this->params['categ_id'])) {
            if ($this->cutCateg((int)$this->params['categ_id'])) {
              $this->params['cmd'] = NULL;
              $this->params['categ_id'] = 0;
              $this->initializeSessionParam('categ_id', array('cmd', 'forum_id'));
              $this->addMsg(MSG_INFO, $this->_gt('Category cut out.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
            }
          }
        }
        break;

      case 'paste_categ':
        if ($this->module->hasPerm(2)) {
          if (isset($this->params['cut_categ_id']) &&
            $this->categExists($this->params['cut_categ_id']) &&
            isset($this->params['categ_id']) && $this->params['categ_id'] >= 0) {

            $pasted = $this->pasteCateg(
              $this->params['categ_id'],
              (int)$this->params['cut_categ_id']
            );
            if ($pasted) {
              $this->params['cmd'] = NULL;
              $this->params['categ_id'] = $this->params['cut_categ_id'];
              $this->initializeSessionParam('categ_id', array('cmd', 'forum_id'));
              $this->addMsg(MSG_INFO, $this->_gt('Category pasted.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
            }
          }
        }
        break;

      case 'paste_forum':
        if ($this->module->hasPerm(3)) {
          if (isset($this->params['cut_forum_id']) &&
              $this->forumExists($this->params['cut_forum_id']) &&
              isset($this->params['categ_id']) &&
                    $this->categExists($this->params['categ_id'])) {
            if ($this->pasteForum($this->params['cut_forum_id'], $this->params['categ_id'])) {
              $this->params['cmd'] = NULL;
              $this->params['forum_id'] = $this->params['cut_forum_id'];
              $this->initializeSessionParam('forum_id', array('offset', 'cmd'));
              $this->addMsg(MSG_INFO, $this->_gt('Forum pasted.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
            }
          }
        }
        break;

      case 'add_forum':
        if ($this->module->hasPerm(3)) {
          if ($newId = $this->addForum((int)$this->params['categ_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Forum added.'));
            $this->params['forum_id'] = $newId;
            $this->initializeSessionParam(
              'forum_id', array('cmd', 'thread_id', 'entry_id', 'offset')
            );
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
        break;
      case 'del_forum':
        if ($this->module->hasPerm(3)) {
          if (isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete']) {
            if ($this->forumExists($this->params['forum_id'])) {
              if ($this->deleteForum($this->params['forum_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Forum deleted.'));
                $this->params['forum_id'] = 0;
                $this->initializeSessionParam(
                  'forum_id', array('cmd', 'thread_id', 'entry_id')
                );
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            } else {
              $this->addMsg(MSG_WARNING, $this->_gt('Forum not found.'));
            }
          }
        }
        break;
      case 'edit_forum':
        if ($this->module->hasPerm(3)) {
          $this->loadCategs();
          if (isset($this->params['categ_id']) && $this->params['categ_id'] > 0) {
            $this->loadBoards(
              $this->params['categ_id'],
              $this->_boardPagingLimit,
              $this->_boardPagingOffset
            );
          }
          $this->initializeForumEditform();
          if ($this->forumDialog->modified()) {
            if ($this->forumDialog->checkDialogInput()) {
              if ($this->saveForum()) {
                $this->addMsg(MSG_INFO, $this->_gt('Forum modified.'));
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            }
          }
        }
        break;

      case 'cut_forum':
        if ($this->module->hasPerm(3)) {
          if ($this->forumExists($this->params['forum_id'])) {
            if ($this->cutForum((int)$this->params['forum_id'])) {
              $this->params['cmd'] = '';
              $this->params['forum_id'] = 0;
              $this->initializeSessionParam('forum_id', array('cmd', 'entry_id'));
              $this->addMsg(MSG_INFO, $this->_gt('Forum cut out.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
            }
          }
        }
        break;
      case 'edit_entry':
        if ($this->module->hasPerm(4)) {
          $this->loadEntry($this->params['entry_id']);
          $this->initializeEntryEditform();
          if ($this->entryDialog->modified()) {
            if ($this->entryDialog->checkDialogInput()) {
              $values = array(
                'entry_subject' => $this->entryDialog->data['entry_subject'],
                'entry_text' => $this->entryDialog->data['entry_text'],
                'entry_notify' => $this->entryDialog->data['entry_sendanswers']
              );
              if ($this->saveEntry($this->entry['entry_id'], $values)) {
                $this->addMsg(MSG_INFO, $this->_gt('Entry modified.'));
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            }
          }
        }
        break;
      case 'del_entry':
        if ($this->module->hasPerm(4)) {
          if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
            $deleteAll = isset($this->params['delete_all'])
              ? (bool)$this->params['delete_all'] : FALSE;
            if ($this->deleteEntry((int)$this->params['entry_id'], $deleteAll)) {
              $this->addMsg(MSG_INFO, $this->_gt('Entry deleted.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
            }
          }
        }
        break;
      case 'block_entry':
        if ($this->module->hasPerm(4)) {
          if ($this->blockEntry((int)$this->params['entry_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Entry blocked.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
          $this->params['categ_id'] = NULL;
          $this->params['thread_id'] = NULL;
        }
        break;
      case 'unblock_entry':
        if ($this->module->hasPerm(4)) {
          if ($this->unblockEntry((int)$this->params['entry_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Entry unblocked.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
          $this->params['categ_id'] = NULL;
          $this->params['thread_id'] = NULL;
        }
        break;
      case 'block_surfer':
        if ($this->module->hasPerm(4)) {
          $community = $this->getCommunityObject();
          if ($community->setValid($this->params['surfer_id'], 3)) {
            $this->addMsg(MSG_INFO, $this->_gt('Surfer blocked.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
        }
        break;
      case 'unblock_surfer':
        if ($this->module->hasPerm(4)) {
          $community = $this->getCommunityObject();
          if ($community->setValid($this->params['surfer_id'], 1)) {
            $this->addMsg(MSG_INFO, $this->_gt('Surfer unblocked.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
        }
        break;
      case 'filter_entries':
        if ($this->module->hasPerm(4)) {
          if (isset($this->params['filter_action'])) {
            switch ($this->params['filter_action']) {
            case 'surfer':
              if (isset($this->params['surfer_id'])) {
                $this->entries = $this->getEntriesBySurferId($this->params['surfer_id']);
              }
              break;
            default:
              break;
            }
          }
        }
        break;
      case 'del_thread':
        if ($this->module->hasPerm(4)) {
          if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
            if ($this->deleteThread((int)$this->params['thread_id'])) {
              $this->addMsg(MSG_INFO, $this->_gt('Thread deleted.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
            }
          }
        }
        break;
      case 'cut_thread':
        if ($this->module->hasPerm(4)) {
          if ($this->entryExists($this->params['thread_id'])) {
            if ($this->cutThread((int)$this->params['thread_id'])) {
              $this->params['cmd'] = NULL;
              $this->params['thread_id'] = 0;
              $this->initializeSessionParam('thread_id', array('cmd', 'entry_id'));
              $this->addMsg(MSG_INFO, $this->_gt('Thread cut out.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
            }
          }
        }
        break;
      case 'paste_thread':
        if ($this->module->hasPerm(4)) {
          if (isset($this->params['cut_thread_id']) &&
            $this->entryExists($this->params['cut_thread_id']) &&
            isset($this->params['forum_id']) &&
            $this->forumExists($this->params['forum_id'])) {
            if ($this->pasteThread()) {
              $this->params['cmd'] = NULL;
              $this->params['thread_id'] = $this->params['cut_thread_id'];
              $this->initializeSessionParam('thread_id', array('cmd', 'entry_id'));
              $this->addMsg(MSG_INFO, $this->_gt('Thread pasted.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
            }
          }
        }
        break;
      }
    }
    $this->sessionParams['categopen'] = $this->categsOpen;
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    $this->loadCategs();
    if (isset($this->params['categ_id']) && $this->params['categ_id'] > 0) {
      $this->loadBoards(
        $this->params['categ_id'],
        $this->_boardPagingLimit,
        $this->_boardPagingOffset
      );
    }
    if (!empty($this->params['thread_id'])) {
      $this->loadThread((int)$this->params['thread_id']);
    } else {
      if (!isset($this->params['filter_action'])) {
        $this->loadLastEntries();
      }
    }
    $this->loadEntry(empty($this->params['entry_id']) ? 0 : (int)$this->params['entry_id']);
    if ($this->cutOutExists()) {
      $this->loadCutElement();
    }
  }

  /**
  * In order to get older entries in forums to be searchable
  * an automatic conversion routine was neccessary. This is it.
  * For all stored forum entries, not having a stripped text field
  * filled with data yet, it is created by taking the current text
  * field of those entries that contents are to be stripped from any
  * html tags.
  *
  * @return Number of converted entries (int).
  */
  function fixSearch() {
    $count = 0;
    $sql = "SELECT entry_id, entry_text
              FROM %s
             WHERE entry_strip = ''
                OR entry_strip IS NULL";
    $params = array($this->tableEntries);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $entries = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $entries[$row['entry_id']] = strip_tags($row['entry_text']);
      }
      foreach ($entries as $entryId => $entryText) {
        $updated = FALSE !== $this->databaseUpdateRecord(
          $this->tableEntries,
          array(
            'entry_strip' => $entryText
          ),
          'entry_id',
          $entryId
        );
        if ($updated) {
          $count++;
        }
      }
    }
    return $count;
  }

  /**
  * Return XML data
  *
  * @access public
  */
  function getXML() {
    if (is_object($this->layout)) {
      $this->getXMLButtons();
      $this->getXMLSurfersTree();
      $this->getSearchDialog();
      $this->getXMLCategTree();
      $this->getXMLForumList();
      if (!isset($this->params['cmd'])) {
        $this->params['cmd'] = '';
      }
      switch ($this->params['cmd']) {
      case 'del_categ':
        if ($this->module->hasPerm(2, FALSE)) {
          $this->getXMLDelCategForm();
        }
        break;
      case 'del_forum':
        if ($this->module->hasPerm(3, FALSE)) {
          $this->getXMLDelForumForm();
        }
        break;
      case 'del_thread':
        if ($this->module->hasPerm(4, FALSE)) {
          $this->getXMLDelThreadForm();
        }
        break;
      case 'del_entry':
        if ($this->module->hasPerm(4, FALSE)) {
          $this->getXMLDelEntryForm();
        }
        break;
      }
      if ($this->showLastEntries === FALSE &&
          isset($this->params['categ_id']) &&
          $this->params['categ_id'] > 0) {

        if (isset($this->board)) {
          if (isset($this->entry)) {
            $this->getXMLEntryForm();
          } else {
            $this->getXMLForumForm();
          }

          if (isset($this->topics)) {
            $this->getXMLTopicsList();
          }
          if (isset($this->entries) && !empty($this->params['thread_id'])) {
            $this->getXMLThread();
          }

        } else {
          $this->getXMLCategForm();
        }
      } else {
        $this->showLastEntries = TRUE;
        $this->getXMLLastEntries();
      }
      if ( (isset($this->cutCategs) && is_array($this->cutCategs)) ||
        (isset($this->cutList) && is_array($this->cutList)) ||
        (isset($this->cutThreads) && is_array($this->cutThreads))) {
        $this->getXMLCutList();
      }
    }
  }

  /**
  * Get XML for buttons
  *
  * @access public
  */
  function getXMLButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;

    $toolbar->addButton(
      'Repair path',
      $this->getLink(array('cmd'=>'repair')),
      'items-option',
      '',
      FALSE
    );
    $toolbar->addButton(
      'Repair search',
      $this->getLink(
        array(
          'cmd' => 'fix_search'
        )
      ),
      'items-option',
      'Repair search data',
      FALSE
    );

    if ($this->module->hasPerm(2)) {
      $toolbar->addButton(
        'Add category',
        $this->getLink(
          array(
            'cmd' => 'add_categ',
            'categ_id' => empty($this->params['categ_id']) ? 0 : (int)$this->params['categ_id']
          )
        ),
        'actions-folder-add',
        '',
        FALSE
      );
    }
    if (isset($this->params['categ_id']) &&
        isset($this->categs[$this->params['categ_id']]) &&
        is_array($this->categs[$this->params['categ_id']])) {
      if ($this->module->hasPerm(2, FALSE)) {
        if (isset($this->params['categ_id']) && $this->params['categ_id'] > 0 &&
            (!isset($this->params['forum_id']) || $this->params['forum_id'] == 0) &&
            (!isset($this->params['thread_id']) || $this->params['thread_id'] == 0)) {
          $toolbar->addButton(
            'Cut category',
            $this->getLink(
              array(
                'cmd' => 'cut_categ',
                'categ_id' => (int)$this->params['categ_id']
              )
            ),
            'actions-edit-cut',
            '',
            FALSE
          );
          $toolbar->addButton(
            'Delete category',
            $this->getLink(
              array(
                'cmd' => 'del_categ',
                'categ_id' => (int)$this->params['categ_id']
              )
            ),
            'actions-folder-delete',
            '',
            FALSE
          );
        }
        $toolbar->addSeperator();
      }

      if ($this->module->hasPerm(3, FALSE)) {
        $toolbar->addButton(
          'Add forum',
          $this->getLink(
            array('cmd' => 'add_forum',
              'categ_id' => (int)$this->params['categ_id'])
            ),
          $this->localImages['forum-add'],
          '',
          FALSE
        );
      }
      if (isset($this->params['forum_id']) &&
          isset($this->boards[(int)$this->params['forum_id']]) &&
          is_array($this->boards[(int)$this->params['forum_id']])) {
        if ($this->module->hasPerm(3, FALSE)) {
          if (isset($this->params['categ_id']) && $this->params['categ_id'] > 0 &&
            (isset($this->params['forum_id']) || $this->params['forum_id'] > 0) &&
            (!isset($this->params['thread_id']) || $this->params['thread_id'] == 0)) {
            $toolbar->addButton(
              'Cut Forum',
              $this->getLink(
                array('cmd' => 'cut_forum', 'forum_id' => (int)$this->params['forum_id'])
              ),
              'actions-edit-cut',
              '',
              FALSE
            );
            $toolbar->addButton(
              'Delete forum',
              $this->getLink(
                array(
                  'cmd' => 'del_forum',
                  'categ_id' => empty($this->params['categ_id'])
                    ? 0 : (int)$this->params['categ_id'],
                  'forum_id' => (int)$this->params['forum_id']
                )
              ),
              $this->localImages['forum-delete'],
              '',
              FALSE
            );
          }
        }
        if ($this->module->hasPerm(4, FALSE)) {
          if (isset($this->params['thread_id']) &&
              isset($this->entries[(int)$this->params['thread_id']]) &&
              is_array($this->entries[(int)$this->params['thread_id']])) {

            $toolbar->addSeperator();
            $toolbar->addButton(
              'Cut Thread',
              $this->getLink(
                array(
                  'cmd' => 'cut_thread',
                  'categ_id' => (int)$this->params['categ_id'],
                  'forum_id' => (int)$this->params['forum_id'],
                  'thread_id' => (int)$this->params['thread_id']
                )
              ),
              'actions-edit-cut',
              '',
              FALSE
            );
            $toolbar->addButton(
              'Delete thread',
              $this->getLink(
                array(
                  'cmd' => 'del_thread',
                  'categ_id' => !empty($this->params['categ_id']) ?
                    $this->params['categ_id'] : NULL,
                  'thread_id' => !empty($this->params['thread_id']) ?
                    $this->params['thread_id'] : NULL,
                  'forum_id' => !empty($this->params['forum_id']) ?
                    $this->params['forum_id'] : NULL,
                  'entry_id' => !empty($this->params['entry_id']) ?
                    $this->params['entry_id'] : NULL
                )
              ),
              'places-trash',
              '',
              FALSE
            );
            $toolbar->addSeperator();
          }
          $toolbar->addSeperator();
          if (!empty($this->entry) && is_array($this->entry) &&
              !empty($this->entry['entry_pid'])) {
            if (isset($this->entryTree[$this->entry['entry_id']]) &&
              is_array($this->entryTree[$this->entry['entry_id']])) {
              $toolbar->addButton(
                'Delete subthread',
                $this->getLink(
                  array(
                    'cmd' => 'del_entry',
                    'categ_id' => !empty($this->params['categ_id']) ?
                      $this->params['categ_id'] : NULL,
                    'thread_id' => !empty($this->params['thread_id']) ?
                      $this->params['thread_id'] : NULL,
                    'forum_id' => !empty($this->params['forum_id']) ?
                      $this->params['forum_id'] : NULL,
                    'entry_id' => !empty($this->params['entry_id']) ?
                      $this->params['entry_id'] : NULL,
                    'delete_all' => 1
                  )
                ),
                'places-trash',
                '',
                FALSE
              );
            }
            $toolbar->addButton(
              'Delete entry',
              $this->getLink(
                array(
                  'cmd' => 'del_entry',
                  'categ_id' => !empty($this->params['categ_id']) ?
                    $this->params['categ_id'] : NULL,
                  'thread_id' => !empty($this->params['thread_id']) ?
                    $this->params['thread_id'] : NULL,
                  'forum_id' => !empty($this->params['forum_id']) ?
                    $this->params['forum_id'] : NULL,
                  'entry_id' => !empty($this->params['entry_id']) ?
                    $this->params['entry_id'] : NULL
                )
              ),
              'places-trash',
              '',
              FALSE
            );
            $toolbar->addSeperator();
          }
        }
      }
    }

    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(
        sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $str)
      );
    }
  }

  /**
  * Add category
  *
  * @param integer $parent
  * @access public
  * @return mixed TRUE or number of affected_rows or database result object
  */
  function addCateg($parent) {
    if ($this->categExists($parent) || ($parent == 0)) {
      if ($parent > 0) {
        if (!isset($this->categs[$parent])) {
          $categ = $this->loadCateg($parent);
          $prevPath = $categ['forumcat_path'].$parent.';';
        } else {
          $prevPath = $this->categs[$parent]['forumcat_path'].$parent.';';
        }
      } else {
        $prevPath = ';0;';
      }
      return $this->databaseInsertRecord(
        $this->tableCategs,
        'forumcat_id',
        array(
          'forumcat_prev' => $parent,
          'forumcat_path' => $prevPath,
          'forumcat_title' => $this->_gt('New category')
        )
      );
    } else {
      return TRUE;
    }
  }

  /**
  * Delete Category
  *
  * @param integer $id category id
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function deleteCateg($id) {
    return $this->databaseDeleteRecord($this->tableCategs, 'forumcat_id', $id);
  }

  /**
  * Save category
  *
  * @access public
  * @return boolean
  */
  function saveCateg() {
    $data = array(
      'forumcat_title' => $this->params['forumcat_title'],
      'forumcat_desc' => $this->params['forumcat_desc']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableCategs, $data, 'forumcat_id', (int)$this->params['categ_id']
    );
  }

  /**
  * Get XML category tree
  *
  * @access public
  */
  function getXMLCategTree() {
    if (isset($this->categs) && is_array($this->categs)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Categories'))
      );
      $result .= '<items>'.LF;
      $selected = empty($this->params['categ_id']) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<listitem href="%s" title="%s" image="%s" %s/>'.LF,
        papaya_strings::escapeHTMLChars($this->getLink()),
        papaya_strings::escapeHTMLChars($this->_gt('Base')),
        papaya_strings::escapeHTMLChars($this->images['places-desktop']),
        $selected
      );
      $result .= $this->getXMLCategSubTree(0, 0);
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get XML surfer tree
  *
  * @access public
  */
  function getXMLSurfersTree() {
    $result = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Surfers'))
    );
    $surferLimit = (isset($this->params['surfer_limit'])) ?
      $this->params['surfer_limit'] : $this->_surferPagingLimit;
    $surferOffset = (isset($this->params['surfer_offset'])) ?
      $this->params['surfer_offset'] : 0;
    $surfers =
      $this->getEntriesSurfers($surferLimit, $surferOffset);
    $result .= $this->generatePagingLinksXml(
      'surfer_offset',
      $this->_pagingParams,
      $this->params['surfer_offset'],
      $this->params['surfer_limit'],
      $this->_totalSurfersCount
    );
    $result .= '<items>'.LF;

    foreach ($surfers as $surferId => $surferData) {

      if (!empty($surferData['surfer_givenname']) && !empty($surferData['surfer_surname'])) {
        $surferName = $surferData['surfer_givenname'].' '.$surferData['surfer_surname'];
      } else {
        $surferName = $surferData['surfer_handle'];
      }

      $selected = ($this->params['surfer_id'] == $surferId ? ' selected="selected"' : '');
      $result .= sprintf(
        '<listitem href="%s" title="%s" image="%s" %s/>'.LF,
        $this->getLink(
          array(
            'surfer_id' => $surferId,
            'cmd' => 'filter_entries',
            'filter_action' => 'surfer',
            'surfer_offset' => $this->params['surfer_offset']
          )
        ),
        papaya_strings::escapeHTMLChars($surferName),
        papaya_strings::escapeHTMLChars($this->images['items-user']),
        $selected
      );
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    $this->layout->addRight($result);
  }

  /**
  * Branch of Category tree
  *
  * @param integer $parent Parent-ID
  * @param integer $indent shifting
  * @return string $result XML
  */
  function getXMLCategSubTree($parent, $indent) {
    $result = '';
    if (isset($this->categTree[$parent]) && is_array($this->categTree[$parent]) &&
        (isset($this->categsOpen[$parent]) || ($parent == 0))) {
      foreach ($this->categTree[$parent] as $id) {
        $result .= $this->getXMLCategEntry($id, $indent);
      }
    }
    return $result;
  }

  /**
  * Element of category tree
  * @param integer $id ID
  * @param integer $indent shifting
  * @return string $result XML
  */
  function getXMLCategEntry($id, $indent) {
    $result = '';
    if (isset($this->categs[$id]) && is_array($this->categs[$id])) {
      if (isset($this->categTree[$id]) && is_array($this->categTree[$id])) {
        $empty = FALSE;
      } else {
        $empty = TRUE;
      }
      $opened = (bool)(isset($this->categsOpen[$id]) && (!$empty));
      if ($empty) {
        $nodeHref = FALSE;
        $node = ' node="empty"';
      } elseif ($opened) {
        $nodeHref = $this->getLink(
          array(
            'cmd' => 'close',
            'categ_id' => (int)$id
          )
        );
        $node = sprintf(
          ' node="open" nhref="%s"',
          papaya_strings::escapeHTMLChars($nodeHref)
        );
      } else {
        $nodeHref = $this->getLink(
          array(
            'cmd' => 'open',
            'categ_id' => (int)$id
          )
        );
        $node = sprintf(
          ' node="close" nhref="%s"',
          papaya_strings::escapeHTMLChars($nodeHref)
        );
      }
      $selected = ($this->params['categ_id'] == $id) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<listitem href="%s" title="%s" indent="%d" %s %s/>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'edit_categ', 'categ_id' => (int)$id))
        ),
        papaya_strings::escapeHTMLChars($this->categs[$id]['forumcat_title']),
        (int)$indent,
        $node,
        $selected
      );
      $result .= $this->getXMLCategSubTree($id, $indent + 1);
    }
    return $result;
  }

  /**
  * Delete category form
  *
  * @access public
  */
  function getXMLDelCategForm() {
    if (isset($this->categs[$this->params['categ_id']]) &&
        is_array($this->categs[$this->params['categ_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_categ',
        'categ_id' => $this->params['categ_id'],
        'categ_prev' => $this->categs[$this->params['categ_id']]['forumcat_prev'],
        'confirm_delete' => 1
      );
      $msg = sprintf(
        $this->_gt('Delete categ "%s" (%s)?'),
        $this->categs[$this->params['categ_id']]['forumcat_title'],
        (int)$this->params['categ_id']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Initialize categ edit form
  *
  * @access public
  */
  function initializeCategEditForm() {
    if (!(isset($this->categDialog) && is_object($this->categDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->categs[$this->params['categ_id']];
      $hideButtons = FALSE;
      $useToken = TRUE;
      if ($this->module->hasPerm(2, FALSE)) {
        $hidden = array(
          'cmd' => 'edit_categ',
          'save' => 1,
          'categ_id' => $this->params['categ_id']
        );
        $fields = array(
          'forumcat_title' => array('Title', 'isNoHTML', TRUE, 'input', 200),
          'forumcat_desc' => array('Description', 'isNoHTML', FALSE, 'textarea', 8)
        );
      } else {
        $hideButtons = TRUE;
        $useToken = FALSE;
        $hidden = NULL;
        $fields = array(
          'forumcat_title' => array('Title', 'isNoHTML', TRUE, 'info'),
          'forumcat_desc' => array('Description', 'isNoHTML', FALSE, 'info')
        );
      }
      $this->categDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->categDialog->dialogHideButtons = $hideButtons;
      $this->categDialog->useToken = $useToken;
      $this->categDialog->msgs = &$this->msgs;
      $this->categDialog->loadParams();
    }
  }

  /**
  * Get category form XML
  *
  * @access public
  */
  function getXMLCategForm() {
    if (isset($this->categs[$this->params['categ_id']]) &&
        is_array($this->categs[$this->params['categ_id']])) {
      $this->initializeCategEditForm();
      $this->categDialog->inputFieldSize = $this->inputFieldSize;
      $this->categDialog->baseLink = $this->baseLink;
      $this->categDialog->dialogTitle = $this->_gt('Properties');
      $this->categDialog->dialogDoubleButtons = FALSE;
      $this->layout->add($this->categDialog->getDialogXML());
    }
  }

  /**
  * Delete forum
  *
  * @param integer $id
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function deleteForum($id) {
    if ($this->databaseDeleteRecord($this->tableEntries, 'forum_id', $id) !== FALSE) {
      return ($this->databaseDeleteRecord($this->tableBoards, 'forum_id', $id) !== FALSE);
    }
    return FALSE;
  }

  /**
  * Save forum
  *
  * @access public
  * @return boolean
  */
  function saveForum() {
    $data = array(
      'forum_title' => $this->params['forum_title'],
      'forum_desc' => $this->params['forum_desc']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableBoards, $data, 'forum_id', (int)$this->params['forum_id']
    );
  }

  /**
  * Get XML for forum list
  *
  * @access public
  */
  function getXMLForumList() {
    if (isset($this->boards) && is_array($this->boards)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Forums'))
      );
      $result .= $this->generatePagingLinksXml(
        'board_offset',
        $this->_pagingParams,
        $this->_boardPagingOffset,
        $this->_boardPagingLimit,
        $this->boardCount
      );
      $result .= '<items>'.LF;
      foreach ($this->boards as $id=>$board) {
        if (isset($board) && is_array($board)) {
          if (isset($this->params['forum_id']) && $this->params['forum_id'] == $id) {
            $selected = ' selected="selected"';
            $imageIndex = 'status-folder-open';
          } else {
            $selected = '';
            $imageIndex = 'items-folder';
          }
          $result .= sprintf(
            '<listitem href="%s" title="%s" image="%s" %s/>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array(
                  'categ_id' => $board['forumcat_id'],
                  'forum_id'=>(int)$id,
                  'board_offset' => $this->_boardPagingOffset
                )
              )
            ),
            papaya_strings::escapeHTMLChars($board['forum_title']),
            papaya_strings::escapeHTMLChars($this->images[$imageIndex]),
            $selected
          );
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get XML for delete forum form
  *
  * @access public
  */
  function getXMLDelForumForm() {
    if (isset($this->boards[$this->params['forum_id']]) &&
        is_array($this->boards[$this->params['forum_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_forum',
        'forum_id' => $this->params['forum_id'],
        'categ_id' => empty($this->params['categ_id']) ? 0 : $this->params['categ_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete forum "%s" (%s)?'),
        $this->boards[$this->params['forum_id']]['forum_title'],
        (int)$this->params['forum_id']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Initialize forum edit form
  *
  * @access public
  */
  function initializeForumEditForm() {
    if (!(isset($this->forumDialog) && is_object($this->forumDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->board;
      $useToken = TRUE;
      $hideButtons = FALSE;
      if ($this->module->hasPerm(3, FALSE)) {
        $hidden = array(
          'cmd' => 'edit_forum',
          'save' => 1,
          'forum_id' => $data['forum_id'],
          'categ_id' => $data['forumcat_id'],
        );
        $fields = array(
          'forum_title' => array('Title', 'isNoHTML', TRUE, 'input', 200),
          'forum_desc' => array('Description', 'isNoHTML', FALSE, 'textarea', 8)
        );
      } else {
        $useToken = FALSE;
        $hideButtons = TRUE;
        $hidden = NULL;
        $fields = array(
          'forum_title' => array('Title', 'isNoHTML', TRUE, 'info'),
          'forum_desc' => array('Description', 'isNoHTML', FALSE, 'info')
        );
      }
      $this->forumDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->forumDialog->dialogHideButtons = $hideButtons;
      $this->forumDialog->useToken = $useToken;
      $this->forumDialog->msgs = &$this->msgs;
      $this->forumDialog->loadParams();
    }
  }

  /**
  * Get XML for froum form
  *
  * @access public
  */
  function getXMLForumForm() {
    if (isset($this->boards[$this->params['forum_id']]) &&
        is_array($this->boards[$this->params['forum_id']])) {
      $this->initializeForumEditForm();
      $this->forumDialog->inputFieldSize = $this->inputFieldSize;
      $this->forumDialog->baseLink = $this->baseLink;
      $this->forumDialog->dialogTitle = $this->_gt('Properties');
      $this->forumDialog->dialogDoubleButtons = FALSE;
      $this->layout->add($this->forumDialog->getDialogXML());
    }
  }

  /**
  * Delete entry
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function deleteEntry($id, $deleteAll = FALSE) {
    $this->loadEntry($id);
    if ($deleteAll === FALSE) {
      // Select all my childs.
      $sql = "SELECT entry_id
                FROM %s
               WHERE entry_pid = %d";
      $params = array($this->tableEntries, $id);
      $childIds = array();
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($entryId = $res->fetchField()) {
          $childIds[] = $entryId;
        }
        // Make them childs of my parent.
        $this->databaseUpdateRecord(
          $this->tableEntries,
          array('entry_pid' => $this->entry['entry_pid']),
          'entry_id',
          $childIds
        );
      }
    } else {
      $sql = "SELECT entry_path
                FROM %s
               WHERE entry_id = '%d'";
      $params = array($this->tableEntries, $id);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($entryPath = $res->fetchField()) {
          $entryPath .= $id.';';
          $sql = "DELETE FROM %s WHERE entry_path LIKE '%%;%s;%%'";
          $this->databaseQueryFmtWrite(
            $sql,
            array($this->tableEntries, $entryPath)
          );
        }
      }
    }
    // Delete me.
    $this->databaseDeleteRecord($this->tableEntries, 'entry_id', $id);
    $this->repairPaths();
    $this->updateThread($this->entry['entry_pid']);
    return TRUE;
  }

  /**
  * Delete thread
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function deleteThread($id) {
    return $this->deleteEntry($id, TRUE);
  }

  /**
  * Get XML for delete thread form
  *
  * @access public
  */
  function getXMLDelThreadForm() {
    if (isset($this->entries[(int)$this->params['entry_id']]) &&
        isset($this->entries[(int)$this->params['thread_id']]) &&
        is_array($this->entries[(int)$this->params['thread_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_thread',
        'thread_id' => $this->params['thread_id'],
        'forum_id' => empty($this->params['forum_id']) ? 0 : $this->params['forum_id'],
        'categ_id' => empty($this->params['categ_id']) ? 0 : $this->params['categ_id'],
        'confirm_delete' =>1,
      );
      $entry = $this->entries[(int)$this->params['thread_id']];
      $msg = sprintf(
        $this->_gt('Delete thread "%s" (%d) from "%s"?'),
        $entry['entry_subject'],
        (int)$entry['entry_id'],
        $entry['entry_username']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get XML for delte entry form
  *
  * @access public
  */
  function getXMLDelEntryForm() {
    if (isset($this->entry) && is_array($this->entry)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_entry',
        'entry_id' => $this->entry['entry_id'],
        'forum_id' => $this->entry['forum_id'],
        'categ_id' => empty($this->params['categ_id']) ? 0 : $this->params['categ_id'],
        'last_entries' => $this->showLastEntries ? 1 : 0,
        'delete_all' => empty($this->params['delete_all']) ? 0 : (int)$this->params['delete_all'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete entry "%s" (%d) from "%s"?'),
        $this->entry['entry_subject'],
        (int)$this->entry['entry_id'],
        $this->entry['entry_username']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Initialize entry edit form
  *
  * @access public
  */
  function initializeEntryEditForm() {
    if (!(isset($this->entryDialog) && is_object($this->entryDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->entry;
      $hidden = array(
        'cmd' => 'edit_entry',
        'save' => 1,
        'entry_id' => $data['entry_id'],
        'forum_id' => $data['forum_id'],
        'categ_id' => empty($this->params['categ_id']) ? 0 : $this->params['categ_id'],
        'thread_offset' => empty($this->params['thread_offset']) ?
          0 : $this->params['thread_offset'],
        'board_offset' => empty($this->params['board_offset']) ?
          0 : $this->params['board_offset']
      );
      $fields = array(
        'entry_subject' => array('Subject', 'isNoHTML', TRUE, 'input', 200),
        'entry_text' => array('Message', 'isSomeText', TRUE, 'textarea', 8),
        'entry_username' => array('Username', 'isNoHTML', TRUE, 'input', 100),
        'entry_useremail' => array('EMail', 'isEMail', FALSE, 'input', 150),
        'entry_userregistered' => array(
          'Registered User', 'isNum', FALSE, 'yesno', 1, '', 0, 'left'
        ),
        'entry_sendanswers' => array(
          'Send answers', 'isNum', FALSE, 'yesno', 1, '', 0, 'left'
        ),
        'entry_ip' => array('IP', 'isNoHTML', TRUE, 'disabled_input', 20),
      );

      $this->entryDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->entryDialog->textYes = $this->_gt('Yes');
      $this->entryDialog->textNo = $this->_gt('No');
      $this->entryDialog->msgs = &$this->msgs;
      $this->entryDialog->loadParams();
    }
  }

  /**
  * Get XML for entry form
  *
  * @access public
  */
  function getXMLEntryForm() {
    if (isset($this->entry) && is_array($this->entry)) {
      $this->initializeEntryEditForm();
      $this->entryDialog->inputFieldSize = $this->inputFieldSize;
      $this->entryDialog->baseLink = $this->baseLink;
      $this->entryDialog->dialogTitle = htmlspecialchars($this->_gt('Properties'));
      $this->entryDialog->dialogDoubleButtons = FALSE;
      $this->layout->add($this->entryDialog->getDialogXML());
    }
  }

  /**
  * Get XML for thread list
  *
  * @access public
  */
  function getXMLThreadList() {
    if (isset($this->topics) && is_array($this->topics)) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Threads'))
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Subject'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Created'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Answers'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Modified'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->entries as $thread) {
        $result .= $this->getXMLThreadElement($thread['entry_id'], 0);
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->add($result);
    }
  }

  /**
  * Get XML for thread list
  *
  * @access public
  */
  function getXMLTopicsList() {
    if (isset($this->topics) && is_array($this->topics)) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Threads'))
      );
      $result .= $this->generatePagingLinksXml(
        'thread_offset',
        $this->_pagingParams,
        $this->params['thread_offset'],
        $this->_threadPagingLimit,
        $this->topicCount
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Subject'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Created'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Answers'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Modified'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->topics as $thread) {
        $result .= $this->getXMLEntry($thread, NULL, 0);
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->add($result);
    }
  }

  /**
  * Get XML for thread output
  *
  * @access public
  */
  function getXMLThread() {
    $result = sprintf(
      '<listview width="100%%" title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Thread'))
    );
    $result .= '<cols>';
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Subject'))
    );
    $result .= sprintf(
      '<col align="center">%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Username'))
    );
    $result .= sprintf(
      '<col align="center">%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Created'))
    );
    $result .= sprintf(
      '<col align="center">%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Modified'))
    );
    $result .= '</cols>';
    $result .= '<items>';
    $result .= $this->getXMLThreadElement($this->params['thread_id'], 0);
    $result .= '</items>';
    $result .= '</listview>';
    $this->layout->add($result);
  }

  /**
  * Get XML for entry listitem
  * @param array $entry
  * @param integer $threadId
  * @param integer $indent
  * @return string
  */
  function getXMLEntry($entry, $threadId, $indent = 0) {
    $result = '';
    $id = ($threadId == NULL) ? $entry['entry_id'] : $threadId;
    if (isset($this->params['entry_id'])) {
      $selected = ($entry['entry_id'] == $this->params['entry_id']) ? ' selected="selected"' : '';
    } else {
      $selected = '';
    }
    $result .= sprintf(
      '<listitem href="%s" title="%s" image="%s" indent="%d" %s>',
      papaya_strings::escapeHTMLChars(
        $this->getLink(
          array(
            'forum_id' => empty($this->params['forum_id']) ? 0 : (int)$this->params['forum_id'],
            'categ_id' => empty($this->params['categ_id']) ? 0 : (int)$this->params['categ_id'],
            'thread_id' => (int)$id,
            'entry_id' => (int)$entry['entry_id'],
            'offset' => empty($this->params['offset']) ? 0 : (int)$this->params['offset'],
            'board_offset' => empty($this->params['board_offset']) ?
              0 : (int)$this->params['board_offset'],
            'surfer_offset' => empty($this->params['surfer_offset']) ?
              0 : (int)$this->params['surfer_offset'],
            'thread_offset' => empty($this->params['thread_offset']) ?
              0 : (int)$this->params['thread_offset'],
          )
        )
      ),
      papaya_strings::escapeHTMLChars($entry['entry_subject']),
      papaya_strings::escapeHTMLChars($this->images['items-message']),
      (int)$indent,
      $selected
    );
    $result .= sprintf(
      '<subitem align="center">%s</subitem>',
      papaya_strings::escapeHTMLChars($entry['entry_username'])
    );
    $result .= sprintf(
      '<subitem align="center">%s</subitem>',
      date('Y-m-d H:i:s', $entry['entry_created'])
    );
    $result .= sprintf(
      '<subitem align="center">%s</subitem>',
      date('Y-m-d H:i:s', $entry['entry_modified'])
    );
    $result .= '</listitem>';

    return $result;
  }

  /**
  * Get XML for thread element output
  *
  * @param integer $id
  * @param integer $indent
  * @access public
  * @return string
  */
  function getXMLThreadElement($id, $indent) {
    $result = '';
    $threadId = $this->params['thread_id'];
    if (isset($this->entries[$id])) {
      $entry = $this->entries[$id];
    } elseif (isset($this->topics[0])) {
      $entry = $this->topics[0];
    } else {
      return '';
    }
    $result = $this->getXMLEntry($entry, $threadId, $indent);
    if ($entry['entry_thread_count'] > 0) {
      if (isset($this->entryTree[$id])) {
        foreach ($this->entryTree[$id] as $pid => $childId) {
          $result .= $this->getXMLThreadElement($childId, $indent + 1);
        }
      }
    }
    return $result;
  }

  /**
  * Get XML sub thread
  *
  * @param integer $pid
  * @param integer $indent
  * @access public
  * @return string
  */
  function getXMLSubThread($pid, $indent) {
    $result = '';
    /* Endless recursion possible ! */
    if ($indent > $this->maxIndent) {
      return $result;
    }

    if (isset($this->entryTree[$pid]) && is_array($this->entryTree[$pid])) {
      foreach ($this->entryTree[$pid] as $id) {
        $result .= $this->getXMLThreadElement($id, $indent);
      }
    }
    return $result;
  }

  /**
  * Cut category to the clipboard.
  *
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function cutCateg () {
    $data = array('forumcat_prev' => -1);
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableCategs, $data, 'forumcat_id', (int)$this->params['categ_id']
    );
  }

  /**
  * Cut Forum to the clipboard
  *
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function cutForum () {
    $data = array('forumcat_id' => -1);
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableBoards, $data, 'forum_id', (int)$this->params['forum_id']
    );
  }

  /**
  * Cut Thread to the clipboard
  *
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function cutThread () {
    $data = array('forum_id'=>'-1');
    $cond = array('entry_id' => $this->params['thread_id']);
    return FALSE !== $this->databaseUpdateRecord($this->tableEntries, $data, $cond);
  }

  /**
  * Check if exists cut Elements
  *
  * @access public
  * @return boolean
  */
  function cutOutExists () {
    $exists = FALSE;
    $sql = "SELECT COUNT(*) FROM %s WHERE forumcat_prev = '%d'";
    $params = array($this->tableCategs, -1);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($res->fetchField() > 0) {
        $exists = TRUE;
      }
    }
    if (!$exists) {
      $sql = "SELECT COUNT(*) FROM %s WHERE forumcat_id = '%d'";
      $params = array($this->tableBoards, -1);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($res->fetchField() > 0) {
          $exists = TRUE;
        }
      }
    }
    if (!$exists) {
      $sql = "SELECT COUNT(*) FROM %s WHERE forum_id = '%d'";
      $params = array($this->tableEntries, -1);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($res->fetchField() > 0) {
          $exists = TRUE;
        }
      }
    }
    return $exists;
  }

  /**
  * Get XML for Clipboard
  *
  * @access public
  */
  function getXMLCutList() {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Clipboard'))
      );
      $result .= '<items>'.LF;
      $result .= $this->getXMLCutElements();
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
  }

  /**
  * Get XML of elements for clipboard
  *
  * @access public
  * @return string $result XML
  */
  function getXMLCutElements() {
    $result = '';
    if (isset($this->cutCategs) && is_array($this->cutCategs)) {
      foreach ($this->cutCategs as $cutCateg) {
        $result .= sprintf(
          '<listitem title="%s" image="%s">'.LF,
          papaya_strings::escapeHTMLChars($cutCateg['forumcat_title']),
          papaya_strings::escapeHTMLChars($this->images['items-folder'])
        );
        $result .= '<subitem align="right">'.LF;
        $result .= sprintf(
          '<a href="%s">'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd'=>'paste_categ',
                'cut_categ_id' => (int)$cutCateg['forumcat_id'],
                'categ_id' => $this->params['categ_id'],
                'forum_id' => 0,
                'entry_id' => 0
              )
            )
          )
        );
        $result .= sprintf(
          '<glyph src="%s" hint="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->images['actions-edit-paste']),
          papaya_strings::escapeHTMLChars($this->_gt('Paste category'))
        );
        $result .= '</a>'.LF;
        $result .= '</subitem>'.LF;
        $result .= '</listitem>'.LF;
      }
    }
    if (isset($this->cutList) && is_array($this->cutList)) {
      foreach ($this->cutList as $cutForum) {
        $result .= sprintf(
          '<listitem title="%s" image="%s">'.LF,
          papaya_strings::escapeHTMLChars($cutForum['forum_title']),
          papaya_strings::escapeHTMLChars($this->images['items-folder'])
        );
        $result .= '<subitem align="right">'.LF;
        $result .= sprintf(
          '<a href="%s">'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd'=>'paste_forum',
                'categ_id' => $this->params['categ_id'],
                'cut_forum_id'=>(int)$cutForum['forum_id']
              )
            )
          )
        );
        $result .= sprintf(
          '<glyph src="%s" hint="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->images['actions-edit-paste']),
          papaya_strings::escapeHTMLChars($this->_gt('Paste forum'))
        );
        $result .= '</a>'.LF;
        $result .= '</subitem>'.LF;
        $result .= '</listitem>'.LF;
      }
    }

    if (isset($this->cutThreads) && is_array($this->cutThreads)) {
      foreach ($this->cutThreads as $cutThread) {
        $result .= sprintf(
          '<listitem title="%s" image="%s">'.LF,
          papaya_strings::escapeHTMLChars($cutThread['entry_subject']),
          papaya_strings::escapeHTMLChars($this->images['items-message'])
        );
        $result .= '<subitem align="right">'.LF;
        $result .= sprintf(
          '<a href="%s">'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd'=>'paste_thread',
                'cut_thread_id'=>(int)$cutThread['entry_id'],
                'forum_id' => empty($this->params['forum_id'])
                  ? 0 : (int)$this->params['forum_id'],
                'categ_id' => empty($this->params['categ_id'])
                  ? 0 : (int)$this->params['categ_id'],
                'thread_id' => empty($this->params['thread_id'])
                  ? 0 : (int)$this->params['thread_id']
              )
            )
          )
        );
        $result .= sprintf(
          '<glyph src="%s" hint="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->images['actions-edit-paste']),
          papaya_strings::escapeHTMLChars($this->_gt('Paste thread'))
        );
        $result .= '</a>'.LF;
        $result .= '</subitem>'.LF;
        $result .= '</listitem>'.LF;
      }
    }
    return $result;
  }

  /**
  * Load Elements from clipboard
  *
  * @access public
  */
  function loadCutElement() {
    $sql = "SELECT forumcat_id,
                   forumcat_prev,
                   forumcat_title
              FROM %s
              WHERE forumcat_prev = '-1'";
    if ($res = $this->databaseQueryFmt($sql, $this->tableCategs)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->cutCategs[(int)$row['forumcat_id']] = $row;
      }
    }
    $sql = "SELECT forum_id,
                   forumcat_id,
                   forum_title
              FROM %s
             WHERE forumcat_id = '-1'";
    if ($res = $this->databaseQueryFmt($sql, $this->tableBoards)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->cutList[(int)$row['forum_id']] = $row;
      }
    }
    $sql = "SELECT entry_id,
                   entry_subject
              FROM %s
             WHERE forum_id = '-1'
               AND entry_pid = '0'";
    if ($res = $this->databaseQueryFmt($sql, $this->tableEntries)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->cutThreads[(int)$row['entry_id']] = $row;
      }
    }
  }

  /**
  * Paste category from clipboard into category tree.
  *
  * @param integer $parent id of new parent category
  * @param integer $categ id of category to paste
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function pasteCateg($parent, $categ) {
    if ($parent == 0) {
      $forumcatPath = ';0;';
    } else {
      if (isset($this->categs[$parent])) {
        $forumcatPath = $this->categs[$parent]['forumcat_path'].$parent.';';
      } else {
        $categ = $this->loadCateg($parent);
        $forumcatPath = $categ['forumcat_path'].$parent.';';
      }
    }
    $data = array(
      'forumcat_prev' => $parent,
      'forumcat_path' => $forumcatPath
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableCategs,
      $data,
      'forumcat_id',
      (int)$categ
    );
  }

  /**
  * Paste forum from clipboard into selected category.
  *
  * @param integer $linkId
  * @param integer $categId
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function pasteForum ($forumId, $categId) {
    $data = array('forumcat_id' => $categId);
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableBoards, $data, 'forum_id', (int)$this->params['cut_forum_id']
    );
  }

  /**
  * Paste thread from clipboard into catgory.
  *
  * @access public
  * @return boolean updated or not
  */
  function pasteThread () {
    $data = array('forum_id' => $this->params['forum_id']);
    $cond = array('entry_id' => $this->params['cut_thread_id']);
    return FALSE !== $this->databaseUpdateRecord($this->tableEntries, $data, $cond);
  }

  /**
  * Calculate preview path
  *
  * @param integer $categId
  * @access public
  * @return string
  */
  function calcPrevPath($categId) {
    if ($categId > 0 && isset($this->categs[$categId])) {
      $categ = $this->categs[$categId];
      if (!isset($categ['newpath'])) {
        if ($categ['forumcat_prev'] > 0) {
          if (isset($this->categs[$categ['forumcat_prev']])) {
            $newPath = $this->calcPrevPath($categ['forumcat_prev']).$categ['forumcat_prev'].';';
          } else {
            $newPath = ';0;';
          }
        } else {
          $newPath = ';0;';
        }
        $this->categs[$categId]['newpath'] = $newPath;
        return $newPath;
      } else {
        return $categ['newpath'];
      }
    }
    return '';
  }

  /**
  * Repair paths in category tree structure.
  *
  * @access public
  */
  function repairPaths() {
    $this->loadCategs(NULL, TRUE);
    $count = 0;
    if (isset($this->categs) && is_array($this->categs)) {
      foreach ($this->categs as $categId=>$categ) {
        if ($categId > 0) {
          $oldPath = $categ['forumcat_path'];
          $path = $this->calcPrevPath($categId);
          if ($oldPath != $path) {
            $data = array('forumcat_path'=>$path);
            $this->databaseUpdateRecord(
              $this->tableCategs, $data, 'forumcat_id', $categId
            );
            $count++;
          }
        }
      }
      if ($count > 0) {
        $this->addMsg(MSG_INFO, sprintf($this->_gt('%s category paths changed.'), $count));
      }
    }
    $count = $this->fixEntryTree();
    if ($count > 0) {
      $this->addMsg(MSG_INFO, sprintf($this->_gt('%s entry paths changed.'), $count));
    }
  }

  /**
  * Calculate path for tree entry item
  * @param array $entries
  * @param integer $entryId
  * @return array
  */
  function getEntryTreePath(&$entries, $entryId) {
    if (empty($entries[$entryId])) {
      return '';
    } if (isset($entries[$entryId]['NEW_PATH'])) {
      return $entries[$entryId]['NEW_PATH'];
    } elseif ($entries[$entryId]['entry_pid'] == 0) {
      return $entries[$entryId]['NEW_PATH'] = '';
    } else {
      $parentId = $entries[$entryId]['entry_pid'];
      $newPath = $this->getEntryTreePath($entries, $parentId).$parentId.';';
      if (substr($newPath, 0, 1) != ';') {
        $entries[$entryId]['NEW_PATH'] = ';'.$newPath;
      } else {
        $entries[$entryId]['NEW_PATH'] = $newPath;
      }
      return $entries[$entryId]['NEW_PATH'];
    }
  }

  /**
  * Fixes syntactical errors of the entry path of all entries.
  *
  * @return int the number of affected entries.
  */
  function fixEntryTree() {
    $sql = "SELECT entry_id, entry_pid, entry_path
              FROM %s";
    $params = array($this->tableEntries);
    $entries = array();
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['entry_pid'] == $row['entry_id']) {
          $row['entry_pid'] = 0;
          $row['UPDATE'] = 0;
        }
        $entries[$row['entry_id']] = $row;
      }
    }
    $count = 0;
    foreach ($entries as $entry) {
      $newPath = $this->getEntryTreePath($entries, $entry['entry_id']);
      if (isset($entry['UPDATE']) || $entry['entry_path'] != $newPath) {
        $entryPathIds = explode(';', $newPath);
        $entryPid = (isset($entryPathIds[count($entryPathIds) - 2])) ?
          $entryPathIds[count($entryPathIds) - 2] : 0;
        $this->databaseUpdateRecord(
          $this->tableEntries,
          array('entry_path' => $newPath, 'entry_pid' => $entryPid),
          'entry_id',
          $entry['entry_id']
        );
        $count++;
      }
    }
    return $count;
  }

  /**
  * Creates a combo box control that contains all categories and
  * forums.
  *
  * @param string $paramName  Parameter name all field names are associated with
  * @param string $fieldName  Name of the field containing the selected option
  * @param array $data List of information to be set in the combobox
  * @return string XML representation of categories and forums as combo box
  */
  function getForumCombo($paramName, $fieldName, $data) {
    $this->loadCategs();
    $this->loadBoards();
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($paramName),
      papaya_strings::escapeHTMLChars($fieldName)
    );
    if (isset($this->categs) && is_array($this->categs)) {
      $result .= $this->getForumComboSubTree(0, 0, $data);
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get forum combo sub tree
  *
  * @param integer $parent
  * @param integer $indent
  * @param array $data
  * @access public
  * @return string
  */
  function getForumComboSubTree($parent, $indent, $data) {
    $result = '';
    if (isset($this->categTree[$parent]) && is_array($this->categTree[$parent])) {
      foreach ($this->categTree[$parent] as $id) {
        $result .= $this->getForumComboCateg($id, $indent, $data);
      }
    }
    return $result;
  }

  /**
  * Get forum combo category
  *
  * @param integer $id
  * @param integer $indent
  * @param array $data
  * @access public
  * @return string
  */
  function getForumComboCateg($id, $indent, $data) {
    $result = '';
    if (isset($this->categs[$id]) && is_array($this->categs[$id])) {
      $categ = $this->categs[$id];
      $indentString = ($indent > 0) ? "'".str_repeat('-', $indent).'->' : '';
      $selected = (!empty($data['id']) && $data['id'] == $id && $data['mode'] == 'categ')
        ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="categ;%d" %s>%s %s</option>'.LF,
        (int)$id,
        $selected,
        papaya_strings::escapeHTMLChars($indentString),
        papaya_strings::escapeHTMLChars($this->categs[$id]['forumcat_title'])
      );
      if (!empty($categ['boards']) && is_array($categ['boards'])) {
        foreach ($categ['boards'] as $boardId) {
          $board = $this->boards[$boardId];
          $indentString = "'".str_repeat('-', $indent * 4).'->';
          if (isset($board) && is_array($board)) {
            if (isset($data['id']) && $board['forum_id'] == $data['id'] &&
                $data['mode'] == 'forum') {
              $selected = ' selected="selected"';
            } else {
              $selected = '';
            }
            $result .= sprintf(
              '<option value="forum;%d" %s>%s %s</option>',
              (int)$board['forum_id'],
              $selected,
              papaya_strings::escapeHTMLChars($indentString),
              papaya_strings::escapeHTMLChars($board['forum_title'])
            );
          }
        }
      }
      $result .= $this->getForumComboSubTree($id, $indent + 1, $data);
    }
    return $result;
  }

  /**
  * Get xml for last entries listview.
  * @return void
  */
  function getXMLLastEntries() {
    if (isset($this->entries) && is_array($this->entries) && count($this->entries) > 0) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Last entries'))
      );
      $result .= '<items>';
      foreach ($this->entries as $entryId => $entry) {
        $tid = ($entry['entry_pid'] > 0) ? $entry['entry_pid'] : $entry['entry_id'];
        $icon = $this->images['items-message'];

        // determine state of post
        if ($entry['entry_blocked'] > 0) {
          $icon = $this->images['status-user-evil'];
        }

        $linkData = array(
          'entry_id' => $entryId,
          'thread_id' => $tid
        );

        if (isset($entry['forumcat_id'])) {
          $linkData['categ_id'] = $entry['forumcat_id'];
          $linkData['board_offset'] = $this->getBoardOffset(
            $entry['forumcat_id'],
            $entry['forum_id']
          );
        }
        if (isset($entry['forum_id'])) {
          $linkData['forum_id'] = $entry['forum_id'];
          $linkData['thread_offset'] = $this->getThreadOffset($entry['forum_id'], $tid);
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s">',
          papaya_strings::escapeHTMLChars($entry['entry_subject']),
          papaya_strings::escapeHTMLChars(
            $this->getLink($linkData)
          ),
          papaya_strings::escapeHTMLChars($icon)
        );
        $result .= sprintf(
          '<subitem>%s</subitem>',
          date('Y-m-d H:i:s', $entry['entry_modified'])
        );
        $result .= sprintf(
          '<subitem>%s</subitem>',
          papaya_strings::escapeHTMLChars($entry['entry_username'])
        );

        // action icons
        // action: (un)block entry
        if ($entry['entry_blocked'] > 0) {
          $blockCommand = 'unblock_entry';
          $blockIcon = $this->images['status-user-angel'];
          $blockHint = 'Unblock entry';
        } else {
          $blockCommand = 'block_entry';
          $blockIcon = $this->images['status-user-evil'];
          $blockHint = 'Block entry';
        }
        $params = array(
          'cmd'=> $blockCommand,
          'entry_id' => $entryId
        );
        if (isset($entry['forum_id'])) {
          $params['forum_id'] = $entry['forum_id'];
        }
        if (isset($entry['forumcat_id'])) {
          $params['categ_id'] = $entry['forumcat_id'];
        }
        $result .= sprintf(
          '<subitem><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
          papaya_strings::escapeHTMLChars(
            $this->getLink($params)
          ),
          papaya_strings::escapeHTMLChars($blockIcon),
          papaya_strings::escapeHTMLChars($this->_gt($blockHint))
        );

        // community functions
        $surferId = NULL;
        if (isset($entry['entry_userguid']) && !empty($entry['entry_userguid'])) {
          $surferId = $entry['entry_userguid'];
        } elseif (isset($entry['entry_userhandle']) && !empty($entry['entry_userhandle'])) {
          $surferId = $this->_communityObject->getIdByHandle($entry['entry_userhandle']);
        } else {
          $surferId = $this->_communityObject->getIdByHandle($entry['entry_username']);
        }
        if (!empty($surferId)) {
          $surfer = $this->getCommunitySurfer($surferId);
          if ($surfer['surfer_valid'] == 3) {
            $icon = $this->images['status-user-locked'];
            $surferCmd = 'unblock_surfer';
            $caption = $this->_gt('Unblock surfer');
          } elseif ($surfer['surfer_valid'] == 1) {
            $icon = $this->images['items-user'];
            $surferCmd = 'block_surfer';
            $caption = $this->_gt('Block surfer');
          } else {
            $icon = $this->images['items-user'];
            $surferCmd = 'block_surfer';
            $caption = $this->_gt('Block surfer');
          }

          $result .= sprintf(
            '<subitem><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
            $this->getLink(
              array(
                'cmd'=> $surferCmd,
                'surfer_id' => $surferId
              )
            ),
            papaya_strings::escapeHTMLChars($icon),
            papaya_strings::escapeHTMLChars($this->_gt($caption))
          );
        } else {
          $result .= '<subitem/>';
        }

        // action: delete
        $result .= sprintf(
          '<subitem><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'categ_id' => (isset($entry['forumcat_id']) ? $entry['forumcat_id'] : 0),
                'forum_id' => (isset($entry['forum_id']) ? $entry['forum_id'] : 0),
                'cmd' => 'del_entry',
                'entry_id' => $entryId,
                'last_entries' => $this->showLastEntries ? 1 : 0
              )
            )
          ),
          papaya_strings::escapeHTMLChars($this->images['places-trash']),
          papaya_strings::escapeHTMLChars($this->_gt('Delete entry'))
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->add($result);
    }
  }

  /**
  * Get the link to a surfer into the communty backend
  *
  * @param string $surferId
  * @return string
  */
  function getCommunitySurferLink($surferId) {
    $community = $this->getCommunityObject();
    return $community->getBackendSurferLink($surferId);
  }

  /**
  * Get the link to a surfer into the communty backend
  *
  * @param string $surferId
  * @return string
  */
  function getCommunitySurfer($surferId) {
    $community = $this->getCommunityObject();
    return $community->loadSurfer($surferId);
  }

  /**
  * Get an instance of the community connector
  *
  * @return connector_surfers
  */
  function getCommunityObject() {
    if (!(isset($this->_communityObject) && is_object($this->_communityObject))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->_communityObject = base_pluginloader::getPluginInstance(
        '06648c9c955e1a0e06a7bd381748c4e4', $this
      );
    }
    return $this->_communityObject;
  }

  /**
  * Get all surfers ever did apost
  * @return unknown_type
  */
  function getEntriesSurfers($limit = 20, $offset = 0) {
    $surfer =
      $this->loadAllEntryUsers($limit, $offset);

    $community = $this->getCommunityObject();
    $surfers = $community->loadSurfers(array_keys($surfer), 'ASC', $limit, 0, TRUE);

    uasort($surfers, 'sortSurferByGivenAndSurname');
    return $surfers;
  }

  /**
  * Add a search dialog to the layout
  */
  function getSearchDialog() {
    $dialog = $this->generateSearchDialogXml();
    $this->layout->addLeft($dialog);
  }

  /**
  * Generate a search dialog xml
  * @return string search dialog xml
  */
  function generateSearchDialogXml() {
    $searchDialog = '';
    if (isset($this->sessionParams['panel_state']['search']) &&
        $this->sessionParams['panel_state']['search'] == 'open') {
      $resize = sprintf(
        ' minimize="%s"',
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'close_panel', 'panel' => 'search'))
        )
      );
      $searchDialog .= sprintf(
        '<dialog action="%s" method="post" title="%s" %s>'.LF,
        papaya_strings::escapeHTMLChars($this->getBaseLink()),
        papaya_strings::escapeHTMLChars($this->_gt('Search')),
        $resize
      );
      $searchDialog .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="search" />',
        papaya_strings::escapeHTMLChars($this->paramName)
      );

      if (isset($this->_searchResult['count']) &&
        $this->_searchResult['count'] > 0 &&
        isset($this->_searchResult['offset']) &&
        $this->_searchResult['offset'] > 0) {
        $searchDialog .= sprintf(
          '<input type="hidden" name="%s[search_offset]" value="%d" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          (int)$this->_searchResult['offset']
        );
      }

      $searchDialog .= '<lines class="dialogXSmall">'.LF;
      if (!empty($this->params['search_string'])) {
        $searchString = $this->params['search_string'];
      } else {
        $searchString = '';
      }
      $searchDialog .= '<line>'.LF;
      $searchDialog .= sprintf(
        '<input type="text" name="%s[search_string]" value="%s" class="dialogInput dialogScale"/>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($searchString)
      );
      $searchDialog .= '</line>'.LF;
      $searchDialog .= '</lines>'.LF;
      if (isset($this->_searchResult['count']) &&
        $this->_searchResult['count'] > 0 &&
        isset($this->_searchResult['offset'])) {
        $searchDialog .= sprintf(
          '<dlgbutton name="%s[search_next]" value="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($this->_gt('Next'))
        );
        $searchDialog .= sprintf(
          '<dlgbutton value="%s" align="left"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Search new'))
        );
      } else {
        $searchDialog .= sprintf(
          '<dlgbutton value="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Search'))
        );
      }
      $searchDialog .= '</dialog>'.LF;
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
      $searchDialog .= sprintf(
        '<listview title="%s" %s />'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Search')),
        $resize
      );
    }
    return $searchDialog;
  }

  /**
  * Find a forum entry matching the search string
  *
  * It is either possible to use an integer to find a specific entry
  * or a string to find a set of entries
  *
  * @param string|integer $searchFor Search phrase
  * @param integer $offset Offset the result shall be returned at
  * @return array List of information about the search result
  */
  function searchForumEntries($searchFor, $offset=NULL) {
    if (preg_match('(^\d+$)', $searchFor)) {
      $entry = $this->loadEntry((int)$searchFor);
      if ($entry) {
        $entry = $this->entry;
      }
      $absCount = 1;
    } else {
      $filter = array();
      $filter['search_string'] = $searchFor;
      $entry = $this->getEntriesByFilter($filter, 1, $offset);
      if (is_array($entry) && count($entry) > 0) {
        $entry = current($entry);
      }
      $absCount = $this->getEntriesTotalCount();
      if ((int)$offset + 1 == $absCount) {
        $offset = 0;
      } elseif ($absCount > 0) {
        $offset = (int)$offset + 1;
      }
    }
    if (empty($entry)) {
      return array();
    } else {
      $forumId = (isset($entry['forum_id'])) ?
        $entry['forum_id'] : 0;

      $this->params['forum_id'] = $forumId;
      $categoryId = $this->getCategoryIdByForumId($forumId);
      $this->params['categ_id'] = $categoryId;
      if (isset($entry['entry_id'])) {
        $this->params['thread_id'] = $entry['entry_id'];
        $this->params['entry_id'] = $entry['entry_id'];
      }

      $this->entry = $entry;

      $returnValue = array(
        'count' => $absCount,
        'offset' => $offset,
        'thread_id' => $entry['entry_id'],
        'forum_id' => $forumId,
        'categ_id' => $categoryId
      );
      return $returnValue;
    }
  }

  /**
  * This method initializes the basic properties of the forum
  */
  function initializeForum() {
    if (!isset($this->params['categ_id'])) {
      $this->params['categ_id'] = 0;
    }

    if (isset($this->sessionParams['categopen']) &&
        is_array($this->sessionParams['categopen'])) {
      $this->categsOpen = $this->sessionParams['categopen'];
    } else {
      $this->categsOpen = array();
    }

    if (isset($this->params['forum_id'])) {
      $this->loadBoard($this->params['forum_id']);
      //proof if offset is set
      $topicsOffset = (isset($this->params['thread_offset'])) ?
        $this->params['thread_offset'] : 0;
      $topicsLimit = (isset($this->params['thread_limit'])) ?
        $this->params['thread_limit'] : $this->_threadPagingLimit;
      $this->loadTopics($this->params['forum_id'], $topicsOffset, $topicsLimit);
    }

    if (!empty($this->params['thread_id'])) {
      $this->loadEntry($this->params['thread_id']);
      $this->loadThread($this->params['thread_id']);
    }

    if (isset($this->params['categ_id'])) {
      $this->loadCategs($this->params['categ_id']);
      $this->loadCateg($this->params['categ_id']);
    } else {
      $this->loadCategs();
    }

    $this->_pagingParams = array(
      'board_offset' => $this->_boardPagingOffset,
      'surfer_offset' => $this->params['surfer_offset'],
      'thread_offset' => $this->params['thread_offset'],
      'surfer_limit' => $this->params['surfer_limit']
    );

    if (isset($this->params['categ_id'])) {
      $this->_pagingParams['categ_id'] = $this->params['categ_id'];
    }

    if (isset($this->params['forum_id'])) {
      $this->_pagingParams['forum_id'] = $this->params['forum_id'];
    }
  }

  /**
  * Generates the output xml for paging links.
  * @param string $paramName offset param name
  * @param array $baseParams
  * @param integer $offset
  * @param integer $limit
  * @param integer $count
  * @return string xml output
  */
  function generatePagingLinksXml($paramName = 'offset', $baseParams = array(),
                                 $offset = 0, $limit = 20, $count = 0) {
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_paging_buttons.php');
    return papaya_paging_buttons::getPagingButtons(
      $this,
      $baseParams,
      $offset,
      $limit,
      $count,
      9,
      $paramName
    );
  }

  /**
  * initialize limits for thread, board and surfer paging
  *
  */
  function initializePagingLimits() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');
    $this->_boardPagingLimit = base_module_options::readOption(
      $this->_edModuleGuid,
      'BOARD_LIMIT',
      10
    );
    $this->_surferPagingLimit = base_module_options::readOption(
      $this->_edModuleGuid,
      'SURFER_LIMIT',
      10
    );
    $this->_threadPagingLimit = base_module_options::readOption(
      $this->_edModuleGuid,
      'THREAD_LIMIT',
      10
    );
  }

  /**
  * get the offset of the current chosen thread if the offset is not available
  *
  * @param integer $threadId
  * @return integer $offset
  */
  function getThreadOffset($boardId, $threadId) {
    $offset = 0;
    $position = $this->getTopicPosition($boardId, $threadId);
    $offset = floor($position / $this->_threadPagingLimit) * $this->_threadPagingLimit;
    return $offset;
  }

  /**
  * get the offset of the current chosen board if the offset is not available
  *
  * @param integer $boardId
  * @return integer $offset
  */
  function getBoardOffset($categoryId, $boardId) {
    $offset = 0;
    $position = $this->getBoardPosition($categoryId, $boardId);
    $offset = floor($position / $this->_boardPagingLimit) * $this->_boardPagingLimit;
    return $offset;
  }


}

/**
* Sort list of surfers by sur-and givenname in ascending order
*
* @param array $a
* @param array $b
* @return interger
*/
function sortSurferByGivenAndSurname($a, $b) {

  $nameA = strtolower($a['surfer_givenname'].' '.$a['surfer_surname']);
  $nameB = strtolower($b['surfer_givenname'].' '.$b['surfer_surname']);

  return strcmp($nameA, $nameB);
}