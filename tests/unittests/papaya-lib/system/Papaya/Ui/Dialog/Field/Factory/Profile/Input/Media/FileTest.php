<?php
require_once(substr(__FILE__, 0, -82).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileInputMediaFileTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputMediaFile::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputMediaFile();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputMediaFile', $field = $profile->getField());
  }
}