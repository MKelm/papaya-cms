<?php
require_once(substr(__FILE__, 0, -77).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application/Profile/Administration/Language.php');

class PapayaApplicationProfileAdministrationLanguageTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileAdministrationLanguage::getIdentifier
  */
  public function testGetIdentifier() {
    $profile = new PapayaApplicationProfileAdministrationLanguage();
    $this->assertEquals(
      'AdministrationLanguage',
      $profile->getIdentifier()
    );
  }

  /**
  * @covers PapayaApplicationProfileAdministrationLanguage::createObject
  */
  public function testCreateObject() {
    $application = $this->getMockApplicationObject();
    $profile = new PapayaApplicationProfileAdministrationLanguage();
    $switch = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaAdministrationLanguagesSwitch',
      $switch
    );
  }
}
?>