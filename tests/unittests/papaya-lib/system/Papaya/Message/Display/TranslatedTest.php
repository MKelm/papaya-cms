<?php
require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Display/Translated.php');

class PapayaMessageDisplayTranslatedTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDisplayTranslated::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessageDisplayTranslated(PapayaMessage::TYPE_INFO, 'Test');
    $string = $this->readAttribute($message, '_message');
    $this->assertInstanceOf(
      'PapayaUiStringTranslated', $string
    );
    $this->assertAttributeEquals(
      'Test', '_pattern', $string
    );
  }

  /**
  * @covers PapayaMessageDisplayTranslated::__construct
  */
  public function testConstructorWithArguments() {
    $message = new PapayaMessageDisplayTranslated(PapayaMessage::TYPE_INFO, 'Test', array(1, 2, 3));
    $string = $this->readAttribute($message, '_message');
    $this->assertAttributeEquals(
      array(1, 2, 3), '_values', $string
    );
  }
}