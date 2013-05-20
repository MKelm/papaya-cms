<?php

require_once(substr(__FILE__, 0, -64).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Password/Weak.php');

class PapayaFilterExceptionPasswordWeakTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionPasswordWeak::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionPasswordWeak();
    $this->assertEquals(
      'Password is to weak.',
      $e->getMessage()
    );
  }
}