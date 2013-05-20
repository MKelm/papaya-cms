<?php
require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Not/Empty.php');

class PapayaFilterExceptionNotEmptyTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionNotEmpty::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionNotEmpty('42');
    $this->assertEquals(
      'Value is to not empty. Got "42".',
      $e->getMessage()
    );
  }
}
