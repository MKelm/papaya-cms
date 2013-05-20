<?php
/**
* Message context containing simple plain text
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
* @subpackage Messages
* @version $Id: List.php 34412 2010-06-24 11:14:41Z weinert $
*/

/**
* Message context containing simple plain text
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageContextList
  implements
    PapayaMessageContextInterfaceList,
    PapayaMessageContextInterfaceXhtml,
    PapayaMessageContextInterfaceString {

  /**
  * List label/caption
  * @var string
  */
  private $_label = '';

  /**
  * list items
  * @var array
  */
  private $_items = array();

  /**
  * Create list context
  *
  * @param array $items
  */
  public function __construct($label, array $items) {
    $this->_label = $label;
    $this->_items = $items;
  }

  public function getLabel() {
    return $this->_label;
  }

  /**
  * Return list as simple array
  *
  * @return string
  */
  public function asArray() {
    return $this->_items;
  }

  /**
  * Get a string representation of the list
  *
  * @return string
  */
  public function asString() {
    return implode("\n", $this->_items);
  }

  /**
  * Get a xhtml representation of the list
  *
  * @return string
  */
  public function asXhtml() {
    if (count($this->_items) > 0) {
      $result = '<ol>';
      foreach ($this->_items as $item) {
        $result .= '<li>'.PapayaUtilStringXml::escape($item).'</li>';
      }
      $result .= '</ol>';
      return $result;
    }
    return '';
  }
}
