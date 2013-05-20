<?php

require_once(substr(__FILE__, 0, -47).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaMediaStorageTest extends PapayaTestCase {

  public function testGetServiceDefault() {
    $service = PapayaMediaStorage::getService();
    $this->assertTrue($service instanceof PapayaMediaStorageService);
    $serviceTwo = PapayaMediaStorage::getService();
    $this->assertTrue($service instanceof PapayaMediaStorageService);
    $this->assertTrue($service === $serviceTwo);
  }

  public function testGetServiceInvalid() {
    $this->setExpectedException('InvalidArgumentException');
    PapayaMediaStorage::getService('InvalidServiceName');
  }

  public function testGetServiceWithConfiguration() {
    $configuration = $this->getMockConfigurationObject();
    $service = PapayaMediaStorage::getService('file', $configuration, FALSE);
  }

  public function testGetServiceNonStatic() {
    $service = PapayaMediaStorage::getService('file', NULL, FALSE);
    $this->assertTrue($service instanceof PapayaMediaStorageService);
    $serviceTwo = PapayaMediaStorage::getService('file', NULL, FALSE);
    $this->assertTrue($service instanceof PapayaMediaStorageService);
    $this->assertTrue($service !== $serviceTwo);
  }
}
