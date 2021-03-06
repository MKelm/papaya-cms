<?php
require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaDatabaseConditionElementTest extends PapayaTestCase {

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testConstructor() {
    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $element = new PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertSame($group, $element->getParent());
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testConstructorWithOperator() {
    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $element = new PapayaDatabaseConditionElement_TestProxy($group, NULL, '=');
    $this->assertAttributeEquals('=', '_operator', $element);
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testGetDatabaseAccess() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $element = new PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertSame($databaseAccess, $element->getDatabaseAccess());
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testGetMapping() {
    $mapping = $this
      ->getMockBuilder('PapayaDatabaseInterfaceMapping')
      ->disableOriginalConstructor()
      ->getMock();
    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getMapping')
      ->will($this->returnValue($mapping));
    $element = new PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertSame($mapping, $element->getMapping());
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testGetMappingExpectingNull() {
    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $element = new PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertNull($element->getMapping());
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testMagicMethodToString() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->will($this->returnValue('sql string'));
    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $element = new PapayaDatabaseConditionElement_TestProxy($group, 'field');
    $this->assertEquals(
      'sql string', (string)$element
    );
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testMapFieldName() {
    $mapping = $this
      ->getMockBuilder('PapayaDatabaseInterfaceMapping')
      ->disableOriginalConstructor()
      ->getMock();
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('field')
      ->will($this->returnValue('mapped_field'));
    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getMapping')
      ->will($this->returnValue($mapping));
    $element = new PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertEquals('mapped_field', $element->mapFieldName('field'));
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testMapFieldNameWithoutMapping() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $element = new PapayaDatabaseConditionElement_TestProxy($group);
    $this->assertEquals('field', $element->mapFieldName('field'));
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testMapFieldNameWithInvalidMappingExpectingException() {
    $mapping = $this
      ->getMockBuilder('PapayaDatabaseInterfaceMapping')
      ->disableOriginalConstructor()
      ->getMock();
    $mapping
      ->expects($this->once())
      ->method('getField')
      ->with('field')
      ->will($this->returnValue(''));
    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getMapping')
      ->will($this->returnValue($mapping));
    $element = new PapayaDatabaseConditionElement_TestProxy($group);
    $this->setExpectedException('LogicException');
    $element->mapFieldName('field');
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testMapFieldNameWithEmptyFieldNameException() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $element = new PapayaDatabaseConditionElement_TestProxy($group);
    $this->setExpectedException('LogicException');
    $element->mapFieldName('');
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testGetSqlWithScalar() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          array(
            array(array('parent_field' => 21), NULL, '=', 'parent_field = 21')
          )
        )
      );

    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));

    $condition = new PapayaDatabaseConditionElement_TestProxy($group, 'parent_field', 21, '=');

    $this->assertEquals(
      'parent_field = 21',
      $condition->getSql()
    );
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testGetSqlWithList() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->will(
        $this->returnValueMap(
          array(
            array(
              array('parent_field' => array(21, 42)), NULL, '=', 'parent_field IN (21, 42)'
            )
          )
        )
      );

    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));

    $condition = new PapayaDatabaseConditionElement_TestProxy(
      $group, 'parent_field', array(21, 42), '='
    );

    $this->assertEquals(
      'parent_field IN (21, 42)',
      $condition->getSql()
    );
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testGetSqlWithInvalidFieldNameExpectingException() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->never())
      ->method('getSqlCondition');

    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));

    $condition = new PapayaDatabaseConditionElement_TestProxy($group, '', NULL, '=');
    $this->setExpectedException('LogicException');
    $condition->getSql();
  }

  /**
   * @covers PapayaDatabaseConditionElement
   */
  public function testGetSqlWithExceptionInSilentMode() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->never())
      ->method('getSqlCondition');

    $group = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));

    $condition = new PapayaDatabaseConditionElement_TestProxy($group, '', NULL);

    $this->assertEquals('', $condition->getSql(TRUE));
  }
}

class PapayaDatabaseConditionElement_TestProxy extends PapayaDatabaseConditionElement {

  public function mapFieldName($value) {
    return parent::mapFieldName($value);
  }
}