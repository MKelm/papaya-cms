<?php
require_once(substr(__FILE__, 0, -54).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaSvnClientExtensionTest extends PapayaTestCase {

  protected function setUp() {
    if (!extension_loaded('svn')) {
      $this->markTestSkipped(
        'The svn extension is not available.'
      );
    }
  }

  /**
  * @covers PapayaSvnClientExtension::ls
  */
  public function testLs() {
    $svn = new PapayaSvnClientExtension();
    // TODO possibly test by extracting a local svn repo in $this->setUp()
    $this->assertFalse(
      @$svn->ls('file:///not-existing-svn-repo/')
    );
  }

}