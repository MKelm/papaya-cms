<?php
require_once(substr(__FILE__, 0, -58).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Listview.php');

class PapayaUiDialogFieldListviewTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldListview::__construct
  * @covers PapayaUiDialogFieldListview::listview
  */
  public function testConstructor() {
    $listview = $this->getMock('PapayaUiListview');
    $field = new PapayaUiDialogFieldListview($listview);
    $this->assertSame(
      $listview, $field->listview()
    );
  }

  /**
  * @covers PapayaUiDialogFieldListview::appendTo
  */
  public function testAppendTo() {
    $listview = $this->getMock('PapayaUiListview');
    $listview
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $field = new PapayaUiDialogFieldListview($listview);
    $this->assertEquals(
      '<field class="DialogFieldListview" error="no"/>',
      $field->getXml()
    );
  }
}