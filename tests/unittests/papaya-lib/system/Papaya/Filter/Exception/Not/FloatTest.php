<?php
require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Not/Empty.php');

class PapayaFilterExceptionNotFloatTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionNotFloat::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionNotFloat('abc');
    $this->assertEquals(
      'Value is not a float: abc',
      $e->getMessage()
    );
  }
}
