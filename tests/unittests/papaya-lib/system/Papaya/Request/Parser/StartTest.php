<?php

require_once(substr(__FILE__, 0, -53).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Url.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser/Start.php');

class PapayaRequestParserStartTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserStart::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock('PapayaUrl', array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserStart();
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
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'page_title' => 'index'
        )
      ),
      array(
        '/index.html.preview',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'preview' => TRUE,
          'page_title' => 'index'
        )
      ),
      array(
        '/index.html.preview.1240848952',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'preview' => TRUE,
          'preview_time' => 1240848952,
          'page_title' => 'index'
        )
      ),
      array(
        '/forum.5.html',
        FALSE
      ),
      array(
        '/index.de.html',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'page_title' => 'index',
          'language' => 'de'
        )
      ),
      array(
        '/foobar.rss',
        array(
          'mode' => 'page',
          'output_mode' => 'rss',
          'page_title' => 'foobar',
        )
      ),
      array(
        '/index.rss',
        array(
          'mode' => 'page',
          'output_mode' => 'rss',
          'page_title' => 'index',
        )
      ),
      array(
        '/index.de.rss',
        array(
          'mode' => 'page',
          'output_mode' => 'rss',
          'page_title' => 'index',
          'language' => 'de'
        )
      )
    );
  }
}

?>