<?php
require_once(substr(__FILE__, 0, -57).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Util/String/Hyphenation.php');

class PapayaUtilStringHyphenationTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringHyphenation::german
  * @dataProvider provideGermanWords
  */
  public function testGerman($expected, $word) {
    $this->assertEquals(
      $expected, PapayaUtilStringHyphenation::german($word)
    );
  }

  /********************************
  * Data Provider
  ********************************/

  public static function provideGermanWords() {
    return array(
      array('meis-tens', 'meistens'),
      array('Kis-ten', 'Kisten'),
      array('Es-pe', 'Espe'),
      array('Mas-ke', 'Maske'),
      array('Zu-cker', 'Zucker'),
      array('Quad-rat' , 'Quadrat'),
      array('beo-bachten', 'beobachten')
    );
  }
}