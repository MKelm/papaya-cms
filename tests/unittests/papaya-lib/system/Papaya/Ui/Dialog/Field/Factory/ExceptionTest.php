<?php
require_once(substr(__FILE__, 0, -67).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryExceptionTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryException
   */
  public function testThrowException() {
    $this->setExpectedException('PapayaUiDialogFieldFactoryException');
    throw new PapayaUiDialogFieldFactoryException_TestProxy();
  }

}

class PapayaUiDialogFieldFactoryException_TestProxy extends PapayaUiDialogFieldFactoryException {

  public function getFilter() {
  }
}
