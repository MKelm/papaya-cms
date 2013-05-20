<?php
require_once(substr(__FILE__, 0, -54).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Context/Text.php');

class PapayaMessageContextTextTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageContextText::__construct
  */
  public function testConstructor() {
    $context = new PapayaMessageContextText('Hello World');
    $this->assertAttributeSame(
      'Hello World',
      '_text',
      $context
    );
  }

  /**
  * @covers PapayaMessageContextText::asString
  */
  public function testAsString() {
    $context = new PapayaMessageContextText('Hello World');
    $this->assertEquals(
      'Hello World',
      $context->asString()
    );
  }
}