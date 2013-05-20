<?php
/**
* A listview subitem displaying multiple icons from a given list.
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
* @version $Id: List.php 36578 2011-12-22 13:55:44Z weinert $
*/

/**
* A listview subitem displaying multiple icons from a given list.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property integer $align
* @property PapayaUiIconList $icons
* @property string $selection
* @property integer $selectionMode
* @property array $actionParameters
*/
class PapayaUiListviewSubitemImageList extends PapayaUiListviewSubitemImageSelect {

  /**
  * Validate the icon indizes using the values of the selection array
  *
  * @var integer
  */
  const VALIDATE_VALUES = 1;

  /**
  * Validate the icon indizes using the keys of the selection array
  *
  * @var integer
  */
  const VALIDATE_KEYS = 2;

  /**
  * Validate the icon indizes using the selection value as an bitmask. The icon indizes need to be
  * integers for that.
  *
  * @var integer
  */
  const VALIDATE_BITMASK = 3;

  /**
  * how to validate if an icon should be displayed
  *
  * @var integer
  */
  protected $_selectionMode = self::VALIDATE_VALUES;

  /**
  * Allow to assign the internal (protected) variables using a public property
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'align' => array('getAlign', 'setAlign'),
    'icons' => array('_icons', 'setIcons'),
    'selection' => array('_selection', '_selection'),
    'selectionMode' => array('_selectionMode', '_selectionMode'),
    'actionParameters' => array('_actionParameters', 'setActionParameters'),
  );

  /**
  * Create subitme and store icon list and selection index.
  *
  * @param PapayaUiIconList $icons
  * @param string $selection
  */
  public function __construct(PapayaUiIconList $icons,
                              $selection,
                              $selectionMode = self::VALIDATE_VALUES,
                              array $actionParameters = NULL) {
    parent::__construct($icons, $selection, $actionParameters);
    $this->_selectionMode = $selectionMode;
  }

  /**
  * Append the subitem to the listitem xml element. If the selected icon is not found
  * the subitem will be empty.
  *
  * @param PapayaXmlElement
  * @return PapayaXmlElement
  */
  public function appendTo(PapayaXmlElement $parent) {
    $subitem = $parent->appendElement(
      'subitem',
      array(
        'align' => PapayaUiOptionAlign::getString($this->getAlign())
      )
    );
    $list = $subitem->appendElement('glyphs');
    foreach ($this->_icons as $index => $icon) {
      $icon = clone $icon;
      if (!$this->validateSelection($index)) {
        $icon->visible = FALSE;
      }
      $icon->appendTo($list);
    }
    return $subitem;
  }

  /**
  * Validate the icon index against the selection depending on the mode.
  *
  * @param mixed $index
  * @return boolean
  */
  protected function validateSelection($index) {
    switch ($this->selectionMode) {
    case self::VALIDATE_BITMASK :
      $result = (int)$this->_selection & (int)$index;
      break;
    case self::VALIDATE_KEYS :
      $result = array_key_exists($index, (array)$this->_selection);
      break;
    case self::VALIDATE_VALUES :
    default :
      $result = in_array($index, (array)$this->_selection);
      break;
    }
    return $result;
  }
}