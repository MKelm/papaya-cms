<?php
/**
* A simple listview subitem displaying an image.
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
* @version $Id: Image.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* A simple listview subitem displaying an image.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property integer $align
* @property string $image
* @property string|PapayaUiString $hint
* @property array $actionParameters
* @property PapayaUiReference $reference
*/
class PapayaUiListviewSubitemImage extends PapayaUiListviewSubitem {

  /**
  * buffer for image index or filename
  *
  * @var string
  */
  protected $_image = '';
  /**
  * buffer for text variable
  *
  * @var string|PapayUiString
  */
  protected $_hint = '';

  /**
  * Basic reference/link
  *
  * @var PapayaUiReference
  */
  protected $_reference = NULL;

  /**
  * Allow to assign the internal (protected) variables using a public property
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'align' => array('getAlign', 'setAlign'),
    'image' => array('_image', '_image'),
    'hint' => array('_hint', '_hint'),
    'actionParameters' => array('_actionParameters', 'setActionParameters'),
    'reference' => array('reference', 'reference')
  );

  /**
  * Create subitem object, set text content and alignment.
  *
  * @param string|PapayaUiString $text
  * @param integer $align
  */
  public function __construct($image, $hint = '', array $actionParameters = NULL) {
    $this->_image = $image;
    $this->_hint = $hint;
    $this->setActionParameters($actionParameters);
  }

  /**
  * Getter/Setter for the reference subobject, this will be initalized from the listview
  * if not set.
  *
  * @param PapayaUiReference $reference
  * @return PapayaUiReference
  */
  public function reference(PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    }
    if (is_null($this->_reference)) {
      // directly return the reference, so it is possible to recognice if it was set.
      return $this->collection()->getListview()->reference();
    }
    return $this->_reference;
  }

  /**
  * Append subitem xml data to parent node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $subitem = $parent->appendElement(
      'subitem',
      array(
        'align' => PapayaUiOptionAlign::getString($this->getAlign())
      )
    );
    if (!empty($this->_image)) {
      $glyph = $subitem->appendElement(
        'glyph',
        array(
          'src' => $this->papaya()->images[(string)$this->_image]
        )
      );
      $hint = (string)$this->_hint;
      if (!empty($hint)) {
        $glyph->setAttribute('hint', $hint);
      }
      if (!empty($this->_actionParameters)) {
        $glyph->setAttribute('href', $this->getUrl());
      }
    }
  }

  /**
  * Use the action parameter and the reference from the items to get an url for the output xml.
  *
  * If you assigned a reference object the action parameters will be applied without an additional
  * parameter group. If the reference is fetched from the listview, the listview parameter group
  * will be used.
  *
  * @return string
  */
  private function getUrl() {
    $reference = clone $this->reference();
    if (isset($this->_reference)) {
      $reference->setParameters($this->_actionParameters);
    } else {
      $reference->setParameters(
        $this->_actionParameters,
        $this->collection()->getListview()->parameterGroup()
      );
    }
    return $reference->getRelative();
  }
}