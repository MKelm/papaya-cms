<?php
require_once(substr(__FILE__, 0, -65).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfile::options
   */
  public function testOptionsGetAfterSet() {
    $profile = new PapayaUiDialogFieldFactoryProfile_TestProxy();
    $profile->options($options = $this->getMock('PapayaUiDialogFieldFactoryOptions'));
    $this->assertSame(
      $options,
      $profile->options()
    );
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfile::options
   */
  public function testOptionsGetImplicitCreate() {
    $profile = new PapayaUiDialogFieldFactoryProfile_TestProxy();
    $this->assertInstanceOf(
      'PapayaUiDialogFieldFactoryOptions',
      $profile->options()
    );
  }

}

class PapayaUiDialogFieldFactoryProfile_TestProxy extends PapayaUiDialogFieldFactoryProfile {

  function getField() {
  }
}