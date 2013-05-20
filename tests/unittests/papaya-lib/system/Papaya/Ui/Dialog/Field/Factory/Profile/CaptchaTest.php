<?php
require_once(substr(__FILE__, 0, -73).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldFactoryProfileCaptchaTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileCaptcha
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'captcha',
        'caption' => 'Captcha'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileCaptcha();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputCaptcha', $field = $profile->getField());
  }
}