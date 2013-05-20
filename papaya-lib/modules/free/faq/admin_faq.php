<?php
/**
* FAQ administration functions module
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
* @subpackage Free-FAQ
* @version $Id: admin_faq.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Basic class for faq
*/
require_once(dirname(__FILE__).'/base_faq.php');

/**
* FAQ administration functions module
*
* @package Papaya-Modules
* @subpackage Free-FAQ
*/
class admin_faq extends base_faq {

  /**
  * Initialize parameters
  *
  * @access public
  */
  function initialize() {
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam(
      'faq_id',
      array('faqgroup_id', 'entry_id', 'note_id')
    );
    $this->initializeSessionParam(
      'faqgroup_id',
      array('entry_id', 'note_id')
    );
    $this->initializeSessionParam('entry_id', array('note_id'));
    $this->initializeSessionParam('note_id', array('cmd'));
    $imagePath = 'module:'.$this->module->guid;
    $this->localImages = array(
      'faq-add' => $imagePath."/faq-add.png",
      'faq-delete' => $imagePath."/faq-delete.png",
      'message-add' => $imagePath."/message-add.png",
      'message-delete' => $imagePath."/message-delete.png"
    );
  }

  /**
  * Execute - basic function for handling parameters
  *
  * @access public
  */
  function execute() {
    if (isset($this->params['cmd'])) {
      switch ($this->params['cmd']) {
      case 'add_faq':
        if ( $this->module->hasPerm(3) ) {
          if ($newId = $this->addFaq()) {
            $this->addMsg(MSG_INFO, $this->_gt('FAQ added.'));
            $this->params['faq_id'] = $newId;
            $this->initializeSessionParam('faq_id', array('cmd'));
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
        break;
      case 'del_faq':
        if ( $this->module->hasPerm(3) ) {
          if (isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete']) {
            if ($this->faqExists($this->params['faq_id'])) {
              if ($this->deleteFaq($this->params['faq_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('FAQ deleted.'));
                $this->params['faq_id'] = 0;
                $this->initializeSessionParam('faq_id', array('cmd'));
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            } else {
              $this->addMsg(MSG_WARNING, $this->_gt('FAQ not found.'));
            }
          }
        }
        break;
      case 'edit_faq':
        if ( $this->module->hasPerm(3) || $this->module->hasPerm(3) ) {
          $this->loadFaqs($this->params['faq_id']);
          $this->initializeFaqEditform();
          if ($this->faqDialog->modified()) {
            if ($this->faqDialog->checkDialogInput()) {
              if ($this->saveFaq()) {
                $this->addMsg(MSG_INFO, $this->_gt('FAQ modified.'));
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            }
          }
        }
        break;
      case 'change_faqgroup':
      case 'add_faqgroup':
      case 'edit_faqgroup' :
        if ($this->module->hasPerm(3)) {
          $this->executeGroupDialog();
        }
        break;
      case 'del_faqgroup':
        if ($this->module->hasPerm(3)) {
          if (isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete']) {
            if ($this->faqGroupExists($this->params['faqgroup_id'])) {
              if ($this->deleteFaqGroup($this->params['faqgroup_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('FAQ group deleted.'));
                $this->params['faqgroup_id'] = 0;
                $this->initializeSessionParam('faqgroup_id', array('cmd'));
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            } else {
              $this->addMsg(MSG_WARNING, $this->_gt('FAQ group not found.'));
            }
          }
        }
        break;
      case 'faqgroup_up' :
        if ($this->module->hasPerm(3) || $this->module->hasPerm(3)) {
          $this->loadFaqGroups($this->params['faq_id']);
          if (isset($this->params['faqgroup_id']) &&
              isset($this->faqGroups) &&
              isset($this->faqGroups[$this->params['faqgroup_id']]) &&
              $this->faqGroups[$this->params['faqgroup_id']]['faqgroup_position'] > 1) {
            $currentPosition = $this->faqGroups[$this->params['faqgroup_id']]['faqgroup_position'];
            $newPosition = $currentPosition - 1;
            $faqGroupIds = array_keys($this->faqGroups);
            $positions = array(
              $this->params['faqgroup_id'] => $newPosition,
              (int)$faqGroupIds[$newPosition - 1] => $currentPosition
            );
            $this->saveFaqGroupPositions($positions);
          }
        }
        break;
      case 'faqgroup_down' :
        if ($this->module->hasPerm(3) || $this->module->hasPerm(3)) {
          $this->loadFaqGroups($this->params['faq_id']);
          if (isset($this->params['faqgroup_id']) &&
              isset($this->faqGroups) &&
              isset($this->faqGroups[$this->params['faqgroup_id']]) &&
              $this->faqGroups[$this->params['faqgroup_id']]['faqgroup_position'] <
                count($this->faqGroups)) {
            $currentPosition = $this->faqGroups[$this->params['faqgroup_id']]['faqgroup_position'];
            $newPosition = $currentPosition + 1;
            $faqGroupIds = array_keys($this->faqGroups);
            $positions = array(
              $this->params['faqgroup_id'] => $newPosition,
              $faqGroupIds[$newPosition - 1] => $currentPosition
            );
            $this->saveFaqGroupPositions($positions);
          }
        }
        break;
      case 'add_entry' :
        if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
          $this->loadEntries($this->params['faqgroup_id']);
          if ($newId = $this->addEntry((int)$this->params['faqgroup_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Entry added.'));
            $this->params['entry_id'] = $newId;
            $this->initializeSessionParam('entry_id', array('cmd'));
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
        break;
      case 'edit_entry' :
        if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
          $this->loadFaqs();
          $this->loadFaqGroups($this->params['faq_id']);
          $this->loadEntries($this->params['faqgroup_id']);
          $this->initializeEntryEditForm();
          if ($this->faqDialog->modified()) {
            if ($this->faqDialog->checkDialogInput()) {
              if ($this->saveEntry()) {
                $this->addMsg(MSG_INFO, $this->_gt('Entry modified.'));
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            }
          }
        }
        break;
      case 'del_entry':
        if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
          if (isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete']) {
            if ($this->entryExists($this->params['entry_id'])) {
              if ($this->deleteEntry($this->params['entry_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Entry deleted.'));
                $this->params['entry_id'] = 0;
                $this->initializeSessionParam('entry_id', array('cmd'));
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            } else {
              $this->addMsg(MSG_WARNING, $this->_gt('FAQ group not found.'));
            }
          }
        }
        break;
      case 'entry_up' :
        if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
          $this->loadEntries($this->params['faqgroup_id']);
          if (isset($this->params['entry_id']) &&
              isset($this->entries) &&
              isset($this->entries[$this->params['entry_id']]) &&
              $this->entries[$this->params['entry_id']]['entry_position'] > 1) {
            $currentPosition = $this->entries[$this->params['entry_id']]['entry_position'];
            $newPosition = $currentPosition - 1;
            $entryIds = array_keys($this->entries);
            $positions = array(
              $this->params['entry_id'] => $newPosition,
              (int)$entryIds[$newPosition - 1] => $currentPosition
            );
            $this->saveEntryPositions($positions);
          }
        }
        break;
      case 'entry_down' :
        if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
          $this->loadEntries($this->params['faqgroup_id']);
          if (isset($this->params['entry_id']) &&
              isset($this->entries) &&
              isset($this->entries[$this->params['entry_id']]) &&
              $this->entries[$this->params['entry_id']]['entry_position'] <
                count($this->entries)) {
            $currentPosition = $this->entries[$this->params['entry_id']]['entry_position'];
            $newPosition = $currentPosition + 1;
            $entryIds = array_keys($this->entries);
            $positions = array(
              $this->params['entry_id'] => $newPosition,
              (int)$entryIds[$newPosition - 1] => $currentPosition
            );
            $this->saveEntryPositions($positions);
          }
        }
        break;
      case 'add_comment':
        if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
          if ($newId = $this->addComment((int)$this->params['entry_id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Comment added.'));
            $this->params['note_id'] = $newId;
            $this->initializeSessionParam('note_id', array('cmd'));
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database error! Changes not saved.')
            );
          }
        }
        break;
      case 'del_comment':
        if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
          if (isset($this->params['confirm_delete']) &&
              $this->params['confirm_delete']) {
            if ($this->commentExists($this->params['note_id'])) {
              if ($this->deleteComment($this->params['note_id'])) {
                $this->addMsg(MSG_INFO, $this->_gt('Comment deleted.'));
                $this->params['note_id'] = 0;
                $this->initializeSessionParam('note_id', array('cmd'));
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            } else {
              $this->addMsg(MSG_WARNING, $this->_gt('FAQ entry not found.'));
            }
          }
        }
        break;
      case 'edit_comment':
        if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
          $this->loadFaqs();
          $this->loadFaqGroups($this->params['faq_id']);
          $this->loadEntries($this->params['faqgroup_id']);
          $this->loadComments($this->params['entry_id']);
          $this->initializeCommentEditForm();
          if ($this->faqDialog->modified()) {
            if ($this->faqDialog->checkDialogInput()) {
              if ($this->saveComment()) {
                $this->addMsg(
                  MSG_INFO,
                  sprintf($this->_gt('%s modified.'), $this->_gt('Comment'))
                );
              } else {
                $this->addMsg(
                  MSG_ERROR,
                  $this->_gt('Database error! Changes not saved.')
                );
              }
            }
          }
        }
        break;
      }
    }
    // store new session data
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    $this->loadFaqs();
    $this->loadFaqGroups($this->params['faq_id']);
    $this->loadEntries($this->params['faqgroup_id']);
    $this->loadComments($this->params['entry_id']);
  }

  /**
  * Get XML output
  *
  * @access public
  * @return string
  */
  function getXML() {
    if (is_object($this->layout)) {
      $this->layout->setParam('COLUMNWIDTH_LEFT', '300px');
      $this->getXMLButtons();
      $this->getXMLTreeList();
      $this->getXMLEntryList();
      if (!isset($this->params['cmd'])) {
        $this->params['cmd'] = '';
      }
      switch ($this->params['cmd']) {
      case 'edit_faq':
        unset($this->comments);
        unset($this->entries);
        unset($this->faqGroups);
        $this->getXMLFaqForm();
        break;
      case 'edit_faqgroup':
        unset($this->comments);
        unset($this->entries);
        $this->getXMLFaqGroupForm();
        break;
      case 'edit_entry':
        $this->getXMLEntryForm();
        break;
      case 'del_faq':
        $this->getXMLDelFaqForm();
        break;
      case 'del_faqgroup':
        $this->getXMLDelFaqGroupForm();
        break;
      case 'del_entry':
        $this->getXMLDelEntryForm();
        break;
      case 'del_comment':
        $this->getXMLDelCommentForm();
        break;
      default:
        // die eione  mit details
        if (isset($this->comments[$this->params['note_id']]) &&
            is_array($this->comments[$this->params['note_id']])) {
          $this->getXMLCommentForm();
        } elseif (isset($this->entries[$this->params['entry_id']]) &&
            is_array($this->entries[$this->params['entry_id']])) {
          $this->getXMLEntryForm();
        } elseif (isset($this->faqGroups[$this->params['faqgroup_id']]) &&
            is_array($this->faqGroups[$this->params['faqgroup_id']])) {
          $this->getXMLFaqGroupForm();
        } elseif (isset($this->faqs[$this->params['faq_id']]) &&
            is_array($this->faqs[$this->params['faq_id']])) {
          $this->getXMLFaqForm();
        }
      }
      $this->getXMLCommentList();
    }
  }

  /**
  * Execute group dialog to add/edit faq groups
  */
  function executeGroupDialog() {
    if (isset($this->params['faq_id']) && $this->params['faq_id'] > 0) {
      $dialog = new PapayaUiDialogDatabaseRecord(
        $this->tableFaqgroups,
        'faqgroup_id',
        array(
          'faq_id' => new PapayaFilterInteger(1),
          'faqgroup_id' => new PapayaFilterInteger(1),
          'faqgroup_title' => NULL,
          'faqgroup_descr' => NULL
        )
      );
      $dialog->options()->captionStyle = PapayaUiDialogOptions::CAPTION_SIDE;
      $dialog->parameterGroup($this->paramName);
      $dialog->hiddenFields()->set('cmd', 'edit_faqgroup');
      $dialog->hiddenFields()->set('faq_id', $this->params['faq_id']);
      $dialog
        ->fields()
        ->add(
          new PapayaUiDialogFieldInput(
            new PapayaUiStringTranslated('Title'),
            'faqgroup_title',
            255,
            new PapayaUiStringTranslated('Group'),
            new PapayaFilterLogicalAnd(
              new PapayaFilterNotEmpty(),
              new PapayaFilterNoLinebreak()
            )
          )
        )
        ->add(
          new PapayaUiDialogFieldTextarea(
            new PapayaUiStringTranslated('Description'),
            'faqgroup_descr',
            10,
            '',
            new PapayaFilterNotEmpty()
          )
        );
      if ($dialog->execute()) {
        $this->papaya()->messages->dispatch(
          new PapayaMessageDisplayTranslated(
            PapayaMessage::TYPE_INFO, 'Faq group saved.'
          )
        );
      } elseif ($dialog->isSubmitted()) {
        $this->papaya()->messages->dispatch(
          new PapayaMessageDisplayTranslated(
            PapayaMessage::TYPE_ERROR,
            'Invalid input. Please check the fields "%s".',
            array(implode(', ', $dialog->errors()->getSourceCaptions()))
          )
        );
      }
      if ($dialog->getDatabaseActionNext() == PapayaUiDialogDatabaseRecord::ACTION_UPDATE) {
        $button = new PapayaUiDialogButtonSubmit('Change');
        $dialog->caption = new PapayaUiStringTranslated('Change faq group');
      } else {
        $button = new PapayaUiDialogButtonSubmit('Add');
        $dialog->caption = new PapayaUiStringTranslated('Add faq group');
      }
      $dialog->buttons()->add($button);
      $this->layout->add($dialog->getXml());
    }
  }

  /**
  * Get XML faq form
  *
  * @access public
  */
  function getXMLFaqForm() {
    if ($this->module->hasPerm(3)) {
      if (isset($this->faqs[$this->params['faq_id']]) &&
          is_array($this->faqs[$this->params['faq_id']])) {
        $this->initializeFaqEditForm();
        $this->faqDialog->dialogTitle = $this->_gt('Properties');
        $this->faqDialog->dialogDoubleButtons = FALSE;
        $this->layout->add($this->faqDialog->getDialogXML());
      }
    } else {
      if (isset($this->faqs[$this->params['faq_id']]) &&
          is_array($this->faqs[$this->params['faq_id']])) {
        $outputXML = sprintf(
          '<listview title="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Properties'))
        );
        $outputXML .= '<items>'.LF;
        $outputXML .= sprintf(
          '<listitem title="%s"><sutitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Title')),
          papaya_strings::escapeHTMLChars(
            $this->faqs[$this->params['faq_id']]['faq_title']
          )
        );
        $outputXML .= sprintf(
          '<listitem title="%s"><sutitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Description')),
          papaya_strings::escapeHTMLChars(
            $this->faqs[$this->params['faq_id']]['faq_descr']
          )
        );
        $outputXML .= '</items>'.LF;
        $outputXML .= '</listview>'.LF;
        $this->layout->add($ouputXML);
      }
    }
  }

  /**
  * Get XML faq group form
  *
  * @access public
  */
  function getXMLFaqGroupForm() {
    if (!$this->module->hasPerm(3)) {
      if (isset($this->faqGroups[$this->params['faqgroup_id']]) &&
          is_array($this->faqGroups[$this->params['faqgroup_id']])) {
        $faqId = $this->params['faqgroup_id'];
        $outputXML = sprintf(
          '<listview title="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Properties'))
        );
        $outputXML .= '<items>'.LF;
        $outputXML .= sprintf(
          '<listitem title="%s"><sutitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Title')),
          papaya_strings::escapeHTMLChars(
            $this->faqGroups[$faqId]['faqgroup_title']
          )
        );
        $outputXML .= sprintf(
          '<listitem title="%s"><sutitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Description')),
          papaya_strings::escapeHTMLChars(
            $this->faqGroups[$faqId]['faqgroup_descr']
          )
        );
        $outputXML .= '</items>'.LF;
        $outputXML .= '</listview>'.LF;
        $this->layout->add($ouputXML);
      }
    }
  }


  /**
  * Get XML entry form
  *
  * @access public
  */
  function getXMLEntryForm() {
    if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
      if (isset($this->entries[$this->params['entry_id']]) &&
          is_array($this->entries[$this->params['entry_id']])) {
        $this->initializeEntryEditForm();
        $this->faqDialog->dialogTitle = $this->_gt('Properties');
        $this->faqDialog->dialogDoubleButtons = FALSE;
        $this->layout->add($this->faqDialog->getDialogXML());
      }
    } else {
      if (isset($this->entries[$this->params['entry_id']]) &&
          is_array($this->entries[$this->params['entry_id']])) {
        $entryId = $this->params['entry_id'];
        $outputXML = sprintf(
          '<listview title="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Properties'))
        );
        $outputXML .= '<items>'.LF;
        $outputXML .= sprintf(
          '<listitem title="%s"><sutitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Title')),
          papaya_strings::escapeHTMLChars(
            $this->entries[$entryId]['entry_title']
          )
        );
        $outputXML .= sprintf(
          '<listitem title="%s"><sutitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Question')),
          papaya_strings::escapeHTMLChars(
            $this->entries[$entryId]['entry_question']
          )
        );
        $outputXML .= sprintf(
          '<listitem title="%s"><sutitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Answer')),
          papaya_strings::escapeHTMLChars(
            $this->entries[$entryId]['entry_answer']
          )
        );
        $outputXML .= '</items>'.LF;
        $outputXML .= '</listview>'.LF;
        $this->layout->add($ouputXML);
      }
    }
  }

  /**
  * Get XML comment form
  *
  * @access public
  */
  function getXMLCommentForm() {
    if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
      if (isset($this->comments[$this->params['note_id']]) &&
          is_array($this->comments[$this->params['note_id']])) {
        $this->initializeCommentEditForm();
        $this->faqDialog->dialogTitle = $this->_gt('Properties');
        $this->faqDialog->dialogDoubleButtons = FALSE;
        $this->layout->add($this->faqDialog->getDialogXML());
      }
    } else {
      if (isset($this->comments[$this->params['note_id']]) &&
          is_array($this->comments[$this->params['note_id']])) {
        $outputXML = sprintf(
          '<listview title="%s">'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Properties'))
        );
        $outputXML .= '<items>'.LF;
        $outputXML .= sprintf(
          '<listitem title="%s"><sutitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Username')),
          papaya_strings::escapeHTMLChars(
            $this->comments[$this->params['note_id']]['note_username']
          )
        );
        $outputXML .= sprintf(
          '<listitem title="%s"><sutitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Created')),
          date('Y-m-d H:i:s', $this->comments[$this->params['note_id']]['note_created'])
        );
        $outputXML .= sprintf(
          '<listitem title="%s"><sutitem>%s</subitem></listitem>'.LF,
          papaya_strings::escapeHTMLChars($this->_gt('Content')),
          papaya_strings::escapeHTMLChars(
            $this->comments[$this->params['note_id']]['note_content']
          )
        );
        $outputXML .= '</items>'.LF;
        $outputXML .= '</listview>'.LF;
        $this->layout->add($ouputXML);
      }
    }
  }

  /**
  * Get XML delete faq group form
  *
  * @access public
  */
  function getXMLDelFaqGroupForm() {
    if (isset($this->faqGroups[$this->params['faqgroup_id']]) &&
        is_array($this->faqGroups[$this->params['faqgroup_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_faqgroup',
        'faqgroup_id' => $this->params['faqgroup_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete FAQ group "%s" (%s)?'),
        $this->faqGroups[$this->params['faqgroup_id']]['faqgroup_title'],
        (int)$this->params['faqgroup_id']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get XML delete faq form
  *
  * @access public
  */
  function getXMLDelFaqForm() {
    if (isset($this->faqs[$this->params['faq_id']]) &&
        is_array($this->faqs[$this->params['faq_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_faq',
        'faq_id' => $this->params['faq_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete FAQ "%s" (%s)?'),
        $this->faqs[$this->params['faq_id']]['faq_title'],
        (int)$this->params['faq_id']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get XML faq list
  *
  * @access public
  * @return string
  */
  function getXMLFaqList() {
    if (isset($this->faqs) && is_array($this->faqs)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('FAQ'))
      );
      $result .= '<items>'.LF;
      foreach ($this->faqs as $id => $faq) {
        if (isset($faq) && is_array($faq)) {
          if (isset($this->params['faq_id']) && $this->params['faq_id'] == $id) {
            $selected = ' selected="selected"';
            $imageIdx = 'status-folder-open';
          } else {
            $selected = '';
            $imageIdx = 'items-folder';
          }
          $result .= sprintf(
            '<listitem href="%s" title="%s" image="%s" %s/>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('faq_id'=>(int)$id))
            ),
            papaya_strings::escapeHTMLChars($faq['faq_title']),
            papaya_strings::escapeHTMLChars($this->images[$imageIdx]),
            $selected
          );
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get XML tree list
  *
  * @access public
  */
  function getXMLTreeList() {
    if (isset($this->faqs) && is_array($this->faqs)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('FAQs'))
      );
      $result .= '<items>'.LF;
      foreach ($this->faqs as $id => $faq) {
        if (isset($faq) && is_array($faq)) {
          if ($this->faqs[$id]['groupcount'] > 0 ) {
            $selected = ($this->params['faq_id'] == $id)
              ? 'node="open" selected="selected"' : 'node="close"';
          } else {
            $selected = ($this->params['faq_id'] == $id)
              ? 'node="empty" selected="selected"' : 'node="empty"';
          }
          $result .= sprintf(
            '<listitem href="%s" title="%s" nhref="%s" span="3" %s />'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('faq_id' => (int)$id, 'cmd' => 'edit_faq'))
            ),
            papaya_strings::escapeHTMLChars(empty($faq['faq_title']) ? '' : $faq['faq_title']),
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('faq_id' => (int)$id))
            ),
            $selected
          );
          if (isset($this->faqGroups) &&
              is_array($this->faqGroups) &&
              $this->params['faq_id'] == $id) {
            $result .= $this->getXMLFaqGroupTreeList($this->params['faq_id']);
          }

        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get XML faq group tree list
  *
  * @param integer $i
  * @access public
  * @return string
  */
  function getXMLFaqGroupTreeList($i) {
    $result = '';
    if (isset($this->faqGroups) && is_array($this->faqGroups)) {
      $faqGroups = array();
      foreach ($this->faqGroups as $id => $faqGroup) {
        if (isset($faqGroup) && is_array($faqGroup)) {
          $faqGroups[$id] = $faqGroup;
        }
      }
      $max = count($faqGroups);
      $counter = 0;
      foreach ($faqGroups as $id => $faqGroup) {
        $selected = ($this->params['faqgroup_id'] == $id)
          ? 'selected="selected"' : '';
        if ($faqGroup['faq_id'] == $i) {
          $imageIndex = ($this->params['faqgroup_id'] == $id)
              ? 'status-folder-open' : 'items-folder';
          $result .= sprintf(
            '<listitem indent="1" href="%s" title="%s" image="%s" %s>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array('faqgroup_id' => (int)$id, 'cmd' => 'edit_faqgroup')
              )
            ),
            papaya_strings::escapeHTMLChars($faqGroup['faqgroup_title']),
            papaya_strings::escapeHTMLChars($this->images[$imageIndex]),
            $selected
          );
          if (++$counter > 1) {
            $result .= sprintf(
              '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'cmd' => 'faqgroup_up',
                    'faq_id' => (int)$faqGroup['faq_id'],
                    'faqgroup_id' => (int)$faqGroup['faqgroup_id']
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($this->images['actions-go-up']),
              papaya_strings::escapeHTMLChars($this->_gt('Move up'))
            );
          } else {
            $result .= '<subitem/>';
          }
          if ($counter < $max) {
            $result .= sprintf(
              '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'cmd' => 'faqgroup_down',
                    'faq_id' => (int)$faqGroup['faq_id'],
                    'faqgroup_id' => (int)$faqGroup['faqgroup_id']
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($this->images['actions-go-down']),
              papaya_strings::escapeHTMLChars($this->_gt('Move down'))
            );
          } else {
            $result .= '<subitem/>';
          }
          $result .= '</listitem>';
        }
      }
      return $result;
    }
  }

  /**
  * Get XML faq group list
  *
  * @access public
  */
  function getXMLFaqGroupList() {
    if (isset($this->faqGroups) && is_array($this->faqGroups)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('FAQ group'))
      );
      $result .= '<items>'.LF;
      foreach ($this->faqGroups as $id => $faqGroup) {
        if (isset($faqGroup) && is_array($faqGroup)) {
          if (isset($this->params['faqgroup_id']) && $this->params['faqgroup_id'] == $id) {
            $selected = ' selected="selected"';
            $imageIdx = 'status-folder-open';
          } else {
            $selected = '';
            $imageIdx = 'items-folder';
          }
          $result .= sprintf(
            '<listitem href="%s" title="%s" image="%s" %s/>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(array('faqgroup_id' => (int)$id))
            ),
            papaya_strings::escapeHTMLChars($faqGroup['faqgroup_title']),
            papaya_strings::escapeHTMLChars($this->images[$imageIdx]),
            $selected
          );
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get XML Buttons
  *
  * @access public
  */
  function getXMLButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;
    if ($this->module->hasPerm(2) || $this->module->hasPerm(3)) {
      if ($this->module->hasPerm(3)) {
        $toolbar->addButton(
          'Add FAQ',
          $this->getLink(array('cmd'=>'add_faq')),
          $this->localImages['faq-add'],
          '',
          FALSE
        );
      }
      if (isset($this->faqs[$this->params['faq_id']]) &&
          is_array($this->faqs[$this->params['faq_id']])) {
        if ($this->module->hasPerm(3)) {
          $toolbar->addButton(
            'Delete FAQ',
            $this->getLink(
              array(
                'cmd' => 'del_faq',
                'faq_id' => (int)$this->params['faq_id']
              )
            ),
            $this->localImages['faq-delete'],
            '',
            FALSE
          );
          $toolbar->addSeperator();
          $toolbar->addButton(
            'Add group',
            $this->getLink(
              array(
                'cmd' => 'add_faqgroup',
                'faq_id' => (int)$this->params['faq_id']
              )
            ),
            'actions-folder-add',
            '',
            FALSE
          );
        }
        if (isset($this->faqGroups[$this->params['faqgroup_id']]) &&
            is_array($this->faqGroups[$this->params['faqgroup_id']])) {
          if ($this->module->hasPerm(3)) {
            $toolbar->addButton(
              'Delete group',
              $this->getLink(
                array(
                  'cmd' => 'del_faqgroup',
                  'faqgroup_id' => (int)$this->params['faqgroup_id']
                )
              ),
              'actions-folder-delete',
              '',
              FALSE
            );
            $toolbar->addSeperator();
          }
          $toolbar->addButton(
            'Add entry',
            $this->getLink(
              array(
                'cmd' => 'add_entry',
                'faqgroup_id'=>(int)$this->params['faqgroup_id']
              )
            ),
            'actions-page-add',
            '',
            FALSE
          );
          if (isset($this->entries[$this->params['entry_id']]) &&
              is_array($this->entries[$this->params['entry_id']])) {
            $toolbar->addButton(
              'Delete entry',
              $this->getLink(
                array(
                  'cmd' => 'del_entry',
                  'entry_id' => (int)$this->params['entry_id']
                )
              ),
              'actions-page-delete',
              '',
              FALSE
            );
            $toolbar->addSeperator();
            $toolbar->addButton(
              'Add comment',
              $this->getLink(
                array(
                  'cmd' => 'add_comment',
                  'entry_id' => (int)$this->params['entry_id']
                )
              ),
              $this->localImages['message-add'],
              '',
              FALSE
            );
            if (isset($this->comments[$this->params['note_id']]) &&
                is_array($this->comments[$this->params['note_id']])) {
              $toolbar->addButton(
                'Delete comment',
                $this->getLink(
                  array(
                    'cmd' => 'del_comment',
                    'note_id' => (int)$this->params['note_id']
                  )
                ),
                $this->localImages['message-delete'],
                '',
                FALSE
              );
              $toolbar->addSeperator();
            }
          }
        }
      }
    }
    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $str));
    }
  }


  /**
  * Save faq changes
  *
  * @access public
  * @return boolean
  */
  function saveFaq() {
    $data = array(
      'faq_title' => $this->params['faq_title'],
      'faq_descr' => $this->params['faq_descr']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableFaqs, $data, 'faq_id', (int)$this->params['faq_id']
    );
  }

  /**
  * Initialize faq edit form
  *
  * @access public
  */
  function initializeFaqEditForm() {
    if (!is_object($this->faqDialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->faqs[$this->params['faq_id']];
      $hidden = array(
        'cmd' => 'edit_faq',
        'save' => 1,
        'faq_id' => $data['faq_id']);
      $fields = array(
        'faq_title' => array('Title', 'isNoHTML', TRUE, 'input', 200),
        'faq_descr' => array('Description', 'isSomeText', FALSE, 'textarea', 8)
      );
      $this->faqDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->faqDialog->msgs = &$this->msgs;
      $this->faqDialog->loadParams();
    }
  }

  /**
  * Initialize entry edit
  *
  * @access public
  */
  function initializeEntryEditForm() {
    if (!is_object($this->faqDialog)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->entries[$this->params['entry_id']];
      $hidden = array(
        'cmd' => 'edit_entry',
        'save' => 1,
        'entry_id' => $data['entry_id']);
      $fields = array(
        'entry_title' => array('Title', 'isNoHTML', TRUE, 'input', 200),
        'entry_question' => array('Question', 'isSomeText', TRUE, 'richtext', 8),
        'entry_answer' => array('Answer', 'isSomeText', TRUE, 'richtext', 8)
      );
      $this->faqDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->faqDialog->msgs = &$this->msgs;
      $this->faqDialog->loadParams();
    }
  }

  /**
  * Initialize
  *
  * @access public
  */
  function initializeCommentEditForm() {
    if (!(isset($this->faqDialog) && is_object($this->faqDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->comments[$this->params['note_id']];
      $hidden = array(
        'cmd' => 'edit_comment',
        'save' => 1,
        'note_id' => $data['note_id']
      );
      $fields = array(
        'note_username' => array(
          'Username', 'isNoHTML', TRUE, 'input', 50
        ),
        'note_content' => array(
          'Content', 'isNoHTML', TRUE, 'textarea', 8
        )
      );
      $this->faqDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $this->faqDialog->msgs = &$this->msgs;
      $this->faqDialog->loadParams();
    }
  }


  /**
  * Add faq
  *
  * @access public
  * @return mixed
  */
  function addFaq() {
    return $this->databaseInsertRecord(
      $this->tableFaqs, 'faq_id', array('faq_title' => $this->_gt('New FAQ'))
    );
  }

  /**
  * Add faq group
  *
  * @param integer $parent
  * @access public
  * @return mixed
  */
  function addFaqGroup($parent) {
    if ($this->faqExists($parent)) {
      $data = array(
        'faq_id' => $parent,
        'faqgroup_title' => $this->_gt('New FAQ group'),
        'faqgroup_position' => count($this->faqGroups) + 1
      );
      return $this->databaseInsertRecord($this->tableFaqgroups, 'faqgroup_id', $data);
    }
    return FALSE;
  }

  /**
  * Add entry
  *
  * @param integer $parent
  * @access public
  * @return mixed
  */
  function addEntry($parent) {
    if ($this->faqGroupExists($parent)) {
      $data = array(
        'faqgroup_id' => $parent,
        'entry_title' => $this->_gt('New entry'),
        'entry_position' => count($this->entries) + 1
      );
      return $this->databaseInsertRecord($this->tableEntries, 'entry_id', $data);
    }
    return FALSE;
  }


  /**
  * Add comment
  *
  * @param integer $parent
  * @access public
  * @return mixed
  */
  function addComment($parent) {
    if ($this->entryExists($parent)) {
      $data = array(
        'entry_id' => $parent,
        'note_content' => $this->_gt('New comment'),
        'note_username' => $this->authUser->user['username'],
        'note_created' => time()
      );
      return $this->databaseInsertRecord($this->tableComments, 'note_id', $data);
    }
    return FALSE;
  }

  /**
  * Delete faq
  *
  * @param integer $id
  * @access public
  * @return mixed returns db_deleteRecord
  */
  function deleteFaq($id) {
    $this->loadFaqGroups($id);
    if (isset($this->faqGroups) && is_array($this->faqGroups)) {
      foreach ($this->faqGroups as $faqGroupId=>$faqGroup) {
        $this->deleteFaqGroup($faqGroupId);
      }
    }
    unset($this->faqs);
    return $this->databaseDeleteRecord($this->tableFaqs, 'faq_id', $id);
  }

  /**
  * Delete faq group
  *
  * @param integer $i
  * @access public
  * @return boolean
  */
  function deleteFaqGroup($i) {
    $this->loadEntries($i);
    if (isset($this->entries) && is_array($this->entries)) {
      foreach ($this->entries as $enid => $entry) {
        $this->databaseDeleteRecord($this->tableComments, 'entry_id', $enid);
      }
    }
    if (FALSE !==
        $this->databaseDeleteRecord($this->tableEntries, 'faqgroup_id', $i)) {
      if (FALSE !==
          $this->databaseDeleteRecord($this->tableFaqgroups, 'faqgroup_id', $i)) {
        unset($this->comments);
        unset($this->entries);
        unset($this->faqGroups);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Delete entry
  *
  * @param integer $i
  * @access public
  * @return mixed return value of db_delete record
  */
  function deleteEntry($i) {
    $temp = $this->comments;
    $this->loadComments($i);
    if (isset($this->comments) && is_array($this->comments)) {
      foreach ($this->comments as $id => $comment) {
        if ($comment['entry_id'] = $i) {
          $this->databaseDeleteRecord($this->tableComments, 'entry_id', $i);
        }
      }
    }
    unset ($this->comments);
    unset ($this->entries);
    return $this->databaseDeleteRecord($this->tableEntries, 'entry_id', $i);
  }

  /**
  * Get XML entry list
  *
  * @access public
  */
  function getXMLEntryList() {
    if (isset($this->entries) && is_array($this->entries)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('FAQ entries'))
      );
      $result .= '<items>'.LF;
      $counter = 0;
      $max = count($this->entries);
      foreach ($this->entries as $id => $entry) {
        if (isset($entry) && is_array($entry)) {
          $selected = ($this->params['entry_id'] == $id)
            ? ' selected="selected"' : '';
          $result .= sprintf(
            '<listitem href="%s" title="%s" image="%s" %s>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getLink(
                array(
                  'entry_id' => (int)$id,
                  'cmd' => 'edit_entry'
                )
              )
            ),
            papaya_strings::escapeHTMLChars($entry['entry_title']),
            papaya_strings::escapeHTMLChars($this->images['items-page']),
            $selected
          );
          if (++$counter > 1) {
            $result .= sprintf(
              '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              $this->getLink(
                array(
                  'cmd' => 'entry_up',
                  'faq_id' => (int)$this->params['faq_id'],
                  'faqgroup_id' => (int)$entry['faqgroup_id'],
                  'entry_id' => (int)$entry['entry_id']
                )
              ),
              papaya_strings::escapeHTMLChars($this->images['actions-go-up']),
              papaya_strings::escapeHTMLChars($this->_gt('Move up'))
            );
          } else {
            $result .= '<subitem/>';
          }
          if ($counter < $max) {
            $result .= sprintf(
              '<subitem align="right"><a href="%s"><glyph src="%s" hint="%s"/></a></subitem>',
              papaya_strings::escapeHTMLChars(
                $this->getLink(
                  array(
                    'cmd' => 'entry_down',
                    'faq_id' => (int)$this->params['faq_id'],
                    'faqgroup_id' => (int)$entry['faqgroup_id'],
                    'entry_id' => (int)$entry['entry_id']
                  )
                )
              ),
              papaya_strings::escapeHTMLChars($this->images['actions-go-down']),
              papaya_strings::escapeHTMLChars($this->_gt('Move down'))
            );
          } else {
            $result .= '<subitem/>';
          }
          $result .= '</listitem>';
        }
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get XML delete entry form
  *
  * @access public
  */
  function getXMLDelEntryForm() {
    if (isset($this->entries[$this->params['entry_id']]) &&
        is_array($this->entries[$this->params['entry_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_entry',
        'entry_id' => $this->params['entry_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete entry "%s" (%s)?'),
        $this->entries[$this->params['entry_id']]['entry_title'],
        (int)$this->params['entry_id']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get XML delete comment
  *
  * @access public
  */
  function getXMLDelCommentForm() {
    if (isset($this->comments[$this->params['note_id']]) &&
        is_array($this->comments[$this->params['note_id']])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_comment',
        'note_id' => $this->params['note_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete comment "%s" (%s)?'),
        $this->comments[$this->params['note_id']]['note_username'],
        (int)$this->params['note_id']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'note_content');
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get XML comment list
  *
  * @access public
  */
  function getXMLCommentList() {
    if (isset($this->comments) && is_array($this->comments)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Comments'))
      );
      $result .= '<cols>'.LF;
      $result .= sprintf(
        '<col>%s</col>',
        papaya_strings::escapeHTMLChars($this->_gt('Username'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Content'))
      );
      $result .= sprintf(
        '<col align="center">%s</col>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Created'))
      );
      $result .= '</cols>'.LF;
      $result .= '<items>'.LF;
      foreach ($this->comments as $id => $comment) {
        if (isset($this->params['note_id']) && $comment['note_id'] == $this->params['note_id']) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<listitem href="%s" title="%s" image="%s" %s>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getLink(array('note_id'=>(int)$id))
          ),
          papaya_strings::escapeHTMLChars($comment['note_username']),
          papaya_strings::escapeHTMLChars($this->images['items-message']),
          $selected
        );
        $result .= sprintf(
          '<subitem>%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars(
            papaya_strings::truncate(strip_tags($comment['note_content']), 200)
          )
        );
        $result .= sprintf(
          '<subitem align="center">%s</subitem>'.LF,
          papaya_strings::escapeHTMLChars(date('Y-m-d H:i:s', $comment['note_created']))
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->add($result);
    }
  }

  /**
  * Delete comment
  *
  * @param integer $id
  * @access public
  * @return mixed return value of db_deleteRecord
  */
  function deleteComment($id) {
    unset ($this->comments);
    return $this->databaseDeleteRecord($this->tableComments, 'note_id', $id);
  }

  /**
  * Save FAQ group
  *
  * @access public
  * @return boolean
  */
  function saveFaqGroup() {
    $data = array(
      'faqgroup_title' => $this->params['faqgroup_title'],
      'faqgroup_descr' => $this->params['faqgroup_descr']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableFaqgroups, $data, 'faqgroup_id', (int)$this->params['faqgroup_id']
    );
  }

  /**
  * Save entry
  *
  * @access public
  * @return boolean
  */
  function saveEntry() {
    $data = array(
      'entry_title' => $this->params['entry_title'],
      'entry_question' => $this->params['entry_question'],
      'entry_answer' => $this->params['entry_answer']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableEntries, $data, 'entry_id', (int)$this->params['entry_id']
    );
  }

  /**
  * SaveComment
  *
  * @access public
  * @return boolean
  */
  function saveComment() {
    $data = array(
      'note_username' => $this->params['note_username'],
      'note_content' => $this->params['note_content']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableComments, $data, 'note_id', (int)$this->params['note_id']
    );
  }
}
?>
