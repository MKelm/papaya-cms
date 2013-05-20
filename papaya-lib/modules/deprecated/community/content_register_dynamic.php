<?php
/**
* Page module - User registration with dynamic data fields
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
* @deprecated functionality completely included in content_register.php
* @version $Id: content_register_dynamic.php 38470 2013-04-30 14:56:54Z kersken $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Basic class for check conditions
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Page module - User registration with dynamic data fields
*
* @package Papaya-Modules
* @subpackage _Base-Community
* @deprecated use content_register instead
*/
class content_register_dynamic extends base_content {

  /**
  * Papaya database table surfer
  * @var string
  */
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  /**
  * Papaya database table link
  * @var string
  */
  var $tableLink = PAPAYA_DB_TBL_SURFERPERMLINK;

  /**
  * Papaya database table surferchangerequests
  * @var string
  */
  var $tableChangeRequests = PAPAYA_DB_TBL_SURFERCHANGEREQUESTS;

  /**
  * Base surfers
  * @var surfer_admin
  */
  var $baseSurfers = NULL;

  /**
  * Available status
  * @var array
  */
  var $availableStatus = array(1 => 1, 2 => 2);

  /**
  * Name of parameter group.
  * @var string
  */
  var $paramName = 'srg';

  /**
  * Fields to edit
  * @var array
  */
  var $editGroups = array(
    array(
      'General',
      'categories-content',
      array(
        'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, NULL, ''),
        'text_basic' => array('Text', 'isSomeText', FALSE, 'richtext', 5, ''),
        'Settings',
        'blacklist_check' => array(
          'Check surfer handles',
          'isNum',
          TRUE,
          'yesno',
          50,
          'Check surfer handles against black list',
          0
        ),
        'dynamic_class' => array(
          'Dynamic data category', 'isNum', FALSE, 'function', 'callbackClasses'
        ),
        'Permissions',
        'default_group' =>
          array('Default group', 'isNum', TRUE, 'function', 'getSurferGroupCombo'),
        'default_status' =>
          array('Default status', 'isNum', TRUE, 'function', 'getSurferStatusCombo')
      ),
    ),
    array(
      'Captions',
      'items-message',
      array(
        'Captions',
        'caption_username' => array('Username', 'isNoHTML', TRUE, 'input', 100, '', 'Username'),
        'caption_givenname' => array('Givenname', 'isNoHTML', TRUE, 'input', 50, '', 'Givenname'),
        'caption_surname' => array('Surname', 'isNoHTML', TRUE, 'input', 50, '', 'Surname'),
        'caption_gender' => array('Gender', 'isNoHTML', TRUE, 'input', 50, '', 'Gender'),
        'caption_female' => array('female', 'isNoHTML', TRUE, 'input', 50, '', 'female'),
        'caption_male' => array('male', 'isNoHTML', TRUE, 'input', 50, '', 'male'),
        'caption_password' => array('Password', 'isNoHTML', TRUE, 'input', 50, '', 'Password'),
        'caption_confirm_password' =>
          array('Confirm Password', 'isNoHTML', TRUE, 'input', 50, '', 'Confirm Password'),
        'caption_email' => array('Email', 'isNoHTML', TRUE, 'input', 100, '', 'Email'),
        'caption_confirm_email' =>
          array('Confirm Email', 'isNoHTML', TRUE, 'input', 100, '', 'Confirm Email'),
        'caption_submit' =>
          array('Submitbutton', 'isNoHTML', TRUE, 'input', 100, '', 'Sign up')
      )
    ),
    array(
      'Messages',
      'items-dialog',
      array(
        'Messages',
        'Success' => array(
          'Registration succeeded', 'isNoHTML', TRUE, 'textarea', 5, '', 'Registration succeeded'
        ),
        'verified' => array(
          'Verification succeeded', 'isNoHTML', TRUE, 'textarea', 5, '', 'Verification succeded'
        ),
        'not_verified' => array(
          'Verification failed', 'isNoHTML', TRUE, 'textarea', 5, '', 'Verification failed'
        ),
        'Error messages',
        'error_user_exists' => array(
          'User Exists', 'isNoHTML', TRUE, 'input', 200, '', 'Error, user exists'
        ),
        'error_username_invalid' => array(
          'Username invalid', 'isNoHTML', TRUE, 'input', 200, '', 'Error, user name invalid'
        ),
        'error_email_exists' => array(
          'Email Exists', 'isNoHTML', TRUE, 'input', 200, '', 'Error, email exists'
        ),
        'error_same_data' => array(
          'Same Data', 'isNoHTML', TRUE, 'input', 200, '', 'Error, same data'
        ),
        'error_password_entry' => array(
          'Password Entry', 'isNoHTML', TRUE, 'input', 200, '', 'Error in password entry'
        ),
        'error_password_too_short' => array(
          'Password too short', 'isNoHTML', TRUE, 'input', 200, '', 'Password too short'
        ),
        'error_password_equals_handle' => array(
          'Password equals handle',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Password is equal to username'
        ),
        'error_password_blacklisted' => array(
          'Password blacklisted', 'isNoHTML', TRUE, 'input', 200, '', 'Illegal password'
        ),
        'error_email_entry' =>
          array('Email Entry', 'isNoHTML', TRUE, 'input', 200, '', 'Error in email entry'
        ),
        'error_datafields_error' => array(
          'Datafields Error', 'isNoHTML', TRUE, 'input', 200, '', 'Error in data fields'
        ),
        'error_mail_send' => array(
          'Mail Send', 'isNoHTML', TRUE, 'input', 200, '', 'Error while sending email'
        ),
        'error_database_input' => array(
          'Database Input', 'isNoHTML', TRUE, 'input', 200, '', 'Error while saving data'
        ),
        'error_registered' => array(
          'Already registered', 'isNoHTML', TRUE, 'input', 200, '', 'Error, already registered'
        )
      )
    ),
    array(
      'E-Mail',
      'items-mail',
      array(
        'Email',
        'mailfrom_name' => array ('Mailfrom Name', 'isNoHTML', TRUE, 'input', 200, ''),
        'mailfrom_email' => array ('Mailfrom Email', 'isEmail', TRUE, 'input', 200, ''),
        'subject' => array('Subject', 'isNoHTML', TRUE, 'input', 200, ''),
        'message' => array(
          'Message',
          'isNoHTML',
          TRUE,
          'textarea',
          10,
          '',
          "Name: {%NAME%}\nTitle: {%TITLE%}\nEmail: {%EMAIL%}\n
          Project: {%PROJECT%}\nLink: {%LINK%}\n"
        ),
        'send_notification_email' => array(
          'Notification email',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'Send a notification email with user data if a confirmation email has been sent.',
          1
        )
      )
    )
  );

  /**
  * Internal helper function to initialize base_surfers object
  *
  * @access private
  */
  function _initBaseSurfers() {
    if (!(isset($this->baseSurfers) && is_object($this->baseSurfers))) {
      include_once(dirname(__FILE__).'/base_surfers.php');
      $this->baseSurfers = new surfer_admin($this->msgs);
    }
  }

  /**
  * Get a combo selection field to select a surfer group.
  *
  * @access public
  * @param string $name Field name
  * @param array $element Field element configuration
  * @param string $data Current field value
  * @return string $result XML
  */
  function getSurferGroupCombo($name, $element, $data) {
    $this->_initBaseSurfers();
    $this->baseSurfers->loadGroups();
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      $this->paramName,
      $name
    );
    if (isset($this->baseSurfers->groupList) && is_array($this->baseSurfers->groupList)) {
      foreach ($this->baseSurfers->groupList as $groupId => $group) {
        $selected = ($groupId == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%d"%s>%s</option>',
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
  * Get a combo selection field to select a surfer status.
  *
  * @access public
  * @param string $name Field name
  * @param array $element Field element configuration
  * @param string $data Current field value
  * @return string $result XML
  */
  function getSurferStatusCombo($name, $element, $data) {
    $this->_initBaseSurfers();
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      $this->paramName,
      $name
    );
    $this->_initBaseSurfers();
    if (isset($this->baseSurfers->status) && is_array($this->baseSurfers->status)) {
      foreach ($this->baseSurfers->status as $statusId => $status) {
        if (isset($this->availableStatus[$statusId])) {
          $selected = ($statusId == $data) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%d"%s>%s</option>',
            (int)$statusId,
            $selected,
            papaya_strings::escapeHTMLChars($status)
          );
        }
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    $result = sprintf(
      '<title>%s</title>'.LF,
      (!empty($this->data['title'])) ?
        papaya_strings::escapeHTMLChars($this->data['title']) : ''
    );
    return $result;
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();

    $result = sprintf(
      '<title>%s</title>'.LF,
      (!empty($this->data['title'])) ?
        papaya_strings::escapeHTMLChars($this->data['title']) : ''
    );
    $this->initializeParams();
    $this->backLink = '';

    // If a valid surfer is logged in, give him/her a login link only
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $surferObj = &base_surfer::getInstance();
    if ($surferObj->isValid) {
      $result .= sprintf(
        '<registered>%s</registered>',
        papaya_strings::escapeHTMLChars($this->data['error_registered'])
      );
    } else {
      if (isset($this->parentObj->topicId)) {
        $this->baseLink = $this->getBaseLink($this->parentObj->topicId);
      }
      if (isset($this->params['save']) && $this->params['save'] > 0 ) {
        $this->initializeOutputForm();
        $result .= $this->saveOutput();
      } else {
        $result .= $this->getOutput();
      }
    }
    return $result;
  }

  /**
  * Get output
  *
  * @access public
  * @return string $result XML
  */
  function getOutput() {
    $result = '<registerpage>';
    if (isset($this->data['text_basic'])) {
      $result .= sprintf(
        '<text>%s</text>',
        $this->getXHTMLString($this->data['text_basic'])
      );
    }
    if (isset($_REQUEST[$this->paramName.'_id'])) {
      if ($this->makeValid($_REQUEST[$this->paramName.'_id'])) {
        $result .= sprintf(
          '<success>%s</success>', papaya_strings::escapeHTMLChars($this->data['verified'])
        );
      } else {
        $this->getErrorXML(papaya_strings::ensureUTF8($this->data['not_verified']));
      }
    } else {
      $result .= $this->getOutputForm();
    }
    $result .= '</registerpage>';
    return $result;
  }

  /**
  * Set surfer to valid status.
  *
  * @access public
  * @param string $confirmString Confirmation token
  * @return Status (status changed)
  */
  function makeValid($confirmString) {
    $surferId = '';
    $sql = "SELECT surferchangerequest_id, surferchangerequest_surferid,
                   surferchangerequest_expiry
              FROM %s
             WHERE surferchangerequest_token = '%s'";
    $params = array($this->tableChangeRequests, $confirmString);
    $this->_initBaseSurfers();
    if ($res = $this->baseSurfers->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $changeId = $row['surferchangerequest_id'];
        $surferId = $row['surferchangerequest_surferid'];
        $expiry = $row['surferchangerequest_expiry'];
      }
    }
    if ($surferId != '' && $expiry >= time()) {
      if (isset($this->data['default_status'])) {
        $newStatus = $this->data['default_status'];
      } else {
        $newStatus = 1;
      }
      $data = array(
        'surfer_valid' => $newStatus,
        'surfer_registration' => time()
      );
      $this->baseSurfers->databaseUpdateRecord(
        $this->tableSurfer, $data, 'surfer_id', $surferId
      );
      $this->baseSurfers->databaseDeleteRecord(
        $this->tableChangeRequests, 'surferchangerequest_id', $changeId
      );
    } else {
      return FALSE; // Error: Invalid or expired token
    }
    return TRUE;
  }

  /**
  * Save / add user and get xml output.
  *
  * @access public
  * @return string $result Page xml with form, errors or success message
  */
  function saveOutput() {
    $this->_initBaseSurfers();
    $result = '<registerpage>';
    if (isset($this->data['text_basic'])) {
      $result .= sprintf(
        '<text>%s</text>',
        $this->getXHTMLString($this->data['text_basic'])
      );
    }
    if ($this->outputDialog->modified()) {
      if ($this->outputDialog->checkDialogInput()) {
        $emailCompare = strcmp(
          $this->outputDialog->params['surfer_email_1'],
          $this->outputDialog->params['surfer_email_2']
        );
        if (0 == $emailCompare) {
          $passwordCompare = strcmp(
            $this->outputDialog->params['surfer_password_1'],
            $this->outputDialog->params['surfer_password_2']
          );
          if (0 == $passwordCompare) {
            if ($this->checkHandle()) {
              $checkPassword = $this->checkPassword();
              if ($checkPassword === 0) {
                if (!$this->checkEmail($this->outputDialog->params['surfer_email_1'])) {
                  $addUserCheck = $this->addUser();
                  if ($addUserCheck) {
                    // Now save the dynamic data, if necessary
                    if (isset($this->data['dynamic_class']) &&
                        is_array($this->data['dynamic_class']) &&
                        !empty($this->data['dynamic_class'])) {
                      // Get the field names
                      $dynFieldNames = $this->baseSurfers->getDataFieldNames(
                        $this->data['dynamic_class']
                      );
                      // Get those fields that are actually set
                      $dynFields = array();
                      foreach ($dynFieldNames as $fieldName) {
                        if (isset($this->params['dynamic_'.$fieldName])) {
                          $dynFields[$fieldName] = $this->params['dynamic_'.$fieldName];
                        }
                      }
                      if (!empty($dynFields)) {
                        $this->baseSurfers->setDynamicData($this->newId, $dynFields);
                      }
                    }
                    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
                    $this->surferObj = &base_surfer::getInstance();
                    $result .= '<success>'.$this->data['Success'].'</success>';
                    $this->surferObj->login(
                      $this->outputDialog->params['surfer_email_1'],
                      $this->outputDialog->params['surfer_password_2']
                    );
                  } elseif ($addUserCheck == -1) {
                    $result .= $this->getErrorXML($this->data['error_mail_send']);
                    $result .= $this->getOutputForm();
                  } elseif ($addUserCheck == -2) {
                    $result .= $this->getErrorXML($this->data['error_database_input']);
                    $result .= $this->getOutputForm();
                  }
                } else {
                  $result .= $this->getErrorXML($this->data['error_email_exists']);
                  $result .= $this->getOutputForm();
                }
              } else {
                if ($checkPassword + 4 <= 0) {
                  $result .= $this->getErrorXML(
                    $this->data['error_password_blacklisted'],
                    'surfer_password_1'
                  );
                  $checkPassword += 4;
                }
                if ($checkPassword + 2 <= 0) {
                  $result .= $this->getErrorXML(
                    $this->data['error_password_equals_handle'],
                    'surfer_password_1'
                  );
                  $checkPassword += 2;
                }
                if ($checkPassword + 1 <= 0) {
                  $result .= $this->getErrorXML(
                    $this->data['error_password_too_short'],
                    'surfer_password_1'
                  );
                }
                $result .= $this->getOutputForm();
              }
            } else {
              $result .= $this->getErrorXML($this->data['error_user_exists']);
              $result .= $this->getOutputForm();
            }
          } else {
            $result .= $this->getErrorXML($this->data['error_password_entry']);
            $result .= $this->getOutputForm();
          }
        } else {
          $result .= $this->getErrorXML($this->data['error_email_entry']);
          $result .= $this->getOutputForm();
        }
      } else {
        $result .= $this->getErrorXML($this->data['error_terms']);
        $result .= $this->getOutputForm($this->data['error_terms']);
      }
    } else {
      // nothing entered, so no error to report
      $result .= $this->getOutputForm();
    }
    $result .= '</registerpage>';
    return $result;
  }

  /**
  * Get escaped XML for error messages
  *
  * @param string $message error message to display
  * @param string $field field for which the error applies (optional, default '')
  * @return string XML
  */
  function getErrorXML($message, $field = '') {
    $for = '';
    if ($field != '') {
      $for = sprintf(' for="%s"', $field);
    }
    return sprintf(
      '<error%s>%s</error>'.LF,
      $for,
      papaya_strings::escapeHTMLChars($message)
    );
  }

  /**
  * Save / add new user
  *
  * @access public
  * @return mixed TRUE on success or integer as error number
  */
  function addUser() {
    $this->_initBaseSurfers();
    // Create token to confirm registration by mail
    srand((double)microtime() * 1000000);
    $rand = uniqid(rand());
    $confirmString = md5($rand);
    // Create new surfer id
    $newId = $this->baseSurfers->createSurferId();
    $this->newId = $newId;
    // Data for surfer table
    $surferData = array(
      'surfer_id' => $newId,
      'surfer_handle' => $this->outputDialog->params['surfer_handle'],
      'surfer_password' => $this->baseSurfers->getPasswordHash(
        $this->outputDialog->params['surfer_password_1']
      ),
      'surfer_givenname' => $this->outputDialog->params['surfer_givenname'],
      'surfer_surname' => $this->outputDialog->params['surfer_surname'],
      'surfer_gender' => $this->outputDialog->params['surfer_gender'],
      'surfer_email' => $this->outputDialog->params['surfer_email_1'],
      'surfer_valid' => FALSE,
      'surfergroup_id' => isset($this->data['default_group']) ?
        (int)$this->data['default_group'] : ''
     );
    // Insert surfer record
    $insert = $this->baseSurfers->databaseInsertRecord(
      $this->tableSurfer, NULL, $surferData
    );
    $now = time();
    // Data for change request table (token)
    $changeRequestData = array(
      'surferchangerequest_surferid' => $newId,
      'surferchangerequest_type' => 'register',
      'surferchangerequest_token' => $confirmString,
      'surferchangerequest_time' => $now,
      'surferchangerequest_expiry' => $now + 86400
    );
    // Insert change request record
    $confirm = $this->baseSurfers->databaseInsertRecord(
      $this->tableChangeRequests, 'surferchangerequest_id', $changeRequestData
    );
    if ($insert && $confirm) {
      if ($this->sendMail($confirmString)) {
        return $insert;
      } else {
        return -1;
      }
    } else {
      return -2;
    }
  }

  /**
  * Send a confirmation email to finalize the registration process.
  *
  * @access public
  * @param string $confirmString Confirmation token
  * @return boolean Send status
  */
  function sendMail($confirmString) {
    include_once (PAPAYA_INCLUDE_PATH.'system/sys_email.php');
    if (preg_match_all('/%([\w]+)%/', $this->data['message'], $regs)) {
      $placeHolder = $regs[0];
    }
    if (isset($placeHolder) && is_array($placeHolder)) {
      foreach ($placeHolder as $key => $val) {
        $tmp[$val] = '';
      }
    }
    $placeHolder = &$tmp;
    $repl = $placeHolder;
    $repl['NAME'] = $this->outputDialog->params['surfer_handle'];
    $repl['EMAIL'] = $this->outputDialog->params['surfer_email_1'];
    $repl['TITLE'] = !empty($this->data['title']) ? $this->data['title'] : '';
    $repl['PROJECT'] = PAPAYA_PROJECT_TITLE;
    $repl['LINK'] = $this->getAbsoluteURL(
      $this->getWebLink(
        NULL,
        NULL,
        NULL,
        array($this->paramName.'_id' => $confirmString),
        NULL,
        $this->parentObj->topic['TRANSLATION']['topic_title']
      )
    );

    $subject = $this->data['subject'];
    $email = new email();
    $email->addAddress(
      $this->outputDialog->data['surfer_email_1'],
      $this->outputDialog->data['surfer_handle']
    );
    $email->setSender($this->data['mailfrom_email'], $this->data['mailfrom_name']);
    $email->setSubject($subject, $repl);
    $email->setBody($this->data['message'], $repl);
    $success = $email->send();
    if (!$success) {
      $this->logMsg(
        MSG_WARNING,
        PAPAYA_LOGTYPE_SURFER,
        sprintf(
          'Cannot send mail to %s.',
          array($this->outputDialog->data['surfer_email_1'])
        ),
        sprintf(
          'Cannot send mail to %s.',
          array($this->outputDialog->data['surfer_email_1'])
        )
      );
      return FALSE;
    } elseif ($this->data['send_notification_email'] == 1 && $this->data['mailfrom_email'] != '') {
      // If the opt-in email is sent to the user and we have a sender mail,
      // send notification to that email address
      $infoSubject = '[Community] New registration';
      $infoBody =
        'Name: '.$this->outputDialog->data['surfer_handle'].LF.
        'Email: '.$this->outputDialog->data['surfer_email_1'].LF;
      $email = new email();
      $email->addAddress($this->data['mailfrom_email']);
      $email->setSubject($infoSubject, $repl);
      $email->setBody($infoBody, $repl);
      $email->send();
    }
    return TRUE;
  }

  /**
  * Check if a surfer handle exists already.
  *
  * @access public
  * @return boolean Status
  */
  function checkHandle() {
    $this->_initBaseSurfers();
    if ($this->baseSurfers->existHandle(
          $this->outputDialog->params['surfer_handle'],
          TRUE)) {
      return FALSE;
    }
    // Black list check
    if (isset($this->data['blacklist_check']) && $this->data['blacklist_check'] != FALSE) {
      if (!$this->baseSurfers->checkHandle($this->outputDialog->params['surfer_handle'])) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
  * Check if a given surfer email exists
  *
  * @see surfers_admin::existEmail Detailed description
  * @access public
  * @param string $email Surfer email
  * @return boolean Status (exists)
  */
  function checkEmail($email) {
    $this->_initBaseSurfers();
    return $this->baseSurfers->existEmail($email, TRUE);
  }

  /**
  * Check password according to current password policy.
  *
  * @see base_surfers::checkPasswordForPolicy
  * @return integer 0 if password complies to the policy, a negative value otherwise
  */
  function checkPassword() {
    $this->_initBaseSurfers();
    $password = $this->outputDialog->params['surfer_password_1'];
    $handle = $this->outputDialog->params['surfer_handle'];
    return $this->baseSurfers->checkPasswordForPolicy($password, $handle);
  }


  /**
  * Initialize the output form and return it's xml.
  *
  * @access public
  * @return string XML
  */
  function getOutputForm() {
    $this->initializeOutputForm();
    return $this->outputDialog->getDialogXML();
  }

  /**
  * Initialize registration output form
  *
  * @todo Return status value
  * @access public
  * @param boolean $loadParams Load dialog parameters from page parameters
  */
  function initializeOutputForm($loadParams = TRUE) {
    if (!(isset($this->outputDialog) && is_object($this->outputDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array('save' => 1);

      // Extract data given by an invitation and store it as predefined form value
      $this->initializeSessionParam(
        isset($this->data['invitation_session_identifier']) ?
          $this->data['invitation_session_identifier'] : NULL
      );
      if ($invitationEmail = $this->getSessionValue('email')) {
        $data = array('surfer_email_1' => $invitationEmail);
      } else {
        $data = array();
      }
      // Load param data destroyed by initializeSessionParam
      $this->initializeParams($this->paramName);
      $fields = array(
        'surfer_handle' => array($this->data['caption_username'],
            '/^[a-z._\d-]{4,'.PAPAYA_COMMUNITY_HANDLE_MAX_LENGTH.'}$/i', TRUE, 'input', 50),
        'surfer_password_1' => array(
          $this->data['caption_password'], 'isPassword', TRUE, 'password', 50
        ),
        'surfer_password_2' => array(
          $this->data['caption_confirm_password'], 'isPassword', TRUE, 'password', 50
        ),
        'surfer_givenname' => array(
          $this->data['caption_givenname'], 'isNoHTML', TRUE, 'input', 50
        ),
        'surfer_surname' => array(
          $this->data['caption_surname'], 'isNoHTML', TRUE, 'input', 50
        ),
        'surfer_gender' => array(
          $this->data['caption_gender'],
          '/^[fm]$/',
          TRUE,
          'combo',
          array('f' => $this->data['caption_female'], 'm' => $this->data['caption_male'])
        ),
        'surfer_email_1' => array(
          $this->data['caption_email'], 'isEmail', TRUE, 'input', 100
        ),
        'surfer_email_2' => array(
          $this->data['caption_confirm_email'], 'isEmail', TRUE, 'input', 100
        )
      );
      // Add dynamic data fields
      if (isset($this->data['dynamic_class']) &&
          is_array($this->data['dynamic_class']) &&
          !empty($this->data['dynamic_class'])) {
        $this->_initBaseSurfers();
        $dynFields = $this->baseSurfers->getDynamicEditFields(
          $this->data['dynamic_class'],
          'dynamic',
          $this->parentObj->topic['TRANSLATION']['lng_id']
        );
        $fields = array_merge($fields, $dynFields);
      }
      $this->outputDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->outputDialog->baseLink = $this->baseLink;
      $this->outputDialog->dialogTitle = htmlspecialchars(@$this->data['title']);
      $this->outputDialog->buttonTitle = isset($this->data['caption_submit']) ?
        $this->data['caption_submit'] : NULL;
      $this->outputDialog->dialogDoubleButtons = FALSE;
      $this->outputDialog->msgs = &$this->msgs;
      if ($loadParams) {
        $this->outputDialog->loadParams();
      }
    }
  }

  /**
  * Get checkboxes to select dynamic data categories by callback.
  *
  * @param string $name Field name
  * @param array $element Field element configuration
  * @param string $data Current field data value
  * @return string $result XML with checkbox inputs
  */
  function callbackClasses($name, $element, $data) {
    $this->_initBaseSurfers();
    $result = '';
    $lng = $this->parentObj->topic['TRANSLATION']['lng_id'];
    $commonTitle = $this->_gt('Category');
    $sql = "SELECT c.surferdataclass_id,
                   ct.surferdataclasstitle_classid,
                   ct.surferdataclasstitle_name,
                   ct.surferdataclasstitle_lang
              FROM %s AS c
              LEFT OUTER JOIN %s AS ct
                ON c.surferdataclass_id = ct.surferdataclasstitle_classid
             WHERE ct.surferdataclasstitle_lang = %d";
    $sqlParams = array(
      $this->baseSurfers->tableDataClasses,
      $this->baseSurfers->tableDataClassTitles,
      $lng
    );
    if ($res = $this->baseSurfers->databaseQueryFmt($sql, $sqlParams)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (isset($row['surferdataclasstitle_name']) &&
            trim($row['surferdataclasstitle_name']) != '') {
          $title = $row['surferdataclasstitle_name'];
        } else {
          $title = sprintf('%s %d', $commonTitle, $row['surferdataclass_id']);
        }
        $checked = (is_array($data) && in_array($row['surferdataclass_id'], $data)) ?
          ' checked="checked"' : '';
        $result .= sprintf(
          '<input type="checkbox" name="%s[%s][]" value="%d" %s />'.LF.
          '%s'.LF,
          $this->paramName,
          $name,
          $row['surferdataclass_id'],
          $checked,
          $title
        );
      }
    }
    return $result;
  }
}

?>
