<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaObjectParametersTest extends PapayaTestCase {

  /**
   * @covers PapayaObjectParameters::__construct
   */
  public function testConstructor() {
    $parameters = new PapayaObjectParameters(array('foo' => 'bar'));
    $this->assertEquals(
      array('foo' => 'bar'),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers PapayaObjectParameters::__construct
   */
  public function testConstructorWithRecursiveArray() {
    $parameters = new PapayaObjectParameters(array('foobar' => array('foo' => 'bar')));
    $this->assertEquals(
      array('foobar' => array('foo' => 'bar')),
      iterator_to_array($parameters)
    );
  }

  /**
  * @covers PapayaObjectParameters::merge
  */
  public function testMergeWithArray() {
    $parameters = new PapayaObjectParameters();
    $parameters->merge(array('foo' => 'bar'));
    $parameters->merge(array('bar' => 'foo'));
    $this->assertEquals(
      array(
        'foo' => 'bar',
        'bar' => 'foo'
      ),
      (array)$parameters
    );
  }

  /**
  * @covers PapayaObjectParameters::merge
  */
  public function testMergeWithInvalidArgument() {
    $parameters = new PapayaObjectParameters();
    $this->setExpectedException('UnexpectedValueException');
    $parameters->merge('foo');
  }

  /**
  * @covers PapayaObjectParameters::merge
  */
  public function testMergeWithObject() {
    $parametersFirst = new PapayaObjectParameters();
    $parametersSecond = new PapayaObjectParameters();
    $parametersFirst->merge(array('foo' => 'bar'));
    $parametersSecond->merge(array('bar' => 'foo'));
    $parametersFirst->merge($parametersSecond);
    $this->assertEquals(
      array(
        'foo' => 'bar',
        'bar' => 'foo'
      ),
      (array)$parametersFirst
    );
  }

  /**
  * @covers PapayaObjectParameters::assign
  */
  public function testAssignReplacesElements() {
    $parameters = new PapayaObjectParameters(
      array(
        'foo' => array(21),
        'bar' => ''
      )
    );
    $parameters->assign(array('foo' => array(42)));
    $this->assertEquals(
      array(
        'foo' => array(42),
        'bar' => ''
      ),
      iterator_to_array($parameters)
    );
  }

  /**
  * @covers PapayaObjectParameters::has
  */
  public function testHasExpectingTrue() {
    $parameters = new PapayaObjectParameters(array('foo' => 'bar'));
    $this->assertTrue($parameters->has('foo'));
  }

  /**
  * @covers PapayaObjectParameters::has
  */
  public function testHasExpectingFalse() {
    $parameters = new PapayaObjectParameters();
    $this->assertFalse($parameters->has('foo'));
  }

  /**
  * @covers PapayaObjectParameters::get
  * @dataProvider provideOffsetsAndDefaultValues
  */
  public function testGet($name, $defaultValue, $expected) {
    $parameters = new PapayaObjectParameters($this->getSampleArray());
    $this->assertSame(
      $expected,
      $parameters->get($name, $defaultValue)
    );
  }

  /**
  * @covers PapayaObjectParameters::get
  */
  public function testGetWithObjectDefaultValueExpectingParameterValue() {
    $defaultValue = new stdClass();
    $parameters = new PapayaObjectParameters();
    $parameters->merge(
      array(
        'sample' => 'success'
      )
    );
    $this->assertSame(
      'success',
      $parameters->get('sample', $defaultValue)
    );
  }

  /**
  * @covers PapayaObjectParameters::get
  */
  public function testGetWithObjectDefaultValueExpectingDefaultValue() {
    $defaultValue = $this->getMock('PapayaUiString', array('__toString'), array(' '));
    $defaultValue
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('success'));
    $parameters = new PapayaObjectParameters();
    $parameters->merge(
      array(
        'sample' => array('failed')
      )
    );
    $this->assertSame(
      'success',
      $parameters->get('sample', $defaultValue)
    );
  }

  /**
  * @covers PapayaObjectParameters::get
  */
  public function testGetWithFilter() {
    $filter = $this->getMock('PapayaFilter', array('filter', 'validate'));
    $filter
      ->expects($this->once('filter'))
      ->method('filter')
      ->with($this->equalTo('42'))
      ->will($this->returnValue(42));
    $parameters = new PapayaObjectParameters();
    $parameters->merge(
      array(
        'integer' => '42'
      )
    );
    $this->assertSame(
      42,
      $parameters->get('integer', 0, $filter)
    );
  }

  /**
  * @covers PapayaObjectParameters::get
  */
  public function testGetWithFilterExpectingDefaultValue() {
    $filter = $this->getMock('PapayaFilter', array('filter', 'validate'));
    $filter
      ->expects($this->once('filter'))
      ->method('filter')
      ->with($this->equalTo('42'))
      ->will($this->returnValue(NULL));
    $parameters = new PapayaObjectParameters();
    $parameters->merge(
      array(
        'integer' => '42'
      )
    );
    $this->assertSame(
      23,
      $parameters->get('integer', 23, $filter)
    );
  }

  /**
  * @covers PapayaObjectParameters::clear
  */
  public function testClear() {
    $parameters = new PapayaObjectParameters(array('foo' => 'bar'));
    $parameters->clear();
    $this->assertCount(0, $parameters);
  }

  /**
  * @covers PapayaObjectParameters::offsetExists
  */
  public function testIssetWithNonExistingOffsetExpectingFalse() {
    $parameters = new PapayaObjectParameters();
    $this->assertFalse(isset($parameters['foo']));
  }

  /**
   * @covers PapayaObjectParameters::offsetSet
   * @covers PapayaObjectParameters::offsetGet
   */
  public function testGetAfterSet() {
    $parameters = new PapayaObjectParameters();
    $parameters['foo'] = 'bar';
    $this->assertEquals('bar', $parameters['foo']);
  }

  /**
   * @covers PapayaObjectParameters::offsetGet
   */
  public function testGetNestedParameter() {
    $parameters = new PapayaObjectParameters(array('foo' => array('bar' => 42)));
    $this->assertEquals(42, $parameters['foo']['bar']);
  }

  /**
   * @covers PapayaObjectParameters::offsetGet
   */
  public function testOffsetGetNestedParameterUsingArrayOffset() {
    $parameters = new PapayaObjectParameters(array('foo' => array('bar' => 42)));
    $this->assertEquals(42, $parameters[array('foo', 'bar')]);
  }

  /**
   * @covers PapayaObjectParameters::offsetSet
   */
  public function testOffsetSetNestedParameterUsingArrayOffset() {
    $parameters = new PapayaObjectParameters();
    $parameters[array('foo', 'bar')] = 42;
    $this->assertEquals(
      array('foo' => array('bar' => 42)),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers PapayaObjectParameters::offsetSet
   */
  public function testOffsetSetNestedParameterUsingArrayOffsetWithSingleElement() {
    $parameters = new PapayaObjectParameters();
    $parameters[array('foo')] = 42;
    $this->assertEquals(
      array('foo' => 42),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers PapayaObjectParameters::offsetSet
   */
  public function testOffsetSetNestedParameterUsingEmptyKeys() {
    $parameters = new PapayaObjectParameters();
    $parameters[array('foo', 'bar', '', '')] = 42;
    $this->assertEquals(
      array('foo' => array('bar' => array(0 => array(0 => 42)))),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers PapayaObjectParameters::offsetSet
   */
  public function testOffsetSetNestedParameterOverridesExistingParameter() {
    $parameters = new PapayaObjectParameters(array('foobar' => array('foo' => 'bar')));
    $parameters[array('foobar', 'foo', 'bar')] = 42;
    $this->assertEquals(
      array('foobar' => array('foo' => array('bar' => 42))),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers PapayaObjectParameters::offsetSet
   */
  public function testOffsetSetNestedParameterOverridesExistingParameterWithNewArray() {
    $parameters = new PapayaObjectParameters(array('foobar' => 'bar'));
    $parameters[array('foobar', 'bar')] = 42;
    $this->assertEquals(
      array('foobar' => array('bar' => 42)),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers PapayaObjectParameters::offsetSet
   */
  public function testOffsetSetNestedParameterOverridesExistingParameterWithNewArrayAppend() {
    $parameters = new PapayaObjectParameters(array('foobar' => 'bar'));
    $parameters[array('foobar', '')] = 42;
    $this->assertEquals(
      array('foobar' => array(42)),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers PapayaObjectParameters::offsetSet
   */
  public function testOffsetSetWithTraversableAsValue() {
    $parameters = new PapayaObjectParameters();
    $parameters[] = new ArrayIterator(array(21, 42));
    $this->assertEquals(
      array(0 => array(21, 42)),
      iterator_to_array($parameters)
    );
  }

  /**
   * @covers PapayaObjectParameters::offsetGet
   * @dataProvider provideOffsetsAndValues
   */
  public function testOffsetGet($name, $expected) {
    $parameters = new PapayaObjectParameters($this->getSampleArray());
    $this->assertEquals(
      $expected,
      $parameters[$name]
    );
  }

  /**
   * @covers PapayaObjectParameters::offsetExists
   * @dataProvider provideExistingOffsets
   */
  public function testOffsetExistsExpectingTrue($name) {
    $parameters = new PapayaObjectParameters($this->getSampleArray());
    $this->assertTrue(isset($parameters[$name]));
  }

  /**
   * @covers PapayaObjectParameters::offsetExists
   * @dataProvider provideNonExistingOffsets
   */
  public function testOffsetExistsExpectingFalse($name) {
    $parameters = new PapayaObjectParameters($this->getSampleArray());
    $this->assertFalse(isset($parameters[$name]));
  }

  /**
   * @covers PapayaObjectParameters::offsetUnset
   * @dataProvider provideExistingOffsets
   */
  public function testOffsetUnset($name) {
    $parameters = new PapayaObjectParameters($this->getSampleArray());
    unset($parameters[$name]);
    $this->assertFalse(isset($parameters[$name]));
  }

  /**
   * @covers PapayaObjectParameters::offsetUnset
   * @dataProvider provideNonExistingOffsets
   */
  public function testOffsetUnsetWithNonExistingParameters($name) {
    $parameters = new PapayaObjectParameters($this->getSampleArray());
    unset($parameters[$name]);
    $this->assertFalse(isset($parameters[$name]));
  }

  /**
  * @covers PapayaObjectParameters::getChecksum
  */
  public function testGetChecksum() {
    $parameters = new PapayaObjectParameters(array('foo' => 'bar'));
    $this->assertEquals(
      '49a3696adf0fbfacc12383a2d7400d51', $parameters->getChecksum()
    );
  }

  /**
  * @covers PapayaObjectParameters::getChecksum
  */
  public function testGetChecksumNormalizesArray() {
    $parameters = new PapayaObjectParameters(array('foo' => 'bar', 'bar' => 42));
    $this->assertEquals(
      'e486614c5fe79b1235ead81bd5fc7292', $parameters->getChecksum()
    );
  }

  /*********************************
   * Fixtures
   ********************************/

  public function getSampleArray() {
    return array(
      'string' => 'test',
      'integer' => '42',
      'float' => '42.21',
      'array' => array('1', '2', '3'),
      'group' => array(
        'element1' => 1,
        'element2' => 2,
        'subgroup' => array(
          'subelement' => 3
        )
      )
    );
  }

  /*********************************
   * Data Provider
   ********************************/

  public static function provideOffsetsAndDefaultValues() {
    return array(
      'no-existing, return default value' =>
        array('NON_EXISTING', 'default', 'default'),
      'string, no default, return string value' =>
        array('integer', NULL, '42'),
      'array, no default, return array value' =>
        array('array', NULL, array('1', '2', '3')),
      'string default, return value' =>
        array('string', '', 'test'),
      'string, integer default, return typecasted value' =>
        array('integer', 0, 42),
      'string, float default, return typecasted value' =>
        array('float', (float)0, (float)42.21),
      'array, array default, return array value' =>
        array('array', array(), array('1', '2', '3')),
      'array, array default, return default' =>
        array('string', array('23'), array('23')),
      'array, integer default, return default' =>
        array('array', 1, 1),
      'sub element' =>
        array(array('group', 'element2'), 0, 2),
      'no-existing sub element' =>
        array(array('group', 'element99'), 0, 0),
      'no-existing group' =>
        array(array('integer', 'element2'), 1, 1)
    );
  }

  public static function provideOffsetsAndValues() {
    return array(
      'no-existing, return NULL' =>
        array('NON_EXISTING', NULL),
      'existing, return integer' =>
        array('integer', 42),
      'existing, return array' =>
        array('array', array('1', '2', '3')),
      'existing, return string' =>
        array('string', 'test'),
      'sub element' =>
        array(array('group', 'element2'), 2),
      'no-existing sub element' =>
        array(array('group', 'element99'), NULL),
      'no-existing group' =>
        array(array('integer', 'element2'), NULL)
    );
  }

  public static function provideExistingOffsets() {
    return array(
      array('integer'),
      array('array'),
      array('string'),
      array(array('group', 'element2')),
      array(array('group', 'subgroup', 'subelement'))
    );
  }

  public static function provideNonExistingOffsets() {
    return array(
      array('', NULL),
      array('NON_EXISTING', NULL),
      array(array('NON_EXISTING'), NULL),
      array(array('NON_EXISTING', 'NON_EXISTING'), NULL),
      array(array('group', 'element99'), NULL),
      array(array('group', 'element1', 'NON_EXISTING'), NULL),
      array(array('group', 'subgroup', 'subelement', 'NON_EXISTING'), NULL),
      array(array('integer', 'element2'), NULL)
    );
  }
}