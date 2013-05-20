<?php
/**
* Replacement for the DOMDocument adding some shortcuts for easier use
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
* @version $Id: Document.php 37795 2012-12-11 10:21:56Z weinert $
*/

/**
* Replacement for the DOMDocument adding some shortcuts for easier use
*
* @package Papaya-Library
* @subpackage Xml
*/
class PapayaXmlDocument
  extends DOMDocument
  implements PapayaXmlNodeInterface {

  private $_xpath = NULL;

  private $_namespaces = array();

  private $_activateEntityLoader = FALSE;

  private $_canDisableEntityLoader = TRUE;

  /**
  * Initialize document object and register own nodeclass(es)
  *
  * @return PapayaXmlDocument
  */
  public function __construct($version = '1.0', $encoding = 'UTF-8') {
    parent::__construct($version, $encoding);
    $this->registerNodeClass('DOMElement', 'PapayaXmlElement');
    $this->_canDisableEntityLoader = function_exists('libxml_disable_entity_loader');
  }

  /**
   * Get an Xpath object for the current document instance, refresh it if the internal document
   * id changes (document loading), register namespaces on the xpath object.
   *
   * @return DOMXpath
   */
  public function xpath() {
    if (is_null($this->_xpath) || $this->_xpath->document != $this) {
      $this->_xpath = new PapayaXmlXpath($this);
      foreach ($this->_namespaces as $prefix => $namespace) {
        $this->xpath()->registerNamespace($prefix, $namespace);
      }
    }
    return $this->_xpath;
  }

  /**
   * Register Namespaces for the document and an attaches Xpath instance.
   *
   * @param array $namespaces
   */
  public function registerNamespaces(array $namespaces) {
    $this->_namespaces = PapayaUtilArray::merge($this->_namespaces, $namespaces);
    if (isset($this->_xpath)) {
      foreach ($namespaces as $prefix => $namespace) {
        $this->xpath()->registerNamespace($prefix, $namespace);
      }
    }
  }

  /**
   * Get the namespace for an prefix. If the $prefix contains a ':' only the part before that
   * character will be used.
   *
   * @param string $prefix
   * @throws UnexpectedValueException
   * @return string
   */
  public function getNamespace($prefix) {
    if (FALSE !== ($position = strpos($prefix, ':'))) {
      $prefix = substr($prefix, 0, $position);
    }
    if (isset($this->_namespaces[$prefix])) {
      return $this->_namespaces[$prefix];
    }
    throw new UnexpectedValueException('Unknown namespace prefix: '.$prefix);
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
    $node = self::createElementNode($this, $name, $attributes, $content);
    $this->appendChild($node);
    return $node;
  }

  /**
  * Append a xml fragment into document.
  *
  * This will fail if the document already has an element
  * or the document fragment does not contain one.
  *
  * If a target is provided, it will append the xml to the target node.
  *
  * @param string $content
  * @param PapayaXmlElement $target
  * @return PapayaXmlElement|PapayaXmlDocument $target
  */
  public function appendXml($content, PapayaXmlElement $target = NULL) {
    if (is_null($target)) {
      $target = $this;
    }
    $fragment = $this->createDocumentFragment();
    $content = sprintf(
      '<papaya:content xmlns:papaya="http://www.papaya-cms.com/ns/papayacms">%s</papaya:content>',
      PapayaUtilStringUtf8::ensure($content)
    );
    $fragment->appendXml($content);
    if ($fragment->firstChild) {
      if ($target->ownerDocument == NULL) {
        if ($fragment->firstChild->firstChild) {
          $target->appendChild($fragment->firstChild->firstChild->cloneNode(TRUE));
        }
      } else {
        foreach ($fragment->firstChild->childNodes as $node) {
          $target->appendChild($node->cloneNode(TRUE));
        }
      }
    }
    return $target;
  }

  /**
   * Overload createDocument(), to look for an namespace prefix in the element name and create
   * an element in this namespace. The namespace needs to be registered on the document object.
   *
   * @see DOMDocument::createElement()
   * @param string $name
   * @param string|NULL $content
   * @return DOMElement
   */
  public function createElement($name, $content = NULL) {
    if (FALSE !== strpos($name, ':')) {
      $node = $this->createElementNS($this->getNamespace($name), $name);
    } else {
      $node = parent::createElement($name);
    }
    if (!is_null($content)) {
      $node->appendChild($this->createTextNode($content));
    }
    return $node;
  }

  /**
  * Create an new element node for a given document
  *
  * @param PapayaXmlDocument $document
  * @param string $name
  * @param array $attributes
  * @param string $content
  * @return PapayaXmlElement new node
  */
  public static function createElementNode(PapayaXmlDocument $document,
                                           $name,
                                           array $attributes = array(),
                                           $content = NULL) {
    $node = $document->createElement($name);
    foreach ($attributes as $name => $value) {
      $node->setAttribute($name, $value);
    }
    if (!is_null($content)) {
      $node->appendChild($document->createTextNode($content));
    }
    return $node;
  }

  /**
   * Get/set the entry loader status
   *
   * @param boolean $status
   */
  public function activateEntityLoader($status = NULL) {
    if (NULL !== $status) {
      $this->_activateEntityLoader = $status;
    }
    return $this->_activateEntityLoader;
  }

  /**
   * Load an xml string, but allow to disable the entitiy loader.
   *
   * @see DOMDocument::load()
   */
  public function loadXml($source, $options = 0) {
    $status = ($this->_canDisableEntityLoader)
      ? libxml_disable_entity_loader(!$this->_activateEntityLoader) : FALSE;
    $result = parent::loadXML($source, $options);
    $status = ($this->_canDisableEntityLoader)
      ? libxml_disable_entity_loader($status) : FALSE;
    return $result;
  }
}