<?php
require_once(substr(__FILE__, 0, -64).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsEmailTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsEmail::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsEmail();
    $this->assertInstanceOf('PapayaFilterEmail', $profile->getFilter());
  }
}
