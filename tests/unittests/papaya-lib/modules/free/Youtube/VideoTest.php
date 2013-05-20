<?php

require_once(substr(dirname(__FILE__), 0, -32).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleYoutube' => PAPAYA_INCLUDE_PATH.'modules/free/Youtube'
  )
);

class PapayaModuleYoutubeVideoTest extends PapayaTestCase {

  /**
  * @covers PapayaModuleYoutubeVideo::setOwner
  */
  public function testSetOwner() {
    $videoObject = new PapayaModuleYoutubeVideo_TestProxy();
    $owner = $this->getMock('OwnerClass');
    $videoObject->setOwner($owner);
    $this->assertAttributeSame($owner, '_owner', $videoObject);
  }

  /**
  * @covers PapayaModuleYoutubeVideo::setPageData
  */
  public function testSetPageData() {
    $videoObject = new PapayaModuleYoutubeVideo_TestProxy();
    $data = array("title" => "new video");
    $videoObject->setPageData($data);
    $this->assertAttributeSame($data, '_data', $videoObject);
  }

  /**
   * @covers PapayaModuleYoutubeVideo::setBoxData
   */
  public function testSetBoxData() {
    $videoObject = new PapayaModuleYoutubeVideo_TestProxy();
    $boxData = array("title" => "new video");
    $videoObject->setBoxData($boxData);
    $this->assertAttributeSame($boxData, '_boxData', $videoObject);
  }

  /**
  * @covers PapayaModuleYoutubeVideo::setPapayaXmlDomObject
  */
  public function testSetPapayaXmlDomObject() {
    $videoObject = new PapayaModuleYoutubeVideo_TestProxy();
    $papayaXmlDomObject = $this->getMock('PapayaXmlDocument');
    $videoObject->setPapayaXmlDomObject($papayaXmlDomObject);
    $this->assertAttributeSame($papayaXmlDomObject, '_papayaXmlDomObject', $videoObject);
  }

  /**
  * @covers PapayaModuleYoutubeVideo::getPapayaXmlDomObject
  */
  public function testGetPapayaXmlDomObject() {
    $videoObject = new PapayaModuleYoutubeVideo_TestProxy();
    $papayaXmlDomObject = $videoObject->getPapayaXmlDomObject();
    $this->assertInstanceOf('PapayaXmlDocument', $papayaXmlDomObject);
  }

  /**
  * @covers PapayaModuleYoutubeVideo::getPageXml
  * @dataProvider getPageXmlProvider
  *
  * @param int $serNoCookie
  * @param string $videoFormat
  * @param string $url
  * @param int $height
  */
  public function testGetPageXml($setNoCookie, $videoFormat, $url, $height) {
    $videoObject = new PapayaModuleYoutubeVideo_TestProxy();
    $data = array(
      "title" => "new video",
      "subtitle" => "",
      "youtube_video_id" => "wPOgvzVOQig",
      "player_width" => 560,
      "video_format" => $videoFormat,
      "autoplay" => 0,
      "related" => 0,
      "show_info" => 1,
      "controls" => 1,
      "set_no_cookie" => $setNoCookie,
      "youtube_url" => "http://www.youtube.com",
      "youtube_no_cookie_url" => "http://www.youtube-nocookie.com",
      "imgalign" => "left",
      "breakstyle" => "none",
      "teaser" => "this is the teaser text",
      "image" => "25732c73d90ab89fc667e15fca30c7e9,180,202,max",
      "text" => "A video from Youtube"
    );
    $videoObject->setPageData($data);
    $owner = $this->getMock('base_object', array('getPapayaImageTag'));
    $videoObject->setOwner($owner);
    $this->assertXmlStringEqualsXmlString(
      '<video>
        <title>new video</title>
        <subtitle/>
        <player videoId="wPOgvzVOQig" width="560" height="'.$height.'"
          autoplay="0" rel="0" info="1" controls="1" url="'.$url.'"/>
        <teaser>this is the teaser text</teaser>
        <image align="left" break="none"/>
        <text>A video from Youtube</text>
      </video>',
      $videoObject->getPageXml()
    );
  }

  //---------------dataProvider---------------
  public static function getPageXmlProvider() {
    return array(
      "no_cookie_16_9" => array("1", "16:9", "http://www.youtube-nocookie.com", 316),
      "no_cookie_4_3" => array("1", "4:3", "http://www.youtube-nocookie.com", 421),
      "cookie_16_9" => array("0", "16:9", "http://www.youtube.com", 316),
      "cookie_4_3" => array("0", "4:3", "http://www.youtube.com", 421)
    );
  }
  
  /**
  * @covers PapayaModuleYoutubeVideo::getTeaserXml
  */
  public function testGetTeaserXml() {
    $videoObject = new PapayaModuleYoutubeVideo_TestProxy();
    $data = array(
      "title" => "new video",
      "subtitle" => "",
      "teaser" => "this is the teaser text",
      "breakstyle" => "none",
      "imgalign" => "left",
      "image" => "25732c73d90ab89fc667e15fca30c7e9,180,202,max",
    );
    $videoObject->setPageData($data);
    $owner = $this->getMock('base_object', array('getPapayaImageTag'));
    $videoObject->setOwner($owner);
    $xml = '<title>new video</title><subtitle></subtitle><text>this is the teaser text</text><image align="left" break="none"/>';
    $this->assertEquals(
      $xml,
      $videoObject->getTeaserXml()
    );
  }
  
  /**
  * @covers PapayaModuleYoutubeVideo::getBoxXml
  * @dataProvider getBoxXmlProvider
  *
  * @param type $setNoCookie
  * @param type $videoFormat
  * @param type $url
  * @param type $height
  */
  public function testGetBoxXml($setNoCookie, $videoFormat, $url, $height) {
    $videoObject = new PapayaModuleYoutubeVideo_TestProxy();
    $data = array(
      "title" => "new video",
      "youtube_video_id" => "wPOgvzVOQig",
      "player_width" => 560,
      "video_format" => $videoFormat,
      "autoplay" => 0,
      "related" => 0,
      "show_info" => 1,
      "controls" => 1,
      "set_no_cookie" => $setNoCookie,
      "youtube_url" => "http://www.youtube.com",
      "youtube_no_cookie_url" => "http://www.youtube-nocookie.com",
      "text" => "A video from Youtube"
    );
    $videoObject->setBoxData($data);
    $owner = $this->getMock('base_object', array('getPapayaImageTag'));
    $videoObject->setOwner($owner);
    $this->assertXmlStringEqualsXmlString(
      '<youtubebox>
        <title>new video</title>
        <player videoId="wPOgvzVOQig" width="560" height="'.$height.'"
         autoplay="0" rel="0" info="1" controls="1" url="'.$url.'"/>
        <text>A video from Youtube</text>
      </youtubebox>',
      $videoObject->getBoxXml()
    );
  }

  //---------------dataProvider---------------
  public static function getBoxXmlProvider() {
    return array(
      "cookie_16_9" => array("0", "16:9", "http://www.youtube.com", 316),
      "cookie_4_3" => array("0", "4:3", "http://www.youtube.com", 421),
      "no_cookie_16_9" => array("1", "16:9", "http://www.youtube-nocookie.com", 316),
      "no_cookie_4_3" => array("1", "4:3", "http://www.youtube-nocookie.com", 421),
    );
  }

}

class PapayaModuleYoutubeVideo_TestProxy extends PapayaModuleYoutubeVideo {
  public function __construct() {
    // Nothing to do here
  }
}
