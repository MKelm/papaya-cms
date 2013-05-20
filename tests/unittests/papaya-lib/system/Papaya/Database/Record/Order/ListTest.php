<?php
require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Database/Record/Order/List.php');

class PapayaDatabaseRecordOrderListTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordOrderList::__construct
  */
  public function testContructorWithoutArguments() {
    $orderBy = new PapayaDatabaseRecordOrderList();
    $this->assertEquals(0, $orderBy->count());
  }

  /**
  * @covers PapayaDatabaseRecordOrderList::__construct
  */
  public function testContructorWithArguments() {
    $child = $this->getMock('PapayaDatabaseInterfaceOrder');
    $orderBy = new PapayaDatabaseRecordOrderList($child);
    $this->assertEquals(1, $orderBy->count());
  }

  /**
  * @covers PapayaDatabaseRecordOrderList::__toString
  */
  public function testToStringWithTwoItems() {
    $one = $this->getMock('PapayaDatabaseInterfaceOrder');
    $one
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('field_one ASC'));
    $two = $this->getMock('PapayaDatabaseInterfaceOrder');
    $two
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('field_two DESC'));
    $orderBy = new PapayaDatabaseRecordOrderList($one, $two);
    $this->assertEquals('field_one ASC, field_two DESC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderList::__toString
  */
  public function testToStringWithoutItems() {
    $orderBy = new PapayaDatabaseRecordOrderList();
    $this->assertEquals('', (string)$orderBy);
  }
}