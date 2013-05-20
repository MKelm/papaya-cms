<?php
/**
* Papaya filter class for xml strings.
*
* @copyright 2011-2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Xml.php 38143 2013-02-19 14:58:24Z weinert $
*/

/**
* Papaya filter class for xml strings.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterXml implements PapayaFilter {

  private $_allowFragments = TRUE;

  /**
   * @param string $allowFragments
   */
  public function __construct($allowFragments = TRUE) {
    $this->_allowFragments = $allowFragments;
  }

  /**
  * Check the value if it's a xml string, if not throw an exception.
  *
  * @throws PapayaFilterExceptionCharacterInvalid
  * @param string $value
  * @return TRUE
  */
  public function validate($value) {
    $value = trim($value);
    if (empty($value)) {
      throw new PapayaFilterExceptionEmpty();
    }
    $errors = new PapayaXmlErrors();
    $errors->activate();
    $dom = new PapayaXmlDocument();
    $result = FALSE;
    try {
      if ($this->_allowFragments) {
        $root = $dom->appendElement('root');
        $root->appendXml($value);
      } else {
        $dom->loadXML($value);
      }
      $errors->omit(TRUE);
    } catch (PapayaXmlException $e) {
      throw new PapayaFilterExceptionXml($e);
    }
    return TRUE;
  }

  /**
  * The filter function is used to read an input value if it is valid.
  *
  * @param string $value
  * @return string
  */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (PapayaFilterException $e) {
      return NULL;
    }
  }
}