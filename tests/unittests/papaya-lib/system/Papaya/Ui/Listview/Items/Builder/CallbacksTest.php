<?php
require_once(substr(__FILE__, 0, -69).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Listview/Items/Builder/Callbacks.php');

class PapayaUiListviewItemsBuilderCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewItemsBuilderCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaUiListviewItemsBuilderCallbacks();
    $this->assertFalse($callbacks->onBeforeFill->defaultReturn);
    $this->assertNull($callbacks->onCreateItem->defaultReturn);
    $this->assertNull($callbacks->onAfterFill->defaultReturn);
  }
}