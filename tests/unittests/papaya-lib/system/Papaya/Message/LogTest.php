<?php
require_once(substr(__FILE__, 0, -45).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Log.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Logable.php');

class PapayaMessageLogTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageLog::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::TYPE_WARNING,
      'Sample Message'
    );
    $this->assertAttributeEquals(
      PapayaMessageLogable::GROUP_SYSTEM,
      '_group',
      $message
    );
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
  * @covers PapayaMessageLog::getGroup
  */
  public function testGetGroup() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::TYPE_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      PapayaMessageLogable::GROUP_SYSTEM,
      $message->getGroup()
    );
  }


  /**
  * @covers PapayaMessageLog::getType
  */
  public function testGetType() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::TYPE_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      PapayaMessage::TYPE_WARNING,
      $message->getType()
    );
  }

  /**
  * @covers PapayaMessageLog::SetContext
  */
  public function testSetContext() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::TYPE_WARNING,
      'Sample Message'
    );
    $context = $this->getMock('PapayaMessageContextGroup');
    $message->setContext($context);
    $this->assertAttributeSame(
      $context,
      '_context',
      $message
    );
  }

  /**
  * @covers PapayaMessageLog::context
  */
  public function testContext() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::TYPE_WARNING,
      'Sample Message'
    );
    $context = $this->getMock('PapayaMessageContextGroup');
    $message->setContext($context);
    $this->assertSame(
      $context,
      $message->context()
    );
  }

  /**
  * @covers PapayaMessageLog::getMessage
  */
  public function testGetMessage() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::TYPE_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }

  /**
  * @covers PapayaMessageLog::Context
  */
  public function testContextImplizitCreate() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::TYPE_WARNING,
      'Sample Message'
    );
    $this->assertInstanceOf(
      'PapayaMessageContextGroup',
      $message->context()
    );
  }
}