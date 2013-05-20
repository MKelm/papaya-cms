<?php
/**
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
* @version $Id: actbox_quiz.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Function class quiz
*/
require_once(dirname(__FILE__).'/base_quiz.php');

/**
* Action box quiz
*
* @package Papaya-Modules
* @subpackage Free-Quiz
*/
class actionbox_quiz extends base_actionbox {

  /**
  * Preview allowed?
  * @var boolean $preview
  */
  var $preview = TRUE;
  /**
  * Language Select
  * @var array $lngSelect
  */
  var $lngSelect = NULL;

  /**
  * Quiz objeckt
  * @var array $quizObject
  */
  var $quizObject = NULL;

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'pr';

  /**
  * edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'quiz' => array('Quiz', 'isNoHTML', TRUE, 'function', 'getQuizCombo', '', '')
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string $str xml
  */
  function getParsedData() {
    $this->setDefaultData();
    $this->quizObject = new base_quiz();
    $this->quizObject->module = &$this;
    $this->quizObject->initialize();
    $str = $this->quizObject->getBoxOutput($this, $this->data);
    return $str;
  }

  /**
  * Get quiz combo for content selection
  *
  * @param string $name
  * @param array $field
  * @param array $data
  * @access public
  * @return string xml
  */
  function getQuizCombo($name, $field, $data) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $this->lngSelect = &base_language_select::getInstance();
    $lngId = $this->lngSelect->currentLanguageId;
    $this->quizObject = new base_quiz();
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
  * @return string xml
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
  * @return string xml
  */
  function getQuizComboEntry($id, $indent, $data) {
    $result = '';
    if (isset($this->quizObject->groups[$id]) &&
        is_array($this->quizObject->groups[$id])) {
      $group = $this->quizObject->groups[$id];
      if ($indent > 0) {
        $indentString = "'".str_repeat('-', $indent).'->';
      } else {
        $indentString = '';
      }
      $title = $this->quizObject->groups[$id]['groupdetail_title'];
      if (!isset($title) || $title == '') {
        $title = 'No Title for this Language';
      }
      $selected = ($data == $id) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<option value="%d" %s>%s %s</option>'.LF,
        (int)$id,
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