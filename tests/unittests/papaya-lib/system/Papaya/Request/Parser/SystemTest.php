<?php

require_once(substr(__FILE__, 0, -55).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Url.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser/System.php');

class PapayaRequestParserSystemTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserSystem::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock('PapayaUrl', array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserSystem();
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
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
        '/index.urls',
        array(
          'mode' => 'urls',
        )
      ),
      array(
        '/index.status',
        array(
          'mode' => 'status',
        )
      )
    );
  }
}

?>