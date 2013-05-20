<?php
require_once(substr(__FILE__, 0, -53).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiReferenceFactoryTest extends PapayaTestCase {

  /**
   * @covers PapayaUiReferenceFactory
   * @dataProvider provideStringsAndExpectedUrls
   */
  public function testByString($expected, $string) {
    $factory = new PapayaUiReferenceFactory();
    $factory->papaya($this->getMockApplicationObject());
    $reference = $factory->byString($string);
    $this->assertEquals($expected, (string)$reference);
  }

  /*******************************
   * Data Provider
   ******************************/

  public static function provideStringsAndExpectedUrls() {
    return array(
      array('http://www.test.tld/test.html', ''),
      array('http://www.papaya-cms.com', 'http://www.papaya-cms.com'),
      array('http://www.test.tld/foo/bar', '/foo/bar'),
      array('http://www.test.tld/foo/bar', 'foo/bar'),
      array('http://www.test.tld/index.42.html', '42'),
      array('http://www.test.tld/index.21.42.html', '21.42'),
      array('http://www.test.tld/index.21.42.en.html', '21.42.en'),
      array('http://www.test.tld/index.21.42.en.atom', '21.42.en.atom')
    );
  }
}