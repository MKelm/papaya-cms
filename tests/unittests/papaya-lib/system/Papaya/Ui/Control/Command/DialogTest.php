<?php
require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Control/Command/Dialog.php');

class PapayaUiControlCommandDialogTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandDialog::appendTo
  */
  public function testAppendTo() {
    $dialog = $this->getMock('PapayaUiDialog');
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(NULL));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $command = new PapayaUiControlCommandDialog();
    $command->dialog($dialog);
    $command->getXml();
  }

  /**
  * @covers PapayaUiControlCommandDialog::appendTo
  */
  public function testAppendToExecuteSuccessful() {
    $dialog = $this->getMock('PapayaUiDialog');
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $callbacks = $this
      ->getMockBuilder('PapayaUiControlCommandDialogCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(array('onExecuteSuccessful'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onExecuteSuccessful')
      ->with($dialog);
    $command = new PapayaUiControlCommandDialog();
    $command->dialog($dialog);
    $command->callbacks($callbacks);
    $command->getXml();
  }

  /**
  * @covers PapayaUiControlCommandDialog::appendTo
  */
  public function testAppendToExecuteSuccessfulWhileHideAfterSuccessIsTrue() {
    $dialog = $this->getMock('PapayaUiDialog');
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->never())
      ->method('appendTo');
    $callbacks = $this
      ->getMockBuilder('PapayaUiControlCommandDialogCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(array('onExecuteSuccessful'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onExecuteSuccessful')
      ->with($dialog);
    $command = new PapayaUiControlCommandDialog();
    $command->dialog($dialog);
    $command->hideAfterSuccess(TRUE);
    $command->callbacks($callbacks);
    $command->getXml();
  }


  /**
  * @covers PapayaUiControlCommandDialog::appendTo
  */
  public function testAppendToExecuteFailed() {
    $dialog = $this->getMock('PapayaUiDialog');
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $callbacks = $this
      ->getMockBuilder('PapayaUiControlCommandDialogCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(array('onExecuteFailed'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onExecuteFailed')
      ->with($dialog);
    $command = new PapayaUiControlCommandDialog();
    $command->dialog($dialog);
    $command->callbacks($callbacks);
    $command->getXml();
  }

  /**
  * @covers PapayaUiControlCommandDialog::dialog
  */
  public function testDialogGetAfterSet() {
    $dialog = $this->getMock('PapayaUiDialog');
    $command = new PapayaUiControlCommandDialog();
    $this->assertSame($dialog, $command->dialog($dialog));
  }

  /**
  * @covers PapayaUiControlCommandDialog::dialog
  * @covers PapayaUiControlCommandDialog::createDialog
  */
  public function testDialogGetImplicitCreate() {
    $command = new PapayaUiControlCommandDialog();
    $this->assertInstanceOf('PapayaUiDialog', $command->dialog());
  }

  /**
  * @covers PapayaUiControlCommandDialog::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->getMock('PapayaUiControlCommandDialogCallbacks');
    $command = new PapayaUiControlCommandDialog();
    $this->assertSame($callbacks, $command->callbacks($callbacks));
  }

  /**
  * @covers PapayaUiControlCommandDialog::callbacks
  */
  public function testCallbacksGetImplicitCreate() {
    $command = new PapayaUiControlCommandDialog();
    $this->assertInstanceOf('PapayaUiControlCommandDialogCallbacks', $command->callbacks());
  }

  /**
  * @covers PapayaUiControlCommandDialog::hideAfterSuccess
  */
  public function testHideAfterSelectSetToTrue() {
    $command = new PapayaUiControlCommandDialog();
    $command->hideAfterSuccess(TRUE);
    $this->assertTrue($command->hideAfterSuccess());
  }

  /**
  * @covers PapayaUiControlCommandDialog::hideAfterSuccess
  */
  public function testHideAfterSelectSetToFalse() {
    $command = new PapayaUiControlCommandDialog();
    $command->hideAfterSuccess(FALSE);
    $this->assertFalse($command->hideAfterSuccess());
  }

  /**
  * @covers PapayaUiControlCommandDialog::context
  */
  public function testContextGetAfterSet() {
    $command = new PapayaUiControlCommandDialog();
    $command->context($context = $this->getMock('PapayaRequestParameters'));
    $this->assertSame($context, $command->context());
  }

  /**
  * @covers PapayaUiControlCommandDialog::context
  */
  public function testContextGetWithoutSetExpectingNull() {
    $command = new PapayaUiControlCommandDialog();
    $this->assertNull($command->context());
  }

}