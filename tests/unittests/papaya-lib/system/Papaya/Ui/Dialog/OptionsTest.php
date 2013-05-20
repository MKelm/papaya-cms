<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Options.php');

class PapayaUiDialogOptionsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogOptions::appendTo
  * @covers PapayaUiDialogOptions::_valueToString
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendChild($dom->createElement('sample'));
    $options = new PapayaUiDialogOptions();
    $options->appendTo($dom->documentElement);
    $this->assertEquals(
      '<options>'.
        '<option name="USE_CONFIRMATION" value="yes"/>'.
        '<option name="USE_TOKEN" value="yes"/>'.
        '<option name="PROTECT_CHANGES" value="yes"/>'.
        '<option name="CAPTION_STYLE" value="1"/>'.
        '<option name="DIALOG_WIDTH" value="1"/>'.
        '<option name="TOP_BUTTONS" value="no"/>'.
        '<option name="BOTTOM_BUTTONS" value="yes"/>'.
        '</options>',
      $dom->saveXml($dom->documentElement->firstChild)
    );
  }
}
