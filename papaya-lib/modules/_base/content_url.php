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
* @version $Id: content_url.php 32792 2009-10-30 17:50:54Z weinert $
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
class content_url extends base_content {

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
    'url' => array ('URL', 'isNoHTML', TRUE, 'pageid', 800,
      'Please input a page ID, a relative or an absolute URL.'),
    'with_params' => array(
      'Include parameters', '/0|1/', TRUE, 'yesno', 0,
      'Include the original parameters when redirecting to the target page.'
     ),
  );

  /**
  * Redirect to URL
  *
  * @access public
  */
  function getParsedData() {
    if (isset($this->data['url']) && trim($this->data['url']) != '' &&
        $this->data['url'] != $this->parentObj->topicId) {
      if (isset($this->data['with_params']) &&
          $this->data['with_params'] == 1 &&
          (int)$this->data['url'] > 0) {
        $href = $this->getAbsoluteURL(
          $this->getWebLink($this->data['url']).$this->encodeQueryString($_GET)
        );
      } else {
        $href = $this->getAbsoluteURL($this->data['url']);
      }
      $GLOBALS['PAPAYA_PAGE']->sendHTTPStatus(301);
      @header("Location: ".$href);
      printf(
        '<html><head><meta http-equiv="refresh" content="0; URL=%s"></head></html>',
        papaya_strings::escapeHTMLChars($href)
      );
      exit;
    }
  }
}

?>