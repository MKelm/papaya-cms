<?php
require_once(substr(__FILE__, 0, -52).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaObjectInteractiveTest extends PapayaTestCase {

  /**
   * @covers PapayaObjectInteractive::parameterMethod
   */
  public function testParameterMethod() {
    $parts = new PapayaObjectInteractive_TestProxy($this->getPageFixture());
    $this->assertEquals(
      PapayaRequestParametersInterface::METHOD_MIXED_POST,
      $parts->parameterMethod()
    );
  }

  /**
   * @covers PapayaObjectInteractive::parameterMethod
   */
  public function testParameterMethodChange() {
    $parts = new PapayaObjectInteractive_TestProxy($this->getPageFixture());
    $this->assertEquals(
      PapayaRequestParametersInterface::METHOD_MIXED_GET,
      $parts->parameterMethod(PapayaRequestParametersInterface::METHOD_MIXED_GET)
    );
  }

  /**
   * @covers PapayaObjectInteractive_TestProxy::parameterGroup
   */
  public function testParameterGroupWithChange() {
    $parts = new PapayaObjectInteractive_TestProxy($this->getPageFixture());
    $this->assertEquals(
      'sample', $parts->parameterGroup('sample')
    );
  }

  /**
   * @covers PapayaObjectInteractive::parameterGroup
   */
  public function testParameterGroupWithoutChange() {
    $parts = new PapayaObjectInteractive_TestProxy($this->getPageFixture());
    $this->assertEquals(
      '', $parts->parameterGroup()
    );
  }

  /**
   * @covers PapayaObjectInteractive::parameters
   */
  public function testParametersGetAfterSet() {
    $parts = new PapayaObjectInteractive_TestProxy($this->getPageFixture());
    $parts->parameters($parameters = $this->getMock('PapayaRequestParameters'));
    $this->assertEquals(
      $parameters, $parts->parameters()
    );
  }

  /**
   * @covers PapayaObjectInteractive::parameters
   */
  public function testParametersGetAllFromApplicationRequest() {
    $request = $this->getMock('PapayaRequest');
    $request
      ->expects($this->once())
      ->method('getParameters')
      ->with(PapayaRequest::SOURCE_QUERY | PapayaRequest::SOURCE_BODY)
      ->will($this->returnValue($this->getMock('PapayaRequestParameters')));
    $parts = new PapayaObjectInteractive_TestProxy($this->getPageFixture());
    $parts->papaya(
      $this->getMockApplicationObject(
        array('Request' => $request)
      )
    );
    $this->assertInstanceOf('PapayaRequestParameters', $parts->parameters());
  }

  /**
   * @covers PapayaObjectInteractive::parameters
   */
  public function testParametersGetGroupFromApplicationRequest() {
    $request = $this->getMock('PapayaRequest');
    $request
      ->expects($this->once())
      ->method('getParameterGroup')
      ->with('group', PapayaRequest::SOURCE_QUERY | PapayaRequest::SOURCE_BODY)
      ->will($this->returnValue($this->getMock('PapayaRequestParameters')));
    $parts = new PapayaObjectInteractive_TestProxy($this->getPageFixture());
    $parts->papaya(
      $this->getMockApplicationObject(
        array('Request' => $request)
      )
    );
    $parts->parameterGroup('group');
    $this->assertInstanceOf('PapayaRequestParameters', $parts->parameters());
  }
}

class PapayaObjectInteractive_TestProxy extends PapayaObjectInteractive {

}