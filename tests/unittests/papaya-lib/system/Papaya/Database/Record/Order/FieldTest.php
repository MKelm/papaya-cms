<?php
require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Database/Record/Order/Field.php');

class PapayaDatabaseRecordOrderFieldTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordOrderField::__construct
  * @covers PapayaDatabaseRecordOrderField::__toString
  */
  public function testSimpleFieldName() {
    $orderBy = new PapayaDatabaseRecordOrderField('field');
    $this->assertEquals('field ASC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderField::__construct
  * @covers PapayaDatabaseRecordOrderField::__toString
  * @covers PapayaDatabaseRecordOrderField::getDirectionString
  */
  public function testFieldNameAndDirection() {
    $orderBy = new PapayaDatabaseRecordOrderField(
      'field', PapayaDatabaseRecordOrderField::DESCENDING
    );
    $this->assertEquals('field DESC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderField::__construct
  * @covers PapayaDatabaseRecordOrderField::__toString
  * @covers PapayaDatabaseRecordOrderField::getDirectionString
  */
  public function testWithInvalidDirectionExpectingAscending() {
    $orderBy = new PapayaDatabaseRecordOrderField(
      'field', -23
    );
    $this->assertEquals('field ASC', (string)$orderBy);
  }
}