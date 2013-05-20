<?php
/**
* Time calculation - UNIX crontab style
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
* @subpackage _Base-Time
* @version $Id: time_crontab.php 38063 2013-01-31 16:33:41Z kersken $
*/

/**
* Basic class time calculation
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_crontime.php');

/**
* Time calculation - UNIX crontab style
*
* @package Papaya-Modules
* @subpackage _Base-Time
*/
class time_crontab extends base_crontime {
  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'minute' => array('Minute', '{^(\*(/\d+)?|\d+(\-\d+(/\d+)?)?|\d+(,\d+)*)$}', FALSE, 'input', 30,
      '0 through 59, *[/interval], m-n[/interval], m,n,...', '*'
    ),
    'hour' => array('Hour', '{^(\*(/\d+)?|\d+(\-\d+(/\d+)?)?|\d+(,\d+)*)$}', FALSE, 'input', 30,
      '0 through 23, *[/interval], m-n[/interval], m,n,...', '*'
    ),
    'date' => array(
      'Day of month', '{^(\*(/\d+)?|\d+(\-\d+(/\d+)?)?|\d+(,\d+)*)$}', FALSE, 'input', 30,
      '1 through 31, *[/interval], m-n[/interval], m,n,...', '*'
    ),
    'month' => array('Month', '{^(\*(/\d+)?|\d+(\-\d+(/\d+)?)?|\d+(,\d+)*)$}', FALSE, 'input', 30,
      '1 through 12, *[/interval], m-n[/interval], m,n,...', '*'
    ),
    'weekday' => array(
      'Day of week', '{^(\*(/\d+)?|\d+(\-\d+(/\d+)?)?|\d+(,\d+)*)$}', FALSE, 'input', 30,
      '0 through 7, *[/interval], m-n[/interval], m,n,...; Sunday is 0 or 7', '*'
    )
  );

  /**
  * Internal helper method to parse a crontab time string
  *
  * @param string $cronStr
  * @param int $min
  * @param int $max
  * @access private
  * @return mixed array|FALSE
  */
  function _parse($cronStr, $min, $max) {
    $cronStr = trim($cronStr);
    // Is the value formally correct?
    if (!(preg_match('{^(\*(/\d+)?|\d+(\-\d+(/\d+)?)?|\d+(,\d+)*)$}', $cronStr))) {
      return FALSE;
    }
    $result = array();
    // Try to split on commas
    $times = explode(',', $cronStr);
    if (count($times) > 1) {
      foreach ($times as $time) {
        // Sort out illegal values
        if ($time < $min || $time > $max) {
          return FALSE;
        }
      }
      // If we're still here, we've got our result ready
      $result = $times;
    } else {
      // Try to split on a slash
      $timeAndInterval = explode('/', $cronStr);
      $time = $timeAndInterval[0];
      if (count($timeAndInterval) > 1) {
        $interval = $timeAndInterval[1];
      } else {
        $interval = 1;
      }
      // If time is an asterisk, use the full $min-$max range
      if ($time == '*') {
        $start = $min;
        $end = $max;
      } else {
        // Try to split time on a dash
        $startAndEnd = explode('-', $time);
        $start = $startAndEnd[0];
        if (count($startAndEnd) > 1) {
          $end = $startAndEnd[1];
        } else {
          $end = $start;
        }
        // Sort out illegal values
        if ($start > $end || $start < $min || $end > $max) {
          return FALSE;
        }
      }
      // Build the set of times
      for ($i = $start; $i <= $end; $i += $interval) {
        $result[] = "$i";
      }
    }
    return $result;
  }

  /**
  * Internal helper function to get a valid crontime
  *
  * May be called recursively to use the next higher-order time component
  *
  * @param string $type
  * @access private
  * @return int
  */
  function _getTime($type) {
    switch ($type) {
    case 'nextyear':
      // Compute the date by both date and weekday, if necessary, and see which one occurs earlier
      if ($this->dates !== FALSE) {
        $result = mktime(
          $this->hours[0],
          $this->minutes[0],
          0,
          $this->months[0],
          $this->dates[0],
          $this->currentYear + 1
        );
      }
      if ($this->weekdays !== FALSE) {
        $timeByWeekday = mktime(
          $this->hours[0],
          $this->minutes[0],
          0,
          $this->months[0],
          $this->weekdays[0] + 1,
          $this->currentYear + 1
        );
        if ((!isset($result)) || $timeByWeekday < $result) {
          $result = $timeByWeekday;
        }
      }
      break;
    case 'nextavailmonth':
      if ($this->currentMonth == 12) {
        return $this->_getTime('nextyear');
      }
      $month = NULL;
      foreach ($this->months as $mon) {
        if ($mon > $this->currentMonth) {
          $month = $mon;
          break;
        }
      }
      if ($month === NULL) {
        return $this->_getTime('nextyear');
      }
      if ($this->dates !== FALSE) {
        $result = mktime(
          $this->hours[0],
          $this->minutes[0],
          0,
          $month,
          $this->dates[0],
          $this->currentYear
        );
      }
      if ($this->weekdays !== FALSE) {
        $timeByWeekday = mktime(
          $this->hours[0],
          $this->minutes[0],
          0,
          $month,
          1 + $this->weekdays[0],
          $this->currentYear
        );
        if ((!isset($result)) || $timeByWeekday < $result) {
          $result = $timeByWeekday;
        }
      }
      break;
    case 'nextavailday':
      $lastDays = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
      if (($this->currentYear % 4 == 0 && $this->currentYear % 100 != 0) ||
          $this->currentYear % 400 == 0) {
        $lastDays[1] = 29;
      }
      if ($this->currentDate == $lastDays[$this->currentMonth - 1] && $this->currentWeekday == 6) {
        return $this->_getTime('nextavailmonth');
      }
      $date = NULL;
      $weekday = NULL;
      if ($this->dates !== FALSE) {
        foreach ($this->dates as $dat) {
          if ($dat > $this->currentDate) {
            $date = $dat;
            break;
          }
        }
        if ($date !== NULL) {
          $result = mktime(
            $this->hours[0],
            $this->minutes[0],
            0,
            $this->currentMonth,
            $date,
            $this->currentYear
          );
        }
      }
      if ($this->weekdays !== FALSE) {
        foreach ($this->weekdays as $wday) {
          if ($wday > $this->currentWeekday) {
            $weekday = $wday;
            break;
          }
        }
        if ($weekday !== NULL) {
          $timeByWeekday = mktime(
            $this->hours[0],
            $this->minutes[0],
            0,
            $this->currentMonth,
            $this->currentDate + $weekday - $this->currentWeekday,
            $this->currentYear
          );
        } else {
          $timeByWeekday = mktime(
            $this->hours[0],
            $this->minutes[0],
            0,
            $this->currentMonth,
            $this->currentDate + $this->weekdays[0] + 7 - $this->currentWeekday,
            $this->currentYear
          );
        }
        if ((!isset($result) || $timeByWeekday < $result)) {
          $result = $timeByWeekday;
        }
      }
      if (!isset($result) && $date === NULL && $weekday === NULL) {
        return $this->_getTime('nextavailmonth');
      }
      break;
    case 'nextavailhour':
      if ($this->currentHour == 23) {
        return $this->_getTime('nextavailday');
      }
      $hour = NULL;
      foreach ($this->hours as $hr) {
        if ($hr > $this->currentHour) {
          $hour = $hr;
          break;
        }
      }
      if ($hour === NULL) {
        return $this->_getTime('nextavailday');
      }
      $result = mktime(
        $hour,
        $this->minutes[0],
        0,
        $this->currentMonth,
        $this->currentDate,
        $this->currentYear
      );
      break;
    case 'nextavailminute':
      if ($this->currentMinute == 59) {
        return $this->_getTime('nextavailhour');
      }
      $minute = NULL;
      foreach ($this->minutes as $min) {
        if ($min > $this->currentMinute) {
          $minute = $min;
          break;
        }
      }
      if ($minute === NULL) {
        return $this->_getTime('nextavailhour');
      }
      $result = mktime(
        $this->currentHour,
        $minute,
        0,
        $this->currentMonth,
        $this->currentDate,
        $this->currentYear
      );
      break;
    default:
      $result = 0;
      break;
    }
    return $result;
  }

  /**
  * Get next date time
  *
  * @param integer $from
  * @access public
  * @return integer
  */
  function getNextDateTime($from) {
    if (!isset($this->data['minute']) || trim($this->data['minute']) == '') {
      $this->data['minute'] = '';
      $this->minutes = FALSE;
    } else {
      $this->minutes = $this->_parse($this->data['minute'], 0, 59);
    }
    if (!isset($this->data['hour']) || trim($this->data['hour']) == '') {
      $this->data['hour'] = '';
      $this->hours = FALSE;
    } else {
      $this->hours = $this->_parse($this->data['hour'], 0, 23);
    }
    if (!isset($this->data['date']) || trim($this->data['date']) == '') {
      $this->data['date'] = '';
      $this->dates = FALSE;
    } else {
      $this->dates = $this->_parse($this->data['date'], 1, 31);
    }
    if (!isset($this->data['month']) || trim($this->data['month']) == '') {
      $this->data['month'] = '';
      $this->months = FALSE;
    } else {
      $this->months = $this->_parse($this->data['month'], 1, 12);
    }
    if (!isset($this->data['weekday']) || trim($this->data['weekday']) == '') {
      $this->data['weekday'] = '';
      $this->weekdays = FALSE;
    } else {
      $this->weekdays = $this->_parse($this->data['weekday'], 0, 7);
    }
    // Adjust the Sunday == 7 rule according to 'man 5 crontab'
    if (is_array($this->weekdays) && in_array(7, $this->weekdays)) {
      array_pop($this->weekdays);
      if (!in_array(0, $this->weekdays)) {
        array_unshift($this->weekdays, 0);
      }
    } 
    if ($this->data['date'] === '*' &&
        $this->data['weekday'] !== '*' &&
        $this->weekdays !== FALSE) {
      $this->dates = FALSE;
    } elseif ($this->data['weekday'] === '*' &&
              $this->data['date'] !== '*' &&
              $this->dates !== FALSE) {
      $this->weekdays = FALSE;
    }
    // If any of these values is explicitly FALSE, return 0
    if ($this->minutes === FALSE || $this->hours === FALSE ||
        ($this->dates === FALSE && $this->weekdays === FALSE) || $this->months === FALSE) {
      return 0;
    }
    // Get the components of the current date/time
    $this->currentMinute = date('i', $from);
    if ($this->currentMinute[0] == '0') {
      $this->currentMinute = substr($this->currentMinute, 1);
    }
    $this->currentHour = date('G', $from);
    $this->currentDate = date('j', $from);
    $this->currentMonth = date('n', $from);
    $this->currentYear = date('Y', $from);
    $this->currentWeekday = date('w', $from);
    // Calculate the next execution time
    if (!in_array($this->currentMonth, $this->months)) {
      return $this->_getTime('nextavailmonth');
    }
    if (is_array($this->dates) && is_array($this->weekdays)) {
      if (!in_array($this->currentDate, $this->dates) &&
          !in_array($this->currentWeekday, $this->weekdays)) {
        return $this->_getTime('nextavailday');
      }
    }
    if (is_array($this->dates)) {
      if (!in_array($this->currentDate, $this->dates)) {
        return $this->_getTime('nextavailday');
      }
    } elseif (!in_array($this->currentWeekday, $this->weekdays)) {
      return $this->_getTime('nextavailday');
    }
    if (!in_array($this->currentHour, $this->hours)) {
      return $this->_getTime('nextavailhour');
    }
    return $this->_getTime('nextavailminute');
  }
}
?>
