<?php
/**
* A listview column represent one part of the column header in a {@see PapayaUiListview}.
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
* @version $Id: Column.php 35729 2011-05-09 12:58:54Z weinert $
*/

/**
* A listview column represent one part of the column header in a {@see PapayaUiListview}.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property integer $align
* @property string|PapayaUiString $caption
*/
class PapayaUiListviewColumn extends PapayaUiControlCollectionItem {

  /**
  * Current caption value
  *
  * @var string|PapayaUiString
  */
  protected $_caption = '';

  /**
  * Current alignment value
  *
  * @var integer
  */
  protected $_align = PapayaUiOptionAlign::LEFT;

  /**
  * Allow to assign the internal (protected) variables using a public property
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'align' => array('getAlign', 'setAlign'),
    'caption' => array('_caption', '_caption')
  );

  /**
  * Initialize object and set standard values.
  *
  * @param string|PapayaUiString $caption
  * @param integer $align
  */
  public function __construct($caption, $align = PapayaUiOptionAlign::LEFT) {
    $this->_caption = $caption;
    $this->setAlign($align);
  }

  /**
  * Set the alignment if it is valid throw an exception if not.
  *
  * @throws InvalidArgumentException
  * @param integer $align
  */
  public function setAlign($align) {
    PapayaUiOptionAlign::validate($align);
    $this->_align = $align;
  }

  /**
  * Read the current alignment
  *
  * @return integer
  */
  public function getAlign() {
    return $this->_align;
  }

  /**
  * Append column xml to parent node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $itemNode = $parent->appendElement(
      'col',
      array(
        'align' => PapayaUiOptionAlign::getString($this->_align)
      ),
      (string)$this->_caption
    );
  }
}