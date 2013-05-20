<?php
require_once(substr(__FILE__, 0, -68).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Control/Command/Dialog/Callbacks.php');

class PapayaUiControlCommandDialogCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandDialogCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiControlCommandDialogCallbacks();
    $this->assertNull($callbacks->onCreateDialog->defaultReturn);
    $this->assertNull($callbacks->onExecuteFailed->defaultReturn);
    $this->assertNull($callbacks->onExecuteSuccessful->defaultReturn);
  }
}