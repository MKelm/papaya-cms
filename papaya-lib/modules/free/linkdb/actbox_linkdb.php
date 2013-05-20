<?php
/**
* Actionbox featuring (random) linkDB links
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
* @package Papaya-Modules
* @subpackage Free-LinkDatabase
* @version $Id: actbox_linkdb.php 32588 2009-10-14 15:09:20Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');
/**
* Basic db object
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');
/**
* Basic string functions
*/
require_once(PAPAYA_INCLUDE_PATH.'system/papaya_strings.php');

/**
* Actionbox featuring (random) linkDB links
*
* @package Papaya-Modules
* @subpackage Free-LinkDatabase
*/
class actbox_linkdb extends base_actionbox {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'link_category_id' => array('Link category', 'isNum', TRUE,
      'function', 'getLinkDBCombo', '', 0),
    'number' => array('Number of links', 'isNum', TRUE, 'input', 3, '', 10),
    'mode' => array('Display mode', 'isNum', TRUE, 'combo',
      array(0 => 'random', 1 => 'latest'), '', 1),
    'page_id' => array('Exitpage for links', 'isNum', TRUE, 'pageid', 10, '', 0),
  );

  /**
  * Links per page
  * @var integer $linksPerPage
  */
  var $linksPerPage = NULL;

  /**
  * PHP5 constructor
  *
  * @param object &$topic base_topic object
  * @param string $paramName optional, default value 'ldb'
  * @access public
  */
  function __construct(&$topic, $paramName = 'ldb') {
    parent::__construct($topic, $paramName);
    $this->dbObj = new base_db();
    $this->tableLinkdb = PAPAYA_DB_TABLEPREFIX.'_linkdb';
    $this->tableLinkdbCateg = PAPAYA_DB_TABLEPREFIX.'_linkdb_categ';
    $this->tableLinkdbClicks = PAPAYA_DB_TABLEPREFIX.'_linkdb_clicks';
  }

  /**
  * PHP4 constructor
  *
  * @param object &$topic base_topic object
  * @param string $paramName optional, default value 'ldb'
  * @access public
  */
  function actbox_linkdb(&$topic, $paramName = 'ldb') {
    $this->__construct($topic, $paramName);
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = '';
    $this->linksPerPage = $this->data['number'];
    switch($this->data['mode']) {
    case 0: // random
      $randFunc = $this->dbObj->databaseGetSQLSource('RANDOM');
      $this->loadLinks($this->data['link_category_id'], TRUE, $randFunc);
      break;
    case 1: // latest
      $this->loadLinks($this->data['link_category_id'], TRUE, 'link_created', 'DESC');
      break;
    }
    $result .= '<links>'.LF;
    if (isset($this->links) && is_array($this->links) && count($this->links) > 0) {
      foreach ($this->links as $link) {
        $result .= sprintf(
          '<link url="%s" title="%s"><description>%s</description></link>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink(
              $this->data['page_id'],
              NULL,
              NULL,
              array('link_id' => $link['link_id']),
              $this->paramName
            )
          ),
          papaya_strings::escapeHTMLChars($link['link_title']),
          $this->getXHTMLString($link['link_description'])
        );
      }
    }
    $result .= '</links>'.LF;
    return $result;
  }

  /**
  * Get link database combo
  *
  * @param string $name
  * @param array $field
  * @param array $data
  * @access public
  * @return string $result XML
  */
  function getLinkDbCombo($name, $field, $data) {
    $result = '';
    $this->loadCategs();
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $selected = ($data == 0) ? ' selected="selected"' : '';
    $result .= sprintf(
      '<option value="%d"%s>%s</option>'.LF,
      0,
      $selected,
      papaya_strings::escapeHTMLChars($this->_gt('Base'))
    );
    if (isset($this->categs) && is_array($this->categs)) {
      $result .= $this->getCategSubTree(0, 0, $data);
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get category subtree
  *
  * @param integer $parent
  * @param integer $indent
  * @param array $data
  * @access public
  * @return string $result
  */
  function getCategSubTree($parent, $indent, $data) {
    $result = '';
    if (isset($this->categTree[$parent]) &&
      is_array($this->categTree[$parent])) {
      foreach ($this->categTree[$parent] as $id) {
        $result .= $this->getChildCategs($id, $indent, $data);
      }
    }
    return $result;
  }

  /**
  * Element of category tree
  *
  * @param integer $id ID
  * @param integer $indent shifting
  * @return string $result String
  */
  function getChildCategs($id, $indent, $data) {
    $result = '';
    if (isset($this->categs[$id]) && is_array($this->categs[$id])) {

      $title = "'".str_repeat('-', $indent * 4).'-> ';
      $title .= papaya_strings::escapeHTMLChars($this->categs[$id]['linkcateg_title']);

      $selected = ($data == $this->categs[$id]['linkcateg_id'])
        ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%d"%s><![CDATA[%s]]></option>'.LF,
        (int)$this->categs[$id]['linkcateg_id'],
        $selected,
        $title
      );
      $result .= $this->getCategSubTree($id, $indent + 1, $data);
    }
    return $result;
  }

  /**
  * Load categories
  *
  * @param integer $id optional, default value NULL
  * @param boolean $repair optional, default value NULL
  * @access public
  * @return boolean
  */
  function loadCategs($id = NULL, $repair = NULL) {
    unset($this->categs);
    unset($this->categTree);
    if (isset($id)) {
      $sql = "SELECT linkcateg_id,
                     linkcateg_parent_id,
                     linkcateg_title,
                     linkcateg_description
                FROM %s
               WHERE linkcateg_parent_id = %d
               ORDER BY linkcateg_title";

      $params = array($this->tableLinkdbCateg, $id);
    } elseif (isset($repair) && $repair == TRUE) {
      $sql = "SELECT linkcateg_id,
                     linkcateg_parent_id,
                     linkcateg_title,
                     linkcateg_path
                FROM %s
               WHERE linkcateg_parent_id > -1
               ORDER BY linkcateg_title";
      $params = array($this->tableLinkdbCateg);
    } else {
      $sql = "SELECT linkcateg_id,
                     linkcateg_parent_id,
                     linkcateg_title,
                     linkcateg_path
                FROM %s
               ORDER BY linkcateg_title";
      $params = array($this->tableLinkdbCateg);
    }

    if ($res = $this->dbObj->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->categs[(int)$row['linkcateg_id']] = $row;
        $this->categTree[(int)$row['linkcateg_parent_id']][] = $row['linkcateg_id'];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load links
  *
  * @param integer $id
  * @param boolean $full optional, default value FALSE
  * @param string $orderBy optional, default value 'link_title'
  * @param string $orderMode optional, default value 'ASC'
  * @access public
  * @return boolean
  */
  function loadLinks($id, $full = FALSE, $orderBy = 'link_title', $orderMode = 'ASC') {
    unset($this->links);
    if ($full) {
      $sql = "SELECT link_id,
                     linkcateg_id,
                     link_title,
                     link_url,
                     link_description,
                     link_status
                FROM %s AS links
               WHERE linkcateg_id = %d
                 AND link_status = 1
               ORDER BY %s %s";
      $params = array($this->tableLinkdb, (int)$id, (string)$orderBy, (string)$orderMode);
    } else {
      $sql = "SELECT link_id,
                     linkcateg_id,
                     link_title,
                     link_url,
                     link_description,
                     link_status
                FROM %s AS links
               WHERE linkcateg_id = %d
               ORDER BY %s %s";
      $params = array($this->tableLinkdb, (int)$id, (string)$orderBy, (string)$orderMode);
    }
    $ids = array();
    $offset = empty($this->params['offset']) ? 0 : (int)$this->params['offset'];
    if ($res = $this->dbObj->databaseQueryFmt($sql, $params, $this->linksPerPage, $offset)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['counted'] = 0;
        $this->links[(int)$row['link_id']] = $row;
        $ids[] = (int)$row['link_id'];
      }
      $this->linkCount = $res->absCount();
      if (count($ids) > 0 && (!$full)) {
        $filter = $this->dbObj->databaseGetSQLCondition('link_id', $ids);
        $sql = "SELECT link_id, SUM(clicks_count) AS counted
                  FROM %s
                 WHERE  $filter
                 GROUP BY link_id";
        if ($res = $this->dbObj->databaseQueryFmt($sql, $this->tableLinkdbClicks)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $this->links[(int)$row['link_id']]['counted'] = $row['counted'];
          }
        }
      }
      return TRUE;
    }
    return FALSE;
  }
}

?>
