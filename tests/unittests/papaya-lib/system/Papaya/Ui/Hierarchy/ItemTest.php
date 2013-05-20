<?php
require_once(substr(__FILE__, 0, -51).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Hierarchy/Item.php');

class PapayaUiHierarchyItemTest extends PapayaTestCase {

  /**
  * @covers PapayaUiHierarchyItem::__construct
  */
  public function testConstructor() {
    $item = new PapayaUiHierarchyItem('sample');
    $this->assertEquals('sample', $item->caption);
  }

  /**
  * @covers PapayaUiHierarchyItem::appendTo
  */
  public function testAppendTo() {
    $item = new PapayaUiHierarchyItem('sample');
    $item->image = 'items-page';
    $item->papaya(
      $this->getMockApplicationObject(
        array(
          'Images' => array(
            'items-page' => 'page.png'
          )
        )
      )
    );
    $this->assertEquals(
      '<item caption="sample" image="page.png" mode="both"/>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiHierarchyItem::appendTo
  */
  public function testAppendToWithReference() {
    $reference = $this->getMock('PapayaUiReference');
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('link.html'));

    $item = new PapayaUiHierarchyItem('sample');
    $item->image = 'items-page';
    $item->reference($reference);
    $item->papaya(
      $this->getMockApplicationObject(
        array(
          'Images' => array(
            'items-page' => 'page.png'
          )
        )
      )
    );
    $this->assertEquals(
      '<item caption="sample" image="page.png" mode="both" href="link.html"/>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiHierarchyItem::reference
  */
  public function testItemsGetAfterSet() {
    $item = new PapayaUiHierarchyItem('sample');
    $reference = $this->getMock('PapayaUiReference');
    $this->assertSame(
      $reference, $item->reference($reference)
    );
  }

  /**
  * @covers PapayaUiHierarchyItem::reference
  */
  public function testItemsGetWithImpliciteCreate() {
    $item = new PapayaUiHierarchyItem('sample');
    $item->papaya($papaya = $this->getMockApplicationObject());
    $this->assertInstanceOf(
      'PapayaUiReference', $item->reference()
    );
    $this->assertSame(
      $papaya, $item->papaya()
    );
  }

  /**
  * @covers PapayaUiHierarchyItem::setDisplayMode
  */
  public function testSetDisplayMode() {
    $item = new PapayaUiHierarchyItem('sample');
    $item->displayMode = PapayaUiHierarchyItem::DISPLAY_TEXT_ONLY;
    $this->assertEquals(
      PapayaUiHierarchyItem::DISPLAY_TEXT_ONLY, $item->displayMode
    );
  }

  /**
  * @covers PapayaUiHierarchyItem::setDisplayMode
  */
  public function testSetDisplayModeExpectingException() {
    $item = new PapayaUiHierarchyItem('sample');
    try {
      $item->displayMode = -99;
    } catch (OutOfBoundsException $e) {
      $this->assertEquals(
        'Invalid display mode for "PapayaUiHierarchyItem".',
        $e->getMessage()
      );
    }
  }
}