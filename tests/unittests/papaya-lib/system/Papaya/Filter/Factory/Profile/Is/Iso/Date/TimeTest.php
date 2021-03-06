<?php
require_once(substr(__FILE__, 0, -72).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileIsIsoDateTimeTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsIsoDateTime::getFilter
   * @dataProvider provideValidDatetimeStrings
   */
  public function testGetFilterExpectTrue($datetime) {
    $profile = new PapayaFilterFactoryProfileIsIsoDateTime();
    $this->assertTrue($profile->getFilter()->validate($datetime));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsIsoDateTime::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsIsoDateTime();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('foo');
  }

  public static function provideValidDatetimeStrings() {
    return array(
      array('2012-08-15 13:37'),
      array('2012-08-15T13:37'),
      array('2012-08-15T13:37+0200')
    );
  }
}
