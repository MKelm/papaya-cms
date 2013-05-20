<?php
/**
* Community Calendar Recurrency Expander
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
* This class is used to expand recurrencies. Initialize an object by specifying
* a relevant timeframe (from start to end). You can then expand the recurrencies
* of individual events. Each instance of a recurring event is called an occurrence.
* Only occurrences that take place within the relevant timeframe will be processed.
*
* See also RFC 2445 (iCalendar), Section 4.3.10 for more info about recurrences,
* although this class does not support all the capabilities of iCalendar
* Recurrence Rules (RRULEs)
*
*
* @package Papaya-Modules
* @subpackage Beta-Calendar
* @version $Id: community_calendar_recurrency_expander.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
 * class includes
 */
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_object.php');


/**
 * Community Calendar Recurrency Expander
 *
* @package Papaya-Modules
* @subpackage Beta-Calendar
 */
class community_calendar_recurrency_expander extends base_object {

  /** @var integer timestamp of the relevant timeframe's start */
  var $start = 0;
  /** @var integer timestamp of the relevant timeframes's end */
  var $end = 0;

  /**
  * Constructor
  *
  * @param integer $start UNIX-Timestamp of the relevant timeframe's start
  * @param integer $end UNIX-Timestamp of the relevant timeframe's end
  */
  function __construct($start, $end) {
    $this->start = $start;
    $this->end = $end;
  }

  /**
  * expand Recurrencies
  * evaluates an event's recurrency information and returns an array containing
  * all occurrences of that event between $start and $end
  *
  * @param array $event event-array
  * @return array array with all occurrences array(array(start, end, event), ..)
  */
  function expandRecurrencies($event) {
    switch($event['recurrence']) {
    case 'yearly':
      return $this->_expandYearlyByMonth($event);
    case 'monthly':
      return $this->_expandMonthlyByDay($event);
    case 'weekly':
      return $this->_expandWeeklyByWeekday($event);
    case 'daily':
      return $this->_expandDaily($event);
    default:
      return $this->_expandNothing($event);
    }
  }

  /**
  * expand yearly by month
  * used for events that occur every n-th year in one or more known months
  *
  * @access private
  * @param array $event event-array
  * @return array all recurrences
  */
  function _expandYearlyByMonth($event) {
    $eventStart = $this->_convertTimestampToArray($event['start_stamp']);

    // also include events that might start before start of relevant timeframe
    // but end in or after relevant timeframe
    $start = $this->start - ($event['end_stamp'] - $event['start_stamp']);

    // how many years can be skipped between the event's start and the start of
    // the relevant timeframe without affecting occurrence-calculations?
    $offset = floor(
      (date('Y', $start) - $eventStart['year']) / $event['recur_yearly_interval']
    ) * $event['recur_yearly_interval'];

    // if offset is below 0, occurrences before the actual event's start would be
    // unnecessarily calculated
    $offset = max(0, $offset);

    // year in which the calculation starts
    $startYear = $eventStart['year'] + $offset;
    $endYear = date('Y', $this->end);

    sort($event['recur_yearly_months']);

    $result = array();
    // loop over the years, each time increasing by specified interval
    for ($year = $startYear; $year <= $endYear; $year += $event['recur_yearly_interval']) {
      // loop over the specified months
      foreach ($event['recur_yearly_months'] as $month) {
        $stamp = mktime(
          $eventStart['hour'],
          $eventStart['minute'],
          $eventStart['second'],
          $month,
          $eventStart['day'],
          $year
        );

        $occurrence = $this->_createOccurrence($event, $stamp);
        if ($this->_isRelevant($occurrence)) {
          $result[] = $occurrence;
        }
      }
    }

    return $result;
  }


  /**
  * expand monthly by day
  * used for events that occur every x-th month on the y-th day or weekday
  *
  * @access private
  * @param array $event event-array
  * @return array all recurrences
  */
  function _expandMonthlyByDay($event) {
    $eventStart = $this->_convertTimestampToArray($event['start_stamp']);

    // also include events that might start before start of relevant timeframe
    // but end in or after relevant timeframe
    $start = $this->start - ($event['end_stamp'] - $event['start_stamp']);

    // how many months can be skipped between the event's start and the start of
    // the relevant timeframe without affecting occurrence-calculations?
    $passedYears = date('Y', $start) - $eventStart['year'];
    $passedMonths = date('n', $start) - $eventStart['month'];
    $offset = floor(
      (($passedYears * 12) + $passedMonths) / $event['recur_monthly_interval']
    ) * $event['recur_monthly_interval'];

    // if offset is below 0, occurrences before the actual event's start would be
    // unnecessarily calculated
    $offset = max(0, $offset);

    $startstamp = mktime(
      0,
      0,
      0,
      $eventStart['month'] + $offset,
      $eventStart['day'],
      $eventStart['year']
    );

    $result = array();
    // loop over the months increasing by specified interval
    for ($current = $startstamp;
         $current <= $this->end;
         $current = mktime(
           0,
           0,
           0,
           date('n', $current) + $event['recur_monthly_interval'],
           1,
           date('Y', $current)
         )
    ) {

      if ($event['recur_monthly_count'] > 0) {
        // count from the beginning of this month
        $month = date('n', $current);
        // already starting on the FIRST of the month, so this must be decreased by 1
        $count = $event['recur_monthly_count'] - 1;
      } else {
        // count back from the beginning of the next month
        $month = date('n', $current) + 1;
        $count = $event['recur_monthly_count'];
      }

      // remove time-information before using 'strtotime'
      $stamp = mktime(0, 0, 0, $month, 1, date('Y', $current));

      $addDays = 0;
      if ((bool)$event['recur_monthly_byweekday']) {
        // go to selected weekday
        $stamp = strtotime(date('l', $event['start_stamp']), $stamp);
        // add (or subtract) selected number of WEEKS
        $addDays = $count * 7;
      } else {
        // add (or subtract) selected number of DAYS
        $addDays = $count;
      }

      // calculate new timestamp
      $stamp = mktime(
        $eventStart['hour'],
        $eventStart['minute'],
        $eventStart['second'],
        date('n', $stamp),
        date('j', $stamp) + $addDays,
        date('Y', $stamp)
      );

      // prevent 'overflowing' a month
      if (date('n', $stamp) !== date('n', $current)) {
        /*
          RFC 2445 says:
            If BYxxx rule part values are found which are beyond the available
            scope (ie, BYMONTHDAY=30 in February), they are simply ignored.

          here, BYxxx is $event['recur_monthly_count'] which can referr to a day
          (as in BYMONTHDAY) or to a week (as in FREQ=MONTHLY in conjunction with
          BYDAY and BYSETPOS)
        */
        continue;
      }

      $occurrence = $this->_createOccurrence($event, $stamp);
      if ($this->_isRelevant($occurrence)) {
        $result[] = $occurrence;
      }
    }

    return $result;
  }

  /**
  * expand weekly by weekday
  * used for events that occur every n weeks on one or more selected weekdays
  *
  * @access private
  * @param array $event event-array
  * @return array all recurrences
  */
  function _expandWeeklyByWeekday($event) {
    // weekday names as they are accepted by 'strtotime'
    static $weekdayNames = array(
      0 => 'Sunday',
      1 => 'Monday',
      2 => 'Tuesday',
      3 => 'Wednesday',
      4 => 'Thursday',
      5 => 'Friday',
      6 => 'Saturday'
    );

    if (!isset($event['recur_weekly_weekstart'])) {
      /*
        RFC 2445 says:
          The WKST rule part specifies the day on which the workweek starts.
          Valid values are MO, TU, WE, TH, FR, SA and SU. This is significant
          when a WEEKLY RRULE has an interval greater than 1, and a BYDAY rule
          part is specified. [...] The default value is MO.

        here, WKST is  $event['recur_weekly_weekstart'] and valid values are
        0 = SU to 6 = SA. BYDAY is $event['recur_weekly_weekdays']
      */
      $event['recur_weekly_weekstart'] = 1;
    }

    // sort the weekdays according to the event's weekstart and save their names
    $weekdays = array();
    for ($i = 0, $n = $event['recur_weekly_weekstart'];
         $i < 7;
         ++$i, $n = ($n + 1) % 7
    ) {
      if (!in_array($n, $event['recur_weekly_weekdays'])) {
        // weekday not specified in event
        continue;
      }
      $weekdays[] = $weekdayNames[$n];
    }

    $eventStart = $this->_convertTimestampToArray($event['start_stamp']);
    $eventStartDate = $this->_dateOnly($event['start_stamp']);

    // also include events that might start before start of relevant timeframe
    // but end in or after relevant timeframe
    $start = $this->start - ($event['end_stamp'] - $event['start_stamp']);

    // how many weeks can be skipped between the event's start and the start of
    // the relevant timeframe without affecting occurrence-calculations?
    $offset = floor(
      ($start - $eventStartDate) / (60 * 60 * 24 * 7 * $event['recur_weekly_interval'])
    ) * $event['recur_weekly_interval'];
    // if offset is below 0, occurrences before the actual event's start would be
    // unnecessarily calculated
    $offset = max(0, $offset);

    // start on the sunday before the event or the timeframe
    $startstamp = mktime(
      0,
      0,
      0,
      $eventStart['month'],
      $eventStart['day'] + ($offset * 7),
      $eventStart['year']
    );
    if (date('w', $startstamp) != 0) {
      $startstamp = strtotime('last Sunday', $startstamp);
    }

    $result = array();

    // loop over the weeks increasing by specified interval
    for ($weekstart = $startstamp;
         $weekstart <= $this->end;
         $weekstart = mktime(
           0,
           0,
           0,
           date('n', $weekstart),
           date('j', $weekstart) + ($event['recur_weekly_interval'] * 7),
           date('Y', $weekstart)
         )) {

      $current = $weekstart;
      // process specified weekdays within current week
      foreach ($weekdays as $weekday) {

        // timestamp for current weekday
        $current = strtotime($weekday, $current);

        $occurrenceStart = mktime(
          $eventStart['hour'],
          $eventStart['minute'],
          $eventStart['second'],
          date('n', $current),
          date('j', $current),
          date('Y', $current)
        );

        $occurrence = $this->_createOccurrence($event, $occurrenceStart);
        if ($this->_isRelevant($occurrence)) {
          $result[] = $occurrence;
        }
      } // foreach
    } // for
    return $result;
  }

  /**
  * expand daily recurrence
  * used for events that occur every x days
  *
  * @access private
  * @param array $event event-array
  * @return array all recurrences
  */
  function _expandDaily($event) {
    $eventStart = $this->_convertTimestampToArray($event['start_stamp']);
    $eventStartDate = $this->_dateOnly($event['start_stamp']);

    // also include events that might start before start of relevant timeframe
    // but end in or after relevant timeframe
    $start = $this->start - ($event['end_stamp'] - $event['start_stamp']);
    // how many days can be skipped between the event's start and the start of
    // the relevant timeframe without affecting occurrence-calculations?
    $offset = floor(
      round(
        ($start - $eventStartDate) / (60 * 60 * 24)
      ) / $event['recur_daily_interval']
    ) * $event['recur_daily_interval'];

    // if offset is below 0, occurrences before the actual event's start would be
    // unnecessarily calculated
    $offset = max(0, $offset);

    $startstamp = mktime(
      0,
      0,
      0,
      $eventStart['month'],
      $eventStart['day'] + $offset,
      $eventStart['year']
    );

    // loop over days
    $result = array();
    for ($current = $startstamp;
         $current <= $this->end;
         $current = mktime(
           0,
           0,
           0,
           date('n', $current),
           date('j', $current) + $event['recur_daily_interval'],
           date('Y', $current)
         )
    ) {
      $stamp = mktime(
        $eventStart['hour'],
        $eventStart['minute'],
        $eventStart['second'],
        date('n', $current),
        date('j', $current),
        date('Y', $current)
      );

      $occurrence = $this->_createOccurrence($event, $stamp);
      if ($this->_isRelevant($occurrence)) {
        $result[] = $occurrence;
      }
    }

    return $result;
  }

  /**
  * expand Nothing
  * used for events that have no recurrence
  *
  * @access private
  * @param array $event event-array
  * @return array all recurrences
  */
  function _expandNothing($event) {
    $occurrence = $this->_createOccurrence($event, $event['start_stamp']);
    if ($this->_isRelevant($occurrence)) {
      return array($occurrence);
    }
    return array();
  }

  /**
  * is relevant
  * find out whether an occurrence is relevant for the current timeframe
  *
  * @access private
  * @param array $occurrence occurrence to test
  * @return boolean TRUE if occurrence is relevant
  */
  function _isRelevant($occurrence) {
    if (isset($occurrence['event']['recur_end'])
        && $occurrence['start'] > $occurrence['event']['recur_end']
    ) {
      // occurrence starts after defined end of recurrences for this event
      return FALSE;
    }
    if ($occurrence['start'] < $occurrence['event']['start_stamp']) {
      // occurrence starts before the event starts, so disregard
      return FALSE;
    }
    return
      // occurrence starts in timeframe
      (
       $occurrence['start'] >= $this->start &&
       $occurrence['start'] <= $this->end
      ) || // or occurrence ends in timeframe
      (
       $occurrence['end'] >= $this->start &&
       $occurrence['end'] <= $this->end
      ) || // or occurrends starts before and ends after timeframe
      (
       $occurrence['start'] < $this->start &&
       $occurrence['end'] > $this->end
      );
  }

  /**
  * create occurrence array
  *
  * @access private
  * @param array $event event-array
  * @param integer $occurrenceStartStamp UNIX-Timestamp of occurrence's start
  * @return array recurrence-array
  */
  function _createOccurrence($event, $occurrenceStartStamp) {
    return array(
      'start' => $occurrenceStartStamp,
      'end' => $occurrenceStartStamp + ($event['end_stamp'] - $event['start_stamp']),
      'event' => $event,
    );
  }

  /**
  * date Only
  * strip time Information from a timestamp leaving only the date at 00:00:00
  *
  * @access private
  * @param integer $timestamp UNIX-Timestamp
  * @return integer UNIX-Timestamp of the same date at 00:00:00
  */
  function _dateOnly($timestamp) {
    return mktime(
      0,
      0,
      0,
      date('n', $timestamp),
      date('j', $timestamp),
      date('Y', $timestamp)
    );
  }

  /**
  * convert timestamp to array
  *
  * @access private
  * @param integer $timestamp UNIX-Timestamp
  * @return array array('hour', 'minute', 'second', 'month', 'day', 'year')
  */
  function _convertTimestampToArray($timestamp) {
    return array(
      'hour' => (int)date('G', $timestamp),
      'minute' => (int)date('i', $timestamp),
      'second' => (int)date('s', $timestamp),
      'month' => (int)date('n', $timestamp),
      'day' => (int)date('j', $timestamp),
      'year' => (int)date('Y', $timestamp)
    );
  }

}

?>