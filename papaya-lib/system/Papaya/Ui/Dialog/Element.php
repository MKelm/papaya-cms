<?php
/**
* Superclass for dialog elements
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
* @version $Id: Element.php 36812 2012-03-08 22:53:15Z weinert $
*/

/**
* Superclass for dialog elements
*
* An dialog element can be a simple input field, a button or a complex element with several
* child elements.
*
* @package Papaya-Library
* @subpackage Ui
*/
abstract class PapayaUiDialogElement extends PapayaUiControlCollectionItem {

  /**
  * Collect filtered dialog input data into $this->_dialog->data()
  */
  public function collect() {
    return $this->collection()->hasOwner();
  }

  /**
  * Get the parameter name
  *
  * If the dialog has a parameter group this will generate an additional parameter array level.
  *
  * If the key is an array is will be converted to a string
  * compatible to PHPs parameter array syntax.
  *
  * @param string|array $key
  * @param boolean $withGroup
  * @return string
  */
  protected function _getParameterName($key, $withGroup = TRUE) {
    if ($withGroup &&
        $this->hasCollection() &&
        $this->collection()->hasOwner()) {
      $name = $this->collection()->owner()->getParameterName($key);
      $prefix = $this->collection()->owner()->parameterGroup();
      if (isset($prefix)) {
        $name->prepend($prefix);
      }
    } else {
      $name = new PapayaRequestParametersName($key);
    }
    return (string)$name;
  }
}
