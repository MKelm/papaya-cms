<?php
/**
* Page module - XHTML
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
* @version $Id: content_xhtml.php 32604 2009-10-14 15:38:08Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - XHTML
*
* @package Papaya-Modules
* @subpackage _Base
*/
class content_xhtml extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array ('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Papaya will apply your linebreaks to the output page.', 0),
    'text' => array ('Text', 'isSomeText', FALSE, 'textarea', 30, '', '')
  );

  /**
  * Get parsed data
  *
  * @access public
  * @param array | NULL $parseParams Parameters from output filter
  * @return string
  */
  function getParsedData($parseParams = NULL) {
    $this->setDefaultData();
    $result = sprintf(
      '<text>%s</text>',
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    return $result;
  }

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    $result = sprintf(
      '<title>%s</title>'.LF,
      $this->getXHTMLString($this->parentObj->topic['TRANSLATION']['topic_title'])
    );
    $teaser = str_replace("\r\n", "\n", strip_tags($this->data['text']));
    if (preg_match("/^(.+)([\n]{2})/sU", $teaser, $regs)) {
      $teaser = $regs[1];
    }
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($teaser, !((bool)$this->data['nl2br']))
    );
    return $result;
  }

}

?>