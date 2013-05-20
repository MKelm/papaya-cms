<?php
/**
* Box to display forum content or page comments.
**************************************************************************************
* +--------------+        +----------------+  * show/browse categories
* | box_forum    |------->| base_actionbox |  * list forums in categories
* +--------------+        +----------------+  * list topics in forum
*       <>                                    * list threads in topics
*        |                                    * show entries in threads
*        |                                    * new topics/threads
*       \|/                                   * new entries
* +---------------+        +--------------+   * page comments
* | actbox_[...]  |------->| output_forum |
* +---------------+        +--------------+
*                                 |
*                                 |
*                                \|/
*                          +--------------+
*                          | base_forum   |   [...] = forum or comments
*                          +--------------+
**************************************************************************************
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Free-Forum
*/

/**
* Base class for box modules.
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Base / Output forum class to perform further steps.
*/
require_once(dirname(__FILE__).'/output_forum.php');

/**
* Box to display forum content.
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/
class box_forum extends base_actionbox {

  /**
  * Module parameter name
  * @var string
  */
  public $paramName = 'ff';

  /**
  * Edit groups and fields to configure the module.
  * @var array $editGroups
  */
  public $editGroups = array(
    array(
      'Main settings',
      'items-page',
      array(
        'title' => array('Title', 'isNoHTML', TRUE, 'input', 200),
        'forum' => array(
          'Forum',
          'isNoHTML',
          TRUE,
          'function',
          'getForumCombo',
          'Please select the forum or category to display.'
        ),
        'mode' => array('Mode', 'isNum', TRUE, 'combo',
          array(0 => 'Threaded', 1 => 'BBS', 2 => 'Threaded BBS'),
          'Threaded means display one post and the tree of the discussion,
            BBS means display linear list of posts sorted by date,
            Threaded BBS means display the tree of discussion with the full posts.',
          0
        ),
        'sort' => array(
          'Sort order',
          'isNoHTML',
          TRUE,
          'translatedcombo',
          array('ASC' => 'Date ascending', 'DESC' => 'Date descending'),
          'Comment sort order',
          'DESC'
        ),
        'text',
        'nl2br' => array(
          'Automatic linebreak',
          'isNum',
          FALSE,
          'yesno',
          NULL,
          'Apply linebreaks from input to the HTML output.',
          0
        ),
        'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10),
        'Page splitting',
        'pages' => array('Number of pages', 'isNum', TRUE, 'input', 4, NULL, 10),
        'perpage' => array('Threads per page', 'isNum', TRUE, 'input', 4, NULL, 30),
        'bbsperpage' => array('Entries per page', 'isNum', TRUE, 'input', 4, NULL, 20),
        'Options',
        'richtext_enabled' => array('Use richtext editor?', 'isNum', TRUE, 'yesno', NULL, NULL, 1),
        'search_enabled' => array('Show search form?', 'isNum', TRUE, 'yesno', NULL, NULL, 1),
        'email_mandatory' => array('Email as mandatory?', 'isNum', TRUE, 'yesno', NULL, NULL, 0),
        'special_group' => array(
          'Moderator group',
          'isNum',
          TRUE,
          'function',
          'getSurferGroupCombo',
          'Members of the selected community group will be marked in the XML output.',
          0
        ),
        'Captchas',
        'use_captcha' => array('Use captcha?', 'isNum', TRUE, 'yesno', NULL, NULL, 0),
        'captcha_title' => array('Field title', 'isNoHTML', FALSE, 'input', 200),
        'captcha_type' => array(
          'Captcha image', 'isSomeText', FALSE, 'function', 'getCaptchasCombo'
        )
      )
    ),
    array(
      'Permissions',
      'items-permission',
      array(
        'Permissions',
        'post_answers' => array(
          'Post answers', 'isNum', TRUE, 'function', 'getPermsCombo', NULL, -1
        ),
        'post_questions' => array(
          'Post questions', 'isNum', TRUE, 'function', 'getPermsCombo', NULL, -1
        ),
        'subscribe_threads' => array(
          'Subscribe threads', 'isNum', TRUE, 'function', 'getPermsCombo', NULL, -1
        )
      )
    ),
    array(
      'Captions',
      'items-dialog',
      array(
        'caption_registered' => array(
          'Registered', 'isNoHTML', TRUE, 'input', 50, NULL, 'Since'
        ),
        'caption_threads' => array(
          'Threads', 'isNoHTML', TRUE, 'input', 50, NULL, 'Threads'
        ),
        'caption_newanswer' => array(
          'Title - answer', 'isNoHTML', TRUE, 'input', 50, NULL, 'Answer'
        ),
        'caption_newquestion' => array(
          'Title - question', 'isNoHTML', TRUE, 'input', 50, NULL, 'Question'
        ),
        'caption_search' => array(
          'Search', 'isNoHTML', TRUE, 'input', 50, NULL, 'Search'
        ),
        'caption_username' => array(
          'Username', 'isNoHTML', TRUE, 'input', 50, NULL, 'Username'
        ),
        'caption_useremail' => array(
          'User EMail', 'isNoHTML', TRUE, 'input', 50, NULL, 'E-Mail'
        ),
        'caption_subject' => array(
          'Subject', 'isNoHTML', TRUE, 'input', 50, NULL, 'Subject'
        ),
        'caption_text' => array(
          'Text', 'isSomeText', TRUE, 'input', 50, NULL, 'Text'
        ),
        'caption_submit' => array(
          'Submit', 'isNoHTML', TRUE, 'input', 50, NULL, 'Submit'
        ),
        'caption_cancel' => array(
          'Cancel', 'isNoHTML', TRUE, 'input', 50, NULL, 'Cancel'
        ),
        'caption_back' => array(
          'Back', 'isNoHTML', TRUE, 'input', 50, NULL, 'Back'
        ),
        'caption_categories' => array(
          'Categories', 'isNoHTML', TRUE, 'input', 50, NULL, 'Categories'
        ),
        'caption_forums' => array(
          'Forums', 'isNoHTML', TRUE, 'input', 50, NULL, 'Forums'
        ),
        'caption_question' => array(
          'Question', 'isNoHTML', TRUE, 'input', 50, NULL, 'Question'
        ),
        'caption_newentry' => array(
          'New entry', 'isNoHTML', TRUE, 'input', 50, NULL, 'New entry'
        ),
        'caption_lastentry' => array(
          'Last entry', 'isNoHTML', TRUE, 'input', 50, NULL, 'Last entry'
        ),
        'caption_newquestion' => array(
          'New question', 'isNoHTML', TRUE, 'input', 50, NULL, 'New question'
        ),
        'caption_subscribe' => array(
          'Subscribe', 'isNoHTML', TRUE, 'input', 50, NULL, 'Subscribe'
        ),
        'caption_unsubscribe' => array(
          'Unsubscribe', 'isNoHTML', TRUE, 'input', 50, NULL, 'Unsubscribe'
        ),
        'caption_view' => array(
          'View', 'isNoHTML', TRUE, 'input', 50, NULL, 'View'
        ),
        'caption_edit' => array(
          'Edit', 'isNoHTML', TRUE, 'input', 50, NULL, 'Edit'
        ),
        'caption_cite' => array(
          'Cite', 'isNoHTML', TRUE, 'input', 50, NULL, 'Cite'
        ),
        'caption_first' => array(
          'First', 'isNoHTML', TRUE, 'input', 50, NULL, 'First'
        ),
        'caption_last' => array(
          'Last', 'isNoHTML', TRUE, 'input', 50, NULL, 'Last'
        ),
        'caption_at' => array(
          'At', 'isNoHTML', TRUE, 'input', 50, NULL, 'At'
        ),
        'caption_from' => array(
          'From', 'isNoHTML', TRUE, 'input', 50, NULL, 'From'
        ),
        'caption_lastentries' => array(
          'Last entries', 'isNoHTML', TRUE, 'input', 50, NULL, 'Last entries'
        ),
        'caption_entries' => array(
          'Entries', 'isNoHTML', TRUE, 'input', 50, NULL, 'Entries'
        ),
        'caption_lastchange' => array(
          'Last change', 'isNoHTML', TRUE, 'input', 50, NULL, 'Last change'
        ),
        'caption_submit' => array(
          'Submit', 'isNoHTML', TRUE, 'input', 50, NULL, 'Submit'
        ),
        'caption_cancel' => array(
          'Cancel', 'isNoHTML', TRUE, 'input', 50, NULL, 'Cancel'
        ),
        'caption_category' => array(
          'Category', 'isNoHTML', TRUE, 'input', 50, NULL, 'Category'
        ),
        'caption_description' => array(
          'Description', 'isNoHTML', TRUE, 'input', 50, NULL, 'Description'
        ),
        'caption_searchresults' => array(
          'Search Results', 'isNoHTML', TRUE, 'input', 50, NULL, 'Search Results'
        )
      )
    ),
    array(
      'Messages and errors',
      'items-message',
      array(
        'Messages',
        'msg_noresult' => array(
          'No result', 'isSomeText', TRUE, 'textarea', 4, NULL, 'No threads found.'
        ),
        'msg_invalid' => array(
          'Invalid search string', 'isSomeText', TRUE, 'textarea', 4, NULL, 'Invalid search string.'
        ),
        'Errors',
        'error_database' => array(
          'Database error', 'isSomeText', TRUE, 'input', 200, NULL, 'Error saving entry.'
        ),
        'error_doublepost' => array(
          'Doublepost error', 'isSomeText', TRUE, 'input', 200, NULL, 'Double post prevented!'
        ),
        'error_permission' => array(
          'Permission error', 'isSomeText', TRUE, 'input', 200, NULL, 'Operation not allowed!'
        ),
        'error_spam' => array(
          'Spamfilter', 'isSomeText', TRUE, 'input', 200, NULL, 'Spam filter alert!'
        ),
        'error_input' => array(
          'Input error', 'isSomeText', TRUE, 'input', 200, NULL, 'Input error'
        ),
        'error_subject' => array(
          'Subject error', 'isSomeText', TRUE, 'input', 200, NULL, 'Please enter a valid subject'
        ),
        'error_body' => array(
          'Body error', 'isSomeText', TRUE, 'input', 200, NULL, 'Please enter a valid text'
        ),
        'error_username' => array(
          'User name error', 'isSomeText', TRUE, 'input', 200, NULL, 'Please enter a valid name'
        ),
        'error_useremail' => array(
          'User email error', 'isSomeText', TRUE, 'input', 200, NULL, 'Please enter a valid email'
        ),
        'error_captcha' => array(
          'Captcha error', 'isSomeText', TRUE, 'input', 200, NULL, 'Please enter the correct code'
        ),
        'Abuse',
        'abuse_entry_text' => array(
          'Blocked Entry text', 'isSomeText', TRUE, 'simplerichtext', 4, NULL, 'Entry blocked.'
        ),
        'abuse_hint_text' => array(
          'Report abuse text', 'isSomeText', TRUE, 'simplerichtext', 4, NULL, 'Entry blocked.'
        )
      )
    ),
    array(
      'Mail settings',
      'items-mail',
      array(
        'Admin notification',
        'admin_sendmails' => array('Send moderator emails', 'isNum', TRUE, 'yesno', NULL, NULL, 0),
        'admin_name' => array(
          'Moderator name',
          'isNoHTML',
          TRUE,
          'input',
          50,
          NULL,
          'Forum moderator'
        ),
        'admin_email' => array(
          'Moderator email',
          'isEMail',
          TRUE,
          'input',
          50,
          NULL,
          'moderator@example.com'
        ),
        'Subscription email',
        'subscriber_sendmails' => array(
          'Send subscriber emails', 'isNum', TRUE, 'yesno', NULL, NULL, 1
        ),
        'email_from_name' => array(
          'From name',
          'isNoHTML',
          TRUE,
          'input',
          50,
          NULL,
          'Forum'
        ),
        'email_from_email' => array(
          'From email',
          'isEMail',
          TRUE,
          'input',
          100,
          NULL,
          'forum@example.com'
        ),
        'Email content',
        'email_subject' => array(
          'Email subject',
          'isNoHTML',
          TRUE,
          'input',
          200,
          'See below for possible place holders.',
          'New entry in forum'
        ),
        'email_text' => array(
          'Email text',
          'isSomeText',
          TRUE,
          'textarea',
          10,
          'Possible placeholders:
            {%NOTIFY%} := ADMINISTRATOR,SUBSCRIPTION , {%RECEIVER%},
            {%ENTRY_AUTHOR_NAME%}, {%ENTRY_AUTHOR_HANDLE%},
            {%ENTRY_SUBJECT%}, {%LINK%}, {%PROJECT%},
            {%ENTRY_CHANGED%}, {%ENTRY_CREATED%}, {%ENTRY_TEXT%}',
          '{%NOTIFY%}, {%RECEIVER%},
            {%ENTRY_AUTHOR_NAME%}, {%ENTRY_AUTHOR_HANDLE%},
            {%ENTRY_SUBJECT%}, {%LINK%}, {%PROJECT%},
            {%ENTRY_CHANGED%}, {%ENTRY_CREATED%}'
        )
      )
    ),
    array(
      'Avatar settings',
      'items-user',
      array(
        'Avatar image size',
        'avatar_width' => array('Width', 'isNum', TRUE, 'input', 4, NULL, 160),
        'avatar_height' => array('Height', 'isNum', TRUE, 'input', 4, NULL, 200)
      )
    )
  );

  /**
  * Contains the final purpose of box module in derivated class.
  * @var integer
  */
  protected $_purpose = NULL;

  /***************************************************************************/
  /** Parsed data                                                            */
  /***************************************************************************/

  /**
  * Get parsed data.
  *
  * @return string
  */
  public function getParsedData() {
    $this->setDefaultData();

    $moduleConfiguration = $this->data;
    $moduleConfiguration['purpose'] = $this->_purpose;
    $moduleConfiguration['post_questions'] = ($this->data['post_questions'] > 0)
      ? $this->checkSurferPerm((int)$this->data['post_questions']) : TRUE;
    $moduleConfiguration['post_answers'] = ($this->data['post_answers'] > 0)
      ? $this->checkSurferPerm((int)$this->data['post_answers']) : TRUE;
    $moduleConfiguration['subscribe_threads'] = ($this->data['subscribe_threads'] > 0)
      ? $this->checkSurferPerm((int)$this->data['subscribe_threads']) : TRUE;

    include_once(dirname(__FILE__).'/output_forum.php');
    $forumOutput = new output_forum();
    $forumOutput->setPageData(
      !empty($this->parentObj->topicId) ? $this->parentObj->topicId : NULL,
      !empty($this->parentObj->topic['TRANSLATION']['topic_title']) ?
        $this->parentObj->topic['TRANSLATION']['topic_title'] : NULL
    );
    $forumOutput->initialize($this, $moduleConfiguration);

    $result = sprintf(
      '<forumbox>'.LF.
      '<title>%s</title>'.LF.
      '<text>%s</text>'.LF.
      '%s%s</forumbox>'.LF,
      isset($this->data['title']) ? papaya_strings::escapeHTMLChars($this->data['title']) : "",
      isset($this->data['text']) 
        ? $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br'])) : "",
      $forumOutput->getContentStatusXml(),
      $forumOutput->getOutput()
    );
    return $result;
  }

  /***************************************************************************/
  /** Helper methods                                                         */
  /***************************************************************************/

  /**
  * Get forum combo from output object.
  *
  * @param string $name
  * @param array $field
  * @param string $data
  * @return string xml
  */
  public function getForumCombo($name, $field, $data) {
    include_once(dirname(__FILE__).'/output_forum.php');
    $forumOutput = new output_forum();
    return $forumOutput->getForumCombo($name, $field, $data);
  }

  /**
  * Get surfer combo from output object.
  *
  * @param string $name
  * @param string $field
  * @param array $data
  * @return string xml
  */
  public function getSurferGroupCombo($name, $field, $data) {
    include_once(dirname(__FILE__).'/output_forum.php');
    $forumOutput = new output_forum();
    return $forumOutput->getSurferGroupCombo($name, $field, $data);
  }

  /**
  * Get captchas combo from output object.
  *
  * @param string $name string Name of the field.
  * @param array $field edit field parameters
  * @param string $data current value
  * @return string $result
  */
  public function getCaptchasCombo($name, $field, $data) {
    include_once(dirname(__FILE__).'/output_forum.php');
    $forumOutput = new output_forum();
    return $forumOutput->getCaptchasCombo($name, $field, $data);
  }

}