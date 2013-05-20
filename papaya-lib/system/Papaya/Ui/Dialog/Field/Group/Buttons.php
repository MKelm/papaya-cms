<?php
/**
* Group several dialog buttons
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
* @version $Id: Buttons.php 37590 2012-10-23 15:11:02Z weinert $
*/

/**
* A simple single line input field with a caption.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property string|PapayaUiString $caption
* @property PapayaUiDialogButtons $buttons
*/
class PapayaUiDialogFieldGroupButtons extends PapayaUiDialogField {

  /**
  * Grouped input buttons
  * @var PapayaUiDialogButtons
  */
  protected $_buttons = NULL;

  /**
  * declare dynamic properties
  *
  * @var unknown_type
  */
  protected $_declaredProperties = array(
    'caption' => array('getCaption', 'setCaption'),
    'buttons' => array('_buttons', '_buttons')
  );

  /**
  * Initialize object, set caption
  *
  * @param string|PapayaUiString $caption
  */
  public function __construct($caption) {
    $this->setCaption($caption);
  }

  /**
  * Group buttons getter/setter
  *
  * @param PapayaUiDialogButtons $buttons
  */
  public function buttons(PapayaUiDialogButtons $buttons = NULL) {
    if (isset($buttons)) {
      $this->_buttons = $buttons;
      if ($this->hasCollection() && $this->collection()->hasOwner()) {
        $buttons->owner($this->collection()->owner());
      }
    }
    if (is_null($this->_buttons)) {
      $this->_buttons = new PapayaUiDialogButtons(
        ($this->hasCollection() && $this->collection()->hasOwner())
          ? $this->collection()->owner() : NULL
      );
    }
    return $this->_buttons;
  }

  /**
  * Return the owner collection of the item.
  *
  * @param PapayaUiControlCollection $collection
  * @return PapayaUiControlCollection
  */
  public function collection(PapayaUiControlCollection $collection = NULL) {
    $result = parent::collection($collection);
    if ($collection != NULL && $collection->hasOwner()) {
      $this->buttons()->owner($collection->owner());
    }
    return $result;
  }

  /**
  * Validate field group
  *
  * @return boolean
  */
  public function validate() {
    return TRUE;
  }

  /**
  * Collect field group data
  *
  * @return boolean
  */
  public function collect() {
    if (parent::collect() &&
        isset($this->_buttons)) {
      return $this->_buttons->collect();
    }
    return FALSE;
  }

  /**
  * Append group and buttons in this group to the DOM.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    if (isset($this->_buttons) && count($this->_buttons) > 0) {
      $group = $parent->appendElement(
        'field-group',
        array(
          'caption' => $this->getCaption(),
          'id' => $this->getId()
        )
      );
      $this->_buttons->appendTo($group);
    }
  }
}