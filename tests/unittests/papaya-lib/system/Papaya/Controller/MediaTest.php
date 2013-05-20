<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
PapayaTestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_MEDIADB_FILES',
  'PAPAYA_DB_TBL_MEDIADB_FILES_DERIVATIONS',
  'PAPAYA_DB_TBL_MEDIADB_FILES_TRANS',
  'PAPAYA_DB_TBL_MEDIADB_FILES_VERSIONS',
  'PAPAYA_DB_TBL_MEDIADB_FOLDERS',
  'PAPAYA_DB_TBL_MEDIADB_FOLDERS_TRANS',
  'PAPAYA_DB_TBL_MEDIADB_FOLDERS_PERMISSIONS',
  'PAPAYA_DB_TBL_MEDIADB_MIMEGROUPS',
  'PAPAYA_DB_TBL_MEDIADB_MIMEGROUPS_TRANS',
  'PAPAYA_DB_TBL_MEDIADB_MIMETYPES',
  'PAPAYA_DB_TBL_MEDIADB_MIMETYPES_EXTENSIONS',
  'PAPAYA_DB_TBL_TAG_LINKS',
  'PAPAYA_DB_TBL_SURFER',
  'PAPAYA_PATH_MEDIAFILES',
  'PAPAYA_PATH_THUMBFILES',
  'PAPAYA_MEDIADB_SUBDIRECTORIES'
);

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Controller/Media.php');

class PapayaControllerMediaTest extends PapayaTestCase {

  /**
  * @covers PapayaControllerMedia::execute
  */
  public function testExecuteNoMediaFound() {
    $controller = new PapayaControllerMedia();
    $controller->papaya($this->getMockApplicationObject());

    $dispatcher = $this->getMock('papaya_page', array('validateEditorAccess', 'logRequest'));
    $this->assertInstanceOf('PapayaControllerError', $controller->execute($dispatcher));
  }

  /**
  * @covers PapayaControllerMedia::execute
  */
  public function testExecuteNonExistentMediaFile() {
    $generator = $this->getMock('base_mediadb');
    $generator
      ->expects($this->once())
      ->method('getFile')
      ->will($this->returnValue(FALSE));

    $controller = new PapayaControllerMedia();
    $controller->setMediaDatabase($generator);
    $controller->papaya(
      $this->getMockApplicationObject(
        array(
          'Request' =>  $this->getMockRequestObject(
            array(
              'media_id' => 'sample'
            )
          )
        )
      )
    );

    $dispatcher = $this->getMock('papaya_page', array('validateEditorAccess', 'logRequest'));
    $this->assertInstanceOf('PapayaControllerError', $controller->execute($dispatcher));
  }

  /**
  * @dataProvider trueFalseDataProvider
  * @covers PapayaControllerMedia::execute
  */
  public function testExecute($enablePreview) {
    $generator = $this->getMock('base_mediadb');
    $generator
      ->expects($this->once())
      ->method('getFile')
      ->will($this->returnValue(TRUE));

    $controller = new PapayaControllerMediaProxy();
    $controller->setMediaDatabase($generator);
    $controller->papaya(
      $this->getMockApplicationObject(
        array(
          'Request' =>  $this->getMockRequestObject(
            array(
              'preview' => $enablePreview,
              'media_id' => 'sample'
            )
          )
        )
      )
    );

    $dispatcher = $this->getMock('papaya_page', array('validateEditorAccess', 'logRequest'));
    $this->assertTrue($controller->execute($dispatcher));
  }

  /**
  * @covers PapayaControllerMedia::setMediaDatabase
  */
  public function testSetMediaDatabase() {
    $generator = $this->getMock('base_mediadb');
    $controller = new PapayaControllerMedia();
    $controller->setMediaDatabase($generator);
    $this->assertAttributeSame(
      $generator, '_mediaDatabase', $controller
    );
  }

  /**
  * @covers PapayaControllerMedia::getMediaDatabase
  */
  public function testGetMediaDatabase() {
    $generator = $this->getMock('base_mediadb');
    $controller = new PapayaControllerMedia();
    $controller->setMediaDatabase($generator);
    $this->assertSame(
      $generator,
      $controller->getMediaDatabase()
    );
  }

  /**
  * @covers PapayaControllerMedia::getMediaDatabase
  */
  public function testGetMediaDatabaseImplizitCreate() {
    $controller = new PapayaControllerMedia();
    $this->assertInstanceOf(
      'base_mediadb',
      $controller->getMediaDatabase()
    );
  }

  /**
  * @covers PapayaControllerMedia::_outputPublicFile
  */
  public function testOutputPublicFileWithFolderPermissions() {
    $surfer = $this->getMock(
      'Surfer',
      array('hasOnePermOf')
    );
    $surfer
      ->expects($this->once())
      ->method('hasOnePermOf')
      ->will($this->returnValue(TRUE));

    $application = $this->getMockApplicationObject(array('Surfer' => $surfer));

    $generator = $this->getMock(
      'base_mediadb',
      array('getFolderPermissions')
    );
    $generator
      ->expects($this->once())
      ->method('getFolderPermissions')
      ->will(
        $this->returnValue(
          array(
            'surfer_view' => array(),
            'surfer_edit' => array(),
          )
        )
      );

    $controller = new PapayaControllerMediaOutputFilesTest;
    $controller->papaya($application);
    $controller->setMediaDatabase($generator);

    $this->assertTrue($controller->_outputPublicFile(array('folder_id' => 123)));
  }

  /**
  * @covers PapayaControllerMedia::_outputPublicFile
  */
  public function testOutputPublicFile() {
    $surfer = $this->getMock(
      'Surfer',
      array('hasOnePermOf')
    );
    $surfer
      ->expects($this->once())
      ->method('hasOnePermOf')
      ->will($this->returnValue(FALSE));

    $application = $this->getMockApplicationObject(array('Surfer' => $surfer));

    $generator = $this->getMock(
      'base_mediadb',
      array('getFolderPermissions')
    );
    $generator
      ->expects($this->once())
      ->method('getFolderPermissions')
      ->will(
        $this->returnValue(
          array(
            'surfer_view' => array(),
            'surfer_edit' => array(),
          )
        )
      );

    $controller = new PapayaControllerMediaOutputFilesTest;
    $controller->papaya($application);
    $controller->setMediaDatabase($generator);

    $this->assertNull($controller->_outputPublicFile(array('folder_id' => 123)));
  }

  /**
  * @covers PapayaControllerMedia::_outputPublicFile
  */
  public function testOutputPublicFileWithoutFolderPermissions() {
    $generator = $this->getMock(
      'base_mediadb',
      array('getFolderPermissions')
    );
    $generator
      ->expects($this->once())
      ->method('getFolderPermissions')
      ->will($this->returnValue(array()));

    $controller = new PapayaControllerMediaOutputFilesTest;
    $controller->setMediaDatabase($generator);

    $this->assertTrue($controller->_outputPublicFile(array('folder_id' => 123)));
  }

  /**
  * @dataProvider trueFalseDataProvider
  * @covers PapayaControllerMedia::_outputPreviewFile
  */
  public function testOutputPreviewFile($userValid) {
    $controller = new PapayaControllerMediaOutputFilesTest;
    $adminuser = $this->getMock('AdministrationUser', array('isValid'));
    $adminuser
      ->expects($this->once())
      ->method('isValid')
      ->will($this->returnValue($userValid));

      $controller->papaya(
      $this->getMockApplicationObject(array('AdministrationUser' => $adminuser)));
    $this->assertNull($controller->_outputPreviewFile(array()));
  }

  /**
  * @covers PapayaControllerMedia::_outputFile
  */
  public function testOutputFile() {
    $this->markTestSkipped('Request on a static function not mockable.');
  }

  /***************************************************************************/
  /** Dataprovider                                                          **/
  /***************************************************************************/

  public static function trueFalseDataProvider() {
    return array(
      array(TRUE),
      array(FALSE),
    );
  }

}

class PapayaControllerMediaProxy extends PapayaControllerMedia {
  public function _outputPreviewFile($file) {
    return TRUE;
  }
  public function _outputPublicFile($file) {
    return TRUE;
  }
}

class PapayaControllerMediaOutputFilesTest extends PapayaControllerMedia {
  public function _outputPublicFile($file) {
    return parent::_outputPublicFile($file);
  }
  public function _outputPreviewFile($file) {
    return parent::_outputPreviewFile($file);
  }
  public function _outputFile($file) {
    return TRUE;
  }
}

class PapayaControllerOutputFileTest extends PapayaControllerMedia {
  public function _outputFile($file) {
    return parent::_outputFile($file);
  }
}