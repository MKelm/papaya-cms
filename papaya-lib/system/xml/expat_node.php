<?php
/**
* expat dom emulation - abstract node class
*
* list for atutor/contributor tags in the feed
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
* @version $Id: expat_node.php 32351 2009-10-08 11:28:16Z weinert $
*/

/**
* expat node - abstract base class
* @package Papaya-Library
* @subpackage XML-DOM-Emulation
*/
class expat_node {

  /**
  * parent element
  * @var object simple_xmlnode $parentNode
  */
  var $parentNode = NULL;

  /**
  * Contains name of active element
  * @var string $nodeName
  */
  var $nodeName = '';

  /**
  * node content
  * @var string $text
  */
  var $nodeValue = '';

  /**
  * node type - undefined
  * @var integer
  */
  var $nodeType;

  /**
  * get text data
  *
  * @access public
  * @return string
  */
  function valueOf() {
    return '';
  }

  /**
  * has this node subnodes?
  *
  * @access public
  * @return boolean
  */
  function hasChildNodes() {
    return FALSE;
  }

  /**
  * attribute exists?
  *
  * @access public
  * @return boolean
  */
  function hasAttribute() {
    return FALSE;
  }

  /**
  * get a single attribute value
  *
  * @param string $attrName
  * @access public
  * @return string
  */
  function getAttribute($attrName) {
    return NULL;
  }
}
?>