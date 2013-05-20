<?php
/**
* A single line input for ISO date with optional time, the internal value is an unix timestamp.
*
* Creates a dialog field for date (and optional time) input.
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
* @version $Id: Timestamp.php 37484 2012-08-27 22:21:02Z weinert $
*/

/**
* A single line input for date and optional time, the internal value is an unix timestamp.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property string|PapayaUiString $caption
* @property string $name
* @property string $hint
* @property string|NULL $defaultValue
* @property boolean $mandatory
*/
class PapayaUiDialogFieldInputTimestamp extends PapayaUiDialogFieldInputDate {

  /**
   * Create object and initalize integer filter
   *
   * @param string|PapayaUiString $caption
   * @param string $name
   * @param integer $default
   * @param boolean $mandatory
   * @param integer $includeTime
   * @param float $step
   */
  public function __construct($caption, $name, $default = NULL,
      $mandatory = FALSE, $includeTime = PapayaFilterDate::DATE_NO_TIME, $step = 60.0) {
    parent::__construct($caption, $name, $default, $mandatory, $includeTime, $step);
    $this->setFilter(new PapayaFilterInteger(1));
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
    $name = $this->getName();
    if ($this->hasCollection() &&
        $this->collection()->hasOwner() &&
        !empty($name) &&
        $this->collection()->owner()->parameters()->has($name)) {
      $dateTime = $this->collection()->owner()->parameters()->get($name);
      return strtotime($dateTime);
    }
    return (int)parent::getCurrentValue();
  }

  /**
  * Append field and input ouptut to DOM
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $input = $field->appendElement(
      'input',
      array(
        'type' => $this->_type,
        'name' => $this->_getParameterName($this->getName()),
        'maxlength' => $this->_maximumLength
      ),
      $this->formatDateTime(
        $this->getCurrentValue(), $this->_includeTime != PapayaFilterDate::DATE_NO_TIME
      )
    );
  }

  /**
  * Convert timestamp into a string
  *
  * @param integer $timestamp
  * @param boolean $includeTime
  * @return string
  */
  private function formatDateTime($timestamp, $includeTime = TRUE) {
    if ($timestamp == 0) {
      return '';
    } elseif ($includeTime) {
      return date('Y-m-d H:i:s', $timestamp);
    } else {
      return date('Y-m-d', $timestamp);
    }
  }
}