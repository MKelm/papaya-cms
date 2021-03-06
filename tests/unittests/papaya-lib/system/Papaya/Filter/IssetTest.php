<?php
require_once(substr(__FILE__, 0, -45).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterIssetTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterIsset::validate
  * @dataProvider provideValues
  */
  public function testCheck($value) {
    $filter = new PapayaFilterIsset();
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterIsset::validate
  */
  public function testCheckExpectingException() {
    $filter = new PapayaFilterIsset();
    $this->setExpectedException('PapayaFilterExceptionUndefined');
    $filter->validate(NULL);
  }

  /**
  * @covers PapayaFilterIsset::filter
  * @dataProvider provideValues
  */
  public function testFilter($value) {
    $filter = new PapayaFilterIsset();
    $this->assertSame($value, $filter->filter($value));
  }

  /************************
  * Data Provider
  ************************/

  public static function provideValues() {
    return array(
      array(''),
      array(' '),
      array('0'),
      array(array()),
      array('some'),
      array('0'),
      array(array('0'))
    );
  }
}