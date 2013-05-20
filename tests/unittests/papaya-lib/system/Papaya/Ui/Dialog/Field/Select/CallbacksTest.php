<?php
require_once(substr(__FILE__, 0, -65).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Select/Callbacks.php');

class PapayaUiDialogFieldSelectCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiDialogFieldSelectCallbacks();
    $this->assertNull($callbacks->getOptionCaption->defaultReturn);
  }
}