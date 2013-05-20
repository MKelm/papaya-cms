<?php
require_once(substr(__FILE__, 0, -47).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Logical.php');

class PapayaFilterLogicalTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterLogical::__construct
  * @covers PapayaFilterLogical::_setFilters
  */
  public function testConstructorWithTwoFilters() {
    $subFilterOne = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $subFilterTwo = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $filter = new PapayaFilterLogical_TestProxy($subFilterOne, $subFilterTwo);
    $this->assertAttributeEquals(
      array($subFilterOne, $subFilterTwo),
      '_filters',
      $filter
    );
  }

  /**
  * @covers PapayaFilterLogical::__construct
  * @covers PapayaFilterLogical::_setFilters
  */
  public function testConstructorWithThreeFilters() {
    $subFilterOne = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $subFilterTwo = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $subFilterThree = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $filter = new PapayaFilterLogical_TestProxy($subFilterOne, $subFilterTwo, $subFilterThree);
    $this->assertAttributeEquals(
      array($subFilterOne, $subFilterTwo, $subFilterThree),
      '_filters',
      $filter
    );
  }

  /**
  * @covers PapayaFilterLogical::__construct
  * @covers PapayaFilterLogical::_setFilters
  */
  public function testContructorWithOneFilterExpectingException() {
    $this->setExpectedException('InvalidArgumentException');
    $filter = new PapayaFilterLogical_TestProxy(
      $this->getMock('PapayaFilter', array('validate', 'filter'))
    );
  }

  /**
  * @covers PapayaFilterLogical::__construct
  * @covers PapayaFilterLogical::_setFilters
  */
  public function testContructorWithInvalidObjectsExpectingException() {
    $this->setExpectedException('InvalidArgumentException');
    $filter = new PapayaFilterLogical_TestProxy(
      new stdClass(), new stdClass()
    );
  }
}

class PapayaFilterLogical_TestProxy extends PapayaFilterLogical {

  public function validate($value) {
  }

  public function filter($value) {
  }
}