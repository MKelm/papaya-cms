<?php
require_once(substr(__FILE__, 0, -44).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Xml/Errors.php');

class PapayaXmlErrorsTest extends PapayaTestCase {

  public function setUp() {
    if (!(extension_loaded('dom'))) {
      $this->markTestSkipped('No dom xml extension found.');
    }
    libxml_use_internal_errors(TRUE);
    libxml_clear_errors();
  }

  public function tearDown() {
    libxml_use_internal_errors(FALSE);
  }

  /**
  * @covers PapayaXmlErrors::activate
  */
  public function testActivate() {
    libxml_use_internal_errors(FALSE);
    $errors = new PapayaXmlErrors();
    $errors->activate();
    $this->assertTrue(
      libxml_use_internal_errors()
    );
  }

  /**
  * @covers PapayaXmlErrors::deactivate
  */
  public function testDeactivate() {
    libxml_use_internal_errors(FALSE);
    $errors = new PapayaXmlErrors();
    $errors->activate();
    $errors->deactivate();
    $this->assertFalse(
      libxml_use_internal_errors()
    );
  }

  /**
  * @covers PapayaXmlErrors::omit
  */
  public function testOmit() {
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageLogable'));
    $errors = new PapayaXmlErrors();
    $errors->papaya(
      $this->getMockApplicationObject(
        array(
          'Messages' => $messages
        )
      )
    );
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHtml('<foo/>');
    $errors->omit();
  }

  /**
  * @covers PapayaXmlErrors::omit
  */
  public function testOmitIgnoringNonFatal() {
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->never())
      ->method('dispatch');
    $errors = new PapayaXmlErrors();
    $errors->papaya(
      $this->getMockApplicationObject(
        array(
          'Messages' => $messages
        )
      )
    );
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHtml('<foo/>');
    $errors->omit(TRUE);
  }

  /**
  * @covers PapayaXmlErrors::omit
  */
  public function testOmitWithFatalError() {
    $errors = new PapayaXmlErrors();
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadXml('<foo>');
    $this->setExpectedException('PapayaXmlException');
    $errors->omit();
  }

  /**
  * @covers PapayaXmlErrors::getMessageFromError
  */
  public function testGetMessageFromError() {
    $error = new libXMLError();
    $error->level = LIBXML_ERR_WARNING;
    $error->code = 42;
    $error->message = 'Test';
    $error->file = '';
    $error->line = 23;
    $error->column = 21;
    $errors = new PapayaXmlErrors();
    $message = $errors->getMessageFromError($error);
    $this->assertEquals(
      PapayaMessageLogable::GROUP_SYSTEM, $message->getGroup()
    );
    $this->assertEquals(
      PapayaMessage::TYPE_WARNING, $message->getType()
    );
    $this->assertEquals(
      '42: Test in line 23 at char 21', $message->getMessage()
    );
  }

  /**
  * @covers PapayaXmlErrors::getMessageFromError
  */
  public function testGetMessageFromErrorWithFile() {
    $error = new libXMLError();
    $error->level = LIBXML_ERR_WARNING;
    $error->code = 42;
    $error->message = 'Test';
    $error->file = __FILE__;
    $error->line = 23;
    $error->column = 21;
    $errors = new PapayaXmlErrors();
    $context = $errors->getMessageFromError($error)->context();
    $this->assertInstanceOf(
      'PapayaMessageContextFile', $context->current()
    );
  }
}