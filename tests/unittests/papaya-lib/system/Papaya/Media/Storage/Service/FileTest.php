<?php

require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaMediaStorageServiceFileTest extends PapayaTestCase {

  public function setUp() {
    if ($directory = $this->createTemporaryDirectory()) {
      $this->_storageDirectory = $directory.DIRECTORY_SEPARATOR.'storage';
      $this->_publicDirectory = $directory.DIRECTORY_SEPARATOR.'public';
      $oldMask = umask(0);
      mkdir($this->_storageDirectory, 0777, TRUE);
      mkdir($this->_publicDirectory, 0777, TRUE);
      umask($oldMask);
    }
  }

  public function tearDown() {
    $this->removeTemporaryDirectory();
  }

  private function createSampleFilesFixture($publicLinks = FALSE) {
    $oldMask = umask(0);
    $resourcePath = DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'0';
    mkdir($this->_storageDirectory.$resourcePath, 0777, TRUE);
    if ($publicLinks) {
      mkdir($this->_publicDirectory.$resourcePath, 0777, TRUE);
    }
    umask($oldMask);
    file_put_contents(
      $this->_storageDirectory.$resourcePath.
        DIRECTORY_SEPARATOR.'012345678901234567890123456789012_v1',
      'SAMPLE_VERSION_1'
    );
    file_put_contents(
      $this->_storageDirectory.$resourcePath.
        DIRECTORY_SEPARATOR.'012345678901234567890123456789012_v2',
      'SAMPLE_VERSION_2'
    );
    if ($publicLinks) {
      link(
        $this->_storageDirectory.$resourcePath.
          DIRECTORY_SEPARATOR.'012345678901234567890123456789012_v1',
        $this->_publicDirectory.$resourcePath.
          DIRECTORY_SEPARATOR.'012345678901234567890123456789012_v1.gif'
      );
    }
  }

  private function getMockConfigurationObjectFixture() {
    $configuration = $this->getMockConfigurationObject(
      array(
        'PAPAYA_MEDIA_STORAGE_DIRECTORY' => $this->_storageDirectory,
        'PAPAYA_MEDIA_PUBLIC_DIRECTORY' => $this->_publicDirectory,
        'PAPAYA_MEDIA_PUBLIC_URL' => 'http://www.sample.tld/papaya-files/'
      )
    );
    return $configuration;
  }

  public function testSetConfiguration() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertSame(
      $this->_storageDirectory, $this->readAttribute($service, '_storageDirectory')
    );
    $this->assertSame(
      $this->_publicDirectory, $this->readAttribute($service, '_publicDirectory')
    );
    $this->assertSame(1, $this->readAttribute($service, '_storageDirectoryDepth'));
  }

  public function testSetConfigurationWithNotExistingPublicDirectory() {
    $configuration = $this->getMockConfigurationObjectFixture();
    rmdir($this->_publicDirectory);
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertSame(
      '', $this->readAttribute($service, '_publicDirectory')
    );
  }

  public function testVerifyConfiguration() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile_TestProxy($configuration);
    $this->assertTrue($service->_verifyConfiguration());
  }

  public function testVerifyConfigurationWithDirectorySeparator() {
    $configuration = $this->getMockConfigurationObject(
      array(
        'PAPAYA_MEDIA_STORAGE_DIRECTORY' => $this->_storageDirectory.'/',
        'PAPAYA_MEDIA_PUBLIC_DIRECTORY' => $this->_publicDirectory.'/',
        'PAPAYA_MEDIA_PUBLIC_URL' => 'http://www.sample.tld/papaya-files/'
      )
    );
    $service = new PapayaMediaStorageServiceFile_TestProxy($configuration);
    $this->assertTrue($service->_verifyConfiguration());
  }

  public function testVerifyConfigurationWhileInvalid() {
    $service = new PapayaMediaStorageServiceFile_TestProxy();
    $this->assertFalse($service->_verifyConfiguration());
  }

  public function testBrowse() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $expected = array(
      '012345678901234567890123456789012_v1',
      '012345678901234567890123456789012_v2'
    );
    $this->assertSame($expected, $service->browse('media'));
  }

  public function testBrowseWithStartString() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $expected = array(
      '012345678901234567890123456789012_v2'
    );
    $this->assertSame($expected, $service->browse('media', '012345678901234567890123456789012_v2'));
  }

  public function testBrowseWithInvalidStorageGroup() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertSame(array(), $service->browse('INVALID_GROUP'));
  }

  public function testStore() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertTrue(
      $service->store(
        'media',
        '012345678901234567890123456789012_v1',
        'SAMPLE_DATA'
      )
    );
    $this->assertFileExists(
      $this->_storageDirectory.'/media/0/012345678901234567890123456789012_v1'
    );
  }

  public function testStoreWithResourceId() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertTrue(
      $service->store(
        'media',
        '012345678901234567890123456789012_v1',
        fopen('data://text/plain,SAMPLE_DATA', 'r')
      )
    );
    $this->assertFileExists(
      $this->_storageDirectory.'/media/0/012345678901234567890123456789012_v1'
    );
  }

  public function testStoreWithInvalidConfiguration() {
    $service = new PapayaMediaStorageServiceFile();
    $this->assertFalse(
      $service->store(
        'media',
        '012345678901234567890123456789012_v1',
        'SAMPLE_DATA'
      )
    );
  }

  public function testStoreWithInvalidStorageParameters() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertFalse(
      $service->store(
        '',
        '',
        'SAMPLE_DATA'
      )
    );
  }

  public function testStoreLocalFile() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertTrue(
      $service->storeLocalFile(
        'media',
        '012345678901234567890123456789012_v1',
        __FILE__
      )
    );
    $this->assertFileExists(
      $this->_storageDirectory.'/media/0/012345678901234567890123456789012_v1'
    );
  }

  public function testStoreLocalFileWithInvalidStorageParameters() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertFalse(
      $service->storeLocalFile(
        '',
        '',
        __FILE__
      )
    );
    $this->assertFileNotExists(
      $this->_storageDirectory.'/media/0/012345678901234567890123456789012_v1'
    );
  }

  public function testStoreLocalFileWithInvalidConfiguration() {
    $service = new PapayaMediaStorageServiceFile();
    $this->assertFalse(
      $service->storeLocalFile(
        'media',
        '012345678901234567890123456789012_v1',
        __FILE__
      )
    );
    $this->assertFileNotExists(
      $this->_storageDirectory.'/media/0/012345678901234567890123456789012_v1'
    );
  }

  public function testRemove() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertTrue(
      $service->remove(
        'media',
        '012345678901234567890123456789012_v1'
      )
    );
    $this->assertFileNotExists(
      $this->_storageDirectory.'/media/0/012345678901234567890123456789012_v1'
    );
  }

  public function testRemoveWithInvalidStorageParameters() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertFalse(
      $service->remove('INVALID_GROUP', 'INVALID_STORAGE_ID')
    );
  }

  public function testExists() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertTrue($service->exists('media', '012345678901234567890123456789012_v1'));
  }

  public function testExistsWithInvalidStorageId() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertFalse($service->exists('media', 'INVALID_STORAGE_ID'));
  }

  public function testExistsWithInvalidStorageGroup() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertFalse($service->exists('INVALID_GROUP', '012345678901234567890123456789012_v1'));
  }

  public function testIsPublic() {
    $this->createSampleFilesFixture(TRUE);
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertTrue(
      $service->isPublic(
        'media',
        '012345678901234567890123456789012_v1',
        'image/gif'
      )
    );
  }

  public function testIsPublicWithPrivateFile() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertFalse(
      $service->isPublic(
        'media',
        '012345678901234567890123456789012_v2',
        'image/gif'
      )
    );
  }

  public function testSetPublicToTrueWithPrivateFile() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertTrue(
      $service->setPublic(
        'media',
        '012345678901234567890123456789012_v1',
        TRUE,
        'image/gif'
      )
    );
    $this->assertFileExists(
      $this->_publicDirectory.
        DIRECTORY_SEPARATOR.'media'.
        DIRECTORY_SEPARATOR.'0'.
        DIRECTORY_SEPARATOR.'012345678901234567890123456789012_v1.gif'
    );
  }

  public function testSetPublicToTrueWithPublicFile() {
    $this->createSampleFilesFixture(TRUE);
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertTrue(
      $service->setPublic(
        'media',
        '012345678901234567890123456789012_v1',
        TRUE,
        'image/gif'
      )
    );
    $this->assertFileExists(
      $this->_publicDirectory.
        DIRECTORY_SEPARATOR.'media'.
        DIRECTORY_SEPARATOR.'0'.
        DIRECTORY_SEPARATOR.'012345678901234567890123456789012_v1.gif'
    );
  }

  public function testSetPublicToTrueWithUnacceptableMimetype() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertFalse(
      $service->setPublic(
        'media',
        '012345678901234567890123456789012_v1',
        TRUE,
        'application/x-httpd-php'
      )
    );
  }

  public function testSetPublicToTrueWithInvalidFile() {
    $this->createSampleFilesFixture(TRUE);
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertFalse(
      $service->setPublic(
        'media',
        '012345678901234567890123456789012_vINVALID',
        TRUE,
        'image/gif'
      )
    );
  }

  public function testSetPublicToTrueWithoutPublicConfiguration() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObject(
      array(
        'PAPAYA_MEDIA_STORAGE_DIRECTORY' => $this->_storageDirectory,
        'PAPAYA_MEDIA_PUBLIC_DIRECTORY' => '',
        'PAPAYA_MEDIA_PUBLIC_URL' => ''
      )
    );
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertFalse(
      $service->setPublic(
        'media',
        '012345678901234567890123456789012_v1',
        TRUE,
        'image/gif'
      )
    );
  }

  public function testSetPublicToFalseWithPrivateFile() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertTrue(
      $service->setPublic(
        'media',
        '012345678901234567890123456789012_v1',
        FALSE,
        'image/gif'
      )
    );
  }

  public function testSetPublicToFalseWithPublicFile() {
    $this->createSampleFilesFixture(TRUE);
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertTrue(
      $service->setPublic(
        'media',
        '012345678901234567890123456789012_v1',
        FALSE,
        'image/gif'
      )
    );
  }

  public function testGet() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertSame(
      'SAMPLE_VERSION_1',
      $service->get('media', '012345678901234567890123456789012_v1')
    );
  }

  public function testGetWithInvalidStorageParameters() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertSame(
      NULL,
      $service->get('INVALID_GROUP', 'INVALID_STORAGE_ID')
    );
  }

  public function testGetUrl() {
    $this->createSampleFilesFixture(TRUE);
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertSame(
      'http://www.sample.tld/papaya-files/media/0/012345678901234567890123456789012_v1.gif',
      $service->getUrl(
        'media',
        '012345678901234567890123456789012_v1',
        'image/gif'
      )
    );
  }

  public function testGetUrlWithExtension() {
    $this->createSampleFilesFixture(TRUE);
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertSame(
      'http://www.sample.tld/papaya-files/media/0/012345678901234567890123456789012_v1.gif',
      $service->getUrl(
        'media',
        '012345678901234567890123456789012_v1.gif',
        'image/gif'
      )
    );
  }

  public function testGetUrlWithInvalidStorageParameters() {
    $this->createSampleFilesFixture(TRUE);
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertNull(
      $service->getUrl('INVALID_GROUP', 'INVALID_STORAGE_ID', 'image/gif')
    );
  }

  public function testGetUrlWithoutPublicConfiguration() {
    $this->createSampleFilesFixture(TRUE);
    $configuration = $this->getMockConfigurationObject(
      array(
        'PAPAYA_MEDIA_STORAGE_DIRECTORY' => $this->_storageDirectory,
        'PAPAYA_MEDIA_PUBLIC_DIRECTORY' => '',
        'PAPAYA_MEDIA_PUBLIC_URL' => ''
      )
    );
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertNull(
      $service->getUrl(
        'media',
        '012345678901234567890123456789012_v1',
        'image/gif'
      )
    );
  }

  public function testGetLocalFile() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertSame(
      array(
        'filename' =>
          $this->_storageDirectory.
            DIRECTORY_SEPARATOR.'media'.
            DIRECTORY_SEPARATOR.'0'.
            DIRECTORY_SEPARATOR.'012345678901234567890123456789012_v1',
        'is_temporary' => FALSE
      ),
      $service->getLocalFile('media', '012345678901234567890123456789012_v1')
    );
  }

  public function testGetLocalFileWithInvalidStorageParameters() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertNull(
      $service->getLocalFile('INVALID_GROUP', 'INVALID_STORAGE_ID')
    );
  }

  public function testOutput() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    ob_start();
    $this->assertTrue(
      $service->output('media', '012345678901234567890123456789012_v1')
    );
    $this->assertSame('SAMPLE_VERSION_1', ob_get_clean());
  }

  public function testOutputWithInvalidStorageParameters() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    $this->assertFalse(
      $service->output('media', '012345678901234567890123456789012_v99')
    );
  }

  public function testOutputWithSmallBufferSize() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    ob_start();
    $this->assertTrue(
      $service->output('media', '012345678901234567890123456789012_v1', 0, 0, 2)
    );
    $this->assertSame('SAMPLE_VERSION_1', ob_get_clean());
  }

  public function testOutputWithRangeOffset() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    ob_start();
    $this->assertTrue(
      $service->output('media', '012345678901234567890123456789012_v1', 7)
    );
    $this->assertSame('VERSION_1', ob_get_clean());
  }

  public function testOutputWithRangeParameters() {
    $this->createSampleFilesFixture();
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile($configuration);
    ob_start();
    $this->assertTrue(
      $service->output('media', '012345678901234567890123456789012_v1', 7, 13)
    );
    $this->assertSame('VERSION', ob_get_clean());
  }

  public function testOutputLocalFileWithNonExistingFile() {
    $configuration = $this->getMockConfigurationObjectFixture();
    $service = new PapayaMediaStorageServiceFile_TestProxy();
    $this->assertFalse(
      @$service->_outputLocalFile('INVALID_FILENAME', 0, 0, 0)
    );
  }
}

class PapayaMediaStorageServiceFile_TestProxy extends PapayaMediaStorageServiceFile {

  public function _verifyConfiguration() {
    return parent::_verifyConfiguration();
  }

  public function _outputLocalFile($fileName, $rangeFrom, $length, $bufferSize) {
    return parent::_outputLocalFile($fileName, $rangeFrom, $length, $bufferSize);
  }
}
