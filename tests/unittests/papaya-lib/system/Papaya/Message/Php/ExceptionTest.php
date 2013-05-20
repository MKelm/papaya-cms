<?php
require_once(substr(__FILE__, 0, -55).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Php/Exception.php');


class PapayaMessagePhpExceptionTest extends PapayaTestCase {

  /**
  * @covers PapayaMessagePhpException::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessagePhpException(
      new ErrorException('Sample Error', 0, E_USER_ERROR, 'sample.php', 42)
    );
    $this->assertAttributeEquals(
      PapayaMessage::TYPE_ERROR,
      '_type',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Error',
      '_message',
      $message
    );
    $this->assertEquals(
      1,
      count($message->context())
    );
  }
}