<?php
require_once(substr(__FILE__, 0, -54).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Csv/Writer/Callbacks.php');

class PapayaCsvWriterCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaCsvWriterCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaCsvWriterCallbacks();
    $this->assertInternalType('array', $callbacks->onMapRow->defaultReturn);
    $this->assertInternalType('array', $callbacks->onMapHeader->defaultReturn);
  }

}