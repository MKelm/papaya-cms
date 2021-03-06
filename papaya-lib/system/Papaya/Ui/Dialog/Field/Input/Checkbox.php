<?php
/**
* A checkbox for an active/inactive value
*
* Creates a dialog field for time input.
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
* @version $Id: Checkbox.php 37484 2012-08-27 22:21:02Z weinert $
*/

/**
* A checkbox for an active/inactive value
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
class PapayaUiDialogFieldInputCheckbox extends PapayaUiDialogFieldInput {

  /**
  * Specify the field type for the template
  *
  * @var string
  */
  protected $_type = 'checkbox';

  /**
  * Field type, used in template
  *
  * @var string
  */
  protected $_values = array(
    'active' => TRUE,
    'inactive' => FALSE
  );

  /**
  * declare dynamic properties
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'caption' => array('getCaption', 'setCaption'),
    'name' => array('getName', 'setName'),
    'hint' => array('getHint', 'setHint'),
    'defaultValue' => array('getDefaultValue', 'setDefaultValue'),
    'mandatory' => array('getMandatory', 'setMandatory')
  );

  /**
  * Creates dialog field for time input with caption, name, default value and
  * mandatory status
  *
  * @param string $caption
  * @param string $name
  * @param mixed $default optional, default NULL
  * @param boolean $mandatory optional, default FALSE
  */
  public function __construct($caption, $name, $default = NULL, $mandatory = TRUE) {
    parent::__construct($caption, $name, 9, $default);
    $this->setMandatory($mandatory);
    $this->setFilter(
      new PapayaFilterEquals($this->_values['active'])
    );
  }

  /**
  * Append the field to the xml output
  *
  * @param PapayaXmlElement $parent
  * @return PapayaXmlElement
  */
  public function appendTo(PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $currentValue = $this->getCurrentValue();
    $input = $field->appendElement(
      'input',
      array(
        'type' => $this->_type,
        'name' => $this->_getParameterName($this->getName()),
        'maxlength' => $this->_maximumLength
      ),
      (string)$this->_values['active']
    );
    if ($currentValue == $this->_values['active']) {
      $input->setAttribute('checked', 'checked');
    }
    return $field;
  }

  /**
  * Allow to change the values
  *
  * @param mixed $active
  * @param mixed $inactive
  */
  public function setValues($active, $inactive) {
    if (empty($active)) {
      throw new InvalidArgumentException(
        'The active value can not be empty.'
      );
    }
    if ($active == $inactive) {
      throw new InvalidArgumentException(
        'The active value and the inactive value must be different.'
      );
    }
    $this->_values = array(
      'active' => $active,
      'inactive' => $inactive
    );
    $this->setFilter(
      new PapayaFilterEquals($this->_values['active'])
    );
  }

  /**
  * Get the current field value. This can be either of two values specified by the member
  * variable $_values
  *
  * @return mixed
  */
  public function getCurrentValue() {
    $value = parent::getCurrentValue();
    if (empty($value) || $value === $this->_values['inactive']) {
      return $this->_values['inactive'];
    } elseif ($value == $this->_values['active']) {
      return $this->_values['active'];
    }
    return $this->_values['inactive'];
  }

  /**
   * The filter is only active if the field is mandatory. Otherwise it will just set the
   * "inactive" value if it is not valid
   *
   * @see PapayaUiDialogField::getFilter()
   */
  public function getFilter() {
    if ($this->getMandatory()) {
      return parent::getFilter();
    } else {
      return NULL;
    }
  }
}