<?php
/**
* A list of string (objects) that will be translated if cast to string
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
* @subpackage Ui
* @version $Id: List.php 37470 2012-08-24 14:28:54Z weinert $
*/

/**
* A list of string (objects) that will be translated if cast to string.
*
* It takes an array or an traversable, cast each element (if read) to string and returns a
* PapayaUiStringTranslated for it.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiStringTranslatedList
  extends IteratorIterator
  implements PapayaObjectInterface {

  /**
  * Application object
  * @var string
  */
  protected $_applicationObject = NULL;

  /**
   * Create object and store traversable as iterator
   *
   * @param array|Traversable $traversable
   */
  public function __construct($traversable) {
    parent::__construct(new PapayaIteratorTraversable($traversable));
  }

  /**
   * Wrap the current element into an translated string and return it.
   *
   * @see IteratorIterator::current()
   * @return string
   */
  public function current() {
    $current = new PapayaUiStringTranslated((string)parent::current());
    $current->papaya($this->papaya());
    return $current;
  }

  /**
  * An combined getter/setter for the Papaya Application object
  *
  * @param PapayaApplication $application
  * @return PapayaApplication
  */
  public function papaya(PapayaApplication $application = NULL) {
    if (isset($application)) {
      $this->_applicationObject = $application;
    } elseif (NULL === $this->_applicationObject) {
      $this->_applicationObject = PapayaApplication::getInstance();
    }
    return $this->_applicationObject;
  }
}
