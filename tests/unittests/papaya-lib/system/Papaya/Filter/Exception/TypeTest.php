<?php
require_once(substr(__FILE__, 0, -55).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Type.php');

class PapayaFilterExceptionTypeTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionType::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionType('integer number');
    $this->assertEquals(
      'Value is not a "integer number".',
      $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionType::getExpectedType
  */
  public function testGetExpectedType() {
    $e = new PapayaFilterExceptionType('integer number');
    $this->assertEquals(
      'integer number',
      $e->getExpectedType()
    );
  }
}
