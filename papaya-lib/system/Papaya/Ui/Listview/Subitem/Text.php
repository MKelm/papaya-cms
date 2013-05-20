<?php
/**
* A simple listview subitem displaying text.
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
* @version $Id: Text.php 35586 2011-03-30 14:24:57Z weinert $
*/

/**
* A simple listview subitem displaying text.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property integer $align
* @property string|PapayaUiString $text
*/
class PapayaUiListviewSubitemText extends PapayaUiListviewSubitem {

  /**
  * buffer for text variable
  *
  * @var string|PapayUiString
  */
  protected $_text = '';

  /**
  * Allow to assign the internal (protected) variables using a public property
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'align' => array('getAlign', 'setAlign'),
    'text' => array('_text', '_text')
  );

  /**
  * Create subitem object, set text content and alignment.
  *
  * @param string|PapayaUiString $text
  * @param integer $align
  */
  public function __construct($text) {
    $this->_text = $text;
  }

  /**
  * Append subitem xml data to parent node.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->appendElement(
      'subitem',
      array(
        'align' => PapayaUiOptionAlign::getString($this->getAlign())
      ),
      (string)$this->_text
    );
  }
}