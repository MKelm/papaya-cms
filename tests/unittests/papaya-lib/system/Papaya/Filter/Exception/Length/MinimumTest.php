<?php
require_once(substr(__FILE__, 0, -65).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Length/Minimum.php');

class PapayaFilterExceptionLengthMinimumTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionLengthMinimum::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionLengthMinimum(42, 21);
    $this->assertEquals(
      'Value is too short. Expecting a minimum of 42 bytes, got 21.',
      $e->getMessage()
    );
  }
}
