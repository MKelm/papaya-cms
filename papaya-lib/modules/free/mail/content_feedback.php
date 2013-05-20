<?php
/**
* Page module - Comment to page site
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
* @version $Id: content_feedback.php 38185 2013-02-26 15:21:15Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Check library for string validation
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Page to send comments
*
* @package Papaya-Modules
* @subpackage Free-Mail
*/
class content_feedback extends base_content {

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
    'title' => array('Page Title', 'isSomeText', TRUE, 'input', 200, '', ''),
    'msg_mailto' => array('Recipient', 'isEmail', TRUE, 'input', 200, '', ''),
    'nl2br' => array ('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
     'Apply linebreaks from input to the HTML output.', 0),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 5, '', ''),
    'msg_text' => array('Text', 'isSomeText', FALSE, 'richtext', 15, '', ''),
    'Image',
    'image' => array('Image', 'isSomeText', FALSE, 'image', 400, '', ''),
    'imgalign' => array('Image align', 'isAlpha', TRUE, 'combo',
      array('left' => 'left', 'right' => 'right', 'center' => 'center'), '', 'left'),
    'breakstyle' => array('Text float', 'isAlpha', TRUE, 'combo',
      array('none' => 'None', 'side' => 'Side'), '', 'none'),
    'Mail',
    'msg_subject' => array('Template Subject', 'isSomeText', FALSE, 'input', 200,
      '{%SUBJECT%}', '[Comment] {%SUBJECT%}'),
    'msg_body' => array('Template Body', 'isSomeText', FALSE, 'textarea', 6,
      '{%FROM%}, {%NAME%}, {%TEXT%}',
      "Name: {%NAME%} Email: {%FROM%}  Comment: {%TEXT%}"
    ),
    'Captions',
    'show_field_sender' => array('Show sender field', 'isNum', TRUE, 'yesno', NULL, '', 1),
    'cap_sender' => array('Sender', 'isNoHTML', FALSE, 'input', 200, '', 'Your name'),
    'cap_email' => array('E-Mail', 'isNoHTML', FALSE, 'input', 200, '', 'Your email address'),
    'cap_subject' => array('Subject', 'isNoHTML', FALSE, 'input', 200, '', 'Subject'),
    'cap_message' => array('Message', 'isNoHTML', FALSE, 'input', 200, '', 'Your message'),
    'Confirmation',
    'confirm_subject' => array('Confirmation Email Subject', 'isNoHTML', FALSE, 'input', 200,
      'Email subject sent to surfer. Leave empty for not sending any email to surfer.', ''),
    'confirm_body' => array('Confirmation Email Body', 'isNoHTML', FALSE,
      'textarea', 5, 'Email body sent to surfer.',
      'Thank you for your comment. We will handle it as soon as possible.'),
    'Messages',
    'msg_error' => array('Input Error', 'isSomeText', FALSE, 'textarea', 3, '',
      'Please check your input.'),
    'msg_send' => array('Confirmation', 'isSomeText', FALSE, 'textarea', 3, '',
      'Message sent. Thank You.'),
    'msg_notsend' => array('Send error', 'isSomeText', FALSE, 'textarea', 3, '',
      'Send error. Please try again.'),
    'msg_privacy' => array('Privacy', 'isSomeText', FALSE, 'textarea', 10, '', ''),
    'Captchas',
    'use_captcha' => array('Use captcha', 'isNum', TRUE, 'yesno', NULL, '', 0),
    'captcha_title' => array('Field title', 'isNoHTML', FALSE, 'input', 200, '', ''),
    'captcha_type' => array(
      'Captcha image', 'isSomeText', FALSE, 'function', 'callbackCaptchas', '', ''
    ),
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
  * Get parsed teaser
  *
  * @access public
  * @return string $result
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    $result = sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<text>%s</text>',
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    return $result;
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
    $this->emailObj = new email();
    $result = '';

    if (isset($this->data['title'])) {
      $result .= sprintf(
        '<title>%s</title>',
        papaya_strings::escapeHTMLChars($this->data['title'])
      );
      $result .= sprintf(
        '<text>%s</text>',
        $this->getXHTMLString($this->data['msg_text'], !((bool)$this->data['nl2br']))
      );
    }
    if (!empty($this->data['image'])) {
      $result .= sprintf(
        '<image align="%s" break="%s">%s</image>'.LF,
        papaya_strings::escapeHTMLChars($this->data['imgalign']),
        papaya_strings::escapeHTMLChars($this->data['breakstyle']),
        $this->getPapayaImageTag($this->data['image'])
      );
    }
    $result .= '<mail>'.LF;

    if (isset($this->params['submit']) &&  $this->params['submit']) {
      $errors = $this->checkMailDialogInput();
      if (!$errors) {
        $message = $this->sendEmail();
        $result .= sprintf(
          '<message type="%s">%s</message>'.LF,
          papaya_strings::escapeHTMLChars($message[1]),
          $this->getXHTMLString($message[2])
        );
      } else {
        $result .= '<message type="error">'.LF;
        $result .= $this->getXHTMLString($this->data['msg_error']);
        if (is_array($errors)) {
          $result .= '<ul>'.LF;
          foreach ($errors as $field => $caption) {
            $result .= sprintf(
              '<li>%s</li>'.LF,
              papaya_strings::escapeHTMLChars($caption)
            );
          }
          $result .= '</ul>'.LF;
        }
        $result .= '</message>'.LF;
        $result .= $this->getMailDialog($errors);
      }
    } else {
      $result .= $this->getMailDialog();
    }
    $result .= sprintf(
      '<privacy>%s</privacy>',
      $this->getXHTMLString($this->data['msg_privacy'])
    );
    $result .= '</mail>'.LF;
    return $result;
  }

  /**
  * Send email
  *
  * @param array $content message data - this is a parameter so the function can be overloaded
  * @access public
  * @return string $result XML
  */
  function sendEmail($content = array()) {
    if (empty($content)) {
      $content['NAME'] = empty($this->params['mail_name']) ? '' : $this->params['mail_name'];
      $content['FROM'] = empty($this->params['mail_from']) ? '' : $this->params['mail_from'];
      $content['SUBJECT'] =
        empty($this->params['mail_subject']) ? '' : $this->params['mail_subject'];
      $content['TEXT'] =
        empty($this->params['mail_message']) ? '' : $this->params['mail_message'];
    }
    if (!empty($content['NAME'])) {
      $this->emailObj->setSender($content['FROM'], $content['NAME']);
    }
    $this->emailObj->setTemplate('subject', $this->data['msg_subject'], $content);
    $this->emailObj->setTemplate('body', $this->data['msg_body'], $content);

    if ($this->emailObj->send($this->data['msg_mailto'])) {
      $result = array(
        TRUE, 'normal', $this->data['msg_send']
      );
      if (!empty($this->data['confirm_subject']) && !empty($content['FROM'])) {
        $emailConfirmObj = new email();
        $emailConfirmObj->setSender($this->data['msg_mailto']);
        $emailConfirmObj->addAddress($content['FROM'], $content['NAME']);
        $emailConfirmObj->setSubject($this->data['confirm_subject'], $content);
        $emailConfirmObj->setBody($this->data['confirm_body'], $content);
        $emailConfirmObj->send();
      }
    } else {
      $result = array(
        FALSE, 'warning', $this->data['msg_notsend']
      );
    }
    return $result;
  }

  /**
  * Get mail dialog
  *
  * @access public
  * @return string $result XML
  */
  function getMailDialog($errors = array()) {
    $result = sprintf(
      '<form action="%s" method="post">'.LF,
      papaya_strings::escapeHTMLChars($this->baseLink)
    );
    $result .= sprintf(
      '<input type="hidden" name="%s[submit]" value="1" />'.LF,
      papaya_strings::escapeHTMLChars($this->paramName)
    );
    if ($this->data['show_field_sender']) {
      $result .= sprintf(
        '<label for="%s[mail_name]">%s</label>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->data['cap_sender'])
      );
      $result .= sprintf(
        '<input type="text" name="%s[mail_name]" value="%s" required="required"%s/>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        empty($this->params['mail_name'])
          ? '' : papaya_strings::escapeHTMLChars($this->params['mail_name']),
        empty($errors['mail_name'])
          ? '' : ' error="error"'
      );
    }
    $result .= sprintf(
      '<label for="%s[mail_from]">%s</label>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->data['cap_email'])
    );
    $result .= sprintf(
      '<input type="text" name="%s[mail_from]" value="%s" required="required"%s/>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($this->params['mail_from'])
        ? '' : papaya_strings::escapeHTMLChars($this->params['mail_from']),
      empty($errors['mail_from'])
        ? '' : ' error="error"'
    );
    $result .= sprintf(
      '<label for="%s[mail_subject]">%s</label>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->data['cap_subject'])
    );
    $result .= sprintf(
      '<input type="text" name="%s[mail_subject]" value="%s" required="required"%s/>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($this->params['mail_subject'])
        ? '' : papaya_strings::escapeHTMLChars($this->params['mail_subject']),
      empty($errors['mail_subject'])
        ? '' : ' error="error"'
    );
    $result .= sprintf(
      '<label for="%s[mail_message]">%s</label>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($this->data['cap_message'])
    );
    $result .= sprintf(
      '<textarea name="%s[mail_message]" required="required"%s>%s</textarea>'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($errors['mail_message'])
        ? '' : ' error="error"',
      empty($this->params['mail_message'])
        ? '' : papaya_strings::escapeHTMLChars($this->params['mail_message'])
    );

    if (isset($this->data['use_captcha']) && $this->data['use_captcha']) {
      $result .= sprintf(
        '<label for="%s[captchaanswer]">%s</label>',
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($this->data['captcha_title'])
      );
      $result .= $this->generateCaptchaInput();
    }

    $result .= '</form>'.LF;
    return $result;
  }

  /**
  * Check mail dialog input
  *
  * @access public
  * @return array $result input contents
  */
  function checkMailDialogInput() {
    $result = FALSE;
    if ($this->data['show_field_sender']) {
      if (empty($this->params['mail_name']) ||
          !checkit::isSomeText($this->params['mail_name'], TRUE)) {
        $result['mail_name'] = $this->data['cap_sender'];
      }
    } else {
      $this->params['mail_name'] = '';
    }
    if (empty($this->params['mail_from']) ||
        !checkit::isEmail($this->params['mail_from'], FALSE)) {
      $result['mail_from'] = $this->data['cap_email'];
    }
    if (empty($this->params['mail_subject']) ||
        !checkit::isSomeText($this->params['mail_subject'], TRUE)) {
      $result['mail_subject'] = $this->data['cap_subject'];
    }
    if (empty($this->params['mail_message']) ||
        !checkit::isSomeText($this->params['mail_message'], TRUE)) {
      $result['mail_message'] = $this->data['cap_message'];
    }
    if (isset($this->data['use_captcha']) && $this->data['use_captcha']) {
      if (empty($this->params['captchaanswer']) ||
          empty($this->params['captchaident']) ||
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
  * @see base_dialog::getDlgElement
  */
  function generateCaptchaInput() {
    $result = '';
    if (!empty($this->data['captcha_type'])) {
      /* calculate random id (session var identifier) */
      srand((double)microtime() * 1000000);
      $randId = md5(uniqid(rand()));
      /* generate hidden-field with id */
      $result .= sprintf(
        '<input type="hidden" name="%s[captchaident]"'.
        ' fid="captcha_hidden" value="%s"></input>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($randId)
      );
      /* generate text input-field */
      $result .= sprintf(
        '<input type="text" name="%s[captchaanswer]"'.
        ' size="100" fid="captcha" class="text" mandatory="true"></input>'.LF,
        papaya_strings::escapeHTMLChars($this->paramName)
      );
      /* generate image-tag */
      $result .= sprintf(
        '<img type="captcha" src="%s.image.jpg?img[identifier]=%s" name="mail[captcha]"/>'.LF,
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
    };
    return $result;
  }

  /**
  * @see base_dialog::checkCaptchaAnswer
  */
  function checkCaptchaAnswer($answer, $identifier) {
    $sessionData = $this->getSessionValue('PAPAYA_SESS_CAPTCHA');
    if (isset($sessionData[$identifier]) &&
        trim($sessionData[$identifier]) != '' &&
        isset($answer)) {
      $this->setSessionValue('PAPAYA_SESS_CAPTCHA', array());
      return ($answer === $sessionData[$identifier]);
    }
    $this->setSessionValue('PAPAYA_SESS_CAPTCHA', array());
    return FALSE;
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