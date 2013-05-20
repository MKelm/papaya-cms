<?php
/**
* PHP Include
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
* @subpackage Free-Include
* @version $Id: content_include.php 32587 2009-10-14 15:09:03Z weinert $
*/

/**
* Basisklasse Seitenmodule
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Input checks
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* PHP Include
*
* @package Papaya-Modules
* @subpackage Free-Include
*/
class content_include extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'file' => array(
      'Filename', 'isFile', TRUE, 'input', 50, '', ''
    ),
    'Content',
    'nl2br' => array(
      'Automatic linebreak', 'isNum', FALSE, 'translatedcombo', array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0
    ),
    'title' => array('Title', 'isSomeText', TRUE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '',''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 10, '', '')
  );

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
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    return $result;
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    if (isset($this->data['file']) && trim($this->data['file']) != '') {
      ob_start();
      if (defined('PAPAYA_DBG_DEVMODE') && PAPAYA_DBG_DEVMODE) {
        $result = include($this->data['file']);
      } else {
        $result = @include($this->data['file']);
      }
      if (isset($result) && is_string($result) && $result != '') {
        ob_end_clean();
        return $result;
      }
      return ob_get_clean();
    } else {
      return '';
    }
  }
}

?>
