<?php
require_once(substr(__FILE__, 0, -49).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Controller/Error.php');

class PapayaControllerErrorTest extends PapayaTestCase {

  /**
  * @covers PapayaControllerError::setStatus
  */
  public function testSetStatus() {
    $controller = new PapayaControllerError();
    $controller->setStatus(403);
    $this->assertAttributeEquals(
      403, '_status', $controller
    );
  }

  /**
  * @covers PapayaControllerError::setError
  */
  public function testSetError() {
    $controller = new PapayaControllerError();
    $controller->setError('ERROR_IDENTIFIER', 'ERROR_MESSAGE');
    $this->assertAttributeEquals(
      'ERROR_MESSAGE', '_errorMessage', $controller
    );
    $this->assertAttributeEquals(
      'ERROR_IDENTIFIER', '_errorIdentifier', $controller
    );
  }

  /**
  * @covers PapayaControllerError::execute
  * @covers PapayaControllerError::_getOutput
  */
  public function testControllerExecute() {
    $response = $this->getMock('PapayaResponse');
    $response
      ->expects($this->once())
      ->method('setStatus')
      ->with(
        $this->equalTo(500)
      );
    $response
      ->expects($this->once())
      ->method('setContentType')
      ->with(
        $this->equalTo('text/html')
      );
    $response
      ->expects($this->once())
      ->method('content')
      ->with(
        $this->isInstanceOf('PapayaResponseContentString')
      );
    $response
      ->expects($this->once())
      ->method('send');
    $controller = new PapayaControllerError();
    $controller->papaya(
      $this->getMockApplicationObject(
        array(
          'Response' => $response
        )
      )
    );
    $this->assertFalse(
      $controller->execute(new stdClass)
    );
  }
}