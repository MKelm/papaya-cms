<?php
/**
* Cronjob-module send updates on subscribed categories
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
* @subpackage Free-Catalog
* @version $Id: cronjob_catalog_subscriptions.php 37373 2012-08-08 13:23:32Z weinert $
*/

/**
* Basic class Cronjobs
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_cronjob.php');
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Cronjob-module send updates on subscribed categories
*
* @package Papaya-Modules
* @subpackage Free-Catalog
*/
class cronjob_catalog_subscriptions extends base_cronjob {

  /**
  * Handler for modification status
  * @var boolean $modified
  */
  var $modified = FALSE;

  /**
  * Class constructor
  *
  * @param array $param
  * @access public
  */
  function __construct(&$owner, $paramName = 'cs') {
    parent::__construct($owner, $paramName);
    $this->tableTopicVersions = PAPAYA_DB_TABLEPREFIX."_topic_versions";
    $this->tableTopicPublic = PAPAYA_DB_TABLEPREFIX."_topic_public";
    $this->tableTopicPublic = PAPAYA_DB_TABLEPREFIX."_topic_public";
    $this->tableTopicPublicTrans = PAPAYA_DB_TABLEPREFIX."_topic_public_trans";
    $this->tableCatalog = PAPAYA_DB_TABLEPREFIX."_catalog";
    $this->tableCatalogSubscriptions = PAPAYA_DB_TABLEPREFIX."_catalog_subscriptions";
    $this->tableCatalogSubscriptionsMeta =
      PAPAYA_DB_TABLEPREFIX."_catalog_subscriptions_meta";
  }

  /**
  * callback function to generate input fields for change levels
  *
  * @param string $name
  * @param array $field
  * @param array $data
  * @access public
  * @return string $result XML
  */
  function callbackChangelevels($name, $field, $data) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_statictables.php');
    $changeLevels = base_statictables::getChangeLevels();
    $result = '<div class="dialogGroup dialogScale">';
    foreach ($changeLevels as $i => $v) {
      $selected = (isset($data[$i])) ? 'checked="checked"' : '';
      $result .= sprintf(
        '<input type="checkbox" name="%s[%s][%d]" value="1" %s></input>%s<br />'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name),
        (int)$i,
        $selected,
        papaya_strings::escapeHTMLChars($v)
      );
    }
    $result .= '</div>';
    return $result;
  }

  /**
  * initialize dialog
  *
  * @see cronjob_catalog_subscriptions::getContentForm()
  * @see base_cronjob::initializeDialog()
  *
  * @access public
  * @return string
  */
  function initializeDialog() {
    $this->layout = &$this->parentObj->layout;
    $this->dialog = &$this->parentObj->dialog;
    $this->initializeParams();
    $this->loadLngSelect();
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'addlng':
        return $this->getContentForm($this->data, $this->lngSelect->currentLanguageId);
        break;
      case 'store':
        $this->data['changelevels'] = $this->params['changelevels'];
        $this->data['bcc'] = $this->params['bcc'];
        $this->data['sender_name'] = $this->params['sender_name'];
        $this->data['sender_email'] = $this->params['sender_email'];
        $this->data['content'][$this->lngSelect->currentLanguageId] = array(
          'subject' => $this->params['subject'],
          'body' => $this->params['body'],
        );
        $this->data['hostname'][$this->lngSelect->currentLanguageId] =
          $this->params['hostname'];
        $this->modified = TRUE;
        return $this->getContentForm($this->data, $this->lngSelect->currentLanguageId);
      default:
        break;
      }
    }
  }

  /**
  * get form
  *
  * @see cronjob_catalog_subscriptions::getContentForm()
  * @see cronjob_catalog_subscriptions::getLngAddForm()
  *
  * @access public
  * @return string
  */
  function getDialog() {
    if (isset($this->data['content']) && is_array($this->data['content'])
        && count($this->data['content']) > 0
        && isset($this->data['content'][$this->lngSelect->currentLanguageId])
        || isset($this->params['cmd']) && $this->params['cmd'] = 'addlng') {
      $this->getContentForm($this->data, $this->lngSelect->currentLanguageId);
      return $this->dialog->getDialogXML();
    } else {
      return $this->getLngAddForm();
    }
  }

  /**
  * get form to add content for language
  *
  * @see base_msgdialog::getMsgDialog()
  *
  * @access public
  * @return string
  */
  function getLngAddForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'addlng'
    );
    $msg = sprintf(
      $this->_gt('Add content for language "%s" (%s)?'),
      $this->lngSelect->currentLanguage['lng_title'],
      $this->lngSelect->currentLanguage['lng_short']
    );
    $this->dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question'
    );
    $this->dialog->buttonTitle = 'Add';
    return $this->dialog->getMsgDialog();
  }

  /**
  * get form for content
  *
  * @see base_dialog::getDialogXML()
  *
  * @param mixed $data optional, default value NULL
  * @param mixed $lngId optional, default value NULL
  * @access public
  * @return string
  */
  function getContentForm($data = NULL, $lngId = NULL) {
    if (!isset($this->dialog) || !is_object($this->dialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array(
        'cmd' => 'store');
      $fields = array(
        'sender_name' => array('Sender Name', 'isNoHTML', TRUE, 'input', 200),
        'sender_email' => array('Sender Email', 'isEmail', TRUE, 'input', 200),
        'subject' => array('Subject', 'isNoHTML', TRUE, 'input', 200),
        'body' => array('Body', 'isNoHTML', TRUE, 'textarea', 10, '',
          "The following topics have been updated:\n\n{%links%}\n\n"
        ),
        'hostname' =>
          array('Basic Hostname', 'isHTTP', TRUE, 'input', 200, 'no trailing /',
            'http://www.domain.tld'),
        'language independent',
        'changelevels' =>
          array('Change Levels', '', TRUE, 'function', 'callbackChangelevels'),
        'bcc' => array('BCC Recipient', 'isEmail', FALSE, 'input', 200),
      );
      if (isset($lngId)) {
        if (isset($data['content'][$lngId])) {
          $useData = $data['content'][$lngId];
        }
        if (isset($data['hostname'][$lngId])) {
          $useData['hostname'] = $data['hostname'][$lngId];
        }
      } else {
        $useData = array();
      }
      $useData['changelevels'] = @$data['changelevels'];
      $useData['bcc'] = @$data['bcc'];
      $useData['sender_name'] = @$data['sender_name'];
      $useData['sender_email'] = @$data['sender_email'];
      $this->dialog =
        new base_dialog($this, $this->paramName, $fields, $useData, $hidden);
      $this->dialog->dialogTitle =
        $this->_gt('Edit Content').': '.$this->lngSelect->currentLanguage['lng_title'];
      $this->dialog->msgs = &$this->msgs;
      $this->dialog->loadParams();
      $this->dialog->tokenKeySuffix = 'cs-cronjob';
      $this->dialog->buttonTitle = 'Save';
    }
  }

  /**
  * function for execution
  *
  * @access public
  */
  function execute() {
    $this->loadLngSelect();
    $time = $this->parentObj->cronjob['cronjob_lastexec'];
    include_once(dirname(__FILE__).'/base_catalog.php');
    $this->catalog = new base_catalog;
    $changeLevelCondition = ' AND '.$this->catalog->databaseGetSQLCondition(
      'topic_change_level', array_keys($this->data['changelevels'])
    );
    $sqlMax = "SELECT MAX(version_time) as max_time, topic_id
                 FROM %s
                WHERE version_time >= %d
                 $changeLevelCondition
             GROUP BY topic_id";
    $paramsMax = array($this->tableTopicVersions, $time);
    if ($resMax = $this->catalog->databaseQueryFmt($sqlMax, $paramsMax)) {
      while ($row = $resMax->fetchRow(DB_FETCHMODE_ASSOC)) {
        $conditions[] = sprintf(
          "tv.topic_id = %d AND tv.version_time = %d",
          $row['topic_id'],
          $row['max_time']
        );
      }
      if (isset($conditions) && is_array($conditions) && count($conditions) > 0) {
        $maxTimeCondition = sprintf(' AND (%s) ', implode(' OR ', $conditions));
      } else {
        $maxTimeCondition = '';
      }
    }
    $sql = "SELECT cl.catalog_id, tv.topic_id, tt.topic_title,
                   c.catalog_parent_path, tt.lng_id
              FROM %s AS cl
              LEFT OUTER JOIN %s AS tv ON (tv.topic_id = cl.topic_id)
              LEFT OUTER JOIN %s AS tt ON (tv.topic_id = tt.topic_id)
              LEFT OUTER JOIN %s AS c ON (c.catalog_id = cl.catalog_id)
             WHERE tv.version_time >= %d
              $maxTimeCondition";

    $params = array(
      $this->catalog->tableCatalogLinks,
      $this->tableTopicVersions,
      $this->tableTopicPublicTrans,
      $this->tableCatalog,
      $time
    );
    if ($res = $this->catalog->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $catalogs[$row['catalog_id']][$row['topic_id']] = 1;
        @$catalogCount[$row['catalog_id']][] = $row['topic_id'];
        $topics[$row['catalog_id']][] = $row;
        $path = explode(';', $row['catalog_parent_path']);
        foreach ($path as $catalogId) {
          if ($catalogId != '' && $catalogId != 0) {
            @$catalogCount[$catalogId][] = $row['topic_id'];
            $topics[$catalogId][] = $row;
          }
        }
      }
    }
    // get all subscriptions in categories that have a modified topic
    if (isset($catalogCount) && is_array($catalogCount) &&
        count($catalogCount) > 0) {
      $catalogCondition = 'WHERE '.$this->catalog->databaseGetSQLCondition(
        's.catalog_id', array_keys($catalogCount)
      );
      $sql = "SELECT m.catalog_subscription_email, s.catalog_id, m.lng_id
                FROM %s AS s
                LEFT OUTER JOIN %s AS m
                  ON (m.catalog_subscription_id = s.catalog_subscription_id)
              $catalogCondition";
      $params = array(
        $this->tableCatalogSubscriptions,
        $this->tableCatalogSubscriptionsMeta
      );
      if ($res = $this->catalog->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $subscriptions[$row['catalog_subscription_email']][] = $row['catalog_id'];
          $lng[$row['catalog_subscription_email']] = $row['lng_id'];
        }
      }
    } else {
      echo 'No modified topics found. ';
      return 0;
    }
    // send emails
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
    $emailObj = new email;

    $mailsCount = 0;
    if (isset($subscriptions) && is_array($subscriptions) && count($subscriptions) > 0) {
      foreach ($subscriptions as $email => $catalogIds) {
        $links = array();
        $lngId = $lng[$email];
        $transformer = new PapayaUrlTransformerCleanup();
        foreach ($catalogIds as $cid) {
          if (isset($topics[$cid]) &&
              is_array($topics[$cid]) &&
              count($topics[$cid]) > 0) {
            foreach ($topics[$cid] as $topic) {
              if ($topic['lng_id'] == $lngId) {
                $link = $this->getWebLink(
                  $topic['topic_id'],
                  $lngId
                );
                $link = $transformer->transform($this->data['hostname'][$lngId].'/'.$link);
                $title = $topic['topic_title'];
                $links[$link] = $title.LF.$link;
              }
            }
          }
        }
        if (!empty($links)) {
          $bodyData = array('links' => implode(LF, $links).LF);
          $emailObj->setTemplate(
            'body', $this->data['content'][$lngId]['body'], $bodyData);
          if (isset($this->data['bcc']) && $this->data['bcc'] != '') {
            $emailObj->setHeader('bcc', $this->data['bcc']);
          }
          if (isset($this->data['sender_email']) && $this->data['sender_email'] != '') {
            $emailObj->setSender(
              $this->data['sender_email'], @$this->data['sender_name']);
          }
          if ($emailObj->send($email, $this->data['content'][$lngId]['subject'])) {
            $mailsCount++;
          }
        }
      }
      printf('%d emails sent.'.LF, $mailsCount);
    }
    return 0;
  }

  /**
  * Check dialog input
  *
  * @see base_dialog::checkDialogInput()
  *
  * @access public
  * @return boolean
  */
  function checkDialogInput() {
    $result = FALSE;
    if (isset($this->dialog) && is_object($this->dialog)) {
      if ($result = $this->dialog->checkDialogInput()) {
        return TRUE;
      }
    }
    return $result;
  }

  /**
  * return modification status
  *
  * @access public
  * @return boolean
  */
  function modified() {
    return $this->modified;
  }

  /**
  * Check execution parameters
  *
  * @access public
  * @return boolean Execution possible?
  */
  function checkExecParams() {
    if (isset($this->data['content']) && is_array($this->data['content'])
        && isset($this->data['changelevels']) && is_array($this->data['changelevels'])
        && isset($this->data['hostname']) && is_array($this->data['hostname'])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * load language selection
  *
  * @access public
  */
  function loadLngSelect() {
    if (!(isset($this->lngSelect) && is_object($this->lngSelect))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
      $this->lngSelect = &base_language_select::getInstance();
    }
  }
}