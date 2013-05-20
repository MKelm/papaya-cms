<?php
require_once(substr(__FILE__, 0, -46).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Util/Bitwise.php');

class PapayaUtilBitwiseTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilBitwise::inBitmask
  * @dataProvider provideInBitmaskPositiveData
  */
  public function testInBitmaskExpectingTrue($bit, $bitmask) {
    $this->assertTrue(
      PapayaUtilBitwise::inBitmask($bit, $bitmask)
    );
  }

  /**
  * @covers PapayaUtilBitwise::inBitmask
  * @dataProvider provideInBitmaskNegativeData
  */
  public function testInBitmaskExpectingFalse($bit, $bitmask) {
    $this->assertFalse(
      PapayaUtilBitwise::inBitmask($bit, $bitmask)
    );
  }

  /****************************************
  * Data Provider
  ****************************************/

  public static function provideInBitmaskPositiveData() {
    return array(
      array(0, 0),
      array(1, 3),
      array(2, 6),
      array(2, 7),
      array(1, 129)
    );
  }

  public static function provideInBitmaskNegativeData() {
    return array(
      array(1, 0),
      array(1, 6),
      array(2, 4),
      array(2, 128)
    );
  }
}