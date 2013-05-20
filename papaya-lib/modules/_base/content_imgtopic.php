<?php
/**
* Page module - article with image
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: content_imgtopic.php 38177 2013-02-25 16:03:55Z weinert $
*/

/**
* Basic class Page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Page module - article with image
*
* @package Papaya-Modules
* @subpackage _Base
*/
class content_imgtopic extends base_content {

  public $cacheable = TRUE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0
    ),
    'title' => array('Title', 'isSomeText', TRUE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 10, '', ''),
    'image' => array('Image', 'isSomeText', FALSE, 'image', 400, '', ''),
    'imgalign' => array('Image align', 'isAlpha', FALSE, 'combo',
      array('left' => 'left', 'right' => 'right', 'top' => 'top', ), '', 'left'),
    'breakstyle' => array('Text float', 'isAlpha', TRUE, 'combo',
      array('none' => 'None', 'side' => 'Side'), '', 'none'),
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
    $keys = array('teaser', 'text');
    $this->prepareFilterData($keys);
    return parent::getData();
  }

  /**
  * Get parsed data
  *
  * @access public
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
      '<teaser>%s</teaser>'.LF,
      $this->getXHTMLString(
        $this->applyFilterData($this->data['teaser']),
        !((bool)$this->data['nl2br'])
      )
    );
    if (!empty($this->data['image'])) {
      $result .= sprintf(
        '<image align="%s" break="%s">%s</image>'.LF,
        papaya_strings::escapeHTMLChars($this->data['imgalign']),
        papaya_strings::escapeHTMLChars($this->data['breakstyle']),
        $this->getPapayaImageTag($this->data['image'])
      );
    }
    $result .= sprintf(
      '<text>%s</text>',
      $this->getXHTMLString(
        $this->applyFilterData($this->data['text']),
        !((bool)$this->data['nl2br'])
      )
    );
    $result .= $this->getFilterData($parseParams);
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
    $dom = new PapayaXmlDocument();
    $teaser = $dom->appendElement('teaser');
    $teaser->appendElement('title', array(), $this->data['title']);
    $teaser->appendElement('subtitle', array(), $this->data['subtitle']);
    $teaser->appendElement('text')->appendXml(
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    if (!empty($this->data['image'])) {
      $teaser
        ->appendElement(
          'image',
          array(
            'align' => $this->data['imgalign'],
            'break' => $this->data['breakstyle']
          )
        )
        ->appendXml($this->getPapayaImageTag($this->data['image']));
    }
    return $teaser->saveFragment();
  }
}

?>