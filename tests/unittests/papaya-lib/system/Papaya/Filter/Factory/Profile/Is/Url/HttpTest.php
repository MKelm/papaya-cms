<?php
require_once(substr(__FILE__, 0, -67).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsUrlHttpTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsUrlHttp::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsUrlHttp();
    $this->assertInstanceOf('PapayaFilterUrlHttp', $profile->getFilter());
  }
}
