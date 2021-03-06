<?php
require_once(substr(__FILE__, 0, -58).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaDatabaseConditionGroupTest extends PapayaTestCase {

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testConstructorWithDatabaseAccess() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $this->assertNull($group->getParent());
    $this->assertSame($databaseAccess, $group->getDatabaseAccess());
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testConstructorWithMapping() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $mapping = $this
      ->getMockBuilder('PapayaDatabaseInterfaceMapping')
      ->getMock();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess, $mapping);
    $this->assertSame($mapping, $group->getMapping());
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testConstructorWithInterfaceDatabaseAccess() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $parent = $this
      ->getMock('PapayaDatabaseInterfaceAccess');
    $parent
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $group = new PapayaDatabaseConditionGroup_TestProxy($parent);
    $this->assertNull($group->getParent());
    $this->assertSame($databaseAccess, $group->getDatabaseAccess());
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testConstructorWithGroup() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $parent = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $parent
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $group = new PapayaDatabaseConditionGroup_TestProxy($parent);
    $this->assertSame($parent, $group->getParent());
    $this->assertSame($databaseAccess, $group->getDatabaseAccess());
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testConstructorWithInvalidParent() {
    $this->setExpectedException('InvalidArgumentException');
    $group = new PapayaDatabaseConditionGroup_TestProxy(new stdClass());
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testEnd() {
    $parent = $this
      ->getMockBuilder('PapayaDatabaseConditionGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group = new PapayaDatabaseConditionGroup_TestProxy($parent);
    $this->assertSame($parent, $group->end());
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testCountWhileEmpty() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $this->assertCount(0, $group);
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testCountTwoElements() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $group
      ->isEqual('foo', 'bar')
      ->isEqual('bar', 'foo');
    $this->assertCount(2, $group);
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testGetIteratorWhileEmpty() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $this->assertEquals(array(), iterator_to_array($group));
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testGetIteratorWithOneSubGroup() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $subGroup = $group->logicalAnd();
    $this->assertCount(1, iterator_to_array($group));
    $this->assertSame($group, $subGroup->end());
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testGetSqlWithIsEqual() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with(array('field' => 'value'))
      ->will($this->returnValue("field = 'value'"));

    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $group->isEqual('field', 'value');
    $this->assertEquals("(field = 'value')", $group->getSql());
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testGetSqlWithTwoSubgroupsOneOfThemEmpty() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('getSqlCondition'))
      ->disableOriginalConstructor()
      ->getMock();
    $databaseAccess
      ->expects($this->any())
      ->method('getSqlCondition')
      ->with(array('field' => 'value'))
      ->will($this->returnValue("field = 'value'"));

    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $group
      ->logicalOr()
        ->end()
      ->logicalOr()
        ->isEqual('field', 'value')
        ->isEqual('field', 'value');

    $this->assertEquals("((field = 'value' OR field = 'value'))", $group->getSql());
  }

  /**
   * @covers PapayaDatabaseConditionGroup
   */
  public function testUnknownConditionCallExpectingException() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->getMock();
    $group = new PapayaDatabaseConditionGroup_TestProxy($databaseAccess);
    $this->setExpectedException('BadMethodCallException');
    $group->isUnknownCondition();
  }
}

class PapayaDatabaseConditionGroup_TestProxy extends PapayaDatabaseConditionGroup {

}