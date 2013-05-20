<?php
/**
* Diary
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
* @subpackage Free:Calendar
* @version $Id: papaya_calendar.php 37551 2012-10-16 11:26:34Z weinert $
*/

/**
* Basic class calendar
*/
require_once(dirname(__FILE__)."/base_calendar.php");

/**
* Diary
*
* @package Papaya-Modules
* @subpackage Free:Calendar
*/
class papaya_calendar extends base_calendar {

  /**
  * This array is used to store icons, that are used within this module only.
  * @var localImages = array()
  */
  var $localImages = array();

  /**
  * images
  * @var array $images
  */
  var $images;

  /**
  * Cronjob modules
  * @var array $cronModules
  */
  var $cronModules;
  /**
  * Regulars
  * @var array $regdates
  */
  var $regdates;
  /**
  * regdate
  * @var array $regdate
  */
  var $regdate;

  /**
  * lngSelect
  * @var array $lngSelect
  */
  var $lngSelect = NULL;

  /**
  * Input field size, defaul medium
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'large';

  /**
   * Separate dialogobjects of single and regular dates.
   * @var base_dialog $regdatePropertiesDialog
   */
  var $regdatePropertiesDialog = NULL;

  /**
   * Separate dialogobjects of single and regular dates.
   *
   * @var base_dialog $datePropertiesDialog
   */
  var $datePropertiesDialog = NULL;

  /**
  * Store alternative date titles in the current content language,
  * if a date translation in the current language does not exist
  * @var array $alternativeDateTitles
  */
  var $alternativeDateTitles = array();

  /**
  * Date fields
  * @var array $dateFields
  */
  var $dateFields = array(
    'date_title' => array('Title', 'isSomeText', TRUE, 'input', 400),
    'date_text' => array('Date text', 'isSomeText', FALSE, 'input', 400),
    'date_content_guid' => array(
      'Content module', 'isSomeText', TRUE,
      'function', 'getComboContentModules'
    ),
    'language independent',
    'date_startf' => array('From', 'isIsoDate', TRUE, 'input', 14,
      'Start date in ISO format (2002-12-31)'),
    'date_endf' => array('To', 'isIsoDate', TRUE, 'input', 14,
      'End date in ISO format (2003-01-01)'),
    'date_state' => array('State', 'isSomeText', TRUE, 'combo',
       array(1 => 'Created', 2 => 'Published'),
      'Set to Published to make this date visible in frontend.', 1
    )
  );

  /**
  * change owner dialog.
  * @var array $dateFields
  */
  var $ownerFields = array(
    'surfergroup_id' => array(
      'Surfergroup', 'isSomeText', TRUE, 'function', 'getComboSurfergroups'
    ),
    'surfer_id' => array(
      'Surfer', 'isSomeText', TRUE, 'function', 'getComboSurfers'
    )
  );

  /**
  * Regular fields
  * @var array $regdateFields
  */
  var $regdateFields = array(
    'regdate_title' => array('Title', 'isSomeText', TRUE, 'input', 400),
    'regdate_text' => array('Date text', 'isSomeText', FALSE, 'input', 400),
    'regdate_content_guid' => array(
      'Content', 'isSomeText', TRUE, 'function', 'getComboContentModules',
      'Content module'
    ),
    'language independent',
    'regdate_days' => array('Duration (Days)', 'isSomeText', FALSE, 'input',
      4, '', 1),
    // 'Module selection',
    'regdate_module_guid' => array(
      'Time', 'isSomeText', TRUE, 'function', 'getComboTimeModules',
      'Module for calculating the dates'
    ),
    'regdate_state' => array('State', 'isSomeText', TRUE, 'combo',
      array(1 => 'Created', 2 => 'Published'),
      'Set to Published to make this date visible in frontend.', 1
    ),
    'Planning horizon and max. count',
    'regdate_start_iso' => array('From', 'isIsoDate', FALSE, 'input', 14,
      'Earliest date in ISO format (2002-12-31)'),
    'regdate_end_iso' => array('To', 'isIsoDate', FALSE, 'input', 14,
      'Latest date in ISO format (2003-12-31)'),
    'regdate_max' => array('Maximum', 'isNum', FALSE, 'input', 4,
      'Maximum count'),
  );

  /**
  * Initialize session parameters
  *
  * @param string $paramName optional, default value 'cal'
  * @param string $userId optional, default value ''
  * @access public
  */
  function initialize($paramName = 'cal', $userId = '') {
    $this->paramName = $paramName;
    $this->authorId = $userId;
    $this->sessionParamName = 'PAPAYA_SESS_calendar'.$this->paramName;
    $this->initializeParams();

    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('regdate', array('date'));
    $this->initializeSessionParam('date', array('regdate'));
    //$this->initializeSessionParam('cmd');
    $this->initializeSessionParam('mode');
    $this->initializeSessionParam('time');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);

    if (isset($this->params['time']) && $this->params['time'] > 0) {
      $this->selectedDate = $this->parseTimeToArray($this->params['time']);
    } else {
      $tmpDate = $this->parseTimeToArray(time());
      $this->selectedDate = $this->parseTimeToArray(
        mktime(0, 0, 0, $tmpDate['month'], $tmpDate['day'], $tmpDate['year']));
    }

    if (!isset($this->lngSelect)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
      $this->lngSelect = &base_language_select::getInstance();
      $this->lngSelect->loadLanguages();
    }
    $this->initLanguageId();
    $this->loadSurferGroups();

    if (!isset($this->tags)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_taglinks.php');
      $this->tags = new papaya_taglinks($this, $this->paramName);
      $this->tags->msgs = &$this->msgs;
      $this->tags->authUser = &$this->authUser;
      $this->tags->images = &$this->images;
    }
    if (isset($this->params['date'])) {

      $this->loadDate($this->params['date']);
      $this->mode = 'date';
      $this->selectedDateId = $this->params['date'];

      if ($this->loadedDate['regdate_id'] != 0) {
        $this->params['regdate'] = $this->loadedDate['regdate_id'];
      }
    }

    if (isset($this->params['regdate'])) {
      $this->loadRegdate($this->params['regdate']);
    }
    if (isset($this->params['regdate']) && !isset($this->params['date'])) {
      $this->loadedDate = array('regdate_id' => $this->params['regdate']);
    }
    if (@$this->params['cmd'] == 'date') {
      if (@$this->params['mode'] == 1 || @$this->params['mode'] == 2) {
        $this->params['mode'] = 0;
      }
    }

    $imagePath = 'module:'.$this->module->guid;
    $this->localImages = array(
      'date' => $this->module->getIconURI('date.png'),
      'date-add' => $this->module->getIconURI('date-add.png'),
      'date-delete' => $this->module->getIconURI('date-delete.png'),
      'date-published' => $this->module->getIconURI('date-published.png'),
      'date-unpublished' => $this->module->getIconURI('date.png'),
      'regdate' => $this->module->getIconURI('regdate.png'),
      'regdate-add' => $this->module->getIconURI('regdate-add.png'),
      'regdate-delete' => $this->module->getIconURI('regdate-delete.png'),
      'regdate-published' => $this->module->getIconURI('regdate-published.png'),
      'regdate-unpublished' => $this->module->getIconURI('regdate.png'),
      'dates-spawn' => $this->images['actions-database-refresh'],
      'dates-table' => $this->images['items-table']
    );

    $this->loadModulesList();
    $this->loadRegdates();
  }

  /**
  * Execute - basic function for handling parameters
  *
  * @access public
  */
  function execute() {
    switch (@$this->params['cmd']) {
    // In case of mcal dates.
    case 'day':
    case 'wday':
    case 'week':
    case 'month':
    case 'date':
      $this->editMcalEdit();
      break;
    // In case of linking dates.
    case 'link_tag':
    case 'unlink_tag':
    case 'date':
    case 'regdate':
    case 'edit':
    case 'createcache':
    case 'transdate':
    case 'copy':
    case 'del':
    case 'transreg':
    case 'separate':
    case 'ignore':
    case 'delignore':
    case 'delreg':
      if (isset($this->params['date']) && $this->checkPerm(2, FALSE)) {
        $this->editDate();
      }
      if (isset($this->params['regdate']) && $this->checkPerm(3, FALSE)) {
        $this->editRegdate();
      }
      if ($this->checkPerm(5, FALSE)) {
        $this->editOwnership();
      }
      break;
    case 'addreg':
      if ($this->checkPerm(3, FALSE)) {
        if ($newId = $this->addRegdate()) {
          $this->initializeSessionParam('mode');
          $this->initializeSessionParam('regdate', array('date'));
          unset($this->params['date']);
          $this->params['mode'] = 0;
          $this->params['regdate'] = $newId;
          $this->loadRegdate($newId);
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error! Could not create a new regular date.')
          );
        }
      }
      break;
    case 'add':
      if ($this->checkPerm(2, FALSE)) {
        if ($newId = $this->addDate()) {
          $this->initializeSessionParam('mode');
          $this->initializeSessionParam('date', array('regdate'));
          unset($this->params['regdate']);
          unset($this->loadedRegdate);
          $this->params['mode'] = 0;
          $this->params['date'] = $newId;
          $this->loadDate($newId);
          $this->mcalClickEdit($newId);
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error! Could not create a new date.')
          );
        }
      }
      break;
    }
  }

  /**
  * Get XML
  *
  * @access public
  */
  function getXML() {
    $this->layout->addLeft($this->showMonthTable());
    $this->layout->addLeft($this->showList($this->images));
    $this->layout->addRight($this->showRegdateDates());

    $this->getToolbar();
    $this->layout->add($this->showData());
    $this->getButtons();
  }

  /**
  * Checks permissions, output errors and unset command parameter
  *
  * @param mixed $permId optional, default value NULL
  * @access public
  * @return boolean
  */
  function checkPerm($permId = NULL, $errorMsg = TRUE) {
    if ($this->module->hasPerm($permId, $errorMsg)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * edit date information.
  *
  */
  function editDate() {
    $this->getContentModuleEdit(FALSE);
    $this->loadModulesList();
    if (isset($this->params['date'])) {
      $this->loadDate($this->params['date']);
    }

    switch (@$this->params['cmd']) {
    case 'del':
      if (!(isset($this->params['confirm']) && $this->params['confirm'])) {
        $this->layout->add($this->showDelConfirm());
      } else {
        if ($this->mcalClickDelete($this->params['date'])) {
          $this->addMsg(
            MSG_INFO,
            $this->_gt('Date has been deleted successfully.')
          );
          $this->loadRegdates();
          $this->getDaysOfMonth();
          unset($this->params['cmd']);
          unset($this->params['mode']);
          unset($this->loadedDate);
          unset($this->params['date']);
        }
      }
      break;
    case 'transdate':
      $this->loadRegdate((int)$this->params['date']);
      if ($this->params['transdate_confirm'] == '1') {
        if ($this->addDateTranslation($this->params['date'])) {
          $this->addMsg(
            MSG_INFO,
            sprintf(
              $this->_gt("A new translation to the %s language has been created for date (%s)."),
              $this->lngSelect->currentLanguage['lng_title'],
              (int)$this->params['date']
            )
          );
          $this->loadDate($this->params['date']);
          $this->params['mode'] = 0;
        }
      }
      break;

    case 'copy':
      $this->params['date'] = $this->copyDate();
      $this->loadDate($this->params['date']);
    case 'date':
    case 'edit':
      if (isset($this->params['save']) && $this->params['save'] == 1) {
        $this->mcalClickEdit($this->params['date']);
        $row = $this->prepareDatePropertiesInput($this->loadDate($this->params['date']));
        if ($this->checkDateInput($row)) {
          $this->saveDate($row);
          if ($this->loadedDate['date_untranslated'] >= 2) {
            $this->loadDate($this->params['date']);
          }
          $this->getDaysOfMonth(FALSE);
        }
      }
      break;
    }
  }

  /**
  * editRegdate - Editing a regulary date.
  *
  * @access public
  */
  function editRegdate() {
    $this->getContentModuleEdit(TRUE);
    $this->getCronModuleEdit();

    $this->loadModulesList();
    if (isset($this->params['save'])  && $this->params['mode'] < 1) {
      if (isset($this->params['regdate'])) {
        if ($changes = $this->checkRegdateInput()) {
          if ($this->saveRegdateProperties()) {
            $this->loadRegdate((int)$this->params['regdate']);
            if ($changes['replace']) {
              // create and delete dates
              if (FALSE !== ($count = $this->recreateRegdateCache())) {
                $this->addMsg(
                  MSG_INFO,
                  sprintf(
                    $this->_gt('Regular date saved and %d dates created.'),
                    (int)$count
                  )
                );
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Regular date saved but no dates created.')
                );
              }
            } elseif ($changes['update']) {
              // modify dates
              if ($this->updateRegdateCache()) {
                $this->addMsg(
                  MSG_INFO,
                  $this->_gt('Regular date saved and dates changed.')
                );
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Regular date saved but dates not changed.')
                );
              }
            }
          }
        }
      }
    }

    switch (@$this->params['cmd']) {
    case 'createcache':
      $this->loadRegdate((int)$this->params['regdate']);
      if (FALSE !== ($count = $this->recreateRegdateCache())) {
        $this->addMsg(
          MSG_INFO,
          sprintf($this->_gt('%d single date(s) created!'), (int)$count)
        );
      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('Database error! No dates created.')
        );
      }
      $this->params['cmd'] = 'regdate';
      $this->params['mode'] = 2;
      //$this->initializeSessionParam('cmd');
      $this->initializeSessionParam('mode');
      $this->loadRegdate((int)$this->params['regdate']);
      break;
    case 'delreg':
      if (isset($this->params['delconfirm'])) {
        if ($this->deleteRegdate((int)$this->params['regdate'])) {
          $this->addMsg(MSG_INFO, $this->_gt('Regular date deleted.'));
          unset($this->loadedRegdate);
          unset($this->loadedDate);
          unset($this->params['cmd']);
          unset($this->params['regdate']);
          unset($this->params['mode']);
          $this->initializeSessionParam('regdate', array('date'));
          $this->initializeSessionParam('mode');
          $this->initializeSessionParam('cmd');
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error! Changes not saved.')
          );
        }
      } else {
        $this->loadRegdate((int)$this->params['regdate']);
        $this->layout->add($this->getDeleteRegdateDialog());
      }
      $this->mode = 'delreg';
      break;
      break;
    case 'separate':
      $this->loadRegdate((int)$this->params['regdate']);
      if (@is_array($this->loadedRegdate)) {
        if ($topicId = $this->addIgnoreDate((int)$this->params['ignore_id'], TRUE)) {
          $this->addMsg(
            MSG_INFO,
            $this->_gt('Date detached from regular date.')
          );
          $this->params['cmd'] = 'date';
          unset($this->params['regdate']);
          $this->params['mode'] = 0;
          $this->params['date'] = $topicId;
          $this->mode = 'date';
          $this->loadDate($topicId);
          unset($this->loadedRegdate);
          unset($this->params['regdate']);
          $this->selectedDateId = $topicId;
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error! Changes not saved.')
          );
        }
      }
      break;
    case 'ignore':
      $this->loadRegdate((int)$this->params['regdate']);
      if (@is_array($this->loadedRegdate)) {
        if ($this->addIgnoreDate((int)$this->params['ignore_id'])) {
          $this->addMsg(MSG_INFO, $this->_gt('Added date to ignore list.'));
          $this->params['cmd'] = 'regdate';
          $this->params['mode'] = 2;
          //$this->initializeSessionParam('cmd');
          $this->initializeSessionParam('mode');
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error! Changes not saved.')
          );
        }
      }
      $this->loadRegdate((int)$this->params['regdate']);
      break;
    case 'delignore':
      $this->loadRegdate((int)$this->params['regdate']);
      if (@is_array($this->loadedRegdate)) {
        if ($this->delIgnoreDate((int)$this->params['ignore_id'])) {
          $this->addMsg(
            MSG_INFO,
            $this->_gt(
              'Removed date from ignore list. '.
              "In case you want to restore the date, please click on 'Create dates'."
            )
          );
          $this->params['cmd'] = 'regdate';
          $this->params['mode'] = 2;
          //$this->initializeSessionParam('cmd');
          $this->initializeSessionParam('mode');
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database error! Changes not saved.')
          );
        }
      }

    case 'regdate':
      break;

    case 'transreg':
      $this->loadRegdate((int)$this->params['regdate']);
      if ($this->params['transreg_confirm'] == '1') {
        if ($this->addRegdateTranslation()) {
          $this->addMsg(
            MSG_INFO,
            sprintf(
              $this->_gt(
                'A new translation to language (%s) has been created'.
                ' for regulary date (%s).'),
              papaya_strings::escapeHTMLChars($this->lngSelect->currentLanguageId),
              papaya_strings::escapeHTMLChars($this->params['regdate'])
            )
          );
          $this->loadRegdate($this->params['regdate']);
          $this->params['mode'] = 0;
        }
      }
      break;
    }

    $this->loadRegdates();
    if (isset($this->params['regdate'])) {
      $this->loadRegdate($this->params['regdate']);
    }
    $this->loadModulesList();
  }

  /**
  * Using the monthly-calendar box to do something.
  *
  * @access public
  */
  function editMcalEdit() {
    switch($this->params['cmd']) {
    case 'day':
      $this->mcalClickDay($this->params['newtime']);
      break;
    case 'wday':
      $this->mcalClickWeekDay($this->params['wday']);
      break;
    case 'week':
      $this->mcalClickWeek($this->params['week']);
      break;
    case 'month':
      $this->mcalClickMonth($this->params['month']);
      break;
    case 'date':
      $this->mode = 'date';
      $this->selectedDateId = (int)$this->params['date'];
      break;
    }
  }

  /**
  * Load a regulary date's data into memory.
  *
  * @param integer $RegdateId
  * @access public
  * @return boolean
  */
  function loadRegdate($regdateId) {
    unset($this->loadedRegdate);
    $sql = "SELECT cr.regdate_id, cr.regdate_days, cr.regdate_start, cr.regdate_end,
                   cr.regdate_max, cr.regdate_state,
                   cr.surfer_id, cr.surfergroup_id,
                   mc.module_class AS class_content,
                   mc.module_path AS module_path_content,
                   mc.module_file AS module_file_content,
                   cr.regdate_module_guid, cr.regdate_moduledata,
                   mt.module_class AS class_time,
                   mt.module_path AS module_path_time,
                   mt.module_file AS module_file_time,
                   t.regdate_content_guid, t.regdate_title,
                   t.regdate_data, t.regdate_text, t.lng_id
              FROM %s AS cr
              LEFT OUTER JOIN %s AS t ON t.regdate_id = cr.regdate_id AND t.lng_id = %d
              LEFT OUTER JOIN %s AS mc ON mc.module_guid = t.regdate_content_guid
              LEFT OUTER JOIN %s AS mt ON mt.module_guid = cr.regdate_module_guid
             WHERE cr.regdate_id = %d";
    $params = array(
      $this->tableRegdates,
      $this->tableRegdateTrans, $this->lngSelect->currentLanguageId,
      $this->tableModules, $this->tableModules,
      $regdateId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['file_content'] = $row['module_path_content'].
          $row['module_file_content'];

        $row['regdate_untranslated'] = 0;
        if (!$row['regdate_title']) {
          $row['regdate_title'] = '';
          $row['regdate_untranslated']++;
        }
        if (!$row['regdate_text']) {
          $row['regdate_text'] = '';
          $row['regdate_untranslated']++;
        }

        $row['file_time'] = $row['module_path_time'].$row['module_file_time'];
        $row['regdate_start_iso'] = date('Y-m-d', (int)$row['regdate_start']);
        $row['regdate_end_iso'] = date('Y-m-d', (int)$row['regdate_end']);
        $this->loadedRegdate = $row;
        $this->mode = 'regdate';
        $this->loadRegdateCache();
        $this->loadRegdateIgnores();
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Associates tags from a regdate to a detached date.
  *
  * @param integer $regdateId, $dateId
  * @access public
  * @return boolean
  */
  function cloneRegdateTags($regdateId, $dateId) {
    $usedTags = $this->tags->getLinkedTags('regdate', $regdateId);
    foreach ($usedTags AS $tag) {
      $this->tags->linkTag('date', $dateId, $tag);
    }
  }

  /**
  * Create translation for the current date.
  *
  * @param integer $lngId
  * @access public
  * @return boolean
  */
  function createDateTranslation($lngId) {
    $data = array(
      'date_id' => (int)$this->dateId,
      'lng_id' => (int)$lngId,
      'date_title' => $this->_gt($this->tplDefaultDateTitle),
      'date_text' => $this->_gt($this->tplDefaultDateDatestr),
      'date_data' => '',
      'date_content_guid' => ''
    );
    return FALSE !== $this->databaseInsertRecord(
      $this->tableDatesTrans, NULL, $data
    );
  }

  /**
  * Adds an date to the database and sets current language content.
  *
  * @access public
  * @return mixed
  */
  function addDate() {
    $data = array(
      'date_start' => $this->selectedDate['time'],
      'date_end' =>$this->selectedDate['time'],
      'author_id' => $this->authorId
    );
    $dateId = $this->databaseInsertRecord($this->tableDates, 'date_id', $data);

    $transdata = array(
      'date_id' => $dateId,
      'lng_id' => $this->lngSelect->currentLanguageId,
      'date_title' => $this->_gt($this->tplDefaultDateTitle),
      'date_text' => $this->_gt($this->tplDefaultDateDatestr),
      'date_data' => '',
      'date_content_guid' => ''
    );

    $this->databaseInsertRecord($this->tableDateTrans, NULL, $transdata);

    return $dateId;
  }

  /**
  * Deletes an date.
  *
  * @param integer $topic
  * @access public
  * @return mixed
  */
  function deleteDate($dateId) {
    if ($dateId > 0) {
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_taglinks.php');
      $tags = papaya_taglinks::getInstance($this);
      $tags->unlinkTag('date', $dateId);
      $result = $this->databaseDeleteRecord(
        $this->tableDateTrans, 'date_id', (int)$dateId
      );
      $result &= $this->databaseDeleteRecord(
        $this->tableDates, 'date_id', (int)$dateId
      );
      unset($this->dateId);
      return $result;
    }

    return FALSE;
  }

  /**
  * Save a calendar date entry.
  *
  * @param array $row
  * @access public
  * @return boolean
  */

  function saveDate($row) {
    $result = FALSE;
    if ($row['date_id'] > 0) {

      if (!$this->checkPerm(4, FALSE) && isset($row['date_state']) &&
          $row['date_state'] == 2) {
        $this->addMsg(MSG_ERROR, $this->_gt('You can not publish dates.'));
        $row['date_state'] = 1;
      }

      $dataTranslation = array(
        'date_id' => $row['date_id'],
        'date_title' => $row['date_title'],
        'date_data' => $row['date_data'],
        'date_text' => $row['date_text'],
        'date_content_guid' => $row['date_content_guid'],
        'lng_id' => $this->lngSelect->currentLanguageId
      );

      $dataProperties = array(
        'date_start' => $row['date_start'],
        'date_end' => $row['date_end'],
        'date_state' => $row['date_state'],
        'author_id' => $this->authorId
      );

      $updated = $this->databaseUpdateRecord(
        $this->tableDates, $dataProperties, 'date_id', (int)$row['date_id']
      );
      if (FALSE !== $updated) {
        $this->msgType = MSG_INFO;
        $this->msg = $this->_gt($this->tplMsgSaved);
        $result = TRUE;
        $this->addMsg(
          MSG_INFO,
          sprintf(
            $this->_gt('Calendar date "%s" modified.'),
            $row['date_title']
          )
        );
      }

      $sql = "SELECT COUNT(*) AS amount
                FROM %s
               WHERE date_id = %d
                 AND lng_id = %d";
      $params = array($this->tableDateTrans, (int)$row['date_id'],
        $this->lngSelect->currentLanguageId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        $count = $res->fetchRow(DB_FETCHMODE_ASSOC);
        if ($count['amount'] != 0) {
          // Yes, update it.
          $updated = $this->databaseUpdateRecord(
            $this->tableDateTrans,
            $dataTranslation,
            array(
              'date_id' => (int)$row['date_id'],
              'lng_id' => $this->currentLanguageId
            )
          );
          if ($updated) {
            $this->addMsg(
              MSG_INFO,
              sprintf(
                $this->_gt('Calendar date "%s" (%s) translation modified.'),
                $row['date_title'],
                $row['date_id']
              )
            );
          } else {
            $result = FALSE;
          }
        } else {
          // No, create a new one.
          $inserted = $this->databaseInsertRecord(
            $this->tableDateTrans, NULL, $dataTranslation
          );
          if ($inserted) {
            $this->addMsg(
              MSG_INFO,
              sprintf(
                $this->_gt('Calendar date "%s" (%s) translation created.'),
                $row['date_title'],
                $row['date_id']
              )
            );
            $result = TRUE;
          } else {
            $result = FALSE;
          }
        }
      }
    }
    return $result;
  }

  /**
  * Copies an date.
  *
  * @access public
  * @return mixed
  */
  function copyDate() {
    if (isset($this->loadedDate) && is_array($this->loadedDate)) {
      $dateId = $this->loadedDate['date_id'];

      $row = $this->loadedDate;
      $data = array(
        'date_start' => $row['date_start'],
        'date_end' => $row['date_end'],
        'date_state' => 1,
        'surfer_id' => $row['surfer_id'],
        'surfergroup_id' => $row['surfergroup_id'],
        'author_id' => $this->authorId
      );

      if ($new = $this->databaseInsertRecord($this->tableDates, 'date_id', $data)) {
        $this->selectedDateId = $new;
      }

      if (isset($new)) {
        // As soon as the new date has been created, look for translations of the old
        // one to copy them as well. If there are no translations available yet, the
        // new entry won't have any translations as well.
        $sql = "SELECT lng_id, date_title, date_data,
                       date_content_guid, date_text
                  FROM %s
                 WHERE date_id = %d";
        $params = array($this->tableDateTrans, $dateId);
        if ($res = $this->databaseQueryFmt($sql, $params)) {
          $translations = array();
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $row['date_id'] = $new;
            $translations[] = $row;
          }
        }

        foreach ($translations as $trans) {
          $this->databaseInsertRecord($this->tableDateTrans, NULL, $trans);
        }

        $this->cloneDateTags($this->loadedDate['date_id'], $new);

        // And finally reload what was changed.
        $this->loadRegdate($new);
        $this->loadRegdates();
        return $new;
      }
    }
    return FALSE;
  }


  /**
  * Copies tags from an entry to date tags, when
  * a date is copied from another date.
  *
  * @param integer $dateID, $newDateId
  * @access public
  * @return boolean
  */
  function cloneDateTags($dateId, $newDateId) {
    $usedTags = $this->tags->getLinkedTags('date', $dateId);
    foreach ($usedTags as $tag) {
      $this->tags->linkTag('date', $newDateId, $tag);
    }
  }

  /**
  * Load Regdates, loads overview of regulary dates into memory.
  *
  * @access public
  */
  function loadRegdates() {
    unset($this->regdates);

    $sql = "SELECT cr.regdate_id,
                   cr.regdate_module_guid, t.regdate_content_guid,
                   cr.regdate_start, cr.regdate_end, cr.regdate_max, cr.regdate_state,
                   cr.surfer_id, cr.surfergroup_id, cr.regdate_moduledata,
                   t.regdate_title, t.regdate_data, t.lng_id, t.regdate_content_guid,
                   count(c.regdate_id) AS regdate_count
              FROM %s AS cr
   LEFT OUTER JOIN %s AS t ON t.regdate_id = cr.regdate_id AND t.lng_id = %d
   LEFT OUTER JOIN %s AS c ON c.regdate_id = cr.regdate_id
             GROUP BY cr.regdate_id, t.regdate_title, t.regdate_data,
                   cr.regdate_start, cr.regdate_end, cr.regdate_max,
                   cr.regdate_module_guid, cr.regdate_moduledata,
                   cr.surfer_id, cr.surfergroup_id, t.lng_id
             ORDER BY cr.regdate_start";

    $params = array(
      $this->tableRegdates,
      $this->tableRegdateTrans, $this->lngSelect->currentLanguageId,
      $this->tableDates);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->regdates[$row['regdate_id']] = $row;
      }

      if (isset($this->regdates) && is_array($this->regdates)) {
        foreach ($this->regdates as $row) {
          if (!$row['regdate_title']) {
            $row['regdate_title'] = '';
          }
          if (!$row['regdate_data']) {
            $row['regdate_data'] = '';
          }
          $this->regdates[$row['regdate_id']] = $row;
        }
      }
    }
  }

  /**
  * Save a regdate entry as well as its translation depending on the
  *  currently selected language.
  * Returns true, if either properties or translation have been changed.
  *
  * @access public
  * @return boolean
  */
  function saveRegdateProperties() {
    if (!$this->checkPerm(4, FALSE) && isset($this->params['regdate_state']) &&
        $this->params['regdate_state'] == 2) {
      $this->addMsg(MSG_ERROR, $this->_gt('You can not publish dates.'));
      $this->params['regdate_state'] = 1;
    }

    // Change the regdate's primary properties.
    $done1 = $this->databaseUpdateRecord(
      $this->tableRegdates,
      array(
        'regdate_days' => (int)$this->params['regdate_days'],
        'regdate_start' => (int)$this->params['regdate_start'],
        'regdate_end' => (int)$this->params['regdate_end'],
        'regdate_max' => (int)$this->params['regdate_max'],
        'regdate_state' => (int)$this->params['regdate_state'],
        'regdate_module_guid' => $this->params['regdate_module_guid']
      ),
      'regdate_id',
      (int)$this->params['regdate']
    );
    $done2 = $this->databaseUpdateRecord(
      $this->tableRegdateTrans,
      array(
        'regdate_title' => $this->params['regdate_title'],
        'regdate_text' => $this->params['regdate_text'],
        'regdate_content_guid' => $this->params['regdate_content_guid']
      ),
      array(
        'regdate_id' => $this->params['regdate'],
        'lng_id' => $this->lngSelect->currentLanguageId
      )
    );
    $this->updateRegdateCache();

    if (($done1 || $done2) !== FALSE) {
      $this->loadRegdates();
      $this->addMsg(
        MSG_INFO,
        sprintf(
          $this->_gt('Regular date "%s" changed.'),
          $this->params['regdate_title']
        )
      );
    }
    return (($done1 || $done2) !== FALSE);
  }

  /**
  * Adds a new translation filled with default strings
  * in the current language for the currently selected
  * regulary date.
  *
  * @access public
  * @return boolean
  */
  function addRegdateTranslation() {
    if (isset($this->loadedRegdate) && is_array($this->loadedRegdate)) {
      $data = array(
        'lng_id' => $this->lngSelect->currentLanguageId,
        'regdate_id' => $this->params['regdate'],
        'regdate_title' =>$this->_gt($this->tplDefaultRegdateTitle),
        'regdate_text' => $this->_gt($this->tplDefaultRegdateDatestr),
        'regdate_data' => '',
        'regdate_content_guid' => ''
      );
      $done = $this->databaseInsertRecord($this->tableRegdateTrans, NULL, $data);

      if (FALSE !== $done) {
        $this->loadRegdates();
        $this->loadRegdate($this->loadedRegdate['regdate_id']);
        $this->updateRegdateCache();
        return TRUE;
      }
    }
  }

  /**
  * Adds a new translation filled with default strings
  * in the current language for an identified date using
  * $dateId.
  *
  * @param integer $dateId
  * @access public
  * @return boolean
  */
  function addDateTranslation($dateId) {
    $sql = "SELECT COUNT(*) AS c
              FROM %s
             WHERE lng_id = %d
               AND date_id = %d
               AND regdate_id = '0'";
    $params = array($this->tableDateTrans, $this->lngSelect->currentLanguageId,
      $dateId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!($row['c'] > 0)) {
          $data = array(
            'lng_id' => $this->lngSelect->currentLanguageId,
            'date_id' => $dateId,
            'date_title' => $this->_gt($this->tplDefaultDateTitle),
            'date_text' => $this->_gt($this->tplDefaultDateDatestr),
            'date_data' => '',
            'date_content_guid' => ''
          );
          $done = $this->databaseInsertRecord(
            $this->tableDateTrans, NULL, $data
          );
          return ($done !== FALSE);
        }
      }
    }
  }

  /**
  * A wrapper function for owner mechanism.
  * Wrapps ownershipchange of regdates or dates.
  *
  * @access public
  */
  function editOwnership() {
    if (isset($this->params['date'])) {
      if ($this->setDateOwner() !== FALSE) {
        $this->addMsg(
          MSG_INFO,
          $this->_gt('Ownership of this date has been changed.')
        );
      }
    }
    if (isset($this->params['regdate']) && !isset($this->params['date'])) {
      if ($this->setRegdateOwner() !== FALSE) {
        $this->addMsg(
          MSG_INFO,
          $this->_gt('Ownership of this regular date has been changed.')
        );
      }
    }
  }

  /**
  * Sets surfer_id and surfergroup_id of a calendar-date.
  *
  * @access public
  * @return boolean
  */
  function setDateOwner() {
    if (isset($this->params['surfer_id']) ||
        isset($this->params['surfergroup_id'])) {
      $data = array();
      // set surfer id
      if (isset($this->params['surfer_id'])) {
        $data['surfer_id'] = $this->params['surfer_id'];
      } else {
        $data['surfer_id'] = '';
      }
      // set surfergroup id
      $data['surfergroup_id'] = (
        isset($this->params['surfergroup_id']) && (int)$this->params['surfergroup_id'] > 0
      ) ? $this->params['surfergroup_id'] : 0;
      // try to update database if data exists
      return FALSE !== $this->databaseUpdateRecord(
        $this->tableDates, $data, 'date_id', (int)$this->params['date']
      );
    }
    return FALSE;
  }

  /**
  * Sets surfer_id and surfergroup_id of a regular date.
  *
  * @access public
  * @return boolean
  */
  function setRegdateOwner() {
    if (isset($this->params['surfer_id']) &&
        isset($this->params['surfergroup_id'])) {
      $done1 = (
        FALSE !== (
          $this->databaseUpdateRecord(
            $this->tableRegdates,
            array(
              'surfer_id' => $this->params['surfer_id'],
              'surfergroup_id' => $this->params['surfergroup_id']
            ),
            'regdate_id',
            (int)$this->params['regdate']
          )
        )
      );
      $done2 = (
        FALSE !== (
        // If there are any dates existing which have been created
        // from this regulary one, their owners must be set as well.
          $this->databaseUpdateRecord(
            $this->tableDates,
            array(
              'surfer_id' => $this->params['surfer_id'],
              'surfergroup_id' => $this->params['surfergroup_id']
            ),
            'regdate_id',
            (int)$this->params['regdate']
          )
        )
      );
      return ($done1 && $done2);
    }
    return FALSE;
  }


  /**
  * Add regular date.
  *
  * @access public
  * @return mixed
  */
  function addRegdate() {
    $start = time();
    $end = $start + 31536000;
    $data = array(
      'regdate_start' => $start,
      'regdate_end' => $end,
      'regdate_state' => 1,
      'regdate_max' => $this->tplDefaultRegdateMax,
      'regdate_module_guid' => '',
      'regdate_moduledata' => ''
    );
    $regdateId = $this->databaseInsertRecord($this->tableRegdates, 'regdate_id', $data);
    $transdata = array(
      'regdate_id' => $regdateId,
      'lng_id' => $this->lngSelect->currentLanguageId,
      'regdate_title' => $this->_gt($this->tplDefaultRegdateTitle),
      'regdate_text' => $this->_gt($this->tplDefaultRegdateDatestr),
      'regdate_content_guid' => '',
      'regdate_data' => ''
    );

    $this->databaseInsertRecord($this->tableRegdateTrans, NULL, $transdata);
    $this->loadRegdates();
    $this->loadRegdate($regdateId);
    return $regdateId;
  }

  /**
  * deletes regular entry and anything composed to it,
  * like its ignorings, translations, still connected single dates and tags.
  * When there are single dates still connected to this regulary date,
  * they will be given all translations of the regular one.
  *
  * @param integer $regdateId
  * @access public
  * @return boolean
  */
  function deleteRegdate($regdateId) {

    $this->loadRegdate($regdateId);

    if (FALSE !== $this->databaseDeleteRecord(
          $this->tableDates, 'regdate_id', (int)$regdateId)) {
      if (FALSE !== $this->databaseDeleteRecord(
            $this->tableRegignore, 'regdate_id', (int)$regdateId)) {
        if (FALSE !== $this->databaseDeleteRecord(
          $this->tableRegdateTrans, 'regdate_id', (int)$regdateId)) {

          $done1 = $this->databaseDeleteRecord(
            $this->tableRegdates, 'regdate_id', (int)$regdateId);

          $done2 = $this->databaseDeleteRecord(
            $this->tableTagLinks,
            array('link_id' => (int)$regdateId, 'link_type' => 'regdate'));

          $dateList = $this->getRegdateConnectedDates($regdateId);

          $done3 = TRUE;
          foreach ($dateList as $date) {
            $done3 &= $this->deleteDate($date['date_id']);
          }
          unset($this->loadedRegdate);
          return (FALSE !== $done1 || $done2 || $done3);
        }
      }
    }

    return FALSE;
  }

  /**
  * Return a list of date_ids containing ids of dates
  * which are currently connected to the specified regular date.
  *
  * @param integer $regdateId
  * @return list
  */
  function getRegdateConnectedDates($regdateId) {
    $sql = "SELECT date_id
              FROM %s
             WHERE regdate_id = %d";
    $params = array(
      $this->tableDates,
      $regdateId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $dates = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $dates[] = $row['date_id'];
      }
      return $dates;
    }
  }

  /**
  * Save content data
  *
  * @param integer $data
  * @access public
  * @return mixed db_updateRecord
  */
  function saveCronData($data) {
    $info = array('regdate_moduledata' => $data);
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableRegdates, $info, "regdate_id", (int)$this->params['regdate']
    );
  }

  /**
  * Save regular content data will update contents of all connected
  * dates as well. After doing that it is not neccessary to reload.
  *
  * @param string $data
  * @access public
  * @return boolean
  */
  function saveRegdateContent($data) {
    $updated = $this->databaseUpdateRecord(
      $this->tableRegdateTrans,
      array('regdate_data' => $data),
      array(
        'regdate_id' => (int)$this->params['regdate'],
        'lng_id' => (int)$this->lngSelect->currentLanguageId
      )
    );
    if (FALSE !== $updated) {
      $dates = $this->getRegdateConnectedDates($this->params['regdate']);
      foreach ($dates as $date) {
        $this->databaseUpdateRecord(
          $this->tableDateTrans,
          array('date_data' => $data),
          array(
            'date_id' => $date['date_id'],
            'lng_id' => $this->lngSelect->currentLanguageId
          )
        );
      }

      if ($this->updateRegdateCache()) {
        $this->addMsg(
          MSG_INFO,
          $this->_gt('Regular date saved and dates changed.')
        );
      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('Database error! Changes not saved.')
        );
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Loads dates spawned by a regular date for the currently selected one (regular).
  * regularId is expected to be in $params['regdate_id'].
  *
  * @access public
  */
  function loadRegdateCache() {
    unset($this->cacheDates);
    $sql = "SELECT c.date_id, c.date_start, c.date_end, c.date_state, c.surfergroup_id,
                   c.author_id, c.regdate_id, c.surfer_id, c.surfergroup_id,
                   tr.date_title, tr.date_data, tr.date_text, tr.date_content_guid
              FROM %s AS c
   LEFT OUTER JOIN %s AS tr ON tr.date_id = c.date_id AND tr.lng_id = %d
             WHERE c.regdate_id = %d
             ORDER BY c.date_start ASC, c.date_end ASC, tr.date_title ASC";
    $params = array(
      $this->tableDates,
      $this->tableDateTrans,
      $this->lngSelect->currentLanguageId,
      (int)$this->loadedRegdate['regdate_id']
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->cacheDates[$row['date_id']] = $row;
      }

      if (isset($this->cacheDates) && is_array($this->cacheDates)) {
        foreach ($this->cacheDates as $row) {
          if (!$row['date_title']) {
            $row['date_title'] = '';
          }
          if (!$row['date_data']) {
            $row['date_data'] = '';
          }
          $this->cacheDates[$row['date_id']] = $row;
        }
      }
    }
  }

  /**
  * Load regdate ignores
  *
  * @access public
  */
  function loadRegdateIgnores() {
    unset($this->regdateIgnores);
    $sql = "SELECT regdate_ignoreid, regdate_ignoretime
              FROM %s
             WHERE regdate_id = %d
             ORDER BY regdate_ignoretime ASC";
    $params = array(
      $this->tableRegignore,
      (int)$this->loadedRegdate['regdate_id']
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->regdateIgnores[$row['regdate_ignoreid']] = $row;
      }
    }
  }

  /**
  * Recreate regular cache
  *
  * @access public
  * @return mixed db_query 0 or FALSE
  */
  function recreateRegdateCache() {
    if (isset($this->loadedRegdate) && is_array($this->loadedRegdate)) {
      $deleted = $this->databaseDeleteRecord(
        $this->tableDates, 'regdate_id', (int)$this->loadedRegdate['regdate_id']
      );
      if (FALSE !== $deleted &&
          !empty($this->loadedRegdate['regdate_module_guid'])) {
        $this->databaseDeleteRecord(
          $this->tableDateTrans,
          array(
            'regdate_id' => (int)$this->loadedRegdate['regdate_id'],
            'lng_id' => (int)$this->lngSelect->currentLanguageId,
          )
        );
        include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
        $module = &base_pluginloader::getPluginInstance(
          $this->loadedRegdate['regdate_module_guid'],
          $this,
          $this->loadedRegdate['regdate_moduledata'],
          $this->loadedRegdate['class_time'],
          $this->loadedRegdate['file_time']
        );
        $counter = 0;
        $daysData = array();
        $daysTrans = array();
        if (isset($module) && is_object($module)) {
          if (@is_array($this->regdateIgnores)) {
            foreach ($this->regdateIgnores as $row) {
              $ignores[] = $this->dayOnly($row['regdate_ignoretime']);
            }
          }
          $date = $this->getNextExecute($module, $this->loadedRegdate['regdate_start']);
          while ($date > 0) {
            $day = $this->dayOnly($date);
            if (!@in_array($day, $ignores)) {
              if ((++$counter > $this->loadedRegdate['regdate_max']) || $counter > 60) {
                break;
              }
              if ($this->loadedRegdate['regdate_days'] <= 1) {
                $dayEnd = $day;
              } else {
                $dayEnd = $this->dayOnly(
                  (($this->loadedRegdate['regdate_days'] - 1) * $this->secOfDay) + $day
                );
              }
              if (strlen(trim($this->loadedRegdate['regdate_text'])) > 0) {
                $dateString = $this->loadedRegdate['regdate_text'];
              } elseif ($day < $dayEnd) {
                $dateString = date('Y-m-d', $day).' - '.date('Y-m-d', $dayEnd);
              }

              $daysData[] = array(
                'regdate_id' => (int)$this->loadedRegdate['regdate_id'],
                'date_state' => $this->loadedRegdate['regdate_state'],
                'date_start' => $day,
                'date_end' => $dayEnd,
                'surfer_id' => $this->loadedRegdate['surfer_id'],
                'surfergroup_id' => $this->loadedRegdate['surfergroup_id'],
                'author_id' => ''
              );

              if (!$this->loadedRegdate['regdate_untranslated']) {
                $daysTrans[] = array(
                  'regdate_id' => $this->loadedRegdate['regdate_id'],
                  'date_title' => $this->loadedRegdate['regdate_title'],
                  'date_data' => $this->loadedRegdate['regdate_data'],
                  'date_content_guid' => $this->loadedRegdate['regdate_content_guid'],
                  'date_text' => $this->loadedRegdate['regdate_text'],
                  'lng_id' => $this->lngSelect->currentLanguageId
                );
              }
            }
            $date = $this->getNextExecute($module, $date);
          }
        }
        if (@is_array($daysData) && (count($daysData) > 0)) {
          $done = TRUE;
          $i = 0;
          foreach ($daysData as $oneDay) {
            if ($id = $this->databaseInsertRecord(
                  $this->tableDates, 'date_id', $oneDay)) {
              $daysTrans[$i]['date_id'] = $id;
              $i++;
            } else {
              $done = FALSE;
              break;
            }
          }

          if ($i > 0) {
            return $done = $i;
          }
          if (!$this->loadedRegdate['regdate_untranslated']) {
            return (
              FALSE !== $done ||
              FALSE !== $this->databaseInsertRecords($this->tableDateTrans, $daysTrans)
            );
          }
          return $done;
        }
        return 0;
      }
    }
    return FALSE;
  }

  /**
  * Add ignore date
  *
  * @param integer $ignoreId
  * @param boolean $createTopic optional, default value FALSE
  * @access public
  * @return boolean
  */
  function addIgnoreDate($ignoreId, $createTopic = FALSE) {
    if (isset($this->loadedRegdate) && is_array($this->loadedRegdate)) {
      $sql = "SELECT date_start
                FROM %s
               WHERE date_id = %d ";
      $params = array($this->tableDates, (int)$ignoreId);

      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $date = $this->dayOnly($row['date_start']);
          if ($date > 0) {
            if ($createTopic) {
              $data = array('regdate_id' => 0, 'date_state' => 1);
              if (FALSE !== $this->databaseUpdateRecord(
                    $this->tableDates, $data, 'date_id', (int)$ignoreId)) {
                $result = $ignoreId;
                $newDateId = $ignoreId;
              }
            } else {
              $result = $this->databaseDeleteRecord(
                 $this->tableDates, 'date_id', (int)$ignoreId);
            }
            if ($result) {
              $data = array(
                'regdate_ignoretime' => $date,
                'regdate_id' => (int)$this->loadedRegdate['regdate_id']
              );
              if ($this->databaseInsertRecord(
                    $this->tableRegignore, 'regdate_ignoreid', $data)) {
                $result = TRUE;
              }
            }
          }
        }
      }

      if ($result) {
        $translation = array(
          'date_id' => $ignoreId,
          'date_title' => $this->loadedRegdate['regdate_title'],
          'date_text' => $this->loadedRegdate['regdate_text'],
          'date_data' => $this->loadedRegdate['regdate_data'],
          'date_content_guid' => $this->loadedRegdate['regdate_content_guid'],
          'lng_id' => $this->loadedRegdate['lng_id']
        );

        $this->databaseInsertRecord($this->tableDateTrans, NULL, $translation);
        $this->cloneRegdateTags($this->loadedRegdate['regdate_id'], $newDateId);
        $this->loadDate($newDateId);
        return $result;
      }
    }
    return FALSE;
  }

  /**
  * Delete ignore date
  *
  * @param integer $ignoreId
  * @access public
  * @return mixed FALSE or db_deleteRecord
  */
  function delIgnoreDate($ignoreId) {
    if (@is_array($this->loadedRegdate)) {
      return $this->databaseDeleteRecord(
        $this->tableRegignore, 'regdate_ignoreid', (int)$ignoreId);
    }
    return FALSE;
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

  /**
  * Get next execute
  *
  * @param object base_cronjob &$cronModule
  * @param integer $minTime
  * @access public
  * @return integer
  */
  function getNextExecute(&$cronModule, $minTime) {
    if (@is_object($cronModule)) {
      $nextExecutionTime = $cronModule->getNextDateTime($minTime);
      if (-1 == $this->checkTimeFrame($nextExecutionTime) && 0 < $nextExecutionTime) {
        $i = $cronModule->getNextDateTime($nextExecutionTime);
        if ($nextExecutionTime == $i) {
          break;
        } else {
          $nextExecutionTime = $i;
        }
      }
      if ($nextExecutionTime > 0) {
        switch ($this->checkTimeFrame($nextExecutionTime)) {
        case -1 :
          return -1;
        case  0 :
          return $nextExecutionTime;
        }
      }
    }
    return 0;
  }


  /**
  * Check time frame
  *
  * @param string $dateTime
  * @access public
  * @return integer
  */
  function checkTimeFrame($dateTime) {
    if (@is_array($this->loadedRegdate)) {
      if ($dateTime >= $this->loadedRegdate['regdate_start'] &&
          $dateTime <= $this->loadedRegdate['regdate_end']) {
        return 0;
      } elseif ($dateTime <= $this->loadedRegdate['regdate_start']) {
        return -1;
      }
    }
    return 1;
  }

  /**
  * Update regdate cache
  *
  * @access public
  * @return mixed return value db_updateRecord
  */

  function updateRegdateCache() {
    // action: currently none
    // todo: check load routine
    if (!@$this->loadedRegdate['regdate_id']) {
        return 0;
    }

    return 1;
  }

  /**
  * Show Dates
  *
  * @param string $headString
  * @param boolean $detail optional, default value TRUE
  * @access public
  * @return string
  */
  function showDates($headString, $detail = TRUE) {
    $result = '';
    if (isset($this->dates) && is_array($this->dates)) {
      if (!$detail) {
        $result .= sprintf(
          '<listview title="%s">'.LF,
          papaya_strings::escapeHTMLChars($headString)
        );
        $result .= '<cols>';
        $result .= sprintf(
          '<col>%s</col>',
          papaya_strings::escapeHTMLChars($this->_gt('Title'))
        );
        $result .= sprintf(
          '<col align="center">%s</col>',
          papaya_strings::escapeHTMLChars($this->_gt('Day'))
        );
        $result .= '</cols>';

        $result .= '<items>';
        foreach ($this->dates as $key => $row) {
          $selected = '';
          $imageIdx = ($row['date_state'] == 2) ? 'items-date' : 'status-date-disabled';
          if ((
               isset($row['date_id']) &&
               isset($this->params['date']) &&
               $row['date_id'] == (int)$this->params['date'] &&
               (int)$this->params['date'] > 0
             ) || (
               isset($row['regdate_id']) &&
               isset($this->params['regdate']) &&
               $row['regdate_id'] == (int)@$this->params['regdate']  &&
              (int)@$this->params['regdate'] > 0
            )) {
            $selected = ' selected="selected"';
          }

          if (isset($this->params['date']) &&
              (int)$this->params['date'] == (int)$row['date_id']) {
            $selected = ' selected="selected"';
          }

          if ($row['regdate_id']) {
            if ($row['date_state'] == 2) {
              $imageIdx = 'regdate-published';
            } else {
              $imageIdx = 'regdate-unpublished';
            }
          } else {
            if ($row['date_state'] == 2) {
              $imageIdx = 'date-published';
            } else {
              $imageIdx = 'date-unpublished';
            }
          }

          if (isset($row['date_title']) && strlen(trim($row['date_title'])) > 0) {
            $itemTitle = $row['date_title'];
          } elseif (isset($this->alternativeDateTitles[$key]) &&
                    strlen(trim($this->alternativeDateTitles[$key])) > 0) {
            $itemTitle = '['.$this->alternativeDateTitles[$key].']';
          } else {
            $itemTitle = '['.$this->_gt('No title').']';
          }

          $result .= sprintf(
            '<listitem title="%s" href="%s" image="%s" %s>'.
              '<subitem align="center">%d</subitem>'.LF,
            papaya_strings::escapeHTMLChars($itemTitle),
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array(
                  'cmd' => 'date',
                  'date' => $row['date_id']
                )
              )
            ),
            papaya_strings::escapeHTMLChars($this->localImages[$imageIdx]),
            $selected,
            date('d', $row['date_start'])
          );
          $result .= '</listitem>'.LF;
        }
        $result .= '</items>';
        $result .= '</listview>'.LF;
      } else {
        $result = base_calendar::showDates($headString, $detail);
      }
    }
    return $result;
  }

  /**
  * Show regular Dates
  *
  * @access public
  * @return string
  */
  function showRegdateDates() {
    $result = '';
    if (isset($this->regdates) && is_array($this->regdates)) {
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Regular dates'))
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Title'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Dates'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->regdates as $row) {
        $imageIdx = ($row['regdate_count'] > 0)
          ? 'regdate-published' : 'regdate-unpublished';
        $selected = ($row['regdate_id'] == @$this->params['regdate'])
          ? ' selected="selected"' : '';
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s" %s>'.LF,
          papaya_strings::escapeHTMLChars($row['regdate_title']),
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array(
                'cmd' => 'regdate',
                'regdate' => $row['regdate_id']
              )
            )
          ),
          papaya_strings::escapeHTMLChars($this->localImages[$imageIdx]),
          $selected
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>',
          papaya_strings::escapeHTMLChars($row['regdate_count'])
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>';
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * Show list
  *
  * @access public
  * @return string
  */
  function showList() {
    $this->loadAlternativeDateTitles();
    $this->getDaysOfMonth(FALSE);
    return $this->showMonth(FALSE);
  }

  /**
  * It is nice to have alternative date titles in the content language if a direct
  * translation of a date does not exist.
  * $this->alternativeDateTitles is used to store alternative titles.
  *
  * @access private
  * @return boolean
  */
  function loadAlternativeDateTitles() {
    unset($this->alternativeDateTitles);
    $this->alternativeDateTitles = array();
    $untranslatedDateIds = array();

    // get untranslated date ids
    if (isset($this->dates) &&
        is_array($this->dates)) {
      foreach ($this->dates as $key => $date) {
        if ($date['date_untranslated'] == 1) {
          $untranslatedDateIds[] = $key;
        }
      }
    }

    if (count($untranslatedDateIds) > 0) {
      $untranslatedDatesCondition = $this->databaseGetSqlCondition(
        'date_id', $untranslatedDateIds
      );
      $sql = "SELECT date_id, date_title
                  FROM %s AS dt
                 WHERE lng_id = %d";
      $params = array(
        $this->tableDateTrans,
        PAPAYA_CONTENT_LANGUAGE
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->alternativeDateTitles[$row['date_id']] = $row['date_title'];
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Show data of an date and makes changes to it when the user
  * requests this.
  *
  * @access public
  * @return string
  */
  function showData() {
    $results = '';
    if ($this->checkPerm(2, FALSE) &&
        !(isset($this->params['cmd']) && $this->params['cmd'] == 'addreg')) {
      if (isset($this->loadedDate) && is_array($this->loadedDate)) {
          $results = $this->showDateEdit();
      }
    }
    if ($this->checkPerm(3, FALSE) &&
        !(isset($this->params['cmd']) && $this->params['cmd'] == 'add')) {
      if (isset($this->loadedRegdate) && is_array($this->loadedRegdate)) {
        $results = $this->showRegdateEdit();
      }
    }
    return $results;
  }

  /**
  * Surfer-Filter-Form.
  * Because a surfer list may soon become far too large to be explored
  * by a human being, it has a filter mechanism to prdate too much
  * search results from being printed into the browser.
  *
  * @access public
  */
  function surfersFilter() {
    $result = "";
    $result .= sprintf(
      '<dialog title="%s" action="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Filter')),
      papaya_strings::escapeHTMLChars($this->surfersObj->baseLink)
    );
    $result .= '<lines class="dialogMedium">'.LF;
    $result .= sprintf(
      '<line caption="%s" hint="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Surferfilter')),
      papaya_strings::escapeHTMLChars($this->_gt('* as wildcard'))
    );
    $result .= sprintf(
      '<input type="text" class="dialogInput dialogScale" name="%s[patt]" '.
        'value="%s" /></line>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars(
        empty($this->params['patt']) ? '' : $this->params['patt']
      )
    );
    $result .= '</lines>'.LF;
    $result .= sprintf(
      '<dlgbutton value="%s" />'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Show'))
    );
    $result .= '</dialog>'.LF;

    return $result;
  }

  /**
  * Collaborates a surfer-admin object which is used to allow
  * surfer selection for calendar-[regulars and Dates]
  *
  * @access public
  * @return none
  */
  function collaborateSurferAdmin() {
    if (!isset($this->surfersObj)) {

      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $this->surfersObj = &base_pluginloader::getPluginInstance(
        '06648c9c955e1a0e06a7bd381748c4e4', $this
      );
      $this->surfersObj->module = &$this;
      $this->surfersObj->images = &$this->images;
      $this->surfersObj->msgs = &$this->msgs;
      $this->surfersObj->layout = &$this->layout;
      $this->surfersObj->authUser = &$this->authUser;
      $this->surfersObj->listLength = 10;

      /**
      * @todo searching for surfers by a pattern
      */
      $this->surferList = $this->surfersObj->searchSurfers(@$this->params['patt']);
      $this->addDateOwnerToSurfersList(@(string)$this->loadedDate['surfer_id']);
    }
  }

  /**
  * Adds the date owner to surfers list
  *
  * @param string $ownerId surfer id of date owner
  * @access private
  */
  function addDateOwnerToSurfersList($ownerId) {
    if ($owner = $this->surfersObj->loadSurfer($ownerId)) {
      $this->surferList[$ownerId] = $owner;
    }
  }

  /**
  * Get buttons
  *
  * @access public
  */
  function getButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;

    $toolbar->addButton(
      'Today',
      $this->getLink(
        array('time' => (string)time())
      ),
      'items-date',
      'Goto current date.',
      FALSE
    );
    if ($this->checkPerm(2, FALSE)) {
      $toolbar->addButton(
        $this->tplAddLink,
        $this->getLink(array('cmd' => 'add')),
        $this->localImages['date-add'],
        '',
        FALSE
      );
    }
    if ($this->checkPerm(3, FALSE)) {
      $toolbar->addButton(
        $this->tplAddLinkReg,
        $this->getLink(array('cmd' => 'addreg', 'mode' => '0')),
        $this->localImages['regdate-add'],
        '',
        FALSE
      );
    }
    if (isset($this->dateId) && ($this->dateId > 0)
        && (!@is_array($this->loadedRegdate) && $this->checkPerm(2, FALSE))) {
      $toolbar->addSeperator();
      $toolbar->addButton(
        $this->tplCopyLink,
        $this->getLink(array('cmd' => 'copy', 'date' => $this->dateId)),
        'actions-edit-copy',
        '',
        FALSE
      );
      $toolbar->addSeperator();
      $toolbar->addButton(
        $this->tplDelLink,
        $this->getLink(
          array('cmd' => 'del', 'date' => $this->dateId)
        ),
        $this->localImages['date-delete'],
        '',
        FALSE
      );
    }
    if ($this->checkPerm(3, FALSE)) {
      if (@is_array($this->loadedRegdate) &&
          $this->loadedRegdate['regdate_module_guid']) {
        $toolbar->addSeperator();
        $toolbar->addButton(
          'Create dates',
          $this->getLink(
            array(
              'cmd' => 'createcache',
              'regdate' => $this->loadedRegdate['regdate_id']
            )
          ),
          $this->localImages['dates-spawn'],
          'Create single dates for a regulary date',
          FALSE
        );
        $toolbar->addSeperator();
      }
      if (isset($this->loadedRegdate) && is_array($this->loadedRegdate)) {
        $toolbar->addButton(
          $this->tplDelLink,
          $this->getLink(
            array(
              'cmd' => 'delreg',
              'regdate' => $this->loadedRegdate['regdate_id']
            )
          ),
          $this->localImages['regdate-delete'],
          '',
          FALSE
        );
      }
    }
    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(
        sprintf('<menu ident="edit">%s</menu>'.LF, $str)
      );
    }
  }

  /**
  * Get toolbar
  *
  * @access public
  */
  function getToolbar() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;

    $showToolbar = FALSE;
    if (isset($this->loadedRegdate) && is_array($this->loadedRegdate) &&
        $this->checkPerm(3, TRUE)) {
      $showToolbar = TRUE;
      $toolbar->addButton(
        'Properties',
        $this->getLink(
          array(
            'mode' => 0,
            'cmd' => 'regdate',
            'regdate' => (int)$this->loadedRegdate['regdate_id']
          )
        ),
        'categories-properties',
        '',
        empty($this->params['mode']) || $this->params['mode'] == 0
      );
      $toolbar->addButton(
        'Content',
        $this->getLink(
          array(
            'mode' => 3,
            'cmd' => 'regdate',
            'regdate' => (int)$this->loadedRegdate['regdate_id']
          )
        ),
        'categories-content',
        '',
        isset($this->params['mode']) && $this->params['mode'] == 3
      );
      $toolbar->addButton(
        'Time',
        $this->getLink(
          array(
            'mode' => 1,
            'cmd' => 'regdate',
            'regdate' => (int)$this->loadedRegdate['regdate_id']
          )
        ),
        'items-time',
        'Time configuration',
        isset($this->params['mode']) && $this->params['mode'] == 1
      );
      $toolbar->addButton(
        'Date list',
        $this->getLink(
          array(
            'mode' => 2,
            'cmd' => 'regdate',
            'regdate' => (int)$this->loadedRegdate['regdate_id']
          )
        ),
        $this->localImages['dates-table'],
        '',
        isset($this->params['mode']) && $this->params['mode'] == 2
      );
      $toolbar->addButton(
        'Tags',
        $this->getLink(
          array(
            'mode' => 4,
            'cmd' => 'regdate',
            'regdate' =>  (int)$this->loadedRegdate['regdate_id']
          )
        ),
        'items-tag',
        '',
        isset($this->params['mode']) && $this->params['mode'] == 4
      );
      $toolbar->addSeparator();
      if ($this->checkPerm(5, FALSE)) {
        $toolbar->addButton(
          'Access',
          $this->getLink(
            array(
              'mode' => 5,
              'cmd' => 'regdate',
              'regdate' =>  (int)$this->loadedRegdate['regdate_id']
            )
          ),
          'categories-access',
          '',
          isset($this->params['mode']) && $this->params['mode'] == 5
        );
      }
    } elseif ((!(isset($this->loadedRegdate) && is_array($this->loadedRegdate))) &&
              isset($this->loadedDate) && is_array($this->loadedDate) &&
              $this->checkPerm(2, TRUE)) {

      $showToolbar = TRUE;
      $toolbar->addButton(
        'Properties',
        $this->getLink(
          array(
            'mode' => 0,
            'cmd' => 'date',
            'date' => (int)$this->selectedDateId
          )
        ),
        'categories-properties',
        '',
        empty($this->params['mode']) || $this->params['mode'] == 0
      );
      $toolbar->addButton(
        'Content',
        $this->getLink(
          array(
            'mode' => 3,
            'cmd' => 'date',
            'date' => (int)$this->selectedDateId
          )
        ),
        'categories-content',
        '',
        isset($this->params['mode']) && $this->params['mode'] == 3
      );

      $toolbar->addButton(
        'Tags',
        $this->getLink(
          array(
            'mode' => 4,
            'cmd' => 'date',
            'date' =>  (int)$this->selectedDateId
          )
        ),
        'items-tag',
        '',
        isset($this->params['mode']) && $this->params['mode'] == 4
      );
      $toolbar->addSeperator();
      if ($this->checkPerm(5, FALSE)) {
        $toolbar->addButton(
          'Access',
          $this->getLink(
            array(
              'mode' => 5,
              'cmd' => 'date',
              'date' =>  (int)$this->selectedDateId
            )
          ),
          'categories-access',
          '',
          isset($this->params['mode']) && $this->params['mode'] == 5
        );
      }
    }
    if ($showToolbar !== FALSE) {
      if ($str = $toolbar->getXML()) {
        $this->layout->add(sprintf('<toolbar>%s</toolbar>'.LF, $str));
      }
    }
  }

  /**
  * initialize the regdate properties dialog.
  *
  * @param $data array, information to fill the form with.
  */
  function initializeRegdatePropertiesDialog($data) {
    if (!isset($this->regdatePropertiesDialog) &&
        !is_object($this->regdatePropertiesDialog)) {
      if (!$this->checkPerm(4, FALSE) && isset($data['regdate_state'])) {
        $data['regdate_state'] = 1;
      }
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array('cmd' => 'edit', 'save' => 1, 'regdate' => $data['regdate_id']);
      $this->regdatePropertiesDialog = new base_dialog(
        $this, $this->paramName, $this->regdateFields, $data, $hidden
      );
      $this->regdatePropertiesDialog->msgs = &$this->msgs;
      $this->regdatePropertiesDialog->loadParams();
      $this->regdatePropertiesDialog->inputFieldSize = $this->inputFieldSize;
      $this->regdatePropertiesDialog->baseLink = $this->baseLink;
      $this->regdatePropertiesDialog->dialogTitle = $this->_gt('Content');
      $this->regdatePropertiesDialog->dialogDoubleButtons = TRUE;
    }
  }

  /**
  * initialize the date properties dialog.
  *
  * @param $data array, information to fill the form with.
  */
  function initializeDatePropertiesDialog($data) {
    if (!isset($this->datePropertiesDialog) && !is_object($this->datePropertiesDialog)) {
      if (!$this->checkPerm(4, FALSE) && isset($data['date_state'])) {
        $data['date_state'] = 1;
      }
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array(
        'cmd' => 'edit',
        'save' => 1,
        'date' => empty($data['date_id']) ? '' : (string)$data['date_id'],
        'time' => empty($data['date_id']) ? '' : $this->params['time'],
        'mode' => 0
      );
      $this->datePropertiesDialog = new base_dialog(
        $this, $this->paramName, $this->dateFields, $data, $hidden
      );
      $this->datePropertiesDialog->msgs = &$this->msgs;
      $this->datePropertiesDialog->loadParams();
      $this->datePropertiesDialog->inputFieldSize = $this->inputFieldSize;
      $this->datePropertiesDialog->baseLink = $this->baseLink;
      $this->datePropertiesDialog->dialogTitle = $this->_gt('Content');
      $this->datePropertiesDialog->dialogDoubleButtons = TRUE;
    }
  }

  /**
  * A wrapper to showDateEdit and showRegdateEdit implemented for compatibility reasons.
  */
  function showEdit() {
    if (isset($this->loadedDate) && is_array($this->loadedDate)) {
      if (isset($this->loadedRegdate) && is_array($this->loadedRegdate)) {
        return $this->showRegdateEdit($this->loadedRegdate);
      } else {
        if ($this->params['mode'] == 0) {
          return $this->showDateEdit($this->loadedDate);
        }
      }
    }

    return $this->showDateEdit($data);
  }

  /**
  * Show the date edit dialogs when it has been created allready,
  * depending on the current mode.
  */
  function showDateEdit() {
    if (@$this->loadedDate['date_untranslated'] >= 2 &&
        @$this->params['cmd'] != 'transdate') {
      return($this->getAddDateTranslationDialog());
    } else {
      if (@$this->params['cmd'] != 'del') {
        switch ($this->params['mode']) {
        case 3:
          if (!empty($this->loadedDate['date_content_guid']) ||
              !empty($this->loadedRegdate['regdate_content_guid'])) {
            return($this->getContentModuleEdit(FALSE));
          } else {
            $this->addMsg(
              MSG_INFO,
              $this->_gt(
                'No content module selected! Please select a content module first.'
              )
            );
          }
          break;
        case 4:
          $this->layout->add(
            $this->tags->getTagLinker(
              'date',
              empty($this->loadedDate['date_id']) ? 0 : $this->loadedDate['date_id']
            )
          );
          break;
        case 5:
          if ($this->checkPerm(5, FALSE)) {
            $this->collaborateSurferAdmin();
            return $this->surfersFilter().$this->showOwnerEdit($this->loadedDate, 'date');
          }
          break;
        default:
          if (!isset($this->datePropertiesDialog) ||
              !is_object($this->datePropertiesDialog)) {
            $this->initializeDatePropertiesDialog(
             $this->prepareDatePropertiesInput($this->loadedDate));
          }
          return $this->datePropertiesDialog->getDialogXML();
        }
      }
    }
  }

  /**
  * Show regular edit
  *
  * @access public
  * @return string
  */
  function showRegdateEdit() {
    if (isset($this->loadedRegdate['regdate_untranslated']) &&
        $this->loadedRegdate['regdate_untranslated'] >= 2 &&
        isset($this->params['cmd']) &&
        $this->params['cmd'] != 'transreg') {
      return($this->getAddRegdateTranslationDialog());
    } else {
      if (!isset($this->params['cmd']) ||
          $this->params['cmd'] != 'delreg') {
        switch ($this->params['mode']) {
        case 1:
          if ($this->loadedRegdate['regdate_module_guid']) {
            return($this->getCronModuleEdit());
          } else {
            $this->addMsg(
              MSG_INFO,
              $this->_gt('No time module selected! Please select a time module first.')
            );
          }
          break;
        case 2:
          $this->loadRegdateCache();
          if (isset($this->cacheDates) &&
              is_array($this->cacheDates) &&
              count($this->cacheDates) > 0) {
            return($this->getRegdateCacheList());
          } else {
            $this->addMsg(
              MSG_INFO,
              $this->_gt(
                'No single dates have been created yet.'.
                ' Please click "create dates" to create single dates from'.
                ' this regular calendar entry.'
              )
            );
          }
          break;
        case 3:
          if ($this->loadedRegdate['regdate_content_guid']) {
            return($this->getContentModuleEdit(TRUE));
          } else {
            $this->addMsg(
              MSG_INFO,
              $this->_gt(
               "No content module selected! Please select a content module first."
              )
            );
          }
          break;
        case 4:
          $this->layout->add(
            $this->tags->getTagLinker(
              'regdate', $this->loadedRegdate['regdate_id']
            )
          );
          break;
        case 5:
          if ($this->checkPerm(5, FALSE)) {
            $this->collaborateSurferAdmin();
            if ($row = $this->loadRegdate($this->params['regdate'])) {
              return (
                $this->surfersFilter().$this->showOwnerEdit($this->loadedRegdate, 'regdate')
              );
            }
          }
          break;
        default:
          if (!isset($this->regdatePropertiesDialog) ||
              !is_object($this->regdatePropertiesDialog)) {
            $this->initializeRegdatePropertiesDialog($this->loadedRegdate);
          }

          return ($this->regdatePropertiesDialog->getDialogXML());
        }
      }
    }
  }


  /**
  * Show form to edit surfer/surfergroups.
  *
  * @param array $data
  * @param $type 'date' or 'regdate'
  * @access public
  * @return string
  */
  function showOwnerEdit($data, $type) {
    if (isset($this->msg) && $this->msg) {
      $this->addMsg($this->msgType, $this->msg);
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');

    switch ($type) {
    case 'date':
      $hidden = array('cmd' => 'edit', 'save' => 1, 'date' => @(string)$data['date_id']);
      break;
    case 'regdate':
      $hidden =
        array('cmd' => 'edit', 'save' => 1, 'regdate' => @(string)$data['regdate_id']);
      break;
    }

    $this->dialog = new base_dialog(
      $this, $this->paramName, $this->ownerFields, $data, $hidden
    );
    $this->dialog->msgs = &$this->msgs;
    $this->dialog->loadParams();
    $this->dialog->inputFieldSize = $this->inputFieldSize;
    $this->dialog->baseLink = $this->baseLink;
    $this->dialog->dialogTitle = $this->_gt('Owner');
    $this->dialog->dialogDoubleButtons = TRUE;
    return $this->dialog->getDialogXML();
  }

  /**
  * Get combo time modules
  *
  * @param string $paramName
  * @param $field
  * @param integer $value
  * @access public
  * @return string
  */
  function getComboTimeModules($paramName, $field, $value) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($paramName)
    );
    if (isset($this->cronModules) && is_array($this->cronModules)) {
      $result .= sprintf(
        '<option value="0">[%s]</option>',
        papaya_strings::escapeHTMLChars($this->_gt('Please select'))
      );
      foreach ($this->cronModules as $moduleGuid => $module) {
        $selected = ($moduleGuid == $value) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>',
          papaya_strings::escapeHTMLChars($moduleGuid),
          $selected,
          papaya_strings::escapeHTMLChars($module['module_title'])
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  /**
  * Get combo content modules
  *
  * @param string $paramName
  * @param array $field
  * @param integer $value
  * @access public
  * @return string
  */
  function getComboContentModules($paramName, $field, $value) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($paramName)
    );
    if (isset($this->contentModules) && is_array($this->contentModules)) {
      $result .= sprintf(
        '<option value="0">[%s]</option>',
        $this->_gt('Please select')
      );
      foreach ($this->contentModules as $moduleGuid => $module) {
        $selected = ($moduleGuid == $value) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>',
          papaya_strings::escapeHTMLChars($moduleGuid),
          $selected,
          papaya_strings::escapeHTMLChars($module['module_title'])
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  /**
  * Get combo languages
  *
  * @param string $paramName
  * @param array $field
  * @param integer $value
  * @access public
  * @return string
  */
  function getComboLanguages($paramName, $field, $value) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($paramName)
    );

    if (isset($this->lngSelect) && is_object($this->lngSelect)) {
      $result .= sprintf(
        '<option value="0">[%s]</option>',
        papaya_strings::escapeHTMLChars($this->_gt('Please select'))
      );

      foreach ($this->lngSelect->languages as $language) {
        $selected = ($language['lng_id'] == $value) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>',
          papaya_strings::escapeHTMLChars($language['lng_id']),
          $selected,
          papaya_strings::escapeHTMLChars($language['lng_title'])
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  /**
  * Get combo with loaded surfergroups.
  *
  * @param string $paramName
  * @param array $field
  * @param integer $value
  * @access public
  * @return string
  */
  function getComboSurfergroups($paramName, $field, $value) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($paramName)
    );
    if (isset($this->surferGroups) && is_array($this->surferGroups)) {
      $result .= sprintf(
        '<option value="0">[%s]</option>',
        papaya_strings::escapeHTMLChars($this->_gt('Please select'))
      );
      foreach ($this->surferGroups as $surferGroup) {
        $selected = ($surferGroup['surfergroup_id'] == $value)
          ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>',
          papaya_strings::escapeHTMLChars($surferGroup['surfergroup_id']),
          $selected,
          papaya_strings::escapeHTMLChars($surferGroup['surfergroup_title'])
        );
      }
    } else {
      $result .= sprintf(
        '<option value="0">[%s]</option>',
        papaya_strings::escapeHTMLChars($this->_gt('No surfer group available'))
      );
    }
    $result .= '</select>';
    return $result;
  }

  /**
  * Get combo with loaded surferlist.
  *
  * @param string $paramName
  * @param array $field
  * @param integer $value
  * @access public
  * @return string
  */
  function getComboSurfers($paramName, $field, $value) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($paramName)
    );
    if (isset($this->surferList) && is_array($this->surferList)) {
      $result .= sprintf(
        '<option value="0">[%s]</option>',
        papaya_strings::escapeHTMLChars($this->_gt('Please select'))
      );
      foreach ($this->surferList as $surfer) {
        $surferHandle = (
          isset($surfer['surfer_handle']) && strlen($surfer['surfer_handle']) > 0
        ) ? $surfer['surfer_handle'] : $this->_gt('No handle');
        $surferDesc = (
          isset($surfer['surfer_givenname']) &&
          strlen($surfer['surfer_givenname']) > 0 &&
          isset($surfer['surfer_surname']) &&
          strlen($surfer['surfer_surname']) > 0
        ) ? $surfer['surfer_surname'].', '.$surfer['surfer_givenname']
          : '['.$surferHandle.']';
        $selected = ($surfer['surfer_id'] == $value) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s" %s>%s</option>',
          papaya_strings::escapeHTMLChars($surfer['surfer_id']),
          $selected,
          papaya_strings::escapeHTMLChars($surferDesc)
        );
      }
    } else {
      $result .= sprintf(
        '<option value="0">[%s]</option>',
        papaya_strings::escapeHTMLChars($this->_gt('No surfer available'))
      );
    }
    $result .= '</select>';
    return $result;
  }

  /**
  * Load list of surfer groups into an array.
  *
  * @access public
  */
  function loadSurferGroups() {
    if (!isset($this->surferObj) || !is_object($this->surferObj)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
      $this->surferObj = new base_surfer;
    }
    if ($this->surferObj->loadSurferGroupsList()) {
      $this->surferGroups = $this->surferObj->surferGroups;
    }
  }

  /**
  * Get delete regdate dialog
  *
  * @access public
  * @return string
  */
  function getDeleteRegdateDialog() {
    if (isset($this->loadedRegdate) &&
        is_array($this->loadedRegdate)) {
      $result = sprintf(
        '<msgdialog action="%s" type="question">',
        papaya_strings::escapeHTMLChars($this->baseLink)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="delreg" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[delconfirm]" value="1" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[regdate]" value="%d" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        (int)$this->loadedRegdate['regdate_id']
      );
      $result .= '<message>';
      $result .= papaya_strings::escapeHTMLChars(
        sprintf(
          $this->_gt('Really delete this regular date "%s (%s)"?'),
          $this->loadedRegdate['regdate_title'],
          (int)$this->loadedRegdate['regdate_id']
        )
      );
      $result .= '</message>';
      $result .= sprintf(
        '<dlgbutton value="%s" />'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Delete'))
      );
      $result .= '</msgdialog>';
      return $result;
    }
    return '';
  }

  /**
  * The 'create-translation?' dialog for regular dates.
  */
  function getAddRegdateTranslationDialog() {
    if (isset($this->loadedRegdate) && is_array($this->loadedRegdate)) {
      $result = sprintf(
        '<msgdialog action="%s" type="question">',
        papaya_strings::escapeHTMLChars($this->baseLink)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="transreg" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[transreg_confirm]" value="1" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[regdate]" value="%d" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        (int)$this->loadedRegdate['regdate_id']
      );
      $result .= '<message>';
      $result .= papaya_strings::escapeHTMLChars(
        sprintf(
          $this->_gt(
            'No translation for this regular date (%s) is set currently for'.
            ' the %s language. Should it be created?'
          ),
          (int)$this->loadedRegdate['regdate_id'],
          $this->lngSelect->currentLanguage['lng_title']
        )
      );
      $result .= '</message>';
      $result .= sprintf(
        '<dlgbutton value="%s" />',
        papaya_strings::escapeHTMLChars($this->_gt('Create'))
      );
      $result .= '</msgdialog>';
      return $result;
    }
    return '';
  }

  /**
  * The 'create-translation?' dialog for dates.
  */
  function getAddDateTranslationDialog() {
    if (@is_array($this->loadedDate)) {
      $result = sprintf(
        '<msgdialog action="%s" type="question">',
        papaya_strings::escapeHTMLChars($this->baseLink)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[cmd]" value="transdate" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[transdate_confirm]" value="1" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<input type="hidden" name="%s[date]" value="%d" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        (int)$this->loadedDate['date_id']
      );
      $result .= '<message>';
      $result .= papaya_strings::escapeHTMLChars(
        sprintf(
          $this->_gt(
            'No translation for this date (%s) is set'.
            ' currently for the %s language. Should it be created?'
          ),
          (int)$this->loadedDate['date_id'],
          $this->lngSelect->currentLanguage['lng_title']
        )
      );
      $result .= '</message>';
      $result .= sprintf(
        '<dlgbutton value="%s" />',
        papaya_strings::escapeHTMLChars($this->_gt('Create'))
      );
      $result .= '</msgdialog>';
      return $result;
    }
    return '';
  }

  /**
  * Prepare input
  *
  * @param array $row
  * @access public
  * @return array
  */
  function prepareDatePropertiesInput($row) {
    $data = $row;
    if (isset($this->params) && is_array($this->params)) {
      if (isset($this->params['date_title'])) {
        $data['date_title'] = $this->params['date_title'];
      }
      if (isset($this->params['date_text'])) {
        $data['date_text'] = $this->params['date_text'];
      }

      if (isset($this->params['date_state'])) {
        $data['date_state'] = (int)$this->params['date_state'];
      }
      if (isset($this->params['date_content_guid'])) {
        $data['date_content_guid'] = $this->params['date_content_guid'];
      }
      if (isset($this->params['date_startf'])) {
        if ($this->params['date_startf'] == '') {
          $data['date_start'] = time();
        } else {
          $date = explode('-', $this->params['date_startf']);
          $data['date_start'] = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        }
      }
      $data['date_startf'] = date('Y-m-d', @(int)$row['date_start']);
      if (isset($this->params['date_endf'])) {
        $date = explode('-', $this->params['date_endf']);
        $data['date_end'] = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        if ($data['date_end'] < $data['date_start']) {
          $data['date_end'] = $data['date_start'];
        }
      }

      $data['date_endf'] = date('Y-m-d', @$data['date_end']);
      return $data;
    } else {
      return $row;
    }
  }

  /**
  * Prepare input
  *
  * @param array $row
  * @access public
  * @return array
  */
  function prepareRegdatePropertiesInput($row) {
    $data = $row;
    if (isset($this->params) && is_array($this->params)) {
      if (isset($this->params['regdate_title'])) {
        $data['regdate_title'] = $this->params['regdate_title'];
      }
      if (isset($this->params['regdate_text'])) {
        $data['regdate_text'] = $this->params['regdate_text'];
      }

      if (isset($this->params['regdate_state'])) {
        $data['regdate_state'] = (int)$this->params['regdate_state'];
      }
      if (isset($this->params['regdate_content_guid'])) {
        $data['regdate_content_guid'] = $this->params['regdate_content_guid'];
      }
      if (isset($this->params['regdate_startf'])) {
        if ($this->params['regdate_startf'] == '') {
          $data['regdate_start'] = time();
        } else {
          $date = explode('-', $this->params['regdate_startf']);
          $data['regdate_start'] = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        }
      }
      $data['regdate_startf'] = date('Y-m-d', $row['regdate_start']);
      if (isset($this->params['regdate_endf'])) {
        $date = explode('-', $this->params['regdate_endf']);
        $data['regdate_end'] = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
        if ($data['regdate_end'] < $data['regdate_start']) {
          $data['regdate_end'] = $data['regdate_start'];
        }
      }

      $data['regdate_endf'] = date('Y-m-d', @$data['regdate_end']);
      return $data;
    } else {
      return $row;
    }
  }


  /**
  * Check input
  *
  * @param array $data
  * @access public
  * @return boolean
  */
  function checkDateInput($data) {
    $result = TRUE;
    if (!checkit::isNoHTML($data['date_title'], TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('Title')
        )
      );
      $result = FALSE;
    }
    if (!checkit::isNoHTML($data['date_text'], TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('Date (text)')
        )
      );
      $result = FALSE;
    }
    if (!checkit::check($data['date_startf'], '/^\d{4}\-\d{2}\-\d{2}$/', TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('From')
        )
      );
      $result = FALSE;
    }
    if (!checkit::check($data['date_endf'], '/^\d{4}\-\d{2}\-\d{2}$/', TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('To')
        )
      );
      $result = FALSE;
    }
    if (!checkit::check($data['date_content_guid'], '/^[a-fA-F\d]{32}$/', TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('Module')
        )
      );
      $result = FALSE;
    }
    $data['date_state'] = (int)$data['date_state'];

    return $result;
  }

  /**
  * Check regdate input
  *
  * @access public
  * @return array or FALSE
  */
  function checkRegdateInput() {
    $result = TRUE;
    $caclDates = FALSE;
    $updateDates = FALSE;
    if (!checkit::isNoHTML($this->params['regdate_title'], TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('Title')
        )
      );
      $result = FALSE;
    } else {
      if ($this->params['regdate_title'] != $this->loadedRegdate['regdate_title']) {
        $updateDates = TRUE;
      }
    }
    if (!checkit::isSomeText($this->params['regdate_text'], FALSE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('Date (text)')
        )
      );
      $result = FALSE;
    } elseif (!checkit::filled($this->params['regdate_text'])) {
      $this->addMsg(
        MSG_WARNING,
        sprintf(
          $this->_gt('No input in field "%s". Using default values.'),
          $this->_gt('Date (text)')
        )
      );
      $updateDates = TRUE;
    } else {
      if ($this->params['regdate_text'] != $this->loadedRegdate['regdate_text']) {
        $caclDates = TRUE;
      }
    }
    if (!checkit::isNum($this->params['regdate_days'], TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('Days')
        )
      );
      $result = FALSE;
    } else {
      if ($this->params['regdate_days'] != $this->loadedRegdate['regdate_days']) {
        $caclDates = TRUE;
      }
    }
    if (!checkit::isISODate($this->params['regdate_start_iso'], TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('From')
        )
      );
      $result = FALSE;
    } else {
      $this->params['regdate_start'] =
        $this->decodeISODatetimeStr($this->params['regdate_start_iso']);
      if ($this->params['regdate_start_iso'] !=
            $this->loadedRegdate['regdate_start_iso']) {
        $caclDates = TRUE;
      }
    }
    if (!checkit::isISODate($this->params['regdate_end_iso'], TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('To')
        )
      );
      $result = FALSE;
    } elseif ($this->params['regdate_start'] >= (
                $this->params['regdate_end'] = $this->decodeISODatetimeStr(
                  $this->params['regdate_end_iso']
                )
              )
             ) {
      $this->params['regdate_end'] = $this->params['regdate_start'] + 31536000;
      $this->params['regdate_end_iso'] = date('Y-m-d', $this->params['regdate_end']);
      $caclDates = TRUE;
    } else {
      if ($this->params['regdate_end_iso'] != $this->loadedRegdate['regdate_end_iso']) {
        $caclDates = TRUE;
      }
    }
    if (!checkit::isGUID($this->params['regdate_module_guid'], TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('Time module')
        )
      );
      $result = FALSE;
    } else {
      if ($this->params['regdate_module_guid'] !=
            $this->loadedRegdate['regdate_module_guid']) {
        $caclDates = TRUE;
      }
    }
    if (!checkit::isNum($this->params['regdate_max'], TRUE)) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('Maximum')
        )
      );
      $result = FALSE;
    } elseif ($this->params['regdate_max'] > 356) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('Maximum')
        )
      );
      $result = FALSE;
    } elseif ($this->params['regdate_max'] < 1) {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('The input in field "%s" is not correct.'),
          $this->_gt('Maximum')
        )
      );
      $result = FALSE;
    } else {
      if ($this->params['regdate_max'] != $this->loadedRegdate['regdate_max']) {
        $caclDates = TRUE;
      }
    }
    $data['regdate_state'] = @(int)$data['regdate_state'];
    return ($result) ? array('update' => $updateDates, 'replace' => $caclDates) : FALSE;
  }

  /**
  * Get regdate cache list
  *
  * @access public
  * @return string
  */
  function getRegdateCacheList() {
    $result = '';

    if (@is_array($this->cacheDates)) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Date list'))
      );
      $result .= '<cols>';
      $result .= '<col>Zeit</col>';
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Detach'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Delete'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->cacheDates as $id => $row) {
        if ($row['date_start'] < $row['date_end']) {
          $titleDate = date('Y-m-d', $row['date_start']).'-'.
            date('Y-m-d', $row['date_end']);
        } else {
          $titleDate = $this->_gt(
            $this->wDay2Str($this->getWDay($row['date_start']))).', '.
            date('Y-m-d', $row['date_start']);
        }
        $result .= sprintf(
          '<listitem title="%s">',
          papaya_strings::escapeHTMLChars($titleDate)
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s"/></a></subitem>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array('cmd' => 'separate', 'ignore_id' => (int)$id)
            )
          ),
          papaya_strings::escapeHTMLChars($this->images['actions-list-remove'])
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s"/></a></subitem>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array('cmd' => 'ignore', 'ignore_id' => (int)$id)
            )
          ),
          papaya_strings::escapeHTMLChars($this->images['places-trash'])
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
      $result .= $this->getRegdateIgnoreList();
    } else {
      $this->addMsg(
        MSG_INFO,
        $this->_gt("Please click on 'Create dates' to create regular dates.")
      );
    }
    return $result;
  }


  /**
  * Get regular Ignore list
  *
  * @access public
  * @return string
  */
  function getRegdateIgnoreList() {
    $result = '';
    if (isset($this->regdateIgnores) && @is_array($this->regdateIgnores)) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Ignored dates'))
      );
      $result .= '<cols>';
      $result .= sprintf('<col>%s</col>', $this->_gt('Date'));
      $result .= sprintf('<col align="center">%s</col>', $this->_gt('Delete'));
      $result .= '</cols>';
      $result .= '<items>';

      foreach ($this->regdateIgnores as $id => $row) {
        $titleDay = $this->_gt(
          $this->wDay2Str($this->getWDay($row['regdate_ignoretime']))
        );
        $result .= sprintf(
          '<listitem title="%s, %s">',
          papaya_strings::escapeHTMLChars($titleDay),
          date('Y-m-d', $row['regdate_ignoretime'])
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s"/></a></subitem>',
          papaya_strings::escapeHTMLChars(
            $this->getLink(
              array('cmd' => 'delignore', 'ignore_id' => (int)$id)
            )
          ),
          papaya_strings::escapeHTMLChars($this->images['places-trash'])
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
    }
    return $result;
  }

  /**
  * Edit area for time module
  *
  * @access public
  * @return mixed string or FALSE
  */
  function getCronModuleEdit() {
    if (@is_array($this->loadedRegdate)) {
      if ($this->loadedRegdate['regdate_module_guid']) {
        return $this->getModuleEdit(
           $this->loadedRegdate['file_time'],
          $this->loadedRegdate['class_time'],
          $this->loadedRegdate['regdate_module_guid'],
          $this->loadedRegdate['regdate_moduledata'],
          'saveCronData'
        );
      }
    }
    return FALSE;
  }

  /**
  * Edit area for Content modules
  *
  * @param boolean $isRegular optional, default value FALSE
  * @access public
  * @return string
  */
  function getContentModuleEdit($isRegular = FALSE) {
    if ($isRegular && @is_array($this->loadedRegdate)) {
      if ($this->loadedRegdate['regdate_content_guid']) {
        $content = (strpos($this->loadedRegdate['regdate_data'], '<data') === 0)
        ? $this->loadedRegdate['regdate_data']
        : sprintf(
            '<data><data-element name="text">%s</data-element></data>',
            papaya_strings::escapeHTMLChars($this->loadedRegdate['regdate_data'])
          );
        return $this->getModuleEdit(
          $this->loadedRegdate['file_content'],
          $this->loadedRegdate['class_content'],
          $this->loadedRegdate['regdate_content_guid'],
          $content,
          'saveRegdateContent'
        );
      }
    } elseif (!$isRegular &&
              @is_array($this->loadedDate) &&
              @$this->loadedDate['date_content_guid'] &&
              isset($this->contentModules[$this->loadedDate['date_content_guid']])) {

      if ($this->loadedDate['date_content_guid']) {
          $module = $this->contentModules[$this->loadedDate['date_content_guid']];
        $content = (strpos($this->loadedDate['date_data'], '<data') === 0)
          ? $this->loadedDate['date_data']
          : sprintf(
            '<data><data-element name="text">%s</data-element></data>',
            papaya_strings::escapeHTMLChars($this->loadedDate['date_data'])
          );
        return $this->getModuleEdit(
          $module['module_path'].$module['module_file'],
          $module['module_class'],
          $module['module_guid'],
          $content,
          'saveDateContent'
        );
      }
    }
    return FALSE;
  }

  /**
  * Get module Edit
  *
  * @param string $fileName
  * @param string $className
  * @param array $data
  * @param string $saveFunc
  * @access public
  * @return string
  */
  function getModuleEdit($fileName, $className, $guid, $data, $saveFunc) {
    if ($guid) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $moduleObj = &base_pluginloader::getPluginInstance(
        $guid, $this, $data, $className, $fileName);
      if (isset($moduleObj) && is_object($moduleObj)) {
        $moduleObj->initializeDialog();
        if ($moduleObj->modified('save')) {
          if ($moduleObj->checkData()) {
            if ($this->$saveFunc($moduleObj->getData())) {
              $this->addMsg(MSG_INFO, $this->_gt('Changes saved.'));
            } else {
              $this->addMsg(
                MSG_ERROR,
                $this->_gt('Database error! Changes not saved.')
              );
            }
          }
        }
        $result = $moduleObj->getForm();
        return $result;
      }
      return '';
    }
  }

  /**
  * This function stores content of an date into the database.
  * It is the second one of two replacing the old one save();
  *
  * @access public
  * @return boolean
  */
  function saveDateContent($data) {
    $updated = $this->databaseUpdateRecord(
      $this->tableDateTrans,
      array('date_data' => $data),
      array(
        'date_id' => (int)$this->selectedDateId,
        'lng_id' => $this->lngSelect->currentLanguageId
      )
    );
    if (FALSE !== $updated) {
      $this->addMsg(
        MSG_INFO,
        sprintf(
          $this->_gt('Translation of date "%s (%d)" changed.'),
          $this->loadedDate['date_title'],
          (int)$this->loadedDate['date_id']
        )
      );
      return TRUE;
    }
    return FALSE;
  }

}
?>
