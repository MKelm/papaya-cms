<?php
/**
* Page Module Mailman
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
* @version $Id: content_mailman.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* base class page modules
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page Module Mailman
*
* @package Papaya-Modules
* @subpackage Beta-Mailman
*/
class content_mailman extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array ('Automatic linebreaks', 'isNum', TRUE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0),
    'title' => array ('Title', 'isSomeText', TRUE, 'input', 400, '', ''),
    'text' => array ('Text', 'isSomeText', FALSE, 'richtext', 10, '', ''),
    'emailtext' => array ('Email Field Text', 'isNoHTML', FALSE, 'input', 400, '', ''),
    'subscribe' => array ('Subscribe', 'isNoHTML', FALSE, 'input', 400, '', ''),
    'unsubscribe' => array ('Unsubscribe', 'isNoHTML', FALSE, 'input', 400, '', ''),
    'submit' => array ('Submit', 'isNoHTML', FALSE, 'input', 400, '',
      'Text on submit button.'),
    'Mailman interface',
    'mode' => array('Mode', 'isNum', TRUE, 'combo', array(0=>'Email', 1=>'Shell'), '', ''),
    'email' => array('Email', 'isEMail', FALSE, 'input', 100, 'request@domain.tld', '', ''),
    'binary' => array('Binary', 'isFile', FALSE, 'input', 100, '', ''),
    'web' => array('Web', 'isHTTPX', TRUE, 'input', 150, '', ''),
    'Messages',
    'success' => array('Success', 'isSomeText', FALSE, 'textarea', 5, '',
      'An email containing further information was sent to you.'),
    'Error Messages',
    'wrong_email' => array('Wrong Email', 'isSomeText', FALSE, 'textarea', 5, '',
      'Please enter a valid email address.'),
    'no_lists' => array('no Lists', 'isSomeText', FALSE, 'textarea', 5, '',
      'There are currently no lists available.'),
    'wrong_list' => array('Wrong List', 'isSomeText', FALSE, 'textarea', 5, '',
      'The list you selected is no longer available.'),
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
    $this->setDefaultData();
    $result = sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $this->executeOutput();
    if (!$this->isSubmitted || isset($this->processErrors)) {
      $result .= $this->getMailForm();
      if (is_array($this->processErrors)) {
        $errors = '';
        foreach ($this->processErrors as $error) {
          $errors .= sprintf(
            '<li>%s</li>'.LF,
            $error
          );
        }
        $result .= sprintf(
          '<error><ul class="mailmanErrors">%s</ul></error>'.LF,
          $errors
        );
      }
    } else {
      $result .= sprintf(
        '<message>%s: %s</message>'.LF,
        papaya_strings::escapeHTMLChars(
          $this->data['mailinglists'][$this->params['subscribeListName']]['title']
        ),
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
      if (count($this->data['mailinglists']) > 1 &&
          !in_array($this->params['subscribeListName'], array_keys($this->data['mailinglists']))) {
        $this->processErrors[] = $this->data['wrong_list'];
      }
      if (!isset($this->processErrors)) {
        $this->request(
          $this->params['email'],
          $this->params['subscribeListName'],
          $this->params['action'],
          $this->data['email']
        );
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
  function request($requestEmail, $toList, $action, $listEmail) {
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
    $to = sprintf('%s-request%s', $toList, substr($listEmail, strpos($listEmail, '@')));
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
      "<text>%s</text>".LF,
      $this->getXHTMLString(
        $this->data['text'], !((bool)@$this->data['nl2br'])
      )
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
    $subscribeChecked = '';
    $unsubscribeChecked = '';
    if (isset($this->params['action']) && $this->params['action'] == 'subscribe') {
      $subscribeChecked = 'checked="checked"';
    } elseif (isset($this->params['action']) && $this->params['action'] == 'unsubscribe') {
      $unsubscribeChecked = 'checked="checked"';
    } else {
      $subscribeChecked = 'checked="checked"';
    }
    $mailForm .= sprintf(
      '<input type="selectaction" name="%s[action]" value="subscribe" %s>%s</input>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      $subscribeChecked,
      papaya_strings::escapeHTMLChars($this->data['subscribe'])
    );
    $mailForm .= sprintf(
      '<input type="selectaction" name="%s[action]" value="unsubscribe" %s>%s</input>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      $unsubscribeChecked,
      papaya_strings::escapeHTMLChars($this->data['unsubscribe'])
     );
    if (!isset($this->data['mailinglists']) || !is_array($this->data['mailinglists'])
        || sizeof($this->data['mailinglists']) == 0) {
      $this->processErrors[] = $this->data['no_lists'];
      return;
    } else {
      uasort($this->data['mailinglists'], array(&$this, 'compareItems'));
      $lists = sprintf(
         '<lists paramName="%s[subscribeListName]">'.LF,
         papaya_strings::escapeHTMLChars($this->paramName)
      );
      while ($listAttributes = array_shift($this->data['mailinglists'])) {
        if (is_array($listAttributes)) {
          if (isset($this->params['subscribeListName']) &&
              $this->params['subscribeListName'] == $listAttributes['name']) {
            $checked = 'checked="checked"';
          }
          $lists .= sprintf(
            '<list name="%s">%s</list>'.LF,
            papaya_strings::escapeHTMLChars($listAttributes['name']),
            papaya_strings::escapeHTMLChars($listAttributes['title'])
          );
          unset($checked);
        }
      }
      $lists .= '</lists>'.LF;
    }
    // mail form footer
    $mailForm .= sprintf(
      '<input type="hidden" name="%s[submitted]" value="TRUE" />'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    $mailForm .= sprintf(
      '<btnSubmit name="%s[submit]" value=" %s " />'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->data['submit'])
    );
    $mailForm .= '</form>'.LF;
    $mailForm .= $lists;
    $mailForm .= '</mailform>';
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

  /**
  * Execution
  *
  * @access public
  */
  function execute() {
    if ($this->params['contentmode'] == 1) {
      switch(@$this->params['ml_cmd']) {
      case 'add':
        if (isset($this->params['ml_name'])) {
          $listName = trim($this->params['ml_name']);
          if (!isset($this->data['mailinglists'][$listName])) {
            $this->initItemDialog($this->selectedItem);
            if ($this->itemDialog->checkDialogInput()) {
              $this->data['mailinglists'][$listName] = array(
                'name' => $listName,
                'title' => trim($this->params['ml_title']),
                'archive' => trim($this->params['ml_archive']),
                'moderated' => trim($this->params['ml_moderated']),
              );
              $this->selectedItem = &$this->data['mailinglists'][$listName];
              $this->modified = TRUE;
            }
            unset($this->itemDialog);
          } else {
            $this->addMsg(MSG_ERROR, 'Please use an unique name.');
          }
        }
        break;
      case 'delete':
        if (isset($this->params['ml_item'])) {
          $listName = trim($this->params['ml_item']);
          if (isset($this->data['mailinglists'][$listName])) {
            unset($this->data['mailinglists'][$this->params['ml_item']]);
            $this->modified = TRUE;
          } else {
            $this->addMsg(MSG_ERROR, 'List could not be deleted, it doesn\'t exist.');
          }
        }
        break;
      case 'edit':
        if (isset($this->params['ml_item']) &&
            isset($this->data['mailinglists'][$this->params['ml_item']])) {
          if (isset($this->params['ml_name'])) {
            $this->selectedItem = &$this->data['mailinglists'][$this->params['ml_item']];
            $listName = trim($this->params['ml_name']);
            if ($listName == $this->params['ml_item']) {
              //same list - check an change
              $this->initItemDialog($this->selectedItem);
              if ($this->itemDialog->checkDialogInput()) {
                $this->data['mailinglists'][$listName] = array(
                  'name' => $listName,
                  'title' => trim($this->params['ml_title']),
                  'archive' => trim($this->params['ml_archive']),
                  'moderated' => trim($this->params['ml_moderated']),
                );
                $this->modified = TRUE;
              }
              unset($this->itemDialog);
            } elseif (!isset($this->data['mailinglists'][$listName])) {
              //new list name - check unset and set new
              $this->initItemDialog($this->selectedItem);
              if ($this->itemDialog->checkDialogInput()) {
                $this->data['mailinglists'][$listName] = array(
                  'name' => $listName,
                  'title' => trim($this->params['ml_title']),
                  'archive' => trim($this->params['ml_archive']),
                  'moderated' => trim($this->params['ml_moderated']),
                );
                unset($this->data['mailinglists'][$this->params['ml_item']]);
                $this->selectedItem = &$this->data['mailinglists'][$listName];
                $this->modified = TRUE;
              }
              unset($this->itemDialog);
            } else {
              $this->addMsg(MSG_ERROR, 'Please use an unique name.');
            }
          }
        }
        break;
      default:
        if (isset($this->params['ml_item']) &&
            isset($this->data['mailinglists'][$this->params['ml_item']])) {
          $this->selectedItem = &$this->data['mailinglists'][$this->params['ml_item']];
        }
        break;
      }
      if (isset($this->data['mailinglists']) && is_array($this->data['mailinglists'])) {
        uasort($this->data['mailinglists'], array(&$this, 'compareItems'));
      }
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Compares two items via strnatcasecmp by item[title] or if empty by item[name]
  *
  * @param array $a first item (mailinglist)
  * @param array $b second item (mailinglist)
  */
  function compareItems($a, $b) {
    if ($a['title'] != $b['title']) {
      return strnatcasecmp($a['title'], $b['title']);
    } else {
      return strnatcasecmp($a['name'], $b['name']);
    }
  }

  /**
  * Get formular
  *
  * @access public
  * @return string $result
  */
  function getForm() {
    $result = '';
    $result .= $this->getContentToolbar();
    switch($this->params['contentmode']) {
    case 1:
      $result .= $this->getItemList();
      $result .= $this->getItemDialog();
      break;
    default:
      $result .= parent::getForm();
      break;
    }
    return $result;
  }

  /**
  * Check if modified
  *
  * @access public
  * @return boolean
  */
  function modified() {
    switch(@$this->params['contentmode']) {
    case 1:
      return $this->modified;
    default:
      return parent::modified();
    }
    return FALSE;
  }

  /**
  * Check data
  *
  * @access public
  * @return boolean
  */
  function checkData() {
    switch(@$this->params['contentmode']) {
    case 1:
      return TRUE;
    default:
      return parent::checkData();
    }
    return FALSE;
  }

  /**
  * Get content toolbar
  *
  * @access public
  * @return string XML
  */
  function getContentToolbar() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$GLOBALS['PAPAYA_IMAGES'];
    $toolbar->addButton(
      'General',
      $this->getLink(
        array('contentmode'=>0)
      ),
      $toolbar->images['categories-content'],
      '',
      $this->params['contentmode'] == 0
    );
    $toolbar->addButton(
      'Lists',
      $this->getLink(
        array('contentmode'=>1)
      ),
      $toolbar->images['categories-view-list'],
      '',
      $this->params['contentmode'] == 1
    );
    $toolbar->addSeperator();
    if ($this->params['contentmode'] == 1 && isset($this->selectedItem) &&
        is_array($this->selectedItem)) {
      $toolbar->addButton(
        'New list',
        $this->getLink(array('contentmode' => 1, 'ml_item' => '')),
        41,
        ''
      );
      $toolbar->addButton(
        'Delete list',
        $this->getLink(
          array(
            'contentmode' => 1,
            'ml_item' => $this->selectedItem['name'],
            'ml_cmd' => 'delete'
          )
        ),
        10,
        ''
      );
    }
    if ($str = $toolbar->getXML()) {
      return '<toolbar>'.$str.'</toolbar>';
    }
    return '';
  }

  /**
  * Generates item listview for mailing lists
  *
   * @access public
  * @return string $result listview XML
  */
  function getItemList() {
    $result = '';
    if (isset($this->data['mailinglists']) && is_array($this->data['mailinglists']) &&
        count($this->data['mailinglists']) > 0) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Lists'))
      );
      $result .= '<cols>';
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Title'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Name'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Archive'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Moderated'))
      );
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->data['mailinglists'] as $list) {
        $result .= sprintf(
          '<listitem title="%s" image="%s" href="%s">',
          papaya_strings::escapeHTMLChars(
            empty($list['title']) ? '...' : $list['title']
          ),
          papaya_strings::escapeHTMLChars($this->images['items-mail']),
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('ml_item'=>$list['name']))
          )
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>',
          papaya_strings::escapeHTMLChars(
            empty($list['name']) ? '' : $list['name']
          )
        );
        $result .= sprintf(
          '<subitem align="center"><glyph src="%s"/></subitem>',
          papaya_strings::escapeHTMLChars(
            empty($list['archive'])
              ? $this->images['status-node-empty-disabled']
              : $this->images['status-node-checked-disabled']
          )
        );
        $result .= sprintf(
          '<subitem align="center"><glyph src="%s"/></subitem>',
          papaya_strings::escapeHTMLChars(
            empty($list['moderated'])
              ? $this->images['status-node-empty-disabled']
              : $this->images['status-node-checked-disabled']
          )
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
    }
    return $result;
  }

  /**
  * Initializes item dialog
  *
  * @param array $itemData
  * @access public
  */
  function initItemDialog($itemData) {
    if (!(isset($this->itemDialog) && is_object($this->itemDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $fields = array(
        'ml_name' => array('Name', 'isAlphaNum', TRUE, 'input', 100),
        'ml_title' => array('Title', 'isNoHTML', TRUE, 'input', 100),
        'Options',
        'ml_archive' => array ('Archive', 'isNum', TRUE, 'yesno', '', '', 1),
        'ml_moderated' => array ('Moderated', 'isNum', TRUE, 'yesno', '', '', 0),
      );
      $hidden = array('m_save'=>1);
      if (isset($itemData) && is_array($itemData)) {
        $data = array(
          'ml_name' => (string)$itemData['name'],
          'ml_title' => (string)$itemData['title'],
          'ml_archive' => (int)$itemData['archive'],
          'ml_moderated' => (int)$itemData['moderated']
        );
        $hidden['ml_item'] = (string)$itemData['name'];
        $hidden['ml_cmd'] = 'edit';
        $title = $this->_gt('Edit mailinglist');
      } else {
        $hidden['ml_cmd'] = 'add';
        $title = $this->_gt('Add mailinglist');
      }
      $this->itemDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->itemDialog->msgs = &$this->msgs;
      $this->itemDialog->loadParams();
      $this->itemDialog->inputFieldSize = $this->inputFieldSize;
      $this->itemDialog->baseLink = $this->baseLink;
      $this->itemDialog->dialogTitle = $title;
    }
  }

  /**
  * Get item dialog
  *
  * @access public
  * @return string XML
  */
  function getItemDialog() {
    $this->initItemDialog($this->selectedItem);
    return $this->itemDialog->getDialogXML();
  }

  /**
  * Check item inputs
  *
  * @access public
  */
  function checkItemInputs() {

  }
}
?>