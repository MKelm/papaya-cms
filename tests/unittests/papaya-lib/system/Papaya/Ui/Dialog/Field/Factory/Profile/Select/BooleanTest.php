<?php
require_once(substr(__FILE__, 0, -80).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileSelectBooleanTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectBoolean::createField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 0
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectBoolean();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelectRadio', $field = $profile->getField());
    $this->assertAttributeInstanceOf('PapayaUiStringTranslatedList', '_values', $field);
  }
}