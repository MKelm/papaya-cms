<?php
require_once(substr(__FILE__, 0, -67).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Callback/Invalid.php');

class PapayaFilterExceptionCallbackInvalidTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionCallbackInvalid::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionCallbackInvalid('strpos');
    $this->assertEquals(
      'Invalid callback specified: "strpos"',
      $e->getMessage()
    );
  }
}
