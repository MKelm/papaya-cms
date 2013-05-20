<?php
require_once(substr(__FILE__, 0, -68).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsCssColorTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsCssColor::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsCssColor();
    $this->assertInstanceOf('PapayaFilterColor', $profile->getFilter());
  }
}
