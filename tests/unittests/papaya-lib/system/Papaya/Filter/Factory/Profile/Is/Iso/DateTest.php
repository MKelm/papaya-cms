<?php
require_once(substr(__FILE__, 0, -67).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsIsoDateTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsIsoDate::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsIsoDate();
    $this->assertTrue($profile->getFilter()->validate('2012-08-15'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsIsoDate::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsIsoDate();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('foo');
  }
}
