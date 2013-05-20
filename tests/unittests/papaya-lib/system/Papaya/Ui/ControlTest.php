<?php
require_once(substr(__FILE__, 0, -44).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Control.php');

class PapayaUiControlTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControl::getXml
  */
  public function testGetXml() {
    $control = new PapayaUiControl_TestProxy();
    $dom = new PapayaXmlDocument;
    $control->nodeStub = array(
      $dom->appendElement('sample')
    );
    $this->assertEquals(
      '<sample/>', $control->getXml()
    );
  }

  /**
  * @covers PapayaUiControl::getXml
  */
  public function testGetXmlWithTextNode() {
    $control = new PapayaUiControl_TestProxy();
    $dom = new PapayaXmlDocument;
    $control->nodeStub = array(
      $dom->createTextNode('sample')
    );
    $this->assertEquals(
      'sample', $control->getXml()
    );
  }

  /**
  * @covers PapayaUiControl::getXml
  */
  public function testGetXmlWithSeveralNodes() {
    $control = new PapayaUiControl_TestProxy();
    $dom = new PapayaXmlDocument;
    $control->nodeStub = array(
      $dom->createTextNode('sample'),
      $dom->createElement('sample'),
      $dom->createComment('comment')
    );
    $this->assertEquals(
      'sample<sample/><!--comment-->', $control->getXml()
    );
  }
}

class PapayaUiControl_TestProxy extends PapayaUiControl {

  public $nodeStub = array();

  public function appendTo(PapayaXmlElement $parent) {
    foreach ($this->nodeStub as $node) {
      $parent->appendChild(
        $parent->ownerDocument->importNode($node)
      );
    }
  }
}
