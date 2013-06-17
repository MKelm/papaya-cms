<?php
/**
* Page module - user profiles
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
* @subpackage _Base-Community
* @version $Id: content_profile.php 38470 2013-04-30 14:56:54Z kersken $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - user profiles
*
* Surfer profile data change form
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class content_profile extends base_content {

  /**
  * Papaya database table surfer
  * @var string $tableSurfer
  */
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  /**
  * Papaya database table surferpermlink
  * @var string $tableSurfer
  */
  var $tableLink = PAPAYA_DB_TBL_SURFERPERMLINK;

  /**
  * Papaya database table surferchangerequests
  * @var string $tableChangeRequests
  */
  var $tableChangeRequests = PAPAYA_DB_TBL_SURFERCHANGEREQUESTS;

  /**
  * List of change requests
  * @var array $surferChangeRequests
  */
  var $surferChangeRequests = NULL;

  /**
  * Base surfers
  * @var object $surferAdmin surfer_admin
  */
  var $baseSurfers = NULL;

  /**
  * Instance of base_surfer
  *
  * @var base_surfer
  */
  var $surferObj = NULL;

  /**
  * Input error
  * @var string $inputError
  */
  var $inputError = '';

  /**
  * Global boolean to store if an avatar image has been uploaded.
  * @var boolean $avatarUploaded
  */
  var $avatarUploaded = FALSE;

  /**
  * Field definitions for backend edit dialog.
  * @var array
  */
  var $editGroups = array(
    array(
      'Main settings',
      'items-page',
      array(
        'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, ''),
        'subtitle' => array('Subtitle', 'isNoHTML', FALSE, 'input', 200, ''),
        'nl2br' => array(
          'Automatic linebreak',
          'isNum',
          FALSE,
          'translatedcombo',
          array(0 => 'Yes', 1 => 'No'),
          'Apply linebreaks from input to the HTML output.'
        ),
        'teaser' => array('Teaser', 'isSomeText', FALSE, 'richtext', 10),
        'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10),
        'Account Display',
        'show_handle' => array(
          'Show handle',
          'isNum',
          TRUE,
          'yesno',
          '',
          'Determines whether the username is displayed at all.',
          1
        ),
        'show_group' => array(
          'Show user group (read-only)',
          'isNum',
          TRUE,
          'yesno',
          '',
          'Determines whether the group the surfer belongs to should be displayed.',
          1
        ),
        'Account Changes',
        'need_oldpassword' => array(
          'Confirm with password?',
          'isNum',
          TRUE,
          'yesno',
          10,
          'Set to "No" if surfers do not need to enter their existing password to confirm changes.',
          1
        ),
        'edit_givenname' => array(
          'Edit given name',
          'isNum',
          TRUE,
          'yesno',
          '',
          'Determines whether the given name is displayed and can be edited.',
          1
        ),
        'edit_surname' => array(
          'Edit surname',
          'isNum',
          TRUE,
          'yesno',
          '',
          'Determines whether the surname is displayed and can be edited.',
          1
        ),
        'edit_gender' => array(
          'Edit gender',
          'isNum',
          TRUE,
          'yesno',
          '',
          'Determines whether the gender is displayed and can be edited.',
          1
        ),
        'upload_avatar' => array(
          'Upload avatar images',
          'isNum',
          TRUE,
          'yesno',
          '',
          'When set, the surfer is allowed to upload an avatar image into the media db.',
          0
        ),
        'change_password' => array(
          'Change password',
          'isNum',
          TRUE,
          'yesno',
          '',
          'Determines whether surfers can change their passwords.',
          1
        ),
        'change_handle' => array(
          'Change handle',
          'isNum',
          TRUE,
          'yesno',
          '',
          'Determines whether surfers can change their usernames.',
          0
        ),
        'change_email' => array(
          'Change email',
          'isNum',
          TRUE,
          'yesno',
          '',
          'Determines whether surfers can change their email address.',
          1
        ),
        'confirmation_page' => array(
          'Confirmation page',
          'isNum',
          TRUE,
          'pageid',
          10,
          'Page for email based change confirmations',
          0
        ),
        'Account Deletion',
        'delete_account' => array(
          'Delete account',
          'isNum',
          TRUE,
          'yesno',
          '',
          'Determines whether surfers can delete their own accounts',
          0
        ),
        'delete_policy' => array(
          'Delete policy',
          'isNum',
          TRUE,
          'radio',
          array(0 => 'Block', 1 => 'Delete'),
          'Determines whether accounts are really deleted or merely blocked',
          0
        ),
        'redir_page' => array(
          'Page after account deletion',
          'isNum',
          TRUE,
          'pageid',
          10,
          'Page to redirect to after the account has been deleted',
          0
        ),
        'redir_param' => array(
          'Param for redirect url',
          'isNoHTML',
          FALSE,
          'input',
          100,
          'Query string for redirect url',
          ''
        ),
        'Dynamic Data',
        'dynamic_class' => array(
          'Categories', 'isNum', FALSE, 'function', 'callbackClasses'
        )
      ),
    ),
    array(
      'Avatar settings',
      'items-user',
      array(
        'Avatar settings',
        'avatar_directory' => array('Image folder', 'isNum', TRUE, 'mediafolder'),
        'avatar_width' => array('Image width', 'isNum', TRUE, 'input', 4, '', 160),
        'avatar_height' => array('Image height', 'isNum', TRUE, 'input', 4, '', 240),
      ),
    ),
    array(
      'Messages and errors',
      'items-message',
      array(
        'Success messages',
        'success_save' => array(
          'Profile saved',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Profile saved.'
        ),
        'success_handle_changed' => array(
          'Handle changed',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Handle changed.'
        ),
        'success_password_changed' => array(
          'Password changed',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Password changed.'
        ),
        'success_upload' => array(
          'Avatar image uploaded',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Avatar image uploaded.'
        ),
        'success_change_request' => array(
          'Change email request ok',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Your request was successful.'
        ),
        'success_email_changed' => array(
          'Email changed successfully',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Your email address has been changed.'
        ),
        'Error messages',
        'error_not_logged_in' => array(
          'Not logged in',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Not logged in.'
        ),
        'error_input' => array(
          'Input error',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Input error'
        ),
        'error_handle_exists' => array(
          'Handle exists',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'This username exists.'
        ),
        'error_handle_blacklisted' => array(
          'Handle is blacklisted',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'This username or part of it has been blacklisted.'
        ),
        'error_email_blacklisted' => array(
          'Email blacklisted',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'This email address has been blacklisted.'
        ),
        'error_new_email_confirm' => array(
          'Emails do not match',
          'isNoHTML',
          TRUE,
          'input',
          200,
          'Email confirmation does not match with given email.',
          'Confirmation does not match.'
        ),
        'error_password' => array(
          'Wrong password',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Wrong password'
        ),
        'error_passwords_dont_match' => array(
          'Passwords do not match',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Passwords do not match.'
        ),
        'error_password_too_short' => array(
          'Password too short',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Password too short.'
        ),
        'error_password_equals_handle' => array(
          'Password equals handle',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Password is equal to username.'
        ),
        'error_password_blacklisted' => array(
          'Password is blacklisted',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'This password or part of it has been blacklisted.'
        ),
        'error_database' => array(
          'Database error',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Database error'
        ),
        'error_new_email_exists' => array(
          'Email already exists',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Email address already exists'
        ),
        'error_upload' => array(
          'Error during upload',
          'isNoHtml',
          TRUE,
          'input',
          200,
          '',
          'Error during upload'
        ),
        'error_nofile' => array(
          'No file uploaded',
          'isNoHtml',
          TRUE,
          'input',
          200,
          '',
          'No file has been uploaded.'
        ),
        'error_file_too_large' => array(
          'File too large',
          'isNoHtml',
          TRUE,
          'input',
          200,
          '',
          'File too large'
        ),
        'error_file_incomplete' => array(
          'File incomplete',
          'isNoHtml',
          TRUE,
          'input',
          200,
          '',
          'File is incomplete'
        ),
        'error_no_temporary_path' => array(
          'No temporary path',
          'isNoHtml',
          TRUE,
          'input',
          200,
          '',
          'No temporary path'
        ),
        'error_file_type' => array(
          'Invalid file type',
          'isNoHtml',
          TRUE,
          'input',
          200,
          '',
          'Invalid file type'
        ),
      ),
    ),
    array(
      'Mail settings',
      'items-mail',
       array(
        'mail_from_name' => array('From name', 'isNoHTML', TRUE, 'input', '200', ''),
        'mail_from_address' => array('From address', 'isEMail', TRUE, 'input', 200, ''),
        'mail_subject' => array('Subject', 'isNoHTML', TRUE, 'input', 200, ''),
        'mail_body' => array('Message', 'isNoHTML', TRUE, 'textarea', 7,
            '%LINK% %HANDLE% %GIVENNAME% %SURNAME%',
            'Confirm address change: {%LINK%}'),
        'mail_expiry' => array(
          'Request expires in (hours)',
          'isNum',
          TRUE,
          'input',
          200,
          '',
          24
        ),
      ),
    ),
    array(
      'Captions',
      'items-dialog',
      array(
        'Captions',
        'caption_form' => array(
          'Form caption',
          'isNoHtml',
          TRUE,
          'input',
          200,
          '',
          'Profile'
        ),
        'caption_upload_image' => array(
          'Upload image caption',
          'isNoHtml',
          TRUE,
          'input',
          200,
          '',
          'Avatar image.'
        ),
        'caption_upload_button' => array(
          'Upload button caption',
          'isNoHtml',
          TRUE,
          'input',
          200,
          '',
          'upload'
        ),
        'caption_req_delete' => array(
          'Delete change request',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Delete'
        ),
        'caption_req_data' => array(
          'Change request data',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Data'
        ),
        'caption_req_date' => array(
          'Change request date',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Date'
        ),
        'caption_req_expiry' => array(
          'Change request expiry',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Expiry'
        ),
        'caption_handle' => array('Handle', 'isNoHTML', TRUE, 'input', 200, '', 'Handle'),
        'caption_email' => array('Email', 'isNoHTML', TRUE, 'input', 200, '', 'Email'),
        'caption_section_email' => array(
          'Section email',
          'isNoHTML',
          FALSE,
          'input',
          200,
          'Adds a section caption above "Change email"',
          ''
        ),
        'caption_change_email' => array(
          'Change email',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Change email'
        ),
        'caption_change_email_verification' => array(
          'Email verification',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Email verification'
        ),
        'caption_group' => array('Group', 'isNoHTML', TRUE, 'input', 200, '', 'Group'),
        'caption_givenname' => array(
          'Given name',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Given name'
        ),
        'caption_surname' => array(
          'Surname',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Surname'
        ),
        'caption_gender' => array('Gender', 'isNoHTML', TRUE, 'input', 200, '', 'Gender'),
        'caption_female' => array('Female', 'isNoHTML', TRUE, 'input', 200, '', 'female'),
        'caption_male' => array('Male', 'isNoHTML', TRUE, 'input', 200, '', 'male'),
        'caption_avatar' => array('Avatar', 'isNoHTML', TRUE, 'input', 200, '', 'Avatar'),
        'caption_section_password' => array(
          'Section password',
          'isNoHTML',
          FALSE,
          'input',
          200,
          'Adds a section caption above "Old password"',
          ''
        ),
        'caption_old_password' => array(
          'Old password',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Old password'
        ),
        'caption_new_password' => array(
          'New password',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'New password'
        ),
        'caption_new_password_verification' => array(
          'New password verification',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Password verification'
        ),
        'headline_delete_account' => array(
          'Delete account headline',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Delete account'
        ),
        'caption_delete_account' => array(
          'Delete account button',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Delete'
        ),
        'caption_submit' => array(
          'Submit button',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Submit'
        ),
        'caption_reset' => array(
          'Cancel button',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Cancel'
        ),
      ),
    ),
    array(
      'Descriptions',
      'items-page',
      array(
        'Long descriptions',
        'descr_change_email' => array(
          'Change email',
          'isSomeText',
          TRUE,
          'simplerichtext',
          7,
          '',
          'Enter the new email address twice and wait for the confirmation mail.'
        ),
        'descr_change_password' => array(
          'Change password',
          'isSomeText',
          TRUE,
          'simplerichtext',
          7,
          '',
          'Enter the new password twice or leave blank to keep the old one.'
        ),
        'descr_old_password' => array(
          'Old password',
          'isSomeText',
          FALSE,
          'simplerichtext',
          7,
          '',
          'Enter the old password if you want to change your email.'
        ),
        'descr_need_old_password' => array(
          'Change password',
          'isSomeText',
          FALSE,
          'simplerichtext',
          7,
          '',
          'Enter the old password to confirm changes.'
        ),
        'descr_delete_account' => array(
          'Delete account',
          'isSomeText',
          TRUE,
          'simplerichtext',
          7,
          '',
          'Please click here if you want to permanently delete your account.'
        ),
        'descr_confirm_delete' => array(
          'Confirm account deletion',
          'isSomeText',
          TRUE,
          'simplerichtext',
          7,
          '',
          'Are you sure you want to permanently delete your account?'
        )
      )
    )
  );

  /**
  * Initialize the profile form
  */
  function initializeProfileForm() {
    if (isset($this->profileForm) && is_object($this->profileForm)) {
      return;
    }

    // Include frontend form class
    include_once(PAPAYA_INCLUDE_PATH.'system/base_frontend_form.php');

    $fields = array(
      'surfer_handle' => array(
        $this->data['caption_handle'],
        '(^[a-z\d._-]{4,'.PAPAYA_COMMUNITY_HANDLE_MAX_LENGTH.'}$)i',
        FALSE,
        'input',
        20
      ),
      'surfer_email' => array(
        $this->data['caption_email'],
        'isEmail',
        TRUE,
        'disabled_input',
        20
      ),
      'surfergroup_title' => array(
        $this->data['caption_group'],
        'isNoHTML',
        TRUE,
        'disabled_input',
        50
      ),
      'surfer_givenname' => array(
        $this->data['caption_givenname'],
        'isNoHTML',
        TRUE,
        'input',
        100
      ),
      'surfer_surname' => array(
        $this->data['caption_surname'],
        'isNoHTML',
        TRUE,
        'input',
        100
      ),
      'surfer_gender' => array(
        $this->data['caption_gender'],
        '/^f|m$/',
        TRUE,
        'radio',
        array(
          'f' => $this->data['caption_female'],
          'm' => $this->data['caption_male']
        )
      )
    );

    if ($this->data['change_handle'] == 0) {
      $fields['surfer_handle'][3] = 'disabled_input';
    }

    if ($this->data['show_handle'] == 0) {
      unset($fields['surfer_handle']);
    }

    if ($this->data['edit_givenname'] == 0) {
      unset($fields['surfer_givenname']);
    }
    if ($this->data['edit_surname'] == 0) {
      unset($fields['surfer_surname']);
    }
    if ($this->data['edit_gender'] == 0) {
      unset($fields['surfer_gender']);
    }

    if ($this->data['show_group'] == 0) {
      unset($fields['surfergroup_title']);
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

    if ($this->data['change_email'] == 1) {
      if (!empty($this->data['caption_section_email'])) {
        $fields[] = $this->data['caption_section_email'];
      }
      $fields['surfer_new_email'] = array(
        $this->data['caption_change_email'],
        'isEmail',
        FALSE,
        'input',
        100
      );
      $fields['surfer_new_email_confirm'] = array(
        $this->data['caption_change_email_verification'],
        'isEmail', FALSE, 'input', 100,
        $this->data['descr_change_email']);
    }

    if ($this->data['change_password'] == 1) {
      if (!empty($this->data['caption_section_password'])) {
        $fields[] = $this->data['caption_section_password'];
      }
      $fields['surfer_password1'] = array(
        $this->data['caption_new_password'],
        'isPassword',
        FALSE,
        'password',
        200
      );
      $fields['surfer_password2'] = array(
        $this->data['caption_new_password_verification'],
        'isSomeText',
        FALSE,
        'password',
        200,
        $this->data['descr_change_password']
      );
    }
    if (!empty($this->data['need_oldpassword']) || !empty($this->data['change_password'])) {
      $fields['surfer_password3'] = array(
        $this->data['caption_old_password'],
        'isSomeText',
        !empty($this->data['need_oldpassword']),
        'password',
        200,
        !empty($this->data['need_oldpassword']) ?
          $this->data['descr_need_old_password'] : $this->data['descr_old_password']
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

    $data = $this->surferData;
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

    $hidden = array('save' => 1);
    $this->profileForm = new base_frontend_form(
      $this, $this->paramName, $fields, $data, $hidden
    );
    $this->profileForm->msgs = &$this->msgs;
    $this->profileForm->loadParams();
    $this->profileForm->baseLink = $this->baseLink;
    $this->profileForm->dialogTitle = $this->data['caption_form'];
    $this->profileForm->buttonTitle = $this->data['caption_submit'];
    $this->profileForm->addButton('reset', $this->data['caption_reset'], 'reset');
    $this->profileForm->uploadFiles = TRUE;
    $this->profileForm->dialogHideButtons = FALSE;
  }

  /**
  * Internal helper function to initialize base_surfers object
  */
  function _initBaseSurfers() {
    if (!(isset($this->baseSurfers) && is_object($this->baseSurfers))) {
      include_once(dirname(__FILE__).'/base_surfers.php');
      $this->baseSurfers = new surfer_admin($this->msgs);
    }
  }

  /**
  * Callback method to get XML for the avatar upload field
  *
  * @param string $name
  * @param array $field
  * @param mixed $data
  * @return string XML
  */
  function getAvatarUploadXml($name, $field, $data) {
    return sprintf(
      '<input value="%s" type="file" name="%s[%s]" maxlength="100"'.
      ' size="50" fid="%s" mandatory="%s" />'.LF.
      '<papaya:media src="%s" width="%s" height="%s" />'.LF,
      papaya_strings::escapeHTMLChars($data),
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name),
      papaya_strings::escapeHTMLChars($name),
      ($field[2] == TRUE) ? 'true' : 'false',
      papaya_strings::escapeHTMLChars($data),
      papaya_strings::escapeHTMLChars($this->data['avatar_width']),
      papaya_strings::escapeHTMLChars($this->data['avatar_height'])
    );
  }

  /**
  * Return xml representation for dialog form - surfer profile.
  */
  function getProfileFormXml() {
    $this->initializeProfileForm();
    $this->profileForm->loadParams();
    return $this->profileForm->getDialogXML();
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
  * Load the surfer's profile data
  *
  * @param mixed string|NULL $surferId optional, default NULL
  */
  function loadProfileData($surferId = NULL) {
    unset($this->surferData);
    if ($surferId == NULL) {
      $surferId = $this->surferObj->surferId;
    }
    $this->surferData = $this->baseSurfers->loadSurfer($surferId, TRUE);
  }

  /**
  * Save the surfer's profile data
  *
  * @return mixed success
  */
  function saveProfileData() {
    unset($this->surferData['surfergroup_title']);
    unset($this->surferData['surfer_new_email']);
    unset($this->surferData['surfer_new_email_confirm']);
    unset($this->surferData['surfer_password1']);
    unset($this->surferData['surfer_password2']);
    unset($this->surferData['surfer_password3']);
    unset($this->surferData['surfer_avatar']);
    if (
      $this->data['change_handle'] == 0 ||
      $this->data['show_handle'] == 0 ||
      empty($this->profileForm->data['surfer_handle'])) {
      unset($this->surferData['surfer_handle']);
    }
    if ($this->data['edit_givenname'] == 0) {
      unset($this->surferData['surfer_givenname']);
    }
    if ($this->data['edit_surname'] == 0) {
      unset($this->surferData['surfer_surname']);
    }
    if ($this->data['edit_gender'] == 0) {
      unset($this->surferData['surfer_gender']);
    }
    $result = $this->baseSurfers->saveSurfer($this->surferData);

    // Now save the dynamic data, if necessary
    if (isset($this->data['dynamic_class']) && is_array($this->data['dynamic_class']) &&
        !empty($this->data['dynamic_class'])) {
      // Get the field names
      $dynFieldNames = $this->baseSurfers->getDataFieldNames($this->data['dynamic_class']);
      // Get those fields that are actually set
      $dynFields = array();
      foreach ($dynFieldNames as $fieldName) {
        if (isset($this->profileForm->data['dynamic_'.$fieldName])) {
          $dynFields[$fieldName] = $this->profileForm->data['dynamic_'.$fieldName];
        }
      }
      if (!empty($dynFields)) {
        $result = $result & $this->baseSurfers->setDynamicData($this->surferObj->surferId, $dynFields);
      }
    }
    return $result;
  }

  /**
  * Save the surfer's uploaded avatar image
  *
  * @return mixed success
  */
  function saveAvatarData() {
    return $this->baseSurfers->saveSurfer(
      array(
        'surfer_id' => $this->surferData['surfer_id'],
        'surfer_avatar' => $this->surferData['surfer_avatar']
      )
    );
  }

  /**
  * Send mail to confirm address change
  *
  * @param string $confirmString
  * @access public
  * @return boolean
  */
  function sendConfirmMail($emailConfirmString) {
    include_once (PAPAYA_INCLUDE_PATH.'system/sys_email.php');
    $email = new email();
    $email->addAddress($this->profileForm->data['surfer_new_email']);
    $email->setSender(
      $this->data['mail_from_address'],
      $this->data['mail_from_name']
    );
    $email->setSubject($this->data['mail_subject']);
    $email->setBody(
      $this->data['mail_body'],
      array(
        'LINK' => sprintf(
          '%s?mailchg=%s',
          $this->getAbsoluteURL(
            $this->getWebLink(
              $this->data['confirmation_page']
            ),
            NULL,
            FALSE
          ),
          $emailConfirmString
        ),
        'GIVENNAME' => $this->surferData['surfer_givenname'],
        'SURNAME' => $this->surferData['surfer_surname'],
        'HANDLE' => $this->surferData['surfer_handle']
      ),
      80
    );
    return $email->send();
  }

  /**
  * Get delete account form
  *
  * @return string XML
  */
  function getDeleteAccountForm() {
    $result = '';
    if ($this->data['delete_account'] != 1) {
      return $result;
    }
    // Include frontend form class
    include_once(PAPAYA_INCLUDE_PATH.'system/base_frontend_form.php');
    $hidden = array(
      'delete_account' => 1
    );
    $step = 'request';
    if (isset($this->params['delete_account']) && $this->params['delete_account'] == 1) {
      if (isset($this->params['confirm']) && $this->params['confirm'] == 1) {
        return $result;
      }
      $hidden['confirm'] = 1;
      $step = 'confirm';
    }
    $fields = array();
    $data = array();
    $deleteForm = new base_frontend_form(
      $this,
      $this->paramName,
      $fields,
      $this->surferData,
      $hidden
    );
    $deleteForm->msgs = &$this->msgs;
    $deleteForm->loadParams();
    $deleteForm->baseLink = $this->baseLink;
    $deleteForm->buttonTitle = $this->data['caption_delete_account'];
    if (is_object($deleteForm)) {
      $result = sprintf(
        '<delete-account step="%s">'.LF,
        $step
      );
      $result .= sprintf(
        '<headline>%s</headline>'.LF,
        papaya_strings::escapeHTMLChars($this->data['headline_delete_account'])
      );
      if (isset($this->params['delete_account']) && $this->params['delete_account'] == 1) {
        $result .= sprintf(
          '<description>%s</description>'.LF,
          $this->getXHTMLString($this->data['descr_confirm_delete'])
        );
      } else {
        $result .= sprintf(
          '<description>%s</description>'.LF,
          $this->getXHTMLString($this->data['descr_delete_account'])
        );
      }
      $result .= $deleteForm->getDialogXML();
      $result .= sprintf(
        '<cancel href="%s">%s</cancel>'.LF,
        $this->getWebLink(),
        papaya_strings::escapeHTMLChars($this->data['caption_reset'])
      );
      $result .= '</delete-account>'.LF;
    }
    return $result;
  }

  /**
  * Delete surfer account
  */
  function deleteAccount() {
    // If unappropriate for any reason, get out of here
    if ($this->data['delete_account'] != 1) {
      return;
    }
    if (!$this->surferObj->isValid) {
      return;
    }
    if (!(
          isset($this->params['delete_account']) &&
          $this->params['delete_account'] == 1 &&
          isset($this->params['confirm']) &&
          $this->params['confirm'] == 1
        )) {
      return;
    }
    if ($this->data['delete_policy'] == 0 ||
        $this->baseSurfers->isEditor($this->surferObj->surferId)) {
      $deleted = $this->baseSurfers->setValid($this->surferObj->surferId, 4);
    } else {
      $deleted = $this->baseSurfers->deleteSurfer($this->surferObj->surferId);
    }
    if ($deleted == TRUE) {
      session_destroy();
    }
    if ($this->data['redir_page'] > 0) {
      $logoutURL = $this->getWebLink($this->data['redir_page']);
      if ($this->data['redir_param'] != '') {
        $logoutURL = $logoutURL.'?'.str_replace('?', '', $this->data['redir_param']);
      }
    } else {
      $logoutURL = NULL;
    }
    $this->surferObj->logout($logoutURL);
  }

  /**
  * Check password according to current password policy.
  *
  * @see base_surfers::checkPasswordForPolicy
  * @return integer 0 if password complies to the policy, a negative value otherwise
  */
  function checkPassword() {
    $this->_initBaseSurfers();
    $password = $this->profileForm->data['surfer_password1'];
    $handle = '';
    if ($this->data['show_handle'] && !empty($this->surferData['surfer_handle'])) {
      $handle = $this->surferData['surfer_handle'];
    }
    return $this->baseSurfers->checkPasswordForPolicy($password, $handle);
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $result = '';
    $saveSuccess = FALSE;
    $this->setDefaultData();
    $this->_initBaseSurfers();
    if (!isset($this->surferObj) || !is_object($this->surferObj)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
      $this->surferObj = &base_surfer::getInstance();
    }
    if (!isset($this->mediaDB) || !is_object($this->mediaDB) ||
        get_class($this->mediaDB) != 'base_mediadb_edit') {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');
      $this->mediaDB = new base_mediadb_edit;
    }
    if (isset($_GET['mailchg'])) {
      $surferId = $this->baseSurfers->getIdByToken($_GET['mailchg']);
      if ($surferId !== FALSE) {
        $this->loadProfileData($surferId);
        $email = $this->baseSurfers->getChangeRequest($_GET['mailchg']);
        if ($email !== FALSE) {
          $this->surferData['surfer_email'] = $email;
          if ($this->saveProfileData()) {
            $this->baseSurfers->deleteChangeRequest($_GET['mailchg']);
            $result .= $this->getSuccessMessageXml($this->data['success_email_changed']);
          } else {
            $result .= $this->getErrorMessageXml($this->data['error_database']);
          }
        }
      }
    }

    if (isset($this->surferObj) &&
        is_object($this->surferObj) &&
        $this->surferObj->isValid) {
      // Delete account and log out if selected and confirmed
      if ($this->data['delete_account'] == 1) {
        if (isset($this->params['delete_account']) && $this->params['delete_account'] == 1 &&
            isset($this->params['confirm']) && $this->params['confirm'] == 1) {
          $this->deleteAccount();
          $this->surferObj->logout();
          exit();
        }
      }
      $this->loadProfileData();
      $this->initializeProfileForm();
      $updateable = FALSE;
      if (isset($this->params['save']) && $this->params['save'] == 1) {
        // Assume that there is no error yet
        $error = FALSE;
        $this->profileForm->loadParams();
        $checkDialog = $this->profileForm->checkDialogInput();
        if (!isset($this->profileForm->inputErrors)) {
          $this->profileForm->inputErrors = array();
        }
        if ($checkDialog) {
          $changeIsValid = FALSE;
          if (isset($this->data['need_oldpassword']) &&
              $this->data['need_oldpassword'] == 0) {
            // verification for old password not needed
            $changeIsValid = TRUE;
          }
          if (!$changeIsValid &&
              isset($this->profileForm->data['surfer_password3'])) {
            // verify old password before change
            $oldPasswordHash = $this->baseSurfers->getPasswordHash(
              $this->profileForm->data['surfer_password3']
            );
            if ($oldPasswordHash == $this->surferData['surfer_password']) {
              $changeIsValid = TRUE;
            }
          }
          if ($changeIsValid) {
            $this->surferData['surfer_givenname'] = isset($this->params['surfer_givenname']) ?
              $this->params['surfer_givenname'] : NULL;
            $this->surferData['surfer_surname'] = isset($this->params['surfer_surname']) ?
              $this->params['surfer_surname'] : NULL;
            $this->surferData['surfer_gender'] = isset($this->params['surfer_gender']) ?
              $this->params['surfer_gender'] : NULL;

            if ($this->data['change_email'] == 1 &&
                $this->profileForm->data['surfer_new_email'] != '' ||
                $this->profileForm->data['surfer_new_email_confirm'] != '') {
              if ($this->profileForm->data['surfer_new_email'] ==
                  $this->profileForm->data['surfer_new_email_confirm']) {
                if (!$this->baseSurfers->existEmail($this->profileForm->data['surfer_new_email'])) {
                  if ($this->baseSurfers->checkEmailAgainstBlacklist(
                    $this->profileForm->data['surfer_new_email'])) {
                    $confirmString = $this->baseSurfers->emailChangeRequest(
                      $this->surferObj->surferId,
                      $this->profileForm->data['surfer_new_email'],
                      $this->data['mail_expiry']);
                    $this->sendConfirmMail($confirmString);
                    $result .= $this->getSuccessMessageXml(
                      $this->data['success_change_request']
                    );
                    $updateable = TRUE;
                  } else {
                    $result .= $this->getErrorMessageXml(
                      $this->data['error_email_blacklisted'],
                      'surfer_new_email'
                    );
                    $this->profileForm->inputErrors['surfer_new_email'] = 1;
                    $this->profileForm->inputErrors['surfer_new_email_confirm'] = 1;
                  }
                } else {
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_new_email_exists'],
                    'surfer_new_email'
                  );
                  $this->profileForm->inputErrors['surfer_new_email'] = 1;
                  $this->profileForm->inputErrors['surfer_new_email_confirm'] = 1;
                }
              } else {
                $result .= $this->getErrorMessageXml(
                  $this->data['error_new_email_confirm'],
                  'surfer_new_email_confirm'
                );
                $this->profileForm->inputErrors['surfer_new_email_confirm'] = 1;
              }
            }
            if ($this->data['change_handle'] == 1 &&
              $this->profileForm->data['surfer_handle'] != '' &&
              $this->profileForm->data['surfer_handle'] !=
                $this->surferObj->surfer['surfer_handle']) {
              if (
                $this->baseSurfers->existHandle($this->profileForm->data['surfer_handle']) &&
                $this->baseSurfers->getIdByHandle($this->profileForm->data['surfer_handle']) !=
                  $this->surferData['surfer_id']) {
                $result .= $this->getErrorMessageXml($this->data['error_handle_exists']);
                $this->profileForm->inputErrors['surfer_handle'] = 1;
              } else {
                if ($this->baseSurfers->checkHandle($this->profileForm->data['surfer_handle'])) {
                  $this->surferData['surfer_handle'] = $this->profileForm->data['surfer_handle'];
                  $result .= $this->getSuccessMessageXml($this->data['success_handle_changed']);
                  $updateable = TRUE;
                } else {
                  $result .= $this->getErrorMessageXml($this->data['error_handle_blacklisted']);
                  $this->profileForm->inputErrors['surfer_handle'] = 1;
                }
              }
            }

            if ($this->data['change_password'] == 1 &&
              $this->profileForm->data['surfer_password1'] != '') {
              $checkPassword = $this->checkPassword();
              if ($checkPassword === 0) {
                if ($this->profileForm->data['surfer_password1'] ==
                    $this->profileForm->data['surfer_password2']) {
                  $this->surferData['surfer_password'] = $this->baseSurfers->getPasswordHash(
                    $this->profileForm->data['surfer_password1']
                  );
                  $result .= $this->getSuccessMessageXml(
                    $this->data['success_password_changed']
                  );
                  $updateable = TRUE;
                } else {
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_passwords_dont_match'],
                    'surfer_password2'
                  );
                  $this->profileForm->inputErrors['surfer_password1'] = 1;
                  $this->profileForm->inputErrors['surfer_password2'] = 1;
                }
              } else {
                $error = FALSE;
                if ($checkPassword + 4 <= 0) {
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_password_blacklisted'],
                    'surfer_password_1'
                  );
                  $checkPassword += 4;
                  $error = TRUE;
                }
                if ($checkPassword + 2 <= 0) {
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_password_equals_handle'],
                    'surfer_password_1'
                  );
                  $checkPassword += 2;
                  $error = TRUE;
                }
                if ($checkPassword + 1 <= 0) {
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_password_too_short'],
                    'surfer_password_1'
                  );
                  $error = TRUE;
                }
                if ($error) {
                  $this->profileForm->inputErrors['surfer_password1'] = 1;
                  $this->profileForm->inputErrors['surfer_password2'] = 1;
                }
              }
            }

            if ($this->data['change_password'] == 1 &&
              $this->profileForm->data['surfer_password1'] == NULL &&
              $this->profileForm->data['surfer_password2'] != '') {

              $result .= $this->getErrorMessageXml(
                $this->data['error_passwords_dont_match'],
                'surfer_password2'
              );
              $this->profileForm->inputErrors['surfer_password1'] = 1;
              $this->profileForm->inputErrors['surfer_password2'] = 1;
            }

            if ($this->data['upload_avatar'] == 1) {
              if (@isset($_FILES[$this->paramName]['tmp_name']['surfer_avatar'])) {
                $updateable = TRUE;
                $uploadData = $_FILES[$this->paramName];
                switch ($uploadData['error']) {  // check if error encountered
                case 1:                        // exceeded max file size
                case 2:                        // exceeded max post size
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_file_too_large'],
                    'surfer_avatar'
                  );
                  $this->profileForm->inputErrors['surfer_avatar'] = 1;
                  break;
                case 3:
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_file_incomplete'],
                    'surfer_avatar'
                  );
                  $this->profileForm->inputErrors['surfer_avatar'] = 1;
                  break;
                case 6:
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_no_temporary_path'],
                    'surfer_avatar'
                  );
                  $this->profileForm->inputErrors['surfer_avatar'] = 1;
                  break;
                case 4:
                  $result .= $this->getErrorMessageXml(
                    $this->data['error_nofile'],
                    'surfer_avatar'
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
                    $result .= $this->getErrorMessageXml($this->data['error_nofile']);
                  } elseif ($tempFileSize >= $this->mediaDB->getMaxUploadSize()) {
                    $result .= $this->getErrorMessageXml(
                      $this->data['error_file_too_large'],
                      'surfer_avatar'
                    );
                  } elseif ($tempFileType == NULL ||
                            $tempFileType < 1 ||
                            $tempFileType > 3) {
                    $result .= $this->getErrorMessageXml(
                      $this->data['error_file_type'],
                      'surfer_avatar'
                    );
                  } else {
                    $fileId = $this->mediaDB->addFile(
                      $uploadData['tmp_name']['surfer_avatar'],
                      $uploadData['name']['surfer_avatar'],
                      $this->data['avatar_directory'],
                      $this->surferObj->surferId,
                      $uploadData['type']['surfer_avatar'],
                      'uploaded_file'
                    );

                    if (!$fileId) {
                      $result .= $this->getErrorMessageXml(
                        $this->data['error_database'],
                        'surfer_avatar'
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
                          $this->data['error_database'],
                          'surfer_avatar'
                        );
                      } else {
                        $mediaId = $fileId;
                      }
                    }
                  }
                }

                if (isset($mediaId) && !empty($mediaId) &&
                    checkit::isGUID($mediaId, TRUE)) {
                  $this->surferData['surfer_avatar'] = $mediaId;
                  $this->saveAvatarData();
                  $result .= $this->getSuccessMessageXml($this->data['success_upload']);
                }
              }
            }
            // save only if there aren't any errors
            if (!in_array(1, $this->profileForm->inputErrors) && $updateable == TRUE) {
              $saveSuccess = $this->saveProfileData();
              if ($saveSuccess) {
                $result .= $this->getSuccessMessageXml($this->data['success_save']);
                if (isset($this->surferData['surfer_handle'])) {
                  $this->setSessionValue(
                    $this->surferObj->surfernameVar, $this->surferData['surfer_handle']
                  );
                }
              } else {
                $result .= $this->getErrorMessageXml($this->data['error_database']);
              }
            }
          } else {
            $this->profileForm->inputErrors['surfer_password3'] = 1;
            $result .= $this->getErrorMessageXml(
              $this->data['error_password'],
              'surfer_password3'
            );
          }
        } else {
          $fields = array(
            'surfer_handle',
            'surfer_givenname',
            'surfer_surname',
            'surfer_gender',
            'surfer_new_email',
            'surfer_new_email_confirm',
            'surfer_password1',
            'surfer_password2',
            'surfer_password3'
          );
          $errorField = '';
          foreach ($fields as $field) {
            if (isset($this->profileForm->inputErrors[$field]) &&
                $this->profileForm->inputErrors[$field] == 1) {
              $errorField = $field;
              break;
            }
          }
          // If an error field exists, the error_input message is displayed
          // Otherwise it is a token error with a different message
          if ($errorField == $field) {
            if (in_array(
                  $errorField, array('surfer_password1', 'surfer_password2', 'surfer_password3'))
               ) {
              $message = $this->data['error_password'];
            } else {
              $message = $this->data['error_input'];
            }
            $result .= $this->getErrorMessageXml($message, $errorField);
          } else {
            $result .= $this->getErrorMessageXml(
              'Invalid Token. Progress is canceled', $errorField
            );
          }
        }
      }
      if ($saveSuccess) {
        unset($this->profileForm); // To enforce use of updated dialog data.
        $this->loadProfileData();
      }
      unset($this->surferData['surfer_password']);
      if (!(
            isset($this->data['delete_account']) &&
            $this->data['delete_account'] == 1 &&
            isset($this->params['delete_account']) &&
            $this->params['delete_account'] == 1
          )) {
        $result .= sprintf(
          '<title>%s</title>'.LF.
          '<subtitle>%s</subtitle>'.LF.
          '<text>%s</text>'.LF.
          '<userdata>%s</userdata>'.LF,
          papaya_strings::escapeHTMLChars($this->data['title']),
          papaya_strings::escapeHTMLChars($this->data['subtitle']),
          $this->getXHTMLString($this->data['text']),
          $this->getProfileFormXml()
        );
        $result .= sprintf(
          '<descr-change-email>%s</descr-change-email>'.LF,
          $this->getXHTMLString($this->data['descr_change_email'])
        );
        $result .= sprintf(
          '<descr-change-password>%s</descr-change-password>'.LF,
          $this->getXHTMLString($this->data['descr_change_password'])
        );
      } else {
        $result .= sprintf(
          '<title>%s</title>'.LF.
          '<subtitle>%s</subtitle>'.LF,
          papaya_strings::escapeHTMLChars($this->data['title']),
          papaya_strings::escapeHTMLChars($this->data['subtitle'])
        );
      }
      if ($this->data['delete_account'] == 1) {
        $result .= $this->getDeleteAccountForm();
      }
    } else {
      $result .= $this->getErrorMessageXml($this->data['error_not_logged_in']);
      $result .= sprintf(
        '<title>%s</title><subtitle>%s</subtitle><text>%s</text>'.LF,
        papaya_strings::escapeHTMLChars($this->data['title']),
        papaya_strings::escapeHTMLChars($this->data['subtitle']),
        $this->getXHTMLString($this->data['text'])
      );
    }

    return $result;
  }

  /**
  * Retrieves parsed page teaser data.
  *
  * @return string
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    $this->initializeParams();
    $result = sprintf(
      '<title>%s</title><text>%s</text>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title']),
      $this->getXHTMLString($this->data['teaser'])
    );
    return $result;
  }

  /**
  * Disable caching for this module because the form in the output contains
  * a token which may become invalid.
  *
  * @see papaya_page::getCacheId
  * @return FALSE|string $cacheId
  */
  function getCacheId() {
    return FALSE;
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
    $this->_initBaseSurfers();
    $result = '';
    $lng = $this->parentObj->topic['TRANSLATION']['lng_id'];
    $commonTitle = $this->_gt('Category');
    $sql = "SELECT c.surferdataclass_id,
                   ct.surferdataclasstitle_classid,
                   ct.surferdataclasstitle_name,
                   ct.surferdataclasstitle_lang
              FROM %s AS c LEFT OUTER JOIN %s AS ct
                ON c.surferdataclass_id = ct.surferdataclasstitle_classid
             WHERE ct.surferdataclasstitle_lang = %d
             ORDER BY c.surferdataclass_order ASC";
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
