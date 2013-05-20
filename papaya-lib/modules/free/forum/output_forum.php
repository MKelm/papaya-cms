<?php
/**
* Forum class to handle front-end user interaction and generate xml outputs.
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
* @version $Id: output_forum.php 38450 2013-04-29 09:43:09Z weinert $
*/

/**
* Base methods to load data and perform some other tasks.
*/
require_once(dirname(__FILE__).'/base_forum.php');

/**
* Forum class to handle front-end user interaction and generate xml outputs.
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/
class output_forum extends base_forum {

  /**
  * Allow session caching for search results.
  * @var boolean TRUE to cache search results, otherwise FALSE
  */
  public $cacheSearchResults = TRUE;

  /**
  * Configuration data of the related content module.
  * @var array
  */
  protected $_moduleConfiguration = array();

  /**
  * Contains the status of the current page to set a code number on missing content.
  * @var integer
  */
  protected $_contentStatus = 1;

  /**
  * Output status, forum root active or not.
  * @var boolean
  */
  protected $_isForumRoot = FALSE;

  /**
  * Contains data of the current forum from decodeDataForum, eg. to load forum data.
  * @var array
  */
  protected $_decodedForumData = array();

  /**
  * This list contains internal mode states to perform output methods correctly.
  * @var array
  */
  protected $_internalMode = array();

  /**
  * Contains parameters with default values.
  * @var array
  */
  protected $_defaultParameters = array();

  /**
  * Overload this to change the target pageid for links to another page.
  * Otherwise all links refer to the current page.
  * Also the page id value is going to be used for new forums in comments mode.
  * @var integer
  */
  protected $_pageId = NULL;

  /**
  * Overload this to change the page title,
  * which is going to be used for new forums in comments mode.
  * @var string
  */
  protected $_pageTitle = NULL;

  /***************************************************************************/
  /** Helper methods                                                         */
  /***************************************************************************/

  /**
  * Get parameters with fixed positions.
  * Use $fixedParamNames to define parameter positions by name.
  *
  * @param array $params reference to support $unsetParams
  * @param array $fixedParamNames
  * @param boolean $unsetParams eg. to add further parameters from $this->params dynamically
  */
  protected function _getFixedParameters(&$params, $fixedParamNames, $unsetParams = FALSE) {
    $result = array();
    if (!empty($fixedParamNames)) {
      foreach ($fixedParamNames as $fixedParamName) {
        if (!empty($params[$fixedParamName])) {
          $result[$fixedParamName] = $params[$fixedParamName];
          if ($unsetParams === TRUE) {
            unset($params[$fixedParamName]);
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get a link with a set of params.
  * Use $fixedParamNames to define a fixed position of a parameter by name.
  *
  * @param array $params
  * @param string $anchorName without #-character
  * @param boolean $absoluteUrl
  * @return string $link
  */
  protected function _getLinkWithParams(
                       $params = array(), $anchorName = NULL, $absoluteUrl = FALSE
                     ) {
    $finalParams = $this->_getFixedParameters(
      $params,
      array('categ_id', 'forum_id', 'thread_id', 'entry_id', 'offset', 'cmd'),
      TRUE
    );
    if (!empty($params)) {
      foreach ($params as $paramName => $paramValue) {
        $finalParams[$paramName] = $paramValue;
      }
    }

    $link = $this->getWebLink(
      $this->_pageId, NULL, NULL, $finalParams, $this->paramName, $this->_pageTitle
    );
    if (!empty($anchorName)) {
      $link .= '#'.$anchorName;
    }
    if ($absoluteUrl === TRUE) {
      return $this->getAbsoluteURL($link);
    }
    return $link;
  }

  /**
  * Set page data by box modules.
  *
  * @param integer $pageId
  * @param string $pageTitle
  */
  public function setPageData($pageId, $pageTitle) {
    $this->_pageId = (int)$pageId;
    $this->_pageTitle = $pageTitle;
  }

  /**
  * Get forum combo to enable selection of either categories or
  * forums in categories within the backend where this page
  * module is configured.
  *
  * @param string $name
  * @param array $field
  * @param string $data
  * @return string
  */
  public function getForumCombo($name, $field, $data) {
    include_once(dirname(__FILE__).'/admin_forum.php');
    $forumAdminObject = new admin_forum();
    return $forumAdminObject->getForumCombo(
      $this->paramName, $name, $this->decodeForumData($data)
    );
  }

  /**
  * Get surfer group combo.
  * Callback function; creates a select field to choose the group
  * the newly registered surfers will join.
  *
  * @param string $name
  * @param string $field
  * @param array $data
  * @return string $result
  */
  public function getSurferGroupCombo($name, $field, $data) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $surfersObj = base_pluginloader::getPluginInstance('06648c9c955e1a0e06a7bd381748c4e4', $this);
    $groups = $surfersObj->loadGroups();
    $result = '';
    if (!empty($groups)) {
      $result = sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        $this->paramName,
        papaya_strings::escapeHtmlChars($name)
      );
      $selected = !empty($data) ? NULL : ' selected="selected"';
      $result .= sprintf('<option value="0"%s>[%s]</option>', $selected, $this->_gt('none'));
      foreach ($groups as $groupId => $group) {
        $selected = ($groupId == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%d"%s>%s</option>',
          $groupId,
          $selected,
          papaya_strings::escapeHtmlChars($group['surfergroup_title'])
        );
      }
      $result .= '</select>'.LF;
    }
    return $result;
  }

  /**
  * Callback function for the captcha module.
  * Returns a list of dynamic images using the captcha-module.
  *
  * @param string $name string Name of the field.
  * @param array $field edit field parameters
  * @param string $data current value
  * @return string $result
  */
  public function getCaptchasCombo($name, $field, $data) {
    $result = '';
    include_once(PAPAYA_INCLUDE_PATH.'system/base_imagegenerator.php');
    $imageGenerator = new base_imagegenerator;
    $captchas = $imageGenerator->getIdentifiersByGUID(
      array('103fecb7cc96c1a66633c7f464b15956', 'fe3dd6359939c142781f70ae4b29c70c')
    );
    if (!empty($captchas) && is_array($captchas)) {
      $result .= sprintf(
        '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
        papaya_strings::escapeHTMLChars($this->paramName),
        papaya_strings::escapeHTMLChars($name)
      );
      $selected = !empty($data) ? '' : ' selected="selected"';
      $result .= sprintf('<option value="0"%s>[%s]</option>', $selected, $this->_gt('none'));
      foreach ($captchas as $captcha) {
        $selected = (!empty($data) && $data == $captcha['image_ident']) ?
          ' selected="selected"' : '';
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

  /**
  * Decode forum data by a given string.
  *
  * @param string $forumDataString
  * @return array
  */
  public function decodeForumData($forumDataString) {
    $currentForumData = explode(';', $forumDataString);
    if (count($currentForumData) > 1) {
      $currentForumData = array(
        'mode' => (trim($currentForumData[0]) == 'forum') ? 'forum' : 'categ',
        'id' => trim($currentForumData[1])
      );
    } else {
      $currentForumData = array(
        'mode' => 'categ',
        'id' => 0
      );
    }
    return $currentForumData;
  }

  /**
  * Add one or more mode states to the internal mode list to handle different internal modes.
  *
  * @param string|array $name
  */
  protected function _addInternalModeState($stateName) {
    if (!is_array($stateName)) {
      $stateName = array($stateName);
    }
    foreach ($stateName as $name) {
      if (!array_key_exists($name, $this->_internalMode)) {
        $this->_internalMode[$name] = TRUE;
      }
    }
  }

  /**
  * Determine if one or more mode states have been set in the internal mode list for conditions.
  *
  * @param string $stateName
  * @return boolean $result status
  */
  protected function _isInternalMode($stateName) {
    if (!is_array($stateName)) {
      $stateName = array($stateName);
    }
    $result = TRUE;
    foreach ($stateName as $name) {
      $result = $result && array_key_exists($name, $this->_internalMode);
    }
    return $result;
  }

  /**
  * Determine content status by expected params and given data.
  *
  * $singleConfiguration = array(
  *   'parameter/data_key', $dataToCheck[, 'data_key_to_check'][, (bool)$matchParameterValue]
  * );
  *   0                1              2                       3
  * or
  * $multipleConfiguration = array($singleConfiguration, $singleConfiguration);
  *
  * Use $this->owner->papaya()->getObject('Response')->setStatus($code) to change a status.
  *
  * @param array $configuration page params / data configuration
  * @return integer content status
  */
  protected function _determineContentStatus($configuration) {
    $this->_contentStatus = 0;
    $statusConditionActive = FALSE;
    if (is_array($configuration) && count($configuration) > 0) {
      if (is_string($configuration[0])) {
        $configuration = array($configuration);
      }
      foreach ($configuration as $config) {
        if (!empty($this->params[$config[0]])) {
          $pageInputValue = $this->params[$config[0]];
        } elseif (!empty($this->_moduleConfiguration[$config[0]])) {
          $pageInputValue = $this->_moduleConfiguration[$config[0]];
        }
        if (isset($pageInputValue)) {
          $statusConditionActive = TRUE;
          if (!empty($config[1])) {
            if ((empty($config[2]) && empty($config[3])) ||
                (empty($config[2]) && $pageInputValue == $config[1]) ||
                (!empty($config[2]) && empty($config[3]) && !empty($config[1][$config[2]])) ||
                (
                 !empty($config[2]) && isset($config[1][$config[2]]) &&
                 $pageInputValue == $config[1][$config[2]])) {
              $this->_contentStatus = 1;
            }
          }
        }
      }
    }
    if ($statusConditionActive === FALSE) {
      $this->_contentStatus = 1;
    }
    //$this->debug($this->_contentStatus);
    return $this->_contentStatus;
  }

  /**
  * Get a content status xml to show a related error message in output.
  *
  * @return string
  */
  public function getContentStatusXml() {
    return sprintf('<content-status>%d</content-status>'.LF, $this->_contentStatus);
  }

  /***************************************************************************/
  /** Initialization                                                         */
  /***************************************************************************/

  /**
  * Initialize basic parameters / configuration and load related data.
  *
  * @param object $module
  * @param array $moduleConfiguration
  */
  public function initialize($module, $moduleConfiguration) {
    $this->initializeParams();
    $this->getCurrentSurfer();

    $this->module = $module;
    $this->_moduleConfiguration = $moduleConfiguration;
    if (isset($moduleConfiguration['forum'])) {
      $this->_decodedForumData = $this->decodeForumData($moduleConfiguration['forum']);
    }

    // Determine whether we are showing the forum root or not
    if (empty($this->_baseParameters['offset'])) {
      if (empty($this->params['categ_id']) && empty($this->params['forum_id']) &&
          empty($this->params['thread_id'])) {
        $this->_isForumRoot = TRUE;
      } elseif (!empty($this->_decodedForumData['mode']) &&
                $this->_decodedForumData['mode'] == 'categ' &&
                !empty($this->params['categ_id']) && !empty($this->_decodedForumData['id']) &&
                $this->params['categ_id'] == $this->_decodedForumData['id'] &&
                empty($this->params['forum_id']) && empty($this->params['thread_id'])) {
        $this->_isForumRoot = TRUE;
      } elseif (!empty($this->_decodedForumData['mode']) &&
                $this->_decodedForumData['mode'] == 'forum' &&
                !empty($this->params['forum_id']) && !empty($this->_decodedForumData['id']) &&
                $this->params['forum_id'] == $this->_decodedForumData['id'] &&
                empty($this->params['thread_id'])) {
        $this->_isForumRoot = TRUE;
      }
    }

    // set current selected categ id as fixed parameter for further calls
    if ($this->_isForumRoot && !empty($this->_decodedForumData['mode']) &&
        $this->_decodedForumData['mode'] == 'categ' && !empty($this->_decodedForumData['id'])) {
      $this->params['categ_id'] = !empty($this->params['categ_id']) ?
        (int)$this->params['categ_id'] : (int)$this->_decodedForumData['id'];
    } else {
      $this->params['categ_id'] = !empty($this->params['categ_id']) ?
        (int)$this->params['categ_id'] : NULL;
    }
    // set current selected forum id as fixed parameter for further calls
    if (!empty($this->_decodedForumData['mode']) &&
        $this->_decodedForumData['mode'] != 'categ' && !empty($this->_decodedForumData['id'])) {
      $this->params['forum_id'] = !empty($this->params['forum_id']) ?
        (int)$this->params['forum_id'] : (int)$this->_decodedForumData['id'];
    } else {
      $this->params['forum_id'] = !empty($this->params['forum_id']) ?
        (int)$this->params['forum_id'] : NULL;
    }
    // set default offset for further calls
    if (!isset($this->params['offset'])) {
      $this->params['offset'] = 0;
    }

    // Load data and determine statusCodeConfiguration depending on the current view
    $statusCodeConfiguration = array();
    if (isset($this->params['cmd']) && $this->params['cmd'] == 'search' &&
        isset($this->params['search']) && $this->params['search'] == 1 &&
        isset($this->params['searchfor'])) {

      // search view
      $this->search(
        $this->params['categ_id'],
        $this->params['forum_id'],
        $this->params['searchfor'],
        $this->params['offset'],
        $this->_moduleConfiguration['perpage']
      );
      $this->_addInternalModeState('search');

      // $statusCodeConfiguration = array(''); ???

    } else {

      // determine view (forum / category)
      if (empty($this->params['forum_id']) && isset ($this->_decodedForumData['mode'])) {
        switch ($this->_decodedForumData['mode']) {
        case 'categ':
          $this->_addInternalModeState('overview');
          break;
        case 'forum':
          $this->_addInternalModeState('forum_view');
          break;
        }
      } else {
        $this->_addInternalModeState('forum_view');
      }
      if ($this->_isInternalMode('forum_view')) {
        $this->loadBoard($this->params['forum_id']);
      }

      // category, thread or entry view
      $purpose = isset($this->_moduleConfiguration['purpose']) ?
        $this->_moduleConfiguration['purpose'] : NULL;
      switch ($purpose) {
      case self::SHOW_COMMENTS: // comments
        // comments module purpose

        // Work as a comment module for pages.
        // When we have set us to a category, this category is where
        // a forum for each page is created containing comments as thread entries.
        // In forum mode, the category is the same as those of the provided
        // forum. The forum itself will not be used to store comments to, it
        // is just a reference to the category.
        $this->_addInternalModeState('comments');
        if ($this->_isInternalMode('overview')) {
          $categoryId = $this->_decodedForumData['id'];
        } else {
          if (isset($this->_decodedForumData['id'])) {
            $this->loadBoard($this->_decodedForumData['id']);
            $categoryId = $this->board['forumcat_id'];
          } else {
            $categoryId = "";
          }
        }
        $this->params['forum_id'] = $this->getForumByPageId($categoryId, $this->_pageId);
        if (empty($this->params['forum_id'])) {
          $this->params['forum_id'] = $this->addForum(
            $categoryId, $this->_pageTitle, '', $this->_pageId
          );
        }
        break;
      case self::SHOW_LATEST: // last entries
        $this->_addInternalModeState('last_entries');
        if (isset($this->_decodedForumData['mode'])) {
          switch ($this->_decodedForumData['mode']) {
          case 'categ':
            $this->params['forum_id'] = NULL;
            $this->params['categ_id'] = $this->_decodedForumData['id'];
            break;
          case 'forum':
            $this->params['forum_id'] = $this->_decodedForumData['id'];
            $this->params['categ_id'] = NULL;
            break;
          }
        }
        $this->params['thread_id'] = NULL;
        $this->params['entry_id'] = NULL;
        break;
      case self::SHOW_FORUM: // forum
      default:
        $this->_addInternalModeState('content');

        if ($this->_isInternalMode(array('overview'))) {
          if (isset($this->params['categ_id'])) {
            $this->loadCategs($this->params['categ_id']);
            $this->loadForumsInCategory($this->params['categ_id']);
          } else {
            $this->loadForumsInCategory($this->params['forum_id']);
            $this->loadCategs($this->params['forum_id']);
          }
        }

        if ($this->_isInternalMode('forum_view')) {
          $mode = isset($this->_moduleConfiguration['mode']) ?
            $this->_moduleConfiguration['mode'] : NULL;
          switch ($mode) {
          case self::MODE_THREADED: // Threaded
            $this->_addInternalModeState('threaded');
          case self::MODE_THREADED_BBS: // Threaded BBS
            $this->_addInternalModeState(array('threaded', 'bbs'));

            // load entry data on request
            $entryLoaded = FALSE;
            if (!empty($this->params['thread_id'])) {
              $entryLoaded = $this->loadEntry($this->params['thread_id']);
            }
            if (isset($this->params['entry_id'])) {
              // @todo check if needed in bbs thread / entries view
              $entryLoaded = $this->loadEntry($this->params['entry_id']);
            }
            if ($entryLoaded) {
              $this->substituteCommunityUsers();
            }

            if (isset($this->params['thread_id'])) {
              $this->loadThread($this->params['thread_id'], 'path');
              $this->_addInternalModeState('entries');
            } elseif (isset($this->params['forum_id'])) {
              $this->loadTopics(
                $this->params['forum_id'],
                $this->params['offset'],
                $this->_moduleConfiguration['perpage'],
                $this->_moduleConfiguration['sort']
              );
              $this->_addInternalModeState('topics');
            }
            break;
          default:
          case self::MODE_BBS: // BBS
            $this->_addInternalModeState('bbs');
            if (!empty($this->params['thread_id'])) {
              $this->loadThread($this->params['thread_id'], 'created');
              if (!empty($this->entries) && is_array($this->entries)) {
                $this->entry = reset($this->entries);
                if (!empty($this->params['entry_id']) &&
                    isset($this->entries[$this->params['entry_id']])) {
                  // @todo add a offset calculation method depending on entry_id and entries before
                  $this->entry = $this->entries[$this->params['entry_id']];
                }
              }
              $this->_addInternalModeState('entries');
              if (!empty($this->params['entry_id'])) {
                $statusCodeConfiguration = array('entry_id', $this->entry, 'entry_id', TRUE);
              } else {
                $statusCodeConfiguration = array('thread_id', $this->entries);
              }
            } else {
              $this->loadTopics(
                $this->params['forum_id'],
                $this->params['offset'],
                $this->_moduleConfiguration['perpage'],
                $this->_moduleConfiguration['sort']
              );
              $this->_addInternalModeState('topics');
              if (isset($this->params['forum_id']) || isset($this->params['categ_id'])) {
                $statusCodeConfiguration =
                  array(
                    array('forum_id', $this->topics, $this->params['forum_id'], TRUE),
                    array('categ_id', $this->topics, $this->params['forum_id'], TRUE)
                  );
              } else {
                $statusCodeConfiguration = array('forum', $this->topics);
              }
            }
            break;
          }
        }
        break;
      }
    }

    // init paging, @todo improve!
    $this->spliceMax = empty($this->_moduleConfiguration['perpage']) ?
      5 : (int)$this->_moduleConfiguration['perpage'];
    $this->spliceOfs = $this->params['offset'];

    if (!empty($statusCodeConfiguration)) {
      $this->_determineContentStatus($statusCodeConfiguration);
    }
    /* $this
      ->papaya()
      ->getObject('Response')
      ->setStatus(
        $this->_contentStatus > 0 ? 200 : 400
      ); @todo add option in page modules */
  }

  /***************************************************************************/
  /** Execution methods                                                      */
  /***************************************************************************/

  /**
  * Executes commands in forum view.
  *
  * @return string $result xml with messages and other response data.
  */
  protected function _executeForumCommands() {
    $result = '';

    if (isset($this->params['cmd'])) {

      switch ($this->params['cmd']) {
      case 'subscribe':
        $this->setSurferThreadSubscription($this->surferHandle, $this->getThreadId($this->entry));
        break;
      case 'unsubscribe':
        $this->clearSurferThreadSubscription($this->surferHandle, $this->getThreadId($this->entry));
        break;
      case 'cite_entry':
      case 'edit_entry':
        if (isset($this->entry)) {
          $this->_initializeOutputForm();
          $this->outputDialog->data['entry_subject'] = $this->entry['entry_subject'];

          if ($this->params['cmd'] == 'cite_entry') {
            $this->params['cmd'] = 'add_entry';

            if ($this->_moduleConfiguration['richtext_enabled']) {
              $this->outputDialog->data['entry_text'] = sprintf(
                '<blockquote><div>%s</div></blockquote><br />',
                $this->getXHTMLString($this->entry['entry_text'])
              );
            } else {
              $this->outputDialog->data['entry_text'] = sprintf(
                '"%s"', $this->getXHTMLString($this->entry['entry_text'])
              );
            }

          } else {
            $this->outputDialog->data['entry_text'] = $this->entry['entry_text'];
          }
        }
      case 'add_entry':
        if (!empty($this->_moduleConfiguration['post_questions']) ||
            !empty($this->_moduleConfiguration['post_answers'])) {

          $this->_initializeOutputForm();
          if (!isset($this->params['reset']) && !empty($this->params['save'])) {
            if ($this->outputDialog->checkDialogInput()) {
              if ($this->checkSpam()) {
                if ($this->params['cmd'] == 'add_entry' ||
                    $this->params['cmd'] == 'cite_entry') {

                  if ($this->surferHandle) {
                    $this->surferName = $this->surferHandle;
                    $this->surferEmail = $this->surferEmail;
                  }

                  $newId = $this->addEntry(
                    array(
                      'forum_id' => $this->params['forum_id'],
                      'entry_pid' => isset($this->entry) ? $this->entry['entry_id'] : 0,
                      'entry_subject' => $this->outputDialog->data['entry_subject'],
                      'entry_text' => $this->outputDialog->data['entry_text']
                    ),
                    $this->_moduleConfiguration['richtext_enabled']
                  );
                  if ($newId) {
                    $this->params['cmd'] = '';
                    $this->params['entry_id'] = $newId;

                    $this->notifySubscribedSurfers($newId);
                    $this->notifyAdministrator($newId);

                    // If a topic was created, go to the first
                    // page in topic view.
                    if (empty($this->params['thread_id'])) {
                      $linkParams = array(
                        'forum_id' => (int)$this->board['forum_id'],
                        'offset' => 0
                      );
                    } else { // Otherwise 'location' depends on forum mode.
                      switch ($this->_moduleConfiguration['mode']) {
                      case self::MODE_THREADED:
                        $linkParams = array(
                          'forum_id' => (int)$this->board['forum_id'],
                          'thread_id' => $this->params['thread_id'],
                          'entry_id' => $newId,
                          'offset' => (int)(
                            $this->entryOffsetInTree($newId) /
                            $this->_moduleConfiguration['perpage']
                          ) * (int)$this->_moduleConfiguration['perpage']
                        );
                        break;
                      case self::MODE_BBS:
                        // In BBS mode, the user is forwarded to the last page of
                        // this thread, where the answer he gave should be.
                        $linkParams = array(
                          'forum_id' => (int)$this->board['forum_id'],
                          'offset' => (int)(
                            $this->entryCount /
                            $this->_moduleConfiguration['perpage']
                          ) * (int)$this->_moduleConfiguration['perpage'],
                          'thread_id' => $this->params['thread_id'],
                          'entry_id' => $newId
                        );
                        break;
                      case self::MODE_THREADED_BBS:
                        // In threaded BBS mode the user is forwarded, too.
                        $linkParams = array(
                          'forum_id' => (int)$this->board['forum_id'],
                          'offset' => (int)(
                            $this->entryOffsetInTree($newId) /
                            $this->_moduleConfiguration['perpage']
                          ) * (int)$this->_moduleConfiguration['perpage'],
                          'thread_id' => $this->params['thread_id']
                        );
                        break;
                      }
                      $linkParams['submitted'] = $newId;
                    }
                    $this->module->deleteCache();
                    header('Location: '.$this->_getLinkWithParams($linkParams, NULL, TRUE));
                    exit;

                  } else {
                    if ($this->rejectedAttackStrings &&
                        isset($this->_moduleConfiguration['error_rejected'])) {
                      $result .= $this->_getErrorMessageXml(
                        $this->_moduleConfiguration['error_rejected']
                      );

                    } elseif ($this->entryTooLong &&
                              isset($this->_moduleConfiguration['error_too_long'])) {
                      $result .= $this->_getErrorMessageXml(
                        $this->_moduleConfiguration['error_too_long']
                      );

                    } else {
                      $result .= $this->_getErrorMessageXml(
                        $this->_moduleConfiguration['error_database']
                      );
                    }
                    if (!empty($this->_moduleConfiguration['text_comment'])) {
                      $result .= sprintf(
                        '<intro>%s</intro>'.LF,
                        $this->getXHTMLString($this->_moduleConfiguration['text_comment'])
                      );
                    }
                    $result .= $this->_getOutputForm(
                      $this->_moduleConfiguration['caption_newquestion']
                    );
                  }
                }

                if ($this->params['cmd'] == 'edit_entry') {
                  $saved = $this->saveEntry(
                    $this->entry['entry_id'],
                    array(
                      'entry_subject' => $this->outputDialog->data['entry_subject'],
                      'entry_text' => $this->outputDialog->data['entry_text']
                    )
                  );
                  if ($saved) {
                    $this->params['cmd'] = '';
                    $this->params['entry_id'] = $this->entry['entry_id'];

                    $this->notifySubscribedSurfers($this->entry['entry_id']);
                    $this->notifyAdministrator($this->entry['entry_id']);

                    $linkParams = array(
                      'forum_id' => (int)$this->board['forum_id'],
                      'offset' => $this->params['offset'],
                      'thread_id' => $this->params['thread_id'],
                      'entry_id' => $this->entry['entry_id']
                    );
                    $this->module->deleteCache();
                    header('Location: '.$this->_getLinkWithParams($linkParams, NULL, TRUE));
                    exit;

                  } else {
                    $result .= $this->_getErrorMessageXml(
                      $this->_moduleConfiguration['error_database']
                    );
                  }
                }
              } else {
                $result .= $this->_getErrorMessageXml(
                  $this->_moduleConfiguration['error_spam'], 'spam'
                );
                $result .= $this->_getOutputForm(
                  $this->_moduleConfiguration['caption_newquestion']
                );
              }
            } else {
              if (!empty($this->_moduleConfiguration['text_comment'])) {
                $result .= sprintf(
                  '<intro>%s</intro>'.LF,
                  $this->getXHTMLString($this->_moduleConfiguration['text_comment'])
                );
              }
              if (isset($this->outputDialog->inputErrors)) {
                if (!empty($this->outputDialog->inputErrors['entry_subject']) &&
                    !empty($this->_moduleConfiguration['error_subject'])) {
                  $result .= $this->_getErrorMessageXml(
                    $this->_moduleConfiguration['error_subject'], 'entry_subject'
                  );
                }
                if (!empty($this->outputDialog->inputErrors['entry_text']) &&
                    !empty($this->_moduleConfiguration['error_body'])) {
                  $result .= $this->_getErrorMessageXml(
                    $this->_moduleConfiguration['error_body'], 'entry_text'
                  );
                }
                if (!empty($this->outputDialog->inputErrors['entry_username']) &&
                    !empty($this->_moduleConfiguration['error_username'])) {
                  $result .= $this->_getErrorMessageXml(
                    $this->_moduleConfiguration['error_username'], 'entry_username'
                  );
                }
                if (!empty($this->outputDialog->inputErrors['entry_useremail']) &&
                    isset($this->_moduleConfiguration['error_useremail'])) {
                  $result .= $this->_getErrorMessageXml(
                    $this->_moduleConfiguration['error_useremail'], 'entry_useremail'
                  );
                }
                if (!empty($this->outputDialog->inputErrors['entry_captcha']) &&
                    isset($this->_moduleConfiguration['error_captcha'])) {
                  $result .= $this->_getErrorMessageXml(
                    $this->_moduleConfiguration['error_captcha'], 'entry_captcha'
                  );
                }
              }
              $result .= $this->_getOutputForm($this->_moduleConfiguration['caption_newquestion']);
            }
          } elseif (!isset($this->params['reset'])) {

            if (!empty($this->_moduleConfiguration['text_comment'])) {
              $result .= sprintf(
                '<intro>%s</intro>'.LF,
                $this->getXHTMLString($this->_moduleConfiguration['text_comment'])
              );
            }
            $result .= $this->_getOutputForm($this->_moduleConfiguration['caption_newquestion']);
          }
        } else {
          $result .= $this->_getErrorMessageXml($this->_moduleConfiguration['error_permission']);
        }
        break;
      default:
        if (empty($this->topics)) {
          if (!empty($this->_moduleConfiguration['post_questions']) ||
              !empty($this->_moduleConfiguration['post_answers'])) {
            $result .= $this->_getOutputForm($this->_moduleConfiguration['caption_newquestion']);
          }
        }
      }
    }

    return $result;
  }

  /***************************************************************************/
  /** Outputs                                                                */
  /***************************************************************************/

  /**
  * Get output for different outputs depending on internal mode by initialization.
  *
  * @return string
  */
  public function getOutput() {
    $result = '';
    if (!empty($this->_internalMode)) {

      if ($this->_isInternalMode('search')) {
        $result .= $this->_getOutputSearch();

      } elseif ($this->_isInternalMode('comments')) {
        $result = $this->_getOutputComments();

      } elseif ($this->_isInternalMode(array('content', 'forum_view'))) {
        $result .= $this->_getOutputForum($this->params['forum_id'], FALSE, $this->_isForumRoot);

      } elseif ($this->_isInternalMode(array('content', 'overview'))) {
        $result .= $this->_getOutputOverview($this->_isForumRoot);
      }

    }
    return $result;
  }

  /**
  * Get output for search (view).
  *
  * @return string xml
  */
  protected function _getOutputSearch() {
    $innerXml = $this->_getSearchResultsXml();
    $innerXml .= $this->_getSearchFormXml($this->_moduleConfiguration['caption_search']);

    if (!$this->_isForumRoot) {

      $linkParameters = array();
      if (!empty($this->params['categ_id'])) {
        $linkParameters['categ_id'] = $this->params['categ_id'];
      }
      if (!empty($this->params['forum_id'])) {
        $linkParameters['forum_id'] = $this->params['forum_id'];
      }
      $innerXml .= $this->_getLinksXml(
        array(array('back', $this->_moduleConfiguration['caption_back'], $linkParameters))
      );
    }

    return $this->_getForumXml($innerXml, TRUE);
  }

  /**
  * Get output for category / forums view.
  *
  * @param boolean $root optional, default FALSE
  * @return string
  */
  protected function _getOutputOverview() {

    $categoriesXml = $this->_getCaptionsXml(
      array(
        'caption_categories' => $this->_moduleConfiguration['caption_categories'],
        'caption_lastentry' => $this->_moduleConfiguration['caption_lastentry'],
        'caption_forums' => $this->_moduleConfiguration['caption_forums'],
        'caption_from' => $this->_moduleConfiguration['caption_from'],
        'caption_threads' => $this->_moduleConfiguration['caption_threads'],
        'caption_subject' => $this->_moduleConfiguration['caption_subject'],
        'caption_entries' => $this->_moduleConfiguration['caption_entries'],
        'caption_registered' => $this->_moduleConfiguration['caption_registered'],
        'caption_category' => $this->_moduleConfiguration['caption_category'],
        'caption_description' => $this->_moduleConfiguration['caption_description']
      )
    );
    if (!empty($this->_moduleConfiguration['search_enabled'])) {
      $categoriesXml .= $this->_getSearchFormXml($this->_moduleConfiguration['caption_search']);
    }

    // When the category has subcategories
    // We need a list of those sub categories
    // Otherwise we need the category itself
    // plus the forums in there.
    // If there are forums selected, the most
    // recent entry of each will be loaded as well.
    if (!empty($this->category) && is_array($this->category)) {
      $categoriesXml .= $this->_getCategoryXml();

      $links = array();
      if ($this->category['forumcat_prev'] > 0) {
        $links[] = array(
          'back',
          $this->_moduleConfiguration['caption_back'],
          array('categ_id' => $this->category['forumcat_prev'])
        );

      } elseif (!$this->_isForumRoot) {
        $links[] = array('back', $this->_moduleConfiguration['caption_back'],  NULL);
      }
      $categoriesXml .= $this->_getLinksXml($links);
    }

    if (!empty($this->categs) && is_array($this->categs)) {
      $innerCategoriesXml = '';
      foreach ($this->categs as $categId => $categData) {
        $innerCategoriesXml .= $this->_getCategoryXml($categData);
      }
      $categoriesXml .= $this->_getCategoriesXml($innerCategoriesXml);
    }

    if (!empty($this->boards) && is_array($this->boards)) {
      $innerForumsXml = '';
      foreach ($this->boards as $boardId => $boardData) {
        $innerForumsXml .= $this->_getForumXml(
          $this->_getOutputEntry($boardData), FALSE, $boardData
        );
      }
      $categoriesXml .= $this->_getForumsXml($innerForumsXml);
    }

    return $this->_getForumXml($categoriesXml, TRUE);
  }

  /**
  * Execute user commands and render forum contents depending on settings.
  * This routine does not render the category overview or forum lists.
  * It does not render search results either. It just renders
  * forum entries and executes user requests.
  *
  * @param integer $forumId
  * @param boolean $categMode optional, default FALSE
  * @param boolean $root optional, default FALSE
  * @return string
  */
  protected function _getOutputForum($forumId, $categMode = FALSE, $root = FALSE) {
    $innerXml = $this->_executeForumCommands();

    $innerXml .= $this->_getCaptionsXml(
      array(
        'caption_categories' => $this->_moduleConfiguration['caption_categories'],
        'caption_first' => $this->_moduleConfiguration['caption_first'],
        'caption_last' => $this->_moduleConfiguration['caption_last'],
        'caption_at' => $this->_moduleConfiguration['caption_at'],
        'caption_entries' => $this->_moduleConfiguration['caption_entries'],
        'caption_lastchange' => $this->_moduleConfiguration['caption_lastchange'],
        'caption_newentry' => $this->_moduleConfiguration['caption_newentry'],
        'caption_forums' => $this->_moduleConfiguration['caption_forums'],
        'caption_lastentry' => $this->_moduleConfiguration['caption_lastentry'],
        'caption_lastentries' => $this->_moduleConfiguration['caption_lastentries'],
        'caption_from' => $this->_moduleConfiguration['caption_from'],
        'caption_threads' => $this->_moduleConfiguration['caption_threads'],
        'caption_registered' => $this->_moduleConfiguration['caption_registered']
      )
    );
    if (!empty($this->_moduleConfiguration['search_enabled'])) {
      $innerXml .= $this->_getSearchFormXml($this->_moduleConfiguration['caption_search']);
    }

    if ($this->_isInternalMode('topics')) {
      $innerXml .= $this->_getTopicsXml();

    } elseif ($this->_isInternalMode('search')) {
      $innerXml .= $this->_getSearchResultsXml();

    } elseif ($this->_isInternalMode('threaded', 'bbs')) {
      $innerXml .= $this->_getOutputThread(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE);

    } elseif ($this->_isInternalMode('threaded')) {
      $innerXml .= $this->_getOutputThread(TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, TRUE);
      if (isset($this->entry)) {
        $innerXml .= $this->_getOutputThread($this->entry, FALSE, TRUE, TRUE, TRUE, FALSE, FALSE);
      }

    } elseif ($this->_isInternalMode('bbs')) {
      $innerXml .= $this->_getOutputThread(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE);

    }
    $innerXml .= $this->_getForumLinksXml();

    return $this->_getForumXml($innerXml);
  }

  /**
  * Get output for entries (view).
  *
  * @param boolean $showViewLink
  * @param boolean $showEditLink
  * @param boolean $showText
  * @param boolean $showUser
  * @param boolean $showAnswerLink
  * @param boolean $showCiteLink
  * @param boolean $showDepth
  * @return string xml
  */
  protected function _getOutputThread(
              $showViewLink = TRUE, $showEditLink = TRUE, $showText = FALSE, $showUser = FALSE,
              $showAnswerLink = FALSE, $showCiteLink = FALSE, $showDepth = TRUE
            ) {

    $innerXml = '';
    if (!empty($this->entries) && is_array($this->entries)) {

      if ($this->_isInternalMode('threaded')) {
        // @todo check this
        $threadId = $this->entry['entry_id'];
      } else {
        $threadId = !empty($this->params['thread_id']) ? $this->params['thread_id'] : NULL;
      }
      $entriesXml = $this->_getOutputThreadEntries(
        $threadId,
        $showViewLink,
        $showEditLink,
        $showText,
        $showUser,
        $showAnswerLink,
        $showCiteLink,
        $showDepth
      );
      $innerXml .= $this->_getEntriesXml($entriesXml);
    }
    return $this->_getThreadXml($threadId, $innerXml);
  }

  /**
  * Create xml output for a currently loaded entryTree by traversing the tree recursively.
  *
  * @param integer $threadId
  * @param boolean $showViewLink
  * @param boolean $showEditLink
  * @param boolean $showText
  * @param boolean $showUser
  * @param boolean $showAnswerLink
  * @param boolean $showCiteLink
  * @param boolean $showDepth
  * @return string $result xml
  */
  protected function _getOutputThreadEntries(
                       $threadId = 0,
                       $showViewLink = TRUE, $showEditLink = TRUE, $showText = FALSE,
                       $showUser = FALSE, $showAnswerLink = FALSE, $showCiteLink = FALSE,
                       $showDepth = TRUE
                     ) {
    $result = '';

    if ($threadId != 0) {
      /* output a range of threads */
      $counter = 0;
      $position = 0;
      $groupedEntries = array();
      if (!empty($this->entries) && is_array($this->entries)) {
        if (count($this->entries) < $this->spliceOfs) {
          $params = $this->params;
          $params['offset'] = $this->spliceMax * (int)(count($this->entries) / $this->spliceMax);
          papaya_page::sendHeader('X-Papaya-Status: forum entry offset out of bounds');
          papaya_page::sendHeader('Location: '.$this->_getLinkWithParams($params, NULL, TRUE));
          exit();
        }
        if ($this->spliceOfs / $this->spliceMax != (int)($this->spliceOfs / $this->spliceMax)) {
          $params = $this->params;
          $params['offset'] = $this->spliceMax * (int)($this->spliceOfs / $this->spliceMax);
          papaya_page::sendHeader('X-Papaya-Status: forum entry offset in between pages');
          papaya_page::sendHeader('Location: '.$this->_getLinkWithParams($params, NULL, TRUE));
          exit();
        }
        // separate entries in page array
        foreach ($this->entries as $entry) {
          if ($counter == $this->spliceMax) {
            $position += $this->spliceMax;
            $counter = 0;
          }
          $groupedEntries[$position][$entry['entry_id']] = $entry;
          $counter++;
        }
        // search with page array element contents the requested entry and show it
        foreach ($groupedEntries as $offset => $entries) {
          if (($this->params['offset'] == 0 && $offset == 0) ||
              (!empty($this->params['offset']) && $offset == $this->params['offset']) ||
              (
               !empty($this->params['entry_id']) &&
               array_key_exists($this->params['entry_id'], $entries)
              )
             ) {
            $this->params['offset'] = $offset;
            foreach ($entries as $entry) {
              $result .= $this->_getOutputEntry(
                $entry,
                $showViewLink,
                $showEditLink,
                $showText,
                $showUser,
                $showAnswerLink,
                $showCiteLink,
                $showDepth
              );
            }
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get output for an entry.
  *
  * @param array $entry
  * @param boolean $showViewLink
  * @param boolean $showEditLink
  * @param boolean $showText
  * @param boolean $showUser
  * @param boolean $showAnswerLink
  * @param boolean $showCiteLink
  * @param boolean $showDepth
  * @param boolean $pageId
  * @return string
  */
  protected function _getOutputEntry(
                       $entry,
                       $showViewLink = FALSE, $showEditLink = FALSE, $showText = TRUE,
                       $showUser = TRUE, $showAnswerLink = FALSE, $showCiteLink = FALSE,
                       $showDepth = TRUE, $pageId = NULL
                     ) {
    if (!isset($entry['entry_id'])) {
      return '';
    }
    $result = '';
    if (empty($pageId)) {
      $pageId = $this->_pageId;
    }
    $isCurrent = FALSE;
    if (!$this->_isInternalMode('last_entries')) {
      if (!empty($this->entry) && isset($this->entry['entry_id']) &&
          $this->entry['entry_id'] == $entry['entry_id']) {
        $isCurrent = TRUE;
      }
      if (isset($this->_moduleConfiguration['mode'])) {
        if ($this->_moduleConfiguration['mode'] == 2 || $this->_moduleConfiguration['mode'] == 1) {
          $isCurrent = TRUE;
        }
      }
    }
    if (!empty($entry) && is_array($entry)) {
      $linksXml = $this->_getForumEntryLinksXml(
        $entry, $pageId, $showViewLink, $showEditLink, $showCiteLink, $showAnswerLink, $isCurrent
      );
      $result .= $this->_getForumEntryXml(
        $entry, $pageId, $showText, $showUser, $showDepth, $isCurrent, $linksXml
      );
    }
    return $result;
  }

  /***************************************************************************/
  /** XML Outputs                                                            */
  /***************************************************************************/

  /**
  * Returns xml representation for error message.
  *
  * @param string $msg
  * @param integer $id
  * @return string xml
  */
  protected function _getErrorMessageXml($msg, $id = NULL) {
    return $this->_getMessageXml('error', $msg, $id);
  }

  /**
  * Returns xml representation for success message.
  *
  * @param string $msg
  * @return string xml
  */
  protected function _getSuccessMessageXml($msg) {
    return $this->_getMessageXml('success', $msg);
  }

  /**
  * Returns xml representation for messages.
  *
  * @param string $type
  * @param string $msg
  * @param integer $id
  * @return string xml
  */
  protected function _getMessageXml($type, $msg, $id = NULL) {
    return sprintf(
      '<message type="%s"%s>%s</message>'.LF,
      papaya_strings::escapeHTMLChars($type),
      (isset($id) && !empty($id)) ? sprintf(' fid="%s"', $id) : '',
      $this->module->getXHTMLString($msg)
    );
  }

  /**
  * Print the provided array of key value pairs as a
  * caption translation xml structure.
  *
  * @param $captions
  * @return string $result xml
  */
  protected function _getCaptionsXml($captions) {
    $result = '';
    if (!empty($captions) && is_array($captions)) {
      $result = '<captions>'.LF;
      foreach ($captions as $name => $caption) {
        $result .= sprintf(
          '<caption name="%s">%s</caption>'.LF,
          papaya_strings::escapeHTMLChars($name),
          $this->getXHTMLString($caption)
        );
      }
      $result .= '</captions>'.LF;
    }
    return $result;
  }

  /**
  * Get a generic xml for links.
  *
  * @param array $links
  * @param integer $pageId
  * @return $result xml
  */
  protected function _getLinksXml($links = array(), $pageId = NULL) {
    $result = '';

    if (!empty($links)) {
      $innerXml = '';

      foreach ($links as $link) {
        $innerXml .= sprintf(
          '<link type="%s" caption="%s" href="%s" />'.LF,
          papaya_strings::escapeHTMLChars($link[0]),
          papaya_strings::escapeHTMLChars($link[1]),
          papaya_strings::escapeHTMLChars($this->_getLinkWithParams($link[2]))
        );
      }
      $result .= sprintf('<links>'.LF.'%s</links>'.LF, $innerXml);
    }

    return $result;
  }

  /**
  * Get generic xml for a categories.
  *
  * @param string $innerXml
  * @return string $result xml
  */
  protected function _getCategoriesXml($innerXml = '') {
    $result = '';
    if (!empty($this->categs) && is_array($this->categs)) {
      $result .= '<categories>'.LF;
      $result .= $innerXml;
      $result .= '</categories>'.LF;
    }
    return $result;
  }

  /**
  * Get xml for a category.
  *
  * @param array $category data to instead of fixed category data.
  * @return string $result xml
  */
  protected function _getCategoryXml($category = array()) {
    $result = '';

    if (empty($category) && !empty($this->category)) {
      $category = $this->category;
    }
    if (!empty($category)) {
      $result .= sprintf(
        '<category id="%d" title="%s" href="%s" categories="%d">%s</category>'.LF,
        (int)$category['forumcat_id'],
        papaya_strings::escapeHTMLChars($category['forumcat_title']),
        papaya_strings::escapeHTMLChars(
          $this->_getLinkWithParams(array('categ_id' => $category['forumcat_id']))
        ),
        (!empty($this->categTree[$category['forumcat_id']])) ?
          count($this->categTree[$category['forumcat_id']]) : 0,
        $this->getXHTMLString($category['forumcat_desc'])
      );
    }

    return $result;
  }

  /**
  * Get generic xml for forums.
  *
  * @return $result xml
  */
  protected function _getForumsXml($innerXml = '') {
    $result = '';
    if (!empty($this->boards) && is_array($this->boards)) {
      $result .= '<forums>'.LF;
      $result .= $innerXml;
      $result .= '</forums>'.LF;
    }
    return $result;
  }

  /**
  * Get generic xml for forum.
  *
  * @return $result xml
  */
  protected function _getForumXml($innerXml = '', $simpleNode = FALSE, $boardData = array()) {
    $result = '';

    if (empty($boardData)) {
      $boardData = $this->board;
    }
    if (isset($boardData) && $simpleNode == FALSE) {

      $additionalAttributes = '';
      if (isset($boardData['thread_count'])) {
        $additionalAttributes .= sprintf(' thread_count="%d"', (int)$boardData['thread_count']);
      }
      if (isset($boardData['entry_count'])) {
        $additionalAttributes .= sprintf(' entry_count="%d"', (int)$boardData['entry_count']);
      }

      $result .= sprintf(
        '<forum id="%d" title="%s" href="%s" mode="%d"%s>'.LF,
        (int)$boardData['forum_id'],
        papaya_strings::escapeHTMLChars($boardData['forum_title']),
        papaya_strings::escapeHTMLChars(
          $this->_getLinkWithParams(array('forum_id' => $boardData['forum_id']))
        ),
        (int)$this->_moduleConfiguration['mode'],
        $additionalAttributes
      );
      if (!empty($boardData['forum_desc'])) {
        $result .= sprintf(
          '<description>%s</description>'.LF,
          papaya_strings::escapeHTMLChars($boardData['forum_desc'])
        );
      }
      $result .= $innerXml.
        '</forum>'.LF;

    } elseif (!empty($innerXml)) {
      $result .= '<forum>'.LF.
        $innerXml.
        '</forum>'.LF;
    }

    return $result;
  }

  /**
  * Get a xml with links for forum view.
  *
  * @return string xml
  */
  protected function _getForumLinksXml() {
    $links = array();

    $linkParams = array();
    if (!$this->_isForumRoot) {
      if ($this->_isInternalMode('search')) {
        // back to last page link in search (view)
        if (!empty($this->params['forum_id'])) {
          $linkParams['forum_id'] = $this->params['forum_id'];
        }
        $links[] = array('back', $this->_moduleConfiguration['caption_back'], $linkParams);

      } else {

        // back to last page link in forums
        if (!empty($this->params['searchfor'])) {
          $linkParams['search'] = $this->params['search'];
          $linkParams['searchfor'] = $this->params['searchfor'];
          $linkParams['cmd'] = 'search';
        }
        if (!empty($this->entry) && !empty($this->entry['forum_id'])) {
          $linkParams['forum_id'] = (int)$this->entry['forum_id'];
        } elseif (!empty($this->board) && !empty($this->board['forumcat_id'])) {
          $linkParams['categ_id'] = (int)$this->board['forumcat_id'];
        }
        $links[] = array('back', $this->_moduleConfiguration['caption_back'], $linkParams);
      }
    }

    $baseLinkParams = array();
    if (isset($this->board) && !empty($this->board['forum_id'])) {
      $baseLinkParams['forum_id'] = $this->board['forum_id'];
    } else {
      $baseLinkParams['forum_id'] = $this->params['forum_id'];
    }
    if (isset($this->entry) && !empty($this->entry['entry_id'])) {
      $baseLinkParams['thread_id'] = $this->entry['entry_id'];
    }

    if (empty($this->entries)) {
      // forum view without entries
      if (!empty($this->_moduleConfiguration['post_questions'])) {

        $links[] = array(
          'add',
          $this->_moduleConfiguration['caption_newquestion'],
          array_merge($baseLinkParams, array('cmd' => 'add_entry'))
        );
      }

    } else {
      // forum view with entries
      if (!empty($this->_moduleConfiguration['post_answers']) &&
          $this->_moduleConfiguration['mode'] == 1 &&
          !empty($this->params['thread_id']) && !empty($this->params['forum_id'])) {

        $linkParams = array_merge(
          $baseLinkParams,
          array(
            'cmd' => 'add_entry',
            'forum_id' => (int)$this->params['forum_id'],
            'thread_id' => (int)$this->params['thread_id']
          )
        );
        if (!empty($this->params['entry_id'])) {
          $linkParams['entry_id'] = (int)$this->params['entry_id'];
        }
        if (!empty($this->params['offset'])) {
          $linkParams['offset'] = (int)$this->params['offset'];
        }

        $links[] = array('reply', $this->_moduleConfiguration['caption_newanswer'], $linkParams);
      }
    }

    if ($this->surferHandle &&
        !empty($this->params['thread_id']) && !empty($this->params['forum_id'])) {
      // thread view with subscription links
      if ($this->_moduleConfiguration['subscribe_threads']) {
        if ($this->checkSurferSubscribedThread($this->surferHandle, $this->params['thread_id'])) {

          $links[] = array(
            'unsubscribe',
            $this->_moduleConfiguration['caption_unsubscribe'],
            array(
              'cmd' => 'unsubscribe',
              'forum_id' => (int)$this->params['forum_id'],
              'thread_id' => (int)$this->params['thread_id']
            )
          );
        } else {

          $links[] = array(
            'subscribe',
            $this->_moduleConfiguration['caption_subscribe'],
            array(
              'cmd' => 'subscribe',
              'forum_id' => (int)$this->params['forum_id'],
              'thread_id' => (int)$this->params['thread_id']
            )
          );
        }
      }
    }

    return $this->_getLinksXml($links);
  }

  /**
  * Returns xml representation for search results.
  *
  * @return string $xml
  */
  protected function _getSearchResultsXml() {
    $result = '';
    if (isset($this->searchResults) && is_array($this->searchResults)) {
      $result .= '<searchresults>'.LF;
      foreach ($this->searchResults as $searchResult) {
        $result .= $this->_getOutputEntry(
          $searchResult, FALSE, FALSE, TRUE, TRUE, FALSE, FALSE, FALSE
        );
      }

      if (!empty($this->searchResultsCount)) {
        $result .= $this->getPagelinksXml(
          $this->searchResultsCount,
          $this->_moduleConfiguration['perpage'],
          $this->params['offset'],
          'offset',
          $this->params
        );
      }
      $result .= '</searchresults>'.LF;
    }
    return $result;
  }

  /**
  * Returns xml representation of a list of topics.
  * Topics are those entries within a forum which do not have parents, called threads.
  *
  * @return xml
  */
  protected function _getTopicsXml() {
    $result = '';
    if (!empty($this->topics) && is_array($this->topics)) {
      $result .= sprintf(
        '<topics count="%d" perpage="%d">'.LF,
        $this->topicCount,
        $this->_moduleConfiguration['perpage']
      );

      foreach ($this->topics as $topic) {
        $result .= $this->_getOutputEntry($topic, FALSE, FALSE);
      }
      if (!empty($this->topicCount)) {
        $result .= $this->getPagelinksXml(
          $this->topicCount,
          $this->_moduleConfiguration['perpage'],
          $this->params['offset'],
          'offset',
          $this->params
        );
      }

      $result .= '</topics>'.LF;
    }
    return $result;
  }

  /**
  * Get xml for a thread.
  *
  * @param integer $threadId
  * @param string $innerXml xml string to set in node
  * @return string xml
  */
  protected function _getThreadXml($threadId, $innerXml = '') {
    $params = array();
    if (!empty($this->params['forum_id'])) {
      $params['forum_id'] = $this->params['forum_id'];
    }
    $params['thread_id'] = $threadId;
    if (!empty($this->params['offset'])) {
      $params['offset'] = $this->params['offset'];
    }
    if ($this->_isInternalMode('threaded')) {
      $entry = $this->entry;
    } elseif (!empty($this->entries)) {
      // get the first entry from entries because $this->entry can be another entry (selection)
      $entry = reset($this->entries);
    }
    $result = sprintf(
      '<thread href="%s" title="%s" created="%s" modified="%s" answers="%d">'.LF,
      papaya_strings::escapeHTMLChars($this->_getLinkWithParams($params)),
      papaya_strings::escapeHTMLChars($entry['entry_subject']),
      date('Y-m-d H:i:s', $entry['entry_created']),
      date('Y-m-d H:i:s', $entry['entry_modified']),
      (int)$entry['entry_thread_count']
    );
    return $result.$innerXml.'</thread>'.LF;
  }

  /**
  * Get xml for entries.
  *
  * @param string $innerXml xml string to set in node
  * @return string $result xml
  */
  protected function _getEntriesXml($innerXml = '') {
    $result = '';
    if (!empty($this->entries) && is_array($this->entries)) {
      $result .= '<entries>'.LF;
      $result .= $innerXml;
      if (!empty($this->entryCount)) {
        // entryCount has the answers, but not the initial thread post
        // @todo check limit parameter to extend functionality
        $result .= $this->getPagelinksXml(
          $this->entryCount + 1,
          $this->_moduleConfiguration['perpage'],
          $this->params['offset'],
          'offset',
          array(
            'forum_id' => empty($this->params['forum_id']) ? 0 : (int)$this->params['forum_id'],
            'thread_id' => empty($this->params['thread_id']) ? 0 : (int)$this->params['thread_id']
          )
        );
      }
      $result .= '</entries>'.LF;
    }
    return $result;
  }

  /**
  * Get xml for an entry.
  *
  * @param array $entry
  * @param integer $pageId
  * @param boolean $showText
  * @param boolean $showUser
  * @param boolean $showDepth
  * @param boolean $isCurrent
  * @return string $result xml
  */
  protected function _getForumEntryXml(
                       $entry, $pageId, $showText, $showUser, $showDepth, $isCurrent, $linksXml
                     ) {
    $result = '';
    $anchorLink = '';
    $anchorName = '';

    if ($this->_isInternalMode('last_entries') && !empty($entry['entry_pid'])) {
      $entryId = (int)$entry['entry_pid'];

    } elseif (!empty($this->_moduleConfiguration['mode']) &&
        !empty($entry['entry_path']) && $entry['entry_path'] != ';;') {
      $entryId = (int)preg_replace('(^;(\d+).*)', '\1', $entry['entry_path']);
    }

    if (!$this->_isInternalMode('topics')) {
      $anchorLink = sprintf('#entry%d', (int)$entry['entry_id']);
      $anchorName = sprintf(' anchor="entry%d"', (int)$entry['entry_id']);
    } else {
      $anchorLink = '#forum_head';
      $anchorName = ' anchor="forum_head"';
    }
    $entryLinkParams = array(
      'forum_id' => (int)$entry['forum_id'],
      'thread_id' => isset($entryId) ? $entryId : (int)$entry['entry_id']
    );
    if (isset($entryId)) {
      $entryLinkParams['entry_id'] = $entry['entry_id'];
    }
    if (!empty($this->params['searchfor'])) {
      $entryLinkParams['searchfor'] = $this->params['searchfor'];
      $entryLinkParams['search'] = 1;
      $entryLinkParams['fromoffset'] = $this->params['offset'];
    }

    $entryDepth = ($showDepth && isset($entry['entry_depth'])) ? (int)$entry['entry_depth'] : 0;
    $threadCount = isset($entry['entry_thread_count']) ? (int)$entry['entry_thread_count'] : 0;
    $modifiedDate = !empty($entry['entry_thread_modified']) ?
      date('Y-m-d H:i:s', $entry['entry_thread_modified']) : 0;
    $additionalAttributes = ($isCurrent) ? 'selected="selected"': '';
    if (isset($this->params['submitted']) &&
        (int)$this->params['submitted'] == (int)$entry['entry_id']
       ) {
      $additionalAttributes .= ' submitted="true"';
    }
    $result .= sprintf(
      '<entry href="%s%s"%s id="%d" created="%s" modified="%s" indent="%d"'.
      ' answers="%d" thread_modified="%s" %s>'.LF,
      papaya_strings::escapeHTMLChars($this->_getLinkWithParams($entryLinkParams)),
      $anchorLink,
      $anchorName,
      (int)$entry['entry_id'],
      date('Y-m-d H:i:s', $entry['entry_created']),
      date('Y-m-d H:i:s', $entry['entry_modified']),
      $entryDepth,
      $threadCount,
      $modifiedDate,
      $additionalAttributes
    );
    $result .= $linksXml;

    if (empty($entry['entry_blocked'])) {
      $result .= sprintf(
        '<subject>%s</subject>'.LF, $this->module->getXHTMLString($entry['entry_subject'])
      );
      if ($showText && isset($entry['entry_text'])) {
        $result .= sprintf(
          '<text>%s</text>'.LF,
          $this->module->getXHTMLString(
            $entry['entry_text'],
            ($this->_moduleConfiguration['richtext_enabled']) ? NULL : TRUE
          )
        );
      }
    } else {
      $result .= sprintf(
        '<subject>%s</subject>'.LF,
        isset($this->_moduleConfiguration['abuse_entry_title']) ?
          $this->_moduleConfiguration['abuse_entry_title'] : 'Entry blocked.'
      );
      if ($showText) {
        $result .= sprintf(
          '<text>%s</text>'.LF,
          isset($this->_moduleConfiguration['abuse_entry_text']) ?
            $this->_moduleConfiguration['abuse_entry_text'] : 'Entry blocked.'
        );
      }
    }
    if (isset($entry['forum_title'])) {
      $result .= sprintf(
        '<forum>%s</forum>'.LF, papaya_strings::escapeHTMLChars($entry['forum_title'])
      );
    }
    if (isset($entry['forumcat_title'])) {
      $result .= sprintf(
        '<category>%s</category>'.LF, papaya_strings::escapeHTMLChars($entry['forumcat_title'])
      );
    }

    if ($showUser) {
      if (isset($this->users[$entry['entry_userhandle']]) &&
          is_array($this->users[$entry['entry_userhandle']]) &&
          count($this->users[$entry['entry_userhandle']]) > 2) {

        $user = $this->users[$entry['entry_userhandle']];
        if (!empty($this->_moduleConfiguration['special_group']) &&
            $user['surfergroup_id'] == $this->_moduleConfiguration['special_group']) {
          $specialGroup = 'true';
        } else {
          $specialGroup = 'false';
        }
        $result .= sprintf(
          '<user registered="true" specialgroup="%s">'.LF.
          '<username>%s</username>'.LF.
          '<handle>%s</handle>'.LF.
          '<givenname>%s</givenname>'.LF.
          '<surname>%s</surname>'.LF.
          '<registration>%s</registration>'.LF.
          '<lastlogin>%s</lastlogin>'.LF.
          '<group>%d</group>'.LF.
          '<entries>%d</entries>'.LF,
          $specialGroup,
          papaya_strings::escapeHTMLChars($entry['entry_username']),
          papaya_strings::escapeHTMLChars($user['surfer_handle']),
          papaya_strings::escapeHTMLChars($user['surfer_givenname']),
          papaya_strings::escapeHTMLChars($user['surfer_surname']),
          date('Y-m-d H:i:s', $user['surfer_registration']),
          date('Y-m-d H:i:s', $user['surfer_lastlogin']),
          (int)$user['surfergroup_id'],
          isset($this->users[$entry['entry_userhandle']]['entry_count']) ?
            (int)$this->users[$entry['entry_userhandle']]['entry_count'] : 0
        );
        if (!empty($user['surfer_avatar'])) {
          $result .= sprintf(
            '<avatar>'.
            '<papaya:media src="%s" width="%d" height="%d" resize="maxfill" />'.
            '</avatar>'.LF,
            papaya_strings::escapeHTMLChars($user['surfer_avatar']),
            (int)$this->_moduleConfiguration['avatar_width'],
            (int)$this->_moduleConfiguration['avatar_height']
          );
        }
        $result .= '</user>'.LF;
      } else {
        $result .= sprintf(
          '<user registered="false" specialgroup="false">'.LF.
          '<username>%s</username>'.LF.
          '</user>'.LF,
          papaya_strings::escapeHTMLChars($entry['entry_username'])
        );
      }
    }
    $result .= '</entry>'.LF;
    return $result;
  }

  /**
  * Get xml for forum entry links.
  *
  * @param array $entry
  * @param integer $pageId
  * @param boolean $showViewLink
  * @param boolean $showEditLink
  * @param boolean $showCiteLink
  * @param boolean $showAnswerLink
  * @param boolean $isCurrent
  * @return string xml
  */
  protected function _getForumEntryLinksXml(
                       $entry, $pageId,
                       $showViewLink, $showEditLink, $showCiteLink, $showAnswerLink, $isCurrent
                     ) {
    $links = array();
    // general link parameters
    $linkParams = array(
      'forum_id' => (int)$entry['forum_id'],
      'thread_id' => empty($this->params['thread_id']) ? 0 : (int)$this->params['thread_id']
    );
    if ((int)$entry['entry_id'] > 0) {
      $linkParams['entry_id'] = (int)$entry['entry_id'];
    }
    if (!empty($this->params['offset'])) {
      $linkParams['offset'] = (int)$this->params['offset'];
    }
    // view link
    if ($showViewLink && !$isCurrent) {
      $links[] = array('view', $this->_moduleConfiguration['caption_edit'], $linkParams);
    }
    // edit link
    if ($showEditLink && $isCurrent && $this->_moduleConfiguration['post_answers'] &&
        !empty($this->surferHandle) && $this->surferHandle == $entry['entry_userhandle']) {
      $editLinkParams = $linkParams;
      $editLinkParams['cmd'] = 'edit_entry';
      $links[] = array('edit', $this->_moduleConfiguration['caption_edit'], $editLinkParams);
    }
    // cite link
    if ($showCiteLink && $isCurrent && $this->_moduleConfiguration['post_answers']) {
      $citeLinkParams = $linkParams;
      $citeLinkParams['cmd'] = 'cite_entry';
      $links[] = array('cite', $this->_moduleConfiguration['caption_cite'], $citeLinkParams);
    }
    // answere link
    if ($showAnswerLink && $isCurrent && $this->_moduleConfiguration['post_answers']) {
      $answLinkParams = $linkParams;
      $answLinkParams['cmd'] = 'add_entry';
      $links[] = array(
        'answer', $this->_moduleConfiguration['caption_newanswer'], $answLinkParams
      );
    }
    return $this->_getLinksXml($links, $pageId);
  }

  /**
  * Output when using this content element as a comment
  * tool. A comment page is associated with a pageId and a category. If
  * there is no forum with the provided pageId contained within the specified
  * category and a user comments a page, a new forum is created. If a forum allready
  * exists, either a new thread is created in there or a new subthread is created
  * when he writes an answer to a specific entry. When the command 'cmd' is set
  * to 'add_entry' and 'save' is not defined and set to 1, a post form to write
  * a new comment is returned. If 'cmd' and 'save' have been provided having
  * 'comment' and 1, a new post or a new forum is created.
  *
  * @return xml string
  */
  protected function _getOutputComments() {
    $result = '';
    if (!empty($this->params['forum_id'])) {
      $result = sprintf(
        '<comments>'.LF.
        '%s</comments>'.LF,
        $this->_getOutputForum($this->params['forum_id'])
      );
    }
    return $result;
  }

  /**
  * Depending on provided arguments, this function reads the most
  * recent $maxCount entries from either a specific thread, a specific forum
  * or a category. When no parameter is provided all entries from
  * all categories and forums will be taken into consideration.
  *
  * @param $categId int (optional)
  * @param $forumId int (optional)
  * @param $threadId int (optional)
  * @param $maxCount int {i e N0} (optional, default 10)
  * @return string $result xml
  */
  public function getOutputLastEntries(
           $categId = NULL, $forumId = NULL, $threadId = NULL, $maxCount = 10
         ) {
    $result = '';
    $this->loadLastEntries($categId, $forumId, $threadId, $maxCount);
    if (!empty($this->entries)) {
      $this->_addInternalModeState('last_entries');
      foreach ($this->entries as $entry) {
        $result .= $this->_getOutputEntry($entry, FALSE, FALSE, FALSE, TRUE, FALSE, FALSE, FALSE);
      }
    }
    return $this->_getEntriesXml($result);
  }

  /***************************************************************************/
  /** Forms                                                                  */
  /***************************************************************************/

  /**
  * Initialize search formular.
  */
  protected function _initializeSearchForm() {
    if (!(isset($this->searchDialog) && is_object($this->searchDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');

      $hidden = $this->_getFixedParameters($this->params, array('categ_id', 'forum_id'));
      $hidden = array_merge(
        $hidden, array('offset' => 0, 'cmd' => 'search', 'save' => 1, 'search' => 1)
      );
      $fields = array(
        'searchfor' => array(
          $this->_moduleConfiguration['caption_search'], 'isNoHTML', TRUE, 'input', 200
        )
      );
      $data = array();
      $this->searchDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->searchDialog->msgs = &$this->msgs;
      $this->searchDialog->buttonTitle = $this->_moduleConfiguration['caption_search'];
      $this->searchDialog->loadParams();
    }
  }

  /**
  * Get search formular.
  *
  * @param string $caption
  * @return string
  */
  protected function _getSearchFormXml($caption) {
    $this->_initializeSearchForm();
    $this->searchDialog->baseLink = $this->baseLink;
    $this->searchDialog->dialogTitle = htmlspecialchars($caption);
    $this->searchDialog->dialogDoubleButtons = FALSE;
    return '<searchdlg>'.LF.$this->searchDialog->getDialogXML().'</searchdlg>'.LF;
  }

  /**
  * Inititalize output form.
  */
  protected function _initializeOutputForm() {
    if (!(isset($this->outputDialog) && is_object($this->outputDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_frontend_form.php');
      if (empty($this->params['cmd'])) {
        $this->params['cmd'] = 'add_entry';
      }

      $data = array();
      switch ($this->params['cmd']) {
      case 'add_entry' :
      case 'cite_entry' :
        if (isset($this->entry) && isset($this->entry['entry_subject'])) {
          if (0 === strpos(strtolower($this->entry['entry_subject']), 're:')) {
            $data['entry_subject'] = $this->entry['entry_subject'];
          } else {
            $data['entry_subject'] = 'Re: '.$this->entry['entry_subject'];
          }
        } else {
          $data['entry_subject'] = '';
        }
        break;
      case 'edit_entry' :
      case 'cite_entry' :
        if (isset($this->entry)) {
          $data['entry_subject'] = $this->entry['entry_subject'];
          $data['entry_text'] = $this->entry['entry_text'];
        }
        if ($this->params['cmd'] == 'cite_entry') {
          $data['entry_text'] = sprintf('<quote>%s</quote>'.LF, $data['entry_text']);
          $data['entry_subject'] = 'Re: '.$this->entry['entry_subject'];
          $this->params['cmd'] = 'add_entry';
        }
        break;
      }

      $hidden = array();
      if (isset($this->board) && !empty($this->board['forum_id'])) {
        $hidden['forum_id'] = $this->board['forum_id'];
      }

      $hidden = array_merge(
        $hidden,
        $this->_getFixedParameters(
          $this->params, array('thread_id', 'entry_id', 'offset', 'cmd')
        )
      );
      $hidden['save'] = 1;

      $fields = array(
        'entry_subject' => array(
          $this->_moduleConfiguration['caption_subject'], 'isNoHTML', TRUE, 'input', 200
        ),
        'entry_text' => array(
          $this->_moduleConfiguration['caption_text'],
          'isSomeText',
          TRUE,
          ($this->_moduleConfiguration['richtext_enabled']) == 1 ? 'richtext' : 'textarea',
          8
        )
      );
      if (!$this->surferValid) {
        $fields['entry_username'] = array(
          $this->_moduleConfiguration['caption_username'], 'isNoHTML', TRUE, 'input', 100
        );
        $fields['entry_useremail'] = array(
          $this->_moduleConfiguration['caption_useremail'],
          'isEMail',
          !empty($this->_moduleConfiguration['email_mandatory']) ? TRUE : FALSE,
          'input',
          150
        );
        // surfer not logged in, use captcha if requested
        if (!empty($this->_moduleConfiguration['use_captcha'])) {
          $fields['entry_captcha'] = array(
            papaya_strings::escapeHTMLChars($this->_moduleConfiguration['captcha_title']),
            'isNoHTML',
            TRUE,
            'captcha',
            $this->_moduleConfiguration['captcha_type']
          );
        }
      }

      $this->outputDialog = new base_frontend_form(
        $this, $this->paramName, $fields, $data, $hidden
      );
      $this->outputDialog->dialogTitle = (
        isset($hidden['thread_id']) && (int)$hidden['thread_id'] > 0) ?
        $this->_moduleConfiguration['caption_newanswer'] :
        $this->_moduleConfiguration['caption_newquestion'];
      $this->outputDialog->msgs = $this->msgs;
      $this->outputDialog->loadParams();
      $this->outputDialog->useToken = FALSE;
      $this->outputDialog->dialogHideButtons = FALSE;
      $this->outputDialog->buttonTitle = $this->_moduleConfiguration['caption_submit'];
      $resetMode = 'reset';
      if (!empty($this->_moduleConfiguration['reset_hides_form'])) {
        $resetMode = 'submit';
      }
      $this->outputDialog->addButton(
        'reset', $this->_moduleConfiguration['caption_cancel'], $resetMode
      );
    }
  }

  /**
  * Get output form.
  *
  * @param string $caption
  * @return string
  */
  protected function _getOutputForm() {
    $this->_initializeOutputForm();
    return sprintf('<newdlg>%s</newdlg>'.LF, $this->outputDialog->getDialogXml());
  }

  /*******************************************************************************************
    OLD CODE / OLD CODE / OLD CODE / OLD CODE / OLD CODE / OLD CODE / OLD CODE / OLD CODE
  ********************************************************************************************/

  /**
  * Sends a notification email to all subscribers of the currently
  * loaded entry's thread.
  *
  * @param mixed $entryId optional, default NULL
  */
  function notifySubscribedSurfers($entryId = NULL) {
    if (empty($this->_moduleConfiguration['subscriber_sendmails'])) {
      return;
    }
    if (TRUE !== $this->loadEntry($entryId, FALSE)) {
      return;
    }
    $path = explode(';', $this->entry['entry_path']);
    if (isset($path[count($path) - 2])) {
      $subscribers = $this->getSubscribedSurfers($path[1]);
    } else {
      $subscribers = $this->getSubscribedSurfers($this->entry['entry_id']);
    }
    if (!empty($subscribers) && !empty($this->entry)) {
      foreach ($subscribers as $surferHandle => $surfer) {
        if ($surferHandle == $this->entry['entry_userhandle']) {
          continue;
        }

        $name = (empty($surfer['surfer_givenname']) || empty($surfer['surfer_surname']))
          ? $surfer['surfer_handle']
          : $surfer['surfer_givenname'].' '.$surfer['surfer_surname'];

        $this->sendNotificationMail($surfer['surfer_email'], $name, 'SUBSCRIPTION');
      }
    }
  }

  /**
  * Sends a notification email to the administrator of the
  * currently selected thread, if this has been activated in
  * the content properties of the page or box where the forum
  * is shown in and the administrator has set send_emails to 1
  * here.
  *
  * @param mixed $entryId id of the new/modified entry or NULL
  */
  function notifyAdministrator($entryId = NULL) {
    if ($this->_moduleConfiguration['admin_sendmails'] == 0) {
      return;
    }
    if (TRUE !== $this->loadEntry($entryId, FALSE)) {
      return;
    }
    if (!empty($this->entry)) {
      $this->sendNotificationMail(
        $this->_moduleConfiguration['admin_email'],
        $this->_moduleConfiguration['admin_name'],
        'ADMINISTRATOR'
      );
    }
  }

  /**
  * Send one notification email for the currently selected thread.
  *
  * @param string $email of the recipient
  * @param string $name of the recipient
  * @param string $type 'ADMINISTRATOR' or 'SUBSCRIPTION'
  * @return void
  */
  function sendNotificationMail($email, $name, $type) {
    $emailObj = new email;
    $emailObj->setReturnPath($this->_moduleConfiguration['email_from_email']);
    $emailObj->setSender(
      $this->_moduleConfiguration['email_from_email'],
      $this->_moduleConfiguration['email_from_name']
    );
    $emailObj->addAddress($email, $name);
    $threadId = $this->entry['entry_id'];
    if (isset($this->params['thread_id']) && (int)$this->params['thread_id'] > 0) {
      $threadId = (int)$this->params['thread_id'];
    }
    $data = array(
      'PROJECT' => PAPAYA_PROJECT_TITLE,
      'NOTIFY' => $type,
      'RECEIVER' => $name,
      /* @deprecated ENTRY_AUTHOR is not advertised in the backend,
          but remains for backwards compatiblity */
      'ENTRY_AUTHOR' => $this->entry['entry_userhandle'].','.$this->entry['entry_username'],
      'ENTRY_AUTHOR_NAME' => $this->entry['entry_username'],
      'ENTRY_AUTHOR_HANDLE' => $this->entry['entry_userhandle'],
      'ENTRY_SUBJECT' => $this->entry['entry_subject'],
      'ENTRY_TEXT' => $this->entry['entry_text'],
      'ENTRY_CHANGED' => date('Y-m-d H:i:s', $this->entry['entry_modified']),
      'ENTRY_CREATED' => date('Y-m-d H:i:s', $this->entry['entry_created']),
      'LINK' => $this->getAbsoluteUrl(
        $this->_getLinkWithParams(
          array(
            'forum_id' => $this->entry['forum_id'],
            'thread_id' => $threadId,
            'entry_id' => $this->entry['entry_id'],
          )
        ),
        NULL,
        FALSE
      )
    );
    $emailObj->setSubject($this->_moduleConfiguration['email_subject'], $data);
    $emailObj->setBody($this->_moduleConfiguration['email_text'], $data);
    $success = $emailObj->send();
  }

  /**
  * Generate page links.
  * General purpose method.
  *
  * @todo improve paging
  *
  * Sets the param $offsetName for the resulting links.
  */
  function getPagelinksXml($nElements, $perPage, $offset, $offsetName, $params) {
    $result = '<pages>'.LF;

    $nPagesClip = 10;

    $nElements = (int)$nElements;
    if ($nElements <= $perPage || $perPage <= 0) {
      return '';
    }

    if ($perPage > 0) {
      // Unset unnecessary base parameters
      if (empty($params['cmd'])) {
        unset($params['cmd']);
      }
      if (empty($params['thread_id'])) {
        unset($params['thread_id']);
      }
      if (empty($params['categ_id'])) {
        unset($params['categ_id']);
      }
      // Calculate the total number of pages:
      $nPages = (int)($nElements / $perPage) + (((int)($nElements % $perPage) == 0) ? 0:1);
      $iPage = (int)($offset / $perPage);
      $result = sprintf('<pages count="%d" current="%d">'.LF, $nPages, $iPage + 1);
      if ($offset > 0) {
        $previousOffset = $offset - $perPage;
        if ($previousOffset < 0) {
          $previousOffset = 0;
        }
        $params['offset'] = $previousOffset;
        $result .= sprintf(
          '<previous-page href="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_getLinkWithParams($params))
        );
      }
      if ($offset + $perPage < $nElements - 1) {
        $nextOffset = $offset + $perPage;
        $params['offset'] = $nextOffset;
        $result .= sprintf(
          '<next-page href="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->_getLinkWithParams($params))
        );
      }

      if ($nPages <= 1) {
        return '';
      }

      $firstPage = 1;
      $lastPage = $nPages;

      if ($nPagesClip > 0) {
        if ($nPages > $nPagesClip) {
          if ($iPage >= $nPagesClip) {
            $firstPage = $iPage - $nPagesClip;
          }

          if ($iPage <= $nPages - $nPagesClip) {
            $lastPage = $iPage + $nPagesClip + 1;
          }
        }
      }

      $params[$offsetName] = 0;

      $result .= sprintf(
        '<pagelink href="%s" type="start" caption="%s" />'.LF,
        papaya_strings::escapeHTMLChars($this->_getLinkWithParams($params)),
        papaya_strings::escapeHTMLChars($this->_moduleConfiguration['caption_first'])
      );

      $count = 0;
      for ($i = $firstPage; $i <= $lastPage; $i ++) {
        $result .= sprintf(
        '<pagelink caption="%d" href="%s" %s />'.LF,
          (int)$i,
          papaya_strings::escapeHTMLChars($this->_getLinkWithParams($params)),
          ($iPage == ($i - 1)) ? 'selected="selected"' : ''
        );
        $count += $perPage;
        $params[$offsetName] = $count;
      }

      $params[$offsetName] -= $perPage;

      $result .= sprintf(
        '<pagelink href="%s" type="end" caption="%s" />'.LF,
        papaya_strings::escapeHTMLChars($this->_getLinkWithParams($params)),
        papaya_strings::escapeHTMLChars($this->_moduleConfiguration['caption_last'])
      );
    }

    $result .= '</pages>'.LF;
    return $result;
  }

  /**
  * Get XML Categorynavigation
  *
  * @access public
  * @return string $string XML
  */
  function getXMLNavigation() {
    $string = '';
    //get Navigation
    if (isset($this->categsById) && is_array($this->categsById)) {
      $string .= '<pathnavi>'.LF;
      foreach ($this->categsById as $categ) {
        $string .= sprintf(
          '<categ title="%s" href="%s" />'.LF,
          papaya_strings::escapeHTMLChars($categ['forumcat_title']),
          papaya_strings::escapeHTMLChars(
            $this->_getLinkWithParams(array('categ_id' => $categ['forumcat_id']))
          )
        );
      }
      $string .= '</pathnavi>'.LF;
    }
    return $string;
  }

  /**
  * Returns the index of a specific entry within the currently
  * loaded entryTree by traversing the tree down.
  *
  * @param integer $entryId
  * @return Offset.
  */
  function entryOffsetInTree($entryId) {
    $this->currentIndex = 0;
    if (isset($this->entryTree) && is_array($this->entryTree)) {
      $this->searchEntryInTree($entryId);
    }
    return $this->currentIndex;
  }

  /**
  * Recursive method, called by entryOffsetInTree.
  * This method parses down the thread tree looking for a specific
  * entry or thread id. If successfull the index of the
  * searched entry within the thread tree is found in the
  * class variable $currentIndex
  *
  * @param integer $entryId EntryId to look for
  * @param integer $threadId ThreadId where to start to search.
  * @return TRUE or FALSE depending rather entryId has been found or not.
  */
  function searchEntryInTree($entryId, $threadId = 0) {
    if ($entryId == $threadId) {
      return TRUE;
    }

    $this->currentIndex++;
    if (isset($this->entryTree[$threadId]) &&
        is_array($this->entryTree[$threadId])) {
      foreach ($this->entryTree[$threadId] as $subThreadId) {
        if ($this->searchEntryInTree($entryId, $subThreadId)) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

}