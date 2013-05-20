<?php
require_once(substr(__FILE__, 0, -64).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsPhoneTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsPhone::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsPhone();
    $this->assertInstanceOf('PapayaFilterPhone', $profile->getFilter());
  }
}
