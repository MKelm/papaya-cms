<?php
require_once(substr(__FILE__, 0, -57).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryExceptionTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryException
   */
  public function testThrowException() {
    $this->setExpectedException('PapayaFilterFactoryException');
    throw new PapayaFilterFactoryException_TestProxy();
  }

}

class PapayaFilterFactoryException_TestProxy extends PapayaFilterFactoryException {

  public function getFilter() {
  }
}
