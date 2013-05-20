<?php
require_once(substr(__FILE__, 0, -74).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileCheckboxTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileCheckbox
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'chebkoxfield',
        'caption' => 'Label',
        'default' => TRUE
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileCheckbox();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputCheckbox', $field = $profile->getField());
  }
}