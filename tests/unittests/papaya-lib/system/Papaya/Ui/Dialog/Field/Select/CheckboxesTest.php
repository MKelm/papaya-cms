<?php
require_once(substr(__FILE__, 0, -66).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Select/Checkboxes.php');

class PapayaUiDialogFieldSelectCheckboxesTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectCheckboxes::_isOptionSelected
  * @covers PapayaUiDialogFieldSelectCheckboxes::_createFilter
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $select = new PapayaUiDialogFieldSelectCheckboxes(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setMandatory(FALSE);
    $select->papaya($this->getMockApplicationObject());
    $select->appendTo($node);
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelectCheckboxes" error="no">'.
        '<select name="name" type="checkboxes">'.
          '<option value="1">One</option>'.
          '<option value="2">Two</option>'.
        '</select>'.
      '</field>',
      $dom->saveXml($node->firstChild)
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectCheckboxes::_isOptionSelected
  * @covers PapayaUiDialogFieldSelectCheckboxes::_createFilter
  */
  public function testAppendToWithSelectedElements() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $select = new PapayaUiDialogFieldSelectCheckboxes(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setDefaultValue(array(1, 2));
    $select->papaya($this->getMockApplicationObject());
    $select->appendTo($node);
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelectCheckboxes" error="no" mandatory="yes">'.
        '<select name="name" type="checkboxes">'.
          '<option value="1" selected="selected">One</option>'.
          '<option value="2" selected="selected">Two</option>'.
        '</select>'.
      '</field>',
      $dom->saveXml($node->firstChild)
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectCheckboxes::_isOptionSelected
  * @covers PapayaUiDialogFieldSelectCheckboxes::_createFilter
  */
  public function testAppendToWithIterator() {
    $select = new PapayaUiDialogFieldSelectCheckboxes(
      'Caption', 'name', new ArrayIterator(array(1 => 'One', 2 => 'Two'), TRUE)
    );
    $select->setDefaultValue(array(1, 2));
    $select->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelectCheckboxes" error="no" mandatory="yes">'.
        '<select name="name" type="checkboxes">'.
          '<option value="1" selected="selected">One</option>'.
          '<option value="2" selected="selected">Two</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectCheckboxes::getCurrentValue
  */
  public function testGetCurrentValueFromDialogParameters() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('name' => array(1, 2)))));
    $select = new PapayaUiDialogFieldSelectCheckboxes(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(array(1, 2), $select->getCurrentValue());
  }

  /*************************
  * Mocks
  *************************/

  public function getCollectionMock($owner = NULL) {
    $collection = $this->getMock('PapayaUiDialogFields');
    if ($owner) {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->any())
        ->method('owner')
        ->will($this->returnValue($owner));
    } else {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(FALSE));
    }
    return $collection;
  }
}
