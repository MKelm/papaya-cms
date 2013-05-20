<?php

require_once(substr(__FILE__, 0, -48).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parser.php');

class PapayaRequestParserTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParser::isLast
  */
  public function testIsLast() {
    $parser = new PapayaRequestParser_TestProxy();
    $this->assertTrue($parser->isLast());
  }
}

class PapayaRequestParser_TestProxy extends PapayaRequestParser {
  public function parse($url) {

  }
}