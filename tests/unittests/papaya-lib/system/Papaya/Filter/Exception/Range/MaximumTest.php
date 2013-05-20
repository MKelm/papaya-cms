<?php
require_once(substr(__FILE__, 0, -64).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Range/Maximum.php');

class PapayaFilterExceptionRangeMaximumTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionRangeMaximum::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionRangeMaximum(21, 42);
    $this->assertEquals(
      'Value is to large. Expecting a maximum of "21", got "42".',
      $e->getMessage()
    );
  }
}
