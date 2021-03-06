<?php
require_once(substr(__FILE__, 0, -63).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Select/Bitmask.php');

class PapayaUiDialogFieldSelectBitmaskTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectBitmask::_createFilter
  */
  public function testConstructorInitializesFilter() {
    $select = new PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $this->assertEquals(
      new PapayaFilterBitmask(array(1, 2)), $select->getFilter()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectBitmask::_createFilter
  */
  public function testConstructorInitializesFilterFromIterator() {
    $select = new PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', new ArrayIterator(array(1 => 'One', 2 => 'Two'))
    );
    $this->assertEquals(
      new PapayaFilterBitmask(array(1, 2)), $select->getFilter()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectBitmask::_createFilter
  */
  public function testConstructorInitializesFilterFromRecursiveIterator() {
    $select = new PapayaUiDialogFieldSelectBitmask(
      'Caption',
      'name',
      new RecursiveArrayIterator(array('group' => array(1 => 'One', 2 => 'Two')))
    );
    $this->assertEquals(
      new PapayaFilterBitmask(array(1, 2)), $select->getFilter()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectBitmask::getDefaultValue
  */
  public function testGetDefaultValue() {
    $select = new PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setDefaultValue('1');
    $this->assertSame(1, $select->getDefaultValue());
  }

  /**
  * @covers PapayaUiDialogFieldSelectBitmask::_isOptionSelected
  */
  public function testAppendTo() {
    $select = new PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelectBitmask" error="no" mandatory="yes">'.
        '<select name="name" type="checkboxes">'.
          '<option value="1">One</option>'.
          '<option value="2">Two</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectBitmask::_isOptionSelected
  */
  public function testAppendToWithSelectedElements() {
    $select = new PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setDefaultValue(3);
    $select->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelectBitmask" error="no" mandatory="yes">'.
        '<select name="name" type="checkboxes">'.
          '<option value="1" selected="selected">One</option>'.
          '<option value="2" selected="selected">Two</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectBitmask::getCurrentValue
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
    $select = new PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(3, $select->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogFieldSelectBitmask::getCurrentValue
  */
  public function testGetCurrentValueWhileDialogWasSendButNoOptionSelected() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array())));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $select = new PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(0, $select->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogFieldSelectBitmask::getCurrentValue
  */
  public function testGetCurrentValueWhileDialogWasNotSend() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array())));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(FALSE));
    $select = new PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->collection($this->getCollectionMock($dialog));
    $this->assertEquals(0, $select->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogFieldSelectBitmask::getCurrentValue
  */
  public function testGetCurrentValueFromDefaultValue() {
    $select = new PapayaUiDialogFieldSelectBitmask(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->setDefaultValue(3);
    $this->assertEquals(3, $select->getCurrentValue());
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
