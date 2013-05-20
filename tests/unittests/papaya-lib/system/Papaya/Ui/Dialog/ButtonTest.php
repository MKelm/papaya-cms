<?php
require_once(substr(__FILE__, 0, -49).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Button.php');

class PapayaUiDialogButtonTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogButton::__construct
  */
  public function testConstructor() {
    $button = new PapayaUiDialogButton_TestProxy();
    $this->assertAttributeEquals(
      PapayaUiDialogButton::ALIGN_RIGHT,
      '_align',
      $button
    );
  }

  /**
  * @covers PapayaUiDialogButton::__construct
  */
  public function testConstructorWithAlign() {
    $button = new PapayaUiDialogButton_TestProxy(PapayaUiDialogButton::ALIGN_LEFT);
    $this->assertAttributeEquals(
      PapayaUiDialogButton::ALIGN_LEFT,
      '_align',
      $button
    );
  }

  /**
  * @covers PapayaUiDialogButton::setAlign
  */
  public function testSetAlign() {
    $button = new PapayaUiDialogButton_TestProxy();
    $button->setAlign(PapayaUiDialogButton::ALIGN_LEFT);
    $this->assertAttributeEquals(
      PapayaUiDialogButton::ALIGN_LEFT,
      '_align',
      $button
    );
  }
}

class PapayaUiDialogButton_TestProxy extends PapayaUiDialogButton {

  public function appendTo(PapayaXmlElement $parent) {
  }
}