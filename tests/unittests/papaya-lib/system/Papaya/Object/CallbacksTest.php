<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Object/Callbacks.php');

class PapayaObjectCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaObjectCallbacks::__construct
  * @covers PapayaObjectCallbacks::defineCallbacks
  */
  public function testContructor() {
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $this->assertEquals(23, $list->sample->defaultReturn);
  }

  /**
  * @covers PapayaObjectCallbacks::__construct
  * @covers PapayaObjectCallbacks::defineCallbacks
  */
  public function testConstructorWithoutDefinitionsExpectingException() {
    $this->setExpectedException(
      'LogicException',
      'No callback definitions provided.'
    );
    $list = new PapayaObjectCallbacks(array());
  }

  /**
  * @covers PapayaObjectCallbacks::__construct
  * @covers PapayaObjectCallbacks::defineCallbacks
  */
  public function testConstructorWithInvalidDefinitionsExpectingException() {
    $this->setExpectedException(
      'LogicException',
      'Method "blocker" does already exists and can not be defined as a callback.'
    );
    $list = new PapayaObjectCallbacks_TestProxy(array('blocker' => NULL));
  }

  /**
  * @covers PapayaObjectCallbacks::__isset
  */
  public function testMagicMethodIssetExpectingTrue() {
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $list->sample = 'substr';
    $this->assertTrue(isset($list->sample));
  }

  /**
  * @covers PapayaObjectCallbacks::__isset
  */
  public function testMagicMethodIssetExpectingFalse() {
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $this->assertFalse(isset($list->sample));
  }

  /**
  * @covers PapayaObjectCallbacks::__get
  * @covers PapayaObjectCallbacks::__set
  * @covers PapayaObjectCallbacks::validateName
  */
  public function testGetAfterSetWithNull() {
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $list->sample = 'substr';
    $list->sample = NULL;
    $this->assertNull($list->sample->callback);
    $this->assertEquals(23, $list->sample->defaultReturn);
  }

  /**
  * @covers PapayaObjectCallbacks::__get
  * @covers PapayaObjectCallbacks::__set
  * @covers PapayaObjectCallbacks::validateName
  */
  public function testGetAfterSetWithCallback() {
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $list->sample = 'substr';
    $this->assertEquals('substr', $list->sample->callback);
    $this->assertEquals(23, $list->sample->defaultReturn);
  }

  /**
  * @covers PapayaObjectCallbacks::__get
  * @covers PapayaObjectCallbacks::__set
  * @covers PapayaObjectCallbacks::validateName
  */
  public function testGetAfterSetWithPapayaObjectCallbackObject() {
    $callback = $this->getMock('PapayaObjectCallback');
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $list->sample = $callback;
    $this->assertSame($callback, $list->sample);
  }

  /**
  * @covers PapayaObjectCallbacks::__set
  * @covers PapayaObjectCallbacks::validateName
  */
  public function testGetWithInvalidValueExpectingException() {
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $this->setExpectedException(
      'LogicException',
      'Argument $callback must be an valid Callback or an instance of PapayaObjectCallback.'
    );
    $list->sample = new stdClass;
  }

  /**
  * @covers PapayaObjectCallbacks::__get
  * @covers PapayaObjectCallbacks::validateName
  */
  public function testGetWithInvalidNameExpectingException() {
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $this->setExpectedException(
      'LogicException',
      'Invalid callback name: UNKNOWN.'
    );
    $list->UNKNOWN = NULL;
  }

  /**
  * @covers PapayaObjectCallbacks::__unset
  */
  public function testUnsetCreatesNewPapayaObjectCallbackObject() {
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $list->sample = 'substr';
    unset($list->sample);
    $this->assertNull($list->sample->callback);
  }

  /**
  * @covers PapayaObjectCallbacks::__call
  */
  public function testCallExecutesCallback() {
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $list->sample = array($this, 'callbackSample');
    $this->assertEquals(42, $list->sample(42));
  }

  public function callbackSample($context, $argument) {
    return $argument;
  }

  /**
  * @covers PapayaObjectCallbacks::__call
  */
  public function testCallWithInvalidNameExpectingException() {
    $list = new PapayaObjectCallbacks(array('sample' => 23));
    $this->setExpectedException(
      'LogicException',
      'Invalid callback name: UNKNOWN.'
    );
    $list->UNKNOWN();
  }
}

class PapayaObjectCallbacks_TestProxy extends PapayaObjectCallbacks {
  public function blocker() {
  }
}