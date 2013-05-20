<?php
require_once(substr(__FILE__, 0, -64).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsFloatTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsFloat::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsFloat();
    $this->assertInstanceOf('PapayaFilterFloat', $profile->getFilter());
  }
}
