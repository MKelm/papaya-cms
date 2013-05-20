<?php
require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileRegexTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileRegex::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileRegex();
    $profile->options('(^pattern$)D');
    $filter = $profile->getFilter();
    $this->assertInstanceOf('PapayaFilterPcre', $filter);
    $this->assertTrue($filter->validate('pattern'));
  }
}