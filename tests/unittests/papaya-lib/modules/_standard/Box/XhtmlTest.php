<?php
require_once(substr(__FILE__, 0, -46).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleStandard' => PAPAYA_INCLUDE_PATH.'modules/_standard/'
  )
);

class PapayaModuleStandardBoxXhtmlTest extends PapayaTestCase {

  /**
   * @covers PapayaModuleStandardBoxXhtml::appendTo
   */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('content');

    $content = new PapayaPluginEditableContent(
      array(
        'text' => 'Sample <b>Text</b>'
      )
    );
    $article = new PapayaModuleStandardBoxXhtml();
    $article->content($content);

    $article->appendTo($dom->documentElement);
    $this->assertXmlStringEqualsXmlString(
      '<content>Sample <b>Text</b></content>',
      $dom->documentElement->saveXml()
    );
  }

  /**
   * @covers PapayaModuleStandardBoxXhtml::content
   */
  public function testContentGetAfterSet() {
    $article = new PapayaModuleStandardBoxXhtml();
    $article->content($content = $this->getMock('PapayaPluginEditableContent'));
    $this->assertSame($content, $article->content());
  }

  /**
   * @covers PapayaModuleStandardBoxXhtml::content
   */
  public function testContentGetImplicitCreate() {
    $article = new PapayaModuleStandardBoxXhtml();
    $this->assertInstanceOf('PapayaPluginEditableContent', $article->content());
  }

  /**
   * @covers PapayaModuleStandardBoxXhtml::content
   * @covers PapayaModuleStandardBoxXhtml::createEditor
   */
  public function testContentEditorGetImplicitCreate() {
    $article = new PapayaModuleStandardBoxXhtml();
    $this->assertInstanceOf('PapayaAdministrationPluginEditorFields', $article->content()->editor());
  }

  /**
   * @covers PapayaModuleStandardBoxXhtml::cacheable
   */
  public function testCacheableGetAfterSet() {
    $article = new PapayaModuleStandardBoxXhtml();
    $article->cacheable($cacheIdentifier = $this->getMock('PapayaCacheIdentifierDefinition'));
    $this->assertSame($cacheIdentifier, $article->cacheable());
  }

  /**
   * @covers PapayaModuleStandardBoxXhtml::cacheable
   */
  public function testCacheableGetImplicitCreate() {
    $article = new PapayaModuleStandardBoxXhtml();
    $this->assertInstanceOf('PapayaCacheIdentifierDefinition', $article->cacheable());
  }

}
