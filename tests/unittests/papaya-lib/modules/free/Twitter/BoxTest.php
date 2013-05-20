<?php
require_once(substr(dirname(__FILE__), 0, -32).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleTwitter' => PAPAYA_INCLUDE_PATH.'modules/free/Twitter'
  )
);

class PapayaModuleTwitterBoxTest extends PapayaTestCase {

  /**
  * @covers PapayaModuleTwitterBox::setBaseObject
  */
  public function testSetBaseObject() {
    $boxObject = new PapayaModuleTwitterBox_TestProxy();
    $baseObject = $this->getMock('PapayaModuleTwitterBoxBase');
    $boxObject->setBaseObject($baseObject);
    $this->assertAttributeSame($baseObject, '_baseObject', $boxObject);
  }

  /**
  * @covers PapayaModuleTwitterBox::getBaseObject
  */
  public function testGetBaseObject() {
    $boxObject = new PapayaModuleTwitterBox_TestProxy();
    $this->assertInstanceOf('PapayaModuleTwitterBoxBase', $boxObject->getBaseObject());
  }

  /**
  * @covers PapayaModuleTwitterBox::getParsedData
  */
  public function testGetParsedData() {
    $boxObject = new PapayaModuleTwitterBox_TestProxy();
    $baseObject = $this->getMock('PapayaModuleTwitterBoxBase');
    $baseObject
      ->expects($this->once())
      ->method('getBoxXml')
      ->will($this->returnValue('<twitter/>'));
    $boxObject->setBaseObject($baseObject);
    $this->assertEquals('<twitter/>', $boxObject->getParsedData());
  }
}

/**
* TwitterBoxProxy
*
* This class is derived from the original TwitterBox class
* and is used to provide an argument-free constructor.
*/
class PapayaModuleTwitterBox_TestProxy extends PapayaModuleTwitterBox {
  public function __construct() {
    // Nothing to do here
  }
}
