<?php
/**
* Actionbox - Login form
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
* @version $Id: actbox_login.php 36798 2012-03-06 15:20:52Z kersken $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');
/**
* Surfer class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');

/**
* Actionbox - Login form
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class actionbox_login extends base_actionbox {

  /**
  * More detailed cache dependencies
  * @var array $cacheDependency
  */
  var $cacheDependency = array(
    'querystring' => FALSE,
    'page' => FALSE,
    'surfer' => TRUE
  );

  /**
  * Preview allowed?
  * @var boolean $preview
  */
  var $preview = FALSE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'login_id' => array(
      'Page after login',
      'isNum',
      FALSE,
      'pageid',
      10,
      'Redirect to this page after login (optional).',
      0
    ),
    'logout_id' => array(
      'Page after logout',
      'isNum',
      FALSE,
      'pageid',
      10,
      'Redirect to this page after logout (optional).',
      0
    ),
    'login_page' => array(
      'Login page',
      'isNum',
      FALSE,
      'pageid',
      10,
      'Optional link to the page where surfers can retrieve forgotten passwords.',
      0
    ),
    'register_page' => array(
      'Registration page',
      'isNum',
      FALSE,
      'pageid',
      10,
      'Optional link to the page were unregistered users can register.',
      0
    ),
    'profile_page' => array(
      'Profile page',
      'isNum',
      FALSE,
      'pageid',
      10,
      'Optional link to the logged in surfers\' profile pages.',
      0
    ),
    'error_report' => array(
      'Report login errors',
      'isNum',
      TRUE,
      'yesno',
      NULL,
      '',
      0
    ),
    'delegate_errors' => array(
      'Delegate errors to login page',
      'isNum',
      TRUE,
      'yesno',
      NULL,
      'Only if Report login errors is off and a login page is set',
      0
    ),
    'Messages',
    'title_login' => array(
      'Login title',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Login'
    ),
    'title_logout' => array(
      'Logout title',
      'isNoHTML',
      FALSE,
      'input',
      100,
      'Optional (if not set, the login title will be used for logout as well)'
    ),
    'text_login' => array('Login text', 'isNoHTML', TRUE, 'textarea', 5, '',
      'Please log in'),
    'text_logout' => array('Logout text', 'isNoHTML', TRUE, 'textarea', 5, '',
      'You are logged in as'),
    'text_register' => array('Registration text', 'isNoHTML', TRUE,
      'textarea', 5, '', 'Please register'),
    'input_error_title' => array('Input error title', 'isNoHTML', TRUE, 'input', 200, '',
    	'An error was occurred.'),
    'input_error' => array('Input error', 'isNoHTML', TRUE, 'input', 200, '',
      'Email and/or password invalid.'),
    'Link captions',
    'linktext_lostpassword' => array(
      'Lost password',
      'isNoHTML',
      FALSE,
      'input',
      50,
      '',
      'Forgot your password?'
    ),
    'linktext_register' => array(
      'Register',
      'isNoHTML',
      FALSE,
      'input',
      50,
      '',
      'Register'
    ),
    'Other captions',
    'caption_email' => array('Email', 'isAlphaNumChar', TRUE, 'input', 50, '',
      'E-Mail'),
    'caption_password' => array('Password', 'isAlphaNumChar', TRUE, 'input', 50, '',
      'Password'),
    'caption_login_button' => array('Login button', 'isAlphaNumChar', TRUE,
      'input', 50, '', 'Login'),
    'caption_logout_button' => array('Logout button', 'isAlphaNumChar', TRUE,
      'input', 50, '', 'Logout')
  );

  /**
  * Get parsed data, generate login or logout dialog
  *
  * @access public
  * @return string $result XML
  */
  function getParsedData() {
    $this->setDefaultData();
    $surferObj = $this->papaya()->surfer;
    $result = '<loginbox>';
    $title = $this->data['title_login'];
    if ($surferObj->isValid && isset($this->data['title_logout']) &&
        trim($this->data['title_logout']) != '') {
      $title = $this->data['title_logout'];
    }
    $result .= sprintf('<title>%s</title>', papaya_strings::escapeHTMLChars($title));

    if ($surferObj->isValid) {
      $logoutId = empty($this->data['logout_id']) ? 0 : (int)$this->data['logout_id'];
      if ($logoutId > 0) {
        $logoutRedirectionLink = $logoutId;
      } else {
        $logoutRedirectionLink = $this->getWebLink(
          NULL,
          NULL,
          NULL,
          isset($this->parentObj->moduleObj->params) ?
            $this->parentObj->moduleObj->params : NULL,
          isset($this->parentObj->moduleObj->paramName) ?
            $this->parentObj->moduleObj->paramName : NULL,
          !empty($this->parentObj->topic['TRANSLATION']['topic_title']) ?
            $this->parentObj->topic['TRANSLATION']['topic_title'] : NULL
        );
      }
      $result .= $surferObj->getFormXML(
        !($surferObj->isValid),
        $this->getAbsoluteURL($logoutRedirectionLink),
        papaya_strings::escapeHTMLChars($this->data['caption_email']),
        papaya_strings::escapeHTMLChars($this->data['caption_password'])
      );
    } else {
      $loginId = empty($this->data['login_id']) ? 0 : (int)$this->data['login_id'];
      if ($loginId > 0) {
        $loginRedirectionLink = $loginId;
      } else {
        $loginRedirectionLink = $this->getWebLink(
          NULL,
          NULL,
          NULL,
          isset($this->parentObj->moduleObj->params) ?
            $this->parentObj->moduleObj->params : NULL,
          isset($this->parentObj->moduleObj->paramName) ?
            $this->parentObj->moduleObj->paramName : NULL,
          !empty($this->parentObj->topic['TRANSLATION']['topic_title']) ?
            $this->parentObj->topic['TRANSLATION']['topic_title'] : NULL
        );
      }
      $result .= $surferObj->getFormXML(
        !($surferObj->isValid),
        $this->getAbsoluteURL($loginRedirectionLink),
        papaya_strings::escapeHTMLChars($this->data['caption_email']),
        papaya_strings::escapeHTMLChars($this->data['caption_password'])
      );
    }

    if ($surferObj->isValid) {
      $result .= sprintf(
        '<logout_text>%s</logout_text>'.LF,
        $this->getXHTMLString($this->data['text_logout'], TRUE)
      );
      $result .= sprintf(
        '<logout_button>%s</logout_button>'.LF,
        papaya_strings::escapeHTMLChars($this->data['caption_logout_button'])
      );
      $profilePage = 0;
      if ($this->data['profile_page'] > 0) {
        $profilePage = $this->data['profile_page'];
      } elseif ($surferObj->surfer['surfergroup_profile_page'] > 0) {
        $profilePage = $surferObj->surfer['surfergroup_profile_page'];
      }
      if ($profilePage > 0) {
        $profileLink = $this->getWebLink($profilePage);
        $result .= sprintf(
          '<link type="profile" href="%s" />'.LF,
          papaya_strings::escapeHTMLChars($profileLink)
        );
      }
    } elseif (!($surferObj->isValid)) {
      $result .= sprintf(
        '<login_text>%s</login_text>'.LF,
        $this->getXHTMLString($this->data['text_login'], TRUE)
      );
      $result .= sprintf(
        '<login_button>%s</login_button>'.LF,
        papaya_strings::escapeHTMLChars($this->data['caption_login_button'])
      );
      if ($this->data['login_page'] > 0 &&
          isset($this->data['linktext_lostpassword']) &&
          trim($this->data['linktext_lostpassword']) != '') {
        $result .= sprintf(
          '<link type="lost-password" href="%s" caption="%s"/>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink(
              $this->data['login_page'],
              NULL,
              NULL,
              array('newpwd' => 1),
              'surf'
            )
          ),
          papaya_strings::escapeHTMLChars($this->data['linktext_lostpassword'])
        );
      }
      if ($this->data['register_page'] > 0 &&
        isset($this->data['linktext_register']) &&
        trim($this->data['linktext_register']) != '') {
        $result .= sprintf(
          '<register_text>%s</register_text>'.LF,
          $this->getXHTMLString($this->data['text_register'], TRUE)
        );
        $result .= sprintf(
          '<link type="register" href="%s" caption="%s"/>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink($this->data['register_page'])
          ),
          papaya_strings::escapeHTMLChars($this->data['linktext_register'])
        );
      }
    }
    if (!($surferObj->isValid)) {
      $check = (
        isset($_POST[$surferObj->logformVar.'_email']) &&
        isset($_POST[$surferObj->logformVar.'_password'])
      ) || (
        isset($_POST[$surferObj->logformVar]['email']) &&
        isset($_POST[$surferObj->logformVar]['password'])
      );
      if ($check) {
        if (isset($this->data['error_report']) && $this->data['error_report'] > 0) {
          $result .= sprintf(
            '<login_error title="%s">%s</login_error>'.LF,
          	empty($this->data['input_error_title']) ?
          		'' : papaya_strings::escapeHTMLChars($this->data['input_error_title']),
            empty($this->data['input_error']) ?
              '' : papaya_strings::escapeHTMLChars($this->data['input_error'])
          );
        } elseif (isset($this->data['delegate_errors']) && isset($this->data['login_page']) &&
            $this->data['login_page'] > 0 && $this->parentObj != $this->data['login_page']) {
          $redirectionURL = $this->getAbsoluteURL(
            $this->getWebLink(
              $this->data['login_page'],
              NULL,
              NULL,
              array('error' => 1),
              $surferObj->logformVar
            )
          );
          $GLOBALS['PAPAYA_PAGE']->protectedRedirect(302, $redirectionURL);
          exit();
        }
      }
    }

    $result .= '</loginbox>';
    return $result;
  }

  /**
  * Get parsed attributes
  *
  * @return array 
  */
  function getParsedAttributes() {
    $result = array();
    $surferObj = $this->papaya()->surfer;
    if ($surferObj->isValid) {
      $data = $surferObj->surfer;
      $fields = array(
        'surfer_handle',
        'surfer_email',
        'surfer_givenname',
        'surfer_surname',
        'surfer_gender',
        'surfergroup_id',
        'surfergroup_title'
      );
      foreach ($fields as $field) {
        if (isset($data[$field])) {
          $result[$field] = $data[$field];
        }
      }
    }
    return $result;
  }
}
