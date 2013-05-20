<?php
/**
* Community Calendar Base Class
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license   GNU General Public Licence (GPL) 2 http://www.gnu.org/copyleft/gpl.html
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
*
* @package Papaya-Modules
* @subpackage Beta-Calendar
* @version $Id: base_community_calendar.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
 * class includes
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');
require_once(dirname(__FILE__).'/community_calendar_recurrency_expander.php');

/**
* Community Calendar Base Class
*
* @package Papaya-Modules
* @subpackage Beta-Calendar
*/
class base_community_calendar extends base_db {

  /** @var string name of the table that holds the calendars */
  var $tableCalendars = '';
  /** @var string name of the table that holds the events */
  var $tableEvents = '';
  /** @var string name of the table that holds the weekdays for recurrence */
  var $tableRecurrenceWeekdays = '';
  /** @var string name of the table that holds the months for recurrence */
  var $tableRecurrenceMonths = '';
  /** @var string name of the table that holds the recommendations */
  var $tableRecommendations = '';

  /** @var array localized month-names */
  var $months = array();
  /** @var array localized weekday-names */
  var $weekdays = array();
  /** @var array localized error-messages */
  var $errorNames = array();

  /** @var array edit-fields for event-dialog */
  var $editFields = array();

  /** @var array default values for the edit-fields */
  var $defaultEventData = array();

  /**
  * Constructor
  */
  function __construct() {
    // add prefix to tables
    $this->tableCalendars = PAPAYA_DB_TABLEPREFIX.'_commcal_calendars';
    $this->tableEvents = PAPAYA_DB_TABLEPREFIX.'_commcal_events';
    $this->tableRecurrenceWeekdays = PAPAYA_DB_TABLEPREFIX.'_commcal_recur_weekdays';
    $this->tableRecurrenceMonths = PAPAYA_DB_TABLEPREFIX.'_commcal_recur_months';
    $this->tableRecommendations = PAPAYA_DB_TABLEPREFIX.'_commcal_recommendations';
    $this->tableSurfers = PAPAYA_DB_TBL_SURFER;

    // localize weekday-names
    $this->weekdays = array(
      1 => $this->_gt('Monday'),
      2 => $this->_gt('Tuesday'),
      3 => $this->_gt('Wednesday'),
      4 => $this->_gt('Thursday'),
      5 => $this->_gt('Friday'),
      6 => $this->_gt('Saturday'),
      0 => $this->_gt('Sunday'),
    );

    // localize month-names
    $this->months = array(
      1 => $this->_gt('January'),
      2 => $this->_gt('February'),
      3 => $this->_gt('March'),
      4 => $this->_gt('April'),
      5 => $this->_gt('May'),
      6 => $this->_gt('June'),
      7 => $this->_gt('July'),
      8 => $this->_gt('August'),
      9 => $this->_gt('September'),
      10 => $this->_gt('October'),
      11 => $this->_gt('November'),
      12 => $this->_gt('December'),
    );

    // matches an ISO-Time like m:hh or mm:hh or mm:hh:ss or m:hh:ss
    $ISOTimeRegexp = '/^(\d|[01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/';

    $this->editFields = array(
      'Title',
      'title' => array('Title', 'isNoHTML', TRUE, 'input', 255,
        'Title for the event'
      ),
      'Duration',
      'start_date' => array('Start Date', 'isISODate', TRUE, 'input', 10,
        'Event\'s Start Date in Format YYYY-MM-DD'
      ),
      'start_time' => array('Start Time', $ISOTimeRegexp, FALSE, 'input', 8,
        'Event\'s Start Time in Format HH:MM[:SS] (Seconds are optional)'
      ),
      'end_date' => array('End Date', 'isISODate', TRUE, 'input', 10,
        'Event\'s End Date in Format YYYY-MM-DD'
      ),
      'end_time' => array('End Time', $ISOTimeRegexp, FALSE, 'input', 8,
        'Event\'s End Time in Format HH:MM[:SS] (Seconds are optional)'
      ),
      'Recurrence',
      'recurrence' => array('Recurrence', '/^none|daily|weekly|monthly|yearly$/',
        FALSE, 'radio', array(
          'none' => $this->_gt('None'),
          'daily' => $this->_gt('Daily'),
          'weekly' => $this->_gt('Weekly'),
          'monthly' => $this->_gt('Monthly'),
          'yearly' => $this->_gt('Yearly'),
        ),
        'Select which recurrence from below to use'
      ),
      'Daily Recurrence',
      'recur_daily_interval' => array('Every x Days', 'isNum', FALSE, 'input', 4,
        'Event will repeat every x Days (enter a value for x here)'
      ),
      'Weekly Recurrence',
      'recur_weekly_interval' => array('Every x Weeks', 'isNum', FALSE, 'input', 4,
        'Event will repeat every x Weeks (enter a value for x here)'
      ),
      'recur_weekly_weekdays' => array('On every', '', FALSE, 'checkgroup',
        $this->weekdays, 'Event will repeat on all selected weekdays, every x weeks'
      ),
      'Monthly Recurrence',
      'recur_monthly_interval' => array('Every x Months', 'isNum', FALSE, 'input',
        4, 'Event will repeat every x Months (enter a value for x here)'
      ),
      'recur_monthly_count' => array('On the', 'isNum', FALSE, 'input', 4,
        'Always on the x-th (week-)day of the month. Enter e.g. -1
         for \'last day of the month\''
      ),
      'recur_monthly_byweekday' => array('Week / Day', '/^[01]$/', FALSE, 'combo',
        array(
          0 => $this->_gt('Day'),
          1 => $this->_gt('Weekday'),
        ),
        "Day: Event occurs on the x-th day of the month, \n'.
        'Week: Event occurs on the x-th Weekday of the month"
      ),
      'Yearly Recurrence',
      'recur_yearly_interval' => array('Every x Years', 'isNum', FALSE, 'input',
        4, 'Event repeats every x Years (enter a value for x here)'
      ),
      'recur_yearly_months' => array('In', '', FALSE, 'checkgroup', $this->months,
        'Event repeats every x Years only in selected months'
      ),
      'Recurrence End',
      'recur_end_date' => array('Recurrence End Date', 'isISODate', FALSE, 'input',
        10, 'Date of Event\'s last recurrence in Format YYYY-MM-DD'
      ),
      'recur_end_time' => array('Recurrence End Time', $ISOTimeRegexp, FALSE, 'input',
        8, 'Time of Event\'s last recurrence in Format HH:MM[:SS] (Seconds are optional)'
      ),
      'Location',
      'location' => array('Location', 'isNoHTML', FALSE, 'input', 255,
        'Location of the Event'
      ),
      'Description',
      'description' => array('Description', 'isNoHTML', FALSE, 'textarea', 7,
        'Longer Description of the Event'
      ),
    );
    $this->defaultEventData = array(
      'all_day' => TRUE,
      'start_date' => date('Y-m-d'),
      'start_time' => '00:00:00',
      'end_date' => date('Y-m-d'),
      'end_time' => '23:59:59',
      'recurrence' => 'none',
      'recur_daily_interval' => 1,
      'recur_weekly_interval' => 1,
      'recur_weekly_weekdays' => array((int)date('w')),
      'recur_weekly_byweekday' => 0,
      'recur_monthly_interval' => 1,
      'recur_monthly_count' => date('j'),
      'recur_yearly_interval' => 1,
      'recur_yearly_months' => array((int)date('n')),
    );
  }

  /**
  * adds a Calendar to the database
  *
  * @param array $calendar calendar-array
  * @return mixed id of inserted calendar or FALSE on error
  */
  function addCalendar($calendar) {
    return $this->databaseInsertRecord(
      $this->tableCalendars,
      'calendar_id',
      $calendar
    );
  }

  /**
  * updates an existing calendar in the database
  *
  * @param array $calendar calendar-array
  * @return boolean TRUE on success
  */
  function updateCalendar($calendar) {
    return $this->databaseUpdateRecord(
      $this->tableCalendars,
      $calendar,
      'calendar_id',
      $calendar['calendar_id']
    ) !== FALSE;
  }

  /**
  * deletes a calendar and all events in it from the database
  *
  * @param int $calendarId id of calendar to delete
  * @return boolean true on success
  */
  function deleteCalendar($calendarId) {
    if (!is_int($calendarId)) {
      return FALSE;
    }

    // load events from this calendar
    $events = $this->loadEventsByCalendar($calendarId);

    if (!$events) {
      return FALSE;
    }

    // extract event-ids
    $ids = array();
    foreach ($events as $event) {
      $ids[] = $event['event_id'];
    }

    // delete events
    if (count($ids) > 0) {
      // delete recurrence information for months
      $this->databaseDeleteRecord(
        $this->tableRecurrenceMonths,
        'event_id',
        $ids
      );
      // delete reucrrence information for weekdays
      $this->databaseDeleteRecord(
        $this->tableRecurrenceWeekdays,
        'event_id',
        $ids
      );
      // delete events
      $this->databaseDeleteRecord(
        $this->tableEvents,
        'calendar_id',
        $calendarId
      );
    }

    // finally, delete the calendar itself
    return $this->databaseDeleteRecord(
      $this->tableCalendars,
      'calendar_id',
      $calendarId
    );
  }

  /**
  * get calendar-id for a surfer-id
  *
  * @param int $surferid id of surfer
  * @return mixed calendar-id or FALSE
  */
  function getSurferCalendarId($surferId) {
    $filter = $this->databaseGetSQLCondition('c.surfer_id', $surferId);
    $sql = "
      SELECT c.calendar_id
      FROM %s AS c
      WHERE $filter
    ";
    $result = $this->databaseQueryFmt($sql, $this->tableCalendars);
    if (!$result || $result->count() == 0) {
      return FALSE;
    }

    $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

    return $row['calendar_id'];
  }

  /**
  * loads a Calendar by given id
  *
  * @param integer $id calendar-id
  * @return array calendar-array
  */
  function loadCalendar($id) {

    if (!is_int($id)) {
      return FALSE;
    }

    $filter = $this->databaseGetSQLCondition('c.calendar_id', $id);

    $sql = "
      SELECT c.calendar_id, c.surfer_id, c.title, c.color, s.surfer_handle
      FROM %s AS c
        LEFT JOIN %s AS s ON (s.surfer_id = c.surfer_id)
      WHERE $filter
    ";

    $result = $this->databaseQueryFmt(
      $sql,
      array(
        $this->tableCalendars,
        $this->tableSurfers
      )
    );
    if (!$result) {
      return FALSE;
    }
    $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

    return $row;
  }

  /**
  * loads all public calendars
  *
  * @return array all public calendars
  */
  function loadPublicCalendars() {
    $sql = "
      SELECT c.calendar_id, c.title, c.color
      FROM %s AS c
      WHERE c.surfer_id IS NULL
      ORDER BY c.title
    ";
    $result = $this->databaseQueryFmt($sql, $this->tableCalendars);
    if (!$result || $result->count() == 0) {
      // database error or no public calendars defined
      return FALSE;
    }

    $calendars = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $calendars[] = $row;
    }

    return $calendars;
  }

  /**
  * adds a recommendation for publication to the database
  *
  * @param array $recommendation recommendation-array
  * @return mixed FALSE on error
  */
  function addRecommendation($recommendation) {
    return $this->databaseInsertRecord(
      $this->tableRecommendations, NULL, $recommendation);
  }

  /**
  * loads Recommendations filtered by eventId and surferId (optional)
  *
  * @param integer $eventId event-id
  * @param string $surferId surfer-id (optional)
  * @return array all recommendations
  */
  function loadRecommendations($eventId, $surferId = NULL) {

    if (!is_int($eventId)) {
      return FALSE;
    }

    $filter = $this->databaseGetSQLCondition('r.event_id', $eventId);
    if (!is_null($surferId)) {
      $filter .= ' AND ' . $this->databaseGetSQLCondition('r.surfer_id', $surferId);
    }

    $sql = "
      SELECT
        r.event_id, r.surfer_id, r.calendar_id,
        c.title AS calendar_title,
        s.surfer_handle AS recommendator
      FROM %s AS r
        LEFT JOIN %s AS c ON (r.calendar_id = c.calendar_id)
        LEFT JOIN %s AS s ON (r.surfer_id = s.surfer_id)
      WHERE $filter
    ";

    $result = $this->databaseQueryFmt(
      $sql,
      array(
        $this->tableRecommendations,
        $this->tableCalendars,
        $this->tableSurfers
      )
    );
    if (!$result) {
      return FALSE;
    }

    $recommendations = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $recommendations[] = $row;
    }

    return $recommendations;
  }

  /**
  * load all recommendations
  *
  * @return mixed list of recommendations or FALSE
  */
  function loadRecommendationsList() {
    $sql = "
      SELECT
        r.event_id, r.surfer_id, r.calendar_id,
        r.recommendation_time, e.title AS event_title,
        COUNT(*) AS num_recommendations
      FROM %s AS r
        LEFT JOIN %s AS e ON (r.event_id = e.event_id)
      GROUP BY r.event_id
      ORDER BY r.recommendation_time DESC
    ";

    $result = $this->databaseQueryFmt(
      $sql,
      array(
        $this->tableRecommendations,
        $this->tableEvents
      )
    );
    if (!$result) {
      return FALSE;
    }

    // process database result
    $recommendations = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $recommendations[] = $row;
    }

    return $recommendations;
  }

  /**
  * delte all recommendations for a given event
  *
  * @param int $eventId id of event
  * @return boolean FALSE on error
  */
  function deleteRecommendations($eventId) {
    if (!is_int($eventId)) {
      return FALSE;
    }

    return $this->databaseDeleteRecord($this->tableRecommendations, 'event_id', $eventId);
  }

  /**
  * load Events for a given calendar
  *
  * @param int $calendarId id of calendar
  * @return mixed list of events or FALSE
  */
  function loadEventsByCalendar($calendarId) {

    if (!is_int($calendarId)) {
      return FALSE;
    }

    $filter = $this->databaseGetSQLCondition('calendar_id', $calendarId);
    $sql = "
      SELECT c.title, c.event_id
      FROM %s AS c
      WHERE $filter
      ORDER BY c.start_stamp
    ";

    $result = $this->databaseQueryFmt($sql, $this->tableEvents);
    if (!$result) {
      return FALSE;
    }

    $events = array();
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $events[] = $row;
    }

    return $events;
  }

  /**
  * load Event by given id
  *
  * @param integer $id event-id
  * @return mixed event-array of FALSE on error
  */
  function loadEvent($id) {

    if (!is_int($id)) {
      return FALSE;
    }

    $filter = $this->databaseGetSQLCondition('e.event_id', $id);

    $sql = "
      SELECT
        c.calendar_id aS calendar_id, c.title AS calendar_title,
        c.color AS calendar_color, c.surfer_id,
        s.surfer_handle AS surfer_handle,
        e.event_id, e.calendar_id,
        e.title, e.location, e.description,
        e.start_stamp, e.end_stamp,
        e.recurrence,
        e.recur_yearly_interval,
        e.recur_monthly_interval, e.recur_monthly_count, e.recur_monthly_byweekday,
        e.recur_weekly_interval,
        e.recur_daily_interval,
        e.recur_end,
        w.recur_weekly_weekday,
        y.recur_yearly_month
      FROM %s AS e
        LEFT JOIN %s AS c ON (e.calendar_id = c.calendar_id)
        LEFT JOIN %s AS w ON (e.event_id = w.event_id)
        LEFT JOIN %s AS y ON (e.event_id = y.event_id)
        LEFT JOIN %s AS s ON (c.surfer_id = s.surfer_id)
      WHERE $filter
    ";
    $params = array(
      $this->tableEvents,
      $this->tableCalendars,
      $this->tableRecurrenceWeekdays,
      $this->tableRecurrenceMonths,
      $this->tableSurfers
    );

    $result = $this->databaseQueryFmt($sql, $params);

    if (!$result) {
      return FALSE;
    }

    $event = $this->_processDatabaseResult($result);

    if (count($event) != 1) {
      return FALSE;
    }

    return reset($event);
  }

  /**
  * loads Events from database (filtered by surfer-Id), expands the recurrencies
  * and returns a sorted array with all occurrences
  *
  * @param integer $startStamp timestamp of the relevant timeframe's start
  * @param integer $endStamp timestamp of the relevant timeframe's end
  * @return array all occurrences in the relevant timeframe, sortet by their start
  */
  function loadEvents($startStamp, $endStamp) {
    $result = array();

    $sql = $this->_buildSelectQuery($startStamp, $endStamp);
    $result = $this->databaseQuery($sql);

    if (!$result) {
      return FALSE;
    }

    $events = $this->_processDatabaseResult($result);

    $recurrencyExpander = new community_calendar_recurrency_expander(
      $startStamp, $endStamp
    );
    $occurrences = array();
    // calculate all occurrences for recurring events
    foreach ($events as $event) {
      $recurrences = $recurrencyExpander->expandRecurrencies($event);
      $occurrences = array_merge($occurrences, $recurrences);
    }

    $result = $occurrences;

    // sort occurrences (required in later processing steps)
    $this->_sortOccurrences($result);

    return $result;
  }

  /**
  * updates an existing event in the database
  *
  * @param array $event event-array
  * @return boolean FALSE on error
  */
  function updateEvent($event) {
    $this->_deleteWeekdays($event['event_id']);
    $this->_deleteMonths($event['event_id']);
    switch ($event['recurrence']) {
    case 'yearly':
      $this->_saveMonths($event['event_id'], $event);
      break;
    case 'weekly':
      $this->_saveWeekdays($event['event_id'], $event);
      break;
    }

    unset($event['recur_yearly_months']);
    unset($event['recur_weekly_weekdays']);

    return $this->databaseUpdateRecord(
      $this->tableEvents,
      $event,
      'event_id',
      $event['event_id']
    ) !== FALSE;
  }

  /**
  * deltes an event from the database
  *
  * @param integer $eventId event-id
  * @return boolean FALSE on error
  */
  function deleteEvent($eventId) {
    if (!is_int($eventId)) {
      return FALSE;
    }
    $this->deleteRecommendations($eventId);
    $this->_deleteWeekdays($eventId);
    $this->_deleteMonths($eventId);
    return $this->databaseDeleteRecord($this->tableEvents, 'event_id', $eventId);
  }

  /**
  * adds a new event to the database
  *
  * @param array $event valid event-array
  * @return boolean TFALSE on failure, id of event on success
  */
  function addEvent($event) {

    $fields = array(
      'event_id', 'calendar_id',
      'title', 'location', 'description',
      'start_stamp', 'end_stamp', 'end_year', 'end_month', 'recurrence',
      'recur_yearly_interval',
      'recur_monthly_interval', 'recur_monthly_count', 'recur_monthly_byweekday',
      'recur_weekly_interval',
      'recur_daily_interval',
      'recur_end'
    );

    $toDB = array();
    foreach ($fields as $field) {
      if (!isset($event[$field])) {
        continue;
      }
      $toDB[$field] = $event[$field];
    }

    // save event
    $event_id = $this->databaseInsertRecord($this->tableEvents, 'event_id', $toDB);

    if (!$event_id) {
      // database error
      return FALSE;
    }

    // save selected months / weekdays
    switch($event['recurrence']) {
    case 'weekly':
      return $this->_saveWeekdays($event_id, $event) !== FALSE;
      break;
    case 'yearly':
      return $this->_saveMonths($event_id, $event) !== FALSE;
      break;
    }

    return $event_id;
  }

  /**
  * copy an event while modifying certain fields
  *
  * @param int $eventId id of event
  * @param array $override fields to change
  * @return mixed id of new event or FALSE
  */
  function copyEvent($eventId, $override) {

    if (!is_int($eventId)) {
      return FALSE;
    }

    // get old event
    $filter = $this->databaseGetSQLCondition('e.event_id', $eventId);
    $sql = "
      SELECT
        e.event_id, e.calendar_id, e.title, e.location, e.description,
        e.start_stamp, e.end_stamp, e.end_year, e.end_month, e.recurrence,
        e.recur_yearly_interval, e.recur_monthly_interval, e.recur_monthly_count,
        e.recur_monthly_byweekday, e.recur_weekly_interval,
        e.recur_daily_interval, e.recur_end,
        y.recur_yearly_month, w.recur_weekly_weekday
      FROM %s AS e
        LEFT JOIN %s AS y ON (e.event_id = y.event_id)
        LEFT JOIN %s AS w ON (e.event_id = w.event_id)
      WHERE $filter
    ";

    $result = $this->databaseQueryFmt(
      $sql,
      array(
        $this->tableEvents,
        $this->tableRecurrenceMonths,
        $this->tableRecurrenceWeekdays
      )
    );

    if (!$result || $result->count() == 0) {
      return FALSE;
    }

    $event = reset($this->_processDatabaseResult($result));

    // modify event
    $event = array_merge($event, $override);

    // save copy
    $newEventId = $this->addEvent($event);
    if (!$newEventId) {
      return FALSE;
    }

    // delete recommendations from old event
    $this->deleteRecommendations($eventId);

    return $newEventId;
  }

  /**
  * process a database-result into an array of event array by joining recurrence
  * information from several lines into arrays
  *
  * @access private
  * @param dbresult_base $result database result object
  * @return array array of event-arrays
  */
  function _processDatabaseResult($result) {
    $events = array();
    // read rows from database-result
    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
      $id = $row['event_id'];
      if (!isset($events[$id])) {
        // event that has not yet been processed
        $events[$id] = $row;
        unset($events[$id]['recur_weekly_weekday']);
        unset($events[$id]['recur_yearly_month']);
      }

      // collect recurrence information (selected weekdays / months) from
      // multiple rows into arrays
      switch ($row['recurrence']) {
      case 'weekly':
        $events[$id]['recur_weekly_weekdays'][] = $row['recur_weekly_weekday'];
        break;
      case 'yearly':
        $events[$id]['recur_yearly_months'][] = $row['recur_yearly_month'];
        break;
      }
    }

    foreach ($events as $event) {
      $events[$event['event_id']]['recur_human_readable'] =
        $this->_getHumanReadableRecurrence($event);
    }

    return $events;
  }

  /**
  * saves an event's recurrence weekdays into the corresponding table
  *
  * @access private
  * @param integer $id id of the event
  * @param array $event event-array
  * @return boolean FALSE on error
  */
  function _saveWeekdays($id, $event) {
    $records = array();
    foreach ($event['recur_weekly_weekdays'] as $weekday) {
      $records[] = array('event_id' => $id, 'recur_weekly_weekday' => $weekday);
    }

    return $this->databaseInsertRecords($this->tableRecurrenceWeekdays, $records);
  }

  /**
  * deletes an event's recurrence weekdays from the database
  *
  * @access private
  * @param integer $eventId event-id
  * @return boolean FALSE on error
  */
  function _deleteWeekdays($eventId) {
    return $this->databaseDeleteRecord(
      $this->tableRecurrenceWeekdays,
      'event_id',
      $eventId
    );
  }

  /**
  * saves an event's recurrence months into the corresponding table
  *
  * @access private
  * @param integer $id id of the event
  * @param array $event event-array
  * @return boolen FALSE on error
  */
  function _saveMonths($id, $event) {
    $records = array();
    foreach ($event['recur_yearly_months'] as $month) {
      $records[] = array('event_id' => $id, 'recur_yearly_month' => $month);
    }

    return $this->databaseInsertRecords($this->tableRecurrenceMonths, $records);
  }

  /**
  * deletes an event's recurrence months from the database
  *
  * @access private
  * @param integer $eventId event-id
  * @return boolen FALSE on error
  */
  function _deleteMonths($eventId) {
    return $this->databaseDeleteRecord(
      $this->tableRecurrenceMonths,
      'event_id',
      $eventId
    );
  }

  /**
  * builds the SQL select query needed to fetch all events that occurr within the
  * relevant timeframe
  *
  * @access private
  * @param integer $start timestamp of relevant timeframe's start
  * @param integer $end timestamp of relevant timeframe's end
  * @return string the SQL Query
  */
  function _buildSelectQuery($start, $end) {

    if (!is_int($start) || !is_int($end)) {
      return FALSE;
    }

    // WHERE-part for yearly recurrence
    $years = array();
    for ($year = (int)date('Y', $start); $year <= date('Y', $end); ++$year) {
      $years[] = sprintf('MOD((%d - e.end_year), e.recur_yearly_interval)', $year);
    }

    // WHERE-part for monthly recurrence
    $months = array();
    $passedMonths = array();
    for ($stamp = $start; $stamp <= $end; $stamp = strtotime('+1 month', $stamp)) {
      $month = (int)date('n', $stamp);
      $passedMonths[] = sprintf(
        'MOD(((%d-e.end_year) * 12) + (%d-e.end_month), e.recur_monthly_interval)',
        (int)date('Y', $stamp),
        $month
      );
      if (in_array($month, $months)) {
        continue;
      }
      $months[] = $month;
    }

    $surferObj = &base_surfer::getInstance();

    $surferFilter = '';
    if ($surferObj->isValid) {
      $surferFilter = 'OR '.$this->databaseGetSQLCondition(
        'c.surfer_id', $surferObj->surferId
      );
    }

    $sql = sprintf(
      "SELECT
        c.title AS calendar_title, c.color AS calendar_color, c.surfer_id,
        e.event_id, e.calendar_id,
        e.event_id, e.start_stamp, e.end_stamp, e.end_month, e.end_year,
        e.title, e.location, e.description,
        e.recurrence,
        e.recur_yearly_interval, y.recur_yearly_month,
        e.recur_monthly_interval, e.recur_monthly_count, e.recur_monthly_byweekday,
        e.recur_weekly_interval, w.recur_weekly_weekday,
        e.recur_daily_interval,
        e.recur_end
      FROM %s AS c
        LEFT JOIN %s AS e ON (c.calendar_id = e.calendar_id)
        LEFT JOIN %s AS w ON (e.event_id = w.event_id)
        LEFT JOIN %s AS y ON (e.event_id = y.event_id)
      WHERE

        (c.surfer_id IS NULL $surferFilter)

        AND

        /* events without recurrence */
        ((e.recurrence != 'none') OR (
             (e.start_stamp >= %d AND e.start_stamp <= %d)
          OR (e.end_stamp   >= %d AND e.end_stamp   <= %d)
          OR (e.start_stamp <  %d AND e.end_stamp   >  %d)
        ))

        AND

        /* check recur_end */
        (e.recur_end IS NULL OR e.recur_end >= %d)

        AND

        /* events with yearly recurrence */
        (e.recurrence != 'yearly' OR (
          (0 IN (
              %s
            )
          )
          AND y.recur_yearly_month IN (%s)
        ))

        AND

        /* events with monthly recurrence */
        (e.recurrence != 'monthly' OR (
          0 IN (
            %s
          )
        ))

        AND

        /* events with weekly recurrence */
        (e.recurrence != 'weekly' OR (
          FLOOR((%d - e.end_stamp) / (60 * 60 * 24 * 7 * e.recur_weekly_interval))
          <
          FLOOR((%d - e.end_stamp) / (60 * 60 * 24 * 7 * e.recur_weekly_interval))
        ))

        AND

        /* events with daily recurrence */
        (e.recurrence != 'daily' OR (
          FLOOR((%d - e.end_stamp) / (60 * 60 * 24 * e.recur_daily_interval))
          <
          FLOOR((%d - e.end_stamp) / (60 * 60 * 24 * e.recur_daily_interval))
        ))
      ",
      $this->tableCalendars,
      $this->tableEvents,
      $this->tableRecurrenceWeekdays,
      $this->tableRecurrenceMonths,
      $start,
      $end,
      $start,
      $end,
      $start,
      $end,
      $start,
      implode(",\r\n            ", $years),
      implode(', ', $months),
      implode(",\r\n          ", $passedMonths),
      $start,
      $end,
      $start,
      $end
    );

    return $sql;
  }

  /**
  * sort Occurrences
  * sort an array with occurrences by the occurrences starts
  *
  * @access private
  * @param array $occurrences array with occurrences (will be sorted)
  * @return void (parameter will be modified)
  */
  function _sortOccurrences(&$occurrences) {
    usort($occurrences, array($this, '_sortOccurrencesCallback'));
  }

  /**
  * sort Occurrences Callback
  * callback used by usort, compares the element 'start' of given arrays
  *
  * @access private
  * @param array $a
  * @param array $b
  * @return integer -1 if $a['start'] is smaller or equal $b['start'], 1 otherwise
  */
  function _sortOccurrencesCallback($a, $b) {
    return $a['start'] <= $b['start'] ? -1 : 1;
  }

  /**
  * get ordinal (1st, 2nd, 3rd, 4th - this function returns st, nd, rd, th
  * for given number
  *
  * @param int $nr number
  * @return string ordinal for given number
  */
  function _getOrdinal($nr) {
    $digit = substr($nr, -1);
    if (!in_array($digit, array(1, 2, 3)) || in_array($nr, array(11, 12, 13))) {
      return 'th';
    }
    switch ($digit) {
    case 1:
      return 'st';
    case 2:
      return 'nd';
    case 3:
      return 'rd';
    }
    return 'th';
  }

  /**
  * parse an event's recurrence information to make it easier to understand by humans
  *
  * @param array $event the event
  * @return string readable description of event's recurrence information
  */
  function _getHumanReadableRecurrence($event) {
    switch($event['recurrence']) {
    // No Recurrence
    case 'none':
      return $this->_gt('None');
    case 'daily':
      if ($event['recur_daily_interval'] == 1) {
        return $this->_gt('Every Day');
      }
      return $this->_gtf('Every %d Days', $event['recur_daily_interval']);
    case 'weekly':
      if ($event['recur_weekly_interval'] == 1) {
        $result = $this->_gt('Every Week');
      } else {
        $result = $this->_gtf('Every %d Weeks', $event['recur_weekly_interval']);
      }
      $weekdays = array();
      foreach ($event['recur_weekly_weekdays'] as $weekday) {
        $weekdays[] = $this->weekdays[$weekday];
      }
      if (count($weekdays) == 1) {
        $result .= $this->_gtf(' on %s', reset($weekdays));
      } else {
        $lastWeekday = array_pop($weekdays);
        $weekdayList = implode(', ', $weekdays);
        $result .= $this->_gtf(' on %s and %s', array($weekdayList, $lastWeekday));
      }
      return $result;
    case 'monthly':
      if ($event['recur_monthly_interval'] == 1) {
        $result = $this->_gt('Every Month');
      } else {
        $result = $this->_gt('Every %d Months', $event['recur_monthly_interval']);
      }
      $append = '';
      if ($event['recur_monthly_count'] > 0) {
        $result .= $this->_gtf(
          ' on the %d%s',
          array(
            $event['recur_monthly_count'],
            $this->_gt($this->_getOrdinal($event['recur_monthly_count']))
          )
        );
      } else {
        if ($event['recur_monthly_count'] == -1) {
          $result .= $this->_gt(' on the last');
        } else {
          $result .= $this->_gtf(
            ' on the %d%s',
            array(
              abs($event['recur_monthly_count']),
              $this->_gt($this->_getOrdinal($event['recur_monthly_count']))
            )
          );
          $append = $this->_gt(' from the end');
        }
      }
      if ($event['recur_monthly_byweekday'] == 1) {
        $result .= ' '.$this->_gt(date('l', $event['start_stamp']));
      } else {
        $result .= $this->_gt(' day');
      }
      $result .= $append;
      return $result;
    case 'yearly':
      if ($event['recur_yearly_interval'] == 1) {
        $result = $this->_gt('Every Year');
      } else {
        $result = $this->_gtf('Every %d Years', $event['recur_yearly_interval']);
      }
      $months = array();
      foreach ($event['recur_yearly_months'] as $month) {
        $months[] = $this->months[$month];
      }
      $day = date('d', $event['start_stamp']);
      $result .= $this->_gtf(
        ' on the %d%s',
        array($day, $this->_gt($this->_getOrdinal($day)))
      );
      if (count($months) == 1) {
        $result .= $this->_gtf(' of %s', reset($months));
      } else {
        $lastMonth = array_pop($months);
        $monthList = implode(', ', $months);
        $result .= $this->_gtf(' of %s and %s', array($monthList, $lastMonth));
      }
      return $result;
    }
  }

  /**
  * validate Event input Dialog (standard checkdialoginput + recurrence information)
  *
  * @access private
  * @param base_dialog $inputDialog input dialog to check
  * @return boolen TRUE if all input is correct
  */
  function validateEventInputDialog(&$inputDialog) {
    $params = &$this->module->params;

    // if checkDialogInput returns FALSE, check the rest anyway so the user can
    // see ALL errors (increases usability)
    $result = $inputDialog->checkDialogInput();

    if (!isset($params['recurrence'])) {
      return $result;
    }

    if ($params['recurrence'] != 'none') {
      // remove all errors detected on recurrences, so only errors for the
      // selected recurrence will be reported
      $inputDialog->inputErrors['recur_daily_interval'] = 0;
      $inputDialog->inputErrors['recur_weekly_interval'] = 0;
      $inputDialog->inputErrors['recur_weekly_weekdays'] = 0;
      $inputDialog->inputErrors['recur_monthly_inverval'] = 0;
      $inputDialog->inputErrors['recur_monthly_count'] = 0;
      $inputDialog->inputErrors['recur_monthly_byweekday'] = 0;
      $inputDialog->inputErrors['recur_yearly_interval'] = 0;
      $inputDialog->inputErrors['recur_yearly_months'] = 0;
    }

    switch($params['recurrence']) {
    case 'daily':
      // interval must be > 0
      if ($params['recur_daily_interval'] < 1) {
        $inputDialog->inputErrors['recur_daily_interval'] = 1;
        $result = FALSE;
      }
      break;
    case 'weekly':
      // interval must be > 0
      if ($params['recur_weekly_interval'] < 1) {
        $inputDialog->inputErrors['recur_weekly_interval'] = 1;
        $result = FALSE;
      }
      // at least one weekday must be selected
      if (!isset($params['recur_weekly_weekdays']) ||
          !is_array($params['recur_weekly_weekdays']) ||
          count($params['recur_weekly_weekdays']) == 0
      ) {
        $inputDialog->inputErrors['recur_weekly_weekdays'] = 1;
        $result = FALSE;
        break;
      }
      // no weekday may be selected twice (only possible if the form was manipulated)
      if (count(array_unique($params['recur_weekly_weekdays'])) !==
          count($params['recur_weekly_weekdays'])
      ) {
        $inputDialog->inputErrors['recur_weekly_weekdays'] = 1;
        $result = FALSE;
      }
      // validate selected weekdays
      if (!$this->_checkArray(
           $params['recur_weekly_weekdays'], array_flip($this->calendarObj->weekdays)
         )) {
        $inputDialog->inputErrors['recur_weekly_weekdays'] = 1;
        $result = FALSE;
      }
      break;
    case 'monthly':
      // interval must be > 0
      if ($params['recur_monthly_interval'] < 1) {
        $inputDialog->inputErrors['recur_monthly_interval'] = 1;
        $result = FALSE;
      }
      // valid range for count is -31 to 31 while 0 is not allowed
      if (abs($params['recur_monthly_count']) > 31 ||
          $params['recur_monthly_count'] == 0
      ) {
        $inputDialog->inputErrors['recur_monthly_count'] = 1;
        $result = FALSE;
      }
      break;
    case 'yearly':
      // interval must be > 0
      if ($params['recur_yearly_interval'] < 1) {
        $inputDialog->inputErrors['recur_yearly_interval'] = 1;
        $result = FALSE;
      }
      // at least one month must be selected
      if (!isset($params['recur_yearly_months']) ||
          !is_array($params['recur_yearly_months']) ||
          count($params['recur_yearly_months']) == 0
      ) {
        $inputDialog->inputErrors['recur_yearly_months'] = 1;
        $result = FALSE;
        break;
      }
      // no month may be selected twice (only possible if form was manipulated)
      if (count(array_unique($params['recur_yearly_months'])) !==
          count($params['recur_yearly_months'])
      ) {
        $inputDialog->inputErrors['recur_yearly_months'] = 1;
        $result = FALSE;
      }
      // validate selected months
      if (!$this->_checkArray(
            $params['recur_yearly_months'], array_flip($this->calendarObj->months)
         )) {
        $inputDialog->inputErrors['recur_yearly_months'] = 1;
        $result = FALSE;
      }
      break;
    }
    return $result;
  }


  /**
  * get event from params
  * extracts and processes the parameters from $this->contentObj into an event-array
  *
  * @param array $params parameters (GET or POST etc)
  * @return array event-array
  */
  function getEventFromParams(&$params) {

    // timestamp of event's start
    $startTime = $params['start_time'];
    if (empty($startTime)) {
      $startTime = '00:00:00';
    }
    if (strlen($startTime) === 5) {
      $startTime .= ':00';
    }
    $startDateTime = $params['start_date'].' '.$startTime;
    $startStamp = checkit::convertISODateTimeToUnix($startDateTime);

    // timestamp of event's end
    $endTime = $params['end_time'];
    if (empty($endTime)) {
      $endTime = '23:59:59';
    }
    if (strlen($endTime) === 5) {
      $endTime .= ':00';
    }
    $endDateTime = $params['end_date'].' '.$endTime;
    $endStamp = checkit::convertISODateTimeToUnix($endDateTime);

    // timestamp of last occurrence
    $recurEndStamp = NULL;
    if (!empty($params['recur_end_date'])) {
      $recurEndDate = $params['recur_end_date'];
      $recurEndTime = '';
      if (!empty($params['recur_end_time'])) {
        $recurEndTime = $params['recur_end_time'];
        if (strlen($recurEndTime) == 5) {
          $recurEndTime .= ':00';
        }
      }
      $recurEndDateTime = $recurEndDate.' '.$recurEndTime;
      $recurEndStamp = checkit::convertISODateTimeToUnix($recurEndDateTime);
    }

    $event = array(
      'title' => $params['title'],
      'location' => $params['location'],
      'description' => $params['description'],

      'start_stamp' => $startStamp,
      'end_stamp' => $endStamp,
      'end_year' => (int)date('Y', $endStamp),
      'end_month' => (int)date('n', $endStamp),

      'recurrence' => $params['recurrence'],
      'recur_yearly_interval' => $params['recur_yearly_interval'],
      'recur_yearly_months' => isset($params['recur_yearly_months'])
        ? $params['recur_yearly_months'] : array(),
      'recur_monthly_interval' => $params['recur_monthly_interval'],
      'recur_monthly_count' => $params['recur_monthly_count'],
      'recur_monthly_byweekday' => $params['recur_monthly_byweekday'],
      'recur_weekly_interval' => $params['recur_weekly_interval'],
      'recur_weekly_weekdays' => isset($params['recur_weekly_weekdays'])
        ? $params['recur_weekly_weekdays'] : array(),
      'recur_daily_interval' => $params['recur_daily_interval'],
    );

    if (isset($params['event_id'])) {
      $event['event_id'] = $params['event_id'];
    }

    if (isset($recurEndStamp)) {
      $event['recur_end'] = $recurEndStamp;
    }

    return $event;
  }

}

?>