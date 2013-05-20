<?php
/**
* emulation for DOMText
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
* @version $Id: expat_text_node.php 32351 2009-10-08 11:28:16Z weinert $
*/

/**
* emulation for DOMText
*
* @package Papaya-Library
* @subpackage XML-DOM-Emulation
*/
class expat_text_node extends expat_node {

  /**
  * dom node type
  * @var integer
  */
  var $nodeType = XML_TEXT_NODE;

  /**
  * Constructor
  *
  * @param object simple_xmlnode &$parentNode
  * @param string $nodeValue content
  * @access public
  */
  function __construct(&$parentNode, $nodeValue) {
    $this->parentNode = &$parentNode;
    $this->nodeValue = $nodeValue;
    if (isset($this->parentNode) && is_object($this->parentNode)) {
      $this->parentNode->_appendChildNode($this);
    }
  }

  /**
  * decode xml entities
  * @param string $str
  * @return string
  */
  function decodeXMLEntities($str) {
    $replace = array('&lt;', '&gt;', '&amp;', '&quot;');
    $with = array('<', '>', '&', '"');
    return str_replace($replace, $with, $str);
  }

  /**
  * Constuctor PHP 4
  *
  * @param object simple_xmlnode &$parent
  * @param string $text content
  * @access public
  */
  function expat_text_node(&$parentNode, $nodeValue) {
    $this->__construct($parentNode, $nodeValue);
  }

  /**
  * save node value to xml
  *
  * @access public
  * @return string
  */
  function saveXML() {
    return htmlspecialchars($this->nodeValue);
  }

  /**
  * return node value
  *
  * @access public
  * @return string
  */
  function valueOf() {
    return $this->decodeXMLEntities($this->nodeValue);
  }

  /**
  * destroy node data
  *
  * @access public
  */
  function free() {
    unset($this->nodeValue);
  }
}
?>