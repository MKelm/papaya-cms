<?php
require_once(substr(__FILE__, 0, -56).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaContentStructureValueTest extends PapayaTestCase {

  /**
   * @covers PapayaContentStructureValue::__construct
   */
  public function testConstructor() {
    $group = $this
      ->getMockBuilder('PapayaContentStructureGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $value = new PapayaContentStructureValue($group);
    $this->assertAttributeSame($group, '_group', $value);
  }

  /**
   * @covers PapayaContentStructureValue::getIdentifier
   */
  public function testGetIdentifier() {
    $group = $this
      ->getMockBuilder('PapayaContentStructureGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getIdentifier')
      ->will($this->returnValue('PAGE/GROUP'));
    $value = new PapayaContentStructureValue($group);
    $value->name = 'VALUE';
    $this->assertEquals(
      'PAGE/GROUP/VALUE', $value->getIdentifier()
    );
  }
}
