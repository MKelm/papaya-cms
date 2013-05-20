<?php
/**
* Seitenmodul Voting
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
* @subpackage Free-Poll
* @version $Id: content_poll.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basisklasse Seitenmodule
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Poll Funktionsklasse
*/
require_once(dirname(__FILE__).'/base_poll.php');

/**
* Seitenmodul Voting
*
* @package Papaya-Modules
* @subpackage Free-Poll
*/
class content_poll extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
       array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 1),
    'title' => array('Title', 'isSomeText', TRUE, 'input', 400, '',''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 30, '',''),
    'poll' => array('Category / Poll', 'isNoHTML', TRUE, 'function',
      'getPollCombo', ''),
    'Messages',
    'error_nopoll' => array('No poll', 'isNoHTML', TRUE, 'input', 400, '',
      'No poll defined.')
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result xml
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      "<text>%s</text>",
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    $poll = new base_poll();
    $poll->module = &$this;
    $result .= $poll->getOutput($this->data);
    return $result;
  }

  /**
  * Get poll combo
  *
  * @param string $name
  * @param array $field
  * @param array $data
  * @access public
  * @return string $result xml
  */
  function getPollCombo($name, $field, $data) {
    $this->pollObject = new base_poll();
    $this->pollObject->loadCategs();
    $this->pollObject->loadPolls();
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    @list($mode, $dataId) = explode(';', $data);
    if (isset($this->pollObject->categs) && is_array($this->pollObject->categs)) {
      foreach ($this->pollObject->categs as $id => $categ) {
        $categName = "[".$categ['categ_title']."] ";
        $selected = ($mode == 'categ' && $dataId == $categ['categ_id']) ?
          ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="categ;%d" %s>%s</option>'.LF,
          (int)$categ['categ_id'],
          $selected,
          papaya_strings::escapeHTMLChars($categName)
        );
        if (isset($this->pollObject->polls) &&
            is_array($this->pollObject->polls)) {
          foreach ($this->pollObject->polls as $id => $poll) {
            $selected = ($mode == 'poll' && $dataId == $poll['poll_id']) ?
              ' selected="selected"' : '';
            if ($poll['categ_id'] == $categ['categ_id']) {
              $result .= sprintf(
                '<option value="poll;%d" %s>%s</option>'.LF,
                (int)$poll['poll_id'],
                $selected,
                "&#160;&#160;&#160;&#160;'->".
                  papaya_strings::escapeHTMLChars($poll['question'])
              );
            }
          }
        }
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }
}
?>