<?php
require_once(substr(__FILE__, 0, -58).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Listview/Subitem/Text.php');

class PapayaUiListviewSubitemTextTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitemText::__construct
  */
  public function testConstructor() {
    $subitem = new PapayaUiListviewSubitemText('Sample text');
    $this->assertEquals(
      'Sample text', $subitem->text
    );
  }

  /**
  * @covers PapayaUiListviewSubitemText::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $subitem = new PapayaUiListviewSubitemText('Sample text');
    $subitem->align = PapayaUiOptionAlign::RIGHT;
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="right">Sample text</subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

}