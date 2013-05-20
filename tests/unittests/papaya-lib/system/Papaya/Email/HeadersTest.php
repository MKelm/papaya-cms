<?php
require_once(substr(__FILE__, 0, -47).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaEmailHeadersTest extends PapayaTestCase {

  /**
  * @covers PapayaEmailHeaders
  */
  public function testConstruct() {
    $object = new PapayaEmailHeaders();
    $this->assertInstanceOf('PapayaHttpHeaders', $object);
  }

}
