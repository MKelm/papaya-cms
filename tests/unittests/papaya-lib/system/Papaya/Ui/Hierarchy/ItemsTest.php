<?php
require_once(substr(__FILE__, 0, -52).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Hierarchy/Items.php');

class PapayaUiHierarchyItemsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiHierarchyItems::appendTo
  */
  public function testAppendToInheritance() {
    $items = new PapayaUiHierarchyItems();
    $this->assertSame('', $items->getXml());
  }

  /**
  * @covers PapayaUiHierarchyItems::appendTo
  */
  public function testAppendToWithLimit3() {
    $items = new PapayaUiHierarchyItems();
    $items->limit = 3;
    $items->spacer = $this->getItemFixture(TRUE);
    $items[] = $this->getItemFixture(TRUE);
    $items[] = $this->getItemFixture(FALSE);
    $items[] = $this->getItemFixture(FALSE);
    $items[] = $this->getItemFixture(TRUE);
    $items[] = $this->getItemFixture(TRUE);

    $this->assertSame('<items/>', $items->getXml());
  }

  /**
  * @covers PapayaUiHierarchyItems::spacer
  */
  public function testSpacerGetAfterSet() {
    $items = new PapayaUiHierarchyItems();
    $spacer = $this->getMock('PapayaUiHierarchyItem', array(), array('...'));
    $this->assertSame(
      $spacer, $items->spacer($spacer)
    );
  }

  /**
  * @covers PapayaUiHierarchyItems::spacer
  */
  public function testSpacerGetWithImpliciteCreate() {
    $items = new PapayaUiHierarchyItems();
    $items->papaya($papaya = $this->getMockApplicationObject());
    $this->assertInstanceOf(
      'PapayaUiHierarchyItem', $spacer = $items->spacer()
    );
    $this->assertSame(
      $papaya, $spacer->papaya()
    );
  }

  public function getItemFixture($expectAppend) {
    $item = $this->getMock('PapayaUiHierarchyItem', array(), array('item'));
    $item
      ->expects($expectAppend ? $this->once() : $this->never())
      ->method('appendTo');
    return $item;
  }
}