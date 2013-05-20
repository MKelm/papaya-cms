<?php
/**
* Papaya Interface String Translated, a string object that will be translated before usage
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
* @subpackage Ui
* @version $Id: Translated.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Papaya Interface String Translated, a string object that will be translated before usage
*
* It allows to create a string object later casted to string. The basic string can
* be a pattern (using sprintf syntax).
*
* Additionally the pattern will be translated into the current user language before the values are
* inserted.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiStringTranslated extends PapayaUiString {

  /**
  * Allow to cast the object into a string, compiling the pattern and values into a result string.
  *
  * return string
  */
  public function __toString() {
    if (is_null($this->_string)) {
      $this->_string = $this->compile(
        $this->translate($this->_pattern), $this->_values
      );
    }
    return $this->_string;
  }

  /**
  * Translate a string using the phrase translations (only availiable in administration mode)
  *
  * return string
  */
  protected function translate($string) {
    $application = $this->papaya();
    if (isset($application->phrases)) {
      return $application->phrases->getText($string);
    } else {
      return $string;
    }
  }
}