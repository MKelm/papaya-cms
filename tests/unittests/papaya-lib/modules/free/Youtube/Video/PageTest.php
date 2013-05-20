<?php

require_once(substr(dirname(__FILE__), 0, -37).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleYoutube' => PAPAYA_INCLUDE_PATH.'modules/free/Youtube'
  )
);

class PapayaModuleYoutubeVideoPageTest extends PapayaTestCase {

  /**
  * @covers PapayaModuleYoutubeVideoPage::setVideoObject
  */
  public function testSetVideoObject() {
    $pageObject = new PapayaModuleYoutubeVideoPage_TestProxy();
    $videoObject = $this->getMock('PapayaModuleYoutubeVideo');
    $pageObject->setVideoObject($videoObject);
    $this->assertAttributeSame($videoObject, '_pageVideoObject', $pageObject);
  }

  /**
  * @covers PapayaModuleYoutubeVideoPage::getVideoObject
  */
  public function testGetVideoObject() {
    $pageObject = new PapayaModuleYoutubeVideoPage_TestProxy();
    $videoObject = $pageObject->getVideoObject();
    $this->assertTrue($videoObject instanceof PapayaModuleYoutubeVideo);
  }

  /**
  * @covers PapayaModuleYoutubeVideoPage::getParsedteaser
  */
  public function testGetParsedData() {
    $pageObject = new PapayaModuleYoutubeVideoPage_TestProxy();
    $videoObject = $this->getMock('PapayaModuleYoutubeVideo');
    $videoObject
      ->expects($this->once())
      ->method('getPageXml')
      ->will($this->returnValue('<video/>'));
    $pageObject->setVideoObject($videoObject);
    $this->assertEquals('<video/>', $pageObject->getParsedData());
  }
  
  public function testGetParsedTeaser() {
    $pageObject = new PapayaModuleYoutubeVideoPage_TestProxy();
    $videoObject = $this->getMock('PapayaModuleYoutubeVideo');
    $videoObject
      ->expects($this->once())
      ->method('getTeaserXml')
      ->will($this->returnValue('<teaser/>'));
    $pageObject->setVideoObject($videoObject);
    $this->assertEquals('<teaser/>', $pageObject->getParsedTeaser());
  }
}

/**
* This class is derived from the original YoutubePage class
* and is used to provide an argument-free constructor.
*/
class PapayaModuleYoutubeVideoPage_TestProxy extends PapayaModuleYoutubeVideoPage {

  public function __construct() {
    // Nothing to do here
  }

  public function initializeParams() {
   // Nothing to do here
  }
}
