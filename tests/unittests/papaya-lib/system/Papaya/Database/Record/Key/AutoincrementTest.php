<?php
require_once(substr(__FILE__, 0, -66).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Database/Record/Key/Autoincrement.php');

class PapayaDatabaseRecordKeyAutoincrementTest extends PapayaTestCase {

  /**
  * PapayaDatabaseRecordKeyAutoincrement::__construct
  */
  public function testConstructor() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertEquals(
      array('id'), $key->getProperties()
    );
  }

  /**
  * PapayaDatabaseRecordKeyAutoincrement::__construct
  * PapayaDatabaseRecordKeyAutoincrement::getProperties
  */
  public function testConstructorWithPropertyParameter() {
    $key = new PapayaDatabaseRecordKeyAutoincrement('other');
    $this->assertEquals(
      array('other'), $key->getProperties()
    );
  }

  /**
  * PapayaDatabaseRecordKeyAutoincrement::assign
  * PapayaDatabaseRecordKeyAutoincrement::getFilter
  */
  public function testAssignAndGetFilter() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertTrue($key->assign(array('id' => 42)));
    $this->assertEquals(
      array('id' => 42), $key->getFilter()
    );
  }

  /**
  * PapayaDatabaseRecordKeyAutoincrement::assign
  * PapayaDatabaseRecordKeyAutoincrement::getFilter
  */
  public function testAssignWithInvalidData() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertFalse($key->assign(array('other' => 42)));
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * PapayaDatabaseRecordKeyAutoincrement::getFilter
  */
  public function testGetFilterWithoutAssign() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * PapayaDatabaseRecordKeyAutoincrement::exists
  */
  public function testExistsExpectingTrue() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $key->assign(array('id' => 42));
    $this->assertTrue($key->exists());
  }

  /**
  * PapayaDatabaseRecordKeyAutoincrement::exists
  */
  public function testExistsExpectingFalse() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertFalse($key->exists());
  }

  /**
  * PapayaDatabaseRecordKeyAutoincrement::getQualities
  */
  public function testGetQualities() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertEquals(PapayaDatabaseInterfaceKey::DATABASE_PROVIDED, $key->getQualities());
  }

  /**
  * PapayaDatabaseRecordKeyAutoincrement::__toString
  */
  public function testMagicToString() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $key->assign(array('id' => 42));
    $this->assertSame('42', (string)$key);
  }
}