<?php
/**
* emulation for DOMAttr
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
* @version $Id: expat_attribute_node.php 32349 2009-10-08 11:06:37Z weinert $
*/

/**
* emulation for DOMAttr
*
* @package Papaya-Library
* @subpackage XML-DOM-Emulation
*/
class expat_attribute_node extends expat_node {

  /**
  * dom node type
  * @var integer
  */
  var $nodeType = XML_ATTRIBUTE_NODE;

  /**
  * attribute name
  * @var string
  */
  var $name = '';

  /**
  * attribute value
  * @var string
  */
  var $value = '';

  /**
  * Constructor
  *
  * @param expat_element_node &$parentNode
  * @param string $nodeValue content
  * @access public
  */
  function __construct(&$parentNode, $name, $value) {
    $this->parentNode = &$parentNode;
    $this->name = $name;
    $this->value = $value;
    if (isset($this->parentNode) && is_object($this->parentNode)) {
      $this->parentNode->_appendAttribute($this);
    }
  }

  /**
  * Constuctor PHP 4
  *
  * @param expat_element_node &$parent
  * @param string $text content
  * @access public
  */
  function expat_attribute_node(&$parentNode, $name, $value) {
    $this->__construct($parentNode, $name, $value);
  }

  /**
  * clear node data
  * @return string
  */
  function free() {
    unset($this->name);
    unset($this->value);
  }
}
?>