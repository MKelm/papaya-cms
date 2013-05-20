<?php
require_once(substr(__FILE__, 0, -51).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Hierarchy/Menu.php');

class PapayaUiHierarchyMenuTest extends PapayaTestCase {

  /**
  * @covers PapayaUiHierarchyMenu::appendTo
  */
  public function testAppendTo() {
    $items = $this->getMock('PapayaUiHierarchyItems');
    $items
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));

    $menu = new PapayaUiHierarchyMenu();
    $menu->items($items);

    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $this->assertInstanceOf('PapayaXmlElement', $menu->appendTo($dom->documentElement));
    $this->assertEquals(
      '<sample><hierarchy-menu/></sample>', $dom->documentElement->saveXml()
    );
  }

  /**
  * @covers PapayaUiHierarchyMenu::appendTo
  */
  public function testAppendToWithoutItemsExpectingNull() {
    $items = $this->getMock('PapayaUiHierarchyItems');
    $items
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(0));
    $menu = new PapayaUiHierarchyMenu();
    $menu->items($items);

    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $this->assertNull($menu->appendTo($dom->documentElement));
    $this->assertEquals(
      '<sample/>', $dom->documentElement->saveXml()
    );
  }

  /**
  * @covers PapayaUiHierarchyMenu::items
  */
  public function testItemsGetAfterSet() {
    $menu = new PapayaUiHierarchyMenu();
    $items = $this->getMock('PapayaUiHierarchyItems');
    $this->assertSame(
      $items, $menu->items($items)
    );
  }

  /**
  * @covers PapayaUiHierarchyMenu::items
  */
  public function testItemsGetWithImpliciteCreate() {
    $menu = new PapayaUiHierarchyMenu();
    $menu->papaya($papaya = $this->getMockApplicationObject());
    $this->assertInstanceOf(
      'PapayaUiHierarchyItems', $menu->items()
    );
    $this->assertSame(
      $papaya, $menu->papaya()
    );
  }
}