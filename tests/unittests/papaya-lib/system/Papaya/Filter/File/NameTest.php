<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFileNameTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFileName
   */
  public function testFilter() {
    $filter = new PapayaFilterFileName();
    $this->assertTrue($filter->validate('/foo/bar'));
  }
}