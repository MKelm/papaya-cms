<?php
require_once(substr(__FILE__, 0, -72).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsIpAddressV4Test extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsIpAddressV4::getFilter
   */
  public function testGetFilterWithIpV4AddressExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsIpAddressV4();
    $this->assertTrue($profile->getFilter()->validate('127.0.0.1'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsIpAddressV4::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsIpAddressV4();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('foo');
  }
}
