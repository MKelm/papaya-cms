<?php
/**
* Twitter Statuses Box
*
* This File contains the class <var>Box.php</var>
*
* @copyright by papaya Software GmbH, Cologne, Germany - All rights reserved.
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Twitter
* @version $Id: Box.php 37990 2013-01-18 19:15:32Z weinert $

/**
 * Basic class action box
 *
 * @include base actionbox
 */
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Twitter Statuses Box Module
*
* This box module displays twitter statuses of a specified twitter user. The number of
* displayed statuses can also be speciefied in the content section of each box.
*
* @package Papaya-Modules
* @subpackage Free-Twitter
*/
class PapayaModuleTwitterBox extends base_actionbox {

  /**
   * Configuration edit fields
   * @var array
   */
  var $editFields = array(
    'title' => array(
      'Title', 'isNoHTML', FALSE, 'input', 200, 'Optional field.', NULL
    ),
    'follow_caption' => array(
      'Follow Link Caption', 'isNoHTML', TRUE, 'input', 200, NULL, 'Follow me on twitter.com'
    ),
    'screen_name' => array(
      'Twitter Screen Name', '(^[a-zA-Z0-9_]{2,15}$)', TRUE, 'input', 200, NULL, 'papayaCMS'
    ),
    'count' => array(
      'Number Of Entries', 'isNum', TRUE, 'input', 3, NULL, 5
    ),
    'include_rts' => array(
      'Include Retweets', '/0|1/', TRUE, 'yesno', NULL, NULL, 0
    ),
    'cache_time' => array(
      'Time To Cache Data', 'isNum', TRUE, 'input', 7, 'in seconds, please!', 300
    ),
    'Text Adaptation',
    'link_replies' => array(
      'Link Replys',
      '/0|1/',
      TRUE,
      'yesno',
      NULL,
      'Set to yes if @user should link to twitter.com/user',
      1
    ),
    'link_tags' => array(
      'Link Hashtags',
      '/0|1/',
      TRUE,
      'yesno',
      NULL,
      'Set to yes if #tag should link to search.twitter.com',
      1
    ),
    'link_urls' => array(
      'Link URLs',
      '/0|1/',
      TRUE,
      'yesno',
      array(1 => 'Yes', 0 => 'No'),
      'Set to yes if links like http://... should be linked',
      1
    ),
    'link_mailaddresses' => array(
      'Link E-Mail Addresses',
      '/0|1/',
      TRUE,
      'yesno',
      NULL,
      'Set to yes if mail addresses like foo@bar.de should be linked',
      1
    ),
    'remove_link_protocols' => array(
      'Remove Link Protocols',
      '/0|1/',
      TRUE,
      'yesno',
      NULL,
      'Set to yes if link protocols like http://... should be removed in text',
      1
    )
  );

  /**
  * TwitterBoxBase object
  * @var TwitterBoxBase
  */
  private $_baseObject = NULL;

  /**
  * Set the base object to be used
  *
  * @param TwitterBoxBase $baseObject
  */
  public function setBaseObject($baseObject) {
    $this->_baseObject = $baseObject;
  }

  /**
  * Get (and, if necessary, initialize) the base object
  *
  * @return TwitterBoxBase
  */
  public function getBaseObject() {
    if (!is_object($this->_baseObject)) {
      $this->_baseObject = new PapayaModuleTwitterBoxBase();
    }
    return $this->_baseObject;
  }

  /**
  * Get the box XML output
  *
  * @return string XML
  */
  public function getParsedData() {
    $baseObject = $this->getBaseObject();
    $this->setDefaultData();
    $baseObject->setOwner($this);
    $baseObject->setBoxData($this->data);
    return $baseObject->getBoxXml();
  }
}