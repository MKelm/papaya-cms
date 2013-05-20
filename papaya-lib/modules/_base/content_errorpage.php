<?php
/**
* Page module - article with error data (404, 500, ...)
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
* @version $Id: content_errorpage.php 32604 2009-10-14 15:38:08Z weinert $
*/

/**
* Basic class Page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Page module - article with error data (404, 500, ...)
*
* @package Papaya-Modules
* @subpackage _Base
*/
class content_errorpage extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0),
    'title' => array('Title', 'isSomeText', TRUE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 30, '', '')
  );

  /**
  * Write data array to xml string
  *
  * @access public
  * @return string $result XML-data string
  * @see base_plugin::getData()
  */
  function getData() {
    $keys = array('text');
    $this->prepareFilterData($keys);
    return parent::getData();
  }

  /**
  * Get parsed data
  *
  * @access public
  * @param array | NULL $parseParams parameters from output filter
  * @return string
  */
  function getParsedData($parseParams = NULL) {
    $this->setDefaultData();
    $this->loadFilterData();
    $result = sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      '<text>%s</text>',
      $this->getXHTMLString(
        $this->applyFilterData($this->data['text']),
        !((bool)$this->data['nl2br'])
      )
    );
    $result .= $this->getFilterData($parseParams);

    if (isset($GLOBALS['PAPAYA_PAGE']) &&
        is_object($GLOBALS['PAPAYA_PAGE'])) {
      if (isset($GLOBALS['PAPAYA_PAGE']->error) &&
          is_array($GLOBALS['PAPAYA_PAGE']->error)) {
        $result .= sprintf(
          '<papaya-error status="%d" code="%d">%s</papaya-error>',
          (int)$GLOBALS['PAPAYA_PAGE']->error['status'],
          (int)$GLOBALS['PAPAYA_PAGE']->error['code'],
          papaya_strings::escapeHTMLChars($GLOBALS['PAPAYA_PAGE']->error['message'])
        );
      } elseif (isset($GLOBALS['PAPAYA_PAGE']->redirect) &&
                is_array($GLOBALS['PAPAYA_PAGE']->redirect)) {
        $result .= sprintf(
          '<papaya-redirect status="%d">%s</papaya-redirect>',
          (int)$GLOBALS['PAPAYA_PAGE']->error['status'],
          papaya_strings::escapeHTMLChars($GLOBALS['PAPAYA_PAGE']->redirect['url'])
        );

      } else {
        $result .= sprintf(
          '<papaya-error status="0" code="0">SAMPLE</papaya-error>'
        );
        $result .= sprintf(
          '<papaya-redirect status="0">http://sample.tld/</papaya-redirect>'
        );
      }
    }
    return $result;
  }
}

?>