<?php
/**
* Box Module Mailman
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
* @subpackage Beta-Mailman
* @version $Id: actbox_mailman.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* base class page modules
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Box Module Mailman
*
* @package Papaya-Modules
* @subpackage Beta-Mailman
*/
class actbox_mailman extends base_actionbox {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array ('Automatic linebreaks', 'isNum', TRUE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0),
    'text' => array ('Text', 'isSomeText', FALSE, 'richtext', 10, '', ''),
    'emailtext' => array ('Email Field Text', 'isNoHTML', FALSE, 'input', 400, '', ''),
    'subscribe' => array ('Subscribe', 'isNoHTML', FALSE, 'input', 400, '', ''),
    'submit' => array ('Submit', 'isNoHTML', FALSE, 'input', 400, '',
      'Text on submit button.'),
    'Mailman interface',
    'mode' => array('Mode', 'isNum', TRUE, 'combo', array(0=>'Email', 1=>'Shell'), '', 0),
    'email' => array('Email', 'isEMail', FALSE, 'input', 100, 'request@domain.tld', ''),
    'binary' => array('Binary', 'isFile', FALSE, 'input', 100, '', ''),
    'web' => array('Web', 'isHTTPX', TRUE, 'input', 150, '', ''),
    'Messages',
    'success' => array('Success', 'isSomeText', FALSE, 'textarea', 5, '',
      'An email containing further information was sent to you.'),
    'Error Messages',
    'wrong_email' => array('Wrong Email', 'isSomeText', FALSE, 'textarea', 5, '',
      'Please enter a valid email address.'),
    'email_failed' => array('Sending email failed', 'isSomeText', FALSE, 'textarea', 5, '',
      'The Email could not be sent.'),
  );

  /**
  * Selected item
  * @var array $selectedItem
  */
  var $selectedItem = NULL;
  /**
  * Is modified
  * @var boolean $modified
  */
  var $modified = FALSE;
  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'mm';
  /**
  * Process errors
  * @var array $processErrors
  */
  var $processErrors = NULL;
  /**
  * Is submitted
  * @var boolean $isSubmitted
  */
  var $isSubmitted = FALSE;

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result XML
  */
  function getParsedData() {
    $this->executeOutput();
    if (!$this->isSubmitted || isset($this->processErrors)) {
      $result = $this->getMailForm();
      if (is_array($this->processErrors)) {
        $errors = '';
        foreach ($this->processErrors as $error) {
          $errors .= sprintf('<li>%s</li>'.LF, $error);
        }
        $result .= sprintf(
          '<error><ul class="mailmanErrors">%s</ul></error>'.LF,
          papaya_strings::escapeHTMLChars($errors)
        );
      }
    } else {
      $result .= sprintf(
        '<message>%s</message>'.LF,
        papaya_strings::escapeHTMLChars($this->data['success'])
      );
    }
    return $result;
  }


  /**
  * evaluates user input and triggers action
  *
  * @access public
  */
  function executeOutput() {
    $this->initializeParams();
    if (is_array($this->params) && isset($this->params['submitted'])) {
      $this->isSubmitted = TRUE;
      if ($this->params['email'] == '' || !checkit::isEmail($this->params['email'])) {
        $this->processErrors[] = $this->data['wrong_email'];
      }
      if (!isset($this->processErrors)) {
        $this->request($this->params['email'], $this->params['action'], $this->data['email']);
        return;
      }
    }
  }

  /**
  * triggers request to be send by email
  *
  * @param string $requestEmail email address to be operated on by request
  * @param string $toList name of list to send request to
  * @param string $action subscribe or unsubscribe to/from list
  * @param string $listEmail emailaddress of list e.g. mailman@somedomain.org
  * @access public
  */
  function request($requestEmail, $action, $listEmail) {
    $headers = '';
    switch ($action) {
    case 'subscribe':
      $subject = sprintf('subscribe address=%s', $requestEmail);
      break;
    case 'unsubscribe':
      $subject = sprintf('unsubscribe address=%s', $requestEmail);
      break;
    default:
      $this->processErrors[] = 'No action given.';
      break;
    }
    $to = $listEmail;
    if (!mail($to, $subject, '', $headers)) {
      $this->processErrors[] = 'Sending subscription email failed.';
    }
  }

  /**
  * checks if form can be created (lists exist) and generates it,
  * otherwise an error is reported and FALSE is returned.
  *
  * @access public
  * @return string $mailForm mail form XML
  */
  function getMailForm() {
    // info text
    $mailForm = sprintf(
      "<newsletter><text>%s</text>".LF,
      $this->getXHTMLString($this->data['text'], !((bool)@$this->data['nl2br']))
    );
    //  mail form header
    $mailForm .= '<mailform>'.LF;
    $mailForm .= sprintf(
      '<form action="%s?%s[subscribe]">'.LF,
      papaya_strings::escapeHTMLChars($this->baseLink),
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $mailForm .= sprintf(
      '<input description="%s" type="email" name="%s[email]" value="%s" />'.LF,
      papaya_strings::escapeHTMLChars($this->data['emailtext']),
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->params['email'])
    );

    // mail form footer
    $mailForm .= sprintf(
      '<input type="hidden" name="%s[submitted]" value="TRUE" />'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $mailForm .= sprintf(
      '<input type="hidden" name="%s[action]" value="subscribe" />'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $mailForm .= sprintf(
      '<btnSubmit name="%s[submit]" value="%s" />'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->data['submit'])
    );
    $mailForm .= '</form>'.LF;
    $mailForm .= '</mailform></newsletter>';
    return $mailForm;
  }

  /**
  * Initialization
  *
  * @access public
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.get_class($this).'_'.$this->paramName;
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('contentmode', 'cmd');
  }

}
?>