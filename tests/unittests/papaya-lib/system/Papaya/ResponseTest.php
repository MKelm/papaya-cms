<?php
require_once(substr(__FILE__, 0, -41).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Response.php');

class PapayaResponseTest extends PapayaTestCase {

  /**
  * @covers PapayaResponse::helper
  */
  public function testHelperSetHelper() {
    $response = new PapayaResponse();
    $helper = $this->getMock('PapayaResponseHelper');
    $response->helper($helper);
    $this->assertAttributeSame(
      $helper, '_helper', $response
    );
  }

  /**
  * @covers PapayaResponse::helper
  */
  public function testHelperGetHelperAfterSet() {
    $response = new PapayaResponse();
    $helper = $this->getMock('PapayaResponseHelper');
    $this->assertSame(
      $helper, $response->helper($helper)
    );
  }

  /**
  * @covers PapayaResponse::helper
  */
  public function testHelperGetHelperImplizitCreate() {
    $response = new PapayaResponse();
    $this->assertInstanceOf(
      'PapayaResponseHelper', $response->helper()
    );
  }

  /**
  * @covers PapayaResponse::headers
  */
  public function testHeadersSetHeaders() {
    $response = new PapayaResponse();
    $headers = $this->getMock('PapayaResponseHeaders');
    $response->headers($headers);
    $this->assertAttributeSame(
      $headers, '_headers', $response
    );
  }

  /**
  * @covers PapayaResponse::headers
  */
  public function testHeadersGetHeadersAfterSet() {
    $response = new PapayaResponse();
    $headers = $this->getMock('PapayaResponseHeaders');
    $this->assertSame(
      $headers, $response->headers($headers)
    );
  }

  /**
  * @covers PapayaResponse::headers
  */
  public function testHeadersGetHeadersImplizitCreate() {
    $response = new PapayaResponse();
    $this->assertInstanceOf(
      'PapayaResponseHeaders', $response->headers()
    );
  }

  /**
  * @covers PapayaResponse::content
  */
  public function testContentSetContent() {
    $response = new PapayaResponse();
    $content = $this->getMock('PapayaResponseContent');
    $response->content($content);
    $this->assertAttributeSame(
      $content, '_content', $response
    );
  }

  /**
  * @covers PapayaResponse::content
  */
  public function testContentGetContentAfterSet() {
    $response = new PapayaResponse();
    $content = $this->getMock('PapayaResponseContent');
    $this->assertSame(
      $content, $response->content($content)
    );
  }

  /**
  * @covers PapayaResponse::content
  */
  public function testContentGetContentImplizitCreate() {
    $response = new PapayaResponse();
    $this->assertInstanceOf(
      'PapayaResponseContent', $response->content()
    );
  }

  /**
  * @covers PapayaResponse::setStatus
  */
  public function testSetStatus() {
    $response = new PapayaResponse();
    $response->setStatus(404);
    $this->assertAttributeEquals(
      404, '_status', $response
    );
  }

  /**
  * @covers PapayaResponse::setStatus
  */
  public function testSetStatusInvalidExpectingError() {
    $response = new PapayaResponse();
    $this->setExpectedException(
      'UnexpectedValueException', 'Unknown response status code: 999'
    );
    $response->setStatus(999);
  }

  /**
  * @covers PapayaResponse::getStatus
  */
  public function testGetStatusAfterSet() {
    $response = new PapayaResponse();
    $response->setStatus(404);
    $this->assertEquals(
      404, $response->getStatus()
    );
  }

  /**
  * @covers PapayaResponse::setContentType
  */
  public function testSetContentType() {
    $headers = $this->getMock('PapayaResponseHeaders', array('set'));
    $headers
      ->expects($this->once())
      ->method('set')
      ->with('Content-Type', 'text/plain; charset=UTF-8');
    $response = new PapayaResponse();
    $response->headers($headers);
    $response->setContentType('text/plain');
  }

  /**
  * @covers PapayaResponse::setContentType
  */
  public function testSetContentTypeAndEncoding() {
    $headers = $this->getMock('PapayaResponseHeaders', array('set'));
    $headers
      ->expects($this->once())
      ->method('set')
      ->with('Content-Type', 'text/plain; charset=ISO-8859-15');
    $response = new PapayaResponse();
    $response->headers($headers);
    $response->setContentType('text/plain', 'ISO-8859-15');
  }

  /**
  * @covers PapayaResponse::setCache
  * @dataProvider provideCacheHeaders
  */
  public function testSetCache($expected, $cacheMode, $cachePeriod, $cacheStartTime, $now) {
    $response = new PapayaResponse();
    $response->setCache($cacheMode, $cachePeriod, $cacheStartTime, $now);
    $this->assertAttributeEquals(
      $expected,
      '_headers',
      $response->headers()
    );
  }

  /**
  * @covers PapayaResponse::setCache
  */
  public function testSetCacheOneHourFromNow() {
    $response = new PapayaResponse();
    $response->setCache('private', 3600);
    $headers = $response->headers();
    $this->assertEquals(
      'private, max-age=3600, pre-check=3600, no-transform', $headers['Cache-Control']
    );
    $this->assertStringStartsWith(
      gmdate('D, d M Y H:i', time() + 3600), $headers['Expires']
    );
  }

  /**
  * @covers PapayaResponse::sendStatus
  */
  public function testSendStatus() {
    $helper = $this->getMock('PapayaResponseHelper');
    $helper
      ->expects($this->once())
      ->method('header')
      ->with(
        $this->equalTo('HTTP/1.1 204 No Content'),
        $this->equalTo(TRUE),
        $this->equalTo(204)
      );
    $response = new PapayaResponse();
    $response->helper($helper);
    $response->sendStatus(204);
  }

  /**
  * @covers PapayaResponse::sendStatus
  */
  public function testSendStatusAfterSetting() {
    $helper = $this->getMock('PapayaResponseHelper');
    $helper
      ->expects($this->once())
      ->method('header')
      ->with(
        $this->equalTo('HTTP/1.1 204 No Content'),
        $this->equalTo(TRUE),
        $this->equalTo(204)
      );
    $response = new PapayaResponse();
    $response->helper($helper);
    $response->setStatus(204);
    $response->sendStatus();
  }

  /**
  * @covers PapayaResponse::sendStatus
  */
  public function testSendStatusInvalid() {
    $helper = $this->getMock('PapayaResponseHelper');
    $helper
      ->expects($this->once())
      ->method('header')
      ->with(
        $this->equalTo('HTTP/1.1 200 OK'),
        $this->equalTo(TRUE),
        $this->equalTo(200)
      );
    $response = new PapayaResponse();
    $response->helper($helper);
    $response->sendStatus(999);
  }

  /**
  * @covers PapayaResponse::sendHeader
  */
  public function testSendHeader() {
    $application = $this->getMockApplicationObject();
    $helper = $this->getMock('PapayaResponseHelper');
    $helper
      ->expects($this->once())
      ->method('headersSent')
      ->will($this->returnValue(FALSE));
    $helper
      ->expects($this->once())
      ->method('header')
      ->with(
        $this->equalTo('X-Unit-Test: true')
      );
    $response = new PapayaResponse();
    $response->papaya($application);
    $response->helper($helper);
    $response->sendHeader('X-Unit-Test: true');
  }

  /**
  * @covers PapayaResponse::sendHeader
  */
  public function testSendHeaderBlockXHeader() {
    $application = $this->getMockApplicationObject(
      array(
        'Options' => $this->getMockConfigurationObject(
          array('PAPAYA_DISABLE_XHEADERS' => TRUE)
        )
      )
    );
    $helper = $this->getMock('PapayaResponseHelper');
    $helper
      ->expects($this->once())
      ->method('headersSent')
      ->will($this->returnValue(FALSE));
    $helper
      ->expects($this->never())
      ->method('header');
    $response = new PapayaResponse();
    $response->papaya($application);
    $response->helper($helper);
    $response->sendHeader('X-Unit-Test: true');
  }

  /**
  * @covers PapayaResponse::send
  */
  public function testSend() {
    $application = $this->getMockApplicationObject();
    $helper = $this->getMock('PapayaResponseHelper');
    $helper
      ->expects($this->exactly(3))
      ->method('header')
      ->with(
        $this->logicalOr(
          $this->equalTo('HTTP/1.1 200 OK'),
          $this->equalTo('Content-Type: text/plain; charset=UTF-8'),
          $this->equalTo('Content-Length: 6')
        )
      );
    $response = new PapayaResponse();
    $response->papaya($application);
    $response->helper($helper);
    $response->setContentType('text/plain');
    $response->content(new PapayaResponseContentString('SAMPLE'));
    ob_start();
    $response->send();
    $this->assertEquals(
      'SAMPLE',
      ob_get_clean()
    );
  }

  /**
  * @covers PapayaResponse::send
  */
  public function testSendWithCustomHeaders() {
    $headers = $this->getMock('PapayaResponseHeaders', array('getIterator'));
    $headers
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              'X-Simple' => '1',
              'X-Complex' => array('2_1', '2_2')
            )
          )
        )
      );
    $helper = $this->getMock('PapayaResponseHelper');
    $helper
      ->expects($this->exactly(4))
      ->method('header')
      ->with(
        $this->logicalOr(
          $this->equalTo('HTTP/1.1 200 OK'),
          $this->equalTo('X-Simple: 1'),
          $this->equalTo('X-Complex: 2_1'),
          $this->equalTo('X-Complex: 2_2')
        )
      );
    $response = new PapayaResponse();
    $response->papaya($this->getMockApplicationObject());
    $response->helper($helper);
    $response->headers($headers);
    $response->setContentType('text/plain');
    $response->content(new PapayaResponseContentString('SAMPLE'));
    ob_start();
    $response->send();
    $this->assertEquals(
      'SAMPLE',
      ob_get_clean()
    );
  }

  /*************************
  * Data Provider
  *************************/

  public static function provideCacheHeaders() {
    return array(
      'nocache' => array(
        array(
          'Cache-Control' =>
            'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform',
          'Pragma' => 'no-cache',
          'Expires' => 'Thu, 19 Nov 1981 08:52:00 GMT'
        ),
        'nocache',
        0,
        NULL,
        NULL
      ),
      'public, 1800 seconds' => array(
        array(
          'Cache-Control' => 'public, max-age=1800, pre-check=1800, no-transform',
          'Expires' => 'Thu, 15 Jun 2000 12:30:00 GMT',
          'Last-Modified' => 'Thu, 15 Jun 2000 12:00:00 GMT',
          'Pragma' => ''
        ),
        'public',
        1800,
        gmmktime(12, 0, 0, 6, 15, 2000),
        gmmktime(12, 0, 0, 6, 15, 2000)
      ),
      'private, 1800 seconds, 900 seconds gone' => array(
        array(
          'Cache-Control' => 'private, max-age=900, pre-check=900, no-transform',
          'Expires' => 'Thu, 15 Jun 2000 12:30:00 GMT',
          'Last-Modified' => 'Thu, 15 Jun 2000 12:00:00 GMT',
          'Pragma' => ''
        ),
        'private',
        1800,
        gmmktime(12, 0, 0, 6, 15, 2000),
        gmmktime(12, 15, 0, 6, 15, 2000)
      )
    );
  }
}
