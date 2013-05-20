<?php
require_once(substr(__FILE__, 0, -47).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaFilterFactoryTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactory::getProfile
   */
  public function testGetProfile() {
    $factory = new PapayaFilterFactory();
    $this->assertInstanceOf('PapayaFilterFactoryProfile', $factory->getProfile('isEmail'));
  }

  /**
   * @covers PapayaFilterFactory::getProfile
   */
  public function testGetProfileExpectingException() {
    $factory = new PapayaFilterFactory();
    $this->setExpectedException('PapayaFilterFactoryExceptionInvalidProfile');
    $factory->getProfile('SomeInvalidProfileName');
  }

  /**
   * @covers PapayaFilterFactory::getFilter
   */
  public function testGetFilter() {
    $profile = $this->getMock('PapayaFilterFactoryProfile');
    $profile
      ->expects($this->once())
      ->method('options')
      ->with(NULL);
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->getMock('PapayaFilter')));
    $factory = new PapayaFilterFactory();
    $filter = $factory->getFilter($profile);
    $this->assertInstanceOf('PapayaFilter', $filter);
    $this->assertNotInstanceOf('PapayaFilterLogicalOr', $filter);
  }

  /**
   * @covers PapayaFilterFactory::getFilter
   */
  public function testGetFilterNotMandatory() {
    $profile = $this->getMock('PapayaFilterFactoryProfile');
    $profile
      ->expects($this->once())
      ->method('options')
      ->with(NULL);
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->getMock('PapayaFilter')));
    $factory = new PapayaFilterFactory();
    $filter = $factory->getFilter($profile, FALSE);
    $this->assertInstanceOf('PapayaFilterLogicalOr', $filter);
  }

  /**
   * @covers PapayaFilterFactory::getFilter
   */
  public function testGetFilterNotMandatoryWithOptions() {
    $profile = $this->getMock('PapayaFilterFactoryProfile');
    $profile
      ->expects($this->once())
      ->method('options')
      ->with('data');
    $profile
      ->expects($this->once())
      ->method('getFilter')
      ->will($this->returnValue($this->getMock('PapayaFilter')));
    $factory = new PapayaFilterFactory();
    $filter = $factory->getFilter($profile, TRUE, 'data');
    $this->assertInstanceOf('PapayaFilter', $filter);
  }

  /**
   * @covers PapayaFilterFactory::getFilter
   */
  public function testGetFilterWithNamedProfile() {
    $factory = new PapayaFilterFactory();
    $this->assertInstanceOf('PapayaFilter', $factory->getFilter('isEmail'));
  }
}