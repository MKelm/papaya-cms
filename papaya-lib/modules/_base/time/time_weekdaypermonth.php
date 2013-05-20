<?php
/**
* Time calculation - particular week day monthly
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
* @subpackage _Base-Time
* @version $Id: time_weekdaypermonth.php 32804 2009-11-02 11:59:37Z weinert $
*/

/**
* Basic class time calculation
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_crontime.php');

/**
* Time calculation - particular week day monthly
*
* @package Papaya-Modules
* @subpackage _Base-Time
*/
class time_weekdaypermonth extends base_crontime {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array();

  /**
  * Seconds of day
  * @var integer $secOfDay
  */
  var $secOfDay = 86400;

  /**
  * Initialize options dialog
  */
  function initializeDialog($hiddenValues = NULL) {
    if (!(isset($this->dialog) && is_object($this->dialog))) {
      $this->editFields = array(
        'weekday' => array(
          'Weekday',
          'isNum',
          TRUE,
          'combo',
          array(
            1 => $this->_gt('Monday'),
            2 => $this->_gt('Thuesday'),
            3 => $this->_gt('Wednesday'),
            4 => $this->_gt('Thursday'),
            5 => $this->_gt('Friday'),
            6 => $this->_gt('Saturday'),
            7 => $this->_gt('Sunday'),
          ),
          '',
          1
        ),
        'repeatmode' => array(
          'Repeat',
          'isNum',
          TRUE,
          'combo',
          array(
            0 => $this->_gt('every 1. [weekday]'),
            1 => $this->_gt('every 2. [weekday]'),
            2 => $this->_gt('every 3. [weekday]'),
            3 => $this->_gt('every 4. [weekday]'),
            4 => $this->_gt('every last [weekday]'),
            5 => $this->_gt('every week'),
            6 => $this->_gt('every 2 week'),
            7 => $this->_gt('every 3 week'),
            8 => $this->_gt('every 4 week'),
          ),
          '',
          0
        ),
        'repeatmonth'=>array(
          'Month',
          'isNum',
          TRUE,
          'combo',
          array(
            0 => $this->_gt('every month'),
            1 => $this->_gt('every 2. month'),
            2 => $this->_gt('every 3. month'),
            3 => $this->_gt('every 6. month'),
          ),
          '',
          0
        )
      );
      parent::initializeDialog($hiddenValues);
    }
  }

  /**
  * Get next date time
  *
  * @param integer $from
  * @access public
  * @return mixed integer or boolean FALSE
  */
  function getNextDateTime($from) {
    $repeatMode = empty($this->data['repeatmode']) ? 0 : $this->data['repeatmode'];
    $weekDay = empty($this->data['weekday']) ? 0 : (int)$this->data['weekday'];
    if ($repeatMode >= 0 && $repeatMode <= 4) {
      $base = $this->parseDateToArray($from);
      $days = $this->getWeekDaysOfMonth($base, $weekDay);
      if ($repeatMode < 4) {
        if ($from < ($result = $days[$repeatMode])) {
          return $result;
        } else {
          $base = $this->parseDateToArray(
            mktime(
              0, 0, 0, $base['month'] + $this->getPlusMonths(), 1, $base['year']
            )
          );
          $days = $this->getWeekDaysOfMonth($base, $weekDay);
          return $days[$repeatMode];
        }
      } else {
        if ($from < ($result = end($days))) {
          return $result;
        } else {
          $base = $this->parseDateToArray(
            mktime(
              0, 0, 0, $base['month'] + $this->getPlusMonths(), 1, $base['year']
            )
          );
          $days = $this->getWeekDaysOfMonth($base, $weekDay);
          return end($days);
        }
      }
    } elseif ($repeatMode >= 5 && $repeatMode <= 8) {
      $day = $from;
      if ($weekDay < 1) {
        $weekDay = 1;
      }
      if ($weekDay > 7) {
        $weekDay = 7;
      }
      $max = $repeatMode - 4;
      $found = 0;
      while ($found < $max) {
        $day = $this->dayOnly($day + $this->secOfDay + 3601);
        if ($this->getWDay($day) == $weekDay) {
          ++$found;
        }
      }
      return $day;
    }
    return FALSE;
  }

  /**
  * Get plus months
  *
  * @access public
  * @return integer
  */
  function getPlusMonths() {
    if (!isset($this->data['repeatmonth'])) {
      $this->data['repeatmonth'] = 0;
    }
    switch ((int)$this->data['repeatmonth']) {
    case 3:
      return 6;
    case 2:
      return 3;
    case 1:
      return 2;
    default:
      return 1;
    }
  }

  /**
  * Get week days of month
  *
  * @param array $base
  * @param integer $weekDay
  * @access public
  * @return integer
  */
  function getWeekDaysOfMonth($base, $weekDay) {
    list($firstDay, $lastDay) = $this->getBorderDays($base);
    $weekDays = array();
    for ($day = $firstDay; $this->dayOnly($day) <= $lastDay; $day += $this->secOfDay) {
      if ($this->getWDay($day) == (int)$weekDay) {
        $weekDays[] = $this->dayOnly($day);
      }
    }
    return $weekDays;
  }

  /**
  * Get border days
  *
  * @param array $date
  * @access public
  * @return array
  */
  function getBorderDays($date) {
    $firstDay = mktime(0, 0, 0, $date['month'], 1, $date['year']);
    $lastDay = mktime(0, 0, 0, $date['month'], date('t', $date['date']), $date['year']);
    return array($firstDay, $lastDay);
  }

  /**
  * Transform date to array
  *
  * @param integer $date
  * @access public
  * @return array
  */
  function parseDateToArray($date) {
    $result['date'] = $date;
    $result['day'] = date('j', $date);
    $result['month'] = date('n', $date);
    $result['year'] = date('Y', $date);
    $result['wday'] = $this->getWDay($date);
    return $result;
  }

  /**
  * Get week day
  *
  * @param integer $date
  * @access public
  * @return integer
  */
  function getWDay($date) {
    $result = date('w', $date);
    $result = ((int)$result == 0) ? 7 : (int)$result;
    return $result;
  }


  /**
  * Get day only
  *
  * @param string $date
  * @access public
  * @return integer
  */
  function dayOnly($date) {
    $dayArr = explode('-', date('Y-m-d', $date));
    if (isset($dayArr) && is_array($dayArr)) {
      return mktime(
        0,
        0,
        0,
        empty($dayArr[1]) ? 0 : (int)$dayArr[1],
        empty($dayArr[1]) ? 0 : (int)$dayArr[2],
        empty($dayArr[1]) ? 0 : (int)$dayArr[0]
      );
    }
    return 0;
  }
}
?>