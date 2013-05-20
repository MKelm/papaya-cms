<?php
require_once(substr(__FILE__, 0, -58).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Invalid.php');

class PapayaFilterExceptionInvalidTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionInvalid::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionInvalid('foo');
    $this->assertEquals(
      'Invalid value "foo".',
      $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionInvalid::getActualValue
  */
  public function testGetPattern() {
    $e = new PapayaFilterExceptionInvalid('foo');
    $this->assertEquals(
      'foo',
      $e->getActualValue()
    );
  }
}
