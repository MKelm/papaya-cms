<?php
require_once(substr(__FILE__, 0, -74).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaTemplateSimpleExceptionUnexpectedEofTest extends PapayaTestCase {

  /**
  * @covers PapayaTemplateSimpleExceptionUnexpectedEof::__construct
  */
  public function testConstructor() {
    $e = new PapayaTemplateSimpleExceptionUnexpectedEof(
      array(PapayaTemplateSimpleScannerToken::TEXT)
    );
    $this->assertAttributeEquals(
      array(PapayaTemplateSimpleScannerToken::TEXT), 'expectedTokens', $e
    );
    $this->assertEquals(
      'Parse error: Unexpected end of file was found while one of TEXT was expected.',
      $e->getMessage()
    );
  }
}