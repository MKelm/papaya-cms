<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFilePathTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFilePath
   */
  public function testFilter() {
    $filter = new PapayaFilterFilePath();
    $this->assertTrue($filter->validate('/foo/bar/'));
  }

}