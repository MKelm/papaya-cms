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
* @version $Id: actbox_flvplayer.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box for FlowPlayer integration
*
* @package Papaya-Modules
* @subpackage Free-Include
*/
class actionbox_flvplayer extends base_actionbox {

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
    'file_id' => array('Movie File (flv)', '~^[a-z\d]{32}$~', TRUE, 'mediafile',
      32, 'Flash movie media ID', ''),
    'General',
    'width' => array('Player width', 'isNum', FALSE, 'input', 5, '', ''),
    'height' => array('Player height', 'isNum', FALSE, 'input', 5, '', ''),
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
    'preview' => array('Preview image', '~^[a-z\d]{32}$~', FALSE,
      'mediafile', 32, '', ''),

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
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $result = '';
    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
    $mediaDB = base_mediadb::getInstance();

    $configuration = array(
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
      'logo' => (!empty($this->data['logo'])) ? $this->data['logo'] : '',
      'image' => (!empty($this->data['preview'])) ? $this->data['preview'] : '',
    );

    $result .= $mediaDB->getFlvViewer(
      $this->data['file_id'],
      $this->data['width'],
      $this->data['height'],
      $this->data['noflash'],
      $this->data['backcolor'],
      $configuration
    );

    return $result;
  }
}
?>