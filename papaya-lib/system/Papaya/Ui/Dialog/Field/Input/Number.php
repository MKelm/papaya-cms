<?php
/**
* A single line input for unsigned numbers with optional minimum/maximum length
*
* Creates a dialog field for unsigned numbers (that may start with 0).
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
* @version $Id: Number.php 37484 2012-08-27 22:21:02Z weinert $
*/

/**
* A single line input for unsigned numbers with optional minimum/maximum length
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
class PapayaUiDialogFieldInputNumber extends PapayaUiDialogFieldInput {

  /**
  * Field type, used in template
  *
  * @var string
  */
  protected $_type = 'number';

  /**
  * Minimum length
  * @var integer
  */
  protected $_minimumLength = NULL;

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
  * Creates dialog field for date input with caption, name, default value and
  * mandatory status
  *
  * @param string $caption
  * @param string $name
  * @param mixed $default optional, default NULL
  * @param boolean $mandatory optional, default FALSE
  * @param integer $minimumLength optional, default NULL
  * @param integer $maximumLength optional, default NULL
  */
  public function __construct($caption, $name, $default = NULL,
      $mandatory = FALSE, $minimumLength = NULL, $maximumLength = 1024) {
    if ($minimumLength !== NULL) {
      if (!is_numeric($minimumLength) || $minimumLength <= 0) {
        throw new UnexpectedValueException('Minimum length must be greater than 0.');
      }
    }
    if (!is_numeric($maximumLength) || $maximumLength <= 0) {
      throw new UnexpectedValueException('Maximum length must be greater than 0.');
    }
    if ($minimumLength !== NULL && $minimumLength > $maximumLength) {
      throw new UnexpectedValueException(
        'Maximum length must be greater than or equal to minimum length.'
      );
    }
    parent::__construct($caption, $name, $maximumLength, $default);
    $this->_minimumLength = $minimumLength;
    $this->setMandatory($mandatory);
    $this->setFilter(
      new PapayaFilterNumber($this->_minimumLength, $this->_maximumLength)
    );
  }
}