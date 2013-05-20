<?php
require_once(substr(__FILE__, 0, -64).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Navigation/Builder/Callbacks.php');

class PapayaUiNavigationBuilderCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiNavigationBuilderCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiNavigationBuilderCallbacks();
    $this->assertNull($callbacks->onBeforeAppend->defaultReturn);
    $this->assertNull($callbacks->onAfterAppend->defaultReturn);
    $this->assertNull($callbacks->onCreateItem->defaultReturn);
    $this->assertNull($callbacks->onAfterAppendItem->defaultReturn);
  }
}