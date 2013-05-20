<?php
require_once(substr(__FILE__, 0, -62).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application/Profile/Languages.php');

class PapayaApplicationProfileLanguagesTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileLanguages::getIdentifier
  */
  public function testGetIdentifier() {
    $profile = new PapayaApplicationProfileLanguages();
    $this->assertEquals(
      'Languages',
      $profile->getIdentifier()
    );
  }

  /**
  * @covers PapayaApplicationProfileLanguages::createObject
  */
  public function testCreateObject() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue(FALSE));
    $databaseManager = $this->getMock('PapayaDatabaseManager');
    $databaseManager
      ->expects($this->once())
      ->method('createDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $application = $this->getMock('PapayaApplication');
    $application
      ->expects($this->any())
      ->method('__get')
      ->with('database')
      ->will($this->returnValue($databaseManager));
    $profile = new PapayaApplicationProfileLanguages();
    $request = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaContentLanguages',
      $request
    );
  }
}