<?php
/**
* Input checks
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
* @subpackage Free-Include
* @version $Id: content_frame.php 32587 2009-10-14 15:09:03Z weinert $
*/

/**
* Basisklasse Seitenmodule
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Input checks
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* IFrame embed
*
* @package Papaya-Modules
* @subpackage Free-Include
*/
class content_frame extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'IFrame',
    'url' => array('URL', 'isNoHTML', TRUE, 'input', 800,
      'Please input a relative or an absolute URL.', ''),
    'height' => array('Height', 'isNum', TRUE, 'input', 4,
      'Iframe height, width will be set in the template.', 300),
    'width' => array('Width', 'isNum', TRUE, 'input', 4,
      'Iframe width, width will be set in the template. (optional)'),
    'scrollbars' => array('Scrollbars', 'isNoHTML', FALSE, 'translatedcombo',
      array('auto' => 'Auto', 'no' => 'No', 'yes' => 'Yes', '' => 'Default behaviour'),
      'Scrollbar behaviour', ''),
    'Content',
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0),
    'title' => array('Title', 'isSomeText', TRUE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 10, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 20, '', '')
  );

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    $result = sprintf(
      '<title>%s</title>'.LF,
      $this->getXHTMLString($this->data['title'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString(
      $this->data['teaser'],
      !((bool)$this->data['nl2br']))
    );
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

    //set IFrame data
    $prefix = substr($this->data['url'], 0, 6);
    if ( $prefix != "http:/" && $prefix != "https:" ) {
      $url = papaya_strings::escapeHTMLChars($this->getAbsoluteURL($this->data['url'], '', TRUE));
    } else {
      $url = $this->data['url'];
    }
    $result = sprintf(
      '<url>%s</url>',
      $url
    );
    $result .= sprintf(
      '<height>%d</height>',
      $this->data['height']
    );

    if (!empty($this->data['width'])) {
      $result .= sprintf(
        '<width>%d</width>',
        $this->data['width']
      );
    }

    if (!empty($this->data['scrollbars'])) {
      $result .= sprintf(
        '<scrollbars>%s</scrollbars>',
        $this->data['scrollbars']
      );
    }

    //set content data
    $result .= sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      $this->getXHTMLString($this->data['title'])
    );
    if (!empty($this->data['subtitle'])) {
      $result .= sprintf(
        '<subtitle>%s</subtitle>'.LF,
        $this->getXHTMLString($this->data['subtitle'])
      );
    }
    if (!empty($this->data['text'])) {
      $result .= sprintf(
        '<text>%s</text>'.LF,
        $this->getXHTMLString(
        $this->data['text'],
        !((bool)$this->data['nl2br']))
      );
    }
    return $result;
  }
}

?>
