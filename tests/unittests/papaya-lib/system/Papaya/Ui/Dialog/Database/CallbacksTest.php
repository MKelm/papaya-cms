<?php
require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Database/Callbacks.php');

class PapayaUiDialogDatabaseCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogDatabaseCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiDialogDatabaseCallbacks();
    $this->assertTrue($callbacks->onBeforeDelete->defaultReturn);
    $this->assertTrue($callbacks->onBeforeSave->defaultReturn);
  }
}