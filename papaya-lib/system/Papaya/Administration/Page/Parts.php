<?php
/**
* Manage the parts of a page. Each part is an interactive ui control.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @subpackage Administration
* @version $Id: Parts.php 37852 2012-12-15 13:06:05Z weinert $
*/

/**
* Manage the parts of a page. Each part is an interactive ui control. On iteration the parameters
* are assigned to the part and fetched back from it.
*
* @package Papaya-Library
* @subpackage Administration
*/
class PapayaAdministrationPageParts
  extends PapayaObjectInteractive
  implements Iterator {

  const PART_CONTENT = 'content';
  const PART_NAVIGATION = 'navigation';
  const PART_INFORMATION = 'information';

  private $_parts = array(
    self::PART_CONTENT => NULL,
    self::PART_NAVIGATION => NULL,
    self::PART_INFORMATION => NULL
  );

  private $_buttonOrder = array(
    self::PART_NAVIGATION,
    self::PART_CONTENT,
    self::PART_INFORMATION
  );

  private $_targets = array(
    self::PART_NAVIGATION => 'leftcol',
    self::PART_INFORMATION => 'rightcol'
  );

  private $_toolbar = NULL;
  private $_page = NULL;

  /**
  * Create and store the $page object. The page object is used to create the parts
  * if needed.
  *
  * @param PapayaAdministrationPage $page
  */
  public function __construct(PapayaAdministrationPage $page) {
    $this->_page = $page;
  }

  public function __get($name) {
    return $this->get($name);
  }

  public function __set($name, $part) {
    return $this->set($name, $part);
  }

  /**
  * Get the specified part, create it if is is defined but does not exist yet.
  *
  * @param string $name
  */
  public function get($name) {
    if (isset($this->_parts[$name])) {
      return $this->_parts[$name];
    }
    return $this->_parts[$name] = $this->create($name);
  }

  /**
  * Set a page part object.
  *
  * @param string $name
  * @param PapayaAdministrationPagePart $part
  */
  public function set($name, PapayaAdministrationPagePart $part = NULL) {
    if (!array_key_exists($name, $this->_parts)) {
      throw new UnexpectedValueException(sprintf('Can not set unknown part "%s".', $name));
    }
    $this->_parts[$name] = $part;
  }

  /**
  * Use the page object to create a page part.
  *
  * @throws UnexpectedValueException
  * @return PapayaAdministrationPagePart|FALSE
  */
  public function create($name) {
    if (!array_key_exists($name, $this->_parts)) {
      throw new UnexpectedValueException(sprintf('Can no create unknown part "%s".', $name));
    }
    if ($part = $this->_page->createPart($name)) {
      $part->papaya($this->papaya());
      $part->parameterGroup($this->parameterGroup());
      $part->toolbar($this->toolbar()->$name);
      return $part;
    }
    return FALSE;
  }

  /**
  * Get the target (layout element) the page part xml shoudl be assigned too.
  *
  * @throws UnexpectedValueException
  * @param string $name
  * @return string
  */
  public function getTarget($name) {
    if (!array_key_exists($name, $this->_parts)) {
      throw new UnexpectedValueException(sprintf('Unknown part "%s".', $name));
    }
    if (!array_key_exists($name, $this->_targets)) {
      return 'centercol';
    }
    return $this->_targets[$name];
  }

  /**
  * The toolbar is composed, so the navigation and the changes subobjects can add elements
  *
  * @param PapayaUiToolbarComposed $toolbar
  */
  public function toolbar(PapayaUiToolbarComposed $toolbar = NULL) {
    if (isset($toolbar)) {
      $this->_toolbar = $toolbar;
    } elseif (is_null($this->_toolbar)) {
      $this->_toolbar = new PapayaUiToolbarComposed(
        array_merge($this->_buttonOrder, array_keys($this->_parts))
      );
      $this->_toolbar->papaya($this->papaya());
    }
    return $this->_toolbar;
  }

  /**
  * Iterator Interface - Rewind $parts array pointer
  * @see Iterator::rewind()
  */
  public function rewind() {
    reset($this->_parts);
  }

  /**
  * Iterator Interface - Move the $parts array pointer to the next element
  *
  * Before moving the array pointer read the parameters of the current element and assign them
  * to the parameters of the list.
  *
  * @see Iterator::next()
  */
  public function next() {
    $previous = current($this->_parts);
    if ($previous) {
      $this->parameters($previous->parameters());
    }
    next($this->_parts);
  }

  /**
  * Iterator Interface - Get the current part after assigning the parameters of the list object.
  *
  * This calls get() to fetch the part, so an implicit call to create is included.
  *
  * @see Iterator::current()
  * @see PapayaAdministrationPageParts::get()
  * @return FALSE|PapayaAdministrationPagePart
  */
  public function current() {
    $part = $this->get($this->key());
    if ($part) {
      $part->parameters($this->parameters());
    }
    return $part;
  }

  /**
  * Iterator Interface - Return the current key value
  *
  * @see Iterator::key()
  * @return string
  */
  public function key() {
    return key($this->_parts);
  }

  /**
  * Iterator Interface - Check if here is an element to iterate
  *
  * @see Iterator::valid()
  * @return boolean
  */
  public function valid() {
    $key = $this->key();
    return ($key !== NULL && $key !== FALSE);
  }
}
