<?php
require_once(substr(__FILE__, 0, -64).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Range/Minimum.php');

class PapayaFilterExceptionRangeMinimumTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionRangeMinimum::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionRangeMinimum(42, 21);
    $this->assertEquals(
      'Value is to small. Expecting a minimum of "42", got "21".',
      $e->getMessage()
    );
  }
}
