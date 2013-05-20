<?php

require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application/Profile/Options.php');

class PapayaApplicationProfileOptionsTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileOptions::getIdentifier
  */
  public function testGetIdentifier() {
    $profile = new PapayaApplicationProfileOptions();
    $this->assertEquals(
      'Options',
      $profile->getIdentifier()
    );
  }

  /**
  * @covers PapayaApplicationProfileOptions::createObject
  */
  public function testCreateObject() {
    $application = $this->getMock('PapayaApplication');
    $profile = new PapayaApplicationProfileOptions();
    $options = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaConfiguration',
      $options
    );
  }
}
