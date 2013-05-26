<?php
/**
* Calendar object used for front-end output
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
* @version $Id: output_calendar.php
*/

/**
* Basic class calendar
*/
require_once(dirname(__FILE__)."/base_calendar.php");

/**
* Calendar object used for front-end output
*
* @package Papaya-Modules
* @subpackage Free:Calendar
*/
class output_calendar extends base_calendar {

  /**
  * Surfer object
  * @var object $surferObj base_surfer
  */
  var $surferObj = NULL;

  /**
  * Dafault module guid
  * @var string $defaultModule module guid
  */
  var $defaultModule = NULL;
  /**
  * Default state
  * @var integer $defaultState
  */
  var $defaultState = NULL;

  /**
  * Dates list
  * @var array $dates
  */
  var $dates = NULL;
  /**
  * Date details
  * @var array $date
  */
  var $date = NULL;

  /**
  * Captions
  * @var array $captions
  */
  var $captions = array();
  /**
  * Status messages
  * @var $messages
  */
  var $messages = NULL;
  /**
  * Message to output
  * @var array $messageOutput
  */
  var $messageOutput = NULL;

  /**
   * timemode from content
   * @var int $timemode
   */
  var $timemode = 0;

  /**
   * input errors
   * @var array $inputErrors
   */
  var $errors = NULL;
  /**
  * Error message to output
  * @var array $errorOutput
  */
  var $errorOutput = NULL;

  /**
  * Initialize variables and parameters
  *
  * @param object &$contentObj i.e. content_calendar_editor
  * @access public
  */
  function initialize(&$contentObj) {
    // initialize parameters
    $contentObj->initializeParams();
    $this->contentObj = &$contentObj;
    $this->params = &$contentObj->params;
    $this->paramName = $contentObj->paramName;
    $this->baseLink = $contentObj->baseLink;
    // important variables
    $this->currentLanguageId = $contentObj->parentObj->getContentLanguageId();
    if (isset($contentObj->data['default_module'])) {
      $this->defaultModule = $contentObj->data['default_module'];
    }
    if (isset($contentObj->data['default_state'])) {
      $this->defaultState = $contentObj->data['default_state'];
    }
    if (isset($contentObj->data['time_mode'])) {
      $this->timemode = $contentObj->data['time_mode'];
    }
    if (isset($contentObj->data['modify_published'])) {
      $this->modifyPublished = $contentObj->data['modify_published'];
    }
    // gets surfer object
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();

    // selectedDateId
    if (isset($this->params['date_id']) && (int)$this->params['date_id'] > 0) {
      $this->selectedDateId = (int)$this->params['date_id'];
    }

    // initialize captions
    $this->captions = array(
      'add_date' => @$contentObj->data['caption_add_date'],
      'edit_date' => @$contentObj->data['caption_edit_date'],
      'delete_date' => @$contentObj->data['caption_delete_date'],
      'delete_trans' => @$contentObj->data['caption_delete_trans'],
      'input_title' => @$contentObj->data['caption_input_title'],
      'save_button' => @$contentObj->data['caption_save_button'],
      'input_datetext' => @$contentObj->data['caption_input_datetext'],
      'input_from' => @$contentObj->data['caption_input_from'],
      'input_to' => @$contentObj->data['caption_input_to'],
      'input_state' => @$contentObj->data['caption_input_state'],
      'input_state_created' => @$contentObj->data['caption_input_state_created'],
      'input_state_published' => @$contentObj->data['caption_input_state_published'],
      'input_date_field_hour' => @$contentObj->data['caption_input_date_field_hour'],
      'input_date_field_minute' => @$contentObj->data['caption_input_date_field_minute']
    );

    // initialize messages
    $this->messages = array(
      'save_datecontent' => @$contentObj->data['message_save_datecontent'],
      'save_date' => @$contentObj->data['message_save_date'],
      'new_date' => @$contentObj->data['message_new_date'],
      'delete_date_question' => @$contentObj->data['message_delete_date_question'],
      'delete_date' => @$contentObj->data['message_delete_date'],
      'delete_datetrans_question' =>
        @$contentObj->data['message_delete_datetrans_question'],
      'delete_datetrans' => @$contentObj->data['message_delete_datetrans']
    );

    $this->errors = array(
      'save_datecontent' => @$contentObj->data['error_save_datecontent'],
      'save_date' => @$contentObj->data['error_save_date'],
      'new_date' => @$contentObj->data['error_new_date'],
      'delete_date' => @$contentObj->data['error_delete_date'],
      'delete_datetrans' => @$contentObj->data['error_delete_datetrans'],
      'check_inputs' => @$contentObj->data['error_check_inputs'],
      'fromto_fields' => @$contentObj->data['error_fromto_fields']
    );

    $this->mandatoryFields = array(
      'date_title' => ((@$contentObj->data['mf_title'] == 1) ? TRUE : FALSE),
      'date_startf' => ((@$contentObj->data['mf_startf'] == 1) ? TRUE : FALSE),
      'date_endf' => ((@$contentObj->data['mf_endf'] == 1) ? TRUE : FALSE),
      'date_text' => ((@$contentObj->data['mf_text'] == 1) ? TRUE : FALSE),
      'date_hour' => ((@$contentObj->data['mf_text'] == 1) ? TRUE : FALSE),
      'date_minute' => ((@$contentObj->data['mf_text'] == 1) ? TRUE : FALSE)
    );

    unset($contentObj);
  }

  /**
  * Execute
  *
  * @access public
  */
  function execute() {
    $deleteTranslation = FALSE;
    // check if valid user exists
    if ($this->surferObj->isValid && isset($this->surferObj->surferId) &&
        $this->surferObj->surferId) {
      switch (@$this->params['cmd']) {
      // edit existing date
      case 'edit':
        $this->loadModulesList();
        if (isset($this->params['date_id']) && $this->params['date_id'] > 0 &&
            $this->validateDateId($this->params['date_id'])) {
          if (isset($this->params['save']) && $this->params['save'] == 1) {
            $this->initializeDateDialog('edit', $this->params['date_id']);
            if ($this->dateDialog->checkDialogInput() && $this->checkTime()) {
                $this->saveDate();
            } else {
              $this->errorOutput = $this->errors['check_inputs'];
            }
          }
          $this->loadDateDetailsForSurfer(
            $this->contentObj->params['date_id'],
            $this->surferObj->surferId,
            $this->surferObj->surfer['surfergroup_id']
          );
        }
        break;
      // add new date
      case 'add':
        $this->loadModulesList();
        if (isset($this->params['save']) && $this->params['save'] == 1) {
          $this->initializeDateDialog('add');
          if ($this->dateDialog->checkDialogInput() && $this->checkTime()) {
            $this->saveDate(TRUE);
          } else {
            $this->errorOutput = $this->errors['check_inputs'];
          }
        }
        break;
      // delete only translation
      case 'delete_trans':
        $deleteTranslation = TRUE;
        // delete data and translation
      case 'delete':
        if (isset($this->params['confirm']) && $this->params['confirm'] == 1 &&
            isset($this->params['date_id']) && $this->params['date_id'] > 0) {
          $this->deleteDate($this->params['date_id'], $deleteTranslation);
          unset($this->params['confirm']);
          unset($this->params['date_id']);
        }
        break;
      }
      // load dates for date list depending on surfer id
      $this->loadDatesForSurfer(
        $this->surferObj->surferId, $this->surferObj->surfer['surfergroup_id'], 1
      );
      $this->loadDatesForSurfer(
        $this->surferObj->surferId, $this->surferObj->surfer['surfergroup_id'], 2
      );
    }
  }

  /**
  * Loads dates to array, using surfer id / group to load only surfer's dates
  *
  * @param string $surferId current surfer id
  * @access private
  * @return boolean loaded or failed
  */
  function loadDatesForSurfer($surferId, $surferGroupId, $state = 1) {
    // filter state by param and set corresponding dates array
    $filterState = sprintf('AND c.date_state = %d', $state);
    $datesArray = ($state == 1) ? 'createdDates' : 'publishedDates';
    // set sql statement and parameters
    $sql = "SELECT c.date_id, c.date_state, ct.date_text,
                   c.date_start, c.date_end, ct.date_title
              FROM %s AS c
   LEFT OUTER JOIN %s AS ct ON ct.date_id = c.date_id AND ct.lng_id = %d
             WHERE c.regdate_id = 0
               AND (c.surfer_id = '%s' or c.surfergroup_id = %d) $filterState
             ORDER BY c.date_start";
    $params = array(
      $this->tableDates,
      $this->tableDateTrans,
      $this->currentLanguageId,
      $surferId, $surferGroupId
    );
    // load and set dates array

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $tmpArray = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $tmpArray[$row['date_id']] = $row;
      }
      foreach ($tmpArray as $row) {
        // check if date is untranslated
        $row['date_untranslated'] = 0;
        if (!$row['date_title']) {
          $row['date_untranslated'] = 1;
          $row['date_title'] = $this->tplDefaultNoTranslation;
        }
        $tmpArray[$row['date_id']] = $row;
      }
      $this->$datesArray = $tmpArray;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Loads date details, using surfer id / group to load only surfer's dates
  *
  * @param integer $dateId selected date id
  * @param string $surferId current surfer id
  * @access private
  * @return boolean loaded or failed
  */
  function loadDateDetailsForSurfer($dateId, $surferId, $surferGroupId) {
    // filters state if default state set (optional)
    $filterState = '';
    if ($this->defaultState == 1 || $this->modifyPublished == 0) {
      $filterState = sprintf('AND c.date_state = %d', 1);
    }
    // sql statement and parameters
    $sql = "SELECT c.date_id, c.date_start, c.date_end, c.date_state,
                   ct.date_title, ct.date_data, ct.date_content_guid, ct.date_text
              FROM %s AS c
   LEFT OUTER JOIN %s AS ct ON ct.date_id = c.date_id AND ct.lng_id = %d
             WHERE c.regdate_id = 0
               AND c.date_id = %d
               AND (c.surfer_id = '%s' or c.surfergroup_id = %d) $filterState";
    $params = array(
      $this->tableDates,
      $this->tableDateTrans,
      $this->currentLanguageId,
      $dateId, $surferId, $surferGroupId
    );
    // fetches and prepare data
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['date_untranslated'] = 0;
        $row['date_startf'] = date('Y-m-d', $row['date_start']);
        $row['date_endf'] = date('Y-m-d', $row['date_end']);
        // check if date is untranslated
        if (!$row['date_title']) {
          $row['date_untranslated'] = 1;
        }
        if (!$row['date_data']) {
          $row['date_untranslated']++;
        }
        if (!$row['date_text']) {
          $row['date_untranslated']++;
        }
        $this->date = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Validates date id before saving and deletion
  *
  * @param integer $dateId selected date id
  * @access public
  * @return boolean
  */
  function validateDateId($dateId) {
    if ($this->modifyPublished == 1) {
      // modification allowed? => no more attention
      return TRUE;
    } elseif ($dateId > 0) {
      $sql = "SELECT date_state
                FROM %s
               WHERE regdate_id = 0
                 AND date_id = %d
                 AND surfer_id = '%s'";
      $params = array(
        $this->tableDates, $dateId, $this->surferObj->surferId
      );
      // dates state has to be created, no modification of published dates is allowed
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        if (isset($row['date_state']) && $row['date_state'] == 1) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Saves date
  *
  * @param boolean $newDate optional, default value FALSE add a new date or not
  * @access private
  * @return boolean saved or failed
  */
  function saveDate($newDate = FALSE) {
    $resultId = 0;

    /* status codes:
      -1 = failed
       0 = unchanged
       1 = success */
    $dateAdded = 0;
    $translationAdded = 0;

    $dateChanged = 0;
    $translationChanged = 0;

    // validation for date ids see above
    if ($newDate || (!$newDate && $this->validateDateId($this->params['date_id']))) {
      // convert iso formated times to timestamps
      if (isset($this->params['date_endf']) && $this->params['date_endf'] > 0) {
        $date = explode('-', $this->params['date_endf']);
        $this->params['date_end'] = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
      }
      if (isset($this->params['date_startf']) && $this->params['date_startf'] > 0) {
        $date = explode('-', $this->params['date_startf']);
        $this->params['date_start'] = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
      }

      // checks start and end value
      $t = time() - 86400;
      if ($this->params['date_end'] > $t && $this->params['date_start'] > $t &&
          $this->params['date_end'] >= $this->params['date_start']) {

        if (!isset($this->params['author_id'])) {
          $this->params['author_id'] = '';
        }

        // the state depends on configuration, if no default state is set
        // users can set the state for their own
        $newState = $this->defaultState;
        if ($this->defaultState == 0) {
          $newState = $this->params['date_state'];
        }
        $dataProperties = array(
          'date_start' => $this->params['date_start'],
          'date_end' => $this->params['date_end'],
          'date_state' => $newState,
          'author_id' => $this->params['author_id']
        );

        // add new date
        if ($newDate) {
          // set surfer id as owner
          $dataProperties['surfer_id'] = $this->surferObj->surfer['surfer_id'];
          // inserts date
          $dateId = $this->databaseInsertRecord(
            $this->tableDates, 'date_id', $dataProperties
          );
          // output message
          if ($dateId) {
            $dateAdded = 1;
            $resultId = $dateId;
          } else {
            $dateAdded = -1;
          }
        } else {
          // updates existing date and set output message
          $updated = $this->databaseUpdateRecord(
            $this->tableDates, $dataProperties, 'date_id', $this->params['date_id']
          );
          if (FALSE !== $updated) {
            $dateChanged = 1;
          }

          $sql = "SELECT COUNT(*) AS amount
                    FROM %s
                   WHERE date_id = %d
                     AND lng_id = %d";
          $params = array($this->tableDateTrans, $this->params['date_id'],
            $this->currentLanguageId);

          if ($res = $this->databaseQueryFmt($sql, $params)) {
            $count = $res->fetchRow(DB_FETCHMODE_ASSOC);
          }
        }

        // set translation data
        $dataTranslation = array(
          'date_title' => $this->params['date_title'],
          'date_content_guid' => $this->contentObj->data['default_module'],
          'lng_id' => $this->currentLanguageId
        );

        if (isset($this->timemode) && $this->timemode == 1) {
          if (isset($this->params['date_hour']) && isset($this->params['date_minute']) &&
            $this->params['date_hour'] != '' && $this->params['date_minute'] != '') {
            $dataTranslation['date_text'] =
              $this->params['date_hour'].':'.$this->params['date_minute'];
          } else {
            $dataTranslation['date_text'] = '';
          }
        } else {
          $dataTranslation['date_text'] = @$this->params['date_text'];
        }

        // update existing translation and set output message
        if (isset($count['amount']) && $count['amount'] > 0 &&
            isset($this->params['date_id']) && $this->params['date_id'] > 0) {
          // needs date_id by parameter
          $dataTranslation['date_id'] = $this->params['date_id'];
          $updated = $this->databaseUpdateRecord(
            $this->tableDateTrans,
            $dataTranslation,
            array('date_id' => $this->params['date_id'], 'lng_id' => $this->currentLanguageId)
          );
          if ($updated) {
            $translationChanged = 1;
          }
          // or add new translation and set output message
        } else {
          // change and set some default data to insert a new translation
          $dataTranslation['date_data'] = '';
          $dataTranslation['regdate_id'] = 0;
          $dataTranslation['date_id'] = 0;
          // needs inserted id or date_id parameter
          if ($newDate && isset($dateId) && $dateId > 0) {
            $dataTranslation['date_id'] = $dateId;
          } elseif (isset($this->params['date_id']) && $this->params['date_id'] > 0) {
            $dataTranslation['date_id'] = $this->params['date_id'];
          }
          // valid date id given?
          if ($dataTranslation['date_id'] > 0) {
            // insert new record
            $inserted = $this->databaseInsertRecord(
              $this->tableDateTrans, NULL, $dataTranslation
            );
            if ($inserted) {
              $this->params['date_id'] = $dataTranslation['date_id'];
              $this->params['cmd'] = 'edit';
              $this->loadDateDetailsForSurfer(
                $dateId,
                $this->surferObj->surferId,
                $this->surferObj->surfer['surfergroup_id']
              );
              $translationAdded = 1;
            } else {
              $translationAdded = -1;
            }
          }
        }
      } else {
        $this->errorOutput = $this->errors['fromto_fields'];
      }
    }

    if ($newDate) {
      if ($dateAdded == 1 && $translationAdded == 1 && $resultId > 0) {
        $this->messageOutput = $this->messages['new_date'];
        return $resultId;
      } elseif ($dateAdded == -1 || $translationAdded == -1) {
        $this->errorOutput = $this->errors['new_date'];
      }
    } elseif ($dateChanged == 1 || $translationChanged == 1) {
      $this->messageOutput = $this->messages['save_date'];
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Deletes date
  *
  * @param integer $dateId selected date id
  * @param boolean $onlyTranslation optional, default value FALSE deletes translation only
  * @access private
  * @return boolean deleted or failed
  */
  function deleteDate($dateId, $onlyTranslation = FALSE) {
    $result = FALSE;
    // validation of date id see above
    if ($this->validateDateId($dateId)) {
      // deletes date translation data or current language
      if ($onlyTranslation) {
        $result = $this->databaseDeleteRecord(
          $this->tableDateTrans,
          array(
            'date_id' => (int)$dateId,
            'lng_id' => (int)$this->currentLanguageId
          )
        );
        // or all translations
      } else {
        $result = $this->databaseDeleteRecord(
          $this->tableDateTrans, 'date_id', (int)$dateId
        );
      }
      // set output message
      if ($result !== FALSE) {
        $this->messageOutput = $this->messages['delete_datetrans'];
      } else {
        $this->errorOutput = $this->errors['delete_datetrans'];
      }
      // deletes all date data
      if (!$onlyTranslation) {
        $result &= $this->databaseDeleteRecord(
          $this->tableDates, 'date_id', (int)$dateId
        );
        // set output message
        if ($result !== FALSE) {
          $this->messageOutput = $this->messages['delete_date'];
        } else {
          $this->errorOutput = $this->errors['delete_date_false'];
        }
      }
    }
    return $result;
  }

  /**
  * Gets dialog of date module to edit
  *
  * @param integer $dialogId id / name for dialog
  * @access private
  * @return string as xml
  * @see output_calendar::getModuleEdit()
  */
  function getContentModuleEdit($dialogId) {
    // checks date, it's content_guid and if the content module (specified by id) exists
    if (isset($this->date) && is_array($this->date) &&
        isset($this->date['date_content_guid']) && $this->date['date_content_guid'] &&
        isset($this->contentModules[$this->date['date_content_guid']])) {
      // sets module to local variable and content from date to xml
      $module = $this->contentModules[$this->date['date_content_guid']];
      $content = (strpos($this->date['date_data'], '<data') === 0) ?
        $this->date['date_data'] :
      sprintf(
        '<data version="2"><data-element name="text"><![CDATA[%s]]></data-element></data>',
        papaya_strings::escapeHTMLChars($this->date['date_data'])
      );
      // uses getModuleEdit to get content formular
      return $this->getModuleEdit(
        $module['module_path'].$module['module_file'],
        $module['module_class'],
        $module['module_guid'],
        $content,
        'saveDateContent',
        $dialogId
      );
    }
    return '';
  }

  /**
  * Gets date module to edit and it's formular
  *
  * @param string $fileName name of module file
  * @param string $className
  * @param string $guid unique id
  * @param array $data for dialog
  * @param string $saveFunc name of function to save data
  * @param string $dialogId name / id of dialog
  * @access private
  * @return string as xml
  */
  function getModuleEdit($fileName, $className, $guid, $data, $saveFunc, $dialogId) {
    // creates plugin object by file-, classname, guid...
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $moduleObj = &base_pluginloader::getPluginInstance(
      $guid, $this, $data, $className, $fileName);
    $moduleObj->paramName = $this->paramName;
    $moduleObj->params = $this->params;
    // checks if module is an object and initializes module's dialog
    if (isset($moduleObj) && is_object($moduleObj)) {
      $hidden = array('save_module' => 1, 'cmd' => $this->params['cmd'],
        'date_id' => $this->params['date_id']);
      $moduleObj->initializeDialog($hidden, $dialogId);
      // if save parameter is set check data, ...
      if (isset($this->params['save_module']) && $this->params['save_module'] == 1
          && $moduleObj->modified('save_module') !== FALSE) {
        if ($moduleObj->checkData()) {
          // ... save it with the specified function and set output message
          if ($this->$saveFunc($moduleObj->getData())) {
            $this->messageOutput = $this->messages['save_datecontent'];
          } else {
            $this->errorOutput = $this->errors['save_datecontent'];
          }
        } else {
          $this->errorOutput = $this->errors['check_inputs'];
        }
      }
      // returns modules content form
      return $moduleObj->getForm();
    }
    return '';
  }

  /**
  * This function stores/updates content of a date
  *
  * @access private
  * @return boolean saved or failed
  */
  function saveDateContent($data) {
    // load published dates for check
    if (!isset($this->publishedDates) || !is_array($this->publishedDates)) {
      $this->loadDatesForSurfer(
        $this->surferObj->surferId,
        $this->surferObj->surfer['surfergroup_id'],
        2
      );
    }
    // update existing date content, published date content if allowed
    if ($this->validateDateId($this->selectedDateId)) {
      $updated = $this->databaseUpdateRecord(
        $this->tableDateTrans,
        array('date_data' => $data),
        array(
          'date_id' => (int)$this->selectedDateId,
          'lng_id' => $this->currentLanguageId
        )
      );
      if (FALSE !== $updated) {
        return TRUE;
      }
    }
    return FALSE;
  }


  /**
  * Initializese dialog fields to add or edit dates
  *
  * @access private
  * @return array $fields dialog fields
  */
  function initializeDateFields() {
    $fields = array(
      'date_title' => array($this->captions['input_title'], 'isSomeText',
        $this->mandatoryFields['date_title'], 'input', 400),
      'date_startf' => array($this->captions['input_from'], 'isIsoDate',
        $this->mandatoryFields['date_startf'], 'input', 14),
      'date_endf' => array($this->captions['input_to'], 'isIsoDate',
        $this->mandatoryFields['date_endf'], 'input', 14)
    );
    if (isset($this->timemode) && $this->timemode == 1) {
      $fields['date_hour'] = array($this->captions['input_date_field_hour'],
        'isNum', $this->mandatoryFields['date_hour'], 'input', 2);
      $fields['date_minute'] = array($this->captions['input_date_field_minute'],
        'isNum', $this->mandatoryFields['date_minute'], 'input', 2);
    } else {
      $fields['date_text'] = array($this->captions['input_datetext'],
        'isSomeText', $this->mandatoryFields['date_text'], 'input', 400);
    }
    // sets a combo box to select state if allowed
    if ($this->defaultState == 0) {
      $fields['date_state'] = array($this->captions['input_state'],
        'isSomeText', TRUE, 'combo',
        array(
          1 => $this->captions['input_state_created'],
          2 => $this->captions['input_state_published']
        )
      );
    }
    return $fields;
  }

  /**
  * Initializes the date dialog to add or edit dates
  *
  * @param string $cmd current command parameter
  * @param mixed $dateId optional, default value NULL selected date id
  * @access private
  */
  function initializeDateDialog($cmd, $dateId = NULL) {
    // checks if dialog object already exists
    if (!(isset($this->dateDialog) && !is_object($this->dateDialog))) {
      $hidden = array('save' => 1, 'cmd' => $cmd);
      // sets state as hidden value if default state is defined
      // note the save function will override this value later
      if ($this->defaultState == 1 || $this->defaultState == 2) {
        $hidden['date_state'] = $this->date['date_state'];
      }
      // set data
      if (isset($dateId) && $dateId > 0) {
        $hidden['date_id'] = $dateId;
        $data = array(
          'date_title' => $this->date['date_title'],
          'date_startf' => $this->date['date_startf'],
          'date_endf' => $this->date['date_endf']
        );
        if (isset($this->timemode) && $this->timemode == 1) {
          if (isset($this->date['date_text']) && $this->date['date_text'] != '') {
            $data['date_hour'] = substr($this->date['date_text'], 0, 2);
            $data['date_minute'] = substr($this->date['date_text'], -2);
          }
        } else {
          $data['date_text'] = $this->date['date_text'];
        }
      } else {
        $data = array();
      }
      if ($this->defaultState == 0) {
        $data['date_state'] = $this->date['date_state'];
      }
      // initializes
      $fields = $this->initializeDateFields();
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $this->dateDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dateDialog->paramName = $this->paramName;
      $this->dateDialog->baseLink = $this->baseLink;
      $this->dateDialog->msgs = &$this->msgs;
      $this->dateDialog->dialogId = 'dateform';
      $this->dateDialog->dialogTitle = $this->captions['edit_date'];
      $this->dateDialog->buttonTitle = $this->captions['save_button'];
      $this->dateDialog->loadParams();
    }
  }

  /**
  * Gets output xml
  *
  * @access public
  * @return string $result as xml
  */
  function getXML() {
    $result = '';
    // check if surfer is valid / logged in
    if ($this->surferObj->isValid && isset($this->surferObj->surferId) &&
        $this->surferObj->surferId) {
      $result .= '<calendar>'.LF;
      $deleteTranslation = FALSE;
      switch(@$this->params['cmd']) {
      // delete translation
      case 'delete_trans':
        $deleteTranslation = TRUE;
        // delete complete date
      case 'delete':
        if (isset($this->params['date_id']) && $this->params['date_id'] > 0) {
          $result .= $this->getDeletionDialog(
            $this->params['cmd'], $this->params['date_id'], $deleteTranslation
          );
        }
        break;
      // add or edit date
      case 'add':
      case 'edit':
        if (isset($this->params['date_id']) && $this->params['date_id'] > 0) {
          $result .= $this->getDialog(
            $this->params['cmd'], $this->params['date_id']
          );
        } else {
          $result .= $this->getDialog($this->params['cmd']);
        }
        $result .= $this->getContentModuleEdit('moduleform');
        break;
      }
      // date and messages lists
      $result .= $this->getDatesLists();
      // message output
      if (isset($this->messageOutput) && strlen($this->messageOutput) > 0) {
        $result .= sprintf('<message>%s</message>'.LF, $this->messageOutput);
      }
      // error output
      if (isset($this->errorOutput) && strlen($this->errorOutput) > 0) {
        $result .= sprintf('<error>%s</error>'.LF, $this->errorOutput);
      }
      $result .= '</calendar>'.LF;
    }
    return $result;
  }

  /**
  * Gets a dialog to add or edit dates
  *
  * @see output_calendar::initializeDateDialog
  * @param string $cmd current command parameter
  * @param mixed $dateId optional, default value NULL selected date id
  * @access private
  * @return string XML
  */
  function getDialog($cmd, $dateId = NULL) {
    // validation of date id see method validateDateId
    if (!(isset($this->dateDialog) && is_object($this->dateDialog)) &&
        ($dateId == NULL || $this->validateDateId($dateId))) {
      // initialize and return dialog xml
      $this->initializeDateDialog($cmd, $dateId);
    }
    if (isset($this->dateDialog) && is_object($this->dateDialog)) {
      return $this->dateDialog->getDialogXML();
    }
    return '';
  }

  /**
  * Gets a dialog which ask to confirm deletion of a date
  *
  * @param string $cmd current command parameter
  * @param integer $dateId selected date id
  * @param boolean $onlyTranslation optional, default value FALSE deletes translation only
  * @access private
  * @return string XML
  */
  function getDeletionDialog($cmd, $dateId, $onlyTranslation = FALSE) {
    $hidden = array(
      'cmd' => $cmd,
      'date_id' => $dateId,
      'confirm' => 1
    );
    // message depends on mode
    $msg = ($onlyTranslation) ? $this->captions['delete_datetrans_question'] :
      $this->messages['delete_date_question'];
    // initialize
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $dialog->msgs = &$this->msgs;
    $dialog->dialogId = 'messageform';
    $dialog->buttonTitle = 'Yes';
    return $dialog->getMsgDialog();
  }

  /**
  * Get xml of dates list
  * @return string
  */
  function getDatesLists() {
     $result = '<dates>'.LF;
     // add date button
     $result .= sprintf(
       '<add href="%s">%s</add>'.LF,
       papaya_strings::escapeHTMLChars(
         $this->contentObj->getLink(array('cmd' => 'add'))
       ),
       papaya_strings::escapeHTMLChars($this->captions['add_date'])
     );
     // created dates list
     $result .= $this->getDatesList(1); // state 1 = created
     $result .= $this->getDatesList(2); // state 2 = published
     $result .= '</dates>'.LF;
     return $result;
  }

  /**
  * Gets a list of dates in xml and links to add, edit and delete dates
  *
  * @access private
  * @return string $result XML
  */
  function getDatesList($state = 1) {
    // set dates array and node name corresponding to state
    $datesArray = ($state == 1) ? 'createdDates' : 'publishedDates';
    $result = '';
    // check if dates exists and go through dates to put out
    if (isset($this->$datesArray) &&
        is_array($this->$datesArray) && count($this->$datesArray ) > 0) {
      // node's name corresponds to state
      $datesNode = ($state == 1) ? 'created' : 'published';
      $datesCaption = ($state == 1)
        ? $this->captions['input_state_created']
        : $this->captions['input_state_published'];
      $result = sprintf('<%s caption="%s">'.LF, $datesNode, $datesCaption);
      foreach ($this->$datesArray as $date) {
        $result .= sprintf(
          '<date id="%d" state="%d" untranslated="%d">'.LF,
          (int)$date['date_id'],
          (int)$date['date_state'],
          (int)$date['date_untranslated']
        );
        $result .= sprintf(
          '<from>%s</from>'.LF,
          date('Y-m-d', (int)$date['date_start'])
        );
        $result .= sprintf(
          '<to>%s</to>'.LF,
          date('Y-m-d', (int)$date['date_end'])
        );
        $result .= sprintf(
          '<title>%s</title>'.LF,
          papaya_strings::escapeHTMLChars($date['date_title'])
        );
        $result .= sprintf(
          '<time>%s</time>'.LF,
          papaya_strings::escapeHTMLChars($date['date_text'])
        );
        // checks state, published dates need extra right by setting
        if ($state == 1 || ($state == 2 && $this->modifyPublished == 1)) {
          $result .= sprintf(
            '<edit href="%s">%s</edit>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->contentObj->getLink(
                array('cmd' => 'edit', 'date_id' => (int)$date['date_id'])
              )
            ),
            papaya_strings::escapeHTMLChars($this->captions['edit_date'])
          );
          $result .= sprintf(
            '<delete href="%s">%s</delete>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->contentObj->getLink(
                array('cmd' => 'delete', 'date_id' => (int)$date['date_id'])
              )
            ),
            papaya_strings::escapeHTMLChars($this->captions['delete_date'])
          );
          $result .= sprintf(
            '<deletetrans href="%s">%s</deletetrans>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->contentObj->getLink(
                array('cmd' => 'delete_trans', 'date_id' => (int)$date['date_id'])
              )
            ),
            papaya_strings::escapeHTMLChars($this->captions['delete_trans'])
          );
        }
        $result .= '</date>'.LF;
      }
      $result .= sprintf('</%s>'.LF, $datesNode);
    }
    return $result;
  }

  /**
  * Verify time input
  * @return boolean
  */
  function checkTime() {
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');
    $checkit = new checkit();
    if (isset($this->params['date_hour']) && $this->params['date_hour'] != '') {
      if (isset($this->params['date_minute']) && $this->params['date_minute'] != '') {
        $time = $this->params['date_hour'].':'.$this->params['date_minute'];
        if (!$checkit->isTime($time)) {
          $this->dateDialog->inputErrors['date_hour'] = 1;
          $this->dateDialog->inputErrors['date_minute'] = 1;
          $this->error = $this->errors['check_inputs'];
          return FALSE;
        }
      } else {
        $this->dateDialog->inputErrors['date_minute'] = 1;
        $this->error = $this->errors['check_inputs'];
        return FALSE;
      }
    } else {
      if (isset($this->params['date_minute']) && $this->params['date_minute'] != '') {
        $this->dateDialog->inputErrors['date_hour'] = 1;
        $this->error = $this->errors['check_inputs'];
        return FALSE;
      }
    }
    return TRUE;
  }
}
?>