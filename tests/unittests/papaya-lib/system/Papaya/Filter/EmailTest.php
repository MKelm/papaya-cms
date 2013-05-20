<?php
require_once(substr(__FILE__, 0, -46).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Email.php');

class PapayaFilterEmailTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterEmail::validate
  */
  public function testValidateExpectingTrue() {
    $filter = new PapayaFilterEmail();
    $this->assertTrue($filter->validate('info@papaya-cms.com'));
  }

  /**
  * @covers PapayaFilterEmail::validate
  */
  public function testValidateExpectingException() {
    $filter = new PapayaFilterEmail();
    $this->setExpectedException('PapayaFilterExceptionType');
    $filter->validate("invalid email @dress");
  }

  /**
  * @covers PapayaFilterEmail::filter
  * @dataProvider provideFilterData
  */
  public function testFilter($expected, $input) {
    $filter = new PapayaFilterEmail();
    $this->assertEquals($expected, $filter->filter($input));
  }

  /**********************
  * Data Provider
  **********************/

  public static function provideFilterData() {
    return array(
      'valid' => array('info@papaya-cms.com', "info@papaya-cms.com"),
      'invalid domain' => array(NULL, 'info@papaya cms.com'),
      'invalid prefix' => array(NULL, 'i n f o@papaya-cms.com'),
      'invalid tld' => array(NULL, 'info@papaya-cms.')
    );
  }
}
