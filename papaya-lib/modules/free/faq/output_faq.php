<?php
/**
* FAQ Output Module
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
* @subpackage Papaya-FAQ
* @version $Id: output_faq.php 38224 2013-02-28 21:42:38Z weinert $
*/

/**
* Basic class for faq
*/
require_once(dirname(__FILE__).'/base_faq.php');

/**
* FAQ Output Module
*
* @package Papaya-Modules
* @subpackage Papaya-FAQ
*/
class output_faq extends base_faq {

  /**
  * Get output
  *
  * @param array $data
  * @access public
  * @return string
  */
  function getOutput($data) {
    $result = '';
    $faqId = $data['faq'];
    $this->initializeParams();
    $this->backLink = '';
    $this->params['faq_id'] = $faqId;
    if (isset($this->params['entry_id']) && isset($this->params['cmd']) &&
        $this->params['entry_id'] > 0 && $this->params['cmd'] == 'add_note') {
      $this->addCommentOutput(
        (int)$this->params['entry_id'],
        empty($this->params['note_content']) ? '' : $this->params['note_content'],
        empty($this->params['note_username']) ? '' : $this->params['note_username'],
        $data
      );
    }

    if (isset($this->params['entry_id']) && $this->params['entry_id'] > 0) {
      if ($this->loadEntry($this->params['entry_id'])) {
        $this->params['faqgroup_id'] = $this->entry['faqgroup_id'];
      }
      $this->loadComments($this->params['entry_id']);
      $this->loadEntries($this->entry['faqgroup_id']);
      $this->loadFaqGroup($this->entry['faqgroup_id']);
    } elseif (isset($this->params['faqgroup_id']) &&
              $this->params['faqgroup_id'] > 0) {
      $this->loadEntries($this->params['faqgroup_id']);
    } elseif (isset($data['faq_group']) &&
              (int)$data['faq_group'] > 0) {
      $this->loadEntries($data['faq_group']);
    }
    $this->loadFaq($faqId);
    $this->loadFaqGroups($faqId);
    $result .= $this->getXMLOutputTreeList($data);
    return $result;
  }

  /**
  * Get XML output tree list
  *
  * @param array $data
  * @access public
  * @return string or FALSE
  */
  function getXMLOutputTreeList($data) {
    $faqId = $data['faq'];
    $result = '';
    if (isset($this->faq) && is_array($this->faq)) {
      $result .= sprintf(
        '<faq href="%s" title="%s" shref="%s">'.LF,
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('faq_id' => (int)$faqId))
        ),
        papaya_strings::escapeHTMLChars($this->faq['faq_title']),
        papaya_strings::escapeHTMLChars(
          $this->getLink(array('faq_id' => (int)$faqId, 'search' => 1))
        )
      );
      $result .= sprintf(
        '<descr>%s</descr>'.LF,
        papaya_strings::escapeHTMLChars($this->faq['faq_descr'])
      );
      if (isset($this->params['search']) && $this->params['search']) {
        $this->initializeSearchForm();
        if (isset($this->params['searchfor'])) {
          if (trim($this->params['searchfor']) != '') {
            $defaultGroup = NULL;
            if (isset($data['faq_group']) && (int)$data['faq_group'] > 0) {
              $defaultGroup = (int)$data['faq_group'];
            }
            $searched = $this->searchFaq($faqId, $this->params['searchfor'], $defaultGroup);
            if ($searched) {
              if (isset($this->entries) && is_array($this->entries)) {
                $result .= $this->getOutputEntries($data);
              } else {
                $result .= sprintf(
                  '<message no="2">%s</message>'.LF,
                  papaya_strings::escapeHTMLChars("msg_noresult")
                ); // msg_noresult
              }
            } else {
              $result .= sprintf(
                '<message no="2">%s</message>'.LF,
                papaya_strings::escapeHTMLChars("msg_noresult")
              ); // msg_noresult
            }
          } else {
            $result .= sprintf(
              '<message no="1">%s</message>'.LF,
              papaya_strings::escapeHTMLChars("msg_invalid")
            );
          }
        }
        $this->backLink = $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array('faq_id' => (int)$this->params['faq_id']),
          $this->paramName
        );
        $result .= $this->getSearchForm("caption_search");
      } elseif (isset($this->module->data['show_search']) &&
                $this->module->data['show_search'] == 1) {
        $this->initializeSearchForm();
        $result .= $this->getSearchForm("caption_search");
      }
      if (isset($this->faqGroups) && is_array($this->faqGroups)) {
        $result .= $this->getXMLOutputFaqGroupTreeList($data);
      }
      $result .= '</faq>'.LF;
      return $result;
    }
    return FALSE;
  }

  /**
  * Get XML output FAQ group tree list
  *
  * @param array $data
  * @access public
  * @return string or FALSE
  */
  function getXMLOutputFaqGroupTreeList($data) {
    $i = $data['faq'];
    $result = '';
    if (isset($this->faqGroups) && is_array($this->faqGroups)) {
      $result .= '<groups>'.LF;
      foreach ($this->faqGroups as $id => $faqGroup) {
        if (isset($faqGroup) && is_array($faqGroup)) {
          $selected = '';
          if ((
               isset($data['faq_group']) &&
               (int)$data['faq_group'] == $id &&
                empty($this->params['faqgroup_id'])
              ) ||
              (isset($this->params['faqgroup_id']) && $this->params['faqgroup_id'] == $id)) {
            $selected .= ' selected="selected"';
          }
          if (isset($data['faq_group']) && (int)$data['faq_group'] == $id) {
            $selected .= ' defaultGroup="true"';
          }
          if ($faqGroup['faq_id'] == $i) {
            $result .= sprintf(
              '<group href="%s" title="%s" entries="%d"%s>'.LF,
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(
                  NULL, NULL, NULL, array('faqgroup_id' => (int)$id), $this->paramName
                )
              ),
              papaya_strings::escapeHTMLChars($faqGroup['faqgroup_title']),
              (int)$faqGroup['entry_count'],
              $selected
            );
            if ('' != $faqGroup['faqgroup_descr']) {
              $result .= sprintf(
                '<descr>%s</descr>'.LF,
                papaya_strings::escapeHTMLChars($faqGroup['faqgroup_descr'])
              );
            }
            if (isset($this->entries) &&
                is_array($this->entries) &&
                (
                 (
                  isset($this->params['faqgroup_id']) &&
                  $this->params['faqgroup_id'] == $id
                 ) ||
                 (
                  isset($data['faq_group']) &&
                  (int)$data['faq_group'] == $id &&
                  empty($this->params['faqgroup_id'])
                 )
                )) {
              $result .= $this->getXMLOutputEntryList($id, $data);

            }
            $result .= '</group>'.LF;
          }
        }
      }
      $result .= '</groups>'.LF;
      return $result;
    }
    return FALSE;
  }

  /**
  * Get XML output entry list
  *
  * @param integer $i
  * @param array $data
  * @access public
  * @return string or FALSE
  * @author Massimiliano Siddi <info@papaya-cms.com>
  * @author Sebastian Janzen <janzen@papaya-cms.com>
  */
  function getXMLOutputEntryList($i, $data) {
    $result = '';

    $display_group_answers = (bool)$data['display_answers_in_group_list'];
    // if a single entry is selected set the display to true otherwise
    if (isset($this->params['entry_id'])) {
      $display_group_answers = TRUE;
    }

    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();
    if (isset($this->entries) && is_array($this->entries)) {
      $result .= '<entries>'.LF;
      foreach ($this->entries as $id => $entry) {
        if (isset($entry) && is_array($entry)) {
          if (isset($this->params['entry_id']) && $this->params['entry_id'] == $id) {
            $selected = 'selected="selected"';
          } else {
            $selected = '';
          }
          if (isset($entry['faqgroup_id']) && $entry['faqgroup_id'] == $i) {
            $result .= sprintf(
              '<entry  href="%s" title="%s" %s>'.LF,
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(NULL, NULL, NULL, array('entry_id' => (int)$id), $this->paramName)
              ),
              papaya_strings::escapeHTMLChars($entry['entry_title']),
              $selected
            );
            $question = trim($entry['entry_question']);
            $answer = trim($entry['entry_answer']);
            $isSelectedEntry = (
              isset($this->params['entry_id']) && $this->params['entry_id'] == $id
            );

            if ('' != $question) {
              $result .= sprintf(
                '<question>%s</question>'.LF,
                $this->module->getXHTMLString($question)
              );
            }
            if (($display_group_answers || $isSelectedEntry) && '' != $answer) {
              if ($isSelectedEntry || !$this->useShortAnswers) {
                $result .= sprintf(
                  '<answer>%s</answer>'.LF,
                  $this->module->getXHTMLString($answer)
                );
              } elseif ($this->useShortAnswers) {
                $dom = new  PapayaXmlDocument();
                $node = $dom->appendElement('answer');
                $node->appendXml($answer);
                $node = PapayaUtilStringXml::truncate(
                  $node,
                  $this->shortCharCount
                );
                $result .= $node->ownerDocument->saveXml($node);
              }
            }
            if ((isset($this->comments) && is_array($this->comments)) &&
                $isSelectedEntry) {
              $result .= $this->getXMLOutputCommentList($this->params['entry_id']);
            }
            if ($isSelectedEntry) {
              if ($data['post_comments'] != -1) {
                if ($this->surferObj->hasPerm($data['post_comments'])) {
                  $result .= $this->getOutputPermForm();
                  $result .= '</entry>'.LF;
                } else {
                  $result .= '</entry>'.LF;
                }
              } else {
                if (isset($this->surferObj) && is_object($this->surferObj) &&
                    $this->surferObj->isValid) {
                  $result .= $this->getOutputPermForm();
                  $result .= '</entry>'.LF;
                } else {
                  $result .= $this->getOutputAnonForm();
                  $result .= '</entry>'.LF;
                }
              }
            } else {
              $result .= '</entry>'.LF;
            }
          }
        }
      }
      $result .= '</entries>'.LF;
      return $result;
    }
    return FALSE;
  }

  /**
  * Get XML output comment list
  *
  * @param integer $i
  * @access public
  * @return string or FALSE
  */
  function getXMLOutputCommentList($i) {
    $result = '';
    if (isset($this->comments) && is_array($this->comments)) {
      $result .= '<notes>';
      foreach ($this->comments as $id => $note) {
        if (isset($note) && is_array($note)) {
          if (isset($this->params['note_id']) && $this->params['note_id'] == $id) {
            $selected = 'selected="selected"';
          } else {
            $selected = '';
          }
          if ($note['entry_id'] == $i) {
            $result .= sprintf(
              '<note href="%s" user="%s" created="%s" %s>%s'.LF,
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(NULL, NULL, NULL, array('note_id' => (int)$id), $this->paramName)
              ),
              papaya_strings::escapeHTMLChars($note['note_username']),
              date('Y-m-d H:i:s', $note['note_created']),
              $selected,
              $this->module->getXHTMLString($note['note_content'], TRUE)
            );
            $result .= '</note>'.LF;
          }
        }
      }
      $result .= '</notes>'.LF;
      return $result;
    }
    return FALSE;
  }

  /**
  * Initialize output anonym formular
  *
  * @access public
  */
  function initializeOutputAnonForm() {
    if (!is_object($this->outputDialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array(
        'cmd' => 'add_note',
        'save' => 1,
        'entry_id' => (int)$this->params['entry_id']
      );
      $fields = array(
        'note_username' => array('Username', 'isSomeText', TRUE, 'input', 200),
        'note_content' => array('Content', 'isSomeText', TRUE, 'textarea', 8)
      );

      $data = array();

      $this->outputDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->outputDialog->msgs = &$this->msgs;
      $this->outputDialog->buttonTitle = $this->module->data['comment_button'];
      $this->outputDialog->loadParams();
    }
  }

  /**
  * initialize output search formular
  *
  * @access public
  */
  function initializeOutputSearchForm() {
    if (!is_object($this->outputDialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array(
        'entry_id' => empty($this->params['entry_id']) ? 0 : (int)$this->params['entry_id']
      );
      $fields = array(
        'searchfor' => array('Search', 'isNoHTML', TRUE, 'input', 200),
      );
      $data = array();
      $this->outputDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->outputDialog->msgs = &$this->msgs;
      $this->outputDialog->buttonTitle = $this->module->data['search_button'];
      $this->outputDialog->loadParams();
    }
  }

  /**
  * Initialize output permission formular
  *
  * @access public
  */
  function initializeOutputPermForm() {
    if (!is_object($this->outputDialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      if (isset($this->surferObj) &&
          is_object($this->surferObj) &&
          $this->surferObj->isValid) {
        $data['note_username'] =
          $this->surferObj->surfer['surfer_givenname'].' '.
          $this->surferObj->surfer['surfer_surname'];
        $usernameInput = FALSE;
      } else {
        $usernameInput = TRUE;
      }
      $hidden = array('cmd' => 'add_note', 'save' => 1,
        'entry_id' => (int)$this->params['entry_id']);
      $fields = array(
        'note_username' => array('Username', 'isNoHTML', TRUE,
          (($usernameInput) ? 'input' : 'disabled_input'), 100),
        'note_content' => array('Message', 'isSomeText', TRUE, 'textarea', 8)
      );
      $this->outputDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->outputDialog->msgs = &$this->msgs;
      $this->outputDialog->buttonTitle = $this->module->data['comment_button'];
      $this->outputDialog->loadParams();
    }
  }

  /**
  * Get output anonym formular
  *
  * @access public
  * @return string
  */
  function getOutputAnonForm() {
    $this->initializeOutputAnonForm();
    $this->outputDialog->dialogDoubleButtons = FALSE;
    return '<newdlg>'.$this->outputDialog->getDialogXML().'</newdlg>';
  }

  /**
  * Get output permission formular
  *
  * @access public
  * @return string
  */
  function getOutputPermForm() {
    $this->initializeOutputPermForm();
    $this->outputDialog->dialogDoubleButtons = FALSE;
    return '<newdlg>'.$this->outputDialog->getDialogXML().'</newdlg>';
  }

  /**
  * Initialize search formular
  *
  * @access public
  */
  function initializeSearchForm() {
    if (!is_object($this->searchDialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $hidden = array(
        'note_id' => 0,
        'faq_id' => empty($this->params['faq_id'])
          ? 0 : (int)$this->params['faq_id'],
        'faqgropup_id' => empty($this->params['faqgroup_id'])
          ? 0 : (int)$this->params['faqgroup_id'],
        'entry_id' => empty($this->params['entry_id'])
          ? 0 : (int)$this->params['entry_id'],
        'search' => TRUE
      );
      $fields = array(
        'searchfor' => array('Search', 'isNoHTML', TRUE, 'input', 200),
      );
      $data = array();
      $this->searchDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->searchDialog->useToken = FALSE;
      $this->searchDialog->dialogMethod = 'get';
      $this->searchDialog->msgs = &$this->msgs;
      $this->searchDialog->buttonTitle = $this->module->data['search_button'];
      $this->searchDialog->loadParams();
    }
  }

  /**
  * Get search formular
  *
  * @param string $caption
  * @access public
  * @return string
  */
  function getSearchForm($caption) {
    $this->initializeOutputSearchForm();
    $this->searchDialog->baseLink = $this->baseLink;
    $this->searchDialog->dialogTitle = $caption;
    $this->searchDialog->dialogDoubleButtons = FALSE;
    return '<searchdlg>'.$this->searchDialog->getDialogXML().'</searchdlg>';
  }

  /**
  * Search entries
  *
  * @param integer $faqId
  * @param string $searchFor
  * @param array &$entryIds
  * @param integer $defaultGroup
  * @access public
  * @return boolean
  */
  function searchEntries($faqId, $searchFor, &$entryIds, $defaultGroup = NULL) {
    $entryIds = NULL;
    include_once(PAPAYA_INCLUDE_PATH.'system/base_searchstringparser.php');
    $parser = new searchstringparser;
    $condition = sprintf("g.faq_id = %d", $faqId);
    if (!is_null($defaultGroup) && (int)$defaultGroup > 0) {
      $condition .= sprintf(" AND g.faqgroup_id = %d", $defaultGroup);
    }
    $filter = $parser->getSQL(
      $searchFor,
      array('e.entry_question', 'e.entry_answer', 'e.entry_title', 'c.note_content'),
      $this->fullTextSearch
    );
    if ($filter) {
      $sql = "SELECT e.entry_id
                FROM %s g, %s e
                LEFT OUTER JOIN %s c ON c.entry_id = e.entry_id
               WHERE $condition
                 AND e.faqgroup_id = g.faqgroup_id
                 AND ( " . str_replace('%', '%%', $filter) . " )";
      $params = array($this->tableFaqgroups, $this->tableEntries, $this->tableComments);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow()) {
          if ($row[0] > 0) {
            $entryIds[$row[0]] = 1;
          } else {
            $entryIds[$row[1]] = 1;
          }
        }
        if (isset($entryIds) && is_array($entryIds)) {
          $entryIds = array_keys($entryIds);
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Search faq
  *
  * @param integer $faqId
  * @param string $searchFor
  * @param integer $defaultGroup
  * @access public
  * @return boolean
  */
  function searchFaq($faqId, $searchFor, $defaultGroup = NULL) {
    unset($this->entries);
    unset($entryIds);
    $searched = FALSE;
    if ($this->cacheSearchResults) {
      $searchResult =
        $this->getSessionValue($this->sessionParamName.'_searchresult');
      if (($searchResult['searchfor'] == $searchFor) &&
          ($searchResult['time'] > time())) {
        $entryIds = $searchResult['entry_ids'];
        $searched = TRUE;
      } else {
        $searched = $this->searchFaqGroups($faqId, $searchFor, $entryIds);
        if ($searched) {
          $searchResult = array(
            'searchfor' => $searchFor,
            'time' => time() + 1800,
            'entry_ids' => $entryIds
          );
          $this->setSessionValue(
            $this->sessionParamName.'_searchresult',
            $searchResult
          );
        }
      }
    } else {
      $searched = $this->searchEntries($faqId, $searchFor, $entryIds, $defaultGroup);
    }
    if (isset($entryIds) && is_array($entryIds)) {
      $filter = str_replace('%', '%%', $this->databaseGetSQLCondition('entry_id', $entryIds));
      $sql = "SELECT e.entry_id, e.entry_question, e.entry_answer, e.entry_title, g.faqgroup_title
                FROM %s e, %s g
               WHERE g.faqgroup_id = e.faqgroup_id
                 AND $filter";
      if ($res = $this->databaseQueryFmt($sql, array($this->tableEntries, $this->tableFaqgroups))) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->entries[$row['entry_id']] = $row;
        }
        return TRUE;
      }
    } else {
      return $searched;
    }
    return FALSE;
  }

  /**
  * Get output thread list
  *
  * @param array $data
  * @access public
  * @return string
  */
  function getOutputEntries($data) {
    $faqId = $data['faq'];
    $result = '';
    if (isset($this->entries) && is_array($this->entries)) {
      $result = sprintf('<results>'.LF);
      foreach ($this->entries as $entry) {
        $selected = ($entry['entry_id'] == $this->params['entry_id'])
          ? ' selected="selected"' : '';
        $result .= sprintf(
          '<result href="%s" title="%s" group="%s" %s>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink(
              NULL,
              NULL,
              NULL,
              array('entry_id'=>(int)$entry['entry_id']),
              $this->paramName
            )
          ),
          papaya_strings::escapeHTMLChars($entry['entry_title']),
          papaya_strings::escapeHTMLChars($entry['faqgroup_title']),
          $selected,
          papaya_strings::escapeHTMLChars($entry['entry_title'])
        );
        if (isset($this->module) && isset($this->module->data)
          && isset($this->module->data['show_ansers_in_searchresults'])
          && $this->module->data['show_ansers_in_searchresults'] == 1) {
          $result .= sprintf(
            '<question>%s</question>',
            $this->module->getXHTMLString($entry['entry_question'])
          );
          $result .= sprintf(
            '<answer>%s</answer>',
            $this->module->getXHTMLString($entry['entry_answer'])
          );
        } else {
          $result .= $this->module->getXHTMLString($entry['entry_question']);
        }
        $result .= '</result>'.LF;
      }
      $result .= '</results>'.LF;
    }
    return $result;
  }

  /**
  * Add comment output
  *
  * @param integer $parent
  * @param string $text
  * @param string $username
  * @param array $data
  * @access public
  * @return mixed
  */
  function addCommentOutput($parent, $text, $username, $data) {
    if ($this->entryExists($parent)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
      $this->surferObj = &base_surfer::getInstance();
      if ($data['post_comments'] != -1 &&
          !(
            isset($this->surferObj) &&
            is_object($this->surferObj) &&
            $this->surferObj->isValid &&
            $this->surferObj->hasPerm($data['post_comments'])
           )
          ) {
        return FALSE;
      } else {
        return $this->databaseInsertRecord(
          $this->tableComments,
          'note_id',
          array(
            'entry_id' => $parent,
            'note_content' => $text,
            'note_username' => $username,
            'note_created' => time()
          )
        );
      }
    }
    return FALSE;
  }
}
?>
