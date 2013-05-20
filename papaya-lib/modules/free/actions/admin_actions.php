<?php
/**
* Action dispatcher management
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
* @subpackage Free-Actions
* @version $Id: admin_actions.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class action dispatcher base class
*/
require_once(dirname(__FILE__).'/base_actions.php');

/**
* Action dispatcher management
*
* @package Papaya-Modules
* @subpackage Free-Actions
*/
class admin_actions extends base_actions {

  /**
  * Constructor
  */
  function __construct(&$msgs, $paramName = 'act') {
    parent::__construct($paramName);
    $this->paramName = $paramName;
    $this->msgs = $msgs;
  }

  /**
  * Basic function for handling parameters
  *
  * Decides which actions to perform depending on the GET/POST paramaters
  * from the paramName array, stored in the params attribute
  *
  * @access public
  */
  function execute() {
    $this->initializeParams();
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'save_group':
        $this->saveGroup();
        break;
      case 'del_group':
        $this->deleteGroup();
        break;
      case 'save_action':
        $this->saveAction();
        break;
      case 'del_action':
        $this->deleteAction();
        break;
      case 'save_observer':
        $this->saveObserver();
        break;
      case 'del_observer':
        $this->deleteObserver();
        break;
      case 'del_all':
        $this->deleteAll();
        break;
      case 'export_xml':
        $this->exportXML();
        break;
      case 'do_import_xml':
        $this->importXML();
        break;
      }
    }
  }

  /**
  * Get page layout
  *
  * Creates the page layout according to parameters
  *
  * @param object xsl_layout &$layout
  * @access public
  */
  function getXML(&$layout) {
    $this->layout->setParam('COLUMNWIDTH_LEFT', '300px');
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'add_group':
        $this->getGroupForm();
        break;
      case 'del_group':
        $this->getDeleteGroupForm();
        break;
      case 'add_action':
        $this->getActionForm();
        break;
      case 'del_action':
        $this->getDeleteActionForm();
        break;
      case 'add_observer':
        $this->getObserverForm();
        break;
      case 'del_all':
        $this->getDeleteAllForm();
        break;
      case 'import_xml':
        $this->getImportForm();
        break;
      }
    }
    $layout->addLeft($this->getGroupsList());
    $layout->addLeft($this->getActionsList());
    $layout->add($this->getObserversList());
  }

  /**
  * Get groups list
  *
  * Display the list of action groups, pageable
  *
  * @return string XML
  */
  function getGroupsList() {
    $groups = $this->getActionGroups();
    if (empty($groups)) {
      return '';
    }
    $result = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Action groups'))
    );
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Group'))
    );
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($groups as $id => $name) {
      $link = $this->getLink(
        array('group_id' => $id)
      );
      $selected = '';
      if (isset($this->params['group_id']) && $this->params['group_id'] == $id) {
        $selected = ' selected="selected"';
      }
      $result .= sprintf(
        '<listitem title="%s" href="%s"%s/>'.LF,
        papaya_strings::escapeHTMLChars($name),
        papaya_strings::escapeHTMLChars($link),
        $selected
      );
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Get form to add a new group
  *
  */
  function getGroupForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'cmd' => 'save_group'
    );
    $fields = array(
      'group_name' => array('Name', 'isNoHTML', TRUE, 'input', 100)
    );
    $data = array();
    $groupForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $groupForm->dialogTitle = $this->_gt('Add action group');
    $groupForm->buttonTitle = 'Add';
    $groupForm->baseLink = $this->baseLink;
    if (is_object($groupForm)) {
      $this->layout->add($groupForm->getDialogXML());
    }
  }

  /**
  * Save a new group
  *
  */
  function saveGroup() {
    if (!isset($this->params['group_name'])) {
      $this->addMsg(MSG_ERROR, $this->_gt('Missing parameters for new group.'));
      return;
    }
    if (!checkit::isNoHTML($this->params['group_name'], TRUE)) {
      $this->addMsg(MSG_ERROR, $this->_gt('Invalid group name'));
      return;
    }
    $group = $this->getGroupIdByName($this->params['group_name']);
    if ($group !== NULL) {
      $this->addMsg(MSG_ERROR, $this->_gt('Group already exists.'));
      return;
    }
    $success = $this->databaseInsertRecord(
      $this->tableGroups,
      'actiongroup_id',
      array('actiongroup_name' => $this->params['group_name'])
    );
    if ($success !== FALSE) {
      $this->addMsg(MSG_INFO, $this->_gt('Action group successfully added.'));
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Could not add action group -- database error.'));
    }
  }


  /**
  * Get delete group dialog
  *
  */
  function getDeleteGroupForm() {
    if (!isset($this->params['group_id']) ||
         isset($this->params['confirm_delete'])) {
      return;
    }
    $group = $this->getGroupNameById($this->params['group_id']);
    if ($group === NULL) {
      return;
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'del_group',
      'confirm_delete' => 1,
      'group_id' => $this->params['group_id']
    );
    $msg = sprintf($this->_gt('Really delete group "%s"?'), $group);
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    if (is_object($dialog)) {
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Delete an action group
  *
  */
  function deleteGroup($report = TRUE) {
    if (!(isset($this->params['confirm_delete']) && $this->params['confirm_delete'] == 1)) {
      return;
    }
    if (!isset($this->params['group_id'])) {
      $this->addMsg(MSG_ERROR, $this->_gt('Missing parameters to delete group.'));
      return;
    }
    // Start by deleting all actions in this group
    $actions = $this->getActionsByGroupId($this->params['group_id']);
    if (!empty($actions)) {
      $actionIds = array_keys($actions);
      $this->deleteAction($actionIds, FALSE);
    }
    $success = $this->databaseDeleteRecord(
      $this->tableGroups,
      'actiongroup_id',
      $this->params['group_id']
    );
    if ($report) {
      if ($success !== FALSE) {
        $this->addMsg(MSG_INFO, $this->_gt('Action group successfully deleted.'));
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Could not delete action group -- database error.'));
      }
    }
  }

  /**
  * Get actions list
  *
  * Display the list of actions for current group, pageable
  *
  * @return string XML
  */
  function getActionsList() {
    if (!isset($this->params['group_id'])) {
      return '';
    }
    $actions = $this->getActionsByGroupId($this->params['group_id']);
    if (empty($actions)) {
      return '';
    }
    $result = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Actions'))
    );
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Action'))
    );
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($actions as $id => $name) {
      $link = $this->getLink(
        array('group_id' => $this->params['group_id'], 'action_id' => $id)
      );
      $selected = '';
      if (isset($this->params['action_id']) && $this->params['action_id'] == $id) {
        $selected = ' selected="selected"';
      }
      $result .= sprintf(
        '<listitem title="%s" href="%s"%s/>'.LF,
        papaya_strings::escapeHTMLChars($name),
        papaya_strings::escapeHTMLChars($link),
        $selected
      );
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Get form to add a new action
  *
  */
  function getActionForm() {
    if (!isset($this->params['group_id'])) {
      return;
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'cmd' => 'save_action',
      'group_id' => $this->params['group_id']
    );
    $fields = array(
      'action_name' => array('Name', 'isNoHTML', TRUE, 'input', 100)
    );
    $data = array();
    $actionForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $actionForm->dialogTitle = $this->_gt('Add action');
    $actionForm->buttonTitle = 'Add';
    $actionForm->baseLink = $this->baseLink;
    if (is_object($actionForm)) {
      $this->layout->add($actionForm->getDialogXML());
    }
  }

  /**
  * Save a new action
  *
  */
  function saveAction() {
    if (!(isset($this->params['group_id']) && isset($this->params['action_name']))) {
      $this->addMsg(MSG_ERROR, $this->_gt('Missing parameters for new action'));
      return;
    }
    if (!checkit::isNoHTML($this->params['action_name'], TRUE)) {
      $this->addMsg(MSG_ERROR, $this->_gt('Invalid action name.'));
      return;
    }
    $group = $this->getGroupNameById($this->params['group_id']);
    if ($group === NULL) {
      $this->addMsg(MSG_ERROR, $this->_gt('Group does not exist.'));
      return;
    }
    $actionName = trim($this->params['action_name']);
    if ($this->getActionIdByGroupAndAction($group, $actionName) !== NULL) {
      $this->addMsg(MSG_ERROR, $this->_gt('Action already exists for this group.'));
      return;
    }
    $success = $this->databaseInsertRecord(
      $this->tableActions,
      'action_id',
      array('action_group' => $this->params['group_id'], 'action_name' => $actionName)
    );
    if ($success !== FALSE) {
      $this->addMsg(MSG_INFO, $this->_gt('Action sucessfully added.'));
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Could not add action -- database error.'));
    }
  }

  /**
  * Get delete action dialog
  *
  */
  function getDeleteActionForm() {
    if (!(isset($this->params['group_id']) && isset($this->params['action_id'])) ||
         isset($this->params['confirm_delete'])) {
      return;
    }
    $action = $this->getActionNameById($this->params['action_id']);
    if ($action === NULL) {
      return;
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'del_action',
      'confirm_delete' => 1,
      'group_id' => $this->params['group_id'],
      'action_id' => $this->params['action_id']
    );
    $msg = sprintf($this->_gt('Really delete action "%s"?'), $action);
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    if (is_object($dialog)) {
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Delete action(s)
  *
  * @param mixed array|NULL $actions optional, default NULL
  * @param boolean $report optional, default TRUE
  */
  function deleteAction($actions = NULL, $report = TRUE) {
    if (!(isset($this->params['confirm_delete']) && $this->params['confirm_delete'] == 1)) {
      return;
    }
    if ($actions === NULL && isset($this->params['action_id'])) {
      $actions = $this->params['action_id'];
    }
    if (!isset($this->params['group_id']) || $actions === NULL) {
      if ($report) {
        $this->addMsg(MSG_ERROR, $this->_gt('Missing parameters to delete action.'));
      }
      return;
    }
    // Start by deleting all observers for action(s)
    if (is_array($actions)) {
      $actionIds = $actions;
    } else {
      $actionIds = array($actions);
    }
    foreach ($actionIds as $actionId) {
      $this->databaseDeleteRecord(
        $this->tableObservers,
        'action_id',
        $actionId
      );
    }
    $cond = $this->databaseGetSQLCondition('action_id', $actions);
    $sql = "DELETE FROM %s
             WHERE ".str_replace('%', '%%', $cond);
    $sqlParams = array($this->tableActions);
    $success = $this->databaseQueryFmtWrite($sql, $sqlParams);
    if ($report) {
      if ($success !== FALSE) {
        $this->addMsg(MSG_INFO, $this->_gt('Action successfully deleted.'));
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Could not delete action -- database error.'));
      }
    }
  }

  /**
  * Get actions list
  *
  * Display the list of observers for current action
  *
  * @return string XML
  */
  function getObserversList() {
    if (!(isset($this->params['group_id']) && isset($this->params['action_id']))) {
      return '';
    }
    $observers = $this->getObserversByActionId($this->params['action_id'], TRUE);
    if (empty($observers)) {
      return '';
    }
    $result = sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Observers'))
    );
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Observer class'))
    );
    $result .= '<col/>'.LF;
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    foreach ($observers as $guid => $class) {
      $link = $this->getLink(
        array(
          'cmd' => 'del_observer',
          'group_id' => $this->params['group_id'],
          'action_id' => $this->params['action_id'],
          'observer_guid' => $guid
        )
      );
      $selected = '';
      if (isset($this->params['observer_guid']) && $this->params['observer_guid'] == $guid) {
        $selected = ' selected="selected"';
      }
      $result .= sprintf(
        '<listitem title="%s" %s>'.LF,
        papaya_strings::escapeHTMLChars($class),
        $selected
      );
      $result .= sprintf(
        '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
        papaya_strings::escapeHTMLChars($link),
        $this->images['actions-generic-delete'],
        $this->_gt('Delete observer class')
      );
      $result .= '</listitem>'.LF;
    }
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Get observer form
  *
  * A simple form to select an action observer
  */
  function getObserverForm() {
    if (!(isset($this->params['group_id']) && isset($this->params['action_id']))) {
      return;
    }
    // Get list of available connector modules
    $connectors = $this->getConnectorModules();
    if (empty($connectors)) {
      $this->addMsg(MSG_WARNING, $this->_gt('No connector modules available.'));
      return;
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'cmd' => 'save_observer',
      'group_id' => $this->params['group_id'],
      'action_id' => $this->params['action_id']
    );
    $fields = array(
      'observer_guid' => array(
        'Observer class',
        'isGuid',
        TRUE,
        'combo',
        $connectors
      )
    );
    $data = array();
    $observerForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $observerForm->dialogTitle = $this->_gt('Add observer class');
    $observerForm->buttonTitle = 'Add';
    $observerForm->baseLink = $this->baseLink;
    if (is_object($observerForm)) {
      $this->layout->add($observerForm->getDialogXML());
    }
  }

  /**
  * Save a new action observer
  *
  */
  function saveObserver() {
    if (!(
          isset($this->params['group_id']) &&
          isset($this->params['action_id']) &&
          isset($this->params['observer_guid'])
        )) {
      $this->addMsg(MSG_ERROR, $this->_gt('Missing parameters for new observer.'));
      return;
    }
    if (!checkit::isGuid($this->params['observer_guid'])) {
      $this->addMsg(MSG_ERROR, $this->_gt('This is not a valid observer class.'));
      return;
    }
    $checked = $this->checkObserverByActionAndGuid(
      $this->params['action_id'], $this->params['observer_guid']
    );
    if ($checked) {
      return;
    }
    $success = $this->databaseInsertRecord(
      $this->tableObservers,
      NULL,
      array(
        'action_id' => $this->params['action_id'],
        'observer_guid' => $this->params['observer_guid']
      )
    );
    if ($success !== FALSE) {
      $class = $this->getClassByGuid($this->params['observer_guid']);
      $this->addMsg(MSG_INFO, sprintf($this->_gt('Observer "%s" successfully added.'), $class));
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Could not add observer -- database error.'));
    }
  }

  /**
  * Delete an observer
  *
  */
  function deleteObserver() {
    if (!(
          isset($this->params['group_id']) &&
          isset($this->params['action_id']) &&
          isset($this->params['observer_guid'])
        )) {
      $this->addMsg(MSG_ERROR, $this->_gt('Missing parameters for new observer.'));
      return;
    }
    if (!checkit::isGuid($this->params['observer_guid'])) {
      $this->addMsg(MSG_ERROR, $this->_gt('This is not a valid observer class.'));
      return;
    }
    $checked = $this->checkObserverByActionAndGuid(
      $this->params['action_id'], $this->params['observer_guid']
    );
    if (!$checked) {
      $this->addMsg(MSG_ERROR, $this->_gt('Observer does not exist.'));
      return;
    }
    $success = $this->databaseDeleteRecord(
      $this->tableObservers,
      array(
        'action_id' => $this->params['action_id'],
        'observer_guid' => $this->params['observer_guid']
      )
    );
    if ($success !== FALSE) {
      $this->addMsg(MSG_INFO, $this->_gt('Observer successfully deleted.'));
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Could not delete observer -- database error.'));
    }
  }

  /**
  * Get delete all data dialog
  *
  */
  function getDeleteAllForm() {
    if (isset($this->params['confirm_delete'])) {
      return;
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'del_all',
      'confirm_delete' => 1
    );
    $msg = sprintf('Really delete the complete configuration?');
    $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Delete';
    if (is_object($dialog)) {
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Delete the complete configuration
  *
  */
  function deleteAll() {
    if (!(isset($this->params['confirm_delete']) && $this->params['confirm_delete'] == 1)) {
      return;
    }
    $success = $this->deleteAllData();
    if ($success) {
      $this->addMsg(MSG_INFO, $this->_gt('Complete configuration deleted.'));
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Could not delete configuration -- database error.'));
    }
  }

  /**
  * Export current configuration as XML
  *
  */
  function exportXML() {
    $groups = $this->getActionGroups();
    if (empty($groups)) {
      $this->addMsg(MSG_INFO, $this->_gt('Nothing to export -- empty configuration.'));
      return;
    }
    $result = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>'.LF;
    $result .= '<action-observers>'.LF;
    foreach ($groups as $groupId => $groupName) {
      $result .= '  <action-group>'.LF;
      $result .= sprintf(
        '    <name>%s</name>'.LF,
        papaya_strings::escapeHTMLChars($groupName)
      );
      $actions = $this->getActionsByGroupId($groupId);
      foreach ($actions as $actionId => $actionName) {
        $result .= sprintf(
          '    <action name="%s">'.LF,
          papaya_strings::escapeHTMLChars($actionName)
        );
        $observers = $this->getObserversByActionId($actionId);
        foreach ($observers as $observerGuid) {
          $result .= sprintf(
            '      <observer guid="%s"/>'.LF,
            papaya_strings::escapeHTMLChars($observerGuid)
          );
        }
        $result .= '    </action>'.LF;
      }
      $result .= '  </action-group>'.LF;
    }
    $result .= '</action-observers>'.LF;
    $agentString = strtolower(@$_SERVER["HTTP_USER_AGENT"]);
    if (strpos($agentString, 'opera') !== FALSE) {
      $agent = 'OPERA';
    } elseif (strpos($agentString, 'msie') !== FALSE) {
      $agent = 'IE';
    } else {
      $agent = 'STD';
    }
    $mimeType = ($agent == 'IE' || $agent == 'OPERA')
      ? 'application/octetstream' : 'application/octet-stream';
    $fileName = 'action-observers_'.date('Y-m-d').'.xml';
    if ($agent == 'IE') {
      header('Content-Disposition: inline; filename="'.$fileName.'"');
    } else {
      header('Content-Disposition: attachment; filename="'.$fileName.'"');
    }
    header('Content-type: ' . $mimeType);
    echo ($result);
    exit;
  }

  /**
  * Get import XML form
  *
  */
  function getImportForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $hidden = array(
      'cmd' => 'do_import_xml'
    );
    $fields = array(
      'Select upload file with action dispatcher configuration',
      'xml_file' => array('XML file', 'isFile', TRUE, 'file', 200),
      'import_mode' => array(
        'Import mode',
        'isNum',
        TRUE,
        'translatedcombo',
        array(
          0 => 'Add to existing configuration',
          1 => 'Replace existing configuration'
        ),
        '',
        0
      )
    );
    $data = array();
    $importForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $importForm->uploadFiles = TRUE;
    $importForm->dialogTitle = $this->_gt('Import configuration');
    $importForm->buttonTitle = 'Import';
    $importForm->baseLink = $this->baseLink;
    $importForm->msgs = &$this->msgs;
    $importForm->loadParams();
    $this->layout->add($importForm->getDialogXML());
  }

  /**
  * Import profile fields
  *
  * @access public
  */
  function importXML() {
    // Media db instance to determine max upload size
    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');
    $mediaDB = new base_mediadb_edit;
    // Import mode
    $importMode = (isset($this->params['import_mode']) && $this->params['import_mode'] > 0) ? 1 : 0;
    // Assume that there is no error
    $error = '';
    // Check whether there's an upload file
    if (isset($_FILES[$this->paramName]['tmp_name']['xml_file'])) {
      // There is a file, but we need to make sure that no upload error occured
      $fileData = $_FILES[$this->paramName];
      if (isset($fileData) && is_array($fileData) && isset($fileData['error']['xml_file'])) {
        switch ($fileData['error']) {  // check if error encountered
        case 1:                        // exceeded max file size
        case 2:                        // exceeded max post size
          $error = $this->_gt('File too large.');
          break;
        case 3:
          $error = $this->_gt('File not complete.');
          break;
        case 6:
          $error = $this->_gt('No temporary path.');
          break;
        case 4:
          $error = $this->_gt('No upload file.');
          break;
        case 0:
        default:
          $tempFileName = (string)$fileData['tmp_name']['xml_file'];
          break;
        }
      }
      // We've got a file, so check its size and type
      if ($error == '' && isset($tempFileName) && @file_exists($tempFileName)
          && is_uploaded_file($tempFileName)) {
        $tempFileSize = @filesize($tempFileName);
        if ($tempFileSize <= 0) {
          $error = $this->_gt('No upload file.');
        } elseif ($tempFileSize >= $mediaDB->getMaxUploadSize()) {
          $error = $this->_gt('File too large.');
        } elseif ($fileData['type']['xml_file'] != 'text/xml') {
          $error = $this->_gt('Wrong file type, XML expected.');
        }
      }
    } else {
      // No file at all
      $error = $this->_gt('No upload file.');
    }
    if ($error != '') {
      // If there's an error, display it and leave
      $this->addMsg(MSG_ERROR, $error);
      return;
    }
    // Try to create an XML tree and check whether it's valid
    $xml = simple_xmltree::createFromXML(file_get_contents($tempFileName), $this);
    if (!($xml && isset($xml->documentElement))) {
      $this->addMsg(MSG_ERROR, $this->_gt('This is not a valid XML file.'));
      return;
    }
    // Formally, everything is okay, so start parsing the XML tree
    $doc = $xml->documentElement;
    if (!($doc->hasChildNodes())) {
      $this->addMsg(MSG_ERROR, $this->_gt('Empty XML tree.'));
      return;
    }
    if ($doc->nodeType != XML_ELEMENT_NODE || $doc->nodeName != 'action-observers') {
      $this->addMsg(MSG_ERROR, $this->_gt('Illegal root element.'));
      return;
    }
    // Create an array from the XML contents
    $config = array();
    if ($doc->hasChildNodes()) {
      for ($i = 0; $i < $doc->childNodes->length; $i++) {
        $groupNode = $doc->childNodes->item($i);
        if ($groupNode->nodeType == XML_ELEMENT_NODE && $groupNode->nodeName == 'action-group') {
          $groupName = NULL;
          $actions = array();
          if ($groupNode->hasChildNodes()) {
            for ($j = 0; $j < $groupNode->childNodes->length; $j++) {
              $subNode = $groupNode->childNodes->item($j);
              if ($subNode->nodeType == XML_ELEMENT_NODE) {
                if ($subNode->nodeName == 'name') {
                  if ($subNode->hasChildNodes()) {
                    $nameNode = $subNode->childNodes->item(0);
                    if ($nameNode->nodeType == XML_TEXT_NODE) {
                      $groupName = $nameNode->valueOf();
                    }
                  }
                } elseif ($subNode->nodeName == 'action') {
                  $actionName = $subNode->getAttribute('name');
                  $observers = array();
                  if ($subNode->hasChildNodes()) {
                    for ($k = 0; $k < $subNode->childNodes->length; $k++) {
                      $observerNode = $subNode->childNodes->item($k);
                      if ($observerNode->nodeType == XML_ELEMENT_NODE &&
                          $observerNode->nodeName == 'observer') {
                        $observer = $observerNode->getAttribute('guid');
                        if (checkit::isGuid($observer, TRUE)) {
                          $observers[] = $observer;
                        }
                      }
                    }
                  }
                  $actions[$actionName] = $observers;
                }
              }
            }
          }
          if ($groupName !== NULL) {
            $config[$groupName] = $actions;
          }
        }
      }
    }
    if (empty($config)) {
      $this->addMsg(MSG_ERROR, $this->_gt('Configuration from this file was empty.'));
      return;
    }
    // In replace mode, we need to get rid of all existing data
    if ($importMode == 1) {
      $this->deleteAllData();
    }
    foreach ($config as $groupName => $groupData) {
      $groupId = $this->getGroupIdByName($groupName);
      if ($groupId === NULL) {
        $groupId = $this->databaseInsertRecord(
          $this->tableGroups,
          'actiongroup_id',
          array('actiongroup_name' => $groupName)
        );
      }
      if ($groupId) {
        foreach ($groupData as $actionName => $observers) {
          $actionId = $this->getActionIdByGroupAndAction($groupName, $actionName);
          if ($actionId === NULL) {
            $actionId = $this->databaseInsertRecord(
              $this->tableActions,
              'action_id',
              array(
                'action_group' => $groupId,
                'action_name' => $actionName
              )
            );
          }
          if ($actionId) {
            foreach ($observers as $guid) {
              $check = $this->checkObserverByActionAndGuid($actionId, $guid);
              if (!$check) {
                $this->databaseInsertRecord(
                  $this->tableObservers,
                  NULL,
                  array(
                    'action_id' => $actionId,
                    'observer_guid' => $guid
                  )
                );
              }
            }
          }
        }
      }
    }
    $this->addMsg(MSG_INFO, $this->_gt('Configuration successfully imported.'));
  }

  /**
  * Get buttons
  *
  * This method builds the main button bar for action dispatcher management
  *
  * @access public
  */
  function getButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;
    $cmd = '';
    if (isset($this->params['cmd'])) {
      $cmd = $this->params['cmd'];
    }
    $pushed = ($cmd == 'add_group') ? TRUE : FALSE;
    $toolbar->addButton(
      'Add group',
      $this->getLink(array('cmd' => 'add_group')),
      'actions-generic-add',
      'Add a new action group',
      $pushed
    );
    if (isset($this->params['group_id'])) {
      $pushed = ($cmd == 'del_group') ? TRUE : FALSE;
      $toolbar->addButton(
        'Delete group',
        $this->getLink(
          array(
            'cmd' => 'del_group',
            'group_id' => $this->params['group_id']
          )
        ),
        'actions-generic-delete',
        'Delete current action group',
        $pushed
      );
      $toolbar->addSeperator();
      $pushed = ($cmd == 'add_action') ? TRUE : FALSE;
      $toolbar->addButton(
        'Add action',
        $this->getLink(
          array(
            'cmd' => 'add_action',
            'group_id' => $this->params['group_id']
          )
        ),
        'actions-generic-add',
        'Add a new action',
        $pushed
      );
      if (isset($this->params['action_id'])) {
        $pushed = ($cmd == 'del_action') ? TRUE : FALSE;
        $toolbar->addButton(
          'Delete action',
          $this->getLink(
            array(
              'cmd' => 'del_action',
              'group_id' => $this->params['group_id'],
              'action_id' => $this->params['action_id']
            )
          ),
          'actions-generic-delete',
          'Delete current action',
          $pushed
        );
        $toolbar->addSeperator();
        $pushed = ($cmd == 'add_observer') ? TRUE : FALSE;
        $toolbar->addButton(
          'Add observer',
          $this->getLink(
            array(
              'cmd' => 'add_observer',
              'group_id' => $this->params['group_id'],
              'action_id' => $this->params['action_id']
            )
          ),
          'actions-generic-add',
          'Add a new action ovserver',
          $pushed
        );
      }
    }
    $toolbar->addSeperator();
    $pushed = ($cmd == 'del_all') ? TRUE : FALSE;
    $toolbar->addButton(
      'Delete all data',
      $this->getLink(array('cmd' => 'del_all')),
      'places-trash',
      'Delete the complete configuration',
      $pushed
    );
    $toolbar->addSeperator();
    $pushed = ($cmd == 'export_xml') ? TRUE : FALSE;
    $toolbar->addButton(
      'Export XML',
      $this->getLink(array('cmd' => 'export_xml')),
      'actions-download',
      'Export the current configuration as XML',
      $pushed
    );
    $pushed = ($cmd == 'import_xml' || $cmd == 'do_import_xml') ? TRUE : FALSE;
    $toolbar->addButton(
      'Import XML',
      $this->getLink(array('cmd' => 'import_xml')),
      'actions-upload',
      'Import configuration from XML',
      $pushed
    );
    $toolbar->images = &$this->images;
    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $str));
    }
  }
}
?>