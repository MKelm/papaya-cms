<?php
require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterExceptionUndefinedTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionUndefined::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionUndefined();
    $this->assertEquals(
      'Value does not exist.',
      $e->getMessage()
    );
  }
}
