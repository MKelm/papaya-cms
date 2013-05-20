<?php
require_once(substr(__FILE__, 0, -62).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldInputCountedTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldInputCounted
  */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldInputCounted('Caption', 'fieldname', 42, TRUE);
    $this->assertEquals('counted', $field->getType());
  }
}