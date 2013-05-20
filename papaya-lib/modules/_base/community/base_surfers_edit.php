<?php
/**
* Community user management, editing
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
* @subpackage _Base-Community
* @version $Id: base_surfers_edit.php 38470 2013-04-30 14:56:54Z kersken $
*/

/**
* Community user management base class
*/
require_once(dirname(__FILE__).'/base_surfers.php');

/**
* Community user management, editing
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class surfer_admin_edit extends surfer_admin {

  /**
  * Cached values of checkAllSurfersAgainstBlacklist()
  * @var array $checkedAllSurfersAgainstBlacklist
  */
  var $checkedAllSurfersAgainstBlacklist = array();

  /**
  * Constructor
  */
  function __construct(&$msgs, $paramName = "sadm") {
    parent::__construct($msgs, $paramName);
    $this->initializeParams('PAPAYA_SESS_'.$paramName);
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->displayThreshold = 100;
    $this->listLength = 10;

    // Non-default tables
    $this->tableSurferLists = PAPAYA_DB_TABLEPREFIX.'_surferlists';
    $this->tableDeletions = PAPAYA_DB_TABLEPREFIX.'_surferdeletions';

    $this->initializeSessionParam('offset');
    $this->initializeSessionParam('order');
    $this->initializeSessionParam('patt', array('offset'));
    $this->initializeSessionParam('mode', array('offset', 'patt'));
    $this->initializeSessionParam('status', array('offset'));
    $this->initializeSessionParam('listlength');

    $this->params['offset'] = @(int)$this->params['offset'];
    $this->params['patt'] = @(string)$this->params['patt'];
    $this->params['listlength'] = @(int)$this->params['listlength'];

    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    if ($this->lngSelect == NULL) {
      $this->lngSelect = &base_language_select::getInstance();
    }
  }

  /**
  * PHP 4 constructor
  */
  function surfer_admin_edit(&$msgs, $paramName = "sadm") {
    $this->__construct($msgs, $paramName);
  }

  /**
  * Get page layout
  *
  * Creates the page layout by calling several methods
  * according to parameters
  *
  * @param object xsl_layout &$layout
  * @access public
  */
  function get(&$layout) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    // Check mode to determine which dialogs are needed
    if ($this->params['mode'] == 1) {
      // Mode 1: Groups
      // Display the group list on the left
      $layout->addLeft($this->getGroupList());
      // Check cmd
      if (@$this->params['cmd'] == 'del_group') {
        // Form to confirm group deletion
        $this->getDelGroupForm($layout);
      } elseif (@$this->params['cmd'] == 'perm_del') {
        // Form to confirm permission deletion
        $this->getDelPermForm($layout);
      }
      // Check whether there's a current group
      if (isset($this->surferGroup) && is_array($this->surferGroup)) {
        // Display form to edit current group
        $this->layout->add($this->getGroupEdit());
      } elseif (isset($this->params['perm_id']) && ($this->params['perm_id'] > 0)) {
        // Otherwise check whether there's a current permission
        if (!(isset($this->editPerm) && is_array($this->editPerm))) {
          // Store current permission in editPerm attribute
          $this->editPerm = $this->permissionsList[$this->params['perm_id']];
        }
        // Display form to edit current permission
        $layout->add($this->editPerm($this->editPerm));
      }
      // Display the permission list on the right
      $layout->addRight($this->permList());
    } elseif ($this->params['mode'] == 2) {
      // Mode 2: Profile data field definitions
      // Decide according to cmd parameter
      // Note: a second, boolean argument in some of
      //       the method calls determins whether a new element
      //       is to be created (TRUE) or whether an existing one
      //       is to be edited (FALSE)
      if ($this->module->hasPerm(4, TRUE)) {
        switch (@$this->params['cmd']) {
        case 'add_field' :
          // Display form to add a profile field (s. note above)
          $this->getEditProfileForm($layout, TRUE);
          break;
        case 'edit_field' :
          // Display form to edit a profile field
          $this->getEditProfileForm($layout, FALSE);
          break;
        case 'del_field' :
          // Display form to delete a profile field
          $this->getDelProfileForm($layout);
          break;
        case 'add_title' :
          // Display form to add a profile data field title
          $this->getEditTitleForm($layout, TRUE);
          break;
        case 'edit_title' :
          // Display form to edit a profile data field title
          $this->getEditTitleForm($layout, FALSE);
          break;
        case 'del_title' :
          // Display form to confirm deletion of a profile data field title
          // Display form for current data field instead if the
          // getDelTitleForm() method reports that it doesn't need
          // to display the delete confirmation form
          if (!$this->getDelTitleForm($layout)) {
            $this->getEditProfileForm($layout, FALSE);
          }
          break;
        case 'save_title' :
          // Re-display form for current data field after saving a title
          $this->getEditProfileForm($layout, FALSE);
          break;
        case 'edit_props' :
          // Display form to edit a profile data field's properties
          $this->getEditFieldPropertiesForm($layout);
          break;
        case 'save_props' :
          // Re-display form for current data field after saving properties
          $this->getEditProfileForm($layout, FALSE);
          break;
        case 'add_class' :
          // Display form to add a profile data category
          $this->getEditProfileCategoryForm($layout, TRUE);
          break;
        case 'edit_class' :
          // Display form to edit a profile data category
          $this->getEditProfileCategoryForm($layout, FALSE);
          break;
        case 'del_class' :
          // Display form to delete a profile data category
          $this->getDelProfileCategoryForm($layout);
          break;
        case 'export_fields' :
          // Display dialog to export dynamic data fields
          $layout->add($this->getExportProfileForm($layout));
          break;
        case 'import_fields' :
          // Display dialog to import dynamic data fields
          $layout->add($this->getImportProfileForm($layout));
          break;
        case 'save_title' :
          // Re-display form for current data field after saving a title
          $this->getEditProfileForm($layout, FALSE);
          break;
        case 'edit_props' :
          // Display form to edit a profile data field's properties
          $this->getEditFieldPropertiesForm($layout);
          break;
        case 'save_props' :
          // Re-display form for current data field after saving properties
          $this->getEditProfileForm($layout, FALSE);
          break;
        case 'add_value' :
          // Display form to edit an existing value
          $this->getEditValueForm($layout, TRUE);
          $this->getEditFieldPropertiesForm($layout);
          break;
        case 'edit_value' :
          // Display form to edit an existing value
          $this->getEditValueForm($layout, FALSE);
          $this->getEditFieldPropertiesForm($layout);
          break;
        case 'del_value' :
          // Display form to delete a multivalue-field value
          $this->getDelValueForm($layout);
          $this->getEditFieldPropertiesForm($layout);
          break;
        case 'save_value' :
          $this->getEditFieldPropertiesForm($layout);
          break;
        case 'move_value' :
          $this->getEditFieldPropertiesForm($layout);
          break;
        }
      }
      // Display list of profile data categories on the left
      $layout->addLeft($this->getProfileCategoryList());
      // Display dropdown to choose a field as display name on the left
      $layout->addLeft($this->getSelectProfileDropdown());
      // Display list of profile data fields if no import/export is performed
      if (!isset($this->params['cmd']) ||
          !in_array($this->params['cmd'], array('export_fields', 'import_fields'))) {
        $layout->add($this->getProfileList());
      }
    } elseif ($this->params['mode'] == 0) {
      // Mode 0: Surfers
      switch (@$this->params['cmd']) {
      case 'delete' :
        // Display form to confirm deletion of a surfer
        $this->getDelSurferForm($layout);
        break;
      case 'del_fav_list' :
        // Display form to confirm deletion of the favorite list
        $this->getDelFavoritesForm($layout);
        break;
      case 'del_request' :
        // Display form to confirm deletion of change request(s)
        $this->getDelChangeRequestForm($layout);
        break;
      case 'perm_del' :
        // Display form to confirm deletion of a permission
        $this->getDelPermForm($layout);
        break;
      case 'editor_add' :
        // Display a form to confirm that an editor user should be added
        $this->getAddEditorForm($layout);
        break;
      case 'search_surfers' :
        // Display a form to search for dynamic profile data
        $this->getSearchDynamicForm($layout);
        break;
      case 'find_surfers' :
        // Get search results and display them
        $this->showSurferResults($layout);
        break;
      }
      if (!isset($this->params['cmd']) ||
          ($this->params['cmd'] != 'search_surfers' && $this->params['cmd'] != 'find_surfers')) {
        // Check whether a new surfer is to be created
        if (isset($this->params['id']) && (!is_array($this->editSurfer)) &&
          ($this->params['id'] == -1)) {
          // Store default values for new surfer in editSurfer attribute
          $this->editSurfer = $this->getNewSurferData();
        } elseif (isset($this->params['id']) && !is_array($this->editSurfer) &&
                    ($this->params['id'] != '')) {
          // Load current surfer if present
          $this->editSurfer = $this->loadSurfer($this->params['id']);
        }
        // Display surfer list on the left
        $layout->addLeft($this->surferList());
        // Check whether there's a surfer to be edited
        if (isset($this->editSurfer) && is_array($this->editSurfer)) {
          // Display form to edit current surfer
          $layout->add($this->editSurfer($this->editSurfer));
        } elseif (isset($this->params['perm_id']) && ($this->params['perm_id'] > 0)) {
          // Otherwise check whether there's a current permission
          if (!(isset($this->editPerm) && is_array($this->editPerm))) {
            // Store current permission in editPerm attribute
            $this->editPerm = $this->permissionsList[$this->params['perm_id']];
          }
          // Display form to edit current permission
          $layout->add($this->editPerm($this->editPerm));
        }
      }
      // Display permissions, statistics, and surfer favorites on the right
      $layout->addRight($this->permList());
      $layout->addRight($this->surferStatistics());
      $layout->addRight($this->surferFavoriteList());
    } elseif ($this->params['mode'] == 3) {
      // Mode 3: General settings
      if ($this->module->hasPerm(5, TRUE)) {
        if (isset($this->params['cmd']) && $this->params['cmd'] == 'check_surfers_in_blacklist') {
          $layout->add($this->getSurfersCheckedAgainstBlacklistXml());
        } else {
          $mode = 'handle';
          if (isset($this->params['bl']) &&
              in_array($this->params['bl'], array('email', 'password'))) {
            $mode = $this->params['bl'];
          }
          $layout->addRight($this->getToggleBlacklistDialog());
          if ($mode == 'handle') {
            $layout->addRight($this->blacklistRules());
          } elseif ($mode == 'email') {
            $layout->addRight($this->emailBlacklistRules());
          } else {
            $layout->addRight($this->passwordBlacklistRules());
          }
          $this->getSettingsForm($layout);
        }
      }
    }
  }

  /**
  * Basic function for handling parameters
  *
  * Decides which actions to perform depending on the GET/POST parameters
  * from the paramName array, stored in the params attribute
  *
  * @access public
  */
  function execute() {
    // Load surfer list first
    $this->loadList();
    // The cmd parameter is used to perform any kind of update command
    // Some of these actions that don't require much code are performed right here
    // Others have their own methods that are called from here if
    // certain prerequisites apply
    //
    // Usually, deletions (and some other changes) are only performed if they were
    // confirmed using a special form before
    //
    // Do we have to add a surfer to favorites?
    if (isset($this->params['d_surfer']) && $this->params['d_surfer'] == 1) {
      if (isset($this->params['id']) && $this->existID($this->params['id'], TRUE)) {
        $this->addSurferToFavorites($this->params['id']);
        $this->addMsg(
          MSG_INFO,
          $this->_gtf(
            'Added surfer "%s" to favorites.',
            $this->getAnyHandleById($this->params['id'])
          )
        );
      } else {
        $this->addMsg(MSG_WARNING, $this->_gt('Could not add surfer to favorites.'));
      }
    }
    // Now go for the cmd parameter
    if (isset($this->params['cmd'])) {
      switch (trim($this->params['cmd'])) {
      case 'del_fav' :
        if (isset($this->params['id']) && $this->existID($this->params['id'], TRUE)) {
          $this->removeSurferFromFavorites($this->params['id']);
          $this->addMsg(
            MSG_INFO,
            $this->_gtf(
              'Removed surfer "%s" from favorites.',
              $this->getAnyHandleById($this->params['id'])
            )
          );
        } else {
          $this->addMsg(MSG_WARNING, $this->_gt('Could not remove surfer from favorites.'));
        }
        break;
      case 'del_fav_list' :
        if (isset($this->params['confirm_delete']) && $this->params['confirm_delete'] == 1) {
          $this->removeSurferFromFavorites();
          $this->addMsg(MSG_INFO, $this->_gt('Removed all surfers from favorites.'));
        }
        break;
      case 'export' :
        // Export surfer data as CSV
        if ($this->module->hasPerm(3, TRUE)) {
          $this->exportSurfers();
        }
        break;
      case 'edit' :
        // Edit surfer's basic data
        if (isset($this->params['id']) && ($this->params['id'] != '')) {
          $this->editSurfer = $this->loadSurfer($this->params['id']);
          $this->editSurfer['surfer_status'] = $this->getOnlineStatus(
            $this->editSurfer['surfer_id']
          );
          if (isset($this->editSurfer) && is_array($this->editSurfer)) {
            $this->initializeSurferForm($this->editSurfer);
            if ($this->surferDialog->modified()) {
              $permit = TRUE;
              if ($this->editSurfer['auth_user_id'] != '' && !($this->authUser->hasPerm(4))) {
                $permit = FALSE;
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Insufficient permissions to edit surfers linked to backend users.')
                );
              }
              if ($permit) {
                $ignore = isset($this->params['ignore_illegal']) && $this->params['ignore_illegal'];
                if (($ignore && $this->checkInputs(TRUE)) ||
                    ($this->surferDialog->checkDialogInput() && $this->checkInputs())) {
                  if ($this->saveSurfer(NULL, $ignore)) {
                    $this->addMsg(MSG_INFO, $this->_gt('Surfer modified.'));
                  } else {
                    $this->addMsg(MSG_ERROR, $this->_gt('Could not save surfer due to errors.'));
                  }
                }
              }
            }
          }
        }
        break;
      case 'edit_profile' :
        // Edit surfer's profile data
        if (isset($this->params['id']) && ($this->params['id'] != '')) {
          $this->editSurfer = $this->loadSurfer($this->params['id']);
          if (isset($this->editSurfer) && is_array($this->editSurfer)) {
            $this->initializeSurferForm($this->editSurfer);
            if ($ignore ||
                $this->surferDialog->checkDialogInput()) {
              if ($this->saveSurferProfile()) {
                $this->addMsg(MSG_INFO, 'Surfer profile data changed.');
              }
            }
          }
        }
        break;
      case 'do_export_fields' :
        // Export profile data fields
        if ($this->module->hasPerm(4, TRUE)) {
          $this->exportProfileFields();
        }
        break;
      case 'do_import_fields' :
        // Import profile data fields
        if ($this->module->hasPerm(4, TRUE)) {
          $this->importProfileFields();
        }
        break;
      case 'add' :
        // Add new surfer
        $this->initializeSurferForm(NULL, 'add');
        $ignore = isset($this->params['ignore_illegal']) && $this->params['ignore_illegal'];
        if (($ignore && $this->checkInputs(TRUE)) ||
            ($this->surferDialog->checkDialogInput() && $this->checkInputs())) {
          if (FALSE !== $this->insertSurfer()) {
            $this->params['id'] = $this->newSurferId;
            $this->editSurfer = $this->loadSurfer($this->newSurferId);
            $this->initializeSurferForm($this->editSurfer, 'edit', TRUE);
            $this->addMsg(MSG_INFO, $this->_gt("New surfer added."));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt("Error inserting surfer."));
          }
        } else {
          $this->editSurfer = $this->getNewSurferData();
        }
        break;
      case 'delete' :
        if ($this->module->hasPerm(6)) {
          // Delete surfer
          if (isset($this->params['id']) && trim($this->params['id']) != '' &&
              isset($this->params['confirm_delete'])) {
            $this->editSurfer = $this->loadSurfer($this->params['id']);
            if (isset($this->editSurfer) && !$this->editSurfer['user_id']) {
              if ($this->deleteSurfer($this->params['id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Surfer deleted.'));
                unset($this->editSurfer);
                unset($this->params['id']);
              } else {
                $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
              }
            }
          }
        }
        break;
      case 'add_rule' :
        // Add a black list rule
        if (isset($this->params['rule']) && trim($this->params['rule']) != '') {
          if ($this->addHandleRuleToBlacklist($this->params['rule'])) {
            $this->addMsg(
              MSG_INFO,
              sprintf($this->_gt('Added rule "%s" to black list.'), $this->params['rule'])
            );
          } else {
            $this->addMsg(
              MSG_ERROR,
              sprintf(
                $this->_gt('Could not add black list rule "%s".'),
                $this->params['rule']
              )
            );
          }
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('No black list rule provided.'));
        }
        break;
      case 'del_rule' :
        // Delete a black list rule
        if (isset($this->params['rule_id']) && trim($this->params['rule_id']) != '') {
          $success = $this->databaseDeleteRecord(
            $this->tableBlacklist,
            'blacklist_id',
            $this->params['rule_id']
          );
          if ($success) {
            $this->addMsg(MSG_INFO, $this->_gt('Black list rule deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('No such black list rule to delete.'));
          }
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('No black list rule to delete.'));
        }
        break;
      case 'add_email_rule' :
        // Add a black list rule
        if (isset($this->params['rule']) && trim($this->params['rule']) != '') {
          if ($this->addEmailRuleToBlacklist($this->params['rule'])) {
            $this->addMsg(
              MSG_INFO,
              sprintf($this->_gt('Added rule "%s" to black list.'), $this->params['rule'])
            );
          } else {
            $this->addMsg(
              MSG_ERROR,
              sprintf(
                $this->_gt('Could not add black list rule "%s".'),
                $this->params['rule']
              )
            );
          }
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('No black list rule provided.'));
        }
        break;
      case 'del_email_rule' :
        // Delete a black list rule
        if (isset($this->params['rule_id']) && trim($this->params['rule_id']) != '') {
          $success = $this->databaseDeleteRecord(
            $this->tableBlacklist,
            'blacklist_id',
            $this->params['rule_id']
          );
          if ($success) {
            $this->addMsg(MSG_INFO, $this->_gt('Black list rule deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('No such black list rule to delete.'));
          }
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('No black list rule to delete.'));
        }
        break;
      case 'delay_on' :
        // Set mode for an email blacklist rule to delay
        if (isset($this->params['rule_id']) && trim($this->params['rule_id']) != '') {
          $success = $this->databaseUpdateRecord(
            $this->tableBlacklist,
            array('blacklist_delay' => 1),
            'blacklist_id',
            $this->params['rule_id']
          );
          if (FALSE !== $success) {
            $this->addMsg(MSG_INFO, $this->_gt('Blacklist rule mode set to delay.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Could not set blacklist rule mode to delay.'));
          }
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('No black list rule to alter the mode for.'));
        }
        break;
      case 'delay_off' :
        // Set mode for an email blacklist rule to reject
        if (isset($this->params['rule_id']) && trim($this->params['rule_id']) != '') {
          $success = $this->databaseUpdateRecord(
            $this->tableBlacklist,
            array('blacklist_delay' => 0),
            'blacklist_id',
            $this->params['rule_id']
          );
          if (FALSE !== $success) {
            $this->addMsg(MSG_INFO, $this->_gt('Blacklist rule mode set to reject.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Could not set blacklist rule mode to reject.'));
          }
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('No black list rule to alter the mode for.'));
        }
        break;
      case 'add_password_rule' :
        // Add a black list rule
        if (isset($this->params['rule']) && trim($this->params['rule']) != '') {
          if ($this->addPasswordRuleToBlacklist($this->params['rule'])) {
            $this->addMsg(
              MSG_INFO,
              sprintf($this->_gt('Added rule "%s" to black list.'), $this->params['rule'])
            );
          } else {
            $this->addMsg(
              MSG_ERROR,
              sprintf(
                $this->_gt('Could not add black list rule "%s".'),
                $this->params['rule']
              )
            );
          }
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('No black list rule provided.'));
        }
        break;
      case 'del_password_rule' :
        // Delete a black list rule
        if (isset($this->params['rule_id']) && trim($this->params['rule_id']) != '') {
          $success = $this->databaseDeleteRecord(
            $this->tableBlacklist,
            'blacklist_id',
            $this->params['rule_id']
          );
          if ($success) {
            $this->addMsg(MSG_INFO, $this->_gt('Black list rule deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('No such black list rule to delete.'));
          }
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('No black list rule to delete.'));
        }
        break;
      case 'del_request' :
        // Delete change request(s)
        if (isset($this->params['request_id']) && $this->params['request_id'] > 0 &&
            isset($this->params['confirm_delete'])) {
          // While we're at it, delete all expired requests and
          // any request of the same type as the clicked one
          $sql = "SELECT surferchangerequest_type
                    FROM %s
                   WHERE surferchangerequest_id = %d";
          $res = $this->databaseQueryFmt(
            $sql,
            array(
              $this->tableChangeRequests,
              $this->params['request_id']
            )
          );
          if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $type = $row['surferchangerequest_type'];
            $this->databaseDeleteRecord(
              $this->tableChangeRequests,
              array(
                'surferchangerequest_surferid' => $this->params['id'],
                'surferchangerequest_type' => $type
              )
            );
          }
          $sql = "DELETE FROM %s WHERE surferchangerequest_expiry < %d";
          $this->databaseQueryFmt($sql, array($this->tableChangeRequests, time()));
          $this->addMsg(MSG_INFO, $this->_gt('Requests deleted.'));
        }
        break;
      case 'move_field' :
        // Move a profile data field in sort order
        if ($this->module->hasPerm(4, TRUE)) {
          if (isset($this->params['data_id']) &&
            $this->params['data_id'] > 0 &&
          isset($this->params['class_id']) &&
          $this->params['class_id'] > 0 &&
          isset($this->params['dir']) &&
          preg_match('/^(up|down)$/', $this->params['dir'])) {
            $dataId = $this->params['data_id'];
            $classId = $this->params['class_id'];
            $dir = $this->params['dir'];
            // Set order in current class by name if there is no order yet
            $this->orderDataFields($classId);
            // Get the order number of the current field
            $order = -1;
            $sql = 'SELECT surferdata_order
                      FROM %s
                     WHERE surferdata_id=%d';
            $res = $this->databaseQueryFmt(
              $sql, array($this->tableData, $dataId)
            );
            if ($num = $res->fetchField()) {
              $order = $num;
            }
            if ($order == -1) {
              $this->addMsg(MSG_ERROR, 'Database error!');
            } else {
              // Check direction
              if ($dir == 'up') {
                // Moving 'up' (lower number)
                // Get minimum order number
                $sql = 'SELECT MIN(surferdata_order)
                          FROM %s
                         WHERE surferdata_class=%d
                         GROUP BY surferdata_class';
                $res = $this->databaseQueryFmt(
                  $sql,
                  array($this->tableData, $classId)
                );
                $minOrder = $res->fetchField();
                // Act only if current field's order number is greater than minimum
                if ($order > $minOrder) {
                  // Swap current field's order number with its predecessor's order number
                  $data = array('surferdata_order' => $order);
                  $this->databaseUpdateRecord(
                    $this->tableData,
                    $data,
                    'surferdata_order',
                    $order - 1);
                  $data = array('surferdata_order' => $order - 1);
                  $this->databaseUpdateRecord(
                    $this->tableData,
                    $data,
                    'surferdata_id',
                    $dataId
                  );
                }
              } else {
                // Moving 'down' (higher number)
                // Get maximum order number
                $sql = 'SELECT MAX(surferdata_order)
                          FROM %s
                         WHERE surferdata_class=%d
                         GROUP BY surferdata_class';
                $res = $this->databaseQueryFmt(
                  $sql,
                  array($this->tableData, $classId)
                );
                $maxOrder = $res->fetchField();
                // Act only if current field's order number is less than maximum
                if ($order < $maxOrder) {
                  // Swap current field's order number with its successor's order number
                  $data = array('surferdata_order' => $order);
                  $this->databaseUpdateRecord(
                    $this->tableData, $data, 'surferdata_order', $order + 1
                  );
                  $data = array('surferdata_order' => $order + 1);
                  $this->databaseUpdateRecord(
                    $this->tableData, $data, 'surferdata_id', $dataId
                  );
                }
              }
            }
          }
        }
        break;
      case 'del_field' :
        // Delete a profile data field
        if ($this->module->hasPerm(4, TRUE)) {
          if (isset($this->params['data_id']) && $this->params['data_id'] > 0 &&
            isset($this->params['confirm_delete'])) {
            // Get the field's category and order number
            $orderData = NULL;
            $sql = "SELECT surferdata_id, surferdata_order, surferdata_class
                      FROM %s
                     WHERE surferdata_id=%d";
            $sqlParams = array($this->tableData, $this->params['data_id']);
            $res = $this->databaseQueryFmt($sql, $sqlParams);
            if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
              $orderData = array(
                'class' => $row['surferdata_class'],
                'order' => $row['surferdata_order']
              );
            }
            // Clean up: Delete related titles first
            $this->databaseDeleteRecord(
              $this->tableDataTitles, 'surferdatatitle_field', $this->params['data_id']
            );
            $this->databaseDeleteRecord(
              $this->tableData, 'surferdata_id', $this->params['data_id']
            );
            $this->addMsg(MSG_INFO, $this->_gt('Data field deleted.'));
            // Close the gap in the field order
            if ($orderData) {
              $sql = "SELECT surferdata_id, surferdata_order
              FROM %s
              WHERE surferdata_class=%d
              AND surferdata_order>%d";
              $sqlParams = array(
                $this->tableData,
                $orderData['class'],
                $orderData['order']
              );
              $fields = array();
              $res = $this->databaseQueryFmt($sql, $sqlParams);
              while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $fields[] = array(
                  'id' => $row['surferdata_id'],
                  'order' => $row['surferdata_order']
                );
              }
              foreach ($fields as $field) {
                $data = array('surferdata_order' => $field['order'] - 1);
                $this->databaseUpdateRecord(
                  $this->tableData,
                  $data,
                  'surferdata_id',
                  $field['id']
                );
              }
            }
          }
        }
        break;
      case 'del_title' :
        // Delete a profile data field's title
        if ($this->module->hasPerm(4, TRUE)) {
          if (isset($this->params['title_id']) && $this->params['title_id'] > 0 &&
            isset($this->params['confirm_delete'])) {
            $this->databaseDeleteRecord(
              $this->tableDataTitles,
              'surferdatatitle_id',
              $this->params['title_id']
            );
            $this->addMsg(MSG_INFO, $this->_gt('Data field title deleted.'));
          }
        }
        break;
      case 'save_props' :
        // Save properties for a data field
        if ($this->module->hasPerm(4, TRUE)) {
          if (isset($this->params['data_id']) && $this->params['data_id'] > 0 &&
            isset($this->params['values'])) {
            // Get the field's type to check the value
            $sql = "SELECT surferdata_type, surferdata_id, surferdata_name
            FROM %s
            WHERE surferdata_id = %d";
            $sqlParams = array($this->tableData, $this->params['data_id']);
            $type = '';
            if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
              if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $type = $row['surferdata_type'];
                $name = $row['surferdata_name'];
              }
            }
            // Do we have a field?
            if ($type == '') {
              // No: issue an error message
              $this->addMsg(MSG_ERROR, $this->_gt('This field does not exist.'));
            } else {
              // Yes: handle the new properties
              $correctType = TRUE;
              if (($type == 'input' || $type == 'textarea') &&
                !is_numeric($this->params['values'])) {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gtf('The properties for "%s" must be a number.', $name)
                );
              } else {
                // Save the value
                $data = array('surferdata_values' => $this->params['values']);
                $this->databaseUpdateRecord(
                  $this->tableData,
                  $data,
                  'surferdata_id',
                  $this->params['data_id']
                );
                $this->addMsg(
                  MSG_INFO,
                  $this->_gtf('Properties for "%s" successfully updated.', $name)
                );
              }
            }
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Not enough data for field properties.'));
          }
        }
        break;
      case 'del_class' :
        // Delete a profile data field category
        if ($this->module->hasPerm(4, TRUE)) {
          if (isset($this->params['class_id']) && $this->params['class_id'] > 0 &&
              isset($this->params['confirm_delete'])) {
            // Get the class's order number
            $sql = "SELECT surferdataclass_order
                      FROM %s
                     WHERE surferdataclass_id=%d";
            $sqlParams = array($this->tableDataClasses, $this->params['class_id']);
            $order = 0;
            $res = $this->databaseQueryFmt($sql, $sqlParams);
            if ($num = $res->fetchField()) {
              $order = $num;
            }
            // Clean up: Delete related titles first
            $this->databaseDeleteRecord(
              $this->tableDataClassTitles, 'surferdataclasstitle_classid', $this->params['class_id']
            );
            $this->databaseDeleteRecord(
              $this->tableDataClasses, 'surferdataclass_id', $this->params['class_id']
            );
            // Decrease the order of fields above the deleted one if appropriate
            if ($order) {
              // Get all fields with a higher order number
              $sql = "SELECT surferdataclass_id,
                             surferdataclass_order
                        FROM %s
                       WHERE surferdataclass_order > %d";
              $sqlParams = array($this->tableDataClasses, $order);
              // Save rows to avoid nested queries
              $classes = array();
              $res = $this->databaseQueryFmt($sql, $sqlParams);
              while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $classes[] = array(
                  'id' => $row['surferdataclass_id'],
                  'order' => $row['surferdataclass_order']
                );
              }
              foreach ($classes as $class) {
                $data = array('surferdataclass_order' => $class['order'] - 1);
                $this->databaseUpdateRecord(
                  $this->tableDataClasses,
                  $data,
                  'surferdataclass_id',
                  $class['id']
                );
              }
            }
            $this->addMsg(MSG_INFO, $this->_gt('Data category deleted.'));
          }
        }
        break;
      case 'move_class' :
        // Move a profile data field category in sort order
        if ($this->module->hasPerm(4, TRUE)) {
          if (isset($this->params['class_id']) &&
              $this->params['class_id'] > 0 &&
              isset($this->params['dir']) &&
              preg_match('/^(up|down)$/', $this->params['dir'])) {
            $classId = $this->params['class_id'];
            $dir = $this->params['dir'];
            // Set order by id if there is no order yet
            $this->orderDataClasses();
            // Get the order number of the current category
            $order = -1;
            $sql = 'SELECT surferdataclass_order
                      FROM %s
                     WHERE surferdataclass_id = %d';
            $res = $this->databaseQueryFmt(
              $sql,
              array($this->tableDataClasses, $classId)
            );
            if ($num = $res->fetchField()) {
              $order = $num;
            }
            if ($order == -1) {
              $this->addMsg(MSG_ERROR, 'Database error!');
            } else {
              // Check direction
              if ($dir == 'up') {
                // Moving 'up' (lower number)
                // Get minimum order number
                $sql = 'SELECT MIN(surferdataclass_order)
                          FROM %s';
                $res = $this->databaseQueryFmt($sql, array($this->tableDataClasses));
                $minOrder = $res->fetchField();
                // Act only if current field's order number is greater than minimum
                if ($order > $minOrder) {
                  // Swap current field's order number with its predecessor's order number
                  $data = array('surferdataclass_order' => $order);
                  $this->databaseUpdateRecord(
                    $this->tableDataClasses,
                    $data,
                    'surferdataclass_order',
                    $order - 1
                  );
                  $data = array('surferdataclass_order' => $order - 1);
                  $this->databaseUpdateRecord(
                    $this->tableDataClasses,
                    $data,
                    'surferdataclass_id',
                    $classId
                  );
                }
              } else {
                // Moving 'down' (higher number)
                // Get maximum order number
                $sql = 'SELECT MAX(surferdataclass_order)
                          FROM %s';
                $res = $this->databaseQueryFmt($sql, array($this->tableDataClasses));
                $maxOrder = $res->fetchField();
                // Act only if current field's order number is less than maximum
                if ($order < $maxOrder) {
                  // Swap current field's order number with its successor's order number
                  $data = array('surferdataclass_order' => $order);
                  $this->databaseUpdateRecord(
                    $this->tableDataClasses,
                    $data,
                    'surferdataclass_order',
                    $order + 1
                  );
                  $data = array('surferdataclass_order' => $order + 1);
                  $this->databaseUpdateRecord(
                    $this->tableDataClasses,
                    $data,
                    'surferdataclass_id',
                    $classId
                  );
                }
              }
            }
          }
        }
        break;
      case 'save_displayfield' :
        // Save profile data field that can be used for surfer display
        if ($this->module->hasPerm(4, TRUE)) {
          if (isset($this->params['field']) && checkit::isNum($this->params['field'])) {
            $this->setProperty('DISPLAY_PROFILE_FIELD', $this->params['field']);
            $this->addMsg(MSG_INFO, $this->_gt('Saved new display profile field.'));
          }
        }
        break;
      case 'add_group' :
        // Add a group
        if ($newId = $this->addGroup()) {
          $this->params['group_id'] = $newId;
          $this->addMsg(MSG_INFO, $this->_gt("New group added."));
        }
        break;
      case 'chg_group' :
        // Change a group's definition
        if (isset($this->params['group_id']) && ($this->params['group_id'] > 0)) {
          $this->loadGroup($this->params['group_id']);
          if (isset($this->surferGroup) && is_array($this->surferGroup)) {
            $this->initializeGroupDialog($this->surferGroup);
            if ($this->groupDialog->modified()) {
              if ($this->groupDialog->checkDialogInput()) {
                if ($this->saveGroup()) {
                  $this->addMsg(MSG_INFO, $this->_gt('Group modified.'));
                } else {
                  $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
                }
              }
            }
          }
        }
        break;
      case 'del_group' :
        // Delete a group
        if (isset($this->params['group_id']) && $this->params['group_id'] > 0 &&
            isset($this->params['confirm_delete'])) {
          if ($this->deleteGroup($this->params['group_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Group deleted.'));
            unset($this->params['group_id']);
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
        }
        break;
      case 'chg_perm' :
        // Change a permission
        if (isset($this->params['perm_id']) && ($this->params['perm_id'] > 0)) {
          $this->editPerm = $this->loadPerm($this->params['perm_id']);
          if (isset($this->editPerm) && is_array($this->editPerm)) {
            $this->initializePermDialog($this->editPerm);
            if ($this->permDialog->modified()) {
              if ($this->permDialog->checkDialogInput()
                  && $this->checkPermInputs()) {
                if ($this->savePerm()) {
                  $this->addMsg(MSG_INFO, $this->_gt('Permission modified.'));
                }
              }
            }
          }
        }
        break;
      case 'addlink' :
        // Create a permission link
        if (isset($this->params['group_id']) && $this->params['group_id'] > 0 &&
            isset($this->params['perm_id']) && $this->params['perm_id'] > 0) {
          // Check whether the link already exists
          $sql = "SELECT COUNT(*)
                    FROM %s
                   WHERE surfer_permid = %d
                     AND surfergroup_id = %d";
          $sqlParams = array($this->tableLink, $this->params['perm_id'], $this->params['group_id']);
          $exists = 0;
          if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
            if ($num = $res->fetchField()) {
              $exists = $num;
            }
          }
          $success = FALSE;
          if ($exists > 0) {
            $success = TRUE;
          } else {
            $data = array(
              'surfer_permid' => $this->params['perm_id'],
              'surfergroup_id' => $this->params['group_id']
            );
            $success = $this->databaseInsertRecord($this->tableLink, NULL, $data);
          }
          if (FALSE !== $success) {
            $this->addMsg(MSG_INFO, $this->_gt('Permission linked.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
        }
        break;
      case 'dellink' :
        // Remove a permission link
        if (isset($this->params['group_id']) && $this->params['group_id'] > 0 &&
            isset($this->params['perm_id']) && $this->params['perm_id'] > 0) {
          $filter = array(
            'surfer_permid' => (int)$this->params['perm_id'],
            'surfergroup_id' => (int)$this->params['group_id']
          );
          if ($this->databaseDeleteRecord($this->tableLink, $filter) !== FALSE) {
            $this->addMsg(MSG_INFO, $this->_gt('Link deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
        }
        break;
      case 'perm_add' :
        // Add a permission
        $newId = $this->databaseInsertRecord(
          $this->tablePerm,
          'surferperm_id',
          array(
            'surferperm_title' => $this->_gt('New permission'),
            'surferperm_active' => FALSE
          )
        );
        if ($newId) {
          $this->addMsg(MSG_INFO, $this->_gt('Permission added.'));
          $this->params['perm_id'] = $newId;
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
        }
        break;
      case 'perm_del' :
        // Delete a permission
        if (isset($this->params['perm_id']) && $this->params['perm_id'] > 0 &&
            isset($this->params['confirm_delete'])) {
          if ($this->deletePerm($this->params['perm_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Permission deleted.'));
            unset($this->params['perm_id']);
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
        }
        break;
      case 'editor_add' :
        // Add an editor user
        if (isset($this->params['confirm_editor_add'])
            && $this->params['confirm_editor_add']
            && ($this->editSurfer = $this->loadSurfer($this->params['id']))
            && trim($this->editSurfer['auth_user_id']) == '') {
          if ($this->addEditorUser()) {
            $this->addMsg(MSG_INFO, $this->_gt('Editor added.'));
            unset($this->editSurfer);
            unset($this->params['cmd']);
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
        }
        break;
      case 'save_field' :
        // Assume no PCRE error
        if ($this->module->hasPerm(4, TRUE)) {
          $err = FALSE;
          if (!isset($this->params['field_name']) || trim($this->params['field_name']) == '') {
            $this->addMsg(
              MSG_ERROR, $this->_gt('You need to enter a field name.')
            );
            $err = TRUE;
          }
          if (!$err) {
            if (isset($this->params['create']) && $this->params['create'] == 1) {
              $new = TRUE;
            } else {
              $new = FALSE;
            }
            // Get data
            $data = array(
              'surferdata_name' => $this->params['field_name'],
              'surferdata_class' => $this->params['field_class'],
              'surferdata_type' => $this->params['field_type'],
              'surferdata_check' => $this->params['field_check'],
              'surferdata_available' => $this->params['field_available'],
              'surferdata_mandatory' => $this->params['field_mandatory'],
              'surferdata_needsapproval' => $this->params['field_needsapproval'],
              'surferdata_approvaldefault' => $this->params['field_approved']
              );
            // For a new field, add default parameters for input fields or checkboxes
            if ($new) {
              if ($this->params['field_type'] == 'input') {
                $data['surferdata_values'] = $this->getProperty('INPUT_MAXLENGTH', 50);
              } elseif ($this->params['field_type'] == 'textarea') {
                $data['surferdata_values'] = $this->getProperty('TEXTAREA_LINES');
              }
            }
            // Check whether a field with the selected name exists
            $sql = "SELECT COUNT(*)
                      FROM %s
                     WHERE surferdata_name='%s'";
            // Add exclusion for current field if we're editing an existing one
            if (!$new) {
              $sql .= sprintf(" AND surferdata_id != %d", @(int)$this->params['data_id']);
            }
            $sqlParams = array($this->tableData, $this->params['field_name']);
            if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
              if ($num = $res->fetchField()) {
                if ($num > 0) {
                  $this->addMsg(MSG_ERROR, $this->_gt('Field of this name already exists.'));
                  break;
                }
              }
            }
            // If check type is 'PCRE', get the value of the PCRE text field
            if ($data['surferdata_check'] == -1) {
              // If PCRE field is empty, report an error and re-display edit form
              if (trim($this->params['field_pcre']) == '') {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Please enter PCRE value or change check type.')
                );
                $err = TRUE;
              } else {
                $data['surferdata_check'] = $this->params['field_pcre'];
              }
            }
            if (!$err) {
              if ($new) {
                // create new record
                $fieldId = $this->databaseInsertRecord(
                  $this->tableData,
                  'surferdata_id',
                  $data
                );
                if ($fieldId > 0) {
                  // This field is will get the highest order number
                  $this->orderDataFields($this->params['field_class'], $fieldId);
                  $this->addMsg(
                    MSG_INFO,
                    $this->_gt('Field created').': '.$this->params['field_name']
                  );
                } else {
                  $this->addMsg(MSG_ERROR, $this->_gt('Input Error. Please check your input.'));
                }
              } else {
                // Update record
                $id = $this->params['data_id'];
                $this->databaseUpdateRecord($this->tableData, $data, 'surferdata_id', $id);
                $this->addMsg(
                  MSG_INFO,
                  $this->_gt('Field updated.').': '.$this->params['field_name']
                );
                // Adjust order if category changed
                if ($this->params['field_class'] != $this->params['field_class_old']) {
                  // Get the field's current order
                  $order = 0;
                  $sql = "SELECT surferdata_order
                            FROM %s
                           WHERE surferdata_id=%d";
                  $sqlParams = array($this->tableData, $id);
                  $res = $this->databaseQueryFmt($sql, $sqlParams);
                  if ($num = $res->fetchField()) {
                    $order = $num;
                  }
                  // Close gap in the old category
                  $sql = "SELECT surferdata_id, surferdata_order
                            FROM %s
                           WHERE surferdata_class=%d
                             AND surferdata_order>%d";
                  $sqlParams = array(
                    $this->tableData,
                    $this->params['field_class_old'],
                    $order
                  );
                  $fields = array();
                  $res = $this->databaseQueryFmt($sql, $sqlParams);
                  while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                    array_push(
                      $fields,
                      array(
                        'id' => $row['surferdata_id'],
                        'order' => $row['surferdata_order']
                      )
                    );
                  }
                  foreach ($fields as $field) {
                    $data = array('surferdata_order' => $field['order'] - 1);
                    $this->databaseUpdateRecord(
                      $this->tableData,
                      $data,
                      'surferdata_id',
                      $field['id']
                    );
                  }
                  // Now assign the highest order number of the new category
                  // to the field
                  $this->orderDataFields($this->params['field_class'], $id);
                }
              }
            }
          }
        }
        break;
      case 'save_class' :
        // Save a profile data category
        if ($this->module->hasPerm(4, TRUE)) {
          $this->saveDataClass();
        }
        break;
      case 'save_title' :
        // Save a profile field title
        // Check whether a new title is created or an existing one is updated
        if ($this->module->hasPerm(4, TRUE)) {
          if (isset($this->params['create']) && $this->params['create'] == 1) {
            $new = TRUE;
          } else {
            $new = FALSE;
          }
          // Get data for field update
          $data = array(
            'surferdatatitle_lang' => $this->params['language'],
            'surferdatatitle_title' => $this->params['title']
            );
          if ($new) {
            // New title
            // Check whether there already is a title for this language
            $sql = 'SELECT COUNT(*)
                      FROM %s
                     WHERE surferdatatitle_field=%d
                       AND surferdatatitle_lang=%d';
            $res = $this->databaseQueryFmt(
              $sql,
              array(
                $this->tableDataTitles, $this->params['data_id'], $this->params['language']
              )
            );
            if ($num = $res->fetchField()) {
              if ($num > 0) {
                // Found a title: Display error message, get out
                $this->addMsg(
                  MSG_WARNING,
                  $this->_gt('Title already exists.').
                  sprintf(' in %s', $this->getLanguageTitle($this->params['language']))
                );
                return;
              }
            }
            // Otherwise insert record
            $data['surferdatatitle_field'] = $this->params['data_id'];
            $success = $this->databaseInsertRecord(
              $this->tableDataTitles,
              'surferdatatitle_id',
              $data);
            if ($success) {
              $this->addMsg(MSG_INFO, $this->_gt('Title created').': '. $this->params['title']);
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Input error. Please check your input.'));
            }
          } else {
            // Existing title: update record
            $id = $this->params['title_id'];
            $this->databaseUpdateRecord(
              $this->tableDataTitles, $data, 'surferdatatitle_id', $id
            );
            $this->addMsg(MSG_INFO, $this->_gt('Title updated.').': '.$this->params['title']);
          }
        }
        break;
      case 'save_value' :
        if ($this->module->hasPerm(4)) {
          $this->saveValue();
        }
        break;
      case 'del_value' :
        if ($this->module->hasPerm(4)) {
          $this->deleteValue();
        }
        break;
      case 'move_value' :
        if ($this->module->hasPerm(4)) {
          $this->moveValue();
        }
        break;
      case 'save_settings' :
        if ($this->module->hasPerm(5)) {
          $this->initSettingsForm();
          if ($this->settingsDialog->checkDialogInput()) {
            if (isset($this->params['idle_timeout'])) {
              $this->setProperty('IDLE_TIMEOUT', $this->params['idle_timeout']);
            }
            if (isset($this->params['max_favorite_surfers'])) {
              $this->setProperty('MAX_FAVORITE_SURFERS', $this->params['max_favorite_surfers']);
            }
            if (isset($this->params['avatar_general'])) {
              $this->setProperty('AVATAR_GENERAL', $this->params['avatar_general']);
            }
            if (isset($this->params['avatar_female'])) {
              $this->setProperty('AVATAR_FEMALE', $this->params['avatar_female']);
            }
            if (isset($this->params['avatar_male'])) {
              $this->setProperty('AVATAR_MALE', $this->params['avatar_male']);
            }
            if (isset($this->params['avatar_default_size'])) {
              $this->setProperty('AVATAR_DEFAULT_SIZE', $this->params['avatar_default_size']);
            }
            if (isset($this->params['password_min_length'])) {
              $this->setProperty('PASSWORD_MIN_LENGTH', $this->params['password_min_length']);
            }
            if (isset($this->params['password_not_equals_handle'])) {
              $this->setProperty(
                'PASSWORD_NOT_EQUALS_HANDLE',
                $this->params['password_not_equals_handle']
              );
            }
            if (isset($this->params['password_blacklist_check'])) {
              $this->setProperty(
                'PASSWORD_BLACKLIST_CHECK',
                $this->params['password_blacklist_check']
              );
            }
            if (isset($this->params['freemail_delay'])) {
              $this->setProperty(
                'FREEMAIL_DELAY',
                $this->params['freemail_delay']
              );
            }
            if (isset($this->params['input_maxlength']) &&
                is_numeric($this->params['input_maxlength'])) {
              $this->setProperty('INPUT_MAXLENGTH', $this->params['input_maxlength']);
            }
            if (isset($this->params['textarea_lines']) &&
                is_numeric($this->params['textarea_lines'])) {
              $this->setProperty('TEXTAREA_LINES', $this->params['textarea_lines']);
            }
            if (isset($this->params['default_contact'])) {
              $this->setProperty('DEFAULT_CONTACT', $this->params['default_contact']);
            }
            if (isset($this->params['path_cache_time'])) {
              $this->setProperty('PATH_CACHE_TIME', $this->params['path_cache_time']);
            }
            $this->addMsg(MSG_INFO, $this->_gt('General settings changed.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Input error.'));
          }
        }
        break;
      case 'cleanup_contacts':
        // Search for all invalid contacts (e.g. deleted surfers) and remove them
        $this->cleanupContacts();
        break;
      case 'clear_oldrequests':
        // Clear old surfer registrations that have expired more than two weeks ago
        list($numSurferIds, $numChangeRequests) = $this->clearOldRegistrations(14 * 86400);
        $this->addMsg(
          MSG_INFO,
          sprintf(
            $this->_gt('Deleted %d registration(s) and %d change request(s)'),
            $numSurferIds,
            $numChangeRequests
          )
        );
        break;
      case 'clear_cache':
        include_once(dirname(__FILE__).'/base_contacts.php');
        $manager = contact_manager::getInstance();
        $manager->clearContactCache();
        $this->addMsg(MSG_INFO, $this->_gt('Removed old entries from contact path cache.'));
        break;
      case 'convert_contacts':
        // Convert old one-way-contacts to the new, two-way format
        $this->convertContacts();
        break;
      case 'convert_blacklist_entries':
        $this->convertBlacklistEntries();
        break;
      case 'check_surfers_in_blacklist':
        if (!$this->checkAllSurfersAgainstBlacklist()) {
          $this->addMsg(
            MSG_INFO,
            $this->_gt('No entries.')
          );
        }
      }
    }
    // Load group list
    $adminGroups = $this->getAdminGroups();
    $this->loadGroups($adminGroups);
    // Load surfer list (which might have changed now)
    $this->loadList();
    // Load data for current group, if set
    if (isset($this->params['group_id']) && $this->params['group_id'] > 0) {
      $this->loadGroup($this->params['group_id']);
    }
    // Load data for current surfer, if set
    if (isset($this->params['id']) && $this->params['id'] > 0) {
      $this->editSurfer = $this->loadSurfer($this->params['id']);
      if ($this->editSurfer['auth_user_id'] && (!$this->editSurfer['user_id'])) {
        $this->params['cmd'] = 'delete';
      }
    }
    // Load data for current permission, if set
    if (isset($this->params['perm_id']) && $this->params['perm_id'] > 0) {
      $this->editPerm = $this->loadPerm($this->params['perm_id']);
    }
    // Save session parameters
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Get administrative groups for the current user
  *
  * Return NULL if the user is in admin group
  *
  * @return mixed array or NULL
  */
  function getAdminGroups() {
    $adminGroups = array(0, $this->authUser->user['group_id']);
    if (!empty($this->authUser->groups)) {
      $adminGroups = array_merge($adminGroups, array_keys($this->authUser->groups));
    }
    if (in_array(-1, $adminGroups)) {
      $adminGroups = NULL;
    }
    return $adminGroups;
  }

  /**
  * Save Surfer profile
  *
  * Stores a surfer's dynamic profile data
  * that was changed in the backend profile form
  * in the surfer contact data table.
  * Returns true if there are any changes,
  * otherwise false
  *
  * @access public
  * @return boolean
  */
  function saveSurferProfile() {
    // Assume that there are no changes
    $changes = FALSE;
    $surferId = $this->editSurfer['surfer_id'];
    // Get field names and ids
    $sql = 'SELECT surferdata_id, surferdata_name
              FROM %s
             WHERE surferdata_available=1';
    $res = $this->databaseQueryFmt($sql, $this->tableData);
    // store data in a name=>id associative array
    $fields = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      $fields[$row['surferdata_name']] = $row['surferdata_id'];
    }
    // If fields are defined
    if (sizeof($fields) > 0) {
      // Check data for each field
      foreach ($fields as $fieldName => $fieldId) {
        if (isset($this->params[$fieldName])) {
          $value = $this->params[$fieldName];
          // Prepare data for contact data table
          $data = array();
          if (is_array($value)) {
            $data['surfercontactdata_value'] = serialize($value);
          } else {
            $data['surfercontactdata_value'] = $value;
          }
          // Check whether a database record for this field exists
          $isql = "SELECT surfercontactdata_id, surfercontactdata_value
                     FROM %s
                    WHERE surfercontactdata_property=%d
                      AND surfercontactdata_surferid='%s'";
          $ires = $this->databaseQueryFmt(
            $isql,
            array($this->tableContactData, $fieldId, $surferId)
          );
          if ($irow = $ires->fetchRow(DB_FETCHMODE_ASSOC)) {
            $dataId = $irow['surfercontactdata_id'];
            $oldVal = $irow['surfercontactdata_value'];
            // Did the value change at all?
            if ($value != $oldVal) {
              // Now here's a change
              $changes = TRUE;
              // Is new data empty?
              if (empty($value)) {
                // Then delete existing record
                $this->databaseDeleteRecord(
                  $this->tableContactData, 'surfercontactdata_id', $dataId
                );
              } else {
                // otherwise update existing field
                $this->databaseUpdateRecord(
                  $this->tableContactData, $data, 'surfercontactdata_id', $dataId
                );
              }
            }
          } else {
            // Insert new field -- if not empty
            if (!empty($value)) {
              // This is a change as well
              $changes = TRUE;
              // Prepare data and insert record
              $data['surfercontactdata_surferid'] = $surferId;
              $data['surfercontactdata_property'] = $fieldId;
              $this->databaseInsertRecord(
                $this->tableContactData, 'surfercontactdata_id', $data
              );
            }
          }
        } else {
          // If the field is not within the params, delete its value
          // (especially suitable for checkgroups in which nothing is selected)
          $conditions = array(
            'surfercontactdata_surferid' => $surferId,
            'surfercontactdata_property' => $fieldId
          );
          $this->databaseDeleteRecord($this->tableContactData, $conditions);
        }
      }
    }
    return $changes;
  }

  /**
  * Save profile data class
  *
  * Inserts a new or changes an existing profile data category
  */
  function saveDataClass() {
    // Check whether this is a new or a changed profile data category
    if (isset($this->params['create']) && $this->params['create'] == 1) {
      // New category
      // Check whether this category was assigned a permission, or use 0 if not
      if (isset($this->params['perm'])) {
        $perm = $this->params['perm'];
      } else {
        $perm = 0;
      }
      // Prepare data
      $data = array('surferdataclass_perm' => $perm);
      // Insert the new category; store its id for order and title relations
      $classId = $this->databaseInsertRecord(
        $this->tableDataClasses, 'surferdataclass_id', $data
      );
      // Order
      $this->orderDataClasses($classId);
      $msg = $this->_gt('Data category added.');
    } else {
      // Change existing category
      $classId = $this->params['class_id'];
      if (isset($this->params['perm'])) {
        $this->databaseUpdateRecord(
          $this->tableDataClasses,
          array(
            'surferdataclass_perm' => $this->params['perm']
          ),
          'surferdataclass_id',
          $classId
        );
      }
      $msg = $this->_gt('Data category updated.');
    }
    // Save the category titles, if provided
    foreach ($this->params as $param => $value) {
      // Find any param that starts with 'lang_'
      if (preg_match('/^lang_(.*)/', $param, $matches)) {
        // Get current language
        $lang = $matches[1];
        // Prepare data
        $data = array(
          'surferdataclasstitle_lang' => $lang,
          'surferdataclasstitle_name' => $value,
          'surferdataclasstitle_classid' => $classId
        );
        // Check whether this is a new or an existing category
        if (isset ($this->params['create']) && $this->params['create'] == 1) {
          // New: Simply insert the title
          if (trim($value) != '') {
            $this->databaseInsertRecord(
              $this->tableDataClassTitles, 'surferdataclasstitle_id', $data
            );
          }
        } else {
          // Existing
          // Get the title's id by category id and language
          $sql = "SELECT surferdataclasstitle_id FROM %s
                   WHERE surferdataclasstitle_classid=%d
                     AND surferdataclasstitle_lang=%d";
          $res = $this->databaseQueryFmt(
            $sql,
            array($this->tableDataClassTitles, $classId, $lang)
          );
          // Check whether a title in current language exists
          if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            // Existing title
            // Check whether the new value is an empty string
            if (trim($value) != '') {
              // Empty: delete existing record
              $this->databaseUpdateRecord(
                $this->tableDataClassTitles,
                $data,
                'surferdataclasstitle_id',
                $row['surferdataclasstitle_id']
              );
            } else {
              // Not empty: update existing record
              $this->databaseDeleteRecord(
                $this->tableDataClassTitles,
                'surferdataclasstitle_id',
                $row['surferdataclasstitle_id']
              );
            }
          } else {
            // New title: insert record
            if (trim($value) != '') {
              $this->databaseInsertRecord(
                $this->tableDataClassTitles,
                'surferdataclasstitle_id',
                $data
              );
            }
          }
        }
      }
    }
    $this->addMsg(MSG_INFO, $msg);
  }

  /**
  * Insert surfer
  *
  * Inserts a database record for a new surfer
  * into surfer table
  * Returns true if this works, otherwise false
  *
  * @access public
  * @return boolean
  */
  function insertSurfer() {
    $this->newSurferId = $this->createSurferId();
    $now = time();
    $data = array(
      'surfer_id' => $this->newSurferId,
      'surfer_handle' => @(string)$this->params['surfer_handle'],
      'surfergroup_id' => (int)$this->params['surfergroup_id'],
      'surfer_givenname' => @(string)$this->params['surfer_givenname'],
      'surfer_surname' => @(string)$this->params['surfer_surname'],
      'surfer_email' => @(string)$this->params['surfer_email'],
      'surfer_gender' => @(string)$this->params['surfer_gender'],
      'surfer_avatar' => @(string)$this->params['surfer_avatar'],
      'surfer_password' => $this->getPasswordHash(@$this->params['surfer_password']),
      'surfer_registration' => $now,
      'surfer_lastmodified' => $now
    );
    // Check the surfer handle against black list
    if (!$this->checkHandle($data['surfer_handle'])) {
      return FALSE;
    }
    // Set value for surfer_valid only if password is set;
    // a surfer without a password will be invalid
    if (isset($this->params['surfer_password']) &&
        trim($this->params['surfer_password']) != '') {
      $data['surfer_valid'] = $this->params['surfer_valid'];
    } else {
      $data['surfer_valid'] = FALSE;
    }
    return $this->databaseInsertRecord($this->tableSurfer, NULL, $data);
  }

  /**
  * Load list
  *
  * Loads the surfer list
  * It's filtered by a pattern from the paramater patt
  *
  * @access public
  * @return boolean
  */
  function loadList() {
    $success = TRUE;
    unset($this->surfers);
    $this->surfers = array();
    $nameOperator = 'OR';
    if (isset($this->params['patt']) && trim($this->params['patt']) != '') {
      $patt = trim($this->params['patt']);

      if (FALSE == strpos($patt, '*')) {
        $patt .= '*';
      }
      $replaceChars = array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_');
      $patt = strtr($patt, $replaceChars);

      $splitPosition = strpos($patt, ',');
      if (FALSE !== $splitPosition) {
        $pattSurname = trim(substr($patt, 0, $splitPosition - 1));
        $pattGivenname = trim(substr($patt, $splitPosition + 1));
        $nameOperator = 'AND';
      } else {
        $pattSurname = $patt;
        $pattGivenname = $patt;
      }
      $patt = str_replace('%', '%%', $patt);
      $pattSurname = str_replace('%', '%%', $pattSurname);
      $pattGivenname = str_replace('%', '%%', $pattGivenname);
    } else {
      $patt = "%";
      $pattSurname = "%";
      $pattGivenname = "%";
    }

    if (isset($this->params['showgroup']) && $this->params['showgroup'] > 0) {
      $groupCondition = ' AND '.
        $this->databaseGetSQLCondition('surfergroup_id', (int)$this->params['showgroup']);
    } else {
      $adminGroups = $this->getAdminGroups();
      if ($adminGroups === NULL) {
        $groupCondition = '';
      } else {
        $allowedGroups = array(0);
        $groupsSql = "SELECT surfergroup_id, surfergroup_admin_group
                        FROM %s
                       WHERE ".str_replace(
          '%',
          '%%',
          $this->databaseGetSQLCondition(array('surfergroup_admin_group' => $adminGroups))
        );
        if ($res = $this->databaseQueryFmt($groupsSql, array($this->tableGroups))) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $allowedGroups[] = $row['surfergroup_id'];
          }
        }
        $groupCondition = ' AND '.
          $this->databaseGetSQLCondition(array('surfergroup_id' => $allowedGroups));
      }
    }
    $sortField = 'surfer_handle';
    if (isset($this->params['order'])) {
      if ($this->params['order'] == 'email') {
        $sortField = 'surfer_email';
      } elseif ($this->params['order'] == 'names') {
        $sortField = 'surfer_surname, surfer_givenname, surfer_handle';
      }
    }

    if (isset($this->params['listlength']) &&
        in_array($this->params['listlength'], array(10, 20, 50, 100))) {
      $this->listLength = $this->params['listlength'];
    }
    if (isset($this->params['status']) && $this->params['status'] != '') {
      $statusCondition = ' AND '.
        $this->databaseGetSQLCondition('surfer_valid', (int)$this->params['status']);
    } else {
      $statusCondition = '';
    }

    $onlineCondition = '';
    if (isset($this->params['online'])) {
      if ($this->params['online'] == 'online') {
        $onlineCondition = ' AND '.
          $this->databaseGetSQLCondition('surfer_status', 1);
      } elseif ($this->params['online'] == 'offline') {
        $onlineCondition = ' AND '.
          $this->databaseGetSQLCondition('surfer_status', 0);
      }
    }

    $sql = "SELECT surfer_id, surfer_handle, surfer_givenname, surfer_surname,
                   surfer_valid, surfer_status, surfer_email, surfergroup_id
              FROM %s
             WHERE (surfer_handle LIKE '%s'
                OR (surfer_surname LIKE '%s' $nameOperator surfer_givenname LIKE '%s')
                OR surfer_email LIKE '%s'
                   ) $groupCondition $statusCondition $onlineCondition
             ORDER BY %s";

    $params = array(
      $this->tableSurfer,
      $patt,
      $pattSurname,
      $pattGivenname,
      $patt,
      $sortField
    );
    $res = $this->databaseQueryFmt(
      $sql,
      $params,
      $this->listLength,
      isset($this->params['offset']) ? (int)$this->params['offset'] : NULL
    );
    if ($res) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        array_push($this->surfers, $row);
      }
      $this->surferCount = (int)$res->absCount();
      return $this->loadPermList();
    }
    return $success;
  }

  /**
  * Load permissions
  *
  * Loads a permission by id
  * Returns the permission from the permissionList attribute
  * if it's already there
  * Otherwise, it looks up the permission in the surferperm table
  * If all else fails, it returns null
  *
  * @param integer $id
  * @access public
  * @return mixed NULL or array
  */
  function loadPerm($id) {
    if (isset($this->permissionsList) && is_array($this->permissionsList) &&
          isset($this->permissionsList[$id])) {
      return $this->permissionsList[$id];
    } else {
      $sql = "SELECT surferperm_id, surferperm_title, surferperm_active
                FROM %s
               WHERE surferperm_id = '%d'";
      if ($res = $this->databaseQueryFmt($sql, array($this->tablePerm, $id))) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          return $row;
        }
      }
    }
    return NULL;
  }

  /**
  * Save permissions
  *
  * Reads permission data from the permission form
  * and stores it in the surferperm database table
  * Returns true on success, otherwise false
  *
  * @access public
  * @return boolean
  */
  function savePerm() {
    $data = array(
      'surferperm_title' => $this->params['surferperm_title'],
      'surferperm_active' => $this->params['surferperm_active']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tablePerm,
      $data,
      'surferperm_id',
      $this->editPerm['surferperm_id']
    );
  }

  /**
  * Permission exists ?
  *
  * Checks whether a certain permission title is already in use,
  * referenced by a 'like' pattern on its title and id
  * Returns true if the title of the examined permission
  * is identical to an existing one
  * (except the current permission itself, of course).
  * Otherwise, it returns false
  *
  * @param string $title surfer permission title
  * @param integer $id surfer permission id
  * @access public
  * @return boolean
  */
  function permExists($title, $id) {
    $sql = "SELECT surferperm_id
              FROM %s
             WHERE surferperm_title LIKE '%s'";
    if ($res = $this->databaseQueryFmt($sql, array($this->tablePerm, $title))) {
      if ($permId = $res->fetchField()) {
        return ($permId != $id);
      }
    }
    return FALSE;
  }

  /**
  * Load group
  *
  * Loads data of a surfer group by group id
  * and stores it in the surferGroup attribute
  * Tries to look up the id in the groupList array
  * attribute first or in the surfergroups db table
  * Returns true if the group was found in either of them,
  * or false if the group doesn't exist
  *
  * @param integer $id surfer group id
  * @access public
  * @return boolean
  */
  function loadGroup($id) {
    unset($this->surferGroup);
    if (isset($this->groupList[$id]) && is_array($this->groupList[$id])) {
      $this->surferGroup = $this->groupList[$id];
      return TRUE;
    } else {
      $sql = "SELECT surfergroup_id, surfergroup_title,
                     surfergroup_profile_page, surfergroup_redirect_page,
                     surfergroup_admin_group, surfergroup_identifier
                FROM %s
               WHERE surfergroup_id = '%d'";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableGroups, $id))) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->surferGroup = $row;
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Add group
  *
  * Inserts a new group into the surfergroups db table
  *
  * @access public
  * @return mixed integer insered record id or boolean FALSE error
  */
  function addGroup() {
    return $this->databaseInsertRecord(
      $this->tableGroups,
      'surfergroup_id',
      array(
        'surfergroup_title' => $this->_gt('New group'),
        'surfergroup_profile_page' => 0,
        'surfergroup_redirect_page' => 0,
        'surfergroup_admin_group' => 0,
        'surfergroup_identifier' => ''
      )
    );
  }

  /**
  * Save group
  *
  * Stores changes to a group in surfergroups db table
  *
  * @access public
  * @return mixed affected records or boolean FALSE error
  */
  function saveGroup() {
    if (empty($this->params['surfergroup_profile_page'])) {
      $profilePage = 0;
    } else {
      $profilePage = (int)$this->params['surfergroup_profile_page'];
    }
    if (empty($this->params['surfergroup_redirect_page'])) {
      $redirectPage = 0;
    } else {
      $redirectPage = (int)$this->params['surfergroup_redirect_page'];
    }
    if (empty($this->params['surfergroup_admin_group'])) {
      $adminGroup = 0;
    } else {
      $adminGroup = (int)$this->params['surfergroup_admin_group'];
    }
    if (empty($this->params['surfergroup_identifier'])) {
      $identifier = '';
    } else {
      $idFromIdentifier = $this->getGroupIdByIdentifier($this->params['surfergroup_identifier']);
      if ($idFromIdentifier != 0 &&
          $idFromIdentifier != $this->surferGroup['surfergroup_id']) {
        $identifier = '';
      } else {
        $identifier = $this->params['surfergroup_identifier'];
      }
    }
    $data = array(
      'surfergroup_title' => $this->params['surfergroup_title'],
      'surfergroup_profile_page' => $profilePage,
      'surfergroup_redirect_page' => $redirectPage,
      'surfergroup_admin_group' => $adminGroup,
      'surfergroup_identifier' => $identifier
    );
    return (
      $this->databaseUpdateRecord(
        $this->tableGroups,
        $data,
        'surfergroup_id',
        $this->surferGroup['surfergroup_id']
      ) !== FALSE
    );
  }

  /**
  * Delete group
  *
  * Deletes a group, specified by its id, from surfergoups db table
  * as well as its permission links from surferlinks table
  * Returns true if deletion succeeds,
  * otherwise false
  *
  * @param integer $groupId
  * @access public
  * @return boolean
  */
  function deleteGroup($groupId) {
    if (FALSE !== $this->databaseDeleteRecord(
          $this->tableLink, 'surfergroup_id', $groupId)) {
      if (FALSE !== $this->databaseDeleteRecord(
            $this->tableGroups, 'surfergroup_id', $groupId)) {
        $this->addMsg(MSG_INFO, $this->_gt('Permission links and group deleted.'));
        return TRUE;
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Permission links deleted. Could not delete group.'));
      }
    }
    return FALSE;
  }

  /**
  * Delete permission
  *
  * Deletes a permission, specified by its id, from surferperms db table
  * Returns true on success, otherwise false
  *
  * @param integer $permId
  * @access public
  * @return boolean
  */
  function deletePerm($permId) {
    if (FALSE !== $this->databaseDeleteRecord(
          $this->tableLink, 'surfer_permid', $permId)) {
      if (FALSE !== $this->databaseDeleteRecord(
            $this->tablePerm, 'surferperm_id', $permId)) {
        return TRUE;
      }
    }
  }

  /**
  * Adds a surfer to the editor user database table
  *
  * Adds a surfer, specified by his/her id, to editors
  * (i.e. the authuser table)
  * Returns true if it works or false if not
  *
  * @param $userId
  * @access public
  * @return TRUE if new editor user could be added, otherwise FALSE
  */
  function addEditorUser() {
    // Create a random unique id for the editor user
    $newUserId = $this->createSurferId();
    // Record data
    $data = array(
      'user_id' => $newUserId,
      'username' => $this->editSurfer['surfer_handle'],
      'givenname' => $this->editSurfer['surfer_givenname'],
      'surname' => $this->editSurfer['surfer_surname'],
      'email' => $this->editSurfer['surfer_email'],
      'group_id' => '0',
      'active' => FALSE,
      'start_node' => 0,
      'sub_level' => 0,
      'user_password' => $this->editSurfer['surfer_password']
    );
    // Attempt to insert record, report success/failure
    if (FALSE !== $this->databaseInsertRecord($this->tableAuthuser, NULL, $data)) {
      $authUserData = array('auth_user_id' => $newUserId);
      $updated = $this->databaseUpdateRecord(
        $this->tableSurfer, $authUserData, 'surfer_id', $this->editSurfer['surfer_id']
      );
      if (FALSE !== $updated) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Load links
  *
  * Loads the permissions linked to a group,
  * identified by group id
  * Stores these links in the linkList attribute
  * Doesn't return anything because it's not an error
  * if no permissions are linked to a certain group
  *
  * @access public
  * @param integer $groupId
  * @return void
  */
  function loadLinks($groupId) {
    unset($this->linkList);
    $sql = "SELECT surfer_permid
              FROM %s
             WHERE surfergroup_id = '%d'";
    $params = array($this->tableLink, $groupId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->linkList[$row['surfer_permid']] = TRUE;
      }
    }
  }

  /**
  * Check permission inputs
  *
  * @access public
  * @return boolean
  */
  function checkPermInputs() {
    $result = $this->permExists(
      $this->params['surferperm_title'],
      $this->editPerm['surferperm_id']
    );
    if ($result) {
      return FALSE;
      $this->addMsg(MSG_ERROR, "Permission title already in use.");
    }
    return TRUE;
  }

  /**
  * Check inputs
  *
  * Check basic data for new or updated surfer:
  * - Password and password repetition must match
  * - Surfer handle must be unique
  * - Email address must be unique (except in 'ignore illegal' mode)
  * Diplay appropriate messages and return false
  * if any of these rules fail
  * Otherwise return true
  *
  * @access public
  * @param boolean $ignoreIllegal optional, default FALSE
  * @return boolean
  */
  function checkInputs($ignoreIllegal = FALSE) {
    // Assume that all inputs are correct
    $result = TRUE;
    // Compare password and its repetition
    if ($this->params['surfer_password'] != $this->params['surfer_password2']) {
      $result = FALSE;
      $this->addMsg(MSG_ERROR, $this->_gt('Password inputs didn\'t match.'));
    }
    // Check whether a changed surfer handle exists or not
    if ($this->editSurfer['surfer_handle'] != $this->params['surfer_handle'] &&
        ($this->getIdByHandle($this->params['surfer_handle'], TRUE) != '')) {
      $result = FALSE;
      $this->addMsg(MSG_ERROR, $this->_gt('Login name already in use.'));
    }
    // Check whether a changed email address
    // (with more than just a change of capitalization)
    // exists or not
    if ($ignoreIllegal == FALSE) {
      if (strtolower($this->editSurfer['surfer_email']) !=
          strtolower($this->params['surfer_email']) &&
          $this->existEmail($this->params['surfer_email'], TRUE)) {
        $result = FALSE;
        $this->addMsg(MSG_ERROR, $this->_gt('Email address already in use.'));
      }
    }
    // Return the final test result
    return $result;
  }

  /**
  * Get edit profile form
  *
  * Creates the XML to display the form
  * to edit the definition of a dynamic profile data field
  * The first argument is a reference to page layout,
  * the second (optional) one determines whether a new
  * field is created (TRUE) or whether an existing one
  * is edited (FALSE)
  *
  * @param object xsl_layout $layout
  * @param bool optional $new
  *
  */
  function getEditProfileForm(&$layout, $new = FALSE) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    // Get out and display error msg if trying to edit unavailable field
    if (!$new &&
        empty($this->params['data_id'])) {
      $this->addMsg(MSG_ERROR, 'The requested field does not exist.');
      return;
    }
    // Get a translatable string in advance to avoid nested queries
    $untitledCategory = $this->_gt('Untitled category');
    // Get profile data categories from database
    $classes = array();
    $sql = "SELECT surferdataclass_id
              FROM %s";
    $sqlParams = array($this->tableDataClasses);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $classes[$row['surferdataclass_id']] = '';
      }
    }
    // Exit with error if there are no categories yet
    if (sizeof($classes) == 0) {
      $this->addMsg(MSG_ERROR, 'Create categories first.');
      return;
    }
    // Now try to get the titles for the categories
    foreach ($classes as $classId => $str) {
      $sql = "SELECT surferdataclasstitle_name,
                     surferdataclasstitle_lang,
                     surferdataclasstitle_classid
                FROM %s
               WHERE surferdataclasstitle_classid = %d
                 AND surferdataclasstitle_lang = %d";
      $sqlParams = array($this->tableDataClassTitles,
                         $classId,
       $this->lngSelect->currentLanguageId
      );
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $classes[$classId] = $row['surferdataclasstitle_name'];
        }
      }
      if ($classes[$classId] == '') {
        $classes[$classId] = sprintf('[%s %s]', $untitledCategory, $classId);
      }
    }
    // Define form fields
    $fields = array(
      'field_name' => array(
        'Field Name', 'isNoHMTL', TRUE, 'input', 200, ''
      ),
      'field_class' => array(
        'Category', 'isNum', TRUE, 'combo', $classes
      ),
      'field_available' => array(
        'Available', 'isNum', TRUE, 'yesno', '', '', 0
      ),
      'field_mandatory' => array(
        'Mandatory', 'isNum', TRUE, 'yesno', '', '', 0
      ),
      'field_needsapproval' => array(
        'Needs approval', 'isNum', TRUE, 'yesno', '', '', 0
      ),
      'field_approved' => array(
        'Approval default', 'isNum', TRUE, 'yesno', '', '', 0
      ),
      'Field Properties',
      'field_check' => array(
        'Check', 'isAlphaChar', TRUE, 'function', 'getCheckFunctionsCombo', '', ''),
      'field_pcre' => array(
        'PCRE', 'isSomeText', FALSE, 'input', 200,
        'Enter your own regular expression if the above field is set to [PCRE]'
      ),
      'field_type' => array('Field type', 'isNoHTML', TRUE, 'function', 'getFieldType', '')
    );
    // Declare arrays for current form data and hidden fields
    $data = array();
    $hidden = array(
      'mode' => 2,
      'cmd' => 'save_field',
    );
    // Get Values from db if editing an existing field
    if (!$new) {
      // This is not a new field, so the hidden create field needs to be 0
      $hidden['create'] = 0;
      // Store current field id both in a hidden form field
      // and in a local variable
      $hidden['data_id'] = $this->params['data_id'];
      $id = $this->params['data_id'];
      // Try to read current field from surferdata database table
      $sql = "SELECT s.surferdata_name, s.surferdata_class, s.surferdata_type,
                     s.surferdata_values, s.surferdata_check,
                     s.surferdata_available, s.surferdata_mandatory,
                     s.surferdata_needsapproval,
                     s.surferdata_approvaldefault
                FROM %s AS s
               WHERE s.surferdata_id = %d";
      $res = $this->databaseQueryFmt(
        $sql, array($this->tableData, $this->params['data_id'])
      );
      // Check whether there's a record
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        // Store current field's data if present
        $data = array(
          'field_name' => $row['surferdata_name'],
          'field_class' => $row['surferdata_class'],
          'field_type' => $row['surferdata_type'],
          'field_params' => $row['surferdata_values'],
          'field_check' => $row['surferdata_check'],
          'field_available' => $row['surferdata_available'],
          'field_mandatory' => $row['surferdata_mandatory'],
          'field_needsapproval' => $row['surferdata_needsapproval'],
          'field_approved' => $row['surferdata_approvaldefault']
        );
        // Set check to PCRE and add PCRE value if appropriate
        if (!$this->isCheckFunction($data['field_check'])) {
          $data['field_pcre'] = $data['field_check'];
          $data['field_check'] = -1;
        }
        // Store current category id in a hidden field for order fix
        $hidden['field_class_old'] = $data['field_class'];
      } else {
        // Exit with an error message if there's no data for the field id
        $this->addMsg(MSG_ERROR, 'Database error!');
        return;
      }
    } else {
      // This is a new field, so the hidden create field needs to be 1
      $hidden['create'] = 1;
    }
    // Title handling -- not for new a field
    $hasTitles = FALSE;
    if (!$new) {
      // Create list of titles for the current field
      $titleList = sprintf(
        '<listview title="%s %s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Titles for')),
        papaya_strings::escapeHTMLChars($row['surferdata_name'])
      );
      $titleList .= '<cols>'.LF;
      $titleList .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Title'))
      );
      $titleList .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Language'))
      );
      $titleList .= '<col/>'.LF;
      $titleList .= '<col/>'.LF;
      $titleList .= '</cols>'.LF;
      $titleList .= '<items>'.LF;
      // Try to read all titles for current field from database
      $sql = "SELECT st.surferdatatitle_id, st.surferdatatitle_title,
                     st.surferdatatitle_lang
                FROM %s AS st
               WHERE st.surferdatatitle_field=%d
               ORDER BY st.surferdatatitle_lang ASC";
      $ires = $this->databaseQueryFmt($sql, array($this->tableDataTitles, @$id));
      // Save results in an array to avoid nested queries
      $irows = array();
      while ($irow = $ires->fetchRow(DB_FETCHMODE_ASSOC)) {
        $irows[] = $irow;
      }
      // Check whether there are titles at all
      $hasTitles = (sizeof($irows) > 0);
      // Only display a title list if there are any titles
      if ($hasTitles) {
        // For each title ...
        foreach ($irows as $irow) {
          // Here, we do know that there's at least one title
          $hasTitles = TRUE;
          // Create listview with titles and their edit/delete links
          $titleList .= sprintf(
            '<listitem title="%s">'.LF,
            papaya_strings::escapeHTMLChars($irow['surferdatatitle_title'])
          );
          $titleList .= sprintf(
            '<subitem>%s</subitem>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLanguageTitle($irow['surferdatatitle_lang'])
            )
          );
          $edit_href = $this->getLink(
            array(
              'mode' => 2,
              'title_id' => $irow['surferdatatitle_id'],
              'data_id' => $this->params['data_id'],
              'cmd' => 'edit_title'
            )
          );
          $del_href = $this->getLink(
            array(
              'mode' => 2,
              'title_id' => $irow['surferdatatitle_id'],
              'data_id' => $this->params['data_id'],
              'cmd' => 'del_title'
            )
          );
          $titleList .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($edit_href),
            papaya_strings::escapeHTMLChars($this->images['actions-edit']),
            papaya_strings::escapeHTMLChars($this->_gt('Edit'))
          );
          $titleList .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($del_href),
            papaya_strings::escapeHTMLChars($this->images['places-trash']),
            papaya_strings::escapeHTMLChars($this->_gt('Delete'))
          );
          $titleList .= '</listitem>'.LF;
        }
        $titleList .= '</items>'.LF;
        $titleList .= '</listview>'.LF;
      }
    }
    // Create the form
    $profileForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    // Check whether this is a new record or not
    // and set dialog title correspondingly
    if ($new) {
      $profileForm->dialogTitle = $this->_gt('Add profile data field');
    } else {
      $profileForm->dialogTitle = $this->_gt('Edit profile data field');
    }
    $profileForm->baseLink = $this->baseLink;
    $profileForm->msgs = &$this->msgs;
    $profileForm->loadParams();
    $layout->add($profileForm->getDialogXML());
    // Check whether there are titles
    if ($hasTitles) {
      // Display titles if available
      $layout->add($titleList);
    } elseif (!$new) {
      // Otherwise, for an existing field, display message that there are no titles
      $this->addMsg(MSG_INFO, $this->_gt('No titles yet.'));
    }
  }

  /**
  * Get edit profile category form
  *
  * Creates the form to edit a profile data category
  * and its multilingual titles
  * The first, mandatory paramter is a reference to
  * page layout, while the second, optional one
  * is a boolean that determines whether a new category is created (TRUE)
  * or whether an existing one is edited (FALSE)
  *
  * @access public
  * @param object xsl_layout $layout
  * @param bool optional $new
  */
  function getEditProfileCategoryForm(&$layout, $new = FALSE) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    // Display error msg and get out if trying to edit unavailable category
    if (!$new &&
        (!isset($this->params['class_id']) || $this->params['class_id'] == '')) {
      $this->addMsg(MSG_ERROR, 'The requested category does not exist.');
      return;
    } else {
      // Generate form fields
      $fields = array(
        'perm' => array('Permission', 'isNum', TRUE, 'function', 'getPermCombo'),
        'Titles'
      );
      // Add fields for titles in all available frontend languages
      $languages = $this->getLanguageSelector();
      foreach ($languages as $id => $title) {
        $fields['lang_'.$id] = array(
          $title, 'isNoHTML', TRUE, 'input', 30, 'Enter title or remove text to delete it'
        );
      }
      $data = array();
      // Hidden fields: mode 2 (profile data), cmd 'save_class'
      $hidden = array(
        'mode' => 2,
        'cmd' => 'save_class'
      );
      // Check whether this is a new category
      if (!$new) {
        // Existing category
        // Set hidden create parameter to 0
        $hidden['create'] = 0;
        // Set hidden class_id parameter
        $hidden['class_id'] = $this->params['class_id'];
        // Get data for current category from database
        $sql = "SELECT surferdataclass_perm
                  FROM %s WHERE surferdataclass_id=%d";
        $res = $this->databaseQueryFmt(
          $sql,
          array($this->tableDataClasses, $this->params['class_id'])
        );
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data['perm'] = $row['surferdataclass_perm'];
        }
        // Get titles for current category from database
        $sql = "SELECT surferdataclasstitle_lang,
                       surferdataclasstitle_name
                  FROM %s
                 WHERE surferdataclasstitle_classid=%d";
        $res = $this->databaseQueryFmt(
          $sql,
          array($this->tableDataClassTitles, $this->params['class_id'])
        );
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data['lang_'.$row['surferdataclasstitle_lang']] =
            $row['surferdataclasstitle_name'];
        }
      } else {
        // New category
        // Set hidden create parameter to 1;
        // do nothing else because there is no data yet
        $hidden['create'] = 1;
      }
      // Create form
      $categoryForm = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      // Set different form titles for new or existing category,
      // respectively
      if ($new) {
        $categoryForm->dialogTitle =
          $this->_gt('Add profile data category');
      } else {
        $categoryForm->dialogTitle =
          $this->_gt('Edit profile data category');
      }
      $categoryForm->msgs = &$this->msgs;
      $categoryForm->loadParams();
      $layout->add($categoryForm->getDialogXML());
    }
  }

  /**
  * get Delete Favorites Form
  *
  * Displays a security question before deleting the favorite list
  * The only parameter is a reference to the page layout
  *
  * @access public
  * @param object xsl_layout $layout
  */
  function getDelFavoritesForm(&$layout) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    // Display the question only if the confirm_delete parameter
    // has not been set by a previous run of this method
    if ((!isset($this->params['confirm_delete']))) {
      $hidden = array(
        'mode' => 0,
        'cmd' => 'del_fav_list',
        'confirm_delete' => 1
      );
      $msg = $this->_gt('Do you really want to delete the surfer favorite list?');
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * get Delete Profile Form
  *
  * Displays a security question before deleting a profile field
  * The only parameter is a reference to the page layout
  *
  * @access public
  * @param object xsl_layout $layout
  */
  function getDelProfileForm(&$layout) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    // Display the question only if the confirm_delete parameter
    // has not been set by a previous run of this method
    // and if there's a field id to delete
    if ((!isset($this->params['confirm_delete'])) &&
         isset($this->params['data_id'])) {
      $hidden = array(
        'mode' => 2,
        'cmd' => 'del_field',
        'confirm_delete' => 1,
        'data_id' => $this->params['data_id']
      );
      $msg = $this->_gt('Do you really want to delete the profile data field?');
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * get Delete Title form
  *
  * Displays a security question before deleting a profile title
  * The only parameter is a reference to the page layout
  * Returns a boolean value to determine whether the form
  * is displayed (TRUE) or not (FALSE) -- if it doesn't have
  * to be displayed any more, the profile field form will
  * be shown instead
  *
  * @access public
  * @param object xsl_layout $layout
  * @return boolean
  */
  function getDelTitleForm(&$layout) {
    // Display the question only if the confirm_delete parameter
    // has not been set by a previous run of this method
    // and if there's a title id to delete
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    if ((!isset($this->params['confirm_delete'])) &&
          isset($this->params['title_id'])) {
      $hidden = array(
        'mode' => 2,
        'cmd' => 'del_title',
        'confirm_delete' => 1,
        'title_id' => $this->params['title_id'],
        'data_id' => $this->params['data_id']
      );
      $msg = $this->_gt('Do you reallly want to delete the profile data title?');
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
      // Report that the form has been displayed
      return TRUE;
    }
    // Here, we can state that it hasn't been displayed
    return FALSE;
  }

  /**
  * get Delete Category form
  *
  * Displays a security question before deleting a profile category,
  * or an error message if trying to delete a category with
  * existing fields
  * The only parameter is a reference to the page layout
  *
  * @access public
  * @param object xsl_layout $layout
  */
  function getDelProfileCategoryForm(&$layout) {
    // Display the question only if the confirm_delete parameter
    // has not been set by a previous run of this method
    // and if there's a category id to delete
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    if ((!isset($this->params['confirm_delete'])) &&
          isset($this->params['class_id'])) {
      // Check whether there are fields with this category
      $sql = "SELECT COUNT(*) AS num
                FROM %s
               WHERE surferdata_class = %d";
      $res = $this->databaseQueryFmt(
        $sql,
        array($this->tableData, $this->params['class_id'])
      );
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (@(int)$row['num'] > 0) {
          // Yes, there are such fields:
          // Display error message and get out of here
          $this->addMsg(
            MSG_WARNING,
            $this->_gt('Can\'t delete category with existing fields.')
          );
          return;
        }
      }
      $hidden = array(
        'mode' => 2,
        'cmd' => 'del_class',
        'confirm_delete' => 1,
        'class_id' => $this->params['class_id']
      );
      $msg = $this->_gt('Delete profile data category?');
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get edit title form
  *
  * Displays a form to edit an existing profile data field title
  * or to create a new one.
  * The first, mandatory argument is a reference to the page layout
  * The second, optional one determines whether this is a new title (TRUE)
  * or not (FALSE, which is the default value)
  *
  * @param object xsl_layout $layout
  * @param bool $new
  * @return string
  */
  function getEditTitleForm(&$layout, $new = FALSE) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    // Get out and display error msg if trying to edit unavailable field
    if (!$new &&
        (!isset($this->params['title_id']) || $this->params['title_id'] == '')) {
      $this->addMsg(MSG_ERROR, 'The requested title does not exist.');
      return;
    }
    // Get language selector array
    $languages = $this->getLanguageSelector();
    // Define form fields
    $fields = array(
      'field' => array('Field', 'isNoHTML', FALSE, 'disabled_input',
                          200, ''),
      'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, ''),
      'language' => array('Language', 'isNoHTML', TRUE, 'combo',
                          $languages)
    );
    // Define hidden parameters:
    // mode 2 (profile data), cmd 'save_title'
    $hidden = array(
      'mode' => 2,
      'cmd' => 'save_title',
      'data_id' => $this->params['data_id']
    );
    // Check whether we are editing an existing title
    // or creating a new one
    if (!$new) {
      // Existing title
      // Set hidden parameter create to 0
      $hidden['create'] = 0;
      // Set hidden parameter title_id to its former value
      $hidden['title_id'] = $this->params['title_id'];
      // Read current title, language and field name from database
      $sql = "SELECT st.surferdatatitle_title,
                     st.surferdatatitle_lang,
                     s.surferdata_name
                FROM %s AS st, %s AS s
               WHERE st.surferdatatitle_id=%d
                 AND st.surferdatatitle_field=s.surferdata_id";
      $res = $this->databaseQueryFmt(
        $sql,
        array($this->tableDataTitles, $this->tableData, $this->params['title_id'])
      );
      // Set data for the form fields
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $data['field'] = papaya_strings::escapeHTMLChars($row['surferdata_name']);
        $data['title'] = $row['surferdatatitle_title'];
        $data['language'] = $row['surferdatatitle_lang'];
      } else {
        $this->addMsg(MSG_ERROR, 'Database error!');
        return;
      }
    } else {
      // New title
      // Set hidden paramater create to 1
      $hidden['create'] = 1;
      // Set hidden parameter data_id (profile data field id)
      // to its former value
      $hidden['data_id'] = $this->params['data_id'];
      // Read field name from database
      $sql = "SELECT surferdata_name
                FROM %s
               WHERE surferdata_id=%d";
      $res = $this->databaseQueryFmt(
        $sql,
        array($this->tableData, $this->params['data_id'])
      );
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        // Set data for the (uneditable) name form field
        $data['field'] =
          papaya_strings::escapeHTMLChars($row['surferdata_name']);
      } else {
        $this->addMsg(MSG_ERROR, 'Database error!');
        return;
      }
      // Set default value of the language selector to
      // currently selected content language
      $data['language'] = $this->lngSelect->currentLanguageId;
    }
    // Create dialog
    $titleForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    // Set different dialog titles for new or existing title, respectively
    if ($new) {
      $titleForm->dialogTitle = $this->_gt('Add data field title');
    } else {
      $titleForm->dialogTitle = $this->_gt('Edit data field title');
    }
    $titleForm->baseLink = $this->baseLink;
    $titleForm->msgs = &$this->msgs;
    $titleForm->loadParams();
    $layout->add($titleForm->getDialogXML());
  }

  /**
  * Get edit field properties form
  *
  * Displays a form to edit the properties for a profile data field.
  * The only argument is a reference to the page layout
  *
  * @param object xsl_layout $layout
  * @param bool $new
  * @return string
  */
  function getEditFieldPropertiesForm(&$layout) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    // Get out of here with an error message if we do not have a field id
    if (!(isset($this->params['data_id']) && is_numeric($this->params['data_id']))) {
      $this->addMsg(MSG_ERROR, 'The requested field does not exist.');
      return;
    }
    // Get language selector array
    $languages = $this->getLanguageSelector();
    // Get the current field's name, type, and existing properties
    $sql = "SELECT surferdata_name,
                   surferdata_type,
                   surferdata_values,
                   surferdata_id
              FROM %s
             WHERE surferdata_id = %d";
    $sqlParams = array($this->tableData, $this->params['data_id']);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fieldName = $row['surferdata_name'];
        $fieldType = $row['surferdata_type'];
        $fieldValues = $row['surferdata_values'];
      }
    }
    // If we do not have a name and a type, this seems to be an invalid field
    if (!(isset($fieldName) && isset($fieldType))) {
      $this->addMsg(MSG_ERROR, 'The requested field does not exist.');
      return;
    }
    if (!isset($fieldValues)) {
      $fieldValues = '';
    }
    if (trim($fieldValues) != '') {
      // Remove inappropriate values that might occur after field type changes
      if ($fieldType == 'input' || $fieldType == 'textarea') {
        if (!is_numeric($fieldValues)) {
          $fieldValues = '';
        }
      } else {
        if (is_numeric($fieldValues)) {
          $fieldValues = '';
        }
      }
    }
    // Create input area according to the field type
    $simpleForm = TRUE;
    if ($fieldType == 'input') {
      $title = $this->_gt('Maximum number of characters');
      $check = 'isNum';
      $type = 'input';
      $size = 50;
      $hint = '';
      $default = $this->getProperty('INPUT_MAXLENGTH', 50);
    } elseif ($fieldType == 'textarea') {
      $title = $this->_gt('Number of rows');
      $check = 'isNum';
      $type = 'input';
      $size = 10;
      $hint = '';
      $default = $this->getProperty('TEXTAREA_LINES', 5);
    } elseif ($fieldType == 'function') {
      $title = $this->_gt('Callback function');
      $check = 'isNoHTML';
      $type = 'input';
      $size = 50;
      $hint = 'Please make sure to actually provide this function';
      $default = '';
    } else {
      // Build a convenient editor for multi-valued fields
      $simpleForm = FALSE;
      // Get the current value array from the XML
      if (trim($fieldValues) != '') {
        $values = $this->parseFormValueXML(
          $fieldValues,
          $this->lngSelect->currentLanguageId
        );
      }
      $listView = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars(
          sprintf(
            $this->_gt('Edit field properties for "%s"'),
            $fieldName
          )
        )
      );
      $listView .= '<buttons>'.LF;
      $newLink = $this->getLink(
        array(
          'mode' => 2,
          'cmd' => 'add_value',
          'data_id' => $this->params['data_id']
        )
      );
      $listView .= sprintf(
        '<button href="%s" glyph="%s" title="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($newLink),
        papaya_strings::escapeHTMLChars($this->images['actions-table-row-add']),
        papaya_strings::escapeHTMLChars($this->_gt('Add value'))
      );
      $listView .= '</buttons>';
      $listView .= '<cols>'.LF;
      $listView .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Value'))
      );
      $listView .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Caption'))
      );
      $listView .= '<col/>'.LF;
      $listView .= '<col/>'.LF;
      $listView .= '<col/>'.LF;
      $listView .= '<col/>'.LF;
      $listView .= '</cols>'.LF;
      if (isset($values) && !empty($values)) {
        $listView .= '<items>'.LF;
        $run = 0;
        foreach ($values as $val => $caption) {
          $run++;
          $listView .= sprintf(
            '<listitem title="%s">'.LF,
            papaya_strings::escapeHTMLChars($val)
          );
          $listView .= sprintf(
            '<subitem>%s</subitem>'.LF,
            papaya_strings::escapeHTMLChars($caption)
          );
          $moveUpLink = $this->getLink(
            array(
              'mode' => 2,
              'dir' => -1,
              'cmd' => 'move_value',
              'data_id' => $this->params['data_id'],
              'val' => $val
            )
          );
          $moveDownLink = $this->getLink(
            array(
              'mode' => 2,
              'dir' => 1,
              'cmd' => 'move_value',
              'data_id' => $this->params['data_id'],
              'val' => $val
            )
          );
          $editLink = $this->getLink(
            array(
              'mode' => 2,
              'cmd' => 'edit_value',
              'data_id' => $this->params['data_id'],
              'val' => $val
            )
          );
          $delLink = $this->getLink(
            array(
              'mode' => 2,
              'cmd' => 'del_value',
              'data_id' => $this->params['data_id'],
              'val' => $val
            )
          );
          if ($run > 1) {
            $listView .= sprintf(
              '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
              papaya_strings::escapeHTMLChars($moveUpLink),
              papaya_strings::escapeHTMLChars($this->images['actions-go-up']),
              papaya_strings::escapeHTMLChars($this->_gt('Move up'))
            );
          } else {
            $listView .= '<subitem/>'.LF;
          }
          if ($run < sizeof($values)) {
            $listView .= sprintf(
              '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
              papaya_strings::escapeHTMLChars($moveDownLink),
              papaya_strings::escapeHTMLChars($this->images['actions-go-down']),
              papaya_strings::escapeHTMLChars($this->_gt('Move down'))
            );
          } else {
            $listView .= '<subitem/>'.LF;
          }
          $listView .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($editLink),
            papaya_strings::escapeHTMLChars($this->images['actions-edit']),
            papaya_strings::escapeHTMLChars($this->_gt('Edit'))
          );
          $listView .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($delLink),
            papaya_strings::escapeHTMLChars($this->images['places-trash']),
            papaya_strings::escapeHTMLChars($this->_gt('Delete'))
          );
          $listView .= '</listitem>';
        }
        $listView .= '</items>'.LF;
      }
      $listView .= '</listview>'.LF;
      $layout->add($listView);
    }
    // Display a simple form for textareas or inputs
    if ($simpleForm) {
      $fields = array(
        'values' => array($title, $check, FALSE, $type, $size, $hint, $default)
      );
      $data = array();
      if (trim($fieldValues) != '') {
        $data['values'] = $fieldValues;
      }
      // Define hidden parameters:
      // mode 2 (profile data), cmd 'save_props'
      $hidden = array(
        'mode' => 2,
        'cmd' => 'save_props',
        'data_id' => $this->params['data_id']
      );
      // Create dialog
      $propsForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $propsForm->dialogTitle = sprintf($this->_gt('Edit field properties for "%s"'), $fieldName);
      $propsForm->baseLink = $this->baseLink;
      $propsForm->msgs = &$this->msgs;
      $propsForm->loadParams();
      $layout->add($propsForm->getDialogXML());
    }
  }

  /**
  * Get edit form for a multi-value field value
  *
  * Creates a dialog to add a new or edit an existing
  * value for a multi-value dynamic profile data field
  * (i.e. combo, radio, checkgroup)
  *
  * @access public
  * @param object xsl_layout $layout
  * @param boolean $new optional
  */
  function getEditValueForm(&$layout, $new = FALSE) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    // Reject if no field is selected
    if (!isset($this->params['data_id'])) {
      return;
    }
    // Reject if editing an existing value with no val parameter given
    if (!($new || isset($this->params['val']))) {
      $this->addMsg(MSG_ERROR, $this->_gt('Please specify the value you want to edit.'));
      return;
    }
    // Get field information from database
    $sql = "SELECT surferdata_name, surferdata_type,
                   surferdata_values, surferdata_id
              FROM %s
             WHERE surferdata_id = %d";
    $sqlParams = array($this->tableData, $this->params['data_id']);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fieldName = $row['surferdata_name'];
        $fieldType = $row['surferdata_type'];
        $fieldValues = @$row['surferdata_values'];
      }
    }
    // Reject if the type is inappropriate
    if ($fieldType == 'input' || $fieldType == 'textarea') {
      $this->addMsg(MSG_WARNING, $this->_gt('You cannot add values for a field of this type.'));
      return;
    }
    // Parse existing values
    if (isset($fieldValues) && trim($fieldValues) != '') {
      $values = $this->parseFormValueXML($fieldValues, 0, TRUE);
    }
    // Reject if we try to edit an existing value that does not exist
    if ($new == FALSE) {
      $val = $this->params['val'];
      if (!(isset($values) && isset($values[$val]))) {
        $this->addMsg(MSG_ERROR, $this->_gtf('The value "%s" does not exist', $val));
      }
    }
    // Generate the form
    $hidden = array(
      'mode' => 2,
      'cmd' => 'save_value',
      'data_id' => $this->params['data_id']
    );
    if ($new == TRUE) {
      $hidden['create'] = 1;
    } else {
      $hidden['oldval'] = $val;
    }
    $fields = array(
      'val' => array('Value', 'isNoHTML', TRUE, 'input', 50),
      'Captions'
    );
    // Add fields for captions in all available frontend languages
    $languages = $this->getLanguageSelector();
    $languageShorts = $this->getLanguageSelector(TRUE, TRUE);
    foreach ($languages as $id => $title) {
      $fields['lang_'.$id] = array($title, 'isNoHTML', TRUE, 'input', 50,
        'Enter caption or remove text to delete it.');
    }
    // Get existing data
    $data = array();
    if ($new == FALSE) {
      $data['val'] = $val;
      if (is_array($values[$val])) {
        foreach ($languageShorts as $id => $langShort) {
          if (isset($fields['lang_'.$id]) && isset($values[$val][$langShort])) {
            $data['lang_'.$id] = $values[$val][$langShort];
          }
        }
      }
    }
    $valuesForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    if ($new == TRUE) {
      $valuesForm->dialogTitle = $this->_gt('Add field value');
    } else {
      $valuesForm->dialogTitle = $this->_gt('Edit field value');
    }
    $valuesForm->baseLink = $this->baseLink;
    $valuesForm->msgs = &$this->msgs;
    $valuesForm->loadParams();
    $layout->add($valuesForm->getDialogXML());
  }

  /**
  * get Delete Value Form
  *
  * Displays a security question before deleting the value
  * The only parameter is a reference to the page layout
  *
  * @access public
  * @param object xsl_layout $layout
  */
  function getDelValueForm(&$layout) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    // Display the question only if the confirm_delete parameter
    // has not been set by a previous run of this method
    if ((!isset($this->params['confirm_delete']))) {
      $hidden = array(
        'mode' => 2,
        'cmd' => 'del_value',
        'data_id' => $this->params['data_id'],
        'val' => $this->params['val'],
        'confirm_delete' => 1
      );
      $msg = $this->_gtf('Really delete value "%s"?', $this->params['val']);
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Save a value with captions
  * for a multi-valued dynamic form element
  *
  * @access public
  */
  function saveValue() {
    // Are we creating a new value?
    if (isset($this->params['create']) && $this->params['create'] == 1) {
      $new = TRUE;
    } else {
      $new = FALSE;
    }
    // Check whether we've got the necessary data
    if (isset($this->params['data_id'])
        && trim($this->params['data_id']) != '') {
      $dataId = $this->params['data_id'];
    } else {
      // If it isn't set, simply return because an error message
      // is generated by the field editor form anyway
      return;
    }
    if (isset($this->params['val'])
        && trim($this->params['val']) != '') {
      $val = $this->params['val'];
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('You cannot edit an empty value.'));
      return;
    }
    if ($new == FALSE) {
      if (isset($this->params['oldval']) && trim($this->params['oldval']) != '') {
        $oldVal = $this->params['oldval'];
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('You cannot edit an empty value.'));
        return;
      }
    }
    // Get current data for this value
    $sql = "SELECT surferdata_id, surferdata_type, surferdata_values
              FROM %s
             WHERE surferdata_id = %d";
    $sqlParams = array($this->tableData, $dataId);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $dataType = $row['surferdata_type'];
        $dataValues = $row['surferdata_values'];
      }
    }
    if (!isset($dataType)) {
      $this->addMsg(MSG_ERROR, $this->_gt('Unknown field or no data type set.'));
      return;
    }
    if ($dataType != 'combo' && $dataType != 'radio' && $dataType != 'checkgroup') {
      $this->addMsg(MSG_ERROR, $this->_gt('Single-valued fields cannot have multiple values.'));
      return;
    }
    // Assume that there is no missing value error
    $errorValue = FALSE;
    if (isset($dataValues) && trim($dataValues != '')) {
      $values = $this->parseFormValueXML($dataValues, 0, TRUE);
      // Exclude new or renamed values with existing names...
      if (isset($values[$val])) {
        if ($new == TRUE || $oldVal != $val) {
          $this->addMsg(MSG_ERROR, $this->_gtf('Value "%s" already exists.', $val));
          return;
        }
      } elseif ($new == FALSE && !isset($values[$oldVal])) {
        // ...as well as existing values that cannot be found
        $errorValue = TRUE;
      }
    } elseif ($new == FALSE) {
      // Besides, we cannot edit existing values if no values exist
      $errorValue = TRUE;
    }
    if ($errorValue == TRUE) {
      $this->addMsg(MSG_ERROR, $this->_gtf('Unknown value "%s".', $val));
      return;
    }
    if (!isset($values)) {
      $values = array();
    }
    // Get all frontend languages
    $languages = $this->getLanguageSelector(TRUE, TRUE);
    // Build the value array
    $newVal = array();
    foreach ($languages as $id => $langShort) {
      if (isset($this->params['lang_'.$id]) && trim($this->params['lang_'.$id]) != '') {
        $newVal[$langShort] = $this->params['lang_'.$id];
      }
    }
    // If we're renaming an existing value, remove the old one
    // and insert the new one at the correct position
    if ($new == FALSE && $oldVal != $val) {
      $oldValues = $values;
      $values = array();
      foreach ($oldValues as $name => $captions) {
        if ($name == $oldVal) {
          $values[$val] = $captions;
        } else {
          $values[$name] = $captions;
        }
      }
    }
    // Set the value in the values array
    $values[$val] = $newVal;
    // Rebuild the XML tree
    $dataValues = $this->buildFormValueXML($values);
    // Reject invalid results
    if ($dataValues === FALSE) {
      $this->addMsg(MSG_ERROR, $this->_gt('Error building form value XML.'));
    }
    // Save the modified data to database
    $data = array(
      'surferdata_values' => $dataValues
    );
    $this->databaseUpdateRecord($this->tableData, $data, 'surferdata_id', $dataId);
    $this->addMsg(MSG_INFO, $this->_gtf('Value "%s" saved.', $val));
  }

  /**
  * Delete a value from a multi-valued dynamic form element
  *
  * @access public
  */
  function deleteValue() {
    // Only do anything if the confirm_delete parameter is set
    if (!(isset($this->params['confirm_delete']) && $this->params['confirm_delete'] == 1)) {
      return;
    }
    // We need a field and a value
    if (isset($this->params['data_id'])) {
      $dataId = $this->params['data_id'];
    } else {
      return;
    }
    if (isset($this->params['val'])) {
      $val = $this->params['val'];
    } else {
      $this->addMsg(MSG_ERROR, 'You need to specify the value you want to delete.');
      return;
    }
    // Get the data
    $sql = "SELECT surferdata_id, surferdata_type, surferdata_values
              FROM %s
             WHERE surferdata_id = %d";
    $sqlParams = array($this->tableData, $dataId);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $dataType = $row['surferdata_type'];
        $dataValues = $row['surferdata_values'];
      }
    }
    if (!(isset($dataType) && isset($dataValues))) {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('The field you want to delete a value from does not exist.')
      );
    }
    // Parse the XML into a values array
    $values = $this->parseFormValueXML($dataValues, 0, TRUE);
    $newValues = array();
    $found = FALSE;
    foreach ($values as $name => $captions) {
      if ($name != $val) {
        $newValues[$name] = $captions;
      } else {
        $found = TRUE;
      }
    }
    if ($found == FALSE) {
      $this->addMsg(MSG_ERROR, $this->_gtf('Value "%s" to delete not found.', $val));
      return;
    }
    // Rebuild the XML tree (or use an empty string) and save it
    if (empty($newValues)) {
      $dataValues = '';
    } else {
      $dataValues = $this->buildFormValueXML($newValues);
    }
    $data = array('surferdata_values' => $dataValues);
    $this->databaseUpdateRecord($this->tableData, $data, 'surferdata_id', $dataId);
    $this->addMsg(MSG_INFO, $this->_gtf('Value "%s" deleted.', $val));
  }

  /**
  * Move a value in a multi-valued dynamic form element
  *
  * @access public
  */
  function moveValue() {
    // We need a field, a value, and a direction
    if (isset($this->params['data_id'])) {
      $dataId = $this->params['data_id'];
    } else {
      return;
    }
    if (isset($this->params['val'])) {
      $val = $this->params['val'];
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('You need to specify the value you want to move.'));
      return;
    }
    if (isset($this->params['dir'])
        && ($this->params['dir'] == -1 || $this->params['dir'] == 1)) {
      $direction = $this->params['dir'];
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('You need to specify a correct moving direction.'));
      return;
    }
    // Get the data
    $sql = "SELECT surferdata_id, surferdata_type, surferdata_values
              FROM %s
             WHERE surferdata_id = %d";
    $sqlParams = array($this->tableData, $dataId);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $dataType = $row['surferdata_type'];
        $dataValues = $row['surferdata_values'];
      }
    }
    if (!(isset($dataType) && isset($dataValues))) {
      $this->addMsg(
        MSG_ERROR,
        $this->_gt('The field you want to move a value in does not exist.')
      );
    }
    // Parse the XML into a values array
    $values = $this->parseFormValueXML($dataValues, 0, TRUE);
    // Find the current position of the element
    $currentPos = -1;
    $pos = -1;
    foreach ($values as $name => $captions) {
      $pos++;
      if ($name == $val) {
        $currentPos = $pos;
      }
    }
    if ($currentPos == -1) {
      $this->addMsg(MSG_ERROR, $this->_gtf('Value "%s" to move not found.', $val));
      return;
    }
    if (($currentPos == 0 && $direction == -1) || ($currentPos == $pos && $direction == 1)) {
      $this->addMsg(MSG_ERROR, $this->_gt('Cannot move into this direction any further.'));
      return;
    }
    // Create a new array with the correct order
    $newValues = array();
    $pos = -1;
    foreach ($values as $name => $captions) {
      $pos++;
      $addCurrentValue = TRUE;
      if ($direction == -1) {
        if ($pos == $currentPos - 1) {
          $helper = array($name, $captions);
          $addCurrentValue = FALSE;
        } elseif ($pos == $currentPos) {
          $newValues[$name] = $captions;
          $newValues[$helper[0]] = $helper[1];
          $addCurrentValue = FALSE;
        }
      } else {
        if ($pos == $currentPos) {
          $helper = array($name, $captions);
          $addCurrentValue = FALSE;
        } elseif ($pos == $currentPos + 1) {
          $newValues[$name] = $captions;
          $newValues[$helper[0]] = $helper[1];
        }
      }
      if ($addCurrentValue == TRUE) {
        $newValues[$name] = $captions;
      }
    }
    // Rebuild the XML tree (or use an empty string) and save it
    if (empty($newValues)) {
      $dataValues = '';
    } else {
      $dataValues = $this->buildFormValueXML($newValues);
    }
    $data = array('surferdata_values' => $dataValues);
    $this->databaseUpdateRecord($this->tableData, $data, 'surferdata_id', $dataId);
    // We do not add any message because the operation should be intuitive and obvious
  }

  /**
  * Get select profile field dropdown
  *
  * Select a single dynamic profile field
  * to be used as an alternative display name
  * for the surfer list
  */
  function getSelectProfileDropdown() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $result = '';
    // Get all available profile fields
    $sql = "SELECT surferdata_id, surferdata_name,
                   surferdata_available, surferdata_class,
                   surferdata_order
              FROM %s
             WHERE surferdata_available = 1
          ORDER BY surferdata_class, surferdata_order, surferdata_name";
    $sqlParams = array($this->tableData);
    $profileFields = array(0 => sprintf('[%s]', $this->_gt('No field')));
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $profileFields[$row['surferdata_id']] = $row['surferdata_name'];
      }
    }
    // Nothing to do if we haven't got any available fields
    if (sizeof($profileFields) == 1) {
      return $result;
    }
    // (Try to) retrieve current value
    $currentField = $this->getProperty('DISPLAY_PROFILE_FIELD', 0);
    // Build the dialog
    $hidden = array(
      'mode' => 2,
      'cmd' => 'save_displayfield'
    );
    $fields = array(
      'field' => array('', 'isNum', TRUE, 'combo', $profileFields, '', 0)
    );
    if ($currentField != 0) {
      $data = array('field' => $currentField);
    } else {
      $data = array();
    }
    $selectFieldForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $selectFieldForm->dialogTitle = $this->_gt('Profile data field to display surfers');
    $selectFieldForm->baseLink = $this->baseLink;
    $selectFieldForm->msgs = &$this->msgs;
    $selectFieldForm->loadParams();
    $result .= $selectFieldForm->getDialogXML();
    return $result;
  }

  /**
  * Get export profile form
  *
  * Creates a dialog to select dynamic profile categories
  * for export, as well as a format
  *
  * @access public
  * @param object xsl_layout $layout
  */
  function getExportProfileForm(&$layout) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    // Get a translatable string in advance to avoid nested queries
    $untitledCategory = $this->_gt('Untitled category');
    // Get profile data categories from database
    $classes = array();
    $sql = "SELECT surferdataclass_id
              FROM %s";
    $sqlParams = array($this->tableDataClasses);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $classes[$row['surferdataclass_id']] = '';
      }
    }
    // Exit with error if there are no categories yet
    if (sizeof($classes) == 0) {
      $this->addMsg(MSG_ERROR, 'No categories to export data from.');
      return;
    }
    // Now try to get the titles for the categories
    foreach ($classes as $classId => $str) {
      $sql = "SELECT surferdataclasstitle_name,
                     surferdataclasstitle_lang,
                     surferdataclasstitle_classid
                FROM %s
               WHERE surferdataclasstitle_classid = %d
                 AND surferdataclasstitle_lang = %d";
      $sqlParams = array($this->tableDataClassTitles,
                         $classId,
       $this->lngSelect->currentLanguageId
      );
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $classes[$classId] = $row['surferdataclasstitle_name'];
        }
      }
      if ($classes[$classId] == '') {
        $classes[$classId] = sprintf('[%s %s]', $untitledCategory, $classId);
      }
    }
    // Add 'category 0' to select all categories
    $classes[0] = sprintf('[%s]', $this->_gt('All categories'));
    ksort($classes);
    // Create dialog
    $fields = array(
      'Select category to export',
      'class' => array('Category', 'isNum', TRUE, 'combo', $classes)
    );
    $data = array();
    $hidden = array('mode' => 2, 'cmd' => 'do_export_fields');
    $exportForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $exportForm->dialogTitle = $this->_gt('Export profile data fields');
    $exportForm->baseLink = $this->baseLink;
    $exportForm->buttonTitle = 'Export';
    $exportForm->msgs = &$this->msgs;
    $exportForm->loadParams();
    $layout->add($exportForm->getDialogXML());
  }

  /**
  * Get import profile form
  *
  * @param xsl_layout $layout
  */
  function getImportProfileForm(&$layout) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $fields = array(
      'Select upload file with profile fields',
      'xml_file' => array('XML file', 'isFile', TRUE, 'file', 200)
    );
    $data = array();
    $hidden = array('mode' => 2,
                    'cmd' => 'do_import_fields'
                   );
    // Check whether we've already got existing profile data
    // and only allow 'replace' mode if there isn't any
    $sql = "SELECT COUNT(*)
              FROM %s";
    $sqlParams = array($this->tableContactData);
    $number = 0;
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($num = $res->fetchField()) {
        $number = $num;
      }
    }
    if ($number == 0) {
      $fields['import_mode'] = array(
        'Import mode', 'isNum', TRUE, 'combo', array(0 => 'add', 1 => 'replace'), '', 0);
      $fields['confirm'] = array(
        'Really replace fields?', 'isNum', FALSE, 'checkbox', 1,
        'Check this in replace mode because all existing fields will be dropped.', 0);
    } else {
      $hidden['import_mode'] = 0;
    }
    $importForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $importForm->uploadFiles = TRUE;
    $importForm->dialogTitle = $this->_gt('Import profile data fields');
    $importForm->buttonTitle = 'Import';
    $importForm->baseLink = $this->baseLink;
    $importForm->msgs = &$this->msgs;
    $importForm->loadParams();
    $layout->add($importForm->getDialogXML());
  }

  /**
  * Get group list
  *
  * Creates a list view with the names of all existing groups
  * Returns an XML layout string
  *
  * @access public
  * @return string
  */
  function getGroupList() {
    $result = '';
    if (isset($this->groupList) && is_array($this->groupList)) {
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Groups'))
      );
      $result .= '<items>'.LF;
      foreach ($this->groupList as $groupId => $group) {
        $selected = ($groupId == @$this->params['group_id']) ?
          ' selected="selected"' : '';
        $href = $this->getLink(
          array(
            'group_id' => @(int)$groupId,
            'offset' => @(int)$this->params['offset']
          )
        );
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s" %s/>',
          papaya_strings::escapeHTMLChars($group['surfergroup_title']),
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($this->images['items-user-group']),
          $selected
        );
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * Get profile category list
  *
  * Creates a list view of all existing profile data categories,
  * their titles in current content language, and their permissions
  * Returns an XML layout string
  *
  * @access public
  * @return string
  */
  function getProfileCategoryList() {
    $result = '';
    // Current content language
    $lng = $this->lngSelect->currentLanguageId;
    // Get all translatable UI titles first
    // to avoid nested database queries
    $titles = array(
      'category' => $this->_gt('Category'),
      'categories' => $this->_gt('Categories'),
      'title' => $this->_gt('Title'),
      'permission' => $this->_gt('Permission'),
      'edit' => $this->_gt('Edit'),
      'delete' => $this->_gt('Delete'),
      'move_up' => $this->_gt('Move up'),
      'move_down' => $this->_gt('Move down')
    );
    // Read id and permission for each category from database
    $sql = "SELECT s.surferdataclass_id,
                   s.surferdataclass_perm,
                   s.surferdataclass_order
              FROM %s AS s
             ORDER BY s.surferdataclass_order ASC,
                      s.surferdataclass_id ASC";
    $res = $this->databaseQueryFmt($sql, $this->tableDataClasses);
    // Check whether there are categories at all
    if ($res) {
      // There are categories
      // Create list header
      $result .= sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($titles['categories'])
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['category'])
      );
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['title'])
      );
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['permission'])
      );
      $result .= '<col/>'.LF;
      $result .= '<col/>'.LF;
      $result .= '<col/>'.LF;
      $result .= '<col/>'.LF;
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      // Store query results in an array to avoid nested queries
      $classes = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $classes[] = array(
          'id' => $row['surferdataclass_id'],
          'perm' => $row['surferdataclass_perm']
        );
      }
      // Get last element's index to omit last down button
      $last = sizeof($classes) - 1;
      // Iterate over each class
      // (don't use foreach in order to have a numeric index)
      for ($i = 0; $i <= $last; $i++) {
        $class = $classes[$i];
        // Try to get the title for this class
        // in the current content language
        // or set it to [untitled] if not available
        $sql = "SELECT st.surferdataclasstitle_name
                  FROM %s AS st
                 WHERE st.surferdataclasstitle_classid=%d
                   AND st.surferdataclasstitle_lang=%d";
        $res = $this->databaseQueryFmt(
          $sql,
          array($this->tableDataClassTitles, $class['id'], $lng)
        );
        $result .= sprintf(
          '<listitem title="%s">'.LF,
          papaya_strings::escapeHTMLChars($class['id'])
        );
        if ($titleField = $res->fetchField()) {
          $title = $titleField;
        } else {
          $title = '[untitled]';
        }

        $up_href = $this->getLink(
          array(
            'mode' => 2,
            'class_id' => $class['id'],
            'cmd' => 'move_class',
            'dir' => 'up'
          )
        );
        $down_href = $this->getLink(
          array(
            'mode' => 2,
            'class_id' => $class['id'],
            'cmd' => 'move_class',
            'dir' => 'down'
          )
        );
        $edit_href = $this->getLink(
          array(
            'mode' => 2,
            'class_id' => $class['id'],
            'cmd' => 'edit_class'
          )
        );
        $del_href = $this->getLink(
          array(
            'mode' => 2,
            'class_id' => $class['id'],
            'cmd' => 'del_class'
          )
        );
        // Create list row for current category
        $result .= sprintf(
          '<subitem><a href="%s">%s</a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($edit_href),
          papaya_strings::escapeHTMLChars($title)
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars($class['perm'])
        );
        // Up arrow (second row or higher)
        if ($i > 0) {
          $result .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($up_href),
            papaya_strings::escapeHTMLChars($this->images['actions-go-up']),
            papaya_strings::escapeHTMLChars($titles['move_up'])
          );
        } else {
          $result .= '<subitem/>';
        }
        // Down arrow (not for last row)
        if ($i < $last) {
          $result .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($down_href),
            papaya_strings::escapeHTMLChars($this->images['actions-go-down']),
            papaya_strings::escapeHTMLChars($titles['move_down'])
          );
        } else {
          $result .= '<subitem/>';
        }
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($edit_href),
          papaya_strings::escapeHTMLChars($this->images['actions-edit']),
          papaya_strings::escapeHTMLChars($titles['edit'])
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($del_href),
          papaya_strings::escapeHTMLChars($this->images['places-trash']),
          papaya_strings::escapeHTMLChars($titles['delete'])
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * Get profile list
  *
  * Creates a list of all defined profile data fields
  * Returns an XML layout string
  *
  * @access public
  * @return string
  */
  function getProfileList() {
    $result = '';
    // Current content language
    $lng = $this->lngSelect->currentLanguageId;
    // Get all translatable UI titles first
    // to avoid nested database queries
    $titles = array(
      'data' => $this->_gt('Profile data'),
      'field' => $this->_gt('Field'),
      'title' => $this->_gt('Title'),
      'type' => $this->_gt('Type'),
      'check' => $this->_gt('Check'),
      'available' => $this->_gt('Available'),
      'mandatory' => $this->_gt('Mandatory'),
      'needsapproval' => $this->_gt('Needs approval'),
      'approvaldefault' => $this->_gt('Approval default'),
      'edit' => $this->_gt('Edit'),
      'delete' => $this->_gt('Delete'),
      'move_up' => $this->_gt('Move up'),
      'move_down' => $this->_gt('Move down')
    );
    // Read all relevant data from database,
    // ordered by category and then by name
    $sql = "SELECT s.surferdata_id, s.surferdata_name,
                   s.surferdata_type, s.surferdata_class,
                   s.surferdata_values, s.surferdata_check,
                   s.surferdata_available, s.surferdata_mandatory,
                   s.surferdata_needsapproval,
                   s.surferdata_approvaldefault, s.surferdata_order
              FROM %s AS s
             ORDER BY s.surferdata_class, s.surferdata_order,
                      s.surferdata_name";
    $res = $this->databaseQueryFmt($sql, array($this->tableData));
    // Check whether there are data fields
    if ($res) {
      // Data fields found
      // Create list header
      $result .= sprintf(
        '<listview title="%s" width="80%%">'.LF,
        papaya_strings::escapeHTMLChars($titles['data'])
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['field'])
      );
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['title'])
      );
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['type'])
      );
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['check'])
      );
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['available'])
      );
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['mandatory'])
      );
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['needsapproval'])
      );
      $result .= sprintf(
        '<col>%s</col>'.LF,
        papaya_strings::escapeHTMLChars($titles['approvaldefault'])
      );
      $result .= '<col/>'.LF;
      $result .= '<col/>'.LF;
      $result .= '<col/>'.LF;
      $result .= '<col/>'.LF;
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      // Save previous category to determine whether
      // a new one has started
      $oldclass = -1;
      // Save results in an array to avoid nested queries
      $rows = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        array_push($rows, $row);
      }
      // Get last field's index
      $last = sizeof($rows) - 1;
      // Iterate over each profile data field
      // (Use for instead of for each in order to get a numeric index)
      for ($i = 0; $i <= $last; $i++) {
        $row = $rows[$i];
        // If there's a category change in this turn,
        // try to get and display category title in current content
        // language or use 'untitled' plus the category number if
        // no title is available
        $newField = FALSE;
        if ($oldclass != $row['surferdata_class']) {
          $newField = TRUE;
          $sql = "SELECT surferdataclasstitle_name
                    FROM %s
                   WHERE surferdataclasstitle_classid=%d
                     AND surferdataclasstitle_lang=%d";
          $res = $this->databaseQueryFmt(
            $sql,
            array($this->tableDataClassTitles, $row['surferdata_class'], $lng)
          );
          if ($irow = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $class = $irow['surferdataclasstitle_name'];
          } else {
            $class = sprintf('untitled (%d)', $row['surferdata_class']);
          }
          $result .= sprintf(
            '<listitem title="%s" span="12" image="%s"/>'.LF,
            papaya_strings::escapeHTMLChars($class),
            papaya_strings::escapeHTMLChars($this->images['items-folder'])
          );
          $oldclass = $row['surferdata_class'];
        }
        // Display list row for current field
        $result .= sprintf(
          '<listitem title="%s" indent="1" image="%s">'.LF,
          papaya_strings::escapeHTMLChars($row['surferdata_name']),
          papaya_strings::escapeHTMLChars($this->images['items-tag'])
        );
        $result .= '<subitem>'.LF;
        // Try to read the field's title in the current content language
        // from database or use [untitled] if no title can be found
        $sql = "SELECT st.surferdatatitle_title
                  FROM %s AS st
                 WHERE st.surferdatatitle_field=%d
                   AND st.surferdatatitle_lang=%d";
        $res = $this->databaseQueryFmt(
          $sql,
          array($this->tableDataTitles, $row['surferdata_id'], $lng)
        );
        if ($irow = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $result .= $irow['surferdatatitle_title'];
        } else {
          $result .= '[untitled]';
        }
        $result .= '</subitem>'.LF;
        $result .= sprintf(
          '<subitem>%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars($row['surferdata_type'])
        );
        $result .= sprintf(
          '<subitem>%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars($row['surferdata_check'])
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars($row['surferdata_available'])
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars($row['surferdata_mandatory'])
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars($row['surferdata_needsapproval'])
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars($row['surferdata_approvaldefault'])
        );
        // Create and display links to edit or delete the current field
        $up_href = $this->getLink(
          array(
            'mode' => 2,
            'cmd' => 'move_field',
            'data_id' => $row['surferdata_id'],
            'class_id' => $row['surferdata_class'],
            'dir' => 'up'
          )
        );
        $down_href = $this->getLink(
          array(
            'mode' => 2,
            'cmd' => 'move_field',
            'data_id' => $row['surferdata_id'],
            'class_id' => $row['surferdata_class'],
            'dir' => 'down'
          )
        );
        $edit_href = $this->getLink(
          array(
            'mode' => 2,
            'cmd' => 'edit_field',
            'data_id' => $row['surferdata_id']
          )
        );
        $del_href = $this->getLink(
          array(
            'mode' => 2,
            'cmd' => 'del_field',
            'data_id' => $row['surferdata_id']
          )
        );
        // Up arrow (not for first element of each group)
        if (!$newField) {
          $result .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($up_href),
            papaya_strings::escapeHTMLChars($this->images['actions-go-up']),
            papaya_strings::escapeHTMLChars($titles['move_up'])
          );
        } else {
          $result .= '<subitem/>';
        }
        // Check whether we need to omit the down arrow
        if ($i == $last ||
            $rows[$i + 1]['surferdata_class'] != $row['surferdata_class']) {
          $down = FALSE;
        } else {
          $down = TRUE;
        }
        // Down arrow if necessary
        if ($down) {
          $result .= sprintf(
            '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
            papaya_strings::escapeHTMLChars($down_href),
            papaya_strings::escapeHTMLChars($this->images['actions-go-down']),
            papaya_strings::escapeHTMLChars($titles['move_down'])
          );
        } else {
          $result .= '<subitem/>';
        }
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($edit_href),
          papaya_strings::escapeHTMLChars($this->images['actions-edit']),
          papaya_strings::escapeHTMLChars($titles['edit'])
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($del_href),
          papaya_strings::escapeHTMLChars($this->images['places-trash']),
          papaya_strings::escapeHTMLChars($titles['delete'])
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * Initialize group dialog
  *
  * Creates a dialog to edit a group
  * specified as an array of its id and title fields
  *
  * @param array $group
  * @access public
  */
  function initializeGroupDialog($group) {
    if (!(isset($this->groupDialog) && is_object($this->groupDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array(
        'save' => 1,
        'cmd' => 'chg_group',
        'group_id' => $group['surfergroup_id'],
        'offset'=> $this->params['offset']);
      $data = array(
        'surfergroup_title' => $group['surfergroup_title'],
        'surfergroup_profile_page' => $group['surfergroup_profile_page'],
        'surfergroup_redirect_page' => $group['surfergroup_redirect_page'],
        'surfergroup_admin_group' => $group['surfergroup_admin_group'],
        'surfergroup_identifier' => $group['surfergroup_identifier']
      );
      $fields = array(
        'surfergroup_title' => array(
          'Title',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          $this->_gt('New')
        ),
        'surfergroup_profile_page' => array(
          'Profile page',
          'isNum',
          TRUE,
          'pageid',
          10,
          'Page for the surfer to edit his or her profile'
        ),
        'surfergroup_redirect_page' => array(
          'Redirection page',
          'isNum',
          TRUE,
          'pageid',
          10,
          'Page to redirect the surfer to after login'
        ),
        'surfergroup_admin_group' => array(
          'Administration group',
          'isNum',
          FALSE,
          'function',
          'callbackAdminGroups',
          'Admin group whose members can edit surfers from this group',
          0
        ),
        'surfergroup_identifier' => array(
          'External identifier',
          'isNoHTML',
          FALSE,
          'input',
          255,
          'Can be used to identify the group externally, e.g. an LDAP DN representing it',
          ''
        )
      );
      $this->groupDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->groupDialog->dialogTitle = $this->_gt('Change group');
      $this->groupDialog->baseLink = $this->baseLink;
      $this->groupDialog->msgs = &$this->msgs;
      $this->groupDialog->loadParams();
    }
  }

  /**
  * Get group edit form
  *
  * Checks whether there is a current group and
  * calls initializeGroupDialog() with its data if there is one
  *
  * @access public
  * @return string
  */
  function getGroupEdit() {
    if (isset($this->surferGroup) && is_array($this->surferGroup)) {
      $this->initializeGroupDialog($this->surferGroup);
      return $this->groupDialog->getDialogXML();
    }
    return '';
  }

  /**
  * Get delete group form
  *
  * Creates a form to confirm deletion of the current group
  * The only argument is a reference to page layout
  *
  * @param object xsl_layout &$layout
  * @access public
  */
  function getDelGroupForm(&$layout) {
    // Only display the form if the confirm_delete parameter is
    // not yet set and if there is a current group
    if ((!isset($this->params['confirm_delete'])) &&
         isset($this->surferGroup) && is_array($this->surferGroup)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_group',
        'group_id' => $this->surferGroup['surfergroup_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete group "%s" (%s)?'),
        $this->surferGroup['surfergroup_title'],
        $this->surferGroup['surfergroup_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get delete surfer form
  *
  * Creates a form to confirm deletion of the current surfer
  * The only argument is a reference to page layout
  *
  * @param object xsl_layout &$layout
  * @access public
  */
  function getDelSurferForm(&$layout) {
    // Load surfer if not present
    if (!isset($this->editSurfer) && isset($this->params['id'])) {
      $this->loadSurfer($this->params['id']);
    }
    // Only display the form if the confirm_delete parameter is
    // not yet set and if there is a current surfer
    if ((!isset($this->params['confirm_delete'])) &&
         @is_array($this->editSurfer)) {
      // Check whether the current surfer is a backend user
      if ($this->editSurfer['auth_user_id'] != '') {
        // Backend user: Display a warning
        $this->addMsg(
          MSG_WARNING,
          $this->_gt('You cannot delete system users.')
        );
      } else {
        // No backend user: Create the real confirmation dialog
        include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
        $hidden = array(
          'cmd' => 'delete',
          'id' => $this->editSurfer['surfer_id'],
          'confirm_delete' => 1,
        );
        // Create display name -- use handle as a fallback
        // if surname and given name are empty
        if (trim($this->editSurfer['surfer_givenname'] == '') &&
            trim($this->editSurfer['surfer_surname'] == '')) {
          $name = '['.$this->editSurfer['surfer_handle'].']';
        } else {
          $name = sprintf(
            '%s %s',
            $this->editSurfer['surfer_givenname'],
            $this->editSurfer['surfer_surname']
          );
        }
        $msg = sprintf(
          $this->_gt('Delete surfer "%s" (%s)?'),
          $name,
          $this->editSurfer['surfer_id']
        );
        $dialog = new base_msgdialog(
          $this, $this->paramName, $hidden, $msg, 'question'
        );
        $dialog->msgs = &$this->msgs;
        $dialog->buttonTitle = 'Delete';
        $dialog->baseLink = $this->baseLink;
        $layout->add($dialog->getMsgDialog());
      }
    }
  }

  /**
  * Get delete change request form
  *
  * Creates a form to confirm deletion of a surfer's change requests
  * The only argument is a reference to page layout
  *
  * @param object xsl_layout &$layout
  * @access public
  */
  function getDelChangeRequestForm(&$layout) {
    // Try to load current surfer if not set
    if (!isset($this->editSurfer)) {
      $this->loadSurfer($this->params['id']);
    }
    // Only display the form if the confirm_delete parameter is
    // not yet set and if there is a current surfer
    if ((!isset($this->params['confirm_delete'])) &&
         @is_array($this->editSurfer)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'mode' => 0,
        'cmd' => 'del_request',
        'id' => $this->editSurfer['surfer_id'],
        'request_id' => $this->params['request_id'],
        'surfmode' => 1,
        'confirm_delete' => 1,
      );
      $msg = $this->_gt('Delete change request?');
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get delete permission form
  *
  * Creates a form to confirm deletion of a permission
  * The only argument is a reference to page layout
  *
  * @param object xsl_layout &$layout
  * @access public
  */
  function getDelPermForm(&$layout) {
    // Only display the form if the confirm_delete parameter is
    // not yet set and if there is a current permission
    if ((!isset($this->params['confirm_delete'])) &&
         @is_array($this->editPerm)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'perm_del',
        'perm_id' => $this->editPerm['surferperm_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete permission "%s" (%s)?'),
        $this->editPerm['surferperm_title'],
        $this->editPerm['surferperm_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get add editor form
  *
  * Creates a form to confirm that the current
  * surfer should be added to editors
  * The only argument is a reference to page layout
  *
  * @access public
  * @param object xsl_layout &$layout
  */
  function getAddEditorForm(&$layout) {
    // Load surfer if not present
    if (!is_array($this->editSurfer)) {
      $this->editSurfer = $this->loadSurfer($this->params['id']);
    }
    // Only display the form if the confirm_editor_add parameter
    // is not yet set
    if (!isset($this->params['confirm_editor_add'])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'editor_add',
        'id' => $this->editSurfer['surfer_id'],
        'confirm_editor_add' => 1,
      );
      $msg = sprintf(
        $this->_gt('Add surfer "%s %s" (%s) to editors?'),
        $this->editSurfer['surfer_givenname'],
        $this->editSurfer['surfer_surname'],
        $this->editSurfer['surfer_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Add';
      $dialog->baseLink = $this->baseLink;
      $layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get general settings form
  *
  * Get a form to manage genral community settings
  * Right now, the default avatars can be set
  *
  * @access public
  * @param object xsl_layout &$layout
  */
  function getSettingsForm(&$layout) {
    $this->initSettingsForm();
    if (isset($this->settingsDialog) && is_object($this->settingsDialog)) {
      $layout->add($this->settingsDialog->getDialogXML());
    }
  }

  /**
  * Initialize general settings form
  */
  function initSettingsForm() {
    if (isset($this->settingsDialog) && is_object($this->settingsDialog)) {
      return;
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    // Allow to select no surfer in the getSurferCombo() call
    $this->allowNoSurfer = TRUE;
    $hidden = array(
      'mode' => 3,
      'cmd' => 'save_settings'
    );
    $fields = array(
      'Online status',
      'idle_timeout' => array(
        'Idle timeout in',
        'isNum',
        TRUE,
        'input',
        30,
        'Idle timeout in (minutes)'
      ),
      'Max. favorites',
      'max_favorite_surfers' => array(
        'Number',
        'isNum',
        TRUE,
        'input',
        30,
        'Maximum favorite surfers',
        25
      ),
      'Default avatars',
      'avatar_general' => array(
        'General avatar',
        'isNoHTML',
        FALSE,
        'imagefixed',
        ''
      ),
      'avatar_female' => array(
        'Female avatar',
        'isNoHTML',
        FALSE,
        'imagefixed',
        ''
      ),
      'avatar_male' => array(
        'Male avatar',
        'isNoHTML',
        FALSE,
        'imagefixed',
        ''
      ),
      'avatar_default_size' => array(
        'Default size',
        'isNum',
        TRUE,
        'input',
        30,
        ''
      ),
      'Password policy',
      'password_min_length' => array(
        'Minimum length',
        'isNum',
        TRUE,
        'input',
        10,
        '',
        0
      ),
      'password_not_equals_handle' => array(
        'Unequal to handle',
        'isNum',
        TRUE,
        'yesno',
        '',
        'Password must not be equal to handle',
        0
      ),
      'password_blacklist_check' => array(
        'Black list check',
        'isNum',
        TRUE,
        'yesno',
        '',
        'Check password against black list',
        0
      ),
      'Email settings',
      'freemail_delay' => array(
        'Freemail delay',
        'isNum',
        TRUE,
        'input',
        10,
        'Value in minutes, 0 for none',
        0
      ),
      'Profile data defaults',
      'input_maxlength' => array(
        'Maxlength (input)',
        'isNum',
        TRUE,
        'input',
        10,
        'Maximum number of chars in input fields',
        50
      ),
      'textarea_lines' => array(
        'Lines (textarea)',
        'isNum',
        TRUE,
        'input',
        10,
        'Number of visible lines in textareas',
        5
      ),
      'Contacts',
      'default_contact' => array(
        'Default contact',
        'isNoHTML',
        FALSE,
        'function',
        'getSurferCombo',
        'Make the selected surfer a default contact for each new surfer.'
      ),
      'path_cache_time' => array(
        'Path cache time',
        'isNoHTML',
        FALSE,
        'input',
        10,
        'Maximum age of contact path cache records (minutes). Default: 1440 minutes (one day).',
        1440
      ),
    );
    $data = array(
      'idle_timeout' =>
        $this->getProperty('IDLE_TIMEOUT', 30),
      'max_favorite_surfers' =>
        $this->getProperty('MAX_FAVORITE_SURFERS', 25),
      'avatar_general' =>
        $this->getProperty('AVATAR_GENERAL'),
      'avatar_female' =>
        $this->getProperty('AVATAR_FEMALE'),
      'avatar_male' =>
        $this->getProperty('AVATAR_GENERAL'),
      'avatar_default_size' =>
        $this->getProperty('AVATAR_DEFAULT_SIZE', AVATAR_DEFAULT_SIZE),
      'password_min_length' =>
        $this->getProperty('PASSWORD_MIN_LENGTH', 0),
      'password_not_equals_handle' =>
        $this->getProperty('PASSWORD_NOT_EQUALS_HANDLE', 0),
      'password_blacklist_check' =>
        $this->getProperty('PASSWORD_BLACKLIST_CHECK', 0),
      'freemail_delay' =>
        $this->getProperty('FREEMAIL_DELAY', 0),
      'input_maxlength' =>
        $this->getProperty('INPUT_MAXLENGTH', 50),
      'textarea_lines' =>
        $this->getProperty('TEXTAREA_LINES', 5),
      'default_contact' =>
        $this->getProperty('DEFAULT_CONTACT'),
      'path_cache_time' =>
        $this->getProperty('PATH_CACHE_TIME', 1440)
    );
    $this->settingsDialog = new base_dialog(
      $this, $this->paramName, $fields, $data, $hidden
    );
    $this->settingsDialog->dialogTitle = $this->_gt('General settings');
    $this->settingsDialog->baseLink = $this->baseLink;
    $this->settingsDialog->msgs = &$this->msgs;
    $this->settingsDialog->loadParams();
  }

  /**
  * Initialize permission dialog
  *
  * Creates a dialog to edit an existing permission
  * The argument is a permission array consisting of
  * its id, title, and active fields
  *
  * @param array $perm
  * @access public
  */
  function initializePermDialog($perm) {
    if (!(isset($this->permDialog) && is_object($this->permDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array(
        'save' => 1,
        'cmd' => 'chg_perm',
        'perm_id' => $perm['surferperm_id']
      );
      $data = array(
        'surferperm_title' => $perm['surferperm_title'],
        'surferperm_active' => $perm['surferperm_active'],
      );
      $fields = array(
        'surferperm_title' => array(
          'Title', 'isNoHTML', TRUE, 'input', 50, '', $this->_gt('New permission')
        ),
        'surferperm_active' => array(
          'Active', 'isNum', TRUE, 'yesno', NULL, '', 0, 'center'
        )
      );
      $this->permDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->permDialog->dialogTitle = $this->_gt('Change permission');
      $this->permDialog->baseLink = $this->baseLink;
      $this->permDialog->msgs = &$this->msgs;
      $this->permDialog->loadParams();
    }
  }

  /**
  * Edit permissions
  *
  * Initialize the permission dialog
  * and return its XML
  *
  * @param array $perm
  * @access public
  * @return string
  */
  function editPerm($perm) {
    $this->initializePermDialog($perm);
    return $this->permDialog->getDialogXML();
  }

  /**
  * Get change requests
  *
  * Read the change requests for a surfer
  * (specified by his or her id)
  * from database and return them as an array
  *
  * @param string optional $id
  * @access public
  * @return mixed NULL or array
  */
  function getChangeRequests($id = '') {
    $result = NULL;
    // Try to get current surfer's id if none was specified
    if ($id == '' && isset($this->editSurfer)) {
      $id = $this->editSurfer['surfer_id'];
    }
    // Check whether a surfer with this id exists
    if ($this->existID($id, TRUE)) {
      // Read this surfer's change requests from database
      $sql = "SELECT s.surferchangerequest_id, s.surferchangerequest_type,
                     s.surferchangerequest_data, s.surferchangerequest_time,
                     s.surferchangerequest_expiry
                     FROM %s As s
                     WHERE s.surferchangerequest_surferid='%s'
                     ORDER BY s.surferchangerequest_type ASC,
                              s.surferchangerequest_time DESC";
      $params = array($this->tableChangeRequests, $id);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        // Read each row
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          // Make $result an array before adding first element
          if ($result == NULL) {
            $result = array();
          }
          $request = array(
            'id' => $row['surferchangerequest_id'],
            'type' => $row['surferchangerequest_type'],
            'data' => $row['surferchangerequest_data'],
            'time' => $row['surferchangerequest_time'],
            'expiry' => $row['surferchangerequest_expiry']
          );
          $result[] = $request;
        }
      }
    }
    return $result;
  }

  /**
  * Get contact form
  *
  * Pick a surfer (excluding the current one)
  * from a select menu
  * and show all contact paths between
  * current surfer and that surfer
  *
  * @access public
  * @param object xsl_layout &$layout
  * @param array &$surfer
  */
  function getContactForm(&$layout, &$surfer) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $fields = array(
      'contactsurfer' => array(
        'Select contact surfer',
        'isGUID',
        TRUE,
        'function',
        'getSurferCombo',
        'Add other surfers to favorites for more options'
      )
    );
    $data = array();
    $hidden = array(
      'surfmode' => 3,
      'id' => $surfer['surfer_id']
    );
    // Has there been a request yet?
    if (isset($this->params['contactsurfer'])) {
      $contactSurfer = $this->params['contactsurfer'];
      include_once(dirname(__FILE__).'/base_contacts.php');
      $manager = contact_manager::getInstance($surfer['surfer_id']);
      // Find contacts between these surfers
      $contactStatus = $manager->findContact($contactSurfer, TRUE);
      // Get contact surfer's handle
      $handle = $this->getAnyHandleById($contactSurfer);
      if ($contactStatus == SURFERCONTACT_NONE) {
        $this->addMsg(
          MSG_INFO,
          sprintf(
            'No contact between "%s" and "%s".',
            $surfer['surfer_handle'],
            $handle
          )
        );
      } else {
        // There is some kind of contact, so check it
        $message = sprintf(
          $this->_gt('Direct contact between "%s" and "%s"'),
          $surfer['surfer_handle'],
          $handle
        );
        if ($contactStatus & SURFERCONTACT_DIRECT) {
          $this->addMsg(
            MSG_INFO,
            papaya_strings::escapeHTMLChars($message)
          );
        } elseif ($contactStatus & SURFERCONTACT_PENDING) {
          $message = sprintf(
            $this->_gt('Direct contact pending between "%s" and "%s"'),
            $surfer['surfer_handle'],
            $handle
          );
          $this->addMsg(
            MSG_INFO,
            papaya_strings::escapeHTMLChars($message)
          );
        }
        if ($contactStatus & SURFERCONTACT_INDIRECT) {
          $paths = &$manager->pathList;
          // Create a list of indirect contacts
          $output = sprintf(
            '<listview title="%s %s %s %s">'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('Indirect contacts between')),
            papaya_strings::escapeHTMLChars($surfer['surfer_handle']),
            papaya_strings::escapeHTMLChars($this->_gt('and')),
            papaya_strings::escapeHTMLChars($handle)
          );
          $output .= '<items>'.LF;
          $counter = 1;
          foreach ($paths as $path) {
            $list = '';
            foreach ($path as $srf) {
              if ($list == '') {
                $list = sprintf('%d. ', $counter++);
              } else {
                $list .= ' --> ';
              }
              $href = $this->getLink(
                array(
                  'surfmode' => 3,
                  'id' => $srf,
                  'offset' => $this->params['offset'],
                  'patt' => $this->params['patt']
                )
              );
              $list .= sprintf(
                '<a href="%s">%s</a>',
                papaya_strings::escapeHTMLChars($href),
                papaya_strings::escapeHTMLChars($this->getAnyHandleById($srf))
              );
            }
            $output .= sprintf(
              '<listitem><subitem>%s</subitem></listitem>'.LF,
              $list
            );
          }
          $output .= '</items>'.LF;
          $output .= '</listview>'.LF;
          $layout->add($output);
        }
      }
    }
    $contactDialog = new base_dialog(
      $this, $this->paramName, $fields, $data, $hidden
    );
    $contactDialog->dialogTitle = $this->_gt('Contact');
    $contactDialog->buttonTitle = $this->_gt('Select');
    $contactDialog->baseLink = $this->baseLink;
    $contactDialog->msgs = &$this->msgs;
    $contactDialog->loadParams();
    $layout->add($contactDialog->getDialogXML());
  }

  /**
  * Initialize surfer form
  *
  * Creates the form to edit surfer data
  * There are five different views determined by the surfmode parameter:
  * 0 - surfer's basic data
  * 1 - surfer's change requests
  * 2 - surfer's profile data
  * 3 - surfer's contacts
  * 4 - surfer's block and bookmark list
  *
  * There are three arguments to this method:
  * - $surfer, an array containing a surfer's data
  * - $mode (optional) determines whether a new surfer is
  *         added ('add') or an existing one is edited ('edit', default)
  * - $force (optional, default FALSE) forces an existing surfer dialog
  *          to be overridden
  *
  * @param array $surfer
  * @param string $mode optional, default value 'edit'
  * @param boolean $force optional, default value FALSE
  * @access public
  */
  function initializeSurferForm($surfer, $mode = 'edit', $force = FALSE) {
    // Remove existing surfer dialog in force mode
    if ($force && isset($this->surferDialog)) {
      unset($this->surferDialog);
    }
    // Only display this dialog at all if it doesn't exist yet
    if (!(isset($this->surferDialog) && is_object($this->surferDialog))) {
      // We need a dialog and a toolbar
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
      $toolbar = new base_btnbuilder;
      $toolbar->images = &$this->images;
      // Initialize all modes with FALSE
      $basicDataMode = FALSE;
      $changeRequestMode = FALSE;
      $profileDataMode = FALSE;
      $contactMode = FALSE;
      $blockMode = FALSE;
      // If surfmode parameter is set ...
      $surfMode = isset($this->params['surfmode']) ? $this->params['surfmode'] : 0;
      switch ($surfMode) {
      case 1:
        // Edit change requests in surfmode 1
        $changeRequestMode = TRUE;
        break;
      case 2:
        // Edit surfer's profile data in surfmode 2
        $profileDataMode = TRUE;
        break;
      case 3:
        // Manage surfer's contacts in surfmode 3
        $contactMode = TRUE;
        break;
      case 4:
        // Manage surfer's block and bookmark list in surfmode 4
        $blockMode = TRUE;
        break;
      case 0:
      default:
        // By default, edit surfer's basic data
        $basicDataMode = TRUE;
      }
      // Create toolbar, with buttons raised or activated according to surfmode
      $toolbar->addButton(
        'Properties',
        $this->getLink(
          array(
            'surfmode' => 0,
            'cmd' => $mode,
            'id' => $surfer['surfer_id']
          )
        ),
        'categories-properties',
        '',
        $basicDataMode
      );
      $toolbar->addButton(
        'Profile data',
        $this->getLink(
          array(
            'surfmode' => 2,
            'cmd' => $mode,
            'id' => $surfer['surfer_id']
          )
        ),
        'categories-content',
        '',
        $profileDataMode
      );
      $toolbar->addButton(
        'Change requests',
        $this->getLink(
          array(
            'surfmode' => 1,
            'cmd' => $mode,
            'id' => $surfer['surfer_id']
          )
        ),
        'categories-view-list',
        '',
        $changeRequestMode
      );
      $toolbar->addButton(
        'Contacts',
        $this->getLink(
          array(
            'surfmode' => 3,
            'cmd' => $mode,
            'id' => $surfer['surfer_id']
          )
        ),
        'items-user-group',
        '',
        $contactMode
      );
      $toolbar->addButton(
        'Blocks and bookmarks',
        $this->getLink(
          array(
            'surfmode' => 4,
            'cmd' => $mode,
            'id' => $surfer['surfer_id']
          )
        ),
        'status-user-locked',
        '',
        $blockMode
      );
      if ($mode == 'edit') {
        $toolbar->addSeperator();
        if ($this->isFavoriteSurfer($surfer['surfer_id'])) {
          $toolbar->addButton(
            'Remove from favorites',
            $this->getLink(
              array(
                'surfmode' => @(int)$this->params['surfmode'],
                'cmd' => 'del_fav',
                'id' => $surfer['surfer_id']
              )
            ),
            'status-favorite-disabled',
            'Remove this surfer from favorites',
            FALSE
          );
        } else {
          $toolbar->addButton(
            'Add to favorites',
            $this->getLink(
              array(
                'surfmode' => @(int)$this->params['surfmode'],
                'cmd' => 'edit',
                'id' => $surfer['surfer_id'],
                'd_surfer' => 1
              )
            ),
            'items-favorite',
            'Add this surfer to favorites',
            FALSE
          );
        }
      }
      if ($basicDataMode) {
        // Basic data mode
        // Create hidden values
        $hidden = array(
          'surfmode' => 0,
          'save' => 1,
          'cmd' => $mode,
          'id' => $surfer['surfer_id'],
          'patt' => $this->params['patt'],
          'offset' => $this->params['offset']);
        // Check whether an existing surfer is edited
        if (isset($surfer) && $mode == 'edit') {
          // Existing surfer: store his/her data for the form fields
          $data = array(
            'surfer_handle' => isset($surfer['surfer_handle']) ?
              (string)$surfer['surfer_handle'] : '',
            'surfergroup_id' => isset($surfer['surfergroup_id']) ?
              (int)$surfer['surfergroup_id'] : 0,
            'surfer_givenname' => isset($surfer['surfer_givenname']) ?
              (string)$surfer['surfer_givenname'] : '',
            'surfer_surname' => isset($surfer['surfer_surname']) ?
              (string)$surfer['surfer_surname'] : '',
            'surfer_email' => isset($surfer['surfer_email']) ? (string)$surfer['surfer_email'] : '',
            'surfer_gender' => isset($surfer['surfer_gender']) ?
              (string)$surfer['surfer_gender'] : '',
            'surfer_avatar' => isset($surfer['surfer_avatar']) ?
              (string)$surfer['surfer_avatar'] : '',
            'surfer_valid' => isset($surfer['surfer_valid']) ? (int)$surfer['surfer_valid'] : 0,
            'surfer_registration' => isset($surfer['surfer_registration']) ?
              date('Y-m-d H:i:s', $surfer['surfer_registration']) : '',
            'surfer_lastlogin' => isset($surfer['surfer_lastlogin']) ?
              date('Y-m-d H:i:s', $surfer['surfer_lastlogin']) : '',
            'surfer_lastaction' => isset($surfer['surfer_lastaction']) ?
              date('Y-m-d H:i:s', $surfer['surfer_lastaction']) : '',
            'surfer_status' => isset($surfer['surfer_status']) && $surfer['surfer_status'] != 0 ?
              'online' : 'offline'
          );
        } else {
          // New surfer: Simply create an empty array for data
          $data = array();
        }
        // Create the form fields
        $fields = array(
          'surfer_email' =>
            array('Email', 'isEMail', TRUE, 'input', 200, ''),
          'surfer_handle' =>
            array('Username', '/^[a-z\d._-]{4,'.PAPAYA_COMMUNITY_HANDLE_MAX_LENGTH.'}$/i',
                  TRUE,
                  'input', 200,
                  '4-20 characters; letters, digits, dashes, periods, and underscores only',
                  'New'
                 ),
          'surfergroup_id' =>
            array('Group', 'isNum', TRUE, 'function',
                  'getGroupCombo', '', 0),
          'surfer_valid' =>
            array('Status', 'isNum', TRUE, 'combo', $this->status, '', 0),
          'surfer_registration' =>
            array('Registration', 'isISODateTime', FALSE, 'disabled_input', 30),
          'surfer_lastlogin' =>
            array('Last Login', 'isISODateTime', FALSE, 'disabled_input', 30),
          'surfer_lastaction' =>
            array('Last activity', 'isISODateTime', FALSE, 'disabled_input', 30),
          'surfer_status' =>
            array('Online Status', 'isNoHTML', FALSE, 'disabled_input', 30),
          'Password',
          'surfer_password' =>
            array('Password', 'isNoHTML', FALSE, 'password', 20),
          'surfer_password2' =>
            array('Repetition', 'isNoHTML', FALSE, 'password', 20),
          'Basic Data',
          'surfer_givenname' =>
            array('Givenname', 'isNoHTML', FALSE, 'input', 200, ''),
          'surfer_surname' =>
            array('Surname', 'isNoHTML', FALSE, 'input', 200, ''),
          'surfer_gender' =>
            array('Gender', 'isNoHTML', FALSE, 'combo',
                  array('' => '-', 'f' => 'female', 'm' => 'male'), ''
                 ),
          'surfer_avatar' =>
            array('Avatar', '', FALSE, 'imagefixed', 200, ''),
          'Options',
          'ignore_illegal' => array(
            'Ignore illegal values', 'isNum', TRUE, 'yesno', NULL,
            'Save even if values do not match mandatory/type criteria', 0)
        );
      } elseif ($changeRequestMode) {
        // Change request mode
        // Create hidden fields
        $hidden = array(
          'surfmode' => 1,
          'save_profile' => 1,
          'cmd' => $mode,
          'id' => $surfer['surfer_id'],
          'patt' => $this->params['patt'],
          'offset' => $this->params['offset']
        );
        // Read change requests for the current surfer by id
        $changereq = $this->getChangeRequests($surfer['surfer_id']);
        // Are there any change requests?
        if ($changereq != NULL) {
          // If yes, create list view with all requests
          // Create list header
          $output = sprintf(
            '<listview title="%s">'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('Change requests'))
          );
          $output .= '<cols>'.LF;
          $output .= sprintf(
            '<col>%s</col>'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('Request type'))
          );
          $output .= sprintf(
            '<col>%s</col>'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('Request data'))
          );
          $output .= sprintf(
            '<col>%s</col>'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('Request time'))
          );
          $output .= sprintf(
            '<col>%s</col>'.LF,
            papaya_strings::escapeHTMLChars($this->_gt('Expires'))
          );
          $output .= '<col/>'.LF;
          $output .= '</cols>'.LF;
          $output .= '<items>'.LF;
          // Iterate over each change request
          foreach ($changereq as $req) {
            // Create a list line for current request
            $output .= sprintf(
              '<listitem title="%s">'.LF,
              papaya_strings::escapeHTMLChars($req['type'])
            );
            $output .= sprintf(
              '<subitem>%s</subitem>'.LF,
              papaya_strings::escapeHTMLChars($req['data'])
            );
            $output .= sprintf(
              '<subitem>%s</subitem>'.LF,
              date('Y-m-d H:i:s', $req['time'])
            );
            if (time() < $req['expiry']) {
              $icon = 'status-sign-ok';
            } else {
              $icon = 'status-sign-off';
            }
            $output .= sprintf(
              '<subitem><glyph src="%s" hint="%s"/>%s</subitem>'.LF,
              papaya_strings::escapeHTMLChars($this->images[$icon]),
              papaya_strings::escapeHTMLChars($this->_gt("valid")),
              date('Y-m-d H:i:s', $req['expiry'])
            );
            // Add link to delete current request
            $href = $this->getLink(
              array(
                'mode' => 0,
                'surfmode' => 1,
                'id' => $surfer['surfer_id'],
                'cmd' => 'del_request',
                'request_id' => (int)$req['id']
              )
            );
            $output .= sprintf(
              '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
              papaya_strings::escapeHTMLChars($href),
              papaya_strings::escapeHTMLChars($this->images['places-trash']),
              papaya_strings::escapeHTMLChars($this->_gt('Delete'))
            );
            $output .= '</listitem>'.LF;
          }
          $output .= '</items>'.LF;
          $output .= '</listview>'.LF;
          $this->layout->add($output);
        }
      } elseif ($profileDataMode) {
        // Profile data mode
        // Create hidden fields
        $hidden = array(
          'surfmode' => 2,
          'save' => 1,
          'cmd' => 'edit_profile',
          'id' => $surfer['surfer_id'],
          'patt' => $this->params['patt'],
          'offset' => $this->params['offset']);
        // Create arrays for form fields and their data
        $fields = array();
        $data = array();
        // Current content language for field titles
        $lng = $this->lngSelect->currentLanguageId;
        // Read all available fields from database,
        // ordered by category and then by name
        $sql = "SELECT surferdata_id, surferdata_name,
                       surferdata_type, surferdata_values,
                       surferdata_check, surferdata_class,
                       surferdata_mandatory
                  FROM %s
                 WHERE surferdata_available=1
                 ORDER BY surferdata_class,
                          surferdata_order,
                          surferdata_name";
        $dbParams = array($this->tableData);
        $res = $this->databaseQueryFmt($sql, $dbParams);
        // Store fields in an array to avoid nested queries
        $rows = array();
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          array_push($rows, $row);
        }
        // Save old category to determine whether a category
        // subtitle has to be displayed
        $oldclass = -1;
        // Iterate over each profile data field
        foreach ($rows as $row) {
          // Get and display a category title if necessary
          if ($row['surferdata_class'] != $oldclass) {
            $oldclass = $row['surferdata_class'];
            $isql = "SELECT surferdataclasstitle_name
                       FROM %s
                      WHERE surferdataclasstitle_classid=%d
                        AND surferdataclasstitle_lang=%d";
            $ires = $this->databaseQueryFmt(
              $isql,
              array($this->tableDataClassTitles, $row['surferdata_class'], $lng)
            );
            $classTitle = '';
            if ($title = $ires->fetchField()) {
              if (trim($title) != '') {
                $classTitle = $title;
              }
            }
            if ($classTitle == '') {
              $classTitle = sprintf('Untitled Category #%d', $row['surferdata_class']);
            }
            array_push($fields, $classTitle);
          }
          // Get field title, if available
          $isql = "SELECT surferdatatitle_title
                     FROM %s
                    WHERE surferdatatitle_field=%d
                      AND surferdatatitle_lang=%d";
          $ires = $this->databaseQueryFmt(
            $isql,
            array($this->tableDataTitles, $row['surferdata_id'], $lng)
          );
          $fieldTitle = papaya_strings::escapeHTMLChars($row['surferdata_name']);
          if ($title = $ires->fetchField()) {
            if (trim($title) != '') {
              $fieldTitle = $title;
            }
          }
          // Add field according to its type
          // To do/problem: Titles are translated automatically
          //                by _gt() if available, but they
          //                should only be determined by the current
          //                content language!
          switch($row['surferdata_type']) {
          case 'input' :
          case 'textarea' :
            $fields[$row['surferdata_name']] = array(
              $fieldTitle,
              $row['surferdata_check'],
              $row['surferdata_mandatory'],
              $row['surferdata_type'],
              $row['surferdata_values'],
              ''
            );
            break;
          case 'radio' :
          case 'checkgroup' :
          case 'combo' :
            /* Sample XML format for choice types (combo, radio, checkgroup)
             with multilingual captions (or the values themselves as a fallback):

             <options>
               <value>
                 <content>single</content>
                 <captions>
                   <en-US>single</en-US>
                   <de-DE>Single</de-DE>
                   <fr-FR></fr-FR>
                   <es-ES></es-ES>
                 </captions>
               </value>
               <value>
                 <content>married</content>
                 <captions>
                   <en-US>married</en-US>
                   <de-DE>verheiratet</de-DE>
                   <es-ES>marido</es-ES>
                 </captions>
               </value>
               <value>
                 <content>divorced</content>
                 <captions>
                   <en-US>divorced</en-US>
                   <de-DE>geschieden</de-DE>
                   <es-ES></es-ES>
                 </captions>
               </value>
               <value><content>widow</content></value>
             </options>
            */
            $vals = $this->parseFormValueXML(
              @$row['surferdata_values'], $this->lngSelect->currentLanguageId
            );
            if (!empty($vals)) {
              $fields[$row['surferdata_name']] = array($fieldTitle,
                $row['surferdata_check'], $row['surferdata_mandatory'], $row['surferdata_type'],
                $vals, ''
              );
            }
            break;
          }
          // Get data for current field, if available
          $isql = "SELECT sc.surfercontactdata_value
                    FROM %s AS s, %s AS sc
                   WHERE s.surferdata_id=sc.surfercontactdata_property
                     AND sc.surfercontactdata_surferid='%s'
                     AND s.surferdata_name='%s'";
          $idbParams = array(
            $this->tableData,
            $this->tableContactData,
            $surfer['surfer_id'],
            $row['surferdata_name']
          );
          $ires = $this->databaseQueryFmt($isql, $idbParams);
          if ($irow = $ires->fetchRow(DB_FETCHMODE_ASSOC)) {
            if ($row['surferdata_type'] == 'checkgroup') {
              $data[$row['surferdata_name']] =
                unserialize($irow['surfercontactdata_value']);
            } else {
              $data[$row['surferdata_name']] =
                $irow['surfercontactdata_value'];
            }
          }
        }
        $fields[] = 'Options';
        $fields['ignore_illegal'] = array(
          'Ignore illegal values',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'Save even if values do not match mandatory/type criteria',
          0
        );
      } elseif ($contactMode) {
        include_once(dirname(__FILE__).'/base_contacts.php');
        $manager = contact_manager::getInstance($surfer['surfer_id']);
        // Save translated titles first to avoid nested queries
        $titles = array(
          'username' => $this->_gt('username'),
          'realname' => $this->_gt('real name'),
          'status' => $this->_gt('status'),
          'accepted' => $this->_gt('accepted'),
          'pending' => $this->_gt('pending')
        );
        // Display the current surfer's contacts
        if ($contacts = $manager->getContacts(NULL, NULL, SURFERCONTACT_STATUS_BOTH)) {
          $title = sprintf(
             $this->_gt('Contacts for %s'),
            papaya_strings::escapeHTMLChars($surfer['surfer_handle'])
          );
          $output = sprintf(
            '<listview title="%s">'.LF,
            $title
          );
          $output .= '<cols>'.LF;
          $output .= sprintf(
            '<col>%s</col>'.LF,
            papaya_strings::escapeHTMLChars($titles['username'])
          );
          $output .= sprintf(
            '<col>%s</col>'.LF,
            papaya_strings::escapeHTMLChars($titles['realname'])
          );
          $output .= sprintf(
            '<col>%s</col>'.LF,
            papaya_strings::escapeHTMLChars($titles['status'])
          );
          $output .= '</cols>'.LF;
          $output .= '<items>'.LF;
          // Iterate over all contact surfers
          foreach ($contacts as $contactSurfer => $contactStatus) {
            // Get data for current contact surfer
            $sql = "SELECT surfer_handle, surfer_givenname, surfer_surname
                      FROM %s
                     WHERE surfer_id='%s'";
            $res = $this->databaseQueryFmt(
              $sql,
              array($this->tableSurfer, $contactSurfer)
            );
            if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
              $href = $this->getLink(
                array(
                  'surfmode' => 3,
                  'id' => $contactSurfer,
                  'offset' => $this->params['offset'],
                  'patt' => $this->params['patt']
                )
              );
              $output .= sprintf(
                '<listitem title="%s" href="%s">'.LF,
                papaya_strings::escapeHTMLChars($row['surfer_handle']),
                papaya_strings::escapeHTMLChars($href)
              );
              $output .= sprintf(
                '<subitem>%s %s</subitem>'.LF,
                papaya_strings::escapeHTMLChars($row['surfer_givenname']),
                papaya_strings::escapeHTMLChars($row['surfer_surname'])
              );
              if ($contactStatus == SURFERCONTACT_STATUS_ACCEPTED) {
                $statusMsg = $titles['accepted'];
              } else {
                $statusMsg = $titles['pending'];
              }
              $output .= sprintf(
                '<subitem>%s</subitem>'.LF,
                papaya_strings::escapeHTMLChars($statusMsg)
              );
              $output .= '</listitem>'.LF;
            }
          }
          $output .= '</items>'.LF;
          $output .= '</listview>';
          // Display the contact form first
          $this->getContactForm($this->layout, $surfer);
          // Now display the contact list
          $this->layout->add($output);
        }
      } elseif ($blockMode) {
        // Display the current surfer's blocks and bookmarks
        // Save translated titles
        $titles = array(
          'username' => $this->_gt('username'),
          'blocked' => $this->_gt('blocked'),
          'bookmarked' => $this->_gt('bookmarked'),
          'yes' => $this->_gt('yes'),
          'no' => $this->_gt('no')
        );
        // Get blocks and bookmarks first
        $blocks = $this->getBlocks($surfer['surfer_id']);
        $bookmarks = $this->getBookmarks($surfer['surfer_id']);
        // Now combine them as an associative array for the list view
        $blocksAndBookmarks = array();
        foreach ($blocks as $blockSurfer) {
          $blocksAndBookmarks[$blockSurfer] = array('blocked' => 1);
        }
        foreach ($bookmarks as $bookmarkSurfer) {
          if (isset($blocksAndBooksmarks[$bookmarkSurfer])) {
            $blocksAndBookmarks[$bookmarkSurfer]['bookmarked'] = 1;
          } else {
            $blocksAndBookmarks[$bookmarkSurfer] = array('bookmarked' => 1);
          }
        }
        // Only output the list view if there are blocks or bookmarks at all
        if (sizeof($blocksAndBookmarks) > 0) {
          $title = sprintf(
            $this->_gt('Blocks and bookmarks for %s'),
            papaya_strings::escapeHTMLChars($surfer['surfer_handle'])
          );
          $output = sprintf(
            '<listview title="%s">'.LF,
            $title
          );
          $output .= '<cols>'.LF;
          $output .= sprintf(
            '<col>%s</col>'.LF,
            papaya_strings::escapeHTMLChars($titles['username'])
          );
          $output .= sprintf(
            '<col>%s</col>'.LF,
            papaya_strings::escapeHTMLChars($titles['blocked'])
          );
          $output .= sprintf(
            '<col>%s</col>'.LF,
            papaya_strings::escapeHTMLChars($titles['bookmarked'])
          );
          $output .= '</cols>'.LF;
          $output .= '<items>'.LF;
          // Iterate over all contact surfers
          foreach ($blocksAndBookmarks as $contactSurfer => $bbSettings) {
            $href = $this->getLink(
              array(
                'surfmode' => 4,
                'id' => $contactSurfer,
                'offset' => $this->params['offset'],
                'patt' => $this->params['patt']
              )
            );
            $output .= sprintf(
              '<listitem title="%s" href="%s">'.LF,
              papaya_strings::escapeHTMLChars($this->getAnyHandleById($contactSurfer)),
              papaya_strings::escapeHTMLChars($href)
            );
            if (isset ($bbSettings['blocked']) && $bbSettings['blocked'] == 1) {
              $blocked = $titles['yes'];
            } else {
              $blocked = $titles['no'];
            }
            if (isset ($bbSettings['bookmarked']) && $bbSettings['bookmarked'] == 1) {
              $bookmarked = $titles['yes'];
            } else {
              $bookmarked = $titles['no'];
            }
            $output .= sprintf(
              '<subitem>%s</subitem>'.LF,
              papaya_strings::escapeHTMLChars($blocked)
            );
            $output .= sprintf(
              '<subitem>%s</subitem>'.LF,
              papaya_strings::escapeHTMLChars($bookmarked)
            );
            $output .= '</listitem>'.LF;
          }
          $output .= '</items>'.LF;
          $output .= '</listview>';
          // Now display the block/bookmark list
          $this->layout->add($output);
        }
      }

      // Create the dialog
      if (!isset($fields)) {
        $fields = array();
      }
      if (!isset($data)) {
        $data = array();
      }
      $this->surferDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->surferDialog->baseLink = $this->baseLink;
      $str = $toolbar->getXML();
      if ($str && $mode != 'add') {
        $this->layout->add(
          sprintf('<toolbar ident="surfer">%s</toolbar>'.LF, $str)
        );
      }
      $this->surferDialog->msgs = &$this->msgs;
      $this->surferDialog->loadParams();
      // Set the 'ignore illegal' option to FALSE,
      // even if it was TRUE in the previous screen
      if (isset($this->surferDialog->params['ignore_illegal'])) {
        $this->surferDialog->params['ignore_illegal'] = 0;
      }
    }
  }

  /**
  * Creates a select field to choose a group This is a callback function
  * called by base_dialog in order to load dynamic field values for the
  * following field types:
  *
  * <ul>
  *   <li>combo boxes</li>
  *   <li>radiobuttons</li>
  *   <li>checkboxes</li>
  * </ul>
  *
  * This function should always return the field data in the xml format
  * described in base_dialog.
  *
  * Creates a select field to choose a group
  *
  * @param string $name
  * @param array $field
  * @param integer $data
  * @access public
  * @return string
  */
  function getGroupCombo($name, $field, $data) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    if (isset($this->groupList) && is_array($this->groupList)) {
      foreach ($this->groupList as $groupId => $group) {
        $selected = ($groupId == @$this->editSurfer['surfergroup_id']) ?
          ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%d"%s>%s</option>'.LF,
          (int)$groupId,
          $selected,
          papaya_strings::escapeHTMLChars($group['surfergroup_title'])
        );
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Callback function to select an admin group
  *
  * @param string $name
  * @param array $field
  * @param integer $data
  * @return string form XML
  */
  function callbackAdminGroups($name, $field, $data) {
    if (!isset($this->authGroups)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_auth.php');
      $authObject = new base_auth();
      $authObject->loadGroups();
      $this->authGroups = $authObject->groups;
    }
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      $this->paramName,
      $name
    );
    $result .= sprintf(
      '<option value="0"%s>[%s]</option>'.LF,
      $data == 0 ? ' selected="selected"' : '',
      $this->_gt('Any group')
    );
    foreach ($this->authGroups as $groupId => $groupData) {
      $result .= sprintf(
        '<option value="%s"%s>%s</option>'.LF,
        $groupId,
        $data == $groupId ? ' selected="selected"' : '',
        $groupData['grouptitle']
      );
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Edit surfer
  *
  * @param array $surfer
  * @access public
  * @return string
  */
  function editSurfer($surfer) {
    $result = "";
    if ($surfer['surfer_id'] == '' && (!$this->delFlag)) {
      $mode = 'add';
    } elseif (!$this->delFlag) {
      $mode = 'edit';
    } else {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_surfer',
        'id' => $surfer['surfer_id'],
        'patt' => $this->params['patt'],
        'offset'=> $this->params['offset']
      );
      $dialog = new base_msgdialog(
        $this,
        $this->paramName,
        $hidden,
        sprintf(
          $this->_gt('Delete surfer "%s"?'), $surfer['surfer_handle']
        ),
        'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->baseLink = $this->baseLink;
      $dialog->buttonTitle = 'Delete';
      return $dialog->getMsgDialog();
    }
    $this->initializeSurferForm($surfer, $mode);
    $this->surferDialog->buttonTitle = ($mode == 'add') ? 'Add' : 'Save';
    $this->surferDialog->dialogTitle = $this->_gt(
      ($mode == 'add') ? 'Add' : 'Change'
    );
    return $this->surferDialog->getDialogXML();
  }

  /**
  * Get new surfer data
  *
  * @access public
  * @return array
  */
  function getNewSurferData() {
    return array(
      'surfer_id' => '',
      'surfer_handle' => $this->_gt('New'),
      'surfer_password' => '',
      'surfer_givenname' => '',
      'surfer_surname' => '',
      'surfer_email' => '',
      'surfer_valid' => FALSE
    );
  }

  /**
  * Toggle black list dialog
  *
  * @access public
  * @return string dialog XML
  */
  function getToggleBlacklistDialog() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $mode = 'handle';
    if (isset($this->params['bl']) && in_array($this->params['bl'], array('email', 'password'))) {
      $mode = $this->params['bl'];
    }
    $fields = array(
      'bl' => array(
        'Black list',
        '(^(handle|email|password)$)',
        TRUE,
        'combo',
        array(
          'handle' => $this->_gt('Handle'),
          'email' => $this->_gt('Email'),
          'password' => $this->_gt('Password')
        ),
        '',
        $mode
      )
    );
    $hidden = array('mode' => 3);
    $data = array();
    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $dialog->dialogTitle = $this->_gt('Select black list');
    $dialog->buttonTitle = $this->_gt('Select');
    if (is_object($dialog)) {
      if ($result = $dialog->getDialogXML()) {
        return $result;
      }
    }
    return '';
  }

  /**
  * Handle black list rules
  *
  * @access public
  * @return string list/form XML
  */
  function blacklistRules() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $result = '';
    // Get the list
    $rules = $this->getBlacklistRules('handle', TRUE);
    // Create the list view only if there already are rules
    $result .= sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Handle black list'))
    );
    if (!empty($rules)) {
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Rule'))
      );
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Delete'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($rules as $id => $rule) {
        $rule = $this->convertFromSqlLikeValue($rule);
        $result .= sprintf(
          '<listitem title="%s">',
          papaya_strings::escapeHTMLChars($rule)
        );
        $href = $this->getLink(
          array(
            'mode' => 3,
            'cmd' => 'del_rule',
            'bl' => 'handle',
            'rule_id' => $id
          )
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($this->images['places-trash']),
          papaya_strings::escapeHTMLChars($this->_gt('Delete'))
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
    }
    $result .= '</listview>';
    // Dialog to add a new rule
    $hidden = array(
      'mode' => 3,
      'cmd' => 'add_rule',
      'bl' => 'handle'
    );
    $fields = array(
      'rule' => array(
        'Rule', '^\*?[a-zA-Z0-9-]+\*?$', TRUE, 'input', 50,
        'Use * at beginning or end for partial matches')
    );
    $data = array();
    $blacklistForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $blacklistForm->dialogTitle = $this->_gt('Add handle black list rule');
    $blacklistForm->baseLink = $this->baseLink;
    $blacklistForm->msgs = &$this->msgs;
    $result .= $blacklistForm->getDialogXML();
    return $result;
  }

  /**
  * Email black list rules
  *
  * @access public
  * @return string list/form XML
  */
  function emailBlacklistRules() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $result = '';
    // Get the list
    $rules = $this->getBlacklistRules('email', TRUE, TRUE);
    // Create the list view only if there already are rules
    $result .= sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Email black list'))
    );
    if (!empty($rules)) {
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Rule'))
      );
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars('Delay')
      );
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Delete'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($rules as $id => $rule) {
        $rule['blacklist_match'] = $this->convertFromSqlLikeValue($rule['blacklist_match']);
        $result .= sprintf(
          '<listitem title="%s">',
          papaya_strings::escapeHTMLChars($rule['blacklist_match'])
        );
        if ($rule['blacklist_delay'] == 1) {
          $cmd = 'delay_off';
          $icon = $this->images['status-node-checked'];
          $hint = $this->_gt('Block email addresses matching to this rule');
        } else {
          $cmd = 'delay_on';
          $icon = $this->images['status-node-empty'];
          $hint = $this->_gt('Delay email addresses matching to this rule');
        }
        $delayHref = $this->getLink(
          array(
            'mode' => 3,
            'cmd' => $cmd,
            'bl' => 'email',
            'rule_id' => $id
          )
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($delayHref),
          papaya_strings::escapeHTMLChars($icon),
          papaya_strings::escapeHTMLChars($hint)
        );
        $href = $this->getLink(
          array(
            'mode' => 3,
            'cmd' => 'del_email_rule',
            'bl' => 'email',
            'rule_id' => $id
          )
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($this->images['places-trash']),
          papaya_strings::escapeHTMLChars($this->_gt('Delete'))
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
    }
    $result .= '</listview>';
    // Dialog to add a new rule
    $hidden = array(
      'mode' => 3,
      'cmd' => 'add_email_rule',
      'bl' => 'email'
    );
    $fields = array(
      'rule' => array(
        'Rule',
        '^\*?([^@]+@?[^@]*|[^@]*@?[^@]+)\*?$',
        TRUE,
        'input',
        50,
        'Use * at beginning or end for partial matches'
      )
    );
    $data = array();
    $blacklistForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $blacklistForm->dialogTitle = $this->_gt('Add email black list rule');
    $blacklistForm->baseLink = $this->baseLink;
    $blacklistForm->msgs = &$this->msgs;
    $result .= $blacklistForm->getDialogXML();
    return $result;
  }

  /**
  * Password black list rules
  *
  * @access public
  * @return string list/form XML
  */
  function passwordBlacklistRules() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $result = '';
    // Get the list
    $rules = $this->getBlacklistRules('password', TRUE);
    // Create the list view only if there already are rules
    $result .= sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Password black list'))
    );
    if (!empty($rules)) {
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Rule'))
      );
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Delete'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($rules as $id => $rule) {
        $rule = $this->convertFromSqlLikeValue($rule);
        $result .= sprintf(
          '<listitem title="%s">',
          papaya_strings::escapeHTMLChars($rule)
        );
        $href = $this->getLink(
          array(
            'mode' => 3,
            'cmd' => 'del_password_rule',
            'bl' => 'password',
            'rule_id' => $id
          )
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($this->images['places-trash']),
          papaya_strings::escapeHTMLChars($this->_gt('Delete'))
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
    }
    $result .= '</listview>';
    // Dialog to add a new rule
    $hidden = array(
      'mode' => 3,
      'cmd' => 'add_password_rule',
      'bl' => 'password'
    );
    $fields = array(
      'rule' => array(
        'Rule',
        '\*?.+\*?',
        TRUE,
        'input',
        50,
        'Use * at beginning or end for partial matches'
      )
    );
    $data = array();
    $blacklistForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    $blacklistForm->dialogTitle = $this->_gt('Add password black list rule');
    $blacklistForm->baseLink = $this->baseLink;
    $blacklistForm->msgs = &$this->msgs;
    $result .= $blacklistForm->getDialogXML();
    return $result;
  }

  /**
  * Permission list
  *
  * @access public
  * @return string
  */
  function permList() {
    $result = "";
    if (isset($this->permissionsList) && is_array($this->permissionsList)) {
      if ((isset($this->editSurfer) && is_array($this->editSurfer))) {
        if (!is_array($this->linkList)) {
          $this->loadLinks(@$this->editSurfer['surfergroup_id']);
        }
        $mode = 'show';
      } elseif ((isset($this->surferGroup) && is_array($this->surferGroup)) &&
                (!isset($this->editSurfer))) {
        if (!is_array($this->linkList)) {
          $this->loadLinks($this->surferGroup['surfergroup_id']);
        }
        $mode = 'link';
      } else {
        $mode = 'edit';
      }
      $result .= sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Permissions'))
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Name'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Activated'))
      );
      if ($mode != 'edit') {
        $result .= sprintf(
          '<col align="center">%s</col>',
          papaya_strings::escapeHTMLChars($this->_gt('Linked'))
        );
      }
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->permissionsList as $permId => $perm) {
        $selected = ($permId == @$this->params['perm_id']) ?
          ' selected="selected"' : '';
        $href = $this->getLink(
          array(
            'offset' => @(int)$this->params['offset'],
            'perm_id' => @(int)$perm['surferperm_id']
          )
        );
        $result .= sprintf(
          '<listitem title="%s" href="%s" image="%s" %s>',
          papaya_strings::escapeHTMLChars($perm['surferperm_title']),
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($this->images['items-permission']),
          $selected
        );
        switch ($mode) {
        case 'show':
          $result .= '<subitem align="center">';
          if ($perm['surferperm_active']) {
            $result .= sprintf(
              '<glyph src="%s" />',
              papaya_strings::escapeHTMLChars($this->images['status-node-checked-disabled'])
            );
          } else {
            $result .= sprintf(
              '<glyph src="%s" />',
              papaya_strings::escapeHTMLChars($this->images['status-node-empty-disabled'])
            );
          }
          $result .= '</subitem>';
          $result .= '<subitem align="center">';
          if (isset($this->linkList[$perm['surferperm_id']])) {
            $result .= sprintf(
              '<glyph src="%s" />',
              papaya_strings::escapeHTMLChars($this->images['status-node-checked-disabled'])
            );
          } else {
            $result .= sprintf(
              '<glyph src="%s" />',
              papaya_strings::escapeHTMLChars($this->images['status-node-empty-disabled'])
            );
          }
          $result .= '</subitem>';
          break;
        case 'link':
          $result .= '<subitem align="center">';
          if ($perm['surferperm_active']) {
            $result .= sprintf(
              '<glyph src="%s" />',
              papaya_strings::escapeHTMLChars($this->images['status-node-checked-disabled'])
            );
          } else {
            $result .= sprintf(
              '<glyph src="%s" />',
              papaya_strings::escapeHTMLChars($this->images['status-node-empty-disabled'])
            );
          }
          $result .= '</subitem>';
          $result .= '<subitem align="center">';
          if (isset($this->linkList[$perm['surferperm_id']])) {
            $href = $this->getLink(
              array(
                'cmd' => 'dellink',
                'group_id' => $this->surferGroup['surfergroup_id'],
                'perm_id' => $perm['surferperm_id']
              )
            );
            $result .= sprintf(
              '<a href="%s"><glyph src="%s" /></a>',
              papaya_strings::escapeHTMLChars($href),
              papaya_strings::escapeHTMLChars($this->images['status-node-checked'])
            );
          } else {
            $href = $this->getLink(
              array(
                'cmd' => 'addlink',
                'group_id' => $this->surferGroup['surfergroup_id'],
                'perm_id' => $perm['surferperm_id']
              )
            );
            $result .= sprintf(
              '<a href="%s"><glyph src="%s" /></a>',
              papaya_strings::escapeHTMLChars($href),
              papaya_strings::escapeHTMLChars($this->images['status-node-empty'])
            );
          }
          $result .= '</subitem>';
          break;
        case 'edit':
          $result .= '<subitem align="center">';
          if ($perm['surferperm_active']) {
            $result .= sprintf(
              '<glyph src="%s" />',
              papaya_strings::escapeHTMLChars($this->images['status-node-checked-disabled'])
            );
          } else {
            $result .= sprintf(
              '<glyph src="%s" />',
              papaya_strings::escapeHTMLChars($this->images['status-node-empty-disabled'])
            );
          }
          $result .= '</subitem>';
          break;
        }
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
    }
    return $result;
  }

  /**
  * Black list rules
  *
  * @access public
  * @return string
  */
  function surferFavoriteList() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
    $result = '';
    // Get the list
    $favoriteSurfers = $this->getFavoriteSurfers();
    // Create the list view only if there already are favorites
    if (!empty($favoriteSurfers) && is_array($favoriteSurfers)) {
      uasort($favoriteSurfers, 'strcasecmp');
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Surfer favorites'))
      );
      $result .= '<buttons>';
      $delListHref = $this->getLink(
        array(
          'mode' => 0,
          'cmd' => 'del_fav_list'
        )
      );
      $result .= sprintf(
        '<button title="%s" glyph="%s" href="%s"/>',
        papaya_strings::escapeHTMLChars($this->_gt('Remove favorite list')),
        papaya_strings::escapeHTMLChars($this->images['places-trash']),
        papaya_strings::escapeHTMLChars($delListHref)
      );
      $result .= '</buttons>';
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Surfer'))
      );
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Remove'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($favoriteSurfers as $surferId => $surferHandle) {
        $href = $this->getLink(
          array(
            'mode' => 0,
             'id' => $surferId
          )
        );
        $result .= sprintf(
          '<listitem title="%s" href="%s">',
          papaya_strings::escapeHTMLChars($surferHandle),
          papaya_strings::escapeHTMLChars($href)
        );
        $delHref = $this->getLink(
          array(
            'mode' => 0,
            'cmd' => 'del_fav',
            'id' => $surferId
          )
        );
        $result .= sprintf(
          '<subitem align="center"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>'.LF,
          papaya_strings::escapeHTMLChars($delHref),
          papaya_strings::escapeHTMLChars($this->images['places-trash']),
          papaya_strings::escapeHTMLChars($this->_gt('Remove from list'))
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
    }
    return $result;
  }

  /**
  * Get pages
  *
  * @param array $list
  * @param mixed integer $numIncs optional, default value NULL
  * @param integer $numEls optional, default value 7
  * @access public
  * @return array
  */
  function getPages($list, $numIncs = NULL, $numEls = 7) {
    unset($incs);
    $sorted = $list;
    sort($sorted);
    $num = count($sorted);
    if (!$numIncs) {
      $numIncs = ceil($num / $numEls);
    }
    for ($i = 0; $i < $numIncs; $i++) {
      $incs[] = array(
        'start' => $sorted[$i * floor($num / $numIncs)],
        'end' => $sorted[(($i + 1) * floor($num / $numIncs)) - 1]
      );
    }
    return $incs;
  }

  /**
  * Get character buttons
  *
  * @access public
  * @return string
  */
  function getCharBtns() {
    $result = '';
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $charCount = strlen($chars);
    for ($i = 0; $i < $charCount; $i++) {
      $result .= sprintf(
        '<a href="%s?%s[patt]=%s*&%s[offset]=0">%s</a> '.LF,
        papaya_strings::escapeHTMLChars($this->baseLink),
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($chars[$i]),
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($chars[$i])
      );
      if ($i == 13) {
        $result .= '<br />';
      }
    }
    $result .= sprintf(
      '<br /><a href="%s?%s[patt]=&%s[offset]=0">%s</a> '.LF,
      papaya_strings::escapeHTMLChars($this->baseLink),
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->_gt('All'))
    );
    return $result;
  }

  /**
  * Add surfer filter parameters to a given list of parameters
  *
  * @param array $params
  * @return array
  */
  function addFilterParams($params) {
    $fields = array('order', 'listlength', 'showgroup', 'status', 'online', 'patt');
    foreach ($fields as $field) {
      if (isset($this->params[$field])) {
        $params[$field] = $this->params[$field];
      }
    }
    return $params;
  }

  /**
  * Display the list of possible filter options for the surfer list.
  *
  * @return string XML
  */
  function surfersFilter() {
    // Convenience shortcut for selected fields
    $selected = ' selected="seleceted"';
    // Prepare dialog
    $result = "";
    $result .= sprintf(
      '<dialog title="%s" action="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Filter')),
      papaya_strings::escapeHTMLChars($this->baseLink)
    );
    $result .= sprintf(
      '<input type="hidden" name="%s[offset]" value="0" />'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $result .= '<lines>'.LF;
    // Select whether real name, login, or email should be displayed
    $result .= sprintf(
      '<linegroup caption="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Display'))
    );
    $result .= '<line align="center">'.LF;
    $result .= sprintf(
      '<select name="%s[order]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $sortOptions = array(
      'names' => $this->_gt('Name'),
      'login' => $this->_gt('Login'),
      'email' => $this->_gt('Email')
    );
    $profileField = $this->getProperty('DISPLAY_PROFILE_FIELD', 0);
    if ($profileField > 0) {
      $sql = "SELECT surferdata_id, surferdata_name, surferdata_available
                FROM %s
               WHERE surferdata_id = %d
                 AND surferdata_available = 1";
      $sqlParams = array($this->tableData, $profileField);
      $fieldName = '';
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $fieldName = $row['surferdata_name'];
        }
      }
      if (trim($fieldName != '')) {
        $sortOptions['dynamic'] = $fieldName;
      }
    }
    foreach ($sortOptions as $value => $caption) {
      if (isset($this->params['order']) &&
          $this->params['order'] === $value) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $result .= sprintf(
        '<option value="%s"%s>%s</option>'.LF,
        papaya_strings::escapeHTMLChars($value),
        $selected,
        papaya_strings::escapeHTMLChars($caption)
      );
    }
    $result .= '</select></line>'.LF;
    // Select how many surfers should be displayed per page
    $result .= sprintf(
      '</linegroup><linegroup caption="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Surfers per page'))
    );
    $result .= '<line align="center">';
    $steps = array(
      '10' => $this->listLength = 10,
      '20' => $this->listLength == 20,
      '50' => $this->listLength == 50,
      '100' => $this->listLength == 100
    );
    $result .= sprintf(
      '<select name="%s[listlength]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    foreach ($steps as $stepSize => $selected) {
      $result .= sprintf(
        '<option value="%d" %s>%d</option>'.LF,
        (int)$stepSize,
        $selected ? ' selected="selected"' : '',
        (int)$stepSize
      );
    }
    $result .= '</select></line>'.LF;
    // Select group to display
    $result .= sprintf(
      '</linegroup><linegroup caption="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Group'))
    );
    $result .= '<line align="center">'.LF;
    if (isset($this->params['showgroup'])) {
      $group = $this->params['showgroup'];
    } else {
      $group = 0;
    }
    $result .= sprintf(
      '<select name="%s[showgroup]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $result .= sprintf(
      '<option value="0"%s>%s</option>'.LF,
      ($group == 0 ? ' selected="selected"' : ''),
      papaya_strings::escapeHTMLChars($this->_gt('All'))
    );
    $sql = "SELECT surfergroup_id, surfergroup_title
              FROM %s";
    $adminGroups = $this->getAdminGroups();
    if ($adminGroups !== NULL) {
      $sql .= " WHERE ".str_replace(
        '%',
        '%%',
        $this->databaseGetSQLCondition(array('surfergroup_admin_group' => $adminGroups))
      );
    }
    $sql .= " ORDER BY surfergroup_title ASC";
    $sqlData = array($this->tableGroups);
    if ($res = $this->databaseQueryFmt($sql, $sqlData)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result .= sprintf(
          '<option value="%s"%s>%s</option>',
          papaya_strings::escapeHTMLChars($row['surfergroup_id']),
          ($group == $row['surfergroup_id'] ? ' selected="selected"' : ''),
          papaya_strings::escapeHTMLChars($row['surfergroup_title'])
        );
      }
    }
    $result .= '</select></line>'.LF;
    // Select status to display
    $result .= sprintf(
      '</linegroup><linegroup caption="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Status'))
    );
    $result .= '<line align="center">'.LF;
    $status = $this->params['status'];
    $selectedStatus = array(
      'all' => ($status == '') ? $selected : '',
      0 => ($status != '' && $status == 0) ? $selected : '',
      2 => (@$status == 2) ? $selected : '',
      1 => (@$status == 1) ? $selected : '',
      3 => (@$status == 3) ? $selected : '',
      4 => (@$status == 4) ? $selected : ''
    );
    $result .= sprintf(
      '<select name="%s[status]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $statusOptions = array(
      'Created' => '0',
      'Confirmed' => '2',
      'Valid' => '1',
      'Locked' => '3',
      'Blocked' => '4'
    );
    if (!isset($this->params['status']) ||
        trim($this->params['status']) === '') {
      $selected = ' selected="selected"';
    } else {
      $selected = '';
    }
    $result .= sprintf(
      '<option value="" %s>%s</option>'.LF,
      $selectedStatus['all'],
      papaya_strings::escapeHTMLChars($this->_gt('All'))
    );
    foreach ($statusOptions as $caption => $value) {
      if (isset($this->params['status']) &&
          $this->params['status'] === $value) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $result .= sprintf(
        '<option value="%d"%s>%s</option>'.LF,
        (int)$value,
        $selected,
        papaya_strings::escapeHTMLChars($this->_gt($caption))
      );
    }
    $result .= '</select></line>';
    // Select online status to display
    $result .= sprintf(
      '</linegroup><linegroup caption="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Online status'))
    );
    $result .= '<line align="center">';
    $onlineOptions = array(
      'All' => '',
      'Online' => 'online',
      'Offline' => 'offline'
    );
    $result .= sprintf(
      '<select name="%s[online]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    foreach ($onlineOptions as $caption => $value) {
      if (isset($this->params['online']) &&
          $this->params['online'] === $value) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $result .= sprintf(
        '<option value="%s"%s>%s</option>'.LF,
        papaya_strings::escapeHTMLChars($value),
        $selected,
        papaya_strings::escapeHTMLChars($this->_gt($caption))
      );
    }
    $result .= '</select></line>'.LF;
    // Select pattern by starting letter
    $result .= sprintf(
      '</linegroup><linegroup caption="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Pattern'))
    );
    $result .= '<line align="center">'.$this->getCharBtns().'</line>'.LF;
    $result .= '<line align="center">'.LF;
    $result .= sprintf(
      '<input type="text" class="dialogInput dialogScale" name="%s[patt]" value="%s" /></line>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars(@$this->params['patt'])
    );
    $result .= '</linegroup>'.LF;
    $result .= '</lines>'.LF;
    $result .= sprintf(
      '<dlgbutton value="%s" />'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Show'))
    );
    $result .= '</dialog>'.LF;

    return $result;
  }

  /**
  * Surfer list
  *
  * @access public
  * @return string
  */
  function surferList() {
    $result = $this->surfersFilter();

    if (isset($this->surfers) && is_array($this->surfers)) {
      $shortSurf = $this->surfers;
      $listLen = $this->listLength;
      $numSurf = $this->surferCount;
      $result .= sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Surfer'))
      );
      $result .= $this->getListViewNav(
        $this->params['offset'], $listLen, $this->surferCount, 9
      );
      $result .= '<items>'.LF;
      // If we are displaying a dynamic data field,
      // Load the contents of this field for the whole list
      if (isset($this->params['order']) && $this->params['order'] == 'dynamic') {
        $surferIds = array();
        foreach ($shortSurf as $surfer) {
          $surferIds[] = $surfer['surfer_id'];
        }
        $dynamicValues = array();
        if (!empty($surferIds)) {
          // Do we have a dynamic data field to display?
          $fieldId = $this->getProperty('DISPLAY_PROFILE_FIELD', 0);
          if ($fieldId > 0) {
            $dynamicValues = $this->getDynamicData($surferIds, $fieldId);
          }
        }
      }
      // Print surfers list
      foreach ($shortSurf as $surfer) {
        $selected = ($surfer['surfer_id'] == @$this->editSurfer['surfer_id'])
          ? ' selected="selected"' : '';
        $href = $this->getLink(
          array(
            'cmd' => 'edit',
            'patt' => @$this->params['patt'],
            'offset' => @$this->params['offset'],
            'id' => $surfer['surfer_id']
          )
        );
        if (trim($surfer['surfer_surname']) == '' &&
            trim($surfer['surfer_givenname']) == '') {
          $name = '['.$surfer['surfer_handle'].']';
        } else {
          $name = $surfer['surfer_surname'].', '.$surfer['surfer_givenname'];
        }
        $title = $name;
        if (isset($this->params['order'])) {
          if ($this->params['order'] == 'login') {
            $title = $surfer['surfer_handle'];
          } elseif ($this->params['order'] == 'email') {
            if (isset($surfer['surfer_email']) && trim($surfer['surfer_email']) != '') {
              $title = $surfer['surfer_email'];
            } else {
              $title = '['.$surfer['surfer_handle'].']';
            }
          } elseif ($this->params['order'] == 'dynamic') {
            if (isset($dynamicValues[$surfer['surfer_id']])) {
              $title = $dynamicValues[$surfer['surfer_id']];
            } else {
              $title = '['.$surfer['surfer_handle'].']';
            }
          }
        }
        $result .= sprintf(
          '<listitem href="%s" title="%s" hint="%s (%s)" image="%s" %s>',
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($title),
          papaya_strings::escapeHTMLChars($name),
          papaya_strings::escapeHTMLChars($surfer['surfer_handle']),
          papaya_strings::escapeHTMLChars(
            $this->images[$this->statusImages[$surfer['surfer_valid']]]
          ),
          $selected
        );
        $result .= '<subitem align="right">'.LF;
        if ($this->isFavoriteSurfer($surfer['surfer_id'])) {
          $href = $this->getLink(
            array(
              'cmd' => 'del_fav',
              'patt' => @$this->params['patt'],
              'offset' => @$this->params['offset'],
              'id' => $surfer['surfer_id']
            )
          );
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" alt="%s" hint="%3$s"/></a>'.LF,
            papaya_strings::escapeHTMLChars($href),
            papaya_strings::escapeHTMLChars($this->images['items-favorite']),
            papaya_strings::escapeHTMLChars($this->_gt('Remove from favorites'))
          );
        } else {
          $href = $this->getLink(
            array(
              'cmd' => 'edit',
              'patt' => @$this->params['patt'],
              'offset' => @$this->params['offset'],
              'id' => $surfer['surfer_id'],
              'd_surfer' => 1
            )
          );
          $result .= sprintf(
            '<a href="%s"><glyph src="%s" alt="%s" hint="%3$s" /></a>'.LF,
            papaya_strings::escapeHTMLChars($href),
            papaya_strings::escapeHTMLChars($this->images['status-favorite-disabled']),
            papaya_strings::escapeHTMLChars($this->_gt('Add to favorites'))
          );
        }
        $result .= '</subitem>'.LF;
        $result .= '<subitem align="right">'.LF;
        if ($surfer['surfer_email'] != '') {
          $result .= sprintf(
            '<a href="mailto:%s"><glyph src="%s" alt="%s" hint="%3$s" /></a>'.LF,
            papaya_strings::escapeHTMLChars($surfer['surfer_email']),
            papaya_strings::escapeHTMLChars($this->images['items-mail']),
            papaya_strings::escapeHTMLChars(
              $this->_gt('Send email to user using your email client')
            )
          );
        }
        $result .= '</subitem>'.LF;
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
    }
    return $result;
  }

  /**
  * Surfer statistics
  *
  * Display the total number of surfers as well as the number of valid and online surfers
  *
  * @access public
  * @return string listview XML
  */
  function surferStatistics() {
    // Get statistics
    $totalSurfersNum = $this->getSurfersNum();
    $validSurfersNum = $this->getSurfersNum('valid');
    $onlineSurfersNum = $this->getSurfersNum('online');
    // Display statistics
    $result = '';
    $result .= sprintf(
      '<listview title="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Statistics'))
    );
    $result .= '<cols>'.LF;
    $result .= sprintf(
      '<col>%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Information'))
    );
    $result .= sprintf(
      '<col align="right">%s</col>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('Number'))
    );
    $result .= '</cols>'.LF;
    $result .= '<items>'.LF;
    $result .= sprintf(
      '<listitem title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Total'))
    );
    $result .= sprintf(
      '<subitem align="right">%d</subitem>',
      (int)$totalSurfersNum
    );
    $result .= '</listitem>';
    $result .= sprintf(
      '<listitem title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Valid'))
    );
    $result .= sprintf(
      '<subitem align="right">%d</subitem>',
      (int)$validSurfersNum
    );
    $result .= '</listitem>';
    $result .= sprintf(
      '<listitem title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Online'))
    );
    $result .= sprintf(
      '<subitem align="right">%d</subitem>',
      (int)$onlineSurfersNum
    );
    $result .= '</listitem>';
    $result .= '</items>'.LF;
    $result .= '</listview>'.LF;
    return $result;
  }

  /**
  * Get pages navigation
  *
  * @param integer $offset current offset
  * @param integer $step offset step
  * @param integer $max max offset
  * @param integer $groupCount page link count
  * @param string $paramName offset param name
  * @access private
  */
  function getListViewNav($offset, $step, $max, $groupCount = 9,
                          $paramName = 'offset') {
    if ($max > $step) {
      $pageCount = ceil($max / $step);
      $currentPage = ceil($offset / $step);

      $result = '<buttons>';
      if ($currentPage >= 5) {
        $i = ($currentPage - 5) * $step;
        $result .= sprintf(
          '<button hint="%s" glyph="%s" href="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('5 pages back')),
          papaya_strings::escapeHTMLChars($this->images['actions-go-previous-fast']),
          papaya_strings::escapeHTMLChars(
            $this->getLink($this->addFilterParams(array('cmd' => 'show', $paramName => $i)))
          )
        );
      }
      if ($currentPage > 0) {
        $i = ($currentPage - 1) * $step;
        $result .= sprintf(
          '<button hint="%s" glyph="%s" href="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Previous page')),
          papaya_strings::escapeHTMLChars($this->images['actions-go-previous']),
          papaya_strings::escapeHTMLChars(
            $this->getLink($this->addFilterParams(array('cmd' => 'show', $paramName => $i)))
          )
        );
      }

      if ($pageCount > $groupCount) {
        $plusMinus = floor($groupCount / 2);
        $pageMin = ceil(($offset - ($step * ($plusMinus))) / $step);
        $pageMax = ceil(($offset + ($step * ($plusMinus))) / $step);
        if ($pageMin < 0) {
          $pageMin = 0;
        }
        if ($pageMin == 0) {
          $pageMax = $groupCount;
        } elseif ($pageMax >= $pageCount) {
          $pageMax = $pageCount;
          $pageMin = $pageCount - $groupCount;
        }
        for ($x = $pageMin; $x < $pageMax; $x++) {
          $i = $x * $step;
          $down = ($i == $offset)? ' down="down"' : '';
          $result .= sprintf(
            '<button title="%s" href="%s"%s/>'.LF,
            $x + 1,
            papaya_strings::escapeHTMLChars(
              $this->getLink($this->addFilterParams(array('cmd' => 'show', $paramName => $i)))
            ),
            $down
          );
        }
      } else {
        for ($i = 0, $x = 1; $i < $max; $i += $step, $x++) {
          $down = ($i == $offset)? ' down="down"' : '';
          $result .= sprintf(
            '<button title="%s" href="%s"%s/>'.LF,
            papaya_strings::escapeHTMLChars($x),
            papaya_strings::escapeHTMLChars(
              $this->getLink($this->addFilterParams(array('cmd' => 'show', $paramName => $i)))
            ),
            $down
          );
        }
      }
      if ($currentPage < $pageCount - 1) {
        $i = ($currentPage + 1) * $step;
        $result .= sprintf(
          '<button hint="%s" glyph="%s" href="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Next page')),
          papaya_strings::escapeHTMLChars($this->images['actions-go-next']),
          papaya_strings::escapeHTMLChars(
            $this->getLink($this->addFilterParams(array('cmd' => 'show', $paramName => $i)))
          )
        );
      }
      if ($currentPage < $pageCount - 5) {
        $i = ($currentPage + 5) * $step;
        $result .= sprintf(
          '<button hint="%s" glyph="%s" href="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('5 pages forward')),
          papaya_strings::escapeHTMLChars($this->images['actions-go-next-fast']),
          papaya_strings::escapeHTMLChars(
            $this->getLink($this->addFilterParams(array('cmd' => 'show', $paramName => $i)))
          )
        );
      }
      $result .= '</buttons>';
      return $result;
    }
    return '';
  }

  /**
  * Get buttons
  *
  * This method builds the main button bar for community management
  * Depending on current mode, main buttons are raised or not, and
  * subbuttons are displayed
  *
  * @access public
  */
  function getButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;

    if ($this->params['mode'] == 1) {
      // Change groups
      $toolbar->addButton(
        'Groups',
        $this->getLink(array('mode' => 1)),
        'items-user-group',
        '',
        TRUE
      );
      $toolbar->addButton(
        'Surfer',
        $this->getLink(array('mode' => 0)),
        'items-user',
        '',
        FALSE
      );
      if ($this->module->hasPerm(4)) {
        $toolbar->addButton(
          'Profile data',
          $this->getLink(array('mode' => 2)),
          'items-table',
          '',
          FALSE
        );
      }
      if ($this->module->hasPerm(5)) {
        $toolbar->addButton(
          'General settings',
          $this->getLink(array('mode' => 3)),
          'items-option',
          '',
          FALSE
        );
      }
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Add group',
        $this->getLink(array('cmd' => 'add_group')),
        'actions-user-group-add',
        '',
        FALSE
      );
      if (isset($this->surferGroup) && is_array($this->surferGroup)) {
        $toolbar->addButton(
          'Delete group',
          $this->getLink(
            array(
              'cmd' => 'del_group',
              'group_id' => (int)$this->surferGroup['surfergroup_id']
            )
          ),
          'actions-user-group-delete',
          '',
          FALSE
        );
      }
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Add permission',
        $this->getLink(array('cmd' => 'perm_add')),
        'actions-permission-add',
        '',
        FALSE
      );

      if (($this->editPerm['surferperm_id'] >= 0)) {
        $toolbar->addButton(
          'Delete permission',
          $this->getLink(
            array(
              'cmd' => 'perm_del',
              'perm_id' => $this->editPerm['surferperm_id']
            )
          ),
          'actions-permission-delete',
          '',
          FALSE
        );
      }

    } elseif ($this->params['mode'] == 2) {
      // Change profile data fields
      $toolbar->addButton(
        'Groups',
        $this->getLink(array('mode' => 1)),
        'items-user-group',
        '',
        FALSE
      );
      $toolbar->addButton(
        'Surfer',
        $this->getLink(array('mode' => 0)),
        'items-user',
        '',
        FALSE
      );
      if ($this->module->hasPerm(4)) {
        $toolbar->addButton(
          'Profile data',
          $this->getLink(array('mode' => 2)),
          'items-table',
          '',
          TRUE
        );
      }
      if ($this->module->hasPerm(5)) {
        $toolbar->addButton(
          'General settings',
          $this->getLink(array('mode' => 3)),
          'items-option',
          '',
          FALSE
        );
      }
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Add category',
        $this->getLink(
        array('mode' => 2,'cmd' => 'add_class')),
        'actions-table-add',
        '',
        FALSE
      );
      $toolbar->addButton(
        'Add field',
        $this->getLink(array('mode' => 2,'cmd' => 'add_field')),
        'actions-tag-add',
        '',
        FALSE
      );
      $editCommands = array(
        'edit_field', 'edit_props', 'save_props', 'add_title', 'edit_title',
        'del_title', 'save_title', 'add_value', 'edit_value', 'del_value',
        'save_value', 'move_value'
      );
      if (isset($this->params['cmd']) &&
          isset($this->params['data_id']) &&
          in_array($this->params['cmd'], $editCommands)) {
        $toolbar->addSeperator();
        $toolbar->addButton(
          'Add title',
          $this->getLink(
            array(
              'mode' => 2,
              'cmd' => 'add_title',
              'data_id' => $this->params['data_id']
            )
          ),
          'actions-tag-add',
          '',
          ($this->params['cmd'] == 'add_title') ? TRUE : FALSE
        );
        $toolbar->addButton(
          'Edit properties',
          $this->getLink(
            array(
              'mode' => 2,
              'cmd' => 'edit_props',
              'data_id' => $this->params['data_id']
            )
          ),
          'categories-properties',
          '',
          in_array(
            $this->params['cmd'],
            array('edit_props', 'add_value', 'edit_value', 'del_value', 'save_value', 'move_value')
          ) ? TRUE : FALSE
        );
      }
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Export fields',
        $this->getLink(array('mode' => 2, 'cmd' => 'export_fields')),
        'actions-download',
        '',
        (isset($this->params['cmd']) && $this->params['cmd'] == 'export_fields') ? TRUE : FALSE
      );
      $toolbar->addButton(
        'Import fields',
        $this->getLink(array('mode' => 2, 'cmd' => 'import_fields')),
        'actions-upload',
        '',
        (isset($this->params['cmd']) && $this->params['cmd'] == 'import_fields') ? TRUE : FALSE
      );
    } elseif ($this->params['mode'] == 0) {
      // Change surfer data
      $toolbar->addButton(
        'Groups',
        $this->getLink(array('mode' => 1)),
        'items-user-group',
        '',
        FALSE
      );
      $toolbar->addButton(
        'Surfer',
        $this->getLink(array('mode' => 0)),
        'items-user',
        '',
        TRUE
      );
      if ($this->module->hasPerm(4)) {
        $toolbar->addButton(
          'Profile data',
          $this->getLink(array('mode' => 2)),
          'items-table',
          '',
          FALSE
        );
      }
      if ($this->module->hasPerm(5)) {
        $toolbar->addButton(
          'General settings',
          $this->getLink(array('mode' => 3)),
          'items-option',
          '',
          FALSE
        );
      }
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Advanced search',
        $this->getLink(
          array(
            'mode' => 0,
            'cmd' => 'search_surfers'
          )
        ),
        'actions-search',
        '',
        (isset($this->params['cmd']) && $this->params['cmd'] == 'search_surfers')
      );
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Add surfer',
        $this->getLink(array('id' => '-1')),
        'actions-user-add',
        '',
        FALSE
      );
      if ($this->module->hasPerm(6)) {
        if (isset($this->editSurfer['surfer_id'])
            && ($this->editSurfer['surfer_id'] != '')) {
          $toolbar->addButton(
            'Delete surfer',
            $this->getLink(
              array(
                'cmd' => 'delete',
                'patt' => $this->params['patt'],
                'offset' => (int)$this->params['offset'],
                'id' =>  $this->editSurfer['surfer_id']
              )
            ),
            'actions-user-delete',
            '',
            FALSE
          );
          $toolbar->addSeperator();
          if ($this->authUser->hasPerm(4)
              && trim($this->editSurfer['auth_user_id']) == '') {
            $toolbar->addButton(
              'Add editor user',
              $this->getLink(
                array(
                  'cmd' => 'editor_add', 'patt' => $this->params['patt'],
                  'offset' => (int)$this->params['offset'],
                  'id' =>  $this->editSurfer['surfer_id']
                )
              ),
              'status-user-new',
              '',
              FALSE
            );
          }
        }
      }
      $toolbar->addSeperator();
      if ($this->module->hasPerm(3)) {
        $toolbar->addSeperator();
        $toolbar->addButton(
          'Export surfers',
          $this->getLink(array('cmd' => 'export')),
          'actions-save-to-disk',
          '',
          FALSE
        );
      }
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Add permission',
        $this->getLink(array('cmd' => 'perm_add')),
        'actions-permission-add',
        '',
        FALSE
      );
      if (($this->editPerm['surferperm_id'] >= 0)) {
        $toolbar->addButton(
          'Delete permission',
          $this->getLink(
            array(
              'cmd' => 'perm_del',
              'perm_id' => $this->editPerm['surferperm_id']
            )
          ),
          'actions-permission-delete',
          '',
          FALSE
        );
      }
    } elseif ($this->params['mode'] == 3) {
      // General settings
      $toolbar->addButton(
        'Groups',
        $this->getLink(array('mode' => 1)),
        'items-user-group',
        '',
        FALSE
      );
      $toolbar->addButton(
        'Surfer',
        $this->getLink(array('mode' => 0)),
        'items-user',
        '',
        FALSE
      );
      $toolbar->addButton(
        'Profile data',
        $this->getLink(array('mode' => 2)),
        'items-table',
        '',
        FALSE
      );
      $toolbar->addButton(
        'General settings',
        $this->getLink(array('mode' => 3)),
        'items-option',
        '',
        TRUE
      );
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Clear contact path cache',
        $this->getLink(array('mode' => 3, 'cmd' => 'clear_cache')),
        'actions-edit-clear',
        '',
        FALSE
      );
      $toolbar->addButton(
        'Cleanup contacts',
        $this->getLink(array('cmd' => 'cleanup_contacts')),
        'actions-database-refresh',
        '',
        (@$this->params['cmd'] == 'cleanup_contacts')
      );
      $toolbar->addButton(
        'Clear old registrations',
        $this->getLink(array('cmd' => 'clear_oldrequests')),
        'places-trash',
        'Delete expired surfer registrations and change requests',
        (@$this->params['cmd'] == 'clear_oldrequests')
      );
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Convert contacts',
        $this->getLink(array('mode' => 3, 'cmd' => 'convert_contacts')),
        'items-option',
        '',
        FALSE
      );
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Convert blacklist',
        $this->getLink(array('mode' => 3, 'cmd' => 'convert_blacklist_entries')),
        'actions-edit-convert',
        'Convert all blacklist entries.',
        (@$this->params['cmd'] == 'convert_blacklist_entries')
      );
      $toolbar->addButton(
        'Check surfers in blacklist',
        $this->getLink(array('mode' => 3, 'cmd' => 'check_surfers_in_blacklist')),
        'actions-database-scan',
        'Check all existing surfers with the blacklist entries (handles and emails).',
        (@$this->params['cmd'] == 'check_surfers_in_blacklist')
      );
    }
    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $str));
    }
  }

  /**
  * Export surfers
  *
  * @access public
  * @todo Add dynamic profile data
  */
  function exportSurfers() {
    $this->loadGroups();
    $sql = "SELECT surfer_id, surfer_handle,
                   surfer_givenname, surfer_surname,
                   surfer_email,
                   surfer_valid,
                   surfergroup_id,
                   surfer_lastlogin,
                   auth_user_id
              FROM %s
             ORDER BY surfer_surname, surfer_givenname, surfer_handle";
    if ($res = $this->databaseQueryFmt($sql, $this->tableSurfer)) {
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
      $fileName = 'surfers_'.date('Y-m-d').'.csv';
      if ($agent == 'IE') {
        header('Content-Disposition: inline; filename="'.$fileName.'"');
      } else {
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
      }
      header('Content-type: ' . $mimeType);
      echo 'ID,Username,Realname,Email,Valid,Group,LastLogin,Editor'."\r\n";
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        echo $this->escapeForCSV($row['surfer_id']).',';
        echo $this->escapeForCSV($row['surfer_handle']).',';
        echo $this->escapeForCSV(
          $row['surfer_givenname'].' '.$row['surfer_surname']
        ).',';
        echo $this->escapeForCSV($row['surfer_email']).',';
        echo $this->escapeForCSV($row['surfer_valid']).',';
        $group = ($row['surfergroup_id'] > 0)
          ? $this->groupList[$row['surfergroup_id']]['surfergroup_title'] : '-';
        echo $this->escapeForCSV($group).',';
        if ($row['surfer_lastlogin'] > 0) {
          echo $this->escapeForCSV(date('Y-m-d H:i:s', $row['surfer_lastlogin'])).
            ',';
        } else {
          echo $this->escapeForCSV(' ').',';
        }
        echo (($row['auth_user_id'] != '') ? 1 : 0)."\r\n";
      }
      exit;
    }
  }

  /**
  * Escape for csv
  *
  * @param string $str
  * @access public
  * @return string
  */
  function escapeForCSV($str) {
    if (strpos($str, ' ') !== FALSE || strpos($str, '"') !== FALSE ||
        strpos($str, ',') !== FALSE) {
      return '"'.str_replace('"', '""', $str).'"';
    } else {
      return $str;
    }
  }

  /**
  * Export dynamic profile data fields
  *
  * @access public
  */
  function exportProfileFields() {
    // Get class parameter
    if (isset($this->params['class'])) {
      $class = $this->params['class'];
    } else {
      $class = 0;
    }
    // Initialize output
    $data = '<?xml version="1.0" encoding="UTF-8" ?>'.LF;
    $data .= '<profile-data>'.LF;
    // Gather all relevant data
    // Start with the available content languages
    $sql = "SELECT lng_id, lng_short
              FROM %s";
    $sqlParams = array($this->tableLng);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($res->count() > 0) {
        $data .= '<languages>'.LF;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data .= sprintf(
            '<language id="%d">%s</language>'.LF,
            (int)$row['lng_id'],
            papaya_strings::escapeHTMLChars($row['lng_short'])
          );
        }
        $data .= '</languages>'.LF;
      }
    }
    // Now go for the category/categories
    $sql = "SELECT surferdataclass_id, surferdataclass_perm, surferdataclass_order
              FROM %s";
    if ($class > 0) {
      $sql .= sprintf(' WHERE surferdataclass_id = %d', $class);
    }
    $sqlParams = array($this->tableDataClasses);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($res->count() > 0) {
        $data .= '<data-classes>'.LF;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data .= sprintf(
            '<data-class id="%d" perm="%d" order="%d" />'.LF,
            papaya_strings::escapeHTMLChars($row['surferdataclass_id']),
            papaya_strings::escapeHTMLChars($row['surferdataclass_perm']),
            papaya_strings::escapeHTMLChars($row['surferdataclass_order'])
          );
        }
        $data .= '</data-classes>'.LF;
      }
    }
    // Next up are the category titles
    $sql = "SELECT surferdataclasstitle_classid, surferdataclasstitle_lang,
                   surferdataclasstitle_name
              FROM %s";
    if ($class > 0) {
      $sql .= sprintf(' WHERE surferdataclasstitle_classid = %d', $class);
    }
    $sqlParams = array($this->tableDataClassTitles);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($res->count() > 0) {
        $data .= '<data-class-titles>'.LF;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data .= sprintf(
            '<data-class-title class-id="%d"'.
            ' lang="%d">%s</data-class-title>'.LF,
            papaya_strings::escapeHTMLChars($row['surferdataclasstitle_classid']),
            papaya_strings::escapeHTMLChars($row['surferdataclasstitle_lang']),
            papaya_strings::escapeHTMLChars($row['surferdataclasstitle_name'])
          );
        }
        $data .= '</data-class-titles>'.LF;
      }
    }
    // And now for the fields themselves
    $sql = "SELECT surferdata_id,
                   surferdata_name,
                   surferdata_type,
                   surferdata_values,
                   surferdata_check,
                   surferdata_class,
                   surferdata_available,
                   surferdata_mandatory,
                   surferdata_needsapproval,
                   surferdata_approvaldefault,
                   surferdata_order
        FROM %s";
    $sqlParams = array($this->tableData);
    if ($class > 0) {
      $sql .= sprintf(' WHERE surferdata_class = %d', $class);
    }
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($res->count() > 0) {
        $data .= '<data-fields>'.LF;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data .= sprintf(
            '<data-field id="%d" type="%s" check="%s" class="%d"'.
            ' available="%d" mandatory="%d" needsapproval="%d"'.
            ' approvaldefault="%d" order="%d">'.LF,
            papaya_strings::escapeHTMLChars($row['surferdata_id']),
            papaya_strings::escapeHTMLChars($row['surferdata_type']),
            papaya_strings::escapeHTMLChars($row['surferdata_check']),
            papaya_strings::escapeHTMLChars($row['surferdata_class']),
            papaya_strings::escapeHTMLChars($row['surferdata_available']),
            papaya_strings::escapeHTMLChars($row['surferdata_mandatory']),
            papaya_strings::escapeHTMLChars($row['surferdata_needsapproval']),
            papaya_strings::escapeHTMLChars($row['surferdata_approvaldefault']),
            papaya_strings::escapeHTMLChars($row['surferdata_order'])
          );
          $data .= sprintf(
            '<name>%s</name>'.LF,
            papaya_strings::escapeHTMLChars($row['surferdata_name'])
          );
          $data .= sprintf(
            '<values>%s</values>'.LF,
            papaya_strings::escapeHTMLChars($row['surferdata_values'])
          );
          $data .= '</data-field>'.LF;
        }
        $data .= '</data-fields>'.LF;
      }
    }
    // Last are the field titles
    // This time, we need to completely different queries
    // depending on the category
    if ($class > 0) {
      $sql = "SELECT s.surferdata_class, s.surferdata_id, st.surferdatatitle_field,
                     st.surferdatatitle_lang, st.surferdatatitle_title
                FROM %s AS s, %s AS st
         WHERE s.surferdata_class = %d";
      $sqlParams = array($this->tableData, $this->tableDataTitles, $class);
    } else {
      $sql = "SELECT surferdatatitle_field, surferdatatitle_lang, surferdatatitle_title
                FROM %s";
      $sqlParams = array($this->tableDataTitles);
    }
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      if ($res->count() > 0) {
        $data .= '<data-field-titles>'.LF;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $data .= sprintf(
            '<data-field-title field="%d" lang="%d">%s</data-field-title>'.LF,
            papaya_strings::escapeHTMLChars($row['surferdatatitle_field']),
            papaya_strings::escapeHTMLChars($row['surferdatatitle_lang']),
            papaya_strings::escapeHTMLChars($row['surferdatatitle_title'])
          );
        }
        $data .= '</data-field-titles>'.LF;
      }
    }
    $data .= '</profile-data>'.LF;
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
    $fileName = 'profile_'.date('Y-m-d').'.xml';
    if ($agent == 'IE') {
      header('Content-Disposition: inline; filename="'.$fileName.'"');
    } else {
      header('Content-Disposition: attachment; filename="'.$fileName.'"');
    }
    header('Content-type: ' . $mimeType);
    echo ($data);
    exit;
  }

  /**
  * Import profile fields
  *
  * @access public
  */
  function importProfileFields() {
    // Media db instance to determine max upload size
    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');
    $mediaDB = new base_mediadb_edit;
    // Import mode
    $importMode = (isset($this->params['import_mode']) && $this->params['import_mode'] > 0) ? 1 : 0;
    // In replace mode, check confirmation
    if ($importMode == 1 && (!isset($this->params['confirm']) || $this->params['confirm'] != 1)) {
      $this->addMsg(MSG_WARNING, 'Please confirm the import or choose add mode.');
      return;
    }
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
    if ($doc->nodeType != XML_ELEMENT_NODE || $doc->nodeName != 'profile-data') {
      $this->addMsg(MSG_ERROR, $this->_gt('Illegal root element.'));
      return;
    }
    // In add mode, get a category id and field id offset
    $classOffset = 0;
    $fieldOffset = 0;
    if ($importMode == 0) {
      $sql = "SELECT MAX(surferdataclass_id)
                FROM %s";
      $sqlParams = array($this->tableDataClasses);
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($num = $res->fetchField()) {
          $classOffset = $num;
        }
      }
      $sql = "SELECT MAX(surferdata_id)
                FROM %s";
      $sqlParams = array($this->tableData);
      if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
        if ($num = $res->fetchField()) {
          $fieldOffset = $num;
        }
      }
    }
    // In replace mode, delete all existing classes and fields!
    if ($importMode == 1) {
      $this->databaseDeleteRecord($this->tableDataClasses, NULL);
      $this->databaseDeleteRecord($this->tableDataClassTitles, NULL);
      $this->databaseDeleteRecord($this->tableData, NULL);
      $this->databaseDeleteRecord($this->tableDataTitles, NULL);
    }
    $dataCategoriesFound = FALSE;
    for ($i = 0; $i < $doc->childNodes->length; $i++) {
      // Import the categories first
      $currentDocumentNode = $doc->childNodes->item($i);
      if ($currentDocumentNode->nodeType == XML_ELEMENT_NODE
          && $currentDocumentNode->nodeName == 'data-classes') {
        $dataCategoriesFound = TRUE;
        $classesNode = &$currentDocumentNode;
        if (!$classesNode->hasChildNodes()) {
          $this->addMsg(MSG_WARNING, 'No data categories defined');
          return;
        }
        $classRecords = array();
        for ($j = 0; $j < $classesNode->childNodes->length; $j++) {
          $class = &$classesNode->childNodes->item($j);
          if ($class->nodeType == XML_ELEMENT_NODE &&
              $class->nodeName == 'data-class') {
            $id = @(int)$class->getAttribute('id') + $classOffset;
            $perm = @(int)$class->getAttribute('perm');
            $order = @(int)$class->getAttribute('order');
            if ($id > $classOffset) {
              $classRecords[] = array(
                'surferdataclass_id' => $id,
                'surferdataclass_perm' => $perm,
                'surferdataclass_order' => $order
              );
            }
          }
        }
        $num = sizeof($classRecords);
        if ($num == 0) {
          $this->addMsg(MSG_WARNING, 'No valid data categories defined.');
          return;
        }
        $this->databaseInsertRecords($this->tableDataClasses, $classRecords);
        $this->addMsg(
          MSG_INFO,
          sprintf(
            $this->_gt('Successfully added %d categories.'),
            $num
          )
        );
      }
      if ($currentDocumentNode->nodeType == XML_ELEMENT_NODE
          && $currentDocumentNode->nodeName == 'data-class-titles') {
        // Now go for the category titles, but only if we've got categories
        if ($dataCategoriesFound == FALSE) {
          $this->addMsg(MSG_WARNING, 'No data categories defined');
          return;
        }
        $classTitles = &$currentDocumentNode;
        $classTitleRecords = array();
        if ($classTitles->hasChildNodes()) {
          $classTitlesNodes = &$classTitles->childNodes;
          for ($j = 0; $j < $classTitlesNodes->length; $j++) {
            $currentClassTitlesNode = &$classTitlesNodes->item($j);
            if ($currentClassTitlesNode->nodeType == XML_ELEMENT_NODE
                && $currentClassTitlesNode->nodeName == 'data-class-title') {
              $classTitle = $classTitles->childNodes->item($j);
              if ($classTitle->hasChildNodes()) {
                $titleNode = &$classTitle->childNodes->item(0);
                if ($titleNode->nodeType == XML_TEXT_NODE) {
                  $classId = @(int)$classTitle->getAttribute('class-id') + $classOffset;
                  $lang = @(int)$classTitle->getAttribute('lang');
                  $title = $titleNode->valueOf();
                  if ($classId > $classOffset) {
                    $classTitleRecords[] = array(
                      'surferdataclasstitle_classid' => $classId,
                      'surferdataclasstitle_lang' => $lang,
                      'surferdataclasstitle_name' => $title
                    );
                  }
                }
              }
            }
          }
          $num = sizeof($classTitleRecords);
          if ($num > 0) {
            $this->databaseInsertRecords(
              $this->tableDataClassTitles,
              $classTitleRecords
            );
            $this->addMsg(
              MSG_INFO,
              sprintf(
                $this->_gt('Successfully added %d category titles.'),
                $num
              )
            );
          }
        }
      }
      if ($currentDocumentNode->nodeType == XML_ELEMENT_NODE
          && $currentDocumentNode->nodeName == 'data-fields') {
        // Next up are the data fields
        $dataFields = &$currentDocumentNode;
        $dataFieldRecords = array();
        if ($dataFields->hasChildNodes()) {
          // In add mode, get all existing field names to leave them out
          $existingFields = array();
          if ($importMode == 0) {
            $sql = "SELECT surferdata_name
                      FROM %s";
            $sqlParams = array($this->tableData);
            if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
              while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $existingFields[] = $row['surferdata_name'];
              }
            }
          }
          for ($j = 0; $j < $dataFields->childNodes->length; $j++) {
            $dataField = &$dataFields->childNodes->item($j);
            if ($dataField->nodeType == XML_ELEMENT_NODE
                && $dataField->nodeName == 'data-field') {
              $fieldId = @(int)$dataField->getAttribute('id') + $fieldOffset;
              $type = @(string)$dataField->getAttribute('type');
              $check = @(string)$dataField->getAttribute('check');
              $class = @(int)$dataField->getAttribute('class') + $classOffset;
              $available = @(int)$dataField->getAttribute('available');
              $mandatory = @(int)$dataField->getAttribute('mandatory');
              $needsApproval = @(int)$dataField->getAttribute('needsapproval');
              $approvalDefault = @(int)$dataField->getAttribute('approvaldefault');
              $order = @(int)$dataField->getAttribute('order');
              $name = '';
              $values = '';
              if ($dataField->hasChildNodes()) {
                $dataFieldSubNodes = &$dataField->childNodes;
                for ($k = 0; $k < $dataFieldSubNodes->length; $k++) {
                  $currentNode = &$dataFieldSubNodes->item($k);
                  if ($currentNode->nodeType == XML_ELEMENT_NODE
                      && $currentNode->nodeName == 'name') {
                    $nameTag = &$currentNode;
                    if ($nameTag->hasChildNodes()) {
                      $nameNode = &$nameTag->childNodes->item(0);
                      if ($nameNode->nodeType == XML_TEXT_NODE) {
                        $name = $nameNode->valueOf();
                      }
                    }
                  }
                  if ($currentNode->nodeType == XML_ELEMENT_NODE
                      && $currentNode->nodeName == 'values') {
                    $valuesTag = &$currentNode;
                    if ($valuesTag->hasChildNodes()) {
                      $valuesNode = &$valuesTag->childNodes->item(0);
                      if ($valuesNode->nodeType == XML_TEXT_NODE) {
                        $values = $valuesNode->valueOf();
                      }
                    }
                  }
                }
              }
              if ($fieldId > $fieldOffset && !in_array($name, $existingFields)) {
                $dataFieldRecords[] = array(
                  'surferdata_id' => $fieldId,
                  'surferdata_name' => $name,
                  'surferdata_type' => $type,
                  'surferdata_values' => $values,
                  'surferdata_check' => $check,
                  'surferdata_class' => $class,
                  'surferdata_available' => $available,
                  'surferdata_mandatory' => $mandatory,
                  'surferdata_needsapproval' => $needsApproval,
                  'surferdata_approvaldefault' => $approvalDefault,
                  'surferdata_order' => $order
                );
              }
            }
          }
          $num = sizeof($dataFieldRecords);
          if ($num > 0) {
            $this->databaseInsertRecords(
              $this->tableData,
              $dataFieldRecords
            );
            $this->addMsg(
              MSG_INFO,
              sprintf(
                $this->_gt('Successfully added %d profile data fields.'),
                $num
              )
            );
          }
        }

      }
      if ($currentDocumentNode->nodeType == XML_ELEMENT_NODE
          && $currentDocumentNode->nodeName == 'data-field-titles') {
        // Last section: the field titles
        $fieldTitles = &$currentDocumentNode;
        $fieldTitleRecords = array();
        if ($fieldTitles->hasChildNodes()) {
          $fieldTitlesNode = &$fieldTitles->childNodes;
          for ($j = 0; $j < $fieldTitlesNode->length; $j++) {
            $fieldTitleNode = &$fieldTitlesNode->item($j);
            if ($fieldTitleNode->nodeType == XML_ELEMENT_NODE &&
                $fieldTitleNode->nodeName == 'data-field-title') {
              $fieldTitle = $fieldTitles->childNodes->item($j);
              if ($fieldTitle->hasChildNodes()) {
                $titleNode = &$fieldTitle->childNodes->item(0);
                if ($titleNode->nodeType == XML_TEXT_NODE) {
                  $fieldId = @(int)$fieldTitle->getAttribute('field') + $fieldOffset;
                  $lang = @(int)$fieldTitle->getAttribute('lang');
                  $title = $titleNode->valueOf();
                  if ($fieldId > $fieldOffset) {
                    $fieldTitleRecords[] = array(
                      'surferdatatitle_field' => $fieldId,
                      'surferdatatitle_lang' => $lang,
                      'surferdatatitle_title' => $title
                    );
                  }
                }
              }
            }
          }
          $num = sizeof($fieldTitleRecords);
          if ($num > 0) {
            $this->databaseInsertRecords(
              $this->tableDataTitles,
              $fieldTitleRecords
            );
            $this->addMsg(
              MSG_INFO,
              sprintf(
                $this->_gt('Successfully added %d data field titles.'),
                $num
              )
            );
          }
        }
      }
    }
  }

  /**
  * Order profile data fields
  *
  * @param int $class => category to be ordered
  * @param int optinal $newField =>
  *        id of a new field that doesn't have an order number yet
  */
  function orderDataFields($class, $newField = 0) {
    // Assume that we don't need reordering
    $reOrder = FALSE;
    // Find out whether the fields need reordering:
    // if there's a value of 0 or if any two fields
    // have got the same order number
    // Check the zero values (except for the new field) first
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE surferdata_class=%d
               AND surferdata_order=0";
    if ($newField) {
      $sql .= " AND surferdata_id != %d";
    }
    $sqlParams = array(
      $this->tableData,
      $class,
      $newField
    );
    $res = $this->databaseQueryFmt($sql, $sqlParams);
    if ($num = $res->fetchField()) {
      if ($num > 0) {
        $reOrder = TRUE;
      }
    }
    // Now check the doubles
    if (!$reOrder) {
      $sql = "SELECT COUNT(*)
                FROM %s
               WHERE surferdata_class=%d
            GROUP BY surferdata_order";
      $sqlParams = array($this->tableData, $class);
      $res = $this->databaseQueryFmt($sql, $sqlParams);
      while ($num = $res->fetchField()) {
        if ($num > 1) {
          $reOrder = TRUE;
        }
      }
    }
    // Check whether we need to reorder
    if ($reOrder) {
      // Just reorder by name
      $sql = "SELECT surferdata_id
                FROM %s
               WHERE surferdata_class=%d
                 AND surferdata_id != %d
            ORDER BY surferdata_name";
      $sqlParams = array(
        $this->tableData,
        $class,
        $newField
      );
      $res = $this->databaseQueryFmt($sql, $sqlParams);
      $fields = array();
      while ($id = $res->fetchField()) {
        array_push($fields, $id);
      }
      $order = 1;
      foreach ($fields as $id) {
        $data = array('surferdata_order' => $order);
        $this->databaseUpdateRecord(
          $this->tableData,
          $data,
          'surferdata_id',
          $id
        );
        $order++;
      }
    }
    // If there's a new field, it needs to get
    // the highest order number
    if ($newField) {
      $max = 0;
      $sql = "SELECT MAX(surferdata_order)
                FROM %s
               WHERE surferdata_class=%d";
      $sqlParams = array($this->tableData, $class);
      $res = $this->databaseQueryFmt($sql, $sqlParams);
      if ($num = $res->fetchField()) {
        $max = $num;
      }
      $order = $max + 1;
      $data = array('surferdata_order' => $order);
      $this->databaseUpdateRecord(
        $this->tableData,
        $data,
        'surferdata_id',
        $newField
      );
    }
  }

  /**
  * Order data categories
  *
  * @param int optinal $newClass =>
  *        id of a new category that doesn't have an order number yet
  * @return int maximum order number yet
  */
  function orderDataClasses($newClass = 0) {
    // Assume that we don't need reordering
    $reOrder = FALSE;
    // Find out whether the categories need reordering:
    // if there's a value of 0 or if any two categories
    // have got the same order number
    // Check the zero values (except for the new category) first
    $sql = "SELECT COUNT(*)
              FROM %s
             WHERE surferdataclass_order=0";
    if ($newClass) {
      $sql .= " AND surferdataclass_id != %d";
    }
    $sqlParams = array($this->tableDataClasses,
                       $newClass);
    $res = $this->databaseQueryFmt($sql, $sqlParams);
    if ($num = $res->fetchField()) {
      if ($num > 0) {
        $reOrder = TRUE;
      }
    }
    // Now check the doubles
    if (!$reOrder) {
      $sql = "SELECT COUNT(*)
                FROM %s
            GROUP BY surferdataclass_order";
      $sqlParams = array($this->tableDataClasses);
      $res = $this->databaseQueryFmt($sql, $sqlParams);
      while ($num = $res->fetchField()) {
        if ($num > 1) {
          $reOrder = TRUE;
        }
      }
    }
    // Check whether we need to reorder
    if ($reOrder) {
      // Just reorder by id
      $sql = "SELECT surferdataclass_id
                FROM %s
               WHERE surferdataclass_id != %d
            ORDER BY surferdataclass_id";
      $sqlParams = array(
        $this->tableDataClasses,
        $newClass
      );
      $res = $this->databaseQueryFmt($sql, $sqlParams);
      $classes = array();
      while ($id = $res->fetchField()) {
        array_push($classes, $id);
      }
      $order = 1;
      foreach ($classes as $id) {
        $data = array('surferdataclass_order' => $order);
        $this->databaseUpdateRecord(
          $this->tableDataClasses,
          $data,
          'surferdataclass_id',
          $id
        );
        $order++;
      }
    }
    // If there's a new category, it needs to get
    // the highest order number
    if ($newClass) {
      $max = 0;
      $sql = "SELECT MAX(surferdataclass_order)
                FROM %s";
      $sqlParams = array($this->tableDataClasses);
      $res = $this->databaseQueryFmt($sql, $sqlParams);
      if ($num = $res->fetchField()) {
        $max = $num;
      }
      $order = $max + 1;
      $data = array('surferdataclass_order' => $order);
      $this->databaseUpdateRecord(
        $this->tableDataClasses,
        $data,
        'surferdataclass_id',
        $newClass
      );
    }
  }

  /**
  * get array for language selection
  *
  * @access public
  * @param string $assoc optional
  * @return string
  */
  function getLanguageSelector($assoc = TRUE, $shortOnly = FALSE) {
    $langs = array();
    $sql = "SELECT lng_id, lng_short, lng_title FROM %s";
    $res = $this->databaseQueryFmt($sql, $this->tableLng);
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      if ($assoc) {
        if ($shortOnly) {
          $langs[$row['lng_id']] = $row['lng_short'];
        } else {
          $langs[$row['lng_id']] = sprintf(
            '%s (%s)',
            $row['lng_title'],
            $row['lng_short']
          );
        }
      } else {
        array_push($langs, $row['lng_id']);
      }
    }
    return $langs;
  }

  /**
  * callback method for media db folder selector
  *
  * @access public
  * @param string $name
  * @return string
  */
  function callbackFolders($name, $field, $data) {
    if (!(isset($this->mediaDB) && is_object($this->mediaDB))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
      $this->mediaDB = new base_mediadb;
    }
    $result = '';
    $folders =
      $this->mediaDB->getFolderComboArray($this->parentObj->currentLanguage['lng_id']);

    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    foreach ($folders as $folderId => $folderName) {
      $selected = ($data == $folderId) ? 'selected="selected"' : '';
      $result .= sprintf(
        '<option value="%s" %s>%s</option>'.LF,
        papaya_strings::escapeHTMLChars($folderId),
        $selected,
        papaya_strings::escapeHTMLChars($folderName)
      );
    }
    $result .= '</select>'.LF;

    return $result;
  }

  /**
  * Check whether a certain value is a check function
  *
  * @access public
  * @param string $checkValue
  * @return boolean
  */
  function isCheckFunction($checkValue) {
    // Return false immediately on empty strings
    if (trim($checkValue) == '') {
      return FALSE;
    }
    // Get list of check functions
    $methods = get_class_methods('checkit');
    // Iterate over all method names
    foreach ($methods as $method) {
      // Return true on first (case-insensitive) match
      if (strtolower($checkValue) == strtolower($method)) {
        return TRUE;
      }
    }
    // Return false here -- no check function found
    return FALSE;
  }

  /**
  * Get combo check-functions
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @access public
  * @return string XML or ''
  */
  function getCheckFunctionsCombo($name, $field, $data) {
    $result = '';
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $methods = get_class_methods('checkit');
    if (is_array($methods) && count($methods) > 0) {
      $selected = ($data == -1) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%d" %s>[%s]</option>'.LF,
        -1,
        $selected,
        papaya_strings::escapeHTMLChars('PCRE')
      );
      foreach ($methods as $method) {
        if (substr($method, 0, 2) == 'is') {
          $selected = (strtolower($data) == strtolower($method)) ?
            ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%s" %s>%s</option>'.LF,
            papaya_strings::escapeHTMLChars($method),
            $selected,
            papaya_strings::escapeHTMLChars($method)
          );
        }
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get Select Form Field Type
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @access public
  * @return string XML
  */
  function getFieldType($name, $field, $data) {
    $result = sprintf(
      '<select id="field_type" name="%s[%s]" class="dialogSelect dialogScale"'.
      ' onchange="outp = 0; displayParameters(this);">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $values = array('input', 'textarea', 'combo', 'radio', 'checkgroup', 'function');
    foreach ($values as $value) {
      $selected = ($data == $value) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%s"%s>%s</option>'.LF,
        papaya_strings::escapeHTMLChars($value),
        $selected,
        papaya_strings::escapeHTMLChars($value)
      );
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get surfer combo
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @access public
  * @return string XML
  */
  function getSurferCombo($name, $field, $data) {
    $result = sprintf(
      '<select id="surfer" name="%s[%s]" class="dialogSelect dialogScale" size="1">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    // Add 'none' field if the $this->allowNoSurfer attribute is set
    if (isset($this->allowNoSurfer) && $this->allowNoSurfer == TRUE) {
      $selected = ($data == '') ? 'selected="selected"' : '';
      $result .= sprintf(
        '<option value="" %s>%s</option>'.LF,
        $selected,
        papaya_strings::escapeHTMLChars($this->_gt('[None]'))
      );
      // Do not allow to select no surfer by default in future calls
      $this->allowNoSurfer = FALSE;
    }
    // Get favorite surfers
    $surferList = $this->getFavoriteSurfers();
    uasort($surferList, 'strcasecmp');
    foreach ($surferList as $id => $handle) {
      $selected = ($data == $id) ? 'selected="selected"' : '';
      if ($id != $this->editSurfer['surfer_id']) {
        $result .= sprintf(
          '<option value="%s" %s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($id),
          $selected,
          papaya_strings::escapeHTMLChars($handle)
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  /**
   * General cleanup process for all papaya contact ids
   * This method provides a full check for surfercontact table
   * and will remove all invalid ids preventing inconsistent data.
   *
   * Necessary if you delete surfers directly from the database
   * instead of using the official backend functionality
  * provided right in this class
   *
   */
  function cleanupContacts() {
    $removedSurfers = array();

    $sql = "SELECT  c.surfercontact_requestor,
                    c.surfercontact_requested,
                    s1.surfer_id as surfer1,
                    s2.surfer_id as surfer2
            FROM    %s c
        LEFT JOIN   %s s1 ON (c.surfercontact_requestor = s1.surfer_id)
        LEFT JOIN   %s s2 ON (c.surfercontact_requested = s2.surfer_id)
            WHERE   s1.surfer_id IS NULL OR s2.surfer_id IS NULL";
    $params = array(
      $this->tableContacts,
      $this->tableSurfer,
      $this->tableSurfer,
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (checkit::isGUID($row['surfercontact_requestor'], TRUE) &&
            $row['surfercontact_requestor'] != $row['surfer1']) {
          $removedSurfers[] = $row['surfercontact_requestor'];
        }
        if (checkit::isGUID($row['surfercontact_requested'], TRUE) &&
            $row['surfercontact_requested'] != $row['surfer2']) {
          $removedSurfers[] = $row['surfercontact_requested'];
        }
      }

      $removedSurfers = array_unique($removedSurfers);
      if (count($removedSurfers) > 0) {
        $cntAll = 0;
        $cnt = $this->databaseDeleteRecord(
          $this->tableContacts,
          array('surfercontact_requestor' => $removedSurfers)
        );
        if ($cnt > 0) {
          $cntAll += $cnt;
        }
        $cnt = $this->databaseDeleteRecord(
          $this->tableContacts,
          array('surfercontact_requested' => $removedSurfers)
        );
        if ($cnt > 0) {
          $cntAll += $cnt;
        }

        $this->addMsg(
          MSG_INFO,
          $this->_gtf(
            'There were found %d invalid surfer contact ids. %d entries were deleted.',
            array(count($removedSurfers), $cntAll)
          )
        );
      } else {
        $this->addMsg(MSG_INFO, $this->_gt('No invalid contact ids found.'));
      }
    }
  }

  /**
  * Convert contacts
  *
  * As of 2008-06-10, all accepted surfer contacts are stored in two-way-form
  * (surfer1 => surfer2 and surfer2 => surfer1) which allows for a much more
  * efficient algorithm to find contact paths.
  *
  * To convert all existing contacts to this format (by adding a contact
  * for the other direction), call this method once by clicking the
  * "Convert contacts" button in the community administration's main toolbar
  * in settings mode
  * Nevermind if you click the button again -- the method can tell whether
  * it has been called before and will not perform any action then.
  *
  * @access public
  */
  function convertContacts() {
    // Call cleanupContacts() first to make sure
    // we don't waste our time converting contacts
    // for non-existing surfers
    $this->cleanupContacts();
    // Get a momentary list of all existing contacts
    $contacts = array();
    $sql = "SELECT surfercontact_requestor,
                   surfercontact_requested,
                   surfercontact_status
              FROM %s
             WHERE surfercontact_status=2";
    $sqlParams = array($this->tableContacts);
    if ($res = $this->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $requestor = $row['surfercontact_requestor'];
        $requested = $row['surfercontact_requested'];
        if (!(isset($contacts[$requestor]) && is_array($contacts[$requestor]))) {
          $contacts[$requestor] = array();
        }
        $contacts[$requestor][] = $requested;
      }
    }
    if (!empty($contacts)) {
      // Check which contacts need to be converted
      $convertibleContacts = array();
      foreach ($contacts as $surfer => $contactList) {
        foreach ($contactList as $contact) {
          if (!(isset($contacts[$contact]) && in_array($surfer, $contacts[$contact]))) {
            $convertibleContacts[] = array(
              'surfercontact_requestor' => $contact,
              'surfercontact_requested' => $surfer,
              'surfercontact_status' => 2
            );
          }
        }
      }
      if (!empty($convertibleContacts)) {
        $result = $this->databaseInsertRecords($this->tableContacts, $convertibleContacts);
        $num = sizeof($convertibleContacts);
        if ($result) {
          $this->addMsg(
            MSG_INFO,
            sprintf($this->_gt('%d contacts converted successfully'), $num)
          );
          return;
        } else {
          $this->addMsg(
            MSG_WARNING,
            sprintf($this->_gt('Need to convert %d contacts, but an error occured.'), $num)
          );
          return;
        }
      }
    }
    $this->addMsg(MSG_INFO, $this->_gt('No contacts need to be converted any more.'));
  }

  /**
  * Each email or password entry of the blacklist will be converted.
  *
  * Since 19.01.2009, all blacklist entries are stored as sql matches (e.g.
  * %notallowed_name_% - the corresponding old value was: *notallowed?name?*).
  *
  * For more techniqual details, see #convertFromSqlValue(string) and #convertToSqlValue(string)
  *
  * @see #convertFromSqlValue()
  * @see #convertToSqlValue()
  */
  function convertBlacklistEntries() {
    $i = 0;
    $rules = $this->getBlacklistRules(array('handle', 'email'));
    foreach ($rules as $ruleId => $rule) {
      // only rules, that match the old style
      if (strpos($rule, '*') !== FALSE || strpos($rule, '?') !== FALSE ) {
        $rule2 = $this->convertToSqlLikeValue($rule);
        // check, that there is no bad input/output
        if (!empty($rule) && !empty($rule2) && $rule != $rule2) {
          $this->databaseUpdateRecord(
            $this->tableBlacklist,
            array('blacklist_match' => $rule2),
            'blacklist_id',
            $ruleId
          );
          $i++;
        }
      }
    }
    $this->addMsg(MSG_INFO, $this->_gtf('%d blacklist entries updated.', $i));
  }

  /**
   * Check all surfers against the email and handle blacklists.
   *
   * @return boolean TRUE if at least one surfer found, FALSE otherwise
   */
  function checkAllSurfersAgainstBlacklist() {
    // init
    $this->checkedAllSurfersAgainstBlacklist = array();

    $partLike = str_replace(
      '%',
      '%%',
      $this->databaseGetSQLSource('LIKE', 'blacklist_match', FALSE)
    );
    $query = "SELECT s.surfer_id, s.surfer_handle, s.surfer_email,
                 s.surfer_givenname, s.surfer_surname,
                 s.surfer_lastlogin, s.surfer_registration,
                 b.blacklist_type, b.blacklist_match
            FROM %s s, %s b
           WHERE (surfer_handle ".$partLike." AND blacklist_type = 'handle')
              OR (surfer_email ".$partLike." AND blacklist_type = 'email')";
    $params = array($this->tableSurfer, $this->tableBlacklist);

    if ($res = $this->databaseQueryFmt($query, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->checkedAllSurfersAgainstBlacklist[$row['surfer_id']] = $row;
      }
    }

    return count($this->checkedAllSurfersAgainstBlacklist) > 0;
  }

  /**
  * Generate the table of surfers checked against the blacklists.
  *
  * @return string
  */
  function getSurfersCheckedAgainstBlacklistXml() {
    $result = '';
    $surfers = &$this->checkedAllSurfersAgainstBlacklist;

    $result .= sprintf(
      '<listview title="%s">',
      papaya_strings::escapeHTMLChars($this->_gt('Surfers with blacklisted handles or emails'))
    );
    $result .= '<cols>';
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Handle'))
    );
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Email'))
    );
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Name'))
    );
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Registration'))
    );
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Last login'))
    );
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Match type'))
    );
    $result .= sprintf(
      '<col>%s</col>',
      papaya_strings::escapeHTMLChars($this->_gt('Match value'))
     );
    $result .= '</cols>';
    $result .= '<items>';
    foreach ($surfers as $surferId => $surfer) {
      $image = ($surfer['blacklist_type'] == 'email')
        ? $this->images['items-mail']
        : $this->images['items-user'];
      $result .= sprintf(
        '<listitem title="%s" href="%s">',
        papaya_strings::escapeHTMLChars($surfer['surfer_handle']),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('mode' => 0, 'cmd' => 'edit', 'id' => $surferId))
        )
      );
      $result .= sprintf(
        '<subitem>%s</subitem>',
        papaya_strings::escapeHTMLChars($surfer['surfer_email'])
      );
      $result .= sprintf(
        '<subitem>%s %s</subitem>',
        papaya_strings::escapeHTMLChars($surfer['surfer_givenname']),
        papaya_strings::escapeHTMLChars($surfer['surfer_surname'])
      );
      $result .= sprintf(
        '<subitem>%s</subitem>',
        ($surfer['surfer_registration'] > 0)
          ? date('Y-m-d H:i:s', $surfer['surfer_registration'])
          : papaya_strings::escapeHTMLChars($this->_gt('Unknown'))
      );
      $result .= sprintf(
        '<subitem>%s</subitem>',
        ($surfer['surfer_lastlogin'] > 0)
          ? date('Y-m-d H:i:s', $surfer['surfer_lastlogin'])
          : papaya_strings::escapeHTMLChars($this->_gt('Not yet'))
      );
      $result .= sprintf(
        '<subitem><glyph src="%s"/></subitem>',
        papaya_strings::escapeHTMLChars($image)
      );
      $result .= sprintf(
        '<subitem>%s</subitem>',
        papaya_strings::escapeHTMLChars(
          $this->convertFromSqlLikeValue($surfer['blacklist_match'])
        )
      );
      $result .= '</listitem>';
    }
    $result .= '</items>';
    $result .= '</listview>';

    return $result;
  }
}

?>
