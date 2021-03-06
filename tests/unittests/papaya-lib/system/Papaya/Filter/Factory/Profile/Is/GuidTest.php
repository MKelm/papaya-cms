<?php
require_once(substr(__FILE__, 0, -63).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsGuidTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsGuid::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsGuid();
    $this->assertTrue($profile->getFilter()->validate('ab123456789012345678901234567890'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsGuid::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsGuid();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('foo');
  }
}
