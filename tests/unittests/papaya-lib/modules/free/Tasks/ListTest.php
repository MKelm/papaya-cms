<?php
require_once(substr(__FILE__, 0, -43).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'modules/free/Tasks/Item.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/free/Tasks/List.php');

class PapayaModuleTasksListTest extends PapayaTestCase {

  /**
  * @covers PapayaModuleTasksList::load
  */
  public function testLoad() {
    $record = array(
      'tasks_item_id' => 'sample_id',
      'tasks_item_status' => PapayaModuleTasksItem::TASK_CREATED,
      'tasks_item_created' => 21,
      'tasks_item_modified' => 42,
      'tasks_item_title' => 'Sample title'
    );
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->exactly(2))
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->onConsecutiveCalls($this->returnValue($record), FALSE));
    $databaseResult
      ->expects($this->once())
      ->method('absCount')
      ->will($this->returnValue(23));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'query'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $databaseAccess
      ->expects($this->once())
      ->method('query')
      ->with($this->isType('string'), 21, 42)
      ->will($this->returnValue($databaseResult));
    $list = new PapayaModuleTasksList();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $list->load(21, 42)
    );
    $this->assertEquals(
      23, $list->countAll()
    );
  }

  /**
  * @covers PapayaModuleTasksList::load
  */
  public function testLoadWithFilter() {
    $record = array(
      'tasks_item_id' => 'sample_id',
      'tasks_item_status' => PapayaModuleTasksItem::TASK_CREATED,
      'tasks_item_created' => 21,
      'tasks_item_modified' => 42,
      'tasks_item_title' => 'Sample title'
    );
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->exactly(2))
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->onConsecutiveCalls($this->returnValue($record), FALSE));
    $databaseResult
      ->expects($this->once())
      ->method('absCount')
      ->will($this->returnValue(23));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'query'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $databaseAccess
      ->expects($this->once())
      ->method('query')
      ->with(
        $this->isType('string'),
        21,
        42
      )
      ->will($this->returnValue($databaseResult));

    $filterObjectMock = $this->getMock('PapayaModuleTasksFilter', array('getFilterConditions'));
    $filterObjectMock
      ->expects($this->once())
      ->method('getFilterConditions')
      ->will($this->returnValue('hello'));

    $list = new PapayaModuleTasksList_TestProxy();
    $list->_setFilterObject($filterObjectMock);
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $list->load(
        21,
        42,
        array('id-starts-with' => 'sample', 'status' => PapayaModuleTasksItem::TASK_CREATED),
        'asc'
      )
    );
  }
  /**
  * @covers PapayaModuleTasksList::load
  */
  public function testLoadExpectingFalse() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'query'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $databaseAccess
      ->expects($this->once())
      ->method('query')
      ->with($this->isType('string'), 21, 42)
      ->will($this->returnValue(FALSE));
    $list = new PapayaModuleTasksList();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $list->load(21, 42)
    );
  }

  /**
  * @covers PapayaModuleTasksList::delete
  */
  public function testDelete() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'deleteRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('tasks_items')
      ->will($this->returnValue('papaya_tasks_items'));
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with('papaya_tasks_items', 'tasks_item_id', 'sample_id')
      ->will($this->returnValue(1));
    $list = new PapayaModuleTasksList();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $list->delete('sample_id')
    );
  }



  /**
  * @covers PapayaModuleTasksList::_setFilterObject
  */
  public function testSetFilterConditionObject() {
    $filterMock = $this->getMock('PapayaModuleTasksFilter');

    $list = new PapayaModuleTasksList_TestProxy();
    $list->_setFilterObject($filterMock);
    $this->assertAttributeInstanceOf('PapayaModuleTasksFilter', '_filterObject', $list);
  }

  /**
  * @covers PapayaModuleTasksList::_getFilterObject
  */
  public function testGetFilterConditionObject() {
    $list = new PapayaModuleTasksList_TestProxy();
    $this->assertInstanceOf('PapayaModuleTasksFilter', $list->_getFilterObject());
  }

}

class PapayaModuleTasksList_TestProxy extends PapayaModuleTasksList {

  public function _setFilterObject($filterObject) {
    parent::_setFilterObject($filterObject);
  }

  public function _getFilterObject() {
    return parent::_getFilterObject();
  }
}