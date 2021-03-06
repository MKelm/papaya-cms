<?php
require_once(substr(__FILE__, 0, -76).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaTemplateSimpleExceptionUnexpectedTokenTest extends PapayaTestCase {

  /**
  * @covers PapayaTemplateSimpleExceptionUnexpectedToken::__construct
  */
  public function testConstructor() {
    $expectedToken = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::TEXT, 42, 'sample'
    );
    $e = new PapayaTemplateSimpleExceptionUnexpectedToken(
      $expectedToken, array(PapayaTemplateSimpleScannerToken::VALUE_NAME)
    );
    $this->assertAttributeEquals(
      $expectedToken, 'encounteredToken', $e
    );
    $this->assertAttributeEquals(
      array(PapayaTemplateSimpleScannerToken::VALUE_NAME), 'expectedTokens', $e
    );
    $this->assertEquals(
      'Parse error: Found TEXT@42: "sample" while one of VALUE_NAME was expected.',
      $e->getMessage()
    );
  }
}