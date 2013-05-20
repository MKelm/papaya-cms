<?php
require_once(substr(__FILE__, 0, -68).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsFileNameTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsFileName::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsFileName();
    $this->assertInstanceOf('PapayaFilterFileName', $profile->getFilter());
  }
}