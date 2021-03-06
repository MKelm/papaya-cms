<?php
require_once(substr(__FILE__, 0, -71).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileInputTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInput::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInput();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInput', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInput::getField
   */
  public function testGetFieldDisabled() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'disabled' => TRUE
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInput();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertTrue($field->getDisabled());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInput::getField
   */
  public function testGetFieldWithHint() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'hint' => 'Some hint text'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInput();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}