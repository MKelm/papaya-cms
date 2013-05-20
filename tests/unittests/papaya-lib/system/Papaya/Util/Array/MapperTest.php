<?php

require_once(substr(__FILE__, 0, -51).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Util/Array/Mapper.php');

class PapayaUtilArrayMapperTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilArrayMapper::byIndex
  */
  public function testByIndex() {
    $this->assertEquals(
      array(
        42 => 'caption one',
        'foo' => 'caption two'
      ),
      PapayaUtilArrayMapper::byIndex(
        array(
          42 => array(
            'key' => 'caption one'
          ),
          'foo' => array(
            'key' => 'caption two'
          ),
          'bar' => array(
            'wrong_key' => 'caption three'
          )
        ),
        'key'
      )
    );
  }

  /**
  * @covers PapayaUtilArrayMapper::byIndex
  */
  public function testByIndexWithTraversable() {
    $this->assertEquals(
      array(
        42 => 'caption one',
        'foo' => 'caption two'
      ),
      PapayaUtilArrayMapper::byIndex(
        new ArrayIterator(
          array(
            42 => array(
              'key' => 'caption one'
            ),
            'foo' => array(
              'key' => 'caption two'
            ),
            'bar' => array(
              'wrong_key' => 'caption three'
            )
          )
        ),
        'key'
      )
    );
  }
}