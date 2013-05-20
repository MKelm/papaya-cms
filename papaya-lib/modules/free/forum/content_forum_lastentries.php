<?php
/**
* Page to show last forum entries.
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Free-Forum
* @version $Id: content_forum_lastentries.php 35642 2011-04-06 12:06:54Z kelm $
*/

/**
* Base class for pages.
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Output / Base class for forum.
*/
require_once(dirname(__FILE__).'/output_forum.php');

/**
* Page to show last forum entries.
*
* @package Papaya-Modules
* @subpackage Free-Forum
*/
class content_forum_lastentries extends base_content {

  /**
  * Module parameter name
  * @var string
  */
  public $paramName = 'ff';

  /**
  * Content edit fields
  * @var array $editFields
  */
  public $editFields = array(
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, NULL, NULL),
    'forum' => array('Forum', 'isSomeText', TRUE, 'function', 'getForumCombo', NULL, NULL),
    'lastcount' => array('Show last entries', 'isNum', TRUE, 'input', 2, NULL, 30),
    'forumpage' => array('Forum page', 'isNum', TRUE, 'pageid', 5, NULL, 0),
    'linkname' => array(
      'Link name',
      'isNoHTML',
      TRUE,
      'input',
      50,
      'Pagename in links, e.g. default value "forum" makes a link like "forum.[id].html".',
      'forum'
    ),
    'text',
    'nl2br' => array(
      'Automatic linebreak',
      'isNum',
      FALSE,
      'yesno',
      NULL,
      'Apply linebreaks from input to the HTML output.',
      0
    ),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10, NULL, NULL),
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string $str
  */
  public function getParsedData() {
    $this->setDefaultData();

    $moduleConfiguration = $this->data;
    $moduleConfiguration['post_answers'] = FALSE;
    $moduleConfiguration['post_questions'] = FALSE;
    $moduleConfiguration['subscribe_threads'] = FALSE;

    include_once(dirname(__FILE__).'/output_forum.php');
    $forumOutput = new output_forum();
    $forumOutput->setPageData($this->data['linkname'], $this->data['forumpage']);
    $forumOutput->initialize($this, $moduleConfiguration);

    $result = sprintf(
      '%s<forum>'.LF.
      '<lastentries>'.LF.
      '<title encoded="%s">%s</title>'.LF.
      '<text>%s</text>'.LF,
      $forumOutput->getContentStatusXml(),
      rawurlencode($this->data['title']),
      papaya_strings::escapeHTMLChars($this->data['title']),
      $this->getXHTMLString(
        $this->applyFilterData($this->data['text']), !((bool)$this->data['nl2br'])
      )
    );

    $baseConfiguration = $forumOutput->decodeForumData($this->data['forum']);
    $result .= $forumOutput->getOutputLastEntries(
      $baseConfiguration['mode'] == 'categ' ? $baseConfiguration['id'] : NULL,
      $baseConfiguration['mode'] == 'forum' ? $baseConfiguration['id'] : NULL,
      NULL,
      $this->data['lastcount']
    );

    return $result.
      '</lastentries>'.LF.
      '</forum>'.LF;
  }

  /**
  * Get forum combo from output object.
  *
  * @param string $name
  * @param array $field
  * @param string $data
  * @return string xml
  */
  public function getForumCombo($name, $field, $data) {
    include_once(dirname(__FILE__).'/output_forum.php');
    $forumOutput = new output_forum();
    return $forumOutput->getForumCombo($name, $field, $data);
  }

}