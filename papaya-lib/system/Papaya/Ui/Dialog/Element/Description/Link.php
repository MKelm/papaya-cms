<?php
/**
* Dialog element description item encapsulationing a simple link.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Link.php 36779 2012-02-29 10:50:59Z weinert $
*/

/**
* Dialog element description item encapsulationing a simple link.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogElementDescriptionLink extends PapayaUiDialogElementDescriptionItem {

  private $_reference = NULL;

  /**
  * Append description element with href attribute to parent xml element.
  *
  * @param PapayaXmlElement $parent
  * @return PapayaXmlElement
  */
  public function appendTo(PapayaXmlElement $parent) {
    return $parent->appendElement(
      'link',
      array(
        'href' => $this->reference()->getRelative()
      )
    );
  }

  /**
  * Getter/Setter for the reference subobject.
  *
  * @param PapayaUiReference $reference
  */
  public function reference(PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new PapayaUiReference();
    }
    return $this->_reference;
  }
}