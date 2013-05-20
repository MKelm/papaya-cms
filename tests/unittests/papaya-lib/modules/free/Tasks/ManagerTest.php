<?php
require_once(substr(__FILE__, 0, -46).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
PapayaAutoloader::registerPath('PapayaModuleTasks', PAPAYA_INCLUDE_PATH.'modules/free/Tasks/');

require_once(PAPAYA_INCLUDE_PATH.'modules/free/Tasks/Manager.php');

class PapayaModuleTasksManagerTest extends PapayaTestCase {

  /**
  * @covers PapayaModuleTasksManager::createTaskObject
  */
  public function testCreateTaskObject() {
    $manager = new PapayaModuleTasksManager();
    $this->assertInstanceOf(
      'PapayaModuleTasksItem', $manager->createTaskObject()
    );
  }

  /**
  * @covers PapayaModuleTasksManager::createTaskList
  */
  public function testCreateTaskList() {
    $manager = new PapayaModuleTasksManager();
    $this->assertInstanceOf(
      'PapayaModuleTasksList', $manager->createTaskList()
    );
  }

  /**
  * @covers PapayaModuleTasksManager::add
  */
  public function testAdd() {
    $item = $this->getMock('PapayaModuleTasksItem');
    $item
      ->expects($this->exactly(4))
      ->method('offsetSet')
      ->with(
        $this->isType('string'),
        $this->logicalOr($this->isType('string'), $this->isType('array'))
      );
    $item
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue('234567abc'));
    $manager = new PapayaModuleTasksManager_TestProxy();
    $manager->taskItem = $item;
    $this->assertEquals(
      '234567abc',
      $manager->add('Title', 'Description', '123456789012345678901234567890ab', array())
    );
  }

  /**
  * @covers PapayaModuleTasksManager::get
  */
  public function testGet() {
    $item = $this->getMock('PapayaModuleTasksItem');
    $item
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo('234567abc'))
      ->will($this->returnValue(TRUE));
    $manager = new PapayaModuleTasksManager_TestProxy();
    $manager->taskItem = $item;
    $this->assertSame(
      $item, $manager->get('234567abc')
    );
  }

  /**
  * @covers PapayaModuleTasksManager::get
  */
  public function testGetExpectingNull() {
    $item = $this->getMock('PapayaModuleTasksItem');
    $item
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo('234567abc'))
      ->will($this->returnValue(FALSE));
    $manager = new PapayaModuleTasksManager_TestProxy();
    $manager->taskItem = $item;
    $this->assertNull(
      $manager->get('234567abc')
    );
  }

  /**
  * @covers PapayaModuleTasksManager::confirm
  */
  public function testConfirm() {
    $manager = new PapayaModuleTasksManager_TestProxy();
    $manager->taskItem = $this->getTaskItemFixture(
      array(
        'id' => '234567abc',
        'title' => 'Sample Title',
        'description' => 'Sample Description',
        'guid' => '123456789012345678901234567890ab',
        'data' => array('sample' => 'foobar')
      ),
      PapayaModuleTasksItem::TASK_CONFIRMED
    );
    $manager->papaya(
      $this->getMockApplicationObject(
        array(
          'Plugins' => $this->getPluginLoaderFixture(PapayaModuleTasksItem::TASK_CONFIRMED, TRUE)
        )
      )
    );
    $this->assertTrue(
      $manager->confirm('234567abc')
    );
  }

  /**
  * @covers PapayaModuleTasksManager::confirm
  */
  public function testConfirmFailedInPlugin() {
    $manager = new PapayaModuleTasksManager_TestProxy();
    $manager->taskItem = $this->getTaskItemFixture(
      array(
        'id' => '234567abc',
        'title' => 'Sample Title',
        'description' => 'Sample Description',
        'guid' => '123456789012345678901234567890ab',
        'data' => array('sample' => 'foobar')
      )
    );
    $manager->papaya(
      $this->getMockApplicationObject(
        array(
          'Plugins' => $this->getPluginLoaderFixture(PapayaModuleTasksItem::TASK_CONFIRMED, FALSE)
        )
      )
    );
    $this->assertFalse(
      $manager->confirm('234567abc')
    );
  }

  /**
  * @covers PapayaModuleTasksManager::confirm
  */
  public function testConfirmWithEmptyGuid() {
    $manager = new PapayaModuleTasksManager_TestProxy();
    $manager->taskItem = $this->getTaskItemFixture(
      array(
        'id' => '234567abc',
        'title' => 'Sample Title',
        'description' => 'Sample Description',
        'guid' => '',
        'data' => array('sample' => 'foobar')
      ),
      PapayaModuleTasksItem::TASK_CONFIRMED
    );
    $this->assertTrue(
      $manager->confirm('234567abc')
    );
  }

  /**
  * @covers PapayaModuleTasksManager::decline
  */
  public function testDecline() {
    $manager = new PapayaModuleTasksManager_TestProxy();
    $manager->taskItem = $this->getTaskItemFixture(
      array(
        'id' => '234567abc',
        'title' => 'Sample Title',
        'description' => 'Sample Description',
        'guid' => '123456789012345678901234567890ab',
        'data' => array('sample' => 'foobar')
      ),
      PapayaModuleTasksItem::TASK_DECLINED
    );
    $manager->papaya(
      $this->getMockApplicationObject(
        array(
          'Plugins' => $this->getPluginLoaderFixture(PapayaModuleTasksItem::TASK_DECLINED, TRUE)
        )
      )
    );
    $this->assertTrue(
      $manager->decline('234567abc')
    );
  }

  /**
  * @covers PapayaModuleTasksManager::decline
  */
  public function testDeclineFailedInPlugin() {
    $manager = new PapayaModuleTasksManager_TestProxy();
    $manager->taskItem = $this->getTaskItemFixture(
      array(
        'id' => '234567abc',
        'title' => 'Sample Title',
        'description' => 'Sample Description',
        'guid' => '123456789012345678901234567890ab',
        'data' => array('sample' => 'foobar')
      )
    );
    $manager->papaya(
      $this->getMockApplicationObject(
        array(
          'Plugins' => $this->getPluginLoaderFixture(PapayaModuleTasksItem::TASK_DECLINED, FALSE)
        )
      )
    );
    $this->assertFalse(
      $manager->decline('234567abc')
    );
  }

  /**
  * @covers PapayaModuleTasksManager::decline
  */
  public function testDeclineWithEmptyGuid() {
    $manager = new PapayaModuleTasksManager_TestProxy();
    $manager->taskItem = $this->getTaskItemFixture(
      array(
        'id' => '234567abc',
        'title' => 'Sample Title',
        'description' => 'Sample Description',
        'guid' => '',
        'data' => array('sample' => 'foobar')
      ),
      PapayaModuleTasksItem::TASK_DECLINED
    );
    $this->assertTrue(
      $manager->decline('234567abc')
    );
  }

  /**
  * @covers PapayaModuleTasksManager::delete
  */
  public function testDelete() {
    $manager = new PapayaModuleTasksManager_TestProxy();
    $manager->taskList = $this->getMock('PapayaModuleTasksList');
    $manager
      ->taskList
      ->expects($this->once())
      ->method('delete')
      ->with('sample_id')
      ->will($this->returnValue(TRUE));
    $this->assertTrue($manager->delete('sample_id'));
  }

  /**
  * @covers PapayaModuleTasksManager::getList
  */
  public function testGetList() {
    $manager = new PapayaModuleTasksManager_TestProxy();
    $list = $this->getMock('PapayaModuleTasksList', array('load'));
    $list
      ->expects($this->once())
      ->method('load')
      ->with(21, 42, array(), 'desc')
      ->will($this->returnValue(TRUE));
    $manager->taskList = $list;
    $this->assertSame(
      $list, $manager->getList(21, 42, array())
    );
  }

  /**
  * @covers PapayaModuleTasksManager::getList
  */
  public function testGetListExpectingFalse() {
    $manager = new PapayaModuleTasksManager_TestProxy();
    $list = $this->getMock('PapayaModuleTasksList');
    $list
      ->expects($this->once())
      ->method('load')
      ->with(NULL, NULL)
      ->will($this->returnValue(FALSE));
    $manager->taskList = $list;
    $this->assertNull(
      $manager->getList()
    );
  }


  /**
  * @covers PapayaModuleTasksManager::getTaskPlugin
  */
  public function testGetTaskPlugin() {
    $plugin = new stdClass();
    $loader = $this->getMock('PapayaPluginLoader', array('getPluginInstance'));
    $loader
      ->expects($this->once())
      ->method('getPluginInstance')
      ->with($this->equalTo('123456789012345678901234567890ab'), $this->isType('object'))
      ->will($this->returnValue($plugin));
    $manager = new PapayaModuleTasksManager();
    $manager->papaya($this->getMockApplicationObject(array('Plugins' => $loader)));
    $this->assertSame(
      $plugin, $manager->getTaskPlugin('123456789012345678901234567890ab')
    );
  }

  public function getTaskItemFixture($data, $status = NULL) {
    $this->testDataTaskItem = $data;
    $item = $this->getMock(
      'PapayaModuleTasksItem', array('load', 'save', 'offsetGet', 'offsetSet')
    );
    $item
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo('234567abc'))
      ->will($this->returnValue(TRUE));
    $item
      ->expects($this->any())
      ->method('offsetGet')
      ->with($this->isType('string'))
      ->will($this->returnCallback(array($this, 'callbackItemData')));
    if (isset($status)) {
      $item
        ->expects($this->once())
        ->method('offsetSet')
        ->with($this->equalTo('status'), $this->equalTo($status))
        ->will($this->returnArgument(1));
      $item
        ->expects($this->once())
        ->method('save')
        ->will($this->returnValue(TRUE));
    }
    return $item;
  }

  public function callbackItemData($name) {
    $this->assertContains($name, array_keys($this->testDataTaskItem));
    return $this->testDataTaskItem[$name];
  }

  public function getPluginLoaderFixture($mode, $saved) {
    $plugin = $this->getMock('PapayaObject', array('confirmTask', 'declineTask'));
    $plugin
      ->expects($this->once())
      ->method($mode == PapayaModuleTasksItem::TASK_CONFIRMED ? 'confirmTask' : 'declineTask')
      ->with($this->equalTo(array('sample' => 'foobar')))
      ->will($this->returnValue($saved));
    $loader = $this->getMock('PapayaPluginLoader', array('getPluginInstance'));
    $loader
      ->expects($this->once())
      ->method('getPluginInstance')
      ->with($this->equalTo('123456789012345678901234567890ab'), $this->isType('object'))
      ->will($this->returnValue($plugin));
    return $loader;
  }
}

/**
* Override the createTaskItem method to allow the injection of a mock object
*/
class PapayaModuleTasksManager_TestProxy extends PapayaModuleTasksManager {

  public $taskItem = NULL;
  public $taskList = NULL;

  public function createTaskObject() {
    return $this->taskItem;
  }
  public function createTaskList() {
    return $this->taskList;
  }
}