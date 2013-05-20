<?php
require_once(substr(__FILE__, 0, -68).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsFilePathTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsFilePath::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsFilePath();
    $this->assertInstanceOf('PapayaFilterFilePath', $profile->getFilter());
  }
}