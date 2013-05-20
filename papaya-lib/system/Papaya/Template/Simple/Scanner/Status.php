<?php
/**
* Abstract superclass for status objects used by the simple template scanner
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
* @subpackage Template
* @version $Id: Status.php 37657 2012-11-09 14:11:15Z weinert $
*/

/**
* Abstract superclass for status objects used by the simple template scanner
*
* @package Papaya-Library
* @subpackage Template
*/
abstract class PapayaTemplateSimpleScannerStatus {
  /**
  * Try to get token in buffer at offset position.
  *
  * @param string $buffer
  * @param integer $offset
  * @return PapayaTemplateSimpleScannerToken
  */
  abstract public function getToken($buffer, $offset);

  /**
  * Check if token ends status
  *
  * @param PapayaTemplateSimpleScannerToken $token
  * @return boolean
  */
  public function isEndToken($token) {
    return FALSE;
  }

  /**
  * Get new (sub)status if needed.
  *
  * @param PapayaTemplateSimpleScannerToken $token
  * @return PapayaTemplateSimpleScannerStatus|NULL
  */
  public function getNewStatus($token) {
    return NULL;
  }

  /**
  * Checks if the given offset position matches the pattern.
  *
  * @param string $buffer
  * @param integer $offset
  * @param string $pattern
  * @return string|NULL
  */
  protected function matchPattern($buffer, $offset, $pattern) {
    $found = preg_match(
      $pattern, $buffer, $match, PREG_OFFSET_CAPTURE, $offset
    );
    if ($found &&
        isset($match[0]) &&
        isset($match[0][1]) &&
        $match[0][1] === $offset) {
      return $match[0][0];
    }
    return NULL;
  }

  /**
  * Checks if the given offset position matches any pattern in the given list. The
  * list is an array with the patterns as keys and token types as values.
  *
  * @param string $buffer
  * @param integer $offset
  * @param array(string=>string) $pattern
  * @return PapayaTemplateSimpleScannerToken|NULL
  */
  protected function matchPatterns($buffer, $offset, $patterns) {
    foreach ($patterns as $pattern => $tokenType) {
      $tokenContent = $this->matchPattern($buffer, $offset, $pattern);
      if (NULL !== $tokenContent) {
        return new PapayaTemplateSimpleScannerToken($tokenType, $offset, $tokenContent);
      }
    }
    return NULL;
  }
}