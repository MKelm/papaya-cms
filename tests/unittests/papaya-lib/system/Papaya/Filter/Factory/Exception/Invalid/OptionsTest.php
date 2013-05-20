<?php
require_once(substr(__FILE__, 0, -73).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryExceptionInvalidOptionsTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryExceptionInvalidOptions
   */
  public function testConstructor() {
    $exception = new PapayaFilterFactoryExceptionInvalidOptions('ExampleProfile');
    $this->assertEquals(
      'Invalid options in filter profile class: "ExampleProfile".',
      $exception->getMessage()
    );
  }

}