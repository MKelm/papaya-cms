<?php
require_once(substr(dirname(__FILE__), 0, -36).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleTwitter' => PAPAYA_INCLUDE_PATH.'modules/free/Twitter'
  )
);

class PapayaModuleTwitterBoxBaseTest extends PapayaTestCase {

  private function getPapayaCacheServiceObjectFixture($additionalMethods = NULL) {
    $methods = array(
      'setConfiguration', 'verify', 'write', 'read', 'exists', 'delete', 'created'
    );
    if (isset($additionalMethods)) {
      $methods = array_merge($methods, $additionalMethods);
    }
    return $this->getMock('PapayaCacheService', $methods);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::setOwner
  */
  public function testSetOwner() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $owner = $this
      ->getMockBuilder('base_plugin')
      ->disableOriginalConstructor()
      ->getMock();
    $baseObject->setOwner($owner);
    $this->assertAttributeSame($owner, '_owner', $baseObject);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::setBoxData
  */
  public function testSetBoxData() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $data = array('title' => 'Tweets');
    $baseObject->setBoxData($data);
    $this->assertAttributeEquals($data, '_data', $baseObject);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getBoxXml
  */
  public function testGetBoxXml() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $data = array(
      'title' => 'Tweets',
      'screen_name' => 'TwitterUser',
      'follow_caption' => 'Follow me',
      'count' => 5,
      'include_rts' => 0,
      'cache_time' => 500,
      'link_replies' => 1,
      'link_tags' => 1,
      'link_urls' => 1,
      'remove_link_protocols' => 1,
      'link_mailaddresses' => 1,
    );
    $baseObject->setBoxData($data);
    $apiXml =
      '<?xml version="1.0" encoding="UTF-8"?>
      <statuses type="array">
        <status>
          <created_at>Mon Jun 28 06:36:16 +0000 2010</created_at>
          <id>1234567890</id>
          <text>Just another tweet</text>
          <source>AnyClient</source>

          <truncated>false</truncated>
          <in_reply_to_status_id>1111111111</in_reply_to_status_id>
          <in_reply_to_user_id>987654</in_reply_to_user_id>
          <favorited>false</favorited>
          <in_reply_to_screen_name>OtherUser</in_reply_to_screen_name>
          <user>

            <id>876543</id>
            <name>The Twitter User</name>
            <screen_name>TwitterUser</screen_name>
            <location>Anywhere, MA, USA</location>
            <description>Just another random twitter user</description>
            <profile_image_url>http://example.com/twitteruserpic</profile_image_url>

            <url></url>
            <protected>false</protected>
            <followers_count>42</followers_count>
            <profile_background_color>000000</profile_background_color>
            <profile_text_color>FFFFFF</profile_text_color>
            <profile_link_color>FFFF00</profile_link_color>

            <profile_sidebar_fill_color>FF9900</profile_sidebar_fill_color>
            <profile_sidebar_border_color>FF0000</profile_sidebar_border_color>
            <friends_count>23</friends_count>
            <created_at>Wed Apr 23 14:33:54 +0000 2008</created_at>
            <favourites_count>1</favourites_count>
            <utc_offset>-18000</utc_offset>

            <time_zone>Boston</time_zone>
            <profile_background_image_url>http://example.com/tbg.png</profile_background_image_url>
            <profile_background_tile>false</profile_background_tile>
            <profile_use_background_image>true</profile_use_background_image>
            <notifications></notifications>
            <geo_enabled>false</geo_enabled>

            <verified>false</verified>
            <following></following>
            <statuses_count>666</statuses_count>
            <lang>en</lang>
            <contributors_enabled>false</contributors_enabled>
          </user>
          <geo/>

          <coordinates/>
          <place/>
          <contributors/>
        </status>
      </statuses>';
    $cacheService = $this->getPapayaCacheServiceObjectFixture();
    $cacheService
      ->expects($this->once())
      ->method('read')
      ->will($this->returnValue($apiXml));
    $baseObject->setCacheService($cacheService);
    $owner = $this
      ->getMockBuilder('base_plugin')
      ->disableOriginalConstructor()
      ->getMock();
    $owner
      ->expects($this->any())
      ->method('getXHTMLString')
      ->will($this->returnArgument(0));
    $baseObject->setOwner($owner);
    $this->assertXmlStringEqualsXmlString(
      '<twitter screen-name="TwitterUser">
        <title>Tweets</title>
        <follow-link href="http://twitter.com/TwitterUser">Follow me</follow-link>
        <status id ="1234567890" created="2010-06-28 08:36:16">
          <text>Just another tweet</text>
          <source>AnyClient</source>
          <reply-to user-id="987654" status-id="1111111111" screen-name="OtherUser" />
        </status>
      </twitter>',
      $baseObject->getBoxXml()
    );
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getApiXml
  */
  public function testGetApiXmlFromCache() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $cacheService = $this->getPapayaCacheServiceObjectFixture();
    $cacheService
      ->expects($this->once())
      ->method('read')
      ->will($this->returnValue('<status />'));
    $baseObject->setCacheService($cacheService);
    $baseObject->setBoxData(
      array(
        'screen_name' => 'TwitterUser',
        'count' => 5,
        'include_rts' => 0,
        'cache_time' => 500
      )
    );
    $this->assertEquals('<status />', $baseObject->getApiXml());
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getApiXml
  */
  public function testGetApiXmlFromWeb() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $cacheService = $this->getPapayaCacheServiceObjectFixture();
    $cacheService
      ->expects($this->once())
      ->method('read')
      ->will($this->returnValue(NULL));
    $baseObject->setCacheService($cacheService);
    $httpClient = $this->getMock(
      'PapayaHttpClient',
      array('addRequestData', 'send', 'getResponseStatus', 'getResponseData')
    );
    $httpClient
      ->expects($this->once())
      ->method('send')
      ->will($this->returnValue(TRUE));
    $httpClient
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $httpClient
      ->expects($this->once())
      ->method('getResponseData')
      ->will($this->returnValue('<status />'));
    $baseObject->setHttpClient($httpClient);
    $baseObject->setBoxData(
      array(
        'screen_name' => 'TwitterUser',
        'count' => 5,
        'include_rts' => 0,
        'cache_time' => 500
      )
    );
    $this->assertEquals('<status />', $baseObject->getApiXml());
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getApiXml
  */
  public function testGetApiXmlFromCacheFallback() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $cacheService = $this->getPapayaCacheServiceObjectFixture();
    $cacheService
      ->expects($this->atLeastOnce())
      ->method('read')
      ->will(
          $this->onConsecutiveCalls(
            $this->returnValue(NULL),
            $this->returnValue('<status />')
          )
        );
    $baseObject->setCacheService($cacheService);
    $httpClient = $this->getMock(
      'PapayaHttpClient',
      array('addRequestData', 'send', 'getResponseStatus', 'getResponseData')
    );
    $httpClient
      ->expects($this->once())
      ->method('send')
      ->will($this->returnValue(TRUE));
    $httpClient
      ->expects($this->once())
      ->method('getResponseStatus')
      ->will($this->returnValue(200));
    $httpClient
      ->expects($this->once())
      ->method('getResponseData')
      ->will($this->returnValue(NULL));
    $baseObject->setHttpClient($httpClient);
    $baseObject->setBoxData(
      array(
        'screen_name' => 'TwitterUser',
        'count' => 5,
        'include_rts' => 0,
        'cache_time' => 500
      )
    );
    $this->assertEquals('<status />', $baseObject->getApiXml());
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getHttpClient
  */
  public function testGetHttpClient() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $httpClient = $baseObject->getHttpClient();
    $this->assertTrue($httpClient instanceof PapayaHttpClient);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::setHttpClient
  */
  public function testSetHttpClient() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $httpClient = $this->getMock('PapayaHttpClient');
    $baseObject->setHttpClient($httpClient);
    $this->assertAttributeSame($httpClient, '_httpClient', $baseObject);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::getCacheService
  */
  public function testGetCacheService() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $baseObject->papaya($this->getMockApplicationObject());
    $cacheService = $baseObject->getCacheService();
    $this->assertTrue($cacheService instanceof PapayaCacheService);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::setCacheService
  */
  public function testSetCacheService() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $cacheService = $this->getPapayaCacheServiceObjectFixture();
    $baseObject->setCacheService($cacheService);
    $this->assertAttributeSame($cacheService, '_cacheService', $baseObject);
  }

  /**
  * @covers PapayaModuleTwitterBoxBase::_addTwitterLinks
  */
  public function testAddTwitterLinks() {
    $baseObject = new PapayaModuleTwitterBoxBase_TestProxy();
    $baseObject->setBoxData(
      array(
        'remove_link_protocols' => 0,
        'link_replies' => 1,
        'link_tags' => 1,
        'link_urls' => 1,
        'link_mailaddresses' => 1
      )
    );
    $this->assertEquals(
      '<a class="twitterReply" target="_blank" href="http://twitter.com/User">@User</a>'.
      ': More info about '.
      '<a class="twitterHashtag" target="_blank"'.
      ' href="http://search.twitter.com/search?tag=subject">#subject</a> at '.
      '<a class="twitterLink" href="http://bit.ly/subject" target="_blank">http://bit.ly/subject</a>'.
      ' or mail '.
      '<a class="twitterMail" href="mailto:info@subject.info">info@subject.info</a>',
      $baseObject->_addTwitterLinks(
        '@User: More info about #subject at http://bit.ly/subject or mail info@subject.info'
      )
    );
  }
}

/**
* Used to set the protected methods of the actual TwitterBoxBase class public
*/
class PapayaModuleTwitterBoxBase_TestProxy extends PapayaModuleTwitterBoxBase {
  public function getApiXml() {
    return parent::getApiXml();
  }

  public function getHttpClient() {
    return parent::getHttpClient();
  }

  public function setHttpClient($client) {
    parent::setHttpClient($client);
  }

  public function getCacheService() {
    return parent::getCacheService();
  }

  public function  _addTwitterLinks($text) {
    return parent::_addTwitterLinks($text);
  }
}
