<?php
require_once(substr(__FILE__, 0, -42).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'modules/free/rss/Reader.php');

class PapayaLibModulesFreeRssReaderTest extends PapayaTestCase {

  private $_htmlPurifierObject = NULL;

  private function loadReaderObjectFixture() {
    $options = array('PAPAYA_DB_TABLEPREFIX' => 'papaya');
    $_pageBaseObject = new RssReader();
    $_pageBaseObject->papaya(
      $this->getMockApplicationObject(
        array(
          'options',
          $this->getMockConfigurationObject($options)
        )
      )
    );
    $_pageBaseObject->setConfiguration($this->getMockConfigurationObject($options));
    return $_pageBaseObject;
  }

  private function getHtmlPurifierObjectFixture() {
    if (is_object($this->_htmlPurifierObject)) {
      $baseObject = $this->loadReaderObjectFixture();
      $this->_htmlPurifierObject = $baseObject->getHtmlPurifierObject();
    }
    return $this->_htmlPurifierObject;
  }

  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
  * @covers RssReader::parseFeed
  */
  public function testParseFeed() {
    $baseObject = $this->loadReaderObjectFixture();
    $responseData = '<feed xmlns="http://www.w3.org/2005/Atom">';
    $responseData .= '<title>Test Feed</title>';
    $responseData .= '<logo>http://domain.tld/any_image.png</logo>';
    $responseData .= '<updated>2010-05-10T00:00:00</updated>';
    $responseData .= '<link href="http://domain.tld/" />';
    $responseData .= '<entry><id>1</id><title>Entry 1</title>';
    $responseData .= '<link rel="alternate" type="text/html" href="/link.php"/>';
    $responseData .= '<published>2010-05-10T00:00:00</published><author><name>Author</name></author>';
    $responseData .= '<content>any content</content></entry>';
    $responseData .= '</feed>';
    $expected = array(
      'title' => 'Test Feed',
      'logo' => 'http://domain.tld/any_image.png',
      'updated' => '2010-05-10 02:00:00',
      'linkPrefix' => 'http://domain.tld/',
      'entries' => array(
        1 => array(
          'title' => 'Entry 1',
          'link' => 'http://domain.tld/link.php',
          'published' => '2010-05-10 02:00:00',
          'author' => 'Author',
          'content' => 'any content'
        )
      )
    );
    $baseObject->setHttpClient(new httpClientProxy(TRUE, 200, $responseData));
    $baseObject->setHtmlPurifierObject(new htmlPurifierProxy);
    $this->assertSame($expected, $baseObject->parseFeed('any url'));
  }

  /**
  * @covers RssReader::parseFeed
  */
  public function testParseFeedWithHttpResultFalse() {
    try {
      $baseObject = $this->loadReaderObjectFixture();
      $baseObject->setHttpClient(new httpClientProxy(FALSE, 404, NULL));
      $baseObject->parseFeed('any url');
      $this->fail('Expected exception not thrown');
    } catch (Exception $e) {
    }
  }

  /**
  * @covers RssReader::parseFeed
  */
  public function testParseFeedWithInvalidResponse() {
    try {
      $baseObject = $this->loadReaderObjectFixture();
      $baseObject->setHttpClient(new httpClientProxy(TRUE, 404, NULL));
      $baseObject->parseFeed('any url');
      $this->fail('Expected exception not thrown');
    } catch (Exception $e) {
    }
  }

  /***************************************************************************/
  /** Helper / instances                                                     */
  /***************************************************************************/

  /**
  * @covers RssReader::setConfiguration
  */
  public function testSetConfiguration() {
    $baseObject = $this->loadReaderObjectFixture();
    $baseObject->setConfiguration(TRUE);
    $this->assertAttributeEquals(TRUE, '_configuration', $baseObject);
  }

  /**
  * @covers RssReader::getHttpClient
  */
  public function testGetHttpClient() {
    $baseObject = $this->loadReaderObjectFixture();
    $this->assertTrue($baseObject->getHttpClient() instanceof PapayaHttpClient);
  }

  /**
  * @covers RssReader::setHttpClient
  */
  public function testSetHttpClientObject() {
    $baseObject = $this->loadReaderObjectFixture();
    $baseObject->setHttpClient(TRUE);
    $this->assertAttributeEquals(TRUE, '_httpClient', $baseObject);
  }

  /**
  * @covers RssReader::getHtmlPurifierObject
  */
  public function testGetHtmlPurifierObject() {
    $baseObject = $this->loadReaderObjectFixture();
    $this->assertTrue($baseObject->getHtmlPurifierObject() instanceof base_htmlpurifier);
  }

  /**
  * @covers RssReader::setHtmlPurifierObject
  */
  public function testSetHtmlPurifierObject() {
    $baseObject = $this->loadReaderObjectFixture();
    $baseObject->setHtmlPurifierObject(TRUE);
    $this->assertAttributeEquals(TRUE, '_htmlPurifier', $baseObject);
  }

  /**
  * @covers RssReader::validateURL
  * @dataProvider validateURLDataProvider
  */
  public function testValidateURL($link, $expected) {
    $baseObject = $this->loadReaderObjectFixture();
    $prefix = 'http://domain.tld/';
    $this->assertSame($expected, $baseObject->validateURL($link, $prefix));
  }

  /**
  * @covers RssReader::validateLinksInContent
  * @dataProvider validateLinksInContentDataProvider
  */
  public function testValidateLinksInContent($content, $expected) {
    $baseObject = $this->loadReaderObjectFixture();
    $prefix = 'http://domain.tld/';
    $this->assertSame($expected, $baseObject->validateLinksInContent($content, $prefix));
  }

  /**
  * @covers RssReader::verifySimpleHTMLInput
  */
  public function testVerifySimpleHTMLInput() {
    $baseObject = $this->loadReaderObjectFixture();
    $baseObject->setHtmlPurifierObject(new htmlPurifierProxy);
    $this->assertSame('text', $baseObject->verifySimpleHTMLInput('text'));
  }

  /**
  * @covers RssReader::translateDateTime
  */
  public function testTranslateDateTime() {
    $baseObject = $this->loadReaderObjectFixture();
    $expected = '2010-05-11 18:00:00';
    $result = $baseObject->translateDateTime('2010-05-11T17:00:00+01:00');
    $this->assertSame($expected, $result);
  }

  /***************************************************************************/
  /** Data Provider                                                          */
  /***************************************************************************/

  public static function validateURLDataProvider() {
    return array(
      'prefix needed' => array('/index.php', 'http://domain.tld/index.php'),
      'no prefix needed' => array('http://domain.tld/index.php', 'http://domain.tld/index.php')
    );
  }

  public static function validateLinksInContentDataProvider() {
    return array(
      'contains a link' => array(
        '<a href="/index.php">link</a>',
        '<a href="http://domain.tld/index.php">link</a>'),
      'contains a image' => array(
        '<img src="/logo.png"/>',
        '<img src="http://domain.tld/logo.png"/>'),
      'contains a link and an image' => array(
        '<a href="/index.php"><img src="/logo.png"/></a>',
        '<a href="http://domain.tld/index.php"><img src="http://domain.tld/logo.png"/></a>')
    );
  }
}

class httpClientProxy {
  public $sendResult;
  public $responseStatus;
  public $responseData;
  public function __construct($sendResult, $responseStatus, $responseData) {
    $this->sendResult = $sendResult;
    $this->responseStatus = $responseStatus;
    $this->responseData = $responseData;
  }
  public function send() {
    return $this->sendResult;
  }
  public function getResponseStatus() {
    return $this->responseStatus;
  }
  public function getResponseData() {
    return $this->responseData;
  }
}

class htmlPurifierProxy {
  public function setUp() {
    return;
  }
  public function addAttribute() {
    return;
  }
  public function purifyInput($text) {
    return $text;
  }
}

?>