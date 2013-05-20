<?php
/**
* A selection field displayed as checkboxes, multiple values can be selected.
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
* @version $Id: Checkboxes.php 37496 2012-08-31 14:28:35Z weinert $
*/

/**
* A selection field displayed as checkboxes, multiple values can be selected.
*
* The actual value is a list of the selected keys.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldSelectCheckboxes extends PapayaUiDialogFieldSelect {

  /**
  * type of the select control, used in the xslt template
  *
  * @var string
  */
  protected $_type = 'checkboxes';

  /**
  * Determine if the option is selected using the current value and the option value.
  *
  * @param mixed $currentValue
  * @param string $optionValue
  */
  protected function _isOptionSelected($currentValue, $optionValue) {
    return in_array($optionValue, (array)$currentValue);
  }

  /**
  * If the values are set, it is nessessary to create a filter based on the values.
  */
  protected function _createFilter() {
    return new PapayaFilterArray(
      parent::_createFilter()
    );
  }

  /**
  * Get the current field value.
  *
  * If the dialog object has a matching paremeter it is used. Otherwise the data object of the
  * dialog is checked and used.
  *
  * If neither dialog parameter or data is available, the default value is returned.
  *
  * @return mixed
  */
  public function getCurrentValue() {
    $result = parent::getCurrentValue();
    return is_array($result) ? $result : array();
  }
}