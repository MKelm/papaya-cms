<?php
require_once(substr(dirname(__FILE__), 0, -29).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader(
  array(
    'PapayaModuleLdap' => PAPAYA_INCLUDE_PATH.'modules/free/Ldap'
  )
);

class PapayaModuleLdapOptionsTest extends PapayaTestCase {
  /**
  * Set up the test environment {
  */
  public function setUp() {
    if (!defined('LDAP_OPT_PROTOCOL_VERSION')) {
      define('LDAP_OPT_PROTOCOL_VERSION', 17);
    }
    if (!defined('LDAP_OPT_CLIENT_CONTROLS')) {
      define('LDAP_OPT_CLIENT_CONTROLS', 19);
    }
    if (!defined('LDAP_OPT_HOST_NAME')) {
      define('LDAP_OPT_HOST_NAME', 48);
    }
    if (!defined('LDAP_OPT_ERROR_NUMBER')) {
      define('LDAP_OPT_ERROR_NUMBER', 49);
    }
  }

  /**
  * @covers PapayaModuleLdapOptions::__construct
  */
  public function testConstruct() {
    $options = new PapayaModuleLdapOptions(
      array(LDAP_OPT_ERROR_NUMBER => 3)
    );
    $this->assertEquals(3, $options[LDAP_OPT_ERROR_NUMBER]);
  }

  /**
  * @covers PapayaModuleLdapOptions::offsetExists
  */
  public function testOffsetExistsWithExistingOption() {
    $options = new PapayaModuleLdapOptions();
    $this->assertTrue(isset($options[LDAP_OPT_PROTOCOL_VERSION]));
  }

  /**
  * @covers PapayaModuleLdapOptions::offsetExists
  */
  public function testOffsetExistsWithInvalidOption() {
    $options = new PapayaModuleLdapOptions();
    $this->assertFalse(isset($options[-1234]));
  }

  /**
  * @covers PapayaModuleLdapOptions::offsetGet
  */
  public function testOffsetGetExpectingInvalidArgumentException() {
    $options = new PapayaModuleLdapOptions();
    try {
      $options[-1234];
      $this->fail('Expected InvalidArgumentException not thrown.');
    } catch(InvalidArgumentException $e) {
      $this->assertEquals('-1234 is not a valid LDAP option.', $e->getMessage());
    }
  }

  /**
  * @covers PapayaModuleLdapOptions::offsetGet
  */
  public function testOffsetGetWithExistingOption() {
    $options = new PapayaModuleLdapOptions();
    $this->assertEquals(3, $options[LDAP_OPT_PROTOCOL_VERSION]);
  }

  /**
  * @covers PapayaModuleLdapOptions::offsetSet
  */
  public function testOffsetSetExpectingInvalidArgumentException() {
    $options = new PapayaModuleLdapOptions();
    try {
      $options[-1234] = 1;
      $this->fail('Expected InvalidArgumentExceptions not thrown.');
    } catch(InvalidArgumentException $e) {
      $this->assertEquals('-1234 is not a valid LDAP option.', $e->getMessage());
    }
  }

  /**
  * @covers PapayaModuleLdapOptions::offsetSet
  */
  public function testOffsetSetWithValidOption() {
    $options = new PapayaModuleLdapOptions();
    $options[LDAP_OPT_HOST_NAME] = 'example.com';
    $this->assertEquals('example.com', $options[LDAP_OPT_HOST_NAME]);
  }

  /**
  * @covers PapayaModuleLdapOptions::offsetUnset
  */
  public function testOffsetUnsetExpectingInvalidArgumentException() {
    $options = new PapayaModuleLdapOptions();
    try {
      unset($options[-1234]);
      $this->fail('Expected InvalidArgumentException not thrown.');
    } catch(InvalidArgumentException $e) {
      $this->assertEquals('-1234 is not a valid LDAP option.', $e->getMessage());
    }
  }

  /**
  * @covers PapayaModuleLdapOptions::offsetUnset
  */
  public function testOffsetUnsetWithExistingOption() {
    $options = new PapayaModuleLdapOptions();
    unset($options[LDAP_OPT_PROTOCOL_VERSION]);
    $this->assertNull($options[LDAP_OPT_PROTOCOL_VERSION]);
  }

  /**
  * @covers PapayaModuleLdapOptions::rewind
  * @covers PapayaModuleLdapOptions::current
  * @covers PapayaModuleLdapOptions::key
  * @covers PapayaModuleLdapOptions::next
  * @covers PapayaModuleLdapOptions::valid
  */
  public function testIterator() {
    $options = new PapayaModuleLdapOptions();
    $options[LDAP_OPT_PROTOCOL_VERSION] = 3;
    $options[LDAP_OPT_ERROR_NUMBER] = 3;
    foreach ($options as $key => $value) {
      $this->assertEquals(3, $value);
    }
  }

  /**
  * @covers PapayaModuleLdapOptions::count
  */
  public function testCount() {
    $options = new PapayaModuleLdapOptions(
      array(LDAP_OPT_ERROR_NUMBER => 3)
    );
    $this->assertEquals(2, count($options));
  }

  /**
  * @covers PapayaModuleLdapOptions::isPermittedOption
  */
  public function testIsPermittedOption() {
    $options = new PapayaModuleLdapOptions();
    $this->assertTrue($options->isPermittedOption(LDAP_OPT_ERROR_NUMBER));
  }

  /**
  * @covers PapayaModuleLdapOptions::getConstantValue
  */
  public function testGetConstantValue() {
    $options = new PapayaModuleLdapOptions_TestProxy();
    $this->assertEquals(
      LDAP_OPT_CLIENT_CONTROLS,
      $options->getConstantValue('LDAP_OPT_CLIENT_CONTROLS')
    );
  }
}

class PapayaModuleLdapOptions_TestProxy extends PapayaModuleLdapOptions {
  public function getConstantValue($offset) {
    return parent::getConstantValue($offset);
  }
}
