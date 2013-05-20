<?php
require_once(substr(__FILE__, 0, -83).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryExceptionInvalidProfileTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryExceptionInvalidProfile::__construct
   */
  public function testConstructor() {
    $exception = new PapayaUiDialogFieldFactoryExceptionInvalidProfile('SampleProfileName');
    $this->assertEquals(
      'Invalid field factory profile name "SampleProfileName".',
      $exception->getMessage()
    );
  }
}