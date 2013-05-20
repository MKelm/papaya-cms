<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Php/Error.php');


class PapayaMessagePhpErrorTest extends PapayaTestCase {

  /**
  * @covers PapayaMessagePhpError::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessagePhpError(E_USER_WARNING, 'Sample Warning', 'Sample Context');
    $this->assertAttributeEquals(
      PapayaMessage::TYPE_WARNING,
      '_type',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Warning',
      '_message',
      $message
    );
    $this->assertEquals(
      2,
      count($message->context())
    );
  }
}