<?php
/**
* Module Feedback. The admin class is used to manage the feedback data of all forms
* and to edit new feedback forms.
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Free-Mail
* @version $Id: admin_feedback_store.php 38016 2013-01-25 11:09:58Z smekal $
*/

/**
* Basic class for database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Base parent class
*/
require_once(PAPAYA_INCLUDE_PATH.'modules/free/mail/base_feedback_store.php');

/**
* Module Feedback. This class inherits from base_feedback_store which handels the
* whole database access functions.
*
* @package Papaya-Modules
* @subpackage Free-Mail
*/
class admin_feedback_store extends base_feedback_store {

  /**
  * Absolute count of entries
  * @var integer $entriesAbsCount
  */
  var $entriesAbsCount = 0;
  /**
  * array for feedback form detail
  * @var array $feedbackFormDetail
  */
  var $feedbackFormDetail = NULL;
  /**
  * Steps for multi paging bar
  * @var integer $steps
  */
  var $steps = 10;

  /**
   * Object form_designer
   * @var base_formdesigner
   */
  var $formDesigner = NULL;

  /**
  * Initialize parameters
  *
  * @access public
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);

    $this->initializeSessionParam('offset');
    $this->initializeSessionParam('ffid');

    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    $imagePath = 'module:'.$this->module->guid;
    $this->localImages = array(
      'dialog-add' => $imagePath."/dialog-add.png",
      'dialog-delete' => $imagePath."/dialog-delete.png",
      'dialog-edit' => $imagePath."/dialog-edit.png",
      'dialog-copy' => $imagePath."/dialog-copy.png"
    );
  }

  /**
  * executes commands sent by user
  *
  * @access public
  */
  function execute() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $this->lngSelect = &base_language_select::getInstance();

    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $this->menubar = new base_btnbuilder;
    $this->menubar->images = &$this->images;

    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    switch ($this->params['cmd']) {
    case 'export_csv':
      $formId = $this->params['ffid'];
      if ($formId == 0) {
        $this->addMsg(
          MSG_WARNING,
          $this->_gt(
            'Please select a form first. Multiple forms '.
            'cannot be exportet as CSV, since forms can have '.
            'different structures. CSV only supports a flat table '.
            'structure.'
          )
        );
        $xmlFeedback = $this->getFeedbackList($formId);
        $this->layout->addCenter($xmlFeedback);
        $xmlForm = $this->getFeedbackForms();
        $this->layout->addLeft($xmlForm);
      } else {
        $this->exportFeedbackList($formId);
      }
      break;
    case 'export_xml':
      $this->exportFeedbackList($this->params['ffid'], TRUE);
      break;
    case 'del_all':
      if (isset($this->params['confirm']) && $this->params['confirm']) {
        if ($this->delAllEntries($this->params['ffid'])) {
          $this->addMsg(MSG_INFO, $this->_gt('All entries deleted.'));
        }
        $xmlFeedback = $this->getFeedbackList($this->params['ffid']);
        $this->layout->addCenter($xmlFeedback);
      } else {
        $this->getDelDialog($this->params['ffid']);
      }
      $xmlForm = $this->getFeedbackForms();
      $this->layout->addLeft($xmlForm);
      break;
    case 'del_entry':
      if (isset($this->params['fid']) && (int)$this->params['fid'] > 0) {
        if (isset($this->params['confirm']) && $this->params['confirm'] > 0) {
          $this->delEntry((int)$this->params['fid']);
        } else {
          $this->getDelDialog();
        }
      }
      $xmlForm = $this->getFeedbackForms();
      $xmlFeedback = $this->getFeedbackList($this->params['ffid']);
      $this->layout->addCenter($xmlFeedback);
      $this->layout->addLeft($xmlForm);
      break;
    case 'feedback':
      $this->markFeedbackRead($this->params['fid']);
      $xmlForm = $this->getFeedbackForms();
      $xmlFeedbackView = $this->getFeedbackView();
      $xmlFeedback = $this->getFeedbackList($this->params['ffid']);
      $this->layout->addCenter($xmlFeedback);
      $this->layout->addLeft($xmlForm);
      $this->layout->addRight($xmlFeedbackView);
      break;
    case 'del_form':
      if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
        if ($this->deleteFeedbackForm()) {
          $this->addMSG(MSG_INFO, $this->_gt('Form deleted'));
          unset($this->feedbackFormDetail);
          $this->params['ffid'] = NULL;
        } else {
          $this->addMSG(MSG_ERORR, $this->_gt('Database error! Form not deleted'));
        }
        $this->getXMLFeedbackEditForm();
      } else {
        $this->getXMLFeedbackDelForm();
      }
      $xmlForm = $this->getFeedbackForms();
      $this->layout->addLeft($xmlForm);
      break;
    case 'edit_form':
      $this->getXMLFeedbackEditForm();
      if (isset($this->params['save']) && !isset($this->params['fielddsg_cmd'])) {
        if ($this->addFeedbackForm()) {
          $this->addMsg(MSG_INFO, $this->_gt('Form added.'));
        } else {
          $this->addMsg(MSG_INFO, $this->_gt('Database error! Changes not saved.'));
        }
      } elseif (isset($this->params['edit']) && !isset($this->params['fielddsg_cmd'])) {
        if (isset($this->params['ffid']) && $this->params['ffid'] > 0) {
          if ($this->dialogFeedback->modified('edit')) {
            if ($this->editFeedbackForm()) {
              $this->addMsg(MSG_INFO, $this->_gt('Form modified.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
            }
          } else {
            $this->addMsg(MSG_INFO, $this->_gt('Nothing modified.'));
          }
        }
      }
      if (isset($this->params['ffid']) && $this->params['ffid'] > 0) {
        $this->loadFeedbackFormStructureData($this->params['ffid']);
        $this->initFormDesigner();
        $this->formDesigner->execute();
        if ($this->formDesigner->modified()) {
          if ($this->saveFormStructure($this->formDesigner->fieldsToXML())) {
            $this->addMsg(MSG_INFO, $this->_gt('Form modified.'));
          }
        }
        $this->getFormDesignerXML();
      }
      $xmlForm = $this->getFeedbackForms();
      $this->layout->addLeft($xmlForm);
      break;
    case 'copy_form':
      $this->copyForm();
      break;
    default:
      if (isset($this->params['ffid']) && $this->params['ffid'] > 0 &&
          isset($this->params['fielddsg_cmd'])) {
        $this->loadFeedbackFormStructureData($this->params['ffid']);
        $this->initFormDesigner();
        $this->formDesigner->execute();
        if ($this->formDesigner->modified()) {
          if ($this->saveFormStructure($this->formDesigner->fieldsToXML())) {
            $this->addMsg(MSG_INFO, $this->_gt('Form modified.'));
          }
        }
        $this->getFormDesignerXML();
      } else {
        $xmlFeedback = $this->getFeedbackList($this->params['ffid']);
        $this->layout->addCenter($xmlFeedback);
      }
      $xmlForm = $this->getFeedbackForms();
      $this->layout->addLeft($xmlForm);
      break;
    }
  }

  /**
  * get del dialog
  *
  * @param int $feedbackForm leave empty to get del dialog for all forms
  * @access public
  */
  function getDelDialog($feedbackForm = 0) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => $this->params['cmd'],
      'confirm' => 1,
    );
    if (isset($this->params['fid'])) {
      $hidden['fid'] = $this->params['fid'];
      $question = 'Do you really want to delete feedback entry #%s?';
      $type = 'question';
    } elseif ($feedbackForm == 0) {
      $question = 'Do you really want to delete all feedback entries?';
      $type = 'warning';
    } else {
      $question = 'Do you really want to delete all feedback entries for the selected form?';
      $type = 'warning';
    }

    $this->dialog = new base_msgdialog(
      $this,
      $this->paramName,
      $hidden,
      sprintf($this->_gt($question), $this->params['fid']),
      $type
    );
    $this->dialog->msgs = &$this->msgs;
    $this->dialog->buttonTitle = 'Delete';
    $this->layout->add($this->dialog->getMsgDialog());
  }

  /**
  * generates XML for admin page
  *
  * @access public
  */
  function getXML() {
    if ($this->module->hasPerm(1, TRUE)) {
      $this->menubar->addButton(
        'View all',
        $this->getLink(array('ffid' => '0')),
        'categories-view-list',
        'View all'
      );
      $this->menubar->addButton(
        'Export CSV',
        $this->getLink(array('cmd' => 'export_csv', 'ffid' => $this->params['ffid'])),
        'actions-save-to-disk',
        'Export as CSV'
      );
      $this->menubar->addButton(
        'Export XML',
        $this->getLink(array('cmd' => 'export_xml', 'ffid' => $this->params['ffid'])),
        'actions-save-to-disk',
        'Export as XML'
      );
      $this->menubar->addButton(
        'Delete all',
        $this->getLink(array('cmd' => 'del_all', 'ffid' => $this->params['ffid'])),
        'places-trash',
        'Delete all entries'
      );

      $this->menubar->addSeperator();
      $this->menubar->addButton(
        'Add form',
        $this->getLink(array('cmd' => 'edit_form', 'ffid' => '0')),
        $this->localImages['dialog-add'],
        'Add form'
      );
      if (isset($this->params['ffid']) && $this->params['ffid'] > 0) {
        $this->menubar->addButton(
          'Edit form',
          $this->getLink(array('cmd' => 'edit_form', 'ffid' => $this->params['ffid'])),
          $this->localImages['dialog-edit'],
          'Edit form'
        );
        $this->menubar->addButton(
          'Copy form',
          $this->getLink(array('cmd' => 'copy_form', 'ffid' => $this->params['ffid'])),
          $this->localImages['dialog-copy'],
          'Copy form'
        );
        $this->menubar->addButton(
          'Delete form',
          $this->getLink(array('cmd' => 'del_form', 'ffid' => $this->params['ffid'])),
          $this->localImages['dialog-delete'],
          'Delete form'
        );
      }
    }
    if ($str = $this->menubar->getXML()) {
      $this->layout->addMenu(sprintf('<menu>%s</menu>'.LF, $str));
    }
  }

  /**
  * get feedback list
  *
  * @param $int feedbackFormId
  * @access public
  * @return string $result xml
  */
  function getFeedbackList($feedbackFormId) {
    $this->loadFeedbackData(
      $feedbackFormId,
      $this->steps,
      empty($this->params['offset']) ? 0 : (int)$this->params['offset']
    );

    $result = '';
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Feedback submissions'))
    );
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_paging_buttons.php');
    $result .= papaya_paging_buttons::getPagingButtons(
      $this,
      array('cmd' => 'show'),
       empty($this->params['offset']) ? 0 : (int)$this->params['offset'],
       $this->steps,
       $this->entriesAbsCount
    );
    $result .= '<cols>';
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Subject/Sender'))
    );
    $result .= sprintf(
      '<col align="center">%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Date'))
    );
    $result .= '</cols>';
    $result .= '<items>';
    if (isset($this->feedbackData) && is_array($this->feedbackData) &&
        count($this->feedbackData) > 0) {
      foreach ($this->feedbackData as $row) {
        if ($row['feedback_new'] == 1) {
          $image = 'items-mail';
        } else {
          $image = 'status-mail-new';
        }
        $selected = '';
        if (isset($this->params['fid']) && $this->params['fid'] == $row['feedback_id']) {

          $selected = 'selected = "selected"';
          $image = 'status-mail-open';
        }
        $result .= sprintf(
          '<listitem title="%s" subtitle="%s (%s)" image="%s" href="%s" %s>',
          papaya_strings::escapeHTMLChars(trim($row['feedback_subject'])),
          papaya_strings::escapeHTMLChars(trim($row['feedback_name'])),
          papaya_strings::escapeHTMLChars(trim($row['feedback_email'])),
          papaya_strings::escapeHTMLChars($this->images[$image]),
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cmd' => 'feedback', 'fid' => $row['feedback_id']))
          ),
          $selected
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>',
          date('Y-m-d H:i:s', $row['feedback_time'])
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s" /></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('cmd' => 'del_entry', 'fid' => $row['feedback_id']))
          ),
          papaya_strings::escapeHTMLChars($this->images['actions-mail-delete']),
          papaya_strings::escapeHtmlChars($this->_gt('Delete entry'))
        );
        $result .= '</listitem>'.LF;
      }
    }
    $result .= '</items>';
    $result .= '</listview>';
    return $result;
  }

  /**
  * get feedback forms
  *
  * @access public
  * @return string $result xml
  */
  function getFeedbackForms() {
    $this->loadFeedbackFormsData();
    $result = '';
    $selected = '';
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Feedback forms'))
    );
    $result .= '<items>';
    if (isset($this->params['ffid']) && $this->params['ffid'] == '-1') {
      $selected = 'selected = "selected"';
    }
    $result .= sprintf(
      '<listitem href="%s" image="%s" title="%s" %s>'.LF,
      papaya_strings::escapeHTMLChars(
        $this->getLink(array('ffid' => '-1'))
      ),
      papaya_strings::escapeHTMLChars($this->images['items-dialog']),
      papaya_strings::escapeHTMLChars($this->_gt('Standard form')),
      $selected
    );

    $count = $this->getFeedbackFormCount($this->tableFeedback);
    $result .= sprintf(
      '<subitem align="right">%d</subitem>',
      empty($count['count']) ? 0 : (int)$count['count']
    );

    $result .= '</listitem>';
    if (isset($this->feedbackFormsData) && is_array($this->feedbackFormsData) &&
        count($this->feedbackFormsData) > 0) {
      foreach ($this->feedbackFormsData as $row) {
        $selected = '';
        if (isset($this->params['ffid']) && $this->params['ffid'] == $row['feedback_form_id']) {
          $selected = ' selected = "selected"';
        }
        $result .= sprintf(
          '<listitem href="%s" image="%s" title="%s"%s>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('ffid' => $row['feedback_form_id']))
          ),
          papaya_strings::escapeHTMLChars($this->images['items-dialog']),
          papaya_strings::escapeHTMLChars(trim($row['feedback_form_title'])),
          $selected
        );
        $result .= sprintf(
          '<subitem align="right">%d</subitem>',
          (int)$row['feedback_count']
        );
        $result .= '</listitem>';
      }
    }
    $result .= '</items>';
    $result .= '</listview>';
    return $result;
  }

  /**
  * Initialize feedback edit form
  *
  * @access public
  */
  function initializeFeedbackEditForm() {
    if (!(isset($this->dialogFeedback) && is_object($this->dialogFeedback))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      if (isset($this->params['ffid'])) {
        $this->loadFeedbackFormStructureData((int)$this->params['ffid']);
      }
      if (isset($this->feedbackFormDetail) && is_array($this->feedbackFormDetail)) {
        $data = $this->feedbackFormDetail;
        $hidden = array(
          'cmd' => 'edit_form',
          'edit' => 1,
          'ffid' => $this->feedbackFormDetail['feedback_form_id']
        );
        $btnCaption = 'Edit';
      } else {
        $data = array();
        $hidden = array(
           'cmd' => 'edit_form',
           'save' => 1
        );
        $btnCaption = 'Save';
      }
      $fields = array(
        'feedback_form_title' => array('Title', 'isNoHTML', TRUE, 'input', 100)
      );

      $this->dialogFeedback = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogFeedback->msgs = &$this->msgs;
      $this->dialogFeedback->loadParams();
      $this->dialogFeedback->baseLink = $this->baseLink;
      $this->dialogFeedback->dialogTitle = $this->_gt('Properties');
      $this->dialogFeedback->buttonTitle = $btnCaption;
      $this->dialogFeedback->dialogDoubleButtons = FALSE;
    }
  }

  /**
  * Get XML for feedback edit form
  *
  * @access public
  */
  function getXMLFeedbackEditForm() {
      $this->initializeFeedbackEditForm();
      $this->layout->addCenter($this->dialogFeedback->getDialogXML());
  }
  /**
  * Delete feedback form
  *
  * @access public
  */
  function getXMLFeedbackDelForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $this->loadFeedbackFormStructureData((int)$this->params['ffid']);
    $hidden = array(
      'cmd' => 'del_form',
      'ffid' => $this->params['ffid'],
      'confirm_delete' => 1
    );

    $msg = sprintf(
      $this->_gt('Delete form "%s" (%d)?'),
      $this->feedbackFormDetail['feedback_form_title'],
      $this->feedbackFormDetail['feedback_form_title'],
      (int)$this->params['ffid']
    );
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->baseLink = $this->baseLink;
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    $this->layout->add($dialog->getMsgDialog());
  }

  /**
  * Initialize form designer
  *
  * @access public
  */
  function initFormDesigner() {
    if (!(isset($this->formDesigner) && is_object($this->formDesigner))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_formdesigner.php');
      $this->formDesigner = new base_formdesigner();
      $this->formDesigner->images = &$this->images;
      $this->formDesigner->msgs = &$this->msgs;
      $this->formDesigner->layout = &$this->layout;
      $this->formDesigner->authUser = &$this->authUser;

      $baseParams = array();
      if (isset($this->params['ffid']) && $this->params['ffid'] > 0) {
        $baseParams['ffid'] = $this->params['ffid'];
      }

      $this->formDesigner->initialize(
        $this->paramName,
        empty($this->feedbackFormDetail['feedback_form_structure'])
          ? '' : $this->feedbackFormDetail['feedback_form_structure'],
        $this->lngSelect->currentLanguageId,
        NULL,
        (count($baseParams) > 0) ? $baseParams: NULL
      );
      $this->formDesigner->baseLink = $this->baseLink;
    }
  }

  /**
  * Get form designer xml
  *
  * @access public
  */
  function getFormDesignerXML() {
    $this->initFormDesigner();

    $params = (isset($this->params['ffid']))
      ? array('ffid' => $this->params['ffid'])
      : array();

    if ($str = $this->formDesigner->getButtonsXML($params)) {
      $this->layout->add('<toolbar>'.$str.'</toolbar>', 'toolbars');
    }
    $this->layout->add($this->formDesigner->getDialogXML());
    $this->layout->addRight($this->formDesigner->getListXML());
  }

  /**
  * Copy an existing feedback form
  */
  function copyForm() {
    // If we don't have a form id, get out of here with an error message
    if (!isset($this->params['ffid']) || empty($this->params['ffid'])) {
      $this->addMsg(MSG_ERROR, $this->_gt('No feedback form selected.'));
      break;
    }
    $this->loadFeedbackFormStructureData($this->params['ffid']);
    if ($this->feedbackFormDetail === NULL) {
      $this->addMsg(MSG_ERROR, $this->_gt('The selected form does not exist.'));
    }
    // Load all feedback form names to create a unique one
    $this->loadFeedbackFormTitles();
    $copyTitle = sprintf(
      $this->_gt('Copy of %s'),
      $this->feedbackFormDetail['feedback_form_title']
    );
    $baseCopyTitle = $copyTitle;
    $distinguisher = 0;
    while (in_array($copyTitle, $this->feedbackFormTitles)) {
      $distinguisher++;
      $copyTitle = $baseCopyTitle . ' ' . $distinguisher;
    }
    $data = array(
      'feedback_form_title' => $copyTitle,
      'feedback_form_structure' => $this->feedbackFormDetail['feedback_form_structure']
    );
    $success = $this->databaseInsertRecord($this->tableFeedbackForms, NULL, $data);
    if (FALSE !== $success) {
      $this->addMsg(MSG_INFO, $this->_gt('Copy created successfully.'));
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Error copying feedback form.'));
    }
  }

  /**
  * export feedback list
  * for CSV rfc4180 see http://www.ietf.org/rfc/rfc4180.txt
  *
  * @todo the mimetype stuff should be generalized,
  *       it is used elswhere; maybe the whole export stuff could be
  *       generalized arrayToCSV() or something like that.
  * @access public
  */
  function exportFeedbackList($feedbackForm = 0, $exportAsXml = FALSE) {
    $agentStr = empty($_SERVER["HTTP_USER_AGENT"]) ? '' : strtolower($_SERVER["HTTP_USER_AGENT"]);
    if (strpos($agentStr, 'opera') !== FALSE) {
      $agent = 'OPERA';
    } elseif (strpos($agentStr, 'msie') !== FALSE) {
      $agent = 'IE';
    } else {
      $agent = 'STD';
    }
    $mimeType = ($agent == 'IE' || $agent == 'OPERA')
      ? 'application/octetstream;'
      : 'application/octet-stream;';

    //$this->loadFeedbackFormStructureData($feedbackForm);
    $this->loadFeedbackFormTitles();
    $feedbackTitle = '';
    if ($feedbackForm > 0) {
      $feedbackTitle = $this->feedbackFormTitles[(string)$feedbackForm];
    } else {
      if ($feedbackForm == -1) {
        $feedbackTitle = 'stnfrm';
      }
    }
    $feedbackTitle = preg_replace('/ /', '_', $feedbackTitle);

    $fileName = 'feedback_'.
      strtolower(papaya_strings::normalizeString($feedbackTitle, 6)).'_'.date('Y-m-d');
    $fileName .= ($exportAsXml) ? '.xml' : '.csv';

    if ($agent == 'IE') {
      header('Content-Disposition: inline; filename="'.$fileName.'"');
    } else {
      header('Content-Disposition: attachment; filename="'.$fileName.'"');
    }
    header('Content-type: ' . $mimeType);

    if ($exportAsXml) {
      echo $this->loadXmlFeedback($feedbackTitle, $feedbackForm);
    } else {
      echo $this->loadCsvFeedback($feedbackTitle, $feedbackForm);
    }
    exit;
  }

  /**
  * Returns feedback data from a selected form as an xml document. If no form id is
  * passed as a parameter, the data from all forms is inserted into the xml document.
  *
  * @param string $feedbackTitle A string giving the title of the feedback form
  * @param int $feedbackForm An integer representing a feedback form,
  *   or all feedback forms iff $feedbackForm is 0.
  * @return string the feedback data of a specified form or of all forms in one xml document
  */
  function loadXmlFeedback($feedbackTitle, $feedbackForm = 0) {
    $result = '';
    if ($feedbackForm == 0) {
      unset($this->feedbackData);
      $this->loadFeedbackData(0);
      if (is_array($this->feedbackData)) {
        $result .= '<forms>'.LF;
        if (!$this->feedbackFormTitles) {
          $this->loadFeedbackFormTitles();
        }
        //$currentForm is the current feedback_form id used in the database
        //since database ids always start with 0, we have to use a start value
        //which isn't used, namely -1.
        $currentForm = -1;
        foreach ($this->feedbackData as $entry) {
          if ($currentForm != $entry['feedback_form']) {
            if ($currentForm != -1) {
              $result .= '</form>'.LF;
            }
            $currentForm = $entry['feedback_form'];
            $currentTitle = '';
            if (isset($this->feedbackFormTitles[$currentForm])) {
                $currentTitle = papaya_strings::escapeHTMLChars(
                  $this->feedbackFormTitles[$currentForm]
                );
            }
            $result .= sprintf(
              '<form title="%s" id="%d">'.LF,
              $currentTitle,
              $currentForm
            );
          }
          $result .= $entry['feedback_xmlmessage'].LF;
        }
        $result .= '</form>'.LF.'</forms>'.LF;
      }
    } else {
      unset($this->feedbackData);
      $this->loadFeedbackData($feedbackForm);
      if (isset($this->feedbackData) &&
          is_array($this->feedbackData) &&
          count($this->feedbackData) > 0) {
        $result .= sprintf(
          '<form title="%s" id="%d">'.LF,
          papaya_strings::escapeHTMLChars($feedbackTitle),
          (int)$feedbackForm
        );
        foreach ($this->feedbackData as $entry) {
          $result .= $this->getXHTMLString($entry['feedback_xmlmessage']);
          $result .= LF;
        }
        $result .= '</form>'.LF;
      }
    }
    return $result;
  }

  /**
  * Exports feedback data as CSV file. Since a CSV file represents a flat table structure with the
  * first line holding the column titles, it is not possible to export more than one feedback form
  * within one CSV file.
  *
  * @param string $feedbackTitle Title of the feedback form
  * @param int $feedbackForm Id of the feedback form to export
  * @return string A string representation of the CSV file
  */
  function loadCsvFeedback($feedbackTitle, $feedbackForm = 0) {
    if ($feedbackForm != 0) {
      $csvData = array();
      $csvCaptions = array();
      $xmlString = $this->loadXmlFeedback($feedbackTitle, $feedbackForm);
      include_once(PAPAYA_INCLUDE_PATH.'system/sys_simple_xmltree.php');
      $xmlTree = simple_xmltree::createFromXML($xmlString, $this);
      if (is_object($xmlTree) &&
          isset($xmlTree->documentElement) &&
          $xmlTree->documentElement->hasChildNodes()) {
        for ($idx1 = 0; $idx1 < $xmlTree->documentElement->childNodes->length; $idx1++) {
          $node = &$xmlTree->documentElement->childNodes->item($idx1);
          if ($node->nodeType == XML_ELEMENT_NODE && $node->nodeName == 'entry') {
            if ($node->hasChildNodes()) {
              $csvRecord = array();
              for ($idx2 = 0; $idx2 < $node->childNodes->length; $idx2++) {
                $currentChildNode = &$node->childNodes->item($idx2);
                if ($currentChildNode->nodeType == XML_ELEMENT_NODE &&
                    $currentChildNode->hasAttribute('name')) {
                  $fieldName = $currentChildNode->getAttribute('name');
                  if (!empty($fieldName)) {
                    switch ($currentChildNode->nodeName) {
                    case 'field' :
                      $csvRecord[$fieldName] = $currentChildNode->valueOf();
                      $csvCaptions[$fieldName] = TRUE;
                      break;
                    case 'fieldset' :
                      //a fiedset consists of multiple field elements
                      $nodeContent = '';
                      for ($idx3 = 0; $idx3 < $currentChildNode->childNodes->length; $idx3++) {
                        $setNode = &$currentChildNode->childNodes->item($idx3);
                        if ($setNode->nodeType == XML_ELEMENT_NODE) {
                          $nodeContent .= $setNode->valueOf().' ';
                        }
                      }
                      $csvRecord[$fieldName] = substr($nodeContent, 0, -1);
                      $csvCaptions[$fieldName] = TRUE;
                      break;
                    }
                  }
                }
              }
              $csvData[] = $csvRecord;
            }
          }
        }
        unset($xmlTree);

        $result = '';
        $iCount = count($csvData);
        if ($iCount > 0) {
          //put out column title
          if (count($csvCaptions) > 0) {
            $captions = array_keys($csvCaptions);
            $field = '';
            if ($field = $captions[0]) {
              $field = trim($field);
              $result .= '"'. $field . '"';
              $iterMax = count($captions);
              for ($iter = 1; $iter < $iterMax; ++$iter) {
                $field = trim($captions[$iter]);
                $result .= ',"' . $field . '"';
              }
              $result .= LF;
            }
            for ($i = 0; $i < $iCount; ++$i) {
              $kCount = count($captions);
              $result .= '"'. trim($csvData[$i][$captions[0]]) . '"';
              for ($k = 1; $k < $kCount; ++$k) {
                if (isset($csvData[$i][$captions[$k]])) {
                  $result .= ',"' . trim($this->escapeForCSV($csvData[$i][$captions[$k]])) . '"';
                } else {
                  $result .= ',"'.'"';
                }
              }
              $result .= LF;
            }
            return substr($result, 0, strlen(LF) * -1);
          }
        }
      } else {
        return;
      }
    }
  }

  /**
  * Escape for CSV
  *
  * @todo should be put in papaya_strings since it's used elsewhere
  * @param string $str
  * @access public
  * @return string $str
  */
  function escapeForCSV($str) {
    if (strpos($str, ' ') !== FALSE || strpos($str, '"') !== FALSE ||
        strpos($str, ',') !== FALSE) {
      $str = str_replace('"', '""', $str);
    }
    return $str;
  }

  /**
  * get feedback view
  *
  * @access public
  * @return view as XML-String
  */
  function getFeedbackView() {
    $result = '';
    $this->loadFeedbackDetail($this->params['fid']);
    if (isset($this->feedbackDetail) && is_array($this->feedbackDetail)) {
      $result = '<sheet width="500">';
      $result .= '<header>';
      $result .= '<lines>';
      $result .= sprintf(
        '<line class="headertitle">%s</line>',
        papaya_strings::escapeHTMLChars($this->feedbackDetail['feedback_subject'])
      );
      $result .= sprintf(
        '<line class="headersubtitle">Von: %s (%s)</line>',
        papaya_strings::escapeHTMLChars($this->feedbackDetail['feedback_name']),
        papaya_strings::escapeHTMLChars($this->feedbackDetail['feedback_email'])
      );
      $result .= '</lines>';
      $result .= '<infos>';
      $result .= sprintf(
        '<line>%s</line>',
        date('Y-m-d H:i:s', $this->feedbackDetail['feedback_time'])
      );
      $result .= '</infos>';
      $result .= '</header>';
      $result .= '<text>';
      $result .= $this->preformatted($this->feedbackDetail['feedback_message']);
      $result .= '</text>';
      $result .= '</sheet>';
    }
    return $result;
  }

  /**
  * Preformatted
  *
  * @param string $str
  * @access public
  * @return string
  */
  function preformatted($str) {
    $result = (trim($str) != '') ? papaya_strings::escapeHTMLChars($str) : "\n\n";
    $result = preg_replace(
      '#(\r\n)|(\n\r)|[\r\n]#', '<br />', $result
    );
    $result = preg_replace(
      '#(^|\s)\*([^\*/_<>]+)\*(\s|$)#', '\1<b>*\2*</b>\3', $result
    );
    $result = preg_replace(
      '#(^|\s)/([^\*/_<>]+)/(\s|$)#', '\1<i>/\2/</i>\3', $result
    );
    $result = preg_replace(
      '#(^|\s)_([^\*/_<>]+)_(\s|$)#', '\1<u>_\2_</u>\3', $result
    );
    return $result;
  }
}
?>
