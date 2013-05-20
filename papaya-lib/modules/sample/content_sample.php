<?php
/**
* Sample page (content) module
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
* @subpackage Sample
* @version $Id: content_sample.php 34695 2010-08-18 09:05:09Z weinert $
*/

/**
* page modules inherit from the base_content super class
* This does not include any database access, because not any module needs it.
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Sample page (content) module
*
* @package Papaya-Modules
* @subpackage Sample
*/
class content_sample extends base_content {

  /**
  * The edit fields definition is used to create a dialog in the backend,
  * The input will go into the "data" property of this object
  * You can group the fields using subtitles.
  * @var array $editFields
  */
  var $editFields = array(
    /* the nl2br field is used to activate a conversion of text linebreaks
     * to (x)html linbreak tags */
    'nl2br' => array(
      'Automatic linebreak', //field caption
      'isNum', //check function (from checkit class)
      FALSE, //mandatory field?
      'translatedcombo', //field type
      array(0 => 'Yes', 1 => 'No'), //field type parameters
      'Apply linebreaks from input to the XML output.', //hint/help string
      0 //default value
    ),
    /* You can group the fields using subtitles */
    'Sample group',
    /* define a title field that can not contain html,
       is mandatory, shows as an input field with a 500 characters maximum,
       has no hint and contains "Hello World!" by default
    */
    'title' => array(
      'Title', 'isNoHTML', TRUE, 'input', 500, '', 'Hello World!'
    ),
    /* define a text field that has to contain some text (not only special chars),
       is not mandatory, shows as an textarea with a 10 lines,
       has no hint and is empty by default
    */
    'text' => array(
      'Text', 'isSomeText', FALSE, 'textarea', 10, '', ''
    )
  );

  /**
  * The getParsedData() method is called by the page controller to get the content for a whole page.
  * The return value has to be an empty string or wellformed xml (it does not need an root tag).
  * It will be put into the <topic> tag of the page xml output.
  *
  * @param array $params Parameters provided by the output filter
  * @access public
  * @return string
  */
  function getParsedData($params) {
    /* setDefaultData() initializes the default data for undefined data fields
     * from the edit fields definition */
    $this->setDefaultData();
    /* escape special chars in the title and add some xml to the result */
    $result = sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    /* make sure that the text content will not break the output xml, convert
     * linebreaks and add it to the result */
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['text'], !$this->data['nl2br'])
    );
    return $result;
  }

  /**
  * The getParsedTeaser method is called by the page controller to get content for a teaser
  * (in a category or box)
  *
  * This implementation returns an empty string, so the page will be hidden in teaser lists
  *
  * @access public
  * @return string
  */
  function getParsedTeaser() {
    return '';
  }
}
?>
