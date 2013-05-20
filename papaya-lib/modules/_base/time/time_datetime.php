<?php
/**
* Time calculation - time specified
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
* @version $Id: time_datetime.php 32361 2009-10-09 09:59:37Z weinert $
*/

/**
* Basic class time calculation
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_crontime.php');
/**
* Time calculation - time specified
*
* @package Papaya-Modules
* @subpackage _Base-Time
*/
class time_datetime extends base_crontime {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'date' => array('Date', 'isISODate', FALSE, 'input', 30, 'ISO Date YYYY-MM-DD'),
    'time' => array('Time', 'isTime', FALSE, 'input', 30, 'ISO Time HH:MM')
  );

  /**
  * Get next date time
  *
  * @param integer $from
  * @access public
  * @return integer
  */
  function getNextDateTime($from) {
    if (isset($this->data['date']) && $this->data['time']) {
      $date = explode('-', $this->data['date']);
      $time = explode(':', $this->data['time']);
      $result = mktime(
        empty($time[0]) ? 0 : (int)$time[0],
        empty($time[1]) ? 0 : (int)$time[1],
        0,
        empty($date[1]) ? 0 : (int)$date[1],
        empty($date[2]) ? 0 : (int)$date[2],
        empty($date[0]) ? 0 : (int)$date[0]
      );
      return (($result > time()) ? $result : 0);
    }
    return 0;
  }

}
?>