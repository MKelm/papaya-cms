<?php
/**
* Online Help
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
* @package Papaya
* @subpackage Administration
* @version $Id: papaya_help.php 36224 2011-09-20 08:00:57Z weinert $
*/


/**
* Basic class Tags
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_object.php');

/**
* Online Help
*
* @package Papaya
* @subpackage Administration
*/
class papaya_help extends base_object {

  /**
  * link parameters base name
  * @var unknown_type
  */
  var $paramName = 'help';

  /**
  * online pages urls
  * @var array
  */
  var $urls = array(
    'news' => PAPAYA_SUPPORT_PAGE_NEWS,
    'manual' => PAPAYA_SUPPORT_PAGE_MANUAL,
  );

  /**
  * bug reports target email
  * @var string
  */
  var $bugReportMail = PAPAYA_SUPPORT_BUG_EMAIL;

  /**
  * Initialize parameters
  * @return void
  */
  function initialize() {
    $this->initializeParams();
  }

  /**
  * Execute actions dependiung on parameters
  * @return void
  */
  function execute() {
    if (isset($this->params['ohmode'])) {
      switch ($this->params['ohmode']) {
      case 'bugreport' :
        if ($this->authUser->isAdmin()) {
          if (isset($this->params['confirm_send']) && $this->params['confirm_send']) {
            $this->initializeSystemTestInformation();
            $this->initializeBugReportDialog();
            if ($this->bugReportDialog->checkDialogInput()) {
              if ($this->sendBugReport()) {
                unset($this->bugReportDialog);
                $this->addMsg(MSG_INFO, $this->_gt('Bug report sent.'));
              } else {
                $this->addMsg(MSG_INFO, $this->_gt('Cannot send bug report.'));
              }
            }
          }
        }
        break;
      }
    }
  }

  /**
  * Get administration page xml
  * @return void
  */
  function getXML() {
    $this->layout->setParam('COLUMNWIDTH_LEFT', '100px');
    $this->layout->setParam('COLUMNWIDTH_CENTER', '100%');
    $this->getButtonsPanel();
    if (!isset($this->params['ohmode'])) {
      $this->params['ohmode'] = '';
    }
    switch ($this->params['ohmode']) {
    case 'bugreport' :
      $this->getBugReportDialog();
      break;
    case 'sysinfo' :
      $this->getSystemInformation();
      break;
    case 'news' :
      $this->getContentFrame($this->urls['news'], 'News');
      break;
    default :
    case 'manual' :
      $this->getContentFrame($this->urls['manual'], 'User Manual');
      break;
    }

  }

  /**
  * Get folder panel xml (navigation bar)
  *
  * @access public
  */
  function getButtonsPanel() {
    $result = '<iconpanel>';
    $result .= sprintf(
      '<icon src="%s" subtitle="%s" href="%s"/>',
      papaya_strings::escapeHTMLChars($this->images['categories-help']),
      papaya_strings::escapeHTMLChars($this->_gt('User Manual')),
      papaya_strings::escapeHTMLChars(
        $this->getLink(array('ohmode' => 'manual'))
      )
    );
    $result .= sprintf(
      '<icon src="%s" subtitle="%s" href="%s"/>',
      papaya_strings::escapeHTMLChars($this->images['categories-news']),
      papaya_strings::escapeHTMLChars($this->_gt('News')),
      papaya_strings::escapeHTMLChars(
        $this->getLink(array('ohmode' => 'news'))
      )
    );
    if ($this->authUser->isAdmin()) {
      $result .= sprintf(
        '<icon src="%s" subtitle="%s" href="%s"/>',
        papaya_strings::escapeHTMLChars($this->images['categories-protocol']),
        papaya_strings::escapeHTMLChars($this->_gt('System Information')),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('ohmode' => 'sysinfo'))
        )
      );
      $result .= sprintf(
        '<icon src="%s" subtitle="%s" href="%s"/>',
        papaya_strings::escapeHTMLChars($this->images['items-bug']),
        papaya_strings::escapeHTMLChars($this->_gt('Bug Report')),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('ohmode' => 'bugreport'))
        )
      );
    }
    $result .= '</iconpanel>';
    $this->layout->addLeft($result);
  }

  /**
  * Get content frame xml for embedding online pages
  * @param string $url
  * @param string $caption
  * @return void
  */
  function getContentFrame($url, $caption) {
    $result = sprintf(
      '<panel width="100%%" title="%s">'.
      '<iframe width="100%%" noresize="noresize" hspace="0" vspace="0" align="center"'.
      ' scrolling="auto" height="600" src="%s?language=%s" class="inset" id="preview" /></panel>',
      papaya_strings::escapeHTMLChars($caption),
      papaya_strings::escapeHTMLChars($url),
      papaya_strings::escapeHTMLChars(
        $this->authUser->options['PAPAYA_UI_LANGUAGE']
      )
    );
    $this->layout->add($result);
  }

  /**
  * Initialize system test object
  * @return void
  */
  function initializeSystemTestInformation() {
    if (!(isset($this->systemTest) && is_object($this->systemTest))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_systemtest.php');
      $this->systemTest = new papaya_systemtest();
      $this->systemTest->execute();
      $this->systemTest->images = &$this->images;
    }
  }

  /**
  * Execute system test and output informations
  * @return unknown_type
  */
  function getSystemInformation() {
    if ($this->authUser->isAdmin()) {
      $this->initializeSystemTestInformation();
      $this->layout->add($this->systemTest->getXMLLists());
    }
  }

  /**
  * Initialize bug report dialog
  * @return void
  */
  function initializeBugReportDialog() {
    if (!(isset($this->bugReportDialog) && is_object($this->bugReportDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');

      $data = array(
        'name' => $this->authUser->user['givenname'].' '.$this->authUser->user['surname'],
        'email' => $this->authUser->user['email']
      );

      if (isset($this->params['log_id']) && $this->params['log_id'] > 0) {
        $logId = (int)$this->params['log_id'];
        include_once(PAPAYA_INCLUDE_PATH.'system/papaya_log.php');
        $logObject = new papaya_log();
        if ($logObject->loadMessage($logId)) {
          $msg = $logObject->messageList[$logId];
          include_once(PAPAYA_INCLUDE_PATH.'system/base_statictables.php');
          $msgGroups = base_statictables::getTableLogGroups();
          $data['description'] =
             '*Log message: '.$msgGroups[$msg['log_msgtype']].
             '* ('.date('Y-m-d H:i:s', $msg['log_time']).")\n\n";
          $data['description'] .= 'URL: <'.$msg['log_msg_uri'].'>'."\n";
          $data['description'] .= 'Script: <'.$msg['log_msg_script'].'>'."\n\n\n";
          $msgText = $msg['log_msg_long'];
          $msgText = str_replace(
            array('</p>', '<br />', '<br/>'),
            array("\n\n", "\n", "\n"),
            $msgText
          );
          $data['description'] .= strip_tags($msgText);
        }
      }

      $hidden = array(
        'ohmode' => 'bugreport',
        'confirm_send' => TRUE
      );
      $fields = array(
        'name' => array('Name', 'isNoHTML', TRUE, 'input', 200),
        'email' => array('EMail', 'isEMail', TRUE, 'input', 200),
        'sendcopy' => array('CC for sender', 'isNum', TRUE, 'yesno', NULL, '', 1),
        'Bug',
        'description' => array('Description', 'isSomeText', TRUE, 'textarea', 14),
        //'screenshot' => array('Screenshot', 'isFile', FALSE, 'file', 200),
        'testdata' => array('Send test results', 'isNum', TRUE, 'yesno', NULL, '', 1),
        'systemdata' => array('Send system information', 'isNum', TRUE, 'yesno', NULL, '', 1),
      );

      $this->bugReportDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->bugReportDialog->msgs = &$this->msgs;
      $this->bugReportDialog->loadParams();
      $this->bugReportDialog->inputFieldSize = 'x-large';
      $this->bugReportDialog->dialogDoubleButtons = TRUE;
      $this->bugReportDialog->buttonTitle = 'Send';
      $this->bugReportDialog->dialogTitle = $this->_gt('Bug report');
    }
  }

  /**
  * Get bug report email dialog
  * @return void
  */
  function getBugReportDialog() {
    if ($this->authUser->isAdmin()) {
      $this->initializeBugReportDialog();
      $this->layout->add($this->bugReportDialog->getDialogXML());
      $this->getSystemInformation();
    }
  }

  /**
  * Send bug report email
  * @return boolean
  */
  function sendBugReport() {
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
    $email = new email();
    $email->addAddress($this->bugReportMail);
    $email->setSender(
      $this->bugReportDialog->data['email'], $this->bugReportDialog->data['name']
    );
    if (isset($this->bugReportDialog->data['sendcopy']) &&
        $this->bugReportDialog->data['sendcopy']) {
      $email->addAddress(
        $this->bugReportDialog->data['email'],
        $this->bugReportDialog->data['name'],
        'CC'
      );
    }
    $email->setSubject('papaya CMS 5 Bug Report');
    $body = $this->bugReportDialog->data['description']."\n";
    if (isset($this->bugReportDialog->data['testdata']) &&
        $this->bugReportDialog->data['testdata']) {
      $body .= "\n\n*Test results*\n\n";
      foreach ($this->systemTest->tests as $testTitle => $testParams) {
        if (isset($this->systemTest->resultTests[$testTitle])) {
          $testResult = $this->systemTest->getResultOuput(
            $this->systemTest->resultTests[$testTitle]
          );
          $body .= $testTitle.': '.$testResult[0]."\n";
        }
      }
    }
    if (isset($this->bugReportDialog->data['systemdata']) &&
        $this->bugReportDialog->data['systemdata']) {
      $body .= "\n\n*System information*\n\n";
      foreach ($this->systemTest->information as $infoTitle => $infoParam) {
        if (isset($this->systemTest->resultInfos[$infoTitle])) {
          $body .= $infoTitle.': '.$this->systemTest->resultInfos[$infoTitle]."\n";
        }
      }
    }
    $email->setBody($body);
    return $email->send();
  }
}
?>