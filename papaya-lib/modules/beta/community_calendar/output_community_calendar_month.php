<?php
/**
* Community Calendar Monthview
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
* @version $Id: output_community_calendar_month.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
 * class includes
 */
require_once(PAPAYA_INCLUDE_PATH.'system/papaya_strings.php');
require_once(dirname(__FILE__).'/base_community_calendar.php');

/**
* Community Calendar Monthview
*
* @package Papaya-Modules
* @subpackage Beta-Calendar
*/
class output_community_calendar_month extends base_object {

  /** @var base_content owning content object (needed to e.g. access parameters) */
  var $contentObj = NULL;

  /** @var string name of GET-Parameter */
  var $paramName = 'ccal';

  /** @var integer year */
  var $year;
  /** @var integer month */
  var $month;

  /** @var integer timestamp of relevant timeframe's start */
  var $startStamp = 0;
  /** @var integer timestamp of relevant timeframe's end */
  var $endStamp = 0;

  /** @var array all days on current calendar-sheet */
  var $days = array();

  /** @var integer number of slots per day */
  var $numSlots = 5;
  /** @var integer index for start of week weekday (0 = Sunday, ..., 6 = Saturday) */
  var $startOfWeekIndex = 1;
  /** @var integer index for end of week weekday (0 = Sunday, ..., 6 = Saturday) */
  var $endOfWeekIndex = 0;
  /** @var string name for start of week weekday (as accepted by strtotime) */
  var $startOfWeekName = 'Monday';
  /** @var string name for end of week weekday (as accepted by strtotime) */
  var $endOfWeekName = 'Sunday';

  /**
  * Constructor
  *
  * @param integer $year current month's year
  * @param integer $month current month's month
  */
  function __construct(&$contentObj, $year, $month) {
    $this->contentObj = &$contentObj;
    $this->year = $year;
    $this->month = $month;

    $this->paramName = $this->contentObj->paramName;

    // first day in this month
    $monthStartStamp = mktime(0, 0, 0, $month, 1, $year);
    $this->startStamp = $monthStartStamp;
    // first day on this calendar sheet (the week containing the first day of
    // selected month will be completely on the sheet)
    if (date('w', $this->startStamp) !== $this->startOfWeekIndex) {
      $this->startStamp = strtotime(
        sprintf('last %s', $this->startOfWeekName),
        $this->startStamp
      );
    }

    // last day in this month at 23:59:59
    $this->endStamp = strtotime('+1 month', $monthStartStamp) - 1;
    // last day on this calendar sheet (the week containing the last day of the
    // selected month will be completely on the sheet)
    if (date('w', $this->endStamp) !== $this->endOfWeekIndex) {
      // get end of week Timestamp
      $this->endStamp = strtotime($this->endOfWeekName, $this->endStamp);
      // move to 23:59:59
      $this->endStamp = strtotime('+1 day', $this->endStamp) - 1;
    }
  }

  /**
  * get XML ready to be used by the template
  *
  * @return string xml-data with output
  */
  function getXML() {

    $calendar = new base_community_calendar();
    $occurrences = $calendar->loadEvents($this->startStamp, $this->endStamp);
    if ($occurrences === FALSE) {
      return '';
    }
    $this->_allocateSlots($occurrences);

    $result = '<month>'.LF;

    // timestamp of selected month's start
    $thisMonth = mktime(0, 0, 0, $this->month, 1, $this->year);

    // link to next month
    $nextMonth = strtotime('+1 month', $thisMonth);
    $result .= sprintf(
      '<link-next href="%s">%s</link-next>',
      papaya_strings::escapeHTMLChars(
        $this->getLink(
          array(
            'year' => date('Y', $nextMonth),
            'month' => date('n', $nextMonth)
          )
        )
      ),
      date('Y-m-d', $nextMonth)
    );

    // link to previous month
    $prevMonth = strtotime('-1 month', $thisMonth);
    $result .= sprintf(
      '<link-prev href="%s">%s</link-prev>',
      papaya_strings::escapeHTMLChars(
        $this->getLink(
          array(
            'year' => date('Y', $prevMonth),
            'month' => date('n', $prevMonth)
          )
        )
      ),
      date('Y-m-d', $prevMonth)
    );

    // date of today
    $result .= sprintf('<today>%s</today>', date('Y-m-d'));
    // displayed month
    $result .= sprintf(
      '<displayed-month>%s-%02s</displayed-month>',
      papaya_strings::escapeHTMLChars($this->year),
      papaya_strings::escapeHTMLChars($this->month)
    );

    $surfer = &base_surfer::getInstance();
    if ($surfer->isValid) {
      $result .= sprintf(
        '<link-add>%s</link-add>',
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'add'))
        )
      );
    }

    $result .= '<weeks>'.LF;

    $day = 1;

    // loop over weeks
    for ( $weekStamp = $this->startStamp;
          $weekStamp <= $this->endStamp;
          $weekStamp = strtotime('+1 week', $weekStamp)
    ) {
      $result .= sprintf('<week no="%s">'.LF, date('W', $weekStamp));

      // loop over slots
      for ($slot = 0; $slot < $this->numSlots; ++$slot) {
        $result .= '<slot>'.LF;

        $endOfWeek = strtotime('+1 week', $weekStamp);
        // loop over days
        for ( $dayStamp = $weekStamp;
              $dayStamp < $endOfWeek;
              $dayStamp = strtotime('+1 day', $dayStamp)
        ) {
          $result .= sprintf('<day date="%s">', date('Y-m-d', $dayStamp));
          $dayHash = $this->_hashDate($dayStamp);
          if (isset($this->days[$dayHash][$slot])) {
            // this day has something in this slot
            if ($this->days[$dayHash][$slot] == 'spacer') {
              // current slot holds an event but on another day
              $result .= '<spacer />';
            } elseif (is_array($this->days[$dayHash][$slot])) {
              $event = $this->days[$dayHash][$slot];
              $result .= sprintf(
                '<event start="%s" end="%s" title="%s" localduration="%s" href="%s" color="%s" />',
                date('Y-m-d', $event['start']),
                date('Y-m-d', $event['end']),
                papaya_strings::escapeHTMLChars($event['event']['title']),
                (int)$event['localDuration'],
                papaya_strings::escapeHTMLChars(
                  $this->getLink(array('event' => $event['event']['event_id']))
                ),
                papaya_strings::escapeHTMLChars($event['event']['calendar_color'])
              );
            } else {
              // current slot is empty for current day
              $result .= '<empty />';
            }
          } else {
            $result .= '<empty />';
          }
          $result .= '</day>'.LF;
        }
        $result .= '</slot>'.LF;
      }
      $result .= '</week>'.LF;
    }
    $result .= '</weeks>'.LF;
    $result .= '</month>'.LF;
    return $result;
  }

  /**
  * allocates Slots
  * each day has a certain amount of slots. Events are sorted into these slots.
  * this makes it possible to display overlapping events properly. Result is
  * saved in {$this->days}
  *
  * @access private
  * @param array $occurrences all occurrences of relevant events
  * @return void
  */
  function _allocateSlots($occurrences) {

    // loop over all occurrences
    foreach ($occurrences as $occurrence) {

      $slot = $this->_findSlot($this->_hashDate($occurrence['start']));

      // loop over all days affected by current occurrence
      for ($day = $occurrence['start'];
           $day <= $occurrence['end'];
           $day = strtotime('+1 day', $day)) {

        $dateHash = $this->_hashDate($day);
        $this->_initializeDay($dateHash);

        if ($dateHash == $this->_hashDate($occurrence['start'])) {
          // current day = start of occurrence
          $endOfWeek = strtotime($this->endOfWeekName, $day);
          $start = mktime(
            0,
            0,
            0,
            (int)date('n', $occurrence['start']),
            (int)date('j', $occurrence['start']),
            (int)date('Y', $occurrence['start'])
          );
          // localDuration is from current day to end of week or end of occurrence
          // (whichever comes first)
          $occurrence['localDuration'] =
            ((min($occurrence['end'], $endOfWeek) - $start) / (60 * 60 * 24)) + 1;
          // save occurrence in slot
          $this->days[$dateHash][$slot] = $occurrence;
        } elseif (date('w', $day) == 1) {
          // current day = start of week
          $endOfWeek = strtotime('Sunday', $day);
          // localDuration is from current day to end of week or end of occurrence
          // (whichever comes first)
          $occurrence['localDuration'] =
            ((min($occurrence['end'], $endOfWeek) - $day) / (60 * 60 * 24)) + 1;
          $this->days[$dateHash][$slot] = $occurrence;
        } else {
          // current day is affected by occurrence, so it must not be used by
          // other occurrences
          $this->days[$dateHash][$slot] = 'spacer';
        }

      }

    }
  }

  /**
  * finds a free slot for a given day
  *
  * @access private
  * @param string $date hash of the day's date
  * @return mixed number of first free slot or FALSE
  *               if there are no free slots on that day
  */
  function _findSlot($date) {
    $this->_initializeDay($date);
    foreach ($this->days[$date] as $nr => $contents) {
      if (!is_array($contents) && $contents !== 'spacer') {
        return $nr;
      }
    }
    return FALSE;
  }

  /**
  * initialize Day
  *
  * @access private
  * @param string $date hash of the day's date
  * @return void
  */
  function _initializeDay($date) {
    if (!isset($this->days[$date]) || !is_array($this->days[$date])) {
      $this->days[$date] = array_fill(0, $this->numSlots, 'empty');
    }
  }

  /**
  * calculates a 'hash-value' of a date. This hash is be used by other functions
  *
  * @access private
  * @param integer $timestmap timestamp of the date which will be hashed
  * @return string hashed date
  */
  function _hashDate($timestamp) {
    return date('Y-m-d', $timestamp);
  }

}

?>