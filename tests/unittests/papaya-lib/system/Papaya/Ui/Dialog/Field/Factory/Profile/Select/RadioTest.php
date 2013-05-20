<?php
require_once(substr(__FILE__, 0, -78).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileSelectRadioTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectRadio::createField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 0,
        'parameters' => array('foo', 'bar')
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileSelectRadio();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelectRadio', $field = $profile->getField());
  }
}