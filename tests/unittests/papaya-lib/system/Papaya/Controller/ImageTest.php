<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
PapayaTestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_IMAGES',
  'PAPAYA_DB_TBL_MODULES',
  'PAPAYA_DB_TBL_MODULEGROUPS'
);

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Controller/Image.php');

class PapayaControllerImageTest extends PapayaTestCase {

  /**
  * @covers PapayaControllerImage::setImageGenerator
  */
  public function testSetImageGenerator() {
    $generator = $this->getMock('base_imagegenerator');
    $controller = new PapayaControllerImage();
    $controller->setImageGenerator($generator);
    $this->assertAttributeSame(
      $generator, '_imageGenerator', $controller
    );
  }

  /**
  * @covers PapayaControllerImage::getImageGenerator
  */
  public function testGetImageGenerator() {
    $generator = $this->getMock('base_imagegenerator');
    $controller = new PapayaControllerImage();
    $controller->setImageGenerator($generator);
    $this->assertSame(
      $generator,
      $controller->getImageGenerator()
    );
  }

  /**
  * @covers PapayaControllerImage::getImageGenerator
  */
  public function testGetImageGeneratorImplizitCreate() {
    $controller = new PapayaControllerImage();
    $this->assertInstanceOf(
      'base_imagegenerator',
      $controller->getImageGenerator()
    );
  }

  /**
  * @covers PapayaControllerImage::execute
  */
  public function testExecute() {
    $controller = new PapayaControllerImage();
    $controller->papaya(
      $this->getMockApplicationObject(
        array(
          'Request' =>  $this->getMockRequestObject(
            array(
              'preview' => TRUE,
              'image_identifier' => 'sample'
            )
          )
        )
      )
    );
    $generator = $this->getMock('base_imagegenerator');
    $generator
      ->expects($this->once())
      ->method('loadByIdent')
      ->will($this->returnValue(TRUE));
    $generator
      ->expects($this->once())
      ->method('generateImage')
      ->will($this->returnValue(TRUE));
    $dispatcher = $this->getMock('papaya_page', array('validateEditorAccess', 'logRequest'));
    $controller->setImageGenerator($generator);
    $this->assertTrue(
      $controller->execute($dispatcher)
    );
  }

  /**
  * @covers PapayaControllerImage::execute
  */
  public function testExecuteImageGenerateFailed() {
    $controller = new PapayaControllerImage();
    $controller->papaya(
      $this->getMockApplicationObject(
        array(
          'Request' =>  $this->getMockRequestObject(
            array(
              'preview' => TRUE,
              'image_identifier' => 'sample'
            )
          )
        )
      )
    );
    $generator = $this->getMock('base_imagegenerator');
    $generator
      ->expects($this->once())
      ->method('loadByIdent')
      ->will($this->returnValue(TRUE));
    $generator
      ->expects($this->once())
      ->method('generateImage')
      ->will($this->returnValue(FALSE));
    $dispatcher = $this->getMock('papaya_page', array('validateEditorAccess', 'logRequest'));
    $controller->setImageGenerator($generator);
    $this->assertInstanceOf(
      'PapayaControllerError',
      $controller->execute($dispatcher)
    );
  }

  /**
  * @covers PapayaControllerImage::execute
  */
  public function testExecuteInvalidImageIdentifier() {
    $controller = new PapayaControllerImage();
    $controller->papaya(
      $this->getMockApplicationObject(
        array(
          'Request' =>  $this->getMockRequestObject(
            array(
              'preview' => TRUE,
              'image_identifier' => ''
            )
          )
        )
      )
    );
    $generator = $this->getMock('base_imagegenerator');
    $dispatcher = $this->getMock('papaya_page', array('validateEditorAccess', 'logRequest'));
    $controller->setImageGenerator($generator);
    $this->assertInstanceOf(
      'PapayaControllerError',
      $controller->execute($dispatcher)
    );
  }

  /**
  * @covers PapayaControllerImage::execute
  */
  public function testExecuteInvalidPermission() {
    $controller = new PapayaControllerImage();
    $controller->papaya(
      $this->getMockApplicationObject(
        array(
          'Request' =>  $this->getMockRequestObject(
            array(
              'preview' => FALSE,
              'image_identifier' => 'sample'
            )
          )
        )
      )
    );
    $generator = $this->getMock('base_imagegenerator');
    $dispatcher = $this->getMock('papaya_page', array('validateEditorAccess', 'logRequest'));
    $dispatcher
      ->expects($this->once())
      ->method('validateEditorAccess')
      ->will($this->returnValue(FALSE));
    $controller->setImageGenerator($generator);
    $this->assertInstanceOf(
      'PapayaControllerError',
      $controller->execute($dispatcher)
    );
  }
}