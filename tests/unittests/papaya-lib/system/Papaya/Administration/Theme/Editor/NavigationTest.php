<?php
require_once(substr(__FILE__, 0, -71).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Administration/Theme/Editor/Navigation.php');

class PapayaAdministrationThemeEditorNavigationTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::appendTo
   */
  public function testAppendTo() {
    $listview = $this->getMock('PapayaUiListview');
    $listview
      ->expects($this->once())
      ->method('appendTo');
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->papaya($this->getMockApplicationObject());
    $navigation->listview($listview);
    $navigation->getXml();
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::listview
   */
  public function testListviewGetAfterSet() {
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->listview($listview = $this->getMock('PapayaUiListview'));
    $this->assertSame(
      $listview, $navigation->listview()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::listview
   * @covers PapayaAdministrationThemeEditorNavigation::createThemeList
   */
  public function testListviewImplicitCreate() {
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->parameters(new PapayaRequestParameters());
    $this->assertInstanceOf(
      'PapayaUiListview', $navigation->listview()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::listview
   * @covers PapayaAdministrationThemeEditorNavigation::createThemeList
   */
  public function testListviewImplicitCreateWithSelectedTheme() {
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->parameters(new PapayaRequestParameters(array('theme' => 'default')));
    $this->assertInstanceOf(
      'PapayaUiListview', $navigation->listview()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::listview
   * @covers PapayaAdministrationThemeEditorNavigation::createThemeList
   */
  public function testListviewImplicitCreateWithSelectedSet() {
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->parameters(
      new PapayaRequestParameters(array('theme' => 'default', 'set_id' => 23))
    );
    $this->assertInstanceOf(
      'PapayaUiListview', $navigation->listview()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::callbackCreateItem
   */
  public function testCallbackCreateItemForInvalidElement() {
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertNull(
      $navigation->callbackCreateItem($this->getBuilderFixture(99), $items, 'sample', 0)
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::callbackCreateItem
   * @covers PapayaAdministrationThemeEditorNavigation::createThemeItem
   */
  public function testCallbackCreateItemForTheme() {
    $papaya = $this->getMockApplicationObject(
      array('images' => array('items-theme' => 'theme.png'))
    );
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiListviewItem'));
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem($this->getBuilderFixture(), $items, 'sample', 0);
    $this->assertEquals(
      '<listitem'.
        ' title="sample"'.
        ' image="theme.png"'.
        ' href="http://www.test.tld/test.html?cmd=theme_show&amp;theme=sample"/>',
      $item->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::callbackCreateItem
   * @covers PapayaAdministrationThemeEditorNavigation::createThemeItem
   */
  public function testCallbackCreateItemForSelectedTheme() {
    $papaya = $this->getMockApplicationObject(
      array(
        'request' => $this->getMockRequestObject(array('theme' => 'sample')),
        'images' => array('items-theme' => 'theme.png')
      )
    );
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiListviewItem'));
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem($this->getBuilderFixture(), $items, 'sample', 0);
    $this->assertEquals(
      '<listitem'.
        ' title="sample"'.
        ' image="theme.png"'.
        ' href="http://www.test.tld/test.html?cmd=theme_show&amp;theme=sample"'.
        ' selected="selected"/>',
      $item->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::callbackCreateItem
   * @covers PapayaAdministrationThemeEditorNavigation::createThemeItem
   */
  public function testCallbackCreateItemForSelectedThemeAndSelectedSet() {
    $papaya = $this->getMockApplicationObject(
      array(
        'request' => $this->getMockRequestObject(array('theme' => 'sample', 'set_id' => 23)),
        'images' => array('items-theme' => 'theme.png')
      )
    );
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiListviewItem'));
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem($this->getBuilderFixture(), $items, 'sample', 0);
    $this->assertEquals(
      '<listitem'.
        ' title="sample"'.
        ' image="theme.png"'.
        ' href="http://www.test.tld/test.html?cmd=theme_show&amp;theme=sample"/>',
      $item->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::callbackCreateItem
   * @covers PapayaAdministrationThemeEditorNavigation::createSetItem
   */
  public function testCallbackCreateItemForSet() {
    $papaya = $this->getMockApplicationObject(
      array(
        'images' => array('items-folder' => 'folder.png')
      )
    );
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiListviewItem'));
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem(
      $this->getBuilderFixture(1),
      $items,
      array('id' => 23, 'title' => 'sample title', 'theme' => 'sample'),
      0
    );
    $this->assertEquals(
      '<listitem'.
        ' title="sample title"'.
        ' image="folder.png"'.
        ' href="http://www.test.tld/test.html?cmd=set_edit&amp;theme=sample&amp;set_id=23"'.
        ' indent="1"/>',
      $item->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::callbackCreateItem
   * @covers PapayaAdministrationThemeEditorNavigation::createSetItem
   */
  public function testCallbackCreateItemForSelectedSet() {
    $papaya = $this->getMockApplicationObject(
      array(
        'request' => $this->getMockRequestObject(array('theme' => 'sample', 'set_id' => 23)),
        'images' => array('items-folder' => 'folder.png')
      )
    );
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiListviewItem'));
    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem(
      $this->getBuilderFixture(1),
      $items,
      array('id' => 23, 'title' => 'sample title', 'theme' => 'sample'),
      0
    );
    $this->assertEquals(
      '<listitem'.
        ' title="sample title"'.
        ' image="folder.png"'.
        ' href="http://www.test.tld/test.html?cmd=set_edit&amp;theme=sample&amp;set_id=23"'.
        ' indent="1"'.
        ' selected="selected"/>',
      $item->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::callbackCreateItem
   * @covers PapayaAdministrationThemeEditorNavigation::createPageItem
   */
  public function testCallbackPageItemForPage() {
    $papaya = $this->getMockApplicationObject(
      array(
        'request' => $this->getMockRequestObject(array('theme' => 'sample', 'set_id' => 23)),
        'images' => array('items-folder' => 'folder.png')
      )
    );
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiListviewItem'));

    $page = new PapayaContentStructurePage();
    $page->title = 'Page title';
    $page->name = 'SAMPLE_PAGE';

    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem(
      $this->getBuilderFixture(2),
      $items,
      $page,
      0
    );
    $this->assertEquals(
      '<listitem'.
        ' title="Page title"'.
        ' image="folder.png"'.
        ' href="http://www.test.tld/test.html?cmd=values_edit&amp;theme=sample&amp;set_id=23&amp;page_identifier=SAMPLE_PAGE"'.
        ' indent="2"/>',
      $item->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorNavigation::callbackCreateItem
   * @covers PapayaAdministrationThemeEditorNavigation::createPageItem
   */
  public function testCallbackPageItemForSelectedPage() {
    $papaya = $this->getMockApplicationObject(
      array(
        'request' => $this->getMockRequestObject(
          array('theme' => 'sample', 'set_id' => 23, 'page_identifier' => 'SAMPLE_PAGE')
         ),
        'images' => array('items-folder' => 'folder.png')
      )
    );
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiListviewItem'));

    $page = new PapayaContentStructurePage();
    $page->title = 'Page title';
    $page->name = 'SAMPLE_PAGE';

    $navigation = new PapayaAdministrationThemeEditorNavigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem(
      $this->getBuilderFixture(2),
      $items,
      $page,
      0
    );
    $this->assertEquals(
      '<listitem'.
        ' title="Page title"'.
        ' image="folder.png"'.
        ' href="http://www.test.tld/test.html?cmd=values_edit&amp;theme=sample&amp;set_id=23&amp;page_identifier=SAMPLE_PAGE"'.
        ' indent="2"'.
        ' selected="selected"/>',
      $item->getXml()
    );
  }

  private function getBuilderFixture($depth = 0) {
    $iterator = $this
      ->getMockBuilder('RecursiveIteratorIterator')
      ->setConstructorArgs(array($this->getMock('RecursiveIterator')))
      ->getMock();
    $iterator
      ->expects($this->once())
      ->method('getDepth')
      ->will($this->returnValue($depth));
    $builder = $this
      ->getMockBuilder('PapayaUiListviewItemsBuilder')
      ->disableOriginalConstructor()
      ->getMock();
    $builder
     ->expects($this->once())
     ->method('getDataSource')
     ->will($this->returnValue($iterator));
    return $builder;
  }
}
