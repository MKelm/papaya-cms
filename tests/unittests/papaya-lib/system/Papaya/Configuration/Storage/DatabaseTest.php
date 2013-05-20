<?php
require_once(substr(__FILE__, 0, -64).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Configuration/Storage.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Configuration/Storage/Database.php');

class PapayaConfigurationStorageDatabaseTest extends PapayaTestCase {

  /**
  * @covers PapayaConfigurationStorageDatabase::records
  */
  public function testRecordsGetAfterSet() {
    $records = $this->getMock('PapayaContentConfiguration');
    $storage = new PapayaConfigurationStorageDatabase();
    $this->assertSame($records, $storage->records($records));
  }

  /**
  * @covers PapayaConfigurationStorageDatabase::records
  */
  public function testRecordsGetImplicitCreate() {
    $storage = new PapayaConfigurationStorageDatabase();
    $this->assertInstanceOf('PapayaContentConfiguration', $storage->records());
  }

  /**
  * @covers PapayaConfigurationStorageDatabase::load
  */
  public function testLoad() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('errorHandler')
      ->with($this->isType('array'));

    $records = $this->getMock('PapayaContentConfiguration');
    $records
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $records
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage = new PapayaConfigurationStorageDatabase();
    $storage->records($records);
    $this->assertTrue($storage->load());
  }

  /**
  * @covers PapayaConfigurationStorageDatabase::handleError
  */
  public function testHandleErrorDevmode() {
    $options = $this->getMockConfigurationObject(
      array(
        'PAPAYA_DBG_DEVMODE' => TRUE
      )
    );
    $response = $this->getMock('PapayaResponse');
    $response
      ->expects($this->once())
      ->method('sendHeader')
      ->with('X-Papaya-Error: PapayaDatabaseExceptionQuery: Sample Error Message');

    $storage = new PapayaConfigurationStorageDatabase();
    $storage->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $options,
          'response' => $response
        )
      )
    );

    $exception = new PapayaDatabaseExceptionQuery(
      'Sample Error Message', 0, PapayaMessage::TYPE_ERROR, ''
    );
    $storage->handleError($exception);
  }

  /**
  * @covers PapayaConfigurationStorageDatabase::handleError
  */
  public function testHandleErrorNoDevmodeSilent() {
    $response = $this->getMock('PapayaResponse');
    $response
      ->expects($this->never())
      ->method('sendHeader');

    $storage = new PapayaConfigurationStorageDatabase();
    $storage->papaya(
      $this->getMockApplicationObject(
        array(
          'response' => $response
        )
      )
    );

    $exception = new PapayaDatabaseExceptionQuery(
      'Sample Error Message', 0, PapayaMessage::TYPE_ERROR, ''
    );
    $storage->handleError($exception);
  }

  /**
  * @covers PapayaConfigurationStorageDatabase::getIterator
  */
  public function testGetIterator() {
    $records = $this->getMock('PapayaContentConfiguration');
    $records
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              'SAMPLE_NAME' => array(
                'name' => 'SAMPLE_NAME',
                'value' => 'sample value'
              )
            )
          )
        )
      );
    $storage = new PapayaConfigurationStorageDatabase();
    $storage->records($records);
    $this->assertEquals(
      array('SAMPLE_NAME' => 'sample value'),
      PapayaUtilArray::ensure($storage)
    );
  }

}