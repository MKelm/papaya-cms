<?php
/**
* LinkDb Output
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
* @version $Id: output_linkdb.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basicclass for database access
*/
require_once(dirname(__FILE__)."/base_linkdb.php");

/**
* LinkDb administration
*
* @package Papaya-Modules
* @subpackage Free-LinkDatabase
*/
class output_linkdb extends base_linkdb {

  /**
  * Get XML link navigation
  *
  * @param integer $offset
  * @param integer $step
  * @param integer $max
  * @param string $paramName
  * @param integer $callCateg
  * @param array $data
  * @access public
  * @return string $result XML
  */
  function getXMLLinkNavigation($offset, $step, $max,
                                $paramName, $callCateg, $data) {
    $string = '';
    if (($max > $step)) {
      $string .= '<linksnavi>';
      if ($offset > 0) {
        $i = (($offset - $step) > 0) ? ($offset - $step) : 0;
        $string .= sprintf(
          '<linksback href="%s" text="%s" />',
          $this->buildLink(
            array(
              'cmd' => 'show',
              $paramName => $i,
              'categ_id' => $callCateg
            )
          ),
          $data['previoslink']
        );
      }
      $string .= sprintf('<pages>');
      for ($i = 0, $x = 1; $i < $max; $i += $step, $x++) {
        $pages[$i] = $x;
        if ($offset == $i) {
          $selected = 'selected';
        } else {
          $selected = '';
        }
        $string .= sprintf(
          '<page num="%d" href="%s" option="%s" />',
          $pages[$i],
          $this->buildLink(
            array(
              'cmd' => 'show',
              'offset' => $i,
              'categ_id' => $callCateg
            )),
          $selected
        );
      }
      $string .= sprintf('</pages>');

      if ($offset < ($max - $step)) {
        $i = (($offset + $step) < $max) ? ($offset + $step) : ($max - $step);
        $string .= sprintf(
          '<linksnext href="%s" text="%s" />',
          $this->buildLink(
            array(
              'cmd' => 'show',
              $paramName => $i,
              'categ_id' => $callCateg
            )
          ),
          $data['nextlink']
        );
      }
      $string .= '</linksnavi>';
    }
    return $string;
  }

  /**
  * Count clicks of links
  *
  * @param integer $id
  * @access public
  */
  function countClicks ($id) {
    if ($this->linkExists($id)) {
      $year = date("Y", time());
      $month = date("m", time());

      $sql = 'SELECT link_id
                FROM %s
               WHERE link_id = %d
                 AND clicks_year = %d
                 AND clicks_month = %d';
      $params = array($this->tableLinkdbClicks, $id, $year, $month);

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          if ((bool)$row[0] > 0 ) {
            $sql = 'UPDATE %s
                       SET clicks_count = clicks_count + 1
                     WHERE link_id = %d
                       AND clicks_year = %d
                       AND clicks_month = %d';
            $params = array($this->tableLinkdbClicks, $id, $year, $month);
            $this->databaseQueryFmtWrite($sql, $params);
          }
        } else {
          $data = array(
            'link_id' => $id,
            'clicks_year' => $year,
            'clicks_month' => $month,
            'clicks_count' => 1
          );
          $this->databaseInsertRecord($this->tableLinkdbClicks, NULL, $data);
        }
      }
    }
  }

  /**
  * Search Lonks
  *
  * @param integer $categId
  * @param string $searchFor
  * @access public
  * @return boolean
  */
  function searchLinks($categId, $searchFor) {
    unset($this->links);
    include_once(PAPAYA_INCLUDE_PATH.'system/base_searchstringparser.php');
    $parser = new searchstringparser;
    if ($searchFilter = $parser->getSQL(
          $searchFor,
          array('l.link_title', 'l.link_description'),
          $this->fullTextSearch)) {
      if ($categId > 0) {
        $categs = array((int)$categId);
        $this->loadCateg($categId);
        $sql = "SELECT linkcateg_id
                  FROM %s
                 WHERE linkcateg_path LIKE '%s%%'";
        $path = $this->categ['linkcateg_path'].$categId.';';
        if ($res = $this->databaseQueryFmt($sql, array($this->tableLinkdbCateg, $path))) {
          while ($row = $res->fetchRow()) {
            $categs[] = (int)$row[0];
          }
        }
        $filter = str_replace('%', '%%', $this->databaseGetSQLCondition('l.linkcateg_id', $categs));
        $sql = "SELECT l.link_id,
                       l.linkcateg_id,
                       l.link_title,
                       l.link_description,
                       l.link_url,
                       l.link_status,
                       c.linkcateg_title
                  FROM %s AS l
                 INNER JOIN %s AS c ON c.linkcateg_id = l.linkcateg_id
                 WHERE ".str_replace('%', '%%', $searchFilter)."
                   AND l.link_status = 1
                   AND $filter
              ORDER BY l.link_title DESC";
      } else {
        $sql = "SELECT l.link_id,
                        l.linkcateg_id,
                        l.link_title,
                        l.link_description,
                        l.link_url,
                        l.link_status,
                        c.linkcateg_title
                  FROM %s AS l
                 INNER JOIN %s AS c ON c.linkcateg_id = l.linkcateg_id
                 WHERE ".str_replace('%', '%%', $searchFilter)."
                   AND l.link_status = 1
              ORDER BY l.link_title DESC";
      }
      $params = array($this->tableLinkdb, $this->tableLinkdbCateg);

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->links[$row['link_id']] = $row;
        }
        return TRUE;
      }
    }
    return FALSE;
  }


  /**
  * Initialize search formular
  *
  * @param array $data
  * @access public
  */
  function initializeSearchForm($data) {
    if (!(isset($this->searchDialog) && is_object($this->searchDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array(
        'cmd' => 'search',
        'save' => 1,
        'search' => TRUE
      );
      $fields = array(
        'searchfor' => array('Search', 'isNoHTML', TRUE, 'input', 200)
      );
      $this->searchDialog = new base_dialog(
        $this,
        $this->paramName,
        $fields,
        $data,
        $hidden
      );
      $this->searchDialog->dialogMethod = 'get';
      $this->searchDialog->useToken = FALSE;
      $this->searchDialog->msgs = &$this->msgs;
      $this->searchDialog->buttonTitle = 'Search';
      $this->searchDialog->loadParams();
    }
  }

  /**
  * Get search formular
  *
  * @param array $data
  * @access public
  * @return string
  */
  function getSearchForm($data) {
    $this->searchDialog->baseLink = $this->baseLink;
    $this->searchDialog->dialogTitle = 'Search';
    $this->searchDialog->dialogDoubleButtons = FALSE;

    return '<searchdlg>'.$this->searchDialog->getDialogXML().'</searchdlg>';
  }


  /**
  * get XML for founded Links
  *
  * @param array $data
  * @access public
  * @return string
  */
  function getXMLFoundedLinks ($data) {
    $string = '';
    $string .= sprintf('<links>'.LF);
    if (isset($this->links) && is_array($this->links)) {
      foreach ($this->links as $link) {
        $string .= sprintf(
          '<link title="%s" href="%s" target="%s" categlink="%s" categ="%s">%s</link>'.LF,
          $link['link_title'],
          $this->getLink(array('link_id' => $link['link_id'])),
          $this->idToTarget(@$data['kind_of_call']),
          $this->getLink(array('categ_id' => $link['linkcateg_id'])),
          $link['linkcateg_title'],
          $this->getXHTMLString(
            $link['link_description'], !((bool)@$data['nl2br_description'])
          )
        );
      }
      $string .= sprintf('</links>'.LF);
    }
    return $string;
  }

  /**
  * get XML for Category
  *
  * @param array $data
  * @access public
  * @return string
  */
  function getXMLCategs ($data) {
    //get Categs
    $string = '';
    if (isset($this->categs) && is_array($this->categs)) {
      $string .= sprintf('<categs>'.LF);
      foreach ($this->categs as $categ) {
        $string .= sprintf(
          '<categ title="%s" href="%s">%s</categ>'.LF,
          $categ['linkcateg_title'],
          $this->getLink(array('categ_id' => $categ['linkcateg_id'])),
          $this->getXHTMLString(
            $categ['linkcateg_description'], !((bool)@$data['nl2br_description'])
          )
        );
      }
      $string .= sprintf('</categs>'.LF);
    }
    return $string;
  }

  /**
  * get XML navigation
  *
  * @param array $data
  * @access public
  * @return string $string XML
  */
  function getXMLNavigation ($data) {
    $string = '';
    //get Navigation
    if (isset($this->categsById) && is_array($this->categsById)) {
      $string .= sprintf('<pathnavi>'.LF);
      foreach ($this->categsById as $categ) {
        $string .= sprintf(
          '<categ title="%s" href="%s" />'.LF,
          $categ['linkcateg_title'],
          $this->getLink(array('categ_id' => $categ['linkcateg_id']))
        );
      }
      $string .= sprintf('</pathnavi>'.LF);
    }
    return $string;
  }

  /**
  * Load Categs by Ids from Array
  *
  * @param array $categIds
  * @access public
  * @return boolean
  */
  function loadCategsByIds($categIds) {
    $filter = $this->databaseGetSQLCondition('linkcateg_id', $categIds);
    $sql = "SELECT linkcateg_id, linkcateg_title
              FROM %s
             WHERE $filter
             ORDER BY linkcateg_path";
    $params = array($this->tableLinkdbCateg);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->categsById[(int)$row['linkcateg_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Get XML for Links
  *
  * @param array $data
  * @access public
  * @return string
  */
  function getXMLLinks ($data) {
    //get Links
    $string = '';
    if (isset($this->links) && is_array($this->links)) {
      $string .= sprintf('<links>'.LF);
      foreach ($this->links as $link) {
        if ((int)$link['link_status'] == 1) {
          $string .= sprintf(
            '<link title="%s" href="%s" target="%s">%s</link>'.LF,
            $link['link_title'],
            $this->getLink(array('link_id' => $link['link_id'])),
            $this->idToTarget(@$data['kind_of_call']),
            $this->getXHTMLString(
              $link['link_description'], !((bool)@$data['nl2br_description'])
            )
          );
        }
      }
      $string .= sprintf('</links>'.LF);
    }
    return $string;
  }


  /**
  * Get output for userarea
  *
  * @param array $data
  * @access public
  * @return string $string XML
  */
  function getOutput($data) {
    $this->initializeParams();
    $string = '';
    $this->initializeSearchForm($data);
    $string .= $this->getSearchForm($data);
    if (isset($this->params['search']) && $this->params['search']) {
      if (trim(@$this->params['searchfor']) != '' && $this->params['save']) {
        $this->searchLinks((int)$data['categ'], $this->params['searchfor']);
        if (isset($this->links) && is_array($this->links)) {
          $string .= $this->getXMLFoundedLinks($data);
        }
      }
      $tmp = $this->getLink(array('categ_id' => $data['categ']));
      $string .= sprintf('<backlink>%s</backlink>', $tmp);
    } else {
      // site location by Link-Id
      if (isset($this->params['link_id'])) {
        $this->loadLink($this->params['link_id']);
        if (isset($this->link) && is_array($this->link)) {
          $this->countClicks($this->params['link_id']);
          $GLOBALS['PAPAYA_PAGE']->sendHTTPStatus(301);
          header(
            'Location: '.
            $this->getAbsoluteURL(
              $this->getWebLink(0, '', 'page', array('exit'=>$this->link['link_url'])),
              '',
              FALSE
            )
          );
          exit();
        }
        //define categ to show
      } elseif (isset($this->params['categ_id'])) {
        $callCateg = $this->params['categ_id'];
      } elseif (isset($data['categ'])) {
        $callCateg = $data['categ'];
      }
      if (isset($callCateg) && $callCateg >= 0) {
        if ($callCateg > 0) {
          $this->loadCateg($callCateg);
        }
        if (isset($this->categ) && is_array($this->categ)) {
          preg_match_all(
            '/\d+/',
            $this->categ['linkcateg_path'].$callCateg.';',
            $regs,
            PREG_PATTERN_ORDER
          );
          $catIds = $regs[0];
          $this->loadCategsByIds($catIds);
        }
        if ($data['categ'] == 0 || $data['categ'] == $callCateg ||
            (isset($catIds) && is_array($catIds) && in_array($data['categ'], $catIds))) {
          $this->loadCategs($callCateg);
          if ($callCateg > 0) {
            $this->linksPerPage = $data['perpage'];
            $this->loadLinks($callCateg, TRUE);
          }
          $string .= $this->getXMLNavigation($data);
          $string .= $this->getXMLCategs($data);
          $string .= $this->getXMLLinks($data);
          $string .= $this->getXMLLinkNavigation(
            @(int)$this->params['offset'],
            $this->linksPerPage,
            $this->linkCount,
            'offset',
            $callCateg,
            $data
          );
          if (isset($this->categ) && is_array($this->categ) && isset($data['categ'])) {
            //backlink
            if ($this->categ['linkcateg_id'] != $data['categ']) {
              $tmp = $this->getLink(
                array('categ_id' => $this->categ['linkcateg_parent_id'])
              );
              $string .= sprintf('<backlink>%s</backlink>', $tmp);
            }
            $tmp = $this->getLink(array('search' => 1));
            //searchlink
            $string .= sprintf('<search>%s</search>', $tmp);
          }
        }
      }
    }
    return $string;
  }

  /**
  * Translate an Id to a HTML target attribute
  * (content of the attribute only)
  *
  * @param string $id
  * @access private
  * @return string HTML target attribute
  */
  function idToTarget($id) {
    switch ($id) {
    case 3:
      return '_blank';
    case 4:
      return '_top';
    case 2:
      return '_parent';
    case 1:
      return '_self';
    default:
      return '';
    }
  }
}
?>