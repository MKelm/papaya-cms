<?php
require_once(substr(__FILE__, 0, -65).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Length/Maximum.php');

class PapayaFilterExceptionLengthMaximumTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionLengthMaximum::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionLengthMaximum(21, 42);
    $this->assertEquals(
      'Value is too long. Expecting a maximum of 21 bytes, got 42.',
      $e->getMessage()
    );
  }
}
