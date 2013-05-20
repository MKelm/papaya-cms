<?php
require_once(substr(__FILE__, 0, -67).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsUrlHostTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsUrlHost::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsUrlHost();
    $this->assertInstanceOf('PapayaFilterUrlHost', $profile->getFilter());
  }
}
