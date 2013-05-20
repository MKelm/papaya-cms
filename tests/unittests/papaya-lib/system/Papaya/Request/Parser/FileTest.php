<?php

require_once(substr(__FILE__, 0, -53).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Url.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser/File.php');

class PapayaRequestParserFileTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserFile::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock('PapayaUrl', array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserFile();
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
  }

  /**
  * @covers PapayaRequestParserFile::isLast
  */
  public function testIsLast() {
    $parser = new PapayaRequestParserFile();
    $this->assertFalse($parser->isLast());
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function parseDataProvider() {
    return array(
      array(
        '',
        FALSE
      ),
      array(
        '/',
        array(
          'file_path' => '/',
        )
      ),
      array(
        '/index.html',
        array(
          'file_path' => '/',
          'file_name' => 'index.html',
          'file_title' => 'index',
          'file_extension' => 'html',
        )
      ),
      array(
        '/sidb57dae676543ce5717fa20ed6c3d5476/index.5.en.html',
        array(
          'file_path' => '/',
          'file_name' => 'index.5.en.html',
          'file_title' => 'index',
          'file_extension' => 'html',
        )
      ),
      array(
        '/sidb57dae676543ce5717fa20ed6c3d5476/index.5.en.html.preview',
        array(
          'file_path' => '/',
          'file_name' => 'index.5.en.html.preview',
          'file_title' => 'index',
          'file_extension' => 'html',
        )
      ),
    );
  }
}

?>