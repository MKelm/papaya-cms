<?php
/**
* Community Calendar Admin Class
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
* @version $Id: admin_community_calendar.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
 * class includes
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');
require_once(dirname(__FILE__).'/base_community_calendar.php');

/**
* Community Calendar Admin Class
*
* @package Papaya-Modules
* @subpackage Beta-Calendar
*/
class admin_community_calendar extends base_object {

  /** @var base_module admin-module that uses this object */
  var $module = NULL;
  /**
  * @var array event details - once they have been loaded, they will be cached here.
  * Always use _getEventDetails() to access
  */
  var $eventDetails = array();
  /** @var base_calendar instance of base_calendar */
  var $calendarObj = NULL;

  /**
  * Constructor
  *
  * @param base_module $module reference to admin-module
  */
  function __construct(&$module) {
    $this->module = &$module;
    $this->images = &$module->images;
    $this->paramName = &$module->paramName;
    $this->layout = &$module->layout;
    $this->calendarObj = new base_community_calendar();

    $this->initializeUI();
  }

  /**
  * show welcome screen (before user has selected anything)
  *
  * @return void
  */
  function showDefaultScreen() {
    $this->layout->addLeft($this->_getPublicCalendarsList());
    $this->layout->addLeft($this->_getRecommendedEventsList());
  }

  /**
  * show Dialogs to add a calendar
  *
  * @return void
  */
  function addCalendar() {
    $dialog = $this->_getCalendarDialog();
    $this->layout->add($dialog->getDialogXML());
    $this->layout->addLeft($this->_getPublicCalendarsList());
    $this->layout->addLeft($this->_getRecommendedEventsList());
  }

  /**
  * show Dialogs to edit a calendar
  *
  * @param int $calendarId id of calendar to edit
  * @return void
  */
  function editCalendar($calendarId) {
    $calendar = new base_community_calendar();
    $calInfo = $this->calendarObj->loadCalendar($calendarId);
    if (!$calInfo) {
      $this->_showError('Invalid Calendar!');
      return;
    }

    $editDialog = $this->_getCalendarDialog($calInfo);
    $this->layout->add($editDialog->getDialogXML());

    $this->layout->addLeft($this->_getPublicCalendarsList($calendarId));
    $this->layout->addLeft($this->_getRecommendedEventsList());
    $this->layout->addRight($this->_getEventsList($calendarId, NULL, $calInfo['title']));
  }

  /**
  * validate and save an updated / new calendar
  *
  * @param string $title Calendar's Title
  * @param string $color Calendar's Color
  * @param integer $calendarId Calendar's ID (when updating an existing)
  * @return void
  */
  function saveCalendar($title, $color, $calendarId = NULL) {

    $calInfo = array(
      'title' => $title,
      'color' => substr($color, 0, 1) == '#' ? substr($color, 1) : $color,
    );

    $update = FALSE;
    if (!is_null($calendarId)) {
      $calInfo['calendar_id'] = $calendarId;
      $update = TRUE;
    }

    $dialog = $this->_getCalendarDialog($calInfo);

    if ($dialog->checkDialogInput()) {

      if ($update) {
        // update an existing calendar
        $status = $this->calendarObj->updateCalendar($calInfo);
        $message = $this->_gt('Calendar updated!');
        $baseLink = $this->getLink(
          array(
            'cmd' => 'calendar',
            'calendar' => $calendarId
          )
        );
      } else {
        // save a new calendar
        $status = $this->calendarObj->addCalendar($calInfo);
        $message = $this->_gt('Calendar added!');
        $baseLink = $this->getBaseLink();
        if ($status != FALSE) {
          $calInfo['calendar_id'] = $status;
          $baseLink = $this->getLink(
            array(
              'cmd' => 'calendar',
              'calendar' => $status
            )
          );
          $dialog = $this->_getCalendarDialog($calInfo);
        }
      }

      // show dialog
      $type = 'info';
      if ($status == FALSE) {
        $type = 'error';
        $message = $this->_gt('Database Error!');
      }

      $hidden = array();

      $msgdialog = new base_msgdialog(
        $this->module, $this->paramName, $hidden, $message, $type
      );
      $msgdialog->buttonTitle = 'OK';
      $msgdialog->baseLink = $baseLink;

      $this->layout->add($msgdialog->getMsgDialog());
    }
    $this->layout->addLeft($this->_getPublicCalendarsList(@$calInfo['calendar_id']));
    $this->layout->addLeft($this->_getRecommendedEventsList());
    $this->layout->add($dialog->getDialogXML());
  }

  /**
  * delete a calendar: show a confirm dialog and delete calendar
  *
  * @param integer $calendarId Calendar to delete
  * @param boolean $save set to TRUE to really delete the calendar
  * @return void
  */
  function deleteCalendar($calendarId, $save = FALSE) {

    if (!$save) {
      // display confirm dialog first
      $calInfo = $this->calendarObj->loadCalendar($calendarId);
      if (!$calInfo) {
        $this->_showError('Invalid Calendar!');
        return;
      }
      $hidden = array(
        'cmd' => 'delete_calendar',
        'calendar' => $calendarId,
        'save' => 1,
      );
      $message = $this->_gtf(
        'Delete Calendar "%s" and all of its Events?', $calInfo['title']);
      $type = 'question';
      $dialog =
        new base_msgdialog($this->module, $this->paramName, $hidden, $message, $type);
      $dialog->baseLink = $this->getBaseLink();
      $dialog->buttonTitle = 'Delete';

      $this->layout->add($dialog->getMsgDialog());
      $this->editCalendar($calendarId);
      return;
    }

    // really delete calendar
    if ($this->calendarObj->deleteCalendar($calendarId)) {
      $type = 'info';
      $message = $this->_gt('Calendar Deleted!');
    } else {
      $type = 'error';
      $message = $this->_gt('Database Error!');
    }

    $hidden = array();

    $msgdialog =
      new base_msgdialog($this->module, $this->paramName, $hidden, $message, $type);
    $msgdialog->buttonTitle = 'OK';
    $msgdialog->baseLink = $this->getBaseLink();

    $this->module->layout->add($msgdialog->getMsgDialog());
    $this->showDefaultScreen();
  }

  /**
  * get calendar dialog (for editing and creating calendars)
  *
  * @param array $data data used to pre-fill dialog (optional)
  * @return base__dialog calendar dialog
  */
  function _getCalendarDialog($data = array('color' => '#FFFFFF')) {
    $data['color'] = @strtoupper($data['color']);
    if (substr($data['color'], 0, 1) != '#') {
      $data['color'] = '#'.$data['color'];
    }
    $fields = array(
      'title' => array('Title', 'isNoHTML', TRUE, 'input', 255),
      'color' => array('Color', '/^#[0-9a-f]{6}$/i', TRUE, 'color', 7,
        'Background color in #RRGGBB Format'),
    );
    $hidden = array('cmd' => 'save_calendar');

    if (isset($data['calendar_id'])) {
      $hidden['calendar_id'] = $data['calendar_id'];
      $dialogTitle = 'Edit Calendar';
    } else {
      $dialogTitle = 'Add Calendar';
    }

    $dialog = new base_dialog($this->module, $this->paramName, $fields, $data, $hidden);
    $dialog->dialogTitle = $dialogTitle;
    $dialog->loadParams();
    return $dialog;
  }

  /**
  * delete all recommendations for a given event after the user confirmed that
  *
  * @param integer $eventId Event's id
  * @param boolean $save really delete all recommendations
  * @return void
  */
  function clearRecommendations($eventId, $save = FALSE) {

    if (!$save) {
      // show confirm dialog
      $event = $this->calendarObj->loadEvent($eventId);
      $hidden = array(
        'cmd' => 'clear',
        'event' => $eventId,
        'save' => 1,
      );
      $message = $this->_gtf(
        'Clear all Recommendations for event "%s"?', $event['title']
      );
      $type = 'question';
      $dialog =
        new base_msgdialog($this->module, $this->paramName, $hidden, $message, $type);
      $dialog->baseLink = $this->getBaseLink();
      $dialog->buttonTitle = 'Yes';

      $this->layout->add($dialog->getMsgDialog());
      $this->showEvent($eventId);
      return;
    }

    // delete all recommendations
    if ($this->calendarObj->deleteRecommendations($eventId)) {
      $type = 'info';
      $message = $this->_gt('Recommendations cleared!');
    } else {
      $type = 'error';
      $message = $this->_gt('Database Error!');
    }

    $hidden = array();

    $msgdialog =
      new base_msgdialog($this->module, $this->paramName, $hidden, $message, $type);
    $msgdialog->buttonTitle = 'OK';
    $msgdialog->baseLink = $this->getLink(
      array(
        'cmd' => 'event',
        'event' => $eventId
      )
    );

    $this->module->layout->add($msgdialog->getMsgDialog());
    $this->showEvent($eventId);
  }

  /**
  * display the add-event dialog
  *
  * @return void
  */
  function addEvent() {
    $dialog = $this->_getEventDialog('Add Event');
    $this->layout->add($dialog->getDialogXML());
    $this->layout->addLeft($this->_getPublicCalendarsList());
    $this->layout->addLeft($this->_getRecommendedEventsList());
  }

  /**
  * display the dialog to edit an event
  *
  * @return void
  */
  function editEvent($eventId) {
    $event = $this->calendarObj->loadEvent($eventId);

    $event['start_date'] = date('Y-m-d', $event['start_stamp']);
    $event['start_time'] = date('H:i:s', $event['start_stamp']);
    $event['end_date'] = date('Y-m-d', $event['end_stamp']);
    $event['end_time'] = date('H:i:s', $event['end_stamp']);

    $dialog = $this->_getEventDialog('Edit Event', $event);
    $this->layout->add($dialog->getDialogXML());
    $this->layout->addLeft($this->_getPublicCalendarsList());
    $this->layout->addLeft($this->_getRecommendedEventsList());
  }

  /**
  * initialize Event-Dialog
  *
  * @param string $title Dialog title (e.g. 'Edit Event' or 'Add Event') (optional)
  * @param array $data data to pre-fill the form with (optional)
  * @return base_dialog initialized event-dialog
  */
  function &_getEventDialog($title = '', $data = NULL) {

    if (is_null($data)) {
      $data = $this->calendarObj->defaultEventData;
    }

    $hidden = array(
      'cmd' => 'save_event',
      'save' => 1,
    );

    if (isset($data['event_id'])) {
      $hidden['event_id'] = $data['event_id'];
    }

    $editFields = $this->calendarObj->editFields;

    // Fix single quotes for JS-Tooltips (frontend-editor needs them in another format)
    foreach ($editFields as $key => $val) {
      if (!is_array($val) || !isset($val[5])) {
        continue;
      }
      $val[5] = str_replace("'", '&#039;', $val[5]);
      $editFields[$key] = $val;
    }

    $cals = $this->_getPublicCalendarsForCombo();
    $editFields[] = 'Calendar';
    $editFields['calendar_id'] = array(
      'Calendar', 'isNum', TRUE, 'combo', $cals, 'Select a Calendar to save this Event into'
    );

    $dialog =
      new base_dialog($this->module, $this->paramName, $editFields, $data, $hidden);
    $dialog->dialogTitle = $title;
    $dialog->loadParams();

    return $dialog;
  }

  /**
  * save a new or an updated Event into the database and display a confirmation /
  * error message
  *
  * @param array $event event-data
  * @return void
  */
  function saveEvent($event) {
    $dialog = &$this->_getEventDialog();

    if (!$this->calendarObj->validateEventInputDialog($dialog)) {
      $this->layout->add($dialog->getDialogXML());
      $this->layout->addLeft($this->_getPublicCalendarsList());
      $this->layout->addLeft($this->_getRecommendedEventsList());
      return;
    }

    $event = $this->calendarObj->getEventFromParams($this->module->params);
    $event['calendar_id'] = (int)$this->module->params['calendar_id'];

    $hidden = array();

    if (isset($event['event_id'])) {

      // update existing event

      if ($this->calendarObj->updateEvent($event)) {
        // update successful
        $msgdialog = new base_msgdialog(
          $this, $this->paramName, $hidden, $this->_gt('Event updated.'), 'info'
        );
        $msgdialog->buttonTitle = 'OK';
        $msgdialog->baseLink = $this->getLink(
          array(
            'cmd' => 'event',
            'event' => $event['event_id'],
          )
        );
        $this->layout->add($msgdialog->getMsgDialog());
        $this->showEvent((int)$event['event_id']);
      } else {
        // update failed
        $msgdialog = new base_msgdialog(
          $this, $this->paramName, $hidden, $this->_gt('Database error.'), 'error'
        );
        $msgdialog->buttonTitle = 'OK';
        $msgdialog->baseLink = $this->getLink(
          array(
            'cmd' => 'edit_event',
            'event' => $event['event_id'],
          )
        );
        $this->layout->add($msgdialog->getMsgDialog());
        $this->editEvent((int)$event['event_id']);
      }

    } else {

      // add new event

      $status = $this->calendarObj->addEvent($event);

      if ($status == FALSE) {
        // save failed
        $msgdialog = new base_msgdialog(
          $this, $this->paramName, $hidden, $this->_gt('Database error.'), 'error'
        );
        $msgdialog->buttonTitle = 'OK';
        $msgdialog->baseLink = $this->getLink(
          array(
            'cmd' => 'add_event',
          )
        );
        $this->layout->add($msgdialog->getMsgDialog());
        $message = $this->_gt('Database error.');
        $type = 'error';
        $this->addEvent();
      } else {
        // save successful
        $msgdialog = new base_msgdialog(
          $this, $this->paramName, $hidden, $this->_gt('Event saved.'), 'info'
        );
        $msgdialog->buttonTitle = 'OK';
        $msgdialog->baseLink = $this->getLink(
          array(
            'cmd' => 'event',
            'event' => $status,
          )
        );
        $this->layout->add($msgdialog->getMsgDialog());
        $this->showEvent((int)$status);
      }

    }
  }

  /**
  * show event details
  *
  * @param integer $eventId Event's id
  * @param integer $calendarId Calendar to display on the side (the calendar the
  * event belongs to or the calendar the event was recommended for)
  * @return void
  */
  function showEvent($eventId, $calendarId = NULL) {

    $eventId = (int)$eventId;

    // load Event Details
    $event = $this->calendarObj->loadEvent($eventId);
    if (!$event) {
      $this->_showError('Invalid Event!');
      return;
    }

    // load Event Recommendations
    $event['recommendations'] = $this->calendarObj->loadRecommendations($eventId);

    // Event Details Display
    $details = sprintf(
      '<listview title="%s">
        <items>
          <listitem>
            <subitem>%s</subitem>
            <subitem>%s</subitem>
          </listitem>
          <listitem>
            <subitem>%s</subitem>
            <subitem>%s</subitem>
          </listitem>
          <listitem>
            <subitem>%s</subitem>
            <subitem>%s</subitem>
          </listitem>
          <listitem>
            <subitem>%s</subitem>
            <subitem>%s</subitem>
          </listitem>
          <listitem>
            <subitem>%s</subitem>
            <subitem>%s</subitem>
          </listitem>
          <listitem>
            <subitem>%s</subitem>
            <subitem><![CDATA[%s]]></subitem>
          </listitem>
          <listitem>
            <subitem>%s</subitem>
            <subitem>%s</subitem>
          </listitem>
        </items>
      </listview>',
      papaya_strings::escapeHTMLChars($this->_gt('Event Details')),
      papaya_strings::escapeHTMLChars($this->_gt('Title')),
      papaya_strings::escapeHTMLChars($event['title']),
      papaya_strings::escapeHTMLChars($this->_gt('Start')),
      date('Y-m-d H:i:s', $event['start_stamp']),
      papaya_strings::escapeHTMLChars($this->_gt('End')),
      date('Y-m-d H:i:s', $event['end_stamp']),
      papaya_strings::escapeHTMLChars($this->_gt('Recurrence')),
      papaya_strings::escapeHTMLChars($event['recur_human_readable']),
      papaya_strings::escapeHTMLChars($this->_gt('Location')),
      papaya_strings::escapeHTMLChars($event['location']),
      papaya_strings::escapeHTMLChars($this->_gt('Description')),
      papaya_strings::escapeHTMLChars($event['description']),
      papaya_strings::escapeHTMLChars($this->_gt('Owner')),
      papaya_strings::escapeHTMLChars($event['surfer_handle'])
    );

    // initialize 'Publish into ...' Dialog
    $cals = $this->_getPublicCalendarsForCombo();
    $fields = array(
      'calendar' => array('into calendar', 'isNum', TRUE, 'combo', $cals),
    );
    $hidden = array(
      'cmd' => 'publish',
      'event' => $eventId
    );
    $data = array();
    if (!is_null($calendarId)) {
      $data['calendar'] = $calendarId;
    }
    $publish = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $publish->dialogTitle = $this->_gt('Publish this Event');
    $publish->buttonTitle = 'Publish!';

    // setup output
    $this->layout->addLeft($this->_getPublicCalendarsList($event['calendar_id']));
    $this->layout->addLeft($this->_getRecommendedEventsList($event['event_id']));

    $this->layout->add($details);
    $this->layout->add($publish->getDialogXML());

    // add calendar info
    if (is_null($calendarId)) {
      $calendarId = $event['calendar_id'];
    }

    $calInfo = $this->calendarObj->loadCalendar((int)$calendarId);

    if (is_null($calInfo['title'])) {
      $calInfo['title'] = $this->_gtf('%s\'s Calendar', $calInfo['surfer_handle']);
    }

    $this->layout->addRight($this->_getRecommendationsList($eventId));
    $this->layout->addRight(
      $this->_getEventsList((int)$calendarId, $eventId, $calInfo['title'])
    );
  }

  /**
  * publish Event into given calendar
  *
  * @param int $eventId id of event to publish
  * @param int $calendarId id of calendar to publish the event into
  * @return void
  */
  function publishEvent($eventId, $calendarId) {

    if ($eventId == 0) {
      $this->_showError('Invalid Event!');
      return;
    }

    $calendar = new base_community_calendar();

    // load calendar info
    $calInfo = $calendar->loadCalendar($calendarId);
    if (!$calInfo) {
      $this->_showError('Invalid Calendar!');
      return;
    }

    // copy event into new calendar
    if ($calendar->copyEvent($eventId, array('calendar_id' => $calendarId))) {
      $type = 'info';
      $message = $this->_gt('Event Published!');
    } else {
      $type = 'error';
      $message = $this->_gt('Database Error!');
    }

    // info-dialog
    $hidden = array();
    $msgdialog = new base_msgdialog($this, $this->paramName, $hidden, $message, $type);
    $msgdialog->buttonTitle = 'OK';
    $msgdialog->baseLink = $this->getLink(
      array(
        'cmd' => 'event',
        'event' => $eventId
      )
    );

    $this->layout->add($msgdialog->getMsgDialog());
    $this->showEvent($eventId);
  }

  /**
  * delete an event after confirmation
  *
  * @param int $eventId id of event to delete
  * @param boolean $save delete from database
  */
  function deleteEvent($eventId, $save = FALSE) {

    // load event-details
    $event = $this->calendarObj->loadEvent($eventId);

    if (!$save) {
      // only display a confirmation dialog
      if (!$event) {
        $this->_showError('Invalid Event!');
        return;
      }
      $hidden = array(
        'cmd' => 'delete_event',
        'event' => $eventId,
        'save' => 1,
      );
      $message = $this->_gtf('Really delete event "%s"?', $event['title']);
      $type = 'question';
      $dialog =
        new base_msgdialog($this->module, $this->paramName, $hidden, $message, $type);
      $dialog->baseLink = $this->getBaseLink();
      $dialog->buttonTitle = 'Delete';

      $this->layout->add($dialog->getMsgDialog());
      $this->showEvent($eventId);
      return;
    }

    // delete event
    if ($this->calendarObj->deleteEvent($eventId)) {
      $type = 'info';
      $message = $this->_gt('Event Deleted!');
    } else {
      $type = 'error';
      $message = $this->_gt('Database Error!');
    }

    // show info-dialog
    $hidden = array();
    $msgdialog =
      new base_msgdialog($this->module, $this->paramName, $hidden, $message, $type);
    $msgdialog->buttonTitle = 'OK';
    $msgdialog->baseLink = $this->getLink(
      array(
        'cmd' => 'calendar',
        'calendar' => $event['calendar_id']
      )
    );

    $this->module->layout->add($msgdialog->getMsgDialog());
    $this->editCalendar((int)$event['calendar_id']);

  }

  /**
  * initilize admin interface
  *
  * @return void
  */
  function initializeUI() {
    $menubar = new base_btnbuilder();
    $menubar->images = &$this->images;
    $menubar->addButton(
      'recommended Events',
      $this->getBaseLink(),
      'status-dummy-icon'
    );
    $menubar->addSeperator();
    $menubar->addButton(
      'Add Calendar',
      $this->getLink(array('cmd' => 'add_calendar')),
      'status-dummy-icon'
    );
    $menubar->addSeperator();
    $menubar->addButton(
      'Add Event',
      $this->getLink(array('cmd' => 'add_event')),
      'status-dummy-icon'
    );
    $this->layout->addMenu(
      sprintf(
        '<menu ident="community_calendar_admin_menu">%s</menu>'.LF,
        $menubar->getXML()
      )
    );
  }

  /**
  * show an error message
  *
  * @param string $message error-message
  * @param string $link link for OK-Button
  * @return void
  */
  function _showError($message, $link = '') {
    if ($link == '') {
      $link = $this->module->getBaseLink();
    }
    $hidden = array();
    $error =
      new base_msgdialog($this->module, $this->paramName, $hidden, $message, 'error');
    $error->baseLink = $link;
    $error->buttonTitle = 'OK';
    $this->layout->add($error->getMsgDialog());
  }

  /**
  * get list of events with recommendations
  *
  * @param int $eventId only fetch recommendations for this event
  * @return string listview xml
  */
  function _getRecommendedEventsList($eventId = NULL) {
    $list = sprintf('<listview title="%s">'.LF, $this->_gt('Recommended Events'));
    $list .= '<items>'.LF;

    // load recommendations
    $recs = $this->calendarObj->loadRecommendationsList();

    foreach ($recs as $rec) {
      $selected = '';
      if ($rec['event_id'] == $eventId) {
        $selected = ' selected="selected"';
      }
      $list .= sprintf(
        '<listitem title="%s" href="%s"%s>'.LF,
        papaya_strings::escapeHTMLChars($rec['event_title']),
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'event',
              'event' => $rec['event_id'],
            )
          )
        ),
        $selected
      );
      $list .= '<subitem align="right">'.LF;
      $list .= sprintf(
        '<a href="%s" title="%s"><glyph src="%s" /></a>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'clear',
              'event' => $rec['event_id']
            )
          )
        ),
        papaya_strings::escapeHTMLChars(
          $this->_gt('Clear all Recommendations for this Event')
        ),
        papaya_strings::escapeHTMLChars($this->images['actions-edit-clear'])
      );
      $list .= '</subitem>'.LF;
      $list .= '</listitem>'.LF;
    }

    $list .= '</items>'.LF.'</listview>'.LF;

    return $list;
  }

  /**
  * get list of recommendations for an event
  *
  * @param int $eventId id of event
  * @return string listview xml
  */
  function _getRecommendationsList($eventId) {

    // get Recommendations for this event
    $recommendations = $this->calendarObj->loadRecommendations($eventId);
    if (!$recommendations) {
      return '';
    }

    $list = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Recommendations'))
    );
    $list .= sprintf(
      '<cols><col>%s</col><col>%s</col></cols>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('By User')),
      papaya_strings::escapeHTMLChars($this->_gt('For Calendar'))
    );
    $list .= '<items>'.LF;

    foreach ($recommendations as $rec) {
      $list .= sprintf(
        '<listitem title="%s">
          <subitem>%s</subitem>
        </listitem>',
        papaya_strings::escapeHTMLChars($rec['recommendator']),
        papaya_strings::escapeHTMLChars($rec['calendar_title'])
      );
    }

    $list .= '</items>'.LF;
    $list .= '</listview>'.LF;

    return $list;
  }

  /**
  * get list of events inside a calendar
  *
  * @param int $calendarId id of calendar
  * @param int $eventId id of event to highlight
  * @param string $calendarTtile title of calendar
  * @return string listview xml
  */
  function _getEventsList($calendarId, $eventId = NULL, $calendarTitle = '') {
    // load Events
    $events = $this->calendarObj->loadEventsByCalendar($calendarId);
    $title = $this->_gt('Events in this Calendar');
    if ($calendarTitle != '') {
      $title = $calendarTitle;
    }
    $list = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($title)
    );
    $list .= '<items>'.LF;

    foreach ($events as $event) {
      $selected = '';
      if ($event['event_id'] == $eventId) {
        $selected = ' selected="selected"';
      }
      $list .= sprintf(
        '<listitem href="%s" title="%s"%s>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'event',
              'event' => $event['event_id']
            )
          )
        ),
        papaya_strings::escapeHTMLChars($event['title']),
        $selected
      );
      $list .= sprintf(
        '<subitem align="right"><a href="%s"><glyph src="%s" /></a></subitem>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'edit_event',
              'event' => $event['event_id']
            )
          )
        ),
        papaya_strings::escapeHTMLChars($this->images['actions-edit'])
      );
      $list .= sprintf(
        '<subitem align="right"><a href="%s"><glyph src="%s" /></a></subitem>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'delete_event',
              'event' => $event['event_id']
            )
          )
        ),
        papaya_strings::escapeHTMLChars($this->images['places-trash'])
      );
      $list .= '</listitem>'.LF;
    }

    $list .= '</items>'.LF;
    $list .= '</listview>';

    return $list;
  }

  /**
  * get list of public calendars
  *
  * @param int $selectedCalendar id of calendar to highlight
  * @return string listview xml
  */
  function _getPublicCalendarsList($selectedCalendar = NULL) {

    // load public calendars
    $publicCalendars = $this->calendarObj->loadPublicCalendars();

    $list = sprintf(
      '<listview title="%s"><items>',
      papaya_strings::escapeHTMLChars($this->_gt('Public Calendars'))
    );

    foreach ($publicCalendars as $cal) {

      $selected = '';
      if (!is_null($selectedCalendar) && $selectedCalendar == $cal['calendar_id']) {
        $selected = ' selected="selected"';
      }

      $list .= sprintf(
        '<listitem title="%s" href="%s"%s>'.LF,
        papaya_strings::escapeHTMLChars($cal['title']),
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'calendar',
              'calendar' => $cal['calendar_id']
            )
          )
        ),
        $selected
      );

      $list .= sprintf(
        '<subitem align="right"><a href="%s" title="%s"><glyph src="%s" /></a></subitem>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(
            array(
              'cmd' => 'delete_calendar',
              'calendar' => $cal['calendar_id']
            )
          )
        ),
        papaya_strings::escapeHTMLChars($this->_gt('Delete this Calendar')),
        papaya_strings::escapeHTMLChars($this->images['places-trash'])
      );

      $list .= '</listitem>'.LF;
    }

    $list .= '</items></listview>';

    return $list;
  }

  /**
  * get List of Public Calendars ready to be used in a dialog combo
  *
  * @return array array of all calendars with id as key and title as value
  */
  function _getPublicCalendarsForCombo() {
    $calendars = $this->calendarObj->loadPublicCalendars();

    $cals = array();
    foreach ($calendars as $calendar) {
      $cals[$calendar['calendar_id']] = $calendar['title'];
    }
    return $cals;
  }

}

?>