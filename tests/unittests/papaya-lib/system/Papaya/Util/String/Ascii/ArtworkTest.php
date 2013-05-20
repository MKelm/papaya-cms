<?php
require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Util/String/Ascii/Artwork.php');

class PapayaUtilStringAsciiArtworkTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringAsciiArtwork::get
  * @dataProvider getDataProvider
  */
  public function testGet($string, $fileName) {
    $this->assertStringEqualsFile(
      dirname(__FILE__).'/TestData/'.$fileName,
      PapayaUtilStringAsciiArtwork::get($string)
    );
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function getDataProvider() {
    return array(
      'ascii' => array('ascii', 'ascii.txt'),
      'numbers' => array('0123456789', 'numbers.txt'),
      'letters' => array('abcdefghijklmnopqrstuvwxyz', 'letters.txt'),
      'special chars' => array('-+:', 'special.txt')
    );
  }
}