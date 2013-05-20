<?php
/**
* Page module - Send feedback email and store values in DB
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
* @version $Id: content_feedback_store.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic database object class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* basic feedback content class
*/
require_once(dirname(__FILE__).'/content_feedback.php');

/**
* Page module - Send feedback email and store values in DB
*
* @package Papaya-Modules
* @subpackage Free-Mail
*/
class content_feedback_store extends content_feedback {

  /**
  * Database object base_db
  * @var object base_db $dbObj
  */
  var $dbObj = NULL;

  /**
  * Send email
  *
  * @access public
  * @return string $result XML
  */
  function sendEmail() {
    $content['NAME'] = empty($this->params['mail_name']) ? '' : $this->params['mail_name'];
    $content['FROM'] = empty($this->params['mail_from']) ? '' : $this->params['mail_from'];
    $content['SUBJECT'] = empty($this->params['mail_subject']) ? '' : $this->params['mail_subject'];
    $content['TEXT'] = empty($this->params['mail_message']) ? '' : $this->params['mail_message'];
    $result = parent::sendEmail($content);
    if ($result[0]) {
      $this->storeFeedback($content);
    }
    return $result;
  }

  /**
  * Store feedback
  *
  * @access public
  * @return boolean
  */
  function storeFeedback($content) {
    if (!isset($this->dbObj) || !is_object($this->dbObj) ||
        get_class($this->dbObj != 'base_db')) {
      $this->dbObj = new base_db;
    }
    $xmlMessage = sprintf(
      '<entry timestamp="%s">'.LF,
      date('Y-m-j H:i:s', time())
    );
    $xmlMessage .= sprintf(
      '<field name="email">%s</field>'.LF,
      papaya_strings::escapeHTMLChars($content['FROM'])
    );
    $xmlMessage .= sprintf(
      '<field name="name">%s</field>'.LF,
      papaya_strings::escapeHTMLChars($content['NAME'])
    );
    $xmlMessage .= sprintf(
      '<field name="subject">%s</field>'.LF,
      papaya_strings::escapeHTMLChars($content['SUBJECT'])
    );
    $xmlMessage .= sprintf(
      '<field name="message">%s</field>'.LF,
      papaya_strings::escapeHTMLChars($content['TEXT'])
    );
    $xmlMessage .= '</entry>';

    $this->tableFeedback = PAPAYA_DB_TABLEPREFIX.'_feedback';
    $data = array(
      'feedback_time' => time(),
      'feedback_email' => $content['FROM'],
      'feedback_name' => $content['NAME'],
      'feedback_subject' => $content['SUBJECT'],
      'feedback_message' => $content['TEXT'],
      'feedback_xmlmessage' => $xmlMessage
    );
    return FALSE !== $this->dbObj->databaseInsertRecord($this->tableFeedback, NULL, $data);
  }

}

?>