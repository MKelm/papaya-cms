<?php

require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application/Profile/Request.php');

class PapayaApplicationProfileRequestTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileRequest::getIdentifier
  */
  public function testGetIdentifier() {
    $profile = new PapayaApplicationProfileRequest();
    $this->assertEquals(
      'Request',
      $profile->getIdentifier()
    );
  }

  /**
  * @covers PapayaApplicationProfileRequest::createObject
  */
  public function testCreateObject() {
    $options = $this->getMockConfigurationObject(
      array(
        'PAPAYA_URL_LEVEL_SEPARATOR' => '[]',
        'PAPAYA_PATH_WEB' => '/'
      )
    );
    $application = $this->getMockApplicationObject(array('options' => $options));
    $profile = new PapayaApplicationProfileRequest();
    $request = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaRequest',
      $request
    );
  }
}
?>
