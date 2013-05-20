<?php
require_once(substr(__FILE__, 0, -43).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'modules/free/Tasks/Item.php');

class PapayaModuleTasksItemTest extends PapayaTestCase {

  /**
  * @covers PapayaModuleTasksItem::load
  */
  public function testLoad() {
    $record = array(
      'tasks_item_id' => 'sample_id',
      'tasks_item_status' => PapayaModuleTasksItem::TASK_CREATED,
      'tasks_item_created' => 21,
      'tasks_item_modified' => 42,
      'tasks_item_modified_by' => 'ab123456789012345678901234567890',
      'tasks_item_title' => 'Sample title',
      'tasks_item_description' => 'Sample description',
      'tasks_item_guid' => '123456789012345678901234567890ab',
      'tasks_item_data' => '<data/>'
    );
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue($record));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('papaya_tasks_items', 'sample_id'))
      ->will($this->returnValue($databaseResult));
    $item = new PapayaModuleTasksItem();
    $item->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $item->load('sample_id')
    );
    $this->assertAttributeEquals(
      array(
        'id' => 'sample_id',
        'status' => 1,
        'created' => 21,
        'modified' => 42,
        'modified_by' => 'ab123456789012345678901234567890',
        'title' => 'Sample title',
        'description' => 'Sample description',
        'guid' => '123456789012345678901234567890ab',
        'data' => array()
      ),
      '_values',
      $item
    );
  }

  /**
  * @covers PapayaModuleTasksItem::load
  */
  public function testLoadFailedExpectingFalse() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('papaya_tasks_items', 'sample_id'))
      ->will($this->returnValue(FALSE));
    $item = new PapayaModuleTasksItem();
    $item->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $item->load('sample_id')
    );
  }

  /**
  * @covers PapayaModuleTasksItem::load
  */
  public function testLoadNoRecordExpectingFalse() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('papaya_tasks_items', 'sample_id'))
      ->will($this->returnValue($databaseResult));
    $item = new PapayaModuleTasksItem();
    $item->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $item->load('sample_id')
    );
  }

  /**
  * @covers PapayaModuleTasksItem::setSequence
  */
  public function testSetSequence() {
    $sequence = $this->getMock(
      'PapayaDatabaseSequenceHuman', array('next'), array('table', 'field')
    );
    $item = new PapayaModuleTasksItem();
    $item->setSequence($sequence);
    $this->assertAttributeSame(
      $sequence, '_sequence', $item
    );
  }


  /**
  * @covers PapayaModuleTasksItem::getSequence
  */
  public function testGetSequence() {
    $sequence = $this->getMock(
      'PapayaDatabaseSequenceHuman', array('next'), array('table', 'field')
    );
    $item = new PapayaModuleTasksItem();
    $item->setSequence($sequence);
    $this->assertSame(
      $sequence, $item->getSequence()
    );
  }

  /**
  * @covers PapayaModuleTasksItem::getSequence
  */
  public function testGetSequenceImplizitCreate() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $item = new PapayaModuleTasksItem();
    $item->setDatabaseAccess($databaseAccess);
    $this->assertInstanceOf(
      'PapayaDatabaseSequenceHuman', $item->getSequence()
    );
  }

  /**
  * @covers PapayaModuleTasksItem::save
  * @covers PapayaModuleTasksItem::_insert
  * @covers PapayaModuleTasksItem::_getCurrentUser
  */
  public function testSaveCreateNew() {
    $sequence = $this->getMock(
      'PapayaDatabaseSequenceHuman', array('next'), array('table', 'field')
    );
    $sequence
      ->expects($this->once())
      ->method('next')
      ->will($this->returnValue('234567abc'));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'insertRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with($this->equalTo('papaya_tasks_items'), $this->isNull(), $this->isType('array'))
      ->will($this->returnCallback(array($this, 'checkInsertData')));
    $item = new PapayaModuleTasksItem();
    $item->papaya($this->getMockApplicationObject());
    $item->setDatabaseAccess($databaseAccess);
    $item->setSequence($sequence);
    $item['title'] = 'Sample Title';
    $item['description'] = 'Sample Description';
    $item['guid'] = '123456789012345678901234567890ab';
    $item['data'] = array('sample' => 'foobar');
    $this->assertEquals('234567abc', $item->save());
  }

  public function checkInsertData($table, $idField, $data) {
    $this->assertEquals(
      '234567abc', $data['tasks_item_id']
    );
    $this->assertGreaterThan(
      0, $data['tasks_item_created']
    );
    $this->assertGreaterThan(
      0, $data['tasks_item_modified']
    );
    $this->assertEquals(
      '', $data['tasks_item_modified_by']
    );
    $this->assertEquals(
      'Sample Title', $data['tasks_item_title']
    );
    $this->assertEquals(
      'Sample Description', $data['tasks_item_description']
    );
    $this->assertEquals(
      '123456789012345678901234567890ab', $data['tasks_item_guid']
    );
    $this->assertEquals(
      '<data version="2"><data-element name="sample">foobar</data-element></data>',
      $data['tasks_item_data']
    );
    return 1;
  }

  /**
  * @covers PapayaModuleTasksItem::save
  * @covers PapayaModuleTasksItem::_insert
  */
  public function testInsertExpectingFalse() {
    $sequence = $this->getMock(
     'PapayaDatabaseSequenceHuman', array('next'), array('table', 'field')
    );
    $sequence
      ->expects($this->once())
      ->method('next')
      ->will($this->returnValue('234567abc'));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'insertRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->withAnyParameters()
      ->will($this->returnValue(FALSE));
    $item = new PapayaModuleTasksItem();
    $item->setDatabaseAccess($databaseAccess);
    $item->setSequence($sequence);
    $this->assertFalse($item->save());
  }

  /**
  * @covers PapayaModuleTasksItem::save
  * @covers PapayaModuleTasksItem::_update
  * @covers PapayaModuleTasksItem::_getCurrentUser
  */
  public function testSaveUpdateExisting() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'updateRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        $this->equalTo('papaya_tasks_items'),
        $this->isType('array'),
        $this->equalTo('tasks_item_id'),
        $this->equalTo('234567abc')
      )
      ->will($this->returnCallback(array($this, 'checkUpdateData')));
    $item = new PapayaModuleTasksItem();
    $item->papaya(
      $this->getMockApplicationObject(
        array('AdministrationUser' => $this->getAuthUserMock())
      )
    );
    $item->setDatabaseAccess($databaseAccess);
    $item['id'] = '234567abc';
    $item['title'] = 'Sample Title';
    $item['description'] = 'Sample Description';
    $item['guid'] = '123456789012345678901234567890ab';
    $item['data'] = array('sample' => 'foobar');
    $this->assertTrue($item->save());
  }

  public function checkUpdateData($table, $data, $field, $value) {
    $this->assertGreaterThan(
      0, $data['tasks_item_modified']
    );
    $this->assertEquals(
      'ab123456789012345678901234567890', $data['tasks_item_modified_by']
    );
    $this->assertEquals(
      'Sample Title', $data['tasks_item_title']
    );
    $this->assertEquals(
      'Sample Description', $data['tasks_item_description']
    );
    $this->assertEquals(
      '123456789012345678901234567890ab', $data['tasks_item_guid']
    );
    $this->assertEquals(
      '<data version="2"><data-element name="sample">foobar</data-element></data>',
      $data['tasks_item_data']
    );
    return '234567abc';
  }

  public function getAuthUserMock() {
    $user = $this->getMock('PapayaAuthenticationUser', array('getUserId'));
    $user
      ->expects($this->once())
      ->method('getUserId')
      ->will($this->returnValue('ab123456789012345678901234567890'));
    return $user;
  }
}