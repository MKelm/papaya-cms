<?php
require_once(substr(__FILE__, 0, -56).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Range.php');

class PapayaFilterExceptionRangeTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionRange::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionRange_TestProxy('Range Error', 42, 21);
    $this->assertEquals(
      'Range Error',
      $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionRange::getExpectedLimit
  */
  public function testGetExpectedLimit() {
    $e = new PapayaFilterExceptionRange_TestProxy('Range Error', 42, 21);
    $this->assertEquals(
      42,
      $e->getExpectedLimit()
    );
  }

  /**
  * @covers PapayaFilterExceptionRange::getActualValue
  */
  public function testgetActualValue() {
    $e = new PapayaFilterExceptionRange_TestProxy('Range Error', 42, 21);
    $this->assertEquals(
      21,
      $e->getActualValue()
    );
  }
}

class PapayaFilterExceptionRange_TestProxy extends PapayaFilterExceptionRange {

}
