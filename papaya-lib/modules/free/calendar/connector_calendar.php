<?php
/**
* Connector Date calendar
*
* Sypnosis:
* <code>
*   include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
*   $calendarObject =
*     base_pluginloader::getPluginInstance('726dd37a95d03017d08781fd1052359a', $this);
* </code>
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
* @version $Id: connector_calendar.php 33119 2009-11-20 13:51:44Z weinert $
*/

/**
* Basic class plugin
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_plugin.php');

/**
* Date calendar basic functionality
*
* @package Papaya-Modules
* @subpackage Free-Calendar
*/
class connector_calendar extends base_plugin {
  /**
   * calendar base object
   *
   * @var base_calendar
   */
  var $calendarObj = NULL;

  /**
  * Initialize calandar object property if it does not already exists
  * @return void
  */
  function initializeCalendarObject() {
    // Check if the object already exists
    if (!(
          isset($this->calendarObj) &&
          is_object($this->calendarObj) &&
          is_a($this->calendarObj, 'base_calendar')
         )
        ) {
      // If object not exist, create it!
      include_once(dirname(__FILE__).'/base_calendar.php');
      $this->calendarObj = new base_calendar($this);
      $this->calendarObj->parentObj = &$this->parentObj->parentObj;
      $this->calendarObj->initialize();
    }
  }

  /**
   * Returns the title of the event belonging to given id
   *
   * @param int $eventId
   * @param int $languageId
   * @return string
   */
  function getTitleByEventId($eventId, $languageId) {
    $this->initializeCalendarObject();
    $details = $this->calendarObj->loadDate($eventId, NULL, $languageId);
    return $details['date_title'];
  }

  /**
   * Returns the start of the event belonging to given id
   *
   * @param int $eventId
   * @return string
   */
  function getDateByEventId($eventId) {
    $this->initializeCalendarObject();
    $details = $this->calendarObj->loadDate($eventId);
    return $details['date_startf'];
  }

  /**
   * Get the events of the next selected months
   *
   * @param int $nbrMonths
   * @param unknown $tag
   * @param unknown $lngId
   * @param string $url optional, link to calendar page
   * @return string
   */
  function getNextEvents($nbrMonths, $tag, $lngId, $url='') {
    $this->initializeCalendarObject();
    if ($nbrMonths < 1) {
      $nbrMonths = 1;
    }
    if ($nbrMonths > 12) {
      $nbrMonths = 12;
    }
    $today = getdate();
    $month = $today['mon'];
    $year = intval($today['year']);
    $start = time();
    $month += $nbrMonths;
    if ($month > 12) {
      $month -= 12;
      $year++;
    }
    $end = mktime(0, 0, 0, $month, 31, $year);
    //$this->debug('start:'.$start, 'end:'.$end, 'month:'.$month, 'year:'.$year);
    $this->calendarObj->loadDatesDetails($start, $end, $tag, $lngId);
    return $this->calendarObj->showDates(NULL, TRUE, $url);
  }
}
?>