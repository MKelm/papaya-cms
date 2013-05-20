<?php
require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Database/Exception/Connect.php');

class PapayaDatabaseExceptionConnectTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseExceptionConnect::__construct
  */
  public function testConstructorWithMessage() {
    $exception = new PapayaDatabaseExceptionConnect('Sample');
    $this->assertEquals(
      'Sample', $exception->getMessage()
    );
    $this->assertEquals(
      PapayaDatabaseException::SEVERITY_ERROR, $exception->getSeverity()
    );
  }

  /**
  * @covers PapayaDatabaseExceptionConnect::__construct
  */
  public function testConstructorWithCode() {
    $exception = new PapayaDatabaseExceptionConnect('Sample', 42);
    $this->assertEquals(
      42, $exception->getCode()
    );
  }
}
