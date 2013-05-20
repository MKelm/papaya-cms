<?php
/**
* Page module unsubscribe for catalog
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
* @version $Id: content_catalog_unsubscribe.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* base catalog module
*/
require_once(dirname(__FILE__).'/base_catalog.php');

/**
* Page module unsubscribe for catalog
*
* @package Papaya-Modules
* @subpackage Free-Catalog
*/
class content_catalog_unsubscribe extends base_content {

  /**
  * Page content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'categ_base_id' =>
      array('Base category Id', 'isNum', TRUE, 'input', 200, '', ''),
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0
    ),
    'title' =>
      array('Title', 'isNoHTML', TRUE, 'input', 200, '', ''),
    'text' =>
      array('Text', 'isSomeText', FALSE, 'richtext', 5, '', ''),
    'Email options',
    'subject' =>
      array('Subject', 'isNoHTML', TRUE, 'input', 200, '', ''),
    'body' =>
      array('Body', 'isSomeText', TRUE, 'textarea', 5, '',
        "{%topics%}\n\n{%confirm_link%}"),
    'valid' =>
      array('Confirmation time', 'isNum', FALSE, 'input', 5, 'Confirmation valid for n hours.', 24),
    'Captions',
    'email_field' =>
      array('Title of email field', 'isNoHTML', FALSE, 'input', 200, '',
        'Email address:'),
    'unsubscribe_field' =>
      array('Unsubscribe button', 'isNoHTML', FALSE, 'input', 200, '',
        'Unsubscribe'),
    'Messages',
    'invalid_email' =>
      array('Invalid email address', 'isNoHTML', FALSE, 'input', 200, '',
      'The email address you entered is invalid.'),
    'email_sent' =>
      array('Email sent', 'isNoHTML', FALSE, 'input', 200, '',
        'An email has been sent to you. Please check your mailbox
         and follow the enclosed instructions.'),
    'unsubscription_completed' =>
      array('Unsubscription completed', 'isNoHTML', FALSE, 'input', 200, '',
      'You have been unsubscribed.'),
    'all_deleted' =>
      array('All subscriptions deleted', 'isNoHTML', FALSE, 'input', 200, '',
        'There are no more subscriptions for this email address.'),
    'no_pending' =>
      array('No subscriptions found', 'isNoHTML', FALSE, 'input', 200, '',
        'No subscriptions found. Perhaps you have unsubscribed already.'),
      'db_error' => array('Database error', 'isNoHTML', FALSE, 'input', 200, '',
        'DB Error: Your subscription could not be processed.')
  );

  /**
  * Base catalog object
  * @var object base_catalog $catalog
  */
  var $catalog = NULL;

  /**
  * Handler for showing catalog
  * @var boolean $showCatalog
  */
  var $showCatalog = FALSE;

  /**
  * Catalog contents
  * @var array $catalogs
  */
  var $catalogs = array();

  /**
  * Catalog tree
  * @var array $catalogTree
  */
  var $catalogTree = array();


  var $subscribedIds = array();

  /**
  * load catalog and set table names
  *
  * @access public
  */
  function initializeOutputObjects() {
    $this->catalog = new base_catalog();
    $this->catalog->module = &$this;
    $this->catalog->tableTopics = $this->parentObj->tableTopics;
    $this->catalog->tableTopicsTrans = $this->parentObj->tableTopicsTrans;
    $this->tableCatalogSubscriptionsMeta =
      PAPAYA_DB_TABLEPREFIX.'_catalog_subscriptions_meta';
    $this->tableCatalogSubscriptions =
      PAPAYA_DB_TABLEPREFIX.'_catalog_subscriptions';
    $this->tableCatalogSubscriptionsPending =
      PAPAYA_DB_TABLEPREFIX.'_catalog_subscriptions_pending';
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    $this->initializeOutputObjects();
    $this->executeOutputObjects();
    $mode = isset($this->params['mode'])? $this->params['mode'] : 'subscribed';
    $params = array('submitted' => '1', 'mode' => $mode);

    if (!empty($this->params['cfstr']) && !empty($this->params['email'])) {
      $params['cfstr'] = $this->params['cfstr'];
      $params['email'] = $this->params['email'];
    }
    $result .= sprintf(
      '<unsubscribe param_name="%s" href="%s">',
      $this->paramName, $this->getLink($params));
    if (empty($this->params['mode']) && empty($this->params['submitted'])) {
      $result .= sprintf(
        '<mailfieldname>%s</mailfieldname>',
        papaya_strings::escapeHTMLChars($this->data['email_field'])
      );
    }
    $result .= sprintf(
      '<unsubscribefield>%s</unsubscribefield>',
      papaya_strings::escapeHTMLChars($this->data['unsubscribe_field'])
    );
    if ($this->showCatalog) {
      $result .= $this->getCatalogXML();
    }
    $result .= '</unsubscribe>';
    $result .= $this->getMsgs();
    return $result;
  }

  /**
  * generate messages XML for user feedback
  *
  * @access public
  * @return string $result XML string <msg type="">text</msg>
  */
  function getMsgs() {
    $result = '';
    if (is_array($this->msgs)) {
      $result = '<msgs>';
      foreach (@$this->msgs as $v) {
        $type = ($v[0] == MSG_ERROR) ? 'Error' : 'Info';
        $result .= sprintf(
          '<msg type="%s">%s</msg>',
          papaya_strings::escapeHTMLChars($type),
          papaya_strings::escapeHTMLChars($v[1])
        );
      }
      $result .= '</msgs>';
    }
    return $result;
  }

  /**
  * execute subscription process steps
  *
  * @access public
  */
  function executeOutputObjects() {
    $this->loadCatalogs($this->data['categ_base_id']);
    if (isset($this->params['mode']) && $this->params['mode'] == 'subscribed' &&
        !empty($this->params['cfstr']) && !empty($this->params['email'])) {
      if (isset($this->params['submitted']) && $this->params['submitted'] == 1) {
        $this->processConfirmation($this->params['cfstr'], $this->params['email']);
      } else {
        $this->loadSubscribedCatalogs(
          $this->params['cfstr'],
          $this->parentObj->getContentLanguageId(),
          $this->data['categ_base_id']
        );
        $this->showCatalog = TRUE;
      }
    } elseif (isset($this->params['submitted']) && $this->params['submitted'] == 1 &&
              $this->checkUnsubscription()) {
      $this->processUnsubscription();
    }
  }

  /**
  * add pending unsubscription to db
  *
  * @access public
  * @param string $action rem
  */
  function processUnsubscription() {
    if ($confirmString = $this->getConfirmString()) {
      include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');

      $confirmLink = $this->getAbsoluteURL(
        $this->getWebLink(
          $this->parentObj->topicId,
          NULL,
          NULL,
          array(
            'mode' => 'subscribed',
            'cfstr' => $confirmString,
            'email' => $this->params['email']
          ),
          $this->paramName
        )
      );
      $topics = '';
      $this->loadSubscribedCatalogs(
        '',
        $this->parentObj->getContentLanguageId(),
        $this->data['categ_base_id']
      );
      foreach ($this->subscribedIds as $id => $set) {
        $topics .= '* ' . $this->catalogs[$id]['catalog_title'].LF;
      }
      $emailObj = new email;
      $emailObj->setBody(
        $this->data['body'],
        array(
          'confirm_link' => $confirmLink,
          'topics' => $topics
        )
      );
      $data = array(
        'catalog_subscription_key' => $confirmString,
      );
      $saved = $this->catalog->databaseUpdateRecord(
        $this->tableCatalogSubscriptionsMeta,
        $data,
        array('catalog_subscription_email' => $this->params['email'])
      );
      if (FALSE != $saved) {
        if ($emailObj->send($this->params['email'], $this->data['subject'])) {
          $this->msgs[] = array(MSG_INFO, $this->data['email_sent']);
        } else {
          $this->msgs[] = array(
            MSG_ERROR,
            $this->_gt(
              'Your confirmation email could not be sent.'.
              ' The error has been reported and will be fixed. Please try again later.'
            )
          );
          $this->logMsg(
            MSG_ERROR,
            PAPAYA_LOGTYPE_MODULES,
            'Subscription email could not be sent. Method sys_email::send() failed.',
            'email failed'
          );
        }
      } else {
        $this->msgs[] = array(
          MSG_ERROR,
          $this->data['db_error']
        );
      }
    }
  }

  /**
  * check submitted values integrity
  *
  * @access public
  * @return boolean $valid TRUE if submitted values are senseful and usable
  */
  function checkUnsubscription() {
    $valid = TRUE;
    if (!checkit::isEmail($this->params['email']) || $this->params['email'] == '') {
      $this->msgs[] = array(MSG_ERROR, $this->data['invalid_email']);
      $valid = FALSE;
    }
    return $valid;
  }

  /**
  * generates unique confirmation string
  *
  * @access public
  * @return mixed $confirmStr confirmation string, else FALSE
  */
  function getConfirmString() {
    $sql = "SELECT catalog_subscription_key
              FROM %s";
    $res = $this->catalog->databaseQueryFmt(
      $sql, array($this->tableCatalogSubscriptionsMeta)
    );
    if ($res) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['catalog_subscription_key']) {
          $confirmStrings[] = $row['catalog_subscription_key'];
        }
      }
      srand((double)microtime());
      do {
        $confirmStr = md5(uniqid(rand(), 1));
      } while (isset($confirmStrings) && is_array($confirmStrings) &&
               count($confirmStrings) > 0 &&
               in_array($confirmStr, $confirmStrings));
      return $confirmStr;
    }
    return FALSE;
  }

  /**
  * add/rem categories from pending table to/from subscriptions where
  * confirmstring is $confirmStr
  *
  * @access public
  * @param string $confirmStr confirmation string
  * @return boolean $success whether operation was successful
  */
  function processConfirmation($confirmStr, $email) {
    $success = FALSE;
    if (!empty($confirmStr) && $email != '') {
      if (isset($this->params['catalog_ids']) &&
          is_array($this->params['catalog_ids']) &&
          count($this->params['catalog_ids']) > 0) {
        $this->loadSubscribedCatalogs(
          $this->params['cfstr'],
          $this->parentObj->getContentLanguageId(),
          $this->data['categ_base_id']
        );
        if (is_array($this->subscribedIds) && count($this->subscribedIds) > 0) {
          $sql = "SELECT catalog_subscription_id
                    FROM %s
                   WHERE catalog_subscription_email = '%s'
                     AND catalog_subscription_key ='%s'";
          $params = array($this->tableCatalogSubscriptionsMeta, $email, $confirmStr);
          if ($res = $this->catalog->databaseQueryFmt($sql, $params)) {
            if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
              if ($subscriptionId = $row['catalog_subscription_id']) {
                $filter = array(
                  'catalog_subscription_id' => $subscriptionId,
                  'catalog_id' => array_keys($this->params['catalog_ids'])
                );
                $deleted = $this->catalog->databaseDeleteRecord(
                  $this->tableCatalogSubscriptions, $filter
                );
                if ($deleted) {
                  $this->msgs[] = array(MSG_INFO,
                    $this->data['unsubscription_completed']);
                  $success = TRUE;
                } else {
                  $this->msgs[] = array(MSG_INFO,
                    $this->_gt('DB Error: Could not delete records.'));
                  $success = FALSE;
                }
              }
            }
          }
        } else {
          $this->msgs[] = array(MSG_INFO, $this->data['no_pending']);
          $success = FALSE;
        }
      } else {
        $this->msgs[] = array(MSG_INFO, $this->data['nothing_selected']);
        $success = FALSE;
      }
    }
    return $success;
  }

  /**
  * generate XML for catalog
  *
  * @access public
  * @return mixed $result string catalog XML or FALSE
  * @uses content_catalog_subscribe::getSelectList
  */
  function getCatalogXML() {
    if (isset($this->subscribedIds) && is_array($this->subscribedIds) &&
        count($this->subscribedIds) > 0) {
      return $this->getSelectList($this->data['categ_base_id']);
    } else {
      $this->msgs[] = array(MSG_INFO, $this->data['no_pending']);
      return FALSE;
    }
  }

  /**
  * generate XML for catalog subtree recursively
  *
  * @access public
  * @return string $result catalog subtree XML
  * @uses content_catalog_subscribe::getSelectEntry
  */
  function getSelectList($parent = 0, $indent = 0) {
    $result = '';
    if (isset($this->catalogTree[$parent]) && is_array($this->catalogTree[$parent])) {
      foreach ($this->catalogTree[$parent] as $id) {
        $result .= $this->getSelectEntry($id, $indent);
      }
    }
    return $result;
  }

  /**
  * generate XML for catalog entry
  *
  * @access public
  * @return string $result catalog entry XML
  * @uses content_catalog_subscribe::getSelectList
  */
  function getSelectEntry($id, $indent = 0) {
    $result = '';
    if (isset($this->catalogs[$id]) && is_array($this->catalogs[$id])) {
      $disabled = (isset($this->subscribedIds[$id])) ?
        ' selected="selected"' : ' disabled="disabled"';
      $result .= sprintf(
        '<listitem catalog_id="%d" title="%s" indent="%d" %s>',
        (int)$id,
        papaya_strings::escapeHTMLChars($this->catalogs[$id]['catalog_title']),
        (int)$indent,
        $disabled
      );
      $subItems = $this->getSelectList($id, ++$indent);
      if ($subItems != '') {
        $result .= sprintf('<list>%s</list>', $subItems);
      }
      $result .= '</listitem>';
    }
    return $result;
  }

  /**
  * load subscribed categories; fills $this->catalogSubscribed
  *
  * @access public
  * @param integer $confirmStr confirmation string
  * @param integer $lngId language id
  * @param integer $baseCatalogId catalog id to start with
  */
  function loadSubscribedCatalogs($confirmStr, $lngId, $baseCatalogId) {
    unset($this->catalogSubscribed);
    $sqlIds = "SELECT s.catalog_id
                 FROM %s AS s
                 LEFT OUTER JOIN %s AS m
                   ON (m.catalog_subscription_id = s.catalog_subscription_id)
                WHERE m.catalog_subscription_key = '%s'
                  AND m.catalog_subscription_email = '%s'";
    $paramsIds = array(
      $this->tableCatalogSubscriptions,
      $this->tableCatalogSubscriptionsMeta,
      $confirmStr,
      $this->params['email']
    );
    if ($resIds = $this->catalog->databaseQueryFmt($sqlIds, $paramsIds)) {
      while ($rowIds = $resIds->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->subscribedIds[$rowIds['catalog_id']] = 1;
      }
    }
  }

  /**
  * loads all catalogs below $parentId; fills $this->catalogs (id => data)
  * and $this->catalogTree (id => array(childids))
  *
  * @access public
  * @param integer $parentId
  * @return boolean
  */
  function loadCatalogs($baseCatalogId) {
    unset($this->catalogs);
    unset($this->catalogTree);
    $sql = "SELECT c.catalog_id, c.catalog_parent, ct.lng_id, ct.catalog_title
              FROM %s AS c
              LEFT OUTER JOIN %s AS ct
                ON (ct.catalog_id=c.catalog_id AND ct.lng_id = '%d')
             WHERE c.catalog_parent_path
              LIKE '%%;%d;%%'
          ORDER BY ct.catalog_title, c.catalog_id DESC";
    $params = array(
      $this->catalog->tableCatalog,
      $this->catalog->tableCatalogTrans,
      $this->parentObj->getContentLanguageId(),
      $baseCatalogId
    );
    if ($res = $this->catalog->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->catalogs[(int)$row['catalog_id']] = $row;
        $this->catalogTree[(int)$row['catalog_parent']][] = $row['catalog_id'];
      }
      return TRUE;
    }
    return FALSE;
  }
}
?>
