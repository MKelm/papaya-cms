<?php
require_once(substr(__FILE__, 0, -48).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Panel/Frame.php');

class PapayaUiPanelFrameTest extends PapayaTestCase {

  /**
  * @covers PapayaUiPanelFrame::__construct
  */
  public function testConstructor() {
    $frame = new PapayaUiPanelFrame('Sample Caption', 'sampleframe');
    $this->assertEquals(
      'Sample Caption', $frame->caption
    );
    $this->assertEquals(
      'sampleframe', $frame->name
    );
  }

  /**
  * @covers PapayaUiPanelFrame::__construct
  */
  public function testConstructorWihtAllParameters() {
    $frame = new PapayaUiPanelFrame('Sample Caption', 'sampleframe', '100%');
    $this->assertEquals(
      '100%', $frame->height
    );
  }

  /**
  * @covers PapayaUiPanelFrame::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $frame = new PapayaUiPanelFrame('Sample Caption', 'sampleframe');
    $frame->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      '<panel title="Sample Caption">'.
        '<iframe id="sampleframe" src="http://www.test.tld/test.html" height="400"/>'.
      '</panel>',
      $frame->getXml()
    );
  }

  /**
  * @covers PapayaUiPanelFrame::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->getMock('PapayaUiReference');
    $frame = new PapayaUiPanelFrame('Sample Caption', 'sampleframe');
    $this->assertSame(
      $reference, $frame->reference($reference)
    );
  }

  /**
  * @covers PapayaUiPanelFrame::reference
  */
  public function testReferenceGetImplicitCreate() {
    $frame = new PapayaUiPanelFrame('Sample Caption', 'sampleframe');
    $this->assertInstanceOf(
      'PapayaUiReference', $frame->reference
    );
  }
}