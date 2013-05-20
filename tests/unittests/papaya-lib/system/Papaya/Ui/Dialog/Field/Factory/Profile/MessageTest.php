<?php
require_once(substr(__FILE__, 0, -72).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileMessageTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileMessage::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'default' => 'some value',
        'parameters' => PapayaMessage::TYPE_INFO
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileMessage();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldMessage', $field = $profile->getField());
  }
}