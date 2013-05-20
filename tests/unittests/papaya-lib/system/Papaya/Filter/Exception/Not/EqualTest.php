<?php
require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Not/Equal.php');

class PapayaFilterExceptionNotEqualTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionNotEqual::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionNotEqual('42');
    $this->assertEquals(
      'Value does not equal comparsion value. Expected "42".',
      $e->getMessage()
    );
  }
}
