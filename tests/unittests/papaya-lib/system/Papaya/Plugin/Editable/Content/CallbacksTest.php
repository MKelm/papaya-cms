<?php
require_once(substr(__FILE__, 0, -67).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaPluginEditableContentCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaPluginEditableContentCallbacks();
    $this->assertNull($callbacks->onCreateEditor->defaultReturn);
  }
}