<?php
require_once(substr(__FILE__, 0, -49).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Paging/Steps.php');

class PapayaUiPagingStepsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiPagingSteps::__construct
  */
  public function testConstructor() {
    $steps = new PapayaUiPagingSteps('steps', 20, array(10, 20, 30));
    $this->assertEquals('steps', $steps->parameterName);
    $this->assertEquals(20, $steps->currentStepSize);
    $this->assertEquals(array(10, 20, 30), $steps->stepSizes);
  }

  /**
  * @covers PapayaUiPagingSteps::appendTo
  */
  public function testAppendTo() {
    $steps = new PapayaUiPagingSteps('steps', 20, array(10, 20, 30));
    $steps->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      '<paging-steps>'.
        '<step-size href="http://www.test.tld/test.html?steps=10">10</step-size>'.
        '<step-size href="http://www.test.tld/test.html?steps=20" selected="selected">20</step-size>'.
        '<step-size href="http://www.test.tld/test.html?steps=30">30</step-size>'.
      '</paging-steps>',
      $steps->getXml()
    );
  }

  /**
  * @covers PapayaUiPagingSteps::appendTo
  */
  public function testAppendToWithTraversable() {
    $steps = new PapayaUiPagingSteps('steps', 20, new ArrayIterator(array(10)));
    $steps->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      '<paging-steps>'.
        '<step-size href="http://www.test.tld/test.html?steps=10">10</step-size>'.
      '</paging-steps>',
      $steps->getXml()
    );
  }

  /**
  * @covers PapayaUiPagingSteps::appendTo
  */
  public function testAppendToWithAdditionalParameters() {
    $steps = new PapayaUiPagingSteps('foo/steps', 20, array(10, 20, 30));
    $steps->papaya($this->getMockApplicationObject());
    $steps->reference()->setParameters(array('foo' => array('role' => 42)));
    $this->assertEquals(
      '<paging-steps>'.
        '<step-size href="http://www.test.tld/test.html?foo[role]=42&amp;foo[steps]=10">'.
          '10</step-size>'.
        '<step-size href="http://www.test.tld/test.html?foo[role]=42&amp;foo[steps]=20"'.
        ' selected="selected">20</step-size>'.
        '<step-size href="http://www.test.tld/test.html?foo[role]=42&amp;foo[steps]=30">'.
          '30</step-size>'.
      '</paging-steps>',
      $steps->getXml()
    );
  }

  /**
  * @covers PapayaUiPagingSteps::setXmlNames
  */
  public function testAppendToWithDifferentXml() {
    $steps = new PapayaUiPagingSteps('foo/steps', 20, array(10, 20, 30));
    $steps->setXmlNames(
      array(
        'list' => 'sizes',
        'item' => 'size'
      )
    );
    $steps->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      '<sizes>'.
        '<size href="http://www.test.tld/test.html?foo[steps]=10">10</size>'.
        '<size href="http://www.test.tld/test.html?foo[steps]=20" selected="selected">20</size>'.
        '<size href="http://www.test.tld/test.html?foo[steps]=30">30</size>'.
      '</sizes>',
      $steps->getXml()
    );
  }

  /**
  * @covers PapayaUiPagingSteps::setXmlNames
  */
  public function testSetXmlWithInvalidElement() {
    $steps = new PapayaUiPagingSteps('foo/steps', 20, array(10, 20, 30));
    $this->setExpectedException(
      'UnexpectedValueException',
      'Invalid/unknown xml name element "invalid" with value "PagingLinks".'
    );
    $steps->setXmlNames(
      array(
        'invalid' => 'PagingLinks'
      )
    );
  }

  /**
  * @covers PapayaUiPagingSteps::setXmlNames
  */
  public function testSetXmlWithInvalidElementName() {
    $steps = new PapayaUiPagingSteps('foo/steps', 20, array(10, 20, 30));
    $this->setExpectedException(
      'UnexpectedValueException',
      'Invalid/unknown xml name element "list" with value "23Invalid".'
    );
    $steps->setXmlNames(
      array(
        'list' => '23Invalid'
      )
    );
  }

  /**
  * @covers PapayaUiPagingSteps::getStepSizes
  * @covers PapayaUiPagingSteps::setStepSizes
  */
  public function testGetStepsAfterSet() {
    $steps = new PapayaUiPagingSteps('foo/steps', 20, array());
    $steps->stepSizes = array(100, 200);
    $this->assertEquals(
      array(100, 200), $steps->stepSizes
    );
  }

  /**
  * @covers PapayaUiPagingSteps::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->getMock('PapayaUiReference');
    $steps = new PapayaUiPagingSteps('steps', 20, array(10, 20, 30));
    $steps->reference($reference);
    $this->assertSame(
      $reference, $steps->reference()
    );
  }

  /**
  * @covers PapayaUiPagingSteps::reference
  */
  public function testReferenceGetImplicitCreate() {
    $steps = new PapayaUiPagingSteps('steps', 20, array(10, 20, 30));
    $steps->papaya(
      $application = $this->getMockApplicationObject()
    );
    $this->assertInstanceOf(
      'PapayaUiReference', $steps->reference()
    );
    $this->assertSame(
      $application, $steps->reference()->papaya()
    );
  }
}