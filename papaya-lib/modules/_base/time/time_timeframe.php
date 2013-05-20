<?php
/**
* Time calculation - Repeat after given timeframe
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
* @version $Id: time_timeframe.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Basic class time claculation
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_crontime.php');
/**
* Time calculation - Repeat after given timeframe
*
* @package Papaya-Modules
* @subpackage _Base-Time
*/
class time_timeframe extends base_crontime {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'days' => array('Days', 'isNum', TRUE, 'input', 5, '', 0),
    'hours' => array('Hours', 'isNum', TRUE, 'input', 5, '', 0),
    'minutes' => array('Minutes', 'isNum', TRUE, 'input', 5, '', 0)
  );

  /**
  * Get next date time
  *
  * @param integer $from
  * @access public
  * @return integer
  */
  function getNextDateTime($from) {
    $secMinutes = empty($this->data['minutes']) ? 0 : $this->data['minutes'] * 60;
    $secHours = empty($this->data['hours']) ? 0 : $this->data['hours'] * 3600;
    $secDays = empty($this->data['days']) ? 0 : $this->data['days'] * 86400;
    $frame = $secMinutes + $secHours + $secDays;
    return (($frame > 0) ? ($from + $frame) : 0);
  }

}
?>