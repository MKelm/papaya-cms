<?php
/**
* Papaya filter superclass
*
* @copyright 2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Filter.php 38143 2013-02-19 14:58:24Z weinert $
*/

/**
* Papaya filter superclass
*
* @package Papaya-Library
* @subpackage Filter
*/
interface PapayaFilter {

  /**
  * The filter function returns the filtered version of an input value.
  *
  * It removes invalid bytes from the input value. A possible implementation whould be a
  * trimmed version of the input.
  *
  * If the input is invalid it should NULL
  *
  * @param mixed|NULL $value
  * @return mixed
  */
  function filter($value);

  /**
  * Checks an input and return true if it is valid.
  *
  * It will throw an PapayaFilterException if the input is invalid.
  *
  * @throws PapayaFilterException
  * @param mixed $value
  * @return boolean
  */
  function validate($value);
}