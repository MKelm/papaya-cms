<?php
require_once(substr(__FILE__, 0, -67).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsPasswordTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsPassword::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsPassword();
    $this->assertInstanceOf('PapayaFilterPassword', $profile->getFilter());
  }
}
