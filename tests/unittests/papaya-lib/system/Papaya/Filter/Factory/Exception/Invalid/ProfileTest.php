<?php
require_once(substr(__FILE__, 0, -73).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryExceptionInvalidProfileTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryExceptionInvalidProfile
   */
  public function testConstructor() {
    $exception = new PapayaFilterFactoryExceptionInvalidProfile('ExampleProfile');
    $this->assertEquals(
      'Invalid or unknown filter factory profile: "ExampleProfile".',
      $exception->getMessage()
    );
  }

}