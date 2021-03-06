<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Toolbar/Group.php');

class PapayaUiToolbarGroupTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarGroup::__construct
  */
  public function testConstructor() {
    $group = new PapayaUiToolbarGroup('group caption');
    $this->assertEquals(
      'group caption', $group->caption
    );
  }

  /**
  * @covers PapayaUiToolbarGroup::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $group = new PapayaUiToolbarGroup('group caption');
    $elements = $this->getMock('PapayaUiToolbarElements', array(), array($group));
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $group->elements($elements);
    $this->assertInstanceOf('PapayaXmlElement', $group->appendTo($dom->documentElement));
    $this->assertEquals(
      '<group title="group caption"/>',
      $dom->documentElement->saveFragment()
    );
  }

  /**
  * @covers PapayaUiToolbarGroup::appendTo
  */
  public function testAppendToWithoutElements() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $group = new PapayaUiToolbarGroup('group caption');
    $elements = $this->getMock('PapayaUiToolbarElements', array(), array($group));
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(0));
    $group->elements($elements);
    $this->assertNull($group->appendTo($dom->documentElement));
    $this->assertEquals(
      '<sample/>',
      $dom->documentElement->saveXml()
    );
  }
}
