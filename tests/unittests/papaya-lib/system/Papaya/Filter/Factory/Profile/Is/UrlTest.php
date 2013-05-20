<?php
require_once(substr(__FILE__, 0, -62).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsUrlTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsUrl::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsUrl();
    $this->assertTrue($profile->getFilter()->validate('http://sample.tld/path/file.html?foo=bar'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsUrl::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsUrl();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('foo');
  }
}
