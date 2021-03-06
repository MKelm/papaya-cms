<?php
/**
* A menu/toolbar button with image and/or text.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Button.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* A menu/toolbar button with image and/or text.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property PapayaUiReference $reference
* @property string|PapayaUiString $caption
* @property string|PapayaUiString $hint
* @property boolean $selected
* @property string $accessKey
* @property string $target
*/
class PapayaUiToolbarButton extends PapayaUiToolbarElement {

  /**
  * Image or image index.  The button needs a cpation or/and an image.
  *
  * @var string
  */
  protected $_image = '';

  /**
  * Button caption. The button needs a caption or/and an image
  *
  * @var string|PapayaUiString
  */
  protected $_caption = '';

  /**
  * Button quickinfo
  *
  * @var string|PapayaUiString
  */
  protected $_hint = '';

  /**
  * The access key define the key for a browser shortcut. The real shortcut depends on
  * the browser
  *
  * @var string
  */
  protected $_accessKey = '';

  /**
  * If the button is selected/down
  *
  * @var boolean
  */
  protected $_selected = FALSE;

  /**
  * Link target
  *
  * @var string
  */
  protected $_target = '_self';

  /**
  * Define public properties.
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'reference' => array('reference', 'reference'),
    'image' => array('_image', '_image'),
    'caption' => array('_caption', '_caption'),
    'hint' => array('_hint', '_hint'),
    'selected' => array('_selected', '_selected'),
    'accessKey' => array('_accessKey', 'setAccessKey'),
    'target' => array('_target', '_target')
  );

  /**
  * Setter for access key character.
  *
  * @param string $key
  */
  public function setAccessKey($key) {
    PapayaUtilConstraints::assertString($key);
    if (strlen($key) == 1) {
      $this->_accessKey = $key;
    } else {
      throw new InvalidArgumentException(
        'InvalidArgumentException: Access key must be an single character.'
      );
    }
  }

  /**
  * Append button xml to menu. The button needs at least a caption or image to be shown.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $image = $this->papaya()->images[(string)$this->_image];
    $caption = (string)$this->_caption;
    if (!(empty($image) && empty($caption))) {
      $button = $parent->appendElement(
        'button',
        array(
          'href' => $this->reference()->getRelative(),
          'target' => $this->_target
        )
      );
      if (!empty($image)) {
        $button->setAttribute('glyph', $image);
      }
      if (!empty($caption)) {
        $button->setAttribute('title', $caption);
      }
      if (!empty($this->_accessKey)) {
        $button->setAttribute('accesskey', $this->_accessKey);
      }
      $hint = (string)$this->_hint;
      if (!empty($hint)) {
        $button->setAttribute('hint', $hint);
      }
      if ((bool)$this->_selected) {
        $button->setAttribute('down', 'down');
      }
    }
  }
}