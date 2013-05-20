<?php
require_once(substr(__FILE__, 0, -82).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryExceptionInvalidOptionTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryExceptionInvalidOption::__construct
   */
  public function testConstructor() {
    $exception = new PapayaUiDialogFieldFactoryExceptionInvalidOption('OptionName');
    $this->assertEquals(
      'Invalid field factory option name "OptionName".',
      $exception->getMessage()
    );
  }
}