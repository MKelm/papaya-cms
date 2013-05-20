<?php
/**
* Date calendar basic functionality
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
* @subpackage Free-Calendar
* @version $Id: base_calendar.php 38021 2013-01-25 14:22:45Z weinert $
*/

/**
* Basic class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');
/**
* Basic cass check conditions
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Date calendar basic functionality
*
*  @package Papaya-Modules
* @subpackage Free-Calendar
*/
class base_calendar extends base_db {

  /**
  * Tables for date translations.
  * @var string $tableDateTrans
  */
  var $tableDateTrans = "";

  /**
  * Tables for regdate translations.
  * @var string $tableRegdateTrans
  */
  var $tableRegdateTrans = "";

  /**
  * Database table dates
  * @var string $tableDates
  */
  var $tableDates = "";
  /**
  * Database table regulars
  * @var string $tableRegdates
  */
  var $tableRegdates = "";
  /**
  * Database table regignore
  * @var string $tableRegignore
  */
  var $tableRegignore = "";
  /**
  * Database table modules
  * @var string $tableModules
  */
  var $tableModules = "";

  /**
  * current content language
  * @var array $currentLanguage
  */
  var $currentLanguage = NULL;

  /**
  * Seconds of day, default 86400
  * @var integer $secOfDay
  */
  var $secOfDay = 86400;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName;
  /**
  * Parameter
  * @var array $params
  */
  var $params;
  /**
  * Base link
  * @var string $baseLink
  */
  var $baseLink = '';

  /**
  * dates
  * @var array $dates
  */
  var $dates;
  /**
  * Selected days
  * @var array $selectedDays
  */
  var $selectedDays;
  /**
  * Mode
  * @var string $mode
  */
  var $mode = '';
  /**
  * Selected Date as array with chronological information.
  * @var array $selectedDate
  */
  var $selectedDate;

  /**
  * Currently loaded calendar date
  * @var selectedDateId
  */
  var $selectedDateId = NULL;

  /**
  * First date
  * @var array $dateFrom
  */
  var $dateFrom = NULL;
  /**
  * Last date
  * @var array $dateTo
  */
  var $dateTo = NULL;

  /**
  * Start on monday
  * @var boolean $startMonday
  */
  var $startMonday = TRUE;
  /**
  * Show edit
  * @var boolean $showEdit
  */
  var $showEdit = FALSE;

  /**
  * Short escriptions for days of week, f.ex. Fr for Friday
  * @var array $wdaysShort
  */
  var $wdaysShort = array('Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su');
  /**
  * Long descriptions for days of week, f.ex. Thuesday
  * @var $wdaysLong
  */
  var $wdaysLong = array(
    'Monday', 'Tuesday', 'Wednesday',
    'Thursday', 'Friday', 'Saturday', 'Sunday'
  );
  /**
  * Long descriptions for months, f.ex. July
  * @var $monthsLong
  */
  var $monthsLong = array(
    'January', 'February', 'March', 'April', 'May', 'June', 'July',
    'August', 'September', 'October', 'November', 'December'
  );

  /**
  * Template string for "quickinfo"
  * @var string $tplQuickInfo
  */
  var $tplQuickInfo = '%s date(s)';
  /**
  * Template string for date
  * @var string $tplDateStr
  */
  var $tplDateStr = 'm/d/Y';

  /**
  * Template string for previous month
  * @var string $tplMonthPrev
  */
  var $tplMonthPrev = 'Previous month';
  /**
  * Template string for next month
  * @var string $tplMonthNext
  */
  var $tplMonthNext = 'Next month';
  /**
  * Template string for current month
  * @var string $tplMonthCurrent
  */
  var $tplMonthCurrent = 'Current month';

  /**
  * Template string for "change"-link
  * @var string $tplChangeLink
  */
  var $tplChangeLink = 'Change';

  /**
  * Template string for "add"-link
  * @var string $tplAddLink
  */
  var $tplAddLink = 'Add date';

  /**
   * Template strings for default translations.
   *
   * @var string $tplDefaultDateTitle
   */
  var $tplDefaultDateTitle = 'New date';

  /**
   * Template strings for default translations.
   *
   * @var string $tplDefaultDateDatestr
   */
  var $tplDefaultDateDatestr = 'when';

  /**
   * Template strings for default translations.
   *
   * @var string $tplDefaultNoTranslation
   */
  var $tplDefaultNoTranslation = 'No translation available';

  /**
   * Template strings for default translations.
   *
   * @var string $tplDefaultRegdateTitle
   */
  var $tplDefaultRegdateTitle = 'New regular date';

  /**
   * Template strings for default translations.
   *
   * @var string $tplDefaultRegdateDatestr
   */
  var $tplDefaultRegdateDatestr = 'when';

  /**
   * Template strings for default translations.
   *
   * @var string $tplDefaultRegdateData
   */
  var $tplDefaultRegdateData = '';

  /**
   * Template strings for default translations.
   *
   * @var string $tplDefaultDateData
   */
  var $tplDefaultDateData = '';

  /**
  * Template integer for default maximum size of regdates.
  * @var string $tplDefaultRegdateMax
  */
  var $tplDefaultRegdateMax = 10;

  /**
  * Template string for "copy date"-link
  * @var string $tplCopyLink
  */
  var $tplCopyLink = 'Copy date';
  /**
  * Template string for "add Regdate date"-link
  * @var string $tplAddLinkReg
  */
  var $tplAddLinkReg = 'Add regular date';
  /**
  * Template string for "delete"-link
  * @var string $tplDelLink
  */
  var $tplDelLink = 'Delete';

  /**
  * Template caption for title
  * @var string $tplCapTitle
  */
  var $tplCapTitle = 'Title';
  /**
  * Template caption for date
  * @var string $tplCapDate
  */
  var $tplCapDate = 'Date (text)';
  /**
  * Template caption for from
  * @var string $tplCapFrom
  */
  var $tplCapFrom = 'From (Y-m-d)';
  /**
  * Template caption for to
  * @var string $tplCapTo
  */
  var $tplCapTo = 'To (Y-m-d)';
  /**
  * Template caption for state
  * @var string $tplCapState
  */
  var $tplCapState = 'State';
  /**
  * Template caption for data / text
  * @var string $tplCapData
  */
  var $tplCapData = 'Text';
  /**
  * Template caption for save
  * @var string $tplSaveCaption
  */
  var $tplSaveCaption = 'Save';
  /**
  * Template caption for delete
  * @var string $tplDelCaption
  */
  var $tplDelCaption = 'Delete';

  /**
  * Template for default title
  * @var string $tplDefaultTitle
  */
  var $tplDefaultTitle = 'New date';

  /**
  * Template message for saved
  * @var string $tplMsgSaved
  */
  var $tplMsgSaved = 'Changes saved.';
  /**
  * Template message for confirm delete
  * @var string $tplDelConfirm
  */
  var $tplDelConfirm = 'Do you really want to delete this date?';

  /**
  * Template input size
  * @var integer $tplInputSize
  */
  var $tplInputSize = 30;
  /**
  * Template input columns
  * @var integer $tplInputCols
  */
  var $tplInputCols = 35;
  /**
  * Template input rows
  * @var integer $tplInputRows
  */
  var $tplInputRows = 10;

  var $currentLanguageId;

  /**
  * Base date
  *
  * @access public
  */
  function __construct() {
    $this->tableDates = PAPAYA_DB_TABLEPREFIX.'_calendar';
    $this->tableRegdates = PAPAYA_DB_TABLEPREFIX.'_calendar_reg';
    $this->tableRegignore = PAPAYA_DB_TABLEPREFIX.'_calendar_reg_ignore';
    $this->tableTagLinks = PAPAYA_DB_TABLEPREFIX.'_tag_links';
    $this->tableDateTrans	= PAPAYA_DB_TABLEPREFIX.'_calendar_trans';
    $this->tableRegdateTrans = PAPAYA_DB_TABLEPREFIX.'_calendar_reg_trans';
    $this->tableTags = PAPAYA_DB_TABLEPREFIX.'_tag_links';
    $this->tableModules = PAPAYA_DB_TBL_MODULES;
  }
  /**
  * Initialize
  *
  * @param string $paramName optional, default value 'cal'
  * @param string $userId optional, default value ''
  * @access public
  */
  function initialize($paramName = 'cal', $userId = '') {
    $this->initLanguageId();
    $this->paramName = $paramName;
    $this->initializeParams();
    if (isset($this->params['time']) && $this->params['time'] > 0) {
      $this->selectedDate = $this->parseTimeToArray($this->params['time']);
    } else {
      $this->selectedDate = $this->parseTimeToArray(time());
    }
  }

  /**
  * Initialize language id variable, wrapper method
  *
  * @access public
  */
  function initLanguageId() {
    if (!isset($this->currentLanguageId)) {
      if (isset($this->lngSelect) && is_object($this->lngSelect)) {
        $this->currentLanguageId = $this->lngSelect->currentLanguageId;
      }
    }
  }

  /**
  * Execute - basic funtion for handling parameters
  *
  * @access public
  */
  function execute() {
    switch (@$this->params['cmd']) {
    case 'day':
      $this->mcalClickDay($this->params['newtime']);
      break;
    case 'wday':
      $this->mcalClickWeekDay($this->params['wday']);
      break;
    case 'week':
      $this->mcalClickWeek($this->params['week']);
      break;
    case 'month':
      $this->mcalClickMonth($this->params['month']);
      break;
    case 'del':
      if ($this->showEdit) {
        $this->mcalClickDelete($this->params['date']);
      }
      break;
    case 'add':
      if ($this->showEdit) {
        $this->params['date'] = $this->add();
      }
    case 'copy':
      if ($this->showEdit) {
        $this->params['date'] = $this->copy();
      }
    case 'date':
      if ($this->showEdit) {
        $this->mcalClickEdit($this->params['date']);
        $row = $this->prepareDateInputProperties(
          $this->loadDate($this->selectedDate));
        if ($this->params['save']) {
          if ($this->checkInput($row)) {
            if ($this->save($row)) {
              $this->addMsg(MSG_INFO, $this->_gt("Data saved."));
            }
          }
        }
      }
      break;
    }
  }

  /**
  * Load modules list
  *
  * @access public
  */
  function loadModulesList() {
    unset($this->cronModules);
    unset($this->contentModules);
    $sql = "SELECT module_guid, module_type, module_class,
                   module_path, module_file, module_title
              FROM %s
             WHERE ((module_type = 'time')
                OR (module_type = 'date'))
               AND module_active = 1";
    if ($res = $this->databaseQueryFmt($sql, $this->tableModules)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['module_type'] == 'time') {
          $this->cronModules[$row['module_guid']] = $row;
        } else {
          $this->contentModules[$row['module_guid']] = $row;
        }
      }
    }
  }

  /**
  * Load dates from calendar.
  *
  * Primary language mode:
  * Set a primary language by using the parameter $lngId. In this case the
  * method is trying to load the specified language content. Dates which have
  * no content in this language are going to be reloaded one time with no direct
  * language specification in the sql statement.
  *
  * @param integer $min
  * @param mixed $max optional, default value NULL
  * @param integer $tagId optional, default value NULL
  * @param integer $lngId optional, set this value to set a primary language
  * @access public
  * @version kelm, 2007-08-23 @ 14:10
  * @return boolean
  */
  function loadDates($min, $max = NULL, $tagId = NULL, $lngId = NULL) {
    // Do not unset $this->dates, if we want to load additional language contents.
    if ($lngId != -1) {
      unset($this->dates);
    }
    if (!isset($max)) {
      $max = $min;
    }
    $this->dateFrom = $this->parseTimeToArray($min);
    $this->dateTo = $this->parseTimeToArray($max);

    $addFilter = (!$this->showEdit) ? 'AND c.date_state = 2' : '';

    // A condition to sort out dates with
    // primary language content which are loaded before.
    $sortOutCondition = '';
    // Set different conditions to load language specific content:
    // -> The first case use the current language:
    if ($lngId === NULL) {
      $lngIdStm = $this->currentLanguageId;
      $lngConditionDate = sprintf('AND tr.lng_id = %d ', $lngIdStm);
      $lngConditionRegDate = sprintf('AND ct.lng_id = %d ', $lngIdStm);
      // -> The second case uses a defined language id as primary language
      //    or loads other languages if the primary language content doesn't exist:
    } else {
      if ($lngId != -1) {
        $lngConditionDate = sprintf('AND (tr.lng_id = %d) ', $lngId);
        $lngConditionRegDate = sprintf('AND (ct.lng_id = %d) ', $lngId);
        // load additional language contents for all dates
        // which has no content in primary language
      } else {
        if (isset($this->dates) && is_array($this->dates) && count($this->dates) > 0) {
          $sortOutCondition = $this->databaseGetSqlCondition(
            'c.date_id', array_keys($this->dates)
          );
          if (count($this->dates) > 1) {
            $sortOutCondition = 'AND '.str_replace('IN', 'NOT IN', $sortOutCondition);
          } else {
            $sortOutCondition = 'AND NOT'.$sortOutCondition;
          }
        }
        $lngConditionDate = sprintf('AND (tr.lng_id > 0) ');
        $lngConditionRegDate = sprintf('AND (ct.lng_id > 0) ');
      }
    }

    if ($tagId) {
      $sql = "SELECT c.date_id, c.date_start, c.date_end,
                    c.author_id, c.date_state,
                    tr.date_title, ct.regdate_title, ct.regdate_id
               FROM %s AS c JOIN %s AS t
               LEFT OUTER JOIN %s AS tr ON (tr.date_id = c.date_id $lngConditionDate)
               LEFT OUTER JOIN %s AS ct ON (ct.regdate_id = c.regdate_id
                                 AND c.regdate_id > 0 $lngConditionRegDate)
              WHERE ((((c.date_start >= %d AND c.date_start <= %d)
                 OR (c.date_end >= %d AND c.date_end <= %d)
                 OR (c.date_start <= %d AND c.date_end >= %d)))
                    $addFilter
                AND ((t.link_type = 'date' AND t.link_id = c.date_id
                     AND t.tag_id = %d AND c.regdate_id = 0)
                 OR (t.link_type = 'regdate' AND t.link_id = c.regdate_id
                     AND t.tag_id = %d AND c.regdate_id <> 0)))
           ORDER BY c.date_start";
      $params = array(
        $this->tableDates,
        $this->tableTags,
        $this->tableDateTrans,
        $this->tableRegdateTrans,
        $min, $max, $min, $max, $min, $max,
        $tagId, $tagId
      );
    } else {
      $sql = "SELECT c.date_id, c.date_start, c.date_end,
                     c.author_id, c.date_state,
                     tr.date_title, ct.regdate_id, ct.regdate_title
                FROM %s AS c
     LEFT OUTER JOIN %s AS tr ON (tr.date_id = c.date_id $lngConditionDate)
     LEFT OUTER JOIN %s AS ct ON (ct.regdate_id = c.regdate_id
                                  AND c.regdate_id > 0 $lngConditionRegDate)
               WHERE ((date_start >= %d AND date_start <= %d)
                      OR (date_end >= %d AND date_end <= %d)
                      OR (date_start <= %d AND date_end >= %d))
            $addFilter
            ORDER BY c.date_start";
      $params = array(
        $this->tableDates,
        $this->tableDateTrans,
        $this->tableRegdateTrans,
        $min, $max, $min, $max, $min, $max);
    }

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->dates[$row['date_id']] = $row;
      }

      if (isset($this->dates) && is_array($this->dates)) {
        $untranslatedDates = FALSE;
        foreach ($this->dates as $row) {
          $row['date_untranslated'] = 0;
          if (!$row['date_title']) {
            if (!$row['regdate_title']) {
              $row['date_title'] = ($this->showEdit) ? '' :
                $this->tplDefaultNoTranslation;
              $row['date_untranslated'] = 1;
            } else {
              $row['date_title'] = $row['regdate_title'];
            }
          }
          // Load translated dates only if a primary language is set
          // because we reload all other entries with alternative language
          // specifications or leave them in database
          if ($lngId !== NULL && $row['date_untranslated'] != 1) {
            $this->dates[$row['date_id']] = $row;
          } elseif ($lngId === NULL) {
            $this->dates[$row['date_id']] = $row;
          }
          if ($lngId !== NULL && $lngId != -1 && $row['date_untranslated'] == 1) {
            $untranslatedDates = TRUE;
            unset($this->dates[$row['date_id']]);
          }
        }
        // reload dates which have untranslated contents if a primary
        // content language is used
        if ($untranslatedDates !== FALSE) {
          $this->loadDates($min, $max, $tagId, -1);
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load current language
  *
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function loadCurrentLanguage($lngId) {
    $sql = "SELECT lng_ident, lng_id, lng_title, lng_short
              FROM %s
             WHERE lng_id = '%s'";
    if ($res = $this->databaseQueryFmt(
          $sql, array($this->tableLanguages, trim($lngId)))) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->currentLanguage = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Loads dates' details using the currently selected language.
  *
  * Primary language mode:
  * Set a primary language by using the parameter $lngId. In this case the
  * method is trying to load the specified language content. Dates which have
  * no content in this language are going to be reloaded one time with no direct
  * language specification in the sql statement.
  *
  * @param integer $min
  * @param mixed $max optional, default value NULL
  * @param integer $tagId optional, default value NULL
  * @param integer $lngId optional, set this value to set a primary language
  * @access public
  * @version kelm, 2007-08-22 @ 18:15
  * @return boolean
  */
  function loadDatesDetails($min, $max = NULL, $tagId = NULL, $lngId = NULL) {
    // Do not unset $this->dates, if we want to load additional language contents.
    if ($lngId != -1) {
      unset($this->dates);
    }
    if (!isset($max)) {
      $max = $min;
    }
    $this->dateFrom = $this->parseTimeToArray($min);
    $this->dateTo = $this->parseTimeToArray($max);

    $addFilter = (!$this->showEdit) ? 'AND c.date_state = 2' : '';

    // A condition to sort out dates with primary language
    // content which are loaded before.
    $sortOutCondition = '';
    // Set different conditions to load language specific content:
    // -> The first case use the current language:
    if ($lngId === NULL) {
      $lngIdStm = $this->currentLanguageId;
      $lngConditionDate = sprintf('AND tr.lng_id = %d ', $lngIdStm);
      $lngConditionRegDate = sprintf('AND ct.lng_id = %d ', $lngIdStm);
      // -> The second case uses a defined language id as primary language
      //    or loads other languages if the primary language content doesn't exist:
    } else {
      if ($lngId != -1) {
        $lngConditionDate = sprintf('AND (tr.lng_id = %d) ', $lngId);
        $lngConditionRegDate = sprintf('AND (ct.lng_id = %d) ', $lngId);
        // load additional language contents for all dates
        // which has no content in primary language
      } else {
        if (isset($this->dates) && is_array($this->dates) && count($this->dates) > 0) {
          $sortOutCondition = $this->databaseGetSqlCondition(
            'c.date_id', array_keys($this->dates)
          );
          if (count($this->dates) > 1) {
            $sortOutCondition = 'AND '.str_replace('IN', 'NOT IN', $sortOutCondition);
          } else {
            $sortOutCondition = 'AND NOT'.$sortOutCondition;
          }
        }
        $lngConditionDate = sprintf('AND (tr.lng_id > 0) ');
        $lngConditionRegDate = sprintf('AND (ct.lng_id > 0) ');
      }
    }

    if ($tagId) {
      $sql = "SELECT c.date_id, c.date_start, c.date_end, c.date_state,
                     c.regdate_id, c.surfer_id, c.surfergroup_id, c.author_id,
                     tr.date_title, tr.date_text, tr.date_data,
                     tr.date_content_guid,
                     ct.regdate_title, ct.regdate_data, ct.regdate_text,
                     ct.regdate_content_guid
                FROM %s AS c
                JOIN %s AS t
     LEFT OUTER JOIN %s AS tr ON (tr.date_id = c.date_id $lngConditionDate)
     LEFT OUTER JOIN %s AS ct ON (ct.regdate_id = c.regdate_id
                                  AND c.regdate_id > 0 $lngConditionRegDate)
               WHERE ((((c.date_start >= %d AND c.date_start <= %d)
                  OR (c.date_end >= %d AND c.date_end <= %d)
                  OR (c.date_start <= %d AND c.date_end >= %d)))
            $addFilter $sortOutCondition
                 AND ((t.link_type = 'date' AND t.link_id = c.date_id
                      AND t.tag_id = %d AND c.regdate_id = 0)
                  OR (t.link_type = 'regdate' AND t.link_id = c.regdate_id
                      AND t.tag_id = %d AND c.regdate_id <> 0)))
            ORDER BY c.date_start";
      $params = array(
        $this->tableDates,
        $this->tableTagLinks,
        $this->tableDateTrans,
        $this->tableRegdateTrans,
        $min, $max, $min, $max,
        $min, $max, $tagId, $tagId);
    } else {
      $sql = "SELECT c.date_id, c.date_start, c.date_end, c.date_state,
                     c.regdate_id, c.surfer_id, c.surfergroup_id, c.author_id,
                     tr.date_title, tr.date_text, tr.date_data,
                     tr.date_content_guid,
                     ct.regdate_title, ct.regdate_data, ct.regdate_text,
                     ct.regdate_content_guid
                FROM %s AS c
     LEFT OUTER JOIN %s AS tr ON (tr.date_id = c.date_id $lngConditionDate)
     LEFT OUTER JOIN %s AS ct ON (ct.regdate_id = c.regdate_id
                                  AND c.regdate_id > 0 $lngConditionRegDate)
               WHERE ((((c.date_start >= %d AND c.date_start <= %d)
                  OR (c.date_end >= %d AND c.date_end <= %d)
                  OR (c.date_start <= %d AND c.date_end >= %d))))
            $addFilter $sortOutCondition
            ORDER BY c.date_start";
      $params = array(
        $this->tableDates,
        $this->tableDateTrans,
        $this->tableRegdateTrans,
        $min, $max, $min, $max, $min, $max
      );
    }

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->dates[$row['date_id']] = $row;
      }

      /*  If there is no individual translation for this date available and
          it is connected to a regular date, then that translation will be used.
      */
      if (isset($this->dates) && is_array($this->dates)) {
        $untranslatedDates = FALSE;
        foreach ($this->dates as $row) {
          $row['date_untranslated'] = 0;
          if (!$row['date_title']) {
            if (!$row['regdate_title']) {
              $row['date_title'] = ($this->showEdit) ? '' :
                $this->tplDefaultNoTranslation;
              $row['date_untranslated'] = 1;
            } else {
              $row['date_title'] = $row['regdate_title'];
              $row['date_text'] = $row['regdate_text'];
              $row['date_data'] = $row['regdate_data'];
              $row['date_content_guid'] = $row['regdate_content_guid'];
            }
          }
          // Load translated dates only if a primary language is set
          // because we reload all other entries with alternative language
          // specifications or leave them in database
          if ($lngId !== NULL && $row['date_untranslated'] != 1) {
            $this->dates[$row['date_id']] = $row;
          } elseif ($lngId === NULL) {
            $this->dates[$row['date_id']] = $row;
          }
          if ($lngId !== NULL && $lngId != -1 && $row['date_untranslated'] == 1) {
            $untranslatedDates = TRUE;
            unset($this->dates[$row['date_id']]);
          }
        }
        // reload dates which have untranslated contents if a primary
        // content language is used
        if ($untranslatedDates !== FALSE) {
          $this->loadDatesDetails($min, $max, $tagId, -1);
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Load date using the current language.
  *
  * Primary language mode:
  * Set a primary language by using the parameter $lngId. In this case the
  * method is trying to load the specified language content. A date which has
  * no content is going to be reloaded one time with no direct
  * language specification in the sql statement.
  *
  * @param integer $dateId
  * @param integer $tagId optional, default value NULL
  * @param integer $lngId optional, set this value to set a primary language
  * @access public
  * @version kelm, 2007-08-23 @ 13:30
  * @return array
  */
  function loadDate($dateId, $tagId = NULL, $lngId = NULL) {
    $result = FALSE;
    $this->dateFrom = array();
    $this->dateTo = array();

    if ($dateId > 0) {
      $addFilter = (!$this->showEdit) ? ' AND c.date_state = 2' : '';

      // Set different conditions to load language specific content:
      // -> The first case use the current language:
      if ($lngId === NULL) {
        $lngIdStm = $this->currentLanguageId;
        $lngConditionDate = sprintf(' AND ct.lng_id = %d ', $lngIdStm);
        // -> The second case uses a defined language id as primary language
        //    or loads other languages if the primary language content doesn't exist:
      } else {
        if ($lngId != -1) {
          $lngConditionDate = sprintf(' AND (ct.lng_id = %d) ', $lngId);
        } else {
          $lngConditionDate = ' AND (ct.lng_id > 0) ';
        }
      }

      if ($tagId) {
        $sql = "SELECT c.date_id, c.date_start, c.date_end, c.date_state,
                       ct.date_title, ct.date_data, ct.date_content_guid, ct.date_text,
                       c.author_id, c.regdate_id, c.surfer_id, c.surfergroup_id, ct.lng_id
                  FROM %s AS c
                  JOIN %s AS t
       LEFT OUTER JOIN %s AS ct ON (ct.date_id = c.date_id $lngConditionDate)
                 WHERE c.date_id = %d
                   $addFilter
                   AND ((t.link_type = 'date' AND t.link_id = c.date_id
                        AND t.tag_id = %d AND c.regdate_id = 0)
                    OR (t.link_type = 'regdate' AND t.link_id = c.regdate_id
                        AND t.tag_id = %d AND c.regdate_id <> 0))";
        $params = array(
          $this->tableDates,
          $this->tableTags,
          $this->tableDateTrans,
          $dateId, $tagId, $tagId
        );
      } else {
        $sql = "SELECT c.date_id, c.date_start, c.date_end, c.date_state,
                       ct.date_title, ct.date_data, ct.date_content_guid, ct.date_text,
                       c.author_id, c.regdate_id, c.surfer_id, c.surfergroup_id, ct.lng_id
                  FROM %s AS c
       LEFT OUTER JOIN %s AS ct ON (ct.date_id = c.date_id $lngConditionDate)
                 WHERE c.date_id = %d
                 $addFilter";
        $params = array(
          $this->tableDates,
          $this->tableDateTrans,
          $dateId
        );
      }

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        $untranslatedDates = FALSE;
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $row['date_untranslated'] = 0;
          $row['date_startf'] = date('Y-m-d', $row['date_start']);
          $row['date_endf'] = date('Y-m-d', $row['date_end']);
          $this->dateFrom = $this->parseTimeToArray($row['date_start']);
          $this->dateTo = $this->parseTimeToArray($row['date_end']);

          if (!$row['date_title']) {
            $row['date_title'] = ($this->showEdit) ? '' :
                $this->tplDefaultNoTranslation;
            $row['date_untranslated'] = 1;
          }
          if (!$row['date_data']) {
            $row['date_data'] = '';
            $row['date_untranslated']++;
          }
          if (!$row['date_text']) {
            $row['date_text'] = '';
            $row['date_untranslated']++;
          }

          // Load translated date only if a primary language is set
          // otherwise we are going to reload it with alternative language
          // specifications or leave it in database
          if ($lngId === NULL ||
              ($lngId !== NULL && $row['date_untranslated'] == 0)) {
            $this->dateId = $row['date_id'];
            $result = $row;
          }
          if ($lngId !== NULL && $lngId != -1 && $row['date_untranslated'] == 1) {
            $untranslatedDates = TRUE;
            unset($this->dates[$row['date_id']]);
          }
        }
        // reload date if it has a untranslated content if a primary
        // content language is used
        if ($untranslatedDates !== FALSE) {
          $this->loadDate($dateId, $tagId, -1);
        }
      }
    }

    $this->loadedDate = $result;
    return $result;
  }

  /**
  * Load details by days
  *
  * Primary language mode:
  * Set a primary language by using the parameter $lngId. In this case the
  * method is trying to load the specified language content. Dates which have
  * no content in this language are going to be reloaded one time with no direct
  * language specification in the sql statement.
  *
  * @param array $days
  * @param integer $tagId optional, default value NULL
  * @param integer $lngId optional, set this value to set a primary language
  * @access public
  * @version kelm, 2007-08-23 @ 14:20
  * @return boolean
  */
  function loadDatesDetailsByDays($days, $tagId = NULL, $lngId = NULL) {
    // Do not unset $this->dates, if we want to load additional language contents.
    if ($lngId != -1) {
      unset($this->dates);
    }
    if (isset($days) && is_array($days)) {
      // sets filter statement
      $filter = '';
      $prefix = "WHERE (";
      foreach ($days as $day) {
        $filter .= sprintf(
          "%s(c.date_start <= %d AND c.date_end >= %d)", $prefix, $day, $day
        );
        if ($prefix == "WHERE (") {
          $prefix = "\n OR ";
        }
      }
      $filter .= ')';
      $filter = preg_replace('/^OR/', '', $filter);
      // checks published or not
      $addFilter = (!$this->showEdit) ? 'AND c.date_state = 2' : '';

      // A condition to sort out dates with
      // primary language content which are loaded before.
      $sortOutCondition = '';
      // Set different conditions to load language specific content:
      // -> The first case use the current language:
      if ($lngId === NULL) {
        $lngIdStm = $this->currentLanguageId;
        $lngConditionDate = sprintf('AND tr.lng_id = %d ', $lngIdStm);
        $lngConditionRegDate = sprintf('AND ct.lng_id = %d ', $lngIdStm);
        // -> The second case uses a defined language id as primary language
        //    or loads other languages if the primary language content doesn't exist:
      } else {
        if ($lngId != -1) {
          $lngConditionDate = sprintf('AND (tr.lng_id = %d) ', $lngId);
          $lngConditionRegDate = sprintf('AND (ct.lng_id = %d) ', $lngId);
          // load additional language contents for
          // all dates which has no content in primary language
        } else {
          if (isset($this->dates) && is_array($this->dates) && count($this->dates) > 0) {
            $sortOutCondition = $this->databaseGetSqlCondition(
              'c.date_id', array_keys($this->dates)
            );
            if (count($this->dates) > 1) {
              $sortOutCondition = 'AND '.str_replace('IN', 'NOT IN', $sortOutCondition);
            } else {
              $sortOutCondition = 'AND NOT'.$sortOutCondition;
            }
          }
          $lngConditionDate = sprintf('AND (tr.lng_id > 0) ');
          $lngConditionRegDate = sprintf('AND (ct.lng_id > 0) ');
        }
      }

      // sql statements depend on tag id
      if ($tagId) {
        $sql = "SELECT c.date_id, c.date_start, c.date_end, c.date_state,
                       c.regdate_id, c.surfer_id, c.surfergroup_id, c.author_id,
                       tr.date_title, tr.date_text, tr.date_data,
                       tr.date_content_guid,
                       ct.regdate_title, ct.regdate_data, ct.regdate_text,
                       ct.regdate_content_guid
                  FROM %s AS c
                  JOIN %s AS t
       LEFT OUTER JOIN %s AS tr ON (tr.date_id = c.date_id $lngConditionDate)
       LEFT OUTER JOIN %s AS ct ON (ct.regdate_id = c.regdate_id
                                    AND c.regdate_id > 0 $lngConditionRegDate)
              $filter
              $addFilter
                   AND ((t.link_type = 'date' AND t.link_id = c.date_id
                        AND t.tag_id = %d AND c.regdate_id = 0)
                    OR (t.link_type = 'regdate' AND t.link_id = c.regdate_id
                        AND t.tag_id = %d AND c.regdate_id <> 0))
              ORDER BY c.date_start";

        $params = array(
          $this->tableDates,
          $this->tableTagLinks,
          $this->tableDateTrans,
          $this->tableRegdateTrans,
          $tagId, $tagId
        );

      } else {
        $sql = "SELECT c.date_id, c.date_start, c.date_end, c.date_state,
                       c.regdate_id, c.surfer_id, c.surfergroup_id, c.author_id,
                       tr.date_title, tr.date_text, tr.date_data,
                       tr.date_content_guid,
                       ct.regdate_title, ct.regdate_data, ct.regdate_text,
                       ct.regdate_content_guid
                  FROM %s AS c
       LEFT OUTER JOIN %s AS tr ON (tr.date_id = c.date_id $lngConditionDate)
       LEFT OUTER JOIN %s AS ct ON (ct.regdate_id = c.regdate_id
                                    AND c.regdate_id > 0 $lngConditionRegDate)
              $filter
              $addFilter
              ORDER BY c.date_start";
        $params = array(
          $this->tableDates,
          $this->tableDateTrans,
          $this->tableRegdateTrans
        );
      }

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->dates[$row['date_id']] = $row;
        }

        /*  If there is no individual translation for this date available and
          it is connected to a regular date, then that translation will be used.
        */
        if (isset($this->dates) && is_array($this->dates)) {
          $untranslatedDates = FALSE;
          foreach ($this->dates as $row) {
            $row['date_untranslated'] = 0;
            if (!$row['date_title']) {
              if (!$row['regdate_title']) {
                $row['date_title'] = ($this->showEdit) ? '' :
                $this->tplDefaultNoTranslation;
                $row['date_untranslated'] = 1;
              } else {
                $row['date_title'] = $row['regdate_title'];
                $row['date_text'] = $row['regdate_text'];
                $row['date_data'] = $row['regdate_data'];
                $row['date_content_guid'] = $row['regdate_content_guid'];
              }
            }
            // Load translated dates only if a primary language is set
            // because we reload all other entries with alternative language
            // specifications or leave them in database
            if ($lngId !== NULL && $row['date_untranslated'] != 1) {
              $this->dates[$row['date_id']] = $row;
            } elseif ($lngId === NULL) {
              $this->dates[$row['date_id']] = $row;
            }
            if ($lngId !== NULL && $lngId != -1 && $row['date_untranslated'] == 1) {
              $untranslatedDates = TRUE;
              unset($this->dates[$row['date_id']]);
            }
          }
          // reload dates which have untranslated contents if a primary
          // content language is used
          if ($untranslatedDates !== FALSE) {
            $this->loadDatesDetailsByDays($days, $tagId, -1);
          }
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get border days
  *
  * @param integer $date
  * @access public
  * @return array
  */
  function getBorderDays($date) {
    $firstDay = mktime(0, 0, 0, $date['month'], 1, $date['year']);
    $lastDay = mktime(0, 0, 0, $date['month'], date('t', $date['time']), $date['year']);
    return array($firstDay, $lastDay);
  }

  /**
  * Get days of month
  *
  * @access public
  */
  function getDaysOfMonth($tagId = NULL) {
    $this->selectedDays = array();
    list($firstDay, $lastDay) = $this->getBorderDays($this->selectedDate);
    if ($this->loadDates($firstDay, $lastDay, $tagId)) {
      if (isset($this->dates) && is_array($this->dates)) {
        foreach ($this->dates as $row) {
          for ($day = (int)@$row['date_start'];
                 $day <= (int)@$row['date_end'];) {
            $dayArray = $this->parseTimeToArray($day);
            @$this->selectedDays[$dayArray['year'].$dayArray['month'].
              $dayArray['day']]++;
            $day = mktime(
              0,
              0,
              0,
              $dayArray['month'],
              $dayArray['day'] + 1,
              $dayArray['year']
            );
          }
        }
      }
    }
  }

  /**
  * Date to array
  *
  * @param integer $date
  * @access public
  * @return array
  */
  function parseTimeToArray($date) {
    $parsed = getdate($date);
    $result['time'] = (string)$date;
    $result['day'] = str_pad($parsed['mday'], 2, '0', STR_PAD_LEFT);
    $result['month'] = str_pad($parsed['mon'], 2, '0', STR_PAD_LEFT);
    $result['year'] = $parsed['year'];
    $result['wday'] = $parsed['wday'];
    return $result;
  }

  /**
  * Get week day
  *
  * @param integer $date
  * @access public
  * @return string
  */
  function getWDay($time) {
    $wDay = date('w', $time);
    return ($wDay == 0) ? 7 : $wDay;
  }

  /**
  * Week day to string
  *
  * @param integer $wday
  * @param boolean $shortStr optional, default value FALSE
  * @access public
  * @return string
  */
  function wDay2Str($wday, $shortStr = FALSE) {
    $result = $this->wDay2Idx($wday);
    if ($shortStr) {
      return $this->wdaysShort[$result - 1];
    } else {
      return $this->wdaysLong[$result - 1];
    }
  }

  /**
  * Week day to index
  *
  * @param integer $wday
  * @access public
  * @return integer
  */
  function wDay2Idx($wday) {
    if (!$this->startMonday) {
      $result = --$wday;
      if ($result == 0) {
        $result = 7;
      }
      return $result;
    } else {
      return $wday;
    }
  }

  /**
  * Build link
  *
  * @param string $caption
  * @param string $url optional, default value ''
  * @param mixed $params optional, default value NULL
  * @param string $target optional, default value ''
  * @param string $class optional, default value ''
  * @param string $title optional, default value ''
  * @param boolean $htmlTag optional, default value TRUE
  * @access public
  * @return string
  */
  function buildLink($caption, $url = '', $params = NULL, $target = '',
                     $class = '', $title = '', $htmlTag = TRUE) {
    if ($url == '') {
      $url = $this->baseLink;
    }
    if (strpos($url, '?')) {
      $queryString = sprintf(
        '&amp;%s[%s]=%s', $this->paramName, 'time', $this->selectedDate['time']
      );
    } else {
      $queryString = sprintf(
        '?%s[%s]=%s', $this->paramName, 'time', $this->selectedDate['time']
      );
    }
    if (isset($params) && is_array($params)) {
      foreach ($params as $key => $val) {
        $queryString .= sprintf(
          '&amp;%s[%s]=%s', $this->paramName, urlencode($key), urlencode($val)
        );
      }
    }
    if ($target == '') {
      $target = '_self';
    }
    if ($htmlTag) {
      if (empty($class)) {
        $result = sprintf(
          '<a href="%s" target="%s" title="%s">%s</a>',
          papaya_strings::escapeHTMLChars($url.$queryString),
          papaya_strings::escapeHTMLChars($target),
          papaya_strings::escapeHTMLChars($title),
          papaya_strings::escapeHTMLChars($caption));
      } else {
        $result = sprintf(
          '<a href="%s" target="%s" class="%s" title="%s">%s</a>',
          papaya_strings::escapeHTMLChars($url.$queryString),
          papaya_strings::escapeHTMLChars($target),
          papaya_strings::escapeHTMLChars($class),
          papaya_strings::escapeHTMLChars($title),
          papaya_strings::escapeHTMLChars($caption));
      }
    } else {
      $result = $url.$queryString;
    }
    return $result;
  }


  /**
  * Click week day
  *
  * @param string $day
  * @access public
  */
  function mcalClickWeekday($day) {
    if ($day > 0) {
      $this->mode = 'wday';
      $this->selectedDate['wday'] = $day;
    }
  }

  /**
  * Click Day
  *
  * @param integer $newTime
  * @access public
  */
  function mcalClickDay($newTime) {
    if ($newTime > 0) {
      $this->mode = 'day';
      $this->selectedDate = $this->parseTimeToArray($newTime);
    }
  }

  /**
  * Click week
  *
  * @param integer $week
  * @access public
  */
  function mcalClickWeek($week) {
    if ($week > 0) {
      $this->mode = 'week';
      $this->selectedDate['week'] = $week;
    }
  }

  /**
  * Click month
  *
  * @param string $month
  * @access public
  */
  function mcalClickMonth($month) {
    $this->mode = 'month';
    $today = $this->selectedDate;

    if ($today['month'] == 0) {
      $today['month'] = 12;
      $today['year']--;
    } else if ($today['month'] == 13) {
      $today['month'] = 1;
      $today['year']++;
    } else {
      $today['month'] = $month;
    }
    if ($today['day'] <= 28) {
      $this->selectedDate = $this->parseTimeToArray(mktime(0, 0, 0));
    } else {
      $dayCount = date('t', mktime(0, 0, 0, $today['month'], 1, $today['year']));
      if ($dayCount < $today['day']) {
        $today['day'] = $dayCount;
      }
    }
    $this->selectedDate = $this->parseTimeToArray(
      mktime(0, 0, 0, $today['month'], $today['day'], $today['year'])
    );
  }

  /**
  * Click topic
  *
  * @param integer $topic
  * @access public
  */
  function mcalClickDate($dateId) {
    $this->selectedDateId = $dateId;
    $this->mode = 'date';
  }

  /**
  * Click edit
  *
  * @param integer $topic
  * @access public
  */
  function mcalClickEdit($dateId) {
    $this->selectedDateId = $dateId;
    $this->mode = 'edit';
  }

  /**
  * Click delete
  *
  * @param integer $topic
  * @access public
  */
  function mcalClickDelete($dateId) {
    $this->selectedDateId = $dateId;
    if (isset($this->params['confirm']) && $this->params['confirm']) {
      $result = $this->deleteDate($dateId);
      $this->mode = 'day';
      return $result;
    } else {
      $this->mode = 'del';
    }
  }

  /**
  * Show month table
  *
  * @access public
  * @return string
  */
  function showMonthTable($tagId = NULL) {
    $this->getDaysOfMonth($tagId);

    $today = $this->parseTimeToArray($this->selectedDate['time']);
    $first = $this->parseTimeToArray(
      mktime(0, 0, 0, $today['month'], 1, $today['year'])
    );
    $monthDay = date('t', $this->selectedDate['time']);

    $result = '<monthcalendar>'.LF;
    $result .= '<monthnav>'.LF;

    $m = $today['month'];
    if ($m < 0) {
      $m = 12;
    }
    if ($m == 1) {
      $mPrev = 12;
    } else {
      $mPrev = $m - 1;
    }
    if ($m == 12) {
      $mNext = 1;
    } else {
      $mNext = $m + 1;
    }

    $href = $this->buildLink(
      $this->_gt($this->tplMonthPrev),
      '',
      array('cmd' => 'month', 'month' => $today['month'] - 1),
      '',
      'mcalmonth',
      '',
      FALSE
    );
    $result .= sprintf(
      '<month position="%s" title="%s" hint="%s" href="%s" month="%d" />'.LF,
      'prior',
      papaya_strings::escapeHTMLChars($this->_gt($this->monthsLong[$mPrev - 1])),
      papaya_strings::escapeHTMLChars(
        $this->_gt($this->monthsLong[$mPrev - 1]).' - '.$this->_gt($this->tplMonthPrev)
      ),
      papaya_strings::escapeHTMLChars($href),
      (int)$mPrev
    );

    $href = $this->buildLink(
      $this->monthsLong[$today['month'] - 1].' '.$today['year'],
      '',
      array('cmd'=>'month', 'month'=>$today['month']),
      '',
      'mcalmonth',
      '',
      FALSE
    );
    $result .= sprintf(
      '<month position="%s" title="%s" hint="%s" href="%s" month="%d" year="%d"/>'.LF,
      'actual',
      papaya_strings::escapeHTMLChars($this->_gt($this->monthsLong[$today['month'] - 1])),
      papaya_strings::escapeHTMLChars(
        $this->_gt($this->monthsLong[$today['month'] - 1]).' - '.$this->_gt($this->tplMonthCurrent)
      ),
      papaya_strings::escapeHTMLChars($href),
      papaya_strings::escapeHTMLChars($today['month']),
      papaya_strings::escapeHTMLChars($today['year']));

    $href = $this->buildLink(
      $this->_gt($this->tplMonthNext),
      '',
      array('cmd' => 'month', 'month' => $today['month'] + 1),
      '',
      'mcalmonth',
      '',
      FALSE
    );
    $result .= sprintf(
      '<month position="%s" title="%s" hint="%s" href="%s" month="%d" />'.LF,
      'next',
      papaya_strings::escapeHTMLChars($this->_gt($this->monthsLong[$mNext - 1])),
      papaya_strings::escapeHTMLChars(
        $this->_gt($this->monthsLong[$mNext - 1]).' - '.$this->_gt($this->tplMonthNext)
      ),
      papaya_strings::escapeHTMLChars($href),
      (int)$mNext
    );
    $result .= '</monthnav>';
    $result .= '<weekdays>'.LF;
    for ($i = 1; $i <= 7; $i++) {
      $href = $this->buildLink(
        $this->wDay2Str($i, TRUE),
        '',
        array('cmd' => 'wday', 'wday' => $this->wDay2Idx($i)),
        '',
        'mcalwday',
        '',
        FALSE
      );
      $result .= sprintf(
        '<wday href="%s" title="%s" hint="%s" wday="%d" />'.LF,
        papaya_strings::escapeHTMLChars($href),
        papaya_strings::escapeHTMLChars($this->_gt($this->wDay2Str($i, TRUE))),
        papaya_strings::escapeHTMLChars($this->_gt($this->wDay2Str($i, FALSE))),
        $i
      );
    }
    $result .= '</weekdays>'.LF;

    $result .= '<weeks>'.LF;
    $y = date('W', mktime(0, 0, 0, $today['month'], 1, $today['year']));
    $href = $this->buildLink(
      $y,
      '',
      array('cmd' => 'week', 'week' => $y),
      '',
      'mcalwday',
      '',
      FALSE
    );
    $result .= sprintf(
      '<week no="%s" href="%s">'.LF,
      $y,
      papaya_strings::escapeHTMLChars($href)
    );
    if ($this->startMonday) {
      $offset = $first['wday'];
    } else {
      $offset = $first['wday'] + 1;
      if ($offset > 7) {
        $offset = 1;
      }
    }
    if ($offset > 1) {
      $result .= sprintf(
        '<day type="spacer" dayspan="%s" />'.LF,
        $offset - 1
      );
    } elseif ($offset == 0) {
      $result .= sprintf(
        '<day type="spacer" dayspan="%s" />'.LF,
        6
      );
    }

    for ($x = 1; $x <= $monthDay; $x++) {
      if (($x == $today['day'] && $today['month'] == date('n')) ||
          (
           $this->selectedDate['day'] == $x &&
           $today['month'] == $this->selectedDate['month']
           )) {
        $isSelected = ' selected="selected"';

      } else {
        $isSelected = '';
      }
      $thisDay = mktime(0, 0, 0, $today['month'], $x, $today['year']);
      $href = $this->buildLink(
        $x,
        '',
        array(
          'cmd' => 'day',
          'newtime' => $thisDay
        ),
        '',
        'mcalday',
        '',
        FALSE
      );
      $thisDate = $today['year'].$today['month'].(($x < 10) ? '0'.$x : $x);
      if (isset($this->selectedDays[$thisDate])) {
        $result .= sprintf(
          '<day type="filled" href="%s" hint="%s" dates="%s" %s>%s</day>'.LF,
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars(
            sprintf(
              $this->_gt($this->tplQuickInfo),
              $this->selectedDays[$thisDate]
            )
          ),
          papaya_strings::escapeHTMLChars($this->selectedDays[$thisDate]),
          $isSelected,
          $x
        );
      } elseif ($this->showEdit) {
        $result .= sprintf(
          '<day type="empty" href="%s" %s>%s</day>'.LF,
          papaya_strings::escapeHTMLChars($href),
          $isSelected,
          $x
        );
      } else {
        $result .= sprintf(
          '<day type="empty" %s>%s</day>'.LF,
          $isSelected,
          $x
        );
      }
      if (!(($x + $offset - 1) % 7) && ($x != $monthDay)) {
        $result .= '</week>'.LF;
        if ($this->startMonday) {
          $y = date(
            'W',
            mktime(0, 0, 0, $today['month'], $x + 1, $today['year'])
          );
        } else {
          $y = date(
            'W',
            mktime(0, 0, 0, $today['month'], $x + 2, $today['year'])
          );
        }
        $href = $this->buildLink(
          $y,
          '',
          array('cmd' => 'week', 'week' => $y),
          '',
          'mcalwday',
          '',
          FALSE
        );
        $result .= sprintf(
          '<week no="%s" href="%s">'.LF,
          papaya_strings::escapeHTMLChars($y),
          papaya_strings::escapeHTMLChars($href)
        );
      }
    }
    $end = 7 - (($x + $offset - 1) % 7) + 1;
    while ($end > 7) {
      $end -= 7;
    }
    if (($end > 0) && ($end < 7)) {
      $result .= sprintf(
        '<day type="spacer" dayspan="%s" />'.LF,
        papaya_strings::escapeHTMLChars($end)
      );
    }
    $result .= '</week>'.LF;
    $result .= '</weeks>'.LF;

    $result .= '</monthcalendar>'.LF;
    return $result;
  }

  /**
  * Show detail
  *
  * @access public
  * @return string
  */
  function showDetail($tagId = NULL) {
    switch ($this->mode) {
    case 'day':
      $result = $this->showDay(TRUE, $tagId);
      break;
    case 'wday':
      $result = $this->showWeekDay(TRUE, $tagId);
      break;
    case 'week':
      $result = $this->showWeek(TRUE, $tagId);
      break;
    case 'date':
      $result = $this->showDate($tagId);
      break;
    case 'del':
      if (!$this->params['delete']) {
        $result = $this->showDelConfirm();
      }
      break;
    case 'edit':
      $result = $this->showEdit(
        $this->prepareDateInputProperties($this->loadDate($this->selectedDateId))
      );
      break;
    default:
      $result = $this->showMonth(TRUE, $tagId);
    }
    return $result;
  }

  /**
  * Show day
  *
  * @param boolean $detail optional, default value TRUE
  * @access public
  * @return string
  */
  function showDay($detail = TRUE, $tagId = NULL) {
    $min = mktime(
      0,
      0,
      0,
      $this->selectedDate['month'],
      $this->selectedDate['day'],
      $this->selectedDate['year']
    );
    $max = mktime(
      23,
      59,
      59,
      $this->selectedDate['month'],
      $this->selectedDate['day'],
      $this->selectedDate['year']
    );
    $this->loadDatesDetails($min, $max, $tagId);
    return $this->showDates(
      date($this->_gt($this->tplDateStr), $this->selectedDate['time']), $detail
    );
  }

  /**
  * Show week days
  *
  * @param boolean $detail optional, default value TRUE
  * @access public
  * @return string
  */
  function showWeekDay($detail = TRUE, $tagId = NULL) {
    if (isset($this->params['wday'])) {
      $this->selectedDate['wday'] = $this->params['wday'];
    }
    list($firstDay, $lastDay) = $this->getBorderDays($this->selectedDate);
    unset($weekDays);
    for ($day = $firstDay;
         $day <= $lastDay;
         $day += $this->secOfDay) {

      if ($this->getWDay($day) == $this->selectedDate['wday']) {
        $weekDays[] = $day;
      }
    }
    $this->loadDatesDetailsByDays($weekDays, $tagId);
    $headString = sprintf(
      '%s - %s %s',
      $this->_gt($this->wDay2Str($this->selectedDate['wday'])),
      $this->_gt($this->monthsLong[$this->selectedDate['month'] - 1]),
      $this->selectedDate['year']
    );

    return $this->showDates($headString, $detail);
  }

  /**
  * Show week
  *
  * @param boolean $detail optional, default value TRUE
  * @access public
  * @return string
  */
  function showWeek($detail = TRUE, $tagId = NULL) {
    if (!isset($this->selectedDate['week'])) {
      $this->selectedDate['week'] = @$this->params['week'];
    }
    $selectedWeek = date('W', $this->selectedDate['time']);
    $diff = ($selectedWeek - $this->selectedDate['week']) * 7 * $this->secOfDay;
    $today = $this->parseTimeToArray($this->selectedDate['time'] - $diff);
    $firstDay = mktime(
      0, 0, 0, $today['month'], $today['day'] - $today['wday'] + 1, $today['year']
    );
    $lastDay = mktime(
      0, 0, 0, $today['month'], $today['day'] - $today['wday'] + 7, $today['year']
    );
    $this->loadDatesDetails($firstDay, $lastDay, $tagId);

    $headString = sprintf(
      '%s - %s',
      date($this->_gt($this->tplDateStr), $firstDay),
      date($this->_gt($this->tplDateStr), $lastDay)
    );
    return $this->showDates($headString, $detail);
  }

  /**
  * Show month
  *
  * @param boolean $detail optional, default value TRUE
  * @access public
  * @return string
  */
  function showMonth($detail = TRUE, $tagId = NULL) {
    list($firstDay, $lastDay) = $this->getBorderDays($this->selectedDate);
    $this->loadDatesDetails($firstDay, $lastDay, $tagId);
    return $this->showDates(
      $this->_gt(
        $this->monthsLong[$this->selectedDate['month'] - 1]
      ).' '.$this->selectedDate['year'],
      $detail
    );
  }


  /**
  * Show Dates
  *
  * @param string $headString
  * @param boolean $detail optional, default value TRUE
  * @param string $url optional, url of calendar page
  * @access public
  * @return string
  */
  function showDates($headString, $detail = TRUE, $url='') {
    if ($this->selectedDate['wday'] == 0) {
      $this->selectedDate['wday'] = 7;
    }
    $this->loadModulesList();
    $result = sprintf(
      '<dategroup mode="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->mode)
    );
    $result .= @sprintf(
      '<date-selected year="%d" month="%d" day="%d" wday="%d" />'.LF,
      papaya_strings::escapeHTMLChars($this->selectedDate['year']),
      papaya_strings::escapeHTMLChars($this->selectedDate['month']),
      papaya_strings::escapeHTMLChars($this->selectedDate['day']),
      papaya_strings::escapeHTMLChars($this->selectedDate['wday'])
    );
    $result .= @sprintf(
      '<date-from year="%d" month="%d" day="%d" wday="%d" />'.LF,
      papaya_strings::escapeHTMLChars($this->dateFrom['year']),
      papaya_strings::escapeHTMLChars($this->dateFrom['month']),
      papaya_strings::escapeHTMLChars($this->dateFrom['day']),
      papaya_strings::escapeHTMLChars($this->dateFrom['wday'])
    );
    $result .= @sprintf(
      '<date-to year="%d" month="%d" day="%d" wday="%d" />'.LF,
      papaya_strings::escapeHTMLChars($this->dateTo['year']),
      papaya_strings::escapeHTMLChars($this->dateTo['month']),
      papaya_strings::escapeHTMLChars($this->dateTo['day']),
      papaya_strings::escapeHTMLChars($this->dateTo['wday'])
    );

    if ($headString) {
      $result .= sprintf(
        '<title>%s</title>'.LF,
        papaya_strings::escapeHTMLChars($headString)
      );
    }
    if (isset($this->dates) && is_array($this->dates)) {
      foreach ($this->dates as $row) {
        $result .= '<date id="'.$row['date_id'].'">'.LF;
        $result .= sprintf(
          '<datetitle>%s</datetitle>'.LF,
          $this->buildLink(
            $row['date_title'],
            $url,
            array('cmd' => 'date', 'date' => $row['date_id']),
            '',
            'mcaldtitle'
          )
        );
        $result .= sprintf(
          '<datestr>%s</datestr>'.LF,
          papaya_strings::escapeHTMLChars($row['date_text'])
        );
        $result .= sprintf(
          '<day>%s</day>'.LF,
          date('Y-m-d', $row['date_start'])
        );
        if (strpos($row['date_data'], '<data') === 0) {
          $content = $row['date_data'];
        } else {
          $content = PapayaUtilStringXml::serializeArray(
            array('text' => $row['date_data'])
          );
        }
        $str = '';
        if (isset($this->contentModules[$row['date_content_guid']])) {
          $module = $this->contentModules[$row['date_content_guid']];
          include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
          $moduleObject = &base_pluginloader::getPluginInstance(
            $module['module_guid'],
            $this,
            $content,
            $module['module_class'],
            $module['module_path'].$module['module_file']
          );
          if (isset($moduleObject) && is_object($moduleObject)) {
            if (method_exists($moduleObject, 'getParsedTeaser')) {
              $str = $moduleObject->getParsedTeaser();
            } elseif (method_exists($moduleObject, 'getParsedData')) {
              $str = $moduleObject->getParsedData();
            }
          }
        }

        $result .= sprintf('<datedetail>%s</datedetail>'.LF, $str);
        $result .= '</date>'.LF;
      }
    }
    $result .= '</dategroup>'.LF;
    return $result;
  }

  /**
  * Show date
  *
  * @access public
  * @return string
  */
  function showDate($tagId = NULL) {
    $result = '';
    if (isset($this->params['date']) && (int)$this->params['date'] > 0) {
      $dateId = (int)$this->params['date'];
    } else {
      $dateId = $this->selectedDateId;
    }
    $row = $this->loadDate($dateId, $tagId);
    $this->loadModulesList();
    if (isset($row) && is_array($row)) {
      if ($this->showEdit) {
        $result = $this->showEdit($row);
      } else {
        $result = sprintf(
          '<date id="%d">',
          (int)$row['date_id']
        );
        $result .= sprintf(
          '<datetitle>%s</datetitle>'.LF,
          papaya_strings::escapeHTMLChars($row['date_title'])
        );
        $result .= sprintf(
          '<datestr>%s</datestr>'.LF,
          papaya_strings::escapeHTMLChars($row['date_text'])
        );
        if (strpos($row['date_data'], '<data') === 0) {
          $content = $row['date_data'];
        } else {
          $content = PapayaUtilStringXml::serializeArray(
            array('text' => $row['date_data'])
          );
        }

        if (isset($this->contentModules[$row['date_content_guid']])) {
          $module = $this->contentModules[$row['date_content_guid']];
          include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
          $moduleObject = &base_pluginloader::getPluginInstance(
            $module['module_guid'],
            $this,
            $content,
            $module['module_class'],
            $module['module_path'].$module['module_file']
          );
        }
        if (isset($moduleObject) && is_object($moduleObject) &&
            method_exists($moduleObject, 'getParsedData')) {
          $str = $moduleObject->getParsedData();
        } else {
          $str = '';
        }

        $result .= sprintf(
          '<datedetail module="%s" guid="%s">%s</datedetail>'.LF,
          papaya_strings::escapeHTMLChars($module['module_class']),
          papaya_strings::escapeHTMLChars($module['module_guid']),
          $str
        );
        $result .= '</date>'.LF;
      }
    }
    return $result;
  }

  /**
  * Show delete confirm dialog
  *
  * @access public
  * @return string
  */
  function showDelConfirm() {
    $row = $this->loadDate($this->selectedDateId);
    $result = sprintf(
      '<msgdialog action="%s" width="100%%" type="question">'.LF,
      $this->buildLink(
        $this->_gt($this->tplChangeLink),
        '',
        array('cmd' => 'del', 'date' => $row['date_id'], 'confirm' => 1),
        '',
        '',
        '',
        FALSE
      )
    );
    if (isset($row) && is_array($row)) {
      $result .= '<message>'.LF;
      $result .= papaya_strings::escapeHTMLChars(
        sprintf(
          $this->_gt('Do you really want to delete date "%s"?').LF,
          $row['date_title']
        )
      );
      $result .= '</message>'.LF;
      $result .= sprintf(
        '<dlgbutton name="%s[delete]" value="%s" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt($this->tplDelCaption))
      );
    }
    $result .= '</msgdialog>'.LF;
    return $result;
  }

  /**
  * Prepare input of an date's properties.
  *
  * @param array $row
  * @access public
  * @return array
  */
  function prepareDateInputProperties($row) {
    $data = $row;
    if (isset($data) && is_array($data)) {

      if (isset($this->params['date_title'])) {
        $data['date_title'] = $this->params['date_title'];
      }
      if (isset($this->params['date_text'])) {
        $data['date_text'] = $this->params['date_text'];
      }
      if (isset($this->params['date_state'])) {
        $data['date_state'] = (int)$this->params['date_state'];
      }
      if (isset($this->params['date_startf'])) {
        if ($this->params['date_startf'] == '') {
          $data['date_start'] = time();
        } else {
          $date = explode('-', $this->params['date_startf']);
          $data['date_start'] = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        }
      }
      if (isset($this->params['surfer_id'])) {
        $data['surfer_id'] = $this->params['surfer_id'];
      }
      if (isset($this->params['surfergroup_id'])) {
        $data['surfergroup_id'] = $this->params['surfergroup_id'];
      }

      $data['date_startf'] = date('Y-m-d', $row['date_start']);
      if (isset($this->params['date_endf'])) {
        $date = explode('-', $this->params['date_endf']);
        $data['date_end'] = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        if ($data['date_end'] < $data['date_start']) {
          $data['date_end'] = $data['date_start'];
        }
      }
      $data['date_endf'] = date('Y-m-d', $data['date_end']);
      return $data;
    } else {
      return $row;
    }
  }

  /**
  * Prepare input of an date's content.
  *
  * @param array $row
  * @access public
  * @return array
  */
  function prepareDateInputContent($row) {
    $data = $row;
    if (isset($data) && is_array($data)) {
      if (isset($this->params['date_data'])) {
        $data['date_data'] = $this->params['date_data'];
      }
      return $data;
    } else {
      return $row;
    }
  }

  /**
  * Show edit
  *
  * @param array $row
  * @access public
  * @return string html
  */
  function showEdit($row) {
    $result = sprintf(
      '<form action="%s" method="POST">',
      $this->buildLink(
        $this->_gt($this->tplChangeLink),
        '',
        array('cmd' => 'edit', 'date' => $row['date_id']),
        '',
        '',
        '',
        FALSE
      )
    );
    $result .= '<table border="0" cellpadding="0" cellspacing="0"><tr>'.
      '<td class="mcalborder">';
    $result .= '<table border="0" cellpadding="2" cellspacing="1" '.
      'class="mcaldetail">';
    if (isset($row) && is_array($row)) {
      if (isset($this->msg)) {
        $result .= sprintf(
          '<tr><th class="mcaldetailhead" colspan="2">%s</th></tr>',
          $this->msg
        );
      }
      $result .= sprintf(
        '<tr><td class="mcaldetail">%s</td><td class="mcaldetail">',
        papaya_strings::escapeHTMLChars($this->_gt($this->tplCapTitle))
      );
      $result .= sprintf(
        '<input type="text" name="%s[date_title]" size="%s" value="%s" /></td></tr>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt($this->tplInputSize)),
        papaya_strings::escapeHTMLChars($row['date_title'])
      );
      $result .= sprintf(
        '<tr><td class="mcaldetail">%s</td><td class="mcaldetail">',
        papaya_strings::escapeHTMLChars($this->_gt($this->tplCapDate))
      );
      $result .= sprintf(
        '<input type="text" name="%s[date_text]" size="%s" value="%s" /></td></tr>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt($this->tplInputSize)),
        papaya_strings::escapeHTMLChars($row['date_text'])
      );
      $result .= sprintf(
        '<tr><td class="mcaldetail">%s</td><td class="mcaldetail">',
        papaya_strings::escapeHTMLChars($this->_gt($this->tplCapFrom))
      );
      $result .= sprintf(
        '<input type="text" name="%s[date_startf]" size="%s" value="%s" /></td></tr>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt($this->tplInputSize)),
        papaya_strings::escapeHTMLChars($row['date_startf'])
      );
      $result .= sprintf(
        '<tr><td class="mcaldetail">%s</td><td class="mcaldetail">',
        papaya_strings::escapeHTMLChars($this->_gt($this->tplCapTo))
      );
      $result .= sprintf(
        '<input type="text" name="%s[date_endf]" size="%s" value="%s" /></td></tr>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt($this->tplInputSize)),
        papaya_strings::escapeHTMLChars($row['date_endf'])
      );
      $result .= sprintf(
        '<tr><td class="mcaldetail">%s</td><td class="mcaldetail">',
        papaya_strings::escapeHTMLChars($this->_gt($this->tplCapState))
      );
      $result .= sprintf(
        '<select name="%s[date_state]">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      if ($row['date_state'] != 2) {
        $result .= sprintf(
          '<option value="1" selected="selected">%s</option>'.
          '<option value="2">%s</option></input></td></tr>',
          papaya_strings::escapeHTMLChars($this->_gt('Created')),
          papaya_strings::escapeHTMLChars($this->_gt('Published'))
        );
      } else {
        $result .= sprintf(
          '<option value="1">%s</option>'.
          '<option value="2" selected="selected">%s</option>',
          papaya_strings::escapeHTMLChars($this->_gt('Created')),
          papaya_strings::escapeHTMLChars($this->_gt('Published'))
        );
      }
      $result .= '</select></td></tr>';
      $result .= sprintf(
        '<tr><td class="mcaldetail" colspan="2">%s</td></tr>'.
        '<td class="mcaldetail" colspan="2">',
        papaya_strings::escapeHTMLChars($this->_gt($this->tplCapData))
      );
      $result .= sprintf(
        '<textarea name="%s[date_data]" cols="%s" rows="%s">%s</textarea></td></tr>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt($this->tplInputCols)),
        papaya_strings::escapeHTMLChars($this->_gt($this->tplInputRows)),
        papaya_strings::escapeHTMLChars($row['date_data'])
      );
      $result .= sprintf(
        '<tr><td colspan="2" align="right" class="mcaldetail">'.
        '<input type="submit" class="btn" name="%s[save]" value="%s"></td></tr>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->_gt($this->tplSaveCaption))
      );
      $result .= sprintf(
        '<tr><td colspan="2" align="right" class="mcaldetail">%s',
        $this->showDelLink($row['date_id'])
      );
    } else {
      $result .= '<tr><td class="mcaldetail">Not Found!</td></tr>';
    }
    $result .= '</table>';
    $result .= '</td></tr></table>';
    $result .= '</form>';
    return $result;
  }

  /**
  * Check input
  *
  * @param array $data
  * @access public
  * @return boolean
  */
  function checkInput($data) {
    $result = TRUE;
    unset($this->errors);
    if (!checkit::isNoHTML($data['date_title'], TRUE)) {
      $this->errors['date_title'] = TRUE;
    }
    if (!checkit::isNoHTML($data['date_text'], TRUE)) {
      $this->errors['date_text'] = TRUE;
    }
    if (!checkit::isNoHTML($data['date_data'], TRUE)) {
      $this->errors['date_data'] = TRUE;
    }
    if (!checkit::check($data['date_startf'], '/^\d{4}\-\d{2}\-\d{2}$/', TRUE)) {
      $this->errors['date_startf'] = TRUE;
    }
    $data['date_state'] = (int)$data['date_state'];
    return $result;
  }

  /**
  * Decode ISO datetime String
  *
  * @param string $isoDateString
  * @access public
  * @return mixed integer or NULL
  */
  function decodeISODatetimeStr($isoDateString) {
    $isoDatetimePattern = '#^([12]\d{3})-(\d|(0\d)|(1[0-2]))-(([012]?\d)|(3[01]))\s+'.
       '((1\d)|(2[0-3])|(0?\d)):([0-6]\d)$#';
    $isoDatePattern = '#^([12]\d{3})-(\d|(0\d)|(1[0-2]))-(([012]?\d)|(3[01]))\s*$#';
    if (preg_match($isoDatetimePattern, $isoDateString, $regs)) {
      return mktime($regs[8], $regs[12], 0, $regs[2], $regs[5], $regs[1]);
    } elseif (preg_match($isoDatePattern, $isoDateString, $regs)) {
      return mktime(0, 0, 0, $regs[2], $regs[5], $regs[1]);
    }
    return NULL;
  }

  /**
  * Show edit link
  *
  * @param integer $topic
  * @access public
  * @return string
  */
  function showEditLink($dateId) {
    if ($dateId > 0) {
      $result = $this->buildLink(
        $this->_gt($this->tplChangeLink),
        '',
        array('cmd' => 'edit', 'date' => $dateId)
      );
    }
    return $result;
  }

  /**
  * Show add link
  *
  * @access public
  * @return string
  */
  function showAddLink() {
    $result = $this->buildLink(
      $this->_gt($this->tplAddLink),
      '',
      array('cmd' => 'add')
    );
    return $result;
  }

  /**
  * Show delete link
  *
  * @param integer $topic
  * @access public
  * @return string
  */
  function showDelLink($dateId) {
    if ($dateId > 0) {
      $result = $this->buildLink(
        $this->_gt($this->tplDelLink),
        '',
        array('cmd' => 'del', 'date' => $dateId)
      );
    }
    return $result;
  }

  /**
  * Load next dates
  *
  * Primary language mode:
  * Set a primary language by using the parameter $lngId. In this case the
  * method is trying to load the specified language content. Dates which have
  * no content in this language are going to be reloaded one time with no direct
  * language specification in the sql statement.
  *
  * @param integer $min
  * @param integer $addDays optional, default value 0
  * @param integer $max optional, default value 1
  * @param integer $tagId optional, default value NULL
  * @param integer $lngId optional, set this value to set a primary language
  * @access public
  * @version kelm, 2007-08-23 @ 14:20
  * @return boolean
  */
  function loadNextDates($min, $max = 1, $tagId = NULL, $lngId = NULL) {
    // Do not unset $this->dates, if we want to load additional language contents.
    if ($lngId != -1) {
      unset($this->dates);
    }
    $minArr = $this->parseTimeToArray($min);
    $minDate = mktime(
      0, 0, 0, $minArr['month'], $minArr['day'], $minArr['year']
    );

    // A condition to sort out dates with primary language
    // content which are loaded before.
    $sortOutCondition = '';
    // Set different conditions to load language specific content:
    // -> The first case use the current language:
    if ($lngId === NULL) {
      $lngIdStm = $this->currentLanguageId;
      $lngConditionDate = sprintf('AND tr.lng_id = %d ', $lngIdStm);
      $lngConditionRegDate = sprintf('AND ct.lng_id = %d ', $lngIdStm);
      // -> The second case uses a defined language id as primary language
      //    or loads other languages if the primary language content doesn't exist:
    } else {
      if ($lngId != -1) {
        $lngConditionDate = sprintf('AND (tr.lng_id = %d) ', $lngId);
        $lngConditionRegDate = sprintf('AND (ct.lng_id = %d) ', $lngId);
        // load additional language contents for all dates
        // which has no content in primary language
      } else {
        if (isset($this->dates) && is_array($this->dates) && count($this->dates) > 0) {
          $sortOutCondition = $this->databaseGetSqlCondition(
            'c.date_id', array_keys($this->dates)
          );
          if (count($this->dates) > 1) {
            $sortOutCondition = 'AND '.str_replace('IN', 'NOT IN', $sortOutCondition);
          } else {
            $sortOutCondition = 'AND NOT'.$sortOutCondition;
          }
        }
        $lngConditionDate = sprintf('AND (tr.lng_id > 0) ');
        $lngConditionRegDate = sprintf('AND (ct.lng_id > 0) ');
      }
    }

    if ($tagId) {
      $sql = "SELECT c.date_id, c.date_start, c.date_end, c.regdate_id,
                     tr.date_title, tr.date_text, tr.date_data,
                     tr.date_content_guid,
                     ct.regdate_title, ct.regdate_text, ct.regdate_data,
                     ct.regdate_content_guid
                FROM %s AS c JOIN %s AS t
     LEFT OUTER JOIN %s AS tr ON (tr.date_id = c.date_id $lngConditionDate)
     LEFT OUTER JOIN %s AS ct ON (ct.regdate_id = c.regdate_id
                                 AND c.regdate_id > 0 $lngConditionRegDate)
               WHERE date_start >= %d
                 AND date_state = 2
                 AND ((t.link_type = 'date' AND t.link_id = c.date_id
                      AND t.tag_id = %d AND c.regdate_id = 0)
                  OR (t.link_type = 'regdate' AND t.link_id = c.regdate_id
                      AND t.tag_id = %d AND c.regdate_id <> 0))
            ORDER BY date_start";
      $params = array(
        $this->tableDates,
        $this->tableTagLinks,
        $this->tableDateTrans,
        $this->tableRegdateTrans,
        $minDate, $tagId, $tagId);
    } else {
      $sql = "SELECT c.date_id, c.date_start, c.date_end, c.regdate_id,
                     tr.date_title, tr.date_text, tr.date_data,
                     tr.date_content_guid,
                     ct.regdate_title, ct.regdate_text, ct.regdate_data,
                     ct.regdate_content_guid
                FROM %s AS c
     LEFT OUTER JOIN %s AS tr ON (tr.date_id = c.date_id $lngConditionDate)
     LEFT OUTER JOIN %s AS ct ON (ct.regdate_id = c.regdate_id
                                 AND c.regdate_id > 0 $lngConditionRegDate)
               WHERE c.date_start >= %d
                 AND c.date_state = 2
            ORDER BY c.date_start";
      $params = array(
        $this->tableDates,
        $this->tableDateTrans,
        $this->tableRegdateTrans,
        $minDate
      );
    }

    if ($res = $this->databaseQueryFmt($sql, $params, $max)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->dates[$row['date_id']] = $row;
      }

      if (isset($this->dates) && is_array($this->dates)) {
        $untranslatedDates = FALSE;
        foreach ($this->dates as $row) {
          $row['date_untranslated'] = 0;
          if (!$row['date_title']) {
            if (!$row['regdate_title']) {
              $row['date_title'] = ($this->showEdit) ? '' :
                  $this->tplDefaultNoTranslation;
              $row['date_untranslated'] = 1;
            } else {
              $row['date_title'] = $row['regdate_title'];
              $row['date_text'] = $row['regdate_text'];
              $row['date_data'] = $row['regdate_data'];
              $row['date_content_guid'] = $row['regdate_content_guid'];
            }
          }
          // Load translated dates only if a primary language is set
          // because we reload all other entries with alternative language
          // specifications or leave them in database
          if ($lngId !== NULL && $row['date_untranslated'] != 1) {
            $this->dates[$row['date_id']] = $row;
          } elseif ($lngId === NULL) {
            $this->dates[$row['date_id']] = $row;
          }
          if ($lngId !== NULL && $lngId != -1 && $row['date_untranslated'] == 1) {
            $untranslatedDates = TRUE;
            unset($this->dates[$row['date_id']]);
          }
        }
        // reload dates which have untranslated contents if a primary
        // content language is used
        if ($untranslatedDates !== FALSE) {
          $this->loadNextDates($min, $max, $tagId, -1);
        }
      }
      return TRUE;
    }
    return FALSE;
  }

}

?>
