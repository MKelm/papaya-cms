<?php

require_once(substr(__FILE__, 0, -53).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Url.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser/Image.php');

class PapayaRequestParserImageTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserImage::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock('PapayaUrl', array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserImage();
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
        '/index.html',
        FALSE
      ),
      array(
        '/testbutton.image.png.preview',
        array(
          'mode' => 'image',
          'preview' => TRUE,
          'image_identifier' => 'testbutton',
          'image_format' => 'png'
        )
      )
    );
  }
}

?>