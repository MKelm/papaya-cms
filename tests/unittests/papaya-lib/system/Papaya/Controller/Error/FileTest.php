<?php
require_once(substr(__FILE__, 0, -54).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Controller/Error/File.php');

class PapayaControllerErrorFileTest extends PapayaTestCase {

  function testSetTemplateFile() {
    $controller = new PapayaControllerErrorFile();
    $fileName = dirname(__FILE__).'/TestData/template.txt';
    $this->assertTrue(
      $controller->setTemplateFile($fileName)
    );
    $this->assertStringEqualsFile(
      $fileName,
      $this->readAttribute($controller, '_template')
    );
  }

  function testSetTemplateFileWithInvalidArgument() {
    $controller = new PapayaControllerErrorFile();
    $this->assertFalse(
      $controller->setTemplateFile('INVALID_FILENAME.txt')
    );
    $this->assertAttributeNotEquals(
      '', '_template', $controller
    );
  }
}