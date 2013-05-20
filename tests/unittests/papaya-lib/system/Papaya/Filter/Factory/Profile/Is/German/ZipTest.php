<?php
require_once(substr(__FILE__, 0, -69).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsGermanZipTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsGermanZip::getFilter
   * @dataProvider provideValidZips
   */
  public function testGetFilterExpectTrue($zip) {
    $profile = new PapayaFilterFactoryProfileIsGermanZip();
    $this->assertTrue($profile->getFilter()->validate($zip));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsGermanZip::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsGermanZip();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('foo');
  }

  public static function provideValidZips() {
    return array(
      array('50670'),
      array('D-50670')
    );
  }
}
