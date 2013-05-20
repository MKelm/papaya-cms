<?php
require_once(substr(dirname(__FILE__), 0, -37).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleYoutube' => PAPAYA_INCLUDE_PATH.'modules/free/Youtube'
  )
);

class PapayaModuleYoutubeVideoBoxTest extends PapayaTestCase {

  /**
  * @covers PapayaModuleYoutubeVideoBox::setVideoObject
  */
  public function testSetVideoObject() {
    $boxObject = new PapayaModuleYoutubeVideoBox_TestProxy();
    $videoObject = $this->getMock('PapayaModuleYoutubeVideo');
    $boxObject->setVideoObject($videoObject);
    $this->assertAttributeSame($videoObject, '_boxVideoObject', $boxObject);
  }

  /**
  * @covers PapayaModuleYoutubeVideoBox::getVideoObject
  */
  public function testGetVideoObject() {
    $boxObject = new PapayaModuleYoutubeVideoBox_TestProxy();
    $videoObject = $boxObject->getVideoObject();
    $this->assertTrue($videoObject instanceof PapayaModuleYoutubeVideo);
  }

  /**
  * @covers PapayaModuleYoutubeVideoBox::getParsedData
  */
  public function testGetParsedData() {
    $boxObject = new PapayaModuleYoutubeVideoBox_TestProxy();
    $videoObject = $this->getMock('PapayaModuleYoutubeVideo');
    $videoObject
      ->expects($this->once())
      ->method('getBoxXml')
      ->will($this->returnValue('<youtubebox/>'));
    $boxObject->setVideoObject($videoObject);
    $this->assertEquals('<youtubebox/>', $boxObject->getParsedData());
  }
}

/**
* This class is derived from the original YoutubePage class
* and is used to provide an argument-free constructor.
*/
class PapayaModuleYoutubeVideoBox_TestProxy extends PapayaModuleYoutubeVideoBox {
  public function __construct() {
    // Nothing to do here
  }

  public function initializeParams() {
   // Nothing to do here
  }
}
