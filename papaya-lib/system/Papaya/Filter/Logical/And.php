<?php
/**
* Abstract filter class implementing logical "and" links between other filters
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
* @version $Id: And.php 34862 2010-09-16 11:18:55Z weinert $
*/

/**
* Abstract filter class implementing logical "and" links between other filters
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterLogicalAnd extends PapayaFilterLogical {

  /**
  * Call validate() on each subfilter, the subfilter will throw an
  * exception and break the loop if the value is invalid.
  *
  * @param string $value
  */
  public function validate($value) {
    foreach ($this->_filters as $filter) {
      $filter->validate($value);
    }
    return TRUE;
  }

  /**
  * Call filter() on each subfilter.
  *
  * If NULL is return from a subfilter method call it is returned. In all other cases the returned
  * value is given to the next filter method call.
  *
  * @param string $value
  */
  public function filter($value) {
    foreach ($this->_filters as $filter) {
      $value = $filter->filter($value);
      if (is_null($value)) {
        return NULL;
      }
    }
    return $value;
  }
}