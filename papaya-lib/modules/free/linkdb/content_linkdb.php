<?php
/**
* Page module LinkDB
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
* @subpackage Free-LinkDatabase
* @version $Id: content_linkdb.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* base linkdb module
*/
require_once(dirname(__FILE__).'/output_linkdb.php');
/**
* Page module LinkDB
*
* @package Papaya-Modules
* @subpackage Free-LinkDatabase
*/
class content_linkdb extends base_content {

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.',
      0),
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 10, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10, '', ''),
    'categ' => array('Category', 'isNoHTML', TRUE, 'function', 'getLinkDbCombo', '', 0),
    'page splitting',
    'perpage' => array('Links per page', 'isNum', TRUE, 'input', 4, '', 30),
    'previoslink' => array('previous page', 'isNoHTML', TRUE, 'input', 100, '',
      'previous page'),
    'nextlink' => array('next page', 'isNoHTML', TRUE, 'input', 100, '', 'next page'),
    'permissions',
    'post_comments' => array('Post comments', 'isNum', TRUE, 'function',
      'getPermsCombo', '', 30),
    'call',
    'kind_of_call' => array('Call', 'isNum', TRUE, 'combo',
      array(
        0 => 'Default',
        1 => 'Current window and frame',
        2 => 'Current window and parent frame',
        4 => 'Current window and top frame',
        3 => 'New window'
      ), '', 0),
    'Descriptions',
    'nl2br_description' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML description output.',
      0)
  );

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
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      '<teaser>%s</teaser>'.LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)@$this->data['nl2br']))
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString(@$this->data['text'], !((bool)@$this->data['nl2br']))
    );
    $linkDatabaseObject = new output_linkdb();
    $linkDatabaseObject->module = &$this;
    $result .= $linkDatabaseObject->getOutput($this->data);
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
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)@$this->data['nl2br']))
    );
    return $result;
  }

  /**
  * Get faq combo
  *
  * @param string $name
  * @param array $field
  * @param array $data
  * @access public
  * @return string
  */
  function getLinkDbCombo($name, $field, $data) {
    $result = "";
    $this->linkDatabaseObject = new output_linkdb();
    $this->linkDatabaseObject->loadCategs();
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      $this->paramName,
      $name
    );
    $selected = ($data == 0) ? ' selected="selected"' : '';
    $result .= sprintf(
      '<option value="%d"%s>%s</option>'.LF,
      0,
      $selected,
      papaya_strings::escapeHTMLChars($this->linkDatabaseObject->_gt('Base'))
    );
    if (isset($this->linkDatabaseObject->categs) &&
        is_array($this->linkDatabaseObject->categs)) {
      $result .= $this->getCategSubTree(0, 0, $data);
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get category subtree
  *
  * @param integer $parent
  * @param integer $indent
  * @param array $data
  * @access public
  * @return string $result
  */
  function getCategSubTree($parent, $indent, $data) {
    $result = '';
    if (isset($this->linkDatabaseObject->categTree[$parent]) &&
        is_array($this->linkDatabaseObject->categTree[$parent])) {
      foreach ($this->linkDatabaseObject->categTree[$parent] as $id) {
        $result .= $this->getChildCategs($id, $indent, $data);
      }
    }
    return $result;
  }

  /**
  * Element of category tree
  * @param integer $id ID
  * @param integer $indent shifting
  * @return string $result String
  */
  function getChildCategs($id, $indent, $data) {
    $result = '';
    if (isset($this->linkDatabaseObject->categs[$id]) &&
        is_array($this->linkDatabaseObject->categs[$id])) {
      $title = "'".str_repeat('-', $indent * 4)."->";
      $title .= papaya_strings::escapeHTMLChars(
        $this->linkDatabaseObject->categs[$id]['linkcateg_title']
      );
      $selected = ($data == $this->linkDatabaseObject->categs[$id]['linkcateg_id'])
        ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%d"%s>%s</option>'.LF,
        $this->linkDatabaseObject->categs[$id]['linkcateg_id'],
        $selected,
        papaya_strings::escapeHTMLChars($title)
      );
      $result .= $this->getCategSubTree($id, $indent + 1, $data);
    }
    return $result;
  }
}

?>
