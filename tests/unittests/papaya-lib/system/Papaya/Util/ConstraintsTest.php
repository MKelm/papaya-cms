<?php

require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Util/Constraints.php');

class PapayaUtilConstraintsTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilConstraints::assertArray
  */
  public function testAssertArray() {
    $this->assertTrue(
      PapayaUtilConstraints::assertArray(array())
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertArray
  * @dataProvider provideInvalidValuesForAssertArray
  */
  public function testAssertArrayFailureExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertArray($value);
  }

  /**
  * @covers PapayaUtilConstraints::assertArrayOrTraversable
  */
  public function testAssertArrayOrTraversableWithArray() {
    $this->assertTrue(
      PapayaUtilConstraints::assertArrayOrTraversable(array())
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertArrayOrTraversable
  */
  public function testAssertArrayOrTraversableWithTraversable() {
    $this->assertTrue(
      PapayaUtilConstraints::assertArrayOrTraversable(new ArrayIterator(array()))
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertArrayOrTraversable
  * @dataProvider provideInvalidValuesForAssertArrayOrTraversable
  */
  public function testAssertArrayOrTraversableFailureExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertArrayOrTraversable($value);
  }

  /**
  * @covers PapayaUtilConstraints::assertBoolean
  */
  public function testAssertBooleanWithTrue() {
    $this->assertTrue(
      PapayaUtilConstraints::assertBoolean(TRUE)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertBoolean
  */
  public function testAssertBooleanWithFalse() {
    $this->assertTrue(
      PapayaUtilConstraints::assertBoolean(FALSE)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertBoolean
  * @dataProvider provideInvalidValuesForAssertBoolean
  */
  public function testAssertBooleanFailureExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertBoolean($value);
  }

  /**
  * @covers PapayaUtilConstraints::assertCallable
  * @dataProvider provideValidValuesForAssertCallable
  */
  public function testAssertCallable($value) {
    $this->assertTrue(
      PapayaUtilConstraints::assertCallable($value)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertCallable
  */
  public function testAssertCallableWithMethod() {
    $this->assertTrue(
      PapayaUtilConstraints::assertCallable(array($this, 'testAssertCallableWithMethod'))
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertCallable
  * @dataProvider provideInvalidValuesForAssertCallable
  */
  public function testAssertCallableFailureExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertCallable($value);
  }

  /**
  * @covers PapayaUtilConstraints::assertFloat
  */
  public function testAssertFloat() {
    $this->assertTrue(
      PapayaUtilConstraints::assertFloat(42.21)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertFloat
  * @dataProvider provideInvalidValuesForAssertFloat
  */
  public function testAssertFloatFailureExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertFloat($value);
  }

  /**
  * @covers PapayaUtilConstraints::assertInstanceOf
  */
  public function testAssertInstanceOf() {
    $this->assertTrue(
      PapayaUtilConstraints::assertInstanceOf('stdClass', new stdClass)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertInstanceOf
  */
  public function testAssertInstanceOfWithSuperclass() {
    $this->assertTrue(
      PapayaUtilConstraints::assertInstanceOf('PapayaTestCase', $this)
    );
  }
  /**
  * @covers PapayaUtilConstraints::assertInstanceOf
  */
  public function testAssertInstanceOfFailureExpectingException() {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertInstanceOf('stdClass', $this);
  }

  /**
  * @covers PapayaUtilConstraints::assertInteger
  */
  public function testAssertInteger() {
    $this->assertTrue(
      PapayaUtilConstraints::assertInteger(42)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertInteger
  * @dataProvider provideInvalidValuesForAssertInteger
  */
  public function testAssertIntegerFailureExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertInteger($value);
  }

  /**
  * @covers PapayaUtilConstraints::assertNotEmpty
  * @dataProvider provideValidValuesForAssertNotEmpty
  */
  public function testAssertNotEmptyWithValidValues($value) {
    $this->assertTrue(
      PapayaUtilConstraints::assertNotEmpty($value)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertNotEmpty
  * @dataProvider provideInvalidValuesForAssertNotEmpty
  */
  public function testAssertNotEmptyWithInValidValuesExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertNotEmpty($value);
  }

  /**
  * @covers PapayaUtilConstraints::assertNotEmpty
  * @dataProvider provideInvalidValuesForAssertNotEmpty
  */
  public function testAssertNotEmptyWithInValidValuesExpectingExceptionIndividualMessage($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertNotEmpty($value, 'SAMPLE MESSAGE');
  }

  /**
  * @covers PapayaUtilConstraints::assertNumber
  */
  public function testAssertNumberWithInteger() {
    $this->assertTrue(
      PapayaUtilConstraints::assertNumber(42)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertNumber
  */
  public function testAssertNumberWithFloat() {
    $this->assertTrue(
      PapayaUtilConstraints::assertNumber(42.21)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertNumber
  * @dataProvider provideInvalidValuesForAssertNumber
  */
  public function testAssertNumberFailureExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertNumber($value);
  }

  /**
  * @covers PapayaUtilConstraints::assertObject
  */
  public function testAssertObject() {
    $this->assertTrue(
      PapayaUtilConstraints::assertObject(new stdClass)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertObject
  * @dataProvider provideInvalidValuesForAssertObject
  */
  public function testAssertObjectFailureExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertObject($value);
  }

  /**
  * @covers PapayaUtilConstraints::assertObjectOrNull
  */
  public function testAssertObjectOrNullWithObject() {
    $this->assertTrue(
      PapayaUtilConstraints::assertObjectOrNull(new stdClass)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertObjectOrNull
  */
  public function testAssertObjectOrNullWithNull() {
    $this->assertTrue(
      PapayaUtilConstraints::assertObjectOrNull(NULL)
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertObjectOrNull
  * @dataProvider provideInvalidValuesForAssertObjectOrNull
  */
  public function testAssertObjectOrNullFailureExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertObjectOrNull($value);
  }

  /**
  * @covers PapayaUtilConstraints::assertResource
  */
  public function testAssertResource() {
    $this->assertTrue(
      PapayaUtilConstraints::assertResource($fh = fopen('php://memory', 'rw'))
    );
    fclose($fh);
  }

  /**
  * @covers PapayaUtilConstraints::assertResource
  */
  public function testAssertResourceFailureExpectingException() {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertResource('');
  }

  /**
  * @covers PapayaUtilConstraints::assertString
  */
  public function testAssertString() {
    $this->assertTrue(
      PapayaUtilConstraints::assertString('')
    );
  }

  /**
  * @covers PapayaUtilConstraints::assertString
  * @dataProvider provideInvalidValuesForAssertString
  */
  public function testAssertStringFailureExpectingException($value) {
    $this->setExpectedException('UnexpectedValueException');
    PapayaUtilConstraints::assertString($value);
  }

  /**
  * @covers PapayaUtilConstraints::handleFailure
  */
  public function testHandleFailureWithScalar() {
    $this->setExpectedException(
      'UnexpectedValueException',
      'Unexpected value type: Expected "string" but "integer" given.'
    );
    PapayaUtilConstraints_TestProxy::handleFailure('string', 42, '');
  }

  /**
  * @covers PapayaUtilConstraints::handleFailure
  */
  public function testHandleFailureWithObject() {
    $this->setExpectedException(
      'UnexpectedValueException',
      'Unexpected value type: Expected "integer, float" but "stdClass" given.'
    );
    PapayaUtilConstraints_TestProxy::handleFailure('integer, float', new stdClass, '');
  }

  /**
  * @covers PapayaUtilConstraints::handleFailure
  */
  public function testHandleFailureWithIndividualMessage() {
    $this->setExpectedException(
      'UnexpectedValueException',
      'SAMPLE MESSAGE'
    );
    PapayaUtilConstraints_TestProxy::handleFailure('', new stdClass, 'SAMPLE MESSAGE');
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function provideInvalidValuesForAssertArray() {
    return array(
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertArrayOrTraversable() {
    return array(
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertBoolean() {
    return array(
      'array' => array(array()),
      'float' => array(1.1),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideValidValuesForAssertCallable() {
    return array(
      'function' => array('is_callable'),
      'class function' => array(array('PapayaUtilConstraints', 'assertCallable'))
    );
  }

  public static function provideInvalidValuesForAssertCallable() {
    return array(
      'array' => array(array()),
      'float' => array(1.1),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertFloat() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertInteger() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideValidValuesForAssertNotEmpty() {
    return array(
      'array' => array(array(1)),
      'boolean' => array(TRUE),
      'integer' => array(1),
      'object' => array(new stdClass),
      'string' => array('foo'),
    );
  }

  public static function provideInvalidValuesForAssertNotEmpty() {
    return array(
      'array' => array(array()),
      'boolean' => array(FALSE),
      'integer' => array(0),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertNumber() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'object' => array(new stdClass),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertObject() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'integer' => array(1),
      'string' => array(''),
      'NULL' => array(NULL)
    );
  }

  public static function provideInvalidValuesForAssertObjectOrNull() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'integer' => array(1),
      'string' => array('')
    );
  }

  public static function provideInvalidValuesForAssertString() {
    return array(
      'array' => array(array()),
      'boolean' => array(TRUE),
      'float' => array(1.1),
      'integer' => array(1),
      'object' => array(new stdClass),
      'NULL' => array(NULL)
    );
  }
}

class PapayaUtilConstraints_TestProxy extends PapayaUtilConstraints {

  public static function handleFailure($expected, $value, $message) {
    parent::handleFailure($expected, $value, $message);
  }
}