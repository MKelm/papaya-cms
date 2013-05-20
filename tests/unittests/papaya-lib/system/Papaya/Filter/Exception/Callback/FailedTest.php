<?php
require_once(substr(__FILE__, 0, -66).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Callback/Failed.php');

class PapayaFilterExceptionCallbackFailedTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionCallbackFailed::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionCallbackFailed('strpos');
    $this->assertEquals(
      'Callback has failed: "strpos"',
      $e->getMessage()
    );
  }
}
