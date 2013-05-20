<?php
require_once(substr(__FILE__, 0, -64).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldXhtmlCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldXhtmlCallback
  * @covers PapayaUiDialogFieldCallback::appendTo
  */
  public function testAppendTo() {
    $xhtml = new PapayaUiDialogFieldXhtmlCallback(
      'Caption', 'name', array($this, 'callbackGetFieldString')
    );
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldXhtmlCallback" error="no">'.
        '<xhtml><select/></xhtml>'.
      '</field>',
      $xhtml->getXml()
    );
  }

  public function callbackGetFieldString($name, $field, $data) {
    return '<select/>';
  }

}