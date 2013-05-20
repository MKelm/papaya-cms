<?php
require_once(substr(__FILE__, 0, -63).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Part/Invalid.php');

class PapayaFilterExceptionPartInvalidTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionPartInvalid::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionPartInvalid(
      3,
      'type',
      'Value is too large. Expecting a maximum of "21", got "42".'
    );
    $this->assertEquals(
      'Part number 3 of type "type" is invalid:'.
        ' Value is too large. Expecting a maximum of "21", got "42".',
      $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionPartInvalid::__construct
  */
  public function testConstructorNoMessage() {
    $e = new PapayaFilterExceptionPartInvalid(3, 'type');
    $this->assertEquals(
      'Part number 3 of type "type" is invalid.',
      $e->getMessage()
    );
  }

}