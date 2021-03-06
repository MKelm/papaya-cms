<?php
require_once(substr(__FILE__, 0, -58).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Context/Variable.php');

class PapayaMessageContextVariableTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageContextVariable::__construct
  */
  public function testConstructor() {
    $context = new PapayaMessageContextVariable(42);
    $this->assertAttributeSame(
      42,
      '_variable',
      $context
    );
  }

  /**
  * @covers PapayaMessageContextVariable::__construct
  * @covers PapayaMessageContextVariable::setDepth
  */
  public function testConstructorWithDepth() {
    $context = new PapayaMessageContextVariable(42, 21);
    $this->assertAttributeSame(
      21,
      '_depth',
      $context
    );
  }

  /**
  * @covers PapayaMessageContextVariable::getDepth
  */
  public function testGetDepth() {
    $context = new PapayaMessageContextVariable(42, 21);
    $this->assertSame(
      21,
      $context->getDepth()
    );
  }

  /**
  * @covers PapayaMessageContextVariable::setDepth
  */
  public function testSetDepthWithInvalidDepthExpectingException() {
    $context = new PapayaMessageContextVariable(NULL);
    $this->setExpectedException('InvalidArgumentException');
    $context->setDepth(0);
  }

  /**
  * @covers PapayaMessageContextVariable::__construct
  * @covers PapayaMessageContextVariable::setStringLength
  */
  public function testConstructorWithStringLength() {
    $context = new PapayaMessageContextVariable(42, 21, 23);
    $this->assertAttributeSame(
      23,
      '_stringLength',
      $context
    );
  }

  /**
  * @covers PapayaMessageContextVariable::getStringLength
  */
  public function testGetStringLength() {
    $context = new PapayaMessageContextVariable(42, 21, 23);
    $this->assertSame(
      23,
      $context->getStringLength()
    );
  }

  /**
  * @covers PapayaMessageContextVariable::setStringLength
  */
  public function testSetStringLengthWithInvalidLengthExpectingException() {
    $context = new PapayaMessageContextVariable(NULL);
    $this->setExpectedException('InvalidArgumentException');
    $context->setStringLength(-1);
  }

  /**
  * @covers PapayaMessageContextVariable::acceptVisitor
  */
  public function testAcceptVisitor() {
    $visitor = $this->getMock(
      'PapayaMessageContextVariableVisitorString', array('visitVariable'), array(21, 42)
    );
    $visitor
      ->expects($this->once())
      ->method('visitVariable')
      ->with($this->equalTo('variable'));
    $context = new PapayaMessageContextVariable('variable');
    $context->acceptVisitor($visitor);
  }

  /**
  * @covers PapayaMessageContextVariable::asString
  */
  public function testAsString() {
    $context = new PapayaMessageContextVariable(FALSE);
    $this->assertEquals(
      'bool(false)',
      $context->asString()
    );
  }

  /**
  * @covers PapayaMessageContextVariable::asXhtml
  */
  public function testAsXhtml() {
    $context = new PapayaMessageContextVariable(FALSE);
    $this->assertEquals(
      '<ul class="variableDump">'.
      '<li><strong>bool</strong>(<em class="boolean">false</em>)</li>'.
      '</ul>',
      $context->asXhtml()
    );
  }
}