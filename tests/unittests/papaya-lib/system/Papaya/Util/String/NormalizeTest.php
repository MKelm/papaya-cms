<?php
require_once(substr(__FILE__, 0, -55).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Util/String/Normalize.php');

class PapayaUtilStringNormalizeTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringNormalize::toHttpHeaderName
  * @dataProvider toHttpHeaderNameDataProvider
  */
  public function testToHttpHeaderName($string, $expected) {
    $this->assertEquals(
      $expected,
      PapayaUtilStringNormalize::toHttpHeaderName($string)
    );
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function toHttpHeaderNameDataProvider() {
    return array(
      'lower case' => array('content-type', 'Content-Type'),
      'upper case' => array('CONTENT-TYPE', 'Content-Type'),
      'mixed case' => array('CoNtEnT-TyPe', 'Content-Type'),
      'single word' => array('cache', 'Cache')
    );
  }
}

?>