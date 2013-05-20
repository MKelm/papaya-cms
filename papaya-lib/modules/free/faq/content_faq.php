<?php
/**
* Page module FAQ
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
* @subpackage Free-FAQ
* @version $Id: content_faq.php 36482 2011-12-08 17:05:00Z bphilipp $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Page module FAQ
*
* @package Papaya-Modules
* @subpackage Free-FAQ
*/
class content_faq extends base_content {

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'faq' => array(
      'Faq', 'isNoHTML', TRUE, 'function', 'getFaqCombo', '', 0
    ),
    'faq_group' => array(
      'Default FAQ group', 'isNoHTML', FALSE, 'function', 'getFaqGroupCombo', '', 0
    ),
    'use_short_answers' => array(
      'Short answers', 'isNum', TRUE, 'yesno', NULL, '', 1),
    'short_char_count' => array(
      'Answer char limit', 'isNum', TRUE, 'input', 10, '', 300
    ),
    'display_answers_in_group_list' => array(
      'Answers in group list?', 'isNum', TRUE, 'yesno', NULL, 'Display answers in group list.', 1),
    'Search options',
    'show_search' => array(
      'Search form on all pages', 'isNum', TRUE, 'yesno', '',
      'Show search form on every FAQ page.', 0
    ),
    'show_ansers_in_searchresults' => array(
      'Results include answers', 'isNum', TRUE, 'yesno', '',
      'The questions in the search results also include the answers.', 0
    ),
    'Texts',
    'nl2br' => array(
      'Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
        array(0 => 'Yes', 1 => 'No'),
      'Papaya will apply your linebreaks to the output page.', 1
    ),
    'title' => array('Title', 'isSomeText', TRUE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 10, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 20, '', ''),
    'Permissions',
    'post_comments' => array(
      'Post comments', 'isNum', TRUE, 'function', 'getPermsCombo', '', 30
    ),
    'Buttons',
    'search_button' => array(
      'Search Submit button', 'isNoHTML', TRUE, 'input', 100, '', 'Search',
    ),
    'comment_button' => array(
      'Comment submit button', 'isNoHTML', TRUE, 'input', 100, '', 'Submit',
    ),
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
      $this->getXHTMLString($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      $this->getXHTMLString($this->data['subtitle'])
    );
    $result .= sprintf(
      '<teaser>%s</teaser>'.LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    include_once(dirname(__FILE__).'/output_faq.php');
    $faq = new output_faq();
    $faq->module = &$this;
    if (isset($this->data['short_char_count'])) {
      $faq->shortCharCount = (int)$this->data['short_char_count'];
    }
    if (isset($this->data['use_short_answers'])) {
      $faq->useShortAnswers = ($this->data['use_short_answers'] == 0) ? FALSE : TRUE;
    }
    $faq->baseLink = $this->getBaselink($this->parentObj->topicId);
    $faq->tableTopics = $this->parentObj->tableTopics;
    $faq->tableTopicsTrans = $this->parentObj->tableTopicsTrans;
    $faq->contentLanguageId = $this->parentObj->getContentLanguageId();
    $result .= $faq->getOutput($this->data);
    return $result;
  }

  /**
  * Get parsed Teaser
  *
  * @access public
  * @return string
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    if (!empty($this->data['teaser']) != '') {
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
        $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
      );
      return $result;
    }
    return '';
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
  function getFaqCombo($name, $field, $data) {
    $result = '';
    include_once(dirname(__FILE__).'/base_faq.php');
    $this->faqObject = new base_faq();
    $this->faqObject->loadFaqs();
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    if (isset($this->faqObject->faqs) && is_array($this->faqObject->faqs)) {
      foreach ($this->faqObject->faqs as $id => $faq) {
        $selected = ($data == $faq['faq_id']) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%d" %s>%s</option>'.LF,
          (int)$faq['faq_id'],
          $selected,
          papaya_strings::escapeHTMLChars($faq['faq_title'])
        );
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get default faq group combo
  *
  * @param string $name
  * @param array $field
  * @param array $data
  * @access public
  * @return string
  */
  function getFaqGroupCombo($name, $field, $data) {
    $result = '';
    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    $result .= sprintf(
      '<option value="">%s</option>'.LF,
      papaya_strings::escapeHTMLChars($this->_gt('none'))
    );
    if (isset($this->data['faq']) && (int)$this->data['faq'] > 0) {
      $faqId = $this->data['faq'];
      include_once(dirname(__FILE__).'/base_faq.php');
      $this->faqObject = new base_faq();
      $this->faqObject->loadFaqGroups($faqId);
      if (isset($this->faqObject->faqGroups) && is_array($this->faqObject->faqGroups)) {
        foreach ($this->faqObject->faqGroups as $id => $faqGroup) {
          $selected = ($data == $faqGroup['faqgroup_id']) ? ' selected="selected"' : '';
          $result .= sprintf(
            '<option value="%d" %s>%s</option>'.LF,
            (int)$faqGroup['faqgroup_id'],
            $selected,
            papaya_strings::escapeHTMLChars($faqGroup['faqgroup_title'])
          );
        }
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }
}
?>
