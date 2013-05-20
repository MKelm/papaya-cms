<?php
require_once(substr(__FILE__, 0, -74).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaTemplateSimpleVisitorOutputCallbacksTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleVisitorOutputCallbacks::__construct
   */
  public function testConstructor() {
    $callbacks = new PapayaTemplateSimpleVisitorOutputCallbacks();
    $this->assertNull($callbacks->onGetValue->defaultReturn);
  }
}