<?php
require_once(substr(__FILE__, 0, -62).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/String/Transliteration/Ascii.php');

class PapayaStringTransliterationAsciiTest extends PapayaTestCase {

  /**
  * @covers PapayaStringTransliterationAscii::transliterate
  * @covers PapayaStringTransliterationAscii::mapCharacterMatch
  * @dataProvider provideTransliterationExamples
  */
  public function testTransliterate($expected, $string, $language) {
    $transliterator = new PapayaStringTransliterationAscii();
    $this->assertEquals(
      $expected, $transliterator->transliterate($string, $language)
    );
  }

  /**
  * @covers PapayaStringTransliterationAscii::mapping
  */
  public function testMappingGetAfterSet() {
    $mapping = $this->getMock('PapayaStringTransliterationAsciiMapping');
    $transliteratorOne = new PapayaStringTransliterationAscii();
    $transliteratorTwo = new PapayaStringTransliterationAscii();
    $transliteratorOne->mapping($mapping);
    $this->assertSame($mapping, $transliteratorTwo->mapping());
  }

  /**
  * @covers PapayaStringTransliterationAscii::mapping
  * @covers PapayaStringTransliterationAscii::resetMapping
  */
  public function testMappingImplicitCreate() {
    $transliterator = new PapayaStringTransliterationAscii();
    $mappingOne = $transliterator->mapping();
    $transliterator->resetMapping();
    $mappingTwo = $transliterator->mapping();
    $this->assertInstanceOf('PapayaStringTransliterationAsciiMapping', $mappingTwo);
    $this->assertNotSame($mappingOne, $mappingTwo);
  }

  public static function provideTransliterationExamples() {
    return array(
      'ascii chars' => array('abcd', 'abcd', ''),
      'umlaut generic' => array('aAoOuU', 'äÄöÖüÜ', ''),
      'umlaut english' => array('aAoOuU', 'äÄöÖüÜ', 'en'),
      'specific german' => array('aeAeoeOeueUe', 'äÄöÖüÜ', 'de'),
      'cyrillic' => array('Russkii', 'Русский', ''),
      'symbol registered' => array('(r)', '®', ''),
      'symbol jing jang' => array(' ', '☯', ''),
      'symbol love' => array('[?]', '♥', '')
    );
  }
}