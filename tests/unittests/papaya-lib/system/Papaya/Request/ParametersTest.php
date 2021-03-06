<?php

require_once(substr(__FILE__, 0, -52).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parameters.php');

class PapayaRequestParametersTest extends PapayaTestCase {
  /**
  * @covers PapayaRequestParameters::toArray
  */
  public function testToArray() {
    $parameters = new PapayaRequestParameters();
    $parameters->merge(
      array('group' => array('foo' => 'bar'))
    );
    $this->assertEquals(
      array('group' => array('foo' => 'bar')),
      $parameters->toArray()
    );
  }

  /**
  * @covers PapayaRequestParameters::getGroup
  */
  public function testGetGroup() {
    $parameters = new PapayaRequestParameters();
    $parameters->merge(
      array('group' => array('foo' => 'bar'))
    );
    $this->assertInstanceOf(
      'PapayaRequestParameters',
      $group = $parameters->getGroup('group')
    );
    $this->assertEquals(
      array('foo' => 'bar'),
      (array)$group
    );
  }

  /**
  * @covers PapayaRequestParameters::set
  * @covers PapayaRequestParameters::_parseParameterName
  * @dataProvider setDataProvider
  */
  public function testSet($before, $parameter, $value, $expected) {
    $parameters = new PapayaRequestParameters();
    $parameters->merge($before);
    $parameters->set($parameter, $value, ':');
    $this->assertEquals(
      $expected,
      (array)$parameters
    );
  }

  /**
  * Set and parameter object
  * @covers PapayaRequestParameters::set
  */
  public function testSetWithObject() {
    $parametersFirst = new PapayaRequestParameters();
    $parametersSecond = new PapayaRequestParameters();
    $parametersFirst->merge(
       array('foo' => 'bar', 'group' => array('e1' => 'fail'))
    );
    $parametersSecond->merge(array('e2' => 'success'));
    $parametersFirst->set('group', $parametersSecond);
    $this->assertEquals(
      array('foo' => 'bar', 'group' => array('e2' => 'success')),
      (array)$parametersFirst
    );
  }

  /**
  * @covers PapayaRequestParameters::has
  * @dataProvider hasDataProviderExpectingTrue
  */
  public function testHasExpectingTrue($parameterName, $parameterData) {
    $parameters = new PapayaRequestParameters();
    $parameters->set($parameterData);
    $this->assertTrue($parameters->has($parameterName));
  }

  /**
  * @covers PapayaRequestParameters::has
  * @dataProvider hasDataProviderExpectingFalse
  */
  public function testHasExpectingFalse($parameterName, $parameterData) {
    $parameters = new PapayaRequestParameters();
    $parameters->set($parameterData);
    $this->assertFalse($parameters->has($parameterName));
  }

  /**
  * @covers PapayaRequestParameters::get
  * @dataProvider getDataProvider
  */
  public function testGet($name, $defaultValue, $expected) {
    $parameters = new PapayaRequestParameters();
    $parameters->merge(
      array(
        'string' => 'test',
        'integer' => '42',
        'float' => '42.21',
        'array' => array('1', '2', '3'),
        'group' => array(
          'element1' => 1,
          'element2' => 2
        )
      )
    );
    $this->assertSame(
      $expected,
      $parameters->get($name, $defaultValue)
    );
  }

  /**
  * @covers PapayaRequestParameters::remove
  * @dataProvider removeDataProvider
  */
  public function testRemove($before, $parameter, $expected) {
    $parameters = new PapayaRequestParameters();
    $parameters->merge($before);
    $parameters->remove($parameter);
    $this->assertEquals(
      $expected,
      (array)$parameters
    );
  }

  /**
  * @covers PapayaRequestParameters::getQueryString
  */
  public function testGetQueryString() {
    $parameters = new PapayaRequestParameters();
    $parameters->merge(array('group' => array('foo' => 'bar')));
    $this->assertEquals(
      'group/foo=bar',
      $parameters->getQueryString('/')
    );
  }

  /**
  * @covers PapayaRequestParameters::getList
  * @covers PapayaRequestParameters::flattenArray
  * @dataProvider getListDataProvider
  */
  public function testGetList($expected, $parameterArray, $separator) {
    $parameters = new PapayaRequestParameters($parameterArray);
    $this->assertEquals(
      $expected, $parameters->getList($separator)
    );
  }

  /**
  * @covers PapayaRequestParameters::prepareParameter
  * @dataProvider prepareParameterDataProvider
  */
  public function testPrepareParameter($value, $stripSlashes, $expected) {
    $parameters = new PapayaRequestParameters();
    $this->assertEquals(
      $expected,
      $parameters->prepareParameter($value, $stripSlashes)
    );
  }

  /**
  * @covers PapayaRequestParameters::offsetSet
  */
  public function testArrayAccessOffsetSet() {
    $parameters = new PapayaRequestParameters();
    $parameters['test'] = 'sample';
    $this->assertEquals(
      array('test' => 'sample'),
      (array)$parameters
    );
  }

  /**
  * @covers PapayaRequestParameters::offsetExists
  */
  public function testArrayAccessOffsetExistsExpectingTrue() {
    $parameters = new PapayaRequestParameters(array('foo' => 'bar'));
    $this->assertTrue(
      isset($parameters['foo'])
    );
  }

  /**
  * @covers PapayaRequestParameters::offsetExists
  */
  public function testArrayAccessOffsetExistsExpectingFalse() {
    $parameters = new PapayaRequestParameters();
    $this->assertFalse(
      isset($parameters['foo'])
    );
  }

  /**
  * @covers PapayaRequestParameters::offsetUnset
  */
  public function testArrayAccessOffsetUnset() {
    $parameters = new PapayaRequestParameters(array('foo' => 'bar'));
    unset($parameters['foo']);
    $this->assertEquals(
      array(),
      (array)$parameters
    );
  }

  /**
  * @covers PapayaRequestParameters::offsetGet
  */
  public function testArrayAccessOffsetGet() {
    $parameters = new PapayaRequestParameters(array('foo' => 'bar'));
    $this->assertEquals(
      'bar', $parameters['foo']
    );
  }

  /**
  * @covers PapayaRequestParameters::offsetGet
  */
  public function testArrayAccessOffsetGetWithArray() {
    $parameters = new PapayaRequestParameters(array('foo' => array('bar' => 'foobar')));
    $this->assertInstanceOf(
      'PapayaRequestParameters', $parameters['foo']
    );
    $this->assertEquals(
      'foobar', $parameters['foo']['bar']
    );
  }

  /**
  * @covers PapayaRequestParameters::count
  */
  public function testCountable() {
    $parameters = new PapayaRequestParameters(array('foo' => 'bar', 'bar' => 'foo'));
    $this->assertEquals(
      2, count($parameters)
    );
  }

  /**
  * @covers PapayaRequestParameters::isEmpty
  */
  public function testIsEmptyExpectingTrue() {
    $parameters = new PapayaRequestParameters();
    $this->assertTrue(
      $parameters->isEmpty()
    );
  }

  /**
  * @covers PapayaRequestParameters::isEmpty
  */
  public function testIsEmptyExpectingFalse() {
    $parameters = new PapayaRequestParameters(array('foo' => 'bar'));
    $this->assertFalse(
      $parameters->isEmpty()
    );
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function setDataProvider() {
    return array(
      array(
        array(),
        'foo',
        'bar',
        array(
          'foo' => 'bar'
        )
      ),
      array(
        array(),
        array('foo' => 'bar'),
        NULL,
        array('foo' => 'bar')
      ),
      array(
        array(),
        array('foo' => 'bar', 'bar' => 'foo'),
        NULL,
        array('foo' => 'bar', 'bar' => 'foo')
      ),
      array(
        array('foo' => 'bar'),
        array('bar' => 'foo'),
        NULL,
        array('foo' => 'bar', 'bar' => 'foo')
      ),
      array(
        array('group' => array('e1' => 1)),
        'group[e2]',
        2,
        array('group' => array('e1' => 1, 'e2' => 2))
      ),
      array(
        array('list' => array(5 => 'foo')),
        'list[]',
        'bar',
        array('list' => array(5 => 'foo', 6 => 'bar')),
      ),
      array(
        array(),
        'foo:bar',
        2,
        array('foo' => array('bar' => 2))
      ),
      array(
        array(),
        'foo:bar',
        2,
        array('foo' => array('bar' => 2))
      ),
      array(
        array(),
        'foo!bar',
        2,
        array('foo' => array('bar' => 2))
      )
    );
  }

  public static function getDataProvider() {
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
        array('group[element2]', 0, 2),
      'no-existing group' =>
        array('integer[element2]', 1, 1)
    );
  }

  public static function hasDataProviderExpectingTrue() {
    return array(
      array(
        'foo',
        array('foo' => 'bar')
      ),
      array(
        'foo/bar',
        array('foo[bar]' => 'bar')
      ),
      array(
        'foo[bar]',
        array('foo[bar]' => 'bar')
      )
    );
  }

  public static function hasDataProviderExpectingFalse() {
    return array(
      array(
        'foo',
        array()
      ),
      array(
        'foo/bar',
        array('foo[foobar]' => 'bar')
      ),
      array(
        'foo[bar]',
        array('foo[foobar]' => 'bar')
      )
    );
  }

  public static function removeDataProvider() {
    return array(
      'simple' => array(
        array('foo' => 1, 'bar' => 2),
        'foo',
        array('bar' => 2)
      ),
      'empty name' => array(
        array('foo' => 1, 'bar' => 2),
        '',
        array('foo' => 1, 'bar' => 2)
      ),
      'no-existing param' => array(
        array('bar' => 2),
        'foo',
        array('bar' => 2)
      ),
      'level param' => array(
        array('foo' => array('bar1' => 1, 'bar2' => 2)),
        'foo[bar1]',
        array('foo' => array('bar2' => 2))
      ),
      'no-existing level param' => array(
        array('foo' => 'bar'),
        'foo[bar][foobar]',
        array('foo' => 'bar'),
      ),
      'multiple parameters' => array(
        array('foo' => 1, 'bar' => 2),
        array('foo', 'bar'),
        array()
      )
    );
  }

  public static function prepareParameterDataProvider() {
    return array(
      array('foo', FALSE, 'foo'),
      array(array('foo' => 'bar'), FALSE, array('foo' => 'bar')),
      array(array('foo' => TRUE), FALSE, array('foo' => TRUE)),
      array('\\x', TRUE, 'x'),
      array('\\x', FALSE, '\\x')
    );
  }

  public static function getListDataProvider() {
    return array(
      'simple list' => array(
        array('foo' => 'bar'),
        array('foo' => 'bar'),
        '[]'
      ),
      'parameter group' => array(
        array('foo[bar]' => 'foobar'),
        array('foo' => array('bar' => 'foobar')),
        '[]'
      ),
      'parameter group with list' => array(
        array(
          'foo[bar][foobar][0]' => '21',
          'foo[bar][foobar][1]' => '42'
        ),
        array('foo' => array('bar' => array('foobar' => array(21, 42)))),
        '[]'
      ),
      'parameter group with defined separator' => array(
        array('foo/bar' => 'foobar'),
        array('foo' => array('bar' => 'foobar')),
        '/'
      ),
      'parameter group with numeric keys' => array(
        array(
          'foo/21' => 'foo 21',
          'foo/42' => 'foo 42'
        ),
        array('foo' => array(21 => 'foo 21', 42 => 'foo 42')),
        '/'
      ),
    );
  }
}