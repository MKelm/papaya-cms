<?php
require_once(substr(__FILE__, 0, -57).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Navigation/Item/Text.php');

class PapayaUiNavigationItemTextTest extends PapayaTestCase {

  /**
  * @covers PapayaUiNavigationItemText::appendTo
  */
  public function testAppendTo() {
    $item = new PapayaUiNavigationItemText('sample');
    $item->papaya(
      $this->getMockApplicationObject()
    );
    $this->assertEquals(
      '<link href="http://www.test.tld/index.html">sample</link>',
      $item->getXml()
    );
  }
}