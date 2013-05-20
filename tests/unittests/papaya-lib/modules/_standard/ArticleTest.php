<?php
require_once(substr(__FILE__, 0, -44).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleStandard' => PAPAYA_INCLUDE_PATH.'modules/_standard/'
  )
);

class PapayaModuleStandardArticleTest extends PapayaTestCase {

  /**
   * @covers PapayaModuleStandardArticle::__construct
   */
  public function testConstructor() {
    $article = new PapayaModuleStandardArticle($page = $this->getContentPageFixture());
    $this->assertAttributeSame($page, '_owner', $article);
  }

  /**
   * @covers PapayaModuleStandardArticle::appendTo
   */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('content');

    $content = new PapayaPluginEditableContent(
      array(
        'title' => 'Sample Title',
        'teaser' => 'Sample <b>Teaser</b>',
        'text' => 'Sample <b>Text</b>'
      )
    );
    $article = new PapayaModuleStandardArticle($this->getContentPageFixture());
    $article->filters($this->getDataFilterFixture());
    $article->content($content);

    $article->appendTo($dom->documentElement);
    $this->assertXmlStringEqualsXmlString(
      '<content>
        <title>Sample Title</title>
        <teaser>Sample <b>Teaser</b></teaser>
        <text>Sample <b>Text</b></text>
      </content>',
      $dom->documentElement->saveXml()
    );
  }

  /**
   * @covers PapayaModuleStandardArticle::appendQuoteTo
   */
  public function testAppendQuoteTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('content');

    $content = new PapayaPluginEditableContent(
      array(
        'title' => 'Sample Title',
        'teaser' => 'Sample <b>Teaser</b>'
      )
    );
    $article = new PapayaModuleStandardArticle($this->getContentPageFixture());
    $article->content($content);

    $article->appendQuoteTo($dom->documentElement);
    $this->assertXmlStringEqualsXmlString(
      '<content>
        <title>Sample Title</title>
        <text>Sample <b>Teaser</b></text>
      </content>',
      $dom->documentElement->saveXml()
    );
  }

  /**
   * @covers PapayaModuleStandardArticle::content
   */
  public function testContentGetAfterSet() {
    $article = new PapayaModuleStandardArticle($this->getContentPageFixture());
    $article->content($content = $this->getMock('PapayaPluginEditableContent'));
    $this->assertSame($content, $article->content());
  }

  /**
   * @covers PapayaModuleStandardArticle::content
   */
  public function testContentGetImplicitCreate() {
    $article = new PapayaModuleStandardArticle($this->getContentPageFixture());
    $this->assertInstanceOf('PapayaPluginEditableContent', $article->content());
  }

  /**
   * @covers PapayaModuleStandardArticle::content
   * @covers PapayaModuleStandardArticle::createEditor
   */
  public function testContentEditorGetImplicitCreate() {
    $article = new PapayaModuleStandardArticle($this->getContentPageFixture());
    $this->assertInstanceOf(
      'PapayaAdministrationPluginEditorFields', $article->content()->editor()
    );
  }

  /**
   * @covers PapayaModuleStandardArticle::cacheable
   */
  public function testCacheableGetAfterSet() {
    $article = new PapayaModuleStandardArticle($this->getContentPageFixture());
    $article->cacheable($cacheIdentifier = $this->getMock('PapayaCacheIdentifierDefinition'));
    $this->assertSame($cacheIdentifier, $article->cacheable());
  }

  /**
   * @covers PapayaModuleStandardArticle::cacheable
   */
  public function testCacheableGetImplicitCreate() {
    $article = new PapayaModuleStandardArticle($this->getContentPageFixture());
    $this->assertInstanceOf('PapayaCacheIdentifierDefinition', $article->cacheable());
  }

  /**
   * @covers PapayaModuleStandardArticle::filters
   */
  public function testFiltersGetAfterSet() {
    $article = new PapayaModuleStandardArticle($this->getContentPageFixture());
    $article->filters($filters = $this->getDataFilterFixture());
    $this->assertSame($filters, $article->filters());
  }

  /**
   * @covers PapayaModuleStandardArticle::filters
   */
  public function testFiltersGetImplicitCreate() {
    $article = new PapayaModuleStandardArticle($this->getContentPageFixture());
    $this->assertInstanceOf('PapayaPluginFilterContent', $article->filters());
  }

  public function getContentPageFixture() {
    $page = $this
      ->getMockBuilder('PapayaUiContentPage')
      ->disableOriginalConstructor()
      ->getMock();
    return $page;
  }

  public function getDataFilterFixture() {
    $filters = $this->getMock('PapayaPluginFilterContent');
    $filters
      ->expects($this->any())
      ->method('applyTo')
      ->will($this->returnArgument(0));
    return $filters;
  }
}