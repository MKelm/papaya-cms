<?php
require_once(substr(__FILE__, 0, -64).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaTemplateSimpleAstNodeValueTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleAstNodeValue::__construct
   */
  public function testConstructorAndPropertyAccess() {
    $node = new PapayaTemplateSimpleAstNodeValue('foo', 'bar');
    $this->assertEquals('foo', $node->name);
    $this->assertEquals('bar', $node->default);
  }

}