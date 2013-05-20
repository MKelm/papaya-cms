<?php
require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
PapayaTestCase::defineConstantDefaults(
  array(
    'PAPAYA_DB_TBL_AUTHOPTIONS',
    'PAPAYA_DB_TBL_AUTHUSER',
    'PAPAYA_DB_TBL_AUTHGROUPS',
    'PAPAYA_DB_TBL_AUTHLINK',
    'PAPAYA_DB_TBL_AUTHPERM',
    'PAPAYA_DB_TBL_AUTHMODPERMS',
    'PAPAYA_DB_TBL_AUTHMODPERMLINKS',
    'PAPAYA_DB_TBL_SURFER'
  )
);

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Dispatcher/Database.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Message/Logable.php');

class PapayaMessageDispatcherDatabaseTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDispatcherDatabase::dispatch
  * @covers PapayaMessageDispatcherDatabase::save
  */
  public function testDispatchExpectingTrue() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getTableName', 'insertRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with($this->equalTo('log'), $this->isTrue())
      ->will($this->returnValue('papaya_log'));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with($this->equalTo('papaya_log'), $this->isNull(), $this->isType('array'))
      ->will($this->returnValue(TRUE));
    $message = $this->getMock('PapayaMessageLogable');
    $message
      ->expects($this->once())
      ->method('getGroup')
      ->will($this->returnValue(PapayaMessageLogable::GROUP_SYSTEM));
    $message
      ->expects($this->exactly(2))
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::TYPE_INFO));
    $message
      ->expects($this->exactly(2))
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $message
      ->expects($this->exactly(2))
      ->method('context')
      ->will($this->returnValue($this->getMock('PapayaMessageContextGroup')));
    $dispatcher = new PapayaMessageDispatcherDatabase();
    $dispatcher->papaya(
      $this->getMockApplicationObject(
        array(
          'Options' => $this->getMockConfigurationObject(
            array(
              'PAPAYA_PROTOCOL_DATABASE' => TRUE
            )
          )
        )
      )
    );
    $dispatcher->setDatabaseAccess($databaseAccess);
    $this->assertTrue($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherDatabase::dispatch
  * @covers PapayaMessageDispatcherDatabase::save
  */
  public function testDispatchWithDebugMessageExpectingTrue() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getTableName', 'insertRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with($this->equalTo('log'), $this->isTrue())
      ->will($this->returnValue('papaya_log'));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with($this->equalTo('papaya_log'), $this->isNull(), $this->isType('array'))
      ->will($this->returnValue(TRUE));
    $user = $this->getMock('base_auth', array('isLoggedIn', 'getUserId', 'getDisplayName'));
    $user
      ->expects($this->once())
      ->method('isLoggedIn')
      ->will($this->returnValue(TRUE));
    $user
      ->expects($this->once())
      ->method('getUserId')
      ->will($this->returnValue('123'));
    $user
      ->expects($this->once())
      ->method('getDisplayName')
      ->will($this->returnValue('Sample User'));
    $message = $this->getMock('PapayaMessageLogable');
    $message
      ->expects($this->once())
      ->method('getGroup')
      ->will($this->returnValue(PapayaMessageLogable::GROUP_SYSTEM));
    $message
      ->expects($this->exactly(2))
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::TYPE_DEBUG));
    $message
      ->expects($this->exactly(2))
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $message
      ->expects($this->exactly(2))
      ->method('context')
      ->will($this->returnValue($this->getMock('PapayaMessageContextGroup')));
    $dispatcher = new PapayaMessageDispatcherDatabase();
    $dispatcher->papaya(
      $this->getMockApplicationObject(
        array(
          'AdministrationUser' => $user,
          'Options' => $this->getMockConfigurationObject(
            array(
              'PAPAYA_PROTOCOL_DATABASE' => TRUE,
              'PAPAYA_PROTOCOL_DATABASE_DEBUG' => TRUE
            )
          )
        )
      )
    );
    $dispatcher->setDatabaseAccess($databaseAccess);
    $this->assertTrue($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherDatabase::dispatch
  */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    $message = $this->getMock('PapayaMessage');
    $dispatcher = new PapayaMessageDispatcherDatabase();
    $this->assertFalse($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherDatabase::dispatch
  */
  public function testDispatchWithDebugMessageExpectingFalse() {
    $message = $this->getMock('PapayaMessageLogable');
    $message
      ->expects($this->once())
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::TYPE_DEBUG));
    $dispatcher = new PapayaMessageDispatcherDatabase();
    $dispatcher->papaya(
      $this->getMockApplicationObject(
        array(
          'Options' => $this->getMockConfigurationObject(
            array(
              'PAPAYA_PROTOCOL_DATABASE' => TRUE,
              'PAPAYA_PROTOCOL_DATABASE_DEBUG' => FALSE
            )
          )
        )
      )
    );
    $this->assertFalse($dispatcher->dispatch($message));
  }

  /**
  * @covers PapayaMessageDispatcherDatabase::save
  */
  public function testDispatchPreventMessageRecursionDefault() {
    $dispatcher = new PapayaMessageDispatcherDatabase();
    $this->assertAttributeEquals(
      FALSE,
      '_preventMessageRecursion',
      $dispatcher
    );
  }

  /**
  * @covers PapayaMessageDispatcherDatabase::save
  */
  public function testDispatchWithRecursion() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getTableName', 'insertRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->never())
      ->method('getTableName');
    $databaseAccess
      ->expects($this->never())
      ->method('insertRecord');
    $message = $this->getMock('PapayaMessageLogable');
    $message
      ->expects($this->once())
      ->method('getGroup')
      ->will($this->returnValue(PapayaMessageLogable::GROUP_SYSTEM));
    $message
      ->expects($this->exactly(2))
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::TYPE_INFO));
    $message
      ->expects($this->exactly(2))
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $message
      ->expects($this->exactly(2))
      ->method('context')
      ->will($this->returnValue($this->getMock('PapayaMessageContextGroup')));
    $dispatcher = new PapayaMessageDispatcherDatabaseProxy();
    $dispatcher->papaya(
      $this->getMockApplicationObject(
        array(
          'Options' => $this->getMockConfigurationObject(
            array(
              'PAPAYA_PROTOCOL_DATABASE' => TRUE
            )
          )
        )
      )
    );
    $dispatcher->setDatabaseAccess($databaseAccess);
    $this->assertFalse($dispatcher->dispatch($message));
    $this->assertAttributeEquals(
      TRUE,
      '_preventMessageRecursion',
      $dispatcher
    );
  }

  /**
  * @covers PapayaMessageDispatcherDatabase::allow
  * @dataProvider allowDataProvider
  *
  * @param boolean $expected
  * @param integer $type
  * @param boolean $dispatcherActive
  * @param boolean $dispatcherHandleDebug
  */
  public function testAllow($expected, $type, $dispatcherActive, $dispatcherHandleDebug) {
    $message = $this->getMock('PapayaMessageLogable');
    $message
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue($type));
    $dispatcher = new PapayaMessageDispatcherDatabase();
    $dispatcher->papaya(
      $this->getMockApplicationObject(
        array(
          'Options' => $this->getMockConfigurationObject(
            array(
              'PAPAYA_PROTOCOL_DATABASE' => $dispatcherActive,
              'PAPAYA_PROTOCOL_DATABASE_DEBUG' => $dispatcherHandleDebug
            )
          )
        )
      )
    );
    $this->assertSame(
      $expected,
      $dispatcher->allow($message)
    );
  }

  public static function allowDataProvider() {
    return array(
      array(FALSE, PapayaMessage::TYPE_INFO, FALSE, FALSE),
      array(TRUE, PapayaMessage::TYPE_INFO, TRUE, FALSE),
      array(FALSE, PapayaMessage::TYPE_DEBUG, TRUE, FALSE),
      array(TRUE, PapayaMessage::TYPE_INFO, TRUE, TRUE),
      array(TRUE, PapayaMessage::TYPE_DEBUG, TRUE, TRUE),
      array(FALSE, PapayaMessage::TYPE_DEBUG, TRUE, FALSE)
    );
  }
}

class PapayaMessageDispatcherDatabaseProxy
  extends PapayaMessageDispatcherDatabase {
  /* change the default to what we want to test */
  protected $_preventMessageRecursion = TRUE;
}
