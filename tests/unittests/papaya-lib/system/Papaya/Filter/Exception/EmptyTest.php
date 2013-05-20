<?php
require_once(substr(__FILE__, 0, -56).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Empty.php');

class PapayaFilterExceptionEmptyTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionEmpty::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionEmpty();
    $this->assertEquals(
      'Value is empty.',
      $e->getMessage()
    );
  }
}
