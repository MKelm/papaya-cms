<?php
/**
* A select field with grouped options
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
* @version $Id: Grouped.php 35573 2011-03-29 10:48:18Z weinert $
*/

/**
* A select field with grouped options.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldSelectGrouped extends PapayaUiDialogFieldSelect {

  /**
  * Set option groups and options.
  *
  * The array can have to different structures.
  *
  * A simple label => list of elements version:
  *
  *   array(
  *     'group caption' => array(
  *       'value' => 'option text',
  *       ...
  *     ),
  *     ...
  *   );
  *
  * To allow more komplex group labels an advanced structure is supported:
  *
  *   array(
  *     array(
  *       'caption' => 'Group Caption',
  *       'options' => array(
  *         'value' => 'option label',
  *         ...
  *       )
  *     ),
  *     ...
  *   );
  *
  * In this case the group label can be an object that support string casting
  * (@see PapayaUiString).
  *
  * @param array $values
  */
  public function setValues(array $values) {
    $this->_values = $values;
    $allowedValues = array();
    foreach ($values as $index => $group) {
      $groupValues = array_keys(isset($group['options']) ? $group['options'] : $group);
      $allowedValues = array_merge($allowedValues, $groupValues);
    }
    $this->setFilter(new PapayaFilterList($allowedValues));
  }

  /**
  * Append field output to DOM
  *
  * @param PapayaXMLElement $parent
  */
  public function appendTo(PapayaXMLElement $parent) {
    $this->_appendOptionGroups(
      $this->_appendSelect(
        $this->_appendFieldTo($parent)
      ),
      $this->_values
    );
  }

  /**
  * Append option groups to DOM.
  *
  * @param PapayaXMLElement $parent
  * @param array $groups
  */
  protected function _appendOptionGroups(PapayaXMLElement $parent, array $groups) {
    foreach ($groups as $key => $group) {
      $options = isset($group['options']) ? $group['options'] : $group;
      $label = isset($group['caption']) ? $group['caption'] : $key;
      if (is_array($options) &&
          count($options) > 0) {
        $this->_appendOptions(
          $parent->appendElement(
            'group',
            array('caption' => (string)$label)
          ),
          $options
        );
      }
    }
  }

}
