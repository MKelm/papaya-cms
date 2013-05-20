<?php
/**
* Page module link with teaser.
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
* @version $Id: content_teaserlink.php 34124 2010-04-28 15:25:38Z roman $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Page module link with teaser.
*
* This link comes with additional data which will be shown by another Category
*
* @package Papaya-Modules
* @subpackage _Base
*/
class content_teaserlink extends base_content {

  /**
  * Is cacheable?
  * @var boolean $cacheable
  */
  var $cacheable = FALSE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'url' => array('URL', 'isNoHTML', TRUE, 'input', 200,
      'Please enter an URL (like "http://www.google.de" or "pages/index.html").
      Otherwise you can enter a papaya-page id (like "42")', ''),
    'title' => array('Title', 'isSomeText', TRUE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
     'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 10, '', ''),
    'image' => array('Image', 'isSomeText', FALSE, 'image', 400, '', ''),
  );

  /**
  * Redirect to URL
  *
  * @access public
  * @param array | NULL $parseParams Parameters from output filter
  * @return string
  */
  function getParsedData($parseParams = NULL) {
    $this->setDefaultData();
    if (isset($this->data) && isset($this->data['url']) &&
        trim($this->data['url']) != '' &&
        $this->data['url'] != $this->parentObj->topicId) {
      $href = $this->getAbsoluteURL($this->data['url'], $this->data['title'], TRUE);
      $GLOBALS['PAPAYA_PAGE']->sendHTTPStatus(301);
      $GLOBALS['PAPAYA_PAGE']->sendHeader('Location: '.$href);
      printf(
        '<html><head><meta http-equiv="refresh" content="0; URL=%s"></head></html>',
        papaya_strings::escapeHTMLChars($href)
      );
      exit;
    }
    return '';
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
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode(papaya_strings::normalizeString($this->data['title'])),
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle encoded="%s">%s</subtitle>'.LF,
      rawurlencode(papaya_strings::normalizeString($this->data['subtitle'])),
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    if (!empty($this->data['image'])) {
      $result .= sprintf(
        '<image>%s</image>'.LF,
        $this->getPapayaImageTag($this->data['image'])
      );
    }
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    return $result;
  }

}

?>