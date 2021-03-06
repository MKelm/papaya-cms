<?php
require_once(substr(__FILE__, 0, -47).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Debug.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Logable.php');

class PapayaMessageDebugTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDebug::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessageDebug(PapayaMessageLogable::GROUP_SYSTEM, 'Sample Message');
    $this->assertAttributeEquals(
      PapayaMessageLogable::GROUP_SYSTEM,
      '_group',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Message',
      '_message',
      $message
    );
    $this->assertAttributeInstanceOf(
      'PapayaMessageContextGroup',
      '_context',
      $message
    );
  }

  /**
  * @covers PapayaMessageDebug::getGroup
  */
  public function testGetGroup() {
    $message = new PapayaMessageDebug();
    $this->assertEquals(
      PapayaMessageLogable::GROUP_DEBUG,
      $message->getGroup()
    );
  }


  /**
  * @covers PapayaMessageDebug::getType
  */
  public function testGetType() {
    $message = new PapayaMessageDebug();
    $this->assertEquals(
      PapayaMessage::TYPE_DEBUG,
      $message->getType()
    );
  }

  /**
  * @covers PapayaMessageDebug::context
  */
  public function testContext() {
    $message = new PapayaMessageDebug();
    $found = array();
    foreach ($message->context() as $subContext) {
      $found[] = get_class($subContext);
    }
    $this->assertEquals(
      array(
        'PapayaMessageContextMemory',
        'PapayaMessageContextRuntime',
        'PapayaMessageContextBacktrace'
      ),
      $found
    );
  }

  /**
  * @covers PapayaMessageDebug::getMessage
  */
  public function testGetMessage() {
    $message = new PapayaMessageDebug(
      PapayaMessageLogable::GROUP_DEBUG,
      'Sample Message'
    );
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }
}