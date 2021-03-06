<?php
require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Database/Record/Key/Fields.php');

class PapayaDatabaseRecordKeyFieldsTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordKeyFields::__construct
  */
  public function testConstructor() {
    $key = $this->getKeyFixture();
    $this->assertEquals(
      array('fk_one_id', 'fk_two_id'), $key->getProperties()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::assign
  * @covers PapayaDatabaseRecordKeyFields::getFilter
  */
  public function testAssignAndGetFilter() {
    $key = $this->getKeyFixture();
    $this->assertTrue($key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42)));
    $this->assertEquals(
      array('fk_one_id' => 21, 'fk_two_id' => 42), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::assign
  * @covers PapayaDatabaseRecordKeyFields::getFilter
  */
  public function testAssignWithInvalidData() {
    $key = $this->getKeyFixture();
    $this->assertFalse($key->assign(array('other' => 42)));
    $this->assertEquals(
      array('fk_one_id' => NULL, 'fk_two_id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::getFilter
  */
  public function testGetFilterWithoutAssign() {
    $key = $this->getKeyFixture();
    $this->assertEquals(
      array('fk_one_id' => NULL, 'fk_two_id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::getProperties
  */
  public function testGetProperties() {
    $key = $this->getKeyFixture();
    $this->assertEquals(
      array('fk_one_id', 'fk_two_id'), $key->getProperties()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::exists
  */
  public function testExistsExpectingTrue() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchField')
      ->will($this->returnValue(1));

    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_one_id' => 21, 'field_two_id' => 42))
      ->will($this->returnValue('{CONDITION}'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with("SELECT COUNT(*) FROM %s WHERE {CONDITION}", array('sometable'))
      ->will($this->returnValue($databaseResult));

    $mapping = $this->getMock('PapayaDatabaseInterfaceMapping');
    $mapping
      ->expects($this->once())
      ->method('mapPropertiesToFields')
      ->with(array('fk_one_id' => 21, 'fk_two_id' => 42))
      ->will($this->returnValue(array('field_one_id' => 21, 'field_two_id' => 42)));

    $record = $this->getMock('PapayaDatabaseRecord');
    $record
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $record
      ->expects($this->any())
      ->method('mapping')
      ->will($this->returnValue($mapping));

    $key = $this->getKeyFixture($record);
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $this->assertTrue($key->exists());
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::exists
  */
  public function testExistsWithDatabaseErrorExpectingFalse() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_one_id' => 21, 'field_two_id' => 42))
      ->will($this->returnValue('{CONDITION}'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with("SELECT COUNT(*) FROM %s WHERE {CONDITION}", array('sometable'))
      ->will($this->returnValue(FALSE));

    $mapping = $this->getMock('PapayaDatabaseInterfaceMapping');
    $mapping
      ->expects($this->once())
      ->method('mapPropertiesToFields')
      ->with(array('fk_one_id' => 21, 'fk_two_id' => 42))
      ->will($this->returnValue(array('field_one_id' => 21, 'field_two_id' => 42)));

    $record = $this->getMock('PapayaDatabaseRecord');
    $record
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $record
      ->expects($this->any())
      ->method('mapping')
      ->will($this->returnValue($mapping));

    $key = $this->getKeyFixture($record);
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $this->assertFalse($key->exists());
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::exists
  */
  public function testExistsWithEmptyMappingResult() {
    $mapping = $this->getMock('PapayaDatabaseInterfaceMapping');
    $mapping
      ->expects($this->once())
      ->method('mapPropertiesToFields')
      ->with(array('fk_one_id' => NULL, 'fk_two_id' => NULL))
      ->will($this->returnValue(array()));
    $record = $this->getMock('PapayaDatabaseRecord');
    $record
      ->expects($this->any())
      ->method('mapping')
      ->will($this->returnValue($mapping));
    $key = $this->getKeyFixture($record);
    $this->assertFalse($key->exists());
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::getQualities
  */
  public function testGetQualities() {
    $key = $this->getKeyFixture();
    $this->assertEquals(0, $key->getQualities());
  }

  /**
  * @covers PapayaDatabaseRecordKeyFields::__toString
  */
  public function testMagicToString() {
    $key = $this->getKeyFixture();
    $key->assign(array('fk_one_id' => 21, 'fk_two_id' => 42));
    $this->assertSame('21|42', (string)$key);
  }

  public function getKeyFixture(PapayaDatabaseRecord $record = NULL) {
    if (is_null($record)) {
      $record = $this->getMock('PapayaDatabaseRecord');
    }
    return new PapayaDatabaseRecordKeyFields(
      $record, 'sometable', array('fk_one_id', 'fk_two_id')
    );
  }
}