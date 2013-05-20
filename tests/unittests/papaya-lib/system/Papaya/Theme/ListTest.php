<?php
require_once(substr(__FILE__, 0, -44).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Theme/List.php');

class PapayaThemeListTest extends PapayaTestCase {

  /**
   * @covers PapayaThemeList::getIterator
   * @covers PapayaThemeList::callbackGetName
   */
  public function testGetIterator() {
    $handler = $this->getMock('PapayaThemeHandler');
    $handler
      ->expects($this->once())
      ->method('getLocalPath')
      ->will($this->returnValue(__DIR__.'/TestDataList/'));
    $list = new PapayaThemeList();
    $list->handler($handler);
    $this->assertEquals(
      array(
        'theme-sample'
      ),
      iterator_to_array($list)
    );
  }

  /**
   * @covers PapayaThemeList::getDefinition
   */
  public function testGetDefinition() {
    $handler = $this->getMock('PapayaThemeHandler');
    $handler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('theme-sample')
      ->will($this->returnValue(new PapayaContentStructure()));
    $list = new PapayaThemeList();
    $list->handler($handler);
    $this->assertInstanceOf(
      'PapayaContentStructure',
      $list->getDefinition('theme-sample')
    );
  }

  /**
   * @covers PapayaThemeList::handler
   */
  public function testHandlerGetAfterSet() {
    $list = new PapayaThemeList();
    $list->handler($handler =  $this->getMock('PapayaThemeHandler'));
    $this->assertSame($handler, $list->handler());
  }

  /**
   * @covers PapayaThemeList::handler
   */
  public function testHandlerGetImplicitCreate() {
    $list = new PapayaThemeList();
    $this->assertInstanceOf('PapayaThemeHandler', $list->handler());
  }
}

