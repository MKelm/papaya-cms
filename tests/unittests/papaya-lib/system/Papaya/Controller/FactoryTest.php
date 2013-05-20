<?php
require_once(substr(__FILE__, 0, -52).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Controller/Factory.php');

class PapayaControllerFactoryTest extends PapayaTestCase {

  /**
  * @covers PapayaControllerFactory::createError
  */
  public function testCreateError() {
    $error = PapayaControllerFactory::createError(404, 'Test', 'TEST');
    $this->assertInstanceOf('PapayaControllerError', $error);
    $this->assertAttributeEquals(
      404, '_status', $error
    );
    $this->assertAttributeEquals(
      'Test', '_errorMessage', $error
    );
    $this->assertAttributeEquals(
      'TEST', '_errorIdentifier', $error
    );
  }

  /**
  * @covers PapayaControllerFactory::createError
  */
  public function testCreateErrorWithFile() {
    $error = PapayaControllerFactory::createError(
      404, 'Test', 'TEST', dirname(__FILE__).'/Error/TestData/template.txt'
    );
    $this->assertInstanceOf('PapayaControllerErrorFile', $error);
    $this->assertAttributeEquals(
      'SAMPLE', '_template', $error
    );
  }

}