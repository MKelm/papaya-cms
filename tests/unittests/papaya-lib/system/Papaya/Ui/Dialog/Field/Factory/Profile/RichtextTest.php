<?php
require_once(substr(__FILE__, 0, -74).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileRichtextTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileRichtext::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'rtefield',
        'caption' => 'Richtext',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileRichtext();
    $profile->options($options);
    $this->assertInstanceOf(
      'PapayaUiDialogFieldTextareaRichtext', $field = $profile->getField()
    );
    $this->assertEquals(
      PapayaUiDialogFieldTextareaRichtext::RTE_DEFAULT,
      $field->getRteMode()
    );
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileRichtext::getField
   */
  public function testGetFieldWihtHint() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'rtefield',
        'caption' => 'Richtext',
        'hint' => 'Richtext Hint'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileRichtext();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertEquals(
      'Richtext Hint',
      $field->getHint()
    );
  }

}