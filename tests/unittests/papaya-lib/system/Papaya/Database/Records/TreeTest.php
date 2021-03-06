<?php
require_once(substr(__FILE__, 0, -55).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Database/Records/Tree.php');

class PapayaDatabaseRecordsTreeTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordsTree::_loadRecords
  * @covers PapayaDatabaseRecordsTree::getIterator
  */
  public function testLoadAndIterateRoot() {
    $records = new PapayaDatabaseRecordsTree_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseFixture());
    $this->assertTrue($records->load());
    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'parent_id' => 0,
          'title' => 'One'
        ),
        2 => array(
          'id' => 2,
          'parent_id' => 0,
          'title' => 'Two'
        ),
      ),
      iterator_to_array($records)
    );
  }

  /**
  * @covers PapayaDatabaseRecordsTree::_loadRecords
  * @covers PapayaDatabaseRecordsTree::getIterator
  */
  public function testLoadAndIterateAll() {
    $records = new PapayaDatabaseRecordsTree_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseFixture());
    $this->assertTrue($records->load());
    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'parent_id' => 0,
          'title' => 'One'
        ),
        3 => array(
          'id' => 3,
          'parent_id' => 1,
          'title' => 'Tree'
        ),
        2 => array(
          'id' => 2,
          'parent_id' => 0,
          'title' => 'Two'
        ),
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($records, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }

  /**
  * @covers PapayaDatabaseRecordsTree::_loadRecords
  * @covers PapayaDatabaseRecordsTree::getIterator
  */
  public function testLoadAndIterateLeafs() {
    $records = new PapayaDatabaseRecordsTree_TestProxy();
    $records->setDatabaseAccess($this->getDatabaseFixture());
    $this->assertTrue($records->load());
    $this->assertEquals(
      array(
        3 => array(
          'id' => 3,
          'parent_id' => 1,
          'title' => 'Tree'
        ),
        2 => array(
          'id' => 2,
          'parent_id' => 0,
          'title' => 'Two'
        ),
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($records)
      )
    );
  }

  /**
  * @covers PapayaDatabaseRecordsTree::_loadRecords
  * @covers PapayaDatabaseRecordsTree::getIterator
  */
  public function testLoadWithInvalidIdentifierExpectingException() {
    $records = new PapayaDatabaseRecordsTree_TestProxy();
    $records->_identifierProperties = array();
    $records->setDatabaseAccess($this->getDatabaseFixture());
    $this->setExpectedException(
      'LogicException',
      'Identifier properties needed to link children to parents.'
    );
    $records->load();
  }

  /**
  * @covers PapayaDatabaseRecordsTree::load
  * @covers PapayaDatabaseRecordsTree::_loadRecords
  */
  public function testLoadExpectingFalse() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field_id' => 42))
      ->will($this->returnValue(" field_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('tablename')
      )
      ->will($this->returnValue(FALSE));
    $records = new PapayaDatabaseRecordsTree_TestProxy();
    $records->setDatabaseAccess($databaseAccess);
    $this->assertFalse($records->load(42));
  }

  /************************
  * Fixtures
  ************************/

  public function getDatabaseFixture() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'field_id' => 1,
            'field_parent_id' => 0,
            'field_title' => 'One'
          ),
          array(
            'field_id' => 2,
            'field_parent_id' => 0,
            'field_title' => 'Two'
          ),
          array(
            'field_id' => 3,
            'field_parent_id' => 1,
            'field_title' => 'Tree'
          )
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array('tablename')
      )
      ->will($this->returnValue($databaseResult));
    return $databaseAccess;
  }
}

class PapayaDatabaseRecordsTree_TestProxy extends PapayaDatabaseRecordsTree {

  public $_identifierProperties = array('id');

  protected $_fields = array(
    'id' => 'field_id',
    'parent_id' => 'field_parent_id',
    'title' => 'field_title'
  );

  protected $_tableName = 'tablename';
}