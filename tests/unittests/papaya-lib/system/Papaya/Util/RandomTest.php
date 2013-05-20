<?php

require_once(substr(__FILE__, 0, -44).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Util/Random.php');

class PapayaUtilRandomTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilRandom::rand
  */
  public function testRand() {
    $random = PapayaUtilRandom::rand();
    $this->assertGreaterThanOrEqual(0, $random);
  }

  /**
  * @covers PapayaUtilRandom::rand
  */
  public function testRandWithLimits() {
    $random = PapayaUtilRandom::rand(1, 1);
    $this->assertGreaterThanOrEqual(1, $random);
  }

  /**
  * @covers PapayaUtilRandom::getId
  */
  public function testGetId() {
    $idOne = PapayaUtilRandom::getId();
    $idTwo = PapayaUtilRandom::getId();
    $this->assertNotEquals($idOne, $idTwo);
  }
}