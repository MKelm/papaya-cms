<?php
require_once(substr(__FILE__, 0, -53).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Navigation/Items.php');

class PapayaUiNavigationItemsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiNavigationItems::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->getMock('PapayaUiReference');
    $items = new PapayaUiNavigationItems();
    $this->assertSame(
      $reference, $items->reference($reference)
    );
  }

  /**
  * @covers PapayaUiNavigationItems::reference
  */
  public function testReferenceImpliciteCreate() {
    $items = new PapayaUiNavigationItems();
    $items->papaya($papaya = $this->getMockApplicationObject());
    $this->assertInstanceOf(
      'PapayaUiReference', $reference = $items->reference()
    );
    $this->assertSame(
      $papaya, $reference->papaya()
    );
  }

}