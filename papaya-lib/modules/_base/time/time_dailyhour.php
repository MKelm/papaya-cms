<?php
/**
* Time calculation - daily, fixed hour
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
* @version $Id: time_dailyhour.php 32361 2009-10-09 09:59:37Z weinert $
*/

/**
* Basic class time calculation
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_crontime.php');
/**
* Time calculation - specified hour each day
*
* @package Papaya-Modules
* @subpackage _Base-Time
*/
class time_dailyhour extends base_crontime {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'hour' => array('Hour', '(^(2[0-3]|1?\d)$)', FALSE, 'input', 30, 'Hour 0-23', 0)
  );

  /**
  * Get next date time
  *
  * @param integer $from
  * @access public
  * @return integer
  */
  function getNextDateTime($from) {
    $hour = empty($this->data['hour']) ? 0 : (int)$this->data['hour'];
    $currentHour = date('G', $from);
    if ($currentHour < $hour) {
      $result = mktime($hour, 0, 0);
    } else {
      $result = mktime(
        $hour,
        0,
        0,
        date('n', $from),
        date('j', $from) + 1,
        date('Y', $from)
      );
    }
    return $result;
  }

}
?>