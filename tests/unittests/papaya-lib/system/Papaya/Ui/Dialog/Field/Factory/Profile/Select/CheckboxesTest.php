<?php
require_once(substr(__FILE__, 0, -83).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileSelectCheckboxesTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectCheckboxes::createField
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
    $profile = new PapayaUiDialogFieldFactoryProfileSelectCheckboxes();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelectCheckboxes', $field = $profile->getField());
  }
}