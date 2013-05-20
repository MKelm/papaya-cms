<?php
/**
* A single line input for that needs to be send in the request always an empty string
*
* Creates a dialog field for an url input.
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
* @version $Id: Honeypot.php 37921 2013-01-02 15:58:19Z weinert $
*/

/**
* A single line input for that needs to be send in the request always an empty string.
*
* If an robot/script fills the field the dialog will not validate and so the action not executed.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property string|PapayaUiString $caption
* @property string $name
* @property string $hint
*/
class PapayaUiDialogFieldHoneypot extends PapayaUiDialogField {

  /**
  * Field type, used in template
  *
  * @var string
  */
  protected $_type = 'text';

  /**
  * declare dynamic properties
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'caption' => array('getCaption', 'setCaption'),
    'name' => array('getName', 'setName'),
    'hint' => array('getHint', 'setHint')
  );

  /**
  * Creates dialog field for url input with caption, name, default value and
  * mandatory status
  *
  * @param string $caption
  * @param string $name
  */
  public function __construct($caption, $name) {
    parent::setMandatory(TRUE);
    parent::setFilter(new PapayaFilterLogicalAnd(new PapayaFilterIsset(), new PapayaFilterEmpty()));
    $this->setCaption($caption);
    $this->setName($name);
  }

  public function getFilter() {
    return parent::getFilter();
  }

  public function setFilter(PapayaFilter $filter) {
    throw new LogicException('The honeypot field filter can not be changed.');
  }

  public function setMandatory($mandatory) {
    throw new LogicException('The honeypot field is always mandatory.');
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
        !empty($name)) {
      if (!$this->getDisabled() && $this->collection()->owner()->parameters()->has($name)) {
        return $this->collection()->owner()->parameters()->get($name);
      } else {
        return NULL;
      }
    }
    return '';
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
        'name' => $this->_getParameterName($this->getName())
      ),
      ''
    );
  }
}