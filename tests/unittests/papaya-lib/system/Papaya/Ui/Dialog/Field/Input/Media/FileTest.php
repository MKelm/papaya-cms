<?php
require_once(substr(__FILE__, 0, -65).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldInputMediaFileTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldInputMediaFile::__construct
   */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldInputMediaFile('caption', 'name', TRUE);
    $this->assertEquals(new PapayaFilterGuid(), $field->getFilter());
  }
}
