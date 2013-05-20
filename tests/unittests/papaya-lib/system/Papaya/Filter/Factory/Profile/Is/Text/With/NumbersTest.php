<?php
require_once(substr(__FILE__, 0, -76).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsTextWithNumbersTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsTextWithNumbers::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsTextWithNumbers();
    $this->assertTrue($profile->getFilter()->validate('Hallo 1. Welt!'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsTextWithNumbers::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsTextWithNumbers();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('');
  }
}
