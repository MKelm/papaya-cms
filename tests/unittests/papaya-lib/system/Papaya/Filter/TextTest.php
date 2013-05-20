<?php
require_once(substr(__FILE__, 0, -45).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterTextTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterText::__construct
   */
  public function testConstructorWithOptionsParameter() {
    $filter = new PapayaFilterText(PapayaFilterText::ALLOW_DIGITS);
    $this->assertAttributeEquals(
      PapayaFilterText::ALLOW_DIGITS, '_options', $filter
    );
  }

  /**
   * @covers PapayaFilterText::validate
   * @covers PapayaFilterText::getPattern
   * @dataProvider provideValidValues
   */
  public function testValidateWithValidValuesExpectingTrue(
    $value, $options = PapayaFilterText::ALLOW_SPACES
  ) {
    $filter = new PapayaFilterText($options);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers PapayaFilterText::validate
   * @covers PapayaFilterText::getPattern
   * @dataProvider provideInvalidValues
   */
  public function testValidateWithInvalidValuesExpectingException(
    $value, $options = PapayaFilterText::ALLOW_SPACES
  ) {
    $filter = new PapayaFilterText($options);
    $this->setExpectedException('PapayaFilterExceptionCharacterInvalid');
    $filter->validate($value);
  }

  /**
   * @covers PapayaFilterText::validate
   * @covers PapayaFilterText::getPattern
   */
  public function testValidateWithEmptyValueExpectingException() {
    $filter = new PapayaFilterText();
    $this->setExpectedException('PapayaFilterExceptionEmpty');
    $filter->validate('');
  }

  /**
   * @covers PapayaFilterText::filter
   * @covers PapayaFilterText::getPattern
   * @dataProvider provideFilterValues
   */
  public function testFilterValues(
    $expected, $value, $options = PapayaFilterText::ALLOW_SPACES
  ) {
    $filter = new PapayaFilterText($options);
    $this->assertEquals($expected, $filter->filter($value));
  }

  public static function provideValidValues() {
    return array(
      array('Hello', 0),
      array('Hello World'),
      array('Schöne Welt'),
      array('こんにちは世界'),
      array('مرحبا العالم'),
      array('Hello (first) World!'),
      array('Hello [first] World!'),
      array(',-_'),
      array('Hello 2. World!', PapayaFilterText::ALLOW_SPACES | PapayaFilterText::ALLOW_DIGITS),
      array("foo\nbar", PapayaFilterText::ALLOW_LINES)
    );
  }

  public static function provideInvalidValues() {
    return array(
      array("foo\nbar"),
      array('Hello World', 0),
      array('Hello 2. World!')
    );
  }

  public static function provideFilterValues() {
    return array(
      array(NULL, '123'),
      array('Hello', 'Hello'),
      array('Hello World', 'Hello World'),
      array('HelloWorld', 'Hello World', 0),
      array(
        'Hello 2. World!', 'Hello 2. World!',
        PapayaFilterText::ALLOW_SPACES | PapayaFilterText::ALLOW_DIGITS
      ),
      array('Hello . World!', 'Hello 2. World!'),
      array("foo\nbar", "foo\nbar", PapayaFilterText::ALLOW_LINES),
      array("foobar", "foo\nbar")
    );
  }
}
