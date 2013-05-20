<?php
require_once(substr(__FILE__, 0, -63).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsTimeTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsTime::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsTime();
    $this->assertTrue($profile->getFilter()->validate('23:54'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsTime::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsTime();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('foo');
  }
}
