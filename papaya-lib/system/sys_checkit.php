<?php
/**
* Implementation of input check functions
*
* Check if strings for example match email address syntax
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Core
* @version $Id: sys_checkit.php 36119 2011-08-19 09:52:44Z weinert $
*/

if (defined('PCRE_VERSION') && version_compare(PCRE_VERSION, '6.7', '>=')) {
  /**
  * define a constant with all unicode letters ready to use in a pcre char class
  * in PHP 5 and PCRE 6.7 we can use unicode properties
  * otherwise we use a bytecode string
  */
  define('PAPAYA_CHECKIT_WORDCHARS', '\\pL');
} else {
  /**
  * @ignore
  */
  define(
    'PAPAYA_CHECKIT_WORDCHARS',
    "\xc3\x80-\xc3\x8f\xc3\x91-\xc3\x96\xc3\x98-\xc3\xb6\xc3\xb8-\xc3\xbf"
  );
}

/**
* Examine a string to given properties like email, numbers etc.
* This class will be used directly. You call the include functions with checkit::xyz.
*
* @package Papaya
* @subpackage Core
*/
class checkit {

  /**
  * Check if string is NOT empty
  *
  * @param string $str string to check
  * @return boolean
  */
  function filled($str) {
    return (strlen(trim((string)$str)) > 0);
  }

  /**
  * Check string against the pattern (PCRE)
  *
  * @param string $str string to check
  * @param string $pattern filter
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function check($str, $pattern, $mustContainValue = FALSE) {
    if (checkit::filled($str)) {
      $result = (preg_match((string)$pattern, (string)$str));
    } else {
      $result = (!($mustContainValue));
    }
    return (boolean)$result;
  }

  /**
  * Check string consists of letter or alfanumeric characters.
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isAlpha($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '~^[a-zA-Z'.PAPAYA_CHECKIT_WORDCHARS.'\. ,_-]+$~u',
      $mustContainValue
    );
  }

  /**
  * Check string consists of letter or numbers.
  *
  * @param string $str string
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isAlphaChar($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '~^[\da-zA-Z'.PAPAYA_CHECKIT_WORDCHARS.'\.\(\)\[\]\/ ,_-]+$~u',
      $mustContainValue
    );
  }


  /**
  * Check string consists of numbers
  *
  * @param string $str string
  * @param boolean $mustContainValue string may be empty?
  * @param integer $digitsMin minimum number of numbers
  * @param integer $digitsMax maximum number of numbers
  * @return boolean
  */
  function isNum($str, $mustContainValue = FALSE,
                 $digitsMin = NULL, $digitsMax = NULL) {
    if (isset($digitsMin)) {
      if (isset($digitsMax)) {
        return checkit::check(
          $str,
          '~^[\+\-]?\d{'.(int)$digitsMin.','.(int)$digitsMax.'}$~u',
          $mustContainValue
        );
      } else {
        return checkit::check(
          $str,
          '~^[\+\-]?\d{'.(int)$digitsMin.',}$~u',
          $mustContainValue
        );
      }
    } else {
      return (checkit::check($str, '~^[\+\-]?\d+$~u', $mustContainValue));
    }
  }

  /**
  * check string is float number (with .)
  *
  * @param string $str
  * @param boolean $mustContainValue optional, default value FALSE
  * @return boolean
  */
  function isFloat($str, $mustContainValue = FALSE) {
    return (checkit::check($str, '~^[\+\-]?\d+(\.\d+)?$~u', $mustContainValue));
  }

  /**
  * Check string consists of alphanumeric characters
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isAlphaNum($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '~^[a-zA-Z0-9'.PAPAYA_CHECKIT_WORDCHARS.'\. ,_-]+$~u',
      $mustContainValue
    );
  }

  /**
  * Check string consists of alphanumeric characters with numbers
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isAlphaNumChar($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '~^[a-zA-Z0-9'.PAPAYA_CHECKIT_WORDCHARS.'\.\(\)\[\]\/ ,_-]+$~u',
      $mustContainValue
    );
  }

  /**
  * Check string consists of measure and numbers
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isNumUnit($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '~^((\d+(px|pt|%)?)|(\d+(\.\d+)?em))$~u',
      $mustContainValue
    );
  }

  /**
  * Check string is safe to use in an url
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function internalSafeURL($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '~^[a-zA-Z0-9\.\(\)\[\]\/ ,_-]+$~u',
      $mustContainValue
    );
  }

  /**
  * Check string is no HTML
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isNoHTML($str, $mustContainValue = FALSE) {
    return checkit::check($str, '~^[^<>]+$~u', $mustContainValue);
  }

  /**
  * Check string is some text
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isSomeText($str, $mustContainValue = FALSE) {
    return checkit::check($str, '~\S~u', $mustContainValue);
  }

  /**
  * Check string is a phone number
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isPhone($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '~^[0-9\+][0-9\(\)\/ -]+$~u',
      $mustContainValue
    );
  }

  /**
  * Check string is filename
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isFile($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '(^(([\.a-zA-Z0-9/_~-]*/)*)([a-zA-Z0-9_-]+(\.[a-z0-9]+)*)$)uD',
      $mustContainValue
    );
  }

  /**
  * Check string is path
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isPath($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '(^([a-zA-Z]:/)?([\.a-zA-Z0-9/_~-]*/)+$)uD',
      $mustContainValue
    );
  }

  /**
  * Check string is date in german format dd.mm.yyyy.
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isGermanDate($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '~^\d{1,2}\.\d{1,2}\.\d{2,4}$~u',
      $mustContainValue
    );
  }

  /**
   * Check string is german zip NNNNN or D-NNNNN
   *
   * @param string $str String to check
   * @param boolen $mustContainValue String may be empty?
   * @return boolean
   */
  function isGermanZip($str, $mustContainValue = FALSE) {
    return checkit::check($str, '~^(D)?[0-9]{5}$~u', $mustContainValue);
  }

  /**
  * Extended HTTP check
  *
  * anchors             http://www.blah.de/index.html#top
  * parameters          http://www.blah.de/index.html?foo=bar
  * virtual directories http://www.blah.de/~user/index.html
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isHTTPX($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '{^https?://
       ([a-z_]+(:[^@]+)?@)?
       ([a-z\d_-]+\.)*
       ([a-z\d_-]+)(\.[a-z]{2,6})?
       (:[\d\W\S]{1,5})?
       (/[+:%@\.a-z\d_~=()!,;-]*)*
       /?
       (\?
        (
         ([:a-z\d\.\[\]()/%@!_,;-]+)|
         (&?[+:a-z\d\.\[\]()/%@_,;-]+=[+:a-z\d_\.\[\]()/%@!,;-]*)|
         &
        )*
       )?
       (\#[&?=+:a-z\d\.\[\]()%@!_,;/\\-]*)?$}iux',
      $mustContainValue
    );
  }

  /**
  * Check web adress (http://* or www.*)
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isHTTP($str, $mustContainValue = FALSE) {
    return (
      checkit::check(
        $str,
        '{^http://[a-z\d_-]+\.[a-z\d_-]*\.?[a-z\d_-]+[a-z\d/\._~@-]*$}iu',
        $mustContainValue
      ) ||
      checkit::check(
        $str,
        '{^www\.[a-z\d_-]+\.[a-z\d9_-]+[a-z\d/\._@-]*$}iu',
        $mustContainValue
      )
    );
  }

  /**
  * Check http host name
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isHTTPHost($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '{^([a-zA-Z'.
      PAPAYA_CHECKIT_WORDCHARS.
      '\d_-]+\.)*([a-zA-Z'.
      PAPAYA_CHECKIT_WORDCHARS.
      '\d-]{2,})(\.[a-z]{2,6})?(:\d{1,5})?$}u',
      $mustContainValue
    );
  }

  /**
  * Check IPv4 address
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isIPv4Address($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '{^((25[0-5]|2[0-4][0-9]|[0-1]\d{2}|\d{1,2})\.){3}(25[0-5]|2[0-4][0-9]|[0-1]\d{2}|\d{1,2})$}',
      $mustContainValue
    );
  }

  /**
  * Check IPv6 address
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isIPv6Address($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '{^((([\da-f]){1,4}:){7}([\da-f]){1,4}'.
      '|::(([\da-f]){1,4}:){0,6}([\da-f]){1,4}'.
      '|(([\da-f]){1,4}:){0,5}([\da-f]){1,4}::(([\da-f]){1,4}:){0,5}([\da-f]){1,4})$}i',
      $mustContainValue
    );
  }

  /**
  * Check any IP address
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isIPAddress($str, $mustContainValue = FALSE) {
    return (
      checkit::isIPv4Address($str, $mustContainValue) ||
      checkit::isIPv6Address($str, $mustContainValue)
    );
  }

  /**
  * Check host name or IP address
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isHTTPHostOrIPAddress($str, $mustContainValue = FALSE) {
    return (
      checkit::isHTTPHost($str, $mustContainValue) ||
      checkit::isIPAddress($str, $mustContainValue)
    );
  }

  /**
  * Check string is email adress
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isEmail($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '{^[-!\#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+
        @[-!\#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+
        \.[-!\#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$}umx',
      $mustContainValue
    );
  }

  /**
  * Check string is 32 byte hexcode
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @access public
  * @return boolean
  */
  function isGUID($str, $mustContainValue = FALSE) {
    return checkit::check($str, '~^[a-fA-F\d]{32}$~u', $mustContainValue);
  }

  /**
  * Check date is in ISO-format
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isISODate($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '~^([12]\d{3})-(\d|(0\d)|(1[0-2]))-(([012]?\d)|(3[01]))$~u',
      $mustContainValue
    );
  }

  /**
   * Check geo position
   *
   * This Method checks if a string consists of 2 comma separeted double values and if
   * they are between -180 and 180 degrees.
   *
   * @param string String to check
   * @param boolean String must contain any values
   * @return boolean True if string is correct
   */
  function isGeoPos($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '(^-?([1-9]?\d)(\.\d+)?,\s*-?(180|1[0-7]\d|\d\d?)(\.\d+)?$)',
      $mustContainValue
    );
  }

  /**
  * Check date and time is in ISO-format
  *
  * @param string $str string
  * @param boolean $mustContainValue string may be empty ?
  * @return mixed FALSE or int
  */
  function isISODateTime($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '(^([12]\d{3})-(\d|(0\d)|(1[0-2]))-(([012]?\d)|(3[01]))\s+
         ([01]\d|2[0-4]):([0-5]\d)(\:([\0-5]\d))?$)ux',
      $mustContainValue
    );
  }

  /**
  * Check string is a time
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isTime($str, $mustContainValue = FALSE) {
    return checkit::check(
      $str,
      '~^((1\d)|(2[0-3])|(0?\d)):([0-5]\d)$~u',
      $mustContainValue
    );
  }

  /**
  * Check string is HTML color
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isHTMLColor($str, $mustContainValue = FALSE) {
    return checkit::check($str, '~^#[\da-fA-F]{6}$~u', $mustContainValue);
  }

  /**
  * Check string is Password
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  function isPassword($str, $mustContainValue = FALSE) {
    if (empty($str)) {
      return !$mustContainValue;
    } else {
      $filter = new PapayaFilterPassword();
      try {
        return $filter->validate($str);
      } catch (PapayaFilterException $e) {
        return FALSE;
      }
    }
  }

  /**
  * convert ISO date/time to unix timestamp
  *
  * @param string $str string to convert
  * @access public
  * @return integer
  */
  function convertISODateTimeToUnix($str) {
    if ($date = PapayaUtilDate::stringToTimestamp($str)) {
      return $date;
    } else {
      return 0;
    }
  }

  /**
  * check string for spam
  *
  * @param string $str
  * @return integer $result
  */
  function isSpam($str) {
    $result = 0;
    $matches = array();
    $result += floor(preg_match_all('~\bhttps?://~iu', $str, $matches) / 2);
    $result += preg_match_all('~<a[^>]+href~iu', $str, $matches);
    return $result;
  }

  /**
  * Check string is xhtml
  *
  * @param $str
  * @param boolean $mustContainValue string may be empty?
  * @return integer $result
  */
  function isXhtml($str, $mustContainValue = FALSE) {
    $result = FALSE;
    if (!$mustContainValue || !empty($str)) {
      $xhtmlContent = sprintf(
        '<xml>%s</xml>',
        papaya_strings::entityToXML($str)
      );
      $null = NULL;
      if ($xhtml = simple_xmltree::createFromXML($xhtmlContent, $null)) {
        return TRUE;
      }
    }
    return FALSE;
  }
}
?>