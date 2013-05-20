<?php
/**
* Feed aggregation (planet) class
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
* @version $Id: base_planet.php 32554 2009-10-14 11:22:09Z weinert $
*/

/**
* Database handling
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

define('PAPAYA_PLANET_FEED_STATUS_OK', 0);
define('PAPAYA_PLANET_FEED_STATUS_BROKEN', 1);
define('PAPAYA_PLANET_FEED_STATUS_DISABLED', 2);

define('PAPAYA_PLANET_ENTRY_STATUS_NEW', 0);
define('PAPAYA_PLANET_ENTRY_STATUS_READED', 1);
define('PAPAYA_PLANET_ENTRY_STATUS_DELETED', 2);

/**
* Feed aggregation (planet) class
*
* @package Papaya-Modules
* @subpackage Beta-Planet
*/
class base_planet extends base_db {

  var $tableFeeds;
  var $tableFeedEntries;
  var $tableFeedRelations;
  var $tableFeedLog;

  var $feeds = array();

  /**
  * Constructor
  */
  function __construct() {
    $this->tableFeeds = PAPAYA_DB_TABLEPREFIX.'_planet_feeds';
    $this->tableFeedEntries = PAPAYA_DB_TABLEPREFIX.'_planet_feedentries';
    $this->tableFeedRelations = PAPAYA_DB_TABLEPREFIX.'_planet_feedrel';
    $this->tableFeedLog = PAPAYA_DB_TABLEPREFIX.'_planet_feedlog';
  }

  /**
  * Load entry records from database
  * @param integer $limit
  * @param integer $offset
  * @param integer $entryStatus
  * @param string $sortBy
  * @return boolean
  */
  function loadEntries($limit, $offset = 0, $entryStatus = NULL, $sortBy = 'updated') {
    $this->entries = array();
    if (isset($entryStatus)) {
      $filter =
        "fe.feedentry_status = '".(int)$entryStatus."'";
    } else {
      $filter =
        "NOT(fe.feedentry_status = '".(int)PAPAYA_PLANET_ENTRY_STATUS_DELETED."')";
    }
    if ($sortBy == 'fetched') {
      $order = "fe.feedentry_fetched DESC, fe.feedentry_updated DESC";
    } else {
      $order = "fe.feedentry_updated DESC, fe.feedentry_fetched DESC";
    }
    $sql = "SELECT fe.feedentry_id, fe.feedentry_status,
                   fe.feedentry_ident, fe.feedentry_url,
                   fe.feedentry_updated, fe.feedentry_fetched,
                   fe.feedentry_title,
                   fe.feedentry_summary, fe.feedentry_summary_type,
                   f.feed_id, f.feed_title
              FROM %s AS fe, %s AS f, %s AS rel
             WHERE rel.feedentry_id = fe.feedentry_id
               AND f.feed_id = rel.feed_id
               AND $filter
             ORDER BY $order";
    $params = array(
      $this->tableFeedEntries,
      $this->tableFeeds,
      $this->tableFeedRelations
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
}

?>