<?php
require_once(substr(__FILE__, 0, -66).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldInputMediaImageTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldInputMediaImage
   */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldInputMediaImage('caption', 'name', TRUE);
    $this->assertEquals(new PapayaFilterGuid(), $field->getFilter());
  }
}
