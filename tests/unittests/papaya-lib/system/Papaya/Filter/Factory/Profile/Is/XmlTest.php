<?php
require_once(substr(__FILE__, 0, -62).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsXmlTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsXml::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsXml();
    $this->assertInstanceOf('PapayaFilterXml', $profile->getFilter());
  }
}
