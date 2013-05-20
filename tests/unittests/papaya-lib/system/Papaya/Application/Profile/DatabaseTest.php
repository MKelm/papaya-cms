<?php

require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application/Profile/Database.php');

class PapayaApplicationProfileDatabaseTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileDatabase::getIdentifier
  */
  public function testGetIdentifier() {
    $profile = new PapayaApplicationProfileDatabase();
    $this->assertEquals(
      'Database',
      $profile->getIdentifier()
    );
  }

  /**
  * @covers PapayaApplicationProfileDatabase::createObject
  */
  public function testCreateObject() {
    $options = $this->getMockConfigurationObject();
    $application = $this->getMock('PapayaApplication');
    $application
      ->expects($this->once())
      ->method('getObject')
      ->with('Options')
      ->will($this->returnValue($options));
    $profile = new PapayaApplicationProfileDatabase();
    $request = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaDatabaseManager',
      $request
    );

  }
}
?>