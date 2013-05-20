<?php
require_once(substr(__FILE__, 0, -90).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileInputMediaImageResizedTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputMediaImageResized::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputMediaImageResized();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputMediaImageResized', $field = $profile->getField());
  }
}