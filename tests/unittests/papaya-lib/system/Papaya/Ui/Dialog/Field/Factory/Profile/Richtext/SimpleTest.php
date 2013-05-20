<?php
require_once(substr(__FILE__, 0, -81).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileRichtextSimpleTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileRichtextSimple::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'rtefield',
        'caption' => 'Richtext',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileRichtextSimple();
    $profile->options($options);
    $this->assertInstanceOf(
      'PapayaUiDialogFieldTextareaRichtext', $field = $profile->getField()
    );
    $this->assertEquals(
      PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE,
      $field->getRteMode()
    );
  }
}