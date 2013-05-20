<?php
/**
* Time calculation - particular week day every n days
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
* @version $Id: time_weekdayperdays.php 32804 2009-11-02 11:59:37Z weinert $
*/

/**
* Basic class time clculation
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_crontime.php');
/**
* Time calculation - particular week day every n days
*
* @package Papaya-Modules
* @subpackage _Base-Time
*/
class time_weekdayperdays extends base_crontime {

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
        'weekday' => array('Weekday', 'isNum', TRUE, 'combo',
          array(
            1 => $this->_gt('Monday'),
            2 => $this->_gt('Thuesday'),
            3 => $this->_gt('Wednesday'),
            4 => $this->_gt('Thursday'),
            5 => $this->_gt('Friday'),
            6 => $this->_gt('Saturday'),
            7 => $this->_gt('Sunday'),
          ), '', 1),
        'repeatdays' => array('Repeat (days)', 'isNum', TRUE, 'input', 3, '', 0)
      );
      parent::initializeDialog($hiddenValues);
    }
  }

  /**
  * Get next date time
  *
  * @param integer $from
  * @access public
  * @return integer
  */
  function getNextDateTime($from) {
    if (isset($this->data['repeatdays']) && (int)$this->data['repeatdays'] >= 2) {
      $step = 4;
      $day = $from;
      if (empty($this->data['weekday']) || (int)$this->data['weekday'] < 1) {
        $this->data['weekday'] = 1;
      }
      do {
        $dateArray = getdate($day);
        $day = mktime(
          0,
          0,
          0,
          $dateArray["mon"],
          $dateArray["mday"] + (int)$this->data['repeatdays'],
          $dateArray["year"]
        );
        while ($this->getWDay($day) != (int)$this->data['weekday']) {
          $day = $this->dayOnly($day - $this->secOfDay);
        }
      } while ($this->dayOnly($day) <= $this->dayOnly($from));
      return $day;
    }
    return FALSE;
  }

  /**
  * Get week day
  *
  * @param $date
  * @access public
  * @return integer
  */
  function getWDay($date) {
    $result = date('w', $date);
    $result = ((int)$result == 0) ? 7 : (int)$result;
    return $result;
  }

  /**
  * Day only
  *
  * @param string $date
  * @access public
  * @return integer
  */
  function dayOnly($date) {
    $dayArr = explode('-', date('Y-m-d', $date));
    if (isset($dayArr) && is_array($dayArr)) {
      return mktime(0, 0, 0, $dayArr[1], $dayArr[2], $dayArr[0]);
    }
    return 0;
  }
}
?>