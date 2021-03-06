<?php
/**
* A list object that is used to output user messages into xml
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
* @subpackage Ui
* @version $Id: Messages.php 36786 2012-03-02 15:13:13Z weinert $
*/

/**
* A list object that is used to output user messages into xml.
*
* This are visible messages for a user that is requesting a page. They can be occured messages
* or messages that are used in javascript.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiMessages extends PapayaObjectList implements PapayaXmlAppendable {

  /**
  * create lsit object and store child superclass limit
  */
  public function __construct() {
    parent::__construct('PapayaUiMessage');
  }

  /**
  * If the list containts items, append them and return the list xml element.
  *
  * @param PapayaXmlElement $parent
  * @return PapayaXmlElement|NULL
  */
  public function appendTo(PapayaXmlElement $parent) {
    if (!$this->isEmpty()) {
      $list = $parent->appendElement('messages');
      foreach ($this as $item) {
        $list->append($item);
      }
      return $list;
    }
    return NULL;
  }

  /**
  * Return object items as xml string
  *
  * @see appendTo
  * @return string
  */
  public function getXml() {
    if (!$this->isEmpty()) {
      $dom = new PapayaXmlDocument();
      $root = $dom->appendElement('root');
      $this->appendTo($root);
      return $root->firstChild->saveXml();
    }
    return '';
  }
}