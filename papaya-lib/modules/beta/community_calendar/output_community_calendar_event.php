<?php
/**
* Community Calendar Event
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
* @version $Id: output_community_calendar_event.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
 * class includes
 */
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
require_once(dirname(__FILE__).'/base_community_calendar.php');

/**
* Community Calendar Event
*
* @package Papaya-Modules
* @subpackage Beta-Calendar
*/
class output_community_calendar_event extends base_db {

  /** @var string name of GET-parameter */
  var $paramName = 'ccal';

  /** @var integer id of current event or NULL if used to add an event */
  var $eventId = NULL;

  /** @var base_content content object */
  var $contentObj = NULL;

  /** @var base_surfer instance of surfer */
  var $surferObj = NULL;

  /** @var base_community_calendar calendar object */
  var $calendarObj = NULL;

  /** @var array details for this event, access only via _getEventDetails() */
  var $_eventDetails = NULL;

  /** @var array localized errors */
  var $errorNames = array();

  /**
  * Constructor
  *
  * @param object $contentObj content-object
  * @param integer $eventId event-id or NULL (when adding an event)
  */
  function __construct(&$contentObj, $eventId = NULL) {
    $this->contentObj = &$contentObj;
    $this->eventId = (int)$eventId;
    $this->surferObj = &base_surfer::getInstance();
    $this->calendarObj = new base_community_calendar();
    $this->paramName = $this->contentObj->paramName;

    // localize errors
    $this->errorNames = array(
      'title' =>  $this->_gt('Title'),
      'start' =>  $this->_gt('Event Start'),
      'end' =>  $this->_gt('Event End'),
      'recurrence' =>  $this->_gt('Recurrence'),
      'recur_daily' =>  $this->_gt('Daily Recurrence'),
      'recur_weekly' => $this->_gt('Weekly Recurrence'),
      'recur_monthly' => $this->_gt('Monthly Recurrence'),
      'recur_yearly' => $this->_gt('Yearly Recurrence'),
      'recur_end' => $this->_gt('Recurrence End'),
      'location' => $this->_gt('Location'),
      'description' => $this->_gt('Description'),
    );
  }

  /**
  * save a Recommendation
  *
  * @return string XML-Data ready for output
  */
  function saveRecommendation() {
    if (!$this->_isRecommendable()) {
      // event is not recommendable
      return $this->getDetails($this->_gt('You cannot recommend this event!'));
    }

    $calInfo = $this->calendarObj->loadCalendar((int)$this->contentObj->params['for']);
    if (!$calInfo || !is_null($calInfo['surfer_id'])) {
      // invalid calendar-id specified or calendar is not a public calendar
      return $this->getDetails($this->_gt('You cannot recommend this event!'));
    }

    $recommendation = array(
      'event_id' => $this->eventId,
      'surfer_id' => $this->surferObj->surferId,
      'calendar_id' => $calInfo['calendar_id'],
      'recommendation_time' => time(),
    );

    if (!$this->calendarObj->addRecommendation($recommendation)) {
      return $this->getDetails($this->_gt('Database error'));
    }

    return $this->getDetails($this->_gt('Recommendation saved.'));
  }

  /**
  * saves an added / updated event
  *
  * @return string XML data with a message and a dialog if input was incorrect
  */
  function save() {

    // Add or Update?
    if (!isset($this->contentObj->params['event_id'])) {
      $dialogTitle = $this->_gt('Add Event');
    } else {
      $dialogTitle = $this->_gt('Edit Event');
    }

    // check for valid surfer
    if (!$this->surferObj->isValid) {
      return $this->_getEventDialogXML(
        $dialogTitle,
        $this->_gt('Please login first!')
      );
    }

    // if an existing event is about to be updated, check wether the surfer is
    // allowed to write to it
    if (isset($this->contentObj->params['event_id']) && !$this->_isWritable()) {
      return $this->_getEventDialogXML(
        $dialogTitle,
        $this->_gt('You are not allowed to edit this event!')
      );
    }

    $inputDialog = &$this->_getInputDialog();

    // validate input
    if (!$this->calendarObj->validateEventInputDialog($inputDialog)) {
      $fields = array();
      // extract user-friendly error-messages
      foreach ($inputDialog->inputErrors as $field => $val) {
        if ($val == 0) {
          continue;
        }
        switch ($field) {
        case 'title':
          $fields[] = $this->errorNames['title'];
          break;
        case 'start_date':
        case 'start_time':
          $fields[] = $this->errorNames['start'];
          break;
        case 'end_date':
        case 'end_time':
          $fields[] = $this->errorNames['end'];
          break;
        case 'recurrence':
          $fields[] = $this->errorNames['recurrence'];
          break;
        case 'recur_daily_interval':
          $fields[] = $this->errorNames['recur_daily'];
          break;
        case 'recur_weekly_interval':
        case 'recur_weekly_weekdays':
          $fields[] = $this->errorNames['recur_weekly'];
          break;
        case 'recur_monthly_interval':
        case 'recur_monthly_count':
        case 'recur_monthly_byweekday':
          $fields[] = $this->errorNames['recur_monthly'];
          break;
        case 'recur_yearly_interval':
        case 'recur_yearly_months':
          $fields[] = $this->errorNames['recur_yearly'];
          break;
        case 'recur_end_date':
        case 'recur_end_time':
          $fields[] = $this->errorNames['recur_end'];
          break;
        default:
          $fields[] = $field;
          break;
        }
        $fields = array_unique($fields);
      }
      return $this->_getEventDialogXML(
        $dialogTitle,
        $this->_gt('Please check the following fields: ') . implode(', ', $fields),
        $inputDialog->getDialogXML()
      );
    }

    $event = $this->calendarObj->getEventFromParams($this->contentObj->params);

    if (isset($this->contentObj->params['event_id'])) {
      // updateEvent
      if ($this->calendarObj->updateEvent($event)) {
        return $this->_getEventDialogXML(
          $dialogTitle,
          $this->_gt('Event updated.')
        );
      } else {
        return $this->_getEventDialogXML(
          $dialogTitle,
          $this->_gt('Database error')
        );
      }
    } else {
      // get surfer's Calendar id
      $cal_id = $this->calendarObj->getSurferCalendarId($this->surferObj->surferId);
      if (!$cal_id) {
        // surfer does not have a calendar yet, so create one
        $cal_id = $this->calendarObj->addCalendar(
          array('surfer_id' => $this->surferObj->surferId));
        if (!$cal_id) {
          return $this->_getEventDialogXML(
            $dialogTitle,
            $this->_gt('Database error')
          );
        }
      }
      $event['calendar_id'] = $cal_id;
      // addEvent in database
      if ($this->calendarObj->addEvent($event)) {
        return $this->_getEventDialogXML(
          $dialogTitle,
          $this->_gt('Event saved.')
        );
      } else {
        return $this->_getEventDialogXML(
          $dialogTitle,
          $this->_gt('Database error')
        );
      }
    }

    return $this->_getEventDialogXML(
      $dialogTitle,
      NULL,
      $inputDialog->getDialogXML()
    );
  }

  /**
  * checks whether this element is writable
  *
  * @return boolean TRUE if this element is writable
  */
  function _isWritable() {
    if (!$this->surferObj->isValid) {
      return FALSE;
    }
    $event = $this->_getEventDetails();

    if (!$event) {
      return FALSE;
    }

    return $event['surfer_id'] === $this->surferObj->surferId;
  }

  /**
  * check whether this event is recommendable
  *
  * @return boolean TRUE if this event is recommendable
  */
  function _isRecommendable() {
    // surfer must be logged in
    if (!$this->surferObj->isValid) {
      return FALSE;
    }

    // event must not already be in a public calendar
    $event = $this->_getEventDetails();
    $calInfo = $this->calendarObj->loadCalendar((int)$event['calendar_id']);

    if (is_null($calInfo['surfer_id'])) {
      return FALSE;
    }

    // surfer must not have recommended this event already
    $recommendations = $this->calendarObj->loadRecommendations(
      $this->eventId,
      $this->surferObj->surferId
    );

    if (count($recommendations) > 0) {
      return FALSE;
    }

    return TRUE;
  }

  /**
  * get a dialog for event-editing
  *
  * @return string XML-Data ready for output
  */
  function getEditDialog() {

    $event = $this->_getEventDetails();

    if (!$this->_isWritable()) {
      return $this->_getEventDialogXML(
        $this->_gt('Edit Event'),
        $this->_gt('You are not allowed to edit this event!')
      );
    }

    $event['start_date'] = date('Y-m-d', $event['start_stamp']);
    $event['start_time'] = date('H:i:s', $event['start_stamp']);
    $event['end_date'] = date('Y-m-d', $event['end_stamp']);
    $event['end_time'] = date('H:i:s', $event['end_stamp']);

    $dialog = $this->_getInputDialog($event);

    return $this->_getEventDialogXML(
      $this->_gt('Edit Event'),
      NULL,
      $dialog->getDialogXML()
    );
  }

  /**
  * deletes an event
  *
  * @return string XML-Data ready for output
  */
  function delete() {

    $message = '';

    if (!$this->_isWritable()) {
      // Surfer may not write to this event (and therefore not delete it)
      return sprintf(
        '<delete-event><message>%s</message></delete-event>',
        papaya_strings::escapeHTMLChars(
          $this->_gt('You are not allowed to remove this event!')
        )
      );
    }

    // prepare confirmation dialog
    $fields = array(
      $this->_gt('Do you really want to delete this Event?'),
    );
    $data = array();
    $hidden = array(
      'cmd' => 'delete',
      'event' => $this->eventId,
      'save' => 1,
    );
    $dialog = new base_dialog(
      $this->contentObj,
      $this->contentObj->paramName,
      $fields,
      $data,
      $hidden
    );
    $dialog->dialogTitle = 'Confirm Deletion';
    $dialog->buttonTitle = 'Yes';
    $dialog->loadParams();

    if (!$dialog->checkDialogInput()) {
      // Dialog has not yet been submited or it has been manipulated
      $details = $this->_getEventDetails();
      return sprintf(
        '<delete-event>
          <event title="%s" />
          %s
          <cancel action="%s" text="%s">
            <input type="hidden" name="%s[event]" value="%d" />
          </cancel>
        </delete-event>',
        papaya_strings::escapeHTMLChars($details['title']),
        $dialog->getDialogXML(),
        papaya_strings::escapeHTMLChars($this->contentObj->getBaseLink()),
        papaya_strings::escapeHTMLChars($this->_gt('No')),
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->eventId)
      );
    }

    // Surfer confirmed deletion of the event
    if ($this->calendarObj->deleteEvent($this->eventId)) {
      $message = $this->_gt('Event removed.');
    } else {
      $message = $this->_gt('Database error');
    }

    return sprintf(
      '<delete-event><message>%s</message></delete-event>',
      papaya_strings::escapeHTMLChars($message)
    );
  }

  /**
  * get Event Details
  *
  * @param string $message optional message to include in output
  * @return string XML-Data ready for output
  */
  function getDetails($message = NULL) {

    $event = $this->_getEventDetails();

    if (!$event) {
      return sprintf(
        '<event><message>%s</message></event>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Invalid Event'))
      );
    }

    if (!is_null($event['surfer_id']) &&
        $event['surfer_id'] != $this->surferObj->surferId
    ) {
      return sprintf(
        '<event><message>%s</message></event>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->_gt('You are not allowed to view this Event!')
        )
      );
    }

    $result = sprintf('<event id="%d">'.LF, $event['event_id']);

    if (!is_null($message)) {
      $result .= sprintf('<message>%s</message>', $message);
    }

    $result .= sprintf(
      '<data>'.LF.
      '<title>%s</title>'.LF.
      '<recurrence>%s</recurrence>'.LF.
      '<location>%s</location>'.LF.
      '<description><![CDATA[%s]]></description>'.LF.
      '<start>%s</start>'.LF.
      '<end>%s</end>'.LF,
      papaya_strings::escapeHTMLChars($event['title']),
      papaya_strings::escapeHTMLChars($event['recur_human_readable']),
      papaya_strings::escapeHTMLChars($event['location']),
      $this->getXHTMLString($event['description'], TRUE),
      date('Y-m-d H:i:s', $event['start_stamp']),
      date('Y-m-d H:i:s', $event['end_stamp'])
    );

    if ($this->_isWritable()) {
      // edit & delete links
      $result .= sprintf(
        '<editlink href="%s">%s</editlink>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'edit', 'event' => $event['event_id']))
        ),
        $this->_gt('Edit this Event')
      );

      $result .= sprintf(
        '<deletelink href="%s">%s</deletelink>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('cmd' => 'delete', 'event' => $event['event_id']))
        ),
        papaya_strings::escapeHTMLChars($this->_gt('Delete this Event'))
      );
    }

    $result .= '</data>'.LF;
    if ($this->_isRecommendable()) {
      // recommendation form
      $publicCalendars = $this->calendarObj->loadPublicCalendars();

      if (count($publicCalendars) > 0) {

        $cals = array();
        foreach ($publicCalendars as $calendar) {
          $cals[$calendar['calendar_id']] = $calendar['title'];
        }

        $this->contentObj->initializeParams();
        $this->contentObj->baseLink = $this->contentObj->getBaseLink();

        $fields = array(
          'for' => array('Recommend for Calendar', 'isNumeric', TRUE, 'combo', $cals),
        );
        $hidden = array(
          'event' => $this->eventId,
          'cmd' => 'recommend',
        );

        $recommendDialog = new base_dialog(
          $this->contentObj, $this->contentObj->paramName, $fields, $data, $hidden
        );
        $recommendDialog->msgs = &$this->contentObj->msgs;
        $recommendDialog->loadParams();
        $recommendDialog->baseLink = $this->contentObj->baseLink;
        $recommendDialog->buttonTitle = 'Go';
        $result .= $recommendDialog->getDialogXML();

      }

    }
    $result .= '</event>'.LF;

    return $result;
  }

  /**
  * get Add Dialog
  *
  * @access private
  * @return string xml-data for event input form
  */
  function getAddDialog() {

    // check for valid surfer
    if (!$this->surferObj->isValid) {
      return $this->_getEventDialogXML(
        $this->_gt('Add Event'),
        $this->_gt('Please login first!')
      );
    }

    $inputDialog = $this->_getInputDialog();

    $xml = $this->_getEventDialogXML(
      $this->_gt('Add Event'),
      NULL,
      $inputDialog->getDialogXML()
    );

    return $xml;
  }

  /**
  * get input dialog for an event
  *
  * @access private
  * @param array $data data for input dialog
  * @return base_dialog inputDialog for an event
  */
  function _getInputDialog($data = array()) {

    if (count($data) == 0) {
      $data = $this->calendarObj->defaultEventData;
    }

    $this->contentObj->initializeParams();

    $this->contentObj->baseLink = $this->contentObj->getBaseLink();

    // prepare form
    $fields = &$this->calendarObj->editFields;

    $hidden = array('cmd' => 'save');

    if (isset($data['event_id'])) {
      $hidden['event_id'] = $data['event_id'];
    }

    $inputDialog = new base_dialog(
      $this->contentObj, $this->contentObj->paramName, $fields, $data, $hidden
    );
    $inputDialog->msgs = &$this->contentObj->msgs;
    $inputDialog->loadParams();
    $inputDialog->baseLink = $this->contentObj->baseLink;

    return $inputDialog;
  }

  /**
  * get event dialog xml
  *
  * @access private
  * @param string $title dialog title
  * @param string $message message to include in body
  * @param string $body dialog body
  * @return string XML-Data ready for output
  */
  function _getEventDialogXML($title, $message = NULL, $body = NULL) {
    $result = sprintf(
      '<eventdialog title="%s">'.LF,
      papaya_strings::escapeHTMLChars($title)
    );
    if ($message !== NULL) {
      $result .= sprintf(
        '<message>%s</message>'.LF,
        papaya_strings::escapeHTMLChars($message)
      );
    }
    if ($body !== NULL) {
      $result .= $body.LF;
    }
    $result .= '</eventdialog>'.LF;

    return $result;
  }

  /**
  * checks an array if it contains only elements from a whitelist
  *
  * @access private
  * @param array $array the array to be checked
  * @param array $whitelist allowed elements
  * @return boolen TRUE if the array contains only elements on the whitelist
  */
  function _checkArray($array, $whitelist) {
    if (!is_array($array) || !is_array($whitelist)) {
      return FALSE;
    }
    foreach ($array as $current) {
      if (!in_array($current, $whitelist)) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
  * get Surfer
  * returns an instance of the surfer
  *
  * @access private
  * @return base_surfer reference to the surfer
  */
  function &_getSurfer() {
    if (is_null($this->_surferObj)) {
      $this->_surferObj = &base_surfer::getInstance();
    }
    return $this->_surferObj;
  }

  /**
  * returns details for this event (ensures that they are loaded from the db only once)
  *
  * @access private
  * @return array event-details
  */
  function _getEventDetails() {
    if (!is_null($this->_eventDetails)) {
      return $this->_eventDetails;
    }
    $this->_eventDetails = $this->calendarObj->loadEvent($this->eventId);
    return $this->_eventDetails;
  }

}

?>