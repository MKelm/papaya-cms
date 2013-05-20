<?php
/**
* Page module subscribe to catalog
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
* @version $Id: content_catalog_subscribe.php 36224 2011-09-20 08:00:57Z weinert $
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
* Page module subscribe to catalog
*
* @package Papaya-Modules
* @subpackage Free-Catalog
*/
class content_catalog_subscribe extends base_content {

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
    'depth' =>
      array('Depth of categories', 'isNum', FALSE, 'input', 5, '', ''),
    'Email Options',
    'sender_name' =>
      array('Sender Name', 'isNoHTML', FALSE, 'input', 200, '', ''),
    'sender_email' =>
      array('Sender Email', 'isEmail', TRUE, 'input', 200, '', ''),
    'subject' =>
      array('Subject', 'isNoHTML', TRUE, 'input', 200, '', ''),
    'body' =>
      array('Body', 'isSomeText', TRUE, 'textarea', 5, '',
      "{%topics%}\n\n{%confirm_link%}"),
    'valid' =>
      array('Confirmation time', 'isNum', FALSE, 'input', 5, 'Confirmation valid for n hours', 24),
    'Captions',
    'email_field' =>
      array('Title of email field', 'isNoHTML', FALSE, 'input', 200, '',
        'Email address:'),
    'subscribe_field' =>
      array('Title of subscribe button', 'isNoHTML', FALSE, 'input', 200, '',
        'Subscribe'),
    'Messages',
    'invalid_email' =>
      array('Invalid email address', 'isNoHTML', FALSE, 'input', 200, '',
      'The email address you entered is invalid.'),
    'nothing_selected' =>
      array('Nothing selected', 'isNoHTML', FALSE, 'input', 200, '',
      'You haven\'t select any categories.'),
    'email_send' =>
      array('Email sent', 'isNoHTML', FALSE, 'input', 200, '',
        'A confirmation email has been sent to you. Please check your mailbox and
         follow the enclosed instructions.'),
    'subscription_completed' =>
      array('Subscription completed', 'isNoHTML', FALSE, 'input', 200, '',
      'Your subscription has been completed.'),
    'no_pending' =>
      array('No pending subscriptions', 'isNoHTML', FALSE, 'input', 200, '',
        'No pending subscriptions found. Perhaps you are already subscribed to
         these categories.'),
    'nothing_new' =>
      array('No new selections', 'isNoHTML', FALSE, 'input', 200,
        'The user didn\'t select any new categories.',
        'You are already subscribed to the selected categories.'),
    'ignored_categories' =>
      array('Ignored categories', 'isNoHTML', FALSE, 'input', 200, '',
        'The following categories were not added because you selected their
         parent categories:'),
    'pending_categories' =>
      array('Pending categories', 'isNoHTML', FALSE, 'input', 200, '',
        'The following categories were added and are awaiting your confirmation:'),
  );

  /**
  * Base catalog object
  * @var object base_catalog $catalog
  */
  var $catalog = NULL;

  /**
  * Handler for hiding catalog
  * @var boolean $hideCatalog
  */
  var $hideCatalog = FALSE;

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

  /**
  * removed ids
  * @var array $removedIds
  */
  var $removedIds = array();

  /**
  * removed categories
  * @var array $removedCategories
  */
  var $removedCategories = array();

  /**
  * selected categories
  * @var array $selectedCategories
  */
  var $selectedCategories = array();

  /**
  * load catalog and set table names
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
  * @return string
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
    if (isset($_GET['refpage']) && trim($_GET['refpage']) != '') {
      $this->params['topic_id'] = (int)$_GET['refpage'];
      $this->params['mode'] = 'related';
      $result .= sprintf(
        '<backlink href="%s" />',
        papaya_strings::escapeHTMLChars(
          $this->getWebLink(
            (int)$_GET['refpage']
          ).$this->recodeQueryString(@$_GET['urlparams'])
        )
      );
    }
    $this->initializeOutputObjects();
    $this->executeOutputObjects();
    $result .= $this->getMsgs();
    if (!$this->hideCatalog) {
      $result .= $this->getCatalogXML();
    }
    return $result;
  }

  /**
  * generate messages XML for user feedback
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
      return $result;
    }
  }

  /**
  * execute subscription process steps
  *
  * @access public
  */
  function executeOutputObjects() {
    $this->loadCatalogs($this->data['categ_base_id']);
    if (isset($this->params['submitted']) && $this->params['submitted'] == 1 &&
        $this->checkSubscription()) {
      $this->processSubscription();
      $this->hideCatalog = TRUE;
    } elseif (isset($this->params['mode']) && $this->params['mode'] == 'related' &&
              !empty($this->params['topic_id'])) {
      $this->loadRelatedCatalogs(
        $this->params['topic_id'],
        $this->parentObj->getContentLanguageId(),
        $this->data['categ_base_id']
      );
    } elseif (isset($this->params['cmd']) && $this->params['cmd'] == 'confirm') {
      $this->processConfirmation($this->params['cfstr']);
      $this->hideCatalog = TRUE;
    }
  }

  /**
  * add pending subscription to db
  *
  * @param string $action add
  * @access public
  */
  function processSubscription() {
    if ($confirmString = $this->getConfirmString()) {
      if ($subscriptionId = $this->getSubscriptionId()) {
        $data = array(
          'catalog_subscription_catalogids' =>
            implode(',', array_keys($this->params['catalog_ids'])),
          'catalog_subscription_valid' =>
            time() + (int)$this->data['valid'] * 3600,
          'catalog_subscription_confirmstring' =>
            $confirmString,
          'catalog_subscription_id' =>
            $subscriptionId,
        );
        if ($this->catalog->databaseInsertRecord(
              $this->tableCatalogSubscriptionsPending, 'catalog_pending_id', $data)) {
          $confirmLink = $this->getAbsoluteURL(
            $this->getWebLink(
              $this->parentObj->topicId,
              NULL,
              NULL,
              array(
                'cmd' => 'confirm',
                'cfstr' => $confirmString
              ),
              $this->paramName
            )
          );
          $topics = '';
          foreach ($this->params['catalog_ids'] as $catalogId => $set) {
            $topics .= $this->catalogs[$catalogId]['catalog_title'].LF;
          }
          $bodyData = array('confirm_link' => $confirmLink, 'topics' => $topics);
          include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
          $emailObj = new email;
          $emailObj->setTemplate('body', $this->data['body'], $bodyData);
          if (isset($this->data['sender_email'])) {
            $emailObj->setSender(
              $this->data['sender_email'], @$this->data['sender_name']);
          }
          if ($emailObj->send($this->params['email'], $this->data['subject'])) {
            if (isset($this->removedCategories) && is_array($this->removedCategories)
              && count($this->removedCategories) > 0) {
                $this->msgs[] = array(MSG_INFO,
                  $this->data['ignored_categories'].' '.
                    implode(', ', $this->removedCategories));
            }
            $this->msgs[] = array(MSG_INFO, @$this->data['email_sent']);
            $this->msgs[] = array(MSG_INFO,
              $this->data['pending_categories'].' '.
                implode(', ', $this->selectedCategories));
          } else {
            $this->msgs[] = array(MSG_ERROR,
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
            $this->_gt(
              'DB Error: Your subscription could not be processed.'.
              ' The error has been reported and will be fixed. Please try again later.'
            )
          );
          $this->logMsg(
            MSG_ERROR,
            PAPAYA_LOGTYPE_MODULES,
            'Pending subscription DB record could not be inserted.',
            'insertRecord pending subscription'
          );
        }
      }
    }
  }

  /**
  * get subscriptionId for submitted email
  *
  * @access public
  * @return integer $subscriptionId subscription ID
  */
  function getSubscriptionId() {
    $subscriptionId = FALSE;
    $sql = "SELECT catalog_subscription_id
              FROM %s
             WHERE catalog_subscription_email = '%s'";
    $res = $this->catalog->databaseQueryFmt(
      $sql, array($this->tableCatalogSubscriptionsMeta, $this->params['email'])
    );
    if ($res) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $subscriptionId = $row['catalog_subscription_id'];
      } else {
        $dataMeta = array(
          'catalog_subscription_email' => $this->params['email'],
          'lng_id' => $this->parentObj->getContentLanguageId(),
        );
        $subscriptionId = $this->catalog->databaseInsertRecord(
          $this->tableCatalogSubscriptionsMeta,
          'catalog_subscription_id',
          $dataMeta
        );
      }
    }
    return $subscriptionId;
  }

  /**
  * check submitted values integrity
  *
  * @access public
  * @return boolean $valid TRUE if submitted values are senseful and usable
  */
  function checkSubscription() {
    $valid = TRUE;
    if (!checkit::isEmail(@$this->params['email'], TRUE)) {
      $this->msgs[] = array(MSG_ERROR, $this->data['invalid_email']);
      $valid = FALSE;
    }
    if (!is_array(@$this->params['catalog_ids']) ||
        count($this->params['catalog_ids']) < 1) {
      $this->msgs[] = array(MSG_ERROR, $this->data['nothing_selected']);
      $valid = FALSE;
    }
    if (is_array(@$this->params['catalog_ids']) &&
       count($this->params['catalog_ids']) > 0) {
      $this->params['catalog_ids'] = $this->stripChildIds($this->params['catalog_ids']);
    }
    return $valid;
  }

  /**
  * if parent id is also selected, selected subcategory is redundant and removed
  *
  * @access public
  * @param array $ids array of selected ids
  * @return array $ids array of stripped ids
  */
  function stripChildIds($ids) {
    if (is_array($ids) && count($ids) > 0) {
      unset ($this->removedIds);
      $newIds = $ids;
      foreach ($ids as $id => $set) {
        if ($this->parentSet($id, $ids)) {
          unset($newIds[$id]);
          $this->removedIds[] = $id;
          $this->removedCategories[] = $this->catalogs[$id]['catalog_title'];
        } else {
          $this->selectedCategories[] = $this->catalogs[$id]['catalog_title'];
        }
      }
    }
    return $newIds;
  }

  /**
  * checks whether parent of id is in ids (catalog ids)
  *
  * @param integer $id id to check
  * @param array $ids array of ids
  * @return boolean  TRUE if parent is set, else FALSE
  */
  function parentSet($id, $ids) {
    $parentId = @$this->catalogs[$id]['catalog_parent'];
    if ($id != $this->data['categ_base_id'] &&
       (isset($ids[$parentId]) || $this->parentSet($parentId, $ids))) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * generates unique confirmation string
  *
  * @access public
  * @return mixed
  */
  function getConfirmString() {
    $sql = "SELECT catalog_subscription_confirmstring
              FROM %s";
    $res = $this->catalog->databaseQueryFmt(
      $sql, array($this->tableCatalogSubscriptionsPending)
    );
    if ($res) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row ['catalog_subscription_confirmstring']) {
          $cfStrings[$row['catalog_subscription_confirmstring']] = TRUE;
        }
      }
      srand((double)microtime());
      do {
        $cfStr = md5(uniqid(rand(), 1));
      } while (isset($cfStrings) && is_array($cfStrings)
        && count($cfStrings) > 0 && isset($cfStrings[$cfStr]));
      return $cfStr;
    }
    return FALSE;
  }

  /**
  * add/rem categories from pending table to/from subscriptions
  * where confirmstring is $cfStr
  *
  * @access public
  * @param string $cfStr confirmation string
  */
  function processConfirmation($cfStr) {
    $success = FALSE;
    if ($cfStr != '') {
      $sql = "SELECT catalog_subscription_id,
                      catalog_subscription_catalogids,
                      catalog_subscription_valid
                FROM %s
               WHERE catalog_subscription_confirmstring = '%s'";
      $params = array($this->tableCatalogSubscriptionsPending, $cfStr);
      // there is a record for this cfStr in pending table
      if ($res = $this->catalog->databaseQueryFmt($sql, $params)) {
        if ($data = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $sql = "SELECT s.catalog_id
                    FROM %s AS s
                    LEFT OUTER JOIN %s AS m
                      ON (m.catalog_subscription_id = s.catalog_subscription_id)
                   WHERE s.catalog_subscription_id = '%d'";
          $params = array($this->tableCatalogSubscriptions,
                          $this->tableCatalogSubscriptionsMeta,
                          $data['catalog_subscription_id']);
          // there is a record for this id in meta (should be!)
          if ($res = $this->catalog->databaseQueryFmt($sql, $params)) {
            $subscribedIds = array();
            while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
              $subscribedIds[] = $row['catalog_id'];
            }
            $omittedIds = array_intersect(
              explode(',', $data['catalog_subscription_catalogids']),
              $subscribedIds
            );
            $ids = array_diff(
              explode(',', $data['catalog_subscription_catalogids']),
              $subscribedIds
            );
            if (isset($ids) && is_array($ids) && count($ids) > 0) {
              foreach ($ids as $id) {
                $values[] = array(
                  'catalog_subscription_id' => $data['catalog_subscription_id'],
                  'catalog_id' => $id,
                );
                $subscribed[] = $this->catalogs[$id]['catalog_title'];
              }
              if ($this->catalog->databaseInsertRecords(
                    $this->tableCatalogSubscriptions, $values)) {
                if (is_array(@$omittedIds) && count($omittedIds) > 0) {
                  foreach ($omittedIds as $id) {
                    $omitted[] = $this->catalogs[$id]['catalog_title'];
                  }
                  $this->msgs[] = array(MSG_INFO,
                    $this->data['ignored_categories'].' '.implode(', ', $omitted));
                }
                $this->msgs[] = array(MSG_INFO,
                  $this->data['subscription_completed'].' '.implode(', ', $subscribed));
                $success = TRUE;
              } else {
                $this->msgs[] = array(MSG_ERROR,
                  'DB Error: Subscription(s) could not be added.');
              }
            } else {
              $this->msgs[] = array(MSG_INFO, $this->data['nothing_new']);
            }
            $condition = array('catalog_subscription_confirmstring' => $cfStr);
            if (!$this->catalog->databaseDeleteRecord(
                   $this->tableCatalogSubscriptionsPending, $condition)) {
              $this->msgs[] = array(MSG_INFO,
                'Pending subscription could not be deleted,'.
                ' but it\'s nothing to worry about.');
            }
          }
        } else {
          $this->msgs[] = array(MSG_INFO, $this->data['no_pending']);
        }
      } else {
        $this->msgs[] = array(MSG_INFO, $this->data['no_pending']);
      }
    }
  }

  /**
  * generate XML for catalog
  *
  * @access public
  * @return string $result catalog XML
  * @uses content_catalog_subscribe::getSelectList
  */
  function getCatalogXML() {
    $result = '';
    $params = array(
      $this->paramName => array(
        'submitted' => '1',
        'mode' => @$this->params['mode'],
      ),
      'refpage' => @(int)$_GET['refpage'],
      'urlparams' => $this->recodeQueryString(@$_GET['urlparams']),
    );
    if (empty($this->params['topic_id'])) {
      $params['topic_id'] = $this->params['topic_id'];
    }
    $result .= sprintf(
      '<subscribe param_name="%s" href="%s">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars(
        $this->getWebLink(
          NULL,
          NULL,
          NULL,
          $params
        )
      )
    );
    $result .= sprintf(
      '<mailfieldname>%s</mailfieldname>',
      papaya_strings::escapeHTMLChars($this->data['email_field'])
    );
    $result .= sprintf(
      '<subscribefield>%s</subscribefield>',
      papaya_strings::escapeHTMLChars($this->data['subscribe_field'])
    );
    $result .= $this->getSelectList($this->data['categ_base_id']);
    $result .= '</subscribe>';
    return $result;
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
    if (isset($this->catalogTree[$parent]) &&
        is_array($this->catalogTree[$parent]) &&
        $indent < $this->data['depth']) {
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
    switch (@$this->params['mode']) {
    case 'related':
      if (isset($this->catalogRelated) && isset($this->catalogs[$id]) &&
          is_array($this->catalogs[$id])&& isset($this->catalogRelated[$id])) {
        $parentId = $id;
        do {
          $parents[] = $this->catalogs[$parentId];
          $parentId = $this->catalogs[$parentId]['catalog_parent'];
        } while ($parentId != $this->data['categ_base_id']);
        $parents = array_reverse($parents);
        $i = 0;
        foreach ($parents as $i => $category) {
          if (isset($this->params['catalog_ids'][$category['catalog_id']])
            || (!@$this->params['submitted'] && $i == 0)) {
              $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $result .= sprintf(
            '<listitem catalog_id="%d" title="%s" indent="%d" %s></listitem>',
            (int)$category['catalog_id'],
            papaya_strings::escapeHTMLChars($category['catalog_title']),
            $i++,
            $selected
          );
        }
      }
      $result .= $this->getSelectList($id);
      break;
    case 'all':
    default:
      if (isset($this->catalogs[$id]) && is_array($this->catalogs[$id])) {
        $selected = (isset($this->params['catalog_ids'][$id]))
          ? ' selected="selected"' : '';
        $result .= sprintf(
          '<listitem catalog_id="%d" title="%s" indent="%d" %s>',
          (int)$id,
          papaya_strings::escapeHTMLChars($this->catalogs[$id]['catalog_title']),
          $indent,
          $selected
        );
        $subItems = $this->getSelectList($id, ++$indent);
        if ($subItems != '') {
          $result .= sprintf('<list>%s</list>', $subItems);
        }
        $result .= '</listitem>';
      }
    }
    return $result;
  }

  /**
  * load related categories; fills $this->catalogRelated
  *
  * @access public
  * @param integer $topicId topic id
  * @param integer $lngId language id
  * @param integer $baseCatalogId catalog id to start with
  */
  function loadRelatedCatalogs($topicId, $lngId, $baseCatalogId) {
    unset($this->catalogRelated);
    $sql = "SELECT ct.catalog_id, ct.catalog_title, ct.catalog_glyph,
                   c.catalog_parent_path, c.catalog_parent
              FROM %s AS cl, %s AS ct, %s AS c
             WHERE cl.topic_id = '%d'
               AND cl.lng_id = '%d'
               AND c.catalog_id = cl.catalog_id
               AND cl.catalog_id = ct.catalog_id
               AND ct.lng_id = cl.lng_id
             ORDER BY ct.catalog_title";
    $params = array($this->catalog->tableCatalogLinks,
                    $this->catalog->tableCatalogTrans,
                    $this->catalog->tableCatalog,
                    $topicId,
                    $lngId);
    if ($res = $this->catalog->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->catalogRelated[$row['catalog_id']] = $row;
      }
    }
  }

  /**
  * loads all catalogs below $parentId; fills $this->catalogs (id => data) and
  * $this->catalogTree (id => array(childids))
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
              LEFT OUTER JOIN %s AS ct ON (ct.catalog_id=c.catalog_id AND ct.lng_id = '%d')
             WHERE c.catalog_parent_path LIKE '%%;%d;%%'
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
