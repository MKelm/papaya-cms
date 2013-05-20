<?php
/**
* FAQ Module
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
* @subpackage Free-FAQ
* @version $Id: base_faq.php 32601 2009-10-14 15:37:15Z weinert $
*/

/**
* Basic class for database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* FAQ Module
*
* @package Papaya-Modules
* @subpackage Free-FAQ
*/
class base_faq extends base_db {

  /**
  * Surfer table
  * @var String $tableSurfer
  */
  var $tableSurfer = "";

  /**
  * Faq table
  * @var String $tableFaqs
  */
  var $tableFaqs = '';
  /**
  * Faq groups table
  * @var String $tableFaqgroups
  */
  var $tableFaqgroups = '';
  /**
  * Entry table
  * @var String $tableEntries
  */
  var $tableEntries = '';
  /**
  * Comment table
  * @var String $tableComments
  */
  var $tableComments = '';

  /**
  * size of input field
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'x-large';

  /**
  * Faq parameter name
  * @var string $paramName
  */
  var $paramName = '';

  /**
  * Faq session parameter name
  * @var string $sessionParamName
  */
  var $sessionParamName = '';

  /**
  * Parameters
  * @var array $params
  */
  var $params = NULL;

  /**
  * Faqs
  * @var array $faqs
  */
  var $faqs = NULL;

  /**
  * Faq groups
  * @var array $faqGroups
  */
  var $faqGroups = NULL;

  /**
  * Faq group
  * @var array $faqGroup
  */
  var $faqGroup = NULL;

  /**
  * Faq
  * @var array $faq
  */
  var $faq = NULL;

  /**
  * Cache search results
  * @var array $cacheSearchResults
  */
  var $cacheSearchResults = FALSE;

  /**
  * Object base dialog
  * @var object base_dialog $faqDialog
  */
  var $faqDialog = NULL;

  /**
  * Full text search parameter
  * @var array $fullTextSearch
  */
  var $fullTextSearch = PAPAYA_SEARCH_BOOLEAN;

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
  * Search dialog
  * @var object base_dialog $searchDialog
  */
  var $searchDialog = NULL;

  /**
  * Output dialog
  * @var object base_dialog $outputDialog
  */
  var $outputDialog = NULL;

  /**
  * Comments
  * @var array $comments
  */
  var $comments = NULL;

  /**
  * Comment
  * @var array $comment
  */
  var $comment = NULL;

  var $shortCharCount = 300;

  /**
  * TRUE iff only $this->shortCharCount amount of chars of the answer
  * should be put out on the faq group page, otherwise FALSE.
  */
  var $useShortAnswers = TRUE;

  /**
  * Constructor
  *
  * Note : although this is a php5 constructor and the class uses
  * php4 format, provision is made in the base class to call the php5 method
  * using a workaround for php4.
  *
  * @param string $paramName optional, default value 'ff'
  * @access public
  */
  function __construct($paramName = 'ff') {
    $this->paramName = $paramName;
    $this->tableFaqs = PAPAYA_DB_TABLEPREFIX.'_faq';
    $this->tableFaqgroups = PAPAYA_DB_TABLEPREFIX.'_faqgroups';
    $this->tableEntries = PAPAYA_DB_TABLEPREFIX.'_faqentries';
    $this->tableComments = PAPAYA_DB_TABLEPREFIX.'_faqnotes';
    $this->tableSurfer = PAPAYA_DB_TABLEPREFIX.'_surfer';
  }

  /**
  * Load faqs
  *
  * @param mixed $id optional, default value NULL
  * @access public
  * @return boolean
  */
  function loadFaqs($id = NULL) {
    unset($this->faqs);
    unset($this->faqTree);
    if (isset($id)) {
      $sql = "SELECT faq_id, faq_title, faq_descr
                FROM %s
               WHERE faq_id = '%d'
               ORDER BY faq_title ASC";
    } else {
      $sql = "SELECT faq_id, faq_title, faq_descr
                FROM %s
               ORDER BY faq_title ASC";
    }
    if ($res = $this->databaseQueryFmt($sql, array($this->tableFaqs, (int)$id))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['groupcount'] = 0;
        $this->faqs[(int)$row['faq_id']] = $row;
      }
      return $this->checkFaqGroups();
    }
    return FALSE;
  }


  /**
  * Load faq
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadFaq($id) {
    unset($this->faq);
    if (isset($id) && $id > 0) {
      $sql = "SELECT faq_id, faq_title, faq_descr
                FROM %s
               WHERE faq_id = '%d'
               ORDER BY faq_title ASC";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableFaqs, (int)$id))) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->faq = $row;
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Faq exists
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function faqExists($id) {
    $sql = "SELECT count(*) FROM %s WHERE faq_id = '%d'";
    $params = array($this->tableFaqs, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Faq group exists
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function faqGroupExists($id) {
    $sql = "SELECT count(*) FROM %s WHERE faqgroup_id = '%d'";
    $params = array($this->tableFaqgroups, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Check faq groups
  *
  * @access public
  * @return boolean
  */
  function checkFaqGroups() {
    $sql = "SELECT count(faqgroup_id), faq_id
              FROM %s
             GROUP BY faq_id";
    $params = array($this->tableFaqgroups);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow()) {
        $this->faqs[(int)$row[1]]['groupcount'] = (int)$row[0];
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load faq groups
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadFaqGroups($id) {
    unset($this->faqGroups);
    if ($id) {
      $sql = "SELECT f.faqgroup_id, f.faq_id,
                     f.faqgroup_title, f.faqgroup_descr,
                     f.faqgroup_position,
                     COUNT(e.entry_id) as entry_count
                FROM %s AS f
                LEFT OUTER JOIN %s AS e ON f.faqgroup_id = e.faqgroup_id
               WHERE f.faq_id = %d
               GROUP BY f.faqgroup_id, f.faq_id, f.faqgroup_title,
                     f.faqgroup_descr, f.faqgroup_position
               ORDER BY f.faqgroup_position, f.faqgroup_title ASC,
                     f.faqgroup_id ASC";
    } else {
      return FALSE;
    }
    $params = array($this->tableFaqgroups, $this->tableEntries, (int)$id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $counter = 0;
      $repairPositions = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ((int)$row['faqgroup_position'] != ++$counter) {
          $row['faqgroup_position'] == $counter;
          $repairPositions[(int)$row['faqgroup_id']] = $counter;
        }
        $this->faqGroups[(int)$row['faqgroup_id']] = $row;
      }
      if (count($repairPositions) > 0) {
        $this->saveFaqGroupPositions($repairPositions);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * "repair" faqgroup position values
  *
  * @param array $values
  * @access public
  * @return boolean
  */
  function saveFaqGroupPositions($values) {
    foreach ($values as $faqGroupId => $faqGroupPosition) {
      $data = array(
        'faqgroup_position' => $faqGroupPosition
      );
      $filter = array(
        'faqgroup_id' => $faqGroupId
      );
      if (FALSE === $this->databaseUpdateRecord($this->tableFaqgroups, $data, $filter)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
  * Load faq group
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadFaqGroup($id) {
    unset($this->faqGroups);
    if ($id) {
      $sql = "SELECT faqgroup_id, faq_id, faqgroup_title, faqgroup_descr
                FROM %s
               WHERE faqgroup_id = %d
               ORDER BY faqgroup_title ASC, faqgroup_id ASC";
    } else {
      return FALSE;
    }
    $params = array($this->tableFaqgroups, (int)$id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->faqGroup = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load Entries
  *
  * @param integer $faqGroupId
  * @access public
  * @return boolean
  */
  function loadEntries($faqGroupId) {
    unset($this->entries);
    if ($faqGroupId) {
      $sql = "SELECT entry_id, entry_title, faqgroup_id,
                     entry_question, entry_answer,
                     entry_position
                FROM %s
               WHERE faqgroup_id = %d
               ORDER BY entry_position, entry_question, entry_id DESC";
    } else {
      return FALSE;
    }
    $params = array($this->tableEntries, $faqGroupId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $counter = 0;
      $repairPositions = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ((int)$row['entry_position'] != ++$counter) {
          $row['entry_position'] == $counter;
          $repairPositions[(int)$row['entry_id']] = $counter;
        }
        $this->entries[(int)$row['entry_id']] = $row;
      }
      if (count($repairPositions) > 0) {
        $this->saveEntryPositions($repairPositions);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * "repair" entry position values
  *
  * @param array $values
  * @access public
  * @return boolean
  */
  function saveEntryPositions($values) {
    foreach ($values as $entryId => $entryPosition) {
      $data = array(
        'entry_position' => $entryPosition
      );
      $filter = array(
        'entry_id' => $entryId
      );
      if (FALSE === $this->databaseUpdateRecord($this->tableEntries, $data, $filter)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
  * Load entries
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadEntry($id) {
    unset($this->entries);
    if ($id) {
      $sql = "SELECT faqgroup_id
                FROM %s
               WHERE entry_id = %d
               ORDER BY entry_id DESC";
    } else {
      return FALSE;
    }
    $params = array($this->tableEntries, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->entry = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Entry exists
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function entryExists($id) {
    $sql = "SELECT count(*)
              FROM %s
             WHERE entry_id = '%d'";
    $params = array($this->tableEntries, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }

  /**
  * Load Comments
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadComments($id) {
    unset($this->comments);
    if ($id) {
      $sql = "SELECT note_id, entry_id,
                     note_username, note_content, note_created
                FROM %s
               WHERE entry_id = %d
               ORDER BY note_id DESC";
    } else {
      return FALSE;
    }
    $params = array($this->tableComments, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->comments[(int)$row['note_id']] = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load comments
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function loadComment($id) {
    unset($this->comments);
    if ($id) {
      $sql = "SELECT entry_id
                FROM %s
               WHERE note_id = %d
               ORDER BY note_id DESC";
    } else {
      return FALSE;
    }
    $params = array($this->tableComments, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->comment = $row;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Comment exist
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function commentExists($id) {
    $sql = "SELECT count(*)
              FROM %s
             WHERE note_id = '%d'";
    $params = array($this->tableComments, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        return ((bool)$row[0] > 0);
      }
    }
    return FALSE;
  }
}
?>
