<?php
require_once(substr(__FILE__, 0, -58).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Navigation/Item/Named.php');

class PapayaUiNavigationItemNamedTest extends PapayaTestCase {

  /**
  * @covers PapayaUiNavigationItemNamed::appendTo
  */
  public function testAppendTo() {
    $item = new PapayaUiNavigationItemNamed('sample');
    $item->papaya(
      $this->getMockApplicationObject()
    );
    $this->assertEquals(
      '<link href="http://www.test.tld/index.html" name="sample"/>',
      $item->getXml()
    );
  }
}