<?php
require_once(substr(__FILE__, 0, -51).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();


class PapayaMessageExceptionTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageException::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessageException(
      new PapayaMessageException_Exception('Sample Error')
    );
    $this->assertAttributeEquals(
      PapayaMessage::TYPE_ERROR,
      '_type',
      $message
    );
    $this->assertStringStartsWith(
      "Uncaught exception 'PapayaMessageException_Exception' with message 'Sample Error' in '",
      $this->readAttribute($message, '_message')
    );
    $this->assertEquals(
      1,
      count($message->context())
    );
  }
}

class PapayaMessageException_Exception extends Exception {
}