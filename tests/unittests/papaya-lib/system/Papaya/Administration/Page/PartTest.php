<?php
require_once(substr(__FILE__, 0, -58).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Administration/Page/Part.php');

class PapayaAdministrationPagePartTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPagePart::toolbar
   */
  public function testToolbarGetAfterSet() {
    $part = new PapayaAdministrationPagePart_TestProxy();
    $part->toolbar($toolbar = $this->getMock('PapayaUiToolbarSet'));
    $this->assertSame($toolbar, $part->toolbar());
  }

  /**
   * @covers PapayaAdministrationPagePart::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $part = new PapayaAdministrationPagePart_TestProxy();
    $this->assertInstanceOf('PapayaUiToolbarSet', $part->toolbar());
  }
}

class PapayaAdministrationPagePart_TestProxy extends PapayaAdministrationPagePart {

  public function appendTo(PapayaXmlElement $parent) {
  }
}