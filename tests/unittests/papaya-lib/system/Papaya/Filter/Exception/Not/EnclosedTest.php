<?php
require_once(substr(__FILE__, 0, -63).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Not/Enclosed.php');

class PapayaFilterExceptionNotEnclosedTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionNotEnclosed::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionNotEnclosed(42);
    $this->assertEquals(
      'Value is to not enclosed in list of valid elements. Got "42".',
      $e->getMessage()
    );
  }
}
