<?php
/**
* Papaya filter class that chcks if the value is an empty one
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
* @version $Id: Empty.php 38143 2013-02-19 14:58:24Z weinert $
*/

/**
* Papaya filter class that chcks if the value is an empty one
*
* The private typeMapping property is used to specifiy possible casts.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterEmpty implements PapayaFilter {

  /**
  * zero will be considered as an empty value
  * @var integer
  */
  private $_ignoreZero = TRUE;

  /**
  * values containing only whitespaces will be considered as an empty value
  * @var integer
  */
  private $_ignoreSpaces = TRUE;

  /**
  * Construct object, check and store options
  *
  * @param string $type
  */
  public function __construct($ignoreZero = TRUE, $ignoreSpaces = TRUE) {
    PapayaUtilConstraints::assertBoolean($ignoreZero);
    PapayaUtilConstraints::assertBoolean($ignoreSpaces);
    $this->_ignoreZero = $ignoreZero;
    $this->_ignoreSpaces = $ignoreSpaces;
  }

  /**
  * Check the value throw exception if value is not empty
  *
  * @throws PapayaFilterExceptionNotEmpty
  * @param string $value
  * @return TRUE
  */
  public function validate($value) {
    if (is_array($value)) {
      if (count($value) == 0) {
        return TRUE;
      }
    } else {
      $value = (string)$value;
      if ($value === '' ||
          ($this->_ignoreZero && $value === '0') ||
          ($this->_ignoreSpaces && trim($value) === '')) {
        return TRUE;
      }
    }
    throw new PapayaFilterExceptionNotEmpty($value);
  }

  /**
  * The filter function always returns NULL
  *
  * @param string $value
  * @return integer|NULL
  */
  public function filter($value) {
    return NULL;
  }
}