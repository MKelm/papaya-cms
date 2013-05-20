<?php
require_once(substr(__FILE__, 0, -44).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Plugin/List.php');

class PapayaPluginListTest extends PapayaTestCase {

  /**
  * @covers PapayaPluginList::load
  */
  public function testLoad() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->exactly(2))
      ->method('fetchRow')
      ->with($this->equalTo(PapayaDatabaseResult::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'module_guid' => '123',
            'module_class' => 'SampleClass',
            'module_path' => '/Sample/Path',
            'module_file' => 'SampleFile.php',
            'modulegroup_prefix' => 'SamplePrefix',
          ),
          FALSE
        )
      );
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'queryFmt'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->equalTo('m.module_guid'), $this->equalTo(array('123')))
      ->will($this->returnValue("m.module_guid = '123'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('modules', 'modulegroups')))
      ->will($this->returnValue($databaseResult));
    $list = new PapayaPluginList();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load('123'));
    $this->assertAttributeEquals(
      array(
        '123' => array(
          'guid' => '123',
          'class' => 'SampleClass',
          'path' => '/Sample/Path',
          'file' => 'SampleFile.php',
          'prefix' => 'SamplePrefix',
        )
      ),
      '_records',
      $list
    );
  }

  /**
  * @covers PapayaPluginList::load
  */
  public function testLoadWithAlreadyLoadedGuid() {
    $list = new PapayaPluginList_TestProxy();
    $list->_records = array(
      '123' => array(
        'guid' => '123',
        'class' => 'SampleClass',
        'path' => '/Sample/Path',
        'file' => 'SampleFile.php',
        'prefix' => 'SamplePrefix',
      )
    );
    $this->assertTrue($list->load(array('123')));
  }

  /**
  * @covers PapayaPluginList::load
  */
  public function testLoadWithSqlErrorExpectingFalse() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'queryFmt'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->equalTo('m.module_guid'), $this->equalTo(array('123')))
      ->will($this->returnValue("m.module_guid = '123'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('modules', 'modulegroups')))
      ->will($this->returnValue(FALSE));
    $list = new PapayaPluginList();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertFalse($list->load('123'));
  }
}

class PapayaPluginList_TestProxy extends PapayaPluginList {
  public $_records = array();
}