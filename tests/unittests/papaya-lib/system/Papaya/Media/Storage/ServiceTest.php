<?php

require_once(substr(__FILE__, 0, -55).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Media/Storage.php');

class PapayaMediaStorageServiceTest extends PapayaTestCase {

  /**
  * @covers PapayaMediaStorageService::__construct
  */
  public function testConstructorWithConfiguration() {
    $configuration = $this->getMock('PapayaOptions');
    $service = new PapayaMediaStorageService_TestProxy($configuration);
    $this->assertSame($configuration, $service->configurationBuffer);
  }

  /**
  * @covers PapayaMediaStorageService::__construct
  */
  public function testConstructorWithoutConfiguration() {
    $configuration = $this->getMock('PapayaOptions');
    $service = new PapayaMediaStorageService_TestProxy();
    $this->assertNull($service->configurationBuffer);
  }
}

class PapayaMediaStorageService_TestProxy extends PapayaMediaStorageService {

  public $configurationBuffer = NULL;

  public function setConfiguration($configuration) {
    $this->configurationBuffer = $configuration;
  }

  /*
  * Implement abstract methods
  */

  public function verifyConfiguration() {
  }

  public function browse($storageGroup, $startsWith = '') {
  }

  public function store($storageGroup, $storageId, $content,
                        $mimeType = 'application/octet-stream',
                        $isPublic = FALSE) {
  }

  public function storeLocalFile($storageGroup, $storageId, $filename,
                                 $mimeType = 'application/octet-stream',
                                 $isPublic = FALSE) {
  }

  public function remove($storageGroup, $storageId) {
  }

  public function exists($storageGroup, $storageId) {
  }

  public function isPublic($storageGroup, $storageId, $mimeType) {
  }

  public function setPublic($storageGroup, $storageId, $isPublic, $mimeType) {
  }

  public function get($storageGroup, $storageId) {
  }

  public function getUrl($storageGroup, $storageId, $mimeType) {
  }

  public function getLocalFile($storageGroup, $storageId) {
  }

  public function output($storageGroup, $storageId, $rangeFrom = 0, $rangeTo = 0,
                         $bufferSize = 1024) {
  }
}

