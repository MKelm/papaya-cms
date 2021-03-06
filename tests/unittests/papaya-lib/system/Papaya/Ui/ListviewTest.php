<?php
require_once(substr(__FILE__, 0, -45).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Listview.php');

class PapayaUiListviewTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListview::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $listview = new PapayaUiListview();
    $items = $this->getMock('PapayaUiListviewItems', array(), array($listview));
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $listview->items($items);
    $columns = $this->getMock('PapayaUiListviewColumns', array(), array($listview));
    $columns
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $listview->columns($columns);
    $toolbars = $this->getMock('PapayaUiToolbars');
    $toolbars
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $listview->toolbars($toolbars);
    $listview->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample><listview/></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListview::appendTo
  */
  public function testAppendToWithCaption() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $listview = new PapayaUiListview();
    $listview->caption = 'test caption';
    $listview->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample><listview title="test caption"/></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListview::appendTo
  */
  public function testAppendToWithMode() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $listview = new PapayaUiListview();
    $listview->mode = PapayaUiListview::MODE_THUMBNAILS;
    $listview->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample><listview mode="thumbnails"/></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListview::items
  */
  public function testItemsGetAfterSet() {
    $listview = new PapayaUiListview();
    $items = $this->getMock('PapayaUiListviewItems', array(), array($listview));
    $this->assertSame($items, $listview->items($items));
  }

  /**
  * @covers PapayaUiListview::items
  * @covers PapayaUiListview::builder
  */
  public function testItemsGetAfterSettingBuilder() {
    $builder = $this
      ->getMockBuilder('PapayaUiListviewItemsBuilder')
      ->disableOriginalConstructor()
      ->getMock();
    $builder
      ->expects($this->once())
      ->method('fill')
      ->with($this->isInstanceOf('PapayaUiListviewItems'));
    $listview = new PapayaUiListview();
    $listview->builder($builder);
    $listview->items();
    $listview->items();
  }

  /**
  * @covers PapayaUiListview::items
  */
  public function testItemsGetImplicitCreate() {
    $listview = new PapayaUiListview();
    $items = $listview->items();
    $this->assertInstanceOf('PapayaUiListviewItems', $items);
    $this->assertSame($listview, $items->owner());
  }

  /**
  * @covers PapayaUiListview::columns
  */
  public function testColumnsGetAfterSet() {
    $listview = new PapayaUiListview();
    $columns = $this->getMock('PapayaUiListviewColumns', array(), array($listview));
    $this->assertSame($columns, $listview->columns($columns));
  }

  /**
  * @covers PapayaUiListview::columns
  */
  public function testColumnsGetImplicitCreate() {
    $listview = new PapayaUiListview();
    $columns = $listview->columns();
    $this->assertInstanceOf('PapayaUiListviewColumns', $columns);
    $this->assertSame($listview, $columns->owner());
  }

  /**
  * @covers PapayaUiListview::toolbars
  */
  public function testToolbarsGetAfterSet() {
    $listview = new PapayaUiListview();
    $toolbars = $this->getMock('PapayaUiToolbars');
    $this->assertSame($toolbars, $listview->toolbars($toolbars));
  }

  /**
  * @covers PapayaUiListview::toolbars
  */
  public function testToolbarsGetImplicitCreate() {
    $listview = new PapayaUiListview();
    $toolbars = $listview->toolbars();
    $this->assertInstanceOf('PapayaUiToolbars', $toolbars);
  }

  /**
  * @covers PapayaUiListview::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->getMock('PapayaUiReference');
    $listview = new PapayaUiListview();
    $this->assertSame(
      $reference, $listview->reference($reference)
    );
  }

  /**
  * @covers PapayaUiListview::reference
  */
  public function testReferenceGetImplicitCreate() {
    $listview = new PapayaUiListview();
    $this->assertInstanceOf(
      'PapayaUiReference', $listview->reference()
    );
  }

  /**
  * @covers PapayaUiListview::setMode
  */
  public function testGetModeAfterSet() {
    $listview = new PapayaUiListview();
    $listview->mode = PapayaUiListview::MODE_THUMBNAILS;
    $this->assertEquals(PapayaUiListview::MODE_THUMBNAILS, $listview->mode);
  }

  /**
  * @covers PapayaUiListview::setMode
  */
  public function testGetModeAfterSetInvalidMode() {
    $listview = new PapayaUiListview();
    $listview->mode = 'invalid mode string';
    $this->assertEquals(PapayaUiListview::MODE_DETAILS, $listview->mode);
  }
}