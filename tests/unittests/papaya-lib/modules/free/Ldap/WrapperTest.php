<?php
require_once(substr(dirname(__FILE__), 0, -29).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleLdap' => PAPAYA_INCLUDE_PATH.'modules/free/Ldap'
  )
);

class PapayaModuleLdapWrapperTest extends PapayaTestCase {
  /**
  * @covers PapayaModuleLdapWrapper::__construct
  */
  public function testConstruct() {
    $wrapper = new PapayaModuleLdapWrapper('ldap.example.com', 636);
    $this->assertEquals('ldap.example.com', $wrapper->host());
    $this->assertEquals(636, $wrapper->port());
  }

  /**
  * @covers PapayaModuleLdapWrapper::options
  */
  public function testOptionsSetObject() {
    $wrapper = new PapayaModuleLdapWrapper();
    $options = $this->getMockBuilder('PapayaModuleLdapOptions')->getMock();
    $this->assertSame($options, $wrapper->options($options));
  }

  /**
  * @covers PapayaModuleLdapWrapper::options
  */
  public function testOptionsSetArray() {
    $wrapper = new PapayaModuleLdapWrapper();
    if (!defined('LDAP_OPT_ERROR_NUMBER')) {
      define('LDAP_OPT_ERROR_NUMBER', 49);
    }
    if (!defined('LDAP_OPT_PROTOCOL_VERSION')) {
      define('LDAP_OPT_PROTOCOL_VERSION', 17);
    }
    $this->assertInstanceOf(
      'PapayaModuleLdapOptions',
      $wrapper->options(array(LDAP_OPT_ERROR_NUMBER => 3))
    );
  }

  /**
  * @covers PapayaModuleLdapWrapper::options
  */
  public function testOptionsSetExpectingInvalidArgumentException() {
    $wrapper = new PapayaModuleLdapWrapper();
    try {
      $wrapper->options('Invalid option type');
      $this->fail('Expected InvalidArgumentException not thrown.');
    } catch(InvalidArgumentException $e) {
      $this->assertEquals('PapayaModuleLdapOptions instance or array expected.', $e->getMessage());
    }
  }

  /**
  * @covers PapayaModuleLdapWrapper::options
  */
  public function testOptionsInitialize() {
    $wrapper = new PapayaModuleLdapWrapper();
    $this->assertInstanceOf('PapayaModuleLdapOptions', $wrapper->options());
  }

  /**
  * @covers PapayaModuleLdapWrapper::host
  */
  public function testHost() {
    $wrapper = new PapayaModuleLdapWrapper();
    $this->assertEquals('ldap.example.com', $wrapper->host('ldap.example.com'));
  }

  /**
  * @covers PapayaModuleLdapWrapper::port
  */
  public function testPort() {
    $wrapper = new PapayaModuleLdapWrapper();
    $this->assertEquals(8389, $wrapper->port(8389));
  }

  /**
  * @covers PapayaModuleLdapWrapper::bind
  */
  public function testBind() {
    $this->markTestSkipped();
  }

  /**
  * @covers PapayaModuleLdapWrapper::search
  */
  public function testSearch() {
    $wrapper = new PapayaModuleLdapWrapper();
    $this->assertEquals(
      array(),
      $wrapper->search('dc=example,dc=com', '(objectClass=*)')
    );
  }

  /**
  * @covers PapayaModuleLdapWrapper::add
  */
  public function testAdd() {
    $wrapper = new PapayaModuleLdapWrapper();
    $this->assertFalse(
      $wrapper->add(
        'cn=newuser,dc=example,dc=com',
        array('objectClass' => 'user', 'cn' => 'newuser', 'sn' => 'New')
      )
    );
  }

  /**
  * @covers PapayaModuleLdapWrapper::modify
  */
  public function testModify() {
    $wrapper = new PapayaModuleLdapWrapper();
    $this->assertFalse(
      $wrapper->modify('cn=newuser,dc=example,dc=com', array('sn' => 'Modified'))
    );
  }

  /**
  * @covers PapayaModuleLdapWrapper::delete
  */
  public function testDelete() {
    $wrapper = new PapayaModuleLdapWrapper();
    $this->assertFalse($wrapper->delete('cn=newuser,dc=example,dc=com'));
  }

  /**
  * @covers PapayaModuleLdapWrapper::hashPassword
  */
  public function testHashPassword() {
    $wrapper = new PapayaModuleLdapWrapper();
    $this->assertEquals(
      '{SSHA}',
      substr($wrapper->hashPassword('test'), 0, 6)
    );
  }

  /**
  * @covers PapayaModuleLdapWrapper::getLastError
  */
  public function testGetLastError() {
    $wrapper = new PapayaModuleLdapWrapper();
    $this->assertEquals(array(0, ''), $wrapper->getLastError());
  }

  /**
  * @covers PapayaModuleLdapWrapper::connect
  */
  public function testConnect() {
    $wrapper = new PapayaModuleLdapWrapper_TestProxy();
    $this->assertTrue($wrapper->connect());
  }
}

class PapayaModuleLdapWrapper_TestProxy extends PapayaModuleLdapWrapper {
  public $connection = TRUE;

  public function connect() {
    return parent::connect();
  }
}