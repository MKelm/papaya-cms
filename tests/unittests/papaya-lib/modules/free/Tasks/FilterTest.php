<?php
require_once(substr(__FILE__, 0, -45).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'modules/free/Tasks/Filter.php');

class PapayaModuleTasksFilterTest extends PapayaTestCase {

  /**
  * @covers PapayaModuleTasksFilter::__construct
  */
  public function testConstructorWithFilterParams() {
    $filterParams = array(
      'id-starts-with' => 'abc1d2ef',
      'status' => '3',
      'data-contains' => 'Hello World'
    );
    $filterObject = new PapayaModuleTasksFilter($filterParams);
    $this->assertAttributeEquals($filterParams, '_filterParams', $filterObject);
  }

  /**
  * @covers PapayaModuleTasksFilter::__construct
  */
  public function testConstructorWithoutFilterParams() {
    $filterObject = new PapayaModuleTasksFilter();
    $this->assertAttributeEquals(array(), '_filterParams', $filterObject);
  }

  /**
  * @covers PapayaModuleTasksFilter::setFilterParams
  */
  public function testSetFilterParams() {
    $filterObject = new PapayaModuleTasksFilter();
    $filterParams = array(
      'id-starts-with' => 'abc1d2ef',
      'status' => '3',
      'data-contains' => 'Hello World'
    );
    $filterObject->setFilterParams($filterParams);
    $this->assertAttributeEquals($filterParams, '_filterParams', $filterObject);
  }

  /**
   * @covers PapayaModuleTasksFilter::getFilterConditions
   */
  public function testGetFiltersWithEmptyParams() {
    $filterObject = new PapayaModuleTasksFilter();
    $filterParams = array();
    $filterObject->setFilterParams($filterParams);
    $this->assertEquals('', $filterObject->getFilterConditions());
  }

  /**
  * @covers PapayaModuleTasksFilter::getFilterConditions
  */
  public function testGetFilterConditions() {
    $filterObject = new PapayaModuleTasksFilter_TestProxy();
    $filterParams = array(
      'id-starts-with' => 'abc1d2ef',
      'status' => '3',
      'data-contains' => 'Hello World',
      'time-from' => '2011-07-11',
      'time-to' => '2011-07-13',
      'task-guid' => 'abcdefghijklmo'
    );

    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('escapeString'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->exactly(3))
      ->method('escapeString')
      ->with($this->isType('string'))
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue('abc1d2ef'),
          $this->returnValue('Hello World'),
          $this->returnValue('abcdefghijklmo')
        )
      );
    $filterObject->setDatabaseAccess($databaseAccess);
    $filterObject->setFilterParams($filterParams);
    $expected = " WHERE tasks_item_id LIKE 'abc1d2ef%' AND tasks_item_status = '3' AND (tasks_item_data LIKE '%Hello World%'
      OR tasks_item_title LIKE '%Hello World%'
      OR tasks_item_description LIKE '%Hello World%') AND tasks_item_created between 1310342400 AND 1310601600 AND tasks_item_guid = 'abcdefghijklmo'";
    $this->assertEquals($expected, $filterObject->getFilterConditions());
  }

  /**
  * @covers PapayaModuleTasksFilter::_getTaskItemIdCondition
  */
  public function testGetTaskItemIdCondition() {
    $filterObject = new PapayaModuleTasksFilter_TestProxy();
    $filterParams = array(
      'id-starts-with' => 'abc1d2ef'
    );

    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('escapeString'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('escapeString')
      ->with($this->isType('string'))
      ->will($this->returnValue('abc1d2ef'));
    $filterObject->setDatabaseAccess($databaseAccess);

    $filterObject->setFilterParams($filterParams);
    $expected = "tasks_item_id LIKE 'abc1d2ef%'";
    $this->assertEquals($expected, $filterObject->_getTaskItemIdCondition());
  }

  /**
  * @covers PapayaModuleTasksFilter::_getTaskItemStatusCondition
  */
  public function testGetTaskItemStatusCondition() {
    $filterObject = new PapayaModuleTasksFilter_TestProxy();
    $filterParams = array(
      'status' => '3'
    );
    $filterObject->setFilterParams($filterParams);
    $expected = "tasks_item_status = '3'";
    $this->assertEquals($expected, $filterObject->_getTaskItemStatusCondition());
  }

  /**
  * @covers PapayaModuleTasksFilter::_getTaskItemGuidCondition
  */
  public function testGetTaskItemGuidCondition() {
    $filterObject = new PapayaModuleTasksFilter_TestProxy();
    $filterParams = array(
      'task-guid' => 'abcdef'
    );

    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('escapeString'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('escapeString')
      ->with($this->isType('string'))
      ->will($this->returnValue('abcdef'));
    $filterObject->setDatabaseAccess($databaseAccess);

    $filterObject->setFilterParams($filterParams);
    $expected = "tasks_item_guid = 'abcdef'";
    $this->assertEquals($expected, $filterObject->_getTaskItemGuidCondition());
  }

  /**
  * @covers PapayaModuleTasksFilter::_getTaskItemDataCondition
  */
  public function testGetTaskItemDataCondition() {
    $filterObject = new PapayaModuleTasksFilter_TestProxy();
    $filterParams = array(
      'data-contains' => 'Hello World'
    );

    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('escapeString'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('escapeString')
      ->with($this->isType('string'))
      ->will($this->returnValue('Hello World'));
    $filterObject->setDatabaseAccess($databaseAccess);

    $filterObject->setFilterParams($filterParams);
    $expected = "(tasks_item_data LIKE '%Hello World%'
      OR tasks_item_title LIKE '%Hello World%'
      OR tasks_item_description LIKE '%Hello World%')";
    $this->assertEquals($expected, $filterObject->_getTaskItemDataCondition());
  }

  /**
  * @covers PapayaModuleTasksFilter::_getTimeframeCondition
  */
  public function testGetTimeframeCondition() {
    $filterObject = new PapayaModuleTasksFilter_TestProxy();
    $filterParams = array(
      'time-from' => '2011-07-11',
      'time-to' => '2011-07-13'
    );
    $filterObject->setFilterParams($filterParams);
    $expected = ' WHERE tasks_item_created between 1310342400 AND 1310601600';
    $this->assertEquals($expected, $filterObject->getFilterConditions());
  }

  /**
  * @covers PapayaModuleTasksFilter::_getTimeframeCondition
  */
  public function testGetTimeframeConditionWithStart() {
    $filterObject = new PapayaModuleTasksFilter_TestProxy();
    $filterParams = array(
      'time-from' => '2011-07-11',
      'time-to' => NULL
    );
    $filterObject->setFilterParams($filterParams);
    $expected = ' WHERE tasks_item_created between 1310342400 AND 1234567890';
    $this->assertEquals($expected, $filterObject->getFilterConditions());
  }

  /**
  * @covers PapayaModuleTasksFilter::_getTimeframeCondition
  */
  public function testGetTimeframeConditionWithEnd() {
    $filterObject = new PapayaModuleTasksFilter_TestProxy();
    $filterParams = array(
      'time-from' => NULL,
      'time-to' => '2011-07-13'
    );
    $filterObject->setFilterParams($filterParams);
    $expected = ' WHERE tasks_item_created between 0 AND 1310601600';
    $this->assertEquals($expected, $filterObject->getFilterConditions());
  }

  /**
  * @covers PapayaModuleTasksFilter::_getCurrentTime
  */
  public function testGetCurrentTime() {
    $filterObject = new PapayaModuleTasksFilter_TestProxy2();
    $this->assertInternalType('integer', $filterObject->_getCurrentTime());
  }

  /**
  * @covers PapayaModuleTasksFilter::getModuleGuids
  */
  public function testGetModuleGuids() {
    $filterObject = new PapayaModuleTasksFilter();
    $record = array(
      'module_guid' => 'abc',
      'module_title' => 'Module One'
    );

    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->exactly(2))
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->onConsecutiveCalls($this->returnValue($record), FALSE));

    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->exactly(2))
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue('papaya_modules'),
          $this->returnValue('papaya_tasks_items')
        )
      );
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue($databaseResult));
    $filterObject->setDatabaseAccess($databaseAccess);

    $expected = array(
      'abc' => 'Module One'
    );
    $this->assertEquals($expected, $filterObject->getModuleGuids());
  }

  /**
  * @covers PapayaModuleTasksFilter::getModuleGuids
  */
  public function testGetModuleGuidsWithoutResults() {
    $filterObject = new PapayaModuleTasksFilter();
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->exactly(2))
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue('papaya_modules'),
          $this->returnValue('papaya_tasks_items')
        )
      );
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue(FALSE));
    $filterObject->setDatabaseAccess($databaseAccess);
    $expected = array();
    $this->assertEquals($expected, $filterObject->getModuleGuids());
  }

}

class PapayaModuleTasksFilter_TestProxy extends PapayaModuleTasksFilter {

  public function _getTaskItemIdCondition() {
    return parent::_getTaskItemIdCondition();
  }

  public function _getTaskItemDataCondition() {
    return parent::_getTaskItemDataCondition();
  }

  public function _getTaskItemStatusCondition() {
    return parent::_getTaskItemStatusCondition();
  }

  public function _getTimeframeCondition() {
    return parent::_getTimeframeCondition();
  }

  public function _getTaskItemGuidCondition() {
    return parent::_getTaskItemGuidCondition();
  }

  public function _getCurrentTime() {
    return 1234567890;
  }
}

class PapayaModuleTasksFilter_TestProxy2 extends PapayaModuleTasksFilter {
  public function _getCurrentTime() {
    return parent::_getCurrentTime();
  }
}