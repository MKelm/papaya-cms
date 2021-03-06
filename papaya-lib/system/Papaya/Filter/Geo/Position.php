<?php
/**
* Papaya filter class validating a geo position string
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
* @version $Id: Position.php 37429 2012-08-16 17:51:53Z weinert $
*/

/**
* Papaya filter class validating a geo position string
*
* This Method checks if a string consists of 2 comma separated double values and if
* they are between -180 and 180 degrees.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterGeoPosition extends PapayaFilterPcre {

  /**
   * set pattern in superclass constructor
   */
  public function __construct() {
    parent::__construct('(^-?([1-9]?\d)(\.\d+)?,\s*-?(180|1[0-7]\d|\d\d?)(\.\d+)?$)');
  }
}
