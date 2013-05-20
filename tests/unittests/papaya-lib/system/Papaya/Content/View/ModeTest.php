<?php
require_once(substr(__FILE__, 0, -51).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaContentViewModeTest extends PapayaTestCase {

  /**
   * @covers PapayaContentViewMode::_createKey
   */
  public function testKey() {
    $mode = new PapayaContentViewMode();
    $this->assertInstanceOf(
      'PapayaDatabaseRecordKeyFields', $mode->key()
    );
  }

}