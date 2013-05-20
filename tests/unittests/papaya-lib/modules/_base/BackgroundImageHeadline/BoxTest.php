<?php
require_once(substr(dirname(__FILE__), 0, -49).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'modules/_base/BackgroundImageHeadline/Box.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/_base/BackgroundImageHeadline/Box/Base.php');

class BackgroundImageHeadlineBoxTest extends PapayaTestCase {
  /**
  * Get an instance of the BackgroundImageHeadlineBox class to be tested
  *
  * @return BackgroundImageHeadlineBoxProxy
  */
  private function getBoxObjectFixture() {
    return new BackgroundImageHeadlineBoxProxy();
  }

  /**
  * @covers BackgroundImageHeadlineBox::getParsedData
  */
  public function testGetParsedData() {
    $boxObject = $this->getBoxObjectFixture();
    $baseObject = $this->getMock('BackgroundImageHeadlineBoxBase');
    $baseObject
      ->expects($this->once())
      ->method('getBoxXml')
      ->will($this->returnValue('<box />'));
    $boxObject->setBaseObject($baseObject);
    $this->assertEquals('<box />', $boxObject->getParsedData());
  }
  /**
  * @covers BackgroundImageHeadlineBox::setBaseObject
  */
  public function testSetBaseObject() {
    $boxObject = $this->getBoxObjectFixture();
    $baseObject = $this->getMock('BackgroundImageHeadlineBoxBase');
    $boxObject->setBaseObject($baseObject);
    $this->assertAttributeSame($baseObject, '_baseObject', $boxObject);
  }

  /**
  * @covers BackgroundImageHeadlineBox::getBaseObject
  */
  public function testGetBaseObject() {
    $boxObject = $this->getBoxObjectFixture();
    $baseObject = $boxObject->getBaseObject();
    $this->assertTrue($baseObject instanceof BackgroundImageHeadlineBoxBase);
  }
}

class BackgroundImageHeadlineBoxProxy extends BackgroundImageHeadlineBox {
  /**
  * Constructor
  *
  * @return BackgroundImageHeadlineBoxProxy
  */
  public function __construct() {
    // No functionality, just override the parent's constructor to get rid of arguments
  }
}
?>