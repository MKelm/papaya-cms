<?php
/**
* Abstract superclass implementing basic features for user interface control
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
* @version $Id: Control.php 36955 2012-04-11 13:17:52Z weinert $
*/

/**
* Abstract superclass implementing basic features for user interface control.
*
* @package Papaya-Library
* @subpackage Ui
*/
abstract class PapayaUiControl extends PapayaUiControlPart {

  /**
  * Compile output xml for the user interface element.
  * @return string
  */
  public function getXml() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('control');
    $this->appendTo($dom->documentElement);
    $xml = '';
    foreach ($dom->documentElement->childNodes as $node) {
      $xml .= $node->ownerDocument->saveXml($node);
    }
    return $xml;
  }
}
