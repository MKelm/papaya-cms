<?php
/**
* Community Calendar View
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
* @version $Id: content_community_calendar.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
 * class includes
 */
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_object.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
require_once(dirname(__FILE__).'/base_community_calendar.php');
require_once(dirname(__FILE__).'/output_community_calendar_event.php');

/**
* Page module
*
* @package Papaya-Modules
* @subpackage Beta-Calendar
*/
class content_community_calendar extends base_content {

  /** @var array Edit fields */
  var $editFields = array();

  /** @var string name of GET-parameter */
  var $paramName = 'ccal';

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $surfer = &base_surfer::getInstance();
    switch (TRUE) {
    case isset($this->params['cmd']) && $this->params['cmd'] == 'add':

      $event = new output_community_calendar_event($this);
      return $event->getAddDialog();
    case isset($this->params['cmd']) && $this->params['cmd'] == 'save':

      $event = new output_community_calendar_event(
        $this,
        @$this->params['event_id']
      );
      return $event->save();
    case isset($this->params['event']) && isset($this->params['cmd']) &&
         $this->params['cmd'] == 'edit':

      $event = new output_community_calendar_event(
        $this,
        (int)$this->params['event']
      );
      return $event->getEditDialog();
    case isset($this->params['event']) && isset($this->params['cmd']) &&
         $this->params['cmd'] == 'delete':

      $event = new output_community_calendar_event(
        $this,
        (int)$this->params['event']
      );
      return $event->delete();
    case isset($this->params['event']) && isset($this->params['cmd']) &&
         $this->params['cmd'] == 'recommend' && isset($this->params['for']):

      $event = new output_community_calendar_event(
        $this,
        (int)$this->params['event']
      );
      return $event->saveRecommendation();
    case isset($this->params['event']):

      $event = new output_community_calendar_event(
        $this,
        (int)$this->params['event']
      );
      return $event->getDetails();
    default:
      if (isset($this->params['year'])) {
        $year = (int)$this->params['year'];
      } else {
        $year = (int)date('Y');
      }
      if (isset($this->params['month'])) {
        $month = (int)$this->params['month'];
      } else {
        $month = (int)date('n');
      }

      include_once(dirname(__FILE__).'/output_community_calendar_month.php');
      $monthview = new output_community_calendar_month($this, $year, $month);
      return $monthview->getXML();
    }
    return '';
  }
}

?>