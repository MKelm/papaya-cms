<?php
/**
* A simple textarea (multiline input) field
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
* @version $Id: Textarea.php 37484 2012-08-27 22:21:02Z weinert $
*/

/**
* A simple textarea (multiline input) field
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldTextarea extends PapayaUiDialogField {

  /**
  * Field lines
  * @var integer
  */
  protected $_lineCount = 0;

  /**
  * Initialize object, set caption, field name and maximum length
  *
  * @param string|PapayaUiString $caption
  * @param string $name
  * @param integer $lines
  * @param mixed $default
  * @param PapayaFilter|NULL $filter
  */
  public function __construct($caption, $name, $lines = 10,
                              $default = NULL, PapayaFilter $filter = NULL) {
    $this->setCaption($caption);
    $this->setName($name);
    $this->setLineCount($lines);
    $this->setDefaultValue($default);
    if (isset($filter)) {
      $this->setFilter($filter);
    }
  }

  /**
  * Set the line count of this element.
  *
  * @param integer $lineCount
  * @return PapayaUiDialogFieldInput
  */
  public function setLineCount($lineCount) {
    PapayaUtilConstraints::assertInteger($lineCount);
    $this->_lineCount = $lineCount;
  }

  /**
  * Append field and textarea output to DOM
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $input = $field->appendElement(
      'textarea',
      array(
        'type' => 'text',
        'name' => $this->_getParameterName($this->getName()),
        'lines' => $this->_lineCount
      ),
      (string)$this->getCurrentValue()
    );
  }

}