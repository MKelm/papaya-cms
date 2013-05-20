<?php
/**
* An IteratorAggregate implementation that uses a callback to create the iterator if needed.
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
* @subpackage Iterator
* @version $Id: Generator.php 37300 2012-07-26 11:30:21Z weinert $
*/

/**
* An IteratorAggregate implementation that uses a callback to create the iterator if needed.
*
* @package Papaya-Library
* @subpackage Iterator
*/
class PapayaIteratorGenerator implements IteratorAggregate {

  /**
   * @var callback
   */
  private $_callback = NULL;

  /**
   * @var array
   */
  private $_arguments = array();

  /**
   * @var Iterator
   */
  private $_iterator = NULL;

  /**
   * Store callback and arguments for later use.
   *
   * @param callback $callback
   * @param array $arguments
   */
  public function __construct($callback, array $arguments = array()) {
    PapayaUtilConstraints::assertCallable($callback);
    $this->_callback = $callback;
    $this->_arguments = $arguments;
  }

  /**
   * IteratorAggregate interface: Trigger callback if not already done and store the
   * created iterator. Return the Iterator.
   *
   * @return Iterator
   */
  public function getIterator() {
    if (NULL == $this->_iterator) {
      $this->_iterator = $this->createIterator();
    }
    return $this->_iterator;
  }

  /**
   * Create and return the Iterator. If possible remove abstraction layers.
   *
   * If the callback returns an array, an ArrayIterator is created.
   * If the callback returns an Iterator, that iterator is returned.
   * If the callback returns an IteratorAggregate, the inner iterator is returned.
   * If the callback returns an Traversable, a PapayaIteratorTraversable is returned.
   *
   * In all other cases an EmptyIterator is returned.
   *
   * @return Iterator
   */
  private function createIterator() {
    $traversable = call_user_func_array($this->_callback, $this->_arguments);
    if (is_array($traversable)) {
      return new ArrayIterator($traversable);
    } elseif ($traversable instanceOf Iterator) {
      return $traversable;
    } elseif ($traversable instanceOf IteratorAggregate) {
      return $traversable->getIterator();
    } else {
      return ($traversable instanceOf Traversable)
        ? new PapayaIteratorTraversable($traversable)
        : new EmptyIterator();
    }
  }
}
