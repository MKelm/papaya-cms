<?php
require_once(substr(__FILE__, 0, -49).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Option/Align.php');

class PapayaUiOptionAlignTest extends PapayaTestCase {

  /**
  * @covers PapayaUiOptionAlign::getString
  */
  public function testGetString() {
    $this->assertEquals(
      'center',
      PapayaUiOptionAlign::getString(PapayaUiOptionAlign::CENTER)
    );
  }

  /**
  * @covers PapayaUiOptionAlign::getString
  */
  public function testGetStringWithInvalidValueExpectingLeft() {
    $this->assertEquals(
      'left',
      PapayaUiOptionAlign::getString(-42)
    );
  }

  /**
  * @covers PapayaUiOptionAlign::validate
  */
  public function testValidate() {
    $this->assertTrue(
      PapayaUiOptionAlign::validate(PapayaUiOptionAlign::CENTER)
    );
  }

  /**
  * @covers PapayaUiOptionAlign::validate
  */
  public function testValidateWithInvalidValue() {
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: Invalid align value "-42".'
    );
    PapayaUiOptionAlign::validate(-42);
  }

  /**
  * @covers PapayaUiOptionAlign::validate
  */
  public function testValidateWithInvalidValueAndIndividualMessage() {
    $this->setExpectedException(
      'InvalidArgumentException',
      'Failed.'
    );
    PapayaUiOptionAlign::validate(-42, 'Failed.');
  }
}