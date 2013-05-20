<?php
/**
* A textarea (multiline input) field, that will be replaced with an RTE using JavaScript
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Richtext.php 37484 2012-08-27 22:21:02Z weinert $
*/

/**
* A textarea (multiline input) field, that will be replaced with an RTE using JavaScript
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldTextareaRichtext extends PapayaUiDialogFieldTextarea {

  const RTE_DEFAULT = 'standard';
  const RTE_SIMPLE = 'simple';
  const RTE_INDIVIDUAL = 'individual';

  private $_rteMode = self::RTE_DEFAULT;

  /**
  * Initialize object, set caption, field name and maximum length
  *
  * @param string|PapayaUiString $caption
  * @param string $name
  * @param integer $lines
  * @param mixed $default
  * @param PapayaFilter|NULL $filter
  * @param integer $rteMode
  */
  public function __construct(
    $caption,
    $name,
    $lines = 10,
    $default = NULL,
    PapayaFilter $filter = NULL,
    $rteMode = self::RTE_DEFAULT
  ) {
    parent::__construct($caption, $name, $lines, $default, $filter);
    $this->setRteMode($rteMode);
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
        'lines' => $this->_lineCount,
        'data-rte' => $this->_rteMode
      ),
      (string)$this->getCurrentValue()
    );
  }

  /**
   * The variant of the richtext editor is mostly defined by javascript
   * so we just need to store a mode and put it into the xml for further use.
   *
   * @param string $mode
   */
  public function setRteMode($mode) {
    $this->_rteMode = $mode;
  }

  /**
   * Read the rte mode used to define the js configuration.
   *
   * @return string
   */
  public function getRteMode() {
    return $this->_rteMode;
  }
}
