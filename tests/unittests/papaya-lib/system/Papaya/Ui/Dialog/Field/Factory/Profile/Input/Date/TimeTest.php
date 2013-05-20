<?php
require_once(substr(__FILE__, 0, -81).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileInputDateTimeTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputDateTime::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputDateTime();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputDate', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputDateTime::getField
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
    $profile = new PapayaUiDialogFieldFactoryProfileInputDateTime();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}