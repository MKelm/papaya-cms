<?php
/**
* Papaya Utilities for Strings
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
* @subpackage Util
* @version $Id: String.php 36895 2012-03-28 09:43:24Z weinert $
*/

/**
* Papaya Utilities for Strings
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilString {

  /**
  * Truncate a string
  *
  * If $cut is TRUE it will do cut the string at the char length. If it is FALSE, it will
  * cut it at the first non letter or return an empty string.
  *
  * @param string $string
  * @param length $charLength
  * @param boolean $cut Cut words
  * @param string $suffix suffix string for truncated strings
  * @return string
  */
  public static function truncate($string, $length, $cut = TRUE, $suffix = '') {
    if ($cut) {
      $pattern = '(^(.{0,'.(int)$length.'}))us';
    } else {
      $pattern = '(^(.{0,'.(int)$length.'})(?:$|\P{L}))us';
    }
    if (preg_match($pattern, $string, $matches)) {
      if ($matches[1] == $string) {
        return $string;
      }
      return PapayaUtilStringUtf8::ensure(chop($matches[1]).$suffix);
    }
    return '';
  }

  /**
  * Escape a string to be used as part of a formatted string pattern (e.g. printf).
  *
  * @param string $string
  * @return string
  */
  public static function escapeForPrintf($string) {
    return str_replace('%', '%%', $string);
  }
}
