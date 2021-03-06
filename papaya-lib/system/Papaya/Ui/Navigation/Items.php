<?php
/**
* An navigation items list.
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
* @version $Id: Items.php 36709 2012-02-06 12:30:12Z weinert $
*/

/**
* An navigation items list.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiNavigationItems extends PapayaUiControlCollection {

  private $_reference = NULL;

  /**
  * Only PapayaUiDialogElement objects are allows in this list
  * @var string
  */
  protected $_itemClass = 'PapayaUiNavigationItem';

  protected $_tagName = 'links';

  /**
  * Getter/Setter for a reference subobject to create detail page links
  *
  * @param PapayaUiReference $reference
  * @return PapayaUiReference
  */
  public function reference(PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      $this->_reference = new PapayaUiReferencePage();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}