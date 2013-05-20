<?php
require_once(substr(__FILE__, 0, -49).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Display.php');

class PapayaMessageDisplayTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDisplay::__construct
  * @covers PapayaMessageDisplay::_isValidType
  */
  public function testConstructor() {
    $message = new PapayaMessageDisplay(PapayaMessage::TYPE_WARNING, 'Sample Message');
    $this->assertAttributeEquals(
      PapayaMessage::TYPE_WARNING,
      '_type',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Message',
      '_message',
      $message
    );
  }

  /**
  * @covers PapayaMessageDisplay::__construct
  * @covers PapayaMessageDisplay::_isValidType
  */
  public function testConstructorWithInvalidTypeExpectingException() {
    $this->setExpectedException('InvalidArgumentException');
    new PapayaMessageDisplay(PapayaMessage::TYPE_DEBUG, 'Sample Message');
  }

  /**
  * @covers PapayaMessageDisplay::getType
  */
  public function testGetType() {
    $message = new PapayaMessageDisplay(PapayaMessage::TYPE_WARNING, 'Sample Message');
    $this->assertEquals(
      PapayaMessage::TYPE_WARNING,
      $message->getType()
    );
  }

  /**
  * @covers PapayaMessageDisplay::getMessage
  */
  public function testGetMessage() {
    $message = new PapayaMessageDisplay(PapayaMessage::TYPE_WARNING, 'Sample Message');
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }

}