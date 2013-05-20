<?php
require_once(substr(__FILE__, 0, -53).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Administration/Page.php');

class PapayaAdministrationPageTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPage::__construct
   */
  public function testConstructor() {
    $page = new PapayaAdministrationPage_TestProxy(
      $layout = $this->getMock('papaya_xsl')
    );
    $this->assertAttributeSame(
      $layout, '_layout', $page
    );
  }

  /**
   * @covers PapayaAdministrationPage
   */
  public function testPageWithoutParts() {
    $layout = $this->getMock('papaya_xsl');
    $layout
      ->expects($this->never())
      ->method('add');
    $layout
      ->expects($this->once())
      ->method('addMenu')
      ->with('');
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $page->execute();
  }

  /**
   * @covers PapayaAdministrationPage
   */
  public function testPageWithContentPart() {
    $layout = $this->getMock('papaya_xsl');
    $layout
      ->expects($this->once())
      ->method('add')
      ->with('<foo/>', 'centercol');
    $layout
      ->expects($this->once())
      ->method('addMenu');
    $content = $this->getMock('PapayaAdministrationPagePart');
    $content
      ->expects($this->once())
      ->method('getXml')
      ->will($this->returnValue('<foo/>'));
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $page->papaya($this->getMockApplicationObject());
    $page->parts()->content = $content;
    $page->execute();
  }

  /**
   * @covers PapayaAdministrationPage::createPart
   */
  public function testCreatePartWithUnknownNameExpectingFalse() {
    $page = new PapayaAdministrationPage_TestProxy($this->getMock('papaya_xsl'));
    $this->assertFalse($page->createPart('NonExistingPart'));
  }

  /**
   * @covers PapayaAdministrationPage::parts
   */
  public function testPartsGetAfterSet() {
    $parts = $this
      ->getMockBuilder('PapayaAdministrationPageParts')
      ->disableOriginalConstructor()
      ->getMock();
    $page = new PapayaAdministrationPage_TestProxy($this->getMock('papaya_xsl'));
    $page->parts($parts);
    $this->assertSame($parts, $page->parts());
  }

  /**
   * @covers PapayaAdministrationPage::toolbar
   */
  public function testToolbarGetAfterSet() {
    $page = new PapayaAdministrationPage_TestProxy($this->getMock('papaya_xsl'));
    $page->toolbar($toolbar = $this->getMock('PapayaUiToolbar'));
    $this->assertSame($toolbar, $page->toolbar());
  }

  /**
   * @covers PapayaAdministrationPage::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $page = new PapayaAdministrationPage_TestProxy($this->getMock('papaya_xsl'));
    $this->assertInstanceOf('PapayaUiToolbar', $page->toolbar());
  }
}

class PapayaAdministrationPage_TestProxy extends PapayaAdministrationPage {

}
