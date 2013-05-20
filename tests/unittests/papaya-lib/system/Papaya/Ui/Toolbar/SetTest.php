<?php
require_once(substr(__FILE__, 0, -48).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Toolbar/Set.php');

class PapayaUiToolbarSetTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarSet::elements
  */
  public function testElementsGetAfterSet() {
    $group = new PapayaUiToolbarSet();
    $elements = $this->getMock('PapayaUiToolbarElements', array(), array($group));
    $elements
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf('PapayaUiToolbarSet'));
    $this->assertSame(
      $elements, $group->elements($elements)
    );
  }

  /**
  * @covers PapayaUiToolbarSet::elements
  */
  public function testElementsImplicitCreate() {
    $group = new PapayaUiToolbarSet();
    $this->assertInstanceOf(
      'PapayaUiToolbarElements', $group->elements()
    );
    $this->assertSame(
      $group, $group->elements()->owner()
    );
  }

  /**
  * @covers PapayaUiToolbarSet::appendTo
  */
  public function testAppendTo() {
    $group = new PapayaUiToolbarSet();
    $elements = $this->getMock('PapayaUiToolbarElements', array(), array($group));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXMlElement'));
    $group->elements($elements);
    $this->assertEquals(
      '',
      $group->getXml()
    );
  }
}
