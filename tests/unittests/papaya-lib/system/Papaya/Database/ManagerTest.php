<?php
require_once(substr(__FILE__, 0, -49).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/sys_db_simple.php');


class PapayaDatabaseManagerTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseManager::setConfiguration
  */
  public function testSetConfiguration() {
    $manager = new PapayaDatabaseManager();
    $options = $this->getMockConfigurationObject();
    $manager->setConfiguration($options);
    $this->assertAttributeSame(
      $options, '_configuration', $manager
    );
  }

  /**
  * @covers PapayaDatabaseManager::getConfiguration
  */
  public function testGetConfigutation() {
    $manager = new PapayaDatabaseManager();
    $options = $this->getMockConfigurationObject();
    $manager->setConfiguration($options);
    $this->assertSame(
      $options, $manager->getConfiguration()
    );
  }

  /**
  * @covers PapayaDatabaseManager::setConnector
  * @covers PapayaDatabaseManager::_getConnectorUris
  */
  public function testSetConnector() {
    $manager = new PapayaDatabaseManager();
    $connector = new db_simple();
    $manager->setConnector($connector, 'READ', 'WRITE');
    $this->assertAttributeSame(
      array("READ\nWRITE" => $connector),
      '_connectors',
      $manager
    );
  }

  /**
  * @covers PapayaDatabaseManager::setConnector
  * @covers PapayaDatabaseManager::_getConnectorUris
  */
  public function testSetConnectorWithoutWriteUri() {
    $manager = new PapayaDatabaseManager();
    $connector = new db_simple();
    $manager->setConnector($connector, 'READ');
    $this->assertAttributeSame(
      array("READ\nREAD" => $connector),
      '_connectors',
      $manager
    );
  }

  /**
  * @covers PapayaDatabaseManager::setConnector
  * @covers PapayaDatabaseManager::_getConnectorUris
  */
  public function testSetConnectorWithoutReadUri() {
    $manager = new PapayaDatabaseManager();
    $connector = new db_simple();
    $manager->setConfiguration(
      $this->getMockConfigurationObject(
        array(
          'PAPAYA_DB_URI' => 'read_default',
          'PAPAYA_DB_URI_WRITE' => 'write_default'
        )
      )
    );
    $manager->setConnector($connector);
    $this->assertAttributeSame(
      array("read_default\nwrite_default" => $connector),
      '_connectors',
      $manager
    );
  }

  /**
  * @covers PapayaDatabaseManager::getConnector
  */
  public function testGetConnector() {
    $manager = new PapayaDatabaseManager();
    $connector = new db_simple();
    $manager->setConnector($connector, 'READ');
    $this->assertSame(
      $connector,
      $manager->getConnector('READ')
    );
  }

  /**
  * @covers PapayaDatabaseManager::getConnector
  */
  public function testGetConnectorImplicitCreate() {
    $manager = new PapayaDatabaseManager();
    $this->assertInstanceOf(
      'db_simple',
      $manager->getConnector('READ')
    );
  }

  /**
  * @covers PapayaDatabaseManager::close
  */
  public function testClose() {
    $manager = new PapayaDatabaseManager();
    $connector = $this->getMock('db_simple', array('close'));
    $connector
      ->expects($this->once())
      ->method('close');
    $manager->setConnector($connector, 'READ');
    $manager->close();
  }

  /**
  * @covers PapayaDatabaseManager::createDatabaseAccess
  */
  public function testCreateDatabaseAccess() {
    $manager = new PapayaDatabaseManager();
    $manager->papaya($papaya = $this->getMockApplicationObject());
    $this->assertInstanceOf(
      'PapayaDatabaseAccess', $databaseAccess = $manager->createDatabaseAccess(new stdClass)
    );
    $this->assertSame(
      $papaya, $databaseAccess->papaya()
    );
  }

  /**
  * @covers PapayaDatabaseManager::createDatabaseAccess
  */
  public function testCreateDatabaseAccessWithUris() {
    $manager = new PapayaDatabaseManager();
    $manager->papaya($papaya = $this->getMockApplicationObject());
    $databaseAccess = $manager->createDatabaseAccess(new stdClass, 'READ_SAMPLE', 'WRITE_SAMPLE');
    $this->assertAttributeEquals(
      'READ_SAMPLE', '_uriRead', $databaseAccess
    );
    $this->assertAttributeEquals(
      'WRITE_SAMPLE', '_uriWrite', $databaseAccess
    );
  }
}

?>