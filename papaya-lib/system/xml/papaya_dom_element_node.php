<?php
/**
* wrapper for DOMElement to add valueOf()
* (it is not possbile to emulate a property line nodeValue in PHP 4)
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage XML-DOM-Emulation
* @version $Id: papaya_dom_element_node.php 32351 2009-10-08 11:28:16Z weinert $
*/

/***
* wrapper for DOMElement to add valueOf()
* (it is not possbile to emulate a property line nodeValue in PHP 4)
*
* @package Papaya-Library
* @subpackage XML-DOM-Emulation
*/

class papaya_dom_element_node extends DOMElement {

  /**
  * return node value
  *
  * @access public
  * @return string
  */
  function valueOf() {
    return $this->nodeValue;
  }

  /**
  * get all attributes in a simple array
  *
  * @access public
  * @return array
  */
  function getAttributesArray() {
    if ($this->hasAttributes() && $this->attributes->length > 0) {
      $result = array();
      for ($idx = 0; $idx < $this->attributes->length; $idx++) {
        $attribute = &$this->attributes->item($idx);
        $result[$attribute->name] = $attribute->value;
      }
      return $result;
    } else {
      return array();
    }
  }

  /**
  * this is the destrcutor in the emulation, we need it here to avoid error messages
  *
  * @access public
  */
  function free() {

  }
}

?>