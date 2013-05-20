<?php
require_once(substr(__FILE__, 0, -85).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileSelectMediaFolderTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileSelectMediaFolder::createField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'mediafolder',
        'caption' => 'Folder'
      )
    );

    $profile = new PapayaUiDialogFieldFactoryProfileSelectMediaFolder();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldSelectMediaFolder', $profile->getField());
  }
}