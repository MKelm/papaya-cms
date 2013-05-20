<?php
require_once(substr(__FILE__, 0, -44).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Cast.php');

class PapayaFilterCastTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterCast::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterCast('int');
    $this->assertAttributeEquals(
      'integer', '_type', $filter
    );
  }

  /**
  * @covers PapayaFilterCast::__construct
  */
  public function testConstructorExpectingException() {
    try {
      $filter = new PapayaFilterCast('invalid_type_string');
      $this->fail('And expected exception has not been thrown.');
    } catch (InvalidArgumentException $e) {
      $this->assertEquals(
        '"invalid_type_string" is not a valid type.', $e->getMessage()
      );
    }
  }

  /**
  * @covers PapayaFilterCast::validate
  */
  public function testCheck() {
    $filter = new PapayaFilterCast('int');
    $this->assertTrue($filter->validate(NULL));
  }

  /**
  * @covers PapayaFilterCast::filter
  */
  public function testFilter() {
    $filter = new PapayaFilterCast('int');
    $this->assertSame(42, $filter->filter('42'));
  }
}