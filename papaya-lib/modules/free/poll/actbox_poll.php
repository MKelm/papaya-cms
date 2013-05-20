<?php
/**
* Poll box
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
* @version $Id: actbox_poll.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Function class poll
*/
require_once(dirname(__FILE__).'/base_poll.php');
/**
* poll box
*
* @package Papaya-Modules
* @subpackage Free-Poll
*/
class actionbox_poll extends base_actionbox {

  /**
  * Preview allowed ?
  * @var boolean $preview
  */
  var $preview = TRUE;

  /**
  * is chacheable ?
  * @var boolean $cacheable
  */
  var $cacheable = FALSE;

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'poll' => array('Category / Poll', 'isNoHTML', TRUE, 'function', 'getPollCombo', ''),
    'Optional Link',
    'poll_page_id' => array('Page ID', 'isNoHTML', FALSE, 'pageid', 100, ''),
    'linktext' => array('Caption', 'isNoHTML', FALSE, 'input', 40, '', 'more')
  );

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
        if (isset($this->pollObject->polls) && is_array($this->pollObject->polls)) {
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


  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $pollBoxObject = new base_poll();
    $pollBoxObject->module = &$this;
    return $pollBoxObject->getOutput($this->data, FALSE);
  }

}
?>