<?php
/**
* This exception is thrown if a value is not enclosed in a list of values.
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
* @version $Id: Enclosed.php 34706 2010-08-19 15:14:49Z weinert $
*/

/**
* This exception is thrown if a value is not enclosed in a list of values.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterExceptionNotEnclosed extends PapayaFilterException {

  /**
  * The actual length of the value
  * @var scalar
  */
  private $_actualValue = 0;

  /**
  * Construct object with value informations
  *
  * @param scalar $actual
  */
  public function __construct($actual) {
    parent::__construct(
      sprintf(
        'Value is to not enclosed in list of valid elements. Got "%s".',
        $actual
      )
    );
    $this->_actualValue = $actual;
  }
}