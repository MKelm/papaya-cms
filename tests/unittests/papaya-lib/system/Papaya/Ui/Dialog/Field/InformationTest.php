<?php
require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Information.php');

class PapayaUiDialogFieldInformationTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldInformation::__construct
  */
  public function testConstructor() {
    $message = new PapayaUiDialogFieldInformation('Information', 'image');
    $this->assertAttributeEquals(
      'Information', '_text', $message
    );
    $this->assertAttributeEquals(
      'image', '_image', $message
    );
  }

  /**
  * @covers PapayaUiDialogFieldInformation::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $message = new PapayaUiDialogFieldInformation('Information', 'image');
    $message->papaya(
      $this->getMockApplicationObject(
        array(
          'images' => array('image' => 'image.png')
        )
      )
    );
    $message->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<field class="DialogFieldInformation" error="no">'.
          '<message image="image.png">Information</message>'.
        '</field>'.
      '</sample>',
      $dom->documentElement->saveXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInformation::appendTo
  */
  public function testAppendToWithoutImage() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $message = new PapayaUiDialogFieldInformation('Information');
    $message->papaya(
      $this->getMockApplicationObject(
        array(
          'images' => array('image' => 'image.png')
        )
      )
    );
    $message->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<field class="DialogFieldInformation" error="no">'.
          '<message>Information</message>'.
        '</field>'.
      '</sample>',
      $dom->documentElement->saveXml()
    );
  }

}
