<?php
/**
* Action box for FlvPlayer integration
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
* @version $Id: content_flvplayer.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Action box for FlvPlayer integration
*
* @package Papaya-Modules
* @subpackage Free-Include
*/
class content_flvplayer extends base_content {

  /**
  * Preview ?
  * @var boolean $preview
  */
  var $preview = TRUE;

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'), 'Apply linebreaks from input to the HTML output.', 0),
    'title' => array('Title', 'isSomeText', FALSE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 5, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10, '', ''),

    'file_id' => array('Movie File (flv)', '~^[a-z\d]{32}$~', TRUE, 'mediafile',
      32, 'Flash movie media ID', ''),
    'General',
    'width' => array('Player width', 'isNum', FALSE, 'input', 5, '', ''),
    'height' => array('Player height', 'isNum', FALSE, 'input', 5, '', ''),
    'displaywidth' => array('Display width', 'isNum', FALSE, 'input', 5, '', ''),
    'displayheight' => array('Display height', 'isNum', FALSE, 'input', 5, '', ''),
    'Player options',
    'autostart' => array('Start movie automatically', 'isNoHTML', FALSE, 'translatedcombo',
      array('false' => 'No', 'true' => 'Yes', 'muted' => 'Muted'), '', 'true'),
    'repeat' => array('Loop movie', 'isNoHTML', FALSE, 'translatedcombo',
      array('false' => 'No', 'true' => 'Yes'), '', 'false'),
    'bufferlength' => array('Buffer length', 'isNum', FALSE, 'input', 3,
      'in seconds', 3),
    'volume' => array('Initial volume', 'isNum', FALSE, 'input', 3, 'in percent', 80),

    'Player appearance',
    'backcolor' => array('Background Color', 'isHTMLColor', FALSE,
      'color', 7, 'RRGGBB', 'CCCCCC'),
    'frontcolor' => array('Front color', 'isHTMLColor', FALSE,
      'color', 7, 'RRGGBB', '000000'),
    'lightcolor' => array('Highlight color', 'isHTMLColor', FALSE,
      'color', 7, 'RRGGBB', 'FFFFFF'),
    'logo' => array('Channel logo', '~^[a-z\d]{32}$~', FALSE,
      'mediafile', 32, 'Displayed in the top right corner of the player', ''),

    'Controls',
    'showdigits' => array('Show play time', 'isNoHTML', FALSE, 'translatedcombo',
      array('false' => 'No', 'true' => 'Yes', 'total' => 'Total'), '', 'total'),
    'showicons' => array('Show overlay controls', 'isNoHTML', FALSE, 'translatedcombo',
      array('false' => 'No', 'true' => 'Yes'), '', 1),
    'showvolume' => array('Show volume control', 'isNoHTML', FALSE, 'translatedcombo',
      array('false' => 'No', 'true' => 'Yes'), '', 1),

    'Alternative texts',
    'noflash' => array('No Flash', 'isSomeText', FALSE, 'textarea', 5, '',
      'Please install the Adobe Flash Player!', '')
  );

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string $result
  */
  function getParsedTeaser() {
    $result .= sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      "<text>%s</text>".LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
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
    $result = '';
    // title
    $result .= sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    // subtitle
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );

    // text, checking whether we need to translate newlines to html tag
    $result .= sprintf(
      "<text>%s</text>".LF,
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );

    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
    $mediaDB = base_mediadb::getInstance();
    $logoFile = $mediaDB->getFile($this->data['logo']);

    $configuration = array(
      'displaywidth' => $this->data['displaywidth'],
      'displayheight' => $this->data['displayheight'],

      'autostart' => $this->data['autostart'],
      'bufferlength' => $this->data['bufferlength'],
      'repeat' => $this->data['repeat'],

      'showdigits' => $this->data['showdigits'],
      'showicons' => $this->data['showicons'],
      'showvolume' => $this->data['showvolume'],

      'backcolor' => ($this->data['backcolor'] != '')
        ? '0x'.substr($this->data['backcolor'], 1) : '',
      'frontcolor' => ($this->data['frontcolor'] != '')
        ? '0x'.substr($this->data['frontcolor'], 1) : '',
      'lightcolor' => ($this->data['lightcolor'] != '')
        ? '0x'.substr($this->data['lightcolor'], 1) : '',

      'logo' => (!empty($this->data['logo'])) ? $this->data['logo'] : ''
    );

    $result .= '<text>';
    $result .= $mediaDB->getFlvViewer(
      $this->data['file_id'],
      $this->data['width'],
      $this->data['height'],
      $this->data['noflash'],
      $this->data['backcolor'],
      $configuration
    );
    $result .= '</text>';
    return $result;
  }
}
?>