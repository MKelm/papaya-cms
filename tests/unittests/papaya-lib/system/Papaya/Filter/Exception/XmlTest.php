<?php
require_once(substr(__FILE__, 0, -54).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterExceptionXmlTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterExceptionXml
   */
  public function testConstructor() {
    $error = new libxmlError();
    $error->code = 23;
    $error->message = 'libxml fatal error sample';
    $error->line = 42;
    $error->column = 21;
    $error->file = '';

    $exception = new PapayaFilterExceptionXml(new PapayaXmlException($error));
    $this->assertNotEmpty($exception->getMessage());
  }
}