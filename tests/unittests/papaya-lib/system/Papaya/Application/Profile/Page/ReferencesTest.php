<?php
require_once(substr(__FILE__, 0, -69).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application/Profile/Page/References.php');

class PapayaApplicationProfilePageReferencesTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfilePageReferences::getIdentifier
  */
  public function testGetIdentifier() {
    $profile = new PapayaApplicationProfilePageReferences();
    $this->assertEquals(
      'PageReferences',
      $profile->getIdentifier()
    );
  }

  /**
  * @covers PapayaApplicationProfilePageReferences::createObject
  */
  public function testCreateObject() {
    $application = $this->getMockApplicationObject();
    $profile = new PapayaApplicationProfilePageReferences();
    $options = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaUiReferencePageFactory',
      $options
    );
  }
}
