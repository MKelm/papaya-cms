<?php
require_once(substr(__FILE__, 0, -56).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Configuration/Iterator.php');

class PapayaConfigurationIteratorTest extends PapayaTestCase {

  public function testIterator() {
    $config = $this
      ->getMockBuilder('PapayaConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $config
      ->expects($this->any())
      ->method('get')
      ->will($this->returnValue(42));
    $iterator = new PapayaConfigurationIterator(array('SAMPLE_INT'), $config);
    $result = iterator_to_array($iterator);
    $this->assertEquals(
      array('SAMPLE_INT' => 42),
      $result
    );
  }

}