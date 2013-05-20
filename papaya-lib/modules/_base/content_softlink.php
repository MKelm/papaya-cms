<?php
/**
* page module - URL-forwarding
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
* @subpackage _Base
* @version $Id: content_softlink.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Basic class check conditions
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');
/**
* page module - URL-forwarding
*
* @package Papaya-Modules
* @subpackage _Base
*/
class content_softlink extends base_content {

  /**
  * Is cacheable?
  * @var boolean $cacheable
  */
  var $cacheable = FALSE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'page' => array('Page Id', 'isNum', TRUE, 'pageid', 5)
  );

  /**
  * Exchange topic in page object.
  *
  * @access public
  * @return boolean FALSE
  */
  function getParsedData() {
    if (isset($this->data['page']) && $this->data['page'] > 0 &&
        $this->data['page'] != $this->parentObj->topicId &&
        isset($GLOBALS['PAPAYA_PAGE'])) {
      $className = get_class($this->parentObj);
      $topicId = (int)$this->data['page'];
      if (!isset($GLOBALS['PAPAYA_PAGE_CURRENT_IDS'][$topicId])) {
        $GLOBALS['PAPAYA_PAGE_CURRENT_IDS'][$topicId] = TRUE;
        $this->linkedTopic = new $className();
        if ($this->linkedTopic->topicExists($topicId) &&
            $this->linkedTopic->loadOutput(
              $topicId,
              $GLOBALS['PAPAYA_PAGE']->requestData['language'],
              $GLOBALS['PAPAYA_PAGE']->versionDateTime
            )
           ) {
          if ($this->linkedTopic->checkPublishPeriod($topicId)) {
            if ($GLOBALS['PAPAYA_PAGE']->validateAccess($topicId)) {
              if ($GLOBALS['PAPAYA_PAGE']->mode == 'xml') {
                if (PAPAYA_DBG_XML_OUTPUT) {
                  $GLOBALS['PAPAYA_PAGE']->topicId = $topicId;
                  $GLOBALS['PAPAYA_PAGE']->topic = &$this->linkedTopic;
                  $GLOBALS['PAPAYA_PAGE']->filter = &$this->filter;
                  $GLOBALS['PAPAYA_PAGE']->layout->add(
                    $this->linkedTopic->parseContent(
                      TRUE,
                      isset($this->filter->data) ? $this->filter->data : NULL
                    ),
                    'content');
                  $GLOBALS['PAPAYA_PAGE']->layout->xml();
                } else {
                  $GLOBALS['PAPAYA_PAGE']->getError(
                    403,
                    'Access forbidden',
                    PAPAYA_PAGE_ERROR_ACCESS
                  );
                }
              } else {
                $viewId = $this->linkedTopic->getViewId();
                if ($viewId > 0) {
                  if ($this->filter =
                        $GLOBALS['PAPAYA_PAGE']->output->getFilter($viewId)) {
                    $GLOBALS['PAPAYA_PAGE']->topicId = $topicId;
                    $GLOBALS['PAPAYA_PAGE']->topic = &$this->linkedTopic;
                    $GLOBALS['PAPAYA_PAGE']->filter = &$this->filter;
                    $GLOBALS['PAPAYA_PAGE']->layout->add(
                      $this->linkedTopic->parseContent(
                        TRUE,
                        isset($this->filter->data) ? $this->filter->data : NULL
                      ),
                      'content');
                  } else {
                    $GLOBALS['PAPAYA_PAGE']->getError(
                      500,
                      'Output mode "'.
                        papaya_strings::escapeHTMLChars(
                          basename($GLOBALS['PAPAYA_PAGE']->mode)
                        ).'" for page #'.$topicId.' not found',
                      PAPAYA_PAGE_ERROR_OUTPUT
                    );
                  }
                } else {
                  $GLOBALS['PAPAYA_PAGE']->getError(
                    500,
                    'View "'.
                      papaya_strings::escapeHTMLChars(
                        basename($GLOBALS['PAPAYA_PAGE']->mode)
                      ).'" for page #'.$topicId.' not found',
                    PAPAYA_PAGE_ERROR_VIEW
                  );
                }
              }
            } else {
              $GLOBALS['PAPAYA_PAGE']->getError(
                403,
                'Access forbidden',
                PAPAYA_PAGE_ERROR_ACCESS
              );
            }
          } else {
            $GLOBALS['PAPAYA_PAGE']->getError(
              404,
              'Page not published',
              PAPAYA_PAGE_ERROR_PAGE_PUBLIC
            );
          }
        } else {
          $GLOBALS['PAPAYA_PAGE']->getError(
            404,
            'Page not found',
            PAPAYA_PAGE_ERROR_PAGE
          );
        }
      } else {
        $GLOBALS['PAPAYA_PAGE']->getError(
          500,
          'Page link recursion found.',
          PAPAYA_PAGE_ERROR_PAGE_RECURSION
        );
      }
    }
    return FALSE;
  }

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string
  */
  function getParsedTeaser() {
    if (isset($this->data['page']) && $this->data['page'] > 0 &&
        $this->data['page'] != $this->parentObj->topicId &&
        isset($GLOBALS['PAPAYA_PAGE'])) {
      $className = get_class($this->parentObj);
      $topicId = (int)$this->data['page'];
      $this->linkedTopic = new $className();
      if ($this->linkedTopic->topicExists($topicId) &&
          $this->linkedTopic->loadOutput(
            $topicId,
            $GLOBALS['PAPAYA_PAGE']->requestData['language'],
            $GLOBALS['PAPAYA_PAGE']->versionDateTime
          )
         ) {
        return $this->linkedTopic->parseContent(FALSE, NULL, FALSE);
      }
    }
    return '';
  }
}

?>