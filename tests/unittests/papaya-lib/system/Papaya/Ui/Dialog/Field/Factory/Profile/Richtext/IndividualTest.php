<?php
require_once(substr(__FILE__, 0, -85).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileRichtextIndividualTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileRichtextIndividual::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'rtefield',
        'caption' => 'Richtext',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileRichtextIndividual();
    $profile->options($options);
    $this->assertInstanceOf(
      'PapayaUiDialogFieldTextareaRichtext', $field = $profile->getField()
    );
    $this->assertEquals(
      PapayaUiDialogFieldTextareaRichtext::RTE_INDIVIDUAL,
      $field->getRteMode()
    );
  }
}