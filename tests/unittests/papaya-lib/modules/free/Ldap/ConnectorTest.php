<?php
require_once(substr(dirname(__FILE__), 0, -29).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleLdap' => PAPAYA_INCLUDE_PATH.'modules/free/Ldap'
  )
);

class PapayaModuleLdapConnectorTest extends PapayaTestCase {
  /**
  * @covers PapayaModuleLdapConnector::wrapper
  */
  public function testWrapperSet() {
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $this->assertSame($wrapper, $connector->wrapper($wrapper));
  }

  /**
  * @covers PapayaModuleLdapConnector::wrapper
  */
  public function testWrapperInitialize() {
    $connector = new PapayaModuleLdapConnector();
    $this->assertInstanceOf('PapayaModuleLdapWrapper', $connector->wrapper());
  }

  /**
  * @covers PapayaModuleLdapConnector::options
  */
  public function testOptions() {
    if (!defined('LDAP_OPT_PROTOCOL_VERSION')) {
      define('LDAP_OPT_PROTOCOL_VERSION', 17);
    }
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $options = $this->getMockBuilder('PapayaModuleLdapOptions')->getMock();
    $wrapper
      ->expects($this->once())
      ->method('options')
      ->with($this->equalTo($options))
      ->will($this->returnValue($options));
    $connector->wrapper($wrapper);
    $this->assertSame($options, $connector->options($options));
  }

  /**
  * @covers PapayaModuleLdapConnector::host
  */
  public function testHost() {
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $wrapper
      ->expects($this->once())
      ->method('host')
      ->with($this->equalTo('ldap.example.com'))
      ->will($this->returnValue('ldap.example.com'));
    $connector->wrapper($wrapper);
    $this->assertEquals('ldap.example.com', $connector->host('ldap.example.com'));
  }

  /**
  * @covers PapayaModuleLdapConnector::port
  */
  public function testPort() {
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $wrapper
      ->expects($this->once())
      ->method('port')
      ->with($this->equalTo(636))
      ->will($this->returnValue(636));
    $connector->wrapper($wrapper);
    $this->assertEquals(636, $connector->port(636));
  }

  /**
  * @covers PapayaModuleLdapConnector::bind
  */
  public function testBind() {
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $wrapper
      ->expects($this->once())
      ->method('bind')
      ->with(
          $this->equalTo('cn=user,dc=example,dc=com'),
          $this->equalTo('password')
        )
      ->will($this->returnValue(TRUE));
    $connector->wrapper($wrapper);
    $this->assertTrue($connector->bind('cn=user,dc=example,dc=com', 'password'));
  }

  /**
  * @covers PapayaModuleLdapConnector::search
  */
  public function testSearch() {
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $wrapper
      ->expects($this->once())
      ->method('search')
      ->with(
          $this->equalTo('dc=example,dc=com'),
          $this->equalTo('(objectClass=*)'),
          $this->equalTo(array('dn'))
        )
      ->will($this->returnValue(array('dn' => 'cn=dummy,dc=example,dc=com')));
    $connector->wrapper($wrapper);
    $this->assertEquals(
      array('dn' => 'cn=dummy,dc=example,dc=com'),
      $connector->search('dc=example,dc=com', '(objectClass=*)', array('dn'))
    );
  }

  /**
  * @covers PapayaModuleLdapConnector::add
  */
  public function testAdd() {
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $wrapper
      ->expects($this->once())
      ->method('add')
      ->with(
          $this->equalTo('cn=newuser,dc=example,dc=com'),
          $this->equalTo(array('cn' => 'newuser', 'sn' => 'New'))
        )
      ->will($this->returnValue(TRUE));
    $connector->wrapper($wrapper);
    $this->assertTrue(
      $connector->add(
        'cn=newuser,dc=example,dc=com',
        array('cn' => 'newuser', 'sn' => 'New')
      )
    );
  }

  /**
  * @covers PapayaModuleLdapConnector::modify
  */
  public function testModify() {
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $wrapper
      ->expects($this->once())
      ->method('modify')
      ->with(
          $this->equalTo('cn=newuser,dc=example,dc=com'),
          $this->equalTo(array('sn' => 'Modified'))
        )
      ->will($this->returnValue(TRUE));
    $connector->wrapper($wrapper);
    $this->assertTrue(
      $connector->modify(
        'cn=newuser,dc=example,dc=com',
        array('sn' => 'Modified')
      )
    );
  }

  /**
  * @covers PapayaModuleLdapConnector::delete
  */
  public function testDelete() {
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $wrapper
      ->expects($this->once())
      ->method('delete')
      ->with($this->equalTo('cn=newuser,dc=example,dc=com'))
      ->will($this->returnValue(TRUE));
    $connector->wrapper($wrapper);
    $this->assertTrue(
      $connector->delete('cn=newuser,dc=example,dc=com')
    );
  }

  /**
  * @covers PapayaModuleLdapConnector::getLastError
  */
  public function testGetLastError() {
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $wrapper
      ->expects($this->once())
      ->method('getLastError')
      ->will($this->returnValue(array(49, 'Invalid credentials')));
    $connector->wrapper($wrapper);
    $this->assertEquals(
      array(49, 'Invalid credentials'),
      $connector->getLastError()
    );
  }

  /**
  * @covers PapayaModuleLdapConnector::hashPassword
  */
  public function testHashPassword() {
    $connector = new PapayaModuleLdapConnector();
    $wrapper = $this->getMockBuilder('PapayaModuleLdapWrapper')->getMock();
    $wrapper
      ->expects($this->once())
      ->method('hashPassword')
      ->with($this->equalTo('test'))
      ->will($this->returnValue('{SSHA}UnEBzP7Obi2cvt+g0KRuFm216VbCzwgE'));
    $connector->wrapper($wrapper);
    $this->assertEquals(
      '{SSHA}UnEBzP7Obi2cvt+g0KRuFm216VbCzwgE',
      $connector->hashPassword('test')
    );
  }
}
