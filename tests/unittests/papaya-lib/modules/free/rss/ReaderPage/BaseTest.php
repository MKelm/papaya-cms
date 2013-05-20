<?php
require_once(substr(__FILE__, 0, -52).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'modules/free/rss/ReaderPage/Base.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/free/rss/Reader.php');

class PapayaLibModulesFreeRssReaderPageBaseTest extends PapayaTestCase {

  private function loadPageBaseObjectFixture($owner = NULL) {
    $options = array('PAPAYA_DB_TABLEPREFIX' => 'papaya');
    $_pageBaseObject = new RssReaderPageBase($owner);
    $_pageBaseObject->setConfiguration($this->getMockConfigurationObject($options));
    return $_pageBaseObject;
  }

  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
  * @covers RssReaderPageBase::__construct
  */
  public function testConstructor() {
    $stdClass = new stdClass;
    $baseObject = new RssReaderPageBase($stdClass);
    $this->assertAttributeSame($stdClass, '_owner', $baseObject);
  }

  /**
  * @covers RssReaderPageBase::getXML
  */
  public function testGetXML() {
    $baseObject = $this->loadPageBaseObjectFixture(new ownerProxy);
    $expected = '<title>Titel</title><text>Text</text>';
    $data = array('title' => 'Titel', 'text' => 'Text', 'feedUrl' => 'http://');
    $baseObject->setPageData($data);
    $readerObject = $this->getMock('RssReader', array('parseFeed'));
    $readerObject
      ->expects($this->once())
      ->method('parseFeed')
      ->will($this->returnValue(array()));
    $baseObject->setReaderObject($readerObject);
    $this->assertSame($expected, $baseObject->getXML());
  }

  /**
  * @covers RssReaderPageBase::getParsedRssFeedXML
  * @dataProvider getParsedRssFeedXMLDataProvider
  */
  public function testGetParsedRssFeedXML($logo) {
    $baseObject = $this->loadPageBaseObjectFixture(new ownerProxy);
    $expected = '<feed last-modified="2010-05-07" ';
    $expected .= (!empty($logo)) ?
      sprintf('logo="%s">', $logo) :
      '>';
    $expected .= '<title>Test Feed</title>';
    $expected .= '<entry published="2010-05-07" href="http://domain.tld">';
    $expected .= '<title>Entry</title>';
    $expected .= '<author>Author name</author>';
    $expected .= '<content>Content</content>';
    $expected .= '</entry>';
    $expected .= '</feed>';
    $feed = array(
      'updated' => '2010-05-07',
      'title' => 'Test Feed',
      'entries' => array(
        'entry-id-1' => array(
          'published' => '2010-05-07',
          'link' => 'http://domain.tld',
          'title' => 'Entry',
          'author' => 'Author name',
          'content' => 'Content'
        )
      )
    );
    if (!empty($logo)) {
      $feed['logo'] = $logo;
    }
    $this->assertEquals($expected, $baseObject->getParsedRssFeedXML($feed));
  }

  /**
  * @covers RssReaderPageBase::addMessage
  */
  public function testAddMessage() {
    $baseObject = $this->loadPageBaseObjectFixture();
    $baseObject->addMessage('Error 1', 'error');
    $this->assertAttributeSame(
      array('error' => array('Error 1')),
      '_messages',
      $baseObject
    );
  }

  /**
  * @covers RssReaderPageBase::getMessagesXML
  */
  public function testGetMessagesXML() {
    $expected = '<messages>';
    $expected .= '<message type="error">Error 1</message>';
    $expected .= '<message type="error">Error 2</message>';
    $expected .= '<message type="warning">Warning 1</message>';
    $expected .= '<message type="info">Info 1</message>';
    $expected .= '</messages>';
    $baseObject = $this->loadPageBaseObjectFixture();
    $baseObject->addMessage('Error 1', 'error');
    $baseObject->addMessage('Error 2', 'error');
    $baseObject->addMessage('Warning 1', 'warning');
    $baseObject->addMessage('Info 1', 'info');
    $this->assertSame($expected, $baseObject->getMessagesXML());
  }

  /***************************************************************************/
  /** Helper / instances                                                     */
  /***************************************************************************/

  /**
  * @covers RssReaderPageBase::setConfiguration
  */
  public function testSetConfiguration() {
    $baseObject = $this->loadPageBaseObjectFixture();
    $baseObject->setConfiguration(TRUE);
    $this->assertAttributeEquals(TRUE, '_configuration', $baseObject);
  }

  /**
  * @covers RssReaderPageBase::setPageData
  */
  public function testSetPageData() {
    $baseObject = $this->loadPageBaseObjectFixture();
    $data = array('test' => 1);
    $baseObject->setPageData($data);
    $this->assertAttributeSame($data, '_data', $baseObject);
  }

  /**
  * @covers RssReaderPageBase::setPageParams
  */
  public function testSetPageParams() {
    $baseObject = $this->loadPageBaseObjectFixture();
    $params = array('test' => 1);
    $baseObject->setPageParams($params);
    $this->assertAttributeSame($params, '_params', $baseObject);
  }


  /**
  * @covers RssReaderPageBase::getReaderObject
  */
  public function testGetReaderObject() {
    $baseObject = $this->loadPageBaseObjectFixture();
    $reader = $baseObject->getReaderObject();
    $this->assertInstanceOf('RssReader', $reader);
  }

  /**
  * @covers RssReaderPageBase::setReaderObject
  */
  public function testSetReaderObject() {
    $baseObject = $this->loadPageBaseObjectFixture();
    $stdObject = new stdClass();
    $baseObject->setReaderObject($stdObject);
    $this->assertAttributeSame($stdObject, '_rssReaderObject', $baseObject);
  }

  /**
  * @covers RssReaderPageBase::validateLink
  */
  public function testValidateLink() {
    $baseObject = $this->loadPageBaseObjectFixture();
    $readerObject = $this->getMock('RssReader', array('validateURL'));
    $readerObject
      ->expects($this->once())
      ->method('validateURL')
      ->will($this->returnValue(TRUE));
    $baseObject->setReaderObject($readerObject);
    $this->assertTrue($baseObject->validateLink('any', 'params'));
  }

  /***************************************************************************/
  /** Data Provider                                                          */
  /***************************************************************************/

  public static function getParsedRssFeedXMLDataProvider() {
    return array(
      'with logo' => array('logo.png'),
      'without logo' => array('')
    );
  }
}

class ownerProxy extends stdClass {

  public function getXHTMLString($text) {
    return $text;
  }

}
?>