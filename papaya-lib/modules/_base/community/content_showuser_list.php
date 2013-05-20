<?php
/**
* Page module - Show user data as a list or one user in detail
*
* This module is an extension to content_showuser with the following additional
* functionality or improvements:
*
* * A list of users from a specified group is displayed if no user is selected
* * Only users in the specified group will be displayed
* * user id may no longer be in $_REQUEST, but has to be in $this->params
* * list by user name is no longer supported
* * sys_email is used to send emails instead of the php internal email() function
* * editFields conform to the coding standard -> not compatible with content_userdata
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
* @version $Id: content_showuser_list.php 37786 2012-12-07 14:54:02Z smekal $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - Show user data as a list or one user in detail and send messages by mail
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class content_showuser_list extends base_content {

  /**
  * Is cacheable?
  * @var boolean $cacheable
  */
  var $cacheable = FALSE;

  /**
  * Papaya database table surfer contacts
  * @var $tableContacts
  */
  var $tableContacts = PAPAYA_DB_TBL_SURFERCONTACTS;

  /**
  * Edit fields
  * @var array $editGroups
  */
  var $editGroups = array(
    array(
      'General',
      'categories-content',
      array(
        'msg_prefix' => array('Subject prefix', 'isNoHTML', TRUE, 'input', 30, '',
          PAPAYA_PROJECT_TITLE),
        'contact_depth' => array('Contact depth', 'isNum', TRUE, 'input', 30, '', 3),
        'Messages',
        'msg_bookmarked'=> array('User bookmarked', 'isNoHTML', TRUE, 'input', 200, '',
          'User %s bookmarked.'),
        'msg_unbookmarked'=> array('User bookmark removed', 'isNoHTML', TRUE, 'input', 200,
          '', 'Bookmark for user %s removed.'),
        'msg_blocked' => array('User blocked', 'isNoHTML', TRUE, 'input', 200, '',
          'User %s blocked.'),
        'msg_unblocked' => array('User unblocked', 'isNoHTML', TRUE, 'input', 200, '',
          'User %s unblocked.'),
        'unknown_user' => array('Unknown user', 'isNoHTML', TRUE, 'input', 200, '',
          'Unknown user'),
        'no_user_found' => array('No user found', 'isNoHTML', TRUE, 'input', 200, '',
          'No user found'),
        'input_error' => array('Input error', 'isNoHTML', TRUE, 'input', 200, '',
          'Input error'),
        'message_sent' => array('Message sent', 'isNoHTML', TRUE, 'input', 200, '',
          'Message sent'),
        'send_error' => array('Send error', 'isNoHTML', TRUE, 'input', 200, '',
          'Send error'),
        'user_group' => array('User group', 'isNum', TRUE, 'function',
          'callbackUserGroup'),
        'Show',
        'show_group' => array('Show group', 'isNum', TRUE, 'combo',
          array('0' => 'No', '1' => 'Yes'), '', 0),
        'show_name_as' => array('Show name as', 'isNoHTML', TRUE, 'combo',
          array('real' => 'Real name', 'nick' => 'Nickname', 'both' => 'Both'),
           '', 'real'),
        'show_email' => array('Show email', 'isNum', TRUE, 'combo',
          array('0' => 'No', '1' => 'Yes'), '', 1)
      )
    ),
    array(
      'Field captions',
      'items-message',
      array(
        'caption_name' => array('Name', 'isNoHTML', TRUE, 'input',
          50, '', 'Name'),
        'caption_email' => array('Email', 'isNoHTML', TRUE, 'input',
          50, '', 'E-Mail'),
        'caption_profile_data' => array('Profile data', 'isNoHTML', TRUE, 'input',
          50, '', 'Profile Data'),
        'caption_userdata' => array('User data',
          'isNoHTML', TRUE, 'input', 50, '', 'User data'),
        'caption_contactlist' => array('Contact list',
          'isNoHTML', TRUE, 'input', 50, '', 'Contact list'),
        'caption_contactrequests' => array('Contact requests',
          'isNoHTML', TRUE, 'input', 50, '', 'Contact requests'),
        'caption_blocked_users' => array('Blocked users',
          'isNoHTML', TRUE, 'input', 50, '', 'Blocked users'),
        'caption_bookmarked_users' => array('Bookmarked users',
          'isNoHTML', TRUE, 'input', 50, '', 'Bookmarked users'),
        'caption_contact' => array('Direct contact',
          'isNoHTML', TRUE, 'input', 50, '', 'This user is in your contact list'),
        'caption_contact_request_self' => array('Own contact request',
          'isNoHTML', TRUE, 'input', 50, '', 'You requested contact'),
        'caption_contact_request_other' => array('Contact requested by other surfer',
          'isNoHTML', TRUE, 'input', 50, '', 'This user requested contact'),
        'caption_contact_send_request' => array('Send contact request',
          'isNoHTML', TRUE, 'input', 50, '', 'Send contact request'),
        'caption_contact_remove' => array('Remove contact',
          'isNoHTML', TRUE, 'input', 50, '', 'Remove contact'),
        'caption_no_contact' => array('No contact',
          'isNoHTML', TRUE, 'input', 50, '', 'No contact'),
        'caption_requested' => array('Contact requested',
          'isNoHTML', TRUE, 'input', 50, '', 'requested'),
        'caption_accept' => array('Accept contact',
          'isNoHTML', TRUE, 'input', 50, '', 'accept'),
        'caption_decline' => array('Decline contact',
          'isNoHTML', TRUE, 'input', 50, '', 'decline'),
        'caption_publish' => array('Publish',
          'isNoHTML', TRUE, 'input', 50, '', 'publish'),
        'caption_publishall' => array('Publish all',
          'isNoHTML', TRUE, 'input', 50, '', 'Publish all'),
        'caption_yes' => array('Yes', 'isNoHTML', TRUE, 'input', 50, '', 'Yes'),
        'caption_no' => array('No', 'isNoHTML', TRUE, 'input', 50, '', 'No'),
      )
    ),
    array(
      'Messaging',
      'items-mail',
      array(
        'caption_send_message' => array('Send message', 'isNoHTML', TRUE,
          'input', 50, '', 'Send message'),
        'caption_subject' => array('Subject', 'isNoHTML', TRUE, 'input',
          50, '', 'Subject'),
        'caption_message' => array('Message', 'isNoHTML', TRUE, 'input',
          50, '', 'Message'),
        'caption_send' => array('Submit button', 'isNoHTML', TRUE, 'input',
          50, '', 'Send')
      )
    ),
    array(
      'Link captions',
      'items-link',
      array(
        'caption_listlink' => array('Back to list',
          'isNoHTML', TRUE, 'input', 50, '', 'Back to list'),
        'caption_contactlink' => array('Trace contact',
          'isNoHTML', TRUE, 'input', 50, '', 'Show extended contacts'),
        'caption_hidelink' => array('Hide extended contacts',
          'isNoHTML', TRUE, 'input', 50, '', 'Hide extended contacts'),
        'caption_publishlink' => array('Show publish form',
          'isNoHTML', TRUE, 'input', 50, '', 'Show publish form'),
        'caption_link_bookmark' => array('Bookmark user',
          'isNoHTML', TRUE, 'input', 50, '', 'Bookmark user'),
        'caption_link_unbookmark' => array('Remove bookmark',
          'isNoHTML', TRUE, 'input', 50, '', 'Remove bookmark'),
        'caption_link_block' => array('Block user',
          'isNoHTML', TRUE, 'input', 50, '', 'Block user'),
        'caption_link_unblock' => array('Unblock user',
          'isNoHTML', TRUE, 'input', 50, '', 'Unblock user')
      )
    )
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();
    $result = '';
    $this->initializeParams();
    include_once(dirname(__FILE__).'/base_surfers.php');
    $surferAdmin = new surfer_admin($this->msgs);
    $userHandle = '';
    if (isset($this->params['handle']) && $this->params['handle'] != '') {
      $userHandle = (string)$this->params['handle'];
    }
    if (isset($this->params['contact']) && $this->params['contact'] != '') {
      $userHandle = (string)$this->params['contact'];
    } elseif (isset($this->params['user_handle']) && $this->params['user_handle'] != '') {
      $userHandle = (string)$this->params['user_handle'];
    }

    if ($userHandle != '') {
      // Handle block/bookmark requests
      if ($this->surferObj->isValid) {
        $contactId = $surferAdmin->getIdByHandle($userHandle);
        if (isset($this->params['block_cmd'])) {
          if ($this->params['block_cmd'] == 'block') {
            $surferAdmin->blockSurfer(
              $this->surferObj->surferId, $contactId, TRUE
            );
            $result .= '<block-message>';
            $result .= papaya_strings::escapeHTMLChars(
              sprintf(
                $this->data['msg_blocked'],
                $userHandle
              )
            );
            $result .= '</block-message>';
          } elseif ($this->params['block_cmd'] == 'unblock') {
            $surferAdmin->blockSurfer(
              $this->surferObj->surferId, $contactId, FALSE
            );
            $result .= '<block-message>';
            $result .= papaya_strings::escapeHTMLChars(
              sprintf(
                $this->data['msg_unblocked'],
                $userHandle
              )
            );
            $result .= '</block-message>';
          }
        }
        if (isset($this->params['bookmark_cmd'])) {
          if ($this->params['bookmark_cmd'] == 'bookmark') {
            $surferAdmin->bookmarkSurfer(
              $this->surferObj->surferId, $contactId, TRUE
            );
            $result .= '<bookmark-message>';
            $result .= papaya_strings::escapeHTMLChars(
              sprintf(
                $this->data['msg_bookmarked'],
                $userHandle
              )
            );
            $result .= '</bookmark-message>';
          } elseif ($this->params['bookmark_cmd'] == 'unbookmark') {
            $surferAdmin->bookmarkSurfer(
              $this->surferObj->surferId, $contactId, FALSE
            );
            $result .= '<bookmark-message>';
            $result .= papaya_strings::escapeHTMLChars(
              sprintf(
                $this->data['msg_unbookmarked'],
                $userHandle
              )
            );
            $result .= '</bookmark-message>';
          }
        }
      }

      $surferAdmin->loadSurfer($surferAdmin->getIdByHandle($userHandle));
      if ($surferAdmin->editSurfer['surfergroup_id'] == $this->data['user_group']) {
        $result .= '<userdata>';
        if (isset($this->data['show_name_as']) &&
            $this->data['show_name_as'] == 'nick') {
          $result .= sprintf(
            '<handle>%s</handle>'.LF,
            papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfer_handle'])
          );
        } elseif (isset($this->data['show_name_as']) &&
                  $this->data['show_name_as'] == 'real') {
          $result .= sprintf(
            '<name>%s %s</name>'.LF,
            papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfer_givenname']),
            papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfer_surname'])
          );
        } elseif (isset($this->data['show_name_as']) &&
                  $this->data['show_name_as'] == 'both') {
          $result .= sprintf(
            '<name>%s (%s %s)</name>'.LF,
            papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfer_handle']),
            papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfer_givenname']),
            papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfer_surname'])
          );
        }
        if (isset($this->data['show_email']) &&
            $this->data['show_email'] && isset($this->surferObj->surfer)) {
          
          include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
          $this->emailObj = new email();
          
          if (isset($_POST['submit'])) {
            $inputError = FALSE;
            if (empty($this->params['mail_subject']) ||
            !checkit::isSomeText($this->params['mail_subject'], TRUE)) {
              $inputError = TRUE;
            }
            if (empty($this->params['mail_text']) ||
            !checkit::isSomeText($this->params['mail_text'], TRUE)) {
              $inputError = TRUE;
            }
            if ($inputError) {
              $result .= sprintf(
                '<message>%s</message>',
                $this->data['input_error']
              );
              $result .= $this->getMailDialog($userHandle);
            } else {
              $subject = $this->params['mail_subject'];
              $body = $this->params['mail_text'];
              $recipient = $surferAdmin->editSurfer['surfer_email'];
              $this->emailObj->setSender($this->surferObj->surfer['surfer_email']);
              if ($this->emailObj->send($recipient, $subject, $body)) {
                $result .= sprintf(
                  '<message>%s</message>',
                  $this->data['message_sent']
                );
              } else {
                $result .= sprintf(
                  '<message>%s</message>',
                  $this->data['send_error']
                );
              }
              $result .= sprintf(
                '<email href="%s">%s</email>'.LF,
                papaya_strings::escapeHTMLChars(
                  $this->getWebLink(
                      NULL,
                      NULL,
                      NULL,
                      array(
                          'user_handle' => $userHandle
                      ),
                      $this->paramName
                  )
              ),
                $this->data['caption_send_message']
              );
            }
          } else {
            $result .= $this->getMailDialog($userHandle);
          }
        }
        if (isset($this->data['show_group']) &&
            $this->data['show_group']) {
          $result .= sprintf(
            '<group>%s</group>'.LF,
            papaya_strings::escapeHTMLChars($surferAdmin->editSurfer['surfergroup_title'])
          );
        }
        // Blocking and bookmark management -- only if we're logged in
        if ($this->surferObj->isValid) {
          $contactId = $surferAdmin->getIdByHandle($userHandle);
          // Check whether this surfer is blocked or not and
          // create an appropriate blocking link
          if ($surferAdmin->isBlocked($this->surferObj->surferId, $contactId)) {
            $result .= sprintf(
              '<block href="%s" caption="%s"/>',
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(
                  NULL,
                  NULL,
                  NULL,
                  array(
                    'block_cmd' => 'unblock',
                    'user_handle' => $userHandle
                  ),
                  $this->paramName
                )
              ),
              papaya_strings::escapeHTMLChars($this->data['caption_link_unblock'])
            );
          } else {
            $result .= sprintf(
              '<block href="%s" caption="%s"/>',
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(
                  NULL,
                  NULL,
                  NULL,
                  array(
                    'block_cmd' => 'block',
                    'user_handle' => $userHandle
                  ),
                  $this->paramName
                )
              ),
              papaya_strings::escapeHTMLChars($this->data['caption_link_block'])
            );
          }
          // Check whether this surfer is bookmarked or not and
          // create an appropriate bookmarking link
          if ($surferAdmin->isBookmarked($this->surferObj->surferId, $contactId)) {
            $result .= sprintf(
              '<bookmark href="%s" caption="%s"/>',
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(
                  NULL,
                  NULL,
                  NULL,
                  array(
                    'bookmark_cmd' => 'unbookmark',
                    'user_handle' => $userHandle
                  ),
                  $this->paramName
                )
              ),
              papaya_strings::escapeHTMLChars(
                $this->data['caption_link_unbookmark']
              )
            );
          } else {
            $result .= sprintf(
              '<bookmark href="%s" caption="%s"/>',
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(
                  NULL,
                  NULL,
                  NULL,
                  array(
                    'bookmark_cmd' => 'bookmark',
                    'user_handle' => $userHandle
                  ),
                  $this->paramName
                )
              ),
              papaya_strings::escapeHTMLChars(
                $this->data['caption_link_bookmark']
              )
            );
          }
        }
        $profileData = $this->getProfileData($userHandle);
        if ($profileData) {
          $result .= sprintf(
            '<profiledata caption="%s">',
            papaya_strings::escapeHTMLChars($this->data['caption_profile_data'])
          );
          foreach ($profileData as $fieldName => $fieldData) {
            if (isset($fieldData['value']) && trim($fieldData['value']) != '') {
              $result .= sprintf(
                '<field id="%s" caption="%s" type="%s">'.LF,
                papaya_strings::escapeHTMLChars($fieldName),
                papaya_strings::escapeHTMLChars($fieldData['title']),
                papaya_strings::escapeHTMLChars($fieldData['type'])
              );
              if (!empty($fieldData['values'])) {
                $result .= sprintf(
                  '<values>%s</values>',
                  papaya_strings::escapeHTMLChars($fieldData['values'])
                );
              }
              $result .= sprintf(
                '<value>%s</value>',
                papaya_strings::escapeHTMLChars($fieldData['value'])
              );
              $result .= '</field>';
            }
          }
          $result .= '</profiledata>';
        }
      }
      if (isset($this->params['request']) && $this->params['request'] != '') {
        $this->addContactRequest($this->params['handle']);
      } elseif (isset($this->params['accept']) && $this->params['accept'] != '') {
        $this->handleContactReply($this->params['handle']);
      } elseif (isset($this->params['decline']) && $this->params['decline'] != '') {
        $this->handleContactReply($this->params['handle']);
      } elseif (isset($this->params['publish']) && $this->params['publish'] != '') {
        $this->storePublishSettings($this->params['handle']);
      }
      $result .= $this->getContactInfo($surferAdmin->editSurfer['surfer_handle']);
      $showForm = TRUE;
    } elseif (isset($this->data['user_group']) && $this->data['user_group'] > 0) {
      if ($list = $this->getContactRequests()) {
        $result .= sprintf(
          '<contactrequests caption="%s">',
          papaya_strings::escapeHTMLChars($this->data['caption_contactrequests'])
        );
        foreach ($list as $contactHandle => $contactData) {
          $result .= sprintf(
            '<request href="%s" givenname="%s" surname="%s"/>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getWebLink(
                NULL,
                NULL,
                NULL,
                array('user_handle' => $contactHandle),
                $this->paramName
              )
            ),
            papaya_strings::escapeHTMLChars($contactData['givenname']),
            papaya_strings::escapeHTMLChars($contactData['surname'])
          );
        }
        $result .= '</contactrequests>';
      }
      if ($list = $this->getContacts()) {
        $result .= sprintf(
          '<contactlist caption="%s" caption-requested="%s">',
          papaya_strings::escapeHTMLChars($this->data['caption_contactlist']),
          papaya_strings::escapeHTMLChars($this->data['caption_requested'])
        );
        foreach ($list as $contactHandle => $contactData) {
          $result .= sprintf(
            '<contact href="%s" status="%s" givenname="%s" surname="%s"/>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getWebLink(
                NULL,
                NULL,
                NULL,
                array('user_handle' => $contactHandle),
                $this->paramName
              )
            ),
            papaya_strings::escapeHTMLChars($contactData['status']),
            papaya_strings::escapeHTMLChars($contactData['givenname']),
            papaya_strings::escapeHTMLChars($contactData['surname'])
          );
        }
        $result .= '</contactlist>';
      }
      if ($list = $this->getBlocks()) {
        $result .= sprintf(
          '<blocks caption="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->data['caption_blocked_users'])
        );
        foreach ($list as $surferHandle) {
          $result .= sprintf(
            '<user href="%s">%s</user>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getWebLink(
                NULL,
                NULL,
                NULL,
                array('user_handle' => $surferHandle)
              )
            ),
            papaya_strings::escapeHTMLChars($surferHandle)
          );
        }
        $result .= '</blocks>';
      }
      if ($list = $this->getBookmarks()) {
        $result .= sprintf(
          '<bookmarks caption="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->data['caption_bookmarked_users'])
        );
        foreach ($list as $surferHandle) {
          $result .= sprintf(
            '<user href="%s">%s</user>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getWebLink(
                NULL,
                NULL,
                NULL,
                array('user_handle' => $surferHandle),
                $this->paramName
              )
            ),
            papaya_strings::escapeHTMLChars($surferHandle)
          );
        }
        $result .= '</bookmarks>';
      }
      if ($list = $this->loadSurferList($this->data['user_group'])) {
        $result .= sprintf(
          '<userdata caption="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->data['caption_userdata'])
        );
        foreach ($list as $surferHandle => $surfer) {
          $result .= sprintf(
            '<user href="%s" givenname="%s" surname="%s"/>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getWebLink(
                NULL,
                NULL,
                NULL,
                array('user_handle' => $surferHandle),
                $this->paramName
              )
            ),
            papaya_strings::escapeHTMLChars($surfer['surfer_givenname']),
            papaya_strings::escapeHTMLChars($surfer['surfer_surname'])
          );
        }
      } else {
        $result .= '<userdata>';
        $result .= sprintf(
          '<message>%s</message>',
          papaya_strings::escapeHTMLChars($this->data['no_user_found'])
        );
      }
    } else {
      $result .= '<userdata>';
      $result .= sprintf(
        '<message>%s</message>',
        papaya_strings::escapeHTMLChars($this->data['unknown_user'])
      );
    }
    if (trim($result) != '') {
      $result .= sprintf(
        '<listlink href="%s" caption="%s"/>',
        papaya_strings::escapeHTMLChars(
          $this->getWebLink()
        ),
        papaya_strings::escapeHTMLChars($this->data['caption_listlink'])
      );
      $result .= '</userdata>';
    }
    return $result;
  }

  /**
   * Get mail dialog
   *
   * @access public
   * @return string xml
   */
  function getMailDialog($userHandle) {
    $result = '';
    $result .= sprintf(
        '<form action="%s" method="post">',
        papaya_strings::escapeHTMLChars(
            $this->getWebLink(
                NULL,
                NULL,
                NULL,
                array('user_handle' => $userHandle),
                $this->paramName
            )
        )
    );
    $result .= sprintf(
        '<label for="%s[mail_subject]">%s</label>',
        papaya_strings::escapeHTMLChars($this->paramName),
        $this->data['caption_subject']
    );
    $result .= sprintf(
        '<input type="text" name="%s[mail_subject]" value="" class="text" />',
        papaya_strings::escapeHTMLChars($this->paramName)
    );
    $result .= sprintf(
        '<label for="%s[mail_text]">%s</label>',
        papaya_strings::escapeHTMLChars($this->paramName),
        $this->data['caption_message']
    );
    $result .= sprintf(
        '<textarea name="%s[mail_text]" class="text"></textarea>',
        papaya_strings::escapeHTMLChars($this->paramName)
    );
    $result .= sprintf(
        '<button type="submit" name="submit">%s</button>',
        $this->data['caption_send']
    );
    $result .= '</form>';
    return $result;
  }
  
  /**
  * load list of surfers in specified group that are valid
  *
  * @param integer $groupId group id
  * @return array $result list of surfers (surfer_handle => array(surfer_data))
  */
  function loadSurferList($groupId) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $surfer = new base_surfer;
    // Exclude current surfer if valid
    if ($this->surferObj->isValid) {
      $id = $this->surferObj->surfer['surfer_id'];
    } else {
      $id = '';
    }
    $result = array();
    $sql = "SELECT surfer_handle, surfer_givenname, surfer_surname,
                   surfer_valid, surfer_email, surfergroup_id
              FROM %s
             WHERE surfergroup_id = %d
               AND surfer_id != '%s'
               AND surfer_valid = 1
             ORDER BY surfer_surname, surfer_givenname, surfer_handle";
    $params = array($surfer->tableSurfers, $groupId, $id);
    if ($res = $surfer->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['surfer_handle']] = $row;
      }
    }
    return $result;
  }

  /**
  * Load list of surfers with contact to current one
  *
  * @return array $result list of surfers (surfer_handle =>
                  array (surfer's real name + contact status))
  */
  function getContacts() {
    include_once(dirname(__FILE__).'/base_surfers.php');
    $surferAdmin = new surfer_admin($this->msgs);
    $result = array();
    if ($this->surferObj->isValid) {
      $sql = "SELECT s.surfer_id, s.surfer_handle,
                     s.surfer_givenname,
                     s.surfer_surname,
                     sc.surfercontact_status
                FROM %s AS s, %s AS sc
               WHERE s.surfer_id=sc.surfercontact_requestor
                 AND sc.surfercontact_requested='%s'
                 AND sc.surfercontact_status > 0
            ORDER BY s.surfer_surname, s.surfer_givenname,
                     s.surfer_handle";
      $dbParams = array($surferAdmin->tableSurfer,
                        $surferAdmin->tableContacts,
                        $this->surferObj->surfer['surfer_id']);
      $res = $surferAdmin->databaseQueryFmt($sql, $dbParams);
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['surfer_handle']] = array(
          'status' => $row['surfercontact_status'],
          'surname' => $row['surfer_surname'],
          'givenname' => $row['surfer_givenname'],
        );
      }
      $sql = "SELECT s.surfer_id, s.surfer_handle,
                     s.surfer_givenname,
                     s.surfer_surname,
                     s.surfer_email,
                     sc.surfercontact_status
                FROM %s AS s, %s AS sc
               WHERE s.surfer_id=sc.surfercontact_requested
                 AND sc.surfercontact_requestor='%s'
                 AND sc.surfercontact_status > 0";
      $dbParams = array($surferAdmin->tableSurfer,
                        $surferAdmin->tableContacts,
                        $this->surferObj->surfer['surfer_id']);
      $res = $surferAdmin->databaseQueryFmt($sql, $dbParams);
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['surfer_handle']] = array(
          'status' => $row['surfercontact_status'],
          'surname' => $row['surfer_surname'],
          'givenname' => $row['surfer_givenname'],
        );
      }
    }
    return $result;
  }

  /**
  * Get contact requests sent to current surfer
  *
  * @return array $result list of surfers (surfer_handle =>
  *               array (surfer's real name))
  */
  function getContactRequests() {
    include_once(dirname(__FILE__).'/base_surfers.php');
    $surferAdmin = new surfer_admin($this->msgs);
    $result = array();
    if ($this->surferObj->isValid) {
      $sql = "SELECT s.surfer_id, s.surfer_handle,
                     s.surfer_givenname,
                     s.surfer_surname,
                     sc.surfercontact_status
                FROM %s AS s, %s AS sc
               WHERE s.surfer_id=sc.surfercontact_requestor
                 AND sc.surfercontact_requested='%s'
                 AND sc.surfercontact_status = 1";
      $dbParams = array($surferAdmin->tableSurfer,
                        $surferAdmin->tableContacts,
                        $this->surferObj->surfer['surfer_id']);
      $res = $surferAdmin->databaseQueryFmt($sql, $dbParams);
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['surfer_handle']] = array(
          'surname' => $row['surfer_surname'],
          'givenname' => $row['surfer_givenname'],
        );
      }
    }
    return $result;
  }

  /**
  * Load a list of blocked users
  *
  * @access public
  * @return mixed NULL or array
  */
  function getBlocks() {
    if ($this->surferObj->isValid) {
      include_once(dirname(__FILE__).'/base_surfers.php');
      $surferAdmin = new surfer_admin($this->msgs);
      $ids = $surferAdmin->getBlocks($this->surferObj->surferId);
      $list = array();
      foreach ($ids as $id) {
        array_push($list, $surferAdmin->getHandleById($id));
      }
      return $list;
    } else {
      return NULL;
    }
  }

  /**
  * Load a list of bookmarked users
  *
  * @access public
  * @return mixed NULL or array
  */
  function getBookmarks() {
    if ($this->surferObj->isValid) {
      include_once(dirname(__FILE__).'/base_surfers.php');
      $surferAdmin = new surfer_admin($this->msgs);
      $ids = $surferAdmin->getBookmarks($this->surferObj->surferId);
      $list = array();
      foreach ($ids as $id) {
        array_push($list, $surferAdmin->getHandleById($id));
      }
      return $list;
    } else {
      return NULL;
    }
  }

  /**
  * Get a surfer's profile data
  *
  * If a field needs approval, it will only be displayed
  * for personal contacts who have got that approval;
  * all data that doesn't need approval will be shown
  * without any test
  *
  * @param string $handle -- a surfer handle
  * @return array $result -- list of field => array(caption, value) pairs
  */
  function getProfileData($handle) {
    include_once(dirname(__FILE__).'/base_surfers.php');
    $surferAdmin = new surfer_admin($this->msgs);
    // In the beginning, there's no data yet
    $result = array();
    // Get current content language
    $lng = $this->parentObj->topic['TRANSLATION']['lng_id'];
    // Get the surfer's id
    $surferId = $surferAdmin->getIdByHandle($handle);
    // First, get all the fields, their titles in current content language
    // and default approval status
    $sql = "SELECT s.surferdata_id, s.surferdata_name,
                   s.surferdata_class, s.surferdata_needsapproval,
                   s.surferdata_type, s.surferdata_values,
                   st.surferdatatitle_field, st.surferdatatitle_lang,
                   st.surferdatatitle_title
              FROM %s AS s, %s AS st
             WHERE s.surferdata_id=st.surferdatatitle_field
               AND st.surferdatatitle_lang='%s'
          ORDER BY s.surferdata_class, s.surferdata_order,
                   st.surferdatatitle_title";
    $sqlParams = array($surferAdmin->tableData,
                       $surferAdmin->tableDataTitles,
                       $lng
                      );
    $res = $surferAdmin->databaseQueryFmt($sql, $sqlParams);
    $fields = array();
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      // Set field to public if it doesn't need approval
      if ($row['surferdata_needsapproval'] == 1) {
        $public = FALSE;
      } else {
        $public = TRUE;
      }
      $fields[$row['surferdata_name']] =
        array('class' => $row['surferdata_class'],
              'id' => $row['surferdata_id'],
              'type' => $row['surferdata_type'],
              'values' => $row['surferdata_values'],
              'public' => $public,
              'title' => $row['surferdatatitle_title']
             );
    }
    // If a field is not public yet,
    // check whether it has been approved to be published
    // to the currently logged in surfer
    // (of course only if there is a valid login)
    if ($this->surferObj->isValid) {
      foreach ($fields as $fieldName => $fieldData) {
        if (!$fieldData['public']) {
          $isql = "SELECT surfercontactpublic_public
                     FROM %s
                    WHERE surfercontactpublic_surferid='%s'
                      AND surfercontactpublic_partner='%s'
                      AND surfercontactpublic_data=%d";
          $isqlParams = array(
            $surferAdmin->tableContactPublic, $surferId,
            $this->surferObj->surfer['surfer_id'], $fieldData['id']);
          $ires = $surferAdmin->databaseQueryFmt($isql, $isqlParams);
          if ($pub = $ires->fetchField()) {
            if ($pub == 1) {
              $fields[$fieldName]['public'] = TRUE;
            }
          }
        }
      }
    }
    // Retrieve data for all public fields
    // and push these fields into result
    foreach ($fields as $fieldName => $fieldData) {
      if ($fieldData['public']) {
        $fsql = "SELECT surfercontactdata_value
                   FROM %s
                  WHERE surfercontactdata_property=%d
                        AND surfercontactdata_surferid='%s'";
        $fsqlParams = array(
          $surferAdmin->tableContactData, $fieldData['id'], $surferId);
        $fres = $surferAdmin->databaseQueryFmt($fsql, $fsqlParams);
        if ($val = $fres->fetchField()) {
          $fields[$fieldName]['value'] = $val;
        }
        $result[$fieldName] = $fields[$fieldName];
      }
    }
    return $result;
  }

  /**
  * Get publish profile data
  *
  * Current publishing settings:
  * - Existing settings for a certain surfer
  * - Fallback I:  The current surfer's general publishing settings
  * - Fallback II: Global defaults
  *
  * @param string $handle -- handle of surfer to whom data is to be published
  * @return array $result -- field id => array (localized title,
  *                                             publishing settings)
  */
  function getPublishProfileData($handle) {
    include_once(dirname(__FILE__).'/base_surfers.php');
    include_once(dirname(__FILE__).'/base_contacts.php');
    $surferAdmin = new surfer_admin($this->msgs);
    // In the beginning, there's no data yet
    $result = array();
    // Get current content language
    $lng = $this->parentObj->topic['TRANSLATION']['lng_id'];
    // Get the contact surfer's id
    $surferId = $surferAdmin->getIdByHandle($handle);
    // Get all fields that need approval
    $sql = "SELECT s.surferdata_id,
                   s.surferdata_needsapproval,
                   s.surferdata_approvaldefault,
                   s.surferdata_available,
                   s.surferdata_class,
                   st.surferdatatitle_field,
                   st.surferdatatitle_title
              FROM %s AS s, %s AS st
             WHERE s.surferdata_available=1
               AND s.surferdata_id=st.surferdatatitle_field
               AND s.surferdata_needsapproval=1
               AND st.surferdatatitle_lang=%d
          ORDER BY s.surferdata_class,
                   s.surferdata_order,
                   st.surferdatatitle_title";
    $sqlParams = array(
      $surferAdmin->tableData, $surferAdmin->tableDataTitles, $lng);
    $res = $surferAdmin->databaseQueryFmt($sql, $sqlParams);
    while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
      $result[$row['surferdata_id']] = array(
        'title' => $row['surferdatatitle_title'],
        'public' => FALSE,
        'public_default' => $row['surferdata_approvaldefault']
      );
    }
    // Assume that there's no publishing information yet
    $publishInfo = FALSE;
    // If there's already contact, get current publishing scheme
    if ($this->surferObj->isValid) {
      $contactManager = new contact_manager($this->surferObj->surfer['surfer_id']);
      $contactStatus = $contactManager->findContact($surferId);
      if ($contactStatus & SURFERCONTACT_DIRECT) {
        $sql = "SELECT surfercontactpublic_public,
                       surfercontactpublic_data,
                       surfercontactpublic_surferid,
                       surfercontactpublic_partner
                  FROM %s
                 WHERE surfercontactpublic_surferid='%s'
                   AND surfercontactpublic_partner='%s'";
        $sqlParams = array($surferAdmin->tableContactPublic,
                           $this->surferObj->surfer['surfer_id'],
                           $surferId
                          );
        $res = $surferAdmin->databaseQueryFmt($sql, $sqlParams);
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          // Now that's publishing info for sure
          $publishInfo = TRUE;
          if ($row['surfercontactpublic_public'] == 1) {
            $result[$row['surfercontactpublic_data']]['public'] = TRUE;
          }
        }
      }
    }
    // If we don't have any publishing info, use the current surfer's defaults
    if (!$publishInfo) {
      $sql = "SELECT surfercontactpublic_public,
                     surfercontactpublic_data,
                       surfercontactpublic_surferid,
                       surfercontactpublic_partner
                FROM %s
               WHERE surfercontactpublic_surferid='%s'
                 AND surfercontactpublic_partner=''";
      $sqlParams = array($surferAdmin->tableContactPublic,
                         $this->surferObj->surfer['surfer_id']
                        );
      $res = $surferAdmin->databaseQueryFmt($sql, $sqlParams);
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        // Okay, but that's publishing info
        $publishInfo = TRUE;
        if ($row['surfercontactpublic_public'] == 1) {
          $result[$row['surfercontactpublic_data']]['public'] = TRUE;
        }
      }
    }
    // If there's still no publishing info, use the system defaults
    if (!$publishInfo) {
      foreach ($result as $key => $field) {
        $result[$key]['public'] = $field['public_default'];
      }
    }
    return $result;
  }

  /**
  * Get publish profile data form
  *
  * This form allows for a surfer to
  * determine which data he/she wants to publish
  * when requesting or accepting contact
  * to some other surfer
  *
  * @param string $handle -- handle of surfer to whom data is to be published
  * @param boolean $auto  -- shows the list automatically if TRUE (default),
  *                          or only when a special parameter ist set if FALSE.
  *                          In the latter case, a link including this parameter
  *                          will be created
  * @return string $result -- form XML
  */
  function getPublishProfileDataForm($handle, $auto = FALSE) {
    if ($auto ||
        (
         isset($this->params['publish_form']) &&
         $this->params['publish_form'] == 1
        )
       ) {
      $href = $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array('handle' => $handle),
          $this->paramName
      );
      $result = sprintf(
        '<publish-fields url="%s" caption="%s" caption-yes="%s" caption-no="%s">'.LF,
        papaya_strings::escapeHTMLChars($href),
        papaya_strings::escapeHTMLChars($this->data['caption_publish']),
        papaya_strings::escapeHTMLChars($this->data['caption_yes']),
        papaya_strings::escapeHTMLChars($this->data['caption_no'])
      );
      $result .= sprintf(
        '<publish-script caption="%s"><content>',
        papaya_strings::escapeHTMLChars(
          $this->data['caption_publishall']
        )
      );
      $result .= '
<script language="JavaScript" type="text/javascript">
<![CDATA[

function checkAll(state) {
  els = document.getElementsByTagName("input");
  for (var i = 0; el = els[i]; i++) {
    if (el.name.match(/publish_/)) {
      if (state) {
        el.checked = (el.value == 1) ? "checked" : "";
      } else {
        el.checked = (el.value == 0) ? "checked" : "";
      }
    }
  }
}

$("#publishAll").bind("click", function() {
  if (typeof(state) == "undefined" || state == false) {
    state = true;
  } else {
    state = false;
  }
  checkAll(state);
  return false;
});

//]]>
</script>
</content>
</publish-script>';
      // Get current fields and publishing settings first
      $fields = $this->getPublishProfileData($handle);
      foreach ($fields as $key => $field) {
        if ($field['public']) {
          $publish = ' publish="1"';
        } else {
          $publish = '';
        }
        $result .= sprintf(
          '<field id="%s" title="%s"%s/>'.LF,
          papaya_strings::escapeHTMLChars($key),
          papaya_strings::escapeHTMLChars($field['title']),
          $publish
        );
      }
      $result .= '</publish-fields>'.LF;
    } elseif (!$auto) {
      $href = $this->getWebLink(
        NULL,
        NULL,
        NULL,
        array('user_handle' => $handle, 'publish_form' => 1),
        $this->paramName
      );
      $result = sprintf(
        '<publish-link href="%s" caption="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($href),
        papaya_strings::escapeHTMLChars($this->data['caption_publishlink'])
      );
    }
    return $result;
  }

  /**
  * callback function for user groups
  */
  function callbackUserGroup($name, $element, $data) {
    $result = '';
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $surfer = new base_surfer;
    $sql = "SELECT surfergroup_id, surfergroup_title
              FROM %s
             ORDER BY surfergroup_title ASC
           ";
    $params = array($surfer->tableSurferGroups);
    if ($res = $surfer->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $groups[$row['surfergroup_id']] = $row['surfergroup_title'];
      }
      if (isset($groups) && is_array($groups) && count($groups) > 0) {
        $result .= sprintf(
          '<select name="%s[%s]" class="dialogScale dialogSelect">'.LF,
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars($name)
        );
        foreach ($groups as $groupId => $groupName) {
          $selected = ($groupId == $data) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%s" %s>%s</option>'.LF,
            papaya_strings::escapeHTMLChars($groupId),
            $selected,
            papaya_strings::escapeHTMLChars($groupName)
          );
        }
        $result .= '</select>'.LF;
      }
    }
    return $result;
  }

  /**
  * Store publishing settings
  *
  * @param string $surferHandle
  */
  function storePublishSettings($surferHandle) {
    if ($this->surferObj->isValid) {
      include_once(dirname(__FILE__).'/base_surfers.php');
      $surferAdmin = new surfer_admin($this->msgs);
      $surferId = $this->surferObj->surfer['surfer_id'];
      $contactId = $surferAdmin->getIdByHandle($surferHandle);
      // Test each publish field
      foreach ($this->params as $fieldName => $val) {
        if (preg_match("/^publish_(.*)/", $fieldName, $matchData)) {
          $fieldId = $matchData[1];
          // Check whether there is a publish setting for this field
          $sql = "SELECT surfercontactpublic_id,
                         surfercontactpublic_surferid,
                         surfercontactpublic_partner,
                         surfercontactpublic_data
                    FROM %s
                   WHERE surfercontactpublic_surferid='%s'
                     AND surfercontactpublic_partner='%s'
                     AND surfercontactpublic_data=%d";
          $sqlParams = array(
            $surferAdmin->tableContactPublic,
            $surferId,
            $contactId,
            $fieldId
          );
          $res = $surferAdmin->databaseQueryFmt($sql, $sqlParams);
          if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $id = $row['surfercontactpublic_id'];
            // Existing field: update
            $data = array('surfercontactpublic_public' => $val);
            $surferAdmin->databaseUpdateRecord(
              $surferAdmin->tableContactPublic,
              $data,
              'surfercontactpublic_id',
              $id
            );
          } else {
            // Insert new field
            $data = array(
              'surfercontactpublic_surferid' => $surferId,
              'surfercontactpublic_partner' => $contactId,
              'surfercontactpublic_data' => $fieldId,
              'surfercontactpublic_public' => $val
            );
            $surferAdmin->databaseInsertRecord(
              $surferAdmin->tableContactPublic, 'surfercontactpublic_id', $data
            );
          }
        }
      }
    }
  }

  /**
  * Handle contact reply
  *
  * @param string $surferHandle
  * @return boolean
  * @version kersken 2007-05-10
  */
  function handleContactReply($surferHandle) {
    if ($this->surferObj->isValid) {
      $this->storePublishSettings($surferHandle);
      include_once(dirname(__FILE__).'/base_surfers.php');
      $surferAdmin = new surfer_admin($this->msgs);
      $userId = $this->surferObj->surfer['surfer_id'];
      $surferId = $surferAdmin->getIdByHandle($surferHandle);
      // Get DB record
      $sql = "SELECT surfercontact_id
                FROM %s
               WHERE surfercontact_requestor = '%s'
                 AND surfercontact_requested = '%s'";
      $res = $surferAdmin->databaseQueryFmt(
        $sql,
        array($this->tableContacts, $surferId, $userId)
      );
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $id = $row['surfercontact_id'];
        if (isset($this->params['accept']) && $this->params['accept'] == 1) {
          $data = array('surfercontact_status' => 2);
          $surferAdmin->databaseUpdateRecord(
            $this->tableContacts, $data, 'surfercontact_id', $id
          );
          return TRUE;
        } elseif (isset($this->params['decline']) && $this->params['decline'] == 1) {
          $data = array('surfercontact_status' => 0);
          $surferAdmin->databaseUpdateRecord(
            $this->tableContacts, $data, 'surfercontact_id', $id
          );
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Add contact request
  *
  * @param string $surferHandle
  * @return boolean
  * @version kersken 2007-05-10
  */
  function addContactRequest($surferHandle) {
    if ($this->surferObj->isValid) {
      $this->storePublishSettings($surferHandle);
      include_once(dirname(__FILE__).'/base_surfers.php');
      $surferAdmin = new surfer_admin($this->msgs);
      $userId = $this->surferObj->surfer['surfer_id'];
      $surferId = $surferAdmin->getIdByHandle($surferHandle);
      $data = array(
        'surfercontact_requestor' => $userId,
        'surfercontact_requested' => $surferId,
        'surfercontact_status' => 1
      );
      $success = $surferAdmin->databaseInsertRecord(
        $this->tableContacts, 'surfercontact_id', $data
      );
      return $success;
    }
    return FALSE;
  }

  /**
  * Trace contact to some surfer without direct contact
  *
  * @access public
  * @param String $SurferHandle
  * @return string
  */
  function traceContact($surferHandle) {
    $result = '';
    if ($this->surferObj->isValid) {
      // Check whether there's a parameter
      // for retrieving extended contact information
      if (isset($this->params['trace_contact'])) {
        // Trace the contact
        include_once(dirname(__FILE__).'/base_surfers.php');
        include_once(dirname(__FILE__).'/base_contacts.php');
        $surferAdmin = new surfer_admin($this->msgs);
        $contactManager = new contact_manager(
          $this->surferObj->surfer['surfer_id'],
          $this->data['contact_depth']);
        // Retrieve the contact surfer's id
        $contactSurferId = $surferAdmin->getIdByHandle($surferHandle);
        $contactStatus = $contactManager->findContact($contactSurferId, TRUE);
        if ($contactStatus & SURFERCONTACT_INDIRECT) {
          foreach ($contactManager->pathList as $path) {
            $result .= '<contact-path>'.LF;
            foreach ($path as $surferId) {
              // Get the surfer's name data
              $currentSurfer = $surferAdmin->getNameById($surferId);
              // Make up the name string according to current settings
              if ($this->data['show_name_as'] == 'handle' ||
                  (
                   $currentSurfer['surfer_surname'] == '' &&
                   $currentSurfer['surfer_givenname'] == ''
                  )
                 ) {
                $currentSurferName = $currentSurfer['surfer_handle'];
              } else {
                $currentSurferName = sprintf(
                  '%s %s',
                  $currentSurfer['surfer_givenname'],
                  $currentSurfer['surfer_surname']
                );
              }
              // Add a link if this is neither current nor contact surfer
              if (($surferId != $this->surferObj->surfer['surfer_id']) &&
                  ($surferId != $contactSurferId)) {
                $href = $this->getWebLink(
                  NULL,
                  NULL,
                  NULL,
                  array('user_handle' => $currentSurfer['surfer_handle']),
                  $this->paramName
                );
                $result .= sprintf(
                  '<surfer><a href="%s">%s</a></surfer>',
                  papaya_strings::escapeHTMLChars($href),
                  papaya_strings::escapeHTMLChars($currentSurferName)
                );
              } else {
                $result .= sprintf(
                  '<surfer>%s</surfer>'.LF,
                  papaya_strings::escapeHTMLChars($currentSurferName)
                );
              }
            }
            $result .= '</contact-path>'.LF;
          }
        }
        $href = $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array('user_handle' => $surferHandle),
          $this->paramName
        );
        $result .= sprintf(
          '<contactlink href="%s" caption="%s" status="%s"/>',
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($this->data['caption_hidelink']),
          papaya_strings::escapeHTMLChars($contactStatus)
        );
      } else {
        // Create trace link instead
        $href = $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array(
            'user_handle' => $surferHandle,
            'trace_contact' => 1
          ),
          $this->paramName
        );
        $result = sprintf(
          '<contactlink href="%s" caption="%s"/>',
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($this->data['caption_contactlink'])
        );
      }
    }
    return $result;
  }

  /**
  * Get contact info for logged-in user to displayed user
  *
  * @param string $surferHandle
  * @access public
  * @return string
  * @version kersken 2007-05-10
  */
  function getContactInfo($surferHandle) {
    $result = '';
    if ($this->surferObj->isValid) {
      include_once(dirname(__FILE__).'/base_surfers.php');
      $surferAdmin = new surfer_admin($this->msgs);
      $userId = $this->surferObj->surfer['surfer_id'];
      $surferId = $surferAdmin->getIdByHandle($surferHandle);
      $sql = "SELECT surfercontact_status
                FROM %s
               WHERE surfercontact_requestor = '%s'
                 AND surfercontact_requested = '%s'";
      $res = $surferAdmin->databaseQueryFmt(
        $sql,
        array(
          $this->tableContacts, $userId, $surferId
        )
      );
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        switch ($row['surfercontact_status']) {
        case 1:
          $result = sprintf(
            '<contact status="%s"/>',
            papaya_strings::escapeHTMLChars($this->data['caption_contact_request_self'])
          );
          $result .= $this->traceContact($surferHandle);
          break;
        case 2:
          $result = sprintf(
            '<contact status="%s"/>',
            papaya_strings::escapeHTMLChars($this->data['caption_contact'])
          );
          $result .= $this->getPublishProfileDataForm($surferHandle, FALSE);
          break;
        case 0:
        default:
          $result = sprintf(
            '<contact status="%s"/>',
            papaya_strings::escapeHTMLChars($this->data['caption_no_contact'])
          );
          $result .= $this->traceContact($surferHandle);
        }
      } else {
        $res = $surferAdmin->databaseQueryFmt(
          $sql, array($this->tableContacts, $surferId, $userId)
        );
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          switch ($row['surfercontact_status']) {
          case 1:
            $result .= sprintf(
              '<contactreply requested="%s" caption="%s" accept-caption="%s"'.
              ' decline-caption="%s" accept-url="%s" decline-url="%s"/>',
              papaya_strings::escapeHTMLChars($surferHandle),
              papaya_strings::escapeHTMLChars($this->data['caption_contact_request_other']),
              papaya_strings::escapeHTMLChars($this->data['caption_accept']),
              papaya_strings::escapeHTMLChars($this->data['caption_decline']),
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(
                  NULL,
                  NULL,
                  NULL,
                  array(
                    'handle' => $surferHandle,
                    'accept' => 1
                  ),
                  $this->paramName
                )
              ),
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(
                  NULL,
                  NULL,
                  NULL,
                  array(
                    'handle' => $surferHandle,
                    'decline' => 1
                  ),
                  $this->paramName
                )
              )
            );
            $result .= $this->getPublishProfileDataForm($surferHandle);
            $result .= $this->traceContact($surferHandle);
            break;
          case 2:
            $result = sprintf(
              '<contact status="%s"/>',
              papaya_strings::escapeHTMLChars($this->data['caption_contact'])
            );
            $result .= $this->getPublishProfileDataForm($surferHandle, FALSE);
            break;
          case 0:
          default:
            $result = sprintf(
              '<contact status="%s"/>',
              papaya_strings::escapeHTMLChars($this->data['caption_no_contact'])
            );
            $result .= $this->traceContact($surferHandle);
          }
        } else {
          $result = sprintf(
            '<contactrequest requested="%s" caption="%s" url="%s"/>',
            papaya_strings::escapeHTMLChars($surferHandle),
            papaya_strings::escapeHTMLChars($this->data['caption_contact_send_request']),
            papaya_strings::escapeHTMLChars(
              $this->getWebLink(
                NULL,
                NULL,
                NULL,
                array(
                  'handle' => $surferHandle, 'request' => TRUE
                ),
                $this->paramName
              )
            )
          );
          $result .= $this->getPublishProfileDataForm($surferHandle);
          $result .= $this->traceContact($surferHandle);
        }
      }
    }
    return $result;
  }
}

?>