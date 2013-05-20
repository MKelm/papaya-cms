<?php

require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application/Profile/Messages.php');

class PapayaApplicationProfileMessagesTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileMessages::getIdentifier
  */
  public function testGetIdentifier() {
    $profile = new PapayaApplicationProfileMessages();
    $this->assertEquals(
      'Messages',
      $profile->getIdentifier()
    );
  }

  /**
  * @covers PapayaApplicationProfileMessages::createObject
  */
  public function testCreateObject() {
    $application = $this->getMock('PapayaApplication');
    $profile = new PapayaApplicationProfileMessages();
    $messages = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaMessageManager', $messages
    );
    $dispatchers = $this->readAttribute($messages, '_dispatchers');
    $this->assertInstanceOf(
      'PapayaMessageDispatcherTemplate', $dispatchers[0]
    );
    $this->assertInstanceOf(
      'PapayaMessageDispatcherDatabase', $dispatchers[1]
    );
    $this->assertInstanceOf(
      'PapayaMessageDispatcherWildfire', $dispatchers[2]
    );
    $this->assertInstanceOf(
      'PapayaMessageDispatcherXhtml', $dispatchers[3]
    );
  }
}