<?php
require_once(substr(__FILE__, 0, -54).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Content/Box/Versions.php');

class PapayaContentBoxVersionsTest extends PapayaTestCase {
/**
  * @covers PapayaContentBoxVersions::load
  */
  public function testLoad() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(PapayaDatabaseResult::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'version_id' => '21',
            'version_time' => '123',
            'version_author_id' => '1',
            'version_message' => 'Version log message',
            'box_id' => '42'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('box_versions', 42), 10, 0)
      ->will($this->returnValue($databaseResult));
    $list = new PapayaContentBoxVersions();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load(42, 10, 0));
    $this->assertAttributeEquals(
      array(
        '21' => array(
          'id' => '21',
          'created' => '123',
          'owner' => '1',
          'message' => 'Version log message',
          'box_id' => '42',
        )
      ),
      '_records',
      $list
    );
  }

  /**
  * @covers PapayaContentBoxVersions::getVersion
  */
  public function testGetVersion() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('box_versions', 21))
      ->will($this->returnValue(FALSE));
    $list = new PapayaContentBoxVersions();
    $list->setDatabaseAccess($databaseAccess);
    $version = $list->getVersion(21);
    $this->assertInstanceOf(
      'PapayaContentBoxVersion', $version
    );
  }
}