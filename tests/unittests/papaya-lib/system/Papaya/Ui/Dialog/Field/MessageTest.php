<?php
require_once(substr(__FILE__, 0, -56).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Message.php');

class PapayaUiDialogFieldMessageTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldMessage::__construct
  */
  public function testConstructor() {
    $message = new PapayaUiDialogFieldMessage(PapayaMessage::TYPE_WARNING, 'Message');
    $this->assertAttributeEquals(
      'Message', '_text', $message
    );
    $this->assertAttributeEquals(
      'status-dialog-warning', '_image', $message
    );
  }

  /**
  * @covers PapayaUiDialogFieldMessage::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $message = new PapayaUiDialogFieldMessage(PapayaMessage::TYPE_INFO, 'Message');
    $message->papaya(
      $this->getMockApplicationObject(
        array(
          'images' => array('status-dialog-information' => 'image.png')
        )
      )
    );
    $message->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<field class="DialogFieldMessage" error="no">'.
          '<message image="image.png">Message</message>'.
        '</field>'.
      '</sample>',
      $dom->documentElement->saveXml()
    );
  }
}
