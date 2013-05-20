<?php
require_once(substr(__FILE__, 0, -66).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsIntegerTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsInteger::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsInteger();
    $this->assertInstanceOf('PapayaFilterInteger', $profile->getFilter());
  }
}
