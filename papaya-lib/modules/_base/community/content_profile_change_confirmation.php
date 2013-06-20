<?php
/**
* Page module - user profiles email confirmation page
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
* @version $Id: content_profile_change_confirmation.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - user profiles email confirmation page
*
* Handle change request confirmation from emails
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class content_profile_change_confirmation extends base_content {
  /**
  * Instance of surfer_admin class
  * @var surfer_admin
  */
  var $surferAdmin = NULL;

  /**
  * Parameter namespace
  * @var string
  */
  var $paramName = 'srp';

  /**
  * Edit fields for page configuration
  * @var string
  */
  var $editFields = array(
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 100, '', 'Confirm email change'),
    'description' => array(
      'Description',
      'isNoHTML',
      TRUE,
      'textarea',
      6,
      'Use {%EMAIL%} placeholder for the new email address.',
      'Please confirm your new email address {%EMAIL%} by entering your password.'
    ),
    'password_required' => array(
      'Password required',
      'isNum',
      TRUE,
      'yesno',
      NULL,
      'Do users need to enter their passwords when logged out?',
      0
    ),
    'Messages',
    'success_message_email_changed' => array(
      'Email changed successfully',
      'isSomeText',
      TRUE,
      'richtext',
      6,
      'Use {%LINK%} for the link',
      'Your email address has been changed.'
    ),
    'error_message_notoken' => array(
      'Invalid token',
      'isSomeText',
      TRUE,
      'richtext',
      6,
      'Use {%LINK%} for the link',
      'Invalid token provided.'
    ),
    'error_message_database' => array(
      'Database error',
      'isSomeText',
      TRUE,
      'richtext',
      6,
      'Use {%LINK%} for the link',
      'Database error'
    ),
    'error_message_password' => array(
      'Password error',
      'isNoHTML',
      TRUE,
      'input',
      100,
      'Use {%LINK%} for the link',
      'Password error'
    ),
    'Links',
    'success_link_email_changed' => array(
      'Target after change',
      'isNum',
      TRUE,
      'pageid',
      10,
      '',
      0
    ),
    'error_link_database' => array(
      'Database error page',
      'isNum',
      TRUE,
      'pageid',
      10,
      '',
      0
    ),
    'error_link_notoken' => array(
      'No token error',
      'isNum',
      TRUE,
      'pageid',
      10,
      '',
      0
    ),
    'Captions',
    'caption_enter_password' => array(
      'Enter password',
      'isNoHTML',
      TRUE,
      'input',
      50,
      '',
      'Enter password'
    ),
    'caption_password' => array(
      'Password',
      'isNoHTML',
      TRUE,
      'input',
      50,
      '',
      'Password'
    ),
    'caption_submit' => array(
      'Submit',
      'isNoHTML',
      TRUE,
      'input',
      50,
      '',
      'Submit'
    )
  );

  /**
  * Initialize surfer_admin instance
  * @return surfer_admin
  */
  function initSurferAdmin() {
    if (!is_object($this->surferAdmin)) {
      include_once(dirname(__FILE__).'/base_surfers.php');
      $this->surferAdmin = new surfer_admin($this->msgs);
    }
    return $this->surferAdmin;
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $result = '';
    $this->setDefaultData();
    $this->initializeParams();
    $redirectTo = 0;
    $token = NULL;
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $surferObj = base_surfer::getInstance();
    if (!$surferObj->isValid && $this->data['password_required'] == 1) {
      $password = NULL;
      if (isset($this->params['mail_token']) && isset($this->params['password'])) {
        $surferAdmin = $this->initSurferAdmin();
        $surferId = $surferAdmin->getIdByToken($this->params['mail_token']);
        if (!empty($surferId)) {
          $surfer = $surferAdmin->loadSurfer($surferId, TRUE);
          if (is_array($surfer) && isset($surfer['surfer_password']) &&
              $surfer['surfer_password'] == $surferAdmin->getPasswordHash(
                $this->params['password'])
             ) {
            $token = $this->params['mail_token'];
          } else {
            $result .= $this->getPasswordDialog($this->params['mail_token'], TRUE);
          }
        }
      }
    }
    if (isset($_GET['mailchg'])) {
      if (!$surferObj->isValid && $this->data['password_required'] == 1) {
        $result .= $this->getPasswordDialog($_GET['mailchg']);
      } else {
        $token = $_GET['mailchg'];
      }
    }
    if ($token) {
      $surferAdmin = $this->initSurferAdmin();
      $email = $surferAdmin->getChangeRequest($token);
      $surferId = $surferAdmin->getIdByToken($token);
      if (!empty($surferId) && !empty($email)) {
        $surferData['surfer_id'] = $surferId;
        $surferData['surfer_email'] = $email;
        if ($surferAdmin->saveSurfer($surferData)) {
          $surferAdmin->deleteChangeRequest($token);
          $result .= $this->getMessageXml(
            $this->data['success_message_email_changed'],
            $this->data['success_link_email_changed'],
            'success'
          );
        } else {
          $result .= $this->getMessageXml(
            $this->data['error_message_database'],
            $this->data['error_link_database']
          );
        }
      } else {
        $result .= $this->getMessageXml(
          $this->data['error_message_notoken'],
          $this->data['error_link_notoken']
        );
      }
    } elseif (!$this->data['password_required']) {
      if (defined('PAPAYA_PAGEID_ERROR_403') &&
          PAPAYA_PAGEID_ERROR_403 > 0 &&
          isset($GLOBALS['PAPAYA_PAGE'])) {
        $GLOBALS['PAPAYA_PAGE']->doRedirect(
          302,
          $this->getAbsoluteUrl($this->getWebLink(PAPAYA_PAGEID_ERROR_403)),
          'Invalid token'
        );
      } else {
        $result .= $this->getMessageXml(
          $this->data['error_message_notoken'],
          $this->data['error_link_notoken']
        );
      }
    }
    return $result;
  }

  /**
  * Get a dialog to enter the password
  * @param string $token The token to change the email address
  * @param boolean $error Has there been a previous input error? Optional, default FALSE
  * @return string dialog XML
  */
  function getPasswordDialog($token, $error = FALSE) {
    $result = sprintf('<title>%s</title>', papaya_strings::escapeHTMLChars($this->data['title']));
    $surferAdmin = $this->initSurferAdmin();
    $email = $surferAdmin->getChangeRequest($token);
    $replace = array('EMAIL' => $email);
    include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
    $template = new base_simpletemplate();
    $description = $template->parse($this->data['description'], $replace);
    $description = papaya_strings::escapeHTMLChars($description);
    $result .= sprintf(
      '<description>%s</description>',
      $description
    );
    if ($error) {
      $result .= sprintf(
        '<error>%s</error>',
        papaya_strings::escapeHTMLChars($this->data['error_message_password'])
      );
    }
    $hidden = array('mail_token' => $token);
    $fields = array(
      'password' => array(
        $this->data['caption_password'],
        'isSomeText',
        TRUE,
        'password',
        30
      )
    );
    $data = array();
    $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    if (is_object($dialog)) {
      $dialog->dialogTitle = papaya_strings::escapeHTMLChars($this->data['caption_enter_password']);
      $dialog->buttonTitle = papaya_strings::escapeHTMLChars($this->data['caption_submit']);
      $result .= $dialog->getDialogXML();
    }
    return $result;
  }

  /**
  * Get XML for messages
  *
  * @param string $message
  * @return string XML
  */
  function getMessageXml($message, $targetPage, $type = 'error') {
    $href = '';
    if ($targetPage != 0) {
      $link = $this->getWebLink($targetPage);
      $href = sprintf(
        ' href="%s"',
        papaya_strings::escapeHTMLChars($link)
      );
      $replace = array('LINK' => $link);
      include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
      $template = new base_simpletemplate();
      $message = $template->parse($message, $replace);
    }
    return sprintf(
      '<message type="%s"%s>%s</message>'.LF,
      papaya_strings::escapeHTMLChars($type),
      $href,
      $this->getXHTMLString($message)
    );
  }
}

?>