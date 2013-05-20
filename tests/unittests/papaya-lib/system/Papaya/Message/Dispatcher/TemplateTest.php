<?php
require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Dispatcher/Template.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Displayable.php');

class PapayaMessageDispatcherTemplateTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDispatcherTemplate::dispatch
  * @backupGlobals enabled
  */
  public function testDispatch() {
    $message = $this->getMock('PapayaMessageDisplayable');
    $message
      ->expects($this->once())
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::TYPE_WARNING));
    $message
      ->expects($this->once())
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $GLOBALS['PAPAYA_MSG'] = $this->getMock('base_errors', array('add'));
    $GLOBALS['PAPAYA_MSG']
      ->expects($this->once())
      ->method('add')
      ->with($this->equalTo(PapayaMessage::TYPE_WARNING), $this->equalTo('Sample message'))
      ->will($this->returnValue(TRUE));
    $dispatcher = new PapayaMessageDispatcherTemplate();
    $this->assertTrue($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherTemplate::dispatch
  */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    $message = $this->getMock('PapayaMessage');
    $dispatcher = new PapayaMessageDispatcherTemplate();
    $this->assertFalse($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherTemplate::dispatch
  */
  public function testDispatchWithoutGlobalObjectExpectingFalse() {
    $message = $this->getMock('PapayaMessageDisplayable');
    $dispatcher = new PapayaMessageDispatcherTemplate();
    $this->assertFalse($dispatcher->dispatch($message));
  }
}