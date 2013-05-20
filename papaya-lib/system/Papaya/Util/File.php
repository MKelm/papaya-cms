<?php
/**
* A bunch of file related utility functions.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
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
* @version $Id: File.php 37046 2012-05-11 12:20:59Z weinert $
*/

/**
* A bunch of file related utility functions.
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilFile {

  /**
  * Format a given bytes value into a human readable string
  *
  * @param integer $bytes
  * @return string
  */
  public static function formatBytes($bytes, $decimals = 2, $decimalSeparator = '.') {
    return PapayaUtilBytes::toString($bytes, $decimals, $decimalSeparator);
  } 

  /**
  * Format a given string into simple ascii and shorten it, to be used in filename/url
  *
  * @param integer $bytes
  * @return string
  */
  public static function normalizeName($utf8string, $maxLength, $language = '', $unknown = '-') {
    $transliterator = new PapayaStringTransliterationAscii();
    $result = trim(
      preg_replace(
        '([^a-zA-Z\d]+)',
        $unknown,
        $transliterator->transliterate($utf8string, $language)
      ),
      $unknown
    );
    if ($maxLength > 0 && strlen($result) > $maxLength) {
      $result = substr($result, 0, $maxLength);
      $p = strrpos($result, $unknown);
      if ($p > 0) {
        $result = substr($result, 0, $p);
      }
    }
    return $result;
  }
}