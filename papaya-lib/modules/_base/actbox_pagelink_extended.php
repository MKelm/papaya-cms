<?php
/**
* Action box page link
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
* @version $Id: actbox_pagelink_extended.php 32604 2009-10-14 15:38:08Z weinert $
*/

/**
* Basic class Aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box page link - generates a link including the current page as referer
*
* @package Papaya-Modules
* @subpackage _Base
*/
class actionbox_pagelink_extended extends base_actionbox {

  /**
  * Is the output cacheable?
  * @var boolean $cacheable
  */
  var $cacheable = FALSE;

  /**
  * Preview allowed?
  * @var boolean $preview
  */
  var $preview = FALSE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'caption' => array('Link Caption', 'isNoHTML', TRUE, 'input', 200, '', ''),
    'text_before' => array('Text before', 'isSomeText', FALSE, 'textarea', 5, '', ''),
    'text_after' => array('Text after', 'isSomeText', FALSE, 'textarea', 5, '', ''),
    'page_id' => array('Page Id', 'isNum', TRUE, 'pageid', 10, '', 0),
    'Popup',
    'use_popup' => array(
      'Use Popup', 'isNum', TRUE, 'combo', array(0 => 'no', 1 => 'yes'), '', 0
    ),
    'popup_name' => array('Window Name', 'isNoHTML', FALSE, 'input', 200, '', 'papaya_popup'),
    'popup_width' => array('Width', 'isNum', FALSE, 'input', 200, '', 600),
    'popup_height' => array('Height', 'isNum', FALSE, 'input', 200, '', 600),
    'popup_bars' => array(
      'Scollbars', 'isNum', FALSE, 'combo', array(0 => 'no', 1 => 'yes'), '', 0),
    'popup_resize' => array(
      'Resizable', 'isNum', FALSE, 'combo', array(0 => 'no', 1 => 'yes'), '', 0),
    'popup_toolbar' => array(
      'Show Toolbar', 'isNum', FALSE, 'combo', array(0 => 'no', 1 => 'yes'), '', 0),
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = '';
    if (isset($this->parentObj)) {
      $result .= '<extended_pagelink>';
      $params['refpage'] = $this->parentObj->topicId;
      if (isset($_SERVER['QUERY_STRING']) &&
          trim($_SERVER['QUERY_STRING']) != '') {
        $params['urlparams'] = urldecode($_SERVER['QUERY_STRING']);
      }
      $href = $this->getWebLink(
        (int)$this->data['page_id'],
        NULL,
        NULL,
        $params,
        NULL,
        $this->data['caption']
      );
      $result .= sprintf(
        '<link href="%s">%s</link>',
        papaya_strings::escapeHTMLChars($href),
        papaya_strings::escapeHTMLChars($this->data['caption'])
      );
      if (isset($this->data['use_popup']) && $this->data['use_popup']) {
        $result .= sprintf(
          '<popup href="%s" name="%s" width="%d" height="%d"'.
          ' scrollbars="%d" resizeable="%d" toolbar="%d" text="%s" />'.LF,
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($this->data['popup_name']),
          (int)$this->data['popup_width'],
          (int)$this->data['popup_height'],
          (int)$this->data['popup_bars'],
          (int)$this->data['popup_resize'],
          (int)$this->data['popup_toolbar'],
          papaya_strings::escapeHTMLChars($this->data['caption'])
        );
      }
      $result .= sprintf(
        '<text-before>%s</text-before>',
        papaya_strings::escapeHTMLChars($this->data['text_before'])
      );
      $result .= sprintf(
        '<text-after>%s</text-after>',
        papaya_strings::escapeHTMLChars($this->data['text_after'])
      );
      $result .= '</extended_pagelink>';
    }
    return $result;
  }
}
?>