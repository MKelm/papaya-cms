<?php

require_once(substr(__FILE__, 0, -58).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Url.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser/Thumbnail.php');

class PapayaRequestParserThumbnailTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserThumbnail::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock('PapayaUrl', array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserThumbnail();
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function parseDataProvider() {
    // @codingStandardsIgnoreStart
    return array(
      array(
        '/index.html',
        FALSE
      ),
      array(
        '/title.thumb.1897806da87c1b264444a5e685e76c3d_max_510x480.png',
        array(
          'mode' => 'thumbnail',
          'media_id' => '1897806da87c1b264444a5e685e76c3d',
          'media_uri' => '1897806da87c1b264444a5e685e76c3d_max_510x480.png',
          'thumbnail_mode' => 'max',
          'thumbnail_size' => '510x480',
          'thumbnail_format' => 'png'
        )
      ),
      array(
        '/title.thumb.1897806da87c1b264444a5e685e76c3dv23_max_510x480.png',
        array(
          'mode' => 'thumbnail',
          'media_id' => '1897806da87c1b264444a5e685e76c3d',
          'media_uri' => '1897806da87c1b264444a5e685e76c3dv23_max_510x480.png',
          'media_version' => 23,
          'thumbnail_mode' => 'max',
          'thumbnail_size' => '510x480',
          'thumbnail_format' => 'png'
        )
      ),
      array(
        '/title.thumb.1897806da87c1b264444a5e685e76c3dv23_max_510x480_b3535db83dc50e27c1bb1392364c95a2.png',
        array(
          'mode' => 'thumbnail',
          'media_id' => '1897806da87c1b264444a5e685e76c3d',
          'media_uri' => '1897806da87c1b264444a5e685e76c3dv23_max_510x480_b3535db83dc50e27c1bb1392364c95a2.png',
          'media_version' => 23,
          'thumbnail_mode' => 'max',
          'thumbnail_size' => '510x480',
          'thumbnail_params' => 'b3535db83dc50e27c1bb1392364c95a2',
          'thumbnail_format' => 'png'
        )
      ),
      array(
        '/hn999sramon-7esrp-tours5.thumb.preview.d7e21e7a82c200090aa0e29327ad4581v23_max_200x150_b3535db83dc50e27c1bb1392364c95a2.png',
        array(
          'mode' => 'thumbnail',
          'preview' => TRUE,
          'media_id' => 'd7e21e7a82c200090aa0e29327ad4581',
          'media_uri' => 'd7e21e7a82c200090aa0e29327ad4581v23_max_200x150_b3535db83dc50e27c1bb1392364c95a2.png',
          'media_version' => 23,
          'thumbnail_mode' => 'max',
          'thumbnail_size' => '200x150',
          'thumbnail_params' => 'b3535db83dc50e27c1bb1392364c95a2',
          'thumbnail_format' => 'png'
        )
      )
    );
    // @codingStandardsIgnoreEnd
  }
}

?>