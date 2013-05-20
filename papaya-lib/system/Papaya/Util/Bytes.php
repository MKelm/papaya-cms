<?php
/**
* A bunch of bytes related utility functions.
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
* @subpackage Util
* @version $Id: Bytes.php 37046 2012-05-11 12:20:59Z weinert $
*/

/**
* A bunch of bytes related utility functions.
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilBytes {

  /**
  * Format a given bytes value into a human readable string
  *
  * @param integer $bytes
  * @return string
  */
  public static function toString($bytes, $decimals = 2, $decimalSeparator = '.') {
    if ($bytes > 1073741824) {
      $size = $bytes / 1073741824;
      $unit = ' GB';
    } elseif ($bytes > 1048576) {
      $size = $bytes / 1048576;
      $unit = ' MB';
    } elseif ($bytes > 1024) {
      $size = $bytes / 1024;
      $unit = ' kB';
    } else {
      return round($bytes).' B';
    }
    return number_format($size, $decimals, $decimalSeparator, '').$unit;
  }

  /**
  * Convert a string containing a unit into an integer
  *
  * @param string $string
  * @return integer
  */
  public static function fromString($string) {
    $string = trim($string);
    if (preg_match('((?P<size>[\d.,]+)\s*(?P<unit>[a-z]*))i', $string, $matches)) {
      $size = PapayaUtilArray::get($matches, 'size', 0);
      $unit = strtolower(PapayaUtilArray::get($matches, 'unit', ''));
    } else {
      $size = $string;
      $unit = '';
    }
    switch($unit) {
    case 'k':
    case 'kb':
      return (int)$size * 1024;
      break;
    case 'm':
    case 'mb':
      return (int)$size * 1048576;
    case 'g':
    case 'gb':
      return (int)$size * 1073741824;
      break;
    default:
      return (int)$size;
    }
  }
}