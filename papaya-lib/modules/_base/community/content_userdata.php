<?php
/**
* Page module - Mixed base and dynamic user data form
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
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
* @version $Id: content_userdata.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - Mixed base and dynamic user data form
*
* Surfer profile data change form
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class content_userdata extends base_content {

  /**
  * Papaya database table surfer
  * @var string
  */
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  /**
  * Papaya database table surferpermlink
  * @var string
  */
  var $tableLink = PAPAYA_DB_TBL_SURFERPERMLINK;

  /**
  * Papaya database table surferchangerequests
  * @var string
  */
  var $tableChangeRequests = PAPAYA_DB_TBL_SURFERCHANGEREQUESTS;

  /**
  * List of change requests
  * @var array
  */
  var $surferChangeRequests = NULL;

  /**
  * Base surfers
  * @var surfer_admin
  */
  var $baseSurfers = NULL;

  /**
  * Input error
  * @var string
  */
  var $inputError = '';

  /**
  * Groups and fields to edit
  * @var array
  */
  var $editGroups = array(
    array(
      'General',
      'categories-content',
      array(
        'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, ''),
        'subtitle' => array('Subtitle', 'isNoHTML', FALSE, 'input', 400, ''),
        'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 10),
        'nl2br' => array(
          'Automatic linebreak',
          'isNum',
          FALSE,
          'translatedcombo',
          array(0 => 'Yes', 1 => 'No'),
          'Apply linebreaks from input to the HTML output.'
        ),
        'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10),
        'change_email' => array(
          'Change email',
          'isNum',
          TRUE,
          'yesno',
          '',
          'Determines whether surfers can change their email address.',
          1
        ),
        'edit_changes' => array(
          'Edit change requests?',
          'isNum',
          TRUE,
          'yesno',
          10,
          'Determines whether surfers can view their own change requests',
          0
        ),
        'need_oldpassword' => array(
          'Confirm with password?',
          'isNum',
          TRUE,
          'yesno',
          10,
          'Set to "No" if surfers do not need to enter their existing password to confirm changes.',
          1
        ),
        'dynamic_class' => array(
          'Dynamic data category', 'isNum', FALSE, 'function', 'callbackClasses'
        )
      )
    ),
    array(
      'Email',
      'items-mail',
      array(
        'Administrative email',
        'sender_name' => array('Sender name', 'isNoHTML', FALSE, 'input', 200, ''),
        'sender_email' => array('Sender email', 'isEMail', FALSE, 'input', 200, ''),
        'recipient_name' => array('Recipient name', 'isNoHTML', FALSE, 'input', 200, ''),
        'recipient_email' => array('Recipient email', 'isNoHTML', FALSE, 'input', 200, ''),
        'email_subject' => array(
          'Subject', 'isNoHTML', FALSE, 'input', 200, '', 'User data changed'
        ),
        'email_intro' => array(
          'Intro text',
          'isNoHTML',
          FALSE,
          'textarea',
          7,
          'Use the placeholder {%USER%} for username/realname, {%TIME%} for timestamp.',
          'User {%USER%} changed personal data at {%TIME%}'
        ),
        'email_line' => array(
          'Change message',
          'isNoHTML',
          FALSE,
          'input',
          200,
          'Use the placeholder {%FIELD%} for field name, {%OLDVALUE%} for old value,
          and {%NEWVALUE%} for new value.',
          '{%FIELD%} changed from "{%OLDVALUE%}" to "{%NEWVALUE%}"'
        ),
        'Confirmation email',
        'Mail_From_Name' => array('Name', 'isNoHTML', TRUE, 'input', '200', ''),
        'Mail_From_Address' => array('Address', 'isEMail', TRUE, 'input', 200, ''),
        'Mail_Subject' => array('Subject', 'isNoHTML', TRUE, 'input', 200, '', 'Confirmation'),
        'Mail_Body' => array(
          'Message', 'isNoHTML', TRUE, 'textarea', 7, '', 'Confirm address change: {%LINK%}'
        ),
        'Mail_Expiry' => array(
          'Request expires in (hours)', 'isNum', TRUE, 'input', 200, '', 24
        )
      )
    ),
    array(
      'Messages',
      'items-dialog',
      array(
        'Deleted' => array('Deleted', 'isNoHTML', TRUE, 'input', 200, '', 'Deleted.'),
        'Saved' => array('Saved', 'isNoHTML', TRUE, 'input', 200, '', 'Saved.'),
        'Login_Page_Id' => array(
          'Login page ID',
          'isNum',
          TRUE,
          'pageid',
          200,
          'The login page is used to confirm the email change.'
        ),
        'Error_No_User' => array('No user', 'isNoHTML', TRUE, 'input', 200, '', 'No user.'),
        'Error_Input' => array('Input error', 'isNoHTML', TRUE, 'input', 200, '', 'Input error.'),
        'Error_Email_exists' => array(
          'Email address exists', 'isNoHTML', TRUE, 'input', 200, '', 'E-Mail exists.'
        ),
        'Error_Password' => array(
          'Wrong password', 'isNoHTML', TRUE, 'input', 200, '', 'Wrong password.'
        ),
        'Error_Invalid_Password' => array(
          'Invalid password', 'isNoHTML', TRUE, 'input', 200, '', 'Invalid password.'
        ),
        'Error_Old_Password' => array(
          'Wrong old password', 'isNoHTML', TRUE, 'input', 200, '', 'Wrong old password.'
        ),
        'Error_Database' => array(
          'Database error', 'isNoHTML', TRUE, 'input', 200, '', 'Database error.'
        )
      )
    ),
    array(
      'Captions',
      'items-message',
      array(
        'caption_req_delete' => array(
          'Delete change request', 'isNoHTML', TRUE, 'input', 200, '', 'Delete'
         ),
        'caption_req_data' => array(
          'Change request data', 'isNoHTML', TRUE, 'input', 200, '', 'Data'
         ),
        'caption_req_date' => array(
          'Change request date', 'isNoHTML', TRUE, 'input', 200, '', 'Date'
         ),
        'caption_req_expiry' => array(
          'Change request expiry', 'isNoHTML', TRUE, 'input', 200, '', 'Expiry'
         ),
        'caption_handle' => array(
          'Handle', 'isNoHTML', TRUE, 'input', 200, '', 'Handle'
         ),
        'caption_email' => array(
          'Email', 'isNoHTML', TRUE, 'input', 200, '', 'Email'
         ),
        'caption_change_email' => array(
          'Change email', 'isNoHTML', TRUE, 'input', 200, '', 'Change email'
         ),
        'caption_email_verification' => array(
          'Email verification', 'isNoHTML', TRUE, 'input', 200, '', 'Email verification'
         ),
        'caption_group' => array(
          'Group', 'isNoHTML', TRUE, 'input', 200, '', 'Group'
         ),
        'caption_givenname' => array(
          'Givenname', 'isNoHTML', TRUE, 'input', 200, '', 'Givenname'
         ),
        'caption_surname' => array(
          'Surname', 'isNoHTML', TRUE, 'input', 200, '', 'Surname'
         ),
        'caption_gender' => array(
          'Gender', 'isNoHTML', TRUE, 'input', 200, '', 'Gender'
         ),
        'caption_female' => array(
          'female', 'isNoHTML', TRUE, 'input', 200, '', 'female'
         ),
        'caption_male' => array(
          'male', 'isNoHTML', TRUE, 'input', 200, '', 'male'
         ),
        'caption_avatar' => array(
          'Avatar', 'isNoHTML', TRUE, 'input', 200, '', 'Avatar'
         ),
        'caption_password' => array(
          'Password', 'isNoHTML', TRUE, 'input', 200, '', 'Password'
         ),
        'caption_password_verification' => array(
          'Password verification', 'isNoHTML', TRUE, 'input', 200, '', 'Password verification'
         ),
        'caption_old_password' => array(
          'Old password', 'isNoHTML', TRUE, 'input', 200, '', 'Old password'
         ),
        'send_button_title' => array(
          'Send button title', 'isNoHTML', FALSE, 'input', 70, '', 'Send'
         )
      )
    ),
    array(
      'Long descriptions',
      'categories-help',
      array(
        'Long descriptions',
        'descr_change_email' => array('Change email', 'isNoHTML', TRUE, 'textarea', 7, '',
          'In order to change your email address, please enter the new address into the
           two following fields. A confirmation email will be sent to this address,
           and the new address will only be accepted if you activate it using the
           hyperlink in that email.'),
        'descr_change_password' => array('Change password', 'isNoHTML', TRUE, 'textarea', 7, '',
          'In order to change your password, please enter it into the two following fields.
           Leave these fields blank if you want to keep your existing password.'),
        'descr_enter_password' => array('Enter password', 'isNoHTML', TRUE, 'textarea', 7, '',
          'Please enter your existing password to change your user data.')
      )
    )
  );

  /**
  * Content profile constructor
  *
  * @param object &$owner Owner object
  * @param string $paramName Parameter group name
  */
  function __construct(&$owner, $paramName = 'srg') {
    parent::__construct($owner, $paramName);
    if (!is_object($this->baseSurfers)) {
      include_once(dirname(__FILE__).'/base_surfers.php');
      $this->baseSurfers = new surfer_admin($this->msgs);
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();
  }

  /**
  * Get parsed teaser
  *
  * @return string $result Teaser XML
  */
  function getParsedTeaser() {
    $result = sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    if (isset($this->data['teaser']) && trim($this->data['teaser'] != '')) {
      $result .= sprintf(
        "<text>%s</text>".LF,
        $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
      );
    } elseif (isset($this->data['text']) && trim($this->data['text']) != '') {
      $teaser = str_replace("\r\n", "\n", $this->data['text']);
      if (preg_match("/^(.+)([\n]{2})/sU", $teaser, $regs)) {
        $teaser = $regs[1];
      }
      $result .= sprintf(
        '<text>%s</text>'.LF,
        $this->getXHTMLString($teaser, !((bool)$this->data['nl2br']))
      );
    }
    return $result;
  }

  /**
  * Get parsed data
  *
  * @return string $result Content XML
  */
  function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $this->baseLink = $this->getBaseLink();
    $result = sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    $result .= '<userdata>'.LF;
    if (isset($this->data['need_oldpassword']) && $this->data['need_oldpassword'] == 0) {
      $result .= '<oldpassword required="false"/>'.LF;
    } else {
      $result .= '<oldpassword required="true"/>'.LF;
    }
    $result .= '<descriptions>'.LF;
    $result .= sprintf(
      '<description-change-email content="%s"/>'.LF,
      papaya_strings::escapeHTMLChars($this->data['descr_change_email'])
    );
    $result .= sprintf(
      '<description-change-password content="%s"/>'.LF,
      papaya_strings::escapeHTMLChars($this->data['descr_change_password'])
    );
    $result .= sprintf(
      '<description-enter-password content="%s"/>'.LF,
      papaya_strings::escapeHTMLChars($this->data['descr_enter_password'])
    );
    $result .= '</descriptions>'.LF;
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();
    if (is_object($this->surferObj) && $this->surferObj->isValid == TRUE) {
      $this->initializeOutputForm();
      if (isset($this->params['save']) && $this->params['save'] > 0) {
        if ($this->outputDialog->modified()) {
          if ($this->outputDialog->checkDialogInput()) {
            if (!$this->checkProfileInput()) {
              $msg = $this->inputError;
              $result .= sprintf(
                '<message type="error">%s</message>'.LF, papaya_strings::escapeHTMLChars($msg)
              );
            } else {
              $passwordCheck1 = isset($this->data['need_oldpassword']) &&
                $this->data['need_oldpassword'] == 0;
              $passwordCheck2 = $this->surferObj->getPasswordHash(
                  $this->params['surfer_old_password']
                ) == $this->surferObj->surfer['surfer_password'];
              if ($passwordCheck1 || $passwordCheck2) {
                if ($this->deleteChangeRequests()) {
                  $result .= sprintf(
                    '<deleted>%s</deleted>'.LF,
                    papaya_strings::escapeHTMLChars($this->data['Deleted'])
                  );
                }
                if ($this->saveProfileData()) {
                  $this->sendAdminEmail();
                  $result .= sprintf(
                    '<message type="success">%s</message>'.LF,
                    papaya_strings::escapeHTMLChars($this->data['Saved'])
                  );
                } else {
                  $result .= sprintf(
                    '<message type="error">%s</message>'.LF,
                    papaya_strings::escapeHTMLChars($this->data['Error_Database'])
                  );
                }
              } else {
                $this->outputDialog->inputErrors['surfer_old_password'] = 1;
                $result .= sprintf(
                  '<message type="error">%s</message>'.LF,
                  papaya_strings::escapeHTMLChars($this->data['Error_Password'])
                );
              }
            }
          } elseif (!empty($this->outputDialog->inputErrors) &&
                    !empty($this->outputDialog->inputErrors['surfer_old_password'])) {
            $result .= sprintf(
              '<message type="error">%s</message>'.LF,
              papaya_strings::escapeHTMLChars($this->data['Error_Old_Password'])
            );
          } else {
            $result .= sprintf(
              '<message type="error">%s</message>'.LF,
              papaya_strings::escapeHTMLChars($this->data['Error_Input'])
            );
          }
        }
      }
      // If we want to handle change requests, do it now
      if (isset($this->data['edit_changes']) && $this->data['edit_changes'] != 0) {
        $this->surferChangeRequests = $this->surferObj->getChangeRequests();
        if (isset ($this->surferChangeRequests) && $this->surferChangeRequests != NULL) {
          $result .= $this->addChangeRequestForm();
        }
      }
      $result .= $this->getOutputForm();
    } else {
      $result .= sprintf(
        '<message type="error">%s</message>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Error_No_User'])
      );
    }
    $result .= '</userdata>';
    return $result;
  }

  /**
  * Initialize output form and return output XML.
  *
  * @return string Form XML
  */
  function getOutputForm() {
    $this->initializeOutputForm();
    $this->outputDialog->baseLink = $this->baseLink;
    $this->outputDialog->dialogTitle = '';
    $this->outputDialog->dialogDoubleButtons = FALSE;
    return $this->outputDialog->getDialogXML();
  }

  /**
  * Initialize user profile form
  *
  * @param boolean $loadParams
  * @return boolean
  */
  function initializeOutputForm($loadParams = TRUE) {
    if (!(isset($this->outputDialog) && is_object($this->outputDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array('save' => 1);
      $surfer = &$this->surferObj->surfer;
      $fields = array(
        'surfer_handle'=> array(
          $this->data['caption_handle'], 'isNoHTML', TRUE, 'disabled_input', 20, NULL,
          $surfer['surfer_handle']
        ),
        'surfer_email' => array(
          $this->data['caption_email'], 'isEmail', TRUE, 'disabled_input', 100, NULL,
          $surfer['surfer_email']
        ),
        'surfergroup_title' => array(
           $this->data['caption_group'], 'isNoHTML', TRUE, 'disabled_input', 50, NULL,
           $surfer['surfergroup_title']
         ),
        'surfer_givenname' => array(
          $this->data['caption_givenname'], 'isNoHTML', TRUE, 'input', 100
        ),
        'surfer_surname' => array(
          $this->data['caption_surname'], 'isNoHTML', TRUE, 'input', 100
        ),
        'surfer_gender' => array(
          $this->data['caption_gender'],
          '/^[fm]$/',
          TRUE,
          'combo',
          array('f' => $this->data['caption_female'], 'm' => $this->data['caption_male'])
        ),
        'surfer_password_1' => array(
          $this->data['caption_password'], 'isPassword', FALSE, 'password', 200
        ),
        'surfer_password_2' => array(
          $this->data['caption_password_verification'], 'isPassword', FALSE, 'password', 200
        )
      );
      if ($this->data['change_email']) {
        // surfers can change their email address
        $fields['surfer_new_email'] = array(
          $this->data['caption_change_email'], 'isEmail', FALSE, 'input', 100
        );
        $fields['surfer_new_email_confirm'] = array(
          $this->data['caption_email_verification'], 'isEmail', FALSE, 'input', 100
        );
      }
      if (!isset($this->data['need_oldpassword']) || $this->data['need_oldpassword'] == 1) {
        $fields['surfer_old_password'] = array(
          $this->data['caption_old_password'], 'isPassword', TRUE, 'password', 200
        );
      }
      // Add dynamic data fields
      if (isset($this->data['dynamic_class']) && is_array($this->data['dynamic_class']) &&
          !empty($this->data['dynamic_class'])) {
        $dynFields = $this->baseSurfers->getDynamicEditFields(
          $this->data['dynamic_class'],
          'dynamic',
          $this->parentObj->topic['TRANSLATION']['lng_id'],
          TRUE
        );
        $fields = array_merge($fields, $dynFields);
      }
      // Get existing base data
      $data = array(
        'surfer_surname' =>
          !empty($surfer['surfer_surname']) ? $surfer['surfer_surname'] : '',
        'surfer_givenname' =>
          !empty($surfer['surfer_givenname']) ? $surfer['surfer_givenname'] : '',
        'surfer_gender' =>
          !empty($surfer['surfer_gender']) ? $surfer['surfer_gender'] : ''
      );
      // Get existing dynamic data
      if (isset($this->data['dynamic_class']) && is_array($this->data['dynamic_class']) &&
          !empty($this->data['dynamic_class'])) {
        $fieldNames = $this->baseSurfers->getDataFieldNames($this->data['dynamic_class']);
        $dynData = $this->baseSurfers->getDynamicData(
          !empty($this->surferObj->surferId) ? $this->surferObj->surferId : '',
          $fieldNames
        );
        if ($dynData != NULL) {
          foreach ($dynData as $fieldName => $fieldValue) {
            $data['dynamic_'.$fieldName] = $fieldValue;
          }
        }
      }
      if ($this->data['change_email']) {
        // surfers can change their email address
        $data['surfer_new_email'] = '';
        $data['surfer_new_email_confirm'] = '';
      }
      $this->outputDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->outputDialog->baseLink = $this->baseLink;
      $this->outputDialog->dialogTitle = papaya_strings::escapeHTMLChars(
        !empty($this->data['title']) ? $this->data['title'] : ''
      );
      if (isset($this->data['send_button_title'])) {
        $this->outputDialog->buttonTitle = $this->data['send_button_title'];
      } else {
        $this->dialogDialog->buttonTitle = 'Send';
      }
      $this->outputDialog->dialogDoubleButtons = FALSE;
      $this->outputDialog->msgs = &$this->msgs;
      if ($loadParams) {
        $this->outputDialog->loadParams();
      }
    }
  }

  /**
  * Return a xml to setup a change request form.
  *
  * @return string $text XML
  */
  function addChangeRequestForm() {
    $text = '<changes>'.LF;
    $text .= sprintf(
      '<captions>'.LF.
      '<delete>%s</delete>'.LF.
      '<data>%s</data>'.LF.
      '<date>%s</date>'.LF.
      '<expiry>%s</expiry>'.LF.
      '</captions>'.LF,
      papaya_strings::escapeHTMLChars($this->data['caption_req_delete']),
      papaya_strings::escapeHTMLChars($this->data['caption_req_data']),
      papaya_strings::escapeHTMLChars($this->data['caption_req_date']),
      papaya_strings::escapeHTMLChars($this->data['caption_req_expiry'])
    );
    foreach ($this->surferChangeRequests as $req) {
      $text .= sprintf(
        '<change>'.LF.
        '<id>%s</id>'.LF.
        '<type>%s</type>'.LF.
        '<data>%s</data>'.LF.
        '<date>%s</date>'.LF.
        '<expiry>%s</expiry>'.LF.
        '</change>'.LF,
        papaya_strings::escapeHTMLChars($req['id']),
        papaya_strings::escapeHTMLChars($req['type']),
        papaya_strings::escapeHTMLChars($req['data']),
        papaya_strings::escapeHTMLChars(date('Y-m-d H:i:s', (int)$req['time']))
      );
    }
    $text .= '</changes>';
    return $text;
  }

  /**
  * Get avatar image / thumbnail.
  *
  * Returns an xml with an avatar thumbnail if an surfer avatar exists.
  *
  * @see surfers_admin::getAvatar
  * @return string XML avatar data or empty
  */
  function getAvatar() {
    include_once(dirname(__FILE__).'/base_surfers.php');
    $surferAdmin = new surfer_admin($this->msgs);
    $avatar = $surferAdmin->getAvatar($this->surferObj->surferId);
    if ($avatar) {
      // Make sure only the first component (src) is used
      $src = explode(',', $avatar);
      return sprintf(
        '<avatar caption="%s">'.LF.
        '<papaya:media src="%s" width="96" height="96" resize="max"/>'.LF.
        '</avatar>'.LF,
        $this->data['caption_avatar'],
        $src[0]
      );
    } else {
      return '';
    }
  }

  /**
  * Check profile input
  *
  * @return boolean $result Statuc (valid inputs)
  */
  function checkProfileInput() {
    $result = TRUE;
    if ($this->data['change_email'] && isset($this->params['surfer_new_email']) &&
        trim($this->params['surfer_new_email']) != '') {
      $emailCheck = isset($this->params['surfer_new_email_confirm']) &&
        $this->params['surfer_new_email'] == $this->params['surfer_new_email_confirm'];
      if (!$emailCheck) {
        $this->outputDialog->inputErrors['surfer_new_email'] = 1;
        $this->outputDialog->inputErrors['surfer_new_email_confirm'] = 1;
        $this->inputError = $this->data['Error_Input'];
        $result = FALSE;
      } else {
        // Only check if this is not simply a change of capitalization
        // (otherwise we'll get an inappropriate error)
        if (strtolower($this->params['surfer_new_email']) !=
            strtolower($this->surferObj->surfer['surfer_email'])) {
          // Check whether the new mail address already exists
          // (which is not allowed, of course!)
          $checkMailId = '';
          $sql = "SELECT surfer_id
                    FROM %s
                   WHERE surfer_email = '%s'";
          $params = array($this->tableSurfer, $this->params['surfer_new_email']);
          if ($res = $this->baseSurfers->databaseQueryFmt($sql, $params)) {
            while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
              $checkMailId = $row['surfer_id'];
            }
          }
          if ($checkMailId) {
            $this->outputDialog->inputErrors['surfer_new_email'] = 1;
            $this->outputDialog->inputErrors['surfer_new_email_confirm'] = 1;
            $this->inputError = $this->data['Error_Email_exists'];
            $result = FALSE;
          }
        }
      }
    }
    if (isset($this->params['surfer_password_1']) &&
        trim($this->params['surfer_password_1']) != '') {
      $passwordCheck = isset($this->params['surfer_password_2']) &&
        $this->params['surfer_password_1'] == $this->params['surfer_password_2'];
      if ($passwordCheck) {
        if ($this->surferObj->surfer['surfer_handle']) {
          $handle = $this->surferObj->surfer['surfer_handle'];
        }
        $passwordCheck = -1 != $this->baseSurfers->checkPasswordForPolicy(
          $this->params['surfer_password_1'], $handle
        );
        if (!$passwordCheck) {
          $this->inputError = $this->data['Error_Invalid_Password'];
        }
      } else {
        $this->inputError = $this->data['Error_Password'];
      }
      if (!$passwordCheck) {
        $this->outputDialog->inputErrors['surfer_password_1'] = 1;
        $this->outputDialog->inputErrors['surfer_password_2'] = 1;
        $result = FALSE;
      }
    }
    // Now go for dynamic data, if present and desired
    if (isset($this->data['dynamic_class']) && is_array($this->data['dynamic_class']) &&
        !empty($this->data['dynamic_class'])) {
      // Field names
      $dynFieldNames = $this->baseSurfers->getDataFieldNames($this->data['dynamic_class']);
      // Field values
      $dynFields = array();
      foreach ($dynFieldNames as $dynFieldName) {
        $dynFields[$dynFieldName] = !empty($this->params['dynamic_'.$dynFieldName]) ?
          $this->params['dynamic_'.$dynFieldName] : '';
      }
      // Check the fields
      $checkDynamic = $this->baseSurfers->checkDynamicData($dynFields);
      foreach ($checkDynamic as $fieldName => $fieldResult) {
        if ($fieldResult == FALSE) {
          $this->outputDialog->inputErrors['dynamic_'.$fieldName] = 1;
          $this->inputError = @$this->data['Error_Input'];
          $result = FALSE;
        }
      }
    }
    return $result;
  }

  /**
  * Delete surfer change request
  *
  * @return boolean $result Status (deleted)
  */
  function deleteChangeRequests() {
    $result = FALSE;
    if (isset ($_REQUEST['del_change']) && $_REQUEST['del_change'] != '') {
      $this->params['surfer_new_email'] = '';
      $this->params['surfer_password_1'] = '';
      include_once(dirname(__FILE__).'/base_surfers.php');
      $surferAdmin = new surfer_admin($this->msgs);
      $deleted = TRUE;
      foreach ($_REQUEST['del_change'] as $del_id => $value) {
        $deleted = $deleted & $surferAdmin->databaseDeleteRecord(
          $this->tableChangeRequests, 'surferchangerequest_id', $del_id
        );
      }
      $result = TRUE;
    }
    return $result;
  }

  /**
  * Send mail to confirm address change
  *
  * @param string $confirmString Token string
  * @return boolean $success Status
  */
  function sendConfirmMail($emailConfirmString) {
    include_once (PAPAYA_INCLUDE_PATH.'system/sys_email.php');
    $login_page_id = $this->data['Login_Page_Id'];
    $from_addr = $this->data['Mail_From_Address'];
    $from_name = $this->data['Mail_From_Name'];
    $subject = $this->data['Mail_Subject'];
    $body = $this->data['Mail_Body'];
    $values['link'] = sprintf(
      '%s?mailchg=%s', $this->getAbsoluteURL($login_page_id), $emailConfirmString
    );
    $email = new email();
    $email->addAddress($this->params['surfer_new_email']);
    $email->setSender($from_addr, $from_name);
    $email->setReturnPath($from_addr);
    $email->setSubject($subject);
    $email->setBody($body, $values, 70);
    $success = $email->send();
    return $success;
  }

  /**
  * Save changed profile data
  *
  * @return boolean $result Status
  */
  function saveProfileData() {
    include_once(dirname(__FILE__).'/base_surfers.php');
    $surferAdmin = new surfer_admin($this->msgs);
    // Save existing data for admin mail
    $surfer = &$this->surferObj->surfer;
    $this->oldData = array(
      'surfer_surname' =>
        !empty($surfer['surfergroup_surname']) ? $surfer['surfergroup_surname'] : '',
      'surfer_givenname' =>
        !empty($surfer['surfergroup_givenname']) ? $surfer['surfergroup_givenname'] : '',
      'surfer_gender' =>
        !empty($surfer['surfergroup_gender']) ? $surfer['surfergroup_gender'] : ''
    );
    if (isset($this->data['dynamic_class']) && is_array($this->data['dynamic_class']) &&
        !empty($this->data['dynamic_class'])) {
      // Get the field names
      $dynFieldNames = $this->baseSurfers->getDataFieldNames($this->data['dynamic_class']);
      // Get data from these fields
      $dynData = $surferAdmin->getDynamicData($this->surferObj->surferId, $dynFieldNames);
      if (!empty($dynData)) {
        $this->oldData = array_merge($this->oldData, $dynData);
      }
    }
    $data = array(
      'surfer_surname' =>
        !empty($this->params['surfer_surname']) ? $this->params['surfer_surname'] : '',
      'surfer_givenname' =>
        !empty($this->params['surfer_givenname']) ? $this->params['surfer_givenname'] : '',
      'surfer_gender' =>
        !empty($this->params['surfer_gender']) ? $this->params['surfer_gender'] : ''
    );
    if ($this->data['change_email'] && isset($this->params['surfer_new_email']) &&
        trim($this->params['surfer_new_email']) != '') {
      srand((double)microtime() * 1000000);
      $rand = uniqid(rand());
      $emailConfirmString = $rand;
      // Keep this XML stuff in mind (and code) for later ...
      // $changedata = sprintf ('<changedata><field name="%s">%s</field></changedata>',
      //  'surfer_new_email', $this->params['surfer_new_email']);
      $t = time();
      $changerequest_data = array(
        'surferchangerequest_surferid' =>
          !empty($this->surferObj->surfer['surfer_id']) ?
            $this->surferObj->surfer['surfer_id'] : '',
        'surferchangerequest_type' => 'email',
        'surferchangerequest_data' => !empty( $this->params['surfer_new_email']) ?
          $this->params['surfer_new_email'] : '',
        'surferchangerequest_token' => md5($emailConfirmString),
        'surferchangerequest_time' => $t,
        'surferchangerequest_expiry' => $t + $this->data['Mail_Expiry'] * 3600
      );
      // Upadate this data in table
      $surferAdmin->databaseInsertRecord(
        $this->tableChangeRequests, 'surferchangerequest_id', $changerequest_data
      );
      // send mail
      $this->sendConfirmMail($emailConfirmString);
    }
    if (isset($this->params['surfer_password_1']) &&
        trim($this->params['surfer_password_1']) != '') {
      $data['surfer_password'] = $this->surferObj->getPasswordHash(
        $this->params['surfer_password_1']
      );
    }
    $surferAdmin->editSurfer['surfer_id'] = $this->surferObj->surferId;
    if ($surferAdmin->saveSurfer($data)) {
      $result = TRUE;
    } else {
      return FALSE;
    }
    // Save necessary parameters for admin mail
    $this->newData = array(
      'surfer_surname' =>
        !empty($this->params['surfer_surname']) ? $this->params['surfer_surname'] : '',
      'surfer_givenname' =>
        !empty($this->params['surfer_givenname']) ? $this->params['surfer_givenname'] : '',
      'surfer_gender' =>
        !empty($this->params['surfer_gender']) ? $this->params['surfer_gender'] : ''
    );
    // Now save the dynamic data, if necessary
    if (isset($this->data['dynamic_class']) && is_array($this->data['dynamic_class']) &&
        !empty($this->data['dynamic_class'])) {
      // Get the field names
      $dynFieldNames = $this->baseSurfers->getDataFieldNames($this->data['dynamic_class']);
      // Get those fields that are actually set
      $dynFields = array();
      foreach ($dynFieldNames as $fieldName) {
        if (isset($this->params['dynamic_'.$fieldName])) {
          $dynFields[$fieldName] = $this->params['dynamic_'.$fieldName];
        }
      }
      if (!empty($dynFields)) {
        $this->newData = array_merge($this->newData, $dynFields);
        $result = $this->baseSurfers->setDynamicData($this->surferObj->surferId, $dynFields);
      }
    }
    return $result;
  }

  /**
  * Send administrative email
  *
  * Will send an email to the site administrator (or any email address configured here)
  * if a surfer changes his or her personal data.
  *
  * @return boolean
  */
  function sendAdminEmail() {
    $success = FALSE;
    // Check whether all the necessary data is set
    if (isset($this->data['sender_email']) && isset($this->data['recipient_email']) &&
        isset($this->data['email_subject']) && isset($this->data['email_intro']) &&
        isset($this->data['email_line'])) {
      // Check whether we've got the data we want to compare
      if (isset($this->oldData) && isset($this->newData)) {
        include_once (PAPAYA_INCLUDE_PATH.'system/sys_email.php');
        include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
        // Check which fields changed
        $changeData = array();
        foreach ($this->newData as $key => $val) {
          if (isset($this->oldData[$key]) && $val != $this->oldData[$key]) {
            $changeData[$key] = array(
              'old' => $this->oldData[$key],
              'new' => $val
            );
          } elseif (!isset($this->oldData[$key])) {
            $changeData[$key] = array(
              'old' => '',
              'new' => $val
            );
          }
        }
        if (!empty($changeData)) {
          $template = new base_simpletemplate();
          $surfer = &$this->surferObj->surfer;
          $replace = array(
            'USER' => sprintf(
              '%s %s (%s)',
              !empty($surfer['surfer_givenname']) ? $surfer['surfer_givenname'] : '',
              !empty($surfer['surfer_surname']) ? $surfer['surfer_surname'] : '',
              !empty($surfer['surfer_handle']) ? $surfer['surfer_handle'] : ''
            ),
            'TIME' => date('Y-m-d, H:i', time())
          );
          $subject = $template->parse($this->data['email_subject'], $replace);
          $intro = $template->parse($this->data['email_intro'], $replace);
          $body = $intro."\n\n";
          foreach ($changeData as $key => $values) {
            $replace = array(
              'FIELD' => $key,
              'OLDVALUE' => $values['old'],
              'NEWVALUE' => $values['new']);
            $template = new base_simpletemplate();
            $line = $template->parse($this->data['email_line'], $replace);
            $body .= $line."\n";
          }
          // Send the mail
          $name = (isset($this->data['recipient_name'])) ?
          $this->data['recipient_name'] : NULL;
          $email = new email();
          if (isset($this->data['sender_name'])) {
            $email->setSender($this->data['sender_email'], $this->data['sender_name']);
          } else {
            $email->setSender($this->data['sender_email']);
          }
          $email->setReturnPath($this->data['sender_email']);
          $email->addAddress($this->data['recipient_email'], $name);
          $email->setSubject($subject);
          $email->setBody($body);
          $success = $email->send();
        }
      }
    }
    return $success;
  }

  /**
  * Get form xml to select dynamic data categories by callback.
  *
  * @param string $name Field name
  * @param array $element Field element configurations
  * @param string $data Current field data
  * @return string $result XML
  */
  function callbackClasses($name, $element, $data) {
    $result = '';
    $lng = $this->parentObj->topic['TRANSLATION']['lng_id'];
    $commonTitle = $this->_gt('Category');
    $sql = "SELECT c.surferdataclass_id,
                   ct.surferdataclasstitle_classid,
                   ct.surferdataclasstitle_name,
                   ct.surferdataclasstitle_lang
              FROM %s AS c LEFT OUTER JOIN %s AS ct
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
          '<input type="checkbox" name="%s[%s][]" value="%d" %s />%s'.LF,
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
