<?php
require_once(substr(__FILE__, 0, -82).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileInputMediaImageTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputMediaImage::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputMediaImage();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputMediaImage', $field = $profile->getField());
  }
}