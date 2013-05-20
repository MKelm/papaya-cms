<?php
/**
* expat_nodemap, expat_nodemap_children and expat_nodemap_attributes
* are class interfaces to emulate ext/dom
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
* @version $Id: expat_nodemap.php 32574 2009-10-14 14:00:46Z weinert $
*/

/**
* abstract class: child classes emulate ext/dom DOMNodeMap
* @package Papaya-Library
* @subpackage XML-DOM-Emulation
*/
class expat_nodemap {

  /**
  * parent - the "real" node
  * @var simple_xmlelement object
  */
  var $parentNode = NULL;

  /**
  * child count
  * @var integer
  */
  var $length = 0;

  /**
  * constructor
  *
  * hast to remember the parent object
  *
  * @param simple_xmlelement object &$parentNode
  * @access public
  */
  function __construct(&$parentNode) {
    $this->parentNode = &$parentNode;
  }

  /**
  * named constructor for php 4
  *
  * @param expat_node object &$parentNode
  * @access public
  */
  function expat_nodemap(&$parentNode) {
    $this->__construct($parentNode);
  }
}

/**
* emulate ext/dom DOMNodeMap for childnodes
* @package Papaya-Library
* @subpackage XML-DOM-Emulation
*/
class expat_nodemap_childnodes extends expat_nodemap {

  /**
  * get an child node by index
  *
  * @param $index
  * @access public
  * @return object expat_node
  */
  function &item($index) {
    $result = NULL;
    if (is_int($index) &&
        isset($this->parentNode) &&
        isset($this->parentNode->_childNodes) &&
        is_array($this->parentNode->_childNodes) &&
        count($this->parentNode->_childNodes) > $index) {
      $result = &$this->parentNode->_childNodes[$index];
    }
    return $result;
  }

  /**
  * save all childnodes to xml
  *
  * @access public
  * @return string
  */
  function saveXML() {
    $result = '';
    if ($this->parentNode->hasChildNodes()) {
      for ($idx = 0; $idx < $this->length; $idx++) {
        $result .= $this->parentNode->_childNodes[$idx]->saveXML();
      }
    }
    return $result;
  }

  /**
  * return text content of all childnodes
  *
  * @access public
  * @return string
  */
  function valueOf() {
    $result = '';
    if ($this->parentNode->hasChildNodes()) {
      for ($idx = 0; $idx < $this->length; $idx++) {
        if (is_object($this->parentNode->_childNodes[$idx])) {
          $result .= $this->parentNode->_childNodes[$idx]->valueOf();
        }
      }
    }
    return $result;
  }
}

/**
* emulate ext/dom DOMNamedNodeMap for attributes
* @package Papaya-Library
* @subpackage XML-DOM-Emulation
*/
class expat_nodemap_attributes extends expat_nodemap {

  /**
  * get an child node by index
  *
  * @param $index
  * @access public
  * @return object expat_attribute_node
  */
  function &item($index) {
    $result = NULL;
    if (is_int($index) &&
        isset($this->parentNode) &&
        isset($this->parentNode->_attributes) &&
        is_array($this->parentNode->_attributes) &&
        count($this->parentNode->_attributes) > $index) {
      $keys = array_keys($this->parentNode->_attributes);
      $result = &$this->parentNode->_attributes[$keys[$index]];
    }
    return $result;
  }

  /**
  * get item by name
  *
  * @param $index
  * @access public
  * @return object expat_attribute_node
  */
  function &getNamedItem($index) {
    $result = NULL;
    if (is_string($index) &&
        isset($this->parentNode) &&
        isset($this->parentNode->_attributes) &&
        is_array($this->parentNode->_attributes) &&
        isset($this->parentNode->_attributes[$index])) {
      $result = &$this->parentNode->_attributes[$index];
    }
    return $result;
  }
}
?>