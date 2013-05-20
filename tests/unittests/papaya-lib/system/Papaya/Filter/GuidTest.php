<?php
require_once(substr(__FILE__, 0, -45).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Pcre.php');

class PapayaFilterGuidTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterGuid
  */
  public function testValidateExpectingTrue() {
    $filter = new PapayaFilterGuid();
    $this->assertTrue(
      $filter->validate('123456789012345678901234567890ab')
    );
  }

  /**
  * @covers PapayaFilterGuid
  */
  public function testValidateExpectingException() {
    $filter = new PapayaFilterGuid();
    $this->setExpectedException('PapayaFilterException');
    $filter->validate('foo');
  }
}
