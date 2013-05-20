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
* @version $Id: actbox_pagelink.php 34957 2010-10-05 15:57:41Z weinert $
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
class actionbox_pagelink extends base_actionbox {

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
    'caption' => array('Caption', 'isNoHTML', TRUE, 'input', 200, '', ''),
    'css_class' => array('CSS class', 'isNoHTML', FALSE, 'input', 20, '', ''),
    'page_id' => array('Page Id', 'isNum', TRUE, 'pageid', 10, '', 0),
    'robots_follow' => array('Robots', 'isNum', TRUE, 'combo',
      array(0 => 'nofollow', 1 => 'follow'), '', '0')
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    if (isset($this->parentObj)) {
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
      $class = '';
      if (isset($this->data['css_class']) && trim($this->data['css_class']) != '') {
        $class = sprintf(
          ' class="%s"',
          papaya_strings::escapeHTMLChars($this->data['css_class'])
        );
      }
      if ((!isset($this->data['robots_follow']))
          || ($this->data['robots_follow']) < 1) {
        $rel = ' rel="nofollow"';
      }

      return sprintf(
        '<a href="%s"%s%s>%s</a>',
        papaya_strings::escapeHTMLChars($href),
        $class,
        $rel,
        papaya_strings::escapeHTMLChars($this->data['caption'])
      );
    }
    return '';
  }
}
?>