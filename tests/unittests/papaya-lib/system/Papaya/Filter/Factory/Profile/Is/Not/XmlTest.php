<?php
require_once(substr(__FILE__, 0, -66).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsNotXmlTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsNotXml::getFilter
   * @dataProvider provideNotXmlStrings
   */
  public function testGetFilterExpectTrue($string) {
    $profile = new PapayaFilterFactoryProfileIsNotXml();
    $this->assertTrue($profile->getFilter()->validate($string));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsNotXml::getFilter
   * @dataProvider provideXmlStrings
   */
  public function testGetFilterExpectException($string) {
    $profile = new PapayaFilterFactoryProfileIsNotXml();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate($string);
  }

  public static function provideNotXmlStrings() {
    return array(
      array('foo'),
      array('foo "bar"')
    );
  }

  public static function provideXmlStrings() {
    return array(
      array('<'),
      array('>'),
      array('&')
    );
  }
}
