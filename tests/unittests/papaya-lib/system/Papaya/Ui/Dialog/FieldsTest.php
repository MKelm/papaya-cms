<?php
require_once(substr(__FILE__, 0, -49).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Fields.php');

class PapayaUiDialogFieldsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFields::validate
  */
  public function testValidateExpectingTrue() {
    $fieldOne = $this->getMockField();
    $fieldOne
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fieldTwo = $this->getMockField();
    $fieldTwo
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fields = new PapayaUiDialogFields();
    $fields->add($fieldOne);
    $fields->add($fieldTwo);
    $this->assertTrue($fields->validate());
  }

  /**
  * @covers PapayaUiDialogFields::validate
  */
  public function testValidateExpectingFalse() {
    $fieldOne = $this->getMockField();
    $fieldOne
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $fieldTwo = $this->getMockField();
    $fieldTwo
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fields = new PapayaUiDialogFields();
    $fields->add($fieldOne);
    $fields->add($fieldTwo);
    $this->assertFalse($fields->validate());
  }

  private function getMockField() {
    $item = $this->getMock(
      'PapayaUiDialogField', array('collection', 'index', 'appendTo', 'validate')
    );
    return $item;
  }
}