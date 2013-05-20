<?php
/**
* expat_element_node emulates a part of ext/dom DOMNode
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
* @version $Id: expat_element_node.php 36224 2011-09-20 08:00:57Z weinert $
*/


/**
* expat_element_node emulates a part of ext/dom DOMNode
*
* @package Papaya-Library
* @subpackage XML-DOM-Emulation
*/
class expat_element_node extends expat_node {

  /**
  * dom node type
  * @var integer
  */
  var $nodeType = XML_ELEMENT_NODE;

  /**
  * Element attributes
  * @var array $_attributes
  */
  var $_attributes;

  /**
  * Children of active Element
  * @var array $_childNodes
  */
  var $_childNodes;

  /**
  * list of empty tags - initialisized if needed
  * @access private
  * @var array
  */
  var $_emptyTags = NULL;

  /**
  * child node list class interface (emulates DOMDocument behavior)
  * @var object expat_nodemap_childnodes
  */
  var $childNodes = NULL;

  /**
  * attribute node list class interface (emulates DOMDocument behavior)
  * @var object expat_nodemap_attributes
  */
  var $attributes = NULL;

  /**
  * Constuctor
  *
  * @param object expat_node &$parentNode
  * @param string $name
  * @param mixed array $attr optional, default value NULL
  * @access public
  */
  function __construct(&$parentNode, $nodeName, $attr = NULL) {
    $this->parentNode = &$parentNode;
    $this->nodeName = $nodeName;
    $this->_childNodes = array();
    $this->_attributes = array();
    $this->childNodes = new expat_nodemap_childnodes($this);
    $this->attributes = new expat_nodemap_attributes($this);
    if (isset($this->parentNode) && is_object($this->parentNode)) {
      $this->parentNode->_appendChildNode($this);
    }
    if (isset($attr) && is_array($attr)) {
      foreach ($attr as $name => $value) {
        new expat_attribute_node($this, $name, $value);
      }
    }
  }

  /**
  * php constructor
  *
  * @param object expat_node &$parent
  * @param string $name
  * @param mixed array $attr optional, default value NULL
  * @access public
  */
  function expat_element_node(&$parentNode, $nodeName, $attr = NULL, $useEmpty = TRUE) {
    $this->__construct($parentNode, $nodeName, $attr, $useEmpty);
  }

  /**
  * add child node to child list
  *
  * @param expat_node &$child
  * @access private
  */
  function _appendChildNode(&$child) {
    $this->childNodes->parentNode = &$this;
    $this->_childNodes[count($this->_childNodes)] = &$child;
    $this->childNodes->length = count($this->_childNodes);
  }

  /**
  * append a child node to this node
  *
  * @param expat_node &$child
  * @access public
  */
  function appendChild(&$child) {
    $this->_appendChildNode($child);
    $child->parentNode = &$this;
  }

  /**
  * add child node to child list
  *
  * @param expat_attribute_node &$attribute
  * @access private
  */
  function _appendAttribute(&$attribute) {
    $this->attributes->parentNode = &$this;
    $this->_attributes[$attribute->name] = &$attribute;
    $this->attributes->length = count($this->_attributes);
  }

  /**
  * get xml
  * @return string
  */
  function saveXML() {
    $result = '<'.$this->nodeName;
    foreach ($this->_attributes as $attribute) {
      $result .= ' '.htmlspecialchars($attribute->name).'="'.
        htmlspecialchars($attribute->value);
    }
    $result .= '>';
    $result .= $this->childNodes->saveXML();
    $result = '</'.$this->nodeName.'>';
    return $result;
  }

  /**
  * get text content
  * @return string
  */
  function valueOf() {
    $result = $this->childNodes->valueOf();
    return $result;
  }

  /**
  * node has child nodes
  * @return boolean
  */
  function hasChildNodes() {
    if (isset($this->_childNodes) && is_array($this->_childNodes) &&
        count($this->_childNodes) > 0) {
      $this->childNodes->length = count($this->_childNodes);
      return TRUE;
    } else {
      $this->childNodes->length = 0;
    }
    return FALSE;
  }

  /**
  * node hast attribute
  * @param string $name
  * @return boolean
  */
  function hasAttribute($name) {
    return isset($this->_attributes[$name]);
  }

  /**
  * get attribute value
  * @param string $name
  * @return string|NULL
  */
  function getAttribute($name) {
    if ($this->hasAttribute($name)) {
      return $this->_attributes[$name]->value;
    } else {
      return NULL;
    }
  }

  /**
  * get attribute value
  * @param string $name
  * @param string $value
  */
  function setAttribute($name, $value) {
    if (in_array($name, $this->_attributes)) {
      $this->_attributes[$name]->value = $value;
    } else {
      new expat_attribute_node($this, $name, $value);
      $this->_attributes->length = count($this->_attributes);
      $this->_attributes->parentNode = &$this;
    }
  }

  /**
  * remove attribute from node
  * @return void
  */
  function removeAttribute() {
    if (in_array($name, $this->_attributes)) {
      unset($this->_attributes[$name]);
      $this->attributes->length = count($this->_attributes);
    }
  }

  /**
  * node has attributes
  * @return boolean
  */
  function hasAttributes() {
    if (isset($this->_attributes) &&
        is_array($this->_attributes) &&
        count($this->_attributes) > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * get all attributes as array
  * @return array
  */
  function getAttributesArray() {
    if (isset($this->_attributes) &&
        is_array($this->_attributes) &&
        count($this->_attributes) > 0) {
      $result = array();
      foreach ($this->_attributes as $attribute) {
        $result[$attribute->name] = $attribute->value;
      }
      return $result;
    } else {
      return array();
    }
  }

  /**
  * return an array of element child nodes with a specific tagname
  *
  * @param string $tagName tag name
  * @param boolean $caseSensitive compare case sensitive
  * @access public
  * @return array
  */
  function &getElementsByTagName($tagName, $caseSensitive = TRUE) {
    $result = array();
    $this->_getElementsByTagNameRecursive($result, $tagName, $caseSensitive);
    return $result;
  }

  /**
  * collect an array of element child nodes with a specific tagname
  *
  * @param array &$nodes node buffer array
  * @param string $tagName tag name
  * @param boolean $caseSensitive compare case sensitive
  * @access public
  * @return void
  */
  function _getElementsByTagNameRecursive(&$nodes, $tagName, $caseSensitive = TRUE) {
    if ($this->hasChildNodes() && !empty($tagName)) {
      for ($i = 0; $i < $this->childNodes->length; $i++) {
        $node = &$this->childNodes->item($i);
        if ($node->nodeType == XML_ELEMENT_NODE) {
          if ($caseSensitive) {
            if ($node->nodeName == $tagName) {
              $nodes[] = &$node;
            }
          } else {
            if (strtolower($node->nodeName) == strtolower($tagName)) {
              $nodes[] = &$node;
            }
          }
          $node->_getElementsByTagNameRecursive($nodes, $tagName, $caseSensitive);
        }
      }
    }
  }

  /**
  * get the first element with a specific id
  *
  * @param string $id id attrbute string
  * @access public
  * @return NULL|expat_element_node object
  */
  function _getElementByIdRecursive($id) {
    $result = NULL;
    if (!empty($id) && $this->hasChildNodes()) {
      for ($i = 0; $i < $this->childNodes->length; $i++) {
        $node = &$this->childNodes->item($i);
        if ($node->nodeType == XML_ELEMENT_NODE) {
          if ($node->hasAttribute('id') &&
              $node->getAttribute('id') == $id) {
            return $node;
          } else {
            $result = $node->_getElementByIdRecursive($id);
            if (!empty($result)) {
              return $result;
            }
          }
        }
      }
    }
    return $result;
  }

  /**
  * destroy nodes
  *
  * @access public
  */
  function free() {
    if (isset($this->_childNodes) && is_array($this->_childNodes)) {
      foreach (array_keys($this->_childNodes) as $id) {
        if (is_object($this->_childNodes[$id])) {
          $this->_childNodes[$id]->free();
        }
        unset($this->_childNodes[$id]);
      }
    }
    if (isset($this->_attributes) && is_array($this->_attributes)) {
      foreach (array_keys($this->_attributes) as $id) {
        unset($this->_attributes[$id]);
      }
    }
    unset($this->childNodes);
    unset($this->attributes);
  }
}
?>