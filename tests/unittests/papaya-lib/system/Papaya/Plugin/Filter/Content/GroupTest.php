<?php
require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaPluginFilterContentGroupTest extends PapayaTestCase {

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testConstructor() {
    $filter = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $this->assertSame($page, $filter->getPage());
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testAddAndIterator() {
    $filter = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filter->add(
      $filterOne = $this->getMock('PapayaPluginFilterContent')
    );
    $filter->add(
      $filterTwo = $this->getMock('PapayaPluginFilterContent')
    );
    $this->assertSame(
      array($filterOne, $filterTwo),
      iterator_to_array($filter, FALSE)
    );
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testPrepare() {
    $filterOne = $this->getMock('PapayaPluginFilterContent');
    $filterOne
      ->expects($this->once())
      ->method('prepare')
      ->with('data');

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->prepare('data');
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testPrepareBC() {
    $filterOne = $this->getMock(
      'stdClass',
      array('initialize', 'prepareFilterData', 'loadFilterData')
    );
    $filterOne
      ->expects($this->once())
      ->method('initialize')
      ->with($this->isInstanceOf('stdClass'));
    $filterOne
      ->expects($this->once())
      ->method('prepareFilterData')
      ->with(array('text' => 'data'), array('text'));
    $filterOne
      ->expects($this->once())
      ->method('loadFilterData')
      ->with(array('text' => 'data'));

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->prepare('data');
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testApplyTo() {
    $filterOne = $this->getMock('PapayaPluginFilterContent');
    $filterOne
      ->expects($this->once())
      ->method('applyTo')
      ->with('data')
      ->will($this->returnValue('success'));

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->applyTo('data');
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testApplyToBC() {
    $filterOne = $this->getMock(
      'stdClass',
      array('applyFilterData')
    );
    $filterOne
      ->expects($this->once())
      ->method('applyFilterData')
      ->with('data')
      ->will($this->returnValue('success'));

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->applyTo('data');
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $node = $dom->appendElement('test');
    $filterOne = $this->getMock('PapayaPluginFilterContent');
    $filterOne
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->appendTo($node);
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testAppendToBC() {
    $dom = new PapayaXmlDocument();
    $node = $dom->appendElement('test');
    $filterOne = $this->getMock(
      'stdClass',
      array('getFilterData')
    );
    $filterOne
      ->expects($this->once())
      ->method('getFilterData')
      ->with()
      ->will($this->returnValue('success'));

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->appendTo($node);
    $this->assertEquals('<test>success</test>', $node->saveXml());
  }

  public function getPageFixture() {
    $page = $this
      ->getMockBuilder('PapayaUiContentPage')
      ->disableOriginalConstructor()
      ->getMock();
    return $page;
  }

}
