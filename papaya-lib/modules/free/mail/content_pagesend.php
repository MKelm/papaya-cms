<?php
/**
* Page module - Page to send page link
*
* Requires a link from [Default/Base - box] Extended Page Link
* where the referred page will be sent by email.
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
* @version $Id: content_pagesend.php 37883 2012-12-19 11:02:58Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Library for sting validation
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Send pages via email
*
* Configured by xml-file
*
* @package Papaya-Modules
* @subpackage Free-Mail
*/
class content_pagesend extends base_content {

  /**
  * Linked topic
  * @var object base_topic $linkedTopic
  */
  var $linkedTopic = NULL;

  /**
  * cacheable ?
  * @var boolean $cacheable
  */
  var $cacheable = FALSE;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'mail';

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'title' => array('Page Title', 'isSomeText', FALSE, 'input', 200, '', ''),
    'refpage' => array('Recommend page', 'isNoHTML', FALSE, 'pageid', 200,
      'Leave empty to not use a default page', ''),
    'Email',
    'msg_subject' => array('Subject template of email', 'isNoHTML', FALSE, 'input', 200, '', ''),
    'msg_body' => array('Body template of email', 'isNoHTML', FALSE, 'textarea', 20, '',
      "{%TITLE%}\n\n{%COMMENT%}\n\n{%FROM%}\n\n{%TEXT%}\n\n{%LINK%}"),
    'Captions',
    'cap_recipient' => array('Recipient', 'isNoHTML', FALSE, 'input', 200, '', 'Recipient'),
    'cap_sender' => array('Sender', 'isNoHTML', FALSE, 'input', 200, '', 'Your Email Adress'),
    'cap_comments' => array('Comments', 'isNoHTML', FALSE, 'input', 200, '', 'Comment'),
    'caption_submit' => array(
      'Recommend page', 'isNoHTML', FALSE, 'input', 200, '', 'Recommend page'
    ),
    'Messages',
    'msg_hello'=> array('Text', 'isSomeText', FALSE, 'textarea', 6, '', ''),
    'msg_send'=> array('Confirmation', 'isSomeText', FALSE, 'textarea', 6, '',
      'Message sent. Thank You.'),
    'msg_error'=> array('Input Error', 'isSomeText', FALSE, 'textarea', 6, '',
      'Please check your input.'),
    'msg_notsend'=> array('Send error', 'isSomeText', FALSE, 'textarea', 6, '',
      'Sent Error. Please try again.'),
    'msg_notopic'=> array('Topic Error', 'isSomeText', FALSE, 'textarea', 6, '',
      'No page selected'),
    'Hints',
    'hnt_privacy' => array('Privacy', 'isSomeText', FALSE, 'textarea', 10, '', ''),
    'hnt_email' => array('Email adress', 'isSomeText', FALSE, 'textarea', 10, '', ''),
    'Captcha',
    'use_captcha' => array('Use captcha', 'isNum', TRUE, 'yesno', '', '', 0),
    'captcha_title' => array('Field title', 'isNoHTML', FALSE, 'input', 200, '', ''),
    'captcha_type' => array('Captcha image', 'isSomeText', FALSE, 'function', 'callbackCaptchas'),
    'captcha_speaker' =>
      array('Captcha speaker icon', 'isSomeText', FALSE, 'imagefixed', 400, '', ''),
    'speaker_alt_text' => array('Speaker alt text', 'isNoHTML', FALSE, 'input', 200, '', ''),
    'speaker_link_title' => array('Speaker link title', 'isNoHTML', FALSE, 'input', 200, '', ''),
  );

  /**
  * captchas array
  */
  var $captchasArray = array(
    '103fecb7cc96c1a66633c7f464b15956',
    'fe3dd6359939c142781f70ae4b29c70c'
  );

  /**
  * Get mail data
  *
  * @param integer $topicId
  * @access public
  * @return array
  */
  function getMailData($topicId) {
    $content = NULL;
    if (isset($GLOBALS['PAPAYA_PAGE'])) {
      $className = get_class($this->parentObj);
      $this->linkedTopic = new $className();
      if ($this->linkedTopic->topicExists($topicId) &&
          $this->linkedTopic->loadOutput($topicId, $this->parentObj->getContentLanguageId())) {
        if ($GLOBALS['PAPAYA_PAGE']->validateAccess($topicId)) {
          if ($str = $this->linkedTopic->parseContent(FALSE)) {
            $content['xml'] = $str;
            $xmlTree = &simple_xmltree::createFromXML($str, $this);
            if (is_object($xmlTree) && $xmlTree->documentElement->hasChildNodes()) {
              for ($idx = 0; $idx < $xmlTree->documentElement->childNodes->length; $idx++) {
                $node = &$xmlTree->documentElement->childNodes->item($idx);
                if ($node->nodeType == XML_ELEMENT_NODE) {
                  switch ($node->nodeName) {
                  case 'text':
                  case 'title':
                    $content[$node->nodeName] = $node->valueOf();
                    break;
                  }
                }
              }
            }
          }
        }
      }
    }
    return $content;
  }


  /**
  * Get parsed data
  *
  * @access public
  * @return string xml
  */
  function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
    $this->emailObj = new email();

    if (isset($_GET['refpage']) && $_GET['refpage'] > 0) {
      $this->params['refpage'] = $_GET['refpage'];
      $refererPage = (int)$this->params['refpage'];
    } elseif (isset($this->params['refpage']) && $this->params['refpage'] > 0) {
      $refererPage = (int)$this->params['refpage'];
    } elseif (isset($this->data['refpage']) && $this->data['refpage'] > 0) {
      $refererPage = $this->data['refpage'];
    } else {
      $refererPage = 0;
    }

    if (isset($_GET['urlparams']) && trim($_GET['urlparams']) != '') {
      $this->params['urlparams'] = $_GET['urlparams'];
      $urlParams = $this->params['urlparams'];
    } elseif (isset($this->params['urlparams'])) {
      $urlParams = $this->params['urlparams'];
    } else {
      $urlParams = '';
    }

    $result = '';

    if (isset($this->data['title'])) {
      $result .= sprintf(
        '<title>%s</title>',
        papaya_strings::escapeHTMLChars($this->data['title'])
      );
    }
    $result .= '<mail>';

    $content = $this->getMailData($refererPage);
    if (isset($content) && is_array($content)) {
      $result .= $content['xml'];

      if (isset($this->params['submit']) &&  $this->params['submit']) {
        $errors = $this->checkMailDialogInput();
        if (!$errors) {
          $content['LINK'] = $this->getAbsoluteURL($this->getWebLink($refererPage)).
            $this->recodeQueryString($urlParams);
          $content['FROM'] = $this->params['mail_from'];
          $content['COMMENT'] = $this->params['mail_comments'];
          if (isset($this->params['mail_from'])) {
            $this->emailObj->setSender($this->params['mail_from']);
          }
          $this->emailObj->setSubject($this->data['msg_subject'], $content);
          $this->emailObj->setBody($this->data['msg_body'], $content);
          if ($this->emailObj->send($this->params['mail_to'])) {

            $this->logStatistic($refererPage, $urlParams);

            $this->data['msg_send'] = str_replace(
              '{%RECIPIENT%}', $this->params['mail_to'], $this->data['msg_send']
            );
            $this->data['msg_send'] = str_replace(
              '{%TITLE%}',
              empty($content['title']) ? '' : $content['title'],
              $this->data['msg_send']
            );
            $result .= sprintf(
              '<message type="normal">%s</message>',
              $this->getXHTMLString($this->data['msg_send'])
            );
          } else {
            $result .= sprintf(
              '<message type="warning">%s</message>',
              $this->getXHTMLString($this->data['msg_notsend'])
            );
          }
        } else {
          $result .= '<message type="error">';
          $result .= $this->getXHTMLString($this->data['msg_error']);
          if (is_array($errors)) {
            $result .= '<ul>';
            foreach ($errors as $fieldCaption) {
              $result .= sprintf(
                '<li>%s</li>',
                papaya_strings::escapeHTMLChars($fieldCaption)
              );
            }
            $result .= '</ul>';
          }
          $result .= '</message>';
          $result .= $this->getMailDialog($refererPage, $urlParams);
        }
      } else {
        $result .= sprintf(
          '<message type="normal">%s</message>',
          $this->getXHTMLString($this->data['msg_hello'])
        );
        $result .= $this->getMailDialog($refererPage, $urlParams);
      }
    } else {
      $result .= sprintf(
        '<message type="warning">%s</message>',
        $this->getXHTMLString($this->data['msg_notopic'])
      );
    }
    $result .= sprintf(
      '<privacy>%s</privacy>',
      $this->getXHTMLString($this->data['hnt_privacy'])
    );
    $result .= sprintf(
      '<email-hint>%s</email-hint>',
      $this->getXHTMLString($this->data['hnt_email'])
    );
    $result .= '</mail>';
    return $result;
  }

  /**
  * Get mail dialog
  *
  * @param string $refererPage
  * @param string $urlParams
  * @access public
  * @return string xml
  */
  function getMailDialog($refererPage, $urlParams) {
    $result = sprintf(
      '<form action="%s" method="post">',
      papaya_strings::escapeHTMLChars($this->baseLink)
    );
    $result .= sprintf(
      '<input type="hidden" name="%s[submit]" value="1" />',
      papaya_strings::escapeHTMLChars($this->paramName)
     );
    $result .= sprintf(
      '<input type="hidden" name="%s[refpage]" value="%s" />',
      papaya_strings::escapeHTMLChars($this->paramName),
      (int)$refererPage
    );
    $result .= sprintf(
      '<input type="hidden" name="%s[urlparams]" value="%s" />',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($urlParams)
    );

    if ($this->data['use_captcha']) {
      /* calculate random id (session var identifier) */
      srand((double)microtime() * 1000000);
      $randId = md5(uniqid(rand()));
      /* generate hidden-field with id */
      $result .= sprintf(
        '<input type="hidden" name="%s[captchaident]"'.
        ' class="dialogInput dialogScale" fid="captcha_hidden" value="%s"></input>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($randId)
      );
      $result .= sprintf(
        '<label for="%s[captchaanswer]">%s</label>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->data['captcha_title'])
      );
      /* generate text input-field */
      $result .= sprintf(
        '<input type="text" name="%s[captchaanswer]"'.
        ' size="100" fid="captcha" mandatory="true"></input>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      $result .= sprintf(
        '<img name="%s[captcha]" src="%s.image.jpg?img[identifier]=%s" />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->data['captcha_type']),
        papaya_strings::escapeHTMLChars($randId)
      );
      if (! empty($this->data['captcha_speaker'])) {
        $linkUrl = sprintf(
          "%s.image.jpg?img[identifier]=%s&img[voice]=1",
          urlencode($this->data['captcha_type']),
          urlencode($randId)
        );
        $speakerIcon = sprintf(
          '<img src="%s" alt="%s"/>',
          $this->getWebMediaLink(
            $this->data['captcha_speaker'],
            'media',
            $this->data['speaker_alt_text']
          ),
          papaya_strings::escapeHTMLChars($this->data['speaker_alt_text'])
        );
        $result .= sprintf(
          '<a name="captcha_speaker" href="%s" title="%s">%s</a>'.LF,
          papaya_strings::escapeHTMLChars($linkUrl),
          papaya_strings::escapeHTMLChars($this->data['speaker_link_title']),
          $speakerIcon
        );
      }
    }

    $result .= sprintf(
      '<label for="%s[mail_to]">%s</label>',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->data['cap_recipient'])
    );
    $result .= sprintf(
      '<input type="text" name="%s[mail_to]" value="%s" class="text" />',
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($this->params['mail_to'])
        ? '' : papaya_strings::escapeHTMLChars($this->params['mail_to'])
    );
    $result .= sprintf(
      '<label for="%s[mail_from]">%s</label>',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->data['cap_sender'])
    );
    $result .= sprintf(
      '<input type="text" name="%s[mail_from]" value="%s" class="text" />',
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($this->params['mail_from'])
        ? '' : papaya_strings::escapeHTMLChars($this->params['mail_from'])
    );
    $result .= sprintf(
      '<label for="%s[mail_comments]">%s</label>',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->data['cap_comments'])
    );
    $result .= sprintf(
      '<textarea name="%s[mail_comments]" class="text">%s</textarea>',
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($this->params['mail_comments'])
        ? '' : papaya_strings::escapeHTMLChars($this->params['mail_comments'])
    );
    $result .= sprintf(
      '<submitbutton caption="%s"/>',
      papaya_strings::escapeHTMLChars($this->data['caption_submit'])
    );
    $result .= '</form>';
    return $result;
  }

  /**
  * Check mail dialog input
  *
  * @access public
  * @return array
  */
  function checkMailDialogInput() {
    $result = FALSE;
    if (empty($this->data['cap_recipient']) ||
        !checkit::isEmail($this->params['mail_to'], TRUE)) {
      $result['mail_to'] = $this->data['cap_recipient'];
    }
    if (empty($this->params['mail_from']) ||
        !checkit::isEmail($this->params['mail_from'], TRUE)) {
      $result['mail_from'] = $this->data['cap_sender'];
    }
    if (empty($this->params['mail_comments']) ||
        !checkit::isSomeText($this->params['mail_comments'], TRUE)) {
      $result['mail_comments'] = $this->data['cap_comments'];
    }
    if ($this->data['use_captcha']) {
      if (empty($this->params['captchaanswer']) || empty($this->params['captchaident']) ||
          !$this->checkCaptchaAnswer(
            $this->params['captchaanswer'], $this->params['captchaident']
          )
         ) {
        $result['captchaanswer'] = $this->data['captcha_title'];
      }
    }
    return $result;
  }

  /**
  * Compare recieved captcha string with session value
  * @param string $answer
  * @param string $identifier
  * @return boolean
  */
  function checkCaptchaAnswer($answer, $identifier) {
    $sessionData = $this->getSessionValue('PAPAYA_SESS_CAPTCHA');
    if (isset($sessionData[$identifier]) &&
        trim($sessionData[$identifier]) != '' &&
        isset($answer)) {
      $this->setSessionValue('PAPAYA_SESS_CAPTCHA', array() );
      return ($answer === $sessionData[$identifier]);
    }
    $this->setSessionValue('PAPAYA_SESS_CAPTCHA', array() );
    return FALSE;
  }

  /**
  * Log sending action for statistic
  * @param integer $refererPage
  * @param string $urlParams
  * @return boolean
  */
  function logStatistic($refererPage, $urlParams) {
    $data = array(
      'topic_id' => $refererPage,
      'urlparams' => $this->recodeQueryString($urlParams),
    );
    include_once(PAPAYA_INCLUDE_PATH.'system/base_statistic_entries_tracking.php');
    return base_statistic_entries_tracking::logEntry($this->guid, 'article_sent', $data);
  }

  /**
  * Callback function for the captcha module. Returns a list of dynamic
  * images using the captcha-module.
  *
  * @param string $name string Name of the field.
  * @param array $field edit field parameters
  * @param string $data current value
  */
  function callbackCaptchas($name, $field, $data) {
    $result = '';
    include_once(PAPAYA_INCLUDE_PATH.'system/base_imagegenerator.php');
    $imageGenerator = new base_imagegenerator;
    $captchas = $imageGenerator->getIdentifiersByGUID($this->captchasArray);
    if (is_array($captchas) && count($captchas) > 0) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      foreach ($captchas as $captcha) {
        if (!empty($data) && $data == $captcha['image_ident']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<option value="%s"%s>%s</option>'.LF,
          papaya_strings::escapeHTMLChars($captcha['image_ident']),
          $selected,
          papaya_strings::escapeHTMLChars($captcha['image_title'])
        );
      }
      $result .= '</select>'.LF;
    }
    return $result;
  }
}
?>