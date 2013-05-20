<?php
require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Database/Record/Key/Sequence.php');

class PapayaDatabaseRecordKeySequenceTest extends PapayaTestCase {

  /**
  * PapayaDatabaseRecordKeySequence::__construct
  */
  public function testConstructor() {
    $sequence = $this->getSequenceFixture();
    $key = new PapayaDatabaseRecordKeySequence($sequence);
    $this->assertAttributeSame(
      $sequence, '_sequence', $key
    );
    $this->assertEquals(
      array('id'), $key->getProperties()
    );
  }

  /**
  * PapayaDatabaseRecordKeySequence::__construct
  */
  public function testConstructorWithPropertyName() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture(), 'ident');
    $this->assertEquals(
      array('ident'), $key->getProperties()
    );
  }


  /**
  * PapayaDatabaseRecordKeySequence::assign
  * PapayaDatabaseRecordKeySequence::getFilter
  */
  public function testAssignAndGetFilter() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertTrue($key->assign(array('id' => 'PROVIDED_SEQUENCE_ID')));
    $this->assertEquals(
      array('id' => 'PROVIDED_SEQUENCE_ID'), $key->getFilter()
    );
  }

  /**
  * PapayaDatabaseRecordKeySequence::assign
  * PapayaDatabaseRecordKeySequence::getFilter
  */
  public function testAssignWithInvalidData() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertFalse($key->assign(array('other' => 'PROVIDED_SEQUENCE_ID')));
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * PapayaDatabaseRecordKeySequence::getFilter
  */
  public function testGetFilterWithoutAssign() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * PapayaDatabaseRecordKeySequence::getFilter
  */
  public function testGetFilterWithoutAssignCreatingId() {
    $sequence =$this->getSequenceFixture();
    $sequence
      ->expects($this->once())
      ->method('next')
      ->will($this->returnValue('CREATED_SEQUENCE_ID'));
    $key = new PapayaDatabaseRecordKeySequence($sequence);
    $this->assertEquals(
      array('id' => 'CREATED_SEQUENCE_ID'),
      $key->getFilter(PapayaDatabaseInterfaceKey::ACTION_CREATE)
    );
  }

  /**
  * PapayaDatabaseRecordKeySequence::exists
  */
  public function testExistsExpectingTrue() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $key->assign(array('id' => 'PROVIDED_SEQUENCE_ID'));
    $this->assertTrue($key->exists());
  }

  /**
  * PapayaDatabaseRecordKeySequence::exists
  */
  public function testExistsExpectingFalse() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertFalse($key->exists());
  }

  /**
  * PapayaDatabaseRecordKeySequence::getQualities
  */
  public function testGetQualities() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertEquals(PapayaDatabaseInterfaceKey::CLIENT_GENERATED, $key->getQualities());
  }

  /**
  * PapayaDatabaseRecordKeySequence::__toString
  */
  public function testMagicToString() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $key->assign(array('id' => 'PROVIDED_SEQUENCE_ID'));
    $this->assertSame('PROVIDED_SEQUENCE_ID', (string)$key);
  }

  private function getSequenceFixture() {
    $sequence = $this
      ->getMockBuilder('PapayaDatabaseSequence')
      ->disableOriginalConstructor()
      ->getMock();
    return $sequence;
  }
}