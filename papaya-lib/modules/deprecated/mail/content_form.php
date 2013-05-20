<?php
/**
* Page module - configurable feedback formular
*
* Configured by xml-file
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
* @subpackage Free-Mail
* @version $Id: content_form.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* papaya XML-path
* @todo configurable by backend
*/
define('PAPAYA_XMLFILES_PATH', PAPAYA_PATH_DATA.'xml/');

/**
* Page module - configurable feedback formular
*
* Configured by xml-file
*
* @package Papaya-Modules
* @subpackage Free-Mail
* @version usese new email object, mail templates and so on
*/
class content_form extends base_content {

  /**
  * cacheable ?
  * @var boolean $cacheable
  */
  var $cacheable = FALSE;

  /**
  * Content edit fields, settings in onLoad and it's load-functions
  * @var array $editFields
  */
  var $editFields = array();

  /**
  * Input name
  * @var string $inputName
  */
  var $inputName = 'xmldialog';

  /**
  * Error on load?
  * @var boolean $onLoadError
  */
  var $onLoadError = NULL;

  /**
  * Array with errors in input fields
  * @var array $errors
  */
  var $errors = array();

  /**
  * An array with input fields (loaded from xml configuration)
  * @var array
  */
  var $inputFields = array();

  /**
  * Mail dialog is a base_dialog object
  * @var object $mailDialog
  */
  var $mailDialog = NULL;

  /**
  * On load function
  *
  * @access public
  */
  function onLoad() {
    $this->loadInputFields(basename(@$this->data['xmlfile']));
    $this->loadMailEditFields();
    $this->loadPageEditFields();
    $this->loadLookups(basename(@$this->data['xmlfile']));
  }

  /**
  * Loads edit fields with mail specifications
  *
  * @access public
  */
  function loadMailEditFields() {
    $this->editFields['xmlfile'] = array(
      'Form XML File', 'isFile', TRUE, 'filecombo',
      array(PAPAYA_XMLFILES_PATH, '/^\w+\.xml$/i'), '');
    $this->editFields['mail_to'] = array(
      'Mail to', 'isEMail', TRUE, 'input', 60, '', 'webmaster@localhost');
    $this->editFields['mail_from'] = array(
      'Mail from (default)', 'isEMail', TRUE, 'input', 60, '',
      'webmaster@localhost');
    $this->editFields['mail_subject'] = array(
       'Subject', 'isNoHTML', TRUE, 'input', 60, '', 'Feedback from your website');
    $this->editFields['mail_text'] = array(
      'Text', 'isSomeText', FALSE, 'textarea', 6, $this->loadMarkersDescription(),
      'The following data has been submitted... Use the markers.');
    $this->editFields['mail_empty_values'] = array(
      'Replace empty values with:', 'isSomeText', FALSE, 'input', 60, '',
      '-');
  }

  /**
  * Loads edit fields with page specifications
  *
  * @access public
  */
  function loadPageEditFields() {
    $this->editFields[] = 'Page';
    $this->editFields['nl2br'] = array (
      'Automatic linebreak', 'isNum', FALSE, 'combo', array(0 => 'Yes', 1 => 'No'));
    $this->editFields['msg_hello'] = array (
      'Intro', 'isSomeText', FALSE, 'textarea', 6, '', '');
    $this->editFields['msg_error'] = array (
      'Input Error', 'isSomeText', FALSE, 'textarea', 6, '',
      'Please check the following inputs.');
    $this->editFields['msg_send'] = array (
      'Send Message', 'isSomeText', FALSE, 'textarea', 6, '',
      'Your message has been sent.');
    $this->editFields['msg_notsend'] = array (
      'Send error', 'isSomeText', FALSE, 'textarea', 6, '',
      'Message sending failed. Please try again or contact...');
  }

  /**
  * Get description for available markes in content to fillin values later
  *
  * @access public
  * @return string
  */
  function loadMarkersDescription() {
    $desc = 'Markers: ';
    if (isset($this->inputFields) && is_array($this->inputFields)) {
      foreach ($this->inputFields as $key => $val) {
        $desc .= '{%'.strtoupper($key).'%} ';
      }
    }
    return $desc;
  }

  /**
  * Initialize Lookups
  *
  * @param string $fileName filename
  * @access public
  */
  function loadLookups($fileName) {
    if (isset($this->inputFields) && is_array($this->inputFields)) {
      $this->editFields[] = 'LookUps';
      foreach ($this->inputFields as $key => $val) {
        if (preg_match('/^lookup_/', $val[3])) {
          $name = 'lookup_'.$val[4];
          $element = array ($val[0], 'isSomeText', FALSE, 'textarea', 6);
          $this->editFields[$name] = $element;
        }
      }
    }
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->initializeParams();
    $this->initializeMailDialog();
    $this->onLoadError = TRUE;
    $this->onLoad();
    $result = '';
    $result .= sprintf(
      '<title>%s</title>', @$this->getXHTMLString(@$this->data['title'])
    );
    $result .= sprintf(
      "<text>%s</text>",
      @$this->getXHTMLString($this->data['msg_hello'], !((bool)@$this->data['nl2br']))
    );
    if ($this->onLoadError) {
      $result .= '<message type="error">'.LF;
        $result .= @$this->getXHTMLString($this->data['msg_error']);
      $result .= '</message>'.LF;
    } elseif (isset($this->params['send']) &&
                trim($this->params['send']) == 1) {
      if ($this->checkMailDialogInput()) {
        if ($this->sendEmail()) {
          $result .= sprintf(
            '<message type="normal">%s</message>'.LF,
            @$this->getXHTMLString($this->data['msg_send'])
          );
        } else {
          $result .= sprintf(
            '<message type="warning">%s</message>'.LF,
            @$this->getXHTMLString($this->data['msg_notsend'])
          );
        }
      } elseif ($this->errors) {
        $result .= '<message type="error">'.LF;
        $result .= @$this->getXHTMLString($this->data['msg_error']);
        if (is_array($this->errors) && count($this->errors > 0)) {
          $result .= '<ul>'.LF;
          foreach ($this->errors as $fieldKey => $fieldError) {
            if ($fieldError == 1) {
              $result .= sprintf(
                '<li>%s</li>'.LF,
                papaya_strings::escapeHTMLChars($this->inputFields[$fieldKey][0])
              );
            }
          }
          $result .= '</ul>'.LF;
        }
        $result .= '</message>'.LF;
        $result .= $this->getMailDialog();
      } else {
        $result .= '<message type="error">'.LF;
          $result .= @$this->getXHTMLString($this->data['msg_notsend']);
        $result .= '</message>'.LF;
        $result .= $this->getMailDialog();
      }
    } else {
      $result .= $this->getMailDialog();
    }
    $result .= sprintf(
      '<privacy>%s</privacy>',
      @$this->getXHTMLString($this->data['msg_privacy'])
    );

    return $result;
  }

  /**
  * Get parameter name
  *
  * @param string $paramName
  * @access public
  * @return string
  */
  function getParamName($paramName) {
    return $paramName.'_cof';
  }

  /**
  * Get content for email body text from $this->params array
  *
  * @access public
  * @return array $content
  */
  function getBodyContent() {
    $content = array();
    foreach ($this->inputFields as $key => $val) {
      $upperKey = strtoupper($key);
      $content[$upperKey] = (@$this->data['mail_empty_values']) ?
        @$this->data['mail_empty_values'] : "";
      if (isset($this->params[$key]) && @trim($this->params[$key])) {
        if ($this->params[$key] == "on" &&
             ($this->inputFields[$key][3] == "checkbox")) {
          $data = $this->inputFields[$key][0];
        } elseif (is_array($this->params[$key]) &&
                    (count($this->params[$key]) > 0)) {
          $data = implode(', ', $this->params[$key]);
        } elseif (substr_count($this->params[$key], "\n") > 0) {
          $data = "\n\n".$this->params[$key].LF;
        } elseif (trim($this->params[$key])) {
          $data = trim($this->params[$key]);
        }
        if (isset($data)) {
          $content[$upperKey] = $data;
        }
      }
    }
    return $content;
  }

  /**
  * Send email
  *
  * @access public
  * @return boolean
  */
  function sendEmail() {
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
    $email = new email();
    if (@$content = $this->getBodyContent()) {
      $sender = (@isset($this->params['email']) && !@$this->errors['email']) ?
        @$this->params['email'] : @$this->data['mail_from'];
      $email->setSender($sender);
      $email->addAddress(@$this->data['mail_to']);
      $email->setSubject(@$this->data['mail_subject']);
      $email->setBody(@$this->data['mail_text'], $content);
      if ($email->send()) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Check box input
  *
  * @access public
  * @return mixed FALSE or array error
  */
  function checkMailDialogInput() {
    if ($this->mailDialog->checkDialogInput()) {
      if ($this->dialogData = $this->mailDialog->data) {
        return TRUE;
      }
    } elseif ($this->mailDialog->checkDialogToken()) {
      $this->errors = $this->mailDialog->inputErrors;
    }
    return FALSE;
  }

  /**
  * Return box formular
  *
  * @access public
  * @return string XML of dialog
  */
  function getMailDialog() {
    return $this->mailDialog->getDialogXML();
  }

  /**
  * Initialize box formular
  *
  * @access public
  */
  function initializeMailDialog() {
    if (!@is_object($this->dialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array('send' => 1);
      $data = NULL;
      $this->mailDialog = new base_dialog(
        $this, $this->paramName, $this->inputFields, $data, $hidden
      );
      $this->mailDialog->loadParams();
      $this->mailDialog->inputFieldSize = $this->inputFieldSize;
      $this->mailDialog->baseLink = $this->baseLink;
    }
  }

  /**
  * Load input fields
  *
  * @access public
  * @param string $fileName filename
  */
  function loadInputFields($fileName) {
    unset($this->inputFields);
    if (file_exists(PAPAYA_XMLFILES_PATH.$fileName) && ($fileName != '')) {
      include_once(PAPAYA_INCLUDE_PATH.'system/sys_simple_xmltree.php');
      $xmlTree = &simple_xmltree::createFromXML(
        file_get_contents(PAPAYA_XMLFILES_PATH.$fileName), $this
      );
      if ($xmlTree) {
        $this->readInputFields($xmlTree);
        simple_xmltree::destroy($xmlTree);
      }
    }
  }

  /**
  * Read input fields
  *
  * @access public
  * @param reference &$xmlTree XML-tree
  */
  function readInputFields(&$xmlTree) {
    if (isset($xmlTree) &&
        isset($xmlTree->documentElement) &&
        $xmlTree->documentElement->hasChildNodes()) {
      for ($idx = 0; $idx < $xmlTree->documentElement->childNodes->length; $idx++) {
        $dialogNode = &$xmlTree->documentElement->childNodes->item($idx);
        if ($dialogNode->nodeType == XML_ELEMENT_NODE
            && $dialogNode->nodeName == 'dialog' && $dialogNode->hasChildNodes()) {
          $this->inputName = $dialogNode->getAttribute('name');
          for ($idx2; $idx2 < $dialogNode->childNodes->length; $idx2++) {
            $fieldNode = &$dialogNode->childNodes->item($idx2);
            if ($fieldNode->nodeType == XML_ELEMENT_NODE && $fieldNode->nodeName == 'element' &&
                $fieldNode->hasAttribute('name')) {
              $this->inputFields[$fieldNode->getAttribute('name')] = array(
                $node->getAttribute('caption'),
                $node->getAttribute('check'),
                $node->getAttribute('needed'),
                $node->getAttribute('type'),
                $node->getAttribute('typeparam'),
                $node->getAttribute('hint'),
                $node->getAttribute('default'),
                $node->getAttribute('align')
              );
              $this->onLoadError = FALSE;
            }
          }
          break;
        }
      }
    }
  }
}

?>
