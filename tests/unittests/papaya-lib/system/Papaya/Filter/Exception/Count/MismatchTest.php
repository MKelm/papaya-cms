<?php

require_once(dirname(__FILE__).'/../../../../../../Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Count/Mismatch.php');

class PapayaFilterExceptionCountMismatchTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionCountMismatch::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionCountMismatch(2, 1, 'type');
    $this->assertEquals(
      '2 element(s) of type "type" expected, 1 found.',
      $e->getMessage()
    );
  }

}