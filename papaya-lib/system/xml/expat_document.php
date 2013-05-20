<?php
/**
* Implementation expat xml access
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
* @version $Id: expat_document.php 36224 2011-09-20 08:00:57Z weinert $
*/
if (!defined('XML_DOCUMENT_NODE')) {
  /**
  * emulate DomDocument constant
  */
  define('XML_DOCUMENT_NODE', 9);
}

if (!defined('XML_ELEMENT_NODE')) {
  /**
  * emulate DomDocument constant
  */
  define('XML_ELEMENT_NODE', 1);
}

if (!defined('XML_ATTRIBUTE_NODE')) {
  /**
  * emulate DomDocument constant
  */
  define('XML_ATTRIBUTE_NODE', 2);
}

if (!defined('XML_TEXT_NODE')) {
  /**
  * emulate DomDocument constant
  */
  define('XML_TEXT_NODE', 3);
}

/**
* error constant empty xml data
*/
define('XML_ERROR_EMPTY', 1);
/**
* error constant invalid file
*/
define('XML_ERROR_FILE', 2);
/**
* error constant xml parser error
*/
define('XML_ERROR_PARSE', 3);

/**
* abstract node class
*/
require_once(dirname(__FILE__).'/expat_node.php');
/**
* element node class
*/
require_once(dirname(__FILE__).'/expat_element_node.php');
/**
* text node class
*/
require_once(dirname(__FILE__).'/expat_text_node.php');
/**
* attribute node class
*/
require_once(dirname(__FILE__).'/expat_attribute_node.php');
/**
* node map (list) class
*/
require_once(dirname(__FILE__).'/expat_nodemap.php');

/**
* Expat xml handling class
* @package Papaya-Library
* @subpackage XML-DOM-Emulation
*/
class expat_document {

  /**
  * element type
  * @var integer
  */
  var $nodeType = XML_DOCUMENT_NODE;

  /**
  * Load from file
  *
  * @param string $fileName
  * @access public
  * @return integer error page
  */
  function load($fileName) {
    unset($this->documentElement);
    unset($this->_currentNode);
    if (!($fp = fopen($fileName, "r"))) {
      $this->setError(XML_ERROR_FILE, array('file' => $fileName));
      return FALSE;
    }
    $parser = $this->initParser();
    while ($data = fread($fp, 4096)) {
      if (!xml_parse($parser, $data, feof($fp))) {
        $errorNumber = xml_get_error_code($parser);
        $this->setError(
          XML_ERROR_PARSE,
          array(
            'errno' => $errorNumber,
            'error' => xml_error_string($errorNumber),
            'file' => $fileName,
            'line' => xml_get_current_line_number($parser)
          ));
        return FALSE;
      }
    }
    xml_parser_free($parser);
    return TRUE;
  }

  /**
  * create a new node for the tree
  *
  * @param $nodeName
  * @access public
  * @return expat_element_node
  */
  function &createElement($nodeName) {
    $null = NULL;
    $node = new expat_element_node($null, $nodeName);
    return $node;
  }

  /**
  * create a new text node for the tree
  *
  * @param $content
  * @access public
  * @return expat_text_node
  */
  function &createTextNode($content) {
    $null = NULL;
    $node = new expat_text_node($null, $content);
    return $node;
  }

  /**
  * append a child to the xmltree, because the xmltree
  * can only have one document element, this is set.
  *
  * @param &$node
  * @access public
  */
  function appendChild(&$node) {
    if (isset($node) && is_object($node) && $node->nodeType == XML_ELEMENT_NODE) {
      $this->documentElement = &$node;
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
    if (isset($this->documentElement) && is_object($this->documentElement)) {
      if ($caseSensitive) {
        if ($this->documentElement->nodeName == $tagName) {
          $nodes[] = &$this->documentElement;
        }
      } else {
        if (strtolower($this->documentElement->nodeName) == strtolower($tagName)) {
          $nodes[] = &$this->documentElement;
        }
      }
      $this->documentElement->_getElementsByTagNameRecursive($result, $tagName, $caseSensitive);
    }
    return $result;
  }

  /**
  * return the first element in tree with the matching id attribute
  *
  * @param string $id
  * @access public
  * @return NULL | expat_element_node object
  */
  function &getElementById($id) {
    $result = NULL;
    if (!empty($id) && isset($this->documentElement) && is_object($this->documentElement)) {
      if ($this->documentElement->hasAttribute('id') &&
          $this->documentElement->getAttribute('id') == $id) {
        return $this->documentElement;
      }
      $result = $this->documentElement->_getElementByIdRecursive($id);
    }
    return $result;
  }

  /**
  * Load from string
  *
  * @param string $str
  * @access public
  * @return integer error page
  */
  function loadXML($str) {
    unset($this->documentElement);
    unset($this->_currentNode);
    if (trim($str) == '') {
      $this->setError(XML_ERROR_EMPTY);
      return FALSE;
    }
    $parser = $this->initParser();
    if (!xml_parse($parser, $str, TRUE)) {
      $errorNumber = xml_get_error_code($parser);
      $this->setError(
        XML_ERROR_PARSE,
        array(
          'errno' => $errorNumber,
          'error' => xml_error_string($errorNumber),
          'line' => xml_get_current_line_number($parser)
        )
      );
      return FALSE;
    }
    xml_parser_free($parser);
    return TRUE;
  }

  /**
  * Init ext/xml Parser and define method handlers
  *
  * @access public
  * @return object xml_parser
  */
  function initParser() {
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, FALSE);
    xml_set_element_handler(
      $parser,
      array(&$this, 'parserStartElement'),
      array(&$this, 'parserEndElement')
    );
    xml_set_character_data_handler($parser, array(&$this, 'parserCharacterData'));

    return $parser;
  }

  /**
  * Handler - new element in xml started
  *
  * @param object $parser
  * @param string $name
  * @param array $attrs
  * @access public
  */
  function parserStartElement(&$parser, $name, $attrs) {
    if (isset($this->_currentNode) && is_object($this->_currentNode)) {
      $parent = &$this->_currentNode;
      $this->_currentNode = new expat_element_node($parent, $name, $attrs);
    } else {
      $parent = NULL;
      $this->documentElement = new expat_element_node($parent, $name, $attrs);
      $this->_currentNode = &$this->documentElement;
    }
  }

  /**
  * Handler - current element in xml closed
  *
  * @param object $parser
  * @param string $name
  * @access public
  */
  function parserEndElement(&$parser, $name) {
    $this->_currentNode = &$this->_currentNode->parentNode;
  }

  /**
  * Handler - text data element
  *
  * @param object $parser
  * @param string $data
  * @access public
  */
  function parserCharacterData(&$parser, $data) {
    $parent = &$this->_currentNode;
    if (is_object($parent) && $parent->childNodes->length > 0) {
      $prevNode = &$parent->childNodes->item($parent->childNodes->length - 1);
      if (is_a($prevNode, 'expat_text_node')) {
        $prevNode->nodeValue .= $data;
        return;
      }
    }
    new expat_text_node($parent, $data);
  }

  /**
  * get nodes as xml string
  *
  * @param mixed $node optional, default value NULL (documentElement node)
  * @access public
  * @return string
  */
  function saveXML($node = NULL) {
    if (isset($node)) {
      return $node->saveXML();
    } elseif (isset($this->documentElement)) {
      return $this->documentElement->saveXML();
    } else {
      return '';
    }
  }

  /**
  * unset tree
  *
  * @access public
  */
  function free() {
    if (isset($this->documentElement)) {
      $this->documentElement->free();
      unset($this->documentElement);
    }
  }

  /**
  * Set error array
  *
  * @param integer $error
  * @param array $params
  * @access public
  */
  function setError($error, $params = NULL) {
    $this->lastError[0] = $error;
    $this->lastError[1] = $params;
  }
}

?>