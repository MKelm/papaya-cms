<?php
require_once(substr(__FILE__, 0, -65).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryProfileGeneratorTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileGenerator::getFilter
   */
  public function testGetFilterWithIntegerMinAndMax() {
    $profile = new PapayaFilterFactoryProfileGenerator();
    $profile->options(array('PapayaFilterInteger', 1, 42));
    $filter = $profile->getFilter();
    $this->assertInstanceOf('PapayaFilterInteger', $filter);
    $this->assertTrue($filter->validate('21'));
  }

  /**
   * @covers PapayaFilterFactoryProfileGenerator::getFilter
   */
  public function testGetFilterWithInvalidOptionsExpectingException() {
    $profile = new PapayaFilterFactoryProfileGenerator();
    $profile->options(NULL);
    $this->setExpectedException('PapayaFilterFactoryExceptionInvalidOptions');
    $profile->getFilter();
  }

  /**
   * @covers PapayaFilterFactoryProfileGenerator::getFilter
   */
  public function testGetFilterWithInvalidFilterClass() {
    $profile = new PapayaFilterFactoryProfileGenerator();
    $profile->options(array('stdClass'));
    $this->setExpectedException('PapayaFilterFactoryExceptionInvalidFilter');
    $profile->getFilter();
  }
}