<?php
require_once(substr(__FILE__, 0, -49).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Manager.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Hook.php');


class PapayaMessageManagerTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageManager::addDispatcher
  */
  public function testAddDispatcher() {
    $dispatcher = $this->getMock('PapayaMessageDispatcher');
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $this->assertAttributeEquals(
      array($dispatcher),
      '_dispatchers',
      $manager
    );
  }

  /**
  * covers PapayaMessageManager::dispatch
  */
  public function testDispatch() {
    $message = $this->getMock('PapayaMessage');
    $dispatcher = $this->getMock('PapayaMessageDispatcher', array('dispatch'));
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->equalTo($message));
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->dispatch($message);
  }

  /**
  * covers PapayaMessageManager::encapsulate
  */
  public function testEncapsulate() {
    $manager = new PapayaMessageManager();
    $manager->papaya($papaya = $this->getMockApplicationObject());
    $sandbox = $manager->encapsulate('substr');
    $this->assertTrue(is_callable($sandbox));
    $this->assertSame($papaya, $sandbox[0]->papaya());
  }

  /**
  * covers PapayaMessageManager::hooks
  */
  public function testHooksSettingHooks() {
    $hookOne = $this->getMock('PapayaMessageHook');
    $hookTwo = $this->getMock('PapayaMessageHook');
    $manager = new PapayaMessageManager();
    $manager->hooks(
      array($hookOne, $hookTwo)
    );
    $this->assertAttributeSame(
      array($hookOne, $hookTwo),
      '_hooks',
      $manager
    );
  }

  /**
  * covers PapayaMessageManager::hooks
  */
  public function testHooksReadHooks() {
    $hookOne = $this->getMock('PapayaMessageHook');
    $manager = new PapayaMessageManager();
    $manager->hooks(array($hookOne));
    $this->assertSame(
      array($hookOne),
      $manager->hooks()
    );
  }

  /**
  * covers PapayaMessageManager::hooks
  */
  public function testHooksReadHooksImplizitCreate() {
    $manager = new PapayaMessageManager();
    $this->assertEquals(
      2,
      count($manager->hooks())
    );
  }

  /**
  * covers PapayaMessageManager::setUp
  */
  public function testSetUp() {
    $errorReporting = error_reporting();
    $options = $this->getMockConfigurationObject();
    $hookOne = $this->getMock('PapayaMessageHook');
    $hookOne
      ->expects($this->once())
      ->method('activate');
    $hookTwo = $this->getMock('PapayaMessageHook');
    $hookTwo
      ->expects($this->once())
      ->method('activate');

    $manager = new PapayaMessageManager();
    $manager->hooks(array($hookOne, $hookTwo));
    $manager->setUp($options);

    $this->assertAttributeGreaterThan(
      0, '_startTime', 'PapayaMessageContextRuntime'
    );
    $this->assertEquals(E_ALL & ~E_STRICT, error_reporting());

    error_reporting($errorReporting);
  }


  /**
  * covers PapayaMessageManager::debug
  */
  public function testDebug() {
    $dispatcher = $this->getMock('PapayaMessageDispatcher', array('dispatch'));
    $dispatcher
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageLog'));
    $manager = new PapayaMessageManager();
    $manager->addDispatcher($dispatcher);
    $manager->debug('test');
  }
}