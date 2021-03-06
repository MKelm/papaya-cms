<?php
require_once(substr(__FILE__, 0, -56).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaTemplateEngineSimpleTest extends PapayaTestCase {

  /**
   * Integration test - block code coverage
   *
   * @covers stdClass
   */
  public function testTemplateEngineRun() {
    $engine = new PapayaTemplateEngineSimple();
    $engine->setTemplateString('Hello /*$foo*/ World!');
    $values = new PapayaXmlDocument();
    $values->appendElement('values')->appendElement('foo', array(), 'Universe');
    $engine->values($values->documentElement);
    $engine->prepare();
    $engine->run();
    $this->assertEquals('Hello Universe!', $engine->getResult());
  }

  /**
  * @covers PapayaTemplateEngineSimple::prepare
   */
  public function testPrepare() {
    $visitor = $this->getMock('PapayaTemplateSimpleVisitor');
    $visitor
      ->expects($this->once())
      ->method('clear');

    $engine = new PapayaTemplateEngineSimple();
    $engine->visitor($visitor);
    $engine->prepare();
  }

  /**
  * @covers PapayaTemplateEngineSimple::run
   */
  public function testRun() {
    $visitor = $this->getMock('PapayaTemplateSimpleVisitor');
    $ast = $this->getMock('PapayaTemplateSimpleAst');
    $ast
      ->expects($this->once())
      ->method('accept')
      ->with($visitor);

    $engine = new PapayaTemplateEngineSimple();
    $engine->visitor($visitor);
    $engine->ast($ast);
    $engine->run();
  }

  /**
  * @covers PapayaTemplateEngineSimple::getResult
   */
  public function testGetResult() {
    $visitor = $this->getMock('PapayaTemplateSimpleVisitor', array('__toString', 'clear'));
    $visitor
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('success'));

    $engine = new PapayaTemplateEngineSimple();
    $engine->visitor($visitor);
    $this->assertEquals('success', $engine->getResult());
  }

  /**
   * @covers PapayaTemplateEngineSimple::callbackGetValue
   */
  public function testCallbackGetValueWithName() {
    $values = new PapayaXmlDocument();
    $values
      ->appendElement('values')
      ->appendElement('page')
      ->appendElement('group')
      ->appendElement('value', array(), 'success');
    $engine = new PapayaTemplateEngineSimple();
    $engine->values($values->documentElement);
    $this->assertEquals(
      'success', $engine->callbackGetValue(new stdClass, 'page.group.value')
    );
  }

  /**
   * @covers PapayaTemplateEngineSimple::callbackGetValue
   */
  public function testCallbackGetValueWithXpath() {
    $values = new PapayaXmlDocument();
    $values
      ->appendElement('values')
      ->appendElement('page')
      ->appendElement('group')
      ->appendElement('value', array(), 'success');
    $engine = new PapayaTemplateEngineSimple();
    $engine->values($values->documentElement);
    $this->assertEquals(
      'success', $engine->callbackGetValue(new stdClass, 'xpath(page/group/value)')
    );
  }

  /**
  * @covers PapayaTemplateEngineSimple::setTemplateString
  */
  public function testSetTemplateString() {
    $engine = new PapayaTemplateEngineSimple();
    $engine->setTemplateString('div { color: /*$FG_COLOR*/ #FFF; }');
    $this->assertAttributeEquals(
      'div { color: /*$FG_COLOR*/ #FFF; }',
      '_template',
      $engine
    );
    $this->assertAttributeEquals(
      FALSE,
      '_templateFile',
      $engine
    );
  }

  /**
  * @covers PapayaTemplateEngineSimple::setTemplateFile
  */
  public function testSetTemplateFile() {
    $engine = new PapayaTemplateEngineSimple();
    $engine->setTemplateFile(dirname(__FILE__).'/TestData/valid.css');
    $this->assertAttributeNotEmpty(
      '_template',
      $engine
    );
    $this->assertAttributeEquals(
      dirname(__FILE__).'/TestData/valid.css',
      '_templateFile',
      $engine
    );
  }

  /**
  * @covers PapayaTemplateEngineSimple::setTemplateFile
  */
  public function testSetTemplateFileWithInvalidFileNameExpectingException() {
    $engine = new PapayaTemplateEngineSimple();
    $this->setExpectedException('InvalidArgumentException');
    $engine->setTemplateFile('NONEXISTING_FILENAME.CSS');
  }

  /**
  * @covers PapayaTemplateEngineSimple::ast
  */
  public function testAstGetAfterSet() {
    $ast = $this->getMock('PapayaTemplateSimpleAst');
    $engine = new PapayaTemplateEngineSimple();
    $engine->ast($ast);
    $this->assertSame($ast, $engine->ast());
  }

  /**
  * @covers PapayaTemplateEngineSimple::ast
  */
  public function testAstGetImplicitCreate() {
    $engine = new PapayaTemplateEngineSimple();
    $this->assertInstanceOf('PapayaTemplateSimpleAst', $engine->ast());
  }

  /**
  * @covers PapayaTemplateEngineSimple::visitor
  */
  public function testVisitorGetAfterSet() {
    $visitor = $this->getMock('PapayaTemplateSimpleVisitor');
    $engine = new PapayaTemplateEngineSimple();
    $engine->visitor($visitor);
    $this->assertSame($visitor, $engine->visitor());
  }

  /**
  * @covers PapayaTemplateEngineSimple::visitor
  */
  public function testVisitorGetImplicitCreate() {
    $engine = new PapayaTemplateEngineSimple();
    $this->assertInstanceOf('PapayaTemplateSimpleVisitor', $engine->visitor());
  }
}