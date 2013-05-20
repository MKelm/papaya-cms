<?php
/**
* Quiz Content Page
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
* @subpackage Free-Quiz
* @version $Id: content_quiz.php 37881 2012-12-19 10:37:20Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Function class quiz
*/
require_once(dirname(__FILE__).'/base_quiz.php');

/**
* Quiz Content Page
*
* @package Papaya-Modules
* @subpackage Free-Quiz
*/
class content_quiz extends base_content {
  /**
  * edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Papaya will apply your linebreaks to the output page.',
      0),
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 255, '', ''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 5, '', ''),
    'quiz' => array('Quiz', 'isNoHTML', TRUE, 'function', 'getQuizCombo', '', ''),
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result xml
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = '';
    $this->quizObject = new base_quiz();
    $this->quizObject->module = &$this;
    $this->quizObject->initialize();
    $result = sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<teaser>%s</teaser>'.LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)@$this->data['nl2br']))
    );
    $result .= $this->quizObject->getContentOutput($this, $this->data);
    return $result;
  }

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string $result or ''
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    if (@trim($this->data['teaser']) != '') {
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
    return '';
  }

  /**
  * Get quiz combo for content selection
  *
  * @param string $name
  * @param array $field
  * @param array $data
  * @access public
  * @return string $result xml
  */
  function getQuizCombo($name, $field, $data) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $this->lngSelect = &base_language_select::getInstance();
    $lngId = $this->lngSelect->currentLanguageId;
    $this->quizObject = new base_quiz('cqz'); // content quiz
    $this->quizObject->loadGroupTree($lngId);
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    if (isset($this->quizObject->groups) && is_array($this->quizObject->groups)) {
      $result .= $this->getQuizComboSubTree(0, 0, $data);
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get quiz combo sub tree
  *
  * @param integer $parent
  * @param integer $indent
  * @param array $data
  * @access public
  * @return string $result xml
  */
  function getQuizComboSubTree($parent, $indent, $data) {
    $result = '';
    if (isset($this->quizObject->groupTree[$parent]) &&
        is_array($this->quizObject->groupTree[$parent])) {
      foreach ($this->quizObject->groupTree[$parent] as $id) {
        $result .= $this->getQuizComboEntry($id, $indent, $data);
      }
    }
    return $result;
  }

  /**
  * Get quiz combo entry
  *
  * @param integer $id
  * @param integer $indent
  * @param array $data
  * @access public
  * @return string $result xml
  */
  function getQuizComboEntry($id, $indent, $data) {
    $result = '';
    if (isset($this->quizObject->groups[$id]) && is_array($this->quizObject->groups[$id])) {
      $group = $this->quizObject->groups[$id];
      if ($indent > 0) {
        $indentString = "'".str_repeat('-', $indent).'->';
      } else {
        $indentString = '';
      }
      $title = $this->quizObject->groups[$id]['groupdetail_title'];
      if (!isset($title) || $title == '') {
        $title = 'No title for this language';
      }
      $selected = ($data == $id) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%d" %s>%s %s</option>'.LF,
        $id,
        $selected,
        papaya_strings::escapeHTMLChars($indentString),
        papaya_strings::escapeHTMLChars($title)
      );
      $result .= $this->getQuizComboSubTree($id, $indent + 1, $data);
    }
    return $result;
  }
}

?>