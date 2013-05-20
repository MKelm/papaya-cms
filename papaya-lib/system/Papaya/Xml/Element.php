<?php
/**
* Replacement for the DOMElement adding some shortcuts for easier use
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
* @version $Id: Element.php 37484 2012-08-27 22:21:02Z weinert $
*/

/**
* Replacement for the DOMElement adding some shortcuts for easier use
*
* @package Papaya-Library
* @subpackage Xml
*/
class PapayaXmlElement
  extends DOMElement
  implements PapayaXmlNodeInterface {

  /**
  * Append a object (with interface PapayaXmlAppendable) to the element
  *
  * @param PapayaXmlAppendable $object
  * @return PapayaXmlElement|NULL
  */
  public function append(PapayaXmlAppendable $object) {
    return $object->appendTo($this);
  }

  /**
  * Append an xml element with attributes and content
  *
  * @param string $name
  * @param array $attributes
  * @param string $content
  * @return PapayaXmlElement new element
  */
  public function appendElement($name, array $attributes = array(), $content = NULL) {
    $node = PapayaXmlDocument::createElementNode(
      $this->ownerDocument, $name, $attributes, $content
    );
    $this->appendChild($node);
    return $node;
  }

  /**
  * Append a new text node into element
  *
  * @param string $content
  * @return PapayaXmlElement $this
  */
  public function appendText($content) {
    $node = $this->ownerDocument->createTextNode($content);
    $this->appendChild($node);
    return $this;
  }

  /**
  * Append a xml fragment into element
  *
  * @param string $content
  * @return PapayaXmlElement $this
  */
  public function appendXml($content) {
    return $this->ownerDocument->appendXml($content, $this);
  }

  /**
  * Append this node to given target (document or element node).
  *
  * Automatically imports the element into the target document if needed.
  *
  * @param DOMelement|DOMDocument $target
  */
  public function appendTo(DOMNode $target) {
    if ($target instanceof DOMElement) {
      $document = $target->ownerDocument;
    } elseif ($target instanceof DOMDocument) {
      $document = $target;
    } else {
      throw new InvalidArgumentException(
        'Can only append to DOMDocument or DOMElement objects.'
      );
    }
    if ($document != $this->ownerDocument) {
      $source = $document->importNode($this, TRUE);
    } else {
      $source = $this;
    }
    $target->appendChild($source);
  }

  /**
  * Store the xml of the current element into a string and return it.
  *
  * @return string
  */
  public function saveXml() {
    return $this->ownerDocument->saveXml($this);
  }

  /**
  * Store the xml of all child nodes (including text nodes) into a string and return it.
  *
  * @return string
  */
  public function saveFragment() {
    $result = '';
    foreach ($this->childNodes as $childNode) {
      $result .= $childNode->ownerDocument->saveXml($childNode);
    }
    return $result;
  }

  /**
   * Allow to remove an attribute by setitng an empty value
   *
   * @see DOMElement::setAttribute()
   */
  public function setAttribute($name, $value) {
    if (isset($value) && $value !== '') {
      parent::setAttribute($name, (string)$value);
    } else {
      parent::removeAttribute($name);
    }
  }
}