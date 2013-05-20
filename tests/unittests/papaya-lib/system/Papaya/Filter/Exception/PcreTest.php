<?php
require_once(substr(__FILE__, 0, -55).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Pcre.php');

class PapayaFilterExceptionPcreTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionPcre::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionPcre('(foo)');
    $this->assertEquals(
      'Value does not match pattern "(foo)"',
      $e->getMessage()
    );
  }

  /**
  * @covers PapayaFilterExceptionPcre::getPattern
  */
  public function testGetPattern() {
    $e = new PapayaFilterExceptionPcre('(foo)');
    $this->assertEquals(
      '(foo)',
      $e->getPattern()
    );
  }
}
