<?php
/**
* Replacement for the DOMXpath without the (broken) automatic namespace registration if possible.
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
* @subpackage Xml
* @version $Id: Xpath.php 37452 2012-08-22 14:43:53Z weinert $
*/

/**
* Replacement for the DOMXpath without the (broken) automatic namespace registration if possible.
*
* @package Papaya-Library
* @subpackage Xml
*/
class PapayaXmlXpath extends DOMXpath {

  /**
   * @var boolean
   */
  private $_registerNodeNamespaces = FALSE;

  /**
   * Create object and diable the automatic namespace registration if possible.
   */
  public function __construct(DOMDocument $dom) {
    parent::__construct($dom);
    $this->registerNodeNamespaces(version_compare(PHP_VERSION, '<', '5.3.3'));
  }

  /**
   * Enable/Disable the automatic namespace registration, return the current status
   * @param boolean|NULL $enabled
   * @return boolean
   */
  public function registerNodeNamespaces($enabled = NULL) {
    if (isset($enabled)) {
      $this->_registerNodeNamespaces = (boolean)$enabled;
    }
    return $this->_registerNodeNamespaces;
  }

  /**
   * Evaluate an xpath expression an return the result
   *
   * @see DOMXPath::evaluate()
   * @param string $expression
   * @param DOMNode $contextnode
   * @return DOMNodelist|String|Float|Integer|Boolean|FALSE
   */
  public function evaluate($expression, DOMNode $contextnode = NULL, $registerNodeNS = NULL) {
    if ($registerNodeNS || (NULL === $registerNodeNS && $this->_registerNodeNamespaces)) {
      return isset($contextnode)
        ? parent::evaluate($expression, $contextnode)
        : parent::evaluate($expression);
    }
    return parent::evaluate($expression, $contextnode, FALSE);
  }

  /**
   * Query should not be used, but evaluate. Block it.
   *
   * @see DOMXPath::query()
   */
  public function query($expression, DOMNode $contextnode = NULL, $registerNodeNS = NULL) {
    throw new LogicException('"query()" should not be used, use "evaluate()".');
  }

}
