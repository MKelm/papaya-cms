<?php
/**
* Link Box with preview page
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
* @subpackage Core
* @version $Id: papaya_boxeslinks.php 37743 2012-11-29 12:35:12Z weinert $
*/

/**
* Linked boxes base class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_boxeslinks.php');

/**
* Input check function
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Link Box with page
*
* @package Papaya
* @subpackage Core
*/
class papaya_boxeslinks extends base_boxeslinks {
  /**
  * Boxes list
  * @var array $boxesList
  */
  var $boxesList = array();
  /**
  * Link list
  * @var array $linkList
  */
  var $linkList = array();
  /**
  * Used
  * @var array $used
  */
  var $used = array();

  /**
  * Initialization
  *
  * @access public
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_boxes';
    $this->initializeParams($this->sessionParamName);
    $this->initializeNodes();
  }

  /**
  * Initiation nodes
  *
  * @access public
  */
  function initializeNodes() {
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->opened = $this->sessionParams['opened'];
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'open':
        if ($this->params['gid'] > 0) {
          $this->opened[$this->params['gid']] = TRUE;
        }
        break;
      case 'close':
        if (isset($this->opened[$this->params['gid']])) {
          unset($this->opened[$this->params['gid']]);
        }
        break;
      }
      $this->sessionParams['opened'] = $this->opened;
      $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    }
  }

  /**
  * Load list
  *
  * @access public
  */
  function loadList() {
    $this->linkList = array();
    $this->used = array();
    $sql = "SELECT boxlink_id, box_id, box_sort, topic_id
              FROM %s
             WHERE topic_id = '%d'
             ORDER BY box_sort";
    $params = array($this->tableLink, $this->topicId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->linkList[$row['boxlink_id']] = $row;
        $this->used[] = $row['box_id'];
      }
    }
  }

  /**
  * Load box list
  *
  * @access public
  */
  function loadBoxList() {
    $this->boxesList = array();
    $sql = "SELECT bl.box_id, bl.box_name, bl.boxgroup_id, bl.box_modified,
                   bl.box_unpublished_languages,
                   bp.box_modified as box_published,
                   bp.box_public_from, bp.box_public_to
              FROM %s bl
              LEFT OUTER JOIN %s bp ON bp.box_id = bl.box_id
             ORDER BY bl.box_name";
    $params = array($this->tableBox, $this->tableBoxPublic);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->boxesList[$row['box_id']] = $row;
      }
    }
  }

  /**
  * Load data list
  *
  * @param integer $lngId
  * @param integer $viewModeId
  * @access public
  * @return boolean
  */
  function loadDataList($lngId, $viewModeId) {
    $this->data = array();
    if ($viewModeId > 0) {
      $sql = "SELECT bl.topic_id, bl.box_id, bl.box_sort, b.box_name, b.boxgroup_id,
                     bt.box_title, bt.box_data, bt.view_id,
                     m.module_guid, m.module_useoutputfilter,
                     m.module_path, m.module_file, m.module_class
                FROM %s bl,
                     %s b,
                     %s bt,
                     %s v,
                     %s vl,
                     %s m
               WHERE bl.topic_id = '%d'
                 AND b.box_id = bl.box_id
                 AND bt.box_id = bl.box_id
                 AND bt.lng_id = '%d'
                 AND v.view_id = bt.view_id
                 AND vl.view_id = bt.view_id
                 AND vl.viewmode_id = %d
                 AND m.module_guid = v.module_guid
                 AND m.module_active = 1
                 AND m.module_type = 'box'
               ORDER BY bl.topic_id, bl.box_sort, bl.box_id";
      $params = array($this->tableLink, $this->tableBox, $this->tableBoxTrans,
        $this->tableViews, $this->tableViewLinks, $this->tableModules,
        $this->topicId, $lngId, $viewModeId);
    } else {
      $sql = "SELECT bl.topic_id, bl.box_id, bl.box_sort, b.box_name, b.boxgroup_id,
                     bt.box_title, bt.box_data, bt.view_id,
                     m.module_guid, m.module_useoutputfilter,
                     m.module_path, m.module_file, m.module_class
                FROM %s bl,
                     %s b,
                     %s bt,
                     %s v,
                     %s m
               WHERE bl.topic_id = '%d'
                 AND b.box_id = bl.box_id
                 AND bt.box_id = bl.box_id
                 AND bt.lng_id = '%d'
                 AND v.view_id = bt.view_id
                 AND m.module_guid = v.module_guid
                 AND m.module_active = 1
                 AND m.module_type = 'box'
               ORDER BY bl.topic_id, bl.box_sort, bl.box_id";
      $params = array($this->tableLink, $this->tableBox, $this->tableBoxTrans,
        $this->tableViews, $this->tableModules, $this->topicId, $lngId);
    }
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['module_file'] = $row['module_path'].$row['module_file'];
        $this->data[$row['box_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Get list
  *
  * @param array &$images
  * @param boolean $editable
  * @param string $caption
  * @access public
  * @return string '' or XML
  */
  function getList(&$images, $editable, $caption) {
    $result = '';
    if (isset($this->linkList) && is_array($this->linkList)) {
      $groupLinks = array();
      foreach ($this->linkList as $key => $val) {
        $box = $this->boxesList[$val['box_id']];
        $groupLinks[$box['boxgroup_id']][] = $val;
      }
      if ((isset($groupLinks) && is_array($groupLinks)) &&
          (isset($this->boxGroupsList) && is_array($this->boxGroupsList))) {
        $result .= sprintf(
          '<dialog title="%s">'.LF,
          papaya_strings::escapeHTMLChars($caption)
        );
        $result .= '<listview><items>'.LF;
        foreach ($this->boxGroupsList as $groupId => $group) {
          if (isset($groupLinks[$groupId]) && is_array($groupLinks[$groupId])) {
            $boxLinks = $groupLinks[$groupId];
            if (isset($this->opened[$groupId]) && $this->opened[$groupId]) {
              $node = 'open';
              $cmd = 'close';
              $imageIdx = 'status-folder-open';
            } else {
              $node = 'close';
              $cmd = 'open';
              $imageIdx = 'items-folder';
            }
            $nodeHref = $this->getLink(
              array('cmd' => $cmd, 'gid' => $groupId, 'page_id' => $this->topicId)
            );
            $indent = 0;
            $result .= sprintf(
              '<listitem title="%s" image="%s" indent="%d" node="%s" nhref="%s">',
              papaya_strings::escapeHTMLChars($group['boxgroup_title']),
              papaya_strings::escapeHTMLChars($images[$imageIdx]),
              (int)$indent,
              papaya_strings::escapeHTMLChars($node),
              papaya_strings::escapeHTMLChars($nodeHref)
            );
            if ($this->authUser->hasPerm(PapayaAdministrationPermissions::BOX_MANAGE)) {
              $result .= '<subitem/>';
            }
            $result .= sprintf(
              '<subitem>%s</subitem>',
              papaya_strings::escapeHTMLChars($group['boxgroup_name'])
            );
            $result .= '<subitem/><subitem/><subitem/>';
            $result .= '</listitem>'.LF;
            if (isset($this->opened[$groupId]) && $this->opened[$groupId]) {
              $first = reset($boxLinks);
              $last = end($boxLinks);
              foreach ($boxLinks as $boxLink) {
                $data = $this->boxesList[$boxLink['box_id']];
                if (isset($data)) {
                  $imageIdx = $this->getBoxStatusImage($data);
                  $result .= sprintf(
                    '<listitem title="%s" image="%s" indent="2">'.LF,
                    papaya_strings::escapeHTMLChars($data['box_name']),
                    papaya_strings::escapeHTMLChars($images[$imageIdx])
                  );
                  if ($this->authUser->hasPerm(PapayaAdministrationPermissions::BOX_MANAGE)) {
                    $result .= '<subitem align="center">';
                    $result .= sprintf(
                      '<a href="%s"><glyph src="%s" hint="%s"/></a>'.LF,
                      papaya_strings::escapeHTMLChars(
                        $this->getLink(
                          array(
                            'cmd' => 'chg_show',
                            'bid' => $data['box_id'],
                            'p_mode' => 1
                          ),
                          'bb',
                          'boxes.php'
                        )
                      ),
                      papaya_strings::escapeHTMLChars($images['actions-edit']),
                      papaya_strings::escapeHTMLChars($this->_gt('Edit'))
                    );
                    $result .= '</subitem>';
                  }
                  $result .= '<subitem/>';
                  if ($editable) {
                    $result .= '<subitem align="right">';
                    if ($boxLink != $first) {
                      $result .= sprintf(
                        '<a href="%s"><glyph src="%s" /></a>'.LF,
                        papaya_strings::escapeHTMLChars(
                          $this->getLink(
                            array(
                              'cmd' => 'up',
                              'boxlink_id' => $boxLink['boxlink_id'],
                              'page_id' => $this->topicId
                            )
                          )
                        ),
                        $images['actions-go-up']
                      );
                    }
                    $result .= '</subitem>';
                    $result .= '<subitem>';
                    if ($boxLink != $last) {
                      $result .= sprintf(
                        '<a href="%s"><glyph src="%s" /></a>'.LF,
                        papaya_strings::escapeHTMLChars(
                          $this->getLink(
                            array(
                              'cmd' => 'down',
                              'boxlink_id' => $boxLink['boxlink_id'],
                              'page_id' => $this->topicId
                            )
                          )
                        ),
                        $images['actions-go-down']
                      );
                    }
                    $result .= '</subitem>';
                    $result .= '<subitem align="right">';
                    $result .= sprintf(
                      '<a href="%s"><glyph src="%s" hint="%s"/></a>'.LF,
                      papaya_strings::escapeHTMLChars(
                        $this->getLink(
                          array(
                            'cmd' => 'del',
                            'boxlink_id' => $boxLink['boxlink_id'],
                            'page_id' => $this->topicId
                          )
                        )
                      ),
                      papaya_strings::escapeHTMLChars($images['actions-list-remove']),
                      papaya_strings::escapeHTMLChars($this->_gt('Remove')));
                    $result .= '</subitem>';
                  } else {
                    $result .= '<subitem/><subitem/><subitem/>';
                  }
                  $result .= '</listitem>';
                }
              }
            }
          }
        }
        $result .= '</items></listview>'.LF;
        $result .= '</dialog>'.LF;
      }
    }
    return $result;
  }

  public function getBoxStatusImage($box) {
    if ($pubDate = $box['box_published']) {
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
    return $imageIndex;
  }

  /**
  * Get Box list
  *
  * @param array &$images
  * @access public
  * @return string '' or XML
  */
  function getBoxList(&$images) {
    $result = '';
    if (isset($this->boxesList) && is_array($this->boxesList)) {
      $groupLinks = array();
      $linked = array();
      if (isset($this->linkList) && is_array($this->linkList)) {
        foreach ($this->linkList as $key => $val) {
          $linked[] = $val['box_id'];
        }
      }
      foreach ($this->boxesList as $boxId=>$box) {
        if (!in_array($boxId, $linked)) {
          $groupLinks[$box['boxgroup_id']][] = $box['box_id'];
        }
      }
      if (isset($groupLinks) && is_array($groupLinks) &&
          is_array($this->boxGroupsList)) {
        $result .= sprintf(
          '<dialog title="%s" width="100%%">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Available boxes'))
        );
        $result .= '<listview>'.LF;
        $result .= '<items>';
        foreach ($this->boxGroupsList as $groupId => $group) {
          if (isset($groupLinks[$groupId]) && is_array($groupLinks[$groupId])) {
            $boxIds = $groupLinks[$groupId];
            if (isset($this->opened[$groupId]) && $this->opened[$groupId]) {
              $node = 'open';
              $cmd = 'close';
              $imageIdx = 'status-folder-open';
            } else {
              $node = 'close';
              $cmd = 'open';
              $imageIdx = 'items-folder';
            }
            $nodeHref = $this->getLink(
              array(
                'cmd' => $cmd,
                'gid' => $groupId
              )
            );
            $indent = 0;
            $result .= sprintf(
              '<listitem title="%s" image="%s" indent="%s" node="%s" nhref="%s">'.LF,
              papaya_strings::escapeHTMLChars($group['boxgroup_title']),
              papaya_strings::escapeHTMLChars($images[$imageIdx]),
              (int)$indent,
              papaya_strings::escapeHTMLChars($node),
              papaya_strings::escapeHTMLChars($nodeHref)
            );
            if ($this->authUser->hasPerm(PapayaAdministrationPermissions::BOX_MANAGE)) {
              $result .= '<subitem/>';
            }
            $result .= sprintf(
              '<subitem>%s</subitem>',
              papaya_strings::escapeHTMLChars($group['boxgroup_name'])
            );
            $result .= '<subitem/>';
            $result .= '</listitem>';
            if (isset($this->opened[$groupId]) && $this->opened[$groupId]) {
              foreach ($boxIds as $boxId) {
                $data = $this->boxesList[$boxId];
                $imageIdx = $this->getBoxStatusImage($data);
                $result .= sprintf(
                  '<listitem title="%s" image="%s" indent="2">'.LF,
                  papaya_strings::escapeHTMLChars($data['box_name']),
                  papaya_strings::escapeHTMLChars($images[$imageIdx])
                );
                if ($this->authUser->hasPerm(PapayaAdministrationPermissions::BOX_MANAGE)) {
                  $result .= '<subitem align="center">';
                  $result .= sprintf(
                    '<a href="%s"><glyph src="%s" hint="%s"/></a>'.LF,
                    papaya_strings::escapeHTMLChars(
                      $this->getLink(
                        array(
                          'cmd' => 'chg_show',
                          'bid' => $data['box_id'],
                          'p_mode' => 1
                        ),
                        'bb',
                        'boxes.php'
                      )
                    ),
                    papaya_strings::escapeHTMLChars($images['actions-edit']),
                    papaya_strings::escapeHTMLChars($this->_gt('Edit'))
                  );
                  $result .= '</subitem>';
                }
                $result .= '<subitem/>';
                $result .= '<subitem align="right">';
                $result .= sprintf(
                  '<a href="%s"><glyph src="%s" hint="%s"/></a>'.LF,
                  papaya_strings::escapeHTMLChars(
                    $this->getLink(array('cmd' => 'add', 'box_id' => $boxId))
                  ),
                  papaya_strings::escapeHTMLChars($images['actions-list-add']),
                  papaya_strings::escapeHTMLChars($this->_gt('Add'))
                );
                $result .= '</subitem>';
                $result .= '</listitem>';
              }
            }
          }
        }
        $result .= '</items></listview>'.LF;
        $result .= '</dialog>'.LF;
      }
    }
    return $result;
  }

  /**
  * User input Processing
  *
  * @access public
  */
  function execute() {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'add':
        return $this->add();
      case 'del':
        return $this->delete();
      case 'up':
        return $this->move('up');
      case 'down':
        return $this->move('down');
      }
    }
    return NULL;
  }

  /**
  * Add a box into link table
  *
  * @access public
  */
  function add() {
    if ($this->params['box_id'] > 0 && $this->topicId > 0) {
      $sql = "SELECT MAX(box_sort)
                FROM %s
               WHERE topic_id = '%d'";
      $sort = 0;
      $params = array($this->tableLink, $this->topicId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          $sort = $row[0];
        }
      }
      ++$sort;
      $values = array(
        'box_id' => $this->params['box_id'],
        'topic_id' => $this->topicId,
        'box_sort' => $sort
      );
      if (FALSE !== $this->databaseInsertRecord(
            $this->tableLink, 'boxlink_id', $values)) {
        $this->loadList();
        return TRUE;
      }
    }
    return NULL;
  }

  /**
  * Delete box from link table
  *
  * @access public
  */
  function delete() {
    if (isset($this->linkList[$this->params['boxlink_id']]) && $this->topicId > 0) {
      $link = $this->linkList[$this->params['boxlink_id']];
      if (FALSE !== $this->databaseDeleteRecord(
            $this->tableLink, 'boxlink_id', $link['boxlink_id'])) {
        $sql = "UPDATE %s
                   SET box_sort = box_sort-1
                 WHERE topic_id = %d
                   AND (box_sort > %d)";
        $params = array($this->tableLink, $link['topic_id'], $link['box_sort']);
        $this->databaseQueryFmtWrite($sql, $params);
      }
      $this->loadList();
      return TRUE;
    }
    return NULL;
  }

  /**
  * Get  grouped ids
  *
  * @param integer $groupId
  * @access public
  * @return mixed array or FALSE
  */
  function getGroupedIDs($groupId) {
    $result = FALSE;
    if (isset($this->linkList) && is_array($this->linkList)) {
      foreach ($this->linkList as $id => $link) {
        if ($groupId == $this->boxesList[$link['box_id']]['boxgroup_id']) {
          $result[$id] = $link;
        }
      }
    }
    if (count($result) < 2) {
      unset($result);
      $result = FALSE;
    }
    return $result;
  }

  /**
  * Move
  *
  * @param string $dir optional, default value 'up'
  * @access public
  * @return boolean
  */
  function move($dir = 'up') {
    $result = NULL;
    $link = $this->linkList[$this->params['boxlink_id']];
    $groupId = $this->boxesList[$link['box_id']]['boxgroup_id'];
    $grouped = $this->getGroupedIDs($groupId);
    if (isset($grouped) && is_array($grouped)) {
      $prior = FALSE;
      $i = reset($grouped);
      while (isset($i) && ($i['boxlink_id'] != $link['boxlink_id'])) {
        $prior = $i;
        $i = next($grouped);
      }
      $next = next($grouped);
      switch ($dir) {
      case 'up':
        $result = $this->exchange($link, $prior);
        break;
      case 'down':
        $result = $this->exchange($link, $next);
        break;
      }
    }
    return $result;
  }

  /**
  * Exchange ( move box link from to )
  *
  * @param array &$src
  * @param array &$des
  * @access public
  * @return boolean
  */
  function exchange(&$src, &$des) {
    $result = FALSE;
    if ((isset($src) && is_array($src)) && (isset($des) && is_array($des))) {
      $updated = $this->databaseUpdateRecord(
        $this->tableLink,
        array('box_sort' => $des['box_sort']),
        'boxlink_id',
        $src['boxlink_id']
      );
      if (FALSE !== $updated) {
        $updated = $this->databaseUpdateRecord(
          $this->tableLink,
          array('box_sort' => $src['box_sort']),
          'boxlink_id',
          $des['boxlink_id']
        );
        if (FALSE !== $updated) {
          $result = TRUE;
        }
      }
      $this->loadList();
    }
    return $result;
  }
}
?>
