<?php
/**
* Papaya Utilities for UTF-8 strings
*
* @copyright 2009-2010 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Utf8.php 36902 2012-03-29 12:35:11Z weinert $
*/

/**
* Papaya Utilities for UTF-8 strings
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilStringUtf8 {

  const EXT_UNKNOWN = 0;
  const EXT_PCRE = -1;
  const EXT_INTL = 1;
  const EXT_MBSTRING = 2;

  /**
  * The used unicode extension is chached
  *
  * @var integer
  */
  private static $extension = 0;

  /**
  * Checks a UTF-8 string for invalid bytes and converts it to UTF-8.
  *
  * It assumes that the invalid bytes are ISO-8859-1. Valid UTF-8 chars stay unchanged.
  *
  * @param string $str
  * @access public
  * @return string
  */
  public static function ensure($string) {
    $pattern = '(
     (
      [\\xC2-\\xDF][\\x80-\\xBF]| #utf8-2
      \\xE0[\\xA0-\\xBF][\\x80-\\xBF]| #utf8-3
      [\\xE1-\\xEC][\\x80-\\xBF]{2}|
      \\xED[\\x80-\\x9F][\\x80-\\xBF]|
      [\\xEE-\\xEF][\\x80-\\xBF]{2}|
      \\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}| #utf8-4
      [\\xF1-\\xF3][\\x80-\\xBF]{3}|
      \\xF4[\\x80-\\x8F][\\x80-\\xBF]{2}
     )
     |([^\\x00-\\x7F]) # latin 1 upper
    )x';
    return preg_replace_callback(
      $pattern,
      array('self', 'ensureCharCallback'),
      (string)$string
    );
  }

  /**
  * Return a codepoint for a given utf 8 encoded character
  *
  * @param string $character
  * @return integer|FALSE
  */
  public static function getCodepoint($character) {
    $cp0 = $cp1 = $cp2 = $cp3 = 0;
    switch (strlen($character)) {
    case 4 :
      $cp3 = ord($character[3]);
    case 3 :
      $cp2 = ord($character[2]);
    case 2 :
      $cp1 = ord($character[1]);
    case 1 :
      $cp0 = ord($character[0]);
      break;
    default :
      return FALSE;
    }
    //single byte utf-8
    if ($cp0 >= 0 && $cp0 <= 127) {
      return $cp0;
    }
    // 2 bytes
    if ($cp0 >= 192 && $cp0 <= 223) {
      return ($cp0 - 192) * 64 + ($cp1 - 128);
    }
    // 3 bytes
    if ($cp0 >= 224 && $cp0 <= 239) {
      return ($cp0 - 224) * 4096 + ($cp1 - 128) * 64 + ($cp2 - 128);
    }
    // 4 bytes
    if ($cp0 >= 240 && $cp0 <= 247) {
      return ($cp0 - 240) * 262144 + ($cp1 - 128) * 4096 + ($cp2 - 128) * 64 + ($cp3 - 128);
    }
    return FALSE;
  }

  /**
  * Callback function for PapayaUtilStringUtf8::ensure*
  *
  * Can get a valid utf-8 sequence in $charMatch[1] or an invalid bytecode in $charMatch[2]
  * If $charMatch[2] is filled papaya assumes that it is a ISO-8859-1 char,
  * because old papaya CMS versions supported only that charset
  *
  * @param array $charMatch
  * @access public
  * @return string
  */
  public static function ensureCharCallback($charMatch) {
    if (isset($charMatch[2]) && $charMatch[2] !== '') {
      $c = ord($charMatch[2]);
      return chr(0xC0 | $c >> 6).chr(0x80 | $c & 0x3F);
    } else {
      return $charMatch[1];
    }
  }

  /**
  * Get string length of an utf-8 string (works only on utf-8 strings)
  *
  * @param string $string
  * @return integer
  */
  public static function length($string) {
    switch (self::getExtension()) {
    case self::EXT_INTL :
      return grapheme_strlen($string);
    case self::EXT_MBSTRING :
      return mb_strlen($string, 'utf-8');
    }
    $string = preg_replace('(.)su', '.', $string);
    return strlen($string);
  }

  /**
  * Copy a part of an utf-8 string (works only on utf-8 strings)
  *
  * @param string $string
  * @param integer $start
  * @param NULL|integer $length
  * @return string
  */
  public static function copy($string, $start, $length = NULL) {
    switch (self::getExtension()) {
    case self::EXT_INTL :
      if (is_null($length)) {
        return grapheme_substr($string, $start);
      } elseif ($length > 0) {
        if ($start >= 0) {
          $possibleLength = self::length($string) - $start;
        } else {
          $possibleLength = abs($start);
        }
        if ($possibleLength < $length) {
          $length = $possibleLength;
        }
      }
      return grapheme_substr($string, $start, $length);
    case self::EXT_MBSTRING :
      if (is_null($length)) {
        $length = self::length($string);
      }
      if ($length == 0) {
        return '';
      }
      return mb_substr($string, $start, $length, 'utf-8');
    }
    $stringLength = self::length($string);
    if ($start < 0) {
      $start = $stringLength + $start;
    }
    if (is_null($length)) {
      $length = $stringLength;
    } elseif ($length < 0) {
      $length = $stringLength + $length - $start;
    }
    if ($length <= 0) {
      return '';
    }
    $pattern = '(.{'.((int)$start).'}(.{1,'.((int)$length).'}))su';
    if (preg_match($pattern, $string, $match)) {
      return $match[1];
    }
    return '';
  }

  /**
  * Get the position of a substring in an utf-8 string (works only on utf-8 strings)
  *
  * @param string $haystack
  * @param string $needle
  * @param integer $offset
  * @return FALSE|integer
  */
  public static function position($haystack, $needle, $offset = 0) {
    switch (self::getExtension()) {
    case self::EXT_INTL :
      return grapheme_strpos($haystack, $needle, $offset);
    case self::EXT_MBSTRING :
      return mb_strpos($haystack, $needle, $offset, 'utf-8');
    }
    if (FALSE !== ($position = strpos($haystack, $needle, $offset))) {
      return self::length(substr($haystack, 0, $position));
    }
    return FALSE;
  }

  /**
  * Determine which extension is available and should be used for utf-8 operations.
  * Preference is ext/intl, ext/mbstring and fallback
  *
  * @return integer
  */
  public static function getExtension() {
    if (self::$extension == self::EXT_UNKNOWN) {
      self::$extension = self::EXT_PCRE;
      $extensions = array(
        self::EXT_INTL => 'intl',
        self::EXT_MBSTRING => 'mbstring'
      );
      foreach ($extensions as $extension => $name) {
        if (extension_loaded($name)) {
          self::$extension = $extension;
          break;
        }
      }
    }
    return self::$extension;
  }

  /**
  * Define the extension that should be used for utf-8 operations
  *
  * @param integer $extension
  */
  public static function setExtension($extension) {
    self::$extension = (int)$extension;
  }
}