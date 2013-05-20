<?php
require_once(substr(__FILE__, 0, -71).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileColorTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileColor
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'colorfield',
        'caption' => 'Color',
        'default' => '#FFF'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileColor();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputColor', $profile->getField());
  }
}