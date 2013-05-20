<?php
require_once(substr(__FILE__, 0, -63).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsTextTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsText::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsText();
    $this->assertTrue($profile->getFilter()->validate('Hallo Welt!'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsText::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsText();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('123');
  }
}
