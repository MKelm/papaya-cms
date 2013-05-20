<?php
/**
* Feed aggregation (planet) administration class
*
* Requires a link from [Default/Base - box] Extended Page Link
* where the referred page will be sent by email.
*
* Configured by xml-file
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
* @subpackage Beta-Planet
* @version $Id: admin_planet.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* feed aggragation base class
*/
require_once(dirname(__FILE__).'/base_planet.php');

/**
* Feed aggregation (planet) administration class
* @package Papaya-Modules
* @subpackage Beta-Planet
*/
class admin_planet extends base_planet {

  var $paramName = 'planet';

  var $feeds = array();
  var $feedsCount = 0;
  var $feedsLimit = 20;
  var $feedsOffset = 0;

  var $feed = NULL;

  var $entries = array();
  var $entriesCount = 0;
  var $entriesLimit = 20;
  var $entriesOffset = 0;
  var $entriesStatus = 0;

  /**
  * default status fallback (if a list is empty)
  * @var integer
  */
  var $defaultStatus = 0;
  /**
  * current feed entry details
  * @var array | NULL
  */
  var $entry = NULL;
  /**
  * entries count for current feed
  * @var array
  */
  var $entryCounts = NULL;

  var $cmds = array(
    'feed_add', 'feed_edit', 'feed_show', 'feed_update',
    'entries_mark_readed', 'entries_mark_new', 'entries_delete',
    'entry_show'
  );

  var $statusPriority = array(
    PAPAYA_PLANET_ENTRY_STATUS_NEW,
    PAPAYA_PLANET_ENTRY_STATUS_READED,
    PAPAYA_PLANET_ENTRY_STATUS_DELETED
  );

  /**
  * Initialize parameters
  * @return void
  */
  function initialize() {
    $this->initializeParams();
    if (isset($this->params['feed_limit'])) {
      $this->feedsLimit = (int)$this->params['feed_limit'];
    }
    if (isset($this->params['feed_offset'])) {
      $this->feedsOffset = (int)$this->params['feed_offset'];
    }
    if (isset($this->params['entry_limit'])) {
      $this->entriesLimit = (int)$this->params['entry_limit'];
    }
    if (isset($this->params['entry_offset'])) {
      $this->entriesOffset = (int)$this->params['entry_offset'];
    }
    if (isset($this->params['entry_status'])) {
      $this->entriesStatus = (int)$this->params['entry_status'];
    }
  }

  /**
  * execute administation actions
  *
  * @return void
  */
  function execute() {
    if (isset($this->params['cmd']) &&
        in_array($this->params['cmd'], $this->cmds)) {
      switch ($this->params['cmd']) {
      case 'feed_add' :
        if (isset($this->params['confirm_add']) && $this->params['confirm_add']) {
          $feedDialog = &$this->initializeAddFeedDialog();
          if ($feedDialog->checkDialogInput()) {
            if ($feed = $this->fetchFeed($feedDialog->data['feed_url'])) {
              $feedIdent = $feed->id->get();
              $feedTitle = $feed->title->getValue();
              if (!empty($feedIdent)) {
                if (!$this->feedExists($feedIdent)) {
                  $newId = $this->addFeed(
                    $feedIdent,
                    $feedTitle,
                    $feedDialog->data['feed_url']
                  );
                  if ($newId) {
                    $this->addMsg(MSG_INFO, $this->_gt('Feed added.'));
                    $this->updateFeed($newId, $feed);
                  }
                }
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('No feed id found.'));
              }
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Can not fetch feed.'));
            }
          }
        }
        break;
      case 'feed_delete' :

        break;
      case 'feed_update' :
        if (isset($this->params['feed_id']) &&
            $this->loadFeed($this->params['feed_id'])) {
          if ($feed = $this->fetchFeed($this->feed['feed_url'])) {
            if ($count = $this->updateFeed($this->feed['feed_id'], $feed)) {
              $this->addMsg(
                MSG_INFO,
                sprintf(
                  $this->_gt('%d entries updated.'),
                  $count
                )
              );
            } else {
              $this->addMsg(MSG_INFO, $this->_gt('Nothing to update.'));
            }
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Can not fetch feed.'));
          }
        }
        break;
      case 'entries_mark_readed' :
        if (isset($this->params['entry_ids']) &&
            is_array($this->params['entry_ids']) &&
            count($this->params['entry_ids']) > 0) {
          $this->setEntryStatus(
            $this->params['entry_ids'],
            PAPAYA_PLANET_ENTRY_STATUS_READED
          );
        }
        break;
      case 'entries_mark_new' :
        if (isset($this->params['entry_ids']) &&
            is_array($this->params['entry_ids']) &&
            count($this->params['entry_ids']) > 0) {
          $this->setEntryStatus(
            $this->params['entry_ids'],
            PAPAYA_PLANET_ENTRY_STATUS_NEW
          );
        }
        break;
      case 'entries_deleted' :
        break;
      }
    }
    $this->loadFeedsList($this->feedsLimit, $this->feedsOffset);
    $this->loadEntryCounts(array_keys($this->feeds));
    if (isset($this->params['feed_id'])) {
      $this->loadFeed($this->params['feed_id']);
      $this->loadEntryCounts($this->params['feed_id']);
      $this->defaultStatus = $this->getDefaultStatus($this->params['feed_id']);
      $this->loadFeedEntries(
        $this->params['feed_id'],
        $this->entriesLimit,
        $this->entriesOffset,
        $this->defaultStatus
      );
    } else {
      $this->loadEntryCounts();
      $this->defaultStatus = $this->getDefaultStatus();
      $this->loadEntries(
        $this->entriesLimit,
        $this->entriesOffset,
        $this->defaultStatus
      );
    }
    if (isset($this->params['entry_id'])) {
      $this->loadEntry($this->params['entry_id']);
    }
  }

  /**
  * Get administration page xml
  * @return void
  */
  function getXML() {
    if (isset($this->params['cmd']) &&
        in_array($this->params['cmd'], $this->cmds)) {
      switch ($this->params['cmd']) {
      case 'feed_add' :
        $this->getDialogAddFeed();
        break;
      case 'entry_show' :
        $this->getXMLFeedDetails();
        $this->getXMLEntryDetails();
        break;
      case 'planet_show' :
      case 'feed_show' :
      case 'entries_mark_new' :
      case 'entries_mark_readed' :
        $this->getXMLFeedDetails();
        $this->getXMLEntriesList();
        break;
      }
    } else {
      //no command or unknown command - default output
      if (count($this->entries) > 0) {
        //output a list of entries
        $this->getXMLEntriesList();
      } elseif (count($this->feeds) <= 0) {
        //no feeds in planet - output add feed dialog
        $this->getDialogAddFeed();
      }
    }
    $this->getXMLFeedsList();
    $this->getXMLButtons();
  }

  /**
  * Get menu bar buttons xml
  * @return void
  */
  function getXMLButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $menu = new base_btnbuilder();
    $menu->addButton(
      'Add feed',
      $this->getLink(
        array(
          'cmd' => 'feed_add'
        )
      ),
      $this->module->getIconURI('feed-add.png'),
      '',
      (isset($this->params['cmd']) && $this->params['cmd'] == 'feed_add')
    );
    if (isset($this->feed)) {
      $menu->addButton(
        'Delete feed',
        $this->getLink(
          array(
            'cmd' => 'feed_delete',
            'feed_id' => $this->feed['feed_id']
          )
        ),
        $this->module->getIconURI('feed-delete.png'),
        '',
        (isset($this->params['cmd']) && $this->params['cmd'] == 'feed_delete')
      );
      $menu->addSeperator();
      $menu->addButton(
        'Update feed',
        $this->getLink(
          array(
            'cmd' => 'feed_update',
            'feed_id' => $this->feed['feed_id']
          )
        ),
        $this->images['actions-refresh']
      );
    }
    if ($str = $menu->getXML()) {
      $this->layout->addMenu('<menu ident="edit">'.$str.'</menu>');
    }
  }

  /**
  * Get feeds listview xml
  * @return void
  */
  function getXMLFeedsList() {
    if (count($this->feeds) > 0) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Feeds'))
      );
      $result .= '<items>'.LF;
      foreach ($this->feeds as $feedId => $feedData) {
        if (isset($this->params['feed_id']) &&
            $this->params['feed_id'] == $feedId) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        if (isset($this->entryCounts[$feedId][PAPAYA_PLANET_ENTRY_STATUS_NEW]) &&
            $this->entryCounts[$feedId][PAPAYA_PLANET_ENTRY_STATUS_NEW] > 0) {
          $emphased = ' emphased="emphased"';
          $title = $feedData['feed_title'].' ('.
            $this->entryCounts[$feedId][PAPAYA_PLANET_ENTRY_STATUS_NEW].')';
        } else {
          $emphased = '';
          $title = $feedData['feed_title'];
        }
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s"%s%s/>'.LF,
          papaya_strings::escapeHTMLChars($title),
          $this->getLink(
            array(
              'cmd' => 'feed_show',
              'feed_id' => $feedId,
              'feed_limit' => $this->feedsLimit,
              'feed_offset' =>  $this->feedsOffset
            )
          ),
          papaya_strings::escapeHTMLChars($this->module->getIconURI('feed.png')),
          $selected,
          $emphased
        );
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get feed details information listview
  * @return void
  */
  function getXMLFeedDetails() {
    if (isset($this->feed)) {
      $result = sprintf(
        '<listview title="%s" icon="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Feed')),
        papaya_strings::escapeHTMLChars($this->module->getIconURI('feed.png'))
      );
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_paging_buttons.php');
      $result .= papaya_paging_buttons::getPagingButtons(
        $this,
        array(),
        $this->feedsOffset,
        $this->feedsLimit,
        $this->feedsCount,
        5,
        $paramName = 'feed_offset'
      );
      $result .= '<items>';
      $result .= sprintf(
        '<listitem title="%s"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Title')),
        papaya_strings::escapeHTMLChars($this->feed['feed_title'])
      );
      $result .= sprintf(
        '<listitem title="%s"><subitem><a href="%2$s" target="_blank">%2$s</a>'.
          '</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('URL')),
        papaya_strings::escapeHTMLChars($this->feed['feed_url'])
      );
      if ($this->feed['feed_title'] != $this->feed['feed_title_fetched']) {
        //show original feed title if it is different
        $result .= sprintf(
          '<listitem title="%s"><subitem>%s</subitem></listitem>',
          papaya_strings::escapeHTMLChars($this->_gt('Original title')),
          papaya_strings::escapeHTMLChars($this->feed['feed_title_fetched'])
        );
      }
      $result .= sprintf(
        '<listitem title="%s"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Added')),
        date('Y-m-d H:i', $this->feed['feed_added'])
      );
      $result .= sprintf(
        '<listitem title="%s"><subitem>%s</subitem></listitem>',
        papaya_strings::escapeHTMLChars($this->_gt('Updated')),
        date('Y-m-d H:i', $this->feed['feed_updated'])
      );

      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->add($result);
    }
  }

  /**
  * Get entry detail view xml
  * @return void
  */
  function getXMLEntryDetails() {
    if (isset($this->entry)) {
      $result = '<sheet>';
      $result .= '<header>';
      $result .= '<lines>';
      $result .= sprintf(
        '<line class="headertitle">%s</line>',
        papaya_strings::escapeHTMLChars($this->entry['feedentry_title'])
      );
      $result .= '</lines>';
      $result .= '</header>';
      $result .= '<text>';
      $result .= '<div style="padding: 1em;">';
      $summary = $this->cleanFeedEntryContent(
        $this->entry['feedentry_summary'],
        $this->entry['feedentry_summary_type']
      );
      $content = $this->cleanFeedEntryContent(
        $this->entry['feedentry_content'],
        $this->entry['feedentry_content_type']
      );
      if (!empty($summary)) {
        $result .= sprintf(
          '<p><i>%s</i></p>',
          $summary
        );
      }
      if (!empty($content)) {
        $result .= sprintf(
          '<p>%s</p>',
          $content
        );
      }
      $result .= '</div>';
      $result .= '</text>';
      $result .= '</sheet>';
      $this->layout->add($result);
    }
  }

  /**
  * Geed feed entries listview and dialog xml
  * @return void
  */
  function getXMLEntriesList() {
    if (count($this->entries) > 0) {
      $result = sprintf(
        '<dialog action="%s" title="%s">'.LF,
        $this->getLink(),
        papaya_strings::escapeHTMLChars($this->_gt('Entries'))
      );
      if (isset($this->feed)) {
        $result .= sprintf(
          '<input type="hidden" name="%s[%s]" value="%s" />',
          papaya_strings::escapeHTMLChars($this->paramName),
          'feed_id',
          (int)$this->feed['feed_id']
        );
        $baseParams = array(
          'cmd' => 'feed_show',
          'feed_id' => $this->feed['feed_id'],
          'entry_status' => $this->entriesStatus
        );
      } else {
        $baseParams = array(
          'cmd' => 'planet_show',
          'entry_status' => $this->entriesStatus
        );
      }
      $result .= sprintf(
        '<input type="hidden" name="%s[%s]" value="%s" />',
        papaya_strings::escapeHTMLChars($this->paramName),
        'entry_limit',
        (int)$this->entriesLimit
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[%s]" value="%s" />',
        papaya_strings::escapeHTMLChars($this->paramName),
        'entry_offset',
        (int)$this->entriesOffset
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[%s]" value="%s" />',
        papaya_strings::escapeHTMLChars($this->paramName),
        'entry_status',
        (int)$this->defaultStatus
      );
      $result .= '<listview>'.LF;
      $result .= '<buttons>'.LF;
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_paging_buttons.php');
      $result .= papaya_paging_buttons::getPagingButtons(
        $this,
        $baseParams,
        $this->entriesOffset,
        $this->entriesLimit,
        $this->entriesCount,
        21,
        $paramName = 'entry_offset',
        'left'
      );
      $feedId = empty($this->feed['feed_id']) ? 0 : $this->feed['feed_id'];
      $buttons = array();
      if (!empty($this->entryCounts[$feedId][PAPAYA_PLANET_ENTRY_STATUS_NEW])) {
        $buttons[PAPAYA_PLANET_ENTRY_STATUS_NEW] = sprintf(
          $this->_gt('New').' (%d)',
          (int)$this->entryCounts[$feedId][PAPAYA_PLANET_ENTRY_STATUS_NEW]
        );
      }
      if (!empty($this->entryCounts[$feedId][PAPAYA_PLANET_ENTRY_STATUS_READED])) {
        $buttons[PAPAYA_PLANET_ENTRY_STATUS_READED] = sprintf(
          $this->_gt('Readed').' (%d)',
          (int)$this->entryCounts[$feedId][PAPAYA_PLANET_ENTRY_STATUS_READED]
        );
      }
      if (!empty($this->entryCounts[$feedId][PAPAYA_PLANET_ENTRY_STATUS_DELETED])) {
        $buttons[PAPAYA_PLANET_ENTRY_STATUS_DELETED] = sprintf(
          $this->_gt('Deleted').' (%d)',
          (int)$this->entryCounts[$feedId][PAPAYA_PLANET_ENTRY_STATUS_DELETED]
        );
      }
      $result .= papaya_paging_buttons::getButtons(
        $this,
        $baseParams,
        $buttons,
        $this->defaultStatus,
        $paramName = 'entry_status',
        'right'
      );
      $result .= '</buttons>'.LF;
      $result .= '<cols>';
      $result .= '<col/>';
      $result .= '<col/>';
      if (!isset($this->feed)) {
        $result .= sprintf(
          '<col>%s</col>',
          papaya_strings::escapeHTMLChars($this->_gt('Feed'))
        );
      }
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Updated'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Fetched'))
      );
      $result .= '<col/>';
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->entries as $entry) {
        $emphased = '';
        switch ($entry['feedentry_status']) {
        case PAPAYA_PLANET_ENTRY_STATUS_NEW :
          $emphased = ' emphased="emphased"';
          $imageIndex = 'status-page-published';
          break;
        case PAPAYA_PLANET_ENTRY_STATUS_READED :
          $imageIndex = 'status-page-modified';
          break;
        case PAPAYA_PLANET_ENTRY_STATUS_DELETED :
          $imageIndex = 'status-page-deleted';
          break;
        default :
          $imageIndex = 'items-page';
          break;
        }
        $linkParams = array(
          'cmd' => 'entry_show',
          'entry_id' => $entry['feedentry_id'],
          'entry_limit' => $this->entriesLimit,
          'entry_offset' => $this->entriesOffset
        );
        if (isset($this->feed)) {
          $linkParams['feed_id'] = $this->feed['feed_id'];
        }
        $result .= sprintf(
          '<listitem title="%s" hint="%s" href="%s" image="%s"%s>',
          papaya_strings::escapeHTMLChars($entry['feedentry_title']),
          $this->cleanFeedEntryContent(
            $entry['feedentry_summary'],
            $entry['feedentry_summary_type']
          ),
          $this->getLink($linkParams),
          papaya_strings::escapeHTMLChars($this->images[$imageIndex]),
          $emphased
        );
        if (!empty($entry['feedentry_url'])) {
          $result .= sprintf(
            '<subitem align="center"><a href="%s" target="_blank"><glyph src="%s" hint="%s"/>'.
              '</a></subitem>',
            papaya_strings::escapeHTMLChars($entry['feedentry_url']),
            papaya_strings::escapeHTMLChars($this->images['items-link']),
            papaya_strings::escapeHTMLChars($this->_gt('View original'))
          );
        } else {
          $result .= '<subitem/>';
        }
        if (!isset($this->feed)) {
          $result .= sprintf(
            '<subitem>%s</subitem>',
            papaya_strings::escapeHTMLChars($entry['feed_title'])
          );
        }
        $result .= sprintf(
          '<subitem align="center">%s</subitem>',
          date('Y-m-d H:i', $entry['feedentry_updated'])
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>',
          date('Y-m-d H:i', $entry['feedentry_fetched'])
        );
        $result .= sprintf(
          '<subitem align="center"><input type="checkbox" name="%s[%s][]" value="%d"/></subitem>',
          papaya_strings::escapeHTMLChars($this->paramName),
          'entry_ids',
          (int)$entry['feedentry_id']
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $result .= sprintf(
        '<dlgbutton type="button" caption="%s" hint="%s" onclick="%s" image="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Invert selection')),
        papaya_strings::escapeHTMLChars($this->_gt('Invert selection')),
        'invertCheckBoxes(this);',
        papaya_strings::escapeHTMLChars($this->images['status-node-checked'])
      );
      $result .= sprintf(
        '<dlgbutton caption="%s" hint="%s" name="%s[%s]" value="%s" image="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Mark read')),
        papaya_strings::escapeHTMLChars($this->_gt('Mark read')),
        papaya_strings::escapeHTMLChars($this->paramName),
        'cmd',
        'entries_mark_readed',
        papaya_strings::escapeHTMLChars($this->images['status-page-modified'])
      );
      $result .= sprintf(
        '<dlgbutton caption="%s" hint="%s" name="%s[%s]" value="%s" image="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Mark new')),
        papaya_strings::escapeHTMLChars($this->_gt('Mark new')),
        papaya_strings::escapeHTMLChars($this->paramName),
        'cmd',
        'entries_mark_new',
        papaya_strings::escapeHTMLChars($this->images['status-page-published'])
      );
      $result .= sprintf(
        '<dlgbutton caption="%s" hint="%s" name="%s[%s]" value="%s" image="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Delete')),
        papaya_strings::escapeHTMLChars($this->_gt('Delete')),
        papaya_strings::escapeHTMLChars($this->paramName),
        'cmd',
        'entries_delete',
        papaya_strings::escapeHTMLChars($this->images['places-trash'])
      );
      $result .= '</dialog>'.LF;
      $this->layout->add($result);
    }
  }

  /**
  * Initialize object for add feed dialog
  * @return base_dialog
  */
  function &initializeAddFeedDialog() {
    static $feedDialog;
    if (isset($feedDialog) && is_object($feedDialog)) {
      return $feedDialog;
    } else {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $fields = array(
        'feed_url' => array(
          'URL', 'isHTTPX', TRUE, 'input', 200
        )
      );
      $hidden = array(
        'cmd' => 'feed_add',
        'confirm_add' => 1
      );
      $data = array();
      $feedDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $feedDialog->dialogTitle = $this->_gt('Add Feed');
      $feedDialog->buttonTitle = 'Add';
      $feedDialog->initializeParams();
      return $feedDialog;
    }
  }

  /**
  * Get add feed dialog xml
  * @return void
  */
  function getDialogAddFeed() {
    $feedDialog = &$this->initializeAddFeedDialog();
    $this->layout->add($feedDialog->getDialogXML());
  }

  /**
  * Getch feed contents and parse it
  * @param string $feedURL
  * @return NULL|papaya_atom_feed
  */
  function &fetchFeed($feedURL) {
    $result = NULL;
    if ($data = file_get_contents($feedURL)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/sys_simple_xmltree.php');
      if ($xmlTree = &simple_xmltree::createFromXML($data, $this)) {
        include_once(PAPAYA_INCLUDE_PATH.'system/xml/feeds/atom/papaya_atom_feed.php');
        $result = new papaya_atom_feed();
        if (!$result->load($xmlTree)) {
          $result = NULL;
        }
        simple_xmltree::destroy($xmlTree);
      }
    }
    return $result;
  }

  /**
  * Check feed exists in database
  * @param string $feedIdent
  * @return boolean
  */
  function feedExists($feedIdent) {
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE feed_ident = '%s'";
    $params = array(
      $this->tableFeeds,
      $feedIdent
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      return $res->fetchField() > 0;
    }
    return FALSE;
  }

  /**
  * Add feed record to database
  * @param string $feedIdent
  * @param string $feedTitle
  * @param string $feedURL
  * @return integer
  */
  function addFeed($feedIdent, $feedTitle, $feedURL) {
    $now = time();
    $data = array(
      'feed_ident' => $feedIdent,
      'feed_title' => $feedTitle,
      'feed_title_fetched' => $feedTitle,
      'feed_url' => $feedURL,
      'feed_added' => $now,
      'feed_updated' => $now,
      'feed_status' => PAPAYA_PLANET_FEED_STATUS_OK,
      'feed_failures' => $now,
    );
    return $this->databaseInsertRecord(
      $this->tableFeeds,
      'feed_id',
      $data
    );
  }

  /**
  * Load feed record from database
  * @param integer $feedId
  * @return boolean
  */
  function loadFeed($feedId) {
    $this->feed = NULL;
    $sql = "SELECT feed_id, feed_ident,
                   feed_title, feed_title_fetched,
                   feed_url,
                   feed_added, feed_updated,
                   feed_status, feed_failures
              FROM %s
             WHERE feed_id = '%d'";
    $params = array(
      $this->tableFeeds,
      $feedId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->feed = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load feeds list from database
  * @param integer $limit
  * @param integer $offset
  * @return boolean
  */
  function loadFeedsList($limit, $offset) {
    $this->feeds = array();
    $sql = "SELECT feed_id, feed_title, feed_url, feed_status
              FROM %s
             ORDER BY feed_title";
    if ($res = $this->databaseQueryFmt($sql, $this->tableFeeds, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->feeds[$row['feed_id']] = $row;
      }
      $this->feedsCount = $res->absCount();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load entry counts for feeds
  * @param NULL|integer|array $feedIds
  * @return boolean
  */
  function loadEntryCounts($feedIds = NULL) {
    $filter = '';
    if (isset($feedIds)) {
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('rel.feed_id', $feedIds)
      );
      $sql = "SELECT rel.feed_id, fe.feedentry_status,
                     COUNT(*) AS entry_count
                FROM %s AS rel, %s AS fe
               WHERE fe.feedentry_id = rel.feedentry_id
                 AND $filter
               GROUP BY rel.feed_id, fe.feedentry_status";
      $params = array(
        $this->tableFeedRelations,
        $this->tableFeedEntries,
      );
    } else {
      $sql = "SELECT fe.feedentry_status,
                     COUNT(*) AS entry_count
                FROM %s AS fe
               GROUP BY fe.feedentry_status";
      $params = array(
        $this->tableFeedEntries
      );
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (isset($row['feed_id'])) {
          $feedId = $row['feed_id'];
        } else {
          //count for all feeds
          $feedId = 0;
        }
        if (!isset($this->entryCounts[$feedId])) {
          $this->entryCounts[$feedId] = array(
            PAPAYA_PLANET_ENTRY_STATUS_NEW => 0,
            PAPAYA_PLANET_ENTRY_STATUS_READED => 0,
            PAPAYA_PLANET_ENTRY_STATUS_DELETED => 0
          );
        }
        $this->entryCounts[$feedId][$row['feedentry_status']] = $row['entry_count'];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * returns the selected status if it contains an entry or the
  * first status in the priority list that contains entries
  *
  * @param integer $feedId optional, default value 0 = all feeds
  * @access public
  * @return NULL | integer
  */
  function getDefaultStatus($feedId = 0) {
    if (isset($this->entryCounts[$feedId])) {
      //if the selected status has entries return this
      if (isset($this->entryCounts[$feedId][$this->entriesStatus]) &&
          $this->entryCounts[$feedId][$this->entriesStatus] > 0) {
        return $this->entriesStatus;
      }
      //else check the priority list
      foreach ($this->statusPriority as $status) {
        if (isset($this->entryCounts[$feedId][$status]) &&
            $this->entryCounts[$feedId][$status] > 0) {
          return $status;
        }
      }
    }
    return NULL;
  }

  /**
  * Load feed entries list from database
  * @param integer $feedId
  * @param integer $limit
  * @param integer $offset
  * @param integer $entryStatus
  * @return boolean
  */
  function loadFeedEntries($feedId, $limit, $offset, $entryStatus = NULL) {
    $this->entries = array();
    if (isset($entryStatus)) {
      $filter =
        "fe.feedentry_status = '".(int)$entryStatus."'";
    } else {
      $filter =
        "NOT(fe.feedentry_status = '".(int)PAPAYA_PLANET_ENTRY_STATUS_DELETED."')";
    }
    $sql = "SELECT fe.feedentry_id, fe.feedentry_status,
                   fe.feedentry_ident, fe.feedentry_url,
                   fe.feedentry_updated, fe.feedentry_fetched,
                   fe.feedentry_title,
                   fe.feedentry_summary, fe.feedentry_summary_type
              FROM %s AS rel, %s AS fe
             WHERE rel.feed_id = '%d'
               AND $filter
               AND fe.feedentry_id = rel.feedentry_id
             ORDER BY fe.feedentry_fetched DESC, fe.feedentry_updated DESC";
    $params = array(
      $this->tableFeedRelations,
      $this->tableFeedEntries,
      $feedId
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $limit, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->entries[$row['feedentry_id']] = $row;
      }
      $this->entriesCount = $res->absCount();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load feed entry details from database
  * @param integer $entryId
  * @return boolean
  */
  function loadEntry($entryId) {
    $sql = "SELECT fe.feedentry_id, fe.feedentry_status,
                   fe.feedentry_ident, fe.feedentry_url,
                   fe.feedentry_updated, fe.feedentry_fetched,
                   fe.feedentry_title,
                   fe.feedentry_summary, fe.feedentry_summary_type,
                   fe.feedentry_content, fe.feedentry_content_type,
                   f.feed_id, f.feed_title
              FROM %s AS fe, %s AS f, %s AS rel
             WHERE fe.feedentry_id = '%d'
               AND rel.feedentry_id = fe.feedentry_id
               AND f.feed_id = rel.feed_id";
    $params = array(
      $this->tableFeedEntries,
      $this->tableFeeds,
      $this->tableFeedRelations,
      $entryId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->entry = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Update feed data
  * @param integer $feedId
  * @param papaya_atom_feed $feed
  * @return boolean
  */
  function updateFeed($feedId, &$feed) {
    $result = 0;
    $now = time();
    $insertEntries = array();
    $updateEntries = array();
    $entryIdents = array();
    for ($i = 0; $i < $feed->entries->count(); $i++) {
      $entry = &$feed->entries->item($i);
      if (isset($entry->id)) {
        $id = $entry->id->get();
        if ($url = $entry->links->getDefaultLink('text/html')) {
          $url = $url->href;
        } else {
          $url = '';
        }
        $insertEntries[$id] = array(
          'feedentry_status' => PAPAYA_PLANET_ENTRY_STATUS_NEW,
          'feedentry_ident' => $id,
          'feedentry_url' => $url,
          'feedentry_updated' => $entry->updated,
          'feedentry_published' => $entry->published,
          'feedentry_fetched' => $now,
          'feedentry_title' => $entry->title->getValue(),
          'feedentry_summary' => $entry->summary->getValue(),
          'feedentry_summary_type' => $entry->summary->getType(),
          'feedentry_content' => $entry->content->getValue(),
          'feedentry_content_type' => $entry->content->getType()
        );
        $entryIdents[] = $id;
      }
    }
    if (count($insertEntries) > 0) {
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('feedentry_ident', $entryIdents)
      );
      $sql = "SELECT feedentry_id, feedentry_ident, feedentry_updated
                FROM %s
               WHERE $filter";
      if ($res = $this->databaseQueryFmt($sql, $this->tableFeedEntries)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $id = $row['feedentry_ident'];
          if ($row['feedentry_updated'] < $insertEntries[$id]['feedentry_updated']) {
            //old entry in database - schedule for update
            $updateEntries[$row['feedentry_id']] = $insertEntries[$id];
          }
          //in database - remove from insert stack
          unset($insertEntries[$id]);
        }
      } else {
        return FALSE;
      }
    }
    if (count($insertEntries) > 0) {
      //first insert all new entries
      if (FALSE !== $this->databaseInsertRecords(
           $this->tableFeedEntries, array_values($insertEntries))) {
        $result = count($insertEntries);
      } else {
        return FALSE;
      }
    }
    foreach ($updateEntries as $updateId => $updateData) {
      if (FALSE !== $this->databaseUpdateRecord(
            $this->tableFeedEntries, $updateData, 'feedentry_id', $updateId)) {
        ++$result;
      } else {
        return FALSE;
      }
    }
    if (count($entryIdents) > 0) {
      //add relations
      $filter = str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition('fe.feedentry_ident', $entryIdents)
      );
      $sql = "SELECT fe.feedentry_id
                FROM %s AS fe
                LEFT JOIN %s AS rel ON (
                       rel.feedentry_id = fe.feedentry_id AND
                       rel.feed_id = '%d'
                     )
                WHERE $filter
                  AND ISNULL(rel.feedentry_id)";
      $params = array(
        $this->tableFeedEntries,
        $this->tableFeedRelations,
        $feedId
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        $entryRelations = array();
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $entryRelations[] = array(
            'feedentry_id' => $row['feedentry_id'],
            'feed_id' => $feedId
          );
        }
        if (count($entryRelations) > 0) {
          if (FALSE === $this->databaseInsertRecords($this->tableFeedRelations, $entryRelations)) {
            return 0;
          }
        }
      }
    }
    return $result;
  }

  /**
  * Set feed status
  * @param array $entryIds
  * @param integer $status
  * @return boolean
  */
  function setEntryStatus($entryIds, $status) {
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableFeedEntries,
      array('feedentry_status' => (int)$status),
      'feedentry_id',
      $entryIds
    );
  }

  /**
  * Purify feed content
  * @param string $content
  * @param string $contentType
  * @return string
  */
  function cleanFeedEntryContent($content, $contentType) {
    $result = '';
    switch ($contentType) {
    case 'text' :
      $result = $content;
      break;
    case 'xhtml' :
    case 'html' :
      $result = strip_tags($content);
      break;
    }
    $typeEnd = substr($contentType, -4);
    if ($typeEnd == '+xml' || $typeEnd == '/xml') {
      $result = strip_tags($content);
    } elseif (substr($contentType, 0, -4) == 'text') {
      $result = $content;
    }
    return htmlspecialchars($result);
  }
}

?>