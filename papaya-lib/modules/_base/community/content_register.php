<?php
/**
* Page module - user registration
*
* @copyright 2002-2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: content_register.php 38470 2013-04-30 14:56:54Z kersken $
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
* Media db (for avatar upload)
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');

/**
* Page module - user registration
*
* Registration form for community
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class content_register extends base_content {

  /**
  * Papaya database table surfer
  * @var string $tableSurfer
  */
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  /**
  * Papaya database table link
  * @var string $tableLink
  */
  var $tableLink = PAPAYA_DB_TBL_SURFERPERMLINK;

  /**
  * Papaya database table surferchangerequests
  * @var string $tableChangeRequests
  */
  var $tableChangeRequests = PAPAYA_DB_TBL_SURFERCHANGEREQUESTS;

  /**
  * Base surfers
  * @var object $baseSurfers surfer_admin
  */
  var $baseSurfers = NULL;

  /**
  * Status to set after opt-in verification
  * @var array $availableStatus
  */
  var $availableStatus = array(1 => 1, 2 => 2);

  /**
  * Global boolean to store if an avatar image has been uploaded.
  * @var boolean $avatarUploaded
  */
  var $avatarUploaded = FALSE;

  /**
  * Surfer avatar media Id
  * @var mixed string|NULL $avatar
  */
  var $avatar = NULL;

  /**
  * Id of the new surfer
  * @var mixed string|NULL $newSurferId
  */
  var $newSurferId = NULL;

  /**
  * Fields to edit
  * @var array $editGroups
  */
  var $editGroups = array(
    array(
      'General',
      'categories-content',
      array(
        'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, NULL, ''),
        'text_basic' => array('Start text', 'isSomeText', FALSE, 'richtext', 5, ''),
        'text_confirm' => array(
          'Confirmation text',
          'isSomeText',
          FALSE,
          'richtext',
          5,
          'You can use {%HANDLE%}, {%GIVENNAME%}, {%SURNAME%}, {%EMAIL%}, and {%GENDER%}.'
        ),
        'text_registered' => array(
          'Text after registration',
          'isSomeText',
          FALSE,
          'richtext',
          5,
          'You can use {%HANDLE%}, {%GIVENNAME%}, {%SURNAME%}, {%EMAIL%}, and {%GENDER%}.'
        ),
        'text_verified' => array(
          'Text after verification',
          'isSomeText',
          FALSE,
          'richtext',
          5,
          'You can use {%HANDLE%}, {%GIVENNAME%}, {%SURNAME%}, {%EMAIL%}, and {%GENDER%}.'
        ),
        'text_delay' => array(
          'Text for delayed verification',
          'isSomeText',
          FALSE,
          'richtext',
          5,
          'You can use {%TIMEINFO%} for n <hour[s]> <and> n <minute[s]>',
          'Please try again in at least {%TIMEINFO%}.'
        ),
        'Settings',
        'use_summary' => array('Input summary', 'isNum', TRUE, 'yesno', '', '', 0),
        'login_page' => array(
          'Login page',
          'isNum',
          TRUE,
          'pageid',
          10,
          'Provide this if you want the login page to verify registration emails.',
          0
        ),
        'blacklist_check' => array(
          'Check surfer handles',
          'isNum',
          TRUE,
          'yesno',
          50,
          'Check surfer handles against black list.',
          0
        ),
        'handle_min_length' => array(
          'Minimum length of surfer handles',
          'isNum',
          TRUE,
          'input',
          10,
          '',
          4
        ),
        'email_blacklist_check' => array(
          'Check email address',
          'isNum',
          TRUE,
          'yesno',
          50,
          'Check email address against black list.',
          0
        ),
        'dynamic_class' => array(
          'Dynamic data category', 'isNum', FALSE, 'function', 'callbackClasses'
        ),
        'Permissions',
        'default_group' => array('Default group', 'isNum', TRUE, 'function', 'getSurferGroupCombo'),
        'default_status' => array(
          'Default status',
          'isNum',
          TRUE,
          'function',
          'getSurferStatusCombo',
          'Select confirmed if you want to administratively validate new surfers',
          1
        )
      )
    ),
    array(
      'Captions',
      'items-message',
      array(
      'Fields and captions',
        'caption_username' => array('Username', 'isNoHTML', TRUE, 'input', 100, '', 'Username'),
        'mandatory_username' => array('Username mandatory', 'isNum', TRUE, 'yesno', NULL, '', 1),
        'show_username' => array(
          'Show username',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'Will be overridden if you set the username mandatory.',
          1
        ),
        'caption_givenname' => array('Givenname', 'isNoHTML', TRUE, 'input', 50, '', 'Givenname'),
        'mandatory_givenname' => array('Givenname mandatory', 'isNum', TRUE, 'yesno', '', '', 1),
        'show_givenname' => array(
          'Show givenname',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'Will be overridden if you set the givenname mandatory.',
          1
        ),
        'caption_surname' => array('Surname', 'isNoHTML', TRUE, 'input', 50, '', 'Surname'),
        'mandatory_surname' => array('Surname mandatory', 'isNum', TRUE, 'yesno', '', '', 1),
        'show_surname' => array(
          'Show surname',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'Will be overridden if you set the surname mandatory.',
          1
        ),
        'caption_gender' => array('Gender', 'isNoHTML', TRUE, 'input', 50, '', 'Gender'),
        'mandatory_gender' => array('Gender mandatory', 'isNum', TRUE, 'yesno', '', '', 1),
        'caption_female' => array('female', 'isNoHTML', TRUE, 'input', 50, '', 'female'),
        'caption_male' => array('male', 'isNoHTML', TRUE, 'input', 50, '', 'male'),
        'show_gender' => array(
          'Show gender',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'Will be overridden if you set the gender mandatory.',
          1
        ),
        'caption_password' => array('Password', 'isNoHTML', TRUE, 'input', 50, '', 'Password'),
        'caption_confirm_password' => array(
          'Confirm Password',
          'isNoHTML',
          TRUE,
          'input',
          50,
          '',
          'Confirm Password'
        ),
        'caption_email' => array('Email', 'isNoHTML', TRUE, 'input', 100, '', 'Email'),
        'caption_confirm_email' => array(
          'Confirm Email',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'Confirm Email'
        ),
        'email_confirmation' => array(
          'Confirm email?',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'Select no if email only needs to be entered once',
          1
        ),
        'caption_confirm' => array('Confirm button', 'isNoHTML', TRUE, 'input', 100, '', 'Confirm'),
        'caption_edit' => array('Edit button', 'isNoHTML', TRUE, 'input', 100, '', 'Back to form'),
        'caption_submit' => array('Submit button', 'isNoHTML', TRUE, 'input', 100, '', 'Sign up'),
        'agree_to_terms' => array('Show terms', 'isNum', TRUE, 'yesno', '', '', 0),
        'headline_terms' => array('Terms (headline)', 'isNoHTML', TRUE, 'input', 255, '', 'Terms'),
        'caption_terms' => array(
          'Terms (text and link)',
          'isNoHTML',
          TRUE,
          'input',
          200,
          'Use {%TERMS%} for the link',
          'I agree to the {%TERMS%}.'
        ),
        'page_terms' => array(
          'Terms page',
          'isNum',
          TRUE,
          'pageid',
          10,
          '',
          0
        ),
        'linktext_terms' => array(
          'Terms link text',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'terms'
        ),
        'terms_blank' => array(
          'Terms page in new window',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'Select yes if you want to open the terms link in a new window',
          0
        ),
        'terms_checkbox_verbose' => array(
          'Verbose info for terms checkbox',
          'isSomeText',
          FALSE,
          'richtext',
          5
        ),
        'terms_info_verbose' => array(
          'Verbose extra info for terms',
          'isSomeText',
          FALSE,
          'richtext',
          5
        ),
        'Time information',
        'caption_hour' => array(
          'Hour',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'hour'
        ),
        'caption_hours' => array(
          'Hours',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'hours'
        ),
        'caption_and' => array(
          'And',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'and'
        ),
        'caption_minute' => array(
          'Minute',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'minute'
        ),
        'caption_minutes' => array(
          'Minutes',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'minutes'
        )
      )
    ),
    array(
      'Avatar settings',
      'items-user',
      array(
        'Avatar settings',
        'upload_avatar' => array(
          'Upload avatar images',
          'isNum',
          TRUE,
          'yesno',
          '',
          'When set, the surfer is allowed to upload an avatar image into the media db.',
          0
        ),
        'avatar_directory' => array('Image folder', 'isNum', TRUE, 'mediafolder'),
        'avatar_width' => array('Image width', 'isNum', TRUE, 'input', 4, '', 160),
        'avatar_height' => array('Image height', 'isNum', TRUE, 'input', 4, '', 240),
        'caption_avatar' => array('Avatar caption', 'isNoHTML', TRUE, 'input', 200, '', 'Avatar'),
      ),
    ),
    array(
      'Messages',
      'items-dialog',
      array(
        'Messages',
        'Success' => array(
          'Registration succeeded',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Registration succeeded'
        ),
        'verified' => array(
          'Verification succeeded',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Verification succeeded'
        ),
        'not_verified' => array(
          'Verification failed',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Verification failed'
        ),
        'error_validation_time' => array(
          'Validation delayed',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'You cannot be validated yet.'
        ),
        'Error messages',
        'error_user_exists' => array(
          'User exists',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'User already exists'
        ),
        'error_handle_blacklisted' => array(
          'Username invalid',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Username invalid'
        ),
        'error_handle_too_short' => array(
          'Username too short',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Username too short'
        ),
        'error_handle_empty' => array(
          'Username empty',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Please enter your username'
        ),
        'error_handle_incorrect' => array(
          'Username incorrect',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Username incorrect'
        ),
        'error_email_exists' => array(
          'Email exists',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Email already exists'
        ),
        'error_email_illegal' => array(
          'Email is illegal',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Illegal email address'
        ),
        'error_email_empty' => array(
          'Email empty',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Please enter your email address'
        ),
        'error_email_incorrect' => array(
          'Email incorrect',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Email incorrect'
        ),
        'error_email_entry' => array(
          'Email entry',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Email entry error'
        ),
        'error_password_entry' => array(
          'Password entry',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Password entry error'
        ),
        'error_password_too_short' => array(
          'Password too short',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Password too short'
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
          'Password blacklisted',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Illegal password'
        ),
        'error_password_empty' => array(
          'Password empty',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Please choose a password'
        ),
        'error_password_incorrect' => array(
          'Password incorrect',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Password incorrect'
        ),
        'error_givenname_empty' => array(
          'Givenname empty',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Please enter your givenname'
        ),
        'error_givenname_incorrect' => array(
          'Givenname incorrect',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Givenname incorrect'
        ),
        'error_surname_empty' => array(
          'Surname empty',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Please enter your surname'
        ),
        'error_surname_incorrect' => array(
          'Surname incorrect',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Surname incorrect'
        ),
        'error_gender_empty' => array(
          'Gender empty',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Please select your gender'
        ),
        'error_gender_incorrect' => array(
          'Gender incorrect',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Gender incorrect'
        ),
        'error_datafields_error' => array(
          'Datafields error',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Datafields error'
        ),
        'error_terms' => array(
          'Terms error',
          'isNoHTML',
          TRUE,
          'input',
          200,
          'User did not agree to the terms.',
          'You must agree to the terms'
        ),
        'error_mail_send' => array(
          'Mail send',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Mail send error'
        ),
        'error_database_input' => array(
          'Database input',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Database input error'
        ),
        'error_registered' => array(
          'Already registered',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Already registered'
        )
      )
    ),
    array(
      'E-Mail',
      'items-mail',
      array(
        'mailfrom_name' => array(
          'Mailfrom name', 'isNoHTML', TRUE, 'input', 200, ''
        ),
        'mailfrom_email' => array (
          'Mailfrom email', 'isEmail', TRUE, 'input', 200, ''
        ),
        'Confirmation Email',
        'subject' => array('Subject', 'isNoHTML', TRUE, 'input', 200, '', 'Your registration'),
        'message' => array(
          'Message',
          'isNoHTML',
          TRUE,
          'textarea',
          10,
          'Placeholders: NAME, TITLE, EMAIL, PROJECT, LINK',
          "Name: {%NAME%}\nTitle: {%TITLE%}\nEmail: {%EMAIL%}\nProject: {%PROJECT%}\n
           Link: {%LINK%}\n"
        ),
        'Delayed Confirmation Email',
        'delay_subject' => array(
          'Subject',
          'isNoHTML',
          FALSE,
          'input',
          200,
          'Subject for emails with freemail delay',
          'Your registration'
        ),
        'delay_message' => array(
          'Message',
          'isNoHTML',
          FALSE,
          'textarea',
          10,
          "Message for emails with freemail delay, placeholders: NAME, TITLE, EMAIL, PROJECT, LINK,
           TIMEINFO is <hour[s]> <and> <minute[s]>.",
          "Name: {%NAME%}\nTitle: {%TITLE%}\nEmail: {%EMAIL%}\nProject: {%PROJECT%}\n
           Link: {%LINK%}\nEarliest verification time: {%TIMEINFO%}"
        ),
        'Notification Email',
        'send_notification_email' => array(
          'Active',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'Send notification email with user data if a confirmation email has been sent.',
          1
        ),
        'notification_recipient' => array(
          'Recipient email', 'isEmail', TRUE, 'input', 200, '', ''
        ),
        'notification_subject' => array(
          'Subject', 'isNoHTML', TRUE, 'input', 200, '', 'New registration'
        ),
        'notification_message' => array(
          'Message',
          'isNoHTML',
          TRUE,
          'textarea',
          10,
          'Placeholders: NAME, TITLE, EMAIL, PROJECT',
          "Name: {%NAME%}\nTitle: {%TITLE%}\nEmail: {%EMAIL%}\nProject: {%PROJECT%}\n"
        )
      )
    )
  );

  /**
  * Content registration constructor
  *
  * @param object &$owner owner object
  * @param string $paramName optional, default 'srg'
  * @access public
  */
  function __construct(&$owner, $paramName = 'srg') {
    parent::__construct($owner, $paramName);
  }

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
  * Get surfer group combo
  *
  * Callback function; creates a select field to choose the group
  * the newly registered surfers will join
  *
  * @param string $name
  * @param string $field
  * @param array $data
  * @return string $result
  *
  * @access public
  */
  function getSurferGroupCombo($name, $field, $data) {
    $this->_initBaseSurfers();
    $this->baseSurfers->loadGroups();
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      $this->paramName,
      papaya_strings::escapeHtmlChars($name)
    );
    if (isset($this->baseSurfers->groupList) && is_array($this->baseSurfers->groupList)) {
      foreach ($this->baseSurfers->groupList as $groupId => $group) {
        $selected = ($groupId == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%d"%s>%s</option>',
          $groupId,
          $selected,
          papaya_strings::escapeHTMLChars($group['surfergroup_title'])
        );
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Generates a drop down menu to choose the status
  * to grant to the surfers after email verification
  * (either 1 [valid] if you want to activate the surfers automatically
  *  or 2 [verified] to finally activate them administratively)
  *
  * @param string $name Name of the generated menu
  * @param string $field
  * @param integer $data Identifier of the currently selected option
  * @return string XML repesenting a drop down menu
  *
  * @access public
  */
  function getSurferStatusCombo($name, $field, $data) {
    $this->_initBaseSurfers();
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      $this->paramName,
      papaya_strings::escapeHtmlChars($name)
    );
    if (isset($this->baseSurfers->status) && is_array($this->baseSurfers->status)) {
      foreach ($this->baseSurfers->status as $statusId => $status) {
        if (isset($this->availableStatus[$statusId])) {
          $selected = ($statusId == $data) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%d"%s>%s</option>',
            $statusId,
            $selected,
            htmlspecialchars($status)
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
        htmlspecialchars($this->data['title']) :
        ''
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
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $result = sprintf(
      '<title>%s</title>'.LF,
      !empty($this->data['title']) ? $this->getXHTMLString($this->data['title']) : ''
    );
    $this->initializeParams();
    $this->backLink = '';
    // If a valid surfer is logged in, give him/her a login link only
    $surferObj = &base_surfer::getInstance();
    if ($surferObj->isValid) {
      $result .= sprintf(
        '<registered>%s</registered>',
        papaya_strings::escapeHtmlChars($this->data['error_registered'])
      );
    } else {
      if (isset($this->parentObj->topicId)) {
        $this->baseLink = $this->getBaseLink($this->parentObj->topicId);
      }
      if ((isset($this->params['save']) && $this->params['save'] > 0) ||
          isset($this->params['final'])) {
        $this->initializeOutputForm();
        $result .= $this->saveOutput();
      } else {
        $result .= $this->getOutput();
      }
    }
    return $result;
  }

  /**
  * Generate an input field to upload a file
  *
  * @param string $name
  * @param array $field
  * @param array $data
  * @return string
  */
  function getAvatarUploadXml($name, $field, $data) {
    return sprintf(
      '<input value="%s" type="file" name="%s[%s]" maxlength="100" '.
      'size="50" fid="%s" mandatory="%s" />'.LF.
      '<papaya:media src="%s" width="%s" height="%s" />'.LF,
      papaya_strings::escapeHTMLChars($data),
      $this->paramName,
      papaya_strings::escapeHTMLChars($name),
      papaya_strings::escapeHTMLChars($name),
      ($field[2] == TRUE) ? 'true' : 'false',
      papaya_strings::escapeHTMLChars($data),
      $this->data['avatar_width'],
      $this->data['avatar_height']
    );
  }

  /**
  * Get output
  *
  * @access public
  * @return string
  */
  function getOutput() {
    $result = '<registerpage>';
    $result .= $this->getVerboseTermsInfo();
    $validationId = $this->_getRawParam($this->paramName.'_id', '');
    if ($validationId != '') {
      $checkValidationTime = $this->checkValidationTime($validationId);
      if ($checkValidationTime == 0) {
        $surferId = $this->makeValid($validationId);
        if (FALSE !== $surferId) {
          $result .= $this->getSuccessMessageXml($this->data['verified']);
          if (isset($this->data['text_verified'])) {
            include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
            $this->_initBaseSurfers();
            $data = $this->baseSurfers->getBasicDataById($surferId);
            $values = array();
            $this->addVerifiedReplaceValue($data, $values, 'HANDLE', 'surfer_handle');
            $this->addVerifiedReplaceValue($data, $values, 'GIVENNAME', 'surfer_givenname');
            $this->addVerifiedReplaceValue($data, $values, 'SURNAME', 'surfer_surname');
            $this->addVerifiedReplaceValue($data, $values, 'EMAIL', 'surfer_email');
            $this->addVerifiedReplaceValue(
              $data,
              $values,
              'GENDER',
              'surfer_gender',
              array('f' => $this->data['caption_female'], 'm' => $this->data['caption_male'])
            );
            $template = new base_simpletemplate();
            $textVerified = $template->parse($this->data['text_verified'], $values);
            $result .= sprintf(
              '<text>%s</text>',
              $this->getXHTMLString($textVerified)
            );
          }
        } else {
          $result .= $this->getErrorMessageXml($this->data['not_verified']);
        }
      } else {
        $result .= $this->getErrorMessageXml($this->data['error_validation_time']);
        $totalMinutes = ceil($checkValidationTime / 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $timeInfo = '';
        if ($hours > 0) {
          $timeInfo .= sprintf(
            '%d %s ',
            $hours,
            $hours == 1 ? $this->data['caption_hour'] : $this->data['caption_hours']
          );
        }
        if ($minutes > 0) {
          if ($hours > 0) {
            $timeInfo .= sprintf('%s ', $this->data['caption_and']);
          }
          $timeInfo .= sprintf(
            '%d %s',
            $minutes,
            $minutes == 1 ? $this->data['caption_minute'] : $this->data['caption_minutes']
          );
        }
        $values = array('TIMEINFO' => $timeInfo);
        include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
        $template = new base_simpletemplate();
        $textDelay = $template->parse($this->data['text_delay'], $values);
        $result .= sprintf(
          '<text>%s</text>', $this->getXHTMLString($textDelay)
        );
      }
    } else {
      if (isset($this->data['text_basic'])) {
        $result .= sprintf(
          '<text>%s</text>', $this->getXHTMLString($this->data['text_basic'])
        );
      }
      $result .= $this->getOutputForm();
    }
    $result .= '</registerpage>';
    return $result;
  }

  /**
  * Get verbose information about terms
  *
  * @return string XML
  */
  public function getVerboseTermsInfo() {
    $result = '<terms-verbose>'.LF;
    if (isset($this->data['terms_checkbox_verbose'])) {
      $result .= sprintf(
        '<checkbox>%s</checkbox>'.LF,
        $this->getXHTMLString($this->data['terms_checkbox_verbose'])
      );
    }
    if (isset($this->data['terms_info_verbose'])) {
      $result .= sprintf(
        '<info>%s</info>'.LF,
        $this->getXHTMLString($this->data['terms_info_verbose'])
      );
    }
    $result .= '</terms-verbose>'.LF;
    return $result;
  }

  /**
  * Make valid
  *
  * @param string $confirmString
  * @access public
  */
  function makeValid($confirmString) {
    $this->_initBaseSurfers();
    return $this->baseSurfers->makeValid($confirmString, $this->data['default_status']);
  }

  /**
  * Check validation time
  *
  * @param string $confirmString
  * @return integer 0 on success, remaining time to wait (in seconds) until verification is possible
  */
  function checkValidationTime($confirmString) {
    $this->_initBaseSurfers();
    return $this->baseSurfers->checkValidationTime($confirmString);
  }

  /**
  * Determines the handle of the current surfer
  *
  */
  function determineSurferHandle() {
    if (!$this->data['mandatory_username'] &&
        !isset($this->outputDialog->params['surfer_handle'])) {
      $this->_initBaseSurfers();
      $email = $this->outputDialog->params['surfer_email_1'];
      $this->surferHandle = $this->baseSurfers->generateHandle($email);
    } else {
      $this->surferHandle = $this->outputDialog->params['surfer_handle'];
    }
  }

  /**
  * Save output
  *
  * @access public
  * @return string
  */
  function saveOutput() {
    $result = '<registerpage>';
    $result .= $this->getVerboseTermsInfo();
    $termsAgreed = FALSE;
    if ($this->data['agree_to_terms'] == 0 ||
        (
          isset($this->outputDialog->params['terms']) &&
          $this->outputDialog->params['terms'] == 1
        )
       ) {
      $termsAgreed = TRUE;
    }
    if ($this->outputDialog->checkDialogInput() && $termsAgreed) {
      $this->determineSurferHandle();
      if ($this->data['email_confirmation'] == 0 ||
          0 == strcmp(
          $this->outputDialog->params['surfer_email_1'],
          $this->outputDialog->params['surfer_email_2'])) {
        if (0 == strcmp(
            $this->outputDialog->params['surfer_password_1'],
            $this->outputDialog->params['surfer_password_2'])) {
          $checkHandle = $this->checkHandle();
          if ($checkHandle === 0) {
            $checkPassword = $this->checkPassword();
            if ($checkPassword === 0) {
              $checkEmail = $this->checkEmail();
              if ($checkEmail === 0) {
                if (isset($this->data['use_summary']) && $this->data['use_summary'] == 1 &&
                    !isset($this->outputDialog->params['final'])) {
                  $result .= $this->getSummaryXML();
                } else {
                  $addUserCheck = $this->addUser();
                  if ($addUserCheck) {
                    // Now save the dynamic data, if necessary
                    if (!empty($this->data['dynamic_class']) &&
                        is_array($this->data['dynamic_class'])) {
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
                        $this->baseSurfers->setDynamicData(
                          $this->newSurferId, $dynFields
                        );
                      }
                    }
                    if ($this->data['upload_avatar'] == 1) {
                      $this->mediaDB = new base_mediadb_edit;
                      if (@isset($_FILES[$this->paramName]['tmp_name']['surfer_avatar'])) {

                        $uploadData = $_FILES[$this->paramName];
                        switch ($uploadData['error']) {  // check if error encountered
                        case 1: // exceeded max file size
                        case 2: // exceeded max post size
                          $result .= $this->getErrorMessageXml(
                            $this->data['error_file_too_large'], 'surfer_avatar'
                          );
                          $this->profileForm->inputErrors['surfer_avatar'] = 1;
                          break;
                        case 3:
                          $result .= $this->getErrorMessageXml(
                            $this->data['error_file_incomplete'], 'surfer_avatar'
                          );
                          $this->profileForm->inputErrors['surfer_avatar'] = 1;
                          break;
                        case 6:
                          $result .= $this->getErrorMessageXml(
                            $this->data['error_no_temporary_path'], 'surfer_avatar'
                          );
                          $this->profileForm->inputErrors['surfer_avatar'] = 1;
                          break;
                        case 4:
                          $result .= $this->getErrorMessageXml(
                            $this->data['error_nofile'], 'surfer_avatar'
                          );
                          $this->profileForm->inputErrors['surfer_avatar'] = 1;
                          break;
                        case 0:
                        default:
                          $tempFileName = (string)$uploadData['tmp_name']['surfer_avatar'];
                          break;
                        }

                        if (isset($tempFileName) &&
                            @file_exists($tempFileName) &&
                            is_uploaded_file($tempFileName)) {
                          $tempFileSize = @filesize($tempFileName);
                          list(,,$tempFileType) = @getimagesize($tempFileName);

                          if ($tempFileSize <= 0) {
                            $result .= $this->getErrorMessageXml(
                              $this->data['error_nofile'], 'surfer_avatar'
                            );
                          } elseif ($tempFileSize >= $this->mediaDB->getMaxUploadSize()) {
                            $result .= $this->getErrorMessageXml(
                              $this->data['error_file_too_large'], 'surfer_avatar'
                            );
                          } elseif ($tempFileType == NULL ||
                                    $tempFileType < 1 ||
                                    $tempFileType > 3) {
                            $result .= $this->getErrorMessageXml(
                              $this->data['error_file_type'], 'surfer_avatar'
                            );
                          } else {
                            $fileId = $this->mediaDB->addFile(
                              $uploadData['tmp_name']['surfer_avatar'],
                              $uploadData['name']['surfer_avatar'],
                              $this->data['avatar_directory'],
                              $this->newSurferId,
                              $uploadData['type']['surfer_avatar'],
                              'uploaded_file'
                            );

                            if (!$fileId) {
                              $result .= $this->getErrorMessageXml(
                                $this->data['error_database'], 'surfer_avatar'
                              );
                            } else {
                              $data = array(
                                'file_id' => $fileId,
                                'file_title' => 'Surfer Avatar -- '.date('Y-m-d H:i:s', time()),
                                'file_description' => '',
                                'lng_id' => (int)$this->parentObj->currentLanguage['lng_id'],
                              );

                              if ($this->mediaDB->databaseInsertRecord(
                                $this->mediaDB->tableFilesTrans,
                                NULL,
                                $data
                              ) == FALSE) {
                                $result .= $this->getErrorMessageXml(
                                  $this->data['error_database'], 'surfer_avatar'
                                );
                              } else {
                                $mediaId = $fileId;
                              }
                            }
                          }
                        }

                        if (isset($mediaId) && !empty($mediaId) &&
                            checkit::isGUID($mediaId, TRUE)) {
                          $this->baseSurfers->databaseUpdateRecord(
                            $this->tableSurfer,
                            array('surfer_avatar' => $mediaId),
                            'surfer_id',
                            $this->newSurferId
                          );
                        }
                      }
                    }
                    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
                    $this->surferObj = &base_surfer::getInstance();
                    $result .= $this->getSuccessMessageXml($this->data['Success']);
                    if (isset($this->data['text_registered'])) {
                      include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
                      $values = array();
                      $this->addReplaceValue($values, 'HANDLE', 'surfer_handle');
                      $this->addReplaceValue($values, 'GIVENNAME', 'surfer_givenname');
                      $this->addReplaceValue($values, 'SURNAME', 'surfer_surname');
                      $this->addReplaceValue($values, 'EMAIL', 'surfer_email_1');
                      $this->addReplaceValue(
                        $values,
                        'GENDER',
                        'surfer_gender',
                        array(
                          'f' => $this->data['caption_female'],
                          'm' => $this->data['caption_male']
                        )
                      );
                      $template = new base_simpletemplate();
                      $textRegistered = $template->parse(
                        $this->data['text_registered'],
                        $values
                      );
                      $result .= sprintf(
                        '<text>%s</text>',
                        $this->getXHTMLString($textRegistered)
                      );
                    }
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
                }
              } else {
                if ($checkEmail == -1) {
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_email_exists'],
                    'surfer_email_1'
                  );
                } else {
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_email_illegal'],
                    'surfer_email_1'
                  );
                }
                if (isset($this->data['text_basic'])) {
                  $result .= sprintf(
                    '<text>%s</text>',
                    $this->getXHTMLString($this->data['text_basic'])
                  );
                }
                $result .= $this->getOutputForm();
              }
            } else {
              if ($checkPassword + 4 <= 0) {
                $result .= $this->getErrorMessageXml(
                  $this->data['error_password_blacklisted'],
                  'surfer_password_1'
                );
                $checkPassword += 4;
              }
              if ($checkPassword + 2 <= 0) {
                $result .= $this->getErrorMessageXml(
                  $this->data['error_password_equals_handle'],
                  'surfer_password_1'
                );
                $checkPassword += 2;
              }
              if ($checkPassword + 1 <= 0) {
                $result .= $this->getErrorMessageXml(
                  $this->data['error_password_too_short'],
                  'surfer_password_1'
                );
              }
              if (isset($this->data['text_basic'])) {
                $result .= sprintf(
                  '<text>%s</text>',
                  $this->getXHTMLString($this->data['text_basic'])
                );
              }
              $result .= $this->getOutputForm();
            }
          } else {
            switch($checkHandle) {
            case -1:
              $errorMsg = $this->data['error_user_exists'];
              break;
            case -2:
              $errorMsg = $this->data['error_handle_blacklisted'];
              break;
            case -4:
              $errorMsg = $this->data['error_handle_too_short'];
              break;
            default:
              $errorMsg = $this->data['error_datafields_error'];
            }
            if ($errorMsg != '') {
              $result .= $this->getErrorMessageXml($errorMsg, 'surfer_handle');
            }
            if (isset($this->data['text_basic'])) {
              $result .= sprintf(
                '<text>%s</text>',
                $this->getXHTMLString($this->data['text_basic'])
              );
            }
            $result .= $this->getOutputForm();
          }
        } else {
          $result .= $this->getErrorMessageXml(
            $this->data['error_password_entry'],
            'surfer_password_2'
          );
          $result .= $this->getOutputForm();
        }
      } else {
        $result .= $this->getErrorMessageXml(
          $this->data['error_email_entry'],
          'surfer_email_2'
        );
        $result .= $this->getOutputForm();
      }
    } else {
      if (!$termsAgreed) {
        $result .= $this->getErrorMessageXml($this->data['error_terms'], 'terms');
      }
      if (isset($this->outputDialog->inputErrors)) {
        $errors = $this->outputDialog->inputErrors;
        $errorMessages = array();
        if (isset($errors['surfer_handle']) && $errors['surfer_handle'] == 1) {
          if (empty($this->outputDialog->params['surfer_handle'])) {
            $errorMessages[] = array($this->data['error_handle_empty'], 'surfer_handle');
          } else {
            $errorMessages[] = array($this->data['error_handle_incorrect'], 'surfer_handle');
          }
        }
        if (isset($errors['surfer_password_1']) && $errors['surfer_password_1'] == 1) {
          if (empty($this->outputDialog->params['surfer_password_1'])) {
            $errorMessages[] = array($this->data['error_password_empty'], 'surfer_password_1');
          } else {
            $errorMessages[] = array(
              $this->data['error_password_incorrect'],
              'surfer_password_1'
            );
          }
        }
        if (isset($errors['surfer_password_2']) && $errors['surfer_password_2'] == 1) {
          if (empty($this->outputDialog->params['surfer_password_2'])) {
            $errorMessages[] = array($this->data['error_password_empty'], 'surfer_password_2');
          } else {
            $errorMessages[] = array(
              $this->data['error_password_incorrect'],
              'surfer_password_2'
            );
          }
        }
        if (isset($errors['surfer_givenname']) && $errors['surfer_givenname'] == 1) {
          if (empty($this->outputDialog->params['surfer_givenname'])) {
            $errorMessages[] = array($this->data['error_givenname_empty'], 'surfer_givenname');
          } else {
            $errorMessages[] = array(
              $this->data['error_givenname_incorrect'],
              'surfer_givenname'
            );
          }
        }
        if (isset($errors['surfer_surname']) && $errors['surfer_surname'] == 1) {
          if (empty($this->outputDialog->params['surfer_surname'])) {
            $errorMessages[] = array($this->data['error_surname_empty'], 'surfer_surname');
          } else {
            $errorMessages[] = array(
              $this->data['error_surname_incorrect'],
              'surfer_surname'
            );
          }
        }
        if (isset($errors['surfer_gender']) && $errors['surfer_gender'] == 1) {
          if (empty($this->outputDialog->params['surfer_gender'])) {
            $errorMessages[] = array($this->data['error_gender_empty'], 'surfer_gender');
          } else {
            $errorMessages[] = array(
              $this->data['error_gender_incorrect'],
              'surfer_gender'
            );
          }
        }
        if (isset($errors['surfer_email_1']) && $errors['surfer_email_1'] == 1) {
          if (empty($this->outputDialog->params['surfer_email_1'])) {
            $errorMessages[] = array($this->data['error_email_empty'], 'surfer_email_1');
          } else {
            $errorMessages[] = array(
              $this->data['error_email_incorrect'],
              'surfer_email_1'
            );
          }
        }
        if (isset($errors['surfer_email_2']) && $errors['surfer_email_2'] == 1) {
          if (empty($this->outputDialog->params['surfer_email_2'])) {
            $errorMessages[] = array($this->data['error_email_empty'], 'surfer_email_2');
          } else {
            $errorMessages[] = array(
              $this->data['error_email_incorrect'],
              'surfer_email_2'
            );
          }
        }
        if (empty($errorMessages)) {
          $result .= $this->getErrorMessageXml($this->data['error_datafields_error']);
        } else {
          foreach ($errorMessages as $errorMessage) {
            $result .= $this->getErrorMessageXml($errorMessage[0], $errorMessage[1]);
          }
        }

        if (isset($this->data['text_basic'])) {
          $result .= sprintf(
            '<text>%s</text>',
            $this->getXHTMLString($this->data['text_basic'])
          );
        }
      }
      $result .= $this->getOutputForm();
    }
    $result .= '</registerpage>';
    return $result;
  }

  /**
  * Get XML for error messages
  *
  * @param string $message
  * @param string $field optional, default ''
  * @return string XML
  */
  function getErrorMessageXml($message, $field = '') {
    $for = '';
    if ($field != '') {
      $for = sprintf(' for="%s"', papaya_strings::escapeHTMLChars($field));
    }
    return sprintf(
      '<message type="error"%s>%s</message>'.LF,
      $for,
      papaya_strings::escapeHTMLChars($message)
    );
  }

  /**
  * Get XML for success messages
  *
  * @param string $message
  * @return string XML
  */
  function getSuccessMessageXml($message) {
    return sprintf(
      '<message type="success">%s</message>'.LF,
      papaya_strings::escapeHTMLChars($message)
    );
  }

  /**
  * Add User
  *
  * @access public
  * @return mixed TRUE or integer
  */
  function addUser() {
    $this->_initBaseSurfers();
    // Create token to confirm registration by mail
    srand((double)microtime() * 1000000);
    $rand = uniqid(rand());
    $confirmString = md5($rand);
    // Create new surfer id
    $newId = $this->baseSurfers->createSurferId();
    // Data for surfer table

    $givenname = ($this->data['mandatory_givenname'])
      ? $this->outputDialog->params['surfer_givenname']
      : '';

    $surname = ($this->data['mandatory_surname'])
      ? $this->outputDialog->params['surfer_surname']
      : '';

    $gender = ($this->data['mandatory_gender'])
      ? $this->outputDialog->params['surfer_gender']
      : '';

    $surferData = array(
      'surfer_id' => $newId,
      'surfer_handle' => $this->surferHandle,
      'surfer_password' =>
        $this->baseSurfers->getPasswordHash($this->outputDialog->params['surfer_password_1']),
      'surfer_givenname' => $givenname,
      'surfer_surname' => $surname,
      'surfer_gender' => $gender,
      'surfer_email' => $this->outputDialog->params['surfer_email_1'],
      'surfer_valid' => FALSE,
      'surfergroup_id'=> @(int)$this->data['default_group']
     );
    // Insert surfer record
    $insert = $this->baseSurfers->databaseInsertRecord(
      $this->tableSurfer,
      NULL,
      $surferData
    );
    $now = time();
    // Data for change request table (token)
    $emailDelayTime = $this->baseSurfers->getEmailDelayTime(
      $this->outputDialog->params['surfer_email_1']
    );
    $changeRequestData = array('surferchangerequest_surferid' => $newId,
      'surferchangerequest_type' => 'register',
      'surferchangerequest_data' => $emailDelayTime,
      'surferchangerequest_token' => $confirmString,
      'surferchangerequest_time' => $now,
      'surferchangerequest_expiry' => $now + 86400
    );
    // Insert change request record
    $confirm = $this->baseSurfers->databaseInsertRecord(
      $this->tableChangeRequests,
      'surferchangerequest_id',
      $changeRequestData
    );
    if ($insert && $confirm) {
      // If we've got a default contact, establish it right here
      $defaultContact = $this->baseSurfers->getDefaultContact();
      if ($defaultContact !== NULL) {
        $this->baseSurfers->forceContact($newId, $defaultContact);
      }
      if ($this->sendMail($confirmString, $emailDelayTime)) {
        $this->newSurferId = $newId;
        return $insert;
      } else {
        return -1;
      }
    } else {
      return -2;
    }
  }

  /**
  * Send mail
  *
  * @param string $confirmString
  * @param integer $emailDelayTime
  * @access public
  * @return boolean
  */
  function sendMail($confirmString, $emailDelayTime) {
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
    $repl['NAME'] = $this->surferHandle;
    $repl['EMAIL'] = $this->outputDialog->params['surfer_email_1'];
    $repl['TITLE'] = !empty($this->data['title']) ? $this->data['title'] : '';
    $repl['PROJECT'] = PAPAYA_PROJECT_TITLE;
    $page = NULL;
    $pageTitle = $this->parentObj->topic['TRANSLATION']['topic_title'];
    if ($this->data['login_page'] > 0) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $pagesConnector = base_pluginloader::getPluginInstance(
        '69db080d0bb7ce20b52b04e7192a60bf', $this
      );
      $loginPageTitle = current(
        $pagesConnector->getTitles(
          $this->data['login_page'], $this->parentObj->topic['TRANSLATION']['lng_id']
        )
      );
      if (!empty($loginPageTitle)) {
        $page = $this->data['login_page'];
        $pageTitle = $loginPageTitle;
      }
    }
    $repl['LINK'] = $this->getAbsoluteURL(
      $this->getWebLink(
        $page,
        NULL,
        NULL,
        array($this->paramName.'_id' => $confirmString),
        NULL,
        $pageTitle
      )
    );
    if ($emailDelayTime == 0) {
      $subject = $this->data['subject'];
      $message = $this->data['message'];
    } else {
      $totalMinutes = floor($emailDelayTime / 60);
      $hours = floor($totalMinutes / 60);
      $minutes = $totalMinutes % 60;
      $timeInfo = '';
      if ($hours > 0) {
        $timeInfo .= sprintf(
          '%d %s ',
          $hours,
          $hours == 1 ? $this->data['caption_hour'] : $this->data['caption_hours']
        );
      }
      if ($minutes > 0) {
        if ($hours > 0) {
          $timeInfo .= sprintf('%s ', $this->data['caption_and']);
        }
        $timeInfo .= sprintf(
          '%d %s',
          $minutes,
          $minutes == 1 ? $this->data['caption_minute'] : $this->data['caption_minutes']
        );
      }
      $repl['TIMEINFO'] = $timeInfo;
      $subject = $this->data['delay_subject'];
      $message = $this->data['delay_message'];
    }
    $email = new email();
    $email->addAddress(
      $this->outputDialog->data['surfer_email_1'],
      $this->outputDialog->data['surfer_handle']
    );
    $email->setSender($this->data['mailfrom_email'], $this->data['mailfrom_name']);
    $email->setSubject($subject, $repl);
    $email->setBody($message, $repl);
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
    } elseif ($this->data['send_notification_email'] == 1 && $this->data['mailfrom_email'] != '' &&
              $this->data['mailfrom_name'] != '' && $this->data['notification_recipient'] != '') {
      // If the opt-in email is sent to the user and we have a notification recipient mail,
      // send notification to that email address
      $email = new email();
      $email->addAddress($this->data['notification_recipient']);
      $email->setSender($this->data['mailfrom_email'], $this->data['mailfrom_name']);
      $email->setSubject($this->data['notification_subject'], $repl);
      $email->setBody($this->data['notification_message'], $repl);
      $email->send();
    }
    return TRUE;
  }

  /**
  * Check handle exists
  *
  * The return values are as follows:
  *
  * 0 => correct
  * -1 => Handle already exists
  * -2 => Handle is blacklisted
  * -4 => Handle too short
  *
  * @access public
  * @return integer 0 if password is correct, a negative value otherwise
  */
  function checkHandle() {
    $this->_initBaseSurfers();
    if ($this->baseSurfers->existHandle($this->surferHandle, TRUE)) {
      return -1;
    }
    // Black list check, only if username is mandatory
    if ($this->data['mandatory_username'] && $this->data['blacklist_check']) {
      if (!$this->baseSurfers->checkHandle($this->surferHandle)) {
        return -2;
      }
    }
    // Check length
    if (strlen($this->surferHandle) < $this->data['handle_min_length']) {
      return -4;
    }
    return 0;
  }

  /**
  * Check email
  *
  * @access public
  * @return integer 0 if email is correct, a negative value otherwise
  */
  function checkEmail() {
    $this->_initBaseSurfers();
    if ($this->baseSurfers->existEmail($this->outputDialog->params['surfer_email_1'], TRUE)) {
      return -1;
    }
    if ($this->data['email_blacklist_check']) {
      if ($this->baseSurfers->checkEmailAgainstBlacklist(
          $this->outputDialog->params['surfer_email_1'])) {
        return 0;
      } else {
        return -2;
      }
    }
    return 0;
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
    $handle = '';
    if ($this->data['show_username']) {
      $handle = $this->outputDialog->params['surfer_handle'];
    }
    return $this->baseSurfers->checkPasswordForPolicy($password, $handle);
  }

  /**
  * Get output form
  *
  * @access public
  * @return string
  */
  function getOutputForm() {
    $result = '';
    $this->initializeOutputForm();
    if (isset($this->outputDialog) && is_object($this->outputDialog)) {
      $result = $this->outputDialog->getDialogXML();
    }
    if ($this->data['agree_to_terms'] == 1) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
      $termsTargetBlank = '';
      if ($this->data['terms_blank']) {
        $termsTargetBlank = ' target="_blank"';
      }
      $termsLink = sprintf(
        '<a href="%s"%s>%s</a>',
        $this->getWebLink($this->data['page_terms']),
        $termsTargetBlank,
        $this->data['linktext_terms']
      );
      $values = array('TERMS' => $termsLink);
      $template = new base_simpletemplate();
      $captionTerms = $template->parse($this->data['caption_terms'], $values);
      $result .= sprintf('<terms>%s</terms>', $this->getXHTMLString($captionTerms));
    }
    return $result;
  }

  /**
  * Initialize output form
  *
  * @access public
  */
  function initializeOutputForm($loadParams = TRUE) {
    if (!(isset($this->outputDialog) && is_object($this->outputDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array('save' => 1);

      // Extract data given by an invitation and store it as predefined form value
      $this->initializeSessionParam(@$this->data['invitation_session_identifier']);
      if ($invitationEmail = $this->getSessionValue('email')) {
        $data = array('surfer_email_1' => $invitationEmail);
      } else {
        $data = array();
      }
      // Load param data destroyed by initializeSessionParam
      $this->initializeParams($this->paramName);
      $fields = array(
        'surfer_handle' => array(
          $this->data['caption_username'],
          '/^[a-z\d._-]{4,'.PAPAYA_COMMUNITY_HANDLE_MAX_LENGTH.'}$/i',
          $this->data['mandatory_username'],
          'input',
          50
        ),
        'surfer_password_1' => array(
          $this->data['caption_password'],
          'isPassword',
          TRUE,
          'password',
          50
        ),
        'surfer_password_2' => array(
          $this->data['caption_confirm_password'],
          'isSomeText',
          FALSE,
          'password',
          50
        ),
        'surfer_givenname' => array(
          $this->data['caption_givenname'],
          'isNoHTML',
          $this->data['mandatory_givenname'],
          'input',
          50
        ),
        'surfer_surname' => array(
          $this->data['caption_surname'],
          'isNoHTML',
          $this->data['mandatory_surname'],
          'input',
          50
        ),
        'surfer_gender' => array(
          $this->data['caption_gender'],
          '/^[fm]$/',
          $this->data['mandatory_gender'],
          'combo',
          array(
            'f' => $this->data['caption_female'],
            'm' => $this->data['caption_male']
          )
        ),
        'surfer_email_1' => array(
          $this->data['caption_email'],
          'isEmail',
          TRUE,
          'input',
          100
        ),
        'surfer_email_2' => array(
          $this->data['caption_confirm_email'],
          'isEmail',
          TRUE,
          'input',
          100
        )
      );
      if ($this->data['email_confirmation'] == 0) {
        unset($fields['surfer_email_2']);
      }
      if ($this->data['mandatory_username'] == 0 && $this->data['show_username'] == 0) {
        unset($fields['surfer_handle']);
      }
      if ($this->data['mandatory_givenname'] == 0 && $this->data['show_givenname'] == 0) {
        unset($fields['surfer_givenname']);
      }
      if ($this->data['mandatory_surname'] == 0 && $this->data['show_surname'] == 0) {
        unset($fields['surfer_surname']);
      }
      if ($this->data['mandatory_gender'] == 0 && $this->data['show_gender'] == 0) {
        unset($fields['surfer_gender']);
      }
      if ($this->data['upload_avatar'] == 1) {
        /**
         * Can be a general purpose frontend form input field later.
         * This has been already prepared but is not in use yet.
         * Until this feature has been adopted, a callback method
         * is used to render its content.
         */
        //  $fields['surfer_avatar'] = array($this->data['caption_avatar'],
        // 'isFile', FALSE, 'imagefile', 100, '','', $this->data['avatar_width'],
        //  $this->data['avatar_height']);
        $fields['surfer_avatar'] = array(
          $this->data['caption_avatar'],
          'isFile',
          FALSE,
          'function',
          'getAvatarUploadXml'
        );
      }
      if ($this->data['agree_to_terms'] == 1) {
        $fields['terms'] = array(
          $this->data['headline_terms'],
          'isNum',
          TRUE,
          'checkbox',
          1,
          '',
          0
        );
      }
      // Add dynamic data fields
      if (!empty($this->data['dynamic_class']) && is_array($this->data['dynamic_class'])) {
        $this->_initBaseSurfers();
        $dynFields = $this->baseSurfers->getDynamicEditFields(
          $this->data['dynamic_class'],
          'dynamic',
          $this->parentObj->topic['TRANSLATION']['lng_id'],
          TRUE
        );
        $fields = array_merge($fields, $dynFields);
      }
      $this->outputDialog = new base_dialog(
        $this,
        $this->paramName,
        $fields,
        $data,
        $hidden
      );
      $this->outputDialog->baseLink = $this->baseLink;
      $this->outputDialog->dialogTitle = papaya_strings::escapeHTMLChars(
        isset($this->data['title']) ? $this->data['title'] : ''
      );
      $this->outputDialog->buttonTitle = papaya_strings::escapeHTMLChars(
        $this->data['caption_submit']
      );
      $this->outputDialog->dialogDoubleButtons = FALSE;
      $this->outputDialog->msgs = &$this->msgs;
      if ($loadParams) {
        $this->outputDialog->loadParams();
      }
    }
  }

  /**
  * Get summary XML
  *
  * Display a summary of the verified user entries before actually registering.
  *
  * @return string XML
  */
  function getSummaryXML() {
    $result = '';
    if (isset($this->data['text_confirm'])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
      $values = array();
      $this->addReplaceValue($values, 'HANDLE', 'surfer_handle');
      $this->addReplaceValue($values, 'GIVENNAME', 'surfer_givenname');
      $this->addReplaceValue($values, 'SURNAME', 'surfer_surname');
      $this->addReplaceValue($values, 'EMAIL', 'surfer_email_1');
      $this->addReplaceValue(
        $values,
        'GENDER',
        'surfer_gender',
        array(
          'f' => $this->data['caption_female'],
          'm' => $this->data['caption_male']
        )
      );
      $template = new base_simpletemplate();
      $textConfirm = $template->parse($this->data['text_confirm'], $values);
      $result .= sprintf(
        '<text>%s</text>',
        $this->getXHTMLString($textConfirm)
      );
    }
    // Generate the summary contents
    $result .= '<summary>';
    $result .= $this->getSummaryItem('handle', 'surfer_handle', 'caption_username');
    $result .= $this->getSummaryItem('givenname', 'surfer_givenname', 'caption_givenname');
    $result .= $this->getSummaryItem('surname', 'surfer_surname', 'caption_surname');
    $result .= $this->getSummaryItem('email', 'surfer_email_1', 'caption_email');
    $result .= $this->getSummaryItem(
      'gender',
      'surfer_gender',
      'caption_gender',
      array('f' => $this->data['caption_female'], 'm' => $this->data['caption_male'])
    );
    // Add dynamic data fields
    if (!empty($this->data['dynamic_class']) && is_array($this->data['dynamic_class'])) {
      $this->_initBaseSurfers();
      $dynFields = $this->baseSurfers->getDynamicEditFields(
        $this->data['dynamic_class'],
        'dynamic',
        $this->parentObj->topic['TRANSLATION']['lng_id']
      );
      if (!empty($dynFields)) {
        foreach ($dynFields as $fieldName => $dynField) {
          $result .= $this->getSummaryItem(
            'dyn_'.$fieldName, $fieldName, $dynField[0], $dynField[5]
          );
        }
      }
    }
    $result .= '</summary>';
    // Now build a form in which all fields are hidden, but with two submit buttons
    $this->addSummaryFormItem($hidden, 'surfer_handle');
    $this->addSummaryFormItem($hidden, 'surfer_givenname');
    $this->addSummaryFormItem($hidden, 'surfer_surname');
    $this->addSummaryFormItem($hidden, 'surfer_email_1');
    $this->addSummaryFormItem($hidden, 'surfer_email_2');
    $this->addSummaryFormItem($hidden, 'surfer_password_1');
    $this->addSummaryFormItem($hidden, 'surfer_password_2');
    $this->addSummaryFormItem($hidden, 'surfer_gender');
    // Add hidden dynamic data fields
    if (!empty($dynFields)) {
      foreach ($dynFields as $fieldName => $dynField) {
        $result .= $this->addSummaryFormItem($hidden, $fieldName);
      }
    }
    if ($this->data['agree_to_terms'] == 1) {
      $hidden['terms'] = 1;
    }
    $fields = array();
    $data = array();
    $verificationDialog = new base_dialog(
      $this,
      $this->paramName,
      $fields,
      $data,
      $hidden
    );
    $verificationDialog->baseLink = $this->baseLink;
    $verificationDialog->buttonTitle = papaya_strings::escapeHTMLChars(
      $this->data['caption_edit']
    );
    $verificationDialog->addButton('final', $this->data['caption_confirm']);
    if (is_object($verificationDialog)) {
      $result .= $verificationDialog->getDialogXML();
    }
    return $result;
  }

  /**
  * Get a summary item
  *
  * @param string $element XML node name to use
  * @param string $field form field to read data from
  * @param string $caption edit field for caption
  * @param mixed $values array to provide data values (optional, default NULL)
  * @return string XML
  */
  function getSummaryItem($element, $field, $caption, $values = NULL) {
    $result = '';
    if (isset($this->outputDialog->params[$field]) &&
        $this->outputDialog->params[$field] != '') {
      if ($values !== NULL && isset($values[$this->outputDialog->params[$field]])) {
        $result = sprintf(
          '<%1$s caption="%2$s">%3$s</%1$s>',
          $element,
          papaya_strings::escapeHTMLChars(
            isset($this->data[$caption]) ? $this->data[$caption] : $caption
          ),
          papaya_strings::escapeHTMLChars($values[$this->outputDialog->params[$field]])
        );
      } else {
        $result = sprintf(
          '<%1$s caption="%2$s">%3$s</%1$s>',
          $element,
          papaya_strings::escapeHTMLChars(
            isset($this->data[$caption]) ? $this->data[$caption] : $caption
          ),
          papaya_strings::escapeHTMLChars($this->outputDialog->params[$field])
        );
      }
    }
    return $result;
  }

  /**
  * Add a replacement item for a simple template
  *
  * @param array &$replacements reference to array of simple template items
  * @param string $item name of the simple template item
  * @param string $field form field to read data from
  * @param mixed $values array to provide data values (optional, default NULL)
  */
  function addReplaceValue(&$replacements, $item, $field, $values = NULL) {
    if (isset($this->outputDialog->params[$field]) &&
        $this->outputDialog->params[$field] != '') {
      if ($values !== NULL && isset($values[$this->outputDialog->params[$field]])) {
        $replacements[$item] = $values[$this->outputDialog->params[$field]];
      } else {
        $replacements[$item] = $this->outputDialog->params[$field];
      }
    }
  }

  /**
  * Add a replacement item for a simple template after verification
  *
  * @param array $data array with data source for replacements
  * @param array &$replacements reference to array of simple template items
  * @param string $item name of the simple template item
  * @param string $field form field to read data from
  * @param mixed $values array to provide data values (optional, default NULL)
  */
  function addVerifiedReplaceValue($data, &$replacements, $item, $field, $values = NULL) {
    if (isset($data[$field]) && $data[$field] != '') {
      if ($values !== NULL && isset($values[$data[$field]])) {
        $replacements[$item] = $values[$data[$field]];
      } else {
        $replacements[$item] = $data[$field];
      }
    }
  }

  /**
  * Add a summary dialog item (hidden field)
  *
  * @param array &$hidden reference to array of hidden fields
  * @param string $field form field to read data from
  */
  function addSummaryFormItem(&$hidden, $field) {
    if (isset($this->outputDialog->params[$field]) &&
      $this->outputDialog->params[$field] != '') {
      $hidden[$field] = $this->outputDialog->params[$field];
    }
  }

  /**
  * Internal helper method to get a parameter outside of the parameter namespace
  *
  * @param string $param name of the parameter
  * @param mixed $default (optional, default NULL) default value if parameter not set
  * @return mixed value of the parameter or $default if not set
  */
  function _getRawParam($param, $default = NULL) {
    $result = $default;
    if (isset($_POST[$param])) {
      $result = $_POST[$param];
    } elseif (isset($_GET[$param])) {
      $result = $_GET[$param];
    }
    return $result;
  }

  /**
  * Check URL filename
  *
  * Called by base_topic's checkURLFileName() method.
  * This method is necessary to allow links in opt-in emails to work correctly
  * when URL fixation is turned on and the page is renamed after an email was sent
  * but before the contained link is clicked.
  * In this case, just a basic check is applied that prevents URLs
  * with special characters from being used.
  * All of this is only taken into consideration if the opt-in parameter is present.
  * Otherwise, the normal redirection behavior is used.
  *
  * @param string $currentFileName
  * @param string $outputMode
  * @return mixed - redirect target or FALSE
  */
  public function checkURLFileName($currentFileName, $outputMode) {
    $registerToken = $this->_getRawParam($this->paramName.'_id', '');
    if ($registerToken != '') {
      $normalizedFileName = $this->escapeForFileName($currentFileName, 'index');
      if ($currentFileName != $normalizedFileName) {
        // Still redirect (and let opt-in fail) if we've got some weird characters in the URL
        return 'index';
      }
      return FALSE;
    }
    // If we are here, there was no opt-in parameter, and we can resume to the normal behavior
    $pageFileName = $this->escapeForFilename(
      $this->parentObj->topic['TRANSLATION']['topic_title'],
      'index',
      $this->parentObj->currentLanguage['lng_ident']
    );
    if ($currentFileName != $pageFileName) {
      $url = $this->getWebLink(
        $this->parentObj->topicId, NULL, $outputMode, NULL, NULL, $pageFileName
      );
      $queryString = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : '';
      $url = $this->getAbsoluteURL($url).$this->recodeQueryString($queryString);
    } else {
      $url = FALSE;
    }
    return $url;
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
