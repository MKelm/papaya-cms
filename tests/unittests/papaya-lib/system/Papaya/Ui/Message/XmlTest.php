<?php
require_once(substr(__FILE__, 0, -48).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Message/Xml.php');

class PapayaUiMessageXmlTest extends PapayaTestCase {

  /**
  * @covers PapayaUiMessageText::appendTo
  */
  public function testAppendTo() {
    $message = new PapayaUiMessageXml(PapayaUiMessage::SEVERITY_ERROR, 'sample', 'content', TRUE);
    $this->assertEquals(
      '<error event="sample" occured="yes">content</error>', $message->getXml()
    );
  }

  /**
  * @covers PapayaUiMessageXml::appendTo
  */
  public function testAppendToWithXmlElements() {
    $message = new PapayaUiMessageXml(
      PapayaUiMessage::SEVERITY_ERROR, 'sample', '<b>foo</b>', TRUE
    );
    $this->assertEquals(
      '<error event="sample" occured="yes"><b>foo</b></error>', $message->getXml()
    );
  }
}