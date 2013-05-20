<?php
/**
* Query log analysis module
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
* @subpackage Free-QueryLog
* @version $Id: admin_querylog.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Guestbook base class
*/
require_once(dirname(__FILE__).'/base_querylog.php');

/**
* Query log analysis module
*
* @package Papaya-Modules
* @subpackage Free-QueryLog
*/
class admin_querylog extends base_querylog {
  /**
  * Constructor
  */
  function __construct(&$msgs, $paramName = "qlog") {
    parent::__construct($msgs, $paramName);
    $this->initializeParams();
  }

  /**
  * PHP4 constructor
  */
  function admin_querylog(&$msgs, $paramName = "qlog") {
    $this->__construct($msgs, $paramName);
  }

  /**
  * Basic function for handling parameters
  *
  * Decides which actions to perform depending on the GET/POST paramaters
  * from the paramName array, stored in the params attribute
  *
  * @access public
  */
  function execute() {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'delete_log':
        $this->deleteQueryLog();
        break;
      }
    }
  }

  /**
  * Get page layout
  *
  * Creates the page layout according to parameters
  *
  * @param object xsl_layout &$layout
  * @access public
  */
  function get(&$layout) {
    $this->initializeParams();
    // Use slow_queries as the default command
    if (!(isset($this->params['cmd']) && trim($this->params['cmd'] != ''))) {
      $this->params['cmd'] = 'slow_queries';
    }
    switch ($this->params['cmd']) {
    case 'slow_queries':
      $layout->add($this->getSlowestQueriesList());
      break;
    case 'frequent_queries':
      $layout->add($this->getMostFrequentQueriesList());
      break;
    case 'complicated_queries':
      $layout->add($this->getComplicatedQueriesList());
      break;
    case 'search_queries':
      $layout->add($this->getSearchForm());
      break;
    case 'show_results':
      $layout->add($this->getSearchForm());
      $layout->add($this->showSearchResults());
      break;
    case 'details':
      $layout->add($this->getQueryDetailsDisplay());
      break;
    case 'delete_log':
      $layout->add($this->getDeleteQueryLogForm());
      break;
    }
  }

  /**
  * Internal helper function to truncate URL paths to a given length (from end)
  *
  * @access private
  * @param string $uri
  * @param int $length optional, default 80
  */
  function _truncateUrl($uri, $length = 80) {
    $uri = explode('?', $uri);
    $uri = $uri[0];
    if (strlen($uri) <= $length) {
      return $uri;
    }
    $uri = '...'.substr($uri, -$length, $length);
    return $uri;
  }

  /**
  * Internal helper function to get/set limit and offset
  *
  * @access private
  * @return array
  */
  function _getLimitAndOffset() {
    $limit = 25;
    $offset = 0;
    if (isset($this->params['limit']) && is_numeric($this->params['limit'])) {
      $limit = $this->params['limit'];
    }
    if (isset($this->params['offset']) && is_numeric($this->params['offset'])) {
      $offset = $this->params['offset'];
    }
    return array($limit, $offset);
  }

  /**
  * Display all details of a specified query
  *
  * @access public
  * @return string listview XML
  */
  function getQueryDetailsDisplay() {
    $result = '';
    $query = array();
    if (isset($this->params['query_id']) && $this->params['query_id'] > 0) {
      // Do we have a query id?
      $query = $this->getQueryDetailsById($this->params['query_id']);
    } elseif (isset($this->params['query_hash']) && $this->params['query_hash'] != '') {
      // Or do we have a query content hash?
      $query = $this->getQueryDetailsByHash($this->params['query_hash']);
    }
    // If we've got neither, issue a warning and return
    if (empty ($query)) {
      $this->addMsg(MSG_WARNING, $this->_gt('Query not found.'));
      return $result;
    }
    // Prepare list view
    $result = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Query details'))
    );
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Information'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Details'))
    );
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    $result .= sprintf(
      '<listitem title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Timestamp'))
    );
    $result .= sprintf(
      '<subitem>%s</subitem>'.LF,
      date('Y-m-d H:i:s', $query['query_timestamp'])
    );
    $result .= '</listitem>'.LF;
    $result .= sprintf(
      '<listitem title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('PHP class'))
    );
    $result .= sprintf(
      '<subitem>%s</subitem>'.LF,
      papaya_strings::escapeHTMLChars($query['query_class'])
    );
    $result .= '</listitem>'.LF;
    $result .= sprintf(
      '<listitem title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('URL path'))
    );
    $result .= sprintf(
      '<subitem>%s</subitem>'.LF,
      papaya_strings::escapeHTMLChars($query['query_uri'])
    );
    $result .= '</listitem>'.LF;
    $result .= sprintf(
      '<listitem title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Connection'))
    );
    $result .= sprintf(
      '<subitem>%s</subitem>'.LF,
      papaya_strings::escapeHTMLChars($query['query_conn'])
    );
    $result .= '</listitem>'.LF;
    $result .= sprintf(
      '<listitem title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Duration'))
    );
    $result .= sprintf(
      '<subitem>%s ms</subitem>'.LF,
      papaya_strings::escapeHTMLChars($query['query_time'])
    );
    $result .= '</listitem>'.LF;
    if ($query['query_records'] > 0) {
      $result .= sprintf(
        '<listitem title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Records'))
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_records'])
      );
      $result .= '</listitem>'.LF;
    }
    if ($query['query_limit'] > 0) {
      $result .= sprintf(
        '<listitem title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Limit'))
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_limit'])
      );
      $result .= '</listitem>'.LF;
    }
    if ($query['query_offset'] > 0) {
      $result .= sprintf(
        '<listitem title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Offset'))
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_offset'])
      );
      $result .= '</listitem>'.LF;
    }
    $result .= sprintf(
      '<listitem title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Query'))
    );
    $result .= sprintf(
      '<subitem>%s</subitem>'.LF,
      nl2br(papaya_strings::escapeHTMLChars($query['query_content']))
    );
    $result .= '</listitem>'.LF;
    if (trim($query['query_explain']) != '') {
      $result .= sprintf(
        '<listitem title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Explain'))
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        nl2br(papaya_strings::escapeHTMLChars($query['query_explain']))
      );
      $result .= '</listitem>'.LF;
    }
    if (trim($query['query_backtrace']) != '') {
      $result .= sprintf(
        '<listitem title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Backtrace'))
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        nl2br(papaya_strings::escapeHTMLChars($query['query_backtrace']))
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>';
    return $result;
  }

  /**
  * Get slowest queries as a list view
  *
  * @access public
  * @return string listview XML
  */
  function getSlowestQueriesList() {
    $result = '';
    list($limit, $offset) = $this->_getLimitAndOffset();
    // Get an array of slowest queries and the abs count
    $queries = $this->getSlowestQueries($limit, $offset);
    $numQueries = $this->absCount;
    // Are there queries at all?
    if (count($queries) == 0) {
      $this->addMsg(MSG_INFO, $this->_gt('No logged queries found.'));
      return $result;
    }
    // Create the list view
    $result = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Slowest queries'))
    );
    $result .= $this->_getPaging($this->params['cmd'], $numQueries, $limit, $offset);
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Query'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Duration'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('URL path'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Class'))
    );
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($queries as $query) {
      $detailLink = $this->getLink(
        array(
          'cmd' => 'details',
          'query_id' => $query['query_id']
        )
      );
      $result .= sprintf(
        '<listitem title="%s" href="%s">'.LF,
        papaya_strings::escapeHTMLChars(
          papaya_strings::truncate($query['query_content'], 100)
        ),
        papaya_strings::escapeHTMLChars($detailLink)
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_time'])
      );
      $result .= sprintf(
        '<subitem><span title="%s">%s</span></subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_uri']),
        papaya_strings::escapeHTMLChars(
          $this->_truncateUrl($query['query_uri'])
        )
      );
      $result .= sprintf('<subitem>%s</subitem>'.LF, $query['query_class']);
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Get most frequently used queries (per page) as a list view
  *
  * @access public
  */
  function getMostFrequentQueriesList() {
    $result = '';
    list($limit, $offset) = $this->_getLimitAndOffset();
    // Get an array of most frequently used queries
    $queries = $this->getMostFrequentQueries($limit, $offset);
    $numQueries = $this->absCount;
    // Are there queries at all?
    if (count($queries) == 0) {
      $this->addMsg(MSG_INFO, $this->_gt('No logged queries found.'));
      return $result;
    }
    // Create the list view
    $result = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Most frequent queries per page request'))
    );
    $result .= $this->_getPaging($this->params['cmd'], $numQueries, $limit, $offset);
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Query'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Count'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('URL path'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('PHP class'))
    );
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($queries as $query) {
      $detailLink = $this->getLink(
        array(
          'cmd' => 'details',
          'query_hash' => $query['query_hash']
        )
      );
      $result .= sprintf(
        '<listitem title="%s" href="%s">'.LF,
        papaya_strings::escapeHTMLChars(
          papaya_strings::truncate($query['query_content'], 200)
        ),
        papaya_strings::escapeHTMLChars($detailLink)
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['num_queries'])
      );
      $result .= sprintf(
        '<subitem><span title="%s">%s</span></subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_uri']),
        papaya_strings::escapeHTMLChars(
          $this->_truncateUrl($query['query_uri'])
        )
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_class'])
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Get complicated queries (using temporary and/or filesort) as a list view
  *
  * @access public
  */
  function getComplicatedQueriesList() {
    $result = '';
    list($limit, $offset) = $this->_getLimitAndOffset();
    // Get an array of complicated queries
    $queries = $this->getComplicatedQueries($limit, $offset);
    $numQueries = $this->absCount;
    // Are there queries at all?
    if (count($queries) == 0) {
      $this->addMsg(MSG_INFO, $this->_gt('No complicated queries found.'));
      return $result;
    }
    // Create the list view
    // Create the list view
    $result = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars(
        $this->_gt('Complicated MySQL queries (using temporary and/or filesort)')
      )
    );
    $result .= $this->_getPaging($this->params['cmd'], $numQueries, $limit, $offset);
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Query'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Duration'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('URL path'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Class'))
    );
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($queries as $query) {
      $detailLink = $this->getLink(
        array(
          'cmd' => 'details',
          'query_id' => $query['query_id']
        )
      );
      $result .= sprintf(
        '<listitem title="%s" href="%s">'.LF,
        papaya_strings::escapeHTMLChars(
          papaya_strings::truncate($query['query_content'])
        ),
        papaya_strings::escapeHTMLChars($detailLink)
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_time'])
      );
      $result .= sprintf(
        '<subitem><span title="%s">%s</span></subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_uri']),
        papaya_strings::escapeHTMLChars(
          $this->_truncateUrl($query['query_uri'])
        )
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_class'])
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * get delete query log form
  *
  * Displays a security question before deleting the query log
  *
  * @access public
  * @return string form xml
  */
  function getDeleteQueryLogForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    // Display the question only if the confirm_delete parameter
    // has not been set by a previous run of this method
    if (!(isset($this->params['confirm_delete']) && $this->params['confirm_delete'] == 1)) {
      $hidden = array(
        'cmd' => 'delete_log',
        'confirm_delete' => 1
      );
      $msg = $this->_gt('Do you really want to delete the query log?');
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      return $dialog->getMsgDialog();
    }
  }

  /**
  * Delete the query log
  *
  * Issue a warning if the debug to database option is still on
  *
  * @access public
  */
  function deleteQueryLog() {
    // Get out of here if the confirm_delete option is not set
    if (!(isset($this->params['confirm_delete']) && $this->params['confirm_delete'] == 1)) {
      return;
    }
    $this->databaseDeleteRecord($this->tableQueryLog, NULL);
    $this->addMsg(MSG_INFO, $this->_gt('Query log deleted.'));
    if ((defined('PAPAYA_DBG_LOG_EXPLAIN') && PAPAYA_DBG_LOG_EXPLAIN) ||
        (defined('PAPAYA_DBG_LOG_SLOWQUERIES') && PAPAYA_DBG_LOG_SLOWQUERIES)) {
      $this->addMsg(
        MSG_WARNING,
        $this->_gt(
         'One or more of the options PAPAYA_DBG_LOG_EXPLAIN or PAPAYA_DBG_LOG_SLOWQUERIES'.
         ' are still set to "On" which will result in new log entries.'
        )
      );
    }
  }

  /**
  * Internal helper method to create a paging button bar
  *
  * @access private
  * @param mixed string|array $params command and parameters to be executed by the links
  *              (a simple string is interpreted as cmd parameter, while an associative
  *               array may contain an arbitrary number of parameters)
  * @param int $absCount total number of items
  * @param int $limit number of items per page
  * @param int $offset current item offset
  */
  function _getPaging($params, $absCount, $limit, $offset) {
    // No paging needed if $absCount is less than or equals $limit
    if ($absCount <= $limit) {
      return '';
    }
    // If our parameter is a simple string, set is as the cmd parameter
    if (is_string($params)) {
      $params = array('cmd' => $params);
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_paging_buttons.php');
    return papaya_paging_buttons::getPagingButtons(
      $this,
      $params,
      $offset,
      $limit,
      $absCount,
      21
    );
  }

  /**
  * Get search form
  *
  * Provides a form to search for specific queries
  * You can choose to search either for the SQL text or for all fields with
  * variable text
  *
  * @access public
  * @return string dialog XML
  */
  function getSearchForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'cmd' => 'show_results'
    );
    $fields = array(
      'term' => array(
        'Search for', 'isNoHTML', TRUE, 'input', 100, 'Use * as a placeholder'
      ),
      'scope' => array(
        'Search in',
        '{^(sql|all)$}',
        TRUE,
        'translatedcombo',
        array('sql' => 'SQL', 'all' => 'All fields')
      )
    );
    $data = array();
    if (!empty($this->params['term'])) {
      $data['term'] = $this->params['term'];
    }
    if (!empty($this->params['scope'])) {
      $data['scope'] = $this->params['scope'];
    }
    $searchForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $searchForm->dialogTitle = $this->_gt('Search queries');
    $searchForm->buttonTitle = $this->_gt('Search');
    $searchForm->baseLink = $this->baseLink;
    $searchForm->msgs = &$this->msgs;
    $searchForm->loadParams();
    return $searchForm->getDialogXML();
  }

  /**
  * Show search results
  *
  * Displays the results of a query search as a pageable list
  *
  * @access public
  * @return string listview XML
  */
  function showSearchResults() {
    $result = '';
    // Check parameters
    if (empty($this->params['term'])) {
      $this->addMsg(MSG_WARNING, $this->_gt('Please enter a search term'));
      return;
    }
    $scope = 'sql';
    if (!empty($this->params['scope']) && trim($this->params['scope']) == 'all') {
      $scope = 'all';
    }
    list($limit, $offset) = $this->_getLimitAndOffset();
    // Get an array of search result queries and the abs count
    $queries = $this->searchQueries($this->params['term'], $scope, $limit, $offset);
    $numQueries = $this->absCount;
    // Are there queries at all?
    if (count($queries) == 0) {
      $this->addMsg(MSG_INFO, $this->_gt('No queries match your search term.'));
      return $result;
    }
    // Create the list view
    $result = sprintf(
      '<listview title="%s \'%s\'">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Queries including')),
      papaya_strings::escapeHTMLChars($this->params['term'])
    );
    $pagingParams = array(
      'cmd' => 'show_results',
      'term' => $this->params['term'],
      'scope' => $scope
    );
    $result .= $this->_getPaging($pagingParams, $numQueries, $limit, $offset);
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Query'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Duration'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('URL path'))
    );
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Class'))
    );
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($queries as $query) {
      $detailLink = $this->getLink(
        array(
          'cmd' => 'details',
          'query_id' => $query['query_id']
        )
      );
      $result .= sprintf(
        '<listitem title="%s" href="%s">'.LF,
        papaya_strings::escapeHTMLChars(
          papaya_strings::truncate($query['query_content'], 100)
        ),
        papaya_strings::escapeHTMLChars($detailLink)
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_time'])
      );
      $result .= sprintf(
        '<subitem><span title="%s">%s</span></subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_uri']),
        papaya_strings::escapeHTMLChars(
          $this->_truncateUrl($query['query_uri'])
        )
      );
      $result .= sprintf(
        '<subitem>%s</subitem>'.LF,
        papaya_strings::escapeHTMLChars($query['query_class'])
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Create the main toolbar for the query log management
  *
  * @access public
  */
  function getButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;
    $toolbar->addButton(
      'Slow queries',
      $this->getLink(array('cmd' => 'slow_queries')),
      'items-cronjob',
      'Show slowest queries',
      (empty($this->params['cmd']) || $this->params['cmd'] == 'slow_queries') ? TRUE : FALSE
    );
    $toolbar->addButton(
      'Most frequent queries',
      $this->getLink(array('cmd' => 'frequent_queries')),
      'status-trash-full',
      'Show most frequent queries per page',
      (isset($this->params['cmd']) && $this->params['cmd'] == 'frequent_queries') ? TRUE : FALSE
    );
    $toolbar->addButton(
      'Complicated queries',
      $this->getLink(array('cmd' => 'complicated_queries')),
      'items-bug',
      'Show queries using filesort and/or temporary tables',
      (isset($this->params['cmd']) && $this->params['cmd'] == 'complicated_queries') ? TRUE : FALSE
    );
    $toolbar->addSeperator();
    $toolbar->addButton(
      'Search queries',
      $this->getLink(array('cmd' => 'search_queries')),
      'actions-search',
      'Search for specific queries by SQL or all data',
      (
        isset($this->params['cmd']) &&
        in_array($this->params['cmd'], array('search_queries', 'show_results'))
      ) ? TRUE : FALSE
    );
    $toolbar->addSeperator();
    $toolbar->addButton(
      'Empty query log',
      $this->getLink(array('cmd' => 'delete_log')),
      'places-trash',
      'Delete all entries in query log',
      (isset($this->params['cmd']) && $this->params['cmd'] == 'delete_log') ? TRUE : FALSE
    );
    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(sprintf('<menu ident="edit">%s</menu>'.LF, $str));
    }
  }
}
