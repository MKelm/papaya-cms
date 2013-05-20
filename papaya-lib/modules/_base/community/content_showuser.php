<?php
/**
* Page module - Show user data
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
* @version $Id: content_showuser.php 37711 2012-11-23 13:16:05Z smekal $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - Show user data
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class content_showuser extends base_content {

  /**
  * Is cacheable?
  * @var boolean
  */
  var $cacheable = FALSE;

  /**
  * Edit fields
  * @var array
  */
  var $editFields = array(
    'msg_prefix' => array (
      'Subject prefix', 'isNoHTML', TRUE, 'input', 30, '', PAPAYA_PROJECT_TITLE
    ),
    'Messages',
    'Unknown_User' => array (
      'Unknown user',
      'isNoHTML',
      TRUE,
      'input',
      200,
      '',
      'Unknown user'
    ),
    'Input_Error' => array (
      'Input error',
      'isNoHTML',
      TRUE,
      'input',
      200,
      '',
      'Input error'
    ),
    'Message_Sent' => array (
      'Message sent',
      'isNoHTML',
      TRUE,
      'input',
      200,
      '',
      'Message sent'
    ),
    'Send_Error' => array (
      'Send error',
      'isNoHTML',
      TRUE,
      'input',
      200,
      '',
      'Send error'
    )
  );

  /**
  * Get parsed data
  *
  * @todo Use papaya's mail object to send emails
  * @access public
  * @return string $result Content XML
  */
  function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $this->baseLink = $this->getBaseLink();
    include_once(dirname(__FILE__).'/base_surfers.php');
    $surferAdmin = new surfer_admin($this->msgs);
    $result = '';
    $userId = '';
    $userName = $this->_getParam(array('user_name', 'user_handle', 'surfer_handle'), '');
    if ($userName != '') {
      $userId = $surferAdmin->getIdByHandle($userName);
    }
    if ($userId != '') {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
      $this->surferObj = &base_surfer::getInstance();
      $surferAdmin->loadSurfer($userId);
      $result .= sprintf(
        '<userdata id="%d">', $surferAdmin->editSurfer['surfer_id']
      );
      $result .= sprintf(
        '<%1$s><![CDATA[%2$s]]></%1$s>'.LF,
        'handle',
        papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfer_handle'])
      );
      $result .= sprintf(
        '<%1$s><![CDATA[%2$s %3$s]]></%1$s>'.LF,
        'name',
        papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfer_givenname']),
        papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfer_surname'])
      );
      $result .= sprintf(
        '<%1$s href="mailto:%3$s"><![CDATA[%2$s]]></%1$s>'.LF,
        'email',
        papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfer_email']),
        papaya_strings::escapeHTMLChars(
          urlencode($surferAdmin->editSurfer['surfer_email'])
        )
      );
      $result .= sprintf(
        '<%1$s id="%3$d"><![CDATA[%2$s]]></%1$s>'.LF,
        'group',
        papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfergroup_title']),
        $surferAdmin->editSurfer['surfergroup_id']
      );
      $this->initFeedbackForm($surferAdmin->editSurfer['surfer_id']);
      $showForm = TRUE;
      if (isset($this->params['cmd']) && $this->params['cmd'] == 'mail') {
        if ($this->dialogFeedback->checkDialogInput()) {
          $to = $surferAdmin->editSurfer['surfer_givenname'].' '.
            $surferAdmin->editSurfer['surfer_surname'].' <'.
            $surferAdmin->editSurfer['surfer_email'].'>';
          $from = $this->surferObj->surfer['surfer_givenname'].' '.
            $this->surferObj->surfer['surfer_surname'].' <'.
            $this->surferObj->surfer['surfer_email'].'>';
          $subject = (trim(@$this->data['msg_prefix']) != '') ? '['.
            $this->data['msg_prefix'].'] ' : '';
          $subject .= $this->params['subject'];
          $msg = $this->params['text'];
          $addHeader = 'From: '.$from."\r\n";
          $addHeader .= 'Content-type: text/plain; charset="ISO-8859-1"';
          if (@mail($to, $subject, $msg, $addHeader)) {
            $result .= sprintf(
              '<message>%s</message>'.LF,
              papaya_strings::escapeHTMLChars($this->data['Message_Sent'])
            );
            $showForm = FALSE;
          } else {
            $result .= sprintf(
              '<message>%s</message>'.LF,
              papaya_strings::escapeHTMLChars($this->data['Send_Error'])
            );
          }
        } else {
          $result .= sprintf(
            '<message>%s</message>'.LF,
            papaya_strings::escapeHTMLChars($this->data['Input_Error'])
          );
        }
      }
      if ($showForm) {
        $result .= $this->getFeedbackForm($surferAdmin->editSurfer['surfer_id']);
      }
    } else {
      $result .= '<userdata>';
      $result .= sprintf(
        '<message>%s</message>'.LF,
        papaya_strings::escapeHTMLChars($this->data['Unknown_User'])
      );
    }
    $result .= '</userdata>';
    return $result;
  }

  /**
  * Initialize feedback form
  *
  * @param integer $userId
  * @access public
  */
  function initFeedbackForm($userId) {
    if (!(isset($this->dialogFeedback) && is_object($this->dialogFeedback))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $fields = array(
        'subject' => array('SUBJECT', 'isSomeText', TRUE, 'input', 50, '', ''),
        'text' => array('MESSAGE', 'isSomeText', TRUE, 'textarea', 5, '', '')
      );
      $hidden = array('cmd' => 'mail', 'save' => 1, 'user_id' => $userId);
      $data = array();
      $this->dialogFeedback = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->dialogFeedback->msgs = &$this->msgs;
      $this->dialogFeedback->loadParams();
    }
  }

  /**
  * Get feedback form
  *
  * @param integer $userId Unique 32-char surfer / user id
  * @access public
  * @return string Dialog / Form XML
  */
  function getFeedbackForm($userId) {
    $this->initFeedbackForm($userId);
    $this->dialogFeedback->baseLink = $this->baseLink;
    $this->dialogFeedback->dialogDoubleButtons = FALSE;
    return $this->dialogFeedback->getDialogXML();
  }
  /**
  * Internal helper method to get both plain parameters and those from $this->params
  *
  * @access private
  * @param mixed string|array $params
  * @param mixed $default optional, default NULL
  * @return mixed
  */
  function _getParam($params, $default = NULL) {
    if (!is_array($params)) {
      $params = array($params);
    }
    foreach ($params as $param) {
      if (isset($this->params[$param])) {
        return $this->params[$param];
      }
      if (isset($_POST[$param])) {
        return $_POST[$param];
      }
      if (isset($_GET[$param])) {
        return $_GET[$param];
      }
    }
    return $default;
  }
}

?>