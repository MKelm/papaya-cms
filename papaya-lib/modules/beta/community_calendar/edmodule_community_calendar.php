<?php
/**
* Community Calendar Administration
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
* @package Papaya-Modules
* @subpackage Beta-Calendar
* @version $Id: edmodule_community_calendar.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class modification module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
require_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
require_once(dirname(__FILE__).'/base_community_calendar.php');
require_once(dirname(__FILE__).'/admin_community_calendar.php');

/**
* Community Calendar Admin Module
*
* @package Papaya-Modules
* @subpackage Beta-Calendar
*/
class edmodule_community_calendar extends base_module {

  /**
  * Permissions
  * @var array $permissions
  */
  var $permissions = array();

  var $paramName = 'ccal';

  /**
  * Decides, which function to execute
  *
  * @access public
  */
  function execModule() {
    if ($this->hasPerm(1, TRUE)) {

      $admin = new admin_community_calendar($this);
      if (!isset($this->params['cmd'])) {
        $admin->showDefaultScreen();
        return;
      }

      switch ($this->params['cmd']) {
      case 'calendar':
        $admin->editCalendar((int)$this->params['calendar']);
        break;
      case 'event':
        if (!isset($this->params['calendar'])) {

          // show Event

          $admin->showEvent((int)$this->params['event']);
        } else {

          // show Event with different calendar on the side

          $admin->showEvent(
            (int)$this->params['event'],
            (int)$this->params['calendar']
          );
        }
        break;
      case 'add_event':
        $admin->addEvent();
        break;
      case 'delete_event':
        if (!isset($this->params['save'])) {
          $admin->deleteEvent((int)$this->params['event']);
        } else {
          $admin->deleteEvent((int)$this->params['event'], TRUE);
        }
        break;
      case 'edit_event':
        $admin->editEvent((int)$this->params['event']);
        break;
      case 'save_event':
        $admin->saveEvent($this->params);
        break;
      case 'publish':
        $admin->publishEvent(
          (int)$this->params['event'],
          (int)$this->params['calendar']
        );
        break;
      case 'clear':
        if (!isset($this->params['save'])) {
          $admin->clearRecommendations((int)$this->params['event']);
        } else {
          $admin->clearRecommendations((int)$this->params['event'], TRUE);
        }
        break;
      case 'add_calendar':
        $admin->addCalendar();
        break;
      case 'save_calendar':
        if (!isset($this->params['calendar_id'])) {
          $admin->saveCalendar($this->params['title'], $this->params['color']);
        } else {
          $admin->saveCalendar(
            $this->params['title'],
            $this->params['color'],
            $this->params['calendar_id']
          );
        }
        break;
      case 'delete_calendar':
        if (!isset($this->params['save'])) {
          $admin->deleteCalendar((int)$this->params['calendar']);
        } else {
          $admin->deletecalendar((int)$this->params['calendar'], TRUE);
        }
        break;
      default:
        include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
        $hidden = array();
        $dialog = new base_msgdialog(
          $this, $this->paramName, $hidden, 'Not implemented Yet', 'info'
        );
        $dialog->baseLink = 'javascript:history.back()';
        $dialog->buttonTitle = 'OK';
        $this->layout->add($dialog->getMsgDialog());
        break;
      }
    }
  }

}

?>