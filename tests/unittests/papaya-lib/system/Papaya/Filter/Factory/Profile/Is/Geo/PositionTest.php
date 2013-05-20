<?php
require_once(substr(__FILE__, 0, -71).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsGeoPositionTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsGeoPosition::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsGeoPosition();
    $this->assertInstanceOf('PapayaFilterGeoPosition', $profile->getFilter());
  }
}
