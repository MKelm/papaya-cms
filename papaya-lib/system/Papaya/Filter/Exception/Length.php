<?php
/**
* A length exception is thrown if a certain length is expected and another if found
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
* @subpackage Filter
* @version $Id: Length.php 34328 2010-06-08 13:10:38Z weinert $
*/

/**
* A length exception is thrown if a certain length is expected and another if found
*
* In other words if a value is to short or to long
*
* @package Papaya-Library
* @subpackage Filter
*/
abstract class PapayaFilterExceptionLength extends PapayaFilterException {

  /**
  * The expected length of the value
  * @var integer
  */
  private $_expectedLength = 0;
  /**
  * The actual length of the value
  * @var unknown_type
  */
  private $_actualLength = 0;

  /**
  * Construct object, set message and length informations
  *
  * @param string $message
  * @param integer $expected
  * @param integer $actual
  */
  public function __construct($message, $expected, $actual) {
    $this->_expectedLength = $expected;
    $this->_actualLength = $actual;
    parent::__construct($message);
  }

  /**
  * Read private expected length property
  *
  * @return integer
  */
  public function getExpectedLength() {
    return $this->_expectedLength;
  }

  /**
  * Read private actual length property
  *
  * @return integer
  */
  public function getActualLength() {
    return $this->_actualLength;
  }
}