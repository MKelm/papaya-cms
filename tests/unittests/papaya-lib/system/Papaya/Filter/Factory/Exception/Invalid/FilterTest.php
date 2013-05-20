<?php
require_once(substr(__FILE__, 0, -72).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryExceptionInvalidFilterTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryExceptionInvalidFilter
   */
  public function testConstructor() {
    $exception = new PapayaFilterFactoryExceptionInvalidFilter('ExampleFilter');
    $this->assertEquals(
      'Can not use invalid filter class: "ExampleFilter".',
      $exception->getMessage()
    );
  }

}