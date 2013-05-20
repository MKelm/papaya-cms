<?php
/**
* Page module - Login
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
* @version $Id: content_login.php 38470 2013-04-30 14:56:54Z kersken $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - Login
*
* Login form for surfer
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class content_login extends base_content {

  /**
  * Is cacheable?
  * @var boolean
  */
  var $cacheable = FALSE;

  /**
  * Parameter group name
  * @var string
  */
  var $paramName = 'surf';

  /**
  * Central instance of surfer_admin base class
  * @var object
  */
  var $surferAdmin = NULL;

  /**
  * Edit field groups
  * @var array
  */
  var $editGroups = array(
    array(
      'General',
      'categories-content',
      array(
        'title' =>
          array('Title', 'isNoHTML', TRUE, 'input', 400, '', ''),
        'subtitle' =>
          array('Subtitle', 'isNoHTML', FALSE, 'input', 400, '', ''),
        'login_by' =>
          array(
            'Login by',
            '/^(email|handle|any)$/',
            TRUE,
            'radio',
            array('email' => 'E-Mail', 'handle' => 'Handle', 'any' => 'Email or Handle'),
            '',
            'email'
          ),
        'reset_password' => array(
            'Reset password by',
            '/^(email|handle|both)$/',
            TRUE,
            'radio',
            array('email' => 'E-Mail', 'handle' => 'Handle', 'both' => 'E-Mail and Handle'),
            '',
            'email'
          ),
        'reg_page' => array('Registration page', 'isNum', FALSE, 'pageid', 10, '', 0),
        'topic' => array(
          'Page after login',
          'isNum',
          TRUE,
          'pageid',
          10,
          'Redirect to this page after login',
          0
        ),
        'password_page' => array(
          'Reset password page',
          'isNum',
          TRUE,
          'pageid',
          10,
          'ID of the page where users can request a password change. For each request,
          users will receive an email. Default is the current page id.', 0
        ),
        'register_paramname' => array(
          'Registration parameter namespace',
          'isNoHTML',
          TRUE,
          'input',
          10,
          'Parameter namespace to get the registration mail token',
          'srg'
        ),
        'Texts',
        'text_login' => array(
          'Login',
          'isSomeText',
          FALSE,
          'richtext',
          5,
          'Can contain the placeholder {%REGISTER%}.',
          'Please log in using your email address or username and password.'
        ),
        'text_change_password' => array(
          'Request password change',
          'isSomeText',
          TRUE,
          'richtext',
          5,
          '',
          'Please enter your email address and/or user name. You will receive an email.'
        ),
        'text_new_passwords' => array(
          'Password change',
          'isSomeText',
          TRUE,
          'richtext',
          5,
          '',
          'Please enter your new password twice.'
        ),
        'text_verified' => array(
          'Registration verified',
          'isSomeText',
          FALSE,
          'richtext',
          5,
          'You can use {%HANDLE%}, {%GIVENNAME%}, {%SURNAME%}, {%EMAIL%}, and {%GENDER%}.',
          'Your registration has been verified and you can log in.'
        ),
        'text_delay' => array(
          'Text for delayed verification',
          'isSomeText',
          FALSE,
          'richtext',
          5,
          'You can use {%TIMEINFO%} for n <hour[s]> <and> n <minute[s]>',
          'Please try again in at least {%TIMEINFO%}.'
        )
      )
    ),
    array(
      'Messages',
      'items-dialog',
      array(
        'Captions',
        'Caption_Prompt' => array(
          'Default prompt',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Enter email address and password'
        ),
        'Caption_Email' => array('Email', 'isNoHTML', TRUE, 'input', 200, '', 'Email'),
        'Caption_Password' => array('Password', 'isNoHTML', TRUE, 'input', 200, '', 'Password'),
        'Caption_Confirm_Password' => array(
          'Confirm password',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Confirm password'
        ),
        'Caption_Username' => array('Username', 'isNoHTML', TRUE, 'input', 200, '', 'Username'),
        'Caption_Username_Or_Email' => array(
          'Username or email',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Username or email'
        ),
        'Caption_Relogin' => array('Relogin', 'isNoHTML', TRUE, 'input', 200, '', 'Auto-relogin'),
        'Caption_Reg_Link' => array(
          'Register link',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'If you do not have a password, please click here.'
        ),
        'Caption_Passwd_Link' => array(
          'Password forgotten link',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Forgot your password? Please click here.'
        ),
        'Caption_Login_Button' => array(
          'Login button',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Login'
        ),
        'Caption_Save_Button' => array(
          'Save button',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Save'
        ),
        'Caption_Request_Button' => array(
          'Request button',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Request'
        ),
        'caption_female' => array('female', 'isNoHTML', TRUE, 'input', 50, '', 'female'),
        'caption_male' => array('male', 'isNoHTML', TRUE, 'input', 50, '', 'male'),
        'caption_hour' => array(
          'Caption "hour"',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'hour'
        ),
        'caption_hours' => array(
          'Caption "hours"',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'hours'
        ),
        'caption_and' => array(
          'Caption "and"',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'and'
        ),
        'caption_minute' => array(
          'Caption "minute"',
          'isNoHTML',
          TRUE,
          'input',
          100,
          '',
          'minute'
        ),
        'caption_minutes' => array(
          'Caption "minutes"',
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
      'Captions',
      'items-message',
      array(
        'Messages',
        'Input_Error' => array('Input error', 'isNoHTML', TRUE, 'input', 200, '', 'Input Error'),
        'Unknown_User' => array('Unknown user', 'isNoHTML', TRUE, 'input', 200, '', 'User unknown'),
        'Change_Requested' => array(
          'Change requested',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Password change requested'
        ),
        'Mail_Changed' => array(
          'Email address changed',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Email address changed'
        ),
        'Error_Mail_Change' => array(
          'Mail change error',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Email address could not be changed.'
        ),
        'Error_Token' => array(
          'Invalid Token',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Invalid Link'
        ),
        'Error_Permissions' => array(
          'Permission error',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Invalid permissions'
        ),
        'Error_Verification' => array(
          'Verification error',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Your registration could not be successfully verified.'
        ),
        'error_validation_time' => array(
          'Validation delayed error',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'You cannot be validated yet.'
        ),
        'Error_Database' => array(
          'Database error', 'isNoHTML', TRUE, 'input', 200, '', 'Database Error'
        )
      )
    ),
    array(
      'Email',
      'items-mail',
      array(
        'Request password (E-Mail)',
        'Password_Mail_From_Name' => array(
          'From (name)',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'SampleSender'
        ),
        'Password_Mail_From' => array(
          'From (email)',
          'isEMail',
          TRUE,
          'input',
          200,
          '',
          'sample@domain.tld'
        ),
        'Password_Mail_Subject' => array(
          'Subject',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          'Password Change requested'
        ),
        'Password_Mail_Body' => array(
          'Body',
          'isSomeText',
          TRUE, 'textarea',
          10,
          'Use {%LINK%} placeholder for the password reset link',
          "Password Change requested\n\n{%LINK%}"
        ),
        'Password_Mail_Expiry' => array(
          'Expires (in hours)',
          'isNum',
          TRUE,
          'input',
          200,
          '',
          24
        ),
        'Administrative email',
        'sender_name' => array('Sender name', 'isNoHTML', FALSE, 'input', 200, ''),
        'sender_email' => array('Sender email', 'isEMail', FALSE, 'input', 200, ''),
        'recipient_name' => array('Recipient name', 'isNoHTML', FALSE, 'input', 200, ''),
        'recipient_email' => array('Recipient email', 'isNoHTML', FALSE, 'input', 200, ''),
        'email_subject' => array(
          'Subject',
          'isNoHTML',
          FALSE,
          'input',
          200,
          '',
          'Email address changed'
        ),
        'email_body' => array(
          'Body',
          'isSomeText',
          FALSE,
          'textarea',
          7,
          'Use {%USER%} for name, {%TIME%} for timestamp, {%EMAIL%} for the new email address.',
          'User {%USER%} changed his/her email address at {%TIME%} to {%EMAIL%}'
        )
      )
    )
  );

  /**
  * Init an instance of the community base class
  *
  * @access private
  * @return object instance of class surfer_admin
  */
  function _initSurferAdmin() {
    if (!(isset($this->surferAdmin) && is_object($this->surferAdmin))) {
      include_once(dirname(__FILE__).'/base_surfers.php');
      $this->surferAdmin = new surfer_admin($this->msgs);
    }
    return $this->surferAdmin;
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result XML
  */
  function getParsedData() {
    $this->setDefaultData();
    $this->setDefaultData(NULL, FALSE, $this->editFieldsMessages);
    $this->setDefaultData(NULL, FALSE, $this->editFieldsCaptions);
    $this->setDefaultData(NULL, FALSE, $this->editFieldRequestMail);
    $result = sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      $this->getXHTMLString($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle encoded="%s">%s</subtitle>'.LF,
      rawurlencode($this->data['subtitle']),
      $this->getXHTMLString($this->data['subtitle'])
    );
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();
    $mailChange = FALSE;
    $mailChangeSuccess = FALSE;
    // For now, simply pipe some data through to XSLT
    // Later, a complete refactoring/cleanup should be done;
    // there is too much stuff in base_surfer.php that should be here etc.
    if (isset($this->data['reg_page']) && $this->data['reg_page'] > 0) {
      $result .= sprintf(
        '<reg-page>%s</reg-page>'.LF,
        $this->getWebLink((int)$this->data['reg_page'])
      );
    }
    $result .= '<captions>'.LF;
    if (!empty($this->data['Caption_Prompt'])) {
      $result .= sprintf(
        '<prompt>%s</prompt>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Prompt'])
      );
    }
    if (!empty($this->data['Caption_Email'])) {
      $result .= sprintf(
        '<email>%s</email>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Email'])
      );
    }
    if (!empty($this->data['Caption_Password'])) {
      $result .= sprintf(
        '<password>%s</password>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Password'])
      );
    }
    if (!empty($this->data['Caption_Password'])) {
      $result .= sprintf(
        '<password_1>%s</password_1>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Password'])
      );
    }
    if (!empty($this->data['Caption_Confirm_Password'])) {
      $result .= sprintf(
        '<password_2>%s</password_2>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Confirm_Password'])
      );
    }
    if (!empty($this->data['Caption_Username'])) {
      $result .= sprintf(
        '<username>%s</username>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Username'])
      );
    }
    if (!empty($this->data['Caption_Reg_Link'])) {
      $result .= sprintf(
        '<reg-link>%s</reg-link>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Reg_Link'])
      );
    }
    if (!empty($this->data['Caption_Passwd_Link'])) {
      $result .= sprintf(
        '<passwd-link>%s</passwd-link>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Passwd_Link'])
      );
    }
    if (!empty($this->data['Caption_Login_Button'])) {
      $result .= sprintf(
        '<login-button>%s</login-button>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Login_Button'])
      );
    }
    if (!empty($this->data['Caption_Save_Button'])) {
      $result .= sprintf(
        '<save-button>%s</save-button>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Save_Button'])
      );
    }
    if (!empty($this->data['Caption_Request_Button'])) {
      $result .= sprintf(
        '<request-button>%s</request-button>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Caption_Request_Button'])
      );
    }
    $result .= '</captions>'.LF;

    // Do we need to verify a registration?
    $validationError = FALSE;
    if (!$this->surferObj->isValid) {
      $registerToken = $this->retrieveData($this->data['register_paramname'].'_id', '');
      if ($registerToken != '') {
        // Check for delayed verification settings
        $checkValidationTime = $this->checkValidationTime($registerToken);
        if ($checkValidationTime == 0) {
          $surferId = $this->makeValid($registerToken);
          if (FALSE !== $surferId) {
            include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
            $surferAdmin = $this->_initSurferAdmin();
            $data = $surferAdmin->getBasicDataById($surferId);
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
          } else {
            $validationError = TRUE;
            $result .= sprintf(
              '<error>%s</error>',
              papaya_strings::escapeHTMLChars($this->data['Error_Verification'])
            );
          }
        } else {
          $validationError = TRUE;
          $result .= sprintf(
            '<error>%s</error>',
            papaya_strings::escapeHTMLChars($this->data['error_validation_time'])
          );
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
            '<text>%s</text>',
            $this->getXHTMLString($textDelay)
          );
        }
      }
    }
    // Do we have to handle an email address change?
    if (isset($_GET['mailchg']) && trim($_GET['mailchg']) != '') {
      $mailChange = TRUE;
      list($mailChangeSuccess, $message, $error) = $this->getMailChange();
      if (isset($message) && strlen($message) > 0) {
        $result .= sprintf(
          '<message>%s</message>',
          papaya_strings::escapeHTMLChars($message)
        );
      }
      if (isset($error) && strlen($error) > 0) {
        $result .= sprintf(
          '<error>%s</error>',
          papaya_strings::escapeHTMLChars($error)
        );
      }
    }
    if ($this->surferObj->isValid && !$mailChange) {
      $this->relocate();
    }
    if ($mailChange && $mailChangeSuccess) {
      if ($this->surferObj->isValid) {
        $result .= sprintf(
          '<mailchange href="%s"/>',
          $this->getWebLink((int)$this->data['topic'])
        );
      } else {
        $result .= sprintf(
          '<message>%s</message>',
          papaya_strings::escapeHTMLChars($this->data['Mail_Changed'])
        );
      }
    } else {
      if (isset($this->params['newpwd'])) {
        if (isset($this->params['reset_by']) && trim($this->params['reset_by']) != '') {
          $resetBy = $this->params['reset_by'];
        } else {
          $resetBy = FALSE;
        }
        $passwordPage = NULL;
        if (isset($this->data['password_page']) && $this->data['password_page'] > 0) {
          $passwordPage = $this->data['password_page'];
        }
        if ($this->surferObj->requestPasswordChange($this->data, $resetBy, $passwordPage)) {
          $result .= '<change-mail-sent/>'.LF;
          $result .= sprintf(
            '<message>%s</message>',
            empty($this->data['Change_Requested'])
              ? 'Change_Requested'
              : papaya_strings::escapeHTMLChars($this->data['Change_Requested'])
          );
          return $result;
        } elseif ($this->params['newpwd'] > 1) {
          $result .= sprintf(
            '<error>%s</error>',
            papaya_strings::escapeHTMLChars($this->data['Unknown_User'])
          );
        }
        $result .= sprintf(
          '<text>%s</text>',
          $this->getXHTMLString($this->data['text_change_password'])
        );
        if (isset($this->data['reset_password']) && trim($this->data['reset_password']) != '') {
          $resetBy = $this->data['reset_password'];
        } else {
          $resetBy = 'email';
        }
        $result .= $this->surferObj->getPasswordRequestForm($resetBy);
        return $result;
      }
      $token = $this->retrieveData($this->surferObj->logformVar.'_chg', NULL);
      if (NULL === $token) {
        if (isset($_POST[$this->surferObj->logformVar]['chg']) &&
            $_POST[$this->surferObj->logformVar]['chg'] != '') {
          $token = $_POST[$this->surferObj->logformVar]['chg'];
        }
        if (NULL === $token) {
          if (isset($_GET[$this->surferObj->logformVar]['chg']) &&
              $_GET[$this->surferObj->logformVar]['chg'] != '') {
            $token = $_GET[$this->surferObj->logformVar]['chg'];
          }
        }
      }
      if ($token != NULL) {
        if ($surferData = $this->surferObj->checkChangePasswordId($token)) {
          $passwordChange = empty($_POST[$this->surferObj->logformVar.'_save'])
            ? ''
            : $_POST[$this->surferObj->logformVar.'_save'];
          if ($passwordChange == '') {
            $passwordChange = empty($_POST[$this->surferObj->logformVar]['save'])
              ? ''
              : $_POST[$this->surferObj->logformVar]['save'];
          }
          $password = '';
          if (isset($_POST[$this->surferObj->logformVar]['password_1'])) {
            $password = $_POST[$this->surferObj->logformVar]['password_1'];
          } elseif (isset($_POST[$this->surferObj->logformVar.'_password_1'])) {
            $password = $_POST[$this->surferObj->logformVar.'_password_1'];
          }
          $passwordControl = '';
          if (isset($_POST[$this->surferObj->logformVar]['password_2'])) {
            $passwordControl = $_POST[$this->surferObj->logformVar]['password_2'];
          } elseif (isset($_POST[$this->surferObj->logformVar.'_password_2'])) {
            $passwordControl = $_POST[$this->surferObj->logformVar.'_password_2'];
          }
          if ($passwordChange) {
            if (checkit::isPassword($password, TRUE)
                && $password == $passwordControl) {
              if ($this->changePassword($surferData, $password)) {
                $this->relocate();
              } else {
                $result .= sprintf(
                  '<error>%s</error>',
                  empty($this->data['Error_Database'])
                    ? 'Error_Database'
                    : papaya_strings::escapeHTMLChars($this->data['Error_Database'])
                );
              }
            } else {
              $result .= sprintf(
                '<error>%s</error>',
                empty($this->data['Input_Error'])
                  ? 'Input_Error'
                  : papaya_strings::escapeHTMLChars($this->data['Input_Error'])
              );
              $result .= $this->surferObj->getPassFormXML($token);
            }
          } else {
            $result .= sprintf(
              '<text>%s</text>',
              $this->getXHTMLString($this->data['text_new_passwords'])
            );
            $result .= $this->surferObj->getPassFormXML($token);
          }
          return $result;
        } else {
          $result .= sprintf(
            '<error>%s</error>',
            empty($this->data['Error_Token'])
              ? 'Error_Token'
              : papaya_strings::escapeHTMLChars($this->data['Error_Token']));
        }
      }

      if (!$validationError) {
        // use a simple template to set login and register links
        $values = array(
          'REGISTER' => sprintf(
            '<a href="%s">%s</a>'.LF,
            $this->getAbsoluteURL((int)$this->data['reg_page']),
            empty($this->data['Caption_Reg_Link'])
              ? ''
              : papaya_strings::escapeHTMLChars($this->data['Caption_Reg_Link'])
          )
        );

        include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
        $template = new base_simpletemplate();
        $text = $template->parse($this->data['text_login'], $values);
        $result .= sprintf(
          '<text>%s</text>',
          $this->getXHTMLString($text)
        );
        // Check whether we've got a redirection parameter
        $redirectionUrl = $this->retrieveData($this->surferObj->logformVar.'_redirection', NULL);
        if (is_numeric($redirectionUrl)) {
          $redirectionUrl = $this->getAbsoluteURL($redirectionUrl);
        } else {
          $redirectionUrl = NULL;
        }
        if (isset($this->data['login_by']) && $this->data['login_by'] == 'handle') {
          $result .= $this->surferObj->getHandleFormXML(
            !($this->surferObj->isValid),
            $redirectionUrl,
            $this->data['Caption_Username'],
            $this->data['Caption_Password'],
            $this->data['Caption_Relogin'],
            array(
              'error_handle' => $this->data['Unknown_User'],
              'error_permissions' => $this->data['Error_Permissions']
            )
          );
        } elseif (isset($this->data['login_by']) && $this->data['login_by'] == 'any') {
          $result .= $this->surferObj->getEmailOrHandleFormXML(
            !($this->surferObj->isValid),
            $redirectionUrl,
            $this->data['Caption_Username_Or_Email'],
            $this->data['Caption_Password'],
            $this->data['Caption_Relogin'],
            array(
              'error_handle' => $this->data['Unknown_User'],
              'error_permissions' => $this->data['Error_Permissions']
            )
          );
        } else {
          $result .= $this->surferObj->getFormXML(
            !($this->surferObj->isValid),
            $redirectionUrl,
            $this->data['Caption_Email'],
            $this->data['Caption_Password'],
            $this->data['Caption_Relogin'],
            array(
              'error_email' => $this->data['Unknown_User'],
              'error_permissions' => $this->data['Error_Permissions']
            )
          );
        }
      }
    }
    return $result;
  }

  /**
  * Password change request
  *
  * @param array &$surferData Surfer
  * @param string $password Password
  *
  * @return boolean
  */
  function changePassword(&$surferData, $password) {
    $surferAdmin = $this->_initSurferAdmin();
    $data = array(
      'surfer_password' => $this->surferObj->getPasswordHash($password)
    );
    $surferAdmin->editSurfer['surfer_id'] = $surferData['surfer_id'];
    if ($surferAdmin->saveSurfer($data)) {
      // Delete corresponding change request
      $condition = array(
        'surferchangerequest_type' => 'passwd',
        'surferchangerequest_surferid' => $surferData['surfer_id']
      );
      $surferAdmin->databaseDeleteRecord(
        $surferAdmin->tableChangeRequests,
        $condition
      );
      // update surfer object
      $this->surferObj->login($surferData['surfer_email'], $password);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Get mail change - check mail address change confirmation and create message.
  *
  * @access public
  * @return array boolean Status, string message and error message
  */
  function getMailChange() {
    if ($this->checkMailConfirm($_GET['mailchg'])) {
      $error = NULL;
      $message = $this->data['Mail_Changed'];
      $result = TRUE;
    } else {
      $error = $this->data['Error_Mail_Change'];
      $message = NULL;
      $result = FALSE;
    }
    return array($result, $message, $error);
  }

  /**
  * Check mail confirmation.
  *
  * @param string $emailConfirmString
  * @access public
  */
  function checkMailConfirm($emailConfirmString) {
    $surferAdmin = $this->_initSurferAdmin();
    $surferEmailNew = '';
    $hash = md5($emailConfirmString);
    $sql = "SELECT surferchangerequest_surferid, surferchangerequest_type,
                   surferchangerequest_data, surferchangerequest_expiry
            FROM %s
            WHERE surferchangerequest_token='%s'";
    $params = array($surferAdmin->tableChangeRequests, $hash);
    if ($res = $surferAdmin->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $changeSurferId = $row['surferchangerequest_surferid'];
        $changeType = $row['surferchangerequest_type'];
        $surferEmailNew = $row['surferchangerequest_data'];
        $expiryTime = $row['surferchangerequest_expiry'];
      }
    }
    // Check the change type for future extensibility ...
    if (isset($changeType) && $changeType == 'email') {
      // Is there an email address and has this change been made before expiry?
      if ($surferEmailNew != '' && time() <= (int)$expiryTime) {
        $data = array(
          'surfer_email' => $surferEmailNew
        );
        $check = $surferAdmin->databaseUpdateRecord(
          $surferAdmin->tableSurfer,
          $data,
          'surfer_id',
          $changeSurferId
        );
        $check = $surferAdmin->databaseDeleteRecord(
          $surferAdmin->tableChangeRequests,
          'surferchangerequest_token',
          $hash
        );
        // if we're logged in, reload data
        if ($this->surferObj->isValid) {
          $this->surferObj->setSessionValue(
            $this->surferObj->surfermailVar,
            $surferEmailNew
          );
          $check = $this->surferObj->loadLogin($surferEmailNew);
          // Send an administrative email
          $this->sendAdminEmail();
        }
      } else {
        return FALSE; // Error: surferchangerequest_token not present or expired
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Forward / Relocation to other pages.
  *
  * @access public
  */
  function relocate() {
    $url = $this->getAbsoluteURL((int)$this->data['topic']);
    $GLOBALS['PAPAYA_PAGE']->sendHTTPStatus(302);
    header('Location: '.$url);
    exit();
  }

  /**
  * Send administrative email.
  *
  * Will send an email to the site administrator
  * (or any email address configured here)
  * if a surfer has successfully changed his or her email address.
  *
  * @access public
  */
  function sendAdminEmail() {
    // Check whether all the necessary data is set
    if (isset($this->data['sender_email']) &&
        isset($this->data['recipient_email']) &&
        isset($this->data['email_subject']) &&
        isset($this->data['email_body'])) {
      include_once (PAPAYA_INCLUDE_PATH.'system/sys_email.php');
      include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
      // Check which fields changed
      $template = new base_simpletemplate();
      $replace = array(
        'USER' => sprintf(
          '%s %s (%s)',
          $this->surferObj->surfer['surfer_givenname'],
          $this->surferObj->surfer['surfer_surname'],
          $this->surferObj->surfer['surfer_handle']),
        'TIME' => date('Y-m-d, H:i', time()),
        'EMAIL' => $this->surferObj->surfer['surfer_email']
      );
      $body = $template->parse($this->data['email_body'], $replace);
      // Send the mail
      $name = (isset($this->data['recipient_name'])) ? $this->data['recipient_name'] : NULL;
      $email = new email();
      if (isset($this->data['sender_name'])) {
        $email->setSender($this->data['sender_email'], $this->data['sender_name']);
      } else {
        $email->setSender($this->data['sender_email']);
      }
      $email->setReturnPath($this->data['sender_email']);
      $email->addAddress($this->data['recipient_email'], $name);
      $email->setSubject($this->data['email_subject']);
      $email->setBody($body);
      $email->send();
    }
  }

  /**
  * Make valid
  *
  * @param string $confirmString
  * @return mixed surfer id on success, FALSE otherwise
  */
  function makeValid($confirmString) {
    $surferAdmin = $this->_initSurferAdmin();
    return $surferAdmin->makeValid($confirmString);
  }

  /**
  * Check validation time
  *
  * @param string $confirmString
  * @return integer 0 on success, remaining time to wait (in seconds) until verification is possible
  */
  function checkValidationTime($confirmString) {
    $surferAdmin = $this->_initSurferAdmin();
    return $surferAdmin->checkValidationTime($confirmString);
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
  * Retrieve raw POST or GET data at once
  *
  * Avoid double code as well as the security issues of $_REQUEST
  *
  * @access public
  * @param string $field name of data field
  * @param mixed $default (optional) default value
  *  */
  function retrieveData($field, $default = '') {
    // Set $value to default value first
    $value = $default;
    if (isset($_POST[$field]) && trim($_POST[$field] != '')) {
      // Use POST value
      $value = $_POST[$field];
    } elseif (isset($_GET[$field]) && trim($_GET[$field] != '')) {
      // Use GET value
      $value = $_GET[$field];
    }
    return $value;
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
    $registerToken = $this->retrieveData($this->data['register_paramname'].'_id', '');
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

}
?>