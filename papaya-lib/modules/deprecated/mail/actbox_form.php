<?php
/**
* Action box email formular
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
* @subpackage Free-Mail
* @version $Id: actbox_form.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* papaya box xmlfiles path
*/
define('PAPAYA_BOXXMLFILES_PATH', PAPAYA_PATH_DATA.'xml/');

/**
* Action box email formular
*
* @package Papaya-Modules
* @subpackage Free-Mail
*/
class actionbox_form extends base_actionbox {

  /**
  * Content edit fields
  *
  * @access public
  * @var array $editFields
  */
  var $editFields = array(
    'xmlfile' => array('XML-Form', 'isFile', TRUE, 'filecombo',
      array(PAPAYA_XMLFILES_PATH, '/^\w+\.xml$/i'), ''),
    'mailto' => array('Recipient', 'isEMail', TRUE, 'input', 60, '',
      'webmaster@localhost'),
    'subject' => array('Subject', 'isNoHTML', TRUE, 'input', 60, '',
      'Feeback from web page'),
    'mailtext' => array('E-Mail text', 'isSomeText', FALSE, 'textarea', 6, '',
      'Short feedback from web page'),
    'Messages',
    'msg_hello' => array ('Hello', 'isSomeText', FALSE, 'textarea', 6, '', ''),
    'msg_error' => array ('Input error', 'isSomeText', FALSE, 'textarea', 6, '',
      'Please check your inputs.'),
    'msg_send' => array ('Confirmation', 'isSomeText', FALSE, 'textarea', 6, '',
      'Message sent. Thanks.'),
    'msg_notsend' => array ('Sending error', 'isSomeText', FALSE, 'textarea', 6, '',
      'Sending error. Please try again.')
  );

  /**
  * On load
  *
  * @access public
  */
  function onLoad() {
    $this->initializeLookups(basename($this->data['xmlfile']));
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string $xmlData
  */
  function getParsedData() {
    $xmlData = '<box>'.LF;
    if ($this->params['send']) {
      if ($error = $this->checkBoxInput()) {
        $xmlData .= $this->getBoxForm();
        $xmlData .= sprintf(
          '<text>%s <ul>%s</ul></text>'.LF,
          $this->getXHTMLString($this->data['msg_error'], TRUE),
          '<li class="boxtext">'.implode('</li><li class="boxtext">', $error).'</li>'
        );
      } else {
        if ($this->sendEmail()) {
          $xmlData .= sprintf(
            '<text>%s</text>'.LF,
            $this->getXHTMLString($this->data['msg_send'], TRUE)
          );
        } else {
          $xmlData .= sprintf(
            '<text>%s</text>'.LF,
            $this->getXHTMLString($this->data['msg_notsend'], TRUE)
          );
        }
      }
    } else {
      $xmlData .= $this->getBoxForm();
      $xmlData .= sprintf(
        '<text>%s</text>'.LF,
        $this->getXHTMLString($this->data['msg_hello'], TRUE)
      );
    }
    $xmlData .= '</box>'.LF;
    return $xmlData;
  }

  /**
  * Get parameter name
  *
  * @param string $paramName
  * @access public
  * @return string
  */
  function getParamName($paramName) {
    return $paramName.'_abf';
  }

  /**
  * Send mail
  *
  * @access public
  * @return integer
  */
  function sendEmail() {
    $msg = $this->data['mailtext']."\n\n";
    foreach ($this->inputFields as $key => $val) {
      $msg .= $val[0].": \t\t".$this->params[$key].LF;
    }
    $result = mail($this->data['mailto'], $this->data['subject'], $msg);
    return $result;
  }

  /**
  * initialize Lookups
  *
  * @access public
  * @param string $fileName filename
  */
  function initializeLookups($fileName) {
    $this->loadInputFields(basename($fileName));
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
  * Initialize box formular
  *
  * @access public
  */
  function initializeBoxForm() {
    if (!@is_object($this->dialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array('send' => 1);
      $data = NULL;
      $this->boxForm = new base_dialog(
        $this, $this->paramName, $this->inputFields, $data, $hidden
      );
      $this->boxForm->loadParams();
      $this->boxForm->inputFieldSize = $this->inputFieldSize;
      $this->boxForm->baseLink = $this->baseLink;
    }
  }

  /**
  * Return box formular
  *
  * @access public
  * @return string XML des Dialoges
  */
  function getBoxForm() {
    $this->initializeBoxForm();
    return $this->boxForm->getDialogXML();
  }

  /**
  * Check box input
  *
  * @access public
  * @return mixed FALSE or array error
  */
  function checkBoxInput() {
    $this->initializeBoxForm();
    if ($result = $this->boxForm->checkDialogInput()) {
      $this->boxData = $this->boxForm->data;
      return FALSE;
    } else {
      return $this->boxForm->inputErrors;
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
    if (file_exists(PAPAYA_BOXXMLFILES_PATH.$fileName) && ($fileName != '')) {
      include_once(PAPAYA_INCLUDE_PATH.'system/sys_simple_xmltree.php');
      $xmlTree = &simple_xmltree::createFromXML(
        file_get_contents(PAPAYA_BOXXMLFILES_PATH.$fileName), $this
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
            }
          }
          break;
        }
      }
    }
  }
}
?>
