<?php

require_once(substr(__FILE__, 0, -53).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Url.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser/Page.php');

class PapayaRequestParserPageTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParserPage::parse
  * @dataProvider parseDataProvider
  */
  public function testParse($path, $expected) {
    $url = $this->getMock('PapayaUrl', array('getPath'));
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserPage();
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
        '/forum.5.html',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'page_id' => 5,
          'page_title' => 'forum'
        )
      ),
      array(
        '/forum.5.en.html',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'page_id' => 5,
          'page_title' => 'forum',
          'language' => 'en'
        )
      ),
      array(
        '/forum.5.html.preview',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'preview' => TRUE,
          'page_id' => 5,
          'page_title' => 'forum',
        )
      ),
      array(
        '/catalog.25.5.en.pdf',
        array(
          'mode' => 'page',
          'output_mode' => 'pdf',
          'page_id' => 5,
          'page_title' => 'catalog',
          'language' => 'en',
          'category_id' => 25
        )
      ),
      array(
        '/index.35.en.html.preview',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'preview' => TRUE,
          'page_id' => 35,
          'page_title' => 'index',
          'language' => 'en'
        )
      ),
      array(
        '/index.6.en.html.preview.1240848952',
        array(
          'mode' => 'page',
          'output_mode' => 'html',
          'preview' => TRUE,
          'preview_time' => 1240848952,
          'page_id' => 6,
          'page_title' => 'index',
          'language' => 'en'
        )
      )
    );
  }
}

?>